<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/header.php';

$pdo = get_db();

$servicesStmt = $pdo->query("SELECT * FROM services WHERE status = 'active' ORDER BY sort_order ASC, id DESC LIMIT 6");
$services = $servicesStmt->fetchAll();

$projectsStmt = $pdo->query("SELECT * FROM projects WHERE status = 'completed' OR status = 'ongoing' ORDER BY featured DESC, id DESC LIMIT 6");
$projects = $projectsStmt->fetchAll();

foreach ($projects as &$project) {
    $imgStmt = $pdo->prepare("SELECT image_path FROM project_images WHERE project_id = ? ORDER BY sort_order ASC");
    $imgStmt->execute([$project['id']]);
    $project['images'] = $imgStmt->fetchAll(PDO::FETCH_COLUMN);
}
unset($project);

$aiStmt = $pdo->query("SELECT * FROM ai_solutions WHERE status = 'active' ORDER BY sort_order ASC, id DESC LIMIT 6");
$aiSolutions = $aiStmt->fetchAll();

$testimonialsStmt = $pdo->query("SELECT * FROM testimonials WHERE status = 'active' ORDER BY id DESC LIMIT 6");
$testimonials = $testimonialsStmt->fetchAll();

$heroVideo = $baseUrl . 'assets/videos/developers_collaborating_hero.mp4';
?>

