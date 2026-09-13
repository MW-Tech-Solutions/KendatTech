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
    <section class="subpage-hero">
        <div class="container text-center">
            <span class="subpage-badge"><?php echo render_icon('FileText', 16); ?> Legal Framework & Agreements</span>
            <h1>Terms of Service</h1>
            <p>Please read these terms and conditions carefully before using our software applications, client portals, AI solutions, or engaging our engineering team.</p>
            <div class="subpage-meta-chips">
                <span class="subpage-chip"><?php echo render_icon('Calendar', 14); ?> Effective Date: September 13, 2026</span>
                <span class="subpage-chip"><?php echo render_icon('FileCheck', 14); ?> Version 3.1</span>
                <span class="subpage-chip"><?php echo render_icon('Scale', 14); ?> Binding Contract</span>
            </div>
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
                <div class="legal-body-content glass-card" style="padding: 40px; border-radius: 24px; line-height: 1.8; color: #334155; font-size: 15px;">
                    
                    <section id="acceptance" style="margin-bottom: 36px;">
                        <h2 style="font-size: 22px; color: #0f172a; margin-bottom: 14px; display: flex; align-items: center; gap: 10px;">
                            <?php echo render_icon('CheckCircle2', 20); ?> 1. Acceptance of Terms
                        </h2>
                        <p>These Terms of Service ("Agreement") constitute a legally binding agreement between you ("Client", "User", or "You") and <strong>Kendat Integrated Services ("Kendat Tech", "We", "Us", or "Our")</strong> governing your access to and use of our corporate website, custom portal applications, API integrations, and software engineering services.</p>
                        <p>By registering an account, submitting a project request, or booking an appointment, you acknowledge that you have read, understood, and agree to be bound by all terms outlined herein.</p>
                    </section>

                    <hr style="border: 0; border-top: 1px solid #e2e8f0; margin: 30px 0;">

                    <section id="services-scope" style="margin-bottom: 36px;">
                        <h2 style="font-size: 22px; color: #0f172a; margin-bottom: 14px; display: flex; align-items: center; gap: 10px;">
                            <?php echo render_icon('BriefcaseBusiness', 20); ?> 2. Scope of Engineering Services
                        </h2>
                        <p>Kendat Integrated Services specializes in enterprise software design, full-stack web application development, institutional school portals, custom AI copilots, cloud database infrastructure, and technical consulting. Detailed project scopes, deliverables, timelines, and acceptance criteria are finalized in specific Statement of Work (SOW) documents executed between Kendat Tech and the Client.</p>
                    </section>

                    <hr style="border: 0; border-top: 1px solid #e2e8f0; margin: 30px 0;">

                    <section id="account-responsibilities" style="margin-bottom: 36px;">
                        <h2 style="font-size: 22px; color: #0f172a; margin-bottom: 14px; display: flex; align-items: center; gap: 10px;">
                            <?php echo render_icon('UserRound', 20); ?> 3. Client Accounts & Security
                        </h2>
                        <p>You are responsible for maintaining the confidentiality of your account authentication credentials (email and password). You agree to notify Kendat Tech immediately upon becoming aware of any unauthorized access to or compromise of your client account.</p>
                        <p>Accounts registered using fraudulent, automated, or disposable email addresses are subject to immediate suspension or termination.</p>
                    </section>

                    <hr style="border: 0; border-top: 1px solid #e2e8f0; margin: 30px 0;">

                    <section id="intellectual-property" style="margin-bottom: 36px;">
                        <h2 style="font-size: 22px; color: #0f172a; margin-bottom: 14px; display: flex; align-items: center; gap: 10px;">
                            <?php echo render_icon('Code', 20); ?> 4. Intellectual Property Rights
                        </h2>
                        <p>Unless explicitly agreed otherwise in a signed contract:</p>
                        <ul style="padding-left: 20px; margin-bottom: 16px;">
                            <li style="margin-bottom: 8px;"><strong>Custom Deliverables:</strong> Upon full payment of agreed project fees, ownership of custom codebase deliverables, graphic assets, and database schemas developed specifically for the Client shall be assigned to the Client.</li>
                            <li style="margin-bottom: 8px;"><strong>Pre-existing Technology:</strong> Kendat Tech retains all rights to pre-existing framework modules, proprietary algorithm components, development toolkits, and software libraries utilized across projects.</li>
                        </ul>
                    </section>

                    <hr style="border: 0; border-top: 1px solid #e2e8f0; margin: 30px 0;">

                    <section id="payment-billing" style="margin-bottom: 36px;">
                        <h2 style="font-size: 22px; color: #0f172a; margin-bottom: 14px; display: flex; align-items: center; gap: 10px;">
                            <?php echo render_icon('CreditCard', 20); ?> 5. Project Payments & Billing Terms
                        </h2>
                        <p>Project milestone billing schedules are stipulated in individual project agreements. Invoices are payable within the payment window specified on the invoice. Milestone deliveries or deployment to production environments may be withheld pending receipt of due milestone payments.</p>
                    </section>

                    <hr style="border: 0; border-top: 1px solid #e2e8f0; margin: 30px 0;">

                    <section id="acceptable-use" style="margin-bottom: 36px;">
                        <h2 style="font-size: 22px; color: #0f172a; margin-bottom: 14px; display: flex; align-items: center; gap: 10px;">
                            <?php echo render_icon('ShieldAlert', 20); ?> 6. Acceptable System Use
                        </h2>
                        <p>You agree not to attempt to breach, probe, or vulnerability-scan Kendat Tech systems, inject malicious software, execute unauthorized data scraping, or circumvent system rate limiters. Violation of network or security integrity may result in civil or criminal prosecution.</p>
                    </section>

                    <hr style="border: 0; border-top: 1px solid #e2e8f0; margin: 30px 0;">

                    <section id="limitation-liability" style="margin-bottom: 36px;">
                        <h2 style="font-size: 22px; color: #0f172a; margin-bottom: 14px; display: flex; align-items: center; gap: 10px;">
                            <?php echo render_icon('Scale', 20); ?> 7. Limitation of Liability
                        </h2>
                        <p>To the maximum extent permitted by applicable law, Kendat Integrated Services shall not be liable for any indirect, incidental, special, consequential, or punitive damages, or any loss of profits or revenues, resulting from your use of or inability to use our website or services.</p>
                    </section>

                    <hr style="border: 0; border-top: 1px solid #e2e8f0; margin: 30px 0;">

                    <section id="governing-law">
                        <h2 style="font-size: 22px; color: #0f172a; margin-bottom: 14px; display: flex; align-items: center; gap: 10px;">
                            <?php echo render_icon('Gavel', 20); ?> 8. Governing Law & Dispute Resolution
                        </h2>
                        <p>These terms shall be governed by and construed in accordance with the laws of the <strong>Federal Republic of Nigeria</strong>. Any disputes arising from or relating to these terms or our services shall be submitted to confidential arbitration in Lagos, Nigeria prior to court litigation.</p>
                        <div style="background: #f1f5f9; padding: 20px; border-radius: 14px; border: 1px solid #cbd5e1; margin-top: 16px;">
                            <strong style="color: #0f172a; display: block; font-size: 16px;">Legal Department - Kendat Integrated Services</strong>
                            <span style="font-size: 14px; color: #475569; display: block; margin-top: 4px;">Email: <a href="mailto:<?php echo htmlspecialchars($contactEmail); ?>" style="color: var(--blue); font-weight: 700; text-decoration: none;"><?php echo htmlspecialchars($contactEmail); ?></a></span>
                            <span style="font-size: 14px; color: #475569; display: block; margin-top: 2px;">Phone: <a href="tel:<?php echo htmlspecialchars($contactPhone); ?>" style="color: #475569; text-decoration: none; font-weight: 600;"><?php echo htmlspecialchars($contactPhone); ?></a></span>
                            <?php if ($officeAddress): ?>
                                <span style="font-size: 14px; color: #475569; display: block; margin-top: 2px;">Office: <?php echo htmlspecialchars($officeAddress); ?></span>
                            <?php endif; ?>
                        </div>
                    </section>

                </div>
            </div>
        </div>
    </section>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
