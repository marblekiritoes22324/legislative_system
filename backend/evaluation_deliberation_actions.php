<?php
// backend/evaluation_deliberation_actions.php — Evaluation Revision Request Action
require_once __DIR__ . '/../config/db.php';
if (file_exists(__DIR__ . '/log_activity.php')) {
    require_once __DIR__ . '/log_activity.php';
}

header('Content-Type: application/json');

if (!isset($conn) || !$conn) {
    echo json_encode(['success' => false, 'error' => 'Database connection failed']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
    exit;
}

// Support JSON payload or form-data
$input_raw = file_get_contents('php://input');
$json_data = json_decode($input_raw, true);
$data = is_array($json_data) ? $json_data : $_POST;

$action = trim($data['action'] ?? '');
$policy_id = intval($data['policy_id'] ?? 0);
$policy_title = trim($data['policy_title'] ?? '');
$actor = !empty($data['evaluator']) ? trim($data['evaluator']) : (!empty($data['actor']) ? trim($data['actor']) : 'Admin');

if ($policy_id <= 0 && !empty($policy_title)) {
    $stmt = mysqli_prepare($conn, "SELECT id FROM policy_records WHERE title = ? LIMIT 1");
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

if ($policy_id <= 0) {
    echo json_encode(['success' => false, 'error' => 'Invalid or missing policy identifier.']);
    exit;
}

// Fetch existing evaluation record
$eval_stmt = mysqli_prepare($conn, "SELECT id, policy_title, notes, status FROM evaluations WHERE policy_id = ? LIMIT 1");
$existing_eval = null;
if ($eval_stmt) {
    mysqli_stmt_bind_param($eval_stmt, "i", $policy_id);
    mysqli_stmt_execute($eval_stmt);
    $res = mysqli_stmt_get_result($eval_stmt);
    if ($res && $row = mysqli_fetch_assoc($res)) {
        $existing_eval = $row;
        if (empty($policy_title)) {
            $policy_title = $row['policy_title'];
        }
    }
    mysqli_stmt_close($eval_stmt);
}

// Parse existing notes
$notes_data = [];
if (!empty($existing_eval['notes'])) {
    $decoded = json_decode($existing_eval['notes'], true);
    if (is_array($decoded)) {
        $notes_data = $decoded;
    }
}

if ($action === 'request_revision') {
    $failed_criteria = $data['failed_criteria'] ?? ($data['criteria'] ?? []);
    if (is_string($failed_criteria)) {
        $decoded_crit = json_decode($failed_criteria, true);
        if (is_array($decoded_crit)) {
            $failed_criteria = $decoded_crit;
        } else {
            $failed_criteria = [$failed_criteria];
        }
    }
    $instructions = trim($data['instructions'] ?? ($data['feedback'] ?? 'Please review and address the cited evaluation criteria deficits before resubmitting.'));

    $notes_data['revision_requested'] = true;
    $notes_data['revision_requested_at'] = date('Y-m-d H:i:s');
    $notes_data['revision_requested_by'] = $actor;
    $notes_data['revision_instructions'] = $instructions;
    $notes_data['failed_criteria'] = $failed_criteria;
    $notes_data['status'] = 'Needs Revision';

    $updated_notes = json_encode($notes_data);
    $upd = mysqli_prepare($conn, "UPDATE evaluations SET status = 'Needs Revision', notes = ?, updated_at = NOW() WHERE policy_id = ?");
    if ($upd) {
        mysqli_stmt_bind_param($upd, "si", $updated_notes, $policy_id);
        mysqli_stmt_execute($upd);
        mysqli_stmt_close($upd);
    }

    $crit_summary = is_array($failed_criteria) ? implode('; ', array_map(function($c) {
        return is_array($c) ? ($c['criterion'] . ': ' . ($c['reason'] ?? '')) : (string)$c;
    }, $failed_criteria)) : (string)$failed_criteria;

    if (function_exists('log_audit_action')) {
        log_audit_action($conn, $actor, 'Evaluations', 'Requested revision for policy "' . $policy_title . '" sent to sponsor/drafter citing: ' . ($crit_summary ?: 'Evaluation criteria deficits'), 'Completed');
    }

    echo json_encode([
        'success' => true,
        'message' => 'Revision request sent back to the sponsor/drafter along with specific failed criteria.',
        'policy_id' => $policy_id,
        'status' => 'Needs Revision',
        'revision_requested_at' => date('M d, Y h:i A')
    ]);
    exit;
}

echo json_encode(['success' => false, 'error' => 'Unknown action: ' . $action]);
