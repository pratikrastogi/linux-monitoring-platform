<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

$page_title = "User Management";
$conn = new mysqli("mysql","monitor","monitor123","monitoring");
if ($conn->connect_error) die("DB Error");

// Handle direct lab assignment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['assign_lab'])) {
    $user_id = (int)$_POST['user_id'];
    $lab_id = (int)$_POST['lab_id'];
    $validity_hours = (int)$_POST['validity_hours'];
    $notes = $conn->real_escape_string($_POST['notes']);
    
    // Check if user already has an active session for this lab
    $existing_session = $conn->query("SELECT id, access_expiry, namespace FROM lab_sessions 
                                      WHERE user_id=$user_id AND lab_id=$lab_id 
                                      AND (status='ACTIVE' OR status='REQUESTED' OR status='PROVISIONING')
                                      AND access_expiry > NOW()")->fetch_assoc();
    
    if ($existing_session) {
        $_SESSION['warning'] = "User already has an active session for this lab. Lab expires on " . 
                               date('M d, Y H:i', strtotime($existing_session['access_expiry'])) . 
                               ". Use the Extend Lab option instead.";
        header("Location: admin_users.php");
        exit;
    }
    
    // Get user info
    $user_info = $conn->query("SELECT username, email FROM users WHERE id=$user_id")->fetch_assoc();
    $username = $user_info['username'] ?: $user_info['email'];
    
    // Get lab info with provisioning scripts
    $lab_info = $conn->query("SELECT * FROM labs WHERE id=$lab_id")->fetch_assoc();
    
    if (!$lab_info) {
        $_SESSION['error'] = "Lab not found!";
        header("Location: admin_users.php");
        exit;
    }
    
    // Calculate expiry
    $access_expiry = date('Y-m-d H:i:s', time() + ($validity_hours * 3600));
    $session_token = bin2hex(random_bytes(16));
    $namespace = 'user-' . $user_id . '-' . time();
    
    // Create lab session with REQUESTED status (provisioning will happen asynchronously)
    $insert_result = $conn->query("INSERT INTO lab_sessions 
                  (user_id, username, lab_id, namespace, access_start, access_expiry, status, session_token, provisioned)
                  VALUES ($user_id, '$username', $lab_id, '$namespace', NOW(), '$access_expiry', 'REQUESTED', '$session_token', 0)");
    
    if (!$insert_result) {
        $_SESSION['error'] = "Database error: " . $conn->error;
        header("Location: admin_users.php");
        exit;
    }
    
    $session_id = $conn->insert_id;
    
    // Log provisioning task (will be picked up by background provisioner.php cron)
    $log_entry = "[" . date('Y-m-d H:i:s') . "] Lab session $session_id created for user $username (lab_id=$lab_id)";
    error_log($log_entry, 3, "/tmp/lab_provisioning.log");
    
    $_SESSION['success'] = "Lab assigned successfully! Provisioning will start shortly. Status: REQUESTED";
    header("Location: admin_users.php");
    exit;
}

// Handle user role update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_user_role'])) {
    $user_id = (int)$_POST['user_id'];
    $new_role = $conn->real_escape_string($_POST['role']);
    
    $conn->query("UPDATE users SET role='$new_role' WHERE id=$user_id");
    $_SESSION['success'] = "User role updated successfully!";
    header("Location: admin_users.php");
    exit;
}

// Handle lab extension
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['extend_lab'])) {
    $session_id = (int)$_POST['session_id'];
    $extend_hours = (int)$_POST['extend_hours'];
    
    // Get current session
    $session = $conn->query("SELECT access_expiry FROM lab_sessions WHERE id=$session_id")->fetch_assoc();
    
    if (!$session) {
        $_SESSION['error'] = "Lab session not found!";
        header("Location: admin_users.php");
        exit;
    }
    
    // Calculate new expiry
    $current_expiry = strtotime($session['access_expiry']);
    $new_expiry = date('Y-m-d H:i:s', $current_expiry + ($extend_hours * 3600));
    
    // Update session expiry
    $update_result = $conn->query("UPDATE lab_sessions SET access_expiry='$new_expiry' WHERE id=$session_id");
    
    if ($update_result) {
        $_SESSION['success'] = "Lab extended successfully until " . date('M d, Y H:i', strtotime($new_expiry));
    } else {
        $_SESSION['error'] = "Failed to extend lab: " . $conn->error;
    }
    
    header("Location: admin_users.php");
    exit;
}

