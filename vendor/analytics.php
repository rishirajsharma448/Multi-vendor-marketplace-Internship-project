<?php
$pageTitle = "Store Analytics & Product Performance - Vyapar Setu Vendor";
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

requireRole('vendor');
$user = getCurrentUser();
$vendor = $user['vendor'] ?? null;
$vendor_id = $vendor['id'] ?? 1;
$db = getDBConnection();

// Fetch Detailed Product Performance Metrics
$perfStmt = $db->prepare("
    SELECT 
        p.id,
        p.sku,
        p.title,
        p.price,
        p.stock,
        pi.image_url,
        COALESCE(SUM(oi.quantity), 0) AS total_units_sold,
        COALESCE(SUM(oi.price * oi.quantity), 0) AS total_revenue_generated
    FROM products p
    LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_primary = 1
    LEFT JOIN order_items oi ON p.id = oi.product_id
    WHERE p.vendor_id = ?
    GROUP BY p.id
    ORDER BY total_revenue_generated DESC
");
$perfStmt->execute([$vendor_id]);
$productsAnalytics = $perfStmt->fetchAll();

// Data for charts
$labels = [];
$revenueData = [];
$unitsData = [];

foreach ($productsAnalytics as $p) {
    $labels[] = mb_strimwidth($p['title'], 0, 18, '...');
    $revenueData[] = (float)$p['total_revenue_generated'];
    $unitsData[] = (int)$p['total_units_sold'];
}
?>

<?php include __DIR__ . '/../includes/navbar.php'; ?>

<div class="dashboard-wrapper">
  <?php include __DIR__ . '/../includes/sidebar.php'; ?>

  <div class="dashboard-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <div>
        <h3 class="fw-bold mb-1"><i class="bi bi-graph-up-arrow text-primary me-2"></i> Product Performance & Revenue Analytics</h3>
        <p class="text-muted small mb-0">Detailed breakdown of top performing products and gross payment revenues earned.</p>
      </div>
      <a href="dashboard.php" class="btn btn-outline-primary btn-sm"><i class="bi bi-speedometer2 me-1"></i> Back to Dashboard</a>
    </div>

    <?php displayAlerts(); ?>

    <div class="row g-4 mb-4">
      <!-- Revenue Breakdown Bar Chart -->
      <div class="col-lg-7">
        <div class="card card-custom p-4 border-0 shadow-sm">
          <h5 class="fw-bold mb-3"><i class="bi bi-bar-chart-line text-success me-2"></i> Revenue Generated Per Product (₹)</h5>
          <canvas id="productRevenueChart" height="230"></canvas>
        </div>
      </div>

      <!-- Units Sold Doughnut Chart -->
      <div class="col-lg-5">
        <div class="card card-custom p-4 border-0 shadow-sm">
          <h5 class="fw-bold mb-3"><i class="bi bi-pie-chart text-primary me-2"></i> Units Sold Distribution</h5>
          <canvas id="productUnitsChart" height="230"></canvas>
        </div>
      </div>
    </div>

    <!-- Product Performance Breakdown Table -->
    <div class="card card-custom border-0 shadow-sm">
      <div class="card-header bg-white py-3 border-0">
        <h5 class="fw-bold mb-0"><i class="bi bi-table text-primary me-2"></i> Product Sales & Revenue Matrix</h5>
      </div>
      <div class="table-responsive">
        <table class="table table-custom align-middle mb-0">
          <thead>
            <tr>
              <th>Product Name</th>
              <th>SKU</th>
              <th>Unit Price</th>
              <th>Units Sold</th>
              <th>Gross Revenue</th>
              <th>Stock Status</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($productsAnalytics as $p): ?>
              <tr>
                <td class="fw-bold text-dark"><?= sanitize($p['title']) ?></td>
                <td><code><?= sanitize($p['sku']) ?></code></td>
                <td><?= formatCurrency($p['price']) ?></td>
                <td class="fw-bold"><?= (int)$p['total_units_sold'] ?> units</td>
                <td class="fw-bold text-success fs-6"><?= formatCurrency($p['total_revenue_generated']) ?></td>
                <td>
                  <?php if ($p['stock'] > 10): ?>
                    <span class="badge bg-success-subtle text-success"><?= (int)$p['stock'] ?> in stock</span>
                  <?php elseif ($p['stock'] > 0): ?>
                    <span class="badge bg-warning-subtle text-warning"><?= (int)$p['stock'] ?> low stock</span>
                  <?php else: ?>
                    <span class="badge bg-danger-subtle text-danger">Out of stock</span>
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

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
  const labels = <?= json_encode($labels) ?>;
  const revenueData = <?= json_encode($revenueData) ?>;
  const unitsData = <?= json_encode($unitsData) ?>;

  const revCtx = document.getElementById('productRevenueChart');
  if (revCtx) {
    new Chart(revCtx, {
      type: 'bar',
      data: {
        labels: labels,
        datasets: [{
          label: 'Total Revenue (₹)',
          data: revenueData,
          backgroundColor: 'rgba(16, 185, 129, 0.7)',
          borderColor: '#10B981',
          borderWidth: 1,
          borderRadius: 6
        }]
      },
      options: { responsive: true, plugins: { legend: { display: false } } }
    });
  }

  const unitsCtx = document.getElementById('productUnitsChart');
  if (unitsCtx) {
    new Chart(unitsCtx, {
      type: 'doughnut',
      data: {
        labels: labels,
        datasets: [{
          data: unitsData,
          backgroundColor: ['#2563EB', '#10B981', '#F59E0B', '#8B5CF6', '#EF4444', '#64748B']
        }]
      },
      options: { responsive: true }
    });
  }
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
