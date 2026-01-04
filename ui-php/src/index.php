<?php
session_start();
if (!isset($_SESSION['user'])) header("Location: login.php");
$role = $_SESSION['role'];
?>
<!DOCTYPE html>
<html>
<head>
<title>Pratik Linux Monitoring</title>
<link rel="stylesheet" href="assets/style.css">
<meta name="viewport" content="width=device-width, initial-scale=1">
</head>

<body>

<!-- ===== TOP NAV BAR ===== -->
<div class="topbar">
  <div class="logo">🖥️ Shivani Rastogi LAB Linux Monitoring</div>
  <div class="top-actions">
    <span class="user">👤 <?php echo $_SESSION['user']; ?></span>
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

    <?php if ($role == 'admin') { ?>
      <hr>
      <a href="add_server.php">➕ Add Server</a>
      <a href="users.php">👥 Users</a>
    <?php } ?>
  </div>

  <!-- ===== CONTENT ===== -->
  <div class="content">

    <!-- SUMMARY CARDS -->
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

    <!-- SERVER TABLE -->
    <div class="card">
      <h3>Server Status</h3>
      <table class="modern-table" id="tbl">
        <thead>
          <tr>
            <th>Hostname</th>
            <th>OS</th>
            <th>Uptime</th>
            <th>SSHD</th>
            <?php if ($role == 'admin') echo "<th>Action</th>"; ?>
          </tr>
        </thead>
        <tbody></tbody>
      </table>
    </div>

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
      <?php if ($role == 'admin') { ?>
      <td><a class="del" href="delete_server.php?id=${s.server_id}">Delete</a></td>
      <?php } ?>
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

