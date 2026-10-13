<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/header.php';
$settings = get_settings();
$contactEmail = $settings['contact_email'] ?? 'privacy@kendattech.com';
$contactPhone = $settings['contact_phone'] ?? '+234 800 000 0000';
$officeAddress = $settings['office_address'] ?? 'Lagos, Nigeria';
?>

<main>
    <!-- Privacy Policy Page Hero -->
    <section class="kt-subpage-hero">
        <div class="kt-container text-center">
            <span class="kt-subpage-pill-badge">DATA PROTECTION & COMPLIANCE</span>
            <h1 class="kt-subpage-hero-title">Privacy Policy</h1>
            <p class="kt-subpage-hero-sub">Your privacy and data security are fundamental to our engineering operations. Learn how Kendat Integrated Services collects, uses, and safeguards your information.</p>
            <div class="kt-subpage-accent-dot"></div>
        </div>
    </section>

    <!-- Privacy Policy Main Content -->
    <section class="legal-section-wrap" style="padding: 40px 0 80px;">
        <div class="container">
            <div class="legal-layout-grid" style="display: grid; grid-template-columns: 280px 1fr; gap: 40px; align-items: start;">
                
                <!-- Quick Navigation Sidebar -->
                <aside class="legal-sidebar glass-card" style="padding: 24px; border-radius: 20px; position: sticky; top: 100px;">
                    <h4 style="font-size: 14px; text-transform: uppercase; letter-spacing: 0.05em; color: var(--muted); margin-bottom: 16px; display: flex; align-items: center; gap: 8px;">
                        <?php echo render_icon('ListFilter', 16); ?> Table of Contents
                    </h4>
                    <nav class="legal-nav" style="display: flex; flex-direction: column; gap: 10px; font-size: 14px;">
                        <a href="#overview" style="color: var(--text-dark); font-weight: 600; text-decoration: none;">1. Overview & Scope</a>
                        <a href="#information-collected" style="color: var(--muted); text-decoration: none;">2. Information We Collect</a>
                        <a href="#how-we-use-data" style="color: var(--muted); text-decoration: none;">3. How We Use Information</a>
                        <a href="#data-security" style="color: var(--muted); text-decoration: none;">4. Security & Encryption</a>
                        <a href="#data-sharing" style="color: var(--muted); text-decoration: none;">5. Data Sharing & Third Parties</a>
                        <a href="#client-rights" style="color: var(--muted); text-decoration: none;">6. Your Data Rights</a>
                        <a href="#cookies" style="color: var(--muted); text-decoration: none;">7. Cookies & Tracking</a>
                        <a href="#contact-dpo" style="color: var(--muted); text-decoration: none;">8. Contact Data Officer</a>
                    </nav>
                </aside>

                <!-- Policy Document Content -->
                <div class="legal-body-content">
                    
                    <article id="overview" class="kt-legal-card">
                        <div class="kt-legal-card-header">
                            <div class="kt-legal-icon-box"><?php echo render_icon('ShieldCheck', 20); ?></div>
                            <h2>1. Overview & Scope</h2>
                        </div>
                        <div class="kt-legal-card-body">
                            <p>At <strong>Kendat Integrated Services ("Kendat Tech", "We", "Us", or "Our")</strong>, we are committed to maintaining the trust and confidence of our clients, website visitors, and enterprise partners. This Privacy Policy outlines how we handle personal data collected across our web portals, custom software applications, AI copilot systems, and IT consulting services.</p>
                            <p style="margin-top: 12px;">By accessing our website or engaging our software engineering services, you agree to the collection and use of information in accordance with this policy and applicable data protection regulations including the Nigeria Data Protection Regulation (NDPR) and international standards.</p>
                        </div>
                    </article>

                    <article id="information-collected" class="kt-legal-card">
                        <div class="kt-legal-card-header">
                            <div class="kt-legal-icon-box"><?php echo render_icon('Bot', 20); ?></div>
                            <h2>2. Information We Collect</h2>
                        </div>
                        <div class="kt-legal-card-body">
                            <p>We collect information to provide high-quality enterprise software, process consultation appointments, and execute custom client projects effectively:</p>
                            <ul style="padding-left: 20px; margin-top: 12px; margin-bottom: 0;">
                                <li style="margin-bottom: 8px;"><strong>Account Information:</strong> Full name, corporate email address, telephone number, organization name, and password hashes created during client registration.</li>
                                <li style="margin-bottom: 8px;"><strong>Project & Appointment Data:</strong> Project specifications, functional requirements, appointment scheduling preferences, and uploaded document attachments.</li>
                                <li><strong>System & Technical Logs:</strong> IP address, browser type, device information, operating system, and access timestamps collected automatically for rate limiting and security auditing.</li>
                            </ul>
                        </div>
                    </article>

                    <article id="how-we-use-data" class="kt-legal-card">
                        <div class="kt-legal-card-header">
                            <div class="kt-legal-icon-box"><?php echo render_icon('BrainCircuit', 20); ?></div>
                            <h2>3. How We Use Information</h2>
                        </div>
                        <div class="kt-legal-card-body">
                            <p>Your information is used strictly for legitimate business and engineering operations, including:</p>
                            <div class="legal-feature-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-top: 16px;">
                                <div style="background: #F8FAFC; padding: 18px; border-radius: 12px; border: 1px solid #E2E8F0;">
                                    <strong style="color: #0F172A; display: block; margin-bottom: 4px; font-size: 14px;">Service Delivery</strong>
                                    <span style="font-size: 13px; color: #475569;">Executing custom software projects, portals, and AI integrations.</span>
                                </div>
                                <div style="background: #F8FAFC; padding: 18px; border-radius: 12px; border: 1px solid #E2E8F0;">
                                    <strong style="color: #0F172A; display: block; margin-bottom: 4px; font-size: 14px;">Client Support</strong>
                                    <span style="font-size: 13px; color: #475569;">Responding to project inquiries, support tickets, and appointment bookings.</span>
                                </div>
                                <div style="background: #F8FAFC; padding: 18px; border-radius: 12px; border: 1px solid #E2E8F0;">
                                    <strong style="color: #0F172A; display: block; margin-bottom: 4px; font-size: 14px;">Security & Auditing</strong>
                                    <span style="font-size: 13px; color: #475569;">Preventing unauthorized access, audit logging, and mitigating cyber threats.</span>
                                </div>
                            </div>
                        </div>
                    </article>

                    <article id="data-security" class="kt-legal-card">
                        <div class="kt-legal-card-header">
                            <div class="kt-legal-icon-box"><?php echo render_icon('ShieldCheck', 20); ?></div>
                            <h2>4. Security & Encryption</h2>
                        </div>
                        <div class="kt-legal-card-body">
                            <p>We employ enterprise-grade security controls to safeguard your data. All data transmissions are encrypted using <strong>TLS/SSL (256-bit encryption)</strong>. User authentication tokens and passwords are standard-hashed using strong one-way cryptographic algorithms (`password_hash` with BCrypt/Argon2).</p>
                            <p style="margin-top: 12px;">Our database architecture enforces strict access controls, rate limiting against brute-force attacks, and automatic CSRF validation on all form submissions.</p>
                        </div>
                    </article>

                    <article id="data-sharing" class="kt-legal-card">
                        <div class="kt-legal-card-header">
                            <div class="kt-legal-icon-box"><?php echo render_icon('Users', 20); ?></div>
                            <h2>5. Data Sharing & Third Parties</h2>
                        </div>
                        <div class="kt-legal-card-body">
                            <p><strong>We do NOT sell, rent, or trade client data to third-party advertisers.</strong> Data is shared only with trusted infrastructure providers (such as secure cloud hosting and email service providers) solely to deliver our services, or when required by law enforcement or regulatory authorities under due legal process.</p>
                        </div>
                    </article>

                    <article id="client-rights" class="kt-legal-card">
                        <div class="kt-legal-card-header">
                            <div class="kt-legal-icon-box"><?php echo render_icon('UserCheck', 20); ?></div>
                            <h2>6. Your Data Rights</h2>
                        </div>
                        <div class="kt-legal-card-body">
                            <p>You have the right to request access to the personal information we hold about you, request corrections to inaccurate data, or request the deletion of your account and associated project history, subject to statutory retention requirements.</p>
                        </div>
                    </article>

                    <article id="cookies" class="kt-legal-card">
                        <div class="kt-legal-card-header">
                            <div class="kt-legal-icon-box"><?php echo render_icon('Globe', 20); ?></div>
                            <h2>7. Cookies & Session Management</h2>
                        </div>
                        <div class="kt-legal-card-body">
                            <p>We use essential HTTP session cookies (`PHPSESSID`) required for secure login authentication, CSRF security verification, and user session management. We do not track users across external third-party websites.</p>
                        </div>
                    </article>

                    <article id="contact-dpo" class="kt-legal-card">
                        <div class="kt-legal-card-header">
                            <div class="kt-legal-icon-box"><?php echo render_icon('Mail', 20); ?></div>
                            <h2>8. Contact Our Data Protection Team</h2>
                        </div>
                        <div class="kt-legal-card-body">
                            <p>If you have questions regarding this Privacy Policy or wish to exercise your data protection rights, please contact our Data Officer:</p>
                            <div style="background: #F8FAFC; padding: 20px; border-radius: 12px; border: 1px solid #E2E8F0; margin-top: 14px;">
                                <strong style="color: #0F172A; display: block; font-size: 15px;">Kendat Integrated Services - Data Protection Office</strong>
                                <span style="font-size: 13.5px; color: #475569; display: block; margin-top: 4px;">Email: <a href="mailto:<?php echo htmlspecialchars($contactEmail); ?>" style="color: #0B5D3B; font-weight: 700; text-decoration: none;"><?php echo htmlspecialchars($contactEmail); ?></a></span>
                                <span style="font-size: 13.5px; color: #475569; display: block; margin-top: 2px;">Phone: <a href="tel:<?php echo htmlspecialchars($contactPhone); ?>" style="color: #475569; text-decoration: none; font-weight: 600;"><?php echo htmlspecialchars($contactPhone); ?></a></span>
                                <?php if ($officeAddress): ?>
                                    <span style="font-size: 13.5px; color: #475569; display: block; margin-top: 2px;">Office: <?php echo htmlspecialchars($officeAddress); ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </article>

                </div>
            </div>
        </div>
    </section>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
