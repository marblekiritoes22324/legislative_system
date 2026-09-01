<!-- staff/includes/modals.php — Shared Modals for Staff Portal -->

<!-- 1. AI Document Summarizer Modal (Official Document Report Layout) -->
<div class="modal fade" id="aiSummarizerModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg" style="max-width: 820px;">
    <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden" style="background-color: #ffffff;">

      <!-- Header Close Button -->
      <div class="modal-header border-0 pb-0 justify-content-end bg-white px-4 pt-3">
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body px-4 px-md-5 pb-4 pt-0" id="aiReportPrintableArea"
        style="max-height: 80vh; overflow-y: auto;">

        <!-- Loading State -->
        <div id="aiSummaryLoading" class="text-center py-5" style="display:none;">
          <div class="d-inline-flex align-items-center justify-content-center mb-3">
            <button id="aiAnalyzingStatusBtn"
              class="btn btn-primary rounded-4 py-2 px-4 fw-semibold text-white shadow-sm border-0" disabled
              style="background-color: #3b82f6;">
              <i id="aiAnalyzingStatusIcon" class="bi bi-arrow-repeat spin me-2" style="font-size:1.1rem;"></i><span
                id="aiAnalyzingStatusText">Analyzing...</span>
            </button>
          </div>
          <div class="fw-semibold text-dark fs-5 mt-2">Gemini AI is reading and analyzing document...</div>
          <p class="text-muted small mb-0">Extracting executive summary, policy impact, and legislative recommendations.
          </p>
        </div>

        <!-- Error State -->
        <div id="aiSummaryError" class="alert alert-danger rounded-3" style="display:none;">
          <i class="bi bi-exclamation-triangle-fill me-2"></i><span id="aiSummaryErrorMsg"></span>
        </div>

        <!-- Official Document Summary Report Content -->
        <div id="aiSummaryContent" style="display:none; font-family: 'Times New Roman', Times, serif; color: #1a1a1a;">

          <!-- Document Seal Header -->
          <div class="text-center mb-4">
            <div class="d-flex align-items-center justify-content-center gap-3 mb-2">
              <img src="../assets/images/manilacityhall.svg" alt="Manila Seal"
                style="width: 70px; height: 70px; object-fit: contain;">
              <div>
                <h4 class="fw-bold mb-0 text-uppercase" style="letter-spacing: 2px; font-size: 1.35rem; color: #000;">
                  MANILA CITY HALL</h4>
                <div class="fw-bold text-uppercase text-secondary" style="font-size: 0.85rem; letter-spacing: 1.5px;">
                  OFFICE OF THE CITY COUNCIL</div>
              </div>
            </div>
            <h2 class="fw-bold text-dark mt-3 mb-2" style="font-size: 1.65rem;">AI Document Summary Report</h2>
            <div class="d-flex align-items-center justify-content-center gap-2">
              <div style="height: 1px; width: 100px; background-color: #333;"></div>
              <i class="bi bi-bank fs-5 text-dark"></i>
              <div style="height: 1px; width: 100px; background-color: #333;"></div>
            </div>
          </div>

          <!-- SECTION 1: DOCUMENT INFORMATION -->
          <div class="mb-4">
            <div class="d-flex align-items-center gap-2 mb-3">
              <i class="bi bi-file-earmark-text fs-5 text-dark"></i>
              <h5 class="fw-bold text-uppercase mb-0" style="font-size: 0.95rem; letter-spacing: 1px;">DOCUMENT
                INFORMATION</h5>
            </div>

            <div class="ps-4" style="font-size: 0.95rem; line-height: 1.8;">
              <div class="row mb-1">
                <div class="col-4 col-sm-3 fw-bold">Document Title</div>
                <div class="col-1 text-center">:</div>
                <div class="col-7 col-sm-8 fw-semibold text-dark" id="aiSum_title">Flood Risk Assessment and Drainage
                  Improvement Plan for Manila City</div>
              </div>
              <div class="row mb-1">
                <div class="col-4 col-sm-3 fw-bold">Ordinance No.</div>
                <div class="col-1 text-center">:</div>
                <div class="col-7 col-sm-8 text-dark" id="aiSum_ordinanceNo">2026-015</div>
              </div>
              <div class="row mb-1">
                <div class="col-4 col-sm-3 fw-bold">Category</div>
                <div class="col-1 text-center">:</div>
                <div class="col-7 col-sm-8 text-dark" id="aiSum_category">Disaster Risk Reduction</div>
              </div>
              <div class="row mb-1">
                <div class="col-4 col-sm-3 fw-bold">Date Generated</div>
                <div class="col-1 text-center">:</div>
                <div class="col-7 col-sm-8 text-dark" id="aiSum_date">August 3, 2026 • 10:46 PM</div>
              </div>
              <div class="row mb-1">
                <div class="col-4 col-sm-3 fw-bold">Generated By</div>
                <div class="col-1 text-center">:</div>
                <div class="col-7 col-sm-8 text-dark" id="aiSum_generatedBy">Gemini AI</div>
              </div>
            </div>
          </div>
          <hr style="border-color: #d1d5db; opacity: 0.7;" class="my-4">

          <!-- SECTION 2: EXECUTIVE SUMMARY -->
          <div class="mb-4">
            <div class="d-flex align-items-center gap-2 mb-2">
              <i class="bi bi-card-text fs-5 text-dark"></i>
              <h5 class="fw-bold text-uppercase mb-0" style="font-size: 0.95rem; letter-spacing: 1px;">EXECUTIVE SUMMARY
              </h5>
            </div>
            <p class="ps-4 mb-0 text-dark" id="aiSum_summary"
              style="font-size: 0.95rem; line-height: 1.7; text-align: justify;">
              This document evaluates the growing frequency of urban flooding in Manila City using field assessments,
              rainfall records, and community surveys. It identifies major vulnerabilities within the city's drainage
              network and outlines strategic infrastructure and management solutions. Implementing these proposals aims
              to reduce flood risks and safeguard public safety and local infrastructure.
            </p>
          </div>
          <hr style="border-color: #d1d5db; opacity: 0.7;" class="my-4">

          <!-- SECTION 3: KEY FINDINGS -->
          <div class="mb-4">
            <div class="d-flex align-items-center gap-2 mb-2">
              <i class="bi bi-search fs-5 text-dark"></i>
              <h5 class="fw-bold text-uppercase mb-0" style="font-size: 0.95rem; letter-spacing: 1px;">KEY FINDINGS</h5>
            </div>
            <div class="ps-4 text-dark" id="aiSum_findings" style="font-size: 0.95rem; line-height: 1.7;">
              <ul class="mb-0 ps-3">
                <li class="mb-1">Clogged drainage systems severely restrict water flow during heavy rainfall events.
                </li>
                <li class="mb-1">Existing pumping stations are inadequate to manage high volumes of storm run-off across
                  districts.</li>
                <li class="mb-1">Improper waste disposal practices exacerbate the blockages in urban drainage channels.
                </li>
                <li class="mb-1">Aging and outdated drainage infrastructure heavily contributes to recurring localized
                  flooding.</li>
              </ul>
            </div>
          </div>
          <hr style="border-color: #d1d5db; opacity: 0.7;" class="my-4">

          <!-- SECTION 4: POLICY IMPACT -->
          <div class="mb-4">
            <div class="d-flex align-items-center gap-2 mb-2">
              <i class="bi bi-bar-chart-line fs-5 text-dark"></i>
              <h5 class="fw-bold text-uppercase mb-0" style="font-size: 0.95rem; letter-spacing: 1px;">POLICY IMPACT
              </h5>
            </div>
            <p class="ps-4 mb-0 text-dark" id="aiSum_impact" style="font-size: 0.95rem; line-height: 1.7;">
              Enforcing stricter waste management regulations alongside drainage modernization will strengthen Manila's
              disaster resilience and protect critical public assets.
            </p>
          </div>
          <hr style="border-color: #d1d5db; opacity: 0.7;" class="my-4">

          <!-- SECTION 5: CONCLUSION -->
          <div class="mb-4">
            <div class="d-flex align-items-center gap-2 mb-2">
              <i class="bi bi-check2-square fs-5 text-dark"></i>
              <h5 class="fw-bold text-uppercase mb-0" style="font-size: 0.95rem; letter-spacing: 1px;">CONCLUSION</h5>
            </div>
            <p class="ps-4 mb-0 text-dark" id="aiSum_recommendation" style="font-size: 0.95rem; line-height: 1.7;">
              The proposed strategy focuses on drainage rehabilitation, pumping station expansion, smart flood
              monitoring, and policy enforcement to minimize economic losses and ensure public safety during the rainy
              season.
            </p>
          </div>
          <hr style="border-color: #d1d5db; opacity: 0.7;" class="my-4">

          <!-- SECTION 6: ORIGINAL DOCUMENT -->
          <div class="mb-2">
            <div class="d-flex align-items-center gap-2 mb-2">
              <i class="bi bi-paperclip fs-5 text-dark"></i>
              <h5 class="fw-bold text-uppercase mb-0" style="font-size: 0.95rem; letter-spacing: 1px;">ORIGINAL DOCUMENT
              </h5>
            </div>
            <div class="ps-4">
              <a id="aiSum_doclink" href="#" target="_blank"
                class="btn btn-outline-secondary btn-sm rounded-3 px-3 py-2 fw-semibold d-inline-flex align-items-center gap-2"
                style="font-family: sans-serif; font-size: 0.85rem;">
                <i class="bi bi-file-earmark-pdf-fill text-danger fs-5"></i> View Original PDF
              </a>
            </div>
          </div>

        </div>

      </div>

      <!-- Footer Actions Bar -->
      <div class="modal-footer bg-white border-top px-4 py-3 justify-content-between">
        <div class="d-flex align-items-center gap-2 text-muted" style="font-size: 0.78rem; font-family: sans-serif;">
          <i class="bi bi-stars text-primary fs-5"></i>
          <div>
            <div class="fw-semibold text-dark">Generated by AI Document Summarization</div>
            <div>Legislative Staff Portal &bull; Manila City Hall</div>
          </div>
        </div>

        <div class="d-flex align-items-center gap-2" style="font-family: sans-serif;">
          <button type="button" class="btn btn-dark fw-semibold px-3 py-2 rounded-3 d-flex align-items-center gap-2"
            onclick="downloadAiReport()">
            <i class="bi bi-download me-1"></i> Download Report
          </button>
        </div>
      </div>

    </div>
  </div>
