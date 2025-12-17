<?php
/**
 * Library Management - Admin
 * CoreSkool School Management System
 */

require_once dirname(dirname(dirname(__DIR__))) . '/config/config.php';

requireAuth();
requireRole('admin');

$pageTitle = 'Library Management';
$db = Database::getInstance();

// Get books
$booksQuery = $db->query("
    SELECT b.*, 
           COUNT(DISTINCT bi.id) as total_issued,
           COUNT(DISTINCT CASE WHEN bi.return_date IS NULL THEN bi.id END) as currently_issued
    FROM books b
    LEFT JOIN book_issues bi ON b.id = bi.book_id
    WHERE b.school_id = ?
    GROUP BY b.id
    ORDER BY b.title
", [$_SESSION['school_id']]);
$books = $booksQuery->fetchAll();

include APP_PATH . '/views/shared/header.php';
?>

<div class="main-content">
    <?php include APP_PATH . '/views/shared/sidebar.php'; ?>
    <?php include APP_PATH . '/views/shared/topbar.php'; ?>
    
    <div class="content-area">
        <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
            <div>
                <h2 style="margin: 0;">Library Management</h2>
                <p style="color: #6b7280; margin: 0.5rem 0 0 0;">Manage library books and issuance</p>
            </div>
            <div style="display: flex; gap: 0.75rem;">
                <a href="<?php echo BASE_URL; ?>public/admin/library/add-book.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add Book
                </a>
                <a href="<?php echo BASE_URL; ?>public/admin/library/issue.php" class="btn btn-success">
                    <i class="fas fa-hand-holding"></i> Issue Book
                </a>
                <a href="<?php echo BASE_URL; ?>public/admin/library/returns.php" class="btn btn-info">
                    <i class="fas fa-undo"></i> Return Book
                </a>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><strong>Library Books</strong></div>
            <div class="card-body">
                <?php if (empty($books)): ?>
                    <div style="text-align: center; padding: 3rem;">
                        <i class="fas fa-book" style="font-size: 4rem; color: #e5e7eb; margin-bottom: 1rem;"></i>
                        <h3 style="color: #6b7280; margin-bottom: 0.5rem;">No Books in Library</h3>
                        <p style="color: #9ca3af; margin-bottom: 1.5rem;">Start adding books to your library catalog</p>
                        <a href="<?php echo BASE_URL; ?>public/admin/library/add-book.php" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Add Book
                        </a>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>Author</th>
                                    <th>ISBN</th>
                                    <th>Category</th>
                                    <th>Total Copies</th>
                                    <th>Available</th>
                                    <th>Issued</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($books as $book): ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($book['title']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($book['author']); ?></td>
                                        <td><?php echo htmlspecialchars($book['isbn'] ?? 'N/A'); ?></td>
                                        <td><?php echo htmlspecialchars($book['category'] ?? 'N/A'); ?></td>
                                        <td><?php echo $book['total_copies']; ?></td>
                                        <td>
                                            <span class="badge badge-success">
                                                <?php echo $book['total_copies'] - $book['currently_issued']; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge badge-warning">
                                                <?php echo $book['currently_issued']; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="<?php echo BASE_URL; ?>public/admin/library/view.php?id=<?php echo $book['id']; ?>" 
                                               class="btn btn-sm btn-info"><i class="fas fa-eye"></i></a>
                                            <a href="<?php echo BASE_URL; ?>public/admin/library/edit.php?id=<?php echo $book['id']; ?>" 
                                               class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include APP_PATH . '/views/shared/footer.php'; ?>
