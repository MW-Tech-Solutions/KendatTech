<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/header.php';

$email = strtolower(trim($_GET['email'] ?? ''));
$token = trim($_GET['token'] ?? '');

$success = false;
$message = '';

if (!empty($email) && !empty($token)) {
    try {
        $pdo = get_db();
        $stmt = $pdo->prepare("SELECT id, full_name, is_activated FROM users WHERE email = ? AND activation_token = ? LIMIT 1");
        $stmt->execute([$email, $token]);
        $userRow = $stmt->fetch();

        if ($userRow) {
            $upd = $pdo->prepare("UPDATE users SET is_activated = 1, activation_token = NULL, activated_at = NOW() WHERE id = ?");
            $upd->execute([$userRow['id']]);
            $success = true;
            $message = "Your account has been activated successfully! You can now log in to access your client workspace.";
        } else {
            // Check if already activated
            $chk = $pdo->prepare("SELECT id, is_activated FROM users WHERE email = ? LIMIT 1");
            $chk->execute([$email]);
            $exist = $chk->fetch();
            if ($exist && (int)$exist['is_activated'] === 1) {
                $success = true;
                $message = "Your account is already activated. Please proceed to login.";
            } else {
                $message = "Invalid or expired activation link. Please request a new activation email from the login page.";
            }
        }
    } catch (Throwable $e) {
        $message = "Activation error: " . $e->getMessage();
    }
} else {
    $message = "Missing activation parameters. Please check the link in your confirmation email.";
}
?>

<main>
    <section class="auth-wrap modern-auth" style="min-height: 70vh; display: grid; place-items: center; padding: 40px 20px;">
        <div class="auth-console glass-card" style="max-width: 540px; width: 100%; text-align: center; padding: 40px 32px;">
            <?php if ($success): ?>
                <div style="width: 64px; height: 64px; border-radius: 50%; background: rgba(34, 197, 94, 0.12); border: 1px solid rgba(34, 197, 94, 0.3); color: #22c55e; display: grid; place-items: center; margin: 0 auto 20px;">
                    <?php echo render_icon('CheckCircle2', 32); ?>
                </div>
                <h1 style="font-size: 24px; font-weight: 800; margin: 0 0 12px; color: #0f172a;">Account Activated!</h1>
                <p style="color: #475569; font-size: 15px; line-height: 1.6; margin-bottom: 28px;"><?php echo htmlspecialchars($message); ?></p>
                <a href="<?php echo $baseUrl; ?>login.php" class="btn primary" style="width: 100%; min-height: 44px; display: inline-flex; align-items: center; justify-content: center;">
                    Proceed to Login <?php echo render_icon('ArrowRight', 18); ?>
                </a>
            <?php else: ?>
                <div style="width: 64px; height: 64px; border-radius: 50%; background: rgba(239, 68, 68, 0.12); border: 1px solid rgba(239, 68, 68, 0.3); color: #ef4444; display: grid; place-items: center; margin: 0 auto 20px;">
                    <?php echo render_icon('TriangleAlert', 32); ?>
                </div>
                <h1 style="font-size: 24px; font-weight: 800; margin: 0 0 12px; color: #0f172a;">Activation Failed</h1>
                <p style="color: #475569; font-size: 15px; line-height: 1.6; margin-bottom: 28px;"><?php echo htmlspecialchars($message); ?></p>
                <div style="display: flex; gap: 12px; flex-wrap: wrap;">
                    <a href="<?php echo $baseUrl; ?>login.php" class="btn primary" style="flex: 1; min-height: 44px; display: inline-flex; align-items: center; justify-content: center;">
                        Go to Login
                    </a>
                    <a href="<?php echo $baseUrl; ?>register.php" class="btn small" style="flex: 1; min-height: 44px; display: inline-flex; align-items: center; justify-content: center;">
                        Register Again
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </section>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
