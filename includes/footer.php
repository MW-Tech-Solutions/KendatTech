<?php
declare(strict_types=1);

$settings = get_settings();
$companyName = $settings['company_name'] ?? 'Kendat Integrated Services';
$slogan = $settings['website_slogan'] ?? 'Building Intelligent Digital Solutions for Businesses, Institutions, and the Future.';
$footerText = $settings['footer_text'] ?? '© ' . date('Y') . ' Kendat Integrated Services. All rights reserved.';
$contactEmail = $settings['contact_email'] ?? 'hello@kendatservices.com';
$contactPhone = $settings['contact_phone'] ?? '+234 800 000 0000';
$officeAddress = $settings['office_address'] ?? 'Lagos, Nigeria';
$officeHours = $settings['office_hours'] ?? 'Mon - Fri, 9:00 AM - 6:00 PM';
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
    <!-- 8. FOOTER SECTION (Dynamic Contact Data from System Settings) -->
    <footer class="kt-footer">
        <div class="kt-container">
            <div class="kt-footer-grid">
                <!-- Column 1: Brand & Socials -->
                <div class="kt-footer-col brand-col">
                    <a class="brand" href="<?php echo $baseUrl; ?>index.php" aria-label="<?php echo htmlspecialchars($companyName); ?>">
                        <?php if ($logoPath): ?>
                            <img src="<?php echo htmlspecialchars($logoPath); ?>" alt="<?php echo htmlspecialchars($companyName); ?>" class="brand-logo-img">
                        <?php else: ?>
                            <span class="brand-logo-flex">
                                <svg width="32" height="32" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg" class="brand-k-icon">
                                    <rect width="32" height="32" rx="8" fill="#ECFDF5"/>
                                    <path d="M9 7V25M9 16L18 7M9 16L18 25" stroke="#10B981" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M19 16L24 7M19 16L24 25" stroke="#34D399" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                                <span class="brand-logo-text white">Kendat<span class="brand-logo-accent">Tech</span></span>
                            </span>
                        <?php endif; ?>
                    </a>
                    <p class="kt-footer-slogan"><?php echo htmlspecialchars($slogan); ?></p>
                    <div class="kt-social-row">
                        <?php foreach ($socials as [$label, $href, $iconName]): ?>
                            <?php if (!empty($href) && $href !== '#'): ?>
                                <a href="<?php echo htmlspecialchars($href); ?>" target="_blank" rel="noopener noreferrer" aria-label="<?php echo htmlspecialchars($label); ?>"><?php echo render_icon($iconName, 16); ?></a>
                            <?php else: ?>
                                <a href="#" aria-label="<?php echo htmlspecialchars($label); ?>"><?php echo render_icon($iconName, 16); ?></a>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Column 2: Quick Links -->
                <div class="kt-footer-col links-col">
                    <h4 class="kt-footer-head">Quick Links</h4>
                    <ul class="kt-footer-links">
                        <li><a href="<?php echo $baseUrl; ?>index.php">Home</a></li>
                        <li><a href="<?php echo $baseUrl; ?>about.php">About</a></li>
                        <li><a href="<?php echo $baseUrl; ?>services.php">Services</a></li>
                        <li><a href="<?php echo $baseUrl; ?>projects.php">Products & Projects</a></li>
                        <li><a href="<?php echo $baseUrl; ?>ai-solutions.php">AI Solutions</a></li>
                        <li><a href="<?php echo $baseUrl; ?>contact.php">Contact</a></li>
                        <li><a href="<?php echo $baseUrl; ?>admin-login.php"><?php echo render_icon('Lock', 13); ?> Staff Login</a></li>
                    </ul>
                </div>

                <!-- Column 3: Our Services -->
                <div class="kt-footer-col services-col">
                    <h4 class="kt-footer-head">Our Services</h4>
                    <ul class="kt-footer-links">
                        <li><a href="<?php echo $baseUrl; ?>services.php">Web Development</a></li>
                        <li><a href="<?php echo $baseUrl; ?>services.php">Mobile App Development</a></li>
                        <li><a href="<?php echo $baseUrl; ?>services.php">Cloud Solutions</a></li>
                        <li><a href="<?php echo $baseUrl; ?>services.php">IT Consulting</a></li>
                        <li><a href="<?php echo $baseUrl; ?>services.php">Cybersecurity</a></li>
                        <li><a href="<?php echo $baseUrl; ?>services.php">Custom Software</a></li>
                    </ul>
                </div>

                <!-- Column 4: Contact Us -->
                <div class="kt-footer-col contact-col">
                    <h4 class="kt-footer-head">Contact Us</h4>
                    <ul class="kt-footer-contact">
                        <li><?php echo render_icon('MapPin', 16); ?> <span><?php echo htmlspecialchars($officeAddress); ?></span></li>
                        <li><?php echo render_icon('Mail', 16); ?> <a href="mailto:<?php echo htmlspecialchars($contactEmail); ?>" style="color:inherit;text-decoration:none;"><span><?php echo htmlspecialchars($contactEmail); ?></span></a></li>
                        <li><?php echo render_icon('Phone', 16); ?> <a href="tel:<?php echo htmlspecialchars(preg_replace('/[^0-9+]/', '', $contactPhone)); ?>" style="color:inherit;text-decoration:none;"><span><?php echo htmlspecialchars($contactPhone); ?></span></a></li>
                        <li><?php echo render_icon('Clock3', 16); ?> <span><?php echo htmlspecialchars($officeHours); ?></span></li>
                    </ul>
                </div>
            </div>

            <!-- Bottom Copyright Bar -->
            <div class="kt-footer-bottom">
                <p><?php echo htmlspecialchars($footerText); ?></p>
                <div class="kt-footer-bottom-links">
                    <a href="<?php echo $baseUrl; ?>privacy.php">Privacy Policy</a>
                    <a href="<?php echo $baseUrl; ?>terms.php">Terms of Service</a>
                    <a href="<?php echo $baseUrl; ?>contact.php">Support</a>
                    <a href="<?php echo $baseUrl; ?>admin-login.php" class="kt-staff-login-link"><?php echo render_icon('ShieldCheck', 13); ?> Staff Login</a>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?php echo $baseUrl; ?>assets/js/main.js?v=<?php echo file_exists(__DIR__ . '/../assets/js/main.js') ? filemtime(__DIR__ . '/../assets/js/main.js') : time(); ?>"></script>
</body>
</html>
