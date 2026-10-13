<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/header.php';
$settings = get_settings();
$contactEmail = $settings['contact_email'] ?? 'legal@kendattech.com';
$contactPhone = $settings['contact_phone'] ?? '+234 800 000 0000';
$officeAddress = $settings['office_address'] ?? 'Lagos, Nigeria';
?>

<main>
    <!-- Terms of Service Page Hero -->
    <section class="kt-subpage-hero">
        <div class="kt-container text-center">
            <span class="kt-subpage-pill-badge">LEGAL FRAMEWORK & AGREEMENTS</span>
            <h1 class="kt-subpage-hero-title">Terms of Service</h1>
            <p class="kt-subpage-hero-sub">Please read these terms and conditions carefully before using our software applications, client portals, AI solutions, or engaging our engineering team.</p>
            <div class="kt-subpage-accent-dot"></div>
        </div>
    </section>

    <!-- Terms of Service Main Content -->
    <section class="legal-section-wrap" style="padding: 40px 0 80px;">
        <div class="container">
            <div class="legal-layout-grid" style="display: grid; grid-template-columns: 280px 1fr; gap: 40px; align-items: start;">
                
                <!-- Quick Navigation Sidebar -->
                <aside class="legal-sidebar glass-card" style="padding: 24px; border-radius: 20px; position: sticky; top: 100px;">
                    <h4 style="font-size: 14px; text-transform: uppercase; letter-spacing: 0.05em; color: var(--muted); margin-bottom: 16px; display: flex; align-items: center; gap: 8px;">
                        <?php echo render_icon('ListFilter', 16); ?> Table of Contents
                    </h4>
                    <nav class="legal-nav" style="display: flex; flex-direction: column; gap: 10px; font-size: 14px;">
                        <a href="#acceptance" style="color: var(--text-dark); font-weight: 600; text-decoration: none;">1. Acceptance of Terms</a>
                        <a href="#services-scope" style="color: var(--muted); text-decoration: none;">2. Scope of Engineering Services</a>
                        <a href="#account-responsibilities" style="color: var(--muted); text-decoration: none;">3. Client Accounts & Credentials</a>
                        <a href="#intellectual-property" style="color: var(--muted); text-decoration: none;">4. Intellectual Property Rights</a>
                        <a href="#payment-billing" style="color: var(--muted); text-decoration: none;">5. Project Payments & Billing</a>
                        <a href="#acceptable-use" style="color: var(--muted); text-decoration: none;">6. Acceptable System Use</a>
                        <a href="#limitation-liability" style="color: var(--muted); text-decoration: none;">7. Limitation of Liability</a>
                        <a href="#governing-law" style="color: var(--muted); text-decoration: none;">8. Governing Law & Dispute Resolution</a>
                    </nav>
                </aside>

                <!-- Document Content Panel -->
                <div class="legal-body-content">
                    
                    <article id="acceptance" class="kt-legal-card">
                        <div class="kt-legal-card-header">
                            <div class="kt-legal-icon-box"><?php echo render_icon('CheckCircle2', 20); ?></div>
                            <h2>1. Acceptance of Terms</h2>
                        </div>
                        <div class="kt-legal-card-body">
                            <p>These Terms of Service ("Agreement") constitute a legally binding agreement between you ("Client", "User", or "You") and <strong>Kendat Integrated Services ("Kendat Tech", "We", "Us", or "Our")</strong> governing your access to and use of our corporate website, custom portal applications, API integrations, and software engineering services.</p>
                            <p style="margin-top: 12px;">By registering an account, submitting a project request, or booking an appointment, you acknowledge that you have read, understood, and agree to be bound by all terms outlined herein.</p>
                        </div>
                    </article>

                    <article id="services-scope" class="kt-legal-card">
                        <div class="kt-legal-card-header">
                            <div class="kt-legal-icon-box"><?php echo render_icon('BriefcaseBusiness', 20); ?></div>
                            <h2>2. Scope of Engineering Services</h2>
                        </div>
                        <div class="kt-legal-card-body">
                            <p>Kendat Integrated Services specializes in enterprise software design, full-stack web application development, institutional school portals, custom AI copilots, cloud database infrastructure, and technical consulting. Detailed project scopes, deliverables, timelines, and acceptance criteria are finalized in specific Statement of Work (SOW) documents executed between Kendat Tech and the Client.</p>
                        </div>
                    </article>

                    <article id="account-responsibilities" class="kt-legal-card">
                        <div class="kt-legal-card-header">
                            <div class="kt-legal-icon-box"><?php echo render_icon('UserRound', 20); ?></div>
                            <h2>3. Client Accounts & Security</h2>
                        </div>
                        <div class="kt-legal-card-body">
                            <p>You are responsible for maintaining the confidentiality of your account authentication credentials (email and password). You agree to notify Kendat Tech immediately upon becoming aware of any unauthorized access to or compromise of your client account.</p>
                            <p style="margin-top: 12px;">Accounts registered using fraudulent, automated, or disposable email addresses are subject to immediate suspension or termination.</p>
                        </div>
                    </article>

                    <article id="intellectual-property" class="kt-legal-card">
                        <div class="kt-legal-card-header">
                            <div class="kt-legal-icon-box"><?php echo render_icon('Code', 20); ?></div>
                            <h2>4. Intellectual Property Rights</h2>
                        </div>
                        <div class="kt-legal-card-body">
                            <p>Unless explicitly agreed otherwise in a signed contract:</p>
                            <ul style="padding-left: 20px; margin-top: 12px; margin-bottom: 0;">
                                <li style="margin-bottom: 8px;"><strong>Custom Deliverables:</strong> Upon full payment of agreed project fees, ownership of custom codebase deliverables, graphic assets, and database schemas developed specifically for the Client shall be assigned to the Client.</li>
                                <li><strong>Pre-existing Technology:</strong> Kendat Tech retains all rights to pre-existing framework modules, proprietary algorithm components, development toolkits, and software libraries utilized across projects.</li>
                            </ul>
                        </div>
                    </article>

                    <article id="payment-billing" class="kt-legal-card">
                        <div class="kt-legal-card-header">
                            <div class="kt-legal-icon-box"><?php echo render_icon('BriefcaseBusiness', 20); ?></div>
                            <h2>5. Project Payments & Billing Terms</h2>
                        </div>
                        <div class="kt-legal-card-body">
                            <p>Project milestone billing schedules are stipulated in individual project agreements. Invoices are payable within the payment window specified on the invoice. Milestone deliveries or deployment to production environments may be withheld pending receipt of due milestone payments.</p>
                        </div>
                    </article>

                    <article id="acceptable-use" class="kt-legal-card">
                        <div class="kt-legal-card-header">
                            <div class="kt-legal-icon-box"><?php echo render_icon('ShieldCheck', 20); ?></div>
                            <h2>6. Acceptable System Use</h2>
                        </div>
                        <div class="kt-legal-card-body">
                            <p>You agree not to attempt to breach, probe, or vulnerability-scan Kendat Tech systems, inject malicious software, execute unauthorized data scraping, or circumvent system rate limiters. Violation of network or security integrity may result in civil or criminal prosecution.</p>
                        </div>
                    </article>

                    <article id="limitation-liability" class="kt-legal-card">
                        <div class="kt-legal-card-header">
                            <div class="kt-legal-icon-box"><?php echo render_icon('Target', 20); ?></div>
                            <h2>7. Limitation of Liability</h2>
                        </div>
                        <div class="kt-legal-card-body">
                            <p>To the maximum extent permitted by applicable law, Kendat Integrated Services shall not be liable for any indirect, incidental, special, consequential, or punitive damages, or any loss of profits or revenues, resulting from your use of or inability to use our website or services.</p>
                        </div>
                    </article>

                    <article id="governing-law" class="kt-legal-card">
                        <div class="kt-legal-card-header">
                            <div class="kt-legal-icon-box"><?php echo render_icon('Compass', 20); ?></div>
                            <h2>8. Governing Law & Dispute Resolution</h2>
                        </div>
                        <div class="kt-legal-card-body">
                            <p>These terms shall be governed by and construed in accordance with the laws of the <strong>Federal Republic of Nigeria</strong>. Any disputes arising from or relating to these terms or our services shall be submitted to confidential arbitration in Lagos, Nigeria prior to court litigation.</p>
                            
                            <div style="background: #F8FAFC; padding: 20px; border-radius: 12px; border: 1px solid #E2E8F0; margin-top: 18px;">
                                <strong style="color: #0F172A; display: block; font-size: 15px;">Legal Department - Kendat Integrated Services</strong>
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
