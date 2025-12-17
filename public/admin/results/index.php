<?php
/**
 * Results Management - Admin
 * CoreSkool School Management System
 */

require_once dirname(dirname(dirname(__DIR__))) . '/config/config.php';

requireAuth();
requireRole('admin');

$pageTitle = 'Results Management';
$db = Database::getInstance();

// Get filter parameters
$exam_id = isset($_GET['exam_id']) ? (int)$_GET['exam_id'] : null;
$class_id = isset($_GET['class_id']) ? (int)$_GET['class_id'] : null;

// Build query
$whereClause = "WHERE r.school_id = ?";
$params = [$_SESSION['school_id']];

if ($exam_id) {
    $whereClause .= " AND r.exam_id = ?";
    $params[] = $exam_id;
}

if ($class_id) {
    $whereClause .= " AND c.id = ?";
    $params[] = $class_id;
}

// Get results
$resultsQuery = $db->query("
    SELECT r.*, u.first_name, u.last_name, u.matric_number, 
           s.name as subject_name, e.name as exam_name, c.name as class_name,
           (r.ca_score + r.exam_score) as total_score
    FROM results r
    INNER JOIN users u ON r.student_id = u.id
    INNER JOIN subjects s ON r.subject_id = s.id
    INNER JOIN exams e ON r.exam_id = e.id
    LEFT JOIN student_classes sc ON u.id = sc.student_id AND sc.status = 'active'
    LEFT JOIN classes c ON sc.class_id = c.id
    $whereClause
    ORDER BY r.created_at DESC
    LIMIT 100
", $params);
$results = $resultsQuery->fetchAll();

// Get exams for filter
$examsQuery = $db->query("SELECT id, name FROM exams WHERE school_id = ? ORDER BY created_at DESC", [$_SESSION['school_id']]);
$exams = $examsQuery->fetchAll();

// Get classes for filter
$classesQuery = $db->query("SELECT id, name FROM classes WHERE school_id = ? AND status = 'active' ORDER BY name", [$_SESSION['school_id']]);
$classes = $classesQuery->fetchAll();

include APP_PATH . '/views/shared/header.php';
?>

<div class="main-content">
    <?php include APP_PATH . '/views/shared/sidebar.php'; ?>
    <?php include APP_PATH . '/views/shared/topbar.php'; ?>
    
    <div class="content-area">
        <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
            <div>
                <h2 style="margin: 0;">Results Management</h2>
                <p style="color: #6b7280; margin: 0.5rem 0 0 0;">View, approve, and manage examination results</p>
            </div>
            <div style="display: flex; gap: 0.75rem;">
                <a href="<?php echo BASE_URL; ?>public/admin/results/add.php" class="btn btn-success">
                    <i class="fas fa-plus"></i> Add Result
                </a>
                <a href="<?php echo BASE_URL; ?>public/admin/results/bulk-upload.php" class="btn btn-info">
                    <i class="fas fa-upload"></i> Bulk Upload
                </a>
            </div>
        </div>

        <!-- Filters -->
        <div class="card" style="margin-bottom: 1.5rem;">
            <div class="card-body">
                <form method="GET" action="">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="exam_id">Filter by Exam</label>
                                <select id="exam_id" name="exam_id" class="form-control" onchange="this.form.submit()">
                                    <option value="">-- All Exams --</option>
                                    <?php foreach ($exams as $exam): ?>
                                        <option value="<?php echo $exam['id']; ?>" <?php echo $exam_id == $exam['id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($exam['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="class_id">Filter by Class</label>
                                <select id="class_id" name="class_id" class="form-control" onchange="this.form.submit()">
                                    <option value="">-- All Classes --</option>
                                    <?php foreach ($classes as $class): ?>
                                        <option value="<?php echo $class['id']; ?>" <?php echo $class_id == $class['id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($class['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        
                        <div class="col-md-4" style="display: flex; align-items: flex-end;">
                            <div class="form-group" style="width: 100%;">
                                <a href="<?php echo BASE_URL; ?>public/admin/results/index.php" class="btn btn-secondary btn-block">
                                    <i class="fas fa-redo"></i> Clear Filters
                                </a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Results List -->
        <div class="card">
            <div class="card-header">
                <strong>Examination Results</strong>
            </div>
            <div class="card-body">
                <?php if (empty($results)): ?>
                    <div style="text-align: center; padding: 3rem;">
                        <i class="fas fa-graduation-cap" style="font-size: 4rem; color: #e5e7eb; margin-bottom: 1rem;"></i>
                        <h3 style="color: #6b7280; margin-bottom: 0.5rem;">No Results Found</h3>
                        <p style="color: #9ca3af; margin-bottom: 1.5rem;">Start adding examination results for students</p>
                        <a href="<?php echo BASE_URL; ?>public/admin/results/add.php" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Add Result
                        </a>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Student</th>
                                    <th>Matric No.</th>
                                    <th>Class</th>
                                    <th>Exam</th>
                                    <th>Subject</th>
                                    <th>CA Score</th>
                                    <th>Exam Score</th>
                                    <th>Total</th>
                                    <th>Grade</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($results as $result): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($result['first_name'] . ' ' . $result['last_name']); ?></td>
                                        <td><?php echo htmlspecialchars($result['matric_number']); ?></td>
                                        <td><?php echo htmlspecialchars($result['class_name'] ?? 'N/A'); ?></td>
                                        <td><?php echo htmlspecialchars($result['exam_name']); ?></td>
                                        <td><?php echo htmlspecialchars($result['subject_name']); ?></td>
                                        <td><?php echo $result['ca_score']; ?></td>
                                        <td><?php echo $result['exam_score']; ?></td>
                                        <td><strong><?php echo $result['total_score']; ?></strong></td>
                                        <td>
                                            <span class="badge badge-primary"><?php echo htmlspecialchars($result['grade']); ?></span>
                                        </td>
                                        <td>
                                            <?php
                                            $statusColors = [
                                                'pending' => 'warning',
                                                'approved' => 'success',
                                                'rejected' => 'danger'
                                            ];
                                            $color = $statusColors[$result['status']] ?? 'secondary';
                                            ?>
                                            <span class="badge badge-<?php echo $color; ?>">
                                                <?php echo ucfirst($result['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="<?php echo BASE_URL; ?>public/admin/results/edit.php?id=<?php echo $result['id']; ?>" 
                                               class="btn btn-sm btn-warning" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <?php if ($result['status'] === 'pending'): ?>
                                                <a href="<?php echo BASE_URL; ?>public/admin/results/approve.php?id=<?php echo $result['id']; ?>" 
                                                   class="btn btn-sm btn-success" title="Approve">
                                                    <i class="fas fa-check"></i>
                                                </a>
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
    </div>
</div>

<?php include APP_PATH . '/views/shared/footer.php'; ?>
