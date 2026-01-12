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
  
  <!-- Split View: Terminal + Guide -->
  <div style="flex: 1; display: flex; overflow: hidden; gap: 0; background: #ddd;">
    <!-- Left: Terminal (70%) -->
    <div style="flex: 1; display: flex; flex-direction: column; background: #000; border-right: 2px solid #dee2e6; overflow: hidden;">
      <div style="background: #2c3e50; color: white; padding: 8px 12px; font-weight: bold; flex: 0 0 auto; display: flex; justify-content: space-between; align-items: center;">
        <span><i class="fas fa-terminal"></i> Terminal - <?= htmlspecialchars($session['ip_address'] ?? 'Lab Server') ?></span>
        <small style="font-weight: normal; opacity: 0.8;">User: <?= htmlspecialchars($session['username']) ?></small>
      </div>
      <div id="terminal" style="flex: 1; background: #000; overflow: hidden;"></div>
    </div>
    
    <!-- Right: Lab Guide (30%) -->
    <div style="flex: 0 0 30%; display: flex; flex-direction: column; background: #f8f9fa; overflow: hidden;">
      <div style="background: #f1f3f4; padding: 8px 12px; font-weight: bold; border-bottom: 1px solid #dee2e6; flex: 0 0 auto;">
        <i class="fas fa-book"></i> Lab Information & Guide
      </div>
      
      <!-- Guide Content -->
      <div style="flex: 1; overflow-y: auto; padding: 20px;" id="labGuidePanel">
        <?php if (!empty($session['lab_guide_content'])): ?>
          <!-- Lab Guide Content from Course -->
          <div class="lab-guide-content">
            <div id="guide-content"></div>
          </div>
        <?php else: ?>
          <!-- Default Lab Information -->
          <div class="alert alert-info">
            <h5><i class="fas fa-info-circle"></i> Lab Information</h5>
            <p>No lab guide available for this course.</p>
          </div>
        <?php endif; ?>
        
        <!-- Lab Specifications -->
        <div class="card mt-4" style="border: 1px solid #dee2e6;">
          <div class="card-header bg-light" style="background: #f1f3f4;">
            <h6 class="mb-0" style="font-size: 12px;"><i class="fas fa-cogs"></i> Lab Specifications</h6>
          </div>
          <div class="card-body" style="padding: 10px;">
            <table class="table table-sm table-borderless" style="margin: 0; font-size: 12px;">
              <tr>
                <td><strong>Lab:</strong></td>
                <td><?= htmlspecialchars($session['lab_name']) ?></td>
              </tr>
              <tr>
                <td><strong>Course:</strong></td>
                <td><?= htmlspecialchars($session['course_name'] ?? 'N/A') ?></td>
              </tr>
              <tr>
                <td><strong>Server IP:</strong></td>
                <td><code style="font-size: 11px;"><?= htmlspecialchars($session['ip_address'] ?? 'N/A') ?></code></td>
              </tr>
              <tr>
                <td><strong>User:</strong></td>
                <td><code style="font-size: 11px;"><?= htmlspecialchars($session['username']) ?></code></td>
              </tr>
              <tr>
                <td><strong>Namespace:</strong></td>
                <td><code style="font-size: 11px;"><?= htmlspecialchars($session['namespace']) ?></code></td>
              </tr>
              <tr>
                <td><strong>SSH Port:</strong></td>
                <td><?= $session['ssh_port'] ?? 22 ?></td>
              </tr>
            </table>
          </div>
        </div>
      </div>
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
$(document).ready(function() {
    // Render lab guide if available
    const guideContent = <?= json_encode($session['lab_guide_content'] ?? null) ?>;
    if (guideContent && document.getElementById('guide-content')) {
        document.getElementById('guide-content').innerHTML = marked.parse(guideContent);
    }
    
    initializeTerminal();
});

function initializeTerminal() {
    // Terminal setup
    const term = new Terminal({
        cursorBlink: true,
        fontSize: 12,
        fontFamily: 'Courier New, monospace',
        theme: { background: '#000000', foreground: '#ffffff' }
    });

    const fitAddon = new FitAddon.FitAddon();
    term.loadAddon(fitAddon);
    term.open(document.getElementById('terminal'));

    // Fit terminal to container
    setTimeout(() => fitAddon.fit(), 100);
    
    // Responsive resize
    window.addEventListener('resize', () => {
        try {
            fitAddon.fit();
        } catch (e) {
            console.error('Fit error:', e);
        }
    });

    // Get server and authentication details
    const serverId = <?= $session['server_id'] ?>;
    const username = "<?= htmlspecialchars($session['username']) ?>";
    const sshUser = "<?= htmlspecialchars($session['username']) ?>";
    const sshPassword = "k8s" + username + "@123!";  // Default provisioner password format
    const sshHost = "<?= htmlspecialchars($session['ip_address']) ?>";
    const sshPort = <?= $session['ssh_port'] ?? 22 ?>;

    if (!serverId || !sshHost) {
        term.write('\r\n❌ No server assigned to this lab\r\n');
        return;
    }

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
    let secs = Math.floor(remaining % 60);
    
    const countdownEl = document.getElementById('countdown');
    if (countdownEl) {
        countdownEl.textContent = Math.max(0, mins);
        // Change color to red when less than 10 minutes
        if (mins < 10) {
            countdownEl.style.color = '#ff6b6b';
        }
    }
    
    if (mins <= 0 && secs <= 0) {
        alert('Session expired! Redirecting to My Labs...');
        location.href = 'my_labs.php';
    }
}, 5000);
</script>

<style>
.lab-guide-content {
    color: #333;
    line-height: 1.8;
    font-size: 13px;
}

.lab-guide-content p {
    margin-bottom: 10px;
}

.lab-guide-content h1,
.lab-guide-content h2,
.lab-guide-content h3,
.lab-guide-content h4,
.lab-guide-content h5,
.lab-guide-content h6 {
    margin-top: 15px;
    margin-bottom: 8px;
    font-weight: 600;
    color: #222;
}

.lab-guide-content code {
    background: #f1f1f1;
    padding: 2px 4px;
    border-radius: 3px;
    font-family: 'Courier New', monospace;
    font-size: 12px;
}

.lab-guide-content pre {
    background: #f1f1f1;
    padding: 10px;
    border-radius: 4px;
    overflow-x: auto;
    border-left: 3px solid #007bff;
}

.lab-guide-content ul, .lab-guide-content ol {
    margin-bottom: 10px;
    margin-left: 20px;
}

.lab-guide-content li {
    margin-bottom: 5px;
}
</style>
