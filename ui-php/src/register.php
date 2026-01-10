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
<title>Register | Pratik Lab</title>
<link rel="stylesheet" href="assets/style.css">
<script>
function validateForm() {
    const pwd = document.getElementById("password").value;
    const confirm = document.getElementById("confirm_password").value;

    const regex = /^(?=.*[A-Z])(?=.*[0-9])(?=.*[@#$%^&+=!]).{8,}$/;

    if (!regex.test(pwd)) {
        alert("Password must be at least 8 characters and include uppercase, number & special character.");
        return false;
    }

    if (pwd !== confirm) {
        alert("Passwords do not match");
        return false;
    }
    return true;
}
</script>
</head>

<body class="login-bg">

<div class="login-box">
    <h2>Create Account</h2>
    <p style="font-size:13px;color:#666;">
        Free access for 1 hour Kubernetes practice
    </p>

    <form method="post" action="register_submit.php" onsubmit="return validateForm();">

        <input type="text" name="username" placeholder="Username" required>

        <input type="email" name="email" placeholder="Email Address" required>

        <input type="text" name="mobile" placeholder="Mobile Number" required>

        <input type="password" id="password" name="password" placeholder="Password" required>

        <input type="password" id="confirm_password" placeholder="Confirm Password" required>

        <button type="submit">Register</button>
    </form>

    <hr>

    <p style="font-size:13px;">
        Already have an account? <a href="login.php">Login</a>
    </p>
</div>

</body>
</html>

