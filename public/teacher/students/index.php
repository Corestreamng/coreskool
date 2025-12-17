<?php
/**
 * My Students - Teacher
 * CoreSkool School Management System
 */

require_once dirname(dirname(dirname(__DIR__))) . '/config/config.php';

requireAuth();
requireRole('teacher');

$pageTitle = 'My Students';
$db = Database::getInstance();
$userId = $_SESSION['user_id'];

// Get teacher's classes
$classesQuery = $db->query("
    SELECT DISTINCT c.id, c.name
    FROM class_teachers ct
    INNER JOIN classes c ON ct.class_id = c.id
    WHERE ct.teacher_id = ? AND c.status = 'active'
", [$userId]);
$classes = $classesQuery->fetchAll();

// Get students in teacher's classes
$studentsQuery = $db->query("
    SELECT DISTINCT u.*, c.name as class_name
    FROM users u
    INNER JOIN student_classes sc ON u.id = sc.student_id
    INNER JOIN class_teachers ct ON sc.class_id = ct.class_id
    INNER JOIN classes c ON sc.class_id = c.id
    WHERE ct.teacher_id = ? AND sc.status = 'active' AND u.status = 'active'
    ORDER BY c.name, u.first_name, u.last_name
", [$userId]);
$students = $studentsQuery->fetchAll();

include APP_PATH . '/views/shared/header.php';
?>

<div class="main-content">
    <?php include APP_PATH . '/views/shared/sidebar.php'; ?>
    <?php include APP_PATH . '/views/shared/topbar.php'; ?>
    
    <div class="content-area">
        <div class="page-header" style="margin-bottom: 2rem;">
            <h2>My Students</h2>
            <p style="color: #6b7280;">Students in your classes</p>
        </div>

        <!-- Classes Info -->
        <div class="row" style="margin-bottom: 1.5rem;">
            <?php foreach ($classes as $class): ?>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body">
                            <h4><i class="fas fa-school"></i> <?php echo htmlspecialchars($class['name']); ?></h4>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Students List -->
        <div class="card">
            <div class="card-header"><strong>Students List</strong></div>
            <div class="card-body">
                <?php if (empty($students)): ?>
                    <div style="text-align: center; padding: 3rem;">
                        <i class="fas fa-users" style="font-size: 4rem; color: #e5e7eb; margin-bottom: 1rem;"></i>
                        <h3 style="color: #6b7280;">No Students</h3>
                        <p style="color: #9ca3af;">No students assigned to your classes yet</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Matric No.</th>
                                    <th>Name</th>
                                    <th>Class</th>
                                    <th>Gender</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($students as $student): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($student['matric_number']); ?></td>
                                        <td><strong><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($student['class_name']); ?></td>
                                        <td><?php echo ucfirst($student['gender']); ?></td>
                                        <td><?php echo htmlspecialchars($student['email'] ?? 'N/A'); ?></td>
                                        <td><?php echo htmlspecialchars($student['phone'] ?? 'N/A'); ?></td>
                                        <td>
                                            <a href="<?php echo BASE_URL; ?>public/teacher/students/view.php?id=<?php echo $student['id']; ?>" 
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
    </div>
</div>

<?php include APP_PATH . '/views/shared/footer.php'; ?>
