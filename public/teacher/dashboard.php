<?php
/**
 * Teacher Dashboard
 * CoreSkool School Management System
 */

require_once dirname(dirname(__DIR__)) . '/config/config.php';

requireAuth();
requireRole('teacher');

$pageTitle = 'Teacher Dashboard';
$db = Database::getInstance();
$userId = $_SESSION['user_id'];

// Get teacher's classes
$classesQuery = $db->query("SELECT COUNT(DISTINCT class_id) as count FROM class_teachers WHERE teacher_id = ?", [$userId]);
$totalClasses = $classesQuery->fetch()['count'];

// Get teacher's subjects
$subjectsQuery = $db->query("SELECT COUNT(DISTINCT subject_id) as count FROM class_subjects WHERE teacher_id = ?", [$userId]);
$totalSubjects = $subjectsQuery->fetch()['count'];

// Get total students in teacher's classes
$studentsQuery = $db->query("
    SELECT COUNT(DISTINCT sc.student_id) as count 
    FROM student_classes sc
    INNER JOIN class_teachers ct ON sc.class_id = ct.class_id
    WHERE ct.teacher_id = ? AND sc.status = 'active'
", [$userId]);
$totalStudents = $studentsQuery->fetch()['count'];

// Get today's classes/periods
$todayQuery = $db->query("
    SELECT COUNT(*) as count 
    FROM timetable 
    WHERE teacher_id = ? AND day_of_week = LOWER(DAYNAME(CURDATE()))
", [$userId]);
$classesToday = $todayQuery->fetch()['count'];

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
                    <p>Here's your teaching schedule and class overview for today.</p>
                </div>
                <div class="col-md-4" style="text-align: right;">
                    <div style="background: rgba(255,255,255,0.2); padding: 1rem; border-radius: 10px; display: inline-block;">
                        <div style="font-size: 1.5rem; font-weight: bold; margin-bottom: 0.25rem;">
                            <?php echo $classesToday; ?>
                        </div>
                        <div style="font-size: 0.875rem; opacity: 0.9;">Classes Today</div>
                    </div>
                </div>
            </div>
            
            <div class="welcome-banner-stats" style="margin-top: 1.5rem;">
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
                        <i class="fas fa-school"></i>
                    </div>
                    <div class="stats-value"><?php echo $totalClasses; ?></div>
                    <div class="stats-label">My Classes</div>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="stats-card">
                    <div class="stats-icon" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white;">
                        <i class="fas fa-book"></i>
                    </div>
                    <div class="stats-value"><?php echo $totalSubjects; ?></div>
                    <div class="stats-label">My Subjects</div>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="stats-card">
                    <div class="stats-icon" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); color: white;">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stats-value"><?php echo $totalStudents; ?></div>
                    <div class="stats-label">Total Students</div>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="stats-card">
                    <div class="stats-icon" style="background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); color: white;">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stats-value"><?php echo $classesToday; ?></div>
                    <div class="stats-label">Classes Today</div>
                </div>
            </div>
        </div>
        
        <!-- Today's Schedule and Quick Actions -->
        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <strong>Today's Schedule</strong>
                    </div>
                    <div class="card-body">
                        <?php
                        $scheduleQuery = $db->query("
                            SELECT t.*, c.name as class_name, s.name as subject_name
                            FROM timetable t
                            INNER JOIN classes c ON t.class_id = c.id
                            INNER JOIN subjects s ON t.subject_id = s.id
                            WHERE t.teacher_id = ? AND t.day_of_week = LOWER(DAYNAME(CURDATE()))
                            ORDER BY t.start_time
                        ", [$userId]);
                        $schedule = $scheduleQuery->fetchAll();
                        ?>
                        
                        <?php if (empty($schedule)): ?>
                            <p class="text-center" style="color: #6b7280; padding: 2rem;">
                                No classes scheduled for today
                            </p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Time</th>
                                            <th>Subject</th>
                                            <th>Class</th>
                                            <th>Room</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($schedule as $item): ?>
                                            <tr>
                                                <td><?php echo date('h:i A', strtotime($item['start_time'])); ?> - <?php echo date('h:i A', strtotime($item['end_time'])); ?></td>
                                                <td><?php echo $item['subject_name']; ?></td>
                                                <td><?php echo $item['class_name']; ?></td>
                                                <td><?php echo $item['room'] ?: 'N/A'; ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
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
                            <a href="<?php echo BASE_URL; ?>public/teacher/attendance/mark.php" class="btn btn-primary">
                                <i class="fas fa-calendar-check"></i> Mark Attendance
                            </a>
                            <a href="<?php echo BASE_URL; ?>public/teacher/results/add.php" class="btn btn-success">
                                <i class="fas fa-graduation-cap"></i> Enter Results
                            </a>
                            <a href="<?php echo BASE_URL; ?>public/teacher/students/index.php" class="btn btn-info">
                                <i class="fas fa-users"></i> View Students
                            </a>
                            <a href="<?php echo BASE_URL; ?>public/teacher/timetable/index.php" class="btn btn-warning">
                                <i class="fas fa-clock"></i> View Timetable
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include APP_PATH . '/views/shared/footer.php'; ?>
