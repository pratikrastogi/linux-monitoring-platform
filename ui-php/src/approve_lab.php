<?php
session_start();
if ($_SESSION['role'] !== 'admin') die("Access denied");

$conn = new mysqli("mysql","monitor","monitor123","monitoring");
$id = (int)$_GET['id'];

/* Fetch request */
$q = $conn->query("
  SELECT sc.user_id, sc.description
  FROM support_cases sc
  WHERE sc.id=$id AND sc.status='OPEN'
")->fetch_assoc();

if (!$q) die("Invalid request");

/* Extract hours */
preg_match('/(\d+)/', $q['description'], $m);
$hours = (int)$m[1];

/* Extend lab session */
$conn->query("
  UPDATE lab_sessions
  SET access_expiry = DATE_ADD(
        IF(access_expiry < NOW(), NOW(), access_expiry),
        INTERVAL {$hours} HOUR
      ),
      status='ACTIVE',
      plan='PAID'
  WHERE user_id={$q['user_id']}
");

/* Update request */
$conn->query("
  UPDATE support_cases
  SET status='APPROVED', resolution='Approved by admin'
  WHERE id=$id
");

header("Location: index.php");
exit;

