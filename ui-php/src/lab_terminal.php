<?php
session_start();
require_once 'auth.php';

$db = new mysqli("mysql", "monitor", "monitor123", "monitoring");
$session_id = (int)$_GET['session_id'];
$uid = $_SESSION['uid'];

$session = $db->query("SELECT ls.*, l.bastion_host, l.bastion_user, l.bastion_password, c.lab_guide_content, c.name as course_name, l.lab_name 
    FROM lab_sessions ls 
    JOIN labs l ON ls.lab_id = l.id 
    JOIN courses c ON l.course_id = c.id 
    WHERE ls.id=$session_id AND ls.user_id=$uid AND ls.status='ACTIVE'")->fetch_assoc();

if (!$session) die("Invalid or expired session");

$remaining = strtotime($session['access_expiry']) - time();
$mins = floor($remaining / 60);

$page_title = "Lab Terminal";
include 'includes/header.php';
?>
<body class="hold-transition sidebar-mini" style="overflow: hidden;">
<div class="wrapper">
<?php include 'includes/navbar.php'; ?>
<?php include 'includes/sidebar.php'; ?>
<div class="content-wrapper" style="display: flex; flex-direction: column; overflow: hidden;">
  <div style="flex: 0 0 auto; display: flex; gap: 20px;">
    <div style="flex: 1; padding: 15px; background: #f8f9fa; border-bottom: 1px solid #ddd;">
      <h5><?= htmlspecialchars($session['course_name']) ?> - <?= htmlspecialchars($session['lab_name']) ?></h5>
      <p class="text-muted mb-0"><i class="fas fa-clock"></i> <span id="countdown"><?= $mins ?></span> minutes remaining</p>
    </div>
    <div style="flex: 0 0 auto; padding: 15px; border-bottom: 1px solid #ddd;">
      <a href="my_labs.php" class="btn btn-sm btn-secondary">
        <i class="fas fa-arrow-left"></i> Back
      </a>
    </div>
  </div>
  
  <div style="flex: 1; display: flex; overflow: hidden; gap: 1px; background: #ddd;">
    <div style="flex: 0 0 30%; overflow-y: auto; background: white; padding: 15px;">
      <h6 class="font-weight-bold mb-3"><i class="fas fa-book"></i> Lab Guide</h6>
      <div id="guide-content" style="font-size: 14px; line-height: 1.6;"></div>
    </div>
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

// WebSocket connection
const ws = new WebSocket('wss://kubearena.pratikrastogi.co.in/terminal');
ws.onopen = () => {
    ws.send(JSON.stringify({
        type: 'auth',
        host: '<?= $session['bastion_host'] ?>',
        user: '<?= $session['bastion_user'] ?>',
        password: '<?= $session['bastion_password'] ?>'
    }));
};

ws.onmessage = (event) => {
    term.write(event.data);
};

term.onData((data) => {
    ws.send(JSON.stringify({ type: 'data', data: data }));
});

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
