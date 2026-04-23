<?php
/**
 * Admin Password Update Script
 * Run this once to update admin credentials
 */

require_once 'config.php';

// New credentials
$newEmail = 'snaptogift@gmail.com';
$newPassword = 'anurag';

// Hash the new password
$hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

echo "<h2>Updating Admin Credentials...</h2>";
echo "<p>New Email: $newEmail</p>";
echo "<p>New Password: $newPassword</p>";
echo "<p>Password Hash: $hashedPassword</p>";

// Update admin in database
$stmt = $db->prepare("UPDATE admins SET email = ?, password = ? WHERE username = 'admin'");
$stmt->bind_param("ss", $newEmail, $hashedPassword);

if ($stmt->execute()) {
    echo "<div style='background: #d4edda; color: #155724; padding: 15px; margin: 20px 0; border-radius: 5px;'>";
    echo "<h3>✅ Admin Credentials Updated Successfully!</h3>";
    echo "<p><strong>Email:</strong> snaptogift@gmail.com</p>";
    echo "<p><strong>Password:</strong> anurag</p>";
    echo "<p><a href='admin/login.php' style='color: #155724;'>Go to Admin Login</a></p>";
    echo "</div>";
} else {
    echo "<div style='background: #f8d7da; color: #721c24; padding: 15px; margin: 20px 0; border-radius: 5px;'>";
    echo "<h3>❌ Update Failed</h3>";
    echo "<p>Error: " . $stmt->error . "</p>";
    echo "</div>";
}

// Also update database.sql for future reference
echo "<hr>";
echo "<p><strong>Note:</strong> This script has been executed. You can now delete this file for security.</p>";
?>
