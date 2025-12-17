<?php
/**
 * Create Exam - Admin
 * CoreSkool School Management System
 */

require_once dirname(dirname(dirname(__DIR__))) . '/config/config.php';

requireAuth();
requireRole('admin');

$pageTitle = 'Create Exam';
$db = Database::getInstance();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $name = sanitize($_POST['name']);
        $type = sanitize($_POST['type']);
        $class_id = !empty($_POST['class_id']) ? (int)$_POST['class_id'] : null;
        $start_date = sanitize($_POST['start_date']);
        $end_date = sanitize($_POST['end_date']);
        $description = sanitize($_POST['description'] ?? '');
        $status = 'scheduled';
        
        $sql = "INSERT INTO exams (school_id, name, type, class_id, start_date, end_date, description, status, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";
        
        $db->query($sql, [
            $_SESSION['school_id'],
            $name,
            $type,
            $class_id,
            $start_date,
            $end_date,
            $description,
            $status
        ]);
        
        $_SESSION['success_message'] = 'Exam created successfully!';
        redirect('public/admin/exams/index.php');
    } catch (Exception $e) {
        $error_message = 'Error creating exam: ' . $e->getMessage();
    }
}

// Get classes for dropdown
$classesQuery = $db->query("SELECT id, name FROM classes WHERE school_id = ? AND status = 'active' ORDER BY name", [$_SESSION['school_id']]);
$classes = $classesQuery->fetchAll();

include APP_PATH . '/views/shared/header.php';
?>

<div class="main-content">
    <?php include APP_PATH . '/views/shared/sidebar.php'; ?>
    <?php include APP_PATH . '/views/shared/topbar.php'; ?>
    
    <div class="content-area">
        <div class="page-header" style="margin-bottom: 2rem;">
            <h2>Create New Exam</h2>
            <p style="color: #6b7280;">Set up a new examination for your school</p>
        </div>

        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <div class="card">
            <div class="card-body">
                <form method="POST" action="">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Exam Name <span style="color: red;">*</span></label>
                                <input type="text" id="name" name="name" class="form-control" required 
                                       placeholder="e.g., First Term Examination 2024">
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="type">Exam Type <span style="color: red;">*</span></label>
                                <select id="type" name="type" class="form-control" required>
                                    <option value="">-- Select Type --</option>
                                    <option value="mid_term">Mid-Term</option>
                                    <option value="final">Final/Terminal</option>
                                    <option value="quiz">Quiz</option>
                                    <option value="test">Class Test</option>
                                    <option value="assessment">Continuous Assessment</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="class_id">Class</label>
                                <select id="class_id" name="class_id" class="form-control">
                                    <option value="">-- All Classes --</option>
                                    <?php foreach ($classes as $class): ?>
                                        <option value="<?php echo $class['id']; ?>">
                                            <?php echo htmlspecialchars($class['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <small class="form-text text-muted">Leave empty to apply to all classes</small>
                            </div>
                        </div>
                        
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="start_date">Start Date <span style="color: red;">*</span></label>
                                <input type="date" id="start_date" name="start_date" class="form-control" required>
                            </div>
                        </div>
                        
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="end_date">End Date <span style="color: red;">*</span></label>
                                <input type="date" id="end_date" name="end_date" class="form-control" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" class="form-control" rows="4" 
                                  placeholder="Enter exam description, instructions, or notes"></textarea>
                    </div>
                    
                    <div style="display: flex; gap: 1rem; margin-top: 2rem;">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Create Exam
                        </button>
                        <a href="<?php echo BASE_URL; ?>public/admin/exams/index.php" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include APP_PATH . '/views/shared/footer.php'; ?>
