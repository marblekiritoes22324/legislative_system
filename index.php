<?php
include __DIR__ . '/config/db.php';
if (file_exists(__DIR__ . '/backend/log_activity.php')) {
  require_once __DIR__ . '/backend/log_activity.php';
}
if (file_exists(__DIR__ . '/config/mailer.php')) {
  require_once __DIR__ . '/config/mailer.php';
}
session_start();

if (!function_exists('get_user_table_name')) {
  function get_user_table_name($conn)
  {
    static $cached = null;
    if ($cached !== null) return $cached;
    $res = @mysqli_query($conn, "SHOW TABLES LIKE 'user_directory'");
    if ($res && mysqli_num_rows($res) > 0) return 'user_directory';
    return 'users';
  }
}

$error = '';

// Handle OTP Verification Request via AJAX
if (isset($_POST['verify_otp'])) {
  header('Content-Type: application/json');
  $email_or_user = trim($_POST['username'] ?? '');
  $code = trim($_POST['otp_code'] ?? '');

  if (empty($email_or_user) || empty($code)) {
    echo json_encode(['success' => false, 'error' => 'Please enter the 6-digit verification code.']);
    exit;
  }

  $valid = false;
  $userObj = null;

  $session_otp = $_SESSION['login_otp_code'] ?? '';
  $session_user = $_SESSION['login_otp_user'] ?? null;
  $session_expiry = $_SESSION['login_otp_expiry'] ?? 0;

  if ($session_user && $session_otp === $code && time() <= $session_expiry) {
    $valid = true;
    $userObj = $session_user;
  } else {
    $u_tbl = get_user_table_name($conn);
    $q = mysqli_prepare($conn, "SELECT * FROM $u_tbl WHERE (LOWER(email) = LOWER(?) OR LOWER(username) = LOWER(?)) AND otp_code = ? AND otp_expires_at >= NOW()");
    if ($q) {
      mysqli_stmt_bind_param($q, "sss", $email_or_user, $email_or_user, $code);
      mysqli_stmt_execute($q);
      $res = mysqli_stmt_get_result($q);
      if ($u = mysqli_fetch_assoc($res)) {
        $valid = true;
        $userObj = [
          'id' => $u['user_id'] ?? ($u['id'] ?? 1),
          'username' => !empty($u['username']) ? $u['username'] : strtolower(explode('@', $u['email'])[0]),
          'name' => $u['full_name'],
          'email' => $u['email'],
          'role' => strtolower($u['role'] ?? 'admin'),
          'department' => $u['department'] ?? 'City Administration',
          'status' => 'approved'
        ];
      }
      mysqli_stmt_close($q);
    }
  }

  if ($valid && $userObj) {
    unset($_SESSION['login_otp_code'], $_SESSION['login_otp_user'], $_SESSION['login_otp_expiry']);
    $u_tbl = get_user_table_name($conn);
    @mysqli_query($conn, "UPDATE $u_tbl SET otp_code = NULL, otp_expires_at = NULL WHERE LOWER(email) = LOWER('" . mysqli_real_escape_string($conn, $email_or_user) . "') OR LOWER(username) = LOWER('" . mysqli_real_escape_string($conn, $email_or_user) . "')");

    if (function_exists('log_audit_action')) {
      log_audit_action($conn, $userObj['name'], 'System', 'Completed 2FA OTP login verification');
    }

    echo json_encode(['success' => true, 'user' => $userObj]);
    exit;
  } else {
    echo json_encode(['success' => false, 'error' => 'Invalid or expired 6-digit verification code. Please try again.']);
    exit;
  }
}

// Handle Resend OTP Request
if (isset($_POST['resend_otp'])) {
  header('Content-Type: application/json');
  $email_or_user = trim($_POST['username'] ?? '');

  $targetEmail = !empty($_SESSION['login_otp_user']['email']) ? $_SESSION['login_otp_user']['email'] : '';
  $targetName = !empty($_SESSION['login_otp_user']['name']) ? $_SESSION['login_otp_user']['name'] : 'User';
  $targetUsername = !empty($_SESSION['login_otp_user']['username']) ? $_SESSION['login_otp_user']['username'] : $email_or_user;

  $u_tbl = get_user_table_name($conn);

  if (empty($targetEmail) && !empty($email_or_user)) {
    $q = mysqli_prepare($conn, "SELECT email, full_name, username FROM $u_tbl WHERE LOWER(email) = LOWER(?) OR LOWER(username) = LOWER(?) LIMIT 1");
    if ($q) {
      mysqli_stmt_bind_param($q, "ss", $email_or_user, $email_or_user);
      mysqli_stmt_execute($q);
      $res = mysqli_stmt_get_result($q);
      if ($row = mysqli_fetch_assoc($res)) {
        $targetEmail = $row['email'];
        $targetName = !empty($row['full_name']) ? $row['full_name'] : $row['username'];
        $targetUsername = $row['username'];
      }
      mysqli_stmt_close($q);
    }
  }

  if (empty($targetEmail)) {
    $targetEmail = 'christiancaspe19@gmail.com';
    $targetName = 'Christian M. Caspe';
    $targetUsername = 'christiancaspe19';
  }

  $otpCode = strval(random_int(100000, 999999));
  $_SESSION['login_otp_code'] = $otpCode;
  $_SESSION['login_otp_expiry'] = time() + (10 * 60);

  $safeEmail = mysqli_real_escape_string($conn, $targetEmail);
  $safeUser = mysqli_real_escape_string($conn, $targetUsername);
  @mysqli_query($conn, "UPDATE $u_tbl SET otp_code = '$otpCode', otp_expires_at = DATE_ADD(NOW(), INTERVAL 10 MINUTE) WHERE LOWER(email) = LOWER('$safeEmail') OR LOWER(username) = LOWER('$safeUser')");

  $mailRes = function_exists('send_login_otp_email') ? send_login_otp_email($targetEmail, $targetName, $otpCode) : ['success' => true, 'simulated' => true];

  echo json_encode([
    'success' => true,
    'message' => 'A new 6-digit verification code has been sent to ' . $targetEmail . '!',
    'dev_hint' => (!empty($mailRes['simulated'])) ? $otpCode : null
  ]);
  exit;
}

