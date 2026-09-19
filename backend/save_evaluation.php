<?php
// backend/save_evaluation.php — Save & Update Policy Evaluation Results in DB
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/log_activity.php';

header('Content-Type: application/json');

if (!isset($conn) || !$conn) {
    echo json_encode(['success' => false, 'error' => 'Database connection failed']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
    exit;
}

$policy_id = isset($_POST['policy_id']) ? intval($_POST['policy_id']) : 0;
$policy_title = isset($_POST['policy_title']) ? trim($_POST['policy_title']) : '';
$risk_level = isset($_POST['risk_level']) ? trim($_POST['risk_level']) : 'Low Risk';
$ai_analysis = isset($_POST['ai_analysis']) ? trim($_POST['ai_analysis']) : '';
$recommendation = isset($_POST['recommendation']) ? trim($_POST['recommendation']) : '';
$reason = isset($_POST['reason']) ? trim($_POST['reason']) : '';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$default_role = (!empty($_SESSION['role']) && strtolower($_SESSION['role']) === 'staff') ? 'Staff' : 'Admin';
$evaluator = !empty($_POST['evaluator']) ? trim($_POST['evaluator']) : '';
if (empty($evaluator) || $evaluator === 'Admin' || $evaluator === 'Staff') {
    if (!empty($_SESSION['full_name']) && trim($_SESSION['full_name']) !== '') {
        $person = trim($_SESSION['full_name']);
        $evaluator = preg_match('/^(Admin|Staff|Administrator)\s*[-:]\s*/i', $person) ? $person : ($default_role . ' - ' . $person);
    } elseif (!empty($_SESSION['username']) && trim($_SESSION['username']) !== '') {
        $person = trim($_SESSION['username']);
        $evaluator = preg_match('/^(Admin|Staff|Administrator)\s*[-:]\s*/i', $person) ? $person : ($default_role . ' - ' . $person);
    }
} else {
    if (!preg_match('/^(Admin|Staff|Administrator)\s*[-:]\s*/i', $evaluator)) {
        $evaluator = $default_role . ' - ' . $evaluator;
    }
}
if (empty($evaluator)) {
    $evaluator = $default_role;
}
$status = 'Completed';

$improvements_raw = isset($_POST['improvements']) ? $_POST['improvements'] : [];
$improvements = [];
if (is_string($improvements_raw)) {
    $decoded = json_decode($improvements_raw, true);
    $improvements = is_array($decoded) ? $decoded : [$improvements_raw];
} elseif (is_array($improvements_raw)) {
    $improvements = $improvements_raw;
}

// 1. If policy_id <= 0, attempt lookup by exact title
if ($policy_id <= 0 && !empty($policy_title)) {
    $stmt = mysqli_prepare($conn, "SELECT id FROM policy_records WHERE title = ? ORDER BY id DESC LIMIT 1");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "s", $policy_title);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $found_id);
        if (mysqli_stmt_fetch($stmt) && $found_id > 0) {
            $policy_id = $found_id;
        }
        mysqli_stmt_close($stmt);
    }
}

// 2. Verify foreign key reference exists in policy_records
$has_fk = false;
if ($policy_id > 0) {
    $chk = mysqli_prepare($conn, "SELECT id, title FROM policy_records WHERE id = ?");
    if ($chk) {
        mysqli_stmt_bind_param($chk, "i", $policy_id);
        mysqli_stmt_execute($chk);
        mysqli_stmt_bind_result($chk, $real_id, $real_title);
        if (mysqli_stmt_fetch($chk)) {
            $has_fk = true;
            if (empty($policy_title))
                $policy_title = $real_title;
        }
        mysqli_stmt_close($chk);
    }
}

// 3. Fallback lookup by title pattern if FK check failed
if (!$has_fk && !empty($policy_title)) {
    $stmt = mysqli_prepare($conn, "SELECT id, title FROM policy_records WHERE title LIKE ? ORDER BY id DESC LIMIT 1");
    if ($stmt) {
        $like_t = "%" . $policy_title . "%";
        mysqli_stmt_bind_param($stmt, "s", $like_t);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $found_id, $found_title);
        if (mysqli_stmt_fetch($stmt) && $found_id > 0) {
            $policy_id = $found_id;
            $policy_title = $found_title;
            $has_fk = true;
        }
        mysqli_stmt_close($stmt);
    }
}

