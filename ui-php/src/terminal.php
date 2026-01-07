<?php
session_start();
if ($_SESSION['role'] != 'admin') die("Access denied");
$server_id = $_GET['id'];
?>
<!DOCTYPE html>
<html>
<head>
  <title>Server Terminal</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/xterm/css/xterm.css" />
  <script src="https://cdn.jsdelivr.net/npm/xterm/lib/xterm.js"></script>
</head>
<body>
<h3>Terminal - Server ID <?= htmlspecialchars($server_id) ?></h3>
<div id="terminal" style="width:100%;height:500px;"></div>

<script>
const term = new Terminal();
term.open(document.getElementById('terminal'));

const ws = new WebSocket(
  "ws://<?= $_SERVER['SERVER_NAME'] ?>:32000/?server_id=<?= $server_id ?>"
);

ws.onmessage = e => term.write(e.data);
term.onData(d => ws.send(d));
</script>
</body>
</html>

