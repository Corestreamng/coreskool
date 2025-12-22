<?php
/**
 * Reset Password Page
 * CoreSkool School Management System
 */

require_once dirname(dirname(__DIR__)) . '/config/config.php';

// If already logged in, redirect to dashboard
if (isLoggedIn()) {
    $role = $_SESSION['user_role'];
    redirect($role . '/dashboard.php');
}

$token = $_GET['token'] ?? '';
$error = null;
$success = false;
$validToken = false;

// Verify token
if (!empty($token)) {
    $db = Database::getInstance();
    define('RESET_TOKEN_UNUSED', 0);
    $resetQuery = $db->query(
        "SELECT pr.*, u.email FROM password_resets pr 
         INNER JOIN users u ON pr.user_id = u.id 
         WHERE pr.token = ? AND pr.expires_at > NOW() AND pr.used = ?",
        [$token, RESET_TOKEN_UNUSED]
    );
    $reset = $resetQuery->fetch();
    
    if ($reset) {
        $validToken = true;
        
        // Handle password reset form submission
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $password = $_POST['password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';
            
            if (empty($password) || empty($confirmPassword)) {
                $error = 'Please fill in all fields';
            } elseif (strlen($password) < 6) {
                $error = 'Password must be at least 6 characters long';
            } elseif ($password !== $confirmPassword) {
                $error = 'Passwords do not match';
            } else {
                // Update password
                $hashedPassword = hashPassword($password);
                $db->query("UPDATE users SET password = ? WHERE id = ?", [$hashedPassword, $reset['user_id']]);
                
                // Mark token as used
                $db->query("UPDATE password_resets SET used = 1 WHERE id = ?", [$reset['id']]);
                
                $success = true;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/assets/css/style.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .reset-password-container {
            width: 100%;
            max-width: 450px;
            padding: 20px;
        }
        
        .reset-password-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            padding: 3rem 2.5rem;
        }
        
        .reset-password-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .reset-password-logo {
            width: 80px;
            height: 80px;
            margin: 0 auto 1rem;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            color: white;
            font-weight: bold;
        }
        
        .reset-password-header h1 {
            font-size: 1.75rem;
            margin-bottom: 0.5rem;
        }
        
        .reset-password-header p {
            color: #6b7280;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <div class="reset-password-container">
        <div class="reset-password-card">
            <div class="reset-password-header">
                <div class="reset-password-logo">CS</div>
                <h1>Reset Password</h1>
                <p>Enter your new password below.</p>
            </div>
            
            <?php if ($success): ?>
                <div class="alert alert-success">
                    Your password has been reset successfully!
                    <br><br>
                    <a href="<?php echo BASE_URL; ?>public/index.php" class="btn btn-primary btn-block">
                        Go to Login
                    </a>
                </div>
            <?php elseif (!$validToken): ?>
                <div class="alert alert-danger">
                    Invalid or expired reset token. Please request a new password reset link.
                    <br><br>
                    <a href="<?php echo BASE_URL; ?>public/auth/forgot-password.php" class="btn btn-primary btn-block">
                        Request New Link
                    </a>
                </div>
            <?php else: ?>
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="password" class="form-label">New Password</label>
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            class="form-control" 
                            placeholder="Enter new password"
                            required
                            minlength="6"
                        >
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password" class="form-label">Confirm Password</label>
                        <input 
                            type="password" 
                            id="confirm_password" 
                            name="confirm_password" 
                            class="form-control" 
                            placeholder="Confirm new password"
                            required
                            minlength="6"
                        >
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-block btn-lg">
                        Reset Password
                    </button>
                </form>
            <?php endif; ?>
        </div>
    </div>
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
</body>
</html>
