<?php
require_once 'config.php';
requireLogin();

$user = getCurrentUser();
$error = '';
$success = '';

// Get recently viewed products
$recently_viewed = getRecentlyViewed($_SESSION['user_id'], 4);

// Get user's reviews
$stmt = $db->prepare("SELECT r.*, p.name as product_name, p.image as product_image, p.id as product_id FROM reviews r JOIN products p ON r.product_id = p.id WHERE r.user_id = ? ORDER BY r.created_at DESC");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$user_reviews = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name']);
    $phone = sanitize($_POST['phone']);
    $address = sanitize($_POST['address']);
    
    if (empty($name)) {
        $error = 'Name is required';
    } else {
        $stmt = $db->prepare("UPDATE users SET name = ?, phone = ?, address = ? WHERE id = ?");
        $stmt->bind_param("sssi", $name, $phone, $address, $_SESSION['user_id']);
        
        if ($stmt->execute()) {
            $success = 'Profile updated successfully';
            $_SESSION['user_name'] = $name;
            $user = getCurrentUser();
        } else {
            $error = 'Failed to update profile';
        }
    }
    
    // Change password
    if (!empty($_POST['current_password']) && !empty($_POST['new_password'])) {
        if (password_verify($_POST['current_password'], $user['password'] ?? '')) {
            if (strlen($_POST['new_password']) >= 6) {
                $new_hash = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
                $stmt = $db->prepare("UPDATE users SET password = ? WHERE id = ?");
                $stmt->bind_param("si", $new_hash, $_SESSION['user_id']);
                $stmt->execute();
                $success = 'Password changed successfully';
            } else {
                $error = 'New password must be at least 6 characters';
            }
        } else {
            $error = 'Current password is incorrect';
        }
    }
}

