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

<style>
.about-futuristic-wrapper {
  position: relative !important;
  background: #040915 !important;
  background: radial-gradient(circle at 10% 20%, rgba(0, 135, 255, 0.2) 0%, transparent 45%),
              radial-gradient(circle at 90% 80%, rgba(0, 229, 255, 0.15) 0%, transparent 50%),
              radial-gradient(circle at 50% 50%, #071026 0%, #030712 100%) !important;
  color: #ffffff !important;
  padding: 65px 24px 75px !important;
  overflow: hidden !important;
  min-height: 85vh !important;
  box-sizing: border-box !important;
}

.about-futuristic-wrapper::before {
  content: '' !important;
  position: absolute !important;
  top: 0 !important; left: 0 !important; right: 0 !important; bottom: 0 !important;
  background-image: 
    linear-gradient(rgba(0, 195, 255, 0.04) 1px, transparent 1px),
    linear-gradient(90deg, rgba(0, 195, 255, 0.04) 1px, transparent 1px) !important;
  background-size: 40px 40px !important;
  pointer-events: none !important;
  z-index: 1 !important;
}

.about-top-bar {
  display: flex !important;
  justify-content: flex-end !important;
  align-items: center !important;
  margin-bottom: 32px !important;
  position: relative !important;
  z-index: 2 !important;
}

.about-top-motto {
  font-family: 'Poppins', sans-serif !important;
  font-size: 11px !important;
  font-weight: 800 !important;
  letter-spacing: 0.25em !important;
  color: #94a3b8 !important;
  text-transform: uppercase !important;
}

.about-handwritten-overlay {
  position: absolute !important;
  top: -10px !important;
  right: 25px !important;
  font-family: 'Caveat', 'Brush Script MT', cursive, sans-serif !important;
  font-size: 32px !important;
  color: rgba(148, 163, 184, 0.3) !important;
  transform: rotate(-8deg) !important;
  pointer-events: none !important;
  z-index: 1 !important;
  line-height: 1.1 !important;
  text-align: right !important;
}

.about-main-layout {
  display: grid !important;
  grid-template-columns: 1fr 1.35fr !important;
  gap: 36px !important;
  align-items: start !important;
  position: relative !important;
  z-index: 2 !important;
}

/* Left Hero Column */
.about-hero-col {
  display: flex !important;
  flex-direction: column !important;
  gap: 22px !important;
}

.about-badge-pill {
  display: inline-flex !important;
  align-items: center !important;
  gap: 8px !important;
  padding: 6px 18px !important;
  border-radius: 999px !important;
  background: rgba(0, 229, 255, 0.08) !important;
  border: 1px solid rgba(0, 229, 255, 0.4) !important;
  color: #00e5ff !important;
  font-family: 'Poppins', sans-serif !important;
  font-size: 12px !important;
  font-weight: 800 !important;
  letter-spacing: 0.15em !important;
  text-transform: uppercase !important;
  width: fit-content !important;
  box-shadow: 0 0 15px rgba(0, 229, 255, 0.15) !important;
}

.about-headline {
  font-family: 'Montserrat', sans-serif !important;
  font-size: 2.7rem !important;
  font-weight: 900 !important;
  line-height: 1.15 !important;
  color: #ffffff !important;
  letter-spacing: -0.02em !important;
  margin: 0 !important;
}

.about-headline .text-accent {
  background: linear-gradient(135deg, #00e5ff 0%, #0087ff 100%) !important;
  -webkit-background-clip: text !important;
  -webkit-text-fill-color: transparent !important;
}

.about-description {
  font-family: 'Open Sans', sans-serif !important;
  font-size: 15px !important;
  line-height: 1.65 !important;
  color: #94a3b8 !important;
  margin: 0 !important;
}

.about-features-duo {
  display: grid !important;
  grid-template-columns: 1fr 1fr !important;
  gap: 16px !important;
  margin-top: 6px !important;
}

.about-feature-box {
  display: flex !important;
  align-items: center !important;
  gap: 14px !important;
  padding: 16px !important;
  border-radius: 16px !important;
  background: rgba(15, 23, 42, 0.65) !important;
  border: 1px solid rgba(0, 135, 255, 0.25) !important;
  backdrop-filter: blur(12px) !important;
  -webkit-backdrop-filter: blur(12px) !important;
  transition: all 0.3s ease !important;
}

.about-feature-box:hover {
  border-color: rgba(0, 229, 255, 0.6) !important;
  box-shadow: 0 8px 24px rgba(0, 135, 255, 0.2) !important;
  transform: translateY(-2px) !important;
}

.about-feature-icon {
  width: 44px !important;
  height: 44px !important;
  border-radius: 12px !important;
  background: linear-gradient(135deg, rgba(0, 135, 255, 0.2), rgba(0, 229, 255, 0.1)) !important;
  border: 1px solid rgba(0, 135, 255, 0.4) !important;
  display: flex !important;
  align-items: center !important;
  justify-content: center !important;
  color: #00e5ff !important;
  flex-shrink: 0 !important;
}

.about-feature-icon svg {
  width: 22px !important;
  height: 22px !important;
  stroke: #00e5ff !important;
}

.about-feature-text h4 {
  font-family: 'Montserrat', sans-serif !important;
  font-size: 13px !important;
  font-weight: 800 !important;
  color: #ffffff !important;
  margin: 0 0 2px 0 !important;
}

.about-feature-text p {
  font-size: 11px !important;
  color: #64748b !important;
  margin: 0 !important;
}

.about-cta-group {
  display: flex !important;
  align-items: center !important;
  gap: 20px !important;
  margin-top: 10px !important;
  flex-wrap: wrap !important;
}

.about-btn-glow {
  display: inline-flex !important;
  align-items: center !important;
  gap: 10px !important;
  padding: 14px 28px !important;
  border-radius: 999px !important;
  background: linear-gradient(90deg, #0072ff 0%, #00c6ff 100%) !important;
  color: #ffffff !important;
  font-family: 'Poppins', sans-serif !important;
  font-size: 14px !important;
  font-weight: 700 !important;
  text-decoration: none !important;
  box-shadow: 0 0 25px rgba(0, 114, 255, 0.5) !important;
  transition: all 0.3s ease !important;
}

.about-btn-glow:hover {
  box-shadow: 0 0 35px rgba(0, 198, 255, 0.8) !important;
  transform: translateY(-2px) !important;
}

.about-btn-subtle {
  color: #94a3b8 !important;
  font-family: 'Poppins', sans-serif !important;
  font-size: 14px !important;
  font-weight: 600 !important;
  text-decoration: none !important;
  display: inline-flex !important;
  align-items: center !important;
  gap: 8px !important;
  transition: color 0.2s ease !important;
}

.about-btn-subtle:hover {
  color: #00e5ff !important;
}

/* Right Team Members Column */
.about-team-col {
  display: flex !important;
  flex-direction: column !important;
  gap: 20px !important;
}

.team-members-grid {
  display: grid !important;
  grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)) !important;
  gap: 20px !important;
}

.team-card-neon {
  position: relative !important;
  border-radius: 20px !important;
  background: rgba(11, 22, 44, 0.85) !important;
  border: 1px solid rgba(0, 195, 255, 0.45) !important;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5), inset 0 0 20px rgba(0, 195, 255, 0.1) !important;
  backdrop-filter: blur(16px) !important;
  -webkit-backdrop-filter: blur(16px) !important;
  overflow: hidden !important;
  display: flex !important;
  flex-direction: column !important;
  transition: all 0.35s cubic-bezier(0.16, 1, 0.3, 1) !important;
}

