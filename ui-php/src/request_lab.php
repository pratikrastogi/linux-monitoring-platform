<?php
session_start();
require_once 'auth.php';

$db = new mysqli("mysql", "monitor", "monitor123", "monitoring");
$uid = $_SESSION['uid'];

$result = $db->query("SELECT l.*, c.name as course_name FROM labs l LEFT JOIN courses c ON l.course_id = c.id WHERE l.active = 1");
$labs = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['lab_id'])) {
    $lab_id = (int)$_POST['lab_id'];
    
    // Check if user already has an active session for this lab
    $existing_session = $db->query("SELECT id, access_expiry, status FROM lab_sessions 
                                    WHERE user_id=$uid AND lab_id=$lab_id 
                                    AND (status='ACTIVE' OR status='REQUESTED' OR status='PROVISIONING')
                                    AND access_expiry > NOW()")->fetch_assoc();
    
    if ($existing_session) {
        $_SESSION['error'] = "You already have an active session for this lab. It expires on " . 
                            date('M d, Y H:i', strtotime($existing_session['access_expiry'])) . 
                            ". Please wait for the session to expire or contact support to extend your access.";
        header("Location: request_lab.php?lab_id=$lab_id&duplicate=1");
        exit;
    }
    
    // Check if there's a pending request for this lab
    $pending_request = $db->query("SELECT id FROM lab_requests 
                                  WHERE user_id=$uid AND lab_id=$lab_id 
                                  AND status='pending'")->fetch_assoc();
    
    if ($pending_request) {
        $_SESSION['warning'] = "You already have a pending request for this lab. Please wait for admin approval.";
        header("Location: request_lab.php?lab_id=$lab_id&pending=1");
        exit;
    }
    
    // Create new request
    $db->query("INSERT INTO lab_requests (user_id, lab_id, status, created_at) VALUES ($uid, $lab_id, 'pending', NOW())");
    $_SESSION['success'] = "Lab request submitted successfully! Please wait for admin approval.";
    header("Location: my_labs.php?msg=requested");
    exit;
}

$page_title = "Request Lab Access";
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
          <h1 class="m-0"><i class="fas fa-flask"></i> Request Lab Access</h1>
        </div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
            <li class="breadcrumb-item active">Request Lab</li>
          </ol>
        </div>
      </div>
    </div>
  </div>
  <section class="content">
    <div class="container-fluid">
      
      <?php if (isset($_SESSION['error'])): ?>
      <div class="alert alert-danger alert-dismissible">
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        <i class="fas fa-exclamation-circle"></i> <?= $_SESSION['error']; unset($_SESSION['error']); ?>
      </div>
      <?php endif; ?>
      
      <?php if (isset($_SESSION['warning'])): ?>
      <div class="alert alert-warning alert-dismissible">
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        <i class="fas fa-info-circle"></i> <?= $_SESSION['warning']; unset($_SESSION['warning']); ?>
      </div>
      <?php endif; ?>
      
      <div class="card card-primary">
        <div class="card-header">
          <h3 class="card-title"><i class="fas fa-plus-circle"></i> Available Labs</h3>
        </div>
        <div class="card-body">
          <?php if (count($labs) === 0): ?>
            <p class="text-muted"><i class="fas fa-info-circle"></i> No labs available.</p>
          <?php else: ?>
            <div class="row">
              <?php foreach ($labs as $lab): 
                // Check if user has active session for this lab
                $active_session = $db->query("SELECT id, access_expiry, status FROM lab_sessions 
                                             WHERE user_id=$uid AND lab_id={$lab['id']} 
                                             AND (status='ACTIVE' OR status='REQUESTED' OR status='PROVISIONING')
                                             AND access_expiry > NOW()")->fetch_assoc();
                
                // Check for pending request
                $pending_req = $db->query("SELECT id FROM lab_requests 
                                         WHERE user_id=$uid AND lab_id={$lab['id']} 
                                         AND status='pending'")->fetch_assoc();
              ?>
                <div class="col-md-6">
                  <div class="card card-outline <?= $active_session ? 'card-warning' : 'card-primary' ?>">
                    <div class="card-header">
                      <h5 class="card-title"><?= htmlspecialchars($lab['course_name']) ?></h5>
                      <h6 class="card-subtitle"><i class="fas fa-flask"></i> <?= htmlspecialchars($lab['lab_name']) ?></h6>
                      <?php if ($active_session): ?>
                      <span class="badge badge-warning float-right">
                        <i class="fas fa-clock"></i> Already Active
                      </span>
                      <?php elseif ($pending_req): ?>
                      <span class="badge badge-info float-right">
                        <i class="fas fa-hourglass-half"></i> Pending
                      </span>
                      <?php endif; ?>
                    </div>
                    <div class="card-body">
                      <p><?= htmlspecialchars(substr($lab['lab_guide'] ?? $lab['guide_url'] ?? 'No description available', 0, 150)) ?>...</p>
                      
                      <?php if ($active_session): ?>
                        <div class="alert alert-info alert-sm mb-2">
                          <small><strong>Active Until:</strong> <?= date('M d, Y H:i', strtotime($active_session['access_expiry'])) ?></small>
                        </div>
                        <form method="POST" action="my_labs.php?extend=<?= $active_session['id'] ?>">
                          <div class="input-group input-group-sm">
                            <select name="extend_hours" class="form-control" required>
                              <option value="">-- Extend by --</option>
                              <option value="1">1 Hour</option>
                              <option value="2">2 Hours</option>
                              <option value="4">4 Hours</option>
                              <option value="8">8 Hours</option>
                              <option value="24">1 Day</option>
                            </select>
                            <div class="input-group-append">
                              <button type="submit" class="btn btn-info btn-sm">
                                <i class="fas fa-plus"></i> Extend
                              </button>
                            </div>
                          </div>
                        </form>
                      <?php elseif ($pending_req): ?>
                        <button class="btn btn-secondary btn-sm" disabled>
                          <i class="fas fa-hourglass-half"></i> Pending Approval
                        </button>
                      <?php else: ?>
                        <form method="POST">
                          <input type="hidden" name="lab_id" value="<?= $lab['id'] ?>">
                          <button type="submit" class="btn btn-primary btn-sm">
                            <i class="fas fa-envelope"></i> Request Access
                          </button>
                        </form>
                      <?php endif; ?>
                    </div>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </section>
</div>
<?php include 'includes/footer.php'; ?>
</div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
</body>
</html>
