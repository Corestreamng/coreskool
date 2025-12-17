<?php
/**
 * My Results - Student
 * CoreSkool School Management System
 */

require_once dirname(dirname(dirname(__DIR__))) . '/config/config.php';

requireAuth();
requireRole('student');

$pageTitle = 'My Results';
$db = Database::getInstance();
$userId = $_SESSION['user_id'];

// Get filter
$exam_id = isset($_GET['exam_id']) ? (int)$_GET['exam_id'] : null;

// Build query
$whereClause = "WHERE r.student_id = ? AND r.status = 'approved'";
$params = [$userId];

if ($exam_id) {
    $whereClause .= " AND r.exam_id = ?";
    $params[] = $exam_id;
}

// Get results
$resultsQuery = $db->query("
    SELECT r.*, s.name as subject_name, e.name as exam_name,
           (r.ca_score + r.exam_score) as total_score
    FROM results r
    INNER JOIN subjects s ON r.subject_id = s.id
    INNER JOIN exams e ON r.exam_id = e.id
    $whereClause
    ORDER BY e.start_date DESC, s.name
", $params);
$results = $resultsQuery->fetchAll();

// Get available exams for filter
$examsQuery = $db->query("
    SELECT DISTINCT e.id, e.name
    FROM results r
    INNER JOIN exams e ON r.exam_id = e.id
    WHERE r.student_id = ? AND r.status = 'approved'
    ORDER BY e.start_date DESC
", [$userId]);
$exams = $examsQuery->fetchAll();

include APP_PATH . '/views/shared/header.php';
?>

<div class="main-content">
    <?php include APP_PATH . '/views/shared/sidebar.php'; ?>
    <?php include APP_PATH . '/views/shared/topbar.php'; ?>
    
    <div class="content-area">
        <div class="page-header" style="margin-bottom: 2rem;">
            <h2>My Results</h2>
            <p style="color: #6b7280;">View your examination results and academic performance</p>
        </div>

        <!-- Filter -->
        <?php if (!empty($exams)): ?>
            <div class="card" style="margin-bottom: 1.5rem;">
                <div class="card-body">
                    <form method="GET" action="">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="exam_id">Filter by Exam</label>
                                    <select id="exam_id" name="exam_id" class="form-control" onchange="this.form.submit()">
                                        <option value="">-- All Exams --</option>
                                        <?php foreach ($exams as $exam): ?>
                                            <option value="<?php echo $exam['id']; ?>" <?php echo $exam_id == $exam['id'] ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($exam['name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        <?php endif; ?>

        <!-- Results -->
        <div class="card">
            <div class="card-header"><strong>My Examination Results</strong></div>
            <div class="card-body">
                <?php if (empty($results)): ?>
                    <div style="text-align: center; padding: 3rem;">
                        <i class="fas fa-graduation-cap" style="font-size: 4rem; color: #e5e7eb; margin-bottom: 1rem;"></i>
                        <h3 style="color: #6b7280;">No Results Available</h3>
                        <p style="color: #9ca3af;">Your examination results will appear here once they are published</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Exam</th>
                                    <th>Subject</th>
                                    <th>CA Score</th>
                                    <th>Exam Score</th>
                                    <th>Total Score</th>
                                    <th>Grade</th>
                                    <th>Remark</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $totalScore = 0;
                                $totalSubjects = 0;
                                foreach ($results as $result): 
                                    $totalScore += $result['total_score'];
                                    $totalSubjects++;
                                ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($result['exam_name']); ?></td>
                                        <td><strong><?php echo htmlspecialchars($result['subject_name']); ?></strong></td>
                                        <td><?php echo $result['ca_score']; ?></td>
                                        <td><?php echo $result['exam_score']; ?></td>
                                        <td><strong><?php echo $result['total_score']; ?></strong></td>
                                        <td>
                                            <span class="badge badge-primary">
                                                <?php echo htmlspecialchars($result['grade']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo htmlspecialchars($result['remark'] ?? '-'); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                                <?php if ($totalSubjects > 0): ?>
                                    <tr style="background-color: #f9fafb; font-weight: bold;">
                                        <td colspan="4" style="text-align: right;">Average:</td>
                                        <td><?php echo number_format($totalScore / $totalSubjects, 2); ?></td>
                                        <td colspan="2"></td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include APP_PATH . '/views/shared/footer.php'; ?>
