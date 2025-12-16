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
        'date_of_birth' => sanitize($_POST['date_of_birth']),
        'email' => sanitize($_POST['email'] ?? ''),
        'phone' => sanitize($_POST['phone'] ?? ''),
        'address' => sanitize($_POST['address'] ?? ''),
        'city' => sanitize($_POST['city'] ?? ''),
        'state' => sanitize($_POST['state'] ?? ''),
        'country' => sanitize($_POST['country'] ?? 'Nigeria'),
        'password' => $_POST['password']
    ];
    
    // Validate required fields
    if (empty($data['first_name']) || empty($data['last_name']) || empty($data['gender']) || empty($data['password'])) {
        setFlash('danger', 'Please fill in all required fields');
    } else {
        // Register teacher
        $auth = new AuthController();
        $result = $auth->register($data);
        
        if ($result['success']) {
            logActivity($_SESSION['user_id'], 'add_teacher', "Added new teacher: {$data['first_name']} {$data['last_name']}", $_SERVER['REMOTE_ADDR']);
            setFlash('success', "Teacher added successfully! Staff ID: {$result['matric_number']}");
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
        <?php 
        $flash = getFlash();
        if ($flash): 
        ?>
            <div class="alert alert-<?php echo $flash['type']; ?>">
                <?php echo $flash['message']; ?>
            </div>
        <?php endif; ?>
        
        <div class="card">
            <div class="card-header">
                <strong>Add New Teacher</strong>
            </div>
            
            <div class="card-body">
                <form method="POST" action="" enctype="multipart/form-data" data-validate>
                    <div class="row">
                        <!-- Personal Information -->
                        <div class="col-md-12">
                            <h4 style="margin-bottom: 1rem; padding-bottom: 0.5rem; border-bottom: 2px solid var(--primary-color);">
                                Personal Information
                            </h4>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="form-label">First Name <span style="color: red;">*</span></label>
                                <input type="text" name="first_name" class="form-control" required>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="form-label">Last Name <span style="color: red;">*</span></label>
                                <input type="text" name="last_name" class="form-control" required>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="form-label">Other Names</label>
                                <input type="text" name="other_names" class="form-control">
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">Gender <span style="color: red;">*</span></label>
                                <select name="gender" class="form-control" required>
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
                        
                        <!-- Contact Information -->
                        <div class="col-md-12">
                            <h4 style="margin: 1.5rem 0 1rem; padding-bottom: 0.5rem; border-bottom: 2px solid var(--primary-color);">
                                Contact Information
                            </h4>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">Email Address</label>
                                <input type="email" name="email" class="form-control">
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">Phone Number</label>
                                <input type="tel" name="phone" class="form-control">
                            </div>
                        </div>
                        
                        <div class="col-md-12">
                            <div class="form-group">
                                <label class="form-label">Address</label>
                                <textarea name="address" class="form-control" rows="2"></textarea>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="form-label">City</label>
                                <input type="text" name="city" class="form-control">
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="form-label">State</label>
                                <input type="text" name="state" class="form-control">
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="form-label">Country</label>
                                <input type="text" name="country" class="form-control" value="Nigeria">
                            </div>
                        </div>
                        
                        <!-- Account Information -->
                        <div class="col-md-12">
                            <h4 style="margin: 1.5rem 0 1rem; padding-bottom: 0.5rem; border-bottom: 2px solid var(--primary-color);">
                                Account Information
                            </h4>
                            <p style="color: #6b7280; font-size: 0.875rem; margin-bottom: 1rem;">
                                <i class="fas fa-info-circle"></i> Staff ID will be automatically generated
                            </p>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">Password <span style="color: red;">*</span></label>
                                <input type="password" name="password" class="form-control" required minlength="6">
                                <small class="text-muted">Minimum 6 characters</small>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">Confirm Password <span style="color: red;">*</span></label>
                                <input type="password" name="confirm_password" class="form-control" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group" style="margin-top: 2rem; padding-top: 1rem; border-top: 1px solid var(--border-color);">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-save"></i> Save Teacher
                        </button>
                        <a href="<?php echo BASE_URL; ?>public/admin/teachers/index.php" class="btn btn-secondary btn-lg">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include APP_PATH . '/views/shared/footer.php'; ?>
