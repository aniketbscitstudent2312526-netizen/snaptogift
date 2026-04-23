<?php
require_once 'config.php';
requireLogin();

$error = '';
$success = '';

// Handle add/remove from comparison
if (isset($_GET['add'])) {
    $product_id = intval($_GET['add']);
    if (addToComparison($_SESSION['user_id'], $product_id)) {
        $success = 'Product added to comparison';
    } else {
        $error = 'Product already in comparison';
    }
    redirect('compare.php');
}

if (isset($_GET['remove'])) {
    $product_id = intval($_GET['remove']);
    removeFromComparison($_SESSION['user_id'], $product_id);
    redirect('compare.php');
}

if (isset($_GET['clear'])) {
    clearComparison($_SESSION['user_id']);
    redirect('compare.php');
}

// Get comparison items
$products = getComparisonItems($_SESSION['user_id']);

$page_title = 'Compare Products';
include 'includes/header.php';
?>

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2><i class="bi bi-grid-3x3-gap me-2"></i>Compare Products</h2>
            <p class="text-muted mb-0">Compare features and prices side by side</p>
        </div>
        <?php if (count($products) > 0): ?>
        <a href="compare.php?clear=1" class="btn btn-outline-danger">
            <i class="bi bi-trash me-2"></i>Clear All
        </a>
        <?php endif; ?>
    </div>
    
    <?php if ($error): ?>
    <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
    <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>
    
    <?php if (empty($products)): ?>
    <div class="text-center py-5">
        <div class="mb-4">
            <i class="bi bi-grid-3x3-gap display-1 text-muted"></i>
        </div>
        <h4 class="text-muted">No products to compare</h4>
        <p class="text-muted">Add products from the product page to compare them here.</p>
        <a href="products.php" class="btn btn-primary btn-lg">
            <i class="bi bi-shop me-2"></i>Browse Products
        </a>
    </div>
    <?php elseif (count($products) == 1): ?>
    <div class="alert alert-info">
        <i class="bi bi-info-circle me-2"></i>Add at least one more product to compare.
    </div>
    <div class="table-responsive">
        <table class="table table-bordered">
            <tr>
                <th style="width: 20%;">Product</th>
                <td>
                    <div class="text-center">
                        <img src="<?php echo $products[0]['image']; ?>" alt="" style="max-height: 150px;">
                        <h5 class="mt-2"><?php echo $products[0]['name']; ?></h5>
                        <a href="product.php?id=<?php echo $products[0]['id']; ?>" class="btn btn-sm btn-primary">View</a>
                        <a href="compare.php?remove=<?php echo $products[0]['id']; ?>" class="btn btn-sm btn-outline-danger">Remove</a>
                    </div>
                </td>
            </tr>
        </table>
    </div>
    <?php else: ?>
    <div class="table-responsive">
        <table class="table table-bordered table-hover">
            <thead class="table-light">
                <tr>
                    <th style="width: 15%;">Feature</th>
                    <?php foreach ($products as $product): ?>
                    <th class="text-center">
                        <a href="compare.php?remove=<?php echo $product['id']; ?>" class="btn btn-sm btn-outline-danger float-end">
                            <i class="bi bi-x"></i>
                        </a>
                    </th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="fw-bold">Image</td>
                    <?php foreach ($products as $product): ?>
                    <td class="text-center">
                        <img src="<?php echo $product['image']; ?>" alt="<?php echo $product['name']; ?>" style="max-height: 150px; object-fit: cover;">
                    </td>
                    <?php endforeach; ?>
                </tr>
                <tr>
                    <td class="fw-bold">Name</td>
                    <?php foreach ($products as $product): ?>
                    <td>
                        <h6><a href="product.php?id=<?php echo $product['id']; ?>" class="text-decoration-none"><?php echo $product['name']; ?></a></h6>
                    </td>
                    <?php endforeach; ?>
                </tr>
                <tr>
                    <td class="fw-bold">Price</td>
                    <?php foreach ($products as $product): ?>
                    <td>
                        <h5 class="text-primary mb-0">₹<?php echo number_format($product['price']); ?></h5>
                    </td>
                    <?php endforeach; ?>
                </tr>
                <tr>
                    <td class="fw-bold">Category</td>
                    <?php foreach ($products as $product): ?>
                    <td>
                        <span class="badge bg-secondary"><?php echo $product['category_name']; ?></span>
                    </td>
                    <?php endforeach; ?>
                </tr>
                <tr>
                    <td class="fw-bold">Rating</td>
                    <?php foreach ($products as $product): ?>
                    <td>
                        <div class="rating-stars">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <i class="bi bi-star<?php echo $i <= round($product['rating']) ? '-fill text-warning' : ' text-muted'; ?>"></i>
                            <?php endfor; ?>
                            <small class="text-muted">(<?php echo $product['reviews']; ?>)</small>
                        </div>
                    </td>
                    <?php endforeach; ?>
                </tr>
                <tr>
                    <td class="fw-bold">Stock</td>
                    <?php foreach ($products as $product): ?>
                    <td>
                        <?php if ($product['stock'] > 0): ?>
                        <span class="text-success"><i class="bi bi-check-circle me-1"></i><?php echo $product['stock']; ?> available</span>
                        <?php else: ?>
                        <span class="text-danger"><i class="bi bi-x-circle me-1"></i>Out of stock</span>
                        <?php endif; ?>
                    </td>
                    <?php endforeach; ?>
                </tr>
                <tr>
                    <td class="fw-bold">Description</td>
                    <?php foreach ($products as $product): ?>
                    <td>
                        <small class="text-muted"><?php echo substr($product['description'], 0, 100); ?>...</small>
                    </td>
                    <?php endforeach; ?>
                </tr>
                <tr>
                    <td class="fw-bold">Actions</td>
                    <?php foreach ($products as $product): ?>
                    <td class="text-center">
                        <button onclick="addToCart(<?php echo $product['id']; ?>)" class="btn btn-primary btn-sm mb-2 w-100" <?php echo $product['stock'] == 0 ? 'disabled' : ''; ?>>
                            <i class="bi bi-cart-plus me-1"></i>Add to Cart
                        </button>
                        <a href="product.php?id=<?php echo $product['id']; ?>" class="btn btn-outline-primary btn-sm w-100">
                            <i class="bi bi-eye me-1"></i>View Details
                        </a>
                    </td>
                    <?php endforeach; ?>
                </tr>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
