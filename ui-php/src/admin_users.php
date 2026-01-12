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
    $lab_type = $conn->real_escape_string($_POST['lab_type']);
    $validity_hours = (int)$_POST['validity_hours'];
    $notes = $conn->real_escape_string($_POST['notes']);
    
    // Calculate expiry
    $expires_at = date('Y-m-d H:i:s', strtotime("+{$validity_hours} hours"));
    
    // Create lab session
    $conn->query("INSERT INTO lab_sessions 
                  (user_id, lab_type, pod_name, status, created_at, expires_at, admin_notes)
                  VALUES ($user_id, '$lab_type', 'pending-provision', 'REQUESTED', NOW(), '$expires_at', '$notes')");
    
    $session_id = $conn->insert_id;
    
    // Get provision target
    $provision_q = $conn->query("SELECT * FROM provision_target LIMIT 1");
    if ($provision_q && $provision_q->num_rows > 0) {
        $target = $provision_q->fetch_assoc();
        $target_ip = $target['target_ip'];
        $target_user = $target['target_user'];
        $target_password = $target['target_password'];
        
        // Get user details
        $user_q = $conn->query("SELECT email FROM users WHERE id=$user_id");
        $user = $user_q->fetch_assoc();
        $user_email = $user['email'];
        
        // Trigger auto-provisioning script
        $provision_command = "sshpass -p '$target_password' ssh -o StrictHostKeyChecking=no $target_user@$target_ip " .
                           "'bash /opt/lab/create_lab_user.sh $user_email $validity_hours $lab_type'";
        
        exec($provision_command . " > /dev/null 2>&1 &");
        
        // Update session status
        $conn->query("UPDATE lab_sessions SET status='ACTIVE' WHERE id=$session_id");
        
        $_SESSION['success'] = "Lab assigned successfully! Auto-provisioning initiated on $target_ip";
    } else {
        $_SESSION['warning'] = "Lab assigned but no provision target configured. Please configure provision target.";
    }
    
    header("Location: admin_users.php");
    exit;
}

