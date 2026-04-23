<?php
$page_title = 'Order Details';
require_once 'includes/header.php';

$order_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$order_id) {
    showAlert('Order not found', 'danger');
    redirect('orders.php');
}

// Get order with full tracking info
$order = getOrderTrackingInfo($order_id);

if (!$order) {
    showAlert('Order not found', 'danger');
    redirect('orders.php');
}

// Handle status update with notes
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $new_status = sanitize($_POST['status']);
    $notes = sanitize($_POST['notes']);
    $tracking_number = sanitize($_POST['tracking_number'] ?? '');
    $shipping_carrier = sanitize($_POST['shipping_carrier'] ?? '');
    $estimated_delivery = sanitize($_POST['estimated_delivery'] ?? '');
    
    if (updateOrderStatus($order_id, $new_status, $notes)) {
        // Update tracking info if provided
        if ($tracking_number || $shipping_carrier || $estimated_delivery) {
            $stmt = $db->prepare("UPDATE orders SET tracking_number = ?, shipping_carrier = ?, estimated_delivery = ? WHERE id = ?");
            $stmt->bind_param("sssi", $tracking_number, $shipping_carrier, $estimated_delivery, $order_id);
            $stmt->execute();
        }
        
        showAlert('Order status updated successfully', 'success');
        redirect('order_detail.php?id=' . $order_id);
    } else {
        $error = 'Failed to update order status';
    }
}

// Get customer info
$stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $order['user_id']);
$stmt->execute();
$customer = $stmt->get_result()->fetch_assoc();
?>

