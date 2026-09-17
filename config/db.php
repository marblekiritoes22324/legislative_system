<?php

// Database Configuration (supports Cloud Environment Variables with local fallbacks)
$host = getenv('DB_HOST') ?: (getenv('MYSQL_HOST') ?: 'localhost');
$username = getenv('DB_USERNAME') ?: (getenv('DB_USER') ?: (getenv('MYSQL_USER') ?: 'root'));
$password = getenv('DB_PASSWORD') !== false ? getenv('DB_PASSWORD') : (getenv('DB_PASS') !== false ? getenv('DB_PASS') : (getenv('MYSQL_PASSWORD') !== false ? getenv('MYSQL_PASSWORD') : ''));
$database = getenv('DB_DATABASE') ?: (getenv('DB_NAME') ?: (getenv('MYSQL_DATABASE') ?: 'legislative_management_db'));
$port = (int)(getenv('DB_PORT') ?: (getenv('MYSQL_PORT') ?: 3306));

// Disable default PHP mysqli uncaught exception throwing for graceful error display
mysqli_report(MYSQLI_REPORT_OFF);

try {
    // Create Database Connection
    $conn = @mysqli_connect($host, $username, $password, $database, $port);

    if (!$conn) {
        throw new Exception(mysqli_connect_error());
    }

    // Auto-seed default policy records and evaluations if the database is newly initialized
    if (file_exists(__DIR__ . '/auto_seed.php')) {
        require_once __DIR__ . '/auto_seed.php';
    }
} catch (Throwable $e) {
    die("
    <div style='font-family: system-ui, -apple-system, sans-serif; max-width: 650px; margin: 60px auto; padding: 32px; border-radius: 16px; background: #ffffff; border: 1px solid #fee2e2; box-shadow: 0 10px 25px -5px rgba(0,0,0,0.1), 0 8px 10px -6px rgba(0,0,0,0.05); color: #1f2937;'>
        <div style='display: flex; align-items: center; gap: 12px; margin-bottom: 16px;'>
            <div style='width: 42px; height: 42px; background: #fee2e2; color: #dc2626; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 20px; font-weight: bold; flex-shrink: 0;'>!</div>
            <h2 style='margin: 0; color: #991b1b; font-size: 1.35rem; font-weight: 700;'>Database Connection Error</h2>
        </div>
        <p style='color: #4b5563; font-size: 0.95rem; line-height: 1.6; margin-bottom: 20px;'>Could not connect to the MySQL database (<strong>" . htmlspecialchars($database) . "</strong>). The MySQL service is currently stopped or not accepting connections.</p>
        <div style='background: #fef2f2; border: 1px solid #fca5a5; padding: 16px; border-radius: 10px; margin-bottom: 20px;'>
            <p style='margin: 0 0 8px 0; font-weight: 600; color: #991b1b; font-size: 0.9rem;'>How to fix this in XAMPP:</p>
            <ol style='margin: 0; padding-left: 20px; color: #7f1d1d; font-size: 0.88rem; line-height: 1.6;'>
                <li>Open <strong>XAMPP Control Panel</strong>.</li>
                <li>Find <strong>MySQL</strong> in the module list.</li>
                <li>Click the <strong>Start</strong> button next to MySQL.</li>
                <li>Refresh this browser page.</li>
            </ol>
        </div>
        <p style='font-size: 0.8rem; color: #9ca3af; margin: 0;'><em>Error details: " . htmlspecialchars($e->getMessage()) . "</em></p>
    </div>
    ");
}

?>