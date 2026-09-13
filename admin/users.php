<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/admin_header.php';

require_csrf_token();

$pdo = get_db();
$notice = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'toggle_status') {
    $id = (int)($_POST['id'] ?? 0);
    $currentStatus = $_POST['current_status'] ?? 'active';
    $newStatus = $currentStatus === 'active' ? 'blocked' : 'active';

    if ($id > 0) {
        $stmt = $pdo->prepare("UPDATE users SET status = ? WHERE id = ?");
        $stmt->execute([$newStatus, $id]);
        $notice = "User status changed to {$newStatus}.";
    }
}

$query = trim($_GET['q'] ?? '');
$sql = "SELECT * FROM users WHERE 1=1";
$params = [];

if (!empty($query)) {
    $sql .= " AND (full_name LIKE ? OR email LIKE ? OR phone LIKE ?)";
    $params[] = "%{$query}%";
    $params[] = "%{$query}%";
    $params[] = "%{$query}%";
}

$sql .= " ORDER BY id DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll();
?>

<div class="admin-page-head">
    <div>
        <span class="eyebrow">Admin module</span>
        <h1>Manage Users</h1>
        <p>View clients, update profile status, and block inactive or unsafe accounts.</p>
    </div>
</div>

<?php if ($notice): ?><div class="admin-notice success"><?php echo render_icon('CheckCircle2'); ?><?php echo htmlspecialchars($notice); ?></div><?php endif; ?>

<div class="toolbar advanced-toolbar">
    <form method="get" action="" style="display:flex; gap:10px; width:100%;">
        <input name="q" placeholder="Search users by name, email or phone..." value="<?php echo htmlspecialchars($query); ?>">
        <button class="btn small" type="submit"><?php echo render_icon('Search'); ?>Search</button>
    </form>
</div>

<div class="admin-projects-card">
    <table class="admin-projects-table">
        <thead>
            <tr>
                <th style="width: 60px;">ID</th>
                <th style="min-width: 180px;">Full Name</th>
                <th style="min-width: 220px;">Email Address</th>
                <th style="width: 140px;">Phone Number</th>
                <th style="width: 120px;">Status</th>
                <th style="width: 130px;">Registered</th>
                <th style="width: 160px; text-align: right;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($rows as $row): ?>
                <tr>
                    <td><span class="admin-id-badge">#<?php echo $row['id']; ?></span></td>
                    <td><strong><?php echo htmlspecialchars($row['full_name']); ?></strong></td>
                    <td><span style="color:#64748b; font-size:13px;"><?php echo htmlspecialchars($row['email']); ?></span></td>
                    <td><span style="color:#475569; font-size:13px; font-weight:600;"><?php echo htmlspecialchars($row['phone'] ?? '-'); ?></span></td>
                    <td>
                        <?php if ($row['status'] === 'active'): ?>
                            <span class="admin-status-pill status-completed">
                                <span class="status-dot-green"></span> Active
                            </span>
                        <?php else: ?>
                            <span class="admin-status-pill" style="background:#fee2e2; color:#dc2626; border:1px solid #fca5a5;">
                                <span style="width:7px; height:7px; border-radius:50%; background:#dc2626; display:inline-block;"></span> Blocked
                            </span>
                        <?php endif; ?>
                    </td>
                    <td><span style="font-size:12px; color:#64748b; font-weight:600;"><?php echo date('M d, Y', strtotime($row['created_at'])); ?></span></td>
                    <td style="text-align: right;">
                        <div class="row-actions">
                            <form method="post" action="users.php" style="display:inline;" onsubmit="return confirm('Are you sure you want to <?php echo $row['status'] === 'active' ? 'block' : 'unblock'; ?> user &quot;<?php echo htmlspecialchars($row['full_name'], ENT_QUOTES); ?>&quot;?');">
                                <?php echo csrf_input(); ?>
                                <input type="hidden" name="action" value="toggle_status">
                                <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                <input type="hidden" name="current_status" value="<?php echo $row['status']; ?>">
                                <?php if ($row['status'] === 'active'): ?>
                                    <button type="submit" class="btn small" style="background:#fee2e2; color:#b91c1c; border:1px solid #fca5a5;" title="Block Account">
                                        <?php echo render_icon('Ban', 13); ?> Block Account
                                    </button>
                                <?php else: ?>
                                    <button type="submit" class="btn small" style="background:#dcfce7; color:#15803d; border:1px solid #bbf7d0;" title="Unblock Account">
                                        <?php echo render_icon('CheckCircle', 13); ?> Unblock Account
                                    </button>
                                <?php endif; ?>
                            </form>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
