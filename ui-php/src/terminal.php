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

/* FIXED server_id (same as old script logic) */
$server_id = 1;
?>
<!DOCTYPE html>
<html>
<head>
  <title>Lab Terminal</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/xterm/css/xterm.css">
  <script src="https://cdn.jsdelivr.net/npm/xterm/lib/xterm.js"></script>

  <style>
    body { background:#111; color:#eee; font-family: monospace; }
    #terminal {
      background:#000;
      height:500px;
      border:1px solid #333;
      margin-top:10px;
    }
    .btn {
      padding:8px 12px;
      background:#4caf50;
      color:#fff;
      border:none;
      cursor:pointer;
    }
    .btn:disabled {
      background:#666;
      cursor:not-allowed;
    }
    input {
      padding:8px;
      background:#222;
      color:#fff;
      border:1px solid #555;
    }
  </style>
</head>

<body>

<h3>🧪 Lab Terminal – User: <?= htmlspecialchars($user) ?></h3>

<?php if (!$lab): ?>

  <p>No lab session found.</p>

<?php elseif ($lab['status'] === 'REQUESTED'): ?>

  <p>⏳ Lab provisioning in progress…</p>
  <p>Please wait, page will refresh automatically.</p>
  <script>
    setTimeout(() => location.reload(), 5000);
  </script>

<?php elseif ($lab['status'] === 'ACTIVE'): ?>

  <p>✅ Lab Active</p>
  <p>Expires at: <b><?= $lab['access_expiry'] ?></b></p>
  <p id="timer"></p>

  <!-- 🔑 PASSWORD INPUT (OLD SCRIPT STYLE) -->
  <input type="password" id="sshpass" placeholder="Lab user password">

  <!-- 🔌 CONNECT BUTTON -->
  <button class="btn" id="connectBtn" onclick="connect()">Connect</button>

  <!-- 🖥 TERMINAL -->
  <div id="terminal"></div>

  <script>
    /* -------- TIMER (DISPLAY ONLY) -------- */
    const expiryTs = new Date("<?= $lab['access_expiry'] ?>".replace(' ', 'T')).getTime();
    const timerEl = document.getElementById("timer");

    setInterval(() => {
      const diff = expiryTs - Date.now();
      if (diff <= 0) {
        timerEl.innerText = "⌛ Lab expired. Refresh page.";
        return;
      }
      const m = Math.floor(diff / 60000);
      const s = Math.floor((diff % 60000) / 1000);
      timerEl.innerText = `⏱ ${m}m ${s}s remaining`;
    }, 1000);

    /* -------- TERMINAL (OLD SCRIPT + FIXES) -------- */
    let term = null;
    let ws   = null;

    function connect() {

      // prevent multiple connections
      if (ws && ws.readyState === WebSocket.OPEN) {
        return;
      }

      const pass = document.getElementById("sshpass").value;
      if (!pass) {
        alert("Please enter SSH password");
        return;
      }

      // terminal created only once (OLD SCRIPT FIX)
      if (!term) {
        term = new Terminal({ cursorBlink: true });
        term.open(document.getElementById("terminal"));
      }

      document.getElementById("connectBtn").disabled = true;

      ws = new WebSocket(
        "ws://<?= $_SERVER['HTTP_HOST'] ?>:32000/?" +
        "server_id=<?= $server_id ?>&user=<?= $user ?>"
      );

      ws.onopen = () => {
        ws.send(JSON.stringify({ password: pass }));
        term.write("🔐 Authenticating...\r\n");
      };

      ws.onmessage = e => term.write(e.data);

      ws.onclose = () => {
        term.write("\r\n❌ Connection closed\r\n");
        ws = null;
        document.getElementById("connectBtn").disabled = false;
      };

      term.onData(d => {
        if (ws && ws.readyState === WebSocket.OPEN) {
          ws.send(d);
        }
      });
    }
  </script>

<?php else: ?>

  <p>❌ Lab not active (Status: <?= htmlspecialchars($lab['status']) ?>)</p>

<?php endif; ?>

</body>
</html>

