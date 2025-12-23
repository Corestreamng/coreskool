<?php
/**
 * Attendance Management
 * CoreSkool School Management System
 */

require_once dirname(dirname(dirname(__DIR__))) . '/config/config.php';

requireAuth();
requireRole('admin');

$pageTitle = 'Attendance Management';
$db = Database::getInstance();

include APP_PATH . '/views/shared/header.php';
?>

<div class="main-content">
    <?php include APP_PATH . '/views/shared/sidebar.php'; ?>
    <?php include APP_PATH . '/views/shared/topbar.php'; ?>
    
    <div class="content-area">
        <div class="card">
            <div class="card-header">
                <strong>Attendance Management</strong>
            </div>
            <div class="card-body">
                <p>Attendance management system is under development.</p>
                <p>This module will allow you to:</p>
                <ul>
                    <li>Mark daily attendance for students</li>
                    <li>View attendance reports</li>
                    <li>Track attendance percentage</li>
                    <li>Generate attendance statistics</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php include APP_PATH . '/views/shared/footer.php'; ?>
