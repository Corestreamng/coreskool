<?php
/**
 * Exams Management - Admin
 * CoreSkool School Management System
 */

require_once dirname(dirname(dirname(__DIR__))) . '/config/config.php';

requireAuth();
requireRole('admin');

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
                <p style="color: #6b7280; margin: 0.5rem 0 0 0;">Create, manage, and monitor examinations</p>
            </div>
            <a href="<?php echo BASE_URL; ?>public/admin/exams/create.php" class="btn btn-primary">
                <i class="fas fa-plus"></i> Create New Exam
            </a>
        </div>

        <!-- Statistics Cards -->
        <div class="row" style="margin-bottom: 2rem;">
            <div class="col-md-3">
                <div class="stats-card">
                    <div class="stats-icon" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                        <i class="fas fa-file-alt"></i>
                    </div>
                    <div class="stats-value"><?php echo count($exams); ?></div>
                    <div class="stats-label">Total Exams</div>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="stats-card">
                    <div class="stats-icon" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white;">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stats-value">
                        <?php 
                        $ongoing = array_filter($exams, fn($e) => $e['status'] === 'ongoing');
                        echo count($ongoing);
                        ?>
                    </div>
                    <div class="stats-label">Ongoing</div>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="stats-card">
                    <div class="stats-icon" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); color: white;">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stats-value">
                        <?php 
                        $completed = array_filter($exams, fn($e) => $e['status'] === 'completed');
                        echo count($completed);
                        ?>
                    </div>
                    <div class="stats-label">Completed</div>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="stats-card">
                    <div class="stats-icon" style="background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); color: white;">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <div class="stats-value">
                        <?php 
                        $upcoming = array_filter($exams, fn($e) => $e['status'] === 'scheduled');
                        echo count($upcoming);
                        ?>
                    </div>
                    <div class="stats-label">Scheduled</div>
                </div>
            </div>
        </div>

        <!-- Exams List -->
        <div class="card">
            <div class="card-header">
                <strong>All Examinations</strong>
            </div>
            <div class="card-body">
                <?php if (empty($exams)): ?>
                    <div style="text-align: center; padding: 3rem;">
                        <i class="fas fa-file-alt" style="font-size: 4rem; color: #e5e7eb; margin-bottom: 1rem;"></i>
                        <h3 style="color: #6b7280; margin-bottom: 0.5rem;">No Exams Created Yet</h3>
                        <p style="color: #9ca3af; margin-bottom: 1.5rem;">Start by creating your first examination</p>
                        <a href="<?php echo BASE_URL; ?>public/admin/exams/create.php" class="btn btn-primary">
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
                                        <td>
                                            <span class="badge badge-info">
                                                <?php echo ucfirst($exam['type']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo htmlspecialchars($exam['class_name'] ?? 'All Classes'); ?></td>
                                        <td><?php echo date('M d, Y', strtotime($exam['start_date'])); ?></td>
                                        <td>
                                            <?php
                                            $statusColors = [
                                                'scheduled' => 'info',
                                                'ongoing' => 'warning',
                                                'completed' => 'success',
                                                'cancelled' => 'danger'
                                            ];
                                            $color = $statusColors[$exam['status']] ?? 'secondary';
                                            ?>
                                            <span class="badge badge-<?php echo $color; ?>">
                                                <?php echo ucfirst($exam['status']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo $exam['results_count']; ?> entries</td>
                                        <td>
                                            <a href="<?php echo BASE_URL; ?>public/admin/exams/view.php?id=<?php echo $exam['id']; ?>" 
                                               class="btn btn-sm btn-info" title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="<?php echo BASE_URL; ?>public/admin/exams/edit.php?id=<?php echo $exam['id']; ?>" 
                                               class="btn btn-sm btn-warning" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="<?php echo BASE_URL; ?>public/admin/results/index.php?exam_id=<?php echo $exam['id']; ?>" 
                                               class="btn btn-sm btn-success" title="View Results">
                                                <i class="fas fa-graduation-cap"></i>
                                            </a>
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
