<?php
/**
 * Mark Attendance
 * CoreSkool School Management System
 */

require_once dirname(dirname(dirname(__DIR__))) . '/config/config.php';

requireAuth();
requireRole('admin');

$pageTitle = 'Mark Attendance';
$db = Database::getInstance();

// Get parameters
$selectedClass = $_GET['class_id'] ?? '';
$selectedDate = $_GET['date'] ?? date('Y-m-d');

if (!$selectedClass) {
    setFlash('danger', 'Please select a class');
    redirect('admin/attendance/index.php');
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
        setFlash('danger', 'Invalid form submission. Please try again.');
    } else {
        $attendanceData = $_POST['attendance'] ?? [];
        $remarks = $_POST['remarks'] ?? [];
        
        if (empty($attendanceData)) {
            setFlash('danger', 'Please mark attendance for at least one student');
        } else {
        try {
            $db->beginTransaction();
            
            foreach ($attendanceData as $studentId => $status) {
                $studentId = (int)$studentId;
                $remark = $remarks[$studentId] ?? '';
                
                // Check if attendance already exists for this student on this date
                $existing = $db->query(
                    "SELECT id FROM attendance WHERE student_id = ? AND date = ?",
                    [$studentId, $selectedDate]
                )->fetch();
                
                if ($existing) {
                    // Update existing attendance
                    $db->query(
                        "UPDATE attendance SET status = ?, remarks = ?, marked_by = ?, class_id = ? WHERE id = ?",
                        [$status, $remark, $_SESSION['user_id'], $selectedClass, $existing['id']]
                    );
                } else {
                    // Insert new attendance
                    $db->query(
                        "INSERT INTO attendance (student_id, class_id, date, status, remarks, marked_by, created_at) 
                         VALUES (?, ?, ?, ?, ?, ?, NOW())",
                        [$studentId, $selectedClass, $selectedDate, $status, $remark, $_SESSION['user_id']]
                    );
                }
            }
            
            $db->commit();
            
            logActivity($_SESSION['user_id'], 'mark_attendance', "Marked attendance for class ID: {$selectedClass} on {$selectedDate}", $_SERVER['REMOTE_ADDR']);
            setFlash('success', 'Attendance marked successfully!');
            redirect("admin/attendance/index.php?class_id={$selectedClass}&date={$selectedDate}");
        } catch (Exception $e) {
            $db->rollBack();
            error_log("Failed to mark attendance: " . $e->getMessage());
            setFlash('danger', 'Failed to mark attendance. Please try again.');
        }
        }
    }
}

// Get class information
$classInfo = $db->query("SELECT name FROM classes WHERE id = ? AND school_id = ?", [$selectedClass, $_SESSION['school_id']])->fetch();

if (!$classInfo) {
    setFlash('danger', 'Invalid class selected');
    redirect('admin/attendance/index.php');
}

// Get students in the class with their current attendance status
$studentsStmt = $db->query(
    "SELECT u.id, u.first_name, u.last_name, u.matric_number,
            a.status as current_status, a.remarks as current_remarks
     FROM users u
     INNER JOIN student_classes sc ON u.id = sc.student_id AND sc.status = 'active'
     LEFT JOIN attendance a ON u.id = a.student_id AND a.date = ?
     WHERE sc.class_id = ? AND u.role = 'student' AND u.status = 'active'
     ORDER BY u.first_name, u.last_name",
    [$selectedDate, $selectedClass]
);
$students = $studentsStmt->fetchAll();

