<?php
declare(strict_types=1);

/**
 * Enterprise Automated Test Runner for Kendat Integrated Services.
 */

echo "====================================================\n";
echo " KENDAT INTEGRATED SERVICES - AUTOMATED TEST SUITE \n";
echo "====================================================\n\n";

$passCount = 0;
$failCount = 0;

function assert_test(string $name, bool $condition, string $failureReason = ''): void {
    global $passCount, $failCount;
    if ($condition) {
        echo " [PASS] {$name}\n";
        $passCount++;
    } else {
        echo " [FAIL] {$name}" . ($failureReason ? " - {$failureReason}" : "") . "\n";
        $failCount++;
    }
}

// 1. PHP Syntax Check
echo "--- 1. PHP Syntax Validation ---\n";
$phpBinary = defined('PHP_BINARY') && PHP_BINARY ? PHP_BINARY : 'C:\\xampp\\php\\php.exe';
$phpFiles = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(__DIR__ . '/..'));
$syntaxFailures = [];
foreach ($phpFiles as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        $filePath = $file->getRealPath();
        if (strpos($filePath, 'vendor') !== false || strpos($filePath, 'scratch') !== false) {
            continue;
        }
        $output = [];
        $returnVar = 0;
        exec(escapeshellarg($phpBinary) . " -l " . escapeshellarg($filePath), $output, $returnVar);
        if ($returnVar !== 0) {
            $syntaxFailures[] = $filePath;
        }
    }
}
assert_test("All PHP files pass syntax lint (php -l)", count($syntaxFailures) === 0, implode(', ', $syntaxFailures));

// 2. Environment & Config Loader
echo "\n--- 2. Environment & Configuration ---\n";
require_once __DIR__ . '/../includes/env.php';
assert_test("Environment loader initialized", function_exists('env'));
assert_test(".env file exists and loaded", file_exists(__DIR__ . '/../.env'));
assert_test("DB_HOST configured", !empty(env('DB_HOST', '')));
assert_test("DB_NAME configured", !empty(env('DB_NAME', '')));

// 3. Database Connection & Migration Status
echo "\n--- 3. Database & Migrations ---\n";
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';
try {
    $pdo = get_db();
    assert_test("Database connection successful", $pdo instanceof PDO);
    $stmt = $pdo->query("SHOW TABLES LIKE 'schema_migrations'");
    assert_test("schema_migrations table exists", $stmt->rowCount() > 0);
    $stmtAudit = $pdo->query("SHOW TABLES LIKE 'audit_logs'");
    assert_test("audit_logs table exists", $stmtAudit->rowCount() > 0);
    $stmtRate = $pdo->query("SHOW TABLES LIKE 'rate_limits'");
    assert_test("rate_limits table exists", $stmtRate->rowCount() > 0);
} catch (\Throwable $e) {
    assert_test("Database connection successful", false, $e->getMessage());
}

// 4. CSRF Protection System
echo "\n--- 4. CSRF Protection System ---\n";
require_once __DIR__ . '/../includes/csrf.php';
$token = generate_csrf_token();
assert_test("CSRF token generated (64 chars)", strlen($token) === 64);
assert_test("Valid CSRF token passes validation", validate_csrf_token($token));
assert_test("Invalid CSRF token fails validation", !validate_csrf_token('invalid_token_1234567890'));

// 5. Rate Limiter Brute Force Engine
echo "\n--- 5. Rate Limiter Engine ---\n";
require_once __DIR__ . '/../includes/rate_limiter.php';
$testKey = 'test_ip_key_' . time();
RateLimiter::clear($testKey);
assert_test("Initial rate check passes", RateLimiter::check($testKey, 3, 60));
RateLimiter::hit($testKey, 60);
RateLimiter::hit($testKey, 60);
RateLimiter::hit($testKey, 60);
assert_test("Exceeding max attempts triggers rate limit rejection", !RateLimiter::check($testKey, 3, 60));
RateLimiter::clear($testKey);
assert_test("Clearing rate limit restores access", RateLimiter::check($testKey, 3, 60));

// 6. File Upload Security Helper
echo "\n--- 6. Upload Security Helper ---\n";
require_once __DIR__ . '/../includes/upload_helper.php';
assert_test("secure_file_upload function exists", function_exists('secure_file_upload'));
$uploadResult = secure_file_upload('dummy_field', 'tests');
assert_test("No-file upload safely returns null", $uploadResult === null);

// 7. Session & Auth Guards
echo "\n--- 7. Auth Guards & Tooling Hardening ---\n";
require_once __DIR__ . '/../includes/auth.php';
assert_test("is_logged_in returns false for guest session", !is_logged_in());
assert_test("is_admin returns false for guest session", !is_admin());

$setupContent = file_get_contents(__DIR__ . '/../setup.php');
assert_test("setup.php enforces CLI-only execution", strpos($setupContent, "PHP_SAPI !== 'cli'") !== false);

$migrateContent = file_get_contents(__DIR__ . '/../migrate_firebase.php');
assert_test("migrate_firebase.php enforces CLI-only execution", strpos($migrateContent, "PHP_SAPI !== 'cli'") !== false);

$messagesContent = file_get_contents(__DIR__ . '/../admin/messages.php');
assert_test("admin/messages.php enforces CSRF token validation", strpos($messagesContent, "require_csrf_token()") !== false);

echo "\n====================================================\n";
echo " TEST SUMMARY: Passed: {$passCount} | Failed: {$failCount}\n";
echo "====================================================\n\n";

exit($failCount === 0 ? 0 : 1);
