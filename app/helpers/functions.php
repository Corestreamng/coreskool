<?php
/**
 * Helper Functions
 * CoreSkool School Management System
 */

/**
 * Sanitize input data
 */
function sanitize($data) {
    if (is_array($data)) {
        return array_map('sanitize', $data);
    }
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

/**
 * Redirect to a URL
 */
function redirect($url) {
    header("Location: " . BASE_URL . $url);
    exit;
}

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['user_role']);
}

/**
 * Get current user data
 */
function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    return [
        'id' => $_SESSION['user_id'],
        'role' => $_SESSION['user_role'],
        'name' => $_SESSION['user_name'] ?? '',
        'email' => $_SESSION['user_email'] ?? '',
        'school_id' => $_SESSION['school_id'] ?? null
    ];
}

/**
 * Check if user has specific role
 */
function hasRole($role) {
    return isLoggedIn() && $_SESSION['user_role'] === $role;
}

/**
 * Check if user has permission
 */
function hasPermission($permission) {
    if (!isLoggedIn()) {
        return false;
    }
    
    $permissions = $_SESSION['permissions'] ?? [];
    return in_array($permission, $permissions) || hasRole('admin');
}

/**
 * Require authentication
 */
function requireAuth() {
    if (!isLoggedIn()) {
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
        redirect('auth/login.php');
    }
}

/**
 * Require specific role
 */
function requireRole($role) {
    requireAuth();
    if (!hasRole($role)) {
        redirect('error/403.php');
    }
}

/**
 * Generate unique ID
 */
function generateUniqueId($prefix = '') {
    return $prefix . uniqid() . bin2hex(random_bytes(4));
}

/**
 * Generate matric number
 */
function generateMatricNumber($school_id = null) {
    $year = date('Y');
    $random = str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
    $school_prefix = $school_id ? substr(md5($school_id), 0, 3) : 'CS';
    return strtoupper($school_prefix) . '/' . $year . '/' . $random;
}

/**
 * Hash password
 */
function hashPassword($password) {
    return password_hash($password, PASSWORD_HASH_ALGO);
}

/**
 * Verify password
 */
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * Format date
 */
function formatDate($date, $format = 'd/m/Y') {
    if (empty($date)) return '';
    return date($format, strtotime($date));
}

/**
 * Format datetime
 */
function formatDateTime($datetime, $format = 'd/m/Y H:i') {
    if (empty($datetime)) return '';
    return date($format, strtotime($datetime));
}

/**
 * Get time ago
 */
function timeAgo($datetime) {
    $timestamp = strtotime($datetime);
    $diff = time() - $timestamp;
    
    if ($diff < 60) return 'Just now';
    if ($diff < 3600) return floor($diff / 60) . ' minutes ago';
    if ($diff < 86400) return floor($diff / 3600) . ' hours ago';
    if ($diff < 604800) return floor($diff / 86400) . ' days ago';
    
    return formatDate($datetime);
}

/**
 * Flash message
 */
function setFlash($type, $message) {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

/**
 * Get and clear flash message
 */
function getFlash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/**
 * Generate CSRF token
 */
function generateCsrfToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 */
function verifyCsrfToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Upload file
 */
function uploadFile($file, $destination, $allowedTypes = []) {
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => 'File upload error'];
    }
    
    if ($file['size'] > MAX_FILE_SIZE) {
        return ['success' => false, 'message' => 'File size exceeds limit'];
    }
    
    $fileType = mime_content_type($file['tmp_name']);
    if (!empty($allowedTypes) && !in_array($fileType, $allowedTypes)) {
        return ['success' => false, 'message' => 'File type not allowed'];
    }
    
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '_' . time() . '.' . $extension;
    $filepath = $destination . '/' . $filename;
    
    if (!is_dir($destination)) {
        mkdir($destination, 0755, true);
    }
    
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        return ['success' => true, 'filename' => $filename, 'filepath' => $filepath];
    }
    
    return ['success' => false, 'message' => 'Failed to move uploaded file'];
}

/**
 * JSON response
 */
function jsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

/**
 * Get grade from score
 */
