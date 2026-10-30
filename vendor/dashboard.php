<?php
$pageTitle = "Vendor Dashboard & Performance Analytics - Vyapar Setu";
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

requireRole('vendor');
$user = getCurrentUser();
$vendor = $user['vendor'] ?? null;
$db = getDBConnection();

$vendor_id = $vendor['id'] ?? 1;

// 1. Fetch Overall Lifetime Store Metrics
$prodCount = $db->prepare("SELECT COUNT(*) AS cnt FROM products WHERE vendor_id = ?");
$prodCount->execute([$vendor_id]);
$totalProducts = $prodCount->fetch()['cnt'] ?? 0;

$ordCount = $db->prepare("SELECT COUNT(DISTINCT order_id) AS cnt FROM order_items WHERE vendor_id = ?");
$ordCount->execute([$vendor_id]);
$totalOrders = $ordCount->fetch()['cnt'] ?? 0;

$revStmt = $db->prepare("SELECT COALESCE(SUM(price * quantity), 0) AS total FROM order_items WHERE vendor_id = ?");
$revStmt->execute([$vendor_id]);
$totalRevenue = $revStmt->fetch()['total'] ?? 0;

$pendingCount = $db->prepare("SELECT COUNT(*) AS cnt FROM order_items WHERE vendor_id = ? AND status = 'Pending'");
$pendingCount->execute([$vendor_id]);
$pendingOrders = $pendingCount->fetch()['cnt'] ?? 0;

// 2. Time-frame Filter Logic for Monthly Earnings Section
$period = sanitize($_GET['period'] ?? '6m');
$startDateInput = sanitize($_GET['start_date'] ?? '');
$endDateInput = sanitize($_GET['end_date'] ?? '');

$currentDate = date('Y-m-d');
if ($period === '1m') {
    $calcStart = date('Y-m-d', strtotime('-30 days'));
    $calcEnd = $currentDate;
    $periodLabel = "1 Month (Last 30 Days)";
} elseif ($period === '3m') {
    $calcStart = date('Y-m-d', strtotime('-90 days'));
    $calcEnd = $currentDate;
    $periodLabel = "3 Months (Last 90 Days)";
} elseif ($period === '1y') {
    $calcStart = date('Y-m-d', strtotime('-1 year'));
    $calcEnd = $currentDate;
    $periodLabel = "1 Year (Past 12 Months)";
} elseif ($period === 'custom' && !empty($startDateInput) && !empty($endDateInput)) {
    $calcStart = $startDateInput;
    $calcEnd = $endDateInput;
    $periodLabel = "Custom Range (" . date('d M Y', strtotime($startDateInput)) . " - " . date('d M Y', strtotime($endDateInput)) . ")";
} else {
    $period = '6m';
    $calcStart = date('Y-m-d', strtotime('-180 days'));
    $calcEnd = $currentDate;
    $periodLabel = "6 Months (Past 180 Days)";
}

// Query Earnings in Period from database
$earningsQuery = "
    SELECT 
        COALESCE(SUM(oi.price * oi.quantity), 0) AS gross_revenue,
        COUNT(DISTINCT oi.order_id) AS total_orders,
        COALESCE(SUM(oi.quantity), 0) AS total_items_sold
    FROM order_items oi
    JOIN orders o ON oi.order_id = o.id
    WHERE oi.vendor_id = ?
      AND DATE(o.created_at) >= ?
      AND DATE(o.created_at) <= ?
";
$eStmt = $db->prepare($earningsQuery);
$eStmt->execute([$vendor_id, $calcStart, $calcEnd]);
$periodData = $eStmt->fetch();

$periodGross = (float)($periodData['gross_revenue'] ?? 0);
$periodOrders = (int)($periodData['total_orders'] ?? 0);

