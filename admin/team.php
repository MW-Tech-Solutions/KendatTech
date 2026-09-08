<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/admin_header.php';
require_once __DIR__ . '/../includes/upload_helper.php';

require_csrf_token();

$pdo = get_db();
$notice = '';
$error = '';

// Handle Delete via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $id = (int)($_POST['id'] ?? 0);
    if ($id > 0) {
        $stmt = $pdo->prepare("DELETE FROM team_members WHERE id = ?");
        $stmt->execute([$id]);
        audit_log('ADMIN_TEAM_MEMBER_DELETED', 'team_member', (string)$id);
        $notice = 'Team member deleted successfully.';
    }
}

// Handle Status Toggle via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'toggle_status') {
    $id = (int)($_POST['id'] ?? 0);
    $newStatus = $_POST['new_status'] === 'active' ? 'active' : 'hidden';
    if ($id > 0) {
        $stmt = $pdo->prepare("UPDATE team_members SET status = ? WHERE id = ?");
        $stmt->execute([$newStatus, $id]);
        audit_log('ADMIN_TEAM_STATUS_TOGGLED', 'team_member', (string)$id, ['status' => $newStatus]);
        $notice = "Team member status updated to {$newStatus}.";
    }
}

// Handle Create / Update Form Submission via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && (!isset($_POST['action']) || $_POST['action'] === 'save')) {
    $id = !empty($_POST['id']) ? (int)$_POST['id'] : null;
    $name = trim($_POST['name'] ?? '');
    $roleTitle = trim($_POST['role_title'] ?? '');
    $specialties = trim($_POST['specialties'] ?? '');
    $bio = trim($_POST['bio'] ?? '');
    $linkedinUrl = trim($_POST['linkedin_url'] ?? '');
    $githubUrl = trim($_POST['github_url'] ?? '');
    $sortOrder = (int)($_POST['sort_order'] ?? 0);
    $status = $_POST['status'] ?? 'active';

    if (empty($name) || empty($roleTitle)) {
        $error = 'Full Name and Role / Title are required fields.';
    } else {
        $photoPath = secure_file_upload('photo', 'team');

        if ($id) {
            if ($photoPath) {
                $stmt = $pdo->prepare("UPDATE team_members SET name=?, role_title=?, specialties=?, photo=?, bio=?, linkedin_url=?, github_url=?, sort_order=?, status=? WHERE id=?");
                $stmt->execute([$name, $roleTitle, $specialties, $photoPath, $bio, $linkedinUrl, $githubUrl, $sortOrder, $status, $id]);
            } else {
                $stmt = $pdo->prepare("UPDATE team_members SET name=?, role_title=?, specialties=?, bio=?, linkedin_url=?, github_url=?, sort_order=?, status=? WHERE id=?");
                $stmt->execute([$name, $roleTitle, $specialties, $bio, $linkedinUrl, $githubUrl, $sortOrder, $status, $id]);
            }
            audit_log('ADMIN_TEAM_MEMBER_UPDATED', 'team_member', (string)$id, ['name' => $name, 'role' => $roleTitle]);
            $notice = 'Team member updated successfully.';
        } else {
            $stmt = $pdo->prepare("INSERT INTO team_members (name, role_title, specialties, photo, bio, linkedin_url, github_url, sort_order, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$name, $roleTitle, $specialties, $photoPath, $bio, $linkedinUrl, $githubUrl, $sortOrder, $status]);
            $newId = (int)$pdo->lastInsertId();
            audit_log('ADMIN_TEAM_MEMBER_CREATED', 'team_member', (string)$newId, ['name' => $name, 'role' => $roleTitle]);
            $notice = 'New team member added successfully.';
        }
    }
}

// Search and Filtering
$query = trim($_GET['q'] ?? '');
$statusFilter = $_GET['status'] ?? 'all';

$sqlWhere = " WHERE 1=1";
$params = [];

