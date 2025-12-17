<?php
/**
 * Messages - Teacher
 * CoreSkool School Management System
 */

require_once dirname(dirname(dirname(__DIR__))) . '/config/config.php';

requireAuth();
requireRole('teacher');

$pageTitle = 'Messages';
$db = Database::getInstance();
$userId = $_SESSION['user_id'];

// Get messages
$messagesQuery = $db->query("
    SELECT m.*, u.first_name, u.last_name, mr.is_read, mr.read_at
    FROM messages m
    INNER JOIN message_recipients mr ON m.id = mr.message_id
    INNER JOIN users u ON m.sender_id = u.id
    WHERE mr.user_id = ?
    ORDER BY m.created_at DESC
", [$userId]);
$messages = $messagesQuery->fetchAll();

include APP_PATH . '/views/shared/header.php';
?>

<div class="main-content">
    <?php include APP_PATH . '/views/shared/sidebar.php'; ?>
    <?php include APP_PATH . '/views/shared/topbar.php'; ?>
    
    <div class="content-area">
        <div class="page-header" style="margin-bottom: 2rem;">
            <h2>Messages</h2>
            <p style="color: #6b7280;">View and send messages</p>
        </div>

        <div class="card">
            <div class="card-header"><strong>Inbox</strong></div>
            <div class="card-body">
                <?php if (empty($messages)): ?>
                    <div style="text-align: center; padding: 3rem;">
                        <i class="fas fa-envelope" style="font-size: 4rem; color: #e5e7eb; margin-bottom: 1rem;"></i>
                        <h3 style="color: #6b7280;">No Messages</h3>
                        <p style="color: #9ca3af;">You haven't received any messages yet</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th width="40"></th>
                                    <th>From</th>
                                    <th>Subject</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($messages as $message): ?>
                                    <tr style="<?php echo !$message['is_read'] ? 'font-weight: bold; background-color: #f9fafb;' : ''; ?>">
                                        <td>
                                            <?php if (!$message['is_read']): ?>
                                                <i class="fas fa-circle" style="color: #3b82f6; font-size: 0.5rem;"></i>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($message['first_name'] . ' ' . $message['last_name']); ?></td>
                                        <td>
                                            <a href="<?php echo BASE_URL; ?>public/teacher/messages/view.php?id=<?php echo $message['id']; ?>" 
                                               style="color: inherit; text-decoration: none;">
                                                <?php echo htmlspecialchars($message['subject']); ?>
                                            </a>
                                        </td>
                                        <td><?php echo date('M d, Y h:i A', strtotime($message['created_at'])); ?></td>
                                        <td>
                                            <a href="<?php echo BASE_URL; ?>public/teacher/messages/view.php?id=<?php echo $message['id']; ?>" 
                                               class="btn btn-sm btn-primary">
                                                <i class="fas fa-eye"></i> View
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include APP_PATH . '/views/shared/footer.php'; ?>
