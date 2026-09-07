<?php
declare(strict_types=1);

/**
 * Hardened File Upload Helper for Kendat Integrated Services.
 */
function secure_file_upload(string $fieldName, string $subfolder = 'general', bool $isPrivate = false): ?string {
    if (!isset($_FILES[$fieldName]) || $_FILES[$fieldName]['error'] !== UPLOAD_ERR_OK) {
        return null;
    }

    $file = $_FILES[$fieldName];
    $tmpName = $file['tmp_name'];
    $originalName = basename($file['name']);

    if (!is_uploaded_file($tmpName)) {
        return null;
    }

    // Maximum File Size: 10MB
    $maxBytes = 10 * 1024 * 1024;
    if ($file['size'] > $maxBytes) {
        return null;
    }

    $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

    // Extension Allowlist
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'pdf', 'doc', 'docx', 'svg'];
    if (!in_array($ext, $allowedExtensions, true)) {
        return null;
    }

    // MIME Type Validation via finfo
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $tmpName);
    finfo_close($finfo);

    $allowedMimes = [
        'image/jpeg' => ['jpg', 'jpeg'],
        'image/png' => ['png'],
        'image/gif' => ['gif'],
        'image/webp' => ['webp'],
        'image/svg+xml' => ['svg'],
        'application/pdf' => ['pdf'],
        'application/msword' => ['doc'],
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => ['docx'],
    ];

    if (!array_key_exists($mime, $allowedMimes) || !in_array($ext, $allowedMimes[$mime], true)) {
        return null;
    }

    // SVG Security Sanitization (Reject SVG containing scripts or event handlers)
    if ($ext === 'svg' || $mime === 'image/svg+xml') {
        $svgContent = file_get_contents($tmpName);
        if (preg_match('/<script|on\w+\s*=/i', $svgContent)) {
            return null; // Malicious SVG detected
        }
    }

    // Target Storage Path
    $baseStorage = $isPrivate
        ? __DIR__ . '/../storage/uploads/'
        : __DIR__ . '/../uploads/';

    $targetDir = $baseStorage . trim($subfolder, '/');
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0755, true);
    }

    // Generate Cryptographically Random Filename
    $randomName = bin2hex(random_bytes(16)) . '.' . $ext;
    $targetPath = $targetDir . '/' . $randomName;

    if (move_uploaded_file($tmpName, $targetPath)) {
        chmod($targetPath, 0644);
        return $subfolder . '/' . $randomName;
    }

    return null;
}
