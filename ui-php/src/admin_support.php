<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

$page_title = "Support Cases Management";
$conn = new mysqli("mysql","monitor","monitor123","monitoring");
if ($conn->connect_error) die("DB Error");

// Handle adding message to case (admin reply)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_message'])) {
    $case_id = (int)$_POST['case_id'];
    $message = $conn->real_escape_string($_POST['message']);
    $admin_id = $_SESSION['uid'];
    
    $conn->query("INSERT INTO support_case_messages (case_id, sender_type, sender_id, message, created_at)
                  VALUES ($case_id, 'ADMIN', $admin_id, '$message', NOW())");
    
    $conn->query("UPDATE support_cases 
                  SET last_response_by='ADMIN', last_response_at=NOW()
                  WHERE id=$case_id");
    
    $_SESSION['success'] = "Reply sent to user!";
    header("Location: admin_support.php?view=$case_id");
    exit;
}

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_case'])) {
    $case_id = (int)$_POST['case_id'];
    $status = $conn->real_escape_string($_POST['status']);
    $resolution = $conn->real_escape_string($_POST['resolution'] ?? '');
    
    $conn->query("UPDATE support_cases SET status='$status', resolution='$resolution' WHERE id=$case_id");
    $_SESSION['success'] = "Support case updated successfully!";
    header("Location: admin_support.php?view=$case_id");
    exit;
}

