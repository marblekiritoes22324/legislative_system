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

    <!-- Search & Filters -->
    <form method="GET" action="staff_dashboard.php" class="row g-2.5 mb-4 align-items-center" id="policyFilterForm">
      <input type="hidden" name="section" value="policyResearchSection">
      <input type="hidden" name="timeframe" id="filterTimeframeInput" value="<?= htmlspecialchars($timeframe_filter ?? '') ?>">
      <input type="hidden" name="date_from" id="filterDateFromInput" value="<?= htmlspecialchars($_GET['date_from'] ?? '') ?>">
      <input type="hidden" name="date_to" id="filterDateToInput" value="<?= htmlspecialchars($_GET['date_to'] ?? '') ?>">
      <?php if (!empty($status_filter)): ?>
        <input type="hidden" name="status" value="<?= htmlspecialchars($status_filter) ?>">
      <?php endif; ?>
      <!-- Search Input -->
      <div class="col-12 col-md-5">
        <div class="input-group shadow-2xs">
          <span class="input-group-text bg-white border-end-0 text-primary"><i class="bi bi-search"></i></span>
          <input type="text" name="search" class="form-control border-start-0 ps-0"
            placeholder="Search ordinances by title or keyword..." value="<?= htmlspecialchars($search ?? '') ?>">
          <?php if (!empty($search)): ?>
            <button class="btn btn-white bg-white border-start-0 border-end border-top border-bottom text-muted" type="button" onclick="this.form.search.value=''; this.form.submit();">
              <i class="bi bi-x"></i>
            </button>
          <?php endif; ?>
        </div>
      </div>

      <!-- Category Filter -->
      <div class="col-12 col-md-3">
        <div class="input-group shadow-2xs">
          <span class="input-group-text bg-white border-end-0 text-primary"><i class="bi bi-tag-fill"></i></span>
          <select name="category" class="form-select border-start-0 ps-0" onchange="this.form.submit()">
            <option value="">All Categories</option>
            <option value="Health and Sanitation" <?= (($category_filter ?? '') === 'Health and Sanitation' || ($category_filter ?? '') === 'Health') ? 'selected' : '' ?>>Health and Sanitation</option>
            <option value="Civil Registry and Public Services" <?= (($category_filter ?? '') === 'Civil Registry and Public Services') ? 'selected' : '' ?>>Civil Registry and Public Services</option>
            <option value="Education and Employment" <?= (($category_filter ?? '') === 'Education and Employment' || ($category_filter ?? '') === 'Education') ? 'selected' : '' ?>>Education and Employment</option>
            <option value="Social Welfare and Community Affairs" <?= (($category_filter ?? '') === 'Social Welfare and Community Affairs') ? 'selected' : '' ?>>Social Welfare and Community Affairs</option>
            <option value="Infrastructure, Traffic and Environment" <?= (($category_filter ?? '') === 'Infrastructure, Traffic and Environment' || ($category_filter ?? '') === 'Infrastructure' || ($category_filter ?? '') === 'Environment') ? 'selected' : '' ?>>Infrastructure, Traffic and Environment</option>
            <option value="Other" <?= (($category_filter ?? '') === 'Other') ? 'selected' : '' ?>>Other</option>
          </select>
        </div>
      </div>

      <!-- Compact Date Filter Button & Popover -->
      <div class="col-auto">
        <div class="dropdown">
          <button class="btn btn-white bg-white border d-inline-flex align-items-center gap-2 rounded-3 py-2 px-3 shadow-2xs <?= $has_active_date ? 'border-primary bg-primary bg-opacity-10 text-primary' : 'text-dark' ?>" 
                  type="button" id="dateFilterDropdownBtn" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false" style="min-height: 38px;">
            <i class="bi <?= $has_active_date ? 'bi-funnel-fill text-primary' : 'bi-funnel text-primary' ?>"></i>
            <span class="fw-semibold small"><?= $has_active_date ? htmlspecialchars($current_timeframe_label) : 'Filter' ?></span>
            <?php if ($has_active_date): ?>
              <span class="badge bg-primary rounded-pill px-1.5 py-0.5 ms-0.5" style="font-size: 0.65rem;">Active</span>
            <?php endif; ?>
            <i class="bi bi-chevron-down text-muted ms-1" style="font-size: 0.7rem;"></i>
          </button>
          
          <div class="dropdown-menu dropdown-menu-end shadow-lg border rounded-4 p-3" style="width: 320px; z-index: 1060;" aria-labelledby="dateFilterDropdownBtn">
            <div class="d-flex align-items-center justify-content-between pb-2 mb-2 border-bottom">
              <span class="fw-bold small text-dark"><i class="bi bi-funnel-fill me-1.5 text-primary"></i>Filter by Date</span>
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
      </div>

      <!-- Action / Record Count (Far Right) -->
      <div class="col-12 col-md d-flex align-items-center justify-content-end gap-2 ms-auto text-end">
        <?php if (!empty($search) || !empty($category_filter) || $has_active_date): ?>
          <a href="staff_dashboard.php?section=policyResearchSection<?= !empty($status_filter) ? '&status=' . urlencode($status_filter) : '' ?>" 
             class="btn btn-outline-danger btn-sm rounded-3 text-nowrap d-inline-flex align-items-center gap-1 shadow-2xs" title="Reset all filters">
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
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th>Doc No. &amp; Title</th>
            <th>Category</th>
            <th>Author</th>
            <th>Status</th>
            <th>Date</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody id="policyTableBody">
          <?php if (!empty($policies)): ?>
            <?php foreach ($policies as $policy): ?>
              <tr>
                <td>
                  <div class="fw-bold text-dark"><?= htmlspecialchars($policy['title']) ?></div>
                  <small class="text-muted"><?= htmlspecialchars($policy['description'] ?? '') ?></small>
                </td>
                <td><span class="badge bg-primary"><?= htmlspecialchars($policy['category']) ?></span></td>
                <td><?= htmlspecialchars($policy['author']) ?></td>
                <td>
                  <?php
                  $statusClass = 'bg-secondary';
                  if ($policy['status'] == 'Published')
                    $statusClass = 'bg-success';
                  if ($policy['status'] == 'Draft')
                    $statusClass = 'bg-warning text-dark';
                  if ($policy['status'] == 'Archived')
                    $statusClass = 'bg-danger';
                  ?>
                  <span class="badge <?= $statusClass ?>"><?= htmlspecialchars($policy['status']) ?></span>
                </td>
                <td><?= htmlspecialchars($policy['publication_date'] ?? 'N/A') ?></td>
                <td>
                  <div class="action-btn-group d-flex align-items-center gap-1.5">
                    <?php if (!empty($policy['file_path'])): ?>
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

                    <!-- Edit Policy Button -->
                    <button class="btn btn-policy-action btn-policy-action-edit" title="Edit Policy Details"
                      onclick='openEditPolicyModal(<?= json_encode($policy) ?>)'>
                      <i class="bi bi-pencil-square"></i>
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