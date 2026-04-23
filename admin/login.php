<?php
require_once '../config.php';

// If already logged in as admin, redirect to dashboard
if (isAdmin()) {
    redirect('index.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];
    
    if (empty($email) || empty($password)) {
        $error = 'Please enter email and password';
    } else {
        $stmt = $db->prepare("SELECT id, password, username, role, email FROM admins WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($admin = $result->fetch_assoc()) {
            if (password_verify($password, $admin['password'])) {
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_username'] = $admin['username'];
                $_SESSION['admin_role'] = $admin['role'];
                
                showAlert('Welcome to Admin Panel, ' . $admin['username'], 'success');
                redirect('index.php');
            } else {
                $error = 'Invalid password';
            }
        } else {
            $error = 'Invalid email';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - SnapToGift</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body {
            background: linear-gradient(135deg, #be123c 0%, #e11d48 50%, #fb7185 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            background: white;
            border-radius: 24px;
            box-shadow: 0 25px 80px rgba(190, 18, 60, 0.4);
            overflow: hidden;
            border: none;
        }
        .login-header {
            background: linear-gradient(135deg, #9f1239 0%, #be123c 50%, #e11d48 100%);
            color: white;
            padding: 50px 40px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        .login-header::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 60%);
            animation: shimmer 3s ease-in-out infinite;
        }
        @keyframes shimmer {
            0%, 100% { transform: translateX(-100%) rotate(45deg); }
            50% { transform: translateX(100%) rotate(45deg); }
        }
        .login-body {
            padding: 45px 40px;
        }
        .btn-primary {
            background: linear-gradient(135deg, #be123c 0%, #e11d48 100%);
            border: none;
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, #9f1239 0%, #be123c 100%);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(190, 18, 60, 0.3);
        }
        .form-control:focus, .input-group-text {
            border-color: #e11d48;
            box-shadow: 0 0 0 0.2rem rgba(225, 29, 72, 0.15);
        }
        .input-group-text {
            background: #fff1f2;
            color: #be123c;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="login-card">
                    <div class="login-header">
                        <i class="bi bi-gift-fill display-1 position-relative" style="z-index: 1;"></i>
                        <h3 class="mt-3 mb-0 position-relative" style="z-index: 1;">SnapToGift</h3>
                        <p class="mb-0 opacity-75 position-relative" style="z-index: 1;">Admin Management</p>
                    </div>
                    <div class="login-body">
                        <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>
                        
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                    <input type="email" name="email" class="form-control" placeholder="Enter email" required>
                                </div>
                            </div>
                            <div class="mb-4">
                                <label class="form-label">Password</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                    <input type="password" name="password" class="form-control" placeholder="Enter password" required>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary w-100 py-2">
                                <i class="bi bi-box-arrow-in-right me-2"></i>Login
                            </button>
                        </form>
                        
                        <div class="text-center mt-3">
                            <small class="text-muted">Email: <code>snaptogift@gmail.com</code> / Password: <code>anurag</code></small>
                        </div>
                        
                        <hr class="my-4">
                        
                        <div class="text-center">
                            <a href="../index.php" class="text-decoration-none">
                                <i class="bi bi-arrow-left me-1"></i>Back to Website
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
