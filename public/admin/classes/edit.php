<?php
/**
 * Edit Class
 * CoreSkool School Management System
 */

require_once dirname(dirname(dirname(__DIR__))) . '/config/config.php';

requireAuth();
requireRole('admin');

$pageTitle = 'Edit Class';
$db = Database::getInstance();

$classId = (int)($_GET['id'] ?? 0);

if (!$classId) {
    setFlash('danger', 'Invalid class ID');
    redirect('admin/classes/index.php');
}

// Get class details
$stmt = $db->query("SELECT * FROM classes WHERE id = ? AND school_id = ?", [$classId, $_SESSION['school_id']]);
$class = $stmt->fetch();

if (!$class) {
    setFlash('danger', 'Class not found');
    redirect('admin/classes/index.php');
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'name' => sanitize($_POST['name']),
        'class_level' => (int)($_POST['class_level'] ?? 0),
        'section' => sanitize($_POST['section'] ?? ''),
        'capacity' => (int)($_POST['capacity'] ?? 40),
        'class_teacher_id' => (int)($_POST['class_teacher_id'] ?? 0) ?: null,
        'room_number' => sanitize($_POST['room_number'] ?? ''),
        'status' => sanitize($_POST['status'])
    ];
    
    // Validate required fields
    if (empty($data['name'])) {
        setFlash('danger', 'Please fill in all required fields');
    } else {
        try {
            $sql = "UPDATE classes SET 
                    name = ?, 
                    class_level = ?, 
                    section = ?, 
                    capacity = ?, 
                    class_teacher_id = ?, 
                    room_number = ?, 
                    status = ?
                    WHERE id = ? AND school_id = ?";
            $db->query($sql, [
                $data['name'],
                $data['class_level'] ?: null,
                $data['section'],
                $data['capacity'],
                $data['class_teacher_id'],
                $data['room_number'],
                $data['status'],
                $classId,
                $_SESSION['school_id']
            ]);
            
            logActivity($_SESSION['user_id'], 'edit_class', "Updated class: {$data['name']}", $_SERVER['REMOTE_ADDR']);
            setFlash('success', 'Class updated successfully!');
            redirect('admin/classes/index.php');
        } catch (Exception $e) {
            error_log("Edit class error: " . $e->getMessage());
            setFlash('danger', 'Failed to update class. Please try again.');
        }
    }
}

// Get teachers for dropdown
$teachersStmt = $db->query("SELECT id, first_name, last_name FROM users WHERE role = 'teacher' AND school_id = ? AND status = 'active' ORDER BY first_name, last_name", [$_SESSION['school_id']]);
$teachers = $teachersStmt->fetchAll();

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
                <strong>Edit Class: <?php echo htmlspecialchars($class['name']); ?></strong>
            </div>
            
            <div class="card-body">
                <form method="POST" action="" data-validate>
                    <div class="row">
                        <!-- Class Information -->
                        <div class="col-md-12">
                            <h4 style="margin-bottom: 1rem; padding-bottom: 0.5rem; border-bottom: 2px solid var(--primary-color);">
                                Class Information
                            </h4>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">Class Name <span style="color: red;">*</span></label>
                                <input type="text" name="name" class="form-control" required 
                                       value="<?php echo htmlspecialchars($class['name']); ?>">
                                <small class="text-muted">Enter the full class name</small>
                            </div>
                        </div>
                        
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="form-label">Class Level</label>
                                <input type="number" name="class_level" class="form-control" min="1" max="12" 
                                       value="<?php echo $class['class_level']; ?>">
                                <small class="text-muted">Optional: Grade level (1-12)</small>
                            </div>
                        </div>
                        
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="form-label">Section</label>
                                <input type="text" name="section" class="form-control" 
                                       value="<?php echo htmlspecialchars($class['section']); ?>">
                                <small class="text-muted">Optional: Class section</small>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="form-label">Room Number</label>
                                <input type="text" name="room_number" class="form-control" 
                                       value="<?php echo htmlspecialchars($class['room_number']); ?>">
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="form-label">Capacity</label>
                                <input type="number" name="capacity" class="form-control" min="1" required
                                       value="<?php echo $class['capacity']; ?>">
                                <small class="text-muted">Maximum number of students</small>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="form-label">Class Teacher</label>
                                <select name="class_teacher_id" class="form-control">
                                    <option value="">Select Class Teacher</option>
                                    <?php foreach ($teachers as $teacher): ?>
                                        <option value="<?php echo $teacher['id']; ?>"
                                            <?php echo $class['class_teacher_id'] == $teacher['id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($teacher['first_name'] . ' ' . $teacher['last_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <small class="text-muted">Optional: Assign class teacher</small>
                            </div>
                        </div>
                        
                        <div class="col-md-12">
                            <div class="form-group">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-control" required>
                                    <option value="active" <?php echo $class['status'] === 'active' ? 'selected' : ''; ?>>Active</option>
                                    <option value="inactive" <?php echo $class['status'] === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group" style="margin-top: 2rem; padding-top: 1rem; border-top: 1px solid var(--border-color);">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-save"></i> Update Class
                        </button>
                        <a href="<?php echo BASE_URL; ?>public/admin/classes/index.php" class="btn btn-secondary btn-lg">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include APP_PATH . '/views/shared/footer.php'; ?>
