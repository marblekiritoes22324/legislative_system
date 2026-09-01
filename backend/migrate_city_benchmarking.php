<?php
// backend/migrate_city_benchmarking.php — Migration & Seeder for Cross-City Benchmarking
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/evaluation_versions_helper.php';

if (!isset($conn) || !$conn) {
    die("Database connection failed\n");
}

echo "Starting Cross-City Benchmarking Database Migration...\n";

// 1. Add city_origin to policy_records if not exists
$col_chk = mysqli_query($conn, "SHOW COLUMNS FROM policy_records LIKE 'city_origin'");
if ($col_chk && mysqli_num_rows($col_chk) === 0) {
    mysqli_query($conn, "ALTER TABLE policy_records ADD COLUMN city_origin VARCHAR(100) DEFAULT 'City of Manila' AFTER category");
    echo "Added city_origin column to policy_records.\n";
} else {
    echo "city_origin column already exists in policy_records.\n";
}

// 2. Set existing records without city_origin to 'City of Manila'
mysqli_query($conn, "UPDATE policy_records SET city_origin = 'City of Manila' WHERE city_origin IS NULL OR city_origin = ''");

// 3. Add city_origin to evaluation_versions if not exists
$col_chk2 = mysqli_query($conn, "SHOW COLUMNS FROM evaluation_versions LIKE 'city_origin'");
if ($col_chk2 && mysqli_num_rows($col_chk2) === 0) {
    mysqli_query($conn, "ALTER TABLE evaluation_versions ADD COLUMN city_origin VARCHAR(100) DEFAULT 'City of Manila' AFTER evaluator");
    echo "Added city_origin column to evaluation_versions.\n";
}

// 4. Seed Quezon City (QC) and other Benchmark LGUs
$benchmarks = [
    [
        'title' => 'QC Ordinance No. SP-2876: Comprehensive Single-Use Plastic Regulation & Recovery Framework',
        'category' => 'Environmental Protection',
        'city_origin' => 'Quezon City (QC Benchmark)',
        'author' => 'Quezon City Council',
        'department' => 'Environmental Protection and Waste Management Dept (EPWMD)',
        'description' => 'A landmark Quezon City ordinance phasing out single-use plastics, imposing environmental recovery fees on non-compliant commercial establishments, and mandating alternative eco-friendly packaging.',
        'keywords' => 'plastic reduction, waste recovery, EPWMD, Quezon City, green ordinance',
        'publication_date' => '2024-03-15',
        'risk_level' => 'Low Risk',
        'overall_score' => 8.8,
        'ai_recommendation' => 'Highly recommended benchmark model. Provides strong regulatory precedent for Manila to strengthen enforcement mechanisms on commercial plastic bag fees and solid waste recovery.',
        'notes' => json_encode([
            'ai_analysis' => 'Quezon City successfully reduced commercial single-use bag usage by over 60% within 18 months through phased commercial merchant compliance and barangay eco-hubs.',
            'reason' => 'Clear fee-collection structure directly funding municipal green initiatives.',
            'criteria' => [
                'economic' => ['level' => 'Low', 'reason' => 'Generates self-sustaining environmental recovery funds collected directly from commercial retail chains.'],
                'social' => ['level' => 'Low', 'reason' => 'High public adoption rate through extensive barangay information campaigns and merchant bag-swap programs.'],
                'env' => ['level' => 'Low', 'reason' => 'Direct reduction of plastic blockage in municipal waterways and pumping stations.'],
                'legal' => ['level' => 'Low', 'reason' => 'Fully aligned with Republic Act 9003 (Ecological Solid Waste Management Act) and DILG guidelines.']
            ]
        ])
    ],
    [
        'title' => 'QC Ordinance No. SP-2350: Quezon City Green Building & Energy Efficiency Code',
        'category' => 'Infrastructure & Energy',
        'city_origin' => 'Quezon City (QC Benchmark)',
        'author' => 'QC Committee on Infrastructure',
        'department' => 'City Building Official & Climate Change Secretariat',
        'description' => 'Establishes mandatory green building standards, energy efficiency ratings, rainwater harvesting, and solar-ready roof requirements for all new commercial and residential developments exceeding 1,000 sqm.',
        'keywords' => 'green building, energy efficiency, solar energy, rainwater harvesting, QC',
        'publication_date' => '2024-05-10',
        'risk_level' => 'Low Risk',
        'overall_score' => 8.6,
        'ai_recommendation' => 'Excellent legislative reference for Manila City Hall urban renewal projects, especially for integrating green roof and flood-resilient rainwater capture into building permit approvals.',
        'notes' => json_encode([
            'ai_analysis' => 'Demonstrates effective local integration of national building code with green infrastructure incentives and tax rebate mechanisms.',
            'reason' => 'Property developers granted real property tax discounts upon achieving certified green performance ratings.',
            'criteria' => [
                'economic' => ['level' => 'Medium', 'reason' => 'Initial developer compliance costs offset by long-term municipal energy savings and commercial property tax incentives.'],
                'social' => ['level' => 'Low', 'reason' => 'Improves urban air quality, reduces building thermal heat islands, and enhances residential safety.'],
                'env' => ['level' => 'Low', 'reason' => 'Significantly curbs municipal carbon emissions and promotes decentralized stormwater retention.'],
                'legal' => ['level' => 'Low', 'reason' => 'Conforms to the Philippine Green Building Code (PD 1096) and DPWH standards.']
            ]
        ])
    ],
    [
        'title' => 'Pasig City Ordinance No. 12: People-Centric Mobility & Protected Bike Lane Network System',
        'category' => 'Transportation & Mobility',
        'city_origin' => 'Pasig City (Benchmark)',
        'author' => 'Pasig City Council',
        'department' => 'Pasig Transport Management Office',
        'description' => 'Institutes a permanent, physically protected bicycle lane network, sidewalk widening standards, and pedestrian-priority zones connecting transit terminals and municipal markets.',
        'keywords' => 'mobility, bike lanes, pedestrian safety, active transport, Pasig',
        'publication_date' => '2024-06-20',
        'risk_level' => 'Low Risk',
        'overall_score' => 8.7,
        'ai_recommendation' => 'Provides an effective framework for Manila City Council to adapt for the University Belt and Port Area pedestrian corridor safety programs.',
        'notes' => json_encode([
            'ai_analysis' => 'Pasig City converted underutilized road space into dedicated high-capacity bike and pedestrian corridors with a 42% decrease in pedestrian accidents.',
            'reason' => 'Integrated with community bicycle parking and public transit intermodal connections.',
            'criteria' => [
                'economic' => ['level' => 'Low', 'reason' => 'Low capital outlay for bollards and green street surfacing with substantial return in traffic decongestion.'],
                'social' => ['level' => 'Low', 'reason' => 'Equitable transportation benefits for students, daily commuters, and low-income workers.'],
                'env' => ['level' => 'Low', 'reason' => 'Cuts vehicular carbon emissions and particulate air pollution in dense urban corridors.'],
                'legal' => ['level' => 'Low', 'reason' => 'Fully aligned with National Active Transport Guidelines (DOTr-DOH-DILG-DPWH Joint Admin Order).']
            ]
        ])
    ]
];

