<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$page_title = "Browse Labs";
$uid = $_SESSION['uid'];
$conn = new mysqli("mysql","monitor","monitor123","monitoring");
if ($conn->connect_error) die("DB Error");

// Get filter
$filter_course = $_GET['course'] ?? null;
$search = $_GET['search'] ?? '';

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
          <h1 class="m-0"><i class="fas fa-graduation-cap"></i> Browse Labs</h1>
        </div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
            <li class="breadcrumb-item active">Browse Labs</li>
          </ol>
        </div>
      </div>
    </div>
  </div>

  <section class="content">
    <div class="container-fluid">

      <!-- Search & Filter -->
      <div class="card">
        <div class="card-body">
          <form method="GET" class="form-inline">
            <div class="form-group mr-3">
              <label class="mr-2">Course:</label>
              <select name="course" class="form-control" onchange="this.form.submit()">
                <option value="">All Courses</option>
                <?php
                $courses = $conn->query("SELECT id, title FROM courses WHERE active=1 ORDER BY title");
                while($c = $courses->fetch_assoc()):
                ?>
                <option value="<?= $c['id'] ?>" <?= $filter_course == $c['id'] ? 'selected' : '' ?>>
                  <?= htmlspecialchars($c['title']) ?>
                </option>
                <?php endwhile; ?>
              </select>
            </div>

            <div class="form-group mr-3">
              <input type="text" name="search" class="form-control" placeholder="Search labs..." 
                     value="<?= htmlspecialchars($search) ?>">
            </div>

            <button type="submit" class="btn btn-primary">
              <i class="fas fa-search"></i> Search
            </button>

            <?php if ($filter_course || $search): ?>
            <a href="browse_labs.php" class="btn btn-default ml-2">
              <i class="fas fa-times"></i> Clear
            </a>
            <?php endif; ?>
          </form>
        </div>
      </div>

      <!-- Labs by Course -->
      <?php
      $sql = "SELECT DISTINCT c.* FROM courses c
              JOIN lab_templates lt ON c.id = lt.course_id
              WHERE c.active = 1";
      if ($filter_course) {
        $sql .= " AND c.id = $filter_course";
      }
      $sql .= " ORDER BY c.title";
      
      $courses_q = $conn->query($sql);
      while($course = $courses_q->fetch_assoc()):
      ?>
      
      <div class="card card-outline card-primary">
        <div class="card-header">
          <h3 class="card-title">
            <i class="fas fa-book"></i> <strong><?= htmlspecialchars($course['title']) ?></strong>
          </h3>
          <div class="card-tools">
            <span class="badge badge-info"><?= htmlspecialchars($course['category']) ?></span>
            <span class="badge badge-secondary"><?= htmlspecialchars($course['level']) ?></span>
          </div>
        </div>
        <div class="card-body">
          <p class="text-muted"><?= htmlspecialchars($course['description']) ?></p>

          <div class="row">
            <?php
            $labs_sql = "SELECT * FROM lab_templates WHERE course_id = {$course['id']} AND active = 1";
            if ($search) {
              $search_term = $conn->real_escape_string($search);
              $labs_sql .= " AND (title LIKE '%$search_term%' OR description LIKE '%$search_term%')";
            }
            $labs_sql .= " ORDER BY created_at ASC";
            
            $labs_q = $conn->query($labs_sql);
            while($lab = $labs_q->fetch_assoc()):
              // Check user progress
              $progress_q = $conn->query("SELECT * FROM lab_progress WHERE user_id=$uid AND lab_template_id={$lab['id']}");
              $progress = $progress_q->num_rows > 0 ? $progress_q->fetch_assoc() : null;
              
              // Check if request pending
              $request_q = $conn->query("SELECT * FROM lab_requests WHERE user_id=$uid AND lab_template_id={$lab['id']} AND status='pending'");
              $has_pending_request = $request_q->num_rows > 0;
              
              // Check if has active session
              $session_q = $conn->query("SELECT * FROM lab_sessions WHERE user_id=$uid AND lab_template_id={$lab['id']} AND status='ACTIVE'");
              $has_active_session = $session_q->num_rows > 0;
            ?>
            <div class="col-md-6 mb-3">
              <div class="card">
                <div class="card-body">
                  <h5 class="card-title"><?= htmlspecialchars($lab['title']) ?></h5>
                  <p class="card-text text-muted"><?= htmlspecialchars($lab['description']) ?></p>
                  
                  <div class="mb-2">
                    <?php
                    $diff_class = $lab['difficulty'] === 'Beginner' ? 'success' : ($lab['difficulty'] === 'Intermediate' ? 'warning' : 'danger');
                    ?>
                    <span class="badge badge-<?= $diff_class ?>"><?= $lab['difficulty'] ?></span>
                    <span class="badge badge-info"><i class="fas fa-clock"></i> <?= $lab['duration_minutes'] ?> min</span>
                  </div>

                  <?php if ($progress): ?>
                  <div class="progress mb-2" style="height: 20px;">
                    <div class="progress-bar <?= $progress['status'] === 'completed' ? 'bg-success' : 'bg-info' ?>" 
                         style="width: <?= $progress['progress_percent'] ?>%">
                      <?= $progress['progress_percent'] ?>%
                    </div>
                  </div>
                  <?php endif; ?>

                  <?php if ($has_active_session): ?>
                  <a href="terminal.php" class="btn btn-success btn-block">
                    <i class="fas fa-play-circle"></i> Continue Lab
                  </a>
                  <?php elseif ($has_pending_request): ?>
                  <button class="btn btn-warning btn-block" disabled>
                    <i class="fas fa-clock"></i> Request Pending Approval
                  </button>
                  <?php elseif ($progress && $progress['status'] === 'completed'): ?>
                  <a href="request_lab.php?lab=<?= $lab['id'] ?>" class="btn btn-primary btn-block">
                    <i class="fas fa-redo"></i> Retake Lab
                  </a>
                  <?php else: ?>
                  <a href="request_lab.php?lab=<?= $lab['id'] ?>" class="btn btn-primary btn-block">
                    <i class="fas fa-play"></i> Request Access
                  </a>
                  <?php endif; ?>
                </div>
              </div>
            </div>
            <?php endwhile; ?>
          </div>
        </div>
      </div>

      <?php endwhile; ?>

    </div>
  </section>
</div>

<?php include 'includes/footer.php'; ?>

</div>
</body>
</html>
