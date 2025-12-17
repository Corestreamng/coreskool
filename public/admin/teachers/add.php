<?php
/**
 * Add New Teacher
 * CoreSkool School Management System
 */

require_once dirname(dirname(dirname(__DIR__))) . '/config/config.php';
require_once APP_PATH . '/controllers/AuthController.php';

requireAuth();
requireRole('admin');

$pageTitle = 'Add New Teacher';
$db = Database::getInstance();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'school_id' => $_SESSION['school_id'],
        'role' => 'teacher',
        'first_name' => sanitize($_POST['first_name']),
        'last_name' => sanitize($_POST['last_name']),
        'other_names' => sanitize($_POST['other_names'] ?? ''),
        'gender' => sanitize($_POST['gender']),
        'date_of_birth' => sanitize($_POST['date_of_birth'] ?? ''),
        'email' => sanitize($_POST['email']),
        'phone' => sanitize($_POST['phone']),
        'address' => sanitize($_POST['address'] ?? ''),
        'city' => sanitize($_POST['city'] ?? ''),
        'state' => sanitize($_POST['state'] ?? ''),
        'country' => sanitize($_POST['country'] ?? 'Nigeria'),
        'password' => $_POST['password']
    ];
    
    // Validate required fields
    if (empty($data['first_name']) || empty($data['last_name']) || empty($data['gender']) || 
        empty($data['email']) || empty($data['phone']) || empty($data['password'])) {
        setFlash('danger', 'Please fill in all required fields');
    } else {
        // Register teacher
        $auth = new AuthController();
        $result = $auth->register($data);
        
        if ($result['success']) {
            logActivity($_SESSION['user_id'], 'add_teacher', "Added new teacher: {$data['first_name']} {$data['last_name']}", $_SERVER['REMOTE_ADDR']);
            setFlash('success', "Teacher added successfully!");
            redirect('admin/teachers/index.php');
        } else {
            setFlash('danger', $result['message']);
        }
    }
}

include APP_PATH . '/views/shared/header.php';
?>

<div class="main-content">
    <?php include APP_PATH . '/views/shared/sidebar.php'; ?>
    <?php include APP_PATH . '/views/shared/topbar.php'; ?>
    
    <div class="content-area">
        <div class="page-header">
            <h2>Add New Teacher</h2>
            <a href="<?php echo BASE_URL; ?>public/admin/teachers/index.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to List
            </a>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <strong>Teacher Information</strong>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="">
                            <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                            
                            <h4 style="margin-bottom: 1rem; color: var(--primary-color);">Personal Information</h4>
                            
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="first_name">First Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="first_name" name="first_name" required>
                                    </div>
                                </div>
                                
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="last_name">Last Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="last_name" name="last_name" required>
                                    </div>
                                </div>
                                
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="other_names">Other Names</label>
                                        <input type="text" class="form-control" id="other_names" name="other_names">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="gender">Gender <span class="text-danger">*</span></label>
                                        <select class="form-control" id="gender" name="gender" required>
                                            <option value="">Select Gender</option>
                                            <option value="male">Male</option>
                                            <option value="female">Female</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="date_of_birth">Date of Birth</label>
                                        <input type="date" class="form-control" id="date_of_birth" name="date_of_birth">
                                    </div>
                                </div>
                            </div>
                            
                            <hr style="margin: 2rem 0;">
                            <h4 style="margin-bottom: 1rem; color: var(--primary-color);">Contact Information</h4>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="email">Email Address <span class="text-danger">*</span></label>
                                        <input type="email" class="form-control" id="email" name="email" required>
                                        <small class="form-text text-muted">Used for login</small>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="phone">Phone Number <span class="text-danger">*</span></label>
                                        <input type="tel" class="form-control" id="phone" name="phone" required>
                                        <small class="form-text text-muted">Used for login</small>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="address">Address</label>
                                <textarea class="form-control" id="address" name="address" rows="2"></textarea>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="city">City</label>
                                        <input type="text" class="form-control" id="city" name="city">
                                    </div>
                                </div>
                                
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="state">State</label>
                                        <input type="text" class="form-control" id="state" name="state" value="Lagos">
                                    </div>
                                </div>
                                
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="country">Country</label>
                                        <input type="text" class="form-control" id="country" name="country" value="Nigeria">
                                    </div>
                                </div>
                            </div>
                            
                            <hr style="margin: 2rem 0;">
                            <h4 style="margin-bottom: 1rem; color: var(--primary-color);">Account Information</h4>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="password">Password <span class="text-danger">*</span></label>
                                        <input type="password" class="form-control" id="password" name="password" 
                                               minlength="6" required>
                                        <small class="form-text text-muted">Minimum 6 characters</small>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="confirm_password">Confirm Password <span class="text-danger">*</span></label>
                                        <input type="password" class="form-control" id="confirm_password" 
                                               name="confirm_password" minlength="6" required>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i>
                                <strong>Note:</strong> Teacher can login using their email or phone number.
                            </div>
                            
                            <div class="form-group" style="margin-top: 2rem;">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-user-plus"></i> Add Teacher
                                </button>
                                <a href="<?php echo BASE_URL; ?>public/admin/teachers/index.php" 
                                   class="btn btn-secondary btn-lg">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Password confirmation validation
document.querySelector('form').addEventListener('submit', function(e) {
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirm_password').value;
    
    if (password !== confirmPassword) {
        e.preventDefault();
        alert('Passwords do not match!');
        return false;
    }
});
</script>

<?php include APP_PATH . '/views/shared/footer.php'; ?>
