<?php
$pageTitle = "Add New Product - Vyapar Setu Vendor";
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

requireRole('vendor');
$user = getCurrentUser();
$vendor = $user['vendor'] ?? null;
$vendor_id = $vendor['id'] ?? 1;
$db = getDBConnection();

$categories = $db->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();
$brands = $db->query("SELECT * FROM brands ORDER BY name ASC")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = sanitize($_POST['title'] ?? '');
    $sku = sanitize($_POST['sku'] ?? '');
    if (empty($sku)) {
        $sku = 'SKU-' . strtoupper(substr(md5(uniqid()), 0, 8));
    }
    $category_id = (int)($_POST['category_id'] ?? 1);
    $brand_id = !empty($_POST['brand_id']) ? (int)$_POST['brand_id'] : null;
    $description = sanitize($_POST['description'] ?? '');
    $price = (float)($_POST['price'] ?? 0);
    $original_price = (float)($_POST['original_price'] ?? $price);
    $stock = (int)($_POST['stock'] ?? 0);
    $spotlight = sanitize($_POST['spotlight'] ?? 'REGULAR');
    $image_url_input = sanitize($_POST['image_url'] ?? '');

    $finalImageUrl = '';

    // Handle File Upload from PC or Mobile Device
    if (isset($_FILES['product_image_file']) && $_FILES['product_image_file']['error'] === UPLOAD_ERR_OK) {
        try {
            $uploadedPath = handleFileUpload('product_image_file', 'uploads/products/');
            if ($uploadedPath) {
                $finalImageUrl = $uploadedPath;
            }
        } catch (Exception $ex) {
            setFlash('danger', 'Photo upload failed: ' . $ex->getMessage());
            header("Location: add-product.php");
            exit;
        }
    }

    if (empty($finalImageUrl)) {
        $finalImageUrl = !empty($image_url_input) ? $image_url_input : 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?auto=format&fit=crop&w=600&q=80';
    }

    if (!empty($title) && $price > 0) {
        $db->beginTransaction();
        try {
            $stmt = $db->prepare("
                INSERT INTO products (sku, vendor_id, category_id, brand_id, title, description, price, original_price, stock, spotlight, status)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'ACTIVE')
            ");
            $stmt->execute([$sku, $vendor_id, $category_id, $brand_id, $title, $description, $price, $original_price, $stock, $spotlight]);
            $productId = $db->lastInsertId();

            if (!empty($finalImageUrl)) {
                $imgStmt = $db->prepare("INSERT INTO product_images (product_id, image_url, is_primary) VALUES (?, ?, 1)");
                $imgStmt->execute([$productId, $finalImageUrl]);
            }

            $db->commit();
            setFlash('success', 'Product listed successfully with uploaded photo!');
            header("Location: products.php");
            exit;
        } catch (Exception $ex) {
            $db->rollBack();
            setFlash('danger', 'Error adding product: ' . $ex->getMessage());
        }
    } else {
        setFlash('danger', 'Please provide a valid product title and price.');
    }
}
?>

<?php include __DIR__ . '/../includes/navbar.php'; ?>

<div class="dashboard-wrapper">
  <?php include __DIR__ . '/../includes/sidebar.php'; ?>

  <div class="dashboard-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <div>
        <h3 class="fw-bold mb-1"><i class="bi bi-plus-circle text-primary me-2"></i> Add Product to Storefront</h3>
        <p class="text-muted small mb-0">Upload product photos directly from PC or Mobile device and set inventory details.</p>
      </div>
      <a href="products.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i> Back to Inventory</a>
    </div>

    <?php displayAlerts(); ?>

    <div class="card card-custom p-4 border-0 shadow-sm">
      <form action="add-product.php" method="POST" enctype="multipart/form-data">
        <div class="row g-3">
          <div class="col-md-8">
            <label class="form-label fw-500">Product Title *</label>
            <input type="text" name="title" class="form-control" placeholder="e.g. Wireless Noise-Cancelling Headphones" required>
          </div>

          <div class="col-md-4">
            <label class="form-label fw-500">SKU Code</label>
            <input type="text" name="sku" class="form-control" placeholder="Auto-generated if empty">
          </div>

          <div class="col-md-6">
            <label class="form-label fw-500">Category *</label>
            <select name="category_id" class="form-select" required>
              <?php foreach ($categories as $c): ?>
                <option value="<?= $c['id'] ?>"><?= sanitize($c['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="col-md-6">
            <label class="form-label fw-500">Brand (Optional)</label>
            <select name="brand_id" class="form-select">
              <option value="">-- Select Brand --</option>
              <?php foreach ($brands as $b): ?>
                <option value="<?= $b['id'] ?>"><?= sanitize($b['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="col-md-4">
            <label class="form-label fw-500">Offer Selling Price (₹) *</label>
            <input type="number" step="0.01" name="price" class="form-control" placeholder="999.00" required>
          </div>

          <div class="col-md-4">
            <label class="form-label fw-500">Original / MRP Price (₹)</label>
            <input type="number" step="0.01" name="original_price" class="form-control" placeholder="1499.00">
          </div>

          <div class="col-md-4">
            <label class="form-label fw-500">Inventory Stock Quantity *</label>
            <input type="number" name="stock" class="form-control" value="25" required>
          </div>

          <!-- Product Image Upload Section -->
          <div class="col-md-6">
            <label class="form-label fw-500 text-primary">
              <i class="bi bi-upload me-1"></i> Upload Photo from Device (PC / Mobile)
            </label>
            <input type="file" name="product_image_file" accept="image/*" class="form-control">
            <small class="text-muted d-block mt-1">Select an image file (JPG, PNG, WEBP, GIF up to 5MB).</small>
          </div>

          <div class="col-md-6">
            <label class="form-label fw-500">OR Enter Web Image URL</label>
            <input type="url" name="image_url" class="form-control" placeholder="https://images.unsplash.com/...">
            <small class="text-muted d-block mt-1">Used if no file is selected above.</small>
          </div>

          <div class="col-md-6">
            <label class="form-label fw-500">Spotlight Status</label>
            <select name="spotlight" class="form-select">
              <option value="REGULAR">Regular Listing</option>
              <option value="FEATURED">Featured Product</option>
              <option value="BESTSELLER">Bestseller Badge</option>
            </select>
          </div>

          <div class="col-12">
            <label class="form-label fw-500">Detailed Product Description</label>
            <textarea name="description" class="form-control" rows="4" placeholder="Highlight key features, material, warranty details..."></textarea>
          </div>

          <div class="col-12 mt-4">
            <button type="submit" class="btn btn-primary-custom px-4 py-2 fw-600">
              <i class="bi bi-cloud-upload me-2"></i> Save & Publish Product
            </button>
          </div>
        </div>
      </form>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
