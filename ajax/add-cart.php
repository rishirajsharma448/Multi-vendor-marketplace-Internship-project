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
        'message' => 'Please login to add items to cart.'
    ]);
    exit;
}

$productId = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
$quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
$user_id = $_SESSION['user_id'];

if ($productId <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid product.']);
    exit;
}

$db = getDBConnection();
// Check product stock & price
$pStmt = $db->prepare("SELECT price, stock FROM products WHERE id = ?");
$pStmt->execute([$productId]);
$product = $pStmt->fetch();

if (!$product) {
    echo json_encode(['status' => 'error', 'message' => 'Product not found.']);
    exit;
}

// Find user's active cart or create one
$cStmt = $db->prepare("SELECT id FROM cart WHERE user_id = ?");
$cStmt->execute([$user_id]);
$cart = $cStmt->fetch();

if (!$cart) {
    $insCart = $db->prepare("INSERT INTO cart (user_id) VALUES (?)");
    $insCart->execute([$user_id]);
    $cartId = $db->lastInsertId();
} else {
    $cartId = $cart['id'];
}

// Check if item already in cart
$itemStmt = $db->prepare("SELECT id, quantity FROM cart_items WHERE cart_id = ? AND product_id = ?");
$itemStmt->execute([$cartId, $productId]);
$existingItem = $itemStmt->fetch();

if ($existingItem) {
    $newQty = $existingItem['quantity'] + $quantity;
    $upItem = $db->prepare("UPDATE cart_items SET quantity = ?, price = ? WHERE id = ?");
    $upItem->execute([$newQty, $product['price'], $existingItem['id']]);
} else {
    $insItem = $db->prepare("INSERT INTO cart_items (cart_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
    $insItem->execute([$cartId, $productId, $quantity, $product['price']]);
}

$cartCount = getCartCount();
echo json_encode([
    'status' => 'success',
    'message' => 'Product added to shopping cart!',
    'cart_count' => $cartCount
]);
