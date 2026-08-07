<?php
// Vyapar Setu Configuration File

if (!defined('APP_NAME')) {
    define('APP_NAME', 'Vyapar Setu');
    define('APP_SLOGAN', 'Multi-Vendor Marketplace');
    define('APP_URL', 'http://localhost/Multi_Vendor_Marketplace_final');
    define('DB_HOST', '127.0.0.1');
    define('DB_USER', 'root');
    define('DB_PASS', '');
    define('DB_NAME', 'vyapar_setu');
    define('TIMEZONE', 'Asia/Kolkata');
}

date_default_timezone_set(TIMEZONE);

// Base directory path
if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(__DIR__));
}
