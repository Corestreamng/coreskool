<?php
/**
 * Reports - Admin
 * CoreSkool School Management System
 */

require_once dirname(dirname(dirname(__DIR__))) . '/config/config.php';

requireAuth();
requireRole('admin');

$pageTitle = 'Reports';
$db = Database::getInstance();

include APP_PATH . '/views/shared/header.php';
?>

<div class="main-content">
    <?php include APP_PATH . '/views/shared/sidebar.php'; ?>
    <?php include APP_PATH . '/views/shared/topbar.php'; ?>
    
    <div class="content-area">
        <div class="page-header" style="margin-bottom: 2rem;">
            <h2>Reports & Analytics</h2>
            <p style="color: #6b7280;">Generate comprehensive reports for your school</p>
        </div>

        <!-- Academic Reports -->
        <div class="card" style="margin-bottom: 1.5rem;">
            <div class="card-header"><strong><i class="fas fa-graduation-cap"></i> Academic Reports</strong></div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6" style="margin-bottom: 1rem;">
                        <div style="border: 1px solid #e5e7eb; border-radius: 8px; padding: 1.5rem;">
                            <h4 style="margin-top: 0;"><i class="fas fa-chart-line"></i> Student Performance Report</h4>
                            <p style="color: #6b7280;">Comprehensive analysis of student academic performance by class, subject, or term</p>
                            <a href="<?php echo BASE_URL; ?>public/admin/reports/student-performance.php" class="btn btn-primary">
                                <i class="fas fa-file-alt"></i> Generate Report
                            </a>
                        </div>
                    </div>
                    
                    <div class="col-md-6" style="margin-bottom: 1rem;">
                        <div style="border: 1px solid #e5e7eb; border-radius: 8px; padding: 1.5rem;">
                            <h4 style="margin-top: 0;"><i class="fas fa-file-alt"></i> Exam Analysis Report</h4>
                            <p style="color: #6b7280;">Detailed analysis of examination results, grades distribution, and subject performance</p>
                            <a href="<?php echo BASE_URL; ?>public/admin/reports/exam-analysis.php" class="btn btn-primary">
                                <i class="fas fa-file-alt"></i> Generate Report
                            </a>
                        </div>
                    </div>
                    
                    <div class="col-md-6" style="margin-bottom: 1rem;">
                        <div style="border: 1px solid #e5e7eb; border-radius: 8px; padding: 1.5rem;">
                            <h4 style="margin-top: 0;"><i class="fas fa-calendar-check"></i> Attendance Report</h4>
                            <p style="color: #6b7280;">Student attendance statistics by class, date range, or individual student</p>
                            <a href="<?php echo BASE_URL; ?>public/admin/reports/attendance.php" class="btn btn-primary">
                                <i class="fas fa-file-alt"></i> Generate Report
                            </a>
                        </div>
                    </div>
                    
                    <div class="col-md-6" style="margin-bottom: 1rem;">
                        <div style="border: 1px solid #e5e7eb; border-radius: 8px; padding: 1.5rem;">
                            <h4 style="margin-top: 0;"><i class="fas fa-trophy"></i> Class Ranking Report</h4>
                            <p style="color: #6b7280;">Student rankings and positions by class, subject, or overall performance</p>
                            <a href="<?php echo BASE_URL; ?>public/admin/reports/class-ranking.php" class="btn btn-primary">
                                <i class="fas fa-file-alt"></i> Generate Report
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Financial Reports -->
        <div class="card" style="margin-bottom: 1.5rem;">
            <div class="card-header"><strong><i class="fas fa-money-bill-wave"></i> Financial Reports</strong></div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6" style="margin-bottom: 1rem;">
                        <div style="border: 1px solid #e5e7eb; border-radius: 8px; padding: 1.5rem;">
                            <h4 style="margin-top: 0;"><i class="fas fa-chart-pie"></i> Fee Collection Report</h4>
                            <p style="color: #6b7280;">Summary of fee collections, outstanding fees, and payment methods</p>
                            <a href="<?php echo BASE_URL; ?>public/admin/reports/fee-collection.php" class="btn btn-success">
                                <i class="fas fa-file-alt"></i> Generate Report
                            </a>
                        </div>
                    </div>
                    
                    <div class="col-md-6" style="margin-bottom: 1rem;">
                        <div style="border: 1px solid #e5e7eb; border-radius: 8px; padding: 1.5rem;">
                            <h4 style="margin-top: 0;"><i class="fas fa-exclamation-triangle"></i> Defaulters Report</h4>
                            <p style="color: #6b7280;">List of students with outstanding fee payments and overdue amounts</p>
                            <a href="<?php echo BASE_URL; ?>public/admin/reports/fee-defaulters.php" class="btn btn-success">
                                <i class="fas fa-file-alt"></i> Generate Report
                            </a>
                        </div>
                    </div>
                    
                    <div class="col-md-6" style="margin-bottom: 1rem;">
                        <div style="border: 1px solid #e5e7eb; border-radius: 8px; padding: 1.5rem;">
                            <h4 style="margin-top: 0;"><i class="fas fa-calendar"></i> Monthly Collection Report</h4>
                            <p style="color: #6b7280;">Monthly revenue analysis and payment trends by fee type</p>
                            <a href="<?php echo BASE_URL; ?>public/admin/reports/monthly-collection.php" class="btn btn-success">
                                <i class="fas fa-file-alt"></i> Generate Report
                            </a>
                        </div>
                    </div>
                    
                    <div class="col-md-6" style="margin-bottom: 1rem;">
                        <div style="border: 1px solid #e5e7eb; border-radius: 8px; padding: 1.5rem;">
                            <h4 style="margin-top: 0;"><i class="fas fa-receipt"></i> Payment Receipts</h4>
                            <p style="color: #6b7280;">Generate and reprint payment receipts for any transaction</p>
                            <a href="<?php echo BASE_URL; ?>public/admin/reports/receipts.php" class="btn btn-success">
                                <i class="fas fa-file-alt"></i> Generate Report
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Administrative Reports -->
        <div class="card">
            <div class="card-header"><strong><i class="fas fa-cog"></i> Administrative Reports</strong></div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6" style="margin-bottom: 1rem;">
                        <div style="border: 1px solid #e5e7eb; border-radius: 8px; padding: 1.5rem;">
                            <h4 style="margin-top: 0;"><i class="fas fa-users"></i> Student Enrollment Report</h4>
                            <p style="color: #6b7280;">Student enrollment statistics by class, gender, and academic year</p>
                            <a href="<?php echo BASE_URL; ?>public/admin/reports/enrollment.php" class="btn btn-info">
                                <i class="fas fa-file-alt"></i> Generate Report
                            </a>
                        </div>
                    </div>
                    
                    <div class="col-md-6" style="margin-bottom: 1rem;">
                        <div style="border: 1px solid #e5e7eb; border-radius: 8px; padding: 1.5rem;">
                            <h4 style="margin-top: 0;"><i class="fas fa-chalkboard-teacher"></i> Teacher Workload Report</h4>
                            <p style="color: #6b7280;">Teacher subject assignments, class loads, and teaching hours</p>
                            <a href="<?php echo BASE_URL; ?>public/admin/reports/teacher-workload.php" class="btn btn-info">
                                <i class="fas fa-file-alt"></i> Generate Report
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include APP_PATH . '/views/shared/footer.php'; ?>
