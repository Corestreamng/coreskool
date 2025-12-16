<?php
/**
 * Authentication Controller
 * CoreSkool School Management System
 */

require_once ROOT_PATH . '/config/config.php';

class AuthController {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Login user
     */
    public function login($identifier, $password, $loginType = 'email') {
        try {
            // Determine login field based on type
            $field = match($loginType) {
                'email' => 'email',
                'phone' => 'phone',
                'matric' => 'matric_number',
                default => 'email'
            };
            
            // Get user
            $sql = "SELECT u.*, s.name as school_name, s.id as school_id 
                    FROM users u 
                    LEFT JOIN schools s ON u.school_id = s.id 
                    WHERE u.$field = ? AND u.status = 'active' 
                    LIMIT 1";
            
            $stmt = $this->db->query($sql, [$identifier]);
            $user = $stmt->fetch();
            
            if (!$user) {
                return ['success' => false, 'message' => 'Invalid credentials'];
            }
            
            // Verify password
            if (!verifyPassword($password, $user['password'])) {
                return ['success' => false, 'message' => 'Invalid credentials'];
            }
            
            // Validate login type based on role
            if (!$this->validateLoginType($user['role'], $loginType)) {
                return ['success' => false, 'message' => 'Invalid login method for this role'];
            }
            
            // Update last login
            $updateSql = "UPDATE users SET last_login = NOW() WHERE id = ?";
            $this->db->query($updateSql, [$user['id']]);
            
            // Set session
            $this->setUserSession($user);
            
            // Get permissions
            $this->loadUserPermissions($user['id']);
            
            // Log activity
            logActivity($user['id'], 'login', 'User logged in', $_SERVER['REMOTE_ADDR']);
            
            return [
                'success' => true, 
                'message' => 'Login successful',
                'redirect' => $this->getRedirectUrl($user['role'])
            ];
            
        } catch (Exception $e) {
            error_log("Login Error: " . $e->getMessage());
            return ['success' => false, 'message' => 'An error occurred during login'];
        }
    }
    
    /**
     * Validate login type based on role
     */
    private function validateLoginType($role, $loginType) {
        $allowedTypes = [
            'admin' => ['email', 'phone'],
            'teacher' => ['phone'],
            'student' => ['matric'],
            'parent' => ['email', 'phone'],
            'exam_officer' => ['email', 'phone'],
            'cashier' => ['email', 'phone'],
            'staff' => ['email', 'phone']
        ];
        
        return in_array($loginType, $allowedTypes[$role] ?? []);
    }
    
