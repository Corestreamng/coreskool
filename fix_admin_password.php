<?php
/**
 * Fix Admin Password Script
 * Run this script to update the admin password to 'admin123'
 * Access via: https://coreskool.coinswipe.xyz/fix_admin_password.php
 * 
 * SECURITY: This file will self-delete after successful execution
 */

// Include configuration
require_once __DIR__ . '/config/config.php';

// Check if database is already installed
$checkFile = __DIR__ . '/.installed';
if (!file_exists($checkFile)) {
    die("<h2>Error</h2><p>Database not installed yet. Please run <a href='install.php'>install.php</a> first.</p>");
}

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
    
    // Self-delete this file for security
    $currentFile = __FILE__;
    if (unlink($currentFile)) {
        echo "<p style='color: green;'><strong>✓ Security:</strong> This file has been automatically deleted.</p>";
    } else {
        echo "<p style='color: orange;'><strong>⚠ Warning:</strong> Could not auto-delete this file. Please manually delete 'fix_admin_password.php' for security.</p>";
    }
    
} catch (Exception $e) {
    echo "<h3 style='color: red;'>Error!</h3>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "<p>Please ensure the database is properly set up by running <a href='install.php'>install.php</a> first.</p>";
}
