<?php
declare(strict_types=1);

$currentAdminPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$baseUrl = get_base_url();

$adminLinks = [
    ['Dashboard', $baseUrl . 'admin/index.php', 'LayoutDashboard'],
    ['Services', $baseUrl . 'admin/services.php', 'Layers3'],
    ['Projects', $baseUrl . 'admin/projects.php', 'PanelsTopLeft'],
    ['AI Solutions', $baseUrl . 'admin/ai-solutions.php', 'Bot'],
    ['Appointments', $baseUrl . 'admin/appointments.php', 'CalendarCheck'],
    ['Project Requests', $baseUrl . 'admin/requests.php', 'ClipboardList'],
    ['Users', $baseUrl . 'admin/users.php', 'Users'],
    ['Send Email', $baseUrl . 'admin/send-mail.php', 'Send'],
    ['Messages', $baseUrl . 'admin/messages.php', 'Mail'],
    ['Testimonials', $baseUrl . 'admin/testimonials.php', 'Star'],
    ['Blog', $baseUrl . 'admin/blog.php', 'Newspaper'],
    ['Audit Logs', $baseUrl . 'admin/audit-logs.php', 'ShieldAlert'],
    ['Settings', $baseUrl . 'admin/settings.php', 'Settings'],
];
?>
<aside class="admin-sidebar">
    <div class="admin-brand-header">
        <span class="admin-brand-logo">KENDAT ADMIN</span>
        <small class="admin-brand-tag">PORTAL ENGINE v4.2</small>
    </div>

    <div class="admin-identity">
        <div class="admin-avatar-box">
            <?php echo render_icon('ShieldCheck', 22); ?>
        </div>
        <div class="admin-identity-info">
            <strong><?php echo htmlspecialchars($user['full_name'] ?? 'Administrator'); ?></strong>
            <span class="role-badge"><?php echo htmlspecialchars(strtoupper((string)($user['role'] ?? 'SUPER_ADMIN'))); ?></span>
        </div>
    </div>

    <nav class="admin-nav-menu">
        <?php foreach ($adminLinks as [$label, $href, $icon]): 
            $linkPath = parse_url($href, PHP_URL_PATH);
            $isActive = ($linkPath === $baseUrl . 'admin/index.php') ? ($currentAdminPath === $linkPath || $currentAdminPath === $baseUrl . 'admin/') : (strpos($currentAdminPath, $linkPath) === 0);
        ?>
            <a class="admin-nav-item <?php echo $isActive ? 'active' : ''; ?>" href="<?php echo htmlspecialchars($href); ?>">
                <?php echo render_icon($icon, 18); ?>
                <span><?php echo htmlspecialchars($label); ?></span>
            </a>
        <?php endforeach; ?>
    </nav>

    <div class="admin-sidebar-footer">
        <a class="admin-logout-btn" href="<?php echo $baseUrl; ?>logout.php">
            <?php echo render_icon('LogOut', 16); ?> Logout Session
        </a>
    </div>
</aside>
