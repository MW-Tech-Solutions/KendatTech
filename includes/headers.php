<?php
declare(strict_types=1);

/**
 * Enterprise HTTP Security Headers for Kendat Integrated Services.
 */
function send_security_headers(): void {
    if (headers_sent()) {
        return;
    }

    header("X-Content-Type-Options: nosniff");
    header("X-Frame-Options: SAMEORIGIN");
    header("X-XSS-Protection: 1; mode=block");
    header("Referrer-Policy: strict-origin-when-cross-origin");
    header("Permissions-Policy: camera=(), microphone=(), geolocation=()");

    if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
        header("Strict-Transport-Security: max-age=31536000; includeSubDomains; preload");
    }

    $csp = "default-src 'self'; " .
           "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com; " .
           "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdn.jsdelivr.net; " .
           "font-src 'self' https://fonts.gstatic.com data:; " .
           "img-src 'self' data: blob: https:; " .
           "connect-src 'self'; " .
           "frame-ancestors 'self';";

    header("Content-Security-Policy: " . $csp);
}

send_security_headers();
