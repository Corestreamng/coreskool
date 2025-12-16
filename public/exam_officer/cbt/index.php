<?php
/**
 * CBT Management
 * CoreSkool School Management System
 */

require_once dirname(dirname(dirname(__DIR__))) . '/config/config.php';

requireAuth();
requireRole('exam_officer');

$pageTitle = 'CBT Management';

include APP_PATH . '/views/shared/header.php';
?>

<div class="main-content">
    <?php include APP_PATH . '/views/shared/sidebar.php'; ?>
    <?php include APP_PATH . '/views/shared/topbar.php'; ?>
    
    <div class="content-area">
        <div class="card">
            <div class="card-header">
                <strong>CBT Management</strong>
            </div>
            
            <div class="card-body">
                <div style="text-align: center; padding: 3rem;">
                    <i class="fas fa-laptop" style="font-size: 4rem; color: #ddd; margin-bottom: 1rem;"></i>
                    <h3 style="color: #6b7280;">CBT Management</h3>
                    <p style="color: #9ca3af;">This feature is under development. You'll be able to manage computer-based tests here soon.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include APP_PATH . '/views/shared/footer.php'; ?>
