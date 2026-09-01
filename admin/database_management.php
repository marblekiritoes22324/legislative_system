<?php
// ============================================================
// Database Management Module — Manila City Hall LIS
// ============================================================

// If accessed directly or included without $conn, include database config
if (!isset($conn) || empty($conn)) {
    require_once __DIR__ . '/../config/db.php';
}

$db_name = $database ?? 'legislative_system';

// Fetch table statistics
$table_stats = [];
$total_rows = 0;
$total_size_bytes = 0;

$status_res = mysqli_query($conn, "SHOW TABLE STATUS");
if ($status_res) {
    while ($row = mysqli_fetch_assoc($status_res)) {
        $tbl_name = $row['Name'];
        $rows = intval($row['Rows']);
        $size = intval($row['Data_length']) + intval($row['Index_length']);
        
        $table_stats[] = [
            'name' => $tbl_name,
            'engine' => $row['Engine'] ?? 'InnoDB',
            'rows' => $rows,
            'size_bytes' => $size,
            'size_formatted' => $size > 1048576 ? round($size / 1048576, 2) . ' MB' : round($size / 1024, 2) . ' KB',
            'collation' => $row['Collation'] ?? 'utf8mb4_unicode_ci',
            'update_time' => $row['Update_time'] ?? '-'
        ];

        $total_rows += $rows;
        $total_size_bytes += $size;
    }
}

$total_tables_count = count($table_stats);
$total_size_formatted = $total_size_bytes > 1048576 ? round($total_size_bytes / 1048576, 2) . ' MB' : round($total_size_bytes / 1024, 2) . ' KB';
$mysql_version = mysqli_get_server_info($conn);

// Map table purposes
$table_descriptions = [
    'policy_records' => 'Legislative policies, ordinances, resolutions, AI summaries & file attachments',
    'user_directory' => 'Official councilor, staff, analyst, and administrative accounts',
    'users' => 'Legacy user accounts and credential table',
    'evaluations' => 'Impact assessment metrics, AI recommendation scores & risk analyses',
    'comparisons' => 'Cross-policy comparative analysis matrices and benchmarks',
    'reports' => 'Generated executive summaries, audit dossiers & export records',
    'audit_logs' => 'Security audit trails, system access events & administrative actions'
];
?>

<style>
    .db-card-action {
        border-radius: 18px;
        transition: transform 0.22s ease, box-shadow 0.22s ease;
        border: 1px solid rgba(226, 232, 240, 0.9);
        overflow: hidden;
    }

    .db-card-action:hover {
        transform: translateY(-4px);
        box-shadow: 0 14px 28px rgba(11, 27, 61, 0.08) !important;
    }

    .db-dropzone {
        border: 2px dashed #CBD5E1;
        background: #F8FAFC;
        border-radius: 14px;
        padding: 28px 20px;
        text-align: center;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .db-dropzone:hover, .db-dropzone.dragover {
        border-color: #2563EB;
        background: #EFF6FF;
    }

    .db-stat-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 4px 12px;
        border-radius: 999px;
        font-size: 0.78rem;
        font-weight: 600;
    }

    .table-health-badge {
        font-size: 0.78rem;
        font-weight: 600;
        padding: 4px 10px;
        border-radius: 6px;
    }
</style>

