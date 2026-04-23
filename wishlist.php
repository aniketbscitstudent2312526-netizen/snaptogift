<?php
require_once 'config.php';
requireLogin();

// Handle add/remove
if (isset($_GET['add'])) {
    $product_id = intval($_GET['add']);
    addToWishlist($_SESSION['user_id'], $product_id);
    showAlert('Added to wishlist', 'success');
    redirect('wishlist.php');
}

if (isset($_GET['remove'])) {
    $product_id = intval($_GET['remove']);
    removeFromWishlist($_SESSION['user_id'], $product_id);
    showAlert('Removed from wishlist', 'success');
    redirect('wishlist.php');
}

// Get wishlist items
$items = getWishlistItems($_SESSION['user_id']);

$page_title = 'My Wishlist';
include 'includes/header.php';
?>

<div class="container py-5">
    <h2 class="mb-4"><i class="bi bi-heart me-2"></i>My Wishlist (<?php echo count($items); ?>)</h2>
    
    <?php if (empty($items)): ?>
    <div class="text-center py-5">
        <i class="bi bi-heart display-1 text-muted"></i>
        <h3 class="mt-3">Your wishlist is empty</h3>
        <p class="text-muted">Save items you love to your wishlist</p>
        <a href="products.php" class="btn btn-primary btn-lg">
            <i class="bi bi-shop me-2"></i>Explore Products
        </a>
    </div>
    <?php else: ?>
    <div class="row g-4">
        <?php foreach ($items as $item): ?>
        <div class="col-md-6 col-lg-4 col-xl-3">
            <div class="card product-card h-100">
                <a href="product.php?id=<?php echo $item['product_id']; ?>">
                    <img src="<?php echo $item['image']; ?>" class="card-img-top" alt="<?php echo $item['name']; ?>">
                </a>
                <div class="card-body d-flex flex-column">
                    <h6 class="card-title">
                        <a href="product.php?id=<?php echo $item['product_id']; ?>" class="text-dark text-decoration-none">
                            <?php echo substr($item['name'], 0, 45); ?><?php echo strlen($item['name']) > 45 ? '...' : ''; ?>
                        </a>
                    </h6>
                    <div class="mt-auto">
                        <span class="price-tag d-block mb-2">₹<?php echo number_format($item['price']); ?></span>
                        <?php if ($item['stock'] > 0): ?>
                        <button onclick="addToCart(<?php echo $item['product_id']; ?>)" class="btn btn-primary w-100 btn-sm">
                            <i class="bi bi-cart-plus me-2"></i>Add to Cart
                        </button>
                        <?php else: ?>
                        <button class="btn btn-secondary w-100 btn-sm" disabled>Out of Stock</button>
                        <?php endif; ?>
                        <a href="wishlist.php?remove=<?php echo $item['product_id']; ?>" class="btn btn-outline-danger w-100 btn-sm mt-2">
                            <i class="bi bi-trash me-2"></i>Remove
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
