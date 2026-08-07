<?php
$pageTitle = "Admin Governance Dashboard - Vyapar Setu";
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

requireRole('admin');
$user = getCurrentUser();
$db = getDBConnection();

// Metrics
$userCnt = $db->query("SELECT COUNT(*) AS cnt FROM users")->fetch()['cnt'] ?? 0;
$custCnt = $db->query("SELECT COUNT(*) AS cnt FROM users WHERE role='customer'")->fetch()['cnt'] ?? 0;
$vendCnt = $db->query("SELECT COUNT(*) AS cnt FROM vendors")->fetch()['cnt'] ?? 0;
$prodCnt = $db->query("SELECT COUNT(*) AS cnt FROM products")->fetch()['cnt'] ?? 0;
$ordCnt = $db->query("SELECT COUNT(*) AS cnt FROM orders")->fetch()['cnt'] ?? 0;
$revTotal = $db->query("SELECT COALESCE(SUM(total_amount), 0) AS total FROM orders WHERE payment_status='Paid'")->fetch()['total'] ?? 0;
$pendVend = $db->query("SELECT COUNT(*) AS cnt FROM vendors WHERE status='PENDING'")->fetch()['cnt'] ?? 0;

// Pending Vendors
$pendingVendors = $db->query("SELECT * FROM vendors WHERE status='PENDING' ORDER BY id DESC")->fetchAll();

// Activity logs
$logs = $db->query("SELECT * FROM activity_logs ORDER BY id DESC LIMIT 6")->fetchAll();
?>

<?php include __DIR__ . '/../includes/navbar.php'; ?>

