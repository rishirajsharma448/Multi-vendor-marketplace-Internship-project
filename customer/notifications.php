<?php
$pageTitle = "Notifications - Vyapar Setu";
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/auth.php';
requireRole('customer');
?>
<?php include __DIR__ . '/../includes/navbar.php'; ?>
<div class="dashboard-wrapper">
  <?php include __DIR__ . '/../includes/sidebar.php'; ?>
  <div class="dashboard-content">
    <h3 class="fw-bold mb-4"><i class="bi bi-bell text-primary me-2"></i> Account Notifications</h3>
    <div class="card card-custom p-4 border-0 shadow-sm">
      <p class="text-muted">You are up to date on all order notifications.</p>
    </div>
  </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
