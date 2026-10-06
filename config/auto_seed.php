<?php
// config/auto_seed.php — Automatically synchronizes full database schema & records on startup
if (!isset($conn) || !$conn) {
    return;
}

try {
    $dump_candidates = [
        __DIR__ . '/../database/sync_live_database.sql',
        __DIR__ . '/../database/legistlative_database',
        __DIR__ . '/../database/legistlative_database.backup.sql'
    ];

    $dump_file = null;
    foreach ($dump_candidates as $candidate) {
        if (file_exists($candidate)) {
            $dump_file = $candidate;
            break;
        }
    }

    if ($dump_file && file_exists($dump_file)) {
        $file_md5 = md5_file($dump_file);

        // Ensure sync tracker table exists
        @mysqli_query($conn, "CREATE TABLE IF NOT EXISTS `_db_sync_version` (
            `id` INT PRIMARY KEY,
            `sync_hash` VARCHAR(64) NOT NULL,
            `synced_at` DATETIME NOT NULL
        ) ENGINE=InnoDB");

        $res = @mysqli_query($conn, "SELECT sync_hash FROM `_db_sync_version` WHERE id = 1");
        $current_hash = ($res && mysqli_num_rows($res) > 0) ? mysqli_fetch_row($res)[0] : '';

        // Only run when the dump file has changed or newly deployed
        if ($current_hash !== $file_md5) {
            $sql_content = file_get_contents($dump_file);
            if (!empty($sql_content)) {
                @mysqli_query($conn, "SET FOREIGN_KEY_CHECKS = 0;");
                @mysqli_query($conn, "SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';");

                if (@mysqli_multi_query($conn, $sql_content)) {
                    do {
                        if ($result = @mysqli_store_result($conn)) {
                            @mysqli_free_result($result);
                        }
                    } while (@mysqli_more_results($conn) && @mysqli_next_result($conn));
                }

                @mysqli_query($conn, "SET FOREIGN_KEY_CHECKS = 1;");

                // Update tracker
                @mysqli_query($conn, "REPLACE INTO `_db_sync_version` (`id`, `sync_hash`, `synced_at`) VALUES (1, '" . mysqli_real_escape_string($conn, $file_md5) . "', NOW())");
            }
        }
    }
} catch (Throwable $e) {
    error_log("[AutoSync Error] " . $e->getMessage());
}
