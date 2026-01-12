<?php
session_start();

$conn = new mysqli("mysql","monitor","monitor123","monitoring");
if ($conn->connect_error) {
    die("DB error");
}

$username = $_POST['username'];
$password = hash("sha256", $_POST['password']);

$q = $conn->prepare("
SELECT id, role, enabled, access_expiry
FROM users
WHERE username=? AND password=?
");
$q->bind_param("ss",$username,$password);
$q->execute();
$r = $q->get_result()->fetch_assoc();

if (!$r) {
    die("Invalid username or password");
}

/* Check enabled */
if ($r['enabled'] != 1) {
    die("Account disabled or expired");
}

/* Check expiry (except admin) */
if ($r['role'] !== 'admin' && $r['access_expiry'] !== null) {
    if (strtotime($r['access_expiry']) < time()) {
        die("Your lab access has expired. Please renew.");
    }
}

/* Login success */
$_SESSION['user'] = $username;
$_SESSION['role'] = $r['role'];
$_SESSION['uid']  = $r['id'];

header("Location: index.php");
exit;

