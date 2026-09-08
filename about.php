<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/header.php';

$db = get_db();
$stmt = $db->query("SELECT * FROM team_members WHERE status = 'active' ORDER BY sort_order ASC, id ASC");
$teamMembers = $stmt->fetchAll(PDO::FETCH_ASSOC);

$aboutCompany = $settings['about_company'] ?? 'Kendat Integrated Services is a technology-driven company focused on software engineering, AI solutions, digital transformation, and enterprise systems for businesses, institutions, and individuals.';
?>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Caveat:wght@600&display=swap" rel="stylesheet">

<main class="about-futuristic-wrapper">
    <div class="container-fluid px-lg-5">
        
        <!-- Top Motto Bar -->
        <div class="about-top-bar">
            <div class="about-top-motto">PEOPLE &times; TECHNOLOGY &times; A BRIGHTER TOMORROW</div>
            <div class="about-handwritten-overlay">Technology People Progress</div>
        </div>

        <div class="about-main-layout">
            
            <!-- Left Hero Column -->
            <div class="about-hero-col">
                <div class="about-badge-pill">ABOUT US</div>

                <h1 class="about-headline">
                    Building Digital Solutions with Vision and <span class="text-accent">Engineering Excellence</span>
                </h1>

                <p class="about-description">
                    <?php echo htmlspecialchars($aboutCompany); ?>
                </p>

                <!-- Duo Feature Cards -->
                <div class="about-features-duo">
                    <div class="about-feature-box">
                        <div class="about-feature-icon">
                            <?php echo render_icon('Settings', 22); ?>
                        </div>
                        <div class="about-feature-text">
                            <h4>Innovative Solutions</h4>
                            <p>For a Smarter Tomorrow</p>
                        </div>
                    </div>
                    <div class="about-feature-box">
                        <div class="about-feature-icon">
                            <?php echo render_icon('TrendingUp', 22); ?>
                        </div>
                        <div class="about-feature-text">
                            <h4>Trusted Technology Partner</h4>
                            <p>Across Africa and Beyond</p>
                        </div>
                    </div>
                </div>

                <!-- CTA Group -->
                <div class="about-cta-group">
                    <a href="<?php echo $baseUrl; ?>contact.php" class="about-btn-glow">
                        Our Journey Continues <?php echo render_icon('ArrowRight', 16); ?>
                    </a>
                    <a href="<?php echo $baseUrl; ?>services.php" class="about-btn-subtle">
                        Let's Build Together &mdash;&mdash;&mdash;
                    </a>
                </div>
            </div>

            <!-- Right Team Members Dynamic Cards Column -->
            <div class="about-team-col">
                <div class="team-members-grid">
                    <?php if (!empty($teamMembers)): ?>
                        <?php foreach ($teamMembers as $member): ?>
                            <div class="team-card-neon">
                                <div class="team-card-photo-box">
                                    <?php if (!empty($member['photo'])): ?>
                                        <img src="<?php echo htmlspecialchars(upload_asset_url($member['photo'])); ?>" alt="<?php echo htmlspecialchars($member['name']); ?>" class="team-card-photo-img">
                                    <?php else: ?>
                                        <div class="team-card-photo-fallback">
                                            <?php echo render_icon('UserCheck', 64); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="team-card-content">
                                    <h3 class="team-card-role-title"><?php echo htmlspecialchars($member['role_title']); ?></h3>
                                    <div class="team-card-name-title"><?php echo htmlspecialchars($member['name']); ?></div>
                                    
                                    <?php if (!empty($member['bio'])): ?>
                                        <p class="team-card-bio-text"><?php echo htmlspecialchars($member['bio']); ?></p>
                                    <?php endif; ?>

                                    <?php if (!empty($member['specialties'])): ?>
                                        <div class="team-card-tags"><?php echo htmlspecialchars($member['specialties']); ?></div>
                                    <?php endif; ?>

                                    <?php if (!empty($member['linkedin_url']) || !empty($member['github_url'])): ?>
                                        <div class="team-card-social-links">
                                            <?php if (!empty($member['linkedin_url'])): ?>
                                                <a href="<?php echo htmlspecialchars($member['linkedin_url']); ?>" target="_blank" rel="noopener" title="LinkedIn">
                                                    <?php echo render_icon('Linkedin', 16); ?>
                                                </a>
                                            <?php endif; ?>
                                            <?php if (!empty($member['github_url'])): ?>
                                                <a href="<?php echo htmlspecialchars($member['github_url']); ?>" target="_blank" rel="noopener" title="GitHub">
                                                    <?php echo render_icon('Github', 16); ?>
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="team-card-neon p-4 text-center">
                            <p class="text-muted mb-0">No team members added yet.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

        </div>

        <!-- Globe / Footer Accent Bar -->
        <div class="about-globe-accent-bar">
            <div class="about-globe-text-brand">
                AFRICA CONNECTED TO A BRIGHTER TOMORROW
            </div>
        </div>

    </div>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

