<?php
/**
 * LAB WIDGETS FOR DASHBOARD
 * Purpose: Role-aware dashboard widgets for Labs platform
 * Integration: Include this in index.php BELOW existing widgets
 * Backward Compatible: Does NOT modify existing dashboard
 */

if (!isset($_SESSION['user'], $_SESSION['role'])) {
    return; // Silent fail if session not active
}

$role = $_SESSION['role'];
$uid = (int)$_SESSION['uid'];

// Database connection (reuse existing)
if (!isset($conn)) {
    $conn = new mysqli("mysql","monitor","monitor123","monitoring");
}

// =============================================
// ADMIN WIDGETS
// =============================================
if ($role === 'admin'): ?>

<div class="row mt-3">
  <div class="col-12">
    <h5 class="text-muted"><i class="fas fa-flask"></i> Labs Platform Management</h5>
  </div>
</div>

<div class="row">
  <!-- Total Lab Requests -->
  <div class="col-lg-3 col-6">
    <div class="small-box" style="background: linear-gradient(135deg, #667eea, #764ba2); color: white;">
      <div class="inner">
        <?php
        $q = $conn->query("SELECT COUNT(*) as cnt FROM lab_requests WHERE status='pending'");
        $pending = $q ? $q->fetch_assoc()['cnt'] : 0;
        ?>
        <h3><?= $pending ?></h3>
        <p>Pending Lab Requests</p>
      </div>
      <div class="icon">
        <i class="fas fa-clock"></i>
      </div>
      <a href="admin_lab_requests.php" class="small-box-footer">
        Review Requests <i class="fas fa-arrow-circle-right"></i>
      </a>
    </div>
  </div>

  <!-- Active Labs -->
  <div class="col-lg-3 col-6">
    <div class="small-box bg-success">
      <div class="inner">
        <?php
        $q = $conn->query("SELECT COUNT(*) as cnt FROM lab_sessions WHERE status='ACTIVE'");
        $active = $q ? $q->fetch_assoc()['cnt'] : 0;
        ?>
        <h3><?= $active ?></h3>
        <p>Active Lab Sessions</p>
      </div>
      <div class="icon">
        <i class="fas fa-play-circle"></i>
      </div>
      <a href="labs.php?filter=active" class="small-box-footer">
        View Details <i class="fas fa-arrow-circle-right"></i>
      </a>
    </div>
  </div>

  <!-- Total Courses -->
  <div class="col-lg-3 col-6">
    <div class="small-box bg-info">
      <div class="inner">
        <?php
        $q = $conn->query("SELECT COUNT(*) as cnt FROM courses WHERE status='published'");
        $courses = $q ? $q->fetch_assoc()['cnt'] : 0;
        ?>
        <h3><?= $courses ?></h3>
        <p>Published Courses</p>
      </div>
      <div class="icon">
        <i class="fas fa-book"></i>
      </div>
      <a href="courses.php" class="small-box-footer">
        Manage Courses <i class="fas fa-arrow-circle-right"></i>
      </a>
    </div>
  </div>

  <!-- Provisioners Status -->
  <div class="col-lg-3 col-6">
    <div class="small-box bg-warning">
      <div class="inner">
        <?php
        $q = $conn->query("SELECT COUNT(*) as cnt FROM provisioners WHERE status='active'");
        $provisioners = $q ? $q->fetch_assoc()['cnt'] : 0;
        ?>
        <h3><?= $provisioners ?></h3>
        <p>Active Provisioners</p>
      </div>
      <div class="icon">
        <i class="fas fa-server"></i>
      </div>
      <a href="provisioners.php" class="small-box-footer">
        Manage Workers <i class="fas fa-arrow-circle-right"></i>
      </a>
    </div>
  </div>
</div>

<?php endif; ?>

<?php
// =============================================
// USER WIDGETS
// =============================================
if ($role === 'user'): ?>

<div class="row mt-4">
  <div class="col-12">
    <h5 class="text-muted"><i class="fas fa-graduation-cap"></i> My Learning Journey</h5>
  </div>
</div>

