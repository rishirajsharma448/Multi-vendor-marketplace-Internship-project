<?php
$pageTitle = "Processing Payment - Vyapar Setu";
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

requireAuth();
$user = getCurrentUser();
$db = getDBConnection();

$orderId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch Order
$stmt = $db->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
$stmt->execute([$orderId, $user['id']]);
$order = $stmt->fetch();

if (!$order) {
    header("Location: " . APP_URL . "/shop.php");
    exit;
}

// Fetch Order Items count
$itemStmt = $db->prepare("SELECT COUNT(*) as item_count FROM order_items WHERE order_id = ?");
$itemStmt->execute([$orderId]);
$itemData = $itemStmt->fetch();
$itemCount = $itemData['item_count'] ?? 0;

$paymentMethod = $order['payment_method'];
$paymentIcon = 'bi-cash-stack';
$paymentLabel = 'Cash on Delivery';
$paymentColor = '#10b981';

if ($paymentMethod === 'UPI') {
    $paymentIcon = 'bi-phone';
    $paymentLabel = 'UPI Payment';
    $paymentColor = '#6366f1';
} elseif ($paymentMethod === 'CARD') {
    $paymentIcon = 'bi-credit-card';
    $paymentLabel = 'Card Payment';
    $paymentColor = '#f59e0b';
}
?>

<?php include __DIR__ . '/includes/navbar.php'; ?>

