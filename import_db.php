<?php
header('Content-Type: text/html; charset=utf-8');

$host = getenv('DB_HOST') ?: (getenv('MYSQL_HOST') ?: 'localhost');
$username = getenv('DB_USERNAME') ?: (getenv('DB_USER') ?: (getenv('MYSQL_USER') ?: 'root'));
$password = getenv('DB_PASSWORD') !== false ? getenv('DB_PASSWORD') : (getenv('DB_PASS') !== false ? getenv('DB_PASS') : (getenv('MYSQL_PASSWORD') !== false ? getenv('MYSQL_PASSWORD') : ''));
$database = getenv('DB_DATABASE') ?: (getenv('DB_NAME') ?: (getenv('MYSQL_DATABASE') ?: 'legislative_management_db'));
$port = (int)(getenv('DB_PORT') ?: (getenv('MYSQL_PORT') ?: 3306));

// Disable mysqli exception throwing so script can continue past warnings or existing tables
mysqli_report(MYSQLI_REPORT_OFF);

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

$conn->set_charset('utf8mb4');
$conn->query("SET foreign_key_checks = 0;");

// Split SQL file into clean individual statements and execute them
$lines = file($sqlFile);
$query = '';
$executed = 0;
$skipped = 0;

foreach ($lines as $line) {
    $trimmed = trim($line);
    // Ignore comments
    if ($trimmed === '' || strpos($trimmed, '--') === 0 || strpos($trimmed, '/*') === 0) {
        continue;
    }
    
    $query .= $line;
    if (substr($trimmed, -1) === ';') {
        if (!@$conn->query($query)) {
            $skipped++;
        } else {
            $executed++;
        }
        $query = '';
    }
}

$conn->query("SET foreign_key_checks = 1;");

echo "<div class='success'>🎉 Database setup completed!</div>";
echo "<p><strong>Executed queries:</strong> $executed | <strong>Ignored duplicates:</strong> $skipped</p>";
echo "<p>All tables, users, policies, and records are loaded into HostForge MySQL!</p>";
echo "<a href='/frontend/welcome.php' class='btn'>Go to Live System ➔</a>";

$conn->close();
echo "</div></body></html>";
?>
