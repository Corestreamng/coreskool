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

// Pagination and filters
$page = $_GET['page'] ?? 1;
$search = $_GET['search'] ?? '';

$where = "WHERE u.role = 'parent' AND u.school_id = ? AND u.status != 'inactive'";
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
$countSql = "SELECT COUNT(*) as total FROM users u $where";
$totalStmt = $db->query($countSql, $params);
$totalItems = $totalStmt->fetch()['total'];

// Pagination calculation
$pagination = paginate($totalItems, $page, 20);

// Get parents with ward count
$sql = "SELECT u.id, u.first_name, u.last_name, u.email, u.phone, u.status, u.created_at,
        COUNT(DISTINCT ps.student_id) as ward_count,
        GROUP_CONCAT(DISTINCT CONCAT(s.first_name, ' ', s.last_name) SEPARATOR ', ') as wards
        FROM users u
        LEFT JOIN parent_student ps ON u.id = ps.parent_id
        LEFT JOIN users s ON ps.student_id = s.id AND s.status = 'active'
        $where
        GROUP BY u.id
        ORDER BY u.created_at DESC
        LIMIT {$pagination['items_per_page']} OFFSET {$pagination['offset']}";

$stmt = $db->query($sql, $params);
$parents = $stmt->fetchAll();

include APP_PATH . '/views/shared/header.php';
?>

<div class="main-content">
    <?php include APP_PATH . '/views/shared/sidebar.php'; ?>
    <?php include APP_PATH . '/views/shared/topbar.php'; ?>
    
    <div class="content-area">
        <div class="page-header">
            <h2>Manage Parents/Guardians</h2>
            <a href="<?php echo BASE_URL; ?>public/admin/parents/add.php" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add New Parent
            </a>
        </div>

        <!-- Search -->
        <div class="card" style="margin-bottom: 1.5rem;">
            <div class="card-body">
                <form method="GET" action="" class="row">
                    <div class="col-md-9">
                        <input type="text" name="search" class="form-control" 
                               placeholder="Search by name, email, phone..." 
                               value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i> Search
                        </button>
                        <a href="<?php echo BASE_URL; ?>public/admin/parents/index.php" class="btn btn-secondary">
                            <i class="fas fa-redo"></i> Reset
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Parents List -->
        <div class="card">
            <div class="card-header">
                <strong>Parents/Guardians List (<?php echo $totalItems; ?> total)</strong>
            </div>
            <div class="card-body">
                <?php if (empty($parents)): ?>
                    <p class="text-center text-muted">No parents found. 
                        <a href="<?php echo BASE_URL; ?>public/admin/parents/add.php">Add your first parent</a>
                    </p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Wards</th>
                                    <th>Status</th>
                                    <th>Registered</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($parents as $parent): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo $parent['first_name'] . ' ' . $parent['last_name']; ?></strong>
                                        </td>
                                        <td><?php echo $parent['email'] ?: 'N/A'; ?></td>
                                        <td><?php echo $parent['phone'] ?: 'N/A'; ?></td>
                                        <td>
                                            <span class="badge badge-info">
                                                <?php echo $parent['ward_count']; ?> ward(s)
                                            </span>
                                            <?php if ($parent['wards']): ?>
                                                <small class="text-muted d-block" style="margin-top: 0.25rem;">
                                                    <?php echo shortenText($parent['wards'], 50); ?>
                                                </small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge badge-<?php echo $parent['status'] === 'active' ? 'success' : 'danger'; ?>">
                                                <?php echo ucfirst($parent['status']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo formatDate($parent['created_at']); ?></td>
                                        <td>
                                            <a href="<?php echo BASE_URL; ?>public/admin/parents/view.php?id=<?php echo $parent['id']; ?>" 
                                               class="btn btn-sm btn-info" title="View">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="<?php echo BASE_URL; ?>public/admin/parents/edit.php?id=<?php echo $parent['id']; ?>" 
                                               class="btn btn-sm btn-primary" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form method="POST" style="display: inline;" 
                                                  onsubmit="return confirm('Are you sure you want to delete this parent?');">
                                                <input type="hidden" name="delete_id" value="<?php echo $parent['id']; ?>">
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
