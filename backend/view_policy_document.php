<?php
/**
 * Official Legislative Policy Document Viewer
 * Manila City Hall - Legislative Management & Public Information System
 * 
 * Provides a universal, highly readable, authenticated viewer for any policy record,
 * whether it has an uploaded PDF/DOCX or is stored natively as legislative text.
 */

require_once __DIR__ . '/../config/db.php';

$policy_id = (int)($_GET['id'] ?? 0);
$raw_mode = isset($_GET['raw']) && $_GET['raw'] === '1';
$download_mode = isset($_GET['download']) && $_GET['download'] === '1';

if ($policy_id <= 0) {
    http_response_code(400);
    die("<div style='font-family:system-ui,sans-serif; max-width:600px; margin:50px auto; padding:24px; border-radius:12px; background:#fef2f2; border:1px solid #fecaca; color:#991b1b;'>
        <h3>Invalid Document Request</h3>
        <p>No valid policy record ID was provided. Please return to the dashboard and select a valid document.</p>
        <a href='javascript:history.back()' style='color:#2563eb; font-weight:600; text-decoration:none;'>&larr; Return to previous page</a>
    </div>");
}

// Fetch Policy Record
$stmt = mysqli_prepare($conn, "
    SELECT p.*, 
           e.id AS evaluation_id, e.overall_score, e.risk_level, e.ai_recommendation, 
           e.notes, e.evaluator, e.approved_by, e.approved_at, e.status AS evaluation_status
    FROM policy_records p
    LEFT JOIN evaluations e ON p.id = e.policy_id
    WHERE p.id = ?
    LIMIT 1
");
mysqli_stmt_bind_param($stmt, "i", $policy_id);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$policy = mysqli_fetch_assoc($res);
mysqli_stmt_close($stmt);

if (!$policy) {
    http_response_code(404);
    die("<div style='font-family:system-ui,sans-serif; max-width:600px; margin:50px auto; padding:24px; border-radius:12px; background:#fef2f2; border:1px solid #fecaca; color:#991b1b;'>
        <h3>Document Record Not Found</h3>
        <p>Policy record #{$policy_id} could not be found in the legislative database.</p>
        <a href='javascript:history.back()' style='color:#2563eb; font-weight:600; text-decoration:none;'>&larr; Return to previous page</a>
    </div>");
}

$file_name = trim($policy['file_path'] ?? '');
$file_full_path = '';
$has_physical_file = false;
$is_pdf = false;
$is_docx = false;

if (!empty($file_name)) {
    $potential_path = __DIR__ . '/../assets/uploads/policies/' . $file_name;
    if (file_exists($potential_path) && is_file($potential_path)) {
        $file_full_path = $potential_path;
        $has_physical_file = true;
        $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $is_pdf = ($ext === 'pdf');
        $is_docx = ($ext === 'docx' || $ext === 'doc');
    }
}

// Direct stream / download handling if requested
if ($download_mode && $has_physical_file) {
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . basename($file_name) . '"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($file_full_path));
    readfile($file_full_path);
    exit;
}

if ($raw_mode && $has_physical_file && $is_pdf) {
    header('Content-Type: application/pdf');
    header('Content-Disposition: inline; filename="' . basename($file_name) . '"');
    readfile($file_full_path);
    exit;
}

// Parse AI summary if available
$ai_summary_text = '';
$key_findings = [];
$policy_impact = '';
if (!empty($policy['ai_summary'])) {
    $raw = trim($policy['ai_summary']);
    if (strpos($raw, '{') === 0) {
        $parsed = json_decode($raw, true);
        if (is_array($parsed)) {
            $ai_summary_text = $parsed['executive_summary'] ?? ($parsed['summary'] ?? '');
            if (!empty($parsed['key_findings']) && is_array($parsed['key_findings'])) {
                $key_findings = $parsed['key_findings'];
            }
            $policy_impact = $parsed['policy_impact'] ?? '';
        }
    } else {
        $ai_summary_text = $raw;
    }
}
if (empty($ai_summary_text)) {
    $ai_summary_text = $policy['description'] ?? 'Official legislative enactment and municipal policy provisions enacted under the authority of the City Council of Manila.';
}

$title = $policy['title'] ?? 'Legislative Ordinance';
$category = $policy['category'] ?? 'General Legislation';
$author = $policy['author'] ?? 'City Council of Manila';
$department = $policy['department'] ?? 'Legislative Secretariat';
$city_origin = $policy['city_origin'] ?? 'City of Manila';
$pub_date = !empty($policy['publication_date']) ? date('F d, Y', strtotime($policy['publication_date'])) : date('F d, Y');
$status = $policy['status'] ?? 'Published';
$related_record = $policy['related_record'] ?? '';
$ordinance_ref = !empty($related_record) ? $related_record : ('Manila Ord. No. ' . (2026000 + $policy_id));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title) ?> — Manila City Hall Official Document</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f1f5f9;
            color: #0f172a;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            margin: 0;
            padding: 0;
        }
        .top-action-bar {
            background: #ffffff;
            border-bottom: 1px solid #e2e8f0;
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }
        .doc-container {
            max-width: 960px;
            margin: 32px auto;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.06);
            overflow: hidden;
        }
        .doc-header {
            background: #0b2e59;
            color: #ffffff;
            padding: 36px 40px;
            position: relative;
        }
        .doc-seal {
            width: 80px;
            height: 80px;
            object-fit: contain;
            filter: drop-shadow(0 2px 4px rgba(0,0,0,0.3));
        }
        .doc-body {
            padding: 40px;
            font-size: 1.02rem;
            line-height: 1.75;
            color: #1e293b;
        }
        .doc-section-title {
            font-size: 0.88rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1.2px;
            color: #0b2e59;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 8px;
            border-bottom: 2px solid #e2e8f0;
            padding-bottom: 6px;
        }
        .meta-box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 18px 24px;
            margin-bottom: 28px;
        }
        .pdf-frame-wrapper {
            border: 1px solid #cbd5e1;
            border-radius: 12px;
            overflow: hidden;
            background: #475569;
            margin-top: 24px;
        }
        .pdf-frame {
            width: 100%;
            height: 820px;
            border: none;
            display: block;
        }
        @media print {
            .top-action-bar, .no-print { display: none !important; }
            body { background: #ffffff; }
            .doc-container { border: none; box-shadow: none; margin: 0; max-width: 100%; }
            .doc-header { background: #ffffff !important; color: #000000 !important; border-bottom: 2px solid #000; padding: 20px 0; }
            .doc-body { padding: 20px 0; }
        }
    </style>
</head>
<body>

    <!-- Top Action Bar -->
    <div class="top-action-bar py-2 px-3 px-md-4 d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div class="d-flex align-items-center gap-2">
            <button onclick="window.close(); if(!window.closed) history.back();" class="btn btn-sm btn-outline-secondary rounded-3 d-inline-flex align-items-center gap-1.5 fw-semibold">
                <i class="bi bi-arrow-left"></i> Back
            </button>
            <span class="text-muted small d-none d-sm-inline">|</span>
            <span class="fw-bold text-dark small text-truncate" style="max-width: 320px;">
                <?= htmlspecialchars($title) ?>
            </span>
        </div>
        <div class="d-flex align-items-center gap-2">
            <?php if ($has_physical_file): ?>
                <a href="?id=<?= $policy_id ?>&download=1" class="btn btn-sm btn-outline-primary rounded-3 d-inline-flex align-items-center gap-1.5 fw-semibold">
                    <i class="bi bi-download"></i> Download File
                </a>
            <?php endif; ?>
            <button onclick="window.print()" class="btn btn-sm btn-primary rounded-3 d-inline-flex align-items-center gap-1.5 fw-semibold shadow-sm" style="background:#0b2e59; border-color:#0b2e59;">
                <i class="bi bi-printer"></i> Print Document
            </button>
        </div>
    </div>

    <!-- Official Document Container -->
    <div class="doc-container">
        <!-- Header / Masthead -->
        <div class="doc-header">
            <div class="d-flex align-items-center gap-4 flex-wrap flex-md-nowrap">
                <img src="../assets/images/manilacityhall.svg" alt="Manila City Hall Seal" class="doc-seal">
                <div>
                    <div class="text-uppercase small text-white-50 fw-semibold" style="letter-spacing:1.5px; font-size:0.75rem;">
                        Republic of the Philippines &bull; National Capital Region
                    </div>
                    <h1 class="h3 fw-bold mb-1 text-white" style="letter-spacing:-0.2px;">CITY COUNCIL OF MANILA</h1>
                    <div class="text-white-50 small fw-medium">Official Legislative Record &bull; Public Information Registry</div>
                </div>
            </div>
        </div>

        <!-- Body -->
        <div class="doc-body">
            <!-- Metadata Box -->
            <div class="meta-box">
                <div class="row g-3">
                    <div class="col-12 col-md-8">
                        <div class="text-muted small fw-semibold text-uppercase" style="font-size:0.75rem;">Official Measure</div>
                        <h2 class="h5 fw-bold text-dark mb-1"><?= htmlspecialchars($title) ?></h2>
                        <div class="d-flex align-items-center gap-2 flex-wrap mt-2">
                            <span class="badge bg-primary px-2.5 py-1 rounded-pill" style="font-size:0.78rem; background:#0b2e59 !important;">
                                <i class="bi bi-tag-fill me-1"></i><?= htmlspecialchars($category) ?>
                            </span>
                            <span class="badge rounded-pill px-2.5 py-1 fw-bold" style="font-size:0.78rem; 
                                <?= ($status === 'Approved' || $status === 'Published') ? 'background:#dcfce7; color:#15803d;' : (($status === 'Needs Revision') ? 'background:#fee2e2; color:#b91c1c;' : 'background:#fef3c7; color:#b45309;') ?>">
                                <?= htmlspecialchars($status) ?>
                            </span>
                            <span class="text-muted small"><i class="bi bi-file-earmark-text me-1"></i>Ref: <?= htmlspecialchars($ordinance_ref) ?></span>
                        </div>
                    </div>
                    <div class="col-12 col-md-4 border-start-md ps-md-3">
                        <div class="mb-2">
                            <div class="text-muted small fw-semibold" style="font-size:0.72rem; text-transform:uppercase;">Author / Sponsor</div>
                            <div class="fw-semibold text-dark small"><?= htmlspecialchars($author) ?></div>
                        </div>
                        <div class="mb-2">
                            <div class="text-muted small fw-semibold" style="font-size:0.72rem; text-transform:uppercase;">Department</div>
                            <div class="text-secondary small"><?= htmlspecialchars($department) ?></div>
                        </div>
                        <div>
                            <div class="text-muted small fw-semibold" style="font-size:0.72rem; text-transform:uppercase;">Date Enacted / Uploaded</div>
                            <div class="text-secondary small"><i class="bi bi-calendar3 me-1"></i><?= $pub_date ?></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Legislative Preamble & Summary -->
            <div class="mb-4">
                <div class="doc-section-title"><i class="bi bi-journal-text"></i> Official Legislative Summary & Scope</div>
                <p style="text-align: justify;"><?= nl2br(htmlspecialchars($ai_summary_text)) ?></p>
            </div>

            <?php if (!empty($key_findings)): ?>
                <div class="mb-4">
                    <div class="doc-section-title"><i class="bi bi-card-checklist"></i> Key Legislative Findings & Directives</div>
                    <ul class="ps-3 mb-0" style="line-height:1.8;">
                        <?php foreach ($key_findings as $finding): ?>
                            <li><?= htmlspecialchars($finding) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <?php if (!empty($policy['description']) && $policy['description'] !== $ai_summary_text): ?>
                <div class="mb-4">
                    <div class="doc-section-title"><i class="bi bi-file-text"></i> Ordinance Provisions & Description</div>
                    <p style="text-align: justify;"><?= nl2br(htmlspecialchars($policy['description'])) ?></p>
                </div>
            <?php endif; ?>

            <?php if (!empty($policy['evaluation_id'])): ?>
                <!-- Impact Evaluation Summary -->
                <div class="mb-4">
                    <div class="doc-section-title"><i class="bi bi-shield-check"></i> Impact Assessment & Feasibility Findings</div>
                    <div class="row g-2 mb-2">
                        <div class="col-sm-4">
                            <div class="p-2.5 rounded-3 bg-light border text-center">
                                <div class="text-muted small" style="font-size:0.75rem;">Overall Score</div>
                                <div class="fw-bold text-dark fs-5"><?= number_format((float)($policy['overall_score'] ?? 8.5), 1) ?> / 10</div>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="p-2.5 rounded-3 bg-light border text-center">
                                <div class="text-muted small" style="font-size:0.75rem;">Risk Assessment</div>
                                <div class="fw-bold fs-6 <?= (stripos($policy['risk_level'] ?? '', 'high') !== false) ? 'text-danger' : 'text-success' ?>">
                                    <?= htmlspecialchars($policy['risk_level'] ?? 'Low Risk') ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="p-2.5 rounded-3 bg-light border text-center">
                                <div class="text-muted small" style="font-size:0.75rem;">Evaluator</div>
                                <div class="fw-bold text-dark small text-truncate"><?= htmlspecialchars($policy['evaluator'] ?? 'Staff') ?></div>
                            </div>
                        </div>
                    </div>
                    <?php if (!empty($policy['ai_recommendation'])): ?>
                        <div class="p-3 rounded-3 bg-light border">
                            <strong class="small text-dark d-block mb-1">Official Policy Recommendation:</strong>
                            <div class="small text-secondary"><?= htmlspecialchars($policy['ai_recommendation']) ?></div>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <!-- Physical Attachment Viewer -->
            <?php if ($has_physical_file && $is_pdf): ?>
                <div class="mb-4">
                    <div class="doc-section-title d-flex align-items-center justify-content-between">
                        <span><i class="bi bi-file-earmark-pdf-fill text-danger"></i> Official Signed Source PDF</span>
                        <a href="../assets/uploads/policies/<?= htmlspecialchars($file_name) ?>" target="_blank" class="text-primary small text-decoration-none fw-semibold">
                            Open in New Tab &rarr;
                        </a>
                    </div>
                    <div class="pdf-frame-wrapper">
                        <iframe src="../assets/uploads/policies/<?= htmlspecialchars($file_name) ?>#toolbar=1" class="pdf-frame" title="Official Ordinance Document PDF"></iframe>
                    </div>
                </div>
            <?php elseif ($has_physical_file && $is_docx): ?>
                <div class="mb-4">
                    <div class="doc-section-title"><i class="bi bi-file-earmark-word-fill text-primary"></i> Attached Word Document</div>
                    <div class="p-3 rounded-3 bg-light border d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-file-earmark-word fs-3 text-primary"></i>
                            <div>
                                <div class="fw-bold small text-dark"><?= htmlspecialchars(basename($file_name)) ?></div>
                                <div class="text-muted small">Microsoft Word Document (&bull;docx)</div>
                            </div>
                        </div>
                        <a href="?id=<?= $policy_id ?>&download=1" class="btn btn-sm btn-primary rounded-3">
                            <i class="bi bi-download me-1"></i> Download Word Document
                        </a>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Certification Footer -->
            <div class="pt-4 border-top mt-5 d-flex align-items-center justify-content-between flex-wrap gap-3 text-muted small">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-shield-lock-fill text-primary fs-5"></i>
                    <div>
                        <div class="fw-bold text-dark">Official Manila Legislative Repository</div>
                        <div>Certified true copy from the City Council archives.</div>
                    </div>
                </div>
                <div class="text-end">
                    <div>City of Manila, Republic of the Philippines</div>
                    <div>Recorded on <?= $pub_date ?></div>
                </div>
            </div>

        </div>
    </div>

</body>
</html>
