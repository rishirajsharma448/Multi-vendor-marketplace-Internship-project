<?php
$pageTitle = "Manage Categories - Vyapar Setu Admin";
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

requireRole('admin');
$db = getDBConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name'] ?? '');
    $icon = sanitize($_POST['icon'] ?? 'bi-box');
    $description = sanitize($_POST['description'] ?? '');
    $slug = slugify($name);

    if (!empty($name)) {
        $stmt = $db->prepare("INSERT INTO categories (name, slug, icon, description) VALUES (?, ?, ?, ?)");
        $stmt->execute([$name, $slug, $icon, $description]);
        setFlash('success', 'Category created successfully!');
        header("Location: categories.php");
        exit;
    }
}

$categories = $db->query("SELECT * FROM categories ORDER BY id ASC")->fetchAll();
?>

<?php include __DIR__ . '/../includes/navbar.php'; ?>

<div class="dashboard-wrapper">
  <?php include __DIR__ . '/../includes/sidebar.php'; ?>

  <div class="dashboard-content">
    <h3 class="fw-bold mb-4"><i class="bi bi-tags text-primary me-2"></i> Categories & Taxonomies</h3>

    <?php displayAlerts(); ?>

    <div class="row g-4">
      <div class="col-lg-4">
        <div class="card card-custom p-4 border-0 shadow-sm">
          <h5 class="fw-bold mb-3">Add New Category</h5>
          <form action="categories.php" method="POST">
            <div class="mb-3">
              <label class="form-label fw-500">Category Name *</label>
              <input type="text" name="name" class="form-control" placeholder="e.g. Footwear & Shoes" required>
            </div>
            <div class="mb-3">
              <label class="form-label fw-500">Bootstrap Icon Class</label>
              <input type="text" name="icon" class="form-control" value="bi-bag-check" placeholder="bi-laptop">
            </div>
            <div class="mb-3">
              <label class="form-label fw-500">Description</label>
              <textarea name="description" class="form-control" rows="3"></textarea>
            </div>
            <button type="submit" class="btn btn-primary-custom w-100 py-2">Create Category</button>
          </form>
        </div>
      </div>

      <div class="col-lg-8">
        <div class="card card-custom border-0 shadow-sm">
          <div class="table-responsive">
            <table class="table table-custom mb-0">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Icon</th>
                  <th>Category Name</th>
                  <th>Slug</th>
                  <th>Description</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($categories as $cat): ?>
                  <tr>
                    <td>#<?= $cat['id'] ?></td>
                    <td><i class="bi <?= sanitize($cat['icon'] ?: 'bi-box') ?> fs-4 text-primary"></i></td>
                    <td class="fw-bold"><?= sanitize($cat['name']) ?></td>
                    <td><code><?= sanitize($cat['slug']) ?></code></td>
                    <td class="small text-muted"><?= sanitize($cat['description']) ?></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
