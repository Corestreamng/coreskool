<?php
/**
 * Teachers List
 * CoreSkool School Management System
 */

require_once dirname(dirname(dirname(__DIR__))) . '/config/config.php';

requireAuth();
requireRole('admin');

$pageTitle = 'Manage Teachers';
$db = Database::getInstance();

// Handle delete
if (isset($_POST['delete_id'])) {
    $deleteId = (int)$_POST['delete_id'];
    try {
        $db->query("UPDATE users SET status = 'inactive' WHERE id = ? AND role = 'teacher'", [$deleteId]);
        setFlash('success', 'Teacher deleted successfully');
        redirect('admin/teachers/index.php');
    } catch (Exception $e) {
        setFlash('danger', 'Failed to delete teacher');
    }
}

// Pagination
$page = $_GET['page'] ?? 1;
$search = $_GET['search'] ?? '';
$subjectFilter = $_GET['subject'] ?? '';

$where = "WHERE u.role = 'teacher' AND u.school_id = ? AND u.status != 'inactive'";
$params = [$_SESSION['school_id']];

if ($search) {
    $where .= " AND (u.first_name LIKE ? OR u.last_name LIKE ? OR u.email LIKE ? OR u.phone LIKE ?)";
    $searchTerm = "%$search%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
}

// Get total count
$countSql = "SELECT COUNT(DISTINCT u.id) as total FROM users u $where";
$totalStmt = $db->query($countSql, $params);
$totalItems = $totalStmt->fetch()['total'];

// Pagination calculation
$pagination = paginate($totalItems, $page, 20);

// Get teachers
$sql = "SELECT u.*, 
        GROUP_CONCAT(DISTINCT s.name SEPARATOR ', ') as subjects
        FROM users u
        LEFT JOIN teacher_subjects ts ON u.id = ts.teacher_id
        LEFT JOIN subjects s ON ts.subject_id = s.id
        $where
        GROUP BY u.id
        ORDER BY u.created_at DESC
        LIMIT {$pagination['items_per_page']} OFFSET {$pagination['offset']}";

$stmt = $db->query($sql, $params);
$teachers = $stmt->fetchAll();

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
                <strong>Teachers List</strong>
                <a href="<?php echo BASE_URL; ?>public/admin/teachers/add.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add New Teacher
                </a>
            </div>
            
            <div class="card-body">
                <!-- Search and Filter -->
                <form method="GET" class="row" style="margin-bottom: 1.5rem;">
                    <div class="col-md-8">
                        <input type="text" name="search" class="form-control" placeholder="Search by name, email, or phone..." value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-info">
                            <i class="fas fa-search"></i> Search
                        </button>
                    </div>
                    <div class="col-md-2">
                        <a href="<?php echo BASE_URL; ?>public/admin/teachers/index.php" class="btn btn-secondary">
                            <i class="fas fa-redo"></i> Reset
                        </a>
                    </div>
                </form>
                
                <!-- Teachers Table -->
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Avatar</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Subjects</th>
                                <th>Gender</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($teachers)): ?>
                                <tr>
                                    <td colspan="8" class="text-center">No teachers found</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($teachers as $teacher): ?>
                                    <tr>
                                        <td>
                                            <img src="<?php echo getAvatarUrl($teacher['avatar']); ?>" 
                                                 alt="Avatar" 
                                                 style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;">
                                        </td>
                                        <td><?php echo $teacher['first_name'] . ' ' . $teacher['last_name']; ?></td>
                                        <td><?php echo $teacher['email'] ?: 'N/A'; ?></td>
                                        <td><?php echo $teacher['phone'] ?: 'N/A'; ?></td>
                                        <td><?php echo $teacher['subjects'] ?: '<span style="color: #6b7280;">Not Assigned</span>'; ?></td>
                                        <td><?php echo ucfirst($teacher['gender']); ?></td>
                                        <td>
                                            <span class="badge badge-<?php echo $teacher['status'] === 'active' ? 'success' : 'secondary'; ?>">
                                                <?php echo ucfirst($teacher['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="<?php echo BASE_URL; ?>public/admin/teachers/view.php?id=<?php echo $teacher['id']; ?>" 
                                               class="btn btn-sm btn-info" title="View">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="<?php echo BASE_URL; ?>public/admin/teachers/edit.php?id=<?php echo $teacher['id']; ?>" 
                                               class="btn btn-sm btn-primary" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this teacher?');">
                                                <input type="hidden" name="delete_id" value="<?php echo $teacher['id']; ?>">
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
