<?php
require_once 'config.php';

// Clear all session data
$_SESSION = array();

// Destroy session cookie
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}

// Destroy session
session_destroy();

// Show message and redirect
showAlert('You have been logged out successfully', 'success');
redirect('index.php');
?>
