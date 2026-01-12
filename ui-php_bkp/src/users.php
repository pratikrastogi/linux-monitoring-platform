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
?>
<!DOCTYPE html>
<html>
<head>
<title>Manage Users</title>
<link rel="stylesheet" href="assets/style.css">
<meta name="viewport" content="width=device-width, initial-scale=1">
</head>

<body>

<!-- ===== TOP BAR ===== -->
<div class="topbar">
  <div class="logo">🖥️ Pratik Rastogi LAB Linux Monitoring</div>
  <div class="top-actions">
    <span class="user">👤 <?= $_SESSION['user'] ?></span>
    <a href="logout.php" class="logout">Logout</a>
  </div>
</div>

<div class="layout">

<!-- ===== SIDEBAR ===== -->
<div class="sidebar">
  <a href="index.php">📊 Dashboard</a>
  <a href="charts.php">📈 Charts</a>
  <a href="alerts.php">🚨 Alerts</a>
  <hr>
  <a href="add_server.php">➕ Add Server</a>
  <a class="active" href="users.php">👥 Users</a>
</div>

<!-- ===== CONTENT ===== -->
<div class="content">

<!-- ADD USER -->
<div class="card">
  <h3>Add New User</h3>

  <form method="post">
    <label>Username</label>
    <input name="username" required>

    <label>Password</label>
    <div style="position:relative">
      <input id="pwd" type="password" name="password" required>
      <span onclick="togglePwd()"
            style="position:absolute;right:10px;top:12px;cursor:pointer">👁</span>
    </div>

    <label>Role</label>
    <select name="role">
      <option value="user">User</option>
      <option value="admin">Admin</option>
    </select>

    <button name="add_user">Add User</button>
  </form>
</div>

<!-- USER LIST -->
<div class="card">
  <h3>Existing Users</h3>

  <table class="modern-table">
    <tr>
      <th>Username</th>
      <th>Role</th>
      <th>Action</th>
    </tr>

    <?php while($u = $users->fetch_assoc()): ?>
    <tr>
      <td><?= htmlspecialchars($u['username']) ?></td>
      <td><?= htmlspecialchars($u['role']) ?></td>
      <td>
        <?php if ($u['username'] != $_SESSION['user']) { ?>
          <a class="del"
             href="?delete=<?= urlencode($u['username']) ?>"
             onclick="return confirm('Delete this user?')">
             Delete
          </a>
        <?php } else { ?>
          <span style="color:#999">Current</span>
        <?php } ?>
      </td>
    </tr>
    <?php endwhile; ?>
  </table>
</div>

</div>
</div>

<script>
function togglePwd(){
 let p=document.getElementById("pwd");
 p.type = p.type === "password" ? "text" : "password";
}
</script>

</body>
</html>

