<?php
/**
 * Exams Management - Exam Officer
 * CoreSkool School Management System
 */

require_once dirname(dirname(dirname(__DIR__))) . '/config/config.php';

requireAuth();
requireRole('exam_officer');

$pageTitle = 'Exams Management';
$db = Database::getInstance();

// Get all exams
$examsQuery = $db->query("
    SELECT e.*, c.name as class_name, COUNT(DISTINCT r.id) as results_count
    FROM exams e
    LEFT JOIN classes c ON e.class_id = c.id
    LEFT JOIN results r ON e.id = r.exam_id
    WHERE e.school_id = ?
    GROUP BY e.id
    ORDER BY e.created_at DESC
", [$_SESSION['school_id']]);
$exams = $examsQuery->fetchAll();

include APP_PATH . '/views/shared/header.php';
?>

<div class="main-content">
    <?php include APP_PATH . '/views/shared/sidebar.php'; ?>
    <?php include APP_PATH . '/views/shared/topbar.php'; ?>
    
    <div class="content-area">
        <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
            <div>
                <h2 style="margin: 0;">Exams Management</h2>
                <p style="color: #6b7280; margin: 0.5rem 0 0 0;">Create and manage examinations</p>
            </div>
            <a href="<?php echo BASE_URL; ?>public/exam_officer/exams/create.php" class="btn btn-primary">
                <i class="fas fa-plus"></i> Create Exam
            </a>
        </div>

        <div class="card">
            <div class="card-header"><strong>All Examinations</strong></div>
            <div class="card-body">
                <?php if (empty($exams)): ?>
                    <div style="text-align: center; padding: 3rem;">
                        <i class="fas fa-file-alt" style="font-size: 4rem; color: #e5e7eb; margin-bottom: 1rem;"></i>
                        <h3 style="color: #6b7280;">No Exams Created</h3>
                        <p style="color: #9ca3af;">Start by creating your first examination</p>
                        <a href="<?php echo BASE_URL; ?>public/exam_officer/exams/create.php" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Create Exam
                        </a>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Exam Name</th>
                                    <th>Type</th>
                                    <th>Class</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th>Results</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($exams as $exam): ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($exam['name']); ?></strong></td>
                                        <td><?php echo ucfirst($exam['type']); ?></td>
                                        <td><?php echo htmlspecialchars($exam['class_name'] ?? 'All'); ?></td>
                                        <td><?php echo date('M d, Y', strtotime($exam['start_date'])); ?></td>
                                        <td>
                                            <span class="badge badge-<?php echo $exam['status'] === 'completed' ? 'success' : 'warning'; ?>">
                                                <?php echo ucfirst($exam['status']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo $exam['results_count']; ?></td>
                                        <td>
                                            <a href="<?php echo BASE_URL; ?>public/exam_officer/exams/view.php?id=<?php echo $exam['id']; ?>" 
                                               class="btn btn-sm btn-info"><i class="fas fa-eye"></i></a>
                                            <a href="<?php echo BASE_URL; ?>public/exam_officer/results/index.php?exam_id=<?php echo $exam['id']; ?>" 
                                               class="btn btn-sm btn-success"><i class="fas fa-graduation-cap"></i></a>
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
