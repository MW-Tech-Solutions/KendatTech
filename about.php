<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/header.php';

$db = get_db();
try {
    $stmt = $db->query("SELECT * FROM team_members WHERE status = 'active' ORDER BY sort_order ASC, id ASC");
    $teamMembers = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
    $teamMembers = [];
}

if (empty($teamMembers)) {
    $teamMembers = [
        [
            'name' => 'Maher Adamu',
            'role_title' => 'CEO & Founder',
            'specialties' => 'LEADERSHIP • STRATEGY',
            'photo' => 'assets/images/team-maher.jpg',
            'bio' => 'Visionary leader with a passion for technology and innovation. Maher drives the strategic direction of KendatTech and ensures we deliver exceptional value to our clients.',
            'linkedin_url' => '#',
            'github_url' => '#'
        ],
        [
            'name' => 'Usman Ibrahim',
            'role_title' => 'Lead Developer',
            'specialties' => 'FULL-STACK • PHP • ARCHITECTURE',
            'photo' => '',
            'bio' => 'Full-stack developer with expertise in modern web technologies. Usman leads our development team in building scalable and high-performance solutions.',
            'linkedin_url' => '#',
            'github_url' => '#'
        ],
        [
            'name' => 'Aisha Bello',
            'role_title' => 'UI/UX Designer',
            'specialties' => 'UI/UX • PRODUCT DESIGN',
            'photo' => '',
            'bio' => 'Creative designer focused on user-centered experiences. Aisha designs intuitive and beautiful interfaces that make technology simple and accessible.',
            'linkedin_url' => '#',
            'github_url' => '#'
        ],
        [
            'name' => 'Abdullahi Musa',
            'role_title' => 'Backend Developer',
            'specialties' => 'BACKEND • CLOUD • APIs',
            'photo' => '',
            'bio' => 'Specializes in building secure, reliable, and efficient server-side systems. Abdullahi ensures our applications run smoothly and handle real-world challenges.',
            'linkedin_url' => '#',
            'github_url' => '#'
        ],
        [
            'name' => 'Sadiq Hassan',
            'role_title' => 'Frontend Developer',
            'specialties' => 'FRONTEND • JS • INTERFACES',
            'photo' => '',
            'bio' => 'Passionate about creating responsive and engaging web experiences. Sadiq turns designs into clean, interactive, and high-performance interfaces.',
            'linkedin_url' => '#',
            'github_url' => '#'
        ],
        [
            'name' => 'Fatima Usman',
            'role_title' => 'IT Support & Operations',
            'specialties' => 'DEVOPS • SUPPORT • INFRASTRUCTURE',
            'photo' => '',
            'bio' => 'Keeps everything running smoothly. Fatima provides technical support and ensures our systems are always available and secure.',
            'linkedin_url' => '#',
            'github_url' => '#'
        ]
    ];
}

$aboutCompany = $settings['about_company'] ?? 'KendatTech is a forward-thinking technology company focused on building high-quality, scalable and secure software solutions for businesses of all sizes. We combine innovation, expertise and passion to deliver digital products that make a real impact.';
?>

