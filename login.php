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
    $targetUrl = is_admin() ? ($baseUrl . 'admin/index.php') : ($baseUrl . 'dashboard.php');
    if (!headers_sent()) {
        header("Location: " . $targetUrl);
    }
    echo '<script>window.location.href = "' . htmlspecialchars($targetUrl) . '";</script>';
    exit;
}

require_csrf_token();

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
                $targetUrl = $baseUrl . "dashboard.php";
                if (!headers_sent()) {
                    header("Location: " . $targetUrl);
                }
                echo '<script>window.location.href = "' . htmlspecialchars($targetUrl) . '";</script>';
                exit;
            }
        } else {
            RateLimiter::hit($rateKey, 900);
            audit_log('LOGIN_FAILED', 'user', null, ['email' => $email]);
            $error = 'Invalid email or password.';
        }
    }
}

$pageTitle = 'Login to Your Account - ' . ($settings['company_name'] ?? 'KendatTech');
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

            <!-- Left Banner: Dark Forest Green with 3D Laptop Illustration -->
            <div class="auth-banner-panel auth-banner-dark">
                <div>
                    <!-- Brand Tag -->
                    <div class="auth-banner-brand-row">
                        <a href="<?php echo $baseUrl; ?>" class="banner-brand-link">
                            <span class="banner-brand-text">Kendat<span style="color:#10B981;">Tech</span></span>
                        </a>
                        <span class="banner-brand-slogan">WE DESIGN &bull; WE DEVELOP &bull; WE DELIVER</span>
                    </div>

                    <!-- Welcome Heading -->
                    <h1 class="auth-banner-title">
                        Welcome Back<br>to <span class="highlight-text">KendatTech</span>
                    </h1>
                    <p class="auth-banner-desc">
                        Log in to your account to access your dashboard, manage your projects and stay connected.
                    </p>

                    <!-- 3D Laptop Illustration -->
                    <div class="auth-illustration-box">
                        <img src="<?php echo $baseUrl; ?>assets/images/auth-login-laptop.jpg" alt="KendatTech Cloud & Development Workspace" class="auth-illustration-img">
                    </div>

                    <!-- 3 Features Row -->
                    <div class="auth-features-row">
                        <div class="auth-feature-pill">
                            <div class="feature-pill-icon">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>
                            </div>
                            <span class="feature-pill-text">Build Innovative Solutions</span>
                        </div>
                        <div class="auth-feature-pill">
                            <div class="feature-pill-icon">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                            </div>
                            <span class="feature-pill-text">Secure &amp; Reliable Platform</span>
                        </div>
                        <div class="auth-feature-pill">
                            <div class="feature-pill-icon">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                            </div>
                            <span class="feature-pill-text">Trusted by Businesses</span>
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
                <div class="auth-form-head">
                    <h2>Login to Your Account</h2>
                    <p>Enter your details below to continue.</p>
                </div>

                <!-- Alerts -->
                <?php if ($notice): ?>
                    <div class="success" style="margin-bottom: 18px; font-size: 13.5px; border-radius: 12px;">
                        <?php echo $notice; ?>
                    </div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="error" style="margin-bottom: 18px; font-size: 13.5px; border-radius: 12px;">
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                    <?php if (!empty($unactivatedEmail)): ?>
                        <div style="margin-bottom: 18px; padding: 12px; border-radius: 12px; background: #f8fafc; border: 1px solid #cbd5e1; text-align: center;">
                            <p style="margin: 0 0 8px; font-size: 12.5px; color: #475569;">Didn't receive the activation email?</p>
                            <form method="post" action="">
                                <?php echo csrf_input(); ?>
                                <input type="hidden" name="resend_email" value="<?php echo htmlspecialchars($unactivatedEmail); ?>">
                                <button type="submit" name="resend_activation" class="auth-submit-btn" style="padding: 8px 16px; font-size: 12.5px;">
                                    Resend Activation Email
                                </button>
                            </form>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>

                <!-- Login Form -->
                <form method="post" action="" novalidate>
                    <?php echo csrf_input(); ?>

                    <!-- Email Field -->
                    <div class="auth-form-group">
                        <label for="loginEmail">Email Address</label>
                        <div class="auth-input-wrapper">
                            <span class="auth-input-icon">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
                            </span>
                            <input type="email" id="loginEmail" name="email" class="auth-text-input" placeholder="you@example.com" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required autocomplete="email">
                        </div>
                    </div>

                    <!-- Password Field -->
                    <div class="auth-form-group">
                        <label for="loginPassword">Password</label>
                        <div class="auth-input-wrapper">
                            <span class="auth-input-icon">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                            </span>
                            <input type="password" id="loginPassword" name="password" class="auth-text-input" placeholder="Enter your password" required autocomplete="current-password">
                            <button type="button" class="auth-password-toggle-btn" onclick="toggleAuthPassword(this)" aria-label="Toggle password visibility">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                            </button>
                        </div>
                    </div>

                    <!-- Remember & Forgot Password -->
                    <div class="auth-row-options">
                        <label class="auth-remember-label">
                            <input type="checkbox" name="remember" id="rememberMe" value="1">
                            <span>Remember me</span>
                        </label>
                        <a href="<?php echo $baseUrl; ?>forgot-password.php" class="auth-forgot-link">Forgot password?</a>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" name="login_submit" class="auth-submit-btn">
                        <span>Login</span>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                    </button>

                    <!-- OR Divider -->
                    <div class="auth-or-divider">OR</div>

                    <!-- Continue with Google Button -->
                    <a href="<?php echo $baseUrl; ?>login.php?provider=google" class="auth-google-btn" onclick="event.preventDefault(); alert('Google OAuth integration can be linked with your Google Client ID.');">
                        <svg class="google-icon-svg" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                            <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                            <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l2.85-2.22.81-.63z" fill="#FBBC05"/>
                            <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84c.87-2.6 3.3-4.52 6.16-4.52z" fill="#EA4335"/>
                        </svg>
                        <span>Continue with Google</span>
                    </a>

                    <!-- Footer Link -->
                    <div class="auth-prompt-footer">
                        Don't have an account?
                        <a href="<?php echo $baseUrl; ?>register.php" class="auth-prompt-link">Register here</a>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <!-- Bottom Double Green Wave Footer -->
    <?php render_auth_footer($settings); ?>

</body>
</html>
