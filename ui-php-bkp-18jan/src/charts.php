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
<div class="content-wrapper" style="display: flex; flex-direction: column; overflow: hidden;">
  <!-- Content Header -->
  <div class="content-header" style="flex: 0 0 auto;">
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
  <section class="content" style="flex: 1; overflow-y: auto;">
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
    .then(res => {
      if (!res.ok) {
        throw new Error('API returned ' + res.status);
      }
      return res.json();
    })
    .then(data => {
      const sel = document.getElementById('serverSelect');
      sel.innerHTML = '';

      console.log('Servers loaded:', data);

      if (!data || data.length === 0) {
        sel.innerHTML = '<option>No servers available</option>';
        console.warn('No servers returned from API');
        return;
      }

      data.forEach(s => {
        const opt = document.createElement('option');
        opt.value = s.server_id;
        opt.text = s.hostname + ' (' + s.ip_address + ')';
        sel.appendChild(opt);
      });

      // Load chart for first server
      if (data.length > 0) {
        loadChart(data[0].server_id);
      }
    })
    .catch(err => {
      console.error('Error loading servers:', err);
      document.getElementById('serverSelect').innerHTML = '<option>Error loading servers</option>';
    });
}

/* -----------------------------
   Load chart data
------------------------------*/
function loadChart(serverId) {
  fetch('api/chart_data.php?server_id=' + serverId)
    .then(res => res.json())
    .then(data => {

      if (!data || data.length === 0) {
        console.log("No chart data for server", serverId);
        const ctx = document.getElementById('perfChart');
        if (ctx) {
          ctx.getContext('2d').clearRect(0, 0, ctx.width, ctx.height);
        }
        return;
      }

      const labels = data.map(x => x.collected_at);
      const cpu = data.map(x => parseFloat(x.cpu_usage || 0));
      const mem = data.map(x => parseFloat(x.mem_usage || 0));

      const ctx = document.getElementById('perfChart');
      
      if (!ctx) {
        console.error('Chart canvas not found');
        return;
      }

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
              tension: 0.3,
              fill: true
            },
            {
              label: 'Memory Usage (%)',
              data: mem,
              borderColor: '#3498db',
              backgroundColor: 'rgba(52,152,219,0.1)',
              tension: 0.3,
              fill: true
            }
          ]
        },
        options: {
          responsive: true,
          maintainAspectRatio: true,
          plugins: {
            legend: { position: 'top' }
          },
          scales: {
            y: {
              beginAtZero: true,
              max: 100,
              ticks: {
                callback: function(value) {
                  return value + '%';
                }
              }
            }
          }
        }
      });
    })
    .catch(err => {
      console.error('Error loading chart data:', err);
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

