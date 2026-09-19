<?php
/**
 * Direct Live Database Migration Tool
 * Automatically imports all local tables, users, policies, evaluations, and datasets
 * to the remote HostForge / production database.
 */

ini_set('display_errors', 1);
error_reporting(E_ALL);
set_time_limit(300);

require_once __DIR__ . '/../config/db.php';

echo "<!DOCTYPE html>
<html>
<head>
    <title>Database Migration Runner</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #0f172a; color: #f8fafc; padding: 40px; }
        .card { max-width: 700px; margin: 0 auto; background: #1e293b; border-radius: 12px; padding: 28px; border: 1px solid #334155; box-shadow: 0 10px 25px rgba(0,0,0,0.5); }
        h1 { font-size: 22px; color: #38bdf8; margin-top: 0; }
        .log { background: #090d16; border-radius: 8px; padding: 16px; font-family: monospace; font-size: 13px; line-height: 1.6; color: #a5f3fc; overflow-x: auto; max-height: 400px; }
        .success { color: #4ade80; font-weight: bold; }
        .error { color: #f87171; font-weight: bold; }
        .btn { display: inline-block; background: #0284c7; color: #ffffff; padding: 10px 20px; border-radius: 6px; text-decoration: none; font-weight: 600; margin-top: 20px; }
        .btn:hover { background: #0369a1; }
    </style>
</head>
<body>
<div class='card'>
    <h1>🚀 Legislative System - Database Migration Runner</h1>";

if (!isset($conn) || !$conn) {
    die("<p class='error'>❌ Database connection failed. Please verify environment credentials.</p></div></body></html>");
}

echo "<div class='log'>";
echo "[INFO] Connected successfully to database: <strong>" . htmlspecialchars($database ?? 'legislative_management_db') . "</strong><br>";

$dump_file = __DIR__ . '/../legistlative_database';
if (!file_exists($dump_file)) {
    $dump_file = __DIR__ . '/../legistlative_database.backup.sql';
}

if (!file_exists($dump_file)) {
    echo "<p class='error'>❌ SQL dump file not found on server.</p>";
} else {
    echo "[INFO] Reading database dump (" . number_format(filesize($dump_file) / 1024, 2) . " KB)...<br>";
    $sql_content = file_get_contents($dump_file);

    if (empty($sql_content)) {
        echo "<p class='error'>❌ SQL dump file is empty.</p>";
    } else {
        mysqli_query($conn, "SET FOREIGN_KEY_CHECKS = 0;");
        mysqli_query($conn, "SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';");

        // Split statements by semicolon where appropriate or use multi_query
        $executed = 0;
        $errors = 0;

        if (mysqli_multi_query($conn, $sql_content)) {
            do {
                if ($result = mysqli_store_result($conn)) {
                    mysqli_free_result($result);
                }
                $executed++;
            } while (mysqli_more_results($conn) && mysqli_next_result($conn));
            
            if (mysqli_errno($conn)) {
                echo "<p class='error'>[WARN] Statement warning: " . htmlspecialchars(mysqli_error($conn)) . "</p>";
            } else {
                echo "<p class='success'>✅ Successfully executed all database dump batches!</p>";
            }
        } else {
            echo "<p class='error'>❌ Multi-query error: " . htmlspecialchars(mysqli_error($conn)) . "</p>";
        }

        mysqli_query($conn, "SET FOREIGN_KEY_CHECKS = 1;");

        // Verification of records
        $u_res = mysqli_query($conn, "SELECT COUNT(*) FROM user_directory");
        $u_total = ($u_res) ? mysqli_fetch_row($u_res)[0] : 0;

        $p_res = mysqli_query($conn, "SELECT COUNT(*) FROM policy_records");
        $p_total = ($p_res) ? mysqli_fetch_row($p_res)[0] : 0;

        $e_res = mysqli_query($conn, "SELECT COUNT(*) FROM evaluations");
        $e_total = ($e_res) ? mysqli_fetch_row($e_res)[0] : 0;

        echo "<br>----------------------------------------<br>";
        echo "[SUMMARY] Migration Statistics:<br>";
        echo "• 👤 Users in user_directory: <strong>$u_total</strong><br>";
        echo "• 📜 Policy Records: <strong>$p_total</strong><br>";
        echo "• 📊 Policy Evaluations: <strong>$e_total</strong><br>";
        echo "----------------------------------------<br>";
    }
}

echo "</div>";
echo "<a href='../frontend/welcome.php' class='btn'>Return to Sign In Page &rarr;</a>";
echo "</div></body></html>";
