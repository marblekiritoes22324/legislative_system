<?php
// users/report.php — User Reports Submodule (Matching Admin side layout with View, PDF, DOCX & Print support)

// Pull policy records from DB for the report selection table
$u_report_policies = [];
if (!empty($conn)) {
  $rq = mysqli_query($conn, "SELECT id, title, category, status, created_at, ai_summary FROM policy_records WHERE (status IS NULL OR status != 'Archived') ORDER BY created_at DESC LIMIT 20");
  if ($rq) {
    while ($row = mysqli_fetch_assoc($rq)) {
      $u_report_policies[] = $row;
    }
  }
}
// Fallback demo records if DB is empty
if (empty($u_report_policies)) {
  $u_report_policies = [
    ['id' => 1, 'title' => 'Plastic Reduction Ordinance', 'category' => 'Environment', 'status' => 'Evaluated', 'created_at' => '2026-05-10', 'ai_summary' => 'This ordinance restricts single-use plastics across commercial establishments in Manila City.'],
    ['id' => 2, 'title' => 'Traffic Congestion Study', 'category' => 'Transportation', 'status' => 'Evaluated', 'created_at' => '2026-05-08', 'ai_summary' => 'Proposes smart traffic management and vehicle reduction strategies along major roads.'],
    ['id' => 3, 'title' => 'Public Health Program', 'category' => 'Health', 'status' => 'Evaluated', 'created_at' => '2026-05-05', 'ai_summary' => 'Expands barangay health center services and medical assistance programs.'],
  ];
}
?>
<!-- 5. REPORTS SUBMODULE (User Read-Only & Download Layout matching Admin Side) -->
<section id="reportsSection"
  class="content-section <?= ($active_section ?? 'userDashboardSection') !== 'reportsSection' ? 'd-none' : '' ?>">

  <!-- Top Header -->
  <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
      <h2 class="h3 fw-bold text-dark mb-1 d-flex align-items-center gap-2">
        <i class="bi bi-file-earmark-text-fill text-primary fs-4"></i> Published Reports &amp; Documents
      </h2>
      <p class="text-muted mb-0">Browse official legislative reports, review evaluation details, download in PDF or DOCX
        format, or print directly.</p>
    </div>
  </div>
  <!-- 1. Select Policy Record -->
  <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-white">
    <h3 class="fw-bold text-dark mb-3 d-flex align-items-center gap-2" style="font-size:1.05rem;">
      <i class="bi bi-journal-check text-primary"></i> 1. Select Policy Record
    </h3>
    <div class="table-responsive border rounded-4 overflow-hidden mb-3">
      <table class="table table-hover align-middle mb-0" style="font-size:0.88rem;">
        <thead style="background-color: #f8fafc; border-bottom: 2px solid #e2e8f0;">
          <tr>
            <th class="text-center py-3.5" style="width:50px;"></th>
            <th class="py-3.5 text-uppercase text-dark fw-bold"
              style="font-size: 0.88rem; letter-spacing: 0.03em; color: #000000 !important;">Policy Title</th>
            <th class="py-3.5 text-uppercase text-dark fw-bold"
              style="font-size: 0.88rem; letter-spacing: 0.03em; color: #000000 !important;">Category</th>
            <th class="py-3.5 text-center text-uppercase text-dark fw-bold"
              style="font-size: 0.88rem; letter-spacing: 0.03em; color: #000000 !important;">Status</th>
            <th class="py-3.5 text-uppercase text-dark fw-bold"
              style="font-size: 0.88rem; letter-spacing: 0.03em; color: #000000 !important;">Date Uploaded</th>
          </tr>
        </thead>
        <tbody id="userReportPolicyTableBody">
          <?php foreach ($u_report_policies as $i => $pol):
            $dateStr = !empty($pol['created_at']) ? date('M d, Y', strtotime($pol['created_at'])) : '—';
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
              $initialPreviewSummary = $summary;
            }

            $risk = 'Low Risk';
            $recText = 'Proceed with implementation and continue monitoring the effectiveness of the policy.';
            $rowBg = $isFirst ? 'background-color:#EFF6FF;' : '';
            ?>
            <tr class="user-report-policy-row <?= $isFirst ? 'active-report-row' : '' ?>"
              style="cursor:pointer; <?= $rowBg ?>" onclick="selectUserReportPolicy(this,
                '<?= addslashes(htmlspecialchars($pol['title'])) ?>',
                '<?= addslashes(htmlspecialchars($pol['category'] ?? '—')) ?>',
                '<?= addslashes(htmlspecialchars($pol['status'] ?? 'Published')) ?>',
                '<?= $dateStr ?>',
                '<?= addslashes(htmlspecialchars($summary)) ?>',
                '<?= $risk ?>',
                '<?= addslashes($recText) ?>')">
              <td class="text-center py-3">
                <input class="form-check-input" type="radio" name="userReportPolicyRadio" <?= $isFirst ? 'checked' : '' ?>
                  onclick="event.stopPropagation();">
              </td>
              <td class="py-3">
                <div class="d-flex align-items-center gap-2.5">
                  <div
                    class="rounded-3 p-1.5 bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center flex-shrink-0"
                    style="width: 32px; height: 32px;">
                    <i class="bi bi-file-earmark-text-fill fs-6"></i>
                  </div>
                  <strong class="text-dark"><?= htmlspecialchars($pol['title']) ?></strong>
                </div>
              </td>
              <td class="py-3">
                <span class="badge rounded-pill text-white fw-bold px-3 py-1.5 shadow-2xs"
                  style="background-color: #0d6efd !important; font-size: 0.78rem;">
                  <?= htmlspecialchars($pol['category'] ?? '—') ?>
                </span>
              </td>
              <td class="text-center py-3">
                <?php
                $st_val = htmlspecialchars($pol['status'] ?? 'Published');
                if ($st_val === 'Archived'): ?>
                  <span class="badge rounded-pill text-white fw-bold px-3 py-1.5 shadow-2xs"
                    style="background-color: #dc3545 !important; color: #ffffff !important; font-size: 0.78rem;">
                    <?= $st_val ?>
                  </span>
                <?php elseif ($st_val === 'Draft' || $st_val === 'Pending'): ?>
                  <span class="badge rounded-pill text-dark fw-bold px-3 py-1.5 shadow-2xs"
                    style="background-color: #ffc107 !important; color: #000000 !important; font-size: 0.78rem;">
                    <?= $st_val ?>
                  </span>
                <?php else: ?>
                  <span class="badge rounded-pill text-white fw-bold px-3 py-1.5 shadow-2xs"
                    style="background-color: #198754 !important; color: #ffffff !important; font-size: 0.78rem;">
                    <?= $st_val ?>
                  </span>
                <?php endif; ?>
              </td>
              <td class="text-secondary fw-medium py-3">
                <i class="bi bi-calendar3 me-1.5 text-muted opacity-75"></i><?= $dateStr ?>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <small class="text-muted fw-medium">Showing 1 to <?= count($u_report_policies) ?> of
      <?= count($u_report_policies) ?>
      records</small>
  </div>

  <!-- 2. Report Review & Actions -->
  <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-white">
    <h3 class="fw-bold text-dark mb-3 d-flex align-items-center gap-2" style="font-size:1.05rem;">
      <i class="bi bi-journal-text text-success"></i> 2. Report Document Review
    </h3>

    <div class="row g-4 align-items-stretch">
      <!-- Left Column: 2-Column Details Table -->
      <div class="col-12 col-lg-8">
        <div class="table-responsive border rounded-4 overflow-hidden shadow-2xs">
          <table class="table table-hover align-middle mb-0" style="font-size: 0.88rem;">
            <thead style="background-color: #f8fafc; border-bottom: 2px solid #e2e8f0;">
              <tr>
                <th style="width: 190px; font-size: 0.88rem; letter-spacing: 0.03em; color: #000000 !important;"
                  class="py-3.5 ps-3 text-uppercase text-dark fw-bold">Field</th>
                <th style="font-size: 0.88rem; letter-spacing: 0.03em; color: #000000 !important;"
                  class="py-3.5 text-uppercase text-dark fw-bold">Details</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td class="fw-bold text-secondary ps-3 py-3" style="background-color: #f8fafc;">
                  <i class="bi bi-journal-bookmark text-primary me-2"></i>Policy Title
                </td>
                <td class="fw-bold text-dark py-3" id="userPrevPolicyTitle">
                  <?= htmlspecialchars($u_report_policies[0]['title'] ?? '—') ?>
                </td>
              </tr>
              <tr>
                <td class="fw-bold text-secondary ps-3 py-3" style="background-color: #f8fafc;">
                  <i class="bi bi-tag-fill text-info me-2"></i>Category
                </td>
                <td class="text-dark py-3" id="userPrevCategory">
                  <?= htmlspecialchars($u_report_policies[0]['category'] ?? '—') ?>
                </td>
              </tr>
              <tr>
                <td class="fw-bold text-secondary ps-3 py-3" style="background-color: #f8fafc;">
                  <i class="bi bi-cpu-fill me-2" style="color: #9333ea;"></i>AI Summary
                </td>
                <td class="text-dark lh-base py-3" id="userPrevAISummary">
                  <?= htmlspecialchars($initialPreviewSummary ?? '') ?>
                </td>
              </tr>
              <tr>
                <td class="fw-bold text-secondary ps-3 py-3" style="background-color: #f8fafc;">
                  <i class="bi bi-shield-check text-success me-2"></i>Evaluation Result
                </td>
                <td class="py-3" id="userPrevEvalResult">
                  <span
                    class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-20 px-3 py-1.5 rounded-3 fw-semibold">
                    Low Risk
                  </span>
                </td>
              </tr>
              <tr>
                <td class="fw-bold text-secondary ps-3 py-3" style="background-color: #f8fafc;">
                  <i class="bi bi-lightbulb-fill text-warning me-2"></i>Recommendation
                </td>
                <td class="text-dark lh-base py-3" id="userPrevRecommendation">
                  Proceed with implementation and continue monitoring the effectiveness of the policy.
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Right Column: Report Actions Box -->
      <div class="col-12 col-lg-4">
        <div class="p-3.5 border rounded-4 bg-white h-100 d-flex flex-column shadow-2xs">
          <div class="d-flex flex-column gap-3 my-auto">
            <!-- Print Report Button (with Save as DOCX option inside) -->
            <button type="button"
              class="btn btn-outline-primary p-3 rounded-3 d-flex align-items-center gap-3 text-start bg-white shadow-sm border-opacity-30 w-100"
              style="border-color: #0B2E59 !important;" onclick="printSelectedUserReport()">
              <div class="rounded-3 p-2 d-flex align-items-center justify-content-center"
                style="width: 44px; height: 44px; background: rgba(11, 46, 89, 0.08); color: #0B2E59;">
                <i class="bi bi-printer-fill fs-4"></i>
              </div>
              <div>
                <span class="fw-bold fs-6 d-block" style="color: #0B2E59;">Print Report</span>
                <small class="text-muted" style="font-size:0.75rem;">Print or Save as DOCX / PDF</small>
              </div>
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>

  <style>
    /* Clean Executive Recent Reports Styling */
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

    .btn-report-download {
      background: #FFFFFF !important;
      color: #0B2E59 !important;
      border: 1.5px solid #CBD5E1 !important;
      padding: 6px 14px !important;
      border-radius: 8px !important;
      font-size: 0.83rem !important;
      font-weight: 600 !important;
      display: inline-flex !important;
      align-items: center !important;
      gap: 6px !important;
      transition: all 0.18s ease !important;
      box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04) !important;
      text-decoration: none !important;
    }

    .btn-report-download:hover {
      background: #0B2E59 !important;
      color: #FFFFFF !important;
      border-color: #0B2E59 !important;
      transform: translateY(-1px);
      box-shadow: 0 4px 10px rgba(11, 46, 89, 0.15) !important;
    }

    .btn-report-download:hover i {
      color: #F59E0B !important;
    }
  </style>

  <!-- 3. Recent Generated Reports Table -->
  <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-white">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
      <div>
        <h3 class="fw-bold text-dark mb-1 d-flex align-items-center gap-2" style="font-size:1.05rem;">
          <i class="bi bi-clock-history text-primary"></i> 3. Generated Reports &amp; Comparative Analyses
        </h3>
        <p class="text-muted mb-0 small">Access, browse, and download legislative policy evaluations, cross-city
          benchmarks, and version evolution summaries.</p>
      </div>
      <div class="d-flex align-items-center gap-2">
        <div class="btn-group btn-group-sm p-1 bg-light rounded-pill border shadow-2xs" role="group">
          <button type="button" class="btn btn-sm rounded-pill px-3 py-1 fw-bold text-white shadow-sm"
            id="filterUserReportAll" style="background:#0B2E59;"
            onclick="filterUserReportsTable('All', this)">All</button>
          <button type="button" class="btn btn-sm rounded-pill px-3 py-1 text-secondary" id="filterUserReportEval"
            style="background:transparent;" onclick="filterUserReportsTable('Evaluation', this)">Evaluations</button>
          <button type="button" class="btn btn-sm rounded-pill px-3 py-1 text-secondary" id="filterUserReportBench"
            style="background:transparent;" onclick="filterUserReportsTable('Benchmark', this)">Benchmarks</button>
          <button type="button" class="btn btn-sm rounded-pill px-3 py-1 text-secondary" id="filterUserReportVersion"
            style="background:transparent;" onclick="filterUserReportsTable('Version', this)">Versions</button>
        </div>
      </div>
    </div>
    <div class="table-responsive border rounded-4 overflow-hidden mb-2">
      <table class="table table-hover align-middle mb-0" style="font-size:0.88rem;">
        <thead style="background-color: #f8fafc; border-bottom: 2px solid #e2e8f0;">
          <tr>
            <th class="py-3.5 text-uppercase text-dark fw-bold" style="font-size: 0.88rem; letter-spacing: 0.03em;">
              Report Name</th>
            <th class="py-3.5 text-uppercase text-dark fw-bold" style="font-size: 0.88rem; letter-spacing: 0.03em;">
              Policy / Subject</th>
            <th class="py-3.5 text-uppercase text-dark fw-bold" style="font-size: 0.88rem; letter-spacing: 0.03em;">Type
            </th>
            <th class="py-3.5 text-uppercase text-dark fw-bold" style="font-size: 0.88rem; letter-spacing: 0.03em;">Date
              Generated</th>
            <th class="py-3.5 text-end text-uppercase text-dark fw-bold"
              style="font-size: 0.88rem; letter-spacing: 0.03em;">Action</th>
          </tr>
        </thead>
        <tbody id="recentUserReportsBody">
          <!-- Dynamically populated when reports are generated -->
        </tbody>
      </table>
    </div>
    <small class="text-muted" id="recentUserReportsCount">Showing 0 records</small>
  </div>

  <!-- 4. Public Download Package -->
  <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
    <h3 class="fw-bold text-dark mb-3" style="font-size:1rem;">4. Public Download Package</h3>
    <div class="row g-4">
      <div class="col-md-6">
        <div class="card border p-4 rounded-4 h-100 bg-light">
          <h4 class="h6 fw-bold text-dark mb-2"><i class="bi bi-file-earmark-pdf text-danger me-2"></i>Annual Policy
            Impact Assessment Report 2025</h4>
          <p class="small text-muted mb-3">Comprehensive breakdown of all enacted city ordinances and their measured
            community impact.</p>
          <div class="d-flex align-items-center gap-2">
            <button class="btn btn-sm btn-danger rounded-3 fw-semibold px-3"
              onclick="exportUserReportFile('Annual_Policy_Impact_Report_2025.pdf')">
              <i class="bi bi-download me-1"></i> Download PDF
            </button>
            <button class="btn btn-sm btn-outline-secondary rounded-3 fw-semibold px-3" onclick="window.print()">
              <i class="bi bi-printer me-1"></i> Print
            </button>
          </div>
        </div>
      </div>
      <div class="col-md-6">
        <div class="card border p-4 rounded-4 h-100 bg-light">
          <h4 class="h6 fw-bold text-dark mb-2"><i class="bi bi-file-earmark-word text-primary me-2"></i>Legislative
            Sector Analytics Summary</h4>
          <p class="small text-muted mb-3">Statistical summary of ordinance enactments across Health, Infrastructure,
            and Environmental sectors.</p>
          <div class="d-flex align-items-center gap-2">
            <button class="btn btn-sm btn-primary rounded-3 fw-semibold px-3"
              onclick="exportUserReportFile('Legislative_Sector_Analytics_Summary.docx')">
              <i class="bi bi-file-earmark-word me-1"></i> Download DOCX
            </button>
            <button class="btn btn-sm btn-outline-secondary rounded-3 fw-semibold px-3" onclick="window.print()">
              <i class="bi bi-printer me-1"></i> Print
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>

