<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/mailer.php';
require_once __DIR__ . '/includes/csrf.php';

require_csrf_token();

$messageSent = '';
$errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $fullName = trim($_POST['full_name'] ?? '');
        $email = strtolower(trim($_POST['email'] ?? ''));
        $phone = trim($_POST['phone'] ?? '');
        $preferredDate = $_POST['preferred_date'] ?? '';
        $preferredTime = $_POST['preferred_time'] ?? '';
        $appointmentType = trim($_POST['appointment_type'] ?? 'Software Consultation');
        $messageText = trim($_POST['message'] ?? '');

        if (empty($fullName) || empty($email) || empty($preferredDate) || empty($preferredTime)) {
            throw new Exception("Please fill in all required fields (Name, Email, Date, and Time).");
        }

        $userId = is_logged_in() ? $_SESSION['user_id'] : null;
        $pdo = get_db();

        $stmt = $pdo->prepare("INSERT INTO appointments (user_id, full_name, email, phone, preferred_date, preferred_time, appointment_type, message) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$userId, $fullName, $email, $phone, $preferredDate, $preferredTime, $appointmentType, $messageText]);

        // Send Email Notifications via SMTP
        send_appointment_receipt_email($email, $fullName, $preferredDate, $preferredTime, $appointmentType);
        send_admin_appointment_alert($fullName, $email, $phone, $preferredDate, $preferredTime, $appointmentType, $messageText);

        $messageSent = "Appointment submitted successfully! A confirmation email has been sent to " . htmlspecialchars($email) . ". Our team will review your schedule request and confirm.";
    } catch (Throwable $e) {
        $errorMessage = $e->getMessage();
    }
}

$user = get_current_user_data();
?>

<main>
    <!-- Sub-Page Hero Banner -->
    <section class="page-hero booking-hero">
        <div class="contact-hero-content">
            <span class="eyebrow">Book Strategy Session</span>
            <h1>Schedule a consultation with Kendat.</h1>
            <p>Select your date and time for a technical discussion on custom software systems, school portals, AI automation, or cloud infrastructure.</p>
            <div class="booking-points">
                <span class="booking-point-pill"><?php echo render_icon('ShieldCheck', 16); ?> 100% Confidential</span>
                <span class="booking-point-pill"><?php echo render_icon('Clock3', 16); ?> 24h Review Window</span>
                <span class="booking-point-pill"><?php echo render_icon('Workflow', 16); ?> Direct Architect Meeting</span>
            </div>
        </div>
        <div class="booking-orb-card">
            <?php echo render_icon('CalendarDays', 42); ?>
            <strong>Consultation Pipeline</strong>
            <p>Submit your preferred time, receive swift confirmation, and begin your software discovery phase.</p>
        </div>
    </section>

    <!-- Booking Form & Sidebar Grid -->
    <section class="band booking-layout booking-page">
        <!-- Left: Premium Booking Form -->
        <form class="glass-card form premium-form booking-form-card" method="post" action="">
            <?php echo csrf_input(); ?>
            <div class="form-head">
                <span class="form-badge"><?php echo render_icon('CalendarCheck', 15); ?>RESERVATION FORM</span>
                <h2>Book Your Appointment</h2>
                <p>Pick a convenient schedule and specify your project needs. Our engineering team will review and confirm.</p>
            </div>

            <?php if ($messageSent): ?>
                <div class="success"><?php echo htmlspecialchars($messageSent); ?></div>
            <?php endif; ?>

            <?php if ($errorMessage): ?>
                <div class="error"><?php echo htmlspecialchars($errorMessage); ?></div>
            <?php endif; ?>

            <div class="booking-form-grid">
                <label>Full Name
                    <input name="full_name" type="text" placeholder="Amina Bello" value="<?php echo htmlspecialchars($_POST['full_name'] ?? $user['full_name'] ?? ''); ?>" required>
                </label>

                <label>Email Address
                    <input name="email" type="email" placeholder="amina.bello@company.ng" value="<?php echo htmlspecialchars($_POST['email'] ?? $user['email'] ?? ''); ?>" required>
                </label>
            </div>

            <div class="booking-form-grid">
                <label>Phone Number
                    <input name="phone" type="tel" placeholder="+234 800 000 0000" value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>">
                </label>

                <label>Appointment Type
                    <select name="appointment_type">
                        <option value="Software Consultation">Software Consultation</option>
                        <option value="AI Software & Automation">AI Software & Automation</option>
                        <option value="School Portal Project">School Portal Project</option>
                        <option value="Business System Development">Business System Development</option>
                        <option value="API & Cloud Infrastructure">API & Cloud Infrastructure</option>
                    </select>
                </label>
            </div>

            <div class="booking-form-grid">
                <label>Preferred Date
                    <input name="preferred_date" type="date" value="<?php echo htmlspecialchars($_POST['preferred_date'] ?? ''); ?>" required>
                </label>

                <label>Preferred Time
                    <input name="preferred_time" type="time" value="<?php echo htmlspecialchars($_POST['preferred_time'] ?? ''); ?>" required>
                </label>
            </div>

            <label>Discussion Topics / Requirements
                <textarea name="message" rows="4" placeholder="Tell us about the software, app, or portal you want to discuss..." required><?php echo htmlspecialchars($_POST['message'] ?? ''); ?></textarea>
            </label>

            <button class="tc-pill-btn-blue" type="submit" style="width: 100%; justify-content: center; margin-top: 10px;">
                Confirm Strategy Session <?php echo render_icon('CalendarCheck', 18); ?>
            </button>
        </form>

        <!-- Right: Sidebar Case & Process Timeline -->
        <aside class="booking-aside">
            <div class="glass-card contact-info-card">
                <div class="card-icon" style="margin-bottom:12px;"><?php echo render_icon('Sparkles', 22); ?></div>
                <h3>Consultation Focus Areas</h3>
                <p style="color:var(--muted); font-size:14px; line-height:1.6; margin:0 0 16px;">
                    Our senior technical team handles strategy sessions across software engineering disciplines:
                </p>
                <div class="project-tech-tags">
                    <span class="tech-tag-pill">Custom Web Systems</span>
                    <span class="tech-tag-pill">School Portals</span>
                    <span class="tech-tag-pill">AI Automation</span>
                    <span class="tech-tag-pill">Mobile Platforms</span>
                    <span class="tech-tag-pill">API Integration</span>
                    <span class="tech-tag-pill">Cloud Infrastructure</span>
                </div>
            </div>

            <div class="glass-card timeline-card">
                <h3>What Happens Next</h3>
                <ul class="booking-timeline-list">
                    <li>
                        <span class="step-num">01</span>
                        <div>
                            <strong>Review & Approval</strong>
                            <small>Admin evaluates your requested date and time slot.</small>
                        </div>
                    </li>
                    <li>
                        <span class="step-num">02</span>
                        <div>
                            <strong>Confirmation Email</strong>
                            <small>You receive a calendar invite & meeting link.</small>
                        </div>
                    </li>
                    <li>
                        <span class="step-num">03</span>
                        <div>
                            <strong>Discovery & Scope</strong>
                            <small>We map your system architecture, budget & timeline.</small>
                        </div>
                    </li>
                </ul>
            </div>
        </aside>
    </section>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
