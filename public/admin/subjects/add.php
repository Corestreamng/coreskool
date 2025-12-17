<?php
/**
 * Add New Subject
 * CoreSkool School Management System
 */

require_once dirname(dirname(dirname(__DIR__))) . '/config/config.php';

requireAuth();
requireRole('admin');

$pageTitle = 'Add New Subject';
$db = Database::getInstance();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'name' => sanitize($_POST['name']),
        'code' => sanitize($_POST['code'] ?? ''),
        'description' => sanitize($_POST['description'] ?? ''),
    ];
    
    // Validate required fields
    if (empty($data['name'])) {
        setFlash('danger', 'Subject name is required');
    } else {
        try {
            $sql = "INSERT INTO subjects (school_id, name, code, description, status, created_at) 
                    VALUES (?, ?, ?, ?, 'active', CURRENT_TIMESTAMP)";
            
            $db->query($sql, [
                $_SESSION['school_id'],
                $data['name'],
                $data['code'],
                $data['description']
            ]);
            
            logActivity($_SESSION['user_id'], 'add_subject', "Created new subject: {$data['name']}", $_SERVER['REMOTE_ADDR']);
            setFlash('success', 'Subject created successfully');
            redirect('admin/subjects/index.php');
        } catch (Exception $e) {
            error_log("Add Subject Error: " . $e->getMessage());
            setFlash('danger', 'Failed to create subject');
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
            <h2>Add New Subject</h2>
            <a href="<?php echo BASE_URL; ?>public/admin/subjects/index.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to List
            </a>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <strong>Subject Information</strong>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="">
                            <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="name">Subject Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="name" name="name" 
                                               placeholder="e.g., Mathematics, English Language" required>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="code">Subject Code</label>
                                        <input type="text" class="form-control" id="code" name="code" 
                                               placeholder="e.g., MAT101, ENG101">
                                        <small class="form-text text-muted">Optional - unique identifier for the subject</small>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="description">Description</label>
                                <textarea class="form-control" id="description" name="description" rows="4"
                                          placeholder="Optional description about the subject and what it covers"></textarea>
                            </div>
                            
                            <div class="alert alert-info" style="margin-top: 1rem;">
                                <i class="fas fa-info-circle"></i>
                                <strong>Note:</strong> After creating the subject, you can assign it to classes and teachers from the classes management page.
                            </div>
                            
                            <div class="form-group" style="margin-top: 2rem;">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-plus"></i> Create Subject
                                </button>
                                <a href="<?php echo BASE_URL; ?>public/admin/subjects/index.php" 
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
