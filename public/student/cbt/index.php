<?php
/**
 * CBT Exams - Student
 * CoreSkool School Management System
 */

require_once dirname(dirname(dirname(__DIR__))) . '/config/config.php';

requireAuth();
requireRole('student');

$pageTitle = 'CBT Exams';
$db = Database::getInstance();
$userId = $_SESSION['user_id'];

// Get student's class
$classQuery = $db->query("SELECT class_id FROM student_classes WHERE student_id = ? AND status = 'active' LIMIT 1", [$userId]);
$studentClass = $classQuery->fetch();
$classId = $studentClass['class_id'] ?? null;

// Get available CBT exams
$examsQuery = $db->query("
    SELECT ce.*, s.name as subject_name,
           ca.id as attempt_id, ca.score, ca.completed_at
    FROM cbt_exams ce
    INNER JOIN subjects s ON ce.subject_id = s.id
    LEFT JOIN cbt_attempts ca ON ce.id = ca.cbt_exam_id AND ca.student_id = ?
    WHERE ce.school_id = ? AND ce.status = 'published' 
          AND (ce.class_id = ? OR ce.class_id IS NULL)
    ORDER BY ce.created_at DESC
", [$userId, $_SESSION['school_id'], $classId]);
$exams = $examsQuery->fetchAll();

include APP_PATH . '/views/shared/header.php';
?>

<div class="main-content">
    <?php include APP_PATH . '/views/shared/sidebar.php'; ?>
    <?php include APP_PATH . '/views/shared/topbar.php'; ?>
    
    <div class="content-area">
        <div class="page-header" style="margin-bottom: 2rem;">
            <h2>CBT Examinations</h2>
            <p style="color: #6b7280;">Take computer-based tests and view your scores</p>
        </div>

        <div class="card">
            <div class="card-header"><strong>Available Tests</strong></div>
            <div class="card-body">
                <?php if (empty($exams)): ?>
                    <div style="text-align: center; padding: 3rem;">
                        <i class="fas fa-laptop" style="font-size: 4rem; color: #e5e7eb; margin-bottom: 1rem;"></i>
                        <h3 style="color: #6b7280;">No CBT Exams Available</h3>
                        <p style="color: #9ca3af;">There are no CBT tests available for you at this time</p>
                    </div>
                <?php else: ?>
                    <div class="row">
                        <?php foreach ($exams as $exam): ?>
                            <div class="col-md-6" style="margin-bottom: 1.5rem;">
                                <div class="card" style="border: 1px solid #e5e7eb;">
                                    <div class="card-body">
                                        <h4><?php echo htmlspecialchars($exam['title']); ?></h4>
                                        <p style="color: #6b7280; margin: 0.5rem 0;">
                                            <strong>Subject:</strong> <?php echo htmlspecialchars($exam['subject_name']); ?>
                                        </p>
                                        <p style="color: #6b7280; margin: 0.5rem 0;">
                                            <strong>Duration:</strong> <?php echo $exam['duration']; ?> minutes
                                        </p>
                                        <p style="color: #6b7280; margin: 0.5rem 0;">
                                            <strong>Questions:</strong> <?php echo $exam['total_questions']; ?>
                                        </p>
                                        <p style="color: #6b7280; margin: 0.5rem 0;">
                                            <strong>Passing Score:</strong> <?php echo $exam['passing_score']; ?>%
                                        </p>
                                        
                                        <?php if ($exam['attempt_id']): ?>
                                            <div style="background-color: #f0f9ff; border: 1px solid #bfdbfe; border-radius: 6px; padding: 1rem; margin-top: 1rem;">
                                                <p style="margin: 0; color: #1e40af;">
                                                    <i class="fas fa-check-circle"></i> 
                                                    <strong>Completed</strong> - Score: <?php echo $exam['score']; ?>%
                                                </p>
                                                <small style="color: #64748b;">
                                                    Completed on: <?php echo date('M d, Y h:i A', strtotime($exam['completed_at'])); ?>
                                                </small>
                                            </div>
                                            <a href="<?php echo BASE_URL; ?>public/student/cbt/result.php?attempt_id=<?php echo $exam['attempt_id']; ?>" 
                                               class="btn btn-info btn-block" style="margin-top: 1rem;">
                                                <i class="fas fa-eye"></i> View Results
                                            </a>
                                        <?php else: ?>
                                            <a href="<?php echo BASE_URL; ?>public/student/cbt/take.php?id=<?php echo $exam['id']; ?>" 
                                               class="btn btn-primary btn-block" style="margin-top: 1rem;">
                                                <i class="fas fa-play"></i> Start Test
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include APP_PATH . '/views/shared/footer.php'; ?>
