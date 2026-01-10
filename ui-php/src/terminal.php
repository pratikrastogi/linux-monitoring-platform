<?php
session_start();

if (!isset($_SESSION['user'], $_SESSION['uid'])) {
    die("Login required");
}

$conn = new mysqli("mysql","monitor","monitor123","monitoring");
if ($conn->connect_error) die("DB error");

$user = $_SESSION['user'];
$uid  = (int)$_SESSION['uid'];

/* ===============================
   FETCH LATEST LAB SESSION
================================ */
$q = $conn->prepare("
    SELECT *
    FROM lab_sessions
    WHERE user_id = ?
    ORDER BY id DESC
    LIMIT 1
");
$q->bind_param("i", $uid);
$q->execute();
$lab = $q->get_result()->fetch_assoc();

/* fixed server id (OLD LOGIC – DO NOT TOUCH) */
$server_id = 1;

/* ===============================
   HANDLE EXTENSION REQUEST FORM
================================ */
$msg = "";

if (isset($_POST['request_extension'])) {

    $hours = (int)$_POST['hours'];
    $exp   = trim($_POST['experience']);
    $dom   = trim($_POST['domain']);
    $fb    = trim($_POST['feedback']);
    $sug   = trim($_POST['suggestion']);

    if ($hours && $exp && $dom && $fb && $sug) {

        $stmt = $conn->prepare("
            INSERT INTO lab_extension_requests
            (user_id, username, hours, status)
            VALUES (?, ?, ?, 'PENDING')
        ");
        $stmt->bind_param("isi", $uid, $user, $hours);
        $stmt->execute();
        $stmt->close();

        $msg = "✅ Request submitted. Please wait for admin approval.";
    } else {
        $msg = "❌ All fields are mandatory.";
    }
}
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
      padding:8px 14px;
      background:#4caf50;
      color:#fff;
      border:none;
      cursor:pointer;
      margin-top:10px;
    }
    .btn.red { background:#e53935; }
    .btn:disabled { background:#666; cursor:not-allowed; }
    input, textarea, select {
      width:100%;
      padding:8px;
      background:#222;
      color:#fff;
      border:1px solid #555;
      margin-top:5px;
    }
  </style>
</head>

<body>

<h3>🧪 Kubernetes Lab – User: <?= htmlspecialchars($user) ?></h3>

<?php if (!$lab): ?>

  <!-- FIRST TIME USER -->
  <p>You have not used your free lab yet.</p>
  <a class="btn" href="generate_free_access.php">
    🚀 Request Free Lab Access (60 min)
  </a>

<?php elseif ($lab['status'] === 'REQUESTED'): ?>

  <!-- PROVISIONING -->
  <p>⏳ Lab provisioning in progress…</p>
  <p>Please wait, this page will refresh automatically.</p>
  <script>
    setTimeout(() => location.reload(), 5000);
  </script>

<?php elseif ($lab['status'] === 'ACTIVE'): ?>

  <!-- ACTIVE LAB -->
  <p>✅ Lab Active</p>
  <p>Expires at: <b><?= htmlspecialchars($lab['access_expiry']) ?></b></p>
  <p id="timer"></p>

  <!-- PASSWORD PROMPT -->
  <input type="password" id="sshpass" placeholder="Lab user password">

  <button class="btn" id="connectBtn" onclick="connect()">Connect</button>

  <!-- TERMINAL -->
  <div id="terminal"></div>

  <script>
    /* TIMER (display only) */
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

    /* TERMINAL – OLD WORKING LOGIC */
    let term = null;
    let ws   = null;

    function connect() {
      if (ws && ws.readyState === WebSocket.OPEN) return;

      const pass = document.getElementById("sshpass").value;
      if (!pass) {
        alert("Please enter lab user password");
        return;
      }

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

<?php elseif ($lab['status'] === 'EXPIRED'): ?>

  <!-- FREE LAB USED -->
  <p class="red">⌛ Your free lab access has expired.</p>
  <p>You can request extended access from admin.</p>

  <?php if ($msg): ?>
    <p><?= htmlspecialchars($msg) ?></p>
  <?php endif; ?>

  <form method="post">
    <label>Current Experience in Linux</label>
    <textarea name="experience" required></textarea>

    <label>Core Technical Domain</label>
    <input name="domain" required>

    <label>Feedback about Product</label>
    <textarea name="feedback" required></textarea>

    <label>Suggestion for Improvement</label>
    <textarea name="suggestion" required></textarea>

    <label>Requested Hours</label>
    <select name="hours">
      <option value="1">1 Hour</option>
      <option value="2">2 Hours</option>
      <option value="4">4 Hours</option>
    </select>

    <button class="btn" name="request_extension">
      📨 Request Access
    </button>
  </form>

<?php elseif ($lab['status'] === 'FAILED'): ?>

  <p>❌ Lab provisioning failed. Please contact admin.</p>

<?php endif; ?>

</body>
</html>

