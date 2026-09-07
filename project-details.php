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
if ($project) {
    $projectDemoLink = trim((string)($project['demo_link'] ?? ''));
    $imgStmt = $pdo->prepare("SELECT image_path FROM project_images WHERE project_id = ? ORDER BY sort_order ASC");
    $imgStmt->execute([$project['id']]);
    $gallery = $imgStmt->fetchAll(PDO::FETCH_COLUMN);
    $rawGallery = array_merge([$project['main_image']], $gallery);
    $detailImages = array_values(array_filter($rawGallery, function($v) {
        return !empty(trim((string)$v));
    }));
    if (empty($detailImages)) {
        $detailImages = [upload_asset_url('', 'analytics')];
    }
}
?>

<main>
    <?php if (!$project): ?>
        <section class="page-hero">
            <div class="section-title">
                <span>CASE STUDY</span>
                <h1>Project Not Found</h1>
                <p>The requested project case study could not be located in our database.</p>
                <a class="tc-pill-btn-blue" href="<?php echo $baseUrl; ?>projects.php">
                    <?php echo render_icon('ArrowLeft', 18); ?> Return to Portfolio
                </a>
            </div>
        </section>
    <?php else: ?>
        <!-- Hero Header -->
        <section class="page-hero detail-hero">
            <div class="section-title">
                <span class="eyebrow"><?php echo htmlspecialchars($project['category']); ?></span>
                <h1><?php echo htmlspecialchars($project['title']); ?></h1>
                <p><?php echo htmlspecialchars($project['short_description']); ?></p>
                <div class="actions hero-actions-row">
                    <?php if ($projectDemoLink !== '' && $projectDemoLink !== '#'): ?>
                        <a class="tc-pill-btn-blue" href="<?php echo htmlspecialchars($projectDemoLink); ?>" target="_blank" rel="noopener">
                            <?php echo render_icon('ExternalLink', 18); ?> Launch Live Demo
                        </a>
                    <?php endif; ?>
                    <a class="tc-pill-btn-white" href="<?php echo $baseUrl; ?>request-project.php?project_type=<?php echo urlencode($project['title']); ?>">
                        <?php echo render_icon('Send', 18); ?> Request Similar System
                    </a>
                    <a class="tc-pill-btn-glass" href="<?php echo $baseUrl; ?>projects.php">
                        <?php echo render_icon('ArrowLeft', 16); ?> All Projects
                    </a>
                </div>
            </div>
        </section>

        <!-- High-Res Interactive Project Gallery Showcase Stage -->
        <section class="band project-detail-stage-section">
            <div class="project-detail-main-stage">
                <div class="project-detail-frame">
                    <img id="mainProjectImg" src="<?php echo htmlspecialchars(upload_asset_url($detailImages[0], 'analytics')); ?>" alt="<?php echo htmlspecialchars($project['title']); ?>" class="project-main-display-img">
                    <div class="project-frame-badge">
                        <span class="status-dot-green"></span>
                        <span>OPERATIONAL PLATFORM</span>
                    </div>
                </div>

                <?php if (count($detailImages) > 1): ?>
                    <!-- Thumbnail Gallery Switcher -->
                    <div class="project-thumb-gallery" aria-label="Project images gallery">
                        <?php foreach ($detailImages as $idx => $imagePath): ?>
                            <button type="button" class="project-thumb-btn <?php echo $idx === 0 ? 'active' : ''; ?>" data-img-src="<?php echo htmlspecialchars(upload_asset_url($imagePath, 'analytics')); ?>" aria-label="View Screenshot <?php echo $idx + 1; ?>">
                                <img src="<?php echo htmlspecialchars(upload_asset_url($imagePath, 'analytics')); ?>" alt="Thumbnail <?php echo $idx + 1; ?>">
                            </button>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <!-- Project Specifications & Architecture Grid -->
        <section class="band project-spec-section">
            <div class="project-spec-grid">
                <!-- Overview & System Capabilities -->
                <div class="glass-card project-overview-card">
                    <div class="card-icon"><?php echo render_icon('FileText', 24); ?></div>
                    <h3>System Overview</h3>
                    <p class="project-full-desc"><?php echo nl2br(htmlspecialchars($project['full_description'] ?? $project['short_description'])); ?></p>
                </div>

                <!-- Features & Technical Highlights -->
                <div class="glass-card project-features-card">
                    <div class="card-icon"><?php echo render_icon('CheckCircle2', 24); ?></div>
                    <h3>Key Capabilities</h3>
                    <ul class="project-features-list">
                        <?php 
                            $featuresArr = array_filter(explode("\n", (string)($project['features'] ?? '')));
                            if (empty($featuresArr)) {
                                $featuresArr = ['High-Speed PDO Encrypted Database Layer', 'Responsive Glassmorphism UI', 'Role-Based Access Control', 'Automated Enterprise Workflows'];
                            }
                            foreach ($featuresArr as $ft): 
                                $ftClean = trim((string)$ft);
                                if (empty($ftClean)) continue;
                        ?>
                            <li>
                                <?php echo render_icon('Check', 16); ?>
                                <span><?php echo htmlspecialchars($ftClean); ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <!-- Technologies & Stack Pills -->
                <div class="glass-card project-tech-card">
                    <div class="card-icon"><?php echo render_icon('Cpu', 24); ?></div>
                    <h3>Technologies & Stack</h3>
                    <div class="project-tech-tags">
                        <?php 
                            $techs = array_filter(explode(',', (string)($project['technologies'] ?? 'PHP 8, MySQL, PDO Encrypted, JavaScript, Bootstrap 5')));
                            foreach ($techs as $tech): 
                        ?>
                            <span class="tech-tag-pill"><?php echo htmlspecialchars(trim($tech)); ?></span>
                        <?php endforeach; ?>
                    </div>

                    <div class="project-meta-box">
                        <div class="meta-item">
                            <small>CLIENT / INSTITUTION</small>
                            <strong><?php echo htmlspecialchars($project['client_name'] ?? 'Enterprise Client'); ?></strong>
                        </div>
                        <div class="meta-item">
                            <small>CATEGORY</small>
                            <strong><?php echo htmlspecialchars($project['category']); ?></strong>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    <?php endif; ?>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
