<?php
$page_title = 'Categories Management';
require_once 'includes/header.php';

// Handle add/edit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id'] ?? 0);
    $name = sanitize($_POST['name']);
    $description = sanitize($_POST['description']);
    
    if ($id) {
        $stmt = $db->prepare("UPDATE categories SET name=?, description=? WHERE id=?");
        $stmt->bind_param("ssi", $name, $description, $id);
        $stmt->execute();
        showAlert('Category updated', 'success');
    } else {
        $stmt = $db->prepare("INSERT INTO categories (name, description) VALUES (?, ?)");
        $stmt->bind_param("ss", $name, $description);
        $stmt->execute();
        showAlert('Category added', 'success');
    }
    redirect('categories.php');
}

// Handle delete
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    // Check if category has products
    $check = $db->query("SELECT COUNT(*) as c FROM products WHERE category_id = $id")->fetch_assoc()['c'];
    if ($check > 0) {
        showAlert('Cannot delete: Category has products', 'danger');
    } else {
        $db->query("DELETE FROM categories WHERE id = $id");
        showAlert('Category deleted', 'success');
    }
    redirect('categories.php');
}

$edit_category = null;
if (isset($_GET['edit'])) {
    $id = intval($_GET['edit']);
    $edit_category = $db->query("SELECT * FROM categories WHERE id = $id")->fetch_assoc();
}

// Get categories with product count
$categories = $db->query("SELECT c.*, COUNT(p.id) as product_count FROM categories c LEFT JOIN products p ON c.id = p.category_id GROUP BY c.id ORDER BY c.name");
?>

<?php if (isset($_GET['add']) || $edit_category): ?>
<div class="card mb-4">
    <div class="card-header bg-white p-4">
        <h5 class="mb-0"><?php echo $edit_category ? 'Edit Category' : 'Add Category'; ?></h5>
    </div>
    <div class="card-body p-4">
        <form method="POST">
            <input type="hidden" name="id" value="<?php echo $edit_category['id'] ?? ''; ?>">
            <div class="mb-3">
                <label class="form-label">Category Name *</label>
                <input type="text" name="name" class="form-control" value="<?php echo $edit_category['name'] ?? ''; ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control" rows="2"><?php echo $edit_category['description'] ?? ''; ?></textarea>
            </div>
            <button type="submit" class="btn btn-primary"><?php echo $edit_category ? 'Update' : 'Add'; ?></button>
            <a href="categories.php" class="btn btn-outline-secondary">Cancel</a>
        </form>
    </div>
</div>
<?php endif; ?>

<div class="table-card">
    <div class="card-header bg-white p-4 d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="bi bi-tags me-2"></i>Categories</h5>
        <a href="categories.php?add=1" class="btn btn-primary"><i class="bi bi-plus-lg me-2"></i>Add Category</a>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Description</th>
                    <th>Products</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($cat = $categories->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $cat['id']; ?></td>
                    <td><strong><?php echo $cat['name']; ?></strong></td>
                    <td><?php echo $cat['description'] ?: '-'; ?></td>
                    <td><span class="badge bg-secondary"><?php echo $cat['product_count']; ?></span></td>
                    <td>
                        <a href="categories.php?edit=<?php echo $cat['id']; ?>" class="btn btn-sm btn-outline-primary me-1"><i class="bi bi-pencil"></i></a>
                        <a href="categories.php?delete=<?php echo $cat['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirmDelete()"><i class="bi bi-trash"></i></a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
