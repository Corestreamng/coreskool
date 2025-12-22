<?php
/**
 * Exams
 * CoreSkool School Management System
 */

require_once dirname(dirname(__DIR__)) . '/config/config.php';

requireAuth();
requireRole('teacher');

$pageTitle = 'Exams';

include APP_PATH . '/views/shared/header.php';
?>

<div class="main-content">
    <?php include APP_PATH . '/views/shared/sidebar.php'; ?>
    <?php include APP_PATH . '/views/shared/topbar.php'; ?>
    
    <div class="content-area">
        <!-- Page Header -->
        <div style="margin-bottom: 2rem;">
            <h2 style="margin-bottom: 0.5rem;">
                <i class="fas fa-file-alt"></i> Exams
            </h2>
            <p style="color: #6b7280;">Manage exams</p>
        </div>
        
        <!-- Main Content -->
        <div class="card">
            <div class="card-header">
                <strong>Exams</strong>
            </div>
            <div class="card-body">
                <p class="text-center" style="padding: 3rem; color: #6b7280;">
                    <i class="fas fa-file-alt" style="font-size: 3rem; margin-bottom: 1rem; display: block; opacity: 0.3;"></i>
                    This page is under development.
                    <br>
                    <small>Content will be available soon.</small>
                </p>
            </div>
        </div>
    </div>
</div>

<?php include APP_PATH . '/views/shared/footer.php'; ?>