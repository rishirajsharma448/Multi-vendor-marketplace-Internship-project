<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/auth.php';

if (!isLoggedIn()) {
    setFlash('info', 'Please sign in to view your shopping cart.');
    header("Location: login.php");
    exit;
}

header("Location: customer/cart.php");
exit;
