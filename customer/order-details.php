<?php
$pageTitle = "Order Details - Vyapar Setu";
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

requireAuth();
$user = getCurrentUser();
$order_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$db = getDBConnection();

$stmt = $db->prepare("SELECT * FROM orders WHERE id = ? AND (user_id = ? OR ? = 'admin')");
$stmt->execute([$order_id, $user['id'], $user['role']]);
$order = $stmt->fetch();

if (!$order) {
    header("Location: orders.php");
    exit;
}

$itemsStmt = $db->prepare("
    SELECT oi.*, p.sku, v.store_name 
    FROM order_items oi 
    JOIN products p ON oi.product_id = p.id 
    LEFT JOIN vendors v ON oi.vendor_id = v.id 
    WHERE oi.order_id = ?
");
$itemsStmt->execute([$order_id]);
$items = $itemsStmt->fetchAll();
?>

<?php include __DIR__ . '/../includes/navbar.php'; ?>

<div class="dashboard-wrapper">
  <?php include __DIR__ . '/../includes/sidebar.php'; ?>

  <div class="dashboard-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <div>
        <h3 class="fw-bold mb-0">Order #<?= sanitize($order['order_number']) ?></h3>
        <small class="text-muted">Placed on <?= date('d M Y, h:i A', strtotime($order['created_at'])) ?></small>
      </div>
      <div>
        <button onclick="window.print()" class="btn btn-outline-secondary btn-sm"><i class="bi bi-printer me-1"></i> Print Invoice</button>
      </div>
    </div>

    <?php displayAlerts(); ?>

    <div class="row g-4 mb-4">
      <div class="col-md-4">
        <div class="card card-custom p-3 border-0 shadow-sm">
          <h6 class="fw-bold text-muted text-uppercase small">Delivery Address</h6>
          <p class="fw-bold mb-1"><?= sanitize($order['customer_name']) ?></p>
          <p class="small text-muted mb-0"><?= nl2br(sanitize($order['address'])) ?></p>
          <small class="text-muted d-block mt-2">Phone: <?= sanitize($order['phone']) ?></small>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card card-custom p-3 border-0 shadow-sm">
          <h6 class="fw-bold text-muted text-uppercase small">Payment Details</h6>
          <p class="fw-bold mb-1"><?= sanitize($order['payment_method']) ?></p>
          <span class="badge bg-success-subtle text-success border border-success me-auto mb-2"><?= sanitize($order['payment_status']) ?></span>
          <p class="small text-muted mb-0">Total Paid: <?= formatCurrency($order['total_amount']) ?></p>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card card-custom p-3 border-0 shadow-sm">
          <h6 class="fw-bold text-muted text-uppercase small">Fulfillment Status</h6>
          <span class="badge <?= getStatusBadgeClass($order['status']) ?> fs-6 p-2 me-auto mb-2"><?= sanitize($order['status']) ?></span>
          <p class="small text-muted mb-0">Dispatched via Vyapar Setu Direct Logistics</p>
        </div>
      </div>
    </div>

    <div class="card card-custom border-0 shadow-sm p-4">
      <h5 class="fw-bold mb-3">Purchased Items</h5>
      <div class="table-responsive">
        <table class="table align-middle">
          <thead>
            <tr class="text-uppercase text-muted small">
              <th>Product Details</th>
              <th>SKU</th>
              <th>Seller</th>
              <th>Unit Price</th>
              <th>Qty</th>
              <th class="text-end">Total</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($items as $it): ?>
              <tr>
                <td class="fw-bold"><?= sanitize($it['product_title']) ?></td>
                <td><code class="small"><?= sanitize($it['sku']) ?></code></td>
                <td><span class="badge bg-light text-dark border"><?= sanitize($it['store_name'] ?? 'Vendor') ?></span></td>
                <td><?= formatCurrency($it['price']) ?></td>
                <td><?= (int)$it['quantity'] ?></td>
                <td class="text-end fw-bold"><?= formatCurrency($it['price'] * $it['quantity']) ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