// Query Raw Order Timelines for Monthly Charting
$rawOrdersStmt = $db->prepare("
    SELECT oi.price, oi.quantity, o.created_at
    FROM order_items oi
    JOIN orders o ON oi.order_id = o.id
    WHERE oi.vendor_id = ?
      AND DATE(o.created_at) >= ?
      AND DATE(o.created_at) <= ?
    ORDER BY o.created_at ASC
");
$rawOrdersStmt->execute([$vendor_id, $calcStart, $calcEnd]);
$rawOrders = $rawOrdersStmt->fetchAll();

// Group into monthly buckets
$monthlyBuckets = [];
foreach ($rawOrders as $ro) {
    $mKey = date('M Y', strtotime($ro['created_at']));
    if (!isset($monthlyBuckets[$mKey])) {
        $monthlyBuckets[$mKey] = 0;
    }
    $monthlyBuckets[$mKey] += ($ro['price'] * $ro['quantity']);
}

$chartLabels = array_keys($monthlyBuckets);
$chartValues = array_values($monthlyBuckets);

if (empty($chartLabels)) {
    $chartLabels = ['No sales yet'];
    $chartValues = [0];
}

$periodNet = $periodGross * 0.90; // 90% net payout after 10% platform fee
$avgOrderValue = $periodOrders > 0 ? ($periodGross / $periodOrders) : 0;

// 3. Fetch Detailed Product Performance Metrics
$perfStmt = $db->prepare("
    SELECT 
        p.id,
        p.sku,
        p.title,
        p.price,
        p.stock,
        p.status,
        p.rating,
        pi.image_url,
        COALESCE(SUM(oi.quantity), 0) AS total_units_sold,
        COALESCE(SUM(oi.price * oi.quantity), 0) AS total_revenue_generated,
        COUNT(DISTINCT oi.order_id) AS total_orders_count
    FROM products p
    LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_primary = 1
    LEFT JOIN order_items oi ON p.id = oi.product_id
    WHERE p.vendor_id = ?
    GROUP BY p.id
    ORDER BY total_revenue_generated DESC, total_units_sold DESC
");
$perfStmt->execute([$vendor_id]);
$productPerformance = $perfStmt->fetchAll();

// Top Bestseller and Top Revenue Product
$topRevenueProduct = !empty($productPerformance) ? $productPerformance[0] : null;

$bestsellerList = $productPerformance;
usort($bestsellerList, function($a, $b) {
    return $b['total_units_sold'] - $a['total_units_sold'];
});
$topBestsellerProduct = !empty($bestsellerList) ? $bestsellerList[0] : null;

// 4. Fetch Recent Vendor Orders
$ordersStmt = $db->prepare("
    SELECT oi.*, o.order_number, o.customer_name, o.created_at AS order_date
    FROM order_items oi
    JOIN orders o ON oi.order_id = o.id
    WHERE oi.vendor_id = ?
    ORDER BY oi.id DESC LIMIT 5
");
$ordersStmt->execute([$vendor_id]);
$recentOrders = $ordersStmt->fetchAll();
?>

<?php include __DIR__ . '/../includes/navbar.php'; ?>

<div class="dashboard-wrapper">
  <?php include __DIR__ . '/../includes/sidebar.php'; ?>

  <div class="dashboard-content">
    <!-- Header Banner -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
      <div>
        <h3 class="fw-bold mb-1"><i class="bi bi-speedometer2 text-primary me-2"></i> <?= sanitize($vendor['store_name'] ?? 'Vendor Console') ?></h3>
        <p class="text-muted small mb-0">Store Code: <code><?= sanitize($vendor['vendor_code'] ?? 'VND-000') ?></code> | Rating: ⭐ <?= number_format($vendor['rating'] ?? 5.0, 1) ?> | Seller Tier: <strong>Verified SME</strong></p>
      </div>
      <div class="d-flex gap-2">
        <a href="add-product.php" class="btn btn-primary-custom btn-sm"><i class="bi bi-plus-circle me-1"></i> Add Product (File/URL Upload)</a>
        <a href="profile.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-person-gear me-1"></i> Account Profile</a>
      </div>
    </div>

    <?php displayAlerts(); ?>

    <!-- Overview Metric Cards -->
    <div class="row g-3 mb-4">
      <div class="col-6 col-md-3">
        <div class="stat-card">
          <div class="stat-icon-wrapper bg-primary-light text-primary">
            <i class="bi bi-boxes fs-4"></i>
          </div>
          <div>
            <h4 class="fw-bold mb-0"><?= $totalProducts ?></h4>
            <small class="text-muted fw-500">Active Products</small>
          </div>
        </div>
      </div>

      <div class="col-6 col-md-3">
        <div class="stat-card">
          <div class="stat-icon-wrapper bg-success-subtle text-success">
            <i class="bi bi-bag-check-fill fs-4"></i>
          </div>
          <div>
            <h4 class="fw-bold mb-0"><?= $totalOrders ?></h4>
            <small class="text-muted fw-500">Total Store Orders</small>
          </div>
        </div>
      </div>

      <div class="col-6 col-md-3">
        <div class="stat-card">
          <div class="stat-icon-wrapper bg-warning-subtle text-warning">
            <i class="bi bi-currency-rupee fs-4"></i>
          </div>
          <div>
            <h4 class="fw-bold mb-0"><?= formatCurrency($totalRevenue) ?></h4>
            <small class="text-muted fw-500">Gross Sales Revenue</small>
          </div>
        </div>
      </div>

      <div class="col-6 col-md-3">
        <div class="stat-card">
          <div class="stat-icon-wrapper bg-danger-subtle text-danger">
            <i class="bi bi-clock-history fs-4"></i>
          </div>
          <div>
            <h4 class="fw-bold mb-0"><?= $pendingOrders ?></h4>
            <small class="text-muted fw-500">Pending Fulfillment</small>
          </div>
        </div>
      </div>
    </div>

    <!-- ======================================================== -->
    <!-- MONTHLY EARNINGS & REVENUE TIMELINE ANALYTICS SECTION   -->
    <!-- ======================================================== -->
    <div class="card card-custom border-0 shadow-sm mb-4" id="earnings-section">
      <div class="card-header bg-white py-3 border-0">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
          <div>
            <h5 class="fw-bold mb-1"><i class="bi bi-cash-stack text-success me-2"></i> Monthly Earnings & Sales Revenue Analytics</h5>
            <small class="text-muted">Filtered Period: <strong class="text-primary"><?= $periodLabel ?></strong></small>
          </div>

          <!-- Time-frame Filter Pills -->
          <div class="d-flex flex-wrap gap-1 align-items-center">
            <a href="dashboard.php?period=1m#earnings-section" class="btn btn-sm <?= $period === '1m' ? 'btn-primary' : 'btn-outline-secondary' ?>">1 Month</a>
            <a href="dashboard.php?period=3m#earnings-section" class="btn btn-sm <?= $period === '3m' ? 'btn-primary' : 'btn-outline-secondary' ?>">3 Months</a>
            <a href="dashboard.php?period=6m#earnings-section" class="btn btn-sm <?= $period === '6m' ? 'btn-primary' : 'btn-outline-secondary' ?>">6 Months</a>
            <a href="dashboard.php?period=1y#earnings-section" class="btn btn-sm <?= $period === '1y' ? 'btn-primary' : 'btn-outline-secondary' ?>">1 Year</a>
            <button type="button" class="btn btn-sm <?= $period === 'custom' ? 'btn-primary' : 'btn-outline-secondary' ?>" data-bs-toggle="collapse" data-bs-target="#customDateForm">
              <i class="bi bi-calendar3 me-1"></i> Custom Range
            </button>
          </div>
        </div>

        <!-- Custom Date Range Collapse Form -->
        <div class="collapse <?= $period === 'custom' ? 'show' : '' ?> mt-3" id="customDateForm">
          <form action="dashboard.php" method="GET" class="row g-2 align-items-end p-3 bg-light rounded border">
            <input type="hidden" name="period" value="custom">
            <div class="col-sm-5 col-md-4">
              <label class="form-label small fw-500 mb-1">Start Date</label>
              <input type="date" name="start_date" class="form-control form-control-sm" value="<?= sanitize($startDateInput) ?>" required>
            </div>
            <div class="col-sm-5 col-md-4">
              <label class="form-label small fw-500 mb-1">End Date</label>
              <input type="date" name="end_date" class="form-control form-control-sm" value="<?= sanitize($endDateInput) ?>" required>
            </div>
            <div class="col-sm-2 col-md-4">
              <button type="submit" class="btn btn-primary btn-sm px-3"><i class="bi bi-filter me-1"></i> Apply Filter</button>
            </div>
          </form>
        </div>
      </div>

      <div class="card-body pt-0">
        <!-- Period Summary Stat Badges -->
        <div class="row g-3 mb-4">
          <div class="col-sm-6 col-lg-3">
            <div class="p-3 border rounded bg-light text-center">
              <span class="text-muted small d-block">Gross Earnings (Period)</span>
              <strong class="fs-5 text-dark"><?= formatCurrency($periodGross) ?></strong>
            </div>
          </div>

          <div class="col-sm-6 col-lg-3">
            <div class="p-3 border rounded bg-success-subtle text-center">
              <span class="text-success small d-block">Net Seller Payout (90%)</span>
              <strong class="fs-5 text-success"><?= formatCurrency($periodNet) ?></strong>
            </div>
          </div>

          <div class="col-sm-6 col-lg-3">
            <div class="p-3 border rounded bg-light text-center">
              <span class="text-muted small d-block">Orders Completed</span>
              <strong class="fs-5 text-dark"><?= $periodOrders ?> orders</strong>
            </div>
          </div>

          <div class="col-sm-6 col-lg-3">
            <div class="p-3 border rounded bg-light text-center">
              <span class="text-muted small d-block">Avg Order Value (AOV)</span>
              <strong class="fs-5 text-primary"><?= formatCurrency($avgOrderValue) ?></strong>
            </div>
          </div>
        </div>

        <!-- Monthly Revenue Chart -->
        <div class="chart-container" style="position: relative; height: 260px;">
          <canvas id="vendorPeriodEarningsChart"></canvas>
        </div>
      </div>
    </div>

    <!-- Product Performance Highlights Cards -->
    <div class="row g-3 mb-4">
      <!-- Highest Revenue Generating Product -->
      <div class="col-md-6">
        <div class="card card-custom p-3 border-0 shadow-sm bg-primary-light h-100">
          <div class="d-flex align-items-center justify-content-between mb-2">
            <span class="badge bg-primary text-uppercase px-2 py-1"><i class="bi bi-trophy-fill me-1"></i> Highest Revenue Product</span>
            <small class="text-primary fw-bold">Top Earner</small>
          </div>
          <?php if ($topRevenueProduct && $topRevenueProduct['total_revenue_generated'] > 0): ?>
            <div class="d-flex align-items-center gap-3">
              <img src="<?= (strpos($topRevenueProduct['image_url'] ?? '', 'http') === 0) ? sanitize($topRevenueProduct['image_url']) : APP_URL . '/' . sanitize($topRevenueProduct['image_url'] ?? 'assets/images/default-product.png') ?>" alt="Product" class="rounded border" width="60" height="60" style="object-fit: contain; background: #f8fafc;">
              <div class="flex-grow-1">
                <h6 class="fw-bold mb-1 text-dark text-truncate" style="max-width: 250px;"><?= sanitize($topRevenueProduct['title']) ?></h6>
                <div class="small text-muted">
                  Revenue Generated: <strong class="text-success fs-6"><?= formatCurrency($topRevenueProduct['total_revenue_generated']) ?></strong>
                </div>
                <div class="small text-muted">
                  Units Sold: <strong><?= (int)$topRevenueProduct['total_units_sold'] ?> items</strong> (<?= (int)$topRevenueProduct['total_orders_count'] ?> orders)
                </div>
              </div>
            </div>
          <?php else: ?>
            <p class="text-muted small mb-0 py-2">No product revenue recorded yet. As orders come in, your top revenue product will appear here.</p>
          <?php endif; ?>
        </div>
      </div>

      <!-- Most Popular Bestseller Product -->
      <div class="col-md-6">
        <div class="card card-custom p-3 border-0 shadow-sm bg-warning-subtle h-100">
          <div class="d-flex align-items-center justify-content-between mb-2">
            <span class="badge bg-warning text-dark text-uppercase px-2 py-1"><i class="bi bi-fire me-1"></i> Top Bestseller Item</span>
            <small class="text-dark fw-bold">Most Units Sold</small>
          </div>
          <?php if ($topBestsellerProduct && $topBestsellerProduct['total_units_sold'] > 0): ?>
            <div class="d-flex align-items-center gap-3">
              <img src="<?= (strpos($topBestsellerProduct['image_url'] ?? '', 'http') === 0) ? sanitize($topBestsellerProduct['image_url']) : APP_URL . '/' . sanitize($topBestsellerProduct['image_url'] ?? 'assets/images/default-product.png') ?>" alt="Product" class="rounded border" width="60" height="60" style="object-fit: contain; background: #f8fafc;">
              <div class="flex-grow-1">
                <h6 class="fw-bold mb-1 text-dark text-truncate" style="max-width: 250px;"><?= sanitize($topBestsellerProduct['title']) ?></h6>
                <div class="small text-muted">
                  Volume Sales: <strong class="text-dark fs-6"><?= (int)$topBestsellerProduct['total_units_sold'] ?> units sold</strong>
                </div>
                <div class="small text-muted">
                  Total Value: <strong><?= formatCurrency($topBestsellerProduct['total_revenue_generated']) ?></strong>
                </div>
              </div>
            </div>
          <?php else: ?>
            <p class="text-muted small mb-0 py-2">No sales volume recorded yet. As customers order items, your most popular bestseller will appear here.</p>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- Product Performance Analytics Table -->
    <div class="card card-custom border-0 shadow-sm mb-4">
      <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
          <h5 class="fw-bold mb-0"><i class="bi bi-graph-up-arrow text-primary me-2"></i> Product Performance & Revenue Analytics</h5>
          <small class="text-muted">Track which products are performing best and driving maximum sales & payments.</small>
        </div>
        <a href="products.php" class="btn btn-sm btn-outline-primary fw-600">View Full Inventory</a>
      </div>
      <div class="table-responsive">
        <table class="table table-custom align-middle mb-0">
          <thead>
            <tr>
              <th>Product Details</th>
              <th>SKU</th>
              <th>Unit Price</th>
              <th>Units Sold</th>
              <th>Total Revenue</th>
              <th>Stock Status</th>
              <th>Performance Rank</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($productPerformance)): ?>
              <tr>
                <td colspan="7" class="text-center py-4 text-muted">No products found in your store inventory.</td>
              </tr>
            <?php else: ?>
              <?php foreach ($productPerformance as $index => $prod): ?>
                <tr>
                  <td>
                    <div class="d-flex align-items-center gap-2">
                      <img src="<?= (strpos($prod['image_url'] ?? '', 'http') === 0) ? sanitize($prod['image_url']) : APP_URL . '/' . sanitize($prod['image_url'] ?? 'assets/images/default-product.png') ?>" class="rounded border" width="40" height="40" style="object-fit: contain; background: #f8fafc;">
                      <div>
                        <span class="fw-bold text-dark d-block text-truncate" style="max-width: 220px;"><?= sanitize($prod['title']) ?></span>
                        <small class="text-muted">⭐ <?= number_format($prod['rating'] ?? 5.0, 1) ?></small>
                      </div>
                    </div>
                  </td>
                  <td><code><?= sanitize($prod['sku']) ?></code></td>
                  <td class="fw-bold"><?= formatCurrency($prod['price']) ?></td>
                  <td>
                    <span class="fw-bold text-dark"><?= (int)$prod['total_units_sold'] ?></span>
                    <small class="text-muted d-block"><?= (int)$prod['total_orders_count'] ?> orders</small>
                  </td>
                  <td>
                    <strong class="text-success fs-6"><?= formatCurrency($prod['total_revenue_generated']) ?></strong>
                  </td>
                  <td>
                    <?php if ($prod['stock'] > 10): ?>
                      <span class="badge bg-success-subtle text-success"><?= (int)$prod['stock'] ?> in stock</span>
                    <?php elseif ($prod['stock'] > 0): ?>
                      <span class="badge bg-warning-subtle text-warning"><?= (int)$prod['stock'] ?> low stock</span>
                    <?php else: ?>
                      <span class="badge bg-danger-subtle text-danger">Out of stock</span>
                    <?php endif; ?>
                  </td>
                  <td>
                    <?php if ($index === 0 && $prod['total_revenue_generated'] > 0): ?>
                      <span class="badge bg-success text-uppercase"><i class="bi bi-trophy-fill me-1"></i> #1 Top Earner</span>
                    <?php elseif ($prod['total_units_sold'] > 5): ?>
                      <span class="badge bg-warning text-dark text-uppercase"><i class="bi bi-fire me-1"></i> High Performer</span>
                    <?php elseif ($prod['total_units_sold'] > 0): ?>
                      <span class="badge bg-info text-dark text-uppercase"><i class="bi bi-graph-up me-1"></i> Active Sales</span>
                    <?php else: ?>
                      <span class="badge bg-secondary text-uppercase">New Listing</span>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Recent Orders Section -->
    <div class="card card-custom border-0 shadow-sm">
      <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
        <h5 class="fw-bold mb-0"><i class="bi bi-receipt me-2 text-primary"></i> Recent Incoming Orders</h5>
        <a href="orders.php" class="btn btn-sm btn-link text-primary text-decoration-none fw-600">View All Orders</a>
      </div>
      <div class="table-responsive">
        <table class="table table-custom mb-0">
          <thead>
            <tr>
              <th>Order Ref</th>
              <th>Customer</th>
              <th>Product Title</th>
              <th>Qty</th>
              <th>Total Amount</th>
              <th>Order Status</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($recentOrders)): ?>
              <tr>
                <td colspan="6" class="text-center py-4 text-muted">No orders for your store yet.</td>
              </tr>
            <?php else: ?>
              <?php foreach ($recentOrders as $ord): ?>
                <tr>
                  <td class="fw-bold text-primary"><?= sanitize($ord['order_number']) ?></td>
                  <td><?= sanitize($ord['customer_name']) ?></td>
                  <td><?= sanitize($ord['product_title']) ?></td>
                  <td><?= (int)$ord['quantity'] ?></td>
                  <td class="fw-bold text-success"><?= formatCurrency($ord['price'] * $ord['quantity']) ?></td>
                  <td><span class="badge <?= getStatusBadgeClass($ord['status']) ?>"><?= sanitize($ord['status']) ?></span></td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
  const chartLabels = <?= json_encode($chartLabels) ?>;
  const chartValues = <?= json_encode($chartValues) ?>;

  const ctx = document.getElementById('vendorPeriodEarningsChart');
  if (ctx) {
    new Chart(ctx, {
      type: 'line',
      data: {
        labels: chartLabels,
        datasets: [{
          label: 'Revenue (₹)',
          data: chartValues,
          borderColor: '#10B981',
          backgroundColor: 'rgba(16, 185, 129, 0.15)',
          fill: true,
          tension: 0.35,
          borderWidth: 3,
          pointBackgroundColor: '#10B981',
          pointRadius: 6,
          pointHoverRadius: 8
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { display: false },
          tooltip: {
            callbacks: {
              label: function(context) {
                return ' Revenue: ₹' + context.raw.toLocaleString();
              }
            }
          }
        },
        scales: {
          y: {
            beginAtZero: true,
            ticks: {
              callback: function(value) {
                return '₹' + value.toLocaleString();
              }
            }
          }
        }
      }
    });
  }
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
