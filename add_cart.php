<?php
/**
 * AJAX Handler for Adding Items to Cart
 */

require_once 'config.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => '', 'cart_count' => 0];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = intval($_POST['product_id'] ?? 0);
    $quantity = intval($_POST['quantity'] ?? 1);
    
    if ($product_id <= 0) {
        $response['message'] = 'Invalid product';
        echo json_encode($response);
        exit;
    }
    
    // Check if product exists and has stock
    $stmt = $db->prepare("SELECT stock FROM products WHERE id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        $response['message'] = 'Product not found';
        echo json_encode($response);
        exit;
    }
    
    $product = $result->fetch_assoc();
    if ($product['stock'] < $quantity) {
        $response['message'] = 'Not enough stock available';
        echo json_encode($response);
        exit;
    }
    
    if (isLoggedIn()) {
        // Database cart
        if (addToCartDB($_SESSION['user_id'], $product_id, $quantity)) {
            $response['success'] = true;
            $response['message'] = 'Added to cart';
        } else {
            $response['message'] = 'Failed to add to cart';
        }
    } else {
        // Session cart
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
        
        if (isset($_SESSION['cart'][$product_id])) {
            $_SESSION['cart'][$product_id] += $quantity;
        } else {
            $_SESSION['cart'][$product_id] = $quantity;
        }
        
        $response['success'] = true;
        $response['message'] = 'Added to cart';
    }
    
    $response['cart_count'] = getCartCount();
}

echo json_encode($response);
?>
