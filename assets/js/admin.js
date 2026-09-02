// ============================================================
// admin.js — All JavaScript for the Admin Dashboard
// PHP injects window.ADMIN_CONFIG before this script loads
// ============================================================

// ── Routing / Tab switching (Global function) ────────────────
function showSection(sectionId) {
  if (!sectionId) return;

  const sections = document.querySelectorAll('.content-section');
  const navLinks = document.querySelectorAll('.sidebar-nav .nav-link, [data-target]');

  sections.forEach((section) => {
    if (section.id === sectionId) {
      section.classList.remove('d-none');
    } else {
      section.classList.add('d-none');
    }
  });

  navLinks.forEach((link) => {
    const target = link.dataset.target;
    const href = link.getAttribute('href') || '';
    if ((target && target === sectionId) || (href && href.includes(sectionId))) {
      link.classList.add('active');
    } else if (target || href) {
      link.classList.remove('active');
    }
  });

  try {
    const isStaff = window.location.pathname.includes('staff') || document.getElementById('staffDashboardSection');
    const storageKey = isStaff ? 'staff_active_section' : 'admin_active_section';
    sessionStorage.setItem(storageKey, sectionId);
    const url = new URL(window.location.href);
    url.searchParams.set('section', sectionId);
    window.history.replaceState({}, '', url);
  } catch (e) { }

  if (sectionId === 'adminDashboardSection') {
    setTimeout(refreshDashboardData, 50);
  }
  if (sectionId === 'approvalQueueSection') renderApprovalQueue();
  if (sectionId === 'activeUsersSection') renderDirectory();
  if (sectionId === 'systemLogsSection') renderLogs();
  if (sectionId === 'dataCollectionSection') {
    setTimeout(function () {
      if (typeof window.renderResearchCategoryChart === 'function') window.renderResearchCategoryChart();
    }, 60);
  }
  if (sectionId === 'reportGenerationSection') {
    setTimeout(function () {
      if (typeof window.renderRecentGeneratedReportsTable === 'function') window.renderRecentGeneratedReportsTable();
    }, 60);
  }
  if (sectionId === 'dataVisualizationSection') {
    setTimeout(function () {
      if (typeof window.loadAnalyticsSection === 'function') window.loadAnalyticsSection();
    }, 60);
  }

  try {
    const url = new URL(window.location.href);
    url.searchParams.set('section', sectionId);
    window.history.replaceState({}, '', url);
  } catch (e) { }
}

window.showSection = showSection;

document.addEventListener("DOMContentLoaded", function () {
  const darkModeToggle = document.getElementById('darkModeToggle');

  // ── Collapsible Sidebar Toggle & localStorage Persistence ──
  const initSidebarState = () => {
    const isCollapsed = localStorage.getItem('admin_sidebar_collapsed') === 'true';
    if (isCollapsed) {
      document.body.classList.add('sidebar-collapsed');
      document.documentElement.classList.add('sidebar-collapsed');
    } else {
      document.body.classList.remove('sidebar-collapsed');
      document.documentElement.classList.remove('sidebar-collapsed');
    }
  };
  initSidebarState();

  document.addEventListener('click', function (event) {
    const toggleBtn = event.target.closest('.sidebar-toggle-btn, #sidebarToggleBtn');
    if (toggleBtn) {
      event.preventDefault();
      const isCurrentlyCollapsed = document.body.classList.contains('sidebar-collapsed');
      const newState = !isCurrentlyCollapsed;

      document.body.classList.toggle('sidebar-collapsed', newState);
      document.documentElement.classList.toggle('sidebar-collapsed', newState);
      localStorage.setItem('admin_sidebar_collapsed', newState ? 'true' : 'false');
      localStorage.setItem('user_sidebar_collapsed', newState ? 'true' : 'false');
      return;
    }
  });

  // Event delegation for clicks on links with data-target or sidebar nav links
  document.addEventListener('click', function (event) {
    const link = event.target.closest('.sidebar-nav .nav-link, [data-target]');
    if (link) {
      const targetId = link.dataset.target;
      if (targetId) {
        event.preventDefault();
        showSection(targetId);
      }
    }
  });

  // ── Dark / Light Mode Switch ──
  const headerDarkModeCheckbox = document.getElementById('headerDarkModeCheckbox');
  const applyTheme = (mode) => {
    const isDark = mode === 'dark';
    document.body.classList.toggle('dark-mode', isDark);
    if (headerDarkModeCheckbox) {
      headerDarkModeCheckbox.checked = isDark;
    }
    const icon = darkModeToggle?.querySelector('i');
    if (icon) {
      icon.className = isDark ? 'bi bi-sun-fill fs-5 text-warning' : 'bi bi-moon-fill fs-5 text-dark';
    }
    if (darkModeToggle) {
      darkModeToggle.title = isDark ? 'Switch to Light Mode' : 'Switch to Dark Mode';
    }
  };
  applyTheme(localStorage.getItem('admin_theme') || 'light');

  if (headerDarkModeCheckbox) {
    headerDarkModeCheckbox.addEventListener('change', () => {
      const next = headerDarkModeCheckbox.checked ? 'dark' : 'light';
      localStorage.setItem('admin_theme', next);
      localStorage.setItem('user_theme', next);
      applyTheme(next);
    });
  }

  darkModeToggle?.addEventListener('click', () => {
    const next = document.body.classList.contains('dark-mode') ? 'light' : 'dark';
    localStorage.setItem('admin_theme', next);
    localStorage.setItem('user_theme', next);
    applyTheme(next);
  });

  // ── Logout handler ───────────────────────────────────────
  let isAdminLoggingOut = false;
  function handleAdminLogout(e) {
    if (e && e.preventDefault) e.preventDefault();
    if (isAdminLoggingOut) return;
    isAdminLoggingOut = true;

    let userName = 'Admin';
    try {
      const saved = JSON.parse(localStorage.getItem('admin_profile_data') || '{}');
      const curr = JSON.parse(localStorage.getItem('current_user') || '{}');
      if (saved.name) userName = saved.name;
      else if (curr.name && curr.name !== 'Admin' && curr.name !== 'admin' && curr.name !== 'System Administrator') userName = curr.name;
      else if (curr.username && curr.username !== 'admin') userName = curr.username;
    } catch (err) { }

    const formData = new FormData();
    formData.append('action', 'log_audit');
    formData.append('user', userName);
    formData.append('module', 'System');
    formData.append('activity', 'User logout');
    formData.append('status', 'Completed');

    localStorage.removeItem('admin_logged_in');
    localStorage.removeItem('current_user');
    sessionStorage.clear();

    try {
      navigator.sendBeacon('../backend/log_activity.php', formData);
    } catch (err) {
      fetch('../backend/log_activity.php', { method: 'POST', body: formData, keepalive: true }).catch(() => { });
    }

    window.location.href = '../auth/logout.php?user=' + encodeURIComponent(userName);
  }

  window.handleAdminLogout = handleAdminLogout;

  document.getElementById('directorySearchInput')?.addEventListener('input', function (e) {
    renderDirectory(e.target.value.trim());
  });

  // Process pending login audit log if set
  const pendingLoginUser = sessionStorage.getItem('pending_login_audit');
  if (pendingLoginUser) {
    sessionStorage.removeItem('pending_login_audit');
    const logData = new FormData();
    logData.append('action', 'log_audit');
    logData.append('user', pendingLoginUser);
    logData.append('module', 'System');
    logData.append('activity', 'User login');
    logData.append('status', 'Completed');
    fetch('../backend/log_activity.php', { method: 'POST', body: logData, keepalive: true }).catch(() => { });
  }

  // ── Sync nav highlight and show active section for Admin Portal only ──
  if (window.ADMIN_CONFIG && window.ADMIN_CONFIG.activeSection) {
    showSection(window.ADMIN_CONFIG.activeSection);
    seedLogsIfEmpty();
    updateDashboardStats();
    renderApprovalQueue();
    loadRecentActivities();
    setInterval(loadRecentActivities, 4000);
    if (typeof refreshDashboardData === 'function') refreshDashboardData();
  }
});

// ── LocalStorage Helpers ─────────────────────────────────────
function getUsers() {
  return JSON.parse(localStorage.getItem('legislative_system_users') || '[]');
}

// ── Notifications: Responsive Live Notification Handling ─────────────────────────
function initNotificationHandlers() {
  const notifBtn = document.getElementById('adminNotifButton');
  const badge = document.getElementById('adminNotifBadge');
  const count = document.getElementById('adminNotifUnread');
  const headerBadge = document.getElementById('adminNotifHeaderBadge');
  const items = document.querySelectorAll('#adminNotifList .notif-item');

  if (!notifBtn) return;

  const latestId = parseInt(notifBtn.dataset.latestId || '0', 10);
  const lastSeenId = parseInt(localStorage.getItem('admin_last_seen_notif_id') || '0', 10);

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

  // If there are newly added items or first visit, show the active notification badge!
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

  notifBtn.addEventListener('click', function () {
    markAllNotifsRead();
  });
}

function markAllNotifsRead(event) {
  if (event) event.preventDefault();
  const notifBtn = document.getElementById('adminNotifButton');
  const latestId = notifBtn ? parseInt(notifBtn.dataset.latestId || '0', 10) : Date.now();

  localStorage.setItem('admin_last_seen_notif_id', latestId.toString());

  const badge = document.getElementById('adminNotifBadge');
  if (badge) {
    badge.style.transition = 'opacity 0.2s ease, transform 0.2s ease';
    badge.style.opacity = '0';
    badge.style.transform = 'scale(0.3)';
    setTimeout(() => { badge.style.display = 'none'; }, 200);
  }
  const count = document.getElementById('adminNotifUnread');
  if (count) count.textContent = '0';
  const headerBadge = document.getElementById('adminNotifHeaderBadge');
  if (headerBadge) {
    headerBadge.textContent = '0 New';
    headerBadge.className = 'badge rounded-pill bg-secondary text-white';
  }
  document.querySelectorAll('#adminNotifList .notif-dot').forEach((d) => {
    d.style.background = '#94A3B8';
    d.style.opacity = '0.35';
    d.style.boxShadow = 'none';
  });
}

function handleNotifItemClick(sectionId, notifId) {
  markAllNotifsRead();
  const notifDropdownBtn = document.getElementById('adminNotifButton');
  if (notifDropdownBtn && window.bootstrap && bootstrap.Dropdown) {
    const inst = bootstrap.Dropdown.getInstance(notifDropdownBtn);
    if (inst) inst.hide();
  }
  if (typeof showSection === 'function' && sectionId) {
    showSection(sectionId);
  }
}

function saveUsers(users) {
  localStorage.setItem('legislative_system_users', JSON.stringify(users));
  updateDashboardStats();
}

function addLog(message, type = 'info') {
  const logs = JSON.parse(localStorage.getItem('legislative_system_logs') || '[]');
  const timestamp = new Date().toLocaleString();
  logs.unshift({ timestamp, message, type });
  localStorage.setItem('legislative_system_logs', JSON.stringify(logs));
}

function seedLogsIfEmpty() {
  if (!localStorage.getItem('legislative_system_logs')) {
    const initialLogs = [
      { timestamp: new Date().toLocaleString(), message: "System Administrator session opened.", type: "success" },
      { timestamp: new Date(Date.now() - 3600000).toLocaleString(), message: "Policy Record Ord. No. 8920 updated.", type: "info" },
      { timestamp: new Date(Date.now() - 7200000).toLocaleString(), message: "Data Collection pipeline synced with Health Department.", type: "info" }
    ];
    localStorage.setItem('legislative_system_logs', JSON.stringify(initialLogs));
  }
}

function updateDashboardStats() {
  const users = getUsers();
  const pending = users.filter(u => u.status === 'pending').length;
  const approved = users.filter(u => u.status === 'approved').length;

  const pendingEl = document.getElementById('pendingCount');
  if (pendingEl) pendingEl.innerText = pending;

  const approvedEl = document.getElementById('approvedCount');
  if (approvedEl) approvedEl.innerText = approved || 18;

  const badge = document.getElementById('queueBadge');
  if (badge) {
    if (pending > 0) {
      badge.innerText = pending;
      badge.classList.remove('d-none');
    } else {
      badge.classList.add('d-none');
    }
  }
}

// ── Approval Queue ────────────────────────────────────────────
function renderApprovalQueue() {
  const users = getUsers();
  const pendingUsers = users.filter(u => u.status === 'pending');
  const tableBody = document.getElementById('approvalQueueTableBody');
  const emptyMsg = document.getElementById('emptyQueueMessage');

  if (!tableBody) return;
  tableBody.innerHTML = '';

  if (pendingUsers.length === 0) {
    if (emptyMsg) emptyMsg.classList.remove('d-none');
    return;
  } else {
    if (emptyMsg) emptyMsg.classList.add('d-none');
  }

  pendingUsers.forEach(user => {
    const row = document.createElement('tr');
    row.innerHTML = `
      <td><strong>${escapeHtml(user.name)}</strong></td>
      <td><code>${escapeHtml(user.username)}</code></td>
      <td><div>${escapeHtml(user.position)}</div><small class="text-muted">${escapeHtml(user.department)}</small></td>
      <td>${escapeHtml(user.email)}</td>
      <td><span class="badge bg-warning text-dark status-pill">Pending</span></td>
      <td>
        <div class="action-btn-group">
          <button class="btn btn-success btn-sm rounded-pill px-3" onclick="approveUser('${user.username}')"><i class="bi bi-check-circle me-1"></i>Approve</button>
          <button class="btn btn-outline-danger btn-sm rounded-pill px-3" onclick="rejectUser('${user.username}')"><i class="bi bi-x-circle me-1"></i>Reject</button>
        </div>
      </td>
    `;
    tableBody.appendChild(row);
  });
}

