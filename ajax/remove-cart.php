<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';

if (isset($_POST['item_id']) && isLoggedIn()) {
    $itemId = (int)$_POST['item_id'];
    $db = getDBConnection();
    $stmt = $db->prepare("DELETE FROM cart_items WHERE id = ? AND cart_id IN (SELECT id FROM cart WHERE user_id = ?)");
    $stmt->execute([$itemId, $_SESSION['user_id']]);
    setFlash('info', 'Item removed from cart.');
}
header('Location: ' . APP_URL . '/customer/cart.php');
exit;