if (!empty($query)) {
    $sqlWhere .= " AND (name LIKE ? OR role_title LIKE ? OR specialties LIKE ?)";
    $params[] = "%{$query}%";
    $params[] = "%{$query}%";
    $params[] = "%{$query}%";
}

if ($statusFilter !== 'all') {
    $sqlWhere .= " AND status = ?";
    $params[] = $statusFilter;
}

$stmt = $pdo->prepare("SELECT * FROM team_members {$sqlWhere} ORDER BY sort_order ASC, id ASC");
$stmt->execute($params);
$teamMembers = $stmt->fetchAll();

// Edit Mode Record
$editRecord = null;
if (isset($_GET['edit'])) {
    $editId = (int)$_GET['edit'];
    $eStmt = $pdo->prepare("SELECT * FROM team_members WHERE id = ? LIMIT 1");
    $eStmt->execute([$editId]);
    $editRecord = $eStmt->fetch();
}
?>

<div class="admin-page-head">
    <div>
        <span class="eyebrow">Corporate Leadership & Development Team</span>
        <h1>Team Members Management</h1>
        <p>Dynamically manage executives, lead developers, AI architects, and team members displayed on the About page.</p>
    </div>
    <a class="btn primary" href="team.php?action=new">
        <?php echo render_icon('UserPlus', 18); ?> Add Team Member
    </a>
</div>

<?php if ($notice): ?><div class="admin-notice success"><?php echo render_icon('CheckCircle2'); ?><?php echo htmlspecialchars($notice); ?></div><?php endif; ?>
<?php if ($error): ?><div class="admin-notice danger"><?php echo render_icon('AlertCircle'); ?><?php echo htmlspecialchars($error); ?></div><?php endif; ?>

<div class="toolbar advanced-toolbar">
    <form method="get" action="" style="display:flex; flex-wrap:wrap; gap:12px; align-items:center; width:100%;">
        <input type="text" name="q" placeholder="Search by name, role, or title..." value="<?php echo htmlspecialchars($query); ?>" style="padding:9px 14px; border-radius:10px; background:rgba(15,23,42,0.8); border:1px solid rgba(255,255,255,0.15); color:#fff; font-size:14px; flex:1; min-width:200px;">
        <select name="status" onchange="this.form.submit()" style="padding:9px 14px; border-radius:10px; background:rgba(15,23,42,0.8); border:1px solid rgba(255,255,255,0.15); color:#fff; font-size:14px;">
            <option value="all" <?php echo $statusFilter==='all'?'selected':''; ?>>All Statuses</option>
            <option value="active" <?php echo $statusFilter==='active'?'selected':''; ?>>Active Only</option>
            <option value="hidden" <?php echo $statusFilter==='hidden'?'selected':''; ?>>Hidden Only</option>
        </select>
        <button type="submit" class="btn small primary">Filter</button>
        <?php if ($query || $statusFilter !== 'all'): ?>
            <a href="team.php" class="btn small ghost">Clear</a>
        <?php endif; ?>
    </form>
</div>

