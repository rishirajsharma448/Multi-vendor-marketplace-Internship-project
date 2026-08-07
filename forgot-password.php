<?php
$pageTitle = "Forgot Password - Vyapar Setu";
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/mail.php';

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email'] ?? '');
    if (empty($email) || !validateEmail($email)) {
        $error = "Please enter a valid email address.";
    } else {
        $db = getDBConnection();
        $stmt = $db->prepare("SELECT id, name FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user) {
            $resetLink = APP_URL . "/reset-password.php?token=" . md5($email . time());
            sendEmail($email, "Password Reset Request - Vyapar Setu", "Hi " . $user['name'] . ",\n\nClick the link to reset your password:\n" . $resetLink);
        }
        $message = "If an account with that email exists, password reset instructions have been sent.";
    }
}
?>

<?php include __DIR__ . '/includes/navbar.php'; ?>

<main class="py-5 bg-light">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-md-6 col-lg-5">
        <div class="card card-custom p-4 shadow-sm border-0">
          <div class="text-center mb-4">
            <i class="bi bi-key text-primary display-4"></i>
            <h4 class="fw-bold mt-2">Reset Password</h4>
            <p class="text-muted small">Enter your email and we'll send password recovery instructions.</p>
          </div>

          <?php if (!empty($message)): ?>
            <div class="alert alert-success"><i class="bi bi-check-circle me-2"></i><?= sanitize($message) ?></div>
          <?php endif; ?>

          <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><i class="bi bi-exclamation-triangle me-2"></i><?= sanitize($error) ?></div>
          <?php endif; ?>

          <form action="forgot-password.php" method="POST">
            <div class="mb-3">
              <label for="email" class="form-label fw-500">Email Address</label>
              <input type="email" name="email" id="email" class="form-control" placeholder="name@example.com" required>
            </div>
            <button type="submit" class="btn btn-primary-custom w-100 py-2 fw-600">Send Recovery Link</button>
          </form>

          <div class="text-center small mt-4">
            <a href="login.php" class="text-decoration-none"><i class="bi bi-arrow-left me-1"></i> Back to Sign In</a>
          </div>
        </div>
      </div>
    </div>
  </div>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
