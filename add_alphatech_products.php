<?php
require_once __DIR__ . '/includes/db.php';

echo "Adding 9 Alpha Tech products (6 (1) to 6 (9)) to database...\n";

function addProductsToDb($db, $dbName) {
    // 1. Ensure User for Alpha Tech
    $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute(['vendor.alphatech@vyaparsetu.in']);
    $user = $stmt->fetch();

    if (!$user) {
        if ($dbName === 'MySQL') {
            $insUser = $db->prepare("INSERT INTO users (name, email, phone, password_hash, role, status) VALUES (?, ?, ?, ?, 'vendor', 'ACTIVE')");
            $insUser->execute(['Alpha Tech', 'vendor.alphatech@vyaparsetu.in', '9811122233', password_hash('vendor123', PASSWORD_DEFAULT)]);
            $userId = $db->lastInsertId();
        } else {
            $db->exec("INSERT OR IGNORE INTO users (id, name, email, phone, password_hash, role, status) VALUES (101, 'Alpha Tech', 'vendor.alphatech@vyaparsetu.in', '9811122233', '$2y$10$12345', 'vendor', 'ACTIVE')");
            $userId = 101;
        }
    } else {
        $userId = $user['id'];
    }

    // 2. Ensure Vendor for Alpha Tech
    $stmtVnd = $db->prepare("SELECT id FROM vendors WHERE store_name = 'Alpha Tech'");
    $stmtVnd->execute();
    $vnd = $stmtVnd->fetch();

    if (!$vnd) {
        if ($dbName === 'MySQL') {
            $insVnd = $db->prepare("INSERT INTO vendors (user_id, vendor_code, store_name, owner_name, email, phone, gstin, category, rating, reviews_count, status, commission_rate) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'APPROVED', '10%')");
            $insVnd->execute([$userId, 'VND-ALPHA01', 'Alpha Tech', 'Alpha Tech Solutions', 'vendor.alphatech@vyaparsetu.in', '9811122233', '27AABCA1234E1Z9', 'Electronics', 4.90, 320]);
            $vendorId = $db->lastInsertId();
        } else {
            $db->exec("INSERT OR IGNORE INTO vendors (id, user_id, vendor_code, store_name, owner_name, email, phone, gstin, category, rating, reviews_count, status, commission_rate) VALUES (101, 101, 'VND-ALPHA01', 'Alpha Tech', 'Alpha Tech Solutions', 'vendor.alphatech@vyaparsetu.in', '9811122233', '27AABCA1234E1Z9', 'Electronics', 4.90, 320, 'APPROVED', '10%')");
            $vendorId = 101;
        }
    } else {
        $vendorId = $vnd['id'];
    }

    // Category ID for Electronics
    $catStmt = $db->query("SELECT id FROM categories WHERE slug = 'electronics' OR name LIKE '%Electronics%' LIMIT 1");
    $catId = $catStmt ? ($catStmt->fetchColumn() ?: 1) : 1;

    // 9 Products Data
    $products = [
        [
            'sku' => 'ALT-PRO-001',
            'title' => 'Alpha Tech Active Noise-Cancelling Bluetooth Headphones Pro',
            'description' => 'Premium Alpha Tech wireless ANC headphones with 40-hour battery life, 40mm neodymium drivers, dual mics for crystal-clear calls, and memory foam cushions.',
            'price' => 4999.00,
            'original_price' => 8999.00,
            'discount' => 44,
            'stock' => 50,
            'image_url' => 'uploads/product_photos/6 (1).jpg',
            'rating' => 4.90,
            'rating_count' => 184,
            'spotlight' => 'BESTSELLER'
        ],
        [
            'sku' => 'ALT-PRO-002',
            'title' => 'Alpha Tech Ultra Smart Watch Series 9 AMOLED Display',
            'description' => '1.96-inch HD AMOLED curved display smartwatch by Alpha Tech featuring Bluetooth calling, heart rate & SpO2 monitoring, 100+ fitness tracking modes, and IP68 waterproof rating.',
            'price' => 3499.00,
            'original_price' => 6999.00,
            'discount' => 50,
            'stock' => 65,
            'image_url' => 'uploads/product_photos/6 (2).jpg',
            'rating' => 4.80,
            'rating_count' => 142,
            'spotlight' => 'FEATURED'
        ],
        [
            'sku' => 'ALT-PRO-003',
            'title' => 'Alpha Tech 20000mAh 22.5W Fast Charge PD Power Bank',
            'description' => 'Heavy-duty metallic slim power bank from Alpha Tech with Power Delivery (PD 3.0), Quick Charge 3.0, dual USB-A + Type-C ports, and intelligent temperature safety system.',
            'price' => 1899.00,
            'original_price' => 3299.00,
            'discount' => 42,
            'stock' => 85,
            'image_url' => 'uploads/product_photos/6 (3).jpg',
            'rating' => 4.70,
            'rating_count' => 98,
            'spotlight' => 'FEATURED'
        ],
        [
            'sku' => 'ALT-PRO-004',
            'title' => 'Alpha Tech Ergonomic RGB Mechanical Gaming Keyboard',
            'description' => 'Tactile blue-switch mechanical gaming keyboard featuring per-key RGB lighting, N-key rollover anti-ghosting, braided Type-C cable, and aircraft-grade aluminum top plate.',
            'price' => 2799.00,
            'original_price' => 4499.00,
            'discount' => 38,
            'stock' => 40,
            'image_url' => 'uploads/product_photos/6 (4).jpg',
            'rating' => 4.90,
            'rating_count' => 210,
            'spotlight' => 'BESTSELLER'
        ],
        [
            'sku' => 'ALT-PRO-005',
            'title' => 'Alpha Tech Quad-Mic Noise Cancelling TWS Studio Earbuds',
            'description' => 'True wireless stereo earbuds by Alpha Tech offering environmental noise cancellation (ENC), 60ms ultra-low latency gaming mode, 32-hour total playback, and touch controls.',
            'price' => 2299.00,
            'original_price' => 3999.00,
            'discount' => 43,
            'stock' => 55,
            'image_url' => 'uploads/product_photos/6 (5).jpg',
            'rating' => 4.60,
            'rating_count' => 76,
            'spotlight' => 'REGULAR'
        ],
        [
            'sku' => 'ALT-PRO-006',
            'title' => 'Alpha Tech 4K Ultra HD Smart Stream Stick',
            'description' => 'High-speed 4K HDR TV streaming stick with Alexa/Google voice remote, Dolby Atmos audio, dual-band Wi-Fi 5, and pre-loaded streaming apps.',
            'price' => 3199.00,
            'original_price' => 5499.00,
            'discount' => 42,
            'stock' => 45,
            'image_url' => 'uploads/product_photos/6 (6).jpg',
            'rating' => 4.80,
            'rating_count' => 115,
            'spotlight' => 'FEATURED'
        ],
        [
            'sku' => 'ALT-PRO-007',
            'title' => 'Alpha Tech High-Precision Ergonomic Wireless Optical Mouse',
            'description' => '2.4GHz rechargeable wireless optical mouse with 4-level adjustable DPI (800-3200), whisper-quiet click buttons, ergonomic rubber grip, and auto sleep energy saving mode.',
            'price' => 999.00,
            'original_price' => 1799.00,
            'discount' => 44,
            'stock' => 100,
            'image_url' => 'uploads/product_photos/6 (7).jpg',
            'rating' => 4.70,
            'rating_count' => 150,
            'spotlight' => 'REGULAR'
        ],
        [
            'sku' => 'ALT-PRO-008',
            'title' => 'Alpha Tech 1080p Full HD Webcam with Dual Microphone & Privacy Shutter',
            'description' => 'Pro streaming 1080p 60fps auto-focus web camera with built-in dual omnidirectional noise-cancelling mics, physical magnetic privacy cover, and 360-degree tripod mount.',
            'price' => 1699.00,
            'original_price' => 2999.00,
            'discount' => 43,
            'stock' => 60,
            'image_url' => 'uploads/product_photos/6 (8).jpg',
            'rating' => 4.80,
            'rating_count' => 135,
            'spotlight' => 'FEATURED'
        ],
        [
            'sku' => 'ALT-PRO-009',
            'title' => 'Alpha Tech High-Speed 7-in-1 USB-C Multi-Port Hub Adapter',
            'description' => 'Aluminum USB-C docking station featuring 4K HDMI 60Hz port, 100W Power Delivery pass-through, SD/TF card reader slots, and 3x USB 3.0 5Gbps high-speed data transfer ports.',
            'price' => 1599.00,
            'original_price' => 2899.00,
            'discount' => 45,
            'stock' => 70,
            'image_url' => 'uploads/product_photos/6 (9).jpg',
            'rating' => 4.90,
            'rating_count' => 190,
            'spotlight' => 'BESTSELLER'
        ]
    ];

    $count = 0;
    foreach ($products as $p) {
        if ($dbName === 'MySQL') {
            $stmtChk = $db->prepare("SELECT id FROM products WHERE sku = ?");
            $stmtChk->execute([$p['sku']]);
            $exist = $stmtChk->fetch();

            if ($exist) {
                $upd = $db->prepare("UPDATE products SET vendor_id=?, category_id=?, title=?, description=?, price=?, original_price=?, discount=?, stock=?, rating=?, rating_count=?, spotlight=?, status='ACTIVE' WHERE sku=?");
                $upd->execute([$vendorId, $catId, $p['title'], $p['description'], $p['price'], $p['original_price'], $p['discount'], $p['stock'], $p['rating'], $p['rating_count'], $p['spotlight'], $p['sku']]);
                $pId = $exist['id'];
            } else {
                $ins = $db->prepare("INSERT INTO products (sku, vendor_id, category_id, title, description, price, original_price, discount, stock, rating, rating_count, spotlight, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'ACTIVE')");
                $ins->execute([$p['sku'], $vendorId, $catId, $p['title'], $p['description'], $p['price'], $p['original_price'], $p['discount'], $p['stock'], $p['rating'], $p['rating_count'], $p['spotlight']]);
                $pId = $db->lastInsertId();
            }

            if ($pId) {
                $db->exec("DELETE FROM product_images WHERE product_id = $pId");
                $insImg = $db->prepare("INSERT INTO product_images (product_id, image_url, is_primary) VALUES (?, ?, 1)");
                $insImg->execute([$pId, $p['image_url']]);
            }
        } else {
            // SQLite
            $stmt = $db->prepare("INSERT OR REPLACE INTO products (sku, vendor_id, category_id, title, description, price, original_price, discount, stock, rating, rating_count, spotlight, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'ACTIVE')");
            $stmt->execute([$p['sku'], $vendorId, $catId, $p['title'], $p['description'], $p['price'], $p['original_price'], $p['discount'], $p['stock'], $p['rating'], $p['rating_count'], $p['spotlight']]);
            
            $stmtId = $db->prepare("SELECT id FROM products WHERE sku = ?");
            $stmtId->execute([$p['sku']]);
            $pId = $stmtId->fetchColumn();

            if ($pId) {
                $db->exec("DELETE FROM product_images WHERE product_id = $pId");
                $insImg = $db->prepare("INSERT INTO product_images (product_id, image_url, is_primary) VALUES (?, ?, 1)");
                $insImg->execute([$pId, $p['image_url']]);
            }
        }
        $count++;
    }

    echo "Successfully processed $count Alpha Tech products in $dbName!\n";
}

// 1. MySQL Connection
try {
    $mysql = new PDO("mysql:host=localhost;dbname=vyapar_setu;charset=utf8mb4", "root", "", [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    addProductsToDb($mysql, 'MySQL');
} catch (Exception $e) {
    echo "MySQL Error: " . $e->getMessage() . "\n";
}

// 2. SQLite Connection
try {
    $sqlite = new PDO("sqlite:" . __DIR__ . "/config/vyapar_setu.sqlite", null, null, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    addProductsToDb($sqlite, 'SQLite');
} catch (Exception $e) {
    echo "SQLite Error: " . $e->getMessage() . "\n";
}

echo "Done seeding Alpha Tech products!\n";
