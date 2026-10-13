<?php
declare(strict_types=1);

$currentAdminPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$baseUrl = get_base_url();

$adminFullName = !empty($user['full_name']) ? (string)$user['full_name'] : 'Muhammad Mukhtar';
$nameParts = explode(' ', trim($adminFullName));
$initials = '';
if (count($nameParts) >= 2) {
    $initials = strtoupper(substr($nameParts[0], 0, 1) . substr($nameParts[count($nameParts) - 1], 0, 1));
} else {
    $initials = strtoupper(substr($adminFullName, 0, 2));
}
$adminRoleName = !empty($user['role']) ? ucfirst(str_replace('_', ' ', (string)$user['role'])) : 'Super Admin';

// Comprehensive dynamic navigation links matching Kendat Admin
$adminLinks = [
    ['Dashboard', $baseUrl . 'admin/index.php', 'LayoutDashboard'],
    ['Appointments', $baseUrl . 'admin/appointments.php', 'CalendarCheck'],
    ['Project Requests', $baseUrl . 'admin/requests.php', 'ClipboardList'],
    ['Customers', $baseUrl . 'admin/users.php', 'Users'],
    ['Services', $baseUrl . 'admin/services.php', 'Briefcase'],
    ['Projects', $baseUrl . 'admin/projects.php', 'Code2'],
    ['AI Solutions', $baseUrl . 'admin/ai-solutions.php', 'Sparkles'],
    ['Messages', $baseUrl . 'admin/messages.php', 'Mail'],
    ['Team Members', $baseUrl . 'admin/team.php', 'UserCheck'],
    ['Testimonials', $baseUrl . 'admin/testimonials.php', 'MessageSquareQuote'],
    ['Blog Posts', $baseUrl . 'admin/blog.php', 'Newspaper'],
    ['Audit Logs', $baseUrl . 'admin/audit-logs.php', 'BarChart3'],
    ['Settings', $baseUrl . 'admin/settings.php', 'Settings'],
];
$settings = $settings ?? get_settings();
$companyName = !empty($settings['company_name']) ? (string)$settings['company_name'] : 'KendatTech';
$dynamicLogoUrl = !empty($settings['logo']) ? upload_asset_url($settings['logo']) : '';
?>
<aside class="admin-sidebar" id="adminSidebar">
    <!-- Brand Logo in Dark Green Sidebar (Dynamic from system_settings) -->
    <div class="admin-sidebar-brand">
        <a href="<?php echo $baseUrl; ?>admin/index.php" class="admin-brand-link" title="<?php echo htmlspecialchars($companyName); ?>">
            <?php if (!empty($dynamicLogoUrl)): ?>
                <span class="admin-brand-mark dynamic-logo-mark">
                    <img src="<?php echo htmlspecialchars($dynamicLogoUrl); ?>" alt="<?php echo htmlspecialchars($companyName); ?>" class="admin-brand-logo-img">
                </span>
            <?php else: ?>
                <span class="admin-brand-mark">
                    <svg width="32" height="32" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <rect width="32" height="32" rx="8" fill="#0B5D3B"/>
                        <path d="M9 7V25M9 16L18 7M9 16L18 25" stroke="#FFFFFF" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M19 16L24 7M19 16L24 25" stroke="#10B981" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </span>
            <?php endif; ?>
            <span class="admin-brand-text">
                Kendat<span class="brand-text-accent">Tech</span>
            </span>
        </a>
        <button type="button" class="admin-sidebar-close-btn" onclick="closeAdminSidebarMobile()" aria-label="Close Sidebar" title="Close Sidebar">
            <?php echo render_icon('X', 18); ?>
        </button>
    </div>

    <!-- Navigation Menu (Rounded Pill Highlights) -->
    <nav class="admin-sidebar-nav">
        <?php foreach ($adminLinks as [$label, $href, $icon]): 
            $linkPath = parse_url($href, PHP_URL_PATH);
            $isActive = ($linkPath === $baseUrl . 'admin/index.php') 
                ? ($currentAdminPath === $linkPath || $currentAdminPath === $baseUrl . 'admin/') 
                : ($currentAdminPath === $linkPath);
        ?>
            <a class="admin-sidebar-nav-item <?php echo $isActive ? 'active' : ''; ?>" href="<?php echo htmlspecialchars($href); ?>">
                <span class="nav-icon"><?php echo render_icon($icon, 18); ?></span>
                <span class="nav-label"><?php echo htmlspecialchars($label); ?></span>
            </a>
        <?php endforeach; ?>
    </nav>

    <!-- Bottom Motivational Card (Exact Match to Mockup) -->
    <div class="admin-sidebar-quote-card">
        <div class="quote-card-icon">
            <?php echo render_icon('Lightbulb', 18); ?>
        </div>
        <div class="quote-card-content">
            <strong>Better Solutions.</strong>
            <span>A Smarter Tomorrow.</span>
        </div>
    </div>

    <!-- Bottom User Profile Pill (Exact Match to Mockup) -->
    <div class="admin-sidebar-user-footer">
        <div class="sidebar-user-avatar">
            <?php echo htmlspecialchars($initials); ?>
        </div>
        <div class="sidebar-user-meta">
            <strong class="sidebar-user-name"><?php echo htmlspecialchars($adminFullName); ?></strong>
            <span class="sidebar-user-role"><?php echo htmlspecialchars($adminRoleName); ?></span>
        </div>
        <span class="sidebar-user-chevron">
            <?php echo render_icon('ChevronDown', 14); ?>
        </span>
    </div>
</aside>
