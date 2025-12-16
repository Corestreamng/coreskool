<?php
/**
 * Send Message
 * CoreSkool School Management System
 */

require_once dirname(dirname(dirname(__DIR__))) . '/config/config.php';

requireAuth();
requireRole('admin');

$pageTitle = 'Send Message';
$db = Database::getInstance();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subject = sanitize($_POST['subject']);
    $message = sanitize($_POST['message']);
    $recipientType = sanitize($_POST['recipient_type']);
    $sendVia = sanitize($_POST['send_via']);
    $classId = isset($_POST['class_id']) ? (int)$_POST['class_id'] : null;
    
    if (empty($subject) || empty($message)) {
        setFlash('danger', 'Subject and message are required');
    } else {
        try {
            // Insert message
            $sql = "INSERT INTO messages (school_id, sender_id, subject, message, recipient_type, recipient_class_id, send_via, status, created_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, 'draft', NOW())";
            $db->query($sql, [$_SESSION['school_id'], $_SESSION['user_id'], $subject, $message, $recipientType, $classId, $sendVia]);
            $messageId = $db->lastInsertId();
            
            // Get recipients
            $recipients = [];
            switch ($recipientType) {
                case 'all':
                    $stmt = $db->query("SELECT id, email, phone FROM users WHERE school_id = ? AND status = 'active'", [$_SESSION['school_id']]);
                    $recipients = $stmt->fetchAll();
                    break;
                    
                case 'staff':
                    $stmt = $db->query("SELECT id, email, phone FROM users WHERE school_id = ? AND role IN ('teacher', 'exam_officer', 'cashier', 'staff', 'admin') AND status = 'active'", [$_SESSION['school_id']]);
                    $recipients = $stmt->fetchAll();
                    break;
                    
                case 'teachers':
                    $stmt = $db->query("SELECT id, email, phone FROM users WHERE school_id = ? AND role = 'teacher' AND status = 'active'", [$_SESSION['school_id']]);
                    $recipients = $stmt->fetchAll();
                    break;
                    
                case 'students':
                    $stmt = $db->query("SELECT id, email, phone FROM users WHERE school_id = ? AND role = 'student' AND status = 'active'", [$_SESSION['school_id']]);
                    $recipients = $stmt->fetchAll();
                    break;
                    
                case 'parents':
                    $stmt = $db->query("SELECT id, email, phone FROM users WHERE school_id = ? AND role = 'parent' AND status = 'active'", [$_SESSION['school_id']]);
                    $recipients = $stmt->fetchAll();
                    break;
                    
                case 'class':
                    if ($classId) {
                        $stmt = $db->query("SELECT u.id, u.email, u.phone FROM users u 
                                          INNER JOIN student_classes sc ON u.id = sc.student_id 
                                          WHERE sc.class_id = ? AND sc.status = 'active' AND u.status = 'active'", [$classId]);
                        $recipients = $stmt->fetchAll();
                    }
                    break;
            }
            
            // Create message recipients
            foreach ($recipients as $recipient) {
                $db->query("INSERT INTO message_recipients (message_id, user_id) VALUES (?, ?)", [$messageId, $recipient['id']]);
            }
            
            // Update total recipients
            $db->query("UPDATE messages SET total_recipients = ? WHERE id = ?", [count($recipients), $messageId]);
            
            // Send messages based on send_via
            $mailer = new Mailer();
            $sentCount = 0;
            
            foreach ($recipients as $recipient) {
                if (in_array($sendVia, ['email', 'all']) && !empty($recipient['email'])) {
                    $mailer->send($recipient['email'], $subject, $mailer->getEmailTemplate($subject, $message));
                    $sentCount++;
                }
                
                if (in_array($sendVia, ['sms', 'all']) && !empty($recipient['phone'])) {
                    sendSMS($recipient['phone'], shortenText($message, 160));
                    $sentCount++;
                }
            }
            
            // Update message status
            $db->query("UPDATE messages SET status = 'sent', sent_at = NOW(), sent_count = ? WHERE id = ?", [$sentCount, $messageId]);
            
            logActivity($_SESSION['user_id'], 'send_message', "Sent message to $recipientType: $subject", $_SERVER['REMOTE_ADDR']);
            setFlash('success', "Message sent successfully to " . count($recipients) . " recipients");
            redirect('admin/messages/index.php');
        } catch (Exception $e) {
            error_log("Message Send Error: " . $e->getMessage());
            setFlash('danger', 'Failed to send message');
        }
    }
}

