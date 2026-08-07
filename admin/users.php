<?php
$pageTitle = "All Users - Vyapar Setu Admin";
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

requireRole('admin');
$db = getDBConnection();
$users = $db->query("SELECT * FROM users ORDER BY id DESC")->fetchAll();
?>

<?php include __DIR__ . '/../includes/navbar.php'; ?>

<div class="dashboard-wrapper">
  <?php include __DIR__ . '/../includes/sidebar.php'; ?>

  <div class="dashboard-content">
    <h3 class="fw-bold mb-4"><i class="bi bi-people-fill text-primary me-2"></i> All System Accounts</h3>

    <div class="card card-custom border-0 shadow-sm">
      <div class="table-responsive">
        <table class="table table-custom mb-0">
          <thead>
            <tr>
              <th>ID</th>
              <th>Name</th>
              <th>Email</th>
              <th>Role</th>
              <th>Status</th>
              <th>Joined Date</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($users as $u): ?>
              <tr>
                <td>#<?= $u['id'] ?></td>
                <td class="fw-bold"><?= sanitize($u['name']) ?></td>
                <td><?= sanitize($u['email']) ?></td>
                <td><span class="badge bg-primary text-uppercase"><?= sanitize($u['role']) ?></span></td>
                <td><span class="badge bg-success"><?= sanitize($u['status']) ?></span></td>
                <td><?= date('d M Y', strtotime($u['created_at'])) ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
