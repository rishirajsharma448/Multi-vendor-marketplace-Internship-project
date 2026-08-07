<?php
// Vyapar Setu PDO Database Connection with fallback support

require_once __DIR__ . '/config.php';

function getDBConnection() {
    static $pdo = null;
    if ($pdo !== null) {
        return $pdo;
    }

    // Attempt MySQL Connection first
    try {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]);
        initMySQLSchema($pdo);
        return $pdo;
    } catch (PDOException $e) {
        // Debug output
        // echo "MySQL Exception: " . $e->getMessage() . "\n";
        // Fallback to SQLite if MySQL is not running or database is missing
        try {
            $sqliteDir = BASE_PATH . '/config';
            if (!is_dir($sqliteDir)) {
                mkdir($sqliteDir, 0777, true);
            }
            $sqlitePath = $sqliteDir . '/vyapar_setu.sqlite';
            $pdo = new PDO("sqlite:" . $sqlitePath);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

            // Initialize SQLite schema if empty
            initSQLiteSchema($pdo);
            return $pdo;
        } catch (PDOException $ex) {
            die("Database Connection Error: " . $ex->getMessage());
        }
    }
}

function initMySQLSchema($pdo) {
    static $migrated = false;
    if ($migrated) return;
    $migrated = true;

    // Repair the core objects first. Older installations may contain a partial
    // schema; one failed migration must not prevent these objects from being
    // created and cause every product/order page to fail.
    try {
        $productColumns = $pdo->query("SHOW COLUMNS FROM `products`")->fetchAll(PDO::FETCH_COLUMN);
        if (!in_array('category_id', $productColumns, true)) {
            $pdo->exec("ALTER TABLE `products` ADD COLUMN `category_id` INT NOT NULL DEFAULT 1 AFTER `vendor_id`");
        }
    } catch (PDOException $e) {
        // The full migration below will create the products table when needed.
    }

    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS `product_images` (
          `id` INT AUTO_INCREMENT PRIMARY KEY,
          `product_id` INT NOT NULL,
          `image_url` VARCHAR(255) NOT NULL,
          `is_primary` TINYINT(1) DEFAULT 0,
          `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    } catch (PDOException $e) {
        // Preserve the existing connection; the main migration reports any
        // remaining schema issue without taking down the page.
    }

    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS `order_items` (
          `id` INT AUTO_INCREMENT PRIMARY KEY,
          `order_id` INT NOT NULL,
          `product_id` INT NOT NULL,
          `vendor_id` INT NOT NULL,
          `product_title` VARCHAR(150) NOT NULL,
          `price` DECIMAL(10,2) NOT NULL,
          `quantity` INT NOT NULL,
          `status` ENUM('Pending', 'Processing', 'Shipped', 'Delivered', 'Cancelled', 'Returned') DEFAULT 'Pending'
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    } catch (PDOException $e) {
        // Preserve the existing connection; see the migration below.
    }

    try {
        // 1. Ensure users table exists & verify columns
        $pdo->exec("CREATE TABLE IF NOT EXISTS `users` (
          `id` INT AUTO_INCREMENT PRIMARY KEY,
          `name` VARCHAR(100) NOT NULL,
          `email` VARCHAR(100) NOT NULL UNIQUE,
          `phone` VARCHAR(20) DEFAULT NULL,
          `password_hash` VARCHAR(255) NOT NULL,
          `role` ENUM('customer', 'vendor', 'admin') NOT NULL DEFAULT 'customer',
          `avatar` VARCHAR(255) DEFAULT 'assets/images/default-avatar.png',
          `status` ENUM('ACTIVE', 'INACTIVE', 'SUSPENDED') DEFAULT 'ACTIVE',
          `email_verified` TINYINT(1) DEFAULT 0,
          `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
          `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        $cols = $pdo->query("SHOW COLUMNS FROM `users`")->fetchAll(PDO::FETCH_COLUMN);
        if (!in_array('phone', $cols)) {
            $pdo->exec("ALTER TABLE `users` ADD COLUMN `phone` VARCHAR(20) DEFAULT NULL AFTER `email`");
        }
        if (!in_array('avatar', $cols)) {
            $pdo->exec("ALTER TABLE `users` ADD COLUMN `avatar` VARCHAR(255) DEFAULT 'assets/images/default-avatar.png'");
        }
        if (!in_array('status', $cols)) {
            $pdo->exec("ALTER TABLE `users` ADD COLUMN `status` ENUM('ACTIVE', 'INACTIVE', 'SUSPENDED') DEFAULT 'ACTIVE'");
        }
        if (!in_array('email_verified', $cols)) {
            $pdo->exec("ALTER TABLE `users` ADD COLUMN `email_verified` TINYINT(1) DEFAULT 0");
        }

        // 2. Ensure vendors table
        $pdo->exec("CREATE TABLE IF NOT EXISTS `vendors` (
          `id` INT AUTO_INCREMENT PRIMARY KEY,
          `user_id` INT DEFAULT NULL UNIQUE,
          `vendor_code` VARCHAR(20) NOT NULL UNIQUE,
          `store_name` VARCHAR(120) NOT NULL,
          `owner_name` VARCHAR(100) NOT NULL,
          `email` VARCHAR(100) NOT NULL,
          `phone` VARCHAR(20) DEFAULT '',
          `gstin` VARCHAR(20) DEFAULT NULL,
          `category` VARCHAR(60) NOT NULL,
          `store_logo` VARCHAR(255) DEFAULT 'assets/images/default-store.png',
          `banner` VARCHAR(255) DEFAULT 'assets/images/default-banner.png',
          `rating` DECIMAL(3,2) DEFAULT 5.00,
          `reviews_count` INT DEFAULT 0,
          `status` ENUM('PENDING', 'APPROVED', 'REJECTED') DEFAULT 'PENDING',
          `commission_rate` VARCHAR(10) DEFAULT '10%',
          `address` TEXT DEFAULT NULL,
          `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
          `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        $vCols = $pdo->query("SHOW COLUMNS FROM `vendors`")->fetchAll(PDO::FETCH_COLUMN);
        if (!in_array('user_id', $vCols)) {
            $pdo->exec("ALTER TABLE `vendors` ADD COLUMN `user_id` INT DEFAULT NULL UNIQUE AFTER `id`");
        }
        if (!in_array('phone', $vCols)) {
            $pdo->exec("ALTER TABLE `vendors` ADD COLUMN `phone` VARCHAR(20) DEFAULT '' AFTER `email`");
        }
        if (!in_array('store_logo', $vCols)) {
            $pdo->exec("ALTER TABLE `vendors` ADD COLUMN `store_logo` VARCHAR(255) DEFAULT 'assets/images/default-store.png'");
        }
        if (!in_array('banner', $vCols)) {
            $pdo->exec("ALTER TABLE `vendors` ADD COLUMN `banner` VARCHAR(255) DEFAULT 'assets/images/default-banner.png'");
        }
        if (!in_array('address', $vCols)) {
            $pdo->exec("ALTER TABLE `vendors` ADD COLUMN `address` TEXT DEFAULT NULL");
        }

        // 3. Ensure categories table
        $pdo->exec("CREATE TABLE IF NOT EXISTS `categories` (
          `id` INT AUTO_INCREMENT PRIMARY KEY,
          `name` VARCHAR(80) NOT NULL UNIQUE,
          `slug` VARCHAR(80) NOT NULL UNIQUE,
          `icon` VARCHAR(50) DEFAULT 'bi-box',
          `parent_id` INT DEFAULT NULL,
          `image` VARCHAR(255) DEFAULT NULL,
          `description` TEXT DEFAULT NULL,
          `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        // 4. Ensure brands table
        $pdo->exec("CREATE TABLE IF NOT EXISTS `brands` (
          `id` INT AUTO_INCREMENT PRIMARY KEY,
          `name` VARCHAR(80) NOT NULL UNIQUE,
          `slug` VARCHAR(80) NOT NULL UNIQUE,
          `logo` VARCHAR(255) DEFAULT NULL,
          `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        // 5. Ensure products table
        $pdo->exec("CREATE TABLE IF NOT EXISTS `products` (
          `id` INT AUTO_INCREMENT PRIMARY KEY,
          `sku` VARCHAR(30) NOT NULL UNIQUE,
          `vendor_id` INT NOT NULL,
          `category_id` INT NOT NULL DEFAULT 1,
          `brand_id` INT DEFAULT NULL,
          `title` VARCHAR(150) NOT NULL,
          `description` TEXT NOT NULL,
          `price` DECIMAL(10,2) NOT NULL,
          `original_price` DECIMAL(10,2) NOT NULL,
          `discount` INT DEFAULT 0,
          `stock` INT NOT NULL DEFAULT 0,
          `spotlight` ENUM('BESTSELLER', 'FEATURED', 'REGULAR') DEFAULT 'REGULAR',
          `status` ENUM('ACTIVE', 'HIDDEN', 'DRAFT') DEFAULT 'ACTIVE',
          `rating` DECIMAL(3,2) DEFAULT 5.00,
          `rating_count` INT DEFAULT 0,
          `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
          `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        $pCols = $pdo->query("SHOW COLUMNS FROM `products`")->fetchAll(PDO::FETCH_COLUMN);
        if (!in_array('category_id', $pCols)) {
            $pdo->exec("ALTER TABLE `products` ADD COLUMN `category_id` INT NOT NULL DEFAULT 1 AFTER `vendor_id`");
        }
        if (!in_array('brand_id', $pCols)) {
            $pdo->exec("ALTER TABLE `products` ADD COLUMN `brand_id` INT DEFAULT NULL AFTER `category_id`");
        }
        if (!in_array('discount', $pCols)) {
            $pdo->exec("ALTER TABLE `products` ADD COLUMN `discount` INT DEFAULT 0 AFTER `original_price`");
        }

        // 6. Ensure product_images table
        $pdo->exec("CREATE TABLE IF NOT EXISTS `product_images` (
          `id` INT AUTO_INCREMENT PRIMARY KEY,
          `product_id` INT NOT NULL,
          `image_url` VARCHAR(255) NOT NULL,
          `is_primary` TINYINT(1) DEFAULT 0,
          `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        // 7. Ensure cart & cart_items tables
        $pdo->exec("CREATE TABLE IF NOT EXISTS `cart` (
          `id` INT AUTO_INCREMENT PRIMARY KEY,
          `user_id` INT DEFAULT NULL,
          `session_id` VARCHAR(100) DEFAULT NULL,
          `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
          `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        $pdo->exec("CREATE TABLE IF NOT EXISTS `cart_items` (
          `id` INT AUTO_INCREMENT PRIMARY KEY,
          `cart_id` INT NOT NULL,
          `product_id` INT NOT NULL,
          `quantity` INT NOT NULL DEFAULT 1,
          `price` DECIMAL(10,2) NOT NULL,
          `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        // 8. Ensure wishlist table
        $pdo->exec("CREATE TABLE IF NOT EXISTS `wishlist` (
          `id` INT AUTO_INCREMENT PRIMARY KEY,
          `user_id` INT NOT NULL,
          `product_id` INT NOT NULL,
          `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
          UNIQUE KEY `user_product_unique` (`user_id`, `product_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        // 9. Ensure orders table
        $pdo->exec("CREATE TABLE IF NOT EXISTS `orders` (
          `id` INT AUTO_INCREMENT PRIMARY KEY,
          `order_number` VARCHAR(30) NOT NULL UNIQUE,
          `user_id` INT DEFAULT NULL,
          `customer_name` VARCHAR(100) NOT NULL,
          `email` VARCHAR(100) NOT NULL,
          `phone` VARCHAR(20) NOT NULL,
          `address` TEXT NOT NULL,
          `payment_method` VARCHAR(50) DEFAULT 'UPI / Razorpay',
          `payment_status` ENUM('Pending', 'Paid', 'Failed', 'Refunded') DEFAULT 'Pending',
          `subtotal` DECIMAL(10,2) NOT NULL,
          `discount` DECIMAL(10,2) DEFAULT 0.00,
          `tax` DECIMAL(10,2) DEFAULT 0.00,
          `shipping_fee` DECIMAL(10,2) DEFAULT 0.00,
          `total_amount` DECIMAL(10,2) NOT NULL,
          `status` ENUM('Order Placed', 'Processing', 'Shipped', 'Out for Delivery', 'Delivered', 'Cancelled') DEFAULT 'Order Placed',
          `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
          `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        $oCols = $pdo->query("SHOW COLUMNS FROM `orders`")->fetchAll(PDO::FETCH_COLUMN);
        if (!in_array('user_id', $oCols)) {
            $pdo->exec("ALTER TABLE `orders` ADD COLUMN `user_id` INT DEFAULT NULL AFTER `order_number`");
        }
        if (!in_array('email', $oCols)) {
            $pdo->exec("ALTER TABLE `orders` ADD COLUMN `email` VARCHAR(100) DEFAULT '' AFTER `customer_name`");
        }
        if (!in_array('payment_status', $oCols)) {
            $pdo->exec("ALTER TABLE `orders` ADD COLUMN `payment_status` ENUM('Pending', 'Paid', 'Failed', 'Refunded') DEFAULT 'Pending' AFTER `payment_method`");
        }

        // 10. Ensure order_items table
        $pdo->exec("CREATE TABLE IF NOT EXISTS `order_items` (
          `id` INT AUTO_INCREMENT PRIMARY KEY,
          `order_id` INT NOT NULL,
          `product_id` INT NOT NULL,
          `vendor_id` INT NOT NULL,
          `product_title` VARCHAR(150) NOT NULL,
          `price` DECIMAL(10,2) NOT NULL,
          `quantity` INT NOT NULL,
          `status` ENUM('Pending', 'Processing', 'Shipped', 'Delivered', 'Cancelled', 'Returned') DEFAULT 'Pending'
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        // 11. Ensure payments table
        $pdo->exec("CREATE TABLE IF NOT EXISTS `payments` (
          `id` INT AUTO_INCREMENT PRIMARY KEY,
          `order_id` INT NOT NULL,
          `transaction_id` VARCHAR(100) NOT NULL UNIQUE,
          `payment_method` VARCHAR(50) NOT NULL,
          `amount` DECIMAL(10,2) NOT NULL,
          `status` ENUM('SUCCESS', 'PENDING', 'FAILED', 'REFUNDED') DEFAULT 'PENDING',
          `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        // 12. Ensure reviews table
        $pdo->exec("CREATE TABLE IF NOT EXISTS `reviews` (
          `id` INT AUTO_INCREMENT PRIMARY KEY,
          `product_id` INT NOT NULL,
          `user_id` INT DEFAULT NULL,
          `customer_name` VARCHAR(100) NOT NULL,
          `rating` INT NOT NULL CHECK (`rating` BETWEEN 1 AND 5),
          `comment` TEXT NOT NULL,
          `vendor_response` TEXT DEFAULT NULL,
          `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        // 13. Ensure notifications table
        $pdo->exec("CREATE TABLE IF NOT EXISTS `notifications` (
          `id` INT AUTO_INCREMENT PRIMARY KEY,
          `user_id` INT NOT NULL,
          `title` VARCHAR(150) NOT NULL,
          `message` TEXT NOT NULL,
          `is_read` TINYINT(1) DEFAULT 0,
          `type` VARCHAR(50) DEFAULT 'info',
          `link` VARCHAR(255) DEFAULT NULL,
          `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        // 14. Ensure support_tickets table
        $pdo->exec("CREATE TABLE IF NOT EXISTS `support_tickets` (
          `id` INT AUTO_INCREMENT PRIMARY KEY,
          `user_id` INT NOT NULL,
          `ticket_no` VARCHAR(30) NOT NULL UNIQUE,
          `subject` VARCHAR(150) NOT NULL,
          `message` TEXT NOT NULL,
          `priority` ENUM('Low', 'Medium', 'High', 'Urgent') DEFAULT 'Medium',
          `status` ENUM('Open', 'In Progress', 'Resolved', 'Closed') DEFAULT 'Open',
          `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        // 15. Ensure activity_logs table
        $pdo->exec("CREATE TABLE IF NOT EXISTS `activity_logs` (
          `id` INT AUTO_INCREMENT PRIMARY KEY,
          `user_id` INT DEFAULT NULL,
          `action_description` TEXT NOT NULL,
          `ip_address` VARCHAR(45) DEFAULT NULL,
          `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        // Seed users if empty
        $uCount = $pdo->query("SELECT COUNT(*) AS cnt FROM users")->fetch()['cnt'] ?? 0;
        if ($uCount == 0) {
            $pass = password_hash('password123', PASSWORD_BCRYPT);
            $pdo->exec("INSERT INTO `users` (`id`, `name`, `email`, `phone`, `password_hash`, `role`, `status`, `email_verified`) VALUES
                (1, 'System Administrator', 'admin@vyaparsetu.com', '+91 99000 11223', '$pass', 'admin', 'ACTIVE', 1),
                (2, 'Rahul Verma (TechCraft)', 'vendor@vyaparsetu.com', '+91 98765 11111', '$pass', 'vendor', 'ACTIVE', 1),
                (3, 'Priya Sharma (Handloom)', 'priya@bharattextiles.in', '+91 98765 22222', '$pass', 'vendor', 'ACTIVE', 1),
                (4, 'Amrit Patel (Organic)', 'amrit@swadeshispices.org', '+91 98765 33333', '$pass', 'vendor', 'ACTIVE', 1),
                (5, 'Rishiraj Sharma', 'customer@vyaparsetu.com', '+91 98765 43210', '$pass', 'customer', 'ACTIVE', 1)");
        }

        // Seed vendors if empty
        $vCount = $pdo->query("SELECT COUNT(*) AS cnt FROM vendors")->fetch()['cnt'] ?? 0;
        if ($vCount == 0) {
            $pdo->exec("INSERT INTO `vendors` (`id`, `user_id`, `vendor_code`, `store_name`, `owner_name`, `email`, `phone`, `gstin`, `category`, `rating`, `reviews_count`, `status`, `commission_rate`, `address`) VALUES
                (1, 2, 'VND-88219', 'TechCraft Electronics', 'Rahul Verma', 'vendor@vyaparsetu.com', '+91 98765 11111', '27AABCT1234H1Z5', 'Electronics', 4.90, 1240, 'APPROVED', '10%', 'Building 4, Tech Park, Mumbai'),
                (2, 3, 'VND-44102', 'Bharat Handloom Textiles', 'Priya Sharma', 'priya@bharattextiles.in', '+91 98765 22222', '27AABCB5678K1Z2', 'Fashion', 4.80, 850, 'APPROVED', '10%', 'Heritage Weaver Lane, Varanasi'),
                (3, 4, 'VND-99301', 'Swadeshi Organic Spices', 'Amrit Patel', 'amrit@swadeshispices.org', '+91 98765 33333', '27AABCS9912M1Z9', 'Spices & Organics', 4.90, 2410, 'APPROVED', '10%', 'Spices Hub, Kochi, Kerala')");
        }

        // Seed categories if empty
        $cCount = $pdo->query("SELECT COUNT(*) AS cnt FROM categories")->fetch()['cnt'] ?? 0;
        if ($cCount == 0) {
            $pdo->exec("INSERT INTO `categories` (`id`, `name`, `slug`, `icon`, `description`) VALUES
                (1, 'Electronics', 'electronics', 'bi-laptop', 'Gadgets, Smart Devices & Accessories'),
                (2, 'Fashion & Apparel', 'fashion', 'bi-bag-heart', 'Ethnic, Western Wear & Handlooms'),
                (3, 'Spices & Organics', 'spices-organics', 'bi-flower1', 'Farm Fresh Spices & Organic Herbs'),
                (4, 'Home & Living', 'home-living', 'bi-house-door', 'Handicrafts, Décor & Kitchenware'),
                (5, 'Beauty & Wellness', 'beauty-wellness', 'bi-heart-pulse', 'Natural Skincare & Herbal Products')");
        }

        // Seed products if empty
        $pCount = $pdo->query("SELECT COUNT(*) AS cnt FROM products")->fetch()['cnt'] ?? 0;
        if ($pCount == 0) {
            $pdo->exec("INSERT INTO `products` (`id`, `sku`, `vendor_id`, `category_id`, `title`, `description`, `price`, `original_price`, `discount`, `stock`, `spotlight`, `status`, `rating`, `rating_count`) VALUES
                (1, 'TC-WLE-01', 1, 1, 'SonicPods Pro Active Noise Cancelling Earbuds', 'Ultra-low latency wireless earbuds featuring Hybrid Active Noise Cancellation (ANC), 30-hour playback with fast charge case.', 2499.00, 4999.00, 50, 42, 'BESTSELLER', 'ACTIVE', 4.80, 312),
                (2, 'TC-SMW-04', 1, 1, 'SmartFit OLED Fitness & SpO2 Smartwatch', 'Sleek aluminum smartwatch with 1.43-inch Always-On AMOLED Display, continuous Heart Rate & SpO2 tracker.', 3999.00, 6999.00, 43, 15, 'FEATURED', 'ACTIVE', 4.90, 184),
                (3, 'BT-SLK-09', 2, 2, 'Handcrafted Pure Mulberry Silk Banarasi Saree', 'Woven by master artisans in Varanasi, this pure silk saree features gold zari brocade work.', 6800.00, 9500.00, 28, 8, 'FEATURED', 'ACTIVE', 4.90, 96),
                (4, 'SS-PEP-02', 3, 3, 'Organic Malabar Black Pepper Whole (500g)', 'Directly sourced from organic certified farms in Western Ghats, Kerala.', 650.00, 850.00, 24, 60, 'BESTSELLER', 'ACTIVE', 5.00, 540)");

            $pdo->exec("INSERT INTO `product_images` (`product_id`, `image_url`, `is_primary`) VALUES
                (1, 'https://images.unsplash.com/photo-1590658268037-6bf12165a8df?auto=format&fit=crop&w=600&q=80', 1),
                (2, 'https://images.unsplash.com/photo-1579586337278-3befd40fd17a?auto=format&fit=crop&w=600&q=80', 1),
                (3, 'https://images.unsplash.com/photo-1610030469983-98e550d6193c?auto=format&fit=crop&w=600&q=80', 1),
                (4, 'https://images.unsplash.com/photo-1599940824399-b87987ceb72a?auto=format&fit=crop&w=600&q=80', 1)");
        }

        // Seed orders if empty
        $oCount = $pdo->query("SELECT COUNT(*) AS cnt FROM orders")->fetch()['cnt'] ?? 0;
        if ($oCount == 0) {
            $pdo->exec("INSERT INTO `orders` (`id`, `order_number`, `user_id`, `customer_name`, `email`, `phone`, `address`, `payment_method`, `payment_status`, `subtotal`, `discount`, `total_amount`, `status`) VALUES
                (1, 'ORD-99012', 5, 'Rishiraj Sharma', 'customer@vyaparsetu.com', '+91 98765 43210', 'Flat 402, G.V. Acharya Campus Road, Shelu, Maharashtra - 410101', 'UPI / Razorpay', 'Paid', 2499.00, 250.00, 2249.00, 'Shipped'),
                (2, 'ORD-99013', 5, 'Ananya Sen', 'ananya@gmail.com', '+91 91234 56789', 'Suite 12, Tech Park Avenue, Pune, Maharashtra', 'Cash on Delivery', 'Pending', 1300.00, 0.00, 1300.00, 'Delivered')");

            $pdo->exec("INSERT INTO `order_items` (`order_id`, `product_id`, `vendor_id`, `product_title`, `price`, `quantity`, `status`) VALUES
                (1, 1, 1, 'SonicPods Pro Active Noise Cancelling Earbuds', 2499.00, 1, 'Shipped'),
                (2, 4, 3, 'Organic Malabar Black Pepper Whole (500g)', 650.00, 2, 'Delivered')");
        }

    } catch (Exception $e) {
        // Migration error log ignored
    }
}

function initSQLiteSchema($pdo) {
    // 1. Users
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        email TEXT NOT NULL UNIQUE,
        phone TEXT DEFAULT NULL,
        password_hash TEXT NOT NULL,
        role TEXT NOT NULL DEFAULT 'customer',
        avatar TEXT DEFAULT 'assets/images/default-avatar.png',
        status TEXT DEFAULT 'ACTIVE',
        email_verified INTEGER DEFAULT 0,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    // 2. Vendors
    $pdo->exec("CREATE TABLE IF NOT EXISTS vendors (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER NOT NULL UNIQUE,
        vendor_code TEXT NOT NULL UNIQUE,
        store_name TEXT NOT NULL,
        owner_name TEXT NOT NULL,
        email TEXT NOT NULL,
        phone TEXT NOT NULL,
        gstin TEXT DEFAULT NULL,
        category TEXT NOT NULL,
        store_logo TEXT DEFAULT 'assets/images/default-store.png',
        banner TEXT DEFAULT 'assets/images/default-banner.png',
        rating REAL DEFAULT 5.00,
        reviews_count INTEGER DEFAULT 0,
        status TEXT DEFAULT 'PENDING',
        commission_rate TEXT DEFAULT '10%',
        address TEXT DEFAULT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    // 3. Categories
    $pdo->exec("CREATE TABLE IF NOT EXISTS categories (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL UNIQUE,
        slug TEXT NOT NULL UNIQUE,
        icon TEXT DEFAULT 'bi-box',
        parent_id INTEGER DEFAULT NULL,
        image TEXT DEFAULT NULL,
        description TEXT DEFAULT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    // 4. Brands
    $pdo->exec("CREATE TABLE IF NOT EXISTS brands (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL UNIQUE,
        slug TEXT NOT NULL UNIQUE,
        logo TEXT DEFAULT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    // 5. Products
    $pdo->exec("CREATE TABLE IF NOT EXISTS products (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        sku TEXT NOT NULL UNIQUE,
        vendor_id INTEGER NOT NULL,
        category_id INTEGER NOT NULL,
        brand_id INTEGER DEFAULT NULL,
        title TEXT NOT NULL,
        description TEXT NOT NULL,
        price REAL NOT NULL,
        original_price REAL NOT NULL,
        discount INTEGER DEFAULT 0,
        stock INTEGER NOT NULL DEFAULT 0,
        spotlight TEXT DEFAULT 'REGULAR',
        status TEXT DEFAULT 'ACTIVE',
        rating REAL DEFAULT 5.00,
        rating_count INTEGER DEFAULT 0,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    // 6. Product Images
    $pdo->exec("CREATE TABLE IF NOT EXISTS product_images (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        product_id INTEGER NOT NULL,
        image_url TEXT NOT NULL,
        is_primary INTEGER DEFAULT 0,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    // 7. Cart
    $pdo->exec("CREATE TABLE IF NOT EXISTS cart (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER DEFAULT NULL,
        session_id TEXT DEFAULT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    // 8. Cart Items
    $pdo->exec("CREATE TABLE IF NOT EXISTS cart_items (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        cart_id INTEGER NOT NULL,
        product_id INTEGER NOT NULL,
        quantity INTEGER NOT NULL DEFAULT 1,
        price REAL NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    // 9. Wishlist
    $pdo->exec("CREATE TABLE IF NOT EXISTS wishlist (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER NOT NULL,
        product_id INTEGER NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    // 10. Orders
    $pdo->exec("CREATE TABLE IF NOT EXISTS orders (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        order_number TEXT NOT NULL UNIQUE,
        user_id INTEGER DEFAULT NULL,
        customer_name TEXT NOT NULL,
        email TEXT NOT NULL,
        phone TEXT NOT NULL,
        address TEXT NOT NULL,
        payment_method TEXT DEFAULT 'UPI / Razorpay',
        payment_status TEXT DEFAULT 'Pending',
        subtotal REAL NOT NULL,
        discount REAL DEFAULT 0.00,
        tax REAL DEFAULT 0.00,
        shipping_fee REAL DEFAULT 0.00,
        total_amount REAL NOT NULL,
        status TEXT DEFAULT 'Order Placed',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    // 11. Order Items
    $pdo->exec("CREATE TABLE IF NOT EXISTS order_items (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        order_id INTEGER NOT NULL,
        product_id INTEGER NOT NULL,
        vendor_id INTEGER NOT NULL,
        product_title TEXT NOT NULL,
        price REAL NOT NULL,
        quantity INTEGER NOT NULL,
        status TEXT DEFAULT 'Pending'
    )");

    // 12. Payments
    $pdo->exec("CREATE TABLE IF NOT EXISTS payments (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        order_id INTEGER NOT NULL,
        transaction_id TEXT NOT NULL UNIQUE,
        payment_method TEXT NOT NULL,
        amount REAL NOT NULL,
        status TEXT DEFAULT 'PENDING',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    // 13. Reviews
    $pdo->exec("CREATE TABLE IF NOT EXISTS reviews (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        product_id INTEGER NOT NULL,
        user_id INTEGER DEFAULT NULL,
        customer_name TEXT NOT NULL,
        rating INTEGER NOT NULL,
        comment TEXT NOT NULL,
        vendor_response TEXT DEFAULT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    // 14. Notifications
    $pdo->exec("CREATE TABLE IF NOT EXISTS notifications (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER NOT NULL,
        title TEXT NOT NULL,
        message TEXT NOT NULL,
        is_read INTEGER DEFAULT 0,
        type TEXT DEFAULT 'info',
        link TEXT DEFAULT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    // 15. Activity Logs
    $pdo->exec("CREATE TABLE IF NOT EXISTS activity_logs (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER DEFAULT NULL,
        action_description TEXT NOT NULL,
        ip_address TEXT DEFAULT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    // Seed default users if empty
    $checkUser = $pdo->query("SELECT COUNT(*) AS cnt FROM users")->fetch();
    if ($checkUser['cnt'] == 0) {
        $passHash = password_hash('password123', PASSWORD_BCRYPT);
        $pdo->exec("INSERT INTO users (id, name, email, phone, password_hash, role, status, email_verified) VALUES
            (1, 'System Administrator', 'admin@vyaparsetu.com', '+91 99000 11223', '$passHash', 'admin', 'ACTIVE', 1),
            (2, 'Rahul Verma (TechCraft)', 'vendor@vyaparsetu.com', '+91 98765 11111', '$passHash', 'vendor', 'ACTIVE', 1),
            (3, 'Priya Sharma (Handloom)', 'priya@bharattextiles.in', '+91 98765 22222', '$passHash', 'vendor', 'ACTIVE', 1),
            (4, 'Rishiraj Sharma', 'customer@vyaparsetu.com', '+91 98765 43210', '$passHash', 'customer', 'ACTIVE', 1)");
        
        $pdo->exec("INSERT INTO vendors (id, user_id, vendor_code, store_name, owner_name, email, phone, gstin, category, rating, reviews_count, status, commission_rate, address) VALUES
            (1, 2, 'VND-88219', 'TechCraft Electronics', 'Rahul Verma', 'vendor@vyaparsetu.com', '+91 98765 11111', '27AABCT1234H1Z5', 'Electronics', 4.90, 1240, 'APPROVED', '10%', 'Building 4, Mumbai'),
            (2, 3, 'VND-44102', 'Bharat Handloom Textiles', 'Priya Sharma', 'priya@bharattextiles.in', '+91 98765 22222', '27AABCB5678K1Z2', 'Fashion', 4.80, 850, 'APPROVED', '10%', 'Heritage Weaver Lane, Varanasi')");
            
        $pdo->exec("INSERT INTO categories (id, name, slug, icon, description) VALUES
            (1, 'Electronics', 'electronics', 'bi-laptop', 'Gadgets, Smart Devices & Accessories'),
            (2, 'Fashion & Apparel', 'fashion', 'bi-bag-heart', 'Ethnic, Western Wear & Handlooms'),
            (3, 'Spices & Organics', 'spices-organics', 'bi-flower1', 'Farm Fresh Spices & Organic Herbs'),
            (4, 'Home & Living', 'home-living', 'bi-house-door', 'Handicrafts, Décor & Kitchenware'),
            (5, 'Beauty & Wellness', 'beauty-wellness', 'bi-heart-pulse', 'Natural Skincare & Herbal Products')");

        $pdo->exec("INSERT INTO products (id, sku, vendor_id, category_id, title, description, price, original_price, discount, stock, spotlight, status, rating, rating_count) VALUES
            (1, 'TC-WLE-01', 1, 1, 'SonicPods Pro Active Noise Cancelling Earbuds', 'Ultra-low latency wireless earbuds featuring ANC, 30-hour playback with fast charge case.', 2499.00, 4999.00, 50, 42, 'BESTSELLER', 'ACTIVE', 4.80, 312),
            (2, 'TC-SMW-04', 1, 1, 'SmartFit OLED Fitness & SpO2 Smartwatch', 'Sleek aluminum smartwatch with 1.43-inch Always-On AMOLED Display, continuous Heart Rate & SpO2 tracker.', 3999.00, 6999.00, 43, 15, 'FEATURED', 'ACTIVE', 4.90, 184),
            (3, 'BT-SLK-09', 2, 2, 'Handcrafted Pure Mulberry Silk Banarasi Saree', 'Woven by master artisans in Varanasi, this pure silk saree features gold zari brocade work.', 6800.00, 9500.00, 28, 8, 'FEATURED', 'ACTIVE', 4.90, 96),
            (4, 'SS-PEP-02', 1, 3, 'Organic Malabar Black Pepper Whole (500g)', 'Directly sourced from organic certified farms in Western Ghats, Kerala.', 650.00, 850.00, 24, 60, 'BESTSELLER', 'ACTIVE', 5.00, 540)");

        $pdo->exec("INSERT INTO product_images (product_id, image_url, is_primary) VALUES
            (1, 'https://images.unsplash.com/photo-1590658268037-6bf12165a8df?auto=format&fit=crop&w=600&q=80', 1),
            (2, 'https://images.unsplash.com/photo-1579586337278-3befd40fd17a?auto=format&fit=crop&w=600&q=80', 1),
            (3, 'https://images.unsplash.com/photo-1610030469983-98e550d6193c?auto=format&fit=crop&w=600&q=80', 1),
            (4, 'https://images.unsplash.com/photo-1599940824399-b87987ceb72a?auto=format&fit=crop&w=600&q=80', 1)");

        $pdo->exec("INSERT INTO orders (id, order_number, user_id, customer_name, email, phone, address, payment_method, payment_status, subtotal, discount, total_amount, status) VALUES
            (1, 'ORD-99012', 4, 'Rishiraj Sharma', 'customer@vyaparsetu.com', '+91 98765 43210', 'Flat 402, G.V. Acharya Campus Road, Shelu, Maharashtra', 'UPI / Razorpay', 'Paid', 2499.00, 250.00, 2249.00, 'Shipped'),
            (2, 'ORD-99013', 4, 'Ananya Sen', 'ananya@gmail.com', '+91 91234 56789', 'Suite 12, Tech Park Avenue, Pune, Maharashtra', 'Cash on Delivery', 'Pending', 1300.00, 0.00, 1300.00, 'Delivered')");
            
        $pdo->exec("INSERT INTO order_items (id, order_id, product_id, vendor_id, product_title, price, quantity, status) VALUES
            (1, 1, 1, 1, 'SonicPods Pro Active Noise Cancelling Earbuds', 2499.00, 1, 'Shipped'),
            (2, 2, 4, 1, 'Organic Malabar Black Pepper Whole (500g)', 650.00, 2, 'Delivered')");

        $pdo->exec("INSERT INTO activity_logs (user_id, action_description, ip_address) VALUES
            (1, 'System initialized with SQLite database & seed data.', '127.0.0.1')");
    }
}

$db = getDBConnection();
