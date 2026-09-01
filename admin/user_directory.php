<?php
// Pull users from user_directory table in database
$directory_users = [];
if (!empty($conn)) {
    $u_tbl = 'user_directory';
    $chk_u = @mysqli_query($conn, "SHOW TABLES LIKE 'user_directory'");
    if (!$chk_u || mysqli_num_rows($chk_u) === 0) {
        $u_tbl = 'users';
    }
    $uq = mysqli_query($conn, "SELECT * FROM $u_tbl ORDER BY created_at DESC");
    if ($uq) {
        while ($row = mysqli_fetch_assoc($uq)) {
            $directory_users[] = $row;
        }
    }
}

// Rich pre-seeded municipal users for Manila City Hall Legislative Information System
if (empty($directory_users)) {
    $directory_users = [
        [
            'id' => 1,
            'name' => 'Hon. Juan Cruz',
            'username' => 'juan_c',
            'role' => 'Councilor',
            'department' => '1st District Office',
            'position' => 'City Councilor',
            'email' => 'juan@manila.gov.ph',
            'status' => 'Active',
            'created_at' => '2026-05-11 10:30:00'
        ],
        [
            'id' => 2,
            'name' => 'Ana Reyes',
            'username' => 'ana_r',
            'role' => 'Policy Analyst',
            'department' => 'Legislative Research Division',
            'position' => 'Senior Policy Analyst',
            'email' => 'ana@manila.gov.ph',
            'status' => 'Archived',
            'created_at' => '2026-05-12 14:45:00'
        ],
        [
            'id' => 3,
            'name' => 'Pedro Dela Cruz',
            'username' => 'pedro_d',
            'role' => 'Legislative Staff',
            'department' => 'Secretariat & Legal Affairs',
            'position' => 'Committee Officer',
            'email' => 'pedro@manila.gov.ph',
            'status' => 'Active',
            'created_at' => '2026-05-13 11:20:00'
        ],
        [
            'id' => 4,
            'name' => 'Liza Garcia',
            'username' => 'liza_g',
            'role' => 'Policy Analyst',
            'department' => 'Impact Evaluation Bureau',
            'position' => 'Socio-Economic Assessor',
            'email' => 'liza@manila.gov.ph',
            'status' => 'Active',
            'created_at' => '2026-05-13 15:10:00'
        ],
        [
            'id' => 5,
            'name' => 'Hon. Roberto Ramos',
            'username' => 'roberto_r',
            'role' => 'Councilor',
            'department' => '3rd District Office',
            'position' => 'City Councilor',
            'email' => 'roberto@manila.gov.ph',
            'status' => 'Active',
            'created_at' => '2026-05-14 08:30:00'
        ]
    ];
}

// Compute live count statistics for summary cards
$total_count = count($directory_users);
$active_count = 0;
$archived_count = 0;

foreach ($directory_users as $du) {
    $st = $du['status'] ?? 'Active';
    if ($st === 'Archived') {
        $archived_count++;
    } else {
        $active_count++;
    }
}
?>

<style>
    .stat-card-clickable {
        cursor: pointer;
        transition: transform 0.18s ease, box-shadow 0.18s ease;
    }

    .stat-card-clickable:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 20px rgba(11, 27, 61, 0.1) !important;
    }

    /* Premium Soft Action Buttons */
    .btn-action-view {
        background: rgba(37, 99, 235, 0.1) !important;
        color: #1E40AF !important;
        border: 1px solid rgba(37, 99, 235, 0.3) !important;
        border-radius: 8px !important;
        padding: 6px 11px !important;
        font-size: 0.9rem !important;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1) !important;
    }

    .btn-action-view:hover {
        background: #1D4ED8 !important;
        color: #FFFFFF !important;
        transform: translateY(-1px);
        box-shadow: 0 4px 10px rgba(37, 99, 235, 0.3);
    }

    .btn-action-edit {
        background: rgba(217, 119, 6, 0.1) !important;
        color: #92400E !important;
        border: 1px solid rgba(217, 119, 6, 0.3) !important;
        border-radius: 8px !important;
        padding: 6px 11px !important;
        font-size: 0.9rem !important;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1) !important;
    }

    .btn-action-edit:hover {
        background: #D97706 !important;
        color: #FFFFFF !important;
        transform: translateY(-1px);
        box-shadow: 0 4px 10px rgba(217, 119, 6, 0.3);
    }

    .btn-action-archive {
        background: rgba(225, 29, 72, 0.1) !important;
        color: #9F1239 !important;
        border: 1px solid rgba(225, 29, 72, 0.3) !important;
        border-radius: 8px !important;
        padding: 6px 11px !important;
        font-size: 0.9rem !important;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1) !important;
    }

    .btn-action-archive:hover {
        background: #E11D48 !important;
        color: #FFFFFF !important;
        transform: translateY(-1px);
        box-shadow: 0 4px 10px rgba(225, 29, 72, 0.3);
    }

    .btn-action-restore {
        background: rgba(16, 185, 129, 0.1) !important;
        color: #065F46 !important;
        border: 1px solid rgba(16, 185, 129, 0.3) !important;
        border-radius: 8px !important;
        padding: 6px 11px !important;
        font-size: 0.9rem !important;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1) !important;
    }

    .btn-action-restore:hover {
        background: #059669 !important;
        color: #FFFFFF !important;
        transform: translateY(-1px);
        box-shadow: 0 4px 10px rgba(16, 185, 129, 0.3);
    }

    .btn-action-delete {
        background: rgba(220, 38, 38, 0.1) !important;
        color: #991B1B !important;
        border: 1px solid rgba(220, 38, 38, 0.3) !important;
        border-radius: 8px !important;
        padding: 6px 11px !important;
        font-size: 0.9rem !important;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1) !important;
    }

    .btn-action-delete:hover {
        background: #DC2626 !important;
        color: #FFFFFF !important;
        transform: translateY(-1px);
        box-shadow: 0 4px 10px rgba(220, 38, 38, 0.3);
    }

    /* Crisp Bold Fonts & Larger Typography */
    .user-table-head th {
        background: #F8FAFC !important;
        color: #0F172A !important;
        font-size: 0.84rem !important;
        font-weight: 700 !important;
        text-transform: uppercase !important;
        letter-spacing: 0.5px !important;
        padding-top: 12px !important;
        padding-bottom: 12px !important;
    }

    .user-row-num {
        color: #0F172A !important;
        font-size: 0.94rem !important;
        font-weight: 700 !important;
    }

    .user-row-name {
        color: #0F172A !important;
        font-size: 0.98rem !important;
        font-weight: 700 !important;
    }

    .user-row-username {
        color: #334155 !important;
        font-size: 0.84rem !important;
        font-weight: 600 !important;
    }

    .user-row-dept {
        color: #0F172A !important;
        font-size: 0.92rem !important;
        font-weight: 600 !important;
    }

    .user-row-email {
        color: #0F172A !important;
        font-size: 0.92rem !important;
        font-weight: 600 !important;
    }

    .user-row-date {
        color: #0F172A !important;
        font-size: 0.88rem !important;
        font-weight: 600 !important;
    }

    .badge-role-councilor {
        background: #FEF3C7 !important;
        color: #78350F !important;
        border: 1px solid #FDE68A !important;
        font-size: 0.82rem !important;
        font-weight: 600 !important;
        padding: 5px 11px !important;
    }

    .badge-role-analyst {
        background: #E0E7FF !important;
        color: #3730A3 !important;
        border: 1px solid #C7D2FE !important;
        font-size: 0.82rem !important;
        font-weight: 600 !important;
        padding: 5px 11px !important;
    }

    .badge-role-admin {
        background: #F3E8FF !important;
        color: #6B21A8 !important;
        border: 1px solid #E9D5FF !important;
        font-size: 0.82rem !important;
        font-weight: 600 !important;
        padding: 5px 11px !important;
    }

    .badge-role-staff {
        background: #CCFBF1 !important;
        color: #115E59 !important;
        border: 1px solid #99F6E4 !important;
        font-size: 0.82rem !important;
        font-weight: 600 !important;
        padding: 5px 11px !important;
    }

    .badge-status-active {
        background: #DCFCE7 !important;
        color: #166534 !important;
        border: 1px solid #86EFAC !important;
        font-size: 0.82rem !important;
        font-weight: 600 !important;
        padding: 5px 12px !important;
    }

    .badge-status-archived {
        background: #FEF3C7 !important;
        color: #92400E !important;
        border: 1px solid #FDE68A !important;
        font-size: 0.82rem !important;
        font-weight: 600 !important;
        padding: 5px 12px !important;
    }
