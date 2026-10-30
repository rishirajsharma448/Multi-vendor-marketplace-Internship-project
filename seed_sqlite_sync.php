<?php
// Ensure SQLite database also has Alpha Tech and FashionXFit
$sqliteFile = __DIR__ . '/config/vyapar_setu.sqlite';
if (file_exists($sqliteFile)) {
    echo "Seeding SQLite database at $sqliteFile...\n";
    $sqlitePdo = new PDO("sqlite:" . $sqliteFile);
    $sqlitePdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Users
    $sqlitePdo->exec("INSERT OR IGNORE INTO users (id, name, email, phone, password_hash, role, status) VALUES (101, 'Alpha Tech', 'vendor.alphatech@vyaparsetu.in', '9811122233', '$2y$10$12345', 'vendor', 'ACTIVE')");
    $sqlitePdo->exec("INSERT OR IGNORE INTO users (id, name, email, phone, password_hash, role, status) VALUES (102, 'FashionXFit', 'vendor.fashionxfit@vyaparsetu.in', '9844455566', '$2y$10$12345', 'vendor', 'ACTIVE')");

    // Vendors
    $sqlitePdo->exec("INSERT OR IGNORE INTO vendors (id, user_id, vendor_code, store_name, owner_name, email, phone, gstin, category, rating, reviews_count, status, commission_rate) VALUES (101, 101, 'VND-ALPHA01', 'Alpha Tech', 'Alpha Tech Solutions', 'vendor.alphatech@vyaparsetu.in', '9811122233', '27AABCA1234E1Z9', 'Electronics', 4.90, 320, 'APPROVED', '10%')");
    $sqlitePdo->exec("INSERT OR IGNORE INTO vendors (id, user_id, vendor_code, store_name, owner_name, email, phone, gstin, category, rating, reviews_count, status, commission_rate) VALUES (102, 102, 'VND-FASHION01', 'FashionXFit', 'FashionXFit Studio', 'vendor.fashionxfit@vyaparsetu.in', '9844455566', '27AABCF5678F1Z3', 'Fashion', 4.85, 410, 'APPROVED', '10%')");

    // Products
    $products = [
        ['SKU-ALPHA-001', 101, 1, 'Alpha Tech Active Noise-Cancelling Headphones Pro', 'Premium Alpha Tech ANC wireless Bluetooth 5.3 headphones with 40-hour battery life.', 4999.00, 8999.00, 44, 50, 'uploads/product_photos/file_1786033724_4830.jpeg', 4.90, 184, 'BESTSELLER'],
        ['SKU-ALPHA-002', 101, 1, 'Alpha Tech Ultra Smart Watch Series 9 AMOLED Display', 'High-definition 1.96-inch AMOLED display smartwatch by Alpha Tech.', 3499.00, 6999.00, 50, 65, 'uploads/product_photos/file_1786033839_5825.jpeg', 4.80, 142, 'FEATURED'],
        ['SKU-ALPHA-003', 101, 1, 'Alpha Tech 20000mAh 22.5W Fast Charge Power Bank', 'Compact metallic power bank from Alpha Tech with Power Delivery.', 1899.00, 3299.00, 42, 85, 'uploads/product_photos/file_1786033887_2392.jpeg', 4.70, 98, 'FEATURED'],
        ['SKU-ALPHA-004', 101, 1, 'Alpha Tech Mechanical Gaming Keyboard RGB Backlit', 'Tactile blue switch mechanical gaming keyboard with custom RGB backlighting.', 2799.00, 4499.00, 38, 35, 'uploads/product_photos/file_1786088272_2660.jpeg', 4.90, 210, 'BESTSELLER'],
        ['SKU-ALPHA-005', 101, 1, 'Alpha Tech Studio Earbuds Quad Mic Noise Cancellation', 'True wireless earbuds engineered by Alpha Tech offering low latency gaming mode.', 2299.00, 3999.00, 43, 55, 'uploads/product_photos/file_1786629168_6088.jpg', 4.60, 76, 'REGULAR'],
        ['SKU-ALPHA-006', 101, 1, 'Alpha Tech 4K Ultra HD Smart TV Streaming Stick', 'Powerful 4K streaming stick with voice remote, Dolby Vision & Atmos support.', 3199.00, 5499.00, 42, 40, 'uploads/product_photos/file_1786629423_6069.jpg', 4.80, 115, 'FEATURED'],
        ['SKU-ALPHA-007', 101, 1, 'Alpha Tech High-Precision Ergonomic Wireless Mouse', '2.4GHz wireless mouse with adjustable DPI, silent click switches.', 999.00, 1799.00, 44, 100, 'uploads/product_photos/file_1786629489_3279.jpg', 4.70, 150, 'REGULAR'],

        ['SKU-FASHION-001', 102, 2, 'FashionXFit Performance Athletic Activewear Hoodie', 'Premium moisture-wicking fleece hoodie by FashionXFit.', 1499.00, 2999.00, 50, 70, 'uploads/product_photos/file_1786628293_8588.jpg', 4.90, 230, 'BESTSELLER'],
        ['SKU-FASHION-002', 102, 2, 'FashionXFit Breathable Flex Gym Joggers', '4-way stretch breathable training joggers with deep zippered pockets.', 1199.00, 2299.00, 48, 90, 'uploads/product_photos/file_1786628339_7720.jpg', 4.80, 165, 'FEATURED'],
        ['SKU-FASHION-003', 102, 2, 'FashionXFit Seamless Compression Sports T-Shirt', 'Body-mapping seamless compression top engineered by FashionXFit.', 899.00, 1599.00, 44, 110, 'uploads/product_photos/file_1786628693_2946.jpg', 4.70, 140, 'REGULAR'],
        ['SKU-FASHION-004', 102, 2, 'FashionXFit Lightweight Pro Cushion Running Shoes', 'High-impact cushioning athletic running shoes with breathable mesh upper.', 2499.00, 4999.00, 50, 55, 'uploads/product_photos/file_1786628723_6405.jpg', 4.90, 310, 'BESTSELLER'],
        ['SKU-FASHION-005', 102, 2, 'FashionXFit Premium Cotton Slim Fit Polo Shirt', '100% Pima cotton pique polo shirt with reinforced collar.', 999.00, 1899.00, 47, 80, 'uploads/product_photos/file_1786628746_5210.jpg', 4.75, 125, 'FEATURED'],
        ['SKU-FASHION-006', 102, 2, 'FashionXFit All-Weather Windbreaker Track Jacket', 'Water-repellent windbreaker jacket featuring a foldable hood.', 1799.00, 3499.00, 49, 65, 'uploads/product_photos/file_1786628775_9057.jpg', 4.80, 95, 'FEATURED'],
        ['SKU-FASHION-007', 102, 2, 'FashionXFit Urban Flex Stretch Denim Jeans', 'Flexible stretch denim jeans with modern slim fit cut.', 1699.00, 3199.00, 47, 75, 'uploads/product_photos/file_1786628799_1419.jpg', 4.70, 180, 'REGULAR']
    ];

    $stmt = $sqlitePdo->prepare("INSERT OR REPLACE INTO products 
        (sku, vendor_id, category_id, title, description, price, original_price, discount, stock, image_url, rating, rating_count, spotlight, status)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'ACTIVE')");

    foreach ($products as $p) {
        $stmt->execute($p);
    }
    echo "SQLite seed completed successfully!\n";
}
