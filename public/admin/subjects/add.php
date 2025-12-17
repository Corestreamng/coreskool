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
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
        setFlash('danger', 'Invalid form submission. Please try again.');
    } else {
        $name = sanitize($_POST['name']);
        $code = sanitize($_POST['code'] ?? '');
        $description = sanitize($_POST['description'] ?? '');
        
        // Validate required fields
        if (empty($name)) {
            setFlash('danger', 'Subject name is required');
        } else {
        try {
            // Check if subject name already exists
            $existingSubject = $db->query("SELECT id FROM subjects WHERE name = ? AND school_id = ? AND status = 'active'", 
                [$name, $_SESSION['school_id']])->fetch();
            
            if ($existingSubject) {
                setFlash('danger', 'A subject with this name already exists');
            } elseif ($code) {
                // Check if code is provided and if it's unique
                $existingCode = $db->query("SELECT id FROM subjects WHERE code = ? AND school_id = ? AND status = 'active'", 
                    [$code, $_SESSION['school_id']])->fetch();
                
                if ($existingCode) {
                    setFlash('danger', 'A subject with this code already exists');
                } else {
                    // Insert subject
                    $sql = "INSERT INTO subjects (school_id, name, code, description, status, created_at) 
                            VALUES (?, ?, ?, ?, 'active', NOW())";
                    
                    $params = [
                        $_SESSION['school_id'],
                        $name,
                        $code,
                        $description ?: null
                    ];
                    
                    $db->query($sql, $params);
                    
                    logActivity($_SESSION['user_id'], 'add_subject', "Added new subject: {$name}", $_SERVER['REMOTE_ADDR']);
                    setFlash('success', "Subject '{$name}' added successfully!");
                    redirect('admin/subjects/index.php');
                }
            } else {
                // Insert subject without code check
                $sql = "INSERT INTO subjects (school_id, name, code, description, status, created_at) 
                        VALUES (?, ?, ?, ?, 'active', NOW())";
                
                $params = [
                    $_SESSION['school_id'],
                    $name,
                    null,
                    $description ?: null
                ];
                
                $db->query($sql, $params);
                
                logActivity($_SESSION['user_id'], 'add_subject', "Added new subject: {$name}", $_SERVER['REMOTE_ADDR']);
                setFlash('success', "Subject '{$name}' added successfully!");
                redirect('admin/subjects/index.php');
            }
        } catch (Exception $e) {
            error_log("Failed to add subject: " . $e->getMessage());
            setFlash('danger', 'Failed to add subject. Please try again.');
        }
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
                <strong>Add New Subject</strong>
            </div>
            
            <div class="card-body">
                <form method="POST" action="">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                    <div class="row">
                        <!-- Basic Information -->
                        <div class="col-md-12">
                            <h4 style="margin-bottom: 1rem; padding-bottom: 0.5rem; border-bottom: 2px solid var(--primary-color);">
                                Subject Information
                            </h4>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">Subject Name <span style="color: red;">*</span></label>
                                <input type="text" name="name" class="form-control" required placeholder="e.g., Mathematics, English Language, Biology">
                                <small class="text-muted">Enter the full subject name</small>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">Subject Code</label>
                                <input type="text" name="code" class="form-control" placeholder="e.g., MATH101, ENG101, BIO201" maxlength="50">
                                <small class="text-muted">Optional unique identifier for the subject</small>
                            </div>
                        </div>
                        
                        <div class="col-md-12">
                            <div class="form-group">
                                <label class="form-label">Description</label>
                                <textarea name="description" class="form-control" rows="4" placeholder="Brief description of the subject content and objectives..."></textarea>
                                <small class="text-muted">Optional description of what this subject covers</small>
                            </div>
                        </div>
                        
                        <!-- Information Box -->
                        <div class="col-md-12">
                            <div class="alert alert-info" style="margin-top: 1rem;">
                                <i class="fas fa-info-circle"></i> 
                                <strong>Note:</strong> After creating a subject, you can assign it to specific classes and teachers from the Classes management section.
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group" style="margin-top: 2rem; padding-top: 1rem; border-top: 1px solid var(--border-color);">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-save"></i> Save Subject
                        </button>
                        <a href="<?php echo BASE_URL; ?>public/admin/subjects/index.php" class="btn btn-secondary btn-lg">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include APP_PATH . '/views/shared/footer.php'; ?>
