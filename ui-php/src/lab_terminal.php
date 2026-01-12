<?php
session_start();
require_once 'auth.php';

$db = new mysqli("mysql", "monitor", "monitor123", "monitoring");
$session_id = (int)$_GET['session_id'];
$uid = $_SESSION['uid'];

// Verify session belongs to user and is active
$session = $db->query("SELECT ls.*, l.bastion_host, l.bastion_user, l.bastion_password, c.lab_guide_content, c.name as course_name, l.lab_name 
    FROM lab_sessions ls 
    JOIN labs l ON ls.lab_id = l.id 
    JOIN courses c ON l.course_id = c.id 
    WHERE ls.id=$session_id AND ls.user_id=$uid AND ls.status='ACTIVE'")->fetch_assoc();

if (!$session) die("Invalid or expired session");

$remaining = strtotime($session['access_expiry']) - time();
$mins = floor($remaining / 60);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Lab Terminal</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/xterm@4.19.0/css/xterm.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <script src="https://cdn.jsdelivr.net/npm/xterm@4.19.0/lib/xterm.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/xterm-addon-fit@0.5.0/lib/xterm-addon-fit.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
    <style>
        body { margin: 0; padding: 0; overflow: hidden; }
        .lab-container { display: flex; height: 100vh; }
        .lab-guide { width: 30%; overflow-y: auto; padding: 20px; background: #f4f4f4; border-right: 1px solid #ccc; }
        .terminal-container { flex: 1; padding: 20px; }
        #terminal { height: calc(100% - 60px); }
        .lab-header { background: #28a745; color: white; padding: 10px; }
    </style>
</head>
<body>
<div class="lab-container">
    <div class="lab-guide">
        <div class="lab-header">
            <h4><?= htmlspecialchars($session['course_name']) ?></h4>
            <h5><?= htmlspecialchars($session['lab_name']) ?></h5>
            <p><i class="fas fa-clock"></i> <?= $mins ?> minutes remaining</p>
        </div>
        <div id="guide-content"></div>
    </div>
    
    <div class="terminal-container">
        <div class="alert alert-info">
            <strong>Connected to:</strong> <?= htmlspecialchars($session['bastion_host']) ?><br>
            <strong>Session expires in:</strong> <span id="countdown"><?= $mins ?></span> minutes
            <button onclick="location.href='my_labs.php'" class="btn btn-sm btn-secondary float-right">Back to My Labs</button>
        </div>
        <div id="terminal"></div>
    </div>
</div>

<script>
// Render lab guide
document.getElementById('guide-content').innerHTML = marked.parse(<?= json_encode($session['lab_guide_content']) ?>);

// Terminal setup (uses your existing WebSocket connection)
const term = new Terminal({
    cursorBlink: true,
    fontSize: 14
});

const fitAddon = new FitAddon.FitAddon();
term.loadAddon(fitAddon);
term.open(document.getElementById('terminal'));
fitAddon.fit();

// Connect to existing terminal WebSocket
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
setInterval(() => {
    let remaining = <?= $remaining ?> - (Date.now() - <?= time() * 1000 ?>) / 1000;
    let mins = Math.floor(remaining / 60);
    document.getElementById('countdown').textContent = mins;
    if (mins <= 0) {
        alert('Session expired!');
        location.href = 'my_labs.php';
    }
}, 60000);
</script>
</body>
</html>
```
```php
<!-- For Admin -->
<li class="nav-item"><a href="admin_courses.php" class="nav-link"><i class="fas fa-book"></i> <p>Courses</p></a></li>
<li class="nav-item"><a href="admin_labs.php" class="nav-link"><i class="fas fa-flask"></i> <p>Labs</p></a></li>
<li class="nav-item"><a href="admin_requests.php" class="nav-link"><i class="fas fa-clipboard-check"></i> <p>Lab Requests</p></a></li>

<!-- For Users -->
<li class="nav-item"><a href="browse_labs.php" class="nav-link"><i class="fas fa-search"></i> <p>Browse Labs</p></a></li>
<li class="nav-item"><a href="my_labs.php" class="nav-link"><i class="fas fa-laptop-code"></i> <p>My Labs</p></a></li>
