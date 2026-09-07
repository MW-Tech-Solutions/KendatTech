<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/admin_header.php';

require_csrf_token();

$pdo = get_db();
$notice = '';
$error = '';

// Handle Delete via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $id = (int)($_POST['id'] ?? 0);
    if ($id > 0) {
        $stmt = $pdo->prepare("DELETE FROM contact_messages WHERE id = ?");
        $stmt->execute([$id]);
        audit_log('ADMIN_MESSAGE_DELETED', 'contact_message', (string)$id);
        $notice = 'Message deleted successfully.';
    }
}

// Handle Status Change via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'status_change') {
    $id = (int)($_POST['id'] ?? 0);
    $newStatus = trim($_POST['status_change'] ?? '');
    $allowedStatuses = ['new', 'read', 'replied', 'archived'];
    if ($id > 0 && in_array($newStatus, $allowedStatuses, true)) {
        $stmt = $pdo->prepare("UPDATE contact_messages SET status = ? WHERE id = ?");
        $stmt->execute([$newStatus, $id]);
        audit_log('ADMIN_MESSAGE_STATUS_UPDATED', 'contact_message', (string)$id, ['status' => $newStatus]);
        $notice = "Message status updated to {$newStatus}.";
    }
}

$statusFilter = $_GET['status'] ?? 'all';
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 20;
$offset = ($page - 1) * $perPage;

$sqlWhere = " WHERE 1=1";
$params = [];

if ($statusFilter !== 'all') {
    $sqlWhere .= " AND status = ?";
    $params[] = $statusFilter;
}

// Count total
$countStmt = $pdo->prepare("SELECT COUNT(*) FROM contact_messages {$sqlWhere}");
$countStmt->execute($params);
$totalRecords = (int)$countStmt->fetchColumn();
$totalPages = max(1, (int)ceil($totalRecords / $perPage));

// Fetch paginated
$sql = "SELECT * FROM contact_messages {$sqlWhere} ORDER BY id DESC LIMIT {$perPage} OFFSET {$offset}";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll();

$viewRecord = null;
if (isset($_GET['view'])) {
    $vStmt = $pdo->prepare("SELECT * FROM contact_messages WHERE id = ?");
    $vStmt->execute([(int)$_GET['view']]);
    $viewRecord = $vStmt->fetch();

    if ($viewRecord && $viewRecord['status'] === 'new') {
        $upStmt = $pdo->prepare("UPDATE contact_messages SET status = 'read' WHERE id = ?");
        $upStmt->execute([(int)$viewRecord['id']]);
        audit_log('ADMIN_MESSAGE_READ', 'contact_message', (string)$viewRecord['id']);
        $viewRecord['status'] = 'read';
    }
}
?>

<div class="admin-page-head">
    <div>
        <span class="eyebrow">Admin module</span>
        <h1>Contact Messages</h1>
        <p>Manage enquiries submitted through the contact page.</p>
    </div>
</div>

<?php if ($notice): ?><div class="admin-notice success"><?php echo render_icon('CheckCircle2'); ?><?php echo htmlspecialchars($notice); ?></div><?php endif; ?>
<?php if ($error): ?><div class="admin-notice danger"><?php echo render_icon('AlertCircle'); ?><?php echo htmlspecialchars($error); ?></div><?php endif; ?>

<div class="toolbar advanced-toolbar">
    <form method="get" action="" style="display:flex; gap:10px; align-items:center;">
        <select name="status" onchange="this.form.submit()">
            <option value="all" <?php echo $statusFilter==='all'?'selected':''; ?>>All statuses</option>
            <option value="new" <?php echo $statusFilter==='new'?'selected':''; ?>>New</option>
            <option value="read" <?php echo $statusFilter==='read'?'selected':''; ?>>Read</option>
            <option value="replied" <?php echo $statusFilter==='replied'?'selected':''; ?>>Replied</option>
            <option value="archived" <?php echo $statusFilter==='archived'?'selected':''; ?>>Archived</option>
        </select>
    </form>
