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

if (!function_exists('renderUserPolicyCategoryBadge')) {
  function renderUserPolicyCategoryBadge($category)
  {
    $cat = trim($category ?? '');
    $lower = strtolower($cat);

    // 1. Health and Sanitation (#E1F5EE, #085041)
    if (strpos($lower, 'health') !== false || strpos($lower, 'sanitation') !== false || strpos($lower, 'medical') !== false) {
      $bg = '#E1F5EE';
      $text = '#085041';
      $border = '#9FE1CB';
      $icon = 'bi-heart-pulse-fill';
      $label = !empty($cat) ? $cat : 'Health and Sanitation';
    }
    // 2. Civil Registry and Public Services (#E6F1FB, #0C447C)
    elseif (strpos($lower, 'civil') !== false || strpos($lower, 'registry') !== false || strpos($lower, 'public') !== false || strpos($lower, 'governance') !== false || strpos($lower, 'legal') !== false) {
      $bg = '#E6F1FB';
      $text = '#0C447C';
      $border = '#B5D7F8';
      $icon = 'bi-file-earmark-person-fill';
      $label = !empty($cat) ? $cat : 'Civil Registry and Public Services';
    }
    // 3. Education and Employment (#EEEDFE, #3C3489)
    elseif (strpos($lower, 'education') !== false || strpos($lower, 'employment') !== false || strpos($lower, 'school') !== false || strpos($lower, 'labor') !== false || strpos($lower, 'livelihood') !== false) {
      $bg = '#EEEDFE';
      $text = '#3C3489';
      $border = '#CBC6FC';
      $icon = 'bi-mortarboard-fill';
      $label = !empty($cat) ? $cat : 'Education and Employment';
    }
    // 4. Social Welfare and Community Affairs (#FAECE7, #712B13)
    elseif (strpos($lower, 'social') !== false || strpos($lower, 'welfare') !== false || strpos($lower, 'community') !== false) {
      $bg = '#FAECE7';
      $text = '#712B13';
      $border = '#F3C4B6';
      $icon = 'bi-people-fill';
      $label = !empty($cat) ? $cat : 'Social Welfare and Community Affairs';
    }
    // 5. Infrastructure, Traffic and Environment (#EAF3DE, #27500A)
    elseif (strpos($lower, 'infrastructure') !== false || strpos($lower, 'traffic') !== false || strpos($lower, 'environment') !== false || strpos($lower, 'transport') !== false || strpos($lower, 'mobility') !== false) {
      $bg = '#EAF3DE';
      $text = '#27500A';
      $border = '#C8E2AE';
      $icon = 'bi-buildings';
      $label = !empty($cat) ? $cat : 'Infrastructure, Traffic and Environment';
    }
    // 6. Other (#F1EFE8, #444441)
    else {
      $bg = '#F1EFE8';
      $text = '#444441';
      $border = '#DCD7C9';
      $icon = 'bi-tag-fill';
      $label = !empty($cat) ? $cat : 'Other';
    }

    return '<span class="category-badge-pill" style="background-color: ' . $bg . ' !important; color: ' . $text . ' !important; border: 1px solid ' . $border . ' !important;" title="' . htmlspecialchars($label) . '">' .
      '<i class="bi ' . $icon . '" style="color: ' . $text . ' !important; opacity: 0.9;"></i>' .
      '<span>' . htmlspecialchars($label) . '</span>' .
      '</span>';
  }
}

$active_section = $_GET['section'] ?? 'userDashboardSection';
$message = '';
$messageType = '';

