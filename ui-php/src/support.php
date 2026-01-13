<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$page_title = "Support Cases";
$uid = $_SESSION['uid'];
$role = $_SESSION['role'];
$conn = new mysqli("mysql","monitor","monitor123","monitoring");
if ($conn->connect_error) die("DB Error");

// Handle new support case
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_case'])) {
    $subject = $conn->real_escape_string($_POST['subject']);
    $description = $conn->real_escape_string($_POST['description']);
    $category = $conn->real_escape_string($_POST['category']);
    
    $conn->query("INSERT INTO support_cases (user_id, subject, description, category, status, created_at)
                  VALUES ($uid, '$subject', '$description', '$category', 'OPEN', NOW())");
    $_SESSION['success'] = "Support case created successfully!";
    header("Location: support.php");
    exit;
}

// Handle case closure (user can close own cases)
if (isset($_GET['close'])) {
    $case_id = (int)$_GET['close'];
    $conn->query("UPDATE support_cases SET status='RESOLVED' WHERE id=$case_id AND user_id=$uid");
    $_SESSION['success'] = "Support case closed.";
    header("Location: support.php");
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
          <h1 class="m-0"><i class="fas fa-life-ring"></i> Support Cases</h1>
        </div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
            <li class="breadcrumb-item active">Support</li>
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

      <div class="row">
        <div class="col-md-8">
          <!-- My Support Cases -->
          <div class="card">
            <div class="card-header">
              <h3 class="card-title"><i class="fas fa-ticket-alt"></i> My Support Cases</h3>
              <div class="card-tools">
                <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#newCaseModal">
                  <i class="fas fa-plus"></i> New Case
                </button>
              </div>
            </div>
            <div class="card-body table-responsive p-0">
              <table class="table table-hover">
                <thead>
                  <tr>
                    <th>ID</th>
                    <th>Subject</th>
                    <th>Category</th>
                    <th>Status</th>
                    <th>Created</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                  $cases_q = $conn->query("SELECT * FROM support_cases WHERE user_id=$uid ORDER BY created_at DESC");
                  while($case = $cases_q->fetch_assoc()):
                    $category_class = $case['category'] === 'PAYMENT' ? 'success' : ($case['category'] === 'REFUND' ? 'danger' : 'info');
                    $status_class = $case['status'] === 'OPEN' ? 'warning' : ($case['status'] === 'IN_PROGRESS' ? 'info' : ($case['status'] === 'RESOLVED' ? 'success' : 'secondary'));
                  ?>
                  <tr>
                    <td>#<?= $case['id'] ?></td>
                    <td>
                      <strong><?= htmlspecialchars($case['subject'] ?? 'N/A') ?></strong><br>
                      <small class="text-muted"><?= htmlspecialchars(substr($case['description'] ?? '', 0, 60)) ?>...</small>
                    </td>
                    <td><span class="badge badge-<?= $category_class ?>"><?= ucfirst($case['category'] ?? 'OTHER') ?></span></td>
                    <td><span class="badge badge-<?= $status_class ?>"><?= ucfirst(str_replace('_', ' ', $case['status'] ?? 'OPEN')) ?></span></td>
                    <td><?= date('M j, Y', strtotime($case['created_at'])) ?></td>
                    <td>
                      <button class="btn btn-xs btn-info" data-toggle="modal" data-target="#viewCase<?= $case['id'] ?>">
                        <i class="fas fa-eye"></i> View
                      </button>
                      <?php if ($case['status'] !== 'RESOLVED' && $case['status'] !== 'REJECTED'): ?>
                      <a href="?close=<?= $case['id'] ?>" class="btn btn-xs btn-secondary"
                         onclick="return confirm('Close this support case?')">
                        <i class="fas fa-times"></i> Close
                      </a>
                      <?php endif; ?>
                    </td>
                  </tr>

                  <!-- View Case Modal -->
                  <div class="modal fade" id="viewCase<?= $case['id'] ?>">
                    <div class="modal-dialog modal-lg">
                      <div class="modal-content">
                        <div class="modal-header">
                          <h4 class="modal-title">Case #<?= $case['id'] ?>: <?= htmlspecialchars($case['subject'] ?? 'N/A') ?></h4>
                          <button type="button" class="close" data-dismiss="modal">&times;</button>
                        </div>
                        <div class="modal-body">
                          <p><strong>Category:</strong> <span class="badge badge-<?= $category_class ?>"><?= ucfirst($case['category'] ?? 'OTHER') ?></span></p>
                          <p><strong>Status:</strong> <span class="badge badge-<?= $status_class ?>"><?= ucfirst(str_replace('_', ' ', $case['status'] ?? 'OPEN')) ?></span></p>
                          <p><strong>Created:</strong> <?= date('M j, Y g:i A', strtotime($case['created_at'])) ?></p>
                          <hr>
                          <p><strong>Description:</strong></p>
                          <p><?= nl2br(htmlspecialchars($case['description'] ?? 'No description')) ?></p>
                          <?php if ($case['resolution']): ?>
                          <hr>
                          <p><strong>Resolution:</strong></p>
                          <div class="alert alert-success">
                            <?= nl2br(htmlspecialchars($case['resolution'])) ?>
                          </div>
                          <?php endif; ?>
                        </div>
                        <div class="modal-footer">
                          <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                        </div>
                      </div>
                    </div>
                  </div>

                  <?php endwhile; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <div class="col-md-4">
          <!-- Support Info -->
          <div class="card card-info">
            <div class="card-header">
              <h3 class="card-title"><i class="fas fa-info-circle"></i> How Can We Help?</h3>
            </div>
            <div class="card-body">
              <p>Create a support case for:</p>
              <ul>
                <li>Technical issues with labs</li>
                <li>Account problems</li>
                <li>Questions about course content</li>
                <li>Billing inquiries</li>
                <li>Feature requests</li>
              </ul>
              <button class="btn btn-primary btn-block" data-toggle="modal" data-target="#newCaseModal">
                <i class="fas fa-plus"></i> Create New Case
              </button>
            </div>
          </div>

          <!-- Quick Links -->
          <div class="card">
            <div class="card-header">
              <h3 class="card-title"><i class="fas fa-link"></i> Quick Links</h3>
            </div>
            <div class="card-body">
              <a href="browse_labs.php" class="btn btn-default btn-block">
                <i class="fas fa-flask"></i> Browse Labs
              </a>
              <a href="my_active_labs.php" class="btn btn-default btn-block">
                <i class="fas fa-laptop-code"></i> My Active Labs
              </a>
              <a href="profile.php" class="btn btn-default btn-block">
                <i class="fas fa-user"></i> My Profile
              </a>
            </div>
          </div>
        </div>
      </div>

    </div>
  </section>
</div>

<!-- New Case Modal -->
<div class="modal fade" id="newCaseModal">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-primary">
        <h4 class="modal-title"><i class="fas fa-plus"></i> Create New Support Case</h4>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
      </div>
      <form method="POST">
        <div class="modal-body">
          <div class="form-group">
            <label>Subject <span class="text-danger">*</span></label>
            <input type="text" name="subject" class="form-control" required 
                   placeholder="Brief description of your issue">
          </div>

          <div class="form-group">
            <label>Category <span class="text-danger">*</span></label>
            <select name="category" class="form-control" required>
              <option value="ACCESS">Access Issue</option>
              <option value="LAB">Lab Technical Issue</option>
              <option value="PAYMENT">Payment / Billing</option>
              <option value="REFUND">Refund Request</option>
              <option value="OTHER">Other</option>
            </select>
          </div>

          <div class="form-group">
            <label>Description <span class="text-danger">*</span></label>
            <textarea name="description" class="form-control" rows="6" required 
                      placeholder="Please provide detailed information about your issue..."></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" name="create_case" class="btn btn-primary">
            <i class="fas fa-paper-plane"></i> Submit Case
          </button>
          <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php include 'includes/footer.php'; ?>

</div>
</body>
</html>
