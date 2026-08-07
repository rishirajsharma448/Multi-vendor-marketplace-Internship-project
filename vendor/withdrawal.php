<?php
$pageTitle = "Withdrawal - Vyapar Setu Vendor";
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/auth.php';
requireRole('vendor');
?>
<?php include __DIR__ . '/../includes/navbar.php'; ?>
<div class="dashboard-wrapper">
  <?php include __DIR__ . '/../includes/sidebar.php'; ?>
  <div class="dashboard-content">
    <h3 class="fw-bold mb-4"><i class="bi bi-bank text-primary me-2"></i> Payout Withdrawal</h3>
    <div class="card card-custom p-4 border-0 shadow-sm max-w-500px">
      <form action="withdrawal.php" method="POST">
        <div class="mb-3">
          <label class="form-label fw-500">Amount to Withdraw (₹)</label>
          <input type="number" class="form-control" placeholder="10000.00" required>
        </div>
        <div class="mb-3">
          <label class="form-label fw-500">Bank Account / UPI ID</label>
          <input type="text" class="form-control" placeholder="vendor@upi" required>
        </div>
        <button type="submit" class="btn btn-primary-custom w-100">Submit Payout Request</button>
      </form>
    </div>
  </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
