<?php
/**
 * Database Installation Script
 * CoreSkool School Management System
 * Run this file once to setup the database
 */

// Include configuration
require_once __DIR__ . '/config/config.php';

// Check if already installed
$checkFile = __DIR__ . '/.installed';
if (file_exists($checkFile)) {
    die("System is already installed. Delete .installed file to reinstall (WARNING: This will delete all data).");
}

try {
    // Connect to database
    $db = Database::getInstance()->getConnection();
    
    // Read SQL file
    $sqlFile = __DIR__ . '/database/migrations/001_create_tables.sql';
    if (!file_exists($sqlFile)) {
        die("SQL migration file not found!");
    }
    
    $sql = file_get_contents($sqlFile);
    
    // Split into individual queries
    $queries = array_filter(array_map('trim', explode(';', $sql)));
    
    echo "<h2>CoreSkool Database Installation</h2>";
    echo "<p>Starting installation...</p>";
    
    $successCount = 0;
    $errorCount = 0;
    
    foreach ($queries as $query) {
        if (empty($query) || strpos($query, '--') === 0) {
            continue;
        }
        
        try {
            $db->exec($query);
            $successCount++;
        } catch (PDOException $e) {
            echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
            $errorCount++;
        }
    }
    
    echo "<h3>Installation Complete!</h3>";
    echo "<p>Executed $successCount queries successfully.</p>";
    
    if ($errorCount > 0) {
        echo "<p style='color: red;'>$errorCount queries failed.</p>";
    }
    
    // Create installation marker
    file_put_contents($checkFile, date('Y-m-d H:i:s'));
    
    echo "<h3>Default Credentials:</h3>";
    echo "<p><strong>Email:</strong> admin@coreskool.coinswipe.xyz<br>";
    echo "<strong>Password:</strong> admin123</p>";
    echo "<p><a href='public/index.php'>Go to Login Page</a></p>";
    
} catch (Exception $e) {
    echo "<h3 style='color: red;'>Installation Failed!</h3>";
    echo "<p>" . $e->getMessage() . "</p>";
}
