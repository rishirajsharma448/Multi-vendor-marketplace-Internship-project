<?php
$pageTitle = "My Addresses - Vyapar Setu";
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/auth.php';
requireRole('customer');
$user = getCurrentUser();
?>
<?php include __DIR__ . '/../includes/navbar.php'; ?>
<div class="dashboard-wrapper">
  <?php include __DIR__ . '/../includes/sidebar.php'; ?>
  <div class="dashboard-content">
    <h3 class="fw-bold mb-4"><i class="bi bi-geo-alt text-primary me-2"></i> Saved Addresses</h3>
    <div class="card card-custom p-4 border-0 shadow-sm max-w-500px">
      <span class="badge bg-primary me-auto mb-2">Default Shipping</span>
      <h6 class="fw-bold mb-1"><?= sanitize($user['name']) ?></h6>
      <p class="text-muted small mb-0">Flat 402, G.V. Acharya Campus Road, Shelu, Maharashtra - 410101</p>
    </div>
  </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
