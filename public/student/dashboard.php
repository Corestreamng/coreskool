<?php
/**
 * Student Dashboard
 * CoreSkool School Management System
 */

require_once dirname(dirname(__DIR__)) . '/config/config.php';

requireAuth();
requireRole('student');

$pageTitle = 'Student Dashboard';
$db = Database::getInstance();
$userId = $_SESSION['user_id'];

// Get student's class
$classQuery = $db->query("
    SELECT c.name as class_name, c.id as class_id
    FROM student_classes sc
    INNER JOIN classes c ON sc.class_id = c.id
    WHERE sc.student_id = ? AND sc.status = 'active'
    LIMIT 1
", [$userId]);
$studentClass = $classQuery->fetch();
$className = $studentClass['class_name'] ?? 'Not Assigned';
$classId = $studentClass['class_id'] ?? null;

// Get total subjects
$subjectsQuery = $db->query("
    SELECT COUNT(*) as count 
    FROM class_subjects 
    WHERE class_id = ?
", [$classId]);
$totalSubjects = $subjectsQuery->fetch()['count'];

// Get attendance percentage
$attendanceQuery = $db->query("
    SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present
    FROM attendance 
    WHERE student_id = ? AND MONTH(date) = MONTH(CURDATE())
", [$userId]);
$attendance = $attendanceQuery->fetch();
$attendancePercentage = $attendance['total'] > 0 ? round(($attendance['present'] / $attendance['total']) * 100, 1) : 0;

// Get pending CBT exams
$cbtQuery = $db->query("
    SELECT COUNT(*) as count
    FROM cbt_exams ce
    WHERE ce.class_id = ? AND ce.status = 'published'
    AND ce.id NOT IN (SELECT cbt_exam_id FROM cbt_attempts WHERE student_id = ?)
", [$classId, $userId]);
$pendingCBT = $cbtQuery->fetch()['count'];

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
                    <p>Your academic dashboard and performance overview.</p>
                </div>
                <div class="col-md-4" style="text-align: right;">
                    <div style="background: rgba(255,255,255,0.2); padding: 1rem; border-radius: 10px; display: inline-block;">
                        <div style="font-size: 1.5rem; font-weight: bold; margin-bottom: 0.25rem;">
                            <?php echo $attendancePercentage; ?>%
                        </div>
                        <div style="font-size: 0.875rem; opacity: 0.9;">Attendance Rate</div>
                    </div>
                </div>
            </div>
            
            <div class="welcome-banner-stats" style="margin-top: 1.5rem;">
                <div class="welcome-stat">
                    <div class="welcome-stat-icon">
                        <i class="fas fa-school"></i>
                    </div>
                    <div class="welcome-stat-info">
                        <p style="margin: 0; font-size: 0.875rem;">My Class</p>
                        <h4 style="margin: 0;"><?php echo $className; ?></h4>
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
                        <i class="fas fa-book"></i>
                    </div>
                    <div class="stats-value"><?php echo $totalSubjects; ?></div>
                    <div class="stats-label">My Subjects</div>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="stats-card">
                    <div class="stats-icon" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white;">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <div class="stats-value"><?php echo $attendancePercentage; ?>%</div>
                    <div class="stats-label">Attendance Rate</div>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="stats-card">
                    <div class="stats-icon" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); color: white;">
                        <i class="fas fa-laptop"></i>
                    </div>
                    <div class="stats-value"><?php echo $pendingCBT; ?></div>
                    <div class="stats-label">Pending CBT Tests</div>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="stats-card">
                    <div class="stats-icon" style="background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); color: white;">
                        <i class="fas fa-graduation-cap"></i>
                    </div>
                    <div class="stats-value">--</div>
                    <div class="stats-label">Current Position</div>
                </div>
            </div>
        </div>
        
        <!-- Content Area -->
        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <strong>Today's Schedule</strong>
                    </div>
                    <div class="card-body">
                        <?php
                        $scheduleQuery = $db->query("
                            SELECT t.*, s.name as subject_name, u.first_name, u.last_name
                            FROM timetable t
                            INNER JOIN subjects s ON t.subject_id = s.id
                            INNER JOIN users u ON t.teacher_id = u.id
                            WHERE t.class_id = ? AND t.day_of_week = LOWER(DAYNAME(CURDATE()))
                            ORDER BY t.start_time
                        ", [$classId]);
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
                                            <th>Teacher</th>
                                            <th>Room</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($schedule as $item): ?>
                                            <tr>
                                                <td><?php echo date('h:i A', strtotime($item['start_time'])); ?> - <?php echo date('h:i A', strtotime($item['end_time'])); ?></td>
                                                <td><?php echo $item['subject_name']; ?></td>
                                                <td><?php echo $item['first_name'] . ' ' . $item['last_name']; ?></td>
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
                            <a href="<?php echo BASE_URL; ?>public/student/results/index.php" class="btn btn-primary">
                                <i class="fas fa-graduation-cap"></i> View My Results
                            </a>
                            <a href="<?php echo BASE_URL; ?>public/student/cbt/index.php" class="btn btn-success">
                                <i class="fas fa-laptop"></i> Take CBT Test
                            </a>
                            <a href="<?php echo BASE_URL; ?>public/student/attendance/index.php" class="btn btn-info">
                                <i class="fas fa-calendar-check"></i> View Attendance
                            </a>
                            <a href="<?php echo BASE_URL; ?>public/student/timetable/index.php" class="btn btn-warning">
                                <i class="fas fa-clock"></i> View Timetable
                            </a>
                        </div>
                    </div>
                </div>
                
                <div class="card" style="margin-top: 1rem;">
                    <div class="card-header">
                        <strong>My Information</strong>
                    </div>
                    <div class="card-body">
                        <?php
                        $studentInfo = $db->query("SELECT * FROM users WHERE id = ?", [$userId])->fetch();
                        ?>
                        <div style="font-size: 0.875rem;">
                            <div style="display: flex; justify-content: space-between; padding: 0.5rem 0; border-bottom: 1px solid #e5e7eb;">
                                <span>Matric Number:</span>
                                <strong><?php echo $studentInfo['matric_number']; ?></strong>
                            </div>
                            <div style="display: flex; justify-content: space-between; padding: 0.5rem 0; border-bottom: 1px solid #e5e7eb;">
                                <span>Class:</span>
                                <strong><?php echo $className; ?></strong>
                            </div>
                            <div style="display: flex; justify-content: space-between; padding: 0.5rem 0; border-bottom: 1px solid #e5e7eb;">
                                <span>Email:</span>
                                <strong><?php echo $studentInfo['email'] ?: 'N/A'; ?></strong>
                            </div>
                            <div style="display: flex; justify-content: space-between; padding: 0.5rem 0;">
                                <span>Phone:</span>
                                <strong><?php echo $studentInfo['phone'] ?: 'N/A'; ?></strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include APP_PATH . '/views/shared/footer.php'; ?>
