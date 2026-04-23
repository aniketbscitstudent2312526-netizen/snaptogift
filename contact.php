<?php
require_once 'config.php';

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name']);
    $email = sanitize($_POST['email']);
    $subject = sanitize($_POST['subject']);
    $message = sanitize($_POST['message']);
    
    if (empty($name) || empty($email) || empty($message)) {
        $error = 'Please fill in all required fields';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address';
    } else {
        // In production, send email here
        // For demo, save to database or session
        if (!isset($_SESSION['contact_messages'])) {
            $_SESSION['contact_messages'] = [];
        }
        $_SESSION['contact_messages'][] = [
            'name' => $name,
            'email' => $email,
            'subject' => $subject,
            'message' => $message,
            'date' => date('Y-m-d H:i:s')
        ];
        $success = 'Thank you for your message! We will get back to you within 24 hours.';
    }
}

$page_title = 'Contact Us';
include 'includes/header.php';
?>

<div class="container py-5">
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="text-center mb-5">
                <h1 class="display-5 fw-bold mb-3">Contact Us</h1>
                <p class="lead text-muted">Have a question? We'd love to hear from you.</p>
            </div>
            
            <?php if ($success): ?>
            <div class="alert alert-success mb-4">
                <i class="bi bi-check-circle-fill me-2"></i><?php echo $success; ?>
            </div>
            <?php endif; ?>
            
            <?php if ($error): ?>
            <div class="alert alert-danger mb-4">
                <i class="bi bi-exclamation-circle-fill me-2"></i><?php echo $error; ?>
            </div>
            <?php endif; ?>
            
            <div class="row g-4 mb-5">
                <div class="col-md-4">
                    <div class="card h-100 text-center p-4 border-0 shadow-sm">
                        <div class="text-danger mb-3">
                            <i class="bi bi-geo-alt-fill fs-1"></i>
                        </div>
                        <h5>Visit Us</h5>
                        <p class="text-muted mb-0">Boring Road<br>Patna, Bihar 800001<br>India</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100 text-center p-4 border-0 shadow-sm">
                        <div class="text-danger mb-3">
                            <i class="bi bi-envelope-fill fs-1"></i>
                        </div>
                        <h5>Email Us</h5>
                        <p class="text-muted mb-0">
                            <a href="mailto:snaptogift@gmail.com" class="text-decoration-none">snaptogift@gmail.com</a><br>
                            <small>We reply within 24 hours</small>
                        </p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100 text-center p-4 border-0 shadow-sm">
                        <div class="text-danger mb-3">
                            <i class="bi bi-telephone-fill fs-1"></i>
                        </div>
                        <h5>Call Us</h5>
                        <p class="text-muted mb-0">
                            <a href="tel:+918651485769" class="text-decoration-none">+91 8651485769</a><br>
                            <small>Mon-Sat, 9AM - 7PM</small>
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="card shadow-sm">
                <div class="card-body p-5">
                    <h4 class="mb-4">Send us a Message</h4>
                    <form method="POST" action="">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Your Name *</label>
                                <input type="text" name="name" class="form-control" placeholder="Enter your name" required 
                                    value="<?php echo isLoggedIn() ? getCurrentUser()['name'] : ''; ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email Address *</label>
                                <input type="email" name="email" class="form-control" placeholder="Enter your email" required
                                    value="<?php echo isLoggedIn() ? getCurrentUser()['email'] : ''; ?>">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Subject</label>
                            <select name="subject" class="form-select">
                                <option value="General Inquiry">General Inquiry</option>
                                <option value="Order Issue">Order Issue</option>
                                <option value="Product Question">Product Question</option>
                                <option value="Returns & Refunds">Returns & Refunds</option>
                                <option value="Feedback">Feedback</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Your Message *</label>
                            <textarea name="message" class="form-control" rows="5" placeholder="How can we help you?" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="bi bi-send me-2"></i>Send Message
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
