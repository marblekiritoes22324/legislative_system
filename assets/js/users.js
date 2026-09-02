document.addEventListener("DOMContentLoaded", function () {
    // ── Collapsible Sidebar Toggle & localStorage Persistence (User Panel) ──
    const initUserSidebarState = () => {
        const isCollapsed = localStorage.getItem('user_sidebar_collapsed') === 'true' || localStorage.getItem('admin_sidebar_collapsed') === 'true';
        if (isCollapsed) {
            document.body.classList.add('sidebar-collapsed');
            document.documentElement.classList.add('sidebar-collapsed');
        } else {
            document.body.classList.remove('sidebar-collapsed');
            document.documentElement.classList.remove('sidebar-collapsed');
        }
    };
    initUserSidebarState();

    document.addEventListener('click', function (event) {
        const toggleBtn = event.target.closest('.sidebar-toggle-btn, #sidebarToggleBtn');
        if (toggleBtn) {
            event.preventDefault();
            const isCurrentlyCollapsed = document.body.classList.contains('sidebar-collapsed');
            const newState = !isCurrentlyCollapsed;

            document.body.classList.toggle('sidebar-collapsed', newState);
            document.documentElement.classList.toggle('sidebar-collapsed', newState);
            localStorage.setItem('user_sidebar_collapsed', newState ? 'true' : 'false');
            localStorage.setItem('admin_sidebar_collapsed', newState ? 'true' : 'false');
            return;
        }
    });

    const navLinks = document.querySelectorAll('.sidebar-nav .nav-link');
    const sections = document.querySelectorAll('.content-section');
    const darkModeToggle = document.getElementById('darkModeToggle');

    // Check for login query parameters (from PHP login redirect)
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('username')) {
        const queryUser = {
            username: urlParams.get('username'),
            name: urlParams.get('name') || urlParams.get('username'),
            email: urlParams.get('email') || (urlParams.get('username') + '@manila.gov.ph'),
            position: urlParams.get('role') || 'City Councilor',
            department: urlParams.get('department') || 'City Council Secretariat',
            role: urlParams.get('role') || 'Councilor',
            status: 'approved'
        };
        localStorage.setItem('current_user', JSON.stringify(queryUser));
        // Clean URL parameters from address bar
        window.history.replaceState({}, document.title, window.location.pathname);
    }

    // Populate user info from localStorage
    const user = JSON.parse(localStorage.getItem('current_user') || '{}');
    if (user.name || user.username) {
        const displayName = user.name || user.username;
        if (document.getElementById('topbarUserName')) document.getElementById('topbarUserName').innerText = displayName;
        if (document.getElementById('topbarUserRole')) document.getElementById('topbarUserRole').innerText = user.role ? (user.role.charAt(0).toUpperCase() + user.role.slice(1)) : 'Councilor';
        if (document.getElementById('userWelcomeHeading')) document.getElementById('userWelcomeHeading').innerText = 'Welcome, ' + (displayName.startsWith('Hon.') ? displayName : 'Hon. ' + displayName);
        if (document.getElementById('profileFullName')) document.getElementById('profileFullName').value = displayName;
        if (document.getElementById('profileEmail')) document.getElementById('profileEmail').value = user.email || (user.username ? user.username + '@manila.gov.ph' : 'user@manila.gov.ph');
        if (document.getElementById('profileUsername')) document.getElementById('profileUsername').value = user.username || 'user';
    }

    // Section Switching
    const showSection = (sectionId) => {
        if (!sectionId) return;
        sections.forEach((s) => s.classList.toggle('d-none', s.id !== sectionId));
        navLinks.forEach((link) => {
            const target = link.dataset.target;
            const href = link.getAttribute('href') || '';
            link.classList.toggle('active', target === sectionId || href.includes(sectionId));
        });

        if (sectionId === 'analyticsSection') {
            if (typeof window.loadUserAnalytics === 'function') {
                window.loadUserAnalytics();
            } else if (typeof initUserCharts === 'function') {
                initUserCharts();
            }
        }

        if (sectionId === 'userDashboardSection') {
            setTimeout(function () {
                if (typeof window.initUserDashboardChart === 'function') window.initUserDashboardChart();
            }, 60);
        }

        try {
            sessionStorage.setItem('user_active_section', sectionId);
            const url = new URL(window.location.href);
            url.searchParams.set('section', sectionId);
            window.history.replaceState({}, '', url);
        } catch (e) { }
    };

    window.showSection = showSection;

    navLinks.forEach((link) => {
        link.addEventListener('click', (e) => {
            if (link.id === 'sidebarLogoutBtn') return handleLogout(e);
            e.preventDefault();
            const target = link.dataset.target;
            if (target) showSection(target);
        });
    });

    // Dark Mode toggle
    const headerDarkModeCheckbox = document.getElementById('headerDarkModeCheckbox');
    const applyUserTheme = (mode) => {
        const isDark = mode === 'dark';
        document.body.classList.toggle('dark-mode', isDark);
        if (headerDarkModeCheckbox) {
            headerDarkModeCheckbox.checked = isDark;
        }
        if (darkModeToggle) {
            const icon = darkModeToggle.querySelector('i');
            if (icon) {
                icon.className = isDark ? 'bi bi-sun-fill' : 'bi bi-moon-fill';
            }
        }
    };
    applyUserTheme(localStorage.getItem('user_theme') || localStorage.getItem('admin_theme') || 'light');

    if (headerDarkModeCheckbox) {
        headerDarkModeCheckbox.addEventListener('change', () => {
            const next = headerDarkModeCheckbox.checked ? 'dark' : 'light';
            localStorage.setItem('user_theme', next);
            localStorage.setItem('admin_theme', next);
            applyUserTheme(next);
        });
    }

    if (darkModeToggle) {
        darkModeToggle.addEventListener('click', () => {
            const next = document.body.classList.contains('dark-mode') ? 'light' : 'dark';
            localStorage.setItem('user_theme', next);
            localStorage.setItem('admin_theme', next);
            applyUserTheme(next);
        });
    }

    // Logout handlers
    let isUserLoggingOut = false;
    const handleUserLogout = (e) => {
        if (e && e.preventDefault) e.preventDefault();
        if (isUserLoggingOut) return;
        isUserLoggingOut = true;

        let userName = 'User';
        try {
            const curr = JSON.parse(localStorage.getItem('current_user') || '{}');
            if (curr.name && curr.name !== 'User' && curr.name !== 'user') userName = curr.name;
            else if (curr.username) userName = curr.username;
        } catch (err) { }

        const formData = new FormData();
        formData.append('action', 'log_audit');
        formData.append('user', userName);
        formData.append('module', 'System');
        formData.append('activity', 'User logout');
        formData.append('status', 'Completed');

        localStorage.removeItem('user_logged_in');
        localStorage.removeItem('current_user');
        sessionStorage.clear();

        try {
            navigator.sendBeacon('../backend/log_activity.php', formData);
        } catch (err) {
            fetch('../backend/log_activity.php', { method: 'POST', body: formData, keepalive: true }).catch(() => { });
        }

        window.location.href = '../auth/logout.php?user=' + encodeURIComponent(userName);
    };

    window.handleUserLogout = handleUserLogout;

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

    document.querySelectorAll('#userProfileDropdown + .dropdown-menu [data-target]').forEach((item) => {
        item.addEventListener('click', (e) => {
            e.preventDefault();
            const target = item.dataset.target;
            if (target) showSection(target);
        });
    });

    // Auto-open section from URL ?section= param or sessionStorage
    const urlSectionParam = new URLSearchParams(window.location.search).get('section');
    const activeSec = urlSectionParam || sessionStorage.getItem('user_active_section') || 'userDashboardSection';
    showSection(activeSec);

    if (window.syncUserProfileUI) window.syncUserProfileUI();
});

