<?php
/**
 * MY LABS PAGE
 * Purpose: User dashboard for browsing and accessing labs
 * Role: user only
 * Backward Compatible: NEW page, does not modify existing functionality
 */

session_start();

if (!isset($_SESSION['user'], $_SESSION['uid'])) {
    header("Location: login.php");
    exit;
}

$conn = new mysqli("mysql","monitor","monitor123","monitoring");
if ($conn->connect_error) die("DB error");

$user = $_SESSION['user'];
$uid = (int)$_SESSION['uid'];
$role = $_SESSION['role'];
$page_title = "My Labs";

// Only users can access (admins use different interface)
if ($role !== 'user') {
    header("Location: labs.php");
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
          <h1 class="m-0"><i class="fas fa-graduation-cap"></i> My Labs</h1>
        </div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
            <li class="breadcrumb-item active">My Labs</li>
          </ol>
        </div>
      </div>
    </div>
  </div>

  <section class="content">
    <div class="container-fluid">

      <!-- Active Session Alert -->
      <?php
      $stmt = $conn->prepare("
          SELECT ls.*, lt.title as lab_title 
          FROM lab_sessions ls
          LEFT JOIN lab_templates lt ON ls.lab_template_id = lt.id
          WHERE ls.user_id = ? AND ls.status = 'ACTIVE'
          ORDER BY ls.id DESC LIMIT 1
      ");
      $stmt->bind_param("i", $uid);
      $stmt->execute();
      $active = $stmt->get_result()->fetch_assoc();
      
      if ($active):
      ?>
      <div class="alert alert-success alert-dismissible">
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        <h5><i class="icon fas fa-check"></i> You have an active lab!</h5>
        <strong><?= htmlspecialchars($active['lab_title'] ?? 'Docker & Kubernetes Lab') ?></strong><br>
        Expires: <?= date('d M Y, h:i A', strtotime($active['access_expiry'])) ?>
        <a href="terminal.php" class="btn btn-sm btn-light ml-3">
          <i class="fas fa-terminal"></i> Open Terminal
        </a>
      </div>
      <?php endif; ?>

      <!-- My Progress -->
      <div class="row">
        <div class="col-md-12">
          <div class="card">
            <div class="card-header" style="background: linear-gradient(135deg, #667eea, #764ba2); color: white;">
              <h3 class="card-title"><i class="fas fa-chart-line"></i> My Learning Progress</h3>
            </div>
            <div class="card-body">
              <?php
              $stmt = $conn->prepare("
                  SELECT 
                      lt.title,
                      lt.difficulty,
                      lp.status,
                      lp.progress_percent,
                      lp.updated_at
                  FROM lab_progress lp
                  JOIN lab_templates lt ON lp.lab_template_id = lt.id
                  WHERE lp.user_id = ?
                  ORDER BY lp.updated_at DESC
              ");
              $stmt->bind_param("i", $uid);
              $stmt->execute();
              $my_progress = $stmt->get_result();
              
              if ($my_progress->num_rows > 0):
              ?>
              <table class="table table-hover">
                <thead>
                  <tr>
                    <th>Lab</th>
                    <th>Difficulty</th>
                    <th>Progress</th>
                    <th>Status</th>
                    <th>Last Updated</th>
                  </tr>
                </thead>
                <tbody>
                  <?php while ($prog = $my_progress->fetch_assoc()): ?>
                  <tr>
                    <td><?= htmlspecialchars($prog['title']) ?></td>
                    <td>
                      <span class="badge badge-<?= $prog['difficulty'] === 'beginner' ? 'success' : ($prog['difficulty'] === 'intermediate' ? 'warning' : 'danger') ?>">
                        <?= ucfirst($prog['difficulty']) ?>
                      </span>
                    </td>
                    <td>
                      <div class="progress" style="height: 20px;">
                        <div class="progress-bar" role="progressbar" 
                             style="width: <?= $prog['progress_percent'] ?>%; background: linear-gradient(135deg, #667eea, #764ba2);">
                          <?= $prog['progress_percent'] ?>%
                        </div>
                      </div>
                    </td>
                    <td>
                      <?php if ($prog['status'] === 'completed'): ?>
                        <span class="badge badge-success"><i class="fas fa-check"></i> Completed</span>
                      <?php elseif ($prog['status'] === 'in_progress'): ?>
                        <span class="badge badge-info"><i class="fas fa-spinner"></i> In Progress</span>
                      <?php else: ?>
                        <span class="badge badge-secondary">Not Started</span>
                      <?php endif; ?>
                    </td>
                    <td><?= date('d M Y', strtotime($prog['updated_at'])) ?></td>
                  </tr>
                  <?php endwhile; ?>
                </tbody>
              </table>
              <?php else: ?>
              <div class="text-center text-muted py-5">
                <i class="fas fa-book-open fa-4x mb-3" style="opacity: 0.2;"></i>
                <p>You haven't started any labs yet. Browse available courses below!</p>
              </div>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>

      <!-- Available Courses -->
      <div class="row mt-3">
        <div class="col-12">
          <div class="card">
            <div class="card-header bg-info">
              <h3 class="card-title"><i class="fas fa-book"></i> Available Courses</h3>
            </div>
            <div class="card-body">
              <div class="row">
                <?php
                $courses = $conn->query("
                    SELECT c.*, COUNT(lt.id) as lab_count
                    FROM courses c
                    LEFT JOIN lab_templates lt ON c.id = lt.course_id AND lt.status='published'
                    WHERE c.status = 'published'
                    GROUP BY c.id
                    ORDER BY c.created_at DESC
                ");
                
                if ($courses && $courses->num_rows > 0):
                    while ($course = $courses->fetch_assoc()):
                ?>
                <div class="col-md-4 mb-3">
                  <div class="card h-100">
                    <div class="card-header text-center" style="background: <?= $course['color'] ?>; color: white;">
                      <i class="fas <?= $course['icon'] ?> fa-3x"></i>
                    </div>
                    <div class="card-body">
                      <h5><?= htmlspecialchars($course['title']) ?></h5>
                      <p class="text-muted small"><?= htmlspecialchars($course['description']) ?></p>
                      <div class="mb-2">
                        <span class="badge badge-info"><?= ucfirst($course['difficulty']) ?></span>
                        <span class="badge badge-secondary"><?= $course['duration_hours'] ?>h</span>
                        <span class="badge badge-primary"><?= $course['lab_count'] ?> Labs</span>
                      </div>
                    </div>
                    <div class="card-footer">
                      <a href="lab_request.php?course_id=<?= $course['id'] ?>" class="btn btn-primary btn-block">
                        <i class="fas fa-play"></i> Request Access
                      </a>
                    </div>
                  </div>
                </div>
                <?php 
                    endwhile;
                else:
                ?>
                <div class="col-12 text-center text-muted py-5">
                  <i class="fas fa-inbox fa-4x mb-3" style="opacity: 0.2;"></i>
                  <p>No courses available yet. Check back soon!</p>
                </div>
                <?php endif; ?>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Pending Requests -->
      <?php
      $stmt = $conn->prepare("
          SELECT lr.*, lt.title as lab_title
          FROM lab_requests lr
          LEFT JOIN lab_templates lt ON lr.lab_template_id = lt.id
          WHERE lr.user_id = ? AND lr.status = 'pending'
          ORDER BY lr.created_at DESC
      ");
      $stmt->bind_param("i", $uid);
      $stmt->execute();
      $pending_requests = $stmt->get_result();
      
      if ($pending_requests->num_rows > 0):
      ?>
      <div class="row mt-3">
        <div class="col-12">
          <div class="card">
            <div class="card-header bg-warning">
              <h3 class="card-title"><i class="fas fa-clock"></i> Pending Lab Requests</h3>
            </div>
            <div class="card-body">
              <table class="table">
                <thead>
                  <tr>
                    <th>Lab</th>
                    <th>Requested</th>
                    <th>Duration</th>
                    <th>Status</th>
                  </tr>
                </thead>
                <tbody>
                  <?php while ($req = $pending_requests->fetch_assoc()): ?>
                  <tr>
                    <td><?= htmlspecialchars($req['lab_title'] ?? 'General Lab Access') ?></td>
                    <td><?= date('d M Y, h:i A', strtotime($req['created_at'])) ?></td>
                    <td><?= $req['requested_hours'] ?> hour(s)</td>
                    <td><span class="badge badge-warning">Pending Approval</span></td>
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

<?php include 'includes/footer.php'; ?>
