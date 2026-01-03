<?php
session_start();
if (!isset($_SESSION['user'])) header("Location: login.php");
?>
<!DOCTYPE html>
<html>
<head>
<title>Alerts</title>
<link rel="stylesheet" href="assets/style.css">
</head>
<body>

<div class="topbar">
 <div class="logo">🚨 Alerts</div>
 <a href="index.php" class="logout">⬅ Back</a>
</div>

<div class="content">
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