// Handle API / AJAX Login Request from assets/login.js
if (isset($_POST['api_login'])) {
  header('Content-Type: application/json');
  $username = trim($_POST['username'] ?? '');
  $password = trim($_POST['password'] ?? '');

  if (empty($username) || empty($password)) {
    echo json_encode(['success' => false, 'error' => 'Please enter username/email and password.']);
    exit;
  }

  $display_name_req = trim($_POST['display_name'] ?? '');

  if ((strtolower(trim($username)) === 'christiancaspe19@gmail.com' || strtolower(trim($username)) === 'christiancaspe19') && $password === '09972000158') {
    $adminName = 'Christian M. Caspe';
    $adminEmail = 'christiancaspe19@gmail.com';
    $otpCode = strval(random_int(100000, 999999));

    $_SESSION['login_otp_code'] = $otpCode;
    $_SESSION['login_otp_expiry'] = time() + (10 * 60);
    $_SESSION['login_otp_user'] = [
      'id' => 1,
      'username' => 'christiancaspe19',
      'name' => $adminName,
      'email' => $adminEmail,
      'role' => 'admin',
      'department' => 'City Administration',
      'status' => 'approved'
    ];

    $u_tbl = get_user_table_name($conn);
    @mysqli_query($conn, "UPDATE $u_tbl SET otp_code = '$otpCode', otp_expires_at = DATE_ADD(NOW(), INTERVAL 10 MINUTE) WHERE LOWER(email) = '$adminEmail' OR LOWER(username) = 'christiancaspe19'");

    $mailRes = function_exists('send_login_otp_email') ? send_login_otp_email($adminEmail, $adminName, $otpCode) : ['success' => true, 'simulated' => true];

    if (function_exists('log_audit_action')) {
      log_audit_action($conn, $adminName, 'System', 'Generated 2FA login OTP for Administrator');
    }

    echo json_encode([
      'success' => true,
      'step' => 'otp_required',
      'username' => 'christiancaspe19',
      'email' => 'c***e19@gmail.com',
      'full_email' => $adminEmail,
      'simulated' => $mailRes['simulated'] ?? false,
      'dev_hint' => (!empty($mailRes['simulated'])) ? $otpCode : null,
      'message' => 'A 6-digit verification code has been sent to ' . $adminEmail . '.'
    ]);
    exit;
  }

  if ((strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $username)) === 'admin' || strtolower($username) === 'admin@manila.gov.ph') && $password === 'admin123') {
    $adminName = !empty($display_name_req) ? $display_name_req : 'Admin';
    if (function_exists('log_audit_action')) {
      log_audit_action($conn, $adminName, 'System', 'User login');
    }
    echo json_encode(['success' => true, 'user' => [
      'username' => 'admin',
      'name' => $adminName !== 'Admin' ? $adminName : 'System Administrator',
      'role' => 'admin',
      'status' => 'approved'
    ]]);
    exit;
  }

  if ((strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $username)) === 'staff' || strtolower($username) === 'staff@manila.gov.ph') && $password === 'staff123') {
    $staffName = !empty($display_name_req) ? $display_name_req : 'Staff Officer';
    if (function_exists('log_audit_action')) {
      log_audit_action($conn, $staffName, 'System', 'User login');
    }
    echo json_encode(['success' => true, 'user' => [
      'username' => 'staff',
      'name' => $staffName,
      'role' => 'staff',
      'department' => 'Legislative Secretariat',
      'status' => 'approved'
    ]]);
    exit;
  }

  $u_tbl = get_user_table_name($conn);
  $sql = "SELECT * FROM $u_tbl WHERE LOWER(username) = LOWER(?) OR LOWER(email) = LOWER(?) OR LOWER(full_name) = LOWER(?)";
  $stmt = mysqli_prepare($conn, $sql);
  if ($stmt) {
    mysqli_stmt_bind_param($stmt, "sss", $username, $username, $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($user = mysqli_fetch_assoc($result)) {
      $match = password_verify($password, $user['password']) || ($password === $user['password']);
      if ($match) {
        $userObj = [
          'id' => $user['user_id'] ?? ($user['id'] ?? 1),
          'username' => !empty($user['username']) ? $user['username'] : strtolower(explode('@', $user['email'])[0]),
          'name' => $user['full_name'],
          'email' => $user['email'],
          'role' => strtolower($user['role'] ?? 'user'),
          'department' => $user['department'] ?? 'Secretariat',
          'status' => 'approved'
        ];

        $targetEmail = trim($user['email'] ?? '');
        if (!empty($targetEmail) && filter_var($targetEmail, FILTER_VALIDATE_EMAIL)) {
          $targetName = !empty($user['full_name']) ? $user['full_name'] : $userObj['username'];
          $otpCode = strval(random_int(100000, 999999));

          $_SESSION['login_otp_code'] = $otpCode;
          $_SESSION['login_otp_expiry'] = time() + (10 * 60);
          $_SESSION['login_otp_user'] = $userObj;

          $safeEmail = mysqli_real_escape_string($conn, $targetEmail);
          $safeUser = mysqli_real_escape_string($conn, $userObj['username']);
          @mysqli_query($conn, "UPDATE $u_tbl SET otp_code = '$otpCode', otp_expires_at = DATE_ADD(NOW(), INTERVAL 10 MINUTE) WHERE LOWER(email) = LOWER('$safeEmail') OR LOWER(username) = LOWER('$safeUser')");

          $mailRes = function_exists('send_login_otp_email') ? send_login_otp_email($targetEmail, $targetName, $otpCode) : ['success' => true, 'simulated' => true];

          if (function_exists('log_audit_action')) {
            log_audit_action($conn, $targetName, 'System', 'Generated 2FA login OTP');
          }

          $emailParts = explode('@', $targetEmail);
          $maskedName = substr($emailParts[0], 0, 1) . '***' . substr($emailParts[0], -1);
          $maskedEmail = $maskedName . '@' . ($emailParts[1] ?? 'gmail.com');

          echo json_encode([
            'success' => true,
            'step' => 'otp_required',
            'username' => $userObj['username'],
            'email' => $maskedEmail,
            'full_email' => $targetEmail,
            'simulated' => $mailRes['simulated'] ?? false,
            'dev_hint' => (!empty($mailRes['simulated'])) ? $otpCode : null,
            'message' => 'A 6-digit verification code has been sent to ' . $targetEmail . '.'
          ]);
          exit;
        }

        if (function_exists('log_audit_action')) {
          log_audit_action($conn, $user['full_name'] ?? 'User', 'System', 'User login');
        }
        echo json_encode(['success' => true, 'user' => $userObj]);
        exit;
      } else {
        echo json_encode(['success' => false, 'error' => 'Incorrect password.']);
        exit;
      }
    } else {
      echo json_encode(['success' => false, 'error' => 'Username or Email does not exist.']);
      exit;
    }
    mysqli_stmt_close($stmt);
  }
  echo json_encode(['success' => false, 'error' => 'Database query error: ' . mysqli_error($conn)]);
  exit;
}

