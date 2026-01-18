<!-- Admin Sidebar Navigation -->
<style>
.admin-sidebar {
    position: fixed;
    top: 0;
    left: -300px;
    width: 300px;
    height: 100vh;
    background: linear-gradient(180deg, #667eea 0%, #764ba2 100%);
    box-shadow: 4px 0 20px rgba(0,0,0,0.3);
    z-index: 1000;
    transition: left 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    overflow-y: auto;
    padding-bottom: 20px;
}

.admin-sidebar.active {
    left: 0;
}

.admin-sidebar-header {
    padding: 2rem 1.5rem;
    background: rgba(0,0,0,0.2);
    border-bottom: 1px solid rgba(255,255,255,0.1);
}

.admin-sidebar-header h2 {
    color: white;
    font-size: 1.6rem;
    margin: 0;
    font-family: 'Orbitron', sans-serif;
    font-weight: 700;
}

.admin-sidebar-header p {
    color: rgba(255,255,255,0.8);
    font-size: 0.85rem;
    margin-top: 0.5rem;
    font-weight: 600;
}

.admin-sidebar-menu {
    list-style: none;
    padding: 1.5rem 0;
    margin: 0;
}

.admin-sidebar-menu li {
    margin: 0;
}

.admin-sidebar-section-title {
    padding: 1rem 1.5rem 0.5rem;
    color: rgba(255,255,255,0.6);
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 1px;
    font-weight: 600;
}

.admin-sidebar-menu a {
    display: flex;
    align-items: center;
    padding: 1rem 1.5rem;
    color: white;
    text-decoration: none;
    transition: all 0.3s;
    border-left: 4px solid transparent;
    font-size: 1rem;
    font-weight: 500;
}

.admin-sidebar-menu a:hover {
    background: rgba(255,255,255,0.15);
    border-left-color: white;
    padding-left: 2rem;
}

.admin-sidebar-menu a.active {
    background: rgba(255,255,255,0.25);
    border-left-color: white;
    font-weight: 600;
}

.admin-sidebar-menu a i {
    margin-right: 1rem;
    width: 24px;
    text-align: center;
    font-size: 1.1rem;
    transition: all 0.3s ease;
}

.admin-sidebar-menu a:hover i {
    transform: translateX(5px) scale(1.2);
    color: #ffd700;
}

.admin-sidebar-menu a.active i {
    color: #fff;
}

.admin-sidebar-toggle {
    position: fixed;
    top: 20px;
    left: 20px;
    width: 55px;
    height: 55px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
    border-radius: 50%;
    cursor: pointer;
    z-index: 999;
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.5);
    transition: all 0.3s;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.5rem;
    border: 2px solid white;
}

.admin-sidebar-toggle:hover {
    transform: scale(1.1);
    box-shadow: 0 6px 25px rgba(102, 126, 234, 0.7);
}

.admin-sidebar-toggle.active {
    left: 320px;
}

.admin-sidebar-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.6);
    z-index: 998;
    opacity: 0;
    visibility: hidden;
    transition: all 0.3s;
    backdrop-filter: blur(4px);
}

.admin-sidebar-overlay.active {
    opacity: 1;
    visibility: visible;
}

.admin-sidebar-footer {
    padding: 1.5rem;
    background: rgba(0,0,0,0.2);
    margin-top: auto;
}

.admin-sidebar-footer .btn {
    width: 100%;
    padding: 0.8rem;
    background: white;
    color: #667eea;
    border: none;
    border-radius: 10px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s;
    text-decoration: none;
    display: block;
    text-align: center;
    margin-bottom: 0.8rem;
    font-size: 0.95rem;
}

.admin-sidebar-footer .btn:last-child {
    margin-bottom: 0;
}

.admin-sidebar-footer .btn:hover {
    transform: translateY(-3px);
    box-shadow: 0 5px 20px rgba(255,255,255,0.4);
}

.admin-sidebar-footer .btn-secondary {
    background: transparent;
    border: 2px solid white;
    color: white;
}

