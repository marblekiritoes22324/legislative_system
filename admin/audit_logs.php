<?php
// Pull audit logs from database if table exists
$audit_logs = [];
if (!empty($conn)) {
  if (function_exists('ensure_audit_logs_table')) {
    ensure_audit_logs_table($conn);
  }
  $alq = mysqli_query($conn, "SELECT * FROM audit_logs ORDER BY created_at DESC, 1 DESC LIMIT 100");
  if ($alq) {
    while ($row = mysqli_fetch_assoc($alq)) {
      $audit_logs[] = $row;
    }
  }
}

// Fallback demo audit logs for Manila City Hall Legislative Information System
if (empty($audit_logs)) {
  $audit_logs = [
    [
      'id' => 1,
      'date_time' => '2026-08-17 13:35:57',
      'user' => 'Admin',
      'role' => 'Admin',
      'module' => 'User Directory',
      'activity' => 'Provisioned new account for Salas123'
    ],
    [
      'id' => 2,
      'date_time' => '2026-08-17 13:34:05',
      'user' => 'Admin',
      'role' => 'Admin',
      'module' => 'User Directory',
      'activity' => 'Provisioned new account for Daniel123'
    ],
    [
      'id' => 3,
      'date_time' => '2026-08-17 12:13:48',
      'user' => 'Quintana',
      'role' => 'Staff',
      'module' => 'Login',
      'activity' => 'Staff user logged in to portal'
    ],
    [
      'id' => 4,
      'date_time' => '2026-08-15 22:10:24',
      'user' => 'Admin',
      'role' => 'Admin',
      'module' => 'Policy Records',
      'activity' => 'Permanently deleted policy "Urban Traffic Congestion Study in Manila City" (ID #28)'
    ],
    [
      'id' => 5,
      'date_time' => '2026-08-15 22:10:21',
      'user' => 'Admin',
      'role' => 'Admin',
      'module' => 'Policy Records',
      'activity' => 'Permanently deleted policy "Urban Traffic Congestion Study in Manila City" (ID #27)'
    ],
    [
      'id' => 6,
      'date_time' => '2026-08-15 22:10:18',
      'user' => 'Admin',
      'role' => 'Admin',
      'module' => 'Policy Records',
      'activity' => 'Permanently deleted policy "Solid Waste Management Improvement Strategy for Manila City" (ID #20)'
    ],
    [
      'id' => 7,
      'date_time' => '2026-08-15 18:23:24',
      'user' => 'Admin',
      'role' => 'Admin',
      'module' => 'Policy Records',
      'activity' => 'Archived policy record #28'
    ],
    [
      'id' => 8,
      'date_time' => '2026-08-14 16:45:10',
      'user' => 'Juan Cruz',
      'role' => 'Staff',
      'module' => 'Research Data',
      'activity' => 'Uploaded dataset "Manila City Air Quality Index Metrics 2026"'
    ],
    [
      'id' => 9,
      'date_time' => '2026-08-14 15:20:00',
      'user' => 'Maria Santos',
      'role' => 'Staff',
      'module' => 'Evaluations',
      'activity' => 'Submitted impact evaluation for Public Transport Optimization Ordinance'
    ],
    [
      'id' => 10,
      'date_time' => '2026-08-14 14:10:15',
      'user' => 'Admin',
      'role' => 'Admin',
      'module' => 'User Directory',
      'activity' => 'Updated user permissions for Policy Analyst role'
    ],
    [
      'id' => 11,
      'date_time' => '2026-08-13 11:30:45',
      'user' => 'Ana Reyes',
      'role' => 'Staff',
      'module' => 'Reports',
      'activity' => 'Generated Executive Summary Report (PDF)'
    ],
    [
      'id' => 12,
      'date_time' => '2026-08-13 09:15:00',
      'user' => 'Admin',
      'role' => 'Admin',
      'module' => 'System',
      'activity' => 'Automated nightly database backup completed successfully'
    ],
    [
      'id' => 13,
      'date_time' => '2026-08-12 17:05:30',
      'user' => 'Christian M. Caspe',
      'role' => 'Councilor',
      'module' => 'Policy Records',
      'activity' => 'Reviewed amendment draft for Single-Use Plastic Regulation'
    ],
    [
      'id' => 14,
      'date_time' => '2026-08-12 14:22:18',
      'user' => 'Christian M. Caspe',
      'role' => 'Councilor',
      'module' => 'Comparison',
      'activity' => 'Ran comparative analysis across 3 district ordinances'
    ],
    [
      'id' => 15,
      'date_time' => '2026-08-11 16:50:00',
      'user' => 'Quintana',
      'role' => 'Staff',
      'module' => 'AI Summary',
      'activity' => 'Generated AI summary report for Coastal Clean-up Initiative'
    ]
  ];
}
?>

