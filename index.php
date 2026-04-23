<?php
$page_title = 'Home';
require_once 'config.php';

// Get AI recommended products
$recommended = [];
if (isLoggedIn()) {
    $recommended = getRecommendedProducts($_SESSION['user_id'], 6);
} else {
    $recommended = getPopularProducts(6);
}

// Get trending products
$trending = getTrendingProducts(4);

// Get popular products
$popular = getPopularProducts(8);

// Get all categories with product counts
$categories = $db->query("SELECT c.*, COUNT(p.id) as product_count FROM categories c LEFT JOIN products p ON c.id = p.category_id GROUP BY c.id ORDER BY c.name");
?>

<?php include 'includes/header.php'; ?>

<!-- Hero Section -->
<section class="hero-section">
    <div class="container position-relative">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <div class="ai-badge">
                    <i class="bi bi-stars"></i> AI-Powered Shopping
                </div>
                <h1 class="display-4 fw-bold mb-4">Smart Shopping with AI Recommendations</h1>
                <p class="lead mb-4 opacity-75">Discover products tailored just for you. Our AI analyzes your preferences to show you the perfect items.</p>
                <div class="d-flex gap-3">
                    <a href="products.php" class="btn btn-light btn-lg px-4">Explore Products</a>
                    <a href="#recommendations" class="btn btn-outline-light btn-lg px-4">
                        <i class="bi bi-magic me-2"></i>AI Recommendations
                    </a>
                </div>
                
                <!-- Stats -->
                <div class="row mt-5 g-4">
                    <div class="col-4">
                        <h3 class="fw-bold mb-1">10K+</h3>
                        <small class="opacity-75">Products</small>
                    </div>
                    <div class="col-4">
                        <h3 class="fw-bold mb-1">5K+</h3>
                        <small class="opacity-75">Happy Customers</small>
                    </div>
                    <div class="col-4">
                        <h3 class="fw-bold mb-1">AI</h3>
                        <small class="opacity-75">Powered</small>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 text-center d-none d-lg-block">
                <img src="https://images.unsplash.com/photo-1607082348824-0a96f2a4b9da?w=600" alt="AI Shopping" class="img-fluid rounded-4 shadow-lg" style="max-height: 400px;">
            </div>
        </div>
    </div>
</section>

<!-- Categories Section -->
<section class="py-5">
    <div class="container">
        <h2 class="text-center mb-4">Shop by Category</h2>
        <div class="d-flex flex-wrap justify-content-center gap-3">
            <?php while ($cat = $categories->fetch_assoc()): ?>
            <a href="products.php?category=<?php echo $cat['id']; ?>" class="category-chip">
                <?php echo $cat['name']; ?> (<?php echo $cat['product_count']; ?>)
            </a>
            <?php endwhile; ?>
        </div>
    </div>
</section>

<!-- AI Recommendations Section -->
<?php if (isLoggedIn() && !empty($recommended)): ?>
<section id="recommendations" class="recommendation-section">
    <div class="container">
        <div class="d-flex align-items-center mb-4">
            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 48px; height: 48px;">
                <i class="bi bi-stars fs-5"></i>
            </div>
            <div>
                <h3 class="mb-0">Recommended for You</h3>
                <p class="text-muted mb-0">Based on your browsing history</p>
            </div>
        </div>
        
        <div class="row g-4">
            <?php foreach ($recommended as $product): ?>
            <div class="col-md-6 col-lg-4 col-xl-2">
                <div class="card product-card h-100">
                    <span class="badge-recommend position-absolute top-0 end-0 m-2">
                        <i class="bi bi-magic"></i> AI Pick
                    </span>
                    <img src="<?php echo $product['image']; ?>" class="card-img-top" alt="<?php echo $product['name']; ?>">
                    <div class="card-body d-flex flex-column">
                        <small class="text-muted"><?php echo $product['category_name']; ?></small>
                        <h6 class="card-title mt-1"><?php echo substr($product['name'], 0, 40); ?><?php echo strlen($product['name']) > 40 ? '...' : ''; ?></h6>
                        <div class="rating-stars mb-2">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <i class="bi bi-star<?php echo $i <= round($product['rating']) ? '-fill' : ''; ?>"></i>
                            <?php endfor; ?>
                            <small class="text-muted">(<?php echo $product['reviews']; ?>)</small>
                        </div>
                        <div class="mt-auto d-flex justify-content-between align-items-center">
                            <span class="price-tag">₹<?php echo number_format($product['price']); ?></span>
                            <button onclick="addToCart(<?php echo $product['id']; ?>)" class="btn btn-primary btn-sm">
                                <i class="bi bi-cart-plus"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Popular Products Section -->
