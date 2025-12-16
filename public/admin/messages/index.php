<?php
/**
 * Messages Management
 * CoreSkool School Management System
 */

require_once dirname(dirname(dirname(__DIR__))) . '/config/config.php';

requireAuth();
requireRole('admin');

$pageTitle = 'Messages';

include APP_PATH . '/views/shared/header.php';
?>

<div class="main-content">
    <?php include APP_PATH . '/views/shared/sidebar.php'; ?>
    <?php include APP_PATH . '/views/shared/topbar.php'; ?>
    
    <div class="content-area">
        <div class="card">
            <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                <strong>Messages</strong>
                <a href="<?php echo BASE_URL; ?>public/admin/messages/send.php" class="btn btn-primary">
                    <i class="fas fa-paper-plane"></i> Send Message
                </a>
            </div>
            
            <div class="card-body">
                <div style="text-align: center; padding: 3rem;">
                    <i class="fas fa-comment" style="font-size: 4rem; color: #ddd; margin-bottom: 1rem;"></i>
                    <h3 style="color: #6b7280;">Messages</h3>
                    <p style="color: #9ca3af;">This feature is under development. You can still send messages using the button above.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include APP_PATH . '/views/shared/footer.php'; ?>
