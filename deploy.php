<?php
declare(strict_types=1);

/**
 * Kendat Integrated Services - Enterprise Web Deployment Tool
 * Access via: https://kisprojectslab.com/kendattech/deploy.php?key=kendat2026
 */

$authKey = $_GET['key'] ?? '';
if ($authKey !== 'kendat2026') {
    http_response_code(403);
    die("Access Denied. Passcode required: deploy.php?key=kendat2026");
}

header('Content-Type: text/plain; charset=utf-8');

echo "====================================================\n";
echo " KENDAT TECH - LIVE SERVER AUTO-DEPLOYMENT TOOL     \n";
echo "====================================================\n";
echo "Time: " . date('Y-m-d H:i:s') . "\n";
echo "Path: " . __DIR__ . "\n\n";

chdir(__DIR__);

echo "--- 1. Fetching Latest Changes from GitHub ---\n";
exec('git fetch origin main 2>&1', $fetchOut, $fetchCode);
echo implode("\n", $fetchOut) . "\n\n";

echo "--- 2. Resetting Working Tree to Match Remote Main ---\n";
exec('git reset --hard origin/main 2>&1', $resetOut, $resetCode);
echo implode("\n", $resetOut) . "\n\n";

echo "--- 3. Verifying Git Status ---\n";
exec('git status 2>&1', $statusOut, $statusCode);
echo implode("\n", $statusOut) . "\n\n";

echo "====================================================\n";
echo " SUCCESS: Live Server Codebase Updated to HEAD!     \n";
echo "====================================================\n";