<section class="py-5">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Popular Products</h2>
            <a href="products.php?sort=popular" class="btn btn-outline-primary">View All <i class="bi bi-arrow-right"></i></a>
        </div>
        
        <div class="row g-4">
            <?php foreach ($popular as $product): ?>
            <div class="col-md-6 col-lg-4 col-xl-3">
                <div class="card product-card h-100">
                    <a href="product.php?id=<?php echo $product['id']; ?>" class="text-decoration-none">
                        <img src="<?php echo $product['image']; ?>" class="card-img-top" alt="<?php echo $product['name']; ?>">
                    </a>
                    <div class="card-body d-flex flex-column">
                        <small class="text-muted"><?php echo $product['category_name']; ?></small>
                        <h6 class="card-title mt-1">
                            <a href="product.php?id=<?php echo $product['id']; ?>" class="text-dark text-decoration-none">
                                <?php echo substr($product['name'], 0, 45); ?><?php echo strlen($product['name']) > 45 ? '...' : ''; ?>
                            </a>
                        </h6>
                        <div class="rating-stars mb-2">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <i class="bi bi-star<?php echo $i <= round($product['rating']) ? '-fill' : ''; ?>"></i>
                            <?php endfor; ?>
                            <small class="text-muted">(<?php echo $product['reviews']; ?>)</small>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mt-auto">
                            <span class="price-tag">₹<?php echo number_format($product['price']); ?></span>
                            <div class="d-flex gap-2">
                                <?php if (isLoggedIn()): ?>
                                <a href="wishlist.php?add=<?php echo $product['id']; ?>" class="btn btn-outline-danger btn-sm">
                                    <i class="bi bi-heart"></i>
                                </a>
                                <?php endif; ?>
                                <button onclick="addToCart(<?php echo $product['id']; ?>)" class="btn btn-primary btn-sm">
                                    <i class="bi bi-cart-plus"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Trending Section -->
<section class="py-5 bg-white">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="mb-0">Trending Now</h2>
                <p class="text-muted">Most viewed products this week</p>
            </div>
            <div class="d-flex gap-2">
                <button class="btn btn-outline-secondary" id="prevTrend"><i class="bi bi-chevron-left"></i></button>
                <button class="btn btn-outline-secondary" id="nextTrend"><i class="bi bi-chevron-right"></i></button>
            </div>
        </div>
        
        <div class="row g-4" id="trendingContainer">
            <?php foreach ($trending as $product): ?>
            <div class="col-md-3">
                <div class="card product-card h-100 border-primary border-2">
                    <div class="position-absolute top-0 start-0 m-2">
                        <span class="badge bg-danger"><i class="bi bi-fire"></i> Trending</span>
                    </div>
                    <img src="<?php echo $product['image']; ?>" class="card-img-top" alt="<?php echo $product['name']; ?>">
                    <div class="card-body">
                        <small class="text-muted"><?php echo $product['category_name']; ?></small>
                        <h6 class="card-title"><?php echo substr($product['name'], 0, 40); ?>...</h6>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="price-tag">₹<?php echo number_format($product['price']); ?></span>
                            <small class="text-success"><i class="bi bi-eye"></i> <?php echo $product['views']; ?> views</small>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="py-5">
    <div class="container">
        <div class="row g-4">
            <div class="col-md-3 text-center">
                <div class="p-4">
                    <div class="bg-primary bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                        <i class="bi bi-cpu text-primary fs-2"></i>
                    </div>
                    <h5>AI Recommendations</h5>
                    <p class="text-muted">Smart product suggestions based on your interests</p>
                </div>
            </div>
            <div class="col-md-3 text-center">
                <div class="p-4">
                    <div class="bg-success bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                        <i class="bi bi-truck text-success fs-2"></i>
                    </div>
                    <h5>Fast Delivery</h5>
                    <p class="text-muted">Quick and reliable shipping to your doorstep</p>
                </div>
            </div>
            <div class="col-md-3 text-center">
                <div class="p-4">
                    <div class="bg-warning bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                        <i class="bi bi-shield-check text-warning fs-2"></i>
                    </div>
                    <h5>Secure Payment</h5>
                    <p class="text-muted">100% secure payment methods</p>
                </div>
            </div>
            <div class="col-md-3 text-center">
                <div class="p-4">
                    <div class="bg-info bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                        <i class="bi bi-headset text-info fs-2"></i>
                    </div>
                    <h5>24/7 Support</h5>
                    <p class="text-muted">Round the clock customer service</p>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
