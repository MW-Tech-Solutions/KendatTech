<?php
declare(strict_types=1);

/**
 * Shared Auth Suite Layout Elements
 * Matches user mockup with dynamic logo, centered header, and dual green wave footer
 */

function render_auth_header(array $settings, string $baseUrl): void {
    $companyName = !empty($settings['company_name']) ? (string)$settings['company_name'] : 'KendatTech';
    $dynamicLogo = !empty($settings['logo']) ? upload_asset_url($settings['logo']) : '';
    ?>
    <header class="auth-top-header">
        <a href="<?php echo htmlspecialchars($baseUrl); ?>" class="auth-header-brand-link" title="<?php echo htmlspecialchars($companyName); ?>">
            <?php if (!empty($dynamicLogo)): ?>
                <img src="<?php echo htmlspecialchars($dynamicLogo); ?>" alt="<?php echo htmlspecialchars($companyName); ?>" class="auth-header-logo-img">
            <?php else: ?>
                <svg width="34" height="34" viewBox="0 0 32 32" fill="none">
                    <rect width="32" height="32" rx="8" fill="#0B5D3B"/>
                    <path d="M9 7V25M9 16L18 7M9 16L18 25" stroke="#FFFFFF" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M19 16L24 7M19 16L24 25" stroke="#10B981" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            <?php endif; ?>
            <span class="auth-header-brand-text">Kendat<span class="tech-accent">Tech</span></span>
        </a>
        <span class="auth-header-slogan">WE DESIGN &bull; WE DEVELOP &bull; WE DELIVER</span>
    </header>
    <?php
}

function render_auth_footer(array $settings): void {
    $companyName = !empty($settings['company_name']) ? (string)$settings['company_name'] : 'KendatTech';
    $slogan = !empty($settings['website_slogan']) ? (string)$settings['website_slogan'] : 'Innovating Today for a Smarter Tomorrow';
    ?>
    <footer class="auth-page-footer">
        <svg class="auth-wave-svg" viewBox="0 0 1440 90" fill="none" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M0,45 C320,85 760,10 1120,48 C1280,65 1380,50 1440,38 L1440,90 L0,90 Z" fill="#10B981"/>
            <path d="M0,60 C300,92 720,28 1080,62 C1260,78 1370,66 1440,54 L1440,90 L0,90 Z" fill="#042617"/>
        </svg>
        <div class="auth-footer-bar">
            <div class="auth-footer-brand-meta">
                <strong><?php echo htmlspecialchars($companyName); ?></strong>
                <span><?php echo htmlspecialchars($slogan); ?></span>
            </div>
            <div class="auth-footer-socials">
                <a href="<?php echo htmlspecialchars($settings['linkedin_link'] ?? '#'); ?>" class="auth-social-icon" aria-label="LinkedIn" target="_blank" rel="noopener">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-2-2 2 2 0 0 0-2 2v7h-4v-7a6 6 0 0 1 6-6z"/><rect width="4" height="12" x="2" y="9"/><circle cx="4" cy="4" r="2"/></svg>
                </a>
                <a href="<?php echo htmlspecialchars($settings['twitter_link'] ?? '#'); ?>" class="auth-social-icon" aria-label="X / Twitter" target="_blank" rel="noopener">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4l11.733 16h4.267l-11.733 -16z"/><path d="M4 20l6.768 -6.768m2.46 -2.46l6.772 -6.772"/></svg>
                </a>
                <a href="<?php echo htmlspecialchars($settings['facebook_link'] ?? '#'); ?>" class="auth-social-icon" aria-label="Facebook" target="_blank" rel="noopener">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/></svg>
                </a>
                <a href="<?php echo htmlspecialchars($settings['instagram_link'] ?? '#'); ?>" class="auth-social-icon" aria-label="Instagram" target="_blank" rel="noopener">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="20" height="20" x="2" y="2" rx="5" ry="5"/><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/><line x1="17.5" x2="17.51" y1="6.5" y2="6.5"/></svg>
                </a>
            </div>
        </div>
    </footer>
    <script>
    function toggleAuthPassword(btn) {
        var wrap = btn.closest('.auth-input-wrapper');
        if (!wrap) return;
        var inp = wrap.querySelector('input');
        if (!inp) return;
        if (inp.type === 'password') {
            inp.type = 'text';
            btn.innerHTML = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"/><path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"/><path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"/><line x1="2" x2="22" y1="2" y2="22"/></svg>';
            btn.setAttribute('aria-label', 'Hide Password');
        } else {
            inp.type = 'password';
            btn.innerHTML = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>';
            btn.setAttribute('aria-label', 'Show Password');
        }
    }
    </script>
    <?php
}