foreach ($benchmarks as $bm) {
    // Check if already seeded
    $chk_p = mysqli_prepare($conn, "SELECT id FROM policy_records WHERE title = ?");
    mysqli_stmt_bind_param($chk_p, "s", $bm['title']);
    mysqli_stmt_execute($chk_p);
    mysqli_stmt_bind_result($chk_p, $existing_id);
    $found = mysqli_stmt_fetch($chk_p);
    mysqli_stmt_close($chk_p);

    if (!$found) {
        $ins = mysqli_prepare($conn, "
            INSERT INTO policy_records (title, category, city_origin, author, department, description, keywords, publication_date, status, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Completed', NOW())
        ");
        mysqli_stmt_bind_param($ins, "ssssssss", $bm['title'], $bm['category'], $bm['city_origin'], $bm['author'], $bm['department'], $bm['description'], $bm['keywords'], $bm['publication_date']);
        mysqli_stmt_execute($ins);
        $new_pid = mysqli_insert_id($conn);
        mysqli_stmt_close($ins);

        // Insert evaluation
        $ins_eval = mysqli_prepare($conn, "
            INSERT INTO evaluations (policy_id, policy_title, evaluator, risk_level, ai_recommendation, notes, status, overall_score, approved_by, approved_at, created_at, updated_at)
            VALUES (?, ?, 'Admin', ?, ?, ?, 'Approved', ?, 'System Administrator', NOW(), NOW(), NOW())
            ON DUPLICATE KEY UPDATE status = 'Approved', approved_by = 'System Administrator', approved_at = NOW()
        ");
        mysqli_stmt_bind_param($ins_eval, "issssd", $new_pid, $bm['title'], $bm['risk_level'], $bm['ai_recommendation'], $bm['notes'], $bm['overall_score']);
        mysqli_stmt_execute($ins_eval);
        mysqli_stmt_close($ins_eval);

        // Record in evaluation_versions
        record_evaluation_version($conn, $new_pid, [
            'evaluator' => 'Admin',
            'risk_level' => $bm['risk_level'],
            'economic_score' => $bm['overall_score'],
            'social_score' => $bm['overall_score'],
            'environmental_score' => $bm['overall_score'],
            'legal_score' => $bm['overall_score'],
            'overall_score' => $bm['overall_score'],
            'ai_recommendation' => $bm['ai_recommendation'],
            'notes' => $bm['notes'],
            'status' => 'Approved',
            'approved_by' => 'System Administrator'
        ]);

        echo "Seeded Benchmark: " . $bm['title'] . " (" . $bm['city_origin'] . ")\n";
    } else {
        // Update city_origin
        mysqli_query($conn, "UPDATE policy_records SET city_origin = '{$bm['city_origin']}' WHERE id = $existing_id");
        echo "Updated city_origin for existing record ID: $existing_id\n";
    }
}

echo "Cross-City Benchmarking migration completed successfully!\n";
