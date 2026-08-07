<?php
$pageTitle = "Store Orders - Vyapar Setu";
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

requireRole('vendor');
$user = getCurrentUser();
$vendor = $user['vendor'] ?? null;
$vendor_id = $vendor['id'] ?? 1;
$db = getDBConnection();

// Status update handling
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['item_id'], $_POST['status'])) {
    $itemId = (int)$_POST['item_id'];
    $status = sanitize($_POST['status']);
    
    $up = $db->prepare("UPDATE order_items SET status = ? WHERE id = ? AND vendor_id = ?");
    $up->execute([$status, $itemId, $vendor_id]);
    setFlash('success', 'Order item status updated to ' . $status);
    header("Location: orders.php");
    exit;
}

$stmt = $db->prepare("
    SELECT oi.*, o.order_number, o.customer_name, o.phone, o.address, o.created_at AS order_date
    FROM order_items oi
    JOIN orders o ON oi.order_id = o.id
    WHERE oi.vendor_id = ?
    ORDER BY oi.id DESC
");
$stmt->execute([$vendor_id]);
$items = $stmt->fetchAll();
?>

<?php include __DIR__ . '/../includes/navbar.php'; ?>

<div class="dashboard-wrapper">
  <?php include __DIR__ . '/../includes/sidebar.php'; ?>

  <div class="dashboard-content">
    <h3 class="fw-bold mb-4"><i class="bi bi-receipt-cutoff text-primary me-2"></i> Manage Store Fulfillment Orders</h3>

    <?php displayAlerts(); ?>

    <div class="card card-custom border-0 shadow-sm">
      <div class="table-responsive">
        <table class="table table-custom mb-0">
          <thead>
            <tr>
              <th>Order Ref</th>
              <th>Customer</th>
              <th>Product Title</th>
              <th>Qty</th>
              <th>Amount</th>
              <th>Item Status</th>
              <th class="text-end">Update Status</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($items)): ?>
              <tr>
                <td colspan="7" class="text-center py-5 text-muted">No order items assigned to your store yet.</td>
              </tr>
            <?php else: ?>
              <?php foreach ($items as $it): ?>
                <tr>
                  <td class="fw-bold text-primary"><?= sanitize($it['order_number']) ?></td>
                  <td>
                    <div class="fw-bold"><?= sanitize($it['customer_name']) ?></div>
                    <small class="text-muted"><?= sanitize($it['phone']) ?></small>
                  </td>
                  <td><?= sanitize($it['product_title']) ?></td>
                  <td><?= (int)$it['quantity'] ?></td>
                  <td class="fw-bold"><?= formatCurrency($it['price'] * $it['quantity']) ?></td>
                  <td><span class="badge <?= getStatusBadgeClass($it['status']) ?>"><?= sanitize($it['status']) ?></span></td>
                  <td class="text-end">
                    <form action="orders.php" method="POST" class="d-inline-flex gap-1">
                      <input type="hidden" name="item_id" value="<?= $it['id'] ?>">
                      <select name="status" class="form-select form-select-sm" style="width: 120px;">
                        <option value="Pending" <?= $it['status'] === 'Pending' ? 'selected' : '' ?>>Pending</option>
                        <option value="Processing" <?= $it['status'] === 'Processing' ? 'selected' : '' ?>>Processing</option>
                        <option value="Shipped" <?= $it['status'] === 'Shipped' ? 'selected' : '' ?>>Shipped</option>
                        <option value="Delivered" <?= $it['status'] === 'Delivered' ? 'selected' : '' ?>>Delivered</option>
                      </select>
                      <button type="submit" class="btn btn-sm btn-primary-custom">Save</button>
                    </form>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