if (count($students) === 0) {
    setFlash('warning', 'No students found in this class');
    redirect('admin/attendance/index.php');
}

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
        
        <div class="card">
            <div class="card-header">
                <strong>Mark Attendance</strong>
            </div>
            
            <div class="card-body">
                <div class="alert alert-info mb-4">
                    <div class="row">
                        <div class="col-md-6">
                            <i class="fas fa-school"></i> 
                            <strong>Class:</strong> <?php echo htmlspecialchars($classInfo['name']); ?>
                        </div>
                        <div class="col-md-6">
                            <i class="fas fa-calendar"></i> 
                            <strong>Date:</strong> <?php echo date('l, F j, Y', strtotime($selectedDate)); ?>
                        </div>
                    </div>
                </div>
                
                <form method="POST" action="">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th style="width: 5%;">#</th>
                                    <th style="width: 15%;">Matric Number</th>
                                    <th style="width: 25%;">Student Name</th>
                                    <th style="width: 35%;">Attendance Status</th>
                                    <th style="width: 20%;">Remarks</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($students as $index => $student): ?>
                                    <tr>
                                        <td><?php echo $index + 1; ?></td>
                                        <td><strong><?php echo htmlspecialchars($student['matric_number']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></td>
                                        <td>
                                            <div class="btn-group btn-group-toggle" data-toggle="buttons">
                                                <label class="btn btn-sm btn-outline-success <?php echo $student['current_status'] === 'present' ? 'active' : ''; ?>">
                                                    <input type="radio" name="attendance[<?php echo $student['id']; ?>]" value="present" 
                                                           <?php echo $student['current_status'] === 'present' ? 'checked' : ''; ?>> 
                                                    <i class="fas fa-check"></i> Present
                                                </label>
                                                <label class="btn btn-sm btn-outline-danger <?php echo $student['current_status'] === 'absent' ? 'active' : ''; ?>">
                                                    <input type="radio" name="attendance[<?php echo $student['id']; ?>]" value="absent" 
                                                           <?php echo $student['current_status'] === 'absent' ? 'checked' : ''; ?>> 
                                                    <i class="fas fa-times"></i> Absent
                                                </label>
                                                <label class="btn btn-sm btn-outline-warning <?php echo $student['current_status'] === 'late' ? 'active' : ''; ?>">
                                                    <input type="radio" name="attendance[<?php echo $student['id']; ?>]" value="late" 
                                                           <?php echo $student['current_status'] === 'late' ? 'checked' : ''; ?>> 
                                                    <i class="fas fa-clock"></i> Late
                                                </label>
                                                <label class="btn btn-sm btn-outline-info <?php echo $student['current_status'] === 'excused' ? 'active' : ''; ?>">
                                                    <input type="radio" name="attendance[<?php echo $student['id']; ?>]" value="excused" 
                                                           <?php echo $student['current_status'] === 'excused' ? 'checked' : ''; ?>> 
                                                    <i class="fas fa-user-check"></i> Excused
                                                </label>
                                            </div>
                                        </td>
                                        <td>
                                            <input type="text" 
                                                   name="remarks[<?php echo $student['id']; ?>]" 
                                                   class="form-control form-control-sm" 
                                                   placeholder="Optional notes..."
                                                   value="<?php echo htmlspecialchars($student['current_remarks'] ?? ''); ?>">
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Quick Actions -->
                    <div class="alert alert-light" style="margin-top: 1rem;">
                        <strong>Quick Actions:</strong>
                        <button type="button" class="btn btn-sm btn-success ml-2" onclick="markAll('present')">
                            <i class="fas fa-check"></i> Mark All Present
                        </button>
                        <button type="button" class="btn btn-sm btn-danger ml-2" onclick="markAll('absent')">
                            <i class="fas fa-times"></i> Mark All Absent
                        </button>
                    </div>
                    
                    <div class="form-group" style="margin-top: 2rem; padding-top: 1rem; border-top: 1px solid var(--border-color);">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-save"></i> Save Attendance
                        </button>
                        <a href="<?php echo BASE_URL; ?>public/admin/attendance/index.php?class_id=<?php echo $selectedClass; ?>&date=<?php echo $selectedDate; ?>" 
                           class="btn btn-secondary btn-lg">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function markAll(status) {
    document.querySelectorAll('input[type="radio"][value="' + status + '"]').forEach(function(radio) {
        radio.checked = true;
        // Update button appearance
        var label = radio.closest('label');
        label.classList.add('active');
        // Remove active from siblings
        var siblings = label.parentElement.querySelectorAll('label');
        siblings.forEach(function(sibling) {
            if (sibling !== label) {
                sibling.classList.remove('active');
            }
        });
    });
}
</script>

<?php include APP_PATH . '/views/shared/footer.php'; ?>
