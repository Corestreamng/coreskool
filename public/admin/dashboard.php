<?php
/**
 * Admin Dashboard
 * CoreSkool School Management System
 */

require_once dirname(dirname(__DIR__)) . '/config/config.php';

// Require authentication and admin role
requireAuth();
requireRole('admin');

$pageTitle = 'Admin Dashboard';

// Get statistics
$db = Database::getInstance();

// Total Students
$studentsQuery = $db->query("SELECT COUNT(*) as count FROM users WHERE role = 'student' AND status = 'active' AND school_id = ?", [$_SESSION['school_id']]);
$totalStudents = $studentsQuery->fetch()['count'];

// Total Teachers
$teachersQuery = $db->query("SELECT COUNT(*) as count FROM users WHERE role = 'teacher' AND status = 'active' AND school_id = ?", [$_SESSION['school_id']]);
$totalTeachers = $teachersQuery->fetch()['count'];

// Total Parents
$parentsQuery = $db->query("SELECT COUNT(*) as count FROM users WHERE role = 'parent' AND status = 'active' AND school_id = ?", [$_SESSION['school_id']]);
$totalParents = $parentsQuery->fetch()['count'];

// Total Staff
$staffQuery = $db->query("SELECT COUNT(*) as count FROM users WHERE role IN ('exam_officer', 'cashier', 'staff') AND status = 'active' AND school_id = ?", [$_SESSION['school_id']]);
$totalStaff = $staffQuery->fetch()['count'];

// Total Classes
$classesQuery = $db->query("SELECT COUNT(*) as count FROM classes WHERE status = 'active' AND school_id = ?", [$_SESSION['school_id']]);
$totalClasses = $classesQuery->fetch()['count'];

// Total Subjects
$subjectsQuery = $db->query("SELECT COUNT(*) as count FROM subjects WHERE status = 'active' AND school_id = ?", [$_SESSION['school_id']]);
$totalSubjects = $subjectsQuery->fetch()['count'];

// Attendance Today
$attendanceQuery = $db->query("SELECT COUNT(*) as count FROM attendance WHERE date = CURDATE() AND status = 'present'");
$attendanceToday = $attendanceQuery->fetch()['count'];

// Fee Collection This Month
$feesQuery = $db->query("SELECT COALESCE(SUM(amount), 0) as total FROM payments WHERE MONTH(payment_date) = MONTH(CURDATE()) AND YEAR(payment_date) = YEAR(CURDATE()) AND status = 'completed' AND school_id = ?", [$_SESSION['school_id']]);
$feesThisMonth = $feesQuery->fetch()['total'];

// Recent Students (Last 5)
$recentStudentsQuery = $db->query("SELECT id, first_name, last_name, matric_number, created_at FROM users WHERE role = 'student' AND school_id = ? ORDER BY created_at DESC LIMIT 5", [$_SESSION['school_id']]);
$recentStudents = $recentStudentsQuery->fetchAll();

