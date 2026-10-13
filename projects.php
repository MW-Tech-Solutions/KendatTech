<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/header.php';

$pdo = get_db();
$selectedCat = $_GET['cat'] ?? 'all';

try {
    if ($selectedCat !== 'all' && !empty($selectedCat)) {
        $stmt = $pdo->prepare("SELECT * FROM projects WHERE status != 'upcoming' AND (LOWER(category) LIKE ? OR slug LIKE ?) ORDER BY featured DESC, id DESC");
        $likeTerm = '%' . strtolower($selectedCat) . '%';
        $stmt->execute([$likeTerm, $likeTerm]);
        $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        $stmt = $pdo->query("SELECT * FROM projects WHERE status != 'upcoming' ORDER BY featured DESC, id DESC");
        $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (Throwable $e) {
    $projects = [];
}
?>

<main class="kt-main-wrapper">

    <!-- Projects Page Hero (Matching Mockup Image) -->
    <section class="kt-projects-hero">
        <div class="kt-container-fluid">
            <div class="kt-projects-hero-grid">
                <!-- Left Content -->
                <div class="kt-projects-hero-left">
                    <div class="kt-eyebrow-line-wrap">
                        <span class="kt-eyebrow-text">OUR PROJECTS</span>
                        <span class="kt-eyebrow-line"></span>
                    </div>
                    <h1 class="kt-projects-hero-title">Our Latest <span class="text-green">Projects</span></h1>
                    <p class="kt-projects-hero-sub">
                        Explore some of our successful projects and see how we turn ideas into powerful digital solutions. Each project represents our commitment to innovation, quality, and client satisfaction.
                    </p>
                    
                    <!-- Hero Stats Bar -->
                    <div class="kt-projects-hero-stats">
                        <div class="kt-proj-stat-item">
                            <div class="kt-proj-stat-icon"><?php echo render_icon('CheckCircle', 18); ?></div>
                            <div class="kt-proj-stat-text">
                                <strong>50+</strong>
                                <small>Projects Completed</small>
                            </div>
                        </div>
                        <div class="kt-proj-stat-item">
                            <div class="kt-proj-stat-icon"><?php echo render_icon('Users', 18); ?></div>
                            <div class="kt-proj-stat-text">
                                <strong>20+</strong>
                                <small>Happy Clients</small>
                            </div>
                        </div>
                        <div class="kt-proj-stat-item">
                            <div class="kt-proj-stat-icon"><?php echo render_icon('Award', 18); ?></div>
                            <div class="kt-proj-stat-text">
                                <strong>3+</strong>
                                <small>Years of Experience</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Graphic Laptop Display & Badges -->
                <div class="kt-projects-hero-right">
                    <div class="kt-projects-laptop-stage">
                        <div class="kt-shape-backdrop green-polygon"></div>
                        <div class="kt-shape-backdrop amber-pill"></div>
                        
                        <div class="kt-floating-card top-left">
                            <div class="kt-float-icon-box"><?php echo render_icon('Code', 18); ?></div>
                            <div class="kt-float-text">
                                <strong>Clean Code</strong>
                                <small>Scalable & Maintainable</small>
                            </div>
                        </div>

                        <div class="kt-laptop-frame">
                            <img src="<?php echo $baseUrl; ?>assets/images/about-workspace.jpg" alt="KendatTech Projects Studio" class="kt-laptop-img">
                        </div>

                        <div class="kt-floating-card bottom-right">
                            <div class="kt-float-icon-box amber"><?php echo render_icon('Lightbulb', 18); ?></div>
                            <div class="kt-float-text">
                                <strong>Innovative Solutions</strong>
                                <small>For a Better Tomorrow</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Category Filter Bar -->
    <div class="kt-portfolio-filter-bar">
        <div class="kt-container-fluid">
            <div class="kt-filter-pill-row">
                <a href="<?php echo $baseUrl; ?>projects.php" class="kt-proj-filter-pill <?php echo ($selectedCat === 'all') ? 'active' : ''; ?>">
                    <?php echo render_icon('Grid', 15); ?> All Projects
                </a>
                <a href="<?php echo $baseUrl; ?>projects.php?cat=web" class="kt-proj-filter-pill <?php echo ($selectedCat === 'web') ? 'active' : ''; ?>">
                    <?php echo render_icon('Code', 15); ?> Web Development
                </a>
                <a href="<?php echo $baseUrl; ?>projects.php?cat=mobile" class="kt-proj-filter-pill <?php echo ($selectedCat === 'mobile') ? 'active' : ''; ?>">
                    <?php echo render_icon('Smartphone', 15); ?> Mobile Apps
                </a>
                <a href="<?php echo $baseUrl; ?>projects.php?cat=cloud" class="kt-proj-filter-pill <?php echo ($selectedCat === 'cloud') ? 'active' : ''; ?>">
                    <?php echo render_icon('Cloud', 15); ?> Cloud Solutions
                </a>
                <a href="<?php echo $baseUrl; ?>projects.php?cat=ai" class="kt-proj-filter-pill <?php echo ($selectedCat === 'ai') ? 'active' : ''; ?>">
                    <?php echo render_icon('BrainCircuit', 15); ?> AI Solutions
                </a>
                <a href="<?php echo $baseUrl; ?>projects.php?cat=consulting" class="kt-proj-filter-pill <?php echo ($selectedCat === 'consulting') ? 'active' : ''; ?>">
                    <?php echo render_icon('ShieldCheck', 15); ?> IT Consulting
                </a>
            </div>
        </div>
    </div>

    <!-- Dynamic Projects Grid (3 Columns, matching mockup) -->
    <section class="kt-projects-showcase-section">
        <div class="kt-container-fluid">
            <div class="kt-projects-grid-3col">
                <?php if (!empty($projects)): ?>
                    <?php foreach ($projects as $pObj): 
                        $pImg = upload_asset_url($pObj['main_image'] ?? '');
                    ?>
                        <article class="kt-project-card-v2">
                            <div class="kt-project-card-v2-img">
                                <?php if (!empty($pImg)): ?>
                                    <img src="<?php echo htmlspecialchars($pImg); ?>" alt="<?php echo htmlspecialchars($pObj['title']); ?>" loading="lazy">
                                <?php else: ?>
                                    <div class="kt-project-card-v2-fallback">
                                        <?php echo render_icon('Globe', 40); ?>
                                    </div>
                                <?php endif; ?>
                                <span class="kt-project-v2-badge">
                                    <?php echo render_icon('Code', 12); ?> <?php echo htmlspecialchars($pObj['category']); ?>
                                </span>
                            </div>
                            <div class="kt-project-card-v2-body">
                                <h3 class="kt-project-v2-title">
                                    <a href="<?php echo $baseUrl; ?>project-details.php?slug=<?php echo urlencode($pObj['slug']); ?>">
                                        <?php echo htmlspecialchars($pObj['title']); ?>
                                    </a>
                                </h3>
                                <p class="kt-project-v2-desc">
                                    <?php echo htmlspecialchars((string)($pObj['short_description'] ?? 'Custom digital platform built for scale and high performance.')); ?>
                                </p>
                                <a href="<?php echo $baseUrl; ?>project-details.php?slug=<?php echo urlencode($pObj['slug']); ?>" class="kt-project-v2-link">
                                    View Project <?php echo render_icon('ArrowRight', 14); ?>
                                </a>
                            </div>
                        </article>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div style="grid-column: 1 / -1; text-align: center; padding: 60px 0; color: #64748B;">
                        <p style="font-size: 16px;">No projects found in this category.</p>
                        <a href="<?php echo $baseUrl; ?>projects.php" class="kt-proj-filter-pill active" style="margin-top: 12px;">View All Projects</a>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Bottom Have a Project in Mind Banner -->
            <div class="kt-join-team-banner" style="margin-top: 60px;">
                <div class="kt-join-banner-left">
                    <div class="kt-join-icon-circle">
                        <?php echo render_icon('Rocket', 24); ?>
                    </div>
                    <div class="kt-join-text">
                        <h3>Have a Project in Mind?</h3>
                        <p><strong>Let's Build Something Great Together.</strong> Partner with KendatTech and turn your ideas into powerful digital solutions.</p>
                    </div>
                </div>
                <div class="kt-join-banner-right">
                    <a href="<?php echo $baseUrl; ?>book-appointment.php" class="kt-btn-join-outline">
                        Get Started <?php echo render_icon('ArrowRight', 16); ?>
                    </a>
                </div>
            </div>

        </div>
    </section>

</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

