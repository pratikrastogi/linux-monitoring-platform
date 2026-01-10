<?php
session_start();
if ($_SESSION['role'] !== 'admin') die("Access denied");

$conn = new mysqli("mysql","monitor","monitor123","monitoring");
if ($conn->connect_error) die("DB error");

$id = (int)$_GET['id'];

/* Fetch extension request */
$q = $conn->query("
  SELECT ler.user_id, ler.username, ler.hours
  FROM lab_extension_requests ler
  WHERE ler.id=$id AND ler.status='PENDING'
")->fetch_assoc();

if (!$q) die("Invalid request");

$user_id = $q['user_id'];
$username = $q['username'];
$hours = (int)$q['hours'];

/* Extend DB session first (UTC safe) */
$conn->query("
  UPDATE lab_sessions
  SET access_expiry = DATE_ADD(
        IF(access_expiry < UTC_TIMESTAMP(), UTC_TIMESTAMP(), access_expiry),
        INTERVAL {$hours} HOUR
      ),
      status='ACTIVE',
      plan='PAID'
  WHERE user_id=$user_id
");

/* Mark request approved */
$conn->query("
  UPDATE lab_extension_requests
  SET status='APPROVED'
  WHERE id=$id
");

/* ================================
   REMOTE TOKEN ISSUE (192.168.1.46)
================================ */
$SSH_KEY = "/root/.ssh/lab_token";
$TOKEN_HOST = "root@192.168.1.46";

$cmd = sprintf(
  'ssh -i %s %s "/opt/lab/issue_k8s_token.sh %s %d"',
  escapeshellarg($SSH_KEY),
  escapeshellarg($TOKEN_HOST),
  escapeshellarg($username),
  $hours
);

$output = shell_exec($cmd);

/* Optional: log output */
file_put_contents(
  "/var/log/lab_token.log",
  date("c") . " | $username | $output\n",
  FILE_APPEND
);

header("Location: index.php#lab-requests");
exit;

