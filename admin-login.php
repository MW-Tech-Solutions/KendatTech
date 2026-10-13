<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/headers.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/csrf.php';
require_once __DIR__ . '/includes/rate_limiter.php';
require_once __DIR__ . '/includes/audit_logger.php';
require_once __DIR__ . '/includes/auth_layout.php';

$baseUrl = get_base_url();
$settings = get_settings();

if (is_admin()) {
    if (!headers_sent()) {
        header("Location: " . $baseUrl . "admin/index.php");
    }
    echo '<script>window.location.href = "' . htmlspecialchars($baseUrl) . 'admin/index.php";</script>';
    exit;
}

require_csrf_token();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';
    $rateKey = 'login_admin_' . md5(($_SERVER['REMOTE_ADDR'] ?? '127.0.0.1') . '_' . $email);

    if (!RateLimiter::check($rateKey, 5, 900)) {
        $error = 'Too many admin login attempts. Please wait 15 minutes before trying again.';
        audit_log('ADMIN_LOGIN_RATELIMIT_EXCEEDED', 'admin', null, ['email' => $email]);
    } elseif (empty($email) || empty($password)) {
        $error = 'Please enter admin email and password.';
    } else {
        $pdo = get_db();
        $stmt = $pdo->prepare("SELECT * FROM admins WHERE email = ? AND status = 'active' LIMIT 1");
        $stmt->execute([$email]);
        $adminAccount = $stmt->fetch();

        if ($adminAccount && password_verify($password, $adminAccount['password_hash'])) {
            RateLimiter::clear($rateKey);
            login_user($adminAccount, $adminAccount['role'] ?? 'admin');
            audit_log('ADMIN_LOGIN_SUCCESS', 'admin', (string)$adminAccount['id']);
            if (!headers_sent()) {
                header("Location: " . $baseUrl . "admin/index.php");
            }
            echo '<script>window.location.href = "' . htmlspecialchars($baseUrl) . 'admin/index.php";</script>';
            exit;
        } else {
            RateLimiter::hit($rateKey, 900);
            audit_log('ADMIN_LOGIN_FAILED', 'admin', null, ['email' => $email]);
            $error = 'Invalid administrator credentials.';
        }
    }
}

$pageTitle = 'Staff & Admin Login - ' . ($settings['company_name'] ?? 'KendatTech');
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
                        <span class="banner-brand-slogan">ADMINISTRATOR &bull; CONTROL &bull; PORTAL</span>
                    </div>

                    <!-- Welcome Heading -->
                    <h1 class="auth-banner-title">
                        Admin Portal<br>to <span class="highlight-text">KendatTech</span>
                    </h1>
                    <p class="auth-banner-desc">
                        Sign in with your administrative credentials to manage clients, bookings, projects and system configurations.
                    </p>

                    <!-- 3D Laptop Illustration -->
                    <div class="auth-illustration-box">
                        <img src="<?php echo $baseUrl; ?>assets/images/auth-login-laptop.jpg" alt="KendatTech Administrator Control Center" class="auth-illustration-img">
                    </div>

                    <!-- 3 Features Row -->
                    <div class="auth-features-row">
                        <div class="auth-feature-pill">
                            <div class="feature-pill-icon">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                            </div>
                            <span class="feature-pill-text">Encrypted Session</span>
                        </div>
                        <div class="auth-feature-pill">
                            <div class="feature-pill-icon">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg>
                            </div>
                            <span class="feature-pill-text">Live Telemetry</span>
                        </div>
                        <div class="auth-feature-pill">
                            <div class="feature-pill-icon">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
                            </div>
                            <span class="feature-pill-text">Root Governance</span>
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
                    <h2>Staff &amp; Admin Access</h2>
                    <p>Enter your administrator credentials below.</p>
                </div>

                <!-- Error Alert -->
                <?php if ($error): ?>
                    <div class="error" style="margin-bottom: 18px; font-size: 13.5px; border-radius: 12px;">
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <!-- Form -->
                <form method="post" action="" novalidate>
                    <?php echo csrf_input(); ?>

                    <!-- Email Field -->
                    <div class="auth-form-group">
                        <label for="adminEmail">Admin Email</label>
                        <div class="auth-input-wrapper">
                            <span class="auth-input-icon">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
                            </span>
                            <input type="email" id="adminEmail" name="email" class="auth-text-input" placeholder="admin@kendat.com" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required autocomplete="email">
                        </div>
                    </div>

                    <!-- Password Field -->
                    <div class="auth-form-group">
                        <label for="adminPassword">Password</label>
                        <div class="auth-input-wrapper">
                            <span class="auth-input-icon">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                            </span>
                            <input type="password" id="adminPassword" name="password" class="auth-text-input" placeholder="Enter administrative password" required autocomplete="current-password">
                            <button type="button" class="auth-password-toggle-btn" onclick="toggleAuthPassword(this)" aria-label="Toggle password visibility">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                            </button>
                        </div>
                    </div>

                    <!-- Forgot Password Link -->
                    <div style="display: flex; justify-content: flex-end; margin: 12px 0 20px;">
                        <a href="<?php echo $baseUrl; ?>forgot-password.php" class="auth-forgot-link">Forgot admin credentials?</a>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="auth-submit-btn">
                        <span>Authenticate &amp; Enter Console</span>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                    </button>

                    <!-- Client Portal Back Link -->
                    <div class="auth-prompt-footer">
                        Are you a client?
                        <a href="<?php echo $baseUrl; ?>login.php" class="auth-prompt-link">Client Portal Login</a>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <!-- Bottom Double Green Wave Footer -->
    <?php render_auth_footer($settings); ?>

</body>
</html>
