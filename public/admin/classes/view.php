<?php
/**
 * View Class Details
 * CoreSkool School Management System
 */

require_once dirname(dirname(dirname(__DIR__))) . '/config/config.php';

requireAuth();
requireRole('admin');

$pageTitle = 'Class Details';
$db = Database::getInstance();

$classId = (int)($_GET['id'] ?? 0);

if (!$classId) {
    setFlash('danger', 'Invalid class ID');
    redirect('admin/classes/index.php');
}

// Get class details with teacher information
$stmt = $db->query("
    SELECT c.*, 
           CONCAT(u.first_name, ' ', u.last_name) as teacher_name,
           u.email as teacher_email,
           u.phone as teacher_phone
    FROM classes c
    LEFT JOIN users u ON c.class_teacher_id = u.id
    WHERE c.id = ? AND c.school_id = ?
", [$classId, $_SESSION['school_id']]);

$class = $stmt->fetch();

if (!$class) {
    setFlash('danger', 'Class not found');
    redirect('admin/classes/index.php');
}

// Get students in this class
$studentsStmt = $db->query("
    SELECT u.id, u.first_name, u.last_name, u.matric_number, u.gender, u.avatar, sc.roll_number
    FROM student_classes sc
    INNER JOIN users u ON sc.student_id = u.id
    WHERE sc.class_id = ? AND sc.status = 'active'
    ORDER BY u.first_name, u.last_name
", [$classId]);
$students = $studentsStmt->fetchAll();

// Get subjects for this class
$subjectsStmt = $db->query("
    SELECT s.id, s.name, s.code, CONCAT(u.first_name, ' ', u.last_name) as teacher_name
    FROM class_subjects cs
    INNER JOIN subjects s ON cs.subject_id = s.id
    LEFT JOIN users u ON cs.teacher_id = u.id
    WHERE cs.class_id = ?
    ORDER BY s.name
", [$classId]);
$subjects = $subjectsStmt->fetchAll();

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
        
        <!-- Class Information Card -->
        <div class="card">
            <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                <strong>Class Details: <?php echo htmlspecialchars($class['name']); ?></strong>
                <div>
                    <a href="<?php echo BASE_URL; ?>public/admin/classes/edit.php?id=<?php echo $class['id']; ?>" class="btn btn-primary">
                        <i class="fas fa-edit"></i> Edit Class
                    </a>
                    <a href="<?php echo BASE_URL; ?>public/admin/classes/index.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to List
                    </a>
                </div>
            </div>
            
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h5 style="margin-bottom: 1rem; color: var(--primary-color);">Basic Information</h5>
                        <table class="table table-bordered">
                            <tr>
                                <th style="width: 40%;">Class Name</th>
                                <td><?php echo htmlspecialchars($class['name']); ?></td>
                            </tr>
                            <tr>
                                <th>Class Level</th>
                                <td><?php echo $class['class_level'] ?: 'N/A'; ?></td>
                            </tr>
                            <tr>
                                <th>Section</th>
                                <td><?php echo htmlspecialchars($class['section']) ?: 'N/A'; ?></td>
                            </tr>
                            <tr>
                                <th>Room Number</th>
                                <td><?php echo htmlspecialchars($class['room_number']) ?: 'N/A'; ?></td>
                            </tr>
                            <tr>
                                <th>Capacity</th>
                                <td><?php echo $class['capacity']; ?></td>
                            </tr>
                            <tr>
                                <th>Current Students</th>
                                <td>
                                    <span class="badge badge-info"><?php echo count($students); ?></span>
                                    <?php if (count($students) >= $class['capacity']): ?>
                                        <span class="badge badge-warning">Full</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <th>Status</th>
                                <td>
                                    <span class="badge badge-<?php echo $class['status'] === 'active' ? 'success' : 'secondary'; ?>">
                                        <?php echo ucfirst($class['status']); ?>
                                    </span>
                                </td>
                            </tr>
                        </table>
                    </div>
                    
                    <div class="col-md-6">
                        <h5 style="margin-bottom: 1rem; color: var(--primary-color);">Class Teacher</h5>
                        <?php if ($class['teacher_name']): ?>
                            <table class="table table-bordered">
                                <tr>
                                    <th style="width: 40%;">Name</th>
                                    <td><?php echo htmlspecialchars($class['teacher_name']); ?></td>
                                </tr>
                                <tr>
                                    <th>Email</th>
                                    <td><?php echo htmlspecialchars($class['teacher_email']) ?: 'N/A'; ?></td>
                                </tr>
                                <tr>
                                    <th>Phone</th>
                                    <td><?php echo htmlspecialchars($class['teacher_phone']) ?: 'N/A'; ?></td>
                                </tr>
                            </table>
                        <?php else: ?>
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle"></i> No class teacher assigned
                            </div>
                        <?php endif; ?>
                        
                        <h5 style="margin: 1.5rem 0 1rem; color: var(--primary-color);">Statistics</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="stats-card" style="margin-bottom: 1rem;">
                                    <div class="stats-value"><?php echo count($students); ?></div>
                                    <div class="stats-label">Total Students</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="stats-card" style="margin-bottom: 1rem;">
                                    <div class="stats-value"><?php echo count($subjects); ?></div>
                                    <div class="stats-label">Total Subjects</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Students List -->
        <div class="card">
            <div class="card-header">
                <strong>Students in <?php echo htmlspecialchars($class['name']); ?> (<?php echo count($students); ?>)</strong>
            </div>
            <div class="card-body">
                <?php if (empty($students)): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> No students assigned to this class yet.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Avatar</th>
                                    <th>Matric Number</th>
                                    <th>Name</th>
                                    <th>Roll Number</th>
                                    <th>Gender</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($students as $student): ?>
                                    <tr>
                                        <td>
                                            <img src="<?php echo getAvatarUrl($student['avatar']); ?>" 
                                                 alt="Avatar" 
                                                 style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;">
                                        </td>
                                        <td><?php echo htmlspecialchars($student['matric_number']); ?></td>
                                        <td><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></td>
                                        <td><?php echo htmlspecialchars($student['roll_number']) ?: 'N/A'; ?></td>
                                        <td><?php echo ucfirst($student['gender']); ?></td>
                                        <td>
                                            <a href="<?php echo BASE_URL; ?>public/admin/students/view.php?id=<?php echo $student['id']; ?>" 
                                               class="btn btn-sm btn-info">
                                                <i class="fas fa-eye"></i> View
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Subjects List -->
        <div class="card">
            <div class="card-header">
                <strong>Subjects for <?php echo htmlspecialchars($class['name']); ?> (<?php echo count($subjects); ?>)</strong>
            </div>
            <div class="card-body">
                <?php if (empty($subjects)): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> No subjects assigned to this class yet.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Subject Code</th>
                                    <th>Subject Name</th>
                                    <th>Teacher</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($subjects as $subject): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($subject['code']) ?: 'N/A'; ?></td>
                                        <td><?php echo htmlspecialchars($subject['name']); ?></td>
                                        <td><?php echo htmlspecialchars($subject['teacher_name']) ?: '<span style="color: #6b7280;">Not Assigned</span>'; ?></td>
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
