<?php
session_start();
if (!isset($_SESSION['user'], $_SESSION['uid'])) {
    die("Login required");
}

$conn = new mysqli("mysql","monitor","monitor123","monitoring");
if ($conn->connect_error) die("DB error");

$user = $_SESSION['user'];
$uid  = (int)$_SESSION['uid'];

/* Fetch latest lab session */
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
  body {
    background:#111;
    color:#eee;
    font-family: monospace;
  }
  .btn {
    padding:10px 14px;
    background:#4caf50;
    color:#fff;
    border:none;
    border-radius:4px;
    cursor:pointer;
    margin-top:10px;
  }
  .status {
    margin:10px 0;
    font-weight:bold;
  }
  #terminal {
    background:#000;
    margin-top:10px;
    height:500px;
    border:1px solid #333;
  }
</style>
</head>

<body>

<h2>🧪 Kubernetes Lab – <?= htmlspecialchars($user) ?></h2>

<?php if (!$lab): ?>

  <!-- NO LAB -->
  <p>You have not used your free lab yet.</p>
  <a class="btn" href="generate_free_access.php">🚀 Generate Free Access (60 min)</a>

<?php elseif ($lab['status'] === 'REQUESTED'): ?>

  <!-- REQUESTED -->
  <p class="status">⏳ Provisioning in progress…</p>
  <p>Please wait. This page will refresh automatically.</p>

  <script>
    setTimeout(() => location.reload(), 5000);
  </script>

<?php elseif ($lab['status'] === 'ACTIVE'): ?>

  <!-- ACTIVE -->
  <p class="status">✅ Lab Active</p>
  <p>Expires at: <b><?= $lab['access_expiry'] ?></b></p>
  <p id="timer"></p>

  <button class="btn" onclick="connect()">🔌 Connect to Lab</button>

  <div id="terminal"></div>

  <script>
    /* -------- TIMER (NO AUTO-RELOAD) -------- */
    const expiryTs = new Date("<?= $lab['access_expiry'] ?>".replace(' ', 'T')).getTime();
    const timerEl = document.getElementById("timer");

    function updateTimer() {
      const diff = expiryTs - Date.now();

      if (diff <= 0) {
        timerEl.innerText = "⌛ Lab expired. Please refresh.";
        return;
      }

      const m = Math.floor(diff / 60000);
      const s = Math.floor((diff % 60000) / 1000);
      timerEl.innerText = `⏱ ${m}m ${s}s remaining`;
    }

    updateTimer();
    setInterval(updateTimer, 1000);

    /* -------- TERMINAL -------- */
    let term = null;
    let ws   = null;

    function connect() {
      if (ws) return;

      term = new Terminal({
        cursorBlink: true,
        theme: {
          background: "#000000",
          foreground: "#ffffff"
        }
      });

      term.open(document.getElementById("terminal"));

      ws = new WebSocket(
        "ws://<?= $_SERVER['HTTP_HOST'] ?>:32000/?" +
        "server_id=1&user=<?= $user ?>"
      );

      ws.onopen = () => {
        term.write("🔐 Connected to lab\r\n");
      };

      ws.onmessage = e => term.write(e.data);

      ws.onclose = () => {
        term.write("\r\n❌ Connection closed\r\n");
        ws = null;
      };

      term.onData(d => {
        if (ws && ws.readyState === 1) {
          ws.send(d);
        }
      });
    }
  </script>

<?php elseif ($lab['status'] === 'FAILED'): ?>

  <!-- FAILED -->
  <p class="status">❌ Provisioning failed.</p>
  <p>Please contact admin.</p>

<?php elseif ($lab['status'] === 'EXPIRED'): ?>

  <!-- EXPIRED -->
  <p class="status">⌛ Your lab has expired.</p>
  <a class="btn" href="request_extension.php">Request More Time</a>

<?php elseif ($lab['status'] === 'REVOKED'): ?>

  <!-- REVOKED -->
  <p class="status">🚫 Lab access revoked by admin.</p>

<?php endif; ?>

</body>
</html>

