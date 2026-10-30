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

        // 2. Ensure customers table (Clean Separation of Customer Details)
        $pdo->exec("CREATE TABLE IF NOT EXISTS `customers` (
          `id` INT AUTO_INCREMENT PRIMARY KEY,
          `user_id` INT NOT NULL UNIQUE,
          `customer_code` VARCHAR(20) NOT NULL UNIQUE,
          `full_name` VARCHAR(100) NOT NULL,
          `email` VARCHAR(100) NOT NULL,
          `phone` VARCHAR(20) DEFAULT NULL,
          `shipping_address` TEXT DEFAULT NULL,
          `city` VARCHAR(60) DEFAULT NULL,
          `state` VARCHAR(60) DEFAULT NULL,
          `pincode` VARCHAR(10) DEFAULT NULL,
          `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
          `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        try {
            $custBackfill = $pdo->query("SELECT u.id, u.name, u.email, u.phone FROM users u LEFT JOIN customers c ON u.id = c.user_id WHERE u.role = 'customer' AND c.id IS NULL")->fetchAll();
            $insCust = $pdo->prepare("INSERT INTO customers (user_id, customer_code, full_name, email, phone, shipping_address, city, state, pincode) VALUES (?, ?, ?, ?, ?, 'Flat 402, G.V. Acharya Campus Road, Shelu', 'Shelu / Karjat', 'Maharashtra', '410101')");
            foreach ($custBackfill as $cb) {
                $cCode = 'CUST-' . rand(10000, 99999);
                $insCust->execute([$cb['id'], $cCode, $cb['name'], $cb['email'], $cb['phone']]);
            }
        } catch (Exception $e) {
            // Ignore if already backfilled
        }

        // 3. Ensure vendors table
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
                (3, 'Priya Sharma (Handloom)', 'priya@bharattextiles.in', '+91 98765 22222', '$pass', 'vendor', 'ACTIVE', 1),
                (4, 'Amrit Patel (Organic)', 'amrit@swadeshispices.org', '+91 98765 33333', '$pass', 'vendor', 'ACTIVE', 1),
                (5, 'Ananya Sen', 'ananya@gmail.com', '+91 91234 56789', '$pass', 'customer', 'ACTIVE', 1)");
        }

        // Seed vendors if empty
        $vCount = $pdo->query("SELECT COUNT(*) AS cnt FROM vendors")->fetch()['cnt'] ?? 0;
        if ($vCount == 0) {
            $pdo->exec("INSERT INTO `vendors` (`id`, `user_id`, `vendor_code`, `store_name`, `owner_name`, `email`, `phone`, `gstin`, `category`, `rating`, `reviews_count`, `status`, `commission_rate`, `address`) VALUES
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



        // Auto-heal / sync missing product descriptions or category fixes
        try {
            $pdo->exec("UPDATE products SET category_id = 1 WHERE id = 13 AND category_id != 1");
            $pdo->exec("UPDATE products SET title = 'Beige jacket & Blue baggy jeans combo' WHERE id = 9");
        } catch (Exception $e) {}

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

    // 2. Customers (Dedicated Customer Details)
    $pdo->exec("CREATE TABLE IF NOT EXISTS customers (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER NOT NULL UNIQUE,
        customer_code TEXT NOT NULL UNIQUE,
        full_name TEXT NOT NULL,
        email TEXT NOT NULL,
        phone TEXT DEFAULT NULL,
        shipping_address TEXT DEFAULT NULL,
        city TEXT DEFAULT NULL,
        state TEXT DEFAULT NULL,
        pincode TEXT DEFAULT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    // 3. Vendors
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
            (3, 'Priya Sharma (Handloom)', 'priya@bharattextiles.in', '+91 98765 22222', '$passHash', 'vendor', 'ACTIVE', 1),
            (4, 'Amrit Patel (Organic)', 'amrit@swadeshispices.org', '+91 98765 33333', '$passHash', 'vendor', 'ACTIVE', 1),
            (5, 'Ananya Sen', 'ananya@gmail.com', '+91 91234 56789', '$passHash', 'customer', 'ACTIVE', 1)");
        
        $pdo->exec("INSERT INTO vendors (id, user_id, vendor_code, store_name, owner_name, email, phone, gstin, category, rating, reviews_count, status, commission_rate, address) VALUES
            (2, 3, 'VND-44102', 'Bharat Handloom Textiles', 'Priya Sharma', 'priya@bharattextiles.in', '+91 98765 22222', '27AABCB5678K1Z2', 'Fashion', 4.80, 850, 'APPROVED', '10%', 'Heritage Weaver Lane, Varanasi'),
            (3, 4, 'VND-99301', 'Swadeshi Organic Spices', 'Amrit Patel', 'amrit@swadeshispices.org', '+91 98765 33333', '27AABCS9912M1Z9', 'Spices & Organics', 4.90, 2410, 'APPROVED', '10%', 'Spices Hub, Kochi, Kerala')");
            
        $pdo->exec("INSERT INTO categories (id, name, slug, icon, description) VALUES
            (1, 'Electronics', 'electronics', 'bi-laptop', 'Gadgets, Smart Devices & Accessories'),
            (2, 'Fashion & Apparel', 'fashion', 'bi-bag-heart', 'Ethnic, Western Wear & Handlooms'),
            (3, 'Spices & Organics', 'spices-organics', 'bi-flower1', 'Farm Fresh Spices & Organic Herbs'),
            (4, 'Home & Living', 'home-living', 'bi-house-door', 'Handicrafts, Décor & Kitchenware'),
            (5, 'Beauty & Wellness', 'beauty-wellness', 'bi-heart-pulse', 'Natural Skincare & Herbal Products')");



        $pdo->exec("INSERT INTO activity_logs (user_id, action_description, ip_address) VALUES
            (NULL, 'System initialized with SQLite database & seed data.', '127.0.0.1')");
    }
}

$db = getDBConnection();
