<?php
require_once 'config.php';

$error = '';
$success = '';
$otpSent = false;
$email = $_SESSION['otp_email'] ?? '';
$purpose = $_SESSION['otp_purpose'] ?? 'registration';

// Redirect if no OTP session
if (empty($email)) {
    header('Location: register.php');
    exit;
}

// Resend OTP
if (isset($_POST['resend_otp'])) {
    $result = resendOTP($email, $purpose);
    if ($result['success']) {
        $success = 'New OTP sent! Check the display below.';
        $otpSent = true;
    } else {
        $error = $result['message'];
    }
}

// Verify OTP
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['verify_otp'])) {
    $code = $_POST['otp_code'] ?? '';
    
    if (strlen($code) !== 6 || !ctype_digit($code)) {
        $error = 'Please enter a valid 6-digit OTP';
    } else {
        $result = verifyOTP($email, $code);
        
        if ($result['success']) {
            // OTP verified - proceed based on purpose
            if ($purpose === 'registration') {
                // Complete registration
                $pendingData = getPendingRegistration($email);
                if ($pendingData) {
                    registerUser(
                        $pendingData['name'],
                        $pendingData['email'],
                        $pendingData['password'],
                        $pendingData['phone']
                    );
                    clearPendingRegistration($email);
                    loginUser($email, $pendingData['password']);
                    
                    // Clear OTP session
                    unset($_SESSION['otp_email']);
                    unset($_SESSION['otp_purpose']);
                    
                    header('Location: profile.php?registered=1');
                    exit;
                }
            } elseif ($purpose === 'login') {
                // Complete login
                $_SESSION['logged_in_user'] = $email;
                unset($_SESSION['otp_email']);
                unset($_SESSION['otp_purpose']);
                header('Location: profile.php');
                exit;
            }
        } else {
            $error = $result['message'];
        }
    }
}

// Check if we just sent an OTP
if (isset($_SESSION['last_sent_otp']) && $_SESSION['last_sent_otp']['email'] === $email) {
    $otpSent = true;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify OTP - SnapToGift</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="admin-body">
    <div class="login-page">
        <div class="login-box" style="max-width: 440px;">
            <div style="text-align: center; margin-bottom: 24px;">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#f43f5e" stroke-width="2" style="margin: 0 auto;">
                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                    <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                </svg>
            </div>
            <h1>Verify Your Email</h1>
            <p style="color: #6b7280;">We've sent a 6-digit verification code to<br><strong><?php echo htmlspecialchars($email); ?></strong></p>
            
            <?php if ($error): ?>
                <div class="login-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div style="background: #dcfce7; color: #166534; padding: 12px 16px; border-radius: 8px; margin-bottom: 20px;">
                    <?php echo $success; ?>
                </div>
            <?php endif; ?>
            
            <!-- Demo OTP Display (In production, this would be sent via SMS/Email) -->
            <?php if ($otpSent && isset($_SESSION['last_sent_otp'])): ?>
                <div style="background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%); border: 2px dashed #f59e0b; padding: 20px; border-radius: 12px; margin-bottom: 24px; text-align: center;">
                    <p style="color: #92400e; font-size: 0.875rem; margin-bottom: 8px;">📱 Demo Mode - Your OTP Code:</p>
                    <div style="font-size: 2.5rem; font-weight: 700; color: #92400e; letter-spacing: 8px; font-family: monospace;">
                        <?php echo $_SESSION['last_sent_otp']['otp']; ?>
                    </div>
                    <p style="color: #92400e; font-size: 0.75rem; margin-top: 8px;">Valid for 10 minutes • Sent at <?php echo $_SESSION['last_sent_otp']['time']; ?></p>
                </div>
            <?php endif; ?>
            
            <form method="post">
                <div class="form-group" style="text-align: center;">
                    <label style="display: block; font-weight: 500; margin-bottom: 12px;">Enter 6-digit OTP</label>
                    <div style="display: flex; gap: 8px; justify-content: center;">
                        <input type="text" name="otp_code" maxlength="6" pattern="[0-9]{6}" placeholder="000000" required
                            style="width: 200px; text-align: center; font-size: 1.5rem; letter-spacing: 8px; padding: 16px;">
                    </div>
                </div>
                
                <button type="submit" name="verify_otp" style="margin-top: 8px;">Verify & Continue</button>
            </form>
            
            <div style="margin-top: 24px; text-align: center;">
                <p style="color: #6b7280; font-size: 0.875rem; margin-bottom: 12px;">Didn't receive the code?</p>
                <form method="post" style="display: inline;">
                    <button type="submit" name="resend_otp" style="background: transparent; color: #f43f5e; padding: 0; font-size: 0.875rem; text-decoration: underline;">Resend OTP</button>
                </form>
            </div>
            
            <div style="margin-top: 24px; padding-top: 24px; border-top: 1px solid #e5e7eb; text-align: center;">
                <a href="register.php" style="color: #6b7280; font-size: 0.875rem;">← Back to Registration</a>
            </div>
        </div>
    </div>
</body>
</html>
