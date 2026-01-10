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
$chk = $conn->prepare("SELECT username FROM users WHERE username=?");
$chk->bind_param("s", $username);
$chk->execute();
if ($chk->get_result()->num_rows > 0) {
    die("Username already exists");
}

/* Password hash (keep SHA256 for compatibility) */
$hash = hash("sha256", $password);

/* 1 hour free access */
$expiry = date("Y-m-d H:i:s", strtotime("+1 hour"));

/* Insert user */
$q = $conn->prepare("
INSERT INTO users
(username,password,role,email,mobile,plan,access_expiry,enabled)
VALUES (?,?,?,?,?,'FREE',?,1)
");
$q->bind_param("ssssss",
    $username,
    $hash,
    $role = 'user',
    $email,
    $mobile,
    $expiry
);
$q->execute();

/* Queue provisioning */
$pq = $conn->prepare("
INSERT INTO provisioning_queue (username, requested_duration)
VALUES (?, 60)
");
$pq->bind_param("s", $username);
$pq->execute();

/* Create lab session */
$uid = $conn->insert_id;
$ls = $conn->prepare("
INSERT INTO lab_sessions
(user_id, username, namespace, access_start, access_expiry)
VALUES (?,?,?,?,?)
");
$namespace = "lab-" . $username;
$start = date("Y-m-d H:i:s");
$ls->bind_param("issss",
    $uid,
    $username,
    $namespace,
    $start,
    $expiry
);
$ls->execute();

$_SESSION['msg'] = "Registration successful. You can login now.";
header("Location: login.php");
exit;