<style>
  /* Audit Logs Premium Styling */
  .audit-table-head th {
    background: #F8FAFC !important;
    color: #334155 !important;
    font-size: 0.82rem !important;
    font-weight: 600 !important;
    text-transform: uppercase !important;
    letter-spacing: 0.5px !important;
    padding-top: 14px !important;
    padding-bottom: 14px !important;
    padding-left: 16px !important;
    padding-right: 16px !important;
    border-bottom: 1px solid #E2E8F0 !important;
  }

  .audit-log-row {
    transition: background 0.15s ease;
  }
  .audit-log-row:hover {
    background-color: #F8FAFC !important;
  }
  .audit-log-row td {
    padding-top: 13px !important;
    padding-bottom: 13px !important;
    padding-left: 16px !important;
    padding-right: 16px !important;
    border-bottom: 1px solid #F1F5F9 !important;
  }

  .audit-num {
    color: #64748B !important;
    font-size: 0.88rem !important;
    font-weight: 500 !important;
    text-align: center;
  }

  .audit-datetime {
    color: #1E293B !important;
    font-size: 0.88rem !important;
    font-weight: 500 !important;
    white-space: nowrap;
  }

  .audit-user {
    color: #0F172A !important;
    font-size: 0.92rem !important;
    font-weight: 600 !important;
    white-space: nowrap;
  }

  .audit-activity {
    color: #0F172A !important;
    font-size: 0.92rem !important;
    font-weight: 600 !important;
    line-height: 1.45;
    letter-spacing: -0.1px;
  }

  /* Role Badges */
  .role-badge-admin {
    background: #EFF6FF !important;
    color: #1D4ED8 !important;
    border: 1px solid #BFDBFE !important;
    font-size: 0.82rem !important;
    font-weight: 600 !important;
    padding: 5px 12px !important;
    display: inline-flex;
    align-items: center;
  }

  .role-badge-staff {
    background: #ECFDF5 !important;
    color: #047857 !important;
    border: 1px solid #A7F3D0 !important;
    font-size: 0.82rem !important;
    font-weight: 600 !important;
    padding: 5px 12px !important;
    display: inline-flex;
    align-items: center;
  }

  .role-badge-councilor {
    background: #FEF3C7 !important;
    color: #92400E !important;
    border: 1px solid #FDE68A !important;
    font-size: 0.82rem !important;
    font-weight: 600 !important;
    padding: 5px 12px !important;
    display: inline-flex;
    align-items: center;
  }

  /* Module Badges - Rich & Vibrant Colors */
  .module-badge-policy {
    background: #EFF6FF !important;
    color: #2563EB !important;
    border: 1px solid #BFDBFE !important;
    font-size: 0.82rem !important;
    font-weight: 600 !important;
    padding: 5px 12px !important;
    display: inline-flex;
    align-items: center;
  }

  .module-badge-system {
    background: #EEF2FF !important;
    color: #4F46E5 !important;
    border: 1px solid #C7D2FE !important;
    font-size: 0.82rem !important;
    font-weight: 600 !important;
    padding: 5px 12px !important;
    display: inline-flex;
    align-items: center;
  }

  .module-badge-research {
    background: #ECFEFF !important;
    color: #0891B2 !important;
    border: 1px solid #A5F3FC !important;
    font-size: 0.82rem !important;
    font-weight: 600 !important;
    padding: 5px 12px !important;
    display: inline-flex;
    align-items: center;
  }

  .module-badge-evaluations {
    background: #FFFBEB !important;
    color: #D97706 !important;
    border: 1px solid #FDE68A !important;
    font-size: 0.82rem !important;
    font-weight: 600 !important;
    padding: 5px 12px !important;
    display: inline-flex;
    align-items: center;
  }

  .module-badge-comparison {
    background: #FAF5FF !important;
    color: #9333EA !important;
    border: 1px solid #E9D5FF !important;
    font-size: 0.82rem !important;
    font-weight: 600 !important;
    padding: 5px 12px !important;
    display: inline-flex;
    align-items: center;
  }

  .module-badge-reports {
    background: #FFF1F2 !important;
    color: #E11D48 !important;
    border: 1px solid #FECDD3 !important;
    font-size: 0.82rem !important;
    font-weight: 600 !important;
    padding: 5px 12px !important;
    display: inline-flex;
    align-items: center;
  }

  .module-badge-user {
    background: #ECFDF5 !important;
    color: #059669 !important;
    border: 1px solid #A7F3D0 !important;
    font-size: 0.82rem !important;
    font-weight: 600 !important;
    padding: 5px 12px !important;
    display: inline-flex;
    align-items: center;
  }
