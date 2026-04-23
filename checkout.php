<?php
require_once 'config.php';
requireLogin();

$cart_items = getCartItems();
$cart_total = getCartTotal();

if (empty($cart_items)) {
    showAlert('Your cart is empty', 'warning');
    redirect('products.php');
}

$error = '';
$coupon_error = '';
$coupon_success = '';
$discount = 0;
$coupon_code = '';

// Handle coupon application
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['apply_coupon'])) {
    $coupon_code = strtoupper(trim($_POST['coupon_code'] ?? ''));
    
    if (empty($coupon_code)) {
        $coupon_error = 'Please enter a coupon code';
    } else {
        $validation = validateCoupon($coupon_code, $_SESSION['user_id'], $cart_total);
        
        if ($validation['valid']) {
            $discount = $validation['discount'];
            $coupon_success = 'Coupon applied! You saved ₹' . number_format($discount, 2);
            $_SESSION['applied_coupon'] = $coupon_code;
            $_SESSION['coupon_discount'] = $discount;
        } else {
            $coupon_error = $validation['error'];
            unset($_SESSION['applied_coupon']);
            unset($_SESSION['coupon_discount']);
        }
    }
}

// Check for previously applied coupon
if (isset($_SESSION['applied_coupon'])) {
    $coupon_code = $_SESSION['applied_coupon'];
    $discount = $_SESSION['coupon_discount'] ?? calculateDiscount($coupon_code, $cart_total);
}

$final_total = $cart_total - $discount;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $shipping_address = sanitize($_POST['address']);
    $payment_method = sanitize($_POST['payment_method']);
    
    // Validate payment method details
    if ($payment_method == 'credit_card') {
        $card_number = str_replace(' ', '', $_POST['card_number'] ?? '');
        $card_expiry = $_POST['card_expiry'] ?? '';
        $card_cvv = $_POST['card_cvv'] ?? '';
        $card_name = $_POST['card_name'] ?? '';
        
        if (empty($card_number) || strlen($card_number) < 16) {
            $error = 'Please enter a valid card number';
        } elseif (empty($card_expiry) || !preg_match('/^\d{2}\/\d{2}$/', $card_expiry)) {
            $error = 'Please enter a valid expiry date (MM/YY)';
        } elseif (empty($card_cvv) || strlen($card_cvv) < 3) {
            $error = 'Please enter a valid CVV';
        } elseif (empty($card_name)) {
            $error = 'Please enter the cardholder name';
        }
    } elseif ($payment_method == 'upi') {
        $upi_id = $_POST['upi_id'] ?? '';
        if (empty($upi_id)) {
            $error = 'Please enter your UPI ID';
        }
    }
    
    if (empty($shipping_address)) {
        $error = 'Please enter shipping address';
    }
    
    if (empty($error)) {
        $gst = $final_total * 0.18;
        $shipping_cost = 0; // Free shipping
        $total_amount = $final_total + $gst + $shipping_cost;
        
        // Store payment details in session
        $_SESSION['payment_details'] = [
            'method' => $payment_method,
            'status' => 'pending'
        ];
        
        // Create order with coupon details
        $order_id = createOrder($_SESSION['user_id'], $total_amount, $shipping_address, $payment_method);
        
        if ($order_id) {
            // Apply coupon if used
            if (!empty($coupon_code)) {
                $stmt = $db->prepare("UPDATE orders SET coupon_code = ?, discount_amount = ?, shipping_cost = ? WHERE id = ?");
                $stmt->bind_param("sddi", $coupon_code, $discount, $shipping_cost, $order_id);
                $stmt->execute();
                
                // Record coupon usage
                applyCoupon($coupon_code, $_SESSION['user_id'], $order_id, $cart_total);
            }
            
            // Add initial status history
            addOrderStatusHistory($order_id, 'pending', 'Order placed');
            
            // Add order items
            foreach ($cart_items as $item) {
                addOrderItem($order_id, $item['product_id'], $item['quantity'], $item['price']);
                updateProductPurchases($item['product_id'], $item['quantity']);
            }
            
            // Send confirmation email
            sendOrderConfirmationEmail($_SESSION['user_id'], $order_id);
            
            // Clear cart and coupon
            clearUserCart($_SESSION['user_id']);
            unset($_SESSION['cart']);
            unset($_SESSION['applied_coupon']);
            unset($_SESSION['coupon_discount']);
            
            showAlert('Order placed successfully! ' . ($payment_method != 'cash_on_delivery' ? 'Please complete your payment.' : ''), 'success');
            redirect('orders.php?success=1');
        } else {
            $error = 'Failed to place order. Please try again.';
        }
    }
}

