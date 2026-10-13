<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/admin_header.php';

$pdo = get_db();

$actionFilter = trim($_GET['action_filter'] ?? '');
$adminFilter = trim($_GET['admin_filter'] ?? '');
$dateFrom = trim($_GET['date_from'] ?? '');
$dateTo = trim($_GET['date_to'] ?? '');

$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 25;
$offset = ($page - 1) * $perPage;

$whereClauses = ["1=1"];
$params = [];

if (!empty($actionFilter)) {
    $whereClauses[] = "action LIKE ?";
    $params[] = '%' . $actionFilter . '%';
}

if (!empty($adminFilter)) {
    $whereClauses[] = "user_id = ?";
    $params[] = (int)$adminFilter;
}

if (!empty($dateFrom)) {
    $whereClauses[] = "created_at >= ?";
    $params[] = $dateFrom . ' 00:00:00';
}

if (!empty($dateTo)) {
    $whereClauses[] = "created_at <= ?";
    $params[] = $dateTo . ' 23:59:59';
}

$sqlWhere = " WHERE " . implode(" AND ", $whereClauses);

// Count Total
$countStmt = $pdo->prepare("SELECT COUNT(*) FROM audit_logs {$sqlWhere}");
$countStmt->execute($params);
$totalRecords = (int)$countStmt->fetchColumn();
$totalPages = max(1, (int)ceil($totalRecords / $perPage));

// Fetch Records
$sql = "SELECT a.*, u.full_name as user_name, u.email as user_email 
        FROM audit_logs a 
        LEFT JOIN users u ON a.user_id = u.id 
        {$sqlWhere} 
        ORDER BY a.id DESC 
        LIMIT {$perPage} OFFSET {$offset}";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$logs = $stmt->fetchAll();
?>

<div class="admin-page-head">
    <div>
        <span class="eyebrow">Enterprise Governance</span>
        <h1>Admin Audit Trail</h1>
        <p>Immutable record of all administrative operations, security events, and system mutations.</p>
    </div>
</div>

<div class="toolbar advanced-toolbar" style="margin-bottom:20px;">
    <form method="get" action="" style="display:flex; flex-wrap:wrap; gap:12px; align-items:center; width:100%;">
        <input type="text" name="action_filter" placeholder="Filter by action (e.g. DELETE)..." value="<?php echo htmlspecialchars($actionFilter); ?>" style="flex:1; min-width:200px;">
        <input type="date" name="date_from" value="<?php echo htmlspecialchars($dateFrom); ?>" title="From Date">
        <input type="date" name="date_to" value="<?php echo htmlspecialchars($dateTo); ?>" title="To Date">

        <button type="submit" class="btn small primary"><?php echo render_icon('Search', 14); ?> Filter Logs</button>
        <?php if ($actionFilter || $dateFrom || $dateTo || $adminFilter): ?>
            <a href="audit-logs.php" class="btn small ghost">Reset</a>
        <?php endif; ?>
    </form>
</div>

<div class="admin-projects-card">
    <table class="admin-projects-table">
        <thead>
            <tr>
                <th style="width: 60px;">ID</th>
                <th style="width: 170px;">Timestamp</th>
                <th style="min-width: 180px;">User / Actor</th>
                <th style="width: 160px;">Action</th>
                <th style="width: 140px;">Resource</th>
                <th style="width: 130px;">IP Address</th>
                <th style="min-width: 200px;">Metadata</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($logs)): ?>
                <tr>
                    <td colspan="7" style="text-align:center; padding:32px; color:#94a3b8;">No audit logs match your filter criteria.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($logs as $log): ?>
                    <tr>
                        <td><span class="admin-id-badge">#<?php echo $log['id']; ?></span></td>
                        <td><span style="font-size:12.5px; color:#475569; font-weight:600;"><?php echo date('d M Y, H:i', strtotime($log['created_at'])); ?></span></td>
                        <td>
                            <?php if ($log['user_name']): ?>
                                <strong style="font-size:13.5px; color:#0f172a; display:block;"><?php echo htmlspecialchars($log['user_name']); ?></strong>
                                <small style="color:#64748b;"><?php echo htmlspecialchars($log['user_email']); ?></small>
                            <?php else: ?>
                                <span style="font-size:13px; color:#64748b; font-weight:600;">System Admin #<?php echo htmlspecialchars((string)($log['user_id'] ?? '1')); ?></span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="admin-status-pill status-ongoing">
                                <?php echo htmlspecialchars($log['action']); ?>
                            </span>
                        </td>
                        <td>
                            <span class="admin-cat-badge">
                                <?php echo htmlspecialchars($log['resource_type'] ?? 'system'); ?>
                                <?php if (!empty($log['resource_id'])): ?>
                                    #<?php echo htmlspecialchars((string)$log['resource_id']); ?>
                                <?php endif; ?>
                            </span>
                        </td>
                        <td><code style="font-size:12px; color:#0B5D3B; background:#ECFDF5; padding:2px 6px; border-radius:4px; font-weight:700;"><?php echo htmlspecialchars($log['ip_address'] ?? '127.0.0.1'); ?></code></td>
                        <td>
                            <?php if (!empty($log['details'])): ?>
                                <span style="font-size:11.5px; color:#475569; word-break:break-all;"><?php echo htmlspecialchars($log['details']); ?></span>
                            <?php else: ?>
                                <span style="color:#94a3b8;">-</span>
                            <?php endif; ?>
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
            <a href="audit-logs.php?page=<?php echo $i; ?>&action_filter=<?php echo urlencode($actionFilter); ?>&date_from=<?php echo urlencode($dateFrom); ?>&date_to=<?php echo urlencode($dateTo); ?>" class="btn small <?php echo $i===$page?'primary':'ghost'; ?>"><?php echo $i; ?></a>
        <?php endfor; ?>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
