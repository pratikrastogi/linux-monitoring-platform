<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$page_title = "My Profile";
$uid = $_SESSION['uid'];
$conn = new mysqli("mysql","monitor","monitor123","monitoring");
if ($conn->connect_error) die("DB Error");

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $name = $conn->real_escape_string($_POST['name']);
    $email = $conn->real_escape_string($_POST['email']);
    
    $conn->query("UPDATE users SET name='$name', email='$email' WHERE id=$uid");
    $_SESSION['success'] = "Profile updated successfully!";
    header("Location: profile.php");
    exit;
}

// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $current_pw = $_POST['current_password'];
    $new_pw = $_POST['new_password'];
    $confirm_pw = $_POST['confirm_password'];
    
    $user_q = $conn->query("SELECT password FROM users WHERE id=$uid");
    $user = $user_q->fetch_assoc();
    
    if (password_verify($current_pw, $user['password'])) {
        if ($new_pw === $confirm_pw) {
            $hashed = password_hash($new_pw, PASSWORD_DEFAULT);
            $conn->query("UPDATE users SET password='$hashed' WHERE id=$uid");
            $_SESSION['success'] = "Password changed successfully!";
        } else {
            $_SESSION['error'] = "New passwords do not match!";
        }
    } else {
        $_SESSION['error'] = "Current password is incorrect!";
    }
    header("Location: profile.php");
    exit;
}

// Get user details
$user_q = $conn->query("SELECT * FROM users WHERE id=$uid");
$user = $user_q->fetch_assoc();

// Get stats
$completed_q = $conn->query("SELECT COUNT(*) as cnt FROM lab_sessions WHERE user_id=$uid AND status='ACTIVE' AND access_expiry > NOW()");
$completed = $completed_q->fetch_assoc()['cnt'];

$courses_q = $conn->query("SELECT COUNT(DISTINCT l.course_id) as cnt FROM lab_sessions ls JOIN labs l ON ls.lab_id=l.id WHERE ls.user_id=$uid");
$courses_enrolled = $courses_q->fetch_assoc()['cnt'];

include 'includes/header.php';
?>

<body class="hold-transition sidebar-mini">
<div class="wrapper">

<?php include 'includes/admin_sidebar.php'; ?>
<?php include 'includes/admin_topbar.php'; ?>

<div class="content-wrapper app-shell">
  <div class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6">
          <h1 class="m-0"><i class="fas fa-user"></i> My Profile</h1>
        </div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
            <li class="breadcrumb-item active">Profile</li>
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

      <?php if (isset($_SESSION['error'])): ?>
      <div class="alert alert-danger alert-dismissible">
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        <?= $_SESSION['error']; unset($_SESSION['error']); ?>
      </div>
      <?php endif; ?>

      <div class="row">
        <div class="col-md-4">
          <!-- Profile Card -->
          <div class="card card-primary card-outline">
            <div class="card-body box-profile">
              <div class="text-center">
                <div class="profile-user-img img-circle" style="width:100px; height:100px; margin:auto; background: linear-gradient(135deg, #667eea, #764ba2); display:flex; align-items:center; justify-content:center; color:white; font-size:48px; font-weight:bold;">
                  <?= strtoupper(substr($user['name'] ?? $user['email'], 0, 1)) ?>
                </div>
              </div>

              <h3 class="profile-username text-center"><?= htmlspecialchars($user['name'] ?? 'User') ?></h3>
              <p class="text-muted text-center"><?= htmlspecialchars($user['email']) ?></p>

              <ul class="list-group list-group-unbordered mb-3">
                <li class="list-group-item">
                  <b>Labs Completed</b> <a class="float-right"><?= $completed ?></a>
                </li>
                <li class="list-group-item">
                  <b>Courses Enrolled</b> <a class="float-right"><?= $courses_enrolled ?></a>
                </li>
                <li class="list-group-item">
                  <b>Member Since</b> <a class="float-right"><?= date('M Y', strtotime($user['created_at'])) ?></a>
                </li>
              </ul>
            </div>
          </div>
        </div>

        <div class="col-md-8">
          <!-- Edit Profile -->
          <div class="card">
            <div class="card-header">
              <h3 class="card-title"><i class="fas fa-edit"></i> Edit Profile</h3>
            </div>
            <form method="POST">
              <div class="card-body">
                <div class="form-group">
                  <label>Full Name</label>
                  <input type="text" name="name" class="form-control" 
                         value="<?= htmlspecialchars($user['name'] ?? '') ?>">
                </div>

                <div class="form-group">
                  <label>Email</label>
                  <input type="email" name="email" class="form-control" 
                         value="<?= htmlspecialchars($user['email']) ?>" required>
                </div>
              </div>
              <div class="card-footer">
                <button type="submit" name="update_profile" class="btn btn-primary">
                  <i class="fas fa-save"></i> Update Profile
                </button>
              </div>
            </form>
          </div>

          <!-- Change Password -->
          <div class="card">
            <div class="card-header">
              <h3 class="card-title"><i class="fas fa-lock"></i> Change Password</h3>
            </div>
            <form method="POST">
              <div class="card-body">
                <div class="form-group">
                  <label>Current Password</label>
                  <input type="password" name="current_password" class="form-control" required>
                </div>

                <div class="form-group">
                  <label>New Password</label>
                  <input type="password" name="new_password" class="form-control" required>
                </div>

                <div class="form-group">
                  <label>Confirm New Password</label>
                  <input type="password" name="confirm_password" class="form-control" required>
                </div>
              </div>
              <div class="card-footer">
                <button type="submit" name="change_password" class="btn btn-warning">
                  <i class="fas fa-key"></i> Change Password
                </button>
              </div>
            </form>
          </div>

          <!-- Achievements -->
          <div class="card" id="achievements">
            <div class="card-header">
              <h3 class="card-title"><i class="fas fa-trophy"></i> Achievements</h3>
            </div>
            <div class="card-body">
              <div class="row">
                <?php if ($completed >= 1): ?>
                <div class="col-md-4 text-center mb-3">
                  <div style="font-size:48px; color:#FFD700;">🏅</div>
                  <strong>First Steps</strong><br>
                  <small>Completed first lab</small>
                </div>
                <?php endif; ?>

                <?php if ($completed >= 5): ?>
                <div class="col-md-4 text-center mb-3">
                  <div style="font-size:48px; color:#C0C0C0;">🥈</div>
                  <strong>Getting Started</strong><br>
                  <small>Completed 5 labs</small>
                </div>
                <?php endif; ?>

                <?php if ($completed >= 10): ?>
                <div class="col-md-4 text-center mb-3">
                  <div style="font-size:48px; color:#CD7F32;">🥇</div>
                  <strong>Lab Expert</strong><br>
                  <small>Completed 10 labs</small>
                </div>
                <?php endif; ?>

                <?php if ($completed < 1): ?>
                <div class="col-12 text-center text-muted">
                  <p>Complete labs to earn achievements!</p>
                </div>
                <?php endif; ?>
              </div>
            </div>
          </div>
        </div>
      </div>

    </div>
  </section>
</div>

<?php include 'includes/footer.php'; ?>

</div>
</body>
</html>
