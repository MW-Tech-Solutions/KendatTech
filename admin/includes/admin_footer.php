<?php
declare(strict_types=1);
?>
        </section>
    </div>

    <!-- Global Interactive Ctrl+K Search Modal -->
    <div class="admin-modal-backdrop" id="adminSearchModal" style="display:none;" onclick="if(event.target === this) closeAdminSearchModal();">
        <div class="admin-search-dialog">
            <div class="search-dialog-header">
                <?php echo render_icon('Search', 18); ?>
                <input type="text" id="adminSearchInput" placeholder="Type to search bookings, projects, clients, services..." oninput="filterAdminSearch(this.value)">
                <button type="button" class="search-dialog-close" onclick="closeAdminSearchModal()">Esc</button>
            </div>
            <div class="search-dialog-results" id="adminSearchResults">
                <div class="search-group-title">QUICK NAVIGATION</div>
                <a href="<?php echo $baseUrl; ?>admin/index.php" class="search-result-item">
                    <?php echo render_icon('LayoutDashboard', 16); ?>
                    <span>Dashboard Control Center</span>
                    <small>Overview</small>
                </a>
                <a href="<?php echo $baseUrl; ?>admin/appointments.php" class="search-result-item">
                    <?php echo render_icon('Calendar', 16); ?>
                    <span>Client Bookings &amp; Consultations</span>
                    <small>Appointments</small>
                </a>
                <a href="<?php echo $baseUrl; ?>admin/requests.php" class="search-result-item">
                    <?php echo render_icon('ClipboardList', 16); ?>
                    <span>Project Requests &amp; Estimates</span>
                    <small>Requests</small>
                </a>
                <a href="<?php echo $baseUrl; ?>admin/projects.php" class="search-result-item">
                    <?php echo render_icon('Code2', 16); ?>
                    <span>Enterprise Software Portfolio</span>
                    <small>Projects</small>
                </a>
                <a href="<?php echo $baseUrl; ?>admin/users.php" class="search-result-item">
                    <?php echo render_icon('Users', 16); ?>
                    <span>Customer &amp; Client Accounts</span>
                    <small>Users</small>
                </a>
                <a href="<?php echo $baseUrl; ?>admin/settings.php" class="search-result-item">
                    <?php echo render_icon('Settings', 16); ?>
                    <span>System Settings &amp; Configuration</span>
                    <small>Settings</small>
                </a>
            </div>
        </div>
    </div>

