<?php
// Vyapar Setu Authentication & Authorization Middleware

require_once __DIR__ . '/session.php';
require_once __DIR__ . '/db.php';

function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function getCurrentUser() {
    static $currentUser = null;
    if ($currentUser !== null) {
        return $currentUser;
    }
    if (!isLoggedIn()) {
        return null;
    }
    
    $db = getDBConnection();
    $stmt = $db->prepare("SELECT id, name, email, phone, role, avatar, status FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    
    if (!$user) {
        logoutUser();
        $currentUser = null;
        return null;
    }
    
    if ($user && $user['role'] === 'vendor') {
        $vStmt = $db->prepare("SELECT * FROM vendors WHERE user_id = ?");
        $vStmt->execute([$user['id']]);
        $user['vendor'] = $vStmt->fetch();
    } elseif ($user && $user['role'] === 'customer') {
        $cStmt = $db->prepare("SELECT * FROM customers WHERE user_id = ?");
        $cStmt->execute([$user['id']]);
        $user['customer'] = $cStmt->fetch();
        if (!$user['customer']) {
            // Auto create customer record if missing
            $cCode = 'CUST-' . rand(10000, 99999);
            $ins = $db->prepare("INSERT INTO customers (user_id, customer_code, full_name, email, phone) VALUES (?, ?, ?, ?, ?)");
            $ins->execute([$user['id'], $cCode, $user['name'], $user['email'], $user['phone'] ?? '']);
            $user['customer'] = [
                'user_id' => $user['id'],
                'customer_code' => $cCode,
                'full_name' => $user['name'],
                'email' => $user['email'],
                'phone' => $user['phone'] ?? '',
                'shipping_address' => '',
                'city' => '',
                'state' => '',
                'pincode' => ''
            ];
        }
    }
    
    $currentUser = $user;
    return $user;
}

function requireAuth() {
    if (!isLoggedIn() || getCurrentUser() === null) {
        setFlash('warning', 'Please login to access this page.');
        header('Location: ' . APP_URL . '/login.php');
        exit;
    }
}

function requireRole($role) {
    requireAuth();
    $user = getCurrentUser();
    if (!$user || $user['role'] !== $role) {
        setFlash('danger', 'Access denied. You do not have permission to access this area.');
        redirectUserByRole($user['role'] ?? 'customer');
    }
}

function redirectUserByRole($role) {
    switch ($role) {
        case 'admin':
            header('Location: ' . APP_URL . '/admin/dashboard.php');
            break;
        case 'vendor':
            header('Location: ' . APP_URL . '/vendor/dashboard.php');
            break;
        case 'customer':
        default:
            header('Location: ' . APP_URL . '/customer/dashboard.php');
            break;
    }
    exit;
}

function loginUser($user) {
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_name'] = $user['name'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_role'] = $user['role'];
    
    // Log activity
    $db = getDBConnection();
    $stmt = $db->prepare("INSERT INTO activity_logs (user_id, action_description, ip_address) VALUES (?, ?, ?)");
    $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    $stmt->execute([$user['id'], ucfirst($user['role']) . ' user logged in.', $ip]);
}

function logoutUser() {
    if (isLoggedIn()) {
        $db = getDBConnection();
        $uCheck = $db->prepare("SELECT id FROM users WHERE id = ?");
        $uCheck->execute([$_SESSION['user_id']]);
        $hasUser = $uCheck->fetch();
        
        $stmt = $db->prepare("INSERT INTO activity_logs (user_id, action_description, ip_address) VALUES (?, ?, ?)");
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $logUserId = $hasUser ? $_SESSION['user_id'] : null;
        $stmt->execute([$logUserId, 'User logged out (session cleared).', $ip]);
    }
    
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy();
}
