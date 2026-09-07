<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/mailer.php';
require_once __DIR__ . '/includes/csrf.php';
require_once __DIR__ . '/includes/rate_limiter.php';
require_once __DIR__ . '/includes/audit_logger.php';

require_csrf_token();

if (is_logged_in()) {
    header("Location: " . (is_admin() ? $baseUrl . 'admin/index.php' : $baseUrl . 'dashboard.php'));
    exit;
}

$error = '';
$notice = '';
$unactivatedEmail = '';

// Handle Resend Activation Link
if (isset($_POST['resend_activation'])) {
    $resendEmail = strtolower(trim($_POST['resend_email'] ?? ''));
    if (!empty($resendEmail)) {
        $pdo = get_db();
        $stmt = $pdo->prepare("SELECT id, full_name, email, is_activated, activation_token FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$resendEmail]);
        $row = $stmt->fetch();

        if ($row) {
            if ((int)$row['is_activated'] === 1) {
                $notice = "Your account is already activated. You can log in below.";
            } else {
                $token = $row['activation_token'];
                if (empty($token)) {
                    $token = bin2hex(random_bytes(32));
                    $upd = $pdo->prepare("UPDATE users SET activation_token = ? WHERE id = ?");
                    $upd->execute([$token, $row['id']]);
                }
                send_account_activation_email($row['email'], $row['full_name'], $token);
                $notice = "A new activation link has been sent to <strong>" . htmlspecialchars($resendEmail) . "</strong>. Please check your inbox.";
            }
        } else {
            $error = "No user account found with that email address.";
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login_submit'])) {
    $email = strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';
    $rateKey = 'login_user_' . md5(($_SERVER['REMOTE_ADDR'] ?? '127.0.0.1') . '_' . $email);

    if (!RateLimiter::check($rateKey, 5, 900)) {
        $error = 'Too many login attempts. Please wait 15 minutes before trying again.';
        audit_log('LOGIN_RATELIMIT_EXCEEDED', 'user', null, ['email' => $email]);
    } elseif (empty($email) || empty($password)) {
        $error = 'Please provide both email and password.';
    } else {
        $pdo = get_db();
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND status = 'active' LIMIT 1");
        $stmt->execute([$email]);
        $account = $stmt->fetch();

        if ($account && password_verify($password, $account['password_hash'])) {
            if (isset($account['is_activated']) && (int)$account['is_activated'] === 0) {
                $unactivatedEmail = $email;
                $error = 'Your account has not been activated yet. Please check your email inbox to confirm and activate your account.';
                audit_log('LOGIN_UNACTIVATED', 'user', (string)$account['id']);
            } else {
                RateLimiter::clear($rateKey);
                login_user($account, 'user');
                audit_log('LOGIN_SUCCESS', 'user', (string)$account['id']);
                header("Location: " . $baseUrl . "dashboard.php");
                exit;
            }
        } else {
            RateLimiter::hit($rateKey, 900);
            audit_log('LOGIN_FAILED', 'user', null, ['email' => $email]);
            $error = 'Invalid email or password.';
        }
    }
}
?>

<main>
    <section class="auth-wrap modern-auth">
        <div class="auth-visual">
            <div>
                <span class="eyebrow">Kendat Client Portal</span>
                <h1>Welcome back to Kendat</h1>
                <p>Sign in to book consultation appointments, submit project requirements, and monitor your enterprise software submissions.</p>
                <div class="auth-chip-grid" style="margin-top: 20px;">
                    <span><?php echo render_icon('CalendarCheck', 16); ?>Appointments</span>
                    <span><?php echo render_icon('ClipboardList', 16); ?>Project requests</span>
                    <span><?php echo render_icon('Activity', 16); ?>Live tracking</span>
                </div>
            </div>

            <div class="auth-console">
                <div><span stop-dot></span> Custom enterprise web applications & portals</div>
                <div><span stop-dot></span> AI copilots & data intelligence automation</div>
                <div><span stop-dot></span> High-speed PDO encrypted database backend</div>
                <div><span stop-dot></span> 24h direct engineering review & NDA protection</div>
            </div>

            <div class="auth-footer-badge" style="display: flex; align-items: center; gap: 10px; font-size: 13px; color: #cbd5e1; padding-top: 16px; border-top: 1px solid rgba(255,255,255,0.15);">
                <?php echo render_icon('ShieldCheck', 20); ?>
                <span>100% Confidential & Encrypted Client Workspace</span>
            </div>
        </div>

        <div class="auth-form-panel">
            <form class="glass-card form auth-card modern-auth-card" method="post" action="">
                <?php echo csrf_input(); ?>
                <div class="form-head">
                    <span class="form-badge"><?php echo render_icon('LogIn', 15); ?>CLIENT ACCESS</span>
                    <h2>Login to Portal</h2>
                    <p>Continue to your Kendat client workspace.</p>
                </div>

                <?php if ($notice): ?>
                    <div class="notice success" style="padding: 14px 16px; border-radius: 12px; background: rgba(34, 197, 94, 0.12); border: 1px solid rgba(34, 197, 94, 0.3); color: #166534; font-size: 14px; line-height: 1.6; margin-bottom: 20px;">
                        <?php echo $notice; ?>
                    </div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="error" style="margin-bottom: 16px;"><?php echo htmlspecialchars($error); ?></div>
                    <?php if (!empty($unactivatedEmail)): ?>
                        <div style="margin-bottom: 20px; padding: 14px; border-radius: 12px; background: #f8fafc; border: 1px solid #cbd5e1; text-align: center;">
                            <p style="margin: 0 0 10px; font-size: 13px; color: #475569;">Didn't receive the activation email?</p>
                            <form method="post" action="">
                                <?php echo csrf_input(); ?>
                                <input type="hidden" name="resend_email" value="<?php echo htmlspecialchars($unactivatedEmail); ?>">
                                <button type="submit" name="resend_activation" class="btn small" style="width: 100%;">
                                    Resend Activation Email
                                </button>
                            </form>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>

                <label>Email Address
                    <input name="email" type="email" placeholder="tunde.adebayo@company.ng" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
                </label>

                <label>Password
                    <span class="password-field">
                        <input name="password" type="password" placeholder="Enter your password" required>
                        <button type="button" class="password-toggle" aria-label="Toggle password visibility">
                            <span class="icon-eye"><?php echo render_icon('Eye', 18); ?></span>
                            <span class="icon-eye-off" style="display:none;"><?php echo render_icon('EyeOff', 18); ?></span>
                        </button>
                    </span>
                </label>

                <button class="tc-pill-btn-blue" type="submit" name="login_submit" style="width: 100%; justify-content: center; margin-top: 10px;">
                    Login to Portal <?php echo render_icon('ArrowRight', 18); ?>
                </button>
                
                <div class="auth-switch" style="text-align: center; margin-top: 20px; font-size: 14px; color: var(--muted);">
                    New to Kendat? <a href="<?php echo $baseUrl; ?>register.php" style="color: var(--blue); font-weight: 800; text-decoration: none;">Create account -></a>
                </div>
            </form>
        </div>
    </section>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
