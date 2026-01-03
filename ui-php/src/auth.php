<?php
session_start();
$c = new mysqli("mysql","monitor","monitor123","monitoring");

$u = $_POST['username'];
$p = hash("sha256", $_POST['password']);

$q = $c->prepare("SELECT id,role FROM users WHERE username=? AND password=?");
$q->bind_param("ss",$u,$p);
$q->execute();
$r = $q->get_result()->fetch_assoc();

if ($r) {
 $_SESSION['user']=$u;
 $_SESSION['role']=$r['role'];
 header("Location: index.php");
} else {
 echo "Invalid login";
}

