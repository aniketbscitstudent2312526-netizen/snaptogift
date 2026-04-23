<?php
require_once 'config.php';

$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$product_id) {
    redirect('products.php');
}

// Get product details
$stmt = $db->prepare("SELECT p.*, c.name as category_name FROM products p JOIN categories c ON p.category_id = c.id WHERE p.id = ?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();

if (!$product) {
    showAlert('Product not found', 'danger');
    redirect('products.php');
}

// Record view for AI recommendations
$user_id = isLoggedIn() ? $_SESSION['user_id'] : null;
recordProductView($user_id, $product_id);

// Get AI recommended products (similar category)
$stmt = $db->prepare("SELECT p.*, c.name as category_name FROM products p 
                      JOIN categories c ON p.category_id = c.id 
                      WHERE p.category_id = ? AND p.id != ? 
                      ORDER BY (p.views + p.purchases * 3) DESC LIMIT 4");
$stmt->bind_param("ii", $product['category_id'], $product_id);
$stmt->execute();
$recommended = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Check if in wishlist
$in_wishlist = isLoggedIn() ? isInWishlist($_SESSION['user_id'], $product_id) : false;

// Check if in comparison
$in_comparison = isLoggedIn() ? (function($uid, $pid) use ($db) { $stmt = $db->prepare("SELECT id FROM product_comparisons WHERE user_id = ? AND product_id = ?"); $stmt->bind_param("ii", $uid, $pid); $stmt->execute(); return $stmt->get_result()->num_rows > 0; })($_SESSION['user_id'], $product_id) : false;

// Handle review submission
$review_error = '';
$review_success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_review']) && isLoggedIn()) {
    $rating = intval($_POST['rating']);
    $title = sanitize($_POST['review_title']);
    $review_text = sanitize($_POST['review_text']);
    $order_id = intval($_POST['order_id'] ?? 0);
    
    if ($rating < 1 || $rating > 5) {
        $review_error = 'Please select a rating';
    } elseif (empty($title)) {
        $review_error = 'Please enter a review title';
    } elseif (empty($review_text)) {
        $review_error = 'Please enter your review';
    } elseif (!hasUserPurchasedProduct($_SESSION['user_id'], $product_id)) {
        $review_error = 'You can only review products you have purchased';
    } elseif (hasUserReviewedProduct($_SESSION['user_id'], $product_id)) {
        $review_error = 'You have already reviewed this product';
    } else {
        if (addReview($_SESSION['user_id'], $product_id, $order_id, $rating, $title, $review_text)) {
            $review_success = 'Thank you for your review!';
            // Refresh product data
            $stmt = $db->prepare("SELECT p.*, c.name as category_name FROM products p JOIN categories c ON p.category_id = c.id WHERE p.id = ?");
            $stmt->bind_param("i", $product_id);
            $stmt->execute();
            $product = $stmt->get_result()->fetch_assoc();
        } else {
            $review_error = 'Failed to submit review';
        }
    }
}

// Add to recently viewed
if (isLoggedIn()) {
    addRecentlyViewed($_SESSION['user_id'], $product_id);
}

// Get reviews
$reviews = getProductReviews($product_id, 10);
$rating_breakdown = getProductRatingBreakdown($product_id);
$can_review = isLoggedIn() && hasUserPurchasedProduct($_SESSION['user_id'], $product_id) && !hasUserReviewedProduct($_SESSION['user_id'], $product_id);

// Get recently viewed (excluding current product)
$recently_viewed = [];
if (isLoggedIn()) {
    $stmt = $db->prepare("SELECT p.* FROM recently_viewed rv JOIN products p ON rv.product_id = p.id WHERE rv.user_id = ? AND rv.product_id != ? ORDER BY rv.viewed_at DESC LIMIT 4");
    $stmt->bind_param("ii", $_SESSION['user_id'], $product_id);
    $stmt->execute();
    $recently_viewed = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

$page_title = $product['name'];
include 'includes/header.php';
?>

<div class="container py-5">
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
            <li class="breadcrumb-item"><a href="products.php?category=<?php echo $product['category_id']; ?>"><?php echo $product['category_name']; ?></a></li>
            <li class="breadcrumb-item active"><?php echo substr($product['name'], 0, 30); ?>...</li>
        </ol>
    </nav>
    
    <div class="row g-5">
        <!-- Product Image -->
        <div class="col-lg-6">
            <div class="card">
                <img src="<?php echo $product['image']; ?>" class="card-img-top" alt="<?php echo $product['name']; ?>" style="max-height: 500px; object-fit: cover;">
            </div>
        </div>
        
        <!-- Product Info -->
        <div class="col-lg-6">
            <span class="badge bg-secondary mb-2"><?php echo $product['category_name']; ?></span>
            <h1 class="mb-3"><?php echo $product['name']; ?></h1>
            
            <div class="d-flex align-items-center mb-3">
                <div class="rating-stars me-2">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                        <i class="bi bi-star<?php echo $i <= round($product['rating']) ? '-fill' : ''; ?>"></i>
                    <?php endfor; ?>
                </div>
                <span class="text-muted">(<?php echo $product['reviews']; ?> reviews)</span>
                <span class="mx-2">|</span>
                <span class="text-muted"><i class="bi bi-eye me-1"></i><?php echo $product['views']; ?> views</span>
            </div>
            
            <h2 class="text-primary mb-4">₹<?php echo number_format($product['price']); ?></h2>
            
            <p class="text-muted mb-4"><?php echo $product['description']; ?></p>
            
            <div class="d-flex align-items-center mb-4">
                <span class="me-3"><strong>Availability:</strong></span>
                <?php if ($product['stock'] > 0): ?>
                <span class="text-success"><i class="bi bi-check-circle-fill me-1"></i><?php echo $product['stock']; ?> in stock</span>
                <?php else: ?>
                <span class="text-danger"><i class="bi bi-x-circle-fill me-1"></i>Out of stock</span>
                <?php endif; ?>
            </div>
            
            <div class="d-flex gap-3 mb-4">
                <div class="input-group" style="width: 150px;">
                    <button class="btn btn-outline-secondary" type="button" onclick="updateQty(-1)"><i class="bi bi-dash"></i></button>
                    <input type="number" id="quantity" class="form-control text-center" value="1" min="1" max="<?php echo $product['stock']; ?>" readonly>
                    <button class="btn btn-outline-secondary" type="button" onclick="updateQty(1)"><i class="bi bi-plus"></i></button>
                </div>
                
                <button onclick="addToCart(<?php echo $product['id']; ?>, document.getElementById('quantity').value)" class="btn btn-primary btn-lg flex-grow-1" <?php echo $product['stock'] == 0 ? 'disabled' : ''; ?>>
                    <i class="bi bi-cart-plus me-2"></i>Add to Cart
                </button>
            </div>
            
            <div class="d-flex gap-2">
                <?php if (isLoggedIn()): ?>
                <a href="wishlist.php?<?php echo $in_wishlist ? 'remove' : 'add'; ?>=<?php echo $product['id']; ?>" class="btn btn-outline-danger">
                    <i class="bi bi-heart<?php echo $in_wishlist ? '-fill' : ''; ?>"></i> <?php echo $in_wishlist ? 'Remove from' : 'Add to'; ?> Wishlist
                </a>
                <a href="compare.php?<?php echo $in_comparison ? 'remove' : 'add'; ?>=<?php echo $product['id']; ?>" class="btn btn-outline-info">
                    <i class="bi bi-<?php echo $in_comparison ? 'check-' : ''; ?>square"></i> <?php echo $in_comparison ? 'Remove from' : 'Add to'; ?> Compare
                </a>
                <?php endif; ?>
                <button class="btn btn-outline-secondary" onclick="shareProduct()">
                    <i class="bi bi-share"></i> Share
                </button>
            </div>
        </div>
    </div>
    
    <!-- Product Tabs -->
    <div class="row mt-5">
        <div class="col-12">
            <ul class="nav nav-tabs" id="productTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="reviews-tab" data-bs-toggle="tab" data-bs-target="#reviews" type="button" role="tab">
                        <i class="bi bi-star-fill"></i> Reviews (<?php echo $product['reviews']; ?>)
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="ratings-tab" data-bs-toggle="tab" data-bs-target="#ratings" type="button" role="tab">
                        <i class="bi bi-bar-chart"></i> Rating Breakdown
                    </button>
                </li>
            </ul>
            <div class="tab-content" id="productTabsContent">
                <!-- Reviews Tab -->
                <div class="tab-pane fade show active" id="reviews" role="tabpanel">
                    <div class="card border-top-0 rounded-0 rounded-bottom">
                        <div class="card-body p-4">
                            <?php if ($review_error): ?>
                            <div class="alert alert-danger"><?php echo $review_error; ?></div>
                            <?php endif; ?>
                            <?php if ($review_success): ?>
                            <div class="alert alert-success"><?php echo $review_success; ?></div>
                            <?php endif; ?>
                            
                            <?php if ($can_review): ?>
                            <div class="mb-4 p-3 bg-light rounded">
                                <h5><i class="bi bi-pencil-square"></i> Write a Review</h5>
                                <form method="POST" action="">
                                    <div class="mb-3">
                                        <label class="form-label">Your Rating</label>
                                        <div class="rating-input">
                                            <?php for ($i = 5; $i >= 1; $i--): ?>
                                            <input type="radio" name="rating" value="<?php echo $i; ?>" id="star<?php echo $i; ?>" required>
                                            <label for="star<?php echo $i; ?>"><i class="bi bi-star-fill"></i></label>
                                            <?php endfor; ?>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Review Title</label>
                                        <input type="text" name="review_title" class="form-control" placeholder="Summarize your experience" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Your Review</label>
                                        <textarea name="review_text" class="form-control" rows="4" placeholder="Tell us what you liked or disliked" required></textarea>
                                    </div>
                                    <button type="submit" name="submit_review" class="btn btn-primary">
                                        <i class="bi bi-send me-2"></i>Submit Review
                                    </button>
                                </form>
                            </div>
                            <?php elseif (!isLoggedIn()): ?>
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle me-2"></i>Please <a href="login.php">login</a> to write a review.
                            </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($reviews)): ?>
                                <?php foreach ($reviews as $review): ?>
                                <div class="review-item border-bottom pb-3 mb-3">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h6 class="mb-1"><?php echo htmlspecialchars($review['title']); ?></h6>
                                            <div class="rating-stars mb-2">
                                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                                    <i class="bi bi-star<?php echo $i <= $review['rating'] ? '-fill text-warning' : ' text-muted'; ?>"></i>
                                                <?php endfor; ?>
                                            </div>
                                        </div>
                                        <small class="text-muted"><?php echo date('M d, Y', strtotime($review['created_at'])); ?></small>
                                    </div>
                                    <p class="mb-2"><?php echo nl2br(htmlspecialchars($review['review'])); ?></p>
                                    <small class="text-muted">By <?php echo htmlspecialchars($review['user_name']); ?></small>
                                </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                            <div class="text-center py-4">
                                <i class="bi bi-chat-square-text display-4 text-muted"></i>
                                <p class="text-muted mt-2">No reviews yet. Be the first to review!</p>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Rating Breakdown Tab -->
                <div class="tab-pane fade" id="ratings" role="tabpanel">
                    <div class="card border-top-0 rounded-0 rounded-bottom">
                        <div class="card-body p-4">
                            <div class="row">
                                <div class="col-md-4 text-center border-end">
                                    <h1 class="display-3 mb-0"><?php echo number_format($product['rating'], 1); ?></h1>
                                    <div class="rating-stars mb-2">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <i class="bi bi-star<?php echo $i <= round($product['rating']) ? '-fill text-warning' : ' text-muted'; ?>"></i>
                                        <?php endfor; ?>
                                    </div>
                                    <p class="text-muted">Based on <?php echo $product['reviews']; ?> reviews</p>
                                </div>
                                <div class="col-md-8">
                                    <?php for ($i = 5; $i >= 1; $i--): 
                                        $count = $rating_breakdown[$i];
                                        $percentage = $product['reviews'] > 0 ? ($count / $product['reviews']) * 100 : 0;
                                    ?>
                                    <div class="d-flex align-items-center mb-2">
                                        <span class="me-2" style="width: 60px;"><?php echo $i; ?> stars</span>
                                        <div class="progress flex-grow-1" style="height: 8px;">
                                            <div class="progress-bar bg-warning" style="width: <?php echo $percentage; ?>%"></div>
                                        </div>
                                        <span class="ms-2" style="width: 40px;"><?php echo $count; ?></span>
                                    </div>
                                    <?php endfor; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Recently Viewed -->
    <?php if (!empty($recently_viewed)): ?>
    <section class="mt-5">
        <h4 class="mb-4"><i class="bi bi-clock-history me-2"></i>Recently Viewed</h4>
        <div class="row g-4">
            <?php foreach ($recently_viewed as $item): ?>
            <div class="col-md-3">
                <div class="card product-card h-100">
                    <a href="product.php?id=<?php echo $item['id']; ?>">
                        <img src="<?php echo $item['image']; ?>" class="card-img-top" alt="<?php echo $item['name']; ?>" style="height: 180px; object-fit: cover;">
                    </a>
                    <div class="card-body">
                        <h6 class="card-title"><?php echo substr($item['name'], 0, 35); ?>...</h6>
                        <span class="price-tag">₹<?php echo number_format($item['price']); ?></span>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>
    
    <!-- AI Recommendations -->
    <?php if (!empty($recommended)): ?>
    <section class="recommendation-section mt-5">
        <div class="d-flex align-items-center mb-4">
            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 48px; height: 48px;">
                <i class="bi bi-stars fs-5"></i>
            </div>
            <div>
                <h3 class="mb-0">You May Also Like</h3>
                <p class="text-muted mb-0">AI-powered recommendations based on this product</p>
            </div>
        </div>
        
        <div class="row g-4">
            <?php foreach ($recommended as $rec): ?>
            <div class="col-md-3">
                <div class="card product-card h-100">
                    <span class="badge-recommend position-absolute top-0 end-0 m-2">
                        <i class="bi bi-magic"></i> AI Pick
                    </span>
                    <a href="product.php?id=<?php echo $rec['id']; ?>">
                        <img src="<?php echo $rec['image']; ?>" class="card-img-top" alt="<?php echo $rec['name']; ?>">
                    </a>
                    <div class="card-body d-flex flex-column">
                        <small class="text-muted"><?php echo $rec['category_name']; ?></small>
                        <h6 class="card-title mt-1">
                            <a href="product.php?id=<?php echo $rec['id']; ?>" class="text-dark text-decoration-none">
                                <?php echo substr($rec['name'], 0, 40); ?>...
                            </a>
                        </h6>
                        <div class="mt-auto d-flex justify-content-between align-items-center">
                            <span class="price-tag">₹<?php echo number_format($rec['price']); ?></span>
                            <button onclick="addToCart(<?php echo $rec['id']; ?>)" class="btn btn-primary btn-sm">
                                <i class="bi bi-cart-plus"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>
</div>

<style>
.rating-input {
    display: flex;
    flex-direction: row-reverse;
    justify-content: flex-end;
}
.rating-input input {
    display: none;
}
.rating-input label {
    cursor: pointer;
    font-size: 1.5rem;
    color: #ddd;
    margin-right: 5px;
}
.rating-input input:checked ~ label,
.rating-input label:hover,
.rating-input label:hover ~ label {
    color: #ffc107;
}
</style>

<script>
function updateQty(change) {
    const input = document.getElementById('quantity');
    let val = parseInt(input.value) + change;
    const max = parseInt(input.max);
    if (val >= 1 && val <= max) {
        input.value = val;
    }
}

function shareProduct() {
    if (navigator.share) {
        navigator.share({
            title: '<?php echo addslashes($product['name']); ?>',
            text: 'Check out this product on AI Shop!',
            url: window.location.href
        });
    } else {
        // Copy to clipboard
        navigator.clipboard.writeText(window.location.href);
        showToast('Link copied to clipboard!', 'success');
    }
}
</script>

<?php include 'includes/footer.php'; ?>
