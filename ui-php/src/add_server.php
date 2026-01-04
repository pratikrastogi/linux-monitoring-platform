<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['role'] != 'admin') {
  header("Location: login.php");
  exit;
}

$conn = new mysqli("mysql","monitor","monitor123","monitoring");
if ($conn->connect_error) die("DB Error");

/* ---------- EDIT MODE ---------- */
$edit = false;
$server = ['id'=>'','hostname'=>'','ip_address'=>'','ssh_user'=>'','ssh_password'=>''];

if (isset($_GET['id'])) {
  $edit = true;
  $st = $conn->prepare("SELECT * FROM servers WHERE id=?");
  $st->bind_param("i", $_GET['id']);
  $st->execute();
  $server = $st->get_result()->fetch_assoc();
}

/* ---------- SAVE ---------- */
if (isset($_POST['save'])) {

  if (!empty($_POST['id'])) {
    $q = $conn->prepare("
      UPDATE servers SET hostname=?, ip_address=?, ssh_user=?, ssh_password=?
      WHERE id=?
    ");
    $q->bind_param("ssssi",
      $_POST['hostname'], $_POST['ip'],
      $_POST['user'], $_POST['password'], $_POST['id']
    );
  } else {
    $q = $conn->prepare("
      INSERT INTO servers (hostname, ip_address, ssh_user, ssh_password)
      VALUES (?,?,?,?)
    ");
    $q->bind_param("ssss",
      $_POST['hostname'], $_POST['ip'],
      $_POST['user'], $_POST['password']
    );
  }

  $q->execute();
  header("Location: add_server.php");
  exit;
}

/* ---------- DELETE ---------- */
if (isset($_GET['delete'])) {
  $d = $conn->prepare("DELETE FROM servers WHERE id=?");
  $d->bind_param("i", $_GET['delete']);
  $d->execute();
  header("Location: add_server.php");
  exit;
}

/* ---------- AJAX SSH TEST (SIMULATED) ---------- */
if (isset($_GET['test_ssh'])) {
  // Temporary logic: IP reachable → PASS, else FAIL
  $ip = $_GET['test_ssh'];
  $status = (exec("ping -c 1 -W 1 $ip >/dev/null 2>&1 && echo OK") == "OK")
            ? "PASS" : "FAIL";
  echo json_encode(["result"=>$status]);
  exit;
}

/* ---------- SERVER LIST ---------- */
$list = $conn->query("SELECT * FROM servers ORDER BY id DESC");
?>
<!DOCTYPE html>
<html>
<head>
<title>Manage Servers</title>
<link rel="stylesheet" href="assets/style.css">
<meta name="viewport" content="width=device-width, initial-scale=1">
</head>

<body>

<!-- ===== TOP BAR ===== -->
<div class="topbar">
  <div class="logo">🖥️ Vibhor Rastogi LAB Linux Monitoring</div>
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
  <a class="active" href="add_server.php">➕ Add Server</a>
  <a href="users.php">👥 Users</a>
</div>

<!-- ===== CONTENT ===== -->
<div class="content">

<!-- ADD / EDIT FORM -->
<div class="card">
  <h3><?= $edit ? "Edit Server" : "Add New Server" ?></h3>

  <form method="post">
    <input type="hidden" name="id" value="<?= $server['id'] ?>">

    <label>Hostname</label>
    <input name="hostname" required value="<?= htmlspecialchars($server['hostname']) ?>">

    <label>IP Address</label>
    <input name="ip" required value="<?= htmlspecialchars($server['ip_address']) ?>">

    <label>SSH Username</label>
    <input name="user" required value="<?= htmlspecialchars($server['ssh_user']) ?>">

    <label>SSH Password</label>
    <div style="position:relative">
      <input id="pwd" type="password" name="password" required
             value="<?= htmlspecialchars($server['ssh_password']) ?>">
      <span onclick="togglePwd()" style="position:absolute;right:10px;top:12px;cursor:pointer">👁</span>
    </div>

    <button name="save"><?= $edit ? "Save Changes" : "Add Server" ?></button>
  </form>
</div>

<!-- SERVER LIST -->
<div class="card">
  <h3>Configured Servers</h3>

  <table class="modern-table">
    <tr>
      <th>Hostname</th>
      <th>IP</th>
      <th>User</th>
      <th>SSH Test</th>
      <th>Action</th>
    </tr>

    <?php while($r=$list->fetch_assoc()): ?>
    <tr>
      <td><?= $r['hostname'] ?></td>
      <td><?= $r['ip_address'] ?></td>
      <td><?= $r['ssh_user'] ?></td>
      <td id="test<?= $r['id'] ?>">-</td>
      <td>
        <a href="?id=<?= $r['id'] ?>">Edit</a> |
        <a href="#" onclick="testSSH(<?= $r['id'] ?>,'<?= $r['ip_address'] ?>')">Test</a> |
        <a class="del" href="?delete=<?= $r['id'] ?>"
           onclick="return confirm('Delete this server?')">Delete</a>
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
 p.type = p.type==="password" ? "text" : "password";
}

function testSSH(id, ip){
 let cell = document.getElementById("test"+id);
 cell.innerHTML = "⏳ Testing...";
 fetch("add_server.php?test_ssh="+ip)
 .then(r=>r.json())
 .then(d=>{
   cell.innerHTML = d.result === "PASS"
     ? "<span class='ok'>PASS</span>"
     : "<span class='bad'>FAIL</span>";
 });
}
</script>

</body>
</html>

