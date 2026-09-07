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
        $stmt = $pdo->prepare("DELETE FROM projects WHERE id = ?");
        $stmt->execute([$id]);
        audit_log('ADMIN_PROJECT_DELETED', 'project', (string)$id);
        $notice = 'Project deleted successfully.';
    }
}

// Handle Form Submission (Add/Edit)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && (!isset($_POST['action']) || $_POST['action'] === 'save')) {
    $id = !empty($_POST['id']) ? (int)$_POST['id'] : null;
    $title = trim($_POST['title'] ?? '');
    $slug = trim($_POST['slug'] ?? '');
    if (empty($slug)) {
        $slug = slugify($title);
    }
    $category = trim($_POST['category'] ?? 'General');
    $shortDesc = trim($_POST['short_description'] ?? '');
    $fullDesc = trim($_POST['full_description'] ?? '');
    $features = trim($_POST['features'] ?? '');
    $technologies = trim($_POST['technologies'] ?? '');
    $demoLink = trim($_POST['demo_link'] ?? '');
    $clientName = trim($_POST['client_name'] ?? '');
    $completionDate = !empty($_POST['completion_date']) ? $_POST['completion_date'] : date('Y-m-d');
    $status = $_POST['status'] ?? 'completed';
    $featured = !empty($_POST['featured']) ? 1 : 0;

    if (empty($title) || empty($shortDesc)) {
        $error = 'Title and Short Description are required.';
    } else {
        $mainImage = handle_file_upload('main_image', 'projects');

        if ($id) {
            if ($mainImage) {
                $stmt = $pdo->prepare("UPDATE projects SET title=?, slug=?, category=?, short_description=?, full_description=?, features=?, technologies=?, main_image=?, demo_link=?, client_name=?, completion_date=?, status=?, featured=? WHERE id=?");
                $stmt->execute([$title, $slug, $category, $shortDesc, $fullDesc, $features, $technologies, $mainImage, $demoLink, $clientName, $completionDate, $status, $featured, $id]);
            } else {
                $stmt = $pdo->prepare("UPDATE projects SET title=?, slug=?, category=?, short_description=?, full_description=?, features=?, technologies=?, demo_link=?, client_name=?, completion_date=?, status=?, featured=? WHERE id=?");
                $stmt->execute([$title, $slug, $category, $shortDesc, $fullDesc, $features, $technologies, $demoLink, $clientName, $completionDate, $status, $featured, $id]);
            }
            $projectId = $id;
            audit_log('ADMIN_PROJECT_UPDATED', 'project', (string)$projectId, ['title' => $title]);
            $notice = 'Project updated successfully.';
        } else {
            $stmt = $pdo->prepare("INSERT INTO projects (title, slug, category, short_description, full_description, features, technologies, main_image, demo_link, client_name, completion_date, status, featured) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$title, $slug, $category, $shortDesc, $fullDesc, $features, $technologies, $mainImage, $demoLink, $clientName, $completionDate, $status, $featured]);
            $projectId = (int)$pdo->lastInsertId();
            audit_log('ADMIN_PROJECT_CREATED', 'project', (string)$projectId, ['title' => $title]);
            $notice = 'Project created successfully.';
        }

        // Handle Gallery Uploads
        $galleryImages = handle_multiple_file_uploads('gallery_images', 'projects');
        if (!empty($galleryImages)) {
            $maxSort = (int)$pdo->query("SELECT COALESCE(MAX(sort_order), 0) FROM project_images WHERE project_id = {$projectId}")->fetchColumn();
            $gStmt = $pdo->prepare("INSERT INTO project_images (project_id, image_path, caption, sort_order) VALUES (?, ?, ?, ?)");
            foreach ($galleryImages as $idx => $gPath) {
                $gStmt->execute([$projectId, $gPath, 'Screenshot', $maxSort + $idx + 1]);
            }
        }
    }
}

// Fetch Projects
$query = trim($_GET['q'] ?? '');
$statusFilter = $_GET['status'] ?? 'all';

