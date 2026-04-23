<?php
require_once 'config.php';
requireLogin();

$order = null;
$error = '';

if (isset($_GET['id'])) {
    $order_id = intval($_GET['id']);
    $order = getOrderTrackingInfo($order_id, $_SESSION['user_id']);
    
    if (!$order) {
        $error = 'Order not found or access denied.';
    }
} else {
    $error = 'No order ID provided.';
}

$page_title = 'Track Order';
include 'includes/header.php';
?>

<div class="container py-5">
    <?php if ($error): ?>
    <div class="alert alert-danger"><?php echo $error; ?></div>
    <div class="text-center">
        <a href="orders.php" class="btn btn-primary">
            <i class="bi bi-arrow-left me-2"></i>Back to My Orders
        </a>
    </div>
    <?php elseif ($order): ?>
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2><i class="bi bi-truck me-2"></i>Track Order #<?php echo $order['order_number']; ?></h2>
            <p class="text-muted mb-0">Placed on <?php echo date('F d, Y', strtotime($order['created_at'])); ?></p>
        </div>
        <span class="badge <?php echo getOrderStatusBadge($order['status']); ?> fs-6 px-3 py-2">
            <?php echo ucfirst(str_replace('_', ' ', $order['status'])); ?>
        </span>
    </div>
    
    <!-- Progress Bar -->
    <div class="card mb-4">
        <div class="card-body">
            <h5 class="mb-4">Order Progress</h5>
            <div class="progress" style="height: 30px;">
                <div class="progress-bar progress-bar-striped progress-bar-animated bg-<?php echo $order['status'] == 'cancelled' || $order['status'] == 'returned' ? 'danger' : 'primary'; ?>" 
                     role="progressbar" 
                     style="width: <?php echo getOrderStatusProgress($order['status']); ?>%">
                    <?php echo getOrderStatusProgress($order['status']); ?>%
                </div>
            </div>
            <div class="d-flex justify-content-between mt-2 text-muted">
                <small>Ordered</small>
                <small>Confirmed</small>
                <small>Shipped</small>
                <small>Out for Delivery</small>
                <small>Delivered</small>
            </div>
        </div>
    </div>
    
    <!-- Tracking Info -->
    <div class="row">
        <div class="col-lg-8">
            <!-- Status History Timeline -->
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="bi bi-clock-history me-2"></i>Order Status History</h5>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        <?php 
                        $status_icons = [
                            'pending' => 'bi-hourglass',
                            'confirmed' => 'bi-check-circle',
                            'processing' => 'bi-gear',
                            'packed' => 'bi-box-seam',
                            'shipped' => 'bi-truck',
                            'in_transit' => 'bi-geo-alt',
                            'out_for_delivery' => 'bi-bicycle',
                            'delivered' => 'bi-check-circle-fill',
                            'cancelled' => 'bi-x-circle',
                            'returned' => 'bi-arrow-return-left',
                            'refunded' => 'bi-cash-stack'
                        ];
                        
                        foreach ($order['status_history'] as $history): 
                            $icon = $status_icons[$history['status']] ?? 'bi-circle';
                        ?>
                        <div class="d-flex mb-3 pb-3 border-bottom">
                            <div class="flex-shrink-0">
                                <div class="bg-<?php echo getOrderStatusBadge($history['status']); ?> text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                    <i class="bi <?php echo $icon; ?> fs-5"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="mb-1"><?php echo ucfirst(str_replace('_', ' ', $history['status'])); ?></h6>
                                <p class="text-muted mb-1"><?php echo $history['notes'] ?: 'Status updated'; ?></p>
                                <small class="text-muted"><?php echo date('M d, Y h:i A', strtotime($history['created_at'])); ?></small>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            
            <!-- Order Items -->
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="bi bi-bag me-2"></i>Order Items</h5>
                </div>
                <div class="card-body">
                    <?php foreach ($order['items'] as $item): ?>
                    <div class="d-flex mb-3 pb-3 border-bottom">
                        <img src="<?php echo $item['image']; ?>" alt="<?php echo $item['name']; ?>" class="rounded" style="width: 80px; height: 80px; object-fit: cover;">
                        <div class="ms-3 flex-grow-1">
                            <h6 class="mb-1"><?php echo $item['name']; ?></h6>
                            <p class="text-muted mb-1">Qty: <?php echo $item['quantity']; ?></p>
                            <p class="mb-0">₹<?php echo number_format($item['price'], 2); ?> each</p>
                        </div>
                        <div class="text-end">
                            <strong>₹<?php echo number_format($item['price'] * $item['quantity'], 2); ?></strong>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <!-- Shipping Info -->
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="bi bi-geo-alt me-2"></i>Shipping Details</h5>
                </div>
                <div class="card-body">
                    <?php if ($order['tracking_number']): ?>
                    <div class="mb-3">
                        <small class="text-muted">Tracking Number</small>
                        <p class="mb-1 fw-bold"><?php echo $order['tracking_number']; ?></p>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($order['shipping_carrier']): ?>
                    <div class="mb-3">
                        <small class="text-muted">Shipping Carrier</small>
                        <p class="mb-1"><?php echo $order['shipping_carrier']; ?></p>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($order['estimated_delivery']): ?>
                    <div class="mb-3">
                        <small class="text-muted">Estimated Delivery</small>
                        <p class="mb-1 fw-bold text-success"><?php echo date('F d, Y', strtotime($order['estimated_delivery'])); ?></p>
                    </div>
                    <?php endif; ?>
                    
                    <div class="mb-0">
                        <small class="text-muted">Shipping Address</small>
                        <p class="mb-0"><?php echo nl2br(htmlspecialchars($order['shipping_address'])); ?></p>
                    </div>
                </div>
            </div>
            
            <!-- Order Summary -->
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="bi bi-receipt me-2"></i>Order Summary</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Subtotal</span>
                        <span>₹<?php echo number_format($order['total_amount'] + $order['discount_amount'] - $order['shipping_cost'], 2); ?></span>
                    </div>
                    <?php if ($order['discount_amount'] > 0): ?>
                    <div class="d-flex justify-content-between mb-2 text-success">
                        <span>Discount</span>
                        <span>-₹<?php echo number_format($order['discount_amount'], 2); ?></span>
                    </div>
                    <?php endif; ?>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Shipping</span>
                        <span>₹<?php echo number_format($order['shipping_cost'], 2); ?></span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between fw-bold">
                        <span>Total</span>
                        <span>₹<?php echo number_format($order['total_amount'], 2); ?></span>
                    </div>
                    <div class="mt-3">
                        <small class="text-muted">Payment Method</small>
                        <p class="mb-0"><?php echo ucfirst(str_replace('_', ' ', $order['payment_method'])); ?></p>
                    </div>
                </div>
            </div>
            
            <!-- Actions -->
            <div class="card mt-4">
                <div class="card-body">
                    <a href="orders.php" class="btn btn-outline-primary w-100 mb-2">
                        <i class="bi bi-arrow-left me-2"></i>Back to Orders
                    </a>
                    <?php if ($order['status'] == 'delivered'): ?>
                    <a href="product.php?id=<?php echo $order['items'][0]['product_id']; ?>" class="btn btn-outline-success w-100">
                        <i class="bi bi-star me-2"></i>Write a Review
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <?php endif; ?>
</div>

<style>
.timeline {
    position: relative;
}
</style>

<?php include 'includes/footer.php'; ?>