$user = getCurrentUser();
$page_title = 'Checkout';
include 'includes/header.php';
?>

<div class="container py-5">
    <h2 class="mb-4"><i class="bi bi-credit-card me-2"></i>Checkout</h2>
    
    <?php if ($error): ?>
    <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <div class="row">
        <div class="col-lg-8">
            <form method="POST" action="">
                <!-- Shipping Information -->
                <div class="card mb-4">
                    <div class="card-header bg-light">
                        <h5 class="mb-0"><i class="bi bi-geo-alt me-2"></i>Shipping Address</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Full Name *</label>
                                <input type="text" name="name" class="form-control" value="<?php echo $user['name']; ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email *</label>
                                <input type="email" name="email" class="form-control" value="<?php echo $user['email']; ?>" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Phone *</label>
                                <input type="tel" name="phone" class="form-control" value="<?php echo $user['phone']; ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">City *</label>
                                <input type="text" name="city" class="form-control" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Complete Address *</label>
                            <textarea name="address" class="form-control" rows="3" placeholder="House number, Street, Landmark, Pincode" required><?php echo $user['address']; ?></textarea>
                        </div>
                    </div>
                </div>
                
                <!-- Payment Method -->
                <div class="card mb-4">
                    <div class="card-header bg-light">
                        <h5 class="mb-0"><i class="bi bi-wallet2 me-2"></i>Payment Method</h5>
                    </div>
                    <div class="card-body">
                        <!-- Cash on Delivery -->
                        <div class="form-check mb-3 p-3 border rounded payment-option">
                            <input class="form-check-input" type="radio" name="payment_method" id="cod" value="cash_on_delivery" checked onchange="togglePaymentFields()">
                            <label class="form-check-label d-flex align-items-center" for="cod">
                                <i class="bi bi-cash-coin me-2 fs-4 text-success"></i>
                                <div>
                                    <strong>Cash on Delivery</strong>
                                    <p class="mb-0 text-muted small">Pay when you receive the order</p>
                                </div>
                            </label>
                        </div>
                        
                        <!-- Credit/Debit Card -->
                        <div class="form-check mb-3 p-3 border rounded payment-option">
                            <input class="form-check-input" type="radio" name="payment_method" id="card" value="credit_card" onchange="togglePaymentFields()">
                            <label class="form-check-label d-flex align-items-center" for="card">
                                <i class="bi bi-credit-card me-2 fs-4 text-primary"></i>
                                <div>
                                    <strong>Credit/Debit Card</strong>
                                    <p class="mb-0 text-muted small">Visa, Mastercard, RuPay</p>
                                </div>
                            </label>
                            <!-- Card Payment Form -->
                            <div id="cardFields" class="mt-3 p-3 bg-light rounded" style="display: none;">
                                <div class="mb-3">
                                    <label class="form-label">Card Number *</label>
                                    <input type="text" name="card_number" class="form-control" placeholder="1234 5678 9012 3456" maxlength="19" oninput="formatCardNumber(this)">
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Expiry Date *</label>
                                        <input type="text" name="card_expiry" class="form-control" placeholder="MM/YY" maxlength="5" oninput="formatExpiry(this)">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">CVV *</label>
                                        <input type="password" name="card_cvv" class="form-control" placeholder="123" maxlength="3">
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Cardholder Name *</label>
                                    <input type="text" name="card_name" class="form-control" placeholder="Name on card">
                                </div>
                                <div class="d-flex gap-2 mb-2">
                                    <img src="https://upload.wikimedia.org/wikipedia/commons/5/5e/Visa_Inc._logo.svg" alt="Visa" style="height: 30px;">
                                    <img src="https://upload.wikimedia.org/wikipedia/commons/2/2a/Mastercard-logo.svg" alt="Mastercard" style="height: 30px;">
                                    <img src="https://upload.wikimedia.org/wikipedia/commons/3/3f/Rupay_logo.svg" alt="RuPay" style="height: 30px;">
                                </div>
                            </div>
                        </div>
                        
                        <!-- UPI Payment -->
                        <div class="form-check p-3 border rounded payment-option">
                            <input class="form-check-input" type="radio" name="payment_method" id="upi" value="upi" onchange="togglePaymentFields()">
                            <label class="form-check-label d-flex align-items-center" for="upi">
                                <i class="bi bi-phone me-2 fs-4 text-info"></i>
                                <div>
                                    <strong>UPI Payment</strong>
                                    <p class="mb-0 text-muted small">Google Pay, PhonePe, Paytm, BHIM</p>
                                </div>
                            </label>
                            <!-- UPI Payment Form -->
                            <div id="upiFields" class="mt-3 p-3 bg-light rounded" style="display: none;">
                                <div class="mb-3">
                                    <label class="form-label">UPI ID *</label>
                                    <div class="input-group">
                                        <input type="text" name="upi_id" class="form-control" placeholder="yourname@upi">
                                        <span class="input-group-text">@upi</span>
                                    </div>
                                    <small class="text-muted">Example: 9876543210@paytm</small>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label d-block">Or select UPI App</label>
                                    <div class="d-flex gap-2 flex-wrap">
                                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="setUPI('googlepay')">
                                            <i class="bi bi-google me-1"></i>GPay
                                        </button>
                                        <button type="button" class="btn btn-outline-info btn-sm" onclick="setUPI('phonepe')">
                                            <i class="bi bi-phone me-1"></i>PhonePe
                                        </button>
                                        <button type="button" class="btn btn-outline-success btn-sm" onclick="setUPI('paytm')">
                                            <i class="bi bi-wallet me-1"></i>Paytm
                                        </button>
                                        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="setUPI('bhim')">
                                            <i class="bi bi-bank me-1"></i>BHIM
                                        </button>
                                    </div>
                                </div>
                                <div class="alert alert-info mb-0">
                                    <small><i class="bi bi-info-circle me-1"></i>You will receive a payment request on your UPI app</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary btn-lg w-100">
                    <i class="bi bi-bag-check me-2"></i>Place Order
                </button>
            </form>
        </div>
        
        <div class="col-lg-4">
            <div class="card sticky-top" style="top: 100px;">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="bi bi-receipt me-2"></i>Order Summary</h5>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush mb-3">
                        <?php foreach ($cart_items as $item): ?>
                        <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <div class="d-flex align-items-center">
                                <img src="<?php echo $item['image']; ?>" class="rounded" style="width: 40px; height: 40px; object-fit: cover;">
                                <div class="ms-2">
                                    <small class="d-block text-truncate" style="max-width: 120px;"><?php echo substr($item['name'], 0, 25); ?></small>
                                    <small class="text-muted">Qty: <?php echo $item['quantity']; ?></small>
                                </div>
                            </div>
                            <span>₹<?php echo number_format($item['price'] * $item['quantity']); ?></span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <!-- Coupon Section -->
                    <div class="mb-3 p-2 bg-light rounded">
                        <?php if ($coupon_error): ?>
                        <div class="alert alert-danger py-2 mb-2"><small><?php echo $coupon_error; ?></small></div>
                        <?php endif; ?>
                        <?php if ($coupon_success): ?>
                        <div class="alert alert-success py-2 mb-2"><small><?php echo $coupon_success; ?></small></div>
                        <?php endif; ?>
                        
                        <?php if (empty($coupon_code)): ?>
                        <form method="POST" action="" class="d-flex gap-2">
                            <input type="text" name="coupon_code" class="form-control form-control-sm text-uppercase" placeholder="Enter coupon code">
                            <button type="submit" name="apply_coupon" class="btn btn-outline-primary btn-sm">Apply</button>
                        </form>
                        <small class="text-muted d-block mt-1">Try: WELCOME10, FLAT50, SAVE20</small>
                        <?php else: ?>
                        <div class="d-flex justify-content-between align-items-center">
                            <span><i class="bi bi-check-circle-fill text-success me-1"></i>Code: <strong><?php echo $coupon_code; ?></strong></span>
                            <a href="checkout.php" class="btn btn-link btn-sm text-danger">Remove</a>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <hr>
                    
                    <div class="d-flex justify-content-between mb-2">
                        <span>Subtotal</span>
                        <span>₹<?php echo number_format($cart_total); ?></span>
                    </div>
                    <?php if ($discount > 0): ?>
                    <div class="d-flex justify-content-between mb-2 text-success">
                        <span>Discount</span>
                        <span>-₹<?php echo number_format($discount); ?></span>
                    </div>
                    <?php endif; ?>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Shipping</span>
                        <span class="text-success">FREE</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>GST (18%)</span>
                        <span>₹<?php echo number_format($final_total * 0.18); ?></span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between">
                        <strong class="fs-5">Total</strong>
                        <strong class="fs-5 text-primary">₹<?php echo number_format($final_total * 1.18); ?></strong>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Toggle payment method fields
