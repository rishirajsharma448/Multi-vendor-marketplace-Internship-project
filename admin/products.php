<?php
$pageTitle = "Manage Products - Vyapar Setu Admin";
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

requireRole('admin');
$db = getDBConnection();

// Action handling
if (isset($_GET['action'], $_GET['id'])) {
    $pId = (int)$_GET['id'];
    if ($_GET['action'] === 'delete') {
        $db->prepare("DELETE FROM products WHERE id = ?")->execute([$pId]);
        setFlash('success', 'Product deleted.');
    }
    header("Location: products.php");
    exit;
}

$products = $db->query("
    SELECT p.*, c.name AS category_name, v.store_name, pi.image_url 
    FROM products p 
    LEFT JOIN categories c ON p.category_id = c.id 
    LEFT JOIN vendors v ON p.vendor_id = v.id 
    LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_primary = 1
    ORDER BY p.id DESC
")->fetchAll();
?>

<?php include __DIR__ . '/../includes/navbar.php'; ?>

<div class="dashboard-wrapper">
  <?php include __DIR__ . '/../includes/sidebar.php'; ?>

  <div class="dashboard-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <div>
        <h3 class="fw-bold mb-1"><i class="bi bi-box-seam text-primary me-2"></i> All Platform Products</h3>
        <p class="text-muted small mb-0">Live catalog with photos and descriptions stored and synced in the database.</p>
      </div>
      <span class="badge bg-primary fs-6 px-3 py-2"><?= count($products) ?> Total Products</span>
    </div>

    <?php displayAlerts(); ?>

    <div class="card card-custom border-0 shadow-sm">
      <div class="table-responsive">
        <table class="table table-custom mb-0">
          <thead>
            <tr>
              <th>Photo</th>
              <th>SKU</th>
              <th>Product Title</th>
              <th>Category</th>
              <th>Seller</th>
              <th>Price</th>
              <th>Stock</th>
              <th>Status</th>
              <th class="text-end">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($products as $p): ?>
              <?php $img = !empty($p['image_url']) ? $p['image_url'] : 'assets/images/default-product.png'; ?>
              <tr>
                <td>
                  <img src="../<?= sanitize($img) ?>" alt="Photo" width="48" height="48" class="rounded border object-fit-cover shadow-sm" onerror="this.src='../assets/images/default-product.png'">
                </td>
                <td><code><?= sanitize($p['sku']) ?></code></td>
                <td>
                  <a href="../product.php?id=<?= $p['id'] ?>" target="_blank" class="fw-bold text-dark text-decoration-none">
                    <?= sanitize($p['title']) ?> <i class="bi bi-box-arrow-up-right small text-muted"></i>
                  </a>
                  <small class="text-muted d-block text-truncate max-w-300px"><?= sanitize(substr($p['description'], 0, 80)) ?>...</small>
                </td>
                <td><span class="badge bg-light text-dark border"><?= sanitize($p['category_name']) ?></span></td>
                <td class="text-success fw-bold"><?= sanitize($p['store_name'] ?? 'Vendor') ?></td>
                <td class="fw-bold"><?= formatCurrency($p['price']) ?></td>
                <td><?= (int)$p['stock'] ?></td>
                <td><span class="badge <?= getStatusBadgeClass($p['status']) ?>"><?= sanitize($p['status']) ?></span></td>
                <td class="text-end">
                  <a href="products.php?action=delete&id=<?= $p['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this product?')" title="Delete Product"><i class="bi bi-trash"></i></a>
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