// ── Notifications: live unread tracking for User / Councilor ──
function initUserNotificationHandlers() {
    const userNotifBtn = document.getElementById('userNotifButton');
    const badge = document.getElementById('userNotifBadge');
    const count = document.getElementById('userNotifUnread');
    const headerBadge = document.getElementById('userNotifHeaderBadge');
    const items = document.querySelectorAll('#userNotifList .notif-item');

    if (!userNotifBtn) return;

    const latestId = parseInt(userNotifBtn.dataset.latestId || '0', 10);
    const lastSeenId = parseInt(localStorage.getItem('user_last_seen_notif_id') || '0', 10);

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

    userNotifBtn.addEventListener('click', function () {
        markAllUserNotifsRead();
    });
}

function markAllUserNotifsRead(event) {
    if (event) event.preventDefault();
    const userNotifBtn = document.getElementById('userNotifButton');
    const latestId = userNotifBtn ? parseInt(userNotifBtn.dataset.latestId || '0', 10) : Date.now();
    localStorage.setItem('user_last_seen_notif_id', latestId.toString());

    const badge = document.getElementById('userNotifBadge');
    if (badge) {
        badge.style.transition = 'opacity 0.2s ease, transform 0.2s ease';
        badge.style.opacity = '0';
        badge.style.transform = 'scale(0.3)';
        setTimeout(() => { badge.style.display = 'none'; }, 200);
    }
    const headerBadge = document.getElementById('userNotifHeaderBadge');
    if (headerBadge) {
        headerBadge.textContent = '0 New';
        headerBadge.className = 'badge rounded-pill bg-secondary text-white';
    }
    const count = document.getElementById('userNotifUnread');
    if (count) count.textContent = '0';
    document.querySelectorAll('#userNotifList .notif-dot').forEach((d) => {
        d.style.background = '#94A3B8';
        d.style.opacity = '0.35';
        d.style.boxShadow = 'none';
    });
}