include 'includes/header.php';
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
          <h1 class="m-0"><i class="fas fa-users-cog"></i> User Management</h1>
        </div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
            <li class="breadcrumb-item active">User Management</li>
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
        <?= $_SESSION['success']; unset($_SESSION['success']); ?>
      </div>
      <?php endif; ?>

      <?php if (isset($_SESSION['warning'])): ?>
      <div class="alert alert-warning alert-dismissible">
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        <?= $_SESSION['warning']; unset($_SESSION['warning']); ?>
      </div>
      <?php endif; ?>

      <!-- Users List with Lab Mapping -->
      <div class="card">
        <div class="card-header">
          <h3 class="card-title"><i class="fas fa-users"></i> All Users</h3>
        </div>
        <div class="card-body table-responsive p-0">
          <table class="table table-hover">
            <thead>
              <tr>
                <th>ID</th>
                <th>User</th>
                <th>Role</th>
                <th>Current Lab</th>
                <th>Lab Expires</th>
                <th>Total Sessions</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php
              $users_q = $conn->query("SELECT * FROM users ORDER BY created_at DESC");
              while($user = $users_q->fetch_assoc()):
                // Get current active lab
                $lab_q = $conn->query("SELECT * FROM lab_sessions 
                                       WHERE user_id={$user['id']} AND status='ACTIVE' 
                                       ORDER BY created_at DESC LIMIT 1");
                $active_lab = $lab_q->num_rows > 0 ? $lab_q->fetch_assoc() : null;
                
                // Get total session count
                $total_q = $conn->query("SELECT COUNT(*) as cnt FROM lab_sessions WHERE user_id={$user['id']}");
                $total_sessions = $total_q->fetch_assoc()['cnt'];
                
                // Calculate time left
                $time_left = '';
                $time_class = 'muted';
                if ($active_lab) {
                    $remaining = strtotime($active_lab['access_expiry']) - time();
                    if ($remaining > 0) {
                        $hours = floor($remaining / 3600);
                        $mins = floor(($remaining % 3600) / 60);
                        $time_left = "{$hours}h {$mins}m left";
                        $time_class = $hours < 2 ? 'danger' : 'success';
                    } else {
                        $time_left = 'EXPIRED';
                        $time_class = 'danger';
                    }
                }
              ?>
              <tr>
                <td><?= $user['id'] ?></td>
                <td>
                  <strong><?= htmlspecialchars($user['username'] ?: 'N/A') ?></strong><br>
                  <?php if (!empty($user['email'])): ?>
                  <small class="text-muted"><?= htmlspecialchars($user['email']) ?></small>
                  <?php endif; ?>
                </td>
                <td>
                  <form method="POST" style="display:inline;">
                    <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                    <select name="role" class="form-control form-control-sm" onchange="if(confirm('Change user role?')) this.form.submit();">
                      <option value="user" <?= $user['role'] === 'user' ? 'selected' : '' ?>>User</option>
                      <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                    </select>
                    <button type="submit" name="update_user_role" style="display:none;"></button>
                  </form>
                </td>
                <td>
                  <?php if ($active_lab): ?>
                  <span class="badge badge-success">
                    Lab Active
                  </span><br>
                  <small>ID: <?= htmlspecialchars($active_lab['id']) ?></small>
                  <?php else: ?>
                  <span class="text-muted">No active lab</span>
                  <?php endif; ?>
                </td>
                <td>
                  <?php if ($active_lab): ?>
                  <span class="text-<?= $time_class ?>">
                    <i class="fas fa-clock"></i> <?= $time_left ?>
                  </span><br>
                  <small class="text-muted"><?= date('M j, g:i A', strtotime($active_lab['access_expiry'])) ?></small>
                  <?php else: ?>
                  -
                  <?php endif; ?>
                </td>
                <td><?= $total_sessions ?></td>
                <td>
                  <button class="btn btn-xs btn-primary assign-lab-btn" data-user-id="<?= $user['id'] ?>" data-user-name="<?= htmlspecialchars($user['username'] ?: ($user['email'] ?: 'User')) ?>">
                    <i class="fas fa-plus"></i> Assign Lab
                  </button>
                  <a href="admin_lab_requests.php?user=<?= $user['id'] ?>" class="btn btn-xs btn-info">
                    <i class="fas fa-history"></i> History
                  </a>
                </td>
              </tr>

              <?php endwhile; ?>
            </tbody>
          </table>
        </div>
      </div>

      <!-- SINGLE Assign Lab Modal - Reusable for all users -->
      <div class="modal fade" id="assignLabModal" tabindex="-1" role="dialog" aria-labelledby="assignLabLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
          <div class="modal-content">
            <div class="modal-header bg-primary">
              <h5 class="modal-title" id="assignLabLabel">
                <i class="fas fa-flask"></i> Assign Lab to <span id="modalUserName"></span>
              </h5>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <form method="POST" id="assignLabForm">
              <div class="modal-body">
                <input type="hidden" name="user_id" id="modalUserId" value="">
                
                <div class="form-group">
                  <label for="courseSelect">Select Course <span class="text-danger">*</span></label>
                  <select name="course_id" id="courseSelect" class="form-control" required onchange="showLabsForCourse()">
                    <option value="">-- Select Course --</option>
                    <?php 
                    $courses_result = $conn->query("SELECT id, name FROM courses WHERE active=1 ORDER BY name");
                    while($c = $courses_result->fetch_assoc()):
                    ?>
                    <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
                    <?php endwhile; ?>
                  </select>
                </div>

                <div class="form-group">
                  <label for="labSelect">Select Lab <span class="text-danger">*</span></label>
                  <select name="lab_id" id="labSelect" class="form-control" required>
                    <option value="">-- Select a lab --</option>
                    <?php
                    // Pre-render all labs
                    $labs_query = $conn->query("
                      SELECT l.id, l.lab_name, l.course_id, l.duration_minutes, s.hostname 
                      FROM labs l 
                      LEFT JOIN servers s ON l.server_id = s.id 
                      WHERE l.active=1 
                      ORDER BY l.course_id, l.lab_name");
                    
                    $labs_by_course = [];
                    while($lab = $labs_query->fetch_assoc()) {
                      if (!isset($labs_by_course[$lab['course_id']])) {
                        $labs_by_course[$lab['course_id']] = [];
                      }
                      $labs_by_course[$lab['course_id']][] = $lab;
                    }
                    
                    foreach($labs_by_course as $cid => $labs):
                      foreach($labs as $l):
                    ?>
                    <option value="<?= $l['id'] ?>" data-course="<?= $cid ?>" class="lab-option" style="display:none;">
                      <?= htmlspecialchars($l['lab_name']) ?> (<?= $l['duration_minutes'] ?> min<?= $l['hostname'] ? ' - ' . htmlspecialchars($l['hostname']) : '' ?>)
                    </option>
                    <?php 
                      endforeach;
                    endforeach;
                    ?>
                  </select>
                </div>

                <div class="form-group">
                  <label for="validityHours">Validity (Hours) <span class="text-danger">*</span></label>
                  <input type="number" name="validity_hours" id="validityHours" class="form-control" value="8" min="1" max="168" required>
                </div>

                <div class="form-group">
                  <label for="adminNotes">Admin Notes</label>
                  <textarea name="notes" id="adminNotes" class="form-control" rows="3" placeholder="Optional notes..."></textarea>
                </div>

                <div class="alert alert-info mb-0">
                  <i class="fas fa-info-circle"></i> Lab will be auto-provisioned on the server.
                </div>
              </div>

              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="submit" name="assign_lab" class="btn btn-primary">
                  <i class="fas fa-rocket"></i> Assign Lab
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>      <!-- User Statistics -->
      <div class="row">
        <div class="col-md-3">
          <div class="small-box bg-info">
            <div class="inner">
              <?php
              $total_users = $conn->query("SELECT COUNT(*) as cnt FROM users")->fetch_assoc()['cnt'];
              ?>
              <h3><?= $total_users ?></h3>
              <p>Total Users</p>
            </div>
            <div class="icon">
              <i class="fas fa-users"></i>
            </div>
          </div>
        </div>

        <div class="col-md-3">
          <div class="small-box bg-success">
            <div class="inner">
              <?php
              $active_labs = $conn->query("SELECT COUNT(*) as cnt FROM lab_sessions WHERE status='ACTIVE'")->fetch_assoc()['cnt'];
              ?>
              <h3><?= $active_labs ?></h3>
              <p>Active Labs</p>
            </div>
            <div class="icon">
              <i class="fas fa-play-circle"></i>
            </div>
          </div>
        </div>

        <div class="col-md-3">
          <div class="small-box bg-warning">
            <div class="inner">
              <?php
              $pending_ext = $conn->query("SELECT COUNT(*) as cnt FROM lab_extension_requests WHERE status='pending'")->fetch_assoc()['cnt'];
              ?>
              <h3><?= $pending_ext ?></h3>
              <p>Pending Extensions</p>
            </div>
            <div class="icon">
              <i class="fas fa-clock"></i>
            </div>
          </div>
        </div>

        <div class="col-md-3">
          <div class="small-box bg-danger">
            <div class="inner">
              <?php
              $expired_labs = $conn->query("SELECT COUNT(*) as cnt FROM lab_sessions WHERE status='ACTIVE' AND access_expiry < NOW()")->fetch_assoc()['cnt'];
              ?>
              <h3><?= $expired_labs ?></h3>
              <p>Expired Labs</p>
            </div>
            <div class="icon">
              <i class="fas fa-exclamation-triangle"></i>
            </div>
          </div>
        </div>
      </div>

    </div>
  </section>
</div>

<?php include 'includes/footer.php'; ?>

</div>

<script>
// Handle Assign Lab button clicks
document.querySelectorAll('.assign-lab-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const userId = this.getAttribute('data-user-id');
        const userName = this.getAttribute('data-user-name');
        
        // Set modal values
        document.getElementById('modalUserId').value = userId;
        document.getElementById('modalUserName').textContent = userName;
        document.getElementById('courseSelect').value = '';
        document.getElementById('labSelect').value = '';
        document.getElementById('validityHours').value = 8;
        document.getElementById('adminNotes').value = '';
        
        // Show modal
        $('#assignLabModal').modal('show');
    });
});

// Show/hide labs based on selected course
function showLabsForCourse() {
    const courseId = document.getElementById('courseSelect').value;
    const allOptions = document.querySelectorAll('.lab-option');
    
    allOptions.forEach(opt => {
        if (courseId && opt.getAttribute('data-course') === courseId) {
            opt.style.display = 'block';
        } else {
            opt.style.display = 'none';
        }
    });
    
    document.getElementById('labSelect').value = '';
}
</script>

</body>
</html>