    /**
     * Set user session
     */
    private function setUserSession($user) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_avatar'] = $user['avatar'];
        $_SESSION['school_id'] = $user['school_id'];
        $_SESSION['school_name'] = $user['school_name'];
        $_SESSION['login_time'] = time();
    }
    
    /**
     * Load user permissions
     */
    private function loadUserPermissions($userId) {
        $sql = "SELECT DISTINCT p.name 
                FROM permissions p
                INNER JOIN user_permissions up ON p.id = up.permission_id
                WHERE up.user_id = ?";
        
        $stmt = $this->db->query($sql, [$userId]);
        $permissions = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        $_SESSION['permissions'] = $permissions;
    }
    
    /**
     * Get redirect URL based on role
     */
    private function getRedirectUrl($role) {
        $urls = [
            'admin' => 'admin/dashboard.php',
            'teacher' => 'teacher/dashboard.php',
            'student' => 'student/dashboard.php',
            'parent' => 'parent/dashboard.php',
            'exam_officer' => 'exam_officer/dashboard.php',
            'cashier' => 'cashier/dashboard.php',
            'staff' => 'staff/dashboard.php'
        ];
        
        return $urls[$role] ?? 'index.php';
    }
    
    /**
     * Logout user
     */
    public function logout() {
        if (isset($_SESSION['user_id'])) {
            logActivity($_SESSION['user_id'], 'logout', 'User logged out', $_SERVER['REMOTE_ADDR']);
        }
        
        session_destroy();
        return ['success' => true, 'message' => 'Logged out successfully'];
    }
    
    /**
     * Register user
     */
    public function register($data) {
        try {
            // Validate required fields
            $required = ['first_name', 'last_name', 'email', 'password', 'role', 'gender'];
            foreach ($required as $field) {
                if (empty($data[$field])) {
                    return ['success' => false, 'message' => "Field $field is required"];
                }
            }
            
            // Check if email exists
            $checkSql = "SELECT id FROM users WHERE email = ? LIMIT 1";
            $stmt = $this->db->query($checkSql, [$data['email']]);
            if ($stmt->fetch()) {
                return ['success' => false, 'message' => 'Email already exists'];
            }
            
            // Generate matric number for students
            if ($data['role'] === 'student') {
                $data['matric_number'] = generateMatricNumber($data['school_id'] ?? null);
            }
            
            // Hash password
            $data['password'] = hashPassword($data['password']);
            
            // Insert user
            $sql = "INSERT INTO users (school_id, email, phone, password, role, matric_number, 
                    first_name, last_name, other_names, gender, date_of_birth, address, 
                    city, state, country, status) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'active')";
            
            $params = [
                $data['school_id'] ?? 1,
                $data['email'],
                $data['phone'] ?? null,
                $data['password'],
                $data['role'],
                $data['matric_number'] ?? null,
                $data['first_name'],
                $data['last_name'],
                $data['other_names'] ?? null,
                $data['gender'],
                $data['date_of_birth'] ?? null,
                $data['address'] ?? null,
                $data['city'] ?? null,
                $data['state'] ?? null,
                $data['country'] ?? 'Nigeria'
            ];
            
            $this->db->query($sql, $params);
            $userId = $this->db->lastInsertId();
            
            // Log activity
            logActivity($userId, 'register', 'User registered', $_SERVER['REMOTE_ADDR']);
            
            return [
                'success' => true, 
                'message' => 'Registration successful',
                'user_id' => $userId,
                'matric_number' => $data['matric_number'] ?? null
            ];
            
        } catch (Exception $e) {
            error_log("Registration Error: " . $e->getMessage());
            return ['success' => false, 'message' => 'An error occurred during registration'];
        }
    }
    
    /**
     * Reset password
     */
    public function resetPassword($email) {
        try {
            // Check if user exists
            $sql = "SELECT id, first_name, last_name FROM users WHERE email = ? LIMIT 1";
            $stmt = $this->db->query($sql, [$email]);
            $user = $stmt->fetch();
            
            if (!$user) {
                return ['success' => false, 'message' => 'Email not found'];
            }
            
            // Generate reset token
            $token = bin2hex(random_bytes(32));
            $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));
            
            // Store token (you may want to create a password_resets table)
            $_SESSION['password_reset_token'] = $token;
            $_SESSION['password_reset_user'] = $user['id'];
            $_SESSION['password_reset_expiry'] = $expiry;
            
            // Send reset email
            $mailer = new Mailer();
            $resetLink = BASE_URL . "auth/reset-password.php?token=$token";
            $content = "
                <p>Hello {$user['first_name']} {$user['last_name']},</p>
                <p>You have requested to reset your password. Click the button below to reset your password:</p>
                <p>This link will expire in 1 hour.</p>
                <p>If you did not request this, please ignore this email.</p>
            ";
            
            $mailer->send(
                $email,
                'Password Reset Request',
                $mailer->getEmailTemplate('Password Reset', $content, 'Reset Password', $resetLink)
            );
            
            return ['success' => true, 'message' => 'Password reset link sent to your email'];
            
        } catch (Exception $e) {
            error_log("Password Reset Error: " . $e->getMessage());
            return ['success' => false, 'message' => 'An error occurred'];
        }
    }
    
    /**
     * Update password
     */
    public function updatePassword($userId, $newPassword) {
        try {
            $hashedPassword = hashPassword($newPassword);
            $sql = "UPDATE users SET password = ?, updated_at = NOW() WHERE id = ?";
            $this->db->query($sql, [$hashedPassword, $userId]);
            
            logActivity($userId, 'password_change', 'Password changed', $_SERVER['REMOTE_ADDR']);
            
            return ['success' => true, 'message' => 'Password updated successfully'];
        } catch (Exception $e) {
            error_log("Update Password Error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to update password'];
        }
    }
    
    /**
     * Check session validity
     */
    public function checkSession() {
        if (!isLoggedIn()) {
            return false;
        }
        
        // Check session timeout
        if (isset($_SESSION['login_time'])) {
            $elapsed = time() - $_SESSION['login_time'];
            if ($elapsed > SESSION_TIMEOUT) {
                $this->logout();
                return false;
            }
        }
        
        return true;
    }
}
