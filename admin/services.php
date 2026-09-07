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
        $stmt = $pdo->prepare("DELETE FROM services WHERE id = ?");
        $stmt->execute([$id]);
        $notice = 'Service deleted successfully.';
    }
}

// Handle Form Submission (Add/Edit)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && (!isset($_POST['action']) || $_POST['action'] === 'save')) {
    $id = !empty($_POST['id']) ? (int)$_POST['id'] : null;
    $title = trim($_POST['title'] ?? '');
    $shortDesc = trim($_POST['short_description'] ?? '');
    $fullDesc = trim($_POST['full_description'] ?? '');
    $icon = trim($_POST['icon'] ?? 'Globe');
    $category = trim($_POST['category'] ?? 'Software Development');
    $status = $_POST['status'] ?? 'active';
    $sortOrder = (int)($_POST['sort_order'] ?? 0);

    if (empty($title) || empty($shortDesc)) {
        $error = 'Title and Short Description are required.';
    } else {
        if ($id) {
            $stmt = $pdo->prepare("UPDATE services SET title=?, short_description=?, full_description=?, icon=?, category=?, status=?, sort_order=? WHERE id=?");
            $stmt->execute([$title, $shortDesc, $fullDesc, $icon, $category, $status, $sortOrder, $id]);
            $notice = 'Service updated successfully.';
        } else {
            $stmt = $pdo->prepare("INSERT INTO services (title, short_description, full_description, icon, category, status, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$title, $shortDesc, $fullDesc, $icon, $category, $status, $sortOrder]);
            $notice = 'Service created successfully.';
        }
    }
}

// Fetch Services
$query = trim($_GET['q'] ?? '');
$statusFilter = $_GET['status'] ?? 'all';

$sql = "SELECT * FROM services WHERE 1=1";
$params = [];

if ($statusFilter !== 'all') {
    $sql .= " AND status = ?";
    $params[] = $statusFilter;
}
if (!empty($query)) {
    $sql .= " AND (title LIKE ? OR category LIKE ?)";
    $params[] = "%{$query}%";
    $params[] = "%{$query}%";
}

$sql .= " ORDER BY sort_order ASC, id DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll();

$editRecord = null;
if (isset($_GET['edit'])) {
    $eStmt = $pdo->prepare("SELECT * FROM services WHERE id = ?");
    $eStmt->execute([(int)$_GET['edit']]);
    $editRecord = $eStmt->fetch();
}
?>

<div class="admin-page-head">
    <div>
        <span class="eyebrow">Admin module</span>
        <h1>Manage Services</h1>
        <p>Create, edit, activate, and organize public service cards.</p>
    </div>
    <div class="admin-head-actions">
        <a class="btn primary" href="services.php?action=new"><?php echo render_icon('Plus'); ?>Add New Service</a>
    </div>
</div>

<?php if ($notice): ?><div class="admin-notice success"><?php echo render_icon('CheckCircle2'); ?><?php echo htmlspecialchars($notice); ?></div><?php endif; ?>
<?php if ($error): ?><div class="admin-notice error"><?php echo render_icon('TriangleAlert'); ?><?php echo htmlspecialchars($error); ?></div><?php endif; ?>

<div class="toolbar advanced-toolbar">
    <form method="get" action="" style="display:flex; gap:10px; width:100%;">
        <input name="q" placeholder="Search services..." value="<?php echo htmlspecialchars($query); ?>">
        <select name="status" onchange="this.form.submit()">
            <option value="all" <?php echo $statusFilter==='all'?'selected':''; ?>>All statuses</option>
            <option value="active" <?php echo $statusFilter==='active'?'selected':''; ?>>Active</option>
            <option value="inactive" <?php echo $statusFilter==='inactive'?'selected':''; ?>>Inactive</option>
        </select>
        <button class="btn small" type="submit"><?php echo render_icon('Search'); ?>Filter</button>
    </form>
</div>

<div class="table-card glass-card">
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Title</th>
                <th>Category</th>
                <th>Status</th>
                <th>Order</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($rows as $row): ?>
                <tr>
                    <td><?php echo $row['id']; ?></td>
                    <td><strong><?php echo htmlspecialchars($row['title']); ?></strong></td>
                    <td><?php echo htmlspecialchars($row['category']); ?></td>
                    <td><span class="status"><?php echo htmlspecialchars($row['status']); ?></span></td>
                    <td><?php echo (int)$row['sort_order']; ?></td>
                    <td>
                        <div class="row-actions">
                            <a class="icon-btn" href="services.php?edit=<?php echo $row['id']; ?>" title="Edit"><?php echo render_icon('Pencil'); ?></a>
                            <form method="post" action="services.php" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete service &quot;<?php echo htmlspecialchars($row['title'], ENT_QUOTES); ?>&quot;? This action cannot be undone.');">
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

<?php if ($editRecord || (isset($_GET['action']) && $_GET['action'] === 'new')): ?>
    <div class="modal-backdrop" role="dialog">
        <form class="admin-modal" method="post" action="services.php">
            <?php echo csrf_input(); ?>
            <input type="hidden" name="action" value="save">
            <input type="hidden" name="id" value="<?php echo $editRecord['id'] ?? ''; ?>">
            <div class="modal-head">
                <div>
                    <span class="eyebrow"><?php echo $editRecord ? 'Edit record' : 'New record'; ?></span>
                    <h2>Services</h2>
                </div>
                <a class="icon-btn" href="services.php"><?php echo render_icon('X'); ?></a>
            </div>

            <div class="admin-form modal-form">
                <label>Title<input name="title" value="<?php echo htmlspecialchars($editRecord['title'] ?? ''); ?>" required></label>
                <label>Category<input name="category" value="<?php echo htmlspecialchars($editRecord['category'] ?? 'Software Development'); ?>" required></label>
                <label>Icon<input name="icon" value="<?php echo htmlspecialchars($editRecord['icon'] ?? 'Globe'); ?>"></label>
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
                <a class="btn ghost" href="services.php">Cancel</a>
                <button class="btn primary" type="submit"><?php echo render_icon('Save'); ?>Save Changes</button>
            </div>
        </form>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
