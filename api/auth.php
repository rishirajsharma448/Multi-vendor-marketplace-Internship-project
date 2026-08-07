<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../config/db.php';

$db = getDB();
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? 'check';

if ($action === 'check') {
    echo json_encode([
        'status' => 'success',
        'logged_in' => isset($_SESSION['role']),
        'user' => [
            'role' => $_SESSION['role'] ?? 'customer',
            'name' => $_SESSION['user_name'] ?? 'Guest Customer',
            'email' => $_SESSION['user_email'] ?? '',
            'vendor_id' => $_SESSION['vendor_id'] ?? null
        ]
    ]);
    exit;
}

if ($action === 'logout') {
    $_SESSION = array();
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy();

    // Check if client expects JSON or direct redirect
    $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
    if (strpos($accept, 'application/json') !== false || (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')) {
        echo json_encode(['status' => 'success', 'message' => 'Logged out successfully', 'redirect' => 'index.php']);
    } else {
        header('Location: ../index.php');
    }
    exit;
}

if ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $role = $data['role'] ?? 'customer';
    $email = strtolower(trim($data['email'] ?? ''));
    $password = trim($data['password'] ?? '');

    if (empty($email) || empty($password)) {
        echo json_encode(['status' => 'error', 'message' => 'Email and password are required']);
        exit;
    }

    if ($role === 'vendor') {
        // Find Vendor by Email in vendors table
        $stmt = $db->prepare("SELECT * FROM vendors WHERE LOWER(email) = :email");
        $stmt->execute([':email' => $email]);
        $vendor = $stmt->fetch();

        if (!$vendor) {
            // Check users table
            $uStmt = $db->prepare("SELECT u.*, v.id as vendor_id, v.store_name FROM users u LEFT JOIN vendors v ON u.id = v.user_id WHERE LOWER(u.email) = :email AND u.role = 'vendor'");
            $uStmt->execute([':email' => $email]);
            $u = $uStmt->fetch();
            if ($u) {
                $vendor = [
                    'id' => $u['vendor_id'] ?? $u['id'],
                    'store_name' => $u['store_name'] ?? $u['name'],
                    'email' => $u['email']
                ];
            }
        }

        if ($vendor) {
            $_SESSION['role'] = 'vendor';
            $_SESSION['vendor_id'] = $vendor['id'];
            $_SESSION['user_name'] = $vendor['store_name'];
            $_SESSION['user_email'] = $vendor['email'];

            echo json_encode([
                'status' => 'success',
                'redirect' => 'vendor_dashboard.php',
                'message' => 'Vendor authentication successful'
            ]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Invalid vendor credentials or email not registered.']);
        }
        exit;
    }

    if ($role === 'admin') {
        if ($email === 'admin@vyaparsetu.in' && $password === 'admin123') {
            $_SESSION['role'] = 'admin';
            $_SESSION['user_name'] = 'Platform Administrator';
            $_SESSION['user_email'] = 'admin@vyaparsetu.in';

            echo json_encode([
                'status' => 'success',
                'redirect' => 'admin_portal.php',
                'message' => 'Admin authentication successful'
            ]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Invalid admin credentials! (Demo Email: admin@vyaparsetu.in, Password: admin123)']);
        }
        exit;
    }

    if ($role === 'customer') {
        $_SESSION['role'] = 'customer';
        $_SESSION['user_name'] = explode('@', $email)[0];
        $_SESSION['user_email'] = $email;

        echo json_encode([
            'status' => 'success',
            'redirect' => 'index.php',
            'message' => 'Customer login successful'
        ]);
        exit;
    }
}
