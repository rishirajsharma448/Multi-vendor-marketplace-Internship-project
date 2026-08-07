<?php
$pageTitle = "Vendor Products - Vyapar Setu";
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

requireRole('vendor');
$user = getCurrentUser();
$vendor = $user['vendor'] ?? null;
$vendor_id = $vendor['id'] ?? 1;
$db = getDBConnection();

// Delete handling
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $pId = (int)$_GET['id'];
    $del = $db->prepare("DELETE FROM products WHERE id = ? AND vendor_id = ?");
    $del->execute([$pId, $vendor_id]);
    setFlash('success', 'Product deleted successfully.');
    header("Location: products.php");
    exit;
}

$stmt = $db->prepare("
    SELECT 
        p.*, 
        c.name AS category_name,
        pi.image_url AS primary_image,
        COALESCE(SUM(oi.quantity), 0) AS total_units_sold,
        COALESCE(SUM(oi.price * oi.quantity), 0) AS total_revenue_generated
    FROM products p 
    LEFT JOIN categories c ON p.category_id = c.id 
    LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_primary = 1
    LEFT JOIN order_items oi ON p.id = oi.product_id
    WHERE p.vendor_id = ? 
    GROUP BY p.id
    ORDER BY p.id DESC
");
$stmt->execute([$vendor_id]);
$products = $stmt->fetchAll();
?>

<?php include __DIR__ . '/../includes/navbar.php'; ?>

<div class="dashboard-wrapper">
  <?php include __DIR__ . '/../includes/sidebar.php'; ?>

  <div class="dashboard-content">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
      <div>
        <h3 class="fw-bold mb-1"><i class="bi bi-boxes text-primary me-2"></i> Store Inventory & Performance</h3>
        <p class="text-muted small mb-0">Manage product listings, upload photos, and track sales revenue performance.</p>
      </div>
      <a href="add-product.php" class="btn btn-primary-custom"><i class="bi bi-plus-circle me-1"></i> Add Product (File/URL Upload)</a>
    </div>

    <?php displayAlerts(); ?>

    <div class="card card-custom border-0 shadow-sm">
      <div class="table-responsive">
        <table class="table table-custom align-middle mb-0">
          <thead>
            <tr>
              <th>Item Photo</th>
              <th>SKU / Title</th>
              <th>Category</th>
              <th>Price</th>
              <th>Units Sold</th>
              <th>Revenue Earned</th>
              <th>Stock Status</th>
              <th class="text-end">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($products)): ?>
              <tr>
                <td colspan="8" class="text-center py-5 text-muted">No products listed yet. Click "Add Product" to get started!</td>
              </tr>
            <?php else: ?>
              <?php foreach ($products as $prod): ?>
                <tr>
                  <td>
                    <img src="<?= (strpos($prod['primary_image'] ?? '', 'http') === 0) ? sanitize($prod['primary_image']) : APP_URL . '/' . sanitize($prod['primary_image'] ?? 'assets/images/default-product.png') ?>" alt="Product" class="rounded border" width="48" height="48" style="object-fit: cover;" onerror="this.src='https://images.unsplash.com/photo-1523275335684-37898b6baf30?auto=format&fit=crop&w=100&q=80'">
                  </td>
                  <td>
                    <code class="small d-block mb-1"><?= sanitize($prod['sku']) ?></code>
                    <span class="fw-bold text-dark d-block text-truncate" style="max-width: 200px;"><?= sanitize($prod['title']) ?></span>
                  </td>
                  <td><span class="badge bg-light text-dark border"><?= sanitize($prod['category_name']) ?></span></td>
                  <td class="fw-bold text-primary-custom"><?= formatCurrency($prod['price']) ?></td>
                  <td>
                    <strong class="text-dark"><?= (int)$prod['total_units_sold'] ?> units</strong>
                  </td>
                  <td>
                    <strong class="text-success"><?= formatCurrency($prod['total_revenue_generated']) ?></strong>
                  </td>
                  <td>
                    <?php if ($prod['stock'] > 10): ?>
                      <span class="badge bg-success-subtle text-success"><?= (int)$prod['stock'] ?> in stock</span>
                    <?php elseif ($prod['stock'] > 0): ?>
                      <span class="badge bg-warning-subtle text-warning"><?= (int)$prod['stock'] ?> low stock</span>
                    <?php else: ?>
                      <span class="badge bg-danger-subtle text-danger">Out of stock</span>
                    <?php endif; ?>
                  </td>
                  <td class="text-end">
                    <a href="edit-product.php?id=<?= $prod['id'] ?>" class="btn btn-sm btn-outline-primary me-1" title="Edit Listing & Upload Photo"><i class="bi bi-pencil"></i></a>
                    <a href="products.php?action=delete&id=<?= $prod['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to delete this product?')" title="Delete Product"><i class="bi bi-trash"></i></a>
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
