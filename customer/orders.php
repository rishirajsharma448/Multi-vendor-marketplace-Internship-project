<?php
$pageTitle = "My Orders - Vyapar Setu";
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

requireRole('customer');
$user = getCurrentUser();
$db = getDBConnection();

$stmt = $db->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY id DESC");
$stmt->execute([$user['id']]);
$orders = $stmt->fetchAll();
?>

<?php include __DIR__ . '/../includes/navbar.php'; ?>

<div class="dashboard-wrapper">
  <?php include __DIR__ . '/../includes/sidebar.php'; ?>

  <div class="dashboard-content">
    <h3 class="fw-bold mb-4"><i class="bi bi-box2-heart text-primary me-2"></i> Order History</h3>

    <div class="card card-custom border-0 shadow-sm">
      <div class="table-responsive">
        <table class="table table-custom mb-0">
          <thead>
            <tr>
              <th>Order Number</th>
              <th>Date</th>
              <th>Items Subtotal</th>
              <th>Payment</th>
              <th>Order Status</th>
              <th class="text-end">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($orders)): ?>
              <tr>
                <td colspan="6" class="text-center py-5 text-muted">You haven't placed any orders yet.</td>
              </tr>
            <?php else: ?>
              <?php foreach ($orders as $ord): ?>
                <tr>
                  <td class="fw-bold text-primary"><?= sanitize($ord['order_number']) ?></td>
                  <td><?= date('d M Y, h:i A', strtotime($ord['created_at'])) ?></td>
                  <td><?= formatCurrency($ord['subtotal']) ?></td>
                  <td><span class="badge bg-light text-dark border"><?= sanitize($ord['payment_method']) ?></span></td>
                  <td><span class="badge <?= getStatusBadgeClass($ord['status']) ?>"><?= sanitize($ord['status']) ?></span></td>
                  <td class="text-end">
                    <a href="order-details.php?id=<?= $ord['id'] ?>" class="btn btn-sm btn-primary-custom">View Receipt</a>
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
