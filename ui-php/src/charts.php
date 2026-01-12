<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}
$page_title = "Performance Charts";
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
          <h1 class="m-0"><i class="fas fa-chart-line"></i> Performance Charts</h1>
        </div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
            <li class="breadcrumb-item active">Charts</li>
          </ol>
        </div>
      </div>
    </div>
  </div>

  <!-- Main content -->
  <section class="content">
    <div class="container-fluid">
      
      <!-- Server Selection -->
      <div class="row">
        <div class="col-12">
          <div class="card">
            <div class="card-header">
              <h3 class="card-title"><i class="fas fa-server"></i> Select Server</h3>
            </div>
            <div class="card-body">
              <select id="serverSelect" class="form-control"></select>
            </div>
          </div>
        </div>
      </div>

      <!-- CPU & Memory Chart -->
      <div class="row">
        <div class="col-12">
          <div class="card">
            <div class="card-header">
              <h3 class="card-title"><i class="fas fa-microchip"></i> CPU & Memory Usage</h3>
            </div>
            <div class="card-body">
              <canvas id="perfChart" height="100"></canvas>
            </div>
          </div>
        </div>
      </div>

    </div>
  </section>
</div>

<?php include 'includes/footer.php'; ?>

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

