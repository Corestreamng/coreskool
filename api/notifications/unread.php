<?php
/**
 * Get Unread Notifications Count
 * CoreSkool School Management System
 */

require_once dirname(dirname(__DIR__)) . '/config/config.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'count' => 0]);
    exit;
}

$db = Database::getInstance();
$userId = $_SESSION['user_id'];

try {
    $sql = "SELECT COUNT(*) as count FROM notifications WHERE user_id = ? AND is_read = 0";
    $stmt = $db->query($sql, [$userId]);
    $result = $stmt->fetch();
    
    echo json_encode(['success' => true, 'count' => $result['count']]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'count' => 0, 'error' => $e->getMessage()]);
}
