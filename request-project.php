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
        $userName = trim($_POST['user_name'] ?? '');
        $email = strtolower(trim($_POST['email'] ?? ''));
        $phone = trim($_POST['phone'] ?? '');
        $companyName = trim($_POST['company_name'] ?? '');
        $projectType = trim($_POST['project_type'] ?? 'New Software Project');
        $projectCategory = trim($_POST['project_category'] ?? 'Website');
        $budgetRange = trim($_POST['budget_range'] ?? 'Negotiable');
        $expectedDelivery = !empty($_POST['expected_delivery_date']) ? $_POST['expected_delivery_date'] : null;
        $description = trim($_POST['description'] ?? '');

        if (empty($userName) || empty($email) || empty($description)) {
            throw new Exception("Please provide your Name, Email, and Project Description.");
        }

        $filePath = handle_file_upload('file', 'requests');
        $userId = is_logged_in() ? $_SESSION['user_id'] : null;

        $pdo = get_db();
        $stmt = $pdo->prepare("INSERT INTO project_requests (user_id, user_name, email, phone, company_name, project_type, project_category, budget_range, expected_delivery_date, description, file_path) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$userId, $userName, $email, $phone, $companyName, $projectType, $projectCategory, $budgetRange, $expectedDelivery, $description, $filePath]);

        // Send Email Receipts to User & Admin via SMTP
        send_project_request_receipt_email($email, $userName, $projectType, $projectCategory, $budgetRange);
        send_admin_project_request_alert($userName, $email, $phone, $projectType, $budgetRange, $description);

        $messageSent = "Project request submitted successfully! A confirmation email has been sent to " . htmlspecialchars($email) . ". Our team will review your proposal and reply promptly.";
    } catch (Throwable $e) {
        $errorMessage = $e->getMessage();
    }
}

$user = get_current_user_data();
?>

