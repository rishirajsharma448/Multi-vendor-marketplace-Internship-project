<?php
require_once __DIR__ . '/auth.php';

$user = getCurrentUser();
$role = $user['role'] ?? 'customer';
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<div class="sidebar">
  <div class="mb-4 text-center pb-3 border-bottom">
    <a href="<?= APP_URL ?>/<?= $role ?>/profile.php" class="text-decoration-none">
      <img src="<?= APP_URL ?>/<?= sanitize($user['avatar'] ?? 'assets/images/default-avatar.png') ?>" class="rounded-circle border mb-2 shadow-sm" width="64" height="64" style="object-fit: cover;" onerror="this.src='https://ui-avatars.com/api/?name=<?= urlencode($user['name'] ?? 'User') ?>&background=2563EB&color=fff'">
      <h6 class="fw-bold text-dark mb-0"><?= sanitize($user['name'] ?? 'User') ?></h6>
    </a>
    <span class="badge bg-primary text-uppercase px-2 py-1 mt-1" style="font-size: 0.65rem;"><?= $role ?> Portal</span>
  </div>

  <nav class="nav flex-column gap-1">
    <?php if ($role === 'admin'): ?>
      <a class="sidebar-link <?= $currentPage === 'dashboard.php' ? 'active' : '' ?>" href="<?= APP_URL ?>/admin/dashboard.php">
        <i class="bi bi-grid-1x2-fill"></i> Overview Dashboard
      </a>
      <a class="sidebar-link <?= $currentPage === 'vendors.php' ? 'active' : '' ?>" href="<?= APP_URL ?>/admin/vendors.php">
        <i class="bi bi-shop-window"></i> Vendor Approvals
      </a>
      <a class="sidebar-link <?= $currentPage === 'customers.php' ? 'active' : '' ?>" href="<?= APP_URL ?>/admin/customers.php">
        <i class="bi bi-people-fill"></i> Manage Customers
      </a>
      <a class="sidebar-link <?= $currentPage === 'products.php' ? 'active' : '' ?>" href="<?= APP_URL ?>/admin/products.php">
        <i class="bi bi-box-seam-fill"></i> Manage Products
      </a>
      <a class="sidebar-link <?= $currentPage === 'categories.php' ? 'active' : '' ?>" href="<?= APP_URL ?>/admin/categories.php">
        <i class="bi bi-tags-fill"></i> Categories & Brands
      </a>
      <a class="sidebar-link <?= $currentPage === 'orders.php' ? 'active' : '' ?>" href="<?= APP_URL ?>/admin/orders.php">
        <i class="bi bi-receipt"></i> Orders & Logistics
      </a>
      <a class="sidebar-link <?= $currentPage === 'profile.php' ? 'active' : '' ?>" href="<?= APP_URL ?>/admin/profile.php">
        <i class="bi bi-person-gear"></i> Account Profile
      </a>
      <a class="sidebar-link <?= $currentPage === 'settings.php' ? 'active' : '' ?>" href="<?= APP_URL ?>/admin/settings.php">
        <i class="bi bi-sliders"></i> Platform Settings
      </a>

    <?php elseif ($role === 'vendor'): ?>
      <a class="sidebar-link <?= $currentPage === 'dashboard.php' ? 'active' : '' ?>" href="<?= APP_URL ?>/vendor/dashboard.php">
        <i class="bi bi-speedometer2"></i> Vendor Dashboard
      </a>
      <a class="sidebar-link <?= $currentPage === 'add-product.php' ? 'active' : '' ?>" href="<?= APP_URL ?>/vendor/add-product.php">
        <i class="bi bi-plus-circle-fill"></i> Add New Product
      </a>
      <a class="sidebar-link <?= $currentPage === 'products.php' ? 'active' : '' ?>" href="<?= APP_URL ?>/vendor/products.php">
        <i class="bi bi-boxes"></i> My Product Inventory
      </a>
      <a class="sidebar-link <?= $currentPage === 'orders.php' ? 'active' : '' ?>" href="<?= APP_URL ?>/vendor/orders.php">
        <i class="bi bi-bag-check-fill"></i> Store Orders
      </a>
      <a class="sidebar-link <?= $currentPage === 'reviews.php' ? 'active' : '' ?>" href="<?= APP_URL ?>/vendor/reviews.php">
        <i class="bi bi-star-fill"></i> Customer Reviews
      </a>
      <a class="sidebar-link <?= $currentPage === 'shop-settings.php' ? 'active' : '' ?>" href="<?= APP_URL ?>/vendor/shop-settings.php">
        <i class="bi bi-shop"></i> Store Profile
      </a>
      <a class="sidebar-link <?= $currentPage === 'profile.php' ? 'active' : '' ?>" href="<?= APP_URL ?>/vendor/profile.php">
        <i class="bi bi-person-gear"></i> Account Profile
      </a>

    <?php else: ?>
      <a class="sidebar-link <?= $currentPage === 'dashboard.php' ? 'active' : '' ?>" href="<?= APP_URL ?>/customer/dashboard.php">
        <i class="bi bi-person-badge-fill"></i> My Dashboard
      </a>
      <a class="sidebar-link <?= $currentPage === 'orders.php' ? 'active' : '' ?>" href="<?= APP_URL ?>/customer/orders.php">
        <i class="bi bi-box2-heart-fill"></i> My Orders
      </a>
      <a class="sidebar-link <?= $currentPage === 'wishlist.php' ? 'active' : '' ?>" href="<?= APP_URL ?>/customer/wishlist.php">
        <i class="bi bi-heart-fill"></i> Saved Wishlist
      </a>
      <a class="sidebar-link <?= $currentPage === 'cart.php' ? 'active' : '' ?>" href="<?= APP_URL ?>/customer/cart.php">
        <i class="bi bi-cart-fill"></i> Shopping Cart
      </a>
      <a class="sidebar-link <?= $currentPage === 'profile.php' ? 'active' : '' ?>" href="<?= APP_URL ?>/customer/profile.php">
        <i class="bi bi-person-gear"></i> Account Profile
      </a>
    <?php endif; ?>

    <hr class="my-3 text-muted">
    <a class="sidebar-link text-danger" href="<?= APP_URL ?>/logout.php">
      <i class="bi bi-box-arrow-right"></i> Log Out
    </a>
  </nav>
</div>
