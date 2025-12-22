<?php
/**
 * Teachers List
 * CoreSkool School Management System
 */

require_once dirname(dirname(__DIR__)) . '/config/config.php';

requireAuth();
requireRole('admin');

$pageTitle = 'Teachers Management';
$db = Database::getInstance();

// Get all teachers
$teachersQuery = $db->query(
    "SELECT * FROM users WHERE role = 'teacher' AND school_id = ? ORDER BY created_at DESC",
    [$_SESSION['school_id']]
);
$teachers = $teachersQuery->fetchAll();

include APP_PATH . '/views/shared/header.php';
?>

<div class="main-content">
    <?php include APP_PATH . '/views/shared/sidebar.php'; ?>
    <?php include APP_PATH . '/views/shared/topbar.php'; ?>
    
    <div class="content-area">
        <!-- Page Header -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
            <div>
                <h2 style="margin-bottom: 0.5rem;">
                    <i class="fas fa-chalkboard-teacher"></i> Teachers Management
                </h2>
                <p style="color: #6b7280;">Manage all teachers in your school</p>
            </div>
            <a href="<?php echo BASE_URL; ?>public/admin/teachers/add.php" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add New Teacher
            </a>
        </div>
        
        <!-- Teachers List -->
        <div class="card">
            <div class="card-header">
                <strong>All Teachers (<?php echo count($teachers); ?>)</strong>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Status</th>
                                <th>Joined</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($teachers)): ?>
                                <tr>
                                    <td colspan="7" class="text-center" style="padding: 2rem;">
                                        No teachers found. <a href="<?php echo BASE_URL; ?>public/admin/teachers/add.php">Add your first teacher</a>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($teachers as $teacher): ?>
                                    <tr>
                                        <td><?php echo $teacher['id']; ?></td>
                                        <td><?php echo $teacher['first_name'] . ' ' . $teacher['last_name']; ?></td>
                                        <td><?php echo $teacher['email'] ?: 'N/A'; ?></td>
                                        <td><?php echo $teacher['phone'] ?: 'N/A'; ?></td>
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
                                               class="btn btn-sm btn-warning" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include APP_PATH . '/views/shared/footer.php'; ?>
