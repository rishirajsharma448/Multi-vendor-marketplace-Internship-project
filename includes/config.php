<?php
// Vyapar Setu Configuration File

if (!defined('APP_NAME')) {
    define('APP_NAME', 'Vyapar Setu');
    define('APP_SLOGAN', 'Multi-Vendor Marketplace');
    
    $scheme = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
    $httpHost = $_SERVER['HTTP_HOST'] ?? 'localhost/Multi_Vendor_Marketplace_final';
    define('APP_URL', getenv('APP_URL') ?: "$scheme://$httpHost");
    
    define('DB_HOST', getenv('MYSQLHOST') ?: (getenv('MYSQL_HOST') ?: (getenv('DB_HOST') ?: '127.0.0.1')));
    define('DB_PORT', getenv('MYSQLPORT') ?: (getenv('MYSQL_PORT') ?: (getenv('DB_PORT') ?: '3306')));
    define('DB_USER', getenv('MYSQLUSER') ?: (getenv('MYSQL_USER') ?: (getenv('DB_USER') ?: 'root')));
    define('DB_PASS', getenv('MYSQLPASSWORD') ?: (getenv('MYSQL_PASSWORD') ?: (getenv('DB_PASS') ?: '')));
    define('DB_NAME', getenv('MYSQLDATABASE') ?: (getenv('MYSQL_DATABASE') ?: (getenv('DB_NAME') ?: 'vyapar_setu')));
    define('TIMEZONE', 'Asia/Kolkata');
}

date_default_timezone_set(TIMEZONE);

// Base directory path
if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(__DIR__));
}
