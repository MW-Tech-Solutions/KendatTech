<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/admin_header.php';
require_once __DIR__ . '/../database/migrator.php';

require_csrf_token();

$pdo = get_db();
$notice = '';
$error = '';
$selectedTable = trim($_GET['table'] ?? '');

// 1. Handle Run Migrations Action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'run_migrations') {
    try {
        $migrator = new Migrator($pdo);
        $logs = $migrator->run();
        audit_log('ADMIN_DATABASE_MIGRATIONS_RUN', 'database', 'system');
        if (empty($logs)) {
            $notice = 'All database tables and migrations are already up to date.';
        } else {
            $notice = 'Migrations executed successfully: ' . implode(', ', $logs);
        }
    } catch (Throwable $e) {
        $error = 'Migration error: ' . $e->getMessage();
    }
}

// 2. Handle Optimize / Repair Table
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && in_array($_POST['action'], ['optimize_table', 'repair_table'], true)) {
    $tName = preg_replace('/[^a-zA-Z0-9_]/', '', $_POST['table_name'] ?? '');
    if ($tName) {
        try {
            $cmd = $_POST['action'] === 'optimize_table' ? "OPTIMIZE TABLE `{$tName}`" : "REPAIR TABLE `{$tName}`";
            $pdo->query($cmd);
            audit_log('ADMIN_DATABASE_TABLE_MAINTENANCE', 'table', $tName, ['command' => $cmd]);
            $notice = "Table `{$tName}` maintenance completed successfully.";
        } catch (Throwable $e) {
            $error = "Maintenance error on `{$tName}`: " . $e->getMessage();
        }
    }
}

// 3. Handle Add Column to Table
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_column') {
    $tName = preg_replace('/[^a-zA-Z0-9_]/', '', $_POST['table_name'] ?? '');
    $colName = preg_replace('/[^a-zA-Z0-9_]/', '', $_POST['column_name'] ?? '');
    $colType = strtoupper(trim($_POST['column_type'] ?? 'VARCHAR'));
    $colLength = trim($_POST['column_length'] ?? '');
    $colDefault = trim($_POST['column_default'] ?? '');
    $isNullable = !empty($_POST['is_nullable']) ? 'NULL' : 'NOT NULL';
    $afterCol = preg_replace('/[^a-zA-Z0-9_]/', '', $_POST['after_column'] ?? '');

    $allowedTypes = ['VARCHAR', 'TEXT', 'MEDIUMTEXT', 'LONGTEXT', 'INT', 'TINYINT', 'BIGINT', 'DECIMAL', 'DATE', 'DATETIME', 'TIMESTAMP', 'ENUM', 'FLOAT', 'DOUBLE'];
    
    if (!$tName || !$colName || !in_array($colType, $allowedTypes, true)) {
        $error = 'Valid Table Name, Column Name, and allowed Column Type are required.';
    } else {
        try {
            $typeDef = $colType;
            if ($colLength !== '') {
                $typeDef .= "({$colLength})";
            } elseif ($colType === 'VARCHAR') {
                $typeDef .= "(255)";
            } elseif ($colType === 'INT') {
                $typeDef .= "(11)";
            } elseif ($colType === 'TINYINT') {
                $typeDef .= "(1)";
            }

            $sql = "ALTER TABLE `{$tName}` ADD COLUMN `{$colName}` {$typeDef} {$isNullable}";
            if ($colDefault !== '') {
                $sql .= " DEFAULT " . $pdo->quote($colDefault);
            } elseif ($isNullable === 'NULL') {
                $sql .= " DEFAULT NULL";
            }
            if ($afterCol) {
                $sql .= " AFTER `{$afterCol}`";
            }

            $pdo->exec($sql);
            audit_log('ADMIN_DATABASE_COLUMN_ADDED', 'table', $tName, ['column' => $colName, 'sql' => $sql]);
            $notice = "Column `{$colName}` added to table `{$tName}` successfully.";
            $selectedTable = $tName;
        } catch (Throwable $e) {
            $error = "Error adding column: " . $e->getMessage();
        }
    }
}

