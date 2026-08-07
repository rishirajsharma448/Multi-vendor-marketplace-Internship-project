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
    SELECT p.*, c.name AS category_name, v.store_name 
    FROM products p 
    LEFT JOIN categories c ON p.category_id = c.id 
    LEFT JOIN vendors v ON p.vendor_id = v.id 
    ORDER BY p.id DESC
")->fetchAll();
?>

<?php include __DIR__ . '/../includes/navbar.php'; ?>

<div class="dashboard-wrapper">
  <?php include __DIR__ . '/../includes/sidebar.php'; ?>

  <div class="dashboard-content">
    <h3 class="fw-bold mb-4"><i class="bi bi-box-seam text-primary me-2"></i> All Platform Products</h3>

    <?php displayAlerts(); ?>

    <div class="card card-custom border-0 shadow-sm">
      <div class="table-responsive">
        <table class="table table-custom mb-0">
          <thead>
            <tr>
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
              <tr>
                <td><code><?= sanitize($p['sku']) ?></code></td>
                <td class="fw-bold"><?= sanitize($p['title']) ?></td>
                <td><span class="badge bg-light text-dark border"><?= sanitize($p['category_name']) ?></span></td>
                <td class="text-success fw-bold"><?= sanitize($p['store_name'] ?? 'Vendor') ?></td>
                <td class="fw-bold"><?= formatCurrency($p['price']) ?></td>
                <td><?= (int)$p['stock'] ?></td>
                <td><span class="badge <?= getStatusBadgeClass($p['status']) ?>"><?= sanitize($p['status']) ?></span></td>
                <td class="text-end">
                  <a href="products.php?action=delete&id=<?= $p['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this product?')"><i class="bi bi-trash"></i></a>
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
