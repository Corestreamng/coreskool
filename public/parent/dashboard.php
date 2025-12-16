<?php
/**
 * Parent Dashboard
 * CoreSkool School Management System
 */

require_once dirname(dirname(__DIR__)) . '/config/config.php';

requireAuth();
requireRole('parent');

$pageTitle = 'Parent Dashboard';
$db = Database::getInstance();
$userId = $_SESSION['user_id'];

// Get parent's wards (children)
$wardsQuery = $db->query("
    SELECT u.*, ps.relationship, c.name as class_name
    FROM parent_student ps
    INNER JOIN users u ON ps.student_id = u.id
    LEFT JOIN student_classes sc ON u.id = sc.student_id AND sc.status = 'active'
    LEFT JOIN classes c ON sc.class_id = c.id
    WHERE ps.parent_id = ?
", [$userId]);
$wards = $wardsQuery->fetchAll();
$totalWards = count($wards);

// Get total outstanding fees
$feesQuery = $db->query("
    SELECT COALESCE(SUM(fa.amount), 0) - COALESCE(SUM(p.amount), 0) as outstanding
    FROM parent_student ps
    INNER JOIN student_classes sc ON ps.student_id = sc.student_id AND sc.status = 'active'
    INNER JOIN fee_assignments fa ON sc.class_id = fa.class_id
    LEFT JOIN payments p ON ps.student_id = p.student_id AND fa.fee_type_id = p.fee_type_id AND p.status = 'completed'
    WHERE ps.parent_id = ?
", [$userId]);
$outstandingFees = $feesQuery->fetch()['outstanding'] ?? 0;

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
            <div class="row">
                <div class="col-md-8">
                    <h2>Welcome back, <?php echo $userName; ?>! 👋</h2>
                    <p>Monitor your ward's academic progress and performance.</p>
                </div>
                <div class="col-md-4" style="text-align: right;">
                    <div style="background: rgba(255,255,255,0.2); padding: 1rem; border-radius: 10px; display: inline-block;">
                        <div style="font-size: 1.5rem; font-weight: bold; margin-bottom: 0.25rem;">
                            <?php echo $totalWards; ?>
                        </div>
                        <div style="font-size: 0.875rem; opacity: 0.9;">My Wards</div>
                    </div>
                </div>
            </div>
            
            <div class="welcome-banner-stats" style="margin-top: 1.5rem;">
                <div class="welcome-stat">
                    <div class="welcome-stat-icon">
                        <i class="fas fa-calendar"></i>
                    </div>
                    <div class="welcome-stat-info">
                        <p style="margin: 0; font-size: 0.875rem;"><?php echo $currentDate; ?></p>
                        <h4 style="margin: 0;"><?php echo $currentTime; ?></h4>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Statistics Cards -->
        <div class="row">
            <div class="col-md-3">
                <div class="stats-card">
                    <div class="stats-icon" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stats-value"><?php echo $totalWards; ?></div>
                    <div class="stats-label">Total Wards</div>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="stats-card">
                    <div class="stats-icon" style="background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); color: white;">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                    <div class="stats-value">₦<?php echo number_format($outstandingFees, 2); ?></div>
                    <div class="stats-label">Outstanding Fees</div>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="stats-card">
                    <div class="stats-icon" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white;">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <div class="stats-value">--</div>
                    <div class="stats-label">New Messages</div>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="stats-card">
                    <div class="stats-icon" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); color: white;">
                        <i class="fas fa-bell"></i>
                    </div>
                    <div class="stats-value">--</div>
                    <div class="stats-label">Notifications</div>
                </div>
            </div>
        </div>
        
        <!-- Wards Information -->
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <strong>My Wards</strong>
                    </div>
                    <div class="card-body">
                        <?php if (empty($wards)): ?>
                            <p class="text-center" style="color: #6b7280; padding: 2rem;">
                                No wards assigned to your account
                            </p>
                        <?php else: ?>
                            <div class="row">
                                <?php foreach ($wards as $ward): ?>
                                    <?php
                                    // Get ward's attendance
                                    $attendQuery = $db->query("
                                        SELECT 
                                            COUNT(*) as total,
                                            SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present
                                        FROM attendance 
                                        WHERE student_id = ? AND MONTH(date) = MONTH(CURDATE())
                                    ", [$ward['id']]);
                                    $attend = $attendQuery->fetch();
                                    $attendRate = $attend['total'] > 0 ? round(($attend['present'] / $attend['total']) * 100, 1) : 0;
                                    ?>
                                    
                                    <div class="col-md-6">
                                        <div class="card" style="margin-bottom: 1rem;">
                                            <div class="card-body">
                                                <div style="display: flex; align-items: center; gap: 1rem;">
                                                    <img src="<?php echo getAvatarUrl($ward['avatar']); ?>" 
                                                         alt="Avatar" 
                                                         style="width: 80px; height: 80px; border-radius: 50%; object-fit: cover;">
                                                    
                                                    <div style="flex: 1;">
                                                        <h4 style="margin-bottom: 0.5rem;">
                                                            <?php echo $ward['first_name'] . ' ' . $ward['last_name']; ?>
                                                        </h4>
                                                        <div style="font-size: 0.875rem; color: #6b7280;">
                                                            <p style="margin: 0.25rem 0;">
                                                                <strong>Matric:</strong> <?php echo $ward['matric_number']; ?>
                                                            </p>
                                                            <p style="margin: 0.25rem 0;">
                                                                <strong>Class:</strong> <?php echo $ward['class_name'] ?: 'Not Assigned'; ?>
                                                            </p>
                                                            <p style="margin: 0.25rem 0;">
                                                                <strong>Attendance:</strong> <?php echo $attendRate; ?>%
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                <div style="display: flex; gap: 0.5rem; margin-top: 1rem;">
                                                    <a href="<?php echo BASE_URL; ?>public/parent/wards/view.php?id=<?php echo $ward['id']; ?>" 
                                                       class="btn btn-sm btn-primary">
                                                        <i class="fas fa-eye"></i> View Details
                                                    </a>
                                                    <a href="<?php echo BASE_URL; ?>public/parent/results/view.php?student_id=<?php echo $ward['id']; ?>" 
                                                       class="btn btn-sm btn-success">
                                                        <i class="fas fa-graduation-cap"></i> Results
                                                    </a>
                                                    <a href="<?php echo BASE_URL; ?>public/parent/attendance/view.php?student_id=<?php echo $ward['id']; ?>" 
                                                       class="btn btn-sm btn-info">
                                                        <i class="fas fa-calendar-check"></i> Attendance
                                                    </a>
                                                </div>
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
        
        <!-- Quick Actions -->
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <strong>Quick Actions</strong>
                    </div>
                    <div class="card-body">
                        <div style="display: flex; gap: 0.75rem; flex-wrap: wrap;">
                            <a href="<?php echo BASE_URL; ?>public/parent/payments/index.php" class="btn btn-primary">
                                <i class="fas fa-money-bill"></i> Make Payment
                            </a>
                            <a href="<?php echo BASE_URL; ?>public/parent/messages/index.php" class="btn btn-success">
                                <i class="fas fa-envelope"></i> View Messages
                            </a>
                            <a href="<?php echo BASE_URL; ?>public/parent/timetable/index.php" class="btn btn-info">
                                <i class="fas fa-clock"></i> View Timetable
                            </a>
                            <a href="<?php echo BASE_URL; ?>public/parent/reports/index.php" class="btn btn-warning">
                                <i class="fas fa-file-alt"></i> Download Reports
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include APP_PATH . '/views/shared/footer.php'; ?>