</section>

<!-- REPORT DETAILS MODAL -->
<div class="modal fade" id="userReportDetailsModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content rounded-4 border-0 shadow-lg">
      <div class="modal-header text-white rounded-top-4" style="background:#0B2E59;">
        <h5 class="modal-header-title mb-0 fw-bold fs-6">
          <i class="bi bi-file-earmark-text me-2"></i>Legislative Report Details
        </h5>
        <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="modal"
          aria-label="Close"></button>
      </div>
      <div class="modal-body p-4">
        <div class="table-responsive border rounded-3 overflow-hidden mb-4">
          <table class="table table-bordered align-middle mb-0" style="font-size:0.9rem;">
            <tbody>
              <tr>
                <td class="bg-light fw-bold" style="width:30%;">Policy Title</td>
                <td id="mReportTitle" class="fw-bold text-dark"></td>
              </tr>
              <tr>
                <td class="bg-light fw-bold">Category</td>
                <td id="mReportCategory"></td>
              </tr>
              <tr>
                <td class="bg-light fw-bold">Status</td>
                <td id="mReportStatus"></td>
              </tr>
              <tr>
                <td class="bg-light fw-bold">Publication Date</td>
                <td id="mReportDate"></td>
              </tr>
              <tr>
                <td class="bg-light fw-bold">Executive AI Summary</td>
                <td id="mReportSummary" class="lh-base"></td>
              </tr>
              <tr>
                <td class="bg-light fw-bold">Impact Risk Assessment</td>
                <td id="mReportRisk"></td>
              </tr>
              <tr>
                <td class="bg-light fw-bold">Recommendation</td>
                <td id="mReportRec" class="lh-base"></td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
      <div class="modal-footer bg-light border-0 px-4 py-3">
        <button type="button" class="btn btn-outline-secondary rounded-3 px-4 fw-semibold"
          data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn btn-danger rounded-3 px-3 fw-semibold" onclick="exportUserReport('PDF')"><i
            class="bi bi-file-earmark-pdf me-1"></i> PDF</button>
        <button type="button" class="btn btn-primary rounded-3 px-3 fw-semibold" onclick="exportUserReport('DOCX')"><i
            class="bi bi-file-earmark-word me-1"></i> DOCX</button>
        <button type="button" class="btn btn-secondary rounded-3 px-3 fw-semibold"
          onclick="printSelectedUserReport()"><i class="bi bi-printer me-1"></i> Print</button>
      </div>
    </div>
  </div>
