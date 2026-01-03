<?php
session_start();
if (!isset($_SESSION['user'])) header("Location: login.php");
?>
<!DOCTYPE html>
<html>
<head>
<title>Charts</title>
<link rel="stylesheet" href="assets/style.css">
<script src="assets/chart.min.js"></script>
</head>
<body>

<div class="topbar">
 <div class="logo">📊 Server Charts</div>
 <a href="index.php" class="logout">⬅ Back</a>
</div>

<div class="content">
 <div class="card">
  <h3>CPU / Memory Usage</h3>
  <select id="serverSelect"></select>
  <canvas id="chart"></canvas>
 </div>
</div>

<script>
let chart;

function loadServers() {
 fetch('api/metrics.php')
 .then(r=>r.json())
 .then(d=>{
  let s=document.getElementById("serverSelect");
  s.innerHTML='';
  d.forEach(x=>{
   s.innerHTML += `<option value="${x.server_id}">${x.hostname}</option>`;
  });
  loadChart(s.value);
 });
}

function loadChart(id) {
 fetch('api/chart_data.php?server_id='+id)
 .then(r=>r.json())
 .then(d=>{
  let labels=d.map(x=>x.collected_at);
  let cpu=d.map(x=>x.cpu_usage);

  if(chart) chart.destroy();
  chart = new Chart(document.getElementById("chart"),{
   type:'line',
   data:{
    labels:labels,
    datasets:[{label:'CPU %',data:cpu,borderColor:'#c33764'}]
   }
  });
 });
}

document.getElementById("serverSelect").onchange=e=>loadChart(e.target.value);

loadServers();
</script>

</body>
</html>