<main class="tc-main-container">

    <!-- Interface Section 1: Cuberto Wickret-Inspired Animated Hero Stage -->
    <section class="tc-hero-dark-stage wickret-hero-stage">
        <video class="tc-hero-bg-video" autoplay loop muted playsinline preload="auto" aria-hidden="true">
            <source src="<?php echo htmlspecialchars($heroVideo); ?>" type="video/mp4">
        </video>
        <div class="tc-hero-video-overlay"></div>

        <!-- Ambient Glowing Animated Orbs -->
        <div class="wickret-aurora-glow">
            <div class="wickret-orb wickret-orb-1"></div>
            <div class="wickret-orb wickret-orb-2"></div>
            <div class="wickret-orb wickret-orb-3"></div>
        </div>
                 
        <!-- Hero Content Center Stage -->
        <div class="wickret-hero-content">
            <div class="tc-hero-trust-pill wickret-trust-badge">
                <span class="tc-live-dot"></span>
                <span>KENDAT ENTERPRISE PLATFORM v4.2 • Trusted by <strong>120+ Institutions</strong></span>
            </div>
            
            <h1 class="tc-hero-title-main wickret-hero-title">
                The World’s Leading <span class="wickret-gradient-text">Enterprise Software & AI Platform</span>
            </h1>
            <p class="tc-hero-subtitle-main wickret-hero-sub">
                Build, scale, and secure your organization with custom web systems, mobile portals, school management platforms, and AI automation engines.
            </p>

            <div class="tc-hero-btn-row wickret-btn-row">
                <a class="tc-pill-btn-blue wickret-shimmer-btn" href="<?php echo $baseUrl; ?>book-appointment.php">
                    <?php echo render_icon('CalendarCheck', 20); ?>Book Strategy Session
                </a>
                <a class="tc-store-btn wickret-glass-btn" href="<?php echo $baseUrl; ?>projects.php">
                    <?php echo render_icon('PanelsTopLeft', 20); ?>
                    <div>
                        <small>Explore Live</small>
                        <strong>Portfolio</strong>
                    </div>
                </a>
                <a class="tc-store-btn wickret-glass-btn" href="<?php echo $baseUrl; ?>services.php">
                    <?php echo render_icon('Layers', 20); ?>
                    <div>
                        <small>View All</small>
                        <strong>Services</strong>
                    </div>
                </a>
            </div>

            <!-- Hero Glowing Search Console Bar with Pre-fill Chips -->
            <form class="tc-hero-search-floating wickret-search-console" action="<?php echo $baseUrl; ?>request-project.php" method="get">
                <span class="tc-search-prefix"><?php echo render_icon('Sparkles', 16); ?> KENDAT</span>
                <input class="tc-search-input wickret-search-input" id="heroSearchInput" name="project_type" placeholder="Search software requirement (e.g. School Portal, AI Assistant, Mobile App)..." required>
                <button class="tc-search-btn-icon wickret-search-btn" type="submit" aria-label="Search Requirements">
                    <?php echo render_icon('Search', 22); ?>
                </button>
            </form>

            <div class="wickret-quick-chips">
                <span>Quick Requirement Fill:</span>
                <button type="button" class="wickret-chip" data-fill="School Management Portal">School Portals</button>
                <button type="button" class="wickret-chip" data-fill="AI Assistant & Automation Engine">AI Assistants</button>
                <button type="button" class="wickret-chip" data-fill="Custom Enterprise ERP System">Custom ERP</button>
                <button type="button" class="wickret-chip" data-fill="Mobile Application Portal">Mobile Apps</button>
            </div>

            <!-- Hero Auto-Scrolling Horizontal Project Marquee Carousel (Astonesoft Style) -->
            <div class="hero-project-marquee-wrapper">
                <div class="hero-marquee-track">
                    <?php foreach ($projects as $pObj): 
                        $pImg = upload_asset_url($pObj['main_image']);
                    ?>
                        <a class="hero-marquee-card" href="<?php echo $baseUrl; ?>project-details.php?slug=<?php echo urlencode($pObj['slug']); ?>">
                            <div class="hero-marquee-img-wrap">
                                <?php if ($pImg): ?>
                                    <img src="<?php echo htmlspecialchars($pImg); ?>" alt="<?php echo htmlspecialchars($pObj['title']); ?>">
                                <?php else: ?>
                                    <div class="hero-marquee-fallback"><?php echo render_icon('Monitor', 24); ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="hero-marquee-info">
                                <span class="hero-marquee-cat"><?php echo htmlspecialchars($pObj['category']); ?></span>
                                <strong class="hero-marquee-title"><?php echo htmlspecialchars($pObj['title']); ?></strong>
                            </div>
                        </a>
                    <?php endforeach; ?>

                    <!-- Duplicate loop for seamless infinite marquee scroll -->
                    <?php foreach ($projects as $pObj): 
                        $pImg = upload_asset_url($pObj['main_image']);
                    ?>
                        <a class="hero-marquee-card" href="<?php echo $baseUrl; ?>project-details.php?slug=<?php echo urlencode($pObj['slug']); ?>">
                            <div class="hero-marquee-img-wrap">
                                <?php if ($pImg): ?>
                                    <img src="<?php echo htmlspecialchars($pImg); ?>" alt="<?php echo htmlspecialchars($pObj['title']); ?>">
                                <?php else: ?>
                                    <div class="hero-marquee-fallback"><?php echo render_icon('Monitor', 24); ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="hero-marquee-info">
                                <span class="hero-marquee-cat"><?php echo htmlspecialchars($pObj['category']); ?></span>
                                <strong class="hero-marquee-title"><?php echo htmlspecialchars($pObj['title']); ?></strong>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </section>






    <!-- Request 1 & 2: Full-Bleed 2x2 Feature Grid with Rich Graphics & High-Res Image -->
 <section class="tc-grid-2x2-section">
        <div class="tc-grid-2x2">
            <!-- Box 1: White Action Box -->
            <div class="tc-box-white">
                <span class="tc-tag-new">NEW</span>
                <h2 class="tc-box-title">Outdated Software? Protect your business with Kendat Tech.</h2>
                <p class="tc-box-desc">Not sure if your legacy web application, manual database, or current portal is vulnerable or slow? Kendat Tech’s software team instantly audits and upgrades your systems.</p>
                <a class="tc-pill-btn-blue" href="<?php echo $baseUrl; ?>request-project.php" style="width: max-content;">
                    Request Systems Audit <?php echo render_icon('ArrowRight', 18); ?>
                </a>
            </div>

            <!-- Box 2: Video Animation Stage (Exact Promo Video!) -->
            <div class="tc-box-blue video-box">
                <video class="tc-promo-video-player" autoplay loop muted playsinline preload="auto">
                    <source src="<?php echo $baseUrl; ?>assets/videos/kendat_promo.mp4" type="video/mp4">
                    Your browser does not support HTML5 video.
                </video>
                <!-- <div class="tc-video-overlay-badge">
                    <span class="tc-live-dot">LIVE ANIMATION</span>
                    <strong>Kendat Integrated Services</strong>
                </div> -->
            </div>

            <!-- Box 3: Software & Web Development Showcase Image (Engineering Office) -->
            <div class="tc-box-image-glass">
                <?php 
                    $webDevImg = upload_asset_url('projects/web_dev_team.jpg');
                ?>
                <img src="<?php echo htmlspecialchars($webDevImg); ?>" alt="Kendat Software Engineering Team" class="tc-full-crop-img">
                <div class="tc-glass-badge">
                    <?php echo render_icon('Code', 20); ?>
                    <span>Custom Web Engineering • High Speed & PDO Encrypted</span>
                </div>
            </div>

            <!-- Box 4: Software & Web Development Advertising Box (Task 2 Fix) -->
            <div class="tc-box-white">
                <span class="tc-kicker">SOFTWARE & WEB DEVELOPMENT</span>
                <h2 class="tc-box-title">Build Fast. Scale Nationwide. Custom Web & Software Systems.</h2>
                <p class="tc-box-desc">From high-speed institutional portals to custom web applications and ERP platforms, Kendat Integrated Services designs bespoke digital software engineered for high performance, top security, and seamless user experience.</p>
                <a class="tc-pill-btn-blue" href="<?php echo $baseUrl; ?>services.php" style="width: max-content;">
                    Explore Web & Software Solutions <?php echo render_icon('ArrowRight', 18); ?>
                </a>
            </div>
        </div>
    </section>

    <!-- Asymmetric AI Automations & Data Analytics Advertising Banner (Task 3 Fix) -->
    <section class="tc-asymmetric-section">
        <div class="tc-asymmetric-banner">
            <!-- Left Side: AI Ad Copy -->
            <div class="tc-banner-left">
                <span class="tc-kicker">NEXT-GEN AI AUTOMATION & ANALYTICS</span>
                <h2 class="tc-box-title">Transform Enterprise Operations with AI Copilots & Data Modeling.</h2>
                <p class="tc-box-desc">Automate complex manual workflows, process large enterprise datasets in real-time, and deploy specialized AI models trained specifically for your business intelligence.</p>
                <a class="tc-pill-btn-blue" href="<?php echo $baseUrl; ?>ai-solutions.php" style="width: max-content;">
                    Deploy AI Solutions <?php echo render_icon('Zap', 18); ?>
                </a>
            </div>

            <!-- Right Side: AI Neural Engine Stage -->
            <div class="tc-banner-right tc-ai-stage-bg">
                <div class="tc-ai-neural-card">
                    <div class="tc-ai-card-header">
                        <span class="tc-ai-live-badge">● AI NEURAL PIPELINE</span>
                        <strong>125,000 Records / sec</strong>
                    </div>
                    <div class="tc-ai-card-body">
                        <div class="tc-ai-metric-row">
                            <small>MODEL ENGINE</small>
                            <strong>Kendat Enterprise LLM v4.2</strong>
                        </div>
                        <div class="tc-ai-metric-row">
                            <small>WORKFLOW AUTOMATION</small>
                            <span class="tc-progress-bar"><span class="tc-progress-fill" style="width: 99.4%;"></span></span>
                            <strong class="c-green">99.4% Automated</strong>
                        </div>
                        <div class="tc-ai-chips-row">
                            <span class="tc-ai-chip"><?php echo render_icon('Cpu', 14); ?> Neural Search</span>
                            <span class="tc-ai-chip"><?php echo render_icon('BarChart3', 14); ?> Predictive Analytics</span>
                            <span class="tc-ai-chip"><?php echo render_icon('Bot', 14); ?> Custom AI Agents</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    




    <!-- Request 3: Completely Redesigned Premium Spotlight (Request 3 Fix) -->
    <section class="tc-premium-spotlight-section">
        <div class="tc-section-header centered">
            <span class="tc-tag-new">ENTERPRISE ENGINE</span>
            <h2 class="tc-section-title">Go beyond basic software. Go Enterprise.</h2>
            <p class="tc-section-desc">If you love reliable performance, you'll also love automated AI reporting, role-based access control, and 24/7 dedicated engineering support.</p>
        </div>

        <div class="tc-premium-showcase-container">
            <div class="tc-device-browser-window">
                <div class="tc-window-header">
                    <span class="tc-window-dot red"></span>
                    <span class="tc-window-dot yellow"></span>
                    <span class="tc-window-dot green"></span>
                    <div class="tc-window-address"><?php echo htmlspecialchars($baseUrl); ?>dashboard.php</div>
                </div>
                <div class="tc-window-body">
                    <div class="tc-window-grid">
                        <div class="tc-window-card">
                            <?php echo render_icon('ShieldCheck', 28); ?>
                            <h4>High Security PDO</h4>
                            <p>Prepared SQL queries protecting database integrity against injection.</p>
                        </div>
                        <div class="tc-window-card">
                            <?php echo render_icon('Bot', 28); ?>
                            <h4>AI Copilot Engine</h4>
                            <p>Automated reporting, student result summaries, and intelligent search.</p>
                        </div>
                        <div class="tc-window-card">
                            <?php echo render_icon('Zap', 28); ?>
                            <h4>High-Speed Engine</h4>
                            <p>Optimized MySQL query pipelines delivering responses under 50ms.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Astonesoft-Style "Projects We're Proud Of" / Portfolio Section -->
    <section class="astone-projects-section" id="portfolio">
        <div class="astone-section-head">
            <span class="astone-kicker">FEATURED WORK</span>
            <h2 class="astone-title">Projects We're Proud Of.</h2>
            <p class="astone-desc">Trust your digital infrastructure with Kendat Tech. We blend custom web engineering, AI copilots, and enterprise database architecture to craft software systems that perform nationwide.</p>
        </div>

        <div class="astone-projects-grid">
            <?php foreach ($projects as $index => $pObj): 
                $pImg = upload_asset_url($pObj['main_image']);
                $projectDemoLink = trim((string)($pObj['demo_link'] ?? ''));
            ?>
                <article class="astone-project-card">
                    <a class="astone-project-media-wrap" href="<?php echo $baseUrl; ?>project-details.php?slug=<?php echo urlencode($pObj['slug']); ?>">
                        <?php if (!empty($pImg)): ?>
                            <img src="<?php echo htmlspecialchars($pImg); ?>" alt="<?php echo htmlspecialchars($pObj['title']); ?>" loading="lazy">
                        <?php else: ?>
                            <div class="astone-media-fallback">
                                <?php echo render_icon('Monitor', 48); ?>
                                <strong><?php echo htmlspecialchars($pObj['title']); ?></strong>
                            </div>
                        <?php endif; ?>
                        <div class="astone-hover-overlay">
                            <span class="astone-hover-btn">View Case Study <?php echo render_icon('ArrowUpRight', 18); ?></span>
                        </div>
                    </a>

                    <div class="astone-project-details">
                        <div class="astone-project-meta">
                            <span class="astone-cat-pill"><?php echo htmlspecialchars($pObj['category']); ?></span>
                            <?php if (!empty($pObj['client_name'])): ?>
                                <span class="astone-client-pill">Client: <?php echo htmlspecialchars($pObj['client_name']); ?></span>
                            <?php endif; ?>
                        </div>
                        <h3 class="astone-project-title">
                            <a href="<?php echo $baseUrl; ?>project-details.php?slug=<?php echo urlencode($pObj['slug']); ?>">
                                <?php echo htmlspecialchars($pObj['title']); ?>
                            </a>
                        </h3>
                        <p class="astone-project-excerpt">
                            <?php echo htmlspecialchars(mb_strimwidth($pObj['short_description'] ?? 'Custom enterprise software solution built by Kendat Tech.', 0, 145, '...')); ?>
                        </p>
                        <div class="astone-project-actions">
                            <a class="astone-link-btn" href="<?php echo $baseUrl; ?>project-details.php?slug=<?php echo urlencode($pObj['slug']); ?>">
                                View Case Details <?php echo render_icon('ArrowRight', 16); ?>
                            </a>
                            <?php if ($projectDemoLink !== '' && $projectDemoLink !== '#'): ?>
                                <a class="astone-link-btn outline" href="<?php echo htmlspecialchars($projectDemoLink); ?>" target="_blank" rel="noopener">
                                    Live Demo <?php echo render_icon('ExternalLink', 15); ?>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- Request 5: Centered Client Testimonials Section -->
    <section class="tc-testimonials-section">
        <div class="tc-section-header centered">
            <span class="tc-kicker">Client Voices</span>
            <h2 class="tc-section-title">Trusted by enterprise leaders and institution administrators</h2>
            <p class="tc-section-desc">See what directors and administrators say about our software development and support.</p>
        </div>
        <div class="tc-testimonials-grid">
            <?php foreach ($testimonials as $t): 
                $initials = implode('', array_map(fn($w) => strtoupper($w[0] ?? ''), explode(' ', $t['client_name'])));
            ?>
                <article class="tc-testimonial-card">
                    <div class="tc-author-box">
                        <div class="tc-avatar"><?php echo htmlspecialchars(substr($initials, 0, 2)); ?></div>
                        <div>
                            <h4><?php echo htmlspecialchars($t['client_name']); ?></h4>
                            <small><?php echo htmlspecialchars($t['position_company'] ?? 'Executive'); ?></small>
                        </div>
                    </div>
                    <div class="tc-stars"><?php echo str_repeat('★', (int)($t['rating'] ?? 5)); ?></div>
                    <p class="tc-quote">"<?php echo htmlspecialchars($t['message']); ?>"</p>
                </article>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- Redesigned High-Impact Enterprise CTA Stage -->
    <section class="tc-cta-section">
        <div class="tc-cta-card-redesigned">
            <div class="tc-cta-glow-mesh"></div>
            <div class="tc-cta-left">
                <span class="tc-cta-kicker">● INSTANT CONSULTATION & PROPOSAL</span>
                <h2 class="tc-cta-title">Ready to Build Your Enterprise Software System?</h2>
                <p class="tc-cta-subtext">Partner with Kendat Tech's senior engineering team. Schedule a 1-on-1 strategy session or submit your custom project requirements for a fast technical proposal.</p>
                <div class="tc-cta-buttons-row">
                    <a class="tc-pill-btn-white" href="<?php echo $baseUrl; ?>book-appointment.php">
                        <?php echo render_icon('CalendarCheck', 18); ?>Book Strategy Session
                    </a>
                    <a class="tc-pill-btn-glass" href="<?php echo $baseUrl; ?>request-project.php">
                        <?php echo render_icon('Send', 18); ?>Submit Project Proposal
                    </a>
                </div>
            </div>
            <div class="tc-cta-right">
                <div class="tc-cta-stats-card">
                    <div class="tc-stat-item">
                        <?php echo render_icon('Zap', 20); ?>
                        <div>
                            <strong>24h Fast Response</strong>
                            <small>Direct engineering review</small>
                        </div>
                    </div>
                    <div class="tc-stat-item">
                        <?php echo render_icon('ShieldCheck', 20); ?>
                        <div>
                            <strong>100% Confidential</strong>
                            <small>Enterprise NDA protected</small>
                        </div>
                    </div>
                    <div class="tc-stat-item">
                        <?php echo render_icon('Clock', 20); ?>
                        <div>
                            <strong>Live Support Desk</strong>
                            <small>Abuja & Lagos Engineering Desk</small>
                        </div>
                    </div>
                    <div class="tc-cta-status-badge">
                        <div class="tc-status-left">
                            <span class="tc-live-dot"></span>
                            <span class="tc-available-text">AVAILABLE TODAY</span>
                        </div>
                        <span class="tc-slots-text">Consultation Slots Open</span>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
