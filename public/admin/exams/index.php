<?php
/**
 * Exams Management
 * CoreSkool School Management System
 */

require_once dirname(dirname(dirname(__DIR__))) . '/config/config.php';

requireAuth();
requireRole('admin');

$pageTitle = 'Exams Management';
$db = Database::getInstance();

include APP_PATH . '/views/shared/header.php';
?>

<div class="main-content">
    <?php include APP_PATH . '/views/shared/sidebar.php'; ?>
    <?php include APP_PATH . '/views/shared/topbar.php'; ?>
    
    <div class="content-area">
        <div class="card">
            <div class="card-header">
                <strong>Exams Management</strong>
            </div>
            <div class="card-body">
                <p>Exams management system is under development.</p>
                <p>This module will allow you to:</p>
                <ul>
                    <li>Create and schedule exams</li>
                    <li>Manage exam timetables</li>
                    <li>Set exam rules and regulations</li>
                    <li>Monitor exam progress</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php include APP_PATH . '/views/shared/footer.php'; ?>
