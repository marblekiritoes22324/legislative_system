<?php
// Calculate counts based on $all_policies (connected to policy records)
$total_datasets = !empty($all_policies) ? count($all_policies) : 28;

// Get unique categories and department counts
$categories_map = [];
$departments_map = [];

if (!empty($all_policies)) {
  foreach ($all_policies as $p) {
    $cat = !empty($p['category']) ? $p['category'] : 'Other';
    $dept = !empty($p['department']) ? $p['department'] : 'General';

    $categories_map[$cat] = ($categories_map[$cat] ?? 0) + 1;
    $departments_map[$dept] = ($departments_map[$dept] ?? 0) + 1;
  }
} else {
  // Default fallback counts matching screenshot
  $categories_map = [
    'Environment' => 12,
    'Health' => 8,
    'Transportation' => 6,
    'Public Safety' => 4,
    'Social Services' => 3
  ];
  $departments_map = [
    'Environmental Management Office' => 12,
    'City Planning Office' => 8,
    'Engineering Office' => 6,
    'Health Department' => 4,
    'Public Safety Department' => 3,
    'Social Welfare Department' => 3
  ];
}

$num_categories = count($categories_map);
$num_departments = count($departments_map);
?>

<section id="dataCollectionSection" class="content-section <?= ($active_section ?? 'adminDashboardSection') !== 'dataCollectionSection' ? 'd-none' : '' ?>">

  <!-- Header Title -->
  <div class="mb-4">
    <h2 class="h3 fw-bold text-dark mb-1 d-flex align-items-center gap-2">
      <i class="bi bi-database-fill text-primary fs-4"></i> Data Collection
    </h2>
    <p class="text-muted mb-0">View and monitor research datasets collected for policy analysis.</p>
  </div>

  <!-- 3 Summary Stat Cards -->
  <div class="row g-3 mb-4">
    <!-- Total Datasets -->
    <div class="col-12 col-md-4">
      <div class="card border-0 shadow-sm rounded-4 p-4 bg-white h-100 border-top border-4 border-primary">
        <div class="d-flex align-items-center gap-3">
          <div class="rounded-4 p-3 d-flex align-items-center justify-content-center"
            style="background-color: #eff6ff; color: #2563eb; width: 60px; height: 60px;">
            <i class="bi bi-database-fill fs-3"></i>
          </div>
          <div>
            <div class="h2 fw-bold text-dark mb-0" id="resTotalDatasets"><?= $total_datasets ?></div>
            <div class="fw-bold text-dark small" style="font-size: 0.95rem;">Total Datasets</div>
            <div class="text-muted small">All collected data</div>
          </div>
        </div>
      </div>
    </div>

    <!-- Research Categories -->
    <div class="col-12 col-md-4">
      <div class="card border-0 shadow-sm rounded-4 p-4 bg-white h-100 border-top border-4 border-success">
        <div class="d-flex align-items-center gap-3">
          <div class="rounded-4 p-3 d-flex align-items-center justify-content-center"
            style="background-color: #dcfce7; color: #16a34a; width: 60px; height: 60px;">
            <i class="bi bi-folder-fill fs-3"></i>
          </div>
          <div>
            <div class="h2 fw-bold text-dark mb-0" id="resTotalCategories"><?= $num_categories ?></div>
            <div class="fw-bold text-dark small" style="font-size: 0.95rem;">Data Categories</div>
            <div class="text-muted small">All categories</div>
          </div>
        </div>
      </div>
    </div>

    <!-- Departments -->
    <div class="col-12 col-md-4">
      <div class="card border-0 shadow-sm rounded-4 p-4 bg-white h-100 border-top border-4"
        style="border-top-color: #9333ea !important;">
        <div class="d-flex align-items-center gap-3">
          <div class="rounded-4 p-3 d-flex align-items-center justify-content-center"
            style="background-color: #f3e8ff; color: #9333ea; width: 60px; height: 60px;">
            <i class="bi bi-building-fill fs-3"></i>
          </div>
          <div>
            <div class="h2 fw-bold text-dark mb-0" id="resTotalDepartments"><?= $num_departments ?></div>
            <div class="fw-bold text-dark small" style="font-size: 0.95rem;">Departments</div>
            <div class="text-muted small">Data contributors</div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Bar Graph: Data Collection by Category -->
  <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-white">
    <h3 class="h6 fw-bold text-dark mb-4" style="font-size: 1rem;">Data Collection by Category</h3>
    <div style="height: 250px; position: relative;">
      <canvas id="researchCategoryChart"></canvas>
    </div>
  </div>

  <!-- Collected Research Data Table -->
  <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">

    <!-- Table Title, Filters & Actions Header -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4 pb-2 border-bottom border-light-subtle">
      <div class="d-flex align-items-center gap-2">
        <h3 class="h6 fw-bold text-dark mb-0" style="font-size: 1.1rem; letter-spacing: -0.2px;">Collected Data</h3>
        <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-20 rounded-pill px-2.5 py-1 fw-semibold ms-1" style="font-size:0.75rem;" id="resTableBadge"><?= !empty($all_policies) ? count($all_policies) : 4 ?> Datasets</span>
      </div>
      
      <div class="d-flex flex-wrap align-items-center gap-2">
        <!-- Category Filter Dropdown -->
        <select id="researchCategoryFilter" class="form-select form-select-sm rounded-3 border-light-subtle py-2 text-secondary fw-medium shadow-2xs" style="width: 190px; font-size: 0.84rem;" onchange="filterResearchDataTable()">
          <option value="">All Categories</option>
          <option value="Health">Health & Sanitation</option>
          <option value="Civil Registry">Civil Registry & Public</option>
          <option value="Education">Education & Employment</option>
          <option value="Social Welfare">Social Welfare & Community</option>
          <option value="Infrastructure">Infrastructure & Environment</option>
          <option value="Environment">Environment</option>
          <option value="Transportation">Transportation</option>
          <option value="Public Safety">Public Safety</option>
          <option value="Other">Other</option>
        </select>

        <!-- Search Bar -->
        <div class="position-relative" style="width: 250px;">
          <input type="search" id="researchDataSearch" class="form-control form-control-sm pe-4 ps-3 py-2 rounded-3 border-light-subtle shadow-2xs"
            placeholder="Search datasets..." onkeyup="filterResearchDataTable()" style="font-size: 0.84rem;">
          <i class="bi bi-search position-absolute top-50 end-0 translate-middle-y me-2.5 text-muted small"></i>
        </div>
      </div>
    </div>

    <!-- Table Responsive Wrapper -->
    <div class="table-responsive border rounded-4 overflow-hidden mb-3">
      <table class="table table-hover align-middle mb-0" style="font-size: 0.88rem;" id="researchDataTable">
        <thead style="background-color: #f8fafc; border-bottom: 2px solid #e2e8f0;">
          <tr>
            <th class="py-3.5 px-3 text-uppercase text-dark fw-bold" style="font-size: 0.88rem; letter-spacing: 0.03em; color: #000000 !important;">Dataset Name</th>
            <th class="py-3.5 text-uppercase text-dark fw-bold" style="font-size: 0.88rem; letter-spacing: 0.03em; color: #000000 !important;">Category</th>
            <th class="py-3.5 text-uppercase text-dark fw-bold" style="font-size: 0.88rem; letter-spacing: 0.03em; color: #000000 !important;">Department</th>
            <th class="py-3.5 text-uppercase text-dark fw-bold" style="font-size: 0.88rem; letter-spacing: 0.03em; color: #000000 !important;">Date Uploaded</th>
            <th class="py-3.5 text-uppercase text-dark fw-bold" style="font-size: 0.88rem; letter-spacing: 0.03em; color: #000000 !important;">Status</th>
            <th class="py-3.5 text-center text-uppercase text-dark fw-bold" style="width: 100px; font-size: 0.88rem; letter-spacing: 0.03em; color: #000000 !important;">Action</th>
          </tr>
        </thead>
        <tbody id="researchDataTableBody">
          <?php if (!empty($all_policies)): ?>
            <?php foreach ($all_policies as $rp):
              $datasetName = htmlspecialchars($rp['title']);
              $category = htmlspecialchars($rp['category'] ?? 'Environment');
              $dept = htmlspecialchars($rp['department'] ?? 'Environmental Management Office');
              $dateUploaded = !empty($rp['created_at']) ? date('M d, Y', strtotime($rp['created_at'])) : 'Aug 03, 2026';
              $status = htmlspecialchars($rp['status'] ?? 'Completed');
              $isCompleted = ($status === 'Completed' || $status === 'Published' || $status === 'Evaluated');
              ?>
              <tr class="res-data-row" data-search="<?= strtolower($datasetName . ' ' . $category . ' ' . $dept) ?>" data-category="<?= strtolower($category) ?>">
                <td class="px-3 py-3">
                  <div class="d-flex align-items-center gap-2.5">
                    <div class="rounded-3 p-2 d-flex align-items-center justify-content-center bg-primary bg-opacity-10 text-primary flex-shrink-0" style="width: 36px; height: 36px;">
                      <i class="bi bi-file-earmark-bar-graph-fill fs-6"></i>
                    </div>
                    <div>
                      <span class="fw-bold text-dark d-block lh-sm"><?= $datasetName ?></span>
                      <small class="text-muted" style="font-size:0.75rem;">Research Dataset</small>
                    </div>
                  </div>
                </td>
                <td class="py-3">
                  <span class="badge rounded-pill text-white fw-bold px-3 py-1.5 shadow-2xs" style="background-color: #0d6efd !important; font-size: 0.78rem;">
                    <?= $category ?>
                  </span>
                </td>
                <td class="py-3 text-secondary fw-medium">
                  <i class="bi bi-building me-1.5 text-muted opacity-75"></i><?= $dept ?>
                </td>
                <td class="py-3 text-secondary fw-medium">
                  <i class="bi bi-calendar3 me-1.5 text-muted opacity-75"></i><?= $dateUploaded ?>
                </td>
                <td class="py-3">
                  <?php if ($isCompleted): ?>
                    <span class="badge rounded-pill text-white fw-bold px-3 py-1.5 shadow-2xs" style="background-color: #198754 !important; font-size: 0.78rem;">
                      Completed
                    </span>
                  <?php else: ?>
                    <span class="badge rounded-pill text-dark fw-bold px-3 py-1.5 shadow-2xs" style="background-color: #ffc107 !important; color: #000000 !important; font-size: 0.78rem;">
                      Draft
                    </span>
                  <?php endif; ?>
                </td>
                <td class="py-3 text-center">
                  <?php if (!empty($rp['file_path'])): ?>
                    <a href="../assets/uploads/policies/<?= htmlspecialchars($rp['file_path']) ?>" target="_blank"
                      class="btn btn-sm btn-light border rounded-3 px-3 py-1.5 d-inline-flex align-items-center gap-1.5 fw-semibold text-primary shadow-2xs"
                      style="font-size: 0.8rem; background-color: #eff6ff; border-color: #bfdbfe !important;">
                      <i class="bi bi-eye-fill text-primary"></i> View
                    </a>
                  <?php else: ?>
                    <button
                      class="btn btn-sm btn-light border rounded-3 px-3 py-1.5 d-inline-flex align-items-center gap-1.5 fw-semibold text-primary shadow-2xs"
                      style="font-size: 0.8rem; background-color: #eff6ff; border-color: #bfdbfe !important;" onclick="showSection('policyResearchSection')">
                      <i class="bi bi-eye-fill text-primary"></i> View
                    </button>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <!-- Default Fallback Records -->
            <tr class="res-data-row" data-search="plastic reduction study environment environmental management office" data-category="infrastructure, traffic and environment">
              <td class="px-3 py-3">
                <div class="d-flex align-items-center gap-2.5">
                  <div class="rounded-3 p-2 d-flex align-items-center justify-content-center bg-success bg-opacity-10 text-success flex-shrink-0" style="width: 36px; height: 36px;">
                    <i class="bi bi-file-earmark-bar-graph-fill fs-6"></i>
                  </div>
                  <div>
                    <span class="fw-bold text-dark d-block lh-sm">Plastic Reduction Study</span>
                    <small class="text-muted" style="font-size:0.75rem;">Research Dataset</small>
                  </div>
                </div>
              </td>
              <td class="py-3">
                <span class="badge rounded-pill text-white fw-bold px-3 py-1.5 shadow-2xs" style="background-color: #0d6efd !important; font-size: 0.78rem;">
                  Infrastructure, Traffic & Environment
                </span>
              </td>
              <td class="py-3 text-secondary fw-medium"><i class="bi bi-building me-1.5 text-muted opacity-75"></i>Environmental Management Office</td>
              <td class="py-3 text-secondary fw-medium"><i class="bi bi-calendar3 me-1.5 text-muted opacity-75"></i>Aug. 3, 2026</td>
              <td class="py-3">
                <span class="badge rounded-pill text-white fw-bold px-3 py-1.5 shadow-2xs" style="background-color: #198754 !important; font-size: 0.78rem;">
                  Completed
                </span>
              </td>
              <td class="py-3 text-center">
                <button class="btn btn-sm btn-light border rounded-3 px-3 py-1.5 d-inline-flex align-items-center gap-1.5 fw-semibold text-primary shadow-2xs"
                  style="font-size: 0.8rem; background-color: #eff6ff; border-color: #bfdbfe !important;" onclick="showSection('policyResearchSection')">
                  <i class="bi bi-eye-fill text-primary"></i> View
                </button>
              </td>
            </tr>

            <tr class="res-data-row" data-search="traffic congestion study transportation city planning office" data-category="infrastructure, traffic and environment">
              <td class="px-3 py-3">
                <div class="d-flex align-items-center gap-2.5">
                  <div class="rounded-3 p-2 d-flex align-items-center justify-content-center bg-primary bg-opacity-10 text-primary flex-shrink-0" style="width: 36px; height: 36px;">
                    <i class="bi bi-file-earmark-bar-graph-fill fs-6"></i>
                  </div>
                  <div>
                    <span class="fw-bold text-dark d-block lh-sm">Traffic Congestion Study</span>
                    <small class="text-muted" style="font-size:0.75rem;">Research Dataset</small>
                  </div>
                </div>
              </td>
              <td class="py-3">
                <span class="badge rounded-pill text-white fw-bold px-3 py-1.5 shadow-2xs" style="background-color: #0d6efd !important; font-size: 0.78rem;">
                  Transportation
                </span>
              </td>
              <td class="py-3 text-secondary fw-medium"><i class="bi bi-building me-1.5 text-muted opacity-75"></i>City Planning Office</td>
              <td class="py-3 text-secondary fw-medium"><i class="bi bi-calendar3 me-1.5 text-muted opacity-75"></i>Aug. 2, 2026</td>
              <td class="py-3">
                <span class="badge rounded-pill text-dark fw-bold px-3 py-1.5 shadow-2xs" style="background-color: #ffc107 !important; color: #000000 !important; font-size: 0.78rem;">
                  Draft
                </span>
              </td>
              <td class="py-3 text-center">
                <button class="btn btn-sm btn-light border rounded-3 px-3 py-1.5 d-inline-flex align-items-center gap-1.5 fw-semibold text-primary shadow-2xs"
                  style="font-size: 0.8rem; background-color: #eff6ff; border-color: #bfdbfe !important;" onclick="showSection('policyResearchSection')">
                  <i class="bi bi-eye-fill text-primary"></i> View
                </button>
              </td>
            </tr>

            <tr class="res-data-row" data-search="flood risk assessment public safety engineering office" data-category="infrastructure, traffic and environment">
              <td class="px-3 py-3">
                <div class="d-flex align-items-center gap-2.5">
                  <div class="rounded-3 p-2 d-flex align-items-center justify-content-center bg-danger bg-opacity-10 text-danger flex-shrink-0" style="width: 36px; height: 36px;">
                    <i class="bi bi-file-earmark-bar-graph-fill fs-6"></i>
                  </div>
                  <div>
                    <span class="fw-bold text-dark d-block lh-sm">Flood Risk Assessment</span>
                    <small class="text-muted" style="font-size:0.75rem;">Research Dataset</small>
                  </div>
                </div>
              </td>
              <td class="py-3">
                <span class="badge rounded-pill text-white fw-bold px-3 py-1.5 shadow-2xs" style="background-color: #0d6efd !important; font-size: 0.78rem;">
                  Infrastructure, Traffic & Environment
                </span>
              </td>
              <td class="py-3 text-secondary fw-medium"><i class="bi bi-building me-1.5 text-muted opacity-75"></i>Engineering Office</td>
              <td class="py-3 text-secondary fw-medium"><i class="bi bi-calendar3 me-1.5 text-muted opacity-75"></i>Aug. 1, 2026</td>
              <td class="py-3">
                <span class="badge rounded-pill text-white fw-bold px-3 py-1.5 shadow-2xs" style="background-color: #198754 !important; font-size: 0.78rem;">
                  Completed
                </span>
              </td>
              <td class="py-3 text-center">
                <button class="btn btn-sm btn-light border rounded-3 px-3 py-1.5 d-inline-flex align-items-center gap-1.5 fw-semibold text-primary shadow-2xs"
                  style="font-size: 0.8rem; background-color: #eff6ff; border-color: #bfdbfe !important;" onclick="showSection('policyResearchSection')">
                  <i class="bi bi-eye-fill text-primary"></i> View
                </button>
              </td>
            </tr>

            <tr class="res-data-row" data-search="mental health survey health health department" data-category="health and sanitation">
              <td class="px-3 py-3">
                <div class="d-flex align-items-center gap-2.5">
                  <div class="rounded-3 p-2 d-flex align-items-center justify-content-center bg-purple bg-opacity-10 text-purple flex-shrink-0" style="width: 36px; height: 36px; background-color: #f3e8ff; color: #6b21a8;">
                    <i class="bi bi-file-earmark-bar-graph-fill fs-6"></i>
                  </div>
                  <div>
                    <span class="fw-bold text-dark d-block lh-sm">Mental Health Survey</span>
                    <small class="text-muted" style="font-size:0.75rem;">Research Dataset</small>
                  </div>
                </div>
              </td>
              <td class="py-3">
                <span class="badge rounded-pill text-white fw-bold px-3 py-1.5 shadow-2xs" style="background-color: #0d6efd !important; font-size: 0.78rem;">
                  Health & Sanitation
                </span>
              </td>
              <td class="py-3 text-secondary fw-medium"><i class="bi bi-building me-1.5 text-muted opacity-75"></i>Health Department</td>
              <td class="py-3 text-secondary fw-medium"><i class="bi bi-calendar3 me-1.5 text-muted opacity-75"></i>Jul. 31, 2026</td>
              <td class="py-3">
                <span class="badge rounded-pill text-white fw-bold px-3 py-1.5 shadow-2xs" style="background-color: #198754 !important; font-size: 0.78rem;">
                  Completed
                </span>
              </td>
              <td class="py-3 text-center">
                <button class="btn btn-sm btn-light border rounded-3 px-3 py-1.5 d-inline-flex align-items-center gap-1.5 fw-semibold text-primary shadow-2xs"
                  style="font-size: 0.8rem; background-color: #eff6ff; border-color: #bfdbfe !important;" onclick="showSection('policyResearchSection')">
                  <i class="bi bi-eye-fill text-primary"></i> View
                </button>
              </td>
            </tr>
          <?php endif; ?>

          <!-- Empty State Row -->
          <tr id="noResearchDataRow" style="display: none;">
            <td colspan="6" class="text-center py-5 text-muted">
              <i class="bi bi-search fs-2 d-block mb-2 text-secondary opacity-50"></i>
              <div class="fw-semibold text-dark mb-1">No research datasets found</div>
              <small class="text-muted">Try adjusting your search term or category filter.</small>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Pagination & Stats Footer -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 pt-2">
      <div class="text-muted small fw-medium" id="researchDataCount">
        Showing 1 to <?= !empty($all_policies) ? count($all_policies) : 4 ?> of
        <?= !empty($all_policies) ? count($all_policies) : 4 ?> entries
      </div>
      <div class="d-flex align-items-center gap-1">
        <button class="btn btn-sm btn-light border rounded-2 px-2.5 py-1" disabled><i class="bi bi-chevron-left"></i></button>
        <button class="btn btn-sm btn-primary rounded-2 px-3 py-1 fw-bold">1</button>
        <button class="btn btn-sm btn-light border rounded-2 px-2.5 py-1"><i class="bi bi-chevron-right"></i></button>
      </div>
    </div>

  </div>