// 4. Handle Modify Column in Table
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'modify_column') {
    $tName = preg_replace('/[^a-zA-Z0-9_]/', '', $_POST['table_name'] ?? '');
    $colName = preg_replace('/[^a-zA-Z0-9_]/', '', $_POST['column_name'] ?? '');
    $newType = strtoupper(trim($_POST['column_type'] ?? 'VARCHAR'));
    $newLength = trim($_POST['column_length'] ?? '');
    $newDefault = trim($_POST['column_default'] ?? '');
    $isNullable = !empty($_POST['is_nullable']) ? 'NULL' : 'NOT NULL';

    if (!$tName || !$colName) {
        $error = 'Table name and column name are required.';
    } else {
        try {
            $typeDef = $newType;
            if ($newLength !== '') {
                $typeDef .= "({$newLength})";
            } elseif ($newType === 'VARCHAR') {
                $typeDef .= "(255)";
            }

            $sql = "ALTER TABLE `{$tName}` MODIFY COLUMN `{$colName}` {$typeDef} {$isNullable}";
            if ($newDefault !== '') {
                $sql .= " DEFAULT " . $pdo->quote($newDefault);
            } elseif ($isNullable === 'NULL') {
                $sql .= " DEFAULT NULL";
            }

            $pdo->exec($sql);
            audit_log('ADMIN_DATABASE_COLUMN_MODIFIED', 'table', $tName, ['column' => $colName, 'sql' => $sql]);
            $notice = "Column `{$colName}` in table `{$tName}` modified successfully.";
            $selectedTable = $tName;
        } catch (Throwable $e) {
            $error = "Error modifying column: " . $e->getMessage();
        }
    }
}

// 5. Handle Drop Column
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'drop_column') {
    $tName = preg_replace('/[^a-zA-Z0-9_]/', '', $_POST['table_name'] ?? '');
    $colName = preg_replace('/[^a-zA-Z0-9_]/', '', $_POST['column_name'] ?? '');
    if ($tName && $colName) {
        try {
            $sql = "ALTER TABLE `{$tName}` DROP COLUMN `{$colName}`";
            $pdo->exec($sql);
            audit_log('ADMIN_DATABASE_COLUMN_DROPPED', 'table', $tName, ['column' => $colName]);
            $notice = "Column `{$colName}` dropped from table `{$tName}` successfully.";
            $selectedTable = $tName;
        } catch (Throwable $e) {
            $error = "Error dropping column: " . $e->getMessage();
        }
    }
}

// 6. Handle Direct Row Cell Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_row_field') {
    $tName = preg_replace('/[^a-zA-Z0-9_]/', '', $_POST['table_name'] ?? '');
    $rowId = (int)($_POST['row_id'] ?? 0);
    $fieldName = preg_replace('/[^a-zA-Z0-9_]/', '', $_POST['field_name'] ?? '');
    $fieldValue = $_POST['field_value'] ?? '';

    if ($tName && $rowId > 0 && $fieldName) {
        try {
            $upStmt = $pdo->prepare("UPDATE `{$tName}` SET `{$fieldName}` = ? WHERE id = ?");
            $upStmt->execute([$fieldValue, $rowId]);
            audit_log('ADMIN_DATABASE_ROW_UPDATED', 'table', $tName, ['row_id' => $rowId, 'field' => $fieldName]);
            $notice = "Row #{$rowId} in table `{$tName}` updated successfully.";
            $selectedTable = $tName;
        } catch (Throwable $e) {
            $error = "Error updating row field: " . $e->getMessage();
        }
    }
}

// Get all tables and their metadata
$tablesStmt = $pdo->query("SHOW TABLE STATUS");
$tableStatuses = $tablesStmt->fetchAll();

// If table selected, inspect columns and records
$selectedColumns = [];
$selectedRecords = [];
if ($selectedTable) {
    try {
        $colStmt = $pdo->query("SHOW FULL COLUMNS FROM `{$selectedTable}`");
        $selectedColumns = $colStmt->fetchAll();

        $recStmt = $pdo->query("SELECT * FROM `{$selectedTable}` ORDER BY id DESC LIMIT 15");
        $selectedRecords = $recStmt->fetchAll();
    } catch (Throwable $e) {
        $error = "Unable to inspect table `{$selectedTable}`: " . $e->getMessage();
    }
}
?>

<div class="admin-page-head">
    <div>
        <span class="eyebrow">Database Administration & Schema Manager</span>
        <h1>Database Tables & Fields Manager</h1>
        <p>Inspect database tables, alter columns, update records, and run migrations dynamically.</p>
    </div>
    <div class="admin-head-actions">
        <form method="post" style="display:inline-block;" onsubmit="return confirm('Execute all pending migrations to synchronize table schemas?');">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="action" value="run_migrations">
            <button type="submit" class="btn primary">
                <?php echo render_icon('RefreshCw', 16); ?> Run Migrations & Sync Schema
            </button>
        </form>
    </div>
