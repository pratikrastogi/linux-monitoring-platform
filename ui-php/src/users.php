<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['role'] != 'admin') {
  header("Location: login.php");
  exit;
}

$conn = new mysqli("mysql","monitor","monitor123","monitoring");
if ($conn->connect_error) die("DB Error");

/* ---------- ADD USER ---------- */
if (isset($_POST['add_user'])) {
  $user = $_POST['username'];
  $pass = hash("sha256", $_POST['password']);
  $role = $_POST['role'];

  $stmt = $conn->prepare(
    "INSERT INTO users (username,password,role) VALUES (?,?,?)"
  );
  $stmt->bind_param("sss", $user, $pass, $role);
  $stmt->execute();

  header("Location: users.php");
  exit;
}

/* ---------- DELETE USER ---------- */
if (isset($_GET['delete'])) {
  $d = $conn->prepare("DELETE FROM users WHERE username=?");
  $d->bind_param("s", $_GET['delete']);
  $d->execute();

  header("Location: users.php");
  exit;
}

/* ---------- USER LIST ---------- */
$users = $conn->query("SELECT username, role FROM users ORDER BY username");
$page_title = "User Management";
include 'includes/header.php';
?>

<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">

<?php include 'includes/navbar.php'; ?>
<?php include 'includes/sidebar.php'; ?>

<!-- Content Wrapper -->
<div class="content-wrapper">
  <!-- Content Header -->
  <div class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6">
          <h1 class="m-0"><i class="fas fa-users"></i> User Management</h1>
        </div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
            <li class="breadcrumb-item active">Users</li>
          </ol>
        </div>
      </div>
    </div>
  </div>

  <!-- Main content -->
  <section class="content">
    <div class="container-fluid">

      <!-- Add User Form -->
      <div class="row">
        <div class="col-md-6">
          <div class="card">
            <div class="card-header">
              <h3 class="card-title"><i class="fas fa-user-plus"></i> Add New User</h3>
            </div>
            <div class="card-body">
              <form method="post">
                <div class="form-group">
                  <label>Username</label>
                  <input type="text" name="username" class="form-control" required>
                </div>
                <div class="form-group">
                  <label>Password</label>
                  <div class="input-group">
                    <input id="pwd" type="password" name="password" class="form-control" required>
                    <div class="input-group-append">
                      <button type="button" class="btn btn-outline-secondary" onclick="togglePwd()">
                        <i class="fas fa-eye"></i>
                      </button>
                    </div>
                  </div>
                </div>
                <div class="form-group">
                  <label>Role</label>
                  <select name="role" class="form-control">
                    <option value="user">User</option>
                    <option value="admin">Admin</option>
                  </select>
                </div>
                <button type="submit" name="add_user" class="btn btn-primary"><i class="fas fa-plus"></i> Add User</button>
              </form>
            </div>
          </div>
        </div>
      </div>

      <!-- User List -->
      <div class="row">
        <div class="col-12">
          <div class="card">
            <div class="card-header">
              <h3 class="card-title"><i class="fas fa-list"></i> Existing Users</h3>
            </div>
            <div class="card-body">
              <table class="table table-bordered table-striped">
                <thead>
                  <tr>
                    <th>Username</th>
                    <th>Role</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody>
                  <?php while($u = $users->fetch_assoc()): ?>
                  <tr>
                    <td><?= htmlspecialchars($u['username']) ?></td>
                    <td><span class="badge badge-<?= $u['role'] == 'admin' ? 'danger' : 'info' ?>"><?= htmlspecialchars($u['role']) ?></span></td>
                    <td>
                      <?php if ($u['username'] != $_SESSION['user']) { ?>
                        <a class="btn btn-sm btn-danger"
                           href="?delete=<?= urlencode($u['username']) ?>"
                           onclick="return confirm('Delete this user?')">
                           <i class="fas fa-trash"></i> Delete
                        </a>
                      <?php } else { ?>
                        <span class="badge badge-secondary">Current User</span>
                      <?php } ?>
                    </td>
                  </tr>
                  <?php endwhile; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>

    </div>
  </section>
</div>

<?php include 'includes/footer.php'; ?>

<script>
function togglePwd(){
 let p=document.getElementById("pwd");
 p.type = p.type === "password" ? "text" : "password";
}
</script>

</body>
</html>