</section>

<script>
  // Research Data Chart Initialization & Filtering
  var researchCategoryChart = null;

  function renderResearchCategoryChart() {
    var ctx = document.getElementById('researchCategoryChart');
    if (!ctx) return;

    var categories = <?= json_encode(array_keys($categories_map)) ?>;
    var counts = <?= json_encode(array_values($categories_map)) ?>;

    if (researchCategoryChart) {
      researchCategoryChart.destroy();
    }

    researchCategoryChart = new Chart(ctx.getContext('2d'), {
      type: 'bar',
      data: {
        labels: categories,
        datasets: [{
          label: 'Datasets',
          data: counts,
          backgroundColor: '#2563eb',
          hoverBackgroundColor: '#1d4ed8',
          borderRadius: 8,
          barThickness: 50
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { display: false },
          tooltip: {
            backgroundColor: '#0f172a',
            titleFont: { size: 13, weight: 'bold' },
            bodyFont: { size: 12 },
            padding: 10,
            cornerRadius: 8
          }
        },
        scales: {
          y: {
            beginAtZero: true,
            grid: { color: '#f1f5f9' },
            ticks: { color: '#64748b', stepSize: 5 }
          },
          x: {
            grid: { display: false },
            ticks: { color: '#334155', font: { weight: '600' } }
          }
        }
      }
    });
  }

  function filterResearchDataTable() {
    var search = (document.getElementById('researchDataSearch').value || '').toLowerCase().trim();
    var catFilter = (document.getElementById('researchCategoryFilter').value || '').toLowerCase().trim();
    var rows = document.querySelectorAll('#researchDataTableBody .res-data-row');

    var visibleCount = 0;
    rows.forEach(function (row) {
      var rowSearch = (row.getAttribute('data-search') || '').toLowerCase();
      var rowCat = (row.getAttribute('data-category') || rowSearch).toLowerCase();

      var matchesSearch = !search || rowSearch.indexOf(search) !== -1;
      var matchesCat = !catFilter || rowCat.indexOf(catFilter) !== -1;

      if (matchesSearch && matchesCat) {
        row.style.display = '';
        visibleCount++;
      } else {
        row.style.display = 'none';
      }
    });

    var countEl = document.getElementById('researchDataCount');
    if (countEl) {
      countEl.textContent = 'Showing 1 to ' + visibleCount + ' of ' + rows.length + ' entries';
    }

    var badgeEl = document.getElementById('resTableBadge');
    if (badgeEl) {
      badgeEl.textContent = visibleCount + ' Datasets';
    }

    var noRow = document.getElementById('noResearchDataRow');
    if (noRow) {
      noRow.style.display = (visibleCount === 0) ? '' : 'none';
    }
  }

  // Render chart on page load or section show
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', renderResearchCategoryChart);
  } else {
    renderResearchCategoryChart();
  }
</script>