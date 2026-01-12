<?php
session_start();
if (!isset($_SESSION['user'])) die("Login required");

$conn = new mysqli("mysql","monitor","monitor123","monitoring");
$user = $_SESSION['user'];
$uid  = $_SESSION['uid'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $hours = (int)$_POST['hours'];

    if (!in_array($hours, [1,2,4,8])) {
        die("Invalid request");
    }

    $desc = "Requested {$hours} hour(s) lab access";

    $q = $conn->prepare("
        INSERT INTO support_cases
        (user_id, category, subject, description, status)
        VALUES (?, 'LAB_EXTENSION', 'Lab Time Extension', ?, 'OPEN')
    ");
    $q->bind_param("is", $uid, $desc);
    $q->execute();

    header("Location: terminal.php");
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Request Lab Access</title>
<link rel="stylesheet" href="assets/style.css">
</head>
<body class="login-bg">
<div class="login-box">
<h3>Request More Lab Time</h3>

<form method="post">
  <select name="hours" required>
    <option value="">Select Duration</option>
    <option value="1">1 Hour</option>
    <option value="2">2 Hours</option>
    <option value="4">4 Hours</option>
    <option value="8">8 Hours</option>
  </select>
  <button>Submit Request</button>
</form>

<p style="font-size:13px">
<a href="terminal.php">Back to Terminal</a>
</p>
</div>
</body>
</html>

