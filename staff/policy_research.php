<!-- staff/policy_research.php — Staff Policy Research Submodule -->
<section id="policyResearchSection"
  class="content-section <?= ($active_section ?? 'staffDashboardSection') !== 'policyResearchSection' ? 'd-none' : '' ?>">
  <?php if (!empty($message)): ?>
    <div class="alert alert-<?= $messageType ?> alert-dismissible fade show shadow-sm" role="alert">
      <?= $message ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  <?php endif; ?>
  <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-white">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
      <div>
        <h2 class="h4 fw-bold text-dark mb-1"><i class="bi bi-journal-bookmark-fill text-primary me-2"></i>Policy
          Research</h2>
        <p class="text-muted mb-0">Manage legislative ordinances, resolutions, policy drafts, and generate AI document
          summaries.</p>
      </div>
      <div class="d-flex flex-wrap align-items-center gap-2">
        <a href="staff_dashboard.php?section=policyResearchSection&status="
          class="btn <?= empty($status_filter) ? 'btn-primary' : 'btn-outline-primary' ?> fw-semibold rounded-3 shadow-sm d-inline-flex align-items-center gap-1.5 px-3 py-2">
          <i class="bi bi-folder-fill"></i> All Policies
        </a>
        <a href="staff_dashboard.php?section=policyResearchSection&status=Archived"
          class="btn <?= (($status_filter ?? '') === 'Archived') ? 'btn-secondary text-white' : 'btn-outline-secondary' ?> fw-semibold rounded-3 shadow-sm d-inline-flex align-items-center gap-1.5 px-3 py-2">
          <i class="bi bi-archive-fill"></i> Archived Policies
        </a>
        <button
          class="btn btn-warning fw-semibold rounded-3 shadow-sm d-inline-flex align-items-center gap-1.5 px-3 py-2 text-dark"
          data-bs-toggle="modal" data-bs-target="#uploadPolicyModal">
          <i class="bi bi-upload"></i> Upload New Policy
        </button>
      </div>
    </div>

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
        elseif (strpos($lower, 'education') !== false || strpos($lower, 'employment') !== false || strpos($lower, 'school') !== false || strpos($lower, 'labor') !== false || strpos($lower, 'job') !== false) {
          $bg = '#EEEDFE';
          $text = '#3C3489';
          $border = '#CECBF6';
          $icon = 'bi-mortarboard-fill';
          $label = !empty($cat) ? $cat : 'Education and Employment';
        }
        // 4. Social Welfare and Community Affairs (#FAECE7, #712B13)
        elseif (strpos($lower, 'social') !== false || strpos($lower, 'welfare') !== false || strpos($lower, 'community') !== false || strpos($lower, 'senior') !== false || strpos($lower, 'youth') !== false || strpos($lower, 'family') !== false) {
          $bg = '#FAECE7';
          $text = '#712B13';
          $border = '#F5C4B5';
          $icon = 'bi-people-fill';
          $label = !empty($cat) ? $cat : 'Social Welfare and Community Affairs';
        }
        // 5. Infrastructure, Traffic and Environment (#EAF3DE, #27500A)
        elseif (strpos($lower, 'infrastructure') !== false || strpos($lower, 'traffic') !== false || strpos($lower, 'transport') !== false || strpos($lower, 'environment') !== false || strpos($lower, 'zoning') !== false || strpos($lower, 'flood') !== false || strpos($lower, 'waste') !== false || strpos($lower, 'road') !== false) {
          $bg = '#EAF3DE';
          $text = '#27500A';
          $border = '#BFE09A';
          $icon = 'bi-tree-fill';
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
    ?>
    <style>
      /* Refined Civic Category Badges (Light Background + Dark Text) - Comfortable & Clear */
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
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04);
      }

      .category-badge-pill i {
        font-size: 0.85rem;
        flex-shrink: 0;
      }

      tr:hover .category-badge-pill {
        transform: translateY(-1px);
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.07);
      }

      /* Refined Status Badges - Comfortable & Clear */
      .policy-status-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 5.5px 13px !important;
        font-size: 0.82rem !important;
        font-weight: 700 !important;
        border-radius: 9999px !important;
        letter-spacing: 0.02em !important;
        line-height: 1.25;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04);
      }

      /* Refined Action Buttons - Sized for Clear Legibility and Easy Interaction */
      .action-btn-group {
        gap: 7px !important;
      }

      .btn-policy-action {
        width: 39px !important;
        height: 39px !important;
        padding: 0 !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        border-radius: 9px !important;
        font-size: 1.12rem !important;
        transition: all 0.18s cubic-bezier(0.4, 0, 0.2, 1) !important;
      }

      /* Clean Minimalist Toolbar Controls */
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

      .policy-date-filter-btn .policy-chevron {
        color: #94A3B8 !important;
        font-size: 0.72rem !important;
      }

      /* Option 1: Civic Slate Metadata Chip - Comfortable & Clear */
      .report-date-cell {
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

      .report-date-cell i {
        color: #0B2E59 !important;
        opacity: 0.85;
        font-size: 0.88rem;
        transition: color 0.18s ease, transform 0.18s ease, opacity 0.18s ease;
      }

      tr:hover .report-date-cell {
        background: #FFFFFF !important;
        border-color: #CBD5E1 !important;
        box-shadow: 0 2px 5px rgba(11, 46, 89, 0.08) !important;
        transform: translateY(-1px);
      }

      tr:hover .report-date-cell i {
        opacity: 1;
        transform: scale(1.08);
        color: #0B2E59 !important;
      }

      tr:hover .report-date-cell .report-date-text {
        color: #0B2E59 !important;
        font-weight: 600;
      }

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
    </style>

    <?php
    $timeframe_labels = [
      '' => 'All Dates',
      'today' => 'Today',
      'last_7_days' => 'Last 7 Days',
      'last_30_days' => 'Last 30 Days',
      'this_month' => 'This Month',
      'last_month' => 'Last Month',
      '2026' => 'Year 2026',
      'this_year' => 'Year 2026',
      '2025' => 'Year 2025',
      '2024' => 'Year 2024',
    ];
    $current_timeframe_label = $timeframe_labels[$timeframe_filter ?? ''] ?? 'All Dates';
    $has_active_date = !empty($timeframe_filter) || !empty($_GET['date_from']) || !empty($_GET['date_to']);
    if (!empty($_GET['date_from']) && !empty($_GET['date_to'])) {
      $current_timeframe_label = date('M d', strtotime($_GET['date_from'])) . ' - ' . date('M d', strtotime($_GET['date_to']));
    } elseif (!empty($_GET['date_from'])) {
      $current_timeframe_label = 'From ' . date('M d', strtotime($_GET['date_from']));
    } elseif (!empty($_GET['date_to'])) {
      $current_timeframe_label = 'Until ' . date('M d', strtotime($_GET['date_to']));
    }
    ?>

    <!-- Search & Filters (Clean Minimalist Toolbar) -->
    <form method="GET" action="staff_dashboard.php" class="d-flex flex-wrap flex-md-nowrap align-items-center gap-2.5 mb-4" id="policyFilterForm">
      <input type="hidden" name="section" value="policyResearchSection">
      <input type="hidden" name="timeframe" id="filterTimeframeInput" value="<?= htmlspecialchars($timeframe_filter ?? '') ?>">
      <input type="hidden" name="date_from" id="filterDateFromInput" value="<?= htmlspecialchars($_GET['date_from'] ?? '') ?>">
      <input type="hidden" name="date_to" id="filterDateToInput" value="<?= htmlspecialchars($_GET['date_to'] ?? '') ?>">
      <?php if (!empty($status_filter)): ?>
        <input type="hidden" name="status" value="<?= htmlspecialchars($status_filter) ?>">
      <?php endif; ?>
      <!-- Search Input -->
      <div class="policy-search-box flex-grow-1" style="min-width: 250px;">
        <i class="bi bi-search policy-search-icon"></i>
        <input type="text" name="search" class="policy-search-input"
          placeholder="Search policies by title, number or author..." value="<?= htmlspecialchars($search ?? '') ?>"
          onkeydown="if(event.key === 'Enter') this.form.submit();">
        <?php if (!empty($search)): ?>
          <button class="policy-search-clear" type="button" onclick="this.form.search.value=''; this.form.submit();" title="Clear search">
            <i class="bi bi-x-lg"></i>
          </button>
        <?php endif; ?>
      </div>

      <!-- Category Filter -->
      <div class="policy-category-wrapper">
        <select name="category" class="form-select policy-category-select" onchange="this.form.submit()">
          <option value="">All Categories</option>
          <option value="Health and Sanitation" <?= (($category_filter ?? '') === 'Health and Sanitation' || ($category_filter ?? '') === 'Health') ? 'selected' : '' ?>>Health and Sanitation</option>
          <option value="Civil Registry and Public Services" <?= (($category_filter ?? '') === 'Civil Registry and Public Services') ? 'selected' : '' ?>>Civil Registry and Public Services</option>
          <option value="Education and Employment" <?= (($category_filter ?? '') === 'Education and Employment' || ($category_filter ?? '') === 'Education') ? 'selected' : '' ?>>Education and Employment</option>
          <option value="Social Welfare and Community Affairs" <?= (($category_filter ?? '') === 'Social Welfare and Community Affairs') ? 'selected' : '' ?>>Social Welfare and Community Affairs</option>
          <option value="Infrastructure, Traffic and Environment" <?= (($category_filter ?? '') === 'Infrastructure, Traffic and Environment' || ($category_filter ?? '') === 'Infrastructure' || ($category_filter ?? '') === 'Environment') ? 'selected' : '' ?>>Infrastructure, Traffic and Environment</option>
          <option value="Other" <?= (($category_filter ?? '') === 'Other') ? 'selected' : '' ?>>Other</option>
        </select>
      </div>

      <!-- Compact Date Filter Button & Popover -->
      <div class="dropdown position-relative" style="flex-shrink: 0;">
        <button class="btn policy-date-filter-btn <?= $has_active_date ? 'border-primary bg-primary bg-opacity-10 text-primary' : '' ?>" 
                type="button" id="dateFilterDropdownBtn" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
          <span class="d-inline-flex align-items-center gap-2">
            <i class="bi <?= $has_active_date ? 'bi-calendar3-fill text-primary' : 'bi-calendar3' ?>" style="color: <?= $has_active_date ? '#0D6EFD' : '#64748B' ?>; font-size: 0.86rem;"></i>
            <span><?= $has_active_date ? htmlspecialchars($current_timeframe_label) : 'Filter Date' ?></span>
            <?php if ($has_active_date): ?>
              <span class="badge bg-primary rounded-pill px-1.5 py-0.5 ms-0.5" style="font-size: 0.65rem;">Active</span>
            <?php endif; ?>
          </span>
          <i class="bi bi-chevron-down policy-chevron"></i>
        </button>
        
        <div class="dropdown-menu dropdown-menu-end shadow-lg border rounded-4 p-3" style="width: 320px; z-index: 1060;" aria-labelledby="dateFilterDropdownBtn">
          <div class="d-flex align-items-center justify-content-between pb-2 mb-2 border-bottom">
            <span class="fw-bold small text-dark"><i class="bi bi-calendar3 me-1.5 text-primary"></i>Filter by Date</span>
            <?php if ($has_active_date): ?>
              <a href="javascript:void(0);" onclick="applyDateFilter('');" class="text-danger small text-decoration-none fw-semibold">Reset</a>
            <?php endif; ?>
          </div>
          
          <!-- Quick Preset Buttons -->
          <div class="mb-3">
            <div class="text-muted fw-semibold small mb-2" style="font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.5px;">Quick Presets</div>
            <div class="d-flex flex-wrap gap-1.5">
              <button type="button" onclick="applyDateFilter('')" class="btn btn-sm rounded-3 <?= empty($timeframe_filter) && empty($_GET['date_from']) ? 'btn-primary' : 'btn-light text-dark border' ?>" style="font-size: 0.78rem;">All Dates</button>
              <button type="button" onclick="applyDateFilter('today')" class="btn btn-sm rounded-3 <?= ($timeframe_filter === 'today') ? 'btn-primary' : 'btn-light text-dark border' ?>" style="font-size: 0.78rem;">Today</button>
              <button type="button" onclick="applyDateFilter('last_7_days')" class="btn btn-sm rounded-3 <?= ($timeframe_filter === 'last_7_days') ? 'btn-primary' : 'btn-light text-dark border' ?>" style="font-size: 0.78rem;">Last 7 Days</button>
              <button type="button" onclick="applyDateFilter('last_30_days')" class="btn btn-sm rounded-3 <?= ($timeframe_filter === 'last_30_days') ? 'btn-primary' : 'btn-light text-dark border' ?>" style="font-size: 0.78rem;">Last 30 Days</button>
              <button type="button" onclick="applyDateFilter('this_month')" class="btn btn-sm rounded-3 <?= ($timeframe_filter === 'this_month') ? 'btn-primary' : 'btn-light text-dark border' ?>" style="font-size: 0.78rem;">This Month</button>
              <button type="button" onclick="applyDateFilter('last_month')" class="btn btn-sm rounded-3 <?= ($timeframe_filter === 'last_month') ? 'btn-primary' : 'btn-light text-dark border' ?>" style="font-size: 0.78rem;">Last Month</button>
            </div>
          </div>

          <!-- Yearly Filter -->
          <div class="mb-3">
            <div class="text-muted fw-semibold small mb-2" style="font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.5px;">By Year</div>
            <div class="d-flex gap-1.5">
              <button type="button" onclick="applyDateFilter('2026')" class="btn btn-sm rounded-3 flex-fill <?= ($timeframe_filter === '2026' || $timeframe_filter === 'this_year') ? 'btn-primary' : 'btn-light text-dark border' ?>" style="font-size: 0.78rem;">2026</button>
              <button type="button" onclick="applyDateFilter('2025')" class="btn btn-sm rounded-3 flex-fill <?= ($timeframe_filter === '2025') ? 'btn-primary' : 'btn-light text-dark border' ?>" style="font-size: 0.78rem;">2025</button>
              <button type="button" onclick="applyDateFilter('2024')" class="btn btn-sm rounded-3 flex-fill <?= ($timeframe_filter === '2024') ? 'btn-primary' : 'btn-light text-dark border' ?>" style="font-size: 0.78rem;">2024</button>
            </div>
          </div>

          <!-- Custom Date Range -->
          <div class="pt-2 border-top">
            <div class="text-muted fw-semibold small mb-2" style="font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.5px;">Custom Range</div>
            <div class="row g-1.5 mb-2">
              <div class="col-6">
                <input type="date" id="customDateFrom" class="form-control form-control-sm rounded-2" style="font-size: 0.75rem;" value="<?= htmlspecialchars($_GET['date_from'] ?? '') ?>" placeholder="From">
              </div>
              <div class="col-6">
                <input type="date" id="customDateTo" class="form-control form-control-sm rounded-2" style="font-size: 0.75rem;" value="<?= htmlspecialchars($_GET['date_to'] ?? '') ?>" placeholder="To">
              </div>
            </div>
            <button type="button" onclick="applyCustomDateRange()" class="btn btn-primary btn-sm rounded-3 w-100 fw-semibold" style="font-size: 0.8rem;">
              <i class="bi bi-check2 me-1"></i> Apply Range
            </button>
          </div>
        </div>
      </div>

      <!-- Action / Record Count (Far Right) -->
      <div class="d-flex align-items-center justify-content-end gap-2 ms-auto text-end" style="flex-shrink: 0;">
        <?php if (!empty($search) || !empty($category_filter) || $has_active_date): ?>
          <a href="staff_dashboard.php?section=policyResearchSection<?= !empty($status_filter) ? '&status=' . urlencode($status_filter) : '' ?>" 
             class="btn btn-outline-danger btn-sm rounded-3 text-nowrap d-inline-flex align-items-center gap-1 shadow-2xs" title="Reset all filters" style="height: 38px;">
            <i class="bi bi-x-circle"></i> Reset
          </a>
        <?php endif; ?>
        <span class="text-muted small text-nowrap"><strong><?= count($policies ?? []) ?></strong> records</span>
      </div>
    </form>

    <script>
      function applyDateFilter(timeframeVal) {
        document.getElementById('filterTimeframeInput').value = timeframeVal;
        document.getElementById('filterDateFromInput').value = '';
        document.getElementById('filterDateToInput').value = '';
        document.getElementById('policyFilterForm').submit();
      }

      function applyCustomDateRange() {
        var fromVal = document.getElementById('customDateFrom').value;
        var toVal = document.getElementById('customDateTo').value;
        if (!fromVal && !toVal) return;
        document.getElementById('filterTimeframeInput').value = '';
        document.getElementById('filterDateFromInput').value = fromVal;
        document.getElementById('filterDateToInput').value = toVal;
        document.getElementById('policyFilterForm').submit();
      }
    </script>

    <!-- Policy Table -->
    <div class="table-responsive border rounded-4 overflow-hidden mb-3">
      <table class="table table-hover align-middle mb-0">
        <thead class="policy-table-thead">
          <tr>
            <th class="py-3 px-3 text-uppercase">Title</th>
            <th class="py-3 px-3 text-uppercase">Category</th>
            <th class="py-3 px-3 text-uppercase">Prepared By</th>
            <th class="py-3 px-3 text-uppercase">Status</th>
            <th class="py-3 px-3 text-uppercase">Date</th>
            <th class="py-3 px-3 text-center text-uppercase">Actions</th>
          </tr>
        </thead>
        <tbody id="policyTableBody">
          <?php if (!empty($policies)): ?>
            <?php foreach ($policies as $policy): ?>
              <tr>
                <td class="py-3 px-3">
                  <div class="fw-bold text-dark"><?= htmlspecialchars($policy['title']) ?></div>
                  <small class="text-muted"><?= htmlspecialchars($policy['description'] ?? '') ?></small>
                </td>
                <td class="py-3 px-3"><?= renderPolicyCategoryBadge($policy['category'] ?? '') ?></td>
                <td class="py-3 px-3"><?= htmlspecialchars($policy['author']) ?></td>
                <td class="py-3 px-3">
                  <?php
                  $statusVal = trim($policy['status'] ?? 'Draft');
                  $statusLower = strtolower($statusVal);
                  $statusClass = 'bg-secondary text-white';
                  if ($statusLower === 'approved' || $statusLower === 'published') {
                    $statusClass = 'bg-success text-white';
                  } elseif ($statusLower === 'draft') {
                    $statusClass = 'bg-warning text-dark';
                  } elseif ($statusLower === 'archived' || $statusLower === 'rejected') {
                    $statusClass = 'bg-danger text-white';
                  } elseif ($statusLower === 'needs revision') {
                    $statusClass = 'bg-danger text-white';
                  } elseif ($statusLower === 'under review' || $statusLower === 'in progress') {
                    $statusClass = 'bg-info text-dark';
                  } elseif ($statusLower === 'pending' || $statusLower === 'pending approval') {
                    $statusClass = 'bg-warning text-dark';
                  }
                  ?>
                  <span class="badge <?= $statusClass ?> policy-status-badge"><?= htmlspecialchars($statusVal) ?></span>
                </td>
                <td class="py-3 px-3">
                  <div class="report-date-cell">
                    <i class="bi bi-calendar3"></i>
                    <span class="report-date-text"><?= htmlspecialchars($policy['publication_date'] ?? 'N/A') ?></span>
                  </div>
                </td>
                <td>
                  <div class="action-btn-group d-flex align-items-center gap-1.5">
                    <?php if (!empty($policy['id'])): ?>
                      <a href="../backend/view_policy_document.php?id=<?= (int) $policy['id'] ?>" target="_blank"
                        class="btn btn-policy-action btn-policy-action-view" title="View Document File">
                        <i class="bi bi-file-earmark-text-fill"></i>
                      </a>
                    <?php elseif (!empty($policy['file_path'])): ?>
                      <a href="../assets/uploads/policies/<?= htmlspecialchars($policy['file_path']) ?>" target="_blank"
                        class="btn btn-policy-action btn-policy-action-view" title="View Document File">
                        <i class="bi bi-file-earmark-text-fill"></i>
                      </a>
                    <?php else: ?>
                      <button class="btn btn-policy-action btn-policy-action-view opacity-50" disabled title="No Document File">
                        <i class="bi bi-file-earmark-text"></i>
                      </button>
                    <?php endif; ?>

                    <!-- Refined AI Summarization Button -->
                    <button class="btn btn-policy-action btn-policy-action-ai" title="AI Document Summary & Key Highlights"
                      onclick='triggerAISummarizer(<?= (int) $policy["id"] ?>, <?= json_encode($policy["title"]) ?>, <?= json_encode($policy["file_path"] ?? "") ?>, <?= json_encode($policy["ai_summary"] ?? null) ?>)'>
                      <i class="bi bi-stars"></i>
                    </button>


                    <?php if (($policy['status'] ?? '') === 'Archived'): ?>
                      <form method="POST" action="staff_dashboard.php" class="d-inline"
                        onsubmit="return confirm('Restore this policy record back to Published?');">
                        <input type="hidden" name="action" value="restore">
                        <input type="hidden" name="id" value="<?= $policy['id'] ?>">
                        <input type="hidden" name="section" value="policyResearchSection">
                        <button type="submit" class="btn btn-policy-action btn-policy-action-restore" title="Restore Policy Record">
                          <i class="bi bi-arrow-counterclockwise"></i>
                        </button>
                      </form>
                      <form method="POST" action="staff_dashboard.php" class="d-inline"
                        onsubmit="return confirm('Are you sure you want to permanently delete this archived policy? This action cannot be undone.');">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" value="<?= $policy['id'] ?>">
                        <input type="hidden" name="section" value="policyResearchSection">
                        <input type="hidden" name="status" value="Archived">
                        <button type="submit" class="btn btn-policy-action btn-policy-action-delete" title="Permanently Delete Policy">
                          <i class="bi bi-trash-fill"></i>
                        </button>
                      </form>
                    <?php else: ?>
                      <form method="POST" action="staff_dashboard.php" class="d-inline"
                        onsubmit="return confirm('Archive this policy record? You can restore it anytime from the Archived tab.');">
                        <input type="hidden" name="action" value="archive">
                        <input type="hidden" name="id" value="<?= $policy['id'] ?>">
                        <input type="hidden" name="section" value="policyResearchSection">
                        <button type="submit" class="btn btn-policy-action btn-policy-action-archive"
                          title="Archive Policy Record">
                          <i class="bi bi-archive-fill"></i>
                        </button>
                      </form>
                    <?php endif; ?>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="6" class="text-center text-muted py-4">No policy records found.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</section>