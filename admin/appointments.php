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
        $stmt = $pdo->prepare("DELETE FROM appointments WHERE id = ?");
        $stmt->execute([$id]);
        $notice = 'Appointment deleted successfully.';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && (!isset($_POST['action']) || $_POST['action'] === 'update')) {
    $id = (int)$_POST['id'];
    $status = $_POST['status'] ?? 'pending';
    $adminNote = trim($_POST['admin_note'] ?? '');

    // Fetch appointment details before updating for email notification
    $fetchStmt = $pdo->prepare("SELECT full_name, email, preferred_date, preferred_time FROM appointments WHERE id = ? LIMIT 1");
    $fetchStmt->execute([$id]);
    $appData = $fetchStmt->fetch();

    $stmt = $pdo->prepare("UPDATE appointments SET status = ?, admin_note = ? WHERE id = ?");
    $stmt->execute([$status, $adminNote, $id]);

    if ($appData) {
        send_appointment_status_email($appData['email'], $appData['full_name'], $appData['preferred_date'], (string)$appData['preferred_time'], $status, $adminNote);
        $notice = 'Appointment updated successfully and email notification sent to ' . htmlspecialchars($appData['email']) . '.';
    } else {
        $notice = 'Appointment updated successfully.';
    }
}

$statusFilter = $_GET['status'] ?? 'all';
$sql = "SELECT * FROM appointments WHERE 1=1";
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
    $eStmt = $pdo->prepare("SELECT * FROM appointments WHERE id = ?");
    $eStmt->execute([(int)$_GET['edit']]);
    $editRecord = $eStmt->fetch();
}
?>

<div class="admin-page-head">
    <div>
        <span class="eyebrow">Admin module</span>
        <h1>Appointments</h1>
        <p>Review bookings, approve schedules, reject requests, and add admin notes.</p>
    </div>
</div>

<?php if ($notice): ?><div class="admin-notice success"><?php echo render_icon('CheckCircle2'); ?><?php echo htmlspecialchars($notice); ?></div><?php endif; ?>

<div class="toolbar advanced-toolbar">
    <form method="get" action="" style="display:flex; gap:10px;">
        <select name="status" onchange="this.form.submit()">
            <option value="all" <?php echo $statusFilter==='all'?'selected':''; ?>>All statuses</option>
            <option value="pending" <?php echo $statusFilter==='pending'?'selected':''; ?>>Pending</option>
            <option value="approved" <?php echo $statusFilter==='approved'?'selected':''; ?>>Approved</option>
            <option value="rejected" <?php echo $statusFilter==='rejected'?'selected':''; ?>>Rejected</option>
            <option value="completed" <?php echo $statusFilter==='completed'?'selected':''; ?>>Completed</option>
        </select>
    </form>
</div>

<div class="table-card glass-card">
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Full Name</th>
                <th>Contact</th>
                <th>Preferred Date & Time</th>
                <th>Type</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($rows as $row): ?>
                <tr>
                    <td><?php echo $row['id']; ?></td>
                    <td><strong><?php echo htmlspecialchars($row['full_name']); ?></strong></td>
                    <td><?php echo htmlspecialchars($row['email']); ?><br><small><?php echo htmlspecialchars($row['phone'] ?? ''); ?></small></td>
                    <td><?php echo htmlspecialchars($row['preferred_date']); ?> at <?php echo htmlspecialchars($row['preferred_time']); ?></td>
                    <td><?php echo htmlspecialchars($row['appointment_type']); ?></td>
                    <td><span class="status"><?php echo htmlspecialchars($row['status']); ?></span></td>
                    <td>
                        <div class="row-actions">
                            <a class="icon-btn" href="appointments.php?edit=<?php echo $row['id']; ?>" title="Edit Note/Status"><?php echo render_icon('Pencil'); ?></a>
                            <form method="post" action="appointments.php" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete appointment for &quot;<?php echo htmlspecialchars($row['full_name'], ENT_QUOTES); ?>&quot;? This action cannot be undone.');">
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
        <form class="admin-modal" method="post" action="appointments.php">
            <?php echo csrf_input(); ?>
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="id" value="<?php echo $editRecord['id']; ?>">
            <div class="modal-head">
                <div>
                    <span class="eyebrow">Edit Appointment</span>
                    <h2><?php echo htmlspecialchars($editRecord['full_name']); ?></h2>
                </div>
                <a class="icon-btn" href="appointments.php"><?php echo render_icon('X'); ?></a>
            </div>

            <div class="admin-form modal-form">
                <label>Status
                    <select name="status">
                        <option value="pending" <?php echo $editRecord['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="approved" <?php echo $editRecord['status'] === 'approved' ? 'selected' : ''; ?>>Approved</option>
                        <option value="rejected" <?php echo $editRecord['status'] === 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                        <option value="completed" <?php echo $editRecord['status'] === 'completed' ? 'selected' : ''; ?>>Completed</option>
                    </select>
                </label>
                <label style="grid-column:1 / -1;">Message from Client<textarea readonly><?php echo htmlspecialchars($editRecord['message'] ?? ''); ?></textarea></label>
                <label style="grid-column:1 / -1;">Admin Note / Feedback<textarea name="admin_note"><?php echo htmlspecialchars($editRecord['admin_note'] ?? ''); ?></textarea></label>
            </div>

            <div class="modal-actions">
                <a class="btn ghost" href="appointments.php">Cancel</a>
                <button class="btn primary" type="submit"><?php echo render_icon('Save'); ?>Save Changes</button>
            </div>
        </form>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
