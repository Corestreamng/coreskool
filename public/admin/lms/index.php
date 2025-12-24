<?php
/**
 * LMS (Learning Management System) - Courses List
 * CoreSkool School Management System
 */

require_once dirname(dirname(dirname(__DIR__))) . '/config/config.php';

requireAuth();
requireRole('admin');

$pageTitle = 'Learning Management System';
$db = Database::getInstance();

// Handle delete
if (isset($_POST['delete_id'])) {
    $deleteId = (int)$_POST['delete_id'];
    try {
        $db->query("UPDATE courses SET status = 'archived' WHERE id = ?", [$deleteId]);
        setFlash('success', 'Course archived successfully');
        redirect('admin/lms/index.php');
    } catch (Exception $e) {
        setFlash('danger', 'Failed to archive course');
    }
}

// Pagination
$page = $_GET['page'] ?? 1;
$search = $_GET['search'] ?? '';
$statusFilter = $_GET['status'] ?? '';

$where = "WHERE c.school_id = ?";
$params = [$_SESSION['school_id']];

if ($search) {
    $where .= " AND (c.title LIKE ? OR c.description LIKE ? OR s.name LIKE ?)";
    $searchTerm = "%$search%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
}

if ($statusFilter) {
    $where .= " AND c.status = ?";
    $params[] = $statusFilter;
}

// Get total count
$countSql = "SELECT COUNT(*) as total FROM courses c $where";
$totalStmt = $db->query($countSql, $params);
$totalItems = $totalStmt->fetch()['total'];

// Pagination calculation
$pagination = paginate($totalItems, $page, 20);

// Get courses
$sql = "SELECT c.*, 
        s.name as subject_name,
        u.first_name as teacher_first_name,
        u.last_name as teacher_last_name,
        COUNT(DISTINCT ce.id) as enrolled_count
        FROM courses c
        LEFT JOIN subjects s ON c.subject_id = s.id
        LEFT JOIN users u ON c.teacher_id = u.id
        LEFT JOIN course_enrollments ce ON c.id = ce.course_id AND ce.status = 'active'
        $where
        GROUP BY c.id
        ORDER BY c.created_at DESC
        LIMIT {$pagination['items_per_page']} OFFSET {$pagination['offset']}";

$stmt = $db->query($sql, $params);
$courses = $stmt->fetchAll();

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
                <strong>Courses & Learning Materials</strong>
                <a href="<?php echo BASE_URL; ?>public/admin/lms/add.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add New Course
                </a>
            </div>
            
            <div class="card-body">
                <!-- Search and Filter -->
                <form method="GET" class="row" style="margin-bottom: 1.5rem;">
                    <div class="col-md-5">
                        <input type="text" name="search" class="form-control" placeholder="Search by course title, subject, or description..." value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    <div class="col-md-3">
                        <select name="status" class="form-control">
                            <option value="">All Statuses</option>
                            <option value="draft" <?php echo $statusFilter == 'draft' ? 'selected' : ''; ?>>Draft</option>
                            <option value="published" <?php echo $statusFilter == 'published' ? 'selected' : ''; ?>>Published</option>
                            <option value="archived" <?php echo $statusFilter == 'archived' ? 'selected' : ''; ?>>Archived</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-info">
                            <i class="fas fa-search"></i> Search
                        </button>
                    </div>
                    <div class="col-md-2">
                        <a href="<?php echo BASE_URL; ?>public/admin/lms/index.php" class="btn btn-secondary">
                            <i class="fas fa-redo"></i> Reset
                        </a>
                    </div>
                </form>
                
                <!-- Courses Table -->
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Course Title</th>
                                <th>Subject</th>
                                <th>Teacher</th>
                                <th>Enrolled</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($courses)): ?>
                                <tr>
                                    <td colspan="7" class="text-center">No courses found</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($courses as $course): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo htmlspecialchars($course['title']); ?></strong>
                                            <?php if ($course['description']): ?>
                                                <br><small style="color: #6b7280;"><?php echo substr(htmlspecialchars($course['description']), 0, 50) . '...'; ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo $course['subject_name'] ?: 'N/A'; ?></td>
                                        <td><?php echo $course['teacher_first_name'] . ' ' . $course['teacher_last_name']; ?></td>
                                        <td><?php echo $course['enrolled_count']; ?> students</td>
                                        <td>
                                            <span class="badge badge-<?php 
                                                echo $course['status'] === 'published' ? 'success' : 
                                                    ($course['status'] === 'draft' ? 'warning' : 'secondary'); 
                                            ?>">
                                                <?php echo ucfirst($course['status']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo formatDate($course['created_at']); ?></td>
                                        <td>
                                            <a href="<?php echo BASE_URL; ?>public/admin/lms/view.php?id=<?php echo $course['id']; ?>" 
                                               class="btn btn-sm btn-info" title="View">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="<?php echo BASE_URL; ?>public/admin/lms/edit.php?id=<?php echo $course['id']; ?>" 
                                               class="btn btn-sm btn-primary" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to archive this course?');">
                                                <input type="hidden" name="delete_id" value="<?php echo $course['id']; ?>">
                                                <button type="submit" class="btn btn-sm btn-danger" title="Archive">
                                                    <i class="fas fa-archive"></i>
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
                            <a href="?page=<?php echo $pagination['current_page'] - 1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo $statusFilter; ?>">
                                <i class="fas fa-chevron-left"></i> Previous
                            </a>
                        <?php endif; ?>
                        
                        <?php for ($i = 1; $i <= $pagination['total_pages']; $i++): ?>
                            <?php if ($i == $pagination['current_page']): ?>
                                <span class="active"><?php echo $i; ?></span>
                            <?php else: ?>
                                <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo $statusFilter; ?>">
                                    <?php echo $i; ?>
                                </a>
                            <?php endif; ?>
                        <?php endfor; ?>
                        
                        <?php if ($pagination['has_next']): ?>
                            <a href="?page=<?php echo $pagination['current_page'] + 1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo $statusFilter; ?>">
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
