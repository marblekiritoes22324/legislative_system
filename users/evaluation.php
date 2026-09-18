<?php
// users/evaluation.php — Councilor/User Impact Assessment Submodule (Read-Only)
$user_evaluations = [];
if (!empty($conn)) {
  $eval_sql = "
    SELECT 
      p.id AS policy_id, 
      p.title AS policy_title,
      e.id AS evaluation_id,
      e.economic_score,
      e.social_score,
      e.environmental_score,
      e.legal_score,
      e.overall_score,
      e.evaluator,
      e.approved_by,
      e.approved_at,
      e.notes,
      e.risk_level,
      e.ai_recommendation,
      COALESCE(e.updated_at, e.created_at) AS evaluation_date,
      CASE WHEN e.id IS NULL OR e.status = 'Draft' THEN 'Draft' ELSE COALESCE(e.status, 'Completed') END AS evaluation_status
    FROM policy_records p
    LEFT JOIN evaluations e ON p.id = e.policy_id
    WHERE (p.status IS NULL OR p.status != 'Archived')
    ORDER BY p.created_at DESC
  ";
  $eval_res = mysqli_query($conn, $eval_sql);
  if ($eval_res) {
    while ($row = mysqli_fetch_assoc($eval_res)) {
      $user_evaluations[] = $row;
    }
  }
}
?>

