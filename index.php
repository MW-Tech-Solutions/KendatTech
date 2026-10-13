<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/header.php';

$pdo = get_db();

try {
    $servicesStmt = $pdo->query("SELECT * FROM services WHERE status = 'active' ORDER BY sort_order ASC, id DESC LIMIT 6");
    $services = $servicesStmt->fetchAll();
} catch (Throwable $e) {
    $services = [];
}

try {
    $projectsStmt = $pdo->query("SELECT * FROM projects WHERE status = 'completed' OR status = 'ongoing' ORDER BY featured DESC, id DESC LIMIT 6");
    $projects = $projectsStmt->fetchAll();

    foreach ($projects as &$project) {
        $imgStmt = $pdo->prepare("SELECT image_path FROM project_images WHERE project_id = ? ORDER BY sort_order ASC");
        $imgStmt->execute([$project['id']]);
        $project['images'] = $imgStmt->fetchAll(PDO::FETCH_COLUMN);
    }
    unset($project);
} catch (Throwable $e) {
    $projects = [];
}

try {
    $testimonialsStmt = $pdo->query("SELECT * FROM testimonials WHERE status = 'active' ORDER BY id DESC LIMIT 3");
    $testimonials = $testimonialsStmt->fetchAll();
} catch (Throwable $e) {
    $testimonials = [];
}

// Fallback data if server DB has fewer than 3 items or missing tables
if (count($projects) < 3) {
    $defaultProjects = [
        [
            'id' => 101,
            'title' => 'EduCore Smart School Portal',
            'slug' => 'educore-smart-school-portal',
            'category' => 'School Portal',
            'short_description' => 'A complete digital school platform for results, payments, admissions, and parent communication.',
            'technologies' => 'React, PHP, MySQL, REST API',
            'main_image' => 'projects/projects_6a14675868b2c8.49575753.png',
            'demo_link' => '#',
            'client_name' => 'Demo Academy',
            'images' => []
        ],
        [
            'id' => 102,
            'title' => 'InsightFlow Analytics Dashboard',
            'slug' => 'insightflow-analytics-dashboard',
            'category' => 'Data Analytics',
            'short_description' => 'A business intelligence dashboard for operational KPIs and forecasting.',
            'technologies' => 'React, PHP, MySQL, Chart.js',
            'main_image' => 'projects/projects_6a1570d42efd04.84387159.png',
            'demo_link' => '#',
            'client_name' => 'Retail Group',
            'images' => []
        ],
        [
            'id' => 103,
            'title' => 'AutoDeskOps Workflow Automation',
            'slug' => 'autodeskops-workflow-automation',
            'category' => 'Automation',
            'short_description' => 'An internal automation system for approvals, tasks, and operational tracking.',
            'technologies' => 'React, PHP, MySQL, REST API',
            'main_image' => 'projects/projects_6a156c0eaea917.04572701.png',
            'demo_link' => '#',
            'client_name' => 'Operations Firm',
            'images' => []
        ]
    ];
    $existingSlugs = array_column($projects, 'slug');
    foreach ($defaultProjects as $defP) {
        if (!in_array($defP['slug'], $existingSlugs, true)) {
            $projects[] = $defP;
        }
    }
}

if (empty($testimonials)) {
    $testimonials = [
        [
            'client_name' => 'Ahmed Bello',
            'position_company' => 'CEO, BrightHub Ltd.',
            'message' => 'KendatTech delivered our project on time and exceeded our expectations. Highly recommended!',
            'rating' => 5
        ],
        [
            'client_name' => 'Fatima Usman',
            'position_company' => 'Founder, SafeTech',
            'message' => 'Professional, innovative and always ready to help. Their team truly cares about results.',
            'rating' => 5
        ],
        [
            'client_name' => 'Usman Ibrahim',
            'position_company' => 'CTO, NextGen Solutions',
            'message' => 'Excellent service and top-notch support. KendatTech is our go-to tech partner.',
            'rating' => 5
        ]
    ];
}
?>

