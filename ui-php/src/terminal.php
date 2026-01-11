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
$res = $q->get_result();
$lab = ($res && $res->num_rows > 0) ? $res->fetch_assoc() : null;

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

/* ===============================
   IST TIME FORMATTER
================================ */
function toIST($utc) {
    $dt = new DateTime($utc, new DateTimeZone('UTC'));
    $dt->setTimezone(new DateTimeZone('Asia/Kolkata'));
    return $dt->format('d M Y, h:i A') . " IST";
}
?>
<!DOCTYPE html>
<html>
<head>
  <title>Lab Terminal</title>

  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/xterm/css/xterm.css">
  <script src="https://cdn.jsdelivr.net/npm/xterm/lib/xterm.js"></script>

  <style>
    body { margin:0; background:#111; color:#eee; font-family: monospace; }
    .layout { display:flex; height:100vh; }
    .left { flex:2; padding:10px; border-right:1px solid #333; }
    .right { flex:1; padding:15px; overflow-y:auto; background:#0f0f0f; }
    #terminal { background:#000; height:520px; border:1px solid #333; margin-top:10px; }
    h3,h4 { color:#4caf50; }
    .btn { padding:8px 14px; background:#4caf50; color:#fff; border:none; cursor:pointer; margin-top:10px; }
    .cmd { background:#000; border:1px solid #444; padding:8px; margin:6px 0; color:#0f0; font-size:13px; }
    .note { color:#ccc; font-size:13px; }
  </style>
</head>

<body>

<h3 style="padding:10px">🧪 Kubernetes Lab – User: <?= htmlspecialchars($user) ?></h3>

<?php if (!$lab): ?>

  <p style="padding:10px">You have not used your free lab yet.</p>
  <a class="btn" href="generate_free_access.php" style="margin-left:10px">
    🚀 Request Free Lab Access (60 min)
  </a>

<?php elseif ($lab['status'] === 'REQUESTED'): ?>

  <p style="padding:10px">⏳ Lab provisioning in progress…</p>
  <script>setTimeout(() => location.reload(), 5000);</script>

<?php elseif ($lab['status'] === 'ACTIVE'): ?>

<div class="layout">

  <!-- TERMINAL -->
  <div class="left">
    <p>✅ Lab Active</p>
    <p>Expires at: <b><?= toIST($lab['access_expiry']) ?></b></p>

    <button class="btn" onclick="connect()">🔌 Connect Terminal</button>
    <div id="terminal"></div>
  </div>

  <!-- GUIDED LAB -->
  <div class="right">
    <h3>📘 LAB-1: Docker & Container Basics</h3>

    <p class="note">
      Containers are the foundation of Kubernetes.  
      This lab ensures you understand containers before moving to Pods.
    </p>

    <h4>1️⃣ Check Runtime</h4>
    <p class="note">Verify Docker or Podman installation.</p>
    <div class="cmd">docker --version</div>
    <div class="cmd">podman --version</div>

    <h4>2️⃣ Images</h4>
    <p class="note">Images are templates used to create containers.</p>
    <div class="cmd">docker images</div>
    <div class="cmd">docker pull nginx</div>

    <h4>3️⃣ Run Container</h4>
    <p class="note">Run nginx in background.</p>
    <div class="cmd">docker run -d --name web nginx</div>
    <div class="cmd">docker ps</div>
    <div class="cmd">docker ps -a</div>

    <h4>4️⃣ Port Mapping</h4>
    <p class="note">Expose container port to host.</p>
    <div class="cmd">docker run -d -p 8080:80 nginx</div>

    <h4>5️⃣ Logs & Inspect</h4>
    <p class="note">Inspect container details and logs.</p>
    <div class="cmd">docker logs web</div>
    <div class="cmd">docker inspect web</div>

    <h4>6️⃣ Persistent Volume</h4>
    <p class="note">Persist data beyond container lifecycle.</p>
    <div class="cmd">
docker run -d \
-v /data:/usr/share/nginx/html \
-p 8081:80 nginx
    </div>

    <h4>7️⃣ Push / Pull (Concept)</h4>
    <p class="note">Share images using registries.</p>
    <div class="cmd">docker login</div>
    <div class="cmd">docker tag nginx myrepo/nginx:v1</div>
    <div class="cmd">docker push myrepo/nginx:v1</div>

    <p class="note">✅ After this, Kubernetes Pods will be easy.</p>
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
    ws.send(JSON.stringify({ password }));
    term.write("🔐 Auto authenticating...\r\n");
  };

  ws.onmessage = e => term.write(e.data);
  ws.onclose = () => { term.write("\r\n❌ Connection closed\r\n"); ws = null; };
  term.onData(d => ws && ws.send(d));
}
</script>

<?php elseif ($lab['status'] === 'EXPIRED'): ?>

  <p style="padding:10px">⌛ Lab expired.</p>
  <?php if ($msg): ?><p><?= htmlspecialchars($msg) ?></p><?php endif; ?>

  <form method="post" style="padding:10px">
    <label>Experience</label>
    <textarea name="experience" required></textarea>

    <label>Domain</label>
    <input name="domain" required>

    <label>Feedback</label>
    <textarea name="feedback" required></textarea>

    <label>Suggestion</label>
    <textarea name="suggestion" required></textarea>

    <label>Hours</label>
    <select name="hours">
      <option value="1">1 Hour</option>
      <option value="2">2 Hours</option>
      <option value="4">4 Hours</option>
    </select>

    <button class="btn" name="request_extension">📨 Request Access</button>
  </form>

<?php endif; ?>

</body>
</html>