// Fallback Standard POST Login
if (isset($_POST['login'])) {
  $raw_user = trim($_POST['username'] ?? '');
  $raw_pass = trim($_POST['password'] ?? '');

  if (empty($raw_user) || empty($raw_pass)) {
    $error = 'Please enter username and password.';
  } else {
    $clean_user = preg_replace('/[^a-zA-Z0-9]/', '', strtolower($raw_user));
    $lower_raw = strtolower($raw_user);

    if (($clean_user === 'christiancaspe19' || $lower_raw === 'christiancaspe19@gmail.com') && $raw_pass === '09972000158') {
      if (function_exists('log_audit_action') && !empty($conn)) {
        log_audit_action($conn, 'Christian M. Caspe', 'System', 'User login');
      }
      echo "<script>
        localStorage.setItem('admin_logged_in', 'true');
        localStorage.removeItem('staff_logged_in');
        localStorage.setItem('current_user', JSON.stringify({username: 'christiancaspe19', name: 'Christian M. Caspe', role: 'admin', email: 'christiancaspe19@gmail.com'}));
        window.location.href = 'admin/admin_dashboard.php';
      </script>";
      exit();
    } elseif ($clean_user === 'admin' || $lower_raw === 'admin@manila.gov.ph') {
      if ($raw_pass === 'admin123') {
        $u_tbl = get_user_table_name($conn);
        $adminDisplayName = 'Admin';
        $aq = @mysqli_query($conn, "SELECT full_name FROM $u_tbl WHERE LOWER(role) = 'admin' OR LOWER(username) = 'admin' LIMIT 1");
        if ($aq && $ar = mysqli_fetch_assoc($aq)) {
          if (!empty($ar['full_name'])) $adminDisplayName = $ar['full_name'];
        }
        if (function_exists('log_audit_action') && !empty($conn)) {
          log_audit_action($conn, $adminDisplayName, 'System', 'User login');
        }
        echo "<script>
          let savedAdmin = {};
          try { savedAdmin = JSON.parse(localStorage.getItem('admin_profile_data') || '{}'); } catch(e) {}
          let finalAdminName = savedAdmin.name || " . json_encode($adminDisplayName) . ";
          localStorage.setItem('admin_logged_in', 'true');
          localStorage.setItem('current_user', JSON.stringify({username: 'admin', name: finalAdminName, role: 'admin'}));
          window.location.href = 'admin/admin_dashboard.php';
        </script>";
        exit();
      } else {
        $error = 'Incorrect password.';
      }
    } elseif (($clean_user === 'staff' || $lower_raw === 'staff@manila.gov.ph') && $raw_pass === 'staff123') {
      if (function_exists('log_audit_action') && !empty($conn)) {
        log_audit_action($conn, 'Staff Officer', 'System', 'User login');
      }
      echo "<script>
        localStorage.setItem('staff_logged_in', 'true');
        localStorage.removeItem('admin_logged_in');
        localStorage.setItem('current_user', JSON.stringify({username: 'staff', name: 'Staff Officer', role: 'staff'}));
        window.location.href = 'staff/staff_dashboard.php';
      </script>";
      exit();
    } else {
      $u_tbl = get_user_table_name($conn);
      $stmt = mysqli_prepare($conn, "SELECT * FROM $u_tbl WHERE LOWER(username) = LOWER(?) OR LOWER(email) = LOWER(?) OR LOWER(full_name) = LOWER(?)");
      if ($stmt) {
        mysqli_stmt_bind_param($stmt, "sss", $raw_user, $raw_user, $raw_user);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        if ($user = mysqli_fetch_assoc($res)) {
          $match = password_verify($raw_pass, $user['password']) || ($raw_pass === $user['password']);
          if ($match) {
            $_SESSION['user_id'] = $user['user_id'] ?? ($user['id'] ?? 1);
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['username'] = $user['username'];
            $role = strtolower($user['role'] ?? 'staff');

            if (function_exists('log_audit_action') && !empty($conn)) {
              log_audit_action($conn, $user['full_name'] ?? 'User', 'System', 'User login');
            }

            $createdAtFormatted = !empty($user['created_at']) ? date('F d, Y', strtotime($user['created_at'])) : date('F d, Y');
            if ($role === 'admin' || $role === 'administrator') {
              echo "<script>
                localStorage.setItem('admin_logged_in', 'true');
                localStorage.removeItem('staff_logged_in');
                localStorage.setItem('current_user', " . json_encode(json_encode(['username' => $user['username'], 'name' => $user['full_name'], 'email' => $user['email'] ?? 'admin@manila.gov.ph', 'department' => $user['department'] ?? 'City Administration', 'role' => 'admin', 'created_at' => $createdAtFormatted])) . ");
                window.location.href = 'admin/admin_dashboard.php';
              </script>";
              exit();
            } elseif ($role === 'staff' || $role === 'legislative staff') {
              echo "<script>
                localStorage.setItem('staff_logged_in', 'true');
                localStorage.removeItem('admin_logged_in');
                localStorage.setItem('current_user', " . json_encode(json_encode(['username' => $user['username'], 'name' => $user['full_name'], 'email' => $user['email'] ?? 'staff@manila.gov.ph', 'department' => $user['department'] ?? 'Secretariat & Legal Affairs', 'role' => 'staff', 'created_at' => $createdAtFormatted])) . ");
                window.location.href = 'staff/staff_dashboard.php';
              </script>";
              exit();
            } else {
              $redirect_url = "users/user_dashboard.php?username=" . urlencode($user['username']) . "&name=" . urlencode($user['full_name']) . "&email=" . urlencode($user['email'] ?? '') . "&department=" . urlencode($user['department'] ?? 'City Council Secretariat') . "&role=" . urlencode($user['role'] ?? 'Councilor');
              echo "<script>
                localStorage.setItem('user_logged_in', 'true');
                localStorage.removeItem('staff_logged_in');
                localStorage.removeItem('admin_logged_in');
                localStorage.setItem('current_user', " . json_encode(json_encode(['username' => $user['username'], 'name' => $user['full_name'], 'email' => $user['email'] ?? '', 'department' => $user['department'] ?? 'City Council Secretariat', 'role' => 'councilor', 'created_at' => $createdAtFormatted])) . ");
                window.location.href = " . json_encode($redirect_url) . ";
              </script>";
              exit();
            }
          } else {
            $error = 'Incorrect password.';
          }
        } else {
          $error = 'Username or Email does not exist.';
        }
        mysqli_stmt_close($stmt);
      } else {
        $error = 'Database error: Unable to authenticate account.';
      }
    }
  }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Integrated Legislative System – City Government of Manila Portal</title>

  <!-- Google Fonts & Bootstrap Icons -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link
    href="https://fonts.googleapis.com/css2?family=Cinzel:wght@700;800;900&family=Outfit:wght@400;500;600;700;800;900&family=Playfair+Display:ital,wght@0,700;0,800;0,900;1,700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
    rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

  <!-- Shared Stylesheet with Embedded Design System -->
  <link rel="stylesheet" href="assets/css/welcome.css?v=<?= time() ?>">
</head>

<body>

  <!-- =========================================================================
       1. TOP NAVIGATION BAR (DARK NAVY & GOLD ACCENTS)
       ========================================================================= -->
  <nav class="ils-navbar" id="ilsNavbar">
    <a href="index.php" class="ils-brand">
      <img src="assets/images/manilacityhall.svg" alt="Manila City Seal" class="ils-brand-seal">
      <div>
        <div class="ils-brand-title">Integrated Legislative System</div>
        <div class="ils-brand-subtitle">City Government Portal</div>
      </div>
    </a>

    <!-- Center Navigation Links -->
    <ul class="ils-nav-menu">
      <li><a href="#home" class="ils-nav-link active">Home</a></li>
      <li><a href="#about" class="ils-nav-link">About</a></li>
      <li><a href="#mission" class="ils-nav-link">Mission &amp; Vision</a></li>
      <li><a href="#subsystems" class="ils-nav-link">Subsystems</a></li>
      <li><a href="#workflow" class="ils-nav-link">Workflow</a></li>
    </ul>

    <!-- Top Right Action Button -->
    <div style="display: flex; align-items: center; gap: 12px;">
      <a href="javascript:void(0)" onclick="openSignInModal()" class="ils-btn-citizen">
        <i class="bi bi-person-badge"></i> Citizen Portal
      </a>
      <a href="javascript:void(0)" onclick="openSignInModal()" class="ils-btn-primary" style="padding: 9px 18px; font-size: 0.82rem;">
        <i class="bi bi-box-arrow-in-right"></i> Sign In
      </a>
    </div>
  </nav>

  <!-- =========================================================================
       2. HERO SECTION (BETTER GOVERNANCE. STRONGER FUTURES.)
       ========================================================================= -->
  <section class="ils-hero-section" id="home">
    <div class="ils-hero-overlay"></div>

    <div class="ils-hero-grid">
      <!-- Left Column: Hero Content -->
      <div class="ils-hero-content">
        <div class="ils-hero-tag">
          <span class="gold-dash">—</span> Building Responsive Legislation
        </div>

        <h1 class="ils-hero-title">
          <span class="title-white">Better Governance.</span><br>
          <span class="title-gold">Stronger Futures.</span>
        </h1>

        <p class="ils-hero-desc">
          Interconnected digital framework for ordinance tracking, agenda scheduling, public hearings, voting, and citizen consultation. Built for transparent municipal administration at Manila City Hall.
        </p>

        <div class="ils-hero-actions">
          <a href="#subsystems" class="ils-btn-primary">
            Explore Subsystems <i class="bi bi-arrow-right ms-1"></i>
          </a>
          <a href="javascript:void(0)" onclick="openSignInModal()" class="ils-btn-secondary">
            <i class="bi bi-person-vcard"></i> Public Citizen Portal
          </a>
        </div>
      </div>

      <!-- Right Column: Massive Official Manila Seal Graphic -->
      <div class="ils-hero-seal-wrapper">
        <div class="ils-hero-seal-circle">
          <img src="assets/images/manilacityhall.svg" alt="Lungsod ng Maynila Seal">
        </div>
      </div>
    </div>
  </section>

  <!-- =========================================================================
       3. ABOUT SECTION
       ========================================================================= -->
  <section class="ils-section" id="about">
    <div class="ils-section-header">
      <span class="ils-section-badge">Municipal Governance Excellence</span>
      <h2 class="ils-section-title">A Unified Digital Infrastructure</h2>
      <p class="ils-section-desc">
        The Manila City Hall Integrated Legislative System centralizes parliamentary procedures, empirical policy research, ordinance drafting, and public civic engagement into a cohesive, secure digital workspace.
      </p>
    </div>

    <div class="ils-cards-grid">
      <div class="ils-card">
        <div>
          <div class="ils-card-icon"><i class="bi bi-shield-check"></i></div>
          <h3 class="ils-card-title">Policy Integrity &amp; Audit</h3>
          <p class="ils-card-desc">Full immutable activity tracking, tamper-proof ordinance codification, and automated audit trails for every legislative document.</p>
        </div>
        <a href="#subsystems" class="ils-card-link">Learn More <i class="bi bi-arrow-right"></i></a>
      </div>

      <div class="ils-card">
        <div>
          <div class="ils-card-icon"><i class="bi bi-graph-up-arrow"></i></div>
          <h3 class="ils-card-title">Empirical Research Analytics</h3>
          <p class="ils-card-desc">Real-time demographic indicators, budget allocations, and empirical policy evaluation tools to empower evidence-based council decisions.</p>
        </div>
        <a href="#subsystems" class="ils-card-link">Learn More <i class="bi bi-arrow-right"></i></a>
      </div>

      <div class="ils-card">
        <div>
          <div class="ils-card-icon"><i class="bi bi-people"></i></div>
          <h3 class="ils-card-title">Transparent Civic Participation</h3>
          <p class="ils-card-desc">Open access to passed city ordinances, municipal resolutions, and interactive public consultation forums for all Manila residents.</p>
        </div>
        <a href="#subsystems" class="ils-card-link">Learn More <i class="bi bi-arrow-right"></i></a>
      </div>
    </div>
  </section>

  <!-- =========================================================================
       4. MISSION & VISION SECTION
       ========================================================================= -->
  <section class="ils-section darker" id="mission">
    <div class="ils-section-header">
      <span class="ils-section-badge">Institutional Mandate</span>
      <h2 class="ils-section-title">Mission &amp; Strategic Vision</h2>
      <p class="ils-section-desc">
        Dedicated to modernizing the legislative machinery of the City of Manila through cutting-edge technology and democratic transparency.
      </p>
    </div>

    <div class="ils-cards-grid" style="grid-template-columns: 1fr 1fr; max-width: 1080px;">
      <div class="ils-card" style="border-left: 4px solid var(--accent-gold);">
        <div>
          <div class="ils-card-icon"><i class="bi bi-compass"></i></div>
          <h3 class="ils-card-title">Our Mission</h3>
          <p class="ils-card-desc" style="font-size: 1.02rem; line-height: 1.75;">
            To deliver an integrated, transparent, and data-driven legislative platform that empowers City Councilors, researchers, and citizens with seamless access to municipal laws, empirical policy data, and collaborative legislative workflows.
          </p>
        </div>
      </div>

      <div class="ils-card" style="border-left: 4px solid var(--accent-gold);">
        <div>
          <div class="ils-card-icon"><i class="bi bi-eye"></i></div>
          <h3 class="ils-card-title">Our Vision</h3>
          <p class="ils-card-desc" style="font-size: 1.02rem; line-height: 1.75;">
            To establish Manila as the benchmark of smart municipal governance in the Philippines, where public policy is crafted with empirical rigor, transparent deliberations, and citizen-centered digital services.
          </p>
        </div>
      </div>
    </div>
  </section>

  <!-- =========================================================================
       5. SUBSYSTEMS SECTION
       ========================================================================= -->
  <section class="ils-section" id="subsystems">
    <div class="ils-section-header">
      <span class="ils-section-badge">Connected Architecture</span>
      <h2 class="ils-section-title">Core Legislative Subsystems</h2>
      <p class="ils-section-desc">
        Modular municipal subsystems working in synchronization to automate the legislative lifecycle.
      </p>
    </div>

    <div class="ils-cards-grid">
      <!-- Subsystem 1 -->
      <div class="ils-card">
        <div>
          <div class="ils-card-icon"><i class="bi bi-file-earmark-text"></i></div>
          <h3 class="ils-card-title">Legislative Information System</h3>
          <p class="ils-card-desc">Central repository for drafting, tracking, amending, and codifying city ordinances and resolutions with full revision history.</p>
        </div>
        <a href="javascript:void(0)" onclick="openSignInModal()" class="ils-btn-primary" style="width: 100%; justify-content: center; padding: 10px 16px;">
          Access Portal <i class="bi bi-box-arrow-in-right ms-1"></i>
        </a>
      </div>

      <!-- Subsystem 2 -->
      <div class="ils-card">
        <div>
          <div class="ils-card-icon"><i class="bi bi-bar-chart-line"></i></div>
          <h3 class="ils-card-title">Policy Evaluation &amp; Analytics</h3>
          <p class="ils-card-desc">Empirical research module providing socioeconomic indicator tracking, stakeholder impact analysis, and report generation.</p>
        </div>
        <a href="javascript:void(0)" onclick="openSignInModal()" class="ils-btn-primary" style="width: 100%; justify-content: center; padding: 10px 16px;">
          Access Portal <i class="bi bi-box-arrow-in-right ms-1"></i>
        </a>
      </div>

      <!-- Subsystem 3 -->
      <div class="ils-card">
        <div>
          <div class="ils-card-icon"><i class="bi bi-calendar-event"></i></div>
          <h3 class="ils-card-title">Council Agenda &amp; Voting</h3>
          <p class="ils-card-desc">Automated order of business scheduling, committee session management, electronic voting recording, and session archiving.</p>
        </div>
        <a href="javascript:void(0)" onclick="openSignInModal()" class="ils-btn-primary" style="width: 100%; justify-content: center; padding: 10px 16px;">
          Access Portal <i class="bi bi-box-arrow-in-right ms-1"></i>
        </a>
      </div>

      <!-- Subsystem 4 -->
      <div class="ils-card">
        <div>
          <div class="ils-card-icon"><i class="bi bi-globe2"></i></div>
          <h3 class="ils-card-title">Public Citizen Consultation</h3>
          <p class="ils-card-desc">Citizen-facing digital portal for reviewing enacted local laws, downloading official copies, and submitting position papers.</p>
        </div>
        <a href="javascript:void(0)" onclick="openSignInModal()" class="ils-btn-secondary" style="width: 100%; justify-content: center; padding: 10px 16px;">
          Open Citizen Portal <i class="bi bi-box-arrow-in-right ms-1"></i>
        </a>
      </div>
    </div>
  </section>

  <!-- =========================================================================
       6. WORKFLOW SECTION
       ========================================================================= -->
  <section class="ils-section darker" id="workflow">
    <div class="ils-section-header">
      <span class="ils-section-badge">Standard Operating Procedure</span>
      <h2 class="ils-section-title">The Legislative Lifecycle</h2>
      <p class="ils-section-desc">
        Standardized 5-stage legislative pipeline from policy proposal to official city gazetting.
      </p>
    </div>

    <div class="ils-workflow-grid">
      <div class="ils-step-box">
        <div class="ils-step-num">01</div>
        <h4 class="ils-step-title">Drafting &amp; Sponsorship</h4>
        <p class="ils-step-desc">Policy drafts are authored by council sponsors and verified for legal compliance.</p>
      </div>

      <div class="ils-step-box">
        <div class="ils-step-num">02</div>
        <h4 class="ils-step-title">First Reading</h4>
        <p class="ils-step-desc">Formal calendar introduction and referral to appropriate standing committees.</p>
      </div>

      <div class="ils-step-box">
        <div class="ils-step-num">03</div>
        <h4 class="ils-step-title">Committee &amp; Public Hearing</h4>
        <p class="ils-step-desc">Empirical research evaluation, stakeholder consultation, and committee reporting.</p>
      </div>

      <div class="ils-step-box">
        <div class="ils-step-num">04</div>
        <h4 class="ils-step-title">Floor Deliberation &amp; Voting</h4>
        <p class="ils-step-desc">Second and third reading floor debates followed by official roll-call voting.</p>
      </div>

      <div class="ils-step-box">
        <div class="ils-step-num">05</div>
        <h4 class="ils-step-title">Enactment &amp; Archival</h4>
        <p class="ils-step-desc">Mayoral signature, city gazette publishing, and permanent digital codification.</p>
      </div>
    </div>
  </section>

  <!-- =========================================================================
       7. SIGN IN & 2FA OTP MODAL
       ========================================================================= -->
  <div id="signInModal" class="modal-overlay" role="dialog" aria-modal="true" aria-labelledby="modalTitle">
    <div class="modal-dialog-card" style="max-width: 460px; background: #FFFFFF; border-radius: 20px; padding: 36px 32px; color: #0F172A; box-shadow: 0 25px 60px rgba(0,0,0,0.5);">

      <button type="button" class="modal-close-btn" onclick="closeSignInModal()" aria-label="Close modal">
        <i class="bi bi-x-lg"></i>
      </button>

      <header class="brand-header" style="text-align: center; margin-bottom: 24px;">
        <div class="brand-logo-wrapper" style="width: 58px; height: 58px; margin: 0 auto 12px auto; border-radius: 50%; background: rgba(11,27,61,0.06); border: 2px solid rgba(11,27,61,0.12); display: flex; align-items: center; justify-content: center; font-size: 1.6rem; color: var(--primary-navy);">
          <i class="bi bi-person-lock"></i>
        </div>
        <h2 id="modalTitle" class="brand-title" style="font-family: 'Outfit', sans-serif; font-size: 1.65rem; font-weight: 700; color: var(--primary-navy); margin-bottom: 4px;">Sign In</h2>
        <p class="brand-subtitle" style="font-size: 0.88rem; color: #64748B; margin: 0;">Log in to access your legislative portal</p>
      </header>

      <?php if ($error): ?>
        <div class="alert-error" style="display: flex; align-items: center; gap: 10px; background: #FEF2F2; border: 1px solid #FCA5A5; color: #DC2626; padding: 12px 16px; border-radius: 8px; font-size: 0.88rem; margin-bottom: 20px;">
          <i class="bi bi-exclamation-triangle-fill"></i>
          <span><?php echo htmlspecialchars($error); ?></span>
        </div>
      <?php endif; ?>

      <form id="loginForm" method="POST" action="index.php" onsubmit="return window.handleLoginFormSubmit(event)">
        <div class="form-group" style="margin-bottom: 18px;">
          <label for="username" class="form-label" style="display: block; font-size: 0.88rem; font-weight: 600; color: #0F172A; margin-bottom: 6px;">Username or Email</label>
          <div class="input-icon-wrapper" style="position: relative; display: flex; align-items: center;">
            <i class="bi bi-person input-icon" style="position: absolute; left: 14px; color: #64748B; font-size: 1.1rem; pointer-events: none;"></i>
            <input type="text" id="username" name="username" class="form-control" style="width: 100%; padding: 12px 16px 12px 44px; border: 1px solid #CBD5E1; border-radius: 8px; font-size: 0.95rem; background: #F8FAFC; color: #0F172A;" placeholder="Enter your username or email" required autocomplete="username">
          </div>
        </div>

        <div class="form-group" style="margin-bottom: 22px;">
          <label for="password" class="form-label" style="display: block; font-size: 0.88rem; font-weight: 600; color: #0F172A; margin-bottom: 6px;">Password</label>
          <div class="input-icon-wrapper" style="position: relative; display: flex; align-items: center;">
            <i class="bi bi-lock input-icon" style="position: absolute; left: 14px; color: #64748B; font-size: 1.1rem; pointer-events: none;"></i>
            <input type="password" id="password" name="password" class="form-control" style="width: 100%; padding: 12px 44px 12px 44px; border: 1px solid #CBD5E1; border-radius: 8px; font-size: 0.95rem; background: #F8FAFC; color: #0F172A;" placeholder="Enter your password" required autocomplete="current-password">
            <button type="button" class="btn-password-toggle" onclick="togglePasswordVisibility('password', this)" style="position: absolute; right: 12px; background: none; border: none; padding: 6px; color: #64748B; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 1.15rem; z-index: 5;" title="Show Password" aria-label="Toggle password visibility">
              <i class="bi bi-eye"></i>
            </button>
          </div>
        </div>

        <button type="submit" name="login" class="btn-primary" style="width: 100%; padding: 14px; background: #0B1B3D; color: #FFFFFF; border: none; border-radius: 8px; font-size: 1rem; font-weight: 700; cursor: pointer; transition: background 0.25s;">
          Sign In
        </button>
      </form>

      <!-- OTP 2FA Form -->
      <div id="otpSection" style="display: none;">
        <div style="text-align: center; margin-bottom: 20px;">
          <div style="width: 52px; height: 52px; background: #e0f2fe; color: #0284c7; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 1.5rem; margin-bottom: 12px;">
            <i class="bi bi-shield-check"></i>
          </div>
          <h3 style="font-size: 1.25rem; font-weight: 700; color: #0B2E59; margin-bottom: 6px;">Security Verification</h3>
          <p style="font-size: 0.85rem; color: #64748B; margin: 0;">
            Enter the 6-digit verification code sent to<br>
            <strong id="otpMaskedEmail" style="color: #0B2E59;">your email</strong>
          </p>
        </div>

        <div id="otpAlertBox" style="display: none; align-items: center; gap: 8px; padding: 10px 14px; border-radius: 8px; font-size: 0.82rem; margin-bottom: 16px;"></div>

        <form id="otpForm" onsubmit="return window.handleOtpFormSubmit(event)">
          <div class="form-group" style="margin-bottom: 18px;">
            <label for="otpCodeInput" class="form-label" style="display: block; font-size: 0.82rem; font-weight: 600; text-align: center; margin-bottom: 8px; color: #0B2E59;">6-Digit Security Code</label>
            <input type="text" id="otpCodeInput" inputmode="numeric" pattern="[0-9]*" maxlength="6" class="form-control" placeholder="123456" style="width: 100%; font-size: 1.8rem; font-weight: 800; letter-spacing: 8px; text-align: center; height: 54px; border: 2px solid #CBD5E1; border-radius: 12px; background: #F8FAFC; color: #0F172A; box-sizing: border-box;" required autocomplete="one-time-code">
          </div>

          <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 18px; font-size: 0.82rem;">
            <span style="color: #64748B;">
              <i class="bi bi-clock-history me-1"></i>Expires: <strong id="otpTimerDisplay" style="color: #0B2E59;">10:00</strong>
            </span>
            <button type="button" id="otpResendBtn" onclick="window.handleOtpResend()" style="background: none; border: none; padding: 0; font-size: 0.82rem; color: #2563EB; font-weight: 600; cursor: pointer;" disabled>
              Resend code
            </button>
          </div>

          <button type="submit" id="otpSubmitBtn" class="btn-primary" style="width: 100%; padding: 14px; background: #0B1B3D; color: #FFFFFF; border: none; border-radius: 8px; font-size: 1rem; font-weight: 700; cursor: pointer;">
            Verify &amp; Sign In <i class="bi bi-arrow-right ms-1"></i>
          </button>

          <button type="button" onclick="window.backToPasswordLogin()" style="width: 100%; padding: 10px; margin-top: 8px; background: #F1F5F9; color: #64748B; border: 1px solid #E2E8F0; border-radius: 8px; font-size: 0.85rem; font-weight: 600; cursor: pointer;">
            <i class="bi bi-arrow-left me-1"></i> Back to sign in
          </button>
        </form>
      </div>

      <div class="auth-footer" style="text-align: center; margin-top: 24px; font-size: 0.82rem; color: #64748B; border-top: 1px solid #F1F5F9; padding-top: 16px;">
        <i class="bi bi-shield-lock me-1"></i> Manila City Hall System — Accounts are provisioned by IT Administrators.
      </div>
    </div>
  </div>

  <!-- =========================================================================
       8. FOOTER SECTION
       ========================================================================= -->
  <footer style="background: #050E1D; color: #FFFFFF; padding: 60px 48px 24px 48px; border-top: 2px solid var(--accent-gold); width: 100%; box-sizing: border-box;">
    <div style="max-width: 1280px; margin: 0 auto;">
      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 36px; margin-bottom: 40px;">
        <!-- Brand Info -->
        <div>
          <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 14px;">
            <img src="assets/images/manilacityhall.svg" alt="Manila Seal" style="width: 44px; height: 44px; background: #FFF; border-radius: 50%; padding: 2px;">
            <div>
              <div style="font-family: 'Outfit', sans-serif; font-weight: 800; font-size: 1.05rem; color: #FFFFFF;">Integrated Legislative System</div>
              <div style="font-size: 0.7rem; color: var(--accent-gold); text-transform: uppercase; font-weight: 700;">City Government of Manila</div>
            </div>
          </div>
          <p style="font-size: 0.88rem; color: rgba(255,255,255,0.7); line-height: 1.6;">
            A unified digital governance framework supporting ordinance drafting, empirical research, and transparent civic administration.
          </p>
        </div>

        <!-- Quick Links -->
        <div>
          <h4 style="font-family: 'Outfit', sans-serif; font-size: 0.95rem; font-weight: 700; color: var(--accent-gold); margin-bottom: 14px; text-transform: uppercase; letter-spacing: 1px;">Quick Links</h4>
          <ul style="list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 8px; font-size: 0.88rem;">
            <li><a href="#home" style="color: rgba(255,255,255,0.8); text-decoration: none;">Home Overview</a></li>
            <li><a href="#about" style="color: rgba(255,255,255,0.8); text-decoration: none;">About System</a></li>
            <li><a href="#mission" style="color: rgba(255,255,255,0.8); text-decoration: none;">Mission &amp; Vision</a></li>
            <li><a href="#subsystems" style="color: rgba(255,255,255,0.8); text-decoration: none;">Core Subsystems</a></li>
            <li><a href="#workflow" style="color: rgba(255,255,255,0.8); text-decoration: none;">Legislative Workflow</a></li>
          </ul>
        </div>

        <!-- Portals & Legal -->
        <div>
          <h4 style="font-family: 'Outfit', sans-serif; font-size: 0.95rem; font-weight: 700; color: var(--accent-gold); margin-bottom: 14px; text-transform: uppercase; letter-spacing: 1px;">Portals &amp; Access</h4>
          <ul style="list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 8px; font-size: 0.88rem;">
            <li><a href="javascript:void(0)" onclick="openSignInModal()" style="color: rgba(255,255,255,0.8); text-decoration: none;">System Sign In</a></li>
            <li><a href="javascript:void(0)" onclick="openSignInModal()" style="color: rgba(255,255,255,0.8); text-decoration: none;">Citizen Consultation Portal</a></li>
            <li><a href="auth/login.php" style="color: rgba(255,255,255,0.8); text-decoration: none;">Administrator Gateway</a></li>
          </ul>
        </div>

        <!-- Contact Info -->
        <div>
          <h4 style="font-family: 'Outfit', sans-serif; font-size: 0.95rem; font-weight: 700; color: var(--accent-gold); margin-bottom: 14px; text-transform: uppercase; letter-spacing: 1px;">Contact Information</h4>
          <div style="font-size: 0.88rem; color: rgba(255,255,255,0.8); display: flex; flex-direction: column; gap: 8px;">
            <div><i class="bi bi-geo-alt-fill me-2" style="color: var(--accent-gold);"></i> Manila City Hall, Ermita, Manila</div>
            <div><i class="bi bi-telephone-fill me-2" style="color: var(--accent-gold);"></i> +63 (2) 8527-0909</div>
            <div><i class="bi bi-envelope-fill me-2" style="color: var(--accent-gold);"></i> legislative@manila.gov.ph</div>
          </div>
        </div>
      </div>

      <div style="border-top: 1px solid rgba(255,255,255,0.1); padding-top: 20px; display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; font-size: 0.82rem; color: rgba(255,255,255,0.6); gap: 10px;">
        <div>Copyright &copy; <?php echo date('Y'); ?> City Government of Manila. All Rights Reserved.</div>
        <div>Integrated Legislative Information System Portal</div>
      </div>
    </div>
  </footer>

  <script src="assets/js/login.js?v=<?= time() ?>"></script>
  <script>
    function togglePasswordVisibility(inputId, btn) {
      var input = typeof inputId === 'string' ? document.getElementById(inputId) : inputId;
      if (!input) return;
      var icon = btn ? btn.querySelector('i') : document.getElementById('passwordToggleIcon');
      if (input.type === 'password') {
        input.type = 'text';
        if (icon) {
          icon.classList.remove('bi-eye');
          icon.classList.add('bi-eye-slash');
        }
        if (btn) btn.setAttribute('title', 'Hide password');
      } else {
        input.type = 'password';
        if (icon) {
          icon.classList.remove('bi-eye-slash');
          icon.classList.add('bi-eye');
        }
        if (btn) btn.setAttribute('title', 'Show password');
      }
    }

    function openSignInModal() {
      var modal = document.getElementById('signInModal');
      if (modal) {
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
        setTimeout(function () {
          var uInput = document.getElementById('username');
          if (uInput) uInput.focus();
        }, 150);
      }
    }

    function closeSignInModal() {
      var modal = document.getElementById('signInModal');
      if (modal) {
        modal.classList.remove('active');
        document.body.style.overflow = '';
      }
    }

    window.addEventListener('click', function (e) {
      var modal = document.getElementById('signInModal');
      if (e.target === modal) {
        closeSignInModal();
      }
    });

    window.addEventListener('keydown', function (e) {
      if (e.key === 'Escape') {
        closeSignInModal();
      }
    });

    document.addEventListener('DOMContentLoaded', function () {
      var urlParams = new URLSearchParams(window.location.search);
      if (urlParams.get('login') === '1' || window.location.hash === '#signin' || window.location.hash === '#login' || <?php echo !empty($error) ? 'true' : 'false'; ?>) {
        openSignInModal();
      }
    });
  </script>
</body>

</html>