<style>
  /* ─── Root Variables ─── */
  :root {
    --pp-primary: #4361ee;
    --pp-success: #10b981;
    --pp-accent: #6366f1;
    --pp-glow: rgba(67, 97, 238, 0.35);
  }

  /* ─── Page Background ─── */
  .payment-processing-page {
    min-height: 85vh;
    background: linear-gradient(160deg, #0f0c29 0%, #1a1a2e 35%, #16213e 70%, #0f0c29 100%);
    position: relative;
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
  }

  /* Animated Floating Particles */
  .payment-processing-page::before {
    content: '';
    position: absolute;
    top: -50%;
    left: -50%;
    width: 200%;
    height: 200%;
    background: radial-gradient(circle at 20% 50%, rgba(67, 97, 238, 0.08) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(99, 102, 241, 0.06) 0%, transparent 50%),
                radial-gradient(circle at 50% 80%, rgba(16, 185, 129, 0.05) 0%, transparent 50%);
    animation: bgDrift 20s ease-in-out infinite;
  }

  @keyframes bgDrift {
    0%, 100% { transform: translate(0, 0) rotate(0deg); }
    33% { transform: translate(30px, -30px) rotate(1deg); }
    66% { transform: translate(-20px, 20px) rotate(-1deg); }
  }

  /* Floating Orbs */
  .floating-orb {
    position: absolute;
    border-radius: 50%;
    filter: blur(60px);
    opacity: 0.3;
    animation: orbFloat 8s ease-in-out infinite;
  }

  .orb-1 {
    width: 300px; height: 300px;
    background: var(--pp-primary);
    top: 10%; left: 5%;
    animation-delay: 0s;
  }

  .orb-2 {
    width: 250px; height: 250px;
    background: var(--pp-accent);
    bottom: 10%; right: 10%;
    animation-delay: -3s;
  }

  .orb-3 {
    width: 200px; height: 200px;
    background: var(--pp-success);
    top: 50%; left: 60%;
    animation-delay: -6s;
  }

  @keyframes orbFloat {
    0%, 100% { transform: translateY(0) scale(1); }
    50% { transform: translateY(-40px) scale(1.1); }
  }

  /* ─── Main Card ─── */
  .processing-card {
    position: relative;
    z-index: 10;
    background: rgba(255, 255, 255, 0.03);
    backdrop-filter: blur(30px);
    -webkit-backdrop-filter: blur(30px);
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: 28px;
    padding: 3rem 2.5rem;
    max-width: 520px;
    width: 100%;
    box-shadow: 0 25px 60px rgba(0, 0, 0, 0.4),
                inset 0 1px 0 rgba(255, 255, 255, 0.06);
    animation: cardReveal 0.8s cubic-bezier(0.34, 1.56, 0.64, 1) forwards;
    opacity: 0;
  }

  @keyframes cardReveal {
    from { opacity: 0; transform: translateY(40px) scale(0.95); }
    to { opacity: 1; transform: translateY(0) scale(1); }
  }

  /* ─── Spinner Ring ─── */
  .spinner-container {
    position: relative;
    width: 120px;
    height: 120px;
    margin: 0 auto 2rem;
  }

  .spinner-ring {
    position: absolute;
    inset: 0;
    border-radius: 50%;
    border: 3px solid rgba(255, 255, 255, 0.06);
  }

  .spinner-ring-active {
    border: 3px solid transparent;
    border-top-color: var(--pp-primary);
    border-right-color: var(--pp-accent);
    animation: spinRing 1.2s linear infinite;
  }

  .spinner-ring-outer {
    position: absolute;
    inset: -8px;
    border-radius: 50%;
    border: 2px solid transparent;
    border-bottom-color: var(--pp-success);
    border-left-color: rgba(99, 102, 241, 0.5);
    animation: spinRing 2s linear infinite reverse;
  }

  @keyframes spinRing {
    to { transform: rotate(360deg); }
  }

  .spinner-icon {
    position: absolute;
    inset: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2.2rem;
    color: #fff;
    transition: all 0.5s cubic-bezier(0.34, 1.56, 0.64, 1);
  }

  .spinner-icon.icon-enter {
    animation: iconPop 0.5s cubic-bezier(0.34, 1.56, 0.64, 1) forwards;
  }

  @keyframes iconPop {
    0% { transform: scale(0) rotate(-45deg); opacity: 0; }
    100% { transform: scale(1) rotate(0deg); opacity: 1; }
  }

  /* Success state for spinner */
  .spinner-success .spinner-ring-active,
  .spinner-success .spinner-ring-outer {
    animation: none;
    border-color: transparent;
  }

  .spinner-success .spinner-ring {
    border-color: var(--pp-success);
    transition: border-color 0.5s ease;
  }

  .spinner-success .spinner-icon {
    color: var(--pp-success);
    animation: successBounce 0.6s cubic-bezier(0.34, 1.56, 0.64, 1);
  }

  @keyframes successBounce {
    0% { transform: scale(0); }
    50% { transform: scale(1.3); }
    100% { transform: scale(1); }
  }

  /* ─── Status Text ─── */
  .status-title {
    color: #fff;
    font-size: 1.5rem;
    font-weight: 700;
    text-align: center;
    margin-bottom: 0.5rem;
    min-height: 2rem;
    transition: all 0.3s ease;
  }

  .status-subtitle {
    color: rgba(255, 255, 255, 0.5);
    font-size: 0.9rem;
    text-align: center;
    margin-bottom: 2rem;
    min-height: 1.4rem;
  }

  /* ─── Progress Steps ─── */
  .progress-steps {
    display: flex;
    flex-direction: column;
    gap: 0;
    margin-bottom: 2rem;
  }

  .progress-step {
    display: flex;
    align-items: flex-start;
    gap: 1rem;
    padding: 0.75rem 0;
    opacity: 0.3;
    transition: all 0.5s ease;
    position: relative;
  }

  .progress-step.active {
    opacity: 1;
  }

  .progress-step.completed {
    opacity: 0.7;
  }

  .step-indicator {
    position: relative;
    display: flex;
    flex-direction: column;
    align-items: center;
    flex-shrink: 0;
  }

  .step-dot {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.06);
    border: 2px solid rgba(255, 255, 255, 0.1);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.85rem;
    color: rgba(255, 255, 255, 0.3);
    transition: all 0.5s cubic-bezier(0.34, 1.56, 0.64, 1);
    position: relative;
    z-index: 2;
  }

  .step-line {
    width: 2px;
    height: 24px;
    background: rgba(255, 255, 255, 0.06);
    transition: background 0.5s ease;
  }

  .progress-step.active .step-dot {
    background: rgba(67, 97, 238, 0.2);
    border-color: var(--pp-primary);
    color: var(--pp-primary);
    box-shadow: 0 0 20px rgba(67, 97, 238, 0.3);
    animation: dotPulse 1.5s ease-in-out infinite;
  }

  @keyframes dotPulse {
    0%, 100% { box-shadow: 0 0 15px rgba(67, 97, 238, 0.25); }
    50% { box-shadow: 0 0 25px rgba(67, 97, 238, 0.5); }
  }

  .progress-step.completed .step-dot {
    background: rgba(16, 185, 129, 0.2);
    border-color: var(--pp-success);
    color: var(--pp-success);
    box-shadow: none;
    animation: none;
  }

  .progress-step.completed .step-line {
    background: var(--pp-success);
  }

  .step-content {
    padding-top: 0.35rem;
  }

  .step-label {
    color: rgba(255, 255, 255, 0.4);
    font-size: 0.9rem;
    font-weight: 600;
    transition: color 0.3s ease;
  }

  .step-desc {
    color: rgba(255, 255, 255, 0.2);
    font-size: 0.78rem;
    margin-top: 2px;
  }

  .progress-step.active .step-label {
    color: #fff;
  }

  .progress-step.active .step-desc {
    color: rgba(255, 255, 255, 0.5);
  }

  .progress-step.completed .step-label {
    color: var(--pp-success);
  }

  /* ─── Amount Badge ─── */
  .amount-badge {
    background: rgba(255, 255, 255, 0.04);
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: 16px;
    padding: 1rem 1.25rem;
    margin-bottom: 1.5rem;
  }

  .amount-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
  }

  .amount-label {
    color: rgba(255, 255, 255, 0.5);
    font-size: 0.85rem;
  }

  .amount-value {
    color: #fff;
    font-size: 1.6rem;
    font-weight: 800;
    letter-spacing: -0.5px;
  }

  .payment-method-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    background: rgba(255, 255, 255, 0.06);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 20px;
    padding: 0.3rem 0.8rem;
    color: rgba(255, 255, 255, 0.7);
    font-size: 0.8rem;
    font-weight: 500;
    margin-top: 0.5rem;
  }

  .payment-method-badge i {
    font-size: 0.9rem;
  }

  /* ─── Bottom Bar ─── */
  .secure-badge {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    color: rgba(255, 255, 255, 0.25);
    font-size: 0.75rem;
    padding-top: 1rem;
    border-top: 1px solid rgba(255, 255, 255, 0.04);
  }

  .secure-badge i {
    color: var(--pp-success);
    font-size: 0.9rem;
  }

  /* ─── Success Overlay ─── */
  .success-overlay {
    display: none;
    position: absolute;
    inset: 0;
    background: rgba(16, 185, 129, 0.05);
    border-radius: 28px;
    z-index: 20;
    opacity: 0;
    transition: opacity 0.5s ease;
  }

  .success-overlay.visible {
    display: block;
    opacity: 1;
  }

  /* ─── Pulse Dots ─── */
  .pulse-dots {
    display: inline-flex;
    gap: 4px;
    margin-left: 4px;
    vertical-align: middle;
  }

  .pulse-dots span {
    width: 5px;
    height: 5px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.5);
    animation: pulseDot 1.4s ease-in-out infinite;
  }

  .pulse-dots span:nth-child(2) { animation-delay: 0.2s; }
  .pulse-dots span:nth-child(3) { animation-delay: 0.4s; }

  @keyframes pulseDot {
    0%, 80%, 100% { transform: scale(0.6); opacity: 0.3; }
    40% { transform: scale(1.2); opacity: 1; }
  }

  /* ─── Order info strip ─── */
  .order-info-strip {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 1.5rem;
    flex-wrap: wrap;
    margin-top: 0.5rem;
  }

  .info-chip {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    color: rgba(255, 255, 255, 0.4);
    font-size: 0.78rem;
  }

  .info-chip i {
    color: rgba(255, 255, 255, 0.25);
    font-size: 0.85rem;
  }

  /* ─── Sparkle particles ─── */
  .sparkle-particle {
    position: absolute;
    width: 4px;
    height: 4px;
    border-radius: 50%;
    background: var(--pp-primary);
    pointer-events: none;
    animation: sparkleFloat 3s ease-out forwards;
  }

  @keyframes sparkleFloat {
    0% { opacity: 1; transform: translateY(0) scale(1); }
    100% { opacity: 0; transform: translateY(-100px) scale(0); }
  }

  /* ─── Responsive ─── */
  @media (max-width: 576px) {
    .processing-card {
      padding: 2rem 1.5rem;
      margin: 1rem;
      border-radius: 20px;
    }

    .spinner-container {
      width: 100px;
      height: 100px;
    }

    .status-title {
      font-size: 1.25rem;
    }

    .amount-value {
      font-size: 1.35rem;
    }
  }