</div>

<div class="table-card glass-card">
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Full Name</th>
                <th>Subject</th>
                <th>Date</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($rows)): ?>
                <tr>
                    <td colspan="6" style="text-align:center; padding:30px; color:#94a3b8;">No contact messages found.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($rows as $row): ?>
                    <tr>
                        <td><?php echo $row['id']; ?></td>
                        <td><strong><?php echo htmlspecialchars($row['full_name']); ?></strong><br><small><?php echo htmlspecialchars($row['email']); ?></small></td>
                        <td><?php echo htmlspecialchars($row['subject'] ?? 'No subject'); ?></td>
                        <td><?php echo date('M d, Y', strtotime($row['created_at'])); ?></td>
                        <td><span class="status"><?php echo htmlspecialchars($row['status']); ?></span></td>
                        <td>
                            <div class="row-actions" style="display:flex; gap:6px;">
                                <a class="icon-btn" href="messages.php?view=<?php echo $row['id']; ?>&status=<?php echo urlencode($statusFilter); ?>" title="Read Message"><?php echo render_icon('Eye'); ?></a>
                                <form method="post" action="messages.php" style="display:inline;" onsubmit="return confirm('Delete message #<?php echo $row['id']; ?>? This action cannot be undone.');">
                                    <?php echo csrf_input(); ?>
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                    <button type="submit" class="icon-btn danger" title="Delete"><?php echo render_icon('Trash2'); ?></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php if ($totalPages > 1): ?>
    <div class="pagination" style="margin-top:20px; display:flex; gap:8px; justify-content:center;">
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <a href="messages.php?page=<?php echo $i; ?>&status=<?php echo urlencode($statusFilter); ?>" class="btn small <?php echo $i===$page?'primary':'ghost'; ?>"><?php echo $i; ?></a>
        <?php endfor; ?>
    </div>
<?php endif; ?>

<?php if ($viewRecord): ?>
    <div class="modal-backdrop" role="dialog" aria-modal="true">
        <div class="admin-modal">
            <div class="modal-head">
                <div>
                    <span class="eyebrow">Message Details</span>
                    <h2><?php echo htmlspecialchars($viewRecord['subject'] ?? 'Enquiry'); ?></h2>
                </div>
                <a class="icon-btn" href="messages.php?status=<?php echo urlencode($statusFilter); ?>"><?php echo render_icon('X'); ?></a>
            </div>

            <div style="display:grid; gap:14px;">
                <p><strong>From:</strong> <?php echo htmlspecialchars($viewRecord['full_name']); ?> (&lt;<?php echo htmlspecialchars($viewRecord['email']); ?>&gt;)</p>
                <p><strong>Phone:</strong> <?php echo htmlspecialchars($viewRecord['phone'] ?? 'N/A'); ?></p>
                <p><strong>Date:</strong> <?php echo htmlspecialchars($viewRecord['created_at']); ?></p>
                <div class="glass-card">
                    <p style="white-space:pre-line; color:#eef7ff;"><?php echo htmlspecialchars($viewRecord['message']); ?></p>
                </div>
            </div>

            <div class="modal-actions" style="margin-top:14px; display:flex; gap:10px;">
                <form method="post" action="messages.php">
                    <?php echo csrf_input(); ?>
                    <input type="hidden" name="action" value="status_change">
                    <input type="hidden" name="id" value="<?php echo $viewRecord['id']; ?>">
                    <input type="hidden" name="status_change" value="replied">
                    <button type="submit" class="btn small primary">Mark as Replied</button>
                </form>

                <form method="post" action="messages.php">
                    <?php echo csrf_input(); ?>
                    <input type="hidden" name="action" value="status_change">
                    <input type="hidden" name="id" value="<?php echo $viewRecord['id']; ?>">
                    <input type="hidden" name="status_change" value="archived">
                    <button type="submit" class="btn small ghost">Archive</button>
                </form>

                <a class="btn ghost" href="messages.php?status=<?php echo urlencode($statusFilter); ?>">Close</a>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
