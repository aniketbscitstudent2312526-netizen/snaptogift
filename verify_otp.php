<?php
require_once 'config.php';

// Check if we have pending registration
if (!isset($_SESSION['pending_registration'])) {
    redirect('register.php');
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $otp = $_POST['otp'] ?? '';
    $email = $_SESSION['pending_registration']['email'];
    
    if (empty($otp)) {
        $error = 'Please enter the OTP code';
    } else {
        if (verifyOTP($email, $otp, 'registration')) {
            // OTP verified, complete registration
            $data = $_SESSION['pending_registration'];
            
            $stmt = $db->prepare("INSERT INTO users (name, email, password, phone, email_verified, email_verified_at) VALUES (?, ?, ?, ?, 1, NOW())");
            $stmt->bind_param("ssss", $data['name'], $data['email'], $data['password'], $data['phone']);
            
            if ($stmt->execute()) {
                unset($_SESSION['pending_registration']);
                unset($_SESSION['otp_registration']);
                
                $success = 'Registration successful! Please login.';
                $_SESSION['alert'] = ['message' => 'Registration successful! Please login.', 'type' => 'success'];
                redirect('login.php');
            } else {
                $error = 'Registration failed. Please try again.';
            }
        } else {
            $error = 'Invalid or expired OTP. Please try again.';
        }
    }
}

$page_title = 'Verify OTP';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - SnapToGift</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        .otp-input {
            width: 50px;
            height: 60px;
            text-align: center;
            font-size: 1.5rem;
            font-weight: bold;
            border: 2px solid #dee2e6;
            border-radius: 8px;
        }
        .otp-input:focus {
            border-color: #0d6efd;
            outline: none;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container">
        <div class="row justify-content-center min-vh-100 align-items-center">
            <div class="col-md-5">
                <div class="text-center mb-4">
                    <i class="bi bi-gift-fill text-primary" style="font-size: 3rem;"></i>
                    <h2 class="mt-2">SnapToGift</h2>
                </div>
                
                <div class="card shadow">
                    <div class="card-body p-4">
                        <h4 class="text-center mb-3"><i class="bi bi-shield-check text-primary"></i> Verify Your Email</h4>
                        <p class="text-muted text-center mb-4">
                            We've sent a 6-digit OTP to <strong><?php echo $_SESSION['pending_registration']['email']; ?></strong>
                        </p>
                        
                        <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>
                        
                        <form method="POST" action="">
                            <div class="mb-4 text-center">
                                <input type="text" name="otp" class="form-control text-center" maxlength="6" placeholder="Enter 6-digit OTP" style="font-size: 1.5rem; letter-spacing: 5px;" required>
                            </div>
                            
                            <button type="submit" class="btn btn-primary w-100 mb-3">
                                <i class="bi bi-check-circle me-2"></i>Verify OTP
                            </button>
                        </form>
                        
                        <div class="text-center">
                            <p class="text-muted">Didn't receive the code?</p>
                            <a href="register.php" class="btn btn-outline-secondary btn-sm">
                                <i class="bi bi-arrow-repeat me-1"></i>Resend OTP
                            </a>
                        </div>
                        
                        <hr class="my-4">
                        
                        <div class="text-center">
                            <a href="register.php" class="text-decoration-none">
                                <i class="bi bi-arrow-left me-1"></i>Back to Registration
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