<main class="kt-main-wrapper">

    <!-- 1. HERO SECTION WITH CINEMATIC VIDEO BACKGROUND WALLPAPER -->
    <section class="kt-hero-stage kt-hero-with-video">
        <!-- Background Video Wallpaper -->
        <div class="kt-hero-video-bg" aria-hidden="true">
            <video autoplay muted loop playsinline preload="auto" poster="<?php echo $baseUrl; ?>assets/images/hero-video-poster.jpg" class="kt-hero-bg-video" id="ktHeroBgVideo">
                <source src="<?php echo $baseUrl; ?>assets/videos/hero-background.mp4" type="video/mp4">
            </video>
            <!-- High-Contrast Enterprise Gradient Overlay -->
            <div class="kt-hero-video-overlay"></div>
        </div>

        <div class="kt-container" style="position: relative; z-index: 3;">
            <div class="kt-hero-grid">
                <!-- Hero Left Column -->
                <div class="kt-hero-left">
                    <span class="kt-eyebrow">INNOVATIVE TECHNOLOGY SOLUTIONS</span>
                    <h1 class="kt-hero-title">
                        Build Smarter<br>With <span class="kt-text-brand">KendatTech</span>
                    </h1>
                    <p class="kt-hero-lead">
                        We design, develop and deliver modern software solutions that help businesses grow, operate efficiently and stay ahead in a digital world.
                    </p>
                    <div class="kt-hero-ctas">
                        <a href="<?php echo $baseUrl; ?>services.php" class="kt-btn-primary">
                            Our Services <?php echo render_icon('ArrowRight', 16); ?>
                        </a>
                        <a href="<?php echo $baseUrl; ?>about.php" class="kt-btn-outline">
                            Learn More
                        </a>
                    </div>
                </div>

                <!-- Hero Right Column (Exact 3D Laptop Graphic from Mockup) -->
                <div class="kt-hero-right">
                    <div class="kt-hero-graphic-wrap">
                        <img src="<?php echo $baseUrl; ?>assets/images/hero-laptop.png" alt="KendatTech Software Platform" class="kt-hero-graphic-img">
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- 2. VALUE HIGHLIGHTS STRIP (4 Columns across) -->
    <section class="kt-feature-bar">
        <div class="kt-container">
            <div class="kt-feature-grid">
                <div class="kt-feature-item">
                    <div class="kt-feature-icon"><?php echo render_icon('Code', 22); ?></div>
                    <div class="kt-feature-info">
                        <h3>Web & Mobile Apps</h3>
                        <p>Modern. Fast. Responsive.</p>
                    </div>
                </div>
                <div class="kt-feature-item">
                    <div class="kt-feature-icon"><?php echo render_icon('Cloud', 22); ?></div>
                    <div class="kt-feature-info">
                        <h3>Cloud Solutions</h3>
                        <p>Scale Without Limits.</p>
                    </div>
                </div>
                <div class="kt-feature-item">
                    <div class="kt-feature-icon"><?php echo render_icon('Sparkles', 22); ?></div>
                    <div class="kt-feature-info">
                        <h3>IT Consulting</h3>
                        <p>Expert Guidance. Real Results.</p>
                    </div>
                </div>
                <div class="kt-feature-item">
                    <div class="kt-feature-icon"><?php echo render_icon('Settings', 22); ?></div>
                    <div class="kt-feature-info">
                        <h3>Software Development</h3>
                        <p>Custom Solutions. Built for You.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- 3. ABOUT KENDATTECH SECTION -->
    <section class="kt-about-section">
        <div class="kt-container">
            <div class="kt-about-grid">
                <!-- About Left Content -->
                <div class="kt-about-left">
                    <span class="kt-eyebrow">ABOUT KENDATTECH</span>
                    <h2 class="kt-section-title">Turning Ideas Into<br>Powerful Digital Solutions</h2>
                    <p class="kt-body-text">
                        KendatTech is a forward-thinking technology company focused on building high-quality, scalable and secure software solutions for businesses of all sizes. We combine innovation, expertise and passion to deliver digital products that make a real impact.
                    </p>
                    <a href="<?php echo $baseUrl; ?>about.php" class="kt-btn-primary mb-4">
                        Learn More <?php echo render_icon('ArrowRight', 16); ?>
                    </a>

                    <!-- Stats Row -->
                    <div class="kt-stats-row">
                        <div class="kt-stat-block">
                            <span class="kt-stat-number">50+</span>
                            <span class="kt-stat-label">Projects Completed</span>
                        </div>
                        <div class="kt-stat-block">
                            <span class="kt-stat-number">20+</span>
                            <span class="kt-stat-label">Happy Clients</span>
                        </div>
                        <div class="kt-stat-block">
                            <span class="kt-stat-number">3+</span>
                            <span class="kt-stat-label">Years of Experience</span>
                        </div>
                    </div>
                </div>

                <!-- About Right Image & Floating Cards -->
                <div class="kt-about-right">
                    <div class="kt-about-img-wrapper">
                        <img src="<?php echo $baseUrl; ?>assets/images/about-workspace.jpg" alt="KendatTech Team Workspace" class="kt-about-img">

                        <!-- Mission Floating Card -->
                        <div class="kt-mission-card top">
                            <div class="kt-card-icon-circle green">
                                <?php echo render_icon('Target', 18); ?>
                            </div>
                            <div>
                                <h4>Our Mission</h4>
                                <p>To empower businesses with innovative and reliable technology solutions that drive growth, efficiency and long-term success.</p>
                            </div>
                        </div>

                        <!-- Vision Floating Card -->
                        <div class="kt-mission-card bottom">
                            <div class="kt-card-icon-circle green">
                                <?php echo render_icon('Eye', 18); ?>
                            </div>
                            <div>
                                <h4>Our Vision</h4>
                                <p>To be a leading technology company known for excellence, innovation and customer satisfaction.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- 4. OUR SERVICES SECTION (3x2 Grid Matching Image Mockup) -->
    <section class="kt-services-section">
        <div class="kt-container">
            <div class="kt-section-head-row">
                <div>
                    <span class="kt-eyebrow">OUR SERVICES</span>
                    <h2 class="kt-section-title mb-2">What We Do</h2>
                    <p class="kt-section-sub">We provide a wide range of technology services to help you build, scale and succeed in the digital era.</p>
                </div>
                <a href="<?php echo $baseUrl; ?>services.php" class="kt-link-arrow">
                    View All Services <?php echo render_icon('ArrowRight', 16); ?>
                </a>
            </div>

            <div class="kt-services-grid">
                <!-- Card 1 -->
                <div class="kt-service-card horiz">
                    <div class="kt-service-icon-box"><?php echo render_icon('Code', 22); ?></div>
                    <div class="kt-service-card-info">
                        <h3>Web Development</h3>
                        <p>Modern, responsive websites and web applications.</p>
                    </div>
                    <a href="<?php echo $baseUrl; ?>services.php" class="kt-card-arrow-btn" aria-label="Web Development Details">
                        <?php echo render_icon('ArrowRight', 16); ?>
                    </a>
                </div>

                <!-- Card 2 -->
                <div class="kt-service-card horiz">
                    <div class="kt-service-icon-box"><?php echo render_icon('Smartphone', 22); ?></div>
                    <div class="kt-service-card-info">
                        <h3>Mobile App Development</h3>
                        <p>Powerful and user-friendly mobile applications.</p>
                    </div>
                    <a href="<?php echo $baseUrl; ?>services.php" class="kt-card-arrow-btn" aria-label="Mobile App Development Details">
                        <?php echo render_icon('ArrowRight', 16); ?>
                    </a>
                </div>

                <!-- Card 3 -->
                <div class="kt-service-card horiz">
                    <div class="kt-service-icon-box"><?php echo render_icon('Cloud', 22); ?></div>
                    <div class="kt-service-card-info">
                        <h3>Cloud Solutions</h3>
                        <p>Secure, scalable and cost-effective cloud infrastructure.</p>
                    </div>
                    <a href="<?php echo $baseUrl; ?>services.php" class="kt-card-arrow-btn" aria-label="Cloud Solutions Details">
                        <?php echo render_icon('ArrowRight', 16); ?>
                    </a>
                </div>

                <!-- Card 4 -->
                <div class="kt-service-card horiz">
                    <div class="kt-service-icon-box"><?php echo render_icon('Settings', 22); ?></div>
                    <div class="kt-service-card-info">
                        <h3>IT Consulting</h3>
                        <p>Strategic technology advice to fuel your growth.</p>
                    </div>
                    <a href="<?php echo $baseUrl; ?>services.php" class="kt-card-arrow-btn" aria-label="IT Consulting Details">
                        <?php echo render_icon('ArrowRight', 16); ?>
                    </a>
                </div>

                <!-- Card 5 -->
                <div class="kt-service-card horiz">
                    <div class="kt-service-icon-box"><?php echo render_icon('ShieldCheck', 22); ?></div>
                    <div class="kt-service-card-info">
                        <h3>Cybersecurity</h3>
                        <p>Protecting your data and business always.</p>
                    </div>
                    <a href="<?php echo $baseUrl; ?>services.php" class="kt-card-arrow-btn" aria-label="Cybersecurity Details">
                        <?php echo render_icon('ArrowRight', 16); ?>
                    </a>
                </div>

                <!-- Card 6 -->
                <div class="kt-service-card horiz">
                    <div class="kt-service-icon-box"><?php echo render_icon('Layers3', 22); ?></div>
                    <div class="kt-service-card-info">
                        <h3>Custom Software</h3>
                        <p>Tailored solutions for your unique needs.</p>
                    </div>
                    <a href="<?php echo $baseUrl; ?>services.php" class="kt-card-arrow-btn" aria-label="Custom Software Details">
                        <?php echo render_icon('ArrowRight', 16); ?>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- 5. FEATURED PROJECTS PORTFOLIO SECTION -->
    <section class="kt-projects-section">
        <div class="kt-container">
            <div class="kt-section-head-row">
                <div>
                    <span class="kt-eyebrow">FEATURED PROJECTS</span>
                    <h2 class="kt-section-title mb-2">Projects We're Proud Of</h2>
                    <p class="kt-section-sub">Explore custom web engineering, portal systems, and enterprise software built by KendatTech.</p>
                </div>
                <a href="<?php echo $baseUrl; ?>projects.php" class="kt-link-arrow">
                    Explore All Projects <?php echo render_icon('ArrowRight', 16); ?>
                </a>
            </div>

            <div class="kt-projects-grid">
                <?php foreach ($projects as $pObj): 
                    $pImg = upload_asset_url($pObj['main_image'] ?? '');
                ?>
                    <article class="kt-project-card">
                        <a href="<?php echo $baseUrl; ?>project-details.php?slug=<?php echo urlencode($pObj['slug']); ?>" class="kt-project-media">
                            <?php if (!empty($pImg)): ?>
                                <img src="<?php echo htmlspecialchars($pImg); ?>" alt="<?php echo htmlspecialchars($pObj['title']); ?>" loading="lazy">
                            <?php else: ?>
                                <div class="kt-project-fallback-icon"><?php echo render_icon('Globe', 48); ?></div>
                            <?php endif; ?>
                            <span class="kt-project-category"><?php echo htmlspecialchars($pObj['category']); ?></span>
                        </a>
                        <div class="kt-project-body">
                            <h3 class="kt-project-title">
                                <a href="<?php echo $baseUrl; ?>project-details.php?slug=<?php echo urlencode($pObj['slug']); ?>">
                                    <?php echo htmlspecialchars($pObj['title']); ?>
                                </a>
                            </h3>
                            <p class="kt-project-desc">
                                <?php echo htmlspecialchars(safe_str_trim((string)($pObj['short_description'] ?? 'Custom enterprise software solution.'), 120, '...')); ?>
                            </p>
                            <a href="<?php echo $baseUrl; ?>project-details.php?slug=<?php echo urlencode($pObj['slug']); ?>" class="kt-project-link">
                                View Case Study <?php echo render_icon('ArrowRight', 16); ?>
                            </a>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- 6. TESTIMONIALS SECTION (Matching Attached Image Mockup Exactly - 2 Column Split Layout) -->
    <section class="kt-testimonials-split-section">
        <div class="kt-container">
            <div class="kt-testimonials-split-grid">
                <!-- Left Column: Eyebrow, Title & Subtitle -->
                <div class="kt-testimonials-left">
                    <span class="kt-eyebrow">TESTIMONIALS</span>
                    <h2 class="kt-section-title">What Our Clients Say</h2>
                    <p class="kt-section-sub">
                        We take pride in the trust our clients place in us. Here's what they have to say about working with KendatTech.
                    </p>
                </div>

                <!-- Right Column: 3 Side-by-Side Cards -->
                <div class="kt-testimonials-right-cards">
                    <?php foreach ($testimonials as $t): 
                        $nameParts = explode(' ', (string)($t['client_name'] ?? 'Client'));
                        $initialsStr = '';
                        foreach ($nameParts as $nP) {
                            if (!empty($nP)) {
                                $initialsStr .= strtoupper($nP[0]);
                            }
                        }
                        $initials = !empty($initialsStr) ? substr($initialsStr, 0, 2) : 'KT';
                    ?>
                        <article class="kt-testimonial-card-mini">
                            <p class="kt-quote-text">"<?php echo htmlspecialchars($t['message']); ?>"</p>
                            <div class="kt-client-row">
                                <div class="kt-client-avatar">
                                    <?php echo htmlspecialchars(substr($initials, 0, 2)); ?>
                                </div>
                                <div class="kt-client-info">
                                    <h4><?php echo htmlspecialchars($t['client_name']); ?></h4>
                                    <small><?php echo htmlspecialchars($t['position_company'] ?? 'Executive'); ?></small>
                                    <div class="kt-stars">★★★★★</div>
                                </div>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </section>

    <!-- 7. CTA BANNER SECTION (Matching Image Green Card & White Pill Button) -->
    <section class="kt-cta-section">
        <div class="kt-container">
            <div class="kt-cta-card">
                <div class="kt-cta-content">
                    <span class="kt-cta-eyebrow">READY TO GET STARTED?</span>
                    <h2 class="kt-cta-title">Let's Build Something Great Together</h2>
                    <p class="kt-cta-sub">Partner with KendatTech and turn your ideas into powerful digital solutions.</p>
                </div>
                <div class="kt-cta-action">
                    <a href="<?php echo $baseUrl; ?>book-appointment.php" class="kt-btn-white-pill">
                        Get Started <?php echo render_icon('ArrowRight', 16); ?>
                    </a>
                </div>
            </div>
        </div>
    </section>

</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
