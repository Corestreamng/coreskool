<?php
/**
 * Logout Page
 * CoreSkool School Management System
 */

require_once dirname(dirname(__DIR__)) . '/config/config.php';
require_once APP_PATH . '/controllers/AuthController.php';

$auth = new AuthController();
$auth->logout();

redirect('index.php');
