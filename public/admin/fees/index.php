<?php
/**
 * Fees and Payments Management
 * CoreSkool School Management System
 */

require_once dirname(dirname(dirname(__DIR__))) . '/config/config.php';

requireAuth();
requireRole('admin');

$pageTitle = 'Fees & Payments';
$db = Database::getInstance();

include APP_PATH . '/views/shared/header.php';
?>

<div class="main-content">
    <?php include APP_PATH . '/views/shared/sidebar.php'; ?>
    <?php include APP_PATH . '/views/shared/topbar.php'; ?>
    
    <div class="content-area">
        <div class="card">
            <div class="card-header">
                <strong>Fees & Payments Management</strong>
            </div>
            <div class="card-body">
                <p>Fees and payments management system is under development.</p>
                <p>This module will allow you to:</p>
                <ul>
                    <li>Set and manage fee structures</li>
                    <li>Track fee payments</li>
                    <li>Generate payment receipts</li>
                    <li>View payment history and reports</li>
                    <li>Send payment reminders</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php include APP_PATH . '/views/shared/footer.php'; ?>