$sql = "SELECT * FROM projects WHERE 1=1";
$params = [];

if ($statusFilter !== 'all') {
    $sql .= " AND status = ?";
    $params[] = $statusFilter;
}
if (!empty($query)) {
    $sql .= " AND (title LIKE ? OR category LIKE ? OR technologies LIKE ?)";
    $params[] = "%{$query}%";
    $params[] = "%{$query}%";
    $params[] = "%{$query}%";
}

$sql .= " ORDER BY featured DESC, id DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll();

$editRecord = null;
if (isset($_GET['edit'])) {
    $eStmt = $pdo->prepare("SELECT * FROM projects WHERE id = ?");
    $eStmt->execute([(int)$_GET['edit']]);
    $editRecord = $eStmt->fetch();
}
?>

<div class="admin-page-head">
    <div>
        <span class="eyebrow">Admin module</span>
        <h1>Manage Projects</h1>
        <p>Control portfolio projects, case study details, technologies, main image, screenshots, and featured status.</p>
    </div>
    <div class="admin-head-actions">
        <a class="btn primary" href="projects.php?action=new"><?php echo render_icon('Plus'); ?>Add New Project</a>
    </div>
</div>

<?php if ($notice): ?><div class="admin-notice success"><?php echo render_icon('CheckCircle2'); ?><?php echo htmlspecialchars($notice); ?></div><?php endif; ?>
<?php if ($error): ?><div class="admin-notice error"><?php echo render_icon('TriangleAlert'); ?><?php echo htmlspecialchars($error); ?></div><?php endif; ?>

<div class="toolbar advanced-toolbar">
    <form method="get" action="" style="display:flex; gap:10px; width:100%;">
        <input name="q" placeholder="Search projects..." value="<?php echo htmlspecialchars($query); ?>">
        <select name="status" onchange="this.form.submit()">
            <option value="all" <?php echo $statusFilter==='all'?'selected':''; ?>>All statuses</option>
            <option value="completed" <?php echo $statusFilter==='completed'?'selected':''; ?>>Completed</option>
            <option value="ongoing" <?php echo $statusFilter==='ongoing'?'selected':''; ?>>Ongoing</option>
            <option value="upcoming" <?php echo $statusFilter==='upcoming'?'selected':''; ?>>Upcoming</option>
        </select>
        <button class="btn small" type="submit"><?php echo render_icon('Search'); ?>Filter</button>
    </form>
</div>

