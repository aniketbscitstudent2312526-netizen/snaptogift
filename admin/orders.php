<?php
$page_title = 'Orders Management';
require_once 'includes/header.php';

// Handle status update
if (isset($_GET['status']) && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $status = sanitize($_GET['status']);
    $db->query("UPDATE orders SET status = '$status' WHERE id = $id");
    showAlert('Order status updated', 'success');
    redirect('orders.php');
}

// Get all orders
$orders = $db->query("SELECT o.*, u.name as user_name, u.email as user_email FROM orders o JOIN users u ON o.user_id = u.id ORDER BY o.created_at DESC");
?>

<div class="table-card">
    <div class="card-header bg-white p-4 d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="bi bi-cart3 me-2"></i>All Orders</h5>
        <div class="d-flex gap-2">
            <span class="badge bg-warning">Pending: <?php echo $db->query("SELECT COUNT(*) as c FROM orders WHERE status='pending'")->fetch_assoc()['c']; ?></span>
            <span class="badge bg-info">Shipped: <?php echo $db->query("SELECT COUNT(*) as c FROM orders WHERE status='shipped'")->fetch_assoc()['c']; ?></span>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Order #</th>
                    <th>Customer</th>
                    <th>Items</th>
                    <th>Total</th>
                    <th>Payment</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($order = $orders->fetch_assoc()): 
                    // Get items count
                    $items_count = $db->query("SELECT COUNT(*) as c FROM order_items WHERE order_id = {$order['id']}")->fetch_assoc()['c'];
                ?>
                <tr>
                    <td><strong><?php echo $order['order_number']; ?></strong></td>
                    <td>
                        <div><?php echo $order['user_name']; ?></div>
                        <small class="text-muted"><?php echo $order['user_email']; ?></small>
                    </td>
                    <td><?php echo $items_count; ?> items</td>
                    <td><strong>₹<?php echo number_format($order['total_amount'], 2); ?></strong></td>
                    <td><?php echo str_replace('_', ' ', ucfirst($order['payment_method'])); ?></td>
                    <td>
                        <span class="badge-status <?php echo getOrderStatusBadge($order['status']); ?> text-white">
                            <i class="bi bi-<?php 
                                $icons = ['pending' => 'clock', 'confirmed' => 'check-circle', 'processing' => 'gear', 'packed' => 'box-seam', 
                                         'shipped' => 'truck', 'in_transit' => 'geo-alt', 'out_for_delivery' => 'bicycle', 
                                         'delivered' => 'check-circle-fill', 'cancelled' => 'x-circle', 'returned' => 'arrow-return-left'];
                                echo $icons[$order['status']] ?? 'circle';
                            ?> me-1"></i>
                            <?php echo ucfirst(str_replace('_', ' ', $order['status'])); ?>
                        </span>
                    </td>
                    <td><?php echo date('M d, Y H:i', strtotime($order['created_at'])); ?></td>
                    <td>
                        <a href="order_detail.php?id=<?php echo $order['id']; ?>" class="btn btn-sm btn-primary">
                            <i class="bi bi-eye me-1"></i>View
                        </a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
