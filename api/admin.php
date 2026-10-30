<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/db.php';

$db = getDB();
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? 'overview';

if ($method === 'GET') {
    if ($action === 'overview') {
        // Vendors
        $vStmt = $db->query("SELECT * FROM vendors ORDER BY id DESC");
        $vendors = $vStmt->fetchAll();

        // Products
        $pStmt = $db->query("SELECT p.*, v.store_name as vendorName FROM products p JOIN vendors v ON p.vendor_id = v.id ORDER BY p.id DESC");
        $products = $pStmt->fetchAll();

        // Orders
        $oStmt = $db->query("SELECT * FROM orders ORDER BY id DESC");
        $orders = $oStmt->fetchAll();

        // Activity Logs
        $aStmt = $db->query("SELECT * FROM activity_logs ORDER BY id DESC LIMIT 15");
        $logs = $aStmt->fetchAll();

        // GMV & Commission Calculation
        $totalGmv = 0;
        foreach ($orders as $o) {
            $totalGmv += (float)$o['total_amount'];
        }
        $commissionPool = $totalGmv * 0.10;

        echo json_encode([
            'status' => 'success',
            'stats' => [
                'total_gmv' => $totalGmv,
                'commission_pool' => $commissionPool,
                'active_vendors' => count(array_filter($vendors, fn($v) => $v['status'] === 'APPROVED')),
                'pending_vendors' => count(array_filter($vendors, fn($v) => $v['status'] === 'PENDING')),
                'total_orders' => count($orders)
            ],
            'vendors' => $vendors,
            'products' => $products,
            'logs' => $logs
        ]);
        exit;
    }
}

if ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    if ($action === 'approve_vendor') {
        $id = (int)($data['id'] ?? 0);
        $stmt = $db->prepare("UPDATE vendors SET status = 'APPROVED' WHERE id = :id");
        $stmt->execute([':id' => $id]);

        $log = $db->prepare("INSERT INTO activity_logs (action_description) VALUES (:act)");
        $log->execute([':act' => "Admin approved SME vendor store ID #$id."]);

        echo json_encode(['status' => 'success', 'message' => 'Vendor approved successfully']);
        exit;
    }

    if ($action === 'toggle_product') {
        $id = (int)($data['id'] ?? 0);
        $stmt = $db->prepare("UPDATE products SET status = CASE WHEN status = 'ACTIVE' THEN 'HIDDEN' ELSE 'ACTIVE' END WHERE id = :id");
        $stmt->execute([':id' => $id]);

        $log = $db->prepare("INSERT INTO activity_logs (action_description) VALUES (:act)");
        $log->execute([':act' => "Admin toggled visibility status for product ID #$id."]);

        echo json_encode(['status' => 'success', 'message' => 'Product status updated']);
        exit;
    }
}