function handleUserNotifItemClick(sectionId, notifId) {
    markAllUserNotifsRead();
    const userNotifBtn = document.getElementById('userNotifButton');
    if (userNotifBtn && window.bootstrap && bootstrap.Dropdown) {
        const inst = bootstrap.Dropdown.getInstance(userNotifBtn);
        if (inst) inst.hide();
    }
    if (typeof showSection === 'function' && sectionId) {
        showSection(sectionId);
    }
}

document.addEventListener('DOMContentLoaded', initUserNotificationHandlers);
if (document.readyState === 'complete' || document.readyState === 'interactive') {
    initUserNotificationHandlers();
}

window.markAllUserNotifsRead = markAllUserNotifsRead;
window.handleUserNotifItemClick = handleUserNotifItemClick;

// Modal Helper Triggers
function viewPolicyDetails(title, category, date, desc) {
    if (document.getElementById('modalPolicyTitle')) document.getElementById('modalPolicyTitle').innerText = title;
    if (document.getElementById('modalPolicyCategory')) document.getElementById('modalPolicyCategory').innerText = category;
    if (document.getElementById('modalPolicyDate')) document.getElementById('modalPolicyDate').innerText = date;
    if (document.getElementById('modalPolicyDesc')) document.getElementById('modalPolicyDesc').innerText = desc;
    const modalEl = document.getElementById('policyDetailModal');
    if (modalEl) {
        const modal = new bootstrap.Modal(modalEl);
        modal.show();
    }
}

// Open Policy View Modal with full details object
window.openPolicyViewModal = function (policy) {
    if (document.getElementById('modalPolicyTitle')) document.getElementById('modalPolicyTitle').innerText = policy.title || '';
    if (document.getElementById('modalPolicyCategory')) document.getElementById('modalPolicyCategory').innerText = policy.category || '';
    if (document.getElementById('modalPolicyStatus')) document.getElementById('modalPolicyStatus').innerText = policy.status || '';
    if (document.getElementById('modalPolicyAuthor')) document.getElementById('modalPolicyAuthor').innerText = policy.author || 'N/A';
    if (document.getElementById('modalPolicyDate')) document.getElementById('modalPolicyDate').innerText = policy.date || 'N/A';
    if (document.getElementById('modalPolicyDesc')) document.getElementById('modalPolicyDesc').innerText = policy.desc || 'No description available.';

    const fileWrapper = document.getElementById('modalPolicyFileWrapper');
    const fileLink = document.getElementById('modalPolicyFileLink');
    const downloadBtn = document.getElementById('modalDownloadBtn');

    if (policy.file && policy.file.trim() !== '') {
        const filePath = '../assets/uploads/policies/' + policy.file;
        if (fileWrapper) fileWrapper.style.display = '';
        if (fileLink) fileLink.href = filePath;
        if (downloadBtn) {
            downloadBtn.href = filePath;
            downloadBtn.style.display = '';
        }
    } else {
        if (fileWrapper) fileWrapper.style.display = 'none';
        if (downloadBtn) downloadBtn.style.display = 'none';
    }

    const modalEl = document.getElementById('policyDetailModal');
    if (modalEl) new bootstrap.Modal(modalEl).show();
};

