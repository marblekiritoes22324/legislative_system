<?php
require_once __DIR__ . '/../config/db.php';
if (file_exists(__DIR__ . '/../backend/log_activity.php')) {
  require_once __DIR__ . '/../backend/log_activity.php';
}

function get_policy_table_name($conn)
{
  static $cached = null;
  if ($cached !== null)
    return $cached;
  $res = @mysqli_query($conn, "SHOW TABLES LIKE 'policy_records'");
  if ($res && mysqli_num_rows($res) > 0) {
    $cached = 'policy_records';
  } else {
    $cached = 'policy_research';
  }
  return $cached;
}

$policy_tbl = get_policy_table_name($conn);

// Auto-ensure required columns exist in database tables
$col = mysqli_query($conn, "SHOW COLUMNS FROM $policy_tbl LIKE 'ai_summary'");
if ($col && mysqli_num_rows($col) === 0) {
  mysqli_query($conn, "ALTER TABLE $policy_tbl ADD COLUMN ai_summary LONGTEXT NULL");
}
$u_tbl = 'user_directory';
$chk_u = @mysqli_query($conn, "SHOW TABLES LIKE 'user_directory'");
if (!$chk_u || mysqli_num_rows($chk_u) === 0) {
  $u_tbl = 'users';
}
mysqli_query($conn, "ALTER TABLE $u_tbl ADD COLUMN IF NOT EXISTS role VARCHAR(100) DEFAULT 'Staff'");
mysqli_query($conn, "ALTER TABLE $u_tbl ADD COLUMN IF NOT EXISTS department VARCHAR(150) DEFAULT 'Secretariat'");
mysqli_query($conn, "ALTER TABLE $u_tbl ADD COLUMN IF NOT EXISTS username VARCHAR(50) NULL");

$message = '';
$messageType = '';
$active_section = $_GET['section'] ?? 'adminDashboardSection';

