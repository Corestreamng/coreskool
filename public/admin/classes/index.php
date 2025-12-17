<?php
/**
 * Classes List
 * CoreSkool School Management System
 */

require_once dirname(dirname(dirname(__DIR__))) . '/config/config.php';

requireAuth();
requireRole('admin');

$pageTitle = 'Manage Classes';
$db = Database::getInstance();

// Handle delete
if (isset($_POST['delete_id'])) {
    $deleteId = (int)$_POST['delete_id'];
    try {
        $db->query("UPDATE classes SET status = 'inactive' WHERE id = ? AND school_id = ?", [$deleteId, $_SESSION['school_id']]);
        setFlash('success', 'Class deleted successfully');
        redirect('admin/classes/index.php');
    } catch (Exception $e) {
        setFlash('danger', 'Failed to delete class');
    }
}

// Pagination and filters
$page = $_GET['page'] ?? 1;
$search = $_GET['search'] ?? '';

$where = "WHERE c.school_id = ? AND c.status != 'inactive'";
$params = [$_SESSION['school_id']];

if ($search) {
    $where .= " AND c.name LIKE ?";
    $params[] = "%$search%";
}

// Get total count
$countSql = "SELECT COUNT(*) as total FROM classes c $where";
$totalStmt = $db->query($countSql, $params);
$totalItems = $totalStmt->fetch()['total'];

// Pagination calculation
$pagination = paginate($totalItems, $page, 20);

// Get classes with teacher and student counts
$sql = "SELECT c.id, c.name, c.description, c.status, c.created_at,
        u.first_name, u.last_name,
        COUNT(DISTINCT sc.student_id) as student_count,
        COUNT(DISTINCT cs.subject_id) as subject_count
        FROM classes c
        LEFT JOIN users u ON c.class_teacher_id = u.id
        LEFT JOIN student_classes sc ON c.id = sc.class_id AND sc.status = 'active'
        LEFT JOIN class_subjects cs ON c.id = cs.class_id
        $where
        GROUP BY c.id
        ORDER BY c.name
        LIMIT {$pagination['items_per_page']} OFFSET {$pagination['offset']}";

$stmt = $db->query($sql, $params);
$classes = $stmt->fetchAll();

include APP_PATH . '/views/shared/header.php';
?>

<div class="main-content">
    <?php include APP_PATH . '/views/shared/sidebar.php'; ?>
    <?php include APP_PATH . '/views/shared/topbar.php'; ?>
    
    <div class="content-area">
        <div class="page-header">
            <h2>Manage Classes</h2>
            <a href="<?php echo BASE_URL; ?>public/admin/classes/add.php" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add New Class
            </a>
        </div>

        <!-- Search -->
        <div class="card" style="margin-bottom: 1.5rem;">
            <div class="card-body">
                <form method="GET" action="" class="row">
                    <div class="col-md-9">
                        <input type="text" name="search" class="form-control" 
                               placeholder="Search by class name..." 
                               value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i> Search
                        </button>
                        <a href="<?php echo BASE_URL; ?>public/admin/classes/index.php" class="btn btn-secondary">
                            <i class="fas fa-redo"></i> Reset
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Classes List -->
        <div class="card">
            <div class="card-header">
                <strong>Classes List (<?php echo $totalItems; ?> total)</strong>
            </div>
            <div class="card-body">
                <?php if (empty($classes)): ?>
                    <p class="text-center text-muted">No classes found. 
                        <a href="<?php echo BASE_URL; ?>public/admin/classes/add.php">Create your first class</a>
                    </p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Class Name</th>
                                    <th>Class Teacher</th>
                                    <th>Students</th>
                                    <th>Subjects</th>
                                    <th>Status</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($classes as $class): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo $class['name']; ?></strong>
                                            <?php if ($class['description']): ?>
                                                <br><small class="text-muted"><?php echo shortenText($class['description'], 50); ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($class['first_name']): ?>
                                                <?php echo $class['first_name'] . ' ' . $class['last_name']; ?>
                                            <?php else: ?>
                                                <span class="text-muted">Not Assigned</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge badge-primary">
                                                <?php echo $class['student_count']; ?> students
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge badge-info">
                                                <?php echo $class['subject_count']; ?> subjects
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge badge-<?php echo $class['status'] === 'active' ? 'success' : 'danger'; ?>">
                                                <?php echo ucfirst($class['status']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo formatDate($class['created_at']); ?></td>
                                        <td>
                                            <a href="<?php echo BASE_URL; ?>public/admin/classes/view.php?id=<?php echo $class['id']; ?>" 
                                               class="btn btn-sm btn-info" title="View">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="<?php echo BASE_URL; ?>public/admin/classes/edit.php?id=<?php echo $class['id']; ?>" 
                                               class="btn btn-sm btn-primary" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form method="POST" style="display: inline;" 
                                                  onsubmit="return confirm('Are you sure you want to delete this class?');">
                                                <input type="hidden" name="delete_id" value="<?php echo $class['id']; ?>">
                                                <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <?php if ($pagination['total_pages'] > 1): ?>
                        <div style="margin-top: 1.5rem; text-align: center;">
                            <nav>
                                <ul class="pagination" style="display: inline-flex; list-style: none; gap: 0.5rem;">
                                    <?php if ($pagination['has_previous']): ?>
                                        <li>
                                            <a href="?page=<?php echo $pagination['current_page'] - 1; ?>&search=<?php echo urlencode($search); ?>" 
                                               class="btn btn-sm btn-secondary">Previous</a>
                                        </li>
                                    <?php endif; ?>
                                    
                                    <li style="padding: 0.5rem 1rem;">
                                        Page <?php echo $pagination['current_page']; ?> of <?php echo $pagination['total_pages']; ?>
                                    </li>
                                    
                                    <?php if ($pagination['has_next']): ?>
                                        <li>
                                            <a href="?page=<?php echo $pagination['current_page'] + 1; ?>&search=<?php echo urlencode($search); ?>" 
                                               class="btn btn-sm btn-secondary">Next</a>
                                        </li>
                                    <?php endif; ?>
                                </ul>
                            </nav>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include APP_PATH . '/views/shared/footer.php'; ?>
