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
        $stmt = $pdo->prepare("DELETE FROM blog_posts WHERE id = ?");
        $stmt->execute([$id]);
        $notice = 'Blog post deleted.';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && (!isset($_POST['action']) || $_POST['action'] === 'save')) {
    $id = !empty($_POST['id']) ? (int)$_POST['id'] : null;
    $title = trim($_POST['title'] ?? '');
    $slug = trim($_POST['slug'] ?? '');
    if (empty($slug)) {
        $slug = slugify($title);
    }
    $category = trim($_POST['category'] ?? 'General');
    $content = trim($_POST['content'] ?? '');
    $author = trim($_POST['author'] ?? 'Kendat Integrated Services');
    $publishedAt = !empty($_POST['published_at']) ? $_POST['published_at'] : date('Y-m-d');
    $status = $_POST['status'] ?? 'draft';

    if (empty($title) || empty($content)) {
        $error = 'Title and content are required.';
    } else {
        if ($id) {
            $stmt = $pdo->prepare("UPDATE blog_posts SET title=?, slug=?, category=?, content=?, author=?, published_at=?, status=? WHERE id=?");
            $stmt->execute([$title, $slug, $category, $content, $author, $publishedAt, $status, $id]);
            $notice = 'Blog post updated successfully.';
        } else {
            $stmt = $pdo->prepare("INSERT INTO blog_posts (title, slug, category, content, author, published_at, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$title, $slug, $category, $content, $author, $publishedAt, $status]);
            $notice = 'Blog post created successfully.';
        }
    }
}

$rows = $pdo->query("SELECT * FROM blog_posts ORDER BY id DESC")->fetchAll();

$editRecord = null;
if (isset($_GET['edit'])) {
    $eStmt = $pdo->prepare("SELECT * FROM blog_posts WHERE id = ?");
    $eStmt->execute([(int)$_GET['edit']]);
    $editRecord = $eStmt->fetch();
}
?>

<div class="admin-page-head">
    <div>
        <span class="eyebrow">Admin module</span>
        <h1>Manage Blog Posts</h1>
        <p>Draft and publish company news, technical updates, and announcements.</p>
    </div>
    <div class="admin-head-actions">
        <a class="btn primary" href="blog.php?action=new"><?php echo render_icon('Plus'); ?>Add Blog Post</a>
    </div>
</div>

<?php if ($notice): ?><div class="admin-notice success"><?php echo render_icon('CheckCircle2'); ?><?php echo htmlspecialchars($notice); ?></div><?php endif; ?>
<?php if ($error): ?><div class="admin-notice error"><?php echo render_icon('TriangleAlert'); ?><?php echo htmlspecialchars($error); ?></div><?php endif; ?>

<div class="table-card glass-card">
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Title</th>
                <th>Category</th>
                <th>Author</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($rows as $row): ?>
                <tr>
                    <td><?php echo $row['id']; ?></td>
                    <td><strong><?php echo htmlspecialchars($row['title']); ?></strong></td>
                    <td><?php echo htmlspecialchars($row['category'] ?? '-'); ?></td>
                    <td><?php echo htmlspecialchars($row['author']); ?></td>
                    <td><span class="status"><?php echo htmlspecialchars($row['status']); ?></span></td>
                    <td>
                        <div class="row-actions">
                            <a class="icon-btn" href="blog.php?edit=<?php echo $row['id']; ?>" title="Edit"><?php echo render_icon('Pencil'); ?></a>
                            <form method="post" action="blog.php" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete blog post &quot;<?php echo htmlspecialchars($row['title'], ENT_QUOTES); ?>&quot;? This action cannot be undone.');">
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
        <form class="admin-modal" method="post" action="blog.php">
            <?php echo csrf_input(); ?>
            <input type="hidden" name="action" value="save">
            <input type="hidden" name="id" value="<?php echo $editRecord['id'] ?? ''; ?>">
            <div class="modal-head">
                <div>
                    <span class="eyebrow"><?php echo $editRecord ? 'Edit record' : 'New record'; ?></span>
                    <h2>Blog Posts</h2>
                </div>
                <a class="icon-btn" href="blog.php"><?php echo render_icon('X'); ?></a>
            </div>

            <div class="admin-form modal-form">
                <label>Title<input name="title" value="<?php echo htmlspecialchars($editRecord['title'] ?? ''); ?>" required></label>
                <label>Slug<input name="slug" value="<?php echo htmlspecialchars($editRecord['slug'] ?? ''); ?>"></label>
                <label>Category<input name="category" value="<?php echo htmlspecialchars($editRecord['category'] ?? 'AI'); ?>"></label>
                <label>Author<input name="author" value="<?php echo htmlspecialchars($editRecord['author'] ?? 'Kendat Integrated Services'); ?>"></label>
                <label>Publish Date<input name="published_at" type="date" value="<?php echo htmlspecialchars($editRecord['published_at'] ?? date('Y-m-d')); ?>"></label>
                <label>Status
                    <select name="status">
                        <option value="draft" <?php echo ($editRecord['status'] ?? '') === 'draft' ? 'selected' : ''; ?>>Draft</option>
                        <option value="published" <?php echo ($editRecord['status'] ?? '') === 'published' ? 'selected' : ''; ?>>Published</option>
                        <option value="archived" <?php echo ($editRecord['status'] ?? '') === 'archived' ? 'selected' : ''; ?>>Archived</option>
                    </select>
                </label>
                <label style="grid-column:1 / -1;">Article Content<textarea name="content" required><?php echo htmlspecialchars($editRecord['content'] ?? ''); ?></textarea></label>
            </div>

            <div class="modal-actions">
                <a class="btn ghost" href="blog.php">Cancel</a>
                <button class="btn primary" type="submit"><?php echo render_icon('Save'); ?>Save Changes</button>
            </div>
        </form>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
