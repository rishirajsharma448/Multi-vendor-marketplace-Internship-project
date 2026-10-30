<?php
require_once __DIR__ . '/includes/db.php';

$db = getDBConnection();

echo "Seeding Alpha Tech & FashionXFit Vendors & Products...\n";

// 1. Insert/Get User for Alpha Tech
$stmtUser1 = $db->prepare("SELECT id FROM users WHERE email = ?");
$stmtUser1->execute(['vendor.alphatech@vyaparsetu.in']);
$user1 = $stmtUser1->fetch();

if (!$user1) {
    $insUser = $db->prepare("INSERT INTO users (name, email, phone, password_hash, role, status) VALUES (?, ?, ?, ?, 'vendor', 'ACTIVE')");
    $insUser->execute(['Alpha Tech', 'vendor.alphatech@vyaparsetu.in', '9811122233', password_hash('vendor123', PASSWORD_DEFAULT)]);
    $user1_id = $db->lastInsertId();
} else {
    $user1_id = $user1['id'];
}

// 2. Insert/Get Vendor for Alpha Tech
$stmtVnd1 = $db->prepare("SELECT id FROM vendors WHERE store_name = 'Alpha Tech'");
$stmtVnd1->execute();
$vnd1 = $stmtVnd1->fetch();

if (!$vnd1) {
    $insVnd = $db->prepare("INSERT INTO vendors (user_id, vendor_code, store_name, owner_name, email, phone, gstin, category, rating, reviews_count, status, commission_rate) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'APPROVED', '10%')");
    $insVnd->execute([$user1_id, 'VND-ALPHA01', 'Alpha Tech', 'Alpha Tech Solutions', 'vendor.alphatech@vyaparsetu.in', '9811122233', '27AABCA1234E1Z9', 'Electronics', 4.90, 320]);
    $vnd1_id = $db->lastInsertId();
} else {
    $vnd1_id = $vnd1['id'];
}

// 3. Insert/Get User for FashionXFit
$stmtUser2 = $db->prepare("SELECT id FROM users WHERE email = ?");
$stmtUser2->execute(['vendor.fashionxfit@vyaparsetu.in']);
$user2 = $stmtUser2->fetch();

if (!$user2) {
    $insUser = $db->prepare("INSERT INTO users (name, email, phone, password_hash, role, status) VALUES (?, ?, ?, ?, 'vendor', 'ACTIVE')");
    $insUser->execute(['FashionXFit', 'vendor.fashionxfit@vyaparsetu.in', '9844455566', password_hash('vendor123', PASSWORD_DEFAULT)]);
    $user2_id = $db->lastInsertId();
} else {
    $user2_id = $user2['id'];
}

// 4. Insert/Get Vendor for FashionXFit
$stmtVnd2 = $db->prepare("SELECT id FROM vendors WHERE store_name = 'FashionXFit'");
$stmtVnd2->execute();
$vnd2 = $stmtVnd2->fetch();

if (!$vnd2) {
    $insVnd = $db->prepare("INSERT INTO vendors (user_id, vendor_code, store_name, owner_name, email, phone, gstin, category, rating, reviews_count, status, commission_rate) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'APPROVED', '10%')");
    $insVnd->execute([$user2_id, 'VND-FASHION01', 'FashionXFit', 'FashionXFit Studio', 'vendor.fashionxfit@vyaparsetu.in', '9844455566', '27AABCF5678F1Z3', 'Fashion', 4.85, 410]);
    $vnd2_id = $db->lastInsertId();
} else {
    $vnd2_id = $vnd2['id'];
}

echo "Alpha Tech Vendor ID: $vnd1_id\n";
echo "FashionXFit Vendor ID: $vnd2_id\n";

// Get category IDs
$catElectronics = $db->query("SELECT id FROM categories WHERE slug = 'electronics' OR name LIKE '%Electronics%' LIMIT 1")->fetchColumn() ?: 1;
$catFashion = $db->query("SELECT id FROM categories WHERE slug = 'fashion' OR name LIKE '%Fashion%' LIMIT 1")->fetchColumn() ?: 2;