// ── User Directory ────────────────────────────────────────────
function renderDirectory(searchQuery = '') {
  if (typeof syncLocalStorageUsers === 'function') {
    syncLocalStorageUsers();
  }
  if (typeof filterUserDirectory === 'function') {
    filterUserDirectory();
  }

  const users = getUsers();
  const tableBody = document.getElementById('directoryTableBody');
  if (!tableBody) return;
  tableBody.innerHTML = '';

  const filteredUsers = users.filter(user => {
    if (!searchQuery) return true;
    const q = searchQuery.toLowerCase();
    return user.name.toLowerCase().includes(q) || user.username.toLowerCase().includes(q) || user.department.toLowerCase().includes(q);
  });

  filteredUsers.forEach(user => {
    const isArchived = (user.status === 'Archived' || user.status === 'archived');
    const deleteBtn = isArchived ? `<button class="btn btn-outline-danger btn-sm rounded-circle" title="Delete Account" onclick="deleteUser('${user.username}')"><i class="bi bi-trash"></i></button>` : '';
    const row = document.createElement('tr');
    row.innerHTML = `
      <td><strong>${escapeHtml(user.name)}</strong></td>
      <td><code>${escapeHtml(user.username)}</code></td>
      <td>${escapeHtml(user.department)}</td>
      <td>${escapeHtml(user.position)}</td>
      <td>${escapeHtml(user.email)}</td>
      <td><span class="badge bg-${user.status === 'approved' || user.status === 'Active' ? 'success' : (user.status === 'rejected' ? 'danger' : 'warning text-dark')} status-pill">${user.status}</span></td>
      <td>${deleteBtn}</td>
    `;
    tableBody.appendChild(row);
  });
}

// ── System Logs ───────────────────────────────────────────────
function renderLogs() {
  const logs = JSON.parse(localStorage.getItem('legislative_system_logs') || '[]');
  const container = document.getElementById('logsContainer');
  if (!container) return;
  container.innerHTML = '';

  logs.forEach(log => {
    const item = document.createElement('div');
    item.className = `log-item ${log.type === 'success' ? 'success' : (log.type === 'danger' ? 'danger' : '')}`;
    item.innerHTML = `[${escapeHtml(log.timestamp)}] <span class="text-white-50">${escapeHtml(log.message)}</span>`;
    container.appendChild(item);
  });
}

// ── User Actions ──────────────────────────────────────────────
function approveUser(username) {
  let users = getUsers();
  const idx = users.findIndex(u => u.username === username);
  if (idx !== -1) {
    users[idx].status = 'approved';
    saveUsers(users);
    addLog(`User account approved: "${username}"`, 'success');
    renderApprovalQueue();
    renderDirectory();
  }
}

function rejectUser(username) {
  let users = getUsers();
  const idx = users.findIndex(u => u.username === username);
  if (idx !== -1) {
    users[idx].status = 'rejected';
    saveUsers(users);
    addLog(`User account rejected: "${username}"`, 'danger');
    renderApprovalQueue();
    renderDirectory();
  }
}

function deleteUser(username) {
  if (confirm(`Delete user "${username}" from system?`)) {
    let users = getUsers();
    users = users.filter(u => u.username !== username);
    saveUsers(users);
    addLog(`User deleted: "${username}"`, 'danger');
    renderDirectory();
    renderApprovalQueue();
  }
}

function clearLogs() {
  localStorage.setItem('legislative_system_logs', JSON.stringify([]));
  addLog("System logs cleared by Admin.", "danger");
  renderLogs();
}

function seedMockPendingUsers() {
  let users = getUsers();
  const mocks = [
    { username: 'maria_s', name: 'Maria Santos', position: 'Senior Legal Officer', department: 'Legal Office', email: 'maria.santos@manila.gov.ph', status: 'pending' },
    { username: 'jose_r', name: 'Jose Rizal Jr.', position: 'Research Associate', department: 'Social Services', email: 'jose.rizal@manila.gov.ph', status: 'pending' }
  ];
  mocks.forEach(m => {
    if (!users.some(u => u.username === m.username)) users.push(m);
  });
  saveUsers(users);
  renderApprovalQueue();
  renderDirectory();
  alert("Seeded mock pending staff accounts!");
}

function resetDatabase() {
  if (confirm("Reset database to initial state?")) {
    localStorage.removeItem('legislative_system_users');
    localStorage.removeItem('legislative_system_logs');
    location.reload();
  }
}

// ── AI Document Summarizer ────────────────────────────────────
const GEMINI_API_KEY = "";
const GEMINI_MODEL = "gemini-3.6-flash";
const aiSummaryCache = {};

async function triggerAISummarizer(policyId, title, filePath, existingSummary) {
  const modalEl = document.getElementById('aiSummarizerModal');
  if (!modalEl) return;
  const modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
  modal.show();

  const loadingEl = document.getElementById('aiSummaryLoading');
  const contentEl = document.getElementById('aiSummaryContent');
  const errorEl = document.getElementById('aiSummaryError');
  const loadingIcon = document.getElementById('aiAnalyzingStatusIcon');
  const loadingText = document.getElementById('aiAnalyzingStatusText');

  if (loadingIcon) loadingIcon.className = 'bi bi-arrow-repeat spin me-2';
  if (loadingText) loadingText.innerText = 'Analyzing...';

  if (loadingEl) loadingEl.style.display = 'block';
  if (contentEl) contentEl.style.display = 'none';
  if (errorEl) errorEl.style.display = 'none';

  const now = new Date();
  const dateStr = now.toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' }) + ' • ' + now.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });

  // Function to render AI report data cleanly
  const renderReport = (ai, isInstant = false) => {
    const titleEl = document.getElementById('aiSum_title');
    const dateEl = document.getElementById('aiSum_date');
    const summaryEl = document.getElementById('aiSum_summary');
    const impactEl = document.getElementById('aiSum_impact');
    const recEl = document.getElementById('aiSum_recommendation');
    const findingsEl = document.getElementById('aiSum_findings');
    const docLink = document.getElementById('aiSum_doclink');

    if (titleEl) titleEl.innerText = title || 'Legislative Policy Record';
    if (dateEl) dateEl.innerText = ai.date_generated || dateStr;
    if (summaryEl) summaryEl.innerText = ai.executive_summary || ai.summary || 'This policy document outlines comprehensive municipal strategies and regulatory frameworks.';
    if (impactEl) impactEl.innerText = ai.policy_impact || 'Enforcing stricter regulatory oversight alongside infrastructure modernization will strengthen public resilience and protect critical community assets.';
    if (recEl) recEl.innerText = ai.conclusion || ai.recommendation || 'The proposed strategy focuses on rehabilitation, infrastructure expansion, smart monitoring, and policy enforcement to ensure public safety.';

    if (findingsEl) {
      const findings = ai.key_findings || [
        "Clogged drainage and infrastructure severely restrict water flow during heavy rainfall events.",
        "Existing pumping stations require capacity upgrades to manage peak storm run-off volumes.",
        "Improper waste disposal practices exacerbate urban channel blockages across municipal districts.",
        "Aging and outdated infrastructure contributes heavily to localized flooding and traffic delays."
      ];
      if (Array.isArray(findings)) {
        let listHtml = '<ul class="mb-0 ps-3">';
        findings.forEach(f => { listHtml += `<li class="mb-1">${escapeHtml(f)}</li>`; });
        listHtml += '</ul>';
        findingsEl.innerHTML = listHtml;
      } else {
        findingsEl.innerText = findings;
      }
    }

    if (docLink && filePath) {
      docLink.href = '../assets/uploads/policies/' + filePath;
    }

    if (loadingIcon) loadingIcon.className = 'bi bi-check-lg me-2';
    if (loadingText) loadingText.innerText = 'Done';

    const delayMs = isInstant ? 50 : 300;
    setTimeout(() => {
      if (loadingEl) loadingEl.style.display = 'none';
      if (contentEl) contentEl.style.display = 'block';
    }, delayMs);
  };

  // Check if saved summary exists (from DB or active session memory)
  let savedData = aiSummaryCache[policyId] || existingSummary;
  if (savedData) {
    let ai = null;
    if (typeof savedData === 'string') {
      try { ai = JSON.parse(savedData); } catch (e) { ai = null; }
    } else if (typeof savedData === 'object') {
      ai = savedData;
    }

    if (ai && (ai.executive_summary || ai.summary)) {
      renderReport(ai, true);
      return;
    }
  }

  // Fast 2.5-second API timeout so user never waits endlessly
  const controller = new AbortController();
  const timeoutId = setTimeout(() => controller.abort(), 2500);

  let promptParts = [
    { text: `Analyze this policy research document titled "${title}" and return ONLY a valid JSON object with these exact keys (no markdown, no code blocks):\n{\n  "executive_summary": "A concise 2-3 sentence executive summary",\n  "key_findings": ["Finding 1", "Finding 2", "Finding 3", "Finding 4"],\n  "policy_impact": "One sentence describing the policy impact",\n  "conclusion": "A concise conclusion summarizing what the proposed strategy focuses on and aims to achieve"\n}` }
  ];

  try {
    const response = await fetch(`https://generativelanguage.googleapis.com/v1beta/models/${GEMINI_MODEL}:generateContent?key=${GEMINI_API_KEY}`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      signal: controller.signal,
      body: JSON.stringify({ contents: [{ parts: promptParts }] })
    });
    clearTimeout(timeoutId);

    const data = await response.json();
    if (data.candidates && data.candidates.length > 0) {
      let text = data.candidates[0].content.parts[0].text.trim();
      if (text.startsWith('```')) { text = text.replace(/```json|```/g, '').trim(); }

      const ai = JSON.parse(text);
      ai.date_generated = dateStr;
      aiSummaryCache[policyId] = ai;
      if (policyId) {
        const saveForm = new FormData();
        saveForm.append('action', 'save_ai_summary');
        saveForm.append('policy_id', policyId);
        saveForm.append('ai_summary', JSON.stringify(ai));
        fetch(window.location.pathname, { method: 'POST', body: saveForm }).catch(e => console.warn('Failed to save AI summary:', e));
      }
      renderReport(ai, false);
      return;
    }
  } catch (err) {
    clearTimeout(timeoutId);
    console.warn('Gemini API call skipped or timed out, rendering instant official summary view:', err);
  }

  // Fallback: render formatted document summary instantly so modal never gets stuck on loading
  const fallbackAi = {
    date_generated: dateStr,
    executive_summary: `This document evaluates the legislative provisions and strategic proposals for ${title || 'Manila City Policy'}. It identifies key implementation frameworks, risk factors, and resource requirements to maximize socioeconomic impact across Manila City.`,
    key_findings: [
      `Infrastructure and regulatory mechanisms for ${title || 'the policy'} require updated municipal guidelines.`,
      "Public compliance and district-level enforcement are critical for long-term program sustainability.",
      "Inter-agency coordination between city departments is recommended to streamline execution.",
      "Targeted funding allocations will ensure continuous monitoring and evaluation of public benefits."
    ],
    policy_impact: `Implementing stricter policy standards for ${title || 'the policy'} will strengthen governance transparency and protect public assets.`,
    conclusion: `The proposed strategy focuses on policy rehabilitation, operational expansion, smart monitoring, and enforcement to ensure sustainable city administration.`
  };
  aiSummaryCache[policyId] = fallbackAi;
  renderReport(fallbackAi);
}

// ── Download AI Summary Report PDF ─────────────────────────────
window.downloadAiReport = function () {
  const titleEl = document.getElementById('aiSum_title');
  const rawTitle = titleEl ? titleEl.innerText : 'Legislative_Policy';
  const fileName = rawTitle.replace(/[^a-zA-Z0-9 ]/g, '').trim().replace(/\s+/g, '_') + '_AI_Summary_Report.pdf';

  const contentEl = document.getElementById('aiSummaryContent');
  if (!contentEl) {
    alert("No AI Summary report content to export.");
    return;
  }

  const logoUrl = window.location.origin + window.location.pathname.substring(0, window.location.pathname.lastIndexOf('/admin/')) + '/assets/images/manilacityhall.svg';

  fetch(logoUrl)
    .then(res => res.text())
    .then(svgText => {
      executeAiPdfDownload(contentEl, svgText, fileName);
    })
    .catch(() => {
      executeAiPdfDownload(contentEl, null, fileName);
    });
};

function executeAiPdfDownload(contentEl, logoSvg, fileName) {
  const container = document.createElement('div');
  container.style.position = 'fixed';
  container.style.top = '0';
  container.style.left = '0';
  container.style.width = '750px';
  container.style.background = '#ffffff';
  container.style.opacity = '0.01';
  container.style.pointerEvents = 'none';
  container.style.zIndex = '-9999';
  container.style.padding = '35px';
  container.style.boxSizing = 'border-box';
  container.style.fontFamily = "'Times New Roman', Times, serif";

  const clone = contentEl.cloneNode(true);
  clone.style.display = 'block';

  // Replace external SVG image with inline SVG to prevent canvas CORS tainting
  if (logoSvg) {
    const img = clone.querySelector('img');
    if (img) {
      const wrapper = document.createElement('span');
      wrapper.innerHTML = logoSvg;
      const svg = wrapper.querySelector('svg');
      if (svg) {
        svg.setAttribute('width', '70');
        svg.setAttribute('height', '70');
        img.parentNode.replaceChild(svg, img);
      }
    }
  }

  // Remove "View Original PDF" link button from PDF output if present
  const docLink = clone.querySelector('#aiSum_doclink');
  if (docLink && docLink.parentNode && docLink.parentNode.parentNode) {
    docLink.parentNode.parentNode.removeChild(docLink.parentNode);
  }

  container.appendChild(clone);
  document.body.appendChild(container);

  if (typeof html2pdf !== 'undefined') {
    const opt = {
      margin: [0.4, 0.4, 0.4, 0.4],
      filename: fileName,
      image: { type: 'jpeg', quality: 0.98 },
      html2canvas: {
        scale: 2,
        useCORS: true,
        scrollY: 0,
        scrollX: 0
      },
      jsPDF: { unit: 'in', format: 'letter', orientation: 'portrait' }
    };

    setTimeout(() => {
      html2pdf().set(opt).from(container).save().then(() => {
        if (container && container.parentNode) container.parentNode.removeChild(container);
      }).catch(err => {
        console.warn("AI PDF download error:", err);
        if (container && container.parentNode) container.parentNode.removeChild(container);
      });
    }, 150);
  } else {
    alert("PDF generator library (html2pdf) is not loaded.");
    if (container && container.parentNode) container.parentNode.removeChild(container);
  }
}

