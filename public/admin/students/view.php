<?php
/**
 * View Student Details
 * CoreSkool School Management System
 */

require_once dirname(dirname(dirname(__DIR__))) . '/config/config.php';

requireAuth();
requireRole('admin');

$pageTitle = 'Student Details';
$db = Database::getInstance();

$studentId = (int)($_GET['id'] ?? 0);

if ($studentId <= 0) {
    setFlash('danger', 'Invalid student ID');
    redirect('admin/students/index.php');
}

// Get student details with class information
$sql = "SELECT u.*, 
        c.name as class_name, c.id as class_id,
        ay.name as academic_year
        FROM users u
        LEFT JOIN student_classes sc ON u.id = sc.student_id AND sc.status = 'active'
        LEFT JOIN classes c ON sc.class_id = c.id
        LEFT JOIN academic_years ay ON sc.academic_year_id = ay.id
        WHERE u.id = ? AND u.role = 'student' AND u.school_id = ?";

$stmt = $db->query($sql, [$studentId, $_SESSION['school_id']]);
$student = $stmt->fetch();

if (!$student) {
    setFlash('danger', 'Student not found');
    redirect('admin/students/index.php');
}

// Get student's results summary
$resultsSql = "SELECT COUNT(*) as total_exams, 
               AVG(r.total_score) as average_score,
               MAX(r.total_score) as highest_score,
               MIN(r.total_score) as lowest_score
               FROM results r
               WHERE r.student_id = ? AND r.status = 'published'";
$resultsStmt = $db->query($resultsSql, [$studentId]);
$resultsData = $resultsStmt->fetch();

// Get attendance summary
$attendanceSql = "SELECT 
                  COUNT(*) as total_days,
                  SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present_days,
                  SUM(CASE WHEN status = 'absent' THEN 1 ELSE 0 END) as absent_days,
                  SUM(CASE WHEN status = 'late' THEN 1 ELSE 0 END) as late_days
                  FROM attendance
                  WHERE student_id = ?";
$attendanceStmt = $db->query($attendanceSql, [$studentId]);
$attendanceData = $attendanceStmt->fetch();

// Get recent attendance (last 10 days)
$recentAttendanceSql = "SELECT date, status, remarks 
                        FROM attendance 
                        WHERE student_id = ? 
                        ORDER BY date DESC 
                        LIMIT 10";
$recentAttendanceStmt = $db->query($recentAttendanceSql, [$studentId]);
$recentAttendance = $recentAttendanceStmt->fetchAll();

// Get parent information
$parentSql = "SELECT u.id, u.first_name, u.last_name, u.email, u.phone, ps.relationship
              FROM parent_student ps
              INNER JOIN users u ON ps.parent_id = u.id
              WHERE ps.student_id = ? AND u.status = 'active'";
$parentStmt = $db->query($parentSql, [$studentId]);
$parents = $parentStmt->fetchAll();

include APP_PATH . '/views/shared/header.php';
?>

