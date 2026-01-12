<?php
session_start();
if (!isset($_SESSION['user'])) header("Location: login.php");
?>
<!DOCTYPE html>
<html>
<head>
  <title>Server Charts</title>
  <link rel="stylesheet" href="assets/style.css">

  <!-- IMPORTANT: Chart.js CDN -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body>

<div class="topbar">
  <div class="logo">📊 Server Performance Charts</div>
  <a href="index.php" class="logout">⬅ Back</a>
</div>

<div class="content">
  <div class="card">
    <h3>Select Server</h3>
    <select id="serverSelect"></select>
  </div>

  <div class="card">
    <h3>CPU & Memory Usage</h3>
    <canvas id="perfChart" height="100"></canvas>
  </div>
</div>

<script>
let chart = null;

/* -----------------------------
   Load server list
------------------------------*/
function loadServers() {
  fetch('api/metrics.php')
    .then(res => res.json())
    .then(data => {
      const sel = document.getElementById('serverSelect');
      sel.innerHTML = '';

      if (data.length === 0) {
        alert("No servers found");
        return;
      }

      data.forEach(s => {
        const opt = document.createElement('option');
        opt.value = s.server_id;
        opt.text = s.hostname;
        sel.appendChild(opt);
      });

      // Load chart for first server
      loadChart(sel.value);
    });
}

/* -----------------------------
   Load chart data
------------------------------*/
function loadChart(serverId) {
  fetch('api/chart_data.php?server_id=' + serverId)
    .then(res => res.json())
    .then(data => {

      if (data.length === 0) {
        console.log("No chart data");
        return;
      }

      const labels = data.map(x => x.collected_at);
      const cpu = data.map(x => Number(x.cpu_usage));
      const mem = data.map(x => Number(x.mem_usage));

      const ctx = document.getElementById('perfChart').getContext('2d');

      if (chart) {
        chart.destroy();
      }

      chart = new Chart(ctx, {
        type: 'line',
        data: {
          labels: labels,
          datasets: [
            {
              label: 'CPU Usage (%)',
              data: cpu,
              borderColor: '#e74c3c',
              backgroundColor: 'rgba(231,76,60,0.1)',
              tension: 0.3
            },
            {
              label: 'Memory Usage (%)',
              data: mem,
              borderColor: '#3498db',
              backgroundColor: 'rgba(52,152,219,0.1)',
              tension: 0.3
            }
          ]
        },
        options: {
          responsive: true,
          plugins: {
            legend: { position: 'top' }
          },
          scales: {
            y: {
              beginAtZero: true,
              max: 100
            }
          }
        }
      });
    });
}

/* -----------------------------
   Events
------------------------------*/
document.getElementById('serverSelect').addEventListener('change', function () {
  loadChart(this.value);
});

loadServers();
</script>

</body>
</html>