<main class="kt-main-wrapper">

    <!-- 1. HERO SECTION (Tell Us About Your Project) -->
    <section class="kt-consult-hero">
        <div class="kt-container">
            <div class="kt-consult-hero-grid">
                <!-- Left Content -->
                <div class="kt-consult-hero-content">
                    <div class="kt-consult-eyebrow">
                        <span>PROJECT INTAKE</span>
                        <span class="eyebrow-line"></span>
                    </div>
                    <h1 class="kt-consult-hero-title">
                        Tell Us About Your<br>
                        <span class="kt-text-brand">Next Project</span>
                    </h1>
                    <p class="kt-consult-hero-desc">
                        Provide details about your custom software, AI assistant, mobile app, or enterprise portal. We'll evaluate your requirements and provide an architectural roadmap and transparent estimate.
                    </p>

                    <!-- 3 Hero Mini Badges -->
                    <div class="kt-consult-hero-badges">
                        <div class="kt-hero-badge-item">
                            <span class="badge-icon-wrap"><?php echo render_icon('ShieldCheck', 18); ?></span>
                            <span class="badge-text">Confidential & NDA Protected</span>
                        </div>
                        <div class="kt-hero-badge-item">
                            <span class="badge-icon-wrap"><?php echo render_icon('Lightbulb', 18); ?></span>
                            <span class="badge-text">Detailed Scope & Estimate</span>
                        </div>
                        <div class="kt-hero-badge-item">
                            <span class="badge-icon-wrap"><?php echo render_icon('Clock', 18); ?></span>
                            <span class="badge-text">Response in 24 Hours</span>
                        </div>
                    </div>
                </div>

                <!-- Right Visual (Matching Desk Mockup) -->
                <div class="kt-consult-hero-visual">
                    <img src="<?php echo $baseUrl; ?>assets/images/consultation-hero-visual.png" alt="KendatTech - Request a Project" class="kt-consult-hero-img">
                </div>
            </div>
        </div>
    </section>

    <!-- 2. FORM & SIDEBAR SECTION (Intake Form & Project Advisory) -->
    <section class="kt-consult-main-section" id="project-form">
        <div class="kt-container">
            <div class="kt-consult-layout-grid">
                
                <!-- Left: Project Request Form Card -->
                <div class="kt-consult-form-card">
                    <div class="kt-form-card-badge">
                        <?php echo render_icon('TerminalSquare', 14); ?>
                        <span>PROJECT PROPOSAL INTAKE</span>
                    </div>
                    <h2 class="kt-form-card-title">Request a Project Proposal</h2>
                    <p class="kt-form-card-sub">
                        Fill in the details below to receive a custom technical roadmap, architecture plan, and cost estimate.
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

                    <form method="post" action="" enctype="multipart/form-data" class="kt-modern-form">
                        <?php echo csrf_input(); ?>

                        <!-- Row 1: Full Name & Email Address -->
                        <div class="kt-form-row-2">
                            <div class="kt-form-group">
                                <label for="user_name" class="kt-form-label">
                                    <?php echo render_icon('UserRound', 15); ?> Your Full Name <span class="required">*</span>
                                </label>
                                <input id="user_name" name="user_name" type="text" class="kt-form-control" placeholder="e.g. Babajide Sanwo-Olu" value="<?php echo htmlspecialchars($_POST['user_name'] ?? $user['full_name'] ?? ''); ?>" required>
                            </div>

                            <div class="kt-form-group">
                                <label for="email" class="kt-form-label">
                                    <?php echo render_icon('Mail', 15); ?> Email Address <span class="required">*</span>
                                </label>
                                <input id="email" name="email" type="email" class="kt-form-control" placeholder="e.g. b.sanwoolu@company.ng" value="<?php echo htmlspecialchars($_POST['email'] ?? $user['email'] ?? ''); ?>" required>
                            </div>
                        </div>

                        <!-- Row 2: Phone & Company Name -->
                        <div class="kt-form-row-2">
                            <div class="kt-form-group">
                                <label for="phone" class="kt-form-label">
                                    <?php echo render_icon('Phone', 15); ?> Phone Number
                                </label>
                                <input id="phone" name="phone" type="tel" class="kt-form-control" placeholder="+234 803 123 4567" value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>">
                            </div>

                            <div class="kt-form-group">
                                <label for="company_name" class="kt-form-label">
                                    <?php echo render_icon('BriefcaseBusiness', 15); ?> Company / Organization Name
                                </label>
                                <input id="company_name" name="company_name" type="text" class="kt-form-control" placeholder="e.g. Lagos Tech Dynamics Ltd" value="<?php echo htmlspecialchars($_POST['company_name'] ?? ''); ?>">
                            </div>
                        </div>

                        <!-- Row 3: Project Title & Category -->
                        <div class="kt-form-row-2">
                            <div class="kt-form-group">
                                <label for="project_type" class="kt-form-label">
                                    <?php echo render_icon('Code', 15); ?> Project Title / Type <span class="required">*</span>
                                </label>
                                <input id="project_type" name="project_type" type="text" class="kt-form-control" placeholder="e.g. AI Customer Agent or ERP" value="<?php echo htmlspecialchars($_GET['service'] ?? $_POST['project_type'] ?? 'Custom Software Project'); ?>" required>
                            </div>

                            <div class="kt-form-group">
                                <label for="project_category" class="kt-form-label">
                                    <?php echo render_icon('Settings', 15); ?> Primary Category <span class="required">*</span>
                                </label>
                                <div class="kt-select-wrap">
                                    <select id="project_category" name="project_category" class="kt-form-control kt-select-input" required>
                                        <option value="Custom Software" <?php echo (($_POST['project_category'] ?? '') === 'Custom Software') ? 'selected' : ''; ?>>Custom Software Development</option>
                                        <option value="Website" <?php echo (($_POST['project_category'] ?? '') === 'Website') ? 'selected' : ''; ?>>Website & Web Application</option>
                                        <option value="Mobile App" <?php echo (($_POST['project_category'] ?? '') === 'Mobile App') ? 'selected' : ''; ?>>Mobile App (iOS & Android)</option>
                                        <option value="AI Solution" <?php echo (($_POST['project_category'] ?? '') === 'AI Solution') ? 'selected' : ''; ?>>AI & Machine Learning Solution</option>
                                        <option value="Cloud Infrastructure" <?php echo (($_POST['project_category'] ?? '') === 'Cloud Infrastructure') ? 'selected' : ''; ?>>Cloud Infrastructure & DevOps</option>
                                        <option value="Database Management" <?php echo (($_POST['project_category'] ?? '') === 'Database Management') ? 'selected' : ''; ?>>Database & System Integration</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Row 4: Budget Range & Expected Delivery Date -->
                        <div class="kt-form-row-2">
                            <div class="kt-form-group">
                                <label for="budget_range" class="kt-form-label">
                                    <?php echo render_icon('TrendingUp', 15); ?> Estimated Budget
                                </label>
                                <div class="kt-select-wrap">
                                    <select id="budget_range" name="budget_range" class="kt-form-control kt-select-input">
                                        <option value="Negotiable" <?php echo (($_POST['budget_range'] ?? '') === 'Negotiable') ? 'selected' : ''; ?>>Negotiable / Open to Discussion</option>
                                        <option value="₦1,000,000 - ₦3,000,000" <?php echo (($_POST['budget_range'] ?? '') === '₦1,000,000 - ₦3,000,000') ? 'selected' : ''; ?>>₦1,000,000 - ₦3,000,000</option>
                                        <option value="₦3,000,000 - ₦10,000,000" <?php echo (($_POST['budget_range'] ?? '') === '₦3,000,000 - ₦10,000,000') ? 'selected' : ''; ?>>₦3,000,000 - ₦10,000,000</option>
                                        <option value="₦10,000,000+" <?php echo (($_POST['budget_range'] ?? '') === '₦10,000,000+') ? 'selected' : ''; ?>>₦10,000,000+ (Enterprise)</option>
                                    </select>
                                </div>
                            </div>

                            <div class="kt-form-group">
                                <label for="expected_delivery_date" class="kt-form-label">
                                    <?php echo render_icon('Calendar', 15); ?> Target Delivery Date
                                </label>
                                <input id="expected_delivery_date" name="expected_delivery_date" type="date" class="kt-form-control" value="<?php echo htmlspecialchars($_POST['expected_delivery_date'] ?? ''); ?>">
                            </div>
                        </div>

                        <!-- Row 5: Detailed Description -->
                        <div class="kt-form-group">
                            <label for="description" class="kt-form-label">
                                <?php echo render_icon('MessageSquareText', 15); ?> Detailed Project Requirements <span class="required">*</span>
                            </label>
                            <textarea id="description" name="description" rows="5" class="kt-form-control" placeholder="Outline your features, target audience, third-party integrations, and key business goals..." required><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
                        </div>

                        <!-- Row 6: File Upload Attachment -->
                        <div class="kt-form-group">
                            <label for="project_file" class="kt-form-label">
                                <?php echo render_icon('ExternalLink', 15); ?> Attach Project Document / RFP / Wireframes (Optional)
                            </label>
                            <div class="kt-file-upload-box">
                                <input id="project_file" name="file" type="file" class="kt-file-input">
                                <div class="kt-file-upload-content">
                                    <span class="upload-icon"><?php echo render_icon('Cloud', 24); ?></span>
                                    <span class="upload-text">Click to browse or drop file here</span>
                                    <span class="upload-hint">Supported formats: PDF, DOCX, ZIP, PNG, JPG (Max 15MB)</span>
                                </div>
                            </div>
                        </div>

                        <!-- Form Footer Actions -->
                        <div class="kt-form-footer">
                            <button type="submit" class="kt-btn-green" style="background-color: #0B5D3B !important; background-image: none !important;">
                                <?php echo render_icon('Send', 16); ?> Submit Project Request <?php echo render_icon('ArrowRight', 16); ?>
                            </button>
                            <div class="kt-form-safety-note">
                                <?php echo render_icon('Lock', 14); ?>
                                <span>Strictly protected by NDA & privacy standards.</span>
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
                            You can also reach our engineering lead directly through our contact details below.
                        </p>
                        <div class="kt-immediate-list">
                            <div class="kt-immediate-item">
                                <div class="kt-immediate-icon-circle">
                                    <?php echo render_icon('Phone', 18); ?>
                                </div>
                                <div class="kt-immediate-text">
                                    <strong class="item-title">+234 803 123 4567</strong>
                                    <span class="item-desc">Direct project advisory</span>
                                </div>
                            </div>

                            <div class="kt-immediate-item">
                                <div class="kt-immediate-icon-circle">
                                    <?php echo render_icon('Mail', 18); ?>
                                </div>
                                <div class="kt-immediate-text">
                                    <strong class="item-title">info@kendattech.com</strong>
                                    <span class="item-desc">Proposal submissions desk</span>
                                </div>
                            </div>

                            <div class="kt-immediate-item">
                                <div class="kt-immediate-icon-circle">
                                    <?php echo render_icon('MapPin', 18); ?>
                                </div>
                                <div class="kt-immediate-text">
                                    <strong class="item-title">Lagos, Nigeria</strong>
                                    <span class="item-desc">Our headquarters</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Card 2: What Happens Next? (White Card with Green Checkmarks) -->
                    <div class="kt-sidebar-why-card">
                        <div class="kt-why-card-head">
                            <div class="kt-why-head-icon">
                                <?php echo render_icon('Headset', 20); ?>
                            </div>
                            <h3 class="kt-why-card-title">What Happens Next?</h3>
                        </div>
                        <ul class="kt-why-check-list">
                            <li>
                                <span class="check-icon"><?php echo render_icon('Check', 14); ?></span>
                                <span>Requirements analysis within 24 hours</span>
                            </li>
                            <li>
                                <span class="check-icon"><?php echo render_icon('Check', 14); ?></span>
                                <span>Technical discovery & scoping session</span>
                            </li>
                            <li>
                                <span class="check-icon"><?php echo render_icon('Check', 14); ?></span>
                                <span>Tailored milestone architecture & estimate</span>
                            </li>
                            <li>
                                <span class="check-icon"><?php echo render_icon('Check', 14); ?></span>
                                <span>Sprint planning and development kickoff</span>
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
                    <span class="flank-text">OUR WORKFLOW</span>
                    <span class="flank-dash">—</span>
                </div>
                <h2 class="kt-section-title">
                    Simple Steps to <span class="kt-text-brand">Launch Your Project</span>
                </h2>
                <p class="kt-section-sub">
                    From your initial idea to full deployment and scale, our engineering team ensures transparent, timely, and secure execution.
                </p>
            </div>

            <div class="kt-steps-flow-wrap">
                <!-- Step 01 -->
                <div class="kt-step-flow-item">
                    <div class="kt-step-icon-circle">
                        <?php echo render_icon('TerminalSquare', 20); ?>
                        <span class="step-num-pill">01</span>
                    </div>
                    <h4 class="kt-step-flow-title">Submit Request</h4>
                    <p class="kt-step-flow-desc">Provide your project scope, features, and timeline goals.</p>
                </div>

                <div class="kt-step-connector-arrow">
                    <?php echo render_icon('ArrowRight', 18); ?>
                </div>

                <!-- Step 02 -->
                <div class="kt-step-flow-item">
                    <div class="kt-step-icon-circle">
                        <?php echo render_icon('Headset', 20); ?>
                        <span class="step-num-pill">02</span>
                    </div>
                    <h4 class="kt-step-flow-title">Discovery Session</h4>
                    <p class="kt-step-flow-desc">Our tech leads align on architecture and data pipelines.</p>
                </div>

                <div class="kt-step-connector-arrow">
                    <?php echo render_icon('ArrowRight', 18); ?>
                </div>

                <!-- Step 03 -->
                <div class="kt-step-flow-item">
                    <div class="kt-step-icon-circle">
                        <?php echo render_icon('Code', 20); ?>
                        <span class="step-num-pill">03</span>
                    </div>
                    <h4 class="kt-step-flow-title">Receive Proposal</h4>
                    <p class="kt-step-flow-desc">Get transparent milestones, fixed timelines, and budget.</p>
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
                    <h4 class="kt-step-flow-title">Build & Launch</h4>
                    <p class="kt-step-flow-desc">Agile development sprints with regular staging reviews.</p>
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
                        <span class="kt-consult-cta-eyebrow">READY TO BRING YOUR VISION TO LIFE?</span>
                        <h2 class="kt-consult-cta-heading">Let's Build Something Exceptional Together</h2>
                        <p class="kt-consult-cta-sub">Speak with our software engineers today and take the first step towards scalable digital transformation.</p>
                    </div>
                </div>
                <div class="kt-consult-cta-right">
                    <a href="<?php echo $baseUrl; ?>book-appointment.php" class="kt-cta-btn-white">
                        Book a Consultation <?php echo render_icon('ArrowRight', 16); ?>
                    </a>
                </div>
            </div>
        </div>
    </section>

</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
