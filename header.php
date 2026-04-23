<?php require_once 'config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SnapToGift - Curated Gifts for Every Occasion</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <a href="index.php" class="logo">
                <svg class="logo-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="8" width="18" height="13" rx="2"/>
                    <path d="M12 8V4a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v4"/>
                    <path d="M6 8V4a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v4"/>
                </svg>
                <span class="logo-text">SnapToGift</span>
            </a>
            
            <ul class="nav-links">
                <li><a href="index.php">Home</a></li>
                <li><a href="products.php">Shop</a></li>
                <li><a href="products.php?occasion=birthday">Birthday</a></li>
                <li><a href="products.php?occasion=anniversary">Anniversary</a></li>
            </ul>
            
            <div class="nav-actions">
                <a href="cart.php" class="cart-btn">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M9 22a1 1 0 1 0 0-2 1 1 0 0 0 0 2z"/>
                        <path d="M20 22a1 1 0 1 0 0-2 1 1 0 0 0 0 2z"/>
                        <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
                    </svg>
                    <?php if (getCartCount() > 0): ?>
                        <span class="cart-count"><?php echo getCartCount(); ?></span>
                    <?php endif; ?>
                </a>
                
                <?php if (isLoggedIn()): ?>
                    <?php $user = getCurrentUser(); ?>
                    <a href="profile.php" style="display: flex; align-items: center; gap: 8px; text-decoration: none; color: #4b5563; margin-left: 16px;">
                        <div style="width: 36px; height: 36px; background: linear-gradient(135deg, #f43f5e 0%, #ec4899 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: 600;">
                            <?php echo strtoupper(substr($user['name'], 0, 1)); ?>
                        </div>
                        <span class="hidden-sm"><?php echo explode(' ', $user['name'])[0]; ?></span>
                    </a>
                <?php else: ?>
                    <a href="login.php" style="display: flex; align-items: center; gap: 8px; text-decoration: none; color: #4b5563; margin-left: 16px;">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                            <circle cx="12" cy="7" r="4"/>
                        </svg>
                        <span>Login</span>
                    </a>
                <?php endif; ?>
            </div>
            
            <button class="mobile-menu-btn" onclick="toggleMenu()">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M3 12h18M3 6h18M3 18h18"/>
                </svg>
            </button>
        </div>
    </nav>
    
    <div class="mobile-menu" id="mobileMenu">
        <a href="index.php">Home</a>
        <a href="products.php">Shop</a>
        <a href="products.php?occasion=birthday">Birthday</a>
        <a href="products.php?occasion=anniversary">Anniversary</a>
        <a href="cart.php">Cart (<?php echo getCartCount(); ?>)</a>
        <?php if (isLoggedIn()): ?>
            <a href="profile.php">My Profile</a>
            <a href="logout.php">Logout</a>
        <?php else: ?>
            <a href="login.php">Login</a>
            <a href="register.php">Register</a>
        <?php endif; ?>
    </div>
