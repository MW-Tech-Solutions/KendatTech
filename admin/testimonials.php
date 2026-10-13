<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/admin_header.php';

require_csrf_token();

$pdo = get_db();
$notice = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $id = (int)($_POST['id'] ?? 0);
    if ($id > 0) {
        $stmt = $pdo->prepare("DELETE FROM testimonials WHERE id = ?");
        $stmt->execute([$id]);
        $notice = 'Testimonial deleted.';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && (!isset($_POST['action']) || $_POST['action'] === 'save')) {
    $id = !empty($_POST['id']) ? (int)$_POST['id'] : null;
    $clientName = trim($_POST['client_name'] ?? '');
    $positionCompany = trim($_POST['position_company'] ?? '');
    $messageText = trim($_POST['message'] ?? '');
    $rating = (int)($_POST['rating'] ?? 5);
    $status = $_POST['status'] ?? 'active';

    if (empty($clientName) || empty($messageText)) {
        $error = 'Client name and message are required.';
    } else {
        if ($id) {
            $stmt = $pdo->prepare("UPDATE testimonials SET client_name=?, position_company=?, message=?, rating=?, status=? WHERE id=?");
            $stmt->execute([$clientName, $positionCompany, $messageText, $rating, $status, $id]);
            $notice = 'Testimonial updated successfully.';
        } else {
            $stmt = $pdo->prepare("INSERT INTO testimonials (client_name, position_company, message, rating, status) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$clientName, $positionCompany, $messageText, $rating, $status]);
            $notice = 'Testimonial created successfully.';
        }
    }
}

$rows = $pdo->query("SELECT * FROM testimonials ORDER BY id DESC")->fetchAll();

$editRecord = null;
if (isset($_GET['edit'])) {
    $eStmt = $pdo->prepare("SELECT * FROM testimonials WHERE id = ?");
    $eStmt->execute([(int)$_GET['edit']]);
    $editRecord = $eStmt->fetch();
}
?>

<div class="admin-page-head">
    <div>
        <span class="eyebrow">Admin module</span>
        <h1>Manage Testimonials</h1>
        <p>Publish client feedback, ratings, and credibility signals.</p>
    </div>
    <div class="admin-head-actions">
        <a class="btn primary" href="testimonials.php?action=new"><?php echo render_icon('Plus'); ?>Add Testimonial</a>
    </div>
</div>

<?php if ($notice): ?><div class="admin-notice success"><?php echo render_icon('CheckCircle2'); ?><?php echo htmlspecialchars($notice); ?></div><?php endif; ?>
<?php if ($error): ?><div class="admin-notice error"><?php echo render_icon('TriangleAlert'); ?><?php echo htmlspecialchars($error); ?></div><?php endif; ?>

<div class="admin-projects-card">
    <table class="admin-projects-table">
        <thead>
            <tr>
                <th style="width: 60px;">ID</th>
                <th style="min-width: 180px;">Client Name</th>
                <th style="min-width: 200px;">Company / Position</th>
                <th style="width: 130px;">Rating</th>
                <th style="width: 120px;">Status</th>
                <th style="width: 120px; text-align: right;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($rows as $row): ?>
                <tr>
                    <td><span class="admin-id-badge">#<?php echo $row['id']; ?></span></td>
                    <td><strong style="color: #0f172a; font-size: 14px;"><?php echo htmlspecialchars($row['client_name']); ?></strong></td>
                    <td><span style="font-size: 13px; color: #475569; font-weight: 600;"><?php echo htmlspecialchars($row['position_company'] ?? '-'); ?></span></td>
                    <td><span style="color: #F59E0B; font-size: 15px; letter-spacing: 2px;"><?php echo str_repeat('★', (int)$row['rating']); ?></span></td>
                    <td>
                        <span class="admin-status-pill status-completed">
                            <span class="status-dot-green"></span> <?php echo htmlspecialchars(ucfirst($row['status'])); ?>
                        </span>
                    </td>
                    <td style="text-align: right;">
                        <div class="admin-actions-group">
                            <a class="btn-action-edit" href="testimonials.php?edit=<?php echo $row['id']; ?>" title="Edit Testimonial">
                                <?php echo render_icon('Pencil', 14); ?>
                            </a>
                            <form method="post" action="testimonials.php" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete testimonial from &quot;<?php echo htmlspecialchars($row['client_name'], ENT_QUOTES); ?>&quot;? This action cannot be undone.');">
                                <?php echo csrf_input(); ?>
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                <button type="submit" class="btn-action-delete" title="Delete Testimonial">
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

<?php if ($editRecord || (isset($_GET['action']) && $_GET['action'] === 'new')): ?>
    <div class="modal-backdrop" role="dialog">
        <form class="admin-modal" method="post" action="testimonials.php">
            <?php echo csrf_input(); ?>
            <input type="hidden" name="action" value="save">
            <input type="hidden" name="id" value="<?php echo $editRecord['id'] ?? ''; ?>">
            <div class="modal-head">
                <div>
                    <span class="eyebrow"><?php echo $editRecord ? 'Edit record' : 'New record'; ?></span>
                    <h2>Testimonials</h2>
                </div>
                <a class="icon-btn" href="testimonials.php"><?php echo render_icon('X'); ?></a>
            </div>

            <div class="admin-form modal-form">
                <label>Client Name<input name="client_name" value="<?php echo htmlspecialchars($editRecord['client_name'] ?? ''); ?>" required></label>
                <label>Position / Company<input name="position_company" value="<?php echo htmlspecialchars($editRecord['position_company'] ?? ''); ?>"></label>
                <label>Rating (1-5)<input name="rating" type="number" min="1" max="5" value="<?php echo htmlspecialchars((string)($editRecord['rating'] ?? 5)); ?>"></label>
                <label>Status
                    <select name="status">
                        <option value="active" <?php echo ($editRecord['status'] ?? '') === 'active' ? 'selected' : ''; ?>>Active</option>
                        <option value="inactive" <?php echo ($editRecord['status'] ?? '') === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                    </select>
                </label>
                <label style="grid-column:1 / -1;">Message<textarea name="message" required><?php echo htmlspecialchars($editRecord['message'] ?? ''); ?></textarea></label>
            </div>

            <div class="modal-actions">
                <a class="btn ghost" href="testimonials.php">Cancel</a>
                <button class="btn primary" type="submit"><?php echo render_icon('Save'); ?>Save Changes</button>
            </div>
        </form>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
