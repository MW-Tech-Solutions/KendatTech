<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

logout_user();

header("Location: " . get_base_url() . "admin-login.php?logged_out=1");
exit;
