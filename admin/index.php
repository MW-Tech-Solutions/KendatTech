<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/admin_header.php';

$pdo = get_db();

$counts = [
    'users' => (int)$pdo->query("SELECT COUNT(*) FROM users")->fetchColumn(),
    'appointments' => (int)$pdo->query("SELECT COUNT(*) FROM appointments")->fetchColumn(),
    'pending_appointments' => (int)$pdo->query("SELECT COUNT(*) FROM appointments WHERE status = 'pending'")->fetchColumn(),
    'project_requests' => (int)$pdo->query("SELECT COUNT(*) FROM project_requests")->fetchColumn(),
    'completed_projects' => (int)$pdo->query("SELECT COUNT(*) FROM projects WHERE status = 'completed'")->fetchColumn(),
    'active_services' => (int)$pdo->query("SELECT COUNT(*) FROM services WHERE status = 'active'")->fetchColumn(),
    'messages' => (int)$pdo->query("SELECT COUNT(*) FROM contact_messages")->fetchColumn(),
    'testimonials' => (int)$pdo->query("SELECT COUNT(*) FROM testimonials")->fetchColumn(),
];

$recentAppointments = $pdo->query("SELECT * FROM appointments ORDER BY id DESC LIMIT 6")->fetchAll();
$recentRequests = $pdo->query("SELECT * FROM project_requests ORDER BY id DESC LIMIT 6")->fetchAll();
$recentMessages = $pdo->query("SELECT * FROM contact_messages ORDER BY id DESC LIMIT 6")->fetchAll();

$statCards = [
    ['Users', $counts['users'], 'Users'],
    ['Appointments', $counts['appointments'], 'CalendarCheck'],
    ['Pending', $counts['pending_appointments'], 'Hourglass'],
    ['Project Requests', $counts['project_requests'], 'ClipboardList'],
    ['Completed Projects', $counts['completed_projects'], 'CheckCircle2'],
    ['Active Services', $counts['active_services'], 'Layers3'],
    ['Messages', $counts['messages'], 'Mail'],
    ['Testimonials', $counts['testimonials'], 'Star'],
];
?>

<div class="admin-page-head">
    <div>
        <span class="eyebrow">SYSTEM OVERVIEW</span>
        <h1>Dashboard Control Center</h1>
        <p>Monitor real-time system metrics, pending client appointments, project requests, and messages.</p>
    </div>
    <div class="admin-head-actions">
        <a class="tc-pill-btn-blue" href="<?php echo $baseUrl; ?>admin/requests.php">
            <?php echo render_icon('ClipboardList', 16); ?> View Requests
        </a>
    </div>
</div>

<!-- High-Impact Sleek Metrics Grid -->
<div class="admin-metrics-grid">
    <?php foreach ($statCards as [$label, $val, $icon]): ?>
        <div class="admin-stat-card">
            <div class="stat-card-left">
                <span class="stat-label"><?php echo htmlspecialchars($label); ?></span>
                <strong class="stat-value"><?php echo number_format((int)$val); ?></strong>
            </div>
            <div class="stat-icon-wrapper">
                <?php echo render_icon($icon, 22); ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<div class="dashboard-grid" style="margin-top: 32px;">
    <!-- Recent Appointments Card -->
    <div class="glass-card table-card">
        <div class="table-card-head">
            <h3>Recent Appointments</h3>
            <a class="table-view-all" href="<?php echo $baseUrl; ?>admin/appointments.php">View All -></a>
        </div>
        <table>
            <thead>
                <tr>
                    <th>Full Name</th>
                    <th>Preferred Date</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($recentAppointments)): ?>
                    <tr><td colspan="4" style="text-align:center; color:var(--muted); padding:24px;">No appointments recorded yet.</td></tr>
                <?php else: ?>
                    <?php foreach ($recentAppointments as $row): 
                        $st = strtolower((string)$row['status']);
                        $stClass = $st === 'completed' || $st === 'approved' ? 'status-completed' : ($st === 'pending' ? 'status-pending' : 'status-blocked');
                    ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($row['full_name']); ?></strong></td>
                            <td><?php echo htmlspecialchars($row['preferred_date']); ?></td>
                            <td><span class="admin-status-badge <?php echo $stClass; ?>"><?php echo htmlspecialchars($row['status']); ?></span></td>
                            <td>
                                <a class="icon-btn small" href="<?php echo $baseUrl; ?>admin/appointments.php" title="Manage">
                                    <?php echo render_icon('ExternalLink', 14); ?>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Recent Project Requests Card -->
    <div class="glass-card table-card">
        <div class="table-card-head">
            <h3>Recent Project Requests</h3>
            <a class="table-view-all" href="<?php echo $baseUrl; ?>admin/requests.php">View All -></a>
        </div>
        <table>
            <thead>
                <tr>
                    <th>Client Name</th>
                    <th>Category</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($recentRequests)): ?>
                    <tr><td colspan="4" style="text-align:center; color:var(--muted); padding:24px;">No project requests recorded yet.</td></tr>
                <?php else: ?>
                    <?php foreach ($recentRequests as $row): 
                        $st = strtolower((string)$row['status']);
                        $stClass = $st === 'completed' || $st === 'approved' ? 'status-completed' : ($st === 'pending' || $st === 'new' ? 'status-pending' : 'status-blocked');
                    ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($row['user_name']); ?></strong></td>
                            <td><span class="tech-tag-pill"><?php echo htmlspecialchars($row['project_category']); ?></span></td>
                            <td><span class="admin-status-badge <?php echo $stClass; ?>"><?php echo htmlspecialchars($row['status']); ?></span></td>
                            <td>
                                <a class="icon-btn small" href="<?php echo $baseUrl; ?>admin/requests.php" title="Manage">
                                    <?php echo render_icon('ExternalLink', 14); ?>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Recent Messages Card (Full Width) -->
    <div class="glass-card table-card" style="grid-column: 1 / -1; margin-top: 12px;">
        <div class="table-card-head">
            <h3>Recent Direct Messages</h3>
            <a class="table-view-all" href="<?php echo $baseUrl; ?>admin/messages.php">View All -></a>
        </div>
        <table>
            <thead>
                <tr>
                    <th>Full Name</th>
                    <th>Email</th>
                    <th>Subject</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($recentMessages)): ?>
                    <tr><td colspan="6" style="text-align:center; color:var(--muted); padding:24px;">No messages received yet.</td></tr>
                <?php else: ?>
                    <?php foreach ($recentMessages as $row): 
                        $st = strtolower((string)($row['status'] ?? 'new'));
                        $stClass = $st === 'read' ? 'status-completed' : 'status-pending';
                    ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($row['full_name']); ?></strong></td>
                            <td><?php echo htmlspecialchars($row['email']); ?></td>
                            <td><?php echo htmlspecialchars($row['subject']); ?></td>
                            <td><small><?php echo htmlspecialchars(substr((string)($row['created_at'] ?? ''), 0, 10)); ?></small></td>
                            <td><span class="admin-status-badge <?php echo $stClass; ?>"><?php echo htmlspecialchars($row['status'] ?? 'new'); ?></span></td>
                            <td>
                                <a class="icon-btn small" href="<?php echo $baseUrl; ?>admin/messages.php" title="View Message">
                                    <?php echo render_icon('Mail', 14); ?>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