// Open AI Summary Modal — parses saved JSON and renders full official report layout
window.openAISummaryModal = function (title, rawSummary, category) {
    // Parse AI summary JSON stored by admin
    let ai = null;
    if (rawSummary) {
        if (typeof rawSummary === 'string') {
            try { ai = JSON.parse(rawSummary); } catch (e) { ai = null; }
        } else if (typeof rawSummary === 'object') {
            ai = rawSummary;
        }
    }

    // Helper to safely escape HTML
    const esc = (s) => {
        const d = document.createElement('div');
        d.textContent = s || '';
        return d.innerHTML;
    };

    // Populate Document Information
    const titleEl = document.getElementById('uAiSum_title');
    const catEl = document.getElementById('uAiSum_category');
    const dateEl = document.getElementById('uAiSum_date');
    const summaryEl = document.getElementById('uAiSum_summary');
    const findingsEl = document.getElementById('uAiSum_findings');
    const impactEl = document.getElementById('uAiSum_impact');
    const conclusionEl = document.getElementById('uAiSum_conclusion');

    if (titleEl) titleEl.innerText = title || 'Legislative Policy Record';
    if (catEl) catEl.innerText = category || '—';

    if (ai) {
        // Date
        if (dateEl) dateEl.innerText = ai.date_generated || 'Generated by Legislative Research Office';

        // Executive Summary
        if (summaryEl) summaryEl.innerText = ai.executive_summary || ai.summary || '—';

        // Key Findings — array or string
        if (findingsEl) {
            const findings = ai.key_findings;
            if (Array.isArray(findings) && findings.length > 0) {
                let listHtml = '<ul class="mb-0 ps-3">';
                findings.forEach(f => { listHtml += `<li class="mb-1">${esc(f)}</li>`; });
                listHtml += '</ul>';
                findingsEl.innerHTML = listHtml;
            } else if (typeof findings === 'string' && findings.trim()) {
                findingsEl.innerText = findings;
            } else {
                findingsEl.innerHTML = '<ul class="mb-0 ps-3"><li>—</li></ul>';
            }
        }

        // Policy Impact
        if (impactEl) impactEl.innerText = ai.policy_impact || '—';

        // Conclusion
        if (conclusionEl) conclusionEl.innerText = ai.conclusion || ai.recommendation || '—';

    } else {
        // Fallback: display raw summary as-is if not valid JSON
        if (dateEl) dateEl.innerText = '—';
        if (summaryEl) summaryEl.innerText = rawSummary || '—';
        if (findingsEl) findingsEl.innerHTML = '<ul class="mb-0 ps-3"><li>—</li></ul>';
        if (impactEl) impactEl.innerText = '—';
        if (conclusionEl) conclusionEl.innerText = '—';
    }

    const modalEl = document.getElementById('aiSummaryModal');
    if (modalEl) new bootstrap.Modal(modalEl).show();
};

