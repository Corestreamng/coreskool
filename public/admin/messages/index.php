<?php
/**
 * Messages
 * CoreSkool School Management System
 */

require_once dirname(dirname(dirname(__DIR__))) . '/config/config.php';

requireAuth();
requireRole('admin');

$pageTitle = 'Messages';
$db = Database::getInstance();

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
                <p>Messages and communication system is under development.</p>
                <p>This module will allow you to:</p>
                <ul>
                    <li>Send SMS messages to students, parents, and teachers</li>
                    <li>Send email notifications</li>
                    <li>View message history</li>
                    <li>Track message delivery status</li>
                    <li>Send bulk messages</li>
                </ul>
                <br>
                <p><a href="<?php echo BASE_URL; ?>public/admin/messages/send.php" class="btn btn-primary">
                    <i class="fas fa-paper-plane"></i> Send a Message Now
                </a></p>
            </div>
        </div>
    </div>
</div>

<?php include APP_PATH . '/views/shared/footer.php'; ?>
