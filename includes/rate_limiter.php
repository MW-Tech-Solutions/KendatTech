<?php
declare(strict_types=1);

/**
 * Enterprise Rate Limiting Engine for Kendat Integrated Services.
 * Features database-backed persistence with automatic TTL cleanup and file-system fallback.
 */
class RateLimiter {
    private static string $storageFile = __DIR__ . '/../storage/rate_limit.json';

    public static function check(string $key, int $maxAttempts = 5, int $decaySeconds = 900): bool {
        $now = time();

        try {
            $pdo = get_db();
            $stmt = $pdo->prepare("SELECT attempts, first_attempt FROM rate_limits WHERE key_name = ? AND expires_at > ?");
            $stmt->execute([$key, $now]);
            $record = $stmt->fetch();

            if ($record) {
                if ((int)$record['attempts'] >= $maxAttempts) {
                    return false;
                }
            }
            return true;
        } catch (Throwable $e) {
            // DB Fallback to JSON file
            return self::checkFileFallback($key, $maxAttempts, $decaySeconds);
        }
    }

    public static function hit(string $key, int $decaySeconds = 900): void {
        $now = time();
        $expiresAt = $now + $decaySeconds;

        try {
            $pdo = get_db();
            
            // Periodically clean expired records
            if (rand(1, 20) === 1) {
                $pdo->exec("DELETE FROM rate_limits WHERE expires_at <= " . $now);
            }

            $stmt = $pdo->prepare("SELECT attempts, first_attempt FROM rate_limits WHERE key_name = ?");
            $stmt->execute([$key]);
            $record = $stmt->fetch();

            if ($record && ($now - (int)$record['first_attempt'] < $decaySeconds)) {
                $upStmt = $pdo->prepare("UPDATE rate_limits SET attempts = attempts + 1, expires_at = ? WHERE key_name = ?");
                $upStmt->execute([$expiresAt, $key]);
            } else {
                $inStmt = $pdo->prepare("INSERT INTO rate_limits (key_name, attempts, first_attempt, expires_at) 
                    VALUES (?, 1, ?, ?) 
                    ON DUPLICATE KEY UPDATE attempts = 1, first_attempt = VALUES(first_attempt), expires_at = VALUES(expires_at)");
                $inStmt->execute([$key, $now, $expiresAt]);
            }
        } catch (Throwable $e) {
            self::hitFileFallback($key, $decaySeconds);
        }
    }

    public static function clear(string $key): void {
        try {
            $pdo = get_db();
            $stmt = $pdo->prepare("DELETE FROM rate_limits WHERE key_name = ?");
            $stmt->execute([$key]);
        } catch (Throwable $e) {
            self::clearFileFallback($key);
        }
    }

    private static function checkFileFallback(string $key, int $maxAttempts, int $decaySeconds): bool {
        $data = self::loadData();
        $now = time();

        if (isset($data[$key])) {
            $record = $data[$key];
            if ($now - $record['first_attempt'] < $decaySeconds) {
                if ($record['attempts'] >= $maxAttempts) {
                    return false;
                }
            } else {
                unset($data[$key]);
            }
        }
        return true;
    }

    private static function hitFileFallback(string $key, int $decaySeconds): void {
        $data = self::loadData();
        $now = time();

        if (!isset($data[$key]) || ($now - $data[$key]['first_attempt'] >= $decaySeconds)) {
            $data[$key] = [
                'attempts' => 1,
                'first_attempt' => $now,
            ];
        } else {
            $data[$key]['attempts']++;
        }

        self::saveData($data);
    }

    private static function clearFileFallback(string $key): void {
        $data = self::loadData();
        if (isset($data[$key])) {
            unset($data[$key]);
            self::saveData($data);
        }
    }

    private static function loadData(): array {
        if (!file_exists(self::$storageFile)) {
            return [];
        }
        $content = @file_get_contents(self::$storageFile);
        if (!$content) {
            return [];
        }
        $data = json_decode($content, true);
        return is_array($data) ? $data : [];
    }

    private static function saveData(array $data): void {
        $dir = dirname(self::$storageFile);
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
        @file_put_contents(self::$storageFile, json_encode($data), LOCK_EX);
    }
}