</style>

<section id="activeUsersSection" class="content-section <?= ($active_section ?? 'adminDashboardSection') !== 'activeUsersSection' ? 'd-none' : '' ?>">

    <!-- Module Subtitle & Description -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <h2 class="h3 fw-bold text-dark mb-1"><i class="bi bi-person-gear text-primary me-2"></i>Account Management
                System</h2>
            <p class="text-muted mb-0" style="font-size: 0.92rem;">Provision, configure, archive, and restore official
                accounts for Manila City Hall councilors, researchers, and staff.</p>
        </div>
        <button class="btn btn-primary py-2.5 px-4 rounded-3 fw-bold shadow-sm d-inline-flex align-items-center gap-2"
            data-bs-toggle="modal" data-bs-target="#provisionUserModal"
            style="background: #0B1B3D; border-color: #0B1B3D;">
            <i class="bi bi-person-plus-fill text-warning fs-5"></i> Provision New Account
        </button>
    </div>

    <!-- Summary Statistics Bar (Interactive Clickable Metric Cards) -->
    <div class="row g-3 mb-4">
        <!-- Total Provisioned Accounts -->
        <div class="col-12 col-md-4">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white border-start border-4 border-primary stat-card-clickable"
                onclick="filterByStatus('ALL')" title="Click to view all accounts">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small fw-semibold uppercase" style="letter-spacing: 0.5px;">Total
                            Accounts</span>
                        <h3 class="fw-bold text-dark mb-0 mt-1" id="statTotalUsers"><?= $total_count ?></h3>
                        <span class="text-primary small fw-medium" style="font-size:0.75rem;"><i
                                class="bi bi-cursor-fill me-1"></i>View All</span>
                    </div>
                    <div class="p-3 bg-primary bg-opacity-10 text-primary rounded-4 fs-4">
                        <i class="bi bi-people-fill"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Active Staff & Councilors -->
        <div class="col-12 col-md-4">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white border-start border-4 border-success stat-card-clickable"
                onclick="filterByStatus('Active')" title="Click to view active accounts">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small fw-semibold uppercase" style="letter-spacing: 0.5px;">Active
                            Accounts</span>
                        <h3 class="fw-bold text-dark mb-0 mt-1" id="statActiveUsers"><?= $active_count ?></h3>
                        <span class="text-success small fw-medium" style="font-size:0.75rem;"><i
                                class="bi bi-check-circle-fill me-1"></i>View Active</span>
                    </div>
                    <div class="p-3 bg-success bg-opacity-10 text-success rounded-4 fs-4">
                        <i class="bi bi-check-circle-fill"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Archived Accounts (Clickable to view archived accounts) -->
        <div class="col-12 col-md-4">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white border-start border-4 border-warning stat-card-clickable"
                onclick="filterByStatus('Archived')" title="Click to view archived accounts">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small fw-semibold uppercase" style="letter-spacing: 0.5px;">Archived
                            Accounts</span>
                        <h3 class="fw-bold text-dark mb-0 mt-1" id="statArchivedUsers"><?= $archived_count ?></h3>
                        <span class="text-warning small fw-medium" style="font-size:0.75rem;"><i
                                class="bi bi-archive-fill me-1"></i>Click to view archived</span>
                    </div>
                    <div class="p-3 bg-warning bg-opacity-10 text-warning rounded-4 fs-4">
                        <i class="bi bi-archive-fill"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main User Table Container Card -->
    <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">

        <!-- Filter & Search Controls Header -->
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
            <!-- Search Input -->
            <div class="position-relative flex-grow-1" style="max-width: 340px;">
                <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
                <input type="search" id="userDirectorySearch"
                    class="form-control ps-5 py-2 rounded-3 border-light-subtle"
                    placeholder="Search name, username, email, department..." onkeyup="filterUserDirectory()">
            </div>

            <!-- Filters & Actions Group -->
            <div class="d-flex flex-wrap align-items-center gap-2">
                <!-- Role Filter Dropdown -->
                <select id="userDirectoryRoleFilter" class="form-select py-2 px-3 rounded-3 border-light-subtle"
                    style="width: 150px;" onchange="filterUserDirectory()">
                    <option value="ALL">All Roles</option>
                    <option value="Staff">Staff</option>
                    <option value="Councilor">Councilor</option>
                </select>

                <!-- Status Filter Dropdown -->
                <select id="userDirectoryStatusFilter" class="form-select py-2 px-3 rounded-3 border-light-subtle"
                    style="width: 150px;" onchange="filterUserDirectory()">
                    <option value="ALL">All Statuses</option>
                    <option value="Active">Active</option>
                    <option value="Archived">Archived</option>
                </select>

                <!-- Clear Filters Button -->
                <button class="btn btn-outline-secondary py-2 px-3 rounded-3" onclick="resetDirectoryFilters()"
                    title="Reset Filters">
                    <i class="bi bi-arrow-counterclockwise me-1"></i> Reset
                </button>
            </div>
        </div>

        <!-- User Table -->
        <div class="table-responsive border rounded-4 overflow-hidden mb-3">
            <table class="table table-hover align-middle mb-0" style="font-size: 0.88rem;">
                <thead class="user-table-head">
                    <tr>
                        <th class="py-3 px-3 text-center" style="width: 45px;">#</th>
                        <th class="py-3">Name &amp; Username</th>
                        <th class="py-3">Role</th>
                        <th class="py-3">Department / Office</th>
                        <th class="py-3">Email Address</th>
                        <th class="py-3 text-center">Status</th>
                        <th class="py-3">Date Provisioned</th>
                        <th class="py-3 text-center" style="width: 140px;">Actions</th>
                    </tr>
                </thead>
                <tbody id="userDirectoryTableBody">
                    <?php foreach ($directory_users as $index => $u):
                        $num = $index + 1;
                        $name = htmlspecialchars($u['full_name'] ?? $u['name'] ?? 'User ' . $num);
                        $username = htmlspecialchars($u['username'] ?? 'user_' . $num);
                        $role = htmlspecialchars($u['role'] ?? 'Staff');
                        $dept = htmlspecialchars($u['department'] ?? 'Staff');
                        if ($dept === 'Secretariat' || $dept === 'Legislative Secretariat') {
                            $dept = 'Staff';
                        }
                        $email = htmlspecialchars($u['email'] ?? 'user' . $num . '@manila.gov.ph');
                        $status = htmlspecialchars($u['status'] ?? 'Active');

                        $rawDate = $u['created_at'] ?? '2026-05-10 09:15:00';
                        $formattedDate = date('M d, Y h:i A', strtotime($rawDate));

                        // Custom Role Badge Styling
                        if ($role === 'Councilor') {
                            $roleBadge = '<span class="badge badge-role-councilor rounded-pill"><i class="bi bi-award-fill me-1"></i>Councilor</span>';
                        } elseif ($role === 'Admin' || $role === 'Administrator') {
                            $roleBadge = '<span class="badge badge-role-admin rounded-pill"><i class="bi bi-shield-lock-fill me-1"></i>Admin</span>';
                        } else {
                            $roleBadge = '<span class="badge badge-role-staff rounded-pill"><i class="bi bi-person-badge-fill me-1"></i>Staff</span>';
                        }

                        // Custom Status Badge Styling
                        if ($status === 'Active') {
                            $statusBadge = '<span class="badge badge-status-active rounded-pill">Active</span>';
                        } else {
                            $statusBadge = '<span class="badge badge-status-archived rounded-pill"><i class="bi bi-archive-fill me-1"></i>Archived</span>';
                        }
                        ?>
                        <tr class="user-row" data-username="<?= strtolower($username) ?>" data-role="<?= $role ?>"
                            data-status="<?= $status ?>"
                            data-search="<?= strtolower($name . ' ' . $username . ' ' . $email . ' ' . $dept . ' ' . $role) ?>">
                            <td class="text-center user-row-num px-3"><?= $num ?></td>
                            <td>
                                <div class="user-row-name"><?= $name ?></div>
                                <div class="user-row-username"><i class="bi bi-at me-0.5"></i><?= $username ?></div>
                            </td>
                            <td class="user-row-role-td"><?= $roleBadge ?></td>
                            <td class="user-row-dept"><?= $dept ?></td>
                            <td class="user-row-email"><?= $email ?></td>
                            <td class="text-center user-row-status-td"><?= $statusBadge ?></td>
                            <td class="user-row-date"><?= $formattedDate ?></td>
                            <td class="text-center">
                                <div class="d-flex align-items-center justify-content-center gap-1.5 action-buttons-cell">
                                    <!-- View Info Button -->
                                    <button class="btn btn-sm btn-action-view" title="View Full Profile"
                                        onclick="viewUserModal('<?= addslashes($name) ?>', '<?= addslashes($username) ?>', '<?= addslashes($email) ?>', '<?= addslashes($role) ?>', '<?= addslashes($dept) ?>', '<?= addslashes($status) ?>', '<?= addslashes($formattedDate) ?>')">
                                        <i class="bi bi-eye-fill"></i>
                                    </button>

                                    <?php if ($status === 'Active'): ?>
                                        <!-- Edit User Button -->
                                        <button class="btn btn-sm btn-action-edit" title="Edit Account Details"
                                            onclick="openEditUserModal('<?= addslashes($name) ?>', '<?= addslashes($username) ?>', '<?= addslashes($email) ?>', '<?= addslashes($role) ?>', '<?= addslashes($dept) ?>', '<?= addslashes($status) ?>')">
                                            <i class="bi bi-pencil-square"></i>
                                        </button>
                                        <!-- Archive Account Button -->
                                        <button class="btn btn-sm btn-action-archive btn-archive-action" title="Archive Account"
                                            onclick="archiveUserRow(this, '<?= addslashes($username) ?>', '<?= addslashes($name) ?>')">
                                            <i class="bi bi-archive-fill"></i>
                                        </button>
                                    <?php else: ?>
                                        <!-- Restore Account Button -->
                                        <button class="btn btn-sm btn-action-restore btn-restore-action"
                                            title="Restore Account Access"
                                            onclick="restoreUserRow(this, '<?= addslashes($username) ?>', '<?= addslashes($name) ?>')">
                                            <i class="bi bi-arrow-counterclockwise fs-6"></i>
                                        </button>
                                        <!-- Delete Account Button -->
                                        <button class="btn btn-sm btn-action-delete btn-delete-action ms-1"
                                            title="Permanently Delete Account"
                                            onclick="confirmDeleteUserRow(this, '<?= addslashes($username) ?>', '<?= addslashes($name) ?>')">
                                            <i class="bi bi-trash-fill"></i>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination Footer -->
        <div class="d-flex flex-wrap align-items-center justify-content-between pt-2 gap-2">
            <div class="text-muted small" id="userDirectoryCount">
                Showing 1 to <?= count($directory_users) ?> of <?= count($directory_users) ?> municipal accounts
            </div>
            <div class="d-flex align-items-center gap-1">
                <button class="btn btn-sm btn-light border rounded-2 px-2" disabled><i
                        class="bi bi-chevron-left"></i></button>
                <button class="btn btn-sm btn-primary rounded-2 px-3 fw-bold"
                    style="background: #0B1B3D; border-color: #0B1B3D;">1</button>
                <button class="btn btn-sm btn-light border rounded-2 px-2" disabled><i
                        class="bi bi-chevron-right"></i></button>
            </div>
        </div>

    </div>