<div class="dashboard-wrapper">
  <?php include __DIR__ . '/../includes/sidebar.php'; ?>

  <div class="dashboard-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <div>
        <h3 class="fw-bold mb-1"><i class="bi bi-shield-check text-primary me-2"></i> Marketplace Master Control</h3>
        <p class="text-muted small mb-0">System status overview, vendor compliance, and overall marketplace health.</p>
      </div>
      <a href="vendors.php" class="btn btn-primary-custom btn-sm"><i class="bi bi-person-check me-1"></i> Review Vendor Applications (<?= $pendVend ?>)</a>
    </div>

    <?php displayAlerts(); ?>

    <!-- Summary Stats -->
    <div class="row g-3 mb-4">
      <div class="col-md-4 col-lg-2.4">
        <div class="stat-card">
          <div class="stat-icon-wrapper bg-primary-light text-primary"><i class="bi bi-people"></i></div>
          <div>
            <h4 class="fw-bold mb-0"><?= $userCnt ?></h4>
            <small class="text-muted fw-500">Total Users</small>
          </div>
        </div>
      </div>

      <div class="col-md-4 col-lg-2.4">
        <div class="stat-card">
          <div class="stat-icon-wrapper bg-info-subtle text-info"><i class="bi bi-shop"></i></div>
          <div>
            <h4 class="fw-bold mb-0"><?= $vendCnt ?></h4>
            <small class="text-muted fw-500">Total Vendors</small>
          </div>
        </div>
      </div>

      <div class="col-md-4 col-lg-2.4">
        <div class="stat-card">
          <div class="stat-icon-wrapper bg-success-subtle text-success"><i class="bi bi-boxes"></i></div>
          <div>
            <h4 class="fw-bold mb-0"><?= $prodCnt ?></h4>
            <small class="text-muted fw-500">Products</small>
          </div>
        </div>
      </div>

      <div class="col-md-4 col-lg-2.4">
        <div class="stat-card">
          <div class="stat-icon-wrapper bg-warning-subtle text-warning"><i class="bi bi-receipt"></i></div>
          <div>
            <h4 class="fw-bold mb-0"><?= $ordCnt ?></h4>
            <small class="text-muted fw-500">Orders</small>
          </div>
        </div>
      </div>

      <div class="col-md-4 col-lg-2.4">
        <div class="stat-card">
          <div class="stat-icon-wrapper bg-danger-subtle text-danger"><i class="bi bi-currency-rupee"></i></div>
          <div>
            <h4 class="fw-bold mb-0"><?= formatCurrency($revTotal) ?></h4>
            <small class="text-muted fw-500">GMV Sales</small>
          </div>
        </div>
      </div>
    </div>

    <!-- Charts -->
    <div class="row g-4 mb-4">
      <div class="col-lg-8">
        <div class="card card-custom p-4 border-0 shadow-sm">
          <h5 class="fw-bold mb-3"><i class="bi bi-bar-chart-line text-primary me-2"></i> Monthly Revenue & Growth Trends</h5>
          <canvas id="adminRevenueChart" height="200"></canvas>
        </div>
      </div>

      <div class="col-lg-4">
        <div class="card card-custom p-4 border-0 shadow-sm h-100">
          <h5 class="fw-bold mb-3"><i class="bi bi-pie-chart text-secondary me-2"></i> User Role Ratio</h5>
          <canvas id="adminUserPieChart" height="200"></canvas>
        </div>
      </div>
    </div>

    <!-- Vendor Approval Requests -->
    <div class="card card-custom border-0 shadow-sm mb-4">
      <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
        <h5 class="fw-bold mb-0"><i class="bi bi-person-exclamation me-2 text-warning"></i> Pending Vendor Approvals (<?= count($pendingVendors) ?>)</h5>
      </div>
      <div class="table-responsive">
        <table class="table table-custom mb-0">
          <thead>
            <tr>
              <th>Vendor Code</th>
              <th>Store Name</th>
              <th>Owner Name</th>
              <th>Email</th>
              <th>Category</th>
              <th>GSTIN</th>
              <th class="text-end">Action</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($pendingVendors)): ?>
              <tr>
                <td colspan="7" class="text-center py-4 text-muted">No pending vendor approval requests. All clean!</td>
              </tr>
            <?php else: ?>
              <?php foreach ($pendingVendors as $v): ?>
                <tr>
                  <td class="fw-bold text-primary"><?= sanitize($v['vendor_code']) ?></td>
                  <td class="fw-bold"><?= sanitize($v['store_name']) ?></td>
                  <td><?= sanitize($v['owner_name']) ?></td>
                  <td><?= sanitize($v['email']) ?></td>
                  <td><span class="badge bg-light text-dark border"><?= sanitize($v['category']) ?></span></td>
                  <td><code><?= sanitize($v['gstin'] ?: 'N/A') ?></code></td>
                  <td class="text-end">
                    <a href="vendors.php?action=approve&id=<?= $v['id'] ?>" class="btn btn-sm btn-success px-2 me-1">Approve</a>
                    <a href="vendors.php?action=reject&id=<?= $v['id'] ?>" class="btn btn-sm btn-outline-danger px-2">Reject</a>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Activity Logs -->
    <div class="card card-custom p-4 border-0 shadow-sm">
      <h5 class="fw-bold mb-3"><i class="bi bi-journal-text me-2 text-primary"></i> System Activity Logs</h5>
      <ul class="list-group list-group-flush small">
        <?php foreach ($logs as $l): ?>
          <li class="list-group-item px-0 d-flex justify-content-between text-muted">
            <span><i class="bi bi-info-circle text-primary me-2"></i><?= sanitize($l['action_description']) ?></span>
            <span><?= date('Y-m-d H:i', strtotime($l['created_at'])) ?> (<?= sanitize($l['ip_address'] ?? '127.0.0.1') ?>)</span>
          </li>
        <?php endforeach; ?>
      </ul>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  const lineCtx = document.getElementById('adminRevenueChart');
  if (lineCtx) {
    new Chart(lineCtx, {
      type: 'bar',
      data: {
        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug'],
        datasets: [{
          label: 'Marketplace GMV (₹)',
          data: [45000, 62000, 58000, 89000, 95000, 112000, 135000, 150000],
          backgroundColor: '#2563EB',
          borderRadius: 6
        }]
      },
      options: { responsive: true }
    });
  }

  const pieCtx = document.getElementById('adminUserPieChart');
  if (pieCtx) {
    new Chart(pieCtx, {
      type: 'doughnut',
      data: {
        labels: ['Customers', 'Vendors', 'Admins'],
        datasets: [{
          data: [<?= $custCnt ?>, <?= $vendCnt ?>, 1],
          backgroundColor: ['#2563EB', '#10B981', '#F59E0B']
        }]
      },
      options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
    });
  }
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
