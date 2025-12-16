<?php
/**
 * Public Index - Login Page
 * CoreSkool School Management System
 */

require_once dirname(__DIR__) . '/config/config.php';
require_once APP_PATH . '/controllers/AuthController.php';

// If already logged in, redirect to dashboard
if (isLoggedIn()) {
    $role = $_SESSION['user_role'];
    redirect($role . '/dashboard.php');
}

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identifier = sanitize($_POST['identifier'] ?? '');
    $password = $_POST['password'] ?? '';
    $loginType = sanitize($_POST['login_type'] ?? 'email');
    
    if (empty($identifier) || empty($password)) {
        $error = 'Please fill in all fields';
    } else {
        $auth = new AuthController();
        $result = $auth->login($identifier, $password, $loginType);
        
        if ($result['success']) {
            header('Location: ' . BASE_URL . $result['redirect']);
            exit;
        } else {
            $error = $result['message'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/assets/css/style.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .login-container {
            width: 100%;
            max-width: 450px;
            padding: 20px;
        }
        
        .login-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            padding: 3rem 2.5rem;
        }
        
        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .login-logo {
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
        
        .login-header h1 {
            font-size: 1.75rem;
            margin-bottom: 0.5rem;
        }
        
        .login-header p {
            color: #6b7280;
            font-size: 0.9rem;
        }
        
        .login-type-selector {
            display: flex;
            gap: 0.5rem;
            margin-bottom: 1.5rem;
            background: #f3f4f6;
            padding: 0.5rem;
            border-radius: 8px;
        }
        
        .login-type-btn {
            flex: 1;
            padding: 0.625rem;
            background: transparent;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.875rem;
            font-weight: 500;
            transition: all 0.3s;
        }
        
        .login-type-btn.active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .login-footer {
            text-align: center;
            margin-top: 2rem;
            color: white;
        }
        
        .login-footer a {
            color: white;
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <div class="login-logo">CS</div>
                <h1><?php echo SITE_NAME; ?></h1>
                <p>School Management System</p>
            </div>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <form method="POST" action="" id="loginForm">
                <div class="login-type-selector">
                    <button type="button" class="login-type-btn active" onclick="selectLoginType('email')">
                        Email/Phone
                    </button>
                    <button type="button" class="login-type-btn" onclick="selectLoginType('matric')">
                        Matric Number
                    </button>
                </div>
                
                <input type="hidden" name="login_type" id="login_type" value="email">
                
                <div class="form-group">
                    <label for="identifier" class="form-label" id="identifierLabel">
                        Email Address or Phone Number
                    </label>
                    <input 
                        type="text" 
                        id="identifier" 
                        name="identifier" 
                        class="form-control" 
                        placeholder="Enter your email or phone"
                        required
                        autocomplete="username"
                    >
                </div>
                
                <div class="form-group">
                    <label for="password" class="form-label">Password</label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        class="form-control" 
                        placeholder="Enter your password"
                        required
                        autocomplete="current-password"
                    >
                </div>
                
                <div class="form-group" style="text-align: right;">
                    <a href="auth/forgot-password.php" style="font-size: 0.875rem;">Forgot Password?</a>
                </div>
                
                <button type="submit" class="btn btn-primary btn-block btn-lg">
                    Login
                </button>
            </form>
            
            <div style="margin-top: 1.5rem; padding-top: 1.5rem; border-top: 1px solid #e5e7eb; text-align: center;">
                <p style="font-size: 0.875rem; color: #6b7280; margin-bottom: 0.5rem;">
                    Login as:
                </p>
                <div style="display: flex; gap: 0.5rem; justify-content: center; flex-wrap: wrap;">
                    <span style="font-size: 0.75rem; padding: 0.25rem 0.75rem; background: #f3f4f6; border-radius: 999px;">
                        Admin/Parent: Email/Phone
                    </span>
                    <span style="font-size: 0.75rem; padding: 0.25rem 0.75rem; background: #f3f4f6; border-radius: 999px;">
                        Teacher: Phone
                    </span>
                    <span style="font-size: 0.75rem; padding: 0.25rem 0.75rem; background: #f3f4f6; border-radius: 999px;">
                        Student: Matric Number
                    </span>
                </div>
            </div>
        </div>
        
        <div class="login-footer">
            <p>&copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. All rights reserved.</p>
            <p><small>Version 1.0 | <a href="<?php echo BASE_URL; ?>install.php">Install Database</a></small></p>
        </div>
    </div>
    
    <script>
        function selectLoginType(type) {
            // Update active button
            document.querySelectorAll('.login-type-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            event.target.classList.add('active');
            
            // Update hidden input
            document.getElementById('login_type').value = type;
            
            // Update label and placeholder
            const label = document.getElementById('identifierLabel');
            const input = document.getElementById('identifier');
            
            if (type === 'matric') {
                label.textContent = 'Matric Number';
                input.placeholder = 'Enter your matric number';
                input.type = 'text';
            } else {
                label.textContent = 'Email Address or Phone Number';
                input.placeholder = 'Enter your email or phone';
                input.type = 'text';
            }
        }
    </script>
</body>
</html>
