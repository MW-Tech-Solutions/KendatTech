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

$error = '';
$notice = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = trim($_POST['full_name'] ?? '');
    $email = strtolower(trim($_POST['email'] ?? ''));
    $phone = trim($_POST['phone'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $confirm = trim($_POST['confirm'] ?? '');

    if (empty($fullName) || empty($email) || empty($phone) || empty($password)) {
        $error = 'Please fill in all required fields.';
    } elseif ($password !== $confirm) {
        $error = 'Password and confirm password must match.';
    } else {
        try {
            $pdo = get_db();
            $chkStmt = $pdo->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
            $chkStmt->execute([$email]);
            if ($chkStmt->fetch()) {
                throw new Exception("An account with this email address already exists.");
            }

            $activationToken = bin2hex(random_bytes(32));
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (full_name, email, phone, password_hash, role, status, is_activated, activation_token) VALUES (?, ?, ?, ?, 'user', 'active', 0, ?)");
            $stmt->execute([$fullName, $email, $phone, $hash, $activationToken]);

            // Send Account Activation Email via SMTP
            send_account_activation_email($email, $fullName, $activationToken);
            audit_log('REGISTER_SUCCESS', 'user', (string)$pdo->lastInsertId(), ['email' => $email]);

            $notice = "Registration successful! We have sent an activation link to <strong>" . htmlspecialchars($email) . "</strong>. Please check your email inbox to activate your account before logging in.";
        } catch (Throwable $e) {
            $error = $e->getMessage();
        }
    }
}

$pageTitle = 'Register an Account - ' . ($settings['company_name'] ?? 'KendatTech');
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

            <!-- Left Banner: Light Mint Soft Tint with 3D Desktop Monitor Illustration -->
            <div class="auth-banner-panel auth-banner-light">
                <div>
                    <!-- Brand Tag -->
                    <div class="auth-banner-brand-row">
                        <a href="<?php echo $baseUrl; ?>" class="banner-brand-link">
                            <span class="banner-brand-text">Kendat<span style="color:#0B5D3B;">Tech</span></span>
                        </a>
                        <span class="banner-brand-slogan">WE DESIGN &bull; WE DEVELOP &bull; WE DELIVER</span>
                    </div>

                    <!-- Main Heading -->
                    <h1 class="auth-banner-title">
                        Create Your<br><span class="highlight-text">KendatTech</span> Account
                    </h1>
                    <p class="auth-banner-desc">
                        Join our growing community and get started with your next big idea.
                    </p>

                    <!-- 3D Desktop Illustration -->
                    <div class="auth-illustration-box">
                        <img src="<?php echo $baseUrl; ?>assets/images/auth-register-desktop.jpg" alt="KendatTech Development & Project Environment" class="auth-illustration-img">
                    </div>

                    <!-- Checklist Items -->
                    <div class="auth-checklist">
                        <div class="auth-check-item">
                            <div class="check-item-icon">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.5"><polyline points="20 6 9 17 4 12"/></svg>
                            </div>
                            <span>Access to all our products &amp; services</span>
                        </div>
                        <div class="auth-check-item">
                            <div class="check-item-icon">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.5"><polyline points="20 6 9 17 4 12"/></svg>
                            </div>
                            <span>Track your projects and updates</span>
                        </div>
                        <div class="auth-check-item">
                            <div class="check-item-icon">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.5"><polyline points="20 6 9 17 4 12"/></svg>
                            </div>
                            <span>Get expert support whenever you need</span>
                        </div>
                        <div class="auth-check-item">
                            <div class="check-item-icon">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.5"><polyline points="20 6 9 17 4 12"/></svg>
                            </div>
                            <span>Be part of a growing tech community</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Panel: White Card Form -->
            <div class="auth-form-panel-v2">
                <div class="auth-form-head">
                    <h2>Register an Account</h2>
                    <p>Fill in the details below to get started.</p>
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
                <?php endif; ?>

                <!-- Registration Form -->
                <form method="post" action="" novalidate>
                    <?php echo csrf_input(); ?>

                    <!-- Full Name Field -->
                    <div class="auth-form-group">
                        <label for="regFullName">Full Name</label>
                        <div class="auth-input-wrapper">
                            <span class="auth-input-icon">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                            </span>
                            <input type="text" id="regFullName" name="full_name" class="auth-text-input" placeholder="Enter your full name" value="<?php echo htmlspecialchars($_POST['full_name'] ?? ''); ?>" required autocomplete="name">
                        </div>
                    </div>

                    <!-- Email Field -->
                    <div class="auth-form-group">
                        <label for="regEmail">Email Address</label>
                        <div class="auth-input-wrapper">
                            <span class="auth-input-icon">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
                            </span>
                            <input type="email" id="regEmail" name="email" class="auth-text-input" placeholder="you@example.com" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required autocomplete="email">
                        </div>
                    </div>

                    <!-- Phone Number Field -->
                    <div class="auth-form-group">
                        <label for="regPhone">Phone Number</label>
                        <div class="auth-input-wrapper">
                            <span class="auth-input-icon">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                            </span>
                            <input type="tel" id="regPhone" name="phone" class="auth-text-input" placeholder="+234 801 234 5678" value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>" required autocomplete="tel">
                        </div>
                    </div>

                    <!-- Password Field -->
                    <div class="auth-form-group">
                        <label for="regPassword">Password</label>
                        <div class="auth-input-wrapper">
                            <span class="auth-input-icon">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                            </span>
                            <input type="password" id="regPassword" name="password" class="auth-text-input" placeholder="Create a password" required autocomplete="new-password">
                            <button type="button" class="auth-password-toggle-btn" onclick="toggleAuthPassword(this)" aria-label="Toggle password visibility">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                            </button>
                        </div>
                    </div>

                    <!-- Confirm Password Field -->
                    <div class="auth-form-group">
                        <label for="regConfirm">Confirm Password</label>
                        <div class="auth-input-wrapper">
                            <span class="auth-input-icon">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                            </span>
                            <input type="password" id="regConfirm" name="confirm" class="auth-text-input" placeholder="Confirm your password" required autocomplete="new-password">
                            <button type="button" class="auth-password-toggle-btn" onclick="toggleAuthPassword(this)" aria-label="Toggle password visibility">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                            </button>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="auth-submit-btn" style="margin-top: 10px;">
                        <span>Create Account</span>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                    </button>

                    <!-- OR Divider -->
                    <div class="auth-or-divider">OR</div>

                    <!-- Continue with Google Button -->
                    <a href="<?php echo $baseUrl; ?>register.php?provider=google" class="auth-google-btn" onclick="event.preventDefault(); alert('Google OAuth integration can be linked with your Google Client ID.');">
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
                        Already have an account?
                        <a href="<?php echo $baseUrl; ?>login.php" class="auth-prompt-link">Login here</a>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <!-- Bottom Double Green Wave Footer -->
    <?php render_auth_footer($settings); ?>

</body>
</html>
