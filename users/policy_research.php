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

    <!-- Search Bar & Category & Timeframe Filter -->
    <form method="GET" action="user_dashboard.php" class="row g-3 mb-4 align-items-center" id="policyLibraryForm">
      <input type="hidden" name="section" value="policyLibrarySection">
      <div class="col-12 col-md-5">
        <div class="input-group shadow-2xs">
          <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
          <input type="text" name="pl_search" id="userPolicySearch" class="form-control border-start-0 py-2"
            placeholder="Search ordinances by title, author or keyword..."
            value="<?= htmlspecialchars($pl_search ?? '') ?>"
            oninput="if(this.value==='') this.form.submit();">
        </div>
      </div>
      <div class="col-12 col-md-3">
        <select name="pl_category" id="userPolicyCategory" class="form-select py-2 shadow-2xs" onchange="this.form.submit()">
          <option value="">All Categories</option>
          <option value="Health and Sanitation" <?= (($pl_category ?? '') === 'Health and Sanitation' || ($pl_category ?? '') === 'Health') ? 'selected' : '' ?>>Health and Sanitation</option>
          <option value="Civil Registry and Public Services" <?= (($pl_category ?? '') === 'Civil Registry and Public Services') ? 'selected' : '' ?>>Civil Registry and Public Services</option>
          <option value="Education and Employment" <?= (($pl_category ?? '') === 'Education and Employment' || ($pl_category ?? '') === 'Education') ? 'selected' : '' ?>>Education and Employment</option>
          <option value="Social Welfare and Community Affairs" <?= (($pl_category ?? '') === 'Social Welfare and Community Affairs') ? 'selected' : '' ?>>Social Welfare and Community Affairs</option>
          <option value="Infrastructure, Traffic and Environment" <?= (($pl_category ?? '') === 'Infrastructure, Traffic and Environment' || ($pl_category ?? '') === 'Infrastructure' || ($pl_category ?? '') === 'Environment') ? 'selected' : '' ?>>Infrastructure, Traffic and Environment</option>
          <option value="Other" <?= (($pl_category ?? '') === 'Other') ? 'selected' : '' ?>>Other</option>
        </select>
      </div>
      <div class="col-12 col-md-2">
        <div class="input-group shadow-2xs">
          <span class="input-group-text bg-white text-muted border-end-0" style="color: #0B2E59 !important;"><i class="bi bi-funnel"></i></span>
          <select name="pl_timeframe" class="form-select border-start-0 py-2" onchange="this.form.submit()" title="Filter by Days, Months, or Years">
            <option value="">All Dates</option>
            <optgroup label="Filter by Days">
              <option value="today" <?= (($pl_timeframe ?? '') === 'today') ? 'selected' : '' ?>>Today</option>
              <option value="last_7_days" <?= (($pl_timeframe ?? '') === 'last_7_days') ? 'selected' : '' ?>>Last 7 Days</option>
              <option value="last_30_days" <?= (($pl_timeframe ?? '') === 'last_30_days') ? 'selected' : '' ?>>Last 30 Days</option>
            </optgroup>
            <optgroup label="Filter by Months">
              <option value="this_month" <?= (($pl_timeframe ?? '') === 'this_month') ? 'selected' : '' ?>>This Month</option>
              <option value="last_month" <?= (($pl_timeframe ?? '') === 'last_month') ? 'selected' : '' ?>>Last Month</option>
            </optgroup>
            <optgroup label="Filter by Years">
              <option value="2026" <?= (($pl_timeframe ?? '') === '2026' || ($pl_timeframe ?? '') === 'this_year') ? 'selected' : '' ?>>Year 2026</option>
              <option value="2025" <?= (($pl_timeframe ?? '') === '2025') ? 'selected' : '' ?>>Year 2025</option>
              <option value="2024" <?= (($pl_timeframe ?? '') === '2024') ? 'selected' : '' ?>>Year 2024</option>
            </optgroup>
          </select>
        </div>
      </div>
      <div class="col-12 col-md-2 d-flex align-items-center justify-content-md-end gap-2">
        <?php if (($pl_search ?? '') !== '' || ($pl_category ?? '') !== '' || ($pl_timeframe ?? '') !== ''): ?>
          <a href="user_dashboard.php?section=policyLibrarySection"
            class="btn btn-outline-secondary py-1.5 px-2.5 rounded-3 text-nowrap small">Clear</a>
        <?php endif; ?>
        <span class="text-muted small text-nowrap">Showing <strong><?= count($pl_policies ?? []) ?></strong> records</span>
      </div>
    </form>

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
