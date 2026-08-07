<?php
$pageTitle = "Coupons - Vyapar Setu Admin";
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/auth.php';
requireRole('admin');
?>
<?php include __DIR__ . '/../includes/navbar.php'; ?>
<div class="dashboard-wrapper">
  <?php include __DIR__ . '/../includes/sidebar.php'; ?>
  <div class="dashboard-content">
    <h3 class="fw-bold mb-4"><i class="bi bi-ticket-perforated text-primary me-2"></i> Promotional Coupons</h3>
    <div class="card card-custom p-4 border-0 shadow-sm">
      <h6 class="fw-bold mb-2">Active Discount Codes</h6>
      <span class="badge bg-primary fs-6 p-2 me-auto">WELCOME10 - 10% OFF</span>
    </div>
  </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
