<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$conn = new mysqli("mysql","monitor","monitor123","monitoring");
$role = $_SESSION['role'];
$page_title = "Alerts";

/* ===== Save webhook ===== */
if ($role == 'admin' && isset($_POST['save_webhook'])) {
  $stmt = $conn->prepare("UPDATE alert_config SET teams_webhook=?, enabled=? WHERE id=1");
  $stmt->bind_param("si", $_POST['webhook'], $_POST['enabled']);
  $stmt->execute();
}

/* ===== Load config ===== */
$config = $conn->query("SELECT * FROM alert_config WHERE id=1")->fetch_assoc();

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
          <h1 class="m-0"><i class="fas fa-exclamation-triangle"></i> Alerts</h1>
        </div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
            <li class="breadcrumb-item active">Alerts</li>
          </ol>
        </div>
      </div>
    </div>
  </div>

  <!-- Main content -->
  <section class="content">
    <div class="container-fluid">

<?php if ($role == 'admin') { ?>
      <!-- Webhook Configuration -->
      <div class="row">
        <div class="col-12">
          <div class="card">
            <div class="card-header">
              <h3 class="card-title"><i class="fab fa-microsoft"></i> Microsoft Teams Alert Configuration</h3>
            </div>
            <div class="card-body">
              <form method="post">
                <div class="form-group">
                  <label>Teams Webhook URL</label>
                  <input type="text" name="webhook" class="form-control" value="<?= htmlspecialchars($config['teams_webhook']) ?>" placeholder="https://outlook.office.com/webhook/...">
                </div>
                <div class="form-group">
                  <div class="custom-control custom-checkbox">
                    <input type="checkbox" class="custom-control-input" id="enableAlerts" name="enabled" value="1" <?= $config['enabled'] ? 'checked' : '' ?>>
                    <label class="custom-control-label" for="enableAlerts">Enable Teams Alerts</label>
                  </div>
                </div>
                <button type="submit" name="save_webhook" class="btn btn-primary"><i class="fas fa-save"></i> Save Configuration</button>
              </form>
            </div>
          </div>
        </div>
      </div>
<?php } ?>

      <!-- Active Alerts -->
      <div class="row">
        <div class="col-12">
          <div class="card">
            <div class="card-header">
              <h3 class="card-title"><i class="fas fa-bell"></i> Active Alerts</h3>
              <div class="card-tools">
                <button type="button" class="btn btn-sm btn-primary" onclick="loadAlerts()">
                  <i class="fas fa-sync-alt"></i> Refresh
                </button>
              </div>
            </div>
            <div class="card-body">
              <table class="table table-bordered table-striped">
                <thead>
                  <tr>
                    <th>Hostname</th>
                    <th>Type</th>
                    <th>Message</th>
                    <th>Time</th>
                  </tr>
                </thead>
                <tbody id="alertBody"></tbody>
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
function loadAlerts() {
 fetch('api/alerts.php')
 .then(r => r.json())
 .then(d => {
  let h = '';
  if (d.length === 0) {
   h = `<tr><td colspan="4" class="text-center">No active alerts</td></tr>`;
  } else {
   d.forEach(a => {
    h += `<tr>
     <td>${a.hostname}</td>
     <td><span class="badge badge-danger">${a.alert_type}</span></td>
     <td>${a.message}</td>
     <td>${a.created_at}</td>
    </tr>`;
   });
  }
  document.getElementById("alertBody").innerHTML = h;
 });
}

loadAlerts();
setInterval(loadAlerts, 5000);
</script>

</body>
</html>

