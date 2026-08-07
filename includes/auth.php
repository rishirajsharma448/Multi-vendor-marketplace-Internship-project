<?php
// Vyapar Setu Authentication & Authorization Middleware

require_once __DIR__ . '/session.php';
require_once __DIR__ . '/db.php';

function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    $db = getDBConnection();
    $stmt = $db->prepare("SELECT id, name, email, phone, role, avatar, status FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    
    if ($user && $user['role'] === 'vendor') {
        $vStmt = $db->prepare("SELECT * FROM vendors WHERE user_id = ?");
        $vStmt->execute([$user['id']]);
        $user['vendor'] = $vStmt->fetch();
    }
    
    return $user;
}

function requireAuth() {
    if (!isLoggedIn()) {
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
        $stmt = $db->prepare("INSERT INTO activity_logs (user_id, action_description, ip_address) VALUES (?, ?, ?)");
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $stmt->execute([$_SESSION['user_id'], 'User logged out.', $ip]);
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
