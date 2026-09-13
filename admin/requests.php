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

<div class="admin-projects-card">
    <table class="admin-projects-table">
        <thead>
            <tr>
                <th style="width: 60px;">ID</th>
                <th style="min-width: 180px;">Client Name & Company</th>
                <th style="min-width: 160px;">Category</th>
                <th style="width: 140px;">Budget Range</th>
                <th style="width: 130px;">Attachment</th>
                <th style="width: 120px;">Status</th>
                <th style="width: 120px; text-align: right;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($rows as $row): ?>
                <tr>
                    <td><span class="admin-id-badge">#<?php echo $row['id']; ?></span></td>
                    <td>
                        <strong style="font-size:14px; color:#0f172a; display:block;"><?php echo htmlspecialchars($row['user_name']); ?></strong>
                        <span style="font-size:12px; color:#64748b; font-weight:600;"><?php echo htmlspecialchars($row['company_name'] ?? ''); ?></span>
                    </td>
                    <td><span class="admin-cat-badge"><?php echo htmlspecialchars($row['project_category']); ?></span></td>
                    <td><span style="font-size:13px; color:#334155; font-weight:700;"><?php echo htmlspecialchars($row['budget_range'] ?? '-'); ?></span></td>
                    <td>
                        <?php if (!empty($row['file_path'])): ?>
                            <a class="admin-demo-pill" href="<?php echo htmlspecialchars(upload_asset_url($row['file_path'])); ?>" target="_blank" rel="noopener">
                                <?php echo render_icon('FileText', 12); ?> Attachment
                            </a>
                        <?php else: ?>
                            <span class="admin-demo-muted">None</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php
                            $st = strtolower($row['status']);
                            $statusClass = 'status-completed';
                            $dotClass = 'status-dot-green';
                            if (in_array($st, ['pending', 'reviewing'])) {
                                $statusClass = 'status-upcoming';
                                $dotClass = 'status-dot-amber';
                            } elseif ($st === 'in_progress') {
                                $statusClass = 'status-ongoing';
                                $dotClass = 'status-dot-blue';
                            }
                        ?>
                        <span class="admin-status-pill <?php echo $statusClass; ?>">
                            <span class="<?php echo $dotClass; ?>"></span> <?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $row['status']))); ?>
                        </span>
                    </td>
                    <td style="text-align: right;">
                        <div class="admin-actions-group">
                            <a class="btn-action-edit" href="requests.php?edit=<?php echo $row['id']; ?>" title="Edit Request Status/Feedback">
                                <?php echo render_icon('Pencil', 14); ?>
                            </a>
                            <form method="post" action="requests.php" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete project request from &quot;<?php echo htmlspecialchars($row['user_name'], ENT_QUOTES); ?>&quot;? This action cannot be undone.');">
                                <?php echo csrf_input(); ?>
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                <button type="submit" class="btn-action-delete" title="Delete Project Request">
                                    <?php echo render_icon('Trash2', 14); ?>
                                </button>
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
