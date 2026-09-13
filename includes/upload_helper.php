<?php
declare(strict_types=1);

/**
 * Hardened File Upload Helper for Kendat Integrated Services.
 * Features multi-tiered fallback for PHP environments where fileinfo extension (finfo_open) is not enabled.
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

    // MIME Type Validation with fallback if PHP fileinfo extension is disabled on host
    $mime = null;
    if (function_exists('finfo_open') && defined('FILEINFO_MIME_TYPE')) {
        $finfo = @finfo_open(FILEINFO_MIME_TYPE);
        if ($finfo) {
            $mime = @finfo_file($finfo, $tmpName);
            @finfo_close($finfo);
        }
    }
    if (!$mime && function_exists('mime_content_type')) {
        $mime = @mime_content_type($tmpName);
    }
    if (!$mime && function_exists('getimagesize') && in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'], true)) {
        $imgInfo = @getimagesize($tmpName);
        if (!empty($imgInfo['mime'])) {
            $mime = $imgInfo['mime'];
        }
    }
    if (!$mime) {
        $mime = strtolower(trim((string)($file['type'] ?? '')));
    }

    $allowedMimes = [
        'image/jpeg' => ['jpg', 'jpeg'],
        'image/pjpeg' => ['jpg', 'jpeg'],
        'image/png' => ['png'],
        'image/gif' => ['gif'],
        'image/webp' => ['webp'],
        'image/svg+xml' => ['svg'],
        'text/xml' => ['svg'],
        'application/pdf' => ['pdf'],
        'application/x-pdf' => ['pdf'],
        'application/msword' => ['doc'],
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => ['docx'],
        'application/octet-stream' => ['jpg', 'jpeg', 'png', 'gif', 'webp', 'pdf', 'doc', 'docx', 'svg'],
    ];

    if (!empty($mime) && array_key_exists($mime, $allowedMimes)) {
        if (!in_array($ext, $allowedMimes[$mime], true)) {
            return null;
        }
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
