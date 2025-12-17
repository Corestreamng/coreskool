<?php
/**
 * Exam Officer Dashboard
 * CoreSkool School Management System
 */

require_once dirname(dirname(__DIR__)) . '/config/config.php';

requireAuth();
requireRole('exam_officer');

$pageTitle = 'Exam Officer Dashboard';
$db = Database::getInstance();

// Get statistics
$examsQuery = $db->query("SELECT COUNT(*) as count FROM exams WHERE school_id = ?", [$_SESSION['school_id']]);
$totalExams = $examsQuery->fetch()['count'];

$ongoingQuery = $db->query("SELECT COUNT(*) as count FROM exams WHERE status = 'ongoing' AND school_id = ?", [$_SESSION['school_id']]);
$ongoingExams = $ongoingQuery->fetch()['count'];

$resultsQuery = $db->query("SELECT COUNT(*) as count FROM results r 
    INNER JOIN users u ON r.student_id = u.id 
    WHERE u.school_id = ? AND r.status = 'approved'", [$_SESSION['school_id']]);
$totalResults = $resultsQuery->fetch()['count'];

$cbtQuery = $db->query("SELECT COUNT(*) as count FROM cbt_exams WHERE school_id = ?", [$_SESSION['school_id']]);
$totalCBT = $cbtQuery->fetch()['count'];

$currentDate = date('l, F j, Y');
$currentTime = date('h:i A');

include APP_PATH . '/views/shared/header.php';
?>

<div class="main-content">
    <?php include APP_PATH . '/views/shared/sidebar.php'; ?>
    <?php include APP_PATH . '/views/shared/topbar.php'; ?>
    
    <div class="content-area">
        <!-- Welcome Banner -->
        <div class="welcome-banner">
            <h2>Welcome back, <?php echo $userName; ?>! 👋</h2>
            <p>Exam management and results processing dashboard.</p>
            <div class="welcome-banner-stats" style="margin-top: 1.5rem;">
                <div class="welcome-stat">
                    <div class="welcome-stat-icon"><i class="fas fa-calendar"></i></div>
                    <div class="welcome-stat-info">
                        <p style="margin: 0; font-size: 0.875rem;"><?php echo $currentDate; ?></p>
                        <h4 style="margin: 0;"><?php echo $currentTime; ?></h4>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Statistics Cards -->
        <div class="row">
            <div class="col-md-3">
                <div class="stats-card">
                    <div class="stats-icon" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                        <i class="fas fa-file-alt"></i>
                    </div>
                    <div class="stats-value"><?php echo $totalExams; ?></div>
                    <div class="stats-label">Total Exams</div>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="stats-card">
                    <div class="stats-icon" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white;">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stats-value"><?php echo $ongoingExams; ?></div>
                    <div class="stats-label">Ongoing Exams</div>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="stats-card">
                    <div class="stats-icon" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); color: white;">
                        <i class="fas fa-graduation-cap"></i>
                    </div>
                    <div class="stats-value"><?php echo $totalResults; ?></div>
                    <div class="stats-label">Approved Results</div>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="stats-card">
                    <div class="stats-icon" style="background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); color: white;">
                        <i class="fas fa-laptop"></i>
                    </div>
                    <div class="stats-value"><?php echo $totalCBT; ?></div>
                    <div class="stats-label">CBT Exams</div>
                </div>
            </div>
        </div>
        
        <!-- Quick Actions -->
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header"><strong>Quick Actions</strong></div>
                    <div class="card-body">
                        <div style="display: flex; gap: 0.75rem; flex-wrap: wrap;">
                            <a href="<?php echo BASE_URL; ?>public/exam_officer/exams/create.php" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Create Exam
                            </a>
                            <a href="<?php echo BASE_URL; ?>public/exam_officer/results/approve.php" class="btn btn-success">
                                <i class="fas fa-check"></i> Approve Results
                            </a>
                            <a href="<?php echo BASE_URL; ?>public/exam_officer/cbt/create.php" class="btn btn-info">
                                <i class="fas fa-laptop"></i> Create CBT
                            </a>
                            <a href="<?php echo BASE_URL; ?>public/exam_officer/reports/generate.php" class="btn btn-warning">
                                <i class="fas fa-chart-bar"></i> Generate Report
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include APP_PATH . '/views/shared/footer.php'; ?>