// Handle Policy Actions (Add, Edit, Archive, Restore, Delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
  $action = $_POST['action'];

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
    if (!empty($_POST['ajax']) || (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false)) {
      header('Content-Type: application/json');
      echo json_encode([
        'success' => ($messageType === 'success'),
        'message' => $message,
        'policy_id' => $new_policy_id ?? null,
        'title' => $title
      ]);
      exit;
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
  $fq = mysqli_query($conn, "SELECT id, title, category, status, author, publication_date, description, file_path, ai_summary FROM $policy_tbl WHERE (status IS NULL OR status != 'Archived') ORDER BY id DESC LIMIT 20");
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

// Fetch Monthly Uploads Timeline for Councilor Dashboard Line Chart (excludes future dates)
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

$daily_counts_map = [];
$total_month_uploads = 0;
if (!empty($conn)) {
  $tq = mysqli_query($conn, "SELECT DAY(COALESCE(publication_date, created_at)) as up_day, COUNT(*) as cnt FROM $policy_tbl WHERE (status IS NULL OR status != 'Archived') AND (COALESCE(publication_date, created_at) LIKE '$cur_m_prefix%') GROUP BY up_day");
  if ($tq && mysqli_num_rows($tq) > 0) {
    while ($tRow = mysqli_fetch_assoc($tq)) {
      $daily_counts_map[(int) $tRow['up_day']] = (int) $tRow['cnt'];
      $total_month_uploads += (int) $tRow['cnt'];
    }
  }
}

$timeline_labels = [];
$timeline_data = [];

foreach ($days_sample as $day_num) {
  $timeline_labels[] = "$cur_m_name $day_num";
  $cnt = $daily_counts_map[$day_num] ?? 0;
  if ($cnt === 0 && $day_num === 1 && $total_month_uploads === 0 && !empty($recent_updates) && array_sum($timeline_data) === 0) {
    $cnt = count($recent_updates);
  }
  $timeline_data[] = $cnt;
}

// ---------------------------------------------------------------
// Policy Library: fetch all policies with optional search+filter
// ---------------------------------------------------------------
$pl_search = trim($_GET['pl_search'] ?? '');
$pl_category = trim($_GET['pl_category'] ?? '');
$pl_timeframe = trim($_GET['pl_timeframe'] ?? '');
$pl_date_from = trim($_GET['pl_date_from'] ?? '');
$pl_date_to = trim($_GET['pl_date_to'] ?? '');

$pl_policies = [];
if (!empty($conn)) {
  $where_clauses = ["(status IS NULL OR status != 'Archived')"];
  $bind_types = '';
  $bind_values = [];

  if ($pl_search !== '') {
    $where_clauses[] = '(title LIKE ? OR description LIKE ? OR keywords LIKE ? OR author LIKE ?)';
    $like = '%' . $pl_search . '%';
    $bind_types .= 'ssss';
    $bind_values[] = $like;
    $bind_values[] = $like;
    $bind_values[] = $like;
    $bind_values[] = $like;
  }
  if ($pl_category !== '') {
    $where_clauses[] = 'category = ?';
    $bind_types .= 's';
    $bind_values[] = $pl_category;
  }

  if (!empty($pl_date_from) && !empty($pl_date_to)) {
    $where_clauses[] = "((DATE(publication_date) BETWEEN ? AND ?) OR (DATE(created_at) BETWEEN ? AND ?))";
    $bind_types .= 'ssss';
    $bind_values[] = $pl_date_from;
    $bind_values[] = $pl_date_to;
    $bind_values[] = $pl_date_from;
    $bind_values[] = $pl_date_to;
  } elseif (!empty($pl_date_from)) {
    $where_clauses[] = "(DATE(publication_date) >= ? OR DATE(created_at) >= ?)";
    $bind_types .= 'ss';
    $bind_values[] = $pl_date_from;
    $bind_values[] = $pl_date_from;
  } elseif (!empty($pl_date_to)) {
    $where_clauses[] = "(DATE(publication_date) <= ? OR DATE(created_at) <= ?)";
    $bind_types .= 'ss';
    $bind_values[] = $pl_date_to;
    $bind_values[] = $pl_date_to;
  } elseif ($pl_timeframe === 'today') {
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

  $where_sql = 'WHERE ' . implode(' AND ', $where_clauses);

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

    function showSection(sectionId) {
      if (!sectionId) return;
      var sections = document.querySelectorAll('.content-section');
      sections.forEach(function (sec) {
        if (sec.id === sectionId) {
          sec.classList.remove('d-none');
          sec.style.display = '';
        } else {
          sec.classList.add('d-none');
        }
      });
      var navLinks = document.querySelectorAll('.sidebar-nav .nav-link, [data-target]');
      navLinks.forEach(function (link) {
        var target = link.getAttribute('data-target');
        var href = link.getAttribute('href') || '';
        var onclick = link.getAttribute('onclick') || '';
        if (target === sectionId || (href && href.indexOf(sectionId) !== -1) || (onclick && onclick.indexOf(sectionId) !== -1)) {
          link.classList.add('active');
        } else if (target || href || onclick) {
          link.classList.remove('active');
        }
      });
      try {
        sessionStorage.setItem('user_active_section', sectionId);
        var url = new URL(window.location.href);
        url.searchParams.set('section', sectionId);
        window.history.replaceState({}, '', url);
      } catch (e) { }
    }
    window.showSection = showSection;
  </script>
  <!-- Bootstrap 5.3 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <!-- System Custom CSS -->
  <link rel="stylesheet" href="../assets/css/Manila City Hall.css?v=<?= time() ?>">
  <link rel="stylesheet" href="../assets/css/Admin.css?v=<?= time() ?>">
  <style>
    /* Refined Civic Category Badges (Light Background + Dark Text) */
    .category-badge-pill {
      display: inline-flex !important;
      align-items: center !important;
      gap: 6.5px !important;
      padding: 4px 12px !important;
      border-radius: 9999px !important;
      font-size: 0.78rem !important;
      font-weight: 600 !important;
      white-space: nowrap !important;
      letter-spacing: -0.01em !important;
      line-height: 1.35 !important;
      transition: all 0.18s cubic-bezier(0.16, 1, 0.3, 1) !important;
      box-shadow: 0 1px 2px rgba(0, 0, 0, 0.03) !important;
    }

    .category-badge-pill i {
      font-size: 0.82rem !important;
      flex-shrink: 0 !important;
    }

    /* Civic Slate Metadata Chip */
    .report-date-cell,
    .report-date-badge {
      display: inline-flex !important;
      align-items: center !important;
      gap: 6px !important;
      font-size: 0.78rem !important;
      color: #334155 !important;
      font-weight: 600 !important;
      font-variant-numeric: tabular-nums !important;
      white-space: nowrap !important;
      letter-spacing: -0.01em !important;
      background: #F8FAFC !important;
      border: 1px solid #E2E8F0 !important;
      border-radius: 7px !important;
      padding: 3.5px 9px !important;
      box-shadow: 0 1px 2px rgba(0, 0, 0, 0.02) !important;
      transition: all 0.18s cubic-bezier(0.16, 1, 0.3, 1) !important;
    }

    .report-date-cell i,
    .report-date-badge i {
      color: #0B2E59 !important;
      opacity: 0.85 !important;
      font-size: 0.82rem !important;
    }

    /* Executive View All Pill Button */
    .btn-view-all-pill {
      display: inline-flex !important;
      align-items: center !important;
      gap: 6px !important;
      background: #EFF6FF !important;
      color: #0B2E59 !important;
      border: 1px solid #BFDBFE !important;
      border-radius: 9999px !important;
      padding: 5px 14px !important;
      font-size: 0.78rem !important;
      font-weight: 600 !important;
      text-decoration: none !important;
      transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1) !important;
    }

    .btn-view-all-pill:hover {
      background: #0B2E59 !important;
      color: #FFFFFF !important;
      border-color: #0B2E59 !important;
      transform: translateY(-1px) !important;
      box-shadow: 0 3px 8px rgba(11, 46, 89, 0.2) !important;
    }

    .btn-view-all-pill i {
      transition: transform 0.2s ease !important;
    }

    .btn-view-all-pill:hover i {
      transform: translateX(2px) !important;
    }

    .min-w-0 {
      min-width: 0 !important;
    }

    /* Rich & Executive Featured Ordinance Card */
    .featured-policy-card {
      background: #FFFFFF !important;
      border: 1px solid #E2E8F0 !important;
      border-left: 3.5px solid #CBD5E1 !important;
      border-radius: 14px !important;
      padding: 16px 18px !important;
      transition: all 0.22s cubic-bezier(0.16, 1, 0.3, 1) !important;
      box-shadow: 0 2px 6px rgba(11, 46, 89, 0.03) !important;
      position: relative !important;
      width: 100% !important;
      max-width: 100% !important;
      box-sizing: border-box !important;
      overflow: hidden !important;
    }

    .featured-policy-card:hover {
      transform: translateY(-2px) !important;
      box-shadow: 0 10px 24px rgba(11, 46, 89, 0.09) !important;
      border-color: #94A3B8 !important;
      border-left-color: #0B2E59 !important;
      background: #FFFFFF !important;
    }

    .featured-policy-card:hover .policy-card-title {
      color: #0B2E59 !important;
    }

    .featured-icon-box {
      width: 44px !important;
      height: 44px !important;
      border-radius: 12px !important;
      display: inline-flex !important;
      align-items: center !important;
      justify-content: center !important;
      font-size: 1.25rem !important;
      flex-shrink: 0 !important;
      box-shadow: 0 2px 6px rgba(0, 0, 0, 0.04) !important;
      transition: transform 0.2s ease !important;
    }

    .featured-policy-card:hover .featured-icon-box {
      transform: scale(1.06) !important;
    }

    .policy-card-title {
      color: #0F172A !important;
      font-weight: 700 !important;
      font-size: 0.94rem !important;
      line-height: 1.35 !important;
      transition: color 0.18s ease !important;
      text-decoration: none !important;
      display: -webkit-box !important;
      -webkit-line-clamp: 2 !important;
      -webkit-box-orient: vertical !important;
      overflow: hidden !important;
      word-break: break-word !important;
      overflow-wrap: break-word !important;
      white-space: normal !important;
    }

    /* Executive Manila Navy & Gold Action Button */
    .btn-read-ordinance {
      background: #0B2E59 !important;
      color: #FFFFFF !important;
      border: 1px solid #082242 !important;
      border-radius: 8px !important;
      padding: 7.5px 16px !important;
      font-size: 0.81rem !important;
      font-weight: 600 !important;
      display: inline-flex !important;
      align-items: center !important;
      gap: 7px !important;
      white-space: nowrap !important;
      flex-shrink: 0 !important;
      margin-left: auto !important;
      transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1) !important;
      text-decoration: none !important;
      box-shadow: 0 1px 3px rgba(11, 46, 89, 0.2), inset 0 1px 0 rgba(255, 255, 255, 0.12) !important;
    }

    .btn-read-ordinance i.bi-file-earmark-text {
      color: #FCD34D !important;
      font-size: 0.88rem !important;
      transition: transform 0.2s ease !important;
    }

    .btn-read-ordinance i.bi-arrow-right-short {
      font-size: 1.05rem !important;
      transition: transform 0.2s ease !important;
      margin-left: -2px !important;
    }

    .btn-read-ordinance:hover {
      background: #123E75 !important;
      border-color: #123E75 !important;
      color: #FFFFFF !important;
      box-shadow: 0 4px 12px rgba(11, 46, 89, 0.3) !important;
      transform: translateY(-1px) !important;
    }

    .btn-read-ordinance:hover i.bi-file-earmark-text {
      transform: scale(1.1) !important;
    }

    .btn-read-ordinance:hover i.bi-arrow-right-short {
      transform: translateX(2px) !important;
    }

    /* Sleek Activity Feed for Recent Updates */
    .update-timeline-item {
      background: #FFFFFF !important;
      border: 1px solid #E2E8F0 !important;
      border-left: 3.5px solid #CBD5E1 !important;
      border-radius: 12px !important;
      padding: 12px 14px !important;
      transition: all 0.22s cubic-bezier(0.16, 1, 0.3, 1) !important;
      box-shadow: 0 1px 4px rgba(11, 46, 89, 0.02) !important;
      cursor: pointer !important;
      width: 100% !important;
      max-width: 100% !important;
      box-sizing: border-box !important;
      overflow: hidden !important;
    }

    .update-timeline-item:hover {
      transform: translateY(-1.5px) !important;
      box-shadow: 0 8px 18px rgba(11, 46, 89, 0.07) !important;
      border-color: #94A3B8 !important;
      border-left-color: #0B2E59 !important;
      background: #FFFFFF !important;
    }

    .update-icon-dot {
      width: 36px !important;
      height: 36px !important;
      border-radius: 10px !important;
      display: inline-flex !important;
      align-items: center !important;
      justify-content: center !important;
      font-size: 0.95rem !important;
      flex-shrink: 0 !important;
      box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04) !important;
      transition: transform 0.2s ease !important;
    }

    .update-timeline-item:hover .update-icon-dot {
      transform: scale(1.08) !important;
    }

    .update-policy-title {
      color: #0F172A !important;
      font-weight: 700 !important;
      font-size: 0.88rem !important;
      line-height: 1.35 !important;
      transition: color 0.18s ease !important;
      display: -webkit-box !important;
      -webkit-line-clamp: 2 !important;
      -webkit-box-orient: vertical !important;
      overflow: hidden !important;
      word-break: break-word !important;
      overflow-wrap: break-word !important;
      white-space: normal !important;
    }

    .update-timeline-item:hover .update-policy-title {
      color: #0B2E59 !important;
    }

    /* Refined Policy Record Modal Styling */
    .policy-modal-dialog {
      max-width: 780px !important;
    }
    .policy-modal-content {
      border: 1px solid rgba(226, 232, 240, 0.95) !important;
      border-radius: 18px !important;
      box-shadow: 0 25px 60px -15px rgba(11, 46, 89, 0.3) !important;
      background: #F8FAFC !important;
      overflow: hidden !important;
    }
    .policy-modal-header {
      background: linear-gradient(135deg, #071D3A 0%, #0B2E59 55%, #123E75 100%) !important;
      border-bottom: 3px solid #F59E0B !important;
      padding: 18px 24px !important;
    }
    .policy-modal-close-btn {
      width: 32px !important;
      height: 32px !important;
      border-radius: 50% !important;
      background: rgba(255, 255, 255, 0.14) !important;
      color: #FFFFFF !important;
      border: 1px solid rgba(255, 255, 255, 0.2) !important;
      display: inline-flex !important;
      align-items: center !important;
      justify-content: center !important;
      opacity: 0.9 !important;
      transition: all 0.2s ease !important;
      cursor: pointer !important;
    }
    .policy-modal-close-btn:hover {
      background: rgba(255, 255, 255, 0.28) !important;
      transform: scale(1.08) !important;
      opacity: 1 !important;
      color: #FFFFFF !important;
    }
    .policy-hero-card {
      background: #FFFFFF !important;
      border: 1px solid #E2E8F0 !important;
      border-radius: 14px !important;
      padding: 20px 22px !important;
      box-shadow: 0 2px 10px rgba(11, 46, 89, 0.04) !important;
      margin-bottom: 14px !important;
    }
    .policy-hero-title {
      font-size: 1.30rem !important;
      font-weight: 800 !important;
      line-height: 1.38 !important;
      letter-spacing: -0.02em !important;
      color: #0F172A !important;
      margin-top: 12px !important;
      margin-bottom: 14px !important;
    }
    .policy-meta-pill {
      background: #F8FAFC !important;
      border: 1px solid #E2E8F0 !important;
      border-radius: 10px !important;
      padding: 9px 13px !important;
      display: flex !important;
      align-items: center !important;
      gap: 10px !important;
      flex: 1 1 0 !important;
      min-width: 0 !important;
      transition: border-color 0.2s ease !important;
    }
    .policy-meta-pill:hover {
      border-color: #CBD5E1 !important;
    }
    .policy-summary-card {
      background: #FFFFFF !important;
      border: 1px solid #E2E8F0 !important;
      border-left: 4px solid #0B2E59 !important;
      border-radius: 12px !important;
      padding: 16px 18px !important;
      box-shadow: 0 1px 4px rgba(11, 46, 89, 0.02) !important;
      margin-bottom: 14px !important;
    }
    .policy-doc-card {
      background: linear-gradient(135deg, #F0FDF4 0%, #DCFCE7 100%) !important;
      border: 1px solid #86EFAC !important;
      border-radius: 14px !important;
      padding: 14px 18px !important;
      box-shadow: 0 2px 8px rgba(22, 101, 52, 0.05) !important;
    }
    .btn-modal-preview {
      background: #16A34A !important;
      color: #FFFFFF !important;
      border: 1px solid #15803D !important;
      font-weight: 600 !important;
      font-size: 0.82rem !important;
      padding: 7px 16px !important;
      border-radius: 8px !important;
      display: inline-flex !important;
      align-items: center !important;
      gap: 7px !important;
      transition: all 0.2s ease !important;
      box-shadow: 0 2px 6px rgba(22, 101, 52, 0.18) !important;
      text-decoration: none !important;
    }
    .btn-modal-preview:hover {
      background: #15803D !important;
      color: #FFFFFF !important;
      transform: translateY(-1px) !important;
      box-shadow: 0 4px 12px rgba(22, 101, 52, 0.25) !important;
    }
    .btn-modal-download {
      background: linear-gradient(135deg, #0B2E59 0%, #15437F 100%) !important;
      color: #FFFFFF !important;
      border: 1px solid #082242 !important;
      font-weight: 700 !important;
      font-size: 0.85rem !important;
      padding: 9px 22px !important;
      border-radius: 9px !important;
      display: inline-flex !important;
      align-items: center !important;
      gap: 8px !important;
      box-shadow: 0 4px 14px rgba(11, 46, 89, 0.25) !important;
      transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1) !important;
      text-decoration: none !important;
    }
    .btn-modal-download:hover {
      background: linear-gradient(135deg, #123E75 0%, #1E4E8C 100%) !important;
      color: #FFFFFF !important;
      transform: translateY(-1px) !important;
      box-shadow: 0 6px 18px rgba(11, 46, 89, 0.35) !important;
    }

    body:not(.sidebar-collapsed) .sidebar {
      flex: 0 0 310px !important;
      width: 310px !important;
    }

    body:not(.sidebar-collapsed) .main-panel {
      width: calc(100% - 310px) !important;
      max-width: calc(100% - 310px) !important;
      margin-left: 310px !important;
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

    /* ── MOBILE OVERRIDE: beat the !important desktop rules on screens <= 991px ── */
    @media (max-width: 991px) {
      body:not(.sidebar-collapsed) .sidebar,
      body.sidebar-collapsed .sidebar,
      .sidebar {
        position: fixed !important;
        left: -320px !important;
        top: 0 !important;
        width: 280px !important;
        flex: 0 0 280px !important;
        height: 100vh !important;
        z-index: 1045 !important;
        transition: left 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
        padding: 18px 12px !important;
        overflow-y: auto !important;
        box-shadow: none !important;
      }

      .sidebar.mobile-open {
        left: 0 !important;
        box-shadow: 6px 0 25px rgba(0, 0, 0, 0.5) !important;
      }

      body:not(.sidebar-collapsed) .main-panel,
      body.sidebar-collapsed .main-panel,
      .main-panel {
        margin-left: 0 !important;
        width: 100% !important;
        max-width: 100% !important;
        padding: 0 0 40px !important;
      }

      .content-area {
        padding-left: 12px !important;
        padding-right: 12px !important;
      }

      .topbar {
        margin: 8px 8px 16px 8px !important;
        padding: 8px 12px !important;
        border-radius: 12px !important;
        flex-wrap: nowrap !important;
      }

      .topbar h2.fs-4 {
        font-size: 0.9rem !important;
        line-height: 1.2 !important;
      }

      .topbar .text-secondary.small {
        display: none !important;
      }

      .mobile-menu-btn {
        display: inline-flex !important;
      }

      .header-divider {
        margin: 0 6px !important;
      }

      .dark-mode-switch {
        width: 38px !important;
        height: 22px !important;
      }

      .header-avatar-wrap {
        width: 34px !important;
        height: 34px !important;
        min-width: 34px !important;
        min-height: 34px !important;
        max-width: 34px !important;
        max-height: 34px !important;
      }
    }

    @media (max-width: 480px) {
      .topbar h2.fs-4 {
        display: none !important;
      }

      .topbar {
        padding: 6px 8px !important;
      }

      .content-area {
        padding-left: 8px !important;
        padding-right: 8px !important;
      }
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

  <!-- Mobile sidebar overlay backdrop -->
  <div class="sidebar-overlay" id="sidebarOverlay"></div>

  <div class="app-shell">
    <!-- USER SIDEBAR NAVIGATION -->
    <aside class="sidebar d-flex flex-column p-3" id="mainSidebar">
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
          <i class="bi bi-grid me-2"></i><span class="nav-text">Dashboard</span>
        </a>

        <div class="sidebar-section-label mt-3">LEGISLATIVE</div>
        <a class="nav-link <?= ($active_section === 'policyLibrarySection') ? 'active' : '' ?> py-2.5 px-3 rounded-3"
          href="#" data-target="policyLibrarySection" onclick="showSection('policyLibrarySection');return false;"
          title="Policy Research">
          <i class="bi bi-book me-2"></i><span class="nav-text">Policy Research</span>
        </a>
        <a class="nav-link <?= ($active_section === 'policyComparisonSection') ? 'active' : '' ?> py-2.5 px-3 rounded-3"
          href="#" data-target="policyComparisonSection" onclick="showSection('policyComparisonSection');return false;"
          title="Benchmarks & Comparison">
          <svg xmlns="http://www.w3.org/2000/svg" width="1.15em" height="1.15em" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-2" style="display:inline-block; vertical-align:-0.18em;"><circle cx="10.5" cy="13.5" r="7.5"/><circle cx="10.5" cy="13.5" r="3.5"/><line x1="10.5" y1="13.5" x2="20" y2="4"/><polyline points="15.5 4 20 4 20 8.5"/></svg><span class="nav-text">Benchmarks & Comparison</span>
        </a>
        <a class="nav-link <?= ($active_section === 'policyImpactSection') ? 'active' : '' ?> py-2.5 px-3 rounded-3"
          href="#" data-target="policyImpactSection" onclick="showSection('policyImpactSection');return false;"
          title="Evaluation">
          <i class="bi bi-graph-up-arrow me-2"></i><span class="nav-text">Evaluation</span>
        </a>

        <div class="sidebar-section-label mt-3">REPORTING</div>
        <a class="nav-link <?= ($active_section === 'reportsSection') ? 'active' : '' ?> py-2.5 px-3 rounded-3" href="#"
          data-target="reportsSection" onclick="showSection('reportsSection');return false;" title="Reports">
          <i class="bi bi-file-earmark-text me-2"></i><span class="nav-text">Reports</span>
        </a>
      </nav>
    </aside>

    <!-- MAIN PANEL -->
    <div class="main-panel flex-grow-1">
      <!-- TOPBAR -->
      <header
        class="topbar d-flex align-items-center justify-content-between px-4 py-3 mb-4 shadow-sm bg-white rounded-4 border border-light">
        <div class="d-flex align-items-center gap-3">
          <!-- Mobile hamburger (only visible on <=991px) -->
          <button class="mobile-menu-btn me-1" id="mobileMenuBtn" type="button" aria-label="Open navigation menu" title="Open Menu">
            <i class="bi bi-list"></i>
          </button>
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
          $user_latest_id = !empty($recent_updates) ? (int) $recent_updates[0]['id'] : 0;
          ?>
          <div class="dropdown">
            <button class="header-notif-btn" id="userNotifButton" type="button" data-bs-toggle="dropdown"
              data-latest-id="<?= $user_latest_id ?>" aria-expanded="false" title="Notifications">
              <i class="bi bi-bell fs-5 text-dark"></i>
              <span class="header-notif-badge" id="userNotifBadge" style="display:none;"></span>
            </button>
            <div class="dropdown-menu dropdown-menu-end shadow-lg border-0 rounded-4 p-0 overflow-hidden mt-2"
              style="width: 370px;" aria-labelledby="userNotifButton">
              <div class="px-3 py-3 d-flex align-items-center justify-content-between text-white notif-header"
                style="background: linear-gradient(120deg, #0B2E59, #1a4a8a);">
                <div>
                  <strong class="fs-6 d-block">Notifications</strong>
                  <small class="opacity-75">You have <span id="userNotifUnread">0</span> new
                    updates</small>
                </div>
                <span id="userNotifHeaderBadge" class="badge rounded-pill bg-warning text-dark"><?= $user_notif_count ?>
                  Updates</span>
              </div>
              <div class="p-2" style="max-height: 290px; overflow-y: auto;">
                <ul class="list-group list-group-flush" id="userNotifList">
                  <?php if (!empty($recent_updates)): ?>
                    <?php foreach ($recent_updates as $upd): ?>
                      <?php
                      $upd_id = (int) $upd['id'];
                      $upd_title = htmlspecialchars($upd['title']);
                      $upd_cat = htmlspecialchars($upd['category'] ?? 'Policy');
                      $upd_date = !empty($upd['publication_date']) ? date('M d, Y', strtotime($upd['publication_date'])) : (!empty($upd['created_at']) ? date('M d, Y', strtotime($upd['created_at'])) : 'Recent');
                      ?>
                      <li
                        class="notif-item list-group-item p-2 mb-1 border rounded-3 d-flex justify-content-between align-items-start"
                        data-notif-id="<?= $upd_id ?>" style="cursor: pointer;"
                        onclick="handleUserNotifItemClick('policyLibrarySection', <?= $upd_id ?>);">
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
                <span class="header-admin-role" id="topbarUserRole">Councilor</span>
                <span class="header-admin-pipe">|</span>
                <span id="topbarUserName" class="header-admin-name">Council Member</span>
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
                <h2 class="h4 fw-bold text-dark mb-1" id="userWelcomeHeading">Welcome, Councilor</h2>
                <p class="text-secondary small mb-0" style="font-size: 0.88rem;">
                  Official executive decision hub &bull; Review ordinances, policy evaluations, and legislative reports.
                </p>
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
            <!-- Featured Policies & Recent Updates -->
          <div class="row g-4 mb-4">
            <!-- Featured Ordinances Card -->
            <div class="col-lg-7">
              <div class="card border-0 shadow-sm rounded-4 p-4 bg-white h-100 d-flex flex-column" style="border: 1px solid rgba(226, 232, 240, 0.8) !important; box-shadow: 0 4px 20px -2px rgba(11, 46, 89, 0.05) !important;">
                <div class="d-flex align-items-center justify-content-between mb-3.5 pb-1">
                  <div class="d-flex align-items-center gap-2.5">
                    <div
                      class="rounded-3 p-2 d-flex align-items-center justify-content-center shadow-2xs"
                      style="width: 36px; height: 36px; background: linear-gradient(135deg, #FEF3C7 0%, #FDE68A 100%); color: #B45309;">
                      <i class="bi bi-star-fill fs-6"></i>
                    </div>
                    <div>
                      <h3 class="h6 fw-bold text-dark mb-0" style="font-size: 1.02rem;">Featured Ordinances</h3>
                      <span class="text-muted small" style="font-size: 0.76rem;">Key enactments and policy frameworks</span>
                    </div>
                  </div>
                  <a href="#" class="btn-view-all-pill" onclick="showSection('policyLibrarySection'); return false;">
                    <span>View All</span> <i class="bi bi-arrow-right"></i>
                  </a>
                </div>
                <div class="d-flex flex-column gap-3 flex-grow-1">
                  <?php foreach ($featured_policies as $policy):
                    $cat = trim($policy['category'] ?? 'General Legislation');
                    $lowerCat = strtolower($cat);

                    // Curated vibrant icon styling
                    if (strpos($lowerCat, 'health') !== false || strpos($lowerCat, 'sanitation') !== false || strpos($lowerCat, 'medical') !== false) {
                      $iconClass = 'bi-heart-pulse-fill';
                      $iconBg = 'background: linear-gradient(135deg, #E1F5EE 0%, #C4ECE0 100%); color: #085041; border: 1px solid #9FE1CB;';
                    } elseif (strpos($lowerCat, 'infra') !== false || strpos($lowerCat, 'traffic') !== false || strpos($lowerCat, 'environment') !== false || strpos($lowerCat, 'transport') !== false) {
                      $iconClass = 'bi-buildings';
                      $iconBg = 'background: linear-gradient(135deg, #EAF3DE 0%, #D8EAC2 100%); color: #27500A; border: 1px solid #C8E2AE;';
                    } elseif (strpos($lowerCat, 'social') !== false || strpos($lowerCat, 'welfare') !== false || strpos($lowerCat, 'community') !== false) {
                      $iconClass = 'bi-people-fill';
                      $iconBg = 'background: linear-gradient(135deg, #FAECE7 0%, #F6DDD3 100%); color: #712B13; border: 1px solid #F3C4B6;';
                    } elseif (strpos($lowerCat, 'education') !== false || strpos($lowerCat, 'employment') !== false || strpos($lowerCat, 'school') !== false) {
                      $iconClass = 'bi-mortarboard-fill';
                      $iconBg = 'background: linear-gradient(135deg, #EEEDFE 0%, #DFDCFD 100%); color: #3C3489; border: 1px solid #CBC6FC;';
                    } elseif (strpos($lowerCat, 'civil') !== false || strpos($lowerCat, 'registry') !== false || strpos($lowerCat, 'public') !== false || strpos($lowerCat, 'governance') !== false) {
                      $iconClass = 'bi-file-earmark-person-fill';
                      $iconBg = 'background: linear-gradient(135deg, #E6F1FB 0%, #D1E5F7 100%); color: #0C447C; border: 1px solid #B5D7F8;';
                    } else {
                      $iconClass = 'bi-tag-fill';
                      $iconBg = 'background: linear-gradient(135deg, #F1EFE8 0%, #E5E0D5 100%); color: #444441; border: 1px solid #DCD7C9;';
                    }

                    $pubDate = !empty($policy['publication_date']) ? date('M d, Y', strtotime($policy['publication_date'])) : (!empty($policy['created_at']) ? date('M d, Y', strtotime($policy['created_at'])) : 'Recent');
                    $policyJson = json_encode([
                      "id" => (int) ($policy["id"] ?? 0),
                      "title" => $policy["title"],
                      "category" => $policy["category"] ?? "General Legislation",
                      "author" => $policy["author"] ?? "City Council of Manila",
                      "status" => $policy["status"] ?? "Published",
                      "date" => $pubDate,
                      "desc" => $policy["description"] ?? "Manila City Ordinance official provisions and guidelines.",
                      "file" => $policy["file_path"] ?? ""
                    ], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
                    ?>
                    <div class="featured-policy-card d-flex flex-column flex-sm-row align-items-start align-items-sm-center justify-content-between gap-3">
                      <div class="d-flex align-items-start gap-3 flex-grow-1 min-w-0" style="min-width: 0; max-width: 100%; overflow: hidden;">
                        <div class="featured-icon-box flex-shrink-0" style="<?= $iconBg ?>">
                          <i class="bi <?= $iconClass ?>"></i>
                        </div>
                        <div class="flex-grow-1 min-w-0" style="min-width: 0; max-width: 100%; overflow: hidden;">
                          <div class="d-flex flex-wrap align-items-center gap-2 mb-1.5">
                            <?= renderUserPolicyCategoryBadge($policy['category'] ?? '') ?>
                            <div class="report-date-cell flex-shrink-0">
                              <i class="bi bi-calendar3"></i>
                              <span class="report-date-text"><?= $pubDate ?></span>
                            </div>
                          </div>
                          <div class="mb-1" style="min-width: 0; max-width: 100%;">
                            <a href="javascript:void(0)" class="policy-card-title d-block" onclick='openPolicyViewModal(<?= $policyJson ?>)'>
                              <?= htmlspecialchars($policy['title']) ?>
                            </a>
                          </div>
                          <p class="text-secondary mb-0" style="font-size: 0.82rem; line-height: 1.45; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; word-break: break-word; overflow-wrap: break-word;">
                            <?= htmlspecialchars($policy['description'] ?? 'Manila City Ordinance official provisions and guidelines.') ?>
                          </p>
                        </div>
                      </div>
                      <button type="button" class="btn btn-read-ordinance flex-shrink-0 align-self-sm-center ms-auto"
                        onclick='openPolicyViewModal(<?= $policyJson ?>)'>
                        <i class="bi bi-file-earmark-text"></i>
                        <span>Read Details</span>
                        <i class="bi bi-arrow-right-short"></i>
                      </button>
                    </div>
                  <?php endforeach; ?>
                </div>
              </div>
            </div>

            <!-- Recent Updates Card -->
            <div class="col-lg-5">
              <div class="card border-0 shadow-sm rounded-4 p-4 bg-white h-100 d-flex flex-column" style="border: 1px solid rgba(226, 232, 240, 0.8) !important; box-shadow: 0 4px 20px -2px rgba(11, 46, 89, 0.05) !important;">
                <div class="d-flex align-items-center justify-content-between mb-3.5 pb-1">
                  <div class="d-flex align-items-center gap-2.5">
                    <div
                      class="rounded-3 p-2 d-flex align-items-center justify-content-center shadow-2xs"
                      style="width: 36px; height: 36px; background: linear-gradient(135deg, #E0F2FE 0%, #BAE6FD 100%); color: #0284C7;">
                      <i class="bi bi-clock-history fs-6"></i>
                    </div>
                    <div>
                      <h3 class="h6 fw-bold text-dark mb-0" style="font-size: 1.02rem;">Recent Updates</h3>
                      <span class="text-muted small" style="font-size: 0.76rem;">Real-time legislative activity feed</span>
                    </div>
                  </div>
                  <a href="#" class="btn-view-all-pill" onclick="showSection('policyLibrarySection'); return false;">
                    <span>View All</span> <i class="bi bi-arrow-right"></i>
                  </a>
                </div>
                <div class="d-flex flex-column gap-2.5 flex-grow-1">
                  <?php foreach ($recent_updates as $idx => $upd):
                    $rawDate = !empty($upd['publication_date']) ? $upd['publication_date'] : ($upd['created_at'] ?? date('Y-m-d'));
                    $formattedDate = date('M d, Y', strtotime($rawDate));
                    $updCat = trim($upd['category'] ?? 'General Legislation');
                    $lowerUpdCat = strtolower($updCat);

                    if (strpos($lowerUpdCat, 'health') !== false || strpos($lowerUpdCat, 'sanitation') !== false) {
                      $dotIcon = 'bi-heart-pulse-fill';
                      $dotBg = 'background: linear-gradient(135deg, #E1F5EE 0%, #C4ECE0 100%); color: #085041; border: 1px solid #9FE1CB;';
                    } elseif (strpos($lowerUpdCat, 'infra') !== false || strpos($lowerUpdCat, 'traffic') !== false || strpos($lowerUpdCat, 'environment') !== false) {
                      $dotIcon = 'bi-buildings';
                      $dotBg = 'background: linear-gradient(135deg, #EAF3DE 0%, #D8EAC2 100%); color: #27500A; border: 1px solid #C8E2AE;';
                    } elseif (strpos($lowerUpdCat, 'social') !== false || strpos($lowerUpdCat, 'welfare') !== false) {
                      $dotIcon = 'bi-people-fill';
                      $dotBg = 'background: linear-gradient(135deg, #FAECE7 0%, #F6DDD3 100%); color: #712B13; border: 1px solid #F3C4B6;';
                    } elseif (strpos($lowerUpdCat, 'education') !== false || strpos($lowerUpdCat, 'employment') !== false) {
                      $dotIcon = 'bi-mortarboard-fill';
                      $dotBg = 'background: linear-gradient(135deg, #EEEDFE 0%, #DFDCFD 100%); color: #3C3489; border: 1px solid #CBC6FC;';
                    } elseif (strpos($lowerUpdCat, 'civil') !== false || strpos($lowerUpdCat, 'registry') !== false) {
                      $dotIcon = 'bi-file-earmark-person-fill';
                      $dotBg = 'background: linear-gradient(135deg, #E6F1FB 0%, #D1E5F7 100%); color: #0C447C; border: 1px solid #B5D7F8;';
                    } else {
                      $dotIcon = 'bi-file-earmark-check-fill';
                      $dotBg = 'background: linear-gradient(135deg, #EFF6FF 0%, #DBEAFE 100%); color: #1E40AF; border: 1px solid #BFDBFE;';
                    }

                    $updJson = json_encode([
                      "id" => (int) ($upd["id"] ?? 0),
                      "title" => $upd["title"],
                      "category" => $upd["category"] ?? "General Legislation",
                      "author" => $upd["author"] ?? "City Council of Manila",
                      "status" => $upd["status"] ?? "Published",
                      "date" => $formattedDate,
                      "desc" => $upd["description"] ?? "Manila City Ordinance official provisions and guidelines.",
                      "file" => $upd["file_path"] ?? ""
                    ], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
                    ?>
                    <div class="update-timeline-item d-flex align-items-start gap-3" onclick='openPolicyViewModal(<?= $updJson ?>)'>
                      <div class="update-icon-dot flex-shrink-0" style="<?= $dotBg ?>">
                        <i class="bi <?= $dotIcon ?>"></i>
                      </div>
                      <div class="flex-grow-1 min-w-0" style="min-width: 0; max-width: 100%; overflow: hidden;">
                        <div class="d-flex flex-wrap align-items-center gap-1.5 mb-1">
                          <span class="badge rounded-pill px-2 py-0.5 fw-semibold flex-shrink-0" style="background: rgba(11, 46, 89, 0.08); color: #0B2E59; font-size: 0.68rem; letter-spacing: 0.2px;">
                            <i class="bi bi-file-earmark-plus-fill me-1 text-primary"></i>New Policy
                          </span>
                          <?= renderUserPolicyCategoryBadge($upd['category'] ?? '') ?>
                        </div>
                        <div class="fw-bold update-policy-title mb-1" style="font-size: 0.88rem; line-height: 1.35; color: #0F172A;">
                          <?= htmlspecialchars($upd['title']) ?>
                        </div>
                        <div class="d-flex align-items-center gap-2 text-muted" style="font-size: 0.76rem;">
                          <div class="report-date-cell flex-shrink-0" style="padding: 2px 7px; font-size: 0.72rem;">
                            <i class="bi bi-calendar3"></i>
                            <span class="report-date-text"><?= $formattedDate ?></span>
                          </div>
                          <span class="text-muted opacity-50">&bull;</span>
                          <span class="text-secondary opacity-75 text-truncate" style="font-size: 0.74rem;">City Council Record</span>
                        </div>
                      </div>
                      <i class="bi bi-chevron-right text-muted opacity-50 align-self-center fs-6 flex-shrink-0 ms-auto"></i>
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
                <div style="display: none !important;"><span id="evalModalStatus"></span></div>
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
              <h5 class="fw-bold text-uppercase mb-3" style="font-size: 0.95rem; letter-spacing: 1px;">EVALUATION CRITERIA</h5>
              <div class="ps-2 ps-md-3">
                <div class="table-responsive">
                  <table class="table table-bordered align-middle mb-0" style="font-size: 0.88rem; border-color: #e2e8f0;">
                    <thead style="background-color: #f8fafc;">
                      <tr class="text-uppercase text-secondary fw-bold" style="font-size: 0.72rem; letter-spacing: 0.5px;">
                        <th scope="col" style="padding: 10px 12px; width: 28%;">CRITERIA</th>
                        <th scope="col" class="text-center" style="padding: 10px 10px; width: 14%;">SCORE</th>
                        <th scope="col" style="padding: 10px 14px; width: 58%;">ASSESSMENT &amp; EVIDENCE-BASED FINDINGS</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr>
                        <td style="padding: 10px 12px;" class="fw-bold text-dark">
                          <div>Economic Feasibility</div>
                          <div class="small text-muted fw-normal" style="font-size:0.75rem;">Funding realism, cost quantification</div>
                        </td>
                        <td style="padding: 10px 10px; text-align:center;" id="evalCriteriaEconomicScore">
                          <span class="badge bg-secondary-subtle text-secondary px-2.5 py-1">Awaiting</span>
                        </td>
                        <td style="padding: 10px 14px;" class="text-dark" id="evalCriteriaEconomicReason">Awaiting evaluation.</td>
                      </tr>
                      <tr>
                        <td style="padding: 10px 12px;" class="fw-bold text-dark">
                          <div>Social Impact</div>
                          <div class="small text-muted fw-normal" style="font-size:0.75rem;">Beneficiaries, burdened parties, community effect</div>
                        </td>
                        <td style="padding: 10px 10px; text-align:center;" id="evalCriteriaSocialScore">
                          <span class="badge bg-secondary-subtle text-secondary px-2.5 py-1">Awaiting</span>
                        </td>
                        <td style="padding: 10px 14px;" class="text-dark" id="evalCriteriaSocialReason">Awaiting evaluation.</td>
                      </tr>
                      <tr>
                        <td style="padding: 10px 12px;" class="fw-bold text-dark">
                          <div>Environmental Impact</div>
                          <div class="small text-muted fw-normal" style="font-size:0.75rem;">Ecological effects, environmental resilience</div>
                        </td>
                        <td style="padding: 10px 10px; text-align:center;" id="evalCriteriaEnvScore">
                          <span class="badge bg-secondary-subtle text-secondary px-2.5 py-1">Awaiting</span>
                        </td>
                        <td style="padding: 10px 14px;" class="text-dark" id="evalCriteriaEnvReason">Awaiting evaluation.</td>
                      </tr>
                      <tr>
                        <td style="padding: 10px 12px;" class="fw-bold text-dark">
                          <div>Legal Compliance</div>
                          <div class="small text-muted fw-normal" style="font-size:0.75rem;">Authority, drafting quality, procedural compliance</div>
                        </td>
                        <td style="padding: 10px 10px; text-align:center;" id="evalCriteriaLegalScore">
                          <span class="badge bg-secondary-subtle text-secondary px-2.5 py-1">Awaiting</span>
                        </td>
                        <td style="padding: 10px 14px;" class="text-dark" id="evalCriteriaLegalReason">Awaiting evaluation.</td>
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
              <div class="ps-2 ps-md-3">
                <p class="mb-0 text-dark" id="evalModalAnalysis"
                  style="font-size: 0.92rem; line-height: 1.7; text-align: justify;">
                  Awaiting evaluation.
                </p>
              </div>
            </div>

            <!-- FULL DOCUMENT ACCESS (Bottom-Left of Report) -->
            <div class="mt-4 pt-3 border-top d-flex flex-wrap align-items-center justify-content-between gap-3" id="evalModalDocumentAccessBlock">
              <div class="d-flex align-items-center gap-2">
                <span class="p-2 rounded-2 bg-primary bg-opacity-10 text-primary">
                  <i class="bi bi-file-earmark-text-fill fs-5"></i>
                </span>
                <div>
                  <div class="fw-bold text-dark small" style="font-size:0.88rem;">Original Ordinance Document</div>
                  <div class="text-muted" style="font-size:0.75rem;">Direct source reference for factual verification</div>
                </div>
              </div>
              <a id="evalModalDocLink" href="#" target="_blank" class="btn btn-sm btn-outline-primary rounded-pill px-3.5 py-1.5 fw-semibold d-inline-flex align-items-center gap-1.5 shadow-2xs" style="font-size:0.82rem;">
                <i class="bi bi-box-arrow-up-right"></i>
                <span>View Full Original Ordinance</span>
              </a>
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
  <!-- 1. Official Policy Record Details Modal (Executive Manila Design) -->
  <div class="modal fade" id="policyDetailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered policy-modal-dialog">
      <div class="modal-content border-0 policy-modal-content">
        
        <!-- Executive Manila Navy Header with Gold Accent -->
        <div class="modal-header border-0 policy-modal-header d-flex align-items-center justify-content-between">
          <div class="d-flex align-items-center gap-3">
            <div class="d-inline-flex align-items-center justify-content-center rounded-3 shadow-2xs" style="width: 44px; height: 44px; background: rgba(245, 158, 11, 0.15); border: 1px solid rgba(245, 158, 11, 0.35); color: #FCD34D;">
              <i class="bi bi-bank2 fs-5"></i>
            </div>
            <div>
              <div class="text-uppercase fw-bold" style="color: #FCD34D; font-size: 0.68rem; letter-spacing: 1.2px;">Republic of the Philippines &bull; City of Manila</div>
              <h5 class="modal-title fw-bold text-white mb-0" style="font-size: 1.15rem; letter-spacing: -0.01em;">Policy Record Details</h5>
              <div class="small" style="color: #BAE6FD; font-size: 0.74rem;">City Council Legislative Archive &bull; Official Record</div>
            </div>
          </div>
          <button type="button" class="policy-modal-close-btn shadow-none" data-bs-dismiss="modal" aria-label="Close">
            <i class="bi bi-x-lg" style="font-size: 0.85rem;"></i>
          </button>
        </div>

        <!-- Modal Body -->
        <div class="modal-body p-4 p-md-4.5" style="background: #F8FAFC;">
          
          <!-- Hero Policy & Metadata Card -->
          <div class="policy-hero-card">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 pb-1">
              <div class="d-flex align-items-center gap-2 flex-wrap" id="modalPolicyBadges">
                <span id="modalPolicyCategoryWrapper">
                  <span class="category-badge-pill" id="modalPolicyCategory" style="background: #E1F5EE; color: #085041; border: 1px solid #9FE1CB;">
                    <i class="bi bi-tag-fill me-1"></i><span>Category</span>
                  </span>
                </span>
                <span id="modalPolicyStatusWrapper">
                  <span class="badge py-1.5 px-3 rounded-pill fw-semibold" id="modalPolicyStatus" style="background: #DCFCE7; color: #15803D; border: 1px solid #86EFAC; font-size: 0.78rem;">
                    <i class="bi bi-check-circle-fill me-1"></i>Approved
                  </span>
                </span>
              </div>
              <div class="d-inline-flex align-items-center gap-1.5 px-2.5 py-1 rounded-pill" style="background: #EFF6FF; border: 1px solid #BFDBFE; font-size: 0.75rem; font-weight: 700; color: #1E40AF;">
                <i class="bi bi-shield-check text-primary"></i> <span id="modalPolicyIdText">Record #107</span>
              </div>
            </div>

            <!-- Policy Title -->
            <h3 class="policy-hero-title" id="modalPolicyTitle">
              Title
            </h3>

            <!-- Metadata Chips Strip -->
            <div class="d-flex flex-column flex-sm-row align-items-stretch gap-2.5 pt-2.5 border-top" style="border-color: #F1F5F9 !important;">
              <div class="policy-meta-pill">
                <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 32px; height: 32px; background: #EFF6FF; color: #0B2E59; border: 1px solid #DBEAFE;">
                  <i class="bi bi-person-fill-check fs-6"></i>
                </div>
                <div class="min-w-0">
                  <div class="text-uppercase text-muted fw-bold" style="font-size: 0.65rem; letter-spacing: 0.5px;">Sponsoring Body / Author</div>
                  <div class="fw-bold text-dark text-truncate" id="modalPolicyAuthor" style="font-size: 0.86rem;">-</div>
                </div>
              </div>

              <div class="policy-meta-pill">
                <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 32px; height: 32px; background: #FEF3C7; color: #B45309; border: 1px solid #FDE68A;">
                  <i class="bi bi-calendar-event-fill fs-6"></i>
                </div>
                <div class="min-w-0">
                  <div class="text-uppercase text-muted fw-bold" style="font-size: 0.65rem; letter-spacing: 0.5px;">Publication Date</div>
                  <div class="fw-bold text-dark text-truncate" id="modalPolicyDate" style="font-size: 0.86rem;">-</div>
                </div>
              </div>
            </div>
          </div>

          <!-- Executive Summary & Purpose Card -->
          <div class="policy-summary-card">
            <div class="d-flex align-items-center justify-content-between mb-2.5 pb-2 border-bottom" style="border-color: #F1F5F9 !important;">
              <div class="d-flex align-items-center gap-2">
                <div class="rounded-2 d-flex align-items-center justify-content-center" style="background: #EFF6FF; color: #0B2E59; width: 28px; height: 28px;">
                  <i class="bi bi-card-text fs-6"></i>
                </div>
                <span class="fw-bold text-dark" style="font-size: 0.88rem; letter-spacing: -0.01em;">Executive Summary &amp; Purpose</span>
              </div>
              <span class="badge" style="background: #F1F5F9; color: #64748B; font-size: 0.70rem; font-weight: 600; padding: 4px 8px; border-radius: 6px;">Official Enactment</span>
            </div>
            <p class="mb-0 text-secondary" id="modalPolicyDesc" style="font-size: 0.90rem; line-height: 1.68; color: #334155 !important; font-weight: 450; white-space: pre-line;">
              Description
            </p>
          </div>

          <!-- Official Document File Attachment Card -->
          <div id="modalPolicyFileWrapper" style="display:none;" class="mt-2.5">
            <div class="policy-doc-card d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-3">
              <div class="d-flex align-items-center gap-3 min-w-0">
                <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0 shadow-sm" style="width: 44px; height: 44px; background: #FFFFFF; color: #16A34A; border: 1px solid #BBF7D0;">
                  <i class="bi bi-file-earmark-pdf-fill fs-4"></i>
                </div>
                <div class="min-w-0">
                  <div class="fw-bold text-truncate" style="font-size: 0.90rem; color: #14532D !important;">Official Enacted Document File</div>
                  <div class="small fw-medium" style="font-size: 0.76rem; color: #15803D;">Legislative Council PDF Record Available &bull; Verified Electronic Copy</div>
                </div>
              </div>
              <a id="modalPolicyFileLink" href="#" target="_blank" class="btn btn-modal-preview flex-shrink-0">
                <i class="bi bi-eye-fill"></i>
                <span>Preview Document</span>
              </a>
            </div>
          </div>

        </div>

        <!-- Modal Footer -->
        <div class="modal-footer border-0 px-4 py-3 bg-white d-flex align-items-center justify-content-end" style="border-top: 1px solid #E2E8F0 !important;">
          <a id="modalDownloadBtn" href="#" download class="btn-modal-download" style="display: none;">
            <i class="bi bi-cloud-arrow-down-fill text-warning fs-6"></i>
            <span>Download Official Document</span>
          </a>
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

  <!-- 3. Direct Upload Policy Modal (Zero Form-Filling) -->
  <div class="modal fade" id="uploadPolicyModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 540px;">
      <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
        <div class="modal-header border-0 pb-0 pt-3 px-4 d-flex align-items-center justify-content-between">
          <div class="d-flex align-items-center gap-2">
            <div class="rounded-circle d-flex align-items-center justify-content-center" 
                 style="width: 36px; height: 36px; background: rgba(245, 158, 11, 0.15); color: #d97706;">
              <i class="bi bi-cloud-arrow-up-fill fs-5"></i>
            </div>
            <div>
              <h5 class="modal-title fw-bold text-dark mb-0 fs-6">Upload Policy Record</h5>
              <div class="text-muted small" style="font-size: 0.75rem;">Direct Document Ingestion &amp; Auto-Analysis</div>
            </div>
          </div>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" id="uploadModalCloseBtn"></button>
        </div>

        <div class="modal-body p-4">
          <!-- Hidden file input for file selection -->
          <input type="file" id="directUploadFileInput" class="d-none" accept=".pdf,.docx,.doc">

          <!-- 1. Idle Drag & Dropzone State -->
          <div id="uploadDropzoneState" 
               class="upload-dropzone p-4 rounded-4 text-center transition-all"
               style="border: 2px dashed #cbd5e1; background: #f8fafc; cursor: pointer;"
               onclick="triggerDirectFileUpload()"
               ondragover="handleUploadDragOver(event)"
               ondragleave="handleUploadDragLeave(event)"
               ondrop="handleUploadDrop(event)">
            
            <div class="mb-3">
              <div class="mx-auto rounded-circle d-flex align-items-center justify-content-center shadow-sm"
                   style="width: 68px; height: 68px; background: linear-gradient(135deg, #fef3c7, #fde68a); color: #b45309;">
                <i class="bi bi-file-earmark-arrow-up fs-2"></i>
              </div>
            </div>

            <h6 class="fw-bold text-dark mb-1">Drag and drop document here</h6>
            <p class="text-muted small mb-3">or <span class="text-primary fw-semibold text-decoration-underline">Browse Files</span> from your computer</p>

            <div class="d-flex align-items-center justify-content-center gap-2 flex-wrap mb-3">
              <span class="badge bg-light text-secondary border px-2.5 py-1 rounded-pill" style="font-size: 0.72rem;">
                <i class="bi bi-filetype-pdf text-danger me-1"></i> PDF
              </span>
              <span class="badge bg-light text-secondary border px-2.5 py-1 rounded-pill" style="font-size: 0.72rem;">
                <i class="bi bi-filetype-docx text-primary me-1"></i> DOCX / DOC
              </span>
              <span class="badge bg-light text-secondary border px-2.5 py-1 rounded-pill" style="font-size: 0.72rem;">
                Max 25MB
              </span>
            </div>

            <div class="p-2.5 rounded-3 text-start small d-flex align-items-center gap-2" 
                 style="background: rgba(79, 70, 229, 0.06); border: 1px solid rgba(79, 70, 229, 0.12); color: #4338ca; font-size: 0.75rem;">
              <i class="bi bi-stars fs-6 flex-shrink-0"></i>
              <span><strong>Instant Processing:</strong> Metadata (Title, Category, Author, Date) is automatically extracted and saved immediately.</span>
            </div>
          </div>

          <!-- 2. Active Uploading & Analyzing State -->
          <div id="uploadActiveState" class="d-none text-center p-4 rounded-4" style="background: #f8fafc; border: 1px solid #e2e8f0;">
            <div class="mb-3 position-relative d-inline-block">
              <div class="spinner-border text-primary" style="width: 3.2rem; height: 3.2rem; border-width: 0.22em;" role="status"></div>
            </div>

            <h6 class="fw-bold text-dark mb-1" id="uploadActiveTitle">Analyzing &amp; Uploading Document...</h6>
            <p class="text-muted small mb-3" id="uploadActiveStatus">Extracting metadata (title, category, author, date)...</p>

            <div class="card border border-slate-200 bg-white shadow-sm p-3 mb-3 text-start rounded-3">
              <div class="d-flex align-items-center gap-2.5">
                <div class="rounded-3 p-2 d-flex align-items-center justify-content-center" style="background: rgba(239, 68, 68, 0.1); color: #dc2626;">
                  <i class="bi bi-file-earmark-text-fill fs-4" id="uploadFileIcon"></i>
                </div>
                <div class="overflow-hidden flex-grow-1">
                  <div class="fw-bold text-dark text-truncate small" id="uploadActiveFileName">document.pdf</div>
                  <div class="text-muted small" style="font-size: 0.72rem;" id="uploadActiveFileSize">Calculating size...</div>
                </div>
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1 rounded-pill small" id="uploadStepBadge">
                  Analyzing
                </span>
              </div>

              <!-- Progress Bar -->
              <div class="progress mt-3" style="height: 6px;">
                <div id="uploadProgressBar" class="progress-bar progress-bar-striped progress-bar-animated bg-primary" 
                     role="progressbar" style="width: 35%; transition: width 0.4s ease;"></div>
              </div>
            </div>

            <div class="text-muted small" style="font-size: 0.75rem;">
              <i class="bi bi-info-circle me-1"></i> Registering record directly in the Legislative repository...
            </div>
          </div>

          <!-- 3. Success State -->
          <div id="uploadSuccessState" class="d-none text-center p-4 rounded-4" style="background: #f0fdf4; border: 1px solid #bbf7d0;">
            <div class="mx-auto rounded-circle d-flex align-items-center justify-content-center shadow-sm mb-3"
                 style="width: 56px; height: 56px; background: #22c55e; color: #ffffff;">
              <i class="bi bi-check-lg fs-2"></i>
            </div>
            <h6 class="fw-bold text-success mb-1">Policy Uploaded Successfully!</h6>
            <p class="text-muted small mb-3" id="uploadSuccessDetails">Record created and added to policy repository.</p>
            <div class="d-flex align-items-center justify-content-center gap-2">
              <div class="spinner-border spinner-border-sm text-success" role="status"></div>
              <span class="text-muted small" style="font-size: 0.75rem;">Refreshing repository view...</span>
            </div>
          </div>

          <!-- 4. Error State -->
          <div id="uploadErrorState" class="d-none text-center p-4 rounded-4" style="background: #fef2f2; border: 1px solid #fecaca;">
            <div class="mx-auto rounded-circle d-flex align-items-center justify-content-center shadow-sm mb-3"
                 style="width: 56px; height: 56px; background: #ef4444; color: #ffffff;">
              <i class="bi bi-exclamation-triangle fs-2"></i>
            </div>
            <h6 class="fw-bold text-danger mb-1">Upload Failed</h6>
            <p class="text-muted small mb-3" id="uploadErrorMessage">An error occurred while uploading.</p>
            <button type="button" class="btn btn-outline-danger btn-sm rounded-3 px-3" onclick="resetDirectUploadUI()">
              <i class="bi bi-arrow-repeat me-1"></i> Try Again
            </button>
          </div>
        </div>
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