</div>

<!-- 2. Upload Policy Modal -->
<div class="modal fade" id="uploadPolicyModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" style="max-width: 630px;">
    <div class="modal-content border-0 shadow rounded-4">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title fw-bold text-dark"><i class="bi bi-upload text-warning me-2"></i> Upload Policy Record
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form method="POST" action="staff_dashboard.php" enctype="multipart/form-data">
        <input type="hidden" name="action" value="add">
        <div class="modal-body p-4" style="max-height: 70vh; overflow-y: auto;">
          <div class="row g-3">
            <div class="col-md-12">
              <label class="form-label fw-semibold small">Research Title <span class="text-danger">*</span></label>
              <input type="text" name="title" class="form-control" placeholder="Enter the policy or research title"
                required>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold small">Category <span class="text-danger">*</span></label>
              <select name="category" class="form-select" required>
                <option value="Health and Sanitation">Health and Sanitation</option>
                <option value="Civil Registry and Public Services">Civil Registry and Public Services</option>
                <option value="Education and Employment">Education and Employment</option>
                <option value="Social Welfare and Community Affairs">Social Welfare and Community Affairs</option>
                <option value="Infrastructure, Traffic and Environment">Infrastructure, Traffic and Environment</option>
                <option value="Other">Other</option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold small">LGU / City Origin <span class="text-danger">*</span></label>
              <select name="city_origin" class="form-select" required>
                <option value="City of Manila" selected>🏛️ City of Manila (Local)</option>
                <option value="Quezon City (QC Benchmark)">🏙️ Quezon City (QC Benchmark)</option>
                <option value="Pasig City (Benchmark)">🏙️ Pasig City (Benchmark)</option>
                <option value="Makati City (Benchmark)">🏙️ Makati City (Benchmark)</option>
                <option value="Taguig City (Benchmark)">🏙️ Taguig City (Benchmark)</option>
                <option value="Valenzuela City (Benchmark)">🏙️ Valenzuela City (Benchmark)</option>
                <option value="Other LGU / National Benchmark">🌐 Other LGU / National Benchmark</option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold small">Author(s) <span class="text-danger">*</span></label>
              <input type="text" name="author" class="form-control" placeholder="Enter author name" required>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold small">Department/Office <span class="text-danger">*</span></label>
              <input type="text" name="department" class="form-control" placeholder="Enter department" required>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold small">Publication Date</label>
              <input type="date" name="publication_date" class="form-control" min="<?= date('Y-m-d') ?>">
            </div>
            <div class="col-md-12">
              <label class="form-label fw-semibold small">Research Description</label>
              <textarea name="description" class="form-control" rows="3"
                placeholder="Brief summary of the research..."></textarea>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold small">Upload Document <span class="text-danger">*</span></label>
              <input type="file" id="researchFileInput" name="research_file" class="form-control"
                accept=".pdf,.docx,.doc" required>
            </div>
            <div class="col-md-6 d-flex align-items-end">
              <input type="hidden" id="aiKeywordsInput" name="keywords" value="">
              <button type="button" class="btn btn-primary fw-semibold rounded-3 w-100" onclick="generateKeywords()"
                id="aiManualBtn">
                <i class="bi bi-magic me-2"></i>Auto Fill
              </button>
            </div>
          </div>
        </div>
        <div class="modal-footer border-0 pt-0">
          <button type="button" class="btn btn-secondary rounded-3" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-warning fw-semibold rounded-3">Upload Record</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- 3. Edit Policy Modal -->
