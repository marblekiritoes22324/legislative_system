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

    <!-- Search & Filters -->
    <form method="GET" action="staff_dashboard.php" class="row g-3 mb-4 align-items-center" id="policyFilterForm">
      <input type="hidden" name="section" value="policyResearchSection">
      <?php if (!empty($status_filter)): ?>
        <input type="hidden" name="status" value="<?= htmlspecialchars($status_filter) ?>">
      <?php endif; ?>
      <div class="col-12 col-md-5">
        <div class="input-group shadow-2xs">
          <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
          <input type="text" name="search" class="form-control border-start-0"
            placeholder="Search ordinances by title or keyword..." value="<?= htmlspecialchars($search ?? '') ?>">
        </div>
      </div>
      <div class="col-12 col-md-3">
        <select name="category" class="form-select shadow-2xs" onchange="this.form.submit()">
          <option value="">All Categories</option>
          <option value="Health and Sanitation" <?= (($category_filter ?? '') === 'Health and Sanitation' || ($category_filter ?? '') === 'Health') ? 'selected' : '' ?>>Health and Sanitation</option>
          <option value="Civil Registry and Public Services" <?= (($category_filter ?? '') === 'Civil Registry and Public Services') ? 'selected' : '' ?>>Civil Registry and Public Services</option>
          <option value="Education and Employment" <?= (($category_filter ?? '') === 'Education and Employment' || ($category_filter ?? '') === 'Education') ? 'selected' : '' ?>>Education and Employment</option>
          <option value="Social Welfare and Community Affairs" <?= (($category_filter ?? '') === 'Social Welfare and Community Affairs') ? 'selected' : '' ?>>Social Welfare and Community Affairs</option>
          <option value="Infrastructure, Traffic and Environment" <?= (($category_filter ?? '') === 'Infrastructure, Traffic and Environment' || ($category_filter ?? '') === 'Infrastructure' || ($category_filter ?? '') === 'Environment') ? 'selected' : '' ?>>Infrastructure, Traffic and Environment</option>
          <option value="Other" <?= (($category_filter ?? '') === 'Other') ? 'selected' : '' ?>>Other</option>
        </select>
      </div>
      <div class="col-12 col-md-2">
        <div class="input-group shadow-2xs">
          <span class="input-group-text bg-white text-muted border-end-0" style="color: #0B2E59 !important;"><i class="bi bi-funnel"></i></span>
          <select name="timeframe" class="form-select border-start-0" onchange="this.form.submit()" title="Filter by Days, Months, or Years">
            <option value="">All Dates</option>
            <optgroup label="Filter by Days">
              <option value="today" <?= (($timeframe_filter ?? '') === 'today') ? 'selected' : '' ?>>Today</option>
              <option value="last_7_days" <?= (($timeframe_filter ?? '') === 'last_7_days') ? 'selected' : '' ?>>Last 7 Days</option>
              <option value="last_30_days" <?= (($timeframe_filter ?? '') === 'last_30_days') ? 'selected' : '' ?>>Last 30 Days</option>
            </optgroup>
            <optgroup label="Filter by Months">
              <option value="this_month" <?= (($timeframe_filter ?? '') === 'this_month') ? 'selected' : '' ?>>This Month</option>
              <option value="last_month" <?= (($timeframe_filter ?? '') === 'last_month') ? 'selected' : '' ?>>Last Month</option>
            </optgroup>
            <optgroup label="Filter by Years">
              <option value="2026" <?= (($timeframe_filter ?? '') === '2026' || ($timeframe_filter ?? '') === 'this_year') ? 'selected' : '' ?>>Year 2026</option>
              <option value="2025" <?= (($timeframe_filter ?? '') === '2025') ? 'selected' : '' ?>>Year 2025</option>
              <option value="2024" <?= (($timeframe_filter ?? '') === '2024') ? 'selected' : '' ?>>Year 2024</option>
            </optgroup>
          </select>
        </div>
      </div>
      <div class="col-12 col-md-2 d-flex align-items-center justify-content-md-end">
        <span class="text-muted small text-nowrap">Showing <strong><?= count($policies ?? []) ?></strong> records</span>
      </div>
    </form>

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