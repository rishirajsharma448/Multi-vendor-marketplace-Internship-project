<?php
$pageTitle = "Shopping Cart - Vyapar Setu";
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

requireAuth();
$user = getCurrentUser();
$db = getDBConnection();

// Fetch Cart
$cartStmt = $db->prepare("
    SELECT ci.id AS item_id, ci.quantity, ci.price, p.id AS product_id, p.title, p.stock, v.store_name, pi.image_url
    FROM cart c
    JOIN cart_items ci ON c.id = ci.cart_id
    JOIN products p ON ci.product_id = p.id
    LEFT JOIN vendors v ON p.vendor_id = v.id
    LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_primary = 1
    WHERE c.user_id = ?
");
$cartStmt->execute([$user['id']]);
$cartItems = $cartStmt->fetchAll();

// Process Quantity Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_qty'])) {
    $itemId = (int)$_POST['item_id'];
    $newQty = max(1, (int)$_POST['quantity']);
    $upStmt = $db->prepare("UPDATE cart_items SET quantity = ? WHERE id = ? AND cart_id IN (SELECT id FROM cart WHERE user_id = ?)");
    $upStmt->execute([$newQty, $itemId, $user['id']]);
    setFlash('success', 'Cart updated successfully.');
    header("Location: " . APP_URL . "/customer/cart.php");
    exit;
}

$subtotal = 0;
foreach ($cartItems as $item) {
    $subtotal += ($item['price'] * $item['quantity']);
}
$shipping = $subtotal > 0 ? 99.00 : 0.00;
$total = $subtotal + $shipping;
?>

<?php include __DIR__ . '/../includes/navbar.php'; ?>

<main class="py-4 bg-light">
  <div class="container-fluid px-lg-5">
    <h3 class="fw-bold mb-4"><i class="bi bi-cart3 text-primary me-2"></i> Your Shopping Cart</h3>

    <?php displayAlerts(); ?>

    <?php if (empty($cartItems)): ?>
      <div class="card card-custom p-5 text-center my-4">
        <i class="bi bi-cart-x text-muted display-2 mb-3"></i>
        <h4>Your cart is currently empty</h4>
        <p class="text-muted">Browse our multi-vendor marketplace to discover thousands of handcrafted and branded products.</p>
        <a href="<?= APP_URL ?>/shop.php" class="btn btn-primary-custom mx-auto max-w-200px">Start Shopping</a>
      </div>
    <?php else: ?>
      <div class="row g-4">
        <div class="col-lg-8">
          <div class="card card-custom border-0 shadow-sm p-3">
            <div class="table-responsive">
              <table class="table align-middle mb-0">
                <thead>
                  <tr class="text-uppercase text-muted small">
                    <th>Product</th>
                    <th>Price</th>
                    <th>Quantity</th>
                    <th>Total</th>
                    <th class="text-end">Action</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($cartItems as $item): ?>
                    <?php $img = !empty($item['image_url']) ? $item['image_url'] : 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?auto=format&fit=crop&w=150&q=80'; ?>
                    <tr>
                      <td>
                        <div class="d-flex align-items-center gap-3">
                          <img src="<?= sanitize($img) ?>" class="rounded border" width="60" height="60" style="object-fit: cover;">
                          <div>
                            <h6 class="fw-bold mb-0 text-dark"><?= sanitize($item['title']) ?></h6>
                            <small class="text-success"><i class="bi bi-shop me-1"></i><?= sanitize($item['store_name'] ?? 'Vendor') ?></small>
                          </div>
                        </div>
                      </td>
                      <td class="fw-bold"><?= formatCurrency($item['price']) ?></td>
                      <td style="width: 140px;">
                        <form action="<?= APP_URL ?>/customer/cart.php" method="POST" class="d-flex align-items-center gap-1">
                          <input type="hidden" name="item_id" value="<?= $item['item_id'] ?>">
                          <input type="number" name="quantity" class="form-control form-control-sm text-center" value="<?= (int)$item['quantity'] ?>" min="1" max="<?= max(1, (int)$item['stock']) ?>" onchange="this.form.submit()">
                          <button type="submit" name="update_qty" class="btn btn-sm btn-outline-secondary" title="Update"><i class="bi bi-check2"></i></button>
                        </form>
                      </td>
                      <td class="fw-bold text-primary-custom"><?= formatCurrency($item['price'] * $item['quantity']) ?></td>
                      <td class="text-end">
                        <form action="<?= APP_URL ?>/ajax/remove-cart.php" method="POST" class="d-inline">
                          <input type="hidden" name="item_id" value="<?= $item['item_id'] ?>">
                          <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                        </form>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <div class="col-lg-4">
          <div class="card card-custom border-0 shadow-sm p-4">
            <h5 class="fw-bold mb-3">Order Summary</h5>
            <div class="d-flex justify-content-between mb-2">
              <span class="text-muted">Subtotal</span>
              <span class="fw-bold"><?= formatCurrency($subtotal) ?></span>
            </div>
            <div class="d-flex justify-content-between mb-2">
              <span class="text-muted">Standard Shipping</span>
              <span class="fw-bold"><?= formatCurrency($shipping) ?></span>
            </div>
            <hr>
            <div class="d-flex justify-content-between mb-4">
              <span class="fs-5 fw-bold">Total Amount</span>
              <span class="fs-4 fw-extrabold text-primary-custom"><?= formatCurrency($total) ?></span>
            </div>

            <a href="checkout.php" class="btn btn-primary-custom py-2 w-100 fw-bold fs-6">
              Proceed to Checkout <i class="bi bi-arrow-right ms-1"></i>
            </a>
          </div>
        </div>
      </div>
    <?php endif; ?>
  </div>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>
