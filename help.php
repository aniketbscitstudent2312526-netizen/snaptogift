<?php
require_once 'config.php';

$page_title = 'Help Center';
include 'includes/header.php';
?>

<div class="container py-5">
    <div class="text-center mb-5">
        <h1 class="display-5 fw-bold mb-3">Help Center</h1>
        <p class="lead text-muted">Find answers to commonly asked questions</p>
    </div>
    
    <!-- Search -->
    <div class="row mb-5">
        <div class="col-lg-6 mx-auto">
            <div class="input-group input-group-lg">
                <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                <input type="text" class="form-control" placeholder="Search for help..." id="helpSearch">
            </div>
        </div>
    </div>
    
    <!-- FAQ Categories -->
    <div class="row g-4 mb-5">
        <div class="col-md-4">
            <a href="#orders" class="card h-100 text-center p-4 text-decoration-none border-0 shadow-sm hover-shadow">
                <i class="bi bi-bag-fill text-danger fs-1 mb-3"></i>
                <h5>Orders & Shipping</h5>
                <p class="text-muted mb-0">Track orders, shipping info, delivery</p>
            </a>
        </div>
        <div class="col-md-4">
            <a href="#returns" class="card h-100 text-center p-4 text-decoration-none border-0 shadow-sm hover-shadow">
                <i class="bi bi-arrow-return-left text-danger fs-1 mb-3"></i>
                <h5>Returns & Refunds</h5>
                <p class="text-muted mb-0">Return policy, refunds, exchanges</p>
            </a>
        </div>
        <div class="col-md-4">
            <a href="#account" class="card h-100 text-center p-4 text-decoration-none border-0 shadow-sm hover-shadow">
                <i class="bi bi-person-fill text-danger fs-1 mb-3"></i>
                <h5>Account</h5>
                <p class="text-muted mb-0">Login, profile, password, settings</p>
            </a>
        </div>
    </div>
    
    <!-- FAQs -->
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="accordion" id="faqAccordion">
                
                <h4 class="mb-3 mt-4" id="orders"><i class="bi bi-bag me-2"></i>Orders & Shipping</h4>
                
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                            How can I track my order?
                        </button>
                    </h2>
                    <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            You can track your order by visiting the <a href="orders.php">My Orders</a> page in your account. Click on any order to see its current status and tracking information.
                        </div>
                    </div>
                </div>
                
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                            How long does shipping take?
                        </button>
                    </h2>
                    <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            Standard shipping takes 3-7 business days depending on your location. Express shipping (available in select cities) delivers within 1-2 business days.
                        </div>
                    </div>
                </div>
                
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                            Is Cash on Delivery available?
                        </button>
                    </h2>
                    <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            Yes! Cash on Delivery (COD) is available for all orders. Simply select "Cash on Delivery" as your payment method during checkout.
                        </div>
                    </div>
                </div>
                
                <h4 class="mb-3 mt-5" id="returns"><i class="bi bi-arrow-return-left me-2"></i>Returns & Refunds</h4>
                
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq4">
                            What is your return policy?
                        </button>
                    </h2>
                    <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            We offer a 7-day return policy for most items. Products must be unused and in their original packaging. Some items like personalized products cannot be returned unless defective.
                        </div>
                    </div>
                </div>
                
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq5">
                            How do I request a refund?
                        </button>
                    </h2>
                    <div id="faq5" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            To request a refund, go to <a href="orders.php">My Orders</a>, select the order, and click "Request Return". Our team will process your request within 2-3 business days.
                        </div>
                    </div>
                </div>
                
                <h4 class="mb-3 mt-5" id="account"><i class="bi bi-person me-2"></i>Account</h4>
                
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq6">
                            How do I reset my password?
                        </button>
                    </h2>
                    <div id="faq6" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            Go to your <a href="profile.php">Profile</a> page, scroll to "Change Password" section, enter your current password and new password, then click "Change Password".
                        </div>
                    </div>
                </div>
                
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq7">
                            How does the AI recommendation work?
                        </button>
                    </h2>
                    <div id="faq7" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            Our AI analyzes products you view and purchase to suggest similar items you might like. The more you browse, the better recommendations get!
                        </div>
                    </div>
                </div>
                
            </div>
            
            <!-- Still need help -->
            <div class="card mt-5 text-center p-5 bg-light border-0">
                <h4 class="mb-3">Still need help?</h4>
                <p class="text-muted mb-4">Can't find what you're looking for? Contact our support team.</p>
                <a href="contact.php" class="btn btn-primary btn-lg">
                    <i class="bi bi-chat-dots me-2"></i>Contact Support
                </a>
            </div>
        </div>
    </div>
</div>

<style>
.hover-shadow:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0,0,0,0.1) !important;
    transition: all 0.3s ease;
}
</style>

<?php include 'includes/footer.php'; ?>
