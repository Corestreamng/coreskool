<?php
/**
 * My Attendance - Student
 * CoreSkool School Management System
 */

require_once dirname(dirname(dirname(__DIR__))) . '/config/config.php';

requireAuth();
requireRole('student');

$pageTitle = 'My Attendance';
$db = Database::getInstance();
$userId = $_SESSION['user_id'];

// Get attendance records
$attendanceQuery = $db->query("
    SELECT * FROM attendance
    WHERE student_id = ?
    ORDER BY date DESC
    LIMIT 100
", [$userId]);
$attendance = $attendanceQuery->fetchAll();

// Calculate statistics
$statsQuery = $db->query("
    SELECT 
        COUNT(*) as total_days,
        SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present_days,
        SUM(CASE WHEN status = 'absent' THEN 1 ELSE 0 END) as absent_days,
        SUM(CASE WHEN status = 'late' THEN 1 ELSE 0 END) as late_days
    FROM attendance
    WHERE student_id = ?
", [$userId]);
$stats = $statsQuery->fetch();
$attendanceRate = $stats['total_days'] > 0 ? round(($stats['present_days'] / $stats['total_days']) * 100, 1) : 0;

include APP_PATH . '/views/shared/header.php';
?>

<div class="main-content">
    <?php include APP_PATH . '/views/shared/sidebar.php'; ?>
    <?php include APP_PATH . '/views/shared/topbar.php'; ?>
    
    <div class="content-area">
        <div class="page-header" style="margin-bottom: 2rem;">
            <h2>My Attendance</h2>
            <p style="color: #6b7280;">View your attendance record and statistics</p>
        </div>

        <!-- Statistics -->
        <div class="row" style="margin-bottom: 2rem;">
            <div class="col-md-3">
                <div class="stats-card">
                    <div class="stats-icon" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white;">
                        <i class="fas fa-percentage"></i>
                    </div>
                    <div class="stats-value"><?php echo $attendanceRate; ?>%</div>
                    <div class="stats-label">Attendance Rate</div>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="stats-card">
                    <div class="stats-icon" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stats-value"><?php echo $stats['present_days'] ?? 0; ?></div>
                    <div class="stats-label">Present Days</div>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="stats-card">
                    <div class="stats-icon" style="background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); color: white;">
                        <i class="fas fa-times-circle"></i>
                    </div>
                    <div class="stats-value"><?php echo $stats['absent_days'] ?? 0; ?></div>
                    <div class="stats-label">Absent Days</div>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="stats-card">
                    <div class="stats-icon" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); color: white;">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stats-value"><?php echo $stats['late_days'] ?? 0; ?></div>
                    <div class="stats-label">Late Days</div>
                </div>
            </div>
        </div>

        <!-- Attendance Records -->
        <div class="card">
            <div class="card-header"><strong>Attendance History</strong></div>
            <div class="card-body">
                <?php if (empty($attendance)): ?>
                    <div style="text-align: center; padding: 3rem;">
                        <i class="fas fa-calendar-check" style="font-size: 4rem; color: #e5e7eb; margin-bottom: 1rem;"></i>
                        <h3 style="color: #6b7280;">No Attendance Records</h3>
                        <p style="color: #9ca3af;">Your attendance records will appear here</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Day</th>
                                    <th>Status</th>
                                    <th>Remarks</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($attendance as $record): ?>
                                    <tr>
                                        <td><?php echo date('M d, Y', strtotime($record['date'])); ?></td>
                                        <td><?php echo date('l', strtotime($record['date'])); ?></td>
                                        <td>
                                            <?php
                                            $statusColors = [
                                                'present' => 'success',
                                                'absent' => 'danger',
                                                'late' => 'warning',
                                                'excused' => 'info'
                                            ];
                                            $color = $statusColors[$record['status']] ?? 'secondary';
                                            ?>
                                            <span class="badge badge-<?php echo $color; ?>">
                                                <?php echo ucfirst($record['status']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo htmlspecialchars($record['remarks'] ?? '-'); ?></td>
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
