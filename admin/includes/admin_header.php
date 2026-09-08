<?php
declare(strict_types=1);

require_once __DIR__ . '/../../includes/headers.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/csrf.php';
require_once __DIR__ . '/../../includes/audit_logger.php';

require_admin('admin-login.php');

$settings = get_settings();
$companyName = $settings['company_name'] ?? 'Kendat Integrated Services';
$baseUrl = get_base_url();
$user = get_current_user_data();
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kendat Admin - <?php echo htmlspecialchars($companyName); ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@700;800;900&family=Open+Sans:wght@400;500;600;700;800&family=Poppins:wght@500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap-grid.min.css">
    <link rel="stylesheet" href="<?php echo $baseUrl; ?>assets/css/style.css?v=<?php echo file_exists(__DIR__ . '/../../assets/css/style.css') ? filemtime(__DIR__ . '/../../assets/css/style.css') : time(); ?>">
</head>
<body>
    <!-- Astonesoft-Style Custom Interactive Cursor Elements -->
    <div class="custom-cursor-dot" id="customCursorDot"></div>
    <div class="custom-cursor-ring" id="customCursorRing"></div>
    <div class="admin-layout">
        <?php require_once __DIR__ . '/admin_sidebar.php'; ?>
        <section class="admin-main">
            <!-- Top Admin Header Bar -->
            <header class="admin-top-bar">
                <div class="admin-top-left">
                    <button class="admin-sidebar-toggle icon-btn" type="button" aria-label="Toggle Navigation">
                        <?php echo render_icon('Menu', 20); ?>
                    </button>
                    <span class="admin-status-pill">
                        <span class="status-dot-green"></span>
                        <span>ENTERPRISE CONSOLE v4.2 • SYSTEM ONLINE</span>
                    </span>
                </div>
                <div class="admin-top-right">
                    <a class="admin-top-btn" href="<?php echo $baseUrl; ?>" target="_blank" rel="noopener">
                        <?php echo render_icon('ExternalLink', 15); ?> Live Website
                    </a>
                    <a class="admin-top-btn logout" href="<?php echo $baseUrl; ?>logout.php">
                        <?php echo render_icon('LogOut', 15); ?> Logout
                    </a>
                </div>
            </header>
