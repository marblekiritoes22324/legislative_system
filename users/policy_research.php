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

    <!-- Search Bar & Category & Timeframe Filter -->
    <form method="GET" action="user_dashboard.php" class="row g-2.5 mb-4 align-items-center" id="policyLibraryForm">
      <input type="hidden" name="section" value="policyLibrarySection">
      <input type="hidden" name="pl_timeframe" id="userFilterTimeframeInput" value="<?= htmlspecialchars($pl_timeframe ?? '') ?>">
      <input type="hidden" name="pl_date_from" id="userFilterDateFromInput" value="<?= htmlspecialchars($_GET['pl_date_from'] ?? '') ?>">
      <input type="hidden" name="pl_date_to" id="userFilterDateToInput" value="<?= htmlspecialchars($_GET['pl_date_to'] ?? '') ?>">
      
      <div class="col-12 col-md-5">
        <div class="input-group shadow-2xs">
          <span class="input-group-text bg-white border-end-0 text-primary"><i class="bi bi-search"></i></span>
          <input type="text" name="pl_search" id="userPolicySearch"
            class="form-control border-start-0 ps-0"
            placeholder="Search ordinances by title or keyword..."
            value="<?= htmlspecialchars($pl_search ?? '') ?>"
            oninput="if(this.value==='') this.form.submit();">
          <?php if (!empty($pl_search)): ?>
            <button class="btn btn-white bg-white border-start-0 border-end border-top border-bottom text-muted" type="button" onclick="this.form.pl_search.value=''; this.form.submit();">
              <i class="bi bi-x"></i>
            </button>
          <?php endif; ?>
        </div>
      </div>
      <div class="col-12 col-md-3">
        <div class="input-group shadow-2xs">
          <span class="input-group-text bg-white border-end-0 text-primary"><i class="bi bi-tag-fill"></i></span>
          <select name="pl_category" id="userPolicyCategory" class="form-select border-start-0 ps-0" onchange="this.form.submit()">
            <option value="">All Categories</option>
            <option value="Health and Sanitation" <?= (($pl_category ?? '') === 'Health and Sanitation' || ($pl_category ?? '') === 'Health') ? 'selected' : '' ?>>Health and Sanitation</option>
            <option value="Civil Registry and Public Services" <?= (($pl_category ?? '') === 'Civil Registry and Public Services') ? 'selected' : '' ?>>Civil Registry and Public Services</option>
            <option value="Education and Employment" <?= (($pl_category ?? '') === 'Education and Employment' || ($pl_category ?? '') === 'Education') ? 'selected' : '' ?>>Education and Employment</option>
            <option value="Social Welfare and Community Affairs" <?= (($pl_category ?? '') === 'Social Welfare and Community Affairs') ? 'selected' : '' ?>>Social Welfare and Community Affairs</option>
            <option value="Infrastructure, Traffic and Environment" <?= (($pl_category ?? '') === 'Infrastructure, Traffic and Environment' || ($pl_category ?? '') === 'Infrastructure' || ($pl_category ?? '') === 'Environment') ? 'selected' : '' ?>>Infrastructure, Traffic and Environment</option>
            <option value="Other" <?= (($pl_category ?? '') === 'Other') ? 'selected' : '' ?>>Other</option>
          </select>
        </div>
      </div>
      <!-- Compact Date Filter Button & Popover -->
      <div class="col-auto">
        <div class="dropdown">
          <button class="btn btn-white bg-white border d-inline-flex align-items-center gap-2 rounded-3 py-2 px-3 shadow-2xs <?= $pl_has_active_date ? 'border-primary bg-primary bg-opacity-10 text-primary' : 'text-dark' ?>" 
                  type="button" id="userDateFilterDropdownBtn" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false" style="min-height: 38px;">
            <i class="bi <?= $pl_has_active_date ? 'bi-funnel-fill text-primary' : 'bi-funnel text-primary' ?>"></i>
            <span class="fw-semibold small"><?= $pl_has_active_date ? htmlspecialchars($pl_current_timeframe_label) : 'Filter' ?></span>
            <?php if ($pl_has_active_date): ?>
              <span class="badge bg-primary rounded-pill px-1.5 py-0.5 ms-0.5" style="font-size: 0.65rem;">Active</span>
            <?php endif; ?>
            <i class="bi bi-chevron-down text-muted ms-1" style="font-size: 0.7rem;"></i>
          </button>
          
          <div class="dropdown-menu dropdown-menu-end shadow-lg border rounded-4 p-3" style="width: 320px; z-index: 1060;" aria-labelledby="userDateFilterDropdownBtn">
            <div class="d-flex align-items-center justify-content-between pb-2 mb-2 border-bottom">
              <span class="fw-bold small text-dark"><i class="bi bi-funnel-fill me-1.5 text-primary"></i>Filter by Date</span>
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
      </div>
      <!-- Action / Record Count (Far Right) -->
      <div class="col-12 col-md d-flex align-items-center justify-content-end gap-2 ms-auto text-end">
        <?php if (($pl_search ?? '') !== '' || ($pl_category ?? '') !== '' || $pl_has_active_date): ?>
          <a href="user_dashboard.php?section=policyLibrarySection"
            class="btn btn-outline-danger btn-sm py-1.5 px-2.5 rounded-3 text-nowrap d-inline-flex align-items-center gap-1 shadow-2xs" title="Reset all filters">
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
    <div class="table-responsive border rounded-3 overflow-hidden">
      <table class="table table-hover align-middle mb-0 w-100" id="userPolicyTable" style="width:100%; font-size: 0.88rem;">
        <thead class="table-light">
          <tr>
            <th style="min-width: 320px;">Doc No. &amp; Title</th>
            <th style="width: 150px;">Category</th>
            <th style="width: 180px;">Author</th>
            <th style="width: 110px;">Status</th>
            <th style="width: 110px;">Date</th>
            <th style="width: 140px; text-align: center;">Actions</th>
          </tr>
        </thead>
        <tbody id="userPolicyTableBody">
          <?php if (!empty($pl_policies)): ?>
            <?php foreach ($pl_policies as $policy): ?>
              <?php
              $statusClass = 'bg-secondary';
              if ($policy['status'] === 'Published' || $policy['status'] === 'Enacted')
                $statusClass = 'bg-success';
              elseif ($policy['status'] === 'Draft')
                $statusClass = 'bg-warning text-dark';
              elseif ($policy['status'] === 'Archived')
                $statusClass = 'bg-danger';
              elseif ($policy['status'] === 'Under Review')
                $statusClass = 'bg-info text-dark';
              $hasAI = !empty($policy['ai_summary']);
              $hasFile = !empty($policy['file_path']);
              ?>
              <tr>
                <td>
                  <div class="fw-bold text-dark mb-0.5"><?= htmlspecialchars($policy['title']) ?></div>
                  <small class="text-muted d-block line-clamp-2"><?= htmlspecialchars($policy['description'] ?? '') ?></small>
                </td>
                <td><span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-2.5 py-1 rounded-1 fw-semibold" style="font-size:0.75rem;"><?= htmlspecialchars($policy['category']) ?></span></td>
                <td class="text-muted small"><?= htmlspecialchars($policy['author']) ?></td>
                <td><span class="badge <?= $statusClass ?> rounded-pill px-2.5 py-1"><?= htmlspecialchars($policy['status']) ?></span></td>
                <td class="text-muted small" style="white-space: nowrap;"><?= htmlspecialchars($policy['publication_date'] ?? 'N/A') ?></td>
                <td style="white-space: nowrap; text-align: center;">
                  <div class="action-btn-group d-inline-flex align-items-center justify-content-center gap-1.5 flex-nowrap" style="white-space: nowrap;">
                    <!-- 1. View Document File (Admin Matched Design) -->
                    <button type="button" class="btn btn-policy-action btn-policy-action-view" title="View Policy Details & Document" onclick='openPolicyViewModal(<?= json_encode([
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
