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

    const policyId = policy.id || policy.policy_id || '';
    const hasPhysicalFile = Boolean(policy.file && policy.file.trim() !== '');

    if (policyId || hasPhysicalFile) {
        const viewUrl = policyId
            ? ('../backend/view_policy_document.php?id=' + encodeURIComponent(policyId))
            : ('../assets/uploads/policies/' + encodeURIComponent(policy.file));
        if (fileWrapper) fileWrapper.style.display = '';
        if (fileLink) fileLink.href = viewUrl;
        if (downloadBtn) {
            if (hasPhysicalFile) {
                downloadBtn.href = '../assets/uploads/policies/' + encodeURIComponent(policy.file);
            } else {
                downloadBtn.href = '../backend/view_policy_document.php?id=' + encodeURIComponent(policyId) + '&download=1';
            }
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
    const hasEvaluation = details.has_evaluation === true || (details.has_evaluation !== false && hasEvaluationDate && (rawStatus === 'Approved' || rawStatus === 'Under Review' || rawStatus === 'Does Not Meet Standards' || rawStatus === 'Completed' || rawStatus === 'Evaluated'));
    const isCompleted = hasEvaluation;

    // Policy title
    const titleEl = document.getElementById('evalModalTitle');
    if (titleEl) titleEl.textContent = details.title || 'Policy Evaluation';

    // Evaluated By & Date
    const evalByEl = document.getElementById('evalModalEvaluator');
    if (evalByEl) {
      let raw = (details.evaluator || '').trim();
      if (isCompleted && raw && raw !== '—') {
        if (/^(Admin|Staff|Administrator)\s*[-:]\s*/i.test(raw)) {
          evalByEl.textContent = raw;
        } else if (raw.toLowerCase() !== 'admin' && raw.toLowerCase() !== 'staff' && raw !== 'A.I. Evaluator') {
          evalByEl.textContent = 'Admin - ' + raw;
        } else {
          evalByEl.textContent = raw;
        }
      } else {
        evalByEl.textContent = isCompleted ? 'Admin' : '—';
      }
    }

    const dateEl = document.getElementById('evalModalDate');
    if (dateEl) dateEl.textContent = isCompleted ? (details.evaluationDate || '—') : '—';

    // Criteria scores directly from record
    const econLevel = isCompleted ? (details.economicLevel || 'High') : 'Awaiting';
    const socialLevel = isCompleted ? (details.socialLevel || 'High') : 'Awaiting';
    const envLevel = isCompleted ? (details.envLevel || 'High') : 'Awaiting';
    const legalLevel = isCompleted ? (details.legalLevel || 'High') : 'Awaiting';

    const isLowLevel = (lvl) => {
        const l = String(lvl || '').toLowerCase().trim();
        return l === 'low' || l === 'fail' || l === 'failed' || l === 'does not meet' || l === 'non-compliant';
    };

    const hasFailedCriterion = isLowLevel(econLevel) || isLowLevel(socialLevel) || isLowLevel(envLevel) || isLowLevel(legalLevel);

    // STATUS MODEL (3 STATES: Draft, Approved, Needs Revision)
    let currentStatus = 'Draft';
    if (hasEvaluation) {
        if (hasFailedCriterion) {
            currentStatus = 'Needs Revision';
        } else {
            currentStatus = 'Approved';
        }
    } else {
        currentStatus = 'Draft';
    }

    const statusEl = document.getElementById('evalModalStatus');
    if (statusEl) {
        statusEl.textContent = currentStatus;
        let style = 'padding: 4px 12px; border-radius: 999px; font-size: 0.8rem; font-weight: 700; display: inline-block; transition: all 0.2s ease;';
        if (currentStatus === 'Approved') {
            style += ' background: rgba(22, 163, 74, 0.12); color: #15803d; border: 1px solid rgba(22, 163, 74, 0.25);';
        } else if (currentStatus === 'Needs Revision') {
            style += ' background: #fef2f2; color: #b91c1c; border: 1px solid #fecaca;';
        } else {
            style += ' background: rgba(107, 114, 128, 0.12); color: #4b5563; border: 1px solid rgba(107, 114, 128, 0.25);';
        }
        statusEl.className = '';
        statusEl.style.cssText = style;
    }

    // Full Document Access Link (Bottom-Left)
    const docLinkEl = document.getElementById('evalModalDocLink');
    if (docLinkEl) {
        const policyId = details.policy_id || details.id || '';
        const rawFilePath = (details.file_path || '').trim();
        if (policyId) {
            // Always use the backend viewer for reliability — it handles both PDF and DOCX,
            // shows metadata, and works regardless of file_path format.
            docLinkEl.href = `../backend/view_policy_document.php?id=${encodeURIComponent(policyId)}`;
            docLinkEl.target = '_blank';
        } else if (rawFilePath) {
            docLinkEl.href = '../assets/uploads/policies/' + encodeURIComponent(rawFilePath);
            docLinkEl.target = '_blank';
        } else {
            docLinkEl.href = '#';
            docLinkEl.target = '_self';
        }
    }

    // 1. Economic Feasibility
    const econScoreEl = document.getElementById('evalCriteriaEconomicScore');
    const econEl = document.getElementById('evalCriteriaEconomicReason');
    if (econScoreEl) econScoreEl.innerHTML = isCompleted ? getEvaluationScoreBadgeUser(econLevel) : '<span class="badge bg-secondary-subtle text-secondary px-2.5 py-1">Awaiting</span>';
    if (econEl) {
        econEl.innerHTML = isCompleted ? (details.economicReason ? String(details.economicReason).replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;") : 'Funding and implementation costs are manageable within municipal allocations.') : '<span class="text-muted fst-italic">Awaiting evaluation.</span>';
    }

    // 2. Social Impact
    const socialScoreEl = document.getElementById('evalCriteriaSocialScore');
    const socialEl = document.getElementById('evalCriteriaSocialReason');
    if (socialScoreEl) socialScoreEl.innerHTML = isCompleted ? getEvaluationScoreBadgeUser(socialLevel) : '<span class="badge bg-secondary-subtle text-secondary px-2.5 py-1">Awaiting</span>';
    if (socialEl) {
        socialEl.innerHTML = isCompleted ? (details.socialReason ? String(details.socialReason).replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;") : 'The policy provides measurable benefits to affected communities and enhances public welfare.') : '<span class="text-muted fst-italic">Awaiting evaluation.</span>';
    }

    // 3. Environmental Impact
    const envScoreEl = document.getElementById('evalCriteriaEnvScore');
    const envEl = document.getElementById('evalCriteriaEnvReason');
    if (envScoreEl) envScoreEl.innerHTML = isCompleted ? getEvaluationScoreBadgeUser(envLevel) : '<span class="badge bg-secondary-subtle text-secondary px-2.5 py-1">Awaiting</span>';
    if (envEl) {
        envEl.innerHTML = isCompleted ? (details.envReason ? String(details.envReason).replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;") : 'The policy satisfies urban environmental standards and sustainability requirements.') : '<span class="text-muted fst-italic">Awaiting evaluation.</span>';
    }

    // 4. Legal Compliance
    const legalScoreEl = document.getElementById('evalCriteriaLegalScore');
    const legalEl = document.getElementById('evalCriteriaLegalReason');
    if (legalScoreEl) legalScoreEl.innerHTML = isCompleted ? getEvaluationScoreBadgeUser(legalLevel) : '<span class="badge bg-secondary-subtle text-secondary px-2.5 py-1">Awaiting</span>';
    if (legalEl) {
        if (isCompleted) {
            if (details.legalAuthority || details.draftingQuality || details.proceduralCompliance) {
                legalEl.innerHTML = `
                  <div class="mb-1.5"><strong class="text-dark">1. Legal Authority:</strong> ${String(details.legalAuthority || 'Within delegated municipal powers under RA 7160.').replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;")}</div>
                  <div class="mb-1.5"><strong class="text-dark">2. Drafting Quality:</strong> ${String(details.draftingQuality || 'Clear operative clauses and severability verified.').replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;")}</div>
                  <div><strong class="text-dark">3. Procedural Compliance:</strong> ${String(details.proceduralCompliance || 'Readings verified; committee report and publication marked as Unverified pending floor submission.').replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;")}</div>
                `;
            } else {
                legalEl.innerHTML = String(details.legalReason || 'Compliant with the Local Government Code and statutory frameworks.').replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;");
            }
        } else {
            legalEl.innerHTML = '<span class="text-muted fst-italic">Awaiting evaluation.</span>';
        }
    }

    // Analysis
    const analysisEl = document.getElementById('evalModalAnalysis');
    if (analysisEl) {
        analysisEl.innerHTML = isCompleted ? (details.aiAnalysis ? String(details.aiAnalysis).replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;") : 'Evidence-based impact analysis confirms alignment with statutory governance.') : '<span class="text-muted fst-italic">Awaiting evaluation.</span>';
    }

    const modalEl = document.getElementById('evaluationDetailModal');
    if (modalEl) {
        (bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl)).show();
    }
}

window.openEvaluationModal = openEvaluationModal;

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
