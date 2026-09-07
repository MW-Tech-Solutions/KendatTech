<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/header.php';

$pdo = get_db();
$projectsStmt = $pdo->query("SELECT * FROM projects WHERE status != 'upcoming' ORDER BY featured DESC, id DESC");
$projects = $projectsStmt->fetchAll();

foreach ($projects as &$project) {
    $imgStmt = $pdo->prepare("SELECT image_path FROM project_images WHERE project_id = ? ORDER BY sort_order ASC");
    $imgStmt->execute([$project['id']]);
    $project['images'] = $imgStmt->fetchAll(PDO::FETCH_COLUMN);
}
unset($project);
?>

<main>
    <section class="page-hero">
        <div class="section-title">
            <span>PROJECT PORTFOLIO</span>
            <h1>Digital Products, Institutional Portals & AI Automation Systems</h1>
            <p>Explore live operational software systems engineered for universities, corporate clients, data analytics, and workflow automation.</p>
        </div>
    </section>

    <section class="band grid projects">
        <?php foreach ($projects as $project): 
            $projectDemoLink = trim((string)($project['demo_link'] ?? ''));
            $rawImages = array_merge([$project['main_image']], $project['images'] ?? []);
            $allImages = array_values(array_filter($rawImages, function($v) {
                return !empty(trim((string)$v));
            }));
            if (empty($allImages)) {
                $allImages = [upload_asset_url('', 'analytics')];
            }
        ?>
            <article class="project-card" tabindex="0">
                <div class="project-media">
                    <img src="<?php echo htmlspecialchars(upload_asset_url($allImages[0], 'analytics')); ?>" alt="<?php echo htmlspecialchars($project['title']); ?>">
                </div>
                <div class="project-overlay">
                    <span><?php echo htmlspecialchars($project['category']); ?></span>
                    <h3><?php echo htmlspecialchars($project['title']); ?></h3>
                    <p><?php echo htmlspecialchars($project['technologies'] ?? ''); ?></p>
                    <div class="project-card-actions">
                        <?php if ($projectDemoLink !== '' && $projectDemoLink !== '#'): ?>
                            <a class="tc-pill-btn-white small" href="<?php echo htmlspecialchars($projectDemoLink); ?>" target="_blank" rel="noopener">
                                <?php echo render_icon('ExternalLink', 14); ?> Live Demo
                            </a>
                        <?php endif; ?>
                        <a class="tc-pill-btn-blue small" href="<?php echo $baseUrl; ?>project-details.php?slug=<?php echo urlencode($project['slug']); ?>">
                            View System Details <?php echo render_icon('ArrowRight', 14); ?>
                        </a>
                    </div>
                </div>
            </article>
        <?php endforeach; ?>
    </section>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