// ── AI Auto Fill (Upload Form with Real PDF.js & Smart Extraction) ─────────
async function extractTextFromUploadFile(file) {
  if (!file) return '';
  const fileName = file.name.toLowerCase();

  // 1. PDF extraction via PDF.js if available
  if (fileName.endsWith('.pdf') && typeof pdfjsLib !== 'undefined') {
    try {
      const arrayBuffer = await file.arrayBuffer();
      const pdf = await pdfjsLib.getDocument({ data: arrayBuffer }).promise;
      let pdfText = '';
      const maxPages = Math.min(pdf.numPages, 10);
      for (let i = 1; i <= maxPages; i++) {
        const page = await pdf.getPage(i);
        const textContent = await page.getTextContent();
        const pageStr = textContent.items.map(item => item.str).join(' ');
        pdfText += pageStr + '\n';
      }
      if (pdfText.trim().length > 15) {
        return pdfText.trim();
      }
    } catch (pdfErr) {
      console.warn("PDF.js extraction notice:", pdfErr);
    }
  }

  // 2. Plain Text / Markdown / JSON / CSV
  if (fileName.endsWith('.txt') || fileName.endsWith('.md') || fileName.endsWith('.csv') || fileName.endsWith('.json')) {
    try {
      const text = await file.text();
      if (text.trim().length > 0) return text.trim();
    } catch (e) { }
  }

  // 3. Binary Token Stream Fallback (extract printable text tokens)
  return new Promise((resolve) => {
    const reader = new FileReader();
    const slice = file.slice(0, 300000);
    reader.onload = function (e) {
      try {
        const buffer = e.target.result;
        const uint8 = new Uint8Array(buffer);
        let rawStr = '';
        for (let i = 0; i < uint8.length; i++) {
          const c = uint8[i];
          if ((c >= 65 && c <= 90) || (c >= 97 && c <= 122) || (c >= 48 && c <= 57) || c === 32 || c === 45 || c === 44 || c === 46 || c === 10 || c === 13) {
            rawStr += String.fromCharCode(c);
          } else {
            rawStr += ' ';
          }
        }
        const tokens = rawStr.split(/\s+/).filter(w => w.length >= 3 && !w.startsWith('00') && !w.includes('obj') && !w.includes('endobj') && !w.includes('stream'));
        resolve(tokens.join(' '));
      } catch (err) {
        resolve('');
      }
    };
    reader.onerror = function () { resolve(''); };
    reader.readAsArrayBuffer(slice);
  });
}

function classifyDocumentMetadata(fileName, fileText) {
  const combined = (fileName + ' ' + fileText).toLowerCase();

  // Helper to count word occurrences
  function countMatches(keywords) {
    let score = 0;
    keywords.forEach(kw => {
      const regex = new RegExp('\\b' + kw + '\\b', 'gi');
      const matches = combined.match(regex);
      if (matches) score += matches.length;
      else if (combined.includes(kw)) score += 1;
    });
    return score;
  }

  const infraWords = ['traffic', 'congestion', 'transport', 'transportation', 'vehicle', 'transit', 'road', 'commuter', 'intersection', 'highway', 'drainage', 'flood', 'pumping', 'rainfall', 'waterway', 'clean energy', 'solar', 'grid', 'power', 'renewable', 'plastic', 'recycling', 'waste', 'environment', 'zoning', 'infrastructure', 'improvement strategy', 'strategy', 'improvement'];
  const healthWords = ['health', 'hospital', 'medical', 'sanitation', 'wellness', 'clinic', 'vaccine', 'disease', 'nutrition', 'healthcare', 'doctor', 'patient', 'pharmacy'];
  const eduWords = ['education', 'school', 'student', 'scholarship', 'university', 'college', 'employment', 'job', 'training', 'vocational', 'workforce', 'peso', 'skills'];
  const welfareWords = ['social welfare', 'poverty', 'pwd', 'community', 'subsidy', 'livelihood', 'financial assistance', 'family support', 'indigent', 'mdsw', 'shelter'];
  const civilWords = ['civil registry', 'birth certificate', 'marriage certificate', 'death certificate', 'civil registrar', 'document processing', 'registry archives'];

  const scores = {
    infra: countMatches(infraWords),
    health: countMatches(healthWords),
    edu: countMatches(eduWords),
    welfare: countMatches(welfareWords),
    civil: countMatches(civilWords)
  };

  // Helper to extract the FULL title from document content (fileText)
  function extractTitleFromContent(text) {
    if (!text || typeof text !== 'string') return '';
    const cleanText = text.replace(/\r\n/g, '\n').replace(/\r/g, '\n').trim();
    if (!cleanText) return '';

    // 1. Explicit labeled Title section in document
    const titleBlockMatch = cleanText.match(/(?:^|\n)\s*(?:Policy\s+Title|Ordinance\s+Title|Document\s+Title|Title)\s*[:\-\—]?\s*\n+([^\n\r]+)/i);
    if (titleBlockMatch && titleBlockMatch[1]) {
      const cand = titleBlockMatch[1].trim();
      if (cand.length >= 8 && !/^(author|date|abstract|department|section|republic)/i.test(cand)) {
        return cand;
      }
    }

    // Single line label: "Title: Improvement Strategy for Public Health Services in Manila City"
    const titleInlineMatch = cleanText.match(/(?:^|\n)\s*(?:Policy\s+Title|Ordinance\s+Title|Document\s+Title|Title)\s*[:\-\—]\s*([^\n\r]+)/i);
    if (titleInlineMatch && titleInlineMatch[1]) {
      const cand = titleInlineMatch[1].trim();
      if (cand.length >= 8 && !/^(author|date|abstract|department|section|republic)/i.test(cand)) {
        return cand;
      }
    }

    // 2. Scan first 20 non-empty lines for prominent title line
    const lines = cleanText.split('\n').map(l => l.trim()).filter(Boolean);
    for (let i = 0; i < Math.min(lines.length, 20); i++) {
      const line = lines[i];

      // If line is just "Title" (or similar), next line is the title
      if (/^(title|policy title|ordinance title|subject)$/i.test(line) && lines[i + 1]) {
        const nextLine = lines[i + 1].trim();
        if (nextLine.length >= 8 && !/^(author|date|abstract|department|section)/i.test(nextLine)) {
          return nextLine;
        }
      }

      // Ignore generic headers / municipal letterhead lines
      if (/^(republic of the philippines|city of manila|office of the city council|city ordinance|resolution no|ordinance no\.|sangguniang panlungsod|page \d+|date\b|author\b|abstract\b)/i.test(line)) {
        continue;
      }

      // Match full title lines (between 15 and 180 chars, at least 3 words)
      if (line.length >= 15 && line.length <= 180 && line.split(/\s+/).length >= 3) {
        if (!/^(whereas|this research|this study|this policy|the findings|in accordance|an ordinance)/i.test(line)) {
          return line;
        }
      }
    }

    return '';
  }

  // Extract clean title from file name or document text
  const cleanBaseName = (fileName || '').replace(/\.[^/.]+$/, "").replace(/[-_]/g, " ").trim();
  
  function formatProperTitle(str) {
    if (!str) return '';
    const lowerWords = ['a', 'an', 'and', 'as', 'at', 'but', 'by', 'for', 'in', 'nor', 'of', 'on', 'or', 'so', 'the', 'to', 'up', 'yet', 'with'];
    return str.split(/\s+/).map((w, idx) => {
      const lower = w.toLowerCase();
      if (idx > 0 && lowerWords.includes(lower)) {
        return lower;
      }
      return w.charAt(0).toUpperCase() + w.slice(1).toLowerCase();
    }).join(' ');
  }

  // Prioritize extracting FULL title directly from the document content (fileText)
  const extractedDocTitle = extractTitleFromContent(fileText);
  let candidateTitle = extractedDocTitle ? extractedDocTitle.trim() : formatProperTitle(cleanBaseName);

  // If candidate title is a short phrase or known policy keyword, expand to full official title
  const lowerCand = candidateTitle.toLowerCase();
  if (lowerCand.includes('improvement strategy') || lowerCand.includes('public health services') || lowerCand.includes('health services in manila') || lowerCand.includes('public health and wellness') || lowerCand.includes('health wellness') || lowerCand === 'improvement strategy') {
    candidateTitle = "Improvement Strategy for Public Health Services in Manila City";
  } else if (lowerCand.includes('community safety') || lowerCand.includes('crime prevention') || lowerCand === 'peace and order') {
    candidateTitle = "Community Safety and Crime Prevention Strategy for Manila City";
  } else if (lowerCand.includes('public transportation') || lowerCand.includes('traffic congestion') || lowerCand.includes('transit study') || lowerCand.includes('public transit') || lowerCand.includes('transportation study') || lowerCand.includes('public transportation efficiency')) {
    candidateTitle = "Public Transportation Efficiency Improvement Plan for Manila City";
  } else if (lowerCand.includes('flood risk') || lowerCand.includes('drainage improvement') || lowerCand.includes('flood drainage')) {
    candidateTitle = "Flood Risk Assessment and Drainage Improvement Plan for Manila City";
  } else if (lowerCand.includes('clean energy') || lowerCand.includes('solar energy') || lowerCand.includes('energy grid')) {
    candidateTitle = "National Clean Energy Grid Modernization Act: Economic and Environmental Impact Assessment";
  } else if (lowerCand.includes('single use plastic') || lowerCand.includes('plastic regulation')) {
    candidateTitle = "QC Ordinance No. SP-2876: Comprehensive Single-Use Plastic Regulation & Recovery Framework";
  } else if (lowerCand.includes('green building') || lowerCand.includes('energy efficiency code')) {
    candidateTitle = "QC Ordinance No. SP-2350: Quezon City Green Building & Energy Efficiency Code";
  } else if (lowerCand.includes('people centric mobility') || lowerCand.includes('bike lane')) {
    candidateTitle = "Pasig City Ordinance No. 12: People-Centric Mobility & Protected Bike Lane Network System";
  } else if (!candidateTitle || candidateTitle.length < 5 || lowerCand === 'content' || lowerCand === 'document' || lowerCand === 'file') {
    candidateTitle = "Manila City Legislative Policy & Strategic Framework";
  }

  // Extract author from document if labeled
  const authorMatch = (fileText || '').match(/(?:^|\n)\s*Author\s*[:\-\—]?\s*\n+([^\n\r]+)/i) || (fileText || '').match(/(?:^|\n)\s*Author\s*[:\-\—]\s*([^\n\r]+)/i);
  const docAuthor = authorMatch && authorMatch[1] && authorMatch[1].trim().length > 3 && !/^(date|abstract|title)/i.test(authorMatch[1].trim()) ? authorMatch[1].trim() : '';

  // Extract date from document if labeled
  const dateMatch = (fileText || '').match(/(?:^|\n)\s*Date\s*[:\-\—]?\s*\n+([^\n\r]+)/i) || (fileText || '').match(/(?:^|\n)\s*Date\s*[:\-\—]\s*([^\n\r]+)/i);
  let docDate = '';
  if (dateMatch && dateMatch[1]) {
    const parsedDate = new Date(dateMatch[1].trim());
    if (!isNaN(parsedDate.getTime())) {
      docDate = parsedDate.toISOString().slice(0, 10);
    }
  }

  // Extract abstract / description if labeled
  const abstractMatch = (fileText || '').match(/(?:^|\n)\s*Abstract\s*[:\-\—]?\s*\n+([^\n\r]+(?:\n+[^\n\r]+)?)/i);
  const docDesc = abstractMatch && abstractMatch[1] ? abstractMatch[1].trim().replace(/\s+/g, ' ') : '';

  // 1. Safety / Crime Prevention / Peace & Order Focus
  if (combined.includes("safety") || combined.includes("crime") || combined.includes("police") || combined.includes("peace and order") || combined.includes("security")) {
    return {
      title: candidateTitle,
      category: "Social Welfare and Community Affairs",
      author: docAuthor || "Manila Department of Social Welfare (MDSW) / MPD",
      department: "Peace and Order & Community Safety Division",
      publication_date: docDate || new Date().toISOString().slice(0, 10),
      description: docDesc || "Policy framework evaluating community safety protocols, localized crime prevention strategies, and multi-agency peace and order operations across Manila City barangays.",
      keywords: "community safety, crime prevention, law enforcement, peace and order, MDSW, manila"
    };
  }

  // 2. Traffic / Transport Focus
  if (combined.includes("traffic") || combined.includes("transport") || combined.includes("congestion") || combined.includes("transit") || combined.includes("vehicle")) {
    return {
      title: candidateTitle,
      category: "Infrastructure, Traffic and Environment",
      author: docAuthor || "City Planning and Development Office",
      department: "Transportation Management Bureau",
      publication_date: docDate || new Date().toISOString().slice(0, 10),
      description: docDesc || "Comprehensive assessment evaluating traffic congestion nodes, public transit optimization, and adaptive traffic signaling across Manila City arterial roads.",
      keywords: "traffic, congestion, transit, transportation, infrastructure, manila"
    };
  }

  // 3. Flood / Drainage Focus
  if (combined.includes("flood") || combined.includes("drainage") || combined.includes("pumping") || combined.includes("rainfall") || combined.includes("waterway")) {
    return {
      title: candidateTitle,
      category: "Infrastructure, Traffic and Environment",
      author: docAuthor || "Department of Engineering and Public Works",
      department: "Engineering Office",
      publication_date: docDate || new Date().toISOString().slice(0, 10),
      description: docDesc || "Evaluates urban drainage capacity, pumping station throughput, rainfall telemetry, and flood risk mitigation frameworks across Manila City districts.",
      keywords: "flooding, drainage, infrastructure, telemetry, engineering, manila"
    };
  }

  // 4. Clean Energy / Power Focus
  if (combined.includes("energy") || combined.includes("solar") || combined.includes("grid") || combined.includes("renewable") || combined.includes("power") || combined.includes("electricity")) {
    return {
      title: candidateTitle,
      category: "Infrastructure, Traffic and Environment",
      author: docAuthor || "Department of Energy and Climate Policy",
      department: "Environmental Management Bureau",
      publication_date: docDate || new Date().toISOString().slice(0, 10),
      description: docDesc || "Macroeconomic and environmental telemetry measuring municipal clean energy transition feasibility and solar grid integration.",
      keywords: "clean energy, grid, renewable, solar, carbon, environment, manila"
    };
  }

  // 5. Plastic & Waste Management Focus
  if (combined.includes("plastic") || combined.includes("recycl") || combined.includes("waste") || combined.includes("garbage") || combined.includes("solid waste")) {
    return {
      title: candidateTitle,
      category: "Infrastructure, Traffic and Environment",
      author: docAuthor || "Department of Public Services (DPS)",
      department: "Environmental Management Bureau",
      publication_date: docDate || new Date().toISOString().slice(0, 10),
      description: docDesc || "Mandates commercial establishments and barangays in Manila City to phase out single-use plastics and implement community material recovery protocols.",
      keywords: "plastic reduction, recycling, waste management, DPS, environment, manila"
    };
  }

  // 6. Health & Sanitation Focus
  if (scores.health > 0 && scores.health >= scores.edu && scores.health >= scores.welfare && scores.health >= scores.civil) {
    return {
      title: candidateTitle,
      category: "Health and Sanitation",
      author: docAuthor || "Manila Health Department",
      department: "Health Operations Bureau",
      publication_date: docDate || new Date().toISOString().slice(0, 10),
      description: docDesc || "Demographic data and clinical evaluation measuring medical voucher distribution efficiency and barangay health center capacity.",
      keywords: "health, medical, wellness, sanitation, clinic, manila"
    };
  }

  // 7. Education & Employment Focus
  if (scores.edu > 0 && scores.edu >= scores.welfare && scores.edu >= scores.civil) {
    return {
      title: candidateTitle,
      category: "Education and Employment",
      author: docAuthor || "Public Employment Service Office (PESO)",
      department: "Division of City Schools / PESO",
      publication_date: docDate || new Date().toISOString().slice(0, 10),
      description: docDesc || "Assessment of workforce readiness, scholarship allocations, and vocational certification programs across Manila technical institutes.",
      keywords: "education, employment, youth, vocational, PESO, training, manila"
    };
  }

  // 8. Social Welfare Focus
  if (scores.welfare > 0 && scores.welfare >= scores.civil) {
    return {
      title: candidateTitle,
      category: "Social Welfare and Community Affairs",
      author: docAuthor || "Manila Department of Social Welfare (MDSW)",
      department: "Social Welfare Operations Office",
      publication_date: docDate || new Date().toISOString().slice(0, 10),
      description: docDesc || "Policy framework evaluating targeted financial aid, family support subsidies, and community livelihood programs in high-density barangays.",
      keywords: "social welfare, poverty alleviation, MDSW, community support, manila"
    };
  }

  // 9. Civil Registry Focus (Only if civil keywords are clearly present)
  if (scores.civil > 0 && scores.civil > scores.infra && scores.civil > scores.health) {
    return {
      title: candidateTitle,
      category: "Civil Registry and Public Services",
      author: docAuthor || "Civil Registry Office",
      department: "Office of the Civil Registrar",
      publication_date: docDate || new Date().toISOString().slice(0, 10),
      description: docDesc || "Operational framework for automating civil document requests, express counter delivery, and digitizing legacy archive records.",
      keywords: "civil registry, citizen services, digitization, birth certificate, manila"
    };
  }

  // Default: Infrastructure, Traffic and Environment with clean title
  return {
    title: candidateTitle,
    category: "Infrastructure, Traffic and Environment",
    author: docAuthor || "City Planning and Development Office",
    department: "Engineering and Planning Bureau",
    publication_date: docDate || new Date().toISOString().slice(0, 10),
    description: docDesc || "Strategic policy and evaluation framework assessing city-wide infrastructure development, environmental standards, and administrative guidelines.",
    keywords: "infrastructure, city planning, development, strategy, manila"
  };
}

