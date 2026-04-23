<?php
require_once 'config.php';

$error = '';
$success = '';
$step = 'email'; // email, otp, reset

if (isset($_GET['email']) && isset($_SESSION['forgot_password_email'])) {
    $step = 'otp';
}

if (isset($_GET['reset']) && isset($_SESSION['otp_verified'])) {
    $step = 'reset';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($step === 'email' && isset($_POST['email'])) {
        $email = sanitize($_POST['email']);
        
        // Check if email exists
        $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            $error = 'No account found with this email address.';
        } else {
            // Generate and send OTP
            $otp = generateOTP();
            sendOTP($email, $otp, 'password_reset');
            
            $_SESSION['forgot_password_email'] = $email;
            $success = 'OTP has been sent to your email address.';
            $step = 'otp';
        }
    } elseif ($step === 'otp' && isset($_POST['otp'])) {
        $email = $_SESSION['forgot_password_email'];
        $otp = $_POST['otp'];
        
        if (verifyOTP($email, $otp, 'password_reset')) {
            $_SESSION['otp_verified'] = true;
            $success = 'OTP verified successfully. Set your new password.';
            $step = 'reset';
        } else {
            $error = 'Invalid or expired OTP. Please try again.';
        }
    } elseif ($step === 'reset' && isset($_POST['new_password'])) {
        $email = $_SESSION['forgot_password_email'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        
        if (strlen($new_password) < 6) {
            $error = 'Password must be at least 6 characters long.';
        } elseif ($new_password !== $confirm_password) {
            $error = 'Passwords do not match.';
        } else {
            // Update password
            $hash = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $db->prepare("UPDATE users SET password = ? WHERE email = ?");
            $stmt->bind_param("ss", $hash, $email);
            
            if ($stmt->execute()) {
                // Clear session data
                unset($_SESSION['forgot_password_email']);
                unset($_SESSION['otp_verified']);
                
                $success = 'Password reset successful! Please login with your new password.';
                $_SESSION['alert'] = ['message' => 'Password reset successful! Please login.', 'type' => 'success'];
                redirect('login.php');
            } else {
                $error = 'Failed to reset password. Please try again.';
            }
        }
    }
}

$page_title = 'Forgot Password';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - SnapToGift</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
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
                        <h4 class="text-center mb-3">
                            <i class="bi bi-key-fill text-primary"></i> 
                            <?php echo $step === 'email' ? 'Forgot Password' : ($step === 'otp' ? 'Verify OTP' : 'Reset Password'); ?>
                        </h4>
                        
                        <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>
                        
                        <?php if ($success): ?>
                        <div class="alert alert-success"><?php echo $success; ?></div>
                        <?php endif; ?>
                        
                        <?php if ($step === 'email'): ?>
                        <p class="text-muted text-center mb-4">Enter your email address and we'll send you an OTP to reset your password.</p>
                        
                        <form method="POST" action="">
                            <div class="mb-3">
                                <label class="form-label">Email Address</label>
                                <input type="email" name="email" class="form-control form-control-lg" placeholder="Enter your email" required>
                            </div>
                            
                            <button type="submit" class="btn btn-primary w-100 btn-lg">
                                <i class="bi bi-send me-2"></i>Send OTP
                            </button>
                        </form>
                        
                        <?php elseif ($step === 'otp'): ?>
                        <p class="text-muted text-center mb-4">Enter the 6-digit OTP sent to <strong><?php echo $_SESSION['forgot_password_email']; ?></strong></p>
                        
                        <form method="POST" action="?email=<?php echo urlencode($_SESSION['forgot_password_email']); ?>">
                            <div class="mb-4">
                                <input type="text" name="otp" class="form-control form-control-lg text-center" maxlength="6" placeholder="Enter 6-digit OTP" style="font-size: 1.5rem; letter-spacing: 5px;" required>
                            </div>
                            
                            <button type="submit" class="btn btn-primary w-100 btn-lg">
                                <i class="bi bi-check-circle me-2"></i>Verify OTP
                            </button>
                        </form>
                        
                        <div class="text-center mt-3">
                            <a href="forgot_password.php" class="text-decoration-none">Resend OTP</a>
                        </div>
                        
                        <?php else: ?>
                        <p class="text-muted text-center mb-4">Set your new password</p>
                        
                        <form method="POST" action="?reset=1">
                            <div class="mb-3">
                                <label class="form-label">New Password</label>
                                <input type="password" name="new_password" class="form-control form-control-lg" minlength="6" placeholder="Enter new password (min 6 chars)" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Confirm New Password</label>
                                <input type="password" name="confirm_password" class="form-control form-control-lg" placeholder="Confirm new password" required>
                            </div>
                            
                            <button type="submit" class="btn btn-success w-100 btn-lg">
                                <i class="bi bi-check-lg me-2"></i>Reset Password
                            </button>
                        </form>
                        <?php endif; ?>
                        
                        <hr class="my-4">
                        
                        <div class="text-center">
                            <a href="login.php" class="text-decoration-none">
                                <i class="bi bi-arrow-left me-1"></i>Back to Login
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
