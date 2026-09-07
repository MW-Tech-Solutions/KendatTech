<?php
declare(strict_types=1);

$settings = get_settings();
$companyName = $settings['company_name'] ?? 'Kendat Integrated Services';
$slogan = $settings['website_slogan'] ?? 'Building Intelligent Digital Solutions for Businesses, Institutions, and the Future.';
$footerText = $settings['footer_text'] ?? '© ' . date('Y') . ' Kendat Integrated Services. Intelligent software for modern organizations.';
$logoPath = upload_asset_url($settings['logo'] ?? '');
$baseUrl = get_base_url();

$servicesLinks = [
    ['Custom Web Applications', $baseUrl . 'services.php'],
    ['AI Copilots & Automation', $baseUrl . 'ai-solutions.php'],
    ['Enterprise Software Audit', $baseUrl . 'request-project.php'],
    ['Database & Cloud Systems', $baseUrl . 'services.php'],
    ['School Portal Development', $baseUrl . 'projects.php'],
];

$companyLinks = [
    ['About Kendat Tech', $baseUrl . 'about.php'],
    ['Featured Projects', $baseUrl . 'projects.php'],
    ['Book Consultation', $baseUrl . 'book-appointment.php'],
    ['Request Custom Project', $baseUrl . 'request-project.php'],
    ['Contact Engineering', $baseUrl . 'contact.php'],
];

$socials = [
    ['LinkedIn', $settings['linkedin_link'] ?? '#', 'Linkedin'],
    ['Twitter', $settings['twitter_link'] ?? '#', 'Twitter'],
    ['Facebook', $settings['facebook_link'] ?? '#', 'Facebook'],
    ['Instagram', $settings['instagram_link'] ?? '#', 'Instagram'],
    ['YouTube', $settings['youtube_link'] ?? '#', 'Youtube'],
];

$hasSocials = false;
foreach ($socials as [$label, $href]) {
    if (!empty($href) && $href !== '#') {
        $hasSocials = true;
        break;
    }
}
?>
    <!-- Redesigned Modern Enterprise Footer (Task 4 Fix) -->
    <footer class="footer-modern">
        <div class="footer-glow-bg"></div>
        <div class="footer-container">
            <div class="footer-grid">
                <!-- Column 1: Brand & Mission -->
                <div class="footer-col-brand">
                    <a class="brand" href="<?php echo $baseUrl; ?>" aria-label="<?php echo htmlspecialchars($companyName); ?>">
                        <span class="brand-mark-wrapper">
                            <?php if ($logoPath): ?>
                                <img src="<?php echo htmlspecialchars($logoPath); ?>" alt="<?php echo htmlspecialchars($companyName); ?>" class="brand-logo-img">
                            <?php else: ?>
                                <span class="brand-logo-text">kendat tech</span>
                            <?php endif; ?>
                        </span>
                    </a>
                    <p class="footer-mission-text">Kendat Integrated Services provides enterprise software engineering, web development, custom AI automation, and cloud IT infrastructure across Nigeria and globally.</p>
                    <div class="footer-status-pill">
                        <span class="status-dot-green"></span>
                        <span>Systems Ready for Consultation</span>
                    </div>
                </div>

                <!-- Column 2: Solutions -->
                <div class="footer-col-nav">
                    <h4 class="footer-head">SOFTWARE & AI</h4>
                    <ul class="footer-links-list">
                        <?php foreach ($servicesLinks as [$label, $href]): ?>
                            <li><a href="<?php echo htmlspecialchars($href); ?>"><?php echo htmlspecialchars($label); ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <!-- Column 3: Company -->
                <div class="footer-col-nav">
                    <h4 class="footer-head">COMPANY</h4>
                    <ul class="footer-links-list">
                        <?php foreach ($companyLinks as [$label, $href]): ?>
                            <li><a href="<?php echo htmlspecialchars($href); ?>"><?php echo htmlspecialchars($label); ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <!-- Column 4: Contact & Social -->
                <div class="footer-col-contact">
                    <h4 class="footer-head">GET IN TOUCH</h4>
                    <div class="footer-contact-items">
                        <a href="mailto:<?php echo htmlspecialchars($settings['contact_email'] ?? 'info@kisprojectslab.com'); ?>" class="footer-contact-link">
                            <?php echo render_icon('Mail', 16); ?>
                            <span><?php echo htmlspecialchars($settings['contact_email'] ?? 'info@kisprojectslab.com'); ?></span>
                        </a>
                        <a href="tel:<?php echo htmlspecialchars($settings['contact_phone'] ?? '+234800KENDATTECH'); ?>" class="footer-contact-link">
                            <?php echo render_icon('Phone', 16); ?>
                            <span><?php echo htmlspecialchars($settings['contact_phone'] ?? '+234 800 KENDAT TECH'); ?></span>
                        </a>
                        <a href="https://wa.me/<?php echo htmlspecialchars($settings['whatsapp_number'] ?? '2348000000000'); ?>" target="_blank" rel="noopener" class="footer-contact-link">
                            <?php echo render_icon('MessageCircle', 16); ?>
                            <span>WhatsApp Support</span>
                        </a>
                    </div>
                    <div class="footer-social-pills">
                        <?php foreach ($socials as [$label, $href, $icon]): ?>
                            <?php if (!empty($href) && $href !== '#'): ?>
                                <a class="footer-social-btn" href="<?php echo htmlspecialchars($href); ?>" aria-label="<?php echo htmlspecialchars($label); ?>" target="_blank" rel="noopener">
                                    <?php echo render_icon($icon, 16); ?>
                                </a>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Footer Bottom Bar -->
            <div class="footer-bottom-bar">
                <p class="footer-copyright"><?php echo htmlspecialchars($footerText); ?></p>
                <div class="footer-bottom-links">
                    <a href="<?php echo $baseUrl; ?>privacy.php">Privacy Policy</a>
                    <span class="sep">•</span>
                    <a href="<?php echo $baseUrl; ?>terms.php">Terms of Service</a>
                    <span class="sep">•</span>
                    <a href="https://kisprojectslab.com" target="_blank" rel="noopener">kisprojectslab.com</a>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?php echo $baseUrl; ?>assets/js/main.js"></script>
</body>
</html>
