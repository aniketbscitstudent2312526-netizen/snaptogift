<?php
require_once 'config.php';
requireLogin();

// Get user's orders
$stmt = $db->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$page_title = 'My Orders';
include 'includes/header.php';
?>

<div class="container py-5">
    <h2 class="mb-4"><i class="bi bi-bag me-2"></i>My Orders</h2>
    
    <?php if (isset($_GET['success'])): ?>
    <div class="alert alert-success">
        <i class="bi bi-check-circle me-2"></i>Your order has been placed successfully! Thank you for shopping with us.
    </div>
    <?php endif; ?>
    
    <?php if (empty($orders)): ?>
    <div class="text-center py-5">
        <i class="bi bi-bag-x display-1 text-muted"></i>
        <h3 class="mt-3">No orders yet</h3>
        <p class="text-muted">You haven't placed any orders yet</p>
        <a href="products.php" class="btn btn-primary btn-lg">
            <i class="bi bi-shop me-2"></i>Start Shopping
        </a>
    </div>
    <?php else: ?>
    <div class="row">
        <?php foreach ($orders as $order): ?>
        <div class="col-12 mb-4">
            <div class="card">
                <div class="card-header bg-light d-flex justify-content-between align-items-center flex-wrap">
                    <div>
                        <strong>Order #<?php echo $order['order_number']; ?></strong>
                        <span class="text-muted ms-3"><i class="bi bi-calendar me-1"></i><?php echo date('M d, Y', strtotime($order['created_at'])); ?></span>
                        <?php if ($order['coupon_code']): ?>
                        <span class="badge bg-success ms-2"><i class="bi bi-ticket-perforated me-1"></i><?php echo $order['coupon_code']; ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="d-flex align-items-center gap-2 mt-2 mt-md-0">
                        <span class="badge <?php echo getOrderStatusBadge($order['status']); ?> fs-6">
                            <i class="bi bi-<?php 
                                echo $order['status'] == 'delivered' ? 'check-circle' : 
                                     ($order['status'] == 'shipped' ? 'truck' : 
                                     ($order['status'] == 'cancelled' ? 'x-circle' : 'clock')); 
                            ?> me-1"></i>
                            <?php echo ucfirst(str_replace('_', ' ', $order['status'])); ?>
                        </span>
                        <a href="track_order.php?id=<?php echo $order['id']; ?>" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-geo-alt me-1"></i>Track
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <?php
                    // Get order items
                    $stmt = $db->prepare("SELECT oi.*, p.name, p.image FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?");
                    $stmt->bind_param("i", $order['id']);
                    $stmt->execute();
                    $items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
                    ?>
                    
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <tbody>
                                <?php foreach ($items as $item): ?>
                                <tr>
                                    <td style="width: 60px;">
                                        <img src="<?php echo $item['image']; ?>" class="rounded" style="width: 50px; height: 50px; object-fit: cover;">
                                    </td>
                                    <td><?php echo $item['name']; ?></td>
                                    <td>Qty: <?php echo $item['quantity']; ?></td>
                                    <td class="text-end">₹<?php echo number_format($item['price'] * $item['quantity']); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <hr>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <p class="mb-1"><strong>Payment Method:</strong> <?php echo str_replace('_', ' ', ucfirst($order['payment_method'])); ?></p>
                            <p class="mb-0"><strong>Shipping Address:</strong> <?php echo $order['shipping_address']; ?></p>
                        </div>
                        <div class="col-md-6 text-md-end">
                            <?php if ($order['discount_amount'] > 0): ?>
                            <p class="mb-1 text-success"><small><i class="bi bi-tag-fill me-1"></i>You saved ₹<?php echo number_format($order['discount_amount'], 2); ?></small></p>
                            <?php endif; ?>
                            <p class="mb-0"><strong>Total Amount:</strong> <span class="fs-5 text-primary">₹<?php echo number_format($order['total_amount'], 2); ?></span></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