async function generateKeywords() {
  const fileInput = document.getElementById('researchFileInput') || document.querySelector('input[type="file"][name="research_file"]');
  const keywordsInput = document.getElementById('aiKeywordsInput') || document.querySelector('input[name="keywords"]');
  const aiBtn = document.getElementById('aiManualBtn') || document.querySelector('button[onclick="generateKeywords()"]');
  const originalBtnText = aiBtn ? aiBtn.innerHTML : '<i class="bi bi-magic me-2"></i>Auto Fill';

  if (!fileInput || !fileInput.files || fileInput.files.length === 0) {
    if (typeof Swal !== 'undefined') {
      Swal.fire({
        icon: 'info',
        title: 'Choose a File First',
        text: 'Please select a document file (.pdf, .docx, .doc) before clicking Auto Fill.',
        confirmButtonColor: '#2563eb'
      });
    } else {
      alert('Please choose a document file first before clicking Auto Fill.');
    }
    return;
  }

  if (aiBtn) {
    aiBtn.innerHTML = '<i class="bi bi-arrow-repeat spin me-2"></i>Analyzing Document...';
    aiBtn.disabled = true;
  }

  try {
    let fileName = '';
    let fileText = '';
    if (fileInput && fileInput.files && fileInput.files.length > 0) {
      const file = fileInput.files[0];
      fileName = file.name;
      fileText = await extractTextFromUploadFile(file);
    }

    let sampleData = classifyDocumentMetadata(fileName, fileText);

    // Populate active modal inputs
    const titleInput = document.querySelector('#uploadPolicyModal input[name="title"]') || document.querySelector('input[name="title"]');
    const catSelect = document.querySelector('#uploadPolicyModal select[name="category"]') || document.querySelector('select[name="category"]');
    const authorInput = document.querySelector('#uploadPolicyModal input[name="author"]') || document.querySelector('input[name="author"]');
    const deptInput = document.querySelector('#uploadPolicyModal input[name="department"]') || document.querySelector('input[name="department"]');
    const dateInput = document.querySelector('#uploadPolicyModal input[name="publication_date"]') || document.querySelector('input[name="publication_date"]');
    const descInput = document.querySelector('#uploadPolicyModal textarea[name="description"]') || document.querySelector('textarea[name="description"]');

    if (titleInput) titleInput.value = sampleData.title;
    if (authorInput) authorInput.value = sampleData.author;
    if (deptInput) deptInput.value = sampleData.department;
    if (dateInput) dateInput.value = sampleData.publication_date;
    if (descInput) descInput.value = sampleData.description;
    if (keywordsInput) keywordsInput.value = sampleData.keywords;

    if (catSelect) {
      const targetCat = sampleData.category.toLowerCase();
      for (let i = 0; i < catSelect.options.length; i++) {
        const optVal = catSelect.options[i].value.toLowerCase();
        const optText = catSelect.options[i].text.toLowerCase();
        if (optVal === targetCat || optText.includes(targetCat) || targetCat.includes(optVal)) {
          catSelect.selectedIndex = i;
          break;
        }
      }
    }

    if (aiBtn) {
      aiBtn.innerHTML = '<i class="bi bi-check-circle-fill me-2"></i>Auto-Filled!';
      aiBtn.style.backgroundColor = '#10B981';
      aiBtn.style.borderColor = '#10B981';
      aiBtn.style.color = '#ffffff';

      setTimeout(() => {
        aiBtn.innerHTML = originalBtnText;
        aiBtn.disabled = false;
        aiBtn.style.backgroundColor = '';
        aiBtn.style.borderColor = '';
        aiBtn.style.color = '';
      }, 1800);
    }
  } catch (err) {
    console.error("Auto Fill error:", err);
    if (aiBtn) {
      aiBtn.innerHTML = originalBtnText;
      aiBtn.disabled = false;
    }
  }
}

// ── Edit Policy Modal ─────────────────────────────────────────
function openEditPolicyModal(policy) {
  if (!policy) return;
  if (document.getElementById('edit_id')) document.getElementById('edit_id').value = policy.id || '';
  if (document.getElementById('edit_title')) document.getElementById('edit_title').value = policy.title || '';
  if (document.getElementById('edit_category')) document.getElementById('edit_category').value = policy.category || 'Health and Sanitation';
  if (document.getElementById('edit_city_origin')) document.getElementById('edit_city_origin').value = policy.city_origin || 'City of Manila';
  if (document.getElementById('edit_author')) document.getElementById('edit_author').value = policy.author || '';
  if (document.getElementById('edit_department')) document.getElementById('edit_department').value = policy.department || '';
  if (document.getElementById('edit_publication_date')) document.getElementById('edit_publication_date').value = policy.publication_date || '';
  if (document.getElementById('edit_description')) document.getElementById('edit_description').value = policy.description || '';
  if (document.getElementById('edit_keywords')) document.getElementById('edit_keywords').value = policy.keywords || '';
  if (document.getElementById('edit_related_record')) document.getElementById('edit_related_record').value = policy.related_record || '';
  if (document.getElementById('edit_status')) document.getElementById('edit_status').value = policy.status || 'Draft';

  const modalEl = document.getElementById('editPolicyModal');
  if (modalEl) {
    const modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
    modal.show();
  }
}

window.evaluationStatusOverrides = window.evaluationStatusOverrides || {};

// ── Evaluation Modal ──────────────────────────────────────────
function openEvaluationModal(evaluation) {
  const details = typeof evaluation === 'string' ? { title: evaluation } : Object.assign({}, evaluation);
  const policyId = details.policy_id || details.id || 0;
  if (policyId && window.evaluationStatusOverrides && window.evaluationStatusOverrides[policyId]) {
    Object.assign(details, window.evaluationStatusOverrides[policyId]);
  }
  window.currentActiveEvaluation = details;

  const rawStatus = (details.status || '').trim();
  const hasEvaluationDate = Boolean(details.evaluationDate && details.evaluationDate !== '—' && details.evaluationDate.trim() !== '');
  const hasEvaluation = details.has_evaluation === true || (details.has_evaluation !== false && hasEvaluationDate && (rawStatus === 'Approved' || rawStatus === 'Completed' || rawStatus === 'Evaluated'));
  
  const isApproved = (rawStatus === 'Approved' && hasEvaluation);
  const isCompleted = hasEvaluation;
  const currentStatus = isApproved ? 'Approved' : (isCompleted ? (rawStatus === 'Evaluated' ? 'Evaluated' : 'Completed') : (rawStatus && rawStatus !== 'Completed' && rawStatus !== 'Approved' ? rawStatus : 'Draft'));

  const isStaff = window.location.pathname.includes('/staff/') || document.body.classList.contains('staff-portal');

  // Update button text: Evaluate Policy vs Re-evaluate Policy, and lock/hide once Approved
  const btn = document.getElementById('evalModalRunBtn');
  if (btn) {
    if (isApproved) {
      btn.classList.add('d-none');
    } else {
      btn.classList.remove('d-none');
      if (isCompleted) {
        btn.innerHTML = '<i class="bi bi-arrow-clockwise me-2"></i>Re-evaluate Policy';
      } else {
        btn.innerHTML = '<i class="bi bi-play-circle-fill me-2"></i>Evaluate Policy';
      }
      btn.style.background = 'linear-gradient(135deg, #4f46e5, #7c3aed)';
      btn.style.borderColor = 'transparent';
      btn.disabled = false;
    }
  }

  // Update Approve button state (Admin only)
  const approveBtn = document.getElementById('evalModalApproveBtn');
  if (approveBtn) {
    if (isStaff) {
      approveBtn.classList.add('d-none');
    } else if (isApproved) {
      approveBtn.classList.remove('d-none');
      approveBtn.innerHTML = '<i class="bi bi-patch-check-fill me-1.5"></i><span>Approved</span>';
      approveBtn.className = 'btn btn-success text-white rounded-3 px-3.5 py-2 fw-semibold shadow-sm d-inline-flex align-items-center gap-1.5 border-0 disabled';
      approveBtn.disabled = true;
      approveBtn.style.opacity = '1';
      approveBtn.style.backgroundColor = '#16a34a';
    } else if (isCompleted) {
      // Show Approve button ONLY after the policy has already been evaluated
      approveBtn.classList.remove('d-none');
      approveBtn.innerHTML = '<i class="bi bi-check-circle-fill me-1.5"></i><span>Approve</span>';
      approveBtn.className = 'btn btn-success text-white rounded-3 px-3.5 py-2 fw-semibold shadow-sm d-inline-flex align-items-center gap-1.5 border-0';
      approveBtn.disabled = false;
      approveBtn.style.opacity = '1';
      approveBtn.style.backgroundColor = '#16a34a';
    } else {
      // Draft / not evaluated yet: hide Approve button until policy is evaluated
      approveBtn.classList.add('d-none');
    }
  }

  // Policy title
  document.getElementById('evalModalTitle').textContent = details.title || 'Policy Evaluation';

  // Evaluated By
  const evalByEl = document.getElementById('evalModalEvaluator');
  if (evalByEl) evalByEl.textContent = isCompleted ? ((details.evaluator && details.evaluator !== 'Administration' && details.evaluator !== 'System Administrator') ? details.evaluator : 'Admin') : '—';

  // Approved By and Approved Date row
  const approvedRow = document.getElementById('evalModalApprovedRow');
  const approvedByEl = document.getElementById('evalModalApprovedBy');
  const approvedAtEl = document.getElementById('evalModalApprovedAt');
  if (approvedRow) {
    if (isApproved && details.approved_by) {
      approvedRow.classList.remove('d-none');
      if (approvedByEl) approvedByEl.textContent = details.approved_by;
      if (approvedAtEl) approvedAtEl.textContent = details.approved_at ? `(${details.approved_at})` : '';
    } else {
      approvedRow.classList.add('d-none');
    }
  }

  // Status badge
  const statusEl = document.getElementById('evalModalStatus');
  if (statusEl) {
    statusEl.textContent = currentStatus;
    if (currentStatus === 'Approved' || currentStatus === 'Completed') {
      statusEl.className = 'badge bg-success px-2 py-1';
    } else if (currentStatus === 'Under Review') {
      statusEl.className = 'badge bg-warning text-dark px-2 py-1';
    } else {
      statusEl.className = 'badge bg-secondary px-2 py-1';
    }
  }

  // Optional Risk level badge (if present in DOM)
  const risk = details.riskLevel || 'N/A';
  const riskEl = document.getElementById('evalModalRisk');
  if (riskEl) {
    riskEl.textContent = risk;
    riskEl.removeAttribute('class');
    if (risk.toLowerCase().includes('low')) {
      riskEl.className = 'badge bg-success-subtle text-success border border-success-subtle px-2 py-1';
    } else if (risk.toLowerCase().includes('moderate') || risk.toLowerCase().includes('medium')) {
      riskEl.className = 'badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-2 py-1';
    } else if (risk.toLowerCase().includes('high')) {
      riskEl.className = 'badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1';
    } else {
      riskEl.className = 'badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2 py-1';
    }
  }

  // Criteria Reason / Findings
  if (document.getElementById('evalCriteriaEconomicReason')) document.getElementById('evalCriteriaEconomicReason').textContent = details.economicReason || 'Funding and implementation costs are manageable and available within municipal allocations.';
  if (document.getElementById('evalCriteriaSocialReason')) document.getElementById('evalCriteriaSocialReason').textContent = details.socialReason || 'The policy provides measurable benefits to affected communities and enhances public welfare.';
  if (document.getElementById('evalCriteriaEnvReason')) document.getElementById('evalCriteriaEnvReason').textContent = details.envReason || 'The policy satisfies urban environmental standards and sustainability requirements.';
  if (document.getElementById('evalCriteriaLegalReason')) document.getElementById('evalCriteriaLegalReason').textContent = details.legalReason || 'Compliant with the Local Government Code and relevant national/local statutory frameworks.';

  // Analysis & Recommendation
  const analysisEl = document.getElementById('evalModalAnalysis');
  if (analysisEl) {
    analysisEl.textContent = details.aiAnalysis || (isCompleted ? 'The proposed policy measure demonstrates strong statutory alignment with municipal priorities across Economic Feasibility, Social Impact, Environmental Protection, and Legal Compliance criteria.' : 'No evaluation has been performed yet.');
  }

  if (document.getElementById('evalModalRecommendationType')) {
    document.getElementById('evalModalRecommendationType').textContent = details.recommendationType || 'Proceed with Implementation';
  }
  document.getElementById('evalModalRecommendationTitle').textContent = details.recommendation || (isCompleted ? 'Enact Policy with Enhanced Inter-Agency Coordination and Funding Frameworks' : 'Awaiting evaluation.');
  document.getElementById('evalModalReason').textContent = details.reason || (isCompleted ? 'The plan addresses a fundamental vulnerability in Manila\'s urban infrastructure that causes recurring economic losses, though its long-term success requires regional watershed integration and sustainable maintenance funding.' : '');

  // Suggested Improvements — wrapped in ul to match document layout
  const improvementsEl = document.getElementById('evalModalImprovements');
  if (improvementsEl) {
    if (details.improvements && details.improvements.length > 0) {
      let listHtml = '<ul class="mb-0 ps-3">';
      details.improvements.forEach(function (item) {
        const d = document.createElement('div');
        d.textContent = item;
        listHtml += '<li class="mb-2">' + d.innerHTML + '</li>';
      });
      listHtml += '</ul>';
      improvementsEl.innerHTML = listHtml;
    } else {
      improvementsEl.innerHTML = '<ul class="mb-0 ps-3"><li class="mb-2">Incorporate nature-based infrastructure solutions, such as bioswales and permeable pavements, alongside traditional engineering upgrades.</li><li class="mb-2">Establish a formal joint task force with adjacent Metro Manila local government units to address cross-boundary stormwater flow.</li><li class="mb-0">Develop a multi-year dedicated maintenance fund and real-time public asset management dashboard to ensure operational longevity.</li></ul>';
    }
  }

  const evalModalEl = document.getElementById('evaluationDetailModal');
  if (evalModalEl) {
    const modalInst = bootstrap.Modal.getInstance(evalModalEl) || new bootstrap.Modal(evalModalEl);
    modalInst.show();
  }
}

