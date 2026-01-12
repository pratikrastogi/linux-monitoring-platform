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
<title>Register | KubeArena</title>
<link rel="stylesheet" href="assets/style.css">
<script src="assets/register.js" defer></script>
</head>

<body class="login-bg">

<div class="login-box">
  <h2>Create <span>KubeArena</span> Account</h2>
  <p style="font-size:13px;color:#666;">
    Free 1-Hour Kubernetes Lab Access
  </p>

<form method="post" action="register_submit.php">

  <div class="field">
    <input type="text" name="username" id="username" required placeholder=" ">
    <label>Username</label>
    <span class="status" id="uStatus"></span>
  </div>

  <div class="field">
    <input type="email" name="email" id="email" required placeholder=" ">
    <label>Email</label>
    <span class="status" id="eStatus"></span>
  </div>

  <div class="field">
    <input type="text" name="mobile" id="mobile" required placeholder=" ">
    <label>Mobile</label>
    <span class="status" id="mStatus"></span>
  </div>

  <div class="field">
    <input type="password" name="password" id="password" required placeholder=" ">
    <label>Password</label>
    <span class="status" id="pStatus"></span>
  </div>

  <button type="submit">Register</button>
</form>

<hr>

<p style="font-size:13px;">
  Already have an account? <a href="login.php">Login</a>
</p>

</div>
</body>
</html>

