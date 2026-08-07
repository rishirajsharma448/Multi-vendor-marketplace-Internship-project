<?php
$pageTitle = "Manage Brands - Vyapar Setu Admin";
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

requireRole('admin');
$db = getDBConnection();
$brands = $db->query("SELECT * FROM brands ORDER BY id ASC")->fetchAll();
?>

<?php include __DIR__ . '/../includes/navbar.php'; ?>

<div class="dashboard-wrapper">
  <?php include __DIR__ . '/../includes/sidebar.php'; ?>

  <div class="dashboard-content">
    <h3 class="fw-bold mb-4"><i class="bi bi-award text-primary me-2"></i> Product Brands</h3>

    <div class="card card-custom border-0 shadow-sm">
      <div class="table-responsive">
        <table class="table table-custom mb-0">
          <thead>
            <tr>
              <th>ID</th>
              <th>Brand Name</th>
              <th>Slug</th>
              <th>Created Date</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($brands as $b): ?>
              <tr>
                <td>#<?= $b['id'] ?></td>
                <td class="fw-bold"><?= sanitize($b['name']) ?></td>
                <td><code><?= sanitize($b['slug']) ?></code></td>
                <td><?= date('d M Y', strtotime($b['created_at'])) ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
