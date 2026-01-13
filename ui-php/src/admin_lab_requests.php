<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

$page_title = "Lab Requests & Assignments";
$conn = new mysqli("mysql","monitor","monitor123","monitoring");
if ($conn->connect_error) die("DB Error");

// Handle approve/deny actions
if (isset($_GET['approve'])) {
    $id = (int)$_GET['approve'];
    $conn->query("UPDATE lab_requests SET status='approved', reviewed_by={$_SESSION['uid']}, reviewed_at=NOW() WHERE id=$id");
    
    // Create lab session
    $req = $conn->query("SELECT * FROM lab_requests WHERE id=$id")->fetch_assoc();
    $lab = $conn->query("SELECT duration_minutes FROM labs WHERE id={$req['lab_id']}")->fetch_assoc();
    $duration_mins = $lab['duration_minutes'] ?? 60;
    $access_expiry = date('Y-m-d H:i:s', time() + ($duration_mins * 60));
    $session_token = bin2hex(random_bytes(16));
    
    $conn->query("INSERT INTO lab_sessions (user_id, username, lab_id, namespace, access_start, access_expiry, status, session_token, plan, provisioned, created_at, updated_at)
                  VALUES ({$req['user_id']}, '{$_SESSION['user']}', {$req['lab_id']}, 'pending', NOW(), '$access_expiry', 'ACTIVE', '$session_token', 'standard', 0, NOW(), NOW())");
    
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

<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">

<?php include 'includes/navbar.php'; ?>
<?php include 'includes/sidebar.php'; ?>

<div class="content-wrapper">
  <div class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6">
          <h1 class="m-0"><i class="fas fa-clipboard-check"></i> Lab Requests & Assignments</h1>
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
        <i class="fas fa-check"></i> <?= $_SESSION['success']; unset($_SESSION['success']); ?>
      </div>
      <?php endif; ?>

      <!-- Stats Cards -->
      <div class="row mb-4">
        <div class="col-lg-3 col-6">
          <div class="info-box bg-warning">
            <span class="info-box-icon"><i class="fas fa-hourglass-half"></i></span>
            <div class="info-box-content">
              <span class="info-box-text">Pending Requests</span>
              <?php
              $pending_count = $conn->query("SELECT COUNT(*) as cnt FROM lab_requests WHERE status='pending'")->fetch_assoc()['cnt'];
              ?>
              <span class="info-box-number"><?= $pending_count ?></span>
            </div>
          </div>
        </div>

        <div class="col-lg-3 col-6">
          <div class="info-box bg-success">
            <span class="info-box-icon"><i class="fas fa-check-circle"></i></span>
            <div class="info-box-content">
              <span class="info-box-text">Approved Requests</span>
              <?php
              $approved_count = $conn->query("SELECT COUNT(*) as cnt FROM lab_requests WHERE status='approved'")->fetch_assoc()['cnt'];
              ?>
              <span class="info-box-number"><?= $approved_count ?></span>
            </div>
          </div>
        </div>

        <div class="col-lg-3 col-6">
          <div class="info-box bg-danger">
            <span class="info-box-icon"><i class="fas fa-times-circle"></i></span>
            <div class="info-box-content">
              <span class="info-box-text">Denied Requests</span>
              <?php
              $denied_count = $conn->query("SELECT COUNT(*) as cnt FROM lab_requests WHERE status='denied'")->fetch_assoc()['cnt'];
              ?>
              <span class="info-box-number"><?= $denied_count ?></span>
            </div>
          </div>
        </div>

        <div class="col-lg-3 col-6">
          <div class="info-box bg-info">
            <span class="info-box-icon"><i class="fas fa-list"></i></span>
            <div class="info-box-content">
              <span class="info-box-text">Total Requests</span>
              <?php
              $total_count = $conn->query("SELECT COUNT(*) as cnt FROM lab_requests")->fetch_assoc()['cnt'];
              ?>
              <span class="info-box-number"><?= $total_count ?></span>
            </div>
          </div>
        </div>
      </div>

      <!-- Pending Requests -->
      <div class="card card-warning card-outline">
        <div class="card-header">
          <h3 class="card-title"><i class="fas fa-hourglass-half"></i> Pending Lab Requests</h3>
        </div>
        <div class="card-body table-responsive p-0">
          <table class="table table-hover table-striped">
            <thead class="bg-warning text-white">
              <tr>
                <th style="width: 5%">ID</th>
                <th style="width: 15%">User</th>
                <th style="width: 20%">Lab</th>
                <th style="width: 15%">Course</th>
                <th style="width: 25%">Justification</th>
                <th style="width: 12%">Requested</th>
                <th style="width: 8%">Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php
              $pending_q = $conn->query("
                SELECT lr.id, lr.justification, lr.created_at, u.email, u.name, l.lab_name, c.title as course_name
                FROM lab_requests lr
                LEFT JOIN users u ON lr.user_id = u.id
                LEFT JOIN labs l ON lr.lab_id = l.id
                LEFT JOIN courses c ON l.course_id = c.id
                WHERE lr.status = 'pending'
                ORDER BY lr.created_at ASC
              ");

              if ($pending_q && $pending_q->num_rows > 0) {
                while($r = $pending_q->fetch_assoc()):
              ?>
              <tr>
                <td><strong>#<?= $r['id'] ?></strong></td>
                <td>
                  <strong><?= htmlspecialchars($r['name'] ?? $r['email']) ?></strong><br>
                  <small class="text-muted"><?= htmlspecialchars($r['email']) ?></small>
                </td>
                <td><?= htmlspecialchars($r['lab_name'] ?? 'N/A') ?></td>
                <td><?= htmlspecialchars($r['course_name'] ?? 'N/A') ?></td>
                <td>
                  <small><?= htmlspecialchars(substr($r['justification'] ?? 'N/A', 0, 50)) ?></small>
                </td>
                <td><?= date('M d, H:i', strtotime($r['created_at'])) ?></td>
                <td>
                  <a href="?approve=<?= $r['id'] ?>" class="btn btn-xs btn-success" title="Approve">
                    <i class="fas fa-check"></i>
                  </a>
                  <a href="?deny=<?= $r['id'] ?>" class="btn btn-xs btn-danger" title="Deny">
                    <i class="fas fa-times"></i>
                  </a>
                </td>
              </tr>
              <?php 
                endwhile;
              } else {
              ?>
              <tr>
                <td colspan="7" class="text-center text-muted p-4">
                  <i class="fas fa-inbox fa-2x mb-2"></i><br>
                  No pending requests
                </td>
              </tr>
              <?php } ?>
            </tbody>
          </table>
        </div>
      </div>

      <!-- All Requests History -->
      <div class="card card-primary mt-4">
        <div class="card-header">
          <h3 class="card-title"><i class="fas fa-history"></i> All Requests & Assignment History</h3>
        </div>
        <div class="card-body">
          <!-- Filters & Search -->
          <form method="GET" class="form-horizontal mb-3">
            <div class="row">
              <div class="col-md-3">
                <div class="form-group">
                  <label>Filter by User:</label>
                  <select name="user_filter" class="form-control">
                    <option value="">All Users</option>
                    <?php
                    $users_q = $conn->query("SELECT DISTINCT u.id, u.email, u.name 
                                             FROM users u 
                                             JOIN lab_requests lr ON u.id = lr.user_id 
                                             ORDER BY u.email");
                    while($u = $users_q->fetch_assoc()):
                      $selected = (isset($_GET['user_filter']) && $_GET['user_filter'] == $u['id']) ? 'selected' : '';
                    ?>
                    <option value="<?= $u['id'] ?>" <?= $selected ?>>
                      <?= htmlspecialchars($u['name'] ?? $u['email']) ?>
                    </option>
                    <?php endwhile; ?>
                  </select>
                </div>
              </div>

              <div class="col-md-3">
                <div class="form-group">
                  <label>Filter by Status:</label>
                  <select name="status_filter" class="form-control">
                    <option value="">All Status</option>
                    <option value="pending" <?= (isset($_GET['status_filter']) && $_GET['status_filter'] === 'pending') ? 'selected' : '' ?>>Pending</option>
                    <option value="approved" <?= (isset($_GET['status_filter']) && $_GET['status_filter'] === 'approved') ? 'selected' : '' ?>>Approved</option>
                    <option value="denied" <?= (isset($_GET['status_filter']) && $_GET['status_filter'] === 'denied') ? 'selected' : '' ?>>Denied</option>
                  </select>
                </div>
              </div>

              <div class="col-md-3">
                <div class="form-group">
                  <label>Lab Name Search:</label>
                  <input type="text" name="lab_search" class="form-control" placeholder="Search lab name..." 
                         value="<?= htmlspecialchars($_GET['lab_search'] ?? '') ?>">
                </div>
              </div>

              <div class="col-md-3">
                <div class="form-group">
                  <label>&nbsp;</label>
                  <button type="submit" class="btn btn-primary btn-block">
                    <i class="fas fa-search"></i> Search
                  </button>
                </div>
              </div>
            </div>

            <?php if (isset($_GET['user_filter']) || isset($_GET['status_filter']) || isset($_GET['lab_search'])): ?>
            <div class="row">
              <div class="col-md-12">
                <a href="admin_lab_requests.php" class="btn btn-default">
                  <i class="fas fa-times"></i> Clear All Filters
                </a>
              </div>
            </div>
            <?php endif; ?>
          </form>
        </div>
      </div>

      <!-- Requests Table -->
      <div class="card card-info">
        <div class="card-body table-responsive p-0">
          <table class="table table-hover table-striped">
            <thead class="bg-info text-white">
              <tr>
                <th style="width: 5%">ID</th>
                <th style="width: 15%">User</th>
                <th style="width: 20%">Lab</th>
                <th style="width: 15%">Course</th>
                <th style="width: 12%">Status</th>
                <th style="width: 14%">Requested Date</th>
                <th style="width: 14%">Reviewed Date</th>
                <th style="width: 9%">Reviewed By</th>
              </tr>
            </thead>
            <tbody>
              <?php
              // Build dynamic query with filters
              $where_clauses = ["1=1"];
              
              if (isset($_GET['user_filter']) && !empty($_GET['user_filter'])) {
                $filter_user = (int)$_GET['user_filter'];
                $where_clauses[] = "lr.user_id = $filter_user";
              }
              
              if (isset($_GET['status_filter']) && !empty($_GET['status_filter'])) {
                $filter_status = $conn->real_escape_string($_GET['status_filter']);
                $where_clauses[] = "lr.status = '$filter_status'";
              }
              
              if (isset($_GET['lab_search']) && !empty($_GET['lab_search'])) {
                $lab_search = $conn->real_escape_string($_GET['lab_search']);
                $where_clauses[] = "l.lab_name LIKE '%$lab_search%'";
              }
              
              $where_sql = implode(' AND ', $where_clauses);
              
              $query = "
                SELECT lr.id, lr.justification, lr.status, lr.created_at, lr.reviewed_at, lr.reviewed_by,
                       u.email, u.name, l.lab_name, c.title as course_name, ru.email as reviewer_email
                FROM lab_requests lr
                LEFT JOIN users u ON lr.user_id = u.id
                LEFT JOIN labs l ON lr.lab_id = l.id
                LEFT JOIN courses c ON l.course_id = c.id
                LEFT JOIN users ru ON lr.reviewed_by = ru.id
                WHERE $where_sql
                ORDER BY lr.created_at DESC
                LIMIT 200
              ";
              
              $all_q = $conn->query($query);
              
              if (!$all_q) {
                  echo "<tr><td colspan='8' class='text-danger'><i class='fas fa-exclamation-triangle'></i> Query error: " . $conn->error . "</td></tr>";
              } else if ($all_q->num_rows === 0) {
                echo "<tr><td colspan='8' class='text-center text-muted p-4'><i class='fas fa-inbox fa-2x mb-2'></i><br>No requests found</td></tr>";
              } else {
                while($r = $all_q->fetch_assoc()):
                  $badge_class = $r['status'] === 'approved' ? 'success' : ($r['status'] === 'denied' ? 'danger' : 'warning');
              ?>
              <tr>
                <td><strong>#<?= $r['id'] ?></strong></td>
                <td>
                  <strong><?= htmlspecialchars($r['name'] ?? $r['email']) ?></strong><br>
                  <small class="text-muted"><?= htmlspecialchars($r['email']) ?></small>
                </td>
                <td><?= htmlspecialchars($r['lab_name'] ?? 'N/A') ?></td>
                <td><?= htmlspecialchars($r['course_name'] ?? 'N/A') ?></td>
                <td>
                  <span class="badge badge-<?= $badge_class ?>">
                    <i class="fas fa-<?= $r['status'] === 'approved' ? 'check-circle' : ($r['status'] === 'denied' ? 'times-circle' : 'hourglass-half') ?>"></i>
                    <?= ucfirst($r['status']) ?>
                  </span>
                </td>
                <td><?= date('M d, Y H:i', strtotime($r['created_at'])) ?></td>
                <td><?= $r['reviewed_at'] ? date('M d, Y H:i', strtotime($r['reviewed_at'])) : '<span class="text-muted">-</span>' ?></td>
                <td><small><?= htmlspecialchars($r['reviewer_email'] ?? '-') ?></small></td>
              </tr>
              <?php 
                endwhile;
              } ?>
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