<div class="modal fade" id="editPolicyModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" style="max-width: 630px;">
    <div class="modal-content border-0 shadow rounded-4">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title fw-bold text-dark"><i class="bi bi-pencil-square text-primary me-2"></i> Edit Policy
          Record</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form method="POST" action="staff_dashboard.php" enctype="multipart/form-data">
        <input type="hidden" name="action" value="edit">
        <input type="hidden" name="id" id="edit_id">
        <div class="modal-body p-4" style="max-height: 70vh; overflow-y: auto;">
          <div class="row g-3">
            <div class="col-md-12">
              <label class="form-label fw-semibold small">Research Title <span class="text-danger">*</span></label>
              <input type="text" name="title" id="edit_title" class="form-control" required>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold small">Category <span class="text-danger">*</span></label>
              <select name="category" id="edit_category" class="form-select" required>
                <option value="Health and Sanitation">Health and Sanitation</option>
                <option value="Civil Registry and Public Services">Civil Registry and Public Services</option>
                <option value="Education and Employment">Education and Employment</option>
                <option value="Social Welfare and Community Affairs">Social Welfare and Community Affairs</option>
                <option value="Infrastructure, Traffic and Environment">Infrastructure, Traffic and Environment</option>
                <option value="Other">Other</option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold small">LGU / City Origin <span class="text-danger">*</span></label>
              <select name="city_origin" id="edit_city_origin" class="form-select" required>
                <option value="City of Manila">🏛️ City of Manila (Local)</option>
                <option value="Quezon City (QC Benchmark)">🏙️ Quezon City (QC Benchmark)</option>
                <option value="Pasig City (Benchmark)">🏙️ Pasig City (Benchmark)</option>
                <option value="Makati City (Benchmark)">🏙️ Makati City (Benchmark)</option>
                <option value="Taguig City (Benchmark)">🏙️ Taguig City (Benchmark)</option>
                <option value="Valenzuela City (Benchmark)">🏙️ Valenzuela City (Benchmark)</option>
                <option value="Other LGU / National Benchmark">🌐 Other LGU / National Benchmark</option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold small">Author(s) <span class="text-danger">*</span></label>
              <input type="text" name="author" id="edit_author" class="form-control" required>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold small">Department/Office <span class="text-danger">*</span></label>
              <input type="text" name="department" id="edit_department" class="form-control" required>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold small">Publication Date</label>
              <input type="date" name="publication_date" id="edit_publication_date" class="form-control"
                min="<?= date('Y-m-d') ?>">
            </div>
            <div class="col-md-12">
              <label class="form-label fw-semibold small">Research Description</label>
              <textarea name="description" id="edit_description" class="form-control" rows="3"></textarea>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold small">Keywords</label>
              <input type="text" name="keywords" id="edit_keywords" class="form-control">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold small">Update Document (Optional)</label>
              <input type="file" name="research_file" class="form-control" accept=".pdf,.docx,.doc">
              <small class="text-muted">Leave empty to keep existing file</small>
            </div>
          </div>
        </div>
        <div class="modal-footer border-0 pt-0">
          <button type="button" class="btn btn-secondary rounded-3" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary fw-semibold rounded-3">Save Changes</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- 4. Data Collection Auto-Sync Modal -->