<div class="row">
    <div class="col-lg-8">
        <!-- Order Header -->
        <div class="card mb-4">
            <div class="card-header bg-white d-flex justify-content-between align-items-center flex-wrap">
                <div>
                    <h5 class="mb-1">Order #<?php echo $order['order_number']; ?></h5>
                    <small class="text-muted">Placed on <?php echo date('F d, Y h:i A', strtotime($order['created_at'])); ?></small>
                </div>
                <div class="mt-2 mt-md-0">
                    <span class="badge <?php echo getOrderStatusBadge($order['status']); ?> fs-6 px-3 py-2">
                        <i class="bi bi-<?php 
                            $icons = ['pending' => 'clock', 'confirmed' => 'check-circle', 'processing' => 'gear', 'packed' => 'box-seam', 
                                     'shipped' => 'truck', 'in_transit' => 'geo-alt', 'out_for_delivery' => 'bicycle', 
                                     'delivered' => 'check-circle-fill', 'cancelled' => 'x-circle', 'returned' => 'arrow-return-left'];
                            echo $icons[$order['status']] ?? 'circle';
                        ?> me-2"></i>
                        <?php echo ucfirst(str_replace('_', ' ', $order['status'])); ?>
                    </span>
                </div>
            </div>
            <div class="card-body">
                <!-- Progress Bar -->
                <div class="mb-4">
                    <div class="d-flex justify-content-between mb-2">
                        <small class="text-muted">Order Progress</small>
                        <small class="fw-bold"><?php echo getOrderStatusProgress($order['status']); ?>%</small>
                    </div>
                    <div class="progress" style="height: 10px;">
                        <div class="progress-bar bg-<?php echo $order['status'] == 'cancelled' ? 'danger' : 'primary'; ?>" 
                             style="width: <?php echo getOrderStatusProgress($order['status']); ?>"></div>
                    </div>
                </div>
                
                <!-- Order Items -->
                <h6 class="mb-3"><i class="bi bi-bag me-2"></i>Order Items</h6>
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead class="table-light">
                            <tr>
                                <th>Product</th>
                                <th>Price</th>
                                <th>Qty</th>
                                <th class="text-end">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($order['items'] as $item): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <img src="<?php echo $item['image']; ?>" alt="" class="rounded" style="width: 50px; height: 50px; object-fit: cover;">
                                        <span class="ms-2"><?php echo $item['name']; ?></span>
                                    </div>
                                </td>
                                <td>₹<?php echo number_format($item['price'], 2); ?></td>
                                <td><?php echo $item['quantity']; ?></td>
                                <td class="text-end">₹<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <!-- Status History -->
        <div class="card mb-4">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-clock-history me-2"></i>Status History</h6>
            </div>
            <div class="card-body">
                <div class="timeline">
                    <?php foreach ($order['status_history'] as $history): 
                        $status_icons = [
                            'pending' => 'hourglass', 'confirmed' => 'check-circle', 'processing' => 'gear', 
                            'packed' => 'box-seam', 'shipped' => 'truck', 'in_transit' => 'geo-alt', 
                            'out_for_delivery' => 'bicycle', 'delivered' => 'check-circle-fill', 
                            'cancelled' => 'x-circle', 'returned' => 'arrow-return-left', 'refunded' => 'cash-stack'
                        ];
                    ?>
                    <div class="d-flex mb-3">
                        <div class="flex-shrink-0">
                            <div class="bg-<?php echo getOrderStatusBadge($history['status']); ?> text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                <i class="bi bi-<?php echo $status_icons[$history['status']] ?? 'circle'; ?>"></i>
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
    </div>
    
    <div class="col-lg-4">
        <!-- Customer Info -->
        <div class="card mb-4">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-person me-2"></i>Customer Information</h6>
            </div>
            <div class="card-body">
                <p class="mb-1"><strong><?php echo $customer['name']; ?></strong></p>
                <p class="text-muted mb-2"><?php echo $customer['email']; ?></p>
                <p class="mb-0"><i class="bi bi-telephone me-2"></i><?php echo $customer['phone'] ?? 'N/A'; ?></p>
            </div>
        </div>
        
        <!-- Shipping Info -->
        <div class="card mb-4">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-geo-alt me-2"></i>Shipping Address</h6>
            </div>
            <div class="card-body">
                <p class="mb-0"><?php echo nl2br(htmlspecialchars($order['shipping_address'])); ?></p>
            </div>
        </div>
        
        <!-- Order Summary -->
        <div class="card mb-4">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-receipt me-2"></i>Order Summary</h6>
            </div>
            <div class="card-body">
                <?php
                $subtotal = $order['total_amount'] + $order['discount_amount'] - ($order['shipping_cost'] ?? 0);
                $gst = $order['total_amount'] - ($subtotal - $order['discount_amount']);
                ?>
                <div class="d-flex justify-content-between mb-2">
                    <span>Subtotal</span>
                    <span>₹<?php echo number_format($subtotal, 2); ?></span>
                </div>
                <?php if ($order['discount_amount'] > 0): ?>
                <div class="d-flex justify-content-between mb-2 text-success">
                    <span>Discount</span>
                    <span>-₹<?php echo number_format($order['discount_amount'], 2); ?></span>
                </div>
                <?php endif; ?>
                <div class="d-flex justify-content-between mb-2">
                    <span>Shipping</span>
                    <span>₹<?php echo number_format($order['shipping_cost'] ?? 0, 2); ?></span>
                </div>
                <hr>
                <div class="d-flex justify-content-between fw-bold">
                    <span>Total</span>
                    <span class="text-primary">₹<?php echo number_format($order['total_amount'], 2); ?></span>
                </div>
                <div class="mt-3">
                    <small class="text-muted">Payment: <?php echo ucfirst(str_replace('_', ' ', $order['payment_method'])); ?></small>
                </div>
            </div>
        </div>
        
        <!-- Update Status Form -->
        <div class="card">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-arrow-repeat me-2"></i>Update Order Status</h6>
            </div>
            <div class="card-body">
                <form method="POST" action="">
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <?php 
                            $statuses = ['pending', 'confirmed', 'processing', 'packed', 'shipped', 'in_transit', 'out_for_delivery', 'delivered', 'cancelled'];
                            foreach ($statuses as $status): 
                            ?>
                            <option value="<?php echo $status; ?>" <?php echo $order['status'] == $status ? 'selected' : ''; ?>>
                                <?php echo ucfirst(str_replace('_', ' ', $status)); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Tracking Number</label>
                        <input type="text" name="tracking_number" class="form-control" value="<?php echo $order['tracking_number'] ?? ''; ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Shipping Carrier</label>
                        <input type="text" name="shipping_carrier" class="form-control" value="<?php echo $order['shipping_carrier'] ?? ''; ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Estimated Delivery</label>
                        <input type="date" name="estimated_delivery" class="form-control" value="<?php echo $order['estimated_delivery'] ?? ''; ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="Add status update notes..."></textarea>
                    </div>
                    
                    <button type="submit" name="update_status" class="btn btn-primary w-100">
                        <i class="bi bi-check-lg me-2"></i>Update Status
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="mt-4">
    <a href="orders.php" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-2"></i>Back to Orders
    </a>
</div>

<?php require_once 'includes/footer.php'; ?>
