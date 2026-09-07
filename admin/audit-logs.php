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
    <form method="get" action="" style="display:flex; flex-wrap:wrap; gap:12px; align-items:center;">
        <input type="text" name="action_filter" placeholder="Filter by action (e.g. DELETE)..." value="<?php echo htmlspecialchars($actionFilter); ?>" style="padding:8px 12px; border-radius:8px; background:rgba(15,23,42,0.8); border:1px solid rgba(255,255,255,0.15); color:#fff; font-size:13px;">
        
        <input type="date" name="date_from" value="<?php echo htmlspecialchars($dateFrom); ?>" title="From Date" style="padding:8px 12px; border-radius:8px; background:rgba(15,23,42,0.8); border:1px solid rgba(255,255,255,0.15); color:#fff; font-size:13px;">
        <input type="date" name="date_to" value="<?php echo htmlspecialchars($dateTo); ?>" title="To Date" style="padding:8px 12px; border-radius:8px; background:rgba(15,23,42,0.8); border:1px solid rgba(255,255,255,0.15); color:#fff; font-size:13px;">

        <button type="submit" class="btn small primary">Filter Logs</button>
        <?php if ($actionFilter || $dateFrom || $dateTo || $adminFilter): ?>
            <a href="audit-logs.php" class="btn small ghost">Reset</a>
        <?php endif; ?>
    </form>
</div>

<div class="table-card glass-card">
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Timestamp</th>
                <th>User / Actor</th>
                <th>Action</th>
                <th>Resource</th>
                <th>IP Address</th>
                <th>Metadata</th>
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
                        <td><code>#<?php echo $log['id']; ?></code></td>
                        <td style="white-space:nowrap; font-size:13px; color:#cbd5e1;"><?php echo date('Y-m-d H:i:s', strtotime($log['created_at'])); ?></td>
                        <td>
                            <?php if ($log['user_name']): ?>
                                <strong><?php echo htmlspecialchars($log['user_name']); ?></strong><br>
                                <small style="color:#94a3b8;"><?php echo htmlspecialchars($log['user_email']); ?></small>
                            <?php else: ?>
                                <span style="color:#64748b;">User #<?php echo htmlspecialchars((string)($log['user_id'] ?? 'System')); ?></span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="badge-pill" style="font-size:11px; padding:3px 8px; border-radius:6px; background:rgba(0,135,255,0.15); color:#38bdf8; font-weight:700;">
                                <?php echo htmlspecialchars($log['action']); ?>
                            </span>
                        </td>
                        <td>
                            <?php echo htmlspecialchars($log['target_type'] ?? 'system'); ?>
                            <?php if (!empty($log['target_id'])): ?>
                                <code>(<?php echo htmlspecialchars((string)$log['target_id']); ?>)</code>
                            <?php endif; ?>
                        </td>
                        <td><code style="font-size:12px; color:#a7f3d0;"><?php echo htmlspecialchars($log['ip_address'] ?? '127.0.0.1'); ?></code></td>
                        <td style="max-width:260px; overflow-x:auto;">
                            <?php if (!empty($log['details'])): ?>
                                <pre style="margin:0; font-size:11px; background:rgba(0,0,0,0.3); padding:4px 8px; border-radius:6px; color:#e2e8f0; white-space:pre-wrap; word-break:break-all;"><?php echo htmlspecialchars($log['details']); ?></pre>
                            <?php else: ?>
                                <span style="color:#64748b;">-</span>
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
