<?php
/**
 * Library - Student
 * CoreSkool School Management System
 */

require_once dirname(dirname(dirname(__DIR__))) . '/config/config.php';

requireAuth();
requireRole('student');

$pageTitle = 'Library';
$db = Database::getInstance();
$userId = $_SESSION['user_id'];

// Get available books
$booksQuery = $db->query("
    SELECT b.*, 
           COUNT(DISTINCT bi.id) as total_issued,
           COUNT(DISTINCT CASE WHEN bi.return_date IS NULL THEN bi.id END) as currently_issued,
           (b.total_copies - COUNT(DISTINCT CASE WHEN bi.return_date IS NULL THEN bi.id END)) as available_copies
    FROM books b
    LEFT JOIN book_issues bi ON b.id = bi.book_id
    WHERE b.school_id = ? AND b.status = 'active'
    GROUP BY b.id
    HAVING available_copies > 0
    ORDER BY b.title
", [$_SESSION['school_id']]);
$books = $booksQuery->fetchAll();

// Get my issued books
$myBooksQuery = $db->query("
    SELECT bi.*, b.title, b.author, b.isbn
    FROM book_issues bi
    INNER JOIN books b ON bi.book_id = b.id
    WHERE bi.student_id = ? AND bi.return_date IS NULL
    ORDER BY bi.issue_date DESC
", [$userId]);
$myBooks = $myBooksQuery->fetchAll();

include APP_PATH . '/views/shared/header.php';
?>

<div class="main-content">
    <?php include APP_PATH . '/views/shared/sidebar.php'; ?>
    <?php include APP_PATH . '/views/shared/topbar.php'; ?>
    
    <div class="content-area">
        <div class="page-header" style="margin-bottom: 2rem;">
            <h2>Library</h2>
            <p style="color: #6b7280;">Browse available books and manage your borrowed books</p>
        </div>

        <!-- My Borrowed Books -->
        <?php if (!empty($myBooks)): ?>
            <div class="card" style="margin-bottom: 1.5rem; border-left: 4px solid #667eea;">
                <div class="card-header"><strong><i class="fas fa-bookmark"></i> My Borrowed Books</strong></div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>Author</th>
                                    <th>Issue Date</th>
                                    <th>Due Date</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($myBooks as $book): ?>
                                    <?php
                                    $dueDate = strtotime($book['due_date']);
                                    $today = strtotime('today');
                                    $isOverdue = $dueDate < $today;
                                    ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($book['title']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($book['author']); ?></td>
                                        <td><?php echo date('M d, Y', strtotime($book['issue_date'])); ?></td>
                                        <td><?php echo date('M d, Y', $dueDate); ?></td>
                                        <td>
                                            <?php if ($isOverdue): ?>
                                                <span class="badge badge-danger">Overdue</span>
                                            <?php else: ?>
                                                <span class="badge badge-success">Active</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Available Books -->
        <div class="card">
            <div class="card-header"><strong>Available Books</strong></div>
            <div class="card-body">
                <?php if (empty($books)): ?>
                    <div style="text-align: center; padding: 3rem;">
                        <i class="fas fa-book" style="font-size: 4rem; color: #e5e7eb; margin-bottom: 1rem;"></i>
                        <h3 style="color: #6b7280;">No Books Available</h3>
                        <p style="color: #9ca3af;">All books are currently issued or the library has no books</p>
                    </div>
                <?php else: ?>
                    <div class="row">
                        <?php foreach ($books as $book): ?>
                            <div class="col-md-4" style="margin-bottom: 1.5rem;">
                                <div class="card" style="height: 100%; border: 1px solid #e5e7eb;">
                                    <div class="card-body">
                                        <h5><?php echo htmlspecialchars($book['title']); ?></h5>
                                        <p style="color: #6b7280; margin: 0.5rem 0;">
                                            <strong>Author:</strong> <?php echo htmlspecialchars($book['author']); ?>
                                        </p>
                                        <?php if ($book['isbn']): ?>
                                            <p style="color: #6b7280; margin: 0.5rem 0;">
                                                <strong>ISBN:</strong> <?php echo htmlspecialchars($book['isbn']); ?>
                                            </p>
                                        <?php endif; ?>
                                        <?php if ($book['category']): ?>
                                            <p style="color: #6b7280; margin: 0.5rem 0;">
                                                <strong>Category:</strong> <?php echo htmlspecialchars($book['category']); ?>
                                            </p>
                                        <?php endif; ?>
                                        <p style="margin: 1rem 0;">
                                            <span class="badge badge-success">
                                                <?php echo $book['available_copies']; ?> Available
                                            </span>
                                        </p>
                                        <p style="color: #6b7280; font-size: 0.875rem;">
                                            Contact the librarian to borrow this book
                                        </p>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include APP_PATH . '/views/shared/footer.php'; ?>