</section>

<!-- 1. VIEW USER PROFILE MODAL -->
<div class="modal fade" id="viewUserDetailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow-lg overflow-hidden">
            <div class="modal-header border-0 px-4 pt-4 pb-2"
                style="background: linear-gradient(135deg, #0B1B3D 0%, #102B66 100%); color: #FFFFFF;">
                <h5 class="modal-title fw-bold d-flex align-items-center gap-2">
                    <i class="bi bi-shield-person text-warning fs-4"></i> Municipal Account Profile
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="text-center mb-4">
                    <div class="avatar-circle mx-auto mb-2 bg-primary bg-opacity-10 text-primary fw-bold fs-3 d-flex align-items-center justify-content-center rounded-circle"
                        style="width: 76px; height: 76px; border: 3px solid #0B1B3D;">
                        <span id="modalUserInitials">MS</span>
                    </div>
                    <h5 class="fw-bold text-dark mb-0 fs-4" id="modalUserName">Maria Santos</h5>
                    <div class="mt-2" id="modalUserRoleContainer">
                        <span class="badge px-3 py-1 rounded-pill" id="modalUserRoleBadge">Councilor</span>
                    </div>
                </div>

                <div class="bg-light p-3.5 rounded-3 border">
                    <div class="row g-3">
                        <div class="col-6">
                            <label class="text-muted small fw-semibold d-block uppercase"
                                style="font-size:0.75rem;">Username</label>
                            <span class="fw-bold text-dark" id="modalUserUsername">maria_s</span>
                        </div>
                        <div class="col-6">
                            <label class="text-muted small fw-semibold d-block uppercase"
                                style="font-size:0.75rem;">Account Status</label>
                            <span id="modalUserStatus">Active</span>
                        </div>
                        <div class="col-12">
                            <label class="text-muted small fw-semibold d-block uppercase"
                                style="font-size:0.75rem;">Department / Office</label>
                            <span class="fw-bold text-dark" id="modalUserDepartment">City Council Secretariat</span>
                        </div>
                        <div class="col-12">
                            <label class="text-muted small fw-semibold d-block uppercase"
                                style="font-size:0.75rem;">Official Email</label>
                            <span class="fw-bold text-dark" id="modalUserEmail">maria@manila.gov.ph</span>
                        </div>
                        <div class="col-12">
                            <label class="text-muted small fw-semibold d-block uppercase"
                                style="font-size:0.75rem;">Provision Timestamp</label>
                            <span class="text-secondary small" id="modalUserDate">May 10, 2026 09:15 AM</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light border-0 px-4 py-3">
                <button type="button" class="btn btn-secondary rounded-3 px-4 fw-semibold"
                    data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- 2. EDIT USER ACCOUNT MODAL -->