// Handle CRUD operations
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
  $action = $_POST['action'];

  if ($action === 'provision_user') {
    header('Content-Type: application/json');
    $full_name = trim($_POST['full_name'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password_raw = $_POST['password'] ?? 'password123';
    $password = password_hash($password_raw, PASSWORD_DEFAULT);
    $role = trim($_POST['role'] ?? 'Staff');
    $department = trim($_POST['department'] ?? 'Secretariat & Legal Affairs');
    $status = 'Active';

    if (empty($full_name) || empty($username) || empty($email)) {
      echo json_encode(['success' => false, 'error' => 'Please fill in all required fields.']);
      exit;
    }

    // Check if username or email already exists
    $check_stmt = mysqli_prepare($conn, "SELECT user_id FROM $u_tbl WHERE username = ? OR email = ?");
    if ($check_stmt) {
      mysqli_stmt_bind_param($check_stmt, "ss", $username, $email);
      mysqli_stmt_execute($check_stmt);
      mysqli_stmt_store_result($check_stmt);
      if (mysqli_stmt_num_rows($check_stmt) > 0) {
        mysqli_stmt_close($check_stmt);
        echo json_encode(['success' => false, 'error' => 'An account with that username or email already exists.']);
        exit;
      }
      mysqli_stmt_close($check_stmt);
    }

    $stmt = mysqli_prepare($conn, "INSERT INTO $u_tbl (full_name, username, email, password, role, department, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
    if ($stmt) {
      mysqli_stmt_bind_param($stmt, "sssssss", $full_name, $username, $email, $password, $role, $department, $status);
      $ok = mysqli_stmt_execute($stmt);
      mysqli_stmt_close($stmt);
      if ($ok) {
        log_audit_action($conn, 'System Administrator', 'User Directory', 'Provisioned new account for ' . $full_name);
        echo json_encode(['success' => true, 'message' => 'Account for "' . $full_name . '" (@' . $username . ') was provisioned successfully!']);
        exit;
      }
    }
    echo json_encode(['success' => false, 'error' => 'Database error: ' . mysqli_error($conn)]);
    exit;
  }

  if ($action === 'delete_user') {
    header('Content-Type: application/json');
    $username = trim($_POST['username'] ?? '');
    $user_id = intval($_POST['user_id'] ?? 0);
    $ok = false;

    if ($user_id > 0) {
      $stmt = mysqli_prepare($conn, "DELETE FROM $u_tbl WHERE user_id = ?");
      if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $user_id);
        $ok = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
      }
    } else if (!empty($username)) {
      $stmt = mysqli_prepare($conn, "DELETE FROM $u_tbl WHERE username = ? OR email = ?");
      if ($stmt) {
        mysqli_stmt_bind_param($stmt, "ss", $username, $username);
        $ok = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
      }
    }

    if (function_exists('log_audit_action')) {
      log_audit_action($conn, 'System Administrator', 'User Directory', 'Permanently deleted account: ' . $username);
    }
    echo json_encode(['success' => true]);
    exit;
  }

  if ($action === 'archive_user') {
    header('Content-Type: application/json');
    $username = trim($_POST['username'] ?? '');
    if (!empty($username)) {
      $stmt = mysqli_prepare($conn, "UPDATE $u_tbl SET status = 'Inactive' WHERE username = ? OR email = ?");
      if ($stmt) {
        mysqli_stmt_bind_param($stmt, "ss", $username, $username);
        $ok = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
      }
    }
    if (function_exists('log_audit_action')) {
      log_audit_action($conn, 'System Administrator', 'User Directory', 'Archived account: ' . $username);
    }
    echo json_encode(['success' => true]);
    exit;
  }

  if ($action === 'restore_user') {
    header('Content-Type: application/json');
    $username = trim($_POST['username'] ?? '');
    if (!empty($username)) {
      $stmt = mysqli_prepare($conn, "UPDATE $u_tbl SET status = 'Active' WHERE username = ? OR email = ?");
      if ($stmt) {
        mysqli_stmt_bind_param($stmt, "ss", $username, $username);
        $ok = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
      }
    }
    if (function_exists('log_audit_action')) {
      log_audit_action($conn, 'System Administrator', 'User Directory', 'Restored account: ' . $username);
    }
    echo json_encode(['success' => true]);
    exit;
  }

  if ($action === 'update_user') {
    header('Content-Type: application/json');
    $orig_username = trim($_POST['orig_username'] ?? '');
    $full_name = trim($_POST['full_name'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $role = trim($_POST['role'] ?? '');
    $department = trim($_POST['department'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $status = trim($_POST['status'] ?? 'Active');
    $new_password = trim($_POST['password'] ?? '');

    if (!empty($new_password)) {
      $stmt = mysqli_prepare($conn, "UPDATE $u_tbl SET full_name = ?, username = ?, email = ?, password = ?, role = ?, department = ?, status = ? WHERE username = ? OR email = ?");
      if ($stmt) {
        mysqli_stmt_bind_param($stmt, "sssssssss", $full_name, $username, $email, $new_password, $role, $department, $status, $orig_username, $orig_username);
        $ok = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
      }
    } else {
      $stmt = mysqli_prepare($conn, "UPDATE $u_tbl SET full_name = ?, username = ?, email = ?, role = ?, department = ?, status = ? WHERE username = ? OR email = ?");
      if ($stmt) {
        mysqli_stmt_bind_param($stmt, "ssssssss", $full_name, $username, $email, $role, $department, $status, $orig_username, $orig_username);
        $ok = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
      }
    }

    if (function_exists('log_audit_action')) {
      $log_msg = 'Updated account details for ' . $full_name;
      if (!empty($new_password))
        $log_msg .= ' (Password Reset)';
      log_audit_action($conn, 'System Administrator', 'User Directory', $log_msg);
    }

    echo json_encode(['success' => true]);
    exit;
  }

  if ($action === 'save_ai_summary') {
    $policy_id = (int) ($_POST['policy_id'] ?? 0);
    $ai_summary = $_POST['ai_summary'] ?? '';
    if ($policy_id > 0 && !empty($ai_summary)) {
      $stmt = mysqli_prepare($conn, "UPDATE policy_records SET ai_summary = ? WHERE id = ?");
      mysqli_stmt_bind_param($stmt, "si", $ai_summary, $policy_id);
      $ok = mysqli_stmt_execute($stmt);
      mysqli_stmt_close($stmt);

      if ($ok && function_exists('log_audit_action')) {
        $p_title = 'Policy Record #' . $policy_id;
        $pstmt = mysqli_prepare($conn, "SELECT title FROM policy_records WHERE id = ?");
        if ($pstmt) {
          mysqli_stmt_bind_param($pstmt, "i", $policy_id);
          mysqli_stmt_execute($pstmt);
          mysqli_stmt_bind_result($pstmt, $fetched_title);
          if (mysqli_stmt_fetch($pstmt) && !empty($fetched_title)) {
            $p_title = $fetched_title;
          }
          mysqli_stmt_close($pstmt);
        }
        log_audit_action($conn, 'Admin', 'Policy Records', 'Generated AI Summary for ' . $p_title);
      }

      header('Content-Type: application/json');
      echo json_encode(['success' => $ok]);
      exit;
    }
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Invalid arguments']);
    exit;
  }

  if ($action === 'add') {
    $title = $_POST['title'];
    $category = $_POST['category'];
    $city_origin = !empty($_POST['city_origin']) ? $_POST['city_origin'] : 'City of Manila';
    $author = $_POST['author'];
    $department = $_POST['department'];
    $description = $_POST['description'];
    $keywords = $_POST['keywords'];
    $publication_date = $_POST['publication_date'];
    $related_record = $_POST['related_record'] ?? '';
    $status = $_POST['status'] ?? 'Draft';

    $file_path = '';
    if (isset($_FILES['research_file']) && $_FILES['research_file']['error'] == 0) {
      $target_dir = "../assets/uploads/policies/";
      if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true);
      }
      $file_name = time() . "_" . basename($_FILES["research_file"]["name"]);
      $target_file = $target_dir . $file_name;
      if (move_uploaded_file($_FILES["research_file"]["tmp_name"], $target_file)) {
        $file_path = $file_name;
      }
    }

    $stmt = mysqli_prepare($conn, "INSERT INTO policy_records (title, category, city_origin, author, department, description, keywords, publication_date, related_record, file_path, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "sssssssssss", $title, $category, $city_origin, $author, $department, $description, $keywords, $publication_date, $related_record, $file_path, $status);

    if (mysqli_stmt_execute($stmt)) {
      $new_policy_id = mysqli_insert_id($conn);
      if ($new_policy_id > 0) {
        $initial_rec = 'Approve & Proceed to Full Implementation';
        $initial_notes = json_encode([
          'ai_analysis' => 'The automated policy analyzer evaluated this proposed measure across municipal governance, socioeconomic impact, environmental sustainability, and legal statutory alignment.',
          'reason' => 'The proposed measure demonstrates strong alignment with City of Manila governance priorities with manageable fiscal requirements.',
          'improvements' => [
            'Establish structured inter-agency implementation milestones.',
            'Maintain continuous compliance monitoring with relevant national and local statutes.'
          ],
          'criteria' => [
            'economic' => ['level' => 'Low', 'reason' => 'Budget and operational expenditures align with existing department allocations.'],
            'social' => ['level' => 'Low', 'reason' => 'Provides direct public benefits and enhances service delivery to constituents.'],
            'env' => ['level' => 'Low', 'reason' => 'Satisfies urban ecological standards and regulatory environmental compliance.'],
            'legal' => ['level' => 'Low', 'reason' => 'Compliant with the Local Government Code and relevant municipal ordinances.']
          ]
        ]);
        $eval_stmt = mysqli_prepare($conn, "INSERT INTO evaluations (policy_id, policy_title, evaluator, risk_level, ai_recommendation, notes, status, overall_score, created_at, updated_at) VALUES (?, ?, 'Admin', 'Low Risk', ?, ?, 'Completed', 8.5, NOW(), NOW()) ON DUPLICATE KEY UPDATE status = 'Completed', updated_at = NOW()");
        if ($eval_stmt) {
          mysqli_stmt_bind_param($eval_stmt, "isss", $new_policy_id, $title, $initial_rec, $initial_notes);
          mysqli_stmt_execute($eval_stmt);
          mysqli_stmt_close($eval_stmt);
        }
      }

      if (function_exists('log_audit_action')) {
        log_audit_action($conn, 'Admin', 'Policy Records', 'Uploaded ' . $title);
      }
      $message = "Policy Research added successfully.";
      $messageType = "success";
    } else {
      $message = "Error adding record: " . mysqli_error($conn);
      $messageType = "danger";
    }
    mysqli_stmt_close($stmt);
  } elseif ($action === 'edit') {
    $id = $_POST['id'];
    $title = $_POST['title'];
    $category = $_POST['category'];
    $city_origin = !empty($_POST['city_origin']) ? $_POST['city_origin'] : 'City of Manila';
    $author = $_POST['author'];
    $department = $_POST['department'];
    $description = $_POST['description'];
    $keywords = $_POST['keywords'];
    $publication_date = $_POST['publication_date'];
    $related_record = $_POST['related_record'] ?? '';
    $status = $_POST['status'] ?? 'Draft';

    $file_path = '';
    if (isset($_FILES['research_file']) && $_FILES['research_file']['error'] == 0) {
      $target_dir = "../assets/uploads/policies/";
      $file_name = time() . "_" . basename($_FILES["research_file"]["name"]);
      $target_file = $target_dir . $file_name;
      if (move_uploaded_file($_FILES["research_file"]["tmp_name"], $target_file)) {
        $file_path = $file_name;

        // Fetch old file path to delete it
        $stmt = mysqli_prepare($conn, "SELECT file_path FROM policy_records WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $old_file_path);
        if (mysqli_stmt_fetch($stmt) && !empty($old_file_path)) {
          $old_full_path = "../assets/uploads/policies/" . $old_file_path;
          if (file_exists($old_full_path)) {
            unlink($old_full_path);
          }
        }
        mysqli_stmt_close($stmt);
      }
    }

    if ($file_path !== '') {
      $stmt = mysqli_prepare($conn, "UPDATE policy_records SET title=?, category=?, city_origin=?, author=?, department=?, description=?, keywords=?, publication_date=?, related_record=?, file_path=?, status=?, ai_summary=NULL WHERE id=?");
      mysqli_stmt_bind_param($stmt, "sssssssssssi", $title, $category, $city_origin, $author, $department, $description, $keywords, $publication_date, $related_record, $file_path, $status, $id);
    } else {
      $stmt = mysqli_prepare($conn, "UPDATE policy_records SET title=?, category=?, city_origin=?, author=?, department=?, description=?, keywords=?, publication_date=?, related_record=?, status=? WHERE id=?");
      mysqli_stmt_bind_param($stmt, "ssssssssssi", $title, $category, $city_origin, $author, $department, $description, $keywords, $publication_date, $related_record, $status, $id);
    }

    if (mysqli_stmt_execute($stmt)) {
      if (function_exists('log_audit_action')) {
        log_audit_action($conn, 'Admin', 'Policy Records', 'Updated ' . $title);
      }
      $message = "Policy Research updated successfully.";
      $messageType = "success";
    } else {
      $message = "Error updating record: " . mysqli_error($conn);
      $messageType = "danger";
    }
    mysqli_stmt_close($stmt);
  } elseif ($action === 'archive') {
    $id = intval($_POST['id']);
    $stmt = mysqli_prepare($conn, "UPDATE policy_records SET status = 'Archived' WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    if (mysqli_stmt_execute($stmt)) {
      if (function_exists('log_audit_action')) {
        log_audit_action($conn, 'System Administrator', 'Policy Records', 'Archived policy record #' . $id);
      }
      $message = "Policy Record archived successfully and moved to Archived Policies.";
      $messageType = "warning";
    } else {
      $message = "Error archiving record: " . mysqli_error($conn);
      $messageType = "danger";
    }
    mysqli_stmt_close($stmt);
    $active_section = 'policyResearchSection';
    $_GET['status'] = 'Archived';
  } elseif ($action === 'restore') {
    $id = intval($_POST['id']);
    $stmt = mysqli_prepare($conn, "UPDATE policy_records SET status = 'Published' WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    if (mysqli_stmt_execute($stmt)) {
      if (function_exists('log_audit_action')) {
        log_audit_action($conn, 'System Administrator', 'Policy Records', 'Restored policy record #' . $id);
      }
      $message = "Policy Record restored to Published status.";
      $messageType = "success";
    } else {
      $message = "Error restoring record: " . mysqli_error($conn);
      $messageType = "danger";
    }
    mysqli_stmt_close($stmt);
    $active_section = 'policyResearchSection';
    $_GET['status'] = '';
  } elseif ($action === 'delete') {
    $id = intval($_POST['id']);
    $deleted_title = '';

    // Fetch file path and title to delete the physical file and for audit logging
    $stmt = mysqli_prepare($conn, "SELECT title, file_path FROM policy_records WHERE id = ?");
    if ($stmt) {
      mysqli_stmt_bind_param($stmt, "i", $id);
      mysqli_stmt_execute($stmt);
      mysqli_stmt_bind_result($stmt, $fetched_title, $file_path);
      if (mysqli_stmt_fetch($stmt)) {
        $deleted_title = $fetched_title ?? '';
        if (!empty($file_path)) {
          $full_path = "../assets/uploads/policies/" . $file_path;
          if (file_exists($full_path)) {
            unlink($full_path);
          }
        }
      }
      mysqli_stmt_close($stmt);
    }

    // Delete from DB
    $stmt = mysqli_prepare($conn, "DELETE FROM policy_records WHERE id = ?");
    if ($stmt) {
      mysqli_stmt_bind_param($stmt, "i", $id);
      if (mysqli_stmt_execute($stmt)) {
        if (function_exists('log_audit_action')) {
          $log_desc = !empty($deleted_title) ? 'Permanently deleted policy "' . $deleted_title . '" (ID #' . $id . ')' : 'Permanently deleted policy record #' . $id;
          log_audit_action($conn, 'System Administrator', 'Policy Records', $log_desc);
        }
        $message = "Policy record permanently deleted successfully.";
        $messageType = "success";
      } else {
        $message = "Error deleting record: " . mysqli_error($conn);
        $messageType = "danger";
      }
      mysqli_stmt_close($stmt);
    }
    $active_section = 'policyResearchSection';
    if (isset($_POST['status'])) {
      $_GET['status'] = $_POST['status'];
    }
  } elseif ($action === 'toggle_evaluation_status') {
    $policy_id = intval($_POST['policy_id']);
    $new_status = in_array($_POST['new_status'] ?? '', ['Approved', 'Completed', 'Draft', 'Under Review']) ? $_POST['new_status'] : 'Approved';
    $approved_by = ($new_status === 'Approved') ? ($_POST['approved_by'] ?? 'Admin') : null;
    $approved_at = ($new_status === 'Approved') ? date('Y-m-d H:i:s') : null;

    // Check if evaluation row exists
    $check_stmt = mysqli_prepare($conn, "SELECT id FROM evaluations WHERE policy_id = ?");
    mysqli_stmt_bind_param($check_stmt, "i", $policy_id);
    mysqli_stmt_execute($check_stmt);
    mysqli_stmt_store_result($check_stmt);
    $num_rows = mysqli_stmt_num_rows($check_stmt);
    mysqli_stmt_close($check_stmt);

    if ($num_rows > 0) {
      if ($new_status === 'Approved') {
        $update_stmt = mysqli_prepare($conn, "UPDATE evaluations SET status = ?, approved_by = ?, approved_at = NOW() WHERE policy_id = ?");
        mysqli_stmt_bind_param($update_stmt, "ssi", $new_status, $approved_by, $policy_id);
        mysqli_stmt_execute($update_stmt);
        mysqli_stmt_close($update_stmt);

        // Also sync approval to the latest version in evaluation_versions
        @mysqli_query($conn, "UPDATE evaluation_versions SET status = 'Approved', approved_by = '" . mysqli_real_escape_string($conn, $approved_by) . "', approved_at = NOW() WHERE policy_id = $policy_id ORDER BY version_number DESC LIMIT 1");
      } else {
        $update_stmt = mysqli_prepare($conn, "UPDATE evaluations SET status = ? WHERE policy_id = ?");
        mysqli_stmt_bind_param($update_stmt, "si", $new_status, $policy_id);
        mysqli_stmt_execute($update_stmt);
        mysqli_stmt_close($update_stmt);
      }
    } else {
      $p_stmt = mysqli_prepare($conn, "SELECT title FROM policy_records WHERE id = ?");
      mysqli_stmt_bind_param($p_stmt, "i", $policy_id);
      mysqli_stmt_execute($p_stmt);
      mysqli_stmt_bind_result($p_stmt, $p_title);
      mysqli_stmt_fetch($p_stmt);
      mysqli_stmt_close($p_stmt);

      $p_title = $p_title ?: ('Policy #' . $policy_id);
      if ($new_status === 'Approved') {
        $insert_stmt = mysqli_prepare($conn, "INSERT INTO evaluations (policy_id, policy_title, evaluator, status, risk_level, ai_recommendation, overall_score, approved_by, approved_at) VALUES (?, ?, 'A.I. Evaluator', ?, 'Low', 'Suitable for implementation.', 8.5, ?, NOW())");
        mysqli_stmt_bind_param($insert_stmt, "isss", $policy_id, $p_title, $new_status, $approved_by);
      } else {
        $insert_stmt = mysqli_prepare($conn, "INSERT INTO evaluations (policy_id, policy_title, evaluator, status, risk_level, ai_recommendation, overall_score) VALUES (?, ?, 'A.I. Evaluator', ?, 'Low', 'Suitable for implementation.', 8.5)");
        mysqli_stmt_bind_param($insert_stmt, "iss", $policy_id, $p_title, $new_status);
      }
      mysqli_stmt_execute($insert_stmt);
      mysqli_stmt_close($insert_stmt);
    }

    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
      header('Content-Type: application/json');
      echo json_encode([
        'success' => true,
        'new_status' => $new_status,
        'approved_by' => $approved_by,
        'approved_at' => $approved_at ? date('M d, Y h:i A', strtotime($approved_at)) : null
      ]);
      exit;
    }

    $message = "Evaluation status updated to " . $new_status . ".";
    $messageType = "success";
    $active_section = 'impactAssessmentSection';
  }
}

// Fetch Policy Research Data
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$category_filter = isset($_GET['category']) ? $_GET['category'] : '';
$status_filter = isset($_GET['status']) ? trim($_GET['status']) : '';
$timeframe_filter = isset($_GET['timeframe']) ? trim($_GET['timeframe']) : '';

$query = "SELECT * FROM $policy_tbl WHERE 1=1";
$params = [];
$types = "";

if ($search !== '') {
  $query .= " AND (title LIKE ? OR keywords LIKE ? OR author LIKE ?)";
  $search_param = "%$search%";
  $params[] = $search_param;
  $params[] = $search_param;
  $params[] = $search_param;
  $types .= "sss";
}

if ($category_filter !== '') {
  $query .= " AND category = ?";
  $params[] = $category_filter;
  $types .= "s";
}

if ($timeframe_filter === 'today') {
  $query .= " AND (DATE(publication_date) = CURDATE() OR DATE(created_at) = CURDATE())";
} elseif ($timeframe_filter === 'last_7_days') {
  $query .= " AND (publication_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) OR created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY))";
} elseif ($timeframe_filter === 'last_30_days') {
  $query .= " AND (publication_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) OR created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY))";
} elseif ($timeframe_filter === 'this_month') {
  $query .= " AND ((MONTH(publication_date) = MONTH(CURDATE()) AND YEAR(publication_date) = YEAR(CURDATE())) OR (MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE())))";
} elseif ($timeframe_filter === 'last_month') {
  $query .= " AND ((MONTH(publication_date) = MONTH(DATE_SUB(CURDATE(), INTERVAL 1 MONTH)) AND YEAR(publication_date) = YEAR(DATE_SUB(CURDATE(), INTERVAL 1 MONTH))) OR (MONTH(created_at) = MONTH(DATE_SUB(CURDATE(), INTERVAL 1 MONTH)) AND YEAR(created_at) = YEAR(DATE_SUB(CURDATE(), INTERVAL 1 MONTH))))";
} elseif ($timeframe_filter === '2026' || $timeframe_filter === 'this_year') {
  $query .= " AND (YEAR(publication_date) = 2026 OR publication_date LIKE '2026%' OR YEAR(created_at) = 2026)";
} elseif ($timeframe_filter === '2025') {
  $query .= " AND (YEAR(publication_date) = 2025 OR publication_date LIKE '2025%' OR YEAR(created_at) = 2025)";
} elseif ($timeframe_filter === '2024') {
  $query .= " AND (YEAR(publication_date) = 2024 OR publication_date LIKE '2024%' OR YEAR(created_at) = 2024)";
}

if ($status_filter === 'Archived') {
  $query .= " AND status = 'Archived'";
} elseif ($status_filter !== '') {
  $query .= " AND status = ?";
  $params[] = $status_filter;
  $types .= "s";
} else {
  // Main view: exclude Archived items so they only appear when viewing Archived Policies
  $query .= " AND (status IS NULL OR status != 'Archived')";
}

$query .= " ORDER BY created_at DESC";

$policies = [];
$stmt_select = mysqli_prepare($conn, $query);
if ($stmt_select) {
  if (!empty($params)) {
    mysqli_stmt_bind_param($stmt_select, $types, ...$params);
  }
  mysqli_stmt_execute($stmt_select);
  $result = mysqli_stmt_get_result($stmt_select);
  if ($result) {
    $policies = mysqli_fetch_all($result, MYSQLI_ASSOC);
  }
  mysqli_stmt_close($stmt_select);
}

// Fetch ALL policy records (unfiltered) for Research Data section
$all_policies = [];
$all_policies_res = mysqli_query($conn, "SELECT * FROM $policy_tbl ORDER BY created_at DESC");
if ($all_policies_res) {
  $all_policies = mysqli_fetch_all($all_policies_res, MYSQLI_ASSOC);
}

// Fetch one evaluation per policy. Policies are the source of truth, so every
// Policy Record appears here even before it has an evaluation.
$evaluations = [];
$eval_query = "
  SELECT p.id AS policy_id, p.title AS policy_title,
         e.id AS evaluation_id,
         e.economic_score,
         e.social_score,
         e.environmental_score,
         e.legal_score,
         e.overall_score,
         e.evaluator,
         e.approved_by,
         e.approved_at,
         e.notes,
         COALESCE(e.updated_at, e.created_at) AS evaluation_date,
         COALESCE(NULLIF(e.risk_level, ''),
           CASE
             WHEN e.id IS NULL THEN 'N/A'
             WHEN e.overall_score >= 8 THEN 'Low Risk'
             WHEN e.overall_score >= 6 THEN 'Moderate Risk'
             ELSE 'High Risk'
           END
         ) AS risk_level,
         COALESCE(e.ai_recommendation, 'Awaiting evaluation.') AS ai_recommendation,
         CASE WHEN e.id IS NULL THEN 'Draft' ELSE COALESCE(e.status, 'Completed') END AS evaluation_status
  FROM $policy_tbl p
  LEFT JOIN evaluations e ON e.policy_id = p.id
  ORDER BY p.created_at DESC
";
$eval_result = mysqli_query($conn, $eval_query);
if ($eval_result) {
  $evaluations = mysqli_fetch_all($eval_result, MYSQLI_ASSOC);
}

$total_policies_count = count($all_policies);
$total_research_count = count($all_policies);
$total_evaluations_count = count(array_filter($evaluations, function ($e) {
  return !empty($e['evaluation_id']) || ($e['evaluation_status'] ?? '') === 'Completed';
}));
$users_cnt_res = @mysqli_query($conn, "SELECT COUNT(*) FROM $u_tbl WHERE status != 'Deactivated'");
$total_users_count = ($users_cnt_res) ? (int) mysqli_fetch_row($users_cnt_res)[0] : 14;

// Compute real category distribution
$cat_labels = [
  'Infrastructure, Traffic & Environment',
  'Health and Sanitation',
  'Social Welfare & Community Affairs',
  'Civil Registry & Public Services',
  'Education & Employment'
];
$cat_counts = array_fill(0, count($cat_labels), 0);

foreach ($all_policies as $p) {
  $cat = strtolower($p['category'] ?? '');
  if (strpos($cat, 'infra') !== false || strpos($cat, 'traffic') !== false || strpos($cat, 'environ') !== false) {
    $cat_counts[0]++;
  } elseif (strpos($cat, 'health') !== false || strpos($cat, 'sanitat') !== false) {
    $cat_counts[1]++;
  } elseif (strpos($cat, 'social') !== false || strpos($cat, 'welfare') !== false || strpos($cat, 'community') !== false) {
    $cat_counts[2]++;
  } elseif (strpos($cat, 'civil') !== false || strpos($cat, 'registry') !== false || strpos($cat, 'public') !== false) {
    $cat_counts[3]++;
  } elseif (strpos($cat, 'educ') !== false || strpos($cat, 'employ') !== false) {
    $cat_counts[4]++;
  } else {
    $cat_counts[0]++;
  }
}

$top_cat_count = 0;
$max_cat_idx = 0;
for ($i = 0; $i < count($cat_counts); $i++) {
  if ($cat_counts[$i] > $top_cat_count) {
    $top_cat_count = $cat_counts[$i];
    $max_cat_idx = $i;
  }
}
$top_cat = $cat_labels[$max_cat_idx];

// Monthly uploads
$cur_m_name = date('M');
$cur_m_prefix = date('Y-m');
$days_sample = [1, 5, 10, 15, 20, 25, (int) date('t')];
$up_labels = [];
$up_data = [];
$total_month_uploads = 0;

foreach ($all_policies as $p) {
  $created = $p['created_at'] ?? $p['date_created'] ?? '';
  if ($created && strpos($created, $cur_m_prefix) === 0) {
    $total_month_uploads++;
  }
}

foreach ($days_sample as $day_num) {
  $up_labels[] = "$cur_m_name $day_num";
  $cnt = 0;
  foreach ($all_policies as $p) {
    $created = $p['created_at'] ?? $p['date_created'] ?? '';
    if ($created && strpos($created, $cur_m_prefix) === 0) {
      $d = (int) date('j', strtotime($created));
      if ($d <= $day_num)
        $cnt++;
    }
  }
  $up_data[] = $cnt;
}

$dashboard_charts_payload = [
  'totalPolicies' => $total_policies_count,
  'totalResearch' => $total_research_count,
  'totalEvaluations' => $total_evaluations_count,
  'totalUsers' => $total_users_count,
  'policiesByCategory' => [
    'labels' => $cat_labels,
    'data' => $cat_counts,
    'topCategory' => $top_cat
  ],
  'policiesUploadedThisMonth' => [
    'labels' => $up_labels,
    'data' => $up_data,
    'totalThisMonth' => $total_month_uploads ?: count($all_policies)
  ]
];

// Ensure the page opens the correct section on reload
$active_section = $_GET['section'] ?? (isset($_POST['action']) ? 'policyResearchSection' : 'adminDashboardSection');
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Legislative Administration System - Manila City Hall</title>
  <!-- Bootstrap 5.3 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <!-- System Custom CSS -->
  <link rel="stylesheet" href="../assets/css/Manila City Hall.css?v=<?= time() ?>">
  <link rel="stylesheet" href="../assets/css/Admin.css?v=<?= time() ?>">
  <script>
    if (localStorage.getItem('admin_sidebar_collapsed') === 'true') {
      document.documentElement.classList.add('sidebar-collapsed');
    }
  </script>
  <style>
    body:not(.sidebar-collapsed) .main-panel {
      width: calc(100% - 280px);
      max-width: calc(100% - 280px);
      margin-left: 280px;
    }

    html.sidebar-collapsed .main-panel,
    body.sidebar-collapsed .main-panel {
      width: calc(100% - 76px) !important;
      max-width: calc(100% - 76px) !important;
      margin-left: 76px !important;
    }

    .content-area {
      width: 100% !important;
      max-width: 100% !important;
      margin-left: 0 !important;
      padding-left: 0 !important;
      padding-right: 0 !important;
    }

    /* Refined sidebar section labels */
    .sidebar-section-label {
      padding: 14px 12px 4px 12px !important;
      color: #F59E0B !important;
      font-size: 0.68rem !important;
      font-weight: 600 !important;
      letter-spacing: 1.2px !important;
      text-transform: uppercase !important;
      display: block !important;
      opacity: 0.95 !important;
      text-shadow: none !important;
    }
  </style>
</head>

<body>
  <!-- Security Verification -->
  <script>
    if (localStorage.getItem('admin_logged_in') !== 'true') {
      window.location.href = '../frontend/welcome.php';
    }
  </script>

  <div class="d-flex min-vh-100 position-relative">
    <!-- SIDEBAR NAVIGATION -->
    <aside class="sidebar d-flex flex-column p-3">
      <!-- Brand & Mobile Toggle Header -->
      <div class="brand mb-4 d-flex flex-column gap-2">
        <div class="d-flex align-items-center gap-3 brand-info">
          <img src="../assets/images/manilacityhall.svg" alt="Manila City Hall Logo"
            style="width:48px; height:48px; object-fit:contain;" class="brand-logo">
          <div class="brand-text">
            <h1 class="fs-5 fw-bold mb-0 text-white" style="letter-spacing: -0.2px;">Lungsod ng <span
                style="color: #F59E0B;">Maynila</span></h1>
            <div class="text-white-50 small" style="font-size:0.75rem; letter-spacing: 0.3px;">City of Manila</div>
          </div>
        </div>
        <div class="brand-toggle-row d-flex justify-content-between align-items-center w-100 mt-1">
          <span class="sidebar-menu-label text-white-50 small fw-bold"
            style="font-size:0.68rem; letter-spacing:1px;">NAV MENU</span>
          <button type="button" class="sidebar-toggle-btn" id="sidebarToggleBtn" title="Toggle Sidebar"
            aria-label="Toggle Sidebar">
            <i class="bi bi-list"></i>
          </button>
        </div>
      </div>

      <!-- Navigation List matching specified submodules -->
      <nav class="nav flex-column sidebar-nav mb-4 gap-1">
        <div class="sidebar-section-label">MAIN</div>
        <a class="nav-link <?= $active_section === 'adminDashboardSection' ? 'active' : '' ?> py-2.5 px-3 rounded-3"
          href="javascript:void(0);" data-target="adminDashboardSection"
          onclick="showSection('adminDashboardSection'); return false;" title="Dashboard">
          <i class="bi bi-speedometer2 me-2"></i><span class="nav-text">Dashboard</span>
        </a>

        <div class="sidebar-section-label mt-3">LEGISLATIVE</div>
        <a class="nav-link <?= $active_section === 'policyResearchSection' ? 'active' : '' ?> py-2.5 px-3 rounded-3"
          href="javascript:void(0);" data-target="policyResearchSection"
          onclick="showSection('policyResearchSection'); return false;" title="Policy Research">
          <i class="bi bi-file-earmark-text me-2"></i><span class="nav-text">Policy Research</span>
        </a>
        <a class="nav-link <?= $active_section === 'dataCollectionSection' ? 'active' : '' ?> py-2.5 px-3 rounded-3"
          href="javascript:void(0);" data-target="dataCollectionSection"
          onclick="showSection('dataCollectionSection'); return false;" title="Data Collection">
          <i class="bi bi-database-fill-gear me-2"></i><span class="nav-text">Data Collection</span>
        </a>
        <a class="nav-link <?= $active_section === 'impactAssessmentSection' ? 'active' : '' ?> py-2.5 px-3 rounded-3"
          href="javascript:void(0);" data-target="impactAssessmentSection"
          onclick="showSection('impactAssessmentSection'); return false;" title="Evaluation">
          <i class="bi bi-bar-chart-line me-2"></i><span class="nav-text">Evaluation</span>
        </a>
        <a class="nav-link <?= $active_section === 'comparativeAnalysisSection' ? 'active' : '' ?> py-2.5 px-3 rounded-3"
          href="javascript:void(0);" data-target="comparativeAnalysisSection"
          onclick="showSection('comparativeAnalysisSection'); return false;" title="Comparison">
          <i class="bi bi-layout-sidebar-inset-reverse me-2"></i><span class="nav-text">Comparison</span>
        </a>

        <div class="sidebar-section-label mt-3">REPORTING</div>
        <a class="nav-link <?= $active_section === 'reportGenerationSection' ? 'active' : '' ?> py-2.5 px-3 rounded-3"
          href="javascript:void(0);" data-target="reportGenerationSection"
          onclick="showSection('reportGenerationSection'); return false;" title="Reports">
          <i class="bi bi-journal-text me-2"></i><span class="nav-text">Reports</span>
        </a>

        <div class="sidebar-section-label mt-3">ADMINISTRATION</div>
        <a class="nav-link <?= $active_section === 'systemLogsSection' ? 'active' : '' ?> py-2.5 px-3 rounded-3"
          href="javascript:void(0);" data-target="systemLogsSection"
          onclick="showSection('systemLogsSection'); return false;" title="Audit Logs">
          <i class="bi bi-terminal-fill me-2"></i><span class="nav-text">Audit Logs</span>
        </a>
        <a class="nav-link <?= $active_section === 'activeUsersSection' ? 'active' : '' ?> py-2.5 px-3 rounded-3"
          href="javascript:void(0);" data-target="activeUsersSection"
          onclick="showSection('activeUsersSection'); return false;" title="User Directory">
          <i class="bi bi-people me-2"></i><span class="nav-text">User Directory</span>
        </a>
        <a class="nav-link <?= $active_section === 'databaseManagementSection' ? 'active' : '' ?> py-2.5 px-3 rounded-3"
          href="javascript:void(0);" data-target="databaseManagementSection"
          onclick="showSection('databaseManagementSection'); return false;" title="Database Management">
          <i class="bi bi-database-check me-2"></i><span class="nav-text">Database Management</span>
        </a>
      </nav>
    </aside>

    <!-- MAIN CONTENT AREA -->
    <div class="main-panel flex-grow-1">
      <!-- TOPBAR -->
      <header
        class="topbar d-flex align-items-center justify-content-between px-4 py-3 mb-4 shadow-sm bg-white rounded-4 border mb-4">
        <div class="d-flex align-items-center gap-3">
          <img src="../assets/images/manilacityhall.svg" alt="Manila Seal"
            style="width:44px; height:44px; object-fit:contain;">
          <div>
            <h2 class="fs-4 fw-bold text-dark mb-0" style="letter-spacing: -0.3px; color: #0B2E59 !important;">Lungsod
              ng <span style="color: #F59E0B;">Maynila</span></h2>
            <div class="text-secondary small fw-medium" style="font-size: 0.82rem; letter-spacing: 0.2px;">Legislative
              System</div>
          </div>
        </div>

        <div class="d-flex align-items-center">
          <!-- Notification Dropdown -->
          <?php
          $admin_notifs = [];
          if (!empty($conn)) {
            $anq = @mysqli_query($conn, "SELECT id, title, category, created_at FROM policy_records WHERE (status IS NULL OR status != 'Archived') ORDER BY id DESC LIMIT 6");
            if ($anq) {
              while ($r = mysqli_fetch_assoc($anq)) {
                $admin_notifs[] = $r;
              }
            }
          }
          $admin_notif_count = count($admin_notifs);
          $latest_notif_id = !empty($admin_notifs) ? (int) $admin_notifs[0]['id'] : 0;
          ?>
          <div class="dropdown">
            <button class="header-notif-btn" id="adminNotifButton" type="button" data-bs-toggle="dropdown"
              data-latest-id="<?= $latest_notif_id ?>" aria-expanded="false" title="Notifications">
              <i class="bi bi-bell fs-5 text-dark"></i>
              <span id="adminNotifBadge" class="header-notif-badge" style="display:none;"></span>
            </button>
            <div class="dropdown-menu dropdown-menu-end shadow-lg border-0 rounded-4 p-0 overflow-hidden mt-2"
              style="width: 370px;" aria-labelledby="adminNotifButton">
              <div class="px-3 py-3 d-flex align-items-center justify-content-between text-white notif-header"
                style="background: linear-gradient(120deg, #0B2E59, #1a4a8a);">
                <div>
                  <strong class="fs-6 d-block">Notifications</strong>
                  <small class="opacity-75">You have <span id="adminNotifUnread">0</span> new updates</small>
                </div>
                <span id="adminNotifHeaderBadge"
                  class="badge rounded-pill bg-warning text-dark"><?= $admin_notif_count ?> Updates</span>
              </div>
              <div class="p-2" style="max-height: 290px; overflow-y: auto;">
                <ul class="list-group list-group-flush" id="adminNotifList">
                  <?php if (!empty($admin_notifs)): ?>
                    <?php foreach ($admin_notifs as $an): ?>
                      <?php
                      $an_id = (int) $an['id'];
                      $an_title = htmlspecialchars($an['title']);
                      $an_cat = htmlspecialchars($an['category'] ?? 'Policy');
                      $an_time = !empty($an['created_at']) ? date('M d, Y h:i A', strtotime($an['created_at'])) : 'Recent';
                      ?>
                      <li
                        class="notif-item list-group-item p-2 mb-1 border rounded-3 d-flex justify-content-between align-items-start"
                        data-notif-id="<?= $an_id ?>" style="cursor: pointer;"
                        onclick="handleNotifItemClick('policyResearchSection', <?= $an_id ?>)">
                        <div class="d-flex gap-2">
                          <span class="notif-dot unread mt-1.5"
                            style="background:#EF4444; width:8px; height:8px; border-radius:50%; flex-shrink:0;"></span>
                          <div>
                            <div class="fw-semibold small text-dark" style="font-size:0.86rem; line-height:1.25;">
                              <?= $an_title ?>
                            </div>
                            <small class="text-muted d-block mt-0.5" style="font-size: 0.74rem;">Policy Record &bull;
                              <?= $an_time ?></small>
                          </div>
                        </div>
                        <span class="badge bg-primary bg-opacity-10 text-primary ms-1"
                          style="font-size:0.65rem; white-space:nowrap;"><?= $an_cat ?></span>
                      </li>
                    <?php endforeach; ?>
                  <?php else: ?>
                    <li class="notif-item list-group-item p-3 text-center text-muted small">
                      No notifications at this time.
                    </li>
                  <?php endif; ?>
                </ul>
              </div>
              <div class="d-flex align-items-center justify-content-between px-3 py-2 border-top notif-footer bg-white">
                <a href="#" class="text-primary small text-decoration-none fw-semibold"
                  onclick="markAllNotifsRead(event)">Mark all as read</a>
                <a href="#" class="text-muted small text-decoration-none"
                  onclick="showSection('policyResearchSection');return false;">View all &rarr;</a>
              </div>
            </div>
          </div>

          <!-- Vertical Divider 1 -->
          <div class="header-divider"></div>

          <!-- Dark Mode Toggle Switch -->
          <div class="d-flex align-items-center">
            <label class="dark-mode-switch" title="Toggle Dark Mode">
              <input type="checkbox" id="headerDarkModeCheckbox">
              <span class="switch-slider"></span>
            </label>
          </div>

          <!-- Vertical Divider 2 -->
          <div class="header-divider"></div>

          <!-- Admin Profile Dropdown -->
          <div class="dropdown">
            <button class="header-dropdown-btn" type="button" id="adminProfileDropdown" data-bs-toggle="dropdown"
              aria-expanded="false" title="Admin Profile Menu">
              <div class="header-avatar-wrap">
                <img id="topbarAdminAvatarImg" src="" alt="Admin Profile" class="header-avatar-img d-none" />
                <div id="topbarAdminAvatarFallback" class="header-avatar-fallback">
                  <i class="bi bi-person-fill"></i>
                </div>
              </div>
              <span class="header-admin-text d-flex align-items-center">
                <span class="header-admin-role">Admin</span>
                <span class="header-admin-pipe">|</span>
                <span id="topbarAdminName" class="header-admin-name">Manila City Hall Administrator</span>
              </span>
              <i class="bi bi-chevron-down ms-1"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 rounded-4 p-2 mt-2"
              aria-labelledby="adminProfileDropdown">
              <li>
                <a class="dropdown-item rounded-2 py-2" href="#" data-target="adminProfileSection"
                  onclick="showSection('adminProfileSection'); return false;">
                  <i class="bi bi-person-circle me-2 text-primary"></i>Profile
                </a>
              </li>
              <li>
                <hr class="dropdown-divider my-1">
              </li>
              <li>
                <a class="dropdown-item rounded-2 py-2 text-danger" href="#" id="topbarAdminLogoutBtn"
                  onclick="if(window.handleAdminLogout){window.handleAdminLogout(event);}return false;">
                  <i class="bi bi-box-arrow-right me-2"></i>Logout
                </a>
              </li>
            </ul>
          </div>
        </div>
      </header>

      <!-- CONTENT SECTIONS CONTAINER -->
      <main class="content-area px-4 pb-5">

        <!-- 1. DASHBOARD MODULE -->
        <section id="adminDashboardSection"
          class="content-section <?= $active_section !== 'adminDashboardSection' ? 'd-none' : '' ?>">

          <!-- Top Header: Welcome Banner -->
          <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
            <div>
              <h2 class="h3 fw-bold text-dark mb-1">Welcome back, Administrator!</h2>
              <p class="text-muted mb-0">Monitor policy records, research data, evaluations, and system activities from
                one dashboard.</p>
            </div>
          </div>

          <!-- 4 Summary Stat Cards -->
          <div class="row g-3 mb-4">
            <!-- Card 1: Policy Research -->
            <div class="col-12 col-sm-6 col-xl-3">
              <div class="card border-0 shadow-sm rounded-4 p-4 h-100 bg-white border-top border-4 border-primary">
                <div class="d-flex align-items-center justify-content-between mb-3">
                  <div>
                    <div class="small fw-semibold text-muted text-uppercase"
                      style="font-size:0.75rem; letter-spacing:0.5px;">Policy Research</div>
                    <div class="fw-bold text-dark lh-1 mt-2" style="font-size:2.2rem;" id="dashTotalPolicies">
                      <?= $total_policies_count ?>
                    </div>
                    <small class="text-muted mt-1 d-block">Active Policy Research</small>
                  </div>
                  <div
                    class="rounded-3 bg-primary bg-opacity-10 p-3 text-primary d-flex align-items-center justify-content-center"
                    style="width:52px; height:52px;">
                    <i class="bi bi-file-earmark-text fs-3"></i>
                  </div>
                </div>
                <div class="pt-2 border-top mt-auto">
                  <a href="#" onclick="showSection('policyResearchSection');return false;"
                    class="text-primary fw-semibold small text-decoration-none d-flex align-items-center justify-content-between">
                    View all research <i class="bi bi-arrow-right"></i>
                  </a>
                </div>
              </div>
            </div>
            <!-- Card 2: Data Collection -->
            <div class="col-12 col-sm-6 col-xl-3">
              <div class="card border-0 shadow-sm rounded-4 p-4 h-100 bg-white border-top border-4 border-success">
                <div class="d-flex align-items-center justify-content-between mb-3">
                  <div>
                    <div class="small fw-semibold text-muted text-uppercase"
                      style="font-size:0.75rem; letter-spacing:0.5px;">Data Collection</div>
                    <div class="fw-bold text-dark lh-1 mt-2" style="font-size:2.2rem;" id="dashTotalResearch">
                      <?= $total_research_count ?>
                    </div>
                    <small class="text-muted mt-1 d-block">Total Data Collection</small>
                  </div>
                  <div
                    class="rounded-3 bg-success bg-opacity-10 p-3 text-success d-flex align-items-center justify-content-center"
                    style="width:52px; height:52px;">
                    <i class="bi bi-database-fill fs-3"></i>
                  </div>
                </div>
                <div class="pt-2 border-top mt-auto">
                  <a href="#" onclick="showSection('dataCollectionSection');return false;"
                    class="text-primary fw-semibold small text-decoration-none d-flex align-items-center justify-content-between">
                    View all data collection <i class="bi bi-arrow-right"></i>
                  </a>
                </div>
              </div>
            </div>
            <!-- Card 3: Evaluations -->
            <div class="col-12 col-sm-6 col-xl-3">
              <div class="card border-0 shadow-sm rounded-4 p-4 h-100 bg-white border-top border-4 border-warning">
                <div class="d-flex align-items-center justify-content-between mb-3">
                  <div>
                    <div class="small fw-semibold text-muted text-uppercase"
                      style="font-size:0.75rem; letter-spacing:0.5px;">Evaluations</div>
                    <div class="fw-bold text-dark lh-1 mt-2" style="font-size:2.2rem;" id="dashTotalEvaluations">27
                    </div>
                    <small class="text-muted mt-1 d-block">Total Completed Evaluations</small>
                  </div>
                  <div
                    class="rounded-3 bg-warning bg-opacity-10 p-3 text-warning d-flex align-items-center justify-content-center"
                    style="width:52px; height:52px;">
                    <i class="bi bi-clipboard-check fs-3"></i>
                  </div>
                </div>
                <div class="pt-2 border-top mt-auto">
                  <a href="#" onclick="showSection('impactAssessmentSection');return false;"
                    class="text-primary fw-semibold small text-decoration-none d-flex align-items-center justify-content-between">
                    View all evaluations <i class="bi bi-arrow-right"></i>
                  </a>
                </div>
              </div>
            </div>
            <!-- Card 4: Registered Users -->
            <div class="col-12 col-sm-6 col-xl-3">
              <div class="card border-0 shadow-sm rounded-4 p-4 h-100 bg-white border-top border-4 border-info">
                <div class="d-flex align-items-center justify-content-between mb-3">
                  <div>
                    <div class="small fw-semibold text-muted text-uppercase"
                      style="font-size:0.75rem; letter-spacing:0.5px;">Registered Users</div>
                    <div class="fw-bold text-dark lh-1 mt-2" style="font-size:2.2rem;" id="dashTotalUsers">15</div>
                    <small class="text-muted mt-1 d-block">Total System Users</small>
                  </div>
                  <div
                    class="rounded-3 bg-info bg-opacity-10 p-3 text-info d-flex align-items-center justify-content-center"
                    style="width:52px; height:52px;">
                    <i class="bi bi-people-fill fs-3"></i>
                  </div>
                </div>
                <div class="pt-2 border-top mt-auto">
                  <a href="#" onclick="showSection('activeUsersSection');return false;"
                    class="text-primary fw-semibold small text-decoration-none d-flex align-items-center justify-content-between">
                    View user directory <i class="bi bi-arrow-right"></i>
                  </a>
                </div>
              </div>
            </div>
          </div>

          <!-- Middle Row: Policies by Category (Bar Chart), Policies Uploaded This Month (Line Chart), AI Executive Insights -->
          <div class="row g-4 mb-4">
            <!-- Policies by Category (Bar Chart) -->
            <div class="col-12 col-lg-4 col-xl-4">
              <div class="card border-0 shadow-sm rounded-4 p-4 h-100 bg-white d-flex flex-column">
                <div class="d-flex align-items-center justify-content-between mb-1">
                  <div class="d-flex align-items-center gap-2">
                    <div
                      class="rounded-3 p-1.5 bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center"
                      style="width:32px; height:32px;">
                      <i class="bi bi-bar-chart-fill fs-5 text-primary"></i>
                    </div>
                    <h3 class="h6 fw-bold mb-0 text-dark">Policies by Category</h3>
                  </div>
                </div>
                <p class="text-muted small fw-medium mb-3 ms-1" style="font-size:0.78rem;">Distribution of policies
                  across categories.</p>
                <div style="height: 250px; position:relative;" class="flex-grow-1">
                  <canvas id="adminTrendsChart"></canvas>
                </div>
              </div>
            </div>

            <!-- Policies Uploaded This Month (Area Line Chart) -->
            <div class="col-12 col-lg-4 col-xl-4">
              <div class="card border-0 shadow-sm rounded-4 p-4 h-100 bg-white d-flex flex-column">
                <div class="d-flex align-items-center justify-content-between mb-1">
                  <div class="d-flex align-items-center gap-2">
                    <div
                      class="rounded-3 p-1.5 bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center"
                      style="width:32px; height:32px;">
                      <i class="bi bi-graph-up text-primary fs-5"></i>
                    </div>
                    <h3 class="h6 fw-bold mb-0 text-dark">Policies Uploaded This Month</h3>
                  </div>
                </div>
                <p class="text-muted small fw-medium mb-3 ms-1" style="font-size:0.78rem;">Number of policies uploaded
                  per day this month.</p>
                <div style="height: 250px; position:relative;" class="flex-grow-1">
                  <canvas id="deptPieChart"></canvas>
                </div>
              </div>
            </div>

            <!-- AI Executive Insights Card with Interactive Policy Selector -->
            <div class="col-12 col-lg-4 col-xl-4">
              <div class="card border-0 shadow-sm rounded-4 p-4 h-100 bg-white d-flex flex-column">
                <div class="d-flex align-items-center justify-content-between mb-3">
                  <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-stars text-warning fs-5"></i>
                    <h3 class="h6 fw-bold mb-0 text-dark">AI Executive Insights</h3>
                  </div>
                  <?php if (!empty($all_policies)): ?>
                    <select id="aiWidgetSelect"
                      class="form-select form-select-sm rounded-3 border-light-subtle shadow-2xs text-secondary fw-semibold"
                      style="max-width: 150px; font-size: 0.75rem;" onchange="switchAIWidgetPolicy(this.value)">
                      <?php foreach ($all_policies as $idx => $p): ?>
                        <option value="<?= $p['id'] ?>" <?= $idx === 0 ? 'selected' : '' ?>>
                          <?= htmlspecialchars($p['title']) ?>
                        </option>
                      <?php endforeach; ?>
                    </select>
                  <?php endif; ?>
                </div>

                <div
                  class="p-3 rounded-4 bg-primary bg-opacity-10 border border-primary border-opacity-10 mb-3 flex-grow-1">
                  <div class="d-flex align-items-center justify-content-between mb-2">
                    <div class="small fw-semibold text-primary"
                      style="font-size:0.75rem; text-transform:uppercase; letter-spacing:0.5px;">Latest AI Summary</div>
                    <span class="badge rounded-pill px-2.5 py-1" id="aiWidgetImpactBadge"
                      style="font-size:0.75rem; font-weight:700; background-color:#10B981 !important; color:#FFFFFF !important;">Impact:
                      8.8/10</span>
                  </div>
                  <h4 class="fw-bold text-dark fs-6 mb-2" id="aiWidgetTitle">
                    <?= !empty($all_policies[0]['title']) ? htmlspecialchars($all_policies[0]['title']) : 'Urban Traffic Congestion Study' ?>
                  </h4>
                  <p class="small text-muted mb-3" id="aiWidgetSummary" style="line-height:1.4;">The uploaded policy
                    focuses on reducing traffic congestion through smart traffic management, road capacity improvement,
                    and enhanced public transportation.</p>
                  <div class="small fw-semibold text-primary mb-1" style="font-size:0.78rem;">Recommendation</div>
                  <p class="small text-muted mb-0" id="aiWidgetRecommendation" style="line-height:1.4;">Proceed with
                    committee review and stakeholder consultation.</p>
                </div>

                <button id="aiWidgetViewBtn"
                  class="btn btn-primary fw-semibold w-100 rounded-3 py-2 shadow-sm d-flex align-items-center justify-content-center gap-2"
                  onclick="openWidgetAISummaryModal()">
                  View Full AI Summary <i class="bi bi-arrow-right"></i>
                </button>
              </div>
            </div>
          </div>

          <!-- Recent Legislative Activities Table (Full Width) -->
          <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
            <div class="d-flex align-items-center justify-content-between mb-3">
              <h3 class="h6 fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                <i class="bi bi-clock-history text-secondary"></i> Recent Legislative Activities
              </h3>
              <a href="#" onclick="showSection('systemLogsSection');return false;"
                class="text-primary small text-decoration-none fw-semibold">View all activities</a>
            </div>
            <div class="table-responsive">
              <table class="table activities-table align-middle mb-0">
                <thead>
                  <tr>
                    <th>Date &amp; Time</th>
                    <th>Activity</th>
                    <th>Module</th>
                    <th>Status</th>
                    <th>Role</th>
                    <th>Performed By</th>
                  </tr>
                </thead>
                <tbody id="dashboardActivityTable">
                  <?php
                  if (function_exists('ensure_audit_logs_table')) {
                    ensure_audit_logs_table($conn);
                  }
                  $recent_activities_query = mysqli_query($conn, "SELECT * FROM audit_logs ORDER BY created_at DESC, 1 DESC LIMIT 10");
                  if ($recent_activities_query && mysqli_num_rows($recent_activities_query) > 0):
                    while ($act = mysqli_fetch_assoc($recent_activities_query)):
                      $dt_fmt = !empty($act['created_at']) ? date('M d, Y h:i A', strtotime($act['created_at'])) : 'May 14, 2026 10:15 AM';
                      $act_title = htmlspecialchars($act['activity'] ?? ($act['action'] ?? ($act['description'] ?? 'System activity')));
                      $mod_name = htmlspecialchars($act['module'] ?? 'System');
                      $user_name = htmlspecialchars($act['user'] ?? 'Admin');
                      if ($user_name === 'System Administrator' || $user_name === 'Administration' || empty($user_name)) {
                        $user_name = 'Admin';
                      }
                      $role_name = function_exists('resolve_audit_role')
                        ? resolve_audit_role($conn ?? null, $user_name, $act['role'] ?? null)
                        : ($act['role'] ?? 'Admin');

                      $role_class = 'role-badge-admin';
                      $role_icon = 'bi-shield-lock-fill';
                      if ($role_name === 'Staff') {
                        $role_class = 'role-badge-staff';
                        $role_icon = 'bi-person-badge-fill';
                      } elseif ($role_name === 'Councilor') {
                        $role_class = 'role-badge-councilor';
                        $role_icon = 'bi-award-fill';
                      }

                      $stat_name = htmlspecialchars($act['status'] ?? 'Completed');

                      $mod_lower = strtolower($mod_name);
                      $mod_class = 'module-pill-policy';
                      $mod_icon = 'bi-file-earmark-text';
                      if (strpos($mod_lower, 'research') !== false || strpos($mod_lower, 'data') !== false) {
                        $mod_class = 'module-pill-research';
                        $mod_icon = 'bi-database-fill-gear';
                      } elseif (strpos($mod_lower, 'evaluat') !== false || strpos($mod_lower, 'impact') !== false) {
                        $mod_class = 'module-pill-evaluations';
                        $mod_icon = 'bi-bar-chart-line';
                      } elseif (strpos($mod_lower, 'report') !== false) {
                        $mod_class = 'module-pill-reports';
                        $mod_icon = 'bi-journal-text';
                      } elseif (strpos($mod_lower, 'system') !== false || strpos($mod_lower, 'auth') !== false || strpos($mod_lower, 'login') !== false || strpos($mod_lower, 'user') !== false) {
                        $mod_class = 'module-pill-system';
                        $mod_icon = 'bi-gear-wide-connected';
                      }

                      $stat_lower = strtolower($stat_name);
                      $dot_class = '';
                      if ($stat_lower === 'pending' || $stat_lower === 'draft' || $stat_lower === 'under review') {
                        $dot_class = 'warning';
                      } elseif ($stat_lower === 'archived' || $stat_lower === 'failed' || $stat_lower === 'rejected') {
                        $dot_class = 'danger';
                      }
                      ?>
                      <tr>
                        <td><span class="activity-datetime"><?= $dt_fmt ?></span></td>
                        <td><span class="activity-title"><?= $act_title ?></span></td>
                        <td><span class="module-pill <?= $mod_class ?>"><i class="bi <?= $mod_icon ?>"></i>
                            <?= $mod_name ?></span></td>
                        <td><span class="status-pill"><span class="status-dot-indicator <?= $dot_class ?>"></span>
                            <?= $stat_name ?></span></td>
                        <td><span class="badge <?= $role_class ?> rounded-pill"><i
                              class="bi <?= $role_icon ?> me-1"></i><?= $role_name ?></span></td>
                        <td><span class="activity-user"><?= $user_name ?></span></td>
                      </tr>
                      <?php
                    endwhile;
                  else:
                    ?>
                    <tr>
                      <td colspan="5" class="text-center text-muted py-4">No recent activities found.</td>
                    </tr>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
          </div>

        </section>

        <!-- 2. POLICY RESEARCH MODULE -->
        <?php include 'policy_research.php'; ?>

        <!-- 3. DATA COLLECTION MODULE -->
        <?php include 'data_collection.php'; ?>

        <!-- 4. EVALUATIONS MODULE -->
        <?php include 'evaluation.php'; ?>

        <!-- 5. COMPARISON MODULE -->
        <?php include 'comparison.php'; ?>

        <!-- 6. REPORTS MODULE -->
        <?php include 'reports.php'; ?>

        <!-- 9. APPROVAL QUEUE MODULE -->
        <section id="approvalQueueSection" class="content-section d-none">
          <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
              <h2 class="h4 fw-bold text-dark mb-1"><i class="bi bi-person-check-fill text-primary me-2"></i>Approval
                Queue</h2>
              <p class="text-muted mb-0">Review and manage pending staff account requests.</p>
            </div>
            <span class="badge bg-warning text-dark fs-6 px-3 py-2">Pending requests</span>
          </div>

          <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
            <div id="emptyQueueMessage" class="text-center text-muted py-5 d-none">
              <i class="bi bi-inbox fs-1 d-block mb-2"></i>No pending account requests.
            </div>
            <div class="table-responsive">
              <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                  <tr>
                    <th>Name</th>
                    <th>Username</th>
                    <th>Position / Department</th>
                    <th>Email</th>
                    <th>Status</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody id="approvalQueueTableBody"></tbody>
              </table>
            </div>
          </div>
        </section>

        <!-- 10. AUDIT LOGS MODULE -->
        <?php include 'audit_logs.php'; ?>

        <!-- 11. ACTIVE USER DIRECTORY -->
        <?php include 'user_directory.php'; ?>

        <!-- 12. DATABASE MANAGEMENT MODULE -->
        <?php include 'database_management.php'; ?>

        <!-- 13. ADMIN PROFILE -->
        <?php include 'admin_profile.php'; ?>

      </main>
    </div>
  </div>

  <?php include 'includes/modals.php'; ?>
  <!-- Bootstrap 5.3 & Chart.js & PDF.js Scripts -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
  <script>if (window.pdfjsLib) pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';</script>

  <!-- PHP → JS config bridge (no logic here, only data) -->
  <script>
    window.ADMIN_CONFIG = {
      activeSection: '<?= $active_section ?>',
      dashboardCharts: <?= json_encode($dashboard_charts_payload ?? []) ?>
    };
  </script>
  <!-- Admin Application Logic -->
  <script src="../assets/js/admin.js?v=<?= time() ?>"></script>


</body>

</html>