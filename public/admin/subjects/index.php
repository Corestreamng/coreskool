<?php
/**
 * Subjects Management
 * CoreSkool School Management System
 */

require_once dirname(dirname(dirname(__DIR__))) . '/config/config.php';

requireAuth();
requireRole('admin');

$pageTitle = 'Manage Subjects';
$db = Database::getInstance();

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
$sql = "SELECT * FROM subjects $where ORDER BY name ASC LIMIT {$pagination['items_per_page']} OFFSET {$pagination['offset']}";
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
                <strong>Subjects</strong>
                <a href="<?php echo BASE_URL; ?>public/admin/subjects/add.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add New Subject
                </a>
            </div>
            
            <div class="card-body">
                <!-- Search -->
                <form method="GET" class="row" style="margin-bottom: 1.5rem;">
                    <div class="col-md-10">
                        <input type="text" name="search" class="form-control" placeholder="Search by subject name or code..." value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-info">
                            <i class="fas fa-search"></i> Search
                        </button>
                    </div>
                </form>
                
                <!-- Subjects Table -->
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Subject Code</th>
                                <th>Subject Name</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($subjects)): ?>
                                <tr>
                                    <td colspan="4" class="text-center">No subjects found</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($subjects as $subject): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($subject['code'] ?: 'N/A'); ?></td>
                                        <td><strong><?php echo htmlspecialchars($subject['name']); ?></strong></td>
                                        <td>
                                            <span class="badge badge-<?php echo $subject['status'] === 'active' ? 'success' : 'secondary'; ?>">
                                                <?php echo ucfirst($subject['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="<?php echo BASE_URL; ?>public/admin/subjects/view.php?id=<?php echo $subject['id']; ?>" 
                                               class="btn btn-sm btn-info" title="View">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="<?php echo BASE_URL; ?>public/admin/subjects/edit.php?id=<?php echo $subject['id']; ?>" 
                                               class="btn btn-sm btn-primary" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
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
