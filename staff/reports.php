<?php
// staff/reports.php — Staff Reports Submodule
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
  class="content-section <?= ($active_section ?? 'staffDashboardSection') !== 'reportGenerationSection' ? 'd-none' : '' ?>">

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
    .clickable-report-row:hover .policy-title-link {
      color: #0d6efd !important;
      text-decoration: underline !important;
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
    .btn-report-download {
      background: #FFFFFF !important;
      color: #0B2E59 !important;
      border: 1.5px solid #CBD5E1 !important;
      padding: 5px 12px !important;
      border-radius: 8px !important;
      font-size: 0.82rem !important;
      font-weight: 600 !important;
      display: inline-flex !important;
      align-items: center !important;
      gap: 6px !important;
      white-space: nowrap !important;
      flex-shrink: 0 !important;
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
        <span class="badge bg-light text-secondary border rounded-pill px-3 py-2 fw-semibold" style="font-size: 0.82rem;">
          <i class="bi bi-collection-fill text-primary me-1.5"></i><?= $total_report_count ?> Policies Available
        </span>
      </div>
    </div>
    <div class="table-responsive border rounded-4 overflow-hidden mb-3">
      <table class="table table-hover align-middle mb-0" style="font-size:0.88rem;">
        <thead style="background-color: #f8fafc; border-bottom: 2px solid #e2e8f0;">
          <tr>
            <th class="py-3.5 text-uppercase text-dark fw-bold" style="width: 44%; font-size: 0.85rem; letter-spacing: 0.03em;">
              Policy Title</th>
            <th class="py-3.5 text-uppercase text-dark fw-bold" style="width: 22%; font-size: 0.85rem; letter-spacing: 0.03em;">
              Category</th>
            <th class="py-3.5 text-uppercase text-dark fw-bold" style="width: 16%; font-size: 0.85rem; letter-spacing: 0.03em;">Date Uploaded</th>
            <th class="py-3.5 text-center text-uppercase text-dark fw-bold" style="width: 18%; font-size: 0.85rem; letter-spacing: 0.03em;">Action</th>
          </tr>
        </thead>
        <tbody id="reportPolicyTableBody">
          <?php foreach ($report_policies as $i => $pol):
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
              $initialStaffSummary = $summary;
            }

            $risk = !empty($pol['risk_level']) ? $pol['risk_level'] : 'Low Risk';
            $recText = !empty($pol['ai_recommendation']) ? $pol['ai_recommendation'] : 'Proceed with implementation and continue monitoring the effectiveness of the policy.';

            $current_status = $pol['computed_status'] ?? 'Approved';
            $eval_state = $pol['status_key'] ?? 'approved';
            $is_approved = ($current_status === 'Approved');

            $policyData = [
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
              data-policy='<?= htmlspecialchars($policyJson, ENT_QUOTES, 'UTF-8') ?>'
              onclick="openPolicyRowReport(this)">
              <td class="py-3">
                <div class="d-flex align-items-center gap-2.5">
                  <div
                    class="rounded-3 p-1.5 <?= $is_approved ? 'bg-primary bg-opacity-10 text-primary' : 'bg-warning bg-opacity-10 text-warning' ?> d-flex align-items-center justify-content-center flex-shrink-0"
                    style="width: 32px; height: 32px;">
                    <i class="bi <?= $is_approved ? 'bi-file-earmark-check-fill' : 'bi-file-earmark-text' ?> fs-6"></i>
                  </div>
                  <div>
                    <a href="javascript:void(0)" class="fw-bold text-dark text-decoration-none policy-title-link"
                      onclick="event.stopPropagation(); openPolicyRowReport(this.closest('tr'));">
                      <?= htmlspecialchars($pol['title']) ?>
                    </a>
                  </div>
                </div>
              </td>
              <td class="py-3">
                <span class="badge rounded-pill text-white fw-bold px-3 py-1.5 shadow-2xs"
                  style="background-color: #0d6efd !important; font-size: 0.78rem;">
                  <?= htmlspecialchars($pol['category'] ?? '—') ?>
                </span>
              </td>

              <td class="text-secondary fw-medium py-3">
                <i class="bi bi-calendar3 me-1.5 text-muted opacity-75"></i>
                <?= $dateStr ?>
              </td>
              <td class="text-center py-3">
                <button type="button" class="btn btn-sm btn-report-download"
                  onclick="event.stopPropagation(); openPolicyRowReport(this.closest('tr'));"
                  title="Download / View Official Legislative Report">
                  <i class="bi bi-download text-primary"></i>
                  <span>Download / View</span>
                </button>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <div class="d-flex align-items-center justify-content-between pt-1">
      <small class="text-muted fw-medium" id="reportPoliciesSummaryText">Showing 1 to <?= min(10, count($report_policies)) ?> of <?= count($report_policies) ?> records</small>
      <div class="d-flex align-items-center gap-1" id="reportPolicyPagination">
        <button type="button" class="btn btn-sm btn-light border rounded-2 pagination-step-btn" id="reportPolicyPrevBtn" onclick="changeReportPolicyPage(-1)" title="Previous page" style="width:32px!important; height:32px!important; min-width:32px!important; max-width:32px!important; padding:0!important; display:inline-flex!important; align-items:center!important; justify-content:center!important;"><i class="bi bi-chevron-left"></i></button>
        <div id="reportPolicyPageNumbers" class="d-flex align-items-center gap-1">
          <button type="button" class="btn btn-sm btn-primary rounded-2 pagination-step-btn fw-bold" style="width:32px!important; height:32px!important; min-width:32px!important; max-width:32px!important; padding:0!important; display:inline-flex!important; align-items:center!important; justify-content:center!important;">1</button>
        </div>
        <button type="button" class="btn btn-sm btn-light border rounded-2 pagination-step-btn" id="reportPolicyNextBtn" onclick="changeReportPolicyPage(1)" title="Next page" style="width:32px!important; height:32px!important; min-width:32px!important; max-width:32px!important; padding:0!important; display:inline-flex!important; align-items:center!important; justify-content:center!important;"><i class="bi bi-chevron-right"></i></button>
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
        <p class="text-muted mb-0 small">Access, browse, and download legislative policy evaluations and cross-city
          benchmarks.</p>
      </div>
      <div class="d-flex align-items-center gap-2">
        <div class="btn-group btn-group-sm p-1 bg-light rounded-pill border shadow-2xs" role="group">
          <button type="button" class="btn btn-sm rounded-pill px-3 py-1 fw-bold text-white shadow-sm"
            id="filterStaffReportAll" style="background:#0B2E59;"
            onclick="filterStaffReportsTable('All', this)">All</button>
          <button type="button" class="btn btn-sm rounded-pill px-3 py-1 text-secondary" id="filterStaffReportEval"
            style="background:transparent;" onclick="filterStaffReportsTable('Evaluation', this)">Evaluations</button>
          <button type="button" class="btn btn-sm rounded-pill px-3 py-1 text-secondary" id="filterStaffReportBench"
            style="background:transparent;" onclick="filterStaffReportsTable('Benchmark', this)">Benchmarks</button>
        </div>
      </div>
    </div>
    <div class="table-responsive border rounded-4 overflow-hidden mb-2">
      <table class="table table-hover align-middle mb-0" id="recentGeneratedReportsTable" style="font-size:0.88rem; table-layout: fixed; width: 100%;">
        <colgroup>
          <col style="width: 28%;">
          <col style="width: 32%;">
          <col style="width: 14%;">
          <col style="width: 14%;">
          <col style="width: 12%;">
        </colgroup>
        <thead style="background-color: #f8fafc; border-bottom: 2px solid #e2e8f0;">
          <tr>
            <th class="py-3 px-3 text-uppercase text-dark fw-bold text-truncate" style="font-size: 0.88rem; letter-spacing: 0.03em;">
              Report Name</th>
            <th class="py-3 px-3 text-uppercase text-dark fw-bold text-truncate" style="font-size: 0.88rem; letter-spacing: 0.03em;">
              Policy / Subject</th>
            <th class="py-3 px-3 text-uppercase text-dark fw-bold text-nowrap" style="font-size: 0.88rem; letter-spacing: 0.03em; width: 140px;">Type
            </th>
            <th class="py-3 px-3 text-uppercase text-dark fw-bold text-nowrap" style="font-size: 0.88rem; letter-spacing: 0.03em; width: 180px;">Date
              Generated</th>
            <th class="py-3 px-3 text-end text-uppercase text-dark fw-bold text-nowrap"
              style="font-size: 0.88rem; letter-spacing: 0.03em; width: 160px;">Action</th>
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
        <button type="button" class="btn btn-sm btn-light border rounded-2 pagination-step-btn" id="recentReportsPrevBtn" onclick="changeRecentReportsPage(-1)" title="Previous page" style="width:32px!important; height:32px!important; min-width:32px!important; max-width:32px!important; padding:0!important; display:inline-flex!important; align-items:center!important; justify-content:center!important;"><i class="bi bi-chevron-left"></i></button>
        <div id="recentReportsPageNumbers" class="d-flex align-items-center gap-1">
          <button type="button" class="btn btn-sm btn-primary rounded-2 pagination-step-btn fw-bold" style="width:32px!important; height:32px!important; min-width:32px!important; max-width:32px!important; padding:0!important; display:inline-flex!important; align-items:center!important; justify-content:center!important;">1</button>
        </div>
        <button type="button" class="btn btn-sm btn-light border rounded-2 pagination-step-btn" id="recentReportsNextBtn" onclick="changeRecentReportsPage(1)" title="Next page" style="width:32px!important; height:32px!important; min-width:32px!important; max-width:32px!important; padding:0!important; display:inline-flex!important; align-items:center!important; justify-content:center!important;"><i class="bi bi-chevron-right"></i></button>
      </div>
    </div>
  </div>

  <!-- Official Document Report Viewer Modal -->
  <div class="modal fade" id="reportDocumentViewerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" style="max-width: 840px;">
      <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden bg-white">
        <div class="modal-header border-bottom px-4 py-3 bg-light d-flex align-items-center justify-content-between">
          <div class="d-flex align-items-center gap-2">
            <i class="bi bi-file-earmark-text-fill text-primary fs-5"></i>
            <h5 class="modal-title fw-bold text-dark mb-0 fs-6" id="reportViewerModalTitle">Official Legislative Document</h5>
          </div>
          <div class="d-flex align-items-center gap-2">
            <!-- View Original Document Button -->
            <button type="button" class="btn btn-sm btn-outline-primary rounded-3 px-3 py-1.5 fw-semibold d-inline-flex align-items-center gap-1.5 shadow-2xs" id="reportModalViewOriginalBtn" title="Open the original uploaded source document">
              <i class="bi bi-box-arrow-up-right"></i> View Original
            </button>
            <!-- Download PDF -->
            <button type="button" class="btn btn-sm btn-primary rounded-3 px-3 py-1.5 fw-semibold d-inline-flex align-items-center gap-1.5 shadow-sm" id="reportModalDownloadPdfBtn">
              <i class="bi bi-file-earmark-pdf-fill"></i> Download PDF
            </button>
            <!-- Download Word -->
            <button type="button" class="btn btn-sm btn-outline-secondary rounded-3 px-2.5 py-1.5 fw-semibold d-inline-flex align-items-center gap-1.5 bg-white shadow-2xs" id="reportModalDownloadDocxBtn">
              <i class="bi bi-file-earmark-word-fill text-primary"></i> Word (.docx)
            </button>
            <!-- Print Button -->
            <button type="button" class="btn btn-sm text-white rounded-3 px-2.5 py-1.5 fw-semibold d-inline-flex align-items-center gap-1.5 shadow-sm" style="background: #0B2E59; border-color: #0B2E59;" id="reportModalPrintBtn" title="Print Document">
              <i class="bi bi-printer-fill"></i> Print
            </button>
            <button type="button" class="btn-close ms-2" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
        </div>
        <div class="modal-body p-4 p-md-4" style="max-height: 75vh; overflow-y: auto; background:#f8fafc;">
          <div id="reportViewerModalDocumentBody" class="bg-white p-4 rounded-3 border shadow-sm mx-auto"
            style="max-width: 740px;">
            <!-- Rendered document will be injected here -->
          </div>
        </div>
        <div class="modal-footer border-top px-4 py-2.5 bg-light d-flex align-items-center justify-content-between">
          <span class="text-muted small"><i class="bi bi-shield-check text-success me-1"></i> Official City Council of Manila Legislative Document</span>
          <button type="button" class="btn btn-sm btn-secondary rounded-3 px-3.5 py-1.5 fw-medium" data-bs-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>

</section>

<script>
  (function () {
    var firstRow = document.querySelector('#reportPolicyTableBody .report-policy-row');
    if (firstRow) firstRow.style.backgroundColor = '#EFF6FF';
    if (typeof renderRecentGeneratedReportsTable === 'function') {
      renderRecentGeneratedReportsTable();
    }
  })();

  var _report = {
    title: '<?= addslashes(htmlspecialchars($report_policies[0]['title'] ?? '')) ?>',
    category: '<?= addslashes(htmlspecialchars($report_policies[0]['category'] ?? '')) ?>',
    status: '<?= addslashes(htmlspecialchars($report_policies[0]['status'] ?? 'Draft')) ?>',
    date: '<?= isset($report_policies[0]['created_at']) ? date('M d, Y', strtotime($report_policies[0]['created_at'])) : '—' ?>',
    summary: '<?= addslashes(htmlspecialchars($initialStaffSummary ?? '')) ?>',
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
        (rep.file_path && rep.file_path.trim() !== '' ?
          ('<a href="../assets/uploads/policies/' + encodeURIComponent(rep.file_path) + '" target="_blank" style="color:#0f766e; font-weight:600; text-decoration:underline; display:inline-flex; align-items:center; gap:5px;"><i class="bi bi-box-arrow-up-right"></i> ' + esc(rep.file_path) + ' (View Original Document)</a>') :
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
        '        <div style="font-weight:700; color:#0f172a; margin-top:20px; font-size:0.9rem;">Legislative Staff Secretariat</div>' +
        '        <div style="color:#64748b; font-size:0.78rem;">City Council of Manila</div>' +
        '      </td>' +
        '      <td width="50%" style="width:50%; border:none; padding:0; text-align:right; vertical-align:top;">' +
        '        <div style="color:#64748b; font-size:0.78rem;">Approved &amp; Endorsed By:</div>' +
        '        <div style="font-weight:700; color:#0f172a; margin-top:20px; font-size:0.9rem;">Legislative Officer</div>' +
        '        <div style="color:#64748b; font-size:0.78rem;">Legislative Information System</div>' +
        '      </td>' +
        '    </tr>' +
        '  </table>' +
        '  <div style="font-size:0.76rem; color:#94a3b8; border-top:1px solid #e2e8f0; padding-top:12px; text-align:center;">' +
        '    Issued by the City Council Legislative Staff Office — Manila City Hall Legislative Information System' +
        '  </div>' +
        '</div>';
    }

    // --- STANDARD EVALUATION REPORT TEMPLATE ---
    var evalRes = formatEvaluationResult(rep.risk);

    return '' +
      '<div style="max-width:720px; margin:0 auto; background:#ffffff; padding:15px; font-family:\'Segoe UI\', Arial, sans-serif; color:#0f172a; text-align:left;">' +
      '  <div style="text-align:center; margin-bottom:18px;">' +
      '    <img src="' + logoUrl + '" width="65" height="65" style="width:65px; height:65px; object-fit:contain; margin-bottom:8px;" alt="Manila Seal">' +
      '    <h1 style="color:#0B2E59; font-weight:800; font-size:1.6rem; letter-spacing:-0.5px; margin:0 0 4px 0;">LUNGSOD NG MAYNILA</h1>' +
      '    <div style="color:#475569; font-weight:600; font-size:0.88rem; margin-bottom:14px;">City of Manila — Legislative Services</div>' +
      '    <div style="margin-bottom:6px;">' +
      '      <span style="background:#0B2E59; color:#ffffff; padding:6px 16px; font-weight:700; text-transform:uppercase; letter-spacing:1px; font-size:0.82rem; border-radius:4px; display:inline-block;">Official Legislative Evaluation &amp; Impact Report</span>' +
      '    </div>' +
      '    <div style="color:#64748b; font-size:0.78rem; margin-top:6px;">Date Generated: ' + nowStr + '</div>' +
      '  </div>' +
      '  <div style="border-bottom:2px solid #0B2E59; margin-bottom:20px;"></div>' +

      '  <table width="100%" style="width:100%; border-collapse:collapse; margin-bottom:20px; font-size:0.88rem;">' +
      '    <tbody>' +
      '      <tr>' +
      '        <th width="170" style="padding:12px 16px; border:1px solid #cbd5e1; background-color:#f8fafc; font-weight:700; width:170px; text-align:left; color:#334155;">Policy Title</th>' +
      '        <td style="padding:12px 16px; border:1px solid #cbd5e1; font-weight:700; color:#0f172a; font-size:0.92rem;">' + esc(rep.title || rep.policy_title) + '</td>' +
      '      </tr>' +
      '      <tr>' +
      '        <th width="170" style="padding:12px 16px; border:1px solid #cbd5e1; background-color:#f8fafc; font-weight:700; width:170px; text-align:left; color:#334155;">Category</th>' +
      '        <td style="padding:12px 16px; border:1px solid #cbd5e1; color:#1e293b;">' + esc(rep.category || 'General Legislation') + '</td>' +
      '      </tr>' +
      '      <tr>' +
      '        <th width="170" style="padding:12px 16px; border:1px solid #cbd5e1; background-color:#f8fafc; font-weight:700; width:170px; text-align:left; color:#334155;">Original Document</th>' +
      '        <td style="padding:12px 16px; border:1px solid #cbd5e1; color:#1e293b;">' +
      (rep.file_path && rep.file_path.trim() !== '' ?
        ('<a href="../assets/uploads/policies/' + encodeURIComponent(rep.file_path) + '" target="_blank" style="color:#0d6efd; font-weight:600; text-decoration:underline; display:inline-flex; align-items:center; gap:5px;"><i class="bi bi-box-arrow-up-right"></i> ' + esc(rep.file_path) + ' (View Original Document)</a>') :
        ('<span style="color:#64748b; font-style:italic;"><i class="bi bi-archive me-1"></i> Official Manila City Legislative Record (Digital Archive)</span>')
      ) +
      '        </td>' +
      '      </tr>' +
      '      <tr>' +
      '        <th width="170" style="padding:12px 16px; border:1px solid #cbd5e1; background-color:#f8fafc; font-weight:700; width:170px; text-align:left; color:#334155;">AI Executive Summary</th>' +
      '        <td style="padding:12px 16px; border:1px solid #cbd5e1; color:#1e293b; line-height:1.65;">' + esc(rep.summary) + '</td>' +
      '      </tr>' +
      '      <tr>' +
      '        <th width="170" style="padding:12px 16px; border:1px solid #cbd5e1; background-color:#f8fafc; font-weight:700; width:170px; text-align:left; color:#334155;">Evaluation Result</th>' +
      '        <td style="padding:12px 16px; border:1px solid #cbd5e1;">' +
      '          <span style="background:' + evalRes.bg + '; color:' + evalRes.color + '; border:1px solid ' + evalRes.border + '; padding:4px 12px; border-radius:4px; font-weight:700; font-size:0.82rem; display:inline-block;">' + esc(evalRes.text) + '</span>' +
      '        </td>' +
      '      </tr>' +
      '      <tr>' +
      '        <th width="170" style="padding:12px 16px; border:1px solid #cbd5e1; background-color:#f8fafc; font-weight:700; width:170px; text-align:left; color:#334155;">Recommendation</th>' +
      '        <td style="padding:12px 16px; border:1px solid #cbd5e1; color:#1e293b; line-height:1.65;">' + esc(rep.recommendation) + '</td>' +
      '      </tr>' +
      '    </tbody>' +
      '  </table>' +

      '  <table width="100%" style="width:100%; border-collapse:collapse; margin-top:35px; margin-bottom:25px; font-size:0.85rem; border:none;">' +
      '    <tr>' +
      '      <td width="50%" style="width:50%; border:none; padding:0; vertical-align:top; text-align:left;">' +
      '        <div style="color:#64748b; font-size:0.78rem;">Prepared &amp; Evaluated By:</div>' +
      '        <div style="font-weight:700; color:#0f172a; margin-top:22px; font-size:0.9rem;">Legislative Staff Secretariat</div>' +
      '        <div style="color:#64748b; font-size:0.78rem;">City Council of Manila</div>' +
      '      </td>' +
      '      <td width="50%" style="width:50%; border:none; padding:0; text-align:right; vertical-align:top;">' +
      '        <div style="color:#64748b; font-size:0.78rem;">Approved By:</div>' +
      '        <div style="font-weight:700; color:#0f172a; margin-top:22px; font-size:0.9rem;">Legislative Officer</div>' +
      '        <div style="color:#64748b; font-size:0.78rem;">Legislative Information System</div>' +
      '      </td>' +
      '    </tr>' +
      '  </table>' +

      '  <div style="font-size:0.78rem; color:#94a3b8; border-top:1px solid #e2e8f0; padding-top:14px; text-align:center;">' +
      '    Issued by the City Council Legislative Staff Office — Manila City Hall Legislative Information System' +
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

    var logoUrl = '../assets/images/manilacityhall.svg';
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
      '    <button id="btnStaffSaveDocxInWindow" class="btn btn-outline-primary btn-sm px-3 fw-bold bg-white shadow-sm">' +
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
          var saveDocxBtn = printWin.document.getElementById('btnStaffSaveDocxInWindow');
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
      var printIframe = document.getElementById('staffReportPrintIframe');
      if (!printIframe) {
        printIframe = document.createElement('iframe');
        printIframe.id = 'staffReportPrintIframe';
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

  var STAFF_RECENT_REPORTS_KEY = 'legislative_staff_recent_reports_v4';
  var currentStaffReportFilter = 'All';
  var _recentStaffReportsCurrentPage = 1;
  var _recentStaffReportsPageSize = 10;

  function filterStaffReportsTable(filterType, btnEl) {
    currentStaffReportFilter = filterType;
    _recentStaffReportsCurrentPage = 1;
    var btns = document.querySelectorAll('.btn-group button[id^="filterStaffReport"]');
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
  window.filterStaffReportsTable = filterStaffReportsTable;

  function changeRecentReportsPage(delta) {
    goToRecentReportsPage(_recentStaffReportsCurrentPage + delta);
  }
  window.changeRecentReportsPage = changeRecentReportsPage;

  function goToRecentReportsPage(page) {
    _recentStaffReportsCurrentPage = page;
    renderRecentGeneratedReportsTable();
  }
  window.goToRecentReportsPage = goToRecentReportsPage;

  function loadRecentGeneratedReports() {
    try {
      var raw = localStorage.getItem(STAFF_RECENT_REPORTS_KEY);
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
      localStorage.setItem(STAFF_RECENT_REPORTS_KEY, JSON.stringify(defaultSeed));
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
      localStorage.setItem(STAFF_RECENT_REPORTS_KEY, JSON.stringify(list));
    } catch (e) { }

    renderRecentGeneratedReportsTable();
  }

  function renderRecentGeneratedReportsTable() {
    var tbody = document.getElementById('recentGeneratedReportsBody');
    var countEl = document.getElementById('recentGeneratedReportsCount');
    var paginationContainer = document.getElementById('recentReportsPagination');
    if (!tbody) return;

    var allList = loadRecentGeneratedReports();
    var list = allList;

    if (currentStaffReportFilter === 'Evaluation') {
      list = allList.filter(function (r) { return !r.report_type || r.report_type.indexOf('Evaluation') !== -1; });
    } else if (currentStaffReportFilter === 'Benchmark') {
      list = allList.filter(function (r) { return r.report_type && (r.report_type.indexOf('Benchmark') !== -1 || r.report_type.indexOf('Comparison') !== -1); });
    } else if (currentStaffReportFilter === 'Version') {
      list = allList.filter(function (r) { return r.report_type && (r.report_type.indexOf('Version') !== -1); });
    }

    if (!list || list.length === 0) {
      tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted py-4"><i class="bi bi-info-circle me-1"></i> No matching reports found for this filter.</td></tr>';
      if (countEl) countEl.textContent = 'Showing 0 records';
      if (paginationContainer) paginationContainer.style.display = 'none';
      return;
    }

    if (paginationContainer) paginationContainer.style.display = 'flex';

    var totalMatching = list.length;
    var totalPages = Math.max(1, Math.ceil(totalMatching / _recentStaffReportsPageSize));

    if (_recentStaffReportsCurrentPage < 1) _recentStaffReportsCurrentPage = 1;
    if (_recentStaffReportsCurrentPage > totalPages) _recentStaffReportsCurrentPage = totalPages;

    var startIdx = (_recentStaffReportsCurrentPage - 1) * _recentStaffReportsPageSize;
    var endIdx = Math.min(startIdx + _recentStaffReportsPageSize, totalMatching);

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
        '<td class="py-2.5 px-3 text-muted small text-nowrap">' +
        '<i class="bi bi-calendar3 me-1.5 text-muted"></i>' + esc(r.date_generated) +
        '</td>' +
        '<td class="py-2.5 px-3 text-end text-nowrap">' +
        '<button type="button" class="btn btn-sm btn-report-download" onclick="downloadRecentGeneratedReport(' + downloadIdx + ')">' +
        '<i class="bi bi-download text-primary"></i>' +
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
      prevBtn.disabled = (_recentStaffReportsCurrentPage <= 1);
      if (_recentStaffReportsCurrentPage <= 1) {
        prevBtn.classList.add('opacity-50');
      } else {
        prevBtn.classList.remove('opacity-50');
      }
    }

    var nextBtn = document.getElementById('recentReportsNextBtn');
    if (nextBtn) {
      nextBtn.disabled = (_recentStaffReportsCurrentPage >= totalPages);
      if (_recentStaffReportsCurrentPage >= totalPages) {
        nextBtn.classList.add('opacity-50');
      } else {
        nextBtn.classList.remove('opacity-50');
      }
    }

    var pagesContainer = document.getElementById('recentReportsPageNumbers');
    if (pagesContainer) {
      var pagesHtml = '';
      for (var p = 1; p <= totalPages; p++) {
        var isActive = (p === _recentStaffReportsCurrentPage);
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

  var _reportPolicyFilter = 'all';
  var _reportPolicyCurrentPage = 1;
  var _reportPolicyPageSize = 10;

  function filterReportPolicies(type, btn) {
    _reportPolicyFilter = type || 'all';
    _reportPolicyCurrentPage = 1;

    var group = btn ? btn.closest('#reportPolicyFilterGroup') : document.getElementById('reportPolicyFilterGroup');
    if (group && btn) {
      var btns = group.querySelectorAll('.policy-filter-tab');
      btns.forEach(function(b) {
        b.classList.remove('active', 'btn-primary', 'text-white');
        b.classList.add('text-secondary');
      });
      btn.classList.add('active', 'btn-primary', 'text-white');
      btn.classList.remove('text-secondary');
    }

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
    if (!allRows.length) return;

    var matchingRows = allRows.filter(function(r) {
      var state = r.getAttribute('data-eval-state');
      return (_reportPolicyFilter === 'all' || state === _reportPolicyFilter);
    });

    var totalMatching = matchingRows.length;
    var totalPages = Math.max(1, Math.ceil(totalMatching / _reportPolicyPageSize));

    if (_reportPolicyCurrentPage < 1) _reportPolicyCurrentPage = 1;
    if (_reportPolicyCurrentPage > totalPages) _reportPolicyCurrentPage = totalPages;

    var startIdx = (_reportPolicyCurrentPage - 1) * _reportPolicyPageSize;
    var endIdx = Math.min(startIdx + _reportPolicyPageSize, totalMatching);

    allRows.forEach(function(r) {
      var matchIdx = matchingRows.indexOf(r);
      if (matchIdx >= startIdx && matchIdx < endIdx) {
        r.style.display = '';
      } else {
        r.style.display = 'none';
      }
    });

    var summaryEl = document.getElementById('reportPoliciesSummaryText');
    if (summaryEl) {
      if (totalMatching === 0) {
        summaryEl.textContent = 'Showing 0 records';
      } else {
        summaryEl.textContent = 'Showing ' + (startIdx + 1) + ' to ' + endIdx + ' of ' + totalMatching + ' records';
      }
    }

    var prevBtn = document.getElementById('reportPolicyPrevBtn');
    if (prevBtn) {
      prevBtn.disabled = (_reportPolicyCurrentPage <= 1);
      if (_reportPolicyCurrentPage <= 1) {
        prevBtn.classList.add('opacity-50');
      } else {
        prevBtn.classList.remove('opacity-50');
      }
    }

    var nextBtn = document.getElementById('reportPolicyNextBtn');
    if (nextBtn) {
      nextBtn.disabled = (_reportPolicyCurrentPage >= totalPages);
      if (_reportPolicyCurrentPage >= totalPages) {
        nextBtn.classList.add('opacity-50');
      } else {
        nextBtn.classList.remove('opacity-50');
      }
    }

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

  window.filterReportPolicies = filterReportPolicies;
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

  var _activeModalReport = null;
  var _activeModalFileName = '';

  function openReportDocumentModal(rep, fileName) {
    _activeModalReport = rep;
    var cleanTitle = ((rep.title || rep.policy_title || 'Policy').replace(/[^a-zA-Z0-9 ]/g, '').trim().replace(/\s+/g, '_'));
    _activeModalFileName = fileName || (cleanTitle + '_Report.pdf');

    var logoUrl = '../assets/images/manilacityhall.svg';
    var htmlContent = buildSharedReportTemplate(rep, logoUrl);

    var bodyEl = document.getElementById('reportViewerModalDocumentBody');
    if (bodyEl) bodyEl.innerHTML = htmlContent;

    var titleEl = document.getElementById('reportViewerModalTitle');
    if (titleEl) titleEl.textContent = rep.report_type || 'Official Legislative Document';

    var viewOrigBtn = document.getElementById('reportModalViewOriginalBtn');
    if (viewOrigBtn) {
      if (rep.file_path && rep.file_path.trim() !== '') {
        viewOrigBtn.classList.remove('d-none');
        viewOrigBtn.onclick = function() {
          window.open('../assets/uploads/policies/' + encodeURIComponent(rep.file_path), '_blank');
        };
      } else {
        viewOrigBtn.classList.remove('d-none');
        viewOrigBtn.onclick = function() {
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
    var logoUrl = '../assets/images/manilacityhall.svg';
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
    var logoUrl = '../assets/images/manilacityhall.svg';

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