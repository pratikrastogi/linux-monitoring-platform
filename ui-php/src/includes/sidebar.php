<!-- Main Sidebar Container -->
<aside class="main-sidebar sidebar-dark-primary elevation-4">
  <!-- Brand Logo -->
  <a href="index.php" class="brand-link">
    <i class="fas fa-rocket brand-image ml-3"></i>
    <span class="brand-text font-weight-light">KubeArena</span>
  </a>

  <!-- Sidebar -->
  <div class="sidebar">
    <!-- Sidebar user panel (optional) -->
    <div class="user-panel mt-3 pb-3 mb-3 d-flex">
      <div class="image">
        <i class="fas fa-user-circle fa-2x text-white"></i>
      </div>
      <div class="info">
        <a href="#" class="d-block"><?php echo htmlspecialchars($_SESSION['user']); ?></a>
        <small class="text-muted"><?php echo ucfirst($_SESSION['role']); ?></small>
      </div>
    </div>

    <!-- Sidebar Menu -->
    <nav class="mt-2">
      <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
        
        <!-- Dashboard -->
        <li class="nav-item">
          <a href="index.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>">
            <i class="nav-icon fas fa-tachometer-alt"></i>
            <p>Dashboard</p>
          </a>
        </li>
        
        <!-- Charts -->
        <li class="nav-item">
          <a href="charts.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'charts.php' ? 'active' : ''; ?>">
            <i class="nav-icon fas fa-chart-line"></i>
            <p>Performance Charts</p>
          </a>
        </li>
        
        <!-- Alerts -->
        <li class="nav-item">
          <a href="alerts.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'alerts.php' ? 'active' : ''; ?>">
            <i class="nav-icon fas fa-exclamation-triangle"></i>
            <p>
              Alerts
              <span class="badge badge-danger right" id="sidebarAlertCount">0</span>
            </p>
          </a>
        </li>
        
        <?php if ($_SESSION['role'] === 'admin') { ?>
        
        <!-- Admin Section -->
        <li class="nav-header">ADMINISTRATION</li>
        
        <!-- Server Management -->
        <li class="nav-item">
          <a href="add_server.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'add_server.php' ? 'active' : ''; ?>">
            <i class="nav-icon fas fa-server"></i>
            <p>Manage Servers</p>
          </a>
        </li>
        
        <!-- User Management -->
        <li class="nav-item">
          <a href="users.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'users.php' ? 'active' : ''; ?>">
            <i class="nav-icon fas fa-users"></i>
            <p>User Management</p>
          </a>
        </li>
        
        <!-- Lab Requests -->
        <li class="nav-item">
          <a href="#lab-requests" class="nav-link">
            <i class="nav-icon fas fa-flask"></i>
            <p>
              Lab Requests
              <span class="badge badge-info right" id="labRequestCount">0</span>
            </p>
          </a>
        </li>
        
        <!-- Free Access Generator -->
        <li class="nav-item">
          <a href="generate_free_access.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'generate_free_access.php' ? 'active' : ''; ?>">
            <i class="nav-icon fas fa-gift"></i>
            <p>Generate Access</p>
          </a>
        </li>
        
        <?php } ?>
        
        <!-- =============================================
             LABS PLATFORM SECTION (Phase 2 - ADDITIVE)
             Purpose: Add Labs platform navigation
             Backward Compatible: Does NOT modify existing menu items
             ============================================= -->
        
        <?php if ($_SESSION['role'] === 'admin'): ?>
        <!-- Admin Labs Section -->
        <li class="nav-header">LABS PLATFORM</li>
        
        <li class="nav-item">
          <a href="courses.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'courses.php' ? 'active' : ''; ?>">
            <i class="nav-icon fas fa-graduation-cap"></i>
            <p>Courses</p>
          </a>
        </li>
        
        <li class="nav-item">
          <a href="labs.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'labs.php' ? 'active' : ''; ?>">
            <i class="nav-icon fas fa-flask"></i>
            <p>Lab Templates</p>
          </a>
        </li>
        
        <li class="nav-item">
          <a href="admin_lab_requests.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'admin_lab_requests.php' ? 'active' : ''; ?>">
            <i class="nav-icon fas fa-clipboard-check"></i>
            <p>
              Lab Requests
              <span class="badge badge-warning right" id="pendingLabRequests">0</span>
            </p>
          </a>
        </li>
        
        <li class="nav-item">
          <a href="provisioners.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'provisioners.php' ? 'active' : ''; ?>">
            <i class="nav-icon fas fa-server"></i>
            <p>Provisioners</p>
          </a>
        </li>
        
        <li class="nav-item">
          <a href="admin_users.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'admin_users.php' ? 'active' : ''; ?>">
            <i class="nav-icon fas fa-users-cog"></i>
            <p>User Management</p>
          </a>
        </li>
        
        <?php endif; ?>
        
        <?php if ($_SESSION['role'] === 'user'): ?>
        <!-- User Labs Section -->
        <li class="nav-header">MY LEARNING</li>
        
        <li class="nav-item">
          <a href="browse_labs.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'browse_labs.php' ? 'active' : ''; ?>">
            <i class="nav-icon fas fa-search"></i>
            <p>Browse Labs</p>
          </a>
        </li>
        
        <li class="nav-item">
          <a href="my_active_labs.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'my_active_labs.php' ? 'active' : ''; ?>">
            <i class="nav-icon fas fa-laptop-code"></i>
            <p>My Active Labs</p>
          </a>
        </li>
        
        <li class="nav-item">
          <a href="terminal.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'terminal.php' ? 'active' : ''; ?>">
            <i class="nav-icon fas fa-terminal"></i>
            <p>Lab Terminal</p>
          </a>
        </li>
        
        <li class="nav-item">
          <a href="profile.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'profile.php' ? 'active' : ''; ?>">
            <i class="nav-icon fas fa-user"></i>
            <p>My Profile</p>
          </a>
        </li>
        
        <li class="nav-item">
          <a href="support.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'support.php' ? 'active' : ''; ?>">
            <i class="nav-icon fas fa-life-ring"></i>
            <p>Support</p>
          </a>
        </li>
        
        <?php endif; ?>
        
        <!-- General Section -->
        <li class="nav-header">ACCOUNT</li>
        
        <!-- Request Access (for non-admin users) -->
        <?php if ($_SESSION['role'] !== 'admin') { ?>
        <li class="nav-item">
          <a href="request_access.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'request_access.php' ? 'active' : ''; ?>">
            <i class="nav-icon fas fa-clock"></i>
            <p>Request Lab Time</p>
          </a>
        </li>
        <?php } ?>
        
        <!-- Logout -->
        <li class="nav-item">
          <a href="logout.php" class="nav-link text-danger">
            <i class="nav-icon fas fa-sign-out-alt"></i>
            <p>Logout</p>
          </a>
        </li>
        
      </ul>
    </nav>
    <!-- /.sidebar-menu -->
  </div>
  <!-- /.sidebar -->
</aside>