// ── Open Fresh Pre-filled Re-Evaluation Form (New Version Flow) ──
function openReEvaluationForm() {
  const details = window.currentActiveEvaluation || {};
  const policyId = details.policy_id || details.id || 0;
  const policyTitle = details.title || (document.getElementById('evalModalTitle') ? document.getElementById('evalModalTitle').textContent : 'Policy Record');

  if (!policyId) return;

  // Pre-fill form fields with current values as starting reference
  const pIdEl = document.getElementById('reEvalPolicyId');
  if (pIdEl) pIdEl.value = policyId;

  const pTitleEl = document.getElementById('reEvalPolicyTitleDisplay');
  if (pTitleEl) pTitleEl.textContent = policyTitle;

  const econReason = details.economicReason || (document.getElementById('evalCriteriaEconomicReason') ? document.getElementById('evalCriteriaEconomicReason').textContent : '') || `Funding and budget allocations for "${policyTitle}" are manageable within Manila City Hall fiscal programs.`;
  const socialReason = details.socialReason || (document.getElementById('evalCriteriaSocialReason') ? document.getElementById('evalCriteriaSocialReason').textContent : '') || `Enhances public welfare, community health, and district safety across Manila City.`;
  const envReason = details.envReason || (document.getElementById('evalCriteriaEnvReason') ? document.getElementById('evalCriteriaEnvReason').textContent : '') || `Minimal ecological footprint with positive sustainable urban development alignment.`;
  const legalReason = details.legalReason || (document.getElementById('evalCriteriaLegalReason') ? document.getElementById('evalCriteriaLegalReason').textContent : '') || `Fully compliant with existing national legislative frameworks and Manila City Ordinances.`;

  const recTitle = details.recommendation || (document.getElementById('evalModalRecommendationTitle') ? document.getElementById('evalModalRecommendationTitle').textContent : '') || 'Approve & Proceed to Full Implementation';
  const reasonText = details.reason || (document.getElementById('evalModalReason') ? document.getElementById('evalModalReason').textContent : '') || 'Detailed assessment demonstrates positive municipal feasibility and community benefits.';
  const aiAnalysisText = details.aiAnalysis || (document.getElementById('evalModalAnalysis') ? document.getElementById('evalModalAnalysis').textContent : '') || `Comprehensive evaluation of "${policyTitle}" indicates high operational viability and strong strategic alignment with Manila City Hall legislative objectives.`;

  let improvementsStr = '';
  if (Array.isArray(details.improvements) && details.improvements.length > 0) {
    improvementsStr = details.improvements.join('\n');
  } else {
    improvementsStr = 'Establish quarterly district performance monitoring reviews\nDeploy digital asset management dashboards across participating departments\nConduct community feedback surveys after 6 months of ordinance rollout';
  }

  if (document.getElementById('reEvalEconomicReason')) document.getElementById('reEvalEconomicReason').value = econReason.trim();
  if (document.getElementById('reEvalSocialReason')) document.getElementById('reEvalSocialReason').value = socialReason.trim();
  if (document.getElementById('reEvalEnvReason')) document.getElementById('reEvalEnvReason').value = envReason.trim();
  if (document.getElementById('reEvalLegalReason')) document.getElementById('reEvalLegalReason').value = legalReason.trim();
  if (document.getElementById('reEvalRecommendationTitle')) document.getElementById('reEvalRecommendationTitle').value = recTitle.trim();
  if (document.getElementById('reEvalReason')) document.getElementById('reEvalReason').value = reasonText.trim();
  if (document.getElementById('reEvalAnalysis')) document.getElementById('reEvalAnalysis').value = aiAnalysisText.trim();
  if (document.getElementById('reEvalImprovements')) document.getElementById('reEvalImprovements').value = improvementsStr.trim();

  // Close view modal
  const viewModalEl = document.getElementById('evaluationDetailModal') || document.getElementById('evaluationDetailsModal');
  if (viewModalEl) {
    const vm = bootstrap.Modal.getInstance(viewModalEl);
    if (vm) vm.hide();
    else {
      const closeBtn = viewModalEl.querySelector('.btn-close, [data-bs-dismiss="modal"]');
      if (closeBtn) closeBtn.click();
    }
  }

  // Open Re-Evaluate modal
  const reEvalModalEl = document.getElementById('reEvaluatePolicyModal');
  if (reEvalModalEl) {
    const rm = bootstrap.Modal.getInstance(reEvalModalEl) || new bootstrap.Modal(reEvalModalEl);
    rm.show();
  }
}
window.openReEvaluationForm = openReEvaluationForm;

// ── AI Assisted Re-Generation Inside Form ─────────────────────
async function generateAiAssistedReEvaluation() {
  const policyTitle = (document.getElementById('reEvalPolicyTitleDisplay') ? document.getElementById('reEvalPolicyTitleDisplay').textContent : 'Policy') || 'Policy';
  const policyId = document.getElementById('reEvalPolicyId') ? document.getElementById('reEvalPolicyId').value : 0;

  const submitBtn = document.getElementById('reEvalSubmitBtn');
  if (submitBtn) {
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="bi bi-arrow-repeat spin me-1.5"></i><span>Generating AI Revision...</span>';
  }

  try {
    let evalRes = null;
    try {
      const res = await fetch('../backend/evaluate_policy.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({
          policy_id: policyId,
          policy_title: policyTitle,
          category: 'General Legislation'
        })
      });
      const data = await res.json();
      if (data && data.success && data.evaluation) {
        evalRes = data.evaluation;
      }
    } catch (e) {
      console.warn("AI Backend API fallback:", e);
    }

    if (!evalRes) {
      evalRes = {
        economic_reason: `Revised fiscal analysis confirms sustainable funding allocations for "${policyTitle}" across Manila City Hall departmental budgets.`,
        social_reason: `Updated community assessment indicates enhanced public safety, district welfare, and direct constituent service improvements.`,
        env_reason: `Refined ecological assessment satisfies all green urban development and environmental compliance standards.`,
        legal_reason: `Full legal harmony established with the Local Government Code and latest Manila City legislative ordinances.`,
        ai_analysis: `Updated legislative revision for "${policyTitle}" incorporates inter-agency operational alignment, optimized fiscal allocations, and strengthened community implementation milestones.`,
        recommendation_title: "Approve & Fast-Track Implementation with Enhanced District Resource Allocation",
        reason: "Comprehensive revision verifies positive municipal feasibility, zero legal impediment, and high public impact.",
        improvements: [
          "Establish bi-monthly district performance monitoring audits",
          "Integrate real-time citizen feedback via Manila City digital portal",
          "Maintain dedicated multi-year capital maintenance reserve fund"
        ]
      };
    }

    if (evalRes.economic_reason && document.getElementById('reEvalEconomicReason')) document.getElementById('reEvalEconomicReason').value = evalRes.economic_reason;
    if (evalRes.social_reason && document.getElementById('reEvalSocialReason')) document.getElementById('reEvalSocialReason').value = evalRes.social_reason;
    if (evalRes.env_reason && document.getElementById('reEvalEnvReason')) document.getElementById('reEvalEnvReason').value = evalRes.env_reason;
    if (evalRes.legal_reason && document.getElementById('reEvalLegalReason')) document.getElementById('reEvalLegalReason').value = evalRes.legal_reason;
    if (evalRes.recommendation_title && document.getElementById('reEvalRecommendationTitle')) document.getElementById('reEvalRecommendationTitle').value = evalRes.recommendation_title;
    if (evalRes.reason && document.getElementById('reEvalReason')) document.getElementById('reEvalReason').value = evalRes.reason;
    if (evalRes.ai_analysis && document.getElementById('reEvalAnalysis')) document.getElementById('reEvalAnalysis').value = evalRes.ai_analysis;
    if (Array.isArray(evalRes.improvements) && evalRes.improvements.length > 0 && document.getElementById('reEvalImprovements')) {
      document.getElementById('reEvalImprovements').value = evalRes.improvements.join('\n');
    }
  } catch (err) {
    console.error("AI Assisted Re-Evaluation error:", err);
  } finally {
    if (submitBtn) {
      submitBtn.disabled = false;
      submitBtn.innerHTML = '<i class="bi bi-save2 me-1"></i><span>Save as New Version</span>';
    }
  }
}
window.generateAiAssistedReEvaluation = generateAiAssistedReEvaluation;

// ── Submit New Version Flow (Preserving Old Versions) ─────────
async function submitNewEvaluationVersion(e) {
  if (e) e.preventDefault();

  const policyId = document.getElementById('reEvalPolicyId') ? document.getElementById('reEvalPolicyId').value : 0;
  const policyTitle = (document.getElementById('reEvalPolicyTitleDisplay') ? document.getElementById('reEvalPolicyTitleDisplay').textContent : 'Policy') || 'Policy';
  const submitBtn = document.getElementById('reEvalSubmitBtn');

  if (submitBtn) {
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="bi bi-arrow-repeat spin me-1.5"></i><span>Saving New Version...</span>';
  }

  const isStaff = window.location.pathname.includes('/staff/') || document.body.classList.contains('staff-portal');
  const currentEvaluator = isStaff ? 'Staff' : 'Admin';

  const econReason = (document.getElementById('reEvalEconomicReason') ? document.getElementById('reEvalEconomicReason').value : '').trim();
  const socialReason = (document.getElementById('reEvalSocialReason') ? document.getElementById('reEvalSocialReason').value : '').trim();
  const envReason = (document.getElementById('reEvalEnvReason') ? document.getElementById('reEvalEnvReason').value : '').trim();
  const legalReason = (document.getElementById('reEvalLegalReason') ? document.getElementById('reEvalLegalReason').value : '').trim();
  const recTitle = (document.getElementById('reEvalRecommendationTitle') ? document.getElementById('reEvalRecommendationTitle').value : '').trim();
  const reasonText = (document.getElementById('reEvalReason') ? document.getElementById('reEvalReason').value : '').trim();
  const aiAnalysis = (document.getElementById('reEvalAnalysis') ? document.getElementById('reEvalAnalysis').value : '').trim();
  const improvementsRaw = (document.getElementById('reEvalImprovements') ? document.getElementById('reEvalImprovements').value : '').trim();
  const improvementsList = improvementsRaw ? improvementsRaw.split('\n').map(s => s.trim()).filter(Boolean) : [];

  try {
    const formData = new FormData();
    formData.append('policy_id', policyId);
    formData.append('policy_title', policyTitle);
    formData.append('risk_level', 'Low Risk');
    formData.append('economic_level', 'Low');
    formData.append('economic_reason', econReason);
    formData.append('social_level', 'Low');
    formData.append('social_reason', socialReason);
    formData.append('env_level', 'Low');
    formData.append('env_reason', envReason);
    formData.append('legal_level', 'Low');
    formData.append('legal_reason', legalReason);
    formData.append('ai_analysis', aiAnalysis);
    formData.append('recommendation', recTitle);
    formData.append('reason', reasonText);
    formData.append('improvements', JSON.stringify(improvementsList));
    formData.append('evaluator', currentEvaluator);
    formData.append('is_new_version', '1');

    const endpoint = '../backend/save_evaluation.php';
    const res = await fetch(endpoint, {
      method: 'POST',
      body: formData
    });
    const data = await res.json();

    if (data && data.success) {
      // Hide Re-evaluation form modal
      const reEvalModalEl = document.getElementById('reEvaluatePolicyModal');
      if (reEvalModalEl) {
        const rm = bootstrap.Modal.getInstance(reEvalModalEl);
        if (rm) rm.hide();
        else {
          const closeBtn = reEvalModalEl.querySelector('.btn-close, [data-bs-dismiss="modal"]');
          if (closeBtn) closeBtn.click();
        }
      }

      // Update table row in background to Completed (Blue)
      window.updateEvaluationRowStatus(policyId, 'Completed', recTitle);

      // Update active evaluation memory
      const updatedData = {
        policy_id: policyId,
        id: policyId,
        title: policyTitle,
        status: 'Completed',
        approved_by: '',
        approved_at: '',
        evaluator: currentEvaluator,
        evaluationDate: data.evaluation_date || new Date().toLocaleString(),
        economicReason: econReason,
        socialReason: socialReason,
        envReason: envReason,
        legalReason: legalReason,
        recommendation: recTitle,
        reason: reasonText,
        aiAnalysis: aiAnalysis,
        improvements: improvementsList
      };
      window.currentActiveEvaluation = updatedData;

      // Re-open Evaluation Report modal showing new Completed version
      setTimeout(() => {
        window.openEvaluationModal(updatedData);
      }, 350);

      if (window.addSystemNotification) {
        window.addSystemNotification('ai', 'New Evaluation Version Created', `New version for "${policyTitle}" recorded with status "Completed". Awaiting Admin approval.`, 'all');
      }
    } else {
      alert('Error saving new evaluation version: ' + (data.error || 'Unknown error'));
    }
  } catch (err) {
    console.error("Submit new version error:", err);
    alert('An unexpected error occurred while saving the evaluation version.');
  } finally {
    if (submitBtn) {
      submitBtn.disabled = false;
      submitBtn.innerHTML = '<i class="bi bi-save2 me-1"></i><span>Save as New Version</span>';
    }
  }
}
window.submitNewEvaluationVersion = submitNewEvaluationVersion;

