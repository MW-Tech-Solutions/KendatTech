<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/auth.php';

/**
 * Record security and administrative operations in audit_logs.
 */
function audit_log(string $action, string $resourceType, ?string $resourceId = null, ?array $details = null): void {
    try {
        $pdo = get_db();
        $adminId = is_admin() ? ($_SESSION['user_id'] ?? null) : null;
        $userId = is_logged_in() ? ($_SESSION['user_id'] ?? null) : null;
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $ua = substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255);
        $detailsJson = $details ? json_encode($details, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : null;

        $stmt = $pdo->prepare("
            INSERT INTO audit_logs (admin_id, user_id, action, resource_type, resource_id, details, ip_address, user_agent)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$adminId, $userId, $action, $resourceType, $resourceId, $detailsJson, $ip, $ua]);
    } catch (\Throwable $e) {
        // Silent fail so audit logging errors never disrupt primary user/admin transactions
        error_log("Audit log failure: " . $e->getMessage());
    }
}