</style>

<main class="payment-processing-page" id="paymentProcessingPage">
  <!-- Floating Orbs -->
  <div class="floating-orb orb-1"></div>
  <div class="floating-orb orb-2"></div>
  <div class="floating-orb orb-3"></div>

  <!-- Main Processing Card -->
  <div class="processing-card" id="processingCard">
    <!-- Success Overlay -->
    <div class="success-overlay" id="successOverlay"></div>

    <!-- Spinner -->
    <div class="spinner-container" id="spinnerContainer">
      <div class="spinner-ring-outer"></div>
      <div class="spinner-ring spinner-ring-active"></div>
      <div class="spinner-icon" id="spinnerIcon">
        <i class="bi bi-shield-lock"></i>
      </div>
    </div>

    <!-- Status Text -->
    <h3 class="status-title" id="statusTitle">
      Verifying Payment
      <span class="pulse-dots"><span></span><span></span><span></span></span>
    </h3>
    <p class="status-subtitle" id="statusSubtitle">Establishing secure connection with payment gateway</p>

    <!-- Progress Steps -->
    <div class="progress-steps" id="progressSteps">
      <!-- Step 1 -->
      <div class="progress-step active" data-step="1">
        <div class="step-indicator">
          <div class="step-dot"><i class="bi bi-shield-lock"></i></div>
          <div class="step-line"></div>
        </div>
        <div class="step-content">
          <div class="step-label">Verifying Payment Details</div>
          <div class="step-desc">Validating your payment information securely</div>
        </div>
      </div>

      <!-- Step 2 -->
      <div class="progress-step" data-step="2">
        <div class="step-indicator">
          <div class="step-dot"><i class="bi bi-arrow-left-right"></i></div>
          <div class="step-line"></div>
        </div>
        <div class="step-content">
          <div class="step-label">Processing Transaction</div>
          <div class="step-desc">Communicating with your bank for authorization</div>
        </div>
      </div>

      <!-- Step 3 -->
      <div class="progress-step" data-step="3">
        <div class="step-indicator">
          <div class="step-dot"><i class="bi bi-patch-check"></i></div>
          <div class="step-line"></div>
        </div>
        <div class="step-content">
          <div class="step-label">Securing Your Order</div>
          <div class="step-desc">Encrypting transaction and generating receipt</div>
        </div>
      </div>

      <!-- Step 4 -->
      <div class="progress-step" data-step="4">
        <div class="step-indicator">
          <div class="step-dot"><i class="bi bi-check-lg"></i></div>
        </div>
        <div class="step-content">
          <div class="step-label">Payment Complete</div>
          <div class="step-desc">Your order has been confirmed successfully</div>
        </div>
      </div>
    </div>

    <!-- Amount Badge -->
    <div class="amount-badge">
      <div class="amount-row">
        <span class="amount-label">Amount</span>
        <span class="amount-value"><?= formatCurrency($order['total_amount']) ?></span>
      </div>
      <div class="order-info-strip">
        <span class="info-chip">
          <i class="bi bi-receipt"></i>
          <?= sanitize($order['order_number']) ?>
        </span>
        <span class="info-chip">
          <i class="bi bi-box-seam"></i>
          <?= $itemCount ?> item<?= $itemCount > 1 ? 's' : '' ?>
        </span>
        <span class="payment-method-badge">
          <i class="bi <?= $paymentIcon ?>"></i>
          <?= $paymentLabel ?>
        </span>
      </div>
    </div>

    <!-- Secure Badge -->
    <div class="secure-badge">
      <i class="bi bi-lock-fill"></i>
      <span>256-bit SSL Encrypted &middot; PCI DSS Compliant &middot; Vyapar Setu Secure Pay</span>
    </div>
  </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
  const steps = [
    {
      title: 'Verifying Payment Details',
      subtitle: 'Establishing secure connection with payment gateway',
      icon: 'bi-shield-lock',
      duration: 2200
    },
    {
      title: 'Processing Transaction',
      subtitle: 'Communicating with your bank for authorization',
      icon: 'bi-arrow-left-right',
      duration: 2500
    },
    {
      title: 'Securing Your Order',
      subtitle: 'Encrypting transaction and generating receipt',
      icon: 'bi-patch-check',
      duration: 2000
    },
    {
      title: 'Payment Successful!',
      subtitle: 'Redirecting you to your order confirmation...',
      icon: 'bi-check-circle-fill',
      duration: 1500
    }
  ];

  const statusTitle = document.getElementById('statusTitle');
  const statusSubtitle = document.getElementById('statusSubtitle');
  const spinnerIcon = document.getElementById('spinnerIcon');
  const spinnerContainer = document.getElementById('spinnerContainer');
  const successOverlay = document.getElementById('successOverlay');
  const processingCard = document.getElementById('processingCard');
  const progressSteps = document.querySelectorAll('.progress-step');

  let currentStep = 0;

  function updateStep(stepIndex) {
    const step = steps[stepIndex];

    // Update main icon with pop animation
    spinnerIcon.classList.remove('icon-enter');
    void spinnerIcon.offsetWidth; // Force reflow
    spinnerIcon.innerHTML = `<i class="bi ${step.icon}"></i>`;
    spinnerIcon.classList.add('icon-enter');

    // Update text
    if (stepIndex < steps.length - 1) {
      statusTitle.innerHTML = `${step.title}<span class="pulse-dots"><span></span><span></span><span></span></span>`;
    } else {
      statusTitle.innerHTML = step.title;
    }
    statusSubtitle.textContent = step.subtitle;

    // Update progress steps
    progressSteps.forEach((el, i) => {
      el.classList.remove('active', 'completed');
      if (i < stepIndex) {
        el.classList.add('completed');
        // Update dot to checkmark for completed steps
        const dot = el.querySelector('.step-dot');
        dot.innerHTML = '<i class="bi bi-check-lg"></i>';
      } else if (i === stepIndex) {
        el.classList.add('active');
      }
    });

    // Spawn sparkle particles on step change
    spawnSparkles(3);
  }

  function spawnSparkles(count) {
    for (let i = 0; i < count; i++) {
      const sparkle = document.createElement('div');
      sparkle.className = 'sparkle-particle';
      sparkle.style.left = (30 + Math.random() * 40) + '%';
      sparkle.style.top = (30 + Math.random() * 40) + '%';
      sparkle.style.background = ['#4361ee', '#6366f1', '#10b981', '#f59e0b', '#ec4899'][Math.floor(Math.random() * 5)];
      sparkle.style.animationDuration = (2 + Math.random() * 2) + 's';
      processingCard.appendChild(sparkle);
      setTimeout(() => sparkle.remove(), 4000);
    }
  }

  function runSteps() {
    if (currentStep >= steps.length) return;

    updateStep(currentStep);

    if (currentStep < steps.length - 1) {
      setTimeout(() => {
        currentStep++;
        runSteps();
      }, steps[currentStep].duration);
    } else {
      // Final step — success!
      setTimeout(() => {
        // Turn spinner to success state
        spinnerContainer.classList.add('spinner-success');
        spinnerIcon.innerHTML = '<i class="bi bi-check-circle-fill"></i>';
        spinnerIcon.style.fontSize = '3rem';
        spinnerIcon.style.color = 'var(--pp-success)';

        // Show success overlay glow
        successOverlay.classList.add('visible');

        // Large sparkle burst
        spawnSparkles(10);

        // Change card border glow
        processingCard.style.borderColor = 'rgba(16, 185, 129, 0.3)';
        processingCard.style.boxShadow = '0 25px 60px rgba(0, 0, 0, 0.4), 0 0 60px rgba(16, 185, 129, 0.15)';

        // Redirect after a beat
        setTimeout(() => {
          window.location.href = '<?= APP_URL ?>/order-confirmation.php?id=<?= $orderId ?>';
        }, 1800);
      }, 500);
    }
  }

  // Start the animation sequence after a brief initial delay
  setTimeout(runSteps, 600);
});
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
