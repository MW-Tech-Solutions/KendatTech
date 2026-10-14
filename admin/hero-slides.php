<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/admin_header.php';
require_once __DIR__ . '/../includes/upload_helper.php';

require_csrf_token();

$pdo = get_db();
$notice = '';
$error = '';

// Ensure hero_slides table exists
try {
    $pdo->query("SELECT 1 FROM hero_slides LIMIT 1");
} catch (Throwable $e) {
    // Auto-create table if missing
    $pdo->exec("CREATE TABLE IF NOT EXISTS `hero_slides` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `badge_text` VARCHAR(150) NOT NULL DEFAULT 'INNOVATIVE TECHNOLOGY SOLUTIONS',
        `title` VARCHAR(255) NOT NULL,
        `highlight_text` VARCHAR(120) DEFAULT 'KendatTech',
        `description` TEXT NOT NULL,
        `primary_cta_text` VARCHAR(80) DEFAULT 'Our Services',
        `primary_cta_url` VARCHAR(255) DEFAULT 'services.php',
        `secondary_cta_text` VARCHAR(80) DEFAULT 'Learn More',
        `secondary_cta_url` VARCHAR(255) DEFAULT 'about.php',
        `slide_image` VARCHAR(255) DEFAULT 'assets/images/hero-laptop.png',
        `badge1_icon` VARCHAR(60) DEFAULT 'Code2',
        `badge1_title` VARCHAR(100) DEFAULT 'Clean Code',
        `badge1_subtitle` VARCHAR(120) DEFAULT 'Scalable & Maintainable',
        `badge2_icon` VARCHAR(60) DEFAULT 'ShieldCheck',
        `badge2_title` VARCHAR(100) DEFAULT 'Reliable Security',
        `badge2_subtitle` VARCHAR(120) DEFAULT 'Your Data, Our Priority',
        `tech_tags` VARCHAR(255) DEFAULT 'PHP, React, MySQL, Cloud APIs',
        `sort_order` INT DEFAULT 1,
        `status` ENUM('active', 'inactive') DEFAULT 'active',
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX `idx_hero_status_sort` (`status`, `sort_order`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
}

// Handle Database Reset / Seed Action via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'seed_defaults') {
    try {
        $seeds = [
            [
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
                'tech_tags' => 'PHP, React, MySQL, REST APIs',
                'sort_order' => 1,
                'status' => 'active'
            ],
            [
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
                'tech_tags' => 'React, Next.js, Laravel, Flutter',
                'sort_order' => 2,
                'status' => 'active'
            ],
            [
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
                'tech_tags' => 'AWS, Docker, Microservices, CI/CD',
                'sort_order' => 3,
                'status' => 'active'
            ],
            [
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
                'tech_tags' => 'Python, OpenAI, PyTorch, Automation',
                'sort_order' => 4,
                'status' => 'active'
            ]
        ];

        $insStmt = $pdo->prepare("INSERT INTO hero_slides (
            badge_text, title, highlight_text, description,
            primary_cta_text, primary_cta_url, secondary_cta_text, secondary_cta_url,
            slide_image, badge1_icon, badge1_title, badge1_subtitle,
            badge2_icon, badge2_title, badge2_subtitle, tech_tags,
            sort_order, status
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        foreach ($seeds as $s) {
            $checkStmt = $pdo->prepare("SELECT id FROM hero_slides WHERE title = ? LIMIT 1");
            $checkStmt->execute([$s['title']]);
            if (!$checkStmt->fetch()) {
                $insStmt->execute([
                    $s['badge_text'], $s['title'], $s['highlight_text'], $s['description'],
                    $s['primary_cta_text'], $s['primary_cta_url'], $s['secondary_cta_text'], $s['secondary_cta_url'],
                    $s['slide_image'], $s['badge1_icon'], $s['badge1_title'], $s['badge1_subtitle'],
                    $s['badge2_icon'], $s['badge2_title'], $s['badge2_subtitle'], $s['tech_tags'],
                    $s['sort_order'], $s['status']
                ]);
            }
        }
        $notice = 'Default Software Development hero slides verified and seeded successfully.';
    } catch (Throwable $e) {
        $error = 'Error seeding slides: ' . $e->getMessage();
    }
}

// Handle Delete via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $id = (int)($_POST['id'] ?? 0);
    if ($id > 0) {
        $stmt = $pdo->prepare("DELETE FROM hero_slides WHERE id = ?");
        $stmt->execute([$id]);
        audit_log('ADMIN_HERO_SLIDE_DELETED', 'hero_slide', (string)$id);
        $notice = 'Hero carousel slide deleted successfully.';
    }
}

// Handle Status Toggle via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'toggle_status') {
    $id = (int)($_POST['id'] ?? 0);
    $newStatus = ($_POST['new_status'] ?? '') === 'active' ? 'active' : 'inactive';
    if ($id > 0) {
        $stmt = $pdo->prepare("UPDATE hero_slides SET status = ? WHERE id = ?");
        $stmt->execute([$newStatus, $id]);
        audit_log('ADMIN_HERO_SLIDE_STATUS_TOGGLED', 'hero_slide', (string)$id, ['status' => $newStatus]);
        $notice = "Slide status updated to {$newStatus}.";
    }
}

