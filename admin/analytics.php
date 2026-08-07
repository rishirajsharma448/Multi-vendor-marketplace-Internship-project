<?php
$pageTitle = "Analytics & Reports - Vyapar Setu Admin";
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

requireRole('admin');
?>

<?php include __DIR__ . '/../includes/navbar.php'; ?>

<div class="dashboard-wrapper">
  <?php include __DIR__ . '/../includes/sidebar.php'; ?>

  <div class="dashboard-content">
    <h3 class="fw-bold mb-4"><i class="bi bi-graph-up-arrow text-primary me-2"></i> Sales Analytics & BI Reports</h3>

    <div class="row g-4">
      <div class="col-lg-6">
        <div class="card card-custom p-4 border-0 shadow-sm">
          <h5 class="fw-bold mb-3">Revenue Projection</h5>
          <canvas id="analyticsChart1" height="200"></canvas>
        </div>
      </div>
      <div class="col-lg-6">
        <div class="card card-custom p-4 border-0 shadow-sm">
          <h5 class="fw-bold mb-3">Order Volume</h5>
          <canvas id="analyticsChart2" height="200"></canvas>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  new Chart(document.getElementById('analyticsChart1'), {
    type: 'line',
    data: { labels: ['Q1', 'Q2', 'Q3', 'Q4'], datasets: [{ label: 'Sales (₹)', data: [150000, 280000, 420000, 600000], borderColor: '#2563EB', fill: false }] }
  });
  new Chart(document.getElementById('analyticsChart2'), {
    type: 'bar',
    data: { labels: ['Q1', 'Q2', 'Q3', 'Q4'], datasets: [{ label: 'Orders', data: [450, 780, 1120, 1500], backgroundColor: '#10B981' }] }
  });
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