</div>

<?php if ($notice): ?>
    <div class="admin-notice success">
        <?php echo render_icon('CheckCircle2', 18); ?>
        <span><?php echo htmlspecialchars($notice); ?></span>
    </div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="admin-notice error">
        <?php echo render_icon('TriangleAlert', 18); ?>
        <span><?php echo htmlspecialchars($error); ?></span>
    </div>
<?php endif; ?>

<div style="display:grid; grid-template-columns: 320px 1fr; gap:24px; align-items:start;">
    <!-- Left: Tables Navigation List -->
    <div class="admin-projects-card" style="background:#FFFFFF; border:1px solid #E2E8F0; border-radius:16px; padding:20px; box-shadow:0 4px 18px rgba(0,0,0,0.03);">
        <h3 style="font-size:16px; font-weight:700; color:#0F172A; margin:0 0 14px 0; display:flex; align-items:center; justify-content:space-between;">
            <span>Tables (<?php echo count($tableStatuses); ?>)</span>
            <span style="font-size:11px; background:#F1F5F9; color:#475569; padding:2px 8px; border-radius:999px;">MySQL</span>
        </h3>
        
        <div style="display:flex; flex-direction:column; gap:6px; max-height:680px; overflow-y:auto; padding-right:4px;">
            <?php foreach ($tableStatuses as $tbl): 
                $tName = $tbl['Name'];
                $rowsCount = (int)($tbl['Rows'] ?? 0);
                $isCurrent = $selectedTable === $tName;
            ?>
                <a href="database-manager.php?table=<?php echo urlencode($tName); ?>" 
                   style="display:flex; align-items:center; justify-content:space-between; padding:10px 14px; border-radius:10px; text-decoration:none; font-size:13px; font-weight:600; transition:all 0.2s ease; background:<?php echo $isCurrent ? '#0B5D3B' : '#F8FAFC'; ?>; color:<?php echo $isCurrent ? '#FFFFFF' : '#1E293B'; ?>; border:1px solid <?php echo $isCurrent ? '#0B5D3B' : '#E2E8F0'; ?>;">
                    <span style="display:flex; align-items:center; gap:8px;">
                        <?php echo render_icon('Database', 15); ?>
                        <span><?php echo htmlspecialchars($tName); ?></span>
                    </span>
                    <span style="font-size:11px; padding:2px 6px; border-radius:999px; background:<?php echo $isCurrent ? 'rgba(255,255,255,0.25)' : '#E2E8F0'; ?>; color:<?php echo $isCurrent ? '#FFFFFF' : '#475569'; ?>;">
                        <?php echo number_format($rowsCount); ?>
                    </span>
                </a>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Right: Selected Table Details or Overview -->
    <div>
        <?php if ($selectedTable && !empty($selectedColumns)): ?>
            <!-- Table Header & Maintenance Actions -->
            <div class="admin-projects-card" style="background:#FFFFFF; border:1px solid #E2E8F0; border-radius:16px; padding:22px; margin-bottom:24px; box-shadow:0 4px 18px rgba(0,0,0,0.03);">
                <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px;">
                    <div>
                        <span style="font-size:11px; font-weight:700; color:#0B5D3B; text-transform:uppercase; letter-spacing:0.06em;">Inspecting Table</span>
                        <h2 style="font-size:22px; font-weight:800; color:#0F172A; margin:2px 0 0 0;">
                            `<?php echo htmlspecialchars($selectedTable); ?>`
                        </h2>
                    </div>
                    <div style="display:flex; gap:8px;">
                        <form method="post" style="display:inline-block;">
                            <?php echo csrf_field(); ?>
                            <input type="hidden" name="action" value="optimize_table">
                            <input type="hidden" name="table_name" value="<?php echo htmlspecialchars($selectedTable); ?>">
                            <button type="submit" class="btn small outline">Optimize Table</button>
                        </form>
                        <form method="post" style="display:inline-block;">
                            <?php echo csrf_field(); ?>
                            <input type="hidden" name="action" value="repair_table">
                            <input type="hidden" name="table_name" value="<?php echo htmlspecialchars($selectedTable); ?>">
                            <button type="submit" class="btn small outline">Repair Table</button>
                        </form>
                        <?php if ($selectedTable === 'hero_slides'): ?>
                            <a href="hero-slides.php" class="btn small primary">Go to Carousel UI</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Table Columns / Fields -->
            <div class="admin-projects-card" style="background:#FFFFFF; border:1px solid #E2E8F0; border-radius:16px; padding:22px; margin-bottom:24px; box-shadow:0 4px 18px rgba(0,0,0,0.03);">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
                    <h3 style="font-size:16px; font-weight:700; color:#0F172A; margin:0; display:flex; align-items:center; gap:8px;">
                        <?php echo render_icon('Layers3', 18); ?> Table Fields & Schema (<?php echo count($selectedColumns); ?> columns)
                    </h3>
                    <button type="button" class="btn small primary" onclick="document.getElementById('addColumnModal').style.display='block';">
                        <?php echo render_icon('Plus', 14); ?> Add New Field / Column
                    </button>
                </div>

                <div style="overflow-x:auto;">
                    <table class="admin-projects-table" style="width:100%; border-collapse:collapse;">
                        <thead>
                            <tr style="border-bottom:2px solid #F1F5F9; text-align:left;">
                                <th style="padding:10px 12px; font-size:12px;">Field Name</th>
                                <th style="padding:10px 12px; font-size:12px;">Data Type</th>
                                <th style="padding:10px 12px; font-size:12px;">Null</th>
                                <th style="padding:10px 12px; font-size:12px;">Key</th>
                                <th style="padding:10px 12px; font-size:12px;">Default</th>
                                <th style="padding:10px 12px; font-size:12px;">Extra</th>
                                <th style="padding:10px 12px; font-size:12px; text-align:right;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($selectedColumns as $col): 
                                $cName = $col['Field'];
                                $cType = $col['Type'];
                                $cNull = $col['Null'];
                                $cKey = $col['Key'];
                                $cDefault = $col['Default'];
                                $cExtra = $col['Extra'];
                            ?>
                                <tr style="border-bottom:1px solid #F1F5F9; font-size:13px;">
                                    <td style="padding:10px 12px; font-weight:700; color:#0F172A;">
                                        <?php echo htmlspecialchars($cName); ?>
                                    </td>
                                    <td style="padding:10px 12px; color:#0B5D3B; font-family:monospace; font-size:12px;">
                                        <?php echo htmlspecialchars($cType); ?>
                                    </td>
                                    <td style="padding:10px 12px;">
                                        <span style="font-size:11px; padding:2px 6px; border-radius:4px; background:<?php echo $cNull === 'YES' ? '#EFF6FF; color:#1D4ED8' : '#F1F5F9; color:#475569'; ?>;">
                                            <?php echo htmlspecialchars($cNull); ?>
                                        </span>
                                    </td>
                                    <td style="padding:10px 12px;">
                                        <?php if ($cKey): ?>
                                            <span style="font-size:11px; font-weight:700; color:#B45309; background:#FEF3C7; padding:2px 6px; border-radius:4px;">
                                                <?php echo htmlspecialchars($cKey); ?>
                                            </span>
                                        <?php else: ?>
                                            <span style="color:#CBD5E1;">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="padding:10px 12px; font-family:monospace; font-size:12px; color:#64748B;">
                                        <?php echo $cDefault !== null ? htmlspecialchars((string)$cDefault) : '<em style="color:#94A3B8;">NULL</em>'; ?>
                                    </td>
                                    <td style="padding:10px 12px; font-size:11px; color:#64748B;">
                                        <?php echo htmlspecialchars($cExtra); ?>
                                    </td>
                                    <td style="padding:10px 12px; text-align:right;">
                                        <?php if ($cName !== 'id'): ?>
                                            <form method="post" style="display:inline-block;" onsubmit="return confirm('Drop column `<?php echo htmlspecialchars($cName); ?>` from table `<?php echo htmlspecialchars($selectedTable); ?>`?');">
                                                <?php echo csrf_field(); ?>
                                                <input type="hidden" name="action" value="drop_column">
                                                <input type="hidden" name="table_name" value="<?php echo htmlspecialchars($selectedTable); ?>">
                                                <input type="hidden" name="column_name" value="<?php echo htmlspecialchars($cName); ?>">
                                                <button type="submit" class="btn small danger" style="padding:4px 8px; font-size:11px; background:#FEE2E2; color:#DC2626; border:none; border-radius:6px; cursor:pointer;">
                                                    Drop
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Browse & Update Table Rows -->
            <div class="admin-projects-card" style="background:#FFFFFF; border:1px solid #E2E8F0; border-radius:16px; padding:22px; box-shadow:0 4px 18px rgba(0,0,0,0.03);">
                <h3 style="font-size:16px; font-weight:700; color:#0F172A; margin:0 0 16px 0; display:flex; align-items:center; gap:8px;">
                    <?php echo render_icon('FileText', 18); ?> Recent Rows in `<?php echo htmlspecialchars($selectedTable); ?>` (Latest 15)
                </h3>

                <?php if (empty($selectedRecords)): ?>
                    <p style="color:#64748B; font-size:14px; margin:0;">This table currently has 0 rows.</p>
                <?php else: ?>
                    <div style="overflow-x:auto;">
                        <table class="admin-projects-table" style="width:100%; border-collapse:collapse; font-size:12px;">
                            <thead>
                                <tr style="border-bottom:2px solid #F1F5F9; text-align:left;">
                                    <?php 
                                    $colKeys = array_keys($selectedRecords[0]);
                                    foreach (array_slice($colKeys, 0, 7) as $ck): ?>
                                        <th style="padding:8px 10px;"><?php echo htmlspecialchars($ck); ?></th>
                                    <?php endforeach; ?>
                                    <th style="padding:8px 10px; text-align:right;">Update Field</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($selectedRecords as $rec): ?>
                                    <tr style="border-bottom:1px solid #F1F5F9;">
                                        <?php foreach (array_slice($colKeys, 0, 7) as $ck): ?>
                                            <td style="padding:8px 10px; max-width:180px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                                                <?php echo htmlspecialchars((string)($rec[$ck] ?? '')); ?>
                                            </td>
                                        <?php endforeach; ?>
                                        <td style="padding:8px 10px; text-align:right;">
                                            <?php if (isset($rec['id'])): ?>
                                                <button type="button" class="btn small outline" style="padding:3px 8px; font-size:11px;"
                                                        onclick="openRowEditModal('<?php echo htmlspecialchars($selectedTable); ?>', <?php echo (int)$rec['id']; ?>, <?php echo htmlspecialchars(json_encode($rec)); ?>)">
                                                    Edit Row Fields
                                                </button>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>

        <?php else: ?>
            <!-- Default Welcome State -->
            <div class="admin-projects-card" style="background:#FFFFFF; border:1px solid #E2E8F0; border-radius:16px; padding:36px; text-align:center; box-shadow:0 4px 18px rgba(0,0,0,0.03);">
                <div style="font-size:48px; margin-bottom:14px;"><?php echo render_icon('Database', 48); ?></div>
                <h2 style="font-size:20px; font-weight:800; color:#0F172A; margin:0 0 8px 0;">Select a Table to Inspect & Update</h2>
                <p style="color:#64748B; font-size:14px; max-width:480px; margin:0 auto 24px auto;">
                    Select any table on the left sidebar to inspect fields, add new columns, update existing schema properties, and edit rows directly.
                </p>
                <a href="database-manager.php?table=hero_slides" class="btn primary">
                    Inspect `hero_slides` Table
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal: Add Column -->
<div id="addColumnModal" style="display:none; position:fixed; inset:0; background:rgba(15,23,42,0.6); z-index:9999; backdrop-filter:blur(3px);">
    <div style="background:#FFFFFF; border-radius:18px; max-width:540px; width:92%; margin:60px auto; padding:28px; box-shadow:0 25px 60px rgba(0,0,0,0.2);">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:18px;">
            <h3 style="font-size:18px; font-weight:700; color:#0F172A; margin:0;">Add New Field / Column</h3>
            <button type="button" onclick="document.getElementById('addColumnModal').style.display='none';" style="background:none; border:none; cursor:pointer; font-size:20px; color:#64748B;">&times;</button>
        </div>
        <form method="post">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="action" value="add_column">
            <input type="hidden" name="table_name" value="<?php echo htmlspecialchars($selectedTable); ?>">

            <div style="margin-bottom:14px;">
                <label style="display:block; font-size:13px; font-weight:600; color:#334155; margin-bottom:4px;">Column Name</label>
                <input type="text" name="column_name" required placeholder="e.g. subtitle, tech_stack, is_featured" class="admin-input" style="width:100%; padding:9px 12px; border:1px solid #CBD5E1; border-radius:8px;">
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:14px;">
                <div>
                    <label style="display:block; font-size:13px; font-weight:600; color:#334155; margin-bottom:4px;">Data Type</label>
                    <select name="column_type" class="admin-input" style="width:100%; padding:9px 12px; border:1px solid #CBD5E1; border-radius:8px;">
                        <option value="VARCHAR">VARCHAR (Text / String)</option>
                        <option value="TEXT">TEXT (Long Description)</option>
                        <option value="INT">INT (Whole Number)</option>
                        <option value="TINYINT">TINYINT (Boolean / Small)</option>
                        <option value="DECIMAL">DECIMAL (Money / Float)</option>
                        <option value="DATE">DATE</option>
                        <option value="DATETIME">DATETIME</option>
                    </select>
                </div>
                <div>
                    <label style="display:block; font-size:13px; font-weight:600; color:#334155; margin-bottom:4px;">Length / Values</label>
                    <input type="text" name="column_length" placeholder="e.g. 255" class="admin-input" style="width:100%; padding:9px 12px; border:1px solid #CBD5E1; border-radius:8px;">
                </div>
            </div>

            <div style="margin-bottom:14px;">
                <label style="display:block; font-size:13px; font-weight:600; color:#334155; margin-bottom:4px;">Default Value (Optional)</label>
                <input type="text" name="column_default" placeholder="e.g. NULL, active, 0" class="admin-input" style="width:100%; padding:9px 12px; border:1px solid #CBD5E1; border-radius:8px;">
            </div>

            <div style="margin-bottom:18px;">
                <label style="display:flex; align-items:center; gap:8px; font-size:13px; font-weight:600; color:#334155; cursor:pointer;">
                    <input type="checkbox" name="is_nullable" value="1" checked>
                    Allow NULL values
                </label>
            </div>

            <div style="display:flex; justify-content:flex-end; gap:10px;">
                <button type="button" class="btn outline" onclick="document.getElementById('addColumnModal').style.display='none';">Cancel</button>
                <button type="submit" class="btn primary">Add Field to Table</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Edit Row Field -->
