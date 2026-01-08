<?php
/**
 * Login Test Script
 * This script tests the login functionality with the correct credentials
 * Access via: https://coreskool.coinswipe.xyz/test_login.php
 * 
 * SECURITY: This file will self-delete after successful test
 */

// Include configuration
require_once __DIR__ . '/config/config.php';
require_once APP_PATH . '/controllers/AuthController.php';

// Check if database is installed
$checkFile = __DIR__ . '/.installed';
if (!file_exists($checkFile)) {
    die("<h2>Error</h2><p>Database not installed yet. Please run <a href='install.php'>install.php</a> first.</p>");
}

echo "<h2>Login Test</h2>";
echo "<p>Testing login with admin@coreskool.coinswipe.xyz / admin123</p>";
echo "<hr>";

try {
    // Test credentials
    $email = 'admin@coreskool.coinswipe.xyz';
    $password = 'admin123';
    
    // Get the stored hash from database
    $db = Database::getInstance();
    $sql = "SELECT email, password, role, status FROM users WHERE email = ?";
    $stmt = $db->query($sql, [$email]);
    $user = $stmt->fetch();
    
    if (!$user) {
        echo "<p style='color: red;'>❌ User not found in database!</p>";
        echo "<p>Please run <a href='install.php'>install.php</a> first.</p>";
        exit;
    }
    
    echo "<h3>User Information:</h3>";
    echo "<ul>";
    echo "<li><strong>Email:</strong> {$user['email']}</li>";
    echo "<li><strong>Role:</strong> {$user['role']}</li>";
    echo "<li><strong>Status:</strong> {$user['status']}</li>";
    echo "<li><strong>Password Hash:</strong> " . substr($user['password'], 0, 30) . "...</li>";
    echo "</ul>";
    
    // Test password verification
    echo "<h3>Password Verification Test:</h3>";
    if (password_verify($password, $user['password'])) {
        echo "<p style='color: green; font-weight: bold;'>✓ SUCCESS: Password 'admin123' matches the stored hash!</p>";
        echo "<p>Login should work correctly now.</p>";
    } else {
        echo "<p style='color: red; font-weight: bold;'>✗ FAILED: Password 'admin123' does NOT match the stored hash!</p>";
        echo "<p>The password hash needs to be fixed. Run <a href='fix_admin_password.php'>fix_admin_password.php</a></p>";
    }
    
    // Test the full login flow
    echo "<h3>Full Login Flow Test:</h3>";
    $auth = new AuthController();
    $result = $auth->login($email, $password, 'email');
    
    if ($result['success']) {
        echo "<p style='color: green; font-weight: bold;'>✓ LOGIN SUCCESSFUL!</p>";
        echo "<p>Message: {$result['message']}</p>";
        echo "<p>Redirect URL: {$result['redirect']}</p>";
        echo "<p><a href='public/index.php'>Go to Login Page</a> | <a href='{$result['redirect']}'>Go to Dashboard</a></p>";
    } else {
        echo "<p style='color: red; font-weight: bold;'>✗ LOGIN FAILED!</p>";
        echo "<p>Error: {$result['message']}</p>";
        echo "<p>Please check the password hash or run <a href='fix_admin_password.php'>fix_admin_password.php</a></p>";
    }
    
    echo "<hr>";
    
    // Self-delete this file for security after successful test
    if ($result['success']) {
        $currentFile = __FILE__;
        if (unlink($currentFile)) {
            echo "<p style='color: green;'><strong>✓ Security:</strong> This test file has been automatically deleted.</p>";
        } else {
            echo "<p style='color: orange;'><strong>⚠ Warning:</strong> Could not auto-delete this file. Please manually delete 'test_login.php' for security.</p>";
        }
    } else {
        echo "<p><strong>Note:</strong> This file will auto-delete after a successful login test. Please fix the issue and refresh.</p>";
    }
    
} catch (Exception $e) {
    echo "<h3 style='color: red;'>Error!</h3>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "<p>Please ensure the database is properly set up.</p>";
}
