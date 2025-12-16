<?php
/**
 * List Notifications
 * CoreSkool School Management System
 */

require_once dirname(dirname(__DIR__)) . '/config/config.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'notifications' => []]);
    exit;
}

$db = Database::getInstance();
$userId = $_SESSION['user_id'];
$limit = $_GET['limit'] ?? 10;

try {
    $sql = "SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT ?";
    $stmt = $db->query($sql, [$userId, (int)$limit]);
    $notifications = $stmt->fetchAll();
    
    // Format timestamps
    foreach ($notifications as &$notif) {
        $notif['created_at'] = timeAgo($notif['created_at']);
    }
    
    echo json_encode(['success' => true, 'notifications' => $notifications]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'notifications' => [], 'error' => $e->getMessage()]);
}
