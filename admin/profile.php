<?php
$pageTitle = "Admin Profile & Governance - Vyapar Setu";
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

requireRole('admin');
$user = getCurrentUser();
$db = getDBConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'update_profile';

    if ($action === 'update_profile') {
        $name = sanitize($_POST['name'] ?? '');
        $phone = sanitize($_POST['phone'] ?? '');

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

            $stmt = $db->prepare("UPDATE users SET name = ?, phone = ?, avatar = ? WHERE id = ?");
            $stmt->execute([$name, $phone, $avatarPath, $user['id']]);
            setFlash('success', 'Admin profile information updated successfully!');
            header("Location: profile.php");
            exit;
        } else {
            setFlash('danger', 'Please enter a valid name.');
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
            setFlash('success', 'Admin account password updated successfully!');
            header("Location: profile.php");
            exit;
        }
    }
}

// Refresh user info
$user = getCurrentUser();
?>

<?php include __DIR__ . '/../includes/navbar.php'; ?>

<div class="dashboard-wrapper">
  <?php include __DIR__ . '/../includes/sidebar.php'; ?>

  <div class="dashboard-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <div>
        <h3 class="fw-bold mb-1"><i class="bi bi-shield-lock text-primary me-2"></i> System Administrator Profile</h3>
        <p class="text-muted small mb-0">Manage administrator details, profile photo, and platform security access.</p>
      </div>
      <span class="badge bg-danger text-uppercase px-3 py-2 fs-6"><i class="bi bi-shield-check me-1"></i> Super Admin</span>
    </div>

    <?php displayAlerts(); ?>

    <div class="row g-4">
      <!-- Admin Avatar Card -->
      <div class="col-lg-4">
        <div class="card card-custom p-4 border-0 shadow-sm text-center">
          <div class="position-relative d-inline-block mx-auto mb-3">
            <img src="<?= APP_URL ?>/<?= sanitize($user['avatar'] ?? 'assets/images/default-avatar.png') ?>" alt="Admin Photo" class="rounded-circle border shadow" width="130" height="130" style="object-fit: cover;" onerror="this.src='https://ui-avatars.com/api/?name=<?= urlencode($user['name']) ?>&background=DC2626&color=fff&size=128'">
          </div>
          <h5 class="fw-bold text-dark mb-1"><?= sanitize($user['name']) ?></h5>
          <p class="text-muted small mb-2"><?= sanitize($user['email']) ?></p>
          <span class="badge bg-danger px-3 py-1 mb-3">Platform Super Administrator</span>

          <hr class="my-3">
          <div class="text-start small text-muted">
            <div class="d-flex justify-content-between mb-2">
              <span>Admin Phone:</span>
              <strong class="text-dark"><?= sanitize($user['phone'] ?? 'Not set') ?></strong>
            </div>
            <div class="d-flex justify-content-between">
              <span>Role Privilege:</span>
              <strong class="text-danger">Full Master Access</strong>
            </div>
          </div>
        </div>
      </div>

      <!-- Admin Profile Forms -->
      <div class="col-lg-8">
        <!-- 1. Profile Details & Avatar Upload Form -->
        <div class="card card-custom p-4 border-0 shadow-sm mb-4">
          <h5 class="fw-bold mb-3"><i class="bi bi-person-lines-fill text-primary me-2"></i> Update Admin Information</h5>
          <form action="profile.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="action" value="update_profile">
            
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label fw-500">Administrator Full Name *</label>
                <input type="text" name="name" class="form-control" value="<?= sanitize($user['name']) ?>" required>
              </div>

              <div class="col-md-6">
                <label class="form-label fw-500">Email Address (Master Email)</label>
                <input type="email" class="form-control bg-light" value="<?= sanitize($user['email']) ?>" readonly>
              </div>

              <div class="col-md-6">
                <label class="form-label fw-500">Contact Phone Number</label>
                <input type="text" name="phone" class="form-control" placeholder="+91 99000 11223" value="<?= sanitize($user['phone'] ?? '') ?>">
              </div>

              <div class="col-md-6">
                <label class="form-label fw-500"><i class="bi bi-image text-primary me-1"></i> Upload Profile Photo (PC / Mobile)</label>
                <input type="file" name="avatar" accept="image/*" class="form-control">
                <small class="text-muted">Supports JPG, PNG, WEBP files up to 5MB.</small>
              </div>

              <div class="col-12 mt-4">
                <button type="submit" class="btn btn-primary-custom px-4 fw-600">
                  <i class="bi bi-check-circle me-1"></i> Save Admin Profile
                </button>
              </div>
            </div>
          </form>
        </div>

        <!-- 2. Security & Password Update Card -->
        <div class="card card-custom p-4 border-0 shadow-sm">
          <h5 class="fw-bold mb-3"><i class="bi bi-key text-primary me-2"></i> Admin Security & Password Change</h5>
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
                  <i class="bi bi-shield-lock me-1"></i> Update Admin Password
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
