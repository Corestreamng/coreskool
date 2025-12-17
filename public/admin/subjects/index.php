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
    $deleteId = (int)$_POST['delete_id'];
    try {
        $db->query("UPDATE subjects SET status = 'inactive' WHERE id = ? AND school_id = ?", [$deleteId, $_SESSION['school_id']]);
        setFlash('success', 'Subject deleted successfully');
        redirect('admin/subjects/index.php');
    } catch (Exception $e) {
        setFlash('danger', 'Failed to delete subject');
    }
}

// Pagination and filters
$page = $_GET['page'] ?? 1;
$search = $_GET['search'] ?? '';

$where = "WHERE s.school_id = ? AND s.status != 'inactive'";
$params = [$_SESSION['school_id']];

if ($search) {
    $where .= " AND (s.name LIKE ? OR s.code LIKE ?)";
    $searchTerm = "%$search%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
}

// Get total count
$countSql = "SELECT COUNT(*) as total FROM subjects s $where";
$totalStmt = $db->query($countSql, $params);
$totalItems = $totalStmt->fetch()['total'];

// Pagination calculation
$pagination = paginate($totalItems, $page, 20);

// Get subjects with class count
$sql = "SELECT s.id, s.name, s.code, s.description, s.status, s.created_at,
        COUNT(DISTINCT cs.class_id) as class_count
        FROM subjects s
        LEFT JOIN class_subjects cs ON s.id = cs.subject_id
        $where
        GROUP BY s.id
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
        <div class="page-header">
            <h2>Manage Subjects</h2>
            <a href="<?php echo BASE_URL; ?>public/admin/subjects/add.php" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add New Subject
            </a>
        </div>

        <!-- Search -->
        <div class="card" style="margin-bottom: 1.5rem;">
            <div class="card-body">
                <form method="GET" action="" class="row">
                    <div class="col-md-9">
                        <input type="text" name="search" class="form-control" 
                               placeholder="Search by subject name or code..." 
                               value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i> Search
                        </button>
                        <a href="<?php echo BASE_URL; ?>public/admin/subjects/index.php" class="btn btn-secondary">
                            <i class="fas fa-redo"></i> Reset
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Subjects List -->
        <div class="card">
            <div class="card-header">
                <strong>Subjects List (<?php echo $totalItems; ?> total)</strong>
            </div>
            <div class="card-body">
                <?php if (empty($subjects)): ?>
                    <p class="text-center text-muted">No subjects found. 
                        <a href="<?php echo BASE_URL; ?>public/admin/subjects/add.php">Add your first subject</a>
                    </p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Subject Code</th>
                                    <th>Subject Name</th>
                                    <th>Description</th>
                                    <th>Classes</th>
                                    <th>Status</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($subjects as $subject): ?>
                                    <tr>
                                        <td><strong><?php echo $subject['code'] ?: 'N/A'; ?></strong></td>
                                        <td><?php echo $subject['name']; ?></td>
                                        <td>
                                            <?php if ($subject['description']): ?>
                                                <small class="text-muted"><?php echo shortenText($subject['description'], 50); ?></small>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge badge-info">
                                                <?php echo $subject['class_count']; ?> class(es)
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge badge-<?php echo $subject['status'] === 'active' ? 'success' : 'danger'; ?>">
                                                <?php echo ucfirst($subject['status']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo formatDate($subject['created_at']); ?></td>
                                        <td>
                                            <a href="<?php echo BASE_URL; ?>public/admin/subjects/view.php?id=<?php echo $subject['id']; ?>" 
                                               class="btn btn-sm btn-info" title="View">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="<?php echo BASE_URL; ?>public/admin/subjects/edit.php?id=<?php echo $subject['id']; ?>" 
                                               class="btn btn-sm btn-primary" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form method="POST" style="display: inline;" 
                                                  onsubmit="return confirm('Are you sure you want to delete this subject?');">
                                                <input type="hidden" name="delete_id" value="<?php echo $subject['id']; ?>">
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
