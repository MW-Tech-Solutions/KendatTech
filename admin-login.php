<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/csrf.php';
require_once __DIR__ . '/includes/rate_limiter.php';
require_once __DIR__ . '/includes/audit_logger.php';

require_csrf_token();

if (is_admin()) {
    header("Location: " . $baseUrl . "admin/index.php");
    exit;
}

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
            header("Location: " . $baseUrl . "admin/index.php");
            exit;
        } else {
            RateLimiter::hit($rateKey, 900);
            audit_log('ADMIN_LOGIN_FAILED', 'admin', null, ['email' => $email]);
            $error = 'Invalid administrator credentials.';
        }
    }
}
?>

<main>
    <section class="auth-wrap modern-auth admin-auth">
        <div class="auth-visual">
            <span class="eyebrow">Kendat Admin</span>
            <h1>Admin control access</h1>
            <p>Manage services, projects, appointments, requests, users, content, and company settings.</p>
            <div class="auth-chip-grid">
                <span><?php echo render_icon('ShieldCheck'); ?>Protected dashboard</span>
                <span><?php echo render_icon('Settings'); ?>Content control</span>
                <span><?php echo render_icon('Activity'); ?>Live operations</span>
            </div>
            <div class="auth-console">
                <div><span stop-dot></span> Manage company services and portfolio</div>
                <div><span stop-dot></span> Review appointments and project requests</div>
                <div><span stop-dot></span> Update website content and settings</div>
            </div>
        </div>

        <div class="auth-form-panel">
            <form class="glass-card form auth-card modern-auth-card" method="post" action="">
                <?php echo csrf_input(); ?>
                <div class="form-head">
                    <span class="form-badge"><?php echo render_icon('ShieldCheck', 15); ?>Secure admin</span>
                    <h1>Admin Login</h1>
                    <p>Use your administrator credentials to open the enterprise dashboard.</p>
                </div>

                <?php if ($error): ?>
                    <p class="error"><?php echo htmlspecialchars($error); ?></p>
                <?php endif; ?>

                <label>Email
                    <input name="email" type="email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
                </label>

                <label>Password
                    <span class="password-field">
                        <input name="password" type="password" required>
                        <button type="button" class="password-toggle" aria-label="Show password">
                            <?php echo render_icon('Eye', 18); ?>
                        </button>
                    </span>
                </label>

                <button class="btn primary" type="submit"><?php echo render_icon('ArrowRight'); ?>Login</button>
            </form>
        </div>
    </section>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