<?php
$footerDb = get_db();
$footerNotifs = $footerDb->query("SELECT * FROM appointments ORDER BY id DESC LIMIT 4")->fetchAll();
$footerMsgs = $footerDb->query("SELECT * FROM contact_messages ORDER BY id DESC LIMIT 4")->fetchAll();
$pendingCount = (int)$footerDb->query("SELECT COUNT(*) FROM appointments WHERE status = 'pending'")->fetchColumn();
$newMsgCount = (int)$footerDb->query("SELECT COUNT(*) FROM contact_messages WHERE status = 'new'")->fetchColumn();
?>
    <!-- Notification Dropdown Popover -->
    <div class="admin-popover-menu" id="notifPopover" style="display:none;">
        <div class="popover-head">
            <strong>System Notifications</strong>
            <span class="badge-count"><?php echo $pendingCount; ?> Pending</span>
        </div>
        <div class="popover-items">
            <?php if (empty($footerNotifs)): ?>
                <div style="padding: 18px; text-align: center; color: #64748b; font-size: 13px;">No new notifications</div>
            <?php else: ?>
                <?php foreach ($footerNotifs as $fn): 
                    $isPending = strtolower($fn['status']) === 'pending';
                    $iconColor = $isPending ? '#FEF3C7' : '#EDFDF5';
                    $textColor = $isPending ? '#D97706' : '#0B5D3B';
                    $iconName = $isPending ? 'Clock3' : 'CalendarCheck';
                ?>
                    <a href="<?php echo $baseUrl; ?>admin/appointments.php" class="popover-item <?php echo $isPending ? 'unread' : ''; ?>" style="text-decoration:none; color:inherit;">
                        <div class="popover-icon" style="background:<?php echo $iconColor; ?>; color:<?php echo $textColor; ?>;">
                            <?php echo render_icon($iconName, 15); ?>
                        </div>
                        <div class="popover-text">
                            <strong>Booking: <?php echo htmlspecialchars($fn['full_name']); ?></strong>
                            <small><?php echo date('d M, h:i A', strtotime($fn['created_at'])); ?> &bull; <?php echo htmlspecialchars($fn['appointment_type']); ?></small>
                        </div>
                    </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <div class="popover-footer">
            <a href="<?php echo $baseUrl; ?>admin/appointments.php">View All Appointments &rarr;</a>
        </div>
    </div>

    <!-- Messages Dropdown Popover -->
    <div class="admin-popover-menu" id="msgPopover" style="display:none;">
        <div class="popover-head">
            <strong>Client Inquiries</strong>
            <span class="badge-count"><?php echo $newMsgCount; ?> New</span>
        </div>
        <div class="popover-items">
            <?php if (empty($footerMsgs)): ?>
                <div style="padding: 18px; text-align: center; color: #64748b; font-size: 13px;">No recent messages</div>
            <?php else: ?>
                <?php foreach ($footerMsgs as $fm): 
                    $isNew = strtolower($fm['status']) === 'new';
                    $parts = explode(' ', trim($fm['full_name']));
                    $init = count($parts) >= 2 ? strtoupper(substr($parts[0], 0, 1) . substr($parts[1], 0, 1)) : strtoupper(substr($fm['full_name'], 0, 2));
                ?>
                    <a href="<?php echo $baseUrl; ?>admin/messages.php" class="popover-item <?php echo $isNew ? 'unread' : ''; ?>" style="text-decoration:none; color:inherit;">
                        <div class="popover-avatar"><?php echo htmlspecialchars($init); ?></div>
                        <div class="popover-text">
                            <strong><?php echo htmlspecialchars($fm['full_name']); ?></strong>
                            <span><?php echo htmlspecialchars(mb_strimwidth($fm['subject'] ?: $fm['message'], 0, 45, '...')); ?></span>
                            <small><?php echo date('d M, h:i A', strtotime($fm['created_at'])); ?></small>
                        </div>
                    </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <div class="popover-footer">
            <a href="<?php echo $baseUrl; ?>admin/messages.php">Open Message Center &rarr;</a>
        </div>
    </div>

    <!-- Sidebar Backdrop for Mobile -->
    <div class="admin-sidebar-backdrop" id="adminSidebarBackdrop" onclick="closeAdminSidebarMobile()"></div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?php echo get_base_url(); ?>assets/js/main.js"></script>
    <script>
    // Unified Global Admin Sidebar Drawer & Desktop Collapse Engine
    window.closeAdminSidebarMobile = function() {
        const sb = document.getElementById('adminSidebar');
        const bd = document.getElementById('adminSidebarBackdrop');
        if (sb) sb.classList.remove('open');
        if (bd) bd.classList.remove('active');
    };

    window.toggleAdminSidebar = function(e) {
        if (e) {
            e.preventDefault();
            e.stopPropagation();
        }
        const layout = document.getElementById('adminLayout') || document.querySelector('.admin-layout');
        const sb = document.getElementById('adminSidebar');
        const bd = document.getElementById('adminSidebarBackdrop');
        const isMobile = window.innerWidth <= 1024;

        if (isMobile) {
            if (sb) {
                const isOpen = sb.classList.toggle('open');
                if (bd) bd.classList.toggle('active', isOpen);
            }
        } else {
            if (layout) {
                layout.classList.toggle('sidebar-collapsed');
            }
        }
    };

    // Global Ctrl+K Search Shortcut & Esc handler
    document.addEventListener('keydown', function(e) {
        if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k') {
            e.preventDefault();
            openAdminSearchModal();
        }
        if (e.key === 'Escape') {
            closeAdminSearchModal();
            closeAllPopovers();
            closeAdminSidebarMobile();
        }
    });

    function openAdminSearchModal() {
        const m = document.getElementById('adminSearchModal');
        if (m) {
            m.style.display = 'flex';
            const inp = document.getElementById('adminSearchInput');
            if (inp) { inp.value = ''; inp.focus(); }
        }
    }

    function closeAdminSearchModal() {
        const m = document.getElementById('adminSearchModal');
        if (m) m.style.display = 'none';
    }

    function filterAdminSearch(q) {
        const res = document.querySelectorAll('#adminSearchResults .search-result-item');
        const term = q.toLowerCase().trim();
        res.forEach(el => {
            const txt = el.textContent.toLowerCase();
            el.style.display = (txt.includes(term) || term === '') ? 'flex' : 'none';
        });
    }

    // Dropdowns and Popovers logic
    function closeAllPopovers() {
        ['notifPopover', 'msgPopover', 'adminUserDropdown'].forEach(id => {
            const el = document.getElementById(id);
            if (el) el.style.display = 'none';
        });
    }

    const notifTrigger = document.getElementById('notifDropdownTrigger');
    if (notifTrigger) {
        notifTrigger.addEventListener('click', function(e) {
            e.stopPropagation();
            const p = document.getElementById('notifPopover');
            const isOpen = p.style.display === 'block';
            closeAllPopovers();
            if (!isOpen) {
                const rect = notifTrigger.getBoundingClientRect();
                p.style.top = (rect.bottom + 10) + 'px';
                p.style.right = (window.innerWidth - rect.right - 10) + 'px';
                p.style.display = 'block';
            }
        });
    }

    const msgTrigger = document.getElementById('msgDropdownTrigger');
    if (msgTrigger) {
        msgTrigger.addEventListener('click', function(e) {
            e.stopPropagation();
            const p = document.getElementById('msgPopover');
            const isOpen = p.style.display === 'block';
            closeAllPopovers();
            if (!isOpen) {
                const rect = msgTrigger.getBoundingClientRect();
                p.style.top = (rect.bottom + 10) + 'px';
                p.style.right = (window.innerWidth - rect.right - 10) + 'px';
                p.style.display = 'block';
            }
        });
    }

    const userMenuTrigger = document.getElementById('adminUserMenuTrigger');
    if (userMenuTrigger) {
        userMenuTrigger.addEventListener('click', function(e) {
            e.stopPropagation();
            const p = document.getElementById('adminUserDropdown');
            const isOpen = p.style.display === 'block';
            closeAllPopovers();
            if (!isOpen) p.style.display = 'block';
        });
    }

    document.addEventListener('click', function() {
        closeAllPopovers();
    });

    // Theme Toggle
    const themeBtn = document.getElementById('adminThemeToggle');
    if (themeBtn) {
        themeBtn.addEventListener('click', function() {
            document.body.classList.toggle('admin-dark-mode');
        });
    }
    </script>
</body>
</html>

