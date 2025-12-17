<?php
/**
 * CBT System - Admin
 * CoreSkool School Management System
 */

require_once dirname(dirname(dirname(__DIR__))) . '/config/config.php';

requireAuth();
requireRole('admin');

$pageTitle = 'CBT System';
$db = Database::getInstance();

// Get CBT exams
$cbtQuery = $db->query("
    SELECT ce.*, c.name as class_name, s.name as subject_name,
           COUNT(DISTINCT ca.id) as attempts_count
    FROM cbt_exams ce
    LEFT JOIN classes c ON ce.class_id = c.id
    LEFT JOIN subjects s ON ce.subject_id = s.id
    LEFT JOIN cbt_attempts ca ON ce.id = ca.cbt_exam_id
    WHERE ce.school_id = ?
    GROUP BY ce.id
    ORDER BY ce.created_at DESC
", [$_SESSION['school_id']]);
$cbtExams = $cbtQuery->fetchAll();

include APP_PATH . '/views/shared/header.php';
?>

<div class="main-content">
    <?php include APP_PATH . '/views/shared/sidebar.php'; ?>
    <?php include APP_PATH . '/views/shared/topbar.php'; ?>
    
    <div class="content-area">
        <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
            <div>
                <h2 style="margin: 0;">CBT System</h2>
                <p style="color: #6b7280; margin: 0.5rem 0 0 0;">Computer-Based Testing management</p>
            </div>
            <a href="<?php echo BASE_URL; ?>public/admin/cbt/create.php" class="btn btn-primary">
                <i class="fas fa-plus"></i> Create CBT Exam
            </a>
        </div>

        <div class="card">
            <div class="card-header"><strong>CBT Examinations</strong></div>
            <div class="card-body">
                <?php if (empty($cbtExams)): ?>
                    <div style="text-align: center; padding: 3rem;">
                        <i class="fas fa-laptop" style="font-size: 4rem; color: #e5e7eb; margin-bottom: 1rem;"></i>
                        <h3 style="color: #6b7280; margin-bottom: 0.5rem;">No CBT Exams Created</h3>
                        <p style="color: #9ca3af; margin-bottom: 1.5rem;">Start creating computer-based tests for your students</p>
                        <a href="<?php echo BASE_URL; ?>public/admin/cbt/create.php" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Create CBT Exam
                        </a>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Exam Title</th>
                                    <th>Subject</th>
                                    <th>Class</th>
                                    <th>Duration</th>
                                    <th>Questions</th>
                                    <th>Attempts</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($cbtExams as $exam): ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($exam['title']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($exam['subject_name'] ?? 'N/A'); ?></td>
                                        <td><?php echo htmlspecialchars($exam['class_name'] ?? 'All'); ?></td>
                                        <td><?php echo $exam['duration']; ?> mins</td>
                                        <td><?php echo $exam['total_questions']; ?></td>
                                        <td><?php echo $exam['attempts_count']; ?></td>
                                        <td>
                                            <span class="badge badge-<?php echo $exam['status'] === 'published' ? 'success' : 'warning'; ?>">
                                                <?php echo ucfirst($exam['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="<?php echo BASE_URL; ?>public/admin/cbt/view.php?id=<?php echo $exam['id']; ?>" 
                                               class="btn btn-sm btn-info"><i class="fas fa-eye"></i></a>
                                            <a href="<?php echo BASE_URL; ?>public/admin/cbt/edit.php?id=<?php echo $exam['id']; ?>" 
                                               class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></a>
                                            <a href="<?php echo BASE_URL; ?>public/admin/cbt/questions.php?exam_id=<?php echo $exam['id']; ?>" 
                                               class="btn btn-sm btn-primary"><i class="fas fa-question"></i></a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include APP_PATH . '/views/shared/footer.php'; ?>
