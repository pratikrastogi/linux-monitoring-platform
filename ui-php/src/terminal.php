<?php
session_start();
if (!isset($_SESSION['user'])) die("Login required");

$conn = new mysqli("mysql","monitor","monitor123","monitoring");
if ($conn->connect_error) die("DB error");

$user = $_SESSION['user'];
$uid  = $_SESSION['uid'] ?? null;
$server_id = $_GET['id'] ?? null;

/* Fetch latest lab session */
$stmt = $conn->prepare("
  SELECT * FROM lab_sessions
  WHERE user_id=?
  ORDER BY id DESC
  LIMIT 1
");
$stmt->bind_param("i",$uid);
$stmt->execute();
$lab = $stmt->get_result()->fetch_assoc();

$now = time();
$state = "NO_ACCESS";
$remaining = 0;

if ($lab) {
    $expiry = strtotime($lab['access_expiry']);
    if ($lab['status'] === 'ACTIVE' && $expiry > $now) {
        $state = "ACTIVE";
        $remaining = $expiry - $now;
    } elseif ($lab['status'] === 'ACTIVE' && $expiry <= $now) {
        // Mark expired
        $conn->query("UPDATE lab_sessions SET status='EXPIRED' WHERE id=".$lab['id']);
        $state = "EXPIRED";
    } elseif ($lab['status'] === 'REQUESTED') {
        $state = "REQUESTED";
    } else {
        $state = $lab['status'];
    }
}
?>
<!DOCTYPE html>
<html>
<head>
  <title>Terminal Access</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/xterm/css/xterm.css">
  <script src="https://cdn.jsdelivr.net/npm/xterm/lib/xterm.js"></script>
  <style>
    .box { max-width:600px;margin:40px auto;text-align:center }
    .btn { padding:10px 20px;font-size:16px;cursor:pointer }
    .warn { color:red;font-weight:bold }
    .info { color:#333 }
  </style>
</head>
<body>

<div class="box">

<h3>🖥 Kubernetes Lab Terminal</h3>
<p>User: <b><?= htmlspecialchars($user) ?></b></p>

<?php if ($state === "NO_ACCESS"): ?>

  <p class="info">You have <b>60 minutes FREE lab access</b>.</p>
  <form method="post" action="generate_free_access.php">
    <button class="btn">🚀 Generate Free Access</button>
  </form>

<?php elseif ($state === "ACTIVE"): ?>

  <p class="info">
    ⏳ Remaining Time:
    <span id="timer"></span>
  </p>

  <input type="password" id="sshpass" placeholder="SSH Password">
  <button class="btn" onclick="connect()">Connect</button>

  <div id="terminal" style="height:500px;border:1px solid #ccc"></div>

<?php elseif ($state === "EXPIRED"): ?>

  <p class="warn">❌ Your free lab access has expired.</p>
  <a href="request_access.php">👉 Request More Time</a>

<?php elseif ($state === "REQUESTED"): ?>

  <p class="info">⏳ Your request is pending admin approval.</p>

<?php endif; ?>

</div>

<?php if ($state === "ACTIVE"): ?>
<script>
let remaining = <?= (int)$remaining ?>;

function format(t){
  let m = Math.floor(t/60);
  let s = t % 60;
  return m + "m " + s + "s";
}

document.getElementById("timer").innerText = format(remaining);

setInterval(() => {
  remaining--;
  if (remaining <= 0) location.reload();
  document.getElementById("timer").innerText = format(remaining);
}, 1000);

let term = new Terminal({ cursorBlink: true });
term.open(document.getElementById('terminal'));

function connect() {
  const pass = document.getElementById("sshpass").value;
  const ws = new WebSocket(
    "ws://<?= $_SERVER['HTTP_HOST'] ?>:32000/?" +
    "server_id=<?= $server_id ?>&user=<?= $user ?>"
  );
  ws.onopen = () => {
    ws.send(JSON.stringify({ password: pass }));
    term.write("🔐 Authenticating...\r\n");
  };
  ws.onmessage = e => term.write(e.data);
  term.onData(d => ws.send(d));
}
</script>
<?php endif; ?>

</body>
</html>

