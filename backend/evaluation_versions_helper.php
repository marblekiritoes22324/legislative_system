<?php
// backend/evaluation_versions_helper.php — Evaluation Version History & Comparison Engine

if (!function_exists('ensure_evaluation_versions_table')) {
    function ensure_evaluation_versions_table($conn)
    {
        static $initialized = false;
        if ($initialized || empty($conn))
            return;

        $create_sql = "
            CREATE TABLE IF NOT EXISTS evaluation_versions (
                id INT AUTO_INCREMENT PRIMARY KEY,
                policy_id INT NOT NULL,
                version_number INT NOT NULL DEFAULT 1,
                version_label VARCHAR(50) DEFAULT 'Version 1',
                evaluator VARCHAR(100) DEFAULT 'Admin',
                risk_level VARCHAR(50) DEFAULT 'Low Risk',
                economic_score DECIMAL(4,2) DEFAULT 8.0,
                social_score DECIMAL(4,2) DEFAULT 8.0,
                environmental_score DECIMAL(4,2) DEFAULT 8.0,
                legal_score DECIMAL(4,2) DEFAULT 8.0,
                overall_score DECIMAL(4,2) DEFAULT 8.0,
                ai_recommendation TEXT,
                notes TEXT,
                status VARCHAR(50) DEFAULT 'Approved',
                approved_by VARCHAR(255) DEFAULT 'System Administrator',
                approved_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_eval_versions_policy (policy_id),
                INDEX idx_eval_versions_status (status)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ";
        @mysqli_query($conn, $create_sql);

        // Seed Version 1 from existing evaluations table if table was empty
        $chk = @mysqli_query($conn, "SELECT COUNT(*) FROM evaluation_versions");
        if ($chk) {
            $row = mysqli_fetch_row($chk);
            if (!empty($row) && (int) $row[0] === 0) {
                $seed_sql = "
                    INSERT INTO evaluation_versions (
                        policy_id, version_number, version_label, evaluator, risk_level,
                        economic_score, social_score, environmental_score, legal_score,
                        overall_score, ai_recommendation, notes, status, approved_by,
                        approved_at, created_at
                    )
                    SELECT 
                        policy_id, 1, 'Version 1', evaluator, risk_level,
                        economic_score, social_score, environmental_score, legal_score,
                        overall_score, ai_recommendation, notes, status, approved_by,
                        COALESCE(approved_at, NOW()), COALESCE(created_at, NOW())
                    FROM evaluations
                    WHERE policy_id > 0
                ";
                @mysqli_query($conn, $seed_sql);

                // Add sample Revision (Version 2) to the first policy to demonstrate diff highlighting
                $first_p = @mysqli_query($conn, "SELECT policy_id, title FROM policy_records WHERE (status IS NULL OR status != 'Archived') LIMIT 1");
                if ($first_p && $p_row = mysqli_fetch_assoc($first_p)) {
                    $pid = (int) $p_row['policy_id'];
                    $v2_notes = json_encode([
                        'ai_analysis' => 'Follow-up evaluation incorporates district council feedback and revised budget allocations.',
                        'reason' => 'Resource allocation streamlined with increased municipal council alignment.',
                        'criteria' => [
                            'economic' => ['level' => 'Low', 'reason' => 'Secured supplemental city council infrastructure funding; operating expenditure decreased by 15%.'],
                            'social' => ['level' => 'Low', 'reason' => 'Direct positive impact across all target districts with broad community support.'],
                            'env' => ['level' => 'Low', 'reason' => 'Environmentally certified sustainable implementation plan with zero adverse footprint.'],
                            'legal' => ['level' => 'Low', 'reason' => 'Fully harmonized with latest 2026 Manila City Ordinances and Department of Interior guidelines.']
                        ]
                    ]);
                    @mysqli_query($conn, "
                        INSERT INTO evaluation_versions (
                            policy_id, version_number, version_label, evaluator, risk_level,
                            economic_score, social_score, environmental_score, legal_score,
                            overall_score, ai_recommendation, notes, status, approved_by,
                            approved_at, created_at
                        ) VALUES (
                            $pid, 2, 'Version 2 (Revision)', 'Admin', 'Low Risk',
                            9.0, 9.0, 9.0, 9.5,
                            9.1, 'Approve & Fast-Track Implementation with Enhanced District Resource Allocation', '$v2_notes', 'Approved', 'System Administrator',
                            NOW(), NOW()
                        )
                    ");
                }
            }
        }

        $initialized = true;
    }
}

if (!function_exists('record_evaluation_version')) {
    function record_evaluation_version($conn, $policy_id, $eval_data)
    {
        if (empty($conn) || empty($policy_id))
            return false;
        ensure_evaluation_versions_table($conn);

        // Find next version number for this policy
        $stmt = mysqli_prepare($conn, "SELECT COALESCE(MAX(version_number), 0) + 1 FROM evaluation_versions WHERE policy_id = ?");
        $next_version = 1;
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "i", $policy_id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_bind_result($stmt, $max_v);
            if (mysqli_stmt_fetch($stmt) && $max_v > 0) {
                $next_version = (int) $max_v;
            }
            mysqli_stmt_close($stmt);
        }

        $version_label = 'Version ' . $next_version;
        $evaluator = $eval_data['evaluator'] ?? 'Admin';
        $risk_level = $eval_data['risk_level'] ?? 'Low Risk';
        $econ_score = floatval($eval_data['economic_score'] ?? 8.0);
        $social_score = floatval($eval_data['social_score'] ?? 8.0);
        $env_score = floatval($eval_data['environmental_score'] ?? 8.0);
        $legal_score = floatval($eval_data['legal_score'] ?? 8.0);
        $overall_score = floatval($eval_data['overall_score'] ?? 8.0);
        $ai_recommendation = $eval_data['ai_recommendation'] ?? 'Suitable for implementation.';
        $notes = is_array($eval_data['notes'] ?? null) ? json_encode($eval_data['notes']) : ($eval_data['notes'] ?? '');
        $status = $eval_data['status'] ?? 'Completed';
        $approved_by = ($status === 'Approved') ? ($eval_data['approved_by'] ?? 'System Administrator') : null;
        $approved_at = ($status === 'Approved') ? date('Y-m-d H:i:s') : null;

        $ins_stmt = mysqli_prepare($conn, "
            INSERT INTO evaluation_versions (
                policy_id, version_number, version_label, evaluator, risk_level,
                economic_score, social_score, environmental_score, legal_score,
                overall_score, ai_recommendation, notes, status, approved_by, approved_at, created_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        if ($ins_stmt) {
            mysqli_stmt_bind_param(
                $ins_stmt,
                "iisssdddddsssss",
                $policy_id,
                $next_version,
                $version_label,
                $evaluator,
                $risk_level,
                $econ_score,
                $social_score,
                $env_score,
                $legal_score,
                $overall_score,
                $ai_recommendation,
                $notes,
                $status,
                $approved_by,
                $approved_at
            );
            $res = mysqli_stmt_execute($ins_stmt);
            mysqli_stmt_close($ins_stmt);
            return [
                'success' => (bool) $res,
                'version_number' => $next_version,
                'version_label' => $version_label
            ];
        }
        return false;
    }
}

if (!function_exists('get_policy_versions_comparison_data')) {
    function get_policy_versions_comparison_data($conn)
    {
        if (empty($conn))
            return [];
        ensure_evaluation_versions_table($conn);

        $extractLevel = function ($crit_item, $notes, $key, $score) {
            if (is_array($crit_item) && !empty($crit_item['level']))
                return $crit_item['level'];
            if (is_string($crit_item) && !empty($crit_item))
                return $crit_item;
            if (is_array($notes) && !empty($notes[$key . '_level']))
                return $notes[$key . '_level'];
            if (!empty($score) && is_numeric($score) && $score > 0) {
                if ($score >= 8)
                    return 'Low';
                if ($score >= 5)
                    return 'Medium';
                return 'High';
            }
            return 'Low';
        };

        $extractReason = function ($crit_item, $notes, $key, $default) {
            if (is_array($crit_item) && !empty($crit_item['reason']))
                return $crit_item['reason'];
            if (is_array($notes) && !empty($notes[$key . '_reason']))
                return $notes[$key . '_reason'];
            return $default;
        };

        // Fetch all policies that have Approved evaluation versions
        $query = "
            SELECT 
                p.id AS policy_id, 
                p.title AS policy_title, 
                p.category AS policy_category,
                COALESCE(p.city_origin, 'City of Manila') AS city_origin,
                ev.id AS version_id,
                ev.version_number,
                ev.version_label,
                ev.evaluator,
                ev.risk_level,
                ev.ai_recommendation,
                ev.economic_score,
                ev.social_score,
                ev.environmental_score,
                ev.legal_score,
                ev.overall_score,
                ev.notes,
                ev.status AS version_status,
                ev.approved_by,
                ev.approved_at,
                ev.created_at
            FROM policy_records p
            INNER JOIN evaluation_versions ev ON ev.policy_id = p.id
            WHERE (p.status IS NULL OR p.status != 'Archived')
              AND (ev.status = 'Approved' OR ev.status = 'Completed')
            ORDER BY p.id ASC, ev.version_number ASC, ev.created_at ASC
        ";

        $res = @mysqli_query($conn, $query);
        $policies_versions = [];

        if ($res) {
            while ($row = mysqli_fetch_assoc($res)) {
                $p_id = (int) $row['policy_id'];
                if (!isset($policies_versions[$p_id])) {
                    $policies_versions[$p_id] = [
                        'policy_id' => $p_id,
                        'title' => $row['policy_title'],
                        'category' => $row['policy_category'] ?: 'General',
                        'city_origin' => $row['city_origin'] ?: 'City of Manila',
                        'versions' => []
                    ];
                }

                $notes_data = [];
                if (!empty($row['notes'])) {
                    $trimmed = trim($row['notes']);
                    if (strpos($trimmed, '{') === 0 || strpos($trimmed, '[') === 0) {
                        $decoded = json_decode($trimmed, true);
                        if (is_array($decoded)) {
                            $notes_data = $decoded;
                        }
                    }
                }
                $crit = $notes_data['criteria'] ?? [];

                $econ_level = $extractLevel($crit['economic'] ?? null, $notes_data, 'economic', $row['economic_score'] ?? 0);
                $social_level = $extractLevel($crit['social'] ?? null, $notes_data, 'social', $row['social_score'] ?? 0);
                $env_level = $extractLevel($crit['env'] ?? ($crit['environmental'] ?? null), $notes_data, 'env', $row['environmental_score'] ?? 0);
                $legal_level = $extractLevel($crit['legal'] ?? null, $notes_data, 'legal', $row['legal_score'] ?? 0);

                $econ_reason = $extractReason($crit['economic'] ?? null, $notes_data, 'economic', 'Funding and implementation costs are manageable and available.');
                $social_reason = $extractReason($crit['social'] ?? null, $notes_data, 'social', 'The policy provides benefits to affected communities and improves quality of life.');
                $env_reason = $extractReason($crit['env'] ?? ($crit['environmental'] ?? null), $notes_data, 'env', 'The policy has minimal expected environmental effects.');
                $legal_reason = $extractReason($crit['legal'] ?? null, $notes_data, 'legal', 'No major legal conflicts were identified with existing laws and regulations.');

                $app_date = !empty($row['approved_at']) ? date('M d, Y h:i A', strtotime($row['approved_at'])) : date('M d, Y h:i A', strtotime($row['created_at']));
                $app_by = !empty($row['approved_by']) ? $row['approved_by'] : ($row['evaluator'] ?: 'System Administrator');

                $policies_versions[$p_id]['versions'][] = [
                    'version_id' => (int) $row['version_id'],
                    'version_number' => (int) $row['version_number'],
                    'version_label' => $row['version_label'] ?: ('Version ' . $row['version_number']),
                    'approved_by' => $app_by,
                    'approved_at' => $app_date,
                    'risk_level' => $row['risk_level'] ?: 'Low Risk',
                    'ai_recommendation' => $row['ai_recommendation'] ?: 'Suitable for implementation.',
                    'economic_level' => $econ_level,
                    'economic_reason' => $econ_reason,
                    'social_level' => $social_level,
                    'social_reason' => $social_reason,
                    'env_level' => $env_level,
                    'env_reason' => $env_reason,
                    'legal_level' => $legal_level,
                    'legal_reason' => $legal_reason,
                ];
            }
        }

        // Process oldest vs newest version for each policy
        $version_comparison_data = [];
        foreach ($policies_versions as $p_id => $data) {
            $versions = $data['versions'];
            $v_count = count($versions);
            if ($v_count === 0)
                continue;

            $oldest = $versions[0];
            $newest = $versions[$v_count - 1];

            $has_multiple = ($v_count > 1);

            $version_comparison_data[] = [
                'policy_id' => $p_id,
                'title' => $data['title'],
                'category' => $data['category'],
                'city_origin' => $data['city_origin'],
                'total_versions' => $v_count,
                'has_multiple' => $has_multiple,
                'oldest' => $oldest,
                'newest' => $newest,
                'all_versions' => $versions
            ];
        }

        return $version_comparison_data;
    }
}
