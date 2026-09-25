<?php
// Pull policy records and evaluations from DB for the select table
$report_policies = [];
if (!empty($conn)) {
  $rq = mysqli_query($conn, "
    SELECT 
      p.id, 
      p.title, 
      p.category, 
      p.status, 
      p.created_at, 
      p.ai_summary, 
      p.author,
      p.file_path,
      COALESCE(p.related_record, '') AS ordinance_number,
      e.id AS evaluation_id,
      e.overall_score,
      e.risk_level,
      e.ai_recommendation,
      e.status AS eval_status
    FROM policy_records p
    LEFT JOIN evaluations e ON p.id = e.policy_id
    WHERE (p.status IS NULL OR p.status != 'Archived')
    ORDER BY p.created_at DESC 
    LIMIT 100
  ");
  if ($rq) {
    while ($row = mysqli_fetch_assoc($rq)) {
      $report_policies[] = $row;
    }
  }
}
// Fallback demo records so the page is never blank
if (empty($report_policies)) {
  $report_policies = [
    ['id' => 1, 'title' => 'Plastic Reduction Ordinance', 'category' => 'Environment', 'status' => 'Pending', 'eval_status' => 'Draft', 'created_at' => '2026-05-10'],
    ['id' => 2, 'title' => 'Traffic Congestion Study', 'category' => 'Transportation', 'status' => 'Pending', 'eval_status' => 'Draft', 'created_at' => '2026-05-08'],
    ['id' => 3, 'title' => 'Public Health Program', 'category' => 'Health', 'status' => 'Pending', 'eval_status' => 'Draft', 'created_at' => '2026-05-05'],
  ];
}

// Compute counts for filter tabs dynamically from actual database statuses
$total_report_count = count($report_policies);
$status_counts = [];
foreach ($report_policies as &$pol) {
  // Determine accurate status directly from database values
  $raw_status = !empty($pol['eval_status']) ? trim($pol['eval_status']) : (!empty($pol['status']) ? trim($pol['status']) : 'Draft');
  if ($raw_status === 'Completed' || $raw_status === 'Published') {
    $raw_status = 'Approved';
  }
  $pol['computed_status'] = $raw_status;
  $status_key = strtolower(str_replace(' ', '_', $raw_status));
  $pol['status_key'] = $status_key;

  if (!isset($status_counts[$status_key])) {
    $status_counts[$status_key] = [
      'key' => $status_key,
      'label' => $raw_status,
      'count' => 0
    ];
  }
  $status_counts[$status_key]['count']++;
}
unset($pol);
?>
<section id="reportGenerationSection"
  class="content-section <?= ($active_section ?? 'adminDashboardSection') !== 'reportGenerationSection' ? 'd-none' : '' ?>">

  <!-- Top Header -->
  <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
    <div>
      <h2 class="h3 fw-bold text-dark mb-1 d-flex align-items-center gap-2">
        <i class="bi bi-file-earmark-text-fill text-primary fs-4"></i> Report Generation Module
      </h2>
      <p class="text-muted mb-0">Generate official legislative reports based on policy records, evaluations, and
        research data.</p>
    </div>
  </div>

  <style>
    /* Deep Manila Navy Table Header */
    .policy-table-thead th {
      background-color: #0B2E59 !important;
      color: #FFFFFF !important;
      font-size: 0.82rem !important;
      font-weight: 800 !important;
      letter-spacing: 0.05em !important;
      border-bottom: 2.5px solid #082242 !important;
      border-top: none !important;
      vertical-align: middle !important;
    }

    .pagination-step-btn {
      width: 32px !important;
      height: 32px !important;
      min-width: 32px !important;
      max-width: 32px !important;
      padding: 0 !important;
      display: inline-flex !important;
      align-items: center !important;
      justify-content: center !important;
      font-size: 0.85rem !important;
      font-weight: 600 !important;
      line-height: 1 !important;
      border-radius: 6px !important;
      transition: all 0.15s ease !important;
      box-sizing: border-box !important;
    }

    .clickable-report-row {
      transition: background-color 0.15s ease, transform 0.1s ease;
    }

    .clickable-report-row:hover {
      background-color: #f1f5f9 !important;
    }

    .clickable-report-row .policy-title-link {
      color: #0F172A !important;
      font-weight: 600 !important;
      transition: color 0.15s ease !important;
      text-decoration: none !important;
    }

    .clickable-report-row:hover .policy-title-link {
      color: #0B2E59 !important;
      text-decoration: none !important;
    }

    body.dark-theme .clickable-report-row:hover {
      background-color: #1e293b !important;
    }

    body.dark-theme .clickable-report-row .policy-title-link {
      color: #f8fafc !important;
    }

    body.dark-theme .clickable-report-row:hover .policy-title-link {
      color: #60a5fa !important;
    }

    .badge-report-type {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      padding: 4px 10px;
      border-radius: 6px;
      font-size: 0.78rem;
      font-weight: 600;
      background: #EFF6FF;
      color: #1E40AF;
      border: 1px solid #DBEAFE;
      white-space: nowrap;
    }

    /* Executive Manila Navy & Gold Action Button (Eliminates AI neon-blue look) */
    .btn-report-download,
    .btn-report-action-view {
      background: #0B2E59 !important;
      color: #FFFFFF !important;
      border: 1px solid #082242 !important;
      padding: 6.5px 15px !important;
      border-radius: 8px !important;
      font-size: 0.81rem !important;
      font-weight: 600 !important;
      display: inline-flex !important;
      align-items: center !important;
      gap: 7px !important;
      white-space: nowrap !important;
      flex-shrink: 0 !important;
      transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1) !important;
      text-decoration: none !important;
      box-shadow: 0 1px 3px rgba(11, 46, 89, 0.2), inset 0 1px 0 rgba(255, 255, 255, 0.12) !important;
    }

    .btn-report-download i,
    .btn-report-action-view i {
      color: #FCD34D !important;
      /* Manila Gold */
      font-size: 0.88rem !important;
      transition: transform 0.2s ease, color 0.2s ease !important;
    }

    .btn-report-download:hover,
    .btn-report-action-view:hover {
      background: #123E75 !important;
      border-color: #123E75 !important;
      color: #FFFFFF !important;
      box-shadow: 0 4px 12px rgba(11, 46, 89, 0.3) !important;
      transform: translateY(-1px) !important;
    }

    .btn-report-download:hover i,
    .btn-report-action-view:hover i {
      color: #FDE047 !important;
      transform: translateY(1px) scale(1.1) !important;
    }

    .btn-report-download:active,
    .btn-report-action-view:active {
      transform: translateY(0) !important;
      box-shadow: 0 1px 2px rgba(11, 46, 89, 0.15) !important;
    }

    /* Clean Minimalist Policy Filter Toolbar */

    .policy-search-box {
      background: #F8FAFC !important;
      border: 1px solid #E2E8F0 !important;
      border-radius: 8px !important;
      height: 40px !important;
      display: flex !important;
      align-items: center !important;
      padding: 0 12px !important;
      gap: 10px !important;
      transition: all 0.18s ease !important;
      flex-grow: 1 !important;
      min-width: 250px !important;
    }

    .policy-search-box:focus-within {
      background: #FFFFFF !important;
      border-color: #CBD5E1 !important;
      box-shadow: 0 0 0 3px rgba(11, 46, 89, 0.06) !important;
    }

    .policy-search-icon {
      color: #94A3B8 !important;
      font-size: 0.88rem !important;
      flex-shrink: 0 !important;
    }

    .policy-search-input {
      border: none !important;
      background: transparent !important;
      outline: none !important;
      box-shadow: none !important;
      width: 100% !important;
      font-size: 0.86rem !important;
      color: #1E293B !important;
      padding: 0 !important;
    }

    .policy-search-input::placeholder {
      color: #94A3B8 !important;
      font-size: 0.85rem !important;
      font-weight: 400 !important;
    }

    .policy-search-clear {
      border: none !important;
      background: transparent !important;
      color: #94A3B8 !important;
      padding: 0 !important;
      font-size: 0.75rem !important;
      cursor: pointer !important;
      display: inline-flex !important;
      align-items: center !important;
      transition: color 0.15s ease !important;
    }

    .policy-search-clear:hover {
      color: #334155 !important;
    }

    .policy-category-wrapper {
      flex-shrink: 0 !important;
      min-width: 165px !important;
    }

    .policy-category-select {
      background-color: #FFFFFF !important;
      border: 1px solid #E2E8F0 !important;
      border-radius: 8px !important;
      height: 40px !important;
      font-size: 0.86rem !important;
      color: #334155 !important;
      font-weight: 500 !important;
      padding: 0 32px 0 12px !important;
      cursor: pointer !important;
      box-shadow: none !important;
      transition: all 0.18s ease !important;
    }

    .policy-category-select:focus,
    .policy-category-select:hover {
      border-color: #CBD5E1 !important;
      box-shadow: 0 0 0 3px rgba(11, 46, 89, 0.06) !important;
    }

    .policy-date-filter-btn {
      background: #FFFFFF !important;
      border: 1px solid #E2E8F0 !important;
      border-radius: 8px !important;
      height: 40px !important;
      font-size: 0.86rem !important;
      color: #334155 !important;
      font-weight: 500 !important;
      padding: 0 12px !important;
      cursor: pointer !important;
      display: inline-flex !important;
      align-items: center !important;
      justify-content: space-between !important;
      gap: 10px !important;
      min-width: 135px !important;
      box-shadow: none !important;
      white-space: nowrap !important;
      transition: all 0.18s ease !important;
    }

    .policy-date-filter-btn:hover,
    .policy-date-filter-btn:focus {
      border-color: #CBD5E1 !important;
      background: #F8FAFC !important;
      color: #0F172A !important;
      box-shadow: 0 0 0 3px rgba(11, 46, 89, 0.06) !important;
    }

    .policy-date-filter-btn #reportDateFilterIcon {
      color: #475569 !important;
      font-size: 0.86rem !important;
    }

    .policy-date-filter-btn .policy-chevron {
      color: #94A3B8 !important;
      font-size: 0.72rem !important;
    }

    /* Option 1: Civic Slate Metadata Chip (Structured, Neutral, High-End) */
    .report-date-cell,
    .report-date-badge {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      font-size: 0.86rem;
      color: #334155;
      font-weight: 600;
      font-variant-numeric: tabular-nums;
      white-space: nowrap;
      letter-spacing: -0.01em;
      background: #F8FAFC !important;
      border: 1px solid #E2E8F0 !important;
      border-radius: 7px;
      padding: 5px 11px !important;
      box-shadow: 0 1px 2px rgba(0, 0, 0, 0.02) !important;
      transition: all 0.18s cubic-bezier(0.16, 1, 0.3, 1);
    }

    .report-date-cell i,
    .report-date-badge i {
      color: #0B2E59 !important;
      opacity: 0.82;
      font-size: 0.88rem;
      transition: color 0.18s ease, transform 0.18s ease, opacity 0.18s ease;
    }

    .report-date-cell:hover,
    .report-date-badge:hover,
    .clickable-report-row:hover .report-date-cell,
    .clickable-report-row:hover .report-date-badge,
    tr:hover .report-date-cell,
    tr:hover .report-date-badge {
      background: #FFFFFF !important;
      border-color: #CBD5E1 !important;
      box-shadow: 0 2px 5px rgba(11, 46, 89, 0.08) !important;
      transform: translateY(-1px);
    }

    .clickable-report-row:hover .report-date-cell i,
    .clickable-report-row:hover .report-date-badge i,
    tr:hover .report-date-cell i,
    tr:hover .report-date-badge i {
      opacity: 1;
      transform: scale(1.08);
      color: #0B2E59 !important;
    }

    .clickable-report-row:hover .report-date-cell .report-date-text,
    .clickable-report-row:hover .report-date-badge span,
    tr:hover .report-date-cell .report-date-text,
    tr:hover .report-date-badge span {
      color: #0B2E59 !important;
      font-weight: 600;
    }

    /* Refined Civic Category Badges (Light Background + Dark Text) - Compact & Sleek */
    .category-badge-pill {
      display: inline-flex;
      align-items: center;
      gap: 6.5px;
      padding: 5px 13px;
      border-radius: 9999px;
      font-size: 0.83rem;
      font-weight: 600;
      white-space: nowrap;
      letter-spacing: -0.01em;
      line-height: 1.35;
      transition: all 0.18s cubic-bezier(0.16, 1, 0.3, 1);
      box-shadow: 0 1px 2px rgba(0, 0, 0, 0.03);
    }

    .category-badge-pill i {
      font-size: 0.85rem;
      flex-shrink: 0;
    }

    .clickable-report-row:hover .category-badge-pill,
    tr:hover .category-badge-pill {
      transform: translateY(-1px);
      box-shadow: 0 2px 6px rgba(0, 0, 0, 0.07);
    }

    @keyframes spinReport {
      from {
        transform: rotate(0deg);
      }

      to {
        transform: rotate(360deg);
      }
    }

    .spin-report-icon {
      animation: spinReport 0.8s linear infinite !important;
      display: inline-block !important;
    }
  </style>

  <!-- Unified Report Generation Module Card -->
  <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
    <!-- 1. Select Policy Record -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
      <div>
        <h3 class="fw-bold text-dark mb-1 d-flex align-items-center gap-2" style="font-size:1.05rem;">
          <i class="bi bi-journal-check text-primary"></i> 1. Select Policy Record
        </h3>
        <p class="text-muted mb-0 small">Browse policies or click to generate the official legislative report.</p>
      </div>

      <div class="d-flex align-items-center gap-2">
        <span class="badge border rounded-pill px-3 py-2 fw-semibold"
          style="background: #EFF6FF; color: #0B2E59; border-color: #BFDBFE !important; font-size: 0.82rem;">
          <i class="bi bi-collection-fill text-primary me-1.5"></i><span
            id="reportPolicyAvailableCount"><?= $total_report_count ?></span> Policies Available
        </span>
      </div>
    </div>

    <!-- Filter Bar: Live Search, Category & Date Filters -->
    <div class="d-flex flex-wrap flex-md-nowrap align-items-center gap-2.5 mb-3">

        <!-- Search Input -->
        <div class="policy-search-box flex-grow-1">
          <i class="bi bi-search policy-search-icon"></i>
          <input type="text" id="reportPolicySearchInput" class="policy-search-input"
            placeholder="Search policies by title, number or author..." aria-label="Search policies"
            oninput="onReportPolicyFilterChange()">
          <button type="button" class="policy-search-clear" id="reportPolicyClearSearchBtn" title="Clear search"
            style="display: none;" onclick="clearReportPolicySearch()">
            <i class="bi bi-x-lg"></i>
          </button>
        </div>

        <!-- All Categories Dropdown -->
        <div class="policy-category-wrapper">
          <select id="reportPolicyCategorySelect" class="form-select policy-category-select"
            aria-label="Filter by category" onchange="onReportPolicyFilterChange()">
            <option value="">All Categories</option>
            <option value="Health and Sanitation">Health and Sanitation</option>
            <option value="Civil Registry and Public Services">Civil Registry and Public Services</option>
            <option value="Education and Employment">Education and Employment</option>
            <option value="Social Welfare and Community Affairs">Social Welfare and Community Affairs</option>
            <option value="Infrastructure, Traffic and Environment">Infrastructure, Traffic and Environment</option>
            <option value="Other">Other</option>
            <?php
            $standardCats = [
              'health and sanitation',
              'civil registry and public services',
              'education and employment',
              'social welfare and community affairs',
              'infrastructure, traffic and environment',
              'other'
            ];
            $extraCats = [];
            foreach ($report_policies as $p) {
              $catName = trim($p['category'] ?? '');
              if ($catName !== '' && !in_array(strtolower($catName), $standardCats) && !in_array($catName, $extraCats)) {
                $extraCats[] = $catName;
              }
            }
            foreach ($extraCats as $ec):
              ?>
              <option value="<?= htmlspecialchars($ec) ?>"><?= htmlspecialchars($ec) ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <!-- Date Filter Dropdown -->
        <div class="dropdown position-relative" id="reportDateFilterInputGroup" style="flex-shrink: 0;">
          <button class="btn policy-date-filter-btn" type="button" id="reportDateFilterDropdownBtn"
            onclick="toggleReportDateDropdown(event)">
            <span class="d-inline-flex align-items-center gap-2">
              <i class="bi bi-calendar3" id="reportDateFilterIcon"></i>
              <span id="reportDateFilterLabel">Filter Date</span>
              <span class="badge bg-primary rounded-pill px-2 py-0.5" id="reportDateFilterActiveBadge"
                style="font-size: 0.68rem; display: none;">Active</span>
            </span>
            <i class="bi bi-chevron-down policy-chevron"></i>
          </button>

          <div class="dropdown-menu dropdown-menu-end shadow-lg border rounded-4 p-3"
            id="reportDateFilterDropdownMenu"
            style="width: 320px; z-index: 1060; position: absolute; right: 0; top: 100%; margin-top: 6px; display: none;"
            aria-labelledby="reportDateFilterDropdownBtn">
            <div class="d-flex align-items-center justify-content-between pb-2 mb-2 border-bottom">
              <span class="fw-bold small text-dark"><i class="bi bi-calendar3 me-1.5 text-primary"></i>Filter by
                Date</span>
              <a href="javascript:void(0);" id="reportDateResetLink"
                onclick="applyReportDateFilter('', 'Filter Date');"
                class="text-danger small text-decoration-none fw-semibold" style="display: none;">Reset</a>
            </div>

            <!-- Quick Presets -->
            <div class="mb-3">
              <div class="text-muted fw-semibold small mb-2"
                style="font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.5px;">Quick Presets</div>
              <div class="d-flex flex-wrap gap-1.5" id="reportDatePresetGroup">
                <button type="button" onclick="applyReportDateFilter('', 'All Dates')"
                  class="btn btn-sm rounded-3 btn-primary text-white date-preset-btn" data-type=""
                  style="font-size: 0.78rem;">All Dates</button>
                <button type="button" onclick="applyReportDateFilter('today', 'Today')"
                  class="btn btn-sm rounded-3 btn-light text-dark border date-preset-btn" data-type="today"
                  style="font-size: 0.78rem;">Today</button>
                <button type="button" onclick="applyReportDateFilter('last_7_days', 'Last 7 Days')"
                  class="btn btn-sm rounded-3 btn-light text-dark border date-preset-btn" data-type="last_7_days"
                  style="font-size: 0.78rem;">Last 7 Days</button>
                <button type="button" onclick="applyReportDateFilter('last_30_days', 'Last 30 Days')"
                  class="btn btn-sm rounded-3 btn-light text-dark border date-preset-btn" data-type="last_30_days"
                  style="font-size: 0.78rem;">Last 30 Days</button>
                <button type="button" onclick="applyReportDateFilter('this_month', 'This Month')"
                  class="btn btn-sm rounded-3 btn-light text-dark border date-preset-btn" data-type="this_month"
                  style="font-size: 0.78rem;">This Month</button>
                <button type="button" onclick="applyReportDateFilter('last_month', 'Last Month')"
                  class="btn btn-sm rounded-3 btn-light text-dark border date-preset-btn" data-type="last_month"
                  style="font-size: 0.78rem;">Last Month</button>
              </div>
            </div>

            <!-- By Year -->
            <div class="mb-3">
              <div class="text-muted fw-semibold small mb-2"
                style="font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.5px;">By Year</div>
              <div class="d-flex gap-1.5" id="reportDateYearGroup">
                <button type="button" onclick="applyReportDateFilter('2026', '2026')"
                  class="btn btn-sm rounded-3 flex-fill btn-light text-dark border date-preset-btn" data-type="2026"
                  style="font-size: 0.78rem;">2026</button>
                <button type="button" onclick="applyReportDateFilter('2025', '2025')"
                  class="btn btn-sm rounded-3 flex-fill btn-light text-dark border date-preset-btn" data-type="2025"
                  style="font-size: 0.78rem;">2025</button>
                <button type="button" onclick="applyReportDateFilter('2024', '2024')"
                  class="btn btn-sm rounded-3 flex-fill btn-light text-dark border date-preset-btn" data-type="2024"
                  style="font-size: 0.78rem;">2024</button>
              </div>
            </div>

            <!-- Custom Range -->
            <div class="pt-2 border-top">
              <div class="text-muted fw-semibold small mb-2"
                style="font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.5px;">Custom Range</div>
              <div class="row g-1.5 mb-2">
                <div class="col-6">
                  <input type="date" id="reportCustomDateFrom" class="form-control form-control-sm rounded-2"
                    style="font-size: 0.75rem;" placeholder="From">
                </div>
                <div class="col-6">
                  <input type="date" id="reportCustomDateTo" class="form-control form-control-sm rounded-2"
                    style="font-size: 0.75rem;" placeholder="To">
                </div>
              </div>
              <button type="button" onclick="applyReportCustomDateRange()"
                class="btn btn-primary btn-sm rounded-3 w-100 fw-semibold" style="font-size: 0.8rem;">
                <i class="bi bi-check2 me-1"></i> Apply Range
              </button>
            </div>
          </div>
        </div>

      </div>

    <div class="table-responsive border rounded-4 overflow-hidden mb-3">
      <table class="table table-hover align-middle mb-0" style="font-size:0.88rem;">
        <thead class="policy-table-thead">
          <tr>
            <th class="py-3 px-3 text-uppercase" style="width: 44%;">Policy Title</th>
            <th class="py-3 px-3 text-uppercase" style="width: 22%;">Category</th>
            <th class="py-3 px-3 text-uppercase" style="width: 16%;">Date Uploaded</th>
            <th class="py-3 px-3 text-center text-uppercase" style="width: 18%;">Action</th>
          </tr>
        </thead>
        <tbody id="reportPolicyTableBody">
          <?php
          if (!function_exists('renderPolicyCategoryBadge')) {
            function renderPolicyCategoryBadge($category)
            {
              $cat = trim($category ?? '');
              $lower = strtolower($cat);

              // Exact requested color pairs (light background + dark text):
              // 1. Health and Sanitation (#E1F5EE, #085041)
              if (strpos($lower, 'health') !== false || strpos($lower, 'sanitation') !== false || strpos($lower, 'medical') !== false) {
                $bg = '#E1F5EE';
                $text = '#085041';
                $border = '#9FE1CB';
                $icon = 'bi-heart-pulse-fill';
                $label = !empty($cat) ? $cat : 'Health and Sanitation';
              }
              // 2. Civil Registry and Public Services (#E6F1FB, #0C447C)
              elseif (strpos($lower, 'civil') !== false || strpos($lower, 'registry') !== false || strpos($lower, 'public service') !== false || strpos($lower, 'governance') !== false || strpos($lower, 'legal') !== false) {
                $bg = '#E6F1FB';
                $text = '#0C447C';
                $border = '#B5D7F8';
                $icon = 'bi-file-earmark-person-fill';
                $label = !empty($cat) ? $cat : 'Civil Registry and Public Services';
              }
              // 3. Education and Employment (#EEEDFE, #3C3489)
              elseif (strpos($lower, 'education') !== false || strpos($lower, 'employment') !== false || strpos($lower, 'school') !== false || strpos($lower, 'labor') !== false || strpos($lower, 'livelihood') !== false) {
                $bg = '#EEEDFE';
                $text = '#3C3489';
                $border = '#CBC6FC';
                $icon = 'bi-mortarboard-fill';
                $label = !empty($cat) ? $cat : 'Education and Employment';
              }
              // 4. Social Welfare and Community Affairs (#FAECE7, #712B13)
              elseif (strpos($lower, 'social') !== false || strpos($lower, 'welfare') !== false || strpos($lower, 'community') !== false) {
                $bg = '#FAECE7';
                $text = '#712B13';
                $border = '#F3C4B6';
                $icon = 'bi-people-fill';
                $label = !empty($cat) ? $cat : 'Social Welfare and Community Affairs';
              }
              // 5. Infrastructure, Traffic and Environment (#EAF3DE, #27500A)
              elseif (strpos($lower, 'infrastructure') !== false || strpos($lower, 'traffic') !== false || strpos($lower, 'environment') !== false || strpos($lower, 'transport') !== false || strpos($lower, 'mobility') !== false) {
                $bg = '#EAF3DE';
                $text = '#27500A';
                $border = '#C8E2AE';
                $icon = 'bi-buildings';
                $label = !empty($cat) ? $cat : 'Infrastructure, Traffic and Environment';
              }
              // 6. Other (#F1EFE8, #444441)
              else {
                $bg = '#F1EFE8';
                $text = '#444441';
                $border = '#DCD7C9';
                $icon = 'bi-tag-fill';
                $label = !empty($cat) ? $cat : 'Other';
              }

              return '<span class="category-badge-pill" style="background-color: ' . $bg . ' !important; color: ' . $text . ' !important; border: 1px solid ' . $border . ' !important;" title="' . htmlspecialchars($label) . '">' .
                '<i class="bi ' . $icon . '" style="color: ' . $text . ' !important; opacity: 0.9;"></i>' .
                '<span>' . htmlspecialchars($label) . '</span>' .
                '</span>';
            }
          }
          foreach ($report_policies as $i => $pol):
            $dateStr = !empty($pol['created_at']) ? date('M d, Y', strtotime($pol['created_at'])) : '—';
            $dateYmd = !empty($pol['created_at']) ? date('Y-m-d', strtotime($pol['created_at'])) : '';
            $isFirst = ($i === 0);

            $rawSummary = $pol['ai_summary'] ?? '';
            $summary = '';
            if (!empty($rawSummary)) {
              if (is_string($rawSummary) && (strpos(trim($rawSummary), '{') === 0 || strpos(trim($rawSummary), '[')) === 0) {
                $jsonDecoded = json_decode($rawSummary, true);
                if (is_array($jsonDecoded)) {
                  $summary = $jsonDecoded['executive_summary'] ?? $jsonDecoded['summary'] ?? '';
                }
              }
              if (empty($summary)) {
                $summary = is_string($rawSummary) ? $rawSummary : '';
              }
            }
            if (empty($summary)) {
              $summary = 'This policy contains official legislative data and impact evaluation findings for ' . $pol['title'] . '.';
            }

            if ($isFirst) {
              $initialAdminSummary = $summary;
            }

            $risk = !empty($pol['risk_level']) ? $pol['risk_level'] : 'Low Risk';
            $recText = !empty($pol['ai_recommendation']) ? $pol['ai_recommendation'] : 'Proceed with implementation and continue monitoring the effectiveness of the policy.';

            $current_status = $pol['computed_status'] ?? 'Approved';
            $eval_state = $pol['status_key'] ?? 'approved';
            $is_approved = ($current_status === 'Approved');

            $policyData = [
              'id' => (int) $pol['id'],
              'policy_id' => (int) $pol['id'],
              'title' => $pol['title'],
              'policy_title' => $pol['title'],
              'category' => $pol['category'] ?? 'General Legislation',
              'status' => $current_status,
              'date' => $dateStr,
              'date_uploaded' => $dateStr,
              'summary' => $summary,
              'risk' => $risk,
              'recommendation' => $recText,
              'author' => $pol['author'] ?? 'City Council of Manila',
              'file_path' => $pol['file_path'] ?? '',
              'ordinance_number' => $pol['ordinance_number'] ?? '',
              'report_type' => $is_approved ? 'Evaluation Report' : 'Policy Research Brief'
            ];
            $policyJson = json_encode($policyData, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
            ?>
            <tr class="report-policy-row clickable-report-row" style="cursor:pointer;"
              data-eval-state="<?= $eval_state ?>"
              data-title="<?= htmlspecialchars(strtolower(trim($pol['title'] ?? '')), ENT_QUOTES, 'UTF-8') ?>"
              data-author="<?= htmlspecialchars(strtolower(trim($pol['author'] ?? '')), ENT_QUOTES, 'UTF-8') ?>"
              data-number="<?= htmlspecialchars(strtolower(trim($pol['ordinance_number'] ?? '')), ENT_QUOTES, 'UTF-8') ?>"
              data-category="<?= htmlspecialchars(strtolower(trim($pol['category'] ?? '')), ENT_QUOTES, 'UTF-8') ?>"
              data-date="<?= $dateYmd ?>" data-policy='<?= htmlspecialchars($policyJson, ENT_QUOTES, 'UTF-8') ?>'
              onclick="openPolicyRowReport(this)">
              <td class="py-3 px-3">
                <div class="d-flex align-items-center gap-2.5">
                  <div
                    class="rounded-3 p-1.5 <?= $is_approved ? 'bg-primary bg-opacity-10 text-primary' : 'bg-warning bg-opacity-10 text-warning' ?> d-flex align-items-center justify-content-center flex-shrink-0"
                    style="width: 32px; height: 32px;">
                    <i class="bi <?= $is_approved ? 'bi-file-earmark-check-fill' : 'bi-file-earmark-text' ?> fs-6"></i>
                  </div>
                  <div class="min-w-0">
                    <a href="javascript:void(0)"
                      class="fw-semibold text-dark text-decoration-none policy-title-link text-truncate d-block"
                      onclick="event.stopPropagation(); openPolicyRowReport(this.closest('tr'));">
                      <?= htmlspecialchars($pol['title']) ?>
                    </a>
                  </div>
                </div>
              </td>
              <td class="py-3 px-3">
                <?= renderPolicyCategoryBadge($pol['category'] ?? '') ?>
              </td>

              <td class="py-3 px-3">
                <div class="report-date-cell">
                  <i class="bi bi-calendar3"></i>
                  <span class="report-date-text"><?= $dateStr ?></span>
                </div>
              </td>
              <td class="text-center py-3 px-3">
                <button type="button" class="btn btn-sm btn-report-download"
                  onclick="event.stopPropagation(); openPolicyRowReport(this.closest('tr'));"
                  title="Download / View Official Legislative Report">
                  <i class="bi bi-download"></i>
                  <span>Download / View</span>
                </button>
              </td>
            </tr>
          <?php endforeach; ?>
          <tr id="reportPolicyNoDataRow" style="display: none;">
            <td colspan="4" class="text-center py-5 text-muted bg-white">
              <div class="py-3">
                <i class="bi bi-search text-secondary opacity-50 d-block mb-2" style="font-size: 2rem;"></i>
                <h6 class="fw-bold text-dark mb-1">No matching policies found</h6>
                <p class="small text-muted mb-0">Try adjusting your search query or selecting a different category.</p>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
    <div class="d-flex align-items-center justify-content-between pt-1">
      <small class="text-muted fw-medium" id="reportPoliciesSummaryText">Showing 1 to
        <?= min(10, count($report_policies)) ?> of <?= count($report_policies) ?> records</small>
      <div class="d-flex align-items-center gap-1" id="reportPolicyPagination">
        <button type="button" class="btn btn-sm btn-light border rounded-2 pagination-step-btn" id="reportPolicyPrevBtn"
          onclick="changeReportPolicyPage(-1)" title="Previous page"
          style="width:32px!important; height:32px!important; min-width:32px!important; max-width:32px!important; padding:0!important; display:inline-flex!important; align-items:center!important; justify-content:center!important;"><i
            class="bi bi-chevron-left"></i></button>
        <div id="reportPolicyPageNumbers" class="d-flex align-items-center gap-1">
          <button type="button" class="btn btn-sm btn-primary rounded-2 pagination-step-btn fw-bold"
            style="width:32px!important; height:32px!important; min-width:32px!important; max-width:32px!important; padding:0!important; display:inline-flex!important; align-items:center!important; justify-content:center!important;">1</button>
        </div>
        <button type="button" class="btn btn-sm btn-light border rounded-2 pagination-step-btn" id="reportPolicyNextBtn"
          onclick="changeReportPolicyPage(1)" title="Next page"
          style="width:32px!important; height:32px!important; min-width:32px!important; max-width:32px!important; padding:0!important; display:inline-flex!important; align-items:center!important; justify-content:center!important;"><i
            class="bi bi-chevron-right"></i></button>
      </div>
    </div>

    <!-- Clean Divider Merging Section 1 and Section 2 seamlessly -->
    <hr class="my-4" style="border-color: #e2e8f0; opacity: 0.7;">

    <!-- 2. Generated Reports & Comparative Analyses -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
      <div>
        <h3 class="fw-bold text-dark mb-1 d-flex align-items-center gap-2" style="font-size:1.05rem;">
          <i class="bi bi-clock-history text-primary"></i> 2. Generated Reports &amp; Comparative Analyses
        </h3>
        <p class="text-muted mb-0 small">Access, browse, search, and download legislative policy evaluations and cross-city benchmarks.</p>
      </div>
      <div class="d-flex align-items-center gap-2">
        <span class="badge border rounded-pill px-3 py-1.5 fw-semibold"
          style="background: #EFF6FF; color: #0B2E59; border-color: #BFDBFE !important; font-size: 0.8rem;">
          <i class="bi bi-file-earmark-check text-primary me-1"></i><span id="recentGeneratedReportsTotalBadge">0</span> Reports Available
        </span>
      </div>
    </div>

    <!-- Filter & Live Search Bar for Generated Reports -->
    <div class="d-flex flex-wrap flex-md-nowrap align-items-center gap-2.5 mb-3">
      <!-- Search Input -->
      <div class="policy-search-box flex-grow-1">
        <i class="bi bi-search policy-search-icon"></i>
        <input type="text" id="recentReportsSearchInput" class="policy-search-input"
          placeholder="Search generated reports by file name, policy subject, or date..."
          aria-label="Search generated reports" oninput="onRecentReportsSearchInput()">
        <button type="button" class="policy-search-clear" id="recentReportsClearSearchBtn" title="Clear search"
          style="display: none;" onclick="clearRecentReportsSearch()">
          <i class="bi bi-x-lg"></i>
        </button>
      </div>

      <!-- Filter Pills -->
      <div class="btn-group btn-group-sm p-1 bg-light rounded-pill border shadow-2xs flex-shrink-0" role="group">
        <button type="button" class="btn btn-sm rounded-pill px-3 py-1 fw-bold text-white shadow-sm"
          id="filterReportAll" style="background:#0B2E59;" onclick="filterReportsTable('All', this)">All</button>
        <button type="button" class="btn btn-sm rounded-pill px-3 py-1 text-secondary" id="filterReportEval"
          style="background:transparent;" onclick="filterReportsTable('Evaluation', this)">Evaluations</button>
        <button type="button" class="btn btn-sm rounded-pill px-3 py-1 text-secondary" id="filterReportBench"
          style="background:transparent;" onclick="filterReportsTable('Benchmark', this)">Benchmarks</button>
      </div>
    </div>
    <div class="table-responsive border rounded-4 overflow-hidden mb-2">
      <table class="table table-hover align-middle mb-0" id="recentGeneratedReportsTable"
        style="font-size:0.88rem; table-layout: fixed; width: 100%;">
        <colgroup>
          <col style="width: 28%;">
          <col style="width: 32%;">
          <col style="width: 14%;">
          <col style="width: 14%;">
          <col style="width: 12%;">
        </colgroup>
        <thead style="background-color: #f8fafc; border-bottom: 2px solid #e2e8f0;">
          <tr>
            <th class="py-3 px-3 text-uppercase text-dark fw-bold text-truncate"
              style="font-size: 0.88rem; letter-spacing: 0.03em; color: #000000 !important;">Report Name</th>
            <th class="py-3 px-3 text-uppercase text-dark fw-bold text-truncate"
              style="font-size: 0.88rem; letter-spacing: 0.03em; color: #000000 !important;">Policy / Subject</th>
            <th class="py-3 px-3 text-uppercase text-dark fw-bold text-nowrap"
              style="font-size: 0.88rem; letter-spacing: 0.03em; color: #000000 !important;">Type</th>
            <th class="py-3 px-3 text-uppercase text-dark fw-bold text-nowrap"
              style="font-size: 0.88rem; letter-spacing: 0.03em; color: #000000 !important;">Date Generated</th>
            <th class="py-3 px-3 text-end text-uppercase text-dark fw-bold text-nowrap"
              style="font-size: 0.88rem; letter-spacing: 0.03em; color: #000000 !important;">Action</th>
          </tr>
        </thead>
        <tbody id="recentGeneratedReportsBody">
          <!-- Dynamically populated when reports are generated -->
        </tbody>
      </table>
    </div>
    <div class="d-flex align-items-center justify-content-between pt-2">
      <small class="text-muted fw-medium" id="recentGeneratedReportsCount">Showing 0 records</small>
      <div class="d-flex align-items-center gap-1" id="recentReportsPagination">
        <button type="button" class="btn btn-sm btn-light border rounded-2 pagination-step-btn"
          id="recentReportsPrevBtn" onclick="changeRecentReportsPage(-1)" title="Previous page"
          style="width:32px!important; height:32px!important; min-width:32px!important; max-width:32px!important; padding:0!important; display:inline-flex!important; align-items:center!important; justify-content:center!important;"><i
            class="bi bi-chevron-left"></i></button>
        <div id="recentReportsPageNumbers" class="d-flex align-items-center gap-1">
          <button type="button" class="btn btn-sm btn-primary rounded-2 pagination-step-btn fw-bold"
            style="width:32px!important; height:32px!important; min-width:32px!important; max-width:32px!important; padding:0!important; display:inline-flex!important; align-items:center!important; justify-content:center!important;">1</button>
        </div>
        <button type="button" class="btn btn-sm btn-light border rounded-2 pagination-step-btn"
          id="recentReportsNextBtn" onclick="changeRecentReportsPage(1)" title="Next page"
          style="width:32px!important; height:32px!important; min-width:32px!important; max-width:32px!important; padding:0!important; display:inline-flex!important; align-items:center!important; justify-content:center!important;"><i
            class="bi bi-chevron-right"></i></button>
      </div>
    </div>
  </div>

  <!-- Official Document Report Viewer Modal -->
  <div class="modal fade" id="reportDocumentViewerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" style="max-width: 880px;">
      <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden bg-white">
        <!-- Clean & Executive Header -->
        <div class="modal-header border-bottom px-4 py-3 bg-white d-flex align-items-center justify-content-between">
          <div class="d-flex align-items-center gap-2.5">
            <div class="rounded-circle d-flex align-items-center justify-content-center"
              style="width: 38px; height: 38px; background: rgba(11, 46, 89, 0.08); color: #0B2E59; flex-shrink: 0;">
              <i class="bi bi-file-earmark-text-fill fs-5"></i>
            </div>
            <div>
              <h5 class="modal-title fw-bold text-dark mb-0 fs-6" id="reportViewerModalTitle">Official Legislative Document</h5>
              <span class="text-muted" style="font-size: 0.76rem;">Official Legislative Services Preview</span>
            </div>
          </div>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <!-- Document Canvas / Paper Body -->
        <div class="modal-body p-3 p-md-4" style="max-height: 72vh; overflow-y: auto; background: #f1f5f9;">
          <div id="reportViewerModalDocumentBody" class="bg-white p-4 p-md-5 rounded-4 border shadow-sm mx-auto"
            style="max-width: 760px; box-shadow: 0 4px 20px rgba(0,0,0,0.05);">
            <!-- Rendered document will be injected here -->
          </div>
        </div>
        <!-- Clean Bottom Actions Footer -->
        <div class="modal-footer border-top px-4 py-2.5 bg-white d-flex flex-wrap align-items-center justify-content-between" style="gap: 10px;">
          <div class="d-flex align-items-center gap-2">
            <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill px-2.5 py-1 d-inline-flex align-items-center gap-1.5 fw-semibold" style="font-size: 0.76rem;">
              <i class="bi bi-shield-check"></i> Official Document
            </span>
            <span class="text-muted small fw-medium d-none d-sm-inline" style="font-size: 0.78rem;">City Council of Manila</span>
          </div>
          <div class="d-flex align-items-center flex-wrap" style="gap: 8px;">
            <!-- View Original Document Button -->
            <button type="button"
              class="btn btn-sm text-white fw-semibold d-inline-flex align-items-center gap-1.5 shadow-2xs"
              style="background: #0284c7; border: 1px solid #0284c7; font-size: 0.82rem; padding: 5px 13px; border-radius: 6px;"
              id="reportModalViewOriginalBtn" title="Open the original uploaded source document">
              <i class="bi bi-box-arrow-up-right" style="font-size: 0.85rem;"></i>
              <span>View Original</span>
            </button>
            <!-- Download Word -->
            <button type="button"
              class="btn btn-sm text-white fw-semibold d-inline-flex align-items-center gap-1.5 shadow-2xs"
              style="background: #2b579a; border: 1px solid #2b579a; font-size: 0.82rem; padding: 5px 13px; border-radius: 6px;"
              id="reportModalDownloadDocxBtn" title="Download Word Document">
              <i class="bi bi-file-earmark-word-fill" style="font-size: 0.85rem;"></i>
              <span>Word (.docx)</span>
            </button>
            <!-- Download PDF (Red) -->
            <button type="button"
              class="btn btn-sm text-white fw-semibold d-inline-flex align-items-center gap-1.5 shadow-2xs"
              style="background: #dc2626; border: 1px solid #dc2626; font-size: 0.82rem; padding: 5px 13px; border-radius: 6px;"
              id="reportModalDownloadPdfBtn" title="Download PDF Document">
              <i class="bi bi-file-earmark-pdf-fill" style="font-size: 0.85rem;"></i>
              <span>Download PDF</span>
            </button>
            <!-- Print Button (Balanced Compact Size) -->
            <button type="button"
              class="btn btn-sm text-white fw-semibold d-inline-flex align-items-center gap-1.5 shadow-2xs"
              style="background: #0B2E59; border: 1px solid #0B2E59; font-size: 0.82rem; padding: 5px 14px; border-radius: 6px;"
              id="reportModalPrintBtn" title="Print Document">
              <i class="bi bi-printer-fill" style="font-size: 0.85rem;"></i>
              <span>Print</span>
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>

</section>

<script>
  (function () {
    // Live date/time in header
    var now = new Date();
    var dateEl = document.getElementById('reportCurrentDate');
    var timeEl = document.getElementById('reportCurrentTime');
    if (dateEl) dateEl.innerHTML = '<i class="bi bi-calendar-event me-1 text-primary"></i>' +
      now.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
    if (timeEl) timeEl.textContent = now.toLocaleDateString('en-US', { weekday: 'long' }) + ', ' +
      now.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });

    // Highlight first row on load
    var firstRow = document.querySelector('#reportPolicyTableBody .report-policy-row');
    if (firstRow) firstRow.style.backgroundColor = '#EFF6FF';

    // Render dynamic reports table on page load
    if (typeof renderRecentGeneratedReportsTable === 'function') {
      renderRecentGeneratedReportsTable();
    }
  })();

  // Active report data object
  var _report = {
    title: '<?= addslashes(htmlspecialchars($report_policies[0]['title'] ?? '')) ?>',
    category: '<?= addslashes(htmlspecialchars($report_policies[0]['category'] ?? '')) ?>',
    status: '<?= addslashes(htmlspecialchars($report_policies[0]['status'] ?? 'Draft')) ?>',
    date: '<?= isset($report_policies[0]['created_at']) ? date('M d, Y', strtotime($report_policies[0]['created_at'])) : '—' ?>',
    summary: '<?= addslashes(htmlspecialchars($initialAdminSummary ?? '')) ?>',
    risk: 'Favorable for Implementation',
    recommendation: 'Proceed with implementation and continue monitoring the effectiveness of the policy.'
  };

  function formatEvaluationResult(riskRaw) {
    var raw = (riskRaw || '').toString();
    if (raw.indexOf('High') !== -1 || raw.indexOf('Reject') !== -1 || raw.indexOf('Conflict') !== -1) {
      return {
        text: 'Requires Committee Review',
        bg: '#fee2e2',
        color: '#b91c1c',
        border: '#fca5a5',
        bsClass: 'danger'
      };
    }
    if (raw.indexOf('Moderate') !== -1 || raw.indexOf('Medium') !== -1 || raw.indexOf('Amend') !== -1) {
      return {
        text: 'Recommended with Amendments',
        bg: '#fef3c7',
        color: '#b45309',
        border: '#fde68a',
        bsClass: 'warning'
      };
    }
    return {
      text: 'Favorable for Implementation',
      bg: '#dcfce7',
      color: '#15803d',
      border: '#bbf7d0',
      bsClass: 'success'
    };
  }

  function formatBenchmarkResult(riskRaw) {
    var raw = (riskRaw || '').toString();
    if (raw.indexOf('High') !== -1 && raw.indexOf('Potential') === -1) {
      return {
        text: 'High Divergence (Requires Major Restructuring)',
        bg: '#fee2e2',
        color: '#b91c1c',
        border: '#fca5a5'
      };
    }
    if (raw.indexOf('Moderate') !== -1 || raw.indexOf('Medium') !== -1) {
      return {
        text: 'Moderate Alignment (Requires Local Adaptation)',
        bg: '#fef3c7',
        color: '#b45309',
        border: '#fde68a'
      };
    }
    return {
      text: 'High Harmonization Potential (Favorable)',
      bg: '#f0fdfa',
      color: '#0f766e',
      border: '#ccfbf1'
    };
  }

  function selectReportPolicy(trEl, isFirst, title, category, status, date, summary, risk, recommendation) {
    _report = { title: title, category: category, status: status, date: date, summary: summary, risk: risk, recommendation: recommendation };

    // Update table highlight + radio
    document.querySelectorAll('#reportPolicyTableBody .report-policy-row').forEach(function (row) {
      row.style.backgroundColor = '';
      var r = row.querySelector('input[type="radio"]');
      if (r) r.checked = false;
    });
    if (trEl) {
      trEl.style.backgroundColor = '#EFF6FF';
      var radio = trEl.querySelector('input[type="radio"]');
      if (radio) radio.checked = true;
    }

    // Update Preview panel
    var titleEl = document.getElementById('prevPolicyTitle');
    var catEl = document.getElementById('prevCategory');
    var sumEl = document.getElementById('prevAISummary');
    var evalEl = document.getElementById('prevEvalResult');
    var recEl = document.getElementById('prevRecommendation');

    if (titleEl) titleEl.textContent = title;
    if (catEl) catEl.textContent = category;
    if (sumEl) sumEl.textContent = summary;
    if (recEl) recEl.textContent = recommendation;

    if (evalEl) {
      var evalObj = formatEvaluationResult(risk);
      evalEl.innerHTML = '<span class="badge bg-' + evalObj.bsClass + ' bg-opacity-10 text-' + evalObj.bsClass +
        ' border border-' + evalObj.bsClass + ' border-opacity-20 px-3 py-1.5 rounded-3 fw-semibold">' + evalObj.text + '</span>';
    }
  }

  function esc(t) {
    if (t === undefined || t === null) return '';
    return String(t).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
  }

  function buildSharedReportTemplate(repObj, logoUrl) {
    var rep = repObj || _report;
    var isBenchmark = false;
    var rType = rep.report_type || '';
    var rTitle = rep.title || rep.policy_title || '';
    if (rType.indexOf('Benchmark') !== -1 || rType.indexOf('Comparison') !== -1 || rTitle.indexOf(' vs') !== -1 || rTitle.indexOf(' vs.') !== -1 || rTitle.indexOf('Comparative') !== -1) {
      isBenchmark = true;
    }

    var nowStr = new Date().toLocaleString('en-US', { dateStyle: 'full', timeStyle: 'short' });

    if (isBenchmark) {
      // --- BENCHMARK & COMPARISON REPORT TEMPLATE ---
      var parts = rTitle.split(/\s+vs\.?\s+/i);
      var policyA = parts[0] ? parts[0].trim() : rTitle;
      var policyB = parts[1] ? parts[1].trim() : 'Comparative Benchmark Standard (Quezon City / Regional Model)';
      var bmRes = formatBenchmarkResult(rep.risk);

      return '' +
        '<div style="max-width:740px; margin:0 auto; background:#ffffff; padding:15px; font-family:\'Segoe UI\', Arial, sans-serif; color:#0f172a; text-align:left;">' +
        '  <div style="text-align:center; margin-bottom:18px;">' +
        '    <img src="' + logoUrl + '" width="65" height="65" style="width:65px; height:65px; object-fit:contain; margin-bottom:8px;" alt="Manila Seal">' +
        '    <h1 style="color:#0B2E59; font-weight:800; font-size:1.55rem; letter-spacing:-0.5px; margin:0 0 4px 0;">LUNGSOD NG MAYNILA</h1>' +
        '    <div style="color:#475569; font-weight:600; font-size:0.88rem; margin-bottom:12px;">City of Manila — Legislative Research &amp; Policy Benchmarking Services</div>' +
        '    <div style="margin-bottom:6px;">' +
        '      <span style="background:#0f766e; color:#ffffff; padding:6px 18px; font-weight:700; text-transform:uppercase; letter-spacing:1px; font-size:0.82rem; border-radius:4px; display:inline-block;">Official Inter-City Benchmark &amp; Comparative Analysis</span>' +
        '    </div>' +
        '    <div style="color:#64748b; font-size:0.78rem; margin-top:6px;">Date Generated: ' + nowStr + '</div>' +
        '  </div>' +
        '  <div style="border-bottom:2px solid #0f766e; margin-bottom:20px;"></div>' +

        '  <table width="100%" style="width:100%; border-collapse:collapse; margin-bottom:20px; font-size:0.88rem;">' +
        '    <tbody>' +
        '      <tr>' +
        '        <th width="170" style="padding:10px 14px; border:1px solid #cbd5e1; background-color:#f0fdfa; font-weight:700; color:#0f766e; text-align:left;">Benchmark Title</th>' +
        '        <td style="padding:10px 14px; border:1px solid #cbd5e1; font-weight:700; color:#0f172a;" colspan="3">' + esc(rTitle) + '</td>' +
        '      </tr>' +
        '      <tr>' +
        '        <th width="170" style="padding:10px 14px; border:1px solid #cbd5e1; background-color:#f0fdfa; font-weight:700; color:#0f766e; text-align:left;">Original Document</th>' +
        '        <td style="padding:10px 14px; border:1px solid #cbd5e1; color:#0f172a;" colspan="3">' +
        ((rep.id || rep.policy_id || (rep.file_path && rep.file_path.trim() !== '')) ?
          ('<a href="' + ((rep.id || rep.policy_id) ? ('../backend/view_policy_document.php?id=' + encodeURIComponent(rep.id || rep.policy_id)) : ('../assets/uploads/policies/' + encodeURIComponent(rep.file_path))) + '" target="_blank" style="color:#0f766e; font-weight:600; text-decoration:underline; display:inline-flex; align-items:center; gap:5px;"><i class="bi bi-box-arrow-up-right"></i> ' + esc(rep.file_path || 'View Full Document') + ' (View Original Document)</a>') :
          ('<span style="color:#64748b; font-style:italic;"><i class="bi bi-archive me-1"></i> Official City Council Legislative Archive (Digital Record)</span>')
        ) +
        '        </td>' +
        '      </tr>' +
        '      <tr>' +
        '        <th width="170" style="padding:10px 14px; border:1px solid #cbd5e1; background-color:#f0fdfa; font-weight:700; color:#0f766e; text-align:left;">Primary Policy (A)</th>' +
        '        <td style="padding:10px 14px; border:1px solid #cbd5e1; color:#1e293b; width:35%;">' + esc(policyA) + ' <span style="font-size:0.75rem; color:#64748b; font-weight:600;">(Manila)</span></td>' +
        '        <th width="140" style="padding:10px 14px; border:1px solid #cbd5e1; background-color:#f0fdfa; font-weight:700; color:#0f766e; text-align:left;">Benchmark (B)</th>' +
        '        <td style="padding:10px 14px; border:1px solid #cbd5e1; color:#1e293b;">' + esc(policyB) + '</td>' +
        '      </tr>' +
        '      <tr>' +
        '        <th style="padding:10px 14px; border:1px solid #cbd5e1; background-color:#f0fdfa; font-weight:700; color:#0f766e; text-align:left;">Policy Sector</th>' +
        '        <td style="padding:10px 14px; border:1px solid #cbd5e1; color:#1e293b;">' + esc(rep.category || 'Environmental & Urban Governance') + '</td>' +
        '        <th style="padding:10px 14px; border:1px solid #cbd5e1; background-color:#f0fdfa; font-weight:700; color:#0f766e; text-align:left;">Harmonization Viability</th>' +
        '        <td style="padding:10px 14px; border:1px solid #cbd5e1;"><span style="background:' + bmRes.bg + '; color:' + bmRes.color + '; border:1px solid ' + bmRes.border + '; padding:3px 10px; border-radius:4px; font-weight:700; font-size:0.8rem;">' + esc(bmRes.text) + '</span></td>' +
        '      </tr>' +
        '    </tbody>' +
        '  </table>' +

        '  <h4 style="color:#0f766e; font-size:0.95rem; font-weight:700; margin:18px 0 10px 0; text-transform:uppercase; letter-spacing:0.5px;">Comparative Dimension Matrix</h4>' +
        '  <table width="100%" style="width:100%; border-collapse:collapse; margin-bottom:20px; font-size:0.85rem;">' +
        '    <thead>' +
        '      <tr style="background:#f8fafc;">' +
        '        <th style="padding:10px 12px; border:1px solid #cbd5e1; text-align:left; width:22%; font-weight:700; color:#334155;">Assessment Dimension</th>' +
        '        <th style="padding:10px 12px; border:1px solid #cbd5e1; text-align:left; width:39%; font-weight:700; color:#0369a1;">City of Manila (Baseline)</th>' +
        '        <th style="padding:10px 12px; border:1px solid #cbd5e1; text-align:left; width:39%; font-weight:700; color:#0f766e;">Benchmark Model / Quezon City</th>' +
        '      </tr>' +
        '    </thead>' +
        '    <tbody>' +
        '      <tr>' +
        '        <td style="padding:10px 12px; border:1px solid #cbd5e1; font-weight:700; background:#f8fafc;">1. Scope &amp; Target Coverage</td>' +
        '        <td style="padding:10px 12px; border:1px solid #cbd5e1; color:#334155;">Applies to local commercial entities and primary retail markets across 6 districts.</td>' +
        '        <td style="padding:10px 12px; border:1px solid #cbd5e1; color:#334155;">Comprehensive coverage including mall operators, fast-food chains, and delivery couriers.</td>' +
        '      </tr>' +
        '      <tr>' +
        '        <td style="padding:10px 12px; border:1px solid #cbd5e1; font-weight:700; background:#f8fafc;">2. Economic &amp; Penalties</td>' +
        '        <td style="padding:10px 12px; border:1px solid #cbd5e1; color:#334155;">Fixed graduated fines from ₱1,000 to ₱5,000 per violation.</td>' +
        '        <td style="padding:10px 12px; border:1px solid #cbd5e1; color:#334155;">Graduated administrative fines with mandatory merchant plastic recovery fees.</td>' +
        '      </tr>' +
        '      <tr>' +
        '        <td style="padding:10px 12px; border:1px solid #cbd5e1; font-weight:700; background:#f8fafc;">3. Implementation Mechanism</td>' +
        '        <td style="padding:10px 12px; border:1px solid #cbd5e1; color:#334155;">City Health &amp; Sanitation inspectors with Barangay Council coordination.</td>' +
        '        <td style="padding:10px 12px; border:1px solid #cbd5e1; color:#334155;">Dedicated Environmental Protection and Waste Management Department (EPWMD).</td>' +
        '      </tr>' +
        '    </tbody>' +
        '  </table>' +

        '  <table width="100%" style="width:100%; border-collapse:collapse; margin-bottom:20px; font-size:0.88rem;">' +
        '    <tbody>' +
        '      <tr>' +
        '        <th width="170" style="padding:12px 14px; border:1px solid #cbd5e1; background-color:#f0fdfa; font-weight:700; color:#0f766e; text-align:left;">Comparative Summary</th>' +
        '        <td style="padding:12px 14px; border:1px solid #cbd5e1; color:#1e293b; line-height:1.6;">' + esc(rep.summary) + '</td>' +
        '      </tr>' +
        '      <tr>' +
        '        <th width="170" style="padding:12px 14px; border:1px solid #cbd5e1; background-color:#f0fdfa; font-weight:700; color:#0f766e; text-align:left;">Harmonization Guidance</th>' +
        '        <td style="padding:12px 14px; border:1px solid #cbd5e1; color:#1e293b; line-height:1.6;">' + esc(rep.recommendation) + '</td>' +
        '      </tr>' +
        '    </tbody>' +
        '  </table>' +

        '  <table width="100%" style="width:100%; border-collapse:collapse; margin-top:30px; margin-bottom:20px; font-size:0.85rem; border:none;">' +
        '    <tr>' +
        '      <td width="50%" style="width:50%; border:none; padding:0; vertical-align:top; text-align:left;">' +
        '        <div style="color:#64748b; font-size:0.78rem;">Prepared &amp; Benchmarked By:</div>' +
        '        <div style="font-weight:700; color:#0f172a; margin-top:20px; font-size:0.9rem;">Legislative Policy &amp; Benchmarking Unit</div>' +
        '        <div style="color:#64748b; font-size:0.78rem;">City Council of Manila</div>' +
        '      </td>' +
        '      <td width="50%" style="width:50%; border:none; padding:0; text-align:right; vertical-align:top;">' +
        '        <div style="color:#64748b; font-size:0.78rem;">Approved &amp; Endorsed By:</div>' +
        '        <div style="font-weight:700; color:#0f172a; margin-top:20px; font-size:0.9rem;">Administrator</div>' +
        '        <div style="color:#64748b; font-size:0.78rem;">Legislative Information System</div>' +
        '      </td>' +
        '    </tr>' +
        '  </table>' +
        '  <div style="font-size:0.76rem; color:#94a3b8; border-top:1px solid #e2e8f0; padding-top:12px; text-align:center;">' +
        '    Issued by the City Council Legislative Administration Office — Manila City Hall Legislative Information System' +
        '  </div>' +
        '</div>';
    }

    // --- STANDARD EVALUATION REPORT TEMPLATE ---
    var evalRes = formatEvaluationResult(rep.risk);

    return '' +
      '<div style="max-width:740px; margin:0 auto; background:#ffffff; padding:20px; font-family:\'Segoe UI\', Arial, sans-serif; color:#0f172a; text-align:left;">' +
      '  <div style="text-align:center; margin-bottom:20px;">' +
      '    <img src="' + logoUrl + '" width="68" height="68" style="width:68px; height:68px; object-fit:contain; margin-bottom:10px;" alt="Manila Seal">' +
      '    <h1 style="color:#0B2E59; font-weight:800; font-size:1.65rem; letter-spacing:-0.5px; margin:0 0 4px 0;">LUNGSOD NG MAYNILA</h1>' +
      '    <div style="color:#475569; font-weight:600; font-size:0.9rem; margin-bottom:14px;">City of Manila — Legislative Services</div>' +
      '    <div style="margin-bottom:8px;">' +
      '      <span style="background:#0B2E59; color:#ffffff; padding:7px 20px; font-weight:700; text-transform:uppercase; letter-spacing:1px; font-size:0.82rem; border-radius:6px; display:inline-block; box-shadow: 0 2px 4px rgba(11,46,89,0.2);">Official Legislative Evaluation &amp; Impact Report</span>' +
      '    </div>' +
      '    <div style="color:#64748b; font-size:0.80rem; margin-top:8px;">Date Generated: ' + nowStr + '</div>' +
      '  </div>' +
      '  <div style="border-bottom:2.5px solid #0B2E59; margin-bottom:24px;"></div>' +

      '  <table width="100%" style="width:100%; border-collapse:collapse; margin-bottom:24px; font-size:0.90rem; border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden;">' +
      '    <tbody>' +
      '      <tr>' +
      '        <th width="180" style="padding:13px 18px; border:1px solid #e2e8f0; background-color:#f8fafc; font-weight:700; width:180px; text-align:left; color:#334155;">Policy Title</th>' +
      '        <td style="padding:13px 18px; border:1px solid #e2e8f0; font-weight:700; color:#0f172a; font-size:0.94rem;">' + esc(rep.title || rep.policy_title) + '</td>' +
      '      </tr>' +
      '      <tr>' +
      '        <th width="180" style="padding:13px 18px; border:1px solid #e2e8f0; background-color:#f8fafc; font-weight:700; width:180px; text-align:left; color:#334155;">Category</th>' +
      '        <td style="padding:13px 18px; border:1px solid #e2e8f0; color:#1e293b; font-weight:500;">' + esc(rep.category || 'General Legislation') + '</td>' +
      '      </tr>' +
      '      <tr>' +
      '        <th width="180" style="padding:13px 18px; border:1px solid #e2e8f0; background-color:#f8fafc; font-weight:700; width:180px; text-align:left; color:#334155;">Original Document</th>' +
      '        <td style="padding:13px 18px; border:1px solid #e2e8f0; color:#1e293b;">' +
      ((rep.id || rep.policy_id || (rep.file_path && rep.file_path.trim() !== '')) ?
        ('<a href="' + ((rep.id || rep.policy_id) ? ('../backend/view_policy_document.php?id=' + encodeURIComponent(rep.id || rep.policy_id)) : ('../assets/uploads/policies/' + encodeURIComponent(rep.file_path))) + '" target="_blank" style="color:#0284c7; font-weight:600; text-decoration:underline; display:inline-flex; align-items:center; gap:6px;"><i class="bi bi-box-arrow-up-right"></i> ' + esc(rep.file_path || 'View Full Document') + ' (View Original Document)</a>') :
        ('<span style="color:#64748b; font-style:italic;"><i class="bi bi-archive me-1"></i> Official Manila City Legislative Record (Digital Archive)</span>')
      ) +
      '        </td>' +
      '      </tr>' +
      '      <tr>' +
      '        <th width="180" style="padding:13px 18px; border:1px solid #e2e8f0; background-color:#f8fafc; font-weight:700; width:180px; text-align:left; color:#334155;">AI Executive Summary</th>' +
      '        <td style="padding:13px 18px; border:1px solid #e2e8f0; color:#1e293b; line-height:1.7;">' + esc(rep.summary) + '</td>' +
      '      </tr>' +
      '      <tr>' +
      '        <th width="180" style="padding:13px 18px; border:1px solid #e2e8f0; background-color:#f8fafc; font-weight:700; width:180px; text-align:left; color:#334155;">Evaluation Result</th>' +
      '        <td style="padding:13px 18px; border:1px solid #e2e8f0;">' +
      '          <span style="background:' + evalRes.bg + '; color:' + evalRes.color + '; border:1px solid ' + evalRes.border + '; padding:5px 14px; border-radius:6px; font-weight:700; font-size:0.84rem; display:inline-block;">' + esc(evalRes.text) + '</span>' +
      '        </td>' +
      '      </tr>' +
      '      <tr>' +
      '        <th width="180" style="padding:13px 18px; border:1px solid #e2e8f0; background-color:#f8fafc; font-weight:700; width:180px; text-align:left; color:#334155;">Recommendation</th>' +
      '        <td style="padding:13px 18px; border:1px solid #e2e8f0; color:#1e293b; line-height:1.7;">' + esc(rep.recommendation) + '</td>' +
      '      </tr>' +
      '    </tbody>' +
      '  </table>' +

      '  <table width="100%" style="width:100%; border-collapse:collapse; margin-top:35px; margin-bottom:25px; font-size:0.86rem; border:none;">' +
      '    <tr>' +
      '      <td width="50%" style="width:50%; border:none; padding:0; vertical-align:top; text-align:left;">' +
      '        <div style="color:#64748b; font-size:0.80rem;">Prepared &amp; Evaluated By:</div>' +
      '        <div style="font-weight:700; color:#0f172a; margin-top:22px; font-size:0.92rem;">Legislative Research Office</div>' +
      '        <div style="color:#64748b; font-size:0.80rem;">City Council of Manila</div>' +
      '      </td>' +
      '      <td width="50%" style="width:50%; border:none; padding:0; text-align:right; vertical-align:top;">' +
      '        <div style="color:#64748b; font-size:0.80rem;">Approved By:</div>' +
      '        <div style="font-weight:700; color:#0f172a; margin-top:22px; font-size:0.92rem;">Administrator</div>' +
      '        <div style="color:#64748b; font-size:0.80rem;">Legislative Information System</div>' +
      '      </td>' +
      '    </tr>' +
      '  </table>' +

      '  <div style="font-size:0.78rem; color:#94a3b8; border-top:1px solid #e2e8f0; padding-top:14px; text-align:center;">' +
      '    Issued by the City Council Legislative Administration Office — Manila City Hall Legislative Information System' +
      '  </div>' +
      '</div>';
  }

  function printSelectedReport(customReport) {
    var targetReport = customReport || getActiveReportData();
    if (!targetReport || !targetReport.title) {
      if (typeof _report !== 'undefined' && _report.title) {
        targetReport = _report;
      }
    }

    try {
      trackGeneratedReport((targetReport && targetReport.title) ? targetReport.title : 'Policy Report', 'PDF', targetReport);
    } catch (e) { }

    var logoUrl = window.location.origin + window.location.pathname.substring(0, window.location.pathname.lastIndexOf('/admin/')) + '/assets/images/manilacityhall.svg';
    var htmlContent = buildSharedReportTemplate(targetReport, logoUrl);
    var cleanTitle = (targetReport.title || 'Policy').replace(/[^a-zA-Z0-9 ]/g, '').trim().replace(/\s+/g, '_');
    var docxFileName = cleanTitle + '_Report.docx';

    var doc = '<!DOCTYPE html><html><head><title>Legislative Evaluation Report - ' + esc(targetReport.title || 'Report') + '</title>' +
      '<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">' +
      '<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">' +
      '<style>' +
      'body { background: #fff; padding: 25px; font-family: "Segoe UI", Arial, sans-serif; color: #0f172a; }' +
      '@media print { body { padding: 0; } .no-print { display: none !important; } }' +
      '</style>' +
      '</head><body>' +
      '<div class="no-print mb-4 p-3 bg-light rounded-3 border d-flex flex-wrap align-items-center justify-content-between gap-2 shadow-sm">' +
      '  <div class="small fw-semibold text-muted">' +
      '    <i class="bi bi-info-circle me-1 text-primary"></i> Choose an action for this report:' +
      '  </div>' +
      '  <div class="d-flex align-items-center gap-2">' +
      '    <button onclick="window.print()" class="btn btn-primary btn-sm px-3 fw-bold shadow-sm" style="background:#0B2E59; border-color:#0B2E59;">' +
      '      <i class="bi bi-printer-fill me-1.5"></i> Print / Save as PDF' +
      '    </button>' +
      '    <button id="btnSaveDocxInWindow" class="btn btn-outline-primary btn-sm px-3 fw-bold bg-white shadow-sm">' +
      '      <i class="bi bi-file-earmark-word-fill text-primary me-1.5"></i> Save as DOCX' +
      '    </button>' +
      '    <button onclick="window.close()" class="btn btn-outline-secondary btn-sm px-3">' +
      '      <i class="bi bi-x-lg me-1"></i> Close' +
      '    </button>' +
      '  </div>' +
      '</div>' +
      htmlContent +
      '</body></html>';

    var printWin = window.open('', '_blank', 'width=950,height=880,menubar=no,toolbar=no,location=no,status=no,resizable=yes,scrollbars=yes');
    if (printWin && printWin.document) {
      printWin.document.open();
      printWin.document.write(doc);
      printWin.document.close();
      printWin.focus();

      setTimeout(function () {
        try {
          var saveDocxBtn = printWin.document.getElementById('btnSaveDocxInWindow');
          if (saveDocxBtn) {
            saveDocxBtn.onclick = function () {
              generateWordDoc(docxFileName, targetReport);
            };
          }
        } catch (e) { }
      }, 200);

      setTimeout(function () {
        try {
          printWin.focus();
          printWin.print();
        } catch (e) {
          console.warn('Auto-print prompt prevented by browser:', e);
        }
      }, 450);
    } else {
      var printIframe = document.getElementById('reportPrintIframe');
      if (!printIframe) {
        printIframe = document.createElement('iframe');
        printIframe.id = 'reportPrintIframe';
        printIframe.style.position = 'fixed';
        printIframe.style.right = '0';
        printIframe.style.bottom = '0';
        printIframe.style.width = '0';
        printIframe.style.height = '0';
        printIframe.style.border = '0';
        document.body.appendChild(printIframe);
      }
      var iframeDoc = printIframe.contentWindow.document;
      iframeDoc.open();
      iframeDoc.write(doc);
      iframeDoc.close();
      setTimeout(function () {
        try {
          printIframe.contentWindow.focus();
          printIframe.contentWindow.print();
        } catch (e) {
          console.warn(e);
        }
      }, 450);
    }
  }

  // --- Dynamic Recent Generated Reports Storage & Table Management ---
  var ADMIN_RECENT_REPORTS_KEY = 'legislative_admin_recent_reports_v4';
  var currentReportFilter = 'All';
  var _recentReportsSearchQuery = '';
  var _recentReportsCurrentPage = 1;
  var _recentReportsPageSize = 10;

  function onRecentReportsSearchInput() {
    var input = document.getElementById('recentReportsSearchInput');
    var clearBtn = document.getElementById('recentReportsClearSearchBtn');
    _recentReportsSearchQuery = input ? input.value.trim().toLowerCase() : '';
    if (clearBtn) {
      clearBtn.style.display = _recentReportsSearchQuery.length > 0 ? 'inline-flex' : 'none';
    }
    _recentReportsCurrentPage = 1;
    renderRecentGeneratedReportsTable();
  }
  window.onRecentReportsSearchInput = onRecentReportsSearchInput;

  function clearRecentReportsSearch() {
    var input = document.getElementById('recentReportsSearchInput');
    var clearBtn = document.getElementById('recentReportsClearSearchBtn');
    if (input) input.value = '';
    if (clearBtn) clearBtn.style.display = 'none';
    _recentReportsSearchQuery = '';
    _recentReportsCurrentPage = 1;
    renderRecentGeneratedReportsTable();
  }
  window.clearRecentReportsSearch = clearRecentReportsSearch;

  function filterReportsTable(filterType, btnEl) {
    currentReportFilter = filterType;
    _recentReportsCurrentPage = 1;
    var btns = document.querySelectorAll('.btn-group button[id^="filterReport"]');
    btns.forEach(function (b) {
      b.style.background = 'transparent';
      b.className = 'btn btn-sm rounded-pill px-3 py-1 text-secondary';
    });
    if (btnEl) {
      btnEl.style.background = '#0B2E59';
      btnEl.className = 'btn btn-sm rounded-pill px-3 py-1 fw-bold text-white shadow-sm';
    }
    renderRecentGeneratedReportsTable();
  }
  window.filterReportsTable = filterReportsTable;

  function changeRecentReportsPage(delta) {
    goToRecentReportsPage(_recentReportsCurrentPage + delta);
  }
  window.changeRecentReportsPage = changeRecentReportsPage;

  function goToRecentReportsPage(page) {
    _recentReportsCurrentPage = page;
    renderRecentGeneratedReportsTable();
  }
  window.goToRecentReportsPage = goToRecentReportsPage;

  function loadRecentGeneratedReports() {
    try {
      var raw = localStorage.getItem(ADMIN_RECENT_REPORTS_KEY);
      if (raw) {
        var parsed = JSON.parse(raw);
        if (Array.isArray(parsed) && parsed.length > 0) return parsed;
      }
    } catch (e) { }

    var defaultSeed = [
      {
        report_name: 'QC_SP-2876_vs_Manila_Single_Use_Plastics_Comparative_Analysis.pdf',
        policy_title: 'Single-Use Plastics Ban (Manila) vs. QC SP-2876 Plastics Recovery Code',
        report_type: 'Cross-LGU Benchmark',
        date_generated: 'Sep 01, 2026 11:45 AM',
        format: 'PDF',
        report_data: {
          title: 'Single-Use Plastics Ban (Manila) vs. QC SP-2876 Plastics Recovery Code',
          category: 'Environment',
          status: 'Approved',
          date: 'Sep 01, 2026',
          summary: 'Inter-city comparative benchmarking between City of Manila and Quezon City (QC EPWMD) regulatory structures, merchant recovery funds, and municipal compliance standards.',
          risk: 'Low Risk',
          recommendation: 'Incorporate Quezon City\'s structured recovery fund mechanisms into Manila City Council legislative committee draft.'
        }
      },
      {
        report_name: 'Disaster_Risk_Reduction_Framework_Version_Evolution.pdf',
        policy_title: 'Disaster Risk Reduction and Infrastructure Resilience Framework',
        report_type: 'Version Comparison',
        date_generated: 'Aug 29, 2026 03:20 PM',
        format: 'PDF',
        report_data: {
          title: 'Disaster Risk Reduction and Infrastructure Resilience Framework (Version Evolution)',
          category: 'Infrastructure',
          status: 'Approved',
          date: 'Aug 29, 2026',
          summary: 'Comparative evolution analysis between Version 1 (Baseline) and Version 2 (Revised), documenting updated drainage funding allocations and multi-agency response protocols.',
          risk: 'Low Risk',
          recommendation: 'Latest revised version is recommended for City Council plenary reading and budget endorsement.'
        }
      },
      {
        report_name: 'Flood_Risk_Assessment_Report.pdf',
        policy_title: 'Flood Risk Assessment and Drainage Improvement Plan for Manila City',
        report_type: 'Evaluation Report',
        date_generated: 'Aug 15, 2026 10:30 AM',
        format: 'PDF',
        report_data: {
          title: 'Flood Risk Assessment and Drainage Improvement Plan for Manila City',
          category: 'Infrastructure',
          status: 'Under Review',
          date: 'Aug 15, 2026',
          summary: 'This study evaluates the increasing frequency of urban flooding in Manila City during heavy rainfall and recommends regular drainage maintenance, expansion of pumping stations, and smart flood monitoring sensors.',
          risk: 'Medium Risk',
          recommendation: 'Proceed with committee review and stakeholder consultation.'
        }
      },
      {
        report_name: 'Clean_Energy_Grid_Act_Report.docx',
        policy_title: 'National Clean Energy Grid Modernization Act: Economic and Environmental Impact Assessment',
        report_type: 'Evaluation Report',
        date_generated: 'Aug 12, 2026 02:15 PM',
        format: 'DOCX',
        report_data: {
          title: 'National Clean Energy Grid Modernization Act: Economic and Environmental Impact Assessment',
          category: 'Health',
          status: 'Draft',
          date: 'Aug 12, 2026',
          summary: 'This policy research document evaluates the macroeconomic effects, grid reliability improvements, and carbon emission reductions associated with national clean energy infrastructure modernization.',
          risk: 'Low Risk',
          recommendation: 'Proceed with committee review and stakeholder consultation.'
        }
      },
      {
        report_name: 'Urban_Traffic_Congestion_Study_Report.pdf',
        policy_title: 'Urban Traffic Congestion Study in Manila City',
        report_type: 'Evaluation Report',
        date_generated: 'Aug 11, 2026 09:45 AM',
        format: 'PDF',
        report_data: {
          title: 'Urban Traffic Congestion Study in Manila City',
          category: 'Infrastructure',
          status: 'Draft',
          date: 'Aug 11, 2026',
          summary: 'An empirical analysis of traffic bottleneck nodes across Manila City district arteries, proposing adaptive traffic signaling and dedicated high-occupancy lanes.',
          risk: 'Low Risk',
          recommendation: 'Proceed with committee review and stakeholder consultation.'
        }
      }
    ];

    try {
      localStorage.setItem(ADMIN_RECENT_REPORTS_KEY, JSON.stringify(defaultSeed));
    } catch (e) { }
    return defaultSeed;
  }

  function trackGeneratedReport(policyTitle, format, reportObj) {
    var ext = (format === 'DOCX') ? 'docx' : 'pdf';
    var name = (policyTitle || 'Policy').replace(/[^a-zA-Z0-9 ]/g, '').trim().replace(/\s+/g, '_');
    var fileName = name + '_Report.' + ext;

    var now = new Date();
    var timeStr = now.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) +
      ' ' + now.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });

    var reportItem = {
      report_name: fileName,
      policy_title: policyTitle,
      report_type: 'Evaluation Report',
      date_generated: timeStr,
      format: format,
      report_data: JSON.parse(JSON.stringify(reportObj || _report))
    };

    var list = loadRecentGeneratedReports();
    if (list.length > 0 && list[0].report_name === fileName && list[0].date_generated === timeStr) {
      return;
    }

    list.unshift(reportItem);
    if (list.length > 30) list = list.slice(0, 30);

    try {
      localStorage.setItem(ADMIN_RECENT_REPORTS_KEY, JSON.stringify(list));
    } catch (e) { }

    renderRecentGeneratedReportsTable();
  }

  function renderRecentGeneratedReportsTable() {
    var tbody = document.getElementById('recentGeneratedReportsBody');
    var countEl = document.getElementById('recentGeneratedReportsCount');
    var paginationContainer = document.getElementById('recentReportsPagination');
    if (!tbody) return;

    var allList = loadRecentGeneratedReports();
    var totalBadge = document.getElementById('recentGeneratedReportsTotalBadge');
    if (totalBadge) totalBadge.textContent = allList.length;

    var list = allList;

    // 1. Filter by report category / tab
    if (currentReportFilter === 'Evaluation') {
      list = allList.filter(function (r) { return !r.report_type || r.report_type.indexOf('Evaluation') !== -1; });
    } else if (currentReportFilter === 'Benchmark') {
      list = allList.filter(function (r) { return r.report_type && (r.report_type.indexOf('Benchmark') !== -1 || r.report_type.indexOf('Comparison') !== -1); });
    } else if (currentReportFilter === 'Version') {
      list = allList.filter(function (r) { return r.report_type && (r.report_type.indexOf('Version') !== -1); });
    }

    // 2. Filter by live search query
    if (_recentReportsSearchQuery) {
      var q = _recentReportsSearchQuery;
      list = list.filter(function (r) {
        var name = (r.report_name || '').toLowerCase();
        var title = (r.policy_title || '').toLowerCase();
        var type = (r.report_type || '').toLowerCase();
        var date = (r.date_generated || '').toLowerCase();
        var format = (r.format || '').toLowerCase();
        return name.indexOf(q) !== -1 || title.indexOf(q) !== -1 || type.indexOf(q) !== -1 || date.indexOf(q) !== -1 || format.indexOf(q) !== -1;
      });
    }

    if (!list || list.length === 0) {
      var msg = _recentReportsSearchQuery ? ('No generated reports found matching "' + esc(_recentReportsSearchQuery) + '".') : 'No matching reports found for this filter.';
      tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted py-4"><i class="bi bi-search me-1"></i> ' + msg + '</td></tr>';
      if (countEl) countEl.textContent = 'Showing 0 records';
      if (paginationContainer) paginationContainer.style.display = 'none';
      return;
    }

    if (paginationContainer) paginationContainer.style.display = 'flex';

    var totalMatching = list.length;
    var totalPages = Math.max(1, Math.ceil(totalMatching / _recentReportsPageSize));

    if (_recentReportsCurrentPage < 1) _recentReportsCurrentPage = 1;
    if (_recentReportsCurrentPage > totalPages) _recentReportsCurrentPage = totalPages;

    var startIdx = (_recentReportsCurrentPage - 1) * _recentReportsPageSize;
    var endIdx = Math.min(startIdx + _recentReportsPageSize, totalMatching);

    var html = '';
    for (var i = startIdx; i < endIdx; i++) {
      var r = list[i];
      var originalIdx = allList.indexOf(r);
      var downloadIdx = (originalIdx !== -1) ? originalIdx : i;
      var isDocx = (r.format === 'DOCX') || (r.report_name && r.report_name.toLowerCase().endsWith('.docx'));
      var fileIcon = isDocx ? 'bi-file-earmark-word-fill text-primary' : 'bi-file-earmark-pdf-fill text-danger';

      var typeBadge = '<span class="badge-report-type"><i class="bi bi-file-earmark-text text-primary"></i><span>' + esc(r.report_type || 'Evaluation Report') + '</span></span>';
      if (r.report_type && r.report_type.indexOf('Benchmark') !== -1) {
        typeBadge = '<span class="badge-report-type" style="background:#f0fdfa; color:#0f766e; border-color:#ccfbf1;"><i class="bi bi-intersect text-teal"></i><span>' + esc(r.report_type) + '</span></span>';
      } else if (r.report_type && r.report_type.indexOf('Version') !== -1) {
        typeBadge = '<span class="badge-report-type" style="background:#fefce8; color:#a16207; border-color:#fef08a;"><i class="bi bi-clock-history text-warning"></i><span>' + esc(r.report_type) + '</span></span>';
      }

      html += '<tr class="align-middle" style="height: 52px;">' +
        '<td class="py-2.5 px-3 text-truncate">' +
        '<div class="d-flex align-items-center gap-2 overflow-hidden">' +
        '<i class="bi ' + fileIcon + ' fs-5 flex-shrink-0"></i>' +
        '<span class="fw-semibold text-dark font-monospace text-truncate" style="font-size: 0.86rem;" title="' + esc(r.report_name) + '">' + esc(r.report_name) + '</span>' +
        '</div>' +
        '</td>' +
        '<td class="py-2.5 px-3 text-dark fw-medium text-truncate" style="font-size: 0.88rem;" title="' + esc(r.policy_title) + '">' + esc(r.policy_title) + '</td>' +
        '<td class="py-2.5 px-3 text-nowrap">' +
        typeBadge +
        '</td>' +
        '<td class="py-2.5 px-3 text-nowrap">' +
        '<span class="report-date-badge"><i class="bi bi-calendar3"></i><span>' + esc(r.date_generated) + '</span></span>' +
        '</td>' +
        '<td class="py-2.5 px-3 text-end text-nowrap">' +
        '<button type="button" class="btn btn-sm btn-report-download" onclick="downloadRecentGeneratedReport(' + downloadIdx + ')">' +
        '<i class="bi bi-download"></i>' +
        '<span>Download / View</span>' +
        '</button>' +
        '</td>' +
        '</tr>';
    }

    tbody.innerHTML = html;

    if (countEl) {
      countEl.textContent = 'Showing ' + (startIdx + 1) + ' to ' + endIdx + ' of ' + totalMatching + ' records';
    }

    var prevBtn = document.getElementById('recentReportsPrevBtn');
    if (prevBtn) {
      prevBtn.disabled = (_recentReportsCurrentPage <= 1);
      if (_recentReportsCurrentPage <= 1) {
        prevBtn.classList.add('opacity-50');
      } else {
        prevBtn.classList.remove('opacity-50');
      }
    }

    var nextBtn = document.getElementById('recentReportsNextBtn');
    if (nextBtn) {
      nextBtn.disabled = (_recentReportsCurrentPage >= totalPages);
      if (_recentReportsCurrentPage >= totalPages) {
        nextBtn.classList.add('opacity-50');
      } else {
        nextBtn.classList.remove('opacity-50');
      }
    }

    var pagesContainer = document.getElementById('recentReportsPageNumbers');
    if (pagesContainer) {
      var pagesHtml = '';
      for (var p = 1; p <= totalPages; p++) {
        var isActive = (p === _recentReportsCurrentPage);
        var cls = isActive ? 'btn-primary text-white fw-bold' : 'btn-light border text-dark';
        pagesHtml += '<button type="button" class="btn btn-sm rounded-2 pagination-step-btn ' + cls + '" style="width:32px!important; height:32px!important; min-width:32px!important; max-width:32px!important; padding:0!important; display:inline-flex!important; align-items:center!important; justify-content:center!important;" onclick="goToRecentReportsPage(' + p + ')">' + p + '</button>';
      }
      pagesContainer.innerHTML = pagesHtml;
    }
  }

  function getActiveReportData() {
    var titleEl = document.getElementById('prevPolicyTitle');
    var catEl = document.getElementById('prevCategory');
    var sumEl = document.getElementById('prevAISummary');
    var evalEl = document.getElementById('prevEvalResult');
    var recEl = document.getElementById('prevRecommendation');

    var title = titleEl ? titleEl.textContent.trim() : '';
    var cat = catEl ? catEl.textContent.trim() : '';
    var sum = sumEl ? sumEl.textContent.trim() : '';
    var risk = evalEl ? evalEl.textContent.trim() : 'Low Risk';
    var rec = recEl ? recEl.textContent.trim() : '';

    if (!title && typeof _report !== 'undefined' && _report.title) {
      return _report;
    }

    return {
      title: title || (_report ? _report.title : 'Policy Report'),
      category: cat || (_report ? _report.category : 'General'),
      status: _report ? _report.status : 'Approved',
      date: _report ? _report.date : new Date().toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }),
      summary: sum || (_report ? _report.summary : 'Policy evaluation and legislative review summary.'),
      risk: risk || (_report ? _report.risk : 'Low Risk'),
      recommendation: rec || (_report ? _report.recommendation : 'Proceed with implementation and continue monitoring the effectiveness of the policy.')
    };
  }

  var _reportPolicyCurrentPage = 1;
  var _reportPolicyPageSize = 10;
  var _reportPolicyDateFilter = {
    type: '',
    from: '',
    to: '',
    label: 'Filter Date'
  };

  function toggleReportDateDropdown(e) {
    if (e) {
      e.preventDefault();
      e.stopPropagation();
    }
    var menu = document.getElementById('reportDateFilterDropdownMenu');
    var btn = document.getElementById('reportDateFilterDropdownBtn');
    if (!menu) return;
    var isOpen = (menu.style.display === 'block' || menu.classList.contains('show'));
    if (isOpen) {
      menu.classList.remove('show');
      menu.style.display = 'none';
      if (btn) btn.setAttribute('aria-expanded', 'false');
    } else {
      menu.classList.add('show');
      menu.style.display = 'block';
      if (btn) btn.setAttribute('aria-expanded', 'true');
    }
  }

  function closeReportDateDropdown() {
    var menu = document.getElementById('reportDateFilterDropdownMenu');
    var btn = document.getElementById('reportDateFilterDropdownBtn');
    if (menu) {
      menu.classList.remove('show');
      menu.style.display = 'none';
    }
    if (btn) btn.setAttribute('aria-expanded', 'false');
  }

  document.addEventListener('click', function (e) {
    var menu = document.getElementById('reportDateFilterDropdownMenu');
    var group = document.getElementById('reportDateFilterInputGroup');
    if (menu && (menu.style.display === 'block' || menu.classList.contains('show'))) {
      if (!menu.contains(e.target) && (!group || !group.contains(e.target))) {
        closeReportDateDropdown();
      }
    }
  });

  function applyReportDateFilter(type, label) {
    _reportPolicyDateFilter.type = type || '';
    _reportPolicyDateFilter.from = '';
    _reportPolicyDateFilter.to = '';
    _reportPolicyDateFilter.label = label || 'Filter Date';

    // Reset custom date inputs
    var fromInput = document.getElementById('reportCustomDateFrom');
    var toInput = document.getElementById('reportCustomDateTo');
    if (fromInput) fromInput.value = '';
    if (toInput) toInput.value = '';

    updateReportDateFilterUI();
    _reportPolicyCurrentPage = 1;
    renderReportPolicyPagination();

    closeReportDateDropdown();
  }

  function applyReportCustomDateRange() {
    var fromInput = document.getElementById('reportCustomDateFrom');
    var toInput = document.getElementById('reportCustomDateTo');
    var fromVal = fromInput ? fromInput.value.trim() : '';
    var toVal = toInput ? toInput.value.trim() : '';

    if (!fromVal && !toVal) {
      applyReportDateFilter('', 'Filter Date');
      return;
    }

    _reportPolicyDateFilter.type = 'custom';
    _reportPolicyDateFilter.from = fromVal;
    _reportPolicyDateFilter.to = toVal;
    _reportPolicyDateFilter.label = (fromVal && toVal) ? (fromVal + ' to ' + toVal) : (fromVal ? 'From ' + fromVal : 'Until ' + toVal);

    updateReportDateFilterUI();
    _reportPolicyCurrentPage = 1;
    renderReportPolicyPagination();

    closeReportDateDropdown();
  }

  function updateReportDateFilterUI() {
    var hasActive = (_reportPolicyDateFilter.type !== '' || _reportPolicyDateFilter.from || _reportPolicyDateFilter.to);
    var labelEl = document.getElementById('reportDateFilterLabel');
    var badgeEl = document.getElementById('reportDateFilterActiveBadge');
    var resetLink = document.getElementById('reportDateResetLink');
    var btn = document.getElementById('reportDateFilterDropdownBtn');
    var icon = document.getElementById('reportDateFilterIcon') || document.getElementById('reportDateFilterAddonIcon');

    if (labelEl) {
      labelEl.textContent = hasActive ? _reportPolicyDateFilter.label : 'Filter Date';
    }
    if (badgeEl) {
      badgeEl.style.display = hasActive ? 'inline-block' : 'none';
    }
    if (resetLink) {
      resetLink.style.display = hasActive ? 'inline' : 'none';
    }
    if (icon) {
      icon.className = hasActive ? 'bi bi-calendar3-fill text-primary' : 'bi bi-calendar3';
    }
    if (btn) {
      if (hasActive) {
        btn.classList.add('border-primary', 'bg-primary', 'bg-opacity-10', 'text-primary');
        btn.classList.remove('text-dark');
      } else {
        btn.classList.remove('border-primary', 'bg-primary', 'bg-opacity-10', 'text-primary');
        btn.classList.add('text-dark');
      }
    }

    // Highlight active preset button
    document.querySelectorAll('#reportDatePresetGroup .date-preset-btn, #reportDateYearGroup .date-preset-btn').forEach(function (b) {
      var btnType = b.getAttribute('data-type');
      if (btnType === _reportPolicyDateFilter.type) {
        b.classList.remove('btn-light', 'text-dark', 'border');
        b.classList.add('btn-primary', 'text-white');
      } else {
        b.classList.remove('btn-primary', 'text-white');
        b.classList.add('btn-light', 'text-dark', 'border');
      }
    });
  }

  function rowMatchesDateFilter(rowDateStr) {
    if (!_reportPolicyDateFilter || !_reportPolicyDateFilter.type) return true;
    if (!rowDateStr) return false;

    var rowParts = rowDateStr.split('-');
    if (rowParts.length !== 3) return false;
    var rowYear = parseInt(rowParts[0], 10);
    var rowMonth = parseInt(rowParts[1], 10);
    var rowDay = parseInt(rowParts[2], 10);

    var rowDate = new Date(rowYear, rowMonth - 1, rowDay, 0, 0, 0);

    var now = new Date();
    var todayStart = new Date(now.getFullYear(), now.getMonth(), now.getDate(), 0, 0, 0);
    var todayEnd = new Date(now.getFullYear(), now.getMonth(), now.getDate(), 23, 59, 59, 999);

    switch (_reportPolicyDateFilter.type) {
      case 'today':
        return rowDate >= todayStart && rowDate <= todayEnd;

      case 'last_7_days':
        var d7 = new Date(todayStart);
        d7.setDate(d7.getDate() - 7);
        return rowDate >= d7 && rowDate <= todayEnd;

      case 'last_30_days':
        var d30 = new Date(todayStart);
        d30.setDate(d30.getDate() - 30);
        return rowDate >= d30 && rowDate <= todayEnd;

      case 'this_month':
        return rowYear === now.getFullYear() && (rowMonth - 1) === now.getMonth();

      case 'last_month':
        var lastMonthDate = new Date(now.getFullYear(), now.getMonth() - 1, 1);
        return rowYear === lastMonthDate.getFullYear() && (rowMonth - 1) === lastMonthDate.getMonth();

      case '2026':
        return rowYear === 2026;
      case '2025':
        return rowYear === 2025;
      case '2024':
        return rowYear === 2024;

      case 'custom':
        var fromDate = _reportPolicyDateFilter.from ? new Date(_reportPolicyDateFilter.from + 'T00:00:00') : null;
        var toDate = _reportPolicyDateFilter.to ? new Date(_reportPolicyDateFilter.to + 'T23:59:59') : null;
        if (fromDate && toDate) {
          return rowDate >= fromDate && rowDate <= toDate;
        } else if (fromDate) {
          return rowDate >= fromDate;
        } else if (toDate) {
          return rowDate <= toDate;
        }
        return true;

      default:
        return true;
    }
  }

  function onReportPolicyFilterChange() {
    var searchInput = document.getElementById('reportPolicySearchInput');
    var clearBtn = document.getElementById('reportPolicyClearSearchBtn');
    if (clearBtn && searchInput) {
      clearBtn.style.display = searchInput.value.trim() ? 'inline-flex' : 'none';
    }

    _reportPolicyCurrentPage = 1;
    renderReportPolicyPagination();
  }

  function clearReportPolicySearch() {
    var searchInput = document.getElementById('reportPolicySearchInput');
    var clearBtn = document.getElementById('reportPolicyClearSearchBtn');
    if (searchInput) {
      searchInput.value = '';
      searchInput.focus();
    }
    if (clearBtn) {
      clearBtn.style.display = 'none';
    }
    _reportPolicyCurrentPage = 1;
    renderReportPolicyPagination();
  }

  function changeReportPolicyPage(delta) {
    goToReportPolicyPage(_reportPolicyCurrentPage + delta);
  }

  function goToReportPolicyPage(page) {
    _reportPolicyCurrentPage = page;
    renderReportPolicyPagination();
  }

  function renderReportPolicyPagination() {
    var allRows = Array.from(document.querySelectorAll('#reportPolicyTableBody .report-policy-row'));
    var noDataRow = document.getElementById('reportPolicyNoDataRow');
    if (!allRows.length && !noDataRow) return;

    var searchInput = document.getElementById('reportPolicySearchInput');
    var categorySelect = document.getElementById('reportPolicyCategorySelect');

    var query = searchInput ? searchInput.value.toLowerCase().trim() : '';
    var selectedCat = categorySelect ? categorySelect.value.toLowerCase().trim() : '';

    // Filter rows by Title, Number, Author, Category, and Date simultaneously
    var matchingRows = allRows.filter(function (r) {
      var rowTitle = (r.getAttribute('data-title') || (r.querySelector('.policy-title-link') ? r.querySelector('.policy-title-link').textContent : '')).toLowerCase().trim();
      var rowAuthor = (r.getAttribute('data-author') || '').toLowerCase().trim();
      var rowNumber = (r.getAttribute('data-number') || '').toLowerCase().trim();
      var rowCat = (r.getAttribute('data-category') || (r.querySelector('.badge') ? r.querySelector('.badge').textContent : '')).toLowerCase().trim();
      var rowDate = (r.getAttribute('data-date') || '').trim();

      var matchSearch = !query || rowTitle.indexOf(query) !== -1 || rowAuthor.indexOf(query) !== -1 || rowNumber.indexOf(query) !== -1;
      var matchCategory = true;
      if (selectedCat) {
        if (selectedCat === 'other') {
          var isStd = (rowCat.indexOf('health') !== -1 || rowCat.indexOf('sanitation') !== -1 ||
            rowCat.indexOf('civil') !== -1 || rowCat.indexOf('registry') !== -1 ||
            rowCat.indexOf('education') !== -1 || rowCat.indexOf('employment') !== -1 ||
            rowCat.indexOf('social') !== -1 || rowCat.indexOf('welfare') !== -1 ||
            rowCat.indexOf('infrastructure') !== -1 || rowCat.indexOf('traffic') !== -1 || rowCat.indexOf('environment') !== -1);
          matchCategory = !isStd || rowCat === 'other';
        } else {
          matchCategory = (rowCat === selectedCat) || (rowCat.indexOf(selectedCat) !== -1) || (selectedCat.indexOf(rowCat) !== -1);
        }
      }
      var matchDate = rowMatchesDateFilter(rowDate);

      return matchSearch && matchCategory && matchDate;
    });

    var totalMatching = matchingRows.length;
    var totalPages = Math.max(1, Math.ceil(totalMatching / _reportPolicyPageSize));

    if (_reportPolicyCurrentPage < 1) _reportPolicyCurrentPage = 1;
    if (_reportPolicyCurrentPage > totalPages) _reportPolicyCurrentPage = totalPages;

    var startIdx = (_reportPolicyCurrentPage - 1) * _reportPolicyPageSize;
    var endIdx = Math.min(startIdx + _reportPolicyPageSize, totalMatching);

    // Show/hide matching rows based on current page
    allRows.forEach(function (r) {
      var matchIdx = matchingRows.indexOf(r);
      if (matchIdx >= startIdx && matchIdx < endIdx) {
        r.style.display = '';
      } else {
        r.style.display = 'none';
      }
    });

    // Show or hide "No matching policies found" row
    if (noDataRow) {
      noDataRow.style.display = totalMatching === 0 ? '' : 'none';
    }

    // Update "X Policies Available" badge counter
    var counterEl = document.getElementById('reportPolicyAvailableCount');
    if (counterEl) {
      counterEl.textContent = totalMatching;
    }

    // Update summary text: e.g. "Showing 1 to 10 of 11 records"
    var summaryEl = document.getElementById('reportPoliciesSummaryText');
    if (summaryEl) {
      if (totalMatching === 0) {
        summaryEl.textContent = 'Showing 0 records';
      } else {
        summaryEl.textContent = 'Showing ' + (startIdx + 1) + ' to ' + endIdx + ' of ' + totalMatching + ' records';
      }
    }

    // Update Previous button state
    var prevBtn = document.getElementById('reportPolicyPrevBtn');
    if (prevBtn) {
      prevBtn.disabled = (_reportPolicyCurrentPage <= 1);
      if (_reportPolicyCurrentPage <= 1) {
        prevBtn.classList.add('opacity-50');
      } else {
        prevBtn.classList.remove('opacity-50');
      }
    }

    // Update Next button state
    var nextBtn = document.getElementById('reportPolicyNextBtn');
    if (nextBtn) {
      nextBtn.disabled = (_reportPolicyCurrentPage >= totalPages || totalMatching === 0);
      if (_reportPolicyCurrentPage >= totalPages || totalMatching === 0) {
        nextBtn.classList.add('opacity-50');
      } else {
        nextBtn.classList.remove('opacity-50');
      }
    }

    // Render pagination page buttons
    var pagesContainer = document.getElementById('reportPolicyPageNumbers');
    if (pagesContainer) {
      var pagesHtml = '';
      for (var p = 1; p <= totalPages; p++) {
        var isActive = (p === _reportPolicyCurrentPage);
        var cls = isActive ? 'btn-primary text-white fw-bold' : 'btn-light border text-dark';
        pagesHtml += '<button type="button" class="btn btn-sm rounded-2 pagination-step-btn ' + cls + '" style="width:32px!important; height:32px!important; min-width:32px!important; max-width:32px!important; padding:0!important; display:inline-flex!important; align-items:center!important; justify-content:center!important;" onclick="goToReportPolicyPage(' + p + ')">' + p + '</button>';
      }
      pagesContainer.innerHTML = pagesHtml;
    }
  }

  window.applyReportDateFilter = applyReportDateFilter;
  window.applyReportCustomDateRange = applyReportCustomDateRange;
  window.onReportPolicyFilterChange = onReportPolicyFilterChange;
  window.clearReportPolicySearch = clearReportPolicySearch;
  window.changeReportPolicyPage = changeReportPolicyPage;
  window.goToReportPolicyPage = goToReportPolicyPage;
  window.renderReportPolicyPagination = renderReportPolicyPagination;
  document.addEventListener('DOMContentLoaded', renderReportPolicyPagination);
  window.addEventListener('load', renderReportPolicyPagination);

  function openPolicyRowReport(trEl) {
    if (!trEl) return;
    var rawPolicy = trEl.getAttribute('data-policy');
    var repObj = null;
    if (rawPolicy) {
      try {
        repObj = JSON.parse(rawPolicy);
      } catch (e) {
        console.error('Failed to parse policy JSON:', e);
      }
    }
    if (!repObj) {
      var linkEl = trEl.querySelector('.policy-title-link') || trEl.querySelector('strong');
      var title = linkEl ? linkEl.textContent.trim() : 'Policy Report';
      var cat = (trEl.cells[1] ? trEl.cells[1].textContent.trim() : 'General Legislation');
      var status = (trEl.cells[2] ? trEl.cells[2].textContent.trim() : 'Draft');
      var date = (trEl.cells[3] ? trEl.cells[3].textContent.trim() : '—');
      repObj = {
        title: title,
        policy_title: title,
        category: cat,
        status: status,
        date: date,
        date_uploaded: date,
        summary: 'This policy contains official legislative data and impact evaluation findings for ' + title + '.',
        risk: 'Low Risk',
        recommendation: 'Proceed with implementation and continue monitoring the effectiveness of the policy.',
        report_type: 'Evaluation Report'
      };
    }

    _report = repObj;
    var cleanTitle = ((repObj.title || repObj.policy_title || 'Policy').replace(/[^a-zA-Z0-9 ]/g, '').trim().replace(/\s+/g, '_'));
    var fileName = cleanTitle + '_Report.pdf';

    try {
      trackGeneratedReport(repObj.title || repObj.policy_title, 'PDF', repObj);
    } catch (e) { }

    openReportDocumentModal(repObj, fileName);
  }

  function openReportDocumentModal(rep, fileName) {
    _activeModalReport = rep;
    var cleanTitle = ((rep.title || rep.policy_title || 'Policy').replace(/[^a-zA-Z0-9 ]/g, '').trim().replace(/\s+/g, '_'));
    _activeModalFileName = fileName || (cleanTitle + '_Report.pdf');

    var logoUrl = window.location.origin + window.location.pathname.substring(0, window.location.pathname.lastIndexOf('/admin/')) + '/assets/images/manilacityhall.svg';
    var htmlContent = buildSharedReportTemplate(rep, logoUrl);

    var bodyEl = document.getElementById('reportViewerModalDocumentBody');
    if (bodyEl) bodyEl.innerHTML = htmlContent;

    var titleEl = document.getElementById('reportViewerModalTitle');
    if (titleEl) titleEl.textContent = rep.report_type || 'Official Legislative Document';

    var viewOrigBtn = document.getElementById('reportModalViewOriginalBtn');
    if (viewOrigBtn) {
      const pId = rep.id || rep.policy_id;
      if (pId) {
        viewOrigBtn.classList.remove('d-none');
        viewOrigBtn.onclick = function () {
          window.open('../backend/view_policy_document.php?id=' + encodeURIComponent(pId), '_blank');
        };
      } else if (rep.file_path && rep.file_path.trim() !== '') {
        viewOrigBtn.classList.remove('d-none');
        viewOrigBtn.onclick = function () {
          window.open('../assets/uploads/policies/' + encodeURIComponent(rep.file_path), '_blank');
        };
      } else {
        viewOrigBtn.classList.remove('d-none');
        viewOrigBtn.onclick = function () {
          alert('No uploaded source document attachment is associated with this policy record.');
        };
      }
    }

    var pdfBtn = document.getElementById('reportModalDownloadPdfBtn');
    if (pdfBtn) {
      pdfBtn.onclick = function () {
        saveReportAsPDF(_activeModalFileName, _activeModalReport);
      };
    }

    var docxBtn = document.getElementById('reportModalDownloadDocxBtn');
    if (docxBtn) {
      docxBtn.onclick = function () {
        var docxName = _activeModalFileName.replace(/\.pdf$/i, '') + '.docx';
        generateWordDoc(docxName, _activeModalReport);
      };
    }

    var printBtn = document.getElementById('reportModalPrintBtn');
    if (printBtn) {
      printBtn.onclick = function () {
        printSelectedReport(_activeModalReport);
      };
    }

    var modalEl = document.getElementById('reportDocumentViewerModal');
    if (modalEl && typeof bootstrap !== 'undefined') {
      var modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
      modal.show();
    }
  }

  function saveReportAsPDF(fileName, rep) {
    var logoUrl = window.location.origin + window.location.pathname.substring(0, window.location.pathname.lastIndexOf('/admin/')) + '/assets/images/manilacityhall.svg';
    var htmlContent = buildSharedReportTemplate(rep, logoUrl);

    if (typeof html2pdf !== 'undefined') {
      var container = document.createElement('div');
      container.innerHTML = htmlContent;
      container.style.position = 'fixed';
      container.style.left = '-9999px';
      container.style.top = '0';
      container.style.width = '750px';
      container.style.background = '#ffffff';
      container.style.padding = '20px';
      document.body.appendChild(container);

      var opt = {
        margin: 0.4,
        filename: fileName.endsWith('.pdf') ? fileName : fileName + '.pdf',
        image: { type: 'jpeg', quality: 0.98 },
        html2canvas: { scale: 2, useCORS: true },
        jsPDF: { unit: 'in', format: 'letter', orientation: 'portrait' }
      };

      html2pdf().set(opt).from(container).save().then(function () {
        if (container && container.parentNode) container.parentNode.removeChild(container);
      }).catch(function (err) {
        if (container && container.parentNode) container.parentNode.removeChild(container);
        printSelectedReport(rep);
      });
    } else {
      printSelectedReport(rep);
    }
  }

  function downloadRecentGeneratedReport(index) {
    var list = loadRecentGeneratedReports();
    var item = list[index];
    if (!item) return;

    var rep = item.report_data || {
      title: item.policy_title,
      category: 'General',
      status: 'Evaluated',
      date: item.date_generated,
      summary: 'This policy contains official legislative data and impact evaluation findings for ' + item.policy_title + '.',
      risk: 'Low Risk',
      recommendation: 'Proceed with implementation and continue monitoring the effectiveness of the policy.'
    };
    if (item.report_type) rep.report_type = item.report_type;

    if (item.format === 'DOCX' || (item.report_name && item.report_name.toLowerCase().endsWith('.docx'))) {
      generateWordDoc(item.report_name, rep);
    } else {
      openReportDocumentModal(rep, item.report_name);
    }
  }

  function exportReport(format) {
    var rep = getActiveReportData();
    var ext = (format === 'DOCX') ? 'docx' : 'pdf';
    var name = (rep.title || 'Policy').replace(/[^a-zA-Z0-9 ]/g, '').trim().replace(/\s+/g, '_');
    var fileName = name + '_Report.' + ext;

    try {
      trackGeneratedReport(rep.title, format, rep);
    } catch (e) { }

    if (format === 'DOCX') {
      generateWordDoc(fileName, rep);
    } else {
      saveReportAsPDF(fileName, rep);
    }
  }

  function generateWordDoc(fileName, customReport) {
    var targetReport = customReport || getActiveReportData();
    var logoUrl = window.location.origin + window.location.pathname.substring(0, window.location.pathname.lastIndexOf('/admin/')) + '/assets/images/manilacityhall.svg';

    var htmlContent = buildSharedReportTemplate(targetReport, logoUrl);

    var wordHTML =
      '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:w="urn:schemas-microsoft-com:office:word" xmlns="http://www.w3.org/TR/REC-html40">' +
      '<head><meta charset="utf-8"><title>Legislative Evaluation Report</title>' +
      '<style>' +
      '@page WordSection1 { size: 8.5in 11.0in; margin: 0.5in 0.5in 0.5in 0.5in; }' +
      'div.WordSection1 { page: WordSection1; width: 540pt; margin: 0 auto; text-align: center; }' +
      'body { font-family: "Segoe UI", Arial, sans-serif; font-size: 11pt; color: #0f172a; background: #ffffff; }' +
      'table { width: 100% !important; border-collapse: collapse; }' +
      'img { max-width: 65px !important; max-height: 65px !important; }' +
      '</style></head><body>' +
      '<div class="WordSection1" align="center">' +
      htmlContent +
      '</div>' +
      '</body></html>';

    var blob = new Blob(['\ufeff' + wordHTML], { type: 'application/msword;charset=utf-8' });
    var url = URL.createObjectURL(blob);
    var a = document.createElement('a');
    a.href = url;
    a.download = fileName;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    URL.revokeObjectURL(url);
  }

  function downloadReportDoc(fileName) {
    var list = loadRecentGeneratedReports();
    for (var i = 0; i < list.length; i++) {
      if (list[i].report_name === fileName) {
        downloadRecentGeneratedReport(i);
        return;
      }
    }
    if (fileName.toLowerCase().endsWith('.docx')) {
      generateWordDoc(fileName, getActiveReportData());
    } else {
      openReportDocumentModal(getActiveReportData(), fileName);
    }
  }

  // Ensure all global report functions are attached to window
  window.openPolicyRowReport = openPolicyRowReport;
  window.exportReport = exportReport;
  window.printSelectedReport = printSelectedReport;
  window.generateWordDoc = generateWordDoc;
  window.downloadRecentGeneratedReport = downloadRecentGeneratedReport;
  window.openReportDocumentModal = openReportDocumentModal;
  window.saveReportAsPDF = saveReportAsPDF;
  window.selectReportPolicy = selectReportPolicy;
  window.renderRecentGeneratedReportsTable = renderRecentGeneratedReportsTable;
  document.addEventListener('DOMContentLoaded', renderRecentGeneratedReportsTable);
  window.addEventListener('load', renderRecentGeneratedReportsTable);
</script>