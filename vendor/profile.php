<?php
$pageTitle = "Vendor Profile & Account - Vyapar Setu";
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
    $action = $_POST['action'] ?? 'update_profile';

    if ($action === 'update_profile') {
        $name = sanitize($_POST['name'] ?? '');
        $phone = sanitize($_POST['phone'] ?? '');
        $store_name = sanitize($_POST['store_name'] ?? '');
        $gstin = sanitize($_POST['gstin'] ?? '');
        $address = sanitize($_POST['address'] ?? '');

        if (!empty($name)) {
            $avatarPath = $user['avatar'] ?? null;
            if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
                try {
                    $newAvatar = handleFileUpload('avatar', 'uploads/avatars/');
                    if ($newAvatar) {
                        $avatarPath = $newAvatar;
                    }
                } catch (Exception $ex) {
                    setFlash('danger', $ex->getMessage());
                    header("Location: profile.php");
                    exit;
                }
            }

            // Update user table
            $stmt = $db->prepare("UPDATE users SET name = ?, phone = ?, avatar = ? WHERE id = ?");
            $stmt->execute([$name, $phone, $avatarPath, $user['id']]);

            // Update vendor table
            if (!empty($store_name) && $vendor_id) {
                $vUp = $db->prepare("UPDATE vendors SET store_name = ?, owner_name = ?, phone = ?, gstin = ?, address = ? WHERE id = ?");
                $vUp->execute([$store_name, $name, $phone, $gstin, $address, $vendor_id]);
            }

            setFlash('success', 'Vendor profile and store details updated successfully!');
            header("Location: profile.php");
            exit;
        } else {
            setFlash('danger', 'Please enter a valid owner name.');
        }
    } elseif ($action === 'change_password') {
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        $stmt = $db->prepare("SELECT password_hash FROM users WHERE id = ?");
        $stmt->execute([$user['id']]);
        $userRow = $stmt->fetch();

        if (!password_verify($currentPassword, $userRow['password_hash']) && $currentPassword !== $userRow['password_hash']) {
            setFlash('danger', 'Current password entered is incorrect.');
        } elseif (strlen($newPassword) < 6) {
            setFlash('danger', 'New password must be at least 6 characters long.');
        } elseif ($newPassword !== $confirmPassword) {
            setFlash('danger', 'New password and confirmation password do not match.');
        } else {
            $newHash = password_hash($newPassword, PASSWORD_BCRYPT);
            $upStmt = $db->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
            $upStmt->execute([$newHash, $user['id']]);
            setFlash('success', 'Vendor account password updated successfully!');
            header("Location: profile.php");
            exit;
        }
    }
}

// Refresh user info
$user = getCurrentUser();
$vendor = $user['vendor'] ?? null;
?>

<?php include __DIR__ . '/../includes/navbar.php'; ?>