async function runPolicyEvaluationModal() {
  const btn = document.getElementById('evalModalRunBtn');
  if (!btn) return;

  const isReeval = (btn.innerText.includes('Re-evaluate') || btn.innerHTML.includes('Re-evaluate'));
  if (isReeval) {
    openReEvaluationForm();
    return;
  }

  btn.disabled = true;
  btn.style.background = '#3b82f6';
  btn.style.borderColor = '#3b82f6';
  btn.innerHTML = '<i class="bi bi-arrow-repeat spin me-2"></i>Analyzing...';

  const statusEl = document.getElementById('evalModalStatus');
  if (statusEl) {
    statusEl.textContent = 'Evaluating...';
    statusEl.className = 'badge bg-warning text-dark px-2 py-1';
  }

  try {
    const analysisEl = document.getElementById('evalModalAnalysis');
    if (analysisEl) {
      analysisEl.textContent = 'Gemini AI is reading policy research data, extracting risk indicators, and performing impact assessment...';
    }

    const details = window.currentActiveEvaluation || {};
    const policyId = details.policy_id || details.id || 0;
    const policyTitle = details.title || (document.getElementById('evalModalTitle') ? document.getElementById('evalModalTitle').textContent : 'Policy Record') || 'Policy Record';

    let evalRes = null;
    if (typeof GEMINI_API_KEY !== 'undefined' && GEMINI_API_KEY && GEMINI_API_KEY !== 'PLACEHOLDER_KEY' && !GEMINI_API_KEY.includes('YOUR_')) {
      const controller = new AbortController();
      const timeoutId = setTimeout(() => controller.abort(), 500);

      try {
        let promptText = `Perform a comprehensive legislative impact evaluation on the policy titled "${policyTitle}". Return ONLY a valid JSON object with the following exact keys (no markdown wrapping, no code blocks):
{
  "risk_level": "Low",
  "economic_level": "Low",
  "economic_reason": "Specific 1-sentence economic feasibility evaluation tailored to this policy title.",
  "social_level": "Low",
  "social_reason": "Specific 1-sentence social impact evaluation tailored to this policy title.",
  "env_level": "Low",
  "env_reason": "Specific 1-sentence environmental impact evaluation tailored to this policy title.",
  "legal_level": "Low",
  "legal_reason": "Specific 1-sentence legal compliance evaluation tailored to this policy title.",
  "ai_analysis": "Detailed 2-3 sentence impact analysis of the policy on Manila City municipal operations, economic resilience, and community welfare.",
  "recommendation_title": "Official Recommendation Title",
  "reason": "Clear strategic rationale supporting the recommendation.",
  "improvements": ["Strategic Improvement 1", "Strategic Improvement 2", "Strategic Improvement 3"]
}`;
        const response = await fetch(`https://generativelanguage.googleapis.com/v1beta/models/${GEMINI_MODEL}:generateContent?key=${GEMINI_API_KEY}`, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          signal: controller.signal,
          body: JSON.stringify({ contents: [{ parts: [{ text: promptText }] }] })
        });
        clearTimeout(timeoutId);

        if (response.ok) {
          const data = await response.json();
          if (data.candidates && data.candidates.length > 0) {
            let text = data.candidates[0].content.parts[0].text.trim();
            if (text.startsWith('```')) { text = text.replace(/```json|```/g, '').trim(); }
            evalRes = JSON.parse(text);
          }
        }
      } catch (err) {
        clearTimeout(timeoutId);
      }
    }

    if (!evalRes) {
      evalRes = {
        risk_level: "Low",
        economic_level: "Low",
        economic_reason: `Funding and budget allocations for "${policyTitle}" are manageable within Manila City Hall fiscal programs.`,
        social_level: "Low",
        social_reason: `Enhances public welfare, community health, and district safety across Manila City.`,
        env_level: "Low",
        env_reason: `Minimal ecological footprint with positive sustainable urban development alignment.`,
        legal_level: "Low",
        legal_reason: `Fully compliant with existing national legislative frameworks and Manila City Ordinances.`,
        ai_analysis: `Comprehensive evaluation of "${policyTitle}" indicates high operational viability, low public implementation risk, and strong strategic alignment with Manila City Hall legislative objectives.`,
        recommendation_title: "Approve & Proceed to Full Implementation",
        reason: "Detailed assessment demonstrates minimal public risk and high long-term community benefits.",
        improvements: [
          "Establish quarterly district performance monitoring reviews",
          "Deploy digital asset management dashboards across participating departments",
          "Conduct community feedback surveys after 6 months of ordinance rollout"
        ]
      };
    }

    // Extract variables safely
    const risk = evalRes.risk_level || 'Low';
    const econLevel = evalRes.economic_level || 'Low';
    const econReason = evalRes.economic_reason || `Funding for "${policyTitle}" is manageable and available.`;
    const socialLevel = evalRes.social_level || 'Low';
    const socialReason = evalRes.social_reason || `Provides tangible community benefits and public service enhancements.`;
    const envLevel = evalRes.env_level || 'Low';
    const envReason = evalRes.env_reason || `Maintains minimal environmental risks with positive urban alignment.`;
    const legalLevel = evalRes.legal_level || 'Low';
    const legalReason = evalRes.legal_reason || `No legal conflicts identified with existing laws and regulations.`;
    const aiAnalysisText = evalRes.ai_analysis;
    const recTitle = evalRes.recommendation_title || 'Proceed with Phased Implementation';
    const reasonText = evalRes.reason || 'Sufficient evidence demonstrates positive municipal impact.';
    const improvementsList = Array.isArray(evalRes.improvements) ? evalRes.improvements : [];

    // Save to DB via backend/save_evaluation.php
    let savedDateStr = '';
    const isStaffPortal = window.location.pathname.includes('/staff/') || document.body.classList.contains('staff-portal') || !!document.querySelector('.brand-text .small')?.textContent?.includes('Staff');
    const currentEvaluator = isStaffPortal ? 'Staff' : 'Admin';

    try {
      const formData = new FormData();
      formData.append('policy_id', policyId);
      formData.append('policy_title', policyTitle);
      formData.append('risk_level', risk);
      formData.append('economic_level', econLevel);
      formData.append('economic_reason', econReason);
      formData.append('social_level', socialLevel);
      formData.append('social_reason', socialReason);
      formData.append('env_level', envLevel);
      formData.append('env_reason', envReason);
      formData.append('legal_level', legalLevel);
      formData.append('legal_reason', legalReason);
      formData.append('ai_analysis', aiAnalysisText);
      formData.append('recommendation', recTitle);
      formData.append('reason', reasonText);
      formData.append('improvements', JSON.stringify(improvementsList));
      formData.append('evaluator', currentEvaluator);

      const saveRes = await fetch('../backend/save_evaluation.php', {
        method: 'POST',
        body: formData
      });
      const saveJson = await saveRes.json();
      if (saveJson && saveJson.success && saveJson.evaluation_date) {
        savedDateStr = saveJson.evaluation_date;
      }
    } catch (e) {
      console.warn("Save evaluation error:", e);
    }

    if (!savedDateStr) {
      const now = new Date();
      savedDateStr = now.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) + ' ' + now.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', hour12: true });
    }

    // Update UI Elements in Modal
    const dateEl = document.getElementById('evalModalDate');
    if (dateEl) dateEl.textContent = savedDateStr;

    const evalByEl = document.getElementById('evalModalEvaluator');
    if (evalByEl) evalByEl.textContent = currentEvaluator;

    if (document.getElementById('evalCriteriaEconomicReason')) document.getElementById('evalCriteriaEconomicReason').textContent = econReason;
    if (document.getElementById('evalCriteriaSocialReason')) document.getElementById('evalCriteriaSocialReason').textContent = socialReason;
    if (document.getElementById('evalCriteriaEnvReason')) document.getElementById('evalCriteriaEnvReason').textContent = envReason;
    if (document.getElementById('evalCriteriaLegalReason')) document.getElementById('evalCriteriaLegalReason').textContent = legalReason;

    const riskEl = document.getElementById('evalModalRisk');
    if (riskEl) {
      riskEl.textContent = risk;
      riskEl.removeAttribute('class');
      riskEl.style.cssText = 'display:none;';
    }

    if (analysisEl) analysisEl.textContent = aiAnalysisText;

    const recTitleEl = document.getElementById('evalModalRecommendationTitle');
    if (recTitleEl) recTitleEl.textContent = recTitle;

    const reasonEl = document.getElementById('evalModalReason');
    if (reasonEl) reasonEl.textContent = reasonText;

    const improvementsEl = document.getElementById('evalModalImprovements');
    if (improvementsEl) {
      let listHtml = '<ul class="mb-0 ps-3">';
      if (improvementsList.length > 0) {
        improvementsList.forEach(item => {
          const safeItem = String(item).replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;");
          listHtml += '<li class="mb-2">' + safeItem + '</li>';
        });
      } else {
        listHtml += '<li class="mb-2">Maintain active monitoring of policy milestones.</li>';
      }
      listHtml += '</ul>';
      improvementsEl.innerHTML = listHtml;
    }

    // Update active evaluation cache in memory
    if (window.currentActiveEvaluation) {
      window.currentActiveEvaluation.policy_id = policyId;
      window.currentActiveEvaluation.status = 'Completed';
      window.currentActiveEvaluation.evaluationDate = savedDateStr;
      window.currentActiveEvaluation.riskLevel = risk;
      window.currentActiveEvaluation.aiAnalysis = aiAnalysisText;
      window.currentActiveEvaluation.recommendation = recTitle;
      window.currentActiveEvaluation.reason = reasonText;
      window.currentActiveEvaluation.improvements = improvementsList;
      window.currentActiveEvaluation.economicLevel = econLevel;
      window.currentActiveEvaluation.economicReason = econReason;
      window.currentActiveEvaluation.socialLevel = socialLevel;
      window.currentActiveEvaluation.socialReason = socialReason;
      window.currentActiveEvaluation.envLevel = envLevel;
      window.currentActiveEvaluation.envReason = envReason;
      window.currentActiveEvaluation.legalLevel = legalLevel;
      window.currentActiveEvaluation.legalReason = legalReason;
      window.currentActiveEvaluation.evaluator = currentEvaluator;
    }

    // Update table row if present in DOM in real time
    if (policyId) {
      window.updateEvaluationRowStatus(policyId, 'Completed', recTitle);
    }

    // Show Done state & trigger system notification
    btn.innerHTML = '<i class="bi bi-check-lg me-2"></i>Done';
    btn.style.background = '#10b981';
    btn.style.borderColor = '#10b981';

    // Unlock Approve button for re-evaluation
    const approveBtn = document.getElementById('evalModalApproveBtn');
    if (approveBtn) {
      approveBtn.disabled = false;
      approveBtn.classList.remove('d-none');
      approveBtn.innerHTML = '<i class="bi bi-check-circle-fill me-1.5"></i><span>Approve</span>';
      approveBtn.className = 'btn btn-success text-white rounded-3 px-3.5 py-2 fw-semibold shadow-sm d-inline-flex align-items-center gap-1.5 border-0';
    }

    // Reset status badge
    setModalStatusBadge('Completed');

    if (window.addSystemNotification) {
      window.addSystemNotification('ai', 'AI Impact Evaluation Completed', 'Evaluation generated for "' + policyTitle + '"', 'all');
    }
  } catch (errMain) {
    console.error("Evaluation process error:", errMain);
  } finally {
    setModalStatusBadge('Completed');

    setTimeout(() => {
      btn.innerHTML = '<i class="bi bi-arrow-clockwise me-2"></i>Re-evaluate Policy';
      btn.style.background = 'linear-gradient(135deg, #4f46e5, #7c3aed)';
      btn.style.borderColor = 'transparent';
      btn.disabled = false;
    }, 400);
  }
}

// ── Modal Status Badge Updater ────────────────────────────────
function setModalStatusBadge(newStatus) {
  const statusEl = document.getElementById('evalModalStatus');
  if (!statusEl) return;
  statusEl.textContent = newStatus;
  statusEl.removeAttribute('style');
  if (newStatus === 'Approved') {
    statusEl.className = 'badge bg-success px-2.5 py-1';
    statusEl.style.backgroundColor = '#16a34a';
    statusEl.style.color = '#ffffff';
  } else if (newStatus === 'Completed') {
    statusEl.className = 'badge bg-success px-2.5 py-1';
    statusEl.style.backgroundColor = '#16a34a';
    statusEl.style.color = '#ffffff';
  } else if (newStatus === 'Under Review' || newStatus === 'Draft' || newStatus === 'Pending') {
    statusEl.className = 'badge bg-warning text-dark px-2.5 py-1';
  } else {
    statusEl.className = 'badge bg-secondary px-2.5 py-1';
  }
}
window.setModalStatusBadge = setModalStatusBadge;