<div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header border-0 px-4 pt-4 pb-3" style="background: #0B1B3D; color: #FFFFFF;">
                <div>
                    <h5 class="modal-title fw-bold" id="editUserModalLabel"><i
                            class="bi bi-pencil-square text-warning me-2"></i>Edit Account Configuration</h5>
                    <p class="small text-white-50 mb-0">Modify official account role, department, or status.</p>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <form id="editUserForm" onsubmit="return handleEditUserSubmit(event)">
                <input type="hidden" id="editUserOriginalUsername">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-dark">Name</label>
                        <input type="text" id="editFullName" class="form-control rounded-3"
                            placeholder="Enter full name" required>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-semibold text-dark">Username</label>
                            <input type="text" id="editUsername" class="form-control rounded-3"
                                placeholder="Enter username" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-semibold text-dark">Assigned Role</label>
                            <select id="editRole" class="form-select rounded-3">
                                <option value="Staff">Staff</option>
                                <option value="Councilor">Councilor</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-dark">Department / Office</label>
                        <input type="text" id="editDepartment" class="form-control rounded-3"
                            placeholder="Enter department" required>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-semibold text-dark">Official Email</label>
                            <input type="email" id="editEmail" class="form-control rounded-3"
                                placeholder="Enter official email" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-semibold text-dark">Account Status</label>
                            <select id="editStatus" class="form-select rounded-3">
                                <option value="Active">Active</option>
                                <option value="Archived">Archived</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-dark">Reset Password <span
                                class="text-muted fw-normal">(Optional — Leave blank to keep current)</span></label>
                        <div class="input-group shadow-2xs">
                            <span class="input-group-text bg-light border-end-0 rounded-start-3"><i
                                    class="bi bi-key-fill text-warning"></i></span>
                            <input type="password" id="editPassword" class="form-control border-start-0 rounded-end-3"
                                placeholder="Enter new password to reset">
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light border-0 px-4 py-3">
                    <button type="button" class="btn btn-outline-secondary rounded-3 px-4"
                        data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-3 px-4 fw-semibold"
                        style="background: #0B1B3D; border-color: #0B1B3D;">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- 3. PROVISION NEW ACCOUNT MODAL -->
<div class="modal fade" id="provisionUserModal" tabindex="-1" aria-labelledby="provisionUserModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header border-0 px-4 pt-4 pb-3" style="background: #0B1B3D; color: #FFFFFF;">
                <div>
                    <h5 class="modal-title fw-bold" id="provisionUserModalLabel"><i
                            class="bi bi-shield-plus text-warning me-2"></i>Provision New Account</h5>
                    <p class="small text-white-50 mb-0">Create an official account for Manila City Hall councilors or
                        staff.</p>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <form id="provisionUserForm" onsubmit="return handleProvisionUserSubmit(event)">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-dark">Name</label>
                        <input type="text" id="provFullName" class="form-control rounded-3"
                            placeholder="Enter full name" required>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-semibold text-dark">Username</label>
                            <input type="text" id="provUsername" class="form-control rounded-3"
                                placeholder="Enter username" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-semibold text-dark">Assigned Role</label>
                            <select id="provRole" class="form-select rounded-3">
                                <option value="Staff">Staff</option>
                                <option value="Councilor">Councilor</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-dark">Department / Office</label>
                        <input type="text" id="provDepartment" class="form-control rounded-3"
                            placeholder="Enter department" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-dark">Official Email Address</label>
                        <input type="email" id="provEmail" class="form-control rounded-3"
                            placeholder="Enter official email" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-dark">Initial Password</label>
                        <input type="password" id="provPassword" class="form-control rounded-3"
                            placeholder="Assign secure password" required>
                    </div>
                </div>
                <div class="modal-footer bg-light border-0 px-4 py-3">
                    <button type="button" class="btn btn-outline-secondary rounded-3 px-4"
                        data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-3 px-4 fw-semibold"
                        style="background: #0B1B3D; border-color: #0B1B3D;">Provision Account</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ACCOUNT MANAGEMENT JAVASCRIPT LOGIC -->