<div class="row">
  <!-- My Active Lab -->
  <div class="col-lg-6">
    <div class="card">
      <div class="card-header" style="background: linear-gradient(135deg, #667eea, #764ba2); color: white;">
        <h3 class="card-title"><i class="fas fa-flask"></i> Current Lab Session</h3>
      </div>
      <div class="card-body">
        <?php
        $stmt = $conn->prepare("
            SELECT ls.*, l.lab_name 
            FROM lab_sessions ls
            LEFT JOIN labs l ON ls.lab_id = l.id
            WHERE ls.user_id = ? AND ls.status = 'ACTIVE' AND ls.access_expiry > NOW()
            ORDER BY ls.id DESC LIMIT 1
        ");
        $stmt->bind_param("i", $uid);
        $stmt->execute();
        $active_lab = $stmt->get_result()->fetch_assoc();
        
        if ($active_lab):
        ?>
          <div class="alert alert-success">
            <i class="fas fa-check-circle"></i> <strong>Lab Active!</strong>
          </div>
          <p><strong>Lab:</strong> <?= htmlspecialchars($active_lab['lab_name'] ?? 'Lab Environment') ?></p>
          <p><strong>Expires:</strong> <?= date('d M Y, h:i A', strtotime($active_lab['access_expiry'])) ?></p>
          <a href="lab_terminal.php" class="btn btn-primary btn-block">
            <i class="fas fa-terminal"></i> Open Terminal
          </a>
        <?php else: ?>
          <div class="text-center text-muted">
            <i class="fas fa-info-circle fa-3x mb-3" style="opacity: 0.3;"></i>
            <p>No active lab session</p>
            <a href="my_labs.php" class="btn btn-outline-primary">
              <i class="fas fa-search"></i> Browse Available Labs
            </a>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- My Progress -->
  <div class="col-lg-6">
    <div class="card">
      <div class="card-header bg-info">
        <h3 class="card-title"><i class="fas fa-chart-line"></i> Learning Progress</h3>
      </div>
      <div class="card-body">
        <?php
        $stmt = $conn->prepare("
            SELECT 
                COUNT(*) as total_labs,
                SUM(CASE WHEN status='ACTIVE' THEN 1 ELSE 0 END) as active_labs
            FROM lab_sessions
            WHERE user_id = ? AND access_expiry > NOW()
        ");
        $stmt->bind_param("i", $uid);
        $stmt->execute();
        $progress = $stmt->get_result()->fetch_assoc();
        
        $total = $progress['total_labs'] ?? 0;
        $active = $progress['active_labs'] ?? 0;
        ?>
        
        <div class="row text-center">
          <div class="col-6">
            <h4><?= $active ?>/<?= $total ?></h4>
            <small class="text-muted">Active Sessions</small>
          </div>
          <div class="col-6">
            <h4><?= $total ?></h4>
            <small class="text-muted">Total Accessed</small>
          </div>
        </div>
        
        <div class="progress mt-3" style="height: 25px;">
          <div class="progress-bar" role="progressbar" 
               style="width: <?= ($total > 0 ? round(($active / $total) * 100) : 0) ?>%; background: linear-gradient(135deg, #667eea, #764ba2);">
            <?= ($total > 0 ? round(($active / $total) * 100) : 0) ?>%
          </div>
        </div>
        
        <a href="my_labs.php" class="btn btn-info btn-block mt-3">
          <i class="fas fa-list"></i> View All My Labs
        </a>
      </div>
    </div>
  </div>
</div>

<!-- Quick Access Courses -->
<div class="row mt-3">
  <div class="col-12">
    <div class="card">
      <div class="card-header">
        <h3 class="card-title"><i class="fas fa-book-open"></i> Featured Courses</h3>
        <div class="card-tools">
          <a href="my_labs.php" class="btn btn-sm btn-primary">View All</a>
        </div>
      </div>
      <div class="card-body">
        <div class="row">
          <?php
          $courses = $conn->query("
              SELECT * FROM courses 
              WHERE status='published' 
              ORDER BY created_at DESC 
              LIMIT 3
          ");
          
          if ($courses && $courses->num_rows > 0):
              while ($course = $courses->fetch_assoc()):
          ?>
            <div class="col-md-4">
              <div class="card">
                <div class="card-body text-center">
                  <i class="fas <?= $course['icon'] ?> fa-3x mb-2" style="color: <?= $course['color'] ?>;"></i>
                  <h5><?= htmlspecialchars($course['title']) ?></h5>
                  <p class="text-muted small"><?= htmlspecialchars(substr($course['description'], 0, 80)) ?>...</p>
                  <span class="badge badge-info"><?= ucfirst($course['difficulty']) ?></span>
                  <span class="badge badge-secondary"><?= $course['duration_hours'] ?>h</span>
                </div>
              </div>
            </div>
          <?php 
              endwhile;
          else:
          ?>
            <div class="col-12 text-center text-muted">
              <p>No courses available yet. Check back soon!</p>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</div>

<?php endif; ?>
