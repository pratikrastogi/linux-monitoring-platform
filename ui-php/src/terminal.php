<?php
session_start();
if (!isset($_SESSION['user'], $_SESSION['uid'])) {
    die("Login required");
}

$conn = new mysqli("mysql","monitor","monitor123","monitoring");
if ($conn->connect_error) die("DB error");

$user = $_SESSION['user'];
$uid  = (int)$_SESSION['uid'];

$q = $conn->prepare("
    SELECT * FROM lab_sessions
    WHERE user_id = ?
    ORDER BY id DESC
    LIMIT 1
");
$q->bind_param("i", $uid);
$q->execute();
$lab = $q->get_result()->fetch_assoc();
?>
<!DOCTYPE html>
<html>
<head>
<title>Lab Terminal</title>
<link rel="stylesheet" href="assets/style.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/xterm/css/xterm.css">
<script src="https://cdn.jsdelivr.net/npm/xterm/lib/xterm.js"></script>

<style>
  body { background:#111; color:#eee; font-family: monospace; }
  #terminal { background:#000; }
  .btn {
    padding:10px 14px;
    background:#4caf50;
    color:#fff;
    text-decoration:none;
    border-radius:4px;
    display:inline-block;
    margin-top:10px;
    cursor:pointer;
  }
  .status { margin:10px 0; }
</style>
</head>

<body>

<h2>🧪 Kubernetes Lab – <?= htmlspecialchars($user) ?></h2>

<?php if (!$lab): ?>

  <p>You have not used your free lab yet.</p>
  <a class="btn" href="generate_free_access.php">🚀 Generate Free Access (60 min)</a>

<?php elseif ($lab['status'] === 'REQUESTED'): ?>

  <p class="status">⏳ Provisioning in progress…</p>
  <p>This page will refresh automatically.</p>
  <script>
    setTimeout(() => location.reload(), 5000);
  </script>

<?php elseif ($lab['status'] === 'ACTIVE'): ?>

  <p class="status">✅ <b>Lab Active</b></p>
  <p>Expires at: <b><?= $lab['access_expiry'] ?></b></p>
  <p id="timer"></p>

  <button class="btn" onclick="connect()">🔌 Connect to Lab</button>

  <div id="terminal" style="height:500px;margin-top:10px;"></div>

  <script>
    const expiryTs = new Date("<?= $lab['access_expiry'] ?>").getTime();
    const timerEl = document.getElementById("timer");

    function updateTimer() {
      const diff = expiryTs - Date.now();
      if (diff <= 0) {
        timerEl.innerText = "⌛ Lab expired. Refreshing…";
        setTimeout(() => location.reload(), 3000);
        return;
      }
      const m = Math.floor(diff / 60000);
      const s = Math.floor((diff % 60000) / 1000);
      timerEl.innerText = `⏱ ${m}m ${s}s remaining`;
    }
    updateTimer();
    setInterval(updateTimer, 1000);

    let term;
    let ws;

    function connect() {
      if (ws) return;

      term = new Terminal({
        cursorBlink: true,
        theme: { background: "#000000", foreground: "#ffffff" }
      });
      term.open(document.getElementById("terminal"));

      ws = new WebSocket(
        "ws://<?= $_SERVER['HTTP_HOST'] ?>:32000/?user=<?= $user ?>"
      );

      ws.onopen = () => term.write("🔐 Connected to lab\r\n");
      ws.onmessage = e => term.write(e.data);
      ws.onclose = () => term.write("\r\n❌ Disconnected\r\n");
      term.onData(d => ws.send(d));
    }
  </script>

<?php elseif ($lab['status'] === 'FAILED'): ?>

  <p>❌ Provisioning failed. Contact admin.</p>

<?php elseif ($lab['status'] === 'EXPIRED'): ?>

  <p>⌛ Your lab has expired.</p>
  <a class="btn" href="request_extension.php">Request More Time</a>

<?php elseif ($lab['status'] === 'REVOKED'): ?>

  <p>🚫 Lab access revoked by admin.</p>

<?php endif; ?>

</body>
</html>