// Get order count
$stmt = $db->prepare("SELECT COUNT(*) as count FROM orders WHERE user_id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$order_count = $stmt->get_result()->fetch_assoc()['count'];

// Get wishlist count
$stmt = $db->prepare("SELECT COUNT(*) as count FROM wishlist WHERE user_id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$wishlist_count = $stmt->get_result()->fetch_assoc()['count'];

$page_title = 'My Profile';
include 'includes/header.php';
?>

<div class="container py-5">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-lg-3 mb-4">
            <div class="card">
                <div class="card-body text-center">
                    <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 100px; height: 100px; font-size: 2.5rem;">
                        <?php echo strtoupper(substr($user['name'] ?? '', 0, 1)); ?>
                    </div>
                    <h5 class="mb-1"><?php echo $user['name'] ?? ''; ?></h5>
                    <p class="text-muted mb-0"><?php echo $user['email'] ?? ''; ?></p>
                    <small class="text-muted">Member since <?php echo date('M Y', strtotime($user['created_at'] ?? 'now')); ?></small>
                </div>
                <div class="list-group list-group-flush">
                    <a href="profile.php" class="list-group-item list-group-item-action active">
                        <i class="bi bi-person me-2"></i>Profile
                    </a>
                    <a href="orders.php" class="list-group-item list-group-item-action">
                        <i class="bi bi-bag me-2"></i>My Orders <span class="badge bg-primary float-end"><?php echo $order_count; ?></span>
                    </a>
                    <a href="wishlist.php" class="list-group-item list-group-item-action">
                        <i class="bi bi-heart me-2"></i>Wishlist <span class="badge bg-danger float-end"><?php echo $wishlist_count; ?></span>
                    </a>
                    <a href="compare.php" class="list-group-item list-group-item-action">
                        <i class="bi bi-grid-3x3-gap me-2"></i>Compare Products
                    </a>
                    <a href="logout.php" class="list-group-item list-group-item-action text-danger">
                        <i class="bi bi-box-arrow-right me-2"></i>Logout
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="col-lg-9">
            <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <!-- Recently Viewed Section -->
            <?php if (!empty($recently_viewed)): ?>
            <div class="card mb-4">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-clock-history me-2"></i>Recently Viewed</h5>
                    <a href="products.php" class="btn btn-sm btn-outline-primary">Browse More</a>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <?php foreach ($recently_viewed as $item): ?>
                        <div class="col-md-3 col-6">
                            <div class="card h-100">
                                <a href="product.php?id=<?php echo $item['id']; ?>">
                                    <img src="<?php echo $item['image']; ?>" class="card-img-top" alt="" style="height: 120px; object-fit: cover;">
                                </a>
                                <div class="card-body p-2">
                                    <h6 class="card-title small mb-1"><?php echo substr($item['name'], 0, 25); ?>...</h6>
                                    <span class="text-primary fw-bold small">₹<?php echo number_format($item['price']); ?></span>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- My Reviews Section -->
            <?php if (!empty($user_reviews)): ?>
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="bi bi-star-fill me-2"></i>My Reviews</h5>
                </div>
                <div class="card-body">
                    <?php foreach ($user_reviews as $review): ?>
                    <div class="d-flex mb-3 pb-3 border-bottom">
                        <img src="<?php echo $review['product_image']; ?>" class="rounded" style="width: 60px; height: 60px; object-fit: cover;">
                        <div class="ms-3 flex-grow-1">
                            <h6 class="mb-1"><a href="product.php?id=<?php echo $review['product_id']; ?>" class="text-decoration-none"><?php echo $review['product_name']; ?></a></h6>
                            <div class="rating-stars mb-1">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <i class="bi bi-star<?php echo $i <= $review['rating'] ? '-fill text-warning' : ' text-muted'; ?>"></i>
                                <?php endfor; ?>
                            </div>
                            <p class="mb-0 small"><strong><?php echo htmlspecialchars($review['title']); ?></strong> - <?php echo substr(htmlspecialchars($review['review']), 0, 80); ?>...</p>
                            <small class="text-muted"><?php echo date('M d, Y', strtotime($review['created_at'])); ?></small>
                        </div>
                        <span class="badge bg-<?php echo $review['is_approved'] ? 'success' : 'warning'; ?> ms-2"><?php echo $review['is_approved'] ? 'Approved' : 'Pending'; ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
            
            <div class="row mb-4">
                <div class="col-md-4 mb-3">
                    <div class="card bg-primary text-white">
                        <div class="card-body text-center">
                            <i class="bi bi-bag display-4"></i>
                            <h3 class="mt-2"><?php echo $order_count; ?></h3>
                            <p class="mb-0">Orders</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="card bg-danger text-white">
                        <div class="card-body text-center">
                            <i class="bi bi-heart display-4"></i>
                            <h3 class="mt-2"><?php echo $wishlist_count; ?></h3>
                            <p class="mb-0">Wishlist</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="card bg-success text-white">
                        <div class="card-body text-center">
                            <i class="bi bi-cart display-4"></i>
                            <h3 class="mt-2"><?php echo getCartCount(); ?></h3>
                            <p class="mb-0">Cart Items</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="bi bi-person me-2"></i>Edit Profile</h5>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Full Name *</label>
                                <input type="text" name="name" class="form-control" value="<?php echo $user['name'] ?? ''; ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email (cannot change)</label>
                                <input type="email" class="form-control" value="<?php echo $user['email'] ?? ''; ?>" disabled>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Phone</label>
                                <input type="tel" name="phone" class="form-control" value="<?php echo $user['phone'] ?? ''; ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Member Since</label>
                                <input type="text" class="form-control" value="<?php echo date('F d, Y', strtotime($user['created_at'] ?? 'now')); ?>" disabled>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Address</label>
                            <textarea name="address" class="form-control" rows="3"><?php echo $user['address'] ?? ''; ?></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check me-2"></i>Update Profile
                        </button>
                    </form>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="bi bi-lock me-2"></i>Change Password</h5>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Current Password</label>
                                <input type="password" name="current_password" class="form-control">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">New Password</label>
                                <input type="password" name="new_password" class="form-control" minlength="6">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Confirm New Password</label>
                                <input type="password" name="confirm_password" class="form-control">
                            </div>
                        </div>
                        <button type="submit" class="btn btn-warning">
                            <i class="bi bi-key me-2"></i>Change Password
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
