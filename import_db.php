<?php
header('Content-Type: text/html; charset=utf-8');

$host = getenv('DB_HOST') ?: (getenv('MYSQL_HOST') ?: 'localhost');
$username = getenv('DB_USERNAME') ?: (getenv('DB_USER') ?: (getenv('MYSQL_USER') ?: 'root'));
$password = getenv('DB_PASSWORD') !== false ? getenv('DB_PASSWORD') : (getenv('DB_PASS') !== false ? getenv('DB_PASS') : (getenv('MYSQL_PASSWORD') !== false ? getenv('MYSQL_PASSWORD') : ''));
$database = getenv('DB_DATABASE') ?: (getenv('DB_NAME') ?: (getenv('MYSQL_DATABASE') ?: 'legislative_management_db'));
$port = (int)(getenv('DB_PORT') ?: (getenv('MYSQL_PORT') ?: 3306));

echo "<!DOCTYPE html><html><head><title>Database Setup</title>";
echo "<style>body{font-family:system-ui,-apple-system,sans-serif;background:#0f172a;color:#e2e8f0;padding:40px;line-height:1.6;} .card{max-width:700px;margin:0 auto;background:#1e293b;border-radius:12px;padding:32px;box-shadow:0 10px 25px rgba(0,0,0,0.5);} h1{color:#38bdf8;margin-top:0;} .success{background:#064e3b;color:#6ee7b7;padding:12px 16px;border-radius:8px;margin:16px 0;font-weight:600;} .error{background:#7f1d1d;color:#fca5a5;padding:12px 16px;border-radius:8px;margin:16px 0;} .btn{display:inline-block;background:#0284c7;color:#fff;text-decoration:none;padding:12px 24px;border-radius:8px;font-weight:600;margin-top:20px;}</style></head><body>";
echo "<div class='card'>";
echo "<h1>🚀 Manila Legislative System - Database Setup</h1>";
echo "<p>Connecting to <strong>$database</strong> on <strong>$host:$port</strong>...</p>";

$conn = @mysqli_connect($host, $username, $password, $database, $port);

if (!$conn) {
    echo "<div class='error'>❌ Database connection failed: " . htmlspecialchars(mysqli_connect_error()) . "</div>";
    echo "</div></body></html>";
    exit;
}

echo "<div class='success'>✅ Connected to MySQL database successfully!</div>";

$sqlFile = __DIR__ . '/legistlative_database.backup.sql';
if (!file_exists($sqlFile)) {
    echo "<div class='error'>❌ Backup SQL file not found at: " . htmlspecialchars($sqlFile) . "</div>";
    echo "</div></body></html>";
    exit;
}

$sqlContent = file_get_contents($sqlFile);
$conn->set_charset('utf8mb4');

// Disable foreign key checks while importing schema and data
$conn->query("SET foreign_key_checks = 0;");

if ($conn->multi_query($sqlContent)) {
    $count = 0;
    do {
        if ($result = $conn->store_result()) {
            $result->free();
        }
        $count++;
    } while ($conn->more_results() && $conn->next_result());
    
    $conn->query("SET foreign_key_checks = 1;");

    if ($conn->errno) {
        echo "<p style='color:#facc15;'>⚠️ Note during import: " . htmlspecialchars($conn->error) . "</p>";
    }

    echo "<div class='success'>🎉 Database imported successfully! ($count query batches executed)</div>";
    echo "<p>All ordinances, users, evaluation tables, and settings are now ready.</p>";
    echo "<a href='/frontend/welcome.php' class='btn'>Go to Live System ➔</a>";
} else {
    echo "<div class='error'>❌ SQL import error: " . htmlspecialchars($conn->error) . "</div>";
}

$conn->close();
echo "</div></body></html>";
?>