.team-card-neon:hover {
  border-color: rgba(0, 229, 255, 0.85) !important;
  box-shadow: 0 16px 40px rgba(0, 195, 255, 0.35), inset 0 0 25px rgba(0, 229, 255, 0.2) !important;
  transform: translateY(-5px) !important;
}

.team-card-photo-box {
  width: 100% !important;
  height: 270px !important;
  position: relative !important;
  overflow: hidden !important;
  background: linear-gradient(180deg, #0b152c 0%, #060e20 100%) !important;
  display: flex !important;
  align-items: center !important;
  justify-content: center !important;
}

.team-card-photo-img {
  width: 100% !important;
  height: 100% !important;
  object-fit: cover !important;
  object-position: top center !important;
  transition: transform 0.4s ease !important;
}

.team-card-neon:hover .team-card-photo-img {
  transform: scale(1.04) !important;
}

.team-card-photo-fallback {
  width: 100% !important;
  height: 100% !important;
  display: flex !important;
  align-items: center !important;
  justify-content: center !important;
  color: rgba(0, 195, 255, 0.5) !important;
  background: radial-gradient(circle at center, rgba(0, 135, 255, 0.2) 0%, rgba(6, 14, 32, 1) 70%) !important;
}

.team-card-photo-fallback svg {
  width: 64px !important;
  height: 64px !important;
  stroke: #00e5ff !important;
}

.team-card-content {
  padding: 16px !important;
  display: flex !important;
  flex-direction: column !important;
  gap: 6px !important;
  flex-grow: 1 !important;
  background: linear-gradient(180deg, rgba(8, 17, 36, 0.95) 0%, rgba(4, 9, 21, 0.98) 100%) !important;
  border-top: 1px solid rgba(0, 195, 255, 0.2) !important;
}

.team-card-role-title {
  font-family: 'Montserrat', sans-serif !important;
  font-size: 14px !important;
  font-weight: 800 !important;
  color: #ffffff !important;
  margin: 0 !important;
  line-height: 1.2 !important;
}

.team-card-name-title {
  font-family: 'Poppins', sans-serif !important;
  font-size: 13px !important;
  font-weight: 600 !important;
  color: #00e5ff !important;
  margin: 0 !important;
}

.team-card-bio-text {
  font-size: 12px !important;
  color: #94a3b8 !important;
  line-height: 1.45 !important;
  margin: 4px 0 0 0 !important;
  display: -webkit-box !important;
  -webkit-line-clamp: 3 !important;
  -webkit-box-orient: vertical !important;
  overflow: hidden !important;
}

.team-card-tags {
  margin-top: auto !important;
  padding-top: 10px !important;
  font-family: 'Poppins', sans-serif !important;
  font-size: 10px !important;
  font-weight: 700 !important;
  letter-spacing: 0.08em !important;
  color: rgba(148, 163, 184, 0.8) !important;
  text-transform: uppercase !important;
  border-top: 1px dashed rgba(255, 255, 255, 0.12) !important;
  word-break: break-word !important;
}

.team-card-social-links {
  display: flex !important;
  align-items: center !important;
  gap: 10px !important;
  margin-top: 8px !important;
}

.team-card-social-links a {
  color: #64748b !important;
  transition: color 0.2s ease !important;
}

.team-card-social-links a:hover {
  color: #00e5ff !important;
}

.team-card-social-links svg {
  width: 16px !important;
  height: 16px !important;
  stroke: currentColor !important;
}

/* Bottom Globe & Footer Accent */
.about-globe-accent-bar {
  display: flex !important;
  justify-content: flex-end !important;
  align-items: center !important;
  margin-top: 40px !important;
  padding-top: 20px !important;
  border-top: 1px solid rgba(0, 195, 255, 0.2) !important;
  position: relative !important;
  z-index: 2 !important;
}

.about-globe-text-brand {
  font-family: 'Poppins', sans-serif !important;
  font-size: 11px !important;
  font-weight: 900 !important;
  letter-spacing: 0.2em !important;
  color: rgba(148, 163, 184, 0.7) !important;
  text-transform: uppercase !important;
  text-align: right !important;
}

/* Responsive adjustments */
@media (max-width: 1024px) {
  .about-main-layout {
    grid-template-columns: 1fr !important;
    gap: 32px !important;
  }
  .about-headline {
    font-size: 2.2rem !important;
  }
}

@media (max-width: 640px) {
  .about-features-duo {
    grid-template-columns: 1fr !important;
  }
  .team-members-grid {
    grid-template-columns: 1fr !important;
  }
  .about-headline {
    font-size: 1.8rem !important;
  }
  .about-cta-group {
    flex-direction: column !important;
    align-items: stretch !important;
  }
.team-card-neon {
  cursor: pointer !important;
}

/* Person Summary Modal */
.team-bio-modal-backdrop {
  position: fixed !important;
  top: 0 !important; left: 0 !important; right: 0 !important; bottom: 0 !important;
  background: rgba(4, 9, 21, 0.88) !important;
  backdrop-filter: blur(14px) !important;
  -webkit-backdrop-filter: blur(14px) !important;
  z-index: 100000 !important;
  display: flex !important;
  align-items: center !important;
  justify-content: center !important;
  padding: 20px !important;
}

.team-bio-modal-card {
  position: relative !important;
  width: 100% !important;
  max-width: 660px !important;
  background: linear-gradient(145deg, rgba(11, 22, 44, 0.98) 0%, rgba(6, 14, 32, 0.99) 100%) !important;
  border: 1px solid rgba(0, 229, 255, 0.5) !important;
  box-shadow: 0 20px 50px rgba(0, 195, 255, 0.35), inset 0 0 30px rgba(0, 195, 255, 0.15) !important;
  border-radius: 24px !important;
  padding: 32px !important;
  color: #ffffff !important;
  display: grid !important;
  grid-template-columns: 180px 1fr !important;
  gap: 24px !important;
  align-items: start !important;
}

.team-bio-modal-close {
  position: absolute !important;
  top: 16px !important;
  right: 18px !important;
  background: rgba(255, 255, 255, 0.1) !important;
  border: 1px solid rgba(255, 255, 255, 0.2) !important;
  color: #00e5ff !important;
  font-size: 22px !important;
  width: 38px !important;
  height: 38px !important;
  border-radius: 50% !important;
  display: flex !important;
  align-items: center !important;
  justify-content: center !important;
  cursor: pointer !important;
  transition: all 0.2s ease !important;
}

.team-bio-modal-close:hover {
  background: rgba(0, 229, 255, 0.25) !important;
  transform: scale(1.08) !important;
}

.team-bio-modal-photo-wrap {
  width: 100% !important;
  height: 220px !important;
  border-radius: 18px !important;
  overflow: hidden !important;
  background: rgba(0, 135, 255, 0.15) !important;
  border: 1px solid rgba(0, 229, 255, 0.3) !important;
}

.team-bio-modal-info {
  display: flex !important;
  flex-direction: column !important;
  gap: 8px !important;
}

.team-bio-modal-role {
  font-family: 'Montserrat', sans-serif !important;
  font-size: 13px !important;
  font-weight: 800 !important;
  color: #00e5ff !important;
  text-transform: uppercase !important;
  letter-spacing: 0.08em !important;
}

.team-bio-modal-name {
  font-family: 'Poppins', sans-serif !important;
  font-size: 22px !important;
  font-weight: 800 !important;
  color: #ffffff !important;
  margin: 0 !important;
}

.team-bio-modal-specialties {
  font-size: 11px !important;
  font-weight: 700 !important;
  letter-spacing: 0.06em !important;
  color: rgba(148, 163, 184, 0.85) !important;
  text-transform: uppercase !important;
}

.team-bio-modal-bio {
  font-size: 13.5px !important;
  line-height: 1.6 !important;
  color: #cbd5e1 !important;
  margin-top: 8px !important;
}

@media (max-width: 640px) {
  .team-features-duo { grid-template-columns: 1fr !important; }
  .team-members-grid { grid-template-columns: 1fr !important; }
  .about-headline { font-size: 1.8rem !important; }
  .about-cta-group { flex-direction: column !important; align-items: stretch !important; }
  .about-btn-glow { justify-content: center !important; }
  .team-bio-modal-card {
    grid-template-columns: 1fr !important;
    max-height: 85vh !important;
    overflow-y: auto !important;
    padding: 22px !important;
  }
}
</style>

<main class="about-futuristic-wrapper">
    <div class="container-fluid px-lg-5">
        
        <!-- Top Motto Bar -->
        <div class="about-top-bar">
            <!-- <div class="about-top-motto">PEOPLE &times; TECHNOLOGY &times; A BRIGHTER TOMORROW</div> -->
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
                        <?php foreach ($teamMembers as $member): 
                            $photoUrl = !empty($member['photo']) ? upload_asset_url($member['photo']) : '';
                        ?>
                            <div class="team-card-neon" 
                                 data-name="<?php echo htmlspecialchars($member['name']); ?>"
                                 data-role="<?php echo htmlspecialchars($member['role_title']); ?>"
                                 data-specialties="<?php echo htmlspecialchars($member['specialties'] ?? ''); ?>"
                                 data-bio="<?php echo htmlspecialchars($member['bio'] ?? ''); ?>"
                                 data-photo="<?php echo htmlspecialchars($photoUrl); ?>"
                                 data-linkedin="<?php echo htmlspecialchars($member['linkedin_url'] ?? ''); ?>"
                                 data-github="<?php echo htmlspecialchars($member['github_url'] ?? ''); ?>">
                                <div class="team-card-photo-box">
                                    <?php if (!empty($member['photo'])): ?>
                                        <img src="<?php echo htmlspecialchars($photoUrl); ?>" alt="<?php echo htmlspecialchars($member['name']); ?>" class="team-card-photo-img">
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

<!-- Interactive Person Brief Summary Modal -->
<div class="team-bio-modal-backdrop" id="teamBioModal" style="display:none;">
    <div class="team-bio-modal-card">
        <button type="button" class="team-bio-modal-close" id="teamBioModalClose">&times;</button>
        <div class="team-bio-modal-photo-wrap" id="modalPhotoWrap"></div>
        <div class="team-bio-modal-info">
            <span class="team-bio-modal-role" id="modalRole"></span>
            <h2 class="team-bio-modal-name" id="modalName"></h2>
            <div class="team-bio-modal-specialties" id="modalSpecialties"></div>
            <div class="team-bio-modal-bio" id="modalBio"></div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('teamBioModal');
    const closeBtn = document.getElementById('teamBioModalClose');
    if (!modal) return;

    document.querySelectorAll('.team-card-neon').forEach(card => {
        card.addEventListener('click', (e) => {
            if (e.target.closest('a')) return;
            
            const name = card.dataset.name || '';
            const role = card.dataset.role || '';
            const specialties = card.dataset.specialties || '';
            const bio = card.dataset.bio || '';
            const photo = card.dataset.photo || '';

            document.getElementById('modalName').textContent = name;
            document.getElementById('modalRole').textContent = role;
            document.getElementById('modalSpecialties').textContent = specialties;
            document.getElementById('modalBio').textContent = bio || 'Executive team member driving software engineering and digital transformation.';
            
            const photoWrap = document.getElementById('modalPhotoWrap');
            if (photo) {
                photoWrap.innerHTML = `<img src="${photo}" alt="${name}" style="width:100%; height:100%; object-fit:cover; border-radius:18px;">`;
            } else {
                photoWrap.innerHTML = `<div style="width:100%; height:100%; display:flex; align-items:center; justify-content:center; background:rgba(0,135,255,0.15); border-radius:18px; color:#00e5ff; font-size:56px;">👤</div>`;
            }

            modal.style.display = 'flex';
        });
    });

    closeBtn?.addEventListener('click', () => {
        modal.style.display = 'none';
    });

    modal.addEventListener('click', (e) => {
        if (e.target === modal) modal.style.display = 'none';
    });
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>


