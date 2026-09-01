<?php
// backend/get_recent_activities.php — Fetch recent legislative activities from DB
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/log_activity.php';

header('Content-Type: application/json');

if (!isset($conn) || !$conn) {
    echo json_encode(['success' => false, 'error' => 'Database connection failed']);
    exit;
}

ensure_audit_logs_table($conn);

$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
$res = mysqli_query($conn, "SELECT * FROM audit_logs ORDER BY created_at DESC, 1 DESC LIMIT " . $limit);
$activities = [];

if ($res) {
    while ($row = mysqli_fetch_assoc($res)) {
        $ts = !empty($row['created_at']) ? strtotime($row['created_at']) : time();
        $uName = $row['user'] ?? 'Admin';
        if ($uName === 'System Administrator' || $uName === 'Administration' || empty($uName)) {
            $uName = 'Admin';
        }
        $activities[] = [
            'id' => $row['log_id'] ?? ($row['id'] ?? 0),
            'date_time' => date('M d, Y h:i A', $ts),
            'raw_date' => $row['created_at'] ?? '',
            'activity' => $row['activity'] ?? ($row['action'] ?? ($row['description'] ?? 'System activity')),
            'module' => $row['module'] ?? 'System',
            'status' => $row['status'] ?? 'Completed',
            'user' => $uName
        ];
    }
}

echo json_encode(['success' => true, 'activities' => $activities]);
