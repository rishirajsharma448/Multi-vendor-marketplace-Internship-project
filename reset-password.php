<?php
$pageTitle = "Reset Password - Vyapar Setu";
require_once __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/navbar.php';
?>
<main class="py-5 bg-light">
  <div class="container text-center">
    <div class="card card-custom p-5 mx-auto max-w-500px">
      <i class="bi bi-shield-lock text-primary display-3 mb-3"></i>
      <h4 class="fw-bold">Password Reset Link Validated</h4>
      <p class="text-muted">Enter a new password for your account below.</p>
      <form action="login.php" method="GET">
        <div class="mb-3 text-start">
          <label class="form-label fw-500">New Password</label>
          <input type="password" class="form-control" placeholder="••••••••" required>
        </div>
        <button type="submit" class="btn btn-primary-custom w-100">Update Password & Login</button>
      </form>
    </div>
  </div>
</main>
<?php include __DIR__ . '/includes/footer.php'; ?>