// Get classes for dropdown
$classesStmt = $db->query("SELECT id, name FROM classes WHERE school_id = ? AND status = 'active' ORDER BY name", [$_SESSION['school_id']]);
$classes = $classesStmt->fetchAll();

include APP_PATH . '/views/shared/header.php';
?>

<div class="main-content">
    <?php include APP_PATH . '/views/shared/sidebar.php'; ?>
    <?php include APP_PATH . '/views/shared/topbar.php'; ?>
    
    <div class="content-area">
        <?php 
        $flash = getFlash();
        if ($flash): 
        ?>
            <div class="alert alert-<?php echo $flash['type']; ?>">
                <?php echo $flash['message']; ?>
            </div>
        <?php endif; ?>
        
        <div class="card">
            <div class="card-header">
                <strong>Send Message</strong>
            </div>
            
            <div class="card-body">
                <form method="POST" action="">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label class="form-label">Subject <span style="color: red;">*</span></label>
                                <input type="text" name="subject" class="form-control" required>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">Send To <span style="color: red;">*</span></label>
                                <select name="recipient_type" class="form-control" id="recipientType" required onchange="toggleClassSelect()">
                                    <option value="">Select Recipients</option>
                                    <option value="all">All Users</option>
                                    <option value="staff">All Staff</option>
                                    <option value="teachers">All Teachers</option>
                                    <option value="students">All Students</option>
                                    <option value="parents">All Parents</option>
                                    <option value="class">Specific Class</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="col-md-6" id="classSelectDiv" style="display: none;">
                            <div class="form-group">
                                <label class="form-label">Select Class</label>
                                <select name="class_id" class="form-control" id="classSelect">
                                    <option value="">Select Class</option>
                                    <?php foreach ($classes as $class): ?>
                                        <option value="<?php echo $class['id']; ?>"><?php echo $class['name']; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">Send Via <span style="color: red;">*</span></label>
                                <select name="send_via" class="form-control" required>
                                    <option value="">Select Method</option>
                                    <option value="in-app">In-app Notification Only</option>
                                    <option value="email">Email Only</option>
                                    <option value="sms">SMS Only</option>
                                    <option value="all">All Methods (Email + SMS + In-app)</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="col-md-12">
                            <div class="form-group">
                                <label class="form-label">Message <span style="color: red;">*</span></label>
                                <textarea name="message" class="form-control" rows="8" required data-maxlength="1000"></textarea>
                            </div>
                        </div>
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> <strong>Note:</strong> SMS messages are limited to 160 characters and will be automatically truncated if longer.
                    </div>
                    
                    <div class="form-group" style="margin-top: 2rem;">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-paper-plane"></i> Send Message
                        </button>
                        <a href="<?php echo BASE_URL; ?>public/admin/messages/index.php" class="btn btn-secondary btn-lg">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function toggleClassSelect() {
    const recipientType = document.getElementById('recipientType').value;
    const classDiv = document.getElementById('classSelectDiv');
    const classSelect = document.getElementById('classSelect');
    
    if (recipientType === 'class') {
        classDiv.style.display = 'block';
        classSelect.required = true;
    } else {
        classDiv.style.display = 'none';
        classSelect.required = false;
    }
}
</script>

<?php include APP_PATH . '/views/shared/footer.php'; ?>
