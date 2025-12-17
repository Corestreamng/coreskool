<?php
/**
 * Edit Student
 * CoreSkool School Management System
 */

require_once dirname(dirname(dirname(__DIR__))) . '/config/config.php';

requireAuth();
requireRole('admin');

$pageTitle = 'Edit Student';
$db = Database::getInstance();

$studentId = (int)($_GET['id'] ?? 0);

if ($studentId <= 0) {
    setFlash('danger', 'Invalid student ID');
    redirect('admin/students/index.php');
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
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
        'status' => sanitize($_POST['status'] ?? 'active')
    ];
    
    $classId = (int)($_POST['class_id'] ?? 0);
    
    // Validate required fields
    if (empty($data['first_name']) || empty($data['last_name']) || empty($data['gender'])) {
        setFlash('danger', 'Please fill in all required fields');
    } else {
        try {
            // Update student information
            $sql = "UPDATE users SET 
                    first_name = ?, 
                    last_name = ?, 
                    other_names = ?, 
                    gender = ?, 
                    date_of_birth = ?, 
                    email = ?, 
                    phone = ?, 
                    address = ?, 
                    city = ?, 
                    state = ?, 
                    country = ?,
                    status = ?,
                    updated_at = CURRENT_TIMESTAMP
                    WHERE id = ? AND role = 'student' AND school_id = ?";
            
            $db->query($sql, [
                $data['first_name'],
                $data['last_name'],
                $data['other_names'],
                $data['gender'],
                $data['date_of_birth'],
                $data['email'],
                $data['phone'],
                $data['address'],
                $data['city'],
                $data['state'],
                $data['country'],
                $data['status'],
                $studentId,
                $_SESSION['school_id']
            ]);
            
            // Update class assignment if changed
            if ($classId > 0) {
                // First, deactivate current class assignment
                $db->query("UPDATE student_classes SET status = 'inactive' WHERE student_id = ? AND status = 'active'", [$studentId]);
                
                // Get current academic year
                $currentYear = $db->query("SELECT id FROM academic_years WHERE school_id = ? AND is_current = 1 LIMIT 1", [$_SESSION['school_id']])->fetch();
                
                if ($currentYear) {
                    // Check if assignment already exists
                    $existingAssignment = $db->query(
                        "SELECT id FROM student_classes WHERE student_id = ? AND class_id = ? AND academic_year_id = ?",
                        [$studentId, $classId, $currentYear['id']]
                    )->fetch();
                    
                    if ($existingAssignment) {
                        // Reactivate existing assignment
                        $db->query("UPDATE student_classes SET status = 'active', assigned_date = CURDATE() WHERE id = ?", [$existingAssignment['id']]);
                    } else {
                        // Create new assignment
                        $db->query(
                            "INSERT INTO student_classes (student_id, class_id, academic_year_id, status, assigned_date) VALUES (?, ?, ?, 'active', CURDATE())",
                            [$studentId, $classId, $currentYear['id']]
                        );
                    }
                }
            }
            
            // Update password if provided
            if (!empty($_POST['password'])) {
                $hashedPassword = hashPassword($_POST['password']);
                $db->query("UPDATE users SET password = ? WHERE id = ?", [$hashedPassword, $studentId]);
            }
            
            logActivity($_SESSION['user_id'], 'edit_student', "Updated student: {$data['first_name']} {$data['last_name']}", $_SERVER['REMOTE_ADDR']);
            setFlash('success', 'Student updated successfully');
            redirect("admin/students/view.php?id={$studentId}");
        } catch (Exception $e) {
            error_log("Edit Student Error: " . $e->getMessage());
            setFlash('danger', 'Failed to update student');
        }
    }
}

// Get student details with class information
$sql = "SELECT u.*, 
        sc.class_id as current_class_id
        FROM users u
        LEFT JOIN student_classes sc ON u.id = sc.student_id AND sc.status = 'active'
        WHERE u.id = ? AND u.role = 'student' AND u.school_id = ?";

$stmt = $db->query($sql, [$studentId, $_SESSION['school_id']]);
$student = $stmt->fetch();

if (!$student) {
    setFlash('danger', 'Student not found');
    redirect('admin/students/index.php');
}