$productsData = [
    // Alpha Tech Products
    [
        'sku' => 'SKU-ALPHA-001',
        'vendor_id' => $vnd1_id,
        'category_id' => $catElectronics,
        'category_name' => 'Electronics',
        'title' => 'Alpha Tech Active Noise-Cancelling Headphones Pro',
        'description' => 'Premium Alpha Tech ANC wireless Bluetooth 5.3 headphones with 40-hour battery life, immersive bass, ergonomic memory foam earcups, and dual mic clarity.',
        'price' => 4999.00,
        'original_price' => 8999.00,
        'discount' => 44,
        'stock' => 50,
        'image_url' => 'uploads/product_photos/file_1786033724_4830.jpeg',
        'rating' => 4.90,
        'rating_count' => 184,
        'spotlight' => 'BESTSELLER'
    ],
    [
        'sku' => 'SKU-ALPHA-002',
        'vendor_id' => $vnd1_id,
        'category_id' => $catElectronics,
        'category_name' => 'Electronics',
        'title' => 'Alpha Tech Ultra Smart Watch Series 9 AMOLED Display',
        'description' => 'High-definition 1.96-inch AMOLED display smartwatch by Alpha Tech featuring Bluetooth calling, heart rate & SpO2 tracking, 100+ sports modes, and IP68 water resistance.',
        'price' => 3499.00,
        'original_price' => 6999.00,
        'discount' => 50,
        'stock' => 65,
        'image_url' => 'uploads/product_photos/file_1786033839_5825.jpeg',
        'rating' => 4.80,
        'rating_count' => 142,
        'spotlight' => 'FEATURED'
    ],
    [
        'sku' => 'SKU-ALPHA-003',
        'vendor_id' => $vnd1_id,
        'category_id' => $catElectronics,
        'category_name' => 'Electronics',
        'title' => 'Alpha Tech 20000mAh 22.5W Fast Charge Power Bank',
        'description' => 'Compact metallic power bank from Alpha Tech with Power Delivery (PD 3.0), Type-C input/output, dual USB ports, and multi-layer safety protection.',
        'price' => 1899.00,
        'original_price' => 3299.00,
        'discount' => 42,
        'stock' => 85,
        'image_url' => 'uploads/product_photos/file_1786033887_2392.jpeg',
        'rating' => 4.70,
        'rating_count' => 98,
        'spotlight' => 'FEATURED'
    ],
    [
        'sku' => 'SKU-ALPHA-004',
        'vendor_id' => $vnd1_id,
        'category_id' => $catElectronics,
        'category_name' => 'Electronics',
        'title' => 'Alpha Tech Mechanical Gaming Keyboard RGB Backlit',
        'description' => 'Tactile blue switch mechanical gaming keyboard with custom RGB backlighting, anti-ghosting keys, detachable Type-C cable, and heavy-duty aluminum chassis.',
        'price' => 2799.00,
        'original_price' => 4499.00,
        'discount' => 38,
        'stock' => 35,
        'image_url' => 'uploads/product_photos/file_1786088272_2660.jpeg',
        'rating' => 4.90,
        'rating_count' => 210,
        'spotlight' => 'BESTSELLER'
    ],
    [
        'sku' => 'SKU-ALPHA-005',
        'vendor_id' => $vnd1_id,
        'category_id' => $catElectronics,
        'category_name' => 'Electronics',
        'title' => 'Alpha Tech Studio Earbuds with Quad Mic Noise Cancellation',
        'description' => 'True wireless earbuds engineered by Alpha Tech offering low latency gaming mode, 32-hour playback, touch controls, and environmental noise cancellation.',
        'price' => 2299.00,
        'original_price' => 3999.00,
        'discount' => 43,
        'stock' => 55,
        'image_url' => 'uploads/product_photos/file_1786629168_6088.jpg',
        'rating' => 4.60,
        'rating_count' => 76,
        'spotlight' => 'REGULAR'
    ],
    [
        'sku' => 'SKU-ALPHA-006',
        'vendor_id' => $vnd1_id,
        'category_id' => $catElectronics,
        'category_name' => 'Electronics',
        'title' => 'Alpha Tech 4K Ultra HD Smart TV Streaming Stick',
        'description' => 'Powerful 4K streaming stick with voice remote, Dolby Vision & Atmos support, dual-band Wi-Fi, and access to all major streaming platforms.',
        'price' => 3199.00,
        'original_price' => 5499.00,
        'discount' => 42,
        'stock' => 40,
        'image_url' => 'uploads/product_photos/file_1786629423_6069.jpg',
        'rating' => 4.80,
        'rating_count' => 115,
        'spotlight' => 'FEATURED'
    ],
    [
        'sku' => 'SKU-ALPHA-007',
        'vendor_id' => $vnd1_id,
        'category_id' => $catElectronics,
        'category_name' => 'Electronics',
        'title' => 'Alpha Tech High-Precision Ergonomic Wireless Mouse',
        'description' => '2.4GHz wireless mouse with adjustable DPI (800-3200), silent click switches, ergonomic thumb rest, and rechargeable 500mAh lithium battery.',
        'price' => 999.00,
        'original_price' => 1799.00,
        'discount' => 44,
        'stock' => 100,
        'image_url' => 'uploads/product_photos/file_1786629489_3279.jpg',
        'rating' => 4.70,
        'rating_count' => 150,
        'spotlight' => 'REGULAR'
    ],

    // FashionXFit Products
    [
        'sku' => 'SKU-FASHION-001',
        'vendor_id' => $vnd2_id,
        'category_id' => $catFashion,
        'category_name' => 'Fashion',
        'title' => 'FashionXFit Performance Athletic Activewear Hoodie',
        'description' => 'Premium moisture-wicking fleece hoodie by FashionXFit designed for gym workouts, outdoor running, and modern athleisure style.',
        'price' => 1499.00,
        'original_price' => 2999.00,
        'discount' => 50,
        'stock' => 70,
        'image_url' => 'uploads/product_photos/file_1786628293_8588.jpg',
        'rating' => 4.90,
        'rating_count' => 230,
        'spotlight' => 'BESTSELLER'
    ],
    [
        'sku' => 'SKU-FASHION-002',
        'vendor_id' => $vnd2_id,
        'category_id' => $catFashion,
        'category_name' => 'Fashion',
        'title' => 'FashionXFit Breathable Flex Gym Joggers',
        'description' => '4-way stretch breathable training joggers with deep zippered pockets, tapered elastic cuffs, and quick-dry fabric technology.',
        'price' => 1199.00,
        'original_price' => 2299.00,
        'discount' => 48,
        'stock' => 90,
        'image_url' => 'uploads/product_photos/file_1786628339_7720.jpg',
        'rating' => 4.80,
        'rating_count' => 165,
        'spotlight' => 'FEATURED'
    ],
    [
        'sku' => 'SKU-FASHION-003',
        'vendor_id' => $vnd2_id,
        'category_id' => $catFashion,
        'category_name' => 'Fashion',
        'title' => 'FashionXFit Seamless Compression Sports T-Shirt',
        'description' => 'Body-mapping seamless compression top engineered by FashionXFit for maximum muscle support, reduced friction, and optimal airflow.',
        'price' => 899.00,
        'original_price' => 1599.00,
        'discount' => 44,
        'stock' => 110,
        'image_url' => 'uploads/product_photos/file_1786628693_2946.jpg',
        'rating' => 4.70,
        'rating_count' => 140,
        'spotlight' => 'REGULAR'
    ],
    [
        'sku' => 'SKU-FASHION-004',
        'vendor_id' => $vnd2_id,
        'category_id' => $catFashion,
        'category_name' => 'Fashion',
        'title' => 'FashionXFit Lightweight Pro Cushion Running Shoes',
        'description' => 'High-impact cushioning athletic running shoes with breathable mesh upper, anti-slip rubber outsole, and ergonomic arch support.',
        'price' => 2499.00,
        'original_price' => 4999.00,
        'discount' => 50,
        'stock' => 55,
        'image_url' => 'uploads/product_photos/file_1786628723_6405.jpg',
        'rating' => 4.90,
        'rating_count' => 310,
        'spotlight' => 'BESTSELLER'
    ],
    [
        'sku' => 'SKU-FASHION-005',
        'vendor_id' => $vnd2_id,
        'category_id' => $catFashion,
        'category_name' => 'Fashion',
        'title' => 'FashionXFit Premium Cotton Slim Fit Polo Shirt',
        'description' => '100% Pima cotton pique polo shirt with reinforced collar, classic 2-button placket, and subtle embroidered FashionXFit logo.',
        'price' => 999.00,
        'original_price' => 1899.00,
        'discount' => 47,
        'stock' => 80,
        'image_url' => 'uploads/product_photos/file_1786628746_5210.jpg',
        'rating' => 4.75,
        'rating_count' => 125,
        'spotlight' => 'FEATURED'
    ],
    [
        'sku' => 'SKU-FASHION-006',
        'vendor_id' => $vnd2_id,
        'category_id' => $catFashion,
        'category_name' => 'Fashion',
        'title' => 'FashionXFit All-Weather Windbreaker Track Jacket',
        'description' => 'Water-repellent windbreaker jacket featuring a foldable hood, full front zip, breathable mesh lining, and reflective safety strips.',
        'price' => 1799.00,
        'original_price' => 3499.00,
        'discount' => 49,
        'stock' => 65,
        'image_url' => 'uploads/product_photos/file_1786628775_9057.jpg',
        'rating' => 4.80,
        'rating_count' => 95,
        'spotlight' => 'FEATURED'
    ],
    [
        'sku' => 'SKU-FASHION-007',
        'vendor_id' => $vnd2_id,
        'category_id' => $catFashion,
        'category_name' => 'Fashion',
        'title' => 'FashionXFit Urban Flex Stretch Denim Jeans',
        'description' => 'Flexible stretch denim jeans with modern slim fit cut, durable double-stitched seams, and comfortable mid-rise waistband.',
        'price' => 1699.00,
        'original_price' => 3199.00,
        'discount' => 47,
        'stock' => 75,
        'image_url' => 'uploads/product_photos/file_1786628799_1419.jpg',
        'rating' => 4.70,
        'rating_count' => 180,
        'spotlight' => 'REGULAR'
    ]
];

