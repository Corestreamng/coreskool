<?php
/**
 * Timetable - Student
 * CoreSkool School Management System
 */

require_once dirname(dirname(dirname(__DIR__))) . '/config/config.php';

requireAuth();
requireRole('student');

$pageTitle = 'My Timetable';
$db = Database::getInstance();
$userId = $_SESSION['user_id'];

// Get student's class
$classQuery = $db->query("SELECT class_id FROM student_classes WHERE student_id = ? AND status = 'active' LIMIT 1", [$userId]);
$studentClass = $classQuery->fetch();
$classId = $studentClass['class_id'] ?? null;

$timetable = [];
if ($classId) {
    $timetableQuery = $db->query("
        SELECT t.*, s.name as subject_name, u.first_name, u.last_name
        FROM timetable t
        INNER JOIN subjects s ON t.subject_id = s.id
        INNER JOIN users u ON t.teacher_id = u.id
        WHERE t.class_id = ?
        ORDER BY 
            FIELD(t.day_of_week, 'monday', 'tuesday', 'wednesday', 'thursday', 'friday'),
            t.start_time
    ", [$classId]);
    $timetable = $timetableQuery->fetchAll();
}

include APP_PATH . '/views/shared/header.php';
?>

<div class="main-content">
    <?php include APP_PATH . '/views/shared/sidebar.php'; ?>
    <?php include APP_PATH . '/views/shared/topbar.php'; ?>
    
    <div class="content-area">
        <div class="page-header" style="margin-bottom: 2rem;">
            <h2>My Class Timetable</h2>
            <p style="color: #6b7280;">View your weekly class schedule</p>
        </div>

        <?php if (!$classId): ?>
            <div class="card">
                <div class="card-body" style="text-align: center; padding: 3rem;">
                    <i class="fas fa-exclamation-triangle" style="font-size: 4rem; color: #f59e0b; margin-bottom: 1rem;"></i>
                    <h3 style="color: #6b7280;">Not Assigned to Class</h3>
                    <p style="color: #9ca3af;">You haven't been assigned to a class yet. Please contact the administrator.</p>
                </div>
            </div>
        <?php elseif (empty($timetable)): ?>
            <div class="card">
                <div class="card-body" style="text-align: center; padding: 3rem;">
                    <i class="fas fa-calendar-times" style="font-size: 4rem; color: #e5e7eb; margin-bottom: 1rem;"></i>
                    <h3 style="color: #6b7280;">No Timetable Available</h3>
                    <p style="color: #9ca3af;">Your class timetable hasn't been created yet</p>
                </div>
            </div>
        <?php else: ?>
            <div class="card">
                <div class="card-header"><strong>Weekly Schedule</strong></div>
                <div class="card-body">
                    <?php
                    $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'];
                    $grouped = [];
                    foreach ($timetable as $period) {
                        $grouped[$period['day_of_week']][] = $period;
                    }
                    ?>
                    
                    <?php foreach ($days as $day): ?>
                        <h4 style="text-transform: capitalize; margin-top: 1.5rem; margin-bottom: 1rem; color: #667eea;">
                            <i class="fas fa-calendar-day"></i> <?php echo $day; ?>
                        </h4>
                        <?php if (isset($grouped[$day])): ?>
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Time</th>
                                            <th>Subject</th>
                                            <th>Teacher</th>
                                            <th>Room</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($grouped[$day] as $period): ?>
                                            <tr>
                                                <td>
                                                    <i class="fas fa-clock"></i>
                                                    <?php echo date('h:i A', strtotime($period['start_time'])); ?> - 
                                                    <?php echo date('h:i A', strtotime($period['end_time'])); ?>
                                                </td>
                                                <td><strong><?php echo htmlspecialchars($period['subject_name']); ?></strong></td>
                                                <td><?php echo htmlspecialchars($period['first_name'] . ' ' . $period['last_name']); ?></td>
                                                <td><?php echo htmlspecialchars($period['room'] ?? 'N/A'); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <p style="color: #9ca3af; padding: 1rem; background-color: #f9fafb; border-radius: 6px;">
                                No classes scheduled for this day
                            </p>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include APP_PATH . '/views/shared/footer.php'; ?>
