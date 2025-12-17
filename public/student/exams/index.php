<?php
/**
 * Exams - Student
 * CoreSkool School Management System
 */

require_once dirname(dirname(dirname(__DIR__))) . '/config/config.php';

requireAuth();
requireRole('student');

$pageTitle = 'My Exams';
$db = Database::getInstance();
$userId = $_SESSION['user_id'];

// Get student's class
$classQuery = $db->query("
    SELECT class_id FROM student_classes WHERE student_id = ? AND status = 'active' LIMIT 1
", [$userId]);
$studentClass = $classQuery->fetch();
$classId = $studentClass['class_id'] ?? null;

// Get exams for student's class
$examsQuery = $db->query("
    SELECT e.*, 
           COUNT(DISTINCT r.id) as my_results_count
    FROM exams e
    LEFT JOIN results r ON e.id = r.exam_id AND r.student_id = ?
    WHERE e.school_id = ? AND (e.class_id = ? OR e.class_id IS NULL)
    GROUP BY e.id
    ORDER BY e.start_date DESC
", [$userId, $_SESSION['school_id'], $classId]);
$exams = $examsQuery->fetchAll();

include APP_PATH . '/views/shared/header.php';
?>

<div class="main-content">
    <?php include APP_PATH . '/views/shared/sidebar.php'; ?>
    <?php include APP_PATH . '/views/shared/topbar.php'; ?>
    
    <div class="content-area">
        <div class="page-header" style="margin-bottom: 2rem;">
            <h2>My Examinations</h2>
            <p style="color: #6b7280;">View your scheduled exams and results</p>
        </div>

        <div class="card">
            <div class="card-header"><strong>Examination Schedule</strong></div>
            <div class="card-body">
                <?php if (empty($exams)): ?>
                    <div style="text-align: center; padding: 3rem;">
                        <i class="fas fa-file-alt" style="font-size: 4rem; color: #e5e7eb; margin-bottom: 1rem;"></i>
                        <h3 style="color: #6b7280;">No Exams Scheduled</h3>
                        <p style="color: #9ca3af;">There are no exams scheduled for your class at this time</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Exam Name</th>
                                    <th>Type</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th>My Results</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($exams as $exam): ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($exam['name']); ?></strong></td>
                                        <td><?php echo ucfirst($exam['type']); ?></td>
                                        <td><?php echo date('M d, Y', strtotime($exam['start_date'])); ?></td>
                                        <td>
                                            <?php
                                            $statusColors = [
                                                'scheduled' => 'info',
                                                'ongoing' => 'warning',
                                                'completed' => 'success'
                                            ];
                                            $color = $statusColors[$exam['status']] ?? 'secondary';
                                            ?>
                                            <span class="badge badge-<?php echo $color; ?>">
                                                <?php echo ucfirst($exam['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($exam['my_results_count'] > 0): ?>
                                                <span class="badge badge-success">Available</span>
                                            <?php else: ?>
                                                <span class="badge badge-secondary">Pending</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($exam['my_results_count'] > 0): ?>
                                                <a href="<?php echo BASE_URL; ?>public/student/results/index.php?exam_id=<?php echo $exam['id']; ?>" 
                                                   class="btn btn-sm btn-primary">
                                                    <i class="fas fa-eye"></i> View Results
                                                </a>
                                            <?php else: ?>
                                                <span style="color: #9ca3af;">No results yet</span>
                                            <?php endif; ?>
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