<main class="kt-main-wrapper">

    <!-- Meet Our Team Section (Matching User Mockup) -->
    <section class="kt-team-showcase-section">
        <div class="kt-container-fluid">
            
            <!-- Top Header & Mini Features Row -->
            <div class="kt-team-header-row">
                <!-- Left Title & Description -->
                <div class="kt-team-header-left">
                    <div class="kt-eyebrow-line-wrap">
                        <span class="kt-eyebrow-text">MEET OUR TEAM</span>
                        <span class="kt-eyebrow-line"></span>
                    </div>
                    <h2 class="kt-team-main-title">The People Behind <span class="text-green">KendatTech</span></h2>
                    <p class="kt-team-main-sub">
                        We are a team of passionate professionals, innovators, and problem solvers, working together to build technology solutions that make a real impact. Our diverse expertise and shared vision drive KendatTech's success and our clients' growth.
                    </p>
                </div>

                <!-- Center 2x2 Mini Feature Grid -->
                <div class="kt-team-mini-features">
                    <div class="kt-mini-feature-box">
                        <div class="kt-mini-icon-circle"><?php echo render_icon('Users', 20); ?></div>
                        <div class="kt-mini-feature-text">
                            <h4>Talented Team</h4>
                            <p>Skilled professionals with diverse expertise.</p>
                        </div>
                    </div>
                    <div class="kt-mini-feature-box">
                        <div class="kt-mini-icon-circle"><?php echo render_icon('Sparkles', 20); ?></div>
                        <div class="kt-mini-feature-text">
                            <h4>Innovative Mindset</h4>
                            <p>Turning ideas into impactful solutions.</p>
                        </div>
                    </div>
                    <div class="kt-mini-feature-box">
                        <div class="kt-mini-icon-circle"><?php echo render_icon('ShieldCheck', 20); ?></div>
                        <div class="kt-mini-feature-text">
                            <h4>Client Focused</h4>
                            <p>Your success is our priority.</p>
                        </div>
                    </div>
                    <div class="kt-mini-feature-box">
                        <div class="kt-mini-icon-circle"><?php echo render_icon('Rocket', 20); ?></div>
                        <div class="kt-mini-feature-text">
                            <h4>Growth Together</h4>
                            <p>Building a better digital future, together.</p>
                        </div>
                    </div>
                </div>

                <!-- Right Handwritten Script Motto Overlay -->
                <div class="kt-team-script-motto">
                    <span>Great</span>
                    <span>People</span>
                    <span>Build</span>
                    <span>Great</span>
                    <span class="highlight">Solutions</span>
                </div>
            </div>

            <!-- Team Member Cards 6-Column Grid Row -->
            <div class="kt-team-cards-grid">
                <?php if (!empty($teamMembers)): ?>
                    <?php foreach ($teamMembers as $member): 
                        $memberName = (string)($member['name'] ?? 'Team Member');
                        $memberRole = (string)($member['role_title'] ?? $member['position'] ?? 'Team Member');
                        $memberBio = (string)($member['bio'] ?? 'Dedicated technical specialist at KendatTech.');
                        $photoFile = $member['photo'] ?? $member['image_path'] ?? '';
                        $memberPhoto = !empty($photoFile) ? upload_asset_url($photoFile) : '';
                        $linkedin = !empty($member['linkedin_url']) ? $member['linkedin_url'] : '#';
                        $github = !empty($member['github_url']) ? $member['github_url'] : '#';
                    ?>
                        <article class="kt-card-v2">
                            <div class="kt-card-v2-img">
                                <?php if (!empty($memberPhoto)): ?>
                                    <img src="<?php echo htmlspecialchars($memberPhoto); ?>" alt="<?php echo htmlspecialchars($memberName); ?>">
                                <?php else: ?>
                                    <div class="kt-card-v2-fallback">
                                        <span><?php echo htmlspecialchars(strtoupper(substr($memberName, 0, 1))); ?></span>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="kt-card-v2-body">
                                <h3 class="kt-card-v2-name"><?php echo htmlspecialchars($memberName); ?></h3>
                                <span class="kt-card-v2-role"><?php echo htmlspecialchars($memberRole); ?></span>
                                <p class="kt-card-v2-bio"><?php echo htmlspecialchars($memberBio); ?></p>
                                <div class="kt-card-v2-socials">
                                    <a href="<?php echo htmlspecialchars($linkedin); ?>" aria-label="LinkedIn"><?php echo render_icon('Linkedin', 13); ?></a>
                                    <a href="#" aria-label="Twitter"><?php echo render_icon('Twitter', 13); ?></a>
                                    <a href="<?php echo htmlspecialchars($github); ?>" aria-label="GitHub"><?php echo render_icon('Github', 13); ?></a>
                                </div>
                            </div>
                        </article>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Join Our Growing Team Bottom Banner -->
            <div class="kt-join-team-banner">
                <div class="kt-join-banner-left">
                    <div class="kt-join-icon-circle">
                        <?php echo render_icon('Users', 24); ?>
                    </div>
                    <div class="kt-join-text">
                        <h3>Join Our Growing Team</h3>
                        <p>We're always looking for talented individuals who are passionate about technology and innovation. Be part of something great.</p>
                    </div>
                </div>
                <div class="kt-join-banner-right">
                    <a href="<?php echo $baseUrl; ?>book-appointment.php" class="kt-btn-join-outline">
                        View Careers <?php echo render_icon('ArrowRight', 16); ?>
                    </a>
                </div>
            </div>

        </div>
    </section>

    <!-- Company Story & Mission/Vision Section -->
    <section class="kt-about-section">
        <div class="kt-container">
            <div class="kt-about-grid">
                <!-- Left Story Column -->
                <div class="kt-about-left">
                    <span class="kt-eyebrow">OUR STORY & VISION</span>
                    <h2 class="kt-section-title">Engineered for Reliability. Built for the Future.</h2>
                    <p class="kt-body-text">
                        <?php echo htmlspecialchars($aboutCompany); ?>
                    </p>
                    <p class="kt-body-text">
                        From high-speed institutional management portals to custom web applications, mobile platforms, and AI automation engines, KendatTech provides end-to-end digital solutions engineered to scale nationwide and globally.
                    </p>

                    <!-- Stats Row -->
                    <div class="kt-stats-row">
                        <div class="kt-stat-block">
                            <span class="kt-stat-number">50+</span>
                            <span class="kt-stat-label">Projects Completed</span>
                        </div>
                        <div class="kt-stat-block">
                            <span class="kt-stat-number">20+</span>
                            <span class="kt-stat-label">Happy Clients</span>
                        </div>
                        <div class="kt-stat-block">
                            <span class="kt-stat-number">3+</span>
                            <span class="kt-stat-label">Years Experience</span>
                        </div>
                    </div>
                </div>

                <!-- Right Workspace Image & Cards -->
                <div class="kt-about-right">
                    <div class="kt-about-img-wrapper">
                        <img src="<?php echo $baseUrl; ?>assets/images/about-workspace.jpg" alt="KendatTech Workspace" class="kt-about-img">

                        <div class="kt-mission-card top">
                            <div class="kt-card-icon-circle green"><?php echo render_icon('Target', 20); ?></div>
                            <div>
                                <h4>Our Mission</h4>
                                <p>To empower businesses with innovative and reliable technology solutions that drive growth, efficiency and long-term success.</p>
                            </div>
                        </div>

                        <div class="kt-mission-card bottom">
                            <div class="kt-card-icon-circle green"><?php echo render_icon('Compass', 20); ?></div>
                            <div>
                                <h4>Our Vision</h4>
                                <p>To be a leading technology company known for excellence, innovation and customer satisfaction.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Core Values Section -->
    <section class="kt-services-section">
        <div class="kt-container">
            <div class="kt-section-head-center">
                <span class="kt-eyebrow">OUR CORE VALUES</span>
                <h2 class="kt-section-title">What Drives Our Work</h2>
                <p class="kt-section-sub">Principles that guide every line of code we write and every solution we deliver.</p>
            </div>

            <div class="kt-feature-grid">
                <div class="kt-feature-item">
                    <div class="kt-feature-icon"><?php echo render_icon('ShieldCheck', 20); ?></div>
                    <div class="kt-feature-info">
                        <h3>Uncompromising Security</h3>
                        <p>PDO prepared queries and data encryption built into every system.</p>
                    </div>
                </div>
                <div class="kt-feature-item">
                    <div class="kt-feature-icon"><?php echo render_icon('Sparkles', 20); ?></div>
                    <div class="kt-feature-info">
                        <h3>High Speed Performance</h3>
                        <p>Optimized database pipelines delivering response times under 50ms.</p>
                    </div>
                </div>
                <div class="kt-feature-item">
                    <div class="kt-feature-icon"><?php echo render_icon('BrainCircuit', 20); ?></div>
                    <div class="kt-feature-info">
                        <h3>Pioneering Innovation</h3>
                        <p>Integrating artificial intelligence copilots and modern cloud infrastructure.</p>
                    </div>
                </div>
                <div class="kt-feature-item">
                    <div class="kt-feature-icon"><?php echo render_icon('Users', 20); ?></div>
                    <div class="kt-feature-info">
                        <h3>Dedicated Support</h3>
                        <p>Direct engineering desks providing ongoing maintenance and upgrades.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="kt-cta-section">
        <div class="kt-container">
            <div class="kt-cta-card">
                <div class="kt-cta-content">
                    <span class="kt-cta-eyebrow">READY TO GET STARTED?</span>
                    <h2 class="kt-cta-title">Let's Build Something Great Together</h2>
                    <p class="kt-cta-sub">Partner with KendatTech and turn your ideas into powerful digital solutions.</p>
                </div>
                <div class="kt-cta-action">
                    <a href="<?php echo $baseUrl; ?>book-appointment.php" class="kt-btn-white-pill">
                        Get Started <?php echo render_icon('ArrowRight', 16); ?>
                    </a>
                </div>
            </div>
        </div>
    </section>

</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