// Open Evaluation Detail Modal — same as admin's Impact Evaluation Matrix
function openEvaluationModal(evaluation) {
    const details = typeof evaluation === 'string' ? { title: evaluation } : evaluation;
    window.currentActiveEvaluation = details;

    const rawStatus = (details.status || '').trim();
    const hasEvaluationDate = Boolean(details.evaluationDate && details.evaluationDate !== '—' && details.evaluationDate.trim() !== '');
    const hasEvaluation = details.has_evaluation === true || (details.has_evaluation !== false && hasEvaluationDate && (rawStatus === 'Approved' || rawStatus === 'Completed' || rawStatus === 'Evaluated'));
    const isCompleted = hasEvaluation;

    // Update button text: Evaluate Policy vs Re-evaluate Policy
    const btn = document.getElementById('evalModalRunBtn');
    if (btn) {
        if (isCompleted) {
            btn.innerHTML = '<i class="bi bi-arrow-clockwise me-2"></i>Re-evaluate Policy';
        } else {
            btn.innerHTML = '<i class="bi bi-play-circle-fill me-2"></i>Evaluate Policy';
        }
        btn.style.background = 'linear-gradient(135deg, #4f46e5, #7c3aed)';
        btn.style.borderColor = 'transparent';
        btn.disabled = false;
    }

    // Policy title
    const titleEl = document.getElementById('evalModalTitle');
    if (titleEl) titleEl.textContent = details.title || 'Policy Evaluation';

    // Evaluated By
    const evalByEl = document.getElementById('evalModalEvaluator');
    if (evalByEl) evalByEl.textContent = isCompleted ? ((details.evaluator && details.evaluator !== 'Administration' && details.evaluator !== 'System Administrator') ? details.evaluator : 'Admin') : '—';

    // Status badge
    const status = details.status || (isCompleted ? 'Completed' : 'Draft');
    const statusEl = document.getElementById('evalModalStatus');
    if (statusEl) {
        statusEl.textContent = status;
        let style = 'padding: 4px 12px; border-radius: 999px; font-size: 0.8rem; font-weight: 700; display: inline-block; transition: all 0.2s ease;';
        if (status === 'Approved') {
            style += ' background:#dcfce7; color:#15803d; border:1px solid #bbf7d0;';
        } else if (status === 'Completed') {
            style += ' background:#dbeafe; color:#1d4ed8; border:1px solid #bfdbfe;';
        } else if (status === 'Under Review' || status === 'Draft' || status === 'Pending') {
            style += ' background:#fef3c7; color:#b45309; border:1px solid #fde68a;';
        } else {
            style += ' background:#f3f4f6; color:#4b5563; border:1px solid #e5e7eb;';
        }
        statusEl.className = '';
        statusEl.style.cssText = style;
    }

    // Optional Risk level badge
    const risk = details.riskLevel || 'N/A';
    const riskEl = document.getElementById('evalModalRisk');
    if (riskEl) {
        riskEl.textContent = risk;
        riskEl.removeAttribute('class');
        if (risk.toLowerCase().includes('low')) {
            riskEl.style.cssText = 'display:inline-block;background:#d1fae5;color:#065f46;border:1px solid #a7f3d0;padding:3px 10px;border-radius:999px;font-size:0.75rem;font-weight:600;';
        } else if (risk.toLowerCase().includes('moderate') || risk.toLowerCase().includes('medium')) {
            riskEl.style.cssText = 'display:inline-block;background:#fef3c7;color:#92400e;border:1px solid #fde68a;padding:3px 10px;border-radius:999px;font-size:0.75rem;font-weight:600;';
        } else if (risk.toLowerCase().includes('high')) {
            riskEl.style.cssText = 'display:inline-block;background:#fee2e2;color:#991b1b;border:1px solid #fca5a5;padding:3px 10px;border-radius:999px;font-size:0.75rem;font-weight:600;';
        } else {
            riskEl.style.cssText = 'display:inline-block;background:#f3f4f6;color:#4b5563;border:1px solid #e5e7eb;padding:3px 10px;border-radius:999px;font-size:0.75rem;font-weight:600;';
        }
    }

    // Criteria reasons / findings
    if (document.getElementById('evalCriteriaEconomicReason')) document.getElementById('evalCriteriaEconomicReason').textContent = details.economicReason || 'Funding and implementation costs are manageable and available within municipal allocations.';
    if (document.getElementById('evalCriteriaSocialReason')) document.getElementById('evalCriteriaSocialReason').textContent = details.socialReason || 'The policy provides measurable benefits to affected communities and enhances public welfare.';
    if (document.getElementById('evalCriteriaEnvReason')) document.getElementById('evalCriteriaEnvReason').textContent = details.envReason || 'The policy satisfies urban environmental standards and sustainability requirements.';
    if (document.getElementById('evalCriteriaLegalReason')) document.getElementById('evalCriteriaLegalReason').textContent = details.legalReason || 'Compliant with the Local Government Code and relevant national/local statutory frameworks.';

    // Evaluation date
    const dateEl = document.getElementById('evalModalDate');
    if (dateEl) dateEl.textContent = details.evaluationDate || '—';

    // Analysis
    const analysisEl = document.getElementById('evalModalAnalysis');
    if (analysisEl) analysisEl.textContent = details.aiAnalysis || (isCompleted ? 'The proposed policy measure demonstrates strong statutory alignment with municipal priorities across Economic Feasibility, Social Impact, Environmental Protection, and Legal Compliance criteria.' : 'No evaluation has been performed yet.');

    // Recommendation type & title
    if (document.getElementById('evalModalRecommendationType')) {
        document.getElementById('evalModalRecommendationType').textContent = details.recommendationType || 'Proceed with Implementation';
    }
    const recEl = document.getElementById('evalModalRecommendationTitle');
    if (recEl) recEl.textContent = details.recommendation || (isCompleted ? 'Enact Policy with Enhanced Inter-Agency Coordination and Funding Frameworks' : 'Awaiting evaluation.');

    // Reason
    const reasonEl = document.getElementById('evalModalReason');
    if (reasonEl) reasonEl.textContent = details.reason || (isCompleted ? 'The plan addresses a fundamental vulnerability in Manila\'s urban infrastructure that causes recurring economic losses, though its long-term success requires regional watershed integration and sustainable maintenance funding.' : '');

    // Suggested Improvements
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

    new bootstrap.Modal(document.getElementById('evaluationDetailModal')).show();
}

