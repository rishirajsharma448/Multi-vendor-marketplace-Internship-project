<?php
$pageTitle = "Manage Customers - Vyapar Setu Admin";
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

requireRole('admin');
$db = getDBConnection();

$customers = $db->query("SELECT * FROM users WHERE role = 'customer' ORDER BY id DESC")->fetchAll();
?>

<?php include __DIR__ . '/../includes/navbar.php'; ?>

<div class="dashboard-wrapper">
  <?php include __DIR__ . '/../includes/sidebar.php'; ?>

  <div class="dashboard-content">
    <h3 class="fw-bold mb-4"><i class="bi bi-people text-primary me-2"></i> Registered Customers</h3>

    <div class="card card-custom border-0 shadow-sm">
      <div class="table-responsive">
        <table class="table table-custom mb-0">
          <thead>
            <tr>
              <th>ID</th>
              <th>Customer Name</th>
              <th>Email</th>
              <th>Phone</th>
              <th>Joined Date</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($customers as $c): ?>
              <tr>
                <td>#<?= $c['id'] ?></td>
                <td class="fw-bold"><?= sanitize($c['name']) ?></td>
                <td><?= sanitize($c['email']) ?></td>
                <td><?= sanitize($c['phone'] ?: 'N/A') ?></td>
                <td><?= date('d M Y', strtotime($c['created_at'])) ?></td>
                <td><span class="badge bg-success"><?= sanitize($c['status']) ?></span></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
