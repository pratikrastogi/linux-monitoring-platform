<?php
session_start();

$conn = new mysqli("mysql","monitor","monitor123","monitoring");
if ($conn->connect_error) {
    die("DB Connection failed");
}

$username = trim($_POST['username']);
$email    = trim($_POST['email']);
$mobile   = trim($_POST['mobile']);
$password = $_POST['password'];

/* Basic validation */
if (!$username || !$email || !$password) {
    die("Invalid input");
}

/* Check if user exists */
$chk = $conn->prepare("SELECT id FROM users WHERE username=?");
$chk->bind_param("s", $username);
$chk->execute();
if ($chk->get_result()->num_rows > 0) {
    die("Username already exists");
}

/* Hash password (keep SHA256 for now) */
$hash = hash("sha256", $password);

/* Define role explicitly */
$role = 'user';

/* Insert ONLY user (no lab, no expiry) */
$q = $conn->prepare("
INSERT INTO users
(username, password, role, email, mobile, plan, enabled)
VALUES (?, ?, ?, ?, ?, 'FREE', 1)
");
$q->bind_param(
    "sssss",
    $username,
    $hash,
    $role,
    $email,
    $mobile
);
$q->execute();

/* Done */
$_SESSION['msg'] = "Registration successful. You can now login and claim your free lab access.";
header("Location: login.php");
exit;

