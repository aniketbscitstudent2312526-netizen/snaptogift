<?php
require_once '../config.php';

// Clear admin session
unset($_SESSION['admin_id']);
unset($_SESSION['admin_username']);
unset($_SESSION['admin_role']);

showAlert('Logged out successfully', 'success');
redirect('login.php');
?>