// Handle Save (Add or Edit) Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && (!isset($_POST['action']) || $_POST['action'] === 'save')) {
    $id = !empty($_POST['id']) ? (int)$_POST['id'] : null;
    $badgeText = trim($_POST['badge_text'] ?? 'INNOVATIVE TECHNOLOGY SOLUTIONS');
    $title = trim($_POST['title'] ?? '');
    $highlightText = trim($_POST['highlight_text'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $primaryCtaText = trim($_POST['primary_cta_text'] ?? 'Our Services');
    $primaryCtaUrl = trim($_POST['primary_cta_url'] ?? 'services.php');
    $secondaryCtaText = trim($_POST['secondary_cta_text'] ?? 'Learn More');
    $secondaryCtaUrl = trim($_POST['secondary_cta_url'] ?? 'about.php');
    $existingImage = trim($_POST['existing_slide_image'] ?? 'assets/images/hero-laptop.png');
    $badge1Icon = trim($_POST['badge1_icon'] ?? 'Code2');
    $badge1Title = trim($_POST['badge1_title'] ?? 'Clean Code');
    $badge1Subtitle = trim($_POST['badge1_subtitle'] ?? 'Scalable & Maintainable');
    $badge2Icon = trim($_POST['badge2_icon'] ?? 'ShieldCheck');
    $badge2Title = trim($_POST['badge2_title'] ?? 'Reliable Security');
    $badge2Subtitle = trim($_POST['badge2_subtitle'] ?? 'Your Data, Our Priority');
    $techTags = trim($_POST['tech_tags'] ?? 'PHP, React, MySQL, Cloud APIs');
    $sortOrder = (int)($_POST['sort_order'] ?? 1);
    $status = ($_POST['status'] ?? 'active') === 'active' ? 'active' : 'inactive';

    if (empty($title) || empty($description)) {
        $error = 'Slide headline title and description are required.';
    } else {
        // Handle file upload if present
        $uploadedImage = secure_file_upload('slide_image_file', 'hero');
        $finalSlideImage = $uploadedImage ?: (!empty($_POST['slide_image_select']) ? trim($_POST['slide_image_select']) : $existingImage);

        if ($id) {
            $stmt = $pdo->prepare("UPDATE hero_slides SET 
                badge_text = ?, title = ?, highlight_text = ?, description = ?,
                primary_cta_text = ?, primary_cta_url = ?, secondary_cta_text = ?, secondary_cta_url = ?,
                slide_image = ?, badge1_icon = ?, badge1_title = ?, badge1_subtitle = ?,
                badge2_icon = ?, badge2_title = ?, badge2_subtitle = ?, tech_tags = ?,
                sort_order = ?, status = ?
                WHERE id = ?");
            $stmt->execute([
                $badgeText, $title, $highlightText, $description,
                $primaryCtaText, $primaryCtaUrl, $secondaryCtaText, $secondaryCtaUrl,
                $finalSlideImage, $badge1Icon, $badge1Title, $badge1Subtitle,
                $badge2Icon, $badge2Title, $badge2Subtitle, $techTags,
                $sortOrder, $status, $id
            ]);
            audit_log('ADMIN_HERO_SLIDE_UPDATED', 'hero_slide', (string)$id, ['title' => $title]);
            $notice = 'Hero carousel slide updated successfully.';
        } else {
            $stmt = $pdo->prepare("INSERT INTO hero_slides (
                badge_text, title, highlight_text, description,
                primary_cta_text, primary_cta_url, secondary_cta_text, secondary_cta_url,
                slide_image, badge1_icon, badge1_title, badge1_subtitle,
                badge2_icon, badge2_title, badge2_subtitle, tech_tags,
                sort_order, status
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $badgeText, $title, $highlightText, $description,
                $primaryCtaText, $primaryCtaUrl, $secondaryCtaText, $secondaryCtaUrl,
                $finalSlideImage, $badge1Icon, $badge1Title, $badge1Subtitle,
                $badge2Icon, $badge2Title, $badge2Subtitle, $techTags,
                $sortOrder, $status
            ]);
            $newId = (int)$pdo->lastInsertId();
            audit_log('ADMIN_HERO_SLIDE_CREATED', 'hero_slide', (string)$newId, ['title' => $title]);
            $notice = 'New hero carousel slide created successfully.';
        }
    }
}

// Fetch all slides
$stmt = $pdo->query("SELECT * FROM hero_slides ORDER BY sort_order ASC, id ASC");
$slides = $stmt->fetchAll();

// Edit Mode Record
$editRecord = null;
if (isset($_GET['edit'])) {
    $editId = (int)$_GET['edit'];
    $eStmt = $pdo->prepare("SELECT * FROM hero_slides WHERE id = ? LIMIT 1");
    $eStmt->execute([$editId]);
    $editRecord = $eStmt->fetch();
}
$isFormActive = isset($_GET['action']) && $_GET['action'] === 'new' || $editRecord !== null;
?>

<div class="admin-page-head">
    <div>
        <span class="eyebrow">Public Homepage Showcase</span>
        <h1>Hero Carousel Management</h1>
        <p>Dynamically manage, order, and customize the Software Development hero carousel slides on the homepage.</p>
    </div>
    <div class="admin-head-actions">
        <form method="post" style="display:inline-block; margin-right:8px;" onsubmit="return confirm('Ensure default Software Development slides are seeded?');">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="action" value="seed_defaults">
            <button type="submit" class="btn outline" title="Seed default slides if table is missing them">
                <?php echo render_icon('RefreshCw', 16); ?> Verify & Seed Defaults
            </button>
        </form>
        <?php if (!$isFormActive): ?>
            <a class="btn primary" href="hero-slides.php?action=new">
                <?php echo render_icon('Plus', 18); ?> Add New Slide
            </a>
        <?php else: ?>
            <a class="btn outline" href="hero-slides.php">
                <?php echo render_icon('ChevronLeft', 18); ?> Back to Slides List
            </a>
        <?php endif; ?>
    </div>
</div>

<?php if ($notice): ?>
    <div class="admin-notice success">
        <?php echo render_icon('CheckCircle2', 18); ?>
        <span><?php echo htmlspecialchars($notice); ?></span>
    </div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="admin-notice error">
        <?php echo render_icon('TriangleAlert', 18); ?>
        <span><?php echo htmlspecialchars($error); ?></span>
    </div>
<?php endif; ?>

<!-- Quick Live Stats Bar -->
<div class="admin-quick-stats" style="display:grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap:16px; margin-bottom:24px;">
    <div class="admin-stat-card" style="background:#FFFFFF; border:1px solid #E2E8F0; border-radius:14px; padding:18px 22px; display:flex; align-items:center; gap:16px;">
        <div style="width:48px; height:48px; border-radius:12px; background:rgba(11,93,59,0.1); color:#0B5D3B; display:flex; align-items:center; justify-content:center;">
            <?php echo render_icon('Layers3', 24); ?>
        </div>
        <div>
            <div style="font-size:13px; color:#64748B; font-weight:600; text-transform:uppercase;">Total Slides</div>
            <div style="font-size:24px; font-weight:800; color:#0F172A;"><?php echo count($slides); ?></div>
        </div>
    </div>
    <div class="admin-stat-card" style="background:#FFFFFF; border:1px solid #E2E8F0; border-radius:14px; padding:18px 22px; display:flex; align-items:center; gap:16px;">
        <div style="width:48px; height:48px; border-radius:12px; background:rgba(16,185,129,0.1); color:#10B981; display:flex; align-items:center; justify-content:center;">
            <?php echo render_icon('CheckCircle2', 24); ?>
        </div>
        <div>
            <div style="font-size:13px; color:#64748B; font-weight:600; text-transform:uppercase;">Active Slides</div>
            <div style="font-size:24px; font-weight:800; color:#0F172A;">
                <?php echo count(array_filter($slides, fn($s) => $s['status'] === 'active')); ?>
            </div>
        </div>
    </div>
    <div class="admin-stat-card" style="background:#FFFFFF; border:1px solid #E2E8F0; border-radius:14px; padding:18px 22px; display:flex; align-items:center; gap:16px;">
        <div style="width:48px; height:48px; border-radius:12px; background:rgba(59,130,246,0.1); color:#3B82F6; display:flex; align-items:center; justify-content:center;">
            <?php echo render_icon('Code2', 24); ?>
        </div>
        <div>
            <div style="font-size:13px; color:#64748B; font-weight:600; text-transform:uppercase;">Domain Focus</div>
            <div style="font-size:15px; font-weight:700; color:#0F172A;">Software Dev</div>
        </div>
    </div>
</div>

<?php if ($isFormActive): 
    $fId = $editRecord['id'] ?? '';
    $fBadge = $editRecord['badge_text'] ?? 'INNOVATIVE TECHNOLOGY SOLUTIONS';
    $fTitle = $editRecord['title'] ?? 'Build Smarter With KendatTech';
    $fHighlight = $editRecord['highlight_text'] ?? 'KendatTech';
    $fDesc = $editRecord['description'] ?? 'We design, develop and deliver modern software solutions that help businesses grow, operate efficiently and stay ahead in a digital world.';
    $fCta1Text = $editRecord['primary_cta_text'] ?? 'Our Services';
    $fCta1Url = $editRecord['primary_cta_url'] ?? 'services.php';
    $fCta2Text = $editRecord['secondary_cta_text'] ?? 'Learn More';
    $fCta2Url = $editRecord['secondary_cta_url'] ?? 'about.php';
    $fImage = $editRecord['slide_image'] ?? 'assets/images/hero-laptop.png';
    $fBadge1Icon = $editRecord['badge1_icon'] ?? 'Code2';
    $fBadge1Title = $editRecord['badge1_title'] ?? 'Clean Code';
    $fBadge1Sub = $editRecord['badge1_subtitle'] ?? 'Scalable & Maintainable';
    $fBadge2Icon = $editRecord['badge2_icon'] ?? 'ShieldCheck';
    $fBadge2Title = $editRecord['badge2_title'] ?? 'Reliable Security';
    $fBadge2Sub = $editRecord['badge2_subtitle'] ?? 'Your Data, Our Priority';
    $fTechTags = $editRecord['tech_tags'] ?? 'PHP, React, MySQL, REST APIs';
    $fSort = $editRecord['sort_order'] ?? (count($slides) + 1);
    $fStatus = $editRecord['status'] ?? 'active';
?>
    <!-- Add / Edit Form Card with Real-time Interactive Live Preview -->
    <div class="admin-projects-card" style="background:#FFFFFF; border:1px solid #E2E8F0; border-radius:18px; padding:28px; margin-bottom:32px; box-shadow:0 4px 20px rgba(0,0,0,0.03);">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px; border-bottom:1px solid #F1F5F9; padding-bottom:16px;">
            <div>
                <h2 style="font-size:20px; font-weight:700; color:#0F172A; margin:0 0 4px 0;">
                    <?php echo $editRecord ? 'Edit Slide #' . (int)$fId : 'Create New Hero Carousel Slide'; ?>
                </h2>
                <p style="margin:0; font-size:14px; color:#64748B;">
                    Configure headline copy, highlighted keywords, software graphics, action buttons, and floating badges.
                </p>
            </div>
            <a href="hero-slides.php" class="btn small outline">Cancel</a>
        </div>

        <form method="post" enctype="multipart/form-data" id="heroSlideForm">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="action" value="save">
            <?php if ($fId): ?>
                <input type="hidden" name="id" value="<?php echo (int)$fId; ?>">
            <?php endif; ?>
            <input type="hidden" name="existing_slide_image" id="existing_slide_image" value="<?php echo htmlspecialchars($fImage); ?>">

            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:24px;">
                <!-- Left: Form Fields -->
                <div>
                    <h3 style="font-size:16px; font-weight:700; color:#0B5D3B; margin-bottom:16px; display:flex; align-items:center; gap:8px;">
                        <?php echo render_icon('Code2', 18); ?> 1. Headline & Copywriting
                    </h3>

                    <div style="margin-bottom:16px;">
                        <label style="display:block; font-size:13px; font-weight:600; color:#334155; margin-bottom:6px;">Eyebrow / Badge Text</label>
                        <input type="text" name="badge_text" id="input_badge_text" class="admin-input" required 
                               value="<?php echo htmlspecialchars($fBadge); ?>" 
                               placeholder="e.g. INNOVATIVE TECHNOLOGY SOLUTIONS"
                               style="width:100%; padding:10px 14px; border:1px solid #CBD5E1; border-radius:10px;">
                    </div>

                    <div style="margin-bottom:16px;">
                        <label style="display:block; font-size:13px; font-weight:600; color:#334155; margin-bottom:6px;">Slide Headline (Title)</label>
                        <input type="text" name="title" id="input_title" class="admin-input" required 
                               value="<?php echo htmlspecialchars($fTitle); ?>" 
                               placeholder="e.g. Build Smarter With KendatTech"
                               style="width:100%; padding:10px 14px; border:1px solid #CBD5E1; border-radius:10px;">
                    </div>

                    <div style="margin-bottom:16px;">
                        <label style="display:block; font-size:13px; font-weight:600; color:#334155; margin-bottom:6px;">
                            Highlighted Keyword / Phrase
                            <span style="font-weight:400; color:#64748B;">(Word inside Title to render in bright brand green)</span>
                        </label>
                        <input type="text" name="highlight_text" id="input_highlight_text" class="admin-input" 
                               value="<?php echo htmlspecialchars($fHighlight); ?>" 
                               placeholder="e.g. KendatTech"
                               style="width:100%; padding:10px 14px; border:1px solid #CBD5E1; border-radius:10px;">
                    </div>

                    <div style="margin-bottom:16px;">
                        <label style="display:block; font-size:13px; font-weight:600; color:#334155; margin-bottom:6px;">Lead Description Text</label>
                        <textarea name="description" id="input_description" class="admin-input" rows="3" required
                                  placeholder="Describe the software engineering capability or solution..."
                                  style="width:100%; padding:10px 14px; border:1px solid #CBD5E1; border-radius:10px;"><?php echo htmlspecialchars($fDesc); ?></textarea>
                    </div>

                    <div style="margin-bottom:16px;">
                        <label style="display:block; font-size:13px; font-weight:600; color:#334155; margin-bottom:6px;">
                            Technologies / Tags
                            <span style="font-weight:400; color:#64748B;">(Comma separated tags)</span>
                        </label>
                        <input type="text" name="tech_tags" id="input_tech_tags" class="admin-input" 
                               value="<?php echo htmlspecialchars($fTechTags); ?>" 
                               placeholder="e.g. PHP, React, MySQL, REST APIs"
                               style="width:100%; padding:10px 14px; border:1px solid #CBD5E1; border-radius:10px;">
                    </div>

                    <h3 style="font-size:16px; font-weight:700; color:#0B5D3B; margin:24px 0 16px 0; display:flex; align-items:center; gap:8px;">
                        <?php echo render_icon('ArrowRight', 18); ?> 2. Call-To-Action Buttons
                    </h3>

                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:16px;">
                        <div>
                            <label style="display:block; font-size:12px; font-weight:600; color:#334155; margin-bottom:4px;">Primary Button Text</label>
                            <input type="text" name="primary_cta_text" id="input_cta1_text" class="admin-input"
                                   value="<?php echo htmlspecialchars($fCta1Text); ?>" placeholder="Our Services"
                                   style="width:100%; padding:9px 12px; border:1px solid #CBD5E1; border-radius:10px;">
                        </div>
                        <div>
                            <label style="display:block; font-size:12px; font-weight:600; color:#334155; margin-bottom:4px;">Primary Button URL</label>
                            <input type="text" name="primary_cta_url" id="input_cta1_url" class="admin-input"
                                   value="<?php echo htmlspecialchars($fCta1Url); ?>" placeholder="services.php"
                                   style="width:100%; padding:9px 12px; border:1px solid #CBD5E1; border-radius:10px;">
                        </div>
                    </div>

                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:16px;">
                        <div>
                            <label style="display:block; font-size:12px; font-weight:600; color:#334155; margin-bottom:4px;">Secondary Button Text</label>
                            <input type="text" name="secondary_cta_text" id="input_cta2_text" class="admin-input"
                                   value="<?php echo htmlspecialchars($fCta2Text); ?>" placeholder="Learn More"
                                   style="width:100%; padding:9px 12px; border:1px solid #CBD5E1; border-radius:10px;">
                        </div>
                        <div>
                            <label style="display:block; font-size:12px; font-weight:600; color:#334155; margin-bottom:4px;">Secondary Button URL</label>
                            <input type="text" name="secondary_cta_url" id="input_cta2_url" class="admin-input"
                                   value="<?php echo htmlspecialchars($fCta2Url); ?>" placeholder="about.php"
                                   style="width:100%; padding:9px 12px; border:1px solid #CBD5E1; border-radius:10px;">
                        </div>
                    </div>
                </div>

                <!-- Right: Graphic, Floating Badges & Order -->
                <div>
                    <h3 style="font-size:16px; font-weight:700; color:#0B5D3B; margin-bottom:16px; display:flex; align-items:center; gap:8px;">
                        <?php echo render_icon('Laptop', 18); ?> 3. Visual & Floating Badges
                    </h3>

                    <div style="margin-bottom:16px;">
                        <label style="display:block; font-size:13px; font-weight:600; color:#334155; margin-bottom:6px;">Select Preset Graphic or Upload</label>
                        <select name="slide_image_select" id="input_slide_image_select" class="admin-input" style="width:100%; padding:10px 14px; border:1px solid #CBD5E1; border-radius:10px; margin-bottom:8px;">
                            <option value="assets/images/hero-laptop.png" <?php echo $fImage === 'assets/images/hero-laptop.png' ? 'selected' : ''; ?>>KendatTech 3D Laptop (Mockup Default)</option>
                            <option value="assets/images/services-hero-laptop.jpg" <?php echo $fImage === 'assets/images/services-hero-laptop.jpg' ? 'selected' : ''; ?>>Enterprise Software Showcase Laptop</option>
                            <option value="assets/images/about-workspace.jpg" <?php echo $fImage === 'assets/images/about-workspace.jpg' ? 'selected' : ''; ?>>Cloud Engineering Workspace</option>
                            <option value="assets/images/ai-hero-visual.png" <?php echo $fImage === 'assets/images/ai-hero-visual.png' ? 'selected' : ''; ?>>AI & Intelligent Automation Visual</option>
                            <option value="assets/images/ai-process-laptop.png" <?php echo $fImage === 'assets/images/ai-process-laptop.png' ? 'selected' : ''; ?>>AI Data Flow & Laptop</option>
                            <option value="custom" <?php echo (!str_contains($fImage, 'assets/images/')) ? 'selected' : ''; ?>>Upload Custom Graphic File Below...</option>
                        </select>
                        <input type="file" name="slide_image_file" id="input_slide_image_file" class="admin-input" accept="image/*" style="width:100%; font-size:13px;">
                        <div style="font-size:12px; color:#64748B; margin-top:4px;">Supported: PNG, JPG, WebP, SVG. Transparent background PNGs look best.</div>
                    </div>

                    <!-- Floating Badge 1 (Top Left) -->
                    <div style="background:#F8FAFC; border:1px solid #E2E8F0; border-radius:12px; padding:14px; margin-bottom:16px;">
                        <div style="font-size:13px; font-weight:700; color:#0F172A; margin-bottom:8px; display:flex; align-items:center; gap:6px;">
                            <span style="color:#10B981;">&lt;/&gt;</span> Floating Badge 1 (Top-Left)
                        </div>
                        <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:10px;">
                            <div>
                                <label style="display:block; font-size:11px; font-weight:600; color:#475569; margin-bottom:3px;">Icon</label>
                                <select name="badge1_icon" id="input_badge1_icon" style="width:100%; padding:7px; border:1px solid #CBD5E1; border-radius:8px;">
                                    <option value="Code2" <?php echo $fBadge1Icon === 'Code2' ? 'selected':''; ?>>Code2 (&lt;/&gt;)</option>
                                    <option value="TerminalSquare" <?php echo $fBadge1Icon === 'TerminalSquare' ? 'selected':''; ?>>Terminal</option>
                                    <option value="Smartphone" <?php echo $fBadge1Icon === 'Smartphone' ? 'selected':''; ?>>Smartphone</option>
                                    <option value="Cloud" <?php echo $fBadge1Icon === 'Cloud' ? 'selected':''; ?>>Cloud</option>
                                    <option value="Cpu" <?php echo $fBadge1Icon === 'Cpu' ? 'selected':''; ?>>Cpu (AI)</option>
                                    <option value="Sparkles" <?php echo $fBadge1Icon === 'Sparkles' ? 'selected':''; ?>>Sparkles</option>
                                </select>
                            </div>
                            <div>
                                <label style="display:block; font-size:11px; font-weight:600; color:#475569; margin-bottom:3px;">Title</label>
                                <input type="text" name="badge1_title" id="input_badge1_title" value="<?php echo htmlspecialchars($fBadge1Title); ?>" placeholder="Clean Code" style="width:100%; padding:7px; border:1px solid #CBD5E1; border-radius:8px;">
                            </div>
                            <div>
                                <label style="display:block; font-size:11px; font-weight:600; color:#475569; margin-bottom:3px;">Subtitle</label>
                                <input type="text" name="badge1_subtitle" id="input_badge1_sub" value="<?php echo htmlspecialchars($fBadge1Sub); ?>" placeholder="Scalable" style="width:100%; padding:7px; border:1px solid #CBD5E1; border-radius:8px;">
                            </div>
                        </div>
                    </div>

                    <!-- Floating Badge 2 (Bottom Right) -->
                    <div style="background:#F8FAFC; border:1px solid #E2E8F0; border-radius:12px; padding:14px; margin-bottom:16px;">
                        <div style="font-size:13px; font-weight:700; color:#0F172A; margin-bottom:8px; display:flex; align-items:center; gap:6px;">
                            <span style="color:#0B5D3B;"><?php echo render_icon('ShieldCheck', 16); ?></span> Floating Badge 2 (Bottom-Right)
                        </div>
                        <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:10px;">
                            <div>
                                <label style="display:block; font-size:11px; font-weight:600; color:#475569; margin-bottom:3px;">Icon</label>
                                <select name="badge2_icon" id="input_badge2_icon" style="width:100%; padding:7px; border:1px solid #CBD5E1; border-radius:8px;">
                                    <option value="ShieldCheck" <?php echo $fBadge2Icon === 'ShieldCheck' ? 'selected':''; ?>>ShieldCheck</option>
                                    <option value="Rocket" <?php echo $fBadge2Icon === 'Rocket' ? 'selected':''; ?>>Rocket</option>
                                    <option value="Zap" <?php echo $fBadge2Icon === 'Zap' ? 'selected':''; ?>>Zap</option>
                                    <option value="Database" <?php echo $fBadge2Icon === 'Database' ? 'selected':''; ?>>Database</option>
                                    <option value="Bot" <?php echo $fBadge2Icon === 'Bot' ? 'selected':''; ?>>Bot</option>
                                    <option value="Layers" <?php echo $fBadge2Icon === 'Layers' ? 'selected':''; ?>>Layers</option>
                                </select>
                            </div>
                            <div>
                                <label style="display:block; font-size:11px; font-weight:600; color:#475569; margin-bottom:3px;">Title</label>
                                <input type="text" name="badge2_title" id="input_badge2_title" value="<?php echo htmlspecialchars($fBadge2Title); ?>" placeholder="Reliable Security" style="width:100%; padding:7px; border:1px solid #CBD5E1; border-radius:8px;">
                            </div>
                            <div>
                                <label style="display:block; font-size:11px; font-weight:600; color:#475569; margin-bottom:3px;">Subtitle</label>
                                <input type="text" name="badge2_subtitle" id="input_badge2_sub" value="<?php echo htmlspecialchars($fBadge2Sub); ?>" placeholder="Your Data" style="width:100%; padding:7px; border:1px solid #CBD5E1; border-radius:8px;">
                            </div>
                        </div>
                    </div>

                    <!-- Slide Order & Status -->
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:20px;">
                        <div>
                            <label style="display:block; font-size:13px; font-weight:600; color:#334155; margin-bottom:6px;">Display Order (Sort Index)</label>
                            <input type="number" name="sort_order" class="admin-input" min="1" max="99" 
                                   value="<?php echo (int)$fSort; ?>" 
                                   style="width:100%; padding:10px 14px; border:1px solid #CBD5E1; border-radius:10px;">
                        </div>
                        <div>
                            <label style="display:block; font-size:13px; font-weight:600; color:#334155; margin-bottom:6px;">Visibility Status</label>
                            <select name="status" class="admin-input" style="width:100%; padding:10px 14px; border:1px solid #CBD5E1; border-radius:10px;">
                                <option value="active" <?php echo $fStatus === 'active' ? 'selected' : ''; ?>>Active (Visible on Homepage)</option>
                                <option value="inactive" <?php echo $fStatus === 'inactive' ? 'selected' : ''; ?>>Inactive (Hidden)</option>
                            </select>
                        </div>
                    </div>

                    <div style="display:flex; justify-content:flex-end; gap:12px; margin-top:28px;">
                        <a href="hero-slides.php" class="btn outline">Cancel</a>
                        <button type="submit" class="btn primary" style="padding:12px 28px;">
                            <?php echo render_icon('Save', 18); ?> <?php echo $editRecord ? 'Update Slide' : 'Publish Slide'; ?>
                        </button>
                    </div>
                </div>
            </div>
        </form>

        <!-- Live Visual Interactive Preview Card -->
        <div style="margin-top:36px; padding-top:24px; border-top:2px dashed #E2E8F0;">
            <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:14px;">
                <h4 style="font-size:15px; font-weight:700; color:#0F172A; margin:0; display:flex; align-items:center; gap:8px;">
                    <?php echo render_icon('Eye', 18); ?> Live Carousel Slide Preview
                </h4>
                <span style="font-size:12px; color:#10B981; background:rgba(16,185,129,0.1); padding:4px 10px; border-radius:999px; font-weight:600;">
                    Real-Time Dynamic Sync
                </span>
            </div>

            <div style="background:linear-gradient(135deg, #042617 0%, #064024 100%); border-radius:20px; padding:36px; color:#FFFFFF; position:relative; overflow:hidden; box-shadow:0 15px 35px rgba(4,38,23,0.3);">
                <div style="position:absolute; inset:0; background:linear-gradient(90deg, rgba(255,255,255,0.92) 0%, rgba(240,253,244,0.7) 100%); z-index:1;"></div>
                <div style="position:relative; z-index:2; display:grid; grid-template-columns:1.1fr 1fr; gap:32px; align-items:center;">
                    <!-- Preview Left -->
                    <div>
                        <div id="prev_badge" style="font-size:11px; font-weight:800; letter-spacing:0.12em; color:#0B5D3B; text-transform:uppercase; margin-bottom:10px;">
                            <?php echo htmlspecialchars($fBadge); ?>
                        </div>
                        <h2 id="prev_title" style="font-size:32px; font-weight:800; color:#0F172A; line-height:1.2; margin:0 0 14px 0;">
                            <?php 
                                if (!empty($fHighlight) && stripos($fTitle, $fHighlight) !== false) {
                                    echo preg_replace('/(' . preg_quote($fHighlight, '/') . ')/i', '<span style="color:#0B5D3B;">$1</span>', htmlspecialchars($fTitle));
                                } else {
                                    echo htmlspecialchars($fTitle);
                                }
                            ?>
                        </h2>
                        <p id="prev_desc" style="font-size:14px; color:#475569; line-height:1.6; margin:0 0 20px 0; max-width:440px;">
                            <?php echo htmlspecialchars($fDesc); ?>
                        </p>
                        <div style="display:flex; gap:12px; flex-wrap:wrap;">
                            <span id="prev_cta1" style="background:#0B5D3B; color:#FFFFFF; padding:10px 22px; border-radius:999px; font-weight:600; font-size:13px; display:inline-flex; align-items:center; gap:6px;">
                                <?php echo htmlspecialchars($fCta1Text); ?> &rarr;
                            </span>
                            <span id="prev_cta2" style="background:#FFFFFF; color:#0F172A; border:1px solid #CBD5E1; padding:10px 22px; border-radius:999px; font-weight:600; font-size:13px;">
                                <?php echo htmlspecialchars($fCta2Text); ?>
                            </span>
                        </div>
                    </div>

                    <!-- Preview Right -->
                    <div style="position:relative; display:flex; justify-content:center; align-items:center;">
                        <!-- Angled brand polygons -->
                        <div style="position:absolute; width:160px; height:80px; background:#10B981; opacity:0.85; transform:skew(-20deg); border-radius:12px; right:30px; top:10px; z-index:1;"></div>
                        <div style="position:absolute; width:120px; height:50px; background:#F59E0B; opacity:0.9; transform:skew(-20deg); border-radius:10px; right:10px; bottom:20px; z-index:1;"></div>
                        
                        <div style="background:#FFFFFF; border:1.5px solid rgba(255,255,255,0.9); border-radius:20px; padding:16px; box-shadow:0 18px 40px rgba(0,0,0,0.12); position:relative; z-index:2; width:100%; max-width:380px;">
                            <img id="prev_img" src="<?php echo htmlspecialchars(upload_asset_url($fImage)); ?>" alt="Preview" style="width:100%; height:auto; display:block; border-radius:12px;">

                            <!-- Badge 1 Top Left -->
                            <div style="position:absolute; top:-12px; left:-12px; background:#FFFFFF; border:1px solid #E2E8F0; border-radius:12px; padding:8px 14px; box-shadow:0 6px 18px rgba(0,0,0,0.08); display:flex; align-items:center; gap:8px;">
                                <span style="color:#10B981; font-weight:800; font-size:14px;">&lt;/&gt;</span>
                                <div>
                                    <div id="prev_b1_title" style="font-size:12px; font-weight:700; color:#0F172A;"><?php echo htmlspecialchars($fBadge1Title); ?></div>
                                    <div id="prev_b1_sub" style="font-size:10px; color:#64748B;"><?php echo htmlspecialchars($fBadge1Sub); ?></div>
                                </div>
                            </div>

                            <!-- Badge 2 Bottom Right -->
                            <div style="position:absolute; bottom:-12px; right:-12px; background:#FFFFFF; border:1px solid #E2E8F0; border-radius:12px; padding:8px 14px; box-shadow:0 6px 18px rgba(0,0,0,0.08); display:flex; align-items:center; gap:8px;">
                                <span style="color:#0B5D3B;"><?php echo render_icon('ShieldCheck', 16); ?></span>
                                <div>
                                    <div id="prev_b2_title" style="font-size:12px; font-weight:700; color:#0F172A;"><?php echo htmlspecialchars($fBadge2Title); ?></div>
                                    <div id="prev_b2_sub" style="font-size:10px; color:#64748B;"><?php echo htmlspecialchars($fBadge2Sub); ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Live Preview Script -->
    <script>
    (function() {
        const badgeInput = document.getElementById('input_badge_text');
        const titleInput = document.getElementById('input_title');
        const highlightInput = document.getElementById('input_highlight_text');
        const descInput = document.getElementById('input_description');
        const cta1Input = document.getElementById('input_cta1_text');
        const cta2Input = document.getElementById('input_cta2_text');
        const b1TitleInput = document.getElementById('input_badge1_title');
        const b1SubInput = document.getElementById('input_badge1_sub');
        const b2TitleInput = document.getElementById('input_badge2_title');
        const b2SubInput = document.getElementById('input_badge2_sub');
        const selectImage = document.getElementById('input_slide_image_select');

        function updatePreview() {
            if (badgeInput) document.getElementById('prev_badge').textContent = badgeInput.value;
            if (descInput) document.getElementById('prev_desc').textContent = descInput.value;
            if (cta1Input) document.getElementById('prev_cta1').innerHTML = cta1Input.value + ' &rarr;';
            if (cta2Input) document.getElementById('prev_cta2').textContent = cta2Input.value;
            if (b1TitleInput) document.getElementById('prev_b1_title').textContent = b1TitleInput.value;
            if (b1SubInput) document.getElementById('prev_b1_sub').textContent = b1SubInput.value;
            if (b2TitleInput) document.getElementById('prev_b2_title').textContent = b2TitleInput.value;
            if (b2SubInput) document.getElementById('prev_b2_sub').textContent = b2SubInput.value;

            if (titleInput) {
                const title = titleInput.value;
                const highlight = highlightInput ? highlightInput.value.trim() : '';
                if (highlight && title.toLowerCase().includes(highlight.toLowerCase())) {
                    const regex = new RegExp('(' + highlight.replace(/[-\/\\^$*+?.()|[\]{}]/g, '\\$&') + ')', 'gi');
                    document.getElementById('prev_title').innerHTML = title.replace(regex, '<span style="color:#0B5D3B;">$1</span>');
                } else {
                    document.getElementById('prev_title').textContent = title;
                }
            }

            if (selectImage && selectImage.value !== 'custom') {
                const baseUrl = '<?php echo $baseUrl; ?>';
                document.getElementById('prev_img').src = baseUrl + selectImage.value;
            }
        }

        [badgeInput, titleInput, highlightInput, descInput, cta1Input, cta2Input, b1TitleInput, b1SubInput, b2TitleInput, b2SubInput].forEach(el => {
            if (el) el.addEventListener('input', updatePreview);
        });
        if (selectImage) selectImage.addEventListener('change', updatePreview);
    })();
    </script>
<?php endif; ?>

<!-- Slides Table List -->
<div class="admin-projects-card" style="background:#FFFFFF; border:1px solid #E2E8F0; border-radius:18px; padding:24px; box-shadow:0 4px 20px rgba(0,0,0,0.03);">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
        <h2 style="font-size:18px; font-weight:700; color:#0F172A; margin:0;">
            Configured Hero Slides (<?php echo count($slides); ?>)
        </h2>
        <span style="font-size:13px; color:#64748B;">Slides are ordered by Sort Order ASC</span>
    </div>

    <?php if (empty($slides)): ?>
        <div style="text-align:center; padding:48px 20px; color:#64748B;">
            <div style="font-size:48px; margin-bottom:16px;">🎠</div>
            <h3 style="font-size:18px; color:#0F172A; margin:0 0 8px 0;">No Hero Slides Found</h3>
            <p style="margin:0 0 20px 0;">The carousel is currently empty. Click below to automatically seed default software engineering slides.</p>
            <form method="post" style="display:inline-block;">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="action" value="seed_defaults">
                <button type="submit" class="btn primary">
                    <?php echo render_icon('RefreshCw', 16); ?> Seed 4 Software Development Slides
                </button>
            </form>
        </div>
    <?php else: ?>
        <div style="overflow-x:auto;">
            <table class="admin-projects-table" style="width:100%; border-collapse:collapse;">
                <thead>
                    <tr style="border-bottom:2px solid #F1F5F9; text-align:left;">
                        <th style="padding:14px; width:60px;">Order</th>
                        <th style="padding:14px; width:90px;">Visual</th>
                        <th style="padding:14px; min-width:240px;">Slide Title & Eyebrow</th>
                        <th style="padding:14px; min-width:220px;">Floating Badges & Tech</th>
                        <th style="padding:14px; width:130px;">Action CTAs</th>
                        <th style="padding:14px; width:100px;">Status</th>
                        <th style="padding:14px; width:140px; text-align:right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($slides as $slide): ?>
                        <tr style="border-bottom:1px solid #F1F5F9; transition:background 0.2s ease;">
                            <td style="padding:14px; font-weight:700; color:#0B5D3B; text-align:center;">
                                #<?php echo (int)$slide['sort_order']; ?>
                            </td>
                            <td style="padding:14px;">
                                <div style="width:68px; height:46px; border-radius:8px; overflow:hidden; border:1px solid #E2E8F0; background:#0B131F; display:flex; align-items:center; justify-content:center;">
                                    <img src="<?php echo htmlspecialchars(upload_asset_url($slide['slide_image'])); ?>" 
                                         alt="Slide Graphic" 
                                         style="max-width:100%; max-height:100%; object-fit:contain;">
                                </div>
                            </td>
                            <td style="padding:14px;">
                                <div style="font-size:11px; font-weight:700; color:#0B5D3B; text-transform:uppercase; letter-spacing:0.06em; margin-bottom:2px;">
                                    <?php echo htmlspecialchars($slide['badge_text']); ?>
                                </div>
                                <div style="font-size:15px; font-weight:700; color:#0F172A; margin-bottom:4px;">
                                    <?php echo htmlspecialchars($slide['title']); ?>
                                </div>
                                <div style="font-size:13px; color:#64748B; max-width:320px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                                    <?php echo htmlspecialchars($slide['description']); ?>
                                </div>
                            </td>
                            <td style="padding:14px;">
                                <div style="display:flex; flex-direction:column; gap:4px;">
                                    <div style="font-size:12px; color:#334155; display:flex; align-items:center; gap:6px;">
                                        <span style="color:#10B981; font-weight:700;">&lt;/&gt;</span>
                                        <strong><?php echo htmlspecialchars($slide['badge1_title']); ?>:</strong>
                                        <span style="color:#64748B;"><?php echo htmlspecialchars($slide['badge1_subtitle']); ?></span>
                                    </div>
                                    <div style="font-size:12px; color:#334155; display:flex; align-items:center; gap:6px;">
                                        <span style="color:#0B5D3B;"><?php echo render_icon('ShieldCheck', 14); ?></span>
                                        <strong><?php echo htmlspecialchars($slide['badge2_title']); ?>:</strong>
                                        <span style="color:#64748B;"><?php echo htmlspecialchars($slide['badge2_subtitle']); ?></span>
                                    </div>
                                    <?php if (!empty($slide['tech_tags'])): ?>
                                        <div style="font-size:11px; color:#0B5D3B; background:rgba(11,93,59,0.08); padding:2px 8px; border-radius:4px; display:inline-block; margin-top:2px; max-width:240px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                                            <?php echo htmlspecialchars($slide['tech_tags']); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td style="padding:14px;">
                                <div style="font-size:12px; color:#0F172A; font-weight:600;">
                                    1. <?php echo htmlspecialchars($slide['primary_cta_text']); ?>
                                </div>
                                <div style="font-size:11px; color:#64748B;">
                                    2. <?php echo htmlspecialchars($slide['secondary_cta_text']); ?>
                                </div>
                            </td>
                            <td style="padding:14px;">
                                <form method="post" style="display:inline-block;">
                                    <?php echo csrf_field(); ?>
                                    <input type="hidden" name="action" value="toggle_status">
                                    <input type="hidden" name="id" value="<?php echo (int)$slide['id']; ?>">
                                    <input type="hidden" name="new_status" value="<?php echo $slide['status'] === 'active' ? 'inactive' : 'active'; ?>">
                                    <button type="submit" 
                                            class="pill" 
                                            style="cursor:pointer; border:none; padding:4px 10px; border-radius:999px; font-size:12px; font-weight:700; background:<?php echo $slide['status'] === 'active' ? 'rgba(16,185,129,0.12)' : 'rgba(100,116,139,0.12)'; ?>; color:<?php echo $slide['status'] === 'active' ? '#059669' : '#64748B'; ?>;"
                                            title="Click to toggle status">
                                        <?php echo ucfirst($slide['status']); ?>
                                    </button>
                                </form>
                            </td>
                            <td style="padding:14px; text-align:right;">
                                <div style="display:flex; justify-content:flex-end; gap:8px;">
                                    <a href="hero-slides.php?edit=<?php echo (int)$slide['id']; ?>" class="btn small outline" title="Edit Slide">
                                        <?php echo render_icon('Pencil', 14); ?> Edit
                                    </a>
                                    <form method="post" style="display:inline-block;" onsubmit="return confirm('Are you sure you want to delete this hero slide?');">
                                        <?php echo csrf_field(); ?>
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?php echo (int)$slide['id']; ?>">
                                        <button type="submit" class="btn small danger" title="Delete Slide" style="background:#FEE2E2; color:#DC2626; border:none; padding:6px 10px; border-radius:8px; cursor:pointer;">
                                            <?php echo render_icon('Trash2', 14); ?>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
