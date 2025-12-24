<?php
/**
 * Library Management
 * CoreSkool School Management System
 */

require_once dirname(dirname(dirname(__DIR__))) . '/config/config.php';

requireAuth();
requireRole('admin');

$pageTitle = 'Library Management';
$db = Database::getInstance();

// Handle delete
if (isset($_POST['delete_id'])) {
    $deleteId = (int)$_POST['delete_id'];
    try {
        $db->query("UPDATE books SET status = 'unavailable' WHERE id = ?", [$deleteId]);
        setFlash('success', 'Book removed from library successfully');
        redirect('admin/library/index.php');
    } catch (Exception $e) {
        setFlash('danger', 'Failed to remove book');
    }
}

// Pagination
$page = $_GET['page'] ?? 1;
$search = $_GET['search'] ?? '';
$categoryFilter = $_GET['category'] ?? '';
$statusFilter = $_GET['status'] ?? '';

$where = "WHERE school_id = ?";
$params = [$_SESSION['school_id']];

if ($search) {
    $where .= " AND (title LIKE ? OR author LIKE ? OR isbn LIKE ? OR publisher LIKE ?)";
    $searchTerm = "%$search%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
}

if ($categoryFilter) {
    $where .= " AND category = ?";
    $params[] = $categoryFilter;
}

if ($statusFilter) {
    $where .= " AND status = ?";
    $params[] = $statusFilter;
}

// Get total count
$countSql = "SELECT COUNT(*) as total FROM books $where";
$totalStmt = $db->query($countSql, $params);
$totalItems = $totalStmt->fetch()['total'];

// Pagination calculation
$pagination = paginate($totalItems, $page, 20);

// Get books
$sql = "SELECT * FROM books 
        $where
        ORDER BY created_at DESC
        LIMIT {$pagination['items_per_page']} OFFSET {$pagination['offset']}";

$stmt = $db->query($sql, $params);
$books = $stmt->fetchAll();

// Get distinct categories for filter
$categoriesStmt = $db->query("SELECT DISTINCT category FROM books WHERE school_id = ? AND category IS NOT NULL ORDER BY category", [$_SESSION['school_id']]);
$categories = $categoriesStmt->fetchAll();

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
                <strong>Library - Books Collection</strong>
                <div>
                    <a href="<?php echo BASE_URL; ?>public/admin/library/issues.php" class="btn btn-info">
                        <i class="fas fa-book-open"></i> Book Issues
                    </a>
                    <a href="<?php echo BASE_URL; ?>public/admin/library/add.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add New Book
                    </a>
                </div>
            </div>
            
            <div class="card-body">
                <!-- Search and Filter -->
                <form method="GET" class="row" style="margin-bottom: 1.5rem;">
                    <div class="col-md-4">
                        <input type="text" name="search" class="form-control" placeholder="Search by title, author, ISBN, or publisher..." value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    <div class="col-md-2">
                        <select name="category" class="form-control">
                            <option value="">All Categories</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo $cat['category']; ?>" <?php echo $categoryFilter == $cat['category'] ? 'selected' : ''; ?>>
                                    <?php echo $cat['category']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="status" class="form-control">
                            <option value="">All Status</option>
                            <option value="available" <?php echo $statusFilter == 'available' ? 'selected' : ''; ?>>Available</option>
                            <option value="unavailable" <?php echo $statusFilter == 'unavailable' ? 'selected' : ''; ?>>Unavailable</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-info">
                            <i class="fas fa-search"></i> Search
                        </button>
                    </div>
                    <div class="col-md-2">
                        <a href="<?php echo BASE_URL; ?>public/admin/library/index.php" class="btn btn-secondary">
                            <i class="fas fa-redo"></i> Reset
                        </a>
                    </div>
                </form>
                
                <!-- Books Table -->
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Cover</th>
                                <th>Title</th>
                                <th>Author</th>
                                <th>ISBN</th>
                                <th>Category</th>
                                <th>Quantity</th>
                                <th>Available</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($books)): ?>
                                <tr>
                                    <td colspan="9" class="text-center">No books found</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($books as $book): ?>
                                    <tr>
                                        <td>
                                            <?php if ($book['cover_image']): ?>
                                                <img src="<?php echo BASE_URL . 'public/uploads/' . $book['cover_image']; ?>" 
                                                     alt="Cover" 
                                                     style="width: 40px; height: 50px; object-fit: cover;">
                                            <?php else: ?>
                                                <div style="width: 40px; height: 50px; background: #e5e7eb; display: flex; align-items: center; justify-content: center;">
                                                    <i class="fas fa-book" style="color: #6b7280;"></i>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td><strong><?php echo htmlspecialchars($book['title']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($book['author'] ?: 'N/A'); ?></td>
                                        <td><?php echo htmlspecialchars($book['isbn'] ?: 'N/A'); ?></td>
                                        <td><?php echo htmlspecialchars($book['category'] ?: 'N/A'); ?></td>
                                        <td><?php echo $book['quantity']; ?></td>
                                        <td>
                                            <span class="<?php echo $book['available_quantity'] > 0 ? 'badge badge-success' : 'badge badge-danger'; ?>">
                                                <?php echo $book['available_quantity']; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge badge-<?php echo $book['status'] === 'available' ? 'success' : 'secondary'; ?>">
                                                <?php echo ucfirst($book['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="<?php echo BASE_URL; ?>public/admin/library/view.php?id=<?php echo $book['id']; ?>" 
                                               class="btn btn-sm btn-info" title="View">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="<?php echo BASE_URL; ?>public/admin/library/edit.php?id=<?php echo $book['id']; ?>" 
                                               class="btn btn-sm btn-primary" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to remove this book?');">
                                                <input type="hidden" name="delete_id" value="<?php echo $book['id']; ?>">
                                                <button type="submit" class="btn btn-sm btn-danger" title="Remove">
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
                            <a href="?page=<?php echo $pagination['current_page'] - 1; ?>&search=<?php echo urlencode($search); ?>&category=<?php echo $categoryFilter; ?>&status=<?php echo $statusFilter; ?>">
                                <i class="fas fa-chevron-left"></i> Previous
                            </a>
                        <?php endif; ?>
                        
                        <?php for ($i = 1; $i <= $pagination['total_pages']; $i++): ?>
                            <?php if ($i == $pagination['current_page']): ?>
                                <span class="active"><?php echo $i; ?></span>
                            <?php else: ?>
                                <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&category=<?php echo $categoryFilter; ?>&status=<?php echo $statusFilter; ?>">
                                    <?php echo $i; ?>
                                </a>
                            <?php endif; ?>
                        <?php endfor; ?>
                        
                        <?php if ($pagination['has_next']): ?>
                            <a href="?page=<?php echo $pagination['current_page'] + 1; ?>&search=<?php echo urlencode($search); ?>&category=<?php echo $categoryFilter; ?>&status=<?php echo $statusFilter; ?>">
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
