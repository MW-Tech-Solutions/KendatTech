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

// Admin Display Name & Initials matching mockup
$adminFullName = !empty($user['full_name']) ? (string)$user['full_name'] : 'Muhammad Mukhtar';
$adminRoleName = !empty($user['role']) ? ucfirst(str_replace('_', ' ', (string)$user['role'])) : 'Super Admin';
$nameParts = explode(' ', trim($adminFullName));
$initials = '';
if (count($nameParts) >= 2) {
    $initials = strtoupper(substr($nameParts[0], 0, 1) . substr($nameParts[count($nameParts) - 1], 0, 1));
} else {
    $initials = strtoupper(substr($adminFullName, 0, 2));
}
$adminDb = get_db();
$pendingNotifCount = (int)$adminDb->query("SELECT COUNT(*) FROM appointments WHERE status = 'pending'")->fetchColumn();
$unreadMsgCount = (int)$adminDb->query("SELECT COUNT(*) FROM contact_messages WHERE status = 'new'")->fetchColumn();
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kendat Admin - <?php echo htmlspecialchars($companyName); ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,300..800;1,300..800&family=Inter:ital,opsz,wght@0,14..32,100..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap-grid.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?php echo $baseUrl; ?>assets/css/style.css?v=<?php echo file_exists(__DIR__ . '/../../assets/css/style.css') ? filemtime(__DIR__ . '/../../assets/css/style.css') : time(); ?>">
    <!-- Chart.js for High-Impact Enterprise Visualizations -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
    window.toggleAdminSidebar = function(e) {
        if (e) {
            e.preventDefault();
            e.stopPropagation();
        }
        var layout = document.getElementById('adminLayout') || document.querySelector('.admin-layout');
        var sb = document.getElementById('adminSidebar');
        var bd = document.getElementById('adminSidebarBackdrop');
        var isMobile = window.innerWidth <= 1024;

        if (isMobile) {
            if (sb) {
                var isOpen = sb.classList.toggle('open');
                if (bd) bd.classList.toggle('active', isOpen);
            }
        } else {
            if (layout) {
                layout.classList.toggle('sidebar-collapsed');
            }
        }
    };
    window.closeAdminSidebarMobile = function() {
        var sb = document.getElementById('adminSidebar');
        var bd = document.getElementById('adminSidebarBackdrop');
        if (sb) sb.classList.remove('open');
        if (bd) bd.classList.remove('active');
    };
    </script>
</head>
<body class="admin-body">
    <div class="admin-layout" id="adminLayout">
        <?php require_once __DIR__ . '/admin_sidebar.php'; ?>
        <section class="admin-main">
            <!-- Modernized White Topbar matching Mockup -->
            <header class="admin-top-bar">
                <div class="admin-top-left">
                    <button class="admin-sidebar-toggle" id="adminSidebarToggleBtn" type="button" aria-label="Toggle Navigation" onclick="toggleAdminSidebar(event)">
                        <?php echo render_icon('Menu', 20); ?>
                    </button>
                    <!-- Global Search Bar with Ctrl+K shortcut -->
                    <div class="admin-search-wrapper" onclick="openAdminSearchModal()">
                        <?php echo render_icon('Search', 17); ?>
                        <span class="search-placeholder">Search anything...</span>
                        <div class="search-badge-kbd">
                            <span>Ctrl</span>
                            <span>K</span>
                        </div>
                    </div>
                </div>

                <div class="admin-top-right">
                    <!-- Notifications Trigger -->
                    <div class="admin-action-chip" id="notifDropdownTrigger" title="Notifications">
                        <?php echo render_icon('Bell', 19); ?>
                        <?php if ($pendingNotifCount > 0): ?>
                            <span class="chip-badge"><?php echo $pendingNotifCount; ?></span>
                        <?php endif; ?>
                    </div>

                    <!-- Messages Trigger -->
                    <div class="admin-action-chip" id="msgDropdownTrigger" title="Messages">
                        <?php echo render_icon('Mail', 19); ?>
                        <?php if ($unreadMsgCount > 0): ?>
                            <span class="chip-badge"><?php echo $unreadMsgCount; ?></span>
                        <?php endif; ?>
                    </div>

                    <!-- Theme Toggle Button -->
                    <div class="admin-action-chip theme-toggle" id="adminThemeToggle" title="Toggle Theme">
                        <?php echo render_icon('Sun', 19); ?>
                    </div>

                    <div class="admin-divider-v"></div>

                    <!-- User Profile Dropdown Pill -->
                    <div class="admin-user-menu-wrapper" id="adminUserMenuWrapper">
                        <div class="admin-user-profile-pill" id="adminUserMenuTrigger" role="button" tabindex="0" aria-haspopup="true" aria-expanded="false" title="Account Menu">
                            <div class="admin-user-avatar">
                                <?php echo htmlspecialchars($initials); ?>
                            </div>
                            <div class="admin-user-details">
                                <span class="user-name"><?php echo htmlspecialchars($adminFullName); ?></span>
                                <span class="user-role"><?php echo htmlspecialchars($adminRoleName); ?></span>
                            </div>
                            <?php echo render_icon('ChevronDown', 15); ?>
                        </div>

                        <!-- Dropdown Menu (Sibling of Trigger) -->
                        <div class="admin-user-dropdown" id="adminUserDropdown">
                            <div class="admin-dropdown-user-header">
                                <strong><?php echo htmlspecialchars($adminFullName); ?></strong>
                                <small><?php echo htmlspecialchars($adminRoleName); ?></small>
                            </div>
                            <div class="dropdown-divider"></div>
                            <a href="<?php echo $baseUrl; ?>admin/settings.php">
                                <?php echo render_icon('User', 15); ?> Profile &amp; Settings
                            </a>
                            <a href="<?php echo $baseUrl; ?>" target="_blank">
                                <?php echo render_icon('ExternalLink', 15); ?> Live Website
                            </a>
                            <div class="dropdown-divider"></div>
                            <a href="<?php echo $baseUrl; ?>logout.php" class="logout-link" id="adminLogoutLink" onclick="window.location.href='<?php echo $baseUrl; ?>logout.php'; return true;">
                                <?php echo render_icon('LogOut', 15); ?> Logout Session
                            </a>
                        </div>
                    </div>
                </div>
            </header>
