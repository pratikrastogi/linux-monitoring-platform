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
    
    // Get user info
    $user_info = $conn->query("SELECT email FROM users WHERE id=$user_id")->fetch_assoc();
    $username = $user_info['email'];
    
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
    
    // Create lab session
    $conn->query("INSERT INTO lab_sessions 
                  (user_id, lab_id, server_id, access_expiry, status, created_at)
                  VALUES ($user_id, $lab_id, ".$lab_info['server_id'].", '$access_expiry', 'ACTIVE', NOW())");
    
    $session_id = $conn->insert_id;
    
    // Get server details from centralized servers table
    $server_result = $conn->query("SELECT * FROM servers WHERE id = ".$lab_info['server_id']);
    $server_info = $server_result->fetch_assoc();
    
    $bastion_host = $server_info['ip_address'];
    $bastion_user = $server_info['ssh_user'];
    $bastion_password = $server_info['ssh_password'];
    $provision_script = $lab_info['provision_script_path'];
    $user_email = $user_info['email'];
    $duration_hours = $validity_hours;
    
    // Trigger auto-provisioning script via sshpass
    $provision_command = "sshpass -p '$bastion_password' ssh -o StrictHostKeyChecking=no $bastion_user@$bastion_host " .
                       "'bash $provision_script $user_email $duration_hours $lab_id'";
    
    exec($provision_command . " > /dev/null 2>&1 &");
    
    $_SESSION['success'] = "Lab assigned successfully! Provisioning initiated on $bastion_host";
    
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
                  <strong><?= htmlspecialchars($user['username'] ?? '') ?></strong><br>
                  <small class="text-muted"><?= htmlspecialchars($user['email'] ?? '') ?></small>
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
                  <button class="btn btn-xs btn-primary" data-toggle="modal" data-target="#assignLab<?= $user['id'] ?>">
                    <i class="fas fa-plus"></i> Assign Lab
                  </button>
                  <a href="admin_lab_requests.php?user=<?= $user['id'] ?>" class="btn btn-xs btn-info">
                    <i class="fas fa-history"></i> History
                  </a>
                </td>
              </tr>

              <!-- Assign Lab Modal -->
              <div class="modal fade" id="assignLab<?= $user['id'] ?>">
                <div class="modal-dialog modal-lg">
                  <div class="modal-content">
                    <div class="modal-header bg-primary">
                      <h4 class="modal-title">
                        <i class="fas fa-flask"></i> Assign Course/Lab to <?= htmlspecialchars($user['name'] ?? $user['email']) ?>
                      </h4>
                      <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <form method="POST">
                      <div class="modal-body">
                        <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                        
                        <div class="form-group">
                          <label>Select Course <span class="text-danger">*</span></label>
                          <select name="course_id" class="form-control course-select" required 
                                  onchange="loadLabsForCourse(this.value, <?= $user['id'] ?>)">
                            <option value="">-- Select Course --</option>
                            <?php 
                            $courses = $conn->query("SELECT id, name FROM courses WHERE active=1 ORDER BY name");
                            while($course = $courses->fetch_assoc()):
                            ?>
                            <option value="<?= $course['id'] ?>"><?= htmlspecialchars($course['name']) ?></option>
                            <?php endwhile; ?>
                          </select>
                        </div>

                        <div class="form-group">
                          <label>Select Lab <span class="text-danger">*</span></label>
                          <select name="lab_id" class="form-control lab-select" required>
                            <option value="">-- Select a course first --</option>
                          </select>
                          <small class="form-text text-muted">Available labs for the selected course will appear here</small>
                        </div>

                        <div class="form-group">
                          <label>Validity (Hours) <span class="text-danger">*</span></label>
                          <input type="number" name="validity_hours" class="form-control" 
                                 value="8" min="1" max="168" required>
                          <small class="text-muted">How long the lab will be accessible (1-168 hours)</small>
                        </div>

                        <div class="form-group">
                          <label>Admin Notes</label>
                          <textarea name="notes" class="form-control" rows="3" 
                                    placeholder="Optional notes about this assignment..."></textarea>
                        </div>

                        <div class="alert alert-info mb-0">
                          <i class="fas fa-info-circle"></i> 
                          <strong>Auto-Provisioning Enabled</strong><br>
                          Lab will be automatically provisioned on the configured server.
                        </div>
                      </div>

                      <div class="modal-footer">
                        <button type="submit" name="assign_lab" class="btn btn-primary">
                          <i class="fas fa-rocket"></i> Assign & Provision Lab
                        </button>
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                      </div>
                    </form>
                  </div>
                </div>
              </div>

              <?php endwhile; ?>
            </tbody>
          </table>
        </div>
      </div>

      <!-- User Statistics -->
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
// Dynamic lab loading based on course selection
function loadLabsForCourse(courseId, userId) {
    const labSelect = document.querySelector('#assignLab' + userId + ' select[name="lab_id"]');
    
    if (!labSelect) {
        console.error('Lab select not found for user', userId);
        return;
    }
    
    if (!courseId) {
        labSelect.innerHTML = '<option value="">-- Select a course first --</option>';
        labSelect.disabled = true;
        return;
    }
    
    labSelect.disabled = true;
    labSelect.innerHTML = '<option value="">Loading labs...</option>';
    
    // Use absolute path for API call
    const apiUrl = window.location.origin + '/api/get_course_labs.php?course_id=' + courseId;
    
    console.log('Fetching from:', apiUrl);
    
    fetch(apiUrl)
        .then(response => {
            console.log('Response status:', response.status);
            if (!response.ok) {
                throw new Error('HTTP ' + response.status);
            }
            return response.json();
        })
        .then(data => {
            console.log('Data received:', data);
            if (data.success && data.labs && data.labs.length > 0) {
                let html = '<option value="">-- Select Lab --</option>';
                data.labs.forEach(lab => {
                    html += '<option value="' + lab.id + '">' + 
                            lab.lab_name + ' (' + lab.duration_minutes + ' min)' +
                            (lab.hostname ? ' - ' + lab.hostname : '') + '</option>';
                });
                labSelect.innerHTML = html;
                labSelect.disabled = false;
            } else {
                labSelect.innerHTML = '<option value="">No labs available for this course</option>';
                labSelect.disabled = true;
            }
        })
        .catch(error => {
            console.error('Error loading labs:', error);
            labSelect.innerHTML = '<option value="">Error loading labs. Check console.</option>';
            labSelect.disabled = true;
        });
}
</script>

</body>
</html>
