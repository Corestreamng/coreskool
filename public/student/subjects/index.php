<?php
/**
 * My Subjects - Student
 * CoreSkool School Management System
 */

require_once dirname(dirname(dirname(__DIR__))) . '/config/config.php';

requireAuth();
requireRole('student');

$pageTitle = 'My Subjects';
$db = Database::getInstance();
$userId = $_SESSION['user_id'];

// Get student's class
$classQuery = $db->query("SELECT class_id FROM student_classes WHERE student_id = ? AND status = 'active' LIMIT 1", [$userId]);
$studentClass = $classQuery->fetch();
$classId = $studentClass['class_id'] ?? null;

// Get subjects
$subjects = [];
if ($classId) {
    $subjectsQuery = $db->query("
        SELECT s.*, cs.is_compulsory, u.first_name, u.last_name
        FROM class_subjects cs
        INNER JOIN subjects s ON cs.subject_id = s.id
        LEFT JOIN users u ON cs.teacher_id = u.id
        WHERE cs.class_id = ?
        ORDER BY s.name
    ", [$classId]);
    $subjects = $subjectsQuery->fetchAll();
}

include APP_PATH . '/views/shared/header.php';
?>

<div class="main-content">
    <?php include APP_PATH . '/views/shared/sidebar.php'; ?>
    <?php include APP_PATH . '/views/shared/topbar.php'; ?>
    
    <div class="content-area">
        <div class="page-header" style="margin-bottom: 2rem;">
            <h2>My Subjects</h2>
            <p style="color: #6b7280;">View your enrolled subjects and teachers</p>
        </div>

        <?php if (!$classId): ?>
            <div class="card">
                <div class="card-body" style="text-align: center; padding: 3rem;">
                    <i class="fas fa-exclamation-triangle" style="font-size: 4rem; color: #f59e0b; margin-bottom: 1rem;"></i>
                    <h3 style="color: #6b7280;">Not Assigned to Class</h3>
                    <p style="color: #9ca3af;">You haven't been assigned to a class yet</p>
                </div>
            </div>
        <?php elseif (empty($subjects)): ?>
            <div class="card">
                <div class="card-body" style="text-align: center; padding: 3rem;">
                    <i class="fas fa-book" style="font-size: 4rem; color: #e5e7eb; margin-bottom: 1rem;"></i>
                    <h3 style="color: #6b7280;">No Subjects Assigned</h3>
                    <p style="color: #9ca3af;">No subjects have been assigned to your class yet</p>
                </div>
            </div>
        <?php else: ?>
            <div class="row">
                <?php foreach ($subjects as $subject): ?>
                    <div class="col-md-6" style="margin-bottom: 1.5rem;">
                        <div class="card" style="border-left: 4px solid #667eea;">
                            <div class="card-body">
                                <h4 style="margin: 0 0 0.5rem 0;">
                                    <i class="fas fa-book"></i> <?php echo htmlspecialchars($subject['name']); ?>
                                    <?php if ($subject['is_compulsory']): ?>
                                        <span class="badge badge-primary">Compulsory</span>
                                    <?php else: ?>
                                        <span class="badge badge-info">Elective</span>
                                    <?php endif; ?>
                                </h4>
                                <p style="color: #6b7280; margin: 0.5rem 0;">
                                    <strong>Subject Code:</strong> <?php echo htmlspecialchars($subject['code'] ?? 'N/A'); ?>
                                </p>
                                <p style="color: #6b7280; margin: 0.5rem 0;">
                                    <strong>Teacher:</strong> 
                                    <?php 
                                    if ($subject['first_name']) {
                                        echo htmlspecialchars($subject['first_name'] . ' ' . $subject['last_name']);
                                    } else {
                                        echo 'Not Assigned';
                                    }
                                    ?>
                                </p>
                                <?php if ($subject['description']): ?>
                                    <p style="color: #6b7280; margin: 0.5rem 0;">
                                        <?php echo htmlspecialchars($subject['description']); ?>
                                    </p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include APP_PATH . '/views/shared/footer.php'; ?>
