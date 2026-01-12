<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

$page_title = "Provisioners Management";
$conn = new mysqli("mysql","monitor","monitor123","monitoring");
if ($conn->connect_error) die("DB Error");

// Handle CRUD operations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['create_provisioner'])) {
        $name = $conn->real_escape_string($_POST['name']);
        $ip_address = $conn->real_escape_string($_POST['ip_address']);
        $ssh_user = $conn->real_escape_string($_POST['ssh_user']);
        $ssh_password = $conn->real_escape_string($_POST['ssh_password']);
        $ssh_key = $conn->real_escape_string($_POST['ssh_key']);
        $max_pods = (int)$_POST['max_pods'];
        $cpu_total = $conn->real_escape_string($_POST['cpu_total']);
        $memory_total = $conn->real_escape_string($_POST['memory_total']);
        
        $conn->query("INSERT INTO provisioners 
                      (name, ip_address, ssh_user, ssh_password, ssh_key, max_pods, cpu_total, memory_total, 
                       current_pods, status, last_health_check, created_at, updated_at) 
                      VALUES ('$name', '$ip_address', '$ssh_user', '$ssh_password', '$ssh_key', $max_pods, 
                              '$cpu_total', '$memory_total', 0, 'active', NOW(), NOW(), NOW())");
        $_SESSION['success'] = "Provisioner added successfully!";
        header("Location: provisioners.php");
        exit;
    }
    
    if (isset($_POST['update_provisioner'])) {
        $id = (int)$_POST['id'];
        $name = $conn->real_escape_string($_POST['name']);
        $ip_address = $conn->real_escape_string($_POST['ip_address']);
        $ssh_user = $conn->real_escape_string($_POST['ssh_user']);
        $ssh_password = $conn->real_escape_string($_POST['ssh_password']);
        $ssh_key = $conn->real_escape_string($_POST['ssh_key']);
        $max_pods = (int)$_POST['max_pods'];
        $cpu_total = $conn->real_escape_string($_POST['cpu_total']);
        $memory_total = $conn->real_escape_string($_POST['memory_total']);
        $status = $conn->real_escape_string($_POST['status']);
        
        $conn->query("UPDATE provisioners SET 
                      name='$name', ip_address='$ip_address', ssh_user='$ssh_user', 
                      ssh_password='$ssh_password', ssh_key='$ssh_key', max_pods=$max_pods,
                      cpu_total='$cpu_total', memory_total='$memory_total', status='$status', updated_at=NOW()
                      WHERE id=$id");
        $_SESSION['success'] = "Provisioner updated successfully!";
        header("Location: provisioners.php");
        exit;
    }
    
    if (isset($_POST['delete_provisioner'])) {
        $id = (int)$_POST['id'];
        $conn->query("UPDATE provisioners SET status='disabled', updated_at=NOW() WHERE id=$id");
        $_SESSION['success'] = "Provisioner disabled successfully!";
        header("Location: provisioners.php");
        exit;
    }
}

// Get action
$action = $_GET['action'] ?? 'list';
$edit_id = $_GET['id'] ?? null;

