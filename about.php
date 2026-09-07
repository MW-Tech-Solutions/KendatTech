<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/header.php';
?>

<main>
    <section class="page-hero">
        <div class="section-title">
            <span>ABOUT US</span>
            <h1>Engineering Next-Gen Digital Infrastructure</h1>
            <p><?php echo htmlspecialchars($settings['about_company'] ?? 'Kendat Integrated Services delivers high-speed web portals, custom software solutions, and AI automation engines designed for reliability, scalability, and measurable impact.'); ?></p>
        </div>
    </section>

    <section class="band split">
        <div class="glass-card">
            <div class="card-icon"><?php echo render_icon('Target', 24); ?></div>
            <h3>Our Mission</h3>
            <p><?php echo htmlspecialchars($settings['mission_statement'] ?? 'To empower enterprises and educational institutions with fast, secure, and intuitive digital platforms.'); ?></p>
        </div>
        <div class="glass-card">
            <div class="card-icon"><?php echo render_icon('Compass', 24); ?></div>
            <h3>Our Vision</h3>
            <p><?php echo htmlspecialchars($settings['vision_statement'] ?? 'To become a premier software engineering and AI automation firm, setting the standard for institutional software excellence.'); ?></p>
        </div>
        <div class="glass-card">
            <div class="card-icon"><?php echo render_icon('Award', 24); ?></div>
            <h3>Core Values</h3>
            <p>Integrity, technical excellence, clarity, security, speed, maintainability, and measurable enterprise value.</p>
        </div>
    </section>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
