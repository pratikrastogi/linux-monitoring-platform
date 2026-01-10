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
  body {
    background:#111;
    color:#eee;
    font-family: monospace;
  }
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
    margin-left:5px;
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

<h2>🧪 Kubernetes Lab – <?= htmlspecialchars($user) ?></h2>

<?php if ($lab && $lab['status'] === 'ACTIVE'): ?>

  <p>✅ <b>Lab Active</b></p>
  <p>Expires at: <b><?= $lab['access_expiry'] ?></b></p>
  <p id="timer"></p>

  <!-- 🔑 PASSWORD INPUT -->
  <input
    type="password"
    id="sshpass"
    placeholder="Lab user password (jaise: LabSunil@123)"
  >

  <!-- 🔌 CONNECT BUTTON -->
  <button class="btn" id="connectBtn" onclick="connect()">🔌 Connect</button>

  <!-- 🖥 TERMINAL -->
  <div id="terminal"></div>

  <script>
    /* -------- TIMER (sirf display ke liye) -------- */
    const expiryTs = new Date("<?= $lab['access_expiry'] ?>".replace(' ', 'T')).getTime();
    const timerEl = document.getElementById("timer");

    setInterval(() => {
      const diff = expiryTs - Date.now();
      if (diff <= 0) {
        timerEl.innerText = "⌛ Lab expired. Page refresh karo.";
        return;
      }
      const m = Math.floor(diff / 60000);
      const s = Math.floor((diff % 60000) / 1000);
      timerEl.innerText = `⏱ ${m}m ${s}s remaining`;
    }, 1000);

    /* -------- TERMINAL LOGIC (SIRF EK BAAR) -------- */
    let term = null;
    let ws   = null;

    function connect() {

      // agar already connected hai to kuch mat karo
      if (ws && ws.readyState === WebSocket.OPEN) {
        return;
      }

      const pass = document.getElementById("sshpass").value;
      if (!pass) {
        alert("Password daalo bhai pehle 😄");
        return;
      }

      // terminal sirf ek baar create hoga
      if (!term) {
        term = new Terminal({ cursorBlink: true });
        term.open(document.getElementById("terminal"));
      }

      // connect button disable
      document.getElementById("connectBtn").disabled = true;

      ws = new WebSocket(
        "ws://<?= $_SERVER['HTTP_HOST'] ?>:32000/?" +
        "server_id=1"
      );

      ws.onopen = () => {
        ws.send(JSON.stringify({ password: pass }));
        term.write("🔐 Password verify ho raha hai...\r\n");
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

  <p>❌ Abhi koi active lab nahi hai.</p>

<?php endif; ?>

</body>
</html>

