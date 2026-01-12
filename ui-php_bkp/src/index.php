<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$role = $_SESSION['role'];
$username = $_SESSION['user'];

$conn = new mysqli("mysql","monitor","monitor123","monitoring");
if ($conn->connect_error) die("DB Error");
?>
<!DOCTYPE html>
<html>
<head>
<title>KubeArena Linux Monitoring</title>
<link rel="stylesheet" href="assets/style.css">
<meta name="viewport" content="width=device-width, initial-scale=1">
</head>

<body>

<!-- ===== TOP NAV BAR ===== -->
<div class="topbar">
  <div class="logo">🚀 KubeArena – Linux Monitoring Platform</div>
  <div class="top-actions">
    <span class="user">👤 <?php echo htmlspecialchars($username); ?></span>
    <a href="logout.php" class="logout">Logout</a>
  </div>
</div>

<!-- ===== MAIN LAYOUT ===== -->
<div class="layout">

  <!-- ===== SIDEBAR ===== -->
  <div class="sidebar">
    <a class="active" href="index.php">📊 Dashboard</a>
    <a href="charts.php">📈 Charts</a>
    <a href="alerts.php">🚨 Alerts</a>

    <?php if ($role === 'admin') { ?>
      <hr>
      <a href="add_server.php">➕ Manage Servers</a>
      <a href="users.php">👥 Users</a>
      <a href="#lab-requests">🧪 Lab Requests</a>
    <?php } ?>
  </div>

  <!-- ===== CONTENT ===== -->
  <div class="content">

    <!-- ===== SUMMARY CARDS ===== -->
    <div class="cards">
      <div class="card">
        <h3>Total Servers</h3>
        <p id="total">0</p>
      </div>
      <div class="card">
        <h3>SSHD Down</h3>
        <p id="sshd">0</p>
      </div>
      <div class="card">
        <h3>Host Down</h3>
        <p id="down">0</p>
      </div>
    </div>

    <!-- ===== SERVER TABLE ===== -->
    <div class="card">
      <h3>Server Status</h3>
      <table class="modern-table" id="tbl">
        <thead>
          <tr>
            <th>Hostname</th>
            <th>OS</th>
            <th>Uptime</th>
            <th>SSHD</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody></tbody>
      </table>
    </div>

<?php if ($role === 'admin') { ?>

<!-- ===== LAB EXTENSION REQUESTS ===== -->
<div class="card" id="lab-requests">
  <h3>🧪 Pending Lab Extension Requests</h3>

  <table class="modern-table">
    <thead>
      <tr>
        <th>User</th>
        <th>Requested Hours</th>
        <th>Status</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody>

<?php
$res = $conn->query("
  SELECT ler.id, u.username, ler.hours, ler.status
  FROM lab_extension_requests ler
  JOIN users u ON ler.user_id = u.id
  WHERE ler.status='PENDING'
  ORDER BY ler.created_at ASC
");

if ($res->num_rows === 0) {
    echo "<tr><td colspan='4'>No pending lab extension requests</td></tr>";
}

while ($r = $res->fetch_assoc()) {
    echo "<tr>
      <td>{$r['username']}</td>
      <td>{$r['hours']} Hour(s)</td>
      <td><span style='color:orange;font-weight:bold'>PENDING</span></td>
      <td>
        <a href='approve_lab.php?id={$r['id']}'>✅ Approve</a> |
        <a href='reject_lab.php?id={$r['id']}'>❌ Reject</a>
      </td>
    </tr>";
}
?>

    </tbody>
  </table>
</div>

<?php } ?>

  </div>
</div>

<!-- ===== JS ===== -->
<script>
setInterval(loadData, 10000);
loadData();

function loadData() {
 fetch('api/metrics.php')
 .then(r => r.json())
 .then(d => {
  let body = "";
  let sshdDown = 0;
  let hostDown = 0;

  d.forEach(s => {
    let sshdClass = s.sshd_status === 'active' ? 'ok' : 'bad';
    if (s.sshd_status !== 'active') sshdDown++;
    if (s.reachable == 0) hostDown++;

    body += `<tr>
      <td>${s.hostname}</td>
      <td>${s.os_version}</td>
      <td>${s.uptime}</td>
      <td class="${sshdClass}">${s.sshd_status}</td>
      <td>
        <a href="terminal.php?id=${s.server_id}">🖥 Terminal</a> |
        <a class="del" href="delete_server.php?id=${s.server_id}">Delete</a>
      </td>
    </tr>`;
  });

  document.querySelector("#tbl tbody").innerHTML = body;
  document.getElementById("total").innerText = d.length;
  document.getElementById("sshd").innerText = sshdDown;
  document.getElementById("down").innerText = hostDown;
 });
}
</script>

</body>
</html>

