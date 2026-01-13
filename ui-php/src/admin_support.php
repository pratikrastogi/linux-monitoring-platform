<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

$page_title = "Support Cases Management";
$conn = new mysqli("mysql","monitor","monitor123","monitoring");
if ($conn->connect_error) die("DB Error");

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_case'])) {
    $case_id = (int)$_POST['case_id'];
    $status = $conn->real_escape_string($_POST['status']);
    $resolution = $conn->real_escape_string($_POST['resolution']);
    
    $conn->query("UPDATE support_cases SET status='$status', resolution='$resolution' WHERE id=$case_id");
    $_SESSION['success'] = "Support case updated successfully!";
    header("Location: admin_support.php");
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
          <h1 class="m-0"><i class="fas fa-headset"></i> Support Cases Management</h1>
        </div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
            <li class="breadcrumb-item active">Support Cases</li>
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
            <span class="info-box-icon"><i class="fas fa-inbox"></i></span>
            <div class="info-box-content">
              <span class="info-box-text">Open Cases</span>
              <?php
              $open_count = $conn->query("SELECT COUNT(*) as cnt FROM support_cases WHERE status='OPEN'")->fetch_assoc()['cnt'];
              ?>
              <span class="info-box-number"><?= $open_count ?></span>
            </div>
          </div>
        </div>

        <div class="col-lg-3 col-6">
          <div class="info-box bg-info">
            <span class="info-box-icon"><i class="fas fa-spinner"></i></span>
            <div class="info-box-content">
              <span class="info-box-text">In Progress</span>
              <?php
              $progress_count = $conn->query("SELECT COUNT(*) as cnt FROM support_cases WHERE status='IN_PROGRESS'")->fetch_assoc()['cnt'];
              ?>
              <span class="info-box-number"><?= $progress_count ?></span>
            </div>
          </div>
        </div>

        <div class="col-lg-3 col-6">
          <div class="info-box bg-success">
            <span class="info-box-icon"><i class="fas fa-check-circle"></i></span>
            <div class="info-box-content">
              <span class="info-box-text">Resolved</span>
              <?php
              $resolved_count = $conn->query("SELECT COUNT(*) as cnt FROM support_cases WHERE status='RESOLVED'")->fetch_assoc()['cnt'];
              ?>
              <span class="info-box-number"><?= $resolved_count ?></span>
            </div>
          </div>
        </div>

        <div class="col-lg-3 col-6">
          <div class="info-box bg-danger">
            <span class="info-box-icon"><i class="fas fa-times-circle"></i></span>
            <div class="info-box-content">
              <span class="info-box-text">Rejected</span>
              <?php
              $rejected_count = $conn->query("SELECT COUNT(*) as cnt FROM support_cases WHERE status='REJECTED'")->fetch_assoc()['cnt'];
              ?>
              <span class="info-box-number"><?= $rejected_count ?></span>
            </div>
          </div>
        </div>
      </div>

      <!-- Filter -->
      <div class="card">
        <div class="card-body">
          <form method="GET" class="form-inline">
            <div class="form-group mr-3">
              <label class="mr-2">Filter by Status:</label>
              <select name="status" class="form-control" onchange="this.form.submit()">
                <option value="">All Status</option>
                <option value="OPEN" <?= (isset($_GET['status']) && $_GET['status'] === 'OPEN') ? 'selected' : '' ?>>Open</option>
                <option value="IN_PROGRESS" <?= (isset($_GET['status']) && $_GET['status'] === 'IN_PROGRESS') ? 'selected' : '' ?>>In Progress</option>
                <option value="RESOLVED" <?= (isset($_GET['status']) && $_GET['status'] === 'RESOLVED') ? 'selected' : '' ?>>Resolved</option>
                <option value="REJECTED" <?= (isset($_GET['status']) && $_GET['status'] === 'REJECTED') ? 'selected' : '' ?>>Rejected</option>
              </select>
            </div>

            <div class="form-group mr-3">
              <label class="mr-2">Filter by Category:</label>
              <select name="category" class="form-control" onchange="this.form.submit()">
                <option value="">All Categories</option>
                <option value="ACCESS" <?= (isset($_GET['category']) && $_GET['category'] === 'ACCESS') ? 'selected' : '' ?>>Access</option>
                <option value="LAB" <?= (isset($_GET['category']) && $_GET['category'] === 'LAB') ? 'selected' : '' ?>>Lab</option>
                <option value="PAYMENT" <?= (isset($_GET['category']) && $_GET['category'] === 'PAYMENT') ? 'selected' : '' ?>>Payment</option>
                <option value="REFUND" <?= (isset($_GET['category']) && $_GET['category'] === 'REFUND') ? 'selected' : '' ?>>Refund</option>
                <option value="OTHER" <?= (isset($_GET['category']) && $_GET['category'] === 'OTHER') ? 'selected' : '' ?>>Other</option>
              </select>
            </div>

            <?php if (isset($_GET['status']) || isset($_GET['category'])): ?>
            <a href="admin_support.php" class="btn btn-default">
              <i class="fas fa-times"></i> Clear Filters
            </a>
            <?php endif; ?>
          </form>
        </div>
      </div>

      <!-- Support Cases Table -->
      <div class="card card-primary">
        <div class="card-header">
          <h3 class="card-title"><i class="fas fa-ticket-alt"></i> All Support Cases</h3>
        </div>
        <div class="card-body table-responsive p-0">
          <table class="table table-hover table-striped">
            <thead class="bg-primary text-white">
              <tr>
                <th style="width: 5%">ID</th>
                <th style="width: 15%">User</th>
                <th style="width: 10%">Category</th>
                <th style="width: 25%">Subject</th>
                <th style="width: 10%">Status</th>
                <th style="width: 15%">Created</th>
                <th style="width: 20%">Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php
              // Build query with filters
              $where_clauses = ["1=1"];
              
              if (isset($_GET['status']) && !empty($_GET['status'])) {
                $filter_status = $conn->real_escape_string($_GET['status']);
                $where_clauses[] = "sc.status = '$filter_status'";
              }
              
              if (isset($_GET['category']) && !empty($_GET['category'])) {
                $filter_category = $conn->real_escape_string($_GET['category']);
                $where_clauses[] = "sc.category = '$filter_category'";
              }
              
              $where_sql = implode(' AND ', $where_clauses);
              
              $query = "
                SELECT sc.*, u.email, u.name
                FROM support_cases sc
                LEFT JOIN users u ON sc.user_id = u.id
                WHERE $where_sql
                ORDER BY 
                  CASE sc.status
                    WHEN 'OPEN' THEN 1
                    WHEN 'IN_PROGRESS' THEN 2
                    WHEN 'RESOLVED' THEN 3
                    WHEN 'REJECTED' THEN 4
                  END,
                  sc.created_at DESC
                LIMIT 200
              ";
              
              $cases_q = $conn->query($query);
              
              if (!$cases_q) {
                  echo "<tr><td colspan='7' class='text-danger'><i class='fas fa-exclamation-triangle'></i> Query error: " . $conn->error . "</td></tr>";
              } else if ($cases_q->num_rows === 0) {
                echo "<tr><td colspan='7' class='text-center text-muted p-4'><i class='fas fa-inbox fa-2x mb-2'></i><br>No support cases found</td></tr>";
              } else {
                while($case = $cases_q->fetch_assoc()):
                  $category_class = $case['category'] === 'PAYMENT' ? 'success' : ($case['category'] === 'REFUND' ? 'danger' : 'info');
                  $status_class = $case['status'] === 'OPEN' ? 'warning' : ($case['status'] === 'IN_PROGRESS' ? 'info' : ($case['status'] === 'RESOLVED' ? 'success' : 'secondary'));
              ?>
              <tr>
                <td><strong>#<?= $case['id'] ?></strong></td>
                <td>
                  <strong><?= htmlspecialchars($case['name'] ?? $case['email']) ?></strong><br>
                  <small class="text-muted"><?= htmlspecialchars($case['email']) ?></small>
                </td>
                <td><span class="badge badge-<?= $category_class ?>"><?= htmlspecialchars($case['category'] ?? 'OTHER') ?></span></td>
                <td>
                  <strong><?= htmlspecialchars($case['subject'] ?? 'N/A') ?></strong><br>
                  <small class="text-muted"><?= htmlspecialchars(substr($case['description'] ?? '', 0, 50)) ?>...</small>
                </td>
                <td><span class="badge badge-<?= $status_class ?>"><?= ucfirst(str_replace('_', ' ', $case['status'] ?? 'OPEN')) ?></span></td>
                <td><?= date('M d, Y H:i', strtotime($case['created_at'])) ?></td>
                <td>
                  <button class="btn btn-xs btn-primary" data-toggle="modal" data-target="#manageCase<?= $case['id'] ?>">
                    <i class="fas fa-edit"></i> Manage
                  </button>
                </td>
              </tr>

              <!-- Manage Case Modal -->
              <div class="modal fade" id="manageCase<?= $case['id'] ?>">
                <div class="modal-dialog modal-lg">
                  <div class="modal-content">
                    <div class="modal-header bg-primary">
                      <h4 class="modal-title">Manage Case #<?= $case['id'] ?>: <?= htmlspecialchars($case['subject'] ?? 'N/A') ?></h4>
                      <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                    </div>
                    <form method="POST">
                      <input type="hidden" name="case_id" value="<?= $case['id'] ?>">
                      <div class="modal-body">
                        <div class="row">
                          <div class="col-md-6">
                            <p><strong>User:</strong> <?= htmlspecialchars($case['name'] ?? $case['email']) ?></p>
                            <p><strong>Email:</strong> <?= htmlspecialchars($case['email']) ?></p>
                          </div>
                          <div class="col-md-6">
                            <p><strong>Category:</strong> <span class="badge badge-<?= $category_class ?>"><?= htmlspecialchars($case['category'] ?? 'OTHER') ?></span></p>
                            <p><strong>Created:</strong> <?= date('M d, Y H:i', strtotime($case['created_at'])) ?></p>
                          </div>
                        </div>
                        <hr>
                        <p><strong>Description:</strong></p>
                        <div class="alert alert-light">
                          <?= nl2br(htmlspecialchars($case['description'] ?? 'No description')) ?>
                        </div>

                        <div class="form-group">
                          <label><strong>Status:</strong></label>
                          <select name="status" class="form-control">
                            <option value="OPEN" <?= $case['status'] === 'OPEN' ? 'selected' : '' ?>>Open</option>
                            <option value="IN_PROGRESS" <?= $case['status'] === 'IN_PROGRESS' ? 'selected' : '' ?>>In Progress</option>
                            <option value="RESOLVED" <?= $case['status'] === 'RESOLVED' ? 'selected' : '' ?>>Resolved</option>
                            <option value="REJECTED" <?= $case['status'] === 'REJECTED' ? 'selected' : '' ?>>Rejected</option>
                          </select>
                        </div>

                        <div class="form-group">
                          <label><strong>Resolution / Response:</strong></label>
                          <textarea name="resolution" class="form-control" rows="6" placeholder="Enter your response or resolution..."><?= htmlspecialchars($case['resolution'] ?? '') ?></textarea>
                        </div>
                      </div>
                      <div class="modal-footer">
                        <button type="submit" name="update_case" class="btn btn-primary">
                          <i class="fas fa-save"></i> Update Case
                        </button>
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                      </div>
                    </form>
                  </div>
                </div>
              </div>

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
