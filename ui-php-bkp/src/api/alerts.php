<?php
header("Content-Type: application/json");
ini_set('display_errors', 1);
error_reporting(E_ALL);

$conn = new mysqli("mysql","monitor","monitor123","monitoring");
if ($conn->connect_error) {
  echo json_encode([]);
  exit;
}

/* =====================================================
   Send Microsoft Teams Notification
===================================================== */
function sendTeams($conn, $title, $host, $msg, $time) {

  $cfg = $conn->query("SELECT teams_webhook, enabled FROM alert_config WHERE id=1")
              ->fetch_assoc();

  if (!$cfg || !$cfg['enabled'] || empty($cfg['teams_webhook'])) return;

  $payload = json_encode([
    "text" =>
      "🚨 **$title**\n\n".
      "**Host:** $host\n".
      "**Message:** $msg\n".
      "**Time:** $time"
  ]);

  $ch = curl_init($cfg['teams_webhook']);
  curl_setopt($ch, CURLOPT_POST, true);
  curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
  curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_exec($ch);
  curl_close($ch);
}

/* =====================================================
   STEP 1: Send NEW DOWN alerts (once)
===================================================== */
$newAlerts = $conn->query("
  SELECT a.id, a.alert_type, a.message, a.created_at,
         s.hostname
  FROM alerts a
  JOIN servers s ON s.id = a.server_id
  WHERE a.active = 1
    AND a.notified = 0
");

while ($a = $newAlerts->fetch_assoc()) {

  sendTeams(
    $conn,
    $a['alert_type'],
    $a['hostname'],
    $a['message'],
    $a['created_at']
  );

  // Mark as notified
  $stmt = $conn->prepare("UPDATE alerts SET notified=1 WHERE id=?");
  $stmt->bind_param("i", $a['id']);
  $stmt->execute();
}

/* =====================================================
   STEP 2: Detect HOST RECOVERY (UP)
===================================================== */
$downHosts = $conn->query("
  SELECT a.id, a.server_id, s.hostname, s.ip_address
  FROM alerts a
  JOIN servers s ON s.id = a.server_id
  WHERE a.alert_type = 'HOST_DOWN'
    AND a.active = 1
");

while ($h = $downHosts->fetch_assoc()) {

  // Ping to check recovery
  exec("ping -c 1 -W 1 {$h['ip_address']} >/dev/null 2>&1", $o, $status);

  if ($status === 0) {

    // Send recovery notification
    sendTeams(
      $conn,
      "HOST RECOVERED",
      $h['hostname'],
      "Host is reachable again",
      date("Y-m-d H:i:s")
    );

    // Mark alert inactive
    $stmt = $conn->prepare("UPDATE alerts SET active=0 WHERE id=?");
    $stmt->bind_param("i", $h['id']);
    $stmt->execute();
  }
}

/* =====================================================
   STEP 3: Return ACTIVE alerts for UI
===================================================== */
$result = $conn->query("
  SELECT s.hostname, a.alert_type, a.message, a.created_at
  FROM alerts a
  JOIN servers s ON s.id = a.server_id
  WHERE a.active = 1
  ORDER BY a.created_at DESC
");

$data = [];
while ($row = $result->fetch_assoc()) {
  $data[] = $row;
}

echo json_encode($data);

