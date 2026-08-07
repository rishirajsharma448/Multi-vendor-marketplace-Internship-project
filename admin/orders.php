<?php
$pageTitle = "Manage Orders - Vyapar Setu Admin";
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

requireRole('admin');
$db = getDBConnection();

$orders = $db->query("SELECT * FROM orders ORDER BY id DESC")->fetchAll();
?>

<?php include __DIR__ . '/../includes/navbar.php'; ?>

<div class="dashboard-wrapper">
  <?php include __DIR__ . '/../includes/sidebar.php'; ?>

  <div class="dashboard-content">
    <h3 class="fw-bold mb-4"><i class="bi bi-receipt text-primary me-2"></i> Marketplace Orders & Transactions</h3>

    <div class="card card-custom border-0 shadow-sm">
      <div class="table-responsive">
        <table class="table table-custom mb-0">
          <thead>
            <tr>
              <th>Order Ref</th>
              <th>Customer</th>
              <th>Date</th>
              <th>Payment Method</th>
              <th>Total Amount</th>
              <th>Order Status</th>
              <th class="text-end">Details</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($orders as $ord): ?>
              <tr>
                <td class="fw-bold text-primary"><?= sanitize($ord['order_number']) ?></td>
                <td>
                  <div class="fw-bold"><?= sanitize($ord['customer_name']) ?></div>
                  <small class="text-muted"><?= sanitize($ord['email']) ?></small>
                </td>
                <td><?= date('d M Y, h:i A', strtotime($ord['created_at'])) ?></td>
                <td><span class="badge bg-light text-dark border"><?= sanitize($ord['payment_method']) ?></span></td>
                <td class="fw-bold text-primary-custom"><?= formatCurrency($ord['total_amount']) ?></td>
                <td><span class="badge <?= getStatusBadgeClass($ord['status']) ?>"><?= sanitize($ord['status']) ?></span></td>
                <td class="text-end">
                  <a href="<?= APP_URL ?>/customer/order-details.php?id=<?= $ord['id'] ?>" class="btn btn-sm btn-outline-primary">View</a>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
