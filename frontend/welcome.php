<?php
include __DIR__ . '/../config/db.php';
if (file_exists(__DIR__ . '/../backend/log_activity.php')) {
  require_once __DIR__ . '/../backend/log_activity.php';
}
session_start();

if (!function_exists('get_user_table_name')) {
  function get_user_table_name($conn)
  {
    static $cached = null;
    if ($cached !== null)
      return $cached;
    $res = @mysqli_query($conn, "SHOW TABLES LIKE 'user_directory'");
    if ($res && mysqli_num_rows($res) > 0)
      return 'user_directory';
    return 'users';
  }
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $raw_user = trim($_POST['username'] ?? '');
  $raw_pass = trim($_POST['password'] ?? '');

  if (empty($raw_user) || empty($raw_pass)) {
    $error = 'Please enter username and password.';
  } else {
    $clean_user = preg_replace('/[^a-zA-Z0-9]/', '', strtolower($raw_user));
    $lower_raw = strtolower($raw_user);

    // 1. Super Admin Hardcoded Check
    if ($clean_user === 'admin' || $lower_raw === 'admin@manila.gov.ph') {
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
          localStorage.removeItem('staff_logged_in');
          localStorage.setItem('current_user', JSON.stringify({username: 'admin', name: finalAdminName, role: 'admin'}));
          window.location.href = '../admin/admin_dashboard.php';
        </script>";
        exit();
      } else {
        $error = 'Incorrect password.';
      }
    }
    // 2. Default Staff Hardcoded Check
    else if (($clean_user === 'staff' || $lower_raw === 'staff@manila.gov.ph') && $raw_pass === 'staff123') {
      if (function_exists('log_audit_action') && !empty($conn)) {
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
    // 3. MySQL Database User Directory Lookup
    else {
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
                window.location.href = '../admin/admin_dashboard.php';
              </script>";
              exit();
            } else if ($role === 'staff' || $role === 'legislative staff') {
              echo "<script>
                localStorage.setItem('staff_logged_in', 'true');
                localStorage.removeItem('admin_logged_in');
                localStorage.setItem('current_user', " . json_encode(json_encode(['username' => $user['username'], 'name' => $user['full_name'], 'email' => $user['email'] ?? 'staff@manila.gov.ph', 'department' => $user['department'] ?? 'Secretariat & Legal Affairs', 'role' => 'staff', 'created_at' => $createdAtFormatted])) . ");
                window.location.href = '../staff/staff_dashboard.php';
              </script>";
              exit();
            } else {
              // Councilor / User Portal
              $redirect_url = "../users/user_dashboard.php?username=" . urlencode($user['username']) . "&name=" . urlencode($user['full_name']) . "&email=" . urlencode($user['email'] ?? '') . "&department=" . urlencode($user['department'] ?? 'City Council Secretariat') . "&role=" . urlencode($user['role'] ?? 'Councilor');
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
  <title>Manila City Hall Portal – Legislative Information System</title>
  <!-- Google Fonts & Bootstrap Icons -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link
    href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
    rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <!-- Shared Stylesheet -->
  <link rel="stylesheet" href="../assets/css/welcome.css?v=2.1">
  <style>
    body {
      margin: 0;
      padding: 0;
      overflow-x: hidden;
      display: flex;
      flex-direction: column;
      min-height: 100vh;
    }

    /* Modal Backdrop and Animation */
    .modal-overlay {
      position: fixed;
      inset: 0;
      z-index: 2000;
      display: none;
      align-items: center;
      justify-content: center;
      background: rgba(7, 19, 44, 0.72);
      backdrop-filter: blur(8px);
      -webkit-backdrop-filter: blur(8px);
      padding: 20px;
      box-sizing: border-box;
      opacity: 0;
      transition: opacity 0.25s ease;
    }

    .modal-overlay.active {
      display: flex;
      opacity: 1;
    }

    .modal-dialog-card {
      width: 100%;
      max-width: 440px;
      background: #FFFFFF;
      border: 1px solid var(--border-color);
      border-radius: 20px;
      padding: 38px 32px;
      box-shadow: 0 25px 60px rgba(0, 0, 0, 0.35);
      position: relative;
      transform: scale(0.92);
      transition: transform 0.25s cubic-bezier(0.16, 1, 0.3, 1);
    }

    .modal-overlay.active .modal-dialog-card {
      transform: scale(1);
    }

    .modal-close-btn {
      position: absolute;
      top: 18px;
      right: 18px;
      width: 36px;
      height: 36px;
      border-radius: 50%;
      background: #F1F5F9;
      border: none;
      display: flex;
      align-items: center;
      justify-content: center;
      color: #64748B;
      font-size: 1.1rem;
      cursor: pointer;
      transition: all 0.2s ease;
    }

    .modal-close-btn:hover {
      background: #E2E8F0;
      color: #0F172A;
      transform: rotate(90deg);
    }

    .btn-nav-outline:hover {
      background: #0B1B3D !important;
      color: #FFFFFF !important;
    }
  </style>
</head>

<body>

  <!-- FULL-WIDTH WHITE NAVIGATION HEADER -->
  <nav class="navbar" id="navbar">
    <a href="welcome.php" class="navbar-brand">
      <img src="../assets/images/manilacityhall.svg" alt="Manila City Hall Logo"
        style="width:48px; height:48px; object-fit:contain;">
      <div class="brand-text-container">
        <span class="brand-title-text">Manila City Hall Portal</span>
        <span class="brand-subtitle-text">Legislative Information System</span>
      </div>
    </a>

    <!-- Center Navigation Links -->
    <ul class="nav-menu">
      <li><a href="welcome.php" class="nav-link-item active">Home</a></li>
      <li><a href="about.php" class="nav-link-item">About</a></li>
      <li><a href="public_ordinances.php" class="nav-link-item">Ordinances</a></li>
      <li><a href="contact.php" class="nav-link-item">Contact</a></li>
    </ul>

    <!-- Right Side Actions Buttons -->
    <div class="navbar-actions" style="display: flex; align-items: center; gap: 12px;">
      <a href="javascript:void(0)" onclick="openSignInModal()" class="btn-nav-outline"
        style="text-decoration: none; padding: 9px 22px; border: 1.5px solid #0B1B3D; color: #0B1B3D; border-radius: 8px; font-weight: 700; font-size: 0.9rem; display: inline-flex; align-items: center; gap: 6px; cursor: pointer; transition: all 0.2s;"><i
          class="bi bi-box-arrow-in-right"></i> Sign In</a>
    </div>
  </nav>

  <!-- FULL-WIDTH HERO SECTION (NO SPLIT SCREEN) -->
  <div
    style="margin-top: 80px; position: relative; min-height: calc(100vh - 80px); width: 100%; display: flex; align-items: center; justify-content: center; background-image: url('../assets/images/manila-city-hall-hd.jpg'), url('../assets/images/manila-city-hall-hd.png'); background-size: cover; background-position: center; background-repeat: no-repeat; padding: 80px 24px; box-sizing: border-box;">

    <!-- Navy Blue Overlay -->
    <div
      style="position: absolute; inset: 0; background: linear-gradient(135deg, rgba(7, 19, 44, 0.88) 0%, rgba(11, 27, 61, 0.82) 50%, rgba(11, 27, 61, 0.92) 100%); z-index: 1; pointer-events: none;">
    </div>

    <!-- Center Hero Content -->
    <div style="position: relative; z-index: 2; text-align: center; max-width: 960px; margin: 0 auto; width: 100%;">

      <!-- Badge -->
      <span
        style="background: rgba(212,175,55,0.2); border: 1px solid rgba(212,175,55,0.45); color: #D4AF37; font-size: 0.85rem; font-weight: 700; letter-spacing: 1.2px; text-transform: uppercase; padding: 6px 20px; border-radius: 50px; display: inline-block; margin-bottom: 22px; box-shadow: 0 4px 15px rgba(0,0,0,0.2);">
        Official Municipal Portal
      </span>

      <!-- Main Headline -->
      <h1
        style="font-family: 'Outfit', sans-serif; font-size: clamp(2.2rem, 5vw, 3.4rem); font-weight: 800; color: #FFFFFF; line-height: 1.2; text-shadow: 0 4px 24px rgba(0,0,0,0.8); margin: 0 auto 20px auto; max-width: 900px;">
        Legislative Research, Policy Analysis, and Impact Evaluation System
      </h1>

      <!-- Subtitle -->
      <p
        style="font-size: clamp(1rem, 2vw, 1.15rem); color: rgba(255,255,255,0.9); max-width: 760px; margin: 0 auto; line-height: 1.65; text-shadow: 0 2px 10px rgba(0,0,0,0.5);">
        Centralizing Manila City Hall ordinances, policy documents, and legislative archives into a secure digital
        platform.
      </p>

    </div>
  </div>

  <!-- SIGN IN MODAL (POPUPS ON 'SIGN IN' CLICK) -->
  <div id="signInModal" class="modal-overlay" role="dialog" aria-modal="true" aria-labelledby="modalTitle">
    <div class="modal-dialog-card">

      <!-- Close Button -->
      <button type="button" class="modal-close-btn" onclick="closeSignInModal()" aria-label="Close modal">
        <i class="bi bi-x-lg"></i>
      </button>

      <header class="brand-header" style="text-align: center; margin-bottom: 24px;">
        <div class="brand-logo-wrapper"
          style="width: 60px; height: 60px; margin: 0 auto 14px auto; border-radius: 50%; background: rgba(11,27,61,0.06); border: 2px solid rgba(11,27,61,0.1); display: flex; align-items: center; justify-content: center; font-size: 1.6rem; color: var(--primary-navy);">
          <i class="bi bi-person-lock"></i>
        </div>
        <h2 id="modalTitle" class="brand-title"
          style="font-family: 'Outfit', sans-serif; font-size: 1.6rem; font-weight: 700; color: var(--primary-navy); margin-bottom: 6px;">
          Sign In</h2>
        <p class="brand-subtitle" style="font-size: 0.88rem; color: var(--text-muted); margin: 0;">Log in to access your
          legislative dashboard</p>
      </header>

      <!-- Error Alert Box -->
      <div id="errorAlert" class="alert-error"
        style="display: <?php echo !empty($error) ? 'flex' : 'none'; ?>; align-items: center; gap: 10px; background: #FEF2F2; border: 1px solid #FCA5A5; color: #DC2626; padding: 12px 16px; border-radius: 8px; font-size: 0.88rem; margin-bottom: 20px;">
        <i class="bi bi-exclamation-triangle-fill"></i>
        <span id="errorMessage"><?php echo htmlspecialchars($error); ?></span>
      </div>

      <form id="loginForm" method="POST" action="welcome.php" onsubmit="return window.handleLoginFormSubmit(event)">
        <!-- Username Field -->
        <div class="form-group" style="margin-bottom: 18px;">
          <label for="username" class="form-label"
            style="display: block; font-size: 0.88rem; font-weight: 600; color: var(--text-dark); margin-bottom: 6px;">Username
            or Email</label>
          <div class="input-icon-wrapper" style="position: relative; display: flex; align-items: center;">
            <i class="bi bi-person input-icon"
              style="position: absolute; left: 14px; color: var(--text-muted); font-size: 1.1rem; pointer-events: none;"></i>
            <input type="text" id="username" name="username" class="form-control"
              style="width: 100%; padding: 12px 16px 12px 44px; border: 1px solid var(--border-color); border-radius: 8px; font-size: 0.95rem; background: #F8FAFC;"
              placeholder="Enter your username or email" required autocomplete="username">
          </div>
        </div>

        <!-- Password Field -->
        <div class="form-group" style="margin-bottom: 22px;">
          <label for="password" class="form-label"
            style="display: block; font-size: 0.88rem; font-weight: 600; color: var(--text-dark); margin-bottom: 6px;">Password</label>
          <div class="input-icon-wrapper" style="position: relative; display: flex; align-items: center;">
            <i class="bi bi-lock input-icon"
              style="position: absolute; left: 14px; color: var(--text-muted); font-size: 1.1rem; pointer-events: none;"></i>
            <input type="password" id="password" name="password" class="form-control"
              style="width: 100%; padding: 12px 44px 12px 44px; border: 1px solid var(--border-color); border-radius: 8px; font-size: 0.95rem; background: #F8FAFC;"
              placeholder="Enter your password" required autocomplete="current-password">
            <button type="button" class="btn-password-toggle" onclick="togglePasswordVisibility('password', this)"
              style="position: absolute; right: 12px; background: none; border: none; padding: 6px; color: #64748b; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 1.15rem; z-index: 5;"
              title="Show Password" aria-label="Toggle password visibility">
              <i class="bi bi-eye"></i>
            </button>
          </div>
        </div>

        <button type="submit" class="btn-primary"
          style="width: 100%; padding: 14px; background: var(--primary-navy); color: #FFFFFF; border: none; border-radius: 8px; font-size: 1rem; font-weight: 700; cursor: pointer; transition: background 0.25s;">Sign
          In</button>
      </form>

      <div class="auth-footer"
        style="text-align: center; margin-top: 24px; font-size: 0.82rem; color: var(--text-muted); border-top: 1px solid #F1F5F9; padding-top: 16px;">
        <i class="bi bi-shield-lock me-1"></i> Manila City Hall System — Accounts are provisioned by IT
        Administrators.
      </div>
    </div>
  </div>

  <!-- FOOTER SECTION -->
  <footer
    style="background: #0B1B3D; color: #FFFFFF; padding: 28px 40px 16px 40px; border-top: 2px solid #D4AF37; width: 100%; box-sizing: border-box;">
    <div style="width: 100%; margin: 0 auto;">
      <!-- Grid 4 Columns -->
      <div
        style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 24px; margin-bottom: 20px;">

        <!-- Column 1: Brand Info -->
        <div>
          <h3
            style="font-family: 'Outfit', sans-serif; font-size: 1.08rem; font-weight: 800; color: #FFFFFF; margin: 0 0 6px 0;">
            Manila City Hall Portal</h3>
          <p style="font-size: 0.84rem; color: rgba(255,255,255,0.75); line-height: 1.5; margin: 0; max-width: 440px;">
            Legislative Information System — A modern digital platform supporting evidence-based policymaking,
            transparent public ordinance access, and municipal document archiving.
          </p>
        </div>

        <!-- Column 2: Quick Links -->
        <div>
          <h4
            style="font-family: 'Outfit', sans-serif; font-size: 0.92rem; font-weight: 700; color: #D4AF37; margin: 0 0 8px 0;">
            Quick Links</h4>
          <ul
            style="list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 5px; font-size: 0.85rem;">
            <li><a href="welcome.php" style="color: rgba(255,255,255,0.85); text-decoration: none;">Home</a></li>
            <li><a href="about.php" style="color: rgba(255,255,255,0.85); text-decoration: none;">About System</a></li>
            <li><a href="public_ordinances.php" style="color: rgba(255,255,255,0.85); text-decoration: none;">Public
                Ordinances</a></li>
            <li><a href="contact.php" style="color: rgba(255,255,255,0.85); text-decoration: none;">Contact Offices</a>
            </li>
          </ul>
        </div>

        <!-- Column 3: Portals & Legal -->
        <div>
          <h4
            style="font-family: 'Outfit', sans-serif; font-size: 0.92rem; font-weight: 700; color: #D4AF37; margin: 0 0 8px 0;">
            Portals &amp; Legal</h4>
          <ul
            style="list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 5px; font-size: 0.85rem;">
            <li><a href="javascript:void(0)" onclick="openSignInModal(); return false;"
                style="color: rgba(255,255,255,0.85); text-decoration: none;">Sign In</a></li>
            <li><a href="about.php" style="color: rgba(255,255,255,0.85); text-decoration: none;">Privacy Policy</a>
            </li>
            <li><a href="about.php" style="color: rgba(255,255,255,0.85); text-decoration: none;">Terms of Use</a></li>
          </ul>
        </div>

        <!-- Column 4: Contact Information -->
        <div>
          <h4
            style="font-family: 'Outfit', sans-serif; font-size: 0.92rem; font-weight: 700; color: #D4AF37; margin: 0 0 8px 0;">
            Contact Information</h4>
          <div
            style="font-size: 0.85rem; color: rgba(255,255,255,0.85); display: flex; flex-direction: column; gap: 5px;">
            <div><i class="bi bi-geo-alt-fill me-1" style="color: #D4AF37;"></i> Manila City Hall, Ermita, Manila</div>
            <div><i class="bi bi-telephone-fill me-1" style="color: #D4AF37;"></i> +63 (2) 8527-0909</div>
            <div><i class="bi bi-envelope-fill me-1" style="color: #D4AF37;"></i> legislative@manila.gov.ph</div>
          </div>
        </div>

      </div>

      <!-- Bottom Sub-footer -->
      <div
        style="border-top: 1px solid rgba(255,255,255,0.12); padding-top: 12px; display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; font-size: 0.78rem; color: rgba(255,255,255,0.65); gap: 10px;">
        <div>Copyright &copy; <?php echo date('Y'); ?> Manila City Hall. All Rights Reserved.</div>
        <div>Designed for City Council Legislative Research Offices</div>
      </div>
    </div>
  </footer>

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

    // Close on clicking backdrop
    window.addEventListener('click', function (e) {
      var modal = document.getElementById('signInModal');
      if (e.target === modal) {
        closeSignInModal();
      }
    });

    // Close on Escape key
    window.addEventListener('keydown', function (e) {
      if (e.key === 'Escape') {
        closeSignInModal();
      }
    });

    // Auto-open modal if requested via URL or server error
    document.addEventListener('DOMContentLoaded', function () {
      var urlParams = new URLSearchParams(window.location.search);
      if (urlParams.get('login') === '1' || window.location.hash === '#signin' || window.location.hash === '#login' || <?php echo !empty($error) ? 'true' : 'false'; ?>) {
        openSignInModal();
      }
    });
  </script>
</body>

</html>