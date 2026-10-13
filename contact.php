<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/csrf.php';

require_csrf_token();

$messageSent = '';
$errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $fullName = trim($_POST['full_name'] ?? '');
        $email = strtolower(trim($_POST['email'] ?? ''));
        $phone = trim($_POST['phone'] ?? '');
        $subject = trim($_POST['subject'] ?? '');
        $messageText = trim($_POST['message'] ?? '');

        if (empty($fullName) || empty($email) || empty($messageText)) {
            throw new Exception("Please enter your Full Name, Email, and Message.");
        }

        $pdo = get_db();
        $stmt = $pdo->prepare("INSERT INTO contact_messages (full_name, email, phone, subject, message) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$fullName, $email, $phone, $subject, $messageText]);

        $messageSent = "Your message has been sent successfully! Our team will contact you shortly.";
    } catch (Throwable $e) {
        $errorMessage = $e->getMessage();
    }
}
?>

<main class="kt-main-wrapper">

    <!-- 1. HERO SECTION (Contact Us - We're Here to Help) -->
    <section class="kt-contact-hero">
        <div class="kt-container">
            <div class="kt-contact-hero-grid">
                <!-- Left Content -->
                <div class="kt-contact-hero-content">
                    <div class="kt-contact-eyebrow">
                        <span>GET IN TOUCH</span>
                        <span class="eyebrow-line"></span>
                    </div>
                    <h1 class="kt-contact-hero-title">
                        Contact Us<br>
                        We're Here to <span class="kt-text-brand">Help</span>
                    </h1>
                    <p class="kt-contact-hero-desc">
                        Have a question, want to discuss a project, or need support? Our team is ready to assist you. Reach out to us through any of the channels below, and we'll get back to you as soon as possible.
                    </p>

                    <!-- 3 Hero Feature Badges with Subtitles -->
                    <div class="kt-contact-hero-badges">
                        <div class="kt-contact-badge-item">
                            <span class="badge-icon-wrap"><?php echo render_icon('Clock', 18); ?></span>
                            <div class="badge-text-wrap">
                                <strong class="badge-title">Quick Response</strong>
                                <span class="badge-sub">Within 24 hours</span>
                            </div>
                        </div>

                        <div class="kt-contact-badge-item">
                            <span class="badge-icon-wrap"><?php echo render_icon('Headset', 18); ?></span>
                            <div class="badge-text-wrap">
                                <strong class="badge-title">Expert Support</strong>
                                <span class="badge-sub">Professional team</span>
                            </div>
                        </div>

                        <div class="kt-contact-badge-item">
                            <span class="badge-icon-wrap"><?php echo render_icon('Award', 18); ?></span>
                            <div class="badge-text-wrap">
                                <strong class="badge-title">Your Success</strong>
                                <span class="badge-sub">Our priority</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Visual (Desk Mockup with KendatTech Laptop & Green Mug) -->
                <div class="kt-contact-hero-visual">
                    <img src="<?php echo $baseUrl; ?>assets/images/consultation-hero-visual.png" alt="KendatTech Contact - We're Here to Help" class="kt-contact-hero-img">
                </div>
            </div>
        </div>
    </section>

    <!-- 2. FORM & CONTACT INFO SECTION (Get In Touch & Channels) -->
    <section class="kt-contact-main-section" id="contact-form">
        <div class="kt-container">
            <div class="kt-contact-layout-grid">
                
                <!-- Left: Send Message Form Card -->
                <div class="kt-contact-form-card">
                    <div class="kt-eyebrow-flanked text-start" style="margin-bottom: 8px;">
                        <span class="flank-text" style="color: #059669; font-size: 0.76rem; font-weight: 700; letter-spacing: 0.08em;">SEND US A MESSAGE</span>
                        <span class="flank-dash" style="color: #059669;">—</span>
                    </div>
                    <h2 class="kt-form-card-title">Get In Touch</h2>
                    <p class="kt-form-card-sub">
                        Fill out the form below and we'll get back to you as soon as possible.
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
                                <label for="contact_name" class="kt-form-label">
                                    <?php echo render_icon('UserRound', 15); ?> Full Name <span class="required">*</span>
                                </label>
                                <input id="contact_name" name="full_name" type="text" class="kt-form-control" placeholder="Enter your full name" value="<?php echo htmlspecialchars($_POST['full_name'] ?? ''); ?>" required>
                            </div>

                            <div class="kt-form-group">
                                <label for="contact_email" class="kt-form-label">
                                    <?php echo render_icon('Mail', 15); ?> Email Address <span class="required">*</span>
                                </label>
                                <input id="contact_email" name="email" type="email" class="kt-form-control" placeholder="Enter your email address" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
                            </div>
                        </div>

                        <!-- Row 2: Phone Number & Subject -->
                        <div class="kt-form-row-2">
                            <div class="kt-form-group">
                                <label for="contact_phone" class="kt-form-label">
                                    <?php echo render_icon('Phone', 15); ?> Phone Number <span class="required">*</span>
                                </label>
                                <input id="contact_phone" name="phone" type="tel" class="kt-form-control" placeholder="+234 801 234 5678" value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>">
                            </div>

                            <div class="kt-form-group">
                                <label for="contact_subject" class="kt-form-label">
                                    <?php echo render_icon('Settings', 15); ?> Subject <span class="required">*</span>
                                </label>
                                <div class="kt-select-wrap">
                                    <select id="contact_subject" name="subject" class="kt-form-control kt-select-input" required>
                                        <option value="" disabled <?php echo empty($_POST['subject']) ? 'selected' : ''; ?>>Select a subject</option>
                                        <option value="General Inquiry" <?php echo (($_POST['subject'] ?? '') === 'General Inquiry') ? 'selected' : ''; ?>>General Inquiry</option>
                                        <option value="Custom Software Development" <?php echo (($_POST['subject'] ?? '') === 'Custom Software Development') ? 'selected' : ''; ?>>Custom Software Development</option>
                                        <option value="Web & Mobile Application" <?php echo (($_POST['subject'] ?? '') === 'Web & Mobile Application') ? 'selected' : ''; ?>>Web & Mobile Application</option>
                                        <option value="AI Solutions & Machine Learning" <?php echo (($_POST['subject'] ?? '') === 'AI Solutions & Machine Learning') ? 'selected' : ''; ?>>AI Solutions & Machine Learning</option>
                                        <option value="Cloud Infrastructure & DevOps" <?php echo (($_POST['subject'] ?? '') === 'Cloud Infrastructure & DevOps') ? 'selected' : ''; ?>>Cloud Infrastructure & DevOps</option>
                                        <option value="Technical Support & Maintenance" <?php echo (($_POST['subject'] ?? '') === 'Technical Support & Maintenance') ? 'selected' : ''; ?>>Technical Support & Maintenance</option>
                                        <option value="Partnership / Enterprise Contract" <?php echo (($_POST['subject'] ?? '') === 'Partnership / Enterprise Contract') ? 'selected' : ''; ?>>Partnership / Enterprise Contract</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Row 3: Message -->
                        <div class="kt-form-group">
                            <label for="contact_message" class="kt-form-label">
                                <?php echo render_icon('MessageSquareText', 15); ?> Message <span class="required">*</span>
                            </label>
                            <textarea id="contact_message" name="message" rows="5" class="kt-form-control" placeholder="Tell us how we can help you..." required><?php echo htmlspecialchars($_POST['message'] ?? ''); ?></textarea>
                        </div>

                        <!-- Form Footer Actions -->
                        <div class="kt-form-footer">
                            <button type="submit" class="kt-btn-green" style="background-color: #0B5D3B !important; background-image: none !important;">
                                <?php echo render_icon('Send', 16); ?> Send Message <?php echo render_icon('ArrowRight', 16); ?>
                            </button>
                            <div class="kt-form-safety-note">
                                <?php echo render_icon('Lock', 14); ?>
                                <span>Your information is safe with us.</span>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Right: Our Contact Information Card -->
                <div class="kt-contact-info-card">
                    <h3 class="kt-contact-info-title">Our Contact Information</h3>
                    <p class="kt-contact-info-sub">
                        You can reach us through any of the following channels. We're always happy to hear from you.
                    </p>

                    <div class="kt-contact-channel-list">
                        <!-- Phone -->
                        <div class="kt-channel-item">
                            <div class="kt-channel-icon-circle">
                                <?php echo render_icon('Phone', 18); ?>
                            </div>
                            <div class="kt-channel-text">
                                <span class="channel-label">Phone</span>
                                <strong class="channel-value">+234 803 123 4567</strong>
                                <span class="channel-hint">Mon - Fri: 8:00 AM - 5:00 PM</span>
                            </div>
                        </div>

                        <!-- Email -->
                        <div class="kt-channel-item">
                            <div class="kt-channel-icon-circle">
                                <?php echo render_icon('Mail', 18); ?>
                            </div>
                            <div class="kt-channel-text">
                                <span class="channel-label">Email</span>
                                <strong class="channel-value">info@kendattech.com</strong>
                                <span class="channel-hint">We'll respond within 24 hours</span>
                            </div>
                        </div>

                        <!-- Office Address -->
                        <div class="kt-channel-item">
                            <div class="kt-channel-icon-circle">
                                <?php echo render_icon('MapPin', 18); ?>
                            </div>
                            <div class="kt-channel-text">
                                <span class="channel-label">Office Address</span>
                                <strong class="channel-value">Makurdi, Benue State, Nigeria</strong>
                                <span class="channel-hint">Our headquarters & tech hub</span>
                            </div>
                        </div>

                        <!-- Follow Us Socials -->
                        <div class="kt-channel-social-item">
                            <div class="kt-channel-icon-circle">
                                <?php echo render_icon('Network', 18); ?>
                            </div>
                            <div class="kt-channel-text">
                                <span class="channel-label">Follow Us</span>
                                <div class="kt-contact-social-icons">
                                    <a href="#" aria-label="LinkedIn" class="kt-social-icon-btn"><?php echo render_icon('Linkedin', 16); ?></a>
                                    <a href="#" aria-label="X (Twitter)" class="kt-social-icon-btn"><?php echo render_icon('XTwitter', 16); ?></a>
                                    <a href="#" aria-label="Instagram" class="kt-social-icon-btn"><?php echo render_icon('Instagram', 16); ?></a>
                                    <a href="#" aria-label="YouTube" class="kt-social-icon-btn"><?php echo render_icon('Youtube', 16); ?></a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- 3. MAP & WHY WORK WITH US SECTION (Side-by-side cards) -->
    <section class="kt-contact-map-why-section">
        <div class="kt-container">
            <div class="kt-contact-map-why-grid">
                
                <!-- Left: Interactive Map Card with Embed Iframe -->
                <div class="kt-contact-map-card">
                    <div class="kt-map-wrapper">
                        <iframe
                            title="Kendat office location map"
                            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3953.659073130574!2d8.53492647476523!3d7.719680092298334!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x105081c80f0bac79%3A0xb52537c1831b17d9!2sKatsina%20Ala%20Street%2C%20Wurukum%2C%20Markurdi%20970101%2C%20Benue!5e0!3m2!1sen!2sng!4v1780922412575!5m2!1sen!2sng"
                            width="100%"
                            height="100%"
                            style="border:0; min-height: 400px; width: 100%; display: block;"
                            allowfullscreen=""
                            loading="lazy"
                            referrerpolicy="no-referrer-when-downgrade">
                        </iframe>
                        <div class="kt-map-floating-overlay">
                            <div class="overlay-left">
                                <span class="map-pin-icon"><?php echo render_icon('MapPin', 18); ?></span>
                                <div class="overlay-text">
                                    <strong class="overlay-title">Headquarters</strong>
                                    <span class="overlay-sub">Makurdi, Benue State, Nigeria</span>
                                </div>
                            </div>
                            <a href="https://maps.google.com/?q=Katsina+Ala+Street,+Wurukum,+Makurdi" target="_blank" rel="noopener noreferrer" class="kt-map-cta-btn">
                                Open in Google Maps <?php echo render_icon('ArrowRight', 14); ?>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Right: Why Work With Us? Card (4 Features) -->
                <div class="kt-contact-why-card">
                    <h3 class="kt-why-card-title">Why Work With Us?</h3>
                    <div class="kt-contact-why-items">
                        
                        <!-- Feature 1 -->
                        <div class="kt-contact-why-item">
                            <div class="kt-why-item-icon">
                                <?php echo render_icon('Lightbulb', 18); ?>
                            </div>
                            <div class="kt-why-item-content">
                                <h4 class="why-item-title">Innovative Solutions</h4>
                                <p class="why-item-desc">We bring fresh ideas and modern technology to every project.</p>
                            </div>
                        </div>

                        <!-- Feature 2 -->
                        <div class="kt-contact-why-item">
                            <div class="kt-why-item-icon">
                                <?php echo render_icon('ShieldCheck', 18); ?>
                            </div>
                            <div class="kt-why-item-content">
                                <h4 class="why-item-title">Reliable & Secure</h4>
                                <p class="why-item-desc">Your data and privacy are our top priority.</p>
                            </div>
                        </div>

                        <!-- Feature 3 -->
                        <div class="kt-contact-why-item">
                            <div class="kt-why-item-icon">
                                <?php echo render_icon('Users', 18); ?>
                            </div>
                            <div class="kt-why-item-content">
                                <h4 class="why-item-title">Experienced Team</h4>
                                <p class="why-item-desc">Skilled professionals with proven track records.</p>
                            </div>
                        </div>

                        <!-- Feature 4 -->
                        <div class="kt-contact-why-item">
                            <div class="kt-why-item-icon">
                                <?php echo render_icon('Scaling', 18); ?>
                            </div>
                            <div class="kt-why-item-content">
                                <h4 class="why-item-title">Client-Focused</h4>
                                <p class="why-item-desc">We work closely with you to achieve your goals.</p>
                            </div>
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- 4. CTA BANNER (Let's Build Something Amazing Together) -->
    <section class="kt-contact-cta-section">
        <div class="kt-container">
            <div class="kt-contact-cta-card">
                <div class="kt-contact-cta-left">
                    <div class="kt-contact-cta-icon-circle">
                        <?php echo render_icon('Send', 28); ?>
                    </div>
                    <div class="kt-contact-cta-text">
                        <span class="kt-contact-cta-eyebrow">READY TO START YOUR PROJECT?</span>
                        <h2 class="kt-contact-cta-heading">Let's Build Something Amazing Together</h2>
                        <p class="kt-contact-cta-sub">Contact us today and take the first step towards your digital transformation.</p>
                    </div>
                </div>
                <div class="kt-contact-cta-right">
                    <a href="#contact-form" class="kt-cta-btn-white">
                        Get in Touch <?php echo render_icon('ArrowRight', 16); ?>
                    </a>
                </div>
            </div>
        </div>
    </section>

</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
