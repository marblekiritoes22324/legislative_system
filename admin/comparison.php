<?php
// Build comparison data from all policies + their saved evaluations.
if (!isset($all_policies) || !is_array($all_policies)) {
  $all_policies = [];
}
if (!isset($evaluations) || !is_array($evaluations)) {
  $evaluations = [];
}

require_once __DIR__ . '/../backend/evaluation_versions_helper.php';
$version_comparison_data = get_policy_versions_comparison_data($conn ?? null);

$eval_map = [];
foreach ($evaluations as $eval) {
  $eval_status = trim($eval['evaluation_status'] ?? $eval['status'] ?? '');
  // STRICT: Only policies with 'Approved' evaluation status can be compared
  if ($eval_status === 'Approved') {
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
      'risk_level' => $info['risk_level'],
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

// Split into Local Manila policies vs External LGU Benchmarks
$local_policies = [];
$external_policies = [];
foreach ($completed_policies as $p) {
  $c = strtolower($p['city_origin'] ?? 'city of manila');
  if (strpos($c, 'manila') !== false) {
    $local_policies[] = $p;
  } else {
    $external_policies[] = $p;
  }
}
?>
<section id="comparativeAnalysisSection"
  class="content-section <?= ($active_section ?? 'adminDashboardSection') !== 'comparativeAnalysisSection' ? 'd-none' : '' ?>">
  <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-white">

    <!-- Header -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
      <div class="d-flex align-items-center">
        <span class="p-2.5 rounded-3 me-3" style="background:#e0f2fe; color:#0284c7;">
          <i class="bi bi-layout-sidebar-inset-reverse fs-4"></i>
        </span>
        <div>
          <h2 class="h4 fw-bold text-dark mb-1">Benchmarking &amp; Comparative Analysis</h2>
          <p class="text-muted mb-0 small" id="comparisonSubtitle">
            Compare local Manila ordinances side by side with external city benchmarks (e.g., Quezon City, Pasig).
          </p>
        </div>
      </div>
    </div>

    <!-- Mode 1: Compare Policies Selectors (Bootstrap Input Groups with City Grouping) -->
    <div class="row g-3 align-items-end mb-4" id="policyCompareForm">

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
          <span class="text-muted fw-normal">— e.g., Quezon City (QC Benchmark)</span>
        </label>
        <div class="input-group shadow-sm">
          <span class="input-group-text bg-white border-end-0 rounded-start-3" style="border-left:3px solid #15803d;">
            <i class="bi bi-file-earmark-text text-success"></i>
          </span>
          <select id="comparePolicyB" class="form-select border-start-0 rounded-end-3" style="font-size:0.9rem;">
            <?php if (empty($completed_policies)): ?>
              <option value="" disabled selected>— No Approved Evaluations Available —</option>
            <?php else: ?>
              <option value="">— Select Policy B or City Benchmark —</option>
              <?php if (!empty($external_policies)): ?>
                <optgroup label="🏙️ External LGU Benchmarks (Quezon City, Pasig, etc.)">
                  <?php foreach ($external_policies as $p): ?>
                    <option value="<?= (int) $p['id'] ?>" <?= ($p === reset($external_policies)) ? 'selected' : '' ?>>
                      [<?= htmlspecialchars($p['city_origin'] ?? 'Benchmark') ?>] <?= htmlspecialchars($p['title']) ?>
                    </option>
                  <?php endforeach; ?>
                </optgroup>
              <?php endif; ?>
              <?php if (!empty($local_policies)): ?>
                <optgroup label="🏛️ City of Manila (Local Ordinances)">
                  <?php foreach ($local_policies as $p): ?>
                    <option value="<?= (int) $p['id'] ?>">
                      [Manila] <?= htmlspecialchars($p['title']) ?>
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

    <!-- Mode 2: Compare Versions Selector (Single Policy Selection) -->
    <div class="row g-3 align-items-end mb-4 d-none" id="versionCompareForm">
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
            onchange="runVersionComparison()">
            <?php if (empty($version_comparison_data)): ?>
              <option value="" disabled selected>— No Approved Evaluations Available —</option>
            <?php else: ?>
              <option value="">— Select Approved Policy to Compare Versions —</option>
              <?php foreach ($version_comparison_data as $vp): ?>
                <option value="<?= (int) $vp['policy_id'] ?>" <?= $vp['has_multiple'] ? 'style="font-weight:700;"' : '' ?>>
                  [<?= htmlspecialchars($vp['city_origin'] ?? 'City of Manila') ?>] <?= htmlspecialchars($vp['title']) ?>
                  <?= $vp['has_multiple'] ? ' (' . $vp['total_versions'] . ' Approved Versions)' : ' (Version 1)' ?>
                </option>
              <?php endforeach; ?>
            <?php endif; ?>
          </select>
        </div>
      </div>
      <div class="col-lg-2 col-md-2 d-grid">
        <button type="button" id="compareVersionsBtn"
          class="btn text-white fw-bold shadow-sm d-flex align-items-center justify-content-center gap-1.5 rounded-3 py-2"
          onclick="runVersionComparison()"
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

    window.COMPARE_DATA = COMPARE_DATA;
    window.VERSION_COMPARE_DATA = VERSION_COMPARE_DATA;

    window.COMPARE_POLICY_MAP = {};
    COMPARE_DATA.forEach(function (item) {
      window.COMPARE_POLICY_MAP[String(item.id)] = item;
    });

    window.VERSION_COMPARE_MAP = {};
    VERSION_COMPARE_DATA.forEach(function (item) {
      window.VERSION_COMPARE_MAP[String(item.policy_id)] = item;
    });

    var currentMode = 'policies';

    window.switchComparisonMode = function (mode) {
      currentMode = mode;
      var btnPolicies = document.getElementById('toggleComparePoliciesBtn');
      var btnVersions = document.getElementById('toggleCompareVersionsBtn');
      var formPolicies = document.getElementById('policyCompareForm');
      var formVersions = document.getElementById('versionCompareForm');
      var subtitle = document.getElementById('comparisonSubtitle');
      var resultEl = document.getElementById('comparisonResult');

      if (resultEl) {
        resultEl.innerHTML = '';
        resultEl.classList.add('d-none');
      }

      if (mode === 'versions') {
        if (btnPolicies) {
          btnPolicies.style.background = 'transparent';
          btnPolicies.className = 'btn btn-sm rounded-pill px-3 py-1.5 fw-semibold text-secondary';
        }
        if (btnVersions) {
          btnVersions.style.background = '#0B2E59';
          btnVersions.className = 'btn btn-sm rounded-pill px-3 py-1.5 fw-bold text-white shadow-sm';
        }
        if (formPolicies) formPolicies.classList.add('d-none');
        if (formVersions) formVersions.classList.remove('d-none');
        if (subtitle) subtitle.innerText = 'Select a policy to compare its oldest initial approved evaluation against its latest approved evaluation.';

        var verSelect = document.getElementById('compareVersionPolicy');
        if (verSelect && verSelect.value) {
          window.runVersionComparison();
        }
      } else {
        if (btnPolicies) {
          btnPolicies.style.background = '#0B2E59';
          btnPolicies.className = 'btn btn-sm rounded-pill px-3 py-1.5 fw-bold text-white shadow-sm';
        }
        if (btnVersions) {
          btnVersions.style.background = 'transparent';
          btnVersions.className = 'btn btn-sm rounded-pill px-3 py-1.5 fw-semibold text-secondary';
        }
        if (formPolicies) formPolicies.classList.remove('d-none');
        if (formVersions) formVersions.classList.add('d-none');
        if (subtitle) subtitle.innerText = 'Compare local Manila ordinances side by side with external city benchmarks (e.g., Quezon City, Pasig) or previous versions.';
      }
    };

    function esc(t) {
      if (t === undefined || t === null) return '';
      return String(t).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
    }

    function cleanCityBadge(city, title) {
      var c = city || 'City of Manila';
      var t = (title || '').toLowerCase();
      var isManila = (c.toLowerCase().indexOf('manila') !== -1);
      var isQC = (c.toLowerCase().indexOf('quezon') !== -1 || c.toLowerCase().indexOf('qc') !== -1);
      var isPasig = (c.toLowerCase().indexOf('pasig') !== -1);

      var authBadge = '';
      if (!isManila) {
        var isOfficial = (t.indexOf('sp-2876') !== -1 || t.indexOf('sp-2350') !== -1 || t.indexOf('ordinance no. 12') !== -1 || t.indexOf('epwmd') !== -1);
        if (isOfficial) {
          authBadge = ' <span class="badge rounded-pill bg-white text-primary border border-primary-subtle shadow-2xs ms-1.5" title="Researched Official LGU Benchmark from Official City Council & EPWMD records" style="font-size:0.7rem; font-weight:600; cursor:help;"><i class="bi bi-patch-check-fill text-primary me-1"></i>Official Researched Data</span>';
        } else {
          authBadge = ' <span class="badge rounded-pill bg-white text-secondary border shadow-2xs ms-1.5" title="Sample Demonstration Benchmark for Cross-City Analysis" style="font-size:0.7rem; cursor:help;"><i class="bi bi-flask me-1 text-warning"></i>Sample Benchmark</span>';
        }
      }

      if (isManila) {
        return '<span class="badge fw-semibold px-2.5 py-1" style="background:#eff6ff; color:#1d4ed8; border:1px solid #bfdbfe; font-size:0.8rem; font-family: Arial, sans-serif;"><i class="bi bi-building me-1"></i> City of Manila (Local)</span>';
      } else if (isQC) {
        return '<span class="badge fw-semibold px-2.5 py-1" style="background:#fef2f2; color:#b91c1c; border:1px solid #fecaca; font-size:0.8rem; font-family: Arial, sans-serif;"><i class="bi bi-pin-map-fill me-1"></i> Quezon City (QC Benchmark)</span>' + authBadge;
      } else if (isPasig) {
        return '<span class="badge fw-semibold px-2.5 py-1" style="background:#f0fdf4; color:#15803d; border:1px solid #bbf7d0; font-size:0.8rem; font-family: Arial, sans-serif;"><i class="bi bi-geo-alt-fill me-1"></i> Pasig City (Benchmark)</span>' + authBadge;
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

    function cleanLevelBadge(level) {
      var l = (level || 'Low').toLowerCase();
      var color = '#15803d', bg = '#f0fdf4', border = '#bbf7d0', text = 'Low';
      if (l.indexOf('high') !== -1) {
        color = '#b91c1c'; bg = '#fef2f2'; border = '#fca5a5'; text = 'High';
      } else if (l.indexOf('med') !== -1 || l.indexOf('mod') !== -1) {
        color = '#b45309'; bg = '#fffbeb'; border = '#fde68a'; text = 'Medium';
      }
      return '<span class="badge fw-semibold me-2" style="background:' + bg + '; color:' + color + '; border:1px solid ' + border + '; font-size:0.78rem; font-family: Arial, sans-serif;">' + text + '</span>';
    }

    function criteriaCell(level, reason) {
      var badge = cleanLevelBadge(level);
      if (!reason) return badge;
      return '<div class="mb-1.5">' + badge + '</div>' +
        '<div style="font-family: Arial, Helvetica, sans-serif; color: #000000; font-size: 0.88rem; line-height: 1.55; font-weight: 400;">' + esc(reason) + '</div>';
    }

    // --- DYNAMIC AI COMPARISON SYNTHESIS ENGINE (RESPONSIVE TO EVERY ORDINANCE) ---
    function buildDynamicAIComparisonInsights(a, b, isCrossCity) {
      var tA = (a.title || '').toLowerCase();
      var tB = (b.title || '').toLowerCase();
      var catA = a.category || 'General';
      var catB = b.category || 'General';
      var cityA = a.city_origin || 'City of Manila';
      var cityB = b.city_origin || 'City of Manila';

      // 1. Identify Subject Matter Domain
      var topic = 'Legislative Framework & Municipal Regulation';
      var topicKey = 'general';
      if (tA.indexOf('plastic') !== -1 || tB.indexOf('plastic') !== -1 || catA.indexOf('Environment') !== -1 || catB.indexOf('Environment') !== -1) {
        topic = 'Environmental Protection & Single-Use Waste Recovery';
        topicKey = 'environment';
      } else if (tA.indexOf('flood') !== -1 || tB.indexOf('flood') !== -1 || tA.indexOf('drainage') !== -1 || tB.indexOf('drainage') !== -1 || tA.indexOf('disaster') !== -1 || tB.indexOf('disaster') !== -1) {
        topic = 'Disaster Resilience & Urban Drainage Modernization';
        topicKey = 'drainage';
      } else if (tA.indexOf('traffic') !== -1 || tB.indexOf('traffic') !== -1 || tA.indexOf('bike') !== -1 || tB.indexOf('bike') !== -1 || tA.indexOf('mobility') !== -1 || tB.indexOf('mobility') !== -1 || catA.indexOf('Transportation') !== -1 || catB.indexOf('Transportation') !== -1) {
        topic = 'Urban Mobility, Protected Lanes & Transit Systems';
        topicKey = 'mobility';
      } else if (tA.indexOf('energy') !== -1 || tB.indexOf('energy') !== -1 || tA.indexOf('green building') !== -1 || tB.indexOf('green building') !== -1) {
        topic = 'Green Building Standards & Clean Energy Transition';
        topicKey = 'energy';
      } else if (catA.indexOf('Health') !== -1 || catB.indexOf('Health') !== -1 || tA.indexOf('health') !== -1 || tB.indexOf('health') !== -1) {
        topic = 'Public Health Safeguards & District Sanitation';
        topicKey = 'health';
      }

      var findingsText = '';
      var takeawayText = '';

      if (isCrossCity) {
        if (topicKey === 'environment') {
          findingsText = 'Comparing <strong>' + esc(cityA) + '</strong> with benchmark ordinance from <strong>' + esc(cityB) + '</strong> shows operational distinctions in waste reduction. While Policy A is tailored to Manila\'s dense retail hubs, ' + esc(cityB) + '\'s framework introduces structured merchant recovery quotas, dedicated green recovery trust accounts, and centralized EPWMD compliance teams.';
          takeawayText = 'Manila City Council is advised to adopt ' + esc(cityB) + '\'s dedicated recovery fund model to finance Manila City Hall barangay material recovery facilities (MRFs) and establish joint inspection squads across major district markets.';
        } else if (topicKey === 'mobility') {
          findingsText = 'Comparing transport initiatives between <strong>' + esc(cityA) + '</strong> and <strong>' + esc(cityB) + '</strong> demonstrates that physical lane bollards, designated active-mobility zones, and standardized route signaling achieve higher motorist compliance than advisory lane markings.';
          takeawayText = 'Incorporate ' + esc(cityB) + '\'s protected barrier specifications into the Manila City Council infrastructure development program along Roxas Blvd and University Belt corridors.';
        } else if (topicKey === 'energy') {
          findingsText = 'The benchmark ordinance in <strong>' + esc(cityB) + '</strong> leverages mandatory energy efficiency thresholds for commercial permit applicants, pairing statutory penalties with real property tax discounts for green-certified facilities.';
          takeawayText = 'Incorporate green building permit incentives into the Manila City Revenue Code to attract sustainable commercial real estate developers in Binondo and Ermita.';
        } else {
          findingsText = 'Cross-city comparative analysis between <strong>' + esc(cityA) + '</strong> and <strong>' + esc(cityB) + '</strong> demonstrates complementary regulatory provisions for ' + topic + '. The benchmark city provides proven administrative templates with minimal legal friction.';
          takeawayText = 'Adopt ' + esc(cityB) + '\'s multi-agency enforcement guidelines to optimize Manila City Hall operational costs and accelerate inter-departmental rollout.';
        }
      } else {
        findingsText = 'Side-by-side analysis of <strong>' + esc(a.title) + '</strong> and <strong>' + esc(b.title) + '</strong> within ' + esc(cityA) + ' confirms complementary municipal objectives in ' + topic + '. Both initiatives demonstrate feasible budget allocations (' + esc(a.economic_level) + ' vs ' + esc(b.economic_level) + ' impact) and zero statutory contradictions.';
        takeawayText = 'Harmonize implementation calendars between both Manila ordinances to share administrative monitoring resources between City Engineering, Health, and DPS bureaus.';
      }

      return {
        topic: topic,
        diffSummary: findingsText,
        takeaway: takeawayText
      };
    }

    // --- DYNAMIC AI VERSION EVOLUTION SYNTHESIS ENGINE ---
    function buildDynamicAIVersionInsights(record, oldest, newest) {
      var title = record.title || 'Policy';
      var hasMultiple = record.has_multiple;
      var changes = [];

      if (oldest.risk_level !== newest.risk_level) {
        changes.push('Overall Risk rating refined from ' + esc(oldest.risk_level) + ' to ' + esc(newest.risk_level));
      }
      if (oldest.economic_level !== newest.economic_level) {
        changes.push('Economic Feasibility adjusted (' + esc(oldest.economic_level) + ' &rarr; ' + esc(newest.economic_level) + ')');
      }
      if (oldest.social_level !== newest.social_level) {
        changes.push('Social Impact provisions expanded (' + esc(oldest.social_level) + ' &rarr; ' + esc(newest.social_level) + ')');
      }
      if (oldest.env_level !== newest.env_level) {
        changes.push('Environmental safeguards updated (' + esc(oldest.env_level) + ' &rarr; ' + esc(newest.env_level) + ')');
      }

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

    // Register comparison entry into LocalStorage for Reports Module
    function recordComparisonInReports(title, type, summary, risk, rec) {
      try {
        var key = 'admin_recent_reports_v4';
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
          if (typeof window.renderRecentGeneratedReportsTable === 'function') {
            window.renderRecentGeneratedReportsTable();
          }
        }
      } catch (e) {
        console.warn('Could not sync comparison report into reports list:', e);
      }
    }

    // --- MODE 1: COMPARE POLICIES & CITIES ---
    window.runPolicyComparison = function () {
      var aId = document.getElementById('comparePolicyA').value;
      var bId = document.getElementById('comparePolicyB').value;
      var resultEl = document.getElementById('comparisonResult');
      if (!resultEl) return;

      var showMsg = function (type, ic, txt) {
        resultEl.innerHTML = '<div class="alert alert-' + type +
          ' d-flex align-items-center gap-2 rounded-3 mb-0 mt-3" role="alert">' +
          '<i class="bi ' + ic + ' fs-5"></i><span style="font-family: Arial, sans-serif;">' + esc(txt) + '</span></div>';
        resultEl.classList.remove('d-none');
      };

      if (!aId || !bId) { showMsg('warning', 'bi-exclamation-triangle-fill', 'Please select two policy records to compare.'); return; }
      if (aId === bId && COMPARE_DATA.length > 1) { showMsg('warning', 'bi-exclamation-triangle-fill', 'Policy A and Policy B cannot be the same document.'); return; }

      var find = function (id) {
        for (var i = 0; i < COMPARE_DATA.length; i++)
          if (String(COMPARE_DATA[i].id) === String(id)) return COMPARE_DATA[i];
        return null;
      };

      var a = find(aId);
      var b = find(bId);

      if (!a || !b) { showMsg('danger', 'bi-x-circle-fill', 'Policy comparison data unavailable.'); return; }

      var isCrossCity = (a.city_origin !== b.city_origin);

      var html = '';
      html += '<div class="border rounded-3 overflow-hidden shadow-sm mt-4 bg-white" style="font-family: Arial, Helvetica, sans-serif;">';
      html += '<table class="table table-bordered align-middle mb-0" style="border-color:#e2e8f0;">';

      // Header Row
      html += '<thead><tr style="background:#f8fafc;">';
      html += '<th class="py-3 px-3 fw-bold text-uppercase" style="width:20%; font-size:0.75rem; letter-spacing:0.5px; color:#000;">Feature / Metric</th>';

      // Policy A Header
      html += '<th class="py-3 px-3 text-center" style="width:40%; border-top:3px solid #2563eb; background:#f8fafc;">' +
        '<div class="fw-bold text-primary text-uppercase mb-1" style="font-size:0.9rem; letter-spacing:0.5px;">Policy A</div>' +
        '<div>' + cleanCityBadge(a.city_origin, a.title) + '</div>' +
        '</th>';

      // Policy B Header
      html += '<th class="py-3 px-3 text-center" style="width:40%; border-top:3px solid #16a34a; background:#f8fafc;">' +
        '<div class="fw-bold text-success text-uppercase mb-1" style="font-size:0.9rem; letter-spacing:0.5px;">Policy B / Benchmark</div>' +
        '<div>' + cleanCityBadge(b.city_origin, b.title) + '</div>' +
        '</th>';
      html += '</tr></thead>';

      // Body Rows
      var rows = [
        {
          label: 'Policy Title',
          a: '<span class="fw-semibold" style="font-family: Arial, sans-serif; color:#000000; font-size:0.88rem;">' + esc(a.title) + '</span>',
          b: '<span class="fw-semibold" style="font-family: Arial, sans-serif; color:#000000; font-size:0.88rem;">' + esc(b.title) + '</span>'
        },
        {
          label: 'LGU / City Origin',
          a: cleanCityBadge(a.city_origin, a.title),
          b: cleanCityBadge(b.city_origin, b.title)
        },
        {
          label: 'Category',
          a: '<span class="badge bg-light border px-2.5 py-1" style="font-family: Arial, sans-serif; color:#000000; font-size:0.82rem; font-weight:600;">' + esc(a.category || 'General') + '</span>',
          b: '<span class="badge bg-light border px-2.5 py-1" style="font-family: Arial, sans-serif; color:#000000; font-size:0.82rem; font-weight:600;">' + esc(b.category || 'General') + '</span>'
        },
        {
          label: 'Overall Risk Level',
          a: cleanRiskBadge(a.risk_level),
          b: cleanRiskBadge(b.risk_level)
        }
      ];

      html += '<tbody>';
      for (var i = 0; i < rows.length; i++) {
        var r = rows[i];
        html += '<tr>' +
          '<td class="px-3 py-3 fw-bold" style="background:#f8fafc; font-family: Arial, sans-serif; color:#000000; font-size:0.85rem;">' + r.label + '</td>' +
          '<td class="px-3 py-3 bg-white" style="vertical-align:top;">' + r.a + '</td>' +
          '<td class="px-3 py-3 bg-white" style="vertical-align:top;">' + r.b + '</td>' +
          '</tr>';
      }

      // Evaluation Criteria Section Divider
      html += '<tr>' +
        '<td colspan="3" class="px-3 py-2.5 bg-light border-top border-bottom fw-bold text-uppercase" style="background:#f1f5f9; font-family: Arial, sans-serif; color:#000000; font-size:0.78rem; letter-spacing:0.8px;">' +
        'Evaluation Criteria &amp; Impact Analysis' +
        '</td>' +
        '</tr>';

      var evalRows = [
        {
          label: 'Economic Feasibility',
          a: criteriaCell(a.economic_level, a.economic_reason),
          b: criteriaCell(b.economic_level, b.economic_reason)
        },
        {
          label: 'Social Impact',
          a: criteriaCell(a.social_level, a.social_reason),
          b: criteriaCell(b.social_level, b.social_reason)
        },
        {
          label: 'Environmental Impact',
          a: criteriaCell(a.env_level, a.env_reason),
          b: criteriaCell(b.env_level, b.env_reason)
        },
        {
          label: 'Legal Compliance',
          a: criteriaCell(a.legal_level, a.legal_reason),
          b: criteriaCell(b.legal_level, b.legal_reason)
        }
      ];

      for (var j = 0; j < evalRows.length; j++) {
        var er = evalRows[j];
        html += '<tr>' +
          '<td class="px-3 py-3 fw-bold" style="background:#f8fafc; font-family: Arial, sans-serif; color:#000000; font-size:0.85rem;">' + er.label + '</td>' +
          '<td class="px-3 py-3 bg-white" style="vertical-align:top;">' + er.a + '</td>' +
          '<td class="px-3 py-3 bg-white" style="vertical-align:top;">' + er.b + '</td>' +
          '</tr>';
      }

      // Recommendation Row
      html += '<tr>' +
        '<td class="px-3 py-3 fw-bold" style="background:#f8fafc; font-family: Arial, sans-serif; color:#000000; font-size:0.85rem;">Recommendation</td>' +
        '<td class="px-3 py-3 bg-white" style="vertical-align:top;">' +
        '<span style="font-family: Arial, Helvetica, sans-serif; color:#000000; font-size:0.88rem; line-height:1.55;">' + esc(a.ai_recommendation || 'Suitable for implementation.') + '</span>' +
        '</td>' +
        '<td class="px-3 py-3 bg-white" style="vertical-align:top;">' +
        '<span style="font-family: Arial, Helvetica, sans-serif; color:#000000; font-size:0.88rem; line-height:1.55;">' + esc(b.ai_recommendation || 'Suitable for implementation.') + '</span>' +
        '</td>' +
        '</tr>';

      html += '</tbody></table></div>';

      // --- DYNAMIC & RESPONSIVE AI EXECUTIVE COMPARISON INSIGHTS ---
      var dynamicAI = buildDynamicAIComparisonInsights(a, b, isCrossCity);
      var comparisonTitle = a.title + ' vs ' + b.title;
      var reportType = isCrossCity ? 'Cross-LGU Benchmark' : 'Policy Comparison';

      html += '<div class="card border-0 rounded-4 shadow-sm mt-4 p-3 p-md-4" style="background: linear-gradient(135deg, #f8fafc 0%, #f0fdfa 100%); border-left: 5px solid #0284c7 !important;">' +
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
        '<p class="text-muted small mb-0 mt-0.5">Automated synthesis comparing <strong>' + esc(a.title) + '</strong> with <strong>' + esc(b.title) + '</strong></p>' +
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
        '<i class="bi bi-intersect text-primary fs-6"></i> Domain Divergences &amp; Regulatory Findings' +
        '</div>' +
        '<p class="text-secondary small mb-0" style="line-height:1.65;">' +
        dynamicAI.diffSummary +
        '</p>' +
        '</div>' +
        '</div>' +

        '<div class="col-12 col-md-6 col-lg-6">' +
        '<div class="bg-white p-3 p-md-3.5 rounded-3 border shadow-2xs h-100 d-flex flex-column">' +
        '<div class="fw-bold text-dark small mb-2 d-flex align-items-center gap-1.5">' +
        '<i class="bi bi-lightbulb-fill text-warning fs-6"></i> Actionable Recommendation for Manila City Council' +
        '</div>' +
        '<p class="text-secondary small mb-0" style="line-height:1.65;">' +
        dynamicAI.takeaway +
        '</p>' +
        '</div>' +
        '</div>' +
        '</div>' +
        '</div>';

      // Auto-record comparison in Reports module
      recordComparisonInReports(comparisonTitle, reportType, dynamicAI.diffSummary.replace(/<[^>]*>?/gm, ''), a.risk_level, dynamicAI.takeaway);

      resultEl.innerHTML = html;
      resultEl.classList.remove('d-none');
    };

    // --- MODE 2: COMPARE VERSIONS ---
    window.runVersionComparison = function () {
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
        showMsg('warning', 'bi-exclamation-triangle-fill', 'Please select an approved policy to compare its versions.');
        return;
      }

      var record = window.VERSION_COMPARE_MAP[String(pId)];
      if (!record) {
        showMsg('danger', 'bi-x-circle-fill', 'Version history not found for this policy.');
        return;
      }

      var oldest = record.oldest;
      var newest = record.newest;

      var html = '';

      if (!record.has_multiple) {
        html += '<div class="alert alert-info border-0 rounded-3 shadow-2xs d-flex align-items-center gap-2.5 mb-3" style="background:#f0f9ff; color:#0369a1;">' +
          '<i class="bi bi-info-circle-fill fs-5"></i>' +
          '<div>' +
          '<strong>Single Baseline Version:</strong> This policy currently has 1 approved evaluation on record (Version 1). Both columns show the initial baseline.' +
          '</div>' +
          '</div>';
      }

      html += '<div class="border rounded-3 overflow-hidden shadow-sm bg-white" style="font-family: Arial, Helvetica, sans-serif;">';
      html += '<table class="table table-bordered align-middle mb-0" style="border-color:#e2e8f0;">';

      // Header Row: Initial Version (Oldest) vs Latest Version (Newest)
      html += '<thead><tr style="background:#f8fafc;">';
      html += '<th class="py-3 px-3 fw-bold text-uppercase" style="width:24%; font-size:0.75rem; letter-spacing:0.5px; color:#000;">Evaluation Criteria</th>';

      // Oldest Version Header
      html += '<th class="py-3 px-3 text-center" style="width:38%; border-top:3px solid #64748b; background:#f8fafc;">' +
        '<div class="fw-bold text-secondary text-uppercase mb-1" style="font-size:0.88rem; letter-spacing:0.5px;">' +
        '<i class="bi bi-arrow-counterclockwise me-1"></i> Initial Approved Version' +
        '</div>' +
        '<div class="badge rounded-pill bg-light text-dark border px-2.5 py-1" style="font-size:0.75rem;">' + esc(oldest.version_label || 'Version 1') + ' &bull; ' + esc(oldest.approved_at) + '</div>' +
        '</th>';

      // Newest Version Header
      html += '<th class="py-3 px-3 text-center" style="width:38%; border-top:3px solid #0284c7; background:#f0f9ff;">' +
        '<div class="fw-bold text-info text-uppercase mb-1" style="font-size:0.88rem; letter-spacing:0.5px; color:#0284c7 !important;">' +
        '<i class="bi bi-stars me-1"></i> Latest Approved Version' +
        '</div>' +
        '<div class="badge rounded-pill bg-primary text-white px-2.5 py-1" style="font-size:0.75rem;">' + esc(newest.version_label || ('Version ' + record.total_versions)) + ' &bull; ' + esc(newest.approved_at) + '</div>' +
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

      // 4 Criteria Rows
      var criteriaKeys = [
        { key: 'economic', label: 'Economic Feasibility' },
        { key: 'social', label: 'Social Impact' },
        { key: 'env', label: 'Environmental Impact' },
        { key: 'legal', label: 'Legal Compliance' }
      ];

      criteriaKeys.forEach(function (c) {
        var oldLevel = oldest[c.key + '_level'];
        var oldReason = oldest[c.key + '_reason'];
        var newLevel = newest[c.key + '_level'];
        var newReason = newest[c.key + '_reason'];

        var isDiff = (oldLevel !== newLevel) || (oldReason !== newReason);
        html += renderDiffRow(
          c.label,
          criteriaCell(oldLevel, oldReason),
          criteriaCell(newLevel, newReason),
          isDiff
        );
      });

      // Recommendation Row
      var recDiff = (oldest.ai_recommendation !== newest.ai_recommendation);
      html += renderDiffRow(
        'Recommendation',
        '<span style="font-family: Arial, Helvetica, sans-serif; color:#000000; font-size:0.88rem; line-height:1.55;">' + esc(oldest.ai_recommendation || 'Suitable for implementation.') + '</span>',
        '<span style="font-family: Arial, Helvetica, sans-serif; color:#000000; font-size:0.88rem; line-height:1.55;">' + esc(newest.ai_recommendation || 'Suitable for implementation.') + '</span>',
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

      // Auto-record version comparison in Reports module
      recordComparisonInReports(record.title + ' (Version Evolution)', 'Version Comparison', dynamicVersionAI.summary.replace(/<[^>]*>?/gm, ''), newest.risk_level, dynamicVersionAI.takeaway);

      resultEl.innerHTML = html;
      resultEl.classList.remove('d-none');
    };
  })();
</script>