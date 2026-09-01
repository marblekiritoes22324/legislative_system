<?php
// Shared admin include — sidebar + topbar
// Each page must define $active_page before including this file.
// $active_page is used to highlight the current sidebar link.
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $page_title ?? 'Admin Panel' ?> — Manila City Hall LIS</title>
  <script>
    if (localStorage.getItem('admin_sidebar_collapsed') === 'true') {
      document.documentElement.classList.add('sidebar-collapsed');
    }
  </script>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/Manila City Hall.css">
  <link rel="stylesheet" href="../assets/css/Admin.css">
</head>

<body>
  <!-- Security Verification -->
  <script>
    if (localStorage.getItem('admin_logged_in') !== 'true') {
      window.location.href = '../frontend/welcome.php';
    }
  </script>

  <div class="app-shell">
    <!-- SIDEBAR -->
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

      <nav class="nav flex-column sidebar-nav mb-4 gap-1">
        <div class="sidebar-section-label">MAIN</div>
        <?php
        $main_nav = [
          ['href' => 'admin_dashboard.php?section=adminDashboardSection', 'icon' => 'bi-speedometer2', 'label' => 'Dashboard'],
        ];
        foreach ($main_nav as $item):
          $is_active = ($active_page === $item['label']) ? 'active' : '';
          ?>
          <a class="nav-link py-2 px-3 rounded-3 <?= $is_active ?>" href="<?= $item['href'] ?>"
            title="<?= htmlspecialchars($item['label']) ?>">
            <i class="<?= $item['icon'] ?> me-2"></i><span class="nav-text"><?= $item['label'] ?></span>
          </a>
        <?php endforeach; ?>

        <div class="sidebar-section-label mt-3">LEGISLATIVE</div>
        <?php
        $leg_nav = [
          ['href' => 'admin_dashboard.php?section=policyResearchSection', 'icon' => 'bi-file-earmark-text', 'label' => 'Policy Research'],
          ['href' => 'admin_dashboard.php?section=dataCollectionSection', 'icon' => 'bi-database-fill-gear', 'label' => 'Data Collection'],
          ['href' => 'admin_dashboard.php?section=impactAssessmentSection', 'icon' => 'bi-bar-chart-line', 'label' => 'Evaluation'],
          ['href' => 'admin_dashboard.php?section=comparativeAnalysisSection', 'icon' => 'bi-layout-sidebar-inset-reverse', 'label' => 'Comparison'],
        ];
        foreach ($leg_nav as $item):
          $is_active = ($active_page === $item['label']) ? 'active' : '';
          ?>
          <a class="nav-link py-2 px-3 rounded-3 <?= $is_active ?>" href="<?= $item['href'] ?>"
            title="<?= htmlspecialchars($item['label']) ?>">
            <i class="<?= $item['icon'] ?> me-2"></i><span class="nav-text"><?= $item['label'] ?></span>
          </a>
        <?php endforeach; ?>

        <div class="sidebar-section-label mt-3">REPORTING</div>
        <?php
        $report_nav = [
          ['href' => 'admin_dashboard.php?section=reportGenerationSection', 'icon' => 'bi-journal-text', 'label' => 'Reports'],
        ];
        foreach ($report_nav as $item):
          $is_active = ($active_page === $item['label']) ? 'active' : '';
          ?>
          <a class="nav-link py-2 px-3 rounded-3 <?= $is_active ?>" href="<?= $item['href'] ?>"
            title="<?= htmlspecialchars($item['label']) ?>">
            <i class="<?= $item['icon'] ?> me-2"></i><span class="nav-text"><?= $item['label'] ?></span>
          </a>
        <?php endforeach; ?>

        <div class="sidebar-section-label mt-3">ADMINISTRATION</div>
        <?php
        $admin_nav_items = [
          ['href' => 'admin_dashboard.php?section=systemLogsSection', 'icon' => 'bi-terminal-fill', 'label' => 'Audit Logs'],
          ['href' => 'admin_dashboard.php?section=activeUsersSection', 'icon' => 'bi-people', 'label' => 'User Directory'],
          ['href' => 'admin_dashboard.php?section=databaseManagementSection', 'icon' => 'bi-database-check', 'label' => 'Database Management'],
        ];
        foreach ($admin_nav_items as $item):
          $is_active = ($active_page === $item['label']) ? 'active' : '';
          ?>
          <a class="nav-link py-2 px-3 rounded-3 <?= $is_active ?>" href="<?= $item['href'] ?>"
            title="<?= htmlspecialchars($item['label']) ?>">
            <i class="<?= $item['icon'] ?> me-2"></i><span class="nav-text"><?= $item['label'] ?></span>
          </a>
        <?php endforeach; ?>
      </nav>
    </aside>

    <!-- MAIN CONTENT -->
    <div class="main-panel flex-grow-1">
      <!-- TOPBAR -->
      <header
        class="topbar d-flex align-items-center justify-content-between px-4 py-3 mb-4 shadow-sm bg-white rounded-4 border mx-4 mt-3">
        <div class="d-flex align-items-center gap-3">
          <div
            class="logo-circle d-flex align-items-center justify-content-center rounded-circle fw-bold text-white shadow-sm"
            style="background:#0B2E59;width:42px;height:42px;">M</div>
          <div>
            <div class="text-uppercase text-muted small fw-semibold" style="letter-spacing:0.5px;font-size:0.72rem;">
              MANILA CITY HALL</div>
            <div class="fs-5 fw-bold text-dark">Legislative Administration System</div>
          </div>
        </div>
        <div class="d-flex align-items-center">
          <!-- Notification Dropdown -->
          <div class="dropdown">
            <button class="header-notif-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false"
              title="Notifications">
              <i class="bi bi-bell fs-5 text-dark"></i>
              <span class="header-notif-badge">3</span>
            </button>
            <div class="dropdown-menu dropdown-menu-end shadow-lg border-0 rounded-4 p-3 mt-2" style="width:300px;">
              <strong class="d-block mb-2 text-dark">Notifications</strong>
              <small class="text-muted d-block">System logs and audit backups up-to-date.</small>
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
                <a class="dropdown-item rounded-2 py-2" href="admin_dashboard.php?section=adminProfileSection">
                  <i class="bi bi-person-circle me-2 text-primary"></i>Profile
                </a>
              </li>
              <li>
                <hr class="dropdown-divider my-1">
              </li>
              <li><a class="dropdown-item rounded-2 py-2 text-danger" href="../auth/logout.php"
                  id="topbarAdminLogoutBtn" onclick="if(window.handleAdminLogout){window.handleAdminLogout(event);}"><i
                    class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
            </ul>
          </div>
        </div>
      </header>

      <main class="content-area px-4 pb-5">