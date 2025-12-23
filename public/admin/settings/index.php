<?php
/**
 * Settings
 * CoreSkool School Management System
 */

require_once dirname(dirname(dirname(__DIR__))) . '/config/config.php';

requireAuth();
requireRole('admin');

$pageTitle = 'Settings';
$db = Database::getInstance();

include APP_PATH . '/views/shared/header.php';
?>

<div class="main-content">
    <?php include APP_PATH . '/views/shared/sidebar.php'; ?>
    <?php include APP_PATH . '/views/shared/topbar.php'; ?>
    
    <div class="content-area">
        <div class="card">
            <div class="card-header">
                <strong>System Settings</strong>
            </div>
            <div class="card-body">
                <p>Settings management is under development.</p>
                <p>This module will allow you to:</p>
                <ul>
                    <li>Configure school information</li>
                    <li>Manage academic years and terms</li>
                    <li>Set up email and SMS settings</li>
                    <li>Configure payment gateways</li>
                    <li>Manage user roles and permissions</li>
                    <li>Customize system appearance</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php include APP_PATH . '/views/shared/footer.php'; ?>