<div class="table-card glass-card">
    <table>
        <thead>
            <tr>
                <th>Photo</th>
                <th>Name & Title</th>
                <th>Specialties / Sub-tags</th>
                <th>Order</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($teamMembers)): ?>
                <tr>
                    <td colspan="6" style="text-align:center; padding:32px; color:#94a3b8;">No team members found. Click "Add Team Member" to create your first team record.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($teamMembers as $m): 
                    $photoUrl = !empty($m['photo']) ? upload_asset_url($m['photo']) : null;
                ?>
                    <tr>
                        <td>
                            <div style="width:48px; height:48px; border-radius:12px; overflow:hidden; background:rgba(0,135,255,0.15); display:grid; place-items:center; border:1px solid rgba(0,229,255,0.3);">
                                <?php if ($photoUrl): ?>
                                    <img src="<?php echo htmlspecialchars($photoUrl); ?>" alt="<?php echo htmlspecialchars($m['name']); ?>" style="width:100%; height:100%; object-fit:cover;">
                                <?php else: ?>
                                    <?php echo render_icon('User', 22); ?>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td>
                            <strong style="font-size:15px; color:#ffffff;"><?php echo htmlspecialchars($m['name']); ?></strong><br>
                            <small style="color:#00e5ff; font-weight:700;"><?php echo htmlspecialchars($m['role_title']); ?></small>
                        </td>
                        <td>
                            <span style="font-size:12px; color:#cbd5e1; font-weight:600; letter-spacing:0.04em;">
                                <?php echo htmlspecialchars($m['specialties'] ?? 'N/A'); ?>
                            </span>
                        </td>
                        <td><code><?php echo $m['sort_order']; ?></code></td>
                        <td>
                            <?php if ($m['status'] === 'active'): ?>
                                <span class="status" style="background:rgba(34,197,94,0.15); color:#4ade80;">ACTIVE</span>
                            <?php else: ?>
                                <span class="status" style="background:rgba(239,68,68,0.15); color:#f87171;">HIDDEN</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="row-actions" style="display:flex; gap:8px;">
                                <a class="icon-btn" href="team.php?edit=<?php echo $m['id']; ?>" title="Edit Team Member"><?php echo render_icon('Pencil'); ?></a>
                                
                                <form method="post" action="team.php" style="display:inline;">
                                    <?php echo csrf_input(); ?>
                                    <input type="hidden" name="action" value="toggle_status">
                                    <input type="hidden" name="id" value="<?php echo $m['id']; ?>">
                                    <input type="hidden" name="new_status" value="<?php echo $m['status']==='active'?'hidden':'active'; ?>">
                                    <button type="submit" class="icon-btn" title="<?php echo $m['status']==='active'?'Hide from public website':'Make active'; ?>">
                                        <?php echo render_icon($m['status']==='active'?'EyeOff':'Eye'); ?>
                                    </button>
                                </form>

                                <form method="post" action="team.php" style="display:inline;" onsubmit="return confirm('Delete team member <?php echo htmlspecialchars($m['name']); ?>? This action cannot be undone.');">
                                    <?php echo csrf_input(); ?>
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?php echo $m['id']; ?>">
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

<!-- Modal Form (Create / Edit Team Member) -->
<?php if ($editRecord || (isset($_GET['action']) && $_GET['action'] === 'new')): 
    $mRec = $editRecord ?? [];
    $isEdit = !empty($mRec['id']);