</div>

<script>
  // Selected Report State
  var _selectedUserReport = {
    title: '<?= addslashes(htmlspecialchars($u_report_policies[0]['title'] ?? '')) ?>',
    category: '<?= addslashes(htmlspecialchars($u_report_policies[0]['category'] ?? '—')) ?>',
    status: '<?= addslashes(htmlspecialchars($u_report_policies[0]['status'] ?? 'Published')) ?>',
    date: '<?= isset($u_report_policies[0]['created_at']) ? date('M d, Y', strtotime($u_report_policies[0]['created_at'])) : '—' ?>',
    summary: '<?= addslashes(htmlspecialchars($initialPreviewSummary ?? 'This policy contains official legislative data and impact evaluation findings.')) ?>',
    risk: 'Low Risk',
    recommendation: 'Proceed with implementation and continue monitoring the effectiveness of the policy.'
  };

  (function () {
    renderUserRecentGeneratedReportsTable();
  })();

  function selectUserReportPolicy(trEl, title, category, status, date, summary, risk, recommendation) {
    _selectedUserReport = { title: title, category: category, status: status, date: date, summary: summary, risk: risk, recommendation: recommendation };

    // Highlight row
    document.querySelectorAll('#userReportPolicyTableBody .user-report-policy-row').forEach(function (row) {
      row.style.backgroundColor = '';
      var r = row.querySelector('input[type="radio"]');
      if (r) r.checked = false;
    });
    if (trEl) {
      trEl.style.backgroundColor = '#EFF6FF';
      var radio = trEl.querySelector('input[type="radio"]');
      if (radio) radio.checked = true;
    }

    // Update Section 2 Review Table
    var tEl = document.getElementById('userPrevPolicyTitle');
    var cEl = document.getElementById('userPrevCategory');
    var sEl = document.getElementById('userPrevAISummary');
    var eEl = document.getElementById('userPrevEvalResult');
    var rEl = document.getElementById('userPrevRecommendation');

    if (tEl) tEl.textContent = title;
    if (cEl) cEl.textContent = category;
    if (sEl) sEl.textContent = summary;
    if (rEl) rEl.textContent = recommendation;

    if (eEl) {
      var rColor = 'success';
      if (risk.indexOf('High') !== -1) rColor = 'danger';
      else if (risk.indexOf('Moderate') !== -1) rColor = 'warning';
      eEl.innerHTML = '<span class="badge bg-' + rColor + ' bg-opacity-10 text-' + rColor +
        ' border border-' + rColor + ' border-opacity-20 px-3 py-1.5 rounded-3 fw-semibold">' + risk + '</span>';
    }
  }

  function viewSelectedReportDetails() {
    document.getElementById('mReportTitle').textContent = _selectedUserReport.title;
    document.getElementById('mReportCategory').textContent = _selectedUserReport.category;
    document.getElementById('mReportStatus').textContent = _selectedUserReport.status;
    document.getElementById('mReportDate').textContent = _selectedUserReport.date;
    document.getElementById('mReportSummary').textContent = _selectedUserReport.summary;
    document.getElementById('mReportRisk').textContent = _selectedUserReport.risk;
    document.getElementById('mReportRec').textContent = _selectedUserReport.recommendation;

    var modalEl = document.getElementById('userReportDetailsModal');
    if (modalEl && typeof bootstrap !== 'undefined') {
      var modal = new bootstrap.Modal(modalEl);
      modal.show();
    }
  }

  function getUserLogoUrl() {
    var path = window.location.pathname;
    var basePath = path.substring(0, path.lastIndexOf('/'));
    if (basePath.endsWith('/users') || basePath.endsWith('/admin')) {
      basePath = basePath.substring(0, basePath.lastIndexOf('/'));
    }
    return window.location.origin + basePath + '/assets/images/manilacityhall.svg';
  }

  function buildUserReportTemplate(repObj) {
    var rep = repObj || _selectedUserReport;
    var logoUrl = getUserLogoUrl();
    var riskColor = '#15803d';
    var riskBg = '#dcfce7';
    var riskBorder = '#bbf7d0';
    if (rep.risk.indexOf('High') !== -1) {
      riskColor = '#b91c1c'; riskBg = '#fee2e2'; riskBorder = '#fca5a5';
    } else if (rep.risk.indexOf('Moderate') !== -1) {
      riskColor = '#b45309'; riskBg = '#fef3c7'; riskBorder = '#fde68a';
    }

    var nowStr = new Date().toLocaleString('en-US', { dateStyle: 'full', timeStyle: 'short' });

    return '' +
      '<div style="max-width:720px; margin:0 auto; background:#ffffff; padding:10px; font-family:\'Segoe UI\', Arial, sans-serif; color:#0f172a; text-align:left;">' +
      '  <div style="text-align:center; margin-bottom:15px;">' +
      '    <img src="' + logoUrl + '" width="65" height="65" style="width:65px; height:65px; object-fit:contain; margin-bottom:8px;" alt="Manila Seal">' +
      '    <h1 style="color:#0B2E59; font-weight:800; font-size:1.6rem; letter-spacing:-0.5px; margin:0 0 4px 0;">LUNGSOD NG MAYNILA</h1>' +
      '    <div style="color:#475569; font-weight:600; font-size:0.88rem; margin-bottom:14px;">City of Manila — Legislative Services</div>' +
      '    <div style="margin-bottom:6px;">' +
      '      <span style="background:#0B2E59; color:#ffffff; padding:6px 16px; font-weight:700; text-transform:uppercase; letter-spacing:1px; font-size:0.82rem; border-radius:4px; display:inline-block;">Official Legislative Evaluation Report</span>' +
      '    </div>' +
      '    <div style="color:#64748b; font-size:0.78rem; margin-top:6px;">Date Published: ' + nowStr + '</div>' +
      '  </div>' +

      '  <div style="border-bottom:2px solid #0B2E59; margin-bottom:20px;"></div>' +

      '  <table width="100%" style="width:100%; border-collapse:collapse; margin-bottom:25px; font-size:0.88rem;">' +
      '    <tbody>' +
      '      <tr>' +
      '        <th width="170" style="padding:12px 16px; border:1px solid #cbd5e1; background-color:#f8fafc; font-weight:700; width:170px; text-align:left; color:#334155;">Policy Title</th>' +
      '        <td style="padding:12px 16px; border:1px solid #cbd5e1; font-weight:700; color:#0f172a; font-size:0.92rem;">' + escapeHtml(rep.title) + '</td>' +
      '      </tr>' +
      '      <tr>' +
      '        <th width="170" style="padding:12px 16px; border:1px solid #cbd5e1; background-color:#f8fafc; font-weight:700; width:170px; text-align:left; color:#334155;">Category</th>' +
      '        <td style="padding:12px 16px; border:1px solid #cbd5e1; color:#1e293b;">' + escapeHtml(rep.category) + '</td>' +
      '      </tr>' +
      '      <tr>' +
      '        <th width="170" style="padding:12px 16px; border:1px solid #cbd5e1; background-color:#f8fafc; font-weight:700; width:170px; text-align:left; color:#334155;">Executive AI Summary</th>' +
      '        <td style="padding:12px 16px; border:1px solid #cbd5e1; color:#1e293b; line-height:1.65;">' + escapeHtml(rep.summary) + '</td>' +
      '      </tr>' +
      '      <tr>' +
      '        <th width="170" style="padding:12px 16px; border:1px solid #cbd5e1; background-color:#f8fafc; font-weight:700; width:170px; text-align:left; color:#334155;">Evaluation Result</th>' +
      '        <td style="padding:12px 16px; border:1px solid #cbd5e1;">' +
      '          <span style="background:' + riskBg + '; color:' + riskColor + '; border:1px solid ' + riskBorder + '; padding:4px 12px; border-radius:4px; font-weight:700; font-size:0.82rem; display:inline-block;">' + escapeHtml(rep.risk) + '</span>' +
      '        </td>' +
      '      </tr>' +
      '      <tr>' +
      '        <th width="170" style="padding:12px 16px; border:1px solid #cbd5e1; background-color:#f8fafc; font-weight:700; width:170px; text-align:left; color:#334155;">Recommendation</th>' +
      '        <td style="padding:12px 16px; border:1px solid #cbd5e1; color:#1e293b; line-height:1.65;">' + escapeHtml(rep.recommendation) + '</td>' +
      '      </tr>' +
      '    </tbody>' +
      '  </table>' +

      '  <table width="100%" style="width:100%; border-collapse:collapse; margin-top:35px; margin-bottom:25px; font-size:0.85rem; border:none;">' +
      '    <tr>' +
      '      <td width="50%" style="width:50%; border:none; padding:0; vertical-align:top; text-align:left;">' +
      '        <div style="color:#64748b; font-size:0.78rem;">Prepared & Evaluated By:</div>' +
      '        <div style="font-weight:700; color:#0f172a; margin-top:22px; font-size:0.9rem;">Legislative Research Office</div>' +
      '        <div style="color:#64748b; font-size:0.78rem;">City Council of Manila</div>' +
      '      </td>' +
      '      <td width="50%" style="width:50%; border:none; padding:0; text-align:right; vertical-align:top;">' +
      '        <div style="color:#64748b; font-size:0.78rem;">Approved By:</div>' +
      '        <div style="font-weight:700; color:#0f172a; margin-top:22px; font-size:0.9rem;">Administrator</div>' +
      '        <div style="color:#64748b; font-size:0.78rem;">Legislative Information System</div>' +
      '      </td>' +
      '    </tr>' +
      '  </table>' +

      '  <div style="font-size:0.78rem; color:#94a3b8; border-top:1px solid #e2e8f0; padding-top:14px; text-align:center;">' +
      '    Issued by the City Council Legislative Administration Office — Manila City Hall Legislative Information System' +
      '  </div>' +
      '</div>';
  }

  function printSelectedUserReport(customReport) {
    var rep = customReport || getActiveUserReportData();
    if (!rep || !rep.title) {
      if (typeof _selectedUserReport !== 'undefined' && _selectedUserReport.title) {
        rep = _selectedUserReport;
      }
    }

    try {
      trackUserGeneratedReport((rep && rep.title) ? rep.title : 'Policy Report', 'PDF', rep);
    } catch (e) { }

    var htmlContent = buildUserReportTemplate(rep);
    var cleanTitle = (rep.title || 'Policy').replace(/[^a-zA-Z0-9 ]/g, '').trim().replace(/\s+/g, '_');
    var docxFileName = cleanTitle + '_Report.docx';

    var doc = '<!DOCTYPE html><html><head><title>Legislative Evaluation Report - ' + escapeHtml(rep.title || 'Report') + '</title>' +
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
      '    <button id="btnUserSaveDocxInWindow" class="btn btn-outline-primary btn-sm px-3 fw-bold bg-white shadow-sm">' +
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
          var saveDocxBtn = printWin.document.getElementById('btnUserSaveDocxInWindow');
          if (saveDocxBtn) {
            saveDocxBtn.onclick = function () {
              generateUserReportWordDoc(docxFileName, rep);
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
      var printIframe = document.getElementById('userReportPrintIframe');
      if (!printIframe) {
        printIframe = document.createElement('iframe');
        printIframe.id = 'userReportPrintIframe';
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

  // --- Dynamic Recent Reports Storage & Table Management for Users ---
  var USER_RECENT_REPORTS_KEY = 'legislative_user_recent_reports_v4';
  var currentUserReportFilter = 'All';

  function filterUserReportsTable(filterType, btnEl) {
    currentUserReportFilter = filterType;
    var btns = document.querySelectorAll('.btn-group button[id^="filterUserReport"]');
    btns.forEach(function (b) {
      b.style.background = 'transparent';
      b.className = 'btn btn-sm rounded-pill px-3 py-1 text-secondary';
    });
    if (btnEl) {
      btnEl.style.background = '#0B2E59';
      btnEl.className = 'btn btn-sm rounded-pill px-3 py-1 fw-bold text-white shadow-sm';
    }
    renderUserRecentGeneratedReportsTable();
  }
  window.filterUserReportsTable = filterUserReportsTable;

  function loadUserRecentGeneratedReports() {
    try {
      var raw = localStorage.getItem(USER_RECENT_REPORTS_KEY);
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
      localStorage.setItem(USER_RECENT_REPORTS_KEY, JSON.stringify(defaultSeed));
    } catch (e) { }
    return defaultSeed;
  }

  function trackUserGeneratedReport(policyTitle, format, reportObj) {
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
      report_data: JSON.parse(JSON.stringify(reportObj || _selectedUserReport))
    };

    var list = loadUserRecentGeneratedReports();
    if (list.length > 0 && list[0].report_name === fileName && list[0].date_generated === timeStr) {
      return;
    }

    list.unshift(reportItem);
    if (list.length > 30) list = list.slice(0, 30);

    try {
      localStorage.setItem(USER_RECENT_REPORTS_KEY, JSON.stringify(list));
    } catch (e) { }

    renderUserRecentGeneratedReportsTable();
  }

  function renderUserRecentGeneratedReportsTable() {
    var tbody = document.getElementById('recentUserReportsBody');
    var countEl = document.getElementById('recentUserReportsCount');
    if (!tbody) return;

    var allList = loadUserRecentGeneratedReports();
    var list = allList;

    if (currentUserReportFilter === 'Evaluation') {
      list = allList.filter(function (r) { return !r.report_type || r.report_type.indexOf('Evaluation') !== -1; });
    } else if (currentUserReportFilter === 'Benchmark') {
      list = allList.filter(function (r) { return r.report_type && (r.report_type.indexOf('Benchmark') !== -1 || r.report_type.indexOf('Comparison') !== -1); });
    } else if (currentUserReportFilter === 'Version') {
      list = allList.filter(function (r) { return r.report_type && (r.report_type.indexOf('Version') !== -1); });
    }

    if (!list || list.length === 0) {
      tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted py-4"><i class="bi bi-info-circle me-1"></i> No matching reports found for this filter.</td></tr>';
      if (countEl) countEl.textContent = 'Showing 0 records';
      return;
    }

    var html = '';
    for (var i = 0; i < list.length; i++) {
      var r = list[i];
      var isDocx = (r.format === 'DOCX') || (r.report_name && r.report_name.toLowerCase().endsWith('.docx'));
      var fileIcon = isDocx ? 'bi-file-earmark-word-fill text-primary' : 'bi-file-earmark-pdf-fill text-danger';

      var typeBadge = '<span class="badge-report-type"><i class="bi bi-file-earmark-text text-primary"></i><span>' + escapeHtml(r.report_type || 'Evaluation Report') + '</span></span>';
      if (r.report_type && r.report_type.indexOf('Benchmark') !== -1) {
        typeBadge = '<span class="badge-report-type" style="background:#f0fdfa; color:#0f766e; border-color:#ccfbf1;"><i class="bi bi-intersect text-teal"></i><span>' + escapeHtml(r.report_type) + '</span></span>';
      } else if (r.report_type && r.report_type.indexOf('Version') !== -1) {
        typeBadge = '<span class="badge-report-type" style="background:#fefce8; color:#a16207; border-color:#fef08a;"><i class="bi bi-clock-history text-warning"></i><span>' + escapeHtml(r.report_type) + '</span></span>';
      }

      html += '<tr class="align-middle">' +
        '<td class="py-3 px-3">' +
        '<div class="d-flex align-items-center gap-2">' +
        '<i class="bi ' + fileIcon + ' fs-5"></i>' +
        '<span class="fw-semibold text-dark font-monospace" style="font-size: 0.86rem;">' + escapeHtml(r.report_name) + '</span>' +
        '</div>' +
        '</td>' +
        '<td class="py-3 px-3 text-dark fw-medium" style="font-size: 0.88rem;">' + escapeHtml(r.policy_title) + '</td>' +
        '<td class="py-3 px-3">' +
        typeBadge +
        '</td>' +
        '<td class="py-3 px-3 text-muted small">' +
        '<i class="bi bi-calendar3 me-1.5 text-muted"></i>' + escapeHtml(r.date_generated) +
        '</td>' +
        '<td class="py-3 px-3 text-end">' +
        '<button type="button" class="btn btn-sm btn-report-download" onclick="downloadUserRecentGeneratedReport(' + i + ')">' +
        '<i class="bi bi-download text-primary"></i>' +
        '<span>Download / View</span>' +
        '</button>' +
        '</td>' +
        '</tr>';
    }

    tbody.innerHTML = html;
    if (countEl) countEl.textContent = 'Showing 1 to ' + list.length + ' of ' + allList.length + ' records';
  }

  function getActiveUserReportData() {
    var titleEl = document.getElementById('prevUserPolicyTitle');
    var catEl = document.getElementById('prevUserCategory');
    var sumEl = document.getElementById('prevUserAISummary');
    var evalEl = document.getElementById('prevUserEvalResult');
    var recEl = document.getElementById('prevUserRecommendation');

    var title = titleEl ? titleEl.textContent.trim() : '';
    var cat = catEl ? catEl.textContent.trim() : '';
    var sum = sumEl ? sumEl.textContent.trim() : '';
    var risk = evalEl ? evalEl.textContent.trim() : 'Low Risk';
    var rec = recEl ? recEl.textContent.trim() : '';

    if (!title && typeof _selectedUserReport !== 'undefined' && _selectedUserReport.title) {
      return _selectedUserReport;
    }

    return {
      title: title || (_selectedUserReport ? _selectedUserReport.title : 'Policy Report'),
      category: cat || (_selectedUserReport ? _selectedUserReport.category : 'General'),
      status: _selectedUserReport ? _selectedUserReport.status : 'Published',
      date: _selectedUserReport ? _selectedUserReport.date : new Date().toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }),
      summary: sum || (_selectedUserReport ? _selectedUserReport.summary : 'Policy evaluation and legislative review summary.'),
      risk: risk || (_selectedUserReport ? _selectedUserReport.risk : 'Low Risk'),
      recommendation: rec || (_selectedUserReport ? _selectedUserReport.recommendation : 'Proceed with implementation and continue monitoring the effectiveness of the policy.')
    };
  }

  function downloadUserRecentGeneratedReport(index) {
    var list = loadUserRecentGeneratedReports();
    var item = list[index];
    if (!item) return;

    var rep = item.report_data || {
      title: item.policy_title,
      category: 'General',
      status: 'Published',
      date: item.date_generated,
      summary: 'This policy contains official legislative data and impact evaluation findings for ' + item.policy_title + '.',
      risk: 'Low Risk',
      recommendation: 'Proceed with implementation and continue monitoring the effectiveness of the policy.'
    };

    if (item.format === 'DOCX' || (item.report_name && item.report_name.toLowerCase().endsWith('.docx'))) {
      generateUserReportWordDoc(item.report_name, rep);
    } else {
      printSelectedUserReport(rep);
    }
  }

  function generateUserReportWordDoc(fileName, customReport) {
    var rep = customReport || getActiveUserReportData();
    var htmlContent = buildUserReportTemplate(rep);

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

  function exportUserReport(format) {
    var rep = getActiveUserReportData();
    var ext = (format === 'DOCX') ? 'docx' : 'pdf';
    var cleanName = (rep.title || 'Policy').replace(/[^a-zA-Z0-9 ]/g, '').trim().replace(/\s+/g, '_');
    var fileName = cleanName + '_Report.' + ext;

    try {
      trackUserGeneratedReport(rep.title, format, rep);
    } catch (e) { }

    if (format === 'DOCX') {
      generateUserReportWordDoc(fileName, rep);
    } else {
      var htmlContent = buildUserReportTemplate(rep);
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
          filename: fileName,
          image: { type: 'jpeg', quality: 0.98 },
          html2canvas: { scale: 2, useCORS: true },
          jsPDF: { unit: 'in', format: 'letter', orientation: 'portrait' }
        };

        html2pdf().set(opt).from(container).save().then(function () {
          if (container && container.parentNode) {
            container.parentNode.removeChild(container);
          }
        }).catch(function (err) {
          if (container && container.parentNode) {
            container.parentNode.removeChild(container);
          }
          printSelectedUserReport(rep);
        });
      } else {
        printSelectedUserReport(rep);
      }
    }
  }

  function exportUserReportFile(fileName) {
    var fmt = fileName.endsWith('.docx') ? 'DOCX' : 'PDF';
    var rep = getActiveUserReportData();
    trackUserGeneratedReport(rep.title, fmt, rep);

    if (fileName.endsWith('.docx')) {
      generateUserReportWordDoc(fileName, rep);
    } else {
      printSelectedUserReport(rep);
    }
  }

  function escapeHtml(str) {
    return (str || '').replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;");
  }

  window.exportUserReport = exportUserReport;
  window.exportUserReportFile = exportUserReportFile;
  window.printSelectedUserReport = printSelectedUserReport;
  window.generateUserReportWordDoc = generateUserReportWordDoc;
  window.downloadUserRecentGeneratedReport = downloadUserRecentGeneratedReport;
  window.renderUserRecentGeneratedReportsTable = renderUserRecentGeneratedReportsTable;
  document.addEventListener('DOMContentLoaded', renderUserRecentGeneratedReportsTable);
  window.addEventListener('load', renderUserRecentGeneratedReportsTable);
</script>