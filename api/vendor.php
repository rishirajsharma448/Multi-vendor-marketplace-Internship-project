<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/db.php';

$db = getDB();
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? 'dashboard';

if ($method === 'GET') {
    if ($action === 'dashboard') {
        $vendorId = (int)($_GET['vendor_id'] ?? 1);

        // Fetch Store Info
        $vStmt = $db->prepare("SELECT * FROM vendors WHERE id = :id");
        $vStmt->execute([':id' => $vendorId]);
        $vendor = $vStmt->fetch();

        // Fetch Products
        $pStmt = $db->prepare("SELECT * FROM products WHERE vendor_id = :id ORDER BY id DESC");
        $pStmt->execute([':id' => $vendorId]);
        $products = $pStmt->fetchAll();

        // Fetch Orders
        $oStmt = $db->prepare("SELECT o.*, oi.product_title, oi.price as item_price, oi.quantity 
                               FROM orders o 
                               JOIN order_items oi ON o.id = oi.order_id 
                               WHERE oi.vendor_id = :id 
                               ORDER BY o.id DESC");
        $oStmt->execute([':id' => $vendorId]);
        $orders = $oStmt->fetchAll();

        // Calculate Revenue
        $revenue = 0;
        foreach ($orders as $o) {
            $revenue += ($o['item_price'] * $o['quantity']);
        }

        echo json_encode([
            'status' => 'success',
            'vendor' => $vendor,
            'stats' => [
                'revenue' => $revenue,
                'total_orders' => count($orders),
                'total_products' => count($products),
                'low_stock' => count(array_filter($products, fn($p) => $p['stock'] < 5))
            ],
            'products' => $products,
            'orders' => $orders
        ]);
        exit;
    }
}

if ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    if ($action === 'update_order') {
        $orderId = (int)($data['order_id'] ?? 0);
        $newStatus = $data['status'] ?? 'Processing';

        $stmt = $db->prepare("UPDATE orders SET status = :status WHERE id = :id");
        $stmt->execute([':status' => $newStatus, ':id' => $orderId]);

        $log = $db->prepare("INSERT INTO audit_logs (action_description) VALUES (:act)");
        $log->execute([':act' => "Order #$orderId shipment status updated to '$newStatus' by vendor."]);

        echo json_encode(['status' => 'success', 'message' => 'Order status updated successfully']);
        exit;
    }

    if ($action === 'register') {
        $storeName = $data['store_name'] ?? '';
        $ownerName = $data['owner_name'] ?? '';
        $email = $data['email'] ?? '';
        $gstin = $data['gstin'] ?? '';
        $category = $data['category'] ?? 'Handicrafts';
        $vCode = 'VND-' . rand(10000, 99999);

        $stmt = $db->prepare("INSERT INTO vendors (vendor_code, store_name, owner_name, email, gstin, category, status) 
                              VALUES (:vcode, :store, :owner, :email, :gst, :cat, 'PENDING')");
        $stmt->execute([
            ':vcode' => $vCode,
            ':store' => $storeName,
            ':owner' => $ownerName,
            ':email' => $email,
            ':gst' => $gstin,
            ':cat' => $category
        ]);

        $log = $db->prepare("INSERT INTO audit_logs (action_description) VALUES (:act)");
        $log->execute([':act' => "New SME vendor application submitted: '$storeName' (GSTIN: $gstin)."]);

        echo json_encode(['status' => 'success', 'message' => 'Vendor onboarding application submitted for admin verification.']);
        exit;
    }
}