// Fetch provisioner for editing
$edit_prov = null;
if ($action === 'edit' && $edit_id) {
    $result = $conn->query("SELECT * FROM provisioners WHERE id=$edit_id");
    $edit_prov = $result->fetch_assoc();
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
          <h1 class="m-0"><i class="fas fa-server"></i> Provisioners Management</h1>
        </div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
            <li class="breadcrumb-item active">Provisioners</li>
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

      <?php if ($action === 'add' || $action === 'edit'): ?>
      <!-- Add/Edit Form -->
      <div class="card card-warning">
        <div class="card-header">
          <h3 class="card-title">
            <i class="fas fa-<?= $action === 'add' ? 'plus' : 'edit' ?>"></i>
            <?= $action === 'add' ? 'Add New Provisioner' : 'Edit Provisioner' ?>
          </h3>
        </div>
        <form method="POST">
          <div class="card-body">
            <?php if ($action === 'edit'): ?>
            <input type="hidden" name="id" value="<?= $edit_prov['id'] ?>">
            <?php endif; ?>

            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label>Provisioner Name <span class="text-danger">*</span></label>
                  <input type="text" name="name" class="form-control" 
                         value="<?= htmlspecialchars($edit_prov['name'] ?? '') ?>" 
                         placeholder="Worker Node 1" required>
                </div>
              </div>

              <div class="col-md-6">
                <div class="form-group">
                  <label>IP Address <span class="text-danger">*</span></label>
                  <input type="text" name="ip_address" class="form-control" 
                         value="<?= htmlspecialchars($edit_prov['ip_address'] ?? '') ?>" 
                         placeholder="192.168.1.100" required>
                </div>
              </div>
            </div>

            <div class="card card-secondary">
              <div class="card-header">
                <h3 class="card-title">SSH Credentials</h3>
              </div>
              <div class="card-body">
                <div class="form-group">
                  <label>SSH Username <span class="text-danger">*</span></label>
                  <input type="text" name="ssh_user" class="form-control" 
                         value="<?= htmlspecialchars($edit_prov['ssh_user'] ?? 'root') ?>" required>
                </div>

                <div class="form-group">
                  <label>SSH Password</label>
                  <input type="password" name="ssh_password" class="form-control" 
                         value="<?= htmlspecialchars($edit_prov['ssh_password'] ?? '') ?>" 
                         placeholder="Leave blank to use SSH key">
                </div>

                <div class="form-group">
                  <label>SSH Private Key</label>
                  <textarea name="ssh_key" class="form-control" rows="6" 
                            placeholder="-----BEGIN RSA PRIVATE KEY-----&#10;...&#10;-----END RSA PRIVATE KEY-----"><?= htmlspecialchars($edit_prov['ssh_key'] ?? '') ?></textarea>
                  <small class="text-muted">Provide either password or private key</small>
                </div>
              </div>
            </div>

            <div class="row mt-3">
              <div class="col-md-4">
                <div class="form-group">
                  <label>Max Pods <span class="text-danger">*</span></label>
                  <input type="number" name="max_pods" class="form-control" 
                         value="<?= $edit_prov['max_pods'] ?? 10 ?>" min="1" max="100" required>
                </div>
              </div>

              <div class="col-md-4">
                <div class="form-group">
                  <label>Total CPU</label>
                  <input type="text" name="cpu_total" class="form-control" 
                         value="<?= htmlspecialchars($edit_prov['cpu_total'] ?? '4') ?>" 
                         placeholder="4">
                  <small class="text-muted">Total CPU cores available</small>
                </div>
              </div>

              <div class="col-md-4">
                <div class="form-group">
                  <label>Total Memory</label>
                  <input type="text" name="memory_total" class="form-control" 
                         value="<?= htmlspecialchars($edit_prov['memory_total'] ?? '8Gi') ?>" 
                         placeholder="8Gi">
                  <small class="text-muted">Total memory available</small>
                </div>
              </div>
            </div>

            <?php if ($action === 'edit'): ?>
            <div class="form-group">
              <label>Status</label>
              <select name="status" class="form-control">
                <option value="active" <?= ($edit_prov['status'] ?? '') === 'active' ? 'selected' : '' ?>>Active</option>
                <option value="maintenance" <?= ($edit_prov['status'] ?? '') === 'maintenance' ? 'selected' : '' ?>>Maintenance</option>
                <option value="disabled" <?= ($edit_prov['status'] ?? '') === 'disabled' ? 'selected' : '' ?>>Disabled</option>
              </select>
            </div>
            <?php endif; ?>
          </div>

          <div class="card-footer">
            <button type="submit" name="<?= $action === 'add' ? 'create_provisioner' : 'update_provisioner' ?>" 
                    class="btn btn-warning">
              <i class="fas fa-save"></i> <?= $action === 'add' ? 'Add' : 'Update' ?> Provisioner
            </button>
            <a href="provisioners.php" class="btn btn-default">
              <i class="fas fa-times"></i> Cancel
            </a>
          </div>
        </form>
      </div>

      <?php else: ?>
      <!-- List View -->
      <div class="card">
        <div class="card-header">
          <h3 class="card-title"><i class="fas fa-list"></i> All Provisioners</h3>
          <div class="card-tools">
            <a href="provisioners.php?action=add" class="btn btn-warning btn-sm">
              <i class="fas fa-plus"></i> Add Provisioner
            </a>
          </div>
        </div>
        <div class="card-body table-responsive p-0">
          <table class="table table-hover">
            <thead>
              <tr>
                <th>ID</th>
                <th>Name</th>
                <th>IP Address</th>
                <th>Capacity</th>
                <th>Usage</th>
                <th>Resources</th>
                <th>Health</th>
                <th>Status</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php
              $result = $conn->query("SELECT * FROM provisioners ORDER BY created_at DESC");
              while($prov = $result->fetch_assoc()):
                $usage_pct = $prov['max_pods'] > 0 ? round(($prov['current_pods'] / $prov['max_pods']) * 100) : 0;
                $usage_class = $usage_pct < 70 ? 'success' : ($usage_pct < 90 ? 'warning' : 'danger');
              ?>
              <tr>
                <td><?= $prov['id'] ?></td>
                <td>
                  <strong><?= htmlspecialchars($prov['name']) ?></strong><br>
                  <small class="text-muted"><?= htmlspecialchars($prov['ssh_user']) ?>@<?= htmlspecialchars($prov['ip_address']) ?></small>
                </td>
                <td><?= htmlspecialchars($prov['ip_address']) ?></td>
                <td>
                  Max: <?= $prov['max_pods'] ?> pods
                </td>
                <td>
                  <div class="progress" style="height: 20px;">
                    <div class="progress-bar bg-<?= $usage_class ?>" style="width: <?= $usage_pct ?>%">
                      <?= $prov['current_pods'] ?>/<?= $prov['max_pods'] ?> (<?= $usage_pct ?>%)
                    </div>
                  </div>
                </td>
                <td>
                  CPU: <?= htmlspecialchars($prov['cpu_total']) ?><br>
                  RAM: <?= htmlspecialchars($prov['memory_total']) ?>
                </td>
                <td>
                  <?php
                  $health_time = strtotime($prov['last_health_check']);
                  $health_age = time() - $health_time;
                  $health_status = $health_age < 300 ? 'success' : ($health_age < 600 ? 'warning' : 'danger');
                  ?>
                  <span class="badge badge-<?= $health_status ?>">
                    <?= date('g:i A', $health_time) ?>
                  </span>
                </td>
                <td>
                  <?php
                  $status_class = $prov['status'] === 'active' ? 'success' : ($prov['status'] === 'maintenance' ? 'warning' : 'secondary');
                  ?>
                  <span class="badge badge-<?= $status_class ?>"><?= ucfirst($prov['status']) ?></span>
                </td>
                <td>
                  <a href="provisioners.php?action=edit&id=<?= $prov['id'] ?>" class="btn btn-xs btn-info">
                    <i class="fas fa-edit"></i>
                  </a>
                  <form method="POST" style="display:inline;" onsubmit="return confirm('Disable this provisioner?');">
                    <input type="hidden" name="id" value="<?= $prov['id'] ?>">
                    <button type="submit" name="delete_provisioner" class="btn btn-xs btn-danger">
                      <i class="fas fa-ban"></i>
                    </button>
                  </form>
                </td>
              </tr>
              <?php endwhile; ?>
            </tbody>
          </table>
        </div>
      </div>
      <?php endif; ?>

    </div>
  </section>
</div>

<?php include 'includes/footer.php'; ?>

</div>
</body>
</html>