$view_case_id = isset($_GET['view']) ? (int)$_GET['view'] : null;

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

      <?php if ($view_case_id): ?>
        <!-- Case Detail View with Chat -->
        <?php
        $case_q = $conn->query("SELECT sc.*, u.username, u.email 
                                FROM support_cases sc 
                                LEFT JOIN users u ON sc.user_id = u.id 
                                WHERE sc.id=$view_case_id");
        
        if (!$case_q) {
            echo '<div class="alert alert-danger">Database error: ' . htmlspecialchars($conn->error) . '</div>';
            $case = null;
        } else {
            $case = $case_q->fetch_assoc();
        }
        
        if (!$case) {
            echo '<div class="alert alert-danger">Case not found.</div>';
        } else:
            $category_class = $case['category'] === 'PAYMENT' ? 'success' : ($case['category'] === 'REFUND' ? 'danger' : 'info');
            $status_class = $case['status'] === 'OPEN' ? 'warning' : ($case['status'] === 'IN_PROGRESS' ? 'info' : ($case['status'] === 'RESOLVED' ? 'success' : 'secondary'));
            
            $pending_on = isset($case['last_response_by']) && $case['last_response_by'] === 'ADMIN' ? 'User' : 'Support Team';
            $pending_class = isset($case['last_response_by']) && $case['last_response_by'] === 'ADMIN' ? 'info' : 'warning';
        ?>
        
        <div class="row">
          <div class="col-md-12">
            <div class="card">
              <div class="card-header bg-primary">
                <h3 class="card-title">
                  <i class="fas fa-ticket-alt"></i> Case #<?= $case['id'] ?> - <?= htmlspecialchars($case['subject'] ?? '') ?>
                </h3>
                <div class="card-tools">
                  <a href="admin_support.php" class="btn btn-sm btn-light">
                    <i class="fas fa-arrow-left"></i> Back to List
                  </a>
                </div>
              </div>
              <div class="card-body">
                <div class="row mb-3">
                  <div class="col-md-3">
                    <strong>User:</strong> 
                    <?= htmlspecialchars($case['username'] ?? $case['email'] ?? 'Unknown') ?>
                  </div>
                  <div class="col-md-2">
                    <strong>Status:</strong> 
                    <span class="badge badge-<?= $status_class ?>">
                      <?= ucfirst(str_replace('_', ' ', $case['status'] ?? 'OPEN')) ?>
                    </span>
                  </div>
                  <div class="col-md-2">
                    <strong>Category:</strong> 
                    <span class="badge badge-<?= $category_class ?>"><?= $case['category'] ?></span>
                  </div>
                  <div class="col-md-2">
                    <strong>Created:</strong> <?= date('M d, Y H:i', strtotime($case['created_at'])) ?>
                  </div>
                  <div class="col-md-3">
                    <strong>Pending On:</strong> 
                    <span class="badge badge-<?= $pending_class ?>">
                      <i class="fas fa-<?= isset($case['last_response_by']) && $case['last_response_by'] === 'ADMIN' ? 'user' : 'headset' ?>"></i> <?= $pending_on ?>
                    </span>
                  </div>
                </div>
                
                <hr>
                
                <!-- Message History -->
                <h5><i class="fas fa-comments"></i> Conversation History</h5>
                <div class="direct-chat-messages" style="height: 400px; overflow-y: auto; border: 1px solid #dee2e6; padding: 10px; border-radius: 5px; background: #f8f9fa;">
                  <?php
                  $messages_q = $conn->query("SELECT scm.*, 
                                              CASE 
                                                WHEN scm.sender_type='USER' THEN u.username
                                                ELSE a.username
                                              END as sender_name,
                                              CASE 
                                                WHEN scm.sender_type='USER' THEN u.email
                                                ELSE a.email
                                              END as sender_email
                                              FROM support_case_messages scm
                                              LEFT JOIN users u ON scm.sender_type='USER' AND scm.sender_id = u.id
                                              LEFT JOIN users a ON scm.sender_type='ADMIN' AND scm.sender_id = a.id
                                              WHERE scm.case_id = $view_case_id
                                              ORDER BY scm.created_at ASC");
                  
                  if (!$messages_q) {
                      echo '<div class="alert alert-warning">Could not load messages: ' . htmlspecialchars($conn->error) . '</div>';
                  } else {
                      while($msg = $messages_q->fetch_assoc()):
                        $is_admin = $msg['sender_type'] === 'ADMIN';
                        $align = $is_admin ? 'right' : 'left';
                        $bg_class = $is_admin ? 'bg-success' : 'bg-primary';
                  ?>
                  <div class="direct-chat-msg <?= $align ?>">
                    <div class="direct-chat-infos clearfix">
                      <span class="direct-chat-name float-<?= $align ?>">
                        <?= $is_admin ? 'Support Team' : 'User' ?> (<?= htmlspecialchars($msg['sender_name'] ?? $msg['sender_email'] ?? 'Unknown') ?>)
                      </span>
                      <span class="direct-chat-timestamp float-<?= $align === 'right' ? 'left' : 'right' ?>">
                        <?= date('M d, Y H:i', strtotime($msg['created_at'])) ?>
                      </span>
                    </div>
                    <div class="direct-chat-text <?= $bg_class ?>" style="<?= $is_admin ? 'margin-left: 50px;' : 'margin-right: 50px;' ?>">
                      <?= nl2br(htmlspecialchars($msg['message'] ?? '')) ?>
                    </div>
                  </div>
                  <?php 
                      endwhile;
                  } // end if messages_q
                  ?>
                </div>
                
                <hr>
                
                <!-- Admin Reply Form -->
                <?php if ($case['status'] !== 'RESOLVED' && $case['status'] !== 'REJECTED'): ?>
                <form method="POST" action="admin_support.php">
                  <input type="hidden" name="case_id" value="<?= $case['id'] ?>">
                  <input type="hidden" name="add_message" value="1">
                  <div class="form-group">
                    <label><i class="fas fa-reply"></i> Reply to User</label>
                    <textarea name="message" class="form-control" rows="4" placeholder="Type your response here..." required></textarea>
                  </div>
                  <button type="submit" class="btn btn-primary">
                    <i class="fas fa-paper-plane"></i> Send Reply
                  </button>
                </form>
                
                <hr>
                
                <!-- Update Case Status -->
                <form method="POST" action="admin_support.php">
                  <input type="hidden" name="case_id" value="<?= $case['id'] ?>">
                  <input type="hidden" name="update_case" value="1">
                  <div class="row">
                    <div class="col-md-4">
                      <div class="form-group">
                        <label>Status</label>
                        <select name="status" class="form-control">
                          <option value="OPEN" <?= $case['status'] === 'OPEN' ? 'selected' : '' ?>>Open</option>
                          <option value="IN_PROGRESS" <?= $case['status'] === 'IN_PROGRESS' ? 'selected' : '' ?>>In Progress</option>
                          <option value="RESOLVED" <?= $case['status'] === 'RESOLVED' ? 'selected' : '' ?>>Resolved</option>
                          <option value="REJECTED" <?= $case['status'] === 'REJECTED' ? 'selected' : '' ?>>Rejected</option>
                        </select>
                      </div>
                    </div>
                    <div class="col-md-8">
                      <div class="form-group">
                        <label>Resolution Notes (Optional)</label>
                        <textarea name="resolution" class="form-control" rows="2" placeholder="Final resolution notes..."><?= htmlspecialchars($case['resolution'] ?? '') ?></textarea>
                      </div>
                    </div>
                  </div>
                  <button type="submit" class="btn btn-success">
                    <i class="fas fa-save"></i> Update Status
                  </button>
                </form>
                <?php else: ?>
                <div class="alert alert-info">
                  <i class="fas fa-info-circle"></i> This case is closed.
                </div>
                <?php endif; ?>
              </div>
            </div>
          </div>
        </div>
        
        <?php endif; ?>
        
      <?php else: ?>
        <!-- Case List View -->

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
                SELECT sc.*, u.email, u.username AS name
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
                  <strong><?= htmlspecialchars($case['name'] ?? $case['email'] ?? '') ?></strong><br>
                  <small class="text-muted"><?= htmlspecialchars($case['email'] ?? '') ?></small>
                </td>
                <td><span class="badge badge-<?= $category_class ?>"><?= htmlspecialchars($case['category'] ?? 'OTHER') ?></span></td>
                <td>
                  <strong><?= htmlspecialchars($case['subject'] ?? 'N/A') ?></strong><br>
                  <small class="text-muted"><?= htmlspecialchars(substr($case['description'] ?? '', 0, 50)) ?>...</small>
                </td>
                <td><span class="badge badge-<?= $status_class ?>"><?= ucfirst(str_replace('_', ' ', $case['status'] ?? 'OPEN')) ?></span></td>
                <td><?= date('M d, Y H:i', strtotime($case['created_at'])) ?></td>
                <td>
                  <a href="admin_support.php?view=<?= $case['id'] ?>" class="btn btn-xs btn-primary">
                    <i class="fas fa-eye"></i> View & Reply
                  </a>
                </td>
              </tr>

              <?php 
                endwhile;
              } ?>
            </tbody>
          </table>
        </div>
      </div>
      
      <?php endif; // end case list view ?>

    </div>
  </section>
</div>

<?php include 'includes/footer.php'; ?>

</div>
</body>
</html>
