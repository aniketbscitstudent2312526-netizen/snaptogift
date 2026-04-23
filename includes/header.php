<?php
require_once __DIR__ . '/../config.php';

$current_page = basename($_SERVER['PHP_SELF'], '.php');
$cart_count = getCartCount();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' : ''; ?>SnapToGift - AI-Powered Shopping</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary: #e11d48;
            --primary-dark: #be123c;
            --secondary: #fb7185;
            --accent: #f43f5e;
            --dark: #1e293b;
            --light: #fff1f2;
            --gradient: linear-gradient(135deg, #e11d48 0%, #fb7185 50%, #fda4af 100%);
            --rose-gradient: linear-gradient(135deg, #be123c 0%, #e11d48 50%, #fb7185 100%);
        }
        
        * {
            font-family: 'Inter', sans-serif;
        }
        
        body {
            background-color: #f1f5f9;
        }
        
        .navbar {
            background: white;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        
        .navbar-brand {
            font-weight: 800;
            font-size: 1.6rem;
            background: var(--rose-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            letter-spacing: -0.5px;
        }
        
        .navbar-brand:hover {
            transform: scale(1.02);
            transition: transform 0.3s ease;
        }
        
        .nav-link {
            font-weight: 500;
            color: var(--dark) !important;
            transition: color 0.3s;
        }
        
        .nav-link:hover, .nav-link.active {
            color: var(--primary) !important;
        }
        
        .btn-primary {
            background: var(--gradient);
            border: none;
            font-weight: 600;
        }
        
        .btn-primary:hover {
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 50%, #db2777 100%);
        }
        
        .card {
            border: none;
            border-radius: 16px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .card:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
        }
        
        .product-card img {
            height: 200px;
            object-fit: cover;
            border-radius: 16px 16px 0 0;
        }
        
        .badge-recommend {
            background: var(--gradient);
            color: white;
            font-size: 0.7rem;
            padding: 0.35em 0.65em;
            border-radius: 20px;
        }
        
        .search-bar {
            border-radius: 50px;
            border: 2px solid #e2e8f0;
            padding: 0.75rem 1.5rem;
        }
        
        .search-bar:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
        }
        
        .cart-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background: var(--primary);
            color: white;
            font-size: 0.65rem;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            box-shadow: 0 2px 8px rgba(225, 29, 72, 0.4);
        }
        
        .hero-section {
            background: var(--rose-gradient);
            color: white;
            padding: 100px 0;
            position: relative;
            overflow: hidden;
        }
        
        .hero-section::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            right: -50%;
            bottom: -50%;
            background: radial-gradient(circle, rgba(255,255,255,0.15) 0%, transparent 60%);
            animation: pulse 15s ease-in-out infinite;
        }
        
        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.1); }
        }
        
        .ai-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(255,255,255,0.2);
            backdrop-filter: blur(10px);
            padding: 8px 16px;
            border-radius: 50px;
            font-size: 0.875rem;
            margin-bottom: 1rem;
        }
        
        .category-chip {
            display: inline-block;
            padding: 8px 20px;
            background: white;
            border-radius: 50px;
            color: var(--dark);
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s;
            border: 2px solid transparent;
        }
        
        .category-chip:hover, .category-chip.active {
            background: var(--gradient);
            color: white;
            border-color: transparent;
        }
        
        .price-tag {
            font-weight: 700;
            color: var(--primary-dark);
            font-size: 1.1rem;
        }
        
        .footer {
            background: var(--dark);
            color: white;
            padding: 60px 0 30px;
        }
        
        .alert-float {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            animation: slideIn 0.3s ease;
        }
        
        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        
        .recommendation-section {
            background: linear-gradient(135deg, #fff1f2 0%, #ffe4e6 100%);
            border-radius: 24px;
            padding: 40px;
            margin: 40px 0;
            border: 1px solid #fecdd3;
        }
        
        .rating-stars {
            color: #fbbf24;
        }
        
        .stock-badge {
            font-size: 0.75rem;
            padding: 0.25em 0.75em;
        }
    </style>
</head>
<body>
    
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg sticky-top">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="bi bi-gift-fill me-2 text-danger"></i>SnapToGift
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link <?php echo $current_page == 'index' ? 'active' : ''; ?>" href="index.php">
                            <i class="bi bi-house me-1"></i>Home
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $current_page == 'products' ? 'active' : ''; ?>" href="products.php">
                            <i class="bi bi-grid me-1"></i>Products
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-tags me-1"></i>Categories
                        </a>
                        <ul class="dropdown-menu">
                            <?php
                            $cats = $db->query("SELECT * FROM categories ORDER BY name");
                            while ($cat = $cats->fetch_assoc()):
                            ?>
                            <li><a class="dropdown-item" href="products.php?category=<?php echo $cat['id']; ?>"><?php echo $cat['name']; ?></a></li>
                            <?php endwhile; ?>
                        </ul>
                    </li>
                </ul>
                
                <form class="d-flex me-3" action="search.php" method="GET">
                    <div class="input-group">
                        <input class="form-control search-bar" type="search" name="q" placeholder="Search products..." aria-label="Search">
                        <button class="btn btn-outline-primary" type="submit"><i class="bi bi-search"></i></button>
                    </div>
                </form>
                
                <ul class="navbar-nav align-items-center">
                    <li class="nav-item me-3">
                        <a class="nav-link position-relative" href="cart.php">
                            <i class="bi bi-cart3 fs-5"></i>
                            <?php if ($cart_count > 0): ?>
                            <span class="cart-badge"><?php echo $cart_count; ?></span>
                            <?php endif; ?>
                        </a>
                    </li>
                    
                    <?php if (isLoggedIn()): 
                        $user = getCurrentUser();
                    ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" role="button" data-bs-toggle="dropdown">
                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 32px; height: 32px; font-size: 0.9rem;">
                                <?php echo strtoupper(substr($user['name'] ?? '', 0, 1)); ?>
                            </div>
                            <span><?php $nameParts = explode(' ', $user['name'] ?? ''); echo $nameParts[0] ?? ''; ?></span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="profile.php"><i class="bi bi-person me-2"></i>My Profile</a></li>
                            <li><a class="dropdown-item" href="orders.php"><i class="bi bi-bag me-2"></i>My Orders</a></li>
                            <li><a class="dropdown-item" href="wishlist.php"><i class="bi bi-heart me-2"></i>Wishlist</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
                        </ul>
                    </li>
                    <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="login.php">Login</a>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-primary ms-2" href="register.php">Register</a>
                    </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
    
    <!-- Alert Messages -->
    <?php $alert = getAlert(); if ($alert): ?>
    <div class="alert-float">
        <div class="alert alert-<?php echo $alert['type']; ?> alert-dismissible fade show shadow" role="alert">
            <?php echo $alert['message']; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    </div>
    <?php endif; ?>
    
    <main>