$stmtCheckProd = $db->prepare("SELECT id FROM products WHERE sku = ?");
$insProduct = $db->prepare("INSERT INTO products 
    (sku, vendor_id, category_id, category_name, title, description, price, original_price, discount, stock, image_url, rating, rating_count, spotlight, status)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'ACTIVE')");
$updProduct = $db->prepare("UPDATE products SET 
    vendor_id=?, category_id=?, category_name=?, title=?, description=?, price=?, original_price=?, discount=?, stock=?, image_url=?, rating=?, rating_count=?, spotlight=?, status='ACTIVE'
    WHERE sku=?");

$insImg = $db->prepare("INSERT INTO product_images (product_id, image_url, is_primary) VALUES (?, ?, 1)");

$count = 0;
foreach ($productsData as $p) {
    try {
        $stmtCheckProd->execute([$p['sku']]);
        $existing = $stmtCheckProd->fetch();
        if ($existing) {
            $updProduct->execute([
                $p['vendor_id'], $p['category_id'], $p['category_name'],
                $p['title'], $p['description'], $p['price'], $p['original_price'],
                $p['discount'], $p['stock'], $p['image_url'], $p['rating'],
                $p['rating_count'], $p['spotlight'], $p['sku']
            ]);
            $prodId = $existing['id'];
        } else {
            $insProduct->execute([
                $p['sku'], $p['vendor_id'], $p['category_id'], $p['category_name'],
                $p['title'], $p['description'], $p['price'], $p['original_price'],
                $p['discount'], $p['stock'], $p['image_url'], $p['rating'],
                $p['rating_count'], $p['spotlight']
            ]);
            $prodId = $db->lastInsertId();
            if ($prodId) {
                $insImg->execute([$prodId, $p['image_url']]);
            }
        }
        $count++;
    } catch (Exception $e) {
        echo "Error inserting " . $p['title'] . ": " . $e->getMessage() . "\n";
    }
}

echo "Successfully inserted $count products for Alpha Tech and FashionXFit!\n";
