<?php
/**
 * Add New Class
 * CoreSkool School Management System
 */

require_once dirname(dirname(dirname(__DIR__))) . '/config/config.php';

requireAuth();
requireRole('admin');

$pageTitle = 'Add New Class';
$db = Database::getInstance();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
        setFlash('danger', 'Invalid form submission. Please try again.');
    } else {
        $name = sanitize($_POST['name']);
        $classLevel = sanitize($_POST['class_level'] ?? '');
        $section = sanitize($_POST['section'] ?? '');
        $capacity = (int)($_POST['capacity'] ?? 40);
        $classTeacherId = (int)($_POST['class_teacher_id'] ?? 0);
        $roomNumber = sanitize($_POST['room_number'] ?? '');
        
        // Validate required fields
        if (empty($name)) {
            setFlash('danger', 'Class name is required');
        } else {
        try {
            // Check if class name already exists
            $existingClass = $db->query("SELECT id FROM classes WHERE name = ? AND school_id = ? AND status = 'active'", 
                [$name, $_SESSION['school_id']])->fetch();
            
            if ($existingClass) {
                setFlash('danger', 'A class with this name already exists');
            } else {
                // Insert class
                $sql = "INSERT INTO classes (school_id, name, class_level, section, capacity, class_teacher_id, room_number, status, created_at) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, 'active', NOW())";
                
                $params = [
                    $_SESSION['school_id'],
                    $name,
                    $classLevel ?: null,
                    $section ?: null,
                    $capacity,
                    $classTeacherId > 0 ? $classTeacherId : null,
                    $roomNumber ?: null
                ];
                
                $db->query($sql, $params);
                
                logActivity($_SESSION['user_id'], 'add_class', "Added new class: {$name}", $_SERVER['REMOTE_ADDR']);
                setFlash('success', "Class '{$name}' added successfully!");
                redirect('admin/classes/index.php');
            }
        } catch (Exception $e) {
            error_log("Failed to add class: " . $e->getMessage());
            setFlash('danger', 'Failed to add class. Please try again.');
        }
        }
    }
}

// Get teachers for dropdown
$teachersStmt = $db->query("SELECT id, first_name, last_name FROM users WHERE role = 'teacher' AND status = 'active' AND school_id = ? ORDER BY first_name, last_name", [$_SESSION['school_id']]);
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
                <strong>Add New Class</strong>
            </div>
            
            <div class="card-body">
                <form method="POST" action="">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                    <div class="row">
                        <!-- Basic Information -->
                        <div class="col-md-12">
                            <h4 style="margin-bottom: 1rem; padding-bottom: 0.5rem; border-bottom: 2px solid var(--primary-color);">
                                Class Information
                            </h4>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">Class Name <span style="color: red;">*</span></label>
                                <input type="text" name="name" class="form-control" required placeholder="e.g., JSS 1, SS 2, Primary 3">
                                <small class="text-muted">Enter the full class name</small>
                            </div>
                        </div>
                        
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="form-label">Class Level</label>
                                <input type="number" name="class_level" class="form-control" placeholder="e.g., 1, 2, 3" min="1" max="12">
                                <small class="text-muted">Optional numeric level</small>
                            </div>
                        </div>
                        
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="form-label">Section</label>
                                <input type="text" name="section" class="form-control" placeholder="e.g., A, B, Science">
                                <small class="text-muted">Optional section/stream</small>
                            </div>
                        </div>
                        
                        <!-- Teacher and Room -->
                        <div class="col-md-12">
                            <h4 style="margin: 1.5rem 0 1rem; padding-bottom: 0.5rem; border-bottom: 2px solid var(--primary-color);">
                                Assignment Details
                            </h4>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">Class Teacher</label>
                                <select name="class_teacher_id" class="form-control">
                                    <option value="">Select Teacher (Optional)</option>
                                    <?php foreach ($teachers as $teacher): ?>
                                        <option value="<?php echo $teacher['id']; ?>">
                                            <?php echo htmlspecialchars($teacher['first_name'] . ' ' . $teacher['last_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <small class="text-muted">Assign a primary class teacher</small>
                            </div>
                        </div>
                        
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="form-label">Room Number</label>
                                <input type="text" name="room_number" class="form-control" placeholder="e.g., Room 101">
                                <small class="text-muted">Optional classroom location</small>
                            </div>
                        </div>
                        
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="form-label">Capacity</label>
                                <input type="number" name="capacity" class="form-control" value="40" min="1" max="200">
                                <small class="text-muted">Maximum students</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group" style="margin-top: 2rem; padding-top: 1rem; border-top: 1px solid var(--border-color);">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-save"></i> Save Class
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
