<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/header.php';

$pdo = get_db();

$slug = $_GET['slug'] ?? '';
$id = $_GET['id'] ?? null;

$project = null;
if (!empty($slug)) {
    $stmt = $pdo->prepare("SELECT * FROM projects WHERE slug = ? LIMIT 1");
    $stmt->execute([$slug]);
    $project = $stmt->fetch();
} elseif ($id) {
    $stmt = $pdo->prepare("SELECT * FROM projects WHERE id = ? LIMIT 1");
    $stmt->execute([(int)$id]);
    $project = $stmt->fetch();
}

if (!$project) {
    // Fallback to first project if not specified
    $stmt = $pdo->query("SELECT * FROM projects ORDER BY id ASC LIMIT 1");
    $project = $stmt->fetch();
}

$detailImages = [];
$relatedProjects = [];

if ($project) {
    $projectId = (int)$project['id'];
    $projectDemoLink = trim((string)($project['demo_link'] ?? ''));
    
    // Fetch gallery images
    $imgStmt = $pdo->prepare("SELECT image_path FROM project_images WHERE project_id = ? ORDER BY sort_order ASC");
    $imgStmt->execute([$projectId]);
    $gallery = $imgStmt->fetchAll(PDO::FETCH_COLUMN);
    
    $rawGallery = array_merge([$project['main_image']], $gallery);
    $detailImages = array_values(array_filter($rawGallery, function($v) {
        return !empty(trim((string)$v));
    }));
    
    // Provide complementary high-res views for interactive showcase if only 1 image exists
    if (count($detailImages) < 2) {
        $fallbacks = [
            'assets/images/ai-process-laptop.png',
            'assets/images/services-hero-laptop.jpg',
            'assets/images/about-workspace.jpg'
        ];
        foreach ($fallbacks as $fb) {
            if (!in_array($fb, $detailImages, true) && file_exists(__DIR__ . '/' . $fb)) {
                $detailImages[] = $fb;
            }
            if (count($detailImages) >= 3) break;
        }
    }
    
    if (empty($detailImages)) {
        $detailImages = [upload_asset_url('', 'analytics')];
    }

    // Fetch related projects
    $relStmt = $pdo->prepare("SELECT * FROM projects WHERE id != ? ORDER BY id DESC LIMIT 3");
    $relStmt->execute([$projectId]);
    $relatedProjects = $relStmt->fetchAll();
}
?>

