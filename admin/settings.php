<?php
$pageTitle = "Platform Settings - Vyapar Setu Admin";
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

requireRole('admin');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    setFlash('success', 'Global marketplace settings saved successfully.');
    header("Location: settings.php");
    exit;
}
?>

<?php include __DIR__ . '/../includes/navbar.php'; ?>

<div class="dashboard-wrapper">
  <?php include __DIR__ . '/../includes/sidebar.php'; ?>

  <div class="dashboard-content">
    <h3 class="fw-bold mb-4"><i class="bi bi-sliders text-primary me-2"></i> Global Marketplace Configuration</h3>

    <?php displayAlerts(); ?>

    <div class="row">
      <div class="col-lg-8">
        <div class="card card-custom p-4 border-0 shadow-sm">
          <form action="settings.php" method="POST">
            <div class="mb-3">
              <label class="form-label fw-500">Platform Name</label>
              <input type="text" class="form-control" value="Vyapar Setu - Multi Vendor Marketplace">
            </div>
            <div class="mb-3">
              <label class="form-label fw-500">Default Vendor Commission Rate (%)</label>
              <input type="text" class="form-control" value="10%">
            </div>
            <div class="mb-3">
              <label class="form-label fw-500">Standard Shipping Fee (₹)</label>
              <input type="number" class="form-control" value="99.00">
            </div>
            <div class="mb-4">
              <label class="form-label fw-500">Support Email</label>
              <input type="email" class="form-control" value="support@vyaparsetu.com">
            </div>
            <button type="submit" class="btn btn-primary-custom px-4">Save Platform Settings</button>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
