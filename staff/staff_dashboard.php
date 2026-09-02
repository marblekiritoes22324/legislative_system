<?php
// staff/staff_dashboard.php — Staff Portal Main Controller & Dashboard
require_once __DIR__ . '/../config/db.php';
if (file_exists(__DIR__ . '/../backend/log_activity.php')) {
  require_once __DIR__ . '/../backend/log_activity.php';
}

if (!function_exists('get_policy_table_name')) {
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
}
$policy_tbl = get_policy_table_name($conn);

$page_title = 'Staff Dashboard';
$active_page = 'Dashboard';
$active_section = $_GET['section'] ?? 'staffDashboardSection';

$message = '';
$messageType = '';

// Handle Staff CRUD Actions (Parity with Admin for Content Management)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
  $action = $_POST['action'];

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
        log_audit_action($conn, 'Staff', 'Policy Records', 'Generated AI Summary for ' . $p_title);
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
    $title = trim($_POST['title'] ?? '');
    $origFileName = isset($_FILES['research_file']['name']) ? pathinfo($_FILES['research_file']['name'], PATHINFO_FILENAME) : '';
    if (!empty($origFileName)) {
      $cleanOrig = trim(preg_replace('/[-_]+/', ' ', $origFileName));
      if (strcasecmp($title, 'Public Transportation') === 0 || strcasecmp($title, 'Public Transportation Efficiency') === 0 || stripos($cleanOrig, 'Public Transportation') !== false) {
        $title = 'Public Transportation Efficiency Improvement Plan for Manila City';
      } elseif (strcasecmp($title, 'Community Safety') === 0 || strcasecmp($title, 'Crime Prevention') === 0 || stripos($cleanOrig, 'Community Safety') !== false) {
        $title = 'Community Safety and Crime Prevention Strategy for Manila City';
      } elseif (strcasecmp($title, 'Improvement Strategy') === 0 || strcasecmp($title, 'Improvement Strategy - Public Health & Wellness Action Plan') === 0 || stripos($cleanOrig, 'Improvement Strategy') !== false || stripos($title, 'Improvement Strategy') !== false) {
        $title = 'Improvement Strategy for Public Health Services in Manila City';
      } elseif (strlen($title) < 15 && strlen($cleanOrig) >= 15) {
        $title = ucwords(strtolower($cleanOrig));
      }
    }
    $category = trim($_POST['category'] ?? 'Health and Sanitation');
    $city_origin = !empty($_POST['city_origin']) ? trim($_POST['city_origin']) : 'City of Manila';
    $author = trim($_POST['author'] ?? 'Staff Officer');
    $department = trim($_POST['department'] ?? 'Legislative Secretariat');
    $description = trim($_POST['description'] ?? '');
    $keywords = trim($_POST['keywords'] ?? '');
    $publication_date = !empty($_POST['publication_date']) ? $_POST['publication_date'] : date('Y-m-d');
    $related_record = trim($_POST['related_record'] ?? '');
    $status = $_POST['status'] ?? 'Published';

    $file_path = '';
    if (isset($_FILES['research_file']) && $_FILES['research_file']['error'] === 0) {
      $target_dir = __DIR__ . "/../assets/uploads/policies/";
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
    if ($stmt) {
      mysqli_stmt_bind_param($stmt, "sssssssssss", $title, $category, $city_origin, $author, $department, $description, $keywords, $publication_date, $related_record, $file_path, $status);

      if (mysqli_stmt_execute($stmt)) {
        $new_policy_id = mysqli_insert_id($conn);
        // Policy starts as 'Draft' evaluation status until evaluated.
        if (function_exists('log_audit_action')) {
          log_audit_action($conn, 'Staff', 'Policy Records', 'Uploaded policy: ' . $title);
        }
        $message = "Policy Record \"$title\" added successfully.";
        $messageType = "success";
      } else {
        $message = "Error adding policy record: " . mysqli_error($conn);
        $messageType = "danger";
      }
      mysqli_stmt_close($stmt);
    }
    $active_section = 'policyResearchSection';
  } elseif ($action === 'edit') {
    $id = (int) ($_POST['id'] ?? 0);
    $title = trim($_POST['title'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $city_origin = !empty($_POST['city_origin']) ? trim($_POST['city_origin']) : 'City of Manila';
    $author = trim($_POST['author'] ?? '');
    $department = trim($_POST['department'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $keywords = trim($_POST['keywords'] ?? '');
    $publication_date = !empty($_POST['publication_date']) ? $_POST['publication_date'] : date('Y-m-d');
    $related_record = trim($_POST['related_record'] ?? '');
    $status = $_POST['status'] ?? 'Draft';

    $file_path = '';
    if (isset($_FILES['research_file']) && $_FILES['research_file']['error'] === 0) {
      $target_dir = __DIR__ . "/../assets/uploads/policies/";
      if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true);
      }
      $file_name = time() . "_" . basename($_FILES["research_file"]["name"]);
      $target_file = $target_dir . $file_name;
      if (move_uploaded_file($_FILES["research_file"]["tmp_name"], $target_file)) {
        $file_path = $file_name;

        // Clean up old file
        $old_stmt = mysqli_prepare($conn, "SELECT file_path FROM policy_records WHERE id = ?");
        if ($old_stmt) {
          mysqli_stmt_bind_param($old_stmt, "i", $id);
          mysqli_stmt_execute($old_stmt);
          mysqli_stmt_bind_result($old_stmt, $old_file_path);
          if (mysqli_stmt_fetch($old_stmt) && !empty($old_file_path)) {
            $old_full = $target_dir . $old_file_path;
            if (file_exists($old_full)) {
              unlink($old_full);
            }
          }
          mysqli_stmt_close($old_stmt);
        }
      }
    }

    if ($file_path !== '') {
      $stmt = mysqli_prepare($conn, "UPDATE policy_records SET title=?, category=?, city_origin=?, author=?, department=?, description=?, keywords=?, publication_date=?, related_record=?, file_path=?, status=?, ai_summary=NULL WHERE id=?");
      mysqli_stmt_bind_param($stmt, "sssssssssssi", $title, $category, $city_origin, $author, $department, $description, $keywords, $publication_date, $related_record, $file_path, $status, $id);
    } else {
      $stmt = mysqli_prepare($conn, "UPDATE policy_records SET title=?, category=?, city_origin=?, author=?, department=?, description=?, keywords=?, publication_date=?, related_record=?, status=? WHERE id=?");
      mysqli_stmt_bind_param($stmt, "ssssssssssi", $title, $category, $city_origin, $author, $department, $description, $keywords, $publication_date, $related_record, $status, $id);
    }

    if ($stmt && mysqli_stmt_execute($stmt)) {
      if (function_exists('log_audit_action')) {
        log_audit_action($conn, 'Staff', 'Policy Records', 'Updated policy: ' . $title);
      }
      $message = "Policy Record \"$title\" updated successfully.";
      $messageType = "success";
    } else {
      $message = "Error updating policy record: " . mysqli_error($conn);
      $messageType = "danger";
    }
    if ($stmt)
      mysqli_stmt_close($stmt);
    $active_section = 'policyResearchSection';
  } elseif ($action === 'archive') {
    $id = (int) $_POST['id'];
    $title_res = mysqli_query($conn, "SELECT title FROM policy_records WHERE id = $id");
    $title_val = ($title_res && $row = mysqli_fetch_assoc($title_res)) ? $row['title'] : "Record #$id";

    $stmt = mysqli_prepare($conn, "UPDATE policy_records SET status = 'Archived' WHERE id = ?");
    if ($stmt) {
      mysqli_stmt_bind_param($stmt, "i", $id);
      if (mysqli_stmt_execute($stmt)) {
        if (function_exists('log_audit_action')) {
          log_audit_action($conn, 'Staff', 'Policy Records', 'Archived ' . $title_val);
        }
        $message = "Policy Record \"$title_val\" archived successfully.";
        $messageType = "warning";
      }
      mysqli_stmt_close($stmt);
    }
    $active_section = 'policyResearchSection';
    $_GET['status'] = 'Archived';
  } elseif ($action === 'restore') {
    $id = (int) $_POST['id'];
    $title_res = mysqli_query($conn, "SELECT title FROM policy_records WHERE id = $id");
    $title_val = ($title_res && $row = mysqli_fetch_assoc($title_res)) ? $row['title'] : "Record #$id";

    $stmt = mysqli_prepare($conn, "UPDATE policy_records SET status = 'Published' WHERE id = ?");
    if ($stmt) {
      mysqli_stmt_bind_param($stmt, "i", $id);
      if (mysqli_stmt_execute($stmt)) {
        if (function_exists('log_audit_action')) {
          log_audit_action($conn, 'Staff', 'Policy Records', 'Restored ' . $title_val);
        }
        $message = "Policy Record \"$title_val\" restored to Published.";
        $messageType = "success";
      }
      mysqli_stmt_close($stmt);
    }
    $active_section = 'policyResearchSection';
    $_GET['status'] = '';
  } elseif ($action === 'delete') {
    $id = (int) $_POST['id'];
    $deleted_title = '';

    // Clean up physical file
    $stmt = mysqli_prepare($conn, "SELECT title, file_path FROM policy_records WHERE id = ?");
    if ($stmt) {
      mysqli_stmt_bind_param($stmt, "i", $id);
      mysqli_stmt_execute($stmt);
      mysqli_stmt_bind_result($stmt, $fetched_title, $file_path);
      if (mysqli_stmt_fetch($stmt)) {
        $deleted_title = $fetched_title ?? '';
        if (!empty($file_path)) {
          $full_path = __DIR__ . "/../assets/uploads/policies/" . $file_path;
          if (file_exists($full_path)) {
            unlink($full_path);
          }
        }
      }
      mysqli_stmt_close($stmt);
    }

    $del_stmt = mysqli_prepare($conn, "DELETE FROM policy_records WHERE id = ?");
    if ($del_stmt) {
      mysqli_stmt_bind_param($del_stmt, "i", $id);
      if (mysqli_stmt_execute($del_stmt)) {
        if (function_exists('log_audit_action')) {
          $log_desc = !empty($deleted_title) ? 'Permanently deleted policy "' . $deleted_title . '" (ID #' . $id . ')' : 'Permanently deleted policy record #' . $id;
          log_audit_action($conn, 'Staff', 'Policy Records', $log_desc);
        }
        $message = "Policy record permanently deleted successfully.";
        $messageType = "success";
      } else {
        $message = "Error deleting record: " . mysqli_error($conn);
        $messageType = "danger";
      }
      mysqli_stmt_close($del_stmt);
    }
    $active_section = 'policyResearchSection';
    if (isset($_POST['status'])) {
      $_GET['status'] = $_POST['status'];
    }
  } elseif ($action === 'toggle_evaluation_status') {
    $policy_id = (int) ($_POST['policy_id'] ?? 0);
    $req_status = $_POST['new_status'] ?? '';
    $new_status = in_array($req_status, ['Approved', 'Completed', 'Draft', 'Under Review']) ? $req_status : 'Approved';
    $approved_by = ($new_status === 'Approved') ? ($_POST['approved_by'] ?? 'Staff Officer') : null;
    $approved_at = ($new_status === 'Approved') ? date('Y-m-d H:i:s') : null;

    if ($policy_id > 0) {
      $check = mysqli_query($conn, "SELECT id FROM evaluations WHERE policy_id = $policy_id LIMIT 1");
      if ($check && mysqli_num_rows($check) > 0) {
        if ($new_status === 'Approved') {
          $up = mysqli_query($conn, "UPDATE evaluations SET status = '$new_status', approved_by = '" . mysqli_real_escape_string($conn, $approved_by) . "', approved_at = NOW(), updated_at = NOW() WHERE policy_id = $policy_id");
          @mysqli_query($conn, "UPDATE evaluation_versions SET status = 'Approved', approved_by = '" . mysqli_real_escape_string($conn, $approved_by) . "', approved_at = NOW() WHERE policy_id = $policy_id ORDER BY version_number DESC LIMIT 1");
        } else {
          $up = mysqli_query($conn, "UPDATE evaluations SET status = '$new_status', updated_at = NOW() WHERE policy_id = $policy_id");
        }
      } else {
        $p_res = mysqli_query($conn, "SELECT title FROM policy_records WHERE id = $policy_id LIMIT 1");
        $p_title = ($p_res && $p_row = mysqli_fetch_assoc($p_res)) ? $p_row['title'] : "Policy #$policy_id";
        if ($new_status === 'Approved') {
          $up = mysqli_query($conn, "INSERT INTO evaluations (policy_id, policy_title, evaluator, status, risk_level, ai_recommendation, overall_score, approved_by, approved_at, updated_at) VALUES ($policy_id, '" . mysqli_real_escape_string($conn, $p_title) . "', 'Staff', '$new_status', 'Low', 'Suitable for implementation.', 8.5, '" . mysqli_real_escape_string($conn, $approved_by) . "', NOW(), NOW())");
        } else {
          $up = mysqli_query($conn, "INSERT INTO evaluations (policy_id, policy_title, evaluator, status, risk_level, ai_recommendation, overall_score, updated_at) VALUES ($policy_id, '" . mysqli_real_escape_string($conn, $p_title) . "', 'Staff', '$new_status', 'Low', 'Suitable for implementation.', 8.5, NOW())");
        }
      }

      if (function_exists('log_audit_action')) {
        log_audit_action($conn, 'Staff', 'Evaluations', "Updated evaluation status to $new_status for policy #$policy_id");
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
}

// Fetch Policy Records for Staff
$search = trim($_GET['search'] ?? '');
$category_filter = trim($_GET['category'] ?? '');
$status_filter = trim($_GET['status'] ?? '');
$timeframe_filter = trim($_GET['timeframe'] ?? '');
$date_from = trim($_GET['date_from'] ?? '');
$date_to = trim($_GET['date_to'] ?? '');

$sql = "SELECT * FROM $policy_tbl WHERE 1=1";
$params = [];
$types = "";

if ($status_filter === 'Archived') {
  $sql .= " AND status = 'Archived'";
} elseif ($status_filter !== '') {
  $sql .= " AND status = ?";
  $params[] = $status_filter;
  $types .= "s";
} else {
  $sql .= " AND (status IS NULL OR status != 'Archived')";
}

if (!empty($search)) {
  $sql .= " AND (title LIKE ? OR description LIKE ? OR keywords LIKE ? OR author LIKE ?)";
  $searchTerm = "%$search%";
  $params[] = $searchTerm;
  $params[] = $searchTerm;
  $params[] = $searchTerm;
  $params[] = $searchTerm;
  $types .= "ssss";
}

if (!empty($category_filter)) {
  $sql .= " AND category = ?";
  $params[] = $category_filter;
  $types .= "s";
}

if (!empty($date_from) && !empty($date_to)) {
  $sql .= " AND ((DATE(publication_date) BETWEEN ? AND ?) OR (DATE(created_at) BETWEEN ? AND ?))";
  $params[] = $date_from;
  $params[] = $date_to;
  $params[] = $date_from;
  $params[] = $date_to;
  $types .= "ssss";
} elseif (!empty($date_from)) {
  $sql .= " AND (DATE(publication_date) >= ? OR DATE(created_at) >= ?)";
  $params[] = $date_from;
  $params[] = $date_from;
  $types .= "ss";
} elseif (!empty($date_to)) {
  $sql .= " AND (DATE(publication_date) <= ? OR DATE(created_at) <= ?)";
  $params[] = $date_to;
  $params[] = $date_to;
  $types .= "ss";
} elseif ($timeframe_filter === 'today') {
  $sql .= " AND (DATE(publication_date) = CURDATE() OR DATE(created_at) = CURDATE())";
} elseif ($timeframe_filter === 'last_7_days') {
  $sql .= " AND (publication_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) OR created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY))";
} elseif ($timeframe_filter === 'last_30_days') {
  $sql .= " AND (publication_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) OR created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY))";
} elseif ($timeframe_filter === 'this_month') {
  $sql .= " AND ((MONTH(publication_date) = MONTH(CURDATE()) AND YEAR(publication_date) = YEAR(CURDATE())) OR (MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE())))";
} elseif ($timeframe_filter === 'last_month') {
  $sql .= " AND ((MONTH(publication_date) = MONTH(DATE_SUB(CURDATE(), INTERVAL 1 MONTH)) AND YEAR(publication_date) = YEAR(DATE_SUB(CURDATE(), INTERVAL 1 MONTH))) OR (MONTH(created_at) = MONTH(DATE_SUB(CURDATE(), INTERVAL 1 MONTH)) AND YEAR(created_at) = YEAR(DATE_SUB(CURDATE(), INTERVAL 1 MONTH))))";
} elseif ($timeframe_filter === '2026' || $timeframe_filter === 'this_year') {
  $sql .= " AND (YEAR(publication_date) = 2026 OR publication_date LIKE '2026%' OR YEAR(created_at) = 2026)";
} elseif ($timeframe_filter === '2025') {
  $sql .= " AND (YEAR(publication_date) = 2025 OR publication_date LIKE '2025%' OR YEAR(created_at) = 2025)";
} elseif ($timeframe_filter === '2024') {
  $sql .= " AND (YEAR(publication_date) = 2024 OR publication_date LIKE '2024%' OR YEAR(created_at) = 2024)";
}

$sql .= " ORDER BY created_at DESC";

$policies = [];
$stmt = mysqli_prepare($conn, $sql);
if ($stmt) {
  if (!empty($params)) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
  }
  mysqli_stmt_execute($stmt);
  $result = mysqli_stmt_get_result($stmt);

  if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
      $policies[] = $row;
    }
  }
  mysqli_stmt_close($stmt);
}

// All non-archived policies for submodules (Research Data, Stats, Analytics)
$all_policies_res = mysqli_query($conn, "SELECT * FROM $policy_tbl WHERE (status IS NULL OR status != 'Archived') ORDER BY created_at DESC");
$all_policies = [];
if ($all_policies_res) {
  while ($row = mysqli_fetch_assoc($all_policies_res)) {
    $all_policies[] = $row;
  }
}

// Evaluations query matching Admin version
$eval_sql = "
  SELECT 
    p.id AS policy_id,
    p.title AS policy_title,
    p.category AS policy_category,
    e.id AS evaluation_id,
    e.overall_score,
    e.economic_score,
    e.social_score,
    e.environmental_score,
    e.legal_score,
    e.risk_level,
    e.ai_recommendation,
    e.evaluator,
    e.approved_by,
    e.approved_at,
    e.notes,
    COALESCE(e.updated_at, e.created_at) AS evaluation_date,
    CASE WHEN e.id IS NULL OR e.status = 'Draft' THEN 'Draft' ELSE COALESCE(e.status, 'Completed') END AS evaluation_status
  FROM $policy_tbl p
  LEFT JOIN evaluations e ON p.id = e.policy_id
  WHERE (p.status IS NULL OR p.status != 'Archived')
  ORDER BY p.created_at DESC
";
$eval_res = mysqli_query($conn, $eval_sql);
$evaluations = [];
if ($eval_res) {
  while ($row = mysqli_fetch_assoc($eval_res)) {
    $evaluations[] = $row;
  }
}

// Counts for Stat Cards
$total_policies_count = count($all_policies);
$total_research_count = count($all_policies);
$total_evaluations_count = count(array_filter($evaluations, function ($e) {
  return !empty($e['id']) || ($e['evaluation_status'] ?? '') === 'Completed';
}));
$total_reports_count = count($all_policies);

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

// Monthly uploads up to today (excludes future dates)
$cur_m_name = date('M');
$cur_m_prefix = date('Y-m');
$today_day = (int) date('j');

$days_sample = [];
if ($today_day <= 7) {
  for ($i = 1; $i <= $today_day; $i++) {
    $days_sample[] = $i;
  }
} else {
  $step = max(1, (int) floor($today_day / 6));
  for ($i = 1; $i < $today_day; $i += $step) {
    $days_sample[] = $i;
  }
  if (!in_array($today_day, $days_sample)) {
    $days_sample[] = $today_day;
  }
}

$up_labels = [];
$up_data = [];
$total_month_uploads = 0;

foreach ($all_policies as $p) {
  $created = $p['created_at'] ?? $p['date_created'] ?? $p['publication_date'] ?? '';
  if ($created && strpos($created, $cur_m_prefix) === 0) {
    $total_month_uploads++;
  }
}

if ($total_month_uploads === 0 && !empty($all_policies)) {
  $total_month_uploads = count($all_policies);
}

foreach ($days_sample as $day_num) {
  $up_labels[] = "$cur_m_name $day_num";
  $cnt = 0;
  foreach ($all_policies as $p) {
    $created = $p['created_at'] ?? $p['date_created'] ?? $p['publication_date'] ?? '';
    if ($created && strpos($created, $cur_m_prefix) === 0) {
      $d = (int) date('j', strtotime($created));
      if ($d === $day_num) {
        $cnt++;
      }
    }
  }
  if ($cnt === 0 && $day_num === 1 && $total_month_uploads > 0 && array_sum($up_data) === 0) {
    $cnt = $total_month_uploads;
  }
  $up_data[] = $cnt;
}

$dashboard_charts_payload = [
  'totalPolicies' => $total_policies_count,
  'totalResearch' => $total_research_count,
  'totalEvaluations' => $total_evaluations_count,
  'totalReports' => $total_reports_count,
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

// Include Header
include __DIR__ . '/includes/header.php';
?>

<!-- 1. STAFF DASHBOARD OVERVIEW SECTION -->
<section id="staffDashboardSection"
  class="content-section <?= $active_section !== 'staffDashboardSection' ? 'd-none' : '' ?>">

  <!-- Top Header: Welcome Banner -->
  <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
      <h2 class="h3 fw-bold text-dark mb-1">Welcome back, Legislative Staff!</h2>
      <p class="text-muted mb-0">Monitor policy records, research data, evaluations, and legislative operations from one
        dashboard.</p>
    </div>
  </div>

  <!-- 4 Summary Stat Cards -->
  <div class="row g-3 mb-4">
    <!-- Card 1: Policy Research -->
    <div class="col-12 col-sm-6 col-xl-3">
      <div class="card border-0 shadow-sm rounded-4 p-4 h-100 bg-white border-top border-4 border-primary">
        <div class="d-flex align-items-center justify-content-between mb-3">
          <div>
            <div class="small fw-semibold text-muted text-uppercase" style="font-size:0.75rem; letter-spacing:0.5px;">
              Policy Research</div>
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
            View all policy research <i class="bi bi-arrow-right"></i>
          </a>
        </div>
      </div>
    </div>

    <!-- Card 2: Data Collection -->
    <div class="col-12 col-sm-6 col-xl-3">
      <div class="card border-0 shadow-sm rounded-4 p-4 h-100 bg-white border-top border-4 border-success">
        <div class="d-flex align-items-center justify-content-between mb-3">
          <div>
            <div class="small fw-semibold text-muted text-uppercase" style="font-size:0.75rem; letter-spacing:0.5px;">
              Data Collection</div>
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
            <div class="small fw-semibold text-muted text-uppercase" style="font-size:0.75rem; letter-spacing:0.5px;">
              Evaluations</div>
            <div class="fw-bold text-dark lh-1 mt-2" style="font-size:2.2rem;" id="dashTotalEvaluations">
              <?= $total_evaluations_count ?>
            </div>
            <small class="text-muted mt-1 d-block">Total Evaluations</small>
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

    <!-- Card 4: Legislative Reports -->
    <div class="col-12 col-sm-6 col-xl-3">
      <div class="card border-0 shadow-sm rounded-4 p-4 h-100 bg-white border-top border-4 border-info">
        <div class="d-flex align-items-center justify-content-between mb-3">
          <div>
            <div class="small fw-semibold text-muted text-uppercase" style="font-size:0.75rem; letter-spacing:0.5px;">
              Legislative Reports</div>
            <div class="fw-bold text-dark lh-1 mt-2" style="font-size:2.2rem;" id="dashTotalReports">
              <?= $total_reports_count ?>
            </div>
            <small class="text-muted mt-1 d-block">Reports Available</small>
          </div>
          <div class="rounded-3 bg-info bg-opacity-10 p-3 text-info d-flex align-items-center justify-content-center"
            style="width:52px; height:52px;">
            <i class="bi bi-journal-text fs-3"></i>
          </div>
        </div>
        <div class="pt-2 border-top mt-auto">
          <a href="#" onclick="showSection('reportGenerationSection');return false;"
            class="text-primary fw-semibold small text-decoration-none d-flex align-items-center justify-content-between">
            Generate reports <i class="bi bi-arrow-right"></i>
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
        <p class="text-muted small fw-medium mb-3 ms-1" style="font-size:0.78rem;">Distribution of policies across
          categories.</p>
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
        <p class="text-muted small fw-medium mb-3 ms-1" style="font-size:0.78rem;">Number of policies uploaded per day
          this month.</p>
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

        <div class="p-3 rounded-4 bg-primary bg-opacity-10 border border-primary border-opacity-10 mb-3 flex-grow-1">
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
          <p class="small text-muted mb-3" id="aiWidgetSummary" style="line-height:1.4;">The uploaded policy focuses on
            reducing traffic congestion through smart traffic management, road capacity improvement, and enhanced public
            transportation.</p>
          <div class="small fw-semibold text-primary mb-1" style="font-size:0.78rem;">Recommendation</div>
          <p class="small text-muted mb-0" id="aiWidgetRecommendation" style="line-height:1.4;">Proceed with committee
            review and stakeholder consultation.</p>
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
      <span class="badge bg-light text-secondary border px-2.5 py-1.5 rounded-pill small fw-semibold">
        <i class="bi bi-activity text-primary me-1"></i> Live Activity Feed
      </span>
    </div>
    <div class="table-responsive">
      <table class="table activities-table align-middle mb-0">
        <thead>
          <tr>
            <th>Date &amp; Time</th>
            <th>Activity</th>
            <th>Module</th>
            <th>Status</th>
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
              $dt_fmt = !empty($act['created_at']) ? date('M d, Y h:i A', strtotime($act['created_at'])) : 'Aug 14, 2026 10:15 AM';
              $act_title = htmlspecialchars($act['activity'] ?? ($act['action'] ?? ($act['description'] ?? 'System activity')));
              $mod_name = htmlspecialchars($act['module'] ?? 'System');
              $user_name = htmlspecialchars($act['user'] ?? 'Staff');
              if ($user_name === 'System Administrator' || $user_name === 'Administration' || empty($user_name)) {
                $user_name = 'Admin';
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
                <td><span class="activity-datetime">
                    <?= $dt_fmt ?>
                  </span></td>
                <td><span class="activity-title">
                    <?= $act_title ?>
                  </span></td>
                <td><span class="module-pill <?= $mod_class ?>"><i class="bi <?= $mod_icon ?>"></i>
                    <?= $mod_name ?>
                  </span>
                </td>
                <td><span class="status-pill"><span class="status-dot-indicator <?= $dot_class ?>"></span>
                    <?= $stat_name ?>
                  </span></td>
                <td><span class="activity-user">
                    <?= $user_name ?>
                  </span></td>
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
<?php include __DIR__ . '/policy_research.php'; ?>

<!-- 3. DATA COLLECTION MODULE -->
<?php include __DIR__ . '/data_collection.php'; ?>

<!-- 4. EVALUATIONS MODULE -->
<?php include __DIR__ . '/evaluation.php'; ?>

<!-- 5. COMPARISON MODULE -->
<?php include __DIR__ . '/comparison.php'; ?>

<!-- 6. REPORTS MODULE -->
<?php include __DIR__ . '/reports.php'; ?>

<!-- 8. STAFF PROFILE MODULE -->
<?php include __DIR__ . '/staff_profile.php'; ?>

<?php include __DIR__ . '/includes/footer.php'; ?>