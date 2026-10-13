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
        $stmt = $pdo->prepare("DELETE FROM ai_solutions WHERE id = ?");
        $stmt->execute([$id]);
        $notice = 'AI Solution deleted successfully.';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && (!isset($_POST['action']) || $_POST['action'] === 'save')) {
    $id = !empty($_POST['id']) ? (int)$_POST['id'] : null;
    $title = trim($_POST['title'] ?? '');
    $shortDesc = trim($_POST['short_description'] ?? '');
    $fullDesc = trim($_POST['full_description'] ?? '');
    $icon = trim($_POST['icon'] ?? 'Bot');
    $status = $_POST['status'] ?? 'active';
    $sortOrder = (int)($_POST['sort_order'] ?? 0);

    if (empty($title) || empty($shortDesc)) {
        $error = 'Title and Short Description are required.';
    } else {
        if ($id) {
            $stmt = $pdo->prepare("UPDATE ai_solutions SET title=?, short_description=?, full_description=?, icon=?, status=?, sort_order=? WHERE id=?");
            $stmt->execute([$title, $shortDesc, $fullDesc, $icon, $status, $sortOrder, $id]);
            $notice = 'AI Solution updated successfully.';
        } else {
            $stmt = $pdo->prepare("INSERT INTO ai_solutions (title, short_description, full_description, icon, status, sort_order) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$title, $shortDesc, $fullDesc, $icon, $status, $sortOrder]);
            $notice = 'AI Solution created successfully.';
        }
    }
}

$rows = $pdo->query("SELECT * FROM ai_solutions ORDER BY sort_order ASC, id DESC")->fetchAll();

$editRecord = null;
if (isset($_GET['edit'])) {
    $eStmt = $pdo->prepare("SELECT * FROM ai_solutions WHERE id = ?");
    $eStmt->execute([(int)$_GET['edit']]);
    $editRecord = $eStmt->fetch();
}
?>

<div class="admin-page-head">
    <div>
        <span class="eyebrow">Admin module</span>
        <h1>Manage AI Solutions</h1>
        <p>Maintain the futuristic AI solution cards shown on the website.</p>
    </div>
    <div class="admin-head-actions">
        <a class="btn primary" href="ai-solutions.php?action=new"><?php echo render_icon('Plus'); ?>Add New Solution</a>
    </div>
</div>

<?php if ($notice): ?><div class="admin-notice success"><?php echo render_icon('CheckCircle2'); ?><?php echo htmlspecialchars($notice); ?></div><?php endif; ?>
<?php if ($error): ?><div class="admin-notice error"><?php echo render_icon('TriangleAlert'); ?><?php echo htmlspecialchars($error); ?></div><?php endif; ?>

<div class="admin-projects-card">
    <table class="admin-projects-table">
        <thead>
            <tr>
                <th style="width: 60px;">ID</th>
                <th style="min-width: 220px;">Title</th>
                <th style="min-width: 260px;">Short Description</th>
                <th style="width: 120px;">Status</th>
                <th style="width: 80px;">Order</th>
                <th style="width: 120px; text-align: right;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($rows as $row): ?>
                <tr>
                    <td><span class="admin-id-badge">#<?php echo $row['id']; ?></span></td>
                    <td><strong style="color: #0f172a;"><?php echo htmlspecialchars($row['title']); ?></strong></td>
                    <td><span style="font-size: 13px; color: #475569;"><?php echo htmlspecialchars($row['short_description']); ?></span></td>
                    <td>
                        <span class="admin-status-pill status-completed">
                            <span class="status-dot-green"></span> <?php echo htmlspecialchars(ucfirst($row['status'])); ?>
                        </span>
                    </td>
                    <td><span style="font-weight: 700; color: #475569;">#<?php echo (int)$row['sort_order']; ?></span></td>
                    <td style="text-align: right;">
                        <div class="admin-actions-group">
                            <a class="btn-action-edit" href="ai-solutions.php?edit=<?php echo $row['id']; ?>" title="Edit AI Solution">
                                <?php echo render_icon('Pencil', 14); ?>
                            </a>
                            <form method="post" action="ai-solutions.php" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete AI solution &quot;<?php echo htmlspecialchars($row['title'], ENT_QUOTES); ?>&quot;? This action cannot be undone.');">
                                <?php echo csrf_input(); ?>
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                <button type="submit" class="btn-action-delete" title="Delete AI Solution">
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
        <form class="admin-modal" method="post" action="ai-solutions.php">
            <?php echo csrf_input(); ?>
            <input type="hidden" name="action" value="save">
            <input type="hidden" name="id" value="<?php echo $editRecord['id'] ?? ''; ?>">
            <div class="modal-head">
                <div>
                    <span class="eyebrow"><?php echo $editRecord ? 'Edit record' : 'New record'; ?></span>
                    <h2>AI Solutions</h2>
                </div>
                <a class="icon-btn" href="ai-solutions.php"><?php echo render_icon('X'); ?></a>
            </div>

            <div class="admin-form modal-form">
                <label>Title<input name="title" value="<?php echo htmlspecialchars($editRecord['title'] ?? ''); ?>" required></label>
                <label>Icon<input name="icon" value="<?php echo htmlspecialchars($editRecord['icon'] ?? 'Bot'); ?>"></label>
                <label>Sort Order<input name="sort_order" type="number" value="<?php echo htmlspecialchars((string)($editRecord['sort_order'] ?? 0)); ?>"></label>
                <label>Status
                    <select name="status">
                        <option value="active" <?php echo ($editRecord['status'] ?? '') === 'active' ? 'selected' : ''; ?>>Active</option>
                        <option value="inactive" <?php echo ($editRecord['status'] ?? '') === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                    </select>
                </label>
                <label style="grid-column:1 / -1;">Short Description<textarea name="short_description" required><?php echo htmlspecialchars($editRecord['short_description'] ?? ''); ?></textarea></label>
                <label style="grid-column:1 / -1;">Full Description<textarea name="full_description"><?php echo htmlspecialchars($editRecord['full_description'] ?? ''); ?></textarea></label>
            </div>

            <div class="modal-actions">
                <a class="btn ghost" href="ai-solutions.php">Cancel</a>
                <button class="btn primary" type="submit"><?php echo render_icon('Save'); ?>Save Changes</button>
            </div>
        </form>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
