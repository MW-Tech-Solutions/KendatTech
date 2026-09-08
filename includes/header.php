<?php
declare(strict_types=1);

require_once __DIR__ . '/headers.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/functions.php';

$settings = get_settings();
$companyName = $settings['company_name'] ?? 'Kendat Integrated Services';
$websiteName = $settings['website_name'] ?? $companyName;
$logoPath = upload_asset_url($settings['logo'] ?? '');
$faviconPath = upload_asset_url($settings['favicon'] ?? '');

$currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$baseUrl = get_base_url();

$navItems = [
    ['Home', $baseUrl . 'index.php'],
    ['About', $baseUrl . 'about.php'],
    ['Services', $baseUrl . 'services.php'],
    ['Projects', $baseUrl . 'projects.php'],
    ['AI Solutions', $baseUrl . 'ai-solutions.php'],
    ['Book Appointment', $baseUrl . 'book-appointment.php'],
    ['Contact', $baseUrl . 'contact.php'],
];

$currentUser = get_current_user_data();
$currentPageFile = basename($_SERVER['PHP_SELF'] ?? '');
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($websiteName); ?></title>
    <?php if ($faviconPath): ?>
        <link id="dynamic-favicon" rel="icon" href="<?php echo htmlspecialchars($faviconPath); ?>">
    <?php else: ?>
        <link id="dynamic-favicon" rel="icon" href="<?php echo $baseUrl; ?>favicon.ico">
    <?php endif; ?>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@700;800;900&family=Open+Sans:wght@400;500;600;700;800&family=Poppins:wght@500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap-grid.min.css">
    <link rel="stylesheet" href="<?php echo $baseUrl; ?>assets/css/style.css?v=<?php echo file_exists(__DIR__ . '/../assets/css/style.css') ? filemtime(__DIR__ . '/../assets/css/style.css') : time(); ?>">
<?php
$isLightHeaderPage = in_array($currentPageFile, ['login.php', 'register.php', 'admin-login.php']);
?>
</head>
<body class="<?php echo $isLightHeaderPage ? 'light-surface-page' : ''; ?>">
    <!-- High-Impact Enterprise Preloader Screen -->
    <div class="kendat-preloader" id="kendatPreloader">
        <div class="preloader-content">
            <div class="preloader-logo-wrap">
                <?php if ($logoPath): ?>
                    <img src="<?php echo htmlspecialchars($logoPath); ?>" alt="<?php echo htmlspecialchars($companyName); ?>" class="preloader-logo-img">
                <?php else: ?>
                    <span class="preloader-logo-text">KENDAT TECH</span>
                <?php endif; ?>
            </div>
            <!-- 3 Dotted Animated Loading Indicator Lines -->
            <div class="preloader-dots-row">
                <span class="dot-line dot-1"></span>
                <span class="dot-line dot-2"></span>
                <span class="dot-line dot-3"></span>
            </div>
        </div>
    </div>
    <script>
    (function() {
        var hidePreloader = function() {
            var el = document.getElementById('kendatPreloader');
            if (el && !el.classList.contains('is-loaded')) {
                el.classList.add('is-loaded');
                setTimeout(function() { el.style.display = 'none'; }, 650);
            }
        };
        if (document.readyState === 'complete') {
            hidePreloader();
        } else {
            window.addEventListener('load', function() { setTimeout(hidePreloader, 200); });
            setTimeout(hidePreloader, 1500);
        }
    })();
    </script>

    <!-- Astonesoft-Style Custom Interactive Cursor Elements -->
    <div class="custom-cursor-dot" id="customCursorDot"></div>
    <div class="custom-cursor-ring" id="customCursorRing"></div>

    <header class="site-header <?php echo $isLightHeaderPage ? 'light-header' : ''; ?>">
        <a class="brand" href="<?php echo $baseUrl; ?>index.php" aria-label="<?php echo htmlspecialchars($companyName); ?>">
            <!-- <span class="brand-mark-wrapper"> -->
                <?php if ($logoPath): ?>
                    <img src="<?php echo htmlspecialchars($logoPath); ?>" alt="<?php echo htmlspecialchars($companyName); ?>" class="brand-logo-img">
                <?php else: ?>
                    <span class="brand-logo-text">kendat tech</span>
                <?php endif; ?>
            <!-- </span> -->
        </a>

        <button class="icon-btn menu-toggle" type="button" aria-label="Open menu">
            <?php echo render_icon('Menu', 22); ?>
        </button>

        <nav class="nav">
            <?php foreach ($navItems as [$label, $href]): 
                $targetFile = basename($href);
                if ($targetFile === 'index.php' || empty($targetFile)) {
                    $isActive = ($currentPageFile === 'index.php' || empty($currentPageFile));
                } else {
                    $isActive = ($currentPageFile === $targetFile);
                }
            ?>
                <a class="nav-link <?php echo $isActive ? 'active' : ''; ?>" href="<?php echo htmlspecialchars($href); ?>">
                    <span><?php echo htmlspecialchars($label); ?></span>
                </a>
            <?php endforeach; ?>

            <?php if ($currentUser): ?>
                <a class="nav-link dashboard-link" href="<?php echo ($currentUser['role'] === 'user') ? ($baseUrl . 'dashboard.php') : ($baseUrl . 'admin/index.php'); ?>">
                    <span>Dashboard</span>
                </a>
                <a class="link-button" href="<?php echo $baseUrl; ?>logout.php">Logout</a>
            <?php else: ?>
                <a class="pill" href="<?php echo $baseUrl; ?>login.php">
                    <?php echo render_icon('UserRound', 16); ?>Login/Register
                </a>
            <?php endif; ?>
        </nav>
    </header>
