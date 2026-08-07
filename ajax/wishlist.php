<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

if (!isLoggedIn()) {
    echo json_encode([
        'status' => 'redirect',
        'url' => APP_URL . '/login.php',
        'message' => 'Please login to add to wishlist.'
    ]);
    exit;
}

$productId = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
$user_id = $_SESSION['user_id'];

if ($productId <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid product']);
    exit;
}

$db = getDBConnection();
$check = $db->prepare("SELECT id FROM wishlist WHERE user_id = ? AND product_id = ?");
$check->execute([$user_id, $productId]);
$exists = $check->fetch();

if ($exists) {
    $del = $db->prepare("DELETE FROM wishlist WHERE id = ?");
    $del->execute([$exists['id']]);
    echo json_encode([
        'status' => 'success',
        'added' => false,
        'message' => 'Removed from wishlist.',
        'wishlist_count' => getWishlistCount()
    ]);
} else {
    $ins = $db->prepare("INSERT INTO wishlist (user_id, product_id) VALUES (?, ?)");
    $ins->execute([$user_id, $productId]);
    echo json_encode([
        'status' => 'success',
        'added' => true,
        'message' => 'Saved to wishlist!',
        'wishlist_count' => getWishlistCount()
    ]);
}
