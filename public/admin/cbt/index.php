<?php
/**
 * CBT (Computer Based Testing) Management
 * CoreSkool School Management System
 */

require_once dirname(dirname(dirname(__DIR__))) . '/config/config.php';

requireAuth();
requireRole('admin');

$pageTitle = 'CBT Management';
$db = Database::getInstance();

include APP_PATH . '/views/shared/header.php';
?>

<div class="main-content">
    <?php include APP_PATH . '/views/shared/sidebar.php'; ?>
    <?php include APP_PATH . '/views/shared/topbar.php'; ?>
    
    <div class="content-area">
        <div class="card">
            <div class="card-header">
                <strong>Computer Based Testing (CBT)</strong>
            </div>
            <div class="card-body">
                <p>CBT management system is under development.</p>
                <p>This module will allow you to:</p>
                <ul>
                    <li>Create computer-based exams</li>
                    <li>Manage exam questions and answers</li>
                    <li>Schedule CBT exams</li>
                    <li>Monitor student attempts</li>
                    <li>View automatic grading results</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php include APP_PATH . '/views/shared/footer.php'; ?>
