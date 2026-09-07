<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/env.php';

class Database {
    private array $hosts;
    private array $ports;
    private string $dbname;
    private string $username;
    private string $password;
    private ?PDO $pdo = null;

    public function __construct() {
        $envHost = env('DB_HOST', '127.0.0.1');
        $this->hosts = array_values(array_unique([$envHost, '127.0.0.1', 'localhost']));

        $envPort = (int)env('DB_PORT', 3306);
        $this->ports = array_values(array_unique([$envPort, 3306, 3308]));

        $this->dbname = (string)env('DB_NAME', 'kendat_integrated_services');
        $this->username = (string)env('DB_USER', 'root');
        $this->password = (string)env('DB_PASS', '');
    }

    public function databaseName(): string {
        return $this->dbname;
    }

    public function connectServer(): PDO {
        $lastException = null;
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];

        foreach ($this->hosts as $host) {
            foreach ($this->ports as $port) {
                try {
                    $dsn = "mysql:host={$host};port={$port};charset=utf8mb4";
                    return new PDO($dsn, $this->username, $this->password, $options);
                } catch (PDOException $e) {
                    $lastException = $e;
                }
            }
        }
        $this->renderDatabaseError($lastException ? $lastException->getMessage() : 'Could not connect to MySQL server.');
        exit;
    }

    public function connect(): PDO {
        if ($this->pdo === null) {
            $lastException = null;
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];

            foreach ($this->hosts as $host) {
                foreach ($this->ports as $port) {
                    try {
                        $dsn = "mysql:host={$host};port={$port};dbname={$this->dbname};charset=utf8mb4";
                        $this->pdo = new PDO($dsn, $this->username, $this->password, $options);
                        return $this->pdo;
                    } catch (PDOException $e) {
                        $lastException = $e;
                    }
                }
            }

            error_log("Database Connection Failure: " . ($lastException ? $lastException->getMessage() : 'Could not connect to MySQL server.'));
            $this->renderDatabaseError($lastException ? $lastException->getMessage() : 'Could not connect to MySQL server.');
            exit;
        }
        return $this->pdo;
    }

    private function renderDatabaseError(string $errorMessage): void {
        $isDev = env('APP_ENV', 'production') === 'development';
        $displayMsg = $isDev ? $errorMessage : 'A database connection error occurred. Please ensure database services are running and try again.';
        http_response_code(500);
        ?>
        <!doctype html>
        <html lang="en">
        <head>
          <meta charset="utf-8">
          <meta name="viewport" content="width=device-width, initial-scale=1">
          <title>Database Connection Error - Kendat Integrated Services</title>
          <link rel="preconnect" href="https://fonts.googleapis.com">
          <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
          <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@700;800;900&family=Open+Sans:wght@400;500;600;700;800&family=Poppins:wght@500;600;700;800;900&display=swap" rel="stylesheet">
          <style>
            body { margin:0; background:#070d19; color:#f8fafc; font-family:'Open Sans',-apple-system,sans-serif; display:grid; place-items:center; min-height:100vh; padding:20px; }
            .error-card { max-width:620px; width:100%; border:1px solid rgba(239, 68, 68, 0.3); border-radius:20px; background:linear-gradient(180deg, rgba(15, 25, 46, 0.95), rgba(7, 13, 25, 0.95)); padding:32px; box-shadow:0 20px 60px rgba(0,0,0,0.5); text-align:center; }
            .icon { width:64px; height:64px; border-radius:16px; background:rgba(239, 68, 68, 0.12); color:#EF4444; display:grid; place-items:center; margin:0 auto 20px; font-size:32px; }
            h1 { margin:0 0 12px; font-size:26px; font-family:'Montserrat',-apple-system,sans-serif; font-weight:700; }
            p { color:#94a3b8; line-height:1.6; margin-bottom:20px; font-size:15px; }
            .detail { background:rgba(7, 13, 25, 0.8); border:1px solid rgba(255,255,255,0.1); border-radius:12px; padding:14px; color:#fecdd3; font-family:monospace; font-size:13px; text-align:left; overflow-x:auto; margin-bottom:24px; }
            .steps { text-align:left; background:rgba(0, 135, 255, 0.08); border:1px solid rgba(0, 135, 255, 0.25); border-radius:14px; padding:18px; margin-bottom:24px; color:#cbd5e1; font-size:14px; }
            .steps ol { margin:8px 0 0 20px; padding:0; line-height:1.7; }
            .btn { display:inline-block; border:0; background:#0087FF; color:#ffffff; padding:12px 24px; border-radius:999px; font-family:'Poppins',-apple-system,sans-serif; font-weight:700; text-decoration:none; cursor:pointer; }
            .btn:hover { background:#0073E6; }
          </style>
        </head>
        <body>
          <div class="error-card">
            <div class="icon">⚠️</div>
            <h1>MySQL Connection Failed</h1>
            <p>The application could not establish a connection to your MySQL database server.</p>
            <div class="detail"><?php echo htmlspecialchars($displayMsg); ?></div>
            <div class="steps">
              <strong>How to fix this in XAMPP:</strong>
              <ol>
                <li>Open the <strong>XAMPP Control Panel</strong> on your computer.</li>
                <li>Find the <strong>MySQL</strong> module and click the <strong>Start</strong> button.</li>
                <li>Ensure MySQL displays a green status indicator (running on port 3306 or 3307).</li>
                <li>Refresh this browser page.</li>
              </ol>
            </div>
            <a href="javascript:location.reload();" class="btn">Retry Connection</a>
          </div>
        </body>
        </html>
        <?php
    }
}
