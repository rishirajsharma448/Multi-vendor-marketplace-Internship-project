<?php
$pageTitle = "Edit Product - Vyapar Setu Vendor";
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

requireRole('vendor');
$user = getCurrentUser();
$vendor = $user['vendor'] ?? null;
$vendor_id = $vendor['id'] ?? 1;
$productId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$db = getDBConnection();

$stmt = $db->prepare("
    SELECT p.*, pi.image_url AS primary_image 
    FROM products p 
    LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_primary = 1
    WHERE p.id = ? AND p.vendor_id = ?
");
$stmt->execute([$productId, $vendor_id]);
$product = $stmt->fetch();

if (!$product) {
    header("Location: products.php");
    exit;
}

$categories = $db->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = sanitize($_POST['title'] ?? '');
    $category_id = (int)($_POST['category_id'] ?? 1);
    $description = sanitize($_POST['description'] ?? '');
    $price = (float)($_POST['price'] ?? 0);
    $original_price = (float)($_POST['original_price'] ?? $price);
    $stock = (int)($_POST['stock'] ?? 0);
    $spotlight = sanitize($_POST['spotlight'] ?? 'REGULAR');
    $image_url_input = sanitize($_POST['image_url'] ?? '');

    $newImageUrl = '';
    // Handle photo file upload from device
    if (isset($_FILES['product_image_file']) && $_FILES['product_image_file']['error'] === UPLOAD_ERR_OK) {
        try {
            $uploadedPath = handleFileUpload('product_image_file', 'uploads/product_photos/');
            if ($uploadedPath) {
                $newImageUrl = $uploadedPath;
            }
        } catch (Exception $ex) {
            setFlash('danger', 'Photo upload failed: ' . $ex->getMessage());
            header("Location: edit-product.php?id=" . $productId);
            exit;
        }
    } elseif (!empty($image_url_input)) {
        $newImageUrl = $image_url_input;
    }

    if (!empty($title) && $price > 0) {
        $up = $db->prepare("UPDATE products SET title = ?, category_id = ?, description = ?, price = ?, original_price = ?, stock = ?, spotlight = ? WHERE id = ? AND vendor_id = ?");
        $up->execute([$title, $category_id, $description, $price, $original_price, $stock, $spotlight, $productId, $vendor_id]);

        if (!empty($newImageUrl)) {
            // Update or Insert product_images
            $chkImg = $db->prepare("SELECT id FROM product_images WHERE product_id = ? AND is_primary = 1");
            $chkImg->execute([$productId]);
            if ($chkImg->fetch()) {
                $upImg = $db->prepare("UPDATE product_images SET image_url = ? WHERE product_id = ? AND is_primary = 1");
                $upImg->execute([$newImageUrl, $productId]);
            } else {
                $insImg = $db->prepare("INSERT INTO product_images (product_id, image_url, is_primary) VALUES (?, ?, 1)");
                $insImg->execute([$productId, $newImageUrl]);
            }
        }

        setFlash('success', 'Product details and photo updated successfully!');
        header("Location: products.php");
        exit;
    }
}
?>

<?php include __DIR__ . '/../includes/navbar.php'; ?>

