<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/headers.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/mailer.php';
require_once __DIR__ . '/includes/csrf.php';
require_once __DIR__ . '/includes/rate_limiter.php';
require_once __DIR__ . '/includes/audit_logger.php';

$baseUrl = get_base_url();

if (is_logged_in()) {
    header("Location: " . $baseUrl . "dashboard.php");
    exit;
}

require_csrf_token();

$notice = '';
$error = '';
$step = $_GET['step'] ?? 'request';
$prefillEmail = strtolower(trim($_GET['email'] ?? ''));

// Handle Step 1: Send Reset Verification Code
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['request_reset'])) {
    $email = strtolower(trim($_POST['email'] ?? ''));
    $rateKey = 'forgot_pass_' . md5(($_SERVER['REMOTE_ADDR'] ?? '127.0.0.1') . '_' . $email);

    if (!RateLimiter::check($rateKey, 5, 900)) {
        $error = 'Too many password reset requests. Please wait 15 minutes before trying again.';
        audit_log('PASSWORD_RESET_RATELIMIT', 'user', null, ['email' => $email]);
    } elseif (empty($email)) {
        $error = 'Please enter your registered email address.';
    } else {
        RateLimiter::hit($rateKey, 900);
        $pdo = get_db();

        // 1. Search Users table
        $stmt = $pdo->prepare("SELECT id, full_name, email FROM users WHERE email = ? AND status = 'active' LIMIT 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        // 2. Search Admins table if not in Users
        $admin = null;
        if (!$user) {
            $aStmt = $pdo->prepare("SELECT id, full_name, email FROM admins WHERE email = ? AND status = 'active' LIMIT 1");
            $aStmt->execute([$email]);
            $admin = $aStmt->fetch();
        }

        if ($user || $admin) {
            $target = $user ?: $admin;
            $table = $user ? 'users' : 'admins';
            $code = (string)random_int(100000, 999999);
            $expiresAt = date('Y-m-d H:i:s', time() + 900); // 15 mins

            $upd = $pdo->prepare("UPDATE {$table} SET reset_code = ?, reset_expires_at = ? WHERE id = ?");
            $upd->execute([$code, $expiresAt, $target['id']]);

            send_password_reset_email($target['email'], $target['full_name'], $code);
            audit_log('PASSWORD_RESET_CODE_SENT', $table, (string)$target['id'], ['email' => $email]);
        }

        // Always show positive notice to protect privacy & prevent enumeration
        $notice = "If an account associated with <strong>" . htmlspecialchars($email) . "</strong> exists in our system, a 6-digit password reset verification code has been sent. Please check your inbox.";
        $step = 'verify';
        $prefillEmail = $email;
    }
}

// Handle Step 2: Verify Code & Reset Password
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['verify_reset'])) {
    $email = strtolower(trim($_POST['email'] ?? ''));
    $code = trim($_POST['code'] ?? '');
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $step = 'verify';
    $prefillEmail = $email;

    if (empty($email) || empty($code) || empty($newPassword)) {
        $error = 'Please fill in all fields including the verification code and new password.';
    } elseif (strlen($newPassword) < 6) {
        $error = 'New password must be at least 6 characters long.';
    } elseif ($newPassword !== $confirmPassword) {
        $error = 'New password and confirm password do not match.';
    } else {
        $pdo = get_db();

        // 1. Check Users
        $stmt = $pdo->prepare("SELECT id, full_name, email FROM users WHERE email = ? AND reset_code = ? AND reset_expires_at > NOW() LIMIT 1");
        $stmt->execute([$email, $code]);
        $user = $stmt->fetch();

        // 2. Check Admins
        $admin = null;
        if (!$user) {
            $aStmt = $pdo->prepare("SELECT id, full_name, email FROM admins WHERE email = ? AND reset_code = ? AND reset_expires_at > NOW() LIMIT 1");
            $aStmt->execute([$email, $code]);
            $admin = $aStmt->fetch();
        }

        if ($user || $admin) {
            $target = $user ?: $admin;
            $table = $user ? 'users' : 'admins';
            $hash = password_hash($newPassword, PASSWORD_DEFAULT);

            $upd = $pdo->prepare("UPDATE {$table} SET password_hash = ?, reset_code = NULL, reset_expires_at = NULL WHERE id = ?");
            $upd->execute([$hash, $target['id']]);

            audit_log('PASSWORD_RESET_SUCCESS', $table, (string)$target['id'], ['email' => $email]);
            $notice = "Your password has been reset successfully! You can now log in with your new password below.";
            $step = 'completed';
        } else {
            $error = 'Invalid or expired verification code. Please request a new reset code.';
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<main>
    <section class="auth-wrap modern-auth">
        <div class="auth-visual">
            <div>
                <span class="eyebrow">Kendat Account Security</span>
                <h1>Reset Password</h1>
                <p>Recover access to your Kendat Integrated Services client workspace securely using your registered email address.</p>
                <div class="auth-chip-grid" style="margin-top: 20px;">
                    <span><?php echo render_icon('ShieldCheck', 16); ?>Encrypted recovery</span>
                    <span><?php echo render_icon('Mail', 16); ?>Instant email code</span>
                    <span><?php echo render_icon('Lock', 16); ?>Protected access</span>
                </div>
            </div>

            <div class="auth-console">
                <div><span stop-dot></span> Premier IT consulting &amp; enterprise software solutions</div>
                <div><span stop-dot></span> End-to-end confidential client portal &amp; data privacy</div>
                <div><span stop-dot></span> 24/7 technical support &amp; solution engineering</div>
                <div><span stop-dot></span> Protected corporate infrastructure &amp; verified access</div>
            </div>

            <div class="auth-footer-badge" style="display: flex; align-items: center; gap: 10px; font-size: 13px; color: #cbd5e1; padding-top: 16px; border-top: 1px solid rgba(255,255,255,0.15);">
                <?php echo render_icon('ShieldCheck', 20); ?>
                <span>100% Confidential &amp; Verified Account Security</span>
            </div>
        </div>

        <div class="auth-form-panel">
            <div class="glass-card form auth-card modern-auth-card">
                <?php if ($step === 'completed'): ?>
                    <div class="form-head">
                        <span class="form-badge" style="background: rgba(34, 197, 94, 0.15); color: #16a34a; border-color: rgba(34, 197, 94, 0.3);">
                            <?php echo render_icon('CheckCircle', 15); ?>SUCCESS
                        </span>
                        <h2>Password Reset</h2>
                        <p>Your password update is complete.</p>
                    </div>

                    <div class="notice success" style="padding: 16px; border-radius: 14px; background: rgba(34, 197, 94, 0.12); border: 1px solid rgba(34, 197, 94, 0.3); color: #166534; font-size: 14px; line-height: 1.6; margin-bottom: 24px;">
                        <?php echo $notice; ?>
                    </div>

                    <a class="tc-pill-btn-blue" href="<?php echo $baseUrl; ?>login.php" style="width: 100%; justify-content: center; text-decoration: none;">
                        Proceed to Login <?php echo render_icon('ArrowRight', 18); ?>
                    </a>

                <?php elseif ($step === 'verify'): ?>
                    <form method="post" action="">
                        <?php echo csrf_input(); ?>
                        <div class="form-head">
                            <span class="form-badge"><?php echo render_icon('KeyRound', 15); ?>VERIFY &amp; RESET</span>
                            <h2>Enter Verification Code</h2>
                            <p>Enter the 6-digit code sent to your email and set a new password.</p>
                        </div>

                        <?php if ($notice): ?>
                            <div class="notice success" style="padding: 14px 16px; border-radius: 12px; background: rgba(34, 197, 94, 0.12); border: 1px solid rgba(34, 197, 94, 0.3); color: #166534; font-size: 13.5px; line-height: 1.6; margin-bottom: 20px;">
                                <?php echo $notice; ?>
                            </div>
                        <?php endif; ?>

                        <?php if ($error): ?>
                            <div class="error" style="margin-bottom: 18px;"><?php echo htmlspecialchars($error); ?></div>
                        <?php endif; ?>

                        <label>Registered Email Address
                            <input name="email" type="email" value="<?php echo htmlspecialchars($prefillEmail); ?>" required>
                        </label>

                        <label>6-Digit Verification Code
                            <input name="code" type="text" placeholder="e.g. 849201" maxlength="6" pattern="[0-9]{6}" style="letter-spacing: 0.25em; font-size: 18px; font-weight: 800; text-align: center;" required>
                        </label>

                        <label>New Password
                            <span class="password-field">
                                <input name="new_password" type="password" placeholder="Minimum 6 characters" required>
                                <button type="button" class="password-toggle" aria-label="Toggle password visibility">
                                    <span class="icon-eye"><?php echo render_icon('Eye', 18); ?></span>
                                    <span class="icon-eye-off" style="display:none;"><?php echo render_icon('EyeOff', 18); ?></span>
                                </button>
                            </span>
                        </label>

                        <label>Confirm New Password
                            <span class="password-field">
                                <input name="confirm_password" type="password" placeholder="Re-enter new password" required>
                                <button type="button" class="password-toggle" aria-label="Toggle password visibility">
                                    <span class="icon-eye"><?php echo render_icon('Eye', 18); ?></span>
                                    <span class="icon-eye-off" style="display:none;"><?php echo render_icon('EyeOff', 18); ?></span>
                                </button>
                            </span>
                        </label>

                        <button class="tc-pill-btn-blue" type="submit" name="verify_reset" style="width: 100%; justify-content: center; margin-top: 10px;">
                            Reset Password Now <?php echo render_icon('ShieldCheck', 18); ?>
                        </button>

                        <div class="auth-switch" style="text-align: center; margin-top: 20px; font-size: 14px; color: var(--muted);">
                            Didn't get code? <a href="<?php echo $baseUrl; ?>forgot-password.php" style="color: var(--blue); font-weight: 800; text-decoration: none; display: inline-flex; align-items: center; gap: 4px;">Resend code <?php echo render_icon('ArrowRight', 14); ?></a>
                        </div>
                    </form>

                <?php else: ?>
                    <form method="post" action="">
                        <?php echo csrf_input(); ?>
                        <div class="form-head">
                            <span class="form-badge"><?php echo render_icon('Lock', 15); ?>RECOVERY ASSISTANT</span>
                            <h2>Forgot Password?</h2>
                            <p>Enter your registered email address and we'll send you a 6-digit verification code to reset your password.</p>
                        </div>

                        <?php if ($error): ?>
                            <div class="error" style="margin-bottom: 18px;"><?php echo htmlspecialchars($error); ?></div>
                        <?php endif; ?>

                        <label>Registered Email Address
                            <input name="email" type="email" placeholder="tunde.adebayo@company.ng" value="<?php echo htmlspecialchars($prefillEmail); ?>" required>
                        </label>

                        <button class="tc-pill-btn-blue" type="submit" name="request_reset" style="width: 100%; justify-content: center; margin-top: 10px;">
                            Send Reset Code <?php echo render_icon('Mail', 18); ?>
                        </button>

                        <div class="auth-switch" style="text-align: center; margin-top: 20px; font-size: 14px; color: var(--muted);">
                            Remembered password? <a href="<?php echo $baseUrl; ?>login.php" style="color: var(--blue); font-weight: 800; text-decoration: none; display: inline-flex; align-items: center; gap: 4px;">Return to login <?php echo render_icon('ArrowRight', 14); ?></a>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </section>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
