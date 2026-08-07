<?php
$pageTitle = "Store Settings - Vyapar Setu";
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

requireRole('vendor');
$user = getCurrentUser();
$vendor = $user['vendor'] ?? null;
$vendor_id = $vendor['id'] ?? 1;
$db = getDBConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $store_name = sanitize($_POST['store_name'] ?? '');
    $phone = sanitize($_POST['phone'] ?? '');
    $gstin = sanitize($_POST['gstin'] ?? '');
    $address = sanitize($_POST['address'] ?? '');

    $up = $db->prepare("UPDATE vendors SET store_name = ?, phone = ?, gstin = ?, address = ? WHERE id = ?");
    $up->execute([$store_name, $phone, $gstin, $address, $vendor_id]);
    setFlash('success', 'Store settings updated successfully.');
    header("Location: shop-settings.php");
    exit;
}
?>

<?php include __DIR__ . '/../includes/navbar.php'; ?>

<div class="dashboard-wrapper">
  <?php include __DIR__ . '/../includes/sidebar.php'; ?>

  <div class="dashboard-content">
    <h3 class="fw-bold mb-4"><i class="bi bi-shop text-primary me-2"></i> Storefront Profile & Branding</h3>

    <?php displayAlerts(); ?>

    <div class="row">
      <div class="col-lg-8">
        <div class="card card-custom p-4 border-0 shadow-sm">
          <form action="shop-settings.php" method="POST">
            <div class="mb-3">
              <label class="form-label fw-500">Store Name</label>
              <input type="text" name="store_name" class="form-control" value="<?= sanitize($vendor['store_name'] ?? '') ?>" required>
            </div>
            <div class="mb-3">
              <label class="form-label fw-500">Store Contact Phone</label>
              <input type="text" name="phone" class="form-control" value="<?= sanitize($vendor['phone'] ?? '') ?>" required>
            </div>
            <div class="mb-3">
              <label class="form-label fw-500">GSTIN / Tax ID</label>
              <input type="text" name="gstin" class="form-control" value="<?= sanitize($vendor['gstin'] ?? '') ?>">
            </div>
            <div class="mb-4">
              <label class="form-label fw-500">Store Address / Origin Location</label>
              <textarea name="address" class="form-control" rows="3"><?= sanitize($vendor['address'] ?? '') ?></textarea>
            </div>
            <button type="submit" class="btn btn-primary-custom px-4">Update Store Profile</button>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
