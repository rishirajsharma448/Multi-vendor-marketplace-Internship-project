<?php
$pageTitle = "Manage Customers - Vyapar Setu Admin";
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

requireRole('admin');
$db = getDBConnection();

$customers = $db->query("
    SELECT u.id, u.name, u.email, u.phone, u.status, u.created_at,
           c.customer_code, c.shipping_address, c.city, c.state, c.pincode,
           (SELECT COUNT(*) FROM orders o WHERE o.user_id = u.id) as orders_count,
           (SELECT COALESCE(SUM(total_amount), 0) FROM orders o WHERE o.user_id = u.id) as total_spent
    FROM users u 
    LEFT JOIN customers c ON u.id = c.user_id 
    WHERE u.role = 'customer' 
    ORDER BY u.id DESC
")->fetchAll();
?>

<?php include __DIR__ . '/../includes/navbar.php'; ?>

<div class="dashboard-wrapper">
  <?php include __DIR__ . '/../includes/sidebar.php'; ?>

  <div class="dashboard-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <div>
        <h3 class="fw-bold mb-1"><i class="bi bi-people text-primary me-2"></i> Registered Customers</h3>
        <p class="text-muted small mb-0">Separate customer profiles, delivery addresses, and purchasing statistics.</p>
      </div>
      <span class="badge bg-primary fs-6 px-3 py-2"><?= count($customers) ?> Active Customers</span>
    </div>

    <div class="card card-custom border-0 shadow-sm">
      <div class="table-responsive">
        <table class="table table-custom mb-0">
          <thead>
            <tr>
              <th>Customer Code</th>
              <th>Customer Name</th>
              <th>Email / Phone</th>
              <th>Saved Delivery Address</th>
              <th>Orders / Spent</th>
              <th>Joined Date</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($customers as $c): ?>
              <tr>
                <td class="fw-bold text-primary"><?= sanitize($c['customer_code'] ?? ('CUST-' . $c['id'])) ?></td>
                <td class="fw-bold"><?= sanitize($c['name']) ?></td>
                <td>
                  <div><?= sanitize($c['email']) ?></div>
                  <small class="text-muted"><?= sanitize($c['phone'] ?: 'N/A') ?></small>
                </td>
                <td>
                  <?php if (!empty($c['shipping_address'])): ?>
                    <small class="text-dark d-block"><?= sanitize($c['shipping_address']) ?></small>
                    <small class="text-muted"><?= sanitize($c['city'] ?? '') ?>, <?= sanitize($c['state'] ?? '') ?> - <?= sanitize($c['pincode'] ?? '') ?></small>
                  <?php else: ?>
                    <small class="text-muted fst-italic">No address saved yet</small>
                  <?php endif; ?>
                </td>
                <td>
                  <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle"><?= (int)$c['orders_count'] ?> Orders</span>
                  <div class="small fw-bold text-success mt-1">₹<?= number_format((float)$c['total_spent'], 2) ?></div>
                </td>
                <td><?= date('d M Y', strtotime($c['created_at'])) ?></td>
                <td><span class="badge bg-success"><?= sanitize($c['status']) ?></span></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
