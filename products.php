<?php
require_once 'config.php';

// Get parameters
$category_id = isset($_GET['category']) ? intval($_GET['category']) : 0;
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'newest';
$search = isset($_GET['q']) ? sanitize($_GET['q']) : '';

// Build query
if ($search) {
    $products_list = searchProducts($search, $category_id ?: null);
    $page_title = 'Search: ' . $search;
} elseif ($category_id) {
    $products_list = getProductsByCategory($category_id, $sort);
    // Get category name
    $stmt = $db->prepare("SELECT name FROM categories WHERE id = ?");
    $stmt->bind_param("i", $category_id);
    $stmt->execute();
    $cat_result = $stmt->get_result()->fetch_assoc();
    $page_title = $cat_result['name'] ?? 'Products';
} else {
    // All products
    $order_by = "p.created_at DESC";
    switch ($sort) {
        case 'price_low': $order_by = "p.price ASC"; break;
        case 'price_high': $order_by = "p.price DESC"; break;
        case 'popular': $order_by = "(p.views + p.purchases) DESC"; break;
        case 'rating': $order_by = "p.rating DESC"; break;
    }
    $result = $db->query("SELECT p.*, c.name as category_name FROM products p JOIN categories c ON p.category_id = c.id ORDER BY $order_by");
    $products_list = $result->fetch_all(MYSQLI_ASSOC);
    $page_title = 'All Products';
}

// Get all categories for filter
$categories = $db->query("SELECT * FROM categories ORDER BY name");

include 'includes/header.php';
?>

<div class="container py-5">
    <div class="row">
        <!-- Sidebar Filters -->
        <div class="col-lg-3 mb-4">
            <div class="card sticky-top" style="top: 100px; z-index: 100;">
                <div class="card-body">
                    <h5 class="mb-3"><i class="bi bi-funnel me-2"></i>Filters</h5>
                    
                    <form method="GET" action="">
                        <?php if ($search): ?>
                        <input type="hidden" name="q" value="<?php echo $search; ?>">
                        <?php endif; ?>
                        
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Categories</label>
                            <div class="list-group list-group-flush">
                                <a href="?<?php echo $search ? 'q=' . urlencode($search) . '&' : ''; ?>sort=<?php echo $sort; ?>" 
                                   class="list-group-item list-group-item-action <?php echo !$category_id ? 'active' : ''; ?>">
                                    All Categories
                                </a>
                                <?php while ($cat = $categories->fetch_assoc()): ?>
                                <a href="?category=<?php echo $cat['id']; ?><?php echo $search ? '&q=' . urlencode($search) : ''; ?>&sort=<?php echo $sort; ?>" 
                                   class="list-group-item list-group-item-action <?php echo $category_id == $cat['id'] ? 'active' : ''; ?>">
                                    <?php echo $cat['name']; ?>
                                </a>
                                <?php endwhile; ?>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Sort By</label>
                            <select name="sort" class="form-select" onchange="this.form.submit()">
                                <option value="newest" <?php echo $sort == 'newest' ? 'selected' : ''; ?>>Newest First</option>
                                <option value="price_low" <?php echo $sort == 'price_low' ? 'selected' : ''; ?>>Price: Low to High</option>
                                <option value="price_high" <?php echo $sort == 'price_high' ? 'selected' : ''; ?>>Price: High to Low</option>
                                <option value="popular" <?php echo $sort == 'popular' ? 'selected' : ''; ?>>Most Popular</option>
                                <option value="rating" <?php echo $sort == 'rating' ? 'selected' : ''; ?>>Highest Rated</option>
                            </select>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Product Grid -->
        <div class="col-lg-9">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0"><?php echo $page_title; ?></h2>
                <span class="text-muted"><?php echo count($products_list); ?> products found</span>
            </div>
            
            <?php if (empty($products_list)): ?>
            <div class="text-center py-5">
                <i class="bi bi-search display-1 text-muted"></i>
                <h4 class="mt-3">No products found</h4>
                <p class="text-muted">Try adjusting your filters or search query</p>
                <a href="products.php" class="btn btn-primary">View All Products</a>
            </div>
            <?php else: ?>
            <div class="row g-4">
                <?php foreach ($products_list as $product): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card product-card h-100">
                        <a href="product.php?id=<?php echo $product['id']; ?>">
                            <img src="<?php echo $product['image']; ?>" class="card-img-top" alt="<?php echo $product['name']; ?>">
                        </a>
                        <div class="card-body d-flex flex-column">
                            <small class="text-muted"><?php echo $product['category_name']; ?></small>
                            <h6 class="card-title mt-1">
                                <a href="product.php?id=<?php echo $product['id']; ?>" class="text-dark text-decoration-none">
                                    <?php echo substr($product['name'], 0, 50); ?><?php echo strlen($product['name']) > 50 ? '...' : ''; ?>
                                </a>
                            </h6>
                            <p class="text-muted small mb-2"><?php echo substr($product['description'], 0, 60); ?>...</p>
                            
                            <div class="rating-stars mb-2">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <i class="bi bi-star<?php echo $i <= round($product['rating']) ? '-fill' : ''; ?>"></i>
                                <?php endfor; ?>
                                <small class="text-muted">(<?php echo $product['reviews']; ?>)</small>
                            </div>
                            
                            <div class="mt-auto d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="price-tag">₹<?php echo number_format($product['price']); ?></span>
                                    <?php if ($product['stock'] < 10): ?>
                                    <br><small class="text-danger stock-badge"><i class="bi bi-exclamation-circle"></i> Only <?php echo $product['stock']; ?> left</small>
                                    <?php endif; ?>
                                </div>
                                <div class="d-flex gap-2">
                                    <?php if (isLoggedIn()): ?>
                                    <a href="wishlist.php?add=<?php echo $product['id']; ?>" class="btn btn-outline-danger btn-sm" title="Add to Wishlist">
                                        <i class="bi bi-heart"></i>
                                    </a>
                                    <?php endif; ?>
                                    <button onclick="addToCart(<?php echo $product['id']; ?>)" class="btn btn-primary btn-sm" title="Add to Cart">
                                        <i class="bi bi-cart-plus"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
