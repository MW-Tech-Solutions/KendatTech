<?php
declare(strict_types=1);

/**
 * Lightweight Environment Variable Loader for Kendat Integrated Services.
 * Reads .env file into $_ENV, $_SERVER, and getenv().
 */
function load_environment(string $path = __DIR__ . '/../.env'): void {
    static $loaded = false;
    if ($loaded) {
        return;
    }

    if (!file_exists($path)) {
        // Fallback default environment if .env is missing
        if (!isset($_ENV['APP_ENV'])) {
            $_ENV['APP_ENV'] = 'development';
        }
        $loaded = true;
        return;
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($lines === false) {
        $loaded = true;
        return;
    }

    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || strpos($line, '#') === 0) {
            continue;
        }

        if (strpos($line, '=') !== false) {
            list($name, $value) = explode('=', $line, 2);
            $name = trim($name);
            $value = trim($value);

            // Strip surrounding quotes
            if ((strpos($value, '"') === 0 && substr($value, -1) === '"') ||
                (strpos($value, "'") === 0 && substr($value, -1) === "'")) {
                $value = substr($value, 1, -1);
            }

            if (!array_key_exists($name, $_SERVER) && !array_key_exists($name, $_ENV)) {
                putenv("{$name}={$value}");
                $_ENV[$name] = $value;
                $_SERVER[$name] = $value;
            }
        }
    }
    $loaded = true;
}

function env(string $key, $default = null) {
    load_environment();

    if (array_key_exists($key, $_ENV)) {
        return $_ENV[$key];
    }
    if (array_key_exists($key, $_SERVER)) {
        return $_SERVER[$key];
    }

    $value = getenv($key);
    if ($value !== false) {
        return $value;
    }

    return $default;
}

// Auto-load environment on include
load_environment();
