<?php
require_once 'config.php';

$query = isset($_GET['q']) ? sanitize($_GET['q']) : '';
$category_id = isset($_GET['category']) ? intval($_GET['category']) : 0;

$results = [];
if ($query) {
    $results = searchProducts($query, $category_id ?: null);
}

$page_title = 'Search: ' . $query;
include 'includes/header.php';
?>

<div class="container py-5">
    <h2 class="mb-4"><i class="bi bi-search me-2"></i>Search Results</h2>
    
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-8">
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="text" name="q" class="form-control form-control-lg" placeholder="Search products..." value="<?php echo $query; ?>" required>
                    </div>
                </div>
                <div class="col-md-3">
                    <select name="category" class="form-select form-select-lg">
                        <option value="0">All Categories</option>
                        <?php
                        $cats = $db->query("SELECT * FROM categories ORDER BY name");
                        while ($cat = $cats->fetch_assoc()):
                        ?>
                        <option value="<?php echo $cat['id']; ?>" <?php echo $category_id == $cat['id'] ? 'selected' : ''; ?>><?php echo $cat['name']; ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="col-md-1">
                    <button type="submit" class="btn btn-primary btn-lg w-100">
                        <i class="bi bi-search"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <?php if (empty($query)): ?>
    <div class="text-center py-5">
        <i class="bi bi-search display-1 text-muted"></i>
        <h4 class="mt-3">Enter a search term</h4>
        <p class="text-muted">Search for products by name or description</p>
    </div>
    <?php elseif (empty($results)): ?>
    <div class="text-center py-5">
        <i class="bi bi-inbox display-1 text-muted"></i>
        <h4 class="mt-3">No results found</h4>
        <p class="text-muted">We couldn't find any products matching "<?php echo $query; ?>"</p>
        <a href="products.php" class="btn btn-primary">
            <i class="bi bi-shop me-2"></i>Browse All Products
        </a>
    </div>
    <?php else: ?>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <p class="text-muted mb-0">Found <?php echo count($results); ?> result(s) for "<?php echo $query; ?>"</p>
    </div>
    
    <div class="row g-4">
        <?php foreach ($results as $product): ?>
        <div class="col-md-6 col-lg-4 col-xl-3">
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
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
