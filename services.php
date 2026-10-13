<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/header.php';

$pdo = get_db();
try {
    $servicesStmt = $pdo->query("SELECT * FROM services WHERE status = 'active' ORDER BY sort_order ASC, id ASC");
    $services = $servicesStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
    $services = [];
}

// Fallback to exact 8 mockup services if database is unavailable
if (empty($services)) {
    $services = [
        [
            'id' => 1,
            'title' => 'Custom Software Development',
            'short_description' => 'Tailored web and mobile applications that solve real business problems and drive growth.',
            'icon' => 'Code'
        ],
        [
            'id' => 2,
            'title' => 'Web Development',
            'short_description' => 'Modern, responsive and scalable websites that deliver great user experiences and results.',
            'icon' => 'Globe'
        ],
        [
            'id' => 3,
            'title' => 'Mobile App Development',
            'short_description' => 'High-performance Android and iOS apps built for speed, security and scalability.',
            'icon' => 'Smartphone'
        ],
        [
            'id' => 4,
            'title' => 'AI Solutions',
            'short_description' => 'Smart AI-powered systems to automate processes, enhance decisions and unlock new opportunities.',
            'icon' => 'Cpu'
        ],
        [
            'id' => 5,
            'title' => 'Cloud Solutions',
            'short_description' => 'Secure, scalable and cost-effective cloud infrastructure for your business needs.',
            'icon' => 'Cloud'
        ],
        [
            'id' => 6,
            'title' => 'IT Consulting & Support',
            'short_description' => 'Expert guidance and ongoing support to keep your systems running smoothly and efficiently.',
            'icon' => 'ShieldCheck'
        ],
        [
            'id' => 7,
            'title' => 'Database Management',
            'short_description' => 'Reliable and optimized database solutions for better performance and security.',
            'icon' => 'Database'
        ],
        [
            'id' => 8,
            'title' => 'System Integration',
            'short_description' => 'Seamless integration of your systems, tools and platforms for maximum efficiency.',
            'icon' => 'Settings'
        ]
    ];
}
?>