<section id="databaseManagementSection" class="content-section <?= ($active_section ?? 'adminDashboardSection') !== 'databaseManagementSection' ? 'd-none' : '' ?>">

    <!-- Header & Breadcrumb -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <h2 class="h3 fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                    <i class="bi bi-database-gear text-primary"></i> Database Management
                </h2>
                <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2.5 py-1 rounded-pill small">
                    <i class="bi bi-check-circle-fill me-1"></i> Connected
                </span>
            </div>
            <p class="text-muted mb-0" style="font-size: 0.92rem;">
                Manage database snapshots, perform 1-click SQL backup exports, restore system archives, and optimize database tables.
            </p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <button type="button" class="btn btn-outline-secondary rounded-3 px-3 py-2 fw-semibold shadow-sm d-inline-flex align-items-center gap-2" onclick="optimizeAllTables(this)">
                <i class="bi bi-arrow-repeat"></i> Optimize All Tables
            </button>
            <a href="../backend/database_backup_handler.php?action=export_backup" class="btn btn-primary rounded-3 px-4 py-2 fw-bold shadow-sm d-inline-flex align-items-center gap-2" style="background: #0B1B3D; border-color: #0B1B3D;">
                <i class="bi bi-download text-warning"></i> Instant SQL Export
            </a>
        </div>
    </div>

    <!-- Alert Banner Container for Feedback -->
    <div id="dbAlertContainer"></div>

    <!-- Metric Overview Cards -->
    <div class="row g-3 mb-4">
        <!-- Database Name & Engine -->
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white border-start border-4 border-primary h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small fw-semibold text-uppercase" style="letter-spacing: 0.5px;">Active Database</span>
                        <h4 class="fw-bold text-dark mb-0 mt-1" style="font-size:1.15rem;"><?= htmlspecialchars($db_name) ?></h4>
                        <span class="text-primary small fw-medium" style="font-size:0.75rem;">MySQL <?= htmlspecialchars($mysql_version) ?></span>
                    </div>
                    <div class="p-3 bg-primary bg-opacity-10 text-primary rounded-4 fs-4">
                        <i class="bi bi-server"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Tables -->
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white border-start border-4 border-info h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small fw-semibold text-uppercase" style="letter-spacing: 0.5px;">System Tables</span>
                        <h4 class="fw-bold text-dark mb-0 mt-1" style="font-size:1.3rem;"><?= $total_tables_count ?></h4>
                        <span class="text-info small fw-medium" style="font-size:0.75rem;"><i class="bi bi-table me-1"></i>InnoDB Engine</span>
                    </div>
                    <div class="p-3 bg-info bg-opacity-10 text-info rounded-4 fs-4">
                        <i class="bi bi-grid-3x3-gap-fill"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Records -->
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white border-start border-4 border-success h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small fw-semibold text-uppercase" style="letter-spacing: 0.5px;">Total Records</span>
                        <h4 class="fw-bold text-dark mb-0 mt-1" style="font-size:1.3rem;"><?= number_format($total_rows) ?></h4>
                        <span class="text-success small fw-medium" style="font-size:0.75rem;"><i class="bi bi-layers-fill me-1"></i>Active Rows</span>
                    </div>
                    <div class="p-3 bg-success bg-opacity-10 text-success rounded-4 fs-4">
                        <i class="bi bi-hdd-stack-fill"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Database Storage Size -->
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white border-start border-4 border-warning h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small fw-semibold text-uppercase" style="letter-spacing: 0.5px;">Storage Size</span>
                        <h4 class="fw-bold text-dark mb-0 mt-1" style="font-size:1.3rem;"><?= $total_size_formatted ?></h4>
                        <span class="text-warning small fw-medium" style="font-size:0.75rem;"><i class="bi bi-pie-chart-fill me-1"></i>Data & Index</span>
                    </div>
                    <div class="p-3 bg-warning bg-opacity-10 text-warning rounded-4 fs-4">
                        <i class="bi bi-pie-chart-fill"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 2 MAIN CARDS: BACKUP & RESTORE -->
    <div class="row g-4 mb-4">
        
        <!-- CARD 1: BACKUP DATABASE -->
        <div class="col-12 col-lg-6">
            <div class="card db-card-action shadow-sm bg-white h-100 p-4">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="p-3 rounded-4 fs-3" style="background: rgba(37, 99, 235, 0.1); color: #2563EB;">
                        <i class="bi bi-cloud-arrow-down-fill"></i>
                    </div>
                    <div>
                        <h3 class="h5 fw-bold text-dark mb-1">Database Backup</h3>
                        <span class="text-muted small">Export full SQL database dump</span>
                    </div>
                </div>

                <p class="text-muted small mb-4" style="line-height: 1.6;">
                    Create and download a full SQL archive snapshot containing all table structures, foreign key constraints, ordinances, user directories, evaluation metrics, and system audit logs.
                </p>

                <div class="p-3 rounded-3 mb-4" style="background: #F8FAFC; border: 1px solid #E2E8F0;">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="small fw-semibold text-dark"><i class="bi bi-file-earmark-code text-primary me-1"></i> Output Format:</span>
                        <span class="badge bg-primary bg-opacity-10 text-primary">Standard SQL (.sql)</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="small fw-semibold text-dark"><i class="bi bi-shield-check text-success me-1"></i> Compatibility:</span>
                        <span class="small text-muted">MySQL 5.7+ / 8.0+ / MariaDB</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="small fw-semibold text-dark"><i class="bi bi-clock-history text-secondary me-1"></i> Scope:</span>
                        <span class="small text-muted">All <?= $total_tables_count ?> Tables + Records</span>
                    </div>
                </div>

                <div class="mt-auto">
                    <a href="../backend/database_backup_handler.php?action=export_backup" class="btn btn-primary w-100 py-2.5 rounded-3 fw-bold shadow-sm d-flex align-items-center justify-content-center gap-2" style="background: #0B2E59; border-color: #0B2E59;">
                        <i class="bi bi-cloud-download-fill fs-5"></i> Generate & Download SQL Backup
                    </a>
                    <div class="text-center mt-2">
                        <small class="text-muted" style="font-size: 0.75rem;">
                            <i class="bi bi-lock-fill text-muted me-1"></i> Automatically logs the backup event to Audit Logs.
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <!-- CARD 2: RESTORE DATABASE -->
        <div class="col-12 col-lg-6">
            <div class="card db-card-action shadow-sm bg-white h-100 p-4">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="p-3 rounded-4 fs-3" style="background: rgba(245, 158, 11, 0.12); color: #D97706;">
                        <i class="bi bi-cloud-arrow-up-fill"></i>
                    </div>
                    <div>
                        <h3 class="h5 fw-bold text-dark mb-1">Database Restore</h3>
                        <span class="text-muted small">Import and restore SQL snapshot</span>
                    </div>
                </div>

                <p class="text-muted small mb-3" style="line-height: 1.6;">
                    Upload a valid <code>.sql</code> backup archive to restore database schema definitions and data records. Existing tables will be safely updated.
                </p>

                <!-- File Dropzone Form -->
                <form id="dbRestoreForm" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="restore_backup">
                    
                    <div class="db-dropzone mb-3" id="dbDropzone" onclick="document.getElementById('dbSqlFileInput').click();">
                        <i class="bi bi-file-earmark-arrow-up fs-1 text-muted d-block mb-1" id="dropzoneIcon"></i>
                        <span class="fw-semibold text-dark d-block mb-1" id="dropzoneLabel">Click or Drag & Drop .sql file here</span>
                        <span class="text-muted small d-block" id="dropzoneSubtext">Max file size: 50MB</span>
                        <input type="file" id="dbSqlFileInput" name="backup_file" accept=".sql" class="d-none" onchange="handleSqlFileSelect(this)">
                    </div>

                    <!-- Safety Confirmation Checkbox -->
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="confirmRestoreCheck" required>
                        <label class="form-check-label small text-muted" for="confirmRestoreCheck">
                            I confirm I want to execute this SQL restore script on the <strong><?= htmlspecialchars($db_name) ?></strong> database.
                        </label>
                    </div>

                    <div class="mt-auto">
                        <button type="submit" id="btnSubmitRestore" class="btn btn-warning w-100 py-2.5 rounded-3 fw-bold shadow-sm d-flex align-items-center justify-content-center gap-2" disabled>
                            <i class="bi bi-arrow-counterclockwise fs-5"></i> Restore Database from Snapshot
                        </button>
                        <div class="text-center mt-2">
                            <small class="text-danger" style="font-size: 0.75rem;">
                                <i class="bi bi-exclamation-triangle-fill me-1"></i> Overwrites active schema with the contents of the uploaded SQL.
                            </small>
                        </div>
                    </div>
                </form>
            </div>
        </div>

    </div>

    <!-- TABLE DETAILS & MAINTENANCE BREAKDOWN -->
    <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
            <div>
                <h3 class="h5 fw-bold text-dark mb-1 d-flex align-items-center gap-2">
                    <i class="bi bi-heart-pulse-fill text-danger"></i> Table Health & Storage Metrics
                </h3>
                <p class="text-muted mb-0 small">Overview of database tables, active record counts, physical storage usage, and optimization actions.</p>
            </div>
            <span class="badge bg-light text-dark border px-3 py-2 fw-semibold">
                <i class="bi bi-cpu me-1 text-primary"></i> <?= $total_tables_count ?> Active Tables
            </span>
        </div>

        <div class="table-responsive rounded-3 border">
            <table class="table table-hover align-middle mb-0" style="font-size: 0.88rem;">
                <thead class="table-light">
                    <tr>
                        <th class="py-3 px-3 text-uppercase text-muted fw-bold" style="font-size: 0.75rem;">Table Name</th>
                        <th class="py-3 px-3 text-uppercase text-muted fw-bold" style="font-size: 0.75rem;">Module / Purpose</th>
                        <th class="py-3 px-3 text-uppercase text-muted fw-bold text-center" style="font-size: 0.75rem;">Records</th>
                        <th class="py-3 px-3 text-uppercase text-muted fw-bold text-center" style="font-size: 0.75rem;">Size</th>
                        <th class="py-3 px-3 text-uppercase text-muted fw-bold text-center" style="font-size: 0.75rem;">Engine</th>
                        <th class="py-3 px-3 text-uppercase text-muted fw-bold text-center" style="font-size: 0.75rem;">Health</th>
                        <th class="py-3 px-3 text-uppercase text-muted fw-bold text-end" style="font-size: 0.75rem;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($table_stats)): ?>
                        <?php foreach ($table_stats as $tbl): ?>
                            <tr>
                                <td class="px-3 py-3">
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="bi bi-table text-primary"></i>
                                        <span class="fw-bold text-dark font-monospace"><?= htmlspecialchars($tbl['name']) ?></span>
                                    </div>
                                </td>
                                <td class="px-3 py-3 text-muted">
                                    <?= htmlspecialchars($table_descriptions[$tbl['name']] ?? 'System application table') ?>
                                </td>
                                <td class="px-3 py-3 text-center">
                                    <span class="badge bg-secondary bg-opacity-10 text-dark fw-bold px-2.5 py-1.5 rounded-pill">
                                        <?= number_format($tbl['rows']) ?>
                                    </span>
                                </td>
                                <td class="px-3 py-3 text-center text-muted fw-semibold">
                                    <?= $tbl['size_formatted'] ?>
                                </td>
                                <td class="px-3 py-3 text-center">
                                    <span class="small text-muted font-monospace"><?= htmlspecialchars($tbl['engine']) ?></span>
                                </td>
                                <td class="px-3 py-3 text-center">
                                    <span class="badge bg-success bg-opacity-10 text-success table-health-badge">
                                        <i class="bi bi-shield-check me-1"></i> Healthy
                                    </span>
                                </td>
                                <td class="px-3 py-3 text-end">
                                    <button type="button" class="btn btn-sm btn-outline-primary rounded-3 px-2.5 py-1" onclick="optimizeSingleTable('<?= htmlspecialchars($tbl['name']) ?>', this)" title="Defragment & Optimize Table">
                                        <i class="bi bi-wrench-adjustable me-1"></i> Optimize
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">No database tables detected.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</section>

