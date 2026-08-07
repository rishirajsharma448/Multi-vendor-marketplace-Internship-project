<?php
$pageTitle = "Store Inventory - Vyapar Setu Vendor";
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

requireRole('vendor');
$user = getCurrentUser();
$vendor = $user['vendor'] ?? null;
$vendor_id = $vendor['id'] ?? 1;
$db = getDBConnection();

$stmt = $db->prepare("SELECT id, sku, title, price, stock FROM products WHERE vendor_id = ? ORDER BY stock ASC");
$stmt->execute([$vendor_id]);
$products = $stmt->fetchAll();
?>

<?php include __DIR__ . '/../includes/navbar.php'; ?>

<div class="dashboard-wrapper">
  <?php include __DIR__ . '/../includes/sidebar.php'; ?>

  <div class="dashboard-content">
    <h3 class="fw-bold mb-4"><i class="bi bi-box-seam text-primary me-2"></i> Inventory Tracking</h3>

    <div class="card card-custom border-0 shadow-sm">
      <div class="table-responsive">
        <table class="table table-custom mb-0">
          <thead>
            <tr>
              <th>SKU</th>
              <th>Product Name</th>
              <th>Price</th>
              <th>Current Stock</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($products as $p): ?>
              <tr>
                <td><code><?= sanitize($p['sku']) ?></code></td>
                <td class="fw-bold"><?= sanitize($p['title']) ?></td>
                <td><?= formatCurrency($p['price']) ?></td>
                <td class="fw-bold"><?= (int)$p['stock'] ?> units</td>
                <td>
                  <?php if ($p['stock'] > 10): ?>
                    <span class="badge bg-success">In Stock</span>
                  <?php else: ?>
                    <span class="badge bg-warning text-dark">Low Stock Alert</span>
                  <?php endif; ?>
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
