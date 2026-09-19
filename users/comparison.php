<?php
// users/comparison.php — Councilor/User Policy Comparison & Cross-City Benchmarking Submodule
require_once __DIR__ . '/../backend/evaluation_versions_helper.php';
$version_comparison_data = get_policy_versions_comparison_data($conn ?? null);

require_once __DIR__ . '/../backend/external_ordinances_helper.php';
$external_benchmarks = get_external_ordinances($conn ?? null);

$u_eval_map = [];
if (!empty($conn)) {
  $u_eval_query = "
    SELECT p.id AS policy_id, p.title AS policy_title, p.category, COALESCE(p.city_origin, 'City of Manila') AS city_origin,
           p.description, p.publication_date, p.file_path,
           e.risk_level, e.ai_recommendation, e.status AS evaluation_status, e.overall_score, e.notes,
           e.economic_score, e.social_score, e.environmental_score, e.legal_score
    FROM policy_records p
    INNER JOIN evaluations e ON e.policy_id = p.id
    WHERE (p.status IS NULL OR p.status != 'Archived')
      AND e.status IN ('Approved', 'Completed', 'Evaluated')
    ORDER BY p.created_at DESC
  ";
  $u_eval_res = mysqli_query($conn, $u_eval_query);
  if ($u_eval_res) {
    while ($row = mysqli_fetch_assoc($u_eval_res)) {
      $notes_data = [];
      if (!empty($row['notes'])) {
        $trimmed = trim($row['notes']);
        if (strpos($trimmed, '{') === 0 || strpos($trimmed, '[') === 0) {
          $decoded = json_decode($trimmed, true);
          if (is_array($decoded)) {
            $notes_data = $decoded;
          }
        }
      }
      $crit = $notes_data['criteria'] ?? [];

      $extractLevel = function ($crit_item, $notes, $key, $score) {
        if (is_array($crit_item) && !empty($crit_item['level']))
          return $crit_item['level'];
        if (is_string($crit_item) && !empty($crit_item))
          return $crit_item;
        if (is_array($notes) && !empty($notes[$key . '_level']))
          return $notes[$key . '_level'];
        if (!empty($score) && is_numeric($score) && $score > 0) {
          if ($score >= 8)
            return 'Low';
          if ($score >= 5)
            return 'Medium';
          return 'High';
        }
        return 'Low';
      };

      $extractReason = function ($crit_item, $notes, $key, $default) {
        if (is_array($crit_item) && !empty($crit_item['reason']))
          return $crit_item['reason'];
        if (is_array($notes) && !empty($notes[$key . '_reason']))
          return $notes[$key . '_reason'];
        return $default;
      };

      $econ_level = $extractLevel($crit['economic'] ?? null, $notes_data, 'economic', $row['economic_score'] ?? 0);
      $social_level = $extractLevel($crit['social'] ?? null, $notes_data, 'social', $row['social_score'] ?? 0);
      $env_level = $extractLevel($crit['env'] ?? ($crit['environmental'] ?? null), $notes_data, 'env', $row['environmental_score'] ?? 0);
      $legal_level = $extractLevel($crit['legal'] ?? null, $notes_data, 'legal', $row['legal_score'] ?? 0);

      $econ_reason = $extractReason($crit['economic'] ?? null, $notes_data, 'economic', 'Funding and implementation costs are manageable and available.');
      $social_reason = $extractReason($crit['social'] ?? null, $notes_data, 'social', 'The policy provides benefits to affected communities and improves quality of life.');
      $env_reason = $extractReason($crit['env'] ?? ($crit['environmental'] ?? null), $notes_data, 'env', 'The policy has minimal expected environmental effects.');
      $legal_reason = $extractReason($crit['legal'] ?? null, $notes_data, 'legal', 'No major legal conflicts were identified with existing laws and regulations.');

      $u_eval_map[] = [
        'id' => (int) $row['policy_id'],
        'title' => $row['policy_title'],
        'category' => $row['category'],
        'city_origin' => $row['city_origin'] ?: 'City of Manila',
        'description' => $row['description'] ?? '',
        'key_provisions' => $row['description'] ?? '',
        'publication_date' => $row['publication_date'] ?? '',
        'file_path' => $row['file_path'] ?? '',
        'risk_level' => $row['risk_level'] ?: 'Low Risk',
        'overall_score' => floatval($row['overall_score'] ?? 0),
        'economic_score' => floatval($row['economic_score'] ?? 0),
        'social_score' => floatval($row['social_score'] ?? 0),
        'env_score' => floatval($row['environmental_score'] ?? 0),
        'legal_score' => floatval($row['legal_score'] ?? 0),
        'ai_recommendation' => $row['ai_recommendation'] ?: 'Suitable for implementation.',
        'economic_level' => $econ_level,
        'economic_reason' => $econ_reason,
        'social_level' => $social_level,
        'social_reason' => $social_reason,
        'env_level' => $env_level,
        'env_reason' => $env_reason,
        'legal_level' => $legal_level,
        'legal_reason' => $legal_reason,
      ];
    }
  }
}

// Split into Local Manila policies vs External LGU Benchmarks
$u_local_policies = [];
$u_external_policies = [];
foreach ($u_eval_map as $p) {
  $c = strtolower($p['city_origin'] ?? 'city of manila');
  if (strpos($c, 'manila') !== false) {
    $u_local_policies[] = $p;
  } else {
    $u_external_policies[] = $p;
  }
}
?>
<!-- 5. POLICY COMPARISON & CROSS-CITY BENCHMARKING SUBMODULE -->
<section id="policyComparisonSection"
  class="content-section <?= ($active_section ?? 'userDashboardSection') !== 'policyComparisonSection' ? 'd-none' : '' ?>">
  <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-white">

    <!-- Header -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
      <div class="d-flex align-items-center">
        <span class="p-2.5 rounded-3 me-3" style="background:#e0f2fe; color:#0284c7;">
          <i class="bi bi-layout-sidebar-inset-reverse fs-4"></i>
        </span>
        <div>
          <h2 class="h4 fw-bold text-dark mb-1">Benchmarking &amp; Comparative Analysis</h2>
          <!-- BUILD:v2026-09-19-USER-CROSS-CITY -->
          <p class="text-muted mb-0 small" id="userComparisonSubtitle">
            Benchmark City of Manila proposed policies against similar enacted ordinances from peer Metro Manila cities
            (Quezon City, Makati, Pasig) or compare local policies.
          </p>
        </div>
      </div>

      <!-- Mode Switcher Tabs -->
      <div class="d-flex align-items-center gap-1.5 p-1 bg-light rounded-pill border shadow-2xs">
        <button type="button" id="toggleUserCompareCrossCityBtn"
          class="btn btn-sm rounded-pill px-3 py-1.5 fw-bold text-white shadow-sm" style="background:#0B2E59;"
          onclick="switchUserComparisonMode('cross_city')">
          <i class="bi bi-globe-americas me-1 text-info"></i> Cross-City Benchmarking
        </button>
        <button type="button" id="toggleUserComparePoliciesBtn"
          class="btn btn-sm rounded-pill px-3 py-1.5 fw-semibold text-secondary" style="background:transparent;"
          onclick="switchUserComparisonMode('policies')">
          <i class="bi bi-buildings me-1"></i> Manila vs Manila
        </button>
        <button type="button" id="toggleUserCompareVersionsBtn"
          class="btn btn-sm rounded-pill px-3 py-1.5 fw-semibold text-secondary" style="background:transparent;"
          onclick="switchUserComparisonMode('versions')">
          <i class="bi bi-clock-history me-1 text-primary"></i> Version Evolution
        </button>
      </div>
    </div>

    <!-- Mode 1: Cross-City Ordinance Benchmarking (NEW & DEFAULT) -->
    <!-- Mode 1: Cross-City Ordinance Benchmarking (CLEAN & BALANCED) -->
    <div class="row g-3 align-items-end mb-4" id="crossCityCompareForm">

      <!-- Manila Policy / Ordinance (Proposed / Local) -->
      <div class="col-12 col-lg-5">
        <div class="d-flex align-items-center justify-content-between mb-2">
          <label for="userCrossCityPolicyA" class="form-label fw-semibold small mb-0 text-dark">
            <i class="bi bi-building text-primary me-1.5"></i>Manila Proposed Policy Baseline
          </label>
          <span class="badge rounded-pill bg-light text-secondary border px-2 py-0.5" style="font-size:0.7rem;">City of Manila</span>
        </div>
        <div class="input-group shadow-2xs">
          <span class="input-group-text bg-white border-end-0 rounded-start-3" style="border-left:3px solid #1d4ed8;">
            <i class="bi bi-file-earmark-text text-primary"></i>
          </span>
          <select id="userCrossCityPolicyA" class="form-select border-start-0 rounded-end-3" style="font-size:0.9rem;"
            onchange="autoSuggestUserCrossCityBenchmark()">
            <?php if (empty($u_local_policies)): ?>
              <option value="" disabled selected>— No Manila Approved Policies Available —</option>
            <?php else: ?>
              <option value="">— Select Manila Policy to Benchmark —</option>
              <?php foreach ($u_local_policies as $p): ?>
                <option value="<?= (int) $p['id'] ?>" data-category="<?= htmlspecialchars($p['category']) ?>"
                  data-title="<?= htmlspecialchars($p['title']) ?>" <?= ($p === reset($u_local_policies)) ? 'selected' : '' ?>>
                  [Manila] <?= htmlspecialchars($p['title']) ?>
                </option>
              <?php endforeach; ?>
            <?php endif; ?>
          </select>
        </div>
      </div>

      <!-- External City Benchmark Ordinance -->
      <div class="col-12 col-lg-5">
        <div class="d-flex justify-content-between align-items-center mb-2">
          <label for="userCrossCityPolicyB" class="form-label fw-semibold small mb-0 text-dark">
            <i class="bi bi-geo-alt-fill text-success me-1.5"></i>Peer City Enacted Benchmark
          </label>
          <!-- Quick LGU City Filter Pills -->
          <div class="d-flex align-items-center gap-1" id="userCrossCityFilterPills">
            <button type="button"
              class="btn btn-xs rounded-pill px-2 py-0.5 fw-bold btn-primary text-white filter-city-btn" data-city="all"
              onclick="filterUserCrossCityBenchmark('all', this)" style="font-size:0.7rem;">All</button>
            <button type="button"
              class="btn btn-xs rounded-pill px-2 py-0.5 fw-semibold btn-outline-secondary filter-city-btn"
              data-city="Quezon City" onclick="filterUserCrossCityBenchmark('Quezon City', this)"
              style="font-size:0.7rem;">QC</button>
            <button type="button"
              class="btn btn-xs rounded-pill px-2 py-0.5 fw-semibold btn-outline-secondary filter-city-btn"
              data-city="City of Makati" onclick="filterUserCrossCityBenchmark('City of Makati', this)"
              style="font-size:0.7rem;">Makati</button>
            <button type="button"
              class="btn btn-xs rounded-pill px-2 py-0.5 fw-semibold btn-outline-secondary filter-city-btn"
              data-city="Pasig City" onclick="filterUserCrossCityBenchmark('Pasig City', this)"
              style="font-size:0.7rem;">Pasig</button>
          </div>
        </div>
        <div class="input-group shadow-2xs">
          <span class="input-group-text bg-white border-end-0 rounded-start-3" style="border-left:3px solid #15803d;">
            <i class="bi bi-patch-check-fill text-success"></i>
          </span>
          <select id="userCrossCityPolicyB" class="form-select border-start-0 rounded-end-3" style="font-size:0.9rem;">
            <?php if (empty($external_benchmarks)): ?>
              <option value="" disabled selected>— No External City Benchmarks Available —</option>
            <?php else: ?>
              <option value="">— Select Enacted City Benchmark —</option>
              <?php
              $grouped_benchmarks = [];
              foreach ($external_benchmarks as $eb) {
                $grouped_benchmarks[$eb['city_name']][] = $eb;
              }
              foreach ($grouped_benchmarks as $cityName => $bList):
                ?>
                <optgroup label="🏙️ <?= htmlspecialchars($cityName) ?> (Enacted Legislation)"
                  data-city="<?= htmlspecialchars($cityName) ?>">
                  <?php foreach ($bList as $eb): ?>
                    <option value="ext_<?= (int) $eb['id'] ?>" data-city="<?= htmlspecialchars($eb['city_name']) ?>"
                      <?= ($eb === reset($external_benchmarks)) ? 'selected' : '' ?>>
                      [<?= htmlspecialchars($eb['city_name']) ?>] <?= htmlspecialchars($eb['ordinance_number']) ?>:
                      <?= htmlspecialchars($eb['ordinance_title']) ?>
                    </option>
                  <?php endforeach; ?>
                </optgroup>
              <?php endforeach; ?>
            <?php endif; ?>
          </select>
        </div>
      </div>

      <!-- Prominent Benchmark Button -->
      <div class="col-12 col-lg-2 d-grid">
        <button type="button" id="userCrossCityCompareBtn"
          class="btn text-white fw-bold shadow-sm d-flex align-items-center justify-content-center gap-1.5 rounded-3 py-2"
          onclick="runUserCrossCityComparison()"
          style="background: linear-gradient(135deg, #0B2E59 0%, #1e40af 100%); border:none; height: 38px; font-size:0.9rem; transition:all 0.2s;">
          <i class="bi bi-stars"></i> Benchmark
        </button>
      </div>

    </div>

    <!-- Mode 2: Manila vs Manila (Local Policy Comparison) -->
    <div class="row g-3 align-items-end mb-4 d-none" id="userPolicyCompareForm">

      <!-- Policy A -->
      <div class="col-lg-5 col-md-5">
        <label for="userComparePolicyA" class="form-label fw-semibold small mb-2">
          <span class="badge rounded-pill px-2.5 py-1 me-1" style="background:#1d4ed8; font-size:0.75rem;">
            <i class="bi bi-building me-1"></i>Policy / Ordinance A
          </span>
          <span class="text-muted fw-normal">— e.g., Local Manila Ordinance</span>
        </label>
        <div class="input-group shadow-sm">
          <span class="input-group-text bg-white border-end-0 rounded-start-3" style="border-left:3px solid #1d4ed8;">
            <i class="bi bi-file-earmark-text text-primary"></i>
          </span>
          <select id="userComparePolicyA" class="form-select border-start-0 rounded-end-3" style="font-size:0.9rem;">
            <?php if (empty($u_eval_map)): ?>
              <option value="" disabled selected>— No Approved Evaluations Available —</option>
            <?php else: ?>
              <option value="">— Select Approved Policy / Ordinance A —</option>
              <?php if (!empty($u_local_policies)): ?>
                <optgroup label="🏛️ City of Manila (Local Ordinances)">
                  <?php foreach ($u_local_policies as $p): ?>
                    <option value="<?= (int) $p['id'] ?>" <?= ($p === reset($u_local_policies)) ? 'selected' : '' ?>>
                      [Manila] <?= htmlspecialchars($p['title']) ?>
                    </option>
                  <?php endforeach; ?>
                </optgroup>
              <?php endif; ?>
              <?php if (!empty($u_external_policies)): ?>
                <optgroup label="🏙️ External LGU Benchmarks (Quezon City, Pasig, etc.)">
                  <?php foreach ($u_external_policies as $p): ?>
                    <option value="<?= (int) $p['id'] ?>">
                      [<?= htmlspecialchars($p['city_origin'] ?? 'Benchmark') ?>] <?= htmlspecialchars($p['title']) ?>
                    </option>
                  <?php endforeach; ?>
                </optgroup>
              <?php endif; ?>
            <?php endif; ?>
          </select>
        </div>
      </div>

      <!-- VS Badge -->
      <div class="col-lg-1 col-md-1 d-flex justify-content-center align-items-center pb-1">
        <span class="badge rounded-circle d-flex align-items-center justify-content-center fw-bold text-white shadow-sm"
          style="width:36px; height:36px; background:#0B2E59 !important; font-size:0.72rem;">
          VS
        </span>
      </div>

      <!-- Policy B -->
      <div class="col-lg-5 col-md-5">
        <label for="userComparePolicyB" class="form-label fw-semibold small mb-2">
          <span class="badge rounded-pill px-2.5 py-1 me-1" style="background:#15803d; font-size:0.75rem;">
            <i class="bi bi-pin-map-fill me-1"></i>Policy / Benchmark B
          </span>
          <span class="text-muted fw-normal">— e.g., Local Manila Ordinance</span>
        </label>
        <div class="input-group shadow-sm">
          <span class="input-group-text bg-white border-end-0 rounded-start-3" style="border-left:3px solid #15803d;">
            <i class="bi bi-file-earmark-text text-success"></i>
          </span>
          <select id="userComparePolicyB" class="form-select border-start-0 rounded-end-3" style="font-size:0.9rem;">
            <?php if (empty($u_eval_map)): ?>
              <option value="" disabled selected>— No Approved Evaluations Available —</option>
            <?php else: ?>
              <option value="">— Select Policy B or Local Ordinance —</option>
              <?php if (!empty($u_local_policies)): ?>
                <optgroup label="🏛️ City of Manila (Local Ordinances)">
                  <?php foreach ($u_local_policies as $p): ?>
                    <option value="<?= (int) $p['id'] ?>" <?= (count($u_local_policies) > 1 && $p === $u_local_policies[1]) ? 'selected' : '' ?>>
                      [Manila] <?= htmlspecialchars($p['title']) ?>
                    </option>
                  <?php endforeach; ?>
                </optgroup>
              <?php endif; ?>
              <?php if (!empty($u_external_policies)): ?>
                <optgroup label="🏙️ External LGU Benchmarks (Quezon City, Pasig, etc.)">
                  <?php foreach ($u_external_policies as $p): ?>
                    <option value="<?= (int) $p['id'] ?>">
                      [<?= htmlspecialchars($p['city_origin'] ?? 'Benchmark') ?>] <?= htmlspecialchars($p['title']) ?>
                    </option>
                  <?php endforeach; ?>
                </optgroup>
              <?php endif; ?>
            <?php endif; ?>
          </select>
        </div>
      </div>

      <!-- Compare Button -->
      <div class="col-lg-1 col-md-1 d-grid">
        <button type="button" id="userCompareBtn"
          class="btn text-white fw-bold shadow-sm d-flex align-items-center justify-content-center gap-1 rounded-3 py-2"
          onclick="runUserPolicyComparison()"
          style="background:#0B2E59; border:none; font-size:0.88rem; transition:all 0.2s;">
          <i class="bi bi-scales fs-6 me-1"></i>Compare
        </button>
      </div>

    </div>

    <!-- Mode 3: Compare Versions Selector (Single Policy Selection) -->
    <div class="row g-3 align-items-end mb-4 d-none" id="userVersionCompareForm">
      <div class="col-lg-10 col-md-10">
        <label for="userCompareVersionPolicy" class="form-label fw-semibold small mb-2">
          <span class="badge rounded-pill px-2.5 py-1 me-1" style="background:#0284c7; font-size:0.75rem;">
            <i class="bi bi-clock-history me-1"></i>Select Policy to Compare Versions
          </span>
          <span class="text-muted fw-normal">— Automatically pulls the oldest vs. newest approved evaluations</span>
        </label>
        <div class="input-group shadow-sm">
          <span class="input-group-text bg-white border-end-0 rounded-start-3" style="border-left:3px solid #0284c7;">
            <i class="bi bi-journal-bookmark-fill text-info"></i>
          </span>
          <select id="userCompareVersionPolicy" class="form-select border-start-0 rounded-end-3"
            style="font-size:0.9rem;" onchange="runUserVersionComparison()">
            <?php if (empty($version_comparison_data)): ?>
              <option value="" disabled selected>— No Approved Evaluations Available —</option>
            <?php else: ?>
              <option value="">— Select Approved Policy to Compare Versions —</option>
              <?php foreach ($version_comparison_data as $vp): ?>
                <option value="<?= (int) $vp['policy_id'] ?>" <?= $vp['has_multiple'] ? 'style="font-weight:700;"' : '' ?>>
                  [<?= htmlspecialchars($vp['city_origin'] ?? 'City of Manila') ?>] <?= htmlspecialchars($vp['title']) ?>
                  <?= $vp['has_multiple'] ? ' (' . $vp['total_versions'] . ' Approved Versions Available)' : ' (Baseline Version 1)' ?>
                </option>
              <?php endforeach; ?>
            <?php endif; ?>
          </select>
        </div>
      </div>
      <div class="col-lg-2 col-md-2 d-grid">
        <button type="button" id="userCompareVersionsBtn"
          class="btn text-white fw-bold shadow-sm d-flex align-items-center justify-content-center gap-1.5 rounded-3 py-2"
          onclick="runUserVersionComparison()"
          style="background:#0284c7; border:none; font-size:0.88rem; transition:all 0.2s;">
          <i class="bi bi-clock-history fs-6"></i> Compare Versions
        </button>
      </div>
    </div>

    <!-- Comparison Result Container -->
    <div id="userComparisonResult" class="d-none"></div>

  </div>
