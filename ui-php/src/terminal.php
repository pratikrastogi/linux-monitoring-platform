<?php
session_start();

if (!isset($_SESSION['user'], $_SESSION['uid'])) {
    die("Login required");
}

$conn = new mysqli("mysql","monitor","monitor123","monitoring");
if ($conn->connect_error) die("DB error");

$user = $_SESSION['user'];
$uid  = (int)$_SESSION['uid'];

/* FETCH LATEST LAB SESSION */
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

$server_id = 1; // DO NOT TOUCH
?>
<!DOCTYPE html>
<html>
<head>
  <title>Lab Terminal</title>

  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/xterm/css/xterm.css">
  <script src="https://cdn.jsdelivr.net/npm/xterm/lib/xterm.js"></script>

  <style>
    body {
      margin:0;
      background:#0f0f0f;
      color:#eee;
      font-family: monospace;
    }
    .layout {
      display:flex;
      height:100vh;
    }
    .terminal-pane {
      flex:2;
      padding:10px;
      border-right:1px solid #333;
    }
    .lab-pane {
      flex:1;
      padding:15px;
      overflow-y:auto;
      background:#111;
    }
    #terminal {
      height:520px;
      background:#000;
      border:1px solid #333;
      margin-top:10px;
    }
    h3,h4 {
      color:#4caf50;
    }
    .btn {
      padding:8px 14px;
      background:#4caf50;
      color:#fff;
      border:none;
      cursor:pointer;
    }
    .cmd {
      background:#000;
      border:1px solid #444;
      padding:8px;
      margin:6px 0;
      color:#0f0;
      font-size:13px;
    }
    .note {
      color:#ccc;
      font-size:13px;
    }
  </style>
</head>

<body>

<?php if ($lab && $lab['status'] === 'ACTIVE'): ?>

<div class="layout">

  <!-- TERMINAL SIDE -->
  <div class="terminal-pane">
    <h3>🧪 Kubernetes Lab Terminal</h3>
    <p>User: <b><?= htmlspecialchars($user) ?></b></p>
    <p>Expires: <?= htmlspecialchars($lab['access_expiry']) ?></p>

    <button class="btn" onclick="connect()">🔌 Connect Terminal</button>

    <div id="terminal"></div>
  </div>

  <!-- LAB GUIDE SIDE -->
  <div class="lab-pane">
    <h3>📘 LAB-1: Container Fundamentals (Mandatory)</h3>

    <p class="note">
      ⚠️ Before Kubernetes, you <b>MUST</b> understand containers.
      This lab will prepare you for Docker / Podman usage.
    </p>

    <h4>1️⃣ Check Container Runtime</h4>
    <div class="cmd">docker --version</div>
    <div class="cmd">podman --version</div>

    <h4>2️⃣ Images</h4>
    <div class="cmd">docker images</div>
    <div class="cmd">docker pull nginx</div>

    <h4>3️⃣ Run a Container</h4>
    <div class="cmd">docker run -d --name web nginx</div>
    <div class="cmd">docker ps</div>
    <div class="cmd">docker ps -a</div>

    <h4>4️⃣ Port Mapping</h4>
    <div class="cmd">docker run -d -p 8080:80 nginx</div>

    <h4>5️⃣ Logs & Inspect</h4>
    <div class="cmd">docker logs web</div>
    <div class="cmd">docker inspect web</div>

    <h4>6️⃣ Persistent Volume</h4>
    <div class="cmd">
docker run -d \
-v /data:/usr/share/nginx/html \
-p 8081:80 nginx
    </div>

    <h4>7️⃣ Push / Pull (Concept)</h4>
    <div class="cmd">docker login</div>
    <div class="cmd">docker tag nginx myrepo/nginx:v1</div>
    <div class="cmd">docker push myrepo/nginx:v1</div>

    <p class="note">
      ✅ After completing LAB-1, you are ready for Kubernetes Pods.
    </p>
  </div>

</div>

<script>
  let term = null;
  let ws   = null;

  function connect() {
    if (ws && ws.readyState === WebSocket.OPEN) return;

    const username = "<?= $user ?>";
    const password = "k8s" + username + "@123!";

    if (!term) {
      term = new Terminal({ cursorBlink: true });
      term.open(document.getElementById("terminal"));
    }

    ws = new WebSocket(
      "wss://kubearena.pratikrastogi.co.in/terminal?" +
      "server_id=<?= $server_id ?>&user=<?= $user ?>"
    );

 
    ws.onopen = () => {
      ws.send(JSON.stringify({ password: password }));
      term.write("🔐 Auto authenticating...\r\n");
    };

    ws.onmessage = e => term.write(e.data);

    ws.onclose = () => {
      term.write("\r\n❌ Connection closed\r\n");
      ws = null;
    };

    term.onData(d => {
      if (ws && ws.readyState === WebSocket.OPEN) {
        ws.send(d);
      }
    });
  }
</script>

<?php else: ?>

<p style="padding:20px">⏳ Lab not active or expired.</p>

<?php endif; ?>

</body>
</html>

