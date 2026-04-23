-- SnapToGift eCommerce Database Schema
-- AI-Powered Shopping Platform

CREATE DATABASE IF NOT EXISTS snaptogift;
USE snaptogift;

-- 1. Users Table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    address TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- 2. Categories Table
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    description TEXT,
    image VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 3. Products Table
CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    description TEXT,
    price DECIMAL(10, 2) NOT NULL,
    category_id INT,
    image VARCHAR(255),
    stock INT DEFAULT 100,
    views INT DEFAULT 0,
    purchases INT DEFAULT 0,
    rating DECIMAL(2,1) DEFAULT 4.5,
    reviews INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
);

-- 4. Cart Table (Database-based cart for logged-in users)
CREATE TABLE cart (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    UNIQUE KEY unique_cart_item (user_id, product_id)
);

-- 5. Orders Table
CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_number VARCHAR(20) UNIQUE NOT NULL,
    user_id INT NOT NULL,
    total_amount DECIMAL(10, 2) NOT NULL,
    shipping_address TEXT,
    status ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
    payment_method VARCHAR(50) DEFAULT 'cash_on_delivery',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- 6. Order Items Table
CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- 7. Product Views Table (for AI recommendations)
CREATE TABLE product_views (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    product_id INT NOT NULL,
    category_id INT,
    viewed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
);

-- 8. Wishlist Table
CREATE TABLE wishlist (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    UNIQUE KEY unique_wishlist (user_id, product_id)
);

-- 9. Admin Table
CREATE TABLE admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100),
    role ENUM('super_admin', 'admin') DEFAULT 'admin',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert Sample Categories
INSERT INTO categories (name, description) VALUES
('Electronics', 'Gadgets and electronic devices'),
('Fashion', 'Clothing, shoes, and accessories'),
('Home & Living', 'Home decor and furniture'),
('Books', 'Physical and digital books'),
('Sports', 'Sports equipment and accessories'),
('Beauty', 'Beauty and personal care products'),
('Toys', 'Toys and games for all ages'),
('Food', 'Food and beverages');

-- Insert Sample Products
INSERT INTO products (name, description, price, category_id, image, stock, rating, reviews) VALUES
('Wireless Bluetooth Headphones', 'Premium noise-cancelling headphones with 30-hour battery life', 2999.00, 1, 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=500', 50, 4.5, 120),
('Smart Watch Pro', 'Fitness tracker with heart rate monitor and GPS', 4999.00, 1, 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=500', 30, 4.3, 85),
('Men\'s Casual T-Shirt', '100% cotton comfortable casual wear', 599.00, 2, 'https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?w=500', 100, 4.2, 45),
('Women\'s Summer Dress', 'Floral print summer dress, lightweight fabric', 1299.00, 2, 'https://images.unsplash.com/photo-1515372039744-b8f02a3ae446?w=500', 75, 4.4, 62),
('Modern Coffee Table', 'Minimalist design coffee table for living room', 3999.00, 3, 'https://images.unsplash.com/photo-1532372320572-cda25653a26d?w=500', 20, 4.6, 38),
('Decorative Wall Art', 'Abstract canvas painting for home decor', 1499.00, 3, 'https://images.unsplash.com/photo-1513519245088-0e12902e35a6?w=500', 40, 4.1, 25),
('Programming Python Book', 'Complete guide to Python programming', 899.00, 4, 'https://images.unsplash.com/photo-1532012197267-da84d127e765?w=500', 60, 4.7, 150),
('Novel: The Great Adventure', 'Bestselling fiction novel', 499.00, 4, 'https://images.unsplash.com/photo-1544947950-fa07a98d237f?w=500', 80, 4.3, 78),
('Yoga Mat Premium', 'Non-slip exercise yoga mat with carrying strap', 799.00, 5, 'https://images.unsplash.com/photo-1601925260368-ae2f83cf8b7f?w=500', 100, 4.5, 95),
('Dumbbells Set', 'Adjustable weight dumbbells for home gym', 2499.00, 5, 'https://images.unsplash.com/photo-1638536532686-d610adfc8e5c?w=500', 25, 4.4, 42),
('Face Cream Moisturizer', 'Hydrating face cream for all skin types', 699.00, 6, 'https://images.unsplash.com/photo-1570194065650-123d2296a5a7?w=500', 150, 4.2, 88),
('Perfume Elegance', 'Long-lasting floral fragrance perfume', 1999.00, 6, 'https://images.unsplash.com/photo-1541643600914-78b084683601?w=500', 45, 4.6, 56),
('Building Blocks Set', 'Educational building blocks for kids', 1299.00, 7, 'https://images.unsplash.com/photo-1585366119957-f973043d4561?w=500', 60, 4.4, 34),
('Remote Control Car', 'High-speed RC car with rechargeable battery', 1999.00, 7, 'https://images.unsplash.com/photo-1594787318286-3d835c1d207f?w=500', 35, 4.3, 47),
('Organic Green Tea', 'Premium organic green tea leaves', 349.00, 8, 'https://images.unsplash.com/photo-1558160074-4d7d8bdf4256?w=500', 200, 4.5, 112),
('Dark Chocolate Box', 'Assorted dark chocolate gift box', 899.00, 8, 'https://images.unsplash.com/photo-1549007994-cb92caebd54b?w=500', 80, 4.7, 134);

-- Insert Default Admin
INSERT INTO admins (username, password, email, role) VALUES
('admin', '$2y$10$8XPHlWHRZR0N1QyCJ7ySw.A1YgARnOwk2DRb1jOnQ0OnXeKkC', 'snaptogift@gmail.com', 'super_admin');
-- Password: Rohit@12849

-- Insert Sample User
INSERT INTO users (name, email, password, phone, address) VALUES
('Demo User', 'demo@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '9876543210', '123 Demo Street, Demo City');
-- Password: password
