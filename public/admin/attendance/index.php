<?php
/**
 * Attendance Management
 * CoreSkool School Management System
 */

require_once dirname(dirname(dirname(__DIR__))) . '/config/config.php';

requireAuth();
requireRole('admin');

$pageTitle = 'Attendance Management';
$db = Database::getInstance();

// Get filters
$selectedDate = $_GET['date'] ?? date('Y-m-d');
$selectedClass = $_GET['class_id'] ?? '';

// Get classes for filter
$classesStmt = $db->query("SELECT id, name FROM classes WHERE school_id = ? AND status = 'active' ORDER BY name", [$_SESSION['school_id']]);
$classes = $classesStmt->fetchAll();

// Get attendance data if a class is selected
$attendanceData = [];
$students = [];

if ($selectedClass) {
    // Get students in the selected class
    $studentsStmt = $db->query(
        "SELECT u.id, u.first_name, u.last_name, u.matric_number,
                a.id as attendance_id, a.status, a.remarks
         FROM users u
         INNER JOIN student_classes sc ON u.id = sc.student_id AND sc.status = 'active'
         LEFT JOIN attendance a ON u.id = a.student_id AND a.date = ? AND a.class_id = ?
         WHERE sc.class_id = ? AND u.role = 'student' AND u.status = 'active'
         ORDER BY u.first_name, u.last_name",
        [$selectedDate, $selectedClass, $selectedClass]
    );
    $students = $studentsStmt->fetchAll();
    
    // Get class name
    $classInfo = $db->query("SELECT name FROM classes WHERE id = ?", [$selectedClass])->fetch();
}

// Calculate statistics if data exists
$totalStudents = count($students);
$presentCount = 0;
$absentCount = 0;
$lateCount = 0;
$excusedCount = 0;

foreach ($students as $student) {
    if ($student['status'] === 'present') $presentCount++;
    elseif ($student['status'] === 'absent') $absentCount++;
    elseif ($student['status'] === 'late') $lateCount++;
    elseif ($student['status'] === 'excused') $excusedCount++;
}

$attendancePercentage = $totalStudents > 0 ? round(($presentCount / $totalStudents) * 100, 1) : 0;

include APP_PATH . '/views/shared/header.php';
?>

<div class="main-content">
    <?php include APP_PATH . '/views/shared/sidebar.php'; ?>
    <?php include APP_PATH . '/views/shared/topbar.php'; ?>
    
    <div class="content-area">
        <?php 
        $flash = getFlash();
        if ($flash): 
        ?>
            <div class="alert alert-<?php echo $flash['type']; ?>">
                <?php echo $flash['message']; ?>
            </div>
        <?php endif; ?>
        
        <!-- Statistics Cards -->
        <?php if ($selectedClass && $totalStudents > 0): ?>
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="stat-card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                    <div class="stat-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-details">
                        <h3><?php echo $totalStudents; ?></h3>
                        <p>Total Students</p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="stat-card" style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);">
                    <div class="stat-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-details">
                        <h3><?php echo $presentCount; ?></h3>
                        <p>Present (<?php echo $attendancePercentage; ?>%)</p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="stat-card" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                    <div class="stat-icon">
                        <i class="fas fa-times-circle"></i>
                    </div>
                    <div class="stat-details">
                        <h3><?php echo $absentCount; ?></h3>
                        <p>Absent</p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="stat-card" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);">
                    <div class="stat-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-details">
                        <h3><?php echo $lateCount; ?></h3>
                        <p>Late</p>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <div class="card">
            <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                <strong>View Attendance</strong>
                <?php if ($selectedClass): ?>
                <a href="<?php echo BASE_URL; ?>public/admin/attendance/mark.php?class_id=<?php echo $selectedClass; ?>&date=<?php echo $selectedDate; ?>" class="btn btn-primary">
                    <i class="fas fa-edit"></i> Mark Attendance
                </a>
                <?php endif; ?>
            </div>
            
            <div class="card-body">
                <!-- Filter Form -->
                <form method="GET" class="mb-4">
                    <div class="row">
                        <div class="col-md-5">
                            <div class="form-group mb-0">
                                <label class="form-label">Select Date</label>
                                <input type="date" name="date" class="form-control" value="<?php echo $selectedDate; ?>" required>
                            </div>
                        </div>
                        
                        <div class="col-md-5">
                            <div class="form-group mb-0">
                                <label class="form-label">Select Class</label>
                                <select name="class_id" class="form-control" required>
                                    <option value="">-- Select Class --</option>
                                    <?php foreach ($classes as $class): ?>
                                        <option value="<?php echo $class['id']; ?>" <?php echo $selectedClass == $class['id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($class['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        
                        <div class="col-md-2">
                            <label class="form-label" style="visibility: hidden;">Action</label>
                            <button type="submit" class="btn btn-primary btn-block">
                                <i class="fas fa-filter"></i> View
                            </button>
                        </div>
                    </div>
                </form>
                
                <!-- Attendance Table -->
                <?php if ($selectedClass && count($students) > 0): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> 
                        Viewing attendance for <strong><?php echo htmlspecialchars($classInfo['name']); ?></strong> on 
                        <strong><?php echo date('l, F j, Y', strtotime($selectedDate)); ?></strong>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Matric Number</th>
                                    <th>Student Name</th>
                                    <th>Status</th>
                                    <th>Remarks</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($students as $index => $student): ?>
                                    <tr>
                                        <td><?php echo $index + 1; ?></td>
                                        <td><strong><?php echo htmlspecialchars($student['matric_number']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></td>
                                        <td>
                                            <?php if ($student['status']): ?>
                                                <?php
                                                $statusColors = [
                                                    'present' => 'success',
                                                    'absent' => 'danger',
                                                    'late' => 'warning',
                                                    'excused' => 'info'
                                                ];
                                                $badgeColor = $statusColors[$student['status']] ?? 'secondary';
                                                ?>
                                                <span class="badge badge-<?php echo $badgeColor; ?>">
                                                    <?php echo ucfirst($student['status']); ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="badge badge-secondary">Not Marked</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($student['remarks']): ?>
                                                <?php echo htmlspecialchars($student['remarks']); ?>
                                            <?php else: ?>
                                                <span style="color: #999;">-</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php elseif ($selectedClass): ?>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i> No students found in this class.
                    </div>
                <?php else: ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> Please select a date and class to view attendance records.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include APP_PATH . '/views/shared/footer.php'; ?>
