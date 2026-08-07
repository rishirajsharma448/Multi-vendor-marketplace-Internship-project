<?php
$pageTitle = "Reports - Vyapar Setu Admin";
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/auth.php';
requireRole('admin');
?>
<?php include __DIR__ . '/../includes/navbar.php'; ?>
<div class="dashboard-wrapper">
  <?php include __DIR__ . '/../includes/sidebar.php'; ?>
  <div class="dashboard-content">
    <h3 class="fw-bold mb-4"><i class="bi bi-file-earmark-bar-graph text-primary me-2"></i> Marketplace Financial & Sales Reports</h3>
    <div class="card card-custom p-4 border-0 shadow-sm">
      <p class="text-muted">Generate and export comprehensive sales reports, tax statements, and commission breakdown CSV files.</p>
      <button class="btn btn-primary-custom max-w-200px"><i class="bi bi-download me-1"></i> Export Sales CSV</button>
    </div>
  </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
