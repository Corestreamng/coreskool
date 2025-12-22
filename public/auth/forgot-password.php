<?php
/**
 * Forgot Password Page
 * CoreSkool School Management System
 */

require_once dirname(dirname(__DIR__)) . '/config/config.php';

// If already logged in, redirect to dashboard
if (isLoggedIn()) {
    $role = $_SESSION['user_role'];
    redirect($role . '/dashboard.php');
}

$success = false;
$error = null;

// Handle forgot password form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email'] ?? '');
    
    if (empty($email)) {
        $error = 'Please enter your email address';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address';
    } else {
        // Check if email exists
        $db = Database::getInstance();
        $userQuery = $db->query("SELECT id, first_name FROM users WHERE email = ? AND status = 'active'", [$email]);
        $user = $userQuery->fetch();
        
        if ($user) {
            // Generate reset token
            $token = bin2hex(random_bytes(32));
            $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));
            
            // Store token in database
            $db->query(
                "INSERT INTO password_resets (user_id, token, expires_at, created_at) VALUES (?, ?, ?, NOW())",
                [$user['id'], $token, $expiry]
            );
            
            // Send email (simplified for now)
            $resetLink = BASE_URL . "public/auth/reset-password.php?token=" . $token;
            
            // TODO: Implement actual email sending
            // For now, we'll just show success
            $success = true;
            $resetLinkDisplay = $resetLink; // For testing purposes
        } else {
            // Don't reveal if email exists or not for security
            $success = true;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/assets/css/style.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .forgot-password-container {
            width: 100%;
            max-width: 450px;
            padding: 20px;
        }
        
        .forgot-password-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            padding: 3rem 2.5rem;
        }
        
        .forgot-password-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .forgot-password-logo {
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
        
        .forgot-password-header h1 {
            font-size: 1.75rem;
            margin-bottom: 0.5rem;
        }
        
        .forgot-password-header p {
            color: #6b7280;
            font-size: 0.9rem;
        }
        
        .back-to-login {
            text-align: center;
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            border-top: 1px solid #e5e7eb;
        }
        
        .back-to-login a {
            color: var(--primary-color);
            text-decoration: none;
            font-size: 0.9rem;
        }
        
        .back-to-login a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="forgot-password-container">
        <div class="forgot-password-card">
            <div class="forgot-password-header">
                <div class="forgot-password-logo">CS</div>
                <h1>Forgot Password?</h1>
                <p>Enter your email address and we'll send you instructions to reset your password.</p>
            </div>
            
            <?php if ($success): ?>
                <div class="alert alert-success">
                    Password reset instructions have been sent to your email address. Please check your inbox.
                    <?php if (isset($resetLinkDisplay)): ?>
                        <br><br>
                        <strong>Testing Mode - Reset Link:</strong><br>
                        <a href="<?php echo $resetLinkDisplay; ?>" style="word-break: break-all; font-size: 0.8rem;">
                            <?php echo $resetLinkDisplay; ?>
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if (!$success): ?>
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="email" class="form-label">Email Address</label>
                        <input 
                            type="email" 
                            id="email" 
                            name="email" 
                            class="form-control" 
                            placeholder="Enter your email address"
                            required
                            autocomplete="email"
                        >
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-block btn-lg">
                        Send Reset Link
                    </button>
                </form>
            <?php endif; ?>
            
            <div class="back-to-login">
                <a href="<?php echo BASE_URL; ?>public/index.php">
                    <i class="fas fa-arrow-left"></i> Back to Login
                </a>
            </div>
        </div>
    </div>
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
</body>
</html>
