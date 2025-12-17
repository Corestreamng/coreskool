<?php
/**
 * Add New Parent/Guardian
 * CoreSkool School Management System
 */

require_once dirname(dirname(dirname(__DIR__))) . '/config/config.php';
require_once APP_PATH . '/controllers/AuthController.php';

requireAuth();
requireRole('admin');

$pageTitle = 'Add New Parent/Guardian';
$db = Database::getInstance();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'school_id' => $_SESSION['school_id'],
        'role' => 'parent',
        'first_name' => sanitize($_POST['first_name']),
        'last_name' => sanitize($_POST['last_name']),
        'other_names' => sanitize($_POST['other_names'] ?? ''),
        'gender' => sanitize($_POST['gender']),
        'email' => sanitize($_POST['email']),
        'phone' => sanitize($_POST['phone']),
        'address' => sanitize($_POST['address'] ?? ''),
        'city' => sanitize($_POST['city'] ?? ''),
        'state' => sanitize($_POST['state'] ?? ''),
        'country' => sanitize($_POST['country'] ?? 'Nigeria'),
        'password' => $_POST['password']
    ];
    
    $students = $_POST['students'] ?? [];
    $relationship = sanitize($_POST['relationship'] ?? 'guardian');
    
    // Validate required fields
    if (empty($data['first_name']) || empty($data['last_name']) || empty($data['gender']) || 
        empty($data['email']) || empty($data['phone']) || empty($data['password'])) {
        setFlash('danger', 'Please fill in all required fields');
    } else {
        // Register parent
        $auth = new AuthController();
        $result = $auth->register($data);
        
        if ($result['success']) {
            $parentId = $result['user_id'];
            
            // Link to students
            if (!empty($students)) {
                foreach ($students as $studentId) {
                    try {
                        $db->query(
                            "INSERT INTO parent_student (parent_id, student_id, relationship, is_primary) VALUES (?, ?, ?, 0)",
                            [$parentId, $studentId, $relationship]
                        );
                    } catch (Exception $e) {
                        error_log("Failed to link student: " . $e->getMessage());
                    }
                }
            }
            
            logActivity($_SESSION['user_id'], 'add_parent', "Added new parent: {$data['first_name']} {$data['last_name']}", $_SERVER['REMOTE_ADDR']);
            setFlash('success', "Parent added successfully!");
            redirect('admin/parents/index.php');
        } else {
            setFlash('danger', $result['message']);
        }
    }
}

// Get students for linking
$studentsStmt = $db->query(
    "SELECT id, first_name, last_name, matric_number FROM users WHERE role = 'student' AND school_id = ? AND status = 'active' ORDER BY first_name, last_name",
    [$_SESSION['school_id']]
);
$students = $studentsStmt->fetchAll();

include APP_PATH . '/views/shared/header.php';
?>

<div class="main-content">
    <?php include APP_PATH . '/views/shared/sidebar.php'; ?>
    <?php include APP_PATH . '/views/shared/topbar.php'; ?>
    
    <div class="content-area">
        <div class="page-header">
            <h2>Add New Parent/Guardian</h2>
            <a href="<?php echo BASE_URL; ?>public/admin/parents/index.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to List
            </a>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <strong>Parent/Guardian Information</strong>
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
                                        <label for="relationship">Relationship to Student <span class="text-danger">*</span></label>
                                        <select class="form-control" id="relationship" name="relationship" required>
                                            <option value="father">Father</option>
                                            <option value="mother">Mother</option>
                                            <option value="guardian" selected>Guardian</option>
                                            <option value="other">Other</option>
                                        </select>
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
                            <h4 style="margin-bottom: 1rem; color: var(--primary-color);">Link to Students (Wards)</h4>
                            
                            <div class="form-group">
                                <label>Select Students</label>
                                <div style="max-height: 200px; overflow-y: auto; border: 1px solid var(--border-color); padding: 1rem; border-radius: 5px;">
                                    <?php if (empty($students)): ?>
                                        <p class="text-muted">No students found. Please add students first.</p>
                                    <?php else: ?>
                                        <?php foreach ($students as $student): ?>
                                            <div class="form-check" style="margin-bottom: 0.5rem;">
                                                <input class="form-check-input" type="checkbox" name="students[]" 
                                                       value="<?php echo $student['id']; ?>" 
                                                       id="student_<?php echo $student['id']; ?>">
                                                <label class="form-check-label" for="student_<?php echo $student['id']; ?>">
                                                    <?php echo $student['first_name'] . ' ' . $student['last_name']; ?>
                                                    <small class="text-muted">(<?php echo $student['matric_number']; ?>)</small>
                                                </label>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                                <small class="form-text text-muted">You can link students now or later from the parent's profile.</small>
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
                                <strong>Note:</strong> Parent/Guardian can login using their email or phone number.
                            </div>
                            
                            <div class="form-group" style="margin-top: 2rem;">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-user-plus"></i> Add Parent/Guardian
                                </button>
                                <a href="<?php echo BASE_URL; ?>public/admin/parents/index.php" 
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
