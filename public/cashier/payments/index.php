<?php
/**
 * Payments Management - Cashier
 * CoreSkool School Management System
 */

require_once dirname(dirname(dirname(__DIR__))) . '/config/config.php';

requireAuth();
requireRole('cashier');

$pageTitle = 'Payments Management';
$db = Database::getInstance();

// Get payments
$paymentsQuery = $db->query("
    SELECT p.*, u.first_name, u.last_name, u.matric_number, ft.name as fee_type_name
    FROM payments p
    INNER JOIN users u ON p.student_id = u.id
    INNER JOIN fee_types ft ON p.fee_type_id = ft.id
    WHERE p.school_id = ?
    ORDER BY p.payment_date DESC
    LIMIT 100
", [$_SESSION['school_id']]);
$payments = $paymentsQuery->fetchAll();

// Get today's statistics
$todayStats = $db->query("
    SELECT 
        COUNT(*) as count,
        SUM(amount) as total
    FROM payments
    WHERE DATE(payment_date) = CURDATE() AND status = 'completed' AND school_id = ?
", [$_SESSION['school_id']])->fetch();

include APP_PATH . '/views/shared/header.php';
?>

<div class="main-content">
    <?php include APP_PATH . '/views/shared/sidebar.php'; ?>
    <?php include APP_PATH . '/views/shared/topbar.php'; ?>
    
    <div class="content-area">
        <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
            <div>
                <h2 style="margin: 0;">Payments Management</h2>
                <p style="color: #6b7280; margin: 0.5rem 0 0 0;">Record and manage fee payments</p>
            </div>
            <a href="<?php echo BASE_URL; ?>public/cashier/payments/record.php" class="btn btn-primary">
                <i class="fas fa-plus"></i> Record Payment
            </a>
        </div>

        <!-- Today's Stats -->
        <div class="row" style="margin-bottom: 2rem;">
            <div class="col-md-6">
                <div class="stats-card">
                    <div class="stats-icon" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white;">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                    <div class="stats-value">₦<?php echo number_format($todayStats['total'] ?? 0, 2); ?></div>
                    <div class="stats-label">Today's Collection</div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="stats-card">
                    <div class="stats-icon" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                        <i class="fas fa-receipt"></i>
                    </div>
                    <div class="stats-value"><?php echo $todayStats['count'] ?? 0; ?></div>
                    <div class="stats-label">Today's Transactions</div>
                </div>
            </div>
        </div>

        <!-- Payments List -->
        <div class="card">
            <div class="card-header"><strong>Payment Records</strong></div>
            <div class="card-body">
                <?php if (empty($payments)): ?>
                    <div style="text-align: center; padding: 3rem;">
                        <i class="fas fa-receipt" style="font-size: 4rem; color: #e5e7eb; margin-bottom: 1rem;"></i>
                        <h3 style="color: #6b7280;">No Payments Recorded</h3>
                        <p style="color: #9ca3af;">Start recording fee payments</p>
                        <a href="<?php echo BASE_URL; ?>public/cashier/payments/record.php" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Record Payment
                        </a>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Receipt No.</th>
                                    <th>Student</th>
                                    <th>Matric No.</th>
                                    <th>Fee Type</th>
                                    <th>Amount</th>
                                    <th>Payment Date</th>
                                    <th>Method</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($payments as $payment): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($payment['receipt_number'] ?? 'N/A'); ?></td>
                                        <td><?php echo htmlspecialchars($payment['first_name'] . ' ' . $payment['last_name']); ?></td>
                                        <td><?php echo htmlspecialchars($payment['matric_number']); ?></td>
                                        <td><?php echo htmlspecialchars($payment['fee_type_name']); ?></td>
                                        <td><strong>₦<?php echo number_format($payment['amount'], 2); ?></strong></td>
                                        <td><?php echo date('M d, Y', strtotime($payment['payment_date'])); ?></td>
                                        <td><?php echo ucfirst($payment['payment_method']); ?></td>
                                        <td>
                                            <span class="badge badge-<?php echo $payment['status'] === 'completed' ? 'success' : 'warning'; ?>">
                                                <?php echo ucfirst($payment['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="<?php echo BASE_URL; ?>public/cashier/payments/receipt.php?id=<?php echo $payment['id']; ?>" 
                                               class="btn btn-sm btn-info" title="Print Receipt">
                                                <i class="fas fa-print"></i>
                                            </a>
                                            <a href="<?php echo BASE_URL; ?>public/cashier/payments/view.php?id=<?php echo $payment['id']; ?>" 
                                               class="btn btn-sm btn-primary" title="View Details">
                                                <i class="fas fa-eye"></i>
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
