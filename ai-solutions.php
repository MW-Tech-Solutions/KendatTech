<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/header.php';

$pdo = get_db();
$aiStmt = $pdo->query("SELECT * FROM ai_solutions WHERE status = 'active' ORDER BY sort_order ASC, id DESC");
$aiSolutions = $aiStmt->fetchAll();
?>

<main>
    <section class="page-hero">
        <div class="section-title">
            <span>AI & DATA AUTOMATION</span>
            <h1>Next-Gen AI Copilots & Enterprise Analytics</h1>
            <p>Deploy custom-trained AI language models, automated data pipelines, predictive analytics engines, and neural search systems built specifically for enterprise workflow productivity.</p>
        </div>
    </section>

    <section class="band">
        <div class="band grid cards">
            <?php foreach ($aiSolutions as $item): ?>
                <article class="glass-card">
                    <div class="card-icon"><?php echo render_icon($item['icon'] ?? 'Bot', 24); ?></div>
                    <span class="tag">AI ENGINE</span>
                    <h3><?php echo htmlspecialchars($item['title']); ?></h3>
                    <p><?php echo htmlspecialchars($item['short_description']); ?></p>
                </article>
            <?php endforeach; ?>
        </div>
    </section>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