<main class="project-details-page">
    <?php if (!$project): ?>
        <section class="page-hero">
            <div class="section-title">
                <span class="eyebrow">CASE STUDY</span>
                <h1>Project Not Found</h1>
                <p>The requested project case study could not be located in our database.</p>
                <div class="actions" style="justify-content: center; margin-top: 24px;">
                    <a class="kt-btn-primary" href="<?php echo $baseUrl; ?>projects.php">
                        <?php echo render_icon('ArrowLeft', 18); ?> Return to Portfolio
                    </a>
                </div>
            </div>
        </section>
    <?php else: ?>
        <!-- Premium Project Detail Hero -->
        <section class="pdetail-hero-section">
            <div class="pdetail-hero-container">
                <!-- Breadcrumb Navigation -->
                <nav class="pdetail-breadcrumbs" aria-label="Breadcrumb">
                    <a href="<?php echo $baseUrl; ?>index.php">Home</a>
                    <span class="pdetail-crumb-sep">/</span>
                    <a href="<?php echo $baseUrl; ?>projects.php">Portfolio</a>
                    <span class="pdetail-crumb-sep">/</span>
                    <span class="pdetail-crumb-current"><?php echo htmlspecialchars($project['title']); ?></span>
                </nav>

                <div class="pdetail-hero-header">
                    <div class="pdetail-hero-left">
                        <div class="pdetail-badge-row">
                            <span class="pdetail-category-badge">
                                <span class="pdetail-dot-pulse"></span>
                                <?php echo htmlspecialchars($project['category'] ?? 'Enterprise Software'); ?>
                            </span>
                            <span class="pdetail-status-pill">
                                <?php echo render_icon('CheckCircle2', 14); ?>
                                <?php echo ucfirst(htmlspecialchars($project['status'] ?? 'completed')); ?> Platform
                            </span>
                        </div>
                        <h1 class="pdetail-main-title"><?php echo htmlspecialchars($project['title']); ?></h1>
                        <p class="pdetail-hero-desc"><?php echo htmlspecialchars($project['short_description']); ?></p>
                        
                        <!-- Metadata Chips Row -->
                        <div class="pdetail-meta-chips">
                            <div class="pdetail-meta-chip">
                                <small>CLIENT</small>
                                <strong><?php echo htmlspecialchars($project['client_name'] ?? 'Enterprise Client'); ?></strong>
                            </div>
                            <div class="pdetail-meta-chip">
                                <small>TIMELINE</small>
                                <strong><?php echo !empty($project['completion_date']) ? date('M Y', strtotime($project['completion_date'])) : 'Production Active'; ?></strong>
                            </div>
                            <div class="pdetail-meta-chip">
                                <small>DEPLOYMENT</small>
                                <strong>Dedicated Cloud / High Availability</strong>
                            </div>
                        </div>
                    </div>

                    <!-- Hero Actions Callout -->
                    <div class="pdetail-hero-right">
                        <div class="pdetail-action-card">
                            <div class="action-card-header">
                                <h3>Experience the System</h3>
                                <p>Review live functionality or initiate requirements for your custom version.</p>
                            </div>
                            <div class="action-card-btns">
                                <?php if ($projectDemoLink !== '' && $projectDemoLink !== '#'): ?>
                                    <a class="kt-btn-primary pdetail-btn-block" href="<?php echo htmlspecialchars($projectDemoLink); ?>" target="_blank" rel="noopener">
                                        <?php echo render_icon('ExternalLink', 18); ?> Launch Live Platform
                                    </a>
                                <?php endif; ?>
                                <a class="pdetail-btn-secondary pdetail-btn-block" href="<?php echo $baseUrl; ?>request-project.php?project_type=<?php echo urlencode($project['title']); ?>">
                                    <?php echo render_icon('Send', 18); ?> Request Similar System
                                </a>
                                <a class="pdetail-btn-ghost pdetail-btn-block" href="<?php echo $baseUrl; ?>book-appointment.php?topic=<?php echo urlencode($project['title']); ?>">
                                    <?php echo render_icon('Calendar', 16); ?> Book Architecture Walkthrough
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- High-Definition Interactive Showcase Stage -->
        <section class="pdetail-showcase-section">
            <div class="pdetail-showcase-container">
                <!-- Browser Chrome Mockup Frame -->
                <div class="pdetail-browser-frame">
                    <div class="pdetail-browser-bar">
                        <div class="browser-traffic-lights">
                            <span class="traffic-dot dot-red"></span>
                            <span class="traffic-dot dot-yellow"></span>
                            <span class="traffic-dot dot-green"></span>
                        </div>
                        <div class="browser-address-field">
                            <?php echo render_icon('Lock', 13); ?>
                            <span>https://live.kendattech.ng/systems/<?php echo htmlspecialchars($project['slug'] ?? 'platform'); ?></span>
                        </div>
                        <div class="browser-status-badge">
                            <span class="status-live-dot"></span>
                            <span>LIVE ENGINE</span>
                        </div>
                    </div>

                    <!-- Main Showcase Display Stage -->
                    <div class="pdetail-stage-viewport">
                        <img id="mainProjectImg" src="<?php echo htmlspecialchars(upload_asset_url($detailImages[0], 'analytics')); ?>" alt="<?php echo htmlspecialchars($project['title']); ?>" class="pdetail-active-img">
                        <div class="pdetail-img-overlay-info">
                            <span id="pdetailImgCounter" class="pdetail-counter-pill">View 1 of <?php echo count($detailImages); ?></span>
                        </div>
                    </div>
                </div>

                <?php if (count($detailImages) > 1): ?>
                    <!-- Interactive Thumbnail Gallery Switcher -->
                    <div class="pdetail-thumbnail-strip" aria-label="Interactive project views">
                        <?php foreach ($detailImages as $idx => $imagePath): ?>
                            <button type="button" class="pdetail-thumb-btn <?php echo $idx === 0 ? 'active' : ''; ?>" data-img-src="<?php echo htmlspecialchars(upload_asset_url($imagePath, 'analytics')); ?>" data-index="<?php echo $idx + 1; ?>" aria-label="Perspective <?php echo $idx + 1; ?>">
                                <img src="<?php echo htmlspecialchars(upload_asset_url($imagePath, 'analytics')); ?>" alt="Perspective <?php echo $idx + 1; ?>">
                                <span class="thumb-label">View 0<?php echo $idx + 1; ?></span>
                            </button>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <!-- Comprehensive System Specifications Grid -->
        <section class="pdetail-specs-section">
            <div class="pdetail-specs-container">
                <div class="pdetail-specs-layout">
                    <!-- Left Column: Deep Narrative & Architecture -->
                    <div class="pdetail-specs-main">
                        <!-- System Overview Card -->
                        <div class="pdetail-card pdetail-overview-box">
                            <div class="pdetail-card-head">
                                <div class="card-head-icon" style="background:#ECFDF5; color:#0B5D3B;">
                                    <?php echo render_icon('FileText', 22); ?>
                                </div>
                                <div>
                                    <span class="pdetail-card-eyebrow">SYSTEM OVERVIEW</span>
                                    <h2>Engineering &amp; Architecture Summary</h2>
                                </div>
                            </div>
                            <div class="pdetail-narrative">
                                <?php 
                                    $fullDesc = $project['full_description'] ?? $project['short_description'];
                                    echo nl2br(htmlspecialchars($fullDesc));
                                ?>
                            </div>
                        </div>

                        <!-- Core Capabilities / Features Checklist -->
                        <div class="pdetail-card pdetail-capabilities-box">
                            <div class="pdetail-card-head">
                                <div class="card-head-icon" style="background:#ECFDF5; color:#0B5D3B;">
                                    <?php echo render_icon('CheckCircle2', 22); ?>
                                </div>
                                <div>
                                    <span class="pdetail-card-eyebrow">SYSTEM CAPABILITIES</span>
                                    <h2>Core Modules &amp; Enterprise Features</h2>
                                </div>
                            </div>
                            
                            <div class="pdetail-features-grid">
                                <?php 
                                    $featuresArr = array_filter(explode("\n", (string)($project['features'] ?? '')));
                                    if (empty($featuresArr)) {
                                        $featuresArr = [
                                            'High-Performance Database Layer with Prepared PDO Transactions',
                                            'Role-Based Granular Access Control (RBAC) & Secure Audit Logging',
                                            'Interactive Responsive Web Interface with Ultra-Low Latency',
                                            'Automated Real-Time Business Intelligence & Operational Reporting',
                                            'SSL/TLS End-to-End Encrypted Data in Transit and at Rest',
                                            'Automated Continuous Integration & Scalable Deployment Pipeline'
                                        ];
                                    }
                                    foreach ($featuresArr as $ft): 
                                        $ftClean = trim((string)$ft);
                                        if (empty($ftClean)) continue;
                                ?>
                                    <div class="pdetail-feature-item">
                                        <div class="feature-icon-badge">
                                            <?php echo render_icon('Check', 16); ?>
                                        </div>
                                        <div class="feature-text">
                                            <span><?php echo htmlspecialchars($ftClean); ?></span>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- Technology Stack & Infrastructure -->
                        <div class="pdetail-card pdetail-tech-box">
                            <div class="pdetail-card-head">
                                <div class="card-head-icon" style="background:#ECFDF5; color:#0B5D3B;">
                                    <?php echo render_icon('Cpu', 22); ?>
                                </div>
                                <div>
                                    <span class="pdetail-card-eyebrow">TECHNOLOGY STACK</span>
                                    <h2>Software Frameworks &amp; Core Tools</h2>
                                </div>
                            </div>
                            
                            <div class="pdetail-stack-pills">
                                <?php 
                                    $techStr = (string)($project['technologies'] ?? 'PHP 8.2, MySQL PDO, JavaScript ES6+, Bootstrap 5, Chart.js, REST API, Linux');
                                    $techs = array_filter(array_map('trim', explode(',', $techStr)));
                                    foreach ($techs as $tech): 
                                ?>
                                    <span class="pdetail-stack-tag">
                                        <span class="stack-dot"></span>
                                        <?php echo htmlspecialchars($tech); ?>
                                    </span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column: System Specs & Direct Engagement CTA -->
                    <aside class="pdetail-specs-sidebar">
                        <!-- Project Metrics Card -->
                        <div class="pdetail-card pdetail-summary-card">
                            <h3>Project Specifications</h3>
                            <div class="pdetail-summary-list">
                                <div class="pdetail-summary-row">
                                    <span class="summary-label">Client / Sponsor</span>
                                    <strong class="summary-value"><?php echo htmlspecialchars($project['client_name'] ?? 'Enterprise Client'); ?></strong>
                                </div>
                                <div class="pdetail-summary-row">
                                    <span class="summary-label">Domain / Category</span>
                                    <strong class="summary-value"><?php echo htmlspecialchars($project['category']); ?></strong>
                                </div>
                                <div class="pdetail-summary-row">
                                    <span class="summary-label">Delivery Status</span>
                                    <strong class="summary-value" style="color: #0B5D3B;">
                                        <span class="status-indicator-live"></span>
                                        <?php echo ucfirst(htmlspecialchars($project['status'] ?? 'completed')); ?>
                                    </strong>
                                </div>
                                <div class="pdetail-summary-row">
                                    <span class="summary-label">Deployment Year</span>
                                    <strong class="summary-value"><?php echo !empty($project['completion_date']) ? date('Y', strtotime($project['completion_date'])) : '2026'; ?></strong>
                                </div>
                                <div class="pdetail-summary-row">
                                    <span class="summary-label">Data Security</span>
                                    <strong class="summary-value">AES-256 / TLS Encrypted</strong>
                                </div>
                                <div class="pdetail-summary-row">
                                    <span class="summary-label">Architecture</span>
                                    <strong class="summary-value">High-Availability Cloud</strong>
                                </div>
                            </div>
                        </div>

                        <!-- Sticky Premium Action Callout -->
                        <div class="pdetail-card pdetail-sticky-cta">
                            <div class="sticky-cta-badge">
                                <?php echo render_icon('Sparkles', 15); ?>
                                <span>CUSTOM BUILDS</span>
                            </div>
                            <h3>Need a Platform Like This?</h3>
                            <p>We architect tailor-made web applications, automated portals, and AI systems built for measurable enterprise growth.</p>
                            
                            <div class="sticky-cta-perks">
                                <div><?php echo render_icon('Check', 14); ?> 24-Hour Scope &amp; Estimation</div>
                                <div><?php echo render_icon('Check', 14); ?> Direct Lead Architect Consultation</div>
                                <div><?php echo render_icon('Check', 14); ?> Full IP &amp; Source Code Ownership</div>
                            </div>

                            <a class="kt-btn-primary" href="<?php echo $baseUrl; ?>request-project.php?project_type=<?php echo urlencode($project['title']); ?>" style="width: 100%; justify-content: center; margin-top: 18px;">
                                Request Custom Project <?php echo render_icon('ArrowRight', 18); ?>
                            </a>
                            <a class="pdetail-cta-link" href="<?php echo $baseUrl; ?>book-appointment.php">
                                Or schedule an online consultation &rarr;
                            </a>
                        </div>
                    </aside>
                </div>
            </div>
        </section>

        <!-- Related Case Studies Section -->
        <?php if (!empty($relatedProjects)): ?>
            <section class="pdetail-related-section">
                <div class="pdetail-related-container">
                    <div class="pdetail-related-header">
                        <div>
                            <span class="pdetail-category-badge" style="margin-bottom: 8px;">PORTFOLIO DISCOVERY</span>
                            <h2>More Enterprise Systems</h2>
                        </div>
                        <a href="<?php echo $baseUrl; ?>projects.php" class="pdetail-view-all-link">
                            View All Projects <?php echo render_icon('ArrowRight', 16); ?>
                        </a>
                    </div>

                    <div class="pdetail-related-grid">
                        <?php foreach ($relatedProjects as $rel): 
                            $relLink = $baseUrl . 'project-details.php?slug=' . urlencode($rel['slug'] ?? '') . '&id=' . $rel['id'];
                            $relImg = !empty($rel['main_image']) ? upload_asset_url($rel['main_image'], 'analytics') : upload_asset_url('', 'analytics');
                        ?>
                            <div class="pdetail-related-card">
                                <a href="<?php echo $relLink; ?>" class="related-img-wrap">
                                    <img src="<?php echo htmlspecialchars($relImg); ?>" alt="<?php echo htmlspecialchars($rel['title']); ?>" loading="lazy">
                                    <span class="related-category-tag"><?php echo htmlspecialchars($rel['category']); ?></span>
                                </a>
                                <div class="related-body">
                                    <h3><a href="<?php echo $relLink; ?>"><?php echo htmlspecialchars($rel['title']); ?></a></h3>
                                    <p><?php echo htmlspecialchars(mb_strimwidth($rel['short_description'] ?? '', 0, 100, '...')); ?></p>
                                    <a href="<?php echo $relLink; ?>" class="related-card-link">
                                        Explore Case Study <?php echo render_icon('ArrowRight', 14); ?>
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </section>
        <?php endif; ?>

        <!-- Interactive Switcher Script -->
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            var mainImg = document.getElementById('mainProjectImg');
            var counterEl = document.getElementById('pdetailImgCounter');
            var thumbBtns = document.querySelectorAll('.pdetail-thumb-btn');
            var totalViews = thumbBtns.length;

            thumbBtns.forEach(function(btn) {
                btn.addEventListener('click', function() {
                    var newSrc = btn.getAttribute('data-img-src');
                    var idx = btn.getAttribute('data-index');
                    if (!newSrc || !mainImg) return;

                    // Active state update
                    thumbBtns.forEach(function(b) { b.classList.remove('active'); });
                    btn.classList.add('active');

                    // Fade transition
                    mainImg.style.opacity = '0.3';
                    mainImg.style.transform = 'scale(0.99)';
                    setTimeout(function() {
                        mainImg.src = newSrc;
                        mainImg.style.opacity = '1';
                        mainImg.style.transform = 'scale(1)';
                    }, 180);

                    if (counterEl) {
                        counterEl.textContent = 'View ' + idx + ' of ' + totalViews;
                    }
                });
            });
        });
        </script>
    <?php endif; ?>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