<div class="dashboard-wrapper">
  <?php include __DIR__ . '/../includes/sidebar.php'; ?>

  <div class="dashboard-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <div>
        <h3 class="fw-bold mb-1"><i class="bi bi-pencil-square text-primary me-2"></i> Edit Product Listing</h3>
        <p class="text-muted small mb-0">Update item details, pricing, inventory stock, and upload new photos from your device.</p>
      </div>
      <a href="products.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i> Back to Inventory</a>
    </div>

    <?php displayAlerts(); ?>

    <div class="card card-custom p-4 border-0 shadow-sm">
      <form action="edit-product.php?id=<?= $productId ?>" method="POST" enctype="multipart/form-data">
        <div class="row g-3">
          <div class="col-md-8">
            <label class="form-label fw-500">Product Title *</label>
            <input type="text" name="title" class="form-control" value="<?= sanitize($product['title']) ?>" required>
          </div>

          <div class="col-md-4">
            <label class="form-label fw-500">SKU Code</label>
            <input type="text" class="form-control bg-light" value="<?= sanitize($product['sku']) ?>" readonly>
          </div>

          <div class="col-md-6">
            <label class="form-label fw-500">Category *</label>
            <select name="category_id" class="form-select">
              <?php foreach ($categories as $c): ?>
                <option value="<?= $c['id'] ?>" <?= $product['category_id'] == $c['id'] ? 'selected' : '' ?>><?= sanitize($c['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="col-md-3">
            <label class="form-label fw-500">Offer Price (₹) *</label>
            <input type="number" step="0.01" name="price" class="form-control" value="<?= $product['price'] ?>" required>
          </div>

          <div class="col-md-3">
            <label class="form-label fw-500">Original Price (₹)</label>
            <input type="number" step="0.01" name="original_price" class="form-control" value="<?= $product['original_price'] ?>">
          </div>

          <div class="col-md-6">
            <label class="form-label fw-500">Stock Quantity *</label>
            <input type="number" name="stock" class="form-control" value="<?= $product['stock'] ?>" required>
          </div>

          <div class="col-md-6">
            <label class="form-label fw-500">Spotlight Status</label>
            <select name="spotlight" class="form-select">
              <option value="REGULAR" <?= ($product['spotlight'] ?? '') === 'REGULAR' ? 'selected' : '' ?>>Regular Listing</option>
              <option value="FEATURED" <?= ($product['spotlight'] ?? '') === 'FEATURED' ? 'selected' : '' ?>>Featured Product</option>
              <option value="BESTSELLER" <?= ($product['spotlight'] ?? '') === 'BESTSELLER' ? 'selected' : '' ?>>Bestseller Badge</option>
            </select>
          </div>

          <!-- Photo File Upload from Device -->
          <div class="col-md-6">
            <label class="form-label fw-500 text-primary"><i class="bi bi-upload me-1"></i> Replace Photo from PC / Mobile</label>
            <input type="file" name="product_image_file" id="product_image_file" accept="image/*" class="form-control">
            <small class="text-muted">Upload a new image file to replace current photo.</small>
          </div>

          <div class="col-md-6">
            <label class="form-label fw-500">OR Update Web Image URL</label>
            <input type="url" name="image_url" id="product_image_url" class="form-control" value="<?= sanitize($product['primary_image'] ?? '') ?>">
          </div>

          <!-- Product Photo Frame Preview (Bounded Fit) -->
          <div class="col-12 mt-2 mb-2">
            <label class="form-label fw-500 text-dark"><i class="bi bi-aspect-ratio me-1"></i> Product Photo Frame Preview (Bounded Fit)</label>
            <div class="product-upload-preview-frame">
              <?php $curImg = !empty($product['primary_image']) ? ((strpos($product['primary_image'], 'http') === 0) ? $product['primary_image'] : APP_URL . '/' . $product['primary_image']) : 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?auto=format&fit=crop&w=600&q=80'; ?>
              <img id="uploadImagePreview" src="<?= sanitize($curImg) ?>" alt="Preview">
            </div>
            <small class="text-muted d-block mt-1"><i class="bi bi-shield-check text-success me-1"></i> Full picture will be kept completely inside this product frame limit without overflowing or stretching.</small>
          </div>

          <div class="col-12">
            <label class="form-label fw-500">Description</label>
            <textarea name="description" class="form-control" rows="4"><?= sanitize($product['description']) ?></textarea>
          </div>

          <div class="col-12 mt-3">
            <button type="submit" class="btn btn-primary-custom px-4 py-2 fw-600">
              <i class="bi bi-save me-1"></i> Update Product Listing
            </button>
          </div>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  const fileInput = document.getElementById('product_image_file');
  const urlInput = document.getElementById('product_image_url');
  const previewImg = document.getElementById('uploadImagePreview');

  if (fileInput && previewImg) {
    fileInput.addEventListener('change', function() {
      if (this.files && this.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
          previewImg.src = e.target.result;
        };
        reader.readAsDataURL(this.files[0]);
      }
    });
  }

  if (urlInput && previewImg) {
    urlInput.addEventListener('input', function() {
      if (this.value.trim() !== '') {
        previewImg.src = this.value.trim();
      }
    });
  }
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
