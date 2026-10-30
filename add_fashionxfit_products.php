<?php
require_once __DIR__ . '/includes/db.php';

echo "Adding 12 FashionXFit products (1 (1) to 1 (12)) to database...\n";

function addProductsToDb($db, $dbName) {
    // 1. Ensure User
    $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute(['vendor.fashionxfit@vyaparsetu.in']);
    $user = $stmt->fetch();

    if (!$user) {
        if ($dbName === 'MySQL') {
            $insUser = $db->prepare("INSERT INTO users (name, email, phone, password_hash, role, status) VALUES (?, ?, ?, ?, 'vendor', 'ACTIVE')");
            $insUser->execute(['FashionXFit', 'vendor.fashionxfit@vyaparsetu.in', '9844455566', password_hash('vendor123', PASSWORD_DEFAULT)]);
            $userId = $db->lastInsertId();
        } else {
            $db->exec("INSERT OR IGNORE INTO users (id, name, email, phone, password_hash, role, status) VALUES (102, 'FashionXFit', 'vendor.fashionxfit@vyaparsetu.in', '9844455566', '$2y$10$12345', 'vendor', 'ACTIVE')");
            $userId = 102;
        }
    } else {
        $userId = $user['id'];
    }

    // 2. Ensure Vendor
    $stmtVnd = $db->prepare("SELECT id FROM vendors WHERE store_name = 'FashionXFit'");
    $stmtVnd->execute();
    $vnd = $stmtVnd->fetch();

    if (!$vnd) {
        if ($dbName === 'MySQL') {
            $insVnd = $db->prepare("INSERT INTO vendors (user_id, vendor_code, store_name, owner_name, email, phone, gstin, category, rating, reviews_count, status, commission_rate) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'APPROVED', '10%')");
            $insVnd->execute([$userId, 'VND-FASHION01', 'FashionXFit', 'FashionXFit Studio', 'vendor.fashionxfit@vyaparsetu.in', '9844455566', '27AABCF5678F1Z3', 'Fashion', 4.85, 410]);
            $vendorId = $db->lastInsertId();
        } else {
            $db->exec("INSERT OR IGNORE INTO vendors (id, user_id, vendor_code, store_name, owner_name, email, phone, gstin, category, rating, reviews_count, status, commission_rate) VALUES (102, 102, 'VND-FASHION01', 'FashionXFit', 'FashionXFit Studio', 'vendor.fashionxfit@vyaparsetu.in', '9844455566', '27AABCF5678F1Z3', 'Fashion', 4.85, 410, 'APPROVED', '10%')");
            $vendorId = 102;
        }
    } else {
        $vendorId = $vnd['id'];
    }

    // Category ID for Fashion
    $catStmt = $db->query("SELECT id FROM categories WHERE slug = 'fashion' OR name LIKE '%Fashion%' LIMIT 1");
    $catId = $catStmt ? ($catStmt->fetchColumn() ?: 2) : 2;

    // 12 Products Data
    $products = [
        [
            'sku' => 'FXF-PRO-001',
            'title' => 'FashionXFit Pro Compression Performance Activewear Hoodie',
            'description' => 'Premium moisture-wicking athletic hoodie from FashionXFit with 4-way stretch fleece fabric, ergonomic thumbholes, and concealed zip pocket for running and workout.',
            'price' => 1499.00,
            'original_price' => 2999.00,
            'discount' => 50,
            'stock' => 75,
            'image_url' => 'uploads/product_photos/1 (1).jpg',
            'rating' => 4.90,
            'rating_count' => 230,
            'spotlight' => 'BESTSELLER'
        ],
        [
            'sku' => 'FXF-PRO-002',
            'title' => 'FashionXFit Breathable Flex Gym Training Joggers',
            'description' => 'Ultra-lightweight dry-fit gym joggers with adjustable drawstring waistband, deep zippered phone pockets, and ribbed ankle cuffs.',
            'price' => 1199.00,
            'original_price' => 2299.00,
            'discount' => 48,
            'stock' => 90,
            'image_url' => 'uploads/product_photos/1 (2).jpg',
            'rating' => 4.80,
            'rating_count' => 165,
            'spotlight' => 'FEATURED'
        ],
        [
            'sku' => 'FXF-PRO-003',
            'title' => 'FashionXFit Seamless Athletic Compression Sports Tee',
            'description' => 'Seamless body-mapping compression t-shirt engineered by FashionXFit to support core muscles, reduce fatigue, and maximize airflow during intense workout sessions.',
            'price' => 899.00,
            'original_price' => 1599.00,
            'discount' => 44,
            'stock' => 110,
            'image_url' => 'uploads/product_photos/1 (3).jpg',
            'rating' => 4.70,
            'rating_count' => 140,
            'spotlight' => 'REGULAR'
        ],
        [
            'sku' => 'FXF-PRO-004',
            'title' => 'FashionXFit Lightweight Pro Cushion Athletic Running Shoes',
            'description' => 'High-rebound foam cushioning running sneakers featuring breathable knit mesh upper, non-slip rubber tread, and reinforced heel counter for maximum agility.',
            'price' => 2499.00,
            'original_price' => 4999.00,
            'discount' => 50,
            'stock' => 60,
            'image_url' => 'uploads/product_photos/1 (4).jpg',
            'rating' => 4.90,
            'rating_count' => 310,
            'spotlight' => 'BESTSELLER'
        ],
        [
            'sku' => 'FXF-PRO-005',
            'title' => 'FashionXFit Premium Pima Cotton Slim-Fit Polo T-Shirt',
            'description' => '100% combed Pima cotton polo shirt with anti-pilling wash, structured collar, 2-button placket, and subtle embroidered FashionXFit logo.',
            'price' => 999.00,
            'original_price' => 1899.00,
            'discount' => 47,
            'stock' => 85,
            'image_url' => 'uploads/product_photos/1 (5).jpg',
            'rating' => 4.75,
            'rating_count' => 125,
            'spotlight' => 'FEATURED'
        ],
        [
            'sku' => 'FXF-PRO-006',
            'title' => 'FashionXFit All-Weather Lightweight Windbreaker Track Jacket',
            'description' => 'Water-resistant, wind-proof lightweight outer track jacket featuring packable hood, mesh lining, and reflective night-visibility strips.',
            'price' => 1799.00,
            'original_price' => 3499.00,
            'discount' => 49,
            'stock' => 65,
            'image_url' => 'uploads/product_photos/1 (6).jpg',
            'rating' => 4.80,
            'rating_count' => 95,
            'spotlight' => 'FEATURED'
        ],
        [
            'sku' => 'FXF-PRO-007',
            'title' => 'FashionXFit Urban Flex Stretch Slim Fit Denim Jeans',
            'description' => 'Flexible elastane-blend stretch denim jeans with classic 5-pocket styling, reinforced stress points, and modern slim tapered leg fit.',
            'price' => 1699.00,
            'original_price' => 3199.00,
            'discount' => 47,
            'stock' => 80,
            'image_url' => 'uploads/product_photos/1 (7).jpg',
            'rating' => 4.70,
            'rating_count' => 180,
            'spotlight' => 'REGULAR'
        ],
        [
            'sku' => 'FXF-PRO-008',
            'title' => 'FashionXFit Seamless Sports Bra & Leggings Activewear Set',
            'description' => 'High-support padded sports bra paired with high-waisted squat-proof compression leggings for yoga, pilates, and fitness training.',
            'price' => 1899.00,
            'original_price' => 3799.00,
            'discount' => 50,
            'stock' => 50,
            'image_url' => 'uploads/product_photos/1 (8).jpg',
            'rating' => 4.90,
            'rating_count' => 195,
            'spotlight' => 'BESTSELLER'
        ],
        [
            'sku' => 'FXF-PRO-009',
            'title' => 'FashionXFit Oversized Heavyweight Cotton Streetwear Graphic Tee',
            'description' => '240 GSM 100% Terry cotton streetwear t-shirt with dropped shoulder cut, high-density chest print, and relaxed vintage wash finish.',
            'price' => 799.00,
            'original_price' => 1499.00,
            'discount' => 47,
            'stock' => 100,
            'image_url' => 'uploads/product_photos/1 (9).jpg',
            'rating' => 4.65,
            'rating_count' => 110,
            'spotlight' => 'FEATURED'
        ],
        [
            'sku' => 'FXF-PRO-010',
            'title' => 'FashionXFit Thermal Tech Fleece Zip-Up Training Vest',
            'description' => 'Sleeveless insulated thermal fleece jacket designed for morning runs and gym layering, featuring high stand collar and zippered chest pocket.',
            'price' => 1299.00,
            'original_price' => 2499.00,
            'discount' => 48,
            'stock' => 70,
            'image_url' => 'uploads/product_photos/1 (10).jpg',
            'rating' => 4.75,
            'rating_count' => 85,
            'spotlight' => 'REGULAR'
        ],
        [
            'sku' => 'FXF-PRO-011',
            'title' => 'FashionXFit Tactical Cargo Utility Workout Shorts',
            'description' => 'Heavy-duty quick-dry fabric workout cargo shorts with towel loop holder, zip cargo pockets, and 7-inch inseam for unrestricted mobility.',
            'price' => 949.00,
            'original_price' => 1799.00,
            'discount' => 47,
            'stock' => 95,
            'image_url' => 'uploads/product_photos/1 (11).jpg',
            'rating' => 4.80,
            'rating_count' => 145,
            'spotlight' => 'FEATURED'
        ],
        [
            'sku' => 'FXF-PRO-012',
            'title' => 'FashionXFit Waterproof Gym Duffle Bag with Shoe Compartment',
            'description' => '45L high-density Oxford polyester sports duffle bag featuring isolated ventilated sneaker pocket, wet-dry separator pocket, and padded shoulder strap.',
            'price' => 1399.00,
            'original_price' => 2799.00,
            'discount' => 50,
            'stock' => 45,
            'image_url' => 'uploads/product_photos/1 (12).jpg',
            'rating' => 4.90,
            'rating_count' => 260,
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

    echo "Successfully processed $count products in $dbName!\n";
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

echo "Done seeding FashionXFit products!\n";
