<?php
header("Content-Type: application/json");

$conn = new mysqli("mysql","monitor","monitor123","monitoring");
if ($conn->connect_error) {
  echo json_encode([]);
  exit;
}

/* ==========================================================
   FUNCTION: Send alert to Microsoft Teams
========================================================== */
function sendTeamsAlert($conn, $alert) {

  // Load webhook config
  $cfgRes = $conn->query("SELECT teams_webhook, enabled FROM alert_config WHERE id=1");
  if (!$cfgRes || $cfgRes->num_rows == 0) return;

  $cfg = $cfgRes->fetch_assoc();

  if ($cfg['enabled'] != 1 || empty($cfg['teams_webhook'])) return;

  // Build Teams message
  $payload = json_encode([
    "text" =>
      "🚨 **ALERT TRIGGERED**\n\n".
      "**Host:** {$alert['hostname']}\n".
      "**Type:** {$alert['alert_type']}\n".
      "**Message:** {$alert['message']}\n".
      "**Time:** {$alert['created_at']}"
  ]);

  // Send to Teams
  $ch = curl_init($cfg['teams_webhook']);
  curl_setopt($ch, CURLOPT_POST, true);
  curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
  curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_exec($ch);
  curl_close($ch);

  // Mark alert as notified (VERY IMPORTANT)
  $stmt = $conn->prepare("UPDATE alerts SET notified=1 WHERE id=?");
  $stmt->bind_param("i", $alert['id']);
  $stmt->execute();
}

/* ==========================================================
   STEP 1: Send notifications for NEW alerts only
========================================================== */
$newAlerts = $conn->query("SELECT * FROM alerts WHERE notified=0");

while ($alert = $newAlerts->fetch_assoc()) {
  sendTeamsAlert($conn, $alert);
}

/* ==========================================================
   STEP 2: Return ACTIVE alerts to UI
========================================================== */
$result = $conn->query("
  SELECT hostname, alert_type, message, created_at
  FROM alerts
  WHERE resolved = 0
  ORDER BY created_at DESC
");

$data = [];
while ($row = $result->fetch_assoc()) {
  $data[] = $row;
}

echo json_encode($data);

