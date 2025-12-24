<?php
/**
 * Hostel Management
 * CoreSkool School Management System
 */

require_once dirname(dirname(dirname(__DIR__))) . '/config/config.php';

requireAuth();
requireRole('admin');

$pageTitle = 'Hostel Management';
$db = Database::getInstance();

include APP_PATH . '/views/shared/header.php';
?>

<div class="main-content">
    <?php include APP_PATH . '/views/shared/sidebar.php'; ?>
    <?php include APP_PATH . '/views/shared/topbar.php'; ?>
    
    <div class="content-area">
        <div class="card">
            <div class="card-header">
                <strong>Hostel Management</strong>
            </div>
            <div class="card-body">
                <p>Hostel management system is under development.</p>
                <p>This module will allow you to:</p>
                <ul>
                    <li>Manage hostels and rooms</li>
                    <li>Allocate rooms to students</li>
                    <li>Track room occupancy</li>
                    <li>Manage hostel staff and wardens</li>
                    <li>Handle hostel fees</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php include APP_PATH . '/views/shared/footer.php'; ?>