function getGrade($score) {
    if ($score >= 90) return 'A+';
    if ($score >= 80) return 'A';
    if ($score >= 70) return 'B';
    if ($score >= 60) return 'C';
    if ($score >= 50) return 'D';
    if ($score >= 40) return 'E';
    return 'F';
}

/**
 * Get remark from score
 */
function getRemark($score) {
    if ($score >= 80) return 'Excellent';
    if ($score >= 70) return 'Very Good';
    if ($score >= 60) return 'Good';
    if ($score >= 50) return 'Fair';
    if ($score >= 40) return 'Pass';
    return 'Fail';
}

/**
 * Calculate average
 */
function calculateAverage($scores) {
    if (empty($scores)) return 0;
    return array_sum($scores) / count($scores);
}

/**
 * Get academic year
 */
function getCurrentAcademicYear() {
    return CURRENT_ACADEMIC_YEAR;
}

/**
 * Get current term
 */
function getCurrentTerm() {
    return CURRENT_TERM;
}

/**
 * Translate text
 */
function translate($key, $lang = null) {
    if ($lang === null) {
        $lang = $_SESSION['language'] ?? DEFAULT_LANGUAGE;
    }
    
    $langFile = ROOT_PATH . "/languages/{$lang}/translations.php";
    if (file_exists($langFile)) {
        $translations = include $langFile;
        return $translations[$key] ?? $key;
    }
    
    return $key;
}

/**
 * Shorten text
 */
function shortenText($text, $length = 100) {
    if (strlen($text) <= $length) {
        return $text;
    }
    return substr($text, 0, $length) . '...';
}

/**
 * Log activity
 */
function logActivity($userId, $action, $description, $ipAddress = null) {
    try {
        $db = Database::getInstance();
        $ip = $ipAddress ?? $_SERVER['REMOTE_ADDR'];
        
        $sql = "INSERT INTO activity_logs (user_id, action, description, ip_address, created_at) 
                VALUES (?, ?, ?, ?, NOW())";
        $db->query($sql, [$userId, $action, $description, $ip]);
    } catch (Exception $e) {
        error_log("Activity Log Error: " . $e->getMessage());
    }
}

/**
 * Send SMS
 */
function sendSMS($phone, $message) {
    // SMS gateway integration
    // This is a placeholder - implement based on your SMS provider
    try {
        if (empty(SMS_GATEWAY_URL) || empty(SMS_API_KEY)) {
            error_log("SMS Gateway not configured");
            return false;
        }
        
        // Example implementation - adjust based on your SMS provider
        $data = [
            'api_key' => SMS_API_KEY,
            'sender' => SMS_SENDER_ID,
            'recipient' => $phone,
            'message' => $message
        ];
        
        $ch = curl_init(SMS_GATEWAY_URL);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        return $httpCode === 200;
    } catch (Exception $e) {
        error_log("SMS Send Error: " . $e->getMessage());
        return false;
    }
}

/**
 * Format phone number
 */
function formatPhoneNumber($phone) {
    // Remove all non-numeric characters
    $phone = preg_replace('/[^0-9]/', '', $phone);
    
    // Add country code if not present (assuming Nigeria +234)
    if (strlen($phone) === 10) {
        $phone = '234' . substr($phone, 1);
    } elseif (strlen($phone) === 11 && substr($phone, 0, 1) === '0') {
        $phone = '234' . substr($phone, 1);
    }
    
    return $phone;
}

/**
 * Get avatar URL
 */
function getAvatarUrl($filename) {
    if (empty($filename) || !file_exists(UPLOADS_PATH . '/' . $filename)) {
        return BASE_URL . 'public/assets/images/default-avatar.png';
    }
    return BASE_URL . 'public/uploads/' . $filename;
}

/**
 * Pagination
 */
function paginate($totalItems, $currentPage, $itemsPerPage = ITEMS_PER_PAGE) {
    $totalPages = ceil($totalItems / $itemsPerPage);
    $currentPage = max(1, min($currentPage, $totalPages));
    $offset = ($currentPage - 1) * $itemsPerPage;
    
    return [
        'total_items' => $totalItems,
        'total_pages' => $totalPages,
        'current_page' => $currentPage,
        'items_per_page' => $itemsPerPage,
        'offset' => $offset,
        'has_previous' => $currentPage > 1,
        'has_next' => $currentPage < $totalPages
    ];
}
