<?php
/**
 * Fix Admin Password Script
 * Run this script to update the admin password to 'admin123'
 * Access via: https://coreskool.coinswipe.xyz/fix_admin_password.php
 */

// Include configuration
require_once __DIR__ . '/config/config.php';

try {
    // Connect to database
    $db = Database::getInstance();
    
    // The correct hash for 'admin123'
    $correctPasswordHash = '$2y$10$oY1NSNGLF22bzdDhCbdxUuYEcTKV.ucL/8jPS/ICJXFIghvvRBaCO';
    
    // Update admin password
    $sql = "UPDATE users 
            SET password = ? 
            WHERE email = 'admin@coreskool.coinswipe.xyz' AND role = 'admin'";
    
    $stmt = $db->query($sql, [$correctPasswordHash]);
    
    echo "<h2>Admin Password Fix</h2>";
    echo "<p style='color: green;'>✓ Admin password has been successfully updated to 'admin123'</p>";
    echo "<h3>Login Credentials:</h3>";
    echo "<p><strong>Email:</strong> admin@coreskool.coinswipe.xyz<br>";
    echo "<strong>Password:</strong> admin123</p>";
    echo "<p><a href='public/index.php'>Go to Login Page</a></p>";
    echo "<hr>";
    echo "<p><strong>Note:</strong> For security reasons, please delete this file (fix_admin_password.php) after use.</p>";
    
} catch (Exception $e) {
    echo "<h3 style='color: red;'>Error!</h3>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "<p>Please ensure the database is properly set up by running <a href='install.php'>install.php</a> first.</p>";
}
