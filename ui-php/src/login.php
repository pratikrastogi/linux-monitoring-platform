<?php
session_start();
if ($_SESSION['user']) header("Location: index.php");
?>

<!DOCTYPE html>
<html>
<head>
<title>Login</title>
<link rel="stylesheet" href="assets/style.css">
</head>
<body class="login-bg">
<div class="login-box">
<h2>Pratik Lab Linux Monitoring</h2>
<form method="post" action="auth.php">
<input type="text" name="username" placeholder="Username" required>
<input type="password" name="password" placeholder="Password" required>
<button>Login</button>
</form>
</div>
</body>
</html>

