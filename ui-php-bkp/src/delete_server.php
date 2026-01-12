<?php
session_start();
if ($_SESSION['role']!='admin') die("Denied");

$c = new mysqli("mysql","monitor","monitor123","monitoring");
$id=$_GET['id'];
$c->query("DELETE FROM servers WHERE id=$id");
header("Location: index.php");

