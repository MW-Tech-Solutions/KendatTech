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

<main>
    <!-- Sub-Page Hero Banner -->
    <section class="page-hero contact-hero">
        <div class="contact-hero-content">
            <span class="eyebrow">Contact Kendat</span>
            <h1>Let's build your next digital platform.</h1>
            <p>Reach out for custom web & software systems, AI automation, school management portals, mobile apps, enterprise dashboards, and cloud infrastructure.</p>
            <div class="contact-quick">
                <a class="contact-quick-btn" href="mailto:<?php echo htmlspecialchars($settings['contact_email'] ?? 'hello@kendatservices.com'); ?>">
                    <?php echo render_icon('Mail', 18); ?> Email Us
                </a>
                <a class="contact-quick-btn" href="tel:<?php echo htmlspecialchars($settings['contact_phone'] ?? '+234 800 000 0000'); ?>">
                    <?php echo render_icon('Phone', 18); ?> Direct Call
                </a>
                <a class="contact-quick-btn whatsapp" href="https://wa.me/<?php echo htmlspecialchars($settings['whatsapp_number'] ?? '2348000000000'); ?>" target="_blank" rel="noopener">
                    <?php echo render_icon('MessageCircle', 18); ?> WhatsApp Chat
                </a>
            </div>
        </div>
        <div class="contact-signal">
            <?php echo render_icon('Radar', 42); ?>
            <strong>Fast Response Channel</strong>
            <span>Direct engineering review, project discovery, support & consultations.</span>
        </div>
    </section>

    <!-- Contact Form & Stack Grid -->
    <section class="band contact-layout contact-page">
        <!-- Left: Premium Contact Form -->
        <form class="glass-card form premium-form contact-form-card" method="post" action="">
            <?php echo csrf_input(); ?>
            <div class="form-head">
                <span class="form-badge"><?php echo render_icon('Send', 15); ?>DIRECT ENQUIRY</span>
                <h2>Send Us a Message</h2>
                <p>Tell us about your project or requirement. Submissions are delivered straight to our admin team.</p>
            </div>

            <?php if ($messageSent): ?>
                <div class="success"><?php echo htmlspecialchars($messageSent); ?></div>
            <?php endif; ?>

            <?php if ($errorMessage): ?>
                <div class="error"><?php echo htmlspecialchars($errorMessage); ?></div>
            <?php endif; ?>

            <div class="contact-form-grid">
                <label>Full Name
                    <input name="full_name" type="text" placeholder="Emeka Okafor" value="<?php echo htmlspecialchars($_POST['full_name'] ?? ''); ?>" required>
                </label>

                <label>Email Address
                    <input name="email" type="email" placeholder="emeka.okafor@company.ng" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
                </label>
            </div>

            <div class="contact-form-grid">
                <label>Phone Number
                    <input name="phone" type="tel" placeholder="+234 800 000 0000" value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>">
                </label>

                <label>Subject / Requirement
                    <input name="subject" type="text" placeholder="e.g. School Management Portal" value="<?php echo htmlspecialchars($_POST['subject'] ?? ''); ?>" required>
                </label>
            </div>

            <label>Detailed Message
                <textarea name="message" rows="5" placeholder="Describe your software requirements, timeline, or questions..." required><?php echo htmlspecialchars($_POST['message'] ?? ''); ?></textarea>
            </label>

            <button class="tc-pill-btn-blue" type="submit" style="width: 100%; justify-content: center; margin-top: 10px;">
                Send Message <?php echo render_icon('Send', 18); ?>
            </button>
        </form>

        <!-- Right: Contact Information & Interactive Map -->
        <div class="contact-stack">
            <div class="glass-card contact-info-card">
                <h3>Contact Information</h3>
                <div class="contact-info-list">
                    <a class="contact-line" href="mailto:<?php echo htmlspecialchars($settings['contact_email'] ?? 'hello@kendatservices.com'); ?>">
                        <div class="contact-icon-box"><?php echo render_icon('Mail', 20); ?></div>
                        <div>
                            <small>EMAIL ADDRESS</small>
                            <strong><?php echo htmlspecialchars($settings['contact_email'] ?? 'hello@kendatservices.com'); ?></strong>
                        </div>
                    </a>

                    <a class="contact-line" href="tel:<?php echo htmlspecialchars($settings['contact_phone'] ?? '+234 800 000 0000'); ?>">
                        <div class="contact-icon-box"><?php echo render_icon('Phone', 20); ?></div>
                        <div>
                            <small>PHONE / MOBILE</small>
                            <strong><?php echo htmlspecialchars($settings['contact_phone'] ?? '+234 800 000 0000'); ?></strong>
                        </div>
                    </a>

                    <a class="contact-line" href="https://wa.me/<?php echo htmlspecialchars($settings['whatsapp_number'] ?? '2348000000000'); ?>" target="_blank" rel="noopener">
                        <div class="contact-icon-box whatsapp"><?php echo render_icon('MessageCircle', 20); ?></div>
                        <div>
                            <small>WHATSAPP BUSINESS</small>
                            <strong><?php echo htmlspecialchars($settings['whatsapp_number'] ?? '+234 800 000 0000'); ?></strong>
                        </div>
                    </a>

                    <div class="contact-line">
                        <div class="contact-icon-box"><?php echo render_icon('MapPin', 20); ?></div>
                        <div>
                            <small>HEAD OFFICE ADDRESS</small>
                            <strong><?php echo htmlspecialchars($settings['office_address'] ?? 'Makurdi, Benue State, Nigeria'); ?></strong>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Glass Location Map Container -->
            <div class="glass-card map-card">
                <div class="map-card-header">
                    <span class="status-dot-green"></span>
                    <span>HEADQUARTERS & TECH HUB</span>
                </div>
                <div class="map-frame">
                    <iframe
                        title="Kendat office location map"
                        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3953.659073130574!2d8.53492647476523!3d7.719680092298334!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x105081c80f0bac79%3A0xb52537c1831b17d9!2sKatsina%20Ala%20Street%2C%20Wurukum%2C%20Markurdi%20970101%2C%20Benue!5e0!3m2!1sen!2sng!4v1780922412575!5m2!1sen!2sng"
                        width="100%"
                        height="260"
                        style="border:0;"
                        allowfullscreen=""
                        loading="lazy"
                        referrerpolicy="no-referrer-when-downgrade">
                    </iframe>
                </div>
            </div>
        </div>
    </section>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
