<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/validation.php';

$input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
$name = trim($input['name'] ?? '');
$email = strtolower(trim($input['email'] ?? ''));
$phone = trim($input['phone'] ?? '');
$password = trim($input['password'] ?? '');
$role = trim($input['role'] ?? 'customer');

// Vendor specific
$store_name = trim($input['store_name'] ?? '');
$category = trim($input['category'] ?? 'General');
$gstin = trim($input['gstin'] ?? '');

if (empty($name) || empty($email) || empty($password)) {
    echo json_encode(['status' => 'error', 'message' => 'Name, email, and password are required']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid email address format']);
    exit;
}

if (strlen($password) < 6) {
    echo json_encode(['status' => 'error', 'message' => 'Password must be at least 6 characters']);
    exit;
}

$db = getDBConnection();

$check = $db->prepare("SELECT id FROM users WHERE LOWER(email) = ?");
$check->execute([$email]);
if ($check->fetch()) {
    echo json_encode(['status' => 'error', 'message' => 'An account with this email already exists']);
    exit;
}

$db->beginTransaction();
try {
    $passHash = password_hash($password, PASSWORD_BCRYPT);
    $ins = $db->prepare("INSERT INTO users (name, email, phone, password_hash, role, status, email_verified) VALUES (?, ?, ?, ?, ?, 'ACTIVE', 1)");
    $ins->execute([$name, $email, $phone, $passHash, $role]);
    $userId = $db->lastInsertId();

    if ($role === 'vendor') {
        $vendorCode = 'VND-' . rand(10000, 99999);
        $vIns = $db->prepare("INSERT INTO vendors (user_id, vendor_code, store_name, owner_name, email, phone, gstin, category, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'APPROVED')");
        $vIns->execute([$userId, $vendorCode, $store_name ?: ($name . ' Store'), $name, $email, $phone, $gstin, $category]);
    } else {
        $customerCode = 'CUST-' . rand(10000, 99999);
        $cIns = $db->prepare("INSERT INTO customers (user_id, customer_code, full_name, email, phone) VALUES (?, ?, ?, ?, ?)");
        $cIns->execute([$userId, $customerCode, $name, $email, $phone]);
    }

    $db->commit();

    $_SESSION['user_id'] = $userId;
    $_SESSION['role'] = $role;
    $_SESSION['user_name'] = $name;
    $_SESSION['user_email'] = $email;

    echo json_encode([
        'status' => 'success',
        'message' => 'Registration successful',
        'user_id' => $userId,
        'role' => $role
    ]);
} catch (Exception $e) {
    $db->rollBack();
    echo json_encode(['status' => 'error', 'message' => 'Registration error: ' . $e->getMessage()]);
}
