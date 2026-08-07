<?php
$pageTitle = "My Wishlist - Vyapar Setu";
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

requireAuth();
$user = getCurrentUser();
$db = getDBConnection();

$stmt = $db->prepare("
    SELECT w.id AS wishlist_id, p.*, c.name AS category_name, v.store_name, pi.image_url 
    FROM wishlist w 
    JOIN products p ON w.product_id = p.id 
    LEFT JOIN categories c ON p.category_id = c.id 
    LEFT JOIN vendors v ON p.vendor_id = v.id 
    LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_primary = 1 
    WHERE w.user_id = ?
");
$stmt->execute([$user['id']]);
$wishlistItems = $stmt->fetchAll();
?>

<?php include __DIR__ . '/../includes/navbar.php'; ?>

<div class="dashboard-wrapper">
  <?php include __DIR__ . '/../includes/sidebar.php'; ?>

  <div class="dashboard-content">
    <h3 class="fw-bold mb-4"><i class="bi bi-heart-fill text-danger me-2"></i> Saved Wishlist Items</h3>

    <?php displayAlerts(); ?>

    <?php if (empty($wishlistItems)): ?>
      <div class="card card-custom p-5 text-center my-4">
        <i class="bi bi-heartbreak text-muted display-3 mb-3"></i>
        <h5>Your wishlist is empty</h5>
        <p class="text-muted">Save your favorite items from the store to buy them later.</p>
        <a href="<?= APP_URL ?>/shop.php" class="btn btn-primary-custom mx-auto max-w-200px">Explore Store</a>
      </div>
    <?php else: ?>
      <div class="row g-4">
        <?php foreach ($wishlistItems as $prod): ?>
          <?php $img = !empty($prod['image_url']) ? $prod['image_url'] : 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?auto=format&fit=crop&w=500&q=80'; ?>
          <div class="col-md-4">
            <div class="product-card">
              <div class="product-card-img-wrapper" onclick="window.location.href='../product.php?id=<?= $prod['id'] ?>'" style="cursor: pointer;">
                <img src="<?= sanitize($img) ?>" class="product-card-img">
              </div>
              <div class="p-3 d-flex flex-column flex-grow-1">
                <small class="text-muted fw-500"><?= sanitize($prod['category_name'] ?? 'General') ?></small>
                <h6 class="fw-bold mb-2 text-truncate"><?= sanitize($prod['title']) ?></h6>
                <div class="mt-auto d-flex align-items-center justify-content-between pt-2 border-top">
                  <span class="fs-5 fw-bold text-primary-custom"><?= formatCurrency($prod['price']) ?></span>
                  <div class="d-flex gap-1">
                    <button class="btn btn-sm btn-outline-danger btn-toggle-wishlist" data-product-id="<?= $prod['id'] ?>" title="Remove from Wishlist"><i class="bi bi-trash"></i></button>
                    <button class="btn btn-sm btn-primary-custom btn-add-cart" data-product-id="<?= $prod['id'] ?>"><i class="bi bi-cart-plus me-1"></i> Add to Cart</button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