function togglePaymentFields() {
    const cod = document.getElementById('cod').checked;
    const card = document.getElementById('card').checked;
    const upi = document.getElementById('upi').checked;
    
    document.getElementById('cardFields').style.display = card ? 'block' : 'none';
    document.getElementById('upiFields').style.display = upi ? 'block' : 'none';
    
    // Make card fields required when selected
    const cardInputs = document.querySelectorAll('#cardFields input');
    cardInputs.forEach(input => input.required = card);
    
    // Make UPI field required when selected
    const upiInput = document.querySelector('input[name="upi_id"]');
    if (upiInput) upiInput.required = upi;
}

// Format card number with spaces
function formatCardNumber(input) {
    let value = input.value.replace(/\s/g, '');
    value = value.replace(/\D/g, '');
    value = value.substring(0, 16);
    
    // Add spaces every 4 digits
    let formattedValue = '';
    for (let i = 0; i < value.length; i++) {
        if (i > 0 && i % 4 === 0) {
            formattedValue += ' ';
        }
        formattedValue += value[i];
    }
    
    input.value = formattedValue;
}

// Format expiry date
function formatExpiry(input) {
    let value = input.value.replace(/\D/g, '');
    value = value.substring(0, 4);
    
    if (value.length >= 2) {
        const month = parseInt(value.substring(0, 2));
        if (month > 12) {
            value = '12' + value.substring(2);
        }
        value = value.substring(0, 2) + '/' + value.substring(2);
    }
    
    input.value = value;
}

// Set UPI ID from buttons
function setUPI(app) {
    const upiInput = document.querySelector('input[name="upi_id"]');
    const upiId = upiInput.value.replace(/@.*$/, '');
    
    const domains = {
        'googlepay': 'okaxis',
        'phonepe': 'ybl',
        'paytm': 'paytm',
        'bhim': 'upi'
    };
    
    upiInput.value = upiId + '@' + domains[app];
}

// Add styles for payment options
const style = document.createElement('style');
style.textContent = `
    .payment-option {
        transition: all 0.3s ease;
        cursor: pointer;
    }
    .payment-option:hover {
        border-color: var(--bs-primary) !important;
        background: #fff1f2;
    }
    .payment-option:has(input:checked) {
        border-color: #e11d48 !important;
        background: #fff1f2;
        box-shadow: 0 0 0 2px rgba(225, 29, 72, 0.1);
    }
`;
document.head.appendChild(style);
</script>

<?php include 'includes/footer.php'; ?>
