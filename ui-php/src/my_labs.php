<?php
session_start();
require_once 'auth.php';

$db = new mysqli("mysql", "monitor", "monitor123", "monitoring");
$uid = $_SESSION['uid'];

$active = $db->query("SELECT ls.*, l.lab_name, c.name as course_name 
    FROM lab_sessions ls 
    JOIN labs l ON ls.lab_id = l.id 
    JOIN courses c ON l.course_id = c.id 
    WHERE ls.user_id=$uid AND ls.status='ACTIVE' AND ls.access_expiry > NOW()");

$pending = $db->query("SELECT lr.*, l.lab_name, c.name as course_name 
    FROM lab_requests lr 
    JOIN labs l ON lr.lab_id = l.id 
    JOIN courses c ON l.course_id = c.id 
    WHERE lr.user_id=$uid AND lr.status='pending'");

$page_title = "My Labs";
include 'includes/header.php';
?>
<body class="hold-transition sidebar-mini">
<div class="wrapper">
<?php include 'includes/admin_sidebar.php'; ?>
<?php include 'includes/admin_topbar.php'; ?>
<div class="content-wrapper app-shell">
  <div class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6">
          <h1 class="m-0"><i class="fas fa-laptop-code"></i> My Labs</h1>
        </div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
            <li class="breadcrumb-item active">My Labs</li>
          </ol>
        </div>
      </div>
    </div>
  </div>
  <section class="content">
    <div class="container-fluid">
      <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible">
          <button type="button" class="close" data-dismiss="alert">&times;</button>
          <i class="fas fa-check"></i> <?= $_SESSION['success']; unset($_SESSION['success']); ?>
        </div>
      <?php endif; ?>
      
      <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible">
          <button type="button" class="close" data-dismiss="alert">&times;</button>
          <i class="fas fa-exclamation-circle"></i> <?= $_SESSION['error']; unset($_SESSION['error']); ?>
        </div>
      <?php endif; ?>
      
      <?php if (isset($_GET['msg']) && $_GET['msg'] === 'requested'): ?>
        <div class="alert alert-success alert-dismissible">
          <button type="button" class="close" data-dismiss="alert">&times;</button>
          <i class="fas fa-check"></i> Request submitted! Waiting for admin approval.
        </div>
      <?php endif; ?>
      
      <div class="card card-success">
        <div class="card-header">
          <h3 class="card-title"><i class="fas fa-play-circle"></i> Active Lab Sessions</h3>
        </div>
        <div class="card-body">
          <?php if ($active->num_rows === 0): ?>
            <p class="text-muted"><i class="fas fa-info-circle"></i> No active labs. <a href="browse_labs.php">Browse available labs</a></p>
          <?php else: ?>
            <div class="row">
              <?php while ($lab = $active->fetch_assoc()): 
                $remaining = strtotime($lab['access_expiry']) - time();
                $mins = floor($remaining / 60);
                $hours = floor($mins / 60);
              ?>
                <div class="col-md-6">
                  <div class="card bg-light border-success">
                    <div class="card-body">
                      <h5 class="card-title"><?= htmlspecialchars($lab['course_name']) ?></h5>
                      <h6 class="card-subtitle mb-2"><i class="fas fa-flask"></i> <?= htmlspecialchars($lab['lab_name']) ?></h6>
                      <p class="card-text text-warning"><i class="fas fa-clock"></i> <strong><?= $hours ?>h <?= $mins % 60 ?>m remaining</strong></p>
                      <p class="card-text text-muted"><small>Expires: <?= date('M d, Y H:i', strtotime($lab['access_expiry'])) ?></small></p>
                      <div class="alert alert-info alert-sm mb-2">
                        <small><i class="fas fa-info-circle"></i> To extend lab access, please contact <strong>admin or support</strong>.</small>
                      </div>
                      <a href="lab_terminal.php?session_id=<?= $lab['id'] ?>" class="btn btn-primary btn-sm">
                        <i class="fas fa-terminal"></i> Launch Terminal
                      </a>
                    </div>
                  </div>
                </div>
              <?php endwhile; ?>
            </div>
          <?php endif; ?>
        </div>
      </div>
      
      <div class="card card-warning">
        <div class="card-header">
          <h3 class="card-title"><i class="fas fa-hourglass-half"></i> Pending Requests</h3>
        </div>
        <div class="card-body table-responsive p-0">
          <?php if ($pending->num_rows === 0): ?>
            <p class="text-muted p-3"><i class="fas fa-info-circle"></i> No pending requests.</p>
          <?php else: ?>
            <table class="table table-hover">
              <thead>
                <tr>
                  <th>Course</th>
                  <th>Lab</th>
                  <th>Requested</th>
                  <th>Status</th>
                </tr>
              </thead>
              <tbody>
                <?php while ($req = $pending->fetch_assoc()): ?>
                  <tr>
                    <td><?= htmlspecialchars($req['course_name']) ?></td>
                    <td><?= htmlspecialchars($req['lab_name']) ?></td>
                    <td><?= date('M d, Y H:i', strtotime($req['created_at'])) ?></td>
                    <td><span class="badge badge-warning">Pending</span></td>
                  </tr>
                <?php endwhile; ?>
              </tbody>
            </table>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </section>
</div>
<?php include 'includes/footer.php'; ?>
</div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
</body>
</html>
