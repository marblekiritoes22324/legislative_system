<?php
include __DIR__ . '/../config/db.php';
if (file_exists(__DIR__ . '/../backend/log_activity.php')) {
  require_once __DIR__ . '/../backend/log_activity.php';
}
session_start();

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $raw_user = trim($_POST['username'] ?? '');
  $raw_pass = trim($_POST['password'] ?? '');

  $clean_user = preg_replace('/[^a-zA-Z0-9]/', '', strtolower($raw_user));

  if ($clean_user === 'admin') {
    if ($raw_pass === 'admin123') {
      $adminDisplayName = 'Admin';
      if (function_exists('log_audit_action') && !empty($conn)) {
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
    } else {
      $error = 'Incorrect password.';
    }
  } elseif ($clean_user === 'user') {
    if ($raw_pass === 'password') {
      if (function_exists('log_audit_action') && !empty($conn)) {
        log_audit_action($conn, 'Juan Dela Cruz', 'System', 'User login');
      }
      echo "<script>
        localStorage.setItem('current_user', JSON.stringify({username: 'user', name: 'Juan Dela Cruz', role: 'user'}));
        window.location.href = '../users/user_dashboard.php?username=user&name=Juan%20Dela%20Cruz';
      </script>";
      exit();
    } else {
      $error = 'Incorrect password.';
    }
  } else {
    $error = 'Username does not exist.';
  }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Services – Manila City Hall Legislative Information System</title>
  <!-- Google Fonts & Bootstrap Icons -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link
    href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
    rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <!-- Shared Stylesheet -->
  <link rel="stylesheet" href="../assets/css/welcome.css?v=2.0">
  <style>
    body {
      margin: 0;
      padding: 0;
      overflow-x: hidden;
    }
  </style>
</head>

<body>

  <!-- FULL-WIDTH WHITE NAVIGATION HEADER (PICTURE 1 LAYOUT) -->
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
      <li><a href="welcome.php" class="nav-link-item">Home</a></li>
      <li><a href="about.php" class="nav-link-item">About</a></li>
      <li><a href="public_ordinances.php" class="nav-link-item">Public Ordinances</a></li>
      <li><a href="services.php" class="nav-link-item active">Services</a></li>
      <li><a href="contact.php" class="nav-link-item">Contact Us</a></li>
    </ul>

    <!-- Right Side Actions Buttons (Admin-controlled account access) -->
    <div class="navbar-actions" style="display: flex; align-items: center; gap: 12px;">
      <a href="welcome.php?login=1" class="btn-nav-outline"
        style="text-decoration: none; padding: 9px 22px; border: 1.5px solid #0B1B3D; color: #0B1B3D; border-radius: 8px; font-weight: 700; font-size: 0.9rem; display: inline-flex; align-items: center; gap: 6px;"><i
          class="bi bi-box-arrow-in-right"></i> Sign In</a>
    </div>
  </nav>

  <!-- SPLIT CONTAINER BELOW NAVBAR -->
  <div style="margin-top: 80px; display: flex; min-height: calc(100vh - 80px); width: 100%;">

    <!-- LEFT COLUMN: NAVY OVERLAY + MANILA CITY HALL BG -->
    <div
      style="flex: 1.15; position: relative; background-image: url('../assets/images/manila-city-hall-hd.jpg'), url('../assets/images/manila-city-hall-hd.png'); background-size: cover; background-position: center; background-repeat: no-repeat; display: flex; flex-direction: column; justify-content: space-between; padding: 40px; color: #FFFFFF; min-height: calc(100vh - 80px); overflow-y: auto;">

      <!-- Navy Blue Overlay -->
      <div
        style="position: absolute; inset: 0; background: linear-gradient(135deg, rgba(7, 19, 44, 0.82) 0%, rgba(11, 27, 61, 0.78) 50%, rgba(11, 27, 61, 0.88) 100%); z-index: 1; pointer-events: none;">
      </div>

      <!-- Center Services Content -->
      <div style="position: relative; z-index: 2; margin: 30px 0;">
        <div style="max-width: 850px; margin: 0 auto;">
          <span
            style="background: rgba(212,175,55,0.18); border: 1px solid rgba(212,175,55,0.4); color: #D4AF37; font-size: 0.8rem; font-weight: 700; letter-spacing: 1px; text-transform: uppercase; padding: 4px 14px; border-radius: 50px;">System
            Services</span>
          <h2
            style="font-family: 'Outfit', sans-serif; font-size: 2rem; font-weight: 800; color: #FFFFFF; margin: 8px 0 16px 0;">
            Legislative Management Capabilities</h2>

          <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 14px;">
            <div
              style="background: rgba(11,27,61,0.82); border: 1px solid rgba(212,175,55,0.35); border-radius: 12px; padding: 18px 16px; backdrop-filter: blur(12px);">
              <div style="font-size: 1.4rem; color: #D4AF37; margin-bottom: 6px;"><i
                  class="bi bi-folder-symlink-fill"></i></div>
              <h3 style="font-size: 0.95rem; color: #fff; margin-bottom: 4px;">Document Repository</h3>
              <p style="font-size: 0.8rem; color: rgba(255,255,255,0.75); line-height: 1.4; margin: 0;">Centralized
                digital storage for ordinances, resolutions, and meeting minutes.</p>
            </div>

            <div
              style="background: rgba(11,27,61,0.82); border: 1px solid rgba(212,175,55,0.35); border-radius: 12px; padding: 18px 16px; backdrop-filter: blur(12px);">
              <div style="font-size: 1.4rem; color: #D4AF37; margin-bottom: 6px;"><i class="bi bi-journal-text"></i>
              </div>
              <h3 style="font-size: 0.95rem; color: #fff; margin-bottom: 4px;">Policy Research Library</h3>
              <p style="font-size: 0.8rem; color: rgba(255,255,255,0.75); line-height: 1.4; margin: 0;">Access municipal
                policy briefs and legal benchmarks for evidence-based laws.</p>
            </div>

            <div
              style="background: rgba(11,27,61,0.82); border: 1px solid rgba(212,175,55,0.35); border-radius: 12px; padding: 18px 16px; backdrop-filter: blur(12px);">
              <div style="font-size: 1.4rem; color: #D4AF37; margin-bottom: 6px;"><i class="bi bi-people-fill"></i>
              </div>
              <h3 style="font-size: 0.95rem; color: #fff; margin-bottom: 4px;">Public Ordinance Access</h3>
              <p style="font-size: 0.8rem; color: rgba(255,255,255,0.75); line-height: 1.4; margin: 0;">Open
                citizen-facing portal providing searchable public access to enacted laws.</p>
            </div>

            <div
              style="background: rgba(11,27,61,0.82); border: 1px solid rgba(212,175,55,0.35); border-radius: 12px; padding: 18px 16px; backdrop-filter: blur(12px);">
              <div style="font-size: 1.4rem; color: #D4AF37; margin-bottom: 6px;"><i class="bi bi-graph-up-arrow"></i>
              </div>
              <h3 style="font-size: 0.95rem; color: #fff; margin-bottom: 4px;">Impact Evaluation</h3>
              <p style="font-size: 0.8rem; color: rgba(255,255,255,0.75); line-height: 1.4; margin: 0;">Analytical
                evaluation frameworks measuring socio-economic and policy impacts.</p>
            </div>

            <div
              style="background: rgba(11,27,61,0.82); border: 1px solid rgba(212,175,55,0.35); border-radius: 12px; padding: 18px 16px; backdrop-filter: blur(12px);">
              <div style="font-size: 1.4rem; color: #D4AF37; margin-bottom: 6px;"><i
                  class="bi bi-filter-square-fill"></i></div>
              <h3 style="font-size: 0.95rem; color: #fff; margin-bottom: 4px;">Advanced Search</h3>
              <p style="font-size: 0.8rem; color: rgba(255,255,255,0.75); line-height: 1.4; margin: 0;">Multi-criteria
                search engine allowing instant query filtering by keyword and year.</p>
            </div>

            <div
              style="background: rgba(11,27,61,0.82); border: 1px solid rgba(212,175,55,0.35); border-radius: 12px; padding: 18px 16px; backdrop-filter: blur(12px);">
              <div style="font-size: 1.4rem; color: #D4AF37; margin-bottom: 6px;"><i class="bi bi-shield-lock-fill"></i>
              </div>
              <h3 style="font-size: 0.95rem; color: #fff; margin-bottom: 4px;">Account Management</h3>
              <p style="font-size: 0.8rem; color: rgba(255,255,255,0.75); line-height: 1.4; margin: 0;">Role-based
                authentication for councilors, research staff, and administrators.</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Copyright at Bottom Left -->
      <div
        style="position: relative; z-index: 2; font-size: 0.85rem; color: rgba(255,255,255,0.75); border-top: 1px solid rgba(255,255,255,0.18); padding-top: 16px;">
        Copyright &copy; <?php echo date('Y'); ?> Manila City Hall. All Rights Reserved.
      </div>
    </div>

    <!-- RIGHT COLUMN: MEMBER SIGN IN FORM -->
    <div
      style="flex: 0.85; background: #F8FAFC; display: flex; align-items: center; justify-content: center; padding: 40px 30px; border-left: 1px solid #E2E8F0; min-height: calc(100vh - 80px);">
      <div class="auth-form-card"
        style="width: 100%; max-width: 420px; background: #FFFFFF; border: 1px solid var(--border-color); border-radius: 18px; padding: 38px 32px; box-shadow: 0 10px 30px rgba(11,27,61,0.06);">
        <header class="brand-header" style="text-align: center; margin-bottom: 24px;">
          <div class="brand-logo-wrapper"
            style="width: 60px; height: 60px; margin: 0 auto 14px auto; border-radius: 50%; background: rgba(11,27,61,0.06); border: 2px solid rgba(11,27,61,0.1); display: flex; align-items: center; justify-content: center; font-size: 1.6rem; color: var(--primary-navy);">
            <i class="bi bi-person-lock"></i>
          </div>
          <h2 class="brand-title"
            style="font-family: 'Outfit', sans-serif; font-size: 1.6rem; font-weight: 700; color: var(--primary-navy); margin-bottom: 6px;">
            Sign In</h2>
          <p class="brand-subtitle" style="font-size: 0.88rem; color: var(--text-muted);">Log in to access your
            legislative dashboard</p>
        </header>

        <!-- Error Alert Box -->
        <div id="errorAlert" class="alert-error"
          style="display: <?php echo !empty($error) ? 'flex' : 'none'; ?>; align-items: center; gap: 10px; background: #FEF2F2; border: 1px solid #FCA5A5; color: #DC2626; padding: 12px 16px; border-radius: 8px; font-size: 0.88rem; margin-bottom: 20px;">
          <i class="bi bi-exclamation-triangle-fill"></i>
          <span id="errorMessage"><?php echo htmlspecialchars($error); ?></span>
        </div>

        <form id="loginForm" method="POST" action="services.php" onsubmit="return window.handleLoginFormSubmit(event)">
          <!-- Username Field -->
          <div class="form-group" style="margin-bottom: 18px;">
            <label for="username" class="form-label"
              style="display: block; font-size: 0.88rem; font-weight: 600; color: var(--text-dark); margin-bottom: 6px;">Username</label>
            <div class="input-icon-wrapper" style="position: relative; display: flex; align-items: center;">
              <i class="bi bi-person input-icon"
                style="position: absolute; left: 14px; color: var(--text-muted); font-size: 1.1rem; pointer-events: none;"></i>
              <input type="text" id="username" name="username" class="form-control"
                style="width: 100%; padding: 12px 16px 12px 44px; border: 1px solid var(--border-color); border-radius: 8px; font-size: 0.95rem; background: #F8FAFC;"
                placeholder="Enter your username" required autocomplete="username">
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