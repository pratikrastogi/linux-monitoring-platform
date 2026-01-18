<!-- Navbar -->
<nav class="main-header navbar navbar-expand navbar-white navbar-light">
  <!-- Left navbar links -->
  <ul class="navbar-nav">
    <li class="nav-item">
      <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
    </li>
    <li class="nav-item d-none d-sm-inline-block">
      <a href="index.php" class="nav-link">Home</a>
    </li>
    <li class="nav-item d-none d-sm-inline-block">
      <a href="charts.php" class="nav-link">Charts</a>
    </li>
  </ul>

  <!-- Right navbar links -->
  <ul class="navbar-nav ml-auto">
    <!-- Dark Mode Toggle -->
    <li class="nav-item">
      <a class="nav-link" id="darkModeToggle" href="#" role="button" title="Toggle Dark Mode">
        <i class="fas fa-moon"></i>
      </a>
    </li>
    
    <!-- Notifications Dropdown Menu -->
    <li class="nav-item dropdown">
      <a class="nav-link" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">
        <i class="far fa-bell"></i>
        <span class="badge badge-warning navbar-badge" id="alertCount">0</span>
      </a>
      <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
        <span class="dropdown-item dropdown-header">Alerts</span>
        <div class="dropdown-divider"></div>
        <div id="alertDropdown">
          <a href="#" class="dropdown-item">
            <i class="fas fa-info-circle mr-2"></i> No alerts
          </a>
        </div>
        <div class="dropdown-divider"></div>
        <a href="alerts.php" class="dropdown-item dropdown-footer">View All Alerts</a>
      </div>
    </li>
    
    <!-- User Menu -->
    <li class="nav-item dropdown">
      <a class="nav-link" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">
        <i class="far fa-user"></i>
        <span class="d-none d-md-inline ml-1"><?php echo htmlspecialchars($_SESSION['user']); ?></span>
      </a>
      <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
        <span class="dropdown-item dropdown-header">
          <i class="fas fa-user mr-2"></i> <?php echo htmlspecialchars($_SESSION['user']); ?>
        </span>
        <div class="dropdown-divider"></div>
        <span class="dropdown-item">
          <i class="fas fa-shield-alt mr-2"></i> Role: <strong><?php echo ucfirst($_SESSION['role']); ?></strong>
        </span>
        <div class="dropdown-divider"></div>
        <a href="profile.php" class="dropdown-item">
          <i class="fas fa-user-circle mr-2"></i> My Profile
        </a>
        <div class="dropdown-divider"></div>
        <a href="logout.php" class="dropdown-item bg-danger text-white">
          <i class="fas fa-sign-out-alt mr-2"></i> Logout
        </a>
      </div>
    </li>
    
    <!-- Fullscreen Toggle -->
    <li class="nav-item">
      <a class="nav-link" data-widget="fullscreen" href="#" role="button">
        <i class="fas fa-expand-arrows-alt"></i>
      </a>
    </li>
  </ul>
</nav>
<!-- /.navbar -->

<script>
// Dark Mode Toggle
document.addEventListener('DOMContentLoaded', function() {
  const darkModeToggle = document.getElementById('darkModeToggle');
  
  if (darkModeToggle) {
    darkModeToggle.addEventListener('click', function(e) {
      e.preventDefault();
      document.body.classList.toggle('dark-mode');
      
      const isDark = document.body.classList.contains('dark-mode');
      document.cookie = "dark_mode=" + (isDark ? "1" : "0") + "; path=/; max-age=31536000";
      
      const icon = this.querySelector('i');
      icon.className = isDark ? 'fas fa-sun' : 'fas fa-moon';
    });
    
    // Set initial icon
    if (document.body.classList.contains('dark-mode')) {
      darkModeToggle.querySelector('i').className = 'fas fa-sun';
    }
  }
});
</script>
