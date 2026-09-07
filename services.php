<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/header.php';

$pdo = get_db();
$servicesStmt = $pdo->query("SELECT * FROM services WHERE status = 'active' ORDER BY sort_order ASC, id DESC");
$services = $servicesStmt->fetchAll();
?>

<main>
    <section class="page-hero">
        <div class="section-title">
            <span>ENTERPRISE SERVICES</span>
            <h1>Technology Services Engineered for Enterprise Growth</h1>
            <p>From custom web systems and mobile platforms to AI copilot integration and database architecture, discover our full suite of technical capabilities.</p>
        </div>
    </section>

    <section class="band grid cards">
        <?php foreach ($services as $service): ?>
            <article class="glass-card">
                <div class="card-icon"><?php echo render_icon($service['icon'] ?? 'Globe', 24); ?></div>
                <span class="tag"><?php echo htmlspecialchars($service['category'] ?? 'Software Development'); ?></span>
                <h3><?php echo htmlspecialchars($service['title']); ?></h3>
                <p><?php echo htmlspecialchars($service['short_description']); ?></p>
            </article>
        <?php endforeach; ?>
    </section>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
