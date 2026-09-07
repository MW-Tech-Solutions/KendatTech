<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/env.php';
require_once __DIR__ . '/config/db.php';

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit("<h1>403 Forbidden</h1><p>Setup is available from CLI only. Run <code>php setup.php</code> in terminal.</p>");
}

$messages = [];
$errors = [];
$startedAt = microtime(true);

try {
    $db = new Database();
    $server = $db->connectServer();
    $databaseName = $db->databaseName();
    
    $server->exec("CREATE DATABASE IF NOT EXISTS `{$databaseName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $messages[] = "Database `{$databaseName}` created or verified.";

    $pdo = $db->connect();
    
    $sqlFile = __DIR__ . '/database/kendat_integrated_services.sql';
    if (file_exists($sqlFile)) {
        $sql = file_get_contents($sqlFile);
        $pdo->exec($sql);
        $messages[] = "Database schema and seed data loaded from `kendat_integrated_services.sql`.";
    } else {
        $errors[] = "SQL file `database/kendat_integrated_services.sql` not found.";
    }

    $adminHash = password_hash('1234567890Aa@', PASSWORD_DEFAULT);
    $adminStmt = $pdo->prepare("INSERT INTO admins (full_name, email, password_hash, role, status) 
        VALUES ('Muhammad Mukhtar', 'muhdmukhtar2019@gmail.com', ?, 'super_admin', 'active') 
        ON DUPLICATE KEY UPDATE full_name = VALUES(full_name), password_hash = VALUES(password_hash), role = VALUES(role), status = VALUES(status)");
    $adminStmt->execute([$adminHash]);
    $messages[] = "Super admin ready: muhdmukhtar2019@gmail.com (Password: 1234567890Aa@)";

} catch (Throwable $e) {
    $errors[] = $e->getMessage();
}

$duration = number_format((microtime(true) - $startedAt) * 1000, 0);
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Kendat Database Setup</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@700;800;900&family=Open+Sans:wght@400;500;600;700;800&family=Poppins:wght@500;600;700;800;900&display=swap" rel="stylesheet">
  <style>
    body { margin:0; background:#050816; color:#eef7ff; font-family:'Open Sans',Segoe UI,Arial,sans-serif; }
    .wrap { min-height:100vh; display:grid; place-items:center; padding:28px; background:radial-gradient(circle at 15% 10%,rgba(34,211,238,.16),transparent 28%),radial-gradient(circle at 85% 15%,rgba(139,92,246,.18),transparent 30%); }
    .card { width:min(720px,100%); border:1px solid rgba(148,163,184,.18); border-radius:12px; background:linear-gradient(180deg,rgba(16,25,46,.88),rgba(5,8,22,.9)); box-shadow:0 28px 90px rgba(0,0,0,.42); padding:28px; }
    h1 { margin:0 0 8px; font-size:clamp(28px,5vw,42px); font-family:'Montserrat',Segoe UI,Arial,sans-serif; font-weight:700; }
    p { color:#9fb0c8; line-height:1.6; }
    .status { display:inline-flex; align-items:center; gap:10px; padding:8px 12px; border-radius:999px; background:rgba(34,211,238,.1); color:#22d3ee; font-weight:800; }
    .dot { width:9px; height:9px; border-radius:50%; background:#34d399; box-shadow:0 0 18px #34d399; }
    ul { display:grid; gap:10px; padding:0; margin:24px 0; list-style:none; }
    li { padding:12px 14px; border:1px solid rgba(148,163,184,.16); border-radius:8px; background:rgba(5,8,22,.55); }
    .error { border-color:rgba(251,113,133,.45); color:#fecdd3; }
    .actions { display:flex; flex-wrap:wrap; gap:12px; margin-top:20px; }
    a { color:#06111f; text-decoration:none; background:linear-gradient(135deg,#22d3ee,#8b5cf6); padding:12px 16px; border-radius:8px; font-family:'Poppins',Segoe UI,Arial,sans-serif; font-weight:900; }
    code { color:#34d399; }
  </style>
</head>
<body>
  <main class="wrap">
    <section class="card">
      <span class="status"><span class="dot"></span><?php echo empty($errors) ? 'Setup complete' : 'Setup attention required'; ?></span>
      <h1>Kendat Integrated Services Database Setup</h1>
      <p>Initializes MySQL database, executes schema tables, seeds default content, and sets up super admin credentials.</p>
      <?php if ($errors): ?>
        <ul><?php foreach ($errors as $error): ?><li class="error"><?php echo htmlspecialchars($error); ?></li><?php endforeach; ?></ul>
      <?php else: ?>
        <ul><?php foreach ($messages as $message): ?><li><?php echo htmlspecialchars($message); ?></li><?php endforeach; ?></ul>
        <p>Admin Email: <code>muhdmukhtar2019@gmail.com</code><br>Password: <code>1234567890Aa@</code><br>Time taken: <?php echo $duration; ?>ms.</p>
      <?php endif; ?>
      <div class="actions">
        <a href="index.php">Go to Website</a>
        <a href="admin-login.php">Open Admin Login</a>
      </div>
    </section>
  </main>
</body>
</html>
