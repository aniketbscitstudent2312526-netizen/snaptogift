<?php
$page_title = 'Users Management';
require_once 'includes/header.php';

// Get all users
$users = $db->query("SELECT u.*, (SELECT COUNT(*) FROM orders WHERE user_id = u.id) as order_count FROM users u ORDER BY u.created_at DESC");
?>

<div class="table-card">
    <div class="card-header bg-white p-4">
        <h5 class="mb-0"><i class="bi bi-people me-2"></i>All Users</h5>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Orders</th>
                    <th>Joined</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($user = $users->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $user['id']; ?></td>
                    <td>
                        <div class="d-flex align-items-center">
                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 35px; height: 35px;">
                                <?php echo strtoupper(substr($user['name'], 0, 1)); ?>
                            </div>
                            <?php echo $user['name']; ?>
                        </div>
                    </td>
                    <td><?php echo $user['email']; ?></td>
                    <td><?php echo $user['phone'] ?: '-'; ?></td>
                    <td><span class="badge bg-primary"><?php echo $user['order_count']; ?></span></td>
                    <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
