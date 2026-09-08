<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/admin_header.php';
require_once __DIR__ . '/../includes/mailer.php';

require_csrf_token();

$pdo = get_db();
$notice = '';
$error = '';

// Fetch registered users list for recipient dropdown
$usersStmt = $pdo->query("SELECT id, full_name, email FROM users ORDER BY full_name ASC");
$allUsers = $usersStmt->fetchAll();

// Handle Retry Email POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'retry_email') {
    $retryId = (int)($_POST['retry_id'] ?? 0);
    if ($retryId > 0) {
        $logStmt = $pdo->prepare("SELECT * FROM sent_emails WHERE id = ? LIMIT 1");
        $logStmt->execute([$retryId]);
        $emailRecord = $logStmt->fetch();

        if ($emailRecord) {
            $ok = send_admin_custom_email(
                $emailRecord['recipient_email'],
                $emailRecord['recipient_name'] ?? 'Valued Client',
                $emailRecord['subject'],
                $emailRecord['badge'] ?? 'General Announcement',
                $emailRecord['message']
            );

            if ($ok) {
                $upStmt = $pdo->prepare("UPDATE sent_emails SET status = 'sent', sent_at = CURRENT_TIMESTAMP WHERE id = ?");
                $upStmt->execute([$retryId]);
                audit_log('ADMIN_EMAIL_RETRY_SUCCESS', 'sent_email', (string)$retryId, ['recipient' => $emailRecord['recipient_email']]);
                $notice = "Retry successful! Re-dispatched email to " . htmlspecialchars($emailRecord['recipient_email']) . " via SSL SMTP.";
            } else {
                audit_log('ADMIN_EMAIL_RETRY_FAILED', 'sent_email', (string)$retryId, ['recipient' => $emailRecord['recipient_email']]);
                $error = "Retry failed for " . htmlspecialchars($emailRecord['recipient_email']) . ". Please check SMTP configuration.";
            }
        } else {
            $error = 'Email log record not found.';
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_email_submit'])) {
    $recipientMode = $_POST['recipient_mode'] ?? 'single_user';
    $selectedUserId = (int)($_POST['user_id'] ?? 0);
    $customEmail = strtolower(trim($_POST['custom_email'] ?? ''));
    $customName = trim($_POST['custom_name'] ?? 'Client');
    
    $subject = trim($_POST['subject'] ?? '');
    $categoryBadge = trim($_POST['category_badge'] ?? 'General Announcement');
    $messageBody = trim($_POST['message_body'] ?? '');
    $ctaText = trim($_POST['cta_text'] ?? '');
    $ctaUrl = trim($_POST['cta_url'] ?? '');

    if (empty($subject) || empty($messageBody)) {
        $error = 'Please provide both an Email Subject and Email Message Body.';
    } else {
        $targets = [];

        if ($recipientMode === 'all_users') {
            foreach ($allUsers as $u) {
                $targets[] = ['email' => $u['email'], 'name' => $u['full_name']];
            }
        } elseif ($recipientMode === 'single_user') {
            $uStmt = $pdo->prepare("SELECT full_name, email FROM users WHERE id = ? LIMIT 1");
            $uStmt->execute([$selectedUserId]);
            $singleUser = $uStmt->fetch();
            if ($singleUser) {
                $targets[] = ['email' => $singleUser['email'], 'name' => $singleUser['full_name']];
            } else {
                $error = 'Selected user was not found.';
            }
        } elseif ($recipientMode === 'custom_email') {
            if (empty($customEmail) || !filter_var($customEmail, FILTER_VALIDATE_EMAIL)) {
                $error = 'Please enter a valid Custom Email Address.';
            } else {
                $targets[] = ['email' => $customEmail, 'name' => !empty($customName) ? $customName : 'Valued Client'];
            }
        }

        if (empty($error) && !empty($targets)) {
            $sentCount = 0;
            $failCount = 0;

            $logStmt = $pdo->prepare("INSERT INTO sent_emails (recipient_email, recipient_name, subject, badge, message, status) VALUES (?, ?, ?, ?, ?, ?)");

            foreach ($targets as $t) {
                $ok = send_admin_custom_email($t['email'], $t['name'], $subject, $categoryBadge, $messageBody, $ctaText, $ctaUrl);
                if ($ok) {
                    $sentCount++;
                    $logStmt->execute([$t['email'], $t['name'], $subject, $categoryBadge, $messageBody, 'sent']);
                } else {
                    $failCount++;
                    $logStmt->execute([$t['email'], $t['name'], $subject, $categoryBadge, $messageBody, 'failed']);
                }
            }

            if ($sentCount > 0) {
                $notice = "Successfully dispatched {$sentCount} email(s) via SSL SMTP!";
                if ($failCount > 0) {
                    $notice .= " ({$failCount} email(s) failed delivery).";
                }
            } else {
                $error = "Failed to send email. Please verify SMTP credentials and server connection.";
            }
        }
    }
}

// Fetch sent email history
$historyStmt = $pdo->query("SELECT * FROM sent_emails ORDER BY id DESC LIMIT 15");
$sentHistory = $historyStmt->fetchAll();
?>

<div class="admin-page-head">
    <div>
        <span class="eyebrow">Admin Email Broadcast</span>
        <h1>Send Email Notification</h1>
        <p>Dispatch custom branded emails, system updates, proposals, and announcements directly to clients via SSL SMTP.</p>
    </div>
</div>

<?php if ($notice): ?>
    <div class="admin-notice success"><?php echo render_icon('CheckCircle2'); ?><?php echo htmlspecialchars($notice); ?></div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="admin-notice error"><?php echo render_icon('TriangleAlert'); ?><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<div class="settings-panel">
    <form class="settings-section" method="post" action="">
        <?php echo csrf_input(); ?>
        <h3 style="display:flex; align-items:center; gap:10px;">
            <?php echo render_icon('Send', 22); ?> Compose Branded Email
        </h3>

        <div class="send-mail-form-wrap" style="display: flex; flex-direction: column; gap: 20px;">

            <!-- Row 1: Recipient Controls -->
            <div style="display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 16px;">
                <label>Recipient Mode
                    <select name="recipient_mode" id="recipientModeSelect" onchange="toggleRecipientFields()">
                        <option value="single_user">Specific Registered Client</option>
                        <option value="all_users">Broadcast to ALL Registered Users (<?php echo count($allUsers); ?> users)</option>
                        <option value="custom_email">Custom Email Address</option>
                    </select>
                </label>

                <div id="singleUserWrap">
                    <label>Select Registered Client
                        <select name="user_id">
                            <option value="">-- Choose Client --</option>
                            <?php foreach ($allUsers as $u): ?>
                                <option value="<?php echo $u['id']; ?>"><?php echo htmlspecialchars($u['full_name']); ?> (<?php echo htmlspecialchars($u['email']); ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                </div>
            </div>

            <!-- Row 1b: Custom Email Target (When Selected) -->
            <div id="customEmailWrap" style="display:none; grid-template-columns: 1fr 1fr; gap: 16px;">
                <label>Recipient Name
                    <input name="custom_name" type="text" placeholder="e.g. Dr. Olumide Johnson">
                </label>
                <label>Recipient Email Address
                    <input name="custom_email" type="email" placeholder="olumide.johnson@company.ng">
                </label>
            </div>

            <!-- Row 2: Subject & Category -->
            <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 16px;">
                <label>Email Subject
                    <input name="subject" type="text" placeholder="e.g. System Upgrade & New AI Portal Features" required>
                </label>

                <label>Email Category / Header Badge
                    <select name="category_badge">
                        <option value="General Announcement">General Announcement</option>
                        <option value="System Update">System Update</option>
                        <option value="Project Proposal">Project Proposal</option>
                        <option value="Account Notice">Account Notice</option>
                        <option value="Scheduled Maintenance">Scheduled Maintenance</option>
                    </select>
                </label>
            </div>

            <!-- Row 3: Optional Button CTA Controls -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                <label>Optional Button CTA Text
                    <input name="cta_text" type="text" placeholder="e.g. View Client Portal">
                </label>
                <label>Optional Button CTA Link URL
                    <input name="cta_url" type="url" placeholder="https://www.kisprojectslab.com/dashboard.php">
                </label>
            </div>

            <!-- Row 4: Message Body Textarea -->
            <div>
                <label style="display:block; width:100%;">Message Body
                    <textarea name="message_body" rows="7" placeholder="Write your email content here..." required style="width:100%; min-height: 150px; resize: vertical; box-sizing: border-box;"></textarea>
                </label>
            </div>

            <div style="margin-top: 8px;">
                <button class="tc-pill-btn-blue" type="submit" name="send_email_submit" style="display:inline-flex; align-items:center; gap:8px;">
                    <?php echo render_icon('Send', 18); ?> Dispatch Email Now
                </button>
            </div>

        </div>
    </form>

    <!-- Sent Email History Log -->
    <section class="settings-section">
        <h3><?php echo render_icon('Inbox', 20); ?> Recently Dispatched Emails Log</h3>
        <div class="table-card" style="margin-top: 14px; border:0; box-shadow:none;">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Recipient</th>
                        <th>Subject</th>
                        <th>Category</th>
                        <th>Status</th>
                        <th>Sent At</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($sentHistory)): ?>
                        <?php foreach ($sentHistory as $log): ?>
                            <tr>
                                <td>#<?php echo $log['id']; ?></td>
                                <td>
                                    <strong><?php echo htmlspecialchars($log['recipient_name'] ?? 'Client'); ?></strong><br>
                                    <small><?php echo htmlspecialchars($log['recipient_email']); ?></small>
                                </td>
                                <td><?php echo htmlspecialchars($log['subject']); ?></td>
                                <td><span class="status"><?php echo htmlspecialchars($log['badge'] ?? 'General'); ?></span></td>
                                <td>
                                    <?php if ($log['status'] === 'sent'): ?>
                                        <span class="status" style="background: rgba(34, 197, 94, 0.12); color: #166534;">SENT</span>
                                    <?php else: ?>
                                        <span class="status" style="background: rgba(239, 68, 68, 0.12); color: #b91c1c;">FAILED</span>
                                    <?php endif; ?>
                                </td>
                                <td><small><?php echo date('M d, Y h:i A', strtotime($log['sent_at'])); ?></small></td>
                                <td>
                                    <?php if ($log['status'] === 'failed'): ?>
                                        <form method="post" action="" style="display:inline;" onsubmit="return confirm('Retry sending email to <?php echo htmlspecialchars($log['recipient_email']); ?>?');">
                                            <?php echo csrf_input(); ?>
                                            <input type="hidden" name="action" value="retry_email">
                                            <input type="hidden" name="retry_id" value="<?php echo $log['id']; ?>">
                                            <button type="submit" class="tc-pill-btn-blue" style="padding: 6px 14px; font-size: 11px; border-radius: 8px; text-decoration: none; cursor: pointer; display: inline-flex; align-items: center; gap: 4px; border:0;" title="Retry sending failed email">
                                                <?php echo render_icon('RotateCw', 13); ?> Retry Send
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <form method="post" action="" style="display:inline;" onsubmit="return confirm('Resend email to <?php echo htmlspecialchars($log['recipient_email']); ?>?');">
                                            <?php echo csrf_input(); ?>
                                            <input type="hidden" name="action" value="retry_email">
                                            <input type="hidden" name="retry_id" value="<?php echo $log['id']; ?>">
                                            <button type="submit" class="btn small ghost" style="padding: 5px 12px; font-size: 11px; border-radius: 8px; cursor: pointer; display: inline-flex; align-items: center; gap: 4px;" title="Resend email to recipient">
                                                <?php echo render_icon('RotateCw', 13); ?> Resend
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 24px; color: #64748b;">No sent email logs recorded yet.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</div>

<script>
function toggleRecipientFields() {
    const mode = document.getElementById('recipientModeSelect').value;
    const singleWrap = document.getElementById('singleUserWrap');
    const customWrap = document.getElementById('customEmailWrap');

    if (mode === 'single_user') {
        singleWrap.style.display = 'block';
        customWrap.style.display = 'none';
    } else if (mode === 'custom_email') {
        singleWrap.style.display = 'none';
        customWrap.style.display = 'grid';
    } else {
        singleWrap.style.display = 'none';
        customWrap.style.display = 'none';
    }
}
</script>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