function openImpactModal(title, score, risk, summary) {
    if (document.getElementById('impactTitle')) document.getElementById('impactTitle').innerText = title;
    if (document.getElementById('impactScore')) document.getElementById('impactScore').innerText = score;
    if (document.getElementById('impactRisk')) document.getElementById('impactRisk').innerText = risk;
    if (document.getElementById('impactSummary')) document.getElementById('impactSummary').innerText = summary;
    const modalEl = document.getElementById('impactModal');
    if (modalEl) {
        const modal = new bootstrap.Modal(modalEl);
        modal.show();
    }
}

function handleProfileUpdate(e) {
    e.preventDefault();
    const name = document.getElementById('profileFullName').value.trim();
    const email = document.getElementById('profileEmail').value.trim();

    const user = JSON.parse(localStorage.getItem('current_user') || '{}');
    user.name = name;
    user.email = email;
    localStorage.setItem('current_user', JSON.stringify(user));

    if (document.getElementById('topbarUserName')) document.getElementById('topbarUserName').innerText = name;
    alert("Profile details updated successfully!");
}

// Analytics Chart.js Initialization
let chartsInitialized = false;
function initUserCharts() {
    if (chartsInitialized) return;
    chartsInitialized = true;

    const lineCtx = document.getElementById('userTrendsChart')?.getContext('2d');
    const pieCtx = document.getElementById('userPieChart')?.getContext('2d');

    if (lineCtx) {
        new Chart(lineCtx, {
            type: 'line',
            data: {
                labels: ['Q1 2025', 'Q2 2025', 'Q3 2025', 'Q4 2025', 'Q1 2026'],
                datasets: [{
                    label: 'Enacted Ordinances',
                    data: [8, 14, 11, 19, 24],
                    borderColor: '#0B2E59',
                    backgroundColor: 'rgba(11, 46, 89, 0.1)',
                    tension: 0.35,
                    fill: true
                }]
            },
            options: { responsive: true, maintainAspectRatio: false }
        });
    }

    if (pieCtx) {
        new Chart(pieCtx, {
            type: 'doughnut',
            data: {
                labels: ['Health & Sanitation', 'Infrastructure', 'Environment', 'Taxation & Finance'],
                datasets: [{
                    data: [35, 25, 25, 15],
                    backgroundColor: ['#0B2E59', '#1D4ED8', '#10B981', '#F59E0B']
                }]
            },
            options: { responsive: true, maintainAspectRatio: false }
        });
    }
}

