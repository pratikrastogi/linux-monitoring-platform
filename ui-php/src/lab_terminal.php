<?php
session_start();
require_once 'auth.php';

$db = new mysqli("mysql", "monitor", "monitor123", "monitoring");
$session_id = (int)$_GET['session_id'];
$uid = $_SESSION['uid'];

// Get lab session with server details for direct SSH connection
$session = $db->query("SELECT 
    ls.id,
    ls.user_id,
    ls.username,
    ls.namespace,
    ls.access_expiry,
    ls.status,
    ls.lab_id,
    l.lab_name,
    l.server_id,
    l.course_id,
    s.ip_address,
    s.ssh_user,
    s.ssh_password,
    s.ssh_port,
    c.lab_guide_content,
    c.name as course_name
    FROM lab_sessions ls 
    JOIN labs l ON ls.lab_id = l.id 
    LEFT JOIN servers s ON l.server_id = s.id
    JOIN courses c ON l.course_id = c.id 
    WHERE ls.id=$session_id AND ls.user_id=$uid AND ls.status='ACTIVE'")->fetch_assoc();

if (!$session) die("Invalid or expired session");

// Verify lab is provisioned (provisioned=1)
if (!$session['username'] || !$session['namespace']) {
    die("Lab not yet provisioned. Please try again in a moment.");
}

$remaining = strtotime($session['access_expiry']) - time();
$mins = floor($remaining / 60);

$page_title = "Lab Terminal";
include 'includes/header.php';
?>
<body class="hold-transition sidebar-mini" style="overflow: hidden;">
<div class="wrapper">
<?php include 'includes/navbar.php'; ?>
<?php include 'includes/sidebar.php'; ?>
<div class="content-wrapper" style="display: flex; flex-direction: column; overflow: hidden; height: 100vh;">
  <!-- Lab Header -->
  <div style="flex: 0 0 auto; background: #f8f9fa; border-bottom: 2px solid #dee2e6; padding: 12px 20px;">
    <div style="display: flex; justify-content: space-between; align-items: center;">
      <div>
        <h5 class="mb-1">
          <i class="fas fa-flask"></i> 
          <?= htmlspecialchars($session['lab_name']) ?>
          <span class="badge badge-success">ACTIVE</span>
        </h5>
        <small class="text-muted">
          <?= htmlspecialchars($session['course_name']) ?> • 
          Server: <?= htmlspecialchars($session['ip_address']) ?> • 
          User: <code><?= htmlspecialchars($session['username']) ?></code> • 
          <i class="fas fa-clock"></i> <span id="countdown" style="font-weight: bold; color: #ff6b6b;"><?= $mins ?></span> minutes remaining
        </small>
      </div>
      <div>
        <a href="my_labs.php" class="btn btn-sm btn-secondary">
          <i class="fas fa-arrow-left"></i> Back to Labs
        </a>
      </div>
    </div>
  </div>
  
  <!-- Split View: Guide + Terminal -->
  <div style="flex: 1; display: flex; overflow: hidden; gap: 1px; background: #ddd;">
    <!-- Left: Lab Guide (30%) -->
    <div style="flex: 0 0 30%; overflow-y: auto; background: white; padding: 20px;">
      <h6 class="font-weight-bold mb-3"><i class="fas fa-book"></i> Lab Guide</h6>
      <div id="guide-content" style="font-size: 13px; line-height: 1.6; color: #333;"></div>
    </div>
    
    <!-- Right: Terminal (70%) -->
    <div style="flex: 1; background: #1e1e1e; overflow: hidden;">
      <div id="terminal" style="height: 100%;"></div>
    </div>
  </div>
</div>
<?php include 'includes/footer.php'; ?>
</div>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/xterm@4.19.0/css/xterm.css">
<script src="https://cdn.jsdelivr.net/npm/xterm@4.19.0/lib/xterm.js"></script>
<script src="https://cdn.jsdelivr.net/npm/xterm-addon-fit@0.5.0/lib/xterm-addon-fit.js"></script>
<script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>

<script>
// Render lab guide
document.getElementById('guide-content').innerHTML = marked.parse(<?= json_encode($session['lab_guide_content'] ?? 'No guide available') ?>);

// Terminal setup
const term = new Terminal({
    cursorBlink: true,
    fontSize: 12,
    fontFamily: 'Courier New, monospace',
    theme: { background: '#1e1e1e', foreground: '#fff' }
});

const fitAddon = new FitAddon.FitAddon();
term.loadAddon(fitAddon);
term.open(document.getElementById('terminal'));

setTimeout(() => fitAddon.fit(), 100);
window.addEventListener('resize', () => fitAddon.fit());

// Get server and authentication details
const serverId = <?= $session['server_id'] ?>;
const username = "<?= htmlspecialchars($session['username']) ?>";
const sshUser = "<?= htmlspecialchars($session['username']) ?>";
const sshPassword = "k8s" + username + "@123!";  // Default provisioner password format
const sshHost = "<?= htmlspecialchars($session['ip_address']) ?>";
const sshPort = <?= $session['ssh_port'] ?? 22 ?>;

if (!serverId || !sshHost) {
    term.write('\r\n❌ No server assigned to this lab\r\n');
} else {
    // SECURE: server_id and user in URL, password sent in JSON message
    let ws = new WebSocket('wss://kubearena.pratikrastogi.co.in/terminal?server_id=' + serverId + '&user=' + encodeURIComponent(sshUser));
    
    ws.onopen = () => {
        term.write('\r\n🔗 Connecting to lab server (' + username + ')...\r\n');
        // Send password as JSON
        ws.send(JSON.stringify({ password: sshPassword }));
    };
    
    ws.onmessage = (event) => {
        term.write(event.data);
    };
    
    ws.onerror = (err) => {
        term.write('\r\n❌ Connection error: ' + (err.message || 'Unknown error') + '\r\n');
        console.error('WebSocket error:', err);
    };
    
    ws.onclose = () => {
        term.write('\r\n❌ Connection closed\r\n');
    };
    
    // Send terminal input to gateway
    term.onData(d => {
        if (ws.readyState === WebSocket.OPEN) {
            ws.send(d);
        }
    });
}

// Countdown timer
let startTime = Date.now();
let totalRemaining = <?= $remaining ?>;
setInterval(() => {
    let elapsed = (Date.now() - startTime) / 1000;
    let remaining = totalRemaining - elapsed;
    let mins = Math.floor(remaining / 60);
    document.getElementById('countdown').textContent = Math.max(0, mins);
    if (mins <= 0) {
        alert('Session expired!');
        location.href = 'my_labs.php';
    }
}, 5000);
</script>

</body>
</html>
