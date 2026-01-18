  <footer class="main-footer">
    <strong>Copyright &copy; 2024-2026 <a href="#">KubeArena</a>.</strong>
    All rights reserved.
    <div class="float-right d-none d-sm-inline-block">
      <b>Version</b> 3.0.0 | Enterprise Linux & Kubernetes Platform
    </div>
  </footer>

</div>
<!-- ./wrapper -->

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- Bootstrap 4 -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- AdminLTE App -->
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
<!-- DataTables -->
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap4.min.js"></script>

<script>
// Global alert check function
function checkAlerts() {
  fetch('api/metrics.php')
    .then(r => r.json())
    .then(data => {
      let alertCount = 0;
      let alertHtml = '';
      
      data.forEach(s => {
        if (s.sshd_status !== 'active' || s.reachable == 0) {
          alertCount++;
          const alertType = s.reachable == 0 ? 'danger' : 'warning';
          const alertMsg = s.reachable == 0 ? 'Host Down' : 'SSHD Down';
          alertHtml += `
            <a href="#" class="dropdown-item">
              <i class="fas fa-exclamation-circle text-${alertType} mr-2"></i> 
              ${s.hostname}: ${alertMsg}
            </a>
          `;
        }
      });
      
      // Update navbar badge
      const alertCountElem = document.getElementById('alertCount');
      if (alertCountElem) {
        alertCountElem.textContent = alertCount;
        alertCountElem.className = alertCount > 0 ? 'badge badge-danger navbar-badge' : 'badge badge-success navbar-badge';
      }
      
      // Update sidebar badge
      const sidebarAlertElem = document.getElementById('sidebarAlertCount');
      if (sidebarAlertElem) {
        sidebarAlertElem.textContent = alertCount;
        sidebarAlertElem.className = alertCount > 0 ? 'badge badge-danger right' : 'badge badge-success right';
      }
      
      // Update dropdown
      const alertDropdown = document.getElementById('alertDropdown');
      if (alertDropdown) {
        alertDropdown.innerHTML = alertCount > 0 ? alertHtml : 
          '<a href="#" class="dropdown-item"><i class="fas fa-check-circle text-success mr-2"></i> All systems operational</a>';
      }
    })
    .catch(err => console.error('Alert check failed:', err));
}

// Run on page load and every 30 seconds
if (typeof checkAlerts === 'function') {
  checkAlerts();
  setInterval(checkAlerts, 30000);
}

// Ensure Bootstrap dropdowns initialize (use native Bootstrap/ AdminLTE handlers)
$(function() {
  $('[data-toggle="dropdown"]').dropdown();

  // Sidebar behavior: collapsed by default on desktop, expand on hover, click to toggle; mobile opens on click
  const $body = $('body');
  const $toggle = $('[data-widget="pushmenu"]');

  // initialize pushmenu
  $toggle.PushMenu();

  // enforce collapsed state on load (desktop)
  if (window.innerWidth >= 992) {
    $toggle.PushMenu('collapse');
  }

  // hover to expand on desktop, collapse on mouse leave
  $('.main-sidebar').hover(
    function() {
      if (window.innerWidth >= 992) {
        $toggle.PushMenu('expand');
      }
    },
    function() {
      if (window.innerWidth >= 992) {
        $toggle.PushMenu('collapse');
      }
    }
  );
});
</script>

</body>
</html>
