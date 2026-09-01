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
$evaluator = !empty($_POST['evaluator']) ? trim($_POST['evaluator']) : 'Admin';
if ($evaluator === 'Administration' || $evaluator === 'System Administrator') {
    $evaluator = 'Admin';
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
            if (empty($policy_title)) $policy_title = $real_title;
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

$econ_level = isset($_POST['economic_level']) ? trim($_POST['economic_level']) : 'Low';
$econ_reason = isset($_POST['economic_reason']) ? trim($_POST['economic_reason']) : 'Funding and implementation costs are manageable and available.';

$social_level = isset($_POST['social_level']) ? trim($_POST['social_level']) : 'Low';
$social_reason = isset($_POST['social_reason']) ? trim($_POST['social_reason']) : 'The policy provides benefits to affected communities and improves quality of life.';

$env_level = isset($_POST['env_level']) ? trim($_POST['env_level']) : 'Low';
$env_reason = isset($_POST['env_reason']) ? trim($_POST['env_reason']) : 'The policy has minimal expected environmental effects.';

$legal_level = isset($_POST['legal_level']) ? trim($_POST['legal_level']) : 'Low';
$legal_reason = isset($_POST['legal_reason']) ? trim($_POST['legal_reason']) : 'No major legal conflicts were identified with existing laws and regulations.';

$notes_payload = json_encode([
    'ai_analysis' => $ai_analysis,
    'reason' => $reason,
    'improvements' => $improvements,
    'criteria' => [
        'economic' => ['level' => $econ_level, 'reason' => $econ_reason],
        'social' => ['level' => $social_level, 'reason' => $social_reason],
        'env' => ['level' => $env_level, 'reason' => $env_reason],
        'legal' => ['level' => $legal_level, 'reason' => $legal_reason]
    ]
]);

// Determine numeric overall score from risk level
$overall_score = 8.5;
if (stripos($risk_level, 'Moderate') !== false || stripos($risk_level, 'Medium') !== false) {
    $overall_score = 6.5;
} elseif (stripos($risk_level, 'High') !== false) {
    $overall_score = 4.5;
}

// Save or Update evaluation record (WITHOUT modifying or deleting the policy_research record)
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
    mysqli_stmt_bind_param($stmt, "issssssd", $policy_id, $policy_title, $evaluator, $risk_level, $recommendation, $notes_payload, $status, $overall_score);
    $executed = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    if ($executed) {
        // Log audit action
        $audit_actor = ($evaluator === 'Staff') ? 'Staff' : 'Admin';
        log_audit_action($conn, $audit_actor, 'Evaluations', 'Evaluated policy: ' . $policy_title, 'Completed');

        // Record evaluation version snapshot
        require_once __DIR__ . '/evaluation_versions_helper.php';
        record_evaluation_version($conn, $policy_id, [
            'evaluator'           => $evaluator,
            'risk_level'          => $risk_level,
            'economic_score'      => $overall_score,
            'social_score'        => $overall_score,
            'environmental_score' => $overall_score,
            'legal_score'         => $overall_score,
            'overall_score'       => $overall_score,
            'ai_recommendation'   => $recommendation,
            'notes'               => $notes_payload,
            'status'              => $status,
            'approved_by'         => ($evaluator === 'Admin') ? 'System Administrator' : 'Staff Evaluator'
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
            'status' => 'Completed'
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
