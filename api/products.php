<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';

$db = getDBConnection();
$stmt = $db->query("SELECT p.*, c.name AS category_name, v.store_name FROM products p LEFT JOIN categories c ON p.category_id = c.id LEFT JOIN vendors v ON p.vendor_id = v.id WHERE p.status = 'ACTIVE'");
$products = $stmt->fetchAll();

echo json_encode([
    'status' => 'success',
    'count' => count($products),
    'data' => $products
]);
