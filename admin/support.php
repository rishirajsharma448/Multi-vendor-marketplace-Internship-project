<?php
$pageTitle = "Support Tickets - Vyapar Setu Admin";
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/auth.php';
requireRole('admin');
?>
<?php include __DIR__ . '/../includes/navbar.php'; ?>
<div class="dashboard-wrapper">
  <?php include __DIR__ . '/../includes/sidebar.php'; ?>
  <div class="dashboard-content">
    <h3 class="fw-bold mb-4"><i class="bi bi-headset text-primary me-2"></i> Support & Resolution Desk</h3>
    <div class="card card-custom p-4 border-0 shadow-sm">
      <p class="text-muted">Manage customer and vendor support tickets.</p>
    </div>
  </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
