<?php
// backend/log_activity.php — Audit Activity Logger Endpoint & Helper Function

if (!function_exists('resolve_audit_role')) {
    function resolve_audit_role($conn = null, $user_name = '', $existing_role = null) {
        if (!empty($existing_role)) {
            $r = strtolower(trim($existing_role));
            if ($r === 'admin' || $r === 'administrator') return 'Admin';
            if ($r === 'staff' || $r === 'researcher' || $r === 'analyst') return 'Staff';
            if ($r === 'councilor' || $r === 'user' || $r === 'councilor/user') return 'Councilor';
            return ucfirst($r);
        }

        $u = strtolower(trim($user_name ?? ''));
        if (empty($u) || $u === 'admin' || $u === 'system administrator' || $u === 'administration' || strpos($u, 'admin') !== false) {
            return 'Admin';
        }

        // Database lookup if connection available
        if (!empty($conn)) {
            $u_tbl = 'users';
            $t_check = @mysqli_query($conn, "SHOW TABLES LIKE 'user_directory'");
            if ($t_check && mysqli_num_rows($t_check) > 0) {
                $u_tbl = 'user_directory';
            }
            $safe_u = mysqli_real_escape_string($conn, $user_name);
            $q = @mysqli_query($conn, "SELECT role FROM $u_tbl WHERE LOWER(full_name) = LOWER('$safe_u') OR LOWER(username) = LOWER('$safe_u') LIMIT 1");
            if ($q && $row = mysqli_fetch_assoc($q)) {
                $db_role = strtolower($row['role'] ?? '');
                if ($db_role === 'admin') return 'Admin';
                if ($db_role === 'staff') return 'Staff';
                if ($db_role === 'user' || $db_role === 'councilor') return 'Councilor';
            }
        }

        // Context heuristic fallback
        if (strpos($u, 'caspe') !== false || strpos($u, 'councilor') !== false || strpos($u, 'hon.') !== false) {
            return 'Councilor';
        }
        if (strpos($u, 'quintana') !== false || strpos($u, 'staff') !== false || strpos($u, 'salas') !== false || strpos($u, 'daniel') !== false || strpos($u, 'researcher') !== false || strpos($u, 'analyst') !== false || strpos($u, 'cruz') !== false || strpos($u, 'santos') !== false || strpos($u, 'reyes') !== false) {
            return 'Staff';
        }

        return 'Admin';
    }
}

if (!function_exists('ensure_audit_logs_table')) {
    function ensure_audit_logs_table($conn) {
        if (empty($conn)) return false;

        $table_check = mysqli_query($conn, "SHOW TABLES LIKE 'audit_logs'");
        if (!$table_check || mysqli_num_rows($table_check) === 0) {
            mysqli_query($conn, "CREATE TABLE IF NOT EXISTS audit_logs (
                log_id INT AUTO_INCREMENT PRIMARY KEY,
                user VARCHAR(150) DEFAULT 'Admin',
                role VARCHAR(50) DEFAULT 'Admin',
                module VARCHAR(100) NOT NULL,
                activity VARCHAR(255) NOT NULL,
                status VARCHAR(50) DEFAULT 'Completed',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )");
        } else {
            // Ensure necessary columns exist
            $cols = [
                'user' => "ALTER TABLE audit_logs ADD COLUMN user VARCHAR(150) DEFAULT 'Admin'",
                'role' => "ALTER TABLE audit_logs ADD COLUMN role VARCHAR(50) DEFAULT 'Admin'",
                'module' => "ALTER TABLE audit_logs ADD COLUMN module VARCHAR(100) DEFAULT 'System'",
                'activity' => "ALTER TABLE audit_logs ADD COLUMN activity VARCHAR(255) DEFAULT 'Action performed'",
                'status' => "ALTER TABLE audit_logs ADD COLUMN status VARCHAR(50) DEFAULT 'Completed'",
                'created_at' => "ALTER TABLE audit_logs ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP"
            ];
            foreach ($cols as $col_name => $alter_query) {
                $c = mysqli_query($conn, "SHOW COLUMNS FROM audit_logs LIKE '$col_name'");
                if ($c && mysqli_num_rows($c) === 0) {
                    @mysqli_query($conn, $alter_query);
                }
            }
        }

        // Auto-seed initial demo records matching user screenshot if table is empty
        $count_res = mysqli_query($conn, "SELECT COUNT(*) as cnt FROM audit_logs");
        if ($count_res) {
            $row_cnt = mysqli_fetch_assoc($count_res);
            if (isset($row_cnt['cnt']) && (int)$row_cnt['cnt'] === 0) {
                $seed_sql = "INSERT INTO audit_logs (user, role, module, activity, status, created_at) VALUES 
                ('Admin', 'Admin', 'Policy Records', 'Uploaded Urban Traffic Congestion Study', 'Completed', '2026-05-14 10:15:00'),
                ('Researcher 01', 'Staff', 'Research Data', 'Added Traffic Survey Dataset', 'Completed', '2026-05-14 09:45:00'),
                ('Analyst 02', 'Staff', 'Evaluations', 'Completed Traffic Policy Evaluation', 'Completed', '2026-05-14 09:20:00'),
                ('Admin', 'Admin', 'Policy Records', 'Generated AI Summary for Traffic Policy', 'Completed', '2026-05-14 08:50:00'),
                ('Christian M. Caspe', 'Councilor', 'System', 'User login', 'Completed', '2026-05-13 16:30:00')";
                @mysqli_query($conn, $seed_sql);
            }
        }

        // Auto-update existing 'System Administrator' or 'Administration' entries to 'Admin'
        @mysqli_query($conn, "UPDATE audit_logs SET user = 'Admin' WHERE user IN ('Administration', 'System Administrator') OR user IS NULL OR user = ''");
        
        // Auto-backfill empty role fields
        @mysqli_query($conn, "UPDATE audit_logs SET role = 'Councilor' WHERE (role IS NULL OR role = '' OR role = 'User') AND (LOWER(user) LIKE '%caspe%' OR LOWER(user) LIKE '%councilor%')");
        @mysqli_query($conn, "UPDATE audit_logs SET role = 'Staff' WHERE (role IS NULL OR role = '' OR role = 'User') AND (LOWER(user) LIKE '%quintana%' OR LOWER(user) LIKE '%salas%' OR LOWER(user) LIKE '%daniel%' OR LOWER(user) LIKE '%staff%' OR LOWER(user) LIKE '%researcher%' OR LOWER(user) LIKE '%analyst%')");
        @mysqli_query($conn, "UPDATE audit_logs SET role = 'Admin' WHERE (role IS NULL OR role = '') AND (LOWER(user) = 'admin' OR LOWER(user) LIKE '%admin%')");
        @mysqli_query($conn, "UPDATE audit_logs SET role = 'Staff' WHERE role IS NULL OR role = '' OR role = 'User'");

        return true;
    }
}

