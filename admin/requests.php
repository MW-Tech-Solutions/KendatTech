<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/admin_header.php';
require_once __DIR__ . '/../includes/mailer.php';

require_csrf_token();

$pdo = get_db();
$notice = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $id = (int)($_POST['id'] ?? 0);
    if ($id > 0) {
        $stmt = $pdo->prepare("DELETE FROM project_requests WHERE id = ?");
        $stmt->execute([$id]);
        $notice = 'Project request deleted.';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && (!isset($_POST['action']) || $_POST['action'] === 'update')) {
    $id = (int)$_POST['id'];
    $status = $_POST['status'] ?? 'pending';
    $adminFeedback = trim($_POST['admin_feedback'] ?? '');

    // Fetch details before updating for email notification
    $fetchStmt = $pdo->prepare("SELECT user_name, email, project_type FROM project_requests WHERE id = ? LIMIT 1");
    $fetchStmt->execute([$id]);
    $reqData = $fetchStmt->fetch();

    $stmt = $pdo->prepare("UPDATE project_requests SET status = ?, admin_feedback = ? WHERE id = ?");
    $stmt->execute([$status, $adminFeedback, $id]);

    if ($reqData) {
        send_project_request_status_email($reqData['email'], $reqData['user_name'], $reqData['project_type'], $status, $adminFeedback);
        $notice = 'Project request updated successfully and email notification sent to ' . htmlspecialchars($reqData['email']) . '.';
    } else {
        $notice = 'Project request updated successfully.';
    }
}

$statusFilter = $_GET['status'] ?? 'all';
$sql = "SELECT * FROM project_requests WHERE 1=1";
$params = [];

if ($statusFilter !== 'all') {
    $sql .= " AND status = ?";
    $params[] = $statusFilter;
}

$sql .= " ORDER BY id DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll();

$editRecord = null;
if (isset($_GET['edit'])) {
    $eStmt = $pdo->prepare("SELECT * FROM project_requests WHERE id = ?");
    $eStmt->execute([(int)$_GET['edit']]);
    $editRecord = $eStmt->fetch();
}
?>

<div class="admin-page-head">
    <div>
        <span class="eyebrow">Admin module</span>
        <h1>Project Requests</h1>
        <p>Track project enquiries from review to approval, delivery, or rejection.</p>
    </div>
</div>

<?php if ($notice): ?><div class="admin-notice success"><?php echo render_icon('CheckCircle2'); ?><?php echo htmlspecialchars($notice); ?></div><?php endif; ?>

<div class="toolbar advanced-toolbar">
    <form method="get" action="" style="display:flex; gap:10px;">
        <select name="status" onchange="this.form.submit()">
            <option value="all" <?php echo $statusFilter==='all'?'selected':''; ?>>All statuses</option>
            <option value="pending" <?php echo $statusFilter==='pending'?'selected':''; ?>>Pending</option>
            <option value="reviewing" <?php echo $statusFilter==='reviewing'?'selected':''; ?>>Reviewing</option>
            <option value="approved" <?php echo $statusFilter==='approved'?'selected':''; ?>>Approved</option>
            <option value="in_progress" <?php echo $statusFilter==='in_progress'?'selected':''; ?>>In Progress</option>
            <option value="completed" <?php echo $statusFilter==='completed'?'selected':''; ?>>Completed</option>
            <option value="rejected" <?php echo $statusFilter==='rejected'?'selected':''; ?>>Rejected</option>
        </select>
    </form>
</div>

<div class="table-card glass-card">
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Client Name</th>
                <th>Category</th>
                <th>Budget Range</th>
                <th>Attachment</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($rows as $row): ?>
                <tr>
                    <td><?php echo $row['id']; ?></td>
                    <td><strong><?php echo htmlspecialchars($row['user_name']); ?></strong><br><small><?php echo htmlspecialchars($row['company_name'] ?? ''); ?></small></td>
                    <td><?php echo htmlspecialchars($row['project_category']); ?></td>
                    <td><?php echo htmlspecialchars($row['budget_range'] ?? '-'); ?></td>
                    <td>
                        <?php if (!empty($row['file_path'])): ?>
                            <a href="<?php echo htmlspecialchars(upload_asset_url($row['file_path'])); ?>" target="_blank" class="muted">View Attachment</a>
                        <?php else: ?>
                            <span class="muted">None</span>
                        <?php endif; ?>
                    </td>
                    <td><span class="status"><?php echo htmlspecialchars($row['status']); ?></span></td>
                    <td>
                        <div class="row-actions">
                            <a class="icon-btn" href="requests.php?edit=<?php echo $row['id']; ?>" title="Edit Status/Feedback"><?php echo render_icon('Pencil'); ?></a>
                            <form method="post" action="requests.php" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete project request from &quot;<?php echo htmlspecialchars($row['user_name'], ENT_QUOTES); ?>&quot;? This action cannot be undone.');">
                                <?php echo csrf_input(); ?>
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                <button type="submit" class="icon-btn danger" title="Delete"><?php echo render_icon('Trash2'); ?></button>
                            </form>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php if ($editRecord): ?>
    <div class="modal-backdrop" role="dialog">
        <form class="admin-modal" method="post" action="requests.php">
            <?php echo csrf_input(); ?>
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="id" value="<?php echo $editRecord['id']; ?>">
            <div class="modal-head">
                <div>
                    <span class="eyebrow">Edit Request</span>
                    <h2><?php echo htmlspecialchars($editRecord['user_name']); ?> (<?php echo htmlspecialchars($editRecord['project_category']); ?>)</h2>
                </div>
                <a class="icon-btn" href="requests.php"><?php echo render_icon('X'); ?></a>
            </div>

            <div class="admin-form modal-form">
                <label>Status
                    <select name="status">
                        <option value="pending" <?php echo $editRecord['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="reviewing" <?php echo $editRecord['status'] === 'reviewing' ? 'selected' : ''; ?>>Reviewing</option>
                        <option value="approved" <?php echo $editRecord['status'] === 'approved' ? 'selected' : ''; ?>>Approved</option>
                        <option value="in_progress" <?php echo $editRecord['status'] === 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
                        <option value="completed" <?php echo $editRecord['status'] === 'completed' ? 'selected' : ''; ?>>Completed</option>
                        <option value="rejected" <?php echo $editRecord['status'] === 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                    </select>
                </label>
                <label style="grid-column:1 / -1;">Description<textarea readonly><?php echo htmlspecialchars($editRecord['description'] ?? ''); ?></textarea></label>
                <label style="grid-column:1 / -1;">Admin Feedback<textarea name="admin_feedback"><?php echo htmlspecialchars($editRecord['admin_feedback'] ?? ''); ?></textarea></label>
            </div>

            <div class="modal-actions">
                <a class="btn ghost" href="requests.php">Cancel</a>
                <button class="btn primary" type="submit"><?php echo render_icon('Save'); ?>Save Changes</button>
            </div>
        </form>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
