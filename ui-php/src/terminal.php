<?php
session_start();
if (!isset($_SESSION['user'])) die("Login required");

$server_id = $_GET['id'] ?? null;
$user = $_SESSION['user'];
?>
<!DOCTYPE html>
<html>
<head>
  <title>Terminal</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/xterm/css/xterm.css">
  <script src="https://cdn.jsdelivr.net/npm/xterm/lib/xterm.js"></script>
</head>
<body>

<h3>Terminal – User: <?= htmlspecialchars($user) ?></h3>

<input type="password" id="sshpass" placeholder="SSH Password">
<button onclick="connect()">Connect</button>

<div id="terminal" style="height:500px;"></div>

<script>
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

</body>
</html>