<div class="dashboard-wrapper">
  <?php include __DIR__ . '/../includes/sidebar.php'; ?>

  <div class="dashboard-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <div>
        <h3 class="fw-bold mb-1"><i class="bi bi-person-gear text-primary me-2"></i> Vendor Account Profile</h3>
        <p class="text-muted small mb-0">Manage owner credentials, profile photo, and security settings.</p>
      </div>
      <span class="badge bg-warning text-dark text-uppercase px-3 py-2 fs-6"><i class="bi bi-shop me-1"></i> Verified SME Seller</span>
    </div>

    <?php displayAlerts(); ?>

    <div class="row g-4">
      <!-- Profile Sidebar Card -->
      <div class="col-lg-4">
        <div class="card card-custom p-4 border-0 shadow-sm text-center">
          <div class="position-relative d-inline-block mx-auto mb-3">
            <img src="<?= APP_URL ?>/<?= sanitize($user['avatar'] ?? 'assets/images/default-avatar.png') ?>" alt="Owner Photo" class="rounded-circle border shadow" width="130" height="130" style="object-fit: cover;" onerror="this.src='https://ui-avatars.com/api/?name=<?= urlencode($user['name']) ?>&background=2563EB&color=fff&size=128'">
          </div>
          <h5 class="fw-bold text-dark mb-1"><?= sanitize($user['name']) ?></h5>
          <p class="text-muted small mb-2"><?= sanitize($vendor['store_name'] ?? 'Store Owner') ?></p>
          <span class="badge bg-primary px-3 py-1 mb-3">Code: <?= sanitize($vendor['vendor_code'] ?? 'VND-000') ?></span>

          <hr class="my-3">
          <div class="text-start small text-muted">
            <div class="d-flex justify-content-between mb-2">
              <span>Contact Email:</span>
              <strong class="text-dark"><?= sanitize($user['email']) ?></strong>
            </div>
            <div class="d-flex justify-content-between mb-2">
              <span>Phone:</span>
              <strong class="text-dark"><?= sanitize($user['phone'] ?? 'Not set') ?></strong>
            </div>
            <div class="d-flex justify-content-between mb-2">
              <span>GSTIN:</span>
              <strong class="text-dark"><?= sanitize($vendor['gstin'] ?? 'N/A') ?></strong>
            </div>
            <div class="d-flex justify-content-between">
              <span>Store Rating:</span>
              <strong class="text-warning">⭐ <?= number_format($vendor['rating'] ?? 5.0, 1) ?></strong>
            </div>
          </div>

          <div class="mt-4 pt-2 border-top">
            <a href="shop-settings.php" class="btn btn-outline-primary btn-sm w-100"><i class="bi bi-gear me-1"></i> Edit Storefront Branding</a>
          </div>
        </div>
      </div>

      <!-- Edit Forms -->
      <div class="col-lg-8">
        <!-- 1. Profile Details & Photo Upload Form -->
        <div class="card card-custom p-4 border-0 shadow-sm mb-4">
          <h5 class="fw-bold mb-3"><i class="bi bi-person-badge text-primary me-2"></i> Update Owner & Store Profile</h5>
          <form action="profile.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="action" value="update_profile">
            
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label fw-500">Owner Full Name *</label>
                <input type="text" name="name" class="form-control" value="<?= sanitize($user['name']) ?>" required>
              </div>

              <div class="col-md-6">
                <label class="form-label fw-500">Owner Email (Read Only)</label>
                <input type="email" class="form-control bg-light" value="<?= sanitize($user['email']) ?>" readonly>
              </div>

              <div class="col-md-6">
                <label class="form-label fw-500">Store Brand Name *</label>
                <input type="text" name="store_name" class="form-control" value="<?= sanitize($vendor['store_name'] ?? '') ?>" required>
              </div>

              <div class="col-md-6">
                <label class="form-label fw-500">Contact Phone Number</label>
                <input type="text" name="phone" class="form-control" placeholder="+91 98765 43210" value="<?= sanitize($user['phone'] ?? $vendor['phone'] ?? '') ?>">
              </div>

              <div class="col-md-6">
                <label class="form-label fw-500">GSTIN / Tax Registration ID</label>
                <input type="text" name="gstin" class="form-control" value="<?= sanitize($vendor['gstin'] ?? '') ?>">
              </div>

              <div class="col-md-6">
                <label class="form-label fw-500"><i class="bi bi-image text-primary me-1"></i> Upload Profile Photo (PC / Mobile)</label>
                <input type="file" name="avatar" accept="image/*" class="form-control">
                <small class="text-muted">Supports JPG, PNG, WEBP files up to 5MB.</small>
              </div>

              <div class="col-12">
                <label class="form-label fw-500">Store Location / Address</label>
                <textarea name="address" class="form-control" rows="2" placeholder="Street address, city, state..."><?= sanitize($vendor['address'] ?? '') ?></textarea>
              </div>

              <div class="col-12 mt-4">
                <button type="submit" class="btn btn-primary-custom px-4 fw-600">
                  <i class="bi bi-check-circle me-1"></i> Save Profile & Store Info
                </button>
              </div>
            </div>
          </form>
        </div>

        <!-- 2. Password Change Form -->
        <div class="card card-custom p-4 border-0 shadow-sm">
          <h5 class="fw-bold mb-3"><i class="bi bi-key text-primary me-2"></i> Security & Password Settings</h5>
          <form action="profile.php" method="POST">
            <input type="hidden" name="action" value="change_password">

            <div class="row g-3">
              <div class="col-md-4">
                <label class="form-label fw-500">Current Password *</label>
                <input type="password" name="current_password" class="form-control" placeholder="••••••••" required>
              </div>

              <div class="col-md-4">
                <label class="form-label fw-500">New Password *</label>
                <input type="password" name="new_password" class="form-control" placeholder="Min 6 characters" required>
              </div>

              <div class="col-md-4">
                <label class="form-label fw-500">Confirm New Password *</label>
                <input type="password" name="confirm_password" class="form-control" placeholder="Re-type new password" required>
              </div>

              <div class="col-12 mt-4">
                <button type="submit" class="btn btn-outline-danger px-4 fw-600">
                  <i class="bi bi-shield-lock me-1"></i> Update Password
                </button>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
