<?php
require_once dirname(dirname(dirname(__DIR__))) . '/config/config.php';
requireAuth();
requireRole('admin');
$pageTitle = 'Attendance Management';
include APP_PATH . '/views/shared/header.php';
?>
<div class="main-content">
    <?php include APP_PATH . '/views/shared/sidebar.php'; ?>
    <?php include APP_PATH . '/views/shared/topbar.php'; ?>
    <div class="content-area">
        <div class="page-header">
            <h2>Attendance Management</h2>
        </div>
        <div class="card">
            <div class="card-body">
                <p class="text-center text-muted" style="padding: 3rem;">
                    <i class="fas fa-calendar-check" style="font-size: 4rem; margin-bottom: 1rem;"></i><br>
                    Attendance management module is under construction.<br>
                    This will allow you to mark and track student attendance.
                </p>
            </div>
        </div>
    </div>
</div>
<?php include APP_PATH . '/views/shared/footer.php'; ?>
