<?php
/**
 * Main Configuration File
 * CoreSkool School Management System
 */

// Error Reporting (Set to 0 in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Timezone
date_default_timezone_set('Africa/Lagos');

// Base URL Configuration
define('BASE_URL', 'https://coreskool.coinswipe.xyz/');
define('SITE_NAME', 'CoreSkool');
define('SITE_TITLE', 'CoreSkool - School Management System');

// Path Configuration
define('ROOT_PATH', dirname(__DIR__));
define('APP_PATH', ROOT_PATH . '/app');
define('PUBLIC_PATH', ROOT_PATH . '/public');
define('UPLOADS_PATH', PUBLIC_PATH . '/uploads');

// Session Configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 1);
session_start();

// Email Configuration
define('SMTP_HOST', 'mail.coreskool.coinswipe.xyz');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'noreply@coreskool.coinswipe.xyz');
define('SMTP_PASSWORD', 'C01ne$w1pe');
define('SMTP_FROM_EMAIL', 'noreply@coreskool.coinswipe.xyz');
define('SMTP_FROM_NAME', 'CoreSkool System');

// SMS Configuration (Can be configured later)
define('SMS_GATEWAY_URL', '');
define('SMS_API_KEY', '');
define('SMS_SENDER_ID', 'CoreSkool');

// Pagination
define('ITEMS_PER_PAGE', 20);

// File Upload Settings
define('MAX_FILE_SIZE', 5242880); // 5MB
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/gif']);
define('ALLOWED_DOCUMENT_TYPES', ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document']);

// Default Language
define('DEFAULT_LANGUAGE', 'en');

// SaaS Configuration
define('MULTI_TENANT_MODE', true);
define('SCHOOL_SUBDOMAIN', ''); // For multi-tenant mode

// Security
define('PASSWORD_HASH_ALGO', PASSWORD_DEFAULT);
define('SESSION_TIMEOUT', 3600); // 1 hour

// Academic Settings
define('CURRENT_ACADEMIC_YEAR', '2024/2025');
define('CURRENT_TERM', '1');

// Load Required Files
require_once ROOT_PATH . '/config/database.php';
require_once ROOT_PATH . '/app/helpers/functions.php';
