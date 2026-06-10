<?php
require_once __DIR__ . '/../config/config.php';

class DB {
    private static ?PDO $instance = null;

    public static function conn(): PDO {
        if (self::$instance === null) {
            $dsn = "pgsql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME;
            try {
                self::$instance = new PDO($dsn, DB_USER, DB_PASS, [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]);
                self::$instance->exec("SET CLIENT_ENCODING TO 'UTF8'");
            } catch (PDOException $e) {
                error_log("DB Connection failed: " . $e->getMessage());
                die(json_encode(['error' => 'Database connection failed']));
            }
        }
        return self::$instance;
    }

    public static function query(string $sql, array $params = []): PDOStatement {
        // Convert PostgreSQL $1/$2/... positional placeholders to PDO named placeholders
        // (named placeholders allow the same parameter to be reused multiple times in a query)
        $bound = [];
        if (!empty($params)) {
            $sql = preg_replace('/\$(\d+)/', ':p$1', $sql);
            foreach ($params as $i => $value) {
                $bound[':p' . ($i + 1)] = $value;
            }
        }
        $stmt = self::conn()->prepare($sql);
        $stmt->execute($bound);
        return $stmt;
    }

    public static function fetch(string $sql, array $params = []): ?array {
        return self::query($sql, $params)->fetch() ?: null;
    }

    public static function fetchAll(string $sql, array $params = []): array {
        return self::query($sql, $params)->fetchAll();
    }

    public static function insert(string $sql, array $params = []): string {
        self::query($sql, $params);
        return self::conn()->lastInsertId();
    }
}