</style>

<section id="systemLogsSection" class="content-section <?= ($active_section ?? 'adminDashboardSection') !== 'systemLogsSection' ? 'd-none' : '' ?>">
  <!-- Top Header Banner -->
  <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
      <h2 class="h3 fw-bold text-dark mb-1 d-flex align-items-center gap-2">
        <i class="bi bi-clipboard-data-fill text-primary fs-4"></i> Audit Logs
      </h2>
      <p class="text-muted mb-0" style="font-size: 0.92rem;">View system activities performed by users.</p>
    </div>
  </div>

  <!-- Main Card Container -->
  <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">

    <!-- Search & Filter Controls -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
      <!-- Search input -->
      <div class="position-relative flex-grow-1" style="max-width: 400px;">
        <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
        <input type="search" id="auditLogSearch" class="form-control ps-5 py-2 rounded-3 border-light-subtle"
          placeholder="Search logs by user, role, activity, module..." onkeyup="filterAuditLogs()">
      </div>

      <!-- Filters Group -->
      <div class="d-flex align-items-center gap-2">
        <!-- Module Filter Dropdown -->
        <select id="auditLogModuleFilter" class="form-select py-2 px-3 rounded-3 border-light-subtle"
          style="width: 180px;" onchange="filterAuditLogs()">
          <option value="ALL">All Modules</option>
          <option value="Login">Login</option>
          <option value="Policy Research">Policy Research</option>
          <option value="Data Collection">Data Collection</option>
          <option value="Evaluations">Evaluations</option>
          <option value="Comparison">Comparison</option>
          <option value="Reports">Reports</option>
          <option value="User Directory">User Directory</option>
          <option value="System">System</option>
        </select>
      </div>
    </div>

    <!-- Table Responsive Wrapper (Fills Card Height & Maximizes Width) -->
    <div class="table-responsive border rounded-4 overflow-hidden mb-3">
      <table class="table table-hover align-middle mb-0" style="font-size: 0.92rem; width: 100%;">
        <thead class="audit-table-head">
          <tr>
            <th class="text-center" style="width: 55px;">#</th>
            <th style="width: 210px;">Date &amp; Time</th>
            <th style="width: 200px;">User</th>
            <th style="width: 145px;">Role</th>
            <th style="width: 185px;">Module</th>
            <th>Activity</th>
          </tr>
        </thead>
        <tbody id="auditLogsTableBody">
          <?php foreach ($audit_logs as $index => $log):
            $num = $index + 1;
            $dt = htmlspecialchars($log['date_time'] ?? ($log['created_at'] ?? 'May 14, 2026 10:15 AM'));
            $user = htmlspecialchars($log['user'] ?? ($log['user_name'] ?? 'Admin'));
            if ($user === 'System Administrator' || $user === 'Administration') $user = 'Admin';
            
            // Resolve Role
            $role = function_exists('resolve_audit_role') 
              ? resolve_audit_role($conn ?? null, $user, $log['role'] ?? null) 
              : ($log['role'] ?? 'Admin');
            
            $role_class = 'role-badge-admin';
            $role_icon = 'bi-shield-lock-fill';
            if ($role === 'Staff') {
              $role_class = 'role-badge-staff';
              $role_icon = 'bi-person-badge-fill';
            } elseif ($role === 'Councilor') {
              $role_class = 'role-badge-councilor';
              $role_icon = 'bi-award-fill';
            }

            $module = htmlspecialchars($log['module'] ?? 'Policy Records');
            $activity = htmlspecialchars($log['activity'] ?? 'System activity');

            $mod_lower = strtolower($module);
            $mod_class = 'module-badge-policy';
            $mod_icon = 'bi-file-earmark-text';
            if (strpos($mod_lower, 'research') !== false || strpos($mod_lower, 'data') !== false) {
              $mod_class = 'module-badge-research';
              $mod_icon = 'bi-database-fill-gear';
            } elseif (strpos($mod_lower, 'evaluat') !== false || strpos($mod_lower, 'impact') !== false) {
              $mod_class = 'module-badge-evaluations';
              $mod_icon = 'bi-bar-chart-line';
            } elseif (strpos($mod_lower, 'compar') !== false) {
              $mod_class = 'module-badge-comparison';
              $mod_icon = 'bi-layout-split';
            } elseif (strpos($mod_lower, 'report') !== false) {
              $mod_class = 'module-badge-reports';
              $mod_icon = 'bi-journal-text';
            } elseif (strpos($mod_lower, 'user') !== false || strpos($mod_lower, 'directory') !== false) {
              $mod_class = 'module-badge-user';
              $mod_icon = 'bi-person-gear';
            } elseif (strpos($mod_lower, 'system') !== false || strpos($mod_lower, 'auth') !== false || strpos($mod_lower, 'login') !== false) {
              $mod_class = 'module-badge-system';
              $mod_icon = 'bi-shield-check';
            }
            ?>
            <tr class="audit-log-row" data-module="<?= $module ?>"
              data-search="<?= strtolower($dt . ' ' . $user . ' ' . $role . ' ' . $module . ' ' . $activity) ?>">
              <td class="text-center audit-num"><?= $num ?></td>
              <td class="audit-datetime"><?= $dt ?></td>
              <td class="audit-user"><?= $user ?></td>
              <td><span class="badge <?= $role_class ?> rounded-pill"><i class="bi <?= $role_icon ?> me-1"></i><?= $role ?></span></td>
              <td><span class="badge <?= $mod_class ?> rounded-pill"><i class="bi <?= $mod_icon ?> me-1"></i> <?= $module ?></span></td>
              <td class="audit-activity"><?= $activity ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <!-- Pagination Footer -->
    <div class="d-flex flex-wrap align-items-center justify-content-between pt-2 gap-2">
      <div class="text-muted small fw-medium" id="auditLogsCount">
        Showing 1 to <?= count($audit_logs) ?> of <?= count($audit_logs) ?> logs
      </div>
      <div class="d-flex align-items-center gap-1" id="auditLogsPagination">
        <!-- Pagination controls dynamically rendered via JavaScript -->
      </div>
    </div>

  </div>