// Get classes
$classesStmt = $db->query("SELECT id, name FROM classes WHERE school_id = ? AND status = 'active' ORDER BY name", [$_SESSION['school_id']]);
$classes = $classesStmt->fetchAll();

include APP_PATH . '/views/shared/header.php';
?>

<div class="main-content">
    <?php include APP_PATH . '/views/shared/sidebar.php'; ?>
    <?php include APP_PATH . '/views/shared/topbar.php'; ?>
    
    <div class="content-area">
        <div class="page-header">
            <h2>Edit Student</h2>
            <a href="<?php echo BASE_URL; ?>public/admin/students/view.php?id=<?php echo $studentId; ?>" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Details
            </a>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <strong>Student Information</strong>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="">
                            <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="first_name">First Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="first_name" name="first_name" 
                                               value="<?php echo $student['first_name']; ?>" required>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="last_name">Last Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="last_name" name="last_name" 
                                               value="<?php echo $student['last_name']; ?>" required>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="other_names">Other Names</label>
                                        <input type="text" class="form-control" id="other_names" name="other_names" 
                                               value="<?php echo $student['other_names']; ?>">
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="matric_number">Matric Number</label>
                                        <input type="text" class="form-control" id="matric_number" 
                                               value="<?php echo $student['matric_number']; ?>" disabled>
                                        <small class="form-text text-muted">Matric number cannot be changed</small>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="gender">Gender <span class="text-danger">*</span></label>
                                        <select class="form-control" id="gender" name="gender" required>
                                            <option value="">Select Gender</option>
                                            <option value="male" <?php echo $student['gender'] === 'male' ? 'selected' : ''; ?>>Male</option>
                                            <option value="female" <?php echo $student['gender'] === 'female' ? 'selected' : ''; ?>>Female</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="date_of_birth">Date of Birth <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control" id="date_of_birth" name="date_of_birth" 
                                               value="<?php echo $student['date_of_birth']; ?>" required>
                                    </div>
                                </div>
                                
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="class_id">Class</label>
                                        <select class="form-control" id="class_id" name="class_id">
                                            <option value="">Select Class</option>
                                            <?php foreach ($classes as $class): ?>
                                                <option value="<?php echo $class['id']; ?>" 
                                                    <?php echo $student['current_class_id'] == $class['id'] ? 'selected' : ''; ?>>
                                                    <?php echo $class['name']; ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="email">Email</label>
                                        <input type="email" class="form-control" id="email" name="email" 
                                               value="<?php echo $student['email']; ?>">
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="phone">Phone Number</label>
                                        <input type="tel" class="form-control" id="phone" name="phone" 
                                               value="<?php echo $student['phone']; ?>">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="address">Address</label>
                                <textarea class="form-control" id="address" name="address" rows="2"><?php echo $student['address']; ?></textarea>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="city">City</label>
                                        <input type="text" class="form-control" id="city" name="city" 
                                               value="<?php echo $student['city']; ?>">
                                    </div>
                                </div>
                                
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="state">State</label>
                                        <input type="text" class="form-control" id="state" name="state" 
                                               value="<?php echo $student['state']; ?>">
                                    </div>
                                </div>
                                
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="country">Country</label>
                                        <input type="text" class="form-control" id="country" name="country" 
                                               value="<?php echo $student['country']; ?>">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="password">New Password (leave blank to keep current)</label>
                                        <input type="password" class="form-control" id="password" name="password" 
                                               minlength="6">
                                        <small class="form-text text-muted">Minimum 6 characters</small>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="status">Status</label>
                                        <select class="form-control" id="status" name="status">
                                            <option value="active" <?php echo $student['status'] === 'active' ? 'selected' : ''; ?>>Active</option>
                                            <option value="inactive" <?php echo $student['status'] === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                                            <option value="suspended" <?php echo $student['status'] === 'suspended' ? 'selected' : ''; ?>>Suspended</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group" style="margin-top: 1.5rem;">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Update Student
                                </button>
                                <a href="<?php echo BASE_URL; ?>public/admin/students/view.php?id=<?php echo $studentId; ?>" 
                                   class="btn btn-secondary">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include APP_PATH . '/views/shared/footer.php'; ?>
