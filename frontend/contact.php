<?php
session_start();

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $raw_user = trim($_POST['username'] ?? '');
  $raw_pass = trim($_POST['password'] ?? '');

  $clean_user = preg_replace('/[^a-zA-Z0-9]/', '', strtolower($raw_user));

  if ($clean_user === 'admin') {
    if ($raw_pass === 'admin123') {
      echo "<script>
        localStorage.setItem('admin_logged_in', 'true');
        localStorage.setItem('current_user', JSON.stringify({username: 'admin', name: 'System Administrator', role: 'admin'}));
        window.location.href = '../admin/admin_dashboard.php';
      </script>";
      exit();
    } else {
      $error = 'Incorrect password.';
    }
  } elseif ($clean_user === 'user') {
    if ($raw_pass === 'password') {
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
  <title>Contact Us – Manila City Hall Legislative Information System</title>
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
      <li><a href="welcome.php" class="nav-link-item">Home</a></li>
      <li><a href="about.php" class="nav-link-item">About</a></li>
      <li><a href="public_ordinances.php" class="nav-link-item">Ordinances</a></li>
      <li><a href="contact.php" class="nav-link-item active">Contact</a></li>
    </ul>

    <!-- Right Side Actions Buttons -->
    <div class="navbar-actions" style="display: flex; align-items: center; gap: 12px;">
      <a href="welcome.php?login=1" class="btn-nav-outline"
        style="text-decoration: none; padding: 9px 22px; border: 1.5px solid #0B1B3D; color: #0B1B3D; border-radius: 8px; font-weight: 700; font-size: 0.9rem; display: inline-flex; align-items: center; gap: 6px;"><i
          class="bi bi-box-arrow-in-right"></i> Sign In</a>
    </div>
  </nav>

  <!-- FULL-WIDTH CONTENT CONTAINER -->
  <div
    style="margin-top: 80px; min-height: calc(100vh - 80px); background: #F8FAFC; padding: 60px 24px 60px 24px; display: flex; flex-direction: column; justify-content: space-between; box-sizing: border-box;">

    <div style="max-width: 1240px; margin: 0 auto; width: 100%;">
      <!-- Section Header -->
      <div style="margin-bottom: 44px; text-align: center;">
        <h1
          style="font-family: 'Outfit', sans-serif; font-size: 2.5rem; font-weight: 800; color: #0B1B3D; margin: 0 0 12px 0; line-height: 1.2;">
          Contact Us
        </h1>
        <p style="font-size: 1.08rem; color: #64748B; max-width: 760px; margin: 0 auto; line-height: 1.6;">
          We are here to assist you. Reach out to Manila City Hall Legislative Information System offices for inquiries
          and public assistance.
        </p>
      </div>

      <!-- Legislative Contact Cards Grid -->
      <div
        style="display: grid; grid-template-columns: repeat(auto-fit, minmax(340px, 1fr)); gap: 24px; margin-bottom: 40px;">

        <!-- Card 1: Main Location -->
        <div
          style="background: #FFFFFF; border: 1px solid #E2E8F0; border-top: 4px solid #2563EB; border-radius: 16px; padding: 32px 28px; box-shadow: 0 4px 20px rgba(11,27,61,0.04); display: flex; gap: 20px; align-items: flex-start;">
          <div
            style="width: 52px; height: 52px; border-radius: 14px; background: #EFF6FF; color: #2563EB; display: flex; align-items: center; justify-content: center; font-size: 1.6rem; flex-shrink: 0;">
            <i class="bi bi-geo-alt-fill"></i>
          </div>
          <div>
            <h3
              style="font-family: 'Outfit', sans-serif; font-size: 1.18rem; font-weight: 700; color: #0B1B3D; margin: 0 0 8px 0;">
              Manila City Hall Address</h3>
            <p style="font-size: 0.94rem; color: #334155; margin: 0 0 8px 0; font-weight: 600; line-height: 1.45;">
              Manila
              City Hall, Padre Burgos Ave, Ermita, Manila, 1000 Metro Manila</p>
            <p style="font-size: 0.86rem; color: #64748B; margin: 0;">Executive Building, 2nd Floor — Legislative
              Secretariat Office</p>
          </div>
        </div>

        <!-- Card 2: Telephone Landlines -->
        <div
          style="background: #FFFFFF; border: 1px solid #E2E8F0; border-top: 4px solid #16A34A; border-radius: 16px; padding: 32px 28px; box-shadow: 0 4px 20px rgba(11,27,61,0.04); display: flex; gap: 20px; align-items: flex-start;">
          <div
            style="width: 52px; height: 52px; border-radius: 14px; background: #F0FDF4; color: #16A34A; display: flex; align-items: center; justify-content: center; font-size: 1.6rem; flex-shrink: 0;">
            <i class="bi bi-telephone-fill"></i>
          </div>
          <div>
            <h3
              style="font-family: 'Outfit', sans-serif; font-size: 1.18rem; font-weight: 700; color: #0B1B3D; margin: 0 0 8px 0;">
              Telephone Lines</h3>
            <p style="font-size: 0.94rem; color: #334155; margin: 0 0 8px 0; font-weight: 600;">+63 (2) 8527-0909 / (02)
              8527-4963</p>
            <p style="font-size: 0.86rem; color: #64748B; margin: 0;">Ext. 204 (Legislative Research), Ext. 208
              (Ordinance Records)</p>
          </div>
        </div>

        <!-- Card 3: Official Email -->
        <div
          style="background: #FFFFFF; border: 1px solid #E2E8F0; border-top: 4px solid #9333EA; border-radius: 16px; padding: 32px 28px; box-shadow: 0 4px 20px rgba(11,27,61,0.04); display: flex; gap: 20px; align-items: flex-start;">
          <div
            style="width: 52px; height: 52px; border-radius: 14px; background: #F3E8FF; color: #9333EA; display: flex; align-items: center; justify-content: center; font-size: 1.6rem; flex-shrink: 0;">
            <i class="bi bi-envelope-at-fill"></i>
          </div>
          <div>
            <h3
              style="font-family: 'Outfit', sans-serif; font-size: 1.18rem; font-weight: 700; color: #0B1B3D; margin: 0 0 8px 0;">
              Official Email Addresses</h3>
            <p style="font-size: 0.94rem; color: #334155; margin: 0 0 8px 0; font-weight: 600;">
              legislative@manila.gov.ph</p>
            <p style="font-size: 0.86rem; color: #64748B; margin: 0;">ordinances@manila.gov.ph (Official Archives & Copy
              Requests)</p>
          </div>
        </div>

        <!-- Card 4: Office Hours -->
        <div
          style="background: #FFFFFF; border: 1px solid #E2E8F0; border-top: 4px solid #EA580C; border-radius: 16px; padding: 32px 28px; box-shadow: 0 4px 20px rgba(11,27,61,0.04); display: flex; gap: 20px; align-items: flex-start;">
          <div
            style="width: 52px; height: 52px; border-radius: 14px; background: #FFF7ED; color: #EA580C; display: flex; align-items: center; justify-content: center; font-size: 1.6rem; flex-shrink: 0;">
            <i class="bi bi-clock-fill"></i>
          </div>
          <div>
            <h3
              style="font-family: 'Outfit', sans-serif; font-size: 1.18rem; font-weight: 700; color: #0B1B3D; margin: 0 0 8px 0;">
              Public Office Hours</h3>
            <p style="font-size: 0.94rem; color: #334155; margin: 0 0 8px 0; font-weight: 600;">Monday – Friday: 8:00 AM
              – 5:00 PM (PST)</p>
            <p style="font-size: 0.86rem; color: #64748B; margin: 0;">Closed on weekends and official national/city
              holidays</p>
          </div>
        </div>

        <!-- Card 5: City Council Secretariat -->
        <div
          style="background: #FFFFFF; border: 1px solid #E2E8F0; border-top: 4px solid #4F46E5; border-radius: 16px; padding: 32px 28px; box-shadow: 0 4px 20px rgba(11,27,61,0.04); display: flex; gap: 20px; align-items: flex-start;">
          <div
            style="width: 52px; height: 52px; border-radius: 14px; background: #EEF2FF; color: #4F46E5; display: flex; align-items: center; justify-content: center; font-size: 1.6rem; flex-shrink: 0;">
            <i class="bi bi-building-fill"></i>
          </div>
          <div>
            <h3
              style="font-family: 'Outfit', sans-serif; font-size: 1.18rem; font-weight: 700; color: #0B1B3D; margin: 0 0 8px 0;">
              City Council Secretariat</h3>
            <p style="font-size: 0.94rem; color: #334155; margin: 0 0 8px 0; font-weight: 600;">Sangguniang Panlungsod
              Offices</p>
            <p style="font-size: 0.86rem; color: #64748B; margin: 0;">For public session calendars, ordinance filings,
              and committee hearings</p>
          </div>
        </div>

        <!-- Card 6: Public Help Desk -->
        <div
          style="background: #FFFFFF; border: 1px solid #E2E8F0; border-top: 4px solid #0D9488; border-radius: 16px; padding: 32px 28px; box-shadow: 0 4px 20px rgba(11,27,61,0.04); display: flex; gap: 20px; align-items: flex-start;">
          <div
            style="width: 52px; height: 52px; border-radius: 14px; background: #F0FDFA; color: #0D9488; display: flex; align-items: center; justify-content: center; font-size: 1.6rem; flex-shrink: 0;">
            <i class="bi bi-info-circle-fill"></i>
          </div>
          <div>
            <h3
              style="font-family: 'Outfit', sans-serif; font-size: 1.18rem; font-weight: 700; color: #0B1B3D; margin: 0 0 8px 0;">
              Legislative Public Help Desk</h3>
            <p style="font-size: 0.94rem; color: #334155; margin: 0 0 8px 0; font-weight: 600;">Public Assistance &
              Research Desk</p>
            <p style="font-size: 0.86rem; color: #64748B; margin: 0;">Assistance with ordinance searches, certified
              copies, and document requests</p>
          </div>
        </div>

      </div>
    </div>

  </div>

  <!-- FOOTER SECTION -->
  <footer
    style="background: #0B1B3D; color: #FFFFFF; padding: 20px 40px 12px 40px; border-top: 2px solid #D4AF37; width: 100%; box-sizing: border-box;">
    <div style="width: 100%; margin: 0 auto;">
      <!-- Grid 4 Columns -->
      <div
        style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 24px; margin-bottom: 14px;">

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
            <li><a href="welcome.php?login=1" style="color: rgba(255,255,255,0.85); text-decoration: none;">Sign In</a>
            </li>
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
        style="border-top: 1px solid rgba(255,255,255,0.12); padding-top: 10px; display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; font-size: 0.78rem; color: rgba(255,255,255,0.65); gap: 10px;">
        <div>Copyright &copy; <?php echo date('Y'); ?> Manila City Hall. All Rights Reserved.</div>
        <div>Designed for City Council Legislative Research Offices</div>
      </div>
    </div>
  </footer>

  <script src="../assets/js/login.js?v=2.0"></script>
</body>

</html>