<script>
    // Handle Provisioning New Account via AJAX (Saves to Database & LocalStorage, Stays on User Directory with Success Message)
    function handleProvisionUserSubmit(e) {
        if (e) e.preventDefault();

        var name = document.getElementById('provFullName').value.trim();
        var username = document.getElementById('provUsername').value.trim();
        var role = document.getElementById('provRole').value;
        var dept = document.getElementById('provDepartment').value.trim();
        var email = document.getElementById('provEmail').value.trim();
        var password = document.getElementById('provPassword').value.trim();

        if (!name || !username || !email || !password) {
            alert('Please fill out all required fields.');
            return false;
        }

        var formData = new FormData();
        formData.append('action', 'provision_user');
        formData.append('full_name', name);
        formData.append('username', username);
        formData.append('role', role);
        formData.append('department', dept);
        formData.append('email', email);
        formData.append('password', password);

        fetch('admin_dashboard.php', {
            method: 'POST',
            body: formData
        })
            .then(function (res) { return res.json(); })
            .then(function (data) {
                if (data && data.success) {
                    // 1. Sync with localStorage for client-side persistence
                    var users = [];
                    try {
                        users = JSON.parse(localStorage.getItem('legislative_system_users') || '[]');
                    } catch (err) { users = []; }

                    var idx = users.findIndex(u => u && u.username && u.username.toLowerCase() === username.toLowerCase());
                    if (idx !== -1) {
                        users[idx] = { name: name, username: username, role: role, department: dept, email: email, status: 'Active' };
                    } else {
                        users.push({ name: name, username: username, role: role, department: dept, email: email, status: 'Active' });
                    }
                    localStorage.setItem('legislative_system_users', JSON.stringify(users));

                    // 2. Hide modal & reset form
                    var modalEl = document.getElementById('provisionUserModal');
                    if (modalEl && typeof bootstrap !== 'undefined') {
                        var modal = bootstrap.Modal.getInstance(modalEl);
                        if (modal) modal.hide();
                    }
                    document.getElementById('provisionUserForm').reset();

                    // 3. Show success alert on User Directory page
                    showProvisionSuccessAlert(data.message || ('Account for "' + name + '" (@' + username + ') was provisioned successfully!'));

                    // 4. Immediately render row & update counters without page refresh
                    syncLocalStorageUsers();
                    filterUserDirectory();
                } else {
                    alert(data.error || 'Failed to provision account. Please check the details and try again.');
                }
            })
            .catch(function () {
                // Fallback save to localStorage if offline
                var users = [];
                try {
                    users = JSON.parse(localStorage.getItem('legislative_system_users') || '[]');
                } catch (err) { users = []; }
                users.push({ name: name, username: username, role: role, department: dept, email: email, status: 'Active' });
                localStorage.setItem('legislative_system_users', JSON.stringify(users));

                var modalEl = document.getElementById('provisionUserModal');
                if (modalEl && typeof bootstrap !== 'undefined') {
                    var modal = bootstrap.Modal.getInstance(modalEl);
                    if (modal) modal.hide();
                }
                document.getElementById('provisionUserForm').reset();
                showProvisionSuccessAlert('Account for "' + name + '" (@' + username + ') was provisioned successfully!');
                syncLocalStorageUsers();
                filterUserDirectory();
            });

        return false;
    }

    function showProvisionSuccessAlert(msg) {
        var alertContainer = document.getElementById('userDirectoryAlertContainer');
        if (!alertContainer) {
            alertContainer = document.createElement('div');
            alertContainer.id = 'userDirectoryAlertContainer';
            var sectionHeader = document.querySelector('#activeUsersSection .mb-4');
            if (sectionHeader && sectionHeader.parentNode) {
                sectionHeader.parentNode.insertBefore(alertContainer, sectionHeader.nextSibling);
            }
        }
        alertContainer.innerHTML = `
    <div class="alert alert-success alert-dismissible fade show rounded-4 border-0 shadow-sm p-3 mb-4 d-flex align-items-center gap-2" role="alert" style="background:#dcfce7; color:#15803d;">
      <i class="bi bi-check-circle-fill fs-5 me-1"></i>
      <div><strong>Success!</strong> ${msg}</div>
      <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  `;
    }

    // Filter Table Directly by Status Card Clicks
    function filterByStatus(status) {
        var statusFilter = document.getElementById('userDirectoryStatusFilter');
        if (statusFilter) {
            statusFilter.value = status;
            filterUserDirectory();
        }
    }

    // Synchronize Local Storage User Statuses with Table Rows & Counters
    function syncLocalStorageUsers() {
        var users = [];
        try {
            users = JSON.parse(localStorage.getItem('legislative_system_users') || '[]');
        } catch (err) {
            users = [];
        }

        var rows = document.querySelectorAll('#userDirectoryTableBody .user-row');
        rows.forEach(function (row) {
            var rowUsername = (row.getAttribute('data-username') || '').toLowerCase().trim();
            var foundUser = users.find(function (u) {
                if (!u || !u.username) return false;
                return u.username.toLowerCase().trim() === rowUsername;
            });

            if (foundUser) {
                if (foundUser.status === 'Archived') {
                    row.setAttribute('data-status', 'Archived');
                    var statusTd = row.cells[5];
                    if (statusTd) {
                        statusTd.innerHTML = '<span class="badge badge-status-archived rounded-pill"><i class="bi bi-archive-fill me-1"></i>Archived</span>';
                    }
                    var actionCell = row.querySelector('.action-buttons-cell');
                    if (actionCell) {
                        var uname = (foundUser.username || '').replace(/'/g, "\\'");
                        var name = (foundUser.name || '').replace(/'/g, "\\'");
                        var email = (foundUser.email || '').replace(/'/g, "\\'");
                        var role = (foundUser.role || '').replace(/'/g, "\\'");
                        var dept = (foundUser.department || '').replace(/'/g, "\\'");
                        actionCell.innerHTML = `
            <button class="btn btn-sm btn-action-view" title="View Full Profile" onclick="viewUserModal('${name}', '${uname}', '${email}', '${role}', '${dept}', 'Archived', 'Just Now')"><i class="bi bi-eye-fill"></i></button>
            <button class="btn btn-sm btn-action-restore btn-restore-action" title="Restore Account Access" onclick="restoreUserRow(this, '${uname}', '${name}')"><i class="bi bi-arrow-counterclockwise fs-6"></i></button>
            <button class="btn btn-sm btn-action-delete btn-delete-action ms-1" title="Permanently Delete Account" onclick="confirmDeleteUserRow(this, '${uname}', '${name}')"><i class="bi bi-trash-fill"></i></button>
          `;
                    }
                } else if (foundUser.status === 'Active') {
                    row.setAttribute('data-status', 'Active');
                    var statusTd = row.cells[5];
                    if (statusTd) {
                        statusTd.innerHTML = '<span class="badge badge-status-active rounded-pill">Active</span>';
                    }
                    var actionCell = row.querySelector('.action-buttons-cell');
                    if (actionCell) {
                        var uname = (foundUser.username || '').replace(/'/g, "\\'");
                        var name = (foundUser.name || '').replace(/'/g, "\\'");
                        var email = (foundUser.email || '').replace(/'/g, "\\'");
                        var role = (foundUser.role || '').replace(/'/g, "\\'");
                        var dept = (foundUser.department || '').replace(/'/g, "\\'");
                        actionCell.innerHTML = `
            <button class="btn btn-sm btn-action-view" title="View Full Profile" onclick="viewUserModal('${name}', '${uname}', '${email}', '${role}', '${dept}', 'Active', 'Just Now')"><i class="bi bi-eye-fill"></i></button>
            <button class="btn btn-sm btn-action-edit" title="Edit Account Details" onclick="openEditUserModal('${name}', '${uname}', '${email}', '${role}', '${dept}', 'Active')"><i class="bi bi-pencil-square"></i></button>
            <button class="btn btn-sm btn-action-archive btn-archive-action ms-1" title="Archive Account" onclick="archiveUserRow(this, '${uname}', '${name}')"><i class="bi bi-archive-fill"></i></button>
          `;
                    }
                }
            }
        });

        // Render newly provisioned localStorage users not present in HTML
        if (users && users.length) {
            renderNewLocalStorageUsers(users);
        }

        // Recalculate top counter cards from visible/rendered DOM rows
        updateDirectoryCounters();
    }

    // Render Newly Provisioned LocalStorage Users
    function renderNewLocalStorageUsers(users) {
        var tbody = document.getElementById('userDirectoryTableBody');
        if (!tbody || !users) return;

        users.forEach(function (u) {
            if (!u || !u.username) return;
            var existingRow = document.querySelector('#userDirectoryTableBody .user-row[data-username="' + u.username.toLowerCase() + '"]');
            if (!existingRow) {
                var rowCount = tbody.querySelectorAll('.user-row').length + 1;
                var deptName = u.department || 'Staff';
                if (deptName === 'Secretariat' || deptName === 'Legislative Secretariat') deptName = 'Staff';

                var roleBadge = '<span class="badge badge-role-staff rounded-pill"><i class="bi bi-person-badge-fill me-1"></i>Staff</span>';
                if (u.role === 'Councilor') {
                    roleBadge = '<span class="badge badge-role-councilor rounded-pill"><i class="bi bi-award-fill me-1"></i>Councilor</span>';
                } else if (u.role === 'Admin' || u.role === 'Administrator') {
                    roleBadge = '<span class="badge badge-role-admin rounded-pill"><i class="bi bi-shield-lock-fill me-1"></i>Admin</span>';
                }

                var isArchived = (u.status === 'Archived');
                var stBadge = isArchived ?
                    '<span class="badge badge-status-archived rounded-pill"><i class="bi bi-archive-fill me-1"></i>Archived</span>' :
                    '<span class="badge badge-status-active rounded-pill">Active</span>';

                var actionButtonsHtml = isArchived ?
                    `<button class="btn btn-sm btn-action-view" title="View Full Profile" onclick="viewUserModal('${u.name}', '${u.username}', '${u.email}', '${u.role}', '${deptName}', '${u.status}', 'Just Now')"><i class="bi bi-eye-fill"></i></button>
         <button class="btn btn-sm btn-action-restore btn-restore-action" title="Restore Account Access" onclick="restoreUserRow(this, '${u.username}', '${u.name}')"><i class="bi bi-arrow-counterclockwise fs-6"></i></button>
         <button class="btn btn-sm btn-action-delete btn-delete-action ms-1" title="Permanently Delete Account" onclick="confirmDeleteUserRow(this, '${u.username}', '${u.name}')"><i class="bi bi-trash-fill"></i></button>` :
                    `<button class="btn btn-sm btn-action-view" title="View Full Profile" onclick="viewUserModal('${u.name}', '${u.username}', '${u.email}', '${u.role}', '${deptName}', '${u.status}', 'Just Now')"><i class="bi bi-eye-fill"></i></button>
         <button class="btn btn-sm btn-action-edit" title="Edit Account Details" onclick="openEditUserModal('${u.name}', '${u.username}', '${u.email}', '${u.role}', '${deptName}', '${u.status}')"><i class="bi bi-pencil-square"></i></button>
         <button class="btn btn-sm btn-action-archive btn-archive-action ms-1" title="Archive Account" onclick="archiveUserRow(this, '${u.username}', '${u.name}')"><i class="bi bi-archive-fill"></i></button>`;

                var tr = document.createElement('tr');
                tr.className = 'user-row';
                tr.setAttribute('data-username', u.username.toLowerCase());
                tr.setAttribute('data-role', u.role || 'Staff');
                tr.setAttribute('data-status', u.status || 'Active');
                tr.setAttribute('data-search', (u.name + ' ' + u.username + ' ' + u.email + ' ' + deptName).toLowerCase());

                tr.innerHTML = `
        <td class="text-center user-row-num px-3">${rowCount}</td>
        <td>
          <div class="user-row-name">${u.name}</div>
          <div class="user-row-username"><i class="bi bi-at me-0.5"></i>${u.username}</div>
        </td>
        <td>${roleBadge}</td>
        <td class="user-row-dept">${deptName}</td>
        <td class="user-row-email">${u.email}</td>
        <td class="text-center">${stBadge}</td>
        <td class="user-row-date">Just Now</td>
        <td class="text-center">
          <div class="d-flex align-items-center justify-content-center gap-1.5 action-buttons-cell">
            ${actionButtonsHtml}
          </div>
        </td>
      `;
                tbody.appendChild(tr);
            }
        });
    }

    // Dynamically Recalculate Top Summary Counter Cards
    function updateDirectoryCounters() {
        var allRows = document.querySelectorAll('#userDirectoryTableBody .user-row');
        var total = allRows.length;
        var active = 0;
        var archived = 0;

        allRows.forEach(function (r) {
            var statusTd = r.cells[5];
            var statusText = statusTd ? statusTd.textContent.trim().toLowerCase() : '';
            var attrStatus = (r.getAttribute('data-status') || '').toLowerCase();

            if (attrStatus === 'archived' || statusText.indexOf('archived') !== -1) {
                archived++;
                r.setAttribute('data-status', 'Archived');
            } else {
                active++;
                r.setAttribute('data-status', 'Active');
            }
        });

        if (document.getElementById('statTotalUsers')) document.getElementById('statTotalUsers').textContent = total;
        if (document.getElementById('statActiveUsers')) document.getElementById('statActiveUsers').textContent = active;
        if (document.getElementById('statArchivedUsers')) document.getElementById('statArchivedUsers').textContent = archived;
    }

    // Filter User Directory by Search Input, Role & Status Dropdowns
    function filterUserDirectory() {
        var search = (document.getElementById('userDirectorySearch')?.value || '').toLowerCase().trim();
        var role = document.getElementById('userDirectoryRoleFilter')?.value || 'ALL';
        var status = document.getElementById('userDirectoryStatusFilter')?.value || 'ALL';
        var rows = document.querySelectorAll('#userDirectoryTableBody .user-row');

        var visibleCount = 0;
        rows.forEach(function (row) {
            var rowRole = row.getAttribute('data-role');
            var rowStatus = row.getAttribute('data-status');
            var rowSearch = row.getAttribute('data-search') || '';

            var matchesRole = (role === 'ALL' || rowRole === role);
            var matchesStatus = (status === 'ALL' || rowStatus === status);
            var matchesSearch = (rowSearch.indexOf(search) !== -1);

            if (matchesRole && matchesStatus && matchesSearch) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });

        var countEl = document.getElementById('userDirectoryCount');
        if (countEl) {
            countEl.textContent = 'Showing ' + visibleCount + ' of ' + rows.length + ' municipal accounts';
        }
    }

    // Reset Filters
    function resetDirectoryFilters() {
        if (document.getElementById('userDirectorySearch')) document.getElementById('userDirectorySearch').value = '';
        if (document.getElementById('userDirectoryRoleFilter')) document.getElementById('userDirectoryRoleFilter').value = 'ALL';
        if (document.getElementById('userDirectoryStatusFilter')) document.getElementById('userDirectoryStatusFilter').value = 'ALL';
        filterUserDirectory();
    }

    // View User Profile Modal
    function viewUserModal(name, username, email, role, dept, status, date) {
        document.getElementById('modalUserName').textContent = name;
        document.getElementById('modalUserUsername').textContent = username;
        document.getElementById('modalUserEmail').textContent = email;
        document.getElementById('modalUserDepartment').textContent = dept || 'Legislative Secretariat';
        document.getElementById('modalUserDate').textContent = date;

        var initials = name.split(' ').map(function (n) { return n[0]; }).join('').toUpperCase();
        document.getElementById('modalUserInitials').textContent = initials || 'U';

        var roleBadge = document.getElementById('modalUserRoleBadge');
        if (roleBadge) {
            roleBadge.textContent = role;
            if (role === 'Councilor') {
                roleBadge.className = 'badge px-3 py-1.5 rounded-pill';
                roleBadge.style.cssText = 'background-color: #fef3c7 !important; color: #d97706 !important; border: 1px solid #fde68a !important;';
            } else if (role === 'Policy Analyst') {
                roleBadge.className = 'badge px-3 py-1.5 rounded-pill';
                roleBadge.style.cssText = 'background-color: #e0f2fe !important; color: #0284c7 !important; border: 1px solid #bae6fd !important;';
            } else {
                roleBadge.className = 'badge px-3 py-1.5 rounded-pill';
                roleBadge.style.cssText = 'background-color: #e2e8f0 !important; color: #475569 !important; border: 1px solid #cbd5e1 !important;';
            }
        }

        var statusEl = document.getElementById('modalUserStatus');
        if (statusEl) {
            var statusClass = (status === 'Active') ? 'bg-success bg-opacity-10 text-success border border-success border-opacity-20' : 'bg-warning bg-opacity-10 text-warning border border-warning border-opacity-20';
            statusEl.innerHTML = '<span class="badge ' + statusClass + ' px-3 py-1 rounded-pill">' + status + '</span>';
        }

        var modal = new bootstrap.Modal(document.getElementById('viewUserDetailModal'));
        modal.show();
    }

    // Open Edit User Modal
    function openEditUserModal(name, username, email, role, dept, status) {
        document.getElementById('editUserOriginalUsername').value = username;
        document.getElementById('editFullName').value = name;
        document.getElementById('editUsername').value = username;
        document.getElementById('editEmail').value = email;
        document.getElementById('editDepartment').value = dept || 'Legislative Secretariat';
        document.getElementById('editRole').value = role;
        document.getElementById('editStatus').value = status;
        if (document.getElementById('editPassword')) document.getElementById('editPassword').value = '';

        var modal = new bootstrap.Modal(document.getElementById('editUserModal'));
        modal.show();
    }

    // Save Edit User Changes
    function handleEditUserSubmit(e) {
        if (e) e.preventDefault();
        var origUsername = document.getElementById('editUserOriginalUsername').value;
        var name = document.getElementById('editFullName').value.trim();
        var username = document.getElementById('editUsername').value.trim();
        var role = document.getElementById('editRole').value;
        var dept = document.getElementById('editDepartment').value.trim();
        var email = document.getElementById('editEmail').value.trim();
        var status = document.getElementById('editStatus').value;
        var password = (document.getElementById('editPassword')?.value || '').trim();

        // Send update + optional password reset to MySQL database via admin_dashboard.php
        var formData = new FormData();
        formData.append('action', 'update_user');
        formData.append('orig_username', origUsername);
        formData.append('full_name', name);
        formData.append('username', username);
        formData.append('role', role);
        formData.append('department', dept);
        formData.append('email', email);
        formData.append('status', status);
        if (password) {
            formData.append('password', password);
        }
        fetch('admin_dashboard.php', { method: 'POST', body: formData }).catch(err => console.warn(err));

        var users = [];
        try {
            users = JSON.parse(localStorage.getItem('legislative_system_users') || '[]');
        } catch (err) {
            users = [];
        }

        var index = users.findIndex(u => u && u.username && u.username.toLowerCase() === origUsername.toLowerCase());
        if (index !== -1) {
            users[index].name = name;
            users[index].username = username;
            users[index].role = role;
            users[index].department = dept;
            users[index].email = email;
            users[index].status = status;
        } else {
            users.push({
                name: name,
                username: username,
                role: role,
                department: dept,
                email: email,
                status: status
            });
        }

        localStorage.setItem('legislative_system_users', JSON.stringify(users));

        var alertMsg = 'Account configuration for "' + name + '" (' + username + ') updated successfully!';
        if (password) {
            alertMsg = 'Account configuration and password for "' + name + '" (' + username + ') reset successfully!';
        }
        alert(alertMsg);

        var modalEl = document.getElementById('editUserModal');
        if (modalEl && typeof bootstrap !== 'undefined') {
            var modal = bootstrap.Modal.getInstance(modalEl);
            if (modal) modal.hide();
        }

        syncLocalStorageUsers();
        filterUserDirectory();
        return false;
    }

    // Archive User Function
    function archiveUserRow(btn, username, userName) {
        if (confirm('Are you sure you want to archive account for "' + userName + '" (' + username + ')?')) {
            var formData = new FormData();
            formData.append('action', 'archive_user');
            formData.append('username', username);
            fetch('admin_dashboard.php', { method: 'POST', body: formData }).catch(e => console.warn(e));

            var users = [];
            try {
                users = JSON.parse(localStorage.getItem('legislative_system_users') || '[]');
            } catch (err) {
                users = [];
            }

            var idx = users.findIndex(u => u && u.username && u.username.toLowerCase() === username.toLowerCase());
            if (idx !== -1) {
                users[idx].status = 'Archived';
            } else {
                users.push({
                    name: userName,
                    username: username,
                    status: 'Archived'
                });
            }
            localStorage.setItem('legislative_system_users', JSON.stringify(users));

            var row = btn.closest('tr');
            if (row) {
                row.setAttribute('data-status', 'Archived');
                var statusTd = row.cells[5];
                if (statusTd) {
                    statusTd.innerHTML = '<span class="badge badge-status-archived rounded-pill"><i class="bi bi-archive-fill me-1"></i>Archived</span>';
                }
                var actionCell = row.querySelector('.action-buttons-cell');
                if (actionCell) {
                    var uEsc = username.replace(/'/g, "\\'");
                    var nEsc = userName.replace(/'/g, "\\'");
                    var email = (row.querySelector('.user-row-email')?.textContent || '').replace(/'/g, "\\'");
                    var role = (row.getAttribute('data-role') || 'Staff').replace(/'/g, "\\'");
                    var dept = (row.querySelector('.user-row-dept')?.textContent || '').replace(/'/g, "\\'");
                    actionCell.innerHTML = `
          <button class="btn btn-sm btn-action-view" title="View Full Profile" onclick="viewUserModal('${nEsc}', '${uEsc}', '${email}', '${role}', '${dept}', 'Archived', 'Just Now')"><i class="bi bi-eye-fill"></i></button>
          <button class="btn btn-sm btn-action-restore btn-restore-action" title="Restore Account Access" onclick="restoreUserRow(this, '${uEsc}', '${nEsc}')"><i class="bi bi-arrow-counterclockwise fs-6"></i></button>
          <button class="btn btn-sm btn-action-delete btn-delete-action ms-1" title="Permanently Delete Account" onclick="confirmDeleteUserRow(this, '${uEsc}', '${nEsc}')"><i class="bi bi-trash-fill"></i></button>
        `;
                }
            }

            updateDirectoryCounters();
            filterUserDirectory();

            alert('Account for "' + userName + '" has been moved to Archived status.');
        }
    }

    // Restore / Reactivate User Function (Puts back the user to Active status!)
    function restoreUserRow(btn, username, userName) {
        if (confirm('Are you sure you want to restore account for "' + userName + '" (' + username + ') back to Active status?')) {
            var formData = new FormData();
            formData.append('action', 'restore_user');
            formData.append('username', username);
            fetch('admin_dashboard.php', { method: 'POST', body: formData }).catch(e => console.warn(e));

            var users = [];
            try {
                users = JSON.parse(localStorage.getItem('legislative_system_users') || '[]');
            } catch (err) {
                users = [];
            }

            var idx = users.findIndex(u => u && u.username && u.username.toLowerCase() === username.toLowerCase());
            if (idx !== -1) {
                users[idx].status = 'Active';
            } else {
                users.push({
                    name: userName,
                    username: username,
                    status: 'Active'
                });
            }
            localStorage.setItem('legislative_system_users', JSON.stringify(users));

            var row = btn.closest('tr');
            if (row) {
                row.setAttribute('data-status', 'Active');
                var statusTd = row.cells[5];
                if (statusTd) {
                    statusTd.innerHTML = '<span class="badge badge-status-active rounded-pill">Active</span>';
                }
                var actionCell = row.querySelector('.action-buttons-cell');
                if (actionCell) {
                    var uEsc = username.replace(/'/g, "\\'");
                    var nEsc = userName.replace(/'/g, "\\'");
                    var email = (row.querySelector('.user-row-email')?.textContent || '').replace(/'/g, "\\'");
                    var role = (row.getAttribute('data-role') || 'Staff').replace(/'/g, "\\'");
                    var dept = (row.querySelector('.user-row-dept')?.textContent || '').replace(/'/g, "\\'");
                    actionCell.innerHTML = `
          <button class="btn btn-sm btn-action-view" title="View Full Profile" onclick="viewUserModal('${nEsc}', '${uEsc}', '${email}', '${role}', '${dept}', 'Active', 'Just Now')"><i class="bi bi-eye-fill"></i></button>
          <button class="btn btn-sm btn-action-edit" title="Edit Account Details" onclick="openEditUserModal('${nEsc}', '${uEsc}', '${email}', '${role}', '${dept}', 'Active')"><i class="bi bi-pencil-square"></i></button>
          <button class="btn btn-sm btn-action-archive btn-archive-action ms-1" title="Archive Account" onclick="archiveUserRow(this, '${uEsc}', '${nEsc}')"><i class="bi bi-archive-fill"></i></button>
        `;
                }
            }

            updateDirectoryCounters();
            filterUserDirectory();

            alert('Account for "' + userName + '" has been successfully restored to Active status.');
        }
    }

    // Permanently Delete User Function
    function confirmDeleteUserRow(btn, username, userName) {
        if (confirm('Are you sure you want to permanently delete the account for "' + userName + '" (' + username + ')? This action cannot be undone.')) {
            var formData = new FormData();
            formData.append('action', 'delete_user');
            formData.append('username', username);
            fetch('admin_dashboard.php', { method: 'POST', body: formData }).catch(e => console.warn(e));

            var users = [];
            try {
                users = JSON.parse(localStorage.getItem('legislative_system_users') || '[]');
            } catch (err) {
                users = [];
            }

            users = users.filter(u => !u || !u.username || u.username.toLowerCase() !== username.toLowerCase());
            localStorage.setItem('legislative_system_users', JSON.stringify(users));

            var row = btn.closest('tr');
            if (row) {
                row.remove();
            }

            updateDirectoryCounters();
            filterUserDirectory();

            alert('Account for "' + userName + '" has been permanently deleted.');
        }
    }

    // Run synchronization on DOM Load and Tab View
    document.addEventListener('DOMContentLoaded', function () {
        setTimeout(syncLocalStorageUsers, 100);
    });
</script>