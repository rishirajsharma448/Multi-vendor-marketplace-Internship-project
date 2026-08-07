<?php
$pageTitle = "Store Reviews - Vyapar Setu";
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

requireRole('vendor');
$user = getCurrentUser();
$vendor = $user['vendor'] ?? null;
$vendor_id = $vendor['id'] ?? 1;
$db = getDBConnection();

// Vendor Response update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['review_id'], $_POST['vendor_response'])) {
    $rId = (int)$_POST['review_id'];
    $resp = sanitize($_POST['vendor_response']);
    
    $up = $db->prepare("UPDATE reviews SET vendor_response = ? WHERE id = ?");
    $up->execute([$resp, $rId]);
    setFlash('success', 'Response submitted to customer review.');
    header("Location: reviews.php");
    exit;
}

$stmt = $db->prepare("
    SELECT r.*, p.title AS product_title 
    FROM reviews r 
    JOIN products p ON r.product_id = p.id 
    WHERE p.vendor_id = ? 
    ORDER BY r.id DESC
");
$stmt->execute([$vendor_id]);
$reviews = $stmt->fetchAll();
?>

<?php include __DIR__ . '/../includes/navbar.php'; ?>

<div class="dashboard-wrapper">
  <?php include __DIR__ . '/../includes/sidebar.php'; ?>

  <div class="dashboard-content">
    <h3 class="fw-bold mb-4"><i class="bi bi-star-fill text-warning me-2"></i> Customer Reviews & Seller Responses</h3>

    <?php displayAlerts(); ?>

    <?php if (empty($reviews)): ?>
      <div class="card card-custom p-5 text-center my-4">
        <i class="bi bi-chat-square-dots text-muted display-4 mb-3"></i>
        <h5>No customer reviews yet</h5>
        <p class="text-muted">Reviews submitted for your products will appear here.</p>
      </div>
    <?php else: ?>
      <div class="row g-4">
        <?php foreach ($reviews as $rev): ?>
          <div class="col-md-6">
            <div class="card card-custom p-4 border-0 shadow-sm">
              <div class="d-flex justify-content-between align-items-center mb-2">
                <h6 class="fw-bold mb-0 text-primary"><?= sanitize($rev['product_title']) ?></h6>
                <small class="text-muted"><?= date('d M Y', strtotime($rev['created_at'])) ?></small>
              </div>
              <div class="mb-2">
                <?= renderStars($rev['rating']) ?>
                <span class="small fw-bold ms-1"><?= sanitize($rev['customer_name']) ?></span>
              </div>
              <p class="text-muted small mb-3">"<?= sanitize($rev['comment']) ?>"</p>

              <?php if (!empty($rev['vendor_response'])): ?>
                <div class="bg-light p-2.5 rounded border-start border-primary border-3">
                  <small class="fw-bold text-dark d-block">Your Response:</small>
                  <small class="text-muted"><?= sanitize($rev['vendor_response']) ?></small>
                </div>
              <?php else: ?>
                <form action="reviews.php" method="POST" class="mt-2">
                  <input type="hidden" name="review_id" value="<?= $rev['id'] ?>">
                  <div class="input-group input-group-sm">
                    <input type="text" name="vendor_response" class="form-control" placeholder="Write seller response..." required>
                    <button type="submit" class="btn btn-primary-custom">Reply</button>
                  </div>
                </form>
              <?php endif; ?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