// Handle provision target update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_provision_target'])) {
    $target_ip = $conn->real_escape_string($_POST['target_ip']);
    $target_user = $conn->real_escape_string($_POST['target_user']);
    $target_password = $conn->real_escape_string($_POST['target_password']);
    
    // Check if provision_target exists
    $check = $conn->query("SELECT id FROM provision_target LIMIT 1");
    if ($check && $check->num_rows > 0) {
        $conn->query("UPDATE provision_target SET 
                      target_ip='$target_ip', 
                      target_user='$target_user', 
                      target_password='$target_password',
                      updated_at=NOW()
                      WHERE id=1");
    } else {
        $conn->query("INSERT INTO provision_target 
                      (target_ip, target_user, target_password, created_at, updated_at)
                      VALUES ('$target_ip', '$target_user', '$target_password', NOW(), NOW())");
    }
    
    $_SESSION['success'] = "Provision target updated successfully!";
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

// Get provision target
$provision_target = null;
$provision_q = $conn->query("SELECT * FROM provision_target LIMIT 1");
if ($provision_q && $provision_q->num_rows > 0) {
    $provision_target = $provision_q->fetch_assoc();
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

      <!-- Provision Target Configuration -->
      <div class="card card-warning collapsed-card">
        <div class="card-header">
          <h3 class="card-title"><i class="fas fa-server"></i> Provision Target Configuration</h3>
          <div class="card-tools">
            <button type="button" class="btn btn-tool" data-card-widget="collapse">
              <i class="fas fa-plus"></i>
            </button>
          </div>
        </div>
        <div class="card-body">
          <form method="POST">
            <div class="row">
              <div class="col-md-4">
                <div class="form-group">
                  <label>Target Server IP <span class="text-danger">*</span></label>
                  <input type="text" name="target_ip" class="form-control" 
                         value="<?= htmlspecialchars($provision_target['target_ip'] ?? '') ?>" 
                         placeholder="192.168.1.46" required>
                  <small class="text-muted">Server where labs will be provisioned</small>
                </div>
              </div>

              <div class="col-md-4">
                <div class="form-group">
                  <label>SSH Username <span class="text-danger">*</span></label>
                  <input type="text" name="target_user" class="form-control" 
                         value="<?= htmlspecialchars($provision_target['target_user'] ?? 'root') ?>" required>
                </div>
              </div>

              <div class="col-md-4">
                <div class="form-group">
                  <label>SSH Password <span class="text-danger">*</span></label>
                  <input type="password" name="target_password" class="form-control" 
                         value="<?= htmlspecialchars($provision_target['target_password'] ?? '') ?>" 
                         placeholder="Enter password" required>
                </div>
              </div>
            </div>

            <button type="submit" name="update_provision_target" class="btn btn-warning">
              <i class="fas fa-save"></i> Update Provision Target
            </button>

            <?php if ($provision_target): ?>
            <span class="badge badge-success ml-2">
              <i class="fas fa-check"></i> Configured: <?= htmlspecialchars($provision_target['target_ip']) ?>
            </span>
            <?php else: ?>
            <span class="badge badge-danger ml-2">
              <i class="fas fa-exclamation-triangle"></i> Not Configured
            </span>
            <?php endif; ?>
          </form>
        </div>
      </div>

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
                    $remaining = strtotime($active_lab['expires_at']) - time();
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
                  <strong><?= htmlspecialchars($user['name'] ?? $user['email']) ?></strong><br>
                  <small class="text-muted"><?= htmlspecialchars($user['email']) ?></small>
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
                    <?= htmlspecialchars($active_lab['lab_type'] ?? 'Active') ?>
                  </span><br>
                  <small>Pod: <?= htmlspecialchars($active_lab['pod_name']) ?></small>
                  <?php else: ?>
                  <span class="text-muted">No active lab</span>
                  <?php endif; ?>
                </td>
                <td>
                  <?php if ($active_lab): ?>
                  <span class="text-<?= $time_class ?>">
                    <i class="fas fa-clock"></i> <?= $time_left ?>
                  </span><br>
                  <small class="text-muted"><?= date('M j, g:i A', strtotime($active_lab['expires_at'])) ?></small>
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
                <div class="modal-dialog">
                  <div class="modal-content">
                    <div class="modal-header bg-primary">
                      <h4 class="modal-title">
                        <i class="fas fa-flask"></i> Assign Lab to <?= htmlspecialchars($user['name'] ?? $user['email']) ?>
                      </h4>
                      <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <form method="POST">
                      <div class="modal-body">
                        <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                        
                        <div class="form-group">
                          <label>Lab Type <span class="text-danger">*</span></label>
                          <select name="lab_type" class="form-control" required>
                            <option value="">-- Select Lab Type --</option>
                            <option value="kubernetes-basics">Kubernetes Basics</option>
                            <option value="docker-fundamentals">Docker Fundamentals</option>
                            <option value="k8s-advanced">Kubernetes Advanced</option>
                            <option value="helm-charts">Helm Charts</option>
                            <option value="cicd-pipeline">CI/CD Pipeline</option>
                            <option value="monitoring-prometheus">Monitoring with Prometheus</option>
                            <option value="custom">Custom Lab</option>
                          </select>
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

                        <?php if ($provision_target): ?>
                        <div class="alert alert-info mb-0">
                          <i class="fas fa-info-circle"></i> 
                          <strong>Auto-Provisioning Enabled</strong><br>
                          Lab will be automatically provisioned on: <code><?= htmlspecialchars($provision_target['target_ip']) ?></code>
                        </div>
                        <?php else: ?>
                        <div class="alert alert-warning mb-0">
                          <i class="fas fa-exclamation-triangle"></i> 
                          <strong>No Provision Target Configured</strong><br>
                          Please configure provision target above for auto-provisioning.
                        </div>
                        <?php endif; ?>
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
              $expired_labs = $conn->query("SELECT COUNT(*) as cnt FROM lab_sessions WHERE status='ACTIVE' AND expires_at < NOW()")->fetch_assoc()['cnt'];
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
</body>
</html>
