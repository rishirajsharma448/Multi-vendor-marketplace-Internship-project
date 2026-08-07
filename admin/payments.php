<?php
$pageTitle = "Payments & Settlements - Vyapar Setu Admin";
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

requireRole('admin');
$db = getDBConnection();
$payments = $db->query("SELECT p.*, o.order_number, o.customer_name FROM payments p JOIN orders o ON p.order_id = o.id ORDER BY p.id DESC")->fetchAll();
?>

<?php include __DIR__ . '/../includes/navbar.php'; ?>

<div class="dashboard-wrapper">
  <?php include __DIR__ . '/../includes/sidebar.php'; ?>

  <div class="dashboard-content">
    <h3 class="fw-bold mb-4"><i class="bi bi-credit-card text-primary me-2"></i> Transactions & Settlements</h3>

    <div class="card card-custom border-0 shadow-sm">
      <div class="table-responsive">
        <table class="table table-custom mb-0">
          <thead>
            <tr>
              <th>Txn ID</th>
              <th>Order Ref</th>
              <th>Customer</th>
              <th>Method</th>
              <th>Amount</th>
              <th>Status</th>
              <th>Date</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($payments as $pay): ?>
              <tr>
                <td><code><?= sanitize($pay['transaction_id']) ?></code></td>
                <td class="fw-bold text-primary"><?= sanitize($pay['order_number']) ?></td>
                <td><?= sanitize($pay['customer_name']) ?></td>
                <td><span class="badge bg-light text-dark border"><?= sanitize($pay['payment_method']) ?></span></td>
                <td class="fw-bold"><?= formatCurrency($pay['amount']) ?></td>
                <td><span class="badge <?= getStatusBadgeClass($pay['status']) ?>"><?= sanitize($pay['status']) ?></span></td>
                <td><?= date('d M Y, h:i A', strtotime($pay['created_at'])) ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
