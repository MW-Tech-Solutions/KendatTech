<?php
declare(strict_types=1);

require_once __DIR__ . '/auth.php';

function generate_csrf_token(): string {
    start_app_session();
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_token(): string {
    return generate_csrf_token();
}

function csrf_input(): string {
    $token = htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8');
    return '<input type="hidden" name="csrf_token" value="' . $token . '">';
}

function validate_csrf_token(?string $submittedToken): bool {
    start_app_session();
    $sessionToken = $_SESSION['csrf_token'] ?? '';
    if (empty($sessionToken) || empty($submittedToken)) {
        return false;
    }
    return hash_equals($sessionToken, $submittedToken);
}

function require_csrf_token(): void {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
        if (!validate_csrf_token($token)) {
            http_response_code(403);
            die("<h1>403 Forbidden</h1><p>CSRF token validation failed. Please refresh the page and try again.</p>");
        }
    }
}
