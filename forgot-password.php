<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/headers.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/mailer.php';
require_once __DIR__ . '/includes/csrf.php';
require_once __DIR__ . '/includes/rate_limiter.php';
require_once __DIR__ . '/includes/audit_logger.php';
require_once __DIR__ . '/includes/auth_layout.php';

$baseUrl = get_base_url();
$settings = get_settings();

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

        // Check users
        $stmt = $pdo->prepare("SELECT id, full_name, reset_code, reset_expires_at FROM users WHERE email = ? AND status = 'active' LIMIT 1");
        $stmt->execute([$email]);
        $targetUser = $stmt->fetch();
        $targetTable = 'users';

        if (!$targetUser) {
            // Check admins
            $aStmt = $pdo->prepare("SELECT id, full_name, reset_code, reset_expires_at FROM admins WHERE email = ? AND status = 'active' LIMIT 1");
            $aStmt->execute([$email]);
            $targetUser = $aStmt->fetch();
            $targetTable = 'admins';
        }

        if ($targetUser && !empty($targetUser['reset_code'])) {
            $now = time();
            $expires = !empty($targetUser['reset_expires_at']) ? strtotime($targetUser['reset_expires_at']) : 0;

            if ($targetUser['reset_code'] !== $code) {
                $error = 'The verification code you entered is incorrect. Please double check your email.';
                audit_log('PASSWORD_RESET_INVALID_CODE', $targetTable, (string)$targetUser['id'], ['email' => $email]);
            } elseif ($now > $expires) {
                $error = 'The verification code has expired (valid for 15 minutes). Please request a new one.';
                audit_log('PASSWORD_RESET_EXPIRED_CODE', $targetTable, (string)$targetUser['id'], ['email' => $email]);
            } else {
                // Update password
                $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
                $upd = $pdo->prepare("UPDATE {$targetTable} SET password_hash = ?, reset_code = NULL, reset_expires_at = NULL WHERE id = ?");
                $upd->execute([$newHash, $targetUser['id']]);

                audit_log('PASSWORD_RESET_SUCCESS', $targetTable, (string)$targetUser['id'], ['email' => $email]);
                set_flash('success', 'Your password has been successfully reset! You can now log in with your new credentials.');

                $redirectUrl = ($targetTable === 'admins') ? ($baseUrl . 'admin-login.php') : ($baseUrl . 'login.php');
                header("Location: " . $redirectUrl);
                exit;
            }
        } else {
            $error = 'Invalid or expired password reset session. Please request a new code.';
        }
    }
}

$pageTitle = 'Reset Your Password - ' . ($settings['company_name'] ?? 'KendatTech');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Caveat:wght@600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo $baseUrl; ?>assets/css/style.css?v=<?php echo file_exists(__DIR__ . '/assets/css/style.css') ? filemtime(__DIR__ . '/assets/css/style.css') : time(); ?>">
    <link rel="icon" type="image/png" href="<?php echo htmlspecialchars(upload_asset_url($settings['favicon'] ?? '')); ?>">
