<?php
$page_title = 'Products Management';
require_once 'includes/header.php';

// Handle delete
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $db->query("DELETE FROM products WHERE id = $id");
    showAlert('Product deleted successfully', 'success');
    redirect('products.php');
}

// Handle add/edit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id'] ?? 0);
    $name = sanitize($_POST['name']);
    $description = sanitize($_POST['description']);
    $price = floatval($_POST['price']);
    $category_id = intval($_POST['category_id']);
    $stock = intval($_POST['stock']);
    $image = sanitize($_POST['image']);
    
    if ($id) {
        // Update
        $stmt = $db->prepare("UPDATE products SET name=?, description=?, price=?, category_id=?, stock=?, image=? WHERE id=?");
        $stmt->bind_param("ssdiisi", $name, $description, $price, $category_id, $stock, $image, $id);
        $stmt->execute();
        showAlert('Product updated successfully', 'success');
    } else {
        // Insert
        $stmt = $db->prepare("INSERT INTO products (name, description, price, category_id, stock, image) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssdiis", $name, $description, $price, $category_id, $stock, $image);
        $stmt->execute();
        showAlert('Product added successfully', 'success');
    }
    redirect('products.php');
}

// Get product for edit
$edit_product = null;
if (isset($_GET['edit'])) {
    $id = intval($_GET['edit']);
    $edit_product = $db->query("SELECT * FROM products WHERE id = $id")->fetch_assoc();
}

// Get all products with categories
$products = $db->query("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id ORDER BY p.id DESC");

// Get categories for dropdown
$categories = $db->query("SELECT * FROM categories ORDER BY name");
?>

<?php if (isset($_GET['add']) || $edit_product): ?>
<!-- Add/Edit Form -->
<div class="card mb-4">
    <div class="card-header bg-white p-4">
        <h5 class="mb-0"><?php echo $edit_product ? 'Edit Product' : 'Add New Product'; ?></h5>
    </div>
    <div class="card-body p-4">
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?php echo $edit_product['id'] ?? ''; ?>">
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Product Name *</label>
                    <input type="text" name="name" class="form-control" value="<?php echo $edit_product['name'] ?? ''; ?>" required>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Price (₹) *</label>
                    <input type="number" name="price" class="form-control" step="0.01" value="<?php echo $edit_product['price'] ?? ''; ?>" required>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Stock *</label>
                    <input type="number" name="stock" class="form-control" value="<?php echo $edit_product['stock'] ?? '100'; ?>" required>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Category *</label>
                    <select name="category_id" class="form-select" required>
                        <option value="">Select Category</option>
                        <?php $cats = $db->query("SELECT * FROM categories ORDER BY name"); while ($cat = $cats->fetch_assoc()): ?>
                        <option value="<?php echo $cat['id']; ?>" <?php echo ($edit_product && $edit_product['category_id'] == $cat['id']) ? 'selected' : ''; ?>><?php echo $cat['name']; ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Image URL *</label>
                    <input type="url" name="image" class="form-control" value="<?php echo $edit_product['image'] ?? 'https://images.unsplash.com/photo-1549465220-1a8b9238cd48?w=500'; ?>" required>
                    <small class="text-muted">Use Unsplash image URL or any direct image link</small>
                </div>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control" rows="3"><?php echo $edit_product['description'] ?? ''; ?></textarea>
            </div>
            
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check me-2"></i><?php echo $edit_product ? 'Update' : 'Add'; ?> Product
                </button>
                <a href="products.php" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<!-- Products List -->
<div class="table-card">
    <div class="card-header bg-white p-4 d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="bi bi-box-seam me-2"></i>All Products</h5>
        <a href="products.php?add=1" class="btn btn-primary">
            <i class="bi bi-plus-lg me-2"></i>Add Product
        </a>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>ID</th>
                    <th>Image</th>
                    <th>Name</th>
                    <th>Category</th>
                    <th>Price</th>
                    <th>Stock</th>
                    <th>Views/Sales</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($product = $products->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $product['id']; ?></td>
                    <td>
                        <img src="<?php echo $product['image']; ?>" alt="<?php echo $product['name']; ?>" class="rounded" style="width: 50px; height: 50px; object-fit: cover;">
                    </td>
                    <td><?php echo substr($product['name'], 0, 40); ?><?php echo strlen($product['name']) > 40 ? '...' : ''; ?></td>
                    <td><span class="badge bg-secondary"><?php echo $product['category_name'] ?? 'N/A'; ?></span></td>
                    <td>₹<?php echo number_format($product['price'], 2); ?></td>
                    <td>
                        <span class="badge bg-<?php echo $product['stock'] < 10 ? 'danger' : ($product['stock'] < 50 ? 'warning' : 'success'); ?>">
                            <?php echo $product['stock']; ?> in stock
                        </span>
                    </td>
                    <td>
                        <small class="text-muted">
                            <i class="bi bi-eye me-1"></i><?php echo $product['views']; ?>
                            <br>
                            <i class="bi bi-bag me-1"></i><?php echo $product['purchases']; ?>
                        </small>
                    </td>
                    <td>
                        <a href="products.php?edit=<?php echo $product['id']; ?>" class="btn btn-sm btn-outline-primary me-1">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <a href="products.php?delete=<?php echo $product['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirmDelete()">
                            <i class="bi bi-trash"></i>
                        </a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