</section>

<script>
  var currentAuditPage = 1;
  var auditRowsPerPage = 10; // Exactly 10 rows per page as requested

  function filterAuditLogs() {
    currentAuditPage = 1;
    renderAuditLogsTable();
  }

  function resetAuditFilters() {
    if (document.getElementById('auditLogSearch')) document.getElementById('auditLogSearch').value = '';
    if (document.getElementById('auditLogModuleFilter')) document.getElementById('auditLogModuleFilter').value = 'ALL';
    filterAuditLogs();
  }

  function renderAuditLogsTable() {
    var search = (document.getElementById('auditLogSearch')?.value || '').toLowerCase().trim();
    var mod = document.getElementById('auditLogModuleFilter')?.value || 'ALL';
    var rows = Array.from(document.querySelectorAll('#auditLogsTableBody .audit-log-row'));

    var filteredRows = rows.filter(function (row) {
      var rowModule = row.getAttribute('data-module') || '';
      var rowSearch = row.getAttribute('data-search') || '';
      var matchesModule = (mod === 'ALL' || rowModule === mod ||
        (mod === 'Policy Research' && (rowModule === 'Policy Records' || rowModule === 'Policy Research')) ||
        (mod === 'Data Collection' && (rowModule === 'Research Data' || rowModule === 'Data Collection')));
      var matchesSearch = (rowSearch.indexOf(search) !== -1);
      return matchesModule && matchesSearch;
    });

    var totalFiltered = filteredRows.length;
    var totalPages = Math.ceil(totalFiltered / auditRowsPerPage) || 1;

    if (currentAuditPage > totalPages) currentAuditPage = totalPages;
    if (currentAuditPage < 1) currentAuditPage = 1;

    var startIndex = (currentAuditPage - 1) * auditRowsPerPage;
    var endIndex = startIndex + auditRowsPerPage;

    rows.forEach(function (row) { row.style.display = 'none'; });

    filteredRows.forEach(function (row, idx) {
      if (idx >= startIndex && idx < endIndex) {
        row.style.display = '';
      }
    });

    // Update Showing count text
    var countEl = document.getElementById('auditLogsCount');
    if (countEl) {
      var showingStart = totalFiltered === 0 ? 0 : startIndex + 1;
      var showingEnd = Math.min(endIndex, totalFiltered);
      countEl.textContent = 'Showing ' + showingStart + ' to ' + showingEnd + ' of ' + totalFiltered + ' logs';
    }

    // Render pagination buttons
    renderAuditPaginationButtons(totalPages);
  }

  function renderAuditPaginationButtons(totalPages) {
    var pagContainer = document.getElementById('auditLogsPagination');
    if (!pagContainer) return;

    var html = '';
    html += '<button class="btn btn-sm btn-outline-secondary rounded-2 px-2.5" ' + (currentAuditPage === 1 ? 'disabled' : '') + ' onclick="goToAuditPage(' + (currentAuditPage - 1) + ')"><i class="bi bi-chevron-left"></i></button>';

    for (var i = 1; i <= totalPages; i++) {
      if (i === currentAuditPage) {
        html += '<button class="btn btn-sm btn-primary rounded-2 px-3 fw-semibold" style="background:#0B1B3D; border-color:#0B1B3D;">' + i + '</button>';
      } else {
        html += '<button class="btn btn-sm btn-outline-secondary rounded-2 px-3" onclick="goToAuditPage(' + i + ')">' + i + '</button>';
      }
    }

    html += '<button class="btn btn-sm btn-outline-secondary rounded-2 px-2.5" ' + (currentAuditPage === totalPages ? 'disabled' : '') + ' onclick="goToAuditPage(' + (currentAuditPage + 1) + ')"><i class="bi bi-chevron-right"></i></button>';

    pagContainer.innerHTML = html;
  }

  function goToAuditPage(page) {
    currentAuditPage = page;
    renderAuditLogsTable();
  }

  // Export Audit Logs to CSV
  function exportAuditLogsCSV() {
    var rows = document.querySelectorAll('#auditLogsTableBody .audit-log-row');
    var csvContent = "data:text/csv;charset=utf-8,#,Date & Time,User,Role,Module,Activity\n";

    rows.forEach(function (row) {
      if (row.style.display !== 'none') {
        var cols = row.querySelectorAll('td');
        var rowData = [];
        cols.forEach(function (col) {
          var text = col.textContent.trim().replace(/"/g, '""');
          rowData.push('"' + text + '"');
        });
        csvContent += rowData.join(",") + "\n";
      }
    });

    var encodedUri = encodeURI(csvContent);
    var link = document.createElement("a");
    link.setAttribute("href", encodedUri);
    link.setAttribute("download", "Audit_Logs_Report.csv");
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
  }

  document.addEventListener('DOMContentLoaded', function () {
    renderAuditLogsTable();
  });
</script>