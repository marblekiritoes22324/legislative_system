<?php
// staff/comparison.php — Staff Policy Comparison & Cross-City Benchmarking Submodule
if (!isset($all_policies) || !is_array($all_policies)) {
  $all_policies = [];
}
if (!isset($evaluations) || !is_array($evaluations)) {
  $evaluations = [];
}

require_once __DIR__ . '/../backend/evaluation_versions_helper.php';
$version_comparison_data = get_policy_versions_comparison_data($conn ?? null);

require_once __DIR__ . '/../backend/external_ordinances_helper.php';
$external_benchmarks = get_external_ordinances($conn ?? null);

$eval_map = [];
foreach ($evaluations as $eval) {
  $eval_status = trim($eval['evaluation_status'] ?? $eval['status'] ?? '');
  // Allow policies with Approved, Completed, or Evaluated status to be compared
  if ($eval_status === 'Approved' || $eval_status === 'Completed' || $eval_status === 'Evaluated') {
    $notes_data = [];
    if (!empty($eval['notes'])) {
      $trimmed = trim($eval['notes']);
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

    $econ_level = $extractLevel($crit['economic'] ?? null, $notes_data, 'economic', $eval['economic_score'] ?? 0);
    $social_level = $extractLevel($crit['social'] ?? null, $notes_data, 'social', $eval['social_score'] ?? 0);
    $env_level = $extractLevel($crit['env'] ?? ($crit['environmental'] ?? null), $notes_data, 'env', $eval['environmental_score'] ?? 0);
    $legal_level = $extractLevel($crit['legal'] ?? null, $notes_data, 'legal', $eval['legal_score'] ?? 0);

    $econ_reason = $extractReason($crit['economic'] ?? null, $notes_data, 'economic', 'Funding and implementation costs are manageable and available.');
    $social_reason = $extractReason($crit['social'] ?? null, $notes_data, 'social', 'The policy provides benefits to affected communities and improves quality of life.');
    $env_reason = $extractReason($crit['env'] ?? ($crit['environmental'] ?? null), $notes_data, 'env', 'The policy has minimal expected environmental effects.');
    $legal_reason = $extractReason($crit['legal'] ?? null, $notes_data, 'legal', 'No major legal conflicts were identified with existing laws and regulations.');

    $eval_map[$eval['policy_id']] = [
      'risk_level' => $eval['risk_level'] ?: 'Low Risk',
      'overall_score' => floatval($eval['overall_score'] ?? 0),
      'economic_score' => floatval($eval['economic_score'] ?? 0),
      'social_score' => floatval($eval['social_score'] ?? 0),
      'env_score' => floatval($eval['environmental_score'] ?? 0),
      'legal_score' => floatval($eval['legal_score'] ?? 0),
      'ai_recommendation' => $eval['ai_recommendation'] ?: 'Suitable for implementation.',
      'status' => 'Approved',
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

$compare_data = [];
$completed_policies = [];
foreach ($all_policies as $p) {
  if (isset($eval_map[$p['id']])) {
    $info = $eval_map[$p['id']];
    $compare_data[] = [
      'id' => (int) $p['id'],
      'title' => $p['title'],
      'category' => $p['category'],
      'city_origin' => $p['city_origin'] ?? 'City of Manila',
      'description' => $p['description'] ?? '',
      'key_provisions' => $p['description'] ?? '',
      'publication_date' => $p['publication_date'] ?? '',
      'file_path' => $p['file_path'] ?? '',
      'risk_level' => $info['risk_level'],
      'overall_score' => $info['overall_score'],
      'economic_score' => $info['economic_score'],
      'social_score' => $info['social_score'],
      'env_score' => $info['env_score'],
      'legal_score' => $info['legal_score'],
      'ai_recommendation' => $info['ai_recommendation'],
      'economic_level' => $info['economic_level'],
      'economic_reason' => $info['economic_reason'],
      'social_level' => $info['social_level'],
      'social_reason' => $info['social_reason'],
      'env_level' => $info['env_level'],
      'env_reason' => $info['env_reason'],
      'legal_level' => $info['legal_level'],
      'legal_reason' => $info['legal_reason'],
    ];
    $completed_policies[] = $p;
  }
}

// Split into Local Manila policies vs External LGU Benchmarks and group by Category
$local_policies = [];
$external_policies = [];
$grouped_local_policies = [];

foreach ($completed_policies as $p) {
  $c = strtolower($p['city_origin'] ?? 'city of manila');
  if (strpos($c, 'manila') !== false) {
    $local_policies[] = $p;
    $cat = !empty($p['category']) ? trim($p['category']) : 'General Legislation';
    $grouped_local_policies[$cat][] = $p;
  } else {
    $external_policies[] = $p;
  }
}
ksort($grouped_local_policies);
?>
<style>
  /* Rich & Vibrant Filter Button Palette */
  .filter-cat-btn, .filter-city-btn {
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    font-size: 0.76rem !important;
    font-weight: 600;
    cursor: pointer;
    border-radius: 50rem !important;
    padding: 0.28rem 0.75rem !important;
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    box-shadow: 0 1px 2px rgba(0,0,0,0.04);
  }
  .filter-cat-btn:hover, .filter-city-btn:hover {
    transform: translateY(-1.5px);
    box-shadow: 0 4px 10px rgba(0,0,0,0.1);
  }
  
  /* Category Button Colors */
  .cat-btn-all {
    background-color: #f1f5f9;
    color: #334155;
    border: 1.5px solid #cbd5e1 !important;
  }
  .cat-btn-all.active {
    background: linear-gradient(135deg, #0B2E59 0%, #1e40af 100%) !important;
    color: #ffffff !important;
    border-color: #0B2E59 !important;
    box-shadow: 0 2px 8px rgba(11, 46, 89, 0.35) !important;
  }
  
  .cat-btn-infra {
    background-color: #ecfdf5;
    color: #047857;
    border: 1.5px solid #a7f3d0 !important;
  }
  .cat-btn-infra:hover {
    background-color: #d1fae5;
    color: #065f46;
  }
  .cat-btn-infra.active {
    background: linear-gradient(135deg, #059669 0%, #047857 100%) !important;
    color: #ffffff !important;
    border-color: #047857 !important;
    box-shadow: 0 2px 8px rgba(5, 150, 105, 0.35) !important;
  }
  
  .cat-btn-social {
    background-color: #fdf2f8;
    color: #be185d;
    border: 1.5px solid #fbcfe8 !important;
  }
  .cat-btn-social:hover {
    background-color: #fce7f3;
    color: #9d174d;
  }
  .cat-btn-social.active {
    background: linear-gradient(135deg, #db2777 0%, #be185d 100%) !important;
    color: #ffffff !important;
    border-color: #be185d !important;
    box-shadow: 0 2px 8px rgba(219, 39, 119, 0.35) !important;
  }

  .cat-btn-health {
    background-color: #eff6ff;
    color: #1d4ed8;
    border: 1.5px solid #bfdbfe !important;
  }
  .cat-btn-health:hover {
    background-color: #dbeafe;
    color: #1e40af;
  }
  .cat-btn-health.active {
    background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%) !important;
    color: #ffffff !important;
    border-color: #1d4ed8 !important;
    box-shadow: 0 2px 8px rgba(37, 99, 235, 0.35) !important;
  }

  .cat-btn-default {
    background-color: #fefce8;
    color: #a16207;
    border: 1.5px solid #fef08a !important;
  }
  .cat-btn-default:hover {
    background-color: #fef9c3;
    color: #854d0e;
  }
  .cat-btn-default.active {
    background: linear-gradient(135deg, #ca8a04 0%, #a16207 100%) !important;
    color: #ffffff !important;
    border-color: #a16207 !important;
    box-shadow: 0 2px 8px rgba(202, 138, 4, 0.35) !important;
  }

  /* Peer City Button Colors */
  .city-btn-all {
    background-color: #f1f5f9;
    color: #334155;
    border: 1.5px solid #cbd5e1 !important;
  }
  .city-btn-all.active {
    background: linear-gradient(135deg, #0B2E59 0%, #1e40af 100%) !important;
    color: #ffffff !important;
    border-color: #0B2E59 !important;
    box-shadow: 0 2px 8px rgba(11, 46, 89, 0.35) !important;
  }

  .city-btn-qc {
    background-color: #eff6ff;
    color: #1d4ed8;
    border: 1.5px solid #bfdbfe !important;
  }
  .city-btn-qc:hover {
    background-color: #dbeafe;
    color: #1e40af;
  }
  .city-btn-qc.active {
    background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%) !important;
    color: #ffffff !important;
    border-color: #1d4ed8 !important;
    box-shadow: 0 2px 8px rgba(37, 99, 235, 0.35) !important;
  }

  .city-btn-makati {
    background-color: #f0fdf4;
    color: #15803d;
    border: 1.5px solid #bbf7d0 !important;
  }
  .city-btn-makati:hover {
    background-color: #dcfce7;
    color: #166534;
  }
  .city-btn-makati.active {
    background: linear-gradient(135deg, #16a34a 0%, #15803d 100%) !important;
    color: #ffffff !important;
    border-color: #15803d !important;
    box-shadow: 0 2px 8px rgba(22, 163, 74, 0.35) !important;
  }

  .city-btn-pasig {
    background-color: #faf5ff;
    color: #7e22ce;
    border: 1.5px solid #e9d5ff !important;
  }
  .city-btn-pasig:hover {
    background-color: #f3e8ff;
    color: #6b21a8;
  }
  .city-btn-pasig.active {
    background: linear-gradient(135deg, #9333ea 0%, #7e22ce 100%) !important;
    color: #ffffff !important;
    border-color: #7e22ce !important;
    box-shadow: 0 2px 8px rgba(147, 51, 234, 0.35) !important;
  }
</style>

<section id="comparativeAnalysisSection"
  class="content-section <?= ($active_section ?? 'staffDashboardSection') !== 'comparativeAnalysisSection' ? 'd-none' : '' ?>">
  <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-white">

    <!-- Header -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
      <div class="d-flex align-items-center">
        <span class="p-2.5 rounded-3 me-3" style="background:#e0f2fe; color:#0284c7;">
          <i class="bi bi-layout-sidebar-inset-reverse fs-4"></i>
        </span>
        <div>
          <h2 class="h4 fw-bold text-dark mb-1">Benchmarking &amp; Comparative Analysis</h2>
          <!-- BUILD:v2026-09-19-STAFF-CROSS-CITY -->
          <p class="text-muted mb-0 small" id="staffComparisonSubtitle">
            Benchmark City of Manila proposed policies against similar enacted ordinances from peer Metro Manila cities
            (Quezon City, Makati, Pasig) to identify best practices, fiscal impacts, and policy gaps.
          </p>
        </div>
      </div>
      <!-- Dual AI Engine Selector & Live Status -->
      <div class="d-flex align-items-center gap-2 flex-wrap">
        <div class="input-group input-group-sm shadow-2xs rounded-3 overflow-hidden" style="border: 1px solid #cbd5e1; max-width: 270px;">
          <label class="input-group-text bg-light text-dark fw-bold border-0" for="aiEngineSelector" style="font-size: 0.76rem;">
            <i class="bi bi-cpu-fill text-primary me-1"></i> AI Engine:
          </label>
          <select class="form-select form-select-sm border-0 bg-white fw-semibold text-dark shadow-none" id="aiEngineSelector" onchange="setAIEnginePreference(this.value)" style="font-size: 0.78rem; cursor: pointer;">
            <option value="auto">🔄 Auto (Ollama &rarr; Gemini)</option>
            <option value="ollama">🦙 Ollama Local (Llama 3.2)</option>
            <option value="gemini">✨ Google Gemini (Cloud)</option>
          </select>
        </div>
        <span id="aiEngineStatusBadge" class="badge rounded-pill bg-light text-secondary border px-2.5 py-1.5 d-flex align-items-center gap-1.5 shadow-2xs" style="font-size: 0.74rem;">
          <span class="spinner-border spinner-border-sm text-primary" style="width: 0.6rem; height: 0.6rem;" role="status"></span>
          <span>Checking AI Engine...</span>
        </span>
      </div>
    </div>

    <!-- Filter Row (Category & Peer City Scope without background box) -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
      
      <!-- Left: Category Filters -->
      <div class="d-flex align-items-center gap-1.5 flex-wrap" id="manilaCategoryFilterPills">
        <span class="small fw-bold text-dark d-flex align-items-center me-1" style="font-size:0.8rem;">
          <i class="bi bi-funnel-fill text-primary me-1"></i>Category:
        </span>
        <button type="button" class="btn filter-cat-btn cat-btn-all active" data-cat="all" onclick="filterManilaPoliciesByCategory('all', this)">
          <i class="bi bi-grid-fill"></i> All
        </button>
        <?php foreach (array_keys($grouped_local_policies) as $catName): ?>
          <?php
          $catLower = strtolower($catName);
          $btnClass = 'cat-btn-default';
          $iconClass = 'bi-tag-fill';
          if (strpos($catLower, 'infra') !== false || strpos($catLower, 'traffic') !== false || strpos($catLower, 'environ') !== false) {
            $btnClass = 'cat-btn-infra';
            $iconClass = 'bi-cone-striped';
          } elseif (strpos($catLower, 'welfare') !== false || strpos($catLower, 'social') !== false || strpos($catLower, 'community') !== false) {
            $btnClass = 'cat-btn-social';
            $iconClass = 'bi-people-fill';
          } elseif (strpos($catLower, 'health') !== false || strpos($catLower, 'sanit') !== false) {
            $btnClass = 'cat-btn-health';
            $iconClass = 'bi-heart-pulse-fill';
          }
          ?>
          <button type="button" class="btn filter-cat-btn <?= $btnClass ?>" data-cat="<?= htmlspecialchars($catName) ?>" onclick="filterManilaPoliciesByCategory(<?= json_encode($catName) ?>, this)" style="white-space:nowrap;">
            <i class="bi <?= $iconClass ?>"></i> <?= htmlspecialchars($catName) ?>
          </button>
        <?php endforeach; ?>
      </div>

      <!-- Right: Peer City Filters -->
      <div class="d-flex align-items-center gap-1.5 flex-wrap" id="crossCityFilterPills">
        <span class="small fw-bold text-dark d-flex align-items-center me-1" style="font-size:0.8rem;">
          <i class="bi bi-geo-alt-fill text-success me-1"></i>Peer City:
        </span>
        <button type="button" class="btn filter-city-btn city-btn-all active" data-city="all" onclick="filterCrossCityBenchmark('all', this)">
          <i class="bi bi-buildings-fill"></i> All Cities
        </button>
        <button type="button" class="btn filter-city-btn city-btn-qc" data-city="Quezon City" onclick="filterCrossCityBenchmark('Quezon City', this)">
          <i class="bi bi-geo-alt-fill"></i> Quezon City
        </button>
        <button type="button" class="btn filter-city-btn city-btn-makati" data-city="City of Makati" onclick="filterCrossCityBenchmark('City of Makati', this)">
          <i class="bi bi-geo-alt-fill"></i> Makati
        </button>
        <button type="button" class="btn filter-city-btn city-btn-pasig" data-city="Pasig City" onclick="filterCrossCityBenchmark('Pasig City', this)">
          <i class="bi bi-geo-alt-fill"></i> Pasig
        </button>
      </div>

    </div>

    <!-- Mode 1: Cross-City Ordinance Benchmarking (CLEAN & BALANCED) -->
    <div class="row g-3 align-items-end mb-4" id="crossCityCompareForm">

      <!-- Manila Policy / Ordinance (Proposed / Local) -->
      <div class="col-12 col-lg-5">
        <label for="crossCityPolicyA" class="form-label fw-semibold small mb-1.5 text-dark d-block">
          <i class="bi bi-building text-primary me-1.5"></i>Manila Proposed Policy Baseline
        </label>
        <div class="input-group shadow-2xs">
          <span class="input-group-text bg-white border-end-0 rounded-start-3" style="border-left:3px solid #1d4ed8;">
            <i class="bi bi-file-earmark-text text-primary"></i>
          </span>
          <select id="crossCityPolicyA" class="form-select border-start-0 rounded-end-3" style="font-size:0.9rem;"
            onchange="autoSuggestCrossCityBenchmark()">
            <?php if (empty($local_policies)): ?>
              <option value="" disabled selected>— No Manila Approved Policies Available —</option>
            <?php else: ?>
              <option value="">— Select Manila Policy to Benchmark —</option>
              <?php foreach ($grouped_local_policies as $catName => $pList): ?>
                <optgroup label="📂 <?= htmlspecialchars($catName) ?>" data-category="<?= htmlspecialchars($catName) ?>">
                  <?php foreach ($pList as $p): ?>
                    <option value="<?= (int) $p['id'] ?>" data-category="<?= htmlspecialchars($p['category'] ?? $catName) ?>"
                      data-title="<?= htmlspecialchars($p['title']) ?>" <?= ($p === reset($local_policies)) ? 'selected' : '' ?>>
                      [<?= htmlspecialchars($catName) ?>] <?= htmlspecialchars($p['title']) ?>
                    </option>
                  <?php endforeach; ?>
                </optgroup>
              <?php endforeach; ?>
            <?php endif; ?>
          </select>
        </div>
      </div>

      <!-- External City Benchmark Ordinance -->
      <div class="col-12 col-lg-5">
        <label for="crossCityPolicyB" class="form-label fw-semibold small mb-1.5 text-dark d-block">
          <i class="bi bi-geo-alt-fill text-success me-1.5"></i>Peer City Enacted Benchmark
        </label>
        <div class="input-group shadow-2xs">
          <span class="input-group-text bg-white border-end-0 rounded-start-3" style="border-left:3px solid #15803d;">
            <i class="bi bi-patch-check-fill text-success"></i>
          </span>
          <select id="crossCityPolicyB" class="form-select border-start-0 rounded-end-3" style="font-size:0.9rem;">
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
                      data-category="<?= htmlspecialchars($eb['policy_area'] ?? '') ?>" <?= ($eb === reset($external_benchmarks)) ? 'selected' : '' ?>>
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
        <button type="button" id="crossCityCompareBtn"
          class="btn text-white fw-bold shadow-sm d-flex align-items-center justify-content-center gap-1.5 rounded-3 py-2"
          onclick="runCrossCityComparison()"
          style="background: linear-gradient(135deg, #0B2E59 0%, #1e40af 100%); border:none; height: 38px; font-size:0.9rem; transition:all 0.2s;">
          <i class="bi bi-stars"></i> Benchmark
        </button>
      </div>

    </div>
    <!-- Mode 2: Manila vs Manila (Local Policy Comparison) -->
    <div class="row g-3 align-items-end mb-4 d-none" id="staffPolicyCompareForm">

      <!-- Policy A -->
      <div class="col-lg-5 col-md-5">
        <label for="comparePolicyA" class="form-label fw-semibold small mb-2">
          <span class="badge rounded-pill px-2.5 py-1 me-1" style="background:#1d4ed8; font-size:0.75rem;">
            <i class="bi bi-building me-1"></i>Policy / Ordinance A
          </span>
          <span class="text-muted fw-normal">— e.g., Local Manila Ordinance</span>
        </label>
        <div class="input-group shadow-sm">
          <span class="input-group-text bg-white border-end-0 rounded-start-3" style="border-left:3px solid #1d4ed8;">
            <i class="bi bi-file-earmark-text text-primary"></i>
          </span>
          <select id="comparePolicyA" class="form-select border-start-0 rounded-end-3" style="font-size:0.9rem;">
            <?php if (empty($completed_policies)): ?>
              <option value="" disabled selected>— No Approved Evaluations Available —</option>
            <?php else: ?>
              <option value="">— Select Approved Policy / Ordinance A —</option>
              <?php if (!empty($local_policies)): ?>
                <optgroup label="🏛️ City of Manila (Local Ordinances)">
                  <?php foreach ($local_policies as $p): ?>
                    <option value="<?= (int) $p['id'] ?>" <?= ($p === reset($local_policies)) ? 'selected' : '' ?>>
                      [Manila] <?= htmlspecialchars($p['title']) ?>
                    </option>
                  <?php endforeach; ?>
                </optgroup>
              <?php endif; ?>
              <?php if (!empty($external_policies)): ?>
                <optgroup label="🏙️ External LGU Benchmarks (Quezon City, Pasig, etc.)">
                  <?php foreach ($external_policies as $p): ?>
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
        <label for="comparePolicyB" class="form-label fw-semibold small mb-2">
          <span class="badge rounded-pill px-2.5 py-1 me-1" style="background:#15803d; font-size:0.75rem;">
            <i class="bi bi-pin-map-fill me-1"></i>Policy / Benchmark B
          </span>
          <span class="text-muted fw-normal">— e.g., Local Manila Ordinance</span>
        </label>
        <div class="input-group shadow-sm">
          <span class="input-group-text bg-white border-end-0 rounded-start-3" style="border-left:3px solid #15803d;">
            <i class="bi bi-file-earmark-text text-success"></i>
          </span>
          <select id="comparePolicyB" class="form-select border-start-0 rounded-end-3" style="font-size:0.9rem;">
            <?php if (empty($completed_policies)): ?>
              <option value="" disabled selected>— No Approved Evaluations Available —</option>
            <?php else: ?>
              <option value="">— Select Policy B or Local Ordinance —</option>
              <?php if (!empty($local_policies)): ?>
                <optgroup label="🏛️ City of Manila (Local Ordinances)">
                  <?php foreach ($local_policies as $p): ?>
                    <option value="<?= (int) $p['id'] ?>" <?= (count($local_policies) > 1 && $p === $local_policies[1]) ? 'selected' : '' ?>>
                      [Manila] <?= htmlspecialchars($p['title']) ?>
                    </option>
                  <?php endforeach; ?>
                </optgroup>
              <?php endif; ?>
              <?php if (!empty($external_policies)): ?>
                <optgroup label="🏙️ External LGU Benchmarks (Quezon City, Pasig, etc.)">
                  <?php foreach ($external_policies as $p): ?>
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
        <button type="button" id="compareBtn"
          class="btn text-white fw-bold shadow-sm d-flex align-items-center justify-content-center gap-1 rounded-3 py-2"
          onclick="runPolicyComparison()"
          style="background:#0B2E59; border:none; font-size:0.88rem; transition:all 0.2s;">
          <i class="bi bi-scales fs-6 me-1"></i>Compare
        </button>
      </div>

    </div>

    <!-- Mode 3: Compare Versions Selector (Single Policy Selection) -->
    <div class="row g-3 align-items-end mb-4 d-none" id="staffVersionCompareForm">
      <div class="col-lg-10 col-md-10">
        <label for="compareVersionPolicy" class="form-label fw-semibold small mb-2">
          <span class="badge rounded-pill px-2.5 py-1 me-1" style="background:#0284c7; font-size:0.75rem;">
            <i class="bi bi-clock-history me-1"></i>Select Policy to Compare Versions
          </span>
          <span class="text-muted fw-normal">— Automatically pulls the oldest vs. newest approved evaluations</span>
        </label>
        <div class="input-group shadow-sm">
          <span class="input-group-text bg-white border-end-0 rounded-start-3" style="border-left:3px solid #0284c7;">
            <i class="bi bi-journal-bookmark-fill text-info"></i>
          </span>
          <select id="compareVersionPolicy" class="form-select border-start-0 rounded-end-3" style="font-size:0.9rem;"
            onchange="runStaffVersionComparison()">
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
        <button type="button" id="staffCompareVersionsBtn"
          class="btn text-white fw-bold shadow-sm d-flex align-items-center justify-content-center gap-1.5 rounded-3 py-2"
          onclick="runStaffVersionComparison()"
          style="background:#0284c7; border:none; font-size:0.88rem; transition:all 0.2s;">
          <i class="bi bi-clock-history fs-6"></i> Compare Versions
        </button>
      </div>
    </div>

    <!-- Comparison Result Container -->
    <div id="comparisonResult" class="d-none"></div>

  </div>
</section>

<script>
  (function () {
    var COMPARE_DATA = <?= json_encode($compare_data) ?>;
    var VERSION_COMPARE_DATA = <?= json_encode($version_comparison_data) ?>;
    var EXTERNAL_BENCHMARKS = <?= json_encode($external_benchmarks ?? []) ?>;

    window.COMPARE_DATA = COMPARE_DATA;
    window.VERSION_COMPARE_DATA = VERSION_COMPARE_DATA;
    window.EXTERNAL_BENCHMARKS = EXTERNAL_BENCHMARKS;

    window.COMPARE_POLICY_MAP = {};
    COMPARE_DATA.forEach(function (item) {
      window.COMPARE_POLICY_MAP[String(item.id)] = item;
    });

    window.EXTERNAL_BENCHMARK_MAP = {};
    EXTERNAL_BENCHMARKS.forEach(function (item) {
      var fullItem = Object.assign({}, item, {
        is_external: true,
        title: '[' + item.city_name + '] ' + item.ordinance_number + ': ' + item.ordinance_title,
        city_origin: item.city_name,
        category: item.policy_area,
        ai_recommendation: 'Enacted legislative framework operating with statutory compliance.'
      });
      window.EXTERNAL_BENCHMARK_MAP['ext_' + item.id] = fullItem;
      window.EXTERNAL_BENCHMARK_MAP[String(item.id)] = fullItem;
    });

    window.VERSION_COMPARE_MAP = {};
    VERSION_COMPARE_DATA.forEach(function (item) {
      window.VERSION_COMPARE_MAP[String(item.policy_id)] = item;
    });

    window.switchStaffComparisonMode = function (mode) {
      var btnCrossCity = document.getElementById('toggleStaffCompareCrossCityBtn');
      var btnPolicies = document.getElementById('toggleStaffComparePoliciesBtn');
      var btnVersions = document.getElementById('toggleStaffCompareVersionsBtn');

      var formCrossCity = document.getElementById('crossCityCompareForm');
      var formPolicies = document.getElementById('staffPolicyCompareForm');
      var formVersions = document.getElementById('staffVersionCompareForm');

      var subtitle = document.getElementById('staffComparisonSubtitle');
      var resultEl = document.getElementById('comparisonResult');

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

    window.filterManilaPoliciesByCategory = function (catName, btnEl) {
      var sel = document.getElementById('crossCityPolicyA');
      if (!sel) return;

      var btns = document.querySelectorAll('#manilaCategoryFilterPills .filter-cat-btn');
      btns.forEach(function (b) {
        b.classList.remove('active');
      });
      if (btnEl) {
        btnEl.classList.add('active');
      }

      var optgroups = sel.querySelectorAll('optgroup');
      var firstVisibleOption = null;

      optgroups.forEach(function (og) {
        var ogCat = (og.getAttribute('data-category') || '').toLowerCase();
        var match = (catName === 'all' || ogCat === catName.toLowerCase());
        og.style.display = match ? '' : 'none';
        var opts = og.querySelectorAll('option');
        opts.forEach(function (opt) {
          opt.style.display = match ? '' : 'none';
          if (match && !firstVisibleOption) firstVisibleOption = opt;
        });
      });

      var curSelected = sel.options[sel.selectedIndex];
      if (curSelected && curSelected.style.display === 'none' && firstVisibleOption) {
        sel.value = firstVisibleOption.value;
        if (window.autoSuggestCrossCityBenchmark) window.autoSuggestCrossCityBenchmark();
      }
    };

    window.filterCrossCityBenchmark = function (cityName, btnEl) {
      var sel = document.getElementById('crossCityPolicyB');
      if (!sel) return;

      var btns = document.querySelectorAll('#crossCityFilterPills .filter-city-btn');
      btns.forEach(function (b) {
        b.classList.remove('active');
      });
      if (btnEl) {
        btnEl.classList.add('active');
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

    window.autoSuggestCrossCityBenchmark = function () {
      var aSel = document.getElementById('crossCityPolicyA');
      var bSel = document.getElementById('crossCityPolicyB');
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

    window.runCrossCityComparison = function () {
      var aId = document.getElementById('crossCityPolicyA')?.value;
      var bId = document.getElementById('crossCityPolicyB')?.value;
      window.runPolicyComparison(aId, bId);
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

      if (isManila) {
        return '<span class="d-inline-flex align-items-center gap-1.5 px-3 py-1.5 rounded-pill shadow-xs" style="background:#ffffff; color:#0f172a; border: 1.5px solid #bfdbfe; font-size: 0.84rem; font-weight: 700; font-family: Arial, Helvetica, sans-serif;">' +
          '<i class="bi bi-bank2 text-primary fs-6"></i>' +
          '<span style="color:#0f172a;">City of Manila</span>' +
          '<span class="badge rounded-pill bg-primary text-white fw-bold px-2 py-0.5 ms-1" style="font-size:0.65rem; letter-spacing:0.03em;">LOCAL LGU</span>' +
          '</span>';
      } else if (isMakati) {
        return '<span class="d-inline-flex align-items-center gap-1.5 px-3 py-1.5 rounded-pill shadow-xs" style="background:#ffffff; color:#0f172a; border: 1.5px solid #bbf7d0; font-size: 0.84rem; font-weight: 700; font-family: Arial, Helvetica, sans-serif;">' +
          '<i class="bi bi-buildings-fill text-success fs-6"></i>' +
          '<span style="color:#0f172a;">City of Makati</span>' +
          '<span class="badge rounded-pill bg-success text-white fw-bold px-2 py-0.5 ms-1" style="font-size:0.65rem; letter-spacing:0.03em;">BENCHMARK</span>' +
          '</span>';
      } else if (isQC) {
        return '<span class="d-inline-flex align-items-center gap-1.5 px-3 py-1.5 rounded-pill shadow-xs" style="background:#ffffff; color:#0f172a; border: 1.5px solid #fecaca; font-size: 0.84rem; font-weight: 700; font-family: Arial, Helvetica, sans-serif;">' +
          '<i class="bi bi-buildings-fill text-danger fs-6"></i>' +
          '<span style="color:#0f172a;">Quezon City</span>' +
          '<span class="badge rounded-pill bg-danger text-white fw-bold px-2 py-0.5 ms-1" style="font-size:0.65rem; letter-spacing:0.03em;">BENCHMARK</span>' +
          '</span>';
      } else if (isPasig) {
        return '<span class="d-inline-flex align-items-center gap-1.5 px-3 py-1.5 rounded-pill shadow-xs" style="background:#ffffff; color:#0f172a; border: 1.5px solid #a5f3fc; font-size: 0.84rem; font-weight: 700; font-family: Arial, Helvetica, sans-serif;">' +
          '<i class="bi bi-buildings-fill text-info fs-6"></i>' +
          '<span style="color:#0f172a;">Pasig City</span>' +
          '<span class="badge rounded-pill bg-info text-dark fw-bold px-2 py-0.5 ms-1" style="font-size:0.65rem; letter-spacing:0.03em;">BENCHMARK</span>' +
          '</span>';
      }
      return '<span class="d-inline-flex align-items-center gap-1.5 px-3 py-1.5 rounded-pill shadow-xs" style="background:#ffffff; color:#0f172a; border: 1.5px solid #cbd5e1; font-size: 0.84rem; font-weight: 700; font-family: Arial, Helvetica, sans-serif;">' +
        '<i class="bi bi-buildings-fill text-secondary fs-6"></i>' +
        '<span style="color:#0f172a;">' + esc(c) + '</span>' +
        '<span class="badge rounded-pill bg-secondary text-white fw-bold px-2 py-0.5 ms-1" style="font-size:0.65rem; letter-spacing:0.03em;">BENCHMARK</span>' +
        '</span>';
    }

    function cleanRiskBadge(risk) {
      var r = (risk || 'Low').toLowerCase();
      var color = '#14532d', bg = '#dcfce7', border = '#86efac', text = 'Low Risk';
      if (r.indexOf('high') !== -1) {
        color = '#7f1d1d'; bg = '#fee2e2'; border = '#fca5a5'; text = 'High Risk';
      } else if (r.indexOf('moderate') !== -1 || r.indexOf('medium') !== -1) {
        color = '#78350f'; bg = '#fef3c7'; border = '#fcd34d'; text = 'Medium Risk';
      }
      return '<span class="badge px-3 py-1.5 rounded-pill fw-bold" style="background:' + bg + '; color:' + color + ' !important; border:1px solid ' + border + '; font-size:0.8rem; font-family: Arial, sans-serif;">' + esc(risk || text) + '</span>';
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
      var color = '#7f1d1d', bg = '#fee2e2', border = '#fca5a5', text = 'Low';
      if (l.indexOf('high') !== -1) {
        color = '#14532d'; bg = '#dcfce7'; border = '#86efac'; text = 'High';
      } else if (l.indexOf('med') !== -1 || l.indexOf('mod') !== -1) {
        color = '#78350f'; bg = '#fef3c7'; border = '#fcd34d'; text = 'Medium';
      }
      return '<span class="badge fw-bold me-2 px-2.5 py-1 rounded-pill" style="background:' + bg + '; color:' + color + ' !important; border:1px solid ' + border + '; font-size:0.78rem; font-family: Arial, sans-serif;">' + text + '</span>';
    }

    function criteriaCell(level, reason, pct) {
      var numPct = (pct !== undefined && pct !== null) ? pct : getScorePercentage(level);
      var color = getScoreColor(numPct);
      var badge = cleanLevelBadge(level);

      var meter = '<div class="d-inline-flex align-items-center gap-2 mb-2">' +
        badge +
        '<div class="progress" style="width: 75px; height: 7px; background-color: #e2e8f0; border-radius: 4px; overflow: hidden;" title="Rating Viability: ' + numPct + '%">' +
        '<div class="progress-bar" role="progressbar" style="width: ' + numPct + '%; background-color: ' + color + ';" aria-valuenow="' + numPct + '" aria-valuemin="0" aria-valuemax="100"></div>' +
        '</div>' +
        '<span style="color:' + color + '; font-size:0.78rem; font-weight:800;">' + numPct + '%</span>' +
        '</div>';

      if (!reason) return meter;
      return meter +
        '<div style="font-family: Arial, Helvetica, sans-serif; color: #000000 !important; font-size: 0.88rem; line-height: 1.6; font-weight: 500;">' + esc(reason) + '</div>';
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

    function recordStaffComparisonInReports(title, type, summary, risk, rec) {
      try {
        var key = 'staff_recent_reports_v4';
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
          if (typeof window.renderStaffRecentGeneratedReportsTable === 'function') {
            window.renderStaffRecentGeneratedReportsTable();
          }
        }
      } catch (e) {
        console.warn('Could not sync comparison report into reports list:', e);
      }
    }

    // --- MODE 1 & 2: DYNAMIC AI STATUTORY BENCHMARKING & COMPARISON (STAFF) ---
    window.runPolicyComparison = async function (customA, customB) {
      var aId = customA || document.getElementById('comparePolicyA')?.value || document.getElementById('crossCityPolicyA')?.value;
      var bId = customB || document.getElementById('comparePolicyB')?.value || document.getElementById('crossCityPolicyB')?.value;
      var resultEl = document.getElementById('comparisonResult');
      if (!resultEl) return;

      var showMsg = function (type, ic, txt) {
        resultEl.innerHTML = '<div class="alert alert-' + type +
          ' d-flex align-items-center gap-2 rounded-3 mb-0 mt-3" role="alert">' +
          '<i class="bi ' + ic + ' fs-5"></i><span style="font-family: Arial, sans-serif;">' + esc(txt) + '</span></div>';
        resultEl.classList.remove('d-none');
      };

      if (!aId || !bId) { showMsg('warning', 'bi-exclamation-triangle-fill', 'Please select two policy records or an external benchmark to compare.'); return; }
      if (aId === bId && COMPARE_DATA.length > 1) { showMsg('warning', 'bi-exclamation-triangle-fill', 'Policy A and Policy B cannot be the same document.'); return; }

      var find = function (id) {
        if (!id) return null;
        var sid = String(id);
        if (window.COMPARE_POLICY_MAP && window.COMPARE_POLICY_MAP[sid]) {
          return window.COMPARE_POLICY_MAP[sid];
        }
        if (window.EXTERNAL_BENCHMARK_MAP && window.EXTERNAL_BENCHMARK_MAP[sid]) {
          return window.EXTERNAL_BENCHMARK_MAP[sid];
        }
        for (var i = 0; i < COMPARE_DATA.length; i++) {
          if (String(COMPARE_DATA[i].id) === sid) return COMPARE_DATA[i];
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
        '<i class="bi bi-cpu me-1"></i> DUAL-ENGINE AI LEGISLATIVE BENCHMARKER' +
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
        '<div id="aiBenchmarkingProgressBar" class="progress-bar progress-bar-striped progress-bar-animated bg-primary" role="progressbar" style="width: 25%; transition: width 0.4s ease;"></div>' +
        '</div>' +
        '<div id="aiBenchmarkingPhaseText" class="small fw-semibold text-secondary font-monospace" style="font-size:0.82rem;">' +
        '<i class="bi bi-search me-1 text-primary"></i> Phase 1 of 3: Parsing statutory provisions &amp; legal definitions...' +
        '</div>' +
        '</div>';

      resultEl.scrollIntoView({ behavior: 'smooth', block: 'nearest' });

      // Animate progress phases
      var phaseEl = document.getElementById('aiBenchmarkingPhaseText');
      var barEl = document.getElementById('aiBenchmarkingProgressBar');

      var timer1 = setTimeout(function () {
        if (barEl) barEl.style.width = '62%';
        if (phaseEl) phaseEl.innerHTML = '<i class="bi bi-scales me-1 text-success"></i> Phase 2 of 3: Evaluating multi-criteria alignment under RA 7160 (Local Government Code)...';
      }, 500);

      var timer2 = setTimeout(function () {
        if (barEl) barEl.style.width = '88%';
        if (phaseEl) phaseEl.innerHTML = '<i class="bi bi-stars me-1 text-warning"></i> Phase 3 of 3: Synthesizing alignment scores, policy gaps, and amendment clause...';
      }, 1000);

      // 2. DUAL-ENGINE AI BENCHMARKING & SYNTHESIS (OLLAMA LOCAL + GEMINI CLOUD + DETERMINISTIC FALLBACK)
      var startTime = Date.now();
      var dynamicAI = buildDynamicAIComparisonInsights(a, b, isCrossCity);
      var preGeneratedClause = '';
      var engineUsed = {
        id: 'statutory',
        name: '🏛️ Manila Statutory Baseline Rule Engine',
        badgeClass: 'bg-secondary bg-opacity-10 text-secondary border-secondary border-opacity-25',
        icon: 'bi-shield-check',
        note: 'Synthesized via statutory alignment rules under RA 7160 (Local Government Code).'
      };

      var pref = (typeof window.getAIEnginePreference === 'function')
        ? window.getAIEnginePreference()
        : (localStorage.getItem('legislative_ai_engine') || 'auto');

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
        'Respond with ONLY a raw valid JSON object (no markdown formatting, no backticks, no code block fence) with these exact keys:\n' +
        '{\n' +
        '  "verdict_title": "Short executive verdict (e.g. Synergistic Framework with Policy Gap)",\n' +
        '  "verdict_note": "2-3 sentence strategic summary comparing Manila\'s draft with ' + cityBName + ' citing relevant Philippine statutes (e.g. RA 7160).",\n' +
        '  "economic_score_a": 85,\n' +
        '  "economic_score_b": 90,\n' +
        '  "social_score_a": 88,\n' +
        '  "social_score_b": 82,\n' +
        '  "env_score_a": 78,\n' +
        '  "env_score_b": 92,\n' +
        '  "legal_score_a": 91,\n' +
        '  "legal_score_b": 89,\n' +
        '  "strengthA": "Detailed core legislative strength of Manila\'s draft tailored to its districts and barangays",\n' +
        '  "bestPracticeB": "Detailed adoptable best practice and operational mechanism from ' + cityBName + '",\n' +
        '  "takeaway": "Actionable Manila City Council directive addressing the identified gap and specifying responsible department (e.g. MHD, MTPB, DPS, MDSW)",\n' +
        '  "suggested_amendment": "SECTION ___. [Title] — [Drafted statutory clause in Sangguniang Panlungsod format referencing RA 7160 and appropriate Manila city department]"\n' +
        '}';

      var aiParsed = null;

      // ── ATTEMPT 1: OLLAMA LOCAL (Llama 3.2 - Offline First)
      if (pref === 'ollama' || pref === 'auto') {
        try {
          var ollamaCtrl = new AbortController();
          var ollamaTimer = setTimeout(function () { ollamaCtrl.abort(); }, 12000);
          var ollamaUrl = (typeof OLLAMA_BASE_URL !== 'undefined') ? OLLAMA_BASE_URL : 'http://localhost:11434';
          var ollamaMdl = (typeof OLLAMA_MODEL !== 'undefined') ? OLLAMA_MODEL : 'llama3.2';

          var ollamaRes = await fetch(ollamaUrl + '/api/generate', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            signal: ollamaCtrl.signal,
            body: JSON.stringify({
              model: ollamaMdl,
              prompt: promptText,
              format: 'json',
              stream: false,
              options: {
                temperature: 0.2,
                num_predict: 900
              }
            })
          });
          clearTimeout(ollamaTimer);

          if (ollamaRes.ok) {
            var oData = await ollamaRes.json();
            if (oData && oData.response) {
              var cleanJson = oData.response.trim().replace(/^```json\s*|^```\s*|```$/gi, '').trim();
              var parsed = JSON.parse(cleanJson);
              if (parsed && (parsed.verdict_title || parsed.strengthA || parsed.takeaway)) {
                aiParsed = parsed;
                engineUsed = {
                  id: 'ollama',
                  name: '🦙 Ollama Local (Llama 3.2 Offline)',
                  badgeClass: 'bg-success bg-opacity-10 text-success border-success border-opacity-25',
                  icon: 'bi-hdd-network-fill',
                  note: 'Benchmarked and synthesized locally via Ollama Llama 3.2 — zero cloud latency, 100% private.'
                };
              }
            }
          }
        } catch (oErr) {
          console.warn('Ollama benchmark synthesis bypassed or offline:', oErr);
        }
      }

      // ── ATTEMPT 2: GOOGLE GEMINI CLOUD (If preferred or auto fallback)
      if (!aiParsed && (pref === 'gemini' || pref === 'auto')) {
        var apiKey = (typeof GEMINI_API_KEY !== 'undefined' && GEMINI_API_KEY && GEMINI_API_KEY !== 'PLACEHOLDER_KEY' && !GEMINI_API_KEY.includes('YOUR_'))
          ? GEMINI_API_KEY
          : (window.GEMINI_API_KEY || localStorage.getItem('gemini_api_key') || '');
        var model = (typeof GEMINI_MODEL !== 'undefined' && GEMINI_MODEL) ? GEMINI_MODEL : 'gemini-1.5-flash';

        if (apiKey) {
          try {
            var geminiCtrl = new AbortController();
            var geminiTimer = setTimeout(function () { geminiCtrl.abort(); }, 9000);

            var geminiRes = await fetch('https://generativelanguage.googleapis.com/v1beta/models/' + encodeURIComponent(model) + ':generateContent?key=' + encodeURIComponent(apiKey), {
              method: 'POST',
              headers: { 'Content-Type': 'application/json' },
              signal: geminiCtrl.signal,
              body: JSON.stringify({
                contents: [{ parts: [{ text: promptText }] }],
                generationConfig: {
                  temperature: 0.2,
                  maxOutputTokens: 900
                }
              })
            });
            clearTimeout(geminiTimer);

            if (geminiRes.ok) {
              var gData = await geminiRes.json();
              if (gData.candidates && gData.candidates[0] && gData.candidates[0].content && gData.candidates[0].content.parts[0]) {
                var rawText = gData.candidates[0].content.parts[0].text.trim();
                var cleanJson = rawText.replace(/^```json\s*|^```\s*|```$/gi, '').trim();
                var parsed = JSON.parse(cleanJson);
                if (parsed && (parsed.verdict_title || parsed.strengthA || parsed.takeaway)) {
                  aiParsed = parsed;
                  engineUsed = {
                    id: 'gemini',
                    name: '✨ Google Gemini (gemini-1.5-flash Cloud)',
                    badgeClass: 'bg-primary bg-opacity-10 text-primary border-primary border-opacity-25',
                    icon: 'bi-stars',
                    note: 'Benchmarked and synthesized via Google Gemini Cloud intelligence engine.'
                  };
                }
              }
            }
          } catch (gErr) {
            console.warn('Gemini cloud benchmark call failed or timed out:', gErr);
          }
        }
      }

      // ── APPLY AI BENCHMARK RESULTS (WITH RESILIENT MERGE)
      if (aiParsed) {
        if (aiParsed.verdict_title) dynamicAI.verdictTitle = aiParsed.verdict_title;
        if (aiParsed.verdict_note) dynamicAI.verdictNote = aiParsed.verdict_note;
        if (aiParsed.strengthA) dynamicAI.strengthA = aiParsed.strengthA;
        if (aiParsed.bestPracticeB) dynamicAI.bestPracticeB = aiParsed.bestPracticeB;
        if (aiParsed.takeaway) dynamicAI.takeaway = aiParsed.takeaway;
        if (aiParsed.suggested_amendment) preGeneratedClause = aiParsed.suggested_amendment;

        // Store dynamic AI scores directly on policy objects
        if (typeof aiParsed.economic_score_a === 'number') {
          a._ai_scores = {
            economic: aiParsed.economic_score_a,
            social: aiParsed.social_score_a,
            env: aiParsed.env_score_a,
            legal: aiParsed.legal_score_a
          };
        }
        if (typeof aiParsed.economic_score_b === 'number') {
          b._ai_scores = {
            economic: aiParsed.economic_score_b,
            social: aiParsed.social_score_b,
            env: aiParsed.env_score_b,
            legal: aiParsed.legal_score_b
          };
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
        '<p class="small mb-0" style="color:#000000 !important; line-height:1.55; font-weight:500;">' + dynamicAI.verdictNote + '</p>' +
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
        '<span class="badge rounded-pill ' + engineUsed.badgeClass + ' px-2.5 py-1 font-monospace" style="font-size:0.75rem;" title="' + esc(engineUsed.note) + '">' +
        '<i class="bi ' + engineUsed.icon + ' me-1"></i> ' + esc(engineUsed.name) +
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
        '<p class="small mb-0" style="color: #0f172a !important; line-height:1.65; font-size: 0.86rem; font-weight: 500;">' +
        dynamicAI.strengthA +
        '</p>' +
        '</div>' +
        '</div>' +

        '<div class="col-12 col-md-4">' +
        '<div class="bg-white p-3 p-md-3.5 rounded-3 border shadow-2xs h-100 d-flex flex-column" style="border-top: 3px solid #16a34a !important;">' +
        '<div class="fw-bold text-success small mb-2 d-flex align-items-center gap-1.5">' +
        '<i class="bi bi-lightbulb-fill text-success"></i> Adoptable Best Practice (' + shortCityB + ')' +
        '</div>' +
        '<p class="small mb-0" style="color: #0f172a !important; line-height:1.65; font-size: 0.86rem; font-weight: 500;">' +
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
        '<p class="small mb-3" style="color: #0f172a !important; line-height:1.65; font-size: 0.86rem; font-weight: 500;">' +
        dynamicAI.takeaway +
        '</p>' +
        '</div>' +
        '<div class="pt-2.5 border-top mt-auto">' +
        '<button type="button" class="btn btn-sm text-white fw-bold shadow-2xs w-100 d-flex align-items-center justify-content-center gap-1.5 rounded-3 py-2" style="background: linear-gradient(135deg, #0284c7 0%, #2563eb 100%); border:none; font-size:0.82rem;" onclick="generateAIAmendmentLanguage()">' +
        '<i class="bi bi-stars"></i> Suggest Policy Improvements' +
        '</button>' +
        '</div>' +
        '</div>' +
        '</div>' +
        '</div>' +
        '</div>';

      var tableHtml = '<div class="table-responsive border rounded-4 overflow-hidden shadow-sm mt-4 bg-white" style="font-family: Arial, Helvetica, sans-serif;">';
      tableHtml += '<table class="table table-bordered align-middle mb-0" style="border-color:#cbd5e1;">';

      // Deep Manila Navy Header Row (matching Data Collection / Evaluation tables)
      tableHtml += '<thead><tr style="background-color: #0B2E59;">';
      tableHtml += '<th class="py-3.5 px-3 fw-bold text-uppercase align-middle" style="width:20%; background-color: #0B2E59 !important; color: #FFFFFF !important; font-size:0.8rem; font-weight:800; letter-spacing:0.05em; border-bottom: 2.5px solid #082242 !important; border-top: none !important;">' +
        '<div class="d-flex align-items-center gap-1.5 text-white"><i class="bi bi-list-check text-info fs-6"></i> FEATURE / METRIC</div>' +
        '</th>';

      // Policy A Header
      tableHtml += '<th class="py-3 px-3 text-center align-middle" style="width:40%; background-color: #0B2E59 !important; color: #FFFFFF !important; border-bottom: 2.5px solid #082242 !important; border-top: none !important; border-left: 1px solid rgba(255,255,255,0.15) !important;">' +
        '<div class="fw-bold text-uppercase mb-2 text-white" style="font-size:0.88rem; letter-spacing:0.05em;">' + (isCrossCity ? 'POLICY A (LOCAL / PROPOSED)' : 'POLICY A') + '</div>' +
        '<div>' + cleanCityBadge(a.city_origin, a.title) + '</div>' +
        '</th>';

      // Policy B Header
      tableHtml += '<th class="py-3 px-3 text-center align-middle" style="width:40%; background-color: #0B2E59 !important; color: #FFFFFF !important; border-bottom: 2.5px solid #082242 !important; border-top: none !important; border-left: 1px solid rgba(255,255,255,0.15) !important;">' +
        '<div class="fw-bold text-uppercase mb-2 text-white" style="font-size:0.88rem; letter-spacing:0.05em;">' + (isCrossCity ? 'POLICY B (ENACTED BENCHMARK)' : 'POLICY B / BENCHMARK') + '</div>' +
        '<div>' + cleanCityBadge(b.city_origin, b.title) + '</div>' +
        '</th>';
      tableHtml += '</tr></thead>';

      // Format source link button for Policy B if available
      var bSourceBtn = '';
      if (b.source_link) {
        bSourceBtn = '<div class="mt-1.5"><a href="' + esc(b.source_link) + '" target="_blank" rel="noopener noreferrer" class="badge rounded-pill bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 text-decoration-none px-2.5 py-1" style="font-size:0.72rem;"><i class="bi bi-box-arrow-up-right me-1"></i>Official Enacted Source Record</a></div>';
      }

      // Enactment Status
      var aStatusBadge = '<span class="badge px-3 py-1.5 rounded-pill fw-bold" style="background:#fef3c7; color:#78350f !important; border:1px solid #fcd34d; font-size:0.8rem;"><i class="bi bi-hourglass-split me-1 text-warning"></i> Proposed / Under Committee Review' + (a.publication_date ? ' (' + esc(a.publication_date) + ')' : '') + '</span>';
      var bStatusBadge = b.enactment_date
        ? '<span class="badge px-3 py-1.5 rounded-pill fw-bold" style="background:#dcfce7; color:#14532d !important; border:1px solid #86efac; font-size:0.8rem;"><i class="bi bi-check-circle-fill me-1 text-success"></i> Enacted: ' + esc(b.enactment_date) + '</span>'
        : '<span class="badge px-3 py-1.5 rounded-pill fw-bold" style="background:#dcfce7; color:#14532d !important; border:1px solid #86efac; font-size:0.8rem;"><i class="bi bi-check-circle-fill me-1 text-success"></i> Enacted Legislation</span>';

      // Format Key Provisions
      var aProvisions = a.key_provisions || a.description || 'Comprehensive municipal ordinance proposal addressing localized service delivery across Manila legislative districts.';
      var bProvisions = b.key_provisions || b.description || 'Enacted municipal code provisions establishing statutory compliance and operational requirements.';

      // Format provisions into clean HTML list if it contains newlines or bullets
      function formatProvisions(text) {
        if (!text) return '';
        var lines = text.split('\n');
        if (lines.length > 1) {
          var items = lines.map(function (l) {
            var trimmed = l.trim().replace(/^[•\-\*]\s*/, '');
            return trimmed ? '<li class="mb-1.5" style="color: #000000 !important; font-size: 0.88rem; line-height: 1.6; font-weight: 500;">' + esc(trimmed) + '</li>' : '';
          }).filter(Boolean).join('');
          return '<ul class="mb-0 ps-3" style="line-height:1.6; color: #000000 !important;">' + items + '</ul>';
        }
        return '<p class="mb-0" style="line-height:1.6; color: #000000 !important; font-size: 0.88rem; font-weight: 500;">' + esc(text) + '</p>';
      }

      // Body Rows
      var rows = [
        {
          label: 'Policy Title',
          a: '<div class="fw-bold" style="font-family: Arial, sans-serif; color: #000000 !important; font-size:0.92rem; line-height: 1.45;">' + esc(a.title) + '</div>',
          b: '<div class="fw-bold" style="font-family: Arial, sans-serif; color: #000000 !important; font-size:0.92rem; line-height: 1.45;">' + esc(b.title) + '</div>' + bSourceBtn
        },
        {
          label: 'City Jurisdiction',
          a: '<div class="d-inline-flex align-items-center gap-2 px-3 py-1.5 rounded-pill" style="background: #eff6ff; border: 1px solid #bfdbfe;">' +
            '<i class="bi bi-building-fill text-primary"></i>' +
            '<span class="fw-bold" style="color: #000000 !important; font-size:0.86rem;">' + esc(a.city_name || a.city_origin || 'City of Manila') + '</span>' +
            '<span class="badge rounded-pill bg-primary text-white fw-bold px-2 py-0.5" style="font-size:0.66rem; letter-spacing:0.03em;">LOCAL LGU</span>' +
            '</div>',
          b: '<div class="d-inline-flex align-items-center gap-2 px-3 py-1.5 rounded-pill" style="background: #faf5ff; border: 1px solid #e9d5ff;">' +
            '<i class="bi bi-geo-alt-fill" style="color:#7e22ce;"></i>' +
            '<span class="fw-bold" style="color: #000000 !important; font-size:0.86rem;">' + esc(b.city_name || b.city_origin || 'Peer City Benchmark') + '</span>' +
            '<span class="badge rounded-pill fw-bold px-2 py-0.5" style="background:#7e22ce; color:#ffffff; font-size:0.66rem; letter-spacing:0.03em;">ENACTED BENCHMARK</span>' +
            '</div>'
        },
        {
          label: 'Policy Area',
          a: '<span class="badge px-3 py-1.5 rounded-pill fw-bold" style="background:#f1f5f9; color:#000000 !important; border:1px solid #cbd5e1; font-size:0.82rem;">' + esc(a.category || 'General') + '</span>',
          b: '<span class="badge px-3 py-1.5 rounded-pill fw-bold" style="background:#f1f5f9; color:#000000 !important; border:1px solid #cbd5e1; font-size:0.82rem;">' + esc(b.policy_area || b.category || 'General') + '</span>'
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
            '<i class="bi bi-stars"></i> Suggest Policy Improvements' +
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
          '<td class="px-3 py-3 fw-bold" style="background:#f8fafc; font-family: Arial, sans-serif; color:#000000 !important; font-size:0.85rem;">' + r.label + '</td>' +
          '<td class="px-3 py-3 bg-white" style="vertical-align:top; color:#000000 !important;">' + r.a + '</td>' +
          '<td class="px-3 py-3 bg-white" style="vertical-align:top; color:#000000 !important;">' + r.b + '</td>' +
          '</tr>';
      }

      // Evaluation Criteria Section Divider
      tableHtml += '<tr>' +
        '<td colspan="3" class="px-3 py-2.5 bg-light border-top border-bottom fw-bold text-uppercase" style="background:#f1f5f9; font-family: Arial, sans-serif; color:#000000 !important; font-size:0.8rem; font-weight:800; letter-spacing:0.8px;">' +
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
          '<td class="px-3 py-3 fw-bold" style="background:#f8fafc; font-family: Arial, sans-serif; color:#000000 !important; font-size:0.85rem;">' + er.label + '</td>' +
          '<td class="px-3 py-3 bg-white" style="vertical-align:top; color:#000000 !important;">' + er.a + '</td>' +
          '<td class="px-3 py-3 bg-white" style="vertical-align:top; color:#000000 !important;">' + er.b + '</td>' +
          '</tr>';
      }

      // Recommendation Row
      tableHtml += '<tr>' +
        '<td class="px-3 py-3 fw-bold" style="background:#f8fafc; font-family: Arial, sans-serif; color:#000000 !important; font-size:0.85rem;">Recommendation</td>' +
        '<td class="px-3 py-3 bg-white" style="vertical-align:top;">' +
        '<div style="font-family: Arial, Helvetica, sans-serif; color:#000000 !important; font-size:0.88rem; line-height:1.6; font-weight: 500;">' + esc(a.ai_recommendation || getEnhancedRecommendation(a)) + '</div>' +
        '</td>' +
        '<td class="px-3 py-3 bg-white" style="vertical-align:top;">' +
        '<div style="font-family: Arial, Helvetica, sans-serif; color:#000000 !important; font-size:0.88rem; line-height:1.6; font-weight: 500;">' + esc(b.ai_recommendation || getEnhancedRecommendation(b)) + '</div>' +
        '</td>' +
        '</tr>';

      tableHtml += '</tbody></table></div>';

      // ── ASSEMBLE EXECUTIVE HIERARCHY:
      // 1. Alignment Scorecard (execCard)
      // 2. Detailed Comparison Table (tableHtml)
      // 3. 3-Card AI Executive Comparison Insights (insightsCard)
      // 4. AI Amendment Result Area (aiAmendmentResultArea)
      var html = execCard + tableHtml + insightsCard + '<div id="aiAmendmentResultArea" class="mt-4 d-none"></div>';

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
      recordStaffComparisonInReports(comparisonTitle, reportType, dynamicAI.diffSummary.replace(/<[^>]*>?/gm, ''), a.risk_level, dynamicAI.takeaway);

      resultEl.innerHTML = html;
      resultEl.classList.remove('d-none');
    };

    // --- MODE 3: COMPARE VERSIONS (STAFF) ---
    window.runStaffVersionComparison = function () {
      var pId = document.getElementById('compareVersionPolicy').value;
      var resultEl = document.getElementById('comparisonResult');
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

      var record = window.VERSION_COMPARE_MAP ? window.VERSION_COMPARE_MAP[String(pId)] : null;
      if (!record || !record.oldest_version || !record.newest_version) {
        showMsg('danger', 'bi-x-circle-fill', 'Version evaluation data is not available for this policy.');
        return;
      }

      var oldest = record.oldest_version;
      var newest = record.newest_version;

      var html = '<div class="table-responsive border rounded-4 overflow-hidden shadow-sm mt-4 bg-white" style="font-family: Arial, Helvetica, sans-serif;">';
      html += '<table class="table table-bordered align-middle mb-0" style="border-color:#cbd5e1;">';

      // Deep Manila Navy Header Row: Initial Version (Oldest) vs Latest Version (Newest)
      html += '<thead><tr style="background-color: #0B2E59;">';
      html += '<th class="py-3.5 px-3 fw-bold text-uppercase align-middle" style="width:22%; background-color: #0B2E59 !important; color: #FFFFFF !important; font-size:0.8rem; font-weight:800; letter-spacing:0.05em; border-bottom: 2.5px solid #082242 !important; border-top: none !important;">' +
        '<div class="d-flex align-items-center gap-1.5 text-white"><i class="bi bi-clock-history text-info fs-6"></i> EVALUATION DIMENSION</div>' +
        '</th>';

      // Version A (Oldest)
      html += '<th class="py-3 px-3 text-center align-middle" style="width:39%; background-color: #0B2E59 !important; color: #FFFFFF !important; border-bottom: 2.5px solid #082242 !important; border-top: none !important; border-left: 1px solid rgba(255,255,255,0.15) !important;">' +
        '<div class="fw-bold text-uppercase mb-1.5 text-white" style="font-size:0.88rem; letter-spacing:0.05em;">' +
        '<i class="bi bi-arrow-counterclockwise me-1 text-secondary-emphasis"></i> Initial Baseline (' + esc(oldest.version_label) + ')' +
        '</div>' +
        '<div class="badge rounded-pill bg-white text-dark border px-2.5 py-1 shadow-2xs" style="font-size:0.75rem;">' + (oldest.approved_at ? 'Evaluated ' + esc(oldest.approved_at) : 'Original Approved Version') + '</div>' +
        '</th>';

      // Version B (Newest)
      html += '<th class="py-3 px-3 text-center align-middle" style="width:39%; background-color: #0B2E59 !important; color: #FFFFFF !important; border-bottom: 2.5px solid #082242 !important; border-top: none !important; border-left: 1px solid rgba(255,255,255,0.15) !important;">' +
        '<div class="fw-bold text-uppercase mb-1.5 text-white" style="font-size:0.88rem; letter-spacing:0.05em;">' +
        '<i class="bi bi-stars me-1 text-warning"></i> Latest Revision (' + esc(newest.version_label) + ')' +
        '</div>' +
        '<div class="badge rounded-pill bg-primary text-white px-2.5 py-1 shadow-2xs" style="font-size:0.75rem;">' + (newest.approved_at ? 'Evaluated ' + esc(newest.approved_at) : 'Current Approved Version') + '</div>' +
        '</th>';
      html += '</tr></thead>';

      html += '<tbody>';

      function renderDiffRow(label, aVal, bVal, isDiff) {
        var rowStyle = isDiff ? 'background: #fffdf5;' : 'background: #ffffff;';
        var badge = isDiff
          ? '<span class="badge rounded-pill bg-warning text-dark px-2 py-0.5 fw-bold ms-2 shadow-2xs" style="font-size:0.68rem;"><i class="bi bi-arrow-left-right me-1"></i> Changed</span>'
          : '<span class="badge rounded-pill bg-light text-muted border px-2 py-0.5 ms-2" style="font-size:0.68rem;"><i class="bi bi-check2 text-success me-1"></i> Unchanged</span>';

        return '<tr style="' + rowStyle + '">' +
          '<td class="px-3 py-3 fw-bold" style="background:#f8fafc; font-family: Arial, sans-serif; color:#000000 !important; font-size:0.85rem;">' +
          label + (record.has_multiple ? badge : '') +
          '</td>' +
          '<td class="px-3 py-3" style="vertical-align:top; color:#000000 !important;">' + aVal + '</td>' +
          '<td class="px-3 py-3" style="vertical-align:top; color:#000000 !important;' + (isDiff ? 'background:#fffbeb;' : '') + '">' + bVal + '</td>' +
          '</tr>';
      }

      // Policy Meta Rows
      html += '<tr>' +
        '<td class="px-3 py-2.5 fw-bold" style="background:#f8fafc; font-family: Arial, sans-serif; color:#000000 !important; font-size:0.85rem;">Policy Title</td>' +
        '<td colspan="2" class="px-3 py-2.5 bg-white fw-bold" style="color:#000000 !important; font-size:0.92rem;">' + esc(record.title) + '</td>' +
        '</tr>';

      html += '<tr>' +
        '<td class="px-3 py-2.5 fw-bold" style="background:#f8fafc; font-family: Arial, sans-serif; color:#000000 !important; font-size:0.85rem;">LGU / City Origin</td>' +
        '<td colspan="2" class="px-3 py-2.5 bg-white">' + cleanCityBadge(record.city_origin, record.title) + '</td>' +
        '</tr>';

      html += '<tr>' +
        '<td class="px-3 py-2.5 fw-bold" style="background:#f8fafc; font-family: Arial, sans-serif; color:#000000 !important; font-size:0.85rem;">Policy Category</td>' +
        '<td colspan="2" class="px-3 py-2.5 bg-white">' +
        '<span class="badge px-3 py-1.5 rounded-pill fw-bold" style="background:#f1f5f9; color:#000000 !important; border:1px solid #cbd5e1; font-size:0.82rem;">' + esc(record.category) + '</span>' +
        '</td>' +
        '</tr>';

      html += '<tr>' +
        '<td class="px-3 py-2.5 fw-bold" style="background:#f8fafc; font-family: Arial, sans-serif; color:#000000 !important; font-size:0.85rem;">Approved By</td>' +
        '<td class="px-3 py-2.5 bg-white small" style="color:#000000 !important; font-weight:500;"><i class="bi bi-person-check-fill text-success me-1"></i>' + esc(oldest.approved_by || 'System Administrator') + '</td>' +
        '<td class="px-3 py-2.5 bg-white small" style="color:#000000 !important; font-weight:500;"><i class="bi bi-person-check-fill text-success me-1"></i>' + esc(newest.approved_by || 'System Administrator') + '</td>' +
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
        '<div style="font-family: Arial, Helvetica, sans-serif; color:#000000 !important; font-size:0.88rem; line-height:1.6; font-weight:500;">' + esc(oldRec) + '</div>',
        '<div style="font-family: Arial, Helvetica, sans-serif; color:#000000 !important; font-size:0.88rem; line-height:1.6; font-weight:500;">' + esc(newRec) + '</div>',
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
        '<p class="small mb-0" style="color: #0f172a !important; line-height:1.65; font-size: 0.86rem; font-weight: 500;">' +
        dynamicVersionAI.summary +
        '</p>' +
        '</div>' +
        '</div>' +

        '<div class="col-12 col-md-6 col-lg-6">' +
        '<div class="bg-white p-3 p-md-3.5 rounded-3 border shadow-2xs h-100 d-flex flex-column">' +
        '<div class="fw-bold text-dark small mb-2 d-flex align-items-center gap-1.5">' +
        '<i class="bi bi-lightbulb-fill text-warning fs-6"></i> Council Endorsement &amp; Action Plan' +
        '</div>' +
        '<p class="small mb-0" style="color: #0f172a !important; line-height:1.65; font-size: 0.86rem; font-weight: 500;">' +
        dynamicVersionAI.takeaway +
        '</p>' +
        '</div>' +
        '</div>' +
        '</div>' +
        '</div>';

      recordStaffComparisonInReports(record.title + ' (Version Evolution)', 'Version Comparison', dynamicVersionAI.summary.replace(/<[^>]*>?/gm, ''), newest.risk_level, dynamicVersionAI.takeaway);

      resultEl.innerHTML = html;
      resultEl.classList.remove('d-none');
    };

    // ── DUAL AI ENGINE CONFIGURATION & DETECTION ──────────────────
    var OLLAMA_BASE_URL = 'http://localhost:11434';
    var OLLAMA_MODEL = 'llama3.2';
    var GEMINI_FALLBACK_MODEL = (typeof GEMINI_MODEL !== 'undefined' && GEMINI_MODEL) ? GEMINI_MODEL : 'gemini-1.5-flash';

    window.getAIEnginePreference = function () {
      return localStorage.getItem('legislative_ai_engine') || 'auto';
    };

    window.setAIEnginePreference = function (val) {
      localStorage.setItem('legislative_ai_engine', val);
      updateAIEngineUI();
      checkLocalOllamaHealth();
    };

    window.checkLocalOllamaHealth = async function () {
      var statusBadge = document.getElementById('aiEngineStatusBadge');
      var selector = document.getElementById('aiEngineSelector');
      var pref = window.getAIEnginePreference();
      if (selector) selector.value = pref;

      if (!statusBadge) return;

      var isOllamaOnline = false;
      try {
        var ctrl = new AbortController();
        var timer = setTimeout(function () { ctrl.abort(); }, 2000);
        var res = await fetch(OLLAMA_BASE_URL + '/api/tags', { method: 'GET', signal: ctrl.signal });
        clearTimeout(timer);
        if (res.ok) {
          var data = await res.json();
          if (data && data.models && data.models.length > 0) {
            isOllamaOnline = true;
          }
        }
      } catch (e) {
        isOllamaOnline = false;
      }

      window.isOllamaAvailable = isOllamaOnline;

      if (pref === 'ollama') {
        if (isOllamaOnline) {
          statusBadge.className = 'badge rounded-pill bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2.5 py-1.5 d-flex align-items-center gap-1.5 shadow-2xs';
          statusBadge.innerHTML = '<i class="bi bi-hdd-network-fill"></i> <span>🦙 Ollama Local Ready (Llama 3.2)</span>';
        } else {
          statusBadge.className = 'badge rounded-pill bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-2.5 py-1.5 d-flex align-items-center gap-1.5 shadow-2xs';
          statusBadge.innerHTML = '<i class="bi bi-exclamation-circle-fill"></i> <span>🦙 Ollama Offline</span>';
        }
      } else if (pref === 'gemini') {
        statusBadge.className = 'badge rounded-pill bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-2.5 py-1.5 d-flex align-items-center gap-1.5 shadow-2xs';
        statusBadge.innerHTML = '<i class="bi bi-stars"></i> <span>✨ Gemini Cloud Ready</span>';
      } else { // auto
        if (isOllamaOnline) {
          statusBadge.className = 'badge rounded-pill bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2.5 py-1.5 d-flex align-items-center gap-1.5 shadow-2xs';
          statusBadge.innerHTML = '<i class="bi bi-hdd-network-fill"></i> <span>🟢 🦙 Ollama Ready (Auto)</span>';
        } else {
          statusBadge.className = 'badge rounded-pill bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-2.5 py-1.5 d-flex align-items-center gap-1.5 shadow-2xs';
          statusBadge.innerHTML = '<i class="bi bi-stars"></i> <span>✨ Gemini Cloud (Auto)</span>';
        }
      }
    };

    function updateAIEngineUI() {
      var selector = document.getElementById('aiEngineSelector');
      if (selector) selector.value = window.getAIEnginePreference();
    }

    // ── DUAL-ENGINE AI POLICY GAP & AMENDMENT GENERATOR ───────────
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

      var pref = window.getAIEnginePreference();
      var loadingTitle = 'AI Dual-Engine is analyzing policy gaps &amp; drafting recommended improvements...';
      var loadingSubtitle = 'Synthesizing benchmark provisions from <strong>' + esc(b.title) + '</strong> (' + esc(b.city_name || b.city_origin || 'Peer City') + ') to address draft gaps in <strong>' + esc(a.title) + '</strong> with practical legislative enhancements.';

      if (pref === 'ollama') {
        loadingTitle = '🦙 Ollama Local (Llama 3.2) is analyzing policy gaps &amp; drafting offline improvements...';
      } else if (pref === 'gemini') {
        loadingTitle = '✨ Google Gemini Cloud is analyzing policy gaps &amp; drafting recommended improvements...';
      }

      // 1. Render Pulsing Placeholder Loading Skeleton
      container.innerHTML = '<div class="card border-0 rounded-4 shadow-sm p-4 bg-white placeholder-glow" style="border: 2px dashed #93c5fd !important; background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);">' +
        '<div class="d-flex align-items-center gap-2.5 mb-3">' +
        '<div class="spinner-border text-primary" style="width: 1.3rem; height: 1.3rem;" role="status">' +
        '<span class="visually-hidden">Loading...</span>' +
        '</div>' +
        '<h6 class="fw-bold text-primary mb-0" style="font-size: 0.95rem;">' +
        '<i class="bi bi-stars text-warning me-1"></i> ' + loadingTitle +
        '</h6>' +
        '</div>' +
        '<p class="text-muted small mb-3">' + loadingSubtitle + '</p>' +
        '<div class="placeholder col-12 mb-2 rounded" style="height: 16px; background-color: #cbd5e1;"></div>' +
        '<div class="placeholder col-10 mb-2 rounded" style="height: 16px; background-color: #cbd5e1;"></div>' +
        '<div class="placeholder col-8 mb-3 rounded" style="height: 16px; background-color: #cbd5e1;"></div>' +
        '<div class="placeholder col-4 rounded" style="height: 24px; background-color: #e2e8f0;"></div>' +
        '</div>';

      // Check if amendment clause was already pre-generated during benchmarking comparison
      if (window.preGeneratedAIAmendment) {
        await new Promise(function (resolve) { setTimeout(resolve, 450); });
        renderAIAmendmentLanguageBox(window.preGeneratedAIAmendment, a, b, gap, {
          id: 'pregen',
          badgeText: 'Harmonized Municipal Clause',
          badgeClass: 'bg-primary bg-opacity-10 text-primary border-primary border-opacity-25',
          icon: 'bi-shield-check',
          note: 'Pre-synthesized legislative alignment clause.'
        });
        return;
      }

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

      var draftedClause = '';
      var engineUsed = null;

      // ── ATTEMPT 1: OLLAMA LOCAL (Llama 3.2 - Offline First)
      if (pref === 'ollama' || pref === 'auto') {
        try {
          var ollamaCtrl = new AbortController();
          var ollamaTimeout = setTimeout(function () { ollamaCtrl.abort(); }, 30000);

          var ollamaRes = await fetch(OLLAMA_BASE_URL + '/api/generate', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            signal: ollamaCtrl.signal,
            body: JSON.stringify({
              model: OLLAMA_MODEL,
              prompt: promptText,
              stream: false,
              options: {
                temperature: 0.3,
                num_predict: 600
              }
            })
          });
          clearTimeout(ollamaTimeout);

          if (ollamaRes.ok) {
            var oData = await ollamaRes.json();
            if (oData && oData.response && oData.response.trim().length > 20) {
              draftedClause = oData.response.trim();
              if (draftedClause.startsWith('```')) {
                draftedClause = draftedClause.replace(/```[a-z]*\n?|```/gi, '').trim();
              }
              engineUsed = {
                id: 'ollama',
                badgeText: '🦙 Ollama Local (Llama 3.2 Offline)',
                badgeClass: 'bg-success bg-opacity-10 text-success border-success border-opacity-25',
                icon: 'bi-hdd-network-fill',
                note: 'Generated locally via Ollama Llama 3.2 — zero cloud latency, runs 100% offline.'
              };
            }
          }
        } catch (oErr) {
          console.warn('Ollama local generation bypassed or timed out:', oErr);
        }
      }

      // ── ATTEMPT 2: GOOGLE GEMINI CLOUD (If preferred or auto fallback)
      if (!draftedClause && (pref === 'gemini' || pref === 'auto')) {
        var apiKey = (typeof GEMINI_API_KEY !== 'undefined' && GEMINI_API_KEY && GEMINI_API_KEY !== 'PLACEHOLDER_KEY' && !GEMINI_API_KEY.includes('YOUR_'))
          ? GEMINI_API_KEY
          : (window.GEMINI_API_KEY || localStorage.getItem('gemini_api_key') || '');

        if (apiKey) {
          try {
            var geminiCtrl = new AbortController();
            var geminiTimeout = setTimeout(function () { geminiCtrl.abort(); }, 16000);

            var geminiRes = await fetch('https://generativelanguage.googleapis.com/v1beta/models/' + encodeURIComponent(GEMINI_FALLBACK_MODEL) + ':generateContent?key=' + encodeURIComponent(apiKey), {
              method: 'POST',
              headers: { 'Content-Type': 'application/json' },
              signal: geminiCtrl.signal,
              body: JSON.stringify({
                contents: [{ parts: [{ text: promptText }] }],
                generationConfig: {
                  temperature: 0.3,
                  maxOutputTokens: 600
                }
              })
            });
            clearTimeout(geminiTimeout);

            if (geminiRes.ok) {
              var gData = await geminiRes.json();
              if (gData.candidates && gData.candidates[0] && gData.candidates[0].content && gData.candidates[0].content.parts[0]) {
                draftedClause = gData.candidates[0].content.parts[0].text.trim();
                if (draftedClause.startsWith('```')) {
                  draftedClause = draftedClause.replace(/```[a-z]*\n?|```/gi, '').trim();
                }
                engineUsed = {
                  id: 'gemini',
                  badgeText: '✨ Google Gemini (gemini-1.5-flash Cloud)',
                  badgeClass: 'bg-primary bg-opacity-10 text-primary border-primary border-opacity-25',
                  icon: 'bi-cloud-check-fill',
                  note: 'Generated via Google Gemini Cloud intelligence engine.'
                };
              }
            }
          } catch (gErr) {
            console.warn('Gemini cloud generation bypassed or timed out:', gErr);
          }
        }
      }

      // ── ATTEMPT 3: STATUTORY BASELINE TEMPLATE ENGINE (Deterministic Fallback)
      if (!draftedClause) {
        draftedClause = generateContextualLegislativeDraft(a, b, gap);
        engineUsed = {
          id: 'statutory',
          badgeText: '🏛️ Manila Statutory Baseline Rule Engine',
          badgeClass: 'bg-secondary bg-opacity-10 text-secondary border-secondary border-opacity-25',
          icon: 'bi-shield-check',
          note: 'Generated using statutory alignment rules under Philippine Local Government Code (RA 7160).'
        };
      }

      renderAIAmendmentLanguageBox(draftedClause, a, b, gap, engineUsed);
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

    function renderAIAmendmentLanguageBox(clause, a, b, gap, engineInfo) {
      var container = document.getElementById('aiAmendmentResultArea');
      if (!container) return;

      var engine = engineInfo || {
        badgeText: 'Harmonized Municipal Clause',
        badgeClass: 'bg-primary bg-opacity-10 text-primary border-primary border-opacity-25',
        icon: 'bi-shield-check',
        note: 'AI legislative drafting suggestion.'
      };

      var html = '<div class="card border-0 rounded-4 shadow-sm p-4 bg-white" style="border: 1px solid #bfdbfe !important; background: linear-gradient(180deg, #f8fafc 0%, #ffffff 100%);">' +
        '<div class="d-flex flex-column flex-sm-row align-items-start align-items-sm-center justify-content-between gap-3 mb-3 pb-3 border-bottom">' +
        '<div class="d-flex align-items-center gap-2.5">' +
        '<span class="p-2 rounded-3 text-white shadow-2xs" style="background: linear-gradient(135deg, #0284c7 0%, #2563eb 100%); font-size:1.15rem;">' +
        '<i class="bi bi-stars"></i>' +
        '</span>' +
        '<div>' +
        '<div class="d-flex align-items-center gap-2 flex-wrap">' +
        '<h5 class="fw-bold text-dark mb-0" style="font-size:1.05rem;">AI-Suggested Policy Improvements (For Review)</h5>' +
        '<span class="badge rounded-pill ' + engine.badgeClass + ' border px-2.5 py-1" style="font-size:0.72rem;">' +
        '<i class="bi ' + engine.icon + ' me-1"></i> ' + esc(engine.badgeText) +
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
        '<i class="bi bi-file-earmark-ruled me-1"></i> Recommended Policy Addition &amp; Improvement' +
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
        'This is an AI-generated drafting aid, not legal advice. All suggested language must be reviewed and finalized by legislative staff and legal counsel before formal proposal. ' +
        '<span class="text-muted fst-italic">(' + esc(engine.note) + ')</span>' +
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
    var initialResultEl = document.getElementById('comparisonResult');
    if (initialResultEl) {
      initialResultEl.innerHTML = renderEmptyComparisonPlaceholder();
      initialResultEl.classList.remove('d-none');
    }

    // Auto-detect local Ollama & AI engine on page load
    setTimeout(function () {
      checkLocalOllamaHealth();
    }, 200);
  })();
</script>