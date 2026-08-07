<?php
$pageTitle = "Help & Support - Vyapar Setu";
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/auth.php';
requireRole('customer');
?>
<?php include __DIR__ . '/../includes/navbar.php'; ?>
<div class="dashboard-wrapper">
  <?php include __DIR__ . '/../includes/sidebar.php'; ?>
  <div class="dashboard-content">
    <h3 class="fw-bold mb-4"><i class="bi bi-headset text-primary me-2"></i> Customer Help & Support</h3>
    <div class="card card-custom p-4 border-0 shadow-sm">
      <p class="text-muted">Need help with an order? Contact our support resolution desk anytime.</p>
      <a href="<?= APP_URL ?>/contact.php" class="btn btn-primary-custom max-w-200px">Contact Support</a>
    </div>
  </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
