<?php
/**
 * Payments - Parent
 * CoreSkool School Management System
 */

require_once dirname(dirname(dirname(__DIR__))) . '/config/config.php';

requireAuth();
requireRole('parent');

$pageTitle = 'Payments';
$db = Database::getInstance();
$userId = $_SESSION['user_id'];

// Get wards
$wardsQuery = $db->query("
    SELECT u.id, u.first_name, u.last_name, u.matric_number
    FROM parent_student ps
    INNER JOIN users u ON ps.student_id = u.id
    WHERE ps.parent_id = ?
", [$userId]);
$wards = $wardsQuery->fetchAll();

// Get ward IDs
$wardIds = array_column($wards, 'id');

// Get payments for all wards
$payments = [];
if (!empty($wardIds)) {
    $placeholders = implode(',', array_fill(0, count($wardIds), '?'));
    $paymentsQuery = $db->query("
        SELECT p.*, u.first_name, u.last_name, ft.name as fee_type_name
        FROM payments p
        INNER JOIN users u ON p.student_id = u.id
        INNER JOIN fee_types ft ON p.fee_type_id = ft.id
        WHERE p.student_id IN ($placeholders)
        ORDER BY p.payment_date DESC
    ", $wardIds);
    $payments = $paymentsQuery->fetchAll();
}

// Get outstanding fees
$outstanding = [];
if (!empty($wardIds)) {
    $outstandingQuery = $db->query("
        SELECT 
            u.first_name, u.last_name,
            ft.name as fee_type_name,
            fa.amount
        FROM fee_assignments fa
        INNER JOIN student_classes sc ON fa.class_id = sc.class_id
        INNER JOIN users u ON sc.student_id = u.id
        INNER JOIN fee_types ft ON fa.fee_type_id = ft.id
        LEFT JOIN payments p ON fa.fee_type_id = p.fee_type_id AND sc.student_id = p.student_id AND p.status = 'completed'
        WHERE sc.student_id IN ($placeholders) AND sc.status = 'active' AND p.id IS NULL
    ", $wardIds);
    $outstanding = $outstandingQuery->fetchAll();
}

include APP_PATH . '/views/shared/header.php';
?>

<div class="main-content">
    <?php include APP_PATH . '/views/shared/sidebar.php'; ?>
    <?php include APP_PATH . '/views/shared/topbar.php'; ?>
    
    <div class="content-area">
        <div class="page-header" style="margin-bottom: 2rem;">
            <h2>Payments</h2>
            <p style="color: #6b7280;">Manage payments for your wards</p>
        </div>

        <!-- Outstanding Fees -->
        <?php if (!empty($outstanding)): ?>
            <div class="card" style="margin-bottom: 1.5rem; border-left: 4px solid #ef4444;">
                <div class="card-header" style="background-color: #fef2f2;">
                    <strong style="color: #dc2626;"><i class="fas fa-exclamation-triangle"></i> Outstanding Fees</strong>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Student</th>
                                    <th>Fee Type</th>
                                    <th>Amount</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($outstanding as $fee): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($fee['first_name'] . ' ' . $fee['last_name']); ?></td>
                                        <td><?php echo htmlspecialchars($fee['fee_type_name']); ?></td>
                                        <td><strong>₦<?php echo number_format($fee['amount'], 2); ?></strong></td>
                                        <td>
                                            <a href="<?php echo BASE_URL; ?>public/parent/payments/make-payment.php" 
                                               class="btn btn-sm btn-primary">
                                                <i class="fas fa-money-bill"></i> Pay Now
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Payment History -->
        <div class="card">
            <div class="card-header"><strong>Payment History</strong></div>
            <div class="card-body">
                <?php if (empty($payments)): ?>
                    <div style="text-align: center; padding: 3rem;">
                        <i class="fas fa-receipt" style="font-size: 4rem; color: #e5e7eb; margin-bottom: 1rem;"></i>
                        <h3 style="color: #6b7280;">No Payment History</h3>
                        <p style="color: #9ca3af;">Payment records will appear here</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Receipt No.</th>
                                    <th>Student</th>
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
                                        <td><?php echo htmlspecialchars($payment['fee_type_name']); ?></td>
                                        <td>₦<?php echo number_format($payment['amount'], 2); ?></td>
                                        <td><?php echo date('M d, Y', strtotime($payment['payment_date'])); ?></td>
                                        <td><?php echo ucfirst($payment['payment_method']); ?></td>
                                        <td>
                                            <span class="badge badge-<?php echo $payment['status'] === 'completed' ? 'success' : 'warning'; ?>">
                                                <?php echo ucfirst($payment['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="<?php echo BASE_URL; ?>public/parent/payments/receipt.php?id=<?php echo $payment['id']; ?>" 
                                               class="btn btn-sm btn-info">
                                                <i class="fas fa-download"></i> Receipt
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
