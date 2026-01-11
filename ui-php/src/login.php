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
<title>Login | KubeArena</title>
<link rel="stylesheet" href="assets/style.css">
</head>
<body class="login-bg">

<div class="login-box">
  <h2>Welcome to <span>KubeArena</span></h2>
  <p style="font-size:13px;color:#666;">
    Enterprise Linux & Kubernetes Practice Labs
  </p>

  <form method="post" action="auth.php">
    <div class="field">
      <label>Username</label>
      <input type="text" name="username" required placeholder=" ">
    </div>

    <div class="field">
      <label>Password</label>
      <input type="password" name="password" required placeholder=" ">
    </div>

    <button type="submit">Login</button>
  </form>

  <div class="oauth">
     <a href="oauth/google_login.php">
     	<button type="button">Continue with Google</button>
     </a>
     <button disabled>Continue with Zoho (Coming Soon)</button>
  </div>

  <hr>

  <p style="font-size:13px;">
    New user? <a href="register.php">Create Account</a>
  </p>
</div>

</body>
</html>

