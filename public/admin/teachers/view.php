<?php
/**
 * View Teacher Details
 * CoreSkool School Management System
 */

require_once dirname(dirname(dirname(__DIR__))) . '/config/config.php';

requireAuth();
requireRole('admin');

$pageTitle = 'Teacher Details';
$db = Database::getInstance();

$teacherId = (int)($_GET['id'] ?? 0);

if ($teacherId <= 0) {
    setFlash('danger', 'Invalid teacher ID');
    redirect('admin/teachers/index.php');
}

// Get teacher details
$sql = "SELECT u.* FROM users u WHERE u.id = ? AND u.role = 'teacher' AND u.school_id = ?";
$stmt = $db->query($sql, [$teacherId, $_SESSION['school_id']]);
$teacher = $stmt->fetch();

if (!$teacher) {
    setFlash('danger', 'Teacher not found');
    redirect('admin/teachers/index.php');
}

// Get classes taught
$classesSql = "SELECT DISTINCT c.id, c.name, ct.is_primary
               FROM class_teachers ct
               INNER JOIN classes c ON ct.class_id = c.id
               WHERE ct.teacher_id = ? AND c.status = 'active'
               ORDER BY c.name";
$classesStmt = $db->query($classesSql, [$teacherId]);
$classes = $classesStmt->fetchAll();

// Get subjects taught
$subjectsSql = "SELECT DISTINCT s.id, s.name, s.code, c.name as class_name
                FROM class_subjects cs
                INNER JOIN subjects s ON cs.subject_id = s.id
                INNER JOIN classes c ON cs.class_id = c.id
                WHERE cs.teacher_id = ? AND s.status = 'active'
                ORDER BY s.name";
$subjectsStmt = $db->query($subjectsSql, [$teacherId]);
$subjects = $subjectsStmt->fetchAll();

// Get total students taught
$studentsSql = "SELECT COUNT(DISTINCT sc.student_id) as total
                FROM student_classes sc
                INNER JOIN class_teachers ct ON sc.class_id = ct.class_id
                WHERE ct.teacher_id = ? AND sc.status = 'active'";
$studentsStmt = $db->query($studentsSql, [$teacherId]);
$studentsData = $studentsStmt->fetch();

include APP_PATH . '/views/shared/header.php';
?>

<div class="main-content">
    <?php include APP_PATH . '/views/shared/sidebar.php'; ?>
    <?php include APP_PATH . '/views/shared/topbar.php'; ?>
    
    <div class="content-area">
        <div class="page-header">
            <h2>Teacher Details</h2>
            <div>
                <a href="<?php echo BASE_URL; ?>public/admin/teachers/edit.php?id=<?php echo $teacherId; ?>" class="btn btn-primary">
                    <i class="fas fa-edit"></i> Edit Teacher
                </a>
                <a href="<?php echo BASE_URL; ?>public/admin/teachers/index.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to List
                </a>
            </div>
        </div>

        <div class="row">
            <!-- Teacher Profile Card -->
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body" style="text-align: center;">
                        <img src="<?php echo getAvatarUrl($teacher['avatar']); ?>" 
                             alt="Teacher Avatar" 
                             style="width: 150px; height: 150px; border-radius: 50%; object-fit: cover; margin-bottom: 1rem;">
                        <h3><?php echo $teacher['first_name'] . ' ' . $teacher['last_name']; ?></h3>
                        <p style="color: #6b7280; margin-bottom: 0.5rem;">
                            <strong>Teacher</strong>
                        </p>
                        <span class="badge badge-<?php echo $teacher['status'] === 'active' ? 'success' : 'danger'; ?>" 
                              style="padding: 0.5rem 1rem; font-size: 0.875rem;">
                            <?php echo ucfirst($teacher['status']); ?>
                        </span>
                    </div>
                </div>

                <!-- Quick Stats -->
                <div class="card" style="margin-top: 1rem;">
                    <div class="card-header"><strong>Quick Stats</strong></div>
                    <div class="card-body">
                        <div style="display: flex; justify-content: space-between; padding: 0.5rem 0; border-bottom: 1px solid var(--border-color);">
                            <span>Classes:</span>
                            <strong><?php echo count($classes); ?></strong>
                        </div>
                        <div style="display: flex; justify-content: space-between; padding: 0.5rem 0; border-bottom: 1px solid var(--border-color);">
                            <span>Subjects:</span>
                            <strong><?php echo count($subjects); ?></strong>
                        </div>
                        <div style="display: flex; justify-content: space-between; padding: 0.5rem 0; border-bottom: 1px solid var(--border-color);">
                            <span>Total Students:</span>
                            <strong><?php echo $studentsData['total'] ?? 0; ?></strong>
                        </div>
                        <div style="display: flex; justify-content: space-between; padding: 0.5rem 0;">
                            <span>Joined:</span>
                            <strong><?php echo formatDate($teacher['created_at']); ?></strong>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Teacher Information -->
            <div class="col-md-8">
                <!-- Personal Information -->
                <div class="card">
                    <div class="card-header"><strong>Personal Information</strong></div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>First Name:</strong> <?php echo $teacher['first_name']; ?></p>
                                <p><strong>Last Name:</strong> <?php echo $teacher['last_name']; ?></p>
                                <p><strong>Other Names:</strong> <?php echo $teacher['other_names'] ?: 'N/A'; ?></p>
                                <p><strong>Gender:</strong> <?php echo ucfirst($teacher['gender']); ?></p>
                                <p><strong>Date of Birth:</strong> <?php echo $teacher['date_of_birth'] ? formatDate($teacher['date_of_birth']) : 'N/A'; ?></p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Email:</strong> <?php echo $teacher['email']; ?></p>
                                <p><strong>Phone:</strong> <?php echo $teacher['phone']; ?></p>
                                <p><strong>Address:</strong> <?php echo $teacher['address'] ?: 'N/A'; ?></p>
                                <p><strong>City:</strong> <?php echo $teacher['city'] ?: 'N/A'; ?></p>
                                <p><strong>State:</strong> <?php echo $teacher['state'] ?: 'N/A'; ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Classes Assigned -->
                <div class="card" style="margin-top: 1rem;">
                    <div class="card-header"><strong>Classes Assigned</strong></div>
                    <div class="card-body">
                        <?php if (empty($classes)): ?>
                            <p class="text-muted">No classes assigned yet.</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Class Name</th>
                                            <th>Role</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($classes as $class): ?>
                                            <tr>
                                                <td><?php echo $class['name']; ?></td>
                                                <td>
                                                    <?php if ($class['is_primary']): ?>
                                                        <span class="badge badge-primary">Class Teacher</span>
                                                    <?php else: ?>
                                                        <span class="badge badge-secondary">Subject Teacher</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Subjects Taught -->
                <div class="card" style="margin-top: 1rem;">
                    <div class="card-header"><strong>Subjects Taught</strong></div>
                    <div class="card-body">
                        <?php if (empty($subjects)): ?>
                            <p class="text-muted">No subjects assigned yet.</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Subject Code</th>
                                            <th>Subject Name</th>
                                            <th>Class</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($subjects as $subject): ?>
                                            <tr>
                                                <td><?php echo $subject['code'] ?: 'N/A'; ?></td>
                                                <td><?php echo $subject['name']; ?></td>
                                                <td><?php echo $subject['class_name']; ?></td>
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
