<!-- 2. POLICY LIBRARY SUBMODULE (USER PORTAL VIEW) -->
<section id="policyLibrarySection" class="content-section <?= ($active_section ?? 'userDashboardSection') !== 'policyLibrarySection' ? 'd-none' : '' ?>">
  <div class="card border shadow-sm rounded-3 p-4 mb-4 bg-white">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
      <div>
        <h2 class="h4 fw-bold text-dark mb-1">
          <i class="bi bi-journal-bookmark-fill text-primary me-2"></i>Policy Research
        </h2>
        <p class="text-muted small mb-0">Browse official Manila City Hall ordinances and legislative resolutions. You may view details, read the AI-generated summary, or download the document.</p>
      </div>
    </div>

    <?php
    if (!function_exists('renderPolicyCategoryBadge')) {
      function renderPolicyCategoryBadge($category)
      {
        $cat = trim($category ?? '');
        $catLower = strtolower($cat);

        // 1. Health and Sanitation (#E1F5EE, #085041)
        if (strpos($catLower, 'health') !== false || strpos($catLower, 'sanitation') !== false) {
          $bg = '#E1F5EE';
          $text = '#085041';
          $border = '#9FE1CB';
          $icon = 'bi-heart-pulse-fill';
          $label = !empty($cat) ? $cat : 'Health and Sanitation';
        }
        // 2. Civil Registry and Public Services (#E6F1FB, #0C447C)
        elseif (strpos($catLower, 'civil') !== false || strpos($catLower, 'registry') !== false || strpos($catLower, 'public service') !== false) {
          $bg = '#E6F1FB';
          $text = '#0C447C';
          $border = '#B5D7F8';
          $icon = 'bi-building';
          $label = !empty($cat) ? $cat : 'Civil Registry and Public Services';
        }
        // 3. Education and Employment (#EEEDFE, #3C3489)
        elseif (strpos($catLower, 'education') !== false || strpos($catLower, 'employment') !== false || strpos($catLower, 'school') !== false) {
          $bg = '#EEEDFE';
          $text = '#3C3489';
          $border = '#CECBF6';
          $icon = 'bi-book-half';
          $label = !empty($cat) ? $cat : 'Education and Employment';
        }
        // 4. Social Welfare and Community Affairs (#FAECE7, #712B13)
        elseif (strpos($catLower, 'social') !== false || strpos($catLower, 'welfare') !== false || strpos($catLower, 'community') !== false) {
          $bg = '#FAECE7';
          $text = '#712B13';
          $border = '#F5C4B5';
          $icon = 'bi-people-fill';
          $label = !empty($cat) ? $cat : 'Social Welfare and Community Affairs';
        }
        // 5. Infrastructure, Traffic and Environment (#EAF3DE, #27500A)
        elseif (strpos($catLower, 'infra') !== false || strpos($catLower, 'traffic') !== false || strpos($catLower, 'environment') !== false || strpos($catLower, 'road') !== false) {
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
      /* Refined Civic Category Badges (Light Background + Dark Text) - Compact & Sleek */
      .category-badge-pill {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 3px 9px;
        border-radius: 9999px;
        font-size: 0.75rem;
        font-weight: 600;
        white-space: nowrap;
        letter-spacing: -0.01em;
        line-height: 1.3;
        transition: all 0.18s cubic-bezier(0.16, 1, 0.3, 1);
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.03);
      }

      .category-badge-pill i {
        font-size: 0.75rem;
        flex-shrink: 0;
      }

      tr:hover .category-badge-pill {
        transform: translateY(-1px);
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.07);
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
        color: #0F172A !important;
      }

      .policy-category-wrapper {
        position: relative;
        flex-shrink: 0;
      }

      .policy-category-select {
        background: #FFFFFF !important;
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

      /* Option 1: Civic Slate Metadata Chip */
      .report-date-cell {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        font-size: 0.81rem;
        color: #334155;
        font-weight: 500;
        font-variant-numeric: tabular-nums;
        white-space: nowrap;
        letter-spacing: -0.01em;
        background: #F8FAFC !important;
        border: 1px solid #E2E8F0 !important;
        border-radius: 6px;
        padding: 3.5px 9.5px !important;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.02) !important;
        transition: all 0.18s cubic-bezier(0.16, 1, 0.3, 1);
      }

      .report-date-cell i {
        color: #0B2E59 !important;
        opacity: 0.78;
        font-size: 0.8rem;
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
    $pl_timeframe_labels = [
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
    $pl_current_timeframe_label = $pl_timeframe_labels[$pl_timeframe ?? ''] ?? 'All Dates';
    $pl_has_active_date = !empty($pl_timeframe) || !empty($_GET['pl_date_from']) || !empty($_GET['pl_date_to']);
    if (!empty($_GET['pl_date_from']) && !empty($_GET['pl_date_to'])) {
      $pl_current_timeframe_label = date('M d', strtotime($_GET['pl_date_from'])) . ' - ' . date('M d', strtotime($_GET['pl_date_to']));
    } elseif (!empty($_GET['pl_date_from'])) {
      $pl_current_timeframe_label = 'From ' . date('M d', strtotime($_GET['pl_date_from']));
    } elseif (!empty($_GET['pl_date_to'])) {
      $pl_current_timeframe_label = 'Until ' . date('M d', strtotime($_GET['pl_date_to']));
    }
    ?>

    <!-- Search Bar & Category & Timeframe Filter (Clean Minimalist Toolbar) -->
    <form method="GET" action="user_dashboard.php" class="d-flex flex-wrap flex-md-nowrap align-items-center gap-2.5 mb-4" id="policyLibraryForm">
      <input type="hidden" name="section" value="policyLibrarySection">
      <input type="hidden" name="pl_timeframe" id="userFilterTimeframeInput" value="<?= htmlspecialchars($pl_timeframe ?? '') ?>">
      <input type="hidden" name="pl_date_from" id="userFilterDateFromInput" value="<?= htmlspecialchars($_GET['pl_date_from'] ?? '') ?>">
      <input type="hidden" name="pl_date_to" id="userFilterDateToInput" value="<?= htmlspecialchars($_GET['pl_date_to'] ?? '') ?>">
      
      <!-- Search Input -->
      <div class="policy-search-box flex-grow-1" style="min-width: 250px;">
        <i class="bi bi-search policy-search-icon"></i>
        <input type="text" name="pl_search" id="userPolicySearch" class="policy-search-input"
          placeholder="Search ordinances by title or keyword..."
          value="<?= htmlspecialchars($pl_search ?? '') ?>"
          onkeydown="if(event.key === 'Enter') this.form.submit();">
        <?php if (!empty($pl_search)): ?>
          <button class="policy-search-clear" type="button" onclick="this.form.pl_search.value=''; this.form.submit();" title="Clear search">
            <i class="bi bi-x-lg"></i>
          </button>
        <?php endif; ?>
      </div>

      <!-- Category Filter -->
      <div class="policy-category-wrapper">
        <select name="pl_category" id="userPolicyCategory" class="form-select policy-category-select" onchange="this.form.submit()">
          <option value="">All Categories</option>
          <option value="Health and Sanitation" <?= (($pl_category ?? '') === 'Health and Sanitation' || ($pl_category ?? '') === 'Health') ? 'selected' : '' ?>>Health and Sanitation</option>
          <option value="Civil Registry and Public Services" <?= (($pl_category ?? '') === 'Civil Registry and Public Services') ? 'selected' : '' ?>>Civil Registry and Public Services</option>
          <option value="Education and Employment" <?= (($pl_category ?? '') === 'Education and Employment' || ($pl_category ?? '') === 'Education') ? 'selected' : '' ?>>Education and Employment</option>
          <option value="Social Welfare and Community Affairs" <?= (($pl_category ?? '') === 'Social Welfare and Community Affairs') ? 'selected' : '' ?>>Social Welfare and Community Affairs</option>
          <option value="Infrastructure, Traffic and Environment" <?= (($pl_category ?? '') === 'Infrastructure, Traffic and Environment' || ($pl_category ?? '') === 'Infrastructure' || ($pl_category ?? '') === 'Environment') ? 'selected' : '' ?>>Infrastructure, Traffic and Environment</option>
          <option value="Other" <?= (($pl_category ?? '') === 'Other') ? 'selected' : '' ?>>Other</option>
        </select>
      </div>

      <!-- Compact Date Filter Button & Popover -->
      <div class="dropdown position-relative" style="flex-shrink: 0;">
        <button class="btn policy-date-filter-btn <?= $pl_has_active_date ? 'border-primary bg-primary bg-opacity-10 text-primary' : '' ?>" 
                type="button" id="userDateFilterDropdownBtn" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
          <span class="d-inline-flex align-items-center gap-2">
            <i class="bi <?= $pl_has_active_date ? 'bi-calendar3-fill text-primary' : 'bi-calendar3' ?>" style="color: <?= $pl_has_active_date ? '#0D6EFD' : '#64748B' ?>; font-size: 0.86rem;"></i>
            <span><?= $pl_has_active_date ? htmlspecialchars($pl_current_timeframe_label) : 'Filter Date' ?></span>
            <?php if ($pl_has_active_date): ?>
              <span class="badge bg-primary rounded-pill px-1.5 py-0.5 ms-0.5" style="font-size: 0.65rem;">Active</span>
            <?php endif; ?>
          </span>
          <i class="bi bi-chevron-down policy-chevron"></i>
        </button>
        
        <div class="dropdown-menu dropdown-menu-end shadow-lg border rounded-4 p-3" style="width: 320px; z-index: 1060;" aria-labelledby="userDateFilterDropdownBtn">
          <div class="d-flex align-items-center justify-content-between pb-2 mb-2 border-bottom">
            <span class="fw-bold small text-dark"><i class="bi bi-calendar3 me-1.5 text-primary"></i>Filter by Date</span>
            <?php if ($pl_has_active_date): ?>
              <a href="javascript:void(0);" onclick="applyUserDateFilter('');" class="text-danger small text-decoration-none fw-semibold">Reset</a>
            <?php endif; ?>
          </div>
          
          <!-- Quick Preset Buttons -->
          <div class="mb-3">
            <div class="text-muted fw-semibold small mb-2" style="font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.5px;">Quick Presets</div>
            <div class="d-flex flex-wrap gap-1.5">
              <button type="button" onclick="applyUserDateFilter('')" class="btn btn-sm rounded-3 <?= empty($pl_timeframe) && empty($_GET['pl_date_from']) ? 'btn-primary' : 'btn-light text-dark border' ?>" style="font-size: 0.78rem;">All Dates</button>
              <button type="button" onclick="applyUserDateFilter('today')" class="btn btn-sm rounded-3 <?= ($pl_timeframe === 'today') ? 'btn-primary' : 'btn-light text-dark border' ?>" style="font-size: 0.78rem;">Today</button>
              <button type="button" onclick="applyUserDateFilter('last_7_days')" class="btn btn-sm rounded-3 <?= ($pl_timeframe === 'last_7_days') ? 'btn-primary' : 'btn-light text-dark border' ?>" style="font-size: 0.78rem;">Last 7 Days</button>
              <button type="button" onclick="applyUserDateFilter('last_30_days')" class="btn btn-sm rounded-3 <?= ($pl_timeframe === 'last_30_days') ? 'btn-primary' : 'btn-light text-dark border' ?>" style="font-size: 0.78rem;">Last 30 Days</button>
              <button type="button" onclick="applyUserDateFilter('this_month')" class="btn btn-sm rounded-3 <?= ($pl_timeframe === 'this_month') ? 'btn-primary' : 'btn-light text-dark border' ?>" style="font-size: 0.78rem;">This Month</button>
              <button type="button" onclick="applyUserDateFilter('last_month')" class="btn btn-sm rounded-3 <?= ($pl_timeframe === 'last_month') ? 'btn-primary' : 'btn-light text-dark border' ?>" style="font-size: 0.78rem;">Last Month</button>
            </div>
          </div>

          <!-- Yearly Filter -->
          <div class="mb-3">
            <div class="text-muted fw-semibold small mb-2" style="font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.5px;">By Year</div>
            <div class="d-flex gap-1.5">
              <button type="button" onclick="applyUserDateFilter('2026')" class="btn btn-sm rounded-3 flex-fill <?= ($pl_timeframe === '2026' || $pl_timeframe === 'this_year') ? 'btn-primary' : 'btn-light text-dark border' ?>" style="font-size: 0.78rem;">2026</button>
              <button type="button" onclick="applyUserDateFilter('2025')" class="btn btn-sm rounded-3 flex-fill <?= ($pl_timeframe === '2025') ? 'btn-primary' : 'btn-light text-dark border' ?>" style="font-size: 0.78rem;">2025</button>
              <button type="button" onclick="applyUserDateFilter('2024')" class="btn btn-sm rounded-3 flex-fill <?= ($pl_timeframe === '2024') ? 'btn-primary' : 'btn-light text-dark border' ?>" style="font-size: 0.78rem;">2024</button>
            </div>
          </div>

          <!-- Custom Date Range -->
          <div class="pt-2 border-top">
            <div class="text-muted fw-semibold small mb-2" style="font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.5px;">Custom Range</div>
            <div class="row g-1.5 mb-2">
              <div class="col-6">
                <input type="date" id="userCustomDateFrom" class="form-control form-control-sm rounded-2" style="font-size: 0.75rem;" value="<?= htmlspecialchars($_GET['pl_date_from'] ?? '') ?>" placeholder="From">
              </div>
              <div class="col-6">
                <input type="date" id="userCustomDateTo" class="form-control form-control-sm rounded-2" style="font-size: 0.75rem;" value="<?= htmlspecialchars($_GET['pl_date_to'] ?? '') ?>" placeholder="To">
              </div>
            </div>
            <button type="button" onclick="applyUserCustomDateRange()" class="btn btn-primary btn-sm rounded-3 w-100 fw-semibold" style="font-size: 0.8rem;">
              <i class="bi bi-check2 me-1"></i> Apply Range
            </button>
          </div>
        </div>
      </div>

      <!-- Action / Record Count (Far Right) -->
      <div class="d-flex align-items-center justify-content-end gap-2 ms-auto text-end" style="flex-shrink: 0;">
        <?php if (($pl_search ?? '') !== '' || ($pl_category ?? '') !== '' || $pl_has_active_date): ?>
          <a href="user_dashboard.php?section=policyLibrarySection"
            class="btn btn-outline-danger btn-sm rounded-3 text-nowrap d-inline-flex align-items-center gap-1 shadow-2xs" title="Reset all filters" style="height: 38px;">
            <i class="bi bi-x-circle"></i> Reset
          </a>
        <?php endif; ?>
        <span class="text-muted small text-nowrap"><strong><?= count($pl_policies ?? []) ?></strong> records</span>
      </div>
    </form>

    <script>
      function applyUserDateFilter(timeframeVal) {
        document.getElementById('userFilterTimeframeInput').value = timeframeVal;
        document.getElementById('userFilterDateFromInput').value = '';
        document.getElementById('userFilterDateToInput').value = '';
        document.getElementById('policyLibraryForm').submit();
      }

      function applyUserCustomDateRange() {
        var fromVal = document.getElementById('userCustomDateFrom').value;
        var toVal = document.getElementById('userCustomDateTo').value;
        if (!fromVal && !toVal) return;
        document.getElementById('userFilterTimeframeInput').value = '';
        document.getElementById('userFilterDateFromInput').value = fromVal;
        document.getElementById('userFilterDateToInput').value = toVal;
        document.getElementById('policyLibraryForm').submit();
      }
    </script>

    <!-- Policy Table -->
    <div class="table-responsive border rounded-4 overflow-hidden mb-3">
      <table class="table table-hover align-middle mb-0 w-100" id="userPolicyTable" style="width:100%; font-size: 0.88rem;">
        <thead class="policy-table-thead">
          <tr>
            <th class="py-3 px-3 text-uppercase" style="min-width: 320px;">Doc No. &amp; Title</th>
            <th class="py-3 px-3 text-uppercase" style="width: 170px;">Category</th>
            <th class="py-3 px-3 text-uppercase" style="width: 180px;">Author</th>
            <th class="py-3 px-3 text-uppercase" style="width: 110px;">Status</th>
            <th class="py-3 px-3 text-uppercase" style="width: 130px;">Date</th>
            <th class="py-3 px-3 text-center text-uppercase" style="width: 140px;">Actions</th>
          </tr>
        </thead>
        <tbody id="userPolicyTableBody">
          <?php if (!empty($pl_policies)): ?>
            <?php foreach ($pl_policies as $policy): ?>
              <?php
              $statusVal = trim($policy['status'] ?? 'Draft');
              $statusLower = strtolower($statusVal);
              $statusClass = 'bg-secondary';
              if ($statusLower === 'approved' || $statusLower === 'published' || $statusLower === 'enacted')
                $statusClass = 'bg-success';
              elseif ($statusLower === 'draft')
                $statusClass = 'bg-warning text-dark';
              elseif ($statusLower === 'archived' || $statusLower === 'rejected' || $statusLower === 'needs revision')
                $statusClass = 'bg-danger';
              elseif ($statusLower === 'under review')
                $statusClass = 'bg-info text-dark';
              elseif ($statusLower === 'pending' || $statusLower === 'pending approval')
                $statusClass = 'bg-warning text-dark';
              $hasAI = !empty($policy['ai_summary']);
              $hasFile = !empty($policy['file_path']);
              ?>
              <tr>
                <td class="py-3 px-3">
                  <div class="fw-bold text-dark mb-0.5"><?= htmlspecialchars($policy['title']) ?></div>
                  <small class="text-muted d-block line-clamp-2"><?= htmlspecialchars($policy['description'] ?? '') ?></small>
                </td>
                <td class="py-3 px-3"><?= renderPolicyCategoryBadge($policy['category'] ?? '') ?></td>
                <td class="py-3 px-3 text-muted small"><?= htmlspecialchars($policy['author']) ?></td>
                <td class="py-3 px-3"><span class="badge <?= $statusClass ?> rounded-pill px-2.5 py-1"><?= htmlspecialchars($policy['status']) ?></span></td>
                <td class="py-3 px-3" style="white-space: nowrap;">
                  <div class="report-date-cell">
                    <i class="bi bi-calendar3"></i>
                    <span class="report-date-text"><?= htmlspecialchars($policy['publication_date'] ?? 'N/A') ?></span>
                  </div>
                </td>
                <td class="py-3 px-3" style="white-space: nowrap; text-align: center;">
                  <div class="action-btn-group d-inline-flex align-items-center justify-content-center gap-1.5 flex-nowrap" style="white-space: nowrap;">
                    <!-- 1. View Document File (Admin Matched Design) -->
                    <button type="button" class="btn btn-policy-action btn-policy-action-view" title="View Policy Details & Document" onclick='openPolicyViewModal(<?= json_encode([
                      "id" => (int) $policy["id"],
                      "title" => $policy["title"],
                      "category" => $policy["category"],
                      "author" => $policy["author"],
                      "status" => $policy["status"],
                      "date" => $policy["publication_date"] ?? "N/A",
                      "desc" => $policy["description"] ?? "",
                      "file" => $policy["file_path"] ?? ""
                    ]) ?>)'>
                      <i class="bi bi-file-earmark-text-fill"></i>
                    </button>

                    <!-- 2. AI Summarization Button (Admin Matched Design) -->
                    <button type="button" class="btn btn-policy-action btn-policy-action-ai" title="AI Document Summary & Key Highlights"
                      onclick='openAISummaryModal(<?= json_encode($policy["title"]) ?>, <?= json_encode($policy["ai_summary"] ?? "") ?>, <?= json_encode($policy["category"]) ?>)'>
                      <i class="bi bi-stars"></i>
                    </button>

                    <!-- 3. Download Document (Admin Matched Design) -->
                    <?php if ($hasFile): ?>
                      <a href="../assets/uploads/policies/<?= htmlspecialchars($policy['file_path']) ?>" download
                        class="btn btn-policy-action btn-policy-action-download" title="Download Official Document">
                        <i class="bi bi-download"></i>
                      </a>
                    <?php else: ?>
                      <button type="button" class="btn btn-policy-action btn-policy-action-download opacity-50" title="No file available" disabled>
                        <i class="bi bi-download"></i>
                      </button>
                    <?php endif; ?>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="6" class="text-center text-muted py-5">
                <i class="bi bi-folder2-open fs-2 d-block mb-2 opacity-50"></i>
                No policy records found<?= (($pl_search ?? '') !== '' || ($pl_category ?? '') !== '') ? ' matching your search criteria' : '' ?>.
              </td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</section>
