<?php
session_start();
require_once 'auth.php';

$db = new mysqli("mysql", "monitor", "monitor123", "monitoring");
$uid = $_SESSION['uid'];

$result = $db->query("SELECT l.*, c.name as course_name FROM labs l JOIN courses c ON l.course_id = c.id");
$labs = $result->fetch_all(MYSQLI_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['lab_id'])) {
    $lab_id = (int)$_POST['lab_id'];
    $db->query("INSERT INTO lab_requests (user_id, lab_id, status, created_at) VALUES ($uid, $lab_id, 'pending', NOW())");
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
      <div class="card card-primary">
        <div class="card-header">
          <h3 class="card-title"><i class="fas fa-plus-circle"></i> Available Labs</h3>
        </div>
        <div class="card-body">
          <?php if (count($labs) === 0): ?>
            <p class="text-muted"><i class="fas fa-info-circle"></i> No labs available.</p>
          <?php else: ?>
            <div class="row">
              <?php foreach ($labs as $lab): ?>
                <div class="col-md-6">
                  <div class="card card-outline card-primary">
                    <div class="card-header">
                      <h5 class="card-title"><?= htmlspecialchars($lab['course_name']) ?></h5>
                      <h6 class="card-subtitle"><i class="fas fa-flask"></i> <?= htmlspecialchars($lab['lab_name']) ?></h6>
                    </div>
                    <div class="card-body">
                      <p><?= htmlspecialchars(substr($lab['description'], 0, 150)) ?>...</p>
                      <form method="POST">
                        <input type="hidden" name="lab_id" value="<?= $lab['id'] ?>">
                        <button type="submit" class="btn btn-primary btn-sm">
                          <i class="fas fa-envelope"></i> Request Access
                        </button>
                      </form>
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
