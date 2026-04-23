<?php
/**
 * AI-Powered eCommerce Website Configuration
 * BCA Final Year Project
 */

// Start session
session_start();

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'snaptogift');

// Create database connection
function getDBConnection() {
    static $conn = null;
    
    if ($conn === null) {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }
        
        $conn->set_charset("utf8mb4");
    }
    
    return $conn;
}

// Get database connection
$db = getDBConnection();

// Helper Functions
function sanitize($data) {
    global $db;
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

function redirect($url) {
    header("Location: " . $url);
    exit();
}

function showAlert($message, $type = 'success') {
    $_SESSION['alert'] = ['message' => $message, 'type' => $type];
}

function getAlert() {
    if (isset($_SESSION['alert'])) {
        $alert = $_SESSION['alert'];
        unset($_SESSION['alert']);
        return $alert;
    }
    return null;
}

// User Authentication Functions
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['admin_id']);
}

function getCurrentUser() {
    if (isLoggedIn()) {
        global $db;
        $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->bind_param("i", $_SESSION['user_id']);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
    return null;
}

function requireLogin() {
    if (!isLoggedIn()) {
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
        redirect('login.php');
    }
}

function requireAdmin() {
    if (!isAdmin()) {
        redirect('admin/login.php');
    }
}

// Cart Functions
function getCartCount() {
    if (isLoggedIn()) {
        global $db;
        $stmt = $db->prepare("SELECT SUM(quantity) as count FROM cart WHERE user_id = ?");
        $stmt->bind_param("i", $_SESSION['user_id']);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        return $result['count'] ?? 0;
    }
    return array_sum($_SESSION['cart'] ?? []);
}

function getCartItems() {
    if (isLoggedIn()) {
        global $db;
        $stmt = $db->prepare("SELECT c.*, p.name, p.price, p.image, p.stock FROM cart c JOIN products p ON c.product_id = p.id WHERE c.user_id = ?");
        $stmt->bind_param("i", $_SESSION['user_id']);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    
    $items = [];
    if (!empty($_SESSION['cart'])) {
        global $db;
        foreach ($_SESSION['cart'] as $product_id => $quantity) {
            $stmt = $db->prepare("SELECT id, name, price, image, stock FROM products WHERE id = ?");
            $stmt->bind_param("i", $product_id);
            $stmt->execute();
            $product = $stmt->get_result()->fetch_assoc();
            if ($product) {
                $product['quantity'] = $quantity;
                $product['product_id'] = $product_id;
                $items[] = $product;
            }
        }
    }
    return $items;
}

function getCartTotal() {
    $items = getCartItems();
    $total = 0;
    foreach ($items as $item) {
        $total += $item['price'] * $item['quantity'];
    }
    return $total;
}

function addToCartDB($user_id, $product_id, $quantity = 1) {
    global $db;
    
    // Check if item already in cart
    $stmt = $db->prepare("SELECT id, quantity FROM cart WHERE user_id = ? AND product_id = ?");
    $stmt->bind_param("ii", $user_id, $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        // Update quantity
        $new_qty = $row['quantity'] + $quantity;
        $stmt = $db->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
        $stmt->bind_param("ii", $new_qty, $row['id']);
        return $stmt->execute();
    } else {
        // Insert new item
        $stmt = $db->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)");
        $stmt->bind_param("iii", $user_id, $product_id, $quantity);
        return $stmt->execute();
    }
}

// AI Recommendation Functions
function getRecommendedProducts($user_id = null, $limit = 6) {
    global $db;
    $products = [];
    
    if ($user_id) {
        // 1. Get categories user has viewed
        $stmt = $db->prepare("SELECT category_id, COUNT(*) as views FROM product_views WHERE user_id = ? GROUP BY category_id ORDER BY views DESC LIMIT 3");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $viewed_categories = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        // 2. Get products from viewed categories (excluding already viewed)
        foreach ($viewed_categories as $cat) {
            $stmt = $db->prepare("SELECT p.*, c.name as category_name FROM products p 
                                  JOIN categories c ON p.category_id = c.id 
                                  WHERE p.category_id = ? AND p.id NOT IN 
                                  (SELECT product_id FROM product_views WHERE user_id = ?)
                                  ORDER BY p.purchases DESC, p.views DESC LIMIT 2");
            $stmt->bind_param("ii", $cat['category_id'], $user_id);
            $stmt->execute();
            $cat_products = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            $products = array_merge($products, $cat_products);
        }
    }
    
    // 3. Fill remaining with popular products
    $remaining = $limit - count($products);
    if ($remaining > 0) {
        $exclude_ids = array_column($products, 'id');
        if (empty($exclude_ids)) {
            $stmt = $db->prepare("SELECT p.*, c.name as category_name FROM products p 
                                  JOIN categories c ON p.category_id = c.id 
                                  ORDER BY (p.views + p.purchases * 3) DESC LIMIT ?");
            $stmt->bind_param("i", $remaining);
        } else {
            $placeholders = implode(',', array_fill(0, count($exclude_ids), '?'));
            $types = str_repeat('i', count($exclude_ids)) . 'i';
            $params = array_merge($exclude_ids, [$remaining]);
            
            $sql = "SELECT p.*, c.name as category_name FROM products p 
                    JOIN categories c ON p.category_id = c.id 
                    WHERE p.id NOT IN ($placeholders)
                    ORDER BY (p.views + p.purchases * 3) DESC LIMIT ?";
            $stmt = $db->prepare($sql);
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $popular = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $products = array_merge($products, $popular);
    }
    
    return array_slice($products, 0, $limit);
}

function getPopularProducts($limit = 8) {
    global $db;
    $stmt = $db->prepare("SELECT p.*, c.name as category_name FROM products p 
                          JOIN categories c ON p.category_id = c.id 
                          ORDER BY (p.views + p.purchases * 3) DESC LIMIT ?");
    $stmt->bind_param("i", $limit);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function getTrendingProducts($limit = 4) {
    global $db;
    $stmt = $db->prepare("SELECT p.*, c.name as category_name FROM products p 
                          JOIN categories c ON p.category_id = c.id 
                          WHERE p.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                          ORDER BY p.views DESC LIMIT ?");
    $stmt->bind_param("i", $limit);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function recordProductView($user_id, $product_id) {
    global $db;
    
    // Get product category
    $stmt = $db->prepare("SELECT category_id FROM products WHERE id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $category_id = $result ? $result['category_id'] : null;
    
    // Record view
    if ($user_id) {
        $stmt = $db->prepare("INSERT INTO product_views (user_id, product_id, category_id) VALUES (?, ?, ?)");
        $stmt->bind_param("iii", $user_id, $product_id, $category_id);
        $stmt->execute();
    }
    
    // Update product view count
    $stmt = $db->prepare("UPDATE products SET views = views + 1 WHERE id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
}

// Order Functions
function generateOrderNumber() {
    return 'ORD' . date('Ymd') . strtoupper(substr(uniqid(), -6));
}

function createOrder($user_id, $total_amount, $shipping_address, $payment_method = 'cash_on_delivery') {
    global $db;
    
    $order_number = generateOrderNumber();
    
    $stmt = $db->prepare("INSERT INTO orders (order_number, user_id, total_amount, shipping_address, payment_method) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("ssdss", $order_number, $user_id, $total_amount, $shipping_address, $payment_method);
    
    if ($stmt->execute()) {
        return $db->insert_id;
    }
    return false;
}

function addOrderItem($order_id, $product_id, $quantity, $price) {
    global $db;
    $stmt = $db->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iiid", $order_id, $product_id, $quantity, $price);
    return $stmt->execute();
}

function clearUserCart($user_id) {
    global $db;
    $stmt = $db->prepare("DELETE FROM cart WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    return $stmt->execute();
}

function updateProductPurchases($product_id, $quantity) {
    global $db;
    $stmt = $db->prepare("UPDATE products SET purchases = purchases + ? WHERE id = ?");
    $stmt->bind_param("ii", $quantity, $product_id);
    return $stmt->execute();
}

// Search and Filter Functions
function searchProducts($keyword, $category_id = null) {
    global $db;
    $keyword = "%$keyword%";
    
    if ($category_id) {
        $stmt = $db->prepare("SELECT p.*, c.name as category_name FROM products p 
                              JOIN categories c ON p.category_id = c.id 
                              WHERE (p.name LIKE ? OR p.description LIKE ?) AND p.category_id = ?");
        $stmt->bind_param("ssi", $keyword, $keyword, $category_id);
    } else {
        $stmt = $db->prepare("SELECT p.*, c.name as category_name FROM products p 
                              JOIN categories c ON p.category_id = c.id 
                              WHERE p.name LIKE ? OR p.description LIKE ?");
        $stmt->bind_param("ss", $keyword, $keyword);
    }
    
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function getProductsByCategory($category_id, $sort = 'newest') {
    global $db;
    
    $order_by = "p.created_at DESC";
    switch ($sort) {
        case 'price_low':
            $order_by = "p.price ASC";
            break;
        case 'price_high':
            $order_by = "p.price DESC";
            break;
        case 'popular':
            $order_by = "(p.views + p.purchases) DESC";
            break;
        case 'rating':
            $order_by = "p.rating DESC";
            break;
    }
    
    $stmt = $db->prepare("SELECT p.*, c.name as category_name FROM products p 
                          JOIN categories c ON p.category_id = c.id 
                          WHERE p.category_id = ? ORDER BY $order_by");
    $stmt->bind_param("i", $category_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

// Analytics Functions (for Admin)
function getTotalUsers() {
    global $db;
    $result = $db->query("SELECT COUNT(*) as total FROM users");
    return $result->fetch_assoc()['total'];
}

function getTotalOrders() {
    global $db;
    $result = $db->query("SELECT COUNT(*) as total FROM orders");
    return $result->fetch_assoc()['total'];
}

function getTotalRevenue() {
    global $db;
    $result = $db->query("SELECT SUM(total_amount) as total FROM orders WHERE status != 'cancelled'");
    return $result->fetch_assoc()['total'] ?? 0;
}

function getTopProducts($limit = 5) {
    global $db;
    $stmt = $db->prepare("SELECT p.name, p.purchases, p.views FROM products p ORDER BY p.purchases DESC LIMIT ?");
    $stmt->bind_param("i", $limit);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

// Wishlist Functions
function addToWishlist($user_id, $product_id) {
    global $db;
    $stmt = $db->prepare("INSERT IGNORE INTO wishlist (user_id, product_id) VALUES (?, ?)");
    $stmt->bind_param("ii", $user_id, $product_id);
    return $stmt->execute();
}

function removeFromWishlist($user_id, $product_id) {
    global $db;
    $stmt = $db->prepare("DELETE FROM wishlist WHERE user_id = ? AND product_id = ?");
    $stmt->bind_param("ii", $user_id, $product_id);
    return $stmt->execute();
}

function getWishlistItems($user_id) {
    global $db;
    $stmt = $db->prepare("SELECT w.*, p.name, p.price, p.image, p.stock FROM wishlist w 
                          JOIN products p ON w.product_id = p.id 
                          WHERE w.user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function isInWishlist($user_id, $product_id) {
    global $db;
    $stmt = $db->prepare("SELECT id FROM wishlist WHERE user_id = ? AND product_id = ?");
    $stmt->bind_param("ii", $user_id, $product_id);
    $stmt->execute();
    return $stmt->get_result()->num_rows > 0;
}

// ==================== OTP FUNCTIONS ====================

function generateOTP() {
    return str_pad(mt_rand(0, 999999), 6, '0', STR_PAD_LEFT);
}

function sendOTP($email, $otp, $type = 'registration') {
    global $db;
    
    // Store OTP in database
    $expires_at = date('Y-m-d H:i:s', strtotime('+15 minutes'));
    $stmt = $db->prepare("INSERT INTO otp_verifications (email, otp_code, type, expires_at) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $email, $otp, $type, $expires_at);
    $stmt->execute();
    
    // For now, store in session for demo (in production, use SMTP)
    $_SESSION['otp_' . $type] = [
        'email' => $email,
        'otp' => $otp,
        'expires' => $expires_at
    ];
    
    return true;
}

function verifyOTP($email, $otp, $type = 'registration') {
    global $db;
    
    $stmt = $db->prepare("SELECT * FROM otp_verifications WHERE email = ? AND otp_code = ? AND type = ? AND expires_at > NOW() AND verified = 0 ORDER BY created_at DESC LIMIT 1");
    $stmt->bind_param("sss", $email, $otp, $type);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        // Mark as verified
        $stmt = $db->prepare("UPDATE otp_verifications SET verified = 1 WHERE id = ?");
        $stmt->bind_param("i", $row['id']);
        $stmt->execute();
        return true;
    }
    
    return false;
}

// ==================== EMAIL FUNCTIONS ====================

function sendEmail($to, $subject, $message, $type = 'general') {
    global $db;
    
    // Log email notification
    if (isLoggedIn()) {
        $user_id = $_SESSION['user_id'];
        $stmt = $db->prepare("INSERT INTO email_notifications (user_id, type, subject, status) VALUES (?, ?, ?, 'pending')");
        $stmt->bind_param("iss", $user_id, $type, $subject);
        $stmt->execute();
    }
    
    // In production, use mail() or SMTP
    // For demo, just log
    return true;
}

function sendOrderConfirmationEmail($user_id, $order_id) {
    global $db;
    
    $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    
    $subject = "Order Confirmation - Order #$order_id";
    $message = "Thank you for your order! Your order #$order_id has been confirmed.";
    
    sendEmail($user['email'], $subject, $message, 'order_confirmation');
}

function sendOrderStatusEmail($user_id, $order_id, $status) {
    global $db;
    
    $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    
    $subject = "Order Update - Order #$order_id";
    $message = "Your order #$order_id status has been updated to: $status";
    
    $type = ($status == 'shipped') ? 'order_shipped' : (($status == 'delivered') ? 'order_delivered' : 'order_confirmation');
    sendEmail($user['email'], $subject, $message, $type);
}

// ==================== REVIEW FUNCTIONS ====================

function addReview($user_id, $product_id, $order_id, $rating, $title, $review) {
    global $db;
    
    $stmt = $db->prepare("INSERT INTO reviews (user_id, product_id, order_id, rating, title, review) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("iiiiss", $user_id, $product_id, $order_id, $rating, $title, $review);
    
    if ($stmt->execute()) {
        // Update product rating
        updateProductRating($product_id);
        return true;
    }
    return false;
}

function updateProductRating($product_id) {
    global $db;
    
    $stmt = $db->prepare("SELECT AVG(rating) as avg_rating, COUNT(*) as total_reviews FROM reviews WHERE product_id = ? AND is_approved = 1");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    
    $stmt = $db->prepare("UPDATE products SET rating = ?, reviews = ? WHERE id = ?");
    $rating = round($result['avg_rating'], 1);
    $reviews = $result['total_reviews'];
    $stmt->bind_param("dii", $rating, $reviews, $product_id);
    $stmt->execute();
}

function getProductReviews($product_id, $limit = 10) {
    global $db;
    
    $stmt = $db->prepare("SELECT r.*, u.name as user_name FROM reviews r JOIN users u ON r.user_id = u.id WHERE r.product_id = ? AND r.is_approved = 1 ORDER BY r.created_at DESC LIMIT ?");
    $stmt->bind_param("ii", $product_id, $limit);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function getProductRatingBreakdown($product_id) {
    global $db;
    
    $stmt = $db->prepare("SELECT rating, COUNT(*) as count FROM reviews WHERE product_id = ? AND is_approved = 1 GROUP BY rating");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $breakdown = [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0];
    while ($row = $result->fetch_assoc()) {
        $breakdown[$row['rating']] = $row['count'];
    }
    
    return $breakdown;
}

function hasUserPurchasedProduct($user_id, $product_id) {
    global $db;
    
    $stmt = $db->prepare("SELECT oi.id FROM order_items oi JOIN orders o ON oi.order_id = o.id WHERE o.user_id = ? AND oi.product_id = ? AND o.status = 'delivered' LIMIT 1");
    $stmt->bind_param("ii", $user_id, $product_id);
    $stmt->execute();
    return $stmt->get_result()->num_rows > 0;
}

function hasUserReviewedProduct($user_id, $product_id) {
    global $db;
    
    $stmt = $db->prepare("SELECT id FROM reviews WHERE user_id = ? AND product_id = ? LIMIT 1");
    $stmt->bind_param("ii", $user_id, $product_id);
    $stmt->execute();
    return $stmt->get_result()->num_rows > 0;
}

// ==================== COUPON FUNCTIONS ====================

function getActiveCoupons() {
    global $db;
    
    $stmt = $db->prepare("SELECT * FROM coupons WHERE is_active = 1 AND start_date <= CURDATE() AND end_date >= CURDATE() AND (usage_limit IS NULL OR usage_count < usage_limit)");
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function validateCoupon($code, $user_id, $order_amount) {
    global $db;
    
    $stmt = $db->prepare("SELECT * FROM coupons WHERE code = ? AND is_active = 1 AND start_date <= CURDATE() AND end_date >= CURDATE()");
    $stmt->bind_param("s", $code);
    $stmt->execute();
    $coupon = $stmt->get_result()->fetch_assoc();
    
    if (!$coupon) {
        return ['valid' => false, 'error' => 'Invalid coupon code'];
    }
    
    if ($coupon['usage_limit'] && $coupon['usage_count'] >= $coupon['usage_limit']) {
        return ['valid' => false, 'error' => 'Coupon usage limit exceeded'];
    }
    
    if ($order_amount < $coupon['min_order_amount']) {
        return ['valid' => false, 'error' => 'Minimum order amount of ₹' . $coupon['min_order_amount'] . ' required'];
    }
    
    // Check if user already used this coupon
    $stmt = $db->prepare("SELECT id FROM user_coupons WHERE user_id = ? AND coupon_id = ?");
    $stmt->bind_param("ii", $user_id, $coupon['id']);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        return ['valid' => false, 'error' => 'You have already used this coupon'];
    }
    
    // Calculate discount
    if ($coupon['type'] == 'percentage') {
        $discount = $order_amount * ($coupon['value'] / 100);
        if ($coupon['max_discount'] && $discount > $coupon['max_discount']) {
            $discount = $coupon['max_discount'];
        }
    } else {
        $discount = $coupon['value'];
    }
    
    return [
        'valid' => true,
        'coupon' => $coupon,
        'discount' => $discount
    ];
}

function applyCoupon($code, $user_id, $order_id, $order_amount) {
    global $db;
    
    $validation = validateCoupon($code, $user_id, $order_amount);
    
    if (!$validation['valid']) {
        return $validation;
    }
    
    $coupon = $validation['coupon'];
    $discount = $validation['discount'];
    
    // Increment usage count
    $stmt = $db->prepare("UPDATE coupons SET usage_count = usage_count + 1 WHERE id = ?");
    $stmt->bind_param("i", $coupon['id']);
    $stmt->execute();
    
    // Record usage
    $stmt = $db->prepare("INSERT INTO user_coupons (user_id, coupon_id, order_id, discount_amount) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iiid", $user_id, $coupon['id'], $order_id, $discount);
    $stmt->execute();
    
    return ['valid' => true, 'discount' => $discount, 'coupon_id' => $coupon['id']];
}

function calculateDiscount($code, $order_amount) {
    global $db;
    
    $stmt = $db->prepare("SELECT * FROM coupons WHERE code = ? AND is_active = 1");
    $stmt->bind_param("s", $code);
    $stmt->execute();
    $coupon = $stmt->get_result()->fetch_assoc();
    
    if (!$coupon) return 0;
    
    if ($coupon['type'] == 'percentage') {
        $discount = $order_amount * ($coupon['value'] / 100);
        if ($coupon['max_discount'] && $discount > $coupon['max_discount']) {
            $discount = $coupon['max_discount'];
        }
    } else {
        $discount = min($coupon['value'], $order_amount);
    }
    
    return $discount;
}

// ==================== RECENTLY VIEWED FUNCTIONS ====================

function addRecentlyViewed($user_id, $product_id) {
    global $db;
    
    // Use INSERT ... ON DUPLICATE KEY UPDATE to handle existing entries
    $stmt = $db->prepare("INSERT INTO recently_viewed (user_id, product_id, viewed_at) VALUES (?, ?, NOW()) ON DUPLICATE KEY UPDATE viewed_at = NOW()");
    $stmt->bind_param("ii", $user_id, $product_id);
    $stmt->execute();
    
    // Keep only last 20 items per user
    $stmt = $db->prepare("DELETE FROM recently_viewed WHERE user_id = ? AND id NOT IN (SELECT id FROM (SELECT id FROM recently_viewed WHERE user_id = ? ORDER BY viewed_at DESC LIMIT 20) as temp)");
    $stmt->bind_param("ii", $user_id, $user_id);
    $stmt->execute();
}

function getRecentlyViewed($user_id, $limit = 8) {
    global $db;
    
    $stmt = $db->prepare("SELECT p.*, rv.viewed_at FROM recently_viewed rv JOIN products p ON rv.product_id = p.id WHERE rv.user_id = ? ORDER BY rv.viewed_at DESC LIMIT ?");
    $stmt->bind_param("ii", $user_id, $limit);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

// ==================== ORDER TRACKING FUNCTIONS ====================

function addOrderStatusHistory($order_id, $status, $notes = '', $created_by = null) {
    global $db;
    
    $stmt = $db->prepare("INSERT INTO order_status_history (order_id, status, notes, created_by) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("issi", $order_id, $status, $notes, $created_by);
    return $stmt->execute();
}

function getOrderStatusHistory($order_id) {
    global $db;
    
    $stmt = $db->prepare("SELECT * FROM order_status_history WHERE order_id = ? ORDER BY created_at ASC");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function updateOrderStatus($order_id, $status, $notes = '') {
    global $db;
    
    $user_id = isLoggedIn() ? $_SESSION['user_id'] : null;
    
    // Update order status
    $stmt = $db->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $order_id);
    
    if ($stmt->execute()) {
        // Add to history
        addOrderStatusHistory($order_id, $status, $notes, $user_id);
        
        // Send email notification
        $stmt = $db->prepare("SELECT user_id FROM orders WHERE id = ?");
        $stmt->bind_param("i", $order_id);
        $stmt->execute();
        $order = $stmt->get_result()->fetch_assoc();
        
        if ($order) {
            sendOrderStatusEmail($order['user_id'], $order_id, $status);
        }
        
        return true;
    }
    
    return false;
}

function getOrderTrackingInfo($order_id, $user_id = null) {
    global $db;
    
    if ($user_id) {
        $stmt = $db->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $order_id, $user_id);
    } else {
        $stmt = $db->prepare("SELECT * FROM orders WHERE id = ?");
        $stmt->bind_param("i", $order_id);
    }
    
    $stmt->execute();
    $order = $stmt->get_result()->fetch_assoc();
    
    if (!$order) return null;
    
    // Get order items
    $stmt = $db->prepare("SELECT oi.*, p.name, p.image FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $order['items'] = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    
    // Get status history
    $order['status_history'] = getOrderStatusHistory($order_id);
    
    return $order;
}

function getOrderStatusBadge($status) {
    $badges = [
        'pending' => 'bg-warning',
        'confirmed' => 'bg-info',
        'processing' => 'bg-primary',
        'packed' => 'bg-secondary',
        'shipped' => 'bg-info',
        'in_transit' => 'bg-primary',
        'out_for_delivery' => 'bg-warning',
        'delivered' => 'bg-success',
        'cancelled' => 'bg-danger',
        'returned' => 'bg-dark',
        'refunded' => 'bg-secondary'
    ];
    
    return $badges[$status] ?? 'bg-secondary';
}

function getOrderStatusProgress($status) {
    $progress = [
        'pending' => 10,
        'confirmed' => 20,
        'processing' => 30,
        'packed' => 40,
        'shipped' => 50,
        'in_transit' => 70,
        'out_for_delivery' => 90,
        'delivered' => 100,
        'cancelled' => 0,
        'returned' => 0,
        'refunded' => 0
    ];
    
    return $progress[$status] ?? 0;
}

// ==================== PRODUCT COMPARISON FUNCTIONS ====================

function addToComparison($user_id, $product_id) {
    global $db;
    
    $stmt = $db->prepare("INSERT IGNORE INTO product_comparisons (user_id, product_id) VALUES (?, ?)");
    $stmt->bind_param("ii", $user_id, $product_id);
    return $stmt->execute();
}

function removeFromComparison($user_id, $product_id) {
    global $db;
    
    $stmt = $db->prepare("DELETE FROM product_comparisons WHERE user_id = ? AND product_id = ?");
    $stmt->bind_param("ii", $user_id, $product_id);
    return $stmt->execute();
}

function getComparisonItems($user_id) {
    global $db;
    
    $stmt = $db->prepare("SELECT p.*, c.name as category_name FROM product_comparisons pc JOIN products p ON pc.product_id = p.id JOIN categories c ON p.category_id = c.id WHERE pc.user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function clearComparison($user_id) {
    global $db;
    
    $stmt = $db->prepare("DELETE FROM product_comparisons WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    return $stmt->execute();
}

?>
