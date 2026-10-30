<?php
// Vyapar Setu Configuration File

if (!defined('APP_NAME')) {
    define('APP_NAME', 'Vyapar Setu');
    define('APP_SLOGAN', 'Multi-Vendor Marketplace');
    
    $scheme = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    
    // Auto-detect project base URI path (e.g. /Multi_Vendor_Marketplace_final)
    $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
    $baseDir = preg_replace('#/(vendor|customer|admin|api|ajax)(/.*)?$#i', '', $scriptDir);
    if ($baseDir === '/' || $baseDir === '.') {
        $baseDir = '';
    }
    $calculatedUrl = rtrim("$scheme://$host" . $baseDir, '/');
    define('APP_URL', getenv('APP_URL') ?: $calculatedUrl);
    
    define('DB_HOST', getenv('MYSQLHOST') ?: (getenv('MYSQL_HOST') ?: (getenv('DB_HOST') ?: '127.0.0.1')));
    define('DB_PORT', getenv('MYSQLPORT') ?: (getenv('MYSQL_PORT') ?: (getenv('DB_PORT') ?: '3306')));
    define('DB_USER', getenv('MYSQLUSER') ?: (getenv('MYSQL_USER') ?: (getenv('DB_USER') ?: 'root')));
    define('DB_PASS', getenv('MYSQLPASSWORD') ?: (getenv('MYSQL_PASSWORD') ?: (getenv('DB_PASS') ?: '')));
    define('DB_NAME', getenv('MYSQLDATABASE') ?: (getenv('MYSQL_DATABASE') ?: (getenv('DB_NAME') ?: 'vyapar_setu')));
    
    // SMTP Mail Configuration Constants
    define('SMTP_HOST', getenv('SMTP_HOST') ?: 'smtp.gmail.com');
    define('SMTP_PORT', getenv('SMTP_PORT') ?: 587);
    define('SMTP_USER', getenv('SMTP_USER') ?: 'rishirajsharma448@gmail.com');
    define('SMTP_PASS', getenv('SMTP_PASS') ?: ''); // Set your SMTP Password / Google App Password via environment variable or config
    define('SMTP_FROM', getenv('SMTP_FROM') ?: (getenv('MAIL_FROM_ADDRESS') ?: 'rishirajsharma448@gmail.com'));
    
    define('TIMEZONE', 'Asia/Kolkata');
}

date_default_timezone_set(TIMEZONE);

// Base directory path
if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(__DIR__));
}
