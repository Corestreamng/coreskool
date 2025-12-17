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

// Pagination and filters
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

if ($subjectFilter) {
    $where .= " AND EXISTS (SELECT 1 FROM subject_teachers st WHERE st.teacher_id = u.id AND st.subject_id = ? AND st.status = 'active')";
    $params[] = $subjectFilter;
}

// Get total count
$countSql = "SELECT COUNT(*) as total FROM users u $where";
$totalStmt = $db->query($countSql, $params);
$totalItems = $totalStmt->fetch()['total'];

// Pagination calculation
$pagination = paginate($totalItems, $page, 20);

// Get teachers
$sql = "SELECT u.id, u.first_name, u.last_name, u.email, u.phone, u.status,
        GROUP_CONCAT(DISTINCT s.name SEPARATOR ', ') as subjects,
        COUNT(DISTINCT st.subject_id) as subject_count,
        u.created_at
        FROM users u
        LEFT JOIN subject_teachers st ON u.id = st.teacher_id AND st.status = 'active'
        LEFT JOIN subjects s ON st.subject_id = s.id
        $where
        GROUP BY u.id
        ORDER BY u.created_at DESC
        LIMIT {$pagination['items_per_page']} OFFSET {$pagination['offset']}";

$stmt = $db->query($sql, $params);
$teachers = $stmt->fetchAll();

// Get subjects for filter
$subjectsStmt = $db->query("SELECT id, name FROM subjects WHERE school_id = ? AND status = 'active' ORDER BY name", [$_SESSION['school_id']]);
$subjects = $subjectsStmt->fetchAll();

include APP_PATH . '/views/shared/header.php';
?>

<div class="main-content">
    <?php include APP_PATH . '/views/shared/sidebar.php'; ?>
    <?php include APP_PATH . '/views/shared/topbar.php'; ?>
    
    <div class="content-area">
        <div class="page-header">
            <h2>Manage Teachers</h2>
            <a href="<?php echo BASE_URL; ?>public/admin/teachers/add.php" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add New Teacher
            </a>
        </div>

        <!-- Filters -->
        <div class="card" style="margin-bottom: 1.5rem;">
            <div class="card-body">
                <form method="GET" action="" class="row">
                    <div class="col-md-5">
                        <input type="text" name="search" class="form-control" 
                               placeholder="Search by name, email, phone..." 
                               value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    <div class="col-md-4">
                        <select name="subject" class="form-control">
                            <option value="">All Subjects</option>
                            <?php foreach ($subjects as $subject): ?>
                                <option value="<?php echo $subject['id']; ?>" 
                                    <?php echo $subjectFilter == $subject['id'] ? 'selected' : ''; ?>>
                                    <?php echo $subject['name']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i> Search
                        </button>
                        <a href="<?php echo BASE_URL; ?>public/admin/teachers/index.php" class="btn btn-secondary">
                            <i class="fas fa-redo"></i> Reset
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Teachers List -->
        <div class="card">
            <div class="card-header">
                <strong>Teachers List (<?php echo $totalItems; ?> total)</strong>
            </div>
            <div class="card-body">
                <?php if (empty($teachers)): ?>
                    <p class="text-center text-muted">No teachers found. 
                        <a href="<?php echo BASE_URL; ?>public/admin/teachers/add.php">Add your first teacher</a>
                    </p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Subjects</th>
                                    <th>Status</th>
                                    <th>Registered</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($teachers as $teacher): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo $teacher['first_name'] . ' ' . $teacher['last_name']; ?></strong>
                                        </td>
                                        <td><?php echo $teacher['email'] ?: 'N/A'; ?></td>
                                        <td><?php echo $teacher['phone'] ?: 'N/A'; ?></td>
                                        <td>
                                            <span class="badge badge-info">
                                                <?php echo $teacher['subject_count']; ?> subject(s)
                                            </span>
                                            <?php if ($teacher['subjects']): ?>
                                                <small class="text-muted d-block" style="margin-top: 0.25rem;">
                                                    <?php echo shortenText($teacher['subjects'], 50); ?>
                                                </small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge badge-<?php echo $teacher['status'] === 'active' ? 'success' : 'danger'; ?>">
                                                <?php echo ucfirst($teacher['status']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo formatDate($teacher['created_at']); ?></td>
                                        <td>
                                            <a href="<?php echo BASE_URL; ?>public/admin/teachers/view.php?id=<?php echo $teacher['id']; ?>" 
                                               class="btn btn-sm btn-info" title="View">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="<?php echo BASE_URL; ?>public/admin/teachers/edit.php?id=<?php echo $teacher['id']; ?>" 
                                               class="btn btn-sm btn-primary" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form method="POST" style="display: inline;" 
                                                  onsubmit="return confirm('Are you sure you want to delete this teacher?');">
                                                <input type="hidden" name="delete_id" value="<?php echo $teacher['id']; ?>">
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
                                            <a href="?page=<?php echo $pagination['current_page'] - 1; ?>&search=<?php echo urlencode($search); ?>&subject=<?php echo $subjectFilter; ?>" 
                                               class="btn btn-sm btn-secondary">Previous</a>
                                        </li>
                                    <?php endif; ?>
                                    
                                    <li style="padding: 0.5rem 1rem;">
                                        Page <?php echo $pagination['current_page']; ?> of <?php echo $pagination['total_pages']; ?>
                                    </li>
                                    
                                    <?php if ($pagination['has_next']): ?>
                                        <li>
                                            <a href="?page=<?php echo $pagination['current_page'] + 1; ?>&search=<?php echo urlencode($search); ?>&subject=<?php echo $subjectFilter; ?>" 
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
