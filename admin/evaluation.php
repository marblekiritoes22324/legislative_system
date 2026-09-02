<?php
if (!isset($evaluations) || !is_array($evaluations)) {
  $evaluations = [];
}
?>
<section id="impactAssessmentSection"
  class="content-section <?= ($active_section ?? 'adminDashboardSection') !== 'impactAssessmentSection' ? 'd-none' : '' ?>">
  <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-white">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
      <div>
        <h2 class="h4 fw-bold text-dark mb-1"><i class="bi bi-bar-chart-line text-warning me-2"></i>Impact Assessment
          Module</h2>
        <p class="text-muted mb-0">Assesses policy effectiveness across Economic, Social, Environmental, and Legal
          evaluation criteria.</p>
      </div>
    </div>

    <!-- Evaluation Records Table -->
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0" style="border-collapse: separate; border-spacing: 0;">
        <thead class="table-light">
          <tr>
            <th scope="col" class="py-3 px-3 text-dark fw-bold text-uppercase"
              style="width: 32%; font-size: 0.85rem; letter-spacing: 0.5px;">Policy Title</th>
            <th scope="col" class="py-3 px-3 text-dark fw-bold text-uppercase"
              style="width: 40%; font-size: 0.85rem; letter-spacing: 0.5px;">AI Recommendation</th>
            <th scope="col" class="py-3 px-3 text-dark fw-bold text-uppercase text-center"
              style="width: 14%; font-size: 0.85rem; letter-spacing: 0.5px;">Status</th>
            <th scope="col" class="py-3 px-3 text-dark fw-bold text-uppercase text-center"
              style="width: 14%; font-size: 0.85rem; letter-spacing: 0.5px;">Action</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!empty($evaluations)): ?>
            <?php foreach ($evaluations as $eval): ?>
              <?php
              $title = $eval['policy_title'];
              $has_evaluation = !empty($eval['evaluation_id']) && !empty($eval['evaluation_date']) && $eval['evaluation_status'] !== 'Draft' && $eval['evaluation_status'] !== 'Pending';
              $status = $has_evaluation ? (!empty($eval['evaluation_status']) && $eval['evaluation_status'] !== '0' ? $eval['evaluation_status'] : 'Completed') : 'Draft';
              $overall_score = $has_evaluation ? number_format((float) $eval['overall_score'], 1) : null;
              $risk_level = 'N/A';

              // Build a human-friendly evaluation date
              $eval_date_fmt = '—';
              if ($has_evaluation && !empty($eval['evaluation_date'])) {
                $eval_date_fmt = date('M d, Y h:i A', strtotime($eval['evaluation_date']));
              }

              // Parse notes JSON if saved
              $notes_data = [];
              if (!empty($eval['notes'])) {
                $trimmed_notes = trim($eval['notes']);
                if (strpos($trimmed_notes, '{') === 0 || strpos($trimmed_notes, '[') === 0) {
                  $decoded = json_decode($trimmed_notes, true);
                  if (is_array($decoded)) {
                    $notes_data = $decoded;
                  }
                }
              }

              $improvements = [];
              if (!empty($notes_data['improvements']) && is_array($notes_data['improvements'])) {
                $improvements = $notes_data['improvements'];
              } elseif ($has_evaluation) {
                if (!empty($eval['economic_score']) && $eval['economic_score'] < 8)
                  $improvements[] = 'Improve economic feasibility planning.';
                if (!empty($eval['social_score']) && $eval['social_score'] < 8)
                  $improvements[] = 'Strengthen social impact measures.';
                if (!empty($eval['environmental_score']) && $eval['environmental_score'] < 8)
                  $improvements[] = 'Enhance environmental safety provisions.';
                if (!empty($eval['legal_score']) && $eval['legal_score'] < 8)
                  $improvements[] = 'Address legal compliance gaps.';
              }
              if (empty($improvements)) {
                $improvements = [
                  'Include a detailed implementation timeline.',
                  'Provide an estimated budget allocation.',
                  'Define measurable performance indicators.',
                  'Assign responsible offices for monitoring and evaluation.',
                ];
              }

              $ai_analysis = !empty($notes_data['ai_analysis']) ? $notes_data['ai_analysis'] : ($has_evaluation
                ? 'The AI analyzed the proposed policy and determined that it supports statutory governance objectives across economic, social, environmental, and legal criteria.'
                : 'No evaluation has been performed yet.');

              $reason = !empty($notes_data['reason']) ? $notes_data['reason'] : ($has_evaluation
                ? 'The proposed policy aligns with its intended objectives and demonstrates measurable benefits across the evaluated criteria.'
                : '');

              $evaluator_name = (!empty($eval['evaluator']) && $eval['evaluator'] !== 'Administration' && $eval['evaluator'] !== 'System Administrator') ? $eval['evaluator'] : 'Admin';

              $criteria_data = !empty($notes_data['criteria']) && is_array($notes_data['criteria']) ? $notes_data['criteria'] : [];

              $approved_at_fmt = (!empty($eval['approved_at'])) ? date('M d, Y h:i A', strtotime($eval['approved_at'])) : null;

              $evaluation_data = [
                'policy_id' => (int) $eval['policy_id'],
                'title' => $title,
                'has_evaluation' => $has_evaluation,
                'status' => $status,
                'approved_by' => $eval['approved_by'] ?? null,
                'approved_at' => $approved_at_fmt,
                'riskLevel' => $risk_level,
                'evaluationDate' => $eval_date_fmt,
                'evaluator' => $evaluator_name,
                'aiAnalysis' => $ai_analysis,
                'recommendation' => $has_evaluation
                  ? ($eval['ai_recommendation'] ?: 'Enact Policy with Enhanced Inter-Agency Coordination and Implementation Monitoring')
                  : 'Awaiting evaluation.',
                'reason' => $reason,
                'improvements' => $improvements,
                'economicLevel' => $criteria_data['economic']['level'] ?? 'Low',
                'economicReason' => $criteria_data['economic']['reason'] ?? 'Funding and implementation costs are manageable and available.',
                'socialLevel' => $criteria_data['social']['level'] ?? 'Low',
                'socialReason' => $criteria_data['social']['reason'] ?? 'The policy provides benefits to affected communities and improves quality of life.',
                'envLevel' => $criteria_data['env']['level'] ?? 'Low',
                'envReason' => $criteria_data['env']['reason'] ?? 'The policy has minimal expected environmental effects.',
                'legalLevel' => $criteria_data['legal']['level'] ?? 'Low',
                'legalReason' => $criteria_data['legal']['reason'] ?? 'No major legal conflicts were identified with existing laws and regulations.',
              ];

              // Read-only Status Pill Badge Styling
              $badge_style = 'background:#f3f4f6; color:#4b5563; border:1px solid #e5e7eb;';
              if ($status === 'Approved') {
                $badge_style = 'background:#dcfce7; color:#15803d; border:1px solid #bbf7d0;';
              } elseif ($status === 'Completed') {
                $badge_style = 'background:#dbeafe; color:#1d4ed8; border:1px solid #bfdbfe;';
              } elseif ($status === 'Under Review' || $status === 'Draft' || $status === 'Pending') {
                $badge_style = 'background:#fef3c7; color:#b45309; border:1px solid #fde68a;';
              }
              ?>
              <tr id="eval-row-<?= (int) $eval['policy_id'] ?>" data-policy-id="<?= (int) $eval['policy_id'] ?>">
                <td class="px-3 py-3 fw-bold text-dark" style="vertical-align: middle;">
                  <?= htmlspecialchars($title) ?>
                </td>
                <td class="px-3 py-3 small text-secondary" id="eval-rec-cell-<?= (int) $eval['policy_id'] ?>"
                  style="vertical-align: middle; line-height: 1.5;">
                  <?php if ($has_evaluation): ?>
                    <span
                      class="text-dark fw-medium"><?= htmlspecialchars($eval['ai_recommendation'] ?: 'Enact Policy with Enhanced Inter-Agency Coordination and Implementation Monitoring') ?></span>
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
                  <button id="eval-view-btn-<?= (int) $eval['policy_id'] ?>"
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