# SnapToGift - AI-Powered eCommerce Platform
## bsc.it Final Year Project

A complete gift shopping eCommerce website with AI-powered product recommendations built with PHP, MySQL, and Bootstrap.

---

## 📋 Project Features

### Core Features
- ✅ **User Authentication** - Registration/Login with password hashing
- ✅ **Product Management** - Browse, search, filter gift products
- ✅ **Shopping Cart** - Add, update, remove items (Session + Database)
- ✅ **Order System** - Place orders, view order history
- ✅ **AI Recommendations** - Smart gift suggestions based on user behavior
- ✅ **Admin Dashboard** - Manage products, orders, users, categories
- ✅ **Wishlist** - Save favorite gifts
- ✅ **Responsive Design** - Mobile-friendly with Bootstrap 5
- ✅ **Beautiful Rose Theme** - Elegant UI with modern aesthetics

### AI Recommendation Features
- Products recommended based on viewed categories
- Popular products based on views and purchases
- Trending products section
- Personalized "Recommended for You" section

---

## 🗂️ Project Structure

```
ai_ecommerce/
├── admin/                    # Admin Panel
│   ├── includes/
│   │   ├── header.php
│   │   └── footer.php
│   ├── index.php            # Dashboard
│   ├── login.php
│   ├── logout.php
│   ├── products.php         # Product management
│   ├── orders.php           # Order management
│   ├── users.php            # User management
├── cart.php                 # Shopping cart
├── checkout.php             # Order placement
├── orders.php               # Order history
├── login.php / register.php / logout.php  # User auth
├── profile.php              # User profile
├── wishlist.php             # Wishlist feature
├── search.php               # Product search
├── add_cart.php             # AJAX cart handler
├── includes/                # Header & footer (Rose theme)
├── admin/                   # Complete admin panel (Rose theme)
└── assets/                  # CSS, JS, images folders
```

---

## ⚙️ Setup Instructions

### Prerequisites
- XAMPP/WAMP installed
- PHP 7.4 or higher
- MySQL 5.7 or higher

### Installation Steps

#### 1. Download and Extract
```bash
# Extract to htdocs folder
C:\xampp\htdocs\ai_ecommerce\
```

#### 2. Create Database
1. Open XAMPP Control Panel
2. Start Apache and MySQL
3. Open browser: http://localhost/phpmyadmin
4. Click "SQL" tab
5. Run the SQL from `database.sql` file

Or import `database.sql` using the Import tab.

#### 3. Configure Database (if needed)
Edit `config.php` if your MySQL credentials are different:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');      // Your MySQL username
define('DB_PASS', '');          // Your MySQL password
define('DB_NAME', 'ai_ecommerce');
```

#### 4. Access the Website
- **Main Website**: http://localhost/ai_ecommerce/
- **Admin Panel**: http://localhost/ai_ecommerce/admin/

---

## 🔑 Default Credentials

### Admin Login
- **Username**: `admin`
- **Email**: `snaptogift@gmail.com`
- **Password**: `Rohit@12849`

### Demo User
- **Email**: `demo@example.com`
- **Password**: `password`

---

## 📊 Database Tables

| Table | Description |
|-------|-------------|
| `users` | Customer accounts |
| `categories` | Product categories |
| `products` | Product catalog |
| `cart` | Shopping cart items |
| `orders` | Customer orders |
| `order_items` | Items in each order |
| `product_views` | For AI recommendations |
| `wishlist` | User wishlists |
| `admins` | Admin accounts |

---

## 🤖 AI Recommendation Logic

### Algorithm
1. **Category-Based**: Recommends products from categories the user has viewed
2. **Popularity-Based**: Suggests products with high views + purchases
3. **Trending**: Shows products with most recent views

### How It Works
- When user views a product, it's recorded in `product_views` table
- AI analyzes viewed categories and shows similar products
- Popular products are weighted (purchases count 3x more than views)

---

## 🎨 Screenshots Guide

### For Your Project Report

1. **Homepage** - http://localhost/ai_ecommerce/
   - Shows hero section, categories, AI recommendations, popular products

2. **Product Listing** - http://localhost/ai_ecommerce/products.php
   - Shows filters, sorting, product grid

3. **Product Detail** - http://localhost/ai_ecommerce/product.php?id=1
   - Shows product info, AI recommendations, add to cart

4. **Cart** - http://localhost/ai_ecommerce/cart.php
   - Add items first, then view cart

5. **Checkout** - http://localhost/ai_ecommerce/checkout.php
   - Requires login and items in cart

6. **User Profile** - http://localhost/ai_ecommerce/profile.php
   - Login required

7. **Admin Dashboard** - http://localhost/ai_ecommerce/admin/
   - Shows statistics, charts, recent orders

8. **Admin Products** - http://localhost/ai_ecommerce/admin/products.php
   - CRUD operations

---

## 📝 Key Features for BCA Project

### Technical Highlights
1. **MVC-like Structure** - Organized folder structure
2. **Security**
   - Password hashing with `password_hash()`
   - SQL injection prevention with prepared statements
   - XSS protection with `htmlspecialchars()`
   - Input sanitization

3. **Database Operations**
   - CRUD operations
   - Joins for complex queries
   - Aggregation functions for analytics

4. **AJAX Integration**
   - Add to cart without page reload
   - Real-time cart count update

5. **Session Management**
   - User authentication
   - Cart persistence
   - Admin access control

6. **AI/ML Component**
   - Recommendation algorithm
   - User behavior tracking
   - Product analytics

---

## 🚀 Optional Enhancements

### To Add Python ML (Advanced)
1. Install Python on your system
2. Install required packages:
   ```bash
   pip install flask scikit-learn pandas
   ```
3. Run the ML API server
4. Connect PHP to Python API

### Additional Features You Can Add
- Payment gateway integration (Razorpay/Stripe)
- Email notifications (PHPMailer)
- SMS notifications (Twilio/Fast2SMS)
- Product reviews and ratings
- Order tracking system

---

## 🐛 Troubleshooting

### Common Issues

1. **Database connection failed**
   - Check XAMPP MySQL is running
   - Verify credentials in `config.php`

2. **404 Not Found**
   - Check URL path
   - Verify files are in correct location

3. **Images not loading**
   - Use direct image URLs (Unsplash)
   - Check internet connection

4. **Session errors**
   - Clear browser cookies
   - Check PHP session is enabled

---

## 📞 Support

For project-related queries:
- Check code comments for explanations
- Review function documentation in `config.php`
- Examine database schema in `database.sql`

---

## 📄 License

This project is created for educational purposes (BCA Final Year Project).

---

## 🎓 Project By

**BCA Final Year Student**
- Project: SnapToGift - AI-Powered eCommerce Platform
- Technologies: PHP, MySQL, Bootstrap 5, JavaScript
- Theme: Rose Red Elegant Design
- Institution: [Your College Name]

---

**Good Luck with Your SnapToGift Project! 🎁🎉**
