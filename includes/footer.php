    </main>
    
    <!-- Footer -->
    <footer class="footer mt-5" style="background: linear-gradient(135deg, #881337 0%, #be123c 50%, #9f1239 100%);">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-4">
                    <h4 class="mb-3"><i class="bi bi-gift-fill me-2"></i>SnapToGift</h4>
                    <p class="text-white-50">Your AI-powered gift shopping destination. Discover perfect gifts with smart recommendations for every special occasion.</p>
                    <div class="d-flex gap-3 mt-3">
                        <a href="#" class="text-white-50 hover-white text-decoration-none" style="transition: all 0.3s;"><i class="bi bi-facebook fs-5"></i></a>
                        <a href="#" class="text-white-50 hover-white text-decoration-none" style="transition: all 0.3s;"><i class="bi bi-twitter fs-5"></i></a>
                        <a href="#" class="text-white-50 hover-white text-decoration-none" style="transition: all 0.3s;"><i class="bi bi-instagram fs-5"></i></a>
                        <a href="#" class="text-white-50 hover-white text-decoration-none" style="transition: all 0.3s;"><i class="bi bi-youtube fs-5"></i></a>
                    </div>
                </div>
                <div class="col-lg-2 col-md-4">
                    <h6 class="mb-3 fw-bold">Shop</h6>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="index.php" class="text-white-50 text-decoration-none hover-white">Home</a></li>
                        <li class="mb-2"><a href="products.php" class="text-white-50 text-decoration-none hover-white">All Products</a></li>
                        <li class="mb-2"><a href="products.php?sort=popular" class="text-white-50 text-decoration-none hover-white">Popular</a></li>
                        <li class="mb-2"><a href="products.php?sort=price_low" class="text-white-50 text-decoration-none hover-white">Deals</a></li>
                    </ul>
                </div>
                <div class="col-lg-2 col-md-4">
                    <h6 class="mb-3 fw-bold">Categories</h6>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="products.php?category=1" class="text-white-50 text-decoration-none hover-white">Electronics</a></li>
                        <li class="mb-2"><a href="products.php?category=2" class="text-white-50 text-decoration-none hover-white">Fashion</a></li>
                        <li class="mb-2"><a href="products.php?category=3" class="text-white-50 text-decoration-none hover-white">Home & Living</a></li>
                        <li class="mb-2"><a href="products.php?category=7" class="text-white-50 text-decoration-none hover-white">Gifts</a></li>
                    </ul>
                </div>
                <div class="col-lg-2 col-md-4">
                    <h6 class="mb-3 fw-bold">Support</h6>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="help.php" class="text-white-50 text-decoration-none hover-white">Help Center</a></li>
                        <li class="mb-2"><a href="orders.php" class="text-white-50 text-decoration-none hover-white">Track Order</a></li>
                        <li class="mb-2"><a href="help.php#returns" class="text-white-50 text-decoration-none hover-white">Returns</a></li>
                        <li class="mb-2"><a href="contact.php" class="text-white-50 text-decoration-none hover-white">Contact Us</a></li>
                    </ul>
                </div>
                <div class="col-lg-2">
                    <h6 class="mb-3 fw-bold">Contact</h6>
                    <ul class="list-unstyled text-white-50">
                        <li class="mb-2"><i class="bi bi-geo-alt me-2 text-danger"></i><a href="https://maps.google.com/?q=Patna,Boring+Road" target="_blank" class="text-white-50 text-decoration-none hover-white">Patna, Boring Road</a></li>
                        <li class="mb-2"><i class="bi bi-telephone me-2 text-danger"></i><a href="tel:+918651485769" class="text-white-50 text-decoration-none hover-white">+91 8651485769</a></li>
                        <li class="mb-2"><i class="bi bi-envelope me-2 text-danger"></i><a href="mailto:snaptogift@gmail.com" class="text-white-50 text-decoration-none hover-white">snaptogift@gmail.com</a></li>
                    </ul>
                </div>
            </div>
            <hr class="my-4 border-secondary opacity-25">
            <div class="row align-items-center">
                <div class="col-md-6 text-center text-md-start">
                    <p class="mb-0 text-white-50">&copy; <?php echo date('Y'); ?> SnapToGift. All rights reserved.</p>
                </div>
                <div class="col-md-6 text-center text-md-end">
                    <a href="privacy.php" class="text-white-50 text-decoration-none me-3 small hover-white">Privacy Policy</a>
                    <a href="terms.php" class="text-white-50 text-decoration-none me-3 small hover-white">Terms</a>
                    <a href="sitemap.php" class="text-white-50 text-decoration-none small hover-white">Sitemap</a>
                </div>
            </div>
        </div>
    </footer>
    
    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JavaScript -->
    <script>
        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert-float .alert');
            alerts.forEach(alert => {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);
        
        // Add to cart functionality
        function addToCart(productId, quantity = 1) {
            fetch('add_cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `product_id=${productId}&quantity=${quantity}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update cart badge
                    const cartBadge = document.querySelector('.cart-badge');
                    if (cartBadge) {
                        cartBadge.textContent = data.cart_count;
                        cartBadge.style.display = 'flex';
                    }
                    
                    // Show success message
                    showToast('Product added to cart!', 'success');
                } else {
                    showToast(data.message || 'Error adding to cart', 'error');
                }
            })
            .catch(error => {
                showToast('Error adding to cart', 'error');
            });
        }
        
        // Toast notification
        function showToast(message, type = 'success') {
            const toastHtml = `
                <div class="toast align-items-center text-white bg-${type === 'success' ? 'success' : 'danger'} border-0" role="alert" style="position: fixed; bottom: 20px; right: 20px; z-index: 9999;">
                    <div class="d-flex">
                        <div class="toast-body">${message}</div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                    </div>
                </div>
            `;
            
            document.body.insertAdjacentHTML('beforeend', toastHtml);
            const toastElement = document.querySelector('.toast:last-child');
            const toast = new bootstrap.Toast(toastElement, { delay: 3000 });
            toast.show();
            
            toastElement.addEventListener('hidden.bs.toast', () => {
                toastElement.remove();
            });
        }
        
        // Quantity controls
        document.querySelectorAll('.qty-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const input = this.parentElement.querySelector('.qty-input');
                let val = parseInt(input.value);
                if (this.dataset.action === 'minus' && val > 1) {
                    input.value = val - 1;
                } else if (this.dataset.action === 'plus') {
                    input.value = val + 1;
                }
            });
        });
    </script>
</body>
</html>
