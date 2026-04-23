<?php
require_once 'config.php';

// Handle cart updates
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_quantity'])) {
        $cart_id = intval($_POST['cart_id']);
        $quantity = intval($_POST['quantity']);
        
        if (isLoggedIn()) {
            if ($quantity <= 0) {
                $stmt = $db->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?");
                $stmt->bind_param("ii", $cart_id, $_SESSION['user_id']);
                $stmt->execute();
            } else {
                $stmt = $db->prepare("UPDATE cart SET quantity = ? WHERE id = ? AND user_id = ?");
                $stmt->bind_param("iii", $quantity, $cart_id, $_SESSION['user_id']);
                $stmt->execute();
            }
        } else {
            // Session-based cart
            $product_id = intval($_POST['product_id']);
            if ($quantity <= 0) {
                unset($_SESSION['cart'][$product_id]);
            } else {
                $_SESSION['cart'][$product_id] = $quantity;
            }
        }
        
        redirect('cart.php');
    }
    
    if (isset($_POST['remove_item'])) {
        $cart_id = intval($_POST['cart_id']);
        
        if (isLoggedIn()) {
            $stmt = $db->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?");
            $stmt->bind_param("ii", $cart_id, $_SESSION['user_id']);
            $stmt->execute();
        } else {
            $product_id = intval($_POST['product_id']);
            unset($_SESSION['cart'][$product_id]);
        }
        
        showAlert('Item removed from cart', 'success');
        redirect('cart.php');
    }
}

// Get cart items
$cart_items = getCartItems();
$cart_total = getCartTotal();
$cart_count = count($cart_items);

$page_title = 'Shopping Cart';
include 'includes/header.php';
?>

<div class="container py-5">
    <h2 class="mb-4"><i class="bi bi-cart3 me-2"></i>Shopping Cart (<?php echo $cart_count; ?>)</h2>
    
    <?php if (empty($cart_items)): ?>
    <div class="text-center py-5">
        <i class="bi bi-cart-x display-1 text-muted"></i>
        <h3 class="mt-3">Your cart is empty</h3>
        <p class="text-muted">Browse our products and add items to your cart</p>
        <a href="products.php" class="btn btn-primary btn-lg">
            <i class="bi bi-shop me-2"></i>Continue Shopping
        </a>
    </div>
    <?php else: ?>
    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Product</th>
                                    <th>Price</th>
                                    <th>Quantity</th>
                                    <th>Total</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($cart_items as $item): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="<?php echo $item['image']; ?>" alt="<?php echo $item['name']; ?>" class="rounded" style="width: 60px; height: 60px; object-fit: cover;">
                                            <div class="ms-3">
                                                <h6 class="mb-1"><a href="product.php?id=<?php echo $item['product_id']; ?>" class="text-dark text-decoration-none"><?php echo substr($item['name'], 0, 40); ?></a></h6>
                                                <?php if ($item['stock'] < 5): ?>
                                                <small class="text-danger"><i class="bi bi-exclamation-circle"></i> Only <?php echo $item['stock']; ?> left</small>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td>₹<?php echo number_format($item['price']); ?></td>
                                    <td>
                                        <form method="POST" class="d-flex align-items-center" style="width: 120px;">
                                            <input type="hidden" name="cart_id" value="<?php echo $item['id'] ?? $item['product_id']; ?>">
                                            <input type="hidden" name="product_id" value="<?php echo $item['product_id']; ?>">
                                            <div class="input-group input-group-sm">
                                                <button type="button" class="btn btn-outline-secondary" onclick="this.parentElement.querySelector('input').stepDown(); this.form.submit()">-</button>
                                                <input type="number" name="quantity" class="form-control text-center" value="<?php echo $item['quantity']; ?>" min="1" max="<?php echo $item['stock']; ?>" style="width: 50px;">
                                                <button type="button" class="btn btn-outline-secondary" onclick="this.parentElement.querySelector('input').stepUp(); this.form.submit()">+</button>
                                            </div>
                                            <input type="hidden" name="update_quantity" value="1">
                                        </form>
                                    </td>
                                    <td class="fw-bold">₹<?php echo number_format($item['price'] * $item['quantity']); ?></td>
                                    <td>
                                        <form method="POST" onsubmit="return confirm('Remove this item?')">
                                            <input type="hidden" name="cart_id" value="<?php echo $item['id'] ?? $item['product_id']; ?>">
                                            <input type="hidden" name="product_id" value="<?php echo $item['product_id']; ?>">
                                            <button type="submit" name="remove_item" class="btn btn-outline-danger btn-sm">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <div class="mt-3">
                <a href="products.php" class="btn btn-outline-primary">
                    <i class="bi bi-arrow-left me-2"></i>Continue Shopping
                </a>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="card sticky-top" style="top: 100px;">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-receipt me-2"></i>Order Summary</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Subtotal (<?php echo $cart_count; ?> items)</span>
                        <span>₹<?php echo number_format($cart_total); ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Shipping</span>
                        <span class="text-success">FREE</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Tax (18% GST)</span>
                        <span>₹<?php echo number_format($cart_total * 0.18); ?></span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between mb-3">
                        <strong class="fs-5">Total</strong>
                        <strong class="fs-5 text-primary">₹<?php echo number_format($cart_total * 1.18); ?></strong>
                    </div>
                    
                    <a href="checkout.php" class="btn btn-primary w-100 btn-lg">
                        <i class="bi bi-credit-card me-2"></i>Proceed to Checkout
                    </a>
                    
                    <div class="text-center mt-3">
                        <small class="text-muted"><i class="bi bi-shield-check me-1"></i>Secure Checkout</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