<!-- Frontend Script for Database Management AJAX Actions -->
<script>
function handleSqlFileSelect(input) {
    const file = input.files[0];
    const dropzoneLabel = document.getElementById('dropzoneLabel');
    const dropzoneSubtext = document.getElementById('dropzoneSubtext');
    const dropzoneIcon = document.getElementById('dropzoneIcon');
    const btnSubmit = document.getElementById('btnSubmitRestore');
    const confirmCheck = document.getElementById('confirmRestoreCheck');

    if (file) {
        if (!file.name.toLowerCase().endsWith('.sql')) {
            alert('Please select a valid .sql backup file.');
            input.value = '';
            btnSubmit.disabled = true;
            return;
        }
        dropzoneLabel.textContent = file.name;
        dropzoneSubtext.textContent = (file.size / (1024 * 1024)).toFixed(2) + ' MB — Ready for restore';
        dropzoneIcon.className = 'bi bi-file-earmark-check fs-1 text-success d-block mb-1';
        btnSubmit.disabled = !confirmCheck.checked;
    } else {
        dropzoneLabel.textContent = 'Click or Drag & Drop .sql file here';
        dropzoneSubtext.textContent = 'Max file size: 50MB';
        dropzoneIcon.className = 'bi bi-file-earmark-arrow-up fs-1 text-muted d-block mb-1';
        btnSubmit.disabled = true;
    }
}

