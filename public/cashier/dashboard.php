<?php
/**
 * Cashier Dashboard
 * CoreSkool School Management System
 */

require_once dirname(dirname(__DIR__)) . '/config/config.php';

requireAuth();
requireRole('cashier');

$pageTitle = 'Cashier Dashboard';
$db = Database::getInstance();

// Get statistics
$todayQuery = $db->query("SELECT COALESCE(SUM(amount), 0) as total FROM payments WHERE DATE(payment_date) = CURDATE() AND status = 'completed' AND school_id = ?", [$_SESSION['school_id']]);
$todayCollection = $todayQuery->fetch()['total'];

$monthQuery = $db->query("SELECT COALESCE(SUM(amount), 0) as total FROM payments WHERE MONTH(payment_date) = MONTH(CURDATE()) AND YEAR(payment_date) = YEAR(CURDATE()) AND status = 'completed' AND school_id = ?", [$_SESSION['school_id']]);
$monthCollection = $monthQuery->fetch()['total'];

$pendingQuery = $db->query("SELECT COUNT(*) as count FROM payments WHERE status = 'pending' AND school_id = ?", [$_SESSION['school_id']]);
$pendingPayments = $pendingQuery->fetch()['count'];

$currentDate = date('l, F j, Y');
$currentTime = date('h:i A');

include APP_PATH . '/views/shared/header.php';
?>

<div class="main-content">
    <?php include APP_PATH . '/views/shared/sidebar.php'; ?>
    <?php include APP_PATH . '/views/shared/topbar.php'; ?>
    
    <div class="content-area">
        <!-- Welcome Banner -->
        <div class="welcome-banner">
            <h2>Welcome back, <?php echo $userName; ?>! 👋</h2>
            <p>Fee collection and payment management dashboard.</p>
            <div class="welcome-banner-stats" style="margin-top: 1.5rem;">
                <div class="welcome-stat">
                    <div class="welcome-stat-icon"><i class="fas fa-calendar"></i></div>
                    <div class="welcome-stat-info">
                        <p style="margin: 0; font-size: 0.875rem;"><?php echo $currentDate; ?></p>
                        <h4 style="margin: 0;"><?php echo $currentTime; ?></h4>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Statistics Cards -->
        <div class="row">
            <div class="col-md-4">
                <div class="stats-card">
                    <div class="stats-icon" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                    <div class="stats-value">₦<?php echo number_format($todayCollection, 2); ?></div>
                    <div class="stats-label">Today's Collection</div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="stats-card">
                    <div class="stats-icon" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white;">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <div class="stats-value">₦<?php echo number_format($monthCollection, 2); ?></div>
                    <div class="stats-label">This Month's Collection</div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="stats-card">
                    <div class="stats-icon" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); color: white;">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stats-value"><?php echo $pendingPayments; ?></div>
                    <div class="stats-label">Pending Payments</div>
                </div>
            </div>
        </div>
        
        <!-- Quick Actions -->
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header"><strong>Quick Actions</strong></div>
                    <div class="card-body">
                        <div style="display: flex; gap: 0.75rem; flex-wrap: wrap;">
                            <a href="<?php echo BASE_URL; ?>public/cashier/payments/record.php" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Record Payment
                            </a>
                            <a href="<?php echo BASE_URL; ?>public/cashier/fees/manage.php" class="btn btn-success">
                                <i class="fas fa-cog"></i> Manage Fees
                            </a>
                            <a href="<?php echo BASE_URL; ?>public/cashier/reports/daily.php" class="btn btn-info">
                                <i class="fas fa-file-alt"></i> Daily Report
                            </a>
                            <a href="<?php echo BASE_URL; ?>public/cashier/reports/monthly.php" class="btn btn-warning">
                                <i class="fas fa-chart-line"></i> Monthly Report
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include APP_PATH . '/views/shared/footer.php'; ?>
