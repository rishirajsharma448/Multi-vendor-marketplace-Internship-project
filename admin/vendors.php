<?php
$pageTitle = "Manage Vendors - Vyapar Setu Admin";
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

requireRole('admin');
$db = getDBConnection();

if (isset($_GET['action'], $_GET['id'])) {
    $vId = (int)$_GET['id'];
    $act = $_GET['action'];
    $status = ($act === 'approve') ? 'APPROVED' : (($act === 'reject') ? 'REJECTED' : 'PENDING');
    
    $up = $db->prepare("UPDATE vendors SET status = ? WHERE id = ?");
    $up->execute([$status, $vId]);
    setFlash('success', 'Vendor status updated to ' . $status);
    header("Location: vendors.php");
    exit;
}

$vendors = $db->query("SELECT * FROM vendors ORDER BY id DESC")->fetchAll();
?>

<?php include __DIR__ . '/../includes/navbar.php'; ?>

<div class="dashboard-wrapper">
  <?php include __DIR__ . '/../includes/sidebar.php'; ?>

  <div class="dashboard-content">
    <h3 class="fw-bold mb-4"><i class="bi bi-shop-window text-primary me-2"></i> Manage Registered Vendors</h3>

    <?php displayAlerts(); ?>

    <div class="card card-custom border-0 shadow-sm">
      <div class="table-responsive">
        <table class="table table-custom mb-0">
          <thead>
            <tr>
              <th>Code</th>
              <th>Store Name</th>
              <th>Owner Name</th>
              <th>Email / Phone</th>
              <th>Category</th>
              <th>Commission</th>
              <th>Status</th>
              <th class="text-end">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($vendors as $v): ?>
              <tr>
                <td class="fw-bold text-primary"><?= sanitize($v['vendor_code']) ?></td>
                <td class="fw-bold"><?= sanitize($v['store_name']) ?></td>
                <td><?= sanitize($v['owner_name']) ?></td>
                <td>
                  <div><?= sanitize($v['email']) ?></div>
                  <small class="text-muted"><?= sanitize($v['phone']) ?></small>
                </td>
                <td><span class="badge bg-light text-dark border"><?= sanitize($v['category']) ?></span></td>
                <td><span class="badge bg-secondary"><?= sanitize($v['commission_rate'] ?: '10%') ?></span></td>
                <td><span class="badge <?= getStatusBadgeClass($v['status']) ?>"><?= sanitize($v['status']) ?></span></td>
                <td class="text-end">
                  <?php if ($v['status'] !== 'APPROVED'): ?>
                    <a href="vendors.php?action=approve&id=<?= $v['id'] ?>" class="btn btn-sm btn-success me-1">Approve</a>
                  <?php endif; ?>
                  <?php if ($v['status'] !== 'REJECTED'): ?>
                    <a href="vendors.php?action=reject&id=<?= $v['id'] ?>" class="btn btn-sm btn-outline-danger">Reject</a>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
