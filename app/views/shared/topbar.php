<div class="topbar">
    <div class="topbar-left">
        <span class="menu-toggle" id="menuToggle">
            <i class="fas fa-bars"></i>
        </span>
        <h4 style="margin: 0; color: var(--dark-color);">
            <?php echo $pageTitle ?? 'Dashboard'; ?>
        </h4>
    </div>
    
    <div class="topbar-right">
        <!-- Language Selector -->
        <div class="topbar-icon" title="Language">
            <select style="border: none; background: transparent; cursor: pointer;" onchange="changeLanguage(this.value)">
                <option value="en" <?php echo ($_SESSION['language'] ?? 'en') === 'en' ? 'selected' : ''; ?>>English</option>
                <option value="ar" <?php echo ($_SESSION['language'] ?? 'en') === 'ar' ? 'selected' : ''; ?>>العربية</option>
            </select>
        </div>
        
        <!-- Notifications -->
        <div class="topbar-icon notification-icon" onclick="toggleNotifications()">
            <i class="fas fa-bell"></i>
            <span class="badge badge-danger" id="notificationBadge" style="display: none;">0</span>
        </div>
        
        <!-- Messages -->
        <div class="topbar-icon" onclick="window.location.href='<?php echo BASE_URL; ?>public/<?php echo $userRole; ?>/messages/index.php'">
            <i class="fas fa-envelope"></i>
            <span class="badge badge-info" id="messageBadge" style="display: none;">0</span>
        </div>
        
        <!-- User Menu -->
        <div class="user-menu" onclick="toggleUserMenu()">
            <img src="<?php echo $userAvatar; ?>" alt="Avatar" class="user-avatar">
            <div>
                <div style="font-weight: 600; font-size: 0.875rem;">
                    <?php echo $userName; ?>
                </div>
                <div style="font-size: 0.75rem; color: #6b7280;">
                    <?php echo ucfirst($userRole); ?>
                </div>
            </div>
            <i class="fas fa-chevron-down" style="font-size: 0.75rem; margin-left: 0.5rem;"></i>
        </div>
    </div>
</div>

<!-- Notification Dropdown (Hidden by default) -->
<div id="notificationDropdown" class="card" style="display: none; position: fixed; right: 20px; top: 70px; width: 350px; max-height: 400px; overflow-y: auto; z-index: 1000;">
    <div class="card-header">
        <strong>Notifications</strong>
        <a href="#" style="float: right; font-size: 0.875rem;" onclick="markAllAsRead()">Mark all as read</a>
    </div>
    <div class="card-body" id="notificationList">
        <p class="text-center" style="color: #6b7280; padding: 2rem;">No new notifications</p>
    </div>
</div>

<script>
function toggleNotifications() {
    const dropdown = document.getElementById('notificationDropdown');
    dropdown.style.display = dropdown.style.display === 'none' ? 'block' : 'none';
    
    if (dropdown.style.display === 'block') {
        loadNotifications();
    }
}

function toggleUserMenu() {
    // Implement user menu dropdown if needed
    window.location.href = '<?php echo BASE_URL; ?>public/<?php echo $userRole; ?>/profile.php';
}

function loadNotifications() {
    // Load notifications via AJAX
    fetch('<?php echo BASE_URL; ?>api/notifications/list.php')
        .then(response => response.json())
        .then(data => {
            const list = document.getElementById('notificationList');
            if (data.notifications && data.notifications.length > 0) {
                list.innerHTML = data.notifications.map(notif => `
                    <div style="padding: 1rem; border-bottom: 1px solid #e5e7eb; ${notif.is_read ? '' : 'background: #f0f9ff;'}">
                        <div style="font-weight: 600; margin-bottom: 0.25rem;">${notif.title}</div>
                        <div style="font-size: 0.875rem; color: #6b7280;">${notif.message}</div>
                        <div style="font-size: 0.75rem; color: #9ca3af; margin-top: 0.25rem;">${notif.created_at}</div>
                    </div>
                `).join('');
            }
        })
        .catch(error => console.error('Error loading notifications:', error));
}

function markAllAsRead() {
    fetch('<?php echo BASE_URL; ?>api/notifications/mark-read.php', {
        method: 'POST'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('notificationBadge').style.display = 'none';
            loadNotifications();
        }
    });
}

function changeLanguage(lang) {
    fetch('<?php echo BASE_URL; ?>api/settings/change-language.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ language: lang })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        }
    });
}
</script>