// 4. Auto-create policy record if missing so evaluation save never fails
if (!$has_fk || $policy_id <= 0) {
    if (!empty($policy_title)) {
        $ins = mysqli_prepare($conn, "INSERT INTO policy_records (title, category, author, status, created_at) VALUES (?, 'Infrastructure & Public Safety', 'Office of the City Council', 'Completed', NOW())");
        if ($ins) {
            mysqli_stmt_bind_param($ins, "s", $policy_title);
            if (mysqli_stmt_execute($ins)) {
                $policy_id = mysqli_insert_id($conn);
                $has_fk = true;
            }
            mysqli_stmt_close($ins);
        }
    }
}

if (!$has_fk || $policy_id <= 0) {
    echo json_encode(['success' => false, 'error' => 'Policy record could not be initialized']);
    exit;
}

function reconcile_criterion_level($level, $reason)
{
    $lvl = ucfirst(strtolower(trim($level ?? 'High')));
    $txt = strtolower(trim($reason ?? ''));
    if (empty($txt) || $txt === 'awaiting evaluation.' || $txt === '—')
        return $lvl ?: 'High';

    $has_no_conflict = (
        stripos($txt, 'no statutory conflict') !== false ||
        stripos($txt, 'no statutory conflicts') !== false ||
        stripos($txt, 'no conflict') !== false ||
        stripos($txt, 'without conflict') !== false ||
        stripos($txt, 'without statutory conflict') !== false
    );

    $has_deficit = (
        stripos($txt, 'insufficient') !== false ||
        stripos($txt, 'gap') !== false ||
        stripos($txt, 'unfunded') !== false ||
        stripos($txt, 'lacks') !== false ||
        stripos($txt, 'unquantified') !== false ||
        (!$has_no_conflict && stripos($txt, 'conflict') !== false) ||
        stripos($txt, 'ultra vires') !== false ||
        stripos($txt, 'severe') !== false ||
        stripos($txt, 'deficient') !== false ||
        stripos($txt, 'missing') !== false
    );

    $has_compliant = (
        stripos($txt, 'manageable') !== false ||
        stripos($txt, 'available') !== false ||
        stripos($txt, 'positive') !== false ||
        stripos($txt, 'strong') !== false ||
        stripos($txt, 'compliant') !== false ||
        stripos($txt, 'satisfies') !== false ||
        stripos($txt, 'enhances') !== false ||
        stripos($txt, 'sustainable') !== false ||
        stripos($txt, 'within delegated') !== false ||
        stripos($txt, 'benefits') !== false ||
        $has_no_conflict
    );

    if ($has_deficit)
        return 'Low';
    if ($lvl === 'Low' && $has_compliant)
        return 'High';
    if ($lvl === 'Low')
        return 'Low';
    if ($lvl === 'Medium' || $lvl === 'Moderate')
        return 'Medium';
    return 'High';
}

$econ_level = isset($_POST['economic_level']) ? trim($_POST['economic_level']) : 'High';
$econ_reason = isset($_POST['economic_reason']) ? trim($_POST['economic_reason']) : 'Funding realism and cost allocations are manageable within municipal budget.';

$social_level = isset($_POST['social_level']) ? trim($_POST['social_level']) : 'High';
$social_reason = isset($_POST['social_reason']) ? trim($_POST['social_reason']) : 'The policy provides measurable community welfare benefits to affected districts.';

$env_level = isset($_POST['env_level']) ? trim($_POST['env_level']) : 'High';
$env_reason = isset($_POST['env_reason']) ? trim($_POST['env_reason']) : 'Maintains positive ecological resilience and sustainability standards.';

$legal_level = isset($_POST['legal_level']) ? trim($_POST['legal_level']) : 'High';
$legal_reason = isset($_POST['legal_reason']) ? trim($_POST['legal_reason']) : 'Within delegated municipal power under RA 7160 with no statutory conflicts.';

$legal_authority = isset($_POST['legal_authority']) ? trim($_POST['legal_authority']) : '';
$drafting_quality = isset($_POST['drafting_quality']) ? trim($_POST['drafting_quality']) : '';
$procedural_compliance = isset($_POST['procedural_compliance']) ? trim($_POST['procedural_compliance']) : '';