document.getElementById('confirmRestoreCheck')?.addEventListener('change', function() {
    const fileInput = document.getElementById('dbSqlFileInput');
    const btnSubmit = document.getElementById('btnSubmitRestore');
    btnSubmit.disabled = !(this.checked && fileInput && fileInput.files.length > 0);
});

// Drag and drop handlers
const dropzone = document.getElementById('dbDropzone');
if (dropzone) {
    ['dragenter', 'dragover'].forEach(eventName => {
        dropzone.addEventListener(eventName, (e) => {
            e.preventDefault();
            e.stopPropagation();
            dropzone.classList.add('dragover');
        }, false);
    });

    ['dragleave', 'drop'].forEach(eventName => {
        dropzone.addEventListener(eventName, (e) => {
            e.preventDefault();
            e.stopPropagation();
            dropzone.classList.remove('dragover');
        }, false);
    });

    dropzone.addEventListener('drop', (e) => {
        const dt = e.dataTransfer;
        const files = dt.files;
        if (files.length) {
            const fileInput = document.getElementById('dbSqlFileInput');
            fileInput.files = files;
            handleSqlFileSelect(fileInput);
        }
    }, false);
}

// Restore form AJAX submission
document.getElementById('dbRestoreForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    const btnSubmit = document.getElementById('btnSubmitRestore');
    const fileInput = document.getElementById('dbSqlFileInput');

    if (!fileInput.files.length) {
        alert('Please choose an SQL backup file first.');
        return;
    }

    if (!confirm('CAUTION: Restoring will overwrite existing tables in the database. Are you sure you want to proceed?')) {
        return;
    }

    const formData = new FormData(this);
    const originalText = btnSubmit.innerHTML;
    btnSubmit.disabled = true;
    btnSubmit.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status"></span> Restoring Database...';

    fetch('../backend/database_backup_handler.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        btnSubmit.disabled = false;
        btnSubmit.innerHTML = originalText;

        const alertContainer = document.getElementById('dbAlertContainer');
        if (data.success) {
            alertContainer.innerHTML = `
                <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm mb-4" role="alert">
                    <i class="bi bi-check-circle-fill me-2 fs-5"></i> ${data.message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            `;
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } else {
            alertContainer.innerHTML = `
                <div class="alert alert-danger alert-dismissible fade show rounded-3 shadow-sm mb-4" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i> <strong>Restore Failed:</strong> ${data.error || 'Unknown error occurred.'}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            `;
        }
    })
    .catch(err => {
        btnSubmit.disabled = false;
        btnSubmit.innerHTML = originalText;
        alert('Error restoring database: ' + err.message);
    });
});

