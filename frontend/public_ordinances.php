<?php
session_start();
require_once __DIR__ . '/../config/db.php';

// Fetch dynamic policies posted from Admin Policy Records (excluding archived policies)
$public_ordinances = [];
if (!empty($conn)) {
  $ord_res = mysqli_query($conn, "SELECT * FROM policy_records WHERE status IS NULL OR status != 'Archived' ORDER BY created_at DESC");
  if ($ord_res) {
    while ($row = mysqli_fetch_assoc($ord_res)) {
      $public_ordinances[] = $row;
    }
  }
}

// Fallback seed ordinances if database has no rows
if (empty($public_ordinances)) {
  $public_ordinances = [
    [
      'id' => 1,
      'title' => 'Digital Governance Ordinance',
      'related_record' => 'Ord. No. 8901',
      'category' => 'Governance',
      'publication_date' => '2026-01-15',
      'created_at' => '2026-01-15',
      'status' => 'Enacted',
      'description' => 'Mandates the digitization of all city council legislative files and automated archiving.',
      'file_path' => ''
    ],
    [
      'id' => 2,
      'title' => 'Green Building Code',
      'related_record' => 'Ord. No. 8845',
      'category' => 'Environment',
      'publication_date' => '2025-11-20',
      'created_at' => '2026-01-01',
      'status' => 'Enacted',
      'description' => 'Requires commercial building developments in Manila to adopt solar energy fixtures and rainwater harvesting.',
      'file_path' => ''
    ],
    [
      'id' => 3,
      'title' => 'Heritage Preservation Code',
      'related_record' => 'Ord. No. 8790',
      'category' => 'Culture',
      'publication_date' => '2025-08-12',
      'created_at' => '2025-08-12',
      'status' => 'Enacted',
      'description' => 'Protects historical landmarks in Ermita, Malate, and Intramuros by setting strict height and architectural limits.',
      'file_path' => ''
    ],
    [
      'id' => 4,
      'title' => 'Small Business Tax Relief',
      'related_record' => 'Ord. No. 8712',
      'category' => 'Economy',
      'publication_date' => '2024-02-28',
      'created_at' => '2025-02-28',
      'status' => 'Approved',
      'description' => 'Provides local tax discounts and extended payment deadlines for micro-entrepreneurs across Manila.',
      'file_path' => ''
    ],
    [
      'id' => 5,
      'title' => 'Traffic Management Ordinance',
      'related_record' => 'Ord. No. 8650',
      'category' => 'Transportation',
      'publication_date' => '2024-10-14',
      'created_at' => '2025-10-14',
      'status' => 'Approved',
      'description' => 'Establishes clear priority lanes, pedestrian safety zones, and traffic signal upgrades in key districts.',
      'file_path' => ''
    ],
  ];
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $raw_user = trim($_POST['username'] ?? '');
  $raw_pass = trim($_POST['password'] ?? '');

  $clean_user = preg_replace('/[^a-zA-Z0-9]/', '', strtolower($raw_user));

  if ($clean_user === 'admin') {
    if ($raw_pass === 'admin123') {
      echo "<script>
        localStorage.setItem('admin_logged_in', 'true');
        localStorage.setItem('current_user', JSON.stringify({username: 'admin', name: 'System Administrator', role: 'admin'}));
        window.location.href = '../admin/admin_dashboard.php';
      </script>";
      exit();
    } else {
      $error = 'Incorrect password.';
    }
  } elseif ($clean_user === 'user') {
    if ($raw_pass === 'password') {
      echo "<script>
        localStorage.setItem('current_user', JSON.stringify({username: 'user', name: 'Juan Dela Cruz', role: 'user'}));
        window.location.href = '../users/user_dashboard.php?username=user&name=Juan%20Dela%20Cruz';
      </script>";
      exit();
    } else {
      $error = 'Incorrect password.';
    }
  } else {
    $error = 'Username does not exist.';
  }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Public Ordinances – Manila City Hall Legislative Information System</title>
  <!-- Google Fonts & Bootstrap Icons -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link
    href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
    rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <!-- Shared Stylesheet -->
  <link rel="stylesheet" href="../assets/css/welcome.css?v=2.0">
  <style>
    body {
      margin: 0;
      padding: 0;
      overflow-x: hidden;
    }
  </style>
</head>

<body>

  <!-- FULL-WIDTH WHITE NAVIGATION HEADER (PICTURE 1 LAYOUT) -->
  <nav class="navbar" id="navbar">
    <a href="welcome.php" class="navbar-brand">
      <img src="../assets/images/manilacityhall.svg" alt="Manila City Hall Logo"
        style="width:48px; height:48px; object-fit:contain;">
      <div class="brand-text-container">
        <span class="brand-title-text">Manila City Hall Portal</span>
        <span class="brand-subtitle-text">Legislative Information System</span>
      </div>
    </a>

    <!-- Center Navigation Links -->
    <ul class="nav-menu">
      <li><a href="welcome.php" class="nav-link-item">Home</a></li>
      <li><a href="about.php" class="nav-link-item">About</a></li>
      <li><a href="public_ordinances.php" class="nav-link-item active">Ordinances</a></li>
      <li><a href="contact.php" class="nav-link-item">Contact</a></li>
    </ul>

    <!-- Right Side Actions Buttons (Admin-controlled account access) -->
    <div class="navbar-actions" style="display: flex; align-items: center; gap: 12px;">
      <a href="welcome.php?login=1" class="btn-nav-outline"
        style="text-decoration: none; padding: 9px 22px; border: 1.5px solid #0B1B3D; color: #0B1B3D; border-radius: 8px; font-weight: 700; font-size: 0.9rem; display: inline-flex; align-items: center; gap: 6px;"><i
          class="bi bi-box-arrow-in-right"></i> Sign In</a>
    </div>
  </nav>

  <!-- FULL-WIDTH CONTENT CONTAINER -->
  <div
    style="margin-top: 80px; min-height: calc(100vh - 80px); background: #F8FAFC; padding: 50px 32px 60px 32px; display: flex; flex-direction: column; justify-content: space-between; box-sizing: border-box;">

    <div style="max-width: 1400px; margin: 0 auto; width: 100%;">
      <!-- Section Header (Centered like About page) -->
      <div style="text-align: center; margin-bottom: 40px;">
        <h1
          style="font-family: 'Outfit', sans-serif; font-size: 2.6rem; font-weight: 800; color: #0B1B3D; margin: 0 0 12px 0; line-height: 1.2;">
          Public Ordinances
        </h1>
        <p style="font-size: 1.1rem; color: #64748B; max-width: 800px; margin: 0 auto; line-height: 1.6;">
          Search and view publicly available ordinances of Manila City.
        </p>
      </div>

      <!-- Search & Filter Controls Card -->
      <div
        style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 16px; padding: 24px 30px; margin-bottom: 28px; box-shadow: 0 4px 18px rgba(11,27,61,0.03);">
        <div style="display: flex; flex-wrap: wrap; gap: 18px; align-items: center;">
          <!-- Search Input -->
          <div style="flex: 1.5; min-width: 260px; position: relative;">
            <i class="bi bi-search"
              style="position: absolute; left: 18px; top: 50%; transform: translateY(-50%); color: #94A3B8; font-size: 1.05rem;"></i>
            <input type="text" id="searchInput" placeholder="Search ordinance..."
              style="width: 100%; padding: 14px 18px 14px 48px; background: #FFFFFF; border: 1px solid #CBD5E1; border-radius: 10px; font-size: 0.98rem; color: #0F172A; outline: none; box-sizing: border-box;">
          </div>

          <!-- Category Select -->
          <div style="flex: 1; min-width: 200px;">
            <select id="categorySelect"
              style="width: 100%; padding: 14px 18px; background: #FFFFFF; border: 1px solid #CBD5E1; border-radius: 10px; font-size: 0.98rem; color: #0F172A; outline: none; box-sizing: border-box; cursor: pointer;">
              <option value="">All Categories</option>
              <option value="Health and Sanitation">Health and Sanitation</option>
              <option value="Civil Registry and Public Services">Civil Registry and Public Services</option>
              <option value="Education and Employment">Education and Employment</option>
              <option value="Social Welfare and Community Affairs">Social Welfare and Community Affairs</option>
              <option value="Infrastructure, Traffic and Environment">Infrastructure, Traffic and Environment</option>
              <option value="Other">Other</option>
            </select>
          </div>

          <!-- Year Select -->
          <div style="flex: 1; min-width: 160px;">
            <select id="yearSelect"
              style="width: 100%; padding: 14px 18px; background: #FFFFFF; border: 1px solid #CBD5E1; border-radius: 10px; font-size: 0.98rem; color: #0F172A; outline: none; box-sizing: border-box; cursor: pointer;">
              <option value="">All Years</option>
              <option value="2026">2026</option>
              <option value="2025">2025</option>
              <option value="2024">2024</option>
              <option value="2023">2023</option>
            </select>
          </div>

          <!-- Search Button -->
          <div style="min-width: 140px;">
            <button onclick="filterOrdinances()"
              style="width: 100%; padding: 14px 30px; background: #0B1B3D; color: #FFFFFF; border: none; border-radius: 10px; font-weight: 700; font-size: 0.98rem; cursor: pointer; transition: background 0.2s ease;">
              Search
            </button>
          </div>
        </div>
      </div>

      <!-- Ordinances Table Card -->
      <div
        style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 20px rgba(11,27,61,0.04); margin-bottom: 40px;">
        <div style="overflow-x: auto;">
          <table style="width: 100%; border-collapse: collapse; text-align: left;">
            <thead>
              <tr style="background: #0B1B3D; color: #FFFFFF; font-size: 0.98rem; font-weight: 700;">
                <th style="padding: 22px 30px; white-space: nowrap; width: 16%;">Ordinance No.</th>
                <th style="padding: 22px 30px; width: 36%;">Ordinance Title</th>
                <th style="padding: 22px 30px; white-space: nowrap; width: 18%;">Category</th>
                <th style="padding: 22px 30px; white-space: nowrap; width: 10%;">Year</th>
                <th style="padding: 22px 30px; white-space: nowrap; width: 10%;">Status</th>
                <th style="padding: 22px 30px; text-align: center; white-space: nowrap; width: 10%;">Action</th>
            </thead>
            <tbody id="ordinancesTableBody">
              <?php foreach ($public_ordinances as $index => $ord):
                $ord_no = !empty($ord['related_record']) ? $ord['related_record'] : ('Ord. No. ' . (8900 - $index));
                $pub_date = !empty($ord['publication_date']) ? $ord['publication_date'] : ($ord['created_at'] ?? '2026-01-01');
                $pub_year = date('Y', strtotime($pub_date));
                $formatted_date = date('F j, Y', strtotime($pub_date));
                $cat = !empty($ord['category']) ? $ord['category'] : 'Governance';
                $status = !empty($ord['status']) ? $ord['status'] : 'Enacted';

                // Status Badge styling
                $bg_color = '#DCFCE7';
                $text_color = '#166534';
                if ($status === 'Approved') {
                  $bg_color = '#DBEAFE';
                  $text_color = '#1E40AF';
                } elseif ($status === 'Draft' || $status === 'Pending') {
                  $bg_color = '#FEF3C7';
                  $text_color = '#92400E';
                } elseif ($status === 'Archived') {
                  $bg_color = '#F3F4F6';
                  $text_color = '#4B5563';
                }

                $description_js = addslashes(htmlspecialchars($ord['description'] ?? 'Manila City Ordinance Policy Document'));
                $title_js = addslashes(htmlspecialchars($ord['title']));
                $ord_no_js = addslashes(htmlspecialchars($ord_no));
                $cat_js = addslashes(htmlspecialchars($cat));
                $status_js = addslashes(htmlspecialchars($status));
                $date_js = addslashes(htmlspecialchars($formatted_date));
                $file_js = addslashes(htmlspecialchars($ord['file_path'] ?? ''));
                ?>
                <tr class="ordinance-row" data-category="<?= htmlspecialchars($cat) ?>"
                  data-year="<?= htmlspecialchars($pub_year) ?>"
                  style="border-bottom: 1px solid #F1F5F9; font-size: 0.96rem; color: #1E293B;">
                  <td style="padding: 22px 30px; font-weight: 700; color: #0F172A; white-space: nowrap;">
                    <?= htmlspecialchars($ord_no) ?>
                  </td>
                  <td style="padding: 22px 30px; font-weight: 600; font-size: 1rem;">
                    <?= htmlspecialchars($ord['title']) ?>
                  </td>
                  <td style="padding: 22px 30px; color: #475569; white-space: nowrap;">
                    <?= htmlspecialchars($cat) ?>
                  </td>
                  <td style="padding: 22px 30px; color: #475569; white-space: nowrap;">
                    <?= htmlspecialchars($pub_year) ?>
                  </td>
                  <td style="padding: 22px 30px; white-space: nowrap;">
                    <span
                      style="display: inline-block; padding: 5px 14px; background: <?= $bg_color ?>; color: <?= $text_color ?>; border-radius: 50px; font-size: 0.82rem; font-weight: 700;">
                      <?= htmlspecialchars($status) ?>
                    </span>
                  </td>
                  <td style="padding: 22px 30px; text-align: center; white-space: nowrap;">
                    <button
                      onclick="openDetailsModal('<?= $ord_no_js ?>', '<?= $title_js ?>', '<?= $date_js ?>', '<?= $cat_js ?>', '<?= $status_js ?>', '<?= $description_js ?>', '<?= $file_js ?>')"
                      style="padding: 8px 24px; background: #FFFFFF; border: 1.5px solid #CBD5E1; color: #0F172A; border-radius: 8px; font-size: 0.88rem; font-weight: 700; cursor: pointer; transition: all 0.2s ease;">
                      View
                    </button>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

  </div>

  <!-- FOOTER SECTION -->
  <footer
    style="background: #0B1B3D; color: #FFFFFF; padding: 20px 40px 12px 40px; border-top: 2px solid #D4AF37; width: 100%; box-sizing: border-box;">
    <div style="width: 100%; margin: 0 auto;">
      <!-- Grid 4 Columns -->
      <div
        style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 24px; margin-bottom: 14px;">

        <!-- Column 1: Brand Info -->
        <div>
          <h3
            style="font-family: 'Outfit', sans-serif; font-size: 1.08rem; font-weight: 800; color: #FFFFFF; margin: 0 0 6px 0;">
            Manila City Hall Portal</h3>
          <p style="font-size: 0.84rem; color: rgba(255,255,255,0.75); line-height: 1.5; margin: 0; max-width: 440px;">
            Legislative Information System — A modern digital platform supporting evidence-based policymaking,
            transparent public ordinance access, and municipal document archiving.
          </p>
        </div>

        <!-- Column 2: Quick Links -->
        <div>
          <h4
            style="font-family: 'Outfit', sans-serif; font-size: 0.92rem; font-weight: 700; color: #D4AF37; margin: 0 0 8px 0;">
            Quick Links</h4>
          <ul
            style="list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 5px; font-size: 0.85rem;">
            <li><a href="welcome.php" style="color: rgba(255,255,255,0.85); text-decoration: none;">Home</a></li>
            <li><a href="about.php" style="color: rgba(255,255,255,0.85); text-decoration: none;">About System</a></li>
            <li><a href="public_ordinances.php" style="color: rgba(255,255,255,0.85); text-decoration: none;">Public
                Ordinances</a></li>
            <li><a href="contact.php" style="color: rgba(255,255,255,0.85); text-decoration: none;">Contact Offices</a>
            </li>
          </ul>
        </div>

        <!-- Column 3: Portals & Legal -->
        <div>
          <h4
            style="font-family: 'Outfit', sans-serif; font-size: 0.92rem; font-weight: 700; color: #D4AF37; margin: 0 0 8px 0;">
            Portals &amp; Legal</h4>
          <ul
            style="list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 5px; font-size: 0.85rem;">
            <li><a href="welcome.php?login=1" style="color: rgba(255,255,255,0.85); text-decoration: none;">Sign In</a>
            </li>
            <li><a href="about.php" style="color: rgba(255,255,255,0.85); text-decoration: none;">Privacy Policy</a>
            </li>
            <li><a href="about.php" style="color: rgba(255,255,255,0.85); text-decoration: none;">Terms of Use</a></li>
          </ul>
        </div>

        <!-- Column 4: Contact Information -->
        <div>
          <h4
            style="font-family: 'Outfit', sans-serif; font-size: 0.92rem; font-weight: 700; color: #D4AF37; margin: 0 0 8px 0;">
            Contact Information</h4>
          <div
            style="font-size: 0.85rem; color: rgba(255,255,255,0.85); display: flex; flex-direction: column; gap: 5px;">
            <div><i class="bi bi-geo-alt-fill me-1" style="color: #D4AF37;"></i> Manila City Hall, Ermita, Manila</div>
            <div><i class="bi bi-telephone-fill me-1" style="color: #D4AF37;"></i> +63 (2) 8527-0909</div>
            <div><i class="bi bi-envelope-fill me-1" style="color: #D4AF37;"></i> legislative@manila.gov.ph</div>
          </div>
        </div>

      </div>

      <!-- Bottom Sub-footer -->
      <div
        style="border-top: 1px solid rgba(255,255,255,0.12); padding-top: 10px; display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; font-size: 0.78rem; color: rgba(255,255,255,0.65); gap: 10px;">
        <div>Copyright &copy; <?php echo date('Y'); ?> Manila City Hall. All Rights Reserved.</div>
        <div>Designed for City Council Legislative Research Offices</div>
      </div>
    </div>
  </footer>

  <!-- ORDINANCE DETAILS MODAL -->
  <div class="modal-overlay" id="detailsModal">
    <div class="modal-card">
      <button class="modal-close-btn" onclick="closeDetailsModal()">&times;</button>
      <div style="font-size: 0.85rem; font-weight: 700; color: #D4AF37; text-transform: uppercase; margin-bottom: 6px;"
        id="modalNum"></div>
      <h3 style="font-family: 'Outfit', sans-serif; font-size: 1.3rem; color: #0B1B3D; margin-bottom: 12px;"
        id="modalTitle"></h3>
      <div style="font-size: 0.9rem; color: var(--text-muted); margin-bottom: 16px;">
        <span style="margin-right: 15px;"><i class="bi bi-calendar3"></i> <span id="modalDate"></span></span>
        <span><i class="bi bi-tag-fill"></i> <span id="modalCategory"></span></span>
      </div>
      <p style="font-size: 0.95rem; color: var(--text-dark); line-height: 1.6; margin-bottom: 20px;" id="modalDesc"></p>
      <div style="display: flex; gap: 12px;">
        <a href="#" id="modalPdfBtn" onclick="alert('Downloading official ordinance PDF file...'); return false;"
          class="btn-hero-gold"
          style="padding: 10px 20px; font-size: 0.9rem; text-decoration: none; display: inline-flex; align-items: center; gap: 6px;">
          <i class="bi bi-file-earmark-pdf-fill"></i> Download PDF
        </a>
        <button onclick="closeDetailsModal()"
          style="padding: 10px 20px; background: #F1F5F9; border: 1px solid #CBD5E1; color: #475569; border-radius: 8px; font-weight: 600; cursor: pointer;">Close</button>
      </div>
    </div>
  </div>

  <script src="../assets/js/welcome.js"></script>
  <script src="../assets/js/login.js?v=2.5"></script>
</body>

</html>