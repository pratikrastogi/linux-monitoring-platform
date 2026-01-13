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
    
    $conn->query("INSERT INTO support_cases (user_id, subject, description, category, status, last_response_by, last_response_at, created_at)
                  VALUES ($uid, '$subject', '$description', '$category', 'OPEN', 'USER', NOW(), NOW())");
    
    $case_id = $conn->insert_id;
    
    // Add initial message to history
    $conn->query("INSERT INTO support_case_messages (case_id, sender_type, sender_id, message, created_at)
                  VALUES ($case_id, 'USER', $uid, '$description', NOW())");
    
    $_SESSION['success'] = "Support case created successfully!";
    header("Location: support.php");
    exit;
}

// Handle adding message to existing case
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_message'])) {
    $case_id = (int)$_POST['case_id'];
    $message = $conn->real_escape_string($_POST['message']);
    
    // Verify case belongs to user
    $verify = $conn->query("SELECT id FROM support_cases WHERE id=$case_id AND user_id=$uid")->fetch_assoc();
    if ($verify) {
        $conn->query("INSERT INTO support_case_messages (case_id, sender_type, sender_id, message, created_at)
                      VALUES ($case_id, 'USER', $uid, '$message', NOW())");
        
        $conn->query("UPDATE support_cases 
                      SET last_response_by='USER', last_response_at=NOW(), status='OPEN'
                      WHERE id=$case_id");
        
        $_SESSION['success'] = "Message added to case #$case_id";
    }
    header("Location: support.php?view=$case_id");
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

$view_case_id = isset($_GET['view']) ? (int)$_GET['view'] : null;

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

      <?php if ($view_case_id): ?>
        <!-- Case Detail View -->
        <?php
        $case_q = $conn->query("SELECT sc.*, u.username, u.email 
                                FROM support_cases sc 
                                LEFT JOIN users u ON sc.user_id = u.id 
                                WHERE sc.id=$view_case_id AND sc.user_id=$uid");
        
        if (!$case_q) {
            echo '<div class="alert alert-danger">Database error: ' . htmlspecialchars($conn->error) . '</div>';
            $case = null;
        } else {
            $case = $case_q->fetch_assoc();
        }
        
        if (!$case) {
            echo '<div class="alert alert-danger">Case not found or access denied.</div>';
        } else {
            $category_class = $case['category'] === 'PAYMENT' ? 'success' : ($case['category'] === 'REFUND' ? 'danger' : 'info');
            $status_class = $case['status'] === 'OPEN' ? 'warning' : ($case['status'] === 'IN_PROGRESS' ? 'info' : ($case['status'] === 'RESOLVED' ? 'success' : 'secondary'));
            
            $pending_on = isset($case['last_response_by']) && $case['last_response_by'] === 'USER' ? 'Support Team' : 'You';
            $pending_class = isset($case['last_response_by']) && $case['last_response_by'] === 'USER' ? 'warning' : 'info';
        ?>
        
        <div class="row">
          <div class="col-md-12">
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">
                  <i class="fas fa-ticket-alt"></i> Case #<?= $case['id'] ?> - <?= htmlspecialchars($case['subject'] ?? '') ?>
                </h3>
                <div class="card-tools">
                  <a href="support.php" class="btn btn-sm btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to List
                  </a>
                </div>
              </div>
              <div class="card-body">
                <div class="row mb-3">
                  <div class="col-md-3">
                    <strong>Status:</strong> 
                    <span class="badge badge-<?= $status_class ?>">
                      <?= ucfirst(str_replace('_', ' ', $case['status'] ?? 'OPEN')) ?>
                    </span>
                  </div>
                  <div class="col-md-3">
                    <strong>Category:</strong> 
                    <span class="badge badge-<?= $category_class ?>"><?= $case['category'] ?></span>
                  </div>
                  <div class="col-md-3">
                    <strong>Created:</strong> <?= date('M d, Y H:i', strtotime($case['created_at'])) ?>
                  </div>
                  <div class="col-md-3">
                    <strong>Pending On:</strong> 
                    <span class="badge badge-<?= $pending_class ?>">
                      <i class="fas fa-<?= $case['last_response_by'] === 'USER' ? 'headset' : 'user' ?>"></i> <?= $pending_on ?>
                    </span>
                  </div>
                </div>
                
                <hr>
                
                <!-- Message History -->
                <div class="direct-chat-messages" style="height: 400px; overflow-y: auto;">
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
                    $is_user = $msg['sender_type'] === 'USER';
                    $align = $is_user ? 'right' : 'left';
                    $bg_class = $is_user ? 'bg-primary' : 'bg-secondary';
                  ?>
                  <div class="direct-chat-msg <?= $align ?>">
                    <div class="direct-chat-infos clearfix">
                      <span class="direct-chat-name float-<?= $align ?>">
                        <?= $is_user ? 'You' : 'Support Team' ?> (<?= htmlspecialchars($msg['sender_name'] ?? $msg['sender_email'] ?? 'Unknown') ?>)
                      </span>
                      <span class="direct-chat-timestamp float-<?= $align === 'right' ? 'left' : 'right' ?>">
                        <?= date('M d, Y H:i', strtotime($msg['created_at'])) ?>
                      </span>
                    </div>
                    <div class="direct-chat-text <?= $bg_class ?>" style="<?= $is_user ? 'margin-left: 50px;' : 'margin-right: 50px;' ?>">
                      <?= nl2br(htmlspecialchars($msg['message'] ?? '')) ?>
                    </div>
                  </div>
                  <?php endwhile; 
                  } // end if messages_q
                  ?>
                </div>
                
                <hr>
                
                <!-- Add New Message Form -->
                <?php if ($case['status'] !== 'RESOLVED' && $case['status'] !== 'REJECTED'): ?>
                <form method="POST" action="support.php">
                  <input type="hidden" name="case_id" value="<?= $case['id'] ?>">
                  <input type="hidden" name="add_message" value="1">
                  <div class="form-group">
                    <label><i class="fas fa-comment"></i> Add Message</label>
                    <textarea name="message" class="form-control" rows="3" placeholder="Type your message here..." required></textarea>
                  </div>
                  <button type="submit" class="btn btn-primary">
                    <i class="fas fa-paper-plane"></i> Send Message
                  </button>
                  <?php if ($case['status'] !== 'RESOLVED'): ?>
                  <a href="support.php?close=<?= $case['id'] ?>" class="btn btn-success" onclick="return confirm('Close this case?')">
                    <i class="fas fa-check-circle"></i> Close Case
                  </a>
                  <?php endif; ?>
                </form>
                <?php else: ?>
                <div class="alert alert-info">
                  <i class="fas fa-info-circle"></i> This case is closed. No further messages can be added.
                </div>
                <?php endif; ?>
              </div>
            </div>
          </div>
        </div>
        
        <?php } // end else case found ?>
        
      <?php else: ?>
        <!-- Case List View -->
        <div class="row">
          <div class="col-md-12">
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
                      <th>Pending On</th>
                      <th>Last Update</th>
                      <th>Created</th>
                      <th>Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php
                    $cases_q = $conn->query("SELECT sc.*,
                                            (SELECT COUNT(*) FROM support_case_messages WHERE case_id=sc.id) as message_count
                                            FROM support_cases sc 
                                            WHERE user_id=$uid 
                                            ORDER BY 
                                              CASE WHEN sc.status='OPEN' OR sc.status='IN_PROGRESS' THEN 1 ELSE 2 END,
                                              sc.last_response_at DESC,
                                              sc.created_at DESC");
                    while($case = $cases_q->fetch_assoc()):
                      $category_class = $case['category'] === 'PAYMENT' ? 'success' : ($case['category'] === 'REFUND' ? 'danger' : 'info');
                      $status_class = $case['status'] === 'OPEN' ? 'warning' : ($case['status'] === 'IN_PROGRESS' ? 'info' : ($case['status'] === 'RESOLVED' ? 'success' : 'secondary'));
                      
                      $pending_on = $case['last_response_by'] === 'USER' ? 'Support Team' : 'You';
                      $pending_class = $case['last_response_by'] === 'USER' ? 'warning' : 'info';
                    ?>
                    <tr>
                      <td><strong>#<?= $case['id'] ?></strong></td>
                      <td>
                        <strong><?= htmlspecialchars($case['subject'] ?? '') ?></strong>
                        <br><small class="text-muted"><?= $case['message_count'] ?> messages</small>
                      </td>
                      <td><span class="badge badge-<?= $category_class ?>"><?= $case['category'] ?></span></td>
                      <td><span class="badge badge-<?= $status_class ?>"><?= ucfirst(str_replace('_', ' ', $case['status'] ?? 'OPEN')) ?></span></td>
                      <td>
                        <span class="badge badge-<?= $pending_class ?>">
                          <i class="fas fa-<?= $case['last_response_by'] === 'USER' ? 'headset' : 'user' ?>"></i> <?= $pending_on ?>
                        </span>
                      </td>
                      <td><?= $case['last_response_at'] ? date('M d, Y H:i', strtotime($case['last_response_at'])) : '-' ?></td>
                      <td><?= date('M d, Y', strtotime($case['created_at'])) ?></td>
                      <td>
                        <a href="support.php?view=<?= $case['id'] ?>" class="btn btn-xs btn-primary">
                          <i class="fas fa-eye"></i> View
                        </a>
                      </td>
                    </tr>
                    <?php endwhile; ?>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      <?php endif; ?>

    </div>
  </section>
</div>

<!-- New Case Modal -->
<div class="modal fade" id="newCaseModal">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-primary">
        <h4 class="modal-title"><i class="fas fa-plus-circle"></i> Create New Support Case</h4>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
      </div>
      <form method="POST" action="support.php">
        <input type="hidden" name="create_case" value="1">
        <div class="modal-body">
          <div class="form-group">
            <label>Category <span class="text-danger">*</span></label>
            <select name="category" class="form-control" required>
              <option value="">-- Select Category --</option>
              <option value="TECHNICAL">Technical Issue</option>
              <option value="BILLING">Billing / Payment</option>
              <option value="ACCOUNT">Account Access</option>
              <option value="FEATURE">Feature Request</option>
              <option value="OTHER">Other</option>
            </select>
          </div>
          
          <div class="form-group">
            <label>Subject <span class="text-danger">*</span></label>
            <input type="text" name="subject" class="form-control" placeholder="Brief description of issue" required maxlength="200">
          </div>
          
          <div class="form-group">
            <label>Description <span class="text-danger">*</span></label>
            <textarea name="description" class="form-control" rows="6" placeholder="Detailed description of your issue or request..." required></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane"></i> Submit Case</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php include 'includes/footer.php'; ?>

</div>
</body>
</html>
