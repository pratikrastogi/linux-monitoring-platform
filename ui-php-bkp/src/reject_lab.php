<?php
session_start();
if ($_SESSION['role'] !== 'admin') die("Access denied");

$conn = new mysqli("mysql","monitor","monitor123","monitoring");
$id = (int)$_GET['id'];

$conn->query("
  UPDATE lab_extension_requests
  SET status='REJECTED'
  WHERE id=$id
");

header("Location: index.php#lab-requests");
exit;

