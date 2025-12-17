<?php
/**
 * Subjects List
 * CoreSkool School Management System
 */

require_once dirname(dirname(dirname(__DIR__))) . '/config/config.php';

requireAuth();
requireRole('admin');

$pageTitle = 'Manage Subjects';
$db = Database::getInstance();

// Handle delete
if (isset($_POST['delete_id'])) {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
        setFlash('danger', 'Invalid form submission. Please try again.');
        redirect('admin/subjects/index.php');
    }
    
    $deleteId = (int)$_POST['delete_id'];
    try {
        $db->query("UPDATE subjects SET status = 'inactive' WHERE id = ? AND school_id = ?", [$deleteId, $_SESSION['school_id']]);
        setFlash('success', 'Subject deleted successfully');
        redirect('admin/subjects/index.php');
    } catch (Exception $e) {
        setFlash('danger', 'Failed to delete subject');
    }
}

// Pagination
$page = $_GET['page'] ?? 1;
$search = $_GET['search'] ?? '';

$where = "WHERE school_id = ? AND status = 'active'";
$params = [$_SESSION['school_id']];

if ($search) {
    $where .= " AND (name LIKE ? OR code LIKE ?)";
    $searchTerm = "%$search%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
}

// Get total count
$countSql = "SELECT COUNT(*) as total FROM subjects $where";
$totalStmt = $db->query($countSql, $params);
$totalItems = $totalStmt->fetch()['total'];

// Pagination calculation
$pagination = paginate($totalItems, $page, 20);

// Get subjects
$sql = "SELECT s.*,
        (SELECT COUNT(DISTINCT class_id) FROM class_subjects cs WHERE cs.subject_id = s.id) as class_count
        FROM subjects s
        $where
        ORDER BY s.name
        LIMIT {$pagination['items_per_page']} OFFSET {$pagination['offset']}";

$stmt = $db->query($sql, $params);
$subjects = $stmt->fetchAll();

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
                <strong>Subjects List</strong>
                <a href="<?php echo BASE_URL; ?>public/admin/subjects/add.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add New Subject
                </a>
            </div>
            
            <div class="card-body">
                <!-- Search Form -->
                <form method="GET" class="mb-4">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group mb-0">
                                <input type="text" name="search" class="form-control" placeholder="Search by subject name or code..." value="<?php echo htmlspecialchars($search); ?>">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i> Search
                            </button>
                            <?php if ($search): ?>
                                <a href="<?php echo BASE_URL; ?>public/admin/subjects/index.php" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> Clear
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </form>
                
                <!-- Subjects Table -->
                <?php if (count($subjects) > 0): ?>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Subject Name</th>
                                    <th>Subject Code</th>
                                    <th>Description</th>
                                    <th>Classes Assigned</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($subjects as $subject): ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($subject['name']); ?></strong></td>
                                        <td>
                                            <?php if ($subject['code']): ?>
                                                <span class="badge badge-secondary"><?php echo htmlspecialchars($subject['code']); ?></span>
                                            <?php else: ?>
                                                <span style="color: #999;">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($subject['description']): ?>
                                                <?php echo htmlspecialchars(substr($subject['description'], 0, 50)) . (strlen($subject['description']) > 50 ? '...' : ''); ?>
                                            <?php else: ?>
                                                <span style="color: #999;">No description</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge badge-info"><?php echo $subject['class_count']; ?> classes</span>
                                        </td>
                                        <td>
                                            <div class="btn-group">
                                                <a href="<?php echo BASE_URL; ?>public/admin/subjects/edit.php?id=<?php echo $subject['id']; ?>" 
                                                   class="btn btn-sm btn-info" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this subject?');">
                                                    <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                                                    <input type="hidden" name="delete_id" value="<?php echo $subject['id']; ?>">
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
                        <i class="fas fa-info-circle"></i> No subjects found. <a href="<?php echo BASE_URL; ?>public/admin/subjects/add.php">Add your first subject</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include APP_PATH . '/views/shared/footer.php'; ?>