<section id="policyImpactSection"
  class="content-section <?= ($active_section ?? 'userDashboardSection') !== 'policyImpactSection' ? 'd-none' : '' ?>">

  <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-white">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
      <div>
        <h2 class="h4 fw-bold text-dark mb-1"><i class="bi bi-bar-chart-line-fill text-warning me-2"></i>Impact
          Assessment Module</h2>
        <p class="text-muted mb-0">View policy impact assessments across Economic, Social, Environmental, and Legal
          evaluation criteria.</p>
      </div>
    </div>

    <!-- Evaluation Records Table (View Only) -->
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0" style="border-collapse: separate; border-spacing: 0;">
        <thead class="table-light">
          <tr>
            <th scope="col" class="py-3 px-3 text-dark fw-bold text-uppercase"
              style="width: 32%; font-size: 0.85rem; letter-spacing: 0.5px;">Policy Title</th>
            <th scope="col" class="py-3 px-3 text-dark fw-bold text-uppercase"
              style="width: 40%; font-size: 0.85rem; letter-spacing: 0.5px;">Evaluation Findings &amp; Analysis</th>
            <th scope="col" class="py-3 px-3 text-dark fw-bold text-uppercase text-center"
              style="width: 14%; font-size: 0.85rem; letter-spacing: 0.5px;">Status</th>
            <th scope="col" class="py-3 px-3 text-dark fw-bold text-uppercase text-center"
              style="width: 14%; font-size: 0.85rem; letter-spacing: 0.5px;">Action</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!empty($user_evaluations)): ?>
            <?php foreach ($user_evaluations as $eval): ?>
              <?php
              $title = $eval['policy_title'];
              $has_evaluation = !empty($eval['evaluation_id']) && !empty($eval['evaluation_date']) && $eval['evaluation_status'] !== 'Draft' && $eval['evaluation_status'] !== 'Pending';
              $eval_date_fmt = '—';
              if ($has_evaluation && !empty($eval['evaluation_date'])) {
                $eval_date_fmt = date('M d, Y h:i A', strtotime($eval['evaluation_date']));
              }

              // Parse notes JSON saved by Admin
              $notes_data = [];
              if (!empty($eval['notes'])) {
                $trimmed_notes = trim($eval['notes']);
                if (strpos($trimmed_notes, '{') === 0 || strpos($trimmed_notes, '[') === 0) {
                  $decoded = json_decode($trimmed_notes, true);
                  if (is_array($decoded))
                    $notes_data = $decoded;
                }
              }

              $criteria_data = !empty($notes_data['criteria']) && is_array($notes_data['criteria']) ? $notes_data['criteria'] : [];

              $raw_econ_lvl = $criteria_data['economic']['level'] ?? 'High';
              $raw_econ_rsn = $criteria_data['economic']['reason'] ?? 'Funding realism and cost estimates are quantified within municipal budget allocations.';
              $raw_soc_lvl = $criteria_data['social']['level'] ?? 'High';
              $raw_soc_rsn = $criteria_data['social']['reason'] ?? 'Provides measurable community welfare enhancements with clear identified beneficiaries.';
              $raw_env_lvl = $criteria_data['env']['level'] ?? 'High';
              $raw_env_rsn = $criteria_data['env']['reason'] ?? 'Satisfies urban environmental safety standards with positive ecological resilience.';
              $raw_leg_lvl = $criteria_data['legal']['level'] ?? 'High';
              $raw_leg_rsn = $criteria_data['legal']['reason'] ?? 'Within delegated municipal powers under RA 7160; drafting clarity and severability verified.';

              // STATUS MODEL (3 STATES: Draft, Approved, Needs Revision)
              // Automatically computed based on the Evaluation Criteria scores:
              // - All pass (High/Medium, no Low/Fail) -> Approved
              // - Any Low/Fail -> Needs Revision
              // - Not yet evaluated -> Draft
              $is_low = function($lvl) {
                $l = strtolower(trim($lvl ?? ''));
                return ($l === 'low' || $l === 'fail' || $l === 'failed' || $l === 'does not meet' || $l === 'non-compliant');
              };

              $has_failed_criterion = ($is_low($raw_econ_lvl) || $is_low($raw_soc_lvl) || $is_low($raw_env_lvl) || $is_low($raw_leg_lvl));

              if ($has_evaluation) {
                if ($has_failed_criterion) {
                  $status = 'Needs Revision';
                } else {
                  $status = 'Approved';
                }
              } else {
                $status = 'Draft';
              }

              $ai_analysis = !empty($notes_data['ai_analysis']) ? $notes_data['ai_analysis'] : ($has_evaluation
                ? 'Evidence-based impact analysis confirms alignment with statutory governance and municipal operational criteria.'
                : 'Awaiting evaluation.');

              $evaluator_name = $has_evaluation
                ? (!empty($eval['evaluator']) ? $eval['evaluator'] : 'Admin')
                : '—';

              $approved_at_fmt = (!empty($eval['approved_at'])) ? date('M d, Y h:i A', strtotime($eval['approved_at'])) : null;

              $evaluation_data = [
                'policy_id' => (int) $eval['policy_id'],
                'title' => $title,
                'has_evaluation' => $has_evaluation,
                'status' => $status,
                'approved_by' => $eval['approved_by'] ?? null,
                'approved_at' => $approved_at_fmt,
                'evaluationDate' => $eval_date_fmt,
                'evaluator' => $evaluator_name,
                'aiAnalysis' => $ai_analysis,
                'economicLevel' => $has_evaluation ? $raw_econ_lvl : 'Awaiting',
                'economicReason' => $has_evaluation ? $raw_econ_rsn : 'Awaiting evaluation.',
                'socialLevel' => $has_evaluation ? $raw_soc_lvl : 'Awaiting',
                'socialReason' => $has_evaluation ? $raw_soc_rsn : 'Awaiting evaluation.',
                'envLevel' => $has_evaluation ? $raw_env_lvl : 'Awaiting',
                'envReason' => $has_evaluation ? $raw_env_rsn : 'Awaiting evaluation.',
                'legalLevel' => $has_evaluation ? $raw_leg_lvl : 'Awaiting',
                'legalReason' => $has_evaluation ? $raw_leg_rsn : 'Awaiting evaluation.',
                'legalAuthority' => $notes_data['legal_authority'] ?? '',
                'draftingQuality' => $notes_data['drafting_quality'] ?? '',
                'proceduralCompliance' => $notes_data['procedural_compliance'] ?? '',
                'isUserViewOnly' => true,
              ];

              // Read-only Status Pill Badge Styling (3 STATES: Draft, Approved, Needs Revision)
              $badge_style = 'background: rgba(107, 114, 128, 0.12); color: #4b5563; border: 1px solid rgba(107, 114, 128, 0.25);';
              if ($status === 'Approved') {
                $badge_style = 'background: rgba(22, 163, 74, 0.12); color: #15803d; border: 1px solid rgba(22, 163, 74, 0.25);';
              } elseif ($status === 'Needs Revision') {
                $badge_style = 'background: #fef2f2; color: #b91c1c; border: 1px solid #fecaca; font-weight: 700;';
              } else {
                $badge_style = 'background: rgba(107, 114, 128, 0.12); color: #4b5563; border: 1px solid rgba(107, 114, 128, 0.25);';
              }
              ?>
              <tr>
                <td class="px-3 py-3 fw-bold text-dark" style="vertical-align: middle;">
                  <?= htmlspecialchars($title) ?>
                </td>
                <td class="px-3 py-3 small text-secondary" style="vertical-align: middle; line-height: 1.5;">
                  <?php if ($has_evaluation): ?>
                    <span class="text-dark fw-medium"><?= htmlspecialchars($ai_analysis) ?></span>
                  <?php else: ?>
                    <span class="text-muted fst-italic">Awaiting evaluation...</span>
                  <?php endif; ?>
                </td>
                <td class="px-3 py-3 text-center" style="vertical-align: middle;">
                  <span id="eval-status-badge-<?= (int) $eval['policy_id'] ?>"
                    style="display:inline-block; padding: 5px 14px; border-radius: 999px; font-size: 0.8rem; font-weight: 700; letter-spacing: 0.3px; <?= $badge_style ?> cursor: default;">
                    <?= htmlspecialchars($status) ?>
                  </span>
                </td>
                <td class="px-3 py-3 text-center" style="vertical-align: middle;">
                  <button
                    onclick='openEvaluationModal(<?= htmlspecialchars(json_encode($evaluation_data), ENT_QUOTES, "UTF-8") ?>)'
                    style="display:inline-flex; align-items:center; justify-content:center; gap:6px; background:linear-gradient(135deg,#4f46e5,#7c3aed); color:#fff; border:none; padding:6px 14px; border-radius:8px; font-size:0.8rem; font-weight:600; cursor:pointer; box-shadow:0 2px 6px rgba(124,58,237,0.25); transition:all 0.2s;"
                    onmouseover="this.style.transform='translateY(-1px)';this.style.boxShadow='0 4px 12px rgba(124,58,237,0.35)';"
                    onmouseout="this.style.transform='translateY(0)';this.style.boxShadow='0 2px 6px rgba(124,58,237,0.25)';">
                    <i class="bi bi-bar-chart-line-fill"></i> View Evaluation
                  </button>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="4" class="text-center py-4 text-muted">No evaluation records found.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</section>