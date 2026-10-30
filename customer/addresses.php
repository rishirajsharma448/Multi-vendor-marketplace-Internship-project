<?php
$pageTitle = "My Shipping Addresses - Vyapar Setu";
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

requireRole('customer');
$user = getCurrentUser();
$db = getDBConnection();

$customer = $user['customer'] ?? null;
if (!$customer) {
    $cStmt = $db->prepare("SELECT * FROM customers WHERE user_id = ?");
    $cStmt->execute([$user['id']]);
    $customer = $cStmt->fetch();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_address'])) {
    $address = sanitize($_POST['shipping_address'] ?? '');
    $city = sanitize($_POST['city'] ?? '');
    $state = sanitize($_POST['state'] ?? '');
    $pincode = sanitize($_POST['pincode'] ?? '');
    $phone = sanitize($_POST['phone'] ?? $user['phone']);

    if (!empty($address) && !empty($city) && !empty($pincode)) {
        if ($customer) {
            $up = $db->prepare("UPDATE customers SET shipping_address = ?, city = ?, state = ?, pincode = ?, phone = ? WHERE user_id = ?");
            $up->execute([$address, $city, $state, $pincode, $phone, $user['id']]);
        } else {
            $code = 'CUST-' . rand(10000, 99999);
            $ins = $db->prepare("INSERT INTO customers (user_id, customer_code, full_name, email, phone, shipping_address, city, state, pincode) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $ins->execute([$user['id'], $code, $user['name'], $user['email'], $phone, $address, $city, $state, $pincode]);
        }
        setFlash('success', 'Shipping address updated successfully!');
        header("Location: addresses.php");
        exit;
    } else {
        setFlash('danger', 'Please provide a valid street address, city, and pincode.');
    }
}
?>
<?php include __DIR__ . '/../includes/navbar.php'; ?>

<div class="dashboard-wrapper">
  <?php include __DIR__ . '/../includes/sidebar.php'; ?>
  <div class="dashboard-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <div>
        <h3 class="fw-bold mb-1"><i class="bi bi-geo-alt-fill text-primary me-2"></i> Saved Shipping Address</h3>
        <p class="text-muted small mb-0">Separate customer profile and delivery address stored securely in database.</p>
      </div>
      <?php if (!empty($customer['customer_code'])): ?>
        <span class="badge bg-primary fs-6 px-3 py-2"><i class="bi bi-person-badge me-1"></i> <?= sanitize($customer['customer_code']) ?></span>
      <?php endif; ?>
    </div>

    <?php displayAlerts(); ?>

    <div class="row g-4">
      <!-- Address Preview Card -->
      <div class="col-lg-5">
        <div class="card card-custom p-4 border-0 shadow-sm h-100">
          <div class="d-flex align-items-center justify-content-between mb-3">
            <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-1">Primary Delivery Address</span>
            <i class="bi bi-truck fs-4 text-primary"></i>
          </div>
          <h5 class="fw-bold text-dark mb-1"><?= sanitize($customer['full_name'] ?? $user['name']) ?></h5>
          <p class="text-muted small mb-2"><i class="bi bi-telephone me-1"></i> <?= sanitize($customer['phone'] ?? $user['phone'] ?? 'Not set') ?></p>
          <hr class="my-2">
          <p class="text-secondary mb-0">
            <?php if (!empty($customer['shipping_address'])): ?>
              <?= nl2br(sanitize($customer['shipping_address'])) ?><br>
              <?= sanitize($customer['city'] ?? '') ?>, <?= sanitize($customer['state'] ?? '') ?> - <strong><?= sanitize($customer['pincode'] ?? '') ?></strong>
            <?php else: ?>
              <span class="text-warning"><i class="bi bi-exclamation-circle me-1"></i> No delivery address configured yet. Please fill the form on the right.</span>
            <?php endif; ?>
          </p>
        </div>
      </div>

      <!-- Edit / Update Address Form -->
      <div class="col-lg-7">
        <div class="card card-custom p-4 border-0 shadow-sm">
          <h5 class="fw-bold text-dark mb-3"><i class="bi bi-pencil-square text-primary me-2"></i> Update Delivery Address</h5>
          <form action="addresses.php" method="POST">
            <div class="mb-3">
              <label class="form-label small fw-bold">Street Address / Flat / Building</label>
              <textarea name="shipping_address" class="form-control" rows="2" required placeholder="House/Flat No., Building Name, Street..."><?= sanitize($customer['shipping_address'] ?? '') ?></textarea>
            </div>
            <div class="row g-3 mb-3">
              <div class="col-md-6">
                <label class="form-label small fw-bold">City / District</label>
                <input type="text" name="city" class="form-control" required value="<?= sanitize($customer['city'] ?? '') ?>" placeholder="e.g. Mumbai, Pune">
              </div>
              <div class="col-md-6">
                <label class="form-label small fw-bold">State</label>
                <input type="text" name="state" class="form-control" required value="<?= sanitize($customer['state'] ?? 'Maharashtra') ?>" placeholder="e.g. Maharashtra">
              </div>
            </div>
            <div class="row g-3 mb-4">
              <div class="col-md-6">
                <label class="form-label small fw-bold">Postal PIN Code</label>
                <input type="text" name="pincode" class="form-control" required value="<?= sanitize($customer['pincode'] ?? '') ?>" placeholder="6-digit PIN code">
              </div>
              <div class="col-md-6">
                <label class="form-label small fw-bold">Contact Phone Number</label>
                <input type="text" name="phone" class="form-control" value="<?= sanitize($customer['phone'] ?? $user['phone'] ?? '') ?>" placeholder="Contact Phone">
              </div>
            </div>
            <button type="submit" name="save_address" class="btn btn-primary-custom px-4">
              <i class="bi bi-check2-circle me-1"></i> Save Shipping Address to Database
            </button>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
