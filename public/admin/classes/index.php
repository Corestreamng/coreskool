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

$where = "WHERE c.school_id = ? AND c.status != 'inactive'";
$params = [$_SESSION['school_id']];

if ($search) {
    $where .= " AND (c.name LIKE ? OR c.room_number LIKE ?)";
    $searchTerm = "%$search%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
}

// Get total count
$countSql = "SELECT COUNT(*) as total FROM classes c $where";
$totalStmt = $db->query($countSql, $params);
$totalItems = $totalStmt->fetch()['total'];

// Pagination calculation
$pagination = paginate($totalItems, $page, 20);

// Get classes with teacher information
$sql = "SELECT c.*, 
        CONCAT(u.first_name, ' ', u.last_name) as teacher_name,
        (SELECT COUNT(*) FROM student_classes sc WHERE sc.class_id = c.id AND sc.status = 'active') as student_count
        FROM classes c
        LEFT JOIN users u ON c.class_teacher_id = u.id
        $where
        ORDER BY c.name ASC
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
                <!-- Search -->
                <form method="GET" class="row" style="margin-bottom: 1.5rem;">
                    <div class="col-md-8">
                        <input type="text" name="search" class="form-control" placeholder="Search by class name or room number..." value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-info">
                            <i class="fas fa-search"></i> Search
                        </button>
                    </div>
                    <div class="col-md-2">
                        <a href="<?php echo BASE_URL; ?>public/admin/classes/index.php" class="btn btn-secondary">
                            <i class="fas fa-redo"></i> Reset
                        </a>
                    </div>
                </form>
                
                <!-- Classes Table -->
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Class Name</th>
                                <th>Level</th>
                                <th>Section</th>
                                <th>Room Number</th>
                                <th>Class Teacher</th>
                                <th>Capacity</th>
                                <th>Students</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($classes)): ?>
                                <tr>
                                    <td colspan="9" class="text-center">No classes found</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($classes as $class): ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($class['name']); ?></strong></td>
                                        <td><?php echo $class['class_level'] ?: 'N/A'; ?></td>
                                        <td><?php echo htmlspecialchars($class['section']) ?: 'N/A'; ?></td>
                                        <td><?php echo htmlspecialchars($class['room_number']) ?: 'N/A'; ?></td>
                                        <td><?php echo $class['teacher_name'] ?: '<span style="color: #6b7280;">Not Assigned</span>'; ?></td>
                                        <td><?php echo $class['capacity']; ?></td>
                                        <td>
                                            <span class="badge badge-info"><?php echo $class['student_count']; ?></span>
                                        </td>
                                        <td>
                                            <span class="badge badge-<?php echo $class['status'] === 'active' ? 'success' : 'secondary'; ?>">
                                                <?php echo ucfirst($class['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="<?php echo BASE_URL; ?>public/admin/classes/view.php?id=<?php echo $class['id']; ?>" 
                                               class="btn btn-sm btn-info" title="View">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="<?php echo BASE_URL; ?>public/admin/classes/edit.php?id=<?php echo $class['id']; ?>" 
                                               class="btn btn-sm btn-primary" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this class?');">
                                                <input type="hidden" name="delete_id" value="<?php echo $class['id']; ?>">
                                                <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <?php if ($pagination['total_pages'] > 1): ?>
                    <div class="pagination">
                        <?php if ($pagination['has_previous']): ?>
                            <a href="?page=<?php echo $pagination['current_page'] - 1; ?>&search=<?php echo urlencode($search); ?>">
                                <i class="fas fa-chevron-left"></i> Previous
                            </a>
                        <?php endif; ?>
                        
                        <?php for ($i = 1; $i <= $pagination['total_pages']; $i++): ?>
                            <?php if ($i == $pagination['current_page']): ?>
                                <span class="active"><?php echo $i; ?></span>
                            <?php else: ?>
                                <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>">
                                    <?php echo $i; ?>
                                </a>
                            <?php endif; ?>
                        <?php endfor; ?>
                        
                        <?php if ($pagination['has_next']): ?>
                            <a href="?page=<?php echo $pagination['current_page'] + 1; ?>&search=<?php echo urlencode($search); ?>">
                                Next <i class="fas fa-chevron-right"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include APP_PATH . '/views/shared/footer.php'; ?>
