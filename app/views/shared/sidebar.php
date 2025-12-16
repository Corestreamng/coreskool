<?php
// Sidebar menu items based on role
$menuItems = [
    'admin' => [
        ['icon' => 'fa-dashboard', 'label' => 'Dashboard', 'url' => 'dashboard.php'],
        ['icon' => 'fa-users', 'label' => 'Students', 'url' => 'students/index.php'],
        ['icon' => 'fa-chalkboard-teacher', 'label' => 'Teachers', 'url' => 'teachers/index.php'],
        ['icon' => 'fa-user-tie', 'label' => 'Parents', 'url' => 'parents/index.php'],
        ['icon' => 'fa-school', 'label' => 'Classes', 'url' => 'classes/index.php'],
        ['icon' => 'fa-book', 'label' => 'Subjects', 'url' => 'subjects/index.php'],
        ['icon' => 'fa-calendar-check', 'label' => 'Attendance', 'url' => 'attendance/index.php'],
        ['icon' => 'fa-file-alt', 'label' => 'Exams', 'url' => 'exams/index.php'],
        ['icon' => 'fa-graduation-cap', 'label' => 'Results', 'url' => 'results/index.php'],
        ['icon' => 'fa-money-bill', 'label' => 'Fees & Payments', 'url' => 'fees/index.php'],
        ['icon' => 'fa-comment', 'label' => 'Messages', 'url' => 'messages/index.php'],
        ['icon' => 'fa-laptop', 'label' => 'CBT System', 'url' => 'cbt/index.php'],
        ['icon' => 'fa-book-reader', 'label' => 'LMS', 'url' => 'lms/index.php'],
        ['icon' => 'fa-book-open', 'label' => 'Library', 'url' => 'library/index.php'],
        ['icon' => 'fa-building', 'label' => 'Hostel', 'url' => 'hostel/index.php'],
        ['icon' => 'fa-clock', 'label' => 'Timetable', 'url' => 'timetable/index.php'],
        ['icon' => 'fa-chart-bar', 'label' => 'Reports', 'url' => 'reports/index.php'],
        ['icon' => 'fa-cog', 'label' => 'Settings', 'url' => 'settings/index.php'],
    ],
    'teacher' => [
        ['icon' => 'fa-dashboard', 'label' => 'Dashboard', 'url' => 'dashboard.php'],
        ['icon' => 'fa-users', 'label' => 'My Students', 'url' => 'students/index.php'],
        ['icon' => 'fa-book', 'label' => 'My Subjects', 'url' => 'subjects/index.php'],
        ['icon' => 'fa-calendar-check', 'label' => 'Attendance', 'url' => 'attendance/index.php'],
        ['icon' => 'fa-file-alt', 'label' => 'Exams', 'url' => 'exams/index.php'],
        ['icon' => 'fa-graduation-cap', 'label' => 'Results', 'url' => 'results/index.php'],
        ['icon' => 'fa-comment', 'label' => 'Messages', 'url' => 'messages/index.php'],
        ['icon' => 'fa-laptop', 'label' => 'CBT', 'url' => 'cbt/index.php'],
        ['icon' => 'fa-book-reader', 'label' => 'Courses', 'url' => 'courses/index.php'],
        ['icon' => 'fa-clock', 'label' => 'Timetable', 'url' => 'timetable/index.php'],
    ],
    'student' => [
        ['icon' => 'fa-dashboard', 'label' => 'Dashboard', 'url' => 'dashboard.php'],
        ['icon' => 'fa-book', 'label' => 'My Subjects', 'url' => 'subjects/index.php'],
        ['icon' => 'fa-calendar-check', 'label' => 'My Attendance', 'url' => 'attendance/index.php'],
        ['icon' => 'fa-file-alt', 'label' => 'Exams', 'url' => 'exams/index.php'],
        ['icon' => 'fa-graduation-cap', 'label' => 'My Results', 'url' => 'results/index.php'],
        ['icon' => 'fa-laptop', 'label' => 'CBT Exams', 'url' => 'cbt/index.php'],
        ['icon' => 'fa-book-reader', 'label' => 'Courses', 'url' => 'courses/index.php'],
        ['icon' => 'fa-book-open', 'label' => 'Library', 'url' => 'library/index.php'],
        ['icon' => 'fa-clock', 'label' => 'Timetable', 'url' => 'timetable/index.php'],
        ['icon' => 'fa-comment', 'label' => 'Messages', 'url' => 'messages/index.php'],
    ],
    'parent' => [
        ['icon' => 'fa-dashboard', 'label' => 'Dashboard', 'url' => 'dashboard.php'],
        ['icon' => 'fa-users', 'label' => 'My Wards', 'url' => 'wards/index.php'],
        ['icon' => 'fa-calendar-check', 'label' => 'Attendance', 'url' => 'attendance/index.php'],
        ['icon' => 'fa-graduation-cap', 'label' => 'Results', 'url' => 'results/index.php'],
        ['icon' => 'fa-money-bill', 'label' => 'Payments', 'url' => 'payments/index.php'],
        ['icon' => 'fa-comment', 'label' => 'Messages', 'url' => 'messages/index.php'],
        ['icon' => 'fa-clock', 'label' => 'Timetable', 'url' => 'timetable/index.php'],
    ],
    'exam_officer' => [
        ['icon' => 'fa-dashboard', 'label' => 'Dashboard', 'url' => 'dashboard.php'],
        ['icon' => 'fa-file-alt', 'label' => 'Exams', 'url' => 'exams/index.php'],
        ['icon' => 'fa-clock', 'label' => 'Exam Timetable', 'url' => 'timetable/index.php'],
        ['icon' => 'fa-graduation-cap', 'label' => 'Results', 'url' => 'results/index.php'],
        ['icon' => 'fa-laptop', 'label' => 'CBT Management', 'url' => 'cbt/index.php'],
        ['icon' => 'fa-chart-bar', 'label' => 'Reports', 'url' => 'reports/index.php'],
        ['icon' => 'fa-comment', 'label' => 'Messages', 'url' => 'messages/index.php'],
    ],
    'cashier' => [
        ['icon' => 'fa-dashboard', 'label' => 'Dashboard', 'url' => 'dashboard.php'],
        ['icon' => 'fa-money-bill', 'label' => 'Fees Management', 'url' => 'fees/index.php'],
        ['icon' => 'fa-receipt', 'label' => 'Payments', 'url' => 'payments/index.php'],
        ['icon' => 'fa-users', 'label' => 'Students', 'url' => 'students/index.php'],
        ['icon' => 'fa-chart-line', 'label' => 'Reports', 'url' => 'reports/index.php'],
        ['icon' => 'fa-comment', 'label' => 'Messages', 'url' => 'messages/index.php'],
    ],
];

$currentMenu = $menuItems[$userRole] ?? [];
$currentPath = $_SERVER['PHP_SELF'];
?>

<div class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <h3><?php echo SITE_NAME; ?></h3>
        <p style="color: rgba(255,255,255,0.7); font-size: 0.75rem; margin: 0;">
            <?php echo strtoupper($userRole); ?>
        </p>
    </div>
    
    <ul class="sidebar-menu">
        <?php foreach ($currentMenu as $item): ?>
            <?php
            $isActive = strpos($currentPath, $item['url']) !== false ? 'active' : '';
            $fullUrl = BASE_URL . 'public/' . $userRole . '/' . $item['url'];
            ?>
            <li>
                <a href="<?php echo $fullUrl; ?>" class="<?php echo $isActive; ?>">
                    <i class="fas <?php echo $item['icon']; ?>"></i>
                    <span><?php echo $item['label']; ?></span>
                </a>
            </li>
        <?php endforeach; ?>
        
        <li style="margin-top: 2rem; border-top: 1px solid rgba(255,255,255,0.1); padding-top: 1rem;">
            <a href="<?php echo BASE_URL; ?>public/auth/logout.php">
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
            </a>
        </li>
    </ul>
</div>