<div id="editRowModal" style="display:none; position:fixed; inset:0; background:rgba(15,23,42,0.6); z-index:9999; backdrop-filter:blur(3px);">
    <div style="background:#FFFFFF; border-radius:18px; max-width:540px; width:92%; margin:60px auto; padding:28px; box-shadow:0 25px 60px rgba(0,0,0,0.2);">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:18px;">
            <h3 style="font-size:18px; font-weight:700; color:#0F172A; margin:0;" id="editRowModalTitle">Update Row Field</h3>
            <button type="button" onclick="document.getElementById('editRowModal').style.display='none';" style="background:none; border:none; cursor:pointer; font-size:20px; color:#64748B;">&times;</button>
        </div>
        <form method="post">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="action" value="update_row_field">
            <input type="hidden" name="table_name" value="<?php echo htmlspecialchars($selectedTable); ?>">
            <input type="hidden" name="row_id" id="editRowModalRowId" value="">

            <div style="margin-bottom:14px;">
                <label style="display:block; font-size:13px; font-weight:600; color:#334155; margin-bottom:4px;">Select Field to Update</label>
                <select name="field_name" id="editRowFieldSelect" class="admin-input" style="width:100%; padding:9px 12px; border:1px solid #CBD5E1; border-radius:8px;" onchange="updateEditRowValueInput()">
                </select>
            </div>

            <div style="margin-bottom:18px;">
                <label style="display:block; font-size:13px; font-weight:600; color:#334155; margin-bottom:4px;">Field Value</label>
                <textarea name="field_value" id="editRowFieldValue" rows="4" class="admin-input" style="width:100%; padding:9px 12px; border:1px solid #CBD5E1; border-radius:8px; font-family:inherit;"></textarea>
            </div>

            <div style="display:flex; justify-content:flex-end; gap:10px;">
                <button type="button" class="btn outline" onclick="document.getElementById('editRowModal').style.display='none';">Cancel</button>
                <button type="submit" class="btn primary">Update Field Value</button>
            </div>
        </form>
    </div>
</div>

<script>
let currentRowData = {};
function openRowEditModal(tableName, rowId, rowData) {
    currentRowData = rowData;
    document.getElementById('editRowModalTitle').textContent = `Update Row #${rowId} in \`${tableName}\``;
    document.getElementById('editRowModalRowId').value = rowId;
    const select = document.getElementById('editRowFieldSelect');
    select.innerHTML = '';
    for (const key in rowData) {
        if (key === 'id') continue;
        const opt = document.createElement('option');
        opt.value = key;
        opt.textContent = key;
        select.appendChild(opt);
    }
    updateEditRowValueInput();
    document.getElementById('editRowModal').style.display = 'block';
}

function updateEditRowValueInput() {
    const key = document.getElementById('editRowFieldSelect').value;
    document.getElementById('editRowFieldValue').value = currentRowData[key] !== null ? currentRowData[key] : '';
}
</script>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
