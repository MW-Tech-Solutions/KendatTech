<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/admin_header.php';

require_csrf_token();

$pdo = get_db();
$notice = '';
$error = '';

// Handle Delete Project via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $id = (int)($_POST['id'] ?? 0);
    if ($id > 0) {
        $stmt = $pdo->prepare("DELETE FROM projects WHERE id = ?");
        $stmt->execute([$id]);
        audit_log('ADMIN_PROJECT_DELETED', 'project', (string)$id);
        $notice = 'Project deleted successfully.';
    }
}

// Handle Delete Gallery Image via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_gallery_image') {
    $imageId = (int)($_POST['image_id'] ?? 0);
    $projectId = (int)($_POST['project_id'] ?? 0);
    if ($imageId > 0) {
        $stmt = $pdo->prepare("SELECT image_path FROM project_images WHERE id = ?");
        $stmt->execute([$imageId]);
        $imgPath = $stmt->fetchColumn();
        if ($imgPath && file_exists(__DIR__ . '/../uploads/' . ltrim($imgPath, '/'))) {
            @unlink(__DIR__ . '/../uploads/' . ltrim($imgPath, '/'));
        }
        $delStmt = $pdo->prepare("DELETE FROM project_images WHERE id = ?");
        $delStmt->execute([$imageId]);
        audit_log('ADMIN_PROJECT_IMAGE_DELETED', 'project', (string)$imageId);
        $notice = 'Gallery screenshot deleted successfully.';
    }
}

// Handle Main Cover Image Clear via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_main_image') {
    $projectId = (int)($_POST['project_id'] ?? 0);
    if ($projectId > 0) {
        $stmt = $pdo->prepare("SELECT main_image FROM projects WHERE id = ?");
        $stmt->execute([$projectId]);
        $imgPath = $stmt->fetchColumn();
        if ($imgPath && file_exists(__DIR__ . '/../uploads/' . ltrim($imgPath, '/'))) {
            @unlink(__DIR__ . '/../uploads/' . ltrim($imgPath, '/'));
        }
        $updStmt = $pdo->prepare("UPDATE projects SET main_image = '' WHERE id = ?");
        $updStmt->execute([$projectId]);
        audit_log('ADMIN_PROJECT_MAIN_IMAGE_CLEARED', 'project', (string)$projectId);
        $notice = 'Main cover image removed successfully.';
    }
}

// Handle Form Submission (Add/Edit Project)
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
$galleryRecordImages = [];
if (isset($_GET['edit'])) {
    $eStmt = $pdo->prepare("SELECT * FROM projects WHERE id = ?");
    $eStmt->execute([(int)$_GET['edit']]);
    $editRecord = $eStmt->fetch();
    if ($editRecord) {
        $gStmt = $pdo->prepare("SELECT * FROM project_images WHERE project_id = ? ORDER BY sort_order ASC, id ASC");
        $gStmt->execute([(int)$editRecord['id']]);
        $galleryRecordImages = $gStmt->fetchAll();
    }
}
?>

