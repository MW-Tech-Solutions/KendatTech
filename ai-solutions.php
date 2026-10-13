<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/header.php';

$pdo = get_db();
try {
    $aiStmt = $pdo->query("SELECT * FROM ai_solutions WHERE status = 'active' ORDER BY sort_order ASC, id DESC");
    $aiSolutions = $aiStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
    $aiSolutions = [];
}
?>

<main class="kt-main-wrapper">

    <!-- 1. HERO SECTION (Powering Smarter Decisions with AI Technology) -->
    <section class="kt-ai-hero">
        <div class="kt-container">
            <div class="kt-ai-hero-grid">
                <!-- Left Hero Content -->
                <div class="kt-ai-hero-content">
                    <div class="kt-ai-eyebrow">
                        <span>ARTIFICIAL INTELLIGENCE SOLUTIONS</span>
                        <span class="eyebrow-line"></span>
                    </div>
                    <h1 class="kt-ai-hero-title">
                        Powering Smarter<br>
                        Decisions with<br>
                        <span class="kt-text-brand">AI Technology</span>
                    </h1>
                    <p class="kt-ai-hero-desc">
                        We build intelligent AI solutions that help businesses automate processes, gain valuable insights, and create smarter, faster, and more efficient operations.
                    </p>
                    <div class="kt-ai-hero-actions">
                        <a href="#ai-solutions-grid" class="kt-btn-emerald">
                            Explore Our AI Solutions <?php echo render_icon('ArrowRight', 16); ?>
                        </a>
                        <a href="<?php echo $baseUrl; ?>book-appointment.php" class="kt-btn-outline-emerald">
                            Get a Consultation
                        </a>
                    </div>
                </div>

                <!-- Right Hero Visual (Exact Match from User Mockup) -->
                <div class="kt-ai-hero-visual">
                    <img src="<?php echo $baseUrl; ?>assets/images/ai-hero-visual.png" alt="KendatTech AI - Powering Smarter Decisions with AI Technology" class="kt-ai-robot-img">
                </div>
            </div>
        </div>
    </section>

    <!-- 2. OUR AI SOLUTIONS - 6 Cards Grid (Intelligent Solutions for Real-World Problems) -->
    <section class="kt-ai-solutions-section" id="ai-solutions-grid">
        <div class="kt-container">
            <div class="kt-section-header-center">
                <div class="kt-eyebrow-flanked">
                    <span class="flank-dash">—</span>
                    <span class="flank-text">OUR AI SOLUTIONS</span>
                    <span class="flank-dash">—</span>
                </div>
                <h2 class="kt-section-title">
                    Intelligent Solutions for <span class="kt-text-brand">Real-World Problems</span>
                </h2>
                <p class="kt-section-sub">
                    From automation to advanced analytics, our AI solutions are designed to help you work smarter, reduce costs, and unlock new opportunities.
                </p>
            </div>

            <div class="kt-ai-cards-grid">
                <!-- Card 1: Machine Learning & Predictive Analytics -->
                <div class="kt-ai-card">
                    <div class="kt-ai-card-icon-wrap">
                        <?php echo render_icon('BrainCircuit', 22); ?>
                    </div>
                    <h3 class="kt-ai-card-title">Machine Learning & Predictive Analytics</h3>
                    <p class="kt-ai-card-desc">
                        Turn your data into actionable insights with machine learning models that predict, classify and optimize outcomes.
                    </p>
                    <a href="<?php echo $baseUrl; ?>request-project.php?service=ai-ml" class="kt-ai-card-link">
                        Learn More <?php echo render_icon('ArrowRight', 14); ?>
                    </a>
                </div>

                <!-- Card 2: Natural Language Processing (NLP) -->
                <div class="kt-ai-card">
                    <div class="kt-ai-card-icon-wrap">
                        <?php echo render_icon('MessageSquareText', 22); ?>
                    </div>
                    <h3 class="kt-ai-card-title">Natural Language Processing (NLP)</h3>
                    <p class="kt-ai-card-desc">
                        Build intelligent chatbots, sentiment analysis and language understanding solutions for better customer engagement.
                    </p>
                    <a href="<?php echo $baseUrl; ?>request-project.php?service=ai-nlp" class="kt-ai-card-link">
                        Learn More <?php echo render_icon('ArrowRight', 14); ?>
                    </a>
                </div>

                <!-- Card 3: Computer Vision & Image Analysis -->
                <div class="kt-ai-card">
                    <div class="kt-ai-card-icon-wrap">
                        <?php echo render_icon('ScanEye', 22); ?>
                    </div>
                    <h3 class="kt-ai-card-title">Computer Vision & Image Analysis</h3>
                    <p class="kt-ai-card-desc">
                        Analyze and interpret visual data for applications like object detection, facial recognition, medical imaging and more.
                    </p>
                    <a href="<?php echo $baseUrl; ?>request-project.php?service=ai-cv" class="kt-ai-card-link">
                        Learn More <?php echo render_icon('ArrowRight', 14); ?>
                    </a>
                </div>

                <!-- Card 4: AI Automation & Process Optimization -->
                <div class="kt-ai-card">
                    <div class="kt-ai-card-icon-wrap">
                        <?php echo render_icon('Settings', 22); ?>
                    </div>
                    <h3 class="kt-ai-card-title">AI Automation & Process Optimization</h3>
                    <p class="kt-ai-card-desc">
                        Automate repetitive tasks and streamline workflows with intelligent AI systems that save time and reduce costs.
                    </p>
                    <a href="<?php echo $baseUrl; ?>request-project.php?service=ai-automation" class="kt-ai-card-link">
                        Learn More <?php echo render_icon('ArrowRight', 14); ?>
                    </a>
                </div>

                <!-- Card 5: Data Analytics & Business Intelligence -->
                <div class="kt-ai-card">
                    <div class="kt-ai-card-icon-wrap">
                        <?php echo render_icon('ChartNoAxesCombined', 22); ?>
                    </div>
                    <h3 class="kt-ai-card-title">Data Analytics & Business Intelligence</h3>
                    <p class="kt-ai-card-desc">
                        Get deeper insights from your data with AI-powered analytics and interactive dashboards.
                    </p>
                    <a href="<?php echo $baseUrl; ?>request-project.php?service=ai-analytics" class="kt-ai-card-link">
                        Learn More <?php echo render_icon('ArrowRight', 14); ?>
                    </a>
                </div>

                <!-- Card 6: Custom AI Solutions -->
                <div class="kt-ai-card">
                    <div class="kt-ai-card-icon-wrap">
                        <?php echo render_icon('Cloud', 22); ?>
                    </div>
                    <h3 class="kt-ai-card-title">Custom AI Solutions</h3>
                    <p class="kt-ai-card-desc">
                        Need something unique? We design and build tailored AI solutions to fit your specific business goals.
                    </p>
                    <a href="<?php echo $baseUrl; ?>request-project.php?service=ai-custom" class="kt-ai-card-link">
                        Learn More <?php echo render_icon('ArrowRight', 14); ?>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- 3. HOW WE WORK - AI DEVELOPMENT PROCESS (5 Steps & Visual) -->
    <section class="kt-ai-process-section">
        <div class="kt-container">
            <div class="kt-process-header-left">
                <div class="kt-eyebrow-flanked text-start">
                    <span class="flank-dash">—</span>
                    <span class="flank-text">HOW WE WORK</span>
                    <span class="flank-dash">—</span>
                </div>
                <h2 class="kt-section-title text-start">
                    Our AI <span class="kt-text-brand">Development Process</span>
                </h2>
                <p class="kt-section-sub text-start">
                    We follow a proven process to ensure your AI solution is delivered on time, within budget, and with measurable results.
                </p>
            </div>

            <div class="kt-ai-process-grid">
                <!-- Left: 5 Continuous Process Steps -->
                <div class="kt-ai-steps-wrap">
                    <div class="kt-ai-steps-track">
                        <!-- Step 01 -->
                        <div class="kt-ai-step-item">
                            <div class="kt-ai-step-circle">
                                <?php echo render_icon('Lightbulb', 20); ?>
                            </div>
                            <div class="kt-ai-step-badge">01 <span class="step-label">Discover</span></div>
                            <p class="kt-ai-step-desc">Understand your goals, challenges and data.</p>
                        </div>

                        <!-- Step 02 -->
                        <div class="kt-ai-step-item">
                            <div class="kt-ai-step-circle">
                                <?php echo render_icon('Database', 20); ?>
                            </div>
                            <div class="kt-ai-step-badge">02 <span class="step-label">Build</span></div>
                            <p class="kt-ai-step-desc">Develop and train AI models using best practices.</p>
                        </div>

                        <!-- Step 03 -->
                        <div class="kt-ai-step-item">
                            <div class="kt-ai-step-circle">
                                <?php echo render_icon('ShieldCheck', 20); ?>
                            </div>
                            <div class="kt-ai-step-badge">03 <span class="step-label">Test</span></div>
                            <p class="kt-ai-step-desc">Validate performance, accuracy and security.</p>
                        </div>

                        <!-- Step 04 -->
                        <div class="kt-ai-step-item">
                            <div class="kt-ai-step-circle">
                                <?php echo render_icon('Cloud', 20); ?>
                            </div>
                            <div class="kt-ai-step-badge">04 <span class="step-label">Deploy</span></div>
                            <p class="kt-ai-step-desc">Integrate into your systems and scale.</p>
                        </div>

                        <!-- Step 05 -->
                        <div class="kt-ai-step-item">
                            <div class="kt-ai-step-circle">
                                <?php echo render_icon('Headset', 20); ?>
                            </div>
                            <div class="kt-ai-step-badge">05 <span class="step-label">Support</span></div>
                            <p class="kt-ai-step-desc">Provide ongoing monitoring and improvements.</p>
                        </div>
                    </div>
                </div>

                <!-- Right: Process Visual Mockup -->
                <div class="kt-ai-process-visual">
                    <img src="<?php echo $baseUrl; ?>assets/images/ai-process-laptop.png" alt="KendatTech AI - From Data to Intelligence" class="kt-ai-process-img">
                </div>
            </div>
        </div>
    </section>

    <!-- 4. WHY CHOOSE KENDATTECH FOR AI? (Two-column layout) -->
    <section class="kt-ai-why-section">
        <div class="kt-container">
            <div class="kt-ai-why-grid">
                <!-- Left: Title, Subtitle, and Get Started Button -->
                <div class="kt-ai-why-left">
                    <div class="kt-eyebrow-flanked text-start">
                        <span class="flank-dash">—</span>
                        <span class="flank-text">WHY CHOOSE US</span>
                        <span class="flank-dash">—</span>
                    </div>
                    <h2 class="kt-section-title text-start">
                        Why Choose <span class="kt-text-brand">KendatTech for AI?</span>
                    </h2>
                    <p class="kt-section-sub text-start">
                        We combine technical expertise, innovation and a client-first approach to deliver AI solutions that create real value for your business.
                    </p>
                    <div class="kt-ai-why-action">
                        <a href="<?php echo $baseUrl; ?>book-appointment.php" class="kt-btn-emerald">
                            Get Started Today <?php echo render_icon('ArrowRight', 16); ?>
                        </a>
                    </div>
                </div>

                <!-- Right: 4 Feature Mini Cards -->
                <div class="kt-ai-why-cards">
                    <!-- Feature 1 -->
                    <div class="kt-ai-feature-card">
                        <div class="kt-ai-feature-icon-wrap">
                            <?php echo render_icon('Users', 20); ?>
                        </div>
                        <div class="kt-ai-feature-info">
                            <h4 class="kt-ai-feature-title">Experienced Team</h4>
                            <p class="kt-ai-feature-desc">Skilled professionals with proven AI experience.</p>
                        </div>
                    </div>

                    <!-- Feature 2 -->
                    <div class="kt-ai-feature-card">
                        <div class="kt-ai-feature-icon-wrap">
                            <?php echo render_icon('Scaling', 20); ?>
                        </div>
                        <div class="kt-ai-feature-info">
                            <h4 class="kt-ai-feature-title">Scalable Solutions</h4>
                            <p class="kt-ai-feature-desc">Built to grow with your business.</p>
                        </div>
                    </div>

                    <!-- Feature 3 -->
                    <div class="kt-ai-feature-card">
                        <div class="kt-ai-feature-icon-wrap">
                            <?php echo render_icon('ShieldCheck', 20); ?>
                        </div>
                        <div class="kt-ai-feature-info">
                            <h4 class="kt-ai-feature-title">Data Security</h4>
                            <p class="kt-ai-feature-desc">Your data is protected at every step.</p>
                        </div>
                    </div>

                    <!-- Feature 4 -->
                    <div class="kt-ai-feature-card">
                        <div class="kt-ai-feature-icon-wrap">
                            <?php echo render_icon('Headset', 20); ?>
                        </div>
                        <div class="kt-ai-feature-info">
                            <h4 class="kt-ai-feature-title">Ongoing Support</h4>
                            <p class="kt-ai-feature-desc">We're with you even after deployment.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- 5. CTA BANNER (Let's Build Your AI Solution Together) -->
    <section class="kt-ai-cta-section">
        <div class="kt-container">
            <div class="kt-ai-cta-card">
                <div class="kt-ai-cta-left">
                    <div class="kt-ai-cta-icon-circle">
                        <?php echo render_icon('BrainCircuit', 28); ?>
                    </div>
                    <div class="kt-ai-cta-text">
                        <span class="kt-ai-cta-eyebrow">READY TO TRANSFORM YOUR BUSINESS?</span>
                        <h2 class="kt-ai-cta-heading">Let's Build Your AI Solution Together</h2>
                        <p class="kt-ai-cta-sub">Get expert guidance and a custom AI solution tailored to your needs.</p>
                    </div>
                </div>
                <div class="kt-ai-cta-right">
                    <a href="<?php echo $baseUrl; ?>contact.php" class="kt-cta-btn-white">
                        Contact Us <?php echo render_icon('ArrowRight', 16); ?>
                    </a>
                </div>
            </div>
        </div>
    </section>

</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
