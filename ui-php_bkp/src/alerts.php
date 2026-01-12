<?php
session_start();
if (!isset($_SESSION['user'])) header("Location: login.php");

$conn = new mysqli("mysql","monitor","monitor123","monitoring");
$role = $_SESSION['role'];

/* ===== Save webhook ===== */
if ($role == 'admin' && isset($_POST['save_webhook'])) {
  $stmt = $conn->prepare("UPDATE alert_config SET teams_webhook=?, enabled=? WHERE id=1");
  $stmt->bind_param("si", $_POST['webhook'], $_POST['enabled']);
  $stmt->execute();
}

/* ===== Load config ===== */
$config = $conn->query("SELECT * FROM alert_config WHERE id=1")->fetch_assoc();
?>
<!DOCTYPE html>
<html>
<head>
<title>Alerts</title>
<link rel="stylesheet" href="assets/style.css">
<meta name="viewport" content="width=device-width, initial-scale=1">
</head>

<body>

<div class="topbar">
 <div class="logo">🚨 Alerts</div>
 <div class="top-actions">
   <a href="index.php" class="logout">⬅ Back</a>
 </div>
</div>

<div class="content">

<?php if ($role == 'admin') { ?>
<!-- WEBHOOK CONFIG -->
<div class="card">
 <h3>Microsoft Teams Alert Configuration</h3>

 <form method="post">
  <label>Teams Webhook URL</label>
  <input name="webhook" value="<?= htmlspecialchars($config['teams_webhook']) ?>"
         placeholder="https://outlook.office.com/webhook/...">

  <label>
    <input type="checkbox" name="enabled" value="1"
      <?= $config['enabled'] ? 'checked' : '' ?>> Enable Teams Alerts
  </label>

  <button name="save_webhook">Save Configuration</button>
 </form>
</div>
<?php } ?>

<!-- ACTIVE ALERTS -->
<div class="card">
 <h3>Active Alerts</h3>
 <table class="modern-table">
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

<script>
function loadAlerts() {
 fetch('api/alerts.php')
 .then(r => r.json())
 .then(d => {
  let h = '';
  if (d.length === 0) {
   h = `<tr><td colspan="4">No active alerts</td></tr>`;
  } else {
   d.forEach(a => {
    h += `<tr>
     <td>${a.hostname}</td>
     <td class="bad">${a.alert_type}</td>
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