// Get current date and time
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
            <div class="row">
                <div class="col-md-8">
                    <h2>Welcome back, <?php echo $userName; ?>! 👋</h2>
                    <p>Here's what's happening with your school today.</p>
                </div>
                <div class="col-md-4" style="text-align: right;">
                    <div style="background: rgba(255,255,255,0.2); padding: 1rem; border-radius: 10px; display: inline-block;">
                        <div style="font-size: 1.5rem; font-weight: bold; margin-bottom: 0.25rem;">
                            <?php echo $totalStudents; ?>
                        </div>
                        <div style="font-size: 0.875rem; opacity: 0.9;">Total Students</div>
                    </div>
                </div>
            </div>
            
            <div class="welcome-banner-stats" style="margin-top: 1.5rem;">
                <div class="welcome-stat">
                    <div class="welcome-stat-icon">
                        <i class="fas fa-sms"></i>
                    </div>
                    <div class="welcome-stat-info">
                        <p style="margin: 0; font-size: 0.875rem;">SMS System</p>
                        <h4 style="margin: 0;">Available</h4>
                    </div>
                </div>
                
                <div class="welcome-stat">
                    <div class="welcome-stat-icon">
                        <i class="fas fa-calendar"></i>
                    </div>
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
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stats-value"><?php echo $totalStudents; ?></div>
                    <div class="stats-label">Total Students</div>
                    <a href="<?php echo BASE_URL; ?>public/admin/students/index.php" style="display: block; margin-top: 1rem; font-size: 0.875rem;">
                        View All <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="stats-card">
                    <div class="stats-icon" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white;">
                        <i class="fas fa-chalkboard-teacher"></i>
                    </div>
                    <div class="stats-value"><?php echo $totalTeachers; ?></div>
                    <div class="stats-label">Total Teachers</div>
                    <a href="<?php echo BASE_URL; ?>public/admin/teachers/index.php" style="display: block; margin-top: 1rem; font-size: 0.875rem;">
                        View All <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="stats-card">
                    <div class="stats-icon" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); color: white;">
                        <i class="fas fa-user-tie"></i>
                    </div>
                    <div class="stats-value"><?php echo $totalParents; ?></div>
                    <div class="stats-label">Total Parents</div>
                    <a href="<?php echo BASE_URL; ?>public/admin/parents/index.php" style="display: block; margin-top: 1rem; font-size: 0.875rem;">
                        View All <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="stats-card">
                    <div class="stats-icon" style="background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); color: white;">
                        <i class="fas fa-user-friends"></i>
                    </div>
                    <div class="stats-value"><?php echo $totalStaff; ?></div>
                    <div class="stats-label">Total Staff</div>
                    <a href="<?php echo BASE_URL; ?>public/admin/staff/index.php" style="display: block; margin-top: 1rem; font-size: 0.875rem;">
                        View All <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Second Row of Stats -->
        <div class="row">
            <div class="col-md-3">
                <div class="stats-card">
                    <div class="stats-icon" style="background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); color: white;">
                        <i class="fas fa-school"></i>
                    </div>
                    <div class="stats-value"><?php echo $totalClasses; ?></div>
                    <div class="stats-label">Total Classes</div>
                    <a href="<?php echo BASE_URL; ?>public/admin/classes/index.php" style="display: block; margin-top: 1rem; font-size: 0.875rem;">
                        View All <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="stats-card">
                    <div class="stats-icon" style="background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%); color: white;">
                        <i class="fas fa-book"></i>
                    </div>
                    <div class="stats-value"><?php echo $totalSubjects; ?></div>
                    <div class="stats-label">Total Subjects</div>
                    <a href="<?php echo BASE_URL; ?>public/admin/subjects/index.php" style="display: block; margin-top: 1rem; font-size: 0.875rem;">
                        View All <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="stats-card">
                    <div class="stats-icon" style="background: linear-gradient(135deg, #14b8a6 0%, #0d9488 100%); color: white;">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <div class="stats-value"><?php echo $attendanceToday; ?></div>
                    <div class="stats-label">Present Today</div>
                    <a href="<?php echo BASE_URL; ?>public/admin/attendance/index.php" style="display: block; margin-top: 1rem; font-size: 0.875rem;">
                        View Attendance <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="stats-card">
                    <div class="stats-icon" style="background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%); color: white;">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                    <div class="stats-value">₦<?php echo number_format($feesThisMonth, 2); ?></div>
                    <div class="stats-label">Fees This Month</div>
                    <a href="<?php echo BASE_URL; ?>public/admin/fees/index.php" style="display: block; margin-top: 1rem; font-size: 0.875rem;">
                        View Details <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Charts and Recent Activity -->
        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <strong>Recent Students</strong>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Matric Number</th>
                                        <th>Name</th>
                                        <th>Registered On</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($recentStudents)): ?>
                                        <tr>
                                            <td colspan="4" class="text-center">No students registered yet</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($recentStudents as $student): ?>
                                            <tr>
                                                <td><?php echo $student['matric_number']; ?></td>
                                                <td><?php echo $student['first_name'] . ' ' . $student['last_name']; ?></td>
                                                <td><?php echo formatDate($student['created_at']); ?></td>
                                                <td>
                                                    <a href="<?php echo BASE_URL; ?>public/admin/students/view.php?id=<?php echo $student['id']; ?>" class="btn btn-sm btn-info">
                                                        View
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <strong>Quick Actions</strong>
                    </div>
                    <div class="card-body">
                        <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                            <a href="<?php echo BASE_URL; ?>public/admin/students/add.php" class="btn btn-primary">
                                <i class="fas fa-user-plus"></i> Add New Student
                            </a>
                            <a href="<?php echo BASE_URL; ?>public/admin/teachers/add.php" class="btn btn-success">
                                <i class="fas fa-chalkboard-teacher"></i> Add New Teacher
                            </a>
                            <a href="<?php echo BASE_URL; ?>public/admin/classes/add.php" class="btn btn-info">
                                <i class="fas fa-school"></i> Create Class
                            </a>
                            <a href="<?php echo BASE_URL; ?>public/admin/messages/send.php" class="btn btn-warning">
                                <i class="fas fa-envelope"></i> Send Message
                            </a>
                            <a href="<?php echo BASE_URL; ?>public/admin/reports/index.php" class="btn btn-secondary">
                                <i class="fas fa-chart-bar"></i> Generate Report
                            </a>
                        </div>
                    </div>
                </div>
                
                <div class="card" style="margin-top: 1rem;">
                    <div class="card-header">
                        <strong>System Info</strong>
                    </div>
                    <div class="card-body">
                        <div style="font-size: 0.875rem;">
                            <div style="display: flex; justify-content: space-between; padding: 0.5rem 0; border-bottom: 1px solid #e5e7eb;">
                                <span>Academic Year:</span>
                                <strong><?php echo getCurrentAcademicYear(); ?></strong>
                            </div>
                            <div style="display: flex; justify-content: space-between; padding: 0.5rem 0; border-bottom: 1px solid #e5e7eb;">
                                <span>Current Term:</span>
                                <strong>Term <?php echo getCurrentTerm(); ?></strong>
                            </div>
                            <div style="display: flex; justify-content: space-between; padding: 0.5rem 0; border-bottom: 1px solid #e5e7eb;">
                                <span>School:</span>
                                <strong><?php echo $_SESSION['school_name']; ?></strong>
                            </div>
                            <div style="display: flex; justify-content: space-between; padding: 0.5rem 0;">
                                <span>Version:</span>
                                <strong>1.0.0</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include APP_PATH . '/views/shared/footer.php'; ?>
