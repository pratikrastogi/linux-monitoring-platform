<?php
session_start();
if (!isset($_SESSION['user'])) die("Login required");

$conn = new mysqli("mysql","monitor","monitor123","monitoring");
if ($conn->connect_error) die("DB error");

$user = $_SESSION['user'];
$uid  = $_SESSION['uid'];

/* 1️⃣ Check existing lab session */
$q = $conn->prepare("
  SELECT * FROM lab_sessions
  WHERE user_id=?
  ORDER BY id DESC
  LIMIT 1
");
$q->bind_param("i", $uid);
$q->execute();
$existing = $q->get_result()->fetch_assoc();

if ($existing && $existing['status'] === 'ACTIVE') {
    die("Lab already active");
}

if ($existing && $existing['plan'] === 'FREE' && $existing['status'] === 'EXPIRED') {
    die("Free lab already used");
}

/* 2️⃣ Create lab session FIRST (source of truth) */
$start  = date("Y-m-d H:i:s");
$expiry = date("Y-m-d H:i:s", strtotime("+60 minutes"));
$namespace = "lab-" . $user;

$ins = $conn->prepare("
INSERT INTO lab_sessions
(user_id, username, namespace, access_start, access_expiry, plan, status)
VALUES (?, ?, ?, ?, ?, 'FREE', 'ACTIVE')
");
$ins->bind_param("issss", $uid, $user, $namespace, $start, $expiry);
$ins->execute();

$session_id = $conn->insert_id;

/* 3️⃣ Trigger provisioning (SYNC SSH execution) */
$target = $conn->query("SELECT * FROM provision_target WHERE id=1")->fetch_assoc();
if (!$target) {
    die("Provisioning target not configured");
}

$host = $target['host_ip'];
$ssh_user = $target['ssh_user'];
$ssh_pass = $target['ssh_password'];

$cmd = "
sshpass -p '{$ssh_pass}' ssh -o StrictHostKeyChecking=no
{$ssh_user}@{$host}
'bash /opt/lab/create_lab_user.sh {$user} 60'
";

exec($cmd, $output, $ret);

/* 4️⃣ Handle result */
if ($ret !== 0) {
    // Rollback session
    $conn->query("UPDATE lab_sessions SET status='FAILED' WHERE id={$session_id}");
    die("Provisioning failed. Please contact admin.");
}

/* 5️⃣ Success → redirect to terminal */
header("Location: terminal.php");
exit;

