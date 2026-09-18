<?php
// staff/includes/header.php — Shared Staff include (sidebar + topbar)
// Scoped for Staff permissions: No User Directory, No Audit Logs, No system configuration.
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $page_title ?? 'Staff Portal' ?> — Manila City Hall LIS</title>
  <script>
    if (localStorage.getItem('staff_sidebar_collapsed') === 'true') {
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
        sessionStorage.setItem('staff_active_section', sectionId);
        var url = new URL(window.location.href);
        url.searchParams.set('section', sectionId);
        window.history.replaceState({}, '', url);
      } catch (e) { }

      try {
        if (sectionId === 'dataCollectionSection' && typeof window.renderResearchCategoryChart === 'function') setTimeout(window.renderResearchCategoryChart, 50);
        if (sectionId === 'reportGenerationSection' && typeof window.renderRecentGeneratedReportsTable === 'function') setTimeout(window.renderRecentGeneratedReportsTable, 50);
      } catch (e) { }
    }
    window.showSection = showSection;
  </script>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/Manila City Hall.css?v=<?= time() ?>">
  <link rel="stylesheet" href="../assets/css/staff.css?v=<?= time() ?>">
  <style>
    body:not(.sidebar-collapsed) .sidebar {
      flex: 0 0 310px !important;
      width: 310px !important;
    }
    body:not(.sidebar-collapsed) .main-panel {
      width: calc(100% - 310px) !important;
      max-width: calc(100% - 310px) !important;
      margin-left: 310px !important;
    }
    .sidebar-nav .nav-link {
      display: flex !important;
      align-items: center !important;
      white-space: nowrap !important;
      font-size: 0.88rem !important;
      padding: 10px 14px !important;
    }
    .sidebar-nav .nav-link i {
      min-width: 1.2rem !important;
      margin-right: 0.6rem !important;
      flex-shrink: 0 !important;
    }
    .sidebar-nav .nav-link .nav-text {
      white-space: nowrap !important;
      font-size: 0.88rem !important;
      letter-spacing: normal !important;
    }
    /* ── MOBILE OVERRIDE: beat the !important rules above on small screens ── */
    @media (max-width: 991px) {
      body:not(.sidebar-collapsed) .sidebar,
      body.sidebar-collapsed .sidebar,
      .sidebar {
        position: fixed !important;
        left: -320px !important;
        top: 0 !important;
        width: 270px !important;
        flex: 0 0 270px !important;
        height: 100vh !important;
        z-index: 1040 !important;
        transition: left 0.3s cubic-bezier(0.4,0,0.2,1) !important;
        padding: 18px 12px !important;
      }
      .sidebar.mobile-open {
        left: 0 !important;
      }
      body:not(.sidebar-collapsed) .main-panel,
      body.sidebar-collapsed .main-panel,
      .main-panel {
        margin-left: 0 !important;
        width: 100% !important;
        max-width: 100% !important;
        padding: 0 0 40px !important;
      }
      .topbar {
        margin: 6px 6px 0 6px !important;
        padding: 8px 10px !important;
        border-radius: 10px !important;
        flex-wrap: nowrap !important;
      }
      .topbar .text-uppercase.small { display: none !important; }
      .topbar .fs-5.fw-bold { font-size: 0.82rem !important; line-height: 1.2 !important; }
      .header-divider { margin: 0 6px !important; }
      .header-admin-name, .header-admin-pipe { display: none !important; }
      .header-admin-role { font-size: 0.8rem !important; }
      .dark-mode-switch { width: 38px !important; height: 22px !important; }
      .header-avatar-wrap { width: 34px !important; height: 34px !important; min-width: 34px !important; min-height: 34px !important; max-width: 34px !important; max-height: 34px !important; }
    }
    @media (max-width: 480px) {
      .topbar .fs-5.fw-bold { display: none !important; }
      .topbar { padding: 6px 8px !important; }
    }
  </style>
</head>

