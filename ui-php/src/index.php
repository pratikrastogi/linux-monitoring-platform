<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$role = $_SESSION['role'];
$username = $_SESSION['user'];
$uid = $_SESSION['uid'];
$page_title = "Dashboard";

$conn = new mysqli("mysql","monitor","monitor123","monitoring");
if ($conn->connect_error) die("DB Error");

include 'includes/header.php';
?>

<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">

<?php include 'includes/navbar.php'; ?>
<?php include 'includes/sidebar.php'; ?>

<!-- Content Wrapper -->
<div class="content-wrapper">
  <!-- Content Header -->
  <div class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6">
          <h1 class="m-0">
            <i class="fas fa-tachometer-alt"></i> 
            <?= $role === 'admin' ? 'Admin Dashboard' : 'My Learning Dashboard' ?>
          </h1>
        </div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="#">Home</a></li>
            <li class="breadcrumb-item active">Dashboard</li>
          </ol>
        </div>
      </div>
    </div>
  </div>

  <!-- Main content -->
  <section class="content">
    <div class="container-fluid">
      
      <?php if ($role === 'admin'): ?>
      <!-- ================================= -->
      <!-- ADMIN DASHBOARD -->
      <!-- ================================= -->
      
      <!-- Welcome Card -->
      <div class="row">
        <div class="col-12">
          <div class="card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
            <div class="card-body">
              <h3><i class="fas fa-crown"></i> Welcome, Administrator!</h3>
              <p class="mb-0">Manage courses, labs, users, and platform operations from your control panel.</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Admin Stats Row -->
      <div class="row">
        <!-- Pending Lab Requests -->
        <div class="col-lg-3 col-6">
          <div class="small-box bg-warning">
            <div class="inner">
              <?php
              $q = $conn->query("SELECT COUNT(*) as cnt FROM lab_requests WHERE status='pending'");
              $pending = $q ? $q->fetch_assoc()['cnt'] : 0;
              ?>
              <h3><?= $pending ?></h3>
              <p>Pending Requests</p>
            </div>
            <div class="icon">
              <i class="fas fa-clock"></i>
            </div>
            <a href="admin_lab_requests.php" class="small-box-footer">
              Review <i class="fas fa-arrow-circle-right"></i>
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
            <a href="admin_lab_sessions.php" class="small-box-footer">
              View All <i class="fas fa-arrow-circle-right"></i>
            </a>
          </div>
        </div>

        <!-- Total Courses -->
        <div class="col-lg-3 col-6">
          <div class="small-box bg-info">
            <div class="inner">
              <?php
              $q = $conn->query("SELECT COUNT(*) as cnt FROM courses WHERE active=1");
              $courses = $q ? $q->fetch_assoc()['cnt'] : 0;
              ?>
              <h3><?= $courses ?></h3>
              <p>Active Courses</p>
            </div>
            <div class="icon">
              <i class="fas fa-graduation-cap"></i>
            </div>
            <a href="courses.php" class="small-box-footer">
              Manage <i class="fas fa-arrow-circle-right"></i>
            </a>
          </div>
        </div>

        <!-- Total Users -->
        <div class="col-lg-3 col-6">
          <div class="small-box bg-danger">
            <div class="inner">
              <?php
              $q = $conn->query("SELECT COUNT(*) as cnt FROM users");
              $users = $q ? $q->fetch_assoc()['cnt'] : 0;
              ?>
              <h3><?= $users ?></h3>
              <p>Registered Users</p>
            </div>
            <div class="icon">
              <i class="fas fa-users"></i>
            </div>
            <a href="admin_users.php" class="small-box-footer">
              Manage <i class="fas fa-arrow-circle-right"></i>
            </a>
          </div>
        </div>
      </div>

      <!-- Quick Actions -->
      <div class="row">
        <div class="col-md-12">
          <div class="card">
            <div class="card-header">
              <h3 class="card-title"><i class="fas fa-bolt"></i> Quick Actions</h3>
            </div>
            <div class="card-body">
              <a href="courses.php?action=create" class="btn btn-primary mr-2">
                <i class="fas fa-plus"></i> Create Course
              </a>
              <a href="labs.php?action=create" class="btn btn-success mr-2">
                <i class="fas fa-flask"></i> Create Lab
              </a>
              <a href="lab_guides.php?action=create" class="btn btn-info mr-2">
                <i class="fas fa-book"></i> Create Lab Guide
              </a>
              <a href="provisioners.php?action=add" class="btn btn-warning mr-2">
                <i class="fas fa-server"></i> Add Provisioner
              </a>
              <a href="admin_users.php?action=create" class="btn btn-secondary">
                <i class="fas fa-user-plus"></i> Add User
              </a>
            </div>
          </div>
        </div>
      </div>

      <!-- Recent Requests Table -->
      <div class="row">
        <div class="col-md-12">
          <div class="card">
            <div class="card-header">
              <h3 class="card-title"><i class="fas fa-list"></i> Recent Lab Requests</h3>
            </div>
            <div class="card-body table-responsive p-0">
              <table class="table table-hover">
                <thead>
                  <tr>
                    <th>User</th>
                    <th>Lab</th>
                    <th>Course</th>
                    <th>Requested</th>
                    <th>Status</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                  $q = $conn->query("
                    SELECT lr.id, u.email, lt.title as lab_title, c.title as course_title, 
                           lr.created_at, lr.status
                    FROM lab_requests lr
                    JOIN users u ON lr.user_id = u.id
                    JOIN lab_templates lt ON lr.lab_template_id = lt.id
                    JOIN courses c ON lt.course_id = c.id
                    ORDER BY lr.created_at DESC
                    LIMIT 10
                  ");
                  while($r = $q->fetch_assoc()):
                    $badge = $r['status'] === 'pending' ? 'warning' : ($r['status'] === 'approved' ? 'success' : 'danger');
                  ?>
                  <tr>
                    <td><?= htmlspecialchars($r['email']) ?></td>
                    <td><?= htmlspecialchars($r['lab_title']) ?></td>
                    <td><?= htmlspecialchars($r['course_title']) ?></td>
                    <td><?= date('M j, Y g:i A', strtotime($r['created_at'])) ?></td>
                    <td><span class="badge badge-<?= $badge ?>"><?= ucfirst($r['status']) ?></span></td>
                    <td>
                      <?php if($r['status'] === 'pending'): ?>
                      <a href="admin_lab_requests.php?approve=<?= $r['id'] ?>" class="btn btn-xs btn-success">
                        <i class="fas fa-check"></i> Approve
                      </a>
                      <a href="admin_lab_requests.php?deny=<?= $r['id'] ?>" class="btn btn-xs btn-danger">
                        <i class="fas fa-times"></i> Deny
                      </a>
                      <?php else: ?>
                      <a href="admin_lab_requests.php?view=<?= $r['id'] ?>" class="btn btn-xs btn-info">
                        <i class="fas fa-eye"></i> View
                      </a>
                      <?php endif; ?>
                    </td>
                  </tr>
                  <?php endwhile; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>

      <?php else: ?>
      <!-- ================================= -->
      <!-- USER DASHBOARD -->
      <!-- ================================= -->
      
      <!-- Welcome Card -->
      <div class="row">
        <div class="col-12">
          <div class="card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
            <div class="card-body">
              <h3><i class="fas fa-rocket"></i> Welcome back, <?= htmlspecialchars($username) ?>!</h3>
              <p class="mb-0">Continue your learning journey with hands-on Kubernetes labs.</p>
            </div>
          </div>
        </div>
      </div>

      <!-- User Stats Row -->
      <div class="row">
        <!-- Active Lab -->
        <div class="col-lg-4 col-6">
          <div class="small-box bg-success">
            <div class="inner">
              <?php
              $q = $conn->query("SELECT COUNT(*) as cnt FROM lab_sessions WHERE user_id=$uid AND status='ACTIVE'");
              $my_active = $q ? $q->fetch_assoc()['cnt'] : 0;
              ?>
              <h3><?= $my_active ?></h3>
              <p>Active Lab Session<?= $my_active != 1 ? 's' : '' ?></p>
            </div>
            <div class="icon">
              <i class="fas fa-play-circle"></i>
            </div>
            <a href="my_active_labs.php" class="small-box-footer">
              <?= $my_active > 0 ? 'Connect Now' : 'No Active Labs' ?> <i class="fas fa-arrow-circle-right"></i>
            </a>
          </div>
        </div>

        <!-- Courses Enrolled -->
        <div class="col-lg-4 col-6">
          <div class="small-box bg-info">
            <div class="inner">
              <?php
              $q = $conn->query("
                SELECT COUNT(DISTINCT lt.course_id) as cnt 
                FROM lab_progress lp
                JOIN lab_templates lt ON lp.lab_template_id = lt.id
                WHERE lp.user_id = $uid
              ");
              $enrolled = $q ? $q->fetch_assoc()['cnt'] : 0;
              ?>
              <h3><?= $enrolled ?></h3>
              <p>Courses In Progress</p>
            </div>
            <div class="icon">
              <i class="fas fa-graduation-cap"></i>
            </div>
            <a href="browse_labs.php" class="small-box-footer">
              Browse More <i class="fas fa-arrow-circle-right"></i>
            </a>
          </div>
        </div>

        <!-- Completed Labs -->
        <div class="col-lg-4 col-6">
          <div class="small-box bg-warning">
            <div class="inner">
              <?php
              $q = $conn->query("SELECT COUNT(*) as cnt FROM lab_progress WHERE user_id=$uid AND status='completed'");
              $completed = $q ? $q->fetch_assoc()['cnt'] : 0;
              ?>
              <h3><?= $completed ?></h3>
              <p>Labs Completed</p>
            </div>
            <div class="icon">
              <i class="fas fa-trophy"></i>
            </div>
            <a href="profile.php#achievements" class="small-box-footer">
              View Achievements <i class="fas fa-arrow-circle-right"></i>
            </a>
          </div>
        </div>
      </div>

      <!-- Active Lab Alert -->
      <?php
      $active_session = $conn->query("
        SELECT ls.*, lt.title, c.title as course_title, ls.expires_at
        FROM lab_sessions ls
        JOIN lab_templates lt ON ls.lab_template_id = lt.id
        JOIN courses c ON lt.course_id = c.id
        WHERE ls.user_id = $uid AND ls.status = 'ACTIVE'
        ORDER BY ls.created_at DESC
        LIMIT 1
      ");
      if ($active_session && $active_session->num_rows > 0):
        $lab = $active_session->fetch_assoc();
        $time_left = strtotime($lab['expires_at']) - time();
        $hours_left = floor($time_left / 3600);
        $mins_left = floor(($time_left % 3600) / 60);
      ?>
      <div class="row">
        <div class="col-12">
          <div class="alert alert-success alert-dismissible">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <h5><i class="icon fas fa-check"></i> You have an active lab!</h5>
            <strong><?= htmlspecialchars($lab['course_title']) ?></strong> - <?= htmlspecialchars($lab['title']) ?><br>
            <small>Time remaining: <?= $hours_left ?>h <?= $mins_left ?>m</small><br>
            <a href="terminal.php" class="btn btn-success btn-sm mt-2">
              <i class="fas fa-terminal"></i> Connect to Lab
            </a>
          </div>
        </div>
      </div>
      <?php endif; ?>

      <!-- Featured Courses -->
      <div class="row">
        <div class="col-12">
          <div class="card">
            <div class="card-header">
              <h3 class="card-title"><i class="fas fa-star"></i> Featured Courses</h3>
              <div class="card-tools">
                <a href="browse_labs.php" class="btn btn-sm btn-primary">View All Courses</a>
              </div>
            </div>
            <div class="card-body">
              <div class="row">
                <?php
                $courses_q = $conn->query("SELECT * FROM courses WHERE active=1 ORDER BY created_at DESC LIMIT 3");
                while($course = $courses_q->fetch_assoc()):
                  // Get lab count
                  $lab_count_q = $conn->query("SELECT COUNT(*) as cnt FROM lab_templates WHERE course_id=".$course['id']);
                  $lab_count = $lab_count_q->fetch_assoc()['cnt'];
                  
                  // Check user progress
                  $progress_q = $conn->query("
                    SELECT COUNT(DISTINCT lp.lab_template_id) as completed
                    FROM lab_progress lp
                    JOIN lab_templates lt ON lp.lab_template_id = lt.id
                    WHERE lt.course_id = {$course['id']} AND lp.user_id = $uid AND lp.status = 'completed'
                  ");
                  $progress = $progress_q->fetch_assoc()['completed'];
                  $progress_pct = $lab_count > 0 ? round(($progress / $lab_count) * 100) : 0;
                ?>
                <div class="col-md-4">
                  <div class="card card-outline card-primary">
                    <div class="card-header">
                      <h5 class="card-title"><?= htmlspecialchars($course['title']) ?></h5>
                    </div>
                    <div class="card-body">
                      <p class="text-muted"><?= htmlspecialchars(substr($course['description'], 0, 100)) ?>...</p>
                      <p><i class="fas fa-flask"></i> <?= $lab_count ?> Labs</p>
                      <?php if($progress_pct > 0): ?>
                      <div class="progress mb-2">
                        <div class="progress-bar bg-success" style="width: <?= $progress_pct ?>%"><?= $progress_pct ?>%</div>
                      </div>
                      <?php endif; ?>
                      <a href="browse_labs.php?course=<?= $course['id'] ?>" class="btn btn-primary btn-block">
                        <i class="fas fa-eye"></i> View Labs
                      </a>
                    </div>
                  </div>
                </div>
                <?php endwhile; ?>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Recent Activity -->
      <div class="row">
        <div class="col-md-12">
          <div class="card">
            <div class="card-header">
              <h3 class="card-title"><i class="fas fa-history"></i> My Recent Activity</h3>
            </div>
            <div class="card-body table-responsive p-0">
              <table class="table table-hover">
                <thead>
                  <tr>
                    <th>Lab</th>
                    <th>Course</th>
                    <th>Status</th>
                    <th>Progress</th>
                    <th>Last Updated</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                  $activity_q = $conn->query("
                    SELECT lp.*, lt.title as lab_title, c.title as course_title, c.id as course_id
                    FROM lab_progress lp
                    JOIN lab_templates lt ON lp.lab_template_id = lt.id
                    JOIN courses c ON lt.course_id = c.id
                    WHERE lp.user_id = $uid
                    ORDER BY lp.updated_at DESC
                    LIMIT 5
                  ");
                  while($act = $activity_q->fetch_assoc()):
                    $badge = $act['status'] === 'completed' ? 'success' : 'info';
                  ?>
                  <tr>
                    <td><?= htmlspecialchars($act['lab_title']) ?></td>
                    <td><?= htmlspecialchars($act['course_title']) ?></td>
                    <td><span class="badge badge-<?= $badge ?>"><?= ucfirst($act['status']) ?></span></td>
                    <td>
                      <div class="progress" style="height: 20px;">
                        <div class="progress-bar" style="width: <?= $act['progress_percent'] ?>%">
                          <?= $act['progress_percent'] ?>%
                        </div>
                      </div>
                    </td>
                    <td><?= date('M j, Y', strtotime($act['updated_at'])) ?></td>
                    <td>
                      <a href="request_lab.php?lab=<?= $act['lab_template_id'] ?>" class="btn btn-xs btn-primary">
                        <i class="fas fa-play"></i> Continue
                      </a>
                    </td>
                  </tr>
                  <?php endwhile; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>

      <?php endif; ?>
      
      <!-- Legacy Info boxes (preserved for backward compatibility) -->
      <div class="row" style="display:none;">
        <div class="col-12 col-sm-6 col-md-4">
          <div class="info-box">
            <span class="info-box-icon bg-info elevation-1"><i class="fas fa-server"></i></span>
            <div class="info-box-content">
              <span class="info-box-text">Total Servers</span>
              <span class="info-box-number" id="total">0</span>
            </div>
          </div>
        </div>
        
        <div class="col-12 col-sm-6 col-md-4">
          <div class="info-box mb-3">
            <span class="info-box-icon bg-warning elevation-1"><i class="fas fa-terminal"></i></span>
            <div class="info-box-content">
              <span class="info-box-text">SSHD Down</span>
              <span class="info-box-number" id="sshd">0</span>
            </div>
          </div>
        </div>
        
        <div class="col-12 col-sm-6 col-md-4">
          <div class="info-box mb-3">
            <span class="info-box-icon bg-danger elevation-1"><i class="fas fa-times-circle"></i></span>
            <div class="info-box-content">
              <span class="info-box-text">Host Down</span>
              <span class="info-box-number" id="down">0</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Server Status Table -->
      <div class="row">
        <div class="col-12">
          <div class="card">
            <div class="card-header">
              <h3 class="card-title"><i class="fas fa-list"></i> Server Status</h3>
              <div class="card-tools">
                <button type="button" class="btn btn-sm btn-primary" onclick="loadData()">
                  <i class="fas fa-sync-alt"></i> Refresh
                </button>
              </div>
            </div>
            <div class="card-body">
              <table class="table table-bordered table-striped" id="tbl">
                <thead>
                  <tr>
                    <th>Hostname</th>
                    <th>OS</th>
                    <th>Uptime</th>
                    <th>SSHD</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody></tbody>
              </table>
            </div>
          </div>
        </div>
      </div>

<?php if ($role === 'admin') { ?>
      <!-- Lab Extension Requests -->
      <div class="row">
        <div class="col-12">
          <div class="card" id="lab-requests">
            <div class="card-header">
              <h3 class="card-title"><i class="fas fa-flask"></i> Pending Lab Extension Requests</h3>
            </div>
            <div class="card-body">
              <table class="table table-bordered table-hover">
                <thead>
                  <tr>
                    <th>User</th>
                    <th>Requested Hours</th>
                    <th>Status</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody>
<?php
$res = $conn->query("
  SELECT ler.id, u.username, ler.hours, ler.status
  FROM lab_extension_requests ler
  JOIN users u ON ler.user_id = u.id
  WHERE ler.status='PENDING'
  ORDER BY ler.created_at ASC
");

if ($res->num_rows === 0) {
    echo "<tr><td colspan='4' class='text-center'>No pending lab extension requests</td></tr>";
}

while ($r = $res->fetch_assoc()) {
    echo "<tr>
      <td>{$r['username']}</td>
      <td>{$r['hours']} Hour(s)</td>
      <td><span class='badge badge-warning'>PENDING</span></td>
      <td>
        <a href='approve_lab.php?id={$r['id']}' class='btn btn-sm btn-success'><i class='fas fa-check'></i> Approve</a>
        <a href='reject_lab.php?id={$r['id']}' class='btn btn-sm btn-danger'><i class='fas fa-times'></i> Reject</a>
      </td>
    </tr>";
}
?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
<?php } ?>

    </div>
  </section>
</div>

<?php include 'includes/footer.php'; ?>

<script>
setInterval(loadData, 10000);
loadData();

function loadData() {
 fetch('api/metrics.php')
 .then(r => r.json())
 .then(d => {
  let body = "";
  let sshdDown = 0;
  let hostDown = 0;

  d.forEach(s => {
    let sshdBadge = s.sshd_status === 'active' ? '<span class="badge badge-success">active</span>' : '<span class="badge badge-danger">' + s.sshd_status + '</span>';
    if (s.sshd_status !== 'active') sshdDown++;
    if (s.reachable == 0) hostDown++;

    body += `<tr>
      <td>${s.hostname}</td>
      <td>${s.os_version}</td>
      <td>${s.uptime}</td>
      <td>${sshdBadge}</td>
      <td>
        <a href="terminal.php?id=${s.server_id}" class="btn btn-sm btn-primary"><i class="fas fa-terminal"></i> Terminal</a>
        <a href="delete_server.php?id=${s.server_id}" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i> Delete</a>
      </td>
    </tr>`;
  });

  document.querySelector("#tbl tbody").innerHTML = body;
  document.getElementById("total").innerText = d.length;
  document.getElementById("sshd").innerText = sshdDown;
  document.getElementById("down").innerText = hostDown;
 });
}
</script>

<?php
// =============================================
// LABS PLATFORM WIDGETS (ADDITIVE - Phase 2)
// Purpose: Add Labs dashboard widgets below existing content
// Backward Compatible: Does NOT modify existing functionality
// =============================================
if (file_exists('widgets/lab_widgets.php')) {
    include 'widgets/lab_widgets.php';
}
?>

</body>
</html>

