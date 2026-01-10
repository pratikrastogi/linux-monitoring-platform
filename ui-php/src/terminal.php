<?php
session_start();
if (!isset($_SESSION['user'], $_SESSION['uid'])) {
    die("Login required");
}

$conn = new mysqli("mysql","monitor","monitor123","monitoring");
if ($conn->connect_error) {
    die("DB error");
}

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
</head>

<body>

<h2>🧪 Kubernetes Lab – <?= htmlspecialchars($user) ?></h2>

<?php if (!$lab): ?>

  <!-- NO LAB -->
  <p>You have not used your free lab yet.</p>
  <a class="btn" href="generate_free_access.php">🚀 Generate Free Access (60 min)</a>

<?php elseif ($lab['status'] === 'REQUESTED'): ?>

  <!-- REQUESTED -->
  <p>⏳ Provisioning in progress…</p>
  <p>Please wait. This page will refresh automatically.</p>
  <script>
    setTimeout(() => location.reload(), 5000);
  </script>

<?php elseif ($lab['status'] === 'ACTIVE'): ?>

  <!-- ACTIVE -->
  <p>✅ Lab Active</p>
  <p>Expires at: <b><?= $lab['access_expiry'] ?></b></p>

  <div id="terminal" style="height:500px;border:1px solid #333;"></div>

  <script>
    const expiry = new Date("<?= $lab['access_expiry'] ?>").getTime();

    function updateTimer() {
      const now = Date.now();
      const diff = expiry - now;

      if (diff <= 0) {
        location.reload();
        return;
      }

      const mins = Math.floor(diff / 60000);
      const secs = Math.floor((diff % 60000) / 1000);
      document.getElementById("timer").innerText =
        `${mins}m ${secs}s remaining`;
    }

    setInterval(updateTimer, 1000);
  </script>

  <p id="timer"></p>

  <script>
    let term = new Terminal({ cursorBlink: true });
    term.open(document.getElementById('terminal'));

    const ws = new WebSocket(
      "ws://<?= $_SERVER['HTTP_HOST'] ?>:32000/?user=<?= $user ?>"
    );

    ws.onmessage = e => term.write(e.data);
    term.onData(d => ws.send(d));
  </script>

<?php elseif ($lab['status'] === 'FAILED'): ?>

  <!-- FAILED -->
  <p>❌ Provisioning failed.</p>
  <p>Please contact admin or try later.</p>

<?php elseif ($lab['status'] === 'EXPIRED'): ?>

  <!-- EXPIRED -->
  <p>⌛ Your free lab has expired.</p>
  <a class="btn" href="request_extension.php">Request More Time</a>

<?php elseif ($lab['status'] === 'REVOKED'): ?>

  <!-- REVOKED -->
  <p>🚫 Your lab access was revoked by admin.</p>

<?php endif; ?>

</body>
</html>

