<?php
/**
 * Add Teacher
 * CoreSkool School Management System
 */

require_once dirname(dirname(__DIR__)) . '/config/config.php';

requireAuth();
requireRole('admin');

$pageTitle = 'Add New Teacher';
$error = null;
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName = sanitize($_POST['first_name'] ?? '');
    $lastName = sanitize($_POST['last_name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $phone = sanitize($_POST['phone'] ?? '');
    $gender = sanitize($_POST['gender'] ?? '');
    $dateOfBirth = sanitize($_POST['date_of_birth'] ?? '');
    $address = sanitize($_POST['address'] ?? '');
    
    if (empty($firstName) || empty($lastName) || empty($phone)) {
        $error = 'Please fill in all required fields';
    } else {
        $db = Database::getInstance();
        
        // Check if phone already exists
        $checkQuery = $db->query("SELECT id FROM users WHERE phone = ?", [$phone]);
        if ($checkQuery->fetch()) {
            $error = 'Phone number already exists';
        } else {
            // Generate default password
            $defaultPassword = 'teacher' . random_int(1000, 9999);
            $hashedPassword = hashPassword($defaultPassword);
            
            // Insert teacher
            $db->query(
                "INSERT INTO users (first_name, last_name, email, phone, password, role, gender, date_of_birth, address, school_id, status, created_at) 
                 VALUES (?, ?, ?, ?, ?, 'teacher', ?, ?, ?, ?, 'active', NOW())",
                [$firstName, $lastName, $email, $phone, $hashedPassword, $gender, $dateOfBirth, $address, $_SESSION['school_id']]
            );
            
            $success = true;
            $teacherId = $db->lastInsertId();
            
            // Store password for display
            $_SESSION['temp_password'] = $defaultPassword;
        }
    }
}

include APP_PATH . '/views/shared/header.php';
?>

<div class="main-content">
    <?php include APP_PATH . '/views/shared/sidebar.php'; ?>
    <?php include APP_PATH . '/views/shared/topbar.php'; ?>
    
    <div class="content-area">
        <div style="margin-bottom: 2rem;">
            <h2 style="margin-bottom: 0.5rem;">
                <i class="fas fa-user-plus"></i> Add New Teacher
            </h2>
            <p style="color: #6b7280;">Create a new teacher account</p>
        </div>
        
        <?php if ($success): ?>
            <div class="alert alert-success">
                Teacher added successfully!
                <br><strong>Default Password:</strong> <?php echo $_SESSION['temp_password']; ?>
                <br><small>Please share this password with the teacher. They can change it after logging in.</small>
                <br><br>
                <a href="<?php echo BASE_URL; ?>public/admin/teachers/index.php" class="btn btn-primary">
                    View All Teachers
                </a>
                <a href="<?php echo BASE_URL; ?>public/admin/teachers/add.php" class="btn btn-secondary">
                    Add Another Teacher
                </a>
            </div>
        <?php else: ?>
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <div class="card">
                <div class="card-header">
                    <strong>Teacher Information</strong>
                </div>
                <div class="card-body">
                    <form method="POST" action="">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">First Name *</label>
                                    <input type="text" name="first_name" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Last Name *</label>
                                    <input type="text" name="last_name" class="form-control" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Email</label>
                                    <input type="email" name="email" class="form-control">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Phone Number *</label>
                                    <input type="tel" name="phone" class="form-control" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Gender</label>
                                    <select name="gender" class="form-control">
                                        <option value="">Select Gender</option>
                                        <option value="male">Male</option>
                                        <option value="female">Female</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Date of Birth</label>
                                    <input type="date" name="date_of_birth" class="form-control">
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Address</label>
                            <textarea name="address" class="form-control" rows="3"></textarea>
                        </div>
                        
                        <div style="margin-top: 2rem;">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Add Teacher
                            </button>
                            <a href="<?php echo BASE_URL; ?>public/admin/teachers/index.php" class="btn btn-secondary">
                                Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php 
unset($_SESSION['temp_password']);
include APP_PATH . '/views/shared/footer.php'; 
?>
