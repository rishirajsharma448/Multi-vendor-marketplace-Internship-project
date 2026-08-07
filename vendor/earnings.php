<?php
$pageTitle = "Earnings - Vyapar Setu Vendor";
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/auth.php';
requireRole('vendor');
?>
<?php include __DIR__ . '/../includes/navbar.php'; ?>
<div class="dashboard-wrapper">
  <?php include __DIR__ . '/../includes/sidebar.php'; ?>
  <div class="dashboard-content">
    <h3 class="fw-bold mb-4"><i class="bi bi-wallet2 text-primary me-2"></i> Vendor Earnings & Net Payouts</h3>
    <div class="card card-custom p-4 border-0 shadow-sm">
      <h5 class="fw-bold mb-1">Available Balance: ₹45,200.00</h5>
      <p class="text-muted small">Commission Deducted: 10%</p>
      <a href="withdrawal.php" class="btn btn-primary-custom max-w-200px">Request Payout</a>
    </div>
  </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
