<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

$page_title = "Live Sessions";
$conn = new mysqli("mysql","monitor","monitor123","monitoring");
if ($conn->connect_error) die("DB Error");

// Handle session termination
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'terminate') {
    $session_id = (int)$_POST['session_id'];
    $conn->query("UPDATE lab_sessions SET status='REVOKED' WHERE id=$session_id");
    $_SESSION['message'] = "Session terminated successfully!";
}

include 'includes/header.php';
?>

<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">

<?php include 'includes/admin_sidebar.php'; ?>
<?php include 'includes/admin_topbar.php'; ?>

<div class="content-wrapper app-shell">
  <div class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6">
          <h1 class="m-0"><i class="fas fa-users-circle"></i> Live User Sessions</h1>
        </div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
            <li class="breadcrumb-item active">Live Sessions</li>
          </ol>
        </div>
      </div>
    </div>
  </div>

  <section class="content">
    <div class="container-fluid">
      
      <?php if (isset($_SESSION['message'])): ?>
      <div class="alert alert-success alert-dismissible">
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        <i class="fas fa-check"></i> <?= htmlspecialchars($_SESSION['message']) ?>
      </div>
      <?php unset($_SESSION['message']); endif; ?>

      <!-- Stats Cards -->
      <div class="row mb-4">
        <?php
          $active_count = $conn->query("SELECT COUNT(*) as count FROM lab_sessions WHERE status='ACTIVE'")->fetch_assoc()['count'];
          $requested_count = $conn->query("SELECT COUNT(*) as count FROM lab_sessions WHERE status='REQUESTED'")->fetch_assoc()['count'];
          $expired_count = $conn->query("SELECT COUNT(*) as count FROM lab_sessions WHERE status='EXPIRED' AND DATE(access_expiry) = CURDATE()")->fetch_assoc()['count'];
        ?>
        
        <div class="col-md-4">
          <div class="info-box bg-success">
            <span class="info-box-icon"><i class="fas fa-play-circle"></i></span>
            <div class="info-box-content">
              <span class="info-box-text">Active Sessions</span>
              <span class="info-box-number"><?= $active_count ?></span>
            </div>
          </div>
        </div>

        <div class="col-md-4">
          <div class="info-box bg-warning">
            <span class="info-box-icon"><i class="fas fa-hourglass-half"></i></span>
            <div class="info-box-content">
              <span class="info-box-text">Pending (Requested)</span>
              <span class="info-box-number"><?= $requested_count ?></span>
            </div>
          </div>
        </div>

        <div class="col-md-4">
          <div class="info-box bg-info">
            <span class="info-box-icon"><i class="fas fa-clock"></i></span>
            <div class="info-box-content">
              <span class="info-box-text">Expired Today</span>
              <span class="info-box-number"><?= $expired_count ?></span>
            </div>
          </div>
        </div>
      </div>

      <!-- Active Sessions Table -->
      <div class="card card-primary">
        <div class="card-header">
          <h3 class="card-title"><i class="fas fa-list"></i> Currently Active Sessions</h3>
        </div>
        <div class="card-body table-responsive p-0">
          <table class="table table-hover table-striped">
            <thead class="bg-primary text-white">
              <tr>
                <th style="width: 5%">ID</th>
                <th style="width: 15%">User Email</th>
                <th style="width: 20%">Lab</th>
                <th style="width: 20%">Session Username</th>
                <th style="width: 12%">Status</th>
                <th style="width: 15%">Expires</th>
                <th style="width: 10%">Time Remaining</th>
                <th style="width: 8%">Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php
                $result = $conn->query("
                  SELECT 
                    ls.id,
                    ls.username,
                    ls.status,
                    ls.namespace,
                    ls.access_expiry,
                    l.lab_name,
                    u.email,
                    u.id as user_id
                  FROM lab_sessions ls
                  LEFT JOIN labs l ON ls.lab_id = l.id
                  LEFT JOIN users u ON ls.user_id = u.id
                  WHERE ls.status IN ('ACTIVE', 'REQUESTED')
                  ORDER BY ls.access_expiry ASC
                ");

                if ($result && $result->num_rows > 0) {
                  while ($session = $result->fetch_assoc()) {
                    $expiry_time = strtotime($session['access_expiry']);
                    $current_time = time();
                    $remaining = $expiry_time - $current_time;
                    
                    $hours = floor($remaining / 3600);
                    $mins = floor(($remaining % 3600) / 60);
                    $secs = $remaining % 60;
                    
                    $status_badge = $session['status'] === 'ACTIVE' 
                      ? '<span class="badge badge-success"><i class="fas fa-check-circle"></i> ACTIVE</span>'
                      : '<span class="badge badge-warning"><i class="fas fa-hourglass-half"></i> REQUESTED</span>';
                    
                    $time_text = $remaining > 0 
                      ? "{$hours}h {$mins}m {$secs}s"
                      : '<span class="text-danger"><strong>EXPIRED</strong></span>';
              ?>
              <tr>
                <td><strong>#<?= $session['id'] ?></strong></td>
                <td><?= htmlspecialchars($session['email'] ?? 'N/A') ?></td>
                <td><?= htmlspecialchars($session['lab_name'] ?? 'N/A') ?></td>
                <td><code><?= htmlspecialchars($session['username']) ?></code></td>
                <td><?= $status_badge ?></td>
                <td><?= date('M d, Y H:i:s', strtotime($session['access_expiry'])) ?></td>
                <td><?= $time_text ?></td>
                <td>
                  <?php if ($session['status'] === 'ACTIVE'): ?>
                  <form method="POST" style="display:inline;">
                    <input type="hidden" name="action" value="terminate">
                    <input type="hidden" name="session_id" value="<?= $session['id'] ?>">
                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Terminate this session?')">
                      <i class="fas fa-times"></i> End
                    </button>
                  </form>
                  <?php else: ?>
                  <span class="text-muted">-</span>
                  <?php endif; ?>
                </td>
              </tr>
              <?php
                  }
                } else {
              ?>
              <tr>
                <td colspan="8" class="text-center text-muted p-4">
                  <i class="fas fa-inbox fa-2x mb-2"></i><br>
                  No active sessions
                </td>
              </tr>
              <?php } ?>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Recent Sessions History -->
      <div class="card card-info mt-4">
        <div class="card-header">
          <h3 class="card-title"><i class="fas fa-history"></i> Session History (Last 7 Days)</h3>
        </div>
        <div class="card-body table-responsive p-0">
          <table class="table table-hover table-striped">
            <thead class="bg-info text-white">
              <tr>
                <th style="width: 5%">ID</th>
                <th style="width: 15%">Username</th>
                <th style="width: 20%">Lab</th>
                <th style="width: 15%">Status</th>
                <th style="width: 18%">Started</th>
                <th style="width: 18%">Ended</th>
                <th style="width: 9%">Duration</th>
              </tr>
            </thead>
            <tbody>
              <?php
                $result = $conn->query("
                  SELECT 
                    ls.id,
                    ls.username,
                    ls.status,
                    ls.access_start,
                    ls.access_expiry,
                    l.lab_name
                  FROM lab_sessions ls
                  LEFT JOIN labs l ON ls.lab_id = l.id
                  WHERE ls.status IN ('EXPIRED', 'REVOKED') 
                    AND ls.access_start >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                  ORDER BY ls.access_expiry DESC
                  LIMIT 20
                ");

                if ($result && $result->num_rows > 0) {
                  while ($session = $result->fetch_assoc()) {
                    $start = strtotime($session['access_start']);
                    $end = strtotime($session['access_expiry']);
                    $duration = $end - $start;
                    
                    $hours = floor($duration / 3600);
                    $mins = floor(($duration % 3600) / 60);
                    $duration_text = "{$hours}h {$mins}m";
                    
                    $status_badge = $session['status'] === 'EXPIRED' 
                      ? '<span class="badge badge-secondary"><i class="fas fa-check-circle"></i> EXPIRED</span>'
                      : '<span class="badge badge-danger"><i class="fas fa-ban"></i> REVOKED</span>';
              ?>
              <tr>
                <td><strong>#<?= $session['id'] ?></strong></td>
                <td><?= htmlspecialchars($session['username']) ?></td>
                <td><?= htmlspecialchars($session['lab_name'] ?? 'N/A') ?></td>
                <td><?= $status_badge ?></td>
                <td><?= date('M d, H:i', $start) ?></td>
                <td><?= date('M d, H:i', $end) ?></td>
                <td><?= $duration_text ?></td>
              </tr>
              <?php
                  }
                } else {
              ?>
              <tr>
                <td colspan="7" class="text-center text-muted p-4">
                  <i class="fas fa-inbox fa-2x mb-2"></i><br>
                  No session history
                </td>
              </tr>
              <?php } ?>
            </tbody>
          </table>
        </div>
      </div>

    </div>
  </section>
</div>

<?php include 'includes/footer.php'; ?>

<script>
// Auto-refresh page every 30 seconds
setTimeout(function() {
  location.reload();
}, 30000);
</script>

</body>
</html>
