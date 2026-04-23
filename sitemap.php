<?php
require_once 'config.php';

// Get all categories for the sitemap
$categories = $db->query("SELECT * FROM categories ORDER BY name");

$page_title = 'Sitemap';
include 'includes/header.php';
?>

<div class="container py-5">
    <div class="text-center mb-5">
        <h1 class="display-5 fw-bold mb-3">Sitemap</h1>
        <p class="lead text-muted">Find your way around SnapToGift</p>
    </div>
    
    <div class="row g-4">
        <!-- Main Pages -->
        <div class="col-md-4">
            <div class="card h-100 shadow-sm">
                <div class="card-body p-4">
                    <h4 class="mb-4 text-danger"><i class="bi bi-house-door me-2"></i>Main Pages</h4>
                    <ul class="list-unstyled">
                        <li class="mb-2">
                            <a href="index.php" class="text-decoration-none"><i class="bi bi-chevron-right me-2 text-muted"></i>Home</a>
                        </li>
                        <li class="mb-2">
                            <a href="products.php" class="text-decoration-none"><i class="bi bi-chevron-right me-2 text-muted"></i>All Products</a>
                        </li>
                        <li class="mb-2">
                            <a href="search.php" class="text-decoration-none"><i class="bi bi-chevron-right me-2 text-muted"></i>Search</a>
                        </li>
                        <li class="mb-2">
                            <a href="cart.php" class="text-decoration-none"><i class="bi bi-chevron-right me-2 text-muted"></i>Shopping Cart</a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        
        <!-- User Account -->
        <div class="col-md-4">
            <div class="card h-100 shadow-sm">
                <div class="card-body p-4">
                    <h4 class="mb-4 text-danger"><i class="bi bi-person me-2"></i>User Account</h4>
                    <ul class="list-unstyled">
                        <li class="mb-2">
                            <a href="login.php" class="text-decoration-none"><i class="bi bi-chevron-right me-2 text-muted"></i>Login</a>
                        </li>
                        <li class="mb-2">
                            <a href="register.php" class="text-decoration-none"><i class="bi bi-chevron-right me-2 text-muted"></i>Register</a>
                        </li>
                        <li class="mb-2">
                            <a href="profile.php" class="text-decoration-none"><i class="bi bi-chevron-right me-2 text-muted"></i>My Profile</a>
                        </li>
                        <li class="mb-2">
                            <a href="orders.php" class="text-decoration-none"><i class="bi bi-chevron-right me-2 text-muted"></i>My Orders</a>
                        </li>
                        <li class="mb-2">
                            <a href="wishlist.php" class="text-decoration-none"><i class="bi bi-chevron-right me-2 text-muted"></i>My Wishlist</a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        
        <!-- Support -->
        <div class="col-md-4">
            <div class="card h-100 shadow-sm">
                <div class="card-body p-4">
                    <h4 class="mb-4 text-danger"><i class="bi bi-headset me-2"></i>Support</h4>
                    <ul class="list-unstyled">
                        <li class="mb-2">
                            <a href="help.php" class="text-decoration-none"><i class="bi bi-chevron-right me-2 text-muted"></i>Help Center</a>
                        </li>
                        <li class="mb-2">
                            <a href="contact.php" class="text-decoration-none"><i class="bi bi-chevron-right me-2 text-muted"></i>Contact Us</a>
                        </li>
                        <li class="mb-2">
                            <a href="help.php#returns" class="text-decoration-none"><i class="bi bi-chevron-right me-2 text-muted"></i>Returns Policy</a>
                        </li>
                        <li class="mb-2">
                            <a href="privacy.php" class="text-decoration-none"><i class="bi bi-chevron-right me-2 text-muted"></i>Privacy Policy</a>
                        </li>
                        <li class="mb-2">
                            <a href="terms.php" class="text-decoration-none"><i class="bi bi-chevron-right me-2 text-muted"></i>Terms of Service</a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        
        <!-- Categories -->
        <div class="col-md-6">
            <div class="card h-100 shadow-sm">
                <div class="card-body p-4">
                    <h4 class="mb-4 text-danger"><i class="bi bi-grid me-2"></i>Categories</h4>
                    <div class="row">
                        <?php 
                        $cats = $db->query("SELECT * FROM categories ORDER BY name");
                        while ($cat = $cats->fetch_assoc()): 
                        ?>
                        <div class="col-sm-6 mb-2">
                            <a href="products.php?category=<?php echo $cat['id']; ?>" class="text-decoration-none">
                                <i class="bi bi-chevron-right me-2 text-muted"></i><?php echo $cat['name']; ?>
                            </a>
                        </div>
                        <?php endwhile; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Admin -->
        <div class="col-md-6">
            <div class="card h-100 shadow-sm">
                <div class="card-body p-4">
                    <h4 class="mb-4 text-danger"><i class="bi bi-shield-lock me-2"></i>Admin Panel</h4>
                    <ul class="list-unstyled">
                        <li class="mb-2">
                            <a href="admin/login.php" class="text-decoration-none"><i class="bi bi-chevron-right me-2 text-muted"></i>Admin Login</a>
                        </li>
                        <li class="mb-2">
                            <a href="admin/index.php" class="text-decoration-none"><i class="bi bi-chevron-right me-2 text-muted"></i>Dashboard</a>
                        </li>
                        <li class="mb-2">
                            <a href="admin/products.php" class="text-decoration-none"><i class="bi bi-chevron-right me-2 text-muted"></i>Manage Products</a>
                        </li>
                        <li class="mb-2">
                            <a href="admin/orders.php" class="text-decoration-none"><i class="bi bi-chevron-right me-2 text-muted"></i>Manage Orders</a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Quick Links -->
    <div class="card mt-5 shadow-sm">
        <div class="card-body p-4 text-center">
            <h5 class="mb-3">Need Help Finding Something?</h5>
            <div class="d-flex flex-wrap justify-content-center gap-2">
                <a href="contact.php" class="btn btn-outline-danger"><i class="bi bi-envelope me-2"></i>Contact Us</a>
                <a href="help.php" class="btn btn-outline-danger"><i class="bi bi-question-circle me-2"></i>Help Center</a>
                <a href="search.php" class="btn btn-outline-danger"><i class="bi bi-search me-2"></i>Search</a>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
