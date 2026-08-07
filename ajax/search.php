<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

$q = sanitize($_GET['q'] ?? '');
$db = getDBConnection();

$results = [];
if (!empty($q)) {
    $stmt = $db->prepare("SELECT id, title, price FROM products WHERE title LIKE ? AND status = 'ACTIVE' LIMIT 5");
    $stmt->execute(["%$q%"]);
    $results = $stmt->fetchAll();
}

echo json_encode(['status' => 'success', 'data' => $results]);
