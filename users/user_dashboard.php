<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
require_once '../config/db.php';
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

$active_section = $_GET['section'] ?? 'userDashboardSection';
$message = '';
$messageType = '';

// Handle Policy Actions (Add, Edit, Archive, Restore, Delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
  $action = $_POST['action'];

  if ($action === 'add') {
    $title = trim($_POST['title'] ?? '');
    $category = trim($_POST['category'] ?? 'Health and Sanitation');
    $author = trim($_POST['author'] ?? 'Staff Officer');
    $department = trim($_POST['department'] ?? 'Legislative Secretariat');
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
      }
    }

    $stmt = mysqli_prepare($conn, "INSERT INTO policy_records (title, category, author, department, description, keywords, publication_date, related_record, file_path, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    if ($stmt) {
      mysqli_stmt_bind_param($stmt, "ssssssssss", $title, $category, $author, $department, $description, $keywords, $publication_date, $related_record, $file_path, $status);
      if (mysqli_stmt_execute($stmt)) {
        if (function_exists('log_audit_action')) {
          log_audit_action($conn, 'Staff', 'Policy Records', 'Uploaded policy: ' . $title);
        }
        $message = "Policy Record \"$title\" added successfully.";
        $messageType = "success";
      } else {
        $message = "Error adding policy: " . mysqli_error($conn);
        $messageType = "danger";
      }
      mysqli_stmt_close($stmt);
    }
  } elseif ($action === 'edit') {
    $id = (int) ($_POST['id'] ?? 0);
    $title = trim($_POST['title'] ?? '');
    $category = trim($_POST['category'] ?? '');
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
      }
    }

    if ($file_path !== '') {
      $stmt = mysqli_prepare($conn, "UPDATE policy_records SET title=?, category=?, author=?, department=?, description=?, keywords=?, publication_date=?, related_record=?, file_path=?, status=?, ai_summary=NULL WHERE id=?");
      mysqli_stmt_bind_param($stmt, "ssssssssssi", $title, $category, $author, $department, $description, $keywords, $publication_date, $related_record, $file_path, $status, $id);
    } else {
      $stmt = mysqli_prepare($conn, "UPDATE policy_records SET title=?, category=?, author=?, department=?, description=?, keywords=?, publication_date=?, related_record=?, status=? WHERE id=?");
      mysqli_stmt_bind_param($stmt, "sssssssssi", $title, $category, $author, $department, $description, $keywords, $publication_date, $related_record, $status, $id);
    }

    if ($stmt && mysqli_stmt_execute($stmt)) {
      if (function_exists('log_audit_action')) {
        log_audit_action($conn, 'Staff', 'Policy Records', 'Updated policy: ' . $title);
      }
      $message = "Policy Record \"$title\" updated successfully.";
      $messageType = "success";
    }
    if ($stmt)
      mysqli_stmt_close($stmt);
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
    $_GET['pl_status'] = 'Archived';
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
    $_GET['pl_status'] = '';
  } elseif ($action === 'delete') {
    $id = (int) $_POST['id'];
    $del_stmt = mysqli_prepare($conn, "DELETE FROM policy_records WHERE id = ?");
    if ($del_stmt) {
      mysqli_stmt_bind_param($del_stmt, "i", $id);
      if (mysqli_stmt_execute($del_stmt)) {
        if (function_exists('log_audit_action')) {
          log_audit_action($conn, 'Staff', 'Policy Records', 'Permanently deleted policy #' . $id);
        }
        $message = "Policy record permanently deleted.";
        $messageType = "success";
      }
      mysqli_stmt_close($del_stmt);
    }
    $_GET['pl_status'] = 'Archived';
  }
}

// Fetch dynamic database counts from policy_records for public user view
$count_ordinances = 0;
$count_research = 0;
$count_evals = 0;
$count_reports = 0;

