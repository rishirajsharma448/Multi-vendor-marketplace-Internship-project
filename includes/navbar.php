<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/functions.php';

$currentUser = getCurrentUser();
$cartCount = getCartCount();
$wishlistCount = getWishlistCount();
?>
<nav class="navbar navbar-expand-lg navbar-vyaparsetu sticky-top py-2">
  <div class="container-fluid px-lg-4">
    <!-- Brand Logo -->
    <a class="navbar-brand d-flex align-items-center me-4" href="<?= APP_URL ?>/index.php" style="text-decoration: none;">
      <img src="<?= APP_URL ?>/assets/images/logo.jpg" alt="Vyapar Setu Logo" style="height: 55px; border-radius: var(--radius-sm); object-fit: contain;">
    </a>

    <!-- Mobile Toggle -->
    <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarContent">
      <!-- Search Form -->
      <form class="d-flex mx-auto my-2 my-lg-0 flex-grow-1 max-w-600px px-lg-4" action="<?= APP_URL ?>/search.php" method="GET">
        <div class="input-group">
          <input type="text" name="q" id="globalSearchInput" class="form-control border-end-0 bg-light" placeholder="Search products, brands, stores..." value="<?= sanitize($_GET['q'] ?? '') ?>">
          <button class="btn btn-primary-custom border-start-0 px-3" type="submit">
            <i class="bi bi-search"></i>
          </button>
        </div>
      </form>

      <!-- Nav Links -->
      <ul class="navbar-align-items-center navbar-nav ms-auto gap-lg-2">
        <li class="nav-item">
          <a class="nav-link fw-500" href="<?= APP_URL ?>/shop.php"><i class="bi bi-grid me-1"></i> Storefront</a>
        </li>

        <?php if ($currentUser): ?>
          <!-- Wishlist Badge -->
          <li class="nav-item">
            <a class="nav-link position-relative" href="<?= APP_URL ?>/customer/wishlist.php" title="Wishlist">
              <i class="bi bi-heart fs-5"></i>
              <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger wishlist-badge" style="font-size: 0.65rem;">
                <?= $wishlistCount ?>
              </span>
            </a>
          </li>

          <!-- Cart Badge -->
          <li class="nav-item">
            <a class="nav-link position-relative me-2" href="<?= APP_URL ?>/customer/cart.php" title="Shopping Cart">
              <i class="bi bi-cart3 fs-5"></i>
              <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-primary cart-badge" style="font-size: 0.65rem;">
                <?= $cartCount ?>
              </span>
            </a>
          </li>

          <!-- User Profile & Responsive Dropdown -->
          <li class="nav-item dropdown ms-lg-2">
            <div class="btn-group">
              <a href="<?= APP_URL ?>/<?= $currentUser['role'] ?>/profile.php" class="btn btn-light btn-sm d-flex align-items-center gap-2 fw-600 text-dark border shadow-sm px-3 py-1" title="Go to My Profile">
                <img src="<?= APP_URL ?>/<?= sanitize($currentUser['avatar'] ?? 'assets/images/default-avatar.png') ?>" alt="Avatar" class="rounded-circle" width="28" height="28" style="object-fit: cover;" onerror="this.src='https://ui-avatars.com/api/?name=<?= urlencode($currentUser['name']) ?>&background=2563EB&color=fff'">
                <span class="d-none d-sm-inline"><?= sanitize($currentUser['name']) ?></span>
                <span class="badge bg-primary text-uppercase" style="font-size: 0.65rem;"><?= $currentUser['role'] ?></span>
              </a>
              <button type="button" class="btn btn-light btn-sm dropdown-toggle dropdown-toggle-split border border-start-0 shadow-sm px-2" data-bs-toggle="dropdown" data-bs-display="static" aria-expanded="false" title="Account Quick Menu">
                <span class="visually-hidden">Toggle Menu</span>
              </button>
              <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2 p-2" style="min-width: 220px; z-index: 1050;">
                <li class="px-2 py-1 mb-1 bg-light rounded">
                  <span class="d-block fw-bold text-dark small"><i class="bi bi-person-circle text-primary me-1"></i> <?= sanitize($currentUser['name']) ?></span>
                  <small class="text-muted d-block text-truncate" style="font-size: 0.72rem;"><?= sanitize($currentUser['email']) ?></small>
                </li>
                <li><a class="dropdown-item rounded fw-500 py-2" href="<?= APP_URL ?>/<?= $currentUser['role'] ?>/profile.php"><i class="bi bi-person-gear text-primary me-2"></i> Account Profile & Settings</a></li>
                <li><hr class="dropdown-divider my-1"></li>
                <?php if ($currentUser['role'] === 'admin'): ?>
                  <li><a class="dropdown-item rounded fw-500 py-2" href="<?= APP_URL ?>/admin/dashboard.php"><i class="bi bi-speedometer2 text-primary me-2"></i> Admin Dashboard</a></li>
                <?php elseif ($currentUser['role'] === 'vendor'): ?>
                  <li><a class="dropdown-item rounded fw-500 py-2" href="<?= APP_URL ?>/vendor/dashboard.php"><i class="bi bi-speedometer2 text-primary me-2"></i> Vendor Dashboard</a></li>
                  <li><a class="dropdown-item rounded fw-500 py-2" href="<?= APP_URL ?>/vendor/products.php"><i class="bi bi-boxes me-2"></i> My Product Inventory</a></li>
                  <li><a class="dropdown-item rounded fw-500 py-2" href="<?= APP_URL ?>/vendor/orders.php"><i class="bi bi-receipt me-2"></i> Store Orders</a></li>
                <?php else: ?>
                  <li><a class="dropdown-item rounded fw-500 py-2" href="<?= APP_URL ?>/customer/dashboard.php"><i class="bi bi-grid-1x2-fill text-primary me-2"></i> Customer Dashboard</a></li>
                  <li><a class="dropdown-item rounded fw-500 py-2" href="<?= APP_URL ?>/customer/orders.php"><i class="bi bi-bag-check me-2"></i> My Orders</a></li>
                  <li><a class="dropdown-item rounded fw-500 py-2 text-dark" href="<?= APP_URL ?>/register.php?role=vendor"><i class="bi bi-shop text-warning me-2"></i> Become a Seller / Vendor</a></li>
                <?php endif; ?>
                <li><hr class="dropdown-divider my-1"></li>
                <li><a class="dropdown-item text-danger rounded fw-500 py-2" href="<?= APP_URL ?>/logout.php"><i class="bi bi-box-arrow-right me-2"></i> Sign Out / Logout</a></li>
              </ul>
            </div>
          </li>
        <?php else: ?>
          <li class="nav-item">
            <a class="nav-link fw-500 text-primary me-lg-2" href="<?= APP_URL ?>/register.php?role=vendor"><i class="bi bi-shop me-1"></i> Become a Seller</a>
          </li>
          <li class="nav-item ms-lg-2">
            <a class="btn btn-outline-primary btn-sm px-3 fw-500 me-2" href="<?= APP_URL ?>/login.php">Sign In</a>
          </li>
          <li class="nav-item">
            <a class="btn btn-primary-custom btn-sm px-3 fw-500" href="<?= APP_URL ?>/register.php">Register</a>
          </li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>