if (!function_exists('log_audit_action')) {
    function log_audit_action($conn, $user, $module, $activity, $status = 'Completed', $role = null) {
        if (empty($conn)) return false;
        ensure_audit_logs_table($conn);

        $user_val = (!empty($user) && $user !== 'System Administrator' && $user !== 'Administration') ? $user : 'Admin';
        $role_val = resolve_audit_role($conn, $user_val, $role);
        $module_val = !empty($module) ? $module : 'System';
        $activity_val = !empty($activity) ? $activity : 'Action performed';
        $status_val = !empty($status) ? $status : 'Completed';

        // Prevent duplicate burst logging within 3 seconds
        $dup_check = @mysqli_query($conn, "SELECT log_id FROM audit_logs WHERE user = '" . mysqli_real_escape_string($conn, $user_val) . "' AND module = '" . mysqli_real_escape_string($conn, $module_val) . "' AND activity = '" . mysqli_real_escape_string($conn, $activity_val) . "' AND created_at >= (NOW() - INTERVAL 3 SECOND) LIMIT 1");
        if ($dup_check && mysqli_num_rows($dup_check) > 0) {
            return true;
        }

        $stmt = mysqli_prepare($conn, "INSERT INTO audit_logs (user, role, module, activity, status) VALUES (?, ?, ?, ?, ?)");
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "sssss", $user_val, $role_val, $module_val, $activity_val, $status_val);
            $res = mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            return $res;
        } else {
            return mysqli_query($conn, "INSERT INTO audit_logs (user, role, module, activity, status) VALUES ('" . mysqli_real_escape_string($conn, $user_val) . "', '" . mysqli_real_escape_string($conn, $role_val) . "', '" . mysqli_real_escape_string($conn, $module_val) . "', '" . mysqli_real_escape_string($conn, $activity_val) . "', '" . mysqli_real_escape_string($conn, $status_val) . "')");
        }
    }
}

// Handle AJAX POST/GET audit logging & fetch calls from frontend JavaScript
if (isset($_REQUEST['action'])) {
    $path_to_db = __DIR__ . '/../config/db.php';
    if (file_exists($path_to_db)) {
        require_once $path_to_db;
    }
    
    if (isset($conn) && $conn) {
        ensure_audit_logs_table($conn);

        if ($_REQUEST['action'] === 'log_audit') {
            $user_post = $_POST['user'] ?? $_GET['user'] ?? 'Admin';
            if ($user_post === 'System Administrator' || $user_post === 'Administration') $user_post = 'Admin';
            $role_post = $_POST['role'] ?? $_GET['role'] ?? null;
            $module_post = $_POST['module'] ?? $_GET['module'] ?? 'System';
            $activity_post = $_POST['activity'] ?? $_GET['activity'] ?? 'Action performed';
            $status_post = $_POST['status'] ?? $_GET['status'] ?? 'Completed';

            $logged = log_audit_action($conn, $user_post, $module_post, $activity_post, $status_post, $role_post);
            header('Content-Type: application/json');
            echo json_encode(['success' => (bool)$logged]);
            exit;
        }

        if ($_REQUEST['action'] === 'get_recent') {
            header('Content-Type: application/json');
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
            $res = mysqli_query($conn, "SELECT * FROM audit_logs ORDER BY created_at DESC, 1 DESC LIMIT " . $limit);
            $activities = [];
            if ($res) {
                while ($row = mysqli_fetch_assoc($res)) {
                    $ts = !empty($row['created_at']) ? strtotime($row['created_at']) : time();
                    $uName = $row['user'] ?? 'Admin';
                    if ($uName === 'System Administrator' || $uName === 'Administration' || empty($uName)) {
                        $uName = 'Admin';
                    }
                    $roleName = $row['role'] ?? resolve_audit_role($conn, $uName);
                    $activities[] = [
                        'id' => $row['log_id'] ?? ($row['id'] ?? 0),
                        'date_time' => date('M d, Y h:i A', $ts),
                        'raw_date' => $row['created_at'] ?? '',
                        'activity' => $row['activity'] ?? ($row['action'] ?? ($row['description'] ?? 'System activity')),
                        'module' => $row['module'] ?? 'System',
                        'status' => $row['status'] ?? 'Completed',
                        'user' => $uName,
                        'role' => $roleName
                    ];
                }
            }
            echo json_encode(['success' => true, 'activities' => $activities]);
            exit;
        }
    }
}

