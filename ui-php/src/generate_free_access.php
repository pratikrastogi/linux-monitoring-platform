<?php
session_start();
if (!isset($_SESSION['user'], $_SESSION['uid'])) {
    die("Login required");
}

$conn = new mysqli("mysql","monitor","monitor123","monitoring");
if ($conn->connect_error) {
    die("DB connection failed");
}

$user = $_SESSION['user'];
$uid  = (int)$_SESSION['uid'];

/* 1️⃣ Check latest lab session */
$q = $conn->prepare("
    SELECT * FROM lab_sessions
    WHERE user_id = ?
    ORDER BY id DESC
    LIMIT 1
");
$q->bind_param("i", $uid);
$q->execute();
$last = $q->get_result()->fetch_assoc();

/* Active lab already exists */
if ($last && $last['status'] === 'ACTIVE') {
    die("Lab already active");
}

/* Free lab already consumed */
if ($last && $last['plan'] === 'FREE' && $last['status'] === 'EXPIRED') {
    die("Free lab already used");
}

/* 2️⃣ Create REQUESTED lab session */
$start  = date("Y-m-d H:i:s");
$expiry = date("Y-m-d H:i:s", strtotime("+60 minutes"));
$namespace = "lab-" . $user;

$ins = $conn->prepare("
    INSERT INTO lab_sessions
    (user_id, username, namespace, access_start, access_expiry, plan, status)
    VALUES (?, ?, ?, ?, ?, 'FREE', 'REQUESTED')
");
$ins->bind_param("issss", $uid, $user, $namespace, $start, $expiry);
$ins->execute();

/* 3️⃣ Return immediately */
header("Location: terminal.php");
exit;