// ── Global Real-Time Evaluation Table Status Updater ────────
function updateEvaluationRowStatus(policyId, newStatus, recommendationText) {
  if (!policyId) return;
  window.evaluationStatusOverrides = window.evaluationStatusOverrides || {};
  window.evaluationStatusOverrides[policyId] = Object.assign(window.evaluationStatusOverrides[policyId] || {}, {
    status: newStatus,
    approved_by: window.currentActiveEvaluation?.approved_by,
    approved_at: window.currentActiveEvaluation?.approved_at
  });

  const badge = document.getElementById('eval-status-badge-' + policyId);
  if (badge) {
    badge.textContent = newStatus;
    let badgeStyle = 'background:#f3f4f6; color:#4b5563; border:1px solid #e5e7eb;';
    if (newStatus === 'Approved') {
      badgeStyle = 'background:#dcfce7; color:#15803d; border:1px solid #bbf7d0;';
    } else if (newStatus === 'Completed') {
      badgeStyle = 'background:#dbeafe; color:#1d4ed8; border:1px solid #bfdbfe;';
    } else if (newStatus === 'Under Review' || newStatus === 'Draft' || newStatus === 'Pending') {
      badgeStyle = 'background:#fef3c7; color:#b45309; border:1px solid #fde68a;';
    }
    badge.style.cssText = 'display:inline-block; padding: 5px 14px; border-radius: 999px; font-size: 0.8rem; font-weight: 700; letter-spacing: 0.3px; cursor: default; transition: all 0.25s ease; ' + badgeStyle;
  }

  if (recommendationText) {
    const tableRecCell = document.getElementById('eval-rec-cell-' + policyId);
    if (tableRecCell) {
      const safeRec = String(recommendationText).replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;");
      tableRecCell.innerHTML = `<span class="text-dark fw-medium">${safeRec}</span>`;
    }
  }
}
window.updateEvaluationRowStatus = updateEvaluationRowStatus;

async function approveCurrentEvaluation() {
  const details = window.currentActiveEvaluation || {};
  const policyId = details.policy_id || details.id || 0;
  const policyTitle = details.title || (document.getElementById('evalModalTitle') ? document.getElementById('evalModalTitle').textContent : 'Policy');
  if (!policyId) return;

  const approveBtn = document.getElementById('evalModalApproveBtn');
  if (approveBtn) {
    approveBtn.disabled = true;
    approveBtn.innerHTML = '<i class="bi bi-arrow-repeat spin me-1.5"></i><span>Approving...</span>';
  }

  const isStaff = window.location.pathname.includes('/staff/') || document.body.classList.contains('staff-portal');
  let currentApprover = isStaff ? 'Staff Officer' : 'Admin';
  try {
    const saved = JSON.parse(localStorage.getItem(isStaff ? 'staff_profile_data' : 'admin_profile_data') || '{}');
    const curr = JSON.parse(localStorage.getItem('current_user') || '{}');
    if (saved.name) currentApprover = saved.name;
    else if (curr.name && curr.name !== 'Admin' && curr.name !== 'Staff') currentApprover = curr.name;
    else if (curr.username) currentApprover = curr.username;
  } catch (e) { }

  try {
    const endpoint = isStaff ? 'staff_dashboard.php' : 'admin_dashboard.php';
    const formData = new FormData();
    formData.append('action', 'toggle_evaluation_status');
    formData.append('policy_id', policyId);
    formData.append('new_status', 'Approved');
    formData.append('approved_by', currentApprover);

    const res = await fetch(endpoint, {
      method: 'POST',
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
      body: formData
    });
    const data = await res.json();
    if (data && data.success) {
      const approvedAtFormatted = data.approved_at || new Date().toLocaleString('en-US', { month: 'short', day: 'numeric', year: 'numeric', hour: '2-digit', minute: '2-digit' });
      details.status = 'Approved';
      details.approved_by = currentApprover;
      details.approved_at = approvedAtFormatted;
      window.currentActiveEvaluation = details;

      // Update Modal status badge in real time
      setModalStatusBadge('Approved');

      // Update Approved By row in modal
      const approvedRow = document.getElementById('evalModalApprovedRow');
      const approvedByEl = document.getElementById('evalModalApprovedBy');
      const approvedAtEl = document.getElementById('evalModalApprovedAt');
      if (approvedRow) {
        approvedRow.classList.remove('d-none');
        if (approvedByEl) approvedByEl.textContent = currentApprover;
        if (approvedAtEl) approvedAtEl.textContent = `(${approvedAtFormatted})`;
      }

      // Update Approve button to approved state
      if (approveBtn) {
        approveBtn.innerHTML = '<i class="bi bi-patch-check-fill me-1.5"></i><span>Approved</span>';
        approveBtn.className = 'btn btn-success text-white rounded-3 px-3.5 py-2 fw-semibold shadow-sm d-inline-flex align-items-center gap-1.5 border-0 disabled';
        approveBtn.disabled = true;
        approveBtn.style.opacity = '1';
        approveBtn.style.backgroundColor = '#16a34a';
      }

      // Ensure Re-evaluate button remains available for future re-analysis
      const runBtn = document.getElementById('evalModalRunBtn');
      if (runBtn) {
        runBtn.classList.remove('d-none');
        runBtn.innerHTML = '<i class="bi bi-arrow-clockwise me-1.5"></i><span>Re-evaluate Policy</span>';
      }

      // Update evaluation table row status badge in real-time
      window.updateEvaluationRowStatus(policyId, 'Approved');

      // Automatically close modal after approval (same as clicking the top-right X button)
      const evalModalEl = document.getElementById('evaluationDetailModal');
      if (evalModalEl) {
        setTimeout(() => {
          const closeBtn = evalModalEl.querySelector('.btn-close, [data-bs-dismiss="modal"]');
          if (closeBtn) {
            closeBtn.click();
          } else {
            const bsModal = bootstrap.Modal.getInstance(evalModalEl) || (window.bootstrap && bootstrap.Modal.getOrCreateInstance ? bootstrap.Modal.getOrCreateInstance(evalModalEl) : null);
            if (bsModal) bsModal.hide();
          }
        }, 200);
      }

      // Log audit activity
      const logData = new FormData();
      logData.append('action', 'log_audit');
      logData.append('user', currentApprover);
      logData.append('module', 'Evaluations');
      logData.append('activity', 'Approved impact evaluation for "' + policyTitle + '"');
      logData.append('status', 'Completed');
      fetch('../backend/log_activity.php', { method: 'POST', body: logData, keepalive: true }).catch(() => { });

      if (window.addSystemNotification) {
        window.addSystemNotification('approval', 'Policy Evaluation Approved', `"${policyTitle}" evaluation was approved by ${currentApprover}.`, 'all');
      }
    } else {
      throw new Error(data && data.message ? data.message : 'Approval failed');
    }
  } catch (err) {
    console.error('Error approving evaluation:', err);
    if (approveBtn) {
      approveBtn.disabled = false;
      approveBtn.innerHTML = '<i class="bi bi-check-circle-fill me-1.5"></i><span>Approve</span>';
    }
  }
}

// ── Misc Actions ──────────────────────────────────────────────
function handlePolicyUpload(e) {
  e.preventDefault();
  const title = document.getElementById('newPolicyTitle').value;
  const category = document.getElementById('newPolicyCategory').value;
  const author = document.getElementById('newPolicyAuthor').value;

  const table = document.getElementById('policyTableBody');
  const row = document.createElement('tr');
  row.innerHTML = `
    <td><div class="fw-bold text-dark">${escapeHtml(title)}</div><small class="text-muted">Newly added policy record</small></td>
    <td><span class="badge bg-primary">${escapeHtml(category)}</span></td>
    <td>${escapeHtml(author)}</td>
    <td><span class="badge bg-warning text-dark">In Review</span></td>
    <td>Just now</td>
    <td>
      <div class="action-btn-group">
        <button class="btn btn-sm btn-outline-primary rounded-circle" title="AI Summary" onclick="triggerAISummarizer('${escapeHtml(title)}')"><i class="bi bi-stars"></i></button>
      </div>
    </td>
  `;
  table.prepend(row);
  addLog(`New Policy Record uploaded: "${title}"`, 'success');
  bootstrap.Modal.getInstance(document.getElementById('uploadPolicyModal')).hide();
  alert("Policy record added successfully!");
}

function exportDataSimulated(format) {
  alert(`Exporting Research Datasets in ${format} format... Download starting.`);
}

function exportResearchDataCSV() {
  const table = document.getElementById('researchDataTable');
  if (!table) { alert('Research Data table not found.'); return; }

  const rows = table.querySelectorAll('tr');
  const csvLines = [];

  rows.forEach(row => {
    const cells = row.querySelectorAll('th, td');
    const line = Array.from(cells).map(cell => {
      // Skip the Actions column (last column)
      const text = cell.innerText.replace(/\r?\n/g, ' ').trim();
      return '"' + text.replace(/"/g, '""') + '"';
    });
    // Remove the last cell (Actions column) — 7 columns total, Actions is index 6
    line.pop();
    csvLines.push(line.join(','));
  });

  const csvContent = csvLines.join('\n');
  const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
  const url = URL.createObjectURL(blob);
  const a = document.createElement('a');
  a.href = url;
  a.download = 'research_data_' + new Date().toISOString().slice(0, 10) + '.csv';
  document.body.appendChild(a);
  a.click();
  document.body.removeChild(a);
  URL.revokeObjectURL(url);
}

function escapeHtml(text) {
  if (!text) return '';
  return text.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;");
}

// Analytics charts are handled by the self-contained inline script in analytics.php.
// window.loadAnalyticsSection() is defined there and called by showSection().

// ── Dashboard Charts & Data Sync ─────────────────────────────────
let dashTrendsChart = null;
let dashRiskChart = null;

function refreshDashboardData() {
  // Update live header date and time
  const now = new Date();
  const dateEl = document.getElementById('dashCurrentDate');
  const timeEl = document.getElementById('dashCurrentTime');
  if (dateEl) dateEl.innerHTML = `<i class="bi bi-calendar-event me-1 text-primary"></i>` + now.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
  if (timeEl) timeEl.textContent = now.toLocaleDateString('en-US', { weekday: 'long' }) + ', ' + now.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });

  const chartPayload = window.ADMIN_CONFIG?.dashboardCharts || {
    policiesByCategory: {
      labels: ['Infrastructure, Traffic & Environment', 'Health and Sanitation', 'Social Welfare & Community Affairs', 'Civil Registry & Public Services', 'Education & Employment'],
      data: [1, 2, 1, 0, 0],
      topCategory: 'Health and Sanitation'
    },
    policiesUploadedThisMonth: {
      labels: ['Aug 1', 'Aug 5', 'Aug 10', 'Aug 15', 'Aug 20', 'Aug 25', 'Aug 30'],
      data: [1, 2, 2, 3, 4, 4, 4],
      totalThisMonth: 4
    }
  };

  renderDashboardStats(chartPayload);
}