</head>
<body class="auth-page-body">

    <!-- Top Centered Brand Header -->
    <?php render_auth_header($settings, $baseUrl); ?>

    <!-- Main Central Split Card Container -->
    <main class="auth-main-container">
        <div class="auth-split-card">

            <!-- Left Banner: Dark Forest Green with 3D Cyber Shield Illustration -->
            <div class="auth-banner-panel auth-banner-dark">
                <div>
                    <!-- Brand Tag -->
                    <div class="auth-banner-brand-row">
                        <a href="<?php echo $baseUrl; ?>" class="banner-brand-link">
                            <span class="banner-brand-text">Kendat<span style="color:#10B981;">Tech</span></span>
                        </a>
                        <span class="banner-brand-slogan">WE DESIGN &bull; WE DEVELOP &bull; WE DELIVER</span>
                    </div>

                    <!-- Main Heading -->
                    <h1 class="auth-banner-title">
                        Reset Your<br><span class="highlight-text">Password</span>
                    </h1>
                    <p class="auth-banner-desc">
                        No worries! It happens. Enter your email address and we'll send you a link to reset your password.
                    </p>

                    <!-- 3D Security Shield Illustration -->
                    <div class="auth-illustration-box">
                        <img src="<?php echo $baseUrl; ?>assets/images/auth-reset-shield.jpg" alt="KendatTech Security & Identity Protection" class="auth-illustration-img">
                    </div>

                    <!-- Step Timeline Checklist -->
                    <div class="auth-step-timeline">
                        <div class="auth-step-item">
                            <div class="step-item-icon">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
                            </div>
                            <div class="step-item-content">
                                <strong>Check your email</strong>
                                <span>We'll send you a secure link to reset your password.</span>
                            </div>
                        </div>
                        <div class="auth-step-item">
                            <div class="step-item-icon">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                            </div>
                            <div class="step-item-content">
                                <strong>Follow the link</strong>
                                <span>Click the link in the email to continue.</span>
                            </div>
                        </div>
                        <div class="auth-step-item">
                            <div class="step-item-icon">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><polyline points="20 6 9 17 4 12"/></svg>
                            </div>
                            <div class="step-item-content">
                                <strong>Set a new password</strong>
                                <span>Choose a strong password and you're all set!</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Handwritten Signature -->
                <div class="auth-signature-wrap">
                    <span class="auth-signature-text">Your Vision Our Code</span>
                </div>
            </div>

            <!-- Right Panel: White Card Form -->
            <div class="auth-form-panel-v2">

                <?php if ($step === 'verify'): ?>
                    <!-- Step 2: Enter Verification Code & New Password -->
                    <div class="auth-form-head">
                        <h2>Set New Password</h2>
                        <p>Enter the 6-digit code sent to your email and set a new password.</p>
                    </div>

                    <?php if ($notice): ?>
                        <div class="success" style="margin-bottom: 18px; font-size: 13.5px; border-radius: 12px;">
                            <?php echo $notice; ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($error): ?>
                        <div class="error" style="margin-bottom: 18px; font-size: 13.5px; border-radius: 12px;">
                            <?php echo htmlspecialchars($error); ?>
                        </div>
                    <?php endif; ?>

                    <form method="post" action="?step=verify" novalidate>
                        <?php echo csrf_input(); ?>

                        <div class="auth-form-group">
                            <label for="verifyEmail">Email Address</label>
                            <div class="auth-input-wrapper">
                                <span class="auth-input-icon">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
                                </span>
                                <input type="email" id="verifyEmail" name="email" class="auth-text-input" placeholder="you@example.com" value="<?php echo htmlspecialchars($prefillEmail); ?>" required autocomplete="email">
                            </div>
                        </div>

                        <div class="auth-form-group">
                            <label for="resetCode">6-Digit Verification Code</label>
                            <div class="auth-input-wrapper">
                                <span class="auth-input-icon">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="7.5" cy="15.5" r="5.5"/><path d="m21 2-9.6 9.6"/><path d="m15.5 7.5 3 3L22 7l-3-3"/></svg>
                                </span>
                                <input type="text" id="resetCode" name="code" class="auth-text-input" placeholder="e.g. 583921" maxlength="6" required autocomplete="one-time-code" style="letter-spacing: 0.2em; font-weight: 800; font-size: 16px;">
                            </div>
                        </div>

                        <div class="auth-form-group">
                            <label for="newPassword">New Password</label>
                            <div class="auth-input-wrapper">
                                <span class="auth-input-icon">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                                </span>
                                <input type="password" id="newPassword" name="new_password" class="auth-text-input" placeholder="Enter new strong password" required autocomplete="new-password">
                                <button type="button" class="auth-password-toggle-btn" onclick="toggleAuthPassword(this)" aria-label="Toggle password visibility">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                                </button>
                            </div>
                        </div>

                        <div class="auth-form-group">
                            <label for="confirmPassword">Confirm New Password</label>
                            <div class="auth-input-wrapper">
                                <span class="auth-input-icon">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                                </span>
                                <input type="password" id="confirmPassword" name="confirm_password" class="auth-text-input" placeholder="Confirm new password" required autocomplete="new-password">
                                <button type="button" class="auth-password-toggle-btn" onclick="toggleAuthPassword(this)" aria-label="Toggle password visibility">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                                </button>
                            </div>
                        </div>

                        <button type="submit" name="verify_reset" class="auth-submit-btn" style="margin-top: 14px;">
                            <span>Reset Password</span>
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                        </button>

                        <div style="text-align: center; margin-top: 18px;">
                            <a href="<?php echo $baseUrl; ?>login.php" class="auth-back-link">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
                                <span>Back to Login</span>
                            </a>
                        </div>
                    </form>

                <?php else: ?>
                    <!-- Step 1: Request Reset Link -->
                    <div class="auth-form-head">
                        <h2>Reset Your Password</h2>
                        <p>Enter your email address and we'll send you a reset link.</p>
                    </div>

                    <?php if ($notice): ?>
                        <div class="success" style="margin-bottom: 18px; font-size: 13.5px; border-radius: 12px;">
                            <?php echo $notice; ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($error): ?>
                        <div class="error" style="margin-bottom: 18px; font-size: 13.5px; border-radius: 12px;">
                            <?php echo htmlspecialchars($error); ?>
                        </div>
                    <?php endif; ?>

                    <form method="post" action="?step=request" novalidate>
                        <?php echo csrf_input(); ?>

                        <div class="auth-form-group">
                            <label for="resetEmail">Email Address</label>
                            <div class="auth-input-wrapper">
                                <span class="auth-input-icon">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
                                </span>
                                <input type="email" id="resetEmail" name="email" class="auth-text-input" placeholder="you@example.com" value="<?php echo htmlspecialchars($prefillEmail); ?>" required autocomplete="email">
                            </div>
                        </div>

                        <button type="submit" name="request_reset" class="auth-submit-btn" style="margin-top: 14px;">
                            <span>Send Reset Link</span>
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                        </button>

                        <div style="text-align: center; margin-top: 20px;">
                            <a href="<?php echo $baseUrl; ?>login.php" class="auth-back-link">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
                                <span>Back to Login</span>
                            </a>
                        </div>
                    </form>
                <?php endif; ?>

            </div>
        </div>
    </main>

    <!-- Bottom Double Green Wave Footer -->
    <?php render_auth_footer($settings); ?>

</body>
</html>
