<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

$page_title = "Lab Requests Management";
$conn = new mysqli("mysql","monitor","monitor123","monitoring");
if ($conn->connect_error) die("DB Error");

// Handle approve/deny actions
if (isset($_GET['approve'])) {
    $id = (int)$_GET['approve'];
    $conn->query("UPDATE lab_requests SET status='approved', reviewed_at=NOW() WHERE id=$id");
    
    // Create lab session
    $req = $conn->query("SELECT * FROM lab_requests WHERE id=$id")->fetch_assoc();
    $duration_mins = $conn->query("SELECT duration_minutes FROM lab_templates WHERE id={$req['lab_template_id']}")->fetch_assoc()['duration_minutes'];
    $expires_at = date('Y-m-d H:i:s', strtotime("+{$duration_mins} minutes"));
    
    $conn->query("INSERT INTO lab_sessions (user_id, lab_template_id, pod_name, status, created_at, expires_at)
                  VALUES ({$req['user_id']}, {$req['lab_template_id']}, 'pending-provision', 'REQUESTED', NOW(), '$expires_at')");
    
    $_SESSION['success'] = "Lab request approved! Session created.";
    header("Location: admin_lab_requests.php");
    exit;
}

if (isset($_GET['deny'])) {
    $id = (int)$_GET['deny'];
    $conn->query("UPDATE lab_requests SET status='denied', reviewed_at=NOW() WHERE id=$id");
    $_SESSION['success'] = "Lab request denied.";
    header("Location: admin_lab_requests.php");
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
          <h1 class="m-0"><i class="fas fa-clipboard-check"></i> Lab Requests Management</h1>
        </div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
            <li class="breadcrumb-item active">Lab Requests</li>
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

      <!-- Filters -->
      <div class="card">
        <div class="card-body">
          <form method="GET" class="form-inline">
            <div class="form-group mr-3">
              <label class="mr-2">Filter by User:</label>
              <select name="user" class="form-control" onchange="this.form.submit()">
                <option value="">All Users</option>
                <?php
                $users_q = $conn->query("SELECT DISTINCT u.id, u.email, u.name 
                                         FROM users u 
                                         JOIN lab_requests lr ON u.id = lr.user_id 
                                         ORDER BY u.email");
                while($u = $users_q->fetch_assoc()):
                  $selected = (isset($_GET['user']) && $_GET['user'] == $u['id']) ? 'selected' : '';
                ?>
                <option value="<?= $u['id'] ?>" <?= $selected ?>>
                  <?= htmlspecialchars($u['name'] ?? $u['email']) ?>
                </option>
                <?php endwhile; ?>
              </select>
            </div>

            <div class="form-group mr-3">
              <label class="mr-2">Status:</label>
              <select name="status" class="form-control" onchange="this.form.submit()">
                <option value="">All Status</option>
                <option value="pending" <?= (isset($_GET['status']) && $_GET['status'] === 'pending') ? 'selected' : '' ?>>Pending</option>
                <option value="approved" <?= (isset($_GET['status']) && $_GET['status'] === 'approved') ? 'selected' : '' ?>>Approved</option>
                <option value="denied" <?= (isset($_GET['status']) && $_GET['status'] === 'denied') ? 'selected' : '' ?>>Denied</option>
              </select>
            </div>

            <?php if (isset($_GET['user']) || isset($_GET['status'])): ?>
            <a href="admin_lab_requests.php" class="btn btn-default">
              <i class="fas fa-times"></i> Clear Filters
            </a>
            <?php endif; ?>
          </form>
        </div>
      </div>

      <!-- Stats Cards -->
      <div class="row">
        <div class="col-lg-3 col-6">
          <div class="small-box bg-warning">
            <div class="inner">
              <?php
              $pending = $conn->query("SELECT COUNT(*) as cnt FROM lab_requests WHERE status='pending'")->fetch_assoc()['cnt'];
              ?>
              <h3><?= $pending ?></h3>
              <p>Pending</p>
            </div>
            <div class="icon">
              <i class="fas fa-clock"></i>
            </div>
          </div>
        </div>
        <div class="col-lg-3 col-6">
          <div class="small-box bg-success">
            <div class="inner">
              <?php
              $approved = $conn->query("SELECT COUNT(*) as cnt FROM lab_requests WHERE status='approved'")->fetch_assoc()['cnt'];
              ?>
              <h3><?= $approved ?></h3>
              <p>Approved</p>
            </div>
            <div class="icon">
              <i class="fas fa-check"></i>
            </div>
          </div>
        </div>
        <div class="col-lg-3 col-6">
          <div class="small-box bg-danger">
            <div class="inner">
              <?php
              $denied = $conn->query("SELECT COUNT(*) as cnt FROM lab_requests WHERE status='denied'")->fetch_assoc()['cnt'];
              ?>
              <h3><?= $denied ?></h3>
              <p>Denied</p>
            </div>
            <div class="icon">
              <i class="fas fa-times"></i>
            </div>
          </div>
        </div>
        <div class="col-lg-3 col-6">
          <div class="small-box bg-info">
            <div class="inner">
              <?php
              $total = $conn->query("SELECT COUNT(*) as cnt FROM lab_requests")->fetch_assoc()['cnt'];
              ?>
              <h3><?= $total ?></h3>
              <p>Total Requests</p>
            </div>
            <div class="icon">
              <i class="fas fa-list"></i>
            </div>
          </div>
        </div>
      </div>

      <!-- Pending Requests -->
      <div class="card card-warning">
        <div class="card-header">
          <h3 class="card-title"><i class="fas fa-clock"></i> Pending Requests</h3>
        </div>
        <div class="card-body table-responsive p-0">
          <table class="table table-hover">
            <thead>
              <tr>
                <th>ID</th>
                <th>User</th>
                <th>Lab</th>
                <th>Course</th>
                <th>Justification</th>
                <th>Requested</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php
              $pending_q = $conn->query("
                SELECT lr.*, u.email, u.name, lt.title as lab_title, c.title as course_title
                FROM lab_requests lr
                JOIN users u ON lr.user_id = u.id
                JOIN lab_templates lt ON lr.lab_template_id = lt.id
                JOIN courses c ON lt.course_id = c.id
                WHERE lr.status = 'pending'
                ORDER BY lr.created_at ASC
              ");
              while($r = $pending_q->fetch_assoc()):
              ?>
              <tr>
                <td><?= $r['id'] ?></td>
                <td>
                  <strong><?= htmlspecialchars($r['name'] ?? $r['email']) ?></strong><br>
                  <small class="text-muted"><?= htmlspecialchars($r['email']) ?></small>
                </td>
                <td><?= htmlspecialchars($r['lab_title']) ?></td>
                <td><?= htmlspecialchars($r['course_title']) ?></td>
                <td><?= htmlspecialchars($r['justification'] ?? 'N/A') ?></td>
                <td><?= date('M j, Y g:i A', strtotime($r['created_at'])) ?></td>
                <td>
                  <a href="?approve=<?= $r['id'] ?>" class="btn btn-sm btn-success" 
                     onclick="return confirm('Approve this request and create lab session?')">
                    <i class="fas fa-check"></i> Approve
                  </a>
                  <a href="?deny=<?= $r['id'] ?>" class="btn btn-sm btn-danger"
                     onclick="return confirm('Deny this request?')">
                    <i class="fas fa-times"></i> Deny
                  </a>
                </td>
              </tr>
              <?php endwhile; ?>
            </tbody>
          </table>
        </div>
      </div>

      <!-- All Requests History -->
      <div class="card">
        <div class="card-header">
          <h3 class="card-title"><i class="fas fa-history"></i> Request History</h3>
        </div>
        <div class="card-body table-responsive p-0" style="max-height: 400px;">
          <table class="table table-hover table-striped">
            <thead>
              <tr>
                <th>ID</th>
                <th>User</th>
                <th>Lab</th>
                <th>Status</th>
                <th>Requested</th>
                <th>Reviewed</th>
              </tr>
            </thead>
            <tbody>
              <?php
              // Build query with filters
              $where_clauses = [];
              if (isset($_GET['user']) && !empty($_GET['user'])) {
                $filter_user = (int)$_GET['user'];
                $where_clauses[] = "lr.user_id = $filter_user";
              }
              if (isset($_GET['status']) && !empty($_GET['status'])) {
                $filter_status = $conn->real_escape_string($_GET['status']);
                $where_clauses[] = "lr.status = '$filter_status'";
              }
              
              $where_sql = !empty($where_clauses) ? 'WHERE ' . implode(' AND ', $where_clauses) : '';
              
              $all_q
                  <strong><?= htmlspecialchars($r['name'] ?? $r['email']) ?></strong><br>
                  <small class="text-muted"><?= htmlspecialchars($r['email']) ?></small>
                
                SELECT lr.*, u.email, u.name, lt.title as lab_title
                FROM lab_requests lr
                JOIN users u ON lr.user_id = u.id
                JOIN lab_templates lt ON lr.lab_template_id = lt.id
                $where_sql
                ORDER BY lr.created_at DESC
                LIMIT 100
              ");
              while($r = $all_q->fetch_assoc()):
                $badge = $r['status'] === 'approved' ? 'success' : ($r['status'] === 'denied' ? 'danger' : 'warning');
              ?>
              <tr>
                <td><?= $r['id'] ?></td>
                <td><?= htmlspecialchars($r['email']) ?></td>
                <td><?= htmlspecialchars($r['lab_title']) ?></td>
                <td><span class="badge badge-<?= $badge ?>"><?= ucfirst($r['status']) ?></span></td>
                <td><?= date('M j, g:i A', strtotime($r['created_at'])) ?></td>
                <td><?= $r['reviewed_at'] ? date('M j, g:i A', strtotime($r['reviewed_at'])) : '-' ?></td>
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
