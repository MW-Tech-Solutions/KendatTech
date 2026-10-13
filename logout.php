<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

$wasAdmin = is_admin();

logout_user();

if ($wasAdmin) {
    header("Location: " . get_base_url() . "admin-login.php?logged_out=1");
} else {
    header("Location: " . get_base_url() . "index.php");
}
exit;
