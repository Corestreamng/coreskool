<?php
/**
 * Timetable Management - Admin
 * CoreSkool School Management System
 */

require_once dirname(dirname(dirname(__DIR__))) . '/config/config.php';

requireAuth();
requireRole('admin');

$pageTitle = 'Timetable Management';
$db = Database::getInstance();

// Get filter
$class_id = isset($_GET['class_id']) ? (int)$_GET['class_id'] : null;

// Get classes for filter
$classesQuery = $db->query("SELECT id, name FROM classes WHERE school_id = ? AND status = 'active' ORDER BY name", [$_SESSION['school_id']]);
$classes = $classesQuery->fetchAll();

// Get timetable if class selected
$timetable = [];
if ($class_id) {
    $timetableQuery = $db->query("
        SELECT t.*, s.name as subject_name, u.first_name, u.last_name
        FROM timetable t
        INNER JOIN subjects s ON t.subject_id = s.id
        INNER JOIN users u ON t.teacher_id = u.id
        WHERE t.class_id = ?
        ORDER BY 
            FIELD(t.day_of_week, 'monday', 'tuesday', 'wednesday', 'thursday', 'friday'),
            t.start_time
    ", [$class_id]);
    $timetable = $timetableQuery->fetchAll();
}

include APP_PATH . '/views/shared/header.php';
?>

<div class="main-content">
    <?php include APP_PATH . '/views/shared/sidebar.php'; ?>
    <?php include APP_PATH . '/views/shared/topbar.php'; ?>
    
    <div class="content-area">
        <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
            <div>
                <h2 style="margin: 0;">Timetable Management</h2>
                <p style="color: #6b7280; margin: 0.5rem 0 0 0;">Create and manage class schedules</p>
            </div>
            <?php if ($class_id): ?>
                <a href="<?php echo BASE_URL; ?>public/admin/timetable/add.php?class_id=<?php echo $class_id; ?>" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add Period
                </a>
            <?php endif; ?>
        </div>

        <!-- Class Filter -->
        <div class="card" style="margin-bottom: 1.5rem;">
            <div class="card-body">
                <form method="GET" action="">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="class_id">Select Class</label>
                                <select id="class_id" name="class_id" class="form-control" onchange="this.form.submit()">
                                    <option value="">-- Select a Class --</option>
                                    <?php foreach ($classes as $class): ?>
                                        <option value="<?php echo $class['id']; ?>" <?php echo $class_id == $class['id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($class['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <?php if ($class_id && !empty($timetable)): ?>
            <!-- Timetable Display -->
            <div class="card">
                <div class="card-header"><strong>Class Timetable</strong></div>
                <div class="card-body">
                    <?php
                    $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'];
                    $grouped = [];
                    foreach ($timetable as $period) {
                        $grouped[$period['day_of_week']][] = $period;
                    }
                    ?>
                    
                    <?php foreach ($days as $day): ?>
                        <h4 style="text-transform: capitalize; margin-top: 1.5rem; margin-bottom: 1rem;">
                            <?php echo $day; ?>
                        </h4>
                        <?php if (isset($grouped[$day])): ?>
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Time</th>
                                            <th>Subject</th>
                                            <th>Teacher</th>
                                            <th>Room</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($grouped[$day] as $period): ?>
                                            <tr>
                                                <td>
                                                    <?php echo date('h:i A', strtotime($period['start_time'])); ?> - 
                                                    <?php echo date('h:i A', strtotime($period['end_time'])); ?>
                                                </td>
                                                <td><?php echo htmlspecialchars($period['subject_name']); ?></td>
                                                <td><?php echo htmlspecialchars($period['first_name'] . ' ' . $period['last_name']); ?></td>
                                                <td><?php echo htmlspecialchars($period['room'] ?? 'N/A'); ?></td>
                                                <td>
                                                    <a href="<?php echo BASE_URL; ?>public/admin/timetable/edit.php?id=<?php echo $period['id']; ?>" 
                                                       class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></a>
                                                    <a href="<?php echo BASE_URL; ?>public/admin/timetable/delete.php?id=<?php echo $period['id']; ?>" 
                                                       class="btn btn-sm btn-danger" onclick="return confirm('Delete this period?')">
                                                        <i class="fas fa-trash"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <p style="color: #9ca3af; padding: 1rem;">No periods scheduled</p>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php elseif ($class_id): ?>
            <div class="card">
                <div class="card-body" style="text-align: center; padding: 3rem;">
                    <i class="fas fa-clock" style="font-size: 4rem; color: #e5e7eb; margin-bottom: 1rem;"></i>
                    <h3 style="color: #6b7280; margin-bottom: 0.5rem;">No Timetable Created</h3>
                    <p style="color: #9ca3af; margin-bottom: 1.5rem;">Start adding periods to this class timetable</p>
                    <a href="<?php echo BASE_URL; ?>public/admin/timetable/add.php?class_id=<?php echo $class_id; ?>" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add Period
                    </a>
                </div>
            </div>
        <?php else: ?>
            <div class="card">
                <div class="card-body" style="text-align: center; padding: 3rem;">
                    <i class="fas fa-calendar-alt" style="font-size: 4rem; color: #e5e7eb; margin-bottom: 1rem;"></i>
                    <h3 style="color: #6b7280;">Select a Class</h3>
                    <p style="color: #9ca3af;">Choose a class from the dropdown above to view or create its timetable</p>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include APP_PATH . '/views/shared/footer.php'; ?>
