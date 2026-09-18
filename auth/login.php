<?php
include __DIR__ . '/../config/db.php';
if (file_exists(__DIR__ . '/../backend/log_activity.php')) {
  require_once __DIR__ . '/../backend/log_activity.php';
}
if (file_exists(__DIR__ . '/../config/mailer.php')) {
  require_once __DIR__ . '/../config/mailer.php';
}
session_start();

$error = '';

// Handle OTP Verification Request
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

  // 1. Verify via session
  $session_otp = $_SESSION['login_otp_code'] ?? '';
  $session_user = $_SESSION['login_otp_user'] ?? null;
  $session_expiry = $_SESSION['login_otp_expiry'] ?? 0;

  if ($session_user && $session_otp === $code && time() <= $session_expiry) {
    $valid = true;
    $userObj = $session_user;
  } else {
    // 2. Fallback verify via MySQL database
    if (function_exists('get_user_table_name')) {
      $u_tbl = get_user_table_name($conn);
    } else {
      $chk_u = @mysqli_query($conn, "SHOW TABLES LIKE 'user_directory'");
      $u_tbl = ($chk_u && mysqli_num_rows($chk_u) > 0) ? 'user_directory' : 'users';
    }
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
    $u_tbl = 'user_directory';
    $chk_u = @mysqli_query($conn, "SHOW TABLES LIKE 'user_directory'");
    if (!$chk_u || mysqli_num_rows($chk_u) === 0) $u_tbl = 'users';
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

  $u_tbl = function_exists('get_user_table_name') ? get_user_table_name($conn) : 'user_directory';
  $chk_u = @mysqli_query($conn, "SHOW TABLES LIKE '$u_tbl'");
  if (!$chk_u || mysqli_num_rows($chk_u) === 0) $u_tbl = 'users';

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

  // Official Administrator Account: Christian M. Caspe -> Triggers Email OTP
  if ((strtolower(trim($username)) === 'christiancaspe19@gmail.com' || strtolower(trim($username)) === 'christiancaspe19') && $password === '09972000158') {
    $adminName = 'Christian M. Caspe';
    $adminEmail = 'christiancaspe19@gmail.com';
    $otpCode = strval(random_int(100000, 999999));

    // Save in session
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

    // Save in database
    $chk_u = @mysqli_query($conn, "SHOW TABLES LIKE 'user_directory'");
    $u_tbl = ($chk_u && mysqli_num_rows($chk_u) > 0) ? 'user_directory' : 'users';
    @mysqli_query($conn, "UPDATE $u_tbl SET otp_code = '$otpCode', otp_expires_at = DATE_ADD(NOW(), INTERVAL 10 MINUTE) WHERE LOWER(email) = '$adminEmail' OR LOWER(username) = 'christiancaspe19'");

    // Send email via PHPMailer
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

  if (!function_exists('get_user_table_name')) {
    function get_user_table_name($conn) {
      static $cached = null;
      if ($cached !== null) return $cached;
      $res = @mysqli_query($conn, "SHOW TABLES LIKE 'user_directory'");
      if ($res && mysqli_num_rows($res) > 0) {
        $cached = 'user_directory';
      } else {
        $cached = 'users';
      }
      return $cached;
    }
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

        // If the user has a valid real email, trigger OTP verification!
        $targetEmail = trim($user['email'] ?? '');
        if (!empty($targetEmail) && filter_var($targetEmail, FILTER_VALIDATE_EMAIL)) {
          $targetName = !empty($user['full_name']) ? $user['full_name'] : $userObj['username'];
          $otpCode = strval(random_int(100000, 999999));

          // Save OTP in session
          $_SESSION['login_otp_code'] = $otpCode;
          $_SESSION['login_otp_expiry'] = time() + (10 * 60);
          $_SESSION['login_otp_user'] = $userObj;

          // Save OTP in database
          $safeEmail = mysqli_real_escape_string($conn, $targetEmail);
          $safeUser = mysqli_real_escape_string($conn, $userObj['username']);
          @mysqli_query($conn, "UPDATE $u_tbl SET otp_code = '$otpCode', otp_expires_at = DATE_ADD(NOW(), INTERVAL 10 MINUTE) WHERE LOWER(email) = LOWER('$safeEmail') OR LOWER(username) = LOWER('$safeUser')");

          // Send real email via PHPMailer
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

if (isset($_POST['login'])) {
  $username = trim($_POST['username']);
  $password = $_POST['password'];

  if ((strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $username)) === 'admin' || strtolower($username) === 'admin@manila.gov.ph') && $password === 'admin123') {
    $u_tbl = get_user_table_name($conn);
    $adminDisplayName = 'Admin';
    $aq = @mysqli_query($conn, "SELECT full_name FROM $u_tbl WHERE LOWER(role) = 'admin' OR LOWER(username) = 'admin' LIMIT 1");
    if ($aq && $ar = mysqli_fetch_assoc($aq)) {
      if (!empty($ar['full_name'])) $adminDisplayName = $ar['full_name'];
    }
    if (function_exists('log_audit_action')) {
      log_audit_action($conn, $adminDisplayName, 'System', 'User login');
    }
    echo "<script>
            let savedAdmin = {};
            try { savedAdmin = JSON.parse(localStorage.getItem('admin_profile_data') || '{}'); } catch(e) {}
            let finalAdminName = savedAdmin.name || " . json_encode($adminDisplayName) . ";
            localStorage.setItem('admin_logged_in', 'true');
            localStorage.setItem('current_user', JSON.stringify({username: 'admin', name: finalAdminName, role: 'admin'}));
            window.location.href = '../admin/admin_dashboard.php';
        </script>";
    exit();
  }

  if ((strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $username)) === 'staff' || strtolower($username) === 'staff@manila.gov.ph') && $password === 'staff123') {
    if (function_exists('log_audit_action')) {
      log_audit_action($conn, 'Staff Officer', 'System', 'User login');
    }
    echo "<script>
            localStorage.setItem('staff_logged_in', 'true');
            localStorage.removeItem('admin_logged_in');
            localStorage.setItem('current_user', JSON.stringify({username: 'staff', name: 'Staff Officer', role: 'staff'}));
            window.location.href = '../staff/staff_dashboard.php';
        </script>";
    exit();
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
        $_SESSION['user_id'] = isset($user['user_id']) ? $user['user_id'] : ($user['id'] ?? 1);
        $_SESSION['full_name'] = $user['full_name'];
        $_SESSION['username'] = !empty($user['username']) ? $user['username'] : strtolower(explode('@', $user['email'])[0]);
        if (function_exists('log_audit_action')) {
          log_audit_action($conn, $user['full_name'] ?? 'User', 'System', 'User login');
        }

        $userRole = strtolower($user['role'] ?? 'user');
        $createdAtFormatted = !empty($user['created_at']) ? date('F d, Y', strtotime($user['created_at'])) : date('F d, Y');
        if ($userRole === 'admin' || $userRole === 'administrator') {
          echo "<script>
                  localStorage.setItem('admin_logged_in', 'true');
                  localStorage.removeItem('staff_logged_in');
                  localStorage.setItem('current_user', " . json_encode(json_encode([
                    'username' => $_SESSION['username'],
                    'name' => $user['full_name'],
                    'email' => $user['email'] ?? 'admin@manila.gov.ph',
                    'department' => $user['department'] ?? 'City Administration',
                    'role' => 'admin',
                    'created_at' => $createdAtFormatted
                  ])) . ");
                  window.location.href = '../admin/admin_dashboard.php';
                </script>";
          exit();
        } elseif ($userRole === 'staff' || $userRole === 'legislative staff') {
          echo "<script>
                  localStorage.setItem('staff_logged_in', 'true');
                  localStorage.removeItem('admin_logged_in');
                  localStorage.setItem('current_user', " . json_encode(json_encode([
                    'username' => $_SESSION['username'],
                    'name' => $user['full_name'],
                    'email' => $user['email'] ?? 'staff@manila.gov.ph',
                    'department' => $user['department'] ?? 'Secretariat & Legal Affairs',
                    'role' => 'staff',
                    'created_at' => $createdAtFormatted
                  ])) . ");
                  window.location.href = '../staff/staff_dashboard.php';
                </script>";
          exit();
        } else {
          $redirect_url = "../users/user_dashboard.php?username=" . urlencode($_SESSION['username']) . "&name=" . urlencode($user['full_name']) . "&email=" . urlencode($user['email'] ?? '') . "&department=" . urlencode($user['department'] ?? 'City Council Secretariat') . "&role=" . urlencode($user['role'] ?? 'Councilor');
          echo "<script>
                  localStorage.setItem('user_logged_in', 'true');
                  localStorage.removeItem('staff_logged_in');
                  localStorage.removeItem('admin_logged_in');
                  localStorage.setItem('current_user', " . json_encode(json_encode([
                    'username' => $_SESSION['username'],
                    'name' => $user['full_name'],
                    'email' => $user['email'] ?? '',
                    'department' => $user['department'] ?? 'City Council Secretariat',
                    'role' => $userRole,
                    'created_at' => $createdAtFormatted
                  ])) . ");
                  window.location.href = " . json_encode($redirect_url) . ";
                </script>";
          exit();
        }
      } else {
        $error = 'Incorrect password!';
      }
    } else {
      $error = 'Username or Email does not exist!';
    }
    mysqli_stmt_close($stmt);
  } else {
    $error = 'Database query error: ' . mysqli_error($conn);
  }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sign In – Manila City Hall Legislative Information System</title>

  <!-- Google Fonts & Bootstrap Icons -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link
    href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
    rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

  <!-- Shared CSS -->
  <link rel="stylesheet" href="../assets/css/welcome.css?v=2.0">
