<?php
$page_title = 'Reviews Management';
require_once 'includes/header.php';

// Handle review approval
if (isset($_GET['approve'])) {
    $id = intval($_GET['approve']);
    $db->query("UPDATE reviews SET is_approved = 1 WHERE id = $id");
    
    // Update product rating
    $result = $db->query("SELECT product_id FROM reviews WHERE id = $id");
    if ($row = $result->fetch_assoc()) {
        updateProductRating($row['product_id']);
    }
    
    showAlert('Review approved', 'success');
    redirect('reviews.php');
}

// Handle review deletion
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    
    // Get product_id before deleting
    $result = $db->query("SELECT product_id FROM reviews WHERE id = $id");
    $product_id = null;
    if ($row = $result->fetch_assoc()) {
        $product_id = $row['product_id'];
    }
    
    $db->query("DELETE FROM reviews WHERE id = $id");
    
    // Update product rating
    if ($product_id) {
        updateProductRating($product_id);
    }
    
    showAlert('Review deleted', 'success');
    redirect('reviews.php');
}

// Get all reviews with product and user info
$reviews = $db->query("SELECT r.*, p.name as product_name, p.image as product_image, u.name as user_name, u.email as user_email 
                      FROM reviews r 
                      JOIN products p ON r.product_id = p.id 
                      JOIN users u ON r.user_id = u.id 
                      ORDER BY r.created_at DESC");
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="mb-0"><i class="bi bi-star-fill me-2"></i>Product Reviews</h5>
    <div class="d-flex gap-2">
        <span class="badge bg-warning">Pending: <?php echo $db->query("SELECT COUNT(*) as c FROM reviews WHERE is_approved = 0")->fetch_assoc()['c']; ?></span>
        <span class="badge bg-success">Approved: <?php echo $db->query("SELECT COUNT(*) as c FROM reviews WHERE is_approved = 1")->fetch_assoc()['c']; ?></span>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Product</th>
                    <th>Customer</th>
                    <th>Rating</th>
                    <th>Review</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($review = $reviews->fetch_assoc()): ?>
                <tr>
                    <td>
                        <div class="d-flex align-items-center">
                            <img src="<?php echo $review['product_image']; ?>" alt="" class="rounded" style="width: 50px; height: 50px; object-fit: cover;">
                            <span class="ms-2"><?php echo substr($review['product_name'], 0, 30); ?>...</span>
                        </div>
                    </td>
                    <td>
                        <div><?php echo $review['user_name']; ?></div>
                        <small class="text-muted"><?php echo $review['user_email']; ?></small>
                    </td>
                    <td>
                        <div class="rating-stars">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <i class="bi bi-star<?php echo $i <= $review['rating'] ? '-fill text-warning' : ' text-muted'; ?>"></i>
                            <?php endfor; ?>
                        </div>
                        <small class="text-muted"><?php echo $review['rating']; ?>/5</small>
                    </td>
                    <td>
                        <strong><?php echo htmlspecialchars($review['title']); ?></strong>
                        <p class="mb-0 text-muted small"><?php echo substr(htmlspecialchars($review['review']), 0, 100); ?>...</p>
                    </td>
                    <td><?php echo date('M d, Y', strtotime($review['created_at'])); ?></td>
                    <td>
                        <span class="badge bg-<?php echo $review['is_approved'] ? 'success' : 'warning'; ?>">
                            <?php echo $review['is_approved'] ? 'Approved' : 'Pending'; ?>
                        </span>
                    </td>
                    <td>
                        <?php if (!$review['is_approved']): ?>
                        <a href="reviews.php?approve=<?php echo $review['id']; ?>" class="btn btn-sm btn-success">
                            <i class="bi bi-check-lg"></i>
                        </a>
                        <?php endif; ?>
                        <button class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#reviewModal<?php echo $review['id']; ?>">
                            <i class="bi bi-eye"></i>
                        </button>
                        <a href="reviews.php?delete=<?php echo $review['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this review?')">
                            <i class="bi bi-trash"></i>
                        </a>
                    </td>
                </tr>
                
                <!-- Review Detail Modal -->
                <div class="modal fade" id="reviewModal<?php echo $review['id']; ?>" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Review Details</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <div class="d-flex align-items-center mb-3">
                                    <img src="<?php echo $review['product_image']; ?>" alt="" class="rounded" style="width: 60px; height: 60px; object-fit: cover;">
                                    <div class="ms-3">
                                        <h6 class="mb-1"><?php echo $review['product_name']; ?></h6>
                                        <div class="rating-stars">
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                <i class="bi bi-star<?php echo $i <= $review['rating'] ? '-fill text-warning' : ' text-muted'; ?>"></i>
                                            <?php endfor; ?>
                                        </div>
                                    </div>
                                </div>
                                <hr>
                                <h6><?php echo htmlspecialchars($review['title']); ?></h6>
                                <p><?php echo nl2br(htmlspecialchars($review['review'])); ?></p>
                                <hr>
                                <div class="d-flex justify-content-between text-muted">
                                    <small>By: <?php echo $review['user_name']; ?></small>
                                    <small><?php echo date('F d, Y', strtotime($review['created_at'])); ?></small>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                <?php if (!$review['is_approved']): ?>
                                <a href="reviews.php?approve=<?php echo $review['id']; ?>" class="btn btn-success">Approve</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
