<!-- Public Sidebar Navigation -->
<style>
.public-sidebar {
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
}

.public-sidebar.active {
    left: 0;
}

.public-sidebar-header {
    padding: 2rem 1.5rem;
    background: rgba(0,0,0,0.2);
    border-bottom: 1px solid rgba(255,255,255,0.1);
}

.public-sidebar-header h2 {
    color: white;
    font-size: 1.6rem;
    margin: 0;
    font-family: 'Orbitron', sans-serif;
}

.public-sidebar-header p {
    color: rgba(255,255,255,0.8);
    font-size: 0.9rem;
    margin-top: 0.5rem;
}

.public-sidebar-menu {
    list-style: none;
    padding: 1.5rem 0;
    margin: 0;
}

.public-sidebar-menu li {
    margin: 0;
}

.public-sidebar-menu a {
    display: flex;
    align-items: center;
    padding: 1rem 1.5rem;
    color: white;
    text-decoration: none;
    transition: all 0.3s;
    border-left: 4px solid transparent;
    font-size: 1rem;
}

.public-sidebar-menu a:hover {
    background: rgba(255,255,255,0.15);
    border-left-color: white;
    padding-left: 2rem;
}

.public-sidebar-menu a.active {
    background: rgba(255,255,255,0.25);
    border-left-color: white;
    font-weight: 600;
}

.public-sidebar-menu a i {
    margin-right: 1rem;
    width: 24px;
    text-align: center;
    font-size: 1.1rem;
}

.public-sidebar-toggle {
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
    font-size: 1.3rem;
}

.public-sidebar-toggle:hover {
    transform: scale(1.1) rotate(90deg);
    box-shadow: 0 6px 25px rgba(102, 126, 234, 0.7);
}

.public-sidebar-toggle.active {
    left: 320px;
}

.public-sidebar-overlay {
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

.public-sidebar-overlay.active {
    opacity: 1;
    visibility: visible;
}

.public-sidebar-section-title {
    padding: 1rem 1.5rem 0.5rem;
    color: rgba(255,255,255,0.6);
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 1px;
    font-weight: 600;
}

.public-sidebar-footer {
    padding: 1.5rem;
    background: rgba(0,0,0,0.2);
    margin-top: auto;
}

.public-sidebar-footer .btn {
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
}

.public-sidebar-footer .btn:last-child {
    margin-bottom: 0;
}

.public-sidebar-footer .btn:hover {
    transform: translateY(-3px);
    box-shadow: 0 5px 20px rgba(255,255,255,0.4);
}

.public-sidebar-footer .btn-secondary {
    background: transparent;
    border: 2px solid white;
    color: white;
}

.public-sidebar-footer .btn-secondary:hover {
    background: white;
    color: #667eea;
}

@media (max-width: 768px) {
    .public-sidebar {
        width: 280px;
        left: -280px;
    }
    
    .public-sidebar-toggle.active {
        left: 300px;
    }
}
</style>

<!-- Sidebar Toggle Button -->
<button class="public-sidebar-toggle" id="publicSidebarToggle" onclick="togglePublicSidebar()">
    <i class="fas fa-bars"></i>
</button>

<!-- Sidebar Overlay -->
<div class="public-sidebar-overlay" id="publicSidebarOverlay" onclick="togglePublicSidebar()"></div>

<!-- Public Sidebar Navigation -->
<nav class="public-sidebar" id="publicSidebar">
    <div class="public-sidebar-header">
        <h2>🚀 KubeArena</h2>
        <p>Master Linux & DevOps</p>
    </div>
    
    <ul class="public-sidebar-menu">
        <li class="public-sidebar-section-title">Navigation</li>
        <li>
            <a href="/" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'index.php' || basename($_SERVER['PHP_SELF']) == '') ? 'active' : ''; ?>">
                <i class="fas fa-home"></i> Home
            </a>
        </li>
        <li>
            <a href="learning_paths.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'learning_paths.php' ? 'active' : ''; ?>">
                <i class="fas fa-route"></i> Learning Paths
            </a>
        </li>
        <li>
            <a href="browse_courses.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'browse_courses.php' ? 'active' : ''; ?>">
                <i class="fas fa-book"></i> Browse Courses
            </a>
        </li>
        <li>
            <a href="blog.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'blog.php' ? 'active' : ''; ?>">
                <i class="fas fa-blog"></i> Blog
            </a>
        </li>
        <li>
            <a href="about_us.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'about_us.php' ? 'active' : ''; ?>">
                <i class="fas fa-info-circle"></i> About Us
            </a>
        </li>
        
        <?php if (isset($_SESSION['user'])): ?>
            <li class="public-sidebar-section-title">My Account</li>
            <li>
                <a href="dashboard.php">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
            </li>
            <li>
                <a href="courses.php">
                    <i class="fas fa-graduation-cap"></i> My Courses
                </a>
            </li>
            <li>
                <a href="labs.php">
                    <i class="fas fa-flask"></i> My Labs
                </a>
            </li>
            
            <li class="public-sidebar-section-title">Admin Tools</li>
            <li>
                <a href="admin/blog_create.php">
                    <i class="fas fa-pen-fancy"></i> Write Blog Post
                </a>
            </li>
            <li>
                <a href="admin/blog_manage.php">
                    <i class="fas fa-tasks"></i> Manage Blogs
                </a>
            </li>
            <li>
                <a href="admin/about_edit.php">
                    <i class="fas fa-edit"></i> Edit About Us
                </a>
            </li>
        <?php endif; ?>
    </ul>
    
    <div class="public-sidebar-footer">
        <?php if (isset($_SESSION['user'])): ?>
            <a href="logout.php" class="btn">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        <?php else: ?>
            <a href="login.php" class="btn">
                <i class="fas fa-sign-in-alt"></i> Login
            </a>
            <a href="register.php" class="btn btn-secondary">
                <i class="fas fa-user-plus"></i> Register Free
            </a>
        <?php endif; ?>
    </div>
</nav>

<script>
function togglePublicSidebar() {
    const sidebar = document.getElementById('publicSidebar');
    const overlay = document.getElementById('publicSidebarOverlay');
    const toggle = document.getElementById('publicSidebarToggle');
    
    sidebar.classList.toggle('active');
    overlay.classList.toggle('active');
    toggle.classList.toggle('active');
}

// Close sidebar on escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        const sidebar = document.getElementById('publicSidebar');
        if (sidebar.classList.contains('active')) {
            togglePublicSidebar();
        }
    }
});
</script>
