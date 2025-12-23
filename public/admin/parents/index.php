<?php
/**
 * Parents List
 * CoreSkool School Management System
 */

require_once dirname(dirname(dirname(__DIR__))) . '/config/config.php';

requireAuth();
requireRole('admin');

$pageTitle = 'Manage Parents';
$db = Database::getInstance();

// Handle delete
if (isset($_POST['delete_id'])) {
    $deleteId = (int)$_POST['delete_id'];
    try {
        $db->query("UPDATE users SET status = 'inactive' WHERE id = ? AND role = 'parent'", [$deleteId]);
        setFlash('success', 'Parent deleted successfully');
        redirect('admin/parents/index.php');
    } catch (Exception $e) {
        setFlash('danger', 'Failed to delete parent');
    }
}

// Pagination
$page = $_GET['page'] ?? 1;
$search = $_GET['search'] ?? '';

$where = "WHERE role = 'parent' AND school_id = ? AND status != 'inactive'";
$params = [$_SESSION['school_id']];

if ($search) {
    $where .= " AND (first_name LIKE ? OR last_name LIKE ? OR email LIKE ? OR phone LIKE ?)";
    $searchTerm = "%$search%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
}

// Get total count
$countSql = "SELECT COUNT(*) as total FROM users $where";
$totalStmt = $db->query($countSql, $params);
$totalItems = $totalStmt->fetch()['total'];

// Pagination calculation
$pagination = paginate($totalItems, $page, 20);

// Get parents
$sql = "SELECT * FROM users $where ORDER BY created_at DESC LIMIT {$pagination['items_per_page']} OFFSET {$pagination['offset']}";
$stmt = $db->query($sql, $params);
$parents = $stmt->fetchAll();

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
                <strong>Parents List</strong>
                <a href="<?php echo BASE_URL; ?>public/admin/parents/add.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add New Parent
                </a>
            </div>
            
            <div class="card-body">
                <!-- Search -->
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
                        <a href="<?php echo BASE_URL; ?>public/admin/parents/index.php" class="btn btn-secondary">
                            <i class="fas fa-redo"></i> Reset
                        </a>
                    </div>
                </form>
                
                <!-- Parents Table -->
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Avatar</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Gender</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($parents)): ?>
                                <tr>
                                    <td colspan="7" class="text-center">No parents found</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($parents as $parent): ?>
                                    <tr>
                                        <td>
                                            <img src="<?php echo getAvatarUrl($parent['avatar']); ?>" 
                                                 alt="Avatar" 
                                                 style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;">
                                        </td>
                                        <td><?php echo $parent['first_name'] . ' ' . $parent['last_name']; ?></td>
                                        <td><?php echo $parent['email'] ?: 'N/A'; ?></td>
                                        <td><?php echo $parent['phone'] ?: 'N/A'; ?></td>
                                        <td><?php echo ucfirst($parent['gender']); ?></td>
                                        <td>
                                            <span class="badge badge-<?php echo $parent['status'] === 'active' ? 'success' : 'secondary'; ?>">
                                                <?php echo ucfirst($parent['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="<?php echo BASE_URL; ?>public/admin/parents/view.php?id=<?php echo $parent['id']; ?>" 
                                               class="btn btn-sm btn-info" title="View">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="<?php echo BASE_URL; ?>public/admin/parents/edit.php?id=<?php echo $parent['id']; ?>" 
                                               class="btn btn-sm btn-primary" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure?');">
                                                <input type="hidden" name="delete_id" value="<?php echo $parent['id']; ?>">
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
                                <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>"><?php echo $i; ?></a>
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
