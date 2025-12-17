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

// Pagination
$page = $_GET['page'] ?? 1;
$search = $_GET['search'] ?? '';

$where = "WHERE school_id = ? AND status = 'active'";
$params = [$_SESSION['school_id']];

if ($search) {
    $where .= " AND name LIKE ?";
    $params[] = "%$search%";
}

// Get total count
$countSql = "SELECT COUNT(*) as total FROM classes $where";
$totalStmt = $db->query($countSql, $params);
$totalItems = $totalStmt->fetch()['total'];

// Pagination calculation
$pagination = paginate($totalItems, $page, 20);

// Get classes
$sql = "SELECT c.*, 
        u.first_name as teacher_first_name, 
        u.last_name as teacher_last_name,
        (SELECT COUNT(*) FROM student_classes sc WHERE sc.class_id = c.id AND sc.status = 'active') as student_count
        FROM classes c
        LEFT JOIN users u ON c.class_teacher_id = u.id
        $where
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
        <?php 
        $flash = getFlash();
        if ($flash): 
        ?>
            <div class="alert alert-<?php echo $flash['type']; ?>">
                <?php echo $flash['message']; ?>
            </div>
        <?php endif; ?>
        
        <div class="card">
            <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                <strong>Classes List</strong>
                <a href="<?php echo BASE_URL; ?>public/admin/classes/add.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add New Class
                </a>
            </div>
            
            <div class="card-body">
                <!-- Search Form -->
                <form method="GET" class="mb-4">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group mb-0">
                                <input type="text" name="search" class="form-control" placeholder="Search by class name..." value="<?php echo htmlspecialchars($search); ?>">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i> Search
                            </button>
                            <?php if ($search): ?>
                                <a href="<?php echo BASE_URL; ?>public/admin/classes/index.php" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> Clear
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </form>
                
                <!-- Classes Table -->
                <?php if (count($classes) > 0): ?>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Class Name</th>
                                    <th>Class Level</th>
                                    <th>Section</th>
                                    <th>Class Teacher</th>
                                    <th>Capacity</th>
                                    <th>Students</th>
                                    <th>Room</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($classes as $class): ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($class['name']); ?></strong></td>
                                        <td><?php echo $class['class_level'] ? htmlspecialchars($class['class_level']) : '-'; ?></td>
                                        <td><?php echo $class['section'] ? htmlspecialchars($class['section']) : '-'; ?></td>
                                        <td>
                                            <?php if ($class['teacher_first_name']): ?>
                                                <?php echo htmlspecialchars($class['teacher_first_name'] . ' ' . $class['teacher_last_name']); ?>
                                            <?php else: ?>
                                                <span style="color: #999;">Not assigned</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($class['capacity']); ?></td>
                                        <td>
                                            <span class="badge badge-info"><?php echo $class['student_count']; ?></span>
                                        </td>
                                        <td><?php echo $class['room_number'] ? htmlspecialchars($class['room_number']) : '-'; ?></td>
                                        <td>
                                            <div class="btn-group">
                                                <a href="<?php echo BASE_URL; ?>public/admin/classes/edit.php?id=<?php echo $class['id']; ?>" 
                                                   class="btn btn-sm btn-info" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this class?');">
                                                    <input type="hidden" name="delete_id" value="<?php echo $class['id']; ?>">
                                                    <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
                    <?php if ($pagination['total_pages'] > 1): ?>
                        <nav aria-label="Page navigation">
                            <ul class="pagination">
                                <?php if ($pagination['current_page'] > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?php echo $pagination['current_page'] - 1; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>">Previous</a>
                                    </li>
                                <?php endif; ?>
                                
                                <?php for ($i = 1; $i <= $pagination['total_pages']; $i++): ?>
                                    <li class="page-item <?php echo $i === $pagination['current_page'] ? 'active' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $i; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>"><?php echo $i; ?></a>
                                    </li>
                                <?php endfor; ?>
                                
                                <?php if ($pagination['current_page'] < $pagination['total_pages']): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?php echo $pagination['current_page'] + 1; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>">Next</a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> No classes found. <a href="<?php echo BASE_URL; ?>public/admin/classes/add.php">Add your first class</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include APP_PATH . '/views/shared/footer.php'; ?>
