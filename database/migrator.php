<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/env.php';
require_once __DIR__ . '/../config/db.php';

class Migrator {
    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function ensureMigrationTable(): void {
        $this->pdo->exec("CREATE TABLE IF NOT EXISTS schema_migrations (
            id INT AUTO_INCREMENT PRIMARY KEY,
            migration VARCHAR(255) NOT NULL UNIQUE,
            executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    }

    public function run(): array {
        $this->ensureMigrationTable();

        $stmt = $this->pdo->query("SELECT migration FROM schema_migrations");
        $executed = $stmt->fetchAll(PDO::FETCH_COLUMN);

        $migrationFiles = glob(__DIR__ . '/migrations/*.sql');
        sort($migrationFiles);

        $results = [];

        foreach ($migrationFiles as $file) {
            $filename = basename($file);
            if (in_array($filename, $executed, true)) {
                continue;
            }

            $sql = file_get_contents($file);
            try {
                $this->pdo->exec($sql);
                
                $logStmt = $this->pdo->prepare("INSERT INTO schema_migrations (migration) VALUES (?)");
                $logStmt->execute([$filename]);
                
                $results[] = "SUCCESS: {$filename}";
            } catch (Throwable $e) {
                $results[] = "FAILED: {$filename} - " . $e->getMessage();
                break;
            }
        }

        return $results;
    }
}

// CLI runner
if (php_sapi_name() === 'cli') {
    echo "=== Kendat Database Migration Engine ===\n";
    $db = new Database();
    $migrator = new Migrator($db->connect());
    $logs = $migrator->run();
    if (empty($logs)) {
        echo "No new migrations to execute.\n";
    } else {
        foreach ($logs as $log) {
            echo "{$log}\n";
        }
    }
}