<div class="admin-page-head">
    <div>
        <span class="eyebrow">Admin module</span>
        <h1>Manage Projects</h1>
        <p>Control portfolio projects, case study details, technologies, main cover image, gallery screenshots, and image deletions.</p>
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
                <th>Cover</th>
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
                            <img class="admin-thumb" src="<?php echo htmlspecialchars(upload_asset_url($row['main_image'])); ?>" alt="" style="width: 50px; height: 35px; object-fit: cover; border-radius: 6px;">
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
                            <a class="icon-btn" href="projects.php?edit=<?php echo $row['id']; ?>" title="Edit Project & Manage Images"><?php echo render_icon('Pencil'); ?> Edit</a>
                            <form method="post" action="projects.php" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete project &quot;<?php echo htmlspecialchars($row['title'], ENT_QUOTES); ?>&quot;? This action cannot be undone.');">
                                <?php echo csrf_input(); ?>
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                <button type="submit" class="icon-btn danger" title="Delete Project"><?php echo render_icon('Trash2'); ?></button>
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
        <form class="admin-modal" method="post" action="projects.php" enctype="multipart/form-data" style="max-width: 850px;">
            <?php echo csrf_input(); ?>
            <input type="hidden" name="action" value="save">
            <input type="hidden" name="id" value="<?php echo $editRecord['id'] ?? ''; ?>">
            <div class="modal-head">
                <div>
                    <span class="eyebrow"><?php echo $editRecord ? 'Edit Record' : 'New Record'; ?></span>
                    <h2><?php echo $editRecord ? 'Edit Project & Manage Media' : 'New Project'; ?></h2>
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

                <!-- Dedicated Visual Media & Gallery Manager Section -->
                <div class="admin-media-manager-card" style="grid-column: 1 / -1; background: #f8fafc; border: 1.5px solid #cbd5e1; border-radius: 16px; padding: 20px; margin-top: 10px;">
                    <h3 style="margin-top: 0; margin-bottom: 12px; font-size: 16px; color: #0f172a; display: flex; align-items: center; gap: 8px;">
                        <?php echo render_icon('Image', 20); ?> Project Cover & Gallery Image Manager
                    </h3>

                    <!-- 1. Main Cover Image Preview & Controls -->
                    <div class="main-image-control-box" style="margin-bottom: 20px; padding-bottom: 16px; border-bottom: 1px solid #e2e8f0;">
                        <label style="font-weight: 700; color: #334155; margin-bottom: 8px; display: block;">Main Cover Image</label>
                        <?php if (!empty($editRecord['main_image'])): 
                            $mainImgUrl = upload_asset_url($editRecord['main_image']);
                            $fileDisk = __DIR__ . '/../uploads/' . ltrim($editRecord['main_image'], '/');
                            $isBroken = !file_exists($fileDisk) || !is_file($fileDisk);
                        ?>
                            <div class="current-media-preview-row" style="display: flex; align-items: center; gap: 16px; background: #ffffff; padding: 12px; border-radius: 12px; border: 1px solid #e2e8f0; margin-bottom: 10px;">
                                <img src="<?php echo htmlspecialchars($mainImgUrl); ?>" alt="Main Cover" style="width: 100px; height: 65px; object-fit: cover; border-radius: 8px; border: 1px solid #cbd5e1;">
                                <div style="flex: 1; min-width: 0;">
                                    <strong style="font-size: 13px; color: #0f172a; display: block; word-break: break-all;"><?php echo htmlspecialchars($editRecord['main_image']); ?></strong>
                                    <?php if ($isBroken): ?>
                                        <span style="font-size: 11px; color: #ef4444; font-weight: 700;">⚠ Missing File on Disk (Fallback placeholder in use)</span>
                                    <?php else: ?>
                                        <span style="font-size: 11px; color: #16a34a; font-weight: 700;">✓ Active File</span>
                                    <?php endif; ?>
                                </div>
                                <form method="post" action="projects.php?edit=<?php echo $editRecord['id']; ?>" style="margin:0;" onsubmit="return confirm('Remove this main cover image?');">
                                    <?php echo csrf_input(); ?>
                                    <input type="hidden" name="action" value="delete_main_image">
                                    <input type="hidden" name="project_id" value="<?php echo $editRecord['id']; ?>">
                                    <button type="submit" class="btn small danger" style="background: #ef4444; color: #fff; border: 0; padding: 6px 12px; border-radius: 8px; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; gap: 4px;">
                                        <?php echo render_icon('Trash2', 14); ?> Delete Cover Image
                                    </button>
                                </form>
                            </div>
                        <?php endif; ?>
                        <input type="file" name="main_image" accept="image/*">
                        <small style="color: #64748b; font-size: 12px; display: block; margin-top: 4px;">Upload a new image to set or replace the main project cover image.</small>
                    </div>

                    <!-- 2. Additional Gallery Screenshots Preview & Delete Controls -->
                    <div class="gallery-images-control-box">
                        <label style="font-weight: 700; color: #334155; margin-bottom: 10px; display: block;">Gallery Screenshots (<?php echo count($galleryRecordImages); ?> uploaded)</label>
                        <?php if (!empty($galleryRecordImages)): ?>
                            <div class="admin-gallery-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(140px, 1fr)); gap: 12px; margin-bottom: 14px;">
                                <?php foreach ($galleryRecordImages as $gImg): 
                                    $gImgUrl = upload_asset_url($gImg['image_path']);
                                    $gDisk = __DIR__ . '/../uploads/' . ltrim($gImg['image_path'], '/');
                                    $gBroken = !file_exists($gDisk) || !is_file($gDisk);
                                ?>
                                    <div class="gallery-item-card" style="background: #ffffff; border: 1px solid #cbd5e1; border-radius: 12px; padding: 8px; display: flex; flex-direction: column; align-items: center; text-align: center; position: relative;">
                                        <img src="<?php echo htmlspecialchars($gImgUrl); ?>" alt="Screenshot" style="width: 100%; height: 80px; object-fit: cover; border-radius: 8px; margin-bottom: 6px;">
                                        <?php if ($gBroken): ?>
                                            <span style="font-size: 10px; color: #ef4444; font-weight: 700; margin-bottom: 4px;">⚠ Broken File</span>
                                        <?php else: ?>
                                            <span style="font-size: 10px; color: #64748b; margin-bottom: 4px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; max-width: 100%;"><?php echo htmlspecialchars(basename($gImg['image_path'])); ?></span>
                                        <?php endif; ?>
                                        <form method="post" action="projects.php?edit=<?php echo $editRecord['id']; ?>" style="margin:0; width: 100%;" onsubmit="return confirm('Delete this screenshot image?');">
                                            <?php echo csrf_input(); ?>
                                            <input type="hidden" name="action" value="delete_gallery_image">
                                            <input type="hidden" name="image_id" value="<?php echo $gImg['id']; ?>">
                                            <input type="hidden" name="project_id" value="<?php echo $editRecord['id']; ?>">
                                            <button type="submit" class="btn small danger" style="width: 100%; background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; padding: 4px 8px; border-radius: 6px; font-size: 11px; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; gap: 4px;">
                                                <?php echo render_icon('Trash2', 12); ?> Delete
                                            </button>
                                        </form>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p style="font-size: 13px; color: #64748b; margin-top: 0; margin-bottom: 10px;">No gallery screenshots uploaded for this project yet.</p>
                        <?php endif; ?>
                        <input type="file" name="gallery_images[]" accept="image/*" multiple>
                        <small style="color: #64748b; font-size: 12px; display: block; margin-top: 4px;">Select multiple images to add new screenshots to the project gallery slider.</small>
                    </div>
                </div>

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
