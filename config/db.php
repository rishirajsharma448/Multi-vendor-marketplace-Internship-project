<?php
/* Vyapar Setu - Database Connection Layer (PHP PDO)
   Supports MySQL (XAMPP / phpMyAdmin) with auto-fallback for local testing.
*/

class Database {
    private static $instance = null;
    private $pdo;

    private function __construct() {
        $host = getenv('MYSQLHOST') ?: (getenv('MYSQL_HOST') ?: (getenv('DB_HOST') ?: '127.0.0.1'));
        $port = getenv('MYSQLPORT') ?: (getenv('MYSQL_PORT') ?: (getenv('DB_PORT') ?: '3306'));
        $db   = getenv('MYSQLDATABASE') ?: (getenv('MYSQL_DATABASE') ?: (getenv('DB_NAME') ?: 'vyapar_setu'));
        $user = getenv('MYSQLUSER') ?: (getenv('MYSQL_USER') ?: (getenv('DB_USER') ?: 'root'));
        $pass = getenv('MYSQLPASSWORD') ?: (getenv('MYSQL_PASSWORD') ?: (getenv('DB_PASSWORD') ?: ''));
        $charset = 'utf8mb4';

        $dsn = "mysql:host=$host;port=$port;dbname=$db;charset=$charset";
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $this->pdo = new PDO($dsn, $user, $pass, $options);
            require_once __DIR__ . '/../includes/db.php';
            initMySQLSchema($this->pdo);
        } catch (PDOException $e) {
            // SQLite Fallback for standalone PHP web server testing when MySQL service is off
            $sqliteFile = __DIR__ . '/vyapar_setu.sqlite';
            $this->pdo = new PDO("sqlite:" . $sqliteFile, null, null, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]);
            $this->initSqliteSchema();
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance->pdo;
    }

    private function initSqliteSchema() {
        $check = $this->pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name='products'");
        if (!$check->fetch()) {
            $sql = file_get_contents(__DIR__ . '/../schema.sql');
            // Remove MySQL specific database & use statements
            $sql = preg_replace('/CREATE DATABASE.*?;/is', '', $sql);
            $sql = preg_replace('/USE `.*?`;/is', '', $sql);
            $sql = preg_replace('/ENGINE=InnoDB.*?;/i', ';', $sql);
            $sql = preg_replace('/AUTO_INCREMENT/i', '', $sql);
            $sql = preg_replace('/ENUM\(.*?\)/i', 'TEXT', $sql);
            $this->pdo->exec($sql);
        }
    }
}

function getDB() {
    return Database::getInstance();
}
