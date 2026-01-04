<?php
session_start();
if ($_SESSION['role'] != 'admin') die("Access denied");

$conn = new mysqli("mysql","monitor","monitor123","monitoring");
if ($conn->connect_error) die("DB connection failed");

/* ================= Defaults ================= */
$edit = false;
$server = [
  'id'=>'','hostname'=>'','ip_address'=>'','ssh_user'=>'','ssh_password'=>'','tag'=>'Generic'
];

/* ================= Edit Mode ================= */
if (isset($_GET['id'])) {
  $edit = true;
  $stmt = $conn->prepare("SELECT * FROM servers WHERE id=?");
  $stmt->bind_param("i", $_GET['id']);
  $stmt->execute();
  $server = $stmt->get_result()->fetch_assoc();
}

/* ================= Save ================= */
if (isset($_POST['save'])) {

  if (!empty($_POST['id'])) {
    $q = $conn->prepare("
      UPDATE servers
      SET hostname=?, ip_address=?, ssh_user=?, ssh_password=?, tag=?
      WHERE id=?
    ");
    $q->bind_param("sssssi",
      $_POST['hostname'], $_POST['ip'], $_POST['user'],
      $_POST['password'], $_POST['tag'], $_POST['id']
    );
  } else {
    $q = $conn->prepare("
      INSERT INTO servers (hostname, ip_address, ssh_user, ssh_password, tag)
      VALUES (?,?,?,?,?)
    ");
    $q->bind_param("sssss",
      $_POST['hostname'], $_POST['ip'],
      $_POST['user'], $_POST['password'], $_POST['tag']
    );
  }
  $q->execute();
  header("Location: ".$_SERVER['PHP_SELF']);
  exit;
}

/* ================= Delete ================= */
if (isset($_GET['delete'])) {
  $stmt = $conn->prepare("DELETE FROM servers WHERE id=?");
  $stmt->bind_param("i", $_GET['delete']);
  $stmt->execute();
  header("Location: ".$_SERVER['PHP_SELF']);
  exit;
}

/* ================= Server List ================= */
$servers = $conn->query("SELECT * FROM servers ORDER BY id DESC");
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Manage Servers</title>
<link rel="stylesheet" href="style.css">

<style>
.form-grid{display:grid;grid-template-columns:1fr 1fr;gap:20px}
.full{grid-column:1/-1}
.primary-btn{background:linear-gradient(135deg,#1d2671,#c33764);color:#fff;border:none;padding:12px;border-radius:8px}
.action-btn{margin-right:10px;cursor:pointer;font-weight:600}
.test{color:#0a7}
.delete{color:red}
.tag{padding:3px 8px;border-radius:6px;color:#fff;font-size:12px}
.Prod{background:#007bff}.DR{background:#dc3545}.SAP{background:#6f42c1}.Generic{background:#6c757d}
.password-box{position:relative}
.password-box span{position:absolute;right:10px;top:50%;transform:translateY(-50%);cursor:pointer}

/* Responsive */
@media(max-width:768px){
 .form-grid{grid-template-columns:1fr}
 .sidebar{display:none}
}
</style>
</head>

<body>

<div class="topbar">
 <div class="logo">Linux Monitoring Microservice</div>
 <div class="top-actions">👤 admin <a class="logout" href="logout.php">Logout</a></div>
</div>

<div class="layout">

<div class="sidebar">
 <a href="index.php">Dashboard</a>
 <a class="active" href="add_server.php">Add Server</a>
</div>

<div class="content">

<!-- FORM -->
<div class="card">
<h3><?= $edit?"Edit Server":"Add New Server" ?></h3>

<form method="post">
<input type="hidden" name="id" value="<?= $server['id'] ?>">

<div class="form-grid">
 <div>
  <label>Hostname</label>
  <input name="hostname" required value="<?= $server['hostname'] ?>">
 </div>

 <div>
  <label>IP Address</label>
  <input name="ip" required value="<?= $server['ip_address'] ?>">
 </div>

 <div>
  <label>SSH User</label>
  <input name="user" required value="<?= $server['ssh_user'] ?>">
 </div>

 <div>
  <label>SSH Password</label>
  <div class="password-box">
   <input id="pwd" type="password" name="password" required value="<?= $server['ssh_password'] ?>">
   <span onclick="togglePwd()">👁</span>
  </div>
 </div>

 <div>
  <label>Server Tag</label>
  <select name="tag">
   <?php foreach(["Prod","DR","SAP","Generic"] as $t): ?>
    <option <?= $server['tag']==$t?"selected":"" ?>><?= $t ?></option>
   <?php endforeach ?>
  </select>
 </div>

 <div class="full">
  <button class="primary-btn" name="save"><?= $edit?"Save Changes":"Add Server" ?></button>
 </div>
</div>
</form>
</div>

<!-- TABLE -->
<div class="card">
<h3>Configured Servers</h3>

<table class="modern-table">
<tr><th>Hostname</th><th>IP</th><th>User</th><th>Tag</th><th>Action</th></tr>

<?php while($r=$servers->fetch_assoc()): ?>
<tr>
<td><?= $r['hostname'] ?></td>
<td><?= $r['ip_address'] ?></td>
<td><?= $r['ssh_user'] ?></td>
<td><span class="tag <?= $r['tag'] ?>"><?= $r['tag'] ?></span></td>
<td>
 <span class="action-btn test" onclick="testConn('<?= $r['ip_address'] ?>')">Test</span>
 <a class="action-btn" href="?id=<?= $r['id'] ?>">Edit</a>
 <span class="action-btn delete" onclick="confirmDel(<?= $r['id'] ?>)">Delete</span>
</td>
</tr>
<?php endwhile ?>
</table>
</div>

</div>
</div>

<script>
function togglePwd(){
 let p=document.getElementById("pwd");
 p.type=p.type==="password"?"text":"password";
}

function confirmDel(id){
 if(confirm("Delete this server?")) location.href="?delete="+id;
}

function testConn(ip){
 alert("Testing SSH connection to "+ip+" (backend hook next)");
}
</script>

</body>
</html>

