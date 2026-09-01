/**
 * Staff Portal Core Script - Manila City Hall Legislative Information System
 */
(function () {
  'use strict';

  // 1. Sidebar Toggle & Persistence
  function initSidebar() {
    const toggleBtn = document.getElementById('sidebarToggleBtn');
    if (toggleBtn) {
      toggleBtn.addEventListener('click', function () {
        const isCollapsed = document.documentElement.classList.toggle('sidebar-collapsed');
        localStorage.setItem('staff_sidebar_collapsed', isCollapsed ? 'true' : 'false');
      });
    }
  }

  // 2. Dark Mode Toggle & Persistence
  function initDarkMode() {
    const darkModeCheckbox = document.getElementById('headerDarkModeCheckbox');
    if (darkModeCheckbox) {
      if (localStorage.getItem('staff_dark_mode') === 'true' || document.body.classList.contains('dark-mode')) {
        darkModeCheckbox.checked = true;
        document.body.classList.add('dark-mode');
      }
      darkModeCheckbox.addEventListener('change', function () {
        if (this.checked) {
          document.body.classList.add('dark-mode');
          localStorage.setItem('staff_dark_mode', 'true');
        } else {
          document.body.classList.remove('dark-mode');
          localStorage.setItem('staff_dark_mode', 'false');
        }
      });
    }
  }

  // 3. Section Switching / Tab Navigation
  window.showSection = function (sectionId) {
    if (!sectionId) return;
    const sections = document.querySelectorAll('.content-section');
    sections.forEach(sec => sec.classList.add('d-none'));

    const target = document.getElementById(sectionId);
    if (target) {
      target.classList.remove('d-none');
      window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    // Update active nav link
    const navLinks = document.querySelectorAll('.sidebar-nav .nav-link, [data-target]');
    navLinks.forEach(link => {
      link.classList.remove('active');
      const href = link.getAttribute('href') || '';
      const dataTarget = link.dataset.target || '';
      if ((dataTarget && dataTarget === sectionId) || (href && href.includes(sectionId))) {
        link.classList.add('active');
      }
    });

    try {
      sessionStorage.setItem('staff_active_section', sectionId);
      const url = new URL(window.location.href);
      url.searchParams.set('section', sectionId);
      window.history.replaceState({}, '', url);
    } catch (e) { }

    if (sectionId === 'dataCollectionSection') {
      setTimeout(function () {
        if (typeof window.renderResearchCategoryChart === 'function') window.renderResearchCategoryChart();
      }, 50);
    }
    if (sectionId === 'reportGenerationSection') {
      setTimeout(function () {
        if (typeof window.renderRecentGeneratedReportsTable === 'function') window.renderRecentGeneratedReportsTable();
      }, 50);
    }

    // Update URL hash/query without page reload
    if (history.replaceState) {
      const newUrl = window.location.protocol + "//" + window.location.host + window.location.pathname + '?section=' + encodeURIComponent(sectionId);
      window.history.replaceState({}, '', newUrl);
    }
  };

  // 4. Activity Logs Loader for Staff Dashboard
  function loadRecentActivities() {
    const tbody = document.getElementById('dashboardActivityTable');
    if (!tbody) return;

    fetch('../backend/get_recent_activities.php?limit=10')
      .then(response => response.json())
      .then(data => {
        if (data && data.success && Array.isArray(data.activities)) {
          if (data.activities.length === 0) {
            tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted py-4">No recent activities found.</td></tr>';
            return;
          }

          let html = '';
          data.activities.forEach(act => {
            const dateTime = act.date_time || '—';
            const activity = act.activity || 'Activity performed';
            const module = act.module || 'System';
            const status = act.status || 'Completed';
            let user = act.user || 'Staff';

            html += `<tr>
              <td><span class="activity-datetime">${escapeHtml(dateTime)}</span></td>
              <td><span class="activity-title">${escapeHtml(activity)}</span></td>
              <td>${getModuleBadgeHtml(module)}</td>
              <td>${getStatusBadgeHtml(status)}</td>
              <td><span class="activity-user">${escapeHtml(user)}</span></td>
            </tr>`;
          });
          tbody.innerHTML = html;
        }
      })
      .catch(err => console.error('Error fetching recent activities:', err));
  }

  function getModuleBadgeHtml(module) {
    const mod = (module || '').trim();
    const lower = mod.toLowerCase();

    if (lower.includes('policy') || lower.includes('ordinance')) {
      return `<span class="module-pill module-pill-policy"><i class="bi bi-file-earmark-text"></i> ${escapeHtml(mod || 'Policy Research')}</span>`;
    }
    if (lower.includes('research') || lower.includes('data') || lower.includes('dataset') || lower.includes('collection')) {
      return `<span class="module-pill module-pill-research"><i class="bi bi-database-fill-gear"></i> ${escapeHtml(mod || 'Data Collection')}</span>`;
    }
    if (lower.includes('evaluat') || lower.includes('impact') || lower.includes('assessment')) {
      return `<span class="module-pill module-pill-evaluations"><i class="bi bi-bar-chart-line"></i> ${escapeHtml(mod || 'Evaluations')}</span>`;
    }
    if (lower.includes('report') || lower.includes('export') || lower.includes('analytic')) {
      return `<span class="module-pill module-pill-reports"><i class="bi bi-journal-text"></i> ${escapeHtml(mod || 'Reports')}</span>`;
    }
    if (lower.includes('system') || lower.includes('auth') || lower.includes('login') || lower.includes('user')) {
      return `<span class="module-pill module-pill-system"><i class="bi bi-gear-wide-connected"></i> ${escapeHtml(mod || 'System')}</span>`;
    }

    return `<span class="module-pill module-pill-policy"><i class="bi bi-file-earmark-text"></i> ${escapeHtml(mod || 'General')}</span>`;
  }

  function getStatusBadgeHtml(status) {
    const st = (status || 'Completed').trim();
    const lower = st.toLowerCase();
    let dotClass = '';

    if (lower === 'pending' || lower === 'draft' || lower === 'under review') {
      dotClass = 'warning';
    } else if (lower === 'archived' || lower === 'failed' || lower === 'rejected' || lower === 'deactivated') {
      dotClass = 'danger';
    }

    return `<span class="status-pill"><span class="status-dot-indicator ${dotClass}"></span> ${escapeHtml(st)}</span>`;
  }

  function escapeHtml(text) {
    if (!text) return '';
    return String(text)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;');
  }

  // 5. Staff Logout Handler
  let isStaffLoggingOut = false;
  function handleStaffLogout(e) {
    if (e && e.preventDefault) e.preventDefault();
    if (isStaffLoggingOut) return;
    isStaffLoggingOut = true;

    let userName = 'Staff Officer';
    try {
      const saved = JSON.parse(localStorage.getItem('staff_profile_data') || '{}');
      const curr = JSON.parse(localStorage.getItem('current_user') || '{}');
      if (saved.name) userName = saved.name;
      else if (curr.name && curr.name !== 'Staff' && curr.name !== 'staff') userName = curr.name;
      else if (curr.username) userName = curr.username;
    } catch (err) {}

    const formData = new FormData();
    formData.append('action', 'log_audit');
    formData.append('user', userName);
    formData.append('module', 'System');
    formData.append('activity', 'User logout');
    formData.append('status', 'Completed');

    localStorage.removeItem('staff_logged_in');
    localStorage.removeItem('current_user');
    sessionStorage.clear();

    try {
      navigator.sendBeacon('../backend/log_activity.php', formData);
    } catch (err) {
      fetch('../backend/log_activity.php', { method: 'POST', body: formData, keepalive: true }).catch(() => {});
    }

    window.location.href = '../auth/logout.php?user=' + encodeURIComponent(userName);
  }

  // 6. Initialisation
  document.addEventListener('DOMContentLoaded', function () {
    initSidebar();
    initDarkMode();
    loadRecentActivities();

    const pendingLoginUser = sessionStorage.getItem('pending_login_audit');
    if (pendingLoginUser) {
      sessionStorage.removeItem('pending_login_audit');
      const logData = new FormData();
      logData.append('action', 'log_audit');
      logData.append('user', pendingLoginUser);
      logData.append('module', 'System');
      logData.append('activity', 'User login');
      logData.append('status', 'Completed');
      fetch('../backend/log_activity.php', { method: 'POST', body: logData, keepalive: true }).catch(() => {});
    }

    // Check URL params for active section, defaulting to staffDashboardSection
    const urlParams = new URLSearchParams(window.location.search);
    const section = urlParams.get('section') || sessionStorage.getItem('staff_active_section') || 'staffDashboardSection';
    window.showSection(section);
  });

  // 7. Notifications: mark all as read for Staff
  // 7. Notifications: live unread tracking for Staff
  function initStaffNotificationHandlers() {
    const staffNotifBtn = document.getElementById('staffNotifButton');
    const badge = document.getElementById('staffNotifBadge');
    const count = document.getElementById('staffNotifUnread');
    const headerBadge = document.getElementById('staffNotifHeaderBadge');
    const items = document.querySelectorAll('#staffNotifList .notif-item');

    if (!staffNotifBtn) return;

    const latestId = parseInt(staffNotifBtn.dataset.latestId || '0', 10);
    const lastSeenId = parseInt(localStorage.getItem('staff_last_seen_notif_id') || '0', 10);

    let unreadCount = 0;
    items.forEach((item) => {
      const itemId = parseInt(item.dataset.notifId || '0', 10);
      const dot = item.querySelector('.notif-dot');
      if (itemId > lastSeenId) {
        unreadCount++;
        if (dot) {
          dot.style.background = '#EF4444';
          dot.style.opacity = '1';
          dot.style.boxShadow = '0 0 6px rgba(239,68,68,0.6)';
        }
      } else {
        if (dot) {
          dot.style.background = '#94A3B8';
          dot.style.opacity = '0.35';
          dot.style.boxShadow = 'none';
        }
      }
    });

    if (unreadCount > 0) {
      if (badge) {
        badge.textContent = unreadCount;
        badge.style.display = 'flex';
        badge.style.opacity = '1';
        badge.style.transform = 'scale(1)';
      }
      if (count) count.textContent = unreadCount;
      if (headerBadge) {
        headerBadge.textContent = unreadCount + ' New';
        headerBadge.className = 'badge rounded-pill bg-warning text-dark';
      }
    } else {
      if (badge) badge.style.display = 'none';
      if (count) count.textContent = '0';
      if (headerBadge) {
        headerBadge.textContent = '0 New';
        headerBadge.className = 'badge rounded-pill bg-secondary text-white';
      }
    }

    staffNotifBtn.addEventListener('click', function () {
      markAllStaffNotifsRead();
    });
  }

  function markAllStaffNotifsRead(event) {
    if (event) event.preventDefault();
    const staffNotifBtn = document.getElementById('staffNotifButton');
    const latestId = staffNotifBtn ? parseInt(staffNotifBtn.dataset.latestId || '0', 10) : Date.now();
    localStorage.setItem('staff_last_seen_notif_id', latestId.toString());

    const badge = document.getElementById('staffNotifBadge');
    if (badge) {
      badge.style.transition = 'opacity 0.2s ease, transform 0.2s ease';
      badge.style.opacity = '0';
      badge.style.transform = 'scale(0.3)';
      setTimeout(() => { badge.style.display = 'none'; }, 200);
    }
    const headerBadge = document.getElementById('staffNotifHeaderBadge');
    if (headerBadge) {
      headerBadge.textContent = '0 New';
      headerBadge.className = 'badge rounded-pill bg-secondary text-white';
    }
    const count = document.getElementById('staffNotifUnread');
    if (count) count.textContent = '0';
    document.querySelectorAll('#staffNotifList .notif-dot').forEach((d) => {
      d.style.background = '#94A3B8';
      d.style.opacity = '0.35';
      d.style.boxShadow = 'none';
    });
  }

  function handleStaffNotifItemClick(sectionId, notifId) {
    markAllStaffNotifsRead();
    const staffNotifBtn = document.getElementById('staffNotifButton');
    if (staffNotifBtn && window.bootstrap && bootstrap.Dropdown) {
      const inst = bootstrap.Dropdown.getInstance(staffNotifBtn);
      if (inst) inst.hide();
    }
    if (typeof showSection === 'function' && sectionId) {
      showSection(sectionId);
    }
  }

  document.addEventListener('DOMContentLoaded', initStaffNotificationHandlers);
  if (document.readyState === 'complete' || document.readyState === 'interactive') {
    initStaffNotificationHandlers();
  }

  window.markAllStaffNotifsRead = markAllStaffNotifsRead;
  window.handleStaffNotifItemClick = handleStaffNotifItemClick;
  window.handleStaffLogout = handleStaffLogout;
  window.loadRecentActivities = loadRecentActivities;
  window.getModuleBadgeHtml = getModuleBadgeHtml;
  window.getStatusBadgeHtml = getStatusBadgeHtml;
})();
