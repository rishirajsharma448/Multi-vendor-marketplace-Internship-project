<?php
$pageTitle = "Customer Dashboard - Vyapar Setu";
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

requireRole('customer');
$user = getCurrentUser();
$db = getDBConnection();

// Fetch summary metrics
$orderCount = $db->prepare("SELECT COUNT(*) AS cnt FROM orders WHERE user_id = ?");
$orderCount->execute([$user['id']]);
$totalOrders = $orderCount->fetch()['cnt'] ?? 0;

$wishCount = $db->prepare("SELECT COUNT(*) AS cnt FROM wishlist WHERE user_id = ?");
$wishCount->execute([$user['id']]);
$totalWishlist = $wishCount->fetch()['cnt'] ?? 0;

// Fetch Recent Orders
$recStmt = $db->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY id DESC LIMIT 5");
$recStmt->execute([$user['id']]);
$recentOrders = $recStmt->fetchAll();
?>

<?php include __DIR__ . '/../includes/navbar.php'; ?>

<div class="dashboard-wrapper">
  <?php include __DIR__ . '/../includes/sidebar.php'; ?>

  <div class="dashboard-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <div>
        <h3 class="fw-bold mb-1">Hello, <?= sanitize($user['name']) ?>! 👋</h3>
        <p class="text-muted small mb-0">Welcome to your personal Vyapar Setu shopping hub.</p>
      </div>
      <a href="<?= APP_URL ?>/shop.php" class="btn btn-primary-custom btn-sm"><i class="bi bi-bag-plus me-1"></i> Browse Marketplace</a>
    </div>

    <?php displayAlerts(); ?>

    <!-- Summary Stat Cards -->
    <div class="row g-3 mb-4">
      <div class="col-md-3">
        <div class="stat-card">
          <div class="stat-icon-wrapper bg-primary-light text-primary">
            <i class="bi bi-box-seam"></i>
          </div>
          <div>
            <h4 class="fw-bold mb-0"><?= $totalOrders ?></h4>
            <small class="text-muted fw-500">Total Orders</small>
          </div>
        </div>
      </div>

      <div class="col-md-3">
        <div class="stat-card">
          <div class="stat-icon-wrapper bg-danger-subtle text-danger">
            <i class="bi bi-heart"></i>
          </div>
          <div>
            <h4 class="fw-bold mb-0"><?= $totalWishlist ?></h4>
            <small class="text-muted fw-500">Wishlist Items</small>
          </div>
        </div>
      </div>

      <div class="col-md-3">
        <div class="stat-card">
          <div class="stat-icon-wrapper bg-success-subtle text-success">
            <i class="bi bi-cart3"></i>
          </div>
          <div>
            <h4 class="fw-bold mb-0"><?= getCartCount() ?></h4>
            <small class="text-muted fw-500">Cart Items</small>
          </div>
        </div>
      </div>

      <div class="col-md-3">
        <div class="stat-card">
          <div class="stat-icon-wrapper bg-warning-subtle text-warning">
            <i class="bi bi-geo-alt"></i>
          </div>
          <div>
            <h4 class="fw-bold mb-0">1</h4>
            <small class="text-muted fw-500">Saved Address</small>
          </div>
        </div>
      </div>
    </div>

    <!-- Quick Actions -->
    <div class="card card-custom p-4 border-0 shadow-sm mb-4">
      <h6 class="fw-bold mb-3"><i class="bi bi-lightning-charge text-warning me-2"></i> Quick Actions</h6>
      <div class="d-flex flex-wrap gap-2">
        <a href="<?= APP_URL ?>/shop.php" class="btn btn-outline-primary btn-sm"><i class="bi bi-search me-1"></i> Browse Products</a>
        <a href="<?= APP_URL ?>/customer/orders.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-receipt me-1"></i> View Order History</a>
        <a href="<?= APP_URL ?>/customer/wishlist.php" class="btn btn-outline-danger btn-sm"><i class="bi bi-heart me-1"></i> Saved Wishlist</a>
        <a href="<?= APP_URL ?>/customer/profile.php" class="btn btn-outline-dark btn-sm"><i class="bi bi-person-gear me-1"></i> Edit Profile</a>
      </div>
    </div>

    <!-- Recent Orders Table -->
    <div class="card card-custom border-0 shadow-sm">
      <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
        <h5 class="fw-bold mb-0"><i class="bi bi-clock-history me-2 text-primary"></i> Recent Orders</h5>
        <a href="<?= APP_URL ?>/customer/orders.php" class="btn btn-sm btn-link text-primary fw-600 text-decoration-none">View All</a>
      </div>
      <div class="table-responsive">
        <table class="table table-custom mb-0">
          <thead>
            <tr>
              <th>Order ID</th>
              <th>Date</th>
              <th>Payment Method</th>
              <th>Total Amount</th>
              <th>Status</th>
              <th class="text-end">Action</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($recentOrders)): ?>
              <tr>
                <td colspan="6" class="text-center py-4 text-muted">No orders placed yet. Start shopping!</td>
              </tr>
            <?php else: ?>
              <?php foreach ($recentOrders as $ord): ?>
                <tr>
                  <td class="fw-bold text-primary"><?= sanitize($ord['order_number']) ?></td>
                  <td><?= date('d M Y', strtotime($ord['created_at'])) ?></td>
                  <td><?= sanitize($ord['payment_method']) ?></td>
                  <td class="fw-bold"><?= formatCurrency($ord['total_amount']) ?></td>
                  <td>
                    <span class="badge <?= getStatusBadgeClass($ord['status']) ?>">
                      <?= sanitize($ord['status']) ?>
                    </span>
                  </td>
                  <td class="text-end">
                    <a href="<?= APP_URL ?>/customer/order-details.php?id=<?= $ord['id'] ?>" class="btn btn-sm btn-outline-primary">Details</a>
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