// ── Councilor Dashboard Charts (Exact Admin Side Match) ────────
let userTrendsChartInstance = null;
let userTimelineChartInstance = null;

const userDashBarValueLabelsPlugin = {
    id: 'userDashBarValueLabels',
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

const userDashLineValueLabelsPlugin = {
    id: 'userDashLineValueLabels',
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

function initUserDashboardChart() {
    if (typeof Chart === 'undefined') return;

    const d = window.USER_DASHBOARD_DATA || {};

    // 1. Policies by Category Bar Chart
    try {
        const barCanvas = document.getElementById('userTrendsChart');
        if (barCanvas) {
            if (userTrendsChartInstance) {
                userTrendsChartInstance.destroy();
                userTrendsChartInstance = null;
            }

            const rawLabels = (d.categories && d.categories.labels && d.categories.labels.length > 0)
                ? d.categories.labels
                : ['Infrastructure, Traffic & Env', 'Health and Sanitation', 'Social Welfare & Community', 'Civil Registry & Public Serv', 'Education & Employment', 'Other'];
            const catData = (d.categories && d.categories.data && d.categories.data.length > 0)
                ? d.categories.data
                : [5, 2, 0, 0, 0, 0];

            function formatDashTickLabel(label) {
                if (!label) return '';
                const parts = label.split('&');
                if (parts.length > 1) {
                    return [parts[0].trim() + ' &', parts.slice(1).join('&').trim()];
                }
                const words = label.split(' ');
                if (words.length > 3) {
                    const mid = Math.ceil(words.length / 2);
                    return [words.slice(0, mid).join(' '), words.slice(mid).join(' ')];
                }
                return label;
            }

            const formattedLabels = rawLabels.map(formatDashTickLabel);
            const BAR_COLORS = ['#2563eb', '#16a34a', '#9333ea', '#eab308', '#94a3b8', '#cbd5e1'];

            userTrendsChartInstance = new Chart(barCanvas.getContext('2d'), {
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
                plugins: [userDashBarValueLabelsPlugin],
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
                            max: Math.max(...catData, 5) + 2,
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
        console.warn("User bar chart error:", e1);
    }

    // 2. Policies Uploaded This Month Area Line Chart
    try {
        const lineCanvas = document.getElementById('userUploadTimelineChart');
        if (lineCanvas) {
            if (userTimelineChartInstance) {
                userTimelineChartInstance.destroy();
                userTimelineChartInstance = null;
            }

            const lineCtx = lineCanvas.getContext('2d');
            const gradient = lineCtx.createLinearGradient(0, 0, 0, 200);
            gradient.addColorStop(0, 'rgba(37, 99, 235, 0.28)');
            gradient.addColorStop(1, 'rgba(37, 99, 235, 0.01)');

            const upLabels = (d.timeline && d.timeline.labels && d.timeline.labels.length > 0)
                ? d.timeline.labels
                : ['Aug 12', 'Aug 15', 'Aug 19', 'Aug 26'];
            const upData = (d.timeline && d.timeline.data && d.timeline.data.length > 0)
                ? d.timeline.data
                : [1, 1, 2, 3];

            userTimelineChartInstance = new Chart(lineCtx, {
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
                plugins: [userDashLineValueLabelsPlugin],
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
                            max: Math.max(...upData, 3) + 1,
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
        console.warn("User line chart error:", e2);
    }
}
window.initUserDashboardChart = initUserDashboardChart;

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

    const logoUrl = window.location.origin + window.location.pathname.substring(0, window.location.pathname.lastIndexOf('/users/')) + '/assets/images/manilacityhall.svg';

    fetch(logoUrl)
        .then(res => res.text())
        .then(svgText => {
            executeUserAiPdfDownload(contentEl, svgText, fileName);
        })
        .catch(() => {
            executeUserAiPdfDownload(contentEl, null, fileName);
        });
};

function executeUserAiPdfDownload(contentEl, logoSvg, fileName) {
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