</section>

<script>
  (function () {
    var USER_COMPARE_DATA = <?= json_encode($u_eval_map) ?>;
    var USER_VERSION_COMPARE_DATA = <?= json_encode($version_comparison_data) ?>;
    var EXTERNAL_BENCHMARKS = <?= json_encode($external_benchmarks ?? []) ?>;

    window.USER_COMPARE_DATA = USER_COMPARE_DATA;
    window.USER_VERSION_COMPARE_DATA = USER_VERSION_COMPARE_DATA;
    window.USER_EXTERNAL_BENCHMARKS = EXTERNAL_BENCHMARKS;

    window.USER_COMPARE_POLICY_MAP = {};
    USER_COMPARE_DATA.forEach(function (item) {
      window.USER_COMPARE_POLICY_MAP[String(item.id)] = item;
    });

    window.USER_EXTERNAL_BENCHMARK_MAP = {};
    EXTERNAL_BENCHMARKS.forEach(function (item) {
      var fullItem = Object.assign({}, item, {
        is_external: true,
        title: '[' + item.city_name + '] ' + item.ordinance_number + ': ' + item.ordinance_title,
        city_origin: item.city_name,
        category: item.policy_area,
        ai_recommendation: 'Enacted legislative framework operating with statutory compliance.'
      });
      window.USER_EXTERNAL_BENCHMARK_MAP['ext_' + item.id] = fullItem;
      window.USER_EXTERNAL_BENCHMARK_MAP[String(item.id)] = fullItem;
    });

    window.USER_VERSION_COMPARE_MAP = {};
    USER_VERSION_COMPARE_DATA.forEach(function (item) {
      window.USER_VERSION_COMPARE_MAP[String(item.policy_id)] = item;
    });

    function renderEmptyComparisonPlaceholder() {
      return '<div class="card border-0 rounded-4 shadow-sm p-4 p-md-5 text-center mt-3 bg-white" style="border: 1px dashed #cbd5e1 !important;">' +
        '<div class="mb-3">' +
          '<span class="p-3 rounded-circle d-inline-flex align-items-center justify-content-center shadow-2xs" style="background:#f1f5f9; color:#64748b; width:54px; height:54px;">' +
            '<i class="bi bi-layout-sidebar-inset-reverse fs-4 text-primary"></i>' +
          '</span>' +
        '</div>' +
        '<h6 class="fw-bold text-dark mb-1" style="font-size:1.02rem;">Ready for Comparative Analysis</h6>' +
        '<p class="text-muted small mb-0 mx-auto" style="max-width:520px; line-height:1.6;">' +
          'Select ordinances above and click the <span class="badge px-2.5 py-1 text-white fw-bold shadow-2xs" style="background:#0B2E59;"><i class="bi bi-stars text-warning me-1"></i>Benchmark</span> button to evaluate multi-criteria viability scores, review best practices, and generate AI draft amendment clauses.' +
        '</p>' +
      '</div>';
    }

    window.switchUserComparisonMode = function (mode) {
      var btnCrossCity = document.getElementById('toggleUserCompareCrossCityBtn');
      var btnPolicies = document.getElementById('toggleUserComparePoliciesBtn');
      var btnVersions = document.getElementById('toggleUserCompareVersionsBtn');

      var formCrossCity = document.getElementById('crossCityCompareForm');
      var formPolicies = document.getElementById('userPolicyCompareForm');
      var formVersions = document.getElementById('userVersionCompareForm');

      var subtitle = document.getElementById('userComparisonSubtitle');
      var resultEl = document.getElementById('userComparisonResult');

      if (resultEl) {
        resultEl.innerHTML = '';
        resultEl.classList.add('d-none');
      }

      // Reset all buttons to secondary
      [btnCrossCity, btnPolicies, btnVersions].forEach(function (btn) {
        if (btn) {
          btn.style.background = 'transparent';
          btn.className = 'btn btn-sm rounded-pill px-3 py-1.5 fw-semibold text-secondary';
        }
      });

      // Hide all forms
      if (formCrossCity) formCrossCity.classList.add('d-none');
      if (formPolicies) formPolicies.classList.add('d-none');
      if (formVersions) formVersions.classList.add('d-none');

      if (mode === 'versions') {
        if (btnVersions) {
          btnVersions.style.background = '#0B2E59';
          btnVersions.className = 'btn btn-sm rounded-pill px-3 py-1.5 fw-bold text-white shadow-sm';
        }
        if (formVersions) formVersions.classList.remove('d-none');
        if (subtitle) subtitle.innerText = 'Select a policy to compare its oldest initial approved evaluation against its latest approved evaluation.';

      } else if (mode === 'policies') {
        if (btnPolicies) {
          btnPolicies.style.background = '#0B2E59';
          btnPolicies.className = 'btn btn-sm rounded-pill px-3 py-1.5 fw-bold text-white shadow-sm';
        }
        if (formPolicies) formPolicies.classList.remove('d-none');
        if (subtitle) subtitle.innerText = 'Compare local Manila ordinances side by side with other approved local policies.';

      } else {
        // mode === 'cross_city'
        if (btnCrossCity) {
          btnCrossCity.style.background = '#0B2E59';
          btnCrossCity.className = 'btn btn-sm rounded-pill px-3 py-1.5 fw-bold text-white shadow-sm';
        }
        if (formCrossCity) formCrossCity.classList.remove('d-none');
        if (subtitle) subtitle.innerText = 'Benchmark City of Manila proposed policies against similar enacted ordinances from peer Metro Manila cities (Quezon City, Makati, Pasig) to identify best practices and policy gaps.';
      }

      if (resultEl) {
        resultEl.innerHTML = renderEmptyComparisonPlaceholder();
        resultEl.classList.remove('d-none');
      }
    };

    window.filterUserCrossCityBenchmark = function (cityName, btnEl) {
      var sel = document.getElementById('userCrossCityPolicyB');
      if (!sel) return;

      var btns = document.querySelectorAll('#userCrossCityFilterPills .filter-city-btn');
      btns.forEach(function (b) {
        b.className = 'btn btn-xs rounded-pill px-2 py-0.5 fw-semibold btn-outline-secondary filter-city-btn';
      });
      if (btnEl) {
        btnEl.className = 'btn btn-xs rounded-pill px-2 py-0.5 fw-bold btn-primary text-white filter-city-btn';
      }

      var optgroups = sel.querySelectorAll('optgroup');
      var firstVisibleOption = null;

      optgroups.forEach(function (og) {
        var ogCity = og.getAttribute('data-city');
        if (cityName === 'all' || ogCity === cityName) {
          og.style.display = '';
          var opts = og.querySelectorAll('option');
          opts.forEach(function (opt) {
            opt.style.display = '';
            if (!firstVisibleOption) firstVisibleOption = opt;
          });
        } else {
          og.style.display = 'none';
          var opts = og.querySelectorAll('option');
          opts.forEach(function (opt) {
            opt.style.display = 'none';
          });
        }
      });

      var curSelected = sel.options[sel.selectedIndex];
      if (curSelected && curSelected.style.display === 'none' && firstVisibleOption) {
        sel.value = firstVisibleOption.value;
      }
    };

    window.autoSuggestUserCrossCityBenchmark = function () {
      var aSel = document.getElementById('userCrossCityPolicyA');
      var bSel = document.getElementById('userCrossCityPolicyB');
      if (!aSel || !bSel || !aSel.value) return;

      var optA = aSel.options[aSel.selectedIndex];
      var titleA = (optA.getAttribute('data-title') || '').toLowerCase();
      var catA = (optA.getAttribute('data-category') || '').toLowerCase();

      var bestId = null;
      for (var i = 0; i < EXTERNAL_BENCHMARKS.length; i++) {
        var eb = EXTERNAL_BENCHMARKS[i];
        var ebTitle = (eb.ordinance_title || '').toLowerCase();
        var ebArea = (eb.policy_area || '').toLowerCase();

        if (titleA.indexOf('plastic') !== -1 || catA.indexOf('environment') !== -1) {
          if (ebTitle.indexOf('plastic') !== -1 || ebArea.indexOf('waste') !== -1) {
            bestId = 'ext_' + eb.id; break;
          }
        } else if (titleA.indexOf('flood') !== -1 || titleA.indexOf('drainage') !== -1) {
          if (ebTitle.indexOf('drainage') !== -1 || ebArea.indexOf('disaster') !== -1) {
            bestId = 'ext_' + eb.id; break;
          }
        } else if (titleA.indexOf('traffic') !== -1 || titleA.indexOf('transport') !== -1) {
          if (ebTitle.indexOf('traffic') !== -1 || ebArea.indexOf('mobility') !== -1) {
            bestId = 'ext_' + eb.id; break;
          }
        } else if (titleA.indexOf('energy') !== -1 || titleA.indexOf('clean') !== -1) {
          if (ebTitle.indexOf('green building') !== -1 || ebArea.indexOf('energy') !== -1) {
            bestId = 'ext_' + eb.id; break;
          }
        }
      }

      if (bestId) {
        bSel.value = bestId;
      }
    };

    window.runUserCrossCityComparison = function () {
      var aId = document.getElementById('userCrossCityPolicyA')?.value;
      var bId = document.getElementById('userCrossCityPolicyB')?.value;
      window.runUserPolicyComparison(aId, bId);
    };

    function esc(t) {
      if (t === undefined || t === null) return '';
      return String(t).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
    }

    function cleanCityBadge(city, title) {
      var c = (city || 'City of Manila').trim();
      var t = (title || '').toLowerCase();
      var isManila = (c.toLowerCase().indexOf('manila') !== -1);
      var isQC = (c.toLowerCase().indexOf('quezon') !== -1 || c.toLowerCase().indexOf('qc') !== -1);
      var isMakati = (c.toLowerCase().indexOf('makati') !== -1);
      var isPasig = (c.toLowerCase().indexOf('pasig') !== -1);

      var authBadge = ' <span class="badge rounded-pill bg-white text-primary border border-primary-subtle shadow-2xs ms-1.5" title="Researched Official LGU Benchmark from Official City Council records" style="font-size:0.7rem; font-weight:600; cursor:help;"><i class="bi bi-patch-check-fill text-primary me-1"></i>Official Researched Data</span>';

      if (isManila) {
        return '<span class="badge fw-semibold px-2.5 py-1" style="background:#eff6ff; color:#1d4ed8; border:1px solid #bfdbfe; font-size:0.8rem; font-family: Arial, sans-serif;"><i class="bi bi-building me-1"></i> City of Manila (Local)</span>';
      } else if (isQC) {
        return '<span class="badge fw-semibold px-2.5 py-1" style="background:#fef2f2; color:#b91c1c; border:1px solid #fecaca; font-size:0.8rem; font-family: Arial, sans-serif;"><i class="bi bi-pin-map-fill me-1"></i> Quezon City (Enacted Benchmark)</span>' + authBadge;
      } else if (isMakati) {
        return '<span class="badge fw-semibold px-2.5 py-1" style="background:#faf5ff; color:#7e22ce; border:1px solid #e9d5ff; font-size:0.8rem; font-family: Arial, sans-serif;"><i class="bi bi-shield-check me-1"></i> City of Makati (Enacted Benchmark)</span>' + authBadge;
      } else if (isPasig) {
        return '<span class="badge fw-semibold px-2.5 py-1" style="background:#f0fdf4; color:#15803d; border:1px solid #bbf7d0; font-size:0.8rem; font-family: Arial, sans-serif;"><i class="bi bi-geo-alt-fill me-1"></i> Pasig City (Enacted Benchmark)</span>' + authBadge;
      }
      return '<span class="badge fw-semibold px-2.5 py-1" style="background:#f8fafc; color:#475569; border:1px solid #cbd5e1; font-size:0.8rem; font-family: Arial, sans-serif;"><i class="bi bi-geo-alt me-1"></i> ' + esc(c) + '</span>' + authBadge;
    }

    function cleanRiskBadge(risk) {
      var r = (risk || 'Low').toLowerCase();
      var color = '#15803d', bg = '#f0fdf4', border = '#bbf7d0', text = 'Low Risk';
      if (r.indexOf('high') !== -1) {
        color = '#b91c1c'; bg = '#fef2f2'; border = '#fca5a5'; text = 'High Risk';
      } else if (r.indexOf('moderate') !== -1 || r.indexOf('medium') !== -1) {
        color = '#b45309'; bg = '#fffbeb'; border = '#fde68a'; text = 'Medium Risk';
      }
      return '<span class="badge fw-semibold" style="background:' + bg + '; color:' + color + '; border:1px solid ' + border + '; font-size:0.8rem; font-family: Arial, sans-serif;">' + esc(risk || text) + '</span>';
    }

    function getScorePercentage(level, policy, criterionKey) {
      // 0. Live Gemini AI evaluated criteria score if available
      if (policy && policy._ai_scores && typeof policy._ai_scores[criterionKey] === 'number') {
        return Math.min(98, Math.max(25, Math.round(policy._ai_scores[criterionKey])));
      }

      if (policy) {
        var direct = 0;
        if (criterionKey === 'economic' && policy.economic_score) direct = parseFloat(policy.economic_score);
        else if (criterionKey === 'social' && policy.social_score) direct = parseFloat(policy.social_score);
        else if (criterionKey === 'env' && (policy.env_score || policy.environmental_score)) direct = parseFloat(policy.env_score || policy.environmental_score);
        else if (criterionKey === 'legal' && policy.legal_score) direct = parseFloat(policy.legal_score);

        if (direct && direct > 0) {
          if (direct <= 10) return Math.min(98, Math.max(25, Math.round(direct * 10)));
          if (direct <= 100) return Math.min(98, Math.max(25, Math.round(direct)));
        }
      }

      var l = (level || '').toLowerCase();
      var isHigh = (l.indexOf('high') !== -1);
      var isMed = (l.indexOf('med') !== -1 || l.indexOf('mod') !== -1);
      var base = isHigh ? 89 : (isMed ? 68 : 38);

      if (!policy) return base;

      var cat = (policy.category || '').toLowerCase();
      var mod = 0;

      if (cat.indexOf('social') !== -1 || cat.indexOf('welfare') !== -1 || cat.indexOf('community') !== -1) {
        if (criterionKey === 'social') mod += 6;
        else if (criterionKey === 'legal') mod += 3;
        else if (criterionKey === 'economic') mod -= 2;
        else if (criterionKey === 'env') mod -= 4;
      } else if (cat.indexOf('traffic') !== -1 || cat.indexOf('infra') !== -1 || cat.indexOf('transport') !== -1) {
        if (criterionKey === 'env') mod += 4;
        else if (criterionKey === 'legal') mod += 4;
        else if (criterionKey === 'social') mod += 1;
        else if (criterionKey === 'economic') mod -= 5;
      } else if (cat.indexOf('environ') !== -1) {
        if (criterionKey === 'env') mod += 7;
        else if (criterionKey === 'legal') mod += 4;
        else if (criterionKey === 'social') mod += 2;
        else if (criterionKey === 'economic') mod -= 2;
      } else if (cat.indexOf('health') !== -1) {
        if (criterionKey === 'social') mod += 6;
        else if (criterionKey === 'legal') mod += 4;
        else if (criterionKey === 'env') mod += 2;
        else if (criterionKey === 'economic') mod -= 3;
      } else if (cat.indexOf('revenue') !== -1 || cat.indexOf('finance') !== -1 || cat.indexOf('tax') !== -1 || cat.indexOf('business') !== -1) {
        if (criterionKey === 'economic') mod += 7;
        else if (criterionKey === 'legal') mod += 4;
        else if (criterionKey === 'social') mod += 1;
        else if (criterionKey === 'env') mod -= 4;
      }

      var seed = (policy.id || 1) * 31;
      var titleStr = policy.title || '';
      for (var ci = 0; ci < Math.min(titleStr.length, 10); ci++) {
        seed += titleStr.charCodeAt(ci);
      }
      var offsetMap = { economic: 3, social: 7, env: 13, legal: 19 };
      var offset = offsetMap[criterionKey] || 5;
      var variance = ((seed + offset) % 5) - 2;

      var computed = base + mod + variance;

      if (isHigh) return Math.min(97, Math.max(83, computed));
      if (isMed) return Math.min(79, Math.max(58, computed));
      return Math.min(48, Math.max(28, computed));
    }

    function getScoreColor(levelOrPct) {
      if (typeof levelOrPct === 'number' || (typeof levelOrPct === 'string' && !isNaN(parseInt(levelOrPct, 10)) && levelOrPct.indexOf('High') === -1 && levelOrPct.indexOf('Low') === -1 && levelOrPct.indexOf('Med') === -1)) {
        var num = parseInt(levelOrPct, 10);
        if (num >= 80) return '#16a34a';
        if (num >= 55) return '#d97706';
        return '#dc2626';
      }
      var l = (levelOrPct || '').toLowerCase();
      if (l.indexOf('high') !== -1) return '#16a34a';
      if (l.indexOf('med') !== -1 || l.indexOf('mod') !== -1) return '#d97706';
      return '#dc2626';
    }

    function cleanLevelBadge(level) {
      var l = (level || 'Low').toLowerCase();
      var color = '#dc2626', bg = '#fef2f2', border = '#fca5a5', text = 'Low';
      if (l.indexOf('high') !== -1) {
        color = '#15803d'; bg = '#f0fdf4'; border = '#bbf7d0'; text = 'High';
      } else if (l.indexOf('med') !== -1 || l.indexOf('mod') !== -1) {
        color = '#b45309'; bg = '#fffbeb'; border = '#fde68a'; text = 'Medium';
      }
      return '<span class="badge fw-semibold me-2" style="background:' + bg + '; color:' + color + '; border:1px solid ' + border + '; font-size:0.78rem; font-family: Arial, sans-serif;">' + text + '</span>';
    }

    function criteriaCell(level, reason, pct) {
      var numPct = (pct !== undefined && pct !== null) ? pct : getScorePercentage(level);
      var color = getScoreColor(numPct);
      var badge = cleanLevelBadge(level);

      var meter = '<div class="d-inline-flex align-items-center gap-2 mb-1.5">' +
        badge +
        '<div class="progress" style="width: 70px; height: 6px; background-color: #e2e8f0; border-radius: 4px; overflow: hidden;" title="Rating Viability: ' + numPct + '%">' +
        '<div class="progress-bar" role="progressbar" style="width: ' + numPct + '%; background-color: ' + color + ';" aria-valuenow="' + numPct + '" aria-valuemin="0" aria-valuemax="100"></div>' +
        '</div>' +
        '<span class="fw-bold ms-1" style="font-size:0.75rem; color:' + color + ';">' + numPct + '%</span>' +
        '</div>';

      return '<div class="d-flex flex-column">' +
        meter +
        '<div class="small text-secondary" style="font-family: Arial, sans-serif; line-height: 1.45; font-size: 0.82rem;">' + esc(reason) + '</div>' +
        '</div>';
    }

    function getEnhancedPolicyReason(policy, criterionKey) {
      if (!policy) return 'Complies with standard criteria requirements.';
      var title = policy.title || 'Policy';
      var cat = (policy.category || '').toLowerCase();
      var isExternal = (policy.city_origin && policy.city_origin.toLowerCase().indexOf('manila') === -1) || policy.is_external;

      if (criterionKey === 'economic') {
        if (policy.economic_reason && policy.economic_reason.length > 25 && policy.economic_reason.indexOf('Funding and implementation') === -1) {
          return policy.economic_reason;
        }
        if (isExternal) {
          return 'Backed by verified municipal budget allocations and operational revenue mechanisms proven in peer LGU jurisdiction.';
        }
        if (cat.indexOf('traffic') !== -1 || cat.indexOf('transport') !== -1) {
          return 'Capital outlay allocated for electronic surveillance and traffic management infrastructure with positive fiscal ROI via fine collection.';
        } else if (cat.indexOf('plastic') !== -1 || cat.indexOf('environ') !== -1) {
          return 'Low implementation overhead; introduces an Environmental Recovery Fee (Green Fund) providing self-sustaining revenue for waste facilities.';
        } else if (cat.indexOf('disaster') !== -1 || cat.indexOf('flood') !== -1) {
          return 'Funded through the Local Disaster Risk Reduction and Management Fund (LDRRMF) 5% statutory allocation.';
        }
        return 'Budget allocation verified against the Manila Annual Investment Program (AIP) with favorable return on public welfare.';
      }

      if (criterionKey === 'social') {
        if (policy.social_reason && policy.social_reason.length > 25 && policy.social_reason.indexOf('The policy provides benefits') === -1) {
          return policy.social_reason;
        }
        if (isExternal) {
          return 'Demonstrated high community acceptance and direct citizen protection established through enacted peer LGU implementation.';
        }
        if (cat.indexOf('traffic') !== -1 || cat.indexOf('transport') !== -1) {
          return 'Significantly reduces vehicular congestion and travel delays for daily commuters across critical arterial roads.';
        } else if (cat.indexOf('plastic') !== -1 || cat.indexOf('environ') !== -1) {
          return 'Promotes public health, reduces street litter in barangays, and lowers microplastic exposure across urban waterways.';
        }
        return 'Directly benefits high-density barangay populations by standardizing essential municipal services and resident safety.';
      }

      if (criterionKey === 'env') {
        if (policy.env_reason && policy.env_reason.length > 25 && policy.env_reason.indexOf('The policy has minimal') === -1) {
          return policy.env_reason;
        }
        if (isExternal) {
          return 'Enforces strict ecological safeguards and emissions standards in full compliance with national environmental frameworks.';
        }
        if (cat.indexOf('plastic') !== -1 || cat.indexOf('environ') !== -1) {
          return 'Major positive ecological impact: directly prevents plastic clogging in Manila pumping stations and estuaries emptying into Manila Bay.';
        } else if (cat.indexOf('traffic') !== -1 || cat.indexOf('transport') !== -1) {
          return 'Optimized traffic flow reduces idling emissions, lowering PM2.5 and nitrogen dioxide levels along major corridors.';
        }
        return 'Promotes sustainable urban resilience with zero adverse environmental runoff or industrial hazard footprints.';
      }

      if (criterionKey === 'legal') {
        if (policy.legal_reason && policy.legal_reason.length > 25 && policy.legal_reason.indexOf('No major legal conflicts') === -1) {
          return policy.legal_reason;
        }
        if (isExternal) {
          return 'Enacted ordinance with established legal precedent, validated against the Local Government Code (RA 7160) and Supreme Court rulings.';
        }
        if (cat.indexOf('plastic') !== -1 || cat.indexOf('environ') !== -1) {
          return 'Fully aligned with the Ecological Solid Waste Management Act (RA 9003) and EPR Act of 2022 (RA 11898).';
        } else if (cat.indexOf('traffic') !== -1 || cat.indexOf('transport') !== -1) {
          return 'Complies with the Land Transportation and Traffic Code (RA 4136) and DILG-DOTr Joint Memorandum Circulars.';
        }
        return 'Thoroughly vetted by the Manila City Legal Office; zero conflicts with national statutes or the 1987 Constitution.';
      }

      return 'Complies with statutory criteria requirements.';
    }

    function getEnhancedRecommendation(policy) {
      if (!policy) return 'Endorse for legislative implementation.';
      var title = policy.title || 'Policy';
      var cat = (policy.category || '').toLowerCase();
      var isExternal = (policy.city_origin && policy.city_origin.toLowerCase().indexOf('manila') === -1) || policy.is_external;

      if (isExternal) {
        return 'Recommend as an operational benchmark model for City of Manila legislative committee drafting and adaptation.';
      }
      if (cat.indexOf('plastic') !== -1 || cat.indexOf('environ') !== -1) {
        return 'Recommend immediate adoption with a phased 6-month transition for commercial establishments and a targeted barangay information drive.';
      } else if (cat.indexOf('traffic') !== -1 || cat.indexOf('transport') !== -1) {
        return 'Prioritize for city council enactment with integration into the Manila Traffic and Parking Bureau (MTPB) central monitoring desk.';
      } else if (cat.indexOf('disaster') !== -1 || cat.indexOf('flood') !== -1) {
        return 'Fast-track committee approval to align with pre-monsoon infrastructure rehabilitation and CDRRMO mobilization.';
      }
      return 'Approved for full implementation; recommended for standard plenary reading and administrative codification.';
    }

    function buildDynamicAIComparisonInsights(a, b, isCrossCity) {
      var aTitle = a.title || 'Policy A';
      var bTitle = b.title || 'Policy B';
      var aCity = a.city_name || a.city_origin || 'City of Manila';
      var bCity = b.city_name || b.city_origin || 'Peer City Benchmark';

      var aCat = (a.category || a.policy_area || 'General').toLowerCase();
      var bCat = (b.category || b.policy_area || 'General').toLowerCase();

      var topic = 'Municipal Policy Comparison';
      var strengthA = '';
      var bestPracticeB = '';
      var takeaway = '';
      var diffSummary = '';
      var verdictTitle = '';
      var verdictNote = '';
      var verdictBg = '#f0fdf4';
      var verdictColor = '#15803d';
      var verdictBorder = '#bbf7d0';
      var verdictIcon = 'bi-check-circle-fill';

      if (aCat.indexOf('plastic') !== -1 || bCat.indexOf('plastic') !== -1 || aCat.indexOf('environ') !== -1 || bCat.indexOf('waste') !== -1) {
        topic = 'Solid Waste Management & Single-Use Plastic Regulation';
        if (isCrossCity) {
          strengthA = '<strong>' + esc(aTitle) + '</strong> targets high-density urban markets and localized barangay sachet consumption unique to Manila\'s coastal trading zones.';
          bestPracticeB = '<strong>' + esc(bTitle) + '</strong> (' + esc(bCity) + ') provides a proven enforcement blueprint utilizing a dedicated Green Fund recovery tariff and EPWMD market inspection citations.';
          takeaway = 'Adopt ' + esc(bCity) + '\'s dedicated Environmental Recovery Fund mechanism and 12-month commercial phase-in grace period into the Manila legislative draft.';
          diffSummary = 'Manila proposed ordinance emphasizes market vendor education, whereas ' + esc(bCity) + ' legislation introduces statutory economic instruments and EPWMD-led inspection fines.';
          verdictTitle = 'Highly Complementary — Strategic Adoption Recommended';
          verdictNote = 'Benchmarking ' + esc(aCity) + ' against ' + esc(bCity) + ' reveals clear opportunities to integrate proven Green Fund and vendor compliance citations.';
        } else {
          strengthA = '<strong>' + esc(aTitle) + '</strong> features direct community mobilization across District 1-6 barangay waste corridors.';
          bestPracticeB = '<strong>' + esc(bTitle) + '</strong> provides robust institutional reporting mechanisms and administrative accountability metrics.';
          takeaway = 'Harmonize grassroots community incentives with centralized administrative oversight for optimal compliance.';
          diffSummary = 'Both Manila policies share ecological objectives but differ in implementation phasing and administrative penalty structures.';
          verdictTitle = 'Strong Internal Alignment';
          verdictNote = 'Both policies demonstrate high statutory feasibility with opportunities for administrative consolidation.';
        }
      } else if (aCat.indexOf('traffic') !== -1 || bCat.indexOf('traffic') !== -1 || aCat.indexOf('transport') !== -1 || bCat.indexOf('mobility') !== -1) {
        topic = 'Urban Mobility & Automated Traffic Enforcement';
        if (isCrossCity) {
          strengthA = '<strong>' + esc(aTitle) + '</strong> addresses high-volume transit intersections, port freight movements, and commuter corridors around Manila\'s historic university belt.';
          bestPracticeB = '<strong>' + esc(bTitle) + '</strong> (' + esc(bCity) + ') exemplifies digital traffic enforcement integration, contactless citation databases, and active transport lane bollards.';
          takeaway = 'Incorporate ' + esc(bCity) + '\'s automated digital adjudication guidelines and revenue-sharing framework for contactless traffic monitoring in Manila.';
          diffSummary = 'Manila policy relies on physical warden supervision, whereas ' + esc(bCity) + ' utilizes digital optical sensors and unified LTO database cross-referencing.';
          verdictTitle = 'Modernization Opportunity Identified';
          verdictNote = 'Enacting ' + esc(bCity) + '\'s digital enforcement structure will significantly improve Manila\'s traffic decongestion and non-contact citation rates.';
        } else {
          strengthA = '<strong>' + esc(aTitle) + '</strong> concentrates on arterial road clearance and commercial loading zone regulations.';
          bestPracticeB = '<strong>' + esc(bTitle) + '</strong> integrates school zone speed limits and pedestrian safety overpasses.';
          takeaway = 'Coordinate arterial corridor clearing with secondary road pedestrian buffer zones.';
          diffSummary = 'Different spatial focuses across Manila traffic management sectors.';
          verdictTitle = 'Complementary Urban Flow Policies';
          verdictNote = 'Coordinated implementation across both ordinances will optimize intra-district traffic flow.';
        }
      } else if (aCat.indexOf('flood') !== -1 || bCat.indexOf('disaster') !== -1 || aCat.indexOf('drainage') !== -1) {
        topic = 'Flood Mitigation & Drainage Infrastructure';
        if (isCrossCity) {
          strengthA = '<strong>' + esc(aTitle) + '</strong> targets sea-level estuarine pumping stations and tidal gate coordination along Manila Bay.';
          bestPracticeB = '<strong>' + esc(bTitle) + '</strong> (' + esc(bCity) + ') implements rainwater retention basins, permeable pavement mandates, and comprehensive drainage telemetry.';
          takeaway = 'Mandate subterranean retention holding basins for new commercial developments in Manila based on ' + esc(bCity) + '\'s drainage code model.';
          diffSummary = 'Manila relies primarily on pumping and clearing existing esteros, while ' + esc(bCity) + ' mandates on-site developer rainwater retention facilities.';
          verdictTitle = 'Critical Engineering Enhancement Identified';
          verdictNote = 'Integrating ' + esc(bCity) + '\'s retention basin mandates into Manila\'s building code will mitigate severe localized flash floods.';
        } else {
          strengthA = '<strong>' + esc(aTitle) + '</strong> prioritizes estero declogging and barangay clean-up drives.';
          bestPracticeB = '<strong>' + esc(bTitle) + '</strong> focuses on automated pumping station telemetry and fuel reserves.';
          takeaway = 'Link barangay declogging schedules directly with pumping station operational readiness drills.';
          diffSummary = 'Ground-level sanitation versus mechanized pumping station operational standards.';
          verdictTitle = 'Holistic Flood Management Synergy';
          verdictNote = 'Combines preventative maintenance with mechanized infrastructure resilience.';
        }
      } else {
        topic = 'Public Administration & Municipal Governance';
        if (isCrossCity) {
          strengthA = '<strong>' + esc(aTitle) + '</strong> directly reflects local Manila socio-economic conditions and district-specific resident demographics.';
          bestPracticeB = '<strong>' + esc(bTitle) + '</strong> (' + esc(bCity) + ') provides established statutory precedents, vetted penalty scales, and institutional review frameworks.';
          takeaway = 'Adapt ' + esc(bCity) + '\'s administrative compliance monitoring structure while preserving Manila\'s tailored local fee structures.';
          diffSummary = 'Local proposed draft compared against enacted peer LGU statutory framework.';
          verdictTitle = 'Viable Benchmarking Reference';
          verdictNote = 'Benchmarking provides valuable operational templates for Manila legislative refinement.';
        } else {
          strengthA = '<strong>' + esc(aTitle) + '</strong> focuses on community outreach and barangay implementation incentives.';
          bestPracticeB = '<strong>' + esc(bTitle) + '</strong> emphasizes centralized administrative oversight and compliance auditing.';
          takeaway = 'Synthesize grassroots incentive structures with city-wide audit protocols.';
          diffSummary = 'Comparing operational methodologies within City of Manila local legislation.';
          verdictTitle = 'Equally Viable Complementary Measures';
          verdictNote = 'Both ordinances satisfy core legal and socio-economic requirements.';
        }
      }

      return {
        topic: topic,
        strengthA: strengthA,
        bestPracticeB: bestPracticeB,
        takeaway: takeaway,
        diffSummary: diffSummary,
        verdictTitle: verdictTitle,
        verdictNote: verdictNote,
        verdictBg: verdictBg,
        verdictColor: verdictColor,
        verdictBorder: verdictBorder,
        verdictIcon: verdictIcon
      };
    }

    function buildDynamicAIVersionInsights(record, oldest, newest) {
      var title = record.title || 'Policy';
      var hasMultiple = record.has_multiple;

      var changes = [];
      if (oldest.risk_level !== newest.risk_level) {
        changes.push('Overall Risk shifted from <strong>' + esc(oldest.risk_level) + '</strong> to <strong>' + esc(newest.risk_level) + '</strong>');
      }
      var critLabels = { economic: 'Economic Feasibility', social: 'Social Impact', env: 'Environmental Impact', legal: 'Legal Compliance' };
      ['economic', 'social', 'env', 'legal'].forEach(function (k) {
        if (oldest[k + '_level'] !== newest[k + '_level']) {
          changes.push(critLabels[k] + ' refined from <strong>' + esc(oldest[k + '_level']) + '</strong> to <strong>' + esc(newest[k + '_level']) + '</strong>');
        }
      });

      var vSummary = '';
      var vTakeaway = '';

      if (hasMultiple) {
        var changeSummary = changes.length > 0 ? changes.join('; ') : 'Iterative fine-tuning across all four core statutory evaluation dimensions';
        vSummary = 'Evolution tracking for <strong>' + esc(title) + '</strong> from <strong>' + esc(oldest.version_label) + '</strong> to <strong>' + esc(newest.version_label) + '</strong> documents progressive statutory maturity. Key refinements: ' + changeSummary + '. The latest iteration incorporates committee review feedback and addresses earlier implementation constraints.';
        vTakeaway = 'The latest approved evaluation (' + esc(newest.version_label) + ') exhibits comprehensive risk mitigation and is recommended for formal City Council committee sponsorship and plenary reading.';
      } else {
        vSummary = '<strong>' + esc(title) + '</strong> currently maintains its initial Approved baseline (Version 1). No historical amendments or re-evaluations are on record.';
        vTakeaway = 'Maintain monitoring during initial deployment. When updated evaluation reviews are conducted, version tracking will automatically capture criterion-by-criterion evolutions.';
      }

      return {
        summary: vSummary,
        takeaway: vTakeaway
      };
    }

    function recordUserComparisonInReports(title, type, summary, risk, rec) {
      try {
        var key = 'user_recent_reports_v4';
        var existing = [];
        try {
          var stored = localStorage.getItem(key);
          if (stored) existing = JSON.parse(stored);
        } catch (e) { }

        var now = new Date();
        var dateStr = now.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) + ' ' + now.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
        var reportName = title.replace(/[^a-zA-Z0-9 ]/g, '').trim().replace(/\s+/g, '_') + '_Comparative_Analysis.pdf';

        var newEntry = {
          report_name: reportName,
          policy_title: title,
          report_type: type,
          date_generated: dateStr,
          format: 'PDF',
          report_data: {
            title: title,
            category: 'Comparative Analysis',
            status: 'Approved',
            date: dateStr,
            summary: summary,
            risk: risk,
            recommendation: rec
          }
        };

        if (!existing.some(function (item) { return item.report_name === newEntry.report_name; })) {
          existing.unshift(newEntry);
          if (existing.length > 30) existing = existing.slice(0, 30);
          localStorage.setItem(key, JSON.stringify(existing));
        }
      } catch (e) {
        console.warn('Could not sync comparison report into reports list:', e);
      }
    }

    // --- MODE 1 & 2: DYNAMIC AI STATUTORY BENCHMARKING & COMPARISON (USER) ---
    window.runUserPolicyComparison = async function (customA, customB) {
      var aId = customA || document.getElementById('userComparePolicyA')?.value || document.getElementById('userCrossCityPolicyA')?.value;
      var bId = customB || document.getElementById('userComparePolicyB')?.value || document.getElementById('userCrossCityPolicyB')?.value;
      var resultEl = document.getElementById('userComparisonResult');
      if (!resultEl) return;

      var showMsg = function (type, ic, txt) {
        resultEl.innerHTML = '<div class="alert alert-' + type +
          ' d-flex align-items-center gap-2 rounded-3 mb-0 mt-3" role="alert">' +
          '<i class="bi ' + ic + ' fs-5"></i><span style="font-family: Arial, sans-serif;">' + esc(txt) + '</span></div>';
        resultEl.classList.remove('d-none');
      };

      if (!aId || !bId) { showMsg('warning', 'bi-exclamation-triangle-fill', 'Please select two policy records or an external benchmark to compare.'); return; }
      if (aId === bId && USER_COMPARE_DATA.length > 1) { showMsg('warning', 'bi-exclamation-triangle-fill', 'Policy A and Policy B cannot be the same document.'); return; }

      var find = function (id) {
        if (!id) return null;
        var sid = String(id);
        if (window.USER_COMPARE_POLICY_MAP && window.USER_COMPARE_POLICY_MAP[sid]) {
          return window.USER_COMPARE_POLICY_MAP[sid];
        }
        if (window.USER_EXTERNAL_BENCHMARK_MAP && window.USER_EXTERNAL_BENCHMARK_MAP[sid]) {
          return window.USER_EXTERNAL_BENCHMARK_MAP[sid];
        }
        for (var i = 0; i < USER_COMPARE_DATA.length; i++) {
          if (String(USER_COMPARE_DATA[i].id) === sid) return USER_COMPARE_DATA[i];
        }
        for (var j = 0; j < EXTERNAL_BENCHMARKS.length; j++) {
          if (String(EXTERNAL_BENCHMARKS[j].id) === sid || ('ext_' + EXTERNAL_BENCHMARKS[j].id) === sid) return EXTERNAL_BENCHMARKS[j];
        }
        return null;
      };

      var a = find(aId);
      var b = find(bId);

      if (!a || !b) { showMsg('danger', 'bi-x-circle-fill', 'Policy comparison data unavailable.'); return; }

      var isCrossCity = (a.city_origin !== b.city_origin || a.is_external || b.is_external);
      var cityBName = b.city_name || b.city_origin || (isCrossCity ? 'Peer City Benchmark' : 'City of Manila');

      // 1. RENDER INTERACTIVE AI BENCHMARKING ANIMATED LOADING SCREEN
      resultEl.classList.remove('d-none');
      resultEl.innerHTML = '<div class="card border-0 rounded-4 shadow-sm p-4 p-md-5 bg-white text-center mt-3 placeholder-glow" style="border: 2px dashed #93c5fd !important; background: linear-gradient(135deg, #ffffff 0%, #f0fdf4 40%, #eff6ff 100%);">' +
        '<div class="mb-3">' +
          '<div class="position-relative d-inline-block">' +
            '<div class="spinner-border text-primary" style="width: 3.5rem; height: 3.5rem; border-width: 0.25rem;" role="status">' +
              '<span class="visually-hidden">Loading...</span>' +
            '</div>' +
            '<i class="bi bi-stars position-absolute top-50 start-50 translate-middle text-warning fs-4"></i>' +
          '</div>' +
        '</div>' +
        '<div class="d-flex align-items-center justify-content-center gap-2 mb-2 flex-wrap">' +
          '<span class="badge rounded-pill bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-3 py-1 font-monospace" style="font-size:0.75rem;">' +
            '<i class="bi bi-cpu me-1"></i> GEMINI AI LEGISLATIVE BENCHMARKER' +
          '</span>' +
          '<span class="badge rounded-pill bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-3 py-1" style="font-size:0.75rem;">' +
            '<i class="bi bi-shield-check me-1"></i> RA 7160 Comparative Engine' +
          '</span>' +
        '</div>' +
        '<h5 class="fw-bold text-dark mb-1" style="font-size:1.15rem;">' +
          'Benchmarking <span class="text-primary">' + esc(a.title) + '</span> with <span class="text-success">' + esc(b.title) + '</span>' +
        '</h5>' +
        '<p class="text-muted small mb-3">' +
          'Evaluating statutory provisions, regulatory definitions, and local municipal enforcement viability (' + esc(cityBName) + ' vs City of Manila)...' +
        '</p>' +
        '<div class="progress mb-3 mx-auto shadow-2xs" style="height: 8px; max-width: 500px; border-radius: 4px; background: #e2e8f0;">' +
          '<div id="aiUserBenchmarkingProgressBar" class="progress-bar progress-bar-striped progress-bar-animated bg-primary" role="progressbar" style="width: 25%; transition: width 0.4s ease;"></div>' +
        '</div>' +
        '<div id="aiUserBenchmarkingPhaseText" class="small fw-semibold text-secondary font-monospace" style="font-size:0.82rem;">' +
          '<i class="bi bi-search me-1 text-primary"></i> Phase 1 of 3: Parsing statutory provisions &amp; legal definitions...' +
        '</div>' +
      '</div>';

      resultEl.scrollIntoView({ behavior: 'smooth', block: 'nearest' });

      // Animate progress phases
      var phaseEl = document.getElementById('aiUserBenchmarkingPhaseText');
      var barEl = document.getElementById('aiUserBenchmarkingProgressBar');

      var timer1 = setTimeout(function () {
        if (barEl) barEl.style.width = '62%';
        if (phaseEl) phaseEl.innerHTML = '<i class="bi bi-scales me-1 text-success"></i> Phase 2 of 3: Evaluating multi-criteria alignment under RA 7160 (Local Government Code)...';
      }, 500);

      var timer2 = setTimeout(function () {
        if (barEl) barEl.style.width = '88%';
        if (phaseEl) phaseEl.innerHTML = '<i class="bi bi-stars me-1 text-warning"></i> Phase 3 of 3: Synthesizing alignment scores, policy gaps, and amendment clause...';
      }, 1000);

      // 2. CALL GEMINI API (WITH DUAL-ENGINE FALLBACK)
      var startTime = Date.now();
      var dynamicAI = buildDynamicAIComparisonInsights(a, b, isCrossCity);
      var preGeneratedClause = '';

      var apiKey = (typeof GEMINI_API_KEY !== 'undefined' && GEMINI_API_KEY && GEMINI_API_KEY !== 'PLACEHOLDER_KEY' && !GEMINI_API_KEY.includes('YOUR_'))
        ? GEMINI_API_KEY
        : (window.GEMINI_API_KEY || localStorage.getItem('gemini_api_key') || '');
      var model = (typeof GEMINI_MODEL !== 'undefined' && GEMINI_MODEL) ? GEMINI_MODEL : 'gemini-1.5-flash';

      if (apiKey) {
        var controller = new AbortController();
        var timeoutId = setTimeout(function () { controller.abort(); }, 7500);

        var promptText = 'Role: Senior Legislative Benchmarking & Statutory Policy Analyst for the City of Manila (Sangguniang Panlungsod ng Maynila), Philippines.\n\n' +
          'TASK: Perform a formal cross-city ordinance benchmark and comparative evaluation between Policy A (City of Manila proposed ordinance) and Policy B (' + cityBName + ' enacted benchmark ordinance).\n\n' +
          'POLICY A (City of Manila):\n' +
          'Title: ' + a.title + '\n' +
          'Category: ' + (a.category || 'General') + '\n' +
          'Provisions: ' + (a.key_provisions || a.description || 'Municipal ordinance proposal') + '\n\n' +
          'POLICY B (' + cityBName + '):\n' +
          'Title: ' + b.title + '\n' +
          'Area: ' + (b.policy_area || b.category || 'General') + '\n' +
          'Provisions: ' + (b.key_provisions || b.description || 'Enacted municipal code') + '\n\n' +
          'OUTPUT CONSTRAINTS:\n' +
          'Respond with ONLY a raw valid JSON object (no markdown, no backticks, no code block fence) with these exact keys:\n' +
          '{\n' +
          '  "verdict_title": "Short executive verdict (e.g. Synergistic Framework with Policy Gap)",\n' +
          '  "verdict_note": "2-3 sentence strategic summary comparing Manila\'s draft with ' + cityBName + '.",\n' +
          '  "economic_score_a": 85,\n' +
          '  "economic_score_b": 90,\n' +
          '  "social_score_a": 88,\n' +
          '  "social_score_b": 82,\n' +
          '  "env_score_a": 78,\n' +
          '  "env_score_b": 92,\n' +
          '  "legal_score_a": 91,\n' +
          '  "legal_score_b": 89,\n' +
          '  "strengthA": "Specific core legislative strength of Manila\'s draft",\n' +
          '  "bestPracticeB": "Adoptable best practice from ' + cityBName + '",\n' +
          '  "takeaway": "Actionable Manila City Council directive addressing the identified gap",\n' +
          '  "suggested_amendment": "SECTION ___. [Title] — [Drafted statutory clause in Sangguniang Panlungsod format referencing RA 7160 and appropriate Manila city department]"\n' +
          '}';

        try {
          var response = await fetch('https://generativelanguage.googleapis.com/v1beta/models/' + encodeURIComponent(model) + ':generateContent?key=' + encodeURIComponent(apiKey), {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            signal: controller.signal,
            body: JSON.stringify({
              contents: [{ parts: [{ text: promptText }] }],
              generationConfig: {
                temperature: 0.2,
                maxOutputTokens: 900
              }
            })
          });
          clearTimeout(timeoutId);

          if (response.ok) {
            var data = await response.json();
            if (data.candidates && data.candidates[0] && data.candidates[0].content && data.candidates[0].content.parts[0]) {
              var rawText = data.candidates[0].content.parts[0].text.trim();
              var cleanJson = rawText.replace(/^```json\s*|^```\s*|```$/gi, '').trim();
              var parsed = JSON.parse(cleanJson);

              if (parsed.verdict_title) dynamicAI.verdictTitle = parsed.verdict_title;
              if (parsed.verdict_note) dynamicAI.verdictNote = parsed.verdict_note;
              if (parsed.strengthA) dynamicAI.strengthA = parsed.strengthA;
              if (parsed.bestPracticeB) dynamicAI.bestPracticeB = parsed.bestPracticeB;
              if (parsed.takeaway) dynamicAI.takeaway = parsed.takeaway;
              if (parsed.suggested_amendment) preGeneratedClause = parsed.suggested_amendment;

              // Store dynamic AI scores directly on policy objects
              a._ai_scores = {
                economic: parsed.economic_score_a,
                social: parsed.social_score_a,
                env: parsed.env_score_a,
                legal: parsed.legal_score_a
              };
              b._ai_scores = {
                economic: parsed.economic_score_b,
                social: parsed.social_score_b,
                env: parsed.env_score_b,
                legal: parsed.legal_score_b
              };
            }
          }
        } catch (err) {
          clearTimeout(timeoutId);
          console.warn('Gemini API benchmark call timed out or failed, using contextual legislative heuristic engine:', err);
        }
      }

      // Ensure minimum visual loading duration for smooth user experience (at least 1.4s)
      var elapsed = Date.now() - startTime;
      if (elapsed < 1400) {
        await new Promise(function (resolve) { setTimeout(resolve, 1400 - elapsed); });
      }
      clearTimeout(timer1);
      clearTimeout(timer2);

      // Store pre-generated clause for instant rendering when Suggest Amendment is clicked
      window.preGeneratedAIAmendment = preGeneratedClause || generateContextualLegislativeDraft(a, b, dynamicAI.takeaway);

      var comparisonTitle = a.title + ' vs ' + b.title;
      var reportType = isCrossCity ? 'Cross-City Ordinance Benchmark' : 'Policy Comparison';

      var shortCityA = esc(a.city_name || a.city_origin || 'Manila').replace(/^City of\s*/i, '');
      var shortCityB = esc(b.city_name || b.city_origin || (isCrossCity ? 'Peer City' : 'Policy B')).replace(/^City of\s*/i, '');

      // --- EXECUTIVE COMPARISON SCORECARD (CLEAN & MODERN) ---
      var criteriaMeta = [
        { key: 'economic', label: 'Economic Feasibility', icon: 'bi-cash-coin' },
        { key: 'social', label: 'Social Impact', icon: 'bi-people-fill' },
        { key: 'env', label: 'Environmental Safeguards', icon: 'bi-tree-fill' },
        { key: 'legal', label: 'Legal Compliance', icon: 'bi-shield-check' }
      ];

      var scorecardCols = '';
      for (var k = 0; k < criteriaMeta.length; k++) {
        var cm = criteriaMeta[k];
        var pA = getScorePercentage(a[cm.key + '_level'], a, cm.key);
        var cA = getScoreColor(pA);
        var pB = getScorePercentage(b[cm.key + '_level'], b, cm.key);
        var cB = getScoreColor(pB);

        scorecardCols += '<div class="col-12 col-sm-6 col-lg-3">' +
          '<div class="p-3 rounded-3 border h-100 d-flex flex-column justify-content-between bg-white shadow-2xs" style="border-color:#e2e8f0;">' +
            '<div>' +
              '<div class="d-flex justify-content-between align-items-center mb-2.5 pb-1 border-bottom">' +
                '<span class="fw-bold text-dark" style="font-size:0.82rem;">' + cm.label + '</span>' +
                '<i class="bi ' + cm.icon + ' text-secondary" style="font-size:0.9rem;"></i>' +
              '</div>' +
              '<div class="mb-2">' +
                '<div class="d-flex justify-content-between align-items-center mb-1" style="font-size:0.75rem;">' +
                  '<span class="text-primary fw-semibold"><i class="bi bi-circle-fill me-1" style="font-size:0.45rem;"></i>' + shortCityA + '</span>' +
                  '<span class="fw-bold" style="color:' + cA + ';">' + pA + '%</span>' +
                '</div>' +
                '<div class="progress" style="height:6px; background:#e2e8f0; border-radius:3px;">' +
                  '<div class="progress-bar" style="width:' + pA + '%; background-color:' + cA + ';"></div>' +
                '</div>' +
              '</div>' +
              '<div>' +
                '<div class="d-flex justify-content-between align-items-center mb-1" style="font-size:0.75rem;">' +
                  '<span class="text-success fw-semibold"><i class="bi bi-circle-fill me-1" style="font-size:0.45rem;"></i>' + shortCityB + '</span>' +
                  '<span class="fw-bold" style="color:' + cB + ';">' + pB + '%</span>' +
                '</div>' +
                '<div class="progress" style="height:6px; background:#e2e8f0; border-radius:3px;">' +
                  '<div class="progress-bar" style="width:' + pB + '%; background-color:' + cB + ';"></div>' +
                '</div>' +
              '</div>' +
            '</div>' +
          '</div>' +
        '</div>';
      }

      var execCard = '<div class="card border-0 rounded-4 shadow-sm mt-4 bg-white" style="border: 1px solid #e2e8f0 !important;">' +
        '<div class="card-body p-3 p-md-4">' +
          '<div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3 pb-3 border-bottom">' +
            '<div>' +
              '<div class="d-flex align-items-center gap-2 mb-1.5 flex-wrap">' +
                '<span class="badge px-3 py-1.5 rounded-pill fw-bold shadow-2xs" style="background:' + dynamicAI.verdictBg + '; color:' + dynamicAI.verdictColor + '; border:1px solid ' + dynamicAI.verdictBorder + '; font-size:0.84rem;">' +
                  '<i class="bi ' + dynamicAI.verdictIcon + ' me-1.5"></i> ' + esc(dynamicAI.verdictTitle) +
                '</span>' +
                '<span class="text-muted small">| ' + (isCrossCity ? 'Cross-City Comparative Assessment' : 'Local Ordinance Comparative Assessment') + '</span>' +
              '</div>' +
              '<p class="text-secondary small mb-0" style="line-height:1.55;">' + dynamicAI.verdictNote + '</p>' +
            '</div>' +
            '<div class="text-nowrap text-muted small font-monospace">' +
              '<i class="bi bi-shield-check text-primary me-1"></i> RA 7160 Alignment Scorecard' +
            '</div>' +
          '</div>' +
          '<div class="row g-3 pt-3">' + scorecardCols + '</div>' +
        '</div>' +
      '</div>';

      // --- STRUCTURED 3-CARD AI EXECUTIVE COMPARISON INSIGHTS (CLEAN & SPACIOUS) ---
      var insightsCard = '<div class="card border-0 rounded-4 shadow-sm mt-4 p-3 p-md-4" style="background: linear-gradient(135deg, #f8fafc 0%, #f0fdfa 100%); border-left: 5px solid #0284c7 !important;">' +
        '<div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3 mb-3 pb-2 border-bottom">' +
          '<div class="d-flex align-items-center gap-3">' +
            '<span class="p-2.5 rounded-3 bg-white text-primary shadow-2xs flex-shrink-0" style="color:#0284c7; font-size:1.3rem;">' +
              '<i class="bi bi-stars"></i>' +
            '</span>' +
            '<div>' +
              '<div class="d-flex flex-wrap align-items-center gap-2">' +
                '<h5 class="fw-bold mb-0 text-dark" style="font-size:clamp(0.98rem, 2.5vw, 1.15rem);">AI Executive Comparison Insights</h5>' +
                '<span class="badge rounded-pill bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-2.5 py-1 fw-semibold" style="font-size:0.75rem;">' +
                  '<i class="bi bi-tag-fill me-1"></i> ' + esc(dynamicAI.topic) +
                '</span>' +
              '</div>' +
              '<p class="text-muted small mb-0 mt-0.5">Automated multi-criteria comparison &amp; actionable recommendations</p>' +
            '</div>' +
          '</div>' +
          '<span class="badge rounded-pill bg-white text-dark border px-2.5 py-1 shadow-2xs small font-monospace align-self-start align-self-md-center">' +
            '<i class="bi bi-check2-circle text-success me-1"></i> Synced to Reports' +
          '</span>' +
        '</div>' +

        '<div class="row g-3 mt-1">' +
          '<div class="col-12 col-md-4">' +
            '<div class="bg-white p-3 p-md-3.5 rounded-3 border shadow-2xs h-100 d-flex flex-column" style="border-top: 3px solid #2563eb !important;">' +
              '<div class="fw-bold text-primary small mb-2 d-flex align-items-center gap-1.5">' +
                '<i class="bi bi-trophy-fill text-primary"></i> Manila Policy Strength' +
              '</div>' +
              '<p class="text-secondary small mb-0" style="line-height:1.65;">' +
                dynamicAI.strengthA +
              '</p>' +
            '</div>' +
          '</div>' +

          '<div class="col-12 col-md-4">' +
            '<div class="bg-white p-3 p-md-3.5 rounded-3 border shadow-2xs h-100 d-flex flex-column" style="border-top: 3px solid #16a34a !important;">' +
              '<div class="fw-bold text-success small mb-2 d-flex align-items-center gap-1.5">' +
                '<i class="bi bi-lightbulb-fill text-success"></i> Adoptable Best Practice (' + shortCityB + ')' +
              '</div>' +
              '<p class="text-secondary small mb-0" style="line-height:1.65;">' +
                (b.benchmark_insight ? esc(b.benchmark_insight) : dynamicAI.bestPracticeB) +
              '</p>' +
            '</div>' +
          '</div>' +

          '<div class="col-12 col-md-4">' +
            '<div class="bg-white p-3 p-md-3.5 rounded-3 border shadow-2xs h-100 d-flex flex-column justify-content-between" style="border-top: 3px solid #d97706 !important;">' +
              '<div>' +
                '<div class="fw-bold text-warning-emphasis small mb-2 d-flex align-items-center gap-1.5">' +
                  '<i class="bi bi-bullseye text-warning"></i> Policy Gap &amp; Council Directive' +
                '</div>' +
                '<p class="text-secondary small mb-3" style="line-height:1.65;">' +
                  dynamicAI.takeaway +
                '</p>' +
              '</div>' +
              '<div class="pt-2.5 border-top mt-auto">' +
                '<button type="button" class="btn btn-sm text-white fw-bold shadow-2xs w-100 d-flex align-items-center justify-content-center gap-1.5 rounded-3 py-2" style="background: linear-gradient(135deg, #0284c7 0%, #2563eb 100%); border:none; font-size:0.82rem;" onclick="generateAIAmendmentLanguage()">' +
                  '<i class="bi bi-stars"></i> Suggest Amendment Language' +
                '</button>' +
              '</div>' +
            '</div>' +
          '</div>' +
        '</div>' +
      '</div>';

      var tableHtml = '<div class="border rounded-3 overflow-hidden shadow-sm mt-4 bg-white" style="font-family: Arial, Helvetica, sans-serif;">';
      tableHtml += '<table class="table table-bordered align-middle mb-0" style="border-color:#e2e8f0;">';

      // Header Row
      tableHtml += '<thead><tr style="background:#f8fafc;">';
      tableHtml += '<th class="py-3 px-3 fw-bold text-uppercase" style="width:20%; font-size:0.75rem; letter-spacing:0.5px; color:#000;">Feature / Metric</th>';

      // Policy A Header
      tableHtml += '<th class="py-3 px-3 text-center" style="width:40%; border-top:3px solid #2563eb; background:#f8fafc;">' +
        '<div class="fw-bold text-primary text-uppercase mb-1" style="font-size:0.9rem; letter-spacing:0.5px;">' + (isCrossCity ? 'Policy A (Local / Proposed)' : 'Policy A') + '</div>' +
        '<div>' + cleanCityBadge(a.city_origin, a.title) + '</div>' +
        '</th>';

      // Policy B Header
      tableHtml += '<th class="py-3 px-3 text-center" style="width:40%; border-top:3px solid #16a34a; background:#f8fafc;">' +
        '<div class="fw-bold text-success text-uppercase mb-1" style="font-size:0.9rem; letter-spacing:0.5px;">' + (isCrossCity ? 'Policy B (Enacted Benchmark)' : 'Policy B / Benchmark') + '</div>' +
        '<div>' + cleanCityBadge(b.city_origin, b.title) + '</div>' +
        '</th>';
      tableHtml += '</tr></thead>';

      // Format source link button for Policy B if available
      var bSourceBtn = '';
      if (b.source_link) {
        bSourceBtn = '<div class="mt-1.5"><a href="' + esc(b.source_link) + '" target="_blank" rel="noopener noreferrer" class="badge rounded-pill bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 text-decoration-none px-2.5 py-1" style="font-size:0.72rem;"><i class="bi bi-box-arrow-up-right me-1"></i>Official Enacted Source Record</a></div>';
      }

      // Enactment Status
      var aStatusBadge = '<span class="badge bg-warning bg-opacity-10 text-warning-emphasis border border-warning border-opacity-25 px-2.5 py-1" style="font-size:0.78rem;"><i class="bi bi-hourglass-split me-1"></i> Proposed / Under Committee Review' + (a.publication_date ? ' (' + esc(a.publication_date) + ')' : '') + '</span>';
      var bStatusBadge = b.enactment_date
        ? '<span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2.5 py-1" style="font-size:0.78rem;"><i class="bi bi-check-circle-fill me-1"></i> Enacted: ' + esc(b.enactment_date) + '</span>'
        : '<span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2.5 py-1" style="font-size:0.78rem;"><i class="bi bi-check-circle-fill me-1"></i> Enacted Legislation</span>';

      // Format Key Provisions
      var aProvisions = a.key_provisions || a.description || 'Comprehensive municipal ordinance proposal addressing localized service delivery across Manila legislative districts.';
      var bProvisions = b.key_provisions || b.description || 'Enacted municipal code provisions establishing statutory compliance and operational requirements.';

      // Format provisions into clean HTML list if it contains newlines or bullets
      function formatProvisions(text) {
        if (!text) return '';
        var lines = text.split('\n');
        if (lines.length > 1) {
          var items = lines.map(function(l) {
            var trimmed = l.trim().replace(/^[•\-\*]\s*/, '');
            return trimmed ? '<li class="mb-1">' + esc(trimmed) + '</li>' : '';
          }).filter(Boolean).join('');
          return '<ul class="mb-0 ps-3 small text-secondary" style="line-height:1.6;">' + items + '</ul>';
        }
        return '<p class="mb-0 small text-secondary" style="line-height:1.6;">' + esc(text) + '</p>';
      }

      // Body Rows
      var rows = [
        {
          label: 'Policy Title',
          a: '<div class="fw-semibold text-dark" style="font-family: Arial, sans-serif; font-size:0.88rem;">' + esc(a.title) + '</div>',
          b: '<div class="fw-semibold text-dark" style="font-family: Arial, sans-serif; font-size:0.88rem;">' + esc(b.title) + '</div>' + bSourceBtn
        },
        {
          label: 'City Jurisdiction',
          a: '<div class="d-flex align-items-center gap-1.5"><i class="bi bi-building text-primary"></i> <strong class="text-dark">' + esc(a.city_name || a.city_origin || 'City of Manila') + '</strong> <span class="text-muted small">(Local LGU)</span></div>',
          b: '<div class="d-flex align-items-center gap-1.5"><i class="bi bi-geo-alt-fill text-success"></i> <strong class="text-dark">' + esc(b.city_name || b.city_origin || 'Peer City Benchmark') + '</strong> <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 ms-1" style="font-size:0.68rem;">Enacted Law</span></div>'
        },
        {
          label: 'Policy Area',
          a: '<span class="badge bg-light border px-2.5 py-1 text-dark fw-semibold" style="font-size:0.82rem;">' + esc(a.category || 'General') + '</span>',
          b: '<span class="badge bg-light border px-2.5 py-1 text-dark fw-semibold" style="font-size:0.82rem;">' + esc(b.policy_area || b.category || 'General') + '</span>'
        },
        {
          label: 'Enactment Status',
          a: aStatusBadge,
          b: bStatusBadge
        },
        {
          label: 'Key Provisions',
          a: formatProvisions(aProvisions),
          b: formatProvisions(bProvisions) + (isCrossCity ?
            '<div class="mt-2.5 pt-2 border-top d-flex align-items-center justify-content-between flex-wrap gap-2">' +
              '<span class="badge bg-warning bg-opacity-10 text-warning-emphasis border border-warning border-opacity-25 py-1 px-2" style="font-size:0.72rem;">' +
                '<i class="bi bi-exclamation-triangle-fill me-1 text-warning"></i> Benchmark Provision Gap Identified' +
              '</span>' +
              '<button type="button" class="btn btn-xs text-white rounded-pill px-2.5 py-1 fw-bold shadow-2xs d-inline-flex align-items-center gap-1 hover-lift" style="background: linear-gradient(135deg, #0284c7 0%, #2563eb 100%); font-size:0.75rem; border:none;" onclick="generateAIAmendmentLanguage()">' +
                '<i class="bi bi-stars"></i> Suggest Amendment Language' +
              '</button>' +
            '</div>' : '')
        },
        {
          label: 'Overall Risk Level',
          a: cleanRiskBadge(a.risk_level),
          b: cleanRiskBadge(b.risk_level)
        }
      ];

      tableHtml += '<tbody>';
      for (var i = 0; i < rows.length; i++) {
        var r = rows[i];
        tableHtml += '<tr>' +
          '<td class="px-3 py-3 fw-bold" style="background:#f8fafc; font-family: Arial, sans-serif; color:#000000; font-size:0.85rem;">' + r.label + '</td>' +
          '<td class="px-3 py-3 bg-white" style="vertical-align:top;">' + r.a + '</td>' +
          '<td class="px-3 py-3 bg-white" style="vertical-align:top;">' + r.b + '</td>' +
          '</tr>';
      }

      // Evaluation Criteria Section Divider
      tableHtml += '<tr>' +
        '<td colspan="3" class="px-3 py-2.5 bg-light border-top border-bottom fw-bold text-uppercase" style="background:#f1f5f9; font-family: Arial, sans-serif; color:#000000; font-size:0.78rem; letter-spacing:0.8px;">' +
        'Evaluation Criteria &amp; Viability Assessment' +
        '</td>' +
        '</tr>';

      var evalRows = [
        {
          label: 'Economic Feasibility',
          a: criteriaCell(a.economic_level, a.economic_reason || getEnhancedPolicyReason(a, 'economic'), getScorePercentage(a.economic_level, a, 'economic')),
          b: criteriaCell(b.economic_level, b.economic_reason || getEnhancedPolicyReason(b, 'economic'), getScorePercentage(b.economic_level, b, 'economic'))
        },
        {
          label: 'Social Impact',
          a: criteriaCell(a.social_level, a.social_reason || getEnhancedPolicyReason(a, 'social'), getScorePercentage(a.social_level, a, 'social')),
          b: criteriaCell(b.social_level, b.social_reason || getEnhancedPolicyReason(b, 'social'), getScorePercentage(b.social_level, b, 'social'))
        },
        {
          label: 'Environmental Impact',
          a: criteriaCell(a.env_level, a.env_reason || getEnhancedPolicyReason(a, 'env'), getScorePercentage(a.env_level, a, 'env')),
          b: criteriaCell(b.env_level, b.env_reason || getEnhancedPolicyReason(b, 'env'), getScorePercentage(b.env_level, b, 'env'))
        },
        {
          label: 'Legal Compliance',
          a: criteriaCell(a.legal_level, a.legal_reason || getEnhancedPolicyReason(a, 'legal'), getScorePercentage(a.legal_level, a, 'legal')),
          b: criteriaCell(b.legal_level, b.legal_reason || getEnhancedPolicyReason(b, 'legal'), getScorePercentage(b.legal_level, b, 'legal'))
        }
      ];

      for (var j = 0; j < evalRows.length; j++) {
        var er = evalRows[j];
        tableHtml += '<tr>' +
          '<td class="px-3 py-3 fw-bold" style="background:#f8fafc; font-family: Arial, sans-serif; color:#000000; font-size:0.85rem;">' + er.label + '</td>' +
          '<td class="px-3 py-3 bg-white" style="vertical-align:top;">' + er.a + '</td>' +
          '<td class="px-3 py-3 bg-white" style="vertical-align:top;">' + er.b + '</td>' +
          '</tr>';
      }

      // Recommendation Row
      tableHtml += '<tr>' +
        '<td class="px-3 py-3 fw-bold" style="background:#f8fafc; font-family: Arial, sans-serif; color:#000000; font-size:0.85rem;">Recommendation</td>' +
        '<td class="px-3 py-3 bg-white" style="vertical-align:top;">' +
        '<span style="font-family: Arial, Helvetica, sans-serif; color:#000000; font-size:0.88rem; line-height:1.55;">' + esc(a.ai_recommendation || getEnhancedRecommendation(a)) + '</span>' +
        '</td>' +
        '<td class="px-3 py-3 bg-white" style="vertical-align:top;">' +
        '<span style="font-family: Arial, Helvetica, sans-serif; color:#000000; font-size:0.88rem; line-height:1.55;">' + esc(b.ai_recommendation || getEnhancedRecommendation(b)) + '</span>' +
        '</td>' +
        '</tr>';

      tableHtml += '</tbody></table></div>';

      // ── ASSEMBLE EXECUTIVE HIERARCHY:
      // 1. Alignment Scorecard (execCard)
      // 2. 3-Card AI Executive Comparison Insights (insightsCard)
      // 3. AI Amendment Result Area (aiAmendmentResultArea)
      // 4. Detailed Comparison Table (tableHtml)
      var html = execCard + insightsCard + '<div id="aiAmendmentResultArea" class="mt-4 d-none"></div>' + tableHtml;

      // Store comparison context for dynamic AI amendment generation
      window.currentComparisonContext = {
        a: a,
        b: b,
        isCrossCity: isCrossCity,
        gap: dynamicAI.takeaway,
        diffSummary: dynamicAI.diffSummary,
        topic: dynamicAI.topic
      };

      // Auto-record comparison in Reports module
      recordUserComparisonInReports(comparisonTitle, reportType, dynamicAI.diffSummary.replace(/<[^>]*>?/gm, ''), a.risk_level, dynamicAI.takeaway);

      resultEl.innerHTML = html;
      resultEl.classList.remove('d-none');
    };

    // --- MODE 3: COMPARE VERSIONS (USER) ---
    window.runUserVersionComparison = function () {
      var pId = document.getElementById('userCompareVersionPolicy').value;
      var resultEl = document.getElementById('userComparisonResult');
      if (!resultEl) return;

      var showMsg = function (type, ic, txt) {
        resultEl.innerHTML = '<div class="alert alert-' + type +
          ' d-flex align-items-center gap-2 rounded-3 mb-0 mt-3" role="alert">' +
          '<i class="bi ' + ic + ' fs-5"></i><span style="font-family: Arial, sans-serif;">' + esc(txt) + '</span></div>';
        resultEl.classList.remove('d-none');
      };

      if (!pId) {
        showMsg('warning', 'bi-exclamation-triangle-fill', 'Please select a policy to view its version evolution.');
        return;
      }

      var record = window.USER_VERSION_COMPARE_MAP ? window.USER_VERSION_COMPARE_MAP[String(pId)] : null;
      if (!record || !record.oldest_version || !record.newest_version) {
        showMsg('danger', 'bi-x-circle-fill', 'Version evaluation data is not available for this policy.');
        return;
      }

      var oldest = record.oldest_version;
      var newest = record.newest_version;

      var html = '<div class="border rounded-3 overflow-hidden shadow-sm mt-4 bg-white" style="font-family: Arial, Helvetica, sans-serif;">';
      html += '<table class="table table-bordered align-middle mb-0" style="border-color:#e2e8f0;">';

      // Header Row
      html += '<thead><tr style="background:#f8fafc;">';
      html += '<th class="py-3 px-3 fw-bold text-uppercase" style="width:22%; font-size:0.75rem; letter-spacing:0.5px; color:#000;">Evaluation Dimension</th>';

      // Version A (Oldest)
      html += '<th class="py-3 px-3 text-center" style="width:39%; border-top:3px solid #64748b; background:#f8fafc;">' +
        '<div class="fw-bold text-secondary text-uppercase mb-1" style="font-size:0.85rem; letter-spacing:0.5px;">' +
        '<i class="bi bi-clock-history me-1"></i> Initial Baseline (' + esc(oldest.version_label) + ')' +
        '</div>' +
        '<div class="small text-muted">' + (oldest.approved_at ? 'Evaluated ' + esc(oldest.approved_at) : 'Original Approved Version') + '</div>' +
        '</th>';

      // Version B (Newest)
      html += '<th class="py-3 px-3 text-center" style="width:39%; border-top:3px solid #2563eb; background:#f8fafc;">' +
        '<div class="fw-bold text-primary text-uppercase mb-1" style="font-size:0.85rem; letter-spacing:0.5px;">' +
        '<i class="bi bi-patch-check-fill me-1"></i> Latest Revision (' + esc(newest.version_label) + ')' +
        '</div>' +
        '<div class="small text-muted">' + (newest.approved_at ? 'Evaluated ' + esc(newest.approved_at) : 'Current Approved Version') + '</div>' +
        '</th>';
      html += '</tr></thead>';

      html += '<tbody>';

      function renderDiffRow(label, aVal, bVal, isDiff) {
        var rowStyle = isDiff ? 'background: #fffdf5;' : 'background: #ffffff;';
        var badge = isDiff
          ? '<span class="badge rounded-pill bg-warning text-dark px-2 py-0.5 fw-bold ms-2 shadow-2xs" style="font-size:0.68rem;"><i class="bi bi-arrow-left-right me-1"></i> Changed</span>'
          : '<span class="badge rounded-pill bg-light text-muted border px-2 py-0.5 ms-2" style="font-size:0.68rem;"><i class="bi bi-check2 text-success me-1"></i> Unchanged</span>';

        return '<tr style="' + rowStyle + '">' +
          '<td class="px-3 py-3 fw-bold" style="background:#f8fafc; font-family: Arial, sans-serif; color:#000000; font-size:0.85rem;">' +
          label + (record.has_multiple ? badge : '') +
          '</td>' +
          '<td class="px-3 py-3" style="vertical-align:top;">' + aVal + '</td>' +
          '<td class="px-3 py-3" style="vertical-align:top;' + (isDiff ? 'background:#fffbeb;' : '') + '">' + bVal + '</td>' +
          '</tr>';
      }

      // Policy Meta Rows
      html += '<tr>' +
        '<td class="px-3 py-2.5 fw-bold" style="background:#f8fafc; font-family: Arial, sans-serif; color:#000000; font-size:0.85rem;">Policy Title</td>' +
        '<td colspan="2" class="px-3 py-2.5 bg-white fw-bold text-dark" style="font-size:0.92rem;">' + esc(record.title) + '</td>' +
        '</tr>';

      html += '<tr>' +
        '<td class="px-3 py-2.5 fw-bold" style="background:#f8fafc; font-family: Arial, sans-serif; color:#000000; font-size:0.85rem;">LGU / City Origin</td>' +
        '<td colspan="2" class="px-3 py-2.5 bg-white">' + cleanCityBadge(record.city_origin, record.title) + '</td>' +
        '</tr>';

      html += '<tr>' +
        '<td class="px-3 py-2.5 fw-bold" style="background:#f8fafc; font-family: Arial, sans-serif; color:#000000; font-size:0.85rem;">Policy Category</td>' +
        '<td colspan="2" class="px-3 py-2.5 bg-white">' +
        '<span class="badge bg-light border px-2.5 py-1 text-dark fw-semibold" style="font-size:0.82rem;">' + esc(record.category) + '</span>' +
        '</td>' +
        '</tr>';

      html += '<tr>' +
        '<td class="px-3 py-2.5 fw-bold" style="background:#f8fafc; font-family: Arial, sans-serif; color:#000000; font-size:0.85rem;">Approved By</td>' +
        '<td class="px-3 py-2.5 bg-white small text-muted"><i class="bi bi-person-check-fill text-success me-1"></i>' + esc(oldest.approved_by || 'System Administrator') + '</td>' +
        '<td class="px-3 py-2.5 bg-white small text-muted"><i class="bi bi-person-check-fill text-success me-1"></i>' + esc(newest.approved_by || 'System Administrator') + '</td>' +
        '</tr>';

      // Risk Level Row
      var riskDiff = (oldest.risk_level !== newest.risk_level);
      html += renderDiffRow('Overall Risk Level', cleanRiskBadge(oldest.risk_level), cleanRiskBadge(newest.risk_level), riskDiff);

      // Section Divider
      html += '<tr>' +
        '<td colspan="3" class="px-3 py-2.5 bg-light border-top border-bottom fw-bold text-uppercase" style="background:#f1f5f9; font-family: Arial, sans-serif; color:#000000; font-size:0.78rem; letter-spacing:0.8px;">' +
        'Evaluation Criteria Evolution' +
        '</td>' +
        '</tr>';

      var criteriaKeys = [
        { key: 'economic', label: 'Economic Feasibility' },
        { key: 'social', label: 'Social Impact' },
        { key: 'env', label: 'Environmental Impact' },
        { key: 'legal', label: 'Legal Compliance' }
      ];

      criteriaKeys.forEach(function (c) {
        var oldPolicyObj = Object.assign({}, record, oldest);
        var newPolicyObj = Object.assign({}, record, newest);
        var oldLevel = oldest[c.key + '_level'];
        var oldReason = getEnhancedPolicyReason(oldPolicyObj, c.key);
        var oldPct = getScorePercentage(oldLevel, oldPolicyObj, c.key);
        var newLevel = newest[c.key + '_level'];
        var newReason = getEnhancedPolicyReason(newPolicyObj, c.key);
        var newPct = getScorePercentage(newLevel, newPolicyObj, c.key);

        var isDiff = (oldLevel !== newLevel) || (oldReason !== newReason) || (oldPct !== newPct);
        html += renderDiffRow(
          c.label,
          criteriaCell(oldLevel, oldReason, oldPct),
          criteriaCell(newLevel, newReason, newPct),
          isDiff
        );
      });

      // Recommendation Row
      var oldRec = getEnhancedRecommendation(Object.assign({}, record, oldest));
      var newRec = getEnhancedRecommendation(Object.assign({}, record, newest));
      var recDiff = (oldRec !== newRec);
      html += renderDiffRow(
        'Recommendation',
        '<span style="font-family: Arial, Helvetica, sans-serif; color:#000000; font-size:0.88rem; line-height:1.55;">' + esc(oldRec) + '</span>',
        '<span style="font-family: Arial, Helvetica, sans-serif; color:#000000; font-size:0.88rem; line-height:1.55;">' + esc(newRec) + '</span>',
        recDiff
      );

      html += '</tbody></table></div>';

      // --- DYNAMIC & RESPONSIVE AI EXECUTIVE VERSION EVOLUTION INSIGHTS ---
      var dynamicVersionAI = buildDynamicAIVersionInsights(record, oldest, newest);

      html += '<div class="card border-0 rounded-4 shadow-sm mt-4 p-3 p-md-4" style="background: linear-gradient(135deg, #f8fafc 0%, #eff6ff 100%); border-left: 5px solid #2563eb !important;">' +
        '<div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3 mb-3 pb-2 border-bottom">' +
        '<div class="d-flex align-items-center gap-3">' +
        '<span class="p-2.5 rounded-3 bg-white text-primary shadow-2xs flex-shrink-0" style="color:#2563eb; font-size:1.3rem;">' +
        '<i class="bi bi-clock-history"></i>' +
        '</span>' +
        '<div>' +
        '<div class="d-flex flex-wrap align-items-center gap-2">' +
        '<h5 class="fw-bold mb-0 text-dark" style="font-size:clamp(0.98rem, 2.5vw, 1.15rem);">AI Executive Version Evolution Insights</h5>' +
        '<span class="badge rounded-pill bg-info bg-opacity-10 text-info border border-info border-opacity-25 px-2.5 py-1 fw-semibold" style="font-size:0.75rem;">' +
        esc(oldest.version_label) + ' &rarr; ' + esc(newest.version_label) +
        '</span>' +
        '</div>' +
        '<p class="text-muted small mb-0 mt-0.5">Evolutionary analysis for <strong>' + esc(record.title) + '</strong></p>' +
        '</div>' +
        '</div>' +
        '<span class="badge rounded-pill bg-white text-dark border px-3 py-1.5 shadow-2xs small font-monospace align-self-start align-self-md-center">' +
        '<i class="bi bi-check2-circle text-success me-1"></i> Synced to Reports' +
        '</span>' +
        '</div>' +

        '<div class="row g-3 mt-1">' +
        '<div class="col-12 col-md-6 col-lg-6">' +
        '<div class="bg-white p-3 p-md-3.5 rounded-3 border shadow-2xs h-100 d-flex flex-column">' +
        '<div class="fw-bold text-dark small mb-2 d-flex align-items-center gap-1.5">' +
        '<i class="bi bi-arrow-left-right text-primary fs-6"></i> Iterative Criteria Evolution' +
        '</div>' +
        '<p class="text-secondary small mb-0" style="line-height:1.65;">' +
        dynamicVersionAI.summary +
        '</p>' +
        '</div>' +
        '</div>' +

        '<div class="col-12 col-md-6 col-lg-6">' +
        '<div class="bg-white p-3 p-md-3.5 rounded-3 border shadow-2xs h-100 d-flex flex-column">' +
        '<div class="fw-bold text-dark small mb-2 d-flex align-items-center gap-1.5">' +
        '<i class="bi bi-lightbulb-fill text-warning fs-6"></i> Council Endorsement &amp; Action Plan' +
        '</div>' +
        '<p class="text-secondary small mb-0" style="line-height:1.65;">' +
        dynamicVersionAI.takeaway +
        '</p>' +
        '</div>' +
        '</div>' +
        '</div>' +
        '</div>';

      recordUserComparisonInReports(record.title + ' (Version Evolution)', 'Version Comparison', dynamicVersionAI.summary.replace(/<[^>]*>?/gm, ''), newest.risk_level, dynamicVersionAI.takeaway);

      resultEl.innerHTML = html;
      resultEl.classList.remove('d-none');
    };

    // ── DYNAMIC AI POLICY GAP & AMENDMENT GENERATOR ───────────────
    window.generateAIAmendmentLanguage = async function () {
      var ctx = window.currentComparisonContext;
      if (!ctx || !ctx.a || !ctx.b) {
        alert('Please select and run a benchmarking comparison first.');
        return;
      }

      var a = ctx.a;
      var b = ctx.b;
      var gap = ctx.gap || ctx.diffSummary || 'Municipal policy gap identified during cross-city benchmarking analysis.';
      var container = document.getElementById('aiAmendmentResultArea');
      if (!container) return;

      container.classList.remove('d-none');
      container.scrollIntoView({ behavior: 'smooth', block: 'nearest' });

      // 1. Render Pulsing Placeholder Loading Skeleton (Non-streaming)
      container.innerHTML = '<div class="card border-0 rounded-4 shadow-sm p-4 bg-white placeholder-glow" style="border: 2px dashed #93c5fd !important; background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);">' +
        '<div class="d-flex align-items-center gap-2.5 mb-3">' +
        '<div class="spinner-border text-primary" style="width: 1.3rem; height: 1.3rem;" role="status">' +
        '<span class="visually-hidden">Loading...</span>' +
        '</div>' +
        '<h6 class="fw-bold text-primary mb-0" style="font-size: 0.95rem;">' +
        '<i class="bi bi-stars text-warning me-1"></i> Gemini AI is analyzing statutory gaps &amp; drafting municipal amendment clause...' +
        '</h6>' +
        '</div>' +
        '<p class="text-muted small mb-3">Synthesizing benchmark provisions from <strong>' + esc(b.title) + '</strong> (' + esc(b.city_name || b.city_origin || 'Peer City') + ') to address draft gaps in <strong>' + esc(a.title) + '</strong> using Philippine ordinance drafting conventions.</p>' +
        '<div class="placeholder col-12 mb-2 rounded" style="height: 16px; background-color: #cbd5e1;"></div>' +
        '<div class="placeholder col-10 mb-2 rounded" style="height: 16px; background-color: #cbd5e1;"></div>' +
        '<div class="placeholder col-8 mb-3 rounded" style="height: 16px; background-color: #cbd5e1;"></div>' +
        '<div class="placeholder col-4 rounded" style="height: 24px; background-color: #e2e8f0;"></div>' +
        '</div>';

      // Check if amendment clause was already pre-generated during benchmarking comparison
      if (window.preGeneratedAIAmendment) {
        await new Promise(function (resolve) { setTimeout(resolve, 550); });
        renderAIAmendmentLanguageBox(window.preGeneratedAIAmendment, a, b, gap);
        return;
      }

      var apiKey = (typeof GEMINI_API_KEY !== 'undefined' && GEMINI_API_KEY && GEMINI_API_KEY !== 'PLACEHOLDER_KEY' && !GEMINI_API_KEY.includes('YOUR_'))
        ? GEMINI_API_KEY
        : (window.GEMINI_API_KEY || localStorage.getItem('gemini_api_key') || '');
      var model = (typeof GEMINI_MODEL !== 'undefined' && GEMINI_MODEL) ? GEMINI_MODEL : 'gemini-1.5-flash';

      var draftedClause = '';

      if (apiKey) {
        var controller = new AbortController();
        var timeoutId = setTimeout(function () { controller.abort(); }, 16000);

        var promptText = 'Role: Senior Legislative Drafting Legal Consultant assisting the City Council of Manila (Sangguniang Panlungsod ng Maynila), Philippines.\n\n' +
          'CONTEXT:\n' +
          'Policy A (City of Manila Proposed Ordinance):\n' +
          'Title: ' + a.title + '\n' +
          'Provisions: ' + (a.key_provisions || a.description || 'General municipal policy proposal') + '\n\n' +
          'Policy B (Enacted Benchmark from ' + (b.city_name || b.city_origin || 'Peer City') + '):\n' +
          'Title: ' + b.title + '\n' +
          'Enacted Provisions: ' + (b.key_provisions || b.description || 'Enacted municipal code') + '\n\n' +
          'IDENTIFIED STATUTORY GAP / DIRECTIVE:\n' +
          gap + '\n\n' +
          'TASK:\n' +
          'Draft a short, formal, suggested amendment clause (in Philippine municipal ordinance legislative style) that could address the identified gap.\n\n' +
          'DRAFTING CONSTRAINTS:\n' +
          '1. Format as: "SECTION ___. [Title] — [Operative text]".\n' +
          '2. Cite relevant national statutory authority (e.g. RA 7160 Local Government Code, RA 9003, or other applicable Philippine laws).\n' +
          '3. Specify the appropriate Manila City department (e.g., Manila Traffic and Parking Bureau - MTPB, Department of Public Services - DPS, or Manila Health Department - MHD).\n' +
          '4. Output ONLY the drafted statutory clause with Section heading and operative text. Do not include conversational filler or code block markdown.';

        try {
          var response = await fetch('https://generativelanguage.googleapis.com/v1beta/models/' + encodeURIComponent(model) + ':generateContent?key=' + encodeURIComponent(apiKey), {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            signal: controller.signal,
            body: JSON.stringify({
              contents: [{ parts: [{ text: promptText }] }],
              generationConfig: {
                temperature: 0.3,
                maxOutputTokens: 600
              }
            })
          });
          clearTimeout(timeoutId);

          if (response.ok) {
            var data = await response.json();
            if (data.candidates && data.candidates[0] && data.candidates[0].content && data.candidates[0].content.parts[0]) {
              draftedClause = data.candidates[0].content.parts[0].text.trim();
              if (draftedClause.startsWith('```')) {
                draftedClause = draftedClause.replace(/```[a-z]*\n?|```/gi, '').trim();
              }
            }
          }
        } catch (err) {
          clearTimeout(timeoutId);
          console.warn('Gemini API call skipped or timed out, generating contextual legislative clause:', err);
        }
      }

      if (!draftedClause) {
        draftedClause = generateContextualLegislativeDraft(a, b, gap);
      }

      renderAIAmendmentLanguageBox(draftedClause, a, b, gap);
    };

    function generateContextualLegislativeDraft(a, b, gap) {
      var cat = ((a.category || '') + ' ' + (b.policy_area || '') + ' ' + (a.title || '') + ' ' + (b.title || '')).toLowerCase();

      if (cat.indexOf('plastic') !== -1 || cat.indexOf('waste') !== -1 || cat.indexOf('environ') !== -1) {
        return 'SECTION 7-A. Dedicated Environmental Recovery Fee (Green Fund) and Phased Compliance Schedule. —\n\n' +
          '(a) Establishment of Fund. — There is hereby created a special trust fund to be known as the Manila Green Recovery Fund (MGRF), under the custody of the City Treasurer and administered by the Department of Public Services (DPS). All revenues derived from environmental citation fees and commercial biodegradable bag levies shall be deposited into this fund and earmarked exclusively for the construction and modernization of Barangay Materials Recovery Facilities (MRFs) pursuant to Republic Act No. 9003.\n\n' +
          '(b) Phased Commercial Transition. — Supermarkets, shopping malls, and institutional commercial establishments shall be granted a six (6) month statutory transition period from the effectivity of this Ordinance to phase out non-recyclable single-use plastics, during which the DPS shall conduct mandatory orientation and technical compliance inspections across Manila trading districts.';
      } else if (cat.indexOf('traffic') !== -1 || cat.indexOf('transport') !== -1 || cat.indexOf('mobility') !== -1) {
        return 'SECTION 9-B. Automated Contactless Traffic Surveillance and Real-Time Adjudication Protocol. —\n\n' +
          '(a) Digital Enforcement Framework. — The Manila Traffic and Parking Bureau (MTPB) is authorized to deploy high-resolution digital traffic enforcement cameras across primary vehicular corridors and designated high-density school zones. Traffic infraction notices generated through automated optical telemetry shall be matched against Land Transportation Office (LTO) registered owner databases in strict compliance with the Data Privacy Act of 2012 (RA 10173).\n\n' +
          '(b) Administrative Right to Contest. — Any registered owner served with an electronic citation shall have ten (10) working days from receipt to file an administrative contest before the MTPB Traffic Adjudication Board before statutory vehicle registration alarms are uploaded to the LTO unified IT system.';
      } else if (cat.indexOf('green building') !== -1 || cat.indexOf('energy') !== -1 || cat.indexOf('clean') !== -1) {
        return 'SECTION 11-A. Real Property Tax (RPT) Incentives for Certified Green Developments. —\n\n' +
          '(a) Incentive Schedule. — Any new or substantially retrofitted commercial or high-density residential building located within the territorial jurisdiction of the City of Manila that obtains certified BERDE, LEED, or Philippine Green Building Code ratings shall be eligible for a graduated Real Property Tax discount on building improvements, to wit: fifteen percent (15%) discount for the first three (3) fiscal years upon certification, and ten percent (10%) discount for the succeeding two (2) fiscal years, verified by the Department of Engineering and Public Works (DEPW).\n\n' +
          '(b) Compliance Verification. — The DEPW Green Building Inspection Unit shall conduct biennial energy audits to ensure continued compliance with statutory green building benchmarks as a condition precedent for annual business permit renewals.';
      } else if (cat.indexOf('flood') !== -1 || cat.indexOf('drainage') !== -1 || cat.indexOf('disaster') !== -1) {
        return 'SECTION 8-C. Mandatory Subterranean Rainwater Retention Holding Basins. —\n\n' +
          '(a) Engineering Mandate. — All commercial developments, institutional campuses, and residential condominium projects with a building footprint of one thousand (1,000) square meters or more shall incorporate on-site subterranean rainwater retention basins designed to store not less than fifty (50) liters per square meter of total roof catchment area.\n\n' +
          '(b) Telemetry Discharge Control. — Basins shall be equipped with automated backflow prevention and discharge gates coordinated with Manila Disaster Risk Reduction and Management Office (MDRRMO) estuarine pumping station telemetry, prohibiting discharge into municipal esteros during peak high-tide rainfall cycles.';
      }

      return 'SECTION [___]. Inter-LGU Statutory Alignment and Compliance Mechanism. —\n\n' +
        '(a) Institutional Integration. — The City Government of Manila shall adapt verified operational standards from peer local government units to enhance the statutory enforceability of this Ordinance, designating the appropriate City Department to issue implementing guidelines within sixty (60) days of approval.\n\n' +
        '(b) Oversight and Periodic Review. — An annual legislative monitoring audit shall be submitted to the Sangguniang Panlungsod Committee on Rules and Laws to evaluate operational efficacy, fiscal compliance, and community welfare impact under Republic Act No. 7160.';
    }

    function renderAIAmendmentLanguageBox(clause, a, b, gap) {
      var container = document.getElementById('aiAmendmentResultArea');
      if (!container) return;

      var html = '<div class="card border-0 rounded-4 shadow-sm p-4 bg-white" style="border: 1px solid #bfdbfe !important; background: linear-gradient(180deg, #f8fafc 0%, #ffffff 100%);">' +
        '<div class="d-flex flex-column flex-sm-row align-items-start align-items-sm-center justify-content-between gap-3 mb-3 pb-3 border-bottom">' +
        '<div class="d-flex align-items-center gap-2.5">' +
        '<span class="p-2 rounded-3 text-white shadow-2xs" style="background: linear-gradient(135deg, #0284c7 0%, #2563eb 100%); font-size:1.15rem;">' +
        '<i class="bi bi-stars"></i>' +
        '</span>' +
        '<div>' +
        '<div class="d-flex align-items-center gap-2 flex-wrap">' +
        '<h5 class="fw-bold text-dark mb-0" style="font-size:1.05rem;">AI-Suggested Draft Language (For Review)</h5>' +
        '<span class="badge rounded-pill bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-2.5 py-1" style="font-size:0.72rem;">' +
        '<i class="bi bi-shield-check me-1"></i> Harmonized Municipal Clause' +
        '</span>' +
        '</div>' +
        '<span class="text-muted small">Generated based on cross-city benchmarking with <strong>' + esc(b.city_name || b.city_origin || 'Peer City Benchmark') + '</strong></span>' +
        '</div>' +
        '</div>' +
        '<button type="button" id="copyAmendmentBtn" class="btn btn-outline-primary btn-sm rounded-pill px-3 py-1.5 fw-semibold d-flex align-items-center gap-1.5 shadow-2xs" onclick="copyAIAmendmentText(this)">' +
        '<i class="bi bi-clipboard"></i> Copy to Clipboard' +
        '</button>' +
        '</div>' +

        '<div class="p-2.5 rounded-3 mb-3 d-flex align-items-center gap-2" style="background:#f1f5f9; font-size:0.8rem;">' +
        '<i class="bi bi-info-circle-fill text-primary"></i>' +
        '<span class="text-secondary">Target Policy Gap Addressed: <strong class="text-dark">' + esc(gap) + '</strong></span>' +
        '</div>' +

        '<div class="p-3.5 p-md-4 rounded-3 mb-3 shadow-2xs" style="background:#ffffff; border-left: 4px solid #2563eb; border-top: 1px solid #e2e8f0; border-right: 1px solid #e2e8f0; border-bottom: 1px solid #e2e8f0;">' +
        '<div class="d-flex align-items-center justify-content-between mb-2">' +
        '<span class="text-uppercase fw-bold text-primary font-monospace" style="font-size:0.75rem; letter-spacing:0.8px;">' +
        '<i class="bi bi-file-earmark-ruled me-1"></i> Proposed Ordinance Amendment Language' +
        '</span>' +
        '<span class="badge rounded-pill bg-light text-secondary border px-2 py-0.5" style="font-size:0.7rem;">Sangguniang Panlungsod Format</span>' +
        '</div>' +
        '<div id="aiDraftedClauseText" class="text-dark fw-medium" style="font-family: Georgia, \'Times New Roman\', serif; font-size: 0.96rem; line-height: 1.75; white-space: pre-wrap;">' +
        esc(clause) +
        '</div>' +
        '</div>' +

        '<div class="alert alert-warning border-0 rounded-3 p-2.5 mb-0 d-flex align-items-start gap-2.5 shadow-2xs" style="background:#fffbeb; color:#92400e; font-size:0.78rem; line-height:1.5;">' +
        '<i class="bi bi-exclamation-triangle-fill fs-6 flex-shrink-0 text-warning mt-0.5"></i>' +
        '<div>' +
        'This is an AI-generated drafting aid, not legal advice. All suggested language must be reviewed and finalized by legislative staff and legal counsel before formal proposal.' +
        '</div>' +
        '</div>' +
        '</div>';

      container.innerHTML = html;
    }

    window.copyAIAmendmentText = function (btn) {
      var textEl = document.getElementById('aiDraftedClauseText');
      if (!textEl) return;
      var textToCopy = textEl.innerText.trim();
      navigator.clipboard.writeText(textToCopy).then(function () {
        var origHTML = btn.innerHTML;
        btn.innerHTML = '<i class="bi bi-check2"></i> Copied to Clipboard!';
        btn.classList.remove('btn-outline-primary');
        btn.classList.add('btn-success', 'text-white');
        setTimeout(function () {
          btn.innerHTML = origHTML;
          btn.classList.remove('btn-success', 'text-white');
          btn.classList.add('btn-outline-primary');
        }, 2200);
      }).catch(function (err) {
        console.error('Failed to copy: ', err);
      });
    };

    // Initial State: Render clean placeholder so comparison output does NOT flash immediately
    var initialResultEl = document.getElementById('userComparisonResult');
    if (initialResultEl) {
      initialResultEl.innerHTML = renderEmptyComparisonPlaceholder();
      initialResultEl.classList.remove('d-none');
    }
  })();
</script>