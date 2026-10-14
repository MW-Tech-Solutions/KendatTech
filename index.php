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

try {
    $heroSlidesStmt = $pdo->query("SELECT * FROM hero_slides WHERE status = 'active' ORDER BY sort_order ASC, id ASC");
    $heroSlides = $heroSlidesStmt->fetchAll();
} catch (Throwable $e) {
    $heroSlides = [];
}

if (empty($heroSlides)) {
    $heroSlides = [
        [
            'id' => 1,
            'badge_text' => 'INNOVATIVE TECHNOLOGY SOLUTIONS',
            'title' => 'Build Smarter With KendatTech',
            'highlight_text' => 'KendatTech',
            'description' => 'We design, develop and deliver modern software solutions that help businesses grow, operate efficiently and stay ahead in a digital world.',
            'primary_cta_text' => 'Our Services',
            'primary_cta_url' => 'services.php',
            'secondary_cta_text' => 'Learn More',
            'secondary_cta_url' => 'about.php',
            'slide_image' => 'assets/images/hero-laptop.png',
            'badge1_icon' => 'Code2',
            'badge1_title' => 'Clean Code',
            'badge1_subtitle' => 'Scalable & Maintainable',
            'badge2_icon' => 'ShieldCheck',
            'badge2_title' => 'Reliable Security',
            'badge2_subtitle' => 'Your Data, Our Priority',
            'tech_tags' => 'PHP, React, MySQL, REST APIs'
        ],
        [
            'id' => 2,
            'badge_text' => 'FULL-STACK SOFTWARE ENGINEERING',
            'title' => 'Enterprise Web & Mobile App Development',
            'highlight_text' => 'Enterprise Web & Mobile',
            'description' => 'End-to-end custom software engineering with reactive frontends, resilient databases, and automated business workflows built to scale seamlessly.',
            'primary_cta_text' => 'Request a Project',
            'primary_cta_url' => 'request-project.php',
            'secondary_cta_text' => 'View Projects',
            'secondary_cta_url' => 'projects.php',
            'slide_image' => 'assets/images/services-hero-laptop.jpg',
            'badge1_icon' => 'Smartphone',
            'badge1_title' => 'Cross-Platform',
            'badge1_subtitle' => 'iOS, Android & Web',
            'badge2_icon' => 'Rocket',
            'badge2_title' => 'Blazing Fast',
            'badge2_subtitle' => 'High Performance Core',
            'tech_tags' => 'React, Next.js, Laravel, Flutter'
        ],
        [
            'id' => 3,
            'badge_text' => 'CLOUD ARCHITECTURE & MICROSERVICES',
            'title' => 'Scalable Cloud Systems & High-Speed APIs',
            'highlight_text' => 'Cloud Systems & APIs',
            'description' => 'Architecting resilient cloud backends, optimized databases, and bulletproof microservices capable of processing millions of concurrent transactions.',
            'primary_cta_text' => 'Book Consultation',
            'primary_cta_url' => 'book-appointment.php',
            'secondary_cta_text' => 'Explore Services',
            'secondary_cta_url' => 'services.php',
            'slide_image' => 'assets/images/about-workspace.jpg',
            'badge1_icon' => 'Cloud',
            'badge1_title' => 'Cloud Native',
            'badge1_subtitle' => '99.99% Guaranteed Uptime',
            'badge2_icon' => 'Database',
            'badge2_title' => 'Optimized SQL',
            'badge2_subtitle' => 'High-Throughput Engines',
            'tech_tags' => 'AWS, Docker, Microservices, CI/CD'
        ],
        [
            'id' => 4,
            'badge_text' => 'AI & INTELLIGENT AUTOMATION',
            'title' => 'AI-Powered Systems & Smart Business Automation',
            'highlight_text' => 'AI-Powered Systems',
            'description' => 'Empower your applications with artificial intelligence, machine learning forecasting, and automated decision bots tailored to accelerate operations.',
            'primary_cta_text' => 'AI Solutions',
            'primary_cta_url' => 'ai-solutions.php',
            'secondary_cta_text' => 'Contact Us',
            'secondary_cta_url' => 'contact.php',
            'slide_image' => 'assets/images/ai-hero-visual.png',
            'badge1_icon' => 'Cpu',
            'badge1_title' => 'Intelligent AI',
            'badge1_subtitle' => 'NLP & Machine Learning',
            'badge2_icon' => 'Bot',
            'badge2_title' => 'Smart Automation',
            'badge2_subtitle' => '24/7 Workflow Processing',
            'tech_tags' => 'Python, OpenAI, PyTorch, Automation'
        ]
    ];
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

    <!-- 1. DYNAMIC HERO CAROUSEL WITH CINEMATIC VIDEO BACKGROUND WALLPAPER -->
    <section class="kt-hero-stage kt-hero-with-video" id="heroStage">
        <!-- Background Video Wallpaper (Continuously Active & Cinematic) -->
        <div class="kt-hero-video-bg" aria-hidden="true">
            <video autoplay muted loop playsinline preload="auto" poster="<?php echo $baseUrl; ?>assets/images/hero-video-poster.jpg" class="kt-hero-bg-video" id="ktHeroBgVideo">
                <source src="<?php echo $baseUrl; ?>assets/videos/hero-background.mp4" type="video/mp4">
            </video>
            <!-- High-Contrast Enterprise Gradient Overlay -->
            <div class="kt-hero-video-overlay"></div>
        </div>

        <div class="kt-container" style="position: relative; z-index: 3;">
            <div class="kt-hero-carousel-container" id="ktHeroCarousel">

                <!-- Previous & Next Arrow Controls -->
                <button type="button" class="kt-hero-nav-arrow prev" id="ktHeroPrev" aria-label="Previous Slide">
                    <?php echo render_icon('ChevronLeft', 24); ?>
                </button>
                <button type="button" class="kt-hero-nav-arrow next" id="ktHeroNext" aria-label="Next Slide">
                    <?php echo render_icon('ChevronRight', 24); ?>
                </button>

                <!-- Slides Viewport -->
                <div class="kt-hero-slides-wrapper">
                    <?php 
                    $slideCount = count($heroSlides);
                    foreach ($heroSlides as $idx => $slide): 
                        $isActive = ($idx === 0);
                        
                        // Highlight text replacement with brand accent
                        $titleRaw = $slide['title'];
                        $highlight = trim((string)($slide['highlight_text'] ?? ''));
                        if ($highlight !== '' && stripos($titleRaw, $highlight) !== false) {
                            $quoted = preg_quote($highlight, '/');
                            $formattedTitle = preg_replace('/(' . $quoted . ')/i', '<span class="kt-text-brand">$1</span>', htmlspecialchars($titleRaw));
                        } else {
                            $formattedTitle = htmlspecialchars($titleRaw);
                        }

                        // Tech tags
                        $techTags = array_filter(array_map('trim', explode(',', (string)($slide['tech_tags'] ?? ''))));
                        
                        // URLs
                        $pUrl = !empty($slide['primary_cta_url']) ? $slide['primary_cta_url'] : 'services.php';
                        if (!str_starts_with($pUrl, 'http://') && !str_starts_with($pUrl, 'https://') && !str_starts_with($pUrl, '#')) {
                            $pUrl = $baseUrl . ltrim($pUrl, '/');
                        }
                        $sUrl = !empty($slide['secondary_cta_url']) ? $slide['secondary_cta_url'] : 'about.php';
                        if (!str_starts_with($sUrl, 'http://') && !str_starts_with($sUrl, 'https://') && !str_starts_with($sUrl, '#')) {
                            $sUrl = $baseUrl . ltrim($sUrl, '/');
                        }
                    ?>
                        <div class="kt-hero-carousel-slide <?php echo $isActive ? 'active' : ''; ?>" data-slide-index="<?php echo $idx; ?>">
                            <div class="kt-hero-grid">
                                <!-- Hero Left Column: Copy & Actions -->
                                <div class="kt-hero-left">
                                    <span class="kt-eyebrow">
                                        <?php echo htmlspecialchars($slide['badge_text']); ?>
                                    </span>

                                    <h1 class="kt-hero-title">
                                        <?php echo $formattedTitle; ?>
                                    </h1>

                                    <p class="kt-hero-lead">
                                        <?php echo htmlspecialchars($slide['description']); ?>
                                    </p>

                                    <?php if (!empty($techTags)): ?>
                                        <div class="kt-hero-tech-chips">
                                            <?php foreach ($techTags as $tag): ?>
                                                <span class="kt-hero-tech-chip">
                                                    <?php echo render_icon('Code2', 13); ?>
                                                    <span><?php echo htmlspecialchars($tag); ?></span>
                                                </span>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>

                                    <div class="kt-hero-ctas">
                                        <a href="<?php echo htmlspecialchars($pUrl); ?>" class="kt-btn-primary">
                                            <?php echo htmlspecialchars($slide['primary_cta_text'] ?? 'Our Services'); ?> <?php echo render_icon('ArrowRight', 16); ?>
                                        </a>
                                        <a href="<?php echo htmlspecialchars($sUrl); ?>" class="kt-btn-outline">
                                            <?php echo htmlspecialchars($slide['secondary_cta_text'] ?? 'Learn More'); ?>
                                        </a>
                                    </div>
                                </div>

                                <!-- Center Connection Node Axis -->
                                <div class="kt-hero-center-axis" aria-hidden="true">
                                    <div class="kt-hero-axis-dot"></div>
                                </div>

                                <!-- Hero Right Column: Graphic Showcase & Floating Badges -->
                                <div class="kt-hero-right">
                                    <div class="kt-hero-showcase-stage">
                                        <!-- Angled Brand Geometric Polygons -->
                                        <div class="kt-hero-poly-green" aria-hidden="true"></div>
                                        <div class="kt-hero-poly-amber" aria-hidden="true"></div>

                                        <!-- Showcase Card -->
                                        <div class="kt-hero-showcase-card">
                                            <img src="<?php echo htmlspecialchars(upload_asset_url($slide['slide_image'])); ?>" 
                                                 alt="<?php echo htmlspecialchars($slide['title']); ?>" 
                                                 class="kt-hero-showcase-img">

                                            <!-- Floating Badge 1 (Top-Left) -->
                                            <div class="kt-hero-float-badge badge-top-left">
                                                <div class="kt-hero-float-icon">
                                                    <?php 
                                                    $b1Icon = $slide['badge1_icon'] ?? 'Code2';
                                                    if ($b1Icon === 'Code2') {
                                                        echo '&lt;/&gt;';
                                                    } else {
                                                        echo render_icon($b1Icon, 18);
                                                    }
                                                    ?>
                                                </div>
                                                <div class="kt-hero-float-meta">
                                                    <strong><?php echo htmlspecialchars($slide['badge1_title'] ?? 'Clean Code'); ?></strong>
                                                    <span><?php echo htmlspecialchars($slide['badge1_subtitle'] ?? 'Scalable & Maintainable'); ?></span>
                                                </div>
                                            </div>

                                            <!-- Floating Badge 2 (Bottom-Right) -->
                                            <div class="kt-hero-float-badge badge-bottom-right">
                                                <div class="kt-hero-float-icon" style="background:rgba(11,93,59,0.12); color:#0B5D3B;">
                                                    <?php echo render_icon($slide['badge2_icon'] ?? 'ShieldCheck', 18); ?>
                                                </div>
                                                <div class="kt-hero-float-meta">
                                                    <strong><?php echo htmlspecialchars($slide['badge2_title'] ?? 'Reliable Security'); ?></strong>
                                                    <span><?php echo htmlspecialchars($slide['badge2_subtitle'] ?? 'Your Data, Our Priority'); ?></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Bottom Carousel Indicators Dock -->
                <div class="kt-hero-bottom-dock">
                    <div class="kt-hero-dock-pill">
                        <span class="kt-hero-counter-label" id="ktHeroCounter">
                            01 / <?php echo sprintf('%02d', $slideCount); ?>
                        </span>
                        
                        <div class="kt-hero-dots-wrap" role="tablist" aria-label="Hero Carousel Slides">
                            <?php for ($i = 0; $i < $slideCount; $i++): ?>
                                <button type="button" 
                                        class="kt-hero-dot-btn <?php echo $i === 0 ? 'active' : ''; ?>" 
                                        data-slide-to="<?php echo $i; ?>" 
                                        aria-label="Go to slide <?php echo $i + 1; ?>">
                                </button>
                            <?php endfor; ?>
                        </div>

                        <button type="button" class="kt-hero-pause-btn" id="ktHeroPause" title="Pause / Resume Rotation" aria-label="Pause Auto Rotation">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="6" y="4" width="4" height="16" rx="1"></rect>
                                <rect x="14" y="4" width="4" height="16" rx="1"></rect>
                            </svg>
                        </button>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- Carousel Vanilla Controller Script -->
    <script>
    (function() {
        function initHeroCarousel() {
            const carousel = document.getElementById('ktHeroCarousel');
            if (!carousel) return;

            const slides = carousel.querySelectorAll('.kt-hero-carousel-slide');
            const dots = carousel.querySelectorAll('.kt-hero-dot-btn');
            const prevBtn = document.getElementById('ktHeroPrev');
            const nextBtn = document.getElementById('ktHeroNext');
            const counter = document.getElementById('ktHeroCounter');
            const pauseBtn = document.getElementById('ktHeroPause');
            
            if (slides.length <= 1) return;

            let currentIndex = 0;
            let isPaused = false;
            let autoInterval = null;
            const slideDuration = 5500;

            function formatNumber(num) {
                return num < 10 ? '0' + num : num;
            }

            function goToSlide(index) {
                slides[currentIndex].classList.remove('active');
                if (dots[currentIndex]) dots[currentIndex].classList.remove('active');

                currentIndex = (index + slides.length) % slides.length;

                slides[currentIndex].classList.add('active');
                if (dots[currentIndex]) dots[currentIndex].classList.add('active');

                if (counter) {
                    counter.textContent = `${formatNumber(currentIndex + 1)} / ${formatNumber(slides.length)}`;
                }
            }

            function nextSlide() {
                goToSlide(currentIndex + 1);
            }

            function prevSlide() {
                goToSlide(currentIndex - 1);
            }

            function startAutoPlay() {
                stopAutoPlay();
                autoInterval = setInterval(() => {
                    if (!isPaused) {
                        nextSlide();
                    }
                }, slideDuration);
            }

            function stopAutoPlay() {
                if (autoInterval) {
                    clearInterval(autoInterval);
                    autoInterval = null;
                }
            }

            if (nextBtn) {
                nextBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    nextSlide();
                    startAutoPlay();
                });
            }

            if (prevBtn) {
                prevBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    prevSlide();
                    startAutoPlay();
                });
            }

            dots.forEach(function(dot, idx) {
                dot.addEventListener('click', function(e) {
                    e.preventDefault();
                    goToSlide(idx);
                    startAutoPlay();
                });
            });

            if (pauseBtn) {
                pauseBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    isPaused = !isPaused;
                    pauseBtn.setAttribute('aria-label', isPaused ? 'Resume auto slide' : 'Pause auto slide');
                    pauseBtn.style.opacity = isPaused ? '0.4' : '1';
                });
            }

            carousel.addEventListener('mouseenter', function() { isPaused = true; });
            carousel.addEventListener('mouseleave', function() { isPaused = false; });

            // Keyboard navigation
            document.addEventListener('keydown', function(e) {
                if (e.target && (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA')) return;
                if (e.key === 'ArrowLeft') { prevSlide(); startAutoPlay(); }
                else if (e.key === 'ArrowRight') { nextSlide(); startAutoPlay(); }
            });

            // Touch swipe support
            let touchStartX = 0;
            carousel.addEventListener('touchstart', function(e) {
                touchStartX = e.changedTouches[0].clientX;
                isPaused = true;
            }, { passive: true });

            carousel.addEventListener('touchend', function(e) {
                const touchEndX = e.changedTouches[0].clientX;
                const diffX = touchEndX - touchStartX;
                if (Math.abs(diffX) > 40) {
                    if (diffX < 0) {
                        nextSlide();
                    } else {
                        prevSlide();
                    }
                }
                isPaused = false;
                startAutoPlay();
            }, { passive: true });

            startAutoPlay();
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initHeroCarousel);
        } else {
            initHeroCarousel();
        }
    })();
    </script>

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

                <!-- Right Column: Cards (Desktop: 3-Col Grid, Mobile: Seamless Infinite Marquee) -->
                <div class="kt-testimonials-marquee-wrapper" id="ktTestimonialsMarqueeWrapper">
                    <div class="kt-testimonials-right-cards kt-testimonials-marquee-track" id="ktTestimonialsMarqueeTrack">
                        <?php 
                        // Set 1: Primary Cards
                        foreach ($testimonials as $t): 
                            $nameParts = explode(' ', (string)($t['client_name'] ?? 'Client'));
                            $initialsStr = '';
                            foreach ($nameParts as $nP) {
                                if (!empty($nP)) {
                                    $initialsStr .= strtoupper($nP[0]);
                                }
                            }
                            $initials = !empty($initialsStr) ? substr($initialsStr, 0, 2) : 'KT';
                            $quote = !empty($t['message']) ? $t['message'] : (!empty($t['content']) ? $t['content'] : (!empty($t['testimonial']) ? $t['testimonial'] : 'KendatTech delivered our project on time and exceeded our expectations. Highly recommended!'));
                        ?>
                            <article class="kt-testimonial-card-mini">
                                <div class="kt-stars">★★★★★</div>
                                <p class="kt-quote-text">"<?php echo htmlspecialchars($quote); ?>"</p>
                                <div class="kt-client-row">
                                    <div class="kt-client-avatar">
                                        <?php echo htmlspecialchars(substr($initials, 0, 2)); ?>
                                    </div>
                                    <div class="kt-client-info">
                                        <h4><?php echo htmlspecialchars($t['client_name'] ?? 'Client'); ?></h4>
                                        <small><?php echo htmlspecialchars($t['position_company'] ?? 'Verified Client'); ?></small>
                                    </div>
                                </div>
                            </article>
                        <?php endforeach; ?>

                        <?php 
                        // Set 2: Duplicate Set for Continuous Seamless Marquee on Mobile
                        foreach ($testimonials as $t): 
                            $nameParts = explode(' ', (string)($t['client_name'] ?? 'Client'));
                            $initialsStr = '';
                            foreach ($nameParts as $nP) {
                                if (!empty($nP)) {
                                    $initialsStr .= strtoupper($nP[0]);
                                }
                            }
                            $initials = !empty($initialsStr) ? substr($initialsStr, 0, 2) : 'KT';
                            $quote = !empty($t['message']) ? $t['message'] : (!empty($t['content']) ? $t['content'] : (!empty($t['testimonial']) ? $t['testimonial'] : 'KendatTech delivered our project on time and exceeded our expectations. Highly recommended!'));
                        ?>
                            <article class="kt-testimonial-card-mini marquee-dup">
                                <div class="kt-stars">★★★★★</div>
                                <p class="kt-quote-text">"<?php echo htmlspecialchars($quote); ?>"</p>
                                <div class="kt-client-row">
                                    <div class="kt-client-avatar">
                                        <?php echo htmlspecialchars(substr($initials, 0, 2)); ?>
                                    </div>
                                    <div class="kt-client-info">
                                        <h4><?php echo htmlspecialchars($t['client_name'] ?? 'Client'); ?></h4>
                                        <small><?php echo htmlspecialchars($t['position_company'] ?? 'Verified Client'); ?></small>
                                    </div>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
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
