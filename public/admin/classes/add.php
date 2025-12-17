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
    $data = [
        'name' => sanitize($_POST['name']),
        'description' => sanitize($_POST['description'] ?? ''),
        'class_teacher_id' => (int)($_POST['class_teacher_id'] ?? 0) ?: null,
        'capacity' => (int)($_POST['capacity'] ?? 0) ?: null,
    ];
    
    // Validate required fields
    if (empty($data['name'])) {
        setFlash('danger', 'Class name is required');
    } else {
        try {
            $sql = "INSERT INTO classes (school_id, name, description, class_teacher_id, capacity, status, created_at) 
                    VALUES (?, ?, ?, ?, ?, 'active', CURRENT_TIMESTAMP)";
            
            $db->query($sql, [
                $_SESSION['school_id'],
                $data['name'],
                $data['description'],
                $data['class_teacher_id'],
                $data['capacity']
            ]);
            
            logActivity($_SESSION['user_id'], 'add_class', "Created new class: {$data['name']}", $_SERVER['REMOTE_ADDR']);
            setFlash('success', 'Class created successfully');
            redirect('admin/classes/index.php');
        } catch (Exception $e) {
            error_log("Add Class Error: " . $e->getMessage());
            setFlash('danger', 'Failed to create class');
        }
    }
}

// Get teachers for class teacher assignment
$teachersStmt = $db->query(
    "SELECT id, first_name, last_name FROM users WHERE role = 'teacher' AND school_id = ? AND status = 'active' ORDER BY first_name, last_name",
    [$_SESSION['school_id']]
);
$teachers = $teachersStmt->fetchAll();

include APP_PATH . '/views/shared/header.php';
?>

<div class="main-content">
    <?php include APP_PATH . '/views/shared/sidebar.php'; ?>
    <?php include APP_PATH . '/views/shared/topbar.php'; ?>
    
    <div class="content-area">
        <div class="page-header">
            <h2>Add New Class</h2>
            <a href="<?php echo BASE_URL; ?>public/admin/classes/index.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to List
            </a>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <strong>Class Information</strong>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="">
                            <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="name">Class Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="name" name="name" 
                                               placeholder="e.g., JSS 1A, SS 2B" required>
                                        <small class="form-text text-muted">Enter the full class name</small>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="class_teacher_id">Class Teacher</label>
                                        <select class="form-control" id="class_teacher_id" name="class_teacher_id">
                                            <option value="">Select Class Teacher</option>
                                            <?php foreach ($teachers as $teacher): ?>
                                                <option value="<?php echo $teacher['id']; ?>">
                                                    <?php echo $teacher['first_name'] . ' ' . $teacher['last_name']; ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <small class="form-text text-muted">Optional - can be assigned later</small>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="description">Description</label>
                                        <textarea class="form-control" id="description" name="description" rows="3"
                                                  placeholder="Optional description about the class"></textarea>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="capacity">Class Capacity</label>
                                        <input type="number" class="form-control" id="capacity" name="capacity" 
                                               placeholder="Maximum number of students" min="1" max="500">
                                        <small class="form-text text-muted">Optional - leave blank for unlimited</small>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="alert alert-info" style="margin-top: 1rem;">
                                <i class="fas fa-info-circle"></i>
                                <strong>Note:</strong> After creating the class, you can assign students and subjects from the class details page.
                            </div>
                            
                            <div class="form-group" style="margin-top: 2rem;">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-plus"></i> Create Class
                                </button>
                                <a href="<?php echo BASE_URL; ?>public/admin/classes/index.php" 
                                   class="btn btn-secondary btn-lg">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include APP_PATH . '/views/shared/footer.php'; ?>