<div class="main-content">
    <?php include APP_PATH . '/views/shared/sidebar.php'; ?>
    <?php include APP_PATH . '/views/shared/topbar.php'; ?>
    
    <div class="content-area">
        <div class="page-header">
            <h2>Student Details</h2>
            <div>
                <a href="<?php echo BASE_URL; ?>public/admin/students/edit.php?id=<?php echo $studentId; ?>" class="btn btn-primary">
                    <i class="fas fa-edit"></i> Edit Student
                </a>
                <a href="<?php echo BASE_URL; ?>public/admin/students/index.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to List
                </a>
            </div>
        </div>

        <div class="row">
            <!-- Student Profile Card -->
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body" style="text-align: center;">
                        <img src="<?php echo getAvatarUrl($student['avatar']); ?>" 
                             alt="Student Avatar" 
                             style="width: 150px; height: 150px; border-radius: 50%; object-fit: cover; margin-bottom: 1rem;">
                        <h3><?php echo $student['first_name'] . ' ' . $student['last_name']; ?></h3>
                        <p style="color: #6b7280; margin-bottom: 0.5rem;">
                            <strong>Matric Number:</strong> <?php echo $student['matric_number']; ?>
                        </p>
                        <p style="color: #6b7280; margin-bottom: 0.5rem;">
                            <strong>Class:</strong> <?php echo $student['class_name'] ?? 'Not Assigned'; ?>
                        </p>
                        <span class="badge badge-<?php echo $student['status'] === 'active' ? 'success' : 'danger'; ?>" 
                              style="padding: 0.5rem 1rem; font-size: 0.875rem;">
                            <?php echo ucfirst($student['status']); ?>
                        </span>
                    </div>
                </div>

                <!-- Quick Stats -->
                <div class="card" style="margin-top: 1rem;">
                    <div class="card-header"><strong>Quick Stats</strong></div>
                    <div class="card-body">
                        <div style="display: flex; justify-content: space-between; padding: 0.5rem 0; border-bottom: 1px solid var(--border-color);">
                            <span>Attendance Rate:</span>
                            <strong>
                                <?php 
                                $attendanceRate = $attendanceData['total_days'] > 0 
                                    ? round(($attendanceData['present_days'] / $attendanceData['total_days']) * 100, 1) 
                                    : 0;
                                echo $attendanceRate . '%';
                                ?>
                            </strong>
                        </div>
                        <div style="display: flex; justify-content: space-between; padding: 0.5rem 0; border-bottom: 1px solid var(--border-color);">
                            <span>Total Exams:</span>
                            <strong><?php echo $resultsData['total_exams'] ?? 0; ?></strong>
                        </div>
                        <div style="display: flex; justify-content: space-between; padding: 0.5rem 0; border-bottom: 1px solid var(--border-color);">
                            <span>Average Score:</span>
                            <strong><?php echo $resultsData['average_score'] ? round($resultsData['average_score'], 2) : 'N/A'; ?></strong>
                        </div>
                        <div style="display: flex; justify-content: space-between; padding: 0.5rem 0;">
                            <span>Registered On:</span>
                            <strong><?php echo formatDate($student['created_at']); ?></strong>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Student Information -->
            <div class="col-md-8">
                <!-- Personal Information -->
                <div class="card">
                    <div class="card-header"><strong>Personal Information</strong></div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>First Name:</strong> <?php echo $student['first_name']; ?></p>
                                <p><strong>Last Name:</strong> <?php echo $student['last_name']; ?></p>
                                <p><strong>Other Names:</strong> <?php echo $student['other_names'] ?: 'N/A'; ?></p>
                                <p><strong>Gender:</strong> <?php echo ucfirst($student['gender']); ?></p>
                                <p><strong>Date of Birth:</strong> <?php echo formatDate($student['date_of_birth']); ?></p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Email:</strong> <?php echo $student['email'] ?: 'N/A'; ?></p>
                                <p><strong>Phone:</strong> <?php echo $student['phone'] ?: 'N/A'; ?></p>
                                <p><strong>Address:</strong> <?php echo $student['address'] ?: 'N/A'; ?></p>
                                <p><strong>City:</strong> <?php echo $student['city'] ?: 'N/A'; ?></p>
                                <p><strong>State:</strong> <?php echo $student['state'] ?: 'N/A'; ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Parent/Guardian Information -->
                <div class="card" style="margin-top: 1rem;">
                    <div class="card-header"><strong>Parent/Guardian Information</strong></div>
                    <div class="card-body">
                        <?php if (empty($parents)): ?>
                            <p class="text-muted">No parent/guardian linked to this student.</p>
                            <a href="<?php echo BASE_URL; ?>public/admin/parents/add.php?student_id=<?php echo $studentId; ?>" class="btn btn-sm btn-primary">
                                <i class="fas fa-plus"></i> Link Parent
                            </a>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Relationship</th>
                                            <th>Email</th>
                                            <th>Phone</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($parents as $parent): ?>
                                            <tr>
                                                <td><?php echo $parent['first_name'] . ' ' . $parent['last_name']; ?></td>
                                                <td><?php echo ucfirst($parent['relationship']); ?></td>
                                                <td><?php echo $parent['email']; ?></td>
                                                <td><?php echo $parent['phone']; ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Recent Attendance -->
                <div class="card" style="margin-top: 1rem;">
                    <div class="card-header"><strong>Recent Attendance</strong></div>
                    <div class="card-body">
                        <?php if (empty($recentAttendance)): ?>
                            <p class="text-muted">No attendance records found.</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Status</th>
                                            <th>Remarks</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recentAttendance as $attendance): ?>
                                            <tr>
                                                <td><?php echo formatDate($attendance['date']); ?></td>
                                                <td>
                                                    <span class="badge badge-<?php 
                                                        echo $attendance['status'] === 'present' ? 'success' : 
                                                            ($attendance['status'] === 'absent' ? 'danger' : 'warning'); 
                                                    ?>">
                                                        <?php echo ucfirst($attendance['status']); ?>
                                                    </span>
                                                </td>
                                                <td><?php echo $attendance['remarks'] ?: '-'; ?></td>
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
    </div>
</div>

<?php include APP_PATH . '/views/shared/footer.php'; ?>
