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

<main class="kt-main-wrapper">

    <!-- 1. HERO SECTION (Schedule Your Consultation) -->
    <section class="kt-consult-hero">
        <div class="kt-container">
            <div class="kt-consult-hero-grid">
                <!-- Left Content -->
                <div class="kt-consult-hero-content">
                    <div class="kt-consult-eyebrow">
                        <span>BOOK AN APPOINTMENT</span>
                        <span class="eyebrow-line"></span>
                    </div>
                    <h1 class="kt-consult-hero-title">
                        Schedule Your<br>
                        <span class="kt-text-brand">Consultation</span>
                    </h1>
                    <p class="kt-consult-hero-desc">
                        Book a free consultation or project discussion with our team. We're here to help you bring your ideas to life with the right technology solutions.
                    </p>
                    
                    <!-- 3 Hero Mini Badges -->
                    <div class="kt-consult-hero-badges">
                        <div class="kt-hero-badge-item">
                            <span class="badge-icon-wrap"><?php echo render_icon('Calendar', 18); ?></span>
                            <span class="badge-text">Quick & Easy Booking</span>
                        </div>
                        <div class="kt-hero-badge-item">
                            <span class="badge-icon-wrap"><?php echo render_icon('ShieldCheck', 18); ?></span>
                            <span class="badge-text">Expert Consultation</span>
                        </div>
                        <div class="kt-hero-badge-item">
                            <span class="badge-icon-wrap"><?php echo render_icon('Clock', 18); ?></span>
                            <span class="badge-text">Flexible Schedule</span>
                        </div>
                    </div>
                </div>

                <!-- Right Visual (Desk Mockup with KendatTech Laptop & Green Mug) -->
                <div class="kt-consult-hero-visual">
                    <img src="<?php echo $baseUrl; ?>assets/images/consultation-hero-visual.png" alt="KendatTech Consultation - Let's Build Something Great Together" class="kt-consult-hero-img">
                </div>
            </div>
        </div>
    </section>

    <!-- 2. FORM & SIDEBAR SECTION (Fill in the Details & Immediate Assistance) -->
    <section class="kt-consult-main-section" id="appointment-form">
        <div class="kt-container">
            <div class="kt-consult-layout-grid">
                
                <!-- Left: Booking Form Card -->
                <div class="kt-consult-form-card">
                    <div class="kt-form-card-badge">
                        <?php echo render_icon('Calendar', 14); ?>
                        <span>BOOK APPOINTMENT</span>
                    </div>
                    <h2 class="kt-form-card-title">Fill in the Details</h2>
                    <p class="kt-form-card-sub">
                        Provide your information below and we'll get back to you as soon as possible.
                    </p>

                    <?php if ($messageSent): ?>
                        <div class="kt-alert-success">
                            <?php echo render_icon('CheckCircle2', 18); ?>
                            <span><?php echo htmlspecialchars($messageSent); ?></span>
                        </div>
                    <?php endif; ?>

                    <?php if ($errorMessage): ?>
                        <div class="kt-alert-error">
                            <?php echo render_icon('TriangleAlert', 18); ?>
                            <span><?php echo htmlspecialchars($errorMessage); ?></span>
                        </div>
                    <?php endif; ?>

                    <form method="post" action="" class="kt-modern-form">
                        <?php echo csrf_input(); ?>

                        <!-- Row 1: Full Name & Email Address -->
                        <div class="kt-form-row-2">
                            <div class="kt-form-group">
                                <label for="full_name" class="kt-form-label">
                                    <?php echo render_icon('UserRound', 15); ?> Full Name <span class="required">*</span>
                                </label>
                                <input id="full_name" name="full_name" type="text" class="kt-form-control" placeholder="Enter your full name" value="<?php echo htmlspecialchars($_POST['full_name'] ?? $user['full_name'] ?? ''); ?>" required>
                            </div>

                            <div class="kt-form-group">
                                <label for="email" class="kt-form-label">
                                    <?php echo render_icon('Mail', 15); ?> Email Address <span class="required">*</span>
                                </label>
                                <input id="email" name="email" type="email" class="kt-form-control" placeholder="Enter your email address" value="<?php echo htmlspecialchars($_POST['email'] ?? $user['email'] ?? ''); ?>" required>
                            </div>
                        </div>

                        <!-- Row 2: Phone Number & Service Required -->
                        <div class="kt-form-row-2">
                            <div class="kt-form-group">
                                <label for="phone" class="kt-form-label">
                                    <?php echo render_icon('Phone', 15); ?> Phone Number <span class="required">*</span>
                                </label>
                                <input id="phone" name="phone" type="tel" class="kt-form-control" placeholder="e.g. +234 801 234 5678" value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>" required>
                            </div>

                            <div class="kt-form-group">
                                <label for="appointment_type" class="kt-form-label">
                                    <?php echo render_icon('Settings', 15); ?> Service Required <span class="required">*</span>
                                </label>
                                <div class="kt-select-wrap">
                                    <select id="appointment_type" name="appointment_type" class="kt-form-control kt-select-input" required>
                                        <option value="" disabled <?php echo empty($_POST['appointment_type']) ? 'selected' : ''; ?>>Select a service</option>
                                        <option value="Custom Software Development" <?php echo (($_POST['appointment_type'] ?? '') === 'Custom Software Development') ? 'selected' : ''; ?>>Custom Software Development</option>
                                        <option value="Web Development" <?php echo (($_POST['appointment_type'] ?? '') === 'Web Development') ? 'selected' : ''; ?>>Web Development</option>
                                        <option value="Mobile App Development" <?php echo (($_POST['appointment_type'] ?? '') === 'Mobile App Development') ? 'selected' : ''; ?>>Mobile App Development</option>
                                        <option value="AI Solutions" <?php echo (($_POST['appointment_type'] ?? '') === 'AI Solutions') ? 'selected' : ''; ?>>AI Solutions</option>
                                        <option value="Cloud Solutions" <?php echo (($_POST['appointment_type'] ?? '') === 'Cloud Solutions') ? 'selected' : ''; ?>>Cloud Solutions</option>
                                        <option value="IT Consulting & Support" <?php echo (($_POST['appointment_type'] ?? '') === 'IT Consulting & Support') ? 'selected' : ''; ?>>IT Consulting & Support</option>
                                        <option value="Database Management" <?php echo (($_POST['appointment_type'] ?? '') === 'Database Management') ? 'selected' : ''; ?>>Database Management</option>
                                        <option value="System Integration" <?php echo (($_POST['appointment_type'] ?? '') === 'System Integration') ? 'selected' : ''; ?>>System Integration</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Row 3: Preferred Date & Preferred Time -->
                        <div class="kt-form-row-2">
                            <div class="kt-form-group">
                                <label for="preferred_date" class="kt-form-label">
                                    <?php echo render_icon('Calendar', 15); ?> Preferred Date <span class="required">*</span>
                                </label>
                                <input id="preferred_date" name="preferred_date" type="date" class="kt-form-control" value="<?php echo htmlspecialchars($_POST['preferred_date'] ?? date('Y-m-d', strtotime('+1 day'))); ?>" required>
                            </div>

                            <div class="kt-form-group">
                                <label for="preferred_time" class="kt-form-label">
                                    <?php echo render_icon('Clock', 15); ?> Preferred Time <span class="required">*</span>
                                </label>
                                <div class="kt-select-wrap">
                                    <select id="preferred_time" name="preferred_time" class="kt-form-control kt-select-input" required>
                                        <option value="" disabled <?php echo empty($_POST['preferred_time']) ? 'selected' : ''; ?>>Select time</option>
                                        <option value="09:00 AM - 10:00 AM" <?php echo (($_POST['preferred_time'] ?? '') === '09:00 AM - 10:00 AM') ? 'selected' : ''; ?>>09:00 AM - 10:00 AM</option>
                                        <option value="10:00 AM - 11:00 AM" <?php echo (($_POST['preferred_time'] ?? '') === '10:00 AM - 11:00 AM') ? 'selected' : ''; ?>>10:00 AM - 11:00 AM</option>
                                        <option value="11:00 AM - 12:00 PM" <?php echo (($_POST['preferred_time'] ?? '') === '11:00 AM - 12:00 PM') ? 'selected' : ''; ?>>11:00 AM - 12:00 PM</option>
                                        <option value="01:00 PM - 02:00 PM" <?php echo (($_POST['preferred_time'] ?? '') === '01:00 PM - 02:00 PM') ? 'selected' : ''; ?>>01:00 PM - 02:00 PM</option>
                                        <option value="02:00 PM - 03:00 PM" <?php echo (($_POST['preferred_time'] ?? '') === '02:00 PM - 03:00 PM') ? 'selected' : ''; ?>>02:00 PM - 03:00 PM</option>
                                        <option value="03:00 PM - 04:00 PM" <?php echo (($_POST['preferred_time'] ?? '') === '03:00 PM - 04:00 PM') ? 'selected' : ''; ?>>03:00 PM - 04:00 PM</option>
                                        <option value="04:00 PM - 05:00 PM" <?php echo (($_POST['preferred_time'] ?? '') === '04:00 PM - 05:00 PM') ? 'selected' : ''; ?>>04:00 PM - 05:00 PM</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Row 4: Additional Message -->
                        <div class="kt-form-group">
                            <label for="message" class="kt-form-label">
                                <?php echo render_icon('MessageSquareText', 15); ?> Additional Message (Optional)
                            </label>
                            <textarea id="message" name="message" rows="4" class="kt-form-control" placeholder="Tell us more about your project or requirements..."><?php echo htmlspecialchars($_POST['message'] ?? ''); ?></textarea>
                        </div>

                        <!-- Form Footer Actions -->
                        <div class="kt-form-footer">
                            <button type="submit" class="kt-btn-green" style="background-color: #0B5D3B !important; background-image: none !important;">
                                <?php echo render_icon('Calendar', 16); ?> Book Appointment <?php echo render_icon('ArrowRight', 16); ?>
                            </button>
                            <div class="kt-form-safety-note">
                                <?php echo render_icon('Lock', 14); ?>
                                <span>Your information is safe with us.</span>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Right: 2 Sidebar Cards -->
                <div class="kt-consult-sidebar">
                    
                    <!-- Card 1: Need Immediate Assistance? (Emerald Gradient) -->
                    <div class="kt-sidebar-immediate-card">
                        <h3 class="kt-immediate-title">Need Immediate Assistance?</h3>
                        <p class="kt-immediate-sub">
                            You can also reach us directly through our contact details below.
                        </p>
                        <div class="kt-immediate-list">
                            <div class="kt-immediate-item">
                                <div class="kt-immediate-icon-circle">
                                    <?php echo render_icon('Phone', 18); ?>
                                </div>
                                <div class="kt-immediate-text">
                                    <strong class="item-title">+234 803 123 4567</strong>
                                    <span class="item-desc">Call us anytime</span>
                                </div>
                            </div>

                            <div class="kt-immediate-item">
                                <div class="kt-immediate-icon-circle">
                                    <?php echo render_icon('Mail', 18); ?>
                                </div>
                                <div class="kt-immediate-text">
                                    <strong class="item-title">info@kendattech.com</strong>
                                    <span class="item-desc">We'll respond within 24 hours</span>
                                </div>
                            </div>

                            <div class="kt-immediate-item">
                                <div class="kt-immediate-icon-circle">
                                    <?php echo render_icon('MapPin', 18); ?>
                                </div>
                                <div class="kt-immediate-text">
                                    <strong class="item-title">Lagos, Nigeria</strong>
                                    <span class="item-desc">Our office location</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Card 2: Why Book With Us? (White Card with Green Checkmarks) -->
                    <div class="kt-sidebar-why-card">
                        <div class="kt-why-card-head">
                            <div class="kt-why-head-icon">
                                <?php echo render_icon('Headset', 20); ?>
                            </div>
                            <h3 class="kt-why-card-title">Why Book With Us?</h3>
                        </div>
                        <ul class="kt-why-check-list">
                            <li>
                                <span class="check-icon"><?php echo render_icon('Check', 14); ?></span>
                                <span>Free initial consultation</span>
                            </li>
                            <li>
                                <span class="check-icon"><?php echo render_icon('Check', 14); ?></span>
                                <span>Expert technical guidance</span>
                            </li>
                            <li>
                                <span class="check-icon"><?php echo render_icon('Check', 14); ?></span>
                                <span>Tailored solutions for your needs</span>
                            </li>
                            <li>
                                <span class="check-icon"><?php echo render_icon('Check', 14); ?></span>
                                <span>No obligation</span>
                            </li>
                        </ul>
                    </div>

                </div>

            </div>
        </div>
    </section>

    <!-- 3. HOW IT WORKS - SIMPLE STEPS TO GET STARTED (4 Horizontal Steps with Arrows) -->
    <section class="kt-consult-steps-section">
        <div class="kt-container">
            <div class="kt-section-header-center">
                <div class="kt-eyebrow-flanked">
                    <span class="flank-dash">—</span>
                    <span class="flank-text">HOW IT WORKS</span>
                    <span class="flank-dash">—</span>
                </div>
                <h2 class="kt-section-title">
                    Simple Steps to <span class="kt-text-brand">Get Started</span>
                </h2>
                <p class="kt-section-sub">
                    Booking an appointment with KendatTech is quick and easy. Follow these simple steps and we'll take care of the rest.
                </p>
            </div>

            <div class="kt-steps-flow-wrap">
                <!-- Step 01 -->
                <div class="kt-step-flow-item">
                    <div class="kt-step-icon-circle">
                        <?php echo render_icon('Calendar', 20); ?>
                        <span class="step-num-pill">01</span>
                    </div>
                    <h4 class="kt-step-flow-title">Book Appointment</h4>
                    <p class="kt-step-flow-desc">Fill out the form with your details and preferred time.</p>
                </div>

                <div class="kt-step-connector-arrow">
                    <?php echo render_icon('ArrowRight', 18); ?>
                </div>

                <!-- Step 02 -->
                <div class="kt-step-flow-item">
                    <div class="kt-step-icon-circle">
                        <?php echo render_icon('MessageSquareText', 20); ?>
                        <span class="step-num-pill">02</span>
                    </div>
                    <h4 class="kt-step-flow-title">Get Confirmation</h4>
                    <p class="kt-step-flow-desc">You'll receive a confirmation via email or phone.</p>
                </div>

                <div class="kt-step-connector-arrow">
                    <?php echo render_icon('ArrowRight', 18); ?>
                </div>

                <!-- Step 03 -->
                <div class="kt-step-flow-item">
                    <div class="kt-step-icon-circle">
                        <?php echo render_icon('Users', 20); ?>
                        <span class="step-num-pill">03</span>
                    </div>
                    <h4 class="kt-step-flow-title">Meet Our Team</h4>
                    <p class="kt-step-flow-desc">Discuss your needs and explore the best solutions.</p>
                </div>

                <div class="kt-step-connector-arrow">
                    <?php echo render_icon('ArrowRight', 18); ?>
                </div>

                <!-- Step 04 -->
                <div class="kt-step-flow-item">
                    <div class="kt-step-icon-circle">
                        <?php echo render_icon('Rocket', 20); ?>
                        <span class="step-num-pill">04</span>
                    </div>
                    <h4 class="kt-step-flow-title">Start Your Project</h4>
                    <p class="kt-step-flow-desc">We turn your ideas into reality.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- 4. CTA BANNER (Let's Build Your Future Together) -->
    <section class="kt-consult-cta-section">
        <div class="kt-container">
            <div class="kt-consult-cta-card">
                <div class="kt-consult-cta-left">
                    <div class="kt-consult-cta-icon-circle">
                        <?php echo render_icon('Lightbulb', 28); ?>
                    </div>
                    <div class="kt-consult-cta-text">
                        <span class="kt-consult-cta-eyebrow">READY TO GET STARTED?</span>
                        <h2 class="kt-consult-cta-heading">Let's Build Your Future Together</h2>
                        <p class="kt-consult-cta-sub">Schedule your appointment today and take the first step towards your digital transformation.</p>
                    </div>
                </div>
                <div class="kt-consult-cta-right">
                    <a href="#appointment-form" class="kt-cta-btn-white">
                        Book Appointment <?php echo render_icon('ArrowRight', 16); ?>
                    </a>
                </div>
            </div>
        </div>
    </section>

</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
