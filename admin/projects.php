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
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && strpos($_POST['action'], 'delete_gallery_image_') === 0) {
    $imageId = (int)str_replace('delete_gallery_image_', '', $_POST['action']);
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
    $projectId = !empty($_POST['id']) ? (int)$_POST['id'] : 0;
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

// Handle Form Submission (Add/Edit Project - Save Changes)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save') {
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

<div class="admin-projects-card">
    <table class="admin-projects-table">
        <thead>
            <tr>
                <th style="width: 60px;">ID</th>
                <th style="width: 90px;">Cover</th>
                <th style="min-width: 260px;">Title & Details</th>
                <th style="width: 160px;">Category</th>
                <th style="width: 120px;">Demo Link</th>
                <th style="width: 120px;">Status</th>
                <th style="width: 130px; text-align: right;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($rows as $row): ?>
                <tr>
                    <td><span class="admin-id-badge">#<?php echo $row['id']; ?></span></td>
                    <td>
                        <?php if (!empty($row['main_image'])): ?>
                            <div class="admin-cover-thumb-wrap">
                                <img class="admin-cover-thumb" src="<?php echo htmlspecialchars(upload_asset_url($row['main_image'])); ?>" alt="Cover">
                            </div>
                        <?php else: ?>
                            <div class="admin-cover-thumb-empty" title="No cover image">
                                <?php echo render_icon('ImageOff', 18); ?>
                            </div>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div class="admin-project-title-box">
                            <span class="admin-project-title-text"><?php echo htmlspecialchars($row['title']); ?></span>
                            <?php if (!empty($row['technologies'])): ?>
                                <span class="admin-project-tech-sub"><?php echo htmlspecialchars($row['technologies']); ?></span>
                            <?php endif; ?>
                        </div>
                    </td>
                    <td><span class="admin-cat-badge"><?php echo htmlspecialchars($row['category']); ?></span></td>
                    <td>
                        <?php if (!empty($row['demo_link']) && $row['demo_link'] !== '#'): ?>
                            <a class="admin-demo-pill" href="<?php echo htmlspecialchars($row['demo_link']); ?>" target="_blank" rel="noopener">
                                <?php echo render_icon('ExternalLink', 12); ?> Open
                            </a>
                        <?php else: ?>
                            <span class="admin-demo-muted">Not set</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php
                            $st = strtolower($row['status']);
                            $dotClass = 'status-dot-green';
                            $statusClass = 'status-completed';
                            if ($st === 'ongoing') {
                                $dotClass = 'status-dot-blue';
                                $statusClass = 'status-ongoing';
                            } elseif ($st === 'upcoming') {
                                $dotClass = 'status-dot-amber';
                                $statusClass = 'status-upcoming';
                            }
                        ?>
                        <span class="admin-status-pill <?php echo $statusClass; ?>">
                            <span class="<?php echo $dotClass; ?>"></span>
                            <?php echo htmlspecialchars(ucfirst($row['status'])); ?>
                        </span>
                    </td>
                    <td style="text-align: right;">
                        <div class="admin-actions-group">
                            <a class="btn-action-edit" href="projects.php?edit=<?php echo $row['id']; ?>" title="Edit Project & Manage Images">
                                <?php echo render_icon('Pencil', 14); ?>
                            </a>
                            <form method="post" action="projects.php" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete project &quot;<?php echo htmlspecialchars($row['title'], ENT_QUOTES); ?>&quot;? This action cannot be undone.');">
                                <?php echo csrf_input(); ?>
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                <button type="submit" class="btn-action-delete" title="Delete Project">
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
        <form class="admin-modal admin-modal-redesigned" method="post" action="projects.php<?php echo $editRecord ? '?edit=' . $editRecord['id'] : ''; ?>" enctype="multipart/form-data">
            <?php echo csrf_input(); ?>
            <input type="hidden" name="id" value="<?php echo $editRecord['id'] ?? ''; ?>">
            
            <!-- Sticky Modern Modal Header -->
            <div class="modal-head-redesigned">
                <div class="modal-head-title-wrap">
                    <span class="modal-eyebrow-tag"><?php echo render_icon('FolderGit2', 13); ?> <?php echo $editRecord ? 'Edit Project' : 'New Project'; ?></span>
                    <h2><?php echo htmlspecialchars($editRecord['title'] ?? 'Create Project Record'); ?></h2>
                    <?php if (!empty($editRecord['id'])): ?>
                        <span class="modal-id-badge">#<?php echo $editRecord['id']; ?></span>
                    <?php endif; ?>
                </div>
                <a class="icon-btn" href="projects.php" title="Close Modal"><?php echo render_icon('X', 18); ?></a>
            </div>

            <div class="modal-body-content">
                <!-- Section 1: Overview & Meta -->
                <div class="modal-section-card">
                    <div class="modal-section-title">
                        <?php echo render_icon('Layers', 16); ?> 1. Overview & General Info
                    </div>
                    <div class="modal-fields-grid">
                        <label class="modal-form-label">
                            <span class="label-text">Project Title *</span>
                            <input name="title" value="<?php echo htmlspecialchars($editRecord['title'] ?? ''); ?>" required placeholder="e.g. KendatPay VTU Portal">
                        </label>
                        <label class="modal-form-label">
                            <span class="label-text">URL Slug</span>
                            <input name="slug" value="<?php echo htmlspecialchars($editRecord['slug'] ?? ''); ?>" placeholder="e.g. kendatpay-vtu-portal">
                        </label>
                        <label class="modal-form-label">
                            <span class="label-text">Category *</span>
                            <input name="category" value="<?php echo htmlspecialchars($editRecord['category'] ?? 'School Portal'); ?>" required placeholder="e.g. FinTech & Telecom">
                        </label>
                        <label class="modal-form-label">
                            <span class="label-text">Client Name</span>
                            <input name="client_name" value="<?php echo htmlspecialchars($editRecord['client_name'] ?? ''); ?>" placeholder="e.g. DOOTOR Enterprises">
                        </label>
                        <label class="modal-form-label modal-field-full">
                            <span class="label-text">Completion Date</span>
                            <input name="completion_date" type="date" value="<?php echo htmlspecialchars($editRecord['completion_date'] ?? date('Y-m-d')); ?>">
                        </label>
                    </div>
                </div>

                <!-- Section 2: Visibility, Status & Demo -->
                <div class="modal-section-card">
                    <div class="modal-section-title">
                        <?php echo render_icon('Globe', 16); ?> 2. Visibility & Links
                    </div>
                    <div class="modal-fields-grid">
                        <label class="modal-form-label">
                            <span class="label-text">Project Status</span>
                            <select name="status">
                                <option value="completed" <?php echo ($editRecord['status'] ?? '') === 'completed' ? 'selected' : ''; ?>>Completed</option>
                                <option value="ongoing" <?php echo ($editRecord['status'] ?? '') === 'ongoing' ? 'selected' : ''; ?>>Ongoing</option>
                                <option value="upcoming" <?php echo ($editRecord['status'] ?? '') === 'upcoming' ? 'selected' : ''; ?>>Upcoming</option>
                            </select>
                        </label>
                        <label class="modal-form-label">
                            <span class="label-text">Featured on Homepage</span>
                            <select name="featured">
                                <option value="1" <?php echo !empty($editRecord['featured']) ? 'selected' : ''; ?>>Yes (Show on Homepage)</option>
                                <option value="0" <?php echo empty($editRecord['featured']) ? 'selected' : ''; ?>>No (Hide from Featured)</option>
                            </select>
                        </label>
                        <label class="modal-form-label">
                            <span class="label-text">Live Demo Link URL</span>
                            <input name="demo_link" type="url" placeholder="https://example.com/demo" value="<?php echo htmlspecialchars(($editRecord['demo_link'] ?? '') === '#' ? '' : ($editRecord['demo_link'] ?? '')); ?>">
                        </label>
                        <label class="modal-form-label">
                            <span class="label-text">Technologies Used</span>
                            <input name="technologies" value="<?php echo htmlspecialchars($editRecord['technologies'] ?? ''); ?>" placeholder="e.g. PHP, MySQL, JavaScript, Tailwind">
                        </label>
                    </div>
                </div>

                <!-- Section 3: Dedicated Cover & Gallery Image Manager -->
                <div class="modal-section-card">
                    <div class="modal-section-title">
                        <?php echo render_icon('Image', 16); ?> 3. Project Cover & Gallery Manager
                    </div>

                    <!-- Main Cover Image -->
                    <div style="margin-bottom: 20px;">
                        <label class="modal-form-label" style="margin-bottom: 8px;">
                            <span class="label-text">Main Cover Image</span>
                        </label>
                        <?php if (!empty($editRecord['main_image'])): 
                            $mainImgUrl = upload_asset_url($editRecord['main_image']);
                            $fileDisk = __DIR__ . '/../uploads/' . ltrim($editRecord['main_image'], '/');
                            $isBroken = !file_exists($fileDisk) || !is_file($fileDisk);
                        ?>
                            <div class="modal-media-cover-card">
                                <img src="<?php echo htmlspecialchars($mainImgUrl); ?>" alt="Main Cover" class="modal-cover-preview-img">
                                <div style="flex: 1; min-width: 0;">
                                    <strong style="font-size: 13px; color: #0f172a; display: block; word-break: break-all;"><?php echo htmlspecialchars(basename($editRecord['main_image'])); ?></strong>
                                    <?php if ($isBroken): ?>
                                        <span style="font-size: 11px; color: #ef4444; font-weight: 700;">⚠ Missing File on Disk (Fallback placeholder in use)</span>
                                    <?php else: ?>
                                        <span style="font-size: 11px; color: #16a34a; font-weight: 700;">✓ Active File</span>
                                    <?php endif; ?>
                                </div>
                                <button type="submit" name="action" value="delete_main_image" class="btn-action-delete" title="Delete Cover Image" onclick="return confirm('Remove this main cover image?');">
                                    <?php echo render_icon('Trash2', 14); ?>
                                </button>
                            </div>
                        <?php endif; ?>
                        <input type="file" name="main_image" accept="image/*">
                        <small style="color: #64748b; font-size: 11px; display: block; margin-top: 4px;">Upload a new image file to set or replace the project's primary cover photo.</small>
                    </div>

                    <!-- Additional Gallery Screenshots -->
                    <div>
                        <label class="modal-form-label" style="margin-bottom: 8px;">
                            <span class="label-text">Gallery Screenshots (<?php echo count($galleryRecordImages); ?> uploaded)</span>
                        </label>
                        <?php if (!empty($galleryRecordImages)): ?>
                            <div class="modal-gallery-preview-grid">
                                <?php foreach ($galleryRecordImages as $gImg): 
                                    $gImgUrl = upload_asset_url($gImg['image_path']);
                                    $gDisk = __DIR__ . '/../uploads/' . ltrim($gImg['image_path'], '/');
                                    $gBroken = !file_exists($gDisk) || !is_file($gDisk);
                                ?>
                                    <div class="gallery-preview-item">
                                        <div class="gallery-img-wrapper">
                                            <img src="<?php echo htmlspecialchars($gImgUrl); ?>" alt="Screenshot" class="gallery-preview-img">
                                            <button type="submit" name="action" value="delete_gallery_image_<?php echo $gImg['id']; ?>" class="gallery-delete-overlay-btn" title="Delete Screenshot" onclick="return confirm('Delete this screenshot image?');">
                                                <?php echo render_icon('Trash2', 13); ?>
                                            </button>
                                        </div>
                                        <div class="gallery-item-footer">
                                            <?php if ($gBroken): ?>
                                                <span class="gallery-status-broken">⚠ Missing</span>
                                            <?php else: ?>
                                                <span class="gallery-item-filename" title="<?php echo htmlspecialchars(basename($gImg['image_path'])); ?>"><?php echo htmlspecialchars(basename($gImg['image_path'])); ?></span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p style="font-size: 12px; color: #94a3b8; font-style: italic; margin-top: 0; margin-bottom: 10px;">No additional screenshot images uploaded yet.</p>
                        <?php endif; ?>
                        <input type="file" name="gallery_images[]" accept="image/*" multiple>
                        <small style="color: #64748b; font-size: 11px; display: block; margin-top: 4px;">Hold Ctrl/Cmd to select multiple images for the interactive project slider.</small>
                    </div>
                </div>

                <!-- Section 4: Descriptions & Features -->
                <div class="modal-section-card">
                    <div class="modal-section-title">
                        <?php echo render_icon('FileText', 16); ?> 4. Descriptions & Features
                    </div>
                    <div class="modal-fields-grid">
                        <label class="modal-form-label modal-field-full">
                            <span class="label-text">Short Description *</span>
                            <textarea name="short_description" required placeholder="Brief summary shown on project cards..." style="min-height: 80px;"><?php echo htmlspecialchars($editRecord['short_description'] ?? ''); ?></textarea>
                        </label>
                        <label class="modal-form-label modal-field-full">
                            <span class="label-text">Full Detailed Description</span>
                            <textarea name="full_description" placeholder="Comprehensive project case study and scope of work..." style="min-height: 140px;"><?php echo htmlspecialchars($editRecord['full_description'] ?? ''); ?></textarea>
                        </label>
                        <label class="modal-form-label modal-field-full">
                            <span class="label-text">Key Features (One feature per line)</span>
                            <textarea name="features" placeholder="Automated Airtime & Data VTU&#10;Instant Wallet Funding System&#10;Real-time Transaction Reporting" style="min-height: 110px;"><?php echo htmlspecialchars($editRecord['features'] ?? ''); ?></textarea>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Sticky Modern Modal Footer -->
            <div class="modal-footer-bar">
                <a class="btn ghost" href="projects.php" style="background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1;">Cancel</a>
                <button class="btn primary" type="submit" name="action" value="save">
                    <?php echo render_icon('Save', 14); ?> Save Changes
                </button>
            </div>
        </form>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
