<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Account Access Notice – Manila City Hall Legislative System</title>

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
          <i class="bi bi-shield-lock-fill me-1"></i> Admin-Controlled Account Access
        </div>
        <h1 class="auth-hero-title">Official Municipal Account Provisioning</h1>
        <p class="auth-hero-desc">
          To maintain security and legislative integrity, self-registration is disabled. Account credentials for Manila City Hall councilors and staff are issued by IT Administrators.
        </p>

        <div class="auth-feature-list">
          <div class="auth-feature-item">
            <i class="bi bi-shield-check text-warning"></i> Role-Based Access Control
          </div>
          <div class="auth-feature-item">
            <i class="bi bi-shield-check text-warning"></i> IT Administrator Provisioned Credentials
          </div>
          <div class="auth-feature-item">
            <i class="bi bi-shield-check text-warning"></i> Secure Municipal System Logging
          </div>
        </div>
      </div>

      <div class="split-auth-left-footer">
        Copyright &copy; <?php echo date('Y'); ?> Manila City Hall. All Rights Reserved.
      </div>
    </div>

    <!-- RIGHT SIDE: OFFICIAL NOTICE CARD -->
    <div class="split-auth-right">
      <div class="auth-form-card" style="text-align: center; max-width: 440px;">
        <header class="brand-header" style="margin-bottom: 20px;">
          <div class="brand-logo-wrapper" style="width: 70px; height: 70px; margin: 0 auto 16px auto; background: #FEF3C7; border: 2px solid #F59E0B; color: #D97706; font-size: 2rem;">
            <i class="bi bi-shield-lock"></i>
          </div>
          <h2 class="brand-title" style="font-size: 1.5rem; color: #0B1B3D; margin-bottom: 8px;">Restricted Registration</h2>
          <p class="brand-subtitle" style="font-size: 0.9rem; color: #64748B; line-height: 1.5;">
            Public self-registration is disabled for the Manila City Hall Legislative Information System.
          </p>
        </header>

        <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 12px; padding: 20px; text-align: left; margin-bottom: 24px;">
          <h4 style="font-size: 0.9rem; font-weight: 700; color: #0B1B3D; margin-bottom: 8px;"><i class="bi bi-info-circle-fill text-primary me-1"></i> How to Get Account Access</h4>
          <p style="font-size: 0.84rem; color: #475569; line-height: 1.5; margin: 0;">
            If you are a City Councilor, Legislative Staff member, or Policy Analyst needing portal access, please contact the <strong>Manila City Hall IT Management Office</strong> to request an account.
          </p>
        </div>

        <a href="login.php" class="btn-primary" style="display: inline-block; text-decoration: none; width: 100%; padding: 14px; text-align: center; font-weight: 700;">
          <i class="bi bi-box-arrow-in-right me-1"></i> Return to Sign In Page
        </a>

        <div class="auth-footer" style="margin-top: 20px;">
          <a href="../frontend/welcome.php" class="auth-link" style="font-size: 0.84rem;">
            <i class="bi bi-arrow-left"></i> Back to Main Portal
          </a>
        </div>
      </div>
    </div>
  </div>

</body>

</html>