// Single table optimization
function optimizeSingleTable(tableName, btn) {
    const origHtml = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status"></span>';

    const formData = new FormData();
    formData.append('action', 'optimize_table');
    formData.append('table_name', tableName);

    fetch('../backend/database_backup_handler.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        btn.disabled = false;
        btn.innerHTML = origHtml;
        const alertContainer = document.getElementById('dbAlertContainer');
        if (data.success) {
            alertContainer.innerHTML = `
                <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm mb-4" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i> ${data.message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            `;
        } else {
            alertContainer.innerHTML = `
                <div class="alert alert-danger alert-dismissible fade show rounded-3 shadow-sm mb-4" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i> ${data.error}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            `;
        }
    })
    .catch(err => {
        btn.disabled = false;
        btn.innerHTML = origHtml;
        alert('Optimization error: ' + err.message);
    });
}

// All tables optimization
function optimizeAllTables(btn) {
    const origHtml = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status"></span> Optimizing...';

    const formData = new FormData();
    formData.append('action', 'optimize_all_tables');

    fetch('../backend/database_backup_handler.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        btn.disabled = false;
        btn.innerHTML = origHtml;
        const alertContainer = document.getElementById('dbAlertContainer');
        if (data.success) {
            alertContainer.innerHTML = `
                <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm mb-4" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i> ${data.message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            `;
        } else {
            alertContainer.innerHTML = `
                <div class="alert alert-danger alert-dismissible fade show rounded-3 shadow-sm mb-4" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i> ${data.error}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            `;
        }
    })
    .catch(err => {
        btn.disabled = false;
        btn.innerHTML = origHtml;
        alert('Optimization error: ' + err.message);
    });
}
</script>
