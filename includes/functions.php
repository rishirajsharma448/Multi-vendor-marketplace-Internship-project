<?php
// Vyapar Setu Helper Utility Functions

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/session.php';
require_once __DIR__ . '/validation.php';
require_once __DIR__ . '/auth.php';

function sanitize($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

function formatCurrency($amount) {
    return '₹' . number_format((float)$amount, 2);
}

function slugify($text) {
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    $text = preg_replace('~[^-\w]+~', '', $text);
    $text = trim($text, '-');
    $text = preg_replace('~-+~', '-', $text);
    $text = strtolower($text);
    return empty($text) ? 'n-a' : $text;
}

function renderStars($rating) {
    $output = '';
    $full = floor($rating);
    $half = ($rating - $full) >= 0.5;
    
    for ($i = 1; $i <= 5; $i++) {
        if ($i <= $full) {
            $output .= '<i class="bi bi-star-fill text-warning"></i> ';
        } elseif ($i == $full + 1 && $half) {
            $output .= '<i class="bi bi-star-half text-warning"></i> ';
        } else {
            $output .= '<i class="bi bi-star text-muted opacity-50"></i> ';
        }
    }
    return $output;
}

function getCartCount() {
    if (!isset($_SESSION['user_id'])) {
        return 0;
    }
    $db = getDBConnection();
    $stmt = $db->prepare("
        SELECT COALESCE(SUM(ci.quantity), 0) AS cnt 
        FROM cart c 
        JOIN cart_items ci ON c.id = ci.cart_id 
        WHERE c.user_id = ?
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $res = $stmt->fetch();
    return (int)($res['cnt'] ?? 0);
}

function getWishlistCount() {
    if (!isset($_SESSION['user_id'])) {
        return 0;
    }
    $db = getDBConnection();
    $stmt = $db->prepare("SELECT COUNT(*) AS cnt FROM wishlist WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $res = $stmt->fetch();
    return (int)($res['cnt'] ?? 0);
}

function isProductInWishlist($productId) {
    if (!isset($_SESSION['user_id'])) {
        return false;
    }
    $db = getDBConnection();
    $stmt = $db->prepare("SELECT id FROM wishlist WHERE user_id = ? AND product_id = ?");
    $stmt->execute([$_SESSION['user_id'], $productId]);
    return $stmt->fetch() !== false;
}

function getStatusBadgeClass($status) {
    switch (strtoupper($status)) {
        case 'ACTIVE':
        case 'APPROVED':
        case 'PAID':
        case 'DELIVERED':
        case 'SUCCESS':
            return 'bg-success';
        case 'PENDING':
        case 'ORDER PLACED':
        case 'PROCESSING':
            return 'bg-warning text-dark';
        case 'SHIPPED':
        case 'OUT FOR DELIVERY':
            return 'bg-info text-dark';
        case 'REJECTED':
        case 'CANCELLED':
        case 'SUSPENDED':
        case 'FAILED':
            return 'bg-danger';
        default:
            return 'bg-secondary';
    }
}

function handleFileUpload($fileInputName, $targetDir = 'uploads/general/') {
    if (!isset($_FILES[$fileInputName]) || $_FILES[$fileInputName]['error'] !== UPLOAD_ERR_OK) {
        return null;
    }

    $file = $_FILES[$fileInputName];
    $allowedMimes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp', 'image/gif'];
    
    // Check MIME type if finfo is available
    if (function_exists('finfo_open')) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        if (!in_array($mimeType, $allowedMimes)) {
            throw new Exception("Invalid image format ($mimeType). Only JPG, PNG, WEBP, and GIF images are allowed.");
        }
    }

    // Size limit (5MB)
    if ($file['size'] > 5 * 1024 * 1024) {
        throw new Exception("Image file size exceeds maximum limit of 5MB.");
    }

    $basePath = defined('BASE_PATH') ? BASE_PATH : dirname(__DIR__);
    $uploadPath = $basePath . '/' . trim($targetDir, '/');
    if (!is_dir($uploadPath)) {
        mkdir($uploadPath, 0777, true);
    }

    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    if (empty($ext)) {
        $ext = 'jpg';
    }

    $filename = 'file_' . time() . '_' . rand(1000, 9999) . '.' . strtolower($ext);
    $targetFile = $uploadPath . '/' . $filename;

    if (move_uploaded_file($file['tmp_name'], $targetFile)) {
        return trim($targetDir, '/') . '/' . $filename;
    }

    throw new Exception("Failed to save uploaded image.");
}
