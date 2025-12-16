<?php
/**
 * Change Language
 * CoreSkool School Management System
 */

require_once dirname(dirname(__DIR__)) . '/config/config.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$language = $data['language'] ?? 'en';

// Validate language
if (!in_array($language, ['en', 'ar'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid language']);
    exit;
}

$_SESSION['language'] = $language;

echo json_encode(['success' => true, 'message' => 'Language changed successfully']);
