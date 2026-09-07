<?php
declare(strict_types=1);

function start_app_session(): void {
    if (session_status() === PHP_SESSION_NONE) {
        if (!headers_sent()) {
            $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || ($_SERVER['SERVER_PORT'] ?? 80) == 443;
            @session_set_cookie_params([
                'lifetime' => 0,
                'path' => '/',
                'domain' => '',
                'secure' => $isHttps,
                'httponly' => true,
                'samesite' => 'Lax'
            ]);
            @ini_set('session.use_strict_mode', '1');
        }
        @session_start();
    }

    // Session Idle Timeout Enforcement (30 minutes)
    $maxIdle = 1800;
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $maxIdle)) {
        $_SESSION = [];
        if (ini_get("session.use_cookies") && !headers_sent()) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        @session_destroy();
        return;
    }
    $_SESSION['last_activity'] = time();
}

start_app_session();

function is_logged_in(): bool {
    return !empty($_SESSION['user_id']);
}

function is_admin(): bool {
    return is_logged_in() && in_array($_SESSION['user_role'] ?? '', ['admin', 'super_admin'], true);
}

function get_current_user_data(): ?array {
    if (!is_logged_in()) {
        return null;
    }
    return [
        'id' => $_SESSION['user_id'],
        'full_name' => $_SESSION['user_name'] ?? '',
        'email' => $_SESSION['user_email'] ?? '',
        'role' => $_SESSION['user_role'] ?? 'user',
    ];
}

function login_user(array $user, string $role = 'user'): void {
    if (session_status() === PHP_SESSION_NONE) {
        start_app_session();
    }
    session_regenerate_id(true);
    $_SESSION['user_id'] = (int)$user['id'];
    $_SESSION['user_name'] = $user['full_name'] ?? '';
    $_SESSION['user_email'] = $user['email'] ?? '';
    $_SESSION['user_role'] = $role;
    $_SESSION['last_activity'] = time();
}

function logout_user(): void {
    if (session_status() === PHP_SESSION_NONE) {
        start_app_session();
    }
    $_SESSION = [];
    if (ini_get("session.use_cookies") && !headers_sent()) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    @session_destroy();
}

function require_login(string $redirect = 'login.php'): void {
    if (!is_logged_in()) {
        $cleanRedirect = ltrim($redirect, './');
        header("Location: " . get_base_url() . $cleanRedirect);
        exit;
    }
}

function require_admin(string $redirect = 'admin-login.php'): void {
    if (!is_admin()) {
        $cleanRedirect = ltrim($redirect, './');
        header("Location: " . get_base_url() . $cleanRedirect);
        exit;
    }
}

