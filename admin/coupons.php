<?php
$page_title = 'Coupons Management';
require_once 'includes/header.php';

// Handle coupon creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_coupon'])) {
    $code = strtoupper(sanitize($_POST['code']));
    $type = sanitize($_POST['type']);
    $value = floatval($_POST['value']);
    $min_order = floatval($_POST['min_order'] ?? 0);
    $max_discount = floatval($_POST['max_discount'] ?? 0);
    $start_date = sanitize($_POST['start_date']);
    $end_date = sanitize($_POST['end_date']);
    $usage_limit = intval($_POST['usage_limit'] ?? 0);
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    
    $max_discount_param = $max_discount > 0 ? $max_discount : null;
    $usage_limit_param = $usage_limit > 0 ? $usage_limit : null;
    
    $stmt = $db->prepare("INSERT INTO coupons (code, type, value, min_order_amount, max_discount, start_date, end_date, usage_limit, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssddsssii", $code, $type, $value, $min_order, $max_discount_param, $start_date, $end_date, $usage_limit_param, $is_active);
    
    if ($stmt->execute()) {
        showAlert('Coupon created successfully', 'success');
    } else {
        showAlert('Failed to create coupon. Code may already exist.', 'danger');
    }
    redirect('coupons.php');
}

// Handle coupon deletion
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $db->query("DELETE FROM coupons WHERE id = $id");
    showAlert('Coupon deleted', 'success');
    redirect('coupons.php');
}

// Handle coupon toggle
if (isset($_GET['toggle'])) {
    $id = intval($_GET['toggle']);
    $db->query("UPDATE coupons SET is_active = NOT is_active WHERE id = $id");
    showAlert('Coupon status updated', 'success');
    redirect('coupons.php');
}

// Get all coupons
$coupons = $db->query("SELECT * FROM coupons ORDER BY created_at DESC");
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="mb-0"><i class="bi bi-ticket-perforated me-2"></i>All Coupons</h5>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCouponModal">
        <i class="bi bi-plus-lg me-2"></i>Add Coupon
    </button>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Code</th>
                    <th>Type</th>
                    <th>Value</th>
                    <th>Min Order</th>
                    <th>Usage</th>
                    <th>Valid Period</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($coupon = $coupons->fetch_assoc()): ?>
                <tr>
                    <td><strong class="text-uppercase"><?php echo $coupon['code']; ?></strong></td>
                    <td>
                        <span class="badge bg-<?php echo $coupon['type'] == 'percentage' ? 'info' : 'secondary'; ?>">
                            <?php echo $coupon['type'] == 'percentage' ? 'Percentage %' : 'Fixed Amount'; ?>
                        </span>
                    </td>
                    <td>
                        <?php echo $coupon['type'] == 'percentage' ? $coupon['value'] . '%' : '₹' . number_format($coupon['value'], 2); ?>
                        <?php if ($coupon['max_discount']): ?>
                        <small class="text-muted d-block">Max: ₹<?php echo number_format($coupon['max_discount'], 2); ?></small>
                        <?php endif; ?>
                    </td>
                    <td>₹<?php echo number_format($coupon['min_order_amount'], 2); ?></td>
                    <td>
                        <?php echo $coupon['usage_count']; ?> / <?php echo $coupon['usage_limit'] ?: '∞'; ?>
                        <div class="progress" style="height: 4px;">
                            <div class="progress-bar" style="width: <?php echo $coupon['usage_limit'] ? ($coupon['usage_count'] / $coupon['usage_limit'] * 100) : 0; ?>%"></div>
                        </div>
                    </td>
                    <td>
                        <small><?php echo date('M d', strtotime($coupon['start_date'])); ?> - <?php echo date('M d, Y', strtotime($coupon['end_date'])); ?></small>
                        <?php 
                        $today = date('Y-m-d');
                        if ($today < $coupon['start_date']) {
                            echo '<span class="badge bg-warning ms-1">Upcoming</span>';
                        } elseif ($today > $coupon['end_date']) {
                            echo '<span class="badge bg-secondary ms-1">Expired</span>';
                        }
                        ?>
                    </td>
                    <td>
                        <span class="badge bg-<?php echo $coupon['is_active'] ? 'success' : 'secondary'; ?>">
                            <?php echo $coupon['is_active'] ? 'Active' : 'Inactive'; ?>
                        </span>
                    </td>
                    <td>
                        <a href="coupons.php?toggle=<?php echo $coupon['id']; ?>" class="btn btn-sm btn-outline-<?php echo $coupon['is_active'] ? 'warning' : 'success'; ?>">
                            <i class="bi bi-<?php echo $coupon['is_active'] ? 'pause' : 'play'; ?>"></i>
                        </a>
                        <a href="coupons.php?delete=<?php echo $coupon['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this coupon?')">
                            <i class="bi bi-trash"></i>
                        </a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add Coupon Modal -->
<div class="modal fade" id="addCouponModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-ticket-perforated me-2"></i>Add New Coupon</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Coupon Code *</label>
                        <input type="text" name="code" class="form-control text-uppercase" placeholder="WELCOME10" required>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Type *</label>
                            <select name="type" class="form-select" required>
                                <option value="percentage">Percentage (%)</option>
                                <option value="fixed">Fixed Amount (₹)</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Value *</label>
                            <input type="number" name="value" class="form-control" step="0.01" min="0" placeholder="10" required>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Min Order Amount</label>
                            <input type="number" name="min_order" class="form-control" step="0.01" min="0" value="0">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Max Discount (for %)</label>
                            <input type="number" name="max_discount" class="form-control" step="0.01" min="0" placeholder="No limit">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Start Date *</label>
                            <input type="date" name="start_date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">End Date *</label>
                            <input type="date" name="end_date" class="form-control" value="<?php echo date('Y-m-d', strtotime('+30 days')); ?>" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Usage Limit (leave blank for unlimited)</label>
                        <input type="number" name="usage_limit" class="form-control" min="1" placeholder="Unlimited">
                    </div>
                    
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="is_active" id="is_active" checked>
                        <label class="form-check-label" for="is_active">Active</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="add_coupon" class="btn btn-primary">Create Coupon</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
