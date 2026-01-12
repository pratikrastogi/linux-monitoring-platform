<?php
session_start();

$conn = new mysqli("mysql","monitor","monitor123","monitoring");
if ($conn->connect_error) {
    die("DB connection failed");
}

$username = trim($_POST['username']);
$email    = trim($_POST['email']);
$mobile   = trim($_POST['mobile']);
$password = $_POST['password'];
$hash = password_hash($password, PASSWORD_BCRYPT);


if (!$username || !$email || !$password) {
    die("Invalid input");
}

/* Check if user exists */
$chk = $conn->prepare("SELECT 1 FROM users WHERE username=?");
$chk->bind_param("s", $username);
$chk->execute();
if ($chk->get_result()->num_rows > 0) {
    die("Username already exists");
}

/* Hash password */
$hash = hash("sha256", $password);

/* Insert user only */
$q = $conn->prepare("
    INSERT INTO users
    (username, password, role, email, mobile, plan, enabled)
    VALUES (?, ?, 'user', ?, ?, 'FREE', 1)
");
$q->bind_param("ssss", $username, $hash, $email, $mobile);
$q->execute();

$_SESSION['msg'] = "Registration successful. Please login.";
header("Location: login.php");
exit;

