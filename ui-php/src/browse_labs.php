<?php
session_start();
require_once 'auth.php';

$db = new mysqli("mysql", "monitor", "monitor123", "monitoring");

// Get search/filter
$course_filter = $_GET['course'] ?? '';
$where = $course_filter ? "AND c.id = $course_filter" : '';

$labs = $db->query("SELECT c.*, 
    (SELECT COUNT(*) FROM labs WHERE course_id = c.id AND active=1) as lab_count
    FROM courses c WHERE c.active=1 $where ORDER BY c.name");

$page_title = "Browse Labs";
include 'includes/header.php';
?>

<body class="hold-transition sidebar-mini layout-fixed sidebar-collapse">
<div class="wrapper">

<?php include 'includes/admin_sidebar.php'; ?>
<?php include 'includes/admin_topbar.php'; ?>
    
<div class="content-wrapper app-shell">
  <div class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6">
          <h1 class="m-0"><i class="fas fa-search"></i> Browse Labs</h1>
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
      <?php while ($course = $labs->fetch_assoc()): ?>
        <div class="card">
          <div class="card-header">
            <h3 class="card-title"><i class="fas fa-book"></i> <?= htmlspecialchars($course['name'] ?? 'N/A') ?></h3>
          </div>
          <div class="card-body">
            <p class="text-muted"><?= htmlspecialchars($course['description'] ?? 'No description available') ?></p>
            <div class="row">
              <?php
              $course_labs = $db->query("SELECT * FROM labs WHERE course_id={$course['id']} AND active=1");
              while ($lab = $course_labs->fetch_assoc()):
              ?>
                <div class="col-md-6 col-lg-4">
                  <div class="card bg-light">
                    <div class="card-body">
                      <h5 class="card-title"><i class="fas fa-flask"></i> <?= htmlspecialchars($lab['lab_name']) ?></h5>
                      <p class="card-text"><i class="fas fa-clock"></i> Duration: <?= $lab['duration_minutes'] ?> minutes</p>
                      <p class="card-text"><i class="fas fa-users"></i> Max Users: <?= $lab['max_concurrent_users'] ?></p>
                      <a href="request_lab.php?lab_id=<?= $lab['id'] ?>" class="btn btn-primary btn-sm">
                        <i class="fas fa-play"></i> Request Access
                      </a>
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