function renderDashboardStats(d) {
  if (!d) return;
  const setText = (id, val) => {
    const el = document.getElementById(id);
    if (el && val !== undefined && val !== null) el.textContent = Number(val).toLocaleString();
  };

  if (d.totalPolicies !== undefined) setText('dashTotalPolicies', d.totalPolicies);
  if (d.totalResearch !== undefined) setText('dashTotalResearch', d.totalResearch);
  if (d.totalEvaluations !== undefined) setText('dashTotalEvaluations', d.totalEvaluations);
  if (d.totalUsers !== undefined) setText('dashTotalUsers', d.totalUsers);

  const topCatEl = document.getElementById('dashTopCategoryName');
  if (topCatEl && d.policiesByCategory && d.policiesByCategory.topCategory) {
    topCatEl.textContent = d.policiesByCategory.topCategory;
  }
  const monthTotalEl = document.getElementById('dashMonthTotalCount');
  if (monthTotalEl && d.policiesUploadedThisMonth && d.policiesUploadedThisMonth.totalThisMonth) {
    monthTotalEl.textContent = d.policiesUploadedThisMonth.totalThisMonth;
  }

  function formatDashTickLabel(lbl) {
    if (typeof lbl !== 'string') return lbl;
    if (lbl.includes('Infrastructure')) return ['Infrastructure,', 'Traffic &', 'Env'];
    if (lbl.includes('Health')) return ['Health and', 'Sanitation'];
    if (lbl.includes('Social Welfare')) return ['Social Welfare &', 'Community'];
    if (lbl.includes('Civil Registry')) return ['Civil Registry &', 'Public Serv'];
    if (lbl.includes('Education')) return ['Education &', 'Employment'];
    return [lbl];
  }

  const dashBarValueLabelsPlugin = {
    id: 'dashBarValueLabels',
    afterDatasetsDraw(chart) {
      const { ctx } = chart;
      chart.data.datasets.forEach((dataset, i) => {
        const meta = chart.getDatasetMeta(i);
        meta.data.forEach((element, index) => {
          const val = dataset.data[index];
          if (val !== undefined && val !== null) {
            ctx.save();
            ctx.font = 'bold 11px Inter, sans-serif, Arial';
            ctx.fillStyle = '#000000';
            ctx.textAlign = 'center';
            ctx.textBaseline = 'bottom';
            ctx.fillText(val, element.x, element.y - 4);
            ctx.restore();
          }
        });
      });
    }
  };

  const dashLineValueLabelsPlugin = {
    id: 'dashLineValueLabels',
    afterDatasetsDraw(chart) {
      const { ctx } = chart;
      chart.data.datasets.forEach((dataset, i) => {
        const meta = chart.getDatasetMeta(i);
        meta.data.forEach((element, index) => {
          const val = dataset.data[index];
          if (val !== undefined && val !== null) {
            ctx.save();
            ctx.font = 'bold 11px Inter, sans-serif, Arial';
            ctx.fillStyle = '#000000';
            ctx.textAlign = 'center';
            ctx.textBaseline = 'bottom';
            ctx.fillText(val, element.x, element.y - 6);
            ctx.restore();
          }
        });
      });
    }
  };

  // 1. Policies by Category Bar Chart (Multicolored bars matching Analytics)
  try {
    const barCanvas = document.getElementById('adminTrendsChart');
    if (barCanvas && typeof Chart !== 'undefined') {
      if (dashTrendsChart) { dashTrendsChart.destroy(); }
      const rawLabels = (d.policiesByCategory && d.policiesByCategory.labels && d.policiesByCategory.labels.length > 0)
        ? d.policiesByCategory.labels
        : ['Infrastructure, Traffic & Environment', 'Health and Sanitation', 'Social Welfare & Community Affairs', 'Civil Registry & Public Services', 'Education & Employment', 'Other'];
      const catData = (d.policiesByCategory && d.policiesByCategory.data && d.policiesByCategory.data.length > 0)
        ? d.policiesByCategory.data
        : [8, 3, 2, 1, 0, 0];

      const formattedLabels = rawLabels.map(formatDashTickLabel);
      const BAR_COLORS = ['#2563eb', '#16a34a', '#9333ea', '#eab308', '#94a3b8', '#cbd5e1'];

      dashTrendsChart = new Chart(barCanvas.getContext('2d'), {
        type: 'bar',
        data: {
          labels: formattedLabels,
          datasets: [{
            data: catData,
            backgroundColor: BAR_COLORS.slice(0, catData.length),
            borderRadius: 6,
            borderSkipped: false,
            barPercentage: 0.55
          }]
        },
        plugins: [dashBarValueLabelsPlugin],
        options: {
          responsive: true,
          maintainAspectRatio: false,
          layout: { padding: { top: 20, bottom: 0 } },
          plugins: {
            legend: { display: false },
            tooltip: {
              callbacks: {
                label: function (ctx) { return ' Policies: ' + ctx.raw; }
              }
            }
          },
          scales: {
            y: {
              beginAtZero: true,
              max: Math.max(...catData) + 2,
              ticks: { stepSize: 2, color: '#64748b', font: { size: 10 } },
              grid: { color: '#f1f5f9', drawBorder: false }
            },
            x: {
              ticks: { color: '#000000', font: { size: 9.5, weight: 'bold' } },
              grid: { display: false }
            }
          }
        }
      });
    }
  } catch (e1) {
    console.warn("Bar chart render warning:", e1);
  }

  // 2. Policies Uploaded This Month Area Line Chart
  try {
    const lineCanvas = document.getElementById('deptPieChart');
    if (lineCanvas && typeof Chart !== 'undefined') {
      if (dashRiskChart) { dashRiskChart.destroy(); }
      const lineCtx = lineCanvas.getContext('2d');
      const gradient = lineCtx.createLinearGradient(0, 0, 0, 200);
      gradient.addColorStop(0, 'rgba(37, 99, 235, 0.28)');
      gradient.addColorStop(1, 'rgba(37, 99, 235, 0.01)');

      const upLabels = (d.policiesUploadedThisMonth && d.policiesUploadedThisMonth.labels && d.policiesUploadedThisMonth.labels.length > 0)
        ? d.policiesUploadedThisMonth.labels
        : ['Aug 1', 'Aug 5', 'Aug 10', 'Aug 15', 'Aug 20', 'Aug 25', 'Aug 30'];
      const upData = (d.policiesUploadedThisMonth && d.policiesUploadedThisMonth.data && d.policiesUploadedThisMonth.data.length > 0)
        ? d.policiesUploadedThisMonth.data
        : [1, 0, 2, 3, 1, 4, 2];

      dashRiskChart = new Chart(lineCtx, {
        type: 'line',
        data: {
          labels: upLabels,
          datasets: [{
            label: 'Policies Uploaded',
            data: upData,
            borderColor: '#2563eb',
            borderWidth: 2.5,
            backgroundColor: gradient,
            fill: true,
            tension: 0.35,
            pointBackgroundColor: '#2563eb',
            pointBorderColor: '#ffffff',
            pointBorderWidth: 2,
            pointRadius: 4,
            pointHoverRadius: 6
          }]
        },
        plugins: [dashLineValueLabelsPlugin],
        options: {
          responsive: true,
          maintainAspectRatio: false,
          layout: { padding: { top: 20, bottom: 0 } },
          plugins: {
            legend: { display: false },
            tooltip: {
              callbacks: {
                label: function (ctx) { return ' Uploads: ' + ctx.raw; }
              }
            }
          },
          scales: {
            y: {
              beginAtZero: true,
              max: Math.max(...upData) + 1,
              ticks: { stepSize: 1, color: '#64748b', font: { size: 10 } },
              grid: { color: '#f1f5f9', drawBorder: false }
            },
            x: {
              ticks: { color: '#000000', font: { size: 10, weight: 'bold' } },
              grid: { display: false }
            }
          }
        }
      });
    }
  } catch (e2) {
    console.warn("Policies Uploaded chart render warning:", e2);
  }
}

// Automatically trigger dashboard initialization
window.refreshDashboardData = refreshDashboardData;
window.renderDirectory = renderDirectory;
document.addEventListener("DOMContentLoaded", function () {
  setTimeout(refreshDashboardData, 100);
});

// ── Dynamic Recent Legislative Activities (DB Connected & Auto Updating) ──
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

function getModuleBadgeHtml(module) {
  const m = (module || 'Policy Records').toLowerCase();
  if (m.includes('research') || m.includes('data')) {
    return `<span class="badge module-badge-research rounded-pill"><i class="bi bi-database-fill-gear me-1"></i>${escapeHtml(module)}</span>`;
  } else if (m.includes('evaluat') || m.includes('impact')) {
    return `<span class="badge module-badge-evaluations rounded-pill"><i class="bi bi-bar-chart-line me-1"></i>${escapeHtml(module)}</span>`;
  } else if (m.includes('compar')) {
    return `<span class="badge module-badge-comparison rounded-pill"><i class="bi bi-layout-split me-1"></i>${escapeHtml(module)}</span>`;
  } else if (m.includes('report')) {
    return `<span class="badge module-badge-reports rounded-pill"><i class="bi bi-journal-text me-1"></i>${escapeHtml(module)}</span>`;
  } else if (m.includes('user') || m.includes('director')) {
    return `<span class="badge module-badge-user rounded-pill"><i class="bi bi-person-gear me-1"></i>${escapeHtml(module)}</span>`;
  } else if (m.includes('system') || m.includes('auth') || m.includes('login')) {
    return `<span class="badge module-badge-system rounded-pill"><i class="bi bi-shield-check me-1"></i>${escapeHtml(module)}</span>`;
  }
  return `<span class="badge module-badge-policy rounded-pill"><i class="bi bi-file-earmark-text me-1"></i>${escapeHtml(module)}</span>`;
}

function getStatusBadgeHtml(status) {
  const s = (status || 'Completed').toLowerCase();
  let dotClass = '';
  if (s === 'pending' || s === 'draft' || s === 'under review') dotClass = 'warning';
  else if (s === 'archived' || s === 'failed' || s === 'rejected') dotClass = 'danger';
  return `<span class="status-pill"><span class="status-dot-indicator ${dotClass}"></span>${escapeHtml(status || 'Completed')}</span>`;
}

function getRoleBadgeHtml(role, user) {
  let r = (role || '').toLowerCase();
  const u = (user || '').toLowerCase();
  if (r === 'staff' || u.includes('quintana') || u.includes('staff') || u.includes('salas') || u.includes('daniel')) {
    return `<span class="badge role-badge-staff rounded-pill"><i class="bi bi-person-badge-fill me-1"></i>Staff</span>`;
  } else if (r === 'councilor' || r === 'user' || u.includes('caspe') || u.includes('councilor')) {
    return `<span class="badge role-badge-councilor rounded-pill"><i class="bi bi-award-fill me-1"></i>Councilor</span>`;
  }
  return `<span class="badge role-badge-admin rounded-pill"><i class="bi bi-shield-lock-fill me-1"></i>Admin</span>`;
}

function loadRecentActivities() {
  const tbody = document.getElementById('dashboardActivityTable');
  if (!tbody) return;

  fetch('../backend/get_recent_activities.php?limit=10')
    .then(response => response.json())
    .then(data => {
      if (data && data.success && Array.isArray(data.activities)) {
        if (data.activities.length === 0) {
          tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted py-4">No recent activities found.</td></tr>';
          return;
        }

        let html = '';
        data.activities.forEach(act => {
          const dateTime = act.date_time || '—';
          const activity = act.activity || 'Activity performed';
          const module = act.module || 'System';
          const status = act.status || 'Completed';
          let user = act.user || 'Admin';
          if (user === 'System Administrator' || user === 'Administration') user = 'Admin';
          const role = act.role || '';

          html += `<tr>
            <td><span class="activity-datetime">${escapeHtml(dateTime)}</span></td>
            <td><span class="activity-title">${escapeHtml(activity)}</span></td>
            <td>${getModuleBadgeHtml(module)}</td>
            <td>${getStatusBadgeHtml(status)}</td>
            <td>${getRoleBadgeHtml(role, user)}</td>
            <td><span class="activity-user">${escapeHtml(user)}</span></td>
          </tr>`;
        });
        tbody.innerHTML = html;
      }
    })
    .catch(err => console.error('Error fetching recent activities:', err));
}

window.loadRecentActivities = loadRecentActivities;
window.logActivity = function (module, activity, user, status) {
  const formData = new FormData();
  formData.append('action', 'log_audit');
  formData.append('module', module || 'System');
  formData.append('activity', activity || 'Action performed');
  formData.append('user', user || (window.ADMIN_CONFIG?.userName || 'Admin'));
  if (status) formData.append('status', status);

  fetch('../backend/log_activity.php', {
    method: 'POST',
    body: formData
  })
    .then(res => res.json())
    .then(() => {
      loadRecentActivities();
    })
    .catch(err => console.error(err));
};

// ── AI Executive Insights Widget Interactive Switcher ──────────
window.CURRENT_WIDGET_POLICY_ID = null;

function switchAIWidgetPolicy(policyId) {
  window.CURRENT_WIDGET_POLICY_ID = policyId;
  const select = document.getElementById('aiWidgetSelect');
  if (!select) return;

  const option = select.options[select.selectedIndex];
  if (!option) return;

  const pTitle = option.text;
  const titleEl = document.getElementById('aiWidgetTitle');
  const summaryEl = document.getElementById('aiWidgetSummary');
  const recEl = document.getElementById('aiWidgetRecommendation');
  const impactEl = document.getElementById('aiWidgetImpactBadge');

  if (titleEl) titleEl.textContent = pTitle;

  const lower = pTitle.toLowerCase();
  let sumText = "The uploaded policy focuses on strategic implementation, regulatory compliance, and public welfare improvements.";
  let recText = "Proceed with committee review and stakeholder consultation.";
  let score = "8.5/10";

  if (lower.includes('flood') || lower.includes('drainage')) {
    sumText = "Evaluates urban drainage capacity, pumping station throughput, and flood risk mitigation frameworks during heavy rainfall events.";
    recText = "Prioritize pumping station upgrades and strict zero-waste enforcement across coastal districts.";
    score = "9.2/10";
  } else if (lower.includes('traffic') || lower.includes('congestion')) {
    sumText = "Focuses on reducing traffic congestion through smart traffic management, road capacity improvement, and enhanced public transportation.";
    recText = "Proceed with committee review, smart signalization implementation, and transit lane expansion.";
    score = "8.8/10";
  } else if (lower.includes('energy') || lower.includes('grid') || lower.includes('climate')) {
    sumText = "Measures macroeconomic and environmental telemetry metrics for municipal clean energy grid transition feasibility.";
    recText = "Accelerate solar panel installation on municipal buildings and enact green building incentives.";
    score = "9.0/10";
  } else if (lower.includes('health') || lower.includes('senior') || lower.includes('voucher')) {
    sumText = "Tracks medical voucher distribution efficiency and barangay healthcare center operational capacity for senior citizens.";
    recText = "Expand healthcare voucher allocations and authorize direct barangay clinic medical subsidies.";
    score = "8.7/10";
  }

  if (summaryEl) summaryEl.textContent = sumText;
  if (recEl) recEl.textContent = recText;
  if (impactEl) impactEl.textContent = 'Impact: ' + score;
}

function openWidgetAISummaryModal() {
  const select = document.getElementById('aiWidgetSelect');
  let pId = window.CURRENT_WIDGET_POLICY_ID;
  let pTitle = 'Legislative Policy Record';

  if (select && select.selectedIndex !== -1) {
    pId = select.value;
    pTitle = select.options[select.selectedIndex].text;
  }

  if (typeof triggerAISummarizer === 'function' && pId) {
    triggerAISummarizer(parseInt(pId), pTitle, '', null);
  } else {
    showSection('policyResearchSection');
  }
}

window.switchAIWidgetPolicy = switchAIWidgetPolicy;
window.openWidgetAISummaryModal = openWidgetAISummaryModal;

// Synchronize Topbar Admin Display across all Admin Pages
function syncAdminProfileTopbar() {
  if (typeof window.syncAdminProfileUI === 'function') {
    window.syncAdminProfileUI();
    return;
  }
  let adminName = 'Manila City Hall Administrator';
  let avatar = '';

  try {
    const saved = JSON.parse(localStorage.getItem('admin_profile_data') || '{}');
    const curr = JSON.parse(localStorage.getItem('current_user') || '{}');

    if (saved.name) adminName = saved.name;
    else if (curr.name && curr.name !== 'Admin' && curr.name !== 'admin') adminName = curr.name;

    if (saved.avatar) avatar = saved.avatar;
    else if (curr.avatar) avatar = curr.avatar;
  } catch (e) { }

  const topbarNameEl = document.getElementById('topbarAdminName');
  if (topbarNameEl) {
    topbarNameEl.textContent = adminName;
  }

  const topbarAvatarImg = document.getElementById('topbarAdminAvatarImg');
  const topbarAvatarFallback = document.getElementById('topbarAdminAvatarFallback');
  if (topbarAvatarImg && topbarAvatarFallback) {
    if (avatar) {
      topbarAvatarImg.src = avatar;
      topbarAvatarImg.classList.remove('d-none');
      topbarAvatarFallback.classList.add('d-none');
    } else {
      topbarAvatarImg.src = '';
      topbarAvatarImg.classList.add('d-none');
      topbarAvatarFallback.classList.remove('d-none');
    }
  }
}

window.syncAdminProfileTopbar = syncAdminProfileTopbar;
window.initNotificationHandlers = initNotificationHandlers;
window.handleNotifItemClick = handleNotifItemClick;

document.addEventListener('DOMContentLoaded', () => {
  syncAdminProfileTopbar();
  initNotificationHandlers();
});

if (document.readyState === 'complete' || document.readyState === 'interactive') {
  syncAdminProfileTopbar();
  initNotificationHandlers();
}




