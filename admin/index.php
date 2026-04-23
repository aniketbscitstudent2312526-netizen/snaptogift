<?php
$page_title = 'Dashboard';
require_once 'includes/header.php';

// Get dashboard statistics
$stats = [
    'users' => getTotalUsers(),
    'orders' => getTotalOrders(),
    'revenue' => getTotalRevenue(),
    'products' => $db->query("SELECT COUNT(*) as total FROM products")->fetch_assoc()['total']
];

// Get recent orders
$recent_orders = $db->query("SELECT o.*, u.name as user_name FROM orders o JOIN users u ON o.user_id = u.id ORDER BY o.created_at DESC LIMIT 5");

// Get top products
$top_products = getTopProducts(5);

// Get monthly revenue data
$monthly_revenue = $db->query("SELECT DATE_FORMAT(created_at, '%Y-%m') as month, SUM(total_amount) as total FROM orders WHERE status != 'cancelled' GROUP BY month ORDER BY month DESC LIMIT 6");
?>

<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="stats-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <p class="text-muted mb-1">Total Users</p>
                    <h3 class="mb-0"><?php echo number_format($stats['users']); ?></h3>
                </div>
                <div class="stats-icon bg-primary bg-opacity-10 text-primary">
                    <i class="bi bi-people"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stats-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <p class="text-muted mb-1">Total Orders</p>
                    <h3 class="mb-0"><?php echo number_format($stats['orders']); ?></h3>
                </div>
                <div class="stats-icon bg-success bg-opacity-10 text-success">
                    <i class="bi bi-cart3"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stats-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <p class="text-muted mb-1">Revenue</p>
                    <h3 class="mb-0">₹<?php echo number_format($stats['revenue']); ?></h3>
                </div>
                <div class="stats-icon bg-warning bg-opacity-10 text-warning">
                    <i class="bi bi-currency-rupee"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stats-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <p class="text-muted mb-1">Products</p>
                    <h3 class="mb-0"><?php echo number_format($stats['products']); ?></h3>
                </div>
                <div class="stats-icon bg-info bg-opacity-10 text-info">
                    <i class="bi bi-box-seam"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Recent Orders -->
    <div class="col-lg-8">
        <div class="table-card">
            <div class="card-header bg-white p-4 border-bottom">
                <h5 class="mb-0"><i class="bi bi-clock-history me-2"></i>Recent Orders</h5>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Order #</th>
                            <th>Customer</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($order = $recent_orders->fetch_assoc()): ?>
                        <tr>
                            <td><a href="orders.php?view=<?php echo $order['id']; ?>" class="text-primary text-decoration-none"><?php echo $order['order_number']; ?></a></td>
                            <td><?php echo $order['user_name']; ?></td>
                            <td>₹<?php echo number_format($order['total_amount'], 2); ?></td>
                            <td>
                                <span class="badge-status bg-<?php 
                                    echo $order['status'] == 'delivered' ? 'success' : 
                                         ($order['status'] == 'cancelled' ? 'danger' : 
                                         ($order['status'] == 'shipped' ? 'info' : 'warning')); 
                                ?>">
                                    <?php echo ucfirst($order['status']); ?>
                                </span>
                            </td>
                            <td><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Top Products -->
    <div class="col-lg-4">
        <div class="table-card">
            <div class="card-header bg-white p-4 border-bottom">
                <h5 class="mb-0"><i class="bi bi-trophy me-2"></i>Top Products</h5>
            </div>
            <div class="list-group list-group-flush">
                <?php foreach ($top_products as $i => $product): ?>
                <div class="list-group-item d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <span class="badge bg-<?php echo $i < 3 ? 'warning' : 'secondary'; ?> me-2">#<?php echo $i + 1; ?></span>
                        <span class="text-truncate" style="max-width: 150px;"><?php echo $product['name']; ?></span>
                    </div>
                    <span class="text-muted"><?php echo $product['purchases']; ?> sales</span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<!-- Monthly Revenue Chart (Simple Table) -->
<div class="row mt-4">
    <div class="col-12">
        <div class="table-card p-4">
            <h5 class="mb-3"><i class="bi bi-graph-up me-2"></i>Monthly Revenue</h5>
            <div class="table-responsive">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Month</th>
                            <th>Revenue</th>
                            <th>Progress</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $max_revenue = 0;
                        $revenue_data = [];
                        while ($row = $monthly_revenue->fetch_assoc()) {
                            $revenue_data[] = $row;
                            $max_revenue = max($max_revenue, $row['total']);
                        }
                        foreach ($revenue_data as $row):
                            $percentage = $max_revenue > 0 ? ($row['total'] / $max_revenue) * 100 : 0;
                        ?>
                        <tr>
                            <td><?php echo date('F Y', strtotime($row['month'] . '-01')); ?></td>
                            <td>₹<?php echo number_format($row['total'], 2); ?></td>
                            <td style="width: 50%;">
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar bg-primary" style="width: <?php echo $percentage; ?>"></div>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
