<?php
require_once __DIR__ . '/../../config.php';
requireAdmin();

$current_page = basename($_SERVER['PHP_SELF'], '.php');
$admin_username = $_SESSION['admin_username'] ?? 'Admin';

// Get stats for sidebar
$total_users = getTotalUsers();
$total_orders = getTotalOrders();
$total_revenue = getTotalRevenue();
$pending_orders = $db->query("SELECT COUNT(*) as count FROM orders WHERE status = 'pending'")->fetch_assoc()['count'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' : ''; ?>Admin - SnapToGift</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <style>
        :root {
            --sidebar-width: 260px;
            --primary: #e11d48;
            --primary-dark: #be123c;
            --rose-light: #fff1f2;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background: #f3f4f6;
        }
        
        .sidebar {
            width: var(--sidebar-width);
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            background: linear-gradient(180deg, #881337 0%, #be123c 50%, #9f1239 100%);
            color: white;
            overflow-y: auto;
            z-index: 1000;
        }
        
        .sidebar-header {
            padding: 25px 20px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        .sidebar-menu {
            padding: 20px 0;
        }
        
        .sidebar-menu a {
            display: flex;
            align-items: center;
            padding: 12px 20px;
            color: rgba(255,255,255,0.7);
            text-decoration: none;
            transition: all 0.3s;
            margin: 4px 12px;
            border-radius: 8px;
        }
        
        .sidebar-menu a:hover, .sidebar-menu a.active {
            background: rgba(255,255,255,0.15);
            color: white;
        }
        
        .sidebar-menu a.active {
            background: rgba(255,255,255,0.2);
            font-weight: 500;
        }
        
        .sidebar-menu i {
            margin-right: 12px;
            font-size: 1.1rem;
        }
        
        .main-content {
            margin-left: var(--sidebar-width);
            padding: 20px;
        }
        
        .topbar {
            background: white;
            padding: 15px 25px;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            margin-bottom: 25px;
        }
        
        .stats-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }
        
        .stats-card:hover {
            transform: translateY(-3px);
        }
        
        .stats-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }
        
        .table-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .badge-status {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 500;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #be123c 0%, #e11d48 100%);
            border: none;
        }
        
        .btn-primary:hover {
            background: linear-gradient(135deg, #9f1239 0%, #be123c 100%);
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <div class="d-flex align-items-center">
                <i class="bi bi-gift-fill fs-3 me-2"></i>
                <div>
                    <h5 class="mb-0 fw-bold">SnapToGift</h5>
                    <small style="opacity: 0.8;">Admin Management</small>
                </div>
            </div>
        </div>
        
        <div class="sidebar-menu">
            <a href="index.php" class="<?php echo $current_page == 'index' ? 'active' : ''; ?>">
                <i class="bi bi-speedometer2"></i>Dashboard
            </a>
            <a href="products.php" class="<?php echo $current_page == 'products' ? 'active' : ''; ?>">
                <i class="bi bi-box-seam"></i>Products
            </a>
            <a href="orders.php" class="<?php echo $current_page == 'orders' ? 'active' : ''; ?>">
                <i class="bi bi-cart3"></i>Orders
                <?php if ($pending_orders > 0): ?>
                <span class="badge bg-danger ms-auto"><?php echo $pending_orders; ?></span>
                <?php endif; ?>
            </a>
            <a href="users.php" class="<?php echo $current_page == 'users' ? 'active' : ''; ?>">
                <i class="bi bi-people"></i>Users
            </a>
            <a href="categories.php" class="<?php echo $current_page == 'categories' ? 'active' : ''; ?>">
                <i class="bi bi-tags"></i>Categories
            </a>
            <a href="coupons.php" class="<?php echo $current_page == 'coupons' ? 'active' : ''; ?>">
                <i class="bi bi-ticket-perforated"></i>Coupons
            </a>
            <a href="reviews.php" class="<?php echo $current_page == 'reviews' ? 'active' : ''; ?>">
                <i class="bi bi-star-fill"></i>Reviews
            </a>
            <hr class="border-secondary mx-3">
            <a href="../index.php" target="_blank">
                <i class="bi bi-shop"></i>View Website
            </a>
            <a href="logout.php">
                <i class="bi bi-box-arrow-right"></i>Logout
            </a>
        </div>
    </div>
    
    <!-- Main Content -->
    <div class="main-content">
        <!-- Topbar -->
        <div class="topbar d-flex justify-content-between align-items-center">
            <h4 class="mb-0"><?php echo $page_title ?? 'Dashboard'; ?></h4>
            <div class="d-flex align-items-center">
                <div class="dropdown">
                    <a class="dropdown-toggle text-decoration-none text-dark" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="bi bi-person-circle fs-5 me-2"></i><?php echo $admin_username; ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
                    </ul>
                </div>
            </div>
        </div>
        
        <?php $alert = getAlert(); if ($alert): ?>
        <div class="alert alert-<?php echo $alert['type']; ?> alert-dismissible fade show" role="alert">
            <?php echo $alert['message']; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>
