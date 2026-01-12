<?php
session_start();
// Redirect to new my_labs.php
header("Location: my_labs.php");
exit;
?>
?>

<body class="hold-transition sidebar-mini">
<div class="wrapper">

<?php include 'includes/navbar.php'; ?>
<?php include 'includes/sidebar.php'; ?>

<div class="content-wrapper">
  <div class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6">
          <h1 class="m-0"><i class="fas fa-laptop-code"></i> My Active Labs</h1>
        </div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
            <li class="breadcrumb-item active">Active Labs</li>
          </ol>
        </div>
      </div>
    </div>
  </div>

  <section class="content">
    <div class="container-fluid">

      <!-- Active Sessions -->
      <?php
      $active_q = $conn->query("
        SELECT ls.*, lt.title as lab_title, lt.description, c.title as course_title
        FROM lab_sessions ls
        JOIN lab_templates lt ON ls.lab_template_id = lt.id
        JOIN courses c ON lt.course_id = c.id
        WHERE ls.user_id = $uid AND ls.status = 'ACTIVE'
        ORDER BY ls.created_at DESC
      ");

      if ($active_q->num_rows > 0):
        while($session = $active_q->fetch_assoc()):
          $time_left = strtotime($session['expires_at']) - time();
          $hours = floor($time_left / 3600);
          $mins = floor(($time_left % 3600) / 60);
          $pct = round((($session['expires_at'] ? strtotime($session['expires_at']) - time() : 0) / 3600) * 100);
      ?>
      <div class="card card-success">
        <div class="card-header">
          <h3 class="card-title">
            <i class="fas fa-play-circle"></i> <?= htmlspecialchars($session['lab_title']) ?>
          </h3>
          <div class="card-tools">
            <span class="badge badge-light">
              <i class="fas fa-clock"></i> <?= $hours ?>h <?= $mins ?>m remaining
            </span>
          </div>
        </div>
        <div class="card-body">
          <p><strong>Course:</strong> <?= htmlspecialchars($session['course_title']) ?></p>
          <p><strong>Pod:</strong> <?= htmlspecialchars($session['pod_name']) ?></p>
          <p><strong>Started:</strong> <?= date('M j, Y g:i A', strtotime($session['created_at'])) ?></p>
          <p><strong>Expires:</strong> <?= date('M j, Y g:i A', strtotime($session['expires_at'])) ?></p>
          
          <div class="progress mb-3">
            <div class="progress-bar bg-<?= $pct > 30 ? 'success' : 'danger' ?>" style="width: <?= min($pct, 100) ?>%">
              Time Left
            </div>
          </div>

          <a href="terminal.php" class="btn btn-success btn-lg">
            <i class="fas fa-terminal"></i> Connect to Lab
          </a>
        </div>
      </div>
      <?php endwhile; else: ?>
      
      <div class="alert alert-info">
        <h5><i class="icon fas fa-info-circle"></i> No Active Labs</h5>
        You don't have any active lab sessions at the moment.
        <a href="browse_labs.php" class="btn btn-primary btn-sm ml-3">
          <i class="fas fa-search"></i> Browse Labs
        </a>
      </div>
      
      <?php endif; ?>

      <!-- Requested Labs -->
      <div class="card">
        <div class="card-header">
          <h3 class="card-title"><i class="fas fa-hourglass-half"></i> Pending Requests</h3>
        </div>
        <div class="card-body table-responsive p-0">
          <table class="table table-hover">
            <thead>
              <tr>
                <th>Lab</th>
                <th>Course</th>
                <th>Requested</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody>
              <?php
              $requests_q = $conn->query("
                SELECT lr.*, lt.title as lab_title, c.title as course_title
                FROM lab_requests lr
                JOIN lab_templates lt ON lr.lab_template_id = lt.id
                JOIN courses c ON lt.course_id = c.id
                WHERE lr.user_id = $uid AND lr.status = 'pending'
                ORDER BY lr.created_at DESC
              ");
              if ($requests_q->num_rows > 0):
                while($req = $requests_q->fetch_assoc()):
              ?>
              <tr>
                <td><?= htmlspecialchars($req['lab_title']) ?></td>
                <td><?= htmlspecialchars($req['course_title']) ?></td>
                <td><?= date('M j, Y g:i A', strtotime($req['created_at'])) ?></td>
                <td><span class="badge badge-warning">Pending Approval</span></td>
              </tr>
              <?php endwhile; else: ?>
              <tr>
                <td colspan="4" class="text-center text-muted">No pending requests</td>
              </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Recent Activity -->
      <div class="card">
        <div class="card-header">
          <h3 class="card-title"><i class="fas fa-history"></i> Recent Lab Activity</h3>
        </div>
        <div class="card-body table-responsive p-0">
          <table class="table table-hover">
            <thead>
              <tr>
                <th>Lab</th>
                <th>Course</th>
                <th>Status</th>
                <th>Progress</th>
                <th>Last Activity</th>
              </tr>
            </thead>
            <tbody>
              <?php
              $history_q = $conn->query("
                SELECT lp.*, lt.title as lab_title, c.title as course_title
                FROM lab_progress lp
                JOIN lab_templates lt ON lp.lab_template_id = lt.id
                JOIN courses c ON lt.course_id = c.id
                WHERE lp.user_id = $uid
                ORDER BY lp.updated_at DESC
                LIMIT 10
              ");
              while($h = $history_q->fetch_assoc()):
                $badge = $h['status'] === 'completed' ? 'success' : 'info';
              ?>
              <tr>
                <td><?= htmlspecialchars($h['lab_title']) ?></td>
                <td><?= htmlspecialchars($h['course_title']) ?></td>
                <td><span class="badge badge-<?= $badge ?>"><?= ucfirst($h['status']) ?></span></td>
                <td>
                  <div class="progress" style="height: 20px;">
                    <div class="progress-bar" style="width: <?= $h['progress_percent'] ?>%">
                      <?= $h['progress_percent'] ?>%
                    </div>
                  </div>
                </td>
                <td><?= date('M j, Y g:i A', strtotime($h['updated_at'])) ?></td>
              </tr>
              <?php endwhile; ?>
            </tbody>
          </table>
        </div>
      </div>

    </div>
  </section>
</div>

<?php include 'includes/footer.php'; ?>

</div>
</body>
</html>