<main class="kt-main-wrapper">

    <!-- 1. HERO SECTION (Technology Solutions for a Smarter Future) -->
    <section class="kt-serv-hero">
        <div class="kt-container">
            <div class="kt-serv-hero-grid">
                <!-- Left Hero Content -->
                <div class="kt-serv-hero-content">
                    <div class="kt-serv-hero-eyebrow">
                        <span>OUR SERVICES</span>
                        <span class="eyebrow-line"></span>
                    </div>
                    <h1 class="kt-serv-hero-title">
                        Technology Solutions<br>
                        for a <span class="kt-text-mint">Smarter Future</span>
                    </h1>
                    <p class="kt-serv-hero-desc">
                        We provide innovative and reliable technology solutions to help businesses, organizations and individuals achieve their goals, optimize operations and stay ahead in the digital world.
                    </p>
                    <div class="kt-serv-hero-actions">
                        <a href="<?php echo $baseUrl; ?>book-appointment.php" class="kt-serv-hero-btn-green">
                            Get Started <?php echo render_icon('ArrowRight', 16); ?>
                        </a>
                        <a href="<?php echo $baseUrl; ?>projects.php" class="kt-serv-hero-btn-outline">
                            Explore Our Projects
                        </a>
                    </div>
                </div>

                <!-- Right Hero Visual (3D Laptop with Badges & Geometric Shapes) -->
                <div class="kt-serv-hero-visual">
                    <img src="<?php echo $baseUrl; ?>assets/images/services-hero-laptop.jpg" alt="KendatTech - Better Solutions. A Smarter Tomorrow." class="kt-serv-laptop-img">
                </div>
            </div>
        </div>
    </section>

    <!-- 2. WHAT WE OFFER - OUR CORE SERVICES (8 Cards Grid) -->
    <section class="kt-serv-core-section">
        <div class="kt-container">
            <div class="kt-serv-section-header">
                <span class="kt-serv-eyebrow-center">WHAT WE OFFER</span>
                <h2 class="kt-serv-section-title">
                    Our Core <span class="kt-text-brand">Services</span>
                </h2>
                <p class="kt-serv-section-sub">
                    From custom software development to AI-powered solutions, we deliver end-to-end technology services designed to meet your unique needs.
                </p>
            </div>

            <div class="kt-serv-core-grid">
                <?php foreach ($services as $service): 
                    $rawIcon = $service['icon'] ?? 'Globe';
                    $iconName = 'Globe';
                    $titleLower = strtolower($service['title'] ?? '');
                    
                    if (str_contains($titleLower, 'software') || str_contains($titleLower, 'custom') || str_contains(strtolower($rawIcon), 'code')) {
                        $iconName = 'Code';
                    } elseif (str_contains($titleLower, 'web')) {
                        $iconName = 'Globe';
                    } elseif (str_contains($titleLower, 'mobile') || str_contains($titleLower, 'app')) {
                        $iconName = 'Smartphone';
                    } elseif (str_contains($titleLower, 'ai') || str_contains($titleLower, 'intelligence') || str_contains($titleLower, 'machine')) {
                        $iconName = 'Cpu';
                    } elseif (str_contains($titleLower, 'cloud')) {
                        $iconName = 'Cloud';
                    } elseif (str_contains($titleLower, 'consult') || str_contains($titleLower, 'support') || str_contains($titleLower, 'security')) {
                        $iconName = 'ShieldCheck';
                    } elseif (str_contains($titleLower, 'database') || str_contains($titleLower, 'data')) {
                        $iconName = 'Database';
                    } elseif (str_contains($titleLower, 'integration') || str_contains($titleLower, 'system')) {
                        $iconName = 'Settings';
                    } else {
                        $iconName = $rawIcon;
                    }
                ?>
                    <div class="kt-core-card">
                        <div class="kt-core-card-icon">
                            <?php echo render_icon($iconName, 22); ?>
                        </div>
                        <h3 class="kt-core-card-title"><?php echo htmlspecialchars($service['title']); ?></h3>
                        <p class="kt-core-card-desc"><?php echo htmlspecialchars($service['short_description']); ?></p>
                        <a href="<?php echo $baseUrl; ?>request-project.php?service=<?php echo urlencode($service['title']); ?>" class="kt-core-card-link">
                            Learn More <?php echo render_icon('ArrowRight', 15); ?>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- 3. WHY CHOOSE US - YOUR SUCCESS IS OUR PRIORITY -->
    <section class="kt-serv-why-section">
        <div class="kt-container">
            <div class="kt-serv-why-grid">
                <!-- Left Content & 4 Benefits -->
                <div class="kt-serv-why-left">
                    <div class="kt-serv-why-eyebrow">
                        <span>WHY CHOOSE US</span>
                        <span class="eyebrow-line"></span>
                    </div>
                    <h2 class="kt-serv-why-title">
                        Your Success is <span class="kt-text-brand">Our Priority</span>
                    </h2>
                    <p class="kt-serv-why-desc">
                        We combine technical expertise, creativity and a client-first approach to deliver solutions that make a real difference. Our team is committed to quality, innovation and long-term partnerships.
                    </p>

                    <div class="kt-serv-why-benefits">
                        <!-- Benefit 1: Experienced Team -->
                        <div class="kt-why-item">
                            <div class="kt-why-icon-badge">
                                <?php echo render_icon('UserRound', 22); ?>
                            </div>
                            <div class="kt-why-item-info">
                                <h4>Experienced Team</h4>
                                <p>Skilled professionals with proven track records.</p>
                            </div>
                        </div>

                        <!-- Benefit 2: On-Time Delivery -->
                        <div class="kt-why-item">
                            <div class="kt-why-icon-badge">
                                <?php echo render_icon('Clock', 22); ?>
                            </div>
                            <div class="kt-why-item-info">
                                <h4>On-Time Delivery</h4>
                                <p>We respect your time and deliver as promised.</p>
                            </div>
                        </div>

                        <!-- Benefit 3: Affordable Pricing -->
                        <div class="kt-why-item">
                            <div class="kt-why-icon-badge">
                                <?php echo render_icon('ShieldCheck', 22); ?>
                            </div>
                            <div class="kt-why-item-info">
                                <h4>Affordable Pricing</h4>
                                <p>Quality solutions that fit your budget.</p>
                            </div>
                        </div>

                        <!-- Benefit 4: Ongoing Support -->
                        <div class="kt-why-item">
                            <div class="kt-why-icon-badge">
                                <?php echo render_icon('Headset', 22); ?>
                            </div>
                            <div class="kt-why-item-info">
                                <h4>Ongoing Support</h4>
                                <p>We're with you even after delivery.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Visual Desk Composition -->
                <div class="kt-serv-why-right">
                    <div class="kt-why-visual-wrap">
                        <div class="kt-why-bg-pill"></div>
                        <div class="kt-why-bg-amber"></div>

                        <!-- Floating Innovate Build Grow Badge -->
                        <div class="kt-why-floating-badge">
                            <div class="kt-floating-badge-text">
                                <span>Innovate</span>
                                <span>Build</span>
                                <span>Grow</span>
                            </div>
                            <div class="kt-floating-badge-arrow">
                                <?php echo render_icon('ArrowRight', 16); ?>
                            </div>
                        </div>

                        <!-- Desk Scene Image -->
                        <img src="<?php echo $baseUrl; ?>assets/images/services-workspace.jpg" alt="KendatTech Modern Engineering Desk" class="kt-why-main-img">
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- 4. CTA BANNER (Ready to Build Your Next Big Idea?) -->
    <section class="kt-serv-cta-section">
        <div class="kt-container">
            <div class="kt-serv-cta-card">
                <div class="kt-cta-amber-accent"></div>
                <div class="kt-cta-left-content">
                    <div class="kt-cta-bulb-circle">
                        <?php echo render_icon('Lightbulb', 26); ?>
                    </div>
                    <div class="kt-cta-text">
                        <h3>Ready to Build Your Next Big Idea?</h3>
                        <p>Let's turn your vision into a powerful digital solution.</p>
                    </div>
                </div>
                <a href="<?php echo $baseUrl; ?>contact.php" class="kt-cta-btn-white">
                    Contact Us <?php echo render_icon('ArrowRight', 16); ?>
                </a>
            </div>
        </div>
    </section>

</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
