<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/admin_header.php';

require_csrf_token();

$pdo = get_db();
$notice = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Handle Brand Asset File Uploads
        $uploadKeys = ['logo', 'favicon', 'hero_background_image'];
        foreach ($uploadKeys as $uKey) {
            $uploadedPath = handle_file_upload($uKey, 'settings');
            if ($uploadedPath) {
                $_POST[$uKey] = $uploadedPath;
            }
        }

        $stmt = $pdo->prepare("INSERT INTO system_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
        
        foreach ($_POST as $key => $value) {
            if (is_array($value)) {
                $value = json_encode($value);
            }
            $stmt->execute([$key, (string)$value]);
        }

        $notice = 'System settings saved successfully.';
        $settings = get_settings(); // Refresh settings cache
    } catch (Throwable $e) {
        $error = $e->getMessage();
    }
}

$values = get_settings();

$groups = [
    'Identity' => ['website_name','company_name','website_slogan','footer_text'],
    'About Content' => ['about_company','mission_statement','vision_statement','hero_title','hero_subtitle'],
    'Contact' => ['contact_email','contact_phone','whatsapp_number','office_address'],
    'Social Links' => ['facebook_link','twitter_link','instagram_link','linkedin_link','youtube_link'],
    'Theme And Metrics' => ['primary_color','secondary_color','maintenance_mode','years_experience','completed_projects','clients_served'],
];
?>

<div class="admin-page-head">
    <div>
        <span class="eyebrow">Admin module</span>
        <h1>System Settings</h1>
        <p>Control website identity, public text, contact information, social links, theme colors, counters, and maintenance mode from the database.</p>
    </div>
</div>

<?php if ($notice): ?><div class="admin-notice success"><?php echo render_icon('CheckCircle2'); ?><?php echo htmlspecialchars($notice); ?></div><?php endif; ?>
<?php if ($error): ?><div class="admin-notice error"><?php echo render_icon('TriangleAlert'); ?><?php echo htmlspecialchars($error); ?></div><?php endif; ?>

<form class="settings-panel" method="post" action="" enctype="multipart/form-data">
    <?php echo csrf_input(); ?>
    <section class="settings-section">
        <h3>Brand Assets</h3>
        <div class="brand-upload-grid">
            <div class="logo-preview-card">
                <span class="brand-mark large">
                    <?php if (!empty($values['logo'])): ?>
                        <img src="<?php echo htmlspecialchars(upload_asset_url($values['logo'])); ?>" alt="Current logo">
                    <?php else: ?>
                        K
                    <?php endif; ?>
                </span>
                <div>
                    <strong>Current logo</strong>
                    <p>This replaces the default K mark in header and footer after saving.</p>
                </div>
            </div>

            <label class="file-field">Upload new logo
                <input type="file" name="logo" accept="image/*">
            </label>
            <label class="file-field">Upload favicon
                <input type="file" name="favicon" accept="image/*">
            </label>
            <label class="file-field">Hero background image
                <input type="file" name="hero_background_image" accept="image/*">
            </label>
        </div>
    </section>

    <?php foreach ($groups as $title => $fields): ?>
        <section class="settings-section">
            <h3><?php echo htmlspecialchars($title); ?></h3>
            <div class="settings-grid">
                <?php foreach ($fields as $f): 
                    $val = $values[$f] ?? '';
                    $isTextarea = in_array($f, ['about_company','mission_statement','vision_statement','hero_subtitle','footer_text','office_address'], true);
                    $isColor = strpos($f, 'color') !== false;
                    $isEmail = strpos($f, 'email') !== false;
                ?>
                    <label style="<?php echo $isTextarea ? 'grid-column:1 / -1;' : ''; ?>">
                        <?php echo htmlspecialchars(ucwords(str_replace('_', ' ', $f))); ?>
                        <?php if ($isTextarea): ?>
                            <textarea name="<?php echo $f; ?>"><?php echo htmlspecialchars($val); ?></textarea>
                        <?php elseif ($isColor): ?>
                            <input name="<?php echo $f; ?>" type="color" value="<?php echo htmlspecialchars($val); ?>">
                        <?php elseif ($isEmail): ?>
                            <input name="<?php echo $f; ?>" type="email" value="<?php echo htmlspecialchars($val); ?>">
                        <?php else: ?>
                            <input name="<?php echo $f; ?>" type="text" value="<?php echo htmlspecialchars($val); ?>">
                        <?php endif; ?>
                    </label>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endforeach; ?>

    <button class="btn primary" type="submit"><?php echo render_icon('Save'); ?>Save All Settings</button>
</form>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
