<?php
/**
 * Fees & Payments Management - Admin
 * CoreSkool School Management System
 */

require_once dirname(dirname(dirname(__DIR__))) . '/config/config.php';

requireAuth();
requireRole('admin');

$pageTitle = 'Fees & Payments';
$db = Database::getInstance();

// Get fee types
$feeTypesQuery = $db->query("
    SELECT ft.*, COUNT(DISTINCT fa.id) as assignments_count
    FROM fee_types ft
    LEFT JOIN fee_assignments fa ON ft.id = fa.fee_type_id
    WHERE ft.school_id = ?
    GROUP BY ft.id
    ORDER BY ft.name
", [$_SESSION['school_id']]);
$feeTypes = $feeTypesQuery->fetchAll();

// Get payment statistics
$statsQuery = $db->query("
    SELECT 
        COUNT(*) as total_payments,
        SUM(CASE WHEN status = 'completed' THEN amount ELSE 0 END) as total_collected,
        SUM(CASE WHEN status = 'pending' THEN amount ELSE 0 END) as total_pending
    FROM payments
    WHERE school_id = ?
", [$_SESSION['school_id']]);
$stats = $statsQuery->fetch();

include APP_PATH . '/views/shared/header.php';
?>

<div class="main-content">
    <?php include APP_PATH . '/views/shared/sidebar.php'; ?>
    <?php include APP_PATH . '/views/shared/topbar.php'; ?>
    
    <div class="content-area">
        <div class="page-header" style="margin-bottom: 2rem;">
            <h2>Fees & Payments Management</h2>
            <p style="color: #6b7280;">Manage fee types, assignments, and track payments</p>
        </div>

        <!-- Statistics Cards -->
        <div class="row" style="margin-bottom: 2rem;">
            <div class="col-md-4">
                <div class="stats-card">
                    <div class="stats-icon" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white;">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                    <div class="stats-value">₦<?php echo number_format($stats['total_collected'] ?? 0, 2); ?></div>
                    <div class="stats-label">Total Collected</div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="stats-card">
                    <div class="stats-icon" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); color: white;">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stats-value">₦<?php echo number_format($stats['total_pending'] ?? 0, 2); ?></div>
                    <div class="stats-label">Pending Payments</div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="stats-card">
                    <div class="stats-icon" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                        <i class="fas fa-receipt"></i>
                    </div>
                    <div class="stats-value"><?php echo $stats['total_payments'] ?? 0; ?></div>
                    <div class="stats-label">Total Transactions</div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="row" style="margin-bottom: 2rem;">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header"><strong>Quick Actions</strong></div>
                    <div class="card-body">
                        <div style="display: flex; gap: 0.75rem; flex-wrap: wrap;">
                            <a href="<?php echo BASE_URL; ?>public/admin/fees/types.php" class="btn btn-primary">
                                <i class="fas fa-list"></i> Manage Fee Types
                            </a>
                            <a href="<?php echo BASE_URL; ?>public/admin/fees/assign.php" class="btn btn-success">
                                <i class="fas fa-link"></i> Assign Fees to Classes
                            </a>
                            <a href="<?php echo BASE_URL; ?>public/admin/payments/index.php" class="btn btn-info">
                                <i class="fas fa-receipt"></i> View All Payments
                            </a>
                            <a href="<?php echo BASE_URL; ?>public/admin/payments/record.php" class="btn btn-warning">
                                <i class="fas fa-plus"></i> Record Payment
                            </a>
                            <a href="<?php echo BASE_URL; ?>public/admin/reports/financial.php" class="btn btn-secondary">
                                <i class="fas fa-chart-line"></i> Financial Reports
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Fee Types List -->
        <div class="card">
            <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                <strong>Fee Types</strong>
                <a href="<?php echo BASE_URL; ?>public/admin/fees/types.php" class="btn btn-sm btn-primary">
                    <i class="fas fa-plus"></i> Add Fee Type
                </a>
            </div>
            <div class="card-body">
                <?php if (empty($feeTypes)): ?>
                    <div style="text-align: center; padding: 3rem;">
                        <i class="fas fa-money-bill" style="font-size: 4rem; color: #e5e7eb; margin-bottom: 1rem;"></i>
                        <h3 style="color: #6b7280; margin-bottom: 0.5rem;">No Fee Types Defined</h3>
                        <p style="color: #9ca3af; margin-bottom: 1.5rem;">Create fee types like Tuition, Books, Uniform, etc.</p>
                        <a href="<?php echo BASE_URL; ?>public/admin/fees/types.php" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Add Fee Type
                        </a>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Fee Name</th>
                                    <th>Description</th>
                                    <th>Amount</th>
                                    <th>Class Assignments</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($feeTypes as $feeType): ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($feeType['name']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($feeType['description'] ?? 'N/A'); ?></td>
                                        <td>₦<?php echo number_format($feeType['amount'], 2); ?></td>
                                        <td><?php echo $feeType['assignments_count']; ?> classes</td>
                                        <td>
                                            <span class="badge badge-<?php echo $feeType['status'] === 'active' ? 'success' : 'secondary'; ?>">
                                                <?php echo ucfirst($feeType['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="<?php echo BASE_URL; ?>public/admin/fees/edit.php?id=<?php echo $feeType['id']; ?>" 
                                               class="btn btn-sm btn-warning" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="<?php echo BASE_URL; ?>public/admin/fees/assign.php?fee_id=<?php echo $feeType['id']; ?>" 
                                               class="btn btn-sm btn-info" title="Assign to Classes">
                                                <i class="fas fa-link"></i>
                                            </a>
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
