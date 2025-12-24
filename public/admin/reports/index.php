<?php
/**
 * Reports
 * CoreSkool School Management System
 */

require_once dirname(dirname(dirname(__DIR__))) . '/config/config.php';

requireAuth();
requireRole('admin');

$pageTitle = 'Reports';
$db = Database::getInstance();

include APP_PATH . '/views/shared/header.php';
?>

<div class="main-content">
    <?php include APP_PATH . '/views/shared/sidebar.php'; ?>
    <?php include APP_PATH . '/views/shared/topbar.php'; ?>
    
    <div class="content-area">
        <div class="card">
            <div class="card-header">
                <strong>Reports & Analytics</strong>
            </div>
            <div class="card-body">
                <p>Reports and analytics system is under development.</p>
                <p>This module will allow you to:</p>
                <ul>
                    <li>Generate various school reports</li>
                    <li>View attendance statistics</li>
                    <li>Analyze academic performance</li>
                    <li>Track fee collection</li>
                    <li>Export reports to PDF/Excel</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php include APP_PATH . '/views/shared/footer.php'; ?>
