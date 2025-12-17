<?php
/**
 * Edit Teacher
 * CoreSkool School Management System
 */

require_once dirname(dirname(dirname(__DIR__))) . '/config/config.php';

requireAuth();
requireRole('admin');

$pageTitle = 'Edit Teacher';
$db = Database::getInstance();

$teacherId = (int)($_GET['id'] ?? 0);

if ($teacherId <= 0) {
    setFlash('danger', 'Invalid teacher ID');
    redirect('admin/teachers/index.php');
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
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
        'status' => sanitize($_POST['status'] ?? 'active')
    ];
    
    // Validate required fields
    if (empty($data['first_name']) || empty($data['last_name']) || empty($data['gender']) || 
        empty($data['email']) || empty($data['phone'])) {
        setFlash('danger', 'Please fill in all required fields');
    } else {
        try {
            // Update teacher information
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
                    WHERE id = ? AND role = 'teacher' AND school_id = ?";
            
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
                $teacherId,
                $_SESSION['school_id']
            ]);
            
            // Update password if provided
            if (!empty($_POST['password'])) {
                $hashedPassword = hashPassword($_POST['password']);
                $db->query("UPDATE users SET password = ? WHERE id = ?", [$hashedPassword, $teacherId]);
            }
            
            logActivity($_SESSION['user_id'], 'edit_teacher', "Updated teacher: {$data['first_name']} {$data['last_name']}", $_SERVER['REMOTE_ADDR']);
            setFlash('success', 'Teacher updated successfully');
            redirect("admin/teachers/view.php?id={$teacherId}");
        } catch (Exception $e) {
            error_log("Edit Teacher Error: " . $e->getMessage());
            setFlash('danger', 'Failed to update teacher');
        }
    }
}

// Get teacher details
$sql = "SELECT u.* FROM users u WHERE u.id = ? AND u.role = 'teacher' AND u.school_id = ?";
$stmt = $db->query($sql, [$teacherId, $_SESSION['school_id']]);
$teacher = $stmt->fetch();

if (!$teacher) {
    setFlash('danger', 'Teacher not found');
    redirect('admin/teachers/index.php');
}

include APP_PATH . '/views/shared/header.php';
?>

<div class="main-content">
    <?php include APP_PATH . '/views/shared/sidebar.php'; ?>
    <?php include APP_PATH . '/views/shared/topbar.php'; ?>
    
    <div class="content-area">
        <div class="page-header">
            <h2>Edit Teacher</h2>
            <a href="<?php echo BASE_URL; ?>public/admin/teachers/view.php?id=<?php echo $teacherId; ?>" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Details
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
                            
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="first_name">First Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="first_name" name="first_name" 
                                               value="<?php echo $teacher['first_name']; ?>" required>
                                    </div>
                                </div>
                                
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="last_name">Last Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="last_name" name="last_name" 
                                               value="<?php echo $teacher['last_name']; ?>" required>
                                    </div>
                                </div>
                                
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="other_names">Other Names</label>
                                        <input type="text" class="form-control" id="other_names" name="other_names" 
                                               value="<?php echo $teacher['other_names']; ?>">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="gender">Gender <span class="text-danger">*</span></label>
                                        <select class="form-control" id="gender" name="gender" required>
                                            <option value="">Select Gender</option>
                                            <option value="male" <?php echo $teacher['gender'] === 'male' ? 'selected' : ''; ?>>Male</option>
                                            <option value="female" <?php echo $teacher['gender'] === 'female' ? 'selected' : ''; ?>>Female</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="date_of_birth">Date of Birth</label>
                                        <input type="date" class="form-control" id="date_of_birth" name="date_of_birth" 
                                               value="<?php echo $teacher['date_of_birth']; ?>">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="email">Email Address <span class="text-danger">*</span></label>
                                        <input type="email" class="form-control" id="email" name="email" 
                                               value="<?php echo $teacher['email']; ?>" required>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="phone">Phone Number <span class="text-danger">*</span></label>
                                        <input type="tel" class="form-control" id="phone" name="phone" 
                                               value="<?php echo $teacher['phone']; ?>" required>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="address">Address</label>
                                <textarea class="form-control" id="address" name="address" rows="2"><?php echo $teacher['address']; ?></textarea>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="city">City</label>
                                        <input type="text" class="form-control" id="city" name="city" 
                                               value="<?php echo $teacher['city']; ?>">
                                    </div>
                                </div>
                                
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="state">State</label>
                                        <input type="text" class="form-control" id="state" name="state" 
                                               value="<?php echo $teacher['state']; ?>">
                                    </div>
                                </div>
                                
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="country">Country</label>
                                        <input type="text" class="form-control" id="country" name="country" 
                                               value="<?php echo $teacher['country']; ?>">
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
                                            <option value="active" <?php echo $teacher['status'] === 'active' ? 'selected' : ''; ?>>Active</option>
                                            <option value="inactive" <?php echo $teacher['status'] === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                                            <option value="suspended" <?php echo $teacher['status'] === 'suspended' ? 'selected' : ''; ?>>Suspended</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group" style="margin-top: 1.5rem;">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Update Teacher
                                </button>
                                <a href="<?php echo BASE_URL; ?>public/admin/teachers/view.php?id=<?php echo $teacherId; ?>" 
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