.admin-sidebar-footer .btn-secondary:hover {
    background: white;
    color: #667eea;
}

@media (max-width: 768px) {
    .admin-sidebar {
        width: 280px;
    }

    .admin-sidebar-toggle.active {
        left: 300px;
    }

    .admin-sidebar-menu a {
        padding: 0.9rem 1.2rem;
        font-size: 0.95rem;
    }
}

/* Hide AdminLTE sidebar */
.main-sidebar {
    display: none !important;
}
</style>

<!-- Admin Sidebar Toggle Button -->
<button class="admin-sidebar-toggle" id="adminSidebarToggle" onclick="toggleAdminSidebar()" title="Toggle menu">
    <i class="fas fa-bars"></i>
</button>

<!-- Admin Sidebar Container -->
<nav class="admin-sidebar" id="adminSidebar">
    <div class="admin-sidebar-header">
        <h2><i class="fas fa-rocket" style="margin-right: 0.5rem;"></i> KubeArena</h2>
        <p>Admin Panel</p>
    </div>

    <ul class="admin-sidebar-menu">
        <!-- Main Navigation -->
        <li><div class="admin-sidebar-section-title">Navigation</div></li>
        
        <li>
            <a href="/index.php" <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'class="active"' : ''; ?> >
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </a>
        </li>
        
        <li>
            <a href="/charts.php" <?php echo basename($_SERVER['PHP_SELF']) == 'charts.php' ? 'class="active"' : ''; ?> >
                <i class="fas fa-chart-line"></i> Performance Charts
            </a>
        </li>
        
        <li>
            <a href="/alerts.php" <?php echo basename($_SERVER['PHP_SELF']) == 'alerts.php' ? 'class="active"' : ''; ?> >
                <i class="fas fa-exclamation-triangle"></i> Alerts
            </a>
        </li>

        <!-- Labs Platform (Admin only) -->
        <?php if ($_SESSION['role'] === 'admin'): ?>
        <li><div class="admin-sidebar-section-title">Labs Platform</div></li>
        
        <li>
            <a href="/courses.php" <?php echo basename($_SERVER['PHP_SELF']) == 'courses.php' ? 'class="active"' : ''; ?> >
                <i class="fas fa-graduation-cap"></i> Courses
            </a>
        </li>
        
        <li>
            <a href="/admin_labs.php" <?php echo basename($_SERVER['PHP_SELF']) == 'admin_labs.php' ? 'class="active"' : ''; ?> >
                <i class="fas fa-flask"></i> Manage Labs
            </a>
        </li>
        
        <li>
            <a href="/admin_lab_requests.php" <?php echo basename($_SERVER['PHP_SELF']) == 'admin_lab_requests.php' ? 'class="active"' : ''; ?> >
                <i class="fas fa-clipboard-check"></i> Lab Requests
            </a>
        </li>
        
        <li>
            <a href="/admin_provisioners.php" <?php echo basename($_SERVER['PHP_SELF']) == 'admin_provisioners.php' ? 'class="active"' : ''; ?> >
                <i class="fas fa-server"></i> Manage Servers
            </a>
        </li>
        
        <li>
            <a href="/admin_users.php" <?php echo basename($_SERVER['PHP_SELF']) == 'admin_users.php' ? 'class="active"' : ''; ?> >
                <i class="fas fa-users-cog"></i> User Management
            </a>
        </li>
        
        <li>
            <a href="/admin_live_sessions.php" <?php echo basename($_SERVER['PHP_SELF']) == 'admin_live_sessions.php' ? 'class="active"' : ''; ?> >
                <i class="fas fa-users-circle"></i> Live Sessions
            </a>
        </li>
        
        <li>
            <a href="/admin_support.php" <?php echo basename($_SERVER['PHP_SELF']) == 'admin_support.php' ? 'class="active"' : ''; ?> >
                <i class="fas fa-headset"></i> Support Cases
            </a>
        </li>

        <!-- Content Management -->
        <li><div class="admin-sidebar-section-title">Content Management</div></li>
        
        <li>
            <a href="/admin/blog_create.php" <?php echo basename($_SERVER['PHP_SELF']) == 'blog_create.php' ? 'class="active"' : ''; ?> >
                <i class="fas fa-pen-fancy"></i> Write Blog Post
            </a>
        </li>
        
        <li>
            <a href="/admin/blog_manage.php" <?php echo basename($_SERVER['PHP_SELF']) == 'blog_manage.php' ? 'class="active"' : ''; ?> >
                <i class="fas fa-blog"></i> Manage Blogs
            </a>
        </li>
        
        <li>
            <a href="/admin/about_edit.php" <?php echo basename($_SERVER['PHP_SELF']) == 'about_edit.php' ? 'class="active"' : ''; ?> >
                <i class="fas fa-info-circle"></i> Update About Us
            </a>
        </li>
        
        <li>
            <a href="/admin/learning_paths_manage.php" <?php echo basename($_SERVER['PHP_SELF']) == 'learning_paths_manage.php' ? 'class="active"' : ''; ?> >
                <i class="fas fa-road"></i> Manage Learning Paths
            </a>
        </li>

        <?php endif; ?>

        <!-- User Section -->
        <?php if ($_SESSION['role'] === 'user'): ?>
        <li><div class="admin-sidebar-section-title">My Learning</div></li>
        
        <li>
            <a href="/browse_labs.php" <?php echo basename($_SERVER['PHP_SELF']) == 'browse_labs.php' ? 'class="active"' : ''; ?> >
                <i class="fas fa-search"></i> Browse Labs
            </a>
        </li>
        
        <li>
            <a href="/my_active_labs.php" <?php echo basename($_SERVER['PHP_SELF']) == 'my_active_labs.php' ? 'class="active"' : ''; ?> >
                <i class="fas fa-laptop-code"></i> My Active Labs
            </a>
        </li>
        
        <li>
            <a href="/profile.php" <?php echo basename($_SERVER['PHP_SELF']) == 'profile.php' ? 'class="active"' : ''; ?> >
                <i class="fas fa-user"></i> My Profile
            </a>
        </li>
        
        <li>
            <a href="/support.php" <?php echo basename($_SERVER['PHP_SELF']) == 'support.php' ? 'class="active"' : ''; ?> >
                <i class="fas fa-life-ring"></i> Support
            </a>
        </li>
        <?php endif; ?>

        <!-- Account Section -->
        <li><div class="admin-sidebar-section-title">Account</div></li>
        
        <li>
            <a href="/profile.php" <?php echo basename($_SERVER['PHP_SELF']) == 'profile.php' ? 'class="active"' : ''; ?> >
                <i class="fas fa-user-circle"></i> My Profile
            </a>
        </li>
    </ul>

    <div class="admin-sidebar-footer">
        <a href="/logout.php" class="btn">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>
    </div>
</nav>

<!-- Admin Sidebar Overlay -->
<div class="admin-sidebar-overlay" id="adminSidebarOverlay" onclick="toggleAdminSidebar()"></div>

<!-- Google Fonts -->
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&family=Poppins:wght@300;400;600&display=swap">
<!-- Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<script>
function toggleAdminSidebar() {
    const sidebar = document.getElementById('adminSidebar');
    const overlay = document.getElementById('adminSidebarOverlay');
    const toggle = document.getElementById('adminSidebarToggle');
    
    sidebar.classList.toggle('active');
    overlay.classList.toggle('active');
    toggle.classList.toggle('active');
}

// Close sidebar on escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        const sidebar = document.getElementById('adminSidebar');
        if (sidebar.classList.contains('active')) {
            toggleAdminSidebar();
        }
    }
});

// Close sidebar when clicking on a link
document.querySelectorAll('.admin-sidebar-menu a').forEach(link => {
    link.addEventListener('click', function() {
        const sidebar = document.getElementById('adminSidebar');
        if (sidebar.classList.contains('active')) {
            toggleAdminSidebar();
        }
    });
});
</script>