$econ_level = reconcile_criterion_level($econ_level, $econ_reason);
$social_level = reconcile_criterion_level($social_level, $social_reason);
$env_level = reconcile_criterion_level($env_level, $env_reason);
$legal_level = reconcile_criterion_level($legal_level, $legal_reason);

// Check if any criterion is Low/Fail
$has_low_score = ($econ_level === 'Low' || $social_level === 'Low' || $env_level === 'Low' || $legal_level === 'Low');

// STATUS MODEL (Draft, Approved, Needs Revision)
$status = $has_low_score ? 'Needs Revision' : 'Approved';

$notes_payload = json_encode([
    'ai_analysis' => $ai_analysis,
    'status' => $status,
    'legal_authority' => $legal_authority,
    'drafting_quality' => $drafting_quality,
    'procedural_compliance' => $procedural_compliance,
    'criteria' => [
        'economic' => ['level' => $econ_level, 'reason' => $econ_reason],
        'social' => ['level' => $social_level, 'reason' => $social_reason],
        'env' => ['level' => $env_level, 'reason' => $env_reason],
        'legal' => ['level' => $legal_level, 'reason' => $legal_reason]
    ]
]);

// Determine numeric overall score
$overall_score = $has_low_score ? 4.5 : 8.5;

// Save evaluation record
$summary_text = $ai_analysis;
$query = "INSERT INTO evaluations (policy_id, policy_title, evaluator, risk_level, ai_recommendation, notes, status, overall_score, updated_at) 
          VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW()) 
          ON DUPLICATE KEY UPDATE 
          policy_title = VALUES(policy_title),
          evaluator = VALUES(evaluator),
          risk_level = VALUES(risk_level),
          ai_recommendation = VALUES(ai_recommendation),
          notes = VALUES(notes),
          status = VALUES(status),
          approved_by = NULL,
          approved_at = NULL,
          overall_score = VALUES(overall_score),
          updated_at = NOW()";

$stmt = mysqli_prepare($conn, $query);
if ($stmt) {
    mysqli_stmt_bind_param($stmt, "issssssd", $policy_id, $policy_title, $evaluator, $risk_level, $summary_text, $notes_payload, $status, $overall_score);
    $executed = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    if ($executed) {
        // Synchronize status with policy_records table
        @mysqli_query($conn, "UPDATE policy_records SET status = '" . mysqli_real_escape_string($conn, $status) . "' WHERE id = " . intval($policy_id));

        // Log audit action
        $audit_actor = ($evaluator === 'Staff') ? 'Staff' : 'Admin';
        log_audit_action($conn, $audit_actor, 'Evaluations', 'Evaluated policy: ' . $policy_title, 'Completed');

        // Record evaluation version snapshot
        require_once __DIR__ . '/evaluation_versions_helper.php';
        record_evaluation_version($conn, $policy_id, [
            'evaluator' => $evaluator,
            'risk_level' => $risk_level,
            'economic_score' => $overall_score,
            'social_score' => $overall_score,
            'environmental_score' => $overall_score,
            'legal_score' => $overall_score,
            'overall_score' => $overall_score,
            'ai_recommendation' => $recommendation,
            'notes' => $notes_payload,
            'status' => $status,
            'approved_by' => ($evaluator === 'Admin') ? 'System Administrator' : 'Staff Evaluator'
        ]);

        $now = new DateTime();
        $date_fmt = $now->format('M d, Y h:i A');

        echo json_encode([
            'success' => true,
            'message' => 'Evaluation saved successfully',
            'evaluation_date' => $date_fmt,
            'policy_id' => $policy_id,
            'policy_title' => $policy_title,
            'risk_level' => $risk_level,
            'recommendation' => $recommendation,
            'ai_analysis' => $ai_analysis,
            'reason' => $reason,
            'improvements' => $improvements,
            'economic_level' => $econ_level,
            'economic_reason' => $econ_reason,
            'social_level' => $social_level,
            'social_reason' => $social_reason,
            'env_level' => $env_level,
            'env_reason' => $env_reason,
            'legal_level' => $legal_level,
            'legal_reason' => $legal_reason,
            'evaluator' => $evaluator,
            'status' => $status
        ]);
        exit;
    } else {
        echo json_encode(['success' => false, 'error' => mysqli_error($conn)]);
        exit;
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Database query preparation failed']);
    exit;
}
