<?php
session_start();
if (!isset($_SESSION['user'])) die("Login required");

$conn = new mysqli("mysql","monitor","monitor123","monitoring");

$user = $_SESSION['user'];
$uid  = $_SESSION['uid'];
$hours = intval($_POST['hours']);

if ($hours <= 0) die("Invalid request");

/* Prevent duplicate pending requests */
$chk = $conn->prepare("
  SELECT id FROM lab_extension_requests
  WHERE user_id=? AND status='PENDING'
");
$chk->bind_param("i",$uid);
$chk->execute();

if ($chk->get_result()->num_rows > 0) {
  die("You already have a pending request");
}

/* Insert request */
$q = $conn->prepare("
INSERT INTO lab_extension_requests (user_id, username, hours)
VALUES (?,?,?)
");
$q->bind_param("isi",$uid,$user,$hours);
$q->execute();

header("Location: terminal.php");
exit;

