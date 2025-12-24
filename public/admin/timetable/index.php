<?php
/**
 * Timetable Management
 * CoreSkool School Management System
 */

require_once dirname(dirname(dirname(__DIR__))) . '/config/config.php';

requireAuth();
requireRole('admin');

$pageTitle = 'Timetable Management';
$db = Database::getInstance();

include APP_PATH . '/views/shared/header.php';
?>

<div class="main-content">
    <?php include APP_PATH . '/views/shared/sidebar.php'; ?>
    <?php include APP_PATH . '/views/shared/topbar.php'; ?>
    
    <div class="content-area">
        <div class="card">
            <div class="card-header">
                <strong>Timetable Management</strong>
            </div>
            <div class="card-body">
                <p>Timetable management system is under development.</p>
                <p>This module will allow you to:</p>
                <ul>
                    <li>Create class timetables</li>
                    <li>Assign teachers to periods</li>
                    <li>Manage exam timetables</li>
                    <li>View and print timetables</li>
                    <li>Handle timetable conflicts</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php include APP_PATH . '/views/shared/footer.php'; ?>
