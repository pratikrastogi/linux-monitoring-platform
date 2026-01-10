<?php
session_start();
if ($_SESSION['role'] !== 'admin') die("Access denied");

$conn = new mysqli("mysql","monitor","monitor123","monitoring");
$id = (int)$_GET['id'];

$conn->query("
  UPDATE support_cases
  SET status='REJECTED', resolution='Rejected by admin'
  WHERE id=$id
");

header("Location: index.php");
exit;