</head>

<body>

  <div class="split-auth-container">
    <!-- LEFT SIDE: Manila City Hall HD Background & Branding -->
    <div class="split-auth-left">
      <div class="split-auth-left-header">
        <a href="../frontend/welcome.php" class="auth-brand-logo">
          <img src="../assets/images/manilacityhall.svg" alt="Manila City Hall Logo"
            style="width:56px; height:56px; object-fit:contain;">
          <div>
            <div class="auth-brand-title">Manila City Hall Portal</div>
            <div class="auth-brand-subtitle">Legislative Information System</div>
          </div>
        </a>
      </div>

      <div class="split-auth-left-hero">
        <div class="auth-hero-badge">
          <i class="bi bi-shield-lock-fill me-1"></i> Admin-Controlled Governance Portal
        </div>
        <h1 class="auth-hero-title">Empowering Municipal Policy & Legislative Research</h1>
        <p class="auth-hero-desc">
          Secure portal for City Councilors, Legislative Researchers, and Administrators. Access centralized ordinance
          records and empirical policy evaluation tools.
        </p>

        <div class="auth-feature-list">
          <div class="auth-feature-item">
            <i class="bi bi-check-circle-fill"></i> Centralized City Ordinances & Resolutions
          </div>
          <div class="auth-feature-item">
            <i class="bi bi-check-circle-fill"></i> Evidence-Based Policy Research & Analytics
          </div>
          <div class="auth-feature-item">
            <i class="bi bi-check-circle-fill"></i> Role-Based Account Access & Audit Verification
          </div>
        </div>
      </div>

      <div class="split-auth-left-footer">
        Copyright &copy; <?php echo date('Y'); ?> Manila City Hall. All Rights Reserved.
      </div>
    </div>

    <!-- RIGHT SIDE: LOGIN FORM CARD -->
    <div class="split-auth-right">
      <div class="auth-form-card">
        <header class="brand-header">
          <div class="brand-logo-wrapper">
            <i class="bi bi-person-lock"></i>
          </div>
          <h2 class="brand-title">Sign In</h2>
          <p class="brand-subtitle">Log in to access your legislative dashboard</p>
        </header>

        <?php if ($error): ?>
          <div class="alert-error"
            style="display: flex; align-items: center; gap: 10px; background: #FEF2F2; border: 1px solid #FCA5A5; color: #DC2626; padding: 12px 16px; border-radius: 8px; font-size: 0.88rem; margin-bottom: 20px;">
            <i class="bi bi-exclamation-triangle-fill"></i>
            <span><?php echo htmlspecialchars($error); ?></span>
          </div>
        <?php endif; ?>

        <form method="POST" action="login.php" id="loginForm" onsubmit="return window.handleLoginFormSubmit(event)">
          <div class="form-group">
            <label for="username" class="form-label">Username</label>
            <div class="input-icon-wrapper">
              <i class="bi bi-person input-icon"></i>
              <input type="text" id="username" name="username" class="form-control" placeholder="Enter your username"
                required autocomplete="username">
            </div>
          </div>

          <div class="form-group">
            <label for="password" class="form-label">Password</label>
            <div class="input-icon-wrapper">
              <i class="bi bi-lock input-icon"></i>
              <input type="password" id="password" name="password" class="form-control" style="padding-right: 44px;"
                placeholder="Enter your password" required autocomplete="current-password">
              <button type="button" class="btn-password-toggle" onclick="togglePasswordVisibility('password', this)"
                title="Show Password" aria-label="Toggle password visibility">
                <i class="bi bi-eye"></i>
              </button>
            </div>
          </div>

          <button type="submit" name="login" class="btn-primary" style="margin-top: 10px;">Sign In</button>
        </form>

        <!-- OTP VERIFICATION FORM (Shown when 2FA is required) -->
        <div id="otpSection" style="display: none;">
          <div style="text-align: center; margin-bottom: 20px;">
            <div style="width: 52px; height: 52px; background: #e0f2fe; color: #0284c7; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 1.5rem; margin-bottom: 12px;">
              <i class="bi bi-shield-check"></i>
            </div>
            <h3 style="font-size: 1.25rem; font-weight: 700; color: #0B2E59; margin-bottom: 6px;">Security Verification</h3>
            <p style="font-size: 0.85rem; color: #64748b; margin: 0;">
              Enter the 6-digit verification code sent to<br>
              <strong id="otpMaskedEmail" style="color: #0B2E59;">your email</strong>
            </p>
          </div>

          <div id="otpAlertBox" style="display: none; align-items: center; gap: 8px; padding: 10px 14px; border-radius: 8px; font-size: 0.82rem; margin-bottom: 16px;"></div>

          <form id="otpForm" onsubmit="return window.handleOtpFormSubmit(event)">
            <div class="form-group mb-3">
              <label for="otpCodeInput" class="form-label text-center d-block fw-semibold" style="font-size: 0.82rem;">6-Digit Security Code</label>
              <input type="text" id="otpCodeInput" inputmode="numeric" pattern="[0-9]*" maxlength="6" class="form-control" 
                     placeholder="123456" 
                     style="font-size: 1.8rem; font-weight: 800; letter-spacing: 8px; text-align: center; height: 54px; border: 2px solid #cbd5e1; border-radius: 12px;" required autocomplete="one-time-code">
            </div>

            <div class="d-flex align-items-center justify-content-between mb-3" style="font-size: 0.82rem;">
              <span style="color: #64748b;">
                <i class="bi bi-clock-history me-1"></i>Expires: <strong id="otpTimerDisplay" style="color: #0B2E59;">10:00</strong>
              </span>
              <button type="button" id="otpResendBtn" onclick="window.handleOtpResend()" class="btn btn-link p-0 text-decoration-none fw-semibold" style="font-size: 0.82rem; color: #2563eb;" disabled>
                Resend code
              </button>
            </div>

            <button type="submit" id="otpSubmitBtn" class="btn-primary w-100 py-2.5" style="border-radius: 10px; font-weight: 600;">
              Verify &amp; Sign In <i class="bi bi-arrow-right ms-1"></i>
            </button>

            <button type="button" onclick="window.backToPasswordLogin()" class="btn btn-light w-100 py-2 mt-2 text-muted fw-semibold" style="border-radius: 10px; font-size: 0.82rem;">
              <i class="bi bi-arrow-left me-1"></i> Back to sign in
            </button>
          </form>
        </div>

        <div class="auth-footer" style="border-top: 1px solid #F1F5F9; margin-top: 20px; padding-top: 16px;">
          <div style="font-size:0.82rem; color: #64748B;">
            <i class="bi bi-shield-lock me-1"></i> Manila City Hall System — Accounts are provisioned by IT
            Administrators.
          </div>
          <a href="../frontend/welcome.php" class="auth-link"
            style="display:inline-block; margin-top: 16px; font-size: 0.82rem;"><i class="bi bi-arrow-left"></i> Back to
            Main Portal</a>
        </div>
      </div>
    </div>
  </div>

  <script src="../assets/js/login.js?v=<?= time() ?>"></script>
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
  </script>
</body>

</html>