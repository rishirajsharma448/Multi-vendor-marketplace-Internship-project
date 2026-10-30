<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';

$db = getDBConnection();
$stmt = $db->query("
    SELECT p.*, c.name AS category_name, v.store_name, pi.image_url,
           (SELECT COUNT(*) FROM reviews r WHERE r.product_id = p.id) as reviews_count
    FROM products p 
    LEFT JOIN categories c ON p.category_id = c.id 
    LEFT JOIN vendors v ON p.vendor_id = v.id 
    LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_primary = 1
    WHERE p.status = 'ACTIVE'
    ORDER BY p.id DESC
");
$products = $stmt->fetchAll();

echo json_encode([
    'status' => 'success',
    'count' => count($products),
    'data' => $products
]);
