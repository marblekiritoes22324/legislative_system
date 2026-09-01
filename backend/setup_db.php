<?php
require_once '../config/db.php';

$tables = [];

// 1. policy_records (already exists)
$tables[] = "CREATE TABLE IF NOT EXISTS policy_records (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    category VARCHAR(100) NOT NULL,
    author VARCHAR(150) NOT NULL,
    department VARCHAR(100) NOT NULL,
    description TEXT,
    keywords VARCHAR(255),
    publication_date DATE,
    related_record VARCHAR(100),
    file_path VARCHAR(255),
    status ENUM('Published','Draft','Archived') DEFAULT 'Draft',
    ai_summary LONGTEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";

// 2. user_directory (user accounts)
$tables[] = "CREATE TABLE IF NOT EXISTS user_directory (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(150) NOT NULL,
    username VARCHAR(50) UNIQUE,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(100) DEFAULT 'Staff',
    department VARCHAR(100),
    status ENUM('Active','Inactive') DEFAULT 'Active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";

// 3. evaluations
$tables[] = "CREATE TABLE IF NOT EXISTS evaluations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    policy_id INT NOT NULL UNIQUE,
    policy_title VARCHAR(255) NOT NULL,
    evaluator VARCHAR(150) NOT NULL,
    economic_score TINYINT DEFAULT 0,
    social_score TINYINT DEFAULT 0,
    environmental_score TINYINT DEFAULT 0,
    legal_score TINYINT DEFAULT 0,
    overall_score DECIMAL(5,2) DEFAULT 0,
    risk_level VARCHAR(50),
    ai_recommendation TEXT,
    notes TEXT,
    status ENUM('Draft','Pending','Under Review','Completed','Archived') DEFAULT 'Draft',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_evaluations_policy FOREIGN KEY (policy_id) REFERENCES policy_records(id) ON DELETE CASCADE
)";

// 4. comparisons
$tables[] = "CREATE TABLE IF NOT EXISTS comparisons (
    id INT AUTO_INCREMENT PRIMARY KEY,
    policy_a_id INT,
    policy_b_id INT,
    policy_a_title VARCHAR(255),
    policy_b_title VARCHAR(255),
    comparison_notes TEXT,
    created_by VARCHAR(150),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

// 5. reports
$tables[] = "CREATE TABLE IF NOT EXISTS reports (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    report_type ENUM('Policy Summary','Evaluation Report','Analytics Report','User Report') DEFAULT 'Policy Summary',
    generated_by VARCHAR(150) NOT NULL,
    date_range_start DATE,
    date_range_end DATE,
    file_path VARCHAR(255),
    status ENUM('Generated','Draft') DEFAULT 'Draft',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

// 6. audit_logs
$tables[] = "CREATE TABLE IF NOT EXISTS audit_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user VARCHAR(150) DEFAULT 'Administrator',
    action VARCHAR(100) NOT NULL,
    module VARCHAR(100) NOT NULL,
    description TEXT,
    ip_address VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

// Create all tables
$success = true;
foreach ($tables as $sql) {
    if (!mysqli_query($conn, $sql)) {
        echo "<p style='color:red;'>Error: " . mysqli_error($conn) . "</p>";
        $success = false;
    }
}

if ($success) {
    echo "<h2 style='color:green;'>✅ All tables created successfully!</h2>";
    echo "<ul>";
    $result = mysqli_query($conn, "SHOW TABLES");
    while ($row = mysqli_fetch_row($result)) {
        echo "<li>" . $row[0] . "</li>";
    }
    echo "</ul>";
    echo "<a href='../admin/admin_dashboard.php' style='display:inline-block;margin-top:10px;padding:10px 20px;background:#0B2E59;color:white;border-radius:8px;text-decoration:none;'>→ Go to Admin Dashboard</a>";
}

mysqli_close($conn);
?>