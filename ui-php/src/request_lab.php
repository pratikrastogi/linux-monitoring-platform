<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$page_title = "Request Lab Access";
$uid = $_SESSION['uid'];
$conn = new mysqli("mysql","monitor","monitor123","monitoring");
if ($conn->connect_error) die("DB Error");

$lab_id = $_GET['lab'] ?? null;
if (!$lab_id) {
    header("Location: browse_labs.php");
    exit;
}

// Get lab details
$lab_q = $conn->query("SELECT lt.*, c.title as course_title, c.id as course_id
                       FROM lab_templates lt
                       JOIN courses c ON lt.course_id = c.id
                       WHERE lt.id = $lab_id");
if ($lab_q->num_rows === 0) {
    header("Location: browse_labs.php");
    exit;
}
$lab = $lab_q->fetch_assoc();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $justification = $conn->real_escape_string($_POST['justification']);
    
    // Check if already has pending request
    $existing = $conn->query("SELECT id FROM lab_requests WHERE user_id=$uid AND lab_template_id=$lab_id AND status='pending'");
    if ($existing->num_rows > 0) {
        $_SESSION['error'] = "You already have a pending request for this lab.";
    } else {
        $conn->query("INSERT INTO lab_requests (user_id, lab_template_id, justification, status, created_at)
                      VALUES ($uid, $lab_id, '$justification', 'pending', NOW())");
        $_SESSION['success'] = "Lab access request submitted! You'll be notified when approved.";
        header("Location: browse_labs.php");
        exit;
    }
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
          <h1 class="m-0"><i class="fas fa-file-alt"></i> Request Lab Access</h1>
        </div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
            <li class="breadcrumb-item"><a href="browse_labs.php">Browse Labs</a></li>
            <li class="breadcrumb-item active">Request Access</li>
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
        <?= $_SESSION['error']; unset($_SESSION['error']); ?>
      </div>
      <?php endif; ?>

      <div class="row">
        <div class="col-md-8">
          <!-- Request Form -->
          <div class="card card-primary">
            <div class="card-header">
              <h3 class="card-title"><i class="fas fa-clipboard-list"></i> Lab Access Request</h3>
            </div>
            <form method="POST">
              <div class="card-body">
                
                <div class="alert alert-info">
                  <h5><i class="icon fas fa-info-circle"></i> Lab Details</h5>
                  <strong><?= htmlspecialchars($lab['title']) ?></strong><br>
                  Course: <?= htmlspecialchars($lab['course_title']) ?><br>
                  Duration: <?= $lab['duration_minutes'] ?> minutes<br>
                  Difficulty: <span class="badge badge-secondary"><?= $lab['difficulty'] ?></span>
                </div>

                <div class="form-group">
                  <label>Why do you want to access this lab? <span class="text-danger">*</span></label>
                  <textarea name="justification" class="form-control" rows="5" required 
                            placeholder="Please provide a brief explanation of your learning goals..."></textarea>
                  <small class="text-muted">This helps administrators understand your learning needs.</small>
                </div>

                <div class="callout callout-warning">
                  <h5>Important Notes:</h5>
                  <ul class="mb-0">
                    <li>Your request will be reviewed by an administrator</li>
                    <li>You'll receive a notification when approved</li>
                    <li>Lab sessions have a time limit of <?= $lab['duration_minutes'] ?> minutes</li>
                    <li>Save your work regularly - sessions expire automatically</li>
                  </ul>
                </div>

              </div>

              <div class="card-footer">
                <button type="submit" class="btn btn-primary">
                  <i class="fas fa-paper-plane"></i> Submit Request
                </button>
                <a href="browse_labs.php?course=<?= $lab['course_id'] ?>" class="btn btn-default">
                  <i class="fas fa-times"></i> Cancel
                </a>
              </div>
            </form>
          </div>
        </div>

        <div class="col-md-4">
          <!-- Lab Guide Preview -->
          <div class="card">
            <div class="card-header">
              <h3 class="card-title"><i class="fas fa-book-open"></i> Lab Guide Preview</h3>
            </div>
            <div class="card-body" style="max-height: 400px; overflow-y: auto;">
              <div id="guide-preview"></div>
            </div>
          </div>

          <!-- Prerequisites -->
          <div class="card card-info">
            <div class="card-header">
              <h3 class="card-title"><i class="fas fa-list-check"></i> Prerequisites</h3>
            </div>
            <div class="card-body">
              <p>Before starting this lab, you should have:</p>
              <ul>
                <li>Basic Linux command line knowledge</li>
                <li>Understanding of containers (for Kubernetes labs)</li>
                <li>Completed <?= htmlspecialchars($lab['course_title']) ?> basics</li>
              </ul>
            </div>
          </div>
        </div>
      </div>

    </div>
  </section>
</div>

<?php include 'includes/footer.php'; ?>

</div>

<!-- Markdown Parser -->
<script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
<script>
// Render lab guide preview
const guideContent = <?= json_encode($lab['lab_guide_content']) ?>;
document.getElementById('guide-preview').innerHTML = marked.parse(guideContent);
</script>

</body>
</html>
