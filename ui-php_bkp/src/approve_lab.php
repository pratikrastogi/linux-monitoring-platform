<?php
session_start();
if ($_SESSION['role'] !== 'admin') die("Access denied");

$conn = new mysqli("mysql","monitor","monitor123","monitoring");
if ($conn->connect_error) die("DB error");

$id = (int)$_GET['id'];

/* Fetch request */
$q = $conn->query("
  SELECT user_id, hours
  FROM lab_extension_requests
  WHERE id=$id AND status='PENDING'
")->fetch_assoc();

if (!$q) die("Invalid request");

$user_id = $q['user_id'];
$hours   = (int)$q['hours'];

/* Extend lab session */
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

/* Mark approved */
$conn->query("
  UPDATE lab_extension_requests
  SET status='APPROVED'
  WHERE id=$id
");

header("Location: index.php#lab-requests");
exit;