<div class="modal fade" id="uploadDatasetModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow rounded-4">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title fw-bold text-dark"><i class="bi bi-database-check text-success me-2"></i> Data Collection
          is Auto-Synced</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4">
        <div class="text-center mb-3">
          <i class="bi bi-arrow-repeat text-success" style="font-size:3rem;"></i>
        </div>
        <p class="text-muted text-center mb-3">
          The <strong>Data Collection</strong> table automatically displays all records from <strong>Policy
            Research</strong>.
          No separate upload is needed — any document uploaded in Policy Research will instantly appear here.
        </p>
        <div class="alert alert-success border-0 rounded-3 py-2 px-3 small text-center">
          <i class="bi bi-check-circle-fill me-1"></i> To add a new entry, go to <strong>Policy Research</strong> and
          upload your document there.
        </div>
      </div>
      <div class="modal-footer border-0 pt-0">
        <button type="button" class="btn btn-secondary rounded-3" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn btn-success fw-semibold rounded-3"
          onclick="bootstrap.Modal.getInstance(document.getElementById('uploadDatasetModal')).hide(); showSection('policyResearchSection');">
          <i class="bi bi-file-earmark-text me-1"></i> Go to Policy Research
        </button>
      </div>
    </div>
  </div>
</div>

<!-- 5. Impact Assessment Modal (Official Document Report Layout & Interactive Execution) -->
<div class="modal fade" id="evaluationDetailModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg" style="max-width: 820px;">
    <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden" style="background-color: #ffffff;">

      <!-- Header Close Button -->
      <div class="modal-header border-0 pb-0 justify-content-end bg-white px-4 pt-3">
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body px-4 px-md-5 pb-4 pt-0" style="max-height: 80vh; overflow-y: auto;">

        <!-- Official Document Report Content -->
        <div id="evalReportContent"
          style="font-family: 'Segoe UI', system-ui, -apple-system, sans-serif; color: #1a1a1a;">

          <!-- Document Seal Header -->
          <div class="text-center mb-4">
            <div class="d-flex align-items-center justify-content-center gap-3 mb-2">
              <img src="../assets/images/manilacityhall.svg" alt="Manila Seal"
                style="width: 70px; height: 70px; object-fit: contain;">
              <div>
                <h4 class="fw-bold mb-0 text-uppercase" style="letter-spacing: 2px; font-size: 1.35rem; color: #000;">
                  MANILA CITY HALL</h4>
                <div class="fw-bold text-uppercase text-secondary" style="font-size: 0.85rem; letter-spacing: 1.5px;">
                  OFFICE OF THE CITY COUNCIL</div>
              </div>
            </div>
            <h2 class="fw-bold text-dark mt-3 mb-2" style="font-size: 1.65rem;">Evaluation Report</h2>
            <div class="d-flex align-items-center justify-content-center gap-2">
              <div style="height: 1px; width: 100px; background-color: #333;"></div>
              <i class="bi bi-bar-chart-line fs-5 text-dark"></i>
              <div style="height: 1px; width: 100px; background-color: #333;"></div>
            </div>
          </div>

          <!-- SECTION 1: EVALUATION INFORMATION -->
          <div class="mb-4">
            <div class="d-flex align-items-center gap-2 mb-3">
              <i class="bi bi-file-earmark-text fs-5 text-dark"></i>
              <h5 class="fw-bold text-uppercase mb-0" style="font-size: 0.95rem; letter-spacing: 1px;">EVALUATION
                INFORMATION</h5>
            </div>
            <div class="ps-4" style="font-size: 0.95rem; line-height: 1.8;">
              <div class="row mb-1">
                <div class="col-4 col-sm-3 fw-bold">Policy Title</div>
                <div class="col-1 text-center">:</div>
                <div class="col-7 col-sm-8 fw-semibold text-dark" id="evalModalTitle">Flood Risk Assessment and Drainage
                  Improvement Plan for Manila City</div>
              </div>
              <div class="row mb-1">
                <div class="col-4 col-sm-3 fw-bold">Status</div>
                <div class="col-1 text-center">:</div>
                <div class="col-7 col-sm-8 text-dark">
                  <span id="evalModalStatus" class="badge bg-success px-2.5 py-1">Completed</span>
                </div>
              </div>
              <div class="row mb-1">
                <div class="col-4 col-sm-3 fw-bold">Evaluation Date</div>
                <div class="col-1 text-center">:</div>
                <div class="col-7 col-sm-8 text-dark" id="evalModalDate">—</div>
              </div>
              <div class="row mb-1">
                <div class="col-4 col-sm-3 fw-bold">Evaluated By</div>
                <div class="col-1 text-center">:</div>
                <div class="col-7 col-sm-8 text-dark" id="evalModalEvaluator">Staff</div>
              </div>
              <div class="row mb-1 d-none" id="evalModalApprovedRow">
                <div class="col-4 col-sm-3 fw-bold text-success"><i class="bi bi-patch-check-fill me-1"></i>Approved By
                </div>
                <div class="col-1 text-center">:</div>
                <div class="col-7 col-sm-8 text-dark fw-medium">
                  <span id="evalModalApprovedBy">Staff</span>
                  <span class="text-muted small ms-2" id="evalModalApprovedAt">—</span>
                </div>
              </div>
            </div>
          </div>
          <hr style="border-color: #e5e7eb; opacity: 0.8;" class="my-4">

          <!-- SECTION 2: EVALUATION CRITERIA -->
          <div class="mb-4">
            <h5 class="fw-bold text-uppercase mb-3" style="font-size: 0.95rem; letter-spacing: 1px;">EVALUATION CRITERIA
            </h5>
            <div class="ps-4">
              <div class="table-responsive">
                <table class="table table-bordered align-middle mb-0" style="font-size: 0.9rem; border-color: #e5e7eb;">
                  <thead style="background-color: #f8fafc;">
                    <tr class="text-uppercase text-secondary fw-bold"
                      style="font-size: 0.75rem; letter-spacing: 0.5px;">
                      <th scope="col" style="padding: 10px 14px; width: 32%;">CRITERIA</th>
                      <th scope="col" style="padding: 10px 14px; width: 68%;">ASSESSMENT &amp; FINDINGS</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr>
                      <td style="padding: 10px 14px;" class="fw-bold text-dark">Economic Feasibility</td>
                      <td style="padding: 10px 14px;" class="text-dark" id="evalCriteriaEconomicReason">Funding and
                        implementation costs are manageable and available within municipal allocations.</td>
                    </tr>
                    <tr>
                      <td style="padding: 10px 14px;" class="fw-bold text-dark">Social Impact</td>
                      <td style="padding: 10px 14px;" class="text-dark" id="evalCriteriaSocialReason">The policy
                        provides measurable benefits to affected communities and enhances public welfare.</td>
                    </tr>
                    <tr>
                      <td style="padding: 10px 14px;" class="fw-bold text-dark">Environmental Impact</td>
                      <td style="padding: 10px 14px;" class="text-dark" id="evalCriteriaEnvReason">The policy satisfies
                        urban environmental standards and sustainability requirements.</td>
                    </tr>
                    <tr>
                      <td style="padding: 10px 14px;" class="fw-bold text-dark">Legal Compliance</td>
                      <td style="padding: 10px 14px;" class="text-dark" id="evalCriteriaLegalReason">Compliant with
                        the Local Government Code and relevant national/local statutory frameworks.</td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
          <hr style="border-color: #e5e7eb; opacity: 0.8;" class="my-4">

          <!-- SECTION 3: ANALYSIS -->
          <div class="mb-4">
            <div class="d-flex align-items-center gap-2 mb-2">
              <i class="bi bi-journal-text fs-5 text-dark"></i>
              <h5 class="fw-bold text-uppercase mb-0" style="font-size: 0.95rem; letter-spacing: 1px;">ANALYSIS</h5>
            </div>
            <div class="ps-4">
              <p class="mb-0 text-dark" id="evalModalAnalysis"
                style="font-size: 0.95rem; line-height: 1.7; text-align: justify;">
                The proposed policy measure demonstrates strong statutory alignment with municipal priorities across Economic Feasibility, Social Impact, Environmental Protection, and Legal Compliance criteria.
              </p>
            </div>
          </div>
          <hr style="border-color: #e5e7eb; opacity: 0.8;" class="my-4">

          <!-- SECTION 4: RECOMMENDATION -->
          <div class="mb-4">
            <div class="d-flex align-items-center gap-2 mb-2">
              <i class="bi bi-check-circle-fill fs-5 text-dark"></i>
              <h5 class="fw-bold text-uppercase mb-0" style="font-size: 0.95rem; letter-spacing: 1px;">RECOMMENDATION
              </h5>
            </div>
            <div class="ps-4">
              <div class="mb-2">
                <span id="evalModalRecommendationType"
                  style="display:inline-block;background:#d1fae5;color:#065f46;border:1px solid #a7f3d0;padding:3px 12px;border-radius:999px;font-size:0.8rem;font-weight:600;">Proceed
                  with Implementation</span>
              </div>
              <h6 class="fw-bold text-dark mb-2" id="evalModalRecommendationTitle"
                style="font-size: 1rem; line-height: 1.4;">
                Enact Policy with Enhanced Inter-Agency Coordination and Funding Frameworks
              </h6>
              <p class="text-dark mb-0" id="evalModalReason"
                style="font-size: 0.95rem; line-height: 1.7; text-align: justify;">
                The plan addresses a fundamental vulnerability in Manila's urban infrastructure that causes recurring
                economic losses, though its long-term success requires regional watershed integration and sustainable
                maintenance funding.
              </p>
            </div>
          </div>
          <hr style="border-color: #e5e7eb; opacity: 0.8;" class="my-4">

          <!-- SECTION 5: SUGGESTED IMPROVEMENTS -->
          <div class="mb-4">
            <div class="d-flex align-items-center gap-2 mb-2">
              <i class="bi bi-lightbulb-fill fs-5 text-dark"></i>
              <h5 class="fw-bold text-uppercase mb-0" style="font-size: 0.95rem; letter-spacing: 1px;">SUGGESTED
                IMPROVEMENTS</h5>
            </div>
            <div class="ps-4">
              <div class="text-dark" id="evalModalImprovements" style="font-size: 0.95rem; line-height: 1.7;">
                <ul class="mb-0 ps-3">
                  <li class="mb-2">Incorporate nature-based infrastructure solutions, such as bioswales and permeable
                    pavements, alongside traditional engineering upgrades.</li>
                  <li class="mb-2">Establish a formal joint task force with adjacent Metro Manila local government units
                    to address cross-boundary stormwater flow.</li>
                  <li class="mb-0">Develop a multi-year dedicated maintenance fund and real-time public asset management
                    dashboard to ensure operational longevity.</li>
                </ul>
              </div>
            </div>
          </div>

        </div>
      </div>

      <!-- Footer Actions Bar -->
      <div class="modal-footer bg-white border-top px-4 py-3 justify-content-between">
        <div class="d-flex align-items-center gap-2 text-muted" style="font-size: 0.78rem; font-family: sans-serif;">
          <i class="bi bi-bar-chart-line text-primary fs-5"></i>
          <div>
            <div class="fw-semibold text-dark">Official Impact Evaluation System</div>
            <div>Legislative Staff Portal &bull; Manila City Hall</div>
          </div>
        </div>
        <div class="d-flex align-items-center gap-2" style="font-family: sans-serif;">
          <button type="button" id="evalModalRunBtn"
            class="btn text-white rounded-3 px-3.5 py-2 fw-semibold shadow-sm border-0 d-inline-flex align-items-center justify-content-center"
            style="background: linear-gradient(135deg, #4f46e5, #7c3aed);" onclick="runPolicyEvaluationModal()">
            <i class="bi bi-play-circle-fill me-2" id="evalBtnIcon"></i><span id="evalBtnText">Evaluate Policy</span>
          </button>
        </div>
      </div>

    </div>
  </div>