if (!empty($conn)) {
  // 1. Published / Enacted Ordinances
  $q1 = mysqli_query($conn, "SELECT COUNT(*) c FROM $policy_tbl WHERE (status IS NULL OR status != 'Archived')");
  if ($q1) {
    $count_ordinances = (int) mysqli_fetch_assoc($q1)['c'];
  }

  // 2. Total Research Documents
  $q2 = mysqli_query($conn, "SELECT COUNT(*) c FROM $policy_tbl WHERE (status IS NULL OR status != 'Archived')");
  if ($q2) {
    $count_research = (int) mysqli_fetch_assoc($q2)['c'];
  }

  // 3. Published Evaluations
  $q3 = mysqli_query($conn, "
    SELECT COUNT(*) c 
    FROM $policy_tbl p 
    INNER JOIN evaluations e ON e.policy_id = p.id 
    WHERE (p.status IS NULL OR p.status != 'Archived')
  ");
  if ($q3) {
    $count_evals = (int) mysqli_fetch_assoc($q3)['c'];
  }

  // 4. Public Reports
  $q4 = mysqli_query($conn, "SELECT COUNT(*) c FROM $policy_tbl WHERE (status IS NULL OR status != 'Archived')");
  if ($q4) {
    $count_reports = (int) mysqli_fetch_assoc($q4)['c'];
  }
}

// Fallback values if DB connection fails or empty
if (empty($conn)) {
  $count_ordinances = 142;
  $count_research = 38;
  $count_evals = 24;
  $count_reports = 16;
}

// Fetch Featured Ordinances from DB (Deduplicated with Fallbacks)
$featured_policies = [];
if (!empty($conn)) {
  $fq = mysqli_query($conn, "SELECT id, title, category, status, author, publication_date, description, ai_summary FROM $policy_tbl WHERE (status IS NULL OR status != 'Archived') ORDER BY id DESC LIMIT 20");
  if ($fq) {
    $seen_titles = [];
    while ($row = mysqli_fetch_assoc($fq)) {
      $normTitle = strtolower(trim($row['title']));
      if (!in_array($normTitle, $seen_titles)) {
        $seen_titles[] = $normTitle;
        $featured_policies[] = $row;
        if (count($featured_policies) >= 4)
          break;
      }
    }
  }
}

// Fallback diverse featured policies if DB has fewer than 4 unique items
if (count($featured_policies) < 4) {
  $default_featured = [
    [
      'id' => 101,
      'title' => 'Ord. No. 8920 – Plastic Reduction & Recycling Code',
      'category' => 'ENVIRONMENT',
      'description' => 'Mandates commercial establishments in Manila City to phase out single-use plastics and implement zero-waste programs.',
      'publication_date' => '2026-03-12',
      'author' => 'Environment & Natural Resources Bureau',
      'status' => 'Published'
    ],
    [
      'id' => 102,
      'title' => 'Ord. No. 8915 – Senior Citizen Health & Wellness Act',
      'category' => 'SOCIAL WELFARE',
      'description' => 'Provides expanded medical subsidies, free maintenance medications, and community center access for Manila seniors.',
      'publication_date' => '2026-03-10',
      'author' => 'Health & Social Welfare Committee',
      'status' => 'Published'
    ],
    [
      'id' => 103,
      'title' => 'Ord. No. 8910 – Smart Flood Control & Pumping Station Modernization',
      'category' => 'INFRASTRUCTURE',
      'description' => 'Upgrades drainage infrastructure and deploys automated flood-monitoring sensors across low-lying coastal districts.',
      'publication_date' => '2026-03-08',
      'author' => 'Infrastructure & Engineering Bureau',
      'status' => 'Published'
    ],
    [
      'id' => 104,
      'title' => 'Ord. No. 8905 – National Clean Energy & Solar Grid Program',
      'category' => 'ENERGY',
      'description' => 'Accelerates solar panel installations on public municipal buildings and provides clean energy tax incentives.',
      'publication_date' => '2026-03-04',
      'author' => 'Energy & City Planning Division',
      'status' => 'Published'
    ]
  ];
  foreach ($default_featured as $df) {
    $dfNorm = strtolower(trim($df['title']));
    $already = false;
    foreach ($featured_policies as $fp) {
      if (strtolower(trim($fp['title'])) === $dfNorm) {
        $already = true;
        break;
      }
    }
    if (!$already) {
      $featured_policies[] = $df;
      if (count($featured_policies) >= 4)
        break;
    }
  }
}

// Fetch Recent Updates from policy_records table (Deduplicated)
$recent_updates = [];
if (!empty($conn)) {
  $rq = mysqli_query($conn, "SELECT id, title, category, status, publication_date, created_at FROM $policy_tbl WHERE (status IS NULL OR status != 'Archived') ORDER BY id DESC LIMIT 20");
  if ($rq) {
    $seen_upd_titles = [];
    while ($r = mysqli_fetch_assoc($rq)) {
      $normTitle = strtolower(trim($r['title']));
      if (!in_array($normTitle, $seen_upd_titles)) {
        $seen_upd_titles[] = $normTitle;
        $recent_updates[] = $r;
        if (count($recent_updates) >= 5)
          break;
      }
    }
  }
}

if (count($recent_updates) < 5) {
  $default_updates = [
    [
      'title' => 'Plastic Reduction & Recycling Code (Ord. 8920)',
      'publication_date' => '2026-03-12',
      'category' => 'ENVIRONMENT'
    ],
    [
      'title' => 'Senior Citizen Health & Wellness Act (Ord. 8915)',
      'publication_date' => '2026-03-10',
      'category' => 'SOCIAL WELFARE'
    ],
    [
      'title' => 'Smart Flood Control & Pumping Station Modernization (Ord. 8910)',
      'publication_date' => '2026-03-08',
      'category' => 'INFRASTRUCTURE'
    ],
    [
      'title' => 'National Clean Energy & Solar Grid Program (Ord. 8905)',
      'publication_date' => '2026-03-05',
      'category' => 'ENERGY'
    ],
    [
      'title' => 'Urban Traffic Congestion Reduction Ordinance (Ord. 8900)',
      'publication_date' => '2026-03-02',
      'category' => 'TRANSPORTATION'
    ]
  ];
  foreach ($default_updates as $du) {
    $duNorm = strtolower(trim($du['title']));
    $already = false;
    foreach ($recent_updates as $ru) {
      if (strtolower(trim($ru['title'])) === $duNorm) {
        $already = true;
        break;
      }
    }
    if (!$already) {
      $recent_updates[] = $du;
      if (count($recent_updates) >= 5)
        break;
    }
  }
}

// Fetch Category Distribution for Councilor Dashboard Chart (Exact Admin Match)
$cat_keys = [
  'Infrastructure, Traffic & Env',
  'Health and Sanitation',
  'Social Welfare & Community',
  'Civil Registry & Public Serv',
  'Education & Employment',
  'Other'
];
$cat_data_map = [
  'Infrastructure, Traffic & Env' => 0,
  'Health and Sanitation' => 0,
  'Social Welfare & Community' => 0,
  'Civil Registry & Public Serv' => 0,
  'Education & Employment' => 0,
  'Other' => 0
];

if (!empty($conn)) {
  $cq = mysqli_query($conn, "SELECT category, COUNT(*) as cnt FROM $policy_tbl WHERE (status IS NULL OR status != 'Archived') GROUP BY category");
  if ($cq) {
    while ($row = mysqli_fetch_assoc($cq)) {
      $cRaw = trim($row['category'] ?? '');
      $cLower = strtolower($cRaw);
      if (strpos($cLower, 'infra') !== false || strpos($cLower, 'traffic') !== false || strpos($cLower, 'env') !== false) {
        $cat_data_map['Infrastructure, Traffic & Env'] += (int) $row['cnt'];
      } elseif (strpos($cLower, 'health') !== false || strpos($cLower, 'sanit') !== false) {
        $cat_data_map['Health and Sanitation'] += (int) $row['cnt'];
      } elseif (strpos($cLower, 'social') !== false || strpos($cLower, 'welfare') !== false || strpos($cLower, 'community') !== false) {
        $cat_data_map['Social Welfare & Community'] += (int) $row['cnt'];
      } elseif (strpos($cLower, 'civil') !== false || strpos($cLower, 'registry') !== false || strpos($cLower, 'public serv') !== false) {
        $cat_data_map['Civil Registry & Public Serv'] += (int) $row['cnt'];
      } elseif (strpos($cLower, 'educ') !== false || strpos($cLower, 'employ') !== false) {
        $cat_data_map['Education & Employment'] += (int) $row['cnt'];
      } else {
        $cat_data_map['Other'] += (int) $row['cnt'];
      }
    }
  }
}

// Ensure non-zero fallback if database is empty
if (array_sum($cat_data_map) === 0) {
  $cat_data_map['Infrastructure, Traffic & Env'] = 5;
  $cat_data_map['Health and Sanitation'] = 2;
}

// Fetch Monthly Uploads Timeline for Councilor Dashboard Line Chart
$timeline_labels = [];
$timeline_data = [];

if (!empty($conn)) {
  $tq = mysqli_query($conn, "SELECT DATE(COALESCE(publication_date, created_at)) as up_date, COUNT(*) as cnt FROM $policy_tbl WHERE (status IS NULL OR status != 'Archived') GROUP BY up_date ORDER BY up_date ASC LIMIT 10");
  if ($tq && mysqli_num_rows($tq) > 0) {
    while ($tRow = mysqli_fetch_assoc($tq)) {
      if (!empty($tRow['up_date'])) {
        $timeline_labels[] = date('M d', strtotime($tRow['up_date']));
        $timeline_data[] = (int) $tRow['cnt'];
      }
    }
  }
}

if (empty($timeline_labels)) {
  $timeline_labels = ['Aug 12', 'Aug 15', 'Aug 19', 'Aug 26'];
  $timeline_data = [1, 1, 2, 3];
}

// ---------------------------------------------------------------
// Policy Library: fetch all policies with optional search+filter
// ---------------------------------------------------------------
$pl_search = trim($_GET['pl_search'] ?? '');
$pl_category = trim($_GET['pl_category'] ?? '');
$pl_timeframe = trim($_GET['pl_timeframe'] ?? '');

$pl_policies = [];
if (!empty($conn)) {
  $where_clauses = ["(status IS NULL OR status != 'Archived')"];
  $bind_types = '';
  $bind_values = [];

  if ($pl_search !== '') {
    $where_clauses[] = '(title LIKE ? OR description LIKE ? OR author LIKE ?)';
    $like = '%' . $pl_search . '%';
    $bind_types .= 'sss';
    $bind_values[] = $like;
    $bind_values[] = $like;
    $bind_values[] = $like;
  }
  if ($pl_category !== '') {
    $where_clauses[] = 'category = ?';
    $bind_types .= 's';
    $bind_values[] = $pl_category;
  }

  if ($pl_timeframe === 'today') {
    $where_clauses[] = "(DATE(publication_date) = CURDATE() OR DATE(created_at) = CURDATE())";
  } elseif ($pl_timeframe === 'last_7_days') {
    $where_clauses[] = "(publication_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) OR created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY))";
  } elseif ($pl_timeframe === 'last_30_days') {
    $where_clauses[] = "(publication_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) OR created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY))";
  } elseif ($pl_timeframe === 'this_month') {
    $where_clauses[] = "((MONTH(publication_date) = MONTH(CURDATE()) AND YEAR(publication_date) = YEAR(CURDATE())) OR (MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE())))";
  } elseif ($pl_timeframe === 'last_month') {
    $where_clauses[] = "((MONTH(publication_date) = MONTH(DATE_SUB(CURDATE(), INTERVAL 1 MONTH)) AND YEAR(publication_date) = YEAR(DATE_SUB(CURDATE(), INTERVAL 1 MONTH))) OR (MONTH(created_at) = MONTH(DATE_SUB(CURDATE(), INTERVAL 1 MONTH)) AND YEAR(created_at) = YEAR(DATE_SUB(CURDATE(), INTERVAL 1 MONTH))))";
  } elseif ($pl_timeframe === '2026' || $pl_timeframe === 'this_year') {
    $where_clauses[] = "(YEAR(publication_date) = 2026 OR publication_date LIKE '2026%' OR YEAR(created_at) = 2026)";
  } elseif ($pl_timeframe === '2025') {
    $where_clauses[] = "(YEAR(publication_date) = 2025 OR publication_date LIKE '2025%' OR YEAR(created_at) = 2025)";
  } elseif ($pl_timeframe === '2024') {
    $where_clauses[] = "(YEAR(publication_date) = 2024 OR publication_date LIKE '2024%' OR YEAR(created_at) = 2024)";
  }

  $where_sql = '';
  if (!empty($where_clauses)) {
    $where_sql = 'WHERE ' . implode(' AND ', $where_clauses);
  }

  $pl_sql = "SELECT id, title, category, author, status, department, keywords, related_record, publication_date, description, file_path, ai_summary FROM $policy_tbl $where_sql ORDER BY id DESC";
  $pl_stmt = mysqli_prepare($conn, $pl_sql);
  if ($pl_stmt) {
    if (!empty($bind_values)) {
      mysqli_stmt_bind_param($pl_stmt, $bind_types, ...$bind_values);
    }
    mysqli_stmt_execute($pl_stmt);
    $pl_result = mysqli_stmt_get_result($pl_stmt);
    while ($row = mysqli_fetch_assoc($pl_result)) {
      $pl_policies[] = $row;
    }
    mysqli_stmt_close($pl_stmt);
  }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Legislative Information Portal - Manila City Hall</title>
  <script>
    (function () {
      const isStaff = localStorage.getItem('staff_logged_in') === 'true';
      const isAdmin = localStorage.getItem('admin_logged_in') === 'true';
      let currentUser = {};
      try {
        currentUser = JSON.parse(localStorage.getItem('current_user') || '{}');
      } catch (e) { }

      const role = (currentUser.role || '').toLowerCase();
      if (role === 'councilor' || role === 'user') {
        localStorage.removeItem('staff_logged_in');
        localStorage.removeItem('admin_logged_in');
        return;
      }
      if (role === 'staff' || (currentUser.username && currentUser.username.toLowerCase() === 'staff') || (isStaff && !isAdmin)) {
        window.location.href = '../staff/staff_dashboard.php';
        return;
      }
      if (role === 'admin' || (currentUser.username && currentUser.username.toLowerCase() === 'admin') || (isAdmin && !isStaff)) {
        window.location.href = '../admin/admin_dashboard.php';
        return;
      }
    })();

    if (localStorage.getItem('admin_sidebar_collapsed') === 'true' || localStorage.getItem('user_sidebar_collapsed') === 'true') {
      document.documentElement.classList.add('sidebar-collapsed');
    }
  </script>
  <!-- Bootstrap 5.3 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <!-- System Custom CSS -->
  <link rel="stylesheet" href="../assets/css/Manila City Hall.css?v=<?= time() ?>">
  <link rel="stylesheet" href="../assets/css/Admin.css?v=<?= time() ?>">
  <style>
    /* Polished Featured Ordinances & Recent Updates Styles Matching Admin Theme */
    .featured-policy-card {
      background: #FFFFFF !important;
      border: 1px solid #E2E8F0 !important;
      border-radius: 14px !important;
      padding: 16px 18px !important;
      transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1) !important;
    }

    .featured-policy-card:hover {
      transform: translateY(-2px) !important;
      box-shadow: 0 8px 20px rgba(15, 23, 42, 0.06) !important;
      border-color: #CBD5E1 !important;
    }

    .featured-icon-box {
      width: 42px;
      height: 42px;
      border-radius: 10px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      font-size: 1.1rem;
      flex-shrink: 0;
    }

    .btn-read-ordinance {
      background: #EFF6FF !important;
      color: #2563EB !important;
      border: 1px solid #BFDBFE !important;
      border-radius: 8px !important;
      padding: 7px 15px !important;
      font-size: 0.8rem !important;
      font-weight: 600 !important;
      transition: all 0.2s ease !important;
      box-shadow: none !important;
      white-space: nowrap !important;
      display: inline-flex !important;
      align-items: center !important;
      gap: 6px !important;
    }

    .btn-read-ordinance:hover {
      background: #2563EB !important;
      color: #FFFFFF !important;
      border-color: #2563EB !important;
      transform: translateY(-1px) !important;
      box-shadow: 0 4px 10px rgba(37, 99, 235, 0.25) !important;
    }

    .update-timeline-item {
      background: #FFFFFF !important;
      border: 1px solid #E2E8F0 !important;
      border-radius: 12px !important;
      padding: 12px 15px !important;
      transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1) !important;
    }

    .update-timeline-item:hover {
      transform: translateY(-1px) !important;
      box-shadow: 0 6px 16px rgba(15, 23, 42, 0.05) !important;
      border-color: #CBD5E1 !important;
    }

    .update-icon-dot {
      width: 34px;
      height: 34px;
      border-radius: 8px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      font-size: 0.88rem;
      flex-shrink: 0;
    }

    .badge-cat-infra {
      background: #ECFDF5 !important;
      color: #047857 !important;
      border: 1px solid #A7F3D0 !important;
    }

    .badge-cat-health {
      background: #FFF1F2 !important;
      color: #BE123C !important;
      border: 1px solid #FECDD3 !important;
    }

    .badge-cat-energy {
      background: #FFFBEB !important;
      color: #B45309 !important;
      border: 1px solid #FDE68A !important;
    }

    .badge-cat-general {
      background: #EEF2FF !important;
      color: #2563EB !important;
      border: 1px solid #BFDBFE !important;
    }

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
  <!-- User Authentication Check -->
  <script>
    const currentUser = JSON.parse(localStorage.getItem('current_user') || 'null');
    if (!currentUser) {
      // Fallback for public demo guest access if not logged in
      const guestUser = { username: 'public_citizen', name: 'Citizen Researcher', email: 'citizen@manila.gov.ph', position: 'Public Researcher', department: 'Public Sector' };
      localStorage.setItem('current_user', JSON.stringify(guestUser));
    }
  </script>

  <div class="app-shell">
    <!-- USER SIDEBAR NAVIGATION -->
    <aside class="sidebar d-flex flex-column p-3">
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

      <!-- Navigation List matching specified user submodules -->
      <nav class="nav flex-column sidebar-nav mb-4 gap-1">
        <div class="sidebar-section-label">MAIN</div>
        <a class="nav-link <?= ($active_section === 'userDashboardSection') ? 'active' : '' ?> py-2.5 px-3 rounded-3"
          href="#" data-target="userDashboardSection" onclick="showSection('userDashboardSection');return false;"
          title="Dashboard">
          <i class="bi bi-speedometer2 me-2"></i><span class="nav-text">Dashboard</span>
        </a>

        <div class="sidebar-section-label mt-3">LEGISLATIVE</div>
        <a class="nav-link <?= ($active_section === 'policyLibrarySection') ? 'active' : '' ?> py-2.5 px-3 rounded-3"
          href="#" data-target="policyLibrarySection" onclick="showSection('policyLibrarySection');return false;"
          title="Policy Research">
          <i class="bi bi-journal-bookmark-fill me-2"></i><span class="nav-text">Policy Research</span>
        </a>
        <a class="nav-link <?= ($active_section === 'policyImpactSection') ? 'active' : '' ?> py-2.5 px-3 rounded-3"
          href="#" data-target="policyImpactSection" onclick="showSection('policyImpactSection');return false;"
          title="Evaluation">
          <i class="bi bi-bar-chart-line-fill me-2"></i><span class="nav-text">Evaluation</span>
        </a>
        <a class="nav-link <?= ($active_section === 'policyComparisonSection') ? 'active' : '' ?> py-2.5 px-3 rounded-3"
          href="#" data-target="policyComparisonSection" onclick="showSection('policyComparisonSection');return false;"
          title="Comparison">
          <i class="bi bi-layout-split me-2"></i><span class="nav-text">Comparison</span>
        </a>

        <div class="sidebar-section-label mt-3">REPORTING</div>
        <a class="nav-link <?= ($active_section === 'reportsSection') ? 'active' : '' ?> py-2.5 px-3 rounded-3" href="#"
          data-target="reportsSection" onclick="showSection('reportsSection');return false;" title="Reports">
          <i class="bi bi-file-earmark-text-fill me-2"></i><span class="nav-text">Reports</span>
        </a>
      </nav>
    </aside>

    <!-- MAIN PANEL -->
    <div class="main-panel flex-grow-1">
      <!-- TOPBAR -->
      <header
        class="topbar d-flex align-items-center justify-content-between px-4 py-3 mb-4 shadow-sm bg-white rounded-4 border border-light">
        <div class="d-flex align-items-center gap-3">
          <img src="../assets/images/manilacityhall.svg" alt="Manila Seal"
            style="width:44px; height:44px; object-fit:contain;">
          <div>
            <h2 class="fs-4 fw-bold text-dark mb-0" style="letter-spacing: -0.3px; color: #0B2E59 !important;">Lungsod
              ng <span style="color: #F59E0B;">Maynila</span></h2>
            <div class="text-secondary small fw-medium" style="font-size: 0.82rem; letter-spacing: 0.2px;">Legislative
              Services — Public Portal</div>
          </div>
        </div>

        <div class="d-flex align-items-center">
          <!-- User / Councilor Notifications Dropdown -->
          <?php
          $user_notif_count = !empty($recent_updates) ? count($recent_updates) : 0;
          $user_latest_id = !empty($recent_updates) ? (int)$recent_updates[0]['id'] : 0;
          ?>
          <div class="dropdown">
            <button class="header-notif-btn" id="userNotifButton" type="button" data-bs-toggle="dropdown"
              data-latest-id="<?= $user_latest_id ?>"
              aria-expanded="false" title="Notifications">
              <i class="bi bi-bell fs-5 text-dark"></i>
              <span class="header-notif-badge" id="userNotifBadge" style="display:none;"></span>
            </button>
            <div class="dropdown-menu dropdown-menu-end shadow-lg border-0 rounded-4 p-0 overflow-hidden mt-2"
              style="width: 370px;" aria-labelledby="userNotifButton">
              <div class="px-3 py-3 d-flex align-items-center justify-content-between text-white notif-header"
                style="background: linear-gradient(120deg, #0B2E59, #1a4a8a);">
                <div>
                  <strong class="fs-6 d-block">Notifications</strong>
                  <small class="opacity-75">You have <span
                      id="userNotifUnread">0</span> new
                    updates</small>
                </div>
                <span id="userNotifHeaderBadge"
                  class="badge rounded-pill bg-warning text-dark"><?= $user_notif_count ?> Updates</span>
              </div>
              <div class="p-2" style="max-height: 290px; overflow-y: auto;">
                <ul class="list-group list-group-flush" id="userNotifList">
                  <?php if (!empty($recent_updates)): ?>
                    <?php foreach ($recent_updates as $upd): ?>
                      <?php
                      $upd_id = (int)$upd['id'];
                      $upd_title = htmlspecialchars($upd['title']);
                      $upd_cat = htmlspecialchars($upd['category'] ?? 'Policy');
                      $upd_date = !empty($upd['publication_date']) ? date('M d, Y', strtotime($upd['publication_date'])) : (!empty($upd['created_at']) ? date('M d, Y', strtotime($upd['created_at'])) : 'Recent');
                      ?>
                      <li
                        class="notif-item list-group-item p-2 mb-1 border rounded-3 d-flex justify-content-between align-items-start"
                        data-notif-id="<?= $upd_id ?>"
                        style="cursor: pointer;" onclick="handleUserNotifItemClick('policyLibrarySection', <?= $upd_id ?>);">
                        <div class="d-flex gap-2">
                          <span class="notif-dot unread mt-1.5"
                            style="background:#EF4444; width:8px; height:8px; border-radius:50%; flex-shrink:0;"></span>
                          <div>
                            <div class="fw-semibold small text-dark" style="font-size:0.86rem; line-height:1.25;">
                              <?= $upd_title ?>
                            </div>
                            <small class="text-muted d-block mt-0.5" style="font-size: 0.74rem;">Policy Uploaded &bull;
                              <?= $upd_date ?></small>
                          </div>
                        </div>
                        <span class="badge bg-primary bg-opacity-10 text-primary ms-1"
                          style="font-size:0.65rem; white-space:nowrap;"><?= $upd_cat ?></span>
                      </li>
                    <?php endforeach; ?>
                  <?php else: ?>
                    <li
                      class="notif-item list-group-item p-2 mb-1 border rounded-3 d-flex justify-content-between align-items-start"
                      style="cursor: pointer;" onclick="handleUserNotifItemClick('policyLibrarySection');">
                      <div class="d-flex gap-2">
                        <span class="notif-dot unread mt-1.5"
                          style="background:#EF4444; width:8px; height:8px; border-radius:50%; flex-shrink:0;"></span>
                        <div>
                          <div class="fw-semibold small text-dark" style="font-size:0.86rem;">Ord. No. 8920 Enacted</div>
                          <small class="text-muted d-block mt-0.5" style="font-size: 0.74rem;">Policy Records &bull; 10m
                            ago</small>
                        </div>
                      </div>
                      <span class="badge bg-warning text-dark ms-1" style="font-size:0.65rem;">Environment</span>
                    </li>
                    <li
                      class="notif-item list-group-item p-2 mb-1 border rounded-3 d-flex justify-content-between align-items-start"
                      style="cursor: pointer;" onclick="showSection('reportsSection');">
                      <div class="d-flex gap-2">
                        <span class="notif-dot unread mt-1.5"
                          style="background:#EF4444; width:8px; height:8px; border-radius:50%; flex-shrink:0;"></span>
                        <div>
                          <div class="fw-semibold small text-dark" style="font-size:0.86rem;">2026 Impact Report Published
                          </div>
                          <small class="text-muted d-block mt-0.5" style="font-size: 0.74rem;">Reports &bull; 45m
                            ago</small>
                        </div>
                      </div>
                      <span class="badge bg-info text-dark ms-1" style="font-size:0.65rem;">Report</span>
                    </li>
                  <?php endif; ?>
                </ul>
              </div>
              <div class="d-flex align-items-center justify-content-between px-3 py-2 border-top notif-footer bg-white">
                <a href="#" class="text-primary small text-decoration-none fw-semibold"
                  onclick="markAllUserNotifsRead(event)">Mark all as read</a>
                <a href="#" class="text-muted small text-decoration-none"
                  onclick="showSection('policyLibrarySection');return false;">View all &rarr;</a>
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

          <!-- Councilor / User Profile Dropdown -->
          <div class="dropdown">
            <button class="header-dropdown-btn" type="button" id="userProfileDropdown" data-bs-toggle="dropdown"
              aria-expanded="false">
              <div class="header-avatar-wrap">
                <img id="topbarUserAvatarImg" src="" alt="User Profile" class="header-avatar-img d-none" />
                <div id="topbarUserAvatarFallback" class="header-avatar-fallback">
                  <i class="bi bi-person-fill"></i>
                </div>
              </div>
              <span class="header-admin-text">
                <span class="header-admin-role">Councilor</span>
                <span class="header-admin-pipe">|</span>
                <span id="topbarUserName" class="header-admin-name">Christian M. Caspe</span>
              </span>
              <i class="bi bi-chevron-down ms-1"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 rounded-4 p-2 mt-2">
              <li><a class="dropdown-item rounded-2 py-2" href="#" data-target="profileSection"
                  onclick="showSection('profileSection');return false;"><i
                    class="bi bi-person-circle me-2 text-primary"></i>Profile</a></li>
              <li>
                <hr class="dropdown-divider my-1">
              </li>
              <li><a class="dropdown-item rounded-2 py-2 text-danger" href="../auth/logout.php" id="topbarLogoutBtn"
                  onclick="if(window.handleUserLogout){window.handleUserLogout(event);}"><i
                    class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
            </ul>
          </div>
        </div>
      </header>

      <!-- CONTENT AREA -->
      <main class="content-area px-4 pb-5">

        <!-- 1. DASHBOARD SUBMODULE -->
        <section id="userDashboardSection"
          class="content-section <?= ($active_section !== 'userDashboardSection') ? 'd-none' : '' ?>">
          <!-- Announcement Executive Briefing Banner -->
          <div class="card border-0 shadow-sm rounded-4 p-4 p-md-4 mb-4 bg-white">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
              <div>
                <div class="d-flex align-items-center gap-2 mb-1.5">
                  <span class="badge rounded-pill px-3 py-1.5 fw-semibold"
                    style="background: rgba(37, 99, 235, 0.1); color: #2563eb; font-size: 0.8rem; letter-spacing: 0.3px;">
                    <i class="bi bi-shield-fill-check me-1"></i> Councilor Legislative Portal
                  </span>
                  <span class="badge rounded-pill px-3 py-1.5 fw-semibold"
                    style="background: rgba(22, 163, 74, 0.1); color: #16a34a; font-size: 0.8rem;">
                    <i class="bi bi-circle-fill me-1" style="font-size: 0.5rem; vertical-align: middle;"></i> 12th City
                    Council Session Active
                  </span>
                </div>
                <h2 class="h4 fw-bold text-dark mb-1">Welcome, Hon. Christian M. Caspe</h2>
                <p class="text-secondary small mb-0" style="font-size: 0.88rem;">
                  Official executive decision hub &bull; Review ordinances, policy evaluations, and legislative reports.
                </p>
              </div>
              <div class="d-flex flex-wrap align-items-center gap-2">
                <button type="button"
                  class="btn btn-outline-primary rounded-3 px-3.5 py-2 fw-semibold d-inline-flex align-items-center gap-2 shadow-2xs"
                  onclick="showSection('policyLibrarySection')">
                  <i class="bi bi-search"></i>
                  <span>Explore Repository</span>
                </button>
                <button type="button"
                  class="btn btn-primary rounded-3 px-3.5 py-2 fw-semibold d-inline-flex align-items-center gap-2 shadow-sm"
                  onclick="showSection('reportsSection')">
                  <i class="bi bi-file-earmark-pdf-fill"></i>
                  <span>Council Reports</span>
                </button>
              </div>
            </div>
          </div>

          <!-- 4 Summary Stat Cards matching Admin Dashboard layout & styling -->
          <div class="row g-3 mb-4">
            <!-- Card 1: Published Ordinances -->
            <div class="col-12 col-sm-6 col-xl-3">
              <div class="card border-0 shadow-sm rounded-4 p-4 h-100 bg-white border-top border-4 border-primary">
                <div class="d-flex align-items-center justify-content-between mb-3">
                  <div>
                    <div class="small fw-semibold text-muted text-uppercase"
                      style="font-size:0.75rem; letter-spacing:0.5px;">Published Ordinances</div>
                    <div class="fw-bold text-dark lh-1 mt-2" style="font-size:2.2rem;">
                      <?= number_format($count_ordinances) ?>
                    </div>
                    <small class="text-muted mt-1 d-block">View all enacted ordinances</small>
                  </div>
                  <div
                    class="rounded-3 bg-primary bg-opacity-10 p-3 text-primary d-flex align-items-center justify-content-center"
                    style="width:52px; height:52px;">
                    <i class="bi bi-file-earmark-text-fill fs-3"></i>
                  </div>
                </div>
                <div class="pt-2 border-top mt-auto">
                  <a href="#" onclick="showSection('policyLibrarySection');return false;"
                    class="text-primary fw-semibold small text-decoration-none d-flex align-items-center justify-content-between">
                    Explore ordinances <i class="bi bi-arrow-right"></i>
                  </a>
                </div>
              </div>
            </div>

            <!-- Card 2: Research Documents -->
            <div class="col-12 col-sm-6 col-xl-3">
              <div class="card border-0 shadow-sm rounded-4 p-4 h-100 bg-white border-top border-4 border-success">
                <div class="d-flex align-items-center justify-content-between mb-3">
                  <div>
                    <div class="small fw-semibold text-muted text-uppercase"
                      style="font-size:0.75rem; letter-spacing:0.5px;">Research Documents</div>
                    <div class="fw-bold text-dark lh-1 mt-2" style="font-size:2.2rem;">
                      <?= number_format($count_research) ?>
                    </div>
                    <small class="text-muted mt-1 d-block">Explore research and datasets</small>
                  </div>
                  <div
                    class="rounded-3 bg-success bg-opacity-10 p-3 text-success d-flex align-items-center justify-content-center"
                    style="width:52px; height:52px;">
                    <i class="bi bi-book-fill fs-3"></i>
                  </div>
                </div>
                <div class="pt-2 border-top mt-auto">
                  <a href="#" onclick="showSection('policyLibrarySection');return false;"
                    class="text-primary fw-semibold small text-decoration-none d-flex align-items-center justify-content-between">
                    View research data <i class="bi bi-arrow-right"></i>
                  </a>
                </div>
              </div>
            </div>

            <!-- Card 3: Published Evaluations -->
            <div class="col-12 col-sm-6 col-xl-3">
              <div class="card border-0 shadow-sm rounded-4 p-4 h-100 bg-white border-top border-4 border-warning">
                <div class="d-flex align-items-center justify-content-between mb-3">
                  <div>
                    <div class="small fw-semibold text-muted text-uppercase"
                      style="font-size:0.75rem; letter-spacing:0.5px;">Published Evaluations</div>
                    <div class="fw-bold text-dark lh-1 mt-2" style="font-size:2.2rem;">
                      <?= number_format($count_evals) ?>
                    </div>
                    <small class="text-muted mt-1 d-block">Impact evaluations and assessments</small>
                  </div>
                  <div
                    class="rounded-3 bg-warning bg-opacity-10 p-3 text-warning d-flex align-items-center justify-content-center"
                    style="width:52px; height:52px;">
                    <i class="bi bi-bar-chart-fill fs-3"></i>
                  </div>
                </div>
                <div class="pt-2 border-top mt-auto">
                  <a href="#" onclick="showSection('policyImpactSection');return false;"
                    class="text-primary fw-semibold small text-decoration-none d-flex align-items-center justify-content-between">
                    View evaluations <i class="bi bi-arrow-right"></i>
                  </a>
                </div>
              </div>
            </div>

            <!-- Card 4: Public Reports -->
            <div class="col-12 col-sm-6 col-xl-3">
              <div class="card border-0 shadow-sm rounded-4 p-4 h-100 bg-white border-top border-4 border-info">
                <div class="d-flex align-items-center justify-content-between mb-3">
                  <div>
                    <div class="small fw-semibold text-muted text-uppercase"
                      style="font-size:0.75rem; letter-spacing:0.5px;">Public Reports</div>
                    <div class="fw-bold text-dark lh-1 mt-2" style="font-size:2.2rem;">
                      <?= number_format($count_reports) ?>
                    </div>
                    <small class="text-muted mt-1 d-block">Reports and publications</small>
                  </div>
                  <div
                    class="rounded-3 bg-info bg-opacity-10 p-3 text-info d-flex align-items-center justify-content-center"
                    style="width:52px; height:52px;">
                    <i class="bi bi-folder-fill fs-3"></i>
                  </div>
                </div>
                <div class="pt-2 border-top mt-auto">
                  <a href="#" onclick="showSection('reportsSection');return false;"
                    class="text-primary fw-semibold small text-decoration-none d-flex align-items-center justify-content-between">
                    View publications <i class="bi bi-arrow-right"></i>
                  </a>
                </div>
              </div>
            </div>
          </div>

          <!-- Middle Row: Policies by Category (Bar Chart) & Policies Uploaded This Month (Line Chart) -->
          <div class="row g-4 mb-4">
            <!-- Policies by Category (Bar Chart) -->
            <div class="col-12 col-lg-6">
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
                  <canvas id="userTrendsChart"></canvas>
                </div>
              </div>
            </div>

            <!-- Policies Uploaded This Month (Area Line Chart) -->
            <div class="col-12 col-lg-6">
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
                  <canvas id="userUploadTimelineChart"></canvas>
                </div>
              </div>
            </div>
          </div>

          <!-- Featured Policies & Recent Updates -->
          <div class="row g-4 mb-4">
            <!-- Featured Ordinances Card -->
            <div class="col-lg-7">
              <div class="card border-0 shadow-sm rounded-4 p-4 bg-white h-100">
                <div class="d-flex align-items-center justify-content-between mb-4">
                  <div class="d-flex align-items-center gap-2">
                    <div
                      class="rounded-3 p-1.5 bg-warning bg-opacity-10 text-warning d-flex align-items-center justify-content-center"
                      style="width: 34px; height: 34px;">
                      <i class="bi bi-star-fill fs-6 text-warning"></i>
                    </div>
                    <div>
                      <h3 class="h6 fw-bold text-dark mb-0">Featured Ordinances</h3>
                      <span class="text-muted small" style="font-size: 0.78rem;">Key enactments and policy
                        frameworks</span>
                    </div>
                  </div>
                  <button class="btn btn-sm btn-link text-primary fw-semibold text-decoration-none p-0"
                    onclick="showSection('policyLibrarySection')">View All <i class="bi bi-arrow-right"></i></button>
                </div>
                <div class="d-flex flex-column gap-3">
                  <?php foreach ($featured_policies as $policy):
                    $cat = strtoupper($policy['category'] ?? 'GENERAL');
                    $iconClass = 'bi-file-earmark-text-fill';
                    $badgeClass = 'badge-cat-general';
                    $iconBgStyle = 'background: #EEF2FF; color: #2563EB;';

                    if (strpos($cat, 'WELFARE') !== false || strpos($cat, 'HEALTH') !== false || strpos($cat, 'SOCIAL') !== false) {
                      $iconClass = 'bi-heart-pulse-fill';
                      $badgeClass = 'badge-cat-health';
                      $iconBgStyle = 'background: #FFF1F2; color: #BE123C;';
                    } elseif (strpos($cat, 'INFRA') !== false || strpos($cat, 'ZONING') !== false || strpos($cat, 'ENVIRONMENT') !== false) {
                      $iconClass = 'bi-building-fill-check';
                      $badgeClass = 'badge-cat-infra';
                      $iconBgStyle = 'background: #ECFDF5; color: #047857;';
                    } elseif (strpos($cat, 'ENERGY') !== false || strpos($cat, 'CLEAN') !== false) {
                      $iconClass = 'bi-lightning-charge-fill';
                      $badgeClass = 'badge-cat-energy';
                      $iconBgStyle = 'background: #FFFBEB; color: #B45309;';
                    }
                    $pubDate = !empty($policy['publication_date']) ? date('M d, Y', strtotime($policy['publication_date'])) : '2026';
                    ?>
                    <div
                      class="featured-policy-card d-flex flex-column flex-sm-row align-items-start align-items-sm-center justify-content-between gap-3">
                      <div class="d-flex align-items-start gap-3">
                        <div class="featured-icon-box" style="<?= $iconBgStyle ?>">
                          <i class="bi <?= $iconClass ?>"></i>
                        </div>
                        <div>
                          <div class="d-flex align-items-center gap-2 mb-1">
                            <span class="badge rounded-pill fw-semibold <?= $badgeClass ?>"
                              style="font-size: 0.72rem; letter-spacing: 0.3px;"><?= htmlspecialchars($cat) ?></span>
                            <span class="text-muted small d-none d-md-inline" style="font-size: 0.75rem;"><i
                                class="bi bi-calendar3 me-1 text-secondary opacity-75"></i><?= $pubDate ?></span>
                          </div>
                          <div class="fw-bold text-dark mb-1" style="font-size: 0.92rem; line-height: 1.35;">
                            <?= htmlspecialchars($policy['title']) ?>
                          </div>
                          <p class="text-secondary mb-0" style="font-size: 0.82rem; line-height: 1.45;">
                            <?= htmlspecialchars($policy['description'] ?? 'Manila City Ordinance official provisions and guidelines.') ?>
                          </p>
                        </div>
                      </div>
                      <button type="button" class="btn btn-read-ordinance flex-shrink-0 align-self-sm-center"
                        onclick="viewPolicyDetails(<?= json_encode($policy['title']) ?>, <?= json_encode($policy['category']) ?>, <?= json_encode($policy['publication_date'] ?? '') ?>, <?= json_encode($policy['description'] ?? '') ?>)">
                        <i class="bi bi-file-earmark-text"></i> Read Details
                      </button>
                    </div>
                  <?php endforeach; ?>
                </div>
              </div>
            </div>

            <!-- Recent Updates Card -->
            <div class="col-lg-5">
              <div class="card border-0 shadow-sm rounded-4 p-4 bg-white h-100">
                <div class="d-flex align-items-center justify-content-between mb-4">
                  <div class="d-flex align-items-center gap-2">
                    <div
                      class="rounded-3 p-1.5 bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center"
                      style="width: 34px; height: 34px;">
                      <i class="bi bi-clock-history fs-6 text-primary"></i>
                    </div>
                    <div>
                      <h3 class="h6 fw-bold text-dark mb-0">Recent Updates</h3>
                      <span class="text-muted small" style="font-size: 0.78rem;">Real-time legislative activity
                        feed</span>
                    </div>
                  </div>
                  <button class="btn btn-sm btn-link text-primary fw-semibold text-decoration-none p-0"
                    onclick="showSection('policyLibrarySection')">View All <i class="bi bi-arrow-right"></i></button>
                </div>
                <div class="d-flex flex-column gap-2.5">
                  <?php foreach ($recent_updates as $idx => $upd):
                    $rawDate = !empty($upd['publication_date']) ? $upd['publication_date'] : ($upd['created_at'] ?? date('Y-m-d'));
                    $formattedDate = date('M d, Y', strtotime($rawDate));
                    $updCat = strtoupper($upd['category'] ?? 'GENERAL');

                    $dotStyles = [
                      ['bg' => '#EFF6FF', 'color' => '#2563EB', 'icon' => 'bi-file-earmark-plus-fill'],
                      ['bg' => '#ECFDF5', 'color' => '#059669', 'icon' => 'bi-check-circle-fill'],
                      ['bg' => '#FFFBEB', 'color' => '#D97706', 'icon' => 'bi-clock-fill'],
                      ['bg' => '#F3E8FF', 'color' => '#9333EA', 'icon' => 'bi-journal-text'],
                      ['bg' => '#FFF1F2', 'color' => '#E11D48', 'icon' => 'bi-shield-check']
                    ];
                    $st = $dotStyles[$idx % count($dotStyles)];
                    ?>
                    <div class="update-timeline-item d-flex align-items-start gap-3">
                      <div class="update-icon-dot" style="background: <?= $st['bg'] ?>; color: <?= $st['color'] ?>;">
                        <i class="bi <?= $st['icon'] ?>"></i>
                      </div>
                      <div class="flex-grow-1">
                        <div class="fw-semibold text-dark mb-0.5" style="font-size: 0.86rem; line-height: 1.35;">
                          New policy uploaded: <span class="fw-bold"><?= htmlspecialchars($upd['title']) ?></span>
                        </div>
                        <div class="d-flex align-items-center gap-2 text-muted" style="font-size: 0.76rem;">
                          <span><i class="bi bi-calendar3 me-1 text-primary opacity-75"></i><?= $formattedDate ?></span>
                          <?php if (!empty($upd['category'])): ?>
                            <span>&bull;</span>
                            <span class="badge bg-light text-secondary border px-2 py-0.5 rounded-pill"
                              style="font-weight: 500; font-size: 0.68rem;"><?= htmlspecialchars($upd['category']) ?></span>
                          <?php endif; ?>
                        </div>
                      </div>
                    </div>
                  <?php endforeach; ?>
                </div>
              </div>
            </div>
          </div>
        </section>

        <!-- 2. POLICY RESEARCH SUBMODULE -->
        <?php include 'policy_research.php'; ?>

        <!-- 3. EVALUATIONS SUBMODULE -->
        <?php include 'evaluation.php'; ?>

        <!-- 4. COMPARISON SUBMODULE -->
        <?php include 'comparison.php'; ?>

        <!-- 5. REPORTS SUBMODULE -->
        <?php include 'report.php'; ?>

        <!-- 7. PROFILE SUBMODULE -->
        <?php include 'profile.php'; ?>

      </main>
    </div>
  </div>

  <!-- 1. Impact Assessment Modal (Official Document Report Layout - Matching Admin & User) -->
  <div class="modal fade" id="evaluationDetailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" style="max-width: 820px;">
      <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden" style="background-color: #ffffff;">

        <!-- Header Close Button -->
        <div class="modal-header border-0 pb-0 justify-content-end bg-white px-4 pt-3">
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body px-4 px-md-5 pb-4 pt-0" style="max-height: 80vh; overflow-y: auto;">

          <!-- Official Document Report Content -->
          <div id="evalReportContent"
            style="font-family: 'Segoe UI', system-ui, -apple-system, sans-serif; color: #1a1a1a;">

            <!-- Document Seal Header -->
            <div class="text-center mb-4">
              <div class="d-flex align-items-center justify-content-center gap-3 mb-2">
                <img src="../assets/images/manilacityhall.svg" alt="Manila Seal"
                  style="width: 70px; height: 70px; object-fit: contain;">
                <div>
                  <h4 class="fw-bold mb-0 text-uppercase" style="letter-spacing: 2px; font-size: 1.35rem; color: #000;">
                    MANILA CITY HALL</h4>
                  <div class="fw-bold text-uppercase text-secondary" style="font-size: 0.85rem; letter-spacing: 1.5px;">
                    OFFICE OF THE CITY COUNCIL</div>
                </div>
              </div>
              <h2 class="fw-bold text-dark mt-3 mb-2" style="font-size: 1.65rem;">Evaluation Report</h2>
              <div class="d-flex align-items-center justify-content-center gap-2">
                <div style="height: 1px; width: 100px; background-color: #333;"></div>
                <i class="bi bi-bar-chart-line fs-5 text-dark"></i>
                <div style="height: 1px; width: 100px; background-color: #333;"></div>
              </div>
            </div>

            <!-- SECTION 1: EVALUATION INFORMATION -->
            <div class="mb-4">
              <div class="d-flex align-items-center gap-2 mb-3">
                <i class="bi bi-file-earmark-text fs-5 text-dark"></i>
                <h5 class="fw-bold text-uppercase mb-0" style="font-size: 0.95rem; letter-spacing: 1px;">EVALUATION
                  INFORMATION</h5>
              </div>
              <div class="ps-4" style="font-size: 0.95rem; line-height: 1.8;">
                <div class="row mb-1">
                  <div class="col-4 col-sm-3 fw-bold">Policy Title</div>
                  <div class="col-1 text-center">:</div>
                  <div class="col-7 col-sm-8 fw-semibold text-dark" id="evalModalTitle">Flood Risk Assessment and
                    Drainage Improvement Plan for Manila City</div>
                </div>
                <div class="row mb-1">
                  <div class="col-4 col-sm-3 fw-bold">Status</div>
                  <div class="col-1 text-center">:</div>
                  <div class="col-7 col-sm-8 text-dark">
                    <span id="evalModalStatus" class="badge bg-success px-2.5 py-1">Completed</span>
                  </div>
                </div>
                <div class="row mb-1">
                  <div class="col-4 col-sm-3 fw-bold">Evaluation Date</div>
                  <div class="col-1 text-center">:</div>
                  <div class="col-7 col-sm-8 text-dark" id="evalModalDate">—</div>
                </div>
                <div class="row mb-1">
                  <div class="col-4 col-sm-3 fw-bold">Evaluated By</div>
                  <div class="col-1 text-center">:</div>
                  <div class="col-7 col-sm-8 text-dark" id="evalModalEvaluator">Admin</div>
                </div>
              </div>
            </div>
            <hr style="border-color: #e5e7eb; opacity: 0.8;" class="my-4">

            <!-- SECTION 2: EVALUATION CRITERIA -->
            <div class="mb-4">
              <h5 class="fw-bold text-uppercase mb-3" style="font-size: 0.95rem; letter-spacing: 1px;">EVALUATION
                CRITERIA</h5>
              <div class="ps-4">
                <div class="table-responsive">
                  <table class="table table-bordered align-middle mb-0"
                    style="font-size: 0.9rem; border-color: #e5e7eb;">
                    <thead style="background-color: #f8fafc;">
                      <tr class="text-uppercase text-secondary fw-bold"
                        style="font-size: 0.75rem; letter-spacing: 0.5px;">
                        <th scope="col" style="padding: 10px 14px; width: 32%;">CRITERIA</th>
                        <th scope="col" style="padding: 10px 14px; width: 68%;">ASSESSMENT &amp; FINDINGS</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr>
                        <td style="padding: 10px 14px;" class="fw-bold text-dark">Economic Feasibility</td>
                        <td style="padding: 10px 14px;" class="text-dark" id="evalCriteriaEconomicReason">Funding and
                          implementation costs are manageable and available within municipal allocations.</td>
                      </tr>
                      <tr>
                        <td style="padding: 10px 14px;" class="fw-bold text-dark">Social Impact</td>
                        <td style="padding: 10px 14px;" class="text-dark" id="evalCriteriaSocialReason">The policy
                          provides measurable benefits to affected communities and enhances public welfare.</td>
                      </tr>
                      <tr>
                        <td style="padding: 10px 14px;" class="fw-bold text-dark">Environmental Impact</td>
                        <td style="padding: 10px 14px;" class="text-dark" id="evalCriteriaEnvReason">The policy satisfies
                          urban environmental standards and sustainability requirements.</td>
                      </tr>
                      <tr>
                        <td style="padding: 10px 14px;" class="fw-bold text-dark">Legal Compliance</td>
                        <td style="padding: 10px 14px;" class="text-dark" id="evalCriteriaLegalReason">Compliant with
                          the Local Government Code and relevant national/local statutory frameworks.</td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
            <hr style="border-color: #e5e7eb; opacity: 0.8;" class="my-4">

            <!-- SECTION 3: ANALYSIS -->
            <div class="mb-4">
              <div class="d-flex align-items-center gap-2 mb-2">
                <i class="bi bi-journal-text fs-5 text-dark"></i>
                <h5 class="fw-bold text-uppercase mb-0" style="font-size: 0.95rem; letter-spacing: 1px;">ANALYSIS</h5>
              </div>
              <div class="ps-4">
                <p class="mb-0 text-dark" id="evalModalAnalysis"
                  style="font-size: 0.95rem; line-height: 1.7; text-align: justify;">
                  The proposed policy measure demonstrates strong statutory alignment with municipal priorities across Economic Feasibility, Social Impact, Environmental Protection, and Legal Compliance criteria.
                </p>
              </div>
            </div>
            <hr style="border-color: #e5e7eb; opacity: 0.8;" class="my-4">

            <!-- SECTION 4: RECOMMENDATION -->
            <div class="mb-4">
              <div class="d-flex align-items-center gap-2 mb-2">
                <i class="bi bi-check-circle-fill fs-5 text-dark"></i>
                <h5 class="fw-bold text-uppercase mb-0" style="font-size: 0.95rem; letter-spacing: 1px;">RECOMMENDATION
                </h5>
              </div>
              <div class="ps-4">
                <div class="mb-2">
                  <span id="evalModalRecommendationType"
                    style="display:inline-block;background:#d1fae5;color:#065f46;border:1px solid #a7f3d0;padding:3px 12px;border-radius:999px;font-size:0.8rem;font-weight:600;">Proceed
                    with Implementation</span>
                </div>
                <h6 class="fw-bold text-dark mb-2" id="evalModalRecommendationTitle"
                  style="font-size: 1rem; line-height: 1.4;">
                  Enact Policy with Enhanced Inter-Agency Coordination and Funding Frameworks
                </h6>
                <p class="text-dark mb-0" id="evalModalReason"
                  style="font-size: 0.95rem; line-height: 1.7; text-align: justify;">
                  The plan addresses a fundamental vulnerability in Manila's urban infrastructure that causes recurring
                  economic losses, though its long-term success requires regional watershed integration and sustainable
                  maintenance funding.
                </p>
              </div>
            </div>
            <hr style="border-color: #e5e7eb; opacity: 0.8;" class="my-4">

            <!-- SECTION 5: SUGGESTED IMPROVEMENTS -->
            <div class="mb-4">
              <div class="d-flex align-items-center gap-2 mb-2">
                <i class="bi bi-lightbulb-fill fs-5 text-dark"></i>
                <h5 class="fw-bold text-uppercase mb-0" style="font-size: 0.95rem; letter-spacing: 1px;">SUGGESTED
                  IMPROVEMENTS</h5>
              </div>
              <div class="ps-4">
                <div class="text-dark" id="evalModalImprovements" style="font-size: 0.95rem; line-height: 1.7;">
                  <ul class="mb-0 ps-3">
                    <li class="mb-2">Incorporate nature-based infrastructure solutions, such as bioswales and permeable
                      pavements, alongside traditional engineering upgrades.</li>
                    <li class="mb-2">Establish a formal joint task force with adjacent Metro Manila local government
                      units to address cross-boundary stormwater flow.</li>
                    <li class="mb-0">Develop a multi-year dedicated maintenance fund and real-time public asset
                      management dashboard to ensure operational longevity.</li>
                  </ul>
                </div>
              </div>
            </div>

          </div>
        </div>

        <!-- Footer Actions Bar -->
        <div class="modal-footer bg-white border-top px-4 py-3 justify-content-between">
          <div class="d-flex align-items-center gap-2 text-muted" style="font-size: 0.78rem; font-family: sans-serif;">
            <i class="bi bi-bar-chart-line text-primary fs-5"></i>
            <div>
              <div class="fw-semibold text-dark">Official Impact Evaluation System</div>
              <div>Legislative Administration System &bull; Manila City Hall</div>
            </div>
          </div>
          <div class="d-flex align-items-center gap-2" style="font-family: sans-serif;">
            <button type="button" class="btn btn-secondary rounded-3 px-3" data-bs-dismiss="modal">Close</button>
          </div>
        </div>

      </div>
    </div>
  </div>
  <div class="modal fade" id="policyDetailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
      <div class="modal-content border-0 shadow rounded-4">
        <div class="modal-header border-0 pb-0 bg-primary bg-opacity-5 rounded-top-4">
          <h5 class="modal-title fw-bold text-dark"><i class="bi bi-journal-text text-primary me-2"></i> Policy Record
            Details</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body p-4">
          <div class="d-flex align-items-center gap-2 mb-3 flex-wrap">
            <span class="badge bg-primary py-2 px-3 fw-bold fs-7" id="modalPolicyCategory">Category</span>
            <span class="badge bg-secondary py-2 px-3 fw-bold fs-7" id="modalPolicyStatus">Status</span>
          </div>
          <h4 class="fw-bold text-dark mb-1" id="modalPolicyTitle">Title</h4>
          <div class="small text-muted mb-1"><i class="bi bi-person me-1"></i> Author: <span id="modalPolicyAuthor"
              class="fw-semibold text-dark">-</span></div>
          <div class="small text-muted mb-3"><i class="bi bi-calendar3 me-1"></i> Publication Date: <span
              id="modalPolicyDate" class="fw-semibold text-dark">-</span></div>
          <hr>
          <h6 class="fw-bold text-dark mb-2">Executive Summary &amp; Purpose:</h6>
          <p class="text-muted" id="modalPolicyDesc">Description</p>
          <div id="modalPolicyFileWrapper" class="mt-3" style="display:none;">
            <a id="modalPolicyFileLink" href="#" target="_blank" class="btn btn-sm btn-outline-primary rounded-3">
              <i class="bi bi-file-earmark-pdf me-1"></i> View Document File
            </a>
          </div>
        </div>
        <div class="modal-footer border-0 pt-0">
          <button type="button" class="btn btn-secondary rounded-3" data-bs-dismiss="modal">Close</button>
          <a id="modalDownloadBtn" href="#" download class="btn btn-success rounded-3" style="display:none;"><i
              class="bi bi-download me-1"></i> Download Document</a>
        </div>
      </div>
    </div>
  </div>

  <!-- 2. AI Summary Modal (Official Document Report Layout - matches Admin design) -->
  <div class="modal fade" id="aiSummaryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" style="max-width: 820px;">
      <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden" style="background-color: #ffffff;">

        <!-- Header Close Button -->
        <div class="modal-header border-0 pb-0 justify-content-end bg-white px-4 pt-3">
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body px-4 px-md-5 pb-4 pt-0" id="aiReportPrintableArea"
          style="max-height: 80vh; overflow-y: auto;">

          <!-- Official Document Summary Report Content -->
          <div id="userAiSummaryContent" style="font-family: 'Times New Roman', Times, serif; color: #1a1a1a;">

            <!-- Document Seal Header -->
            <div class="text-center mb-4">
              <div class="d-flex align-items-center justify-content-center gap-3 mb-2">
                <img src="../assets/images/manilacityhall.svg" alt="Manila Seal"
                  style="width: 70px; height: 70px; object-fit: contain;">
                <div>
                  <h4 class="fw-bold mb-0 text-uppercase" style="letter-spacing: 2px; font-size: 1.35rem; color: #000;">
                    MANILA CITY HALL</h4>
                  <div class="fw-bold text-uppercase text-secondary" style="font-size: 0.85rem; letter-spacing: 1.5px;">
                    OFFICE OF THE CITY COUNCIL</div>
                </div>
              </div>
              <h2 class="fw-bold text-dark mt-3 mb-2" style="font-size: 1.65rem;">AI Document Summary Report</h2>
              <div class="d-flex align-items-center justify-content-center gap-2">
                <div style="height: 1px; width: 100px; background-color: #333;"></div>
                <i class="bi bi-bank fs-5 text-dark"></i>
                <div style="height: 1px; width: 100px; background-color: #333;"></div>
              </div>
            </div>

            <!-- SECTION 1: DOCUMENT INFORMATION -->
            <div class="mb-4">
              <div class="d-flex align-items-center gap-2 mb-3">
                <i class="bi bi-file-earmark-text fs-5 text-dark"></i>
                <h5 class="fw-bold text-uppercase mb-0" style="font-size: 0.95rem; letter-spacing: 1px;">DOCUMENT
                  INFORMATION</h5>
              </div>
              <div class="ps-4" style="font-size: 0.95rem; line-height: 1.8;">
                <div class="row mb-1">
                  <div class="col-4 col-sm-3 fw-bold">Document Title</div>
                  <div class="col-1 text-center">:</div>
                  <div class="col-7 col-sm-8 fw-semibold text-dark" id="uAiSum_title">—</div>
                </div>
                <div class="row mb-1">
                  <div class="col-4 col-sm-3 fw-bold">Category</div>
                  <div class="col-1 text-center">:</div>
                  <div class="col-7 col-sm-8 text-dark" id="uAiSum_category">—</div>
                </div>
                <div class="row mb-1">
                  <div class="col-4 col-sm-3 fw-bold">Date Generated</div>
                  <div class="col-1 text-center">:</div>
                  <div class="col-7 col-sm-8 text-dark" id="uAiSum_date">—</div>
                </div>
                <div class="row mb-1">
                  <div class="col-4 col-sm-3 fw-bold">Generated By</div>
                  <div class="col-1 text-center">:</div>
                  <div class="col-7 col-sm-8 text-dark">Gemini AI &bull; Legislative Research Office</div>
                </div>
              </div>
            </div>
            <hr style="border-color: #d1d5db; opacity: 0.7;" class="my-4">

            <!-- SECTION 2: EXECUTIVE SUMMARY -->
            <div class="mb-4">
              <div class="d-flex align-items-center gap-2 mb-2">
                <i class="bi bi-card-text fs-5 text-dark"></i>
                <h5 class="fw-bold text-uppercase mb-0" style="font-size: 0.95rem; letter-spacing: 1px;">EXECUTIVE
                  SUMMARY</h5>
              </div>
              <p class="ps-4 mb-0 text-dark" id="uAiSum_summary"
                style="font-size: 0.95rem; line-height: 1.7; text-align: justify;">
                —
              </p>
            </div>
            <hr style="border-color: #d1d5db; opacity: 0.7;" class="my-4">

            <!-- SECTION 3: KEY FINDINGS -->
            <div class="mb-4">
              <div class="d-flex align-items-center gap-2 mb-2">
                <i class="bi bi-search fs-5 text-dark"></i>
                <h5 class="fw-bold text-uppercase mb-0" style="font-size: 0.95rem; letter-spacing: 1px;">KEY FINDINGS
                </h5>
              </div>
              <div class="ps-4 text-dark" id="uAiSum_findings" style="font-size: 0.95rem; line-height: 1.7;">
                <ul class="mb-0 ps-3">
                  <li>—</li>
                </ul>
              </div>
            </div>
            <hr style="border-color: #d1d5db; opacity: 0.7;" class="my-4">

            <!-- SECTION 4: POLICY IMPACT -->
            <div class="mb-4">
              <div class="d-flex align-items-center gap-2 mb-2">
                <i class="bi bi-bar-chart-line fs-5 text-dark"></i>
                <h5 class="fw-bold text-uppercase mb-0" style="font-size: 0.95rem; letter-spacing: 1px;">POLICY IMPACT
                </h5>
              </div>
              <p class="ps-4 mb-0 text-dark" id="uAiSum_impact" style="font-size: 0.95rem; line-height: 1.7;">—</p>
            </div>
            <hr style="border-color: #d1d5db; opacity: 0.7;" class="my-4">

            <!-- SECTION 5: CONCLUSION -->
            <div class="mb-4">
              <div class="d-flex align-items-center gap-2 mb-2">
                <i class="bi bi-check2-square fs-5 text-dark"></i>
                <h5 class="fw-bold text-uppercase mb-0" style="font-size: 0.95rem; letter-spacing: 1px;">CONCLUSION</h5>
              </div>
              <p class="ps-4 mb-0 text-dark" id="uAiSum_conclusion" style="font-size: 0.95rem; line-height: 1.7;">—</p>
            </div>

          </div>
        </div>

        <!-- Footer Actions Bar -->
        <div class="modal-footer bg-white border-top px-4 py-3 justify-content-between">
          <div class="d-flex align-items-center gap-2 text-muted" style="font-size: 0.78rem; font-family: sans-serif;">
            <i class="bi bi-stars text-primary fs-5"></i>
            <div>
              <div class="fw-semibold text-dark">Generated by AI Document Summarization</div>
              <div>Legislative Administration System &bull; Manila City Hall</div>
            </div>
          </div>
          <div style="font-family: sans-serif;">
            <button type="button" class="btn btn-secondary rounded-3" data-bs-dismiss="modal">Close</button>
          </div>
        </div>

      </div>
    </div>
  </div>



  <!-- 2. Impact Detail View Modal -->
  <div class="modal fade" id="impactModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content border-0 shadow rounded-4">
        <div class="modal-header border-0 pb-0">
          <h5 class="modal-title fw-bold text-dark"><i class="bi bi-bar-chart-line text-warning me-2"></i> Impact
            Evaluation Scorecard</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body p-4">
          <h6 class="fw-bold text-dark mb-1" id="impactTitle">Title</h6>
          <div class="d-flex gap-2 my-2">
            <span class="badge bg-success p-2 fs-6" id="impactScore">Score</span>
            <span class="badge bg-light text-dark border p-2" id="impactRisk">Risk</span>
          </div>
          <p class="small text-muted mt-3" id="impactSummary">Summary</p>
        </div>
        <div class="modal-footer border-0 pt-0">
          <button type="button" class="btn btn-secondary rounded-3" data-bs-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>

  <!-- 3. Upload Policy Modal -->
  <div class="modal fade" id="uploadPolicyModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content border-0 shadow rounded-4">
        <div class="modal-header border-0 pb-0">
          <h5 class="modal-title fw-bold text-dark"><i class="bi bi-upload text-warning me-2"></i> Upload Policy Record
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form method="POST" action="user_dashboard.php" enctype="multipart/form-data">
          <input type="hidden" name="action" value="add">
          <input type="hidden" name="section" value="policyLibrarySection">
          <div class="modal-body p-4" style="max-height: 70vh; overflow-y: auto;">
            <div class="row g-3">
              <div class="col-md-12">
                <label class="form-label fw-semibold small">Research Title <span class="text-danger">*</span></label>
                <input type="text" name="title" class="form-control" placeholder="e.g. Urban Traffic Study" required>
              </div>
              <div class="col-md-6">
                <label class="form-label fw-semibold small">Category <span class="text-danger">*</span></label>
                <select name="category" class="form-select" required>
                  <option value="Health and Sanitation">Health and Sanitation</option>
                  <option value="Civil Registry and Public Services">Civil Registry and Public Services</option>
                  <option value="Education and Employment">Education and Employment</option>
                  <option value="Social Welfare and Community Affairs">Social Welfare and Community Affairs</option>
                  <option value="Infrastructure, Traffic and Environment">Infrastructure, Traffic and Environment
                  </option>
                  <option value="Other">Other</option>
                </select>
              </div>
              <div class="col-md-6">
                <label class="form-label fw-semibold small">Author(s) <span class="text-danger">*</span></label>
                <input type="text" name="author" class="form-control" placeholder="e.g. Staff Researcher" required>
              </div>
              <div class="col-md-6">
                <label class="form-label fw-semibold small">Department/Office <span class="text-danger">*</span></label>
                <input type="text" name="department" class="form-control" placeholder="e.g. Legislative Secretariat"
                  required>
              </div>
              <div class="col-md-6">
                <label class="form-label fw-semibold small">Publication Date</label>
                <input type="date" name="publication_date" class="form-control" min="<?= date('Y-m-d') ?>">
              </div>
              <div class="col-md-12">
                <label class="form-label fw-semibold small">Research Description</label>
                <textarea name="description" class="form-control" rows="3"
                  placeholder="Brief summary of the research..."></textarea>
              </div>
              <div class="col-md-12">
                <label class="form-label fw-semibold small">Upload Document <span class="text-danger">*</span></label>
                <input type="file" id="researchFileInput" name="research_file" class="form-control"
                  accept=".pdf,.docx,.doc" required>
              </div>
            </div>
          </div>
          <div class="modal-footer border-0 pt-0">
            <button type="button" class="btn btn-secondary rounded-3" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-warning fw-semibold rounded-3 text-dark">Upload Record</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- 4. Edit Policy Modal -->
  <div class="modal fade" id="editPolicyModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content border-0 shadow rounded-4">
        <div class="modal-header border-0 pb-0">
          <h5 class="modal-title fw-bold text-dark"><i class="bi bi-pencil-square text-primary me-2"></i> Edit Policy
            Record</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form method="POST" action="user_dashboard.php" enctype="multipart/form-data">
          <input type="hidden" name="action" value="edit">
          <input type="hidden" name="id" id="edit_id">
          <input type="hidden" name="section" value="policyLibrarySection">
          <div class="modal-body p-4" style="max-height: 70vh; overflow-y: auto;">
            <div class="row g-3">
              <div class="col-md-12">
                <label class="form-label fw-semibold small">Research Title <span class="text-danger">*</span></label>
                <input type="text" name="title" id="edit_title" class="form-control" required>
              </div>
              <div class="col-md-6">
                <label class="form-label fw-semibold small">Category <span class="text-danger">*</span></label>
                <select name="category" id="edit_category" class="form-select" required>
                  <option value="Health and Sanitation">Health and Sanitation</option>
                  <option value="Civil Registry and Public Services">Civil Registry and Public Services</option>
                  <option value="Education and Employment">Education and Employment</option>
                  <option value="Social Welfare and Community Affairs">Social Welfare and Community Affairs</option>
                  <option value="Infrastructure, Traffic and Environment">Infrastructure, Traffic and Environment
                  </option>
                  <option value="Other">Other</option>
                </select>
              </div>
              <div class="col-md-6">
                <label class="form-label fw-semibold small">Author(s) <span class="text-danger">*</span></label>
                <input type="text" name="author" id="edit_author" class="form-control" required>
              </div>
              <div class="col-md-6">
                <label class="form-label fw-semibold small">Department/Office <span class="text-danger">*</span></label>
                <input type="text" name="department" id="edit_department" class="form-control" required>
              </div>
              <div class="col-md-6">
                <label class="form-label fw-semibold small">Publication Date</label>
                <input type="date" name="publication_date" id="edit_publication_date" class="form-control"
                  min="<?= date('Y-m-d') ?>">
              </div>
              <div class="col-md-12">
                <label class="form-label fw-semibold small">Research Description</label>
                <textarea name="description" id="edit_description" class="form-control" rows="3"></textarea>
              </div>
              <div class="col-md-6">
                <label class="form-label fw-semibold small">Keywords</label>
                <input type="text" name="keywords" id="edit_keywords" class="form-control">
              </div>
              <div class="col-md-6">
                <label class="form-label fw-semibold small">Update Document (Optional)</label>
                <input type="file" name="research_file" class="form-control" accept=".pdf,.docx,.doc">
                <small class="text-muted">Leave empty to keep existing file</small>
              </div>
            </div>
          </div>
          <div class="modal-footer border-0 pt-0">
            <button type="button" class="btn btn-secondary rounded-3" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary fw-semibold rounded-3">Save Changes</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- 4. Change User Password Modal -->
  <div class="modal fade" id="changeUserPasswordModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 500px;">
      <div class="modal-content border-0 shadow rounded-4">
        <div class="modal-header border-0 pb-0">
          <h5 class="modal-title fw-bold text-dark"><i class="bi bi-key-fill text-warning me-2"></i> Change Password
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form
          onsubmit="alert('Account password updated successfully!'); bootstrap.Modal.getInstance(this.closest('.modal')).hide(); return false;">
          <div class="modal-body p-4">
            <div class="row g-3">
              <div class="col-md-12">
                <label class="form-label fw-semibold small">Current Password</label>
                <input type="password" class="form-control" placeholder="Enter current password" required>
              </div>
              <div class="col-md-12">
                <label class="form-label fw-semibold small">New Password</label>
                <input type="password" class="form-control" placeholder="Enter new strong password" required>
              </div>
              <div class="col-md-12">
                <label class="form-label fw-semibold small">Confirm New Password</label>
                <input type="password" class="form-control" placeholder="Re-enter new password" required>
              </div>
            </div>
          </div>
          <div class="modal-footer border-0 pt-0">
            <button type="button" class="btn btn-secondary rounded-3" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-warning fw-semibold rounded-3 px-4">Update Password</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Bootstrap 5.3 & Chart.js & PDF.js Scripts -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
  <script>if (window.pdfjsLib) pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';</script>

  <!-- PHP -> JS config bridge -->
  <script>
    window.USER_DASHBOARD_DATA = {
      categories: {
        labels: <?= json_encode(array_keys($cat_data_map)) ?>,
        data: <?= json_encode(array_values($cat_data_map)) ?>
      },
      timeline: {
        labels: <?= json_encode($timeline_labels) ?>,
        data: <?= json_encode($timeline_data) ?>
      }
    };
  </script>
  <script src="../assets/js/users.js?v=<?= time() ?>"></script>
  <script src="../assets/js/admin.js?v=<?= time() ?>"></script>
</body>

</html>