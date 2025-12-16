<?php
/**
 * Add New Class
 * CoreSkool School Management System
 */

require_once dirname(dirname(dirname(__DIR__))) . '/config/config.php';

requireAuth();
requireRole('admin');

$pageTitle = 'Add New Class';

include APP_PATH . '/views/shared/header.php';
?>

<div class="main-content">
    <?php include APP_PATH . '/views/shared/sidebar.php'; ?>
    <?php include APP_PATH . '/views/shared/topbar.php'; ?>
    
    <div class="content-area">
        <div class="card">
            <div class="card-header">
                <strong>Add New Class</strong>
            </div>
            
            <div class="card-body">
                <div style="text-align: center; padding: 3rem;">
                    <i class="fas fa-school" style="font-size: 4rem; color: #ddd; margin-bottom: 1rem;"></i>
                    <h3 style="color: #6b7280;">Add Class</h3>
                    <p style="color: #9ca3af;">This feature is under development.</p>
                    <a href="<?php echo BASE_URL; ?>public/admin/classes/index.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Classes
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include APP_PATH . '/views/shared/footer.php'; ?>