?>
    <div class="modal-backdrop" role="dialog" aria-modal="true">
        <div class="admin-modal" style="max-width:640px;">
            <div class="modal-head">
                <div>
                    <span class="eyebrow"><?php echo $isEdit ? 'Update Team Member' : 'New Team Member'; ?></span>
                    <h2><?php echo $isEdit ? htmlspecialchars($mRec['name']) : 'Add Leadership / Team Member'; ?></h2>
                </div>
                <a class="icon-btn" href="team.php"><?php echo render_icon('X'); ?></a>
            </div>

            <form method="post" action="team.php" enctype="multipart/form-data" style="display:grid; gap:16px; margin-top:16px;">
                <?php echo csrf_input(); ?>
                <input type="hidden" name="action" value="save">
                <?php if ($isEdit): ?>
                    <input type="hidden" name="id" value="<?php echo $mRec['id']; ?>">
                <?php endif; ?>

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:14px;">
                    <label style="display:grid; gap:6px; color:#cbd5e1; font-weight:700; font-size:13px;">Full Name *
                        <input type="text" name="name" required value="<?php echo htmlspecialchars($mRec['name'] ?? ''); ?>" placeholder="e.g. Engr. Muhammad Mukhtar" style="padding:10px; border-radius:8px; background:rgba(15,23,42,0.9); border:1px solid rgba(255,255,255,0.18); color:#fff;">
                    </label>

                    <label style="display:grid; gap:6px; color:#cbd5e1; font-weight:700; font-size:13px;">Role / Title *
                        <input type="text" name="role_title" required value="<?php echo htmlspecialchars($mRec['role_title'] ?? ''); ?>" placeholder="e.g. Chief Executive Officer or Lead Developer" style="padding:10px; border-radius:8px; background:rgba(15,23,42,0.9); border:1px solid rgba(255,255,255,0.18); color:#fff;">
                    </label>
                </div>

                <label style="display:grid; gap:6px; color:#cbd5e1; font-weight:700; font-size:13px;">Specialties / Sub-tags (Displayed below Title)
                    <input type="text" name="specialties" value="<?php echo htmlspecialchars($mRec['specialties'] ?? ''); ?>" placeholder="e.g. LEADERSHIP • STRATEGY • IMPACT or SOFTWARE • AI SOLUTIONS" style="padding:10px; border-radius:8px; background:rgba(15,23,42,0.9); border:1px solid rgba(255,255,255,0.18); color:#fff;">
                </label>

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:14px;">
                    <label style="display:grid; gap:6px; color:#cbd5e1; font-weight:700; font-size:13px;">Member Portrait Photo
                        <input type="file" name="photo" accept="image/*" style="padding:8px; border-radius:8px; background:rgba(15,23,42,0.9); border:1px solid rgba(255,255,255,0.18); color:#fff;">
                        <?php if (!empty($mRec['photo'])): ?>
                            <small style="color:#38bdf8;">Current: <?php echo htmlspecialchars(basename($mRec['photo'])); ?></small>
                        <?php endif; ?>
                    </label>

                    <label style="display:grid; gap:6px; color:#cbd5e1; font-weight:700; font-size:13px;">Sort Order
                        <input type="number" name="sort_order" value="<?php echo (int)($mRec['sort_order'] ?? 1); ?>" style="padding:10px; border-radius:8px; background:rgba(15,23,42,0.9); border:1px solid rgba(255,255,255,0.18); color:#fff;">
                    </label>
                </div>

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:14px;">
                    <label style="display:grid; gap:6px; color:#cbd5e1; font-weight:700; font-size:13px;">LinkedIn Profile URL (Optional)
                        <input type="url" name="linkedin_url" value="<?php echo htmlspecialchars($mRec['linkedin_url'] ?? ''); ?>" placeholder="https://linkedin.com/in/username" style="padding:10px; border-radius:8px; background:rgba(15,23,42,0.9); border:1px solid rgba(255,255,255,0.18); color:#fff;">
                    </label>

                    <label style="display:grid; gap:6px; color:#cbd5e1; font-weight:700; font-size:13px;">Status
                        <select name="status" style="padding:10px; border-radius:8px; background:rgba(15,23,42,0.9); border:1px solid rgba(255,255,255,0.18); color:#fff;">
                            <option value="active" <?php echo ($mRec['status']??'active')==='active'?'selected':''; ?>>Active (Visible on Website)</option>
                            <option value="hidden" <?php echo ($mRec['status']??'')==='hidden'?'selected':''; ?>>Hidden</option>
                        </select>
                    </label>
                </div>

                <label style="display:grid; gap:6px; color:#cbd5e1; font-weight:700; font-size:13px;">Short Bio / Highlights (Optional)
                    <textarea name="bio" rows="3" placeholder="Brief summary of professional background or focus area..." style="padding:10px; border-radius:8px; background:rgba(15,23,42,0.9); border:1px solid rgba(255,255,255,0.18); color:#fff; resize:vertical;"><?php echo htmlspecialchars($mRec['bio'] ?? ''); ?></textarea>
                </label>

                <div class="modal-actions" style="margin-top:10px; display:flex; gap:10px; justify-content:flex-end;">
                    <a class="btn ghost" href="team.php">Cancel</a>
                    <button type="submit" class="btn primary"><?php echo $isEdit ? 'Save Changes' : 'Create Team Member'; ?></button>
                </div>
            </form>
        </div>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
