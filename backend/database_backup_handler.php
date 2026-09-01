<?php
// ============================================================
// Database Backup & Restore Handler for Manila City Hall LIS
// ============================================================

require_once __DIR__ . '/../config/db.php';
if (file_exists(__DIR__ . '/log_activity.php')) {
    require_once __DIR__ . '/log_activity.php';
}

// 1. Handle GET / Direct Export Download
if (isset($_GET['action']) && $_GET['action'] === 'export_backup') {
    if (ob_get_level()) {
        ob_end_clean();
    }

    $db_name = $database ?? 'legislative_system';
    $timestamp = date('Y-m-d_His');
    $filename = "backup_{$db_name}_{$timestamp}.sql";

    header('Content-Type: application/octet-stream');
    header("Content-Disposition: attachment; filename=\"{$filename}\"");
    header('Pragma: no-cache');
    header('Expires: 0');

    $out = "-- ============================================================\n";
    $out .= "-- Manila City Hall - Legislative Information System (LIS)\n";
    $out .= "-- Database Backup Archive\n";
    $out .= "-- Database: `{$db_name}`\n";
    $out .= "-- Generated: " . date('Y-m-d H:i:s') . "\n";
    $out .= "-- Server Version: " . mysqli_get_server_info($conn) . "\n";
    $out .= "-- ============================================================\n\n";
    $out .= "SET FOREIGN_KEY_CHECKS = 0;\n";
    $out .= "SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';\n";
    $out .= "SET AUTOCOMMIT = 0;\n";
    $out .= "START TRANSACTION;\n";
    $out .= "SET time_zone = '+08:00';\n\n";

    // Retrieve all tables
    $tables = [];
    $res = mysqli_query($conn, "SHOW TABLES");
    if ($res) {
        while ($row = mysqli_fetch_row($res)) {
            $tables[] = $row[0];
        }
    }

    foreach ($tables as $tbl) {
        $out .= "-- ------------------------------------------------------------\n";
        $out .= "-- Table structure for `{$tbl}`\n";
        $out .= "-- ------------------------------------------------------------\n";
        $out .= "DROP TABLE IF EXISTS `{$tbl}`;\n";

        $create_res = mysqli_query($conn, "SHOW CREATE TABLE `{$tbl}`");
        if ($create_res && $create_row = mysqli_fetch_row($create_res)) {
            $out .= $create_row[1] . ";\n\n";
        }

        // Table Data
        $data_res = mysqli_query($conn, "SELECT * FROM `{$tbl}`");
        if ($data_res && mysqli_num_rows($data_res) > 0) {
            $out .= "-- Dumping data for table `{$tbl}`\n";
            $cols_count = mysqli_num_fields($data_res);

            while ($row = mysqli_fetch_row($data_res)) {
                $out .= "INSERT INTO `{$tbl}` VALUES (";
                $vals = [];
                for ($i = 0; $i < $cols_count; $i++) {
                    if (is_null($row[$i])) {
                        $vals[] = "NULL";
                    } else {
                        $escaped = mysqli_real_escape_string($conn, $row[$i]);
                        $vals[] = "'{$escaped}'";
                    }
                }
                $out .= implode(', ', $vals) . ");\n";
            }
            $out .= "\n";
        }
    }

    $out .= "SET FOREIGN_KEY_CHECKS = 1;\n";
    $out .= "COMMIT;\n";
    $out .= "-- End of backup file\n";

    if (function_exists('log_audit_action')) {
        log_audit_action($conn, 'System Administrator', 'Database Management', "Generated and downloaded database backup archive ({$filename})");
    }

    echo $out;
    exit;
}

// 2. Handle POST Actions (Restore, Optimize, Clear Logs)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    $action = $_POST['action'] ?? '';

    if ($action === 'restore_backup') {
        if (!isset($_FILES['backup_file']) || $_FILES['backup_file']['error'] !== UPLOAD_ERR_OK) {
            echo json_encode(['success' => false, 'error' => 'Please select a valid .sql backup file to upload.']);
            exit;
        }

        $file = $_FILES['backup_file'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if ($ext !== 'sql') {
            echo json_encode(['success' => false, 'error' => 'Invalid file format. Only .sql files are supported.']);
            exit;
        }

        if ($file['size'] > 50 * 1024 * 1024) { // 50MB max
            echo json_encode(['success' => false, 'error' => 'Backup file exceeds maximum allowed size (50MB).']);
            exit;
        }

        $sql_content = file_get_contents($file['tmp_name']);
        if (empty($sql_content)) {
            echo json_encode(['success' => false, 'error' => 'The uploaded backup file is empty.']);
            exit;
        }

        // Disable foreign keys and auto-commit for safety & performance
        mysqli_query($conn, "SET FOREIGN_KEY_CHECKS = 0");
        
        // Execute multi-query
        if (mysqli_multi_query($conn, $sql_content)) {
            do {
                if ($result = mysqli_store_result($conn)) {
                    mysqli_free_result($result);
                }
            } while (mysqli_more_results($conn) && mysqli_next_result($conn));

            mysqli_query($conn, "SET FOREIGN_KEY_CHECKS = 1");

            if (function_exists('log_audit_action')) {
                log_audit_action($conn, 'System Administrator', 'Database Management', "Restored database from uploaded backup file ({$file['name']})");
            }

            echo json_encode([
                'success' => true,
                'message' => 'Database successfully restored from backup snapshot (' . htmlspecialchars($file['name']) . ')!'
            ]);
            exit;
        } else {
            mysqli_query($conn, "SET FOREIGN_KEY_CHECKS = 1");
            echo json_encode([
                'success' => false,
                'error' => 'SQL Execution Error during restore: ' . mysqli_error($conn)
            ]);
            exit;
        }
    }

    if ($action === 'optimize_table') {
        $table_name = trim($_POST['table_name'] ?? '');
        if (empty($table_name)) {
            echo json_encode(['success' => false, 'error' => 'Table name is required.']);
            exit;
        }

        // Validate table name to avoid SQL injection
        $table_name_clean = preg_replace('/[^a-zA-Z0-9_]/', '', $table_name);
        $opt = mysqli_query($conn, "OPTIMIZE TABLE `{$table_name_clean}`");

        if ($opt) {
            if (function_exists('log_audit_action')) {
                log_audit_action($conn, 'System Administrator', 'Database Management', "Optimized database table `{$table_name_clean}`");
            }
            echo json_encode([
                'success' => true,
                'message' => "Table `{$table_name_clean}` was successfully optimized and defragmented."
            ]);
            exit;
        } else {
            echo json_encode(['success' => false, 'error' => mysqli_error($conn)]);
            exit;
        }
    }

    if ($action === 'optimize_all_tables') {
        $res = mysqli_query($conn, "SHOW TABLES");
        $optimized = [];
        if ($res) {
            while ($row = mysqli_fetch_row($res)) {
                $tbl = $row[0];
                mysqli_query($conn, "OPTIMIZE TABLE `{$tbl}`");
                $optimized[] = $tbl;
            }
        }

        if (function_exists('log_audit_action')) {
            log_audit_action($conn, 'System Administrator', 'Database Management', "Optimized all database tables (" . count($optimized) . " tables)");
        }

        echo json_encode([
            'success' => true,
            'message' => 'All database tables (' . count($optimized) . ') were successfully optimized!'
        ]);
        exit;
    }

    echo json_encode(['success' => false, 'error' => 'Unknown action request.']);
    exit;
}