<body>
  <!-- Security Verification for Staff -->
  <script>
    (function () {
      const userStr = localStorage.getItem('current_user');
      let currentUser = {};
      try {
        currentUser = JSON.parse(userStr || '{}');
      } catch (e) { }

      const role = (currentUser.role || '').toLowerCase();
      if (role === 'councilor' || role === 'user') {
        localStorage.removeItem('staff_logged_in');
        window.location.href = '../users/user_dashboard.php';
        return;
      }

      const isStaffLoggedIn = localStorage.getItem('staff_logged_in') === 'true';
      const isAdminLoggedIn = localStorage.getItem('admin_logged_in') === 'true';
      if (!isStaffLoggedIn && !isAdminLoggedIn && !userStr) {
        window.location.href = '../frontend/welcome.php';
      }
    })();
  </script>

  <!-- Mobile sidebar overlay backdrop -->
  <div class="sidebar-overlay" id="sidebarOverlay"></div>

  <div class="app-shell">
    <!-- SIDEBAR -->
    <aside class="sidebar d-flex flex-column p-3" id="mainSidebar">
      <div class="brand mb-4 d-flex flex-column gap-2">
        <div class="d-flex align-items-center gap-3 brand-info">
          <img src="../assets/images/manilacityhall.svg" alt="Manila City Hall Logo"
            style="width:48px; height:48px; object-fit:contain;" class="brand-logo">
          <div class="brand-text">
            <h1 class="fs-5 fw-bold mb-0 text-white" style="letter-spacing: -0.2px;">Lungsod ng <span
                style="color: #F59E0B;">Maynila</span></h1>
            <div class="text-white-50 small" style="font-size:0.75rem; letter-spacing: 0.3px;">Legislative Staff Portal
            </div>
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
          ['target' => 'staffDashboardSection', 'icon' => 'bi-speedometer2', 'label' => 'Dashboard'],
        ];
        foreach ($main_nav as $item):
          $is_active = (($active_section ?? 'staffDashboardSection') === $item['target']) ? 'active' : '';
          ?>
          <a class="nav-link py-2 px-3 rounded-3 <?= $is_active ?>" href="javascript:void(0);"
            data-target="<?= $item['target'] ?>" onclick="showSection('<?= $item['target'] ?>'); return false;"
            title="<?= htmlspecialchars($item['label']) ?>">
            <i class="<?= $item['icon'] ?> me-2"></i><span class="nav-text"><?= $item['label'] ?></span>
          </a>
        <?php endforeach; ?>

        <div class="sidebar-section-label mt-3">LEGISLATIVE</div>
        <?php
        $leg_nav = [
          ['target' => 'policyResearchSection', 'icon' => 'bi-journal-bookmark-fill', 'label' => 'Policy Research'],
          ['target' => 'dataCollectionSection', 'icon' => 'bi-database-fill-gear', 'label' => 'Data Collection'],
          ['target' => 'impactAssessmentSection', 'icon' => 'bi-bar-chart-line', 'label' => 'Evaluation'],
          ['target' => 'comparativeAnalysisSection', 'icon' => 'bi-layout-sidebar-inset-reverse', 'label' => 'Benchmarks & Comparison'],
        ];
        foreach ($leg_nav as $item):
          $is_active = (($active_section ?? '') === $item['target']) ? 'active' : '';
          ?>
          <a class="nav-link py-2 px-3 rounded-3 <?= $is_active ?>" href="javascript:void(0);"
            data-target="<?= $item['target'] ?>" onclick="showSection('<?= $item['target'] ?>'); return false;"
            title="<?= htmlspecialchars($item['label']) ?>">
            <i class="<?= $item['icon'] ?> me-2"></i><span class="nav-text"><?= $item['label'] ?></span>
          </a>
        <?php endforeach; ?>

        <div class="sidebar-section-label mt-3">REPORTING</div>
        <?php
        $report_nav = [
          ['target' => 'reportGenerationSection', 'icon' => 'bi-journal-text', 'label' => 'Reports'],
        ];
        foreach ($report_nav as $item):
          $is_active = (($active_section ?? '') === $item['target']) ? 'active' : '';
          ?>
          <a class="nav-link py-2 px-3 rounded-3 <?= $is_active ?>" href="javascript:void(0);"
            data-target="<?= $item['target'] ?>" onclick="showSection('<?= $item['target'] ?>'); return false;"
            title="<?= htmlspecialchars($item['label']) ?>">
            <i class="<?= $item['icon'] ?> me-2"></i><span class="nav-text"><?= $item['label'] ?></span>
          </a>
        <?php endforeach; ?>

        <!-- Note: ADMINISTRATION Section omitted for Staff role -->
      </nav>
    </aside>

    <!-- MAIN CONTENT -->
    <div class="main-panel flex-grow-1">
      <!-- TOPBAR -->
      <header
        class="topbar d-flex align-items-center justify-content-between px-4 py-3 mb-4 shadow-sm bg-white rounded-4 border mx-4 mt-3">
        <div class="d-flex align-items-center gap-3">
          <!-- Mobile hamburger (only visible on ≤991px) -->
          <button class="mobile-menu-btn me-1" id="mobileMenuBtn" type="button" aria-label="Open navigation menu" title="Open Menu">
            <i class="bi bi-list"></i>
          </button>
          <img src="../assets/images/manilacityhall.svg" alt="Lungsod ng Maynila Logo"
            style="width: 44px; height: 44px; object-fit: contain;">
          <div>
            <div class="text-uppercase text-muted small fw-semibold" style="letter-spacing:0.5px;font-size:0.72rem;">
              MANILA CITY HALL</div>
            <div class="fs-5 fw-bold text-dark">Legislative Staff Portal</div>
          </div>
        </div>
        <div class="d-flex align-items-center">
          <!-- Notification Dropdown -->
          <?php
          $staff_notifs = [];
          if (!empty($conn)) {
            $snq = @mysqli_query($conn, "SELECT id, title, category, publication_date, created_at FROM policy_records WHERE (status IS NULL OR status != 'Archived') ORDER BY id DESC LIMIT 5");
            if ($snq) {
              while ($r = mysqli_fetch_assoc($snq)) {
                $staff_notifs[] = $r;
              }
            }
          }
          $staff_notif_count = count($staff_notifs);
          $staff_latest_id = !empty($staff_notifs) ? (int) $staff_notifs[0]['id'] : 0;
          ?>
          <div class="dropdown">
            <button class="header-notif-btn" id="staffNotifButton" type="button" data-bs-toggle="dropdown"
              data-latest-id="<?= $staff_latest_id ?>" aria-expanded="false" title="Notifications">
              <i class="bi bi-bell fs-5 text-dark"></i>
              <span class="header-notif-badge" id="staffNotifBadge" style="display:none;"></span>
            </button>
            <div class="dropdown-menu dropdown-menu-end shadow-lg border-0 rounded-4 p-0 overflow-hidden mt-2"
              style="width:370px;" aria-labelledby="staffNotifButton">
              <div class="px-3 py-3 d-flex align-items-center justify-content-between text-white notif-header"
                style="background: linear-gradient(120deg, #0B2E59, #1a4a8a);">
                <div>
                  <strong class="fs-6 d-block">Notifications</strong>
                  <small class="opacity-75">You have <span id="staffNotifUnread">0</span> new
                    updates</small>
                </div>
                <span id="staffNotifHeaderBadge"
                  class="badge rounded-pill bg-warning text-dark"><?= $staff_notif_count ?> Updates</span>
              </div>
              <div class="p-2" style="max-height: 290px; overflow-y: auto;">
                <ul class="list-group list-group-flush" id="staffNotifList">
                  <?php if (!empty($staff_notifs)): ?>
                    <?php foreach ($staff_notifs as $upd): ?>
                      <?php
                      $upd_id = (int) $upd['id'];
                      $upd_title = htmlspecialchars($upd['title']);
                      $upd_cat = htmlspecialchars($upd['category'] ?? 'Policy');
                      $upd_date = !empty($upd['publication_date']) ? date('M d, Y', strtotime($upd['publication_date'])) : (!empty($upd['created_at']) ? date('M d, Y', strtotime($upd['created_at'])) : 'Recent');
                      ?>
                      <li
                        class="notif-item list-group-item p-2 mb-1 border rounded-3 d-flex justify-content-between align-items-start"
                        data-notif-id="<?= $upd_id ?>" style="cursor: pointer;"
                        onclick="handleStaffNotifItemClick('policyResearchSection', <?= $upd_id ?>);">
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
                      style="cursor: pointer;" onclick="handleStaffNotifItemClick('policyResearchSection');">
                      <div class="d-flex gap-2">
                        <span class="notif-dot unread mt-1.5"
                          style="background:#EF4444; width:8px; height:8px; border-radius:50%; flex-shrink:0;"></span>
                        <div>
                          <div class="fw-semibold small text-dark" style="font-size:0.86rem;">New Policy Uploaded</div>
                          <small class="text-muted d-block mt-0.5" style="font-size: 0.74rem;">Policy Records &bull; 10m
                            ago</small>
                        </div>
                      </div>
                      <span class="badge bg-warning text-dark ms-1" style="font-size:0.65rem;">Policy</span>
                    </li>
                  <?php endif; ?>
                </ul>
              </div>
              <div class="d-flex align-items-center justify-content-between px-3 py-2 border-top notif-footer bg-white">
                <a href="#" class="text-primary small text-decoration-none fw-semibold"
                  onclick="markAllStaffNotifsRead(event)">Mark all as read</a>
                <a href="staff_dashboard.php?section=staffPoliciesSection"
                  class="text-muted small text-decoration-none">View all &rarr;</a>
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

          <!-- Staff Profile Dropdown -->
          <div class="dropdown">
            <button class="header-dropdown-btn" type="button" id="staffProfileDropdown" data-bs-toggle="dropdown"
              aria-expanded="false">
              <div class="header-avatar-wrap">
                <img id="topbarStaffAvatarImg" src="" alt="Staff Profile" class="header-avatar-img d-none" />
                <div id="topbarStaffAvatarFallback" class="header-avatar-fallback">
                  <i class="bi bi-person-fill"></i>
                </div>
              </div>
              <span class="header-admin-text">
                <span class="header-admin-role">Staff</span>
                <span class="header-admin-pipe">|</span>
                <span id="topbarStaffName" class="header-admin-name">Legislative Staff Officer</span>
              </span>
              <i class="bi bi-chevron-down ms-1"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 rounded-4 p-2 mt-2"
              aria-labelledby="staffProfileDropdown">
              <li>
                <a class="dropdown-item rounded-2 py-2" href="#"
                  onclick="showSection('staffProfileSection');return false;">
                  <i class="bi bi-person-circle me-2 text-primary"></i>Profile
                </a>
              </li>
              <li>
                <hr class="dropdown-divider my-1">
              </li>
              <li>
                <a class="dropdown-item rounded-2 py-2 text-danger" href="../auth/logout.php" id="topbarStaffLogoutBtn"
                  onclick="if(window.handleStaffLogout){window.handleStaffLogout(event);}">
                  <i class="bi bi-box-arrow-right me-2"></i>Logout
                </a>
              </li>
            </ul>
          </div>
        </div>
      </header>

      <main class="content-area px-4 pb-5">

  <script>
    // --- Mobile sidebar toggle ---
    (function () {
      var sidebar = document.getElementById('mainSidebar');
      var overlay = document.getElementById('sidebarOverlay');
      var menuBtn = document.getElementById('mobileMenuBtn');

      function openSidebar() {
        if (sidebar) sidebar.classList.add('mobile-open');
        if (overlay) overlay.classList.add('active');
        if (menuBtn) menuBtn.querySelector('i').className = 'bi bi-x-lg';
        document.body.style.overflow = 'hidden';
      }

      function closeSidebar() {
        if (sidebar) sidebar.classList.remove('mobile-open');
        if (overlay) overlay.classList.remove('active');
        if (menuBtn) menuBtn.querySelector('i').className = 'bi bi-list';
        document.body.style.overflow = '';
      }

      if (menuBtn) menuBtn.addEventListener('click', function () {
        if (sidebar && sidebar.classList.contains('mobile-open')) {
          closeSidebar();
        } else {
          openSidebar();
        }
      });

      if (overlay) overlay.addEventListener('click', closeSidebar);

      // Close sidebar when a nav link is tapped on mobile
      if (sidebar) {
        sidebar.querySelectorAll('.nav-link').forEach(function (link) {
          link.addEventListener('click', function () {
            if (window.innerWidth <= 991) closeSidebar();
          });
        });
      }

      // Close on resize back to desktop
      window.addEventListener('resize', function () {
        if (window.innerWidth > 991) closeSidebar();
      });
    })();
  </script>