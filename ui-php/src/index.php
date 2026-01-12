<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$role = $_SESSION['role'];
$username = $_SESSION['user'];
$page_title = "Dashboard";

$conn = new mysqli("mysql","monitor","monitor123","monitoring");
if ($conn->connect_error) die("DB Error");

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
          <h1 class="m-0"><i class="fas fa-tachometer-alt"></i> Dashboard</h1>
        </div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="#">Home</a></li>
            <li class="breadcrumb-item active">Dashboard</li>
          </ol>
        </div>
      </div>
    </div>
  </div>

  <!-- Main content -->
  <section class="content">
    <div class="container-fluid">
      
      <!-- Info boxes -->
      <div class="row">
        <div class="col-12 col-sm-6 col-md-4">
          <div class="info-box">
            <span class="info-box-icon bg-info elevation-1"><i class="fas fa-server"></i></span>
            <div class="info-box-content">
              <span class="info-box-text">Total Servers</span>
              <span class="info-box-number" id="total">0</span>
            </div>
          </div>
        </div>
        
        <div class="col-12 col-sm-6 col-md-4">
          <div class="info-box mb-3">
            <span class="info-box-icon bg-warning elevation-1"><i class="fas fa-terminal"></i></span>
            <div class="info-box-content">
              <span class="info-box-text">SSHD Down</span>
              <span class="info-box-number" id="sshd">0</span>
            </div>
          </div>
        </div>
        
        <div class="col-12 col-sm-6 col-md-4">
          <div class="info-box mb-3">
            <span class="info-box-icon bg-danger elevation-1"><i class="fas fa-times-circle"></i></span>
            <div class="info-box-content">
              <span class="info-box-text">Host Down</span>
              <span class="info-box-number" id="down">0</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Server Status Table -->
      <div class="row">
        <div class="col-12">
          <div class="card">
            <div class="card-header">
              <h3 class="card-title"><i class="fas fa-list"></i> Server Status</h3>
              <div class="card-tools">
                <button type="button" class="btn btn-sm btn-primary" onclick="loadData()">
                  <i class="fas fa-sync-alt"></i> Refresh
                </button>
              </div>
            </div>
            <div class="card-body">
              <table class="table table-bordered table-striped" id="tbl">
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
          </div>
        </div>
      </div>

<?php if ($role === 'admin') { ?>
      <!-- Lab Extension Requests -->
      <div class="row">
        <div class="col-12">
          <div class="card" id="lab-requests">
            <div class="card-header">
              <h3 class="card-title"><i class="fas fa-flask"></i> Pending Lab Extension Requests</h3>
            </div>
            <div class="card-body">
              <table class="table table-bordered table-hover">
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
    echo "<tr><td colspan='4' class='text-center'>No pending lab extension requests</td></tr>";
}

while ($r = $res->fetch_assoc()) {
    echo "<tr>
      <td>{$r['username']}</td>
      <td>{$r['hours']} Hour(s)</td>
      <td><span class='badge badge-warning'>PENDING</span></td>
      <td>
        <a href='approve_lab.php?id={$r['id']}' class='btn btn-sm btn-success'><i class='fas fa-check'></i> Approve</a>
        <a href='reject_lab.php?id={$r['id']}' class='btn btn-sm btn-danger'><i class='fas fa-times'></i> Reject</a>
      </td>
    </tr>";
}
?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
<?php } ?>

    </div>
  </section>
</div>

<?php include 'includes/footer.php'; ?>

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
    let sshdBadge = s.sshd_status === 'active' ? '<span class="badge badge-success">active</span>' : '<span class="badge badge-danger">' + s.sshd_status + '</span>';
    if (s.sshd_status !== 'active') sshdDown++;
    if (s.reachable == 0) hostDown++;

    body += `<tr>
      <td>${s.hostname}</td>
      <td>${s.os_version}</td>
      <td>${s.uptime}</td>
      <td>${sshdBadge}</td>
      <td>
        <a href="terminal.php?id=${s.server_id}" class="btn btn-sm btn-primary"><i class="fas fa-terminal"></i> Terminal</a>
        <a href="delete_server.php?id=${s.server_id}" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i> Delete</a>
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

<?php
// =============================================
// LABS PLATFORM WIDGETS (ADDITIVE - Phase 2)
// Purpose: Add Labs dashboard widgets below existing content
// Backward Compatible: Does NOT modify existing functionality
// =============================================
if (file_exists('widgets/lab_widgets.php')) {
    include 'widgets/lab_widgets.php';
}
?>

</body>
</html>