</div>

<!-- Change Staff Password Modal -->
<!-- Re-Evaluate Policy Modal (Official Document Layout Form) -->
<div class="modal fade" id="reEvaluatePolicyModal" tabindex="-1" aria-labelledby="reEvaluatePolicyModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg" style="max-width: 820px;">
    <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden" style="background-color: #ffffff;">
      
      <!-- Header Close Button -->
      <div class="modal-header border-0 pb-0 justify-content-end bg-white px-4 pt-3">
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body px-4 px-md-5 pb-4 pt-0" style="max-height: 80vh; overflow-y: auto;">
        
        <!-- Document Seal Header -->
        <div class="text-center mb-4">
          <div class="d-flex align-items-center justify-content-center gap-3 mb-2">
            <img src="../assets/images/manilacityhall.svg" alt="Manila Seal"
              style="width: 70px; height: 70px; object-fit: contain;">
            <div>
              <h4 class="fw-bold mb-0 text-uppercase" style="letter-spacing: 2px; font-size: 1.35rem; color: #000;">
                MANILA CITY HALL</h4>
              <div class="fw-bold text-uppercase text-secondary" style="font-size: 0.85rem; letter-spacing: 1.5px;">
                OFFICE OF THE CITY COUNCIL</div>
            </div>
          </div>
          <h2 class="fw-bold text-dark mt-3 mb-2" style="font-size: 1.65rem;" id="reEvaluatePolicyModalLabel">Evaluation Revision Form</h2>
          <div class="d-flex align-items-center justify-content-center gap-2">
            <div style="height: 1px; width: 100px; background-color: #333;"></div>
            <i class="bi bi-pencil-square fs-5 text-dark"></i>
            <div style="height: 1px; width: 100px; background-color: #333;"></div>
          </div>
        </div>

        <!-- Version Notice Banner -->
        <div class="d-flex flex-wrap align-items-center justify-content-between p-2.5 px-3 rounded-3 mb-4 border" style="background: #f0fdf4; border-color: #bbf7d0 !important;">
          <div class="d-flex align-items-center gap-2 text-success small fw-medium">
            <i class="bi bi-shield-check fs-5"></i>
            <span><strong>Version History Protected:</strong> Submitting creates a brand-new evaluation version. Previous versions remain archived.</span>
          </div>
          <span class="badge bg-success text-white px-2.5 py-1 small fw-bold" id="reEvalVersionBadge">New Revision</span>
        </div>

        <form id="reEvaluatePolicyForm" onsubmit="submitNewEvaluationVersion(event)">
          <input type="hidden" id="reEvalPolicyId" name="policy_id" value="">
          
          <!-- SECTION 1: EVALUATION INFORMATION -->
          <div class="mb-4">
            <div class="d-flex align-items-center gap-2 mb-3">
              <i class="bi bi-file-earmark-text fs-5 text-dark"></i>
              <h5 class="fw-bold text-uppercase mb-0" style="font-size: 0.95rem; letter-spacing: 1px;">EVALUATION INFORMATION</h5>
            </div>
            <div class="ps-4" style="font-size: 0.95rem; line-height: 1.8;">
              <div class="row mb-2 align-items-center">
                <div class="col-4 col-sm-3 fw-bold text-dark">Policy Title</div>
                <div class="col-1 text-center">:</div>
                <div class="col-7 col-sm-8">
                  <div class="p-2 px-3 rounded-3 fw-semibold text-dark border bg-light" id="reEvalPolicyTitleDisplay" style="font-size: 0.95rem;">—</div>
                </div>
              </div>
              <div class="row mb-1 align-items-center">
                <div class="col-4 col-sm-3 fw-bold text-dark">Revision Mode</div>
                <div class="col-1 text-center">:</div>
                <div class="col-7 col-sm-8 text-dark">
                  <span class="badge bg-primary px-2.5 py-1"><i class="bi bi-arrow-repeat me-1"></i>New Evaluation Version</span>
                </div>
              </div>
            </div>
          </div>
          <hr style="border-color: #e5e7eb; opacity: 0.8;" class="my-4">

          <!-- SECTION 2: EVALUATION CRITERIA -->
          <div class="mb-4">
            <div class="d-flex align-items-center justify-content-between mb-3">
              <div class="d-flex align-items-center gap-2">
                <i class="bi bi-clipboard-data fs-5 text-dark"></i>
                <h5 class="fw-bold text-uppercase mb-0" style="font-size: 0.95rem; letter-spacing: 1px;">EVALUATION CRITERIA</h5>
              </div>
              <button type="button" class="btn btn-sm rounded-pill px-3 fw-semibold text-white shadow-sm d-inline-flex align-items-center gap-1.5"
                style="background: linear-gradient(135deg, #4f46e5, #7c3aed); border: none; font-size: 0.8rem; padding-top: 6px; padding-bottom: 6px;"
                onclick="generateAiAssistedReEvaluation()">
                <i class="bi bi-stars"></i><span>Re-generate with AI</span>
              </button>
            </div>
            <div class="ps-4">
              <div class="table-responsive">
                <table class="table table-bordered align-middle mb-0" style="font-size: 0.9rem; border-color: #e5e7eb;">
                  <thead style="background-color: #f8fafc;">
                    <tr class="text-uppercase text-secondary fw-bold" style="font-size: 0.75rem; letter-spacing: 0.5px;">
                      <th scope="col" style="padding: 10px 14px; width: 30%;">CRITERIA</th>
                      <th scope="col" style="padding: 10px 14px; width: 70%;">ASSESSMENT &amp; FINDINGS</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr>
                      <td style="padding: 12px 14px;" class="fw-bold text-dark align-top">
                        <div class="d-flex align-items-center gap-1.5 mb-1">
                          <i class="bi bi-cash-coin text-success"></i>
                          <span>Economic Feasibility</span>
                        </div>
                        <small class="text-muted fw-normal d-block" style="font-size: 0.75rem;">Budget &amp; fiscal viability</small>
                      </td>
                      <td style="padding: 8px 12px;">
                        <textarea class="form-control border-0 bg-light-subtle rounded-2 p-2" id="reEvalEconomicReason" rows="2" required placeholder="Fiscal and budgetary viability..." style="font-size: 0.9rem; line-height: 1.5; resize: vertical;"></textarea>
                      </td>
                    </tr>
                    <tr>
                      <td style="padding: 12px 14px;" class="fw-bold text-dark align-top">
                        <div class="d-flex align-items-center gap-1.5 mb-1">
                          <i class="bi bi-people text-primary"></i>
                          <span>Social Impact</span>
                        </div>
                        <small class="text-muted fw-normal d-block" style="font-size: 0.75rem;">Public welfare &amp; safety</small>
                      </td>
                      <td style="padding: 8px 12px;">
                        <textarea class="form-control border-0 bg-light-subtle rounded-2 p-2" id="reEvalSocialReason" rows="2" required placeholder="Community welfare, health, safety benefits..." style="font-size: 0.9rem; line-height: 1.5; resize: vertical;"></textarea>
                      </td>
                    </tr>
                    <tr>
                      <td style="padding: 12px 14px;" class="fw-bold text-dark align-top">
                        <div class="d-flex align-items-center gap-1.5 mb-1">
                          <i class="bi bi-tree text-success"></i>
                          <span>Environmental Impact</span>
                        </div>
                        <small class="text-muted fw-normal d-block" style="font-size: 0.75rem;">Ecological sustainability</small>
                      </td>
                      <td style="padding: 8px 12px;">
                        <textarea class="form-control border-0 bg-light-subtle rounded-2 p-2" id="reEvalEnvReason" rows="2" required placeholder="Ecological and urban sustainability alignment..." style="font-size: 0.9rem; line-height: 1.5; resize: vertical;"></textarea>
                      </td>
                    </tr>
                    <tr>
                      <td style="padding: 12px 14px;" class="fw-bold text-dark align-top">
                        <div class="d-flex align-items-center gap-1.5 mb-1">
                          <i class="bi bi-journal-check text-warning"></i>
                          <span>Legal Compliance</span>
                        </div>
                        <small class="text-muted fw-normal d-block" style="font-size: 0.75rem;">Statutory &amp; code alignment</small>
                      </td>
                      <td style="padding: 8px 12px;">
                        <textarea class="form-control border-0 bg-light-subtle rounded-2 p-2" id="reEvalLegalReason" rows="2" required placeholder="Statutory alignment with laws and ordinances..." style="font-size: 0.9rem; line-height: 1.5; resize: vertical;"></textarea>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
          <hr style="border-color: #e5e7eb; opacity: 0.8;" class="my-4">

          <!-- SECTION 3: ANALYSIS -->
          <div class="mb-4">
            <div class="d-flex align-items-center gap-2 mb-3">
              <i class="bi bi-cpu-fill fs-5 text-dark"></i>
              <h5 class="fw-bold text-uppercase mb-0" style="font-size: 0.95rem; letter-spacing: 1px;">ANALYSIS</h5>
            </div>
            <div class="ps-4">
              <label class="form-label fw-bold text-secondary text-uppercase mb-1" style="font-size: 0.78rem; letter-spacing: 0.5px;">Overall Policy Analysis</label>
              <textarea class="form-control rounded-3 p-3 text-dark border bg-light-subtle" id="reEvalAnalysis" rows="3" required placeholder="Comprehensive policy evaluation and synthesis..." style="font-size: 0.95rem; line-height: 1.7;"></textarea>
            </div>
          </div>
          <hr style="border-color: #e5e7eb; opacity: 0.8;" class="my-4">

          <!-- SECTION 4: RECOMMENDATION -->
          <div class="mb-4">
            <div class="d-flex align-items-center gap-2 mb-3">
              <i class="bi bi-check-circle-fill fs-5 text-dark"></i>
              <h5 class="fw-bold text-uppercase mb-0" style="font-size: 0.95rem; letter-spacing: 1px;">RECOMMENDATION</h5>
            </div>
            <div class="ps-4">
              <div class="mb-3">
                <label class="form-label fw-bold text-secondary text-uppercase mb-1" style="font-size: 0.78rem; letter-spacing: 0.5px;">Official Recommendation Title</label>
                <input type="text" class="form-control rounded-3 fw-bold text-dark p-2.5 border bg-light-subtle" id="reEvalRecommendationTitle" required placeholder="e.g., Approve & Proceed to Full Implementation" style="font-size: 0.95rem;">
              </div>
              <div>
                <label class="form-label fw-bold text-secondary text-uppercase mb-1" style="font-size: 0.78rem; letter-spacing: 0.5px;">Recommendation Summary / Rationale</label>
                <textarea class="form-control rounded-3 p-3 text-dark border bg-light-subtle" id="reEvalReason" rows="2" required placeholder="Strategic rationale for the recommendation..." style="font-size: 0.95rem; line-height: 1.7;"></textarea>
              </div>
            </div>
          </div>
          <hr style="border-color: #e5e7eb; opacity: 0.8;" class="my-4">

          <!-- SECTION 5: SUGGESTED IMPROVEMENTS -->
          <div class="mb-4">
            <div class="d-flex align-items-center gap-2 mb-3">
              <i class="bi bi-lightbulb-fill fs-5 text-dark"></i>
              <h5 class="fw-bold text-uppercase mb-0" style="font-size: 0.95rem; letter-spacing: 1px;">SUGGESTED IMPROVEMENTS</h5>
            </div>
            <div class="ps-4">
              <label class="form-label fw-bold text-secondary text-uppercase mb-1" style="font-size: 0.78rem; letter-spacing: 0.5px;">Action Items &amp; Strategic Improvements (One per line)</label>
              <textarea class="form-control rounded-3 p-3 text-dark border bg-light-subtle font-monospace" id="reEvalImprovements" rows="3" placeholder="Establish quarterly district performance monitoring reviews&#10;Deploy digital asset management dashboards across participating departments&#10;Conduct community feedback surveys after 6 months of ordinance rollout" style="font-size: 0.88rem; line-height: 1.7;"></textarea>
            </div>
          </div>

          <!-- Footer Actions Bar -->
          <div class="modal-footer bg-white border-top px-0 py-3 mt-4 justify-content-between" style="border-bottom-left-radius: inherit; border-bottom-right-radius: inherit;">
            <div class="d-flex align-items-center gap-2 text-muted" style="font-size: 0.78rem; font-family: sans-serif;">
              <i class="bi bi-bar-chart-line text-primary fs-5"></i>
              <div>
                <div class="fw-semibold text-dark">Official Impact Evaluation System</div>
                <div>Legislative Administration System &bull; Manila City Hall</div>
              </div>
            </div>
            <div class="d-flex align-items-center gap-2" style="font-family: sans-serif;">
              <button type="button" class="btn btn-light rounded-3 px-3.5 py-2 fw-semibold border" data-bs-dismiss="modal">Cancel</button>
              <button type="submit" id="reEvalSubmitBtn" class="btn text-white rounded-3 px-4 py-2 fw-semibold shadow-sm border-0 d-inline-flex align-items-center gap-1.5"
                style="background: linear-gradient(135deg, #4f46e5, #7c3aed);">
                <i class="bi bi-save2 me-1"></i><span>Save as New Version</span>
              </button>
            </div>
          </div>

        </form>

      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="changeStaffPasswordModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" style="max-width: 500px;">
    <div class="modal-content border-0 shadow rounded-4">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title fw-bold text-dark"><i class="bi bi-key-fill text-warning me-2"></i> Change Password</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form
        onsubmit="alert('Staff account password updated successfully!'); bootstrap.Modal.getInstance(this.closest('.modal')).hide(); return false;">
        <div class="modal-body p-4">
          <div class="row g-3">
            <div class="col-md-12">
              <label class="form-label fw-semibold small">Current Password</label>
              <input type="password" class="form-control" placeholder="Enter current password" required>
            </div>
            <div class="col-md-12">
              <label class="form-label fw-semibold small">New Password</label>
              <input type="password" class="form-control" placeholder="Enter new strong password" required>
            </div>
            <div class="col-md-12">
              <label class="form-label fw-semibold small">Confirm New Password</label>
              <input type="password" class="form-control" placeholder="Re-enter new password" required>
            </div>
          </div>
        </div>
        <div class="modal-footer border-0 pt-0">
          <button type="button" class="btn btn-secondary rounded-3" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-warning fw-semibold rounded-3 px-4">Update Password</button>
        </div>
      </form>
    </div>
  </div>
</div>