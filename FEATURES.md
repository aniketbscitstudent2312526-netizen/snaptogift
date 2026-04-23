# SnapToGift E-Commerce - New Features

## Features Added (Flipkart-style)

### 1. OTP Verification System
- **Files Created:** `verify_otp.php`, `forgot_password.php`
- **Files Updated:** `register.php`, `login.php`, `config.php`
- **Features:**
  - Email OTP verification for new registrations
  - 6-digit OTP generation and validation
  - Password reset with OTP
  - 15-minute OTP expiry
  - Secure OTP storage in database

### 2. Product Reviews & Ratings
- **Files Created:** None (integrated into existing)
- **Files Updated:** `product.php`, `config.php`
- **Database Tables:** `reviews`, `review_votes`
- **Features:**
  - Star rating system (1-5 stars)
  - Review title and text
  - Only verified buyers can review
  - Rating breakdown visualization (5-star, 4-star counts)
  - Average rating display
  - Review approval system
  - Helpful votes on reviews

### 3. Order Tracking System
- **Files Created:** `track_order.php`
- **Files Updated:** `config.php`, `orders.php`
- **Database Tables:** `order_status_history` (updated)
- **Features:**
  - Real-time order status tracking
  - Visual progress bar
  - Status history timeline
  - Multiple status stages: Pending → Confirmed → Processing → Packed → Shipped → In Transit → Out for Delivery → Delivered
  - Tracking number and carrier info
  - Estimated delivery dates
  - Order cancellation tracking

### 4. Coupon/Discount System
- **Files Created:** `admin/coupons.php`
- **Files Updated:** `checkout.php`, `orders.php`, `config.php`, `admin/includes/header.php`
- **Database Tables:** `coupons`, `user_coupons`
- **Features:**
  - Percentage-based discounts (e.g., 10% off)
  - Fixed amount discounts (e.g., ₹50 off)
  - Minimum order amount requirements
  - Maximum discount caps
  - Usage limits per coupon
  - One-time use per user
  - Coupon validity period (start/end dates)
  - Pre-loaded coupons: WELCOME10, FLAT50, SAVE20, FIRSTORDER
  - Admin coupon management dashboard

### 5. Recently Viewed Products
- **Files Updated:** `product.php`, `profile.php`, `config.php`
- **Database Tables:** `recently_viewed`
- **Features:**
  - Tracks user's browsing history
  - Shows last 20 viewed products
  - Displays on product pages
  - Shows in user profile
  - Auto-updates timestamp on re-view

### 6. Product Comparison
- **Files Created:** `compare.php`
- **Files Updated:** `product.php`, `profile.php`, `config.php`
- **Database Tables:** `product_comparisons`
- **Features:**
  - Compare up to 4 products side-by-side
  - Compare price, rating, stock, features
  - Add/remove from comparison
  - Clear all comparisons
  - Accessible from product page and profile

### 7. Email Notification System
- **Files Updated:** `config.php`, `checkout.php`
- **Database Tables:** `email_notifications`
- **Features:**
  - Order confirmation emails
  - Order status update notifications
  - Email logging system
  - Ready for SMTP integration

### 8. Admin Dashboard Enhancements
- **Files Created:** 
  - `admin/order_detail.php` - Detailed order view with status management
  - `admin/coupons.php` - Coupon management
  - `admin/reviews.php` - Review moderation
- **Files Updated:** `admin/orders.php`, `admin/includes/header.php`
- **Features:**
  - Order detail view with full tracking
  - Status update with notes
  - Tracking number management
  - Coupon creation and management
  - Review approval/rejection system
  - Enhanced navigation menu

## Database Updates

### New Tables Created:
```sql
1. otp_verifications - Store OTP codes
2. reviews - Product reviews
3. review_votes - Helpful votes on reviews
4. coupons - Discount coupons
5. user_coupons - Track coupon usage
6. recently_viewed - User browsing history
7. product_comparisons - Products to compare
8. order_status_history - Order tracking history
9. email_notifications - Email log
```

### Updated Tables:
```sql
1. users - Added email_verified, email_verified_at
2. orders - Added tracking_number, shipping_carrier, estimated_delivery, coupon_code, discount_amount, shipping_cost
```

## Default Coupons
- **WELCOME10**: 10% off, min order ₹500, max discount ₹200
- **FLAT50**: ₹50 off, min order ₹300
- **SAVE20**: 20% off, min order ₹1000, max discount ₹500
- **FIRSTORDER**: 15% off, no min order, max discount ₹300

## How to Use

### For Customers:
1. Register with OTP verification
2. Login with forgot password option
3. Browse products and add to comparison
4. View recently viewed products in profile
5. Apply coupons at checkout
6. Track orders in detail
7. Review purchased products
8. View order status history

### For Admins:
1. Login at `/admin/login.php`
2. Default: admin / Rohit@12849
3. Manage orders with full tracking
4. Create and manage coupons
5. Approve/reject product reviews
6. Update order statuses with notes

## Demo Accounts
- **Customer:** demo@example.com / password
- **Admin:** snaptogift@gmail.com / Rohit@12849

## Security Features
- Password hashing with bcrypt
- OTP verification for registration
- OTP verification for password reset
- SQL injection protection (prepared statements)
- XSS protection (htmlspecialchars)
- CSRF protection ready

## Next Steps for Production
1. Configure SMTP for email sending
2. Set up SSL certificate
3. Configure payment gateway integration
4. Add Google Analytics
5. Optimize images and assets
6. Set up CDN for static files
7. Configure backup system
8. Add SEO meta tags
9. Create sitemap.xml
10. Set up robots.txt
