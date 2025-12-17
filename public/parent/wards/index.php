<?php
/**
 * My Wards - Parent
 * CoreSkool School Management System
 */

require_once dirname(dirname(dirname(__DIR__))) . '/config/config.php';

requireAuth();
requireRole('parent');

$pageTitle = 'My Wards';
$db = Database::getInstance();
$userId = $_SESSION['user_id'];

// Get parent's wards
$wardsQuery = $db->query("
    SELECT u.*, ps.relationship, c.name as class_name
    FROM parent_student ps
    INNER JOIN users u ON ps.student_id = u.id
    LEFT JOIN student_classes sc ON u.id = sc.student_id AND sc.status = 'active'
    LEFT JOIN classes c ON sc.class_id = c.id
    WHERE ps.parent_id = ?
", [$userId]);
$wards = $wardsQuery->fetchAll();

include APP_PATH . '/views/shared/header.php';
?>

<div class="main-content">
    <?php include APP_PATH . '/views/shared/sidebar.php'; ?>
    <?php include APP_PATH . '/views/shared/topbar.php'; ?>
    
    <div class="content-area">
        <div class="page-header" style="margin-bottom: 2rem;">
            <h2>My Wards</h2>
            <p style="color: #6b7280;">Manage and monitor your children's academic progress</p>
        </div>

        <?php if (empty($wards)): ?>
            <div class="card">
                <div class="card-body" style="text-align: center; padding: 3rem;">
                    <i class="fas fa-users" style="font-size: 4rem; color: #e5e7eb; margin-bottom: 1rem;"></i>
                    <h3 style="color: #6b7280;">No Wards Assigned</h3>
                    <p style="color: #9ca3af;">No students have been linked to your account yet. Please contact the administrator.</p>
                </div>
            </div>
        <?php else: ?>
            <div class="row">
                <?php foreach ($wards as $ward): ?>
                    <?php
                    // Get attendance
                    $attendQuery = $db->query("
                        SELECT 
                            COUNT(*) as total,
                            SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present
                        FROM attendance 
                        WHERE student_id = ? AND MONTH(date) = MONTH(CURDATE())
                    ", [$ward['id']]);
                    $attend = $attendQuery->fetch();
                    $attendRate = $attend['total'] > 0 ? round(($attend['present'] / $attend['total']) * 100, 1) : 0;
                    
                    // Get latest results
                    $resultsQuery = $db->query("
                        SELECT AVG(ca_score + exam_score) as avg_score
                        FROM results
                        WHERE student_id = ? AND status = 'approved'
                    ", [$ward['id']]);
                    $results = $resultsQuery->fetch();
                    $avgScore = $results['avg_score'] ? round($results['avg_score'], 1) : 0;
                    ?>
                    
                    <div class="col-md-6" style="margin-bottom: 1.5rem;">
                        <div class="card" style="border: 2px solid #e5e7eb;">
                            <div class="card-body">
                                <div style="display: flex; align-items: flex-start; gap: 1.5rem;">
                                    <img src="<?php echo getAvatarUrl($ward['avatar']); ?>" 
                                         alt="Avatar" 
                                         style="width: 100px; height: 100px; border-radius: 50%; object-fit: cover; border: 3px solid #667eea;">
                                    
                                    <div style="flex: 1;">
                                        <h3 style="margin: 0 0 0.5rem 0;">
                                            <?php echo htmlspecialchars($ward['first_name'] . ' ' . $ward['last_name']); ?>
                                        </h3>
                                        <p style="margin: 0.25rem 0; color: #6b7280;">
                                            <i class="fas fa-id-card"></i> 
                                            <strong>Matric:</strong> <?php echo htmlspecialchars($ward['matric_number']); ?>
                                        </p>
                                        <p style="margin: 0.25rem 0; color: #6b7280;">
                                            <i class="fas fa-school"></i> 
                                            <strong>Class:</strong> <?php echo htmlspecialchars($ward['class_name'] ?? 'Not Assigned'); ?>
                                        </p>
                                        <p style="margin: 0.25rem 0; color: #6b7280;">
                                            <i class="fas fa-users"></i> 
                                            <strong>Relationship:</strong> <?php echo ucfirst($ward['relationship']); ?>
                                        </p>
                                        
                                        <div style="display: flex; gap: 1rem; margin-top: 1rem;">
                                            <div style="flex: 1; background-color: #f0f9ff; padding: 0.75rem; border-radius: 6px; text-align: center;">
                                                <div style="font-size: 1.5rem; font-weight: bold; color: #1e40af;">
                                                    <?php echo $attendRate; ?>%
                                                </div>
                                                <div style="font-size: 0.75rem; color: #64748b;">Attendance</div>
                                            </div>
                                            <div style="flex: 1; background-color: #f0fdf4; padding: 0.75rem; border-radius: 6px; text-align: center;">
                                                <div style="font-size: 1.5rem; font-weight: bold; color: #15803d;">
                                                    <?php echo $avgScore; ?>%
                                                </div>
                                                <div style="font-size: 0.75rem; color: #64748b;">Avg Score</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div style="display: flex; gap: 0.5rem; margin-top: 1.5rem; padding-top: 1rem; border-top: 1px solid #e5e7eb;">
                                    <a href="<?php echo BASE_URL; ?>public/parent/wards/view.php?id=<?php echo $ward['id']; ?>" 
                                       class="btn btn-sm btn-primary" style="flex: 1;">
                                        <i class="fas fa-eye"></i> View Profile
                                    </a>
                                    <a href="<?php echo BASE_URL; ?>public/parent/results/index.php?student_id=<?php echo $ward['id']; ?>" 
                                       class="btn btn-sm btn-success" style="flex: 1;">
                                        <i class="fas fa-graduation-cap"></i> Results
                                    </a>
                                    <a href="<?php echo BASE_URL; ?>public/parent/attendance/index.php?student_id=<?php echo $ward['id']; ?>" 
                                       class="btn btn-sm btn-info" style="flex: 1;">
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

<?php include APP_PATH . '/views/shared/footer.php'; ?>
