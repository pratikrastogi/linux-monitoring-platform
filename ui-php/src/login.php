<?php
session_start();
if (isset($_SESSION['user'])) {
    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Login | Pratik Lab</title>
<link rel="stylesheet" href="assets/style.css">
</head>
<body class="login-bg">

<div class="login-box">
    <h2>Pratik Kubernetes Lab</h2>
    <p style="font-size:13px;color:#666;">
        Practice Linux & Kubernetes on Live Environment
    </p>

    <form method="post" action="auth.php">
        <input type="text" name="username" placeholder="Username" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit">Login</button>
    </form>

    <hr>

    <p style="font-size:13px;">
        New user? <a href="register.php">Create Account</a>
    </p>

    <p style="font-size:13px;">
        <a href="forgot_password.php">Forgot Password?</a>
    </p>
</div>

</body>
</html>

