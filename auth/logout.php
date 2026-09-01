<?php
session_start();
require_once __DIR__ . '/../config/db.php';
if (file_exists(__DIR__ . '/../backend/log_activity.php')) {
  require_once __DIR__ . '/../backend/log_activity.php';
}

$user = $_REQUEST['user'] ?? ($_SESSION['full_name'] ?? ($_SESSION['username'] ?? 'User'));
if (function_exists('log_audit_action') && !empty($conn)) {
  log_audit_action($conn, $user, 'System', 'User logout');
}

// Remove all session data
session_unset();

// Destroy the session
session_destroy();

// Redirect to the Welcome Page
if (!isset($_GET['ajax']) && !isset($_POST['ajax'])) {
  header("Location: ../frontend/welcome.php");
  exit();
} else {
  header('Content-Type: application/json');
  echo json_encode(['success' => true]);
  exit();
}
?>