<div class="table-card glass-card">
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Preview</th>
                <th>Title</th>
                <th>Category</th>
                <th>Demo</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($rows as $row): ?>
                <tr>
                    <td><?php echo $row['id']; ?></td>
                    <td>
                        <?php if ($row['main_image']): ?>
                            <img class="admin-thumb" src="<?php echo htmlspecialchars(upload_asset_url($row['main_image'])); ?>" alt="">
                        <?php else: ?>
                            <span class="muted">No image</span>
                        <?php endif; ?>
                    </td>
                    <td><strong><?php echo htmlspecialchars($row['title']); ?></strong></td>
                    <td><?php echo htmlspecialchars($row['category']); ?></td>
                    <td>
                        <?php if (!empty($row['demo_link']) && $row['demo_link'] !== '#'): ?>
                            <a class="admin-demo-link" href="<?php echo htmlspecialchars($row['demo_link']); ?>" target="_blank" rel="noopener">
                                <?php echo render_icon('ExternalLink', 14); ?>Open
                            </a>
                        <?php else: ?>
                            <span class="muted">Not set</span>
                        <?php endif; ?>
                    </td>
                    <td><span class="status"><?php echo htmlspecialchars($row['status']); ?></span></td>
                    <td>
                        <div class="row-actions">
                            <a class="icon-btn" href="projects.php?edit=<?php echo $row['id']; ?>" title="Edit"><?php echo render_icon('Pencil'); ?></a>
                            <form method="post" action="projects.php" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete project &quot;<?php echo htmlspecialchars($row['title'], ENT_QUOTES); ?>&quot;? This action cannot be undone.');">
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
        <form class="admin-modal" method="post" action="projects.php" enctype="multipart/form-data">
            <?php echo csrf_input(); ?>
            <input type="hidden" name="action" value="save">
            <input type="hidden" name="id" value="<?php echo $editRecord['id'] ?? ''; ?>">
            <div class="modal-head">
                <div>
                    <span class="eyebrow"><?php echo $editRecord ? 'Edit record' : 'New record'; ?></span>
                    <h2>Projects</h2>
                </div>
                <a class="icon-btn" href="projects.php"><?php echo render_icon('X'); ?></a>
            </div>

            <div class="admin-form modal-form">
                <label>Title<input name="title" value="<?php echo htmlspecialchars($editRecord['title'] ?? ''); ?>" required></label>
                <label>Slug<input name="slug" value="<?php echo htmlspecialchars($editRecord['slug'] ?? ''); ?>"></label>
                <label>Category<input name="category" value="<?php echo htmlspecialchars($editRecord['category'] ?? 'School Portal'); ?>" required></label>
                <label>Technologies<input name="technologies" value="<?php echo htmlspecialchars($editRecord['technologies'] ?? ''); ?>"></label>
                <label>Demo Link<input name="demo_link" type="url" placeholder="https://example.com/demo" value="<?php echo htmlspecialchars(($editRecord['demo_link'] ?? '') === '#' ? '' : ($editRecord['demo_link'] ?? '')); ?>"></label>
                <label>Client Name<input name="client_name" value="<?php echo htmlspecialchars($editRecord['client_name'] ?? ''); ?>"></label>
                <label>Completion Date<input name="completion_date" type="date" value="<?php echo htmlspecialchars($editRecord['completion_date'] ?? date('Y-m-d')); ?>"></label>
                <label>Status
                    <select name="status">
                        <option value="completed" <?php echo ($editRecord['status'] ?? '') === 'completed' ? 'selected' : ''; ?>>Completed</option>
                        <option value="ongoing" <?php echo ($editRecord['status'] ?? '') === 'ongoing' ? 'selected' : ''; ?>>Ongoing</option>
                        <option value="upcoming" <?php echo ($editRecord['status'] ?? '') === 'upcoming' ? 'selected' : ''; ?>>Upcoming</option>
                    </select>
                </label>
                <label>Featured
                    <select name="featured">
                        <option value="1" <?php echo !empty($editRecord['featured']) ? 'selected' : ''; ?>>Yes (1)</option>
                        <option value="0" <?php echo empty($editRecord['featured']) ? 'selected' : ''; ?>>No (0)</option>
                    </select>
                </label>
                <label class="file-field" style="grid-column:1 / -1;">Main project image
                    <input type="file" name="main_image" accept="image/*">
                    <?php if (!empty($editRecord['main_image'])): ?>
                        <span>Current: <?php echo htmlspecialchars($editRecord['main_image']); ?></span>
                    <?php endif; ?>
                </label>
                <label class="file-field" style="grid-column:1 / -1;">Project gallery screenshots
                    <input type="file" name="gallery_images[]" accept="image/*" multiple>
                    <span>Upload multiple screenshots for slider carousel.</span>
                </label>
                <label style="grid-column:1 / -1;">Short Description<textarea name="short_description" required><?php echo htmlspecialchars($editRecord['short_description'] ?? ''); ?></textarea></label>
                <label style="grid-column:1 / -1;">Full Description<textarea name="full_description"><?php echo htmlspecialchars($editRecord['full_description'] ?? ''); ?></textarea></label>
                <label style="grid-column:1 / -1;">Features (one per line)<textarea name="features"><?php echo htmlspecialchars($editRecord['features'] ?? ''); ?></textarea></label>
            </div>

            <div class="modal-actions">
                <a class="btn ghost" href="projects.php">Cancel</a>
                <button class="btn primary" type="submit"><?php echo render_icon('Save'); ?>Save Changes</button>
            </div>
        </form>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
