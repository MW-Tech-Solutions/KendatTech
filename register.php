<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/mailer.php';
require_once __DIR__ . '/includes/csrf.php';

require_csrf_token();

if (is_logged_in()) {
    header("Location: " . $baseUrl . "dashboard.php");
    exit;
}

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
        $error = 'Password and verify password must match.';
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

            $notice = "Registration successful! We have sent an activation link to <strong>" . htmlspecialchars($email) . "</strong>. Please check your email inbox to activate your account before logging in.";
        } catch (Throwable $e) {
            $error = $e->getMessage();
        }
    }
}
?>

<main>
    <section class="auth-wrap modern-auth">
        <div class="auth-visual">
            <div>
                <span class="eyebrow">Kendat Client Portal</span>
                <h1>Create your client workspace</h1>
                <p>Register once, then book appointments, request custom software projects, and monitor every submission from your workspace dashboard.</p>
                <div class="auth-chip-grid" style="margin-top: 20px;">
                    <span><?php echo render_icon('CalendarCheck', 16); ?>Appointments</span>
                    <span><?php echo render_icon('ClipboardList', 16); ?>Project intake</span>
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
            <form class="glass-card form auth-card modern-auth-card register-card" method="post" action="">
                <?php echo csrf_input(); ?>
                <div class="form-head">
                    <span class="form-badge"><?php echo render_icon('UserPlus', 15); ?>NEW CLIENT REGISTRATION</span>
                    <h2>Create Account</h2>
                    <p>Start a secure Kendat profile for appointments and project requests.</p>
                </div>

                <?php if ($notice): ?>
                    <div class="notice success" style="padding: 16px; border-radius: 12px; background: rgba(34, 197, 94, 0.12); border: 1px solid rgba(34, 197, 94, 0.3); color: #166534; font-size: 14px; line-height: 1.6; margin-bottom: 20px;">
                        <?php echo $notice; ?>
                    </div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="error"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>

                <div class="register-grid">
                    <label>Full Name
                        <input name="full_name" type="text" placeholder="Tunde Adebayo" value="<?php echo htmlspecialchars($_POST['full_name'] ?? ''); ?>" required>
                    </label>

                    <label>Email Address
                        <input name="email" type="email" placeholder="tunde.adebayo@company.ng" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
                    </label>

                    <label>Phone Number
                        <input name="phone" type="tel" placeholder="+234 800 000 0000" value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>" required>
                    </label>

                    <label>Password
                        <span class="password-field">
                            <input name="password" type="password" placeholder="Create a password" required>
                            <button type="button" class="password-toggle" aria-label="Toggle password visibility">
                                <span class="icon-eye"><?php echo render_icon('Eye', 18); ?></span>
                                <span class="icon-eye-off" style="display:none;"><?php echo render_icon('EyeOff', 18); ?></span>
                            </button>
                        </span>
                    </label>

                    <div class="wide-field">
                        <label>Confirm Password
                            <span class="password-field">
                                <input name="confirm" type="password" placeholder="Verify password" required>
                                <button type="button" class="password-toggle" aria-label="Toggle password visibility">
                                    <span class="icon-eye"><?php echo render_icon('Eye', 18); ?></span>
                                    <span class="icon-eye-off" style="display:none;"><?php echo render_icon('EyeOff', 18); ?></span>
                                </button>
                            </span>
                        </label>
                    </div>
                </div>

                <button class="tc-pill-btn-blue" type="submit" style="width: 100%; justify-content: center; margin-top: 10px;">
                    Create Client Profile <?php echo render_icon('Sparkles', 18); ?>
                </button>

                <div class="auth-switch" style="text-align: center; margin-top: 20px; font-size: 14px; color: var(--muted);">
                    Already registered? <a href="<?php echo $baseUrl; ?>login.php" style="color: var(--blue); font-weight: 800; text-decoration: none;">Login to Portal -></a>
                </div>
            </form>
        </div>
    </section>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
