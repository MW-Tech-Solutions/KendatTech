<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/config/db.php';

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit("<h1>403 Forbidden</h1><p>Migration tools are available from CLI only. Run <code>php migrate_firebase.php</code> in terminal.</p>");
}

$messages = [];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $db = new Database();
        $pdo = $db->connect();
        
        $jsonInput = '';
        if (isset($_FILES['firebase_file']) && $_FILES['firebase_file']['error'] === UPLOAD_ERR_OK) {
            $jsonInput = file_get_contents($_FILES['firebase_file']['tmp_name']);
        } elseif (!empty($_POST['json_data'])) {
            $jsonInput = $_POST['json_data'];
        }
        
        if (empty($jsonInput)) {
            throw new Exception("Please upload a Firebase JSON export file or paste JSON data.");
        }
        
        $data = json_decode($jsonInput, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("Invalid JSON format: " . json_last_error_msg());
        }
        
        $migratedCount = 0;
        
        if (!empty($data['users']) && is_array($data['users'])) {
            $stmt = $pdo->prepare("INSERT INTO users (full_name, email, phone, password_hash, status) 
                VALUES (?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE full_name=VALUES(full_name), phone=VALUES(phone)");
            foreach ($data['users'] as $u) {
                $stmt->execute([
                    $u['full_name'] ?? $u['displayName'] ?? 'User',
                    strtolower(trim($u['email'] ?? '')),
                    $u['phone'] ?? $u['phoneNumber'] ?? '',
                    password_hash($u['password'] ?? '12345678', PASSWORD_DEFAULT),
                    $u['status'] ?? 'active'
                ]);
                $migratedCount++;
            }
            $messages[] = "Migrated users collection.";
        }

        if (!empty($data['services']) && is_array($data['services'])) {
            $stmt = $pdo->prepare("INSERT INTO services (title, short_description, full_description, icon, category, status, sort_order) 
                VALUES (?, ?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE short_description=VALUES(short_description)");
            foreach ($data['services'] as $idx => $s) {
                $stmt->execute([
                    $s['title'] ?? 'Service',
                    $s['short_description'] ?? '',
                    $s['full_description'] ?? '',
                    $s['icon'] ?? 'Globe',
                    $s['category'] ?? 'Software Development',
                    $s['status'] ?? 'active',
                    $s['sort_order'] ?? ($idx + 1)
                ]);
                $migratedCount++;
            }
            $messages[] = "Migrated services collection.";
        }

        if (!empty($data['projects']) && is_array($data['projects'])) {
            $stmt = $pdo->prepare("INSERT INTO projects (title, slug, category, short_description, full_description, features, technologies, demo_link, client_name, completion_date, status, featured) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE short_description=VALUES(short_description)");
            foreach ($data['projects'] as $p) {
                $slug = $p['slug'] ?? slugify($p['title'] ?? 'Project');
                $stmt->execute([
                    $p['title'] ?? 'Project',
                    $slug,
                    $p['category'] ?? 'General',
                    $p['short_description'] ?? '',
                    $p['full_description'] ?? '',
                    is_array($p['features'] ?? null) ? implode("\n", $p['features']) : ($p['features'] ?? ''),
                    is_array($p['technologies'] ?? null) ? implode(', ', $p['technologies']) : ($p['technologies'] ?? ''),
                    $p['demo_link'] ?? '#',
                    $p['client_name'] ?? '',
                    $p['completion_date'] ?? date('Y-m-d'),
                    $p['status'] ?? 'completed',
                    !empty($p['featured']) ? 1 : 0
                ]);
                $migratedCount++;
            }
            $messages[] = "Migrated projects collection.";
        }

        if (!empty($data['testimonials']) && is_array($data['testimonials'])) {
            $stmt = $pdo->prepare("INSERT INTO testimonials (client_name, position_company, message, rating, status) 
                VALUES (?, ?, ?, ?, ?)");
            foreach ($data['testimonials'] as $t) {
                $stmt->execute([
                    $t['client_name'] ?? 'Client',
                    $t['position_company'] ?? '',
                    $t['message'] ?? '',
                    $t['rating'] ?? 5,
                    $t['status'] ?? 'active'
                ]);
                $migratedCount++;
            }
            $messages[] = "Migrated testimonials collection.";
        }
        
        $messages[] = "Data migration completed! Total items processed: {$migratedCount}.";

    } catch (Throwable $e) {
        $errors[] = $e->getMessage();
    }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Kendat Firebase to MySQL Migration</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@700;800;900&family=Open+Sans:wght@400;500;600;700;800&family=Poppins:wght@500;600;700;800;900&display=swap" rel="stylesheet">
  <style>
    body { margin:0; background:#050816; color:#eef7ff; font-family:'Open Sans',Segoe UI,Arial,sans-serif; }
    .wrap { min-height:100vh; display:grid; place-items:center; padding:28px; background:radial-gradient(circle at 15% 10%,rgba(34,211,238,.16),transparent 28%),radial-gradient(circle at 85% 15%,rgba(139,92,246,.18),transparent 30%); }
    .card { width:min(720px,100%); border:1px solid rgba(148,163,184,.18); border-radius:12px; background:linear-gradient(180deg,rgba(16,25,46,.88),rgba(5,8,22,.9)); box-shadow:0 28px 90px rgba(0,0,0,.42); padding:28px; }
    h1 { margin:0 0 8px; font-size:clamp(24px,4vw,36px); font-family:'Montserrat',Segoe UI,Arial,sans-serif; font-weight:700; }
    p { color:#9fb0c8; line-height:1.6; }
    ul { display:grid; gap:10px; padding:0; margin:18px 0; list-style:none; }
    li { padding:10px 12px; border:1px solid rgba(148,163,184,.16); border-radius:8px; background:rgba(5,8,22,.55); color:#34d399; }
    .error { border-color:rgba(251,113,133,.45); color:#fecdd3; }
    label { display:grid; gap:8px; color:#9fb0c8; margin-top:14px; }
    textarea, input[type=file] { width:100%; color:#eef7ff; background:rgba(5,8,22,.72); border:1px solid rgba(148,163,184,.2); border-radius:8px; padding:10px; }
    textarea { min-height:140px; }
    button, a.btn { border:0; background:linear-gradient(135deg,#22d3ee,#8b5cf6); color:#06111f; padding:12px 18px; border-radius:8px; font-family:'Poppins',Segoe UI,Arial,sans-serif; font-weight:900; cursor:pointer; text-decoration:none; display:inline-block; margin-top:14px; }
  </style>
</head>
<body>
  <main class="wrap">
    <section class="card">
      <h1>Firebase to MySQL Migration Tool</h1>
      <p>Upload your Firebase JSON export file or paste raw JSON data to extract collections into local MySQL database.</p>

      <?php if ($errors): ?>
        <ul><?php foreach ($errors as $err): ?><li class="error"><?php echo htmlspecialchars($err); ?></li><?php endforeach; ?></ul>
      <?php endif; ?>

      <?php if ($messages): ?>
        <ul><?php foreach ($messages as $msg): ?><li><?php echo htmlspecialchars($msg); ?></li><?php endforeach; ?></ul>
      <?php endif; ?>

      <form method="post" enctype="multipart/form-data">
        <label>Upload Firebase JSON file
          <input type="file" name="firebase_file" accept=".json">
        </label>
        <label>Or paste JSON payload
          <textarea name="json_data" placeholder='{"users": [], "services": [], "projects": []}'></textarea>
        </label>
        <button type="submit">Start Migration</button>
        <a href="index.php" class="btn" style="background:transparent; color:#22d3ee; border:1px solid #22d3ee; margin-left:10px;">Return Home</a>
      </form>
    </section>
  </main>
</body>
</html>
