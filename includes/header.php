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
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,300..800;1,300..800&family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap-grid.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?php echo $baseUrl; ?>assets/css/style.css?v=<?php echo file_exists(__DIR__ . '/../assets/css/style.css') ? filemtime(__DIR__ . '/../assets/css/style.css') : time(); ?>">
<?php
$isLightHeaderPage = in_array($currentPageFile, [
    'services.php',
    'ai-solutions.php',
    'book-appointment.php',
    'contact.php',
    'request-project.php',
    'about.php',
    'projects.php',
    'project-details.php',
    'login.php',
    'register.php',
    'admin-login.php',
    'forgot-password.php'
]);
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
            <?php if ($logoPath): ?>
                <img src="<?php echo htmlspecialchars($logoPath); ?>" alt="<?php echo htmlspecialchars($companyName); ?>" class="brand-logo-img">
            <?php else: ?>
                <span class="brand-logo-flex">
                    <svg width="44" height="44" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg" class="brand-k-icon">
                        <rect width="32" height="32" rx="9" fill="#ECFDF5"/>
                        <path d="M9 7V25M9 16L18 7M9 16L18 25" stroke="#0B5D3B" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M19 16L24 7M19 16L24 25" stroke="#10B981" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <span class="brand-logo-text">Kendat<span class="brand-logo-accent">Tech</span></span>
                </span>
            <?php endif; ?>
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
                <a class="kt-btn-green" href="<?php echo ($currentUser['role'] === 'user') ? ($baseUrl . 'dashboard.php') : ($baseUrl . 'admin/index.php'); ?>">
                    Dashboard
                </a>
                <a class="kt-nav-logout-btn" href="<?php echo $baseUrl; ?>logout.php">Logout</a>
            <?php else: ?>
                <a class="kt-btn-green" href="<?php echo $baseUrl; ?>login.php">
                    Login
                </a>
            <?php endif; ?>
        </nav>
    </header>
    <script>
    (function() {
        var header = document.querySelector('.site-header');
        if (!header) return;
        var checkScroll = function() {
            var st = window.scrollY || window.pageYOffset || document.documentElement.scrollTop || document.body.scrollTop || 0;
            if (st > 20) {
                header.classList.add('is-scrolled');
            } else {
                header.classList.remove('is-scrolled');
            }
        };
        window.addEventListener('scroll', checkScroll, { passive: true });
        document.addEventListener('scroll', checkScroll, { passive: true });
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', checkScroll);
        } else {
            checkScroll();
        }
    })();
    </script>
