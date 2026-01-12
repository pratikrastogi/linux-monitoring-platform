<?php
session_start();
$conn = new mysqli("mysql","monitor","monitor123","monitoring");

$msg = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];

    $q = $conn->prepare("SELECT username FROM users WHERE username=?");
    $q->bind_param("s",$username);
    $q->execute();

    if ($q->get_result()->num_rows === 1) {
        $token = bin2hex(random_bytes(16));
        $_SESSION['reset_token'] = $token;
        $_SESSION['reset_user']  = $username;
        $msg = "Reset token generated. Contact admin with token: $token";
    } else {
        $msg = "User not found";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Forgot Password</title>
<link rel="stylesheet" href="assets/style.css">
</head>
<body class="login-bg">

<div class="login-box">
<h2>Forgot Password</h2>

<?php if ($msg) echo "<p style='color:red'>$msg</p>"; ?>

<form method="post">
<input type="text" name="username" placeholder="Username" required>
<button>Generate Reset Token</button>
</form>

<p style="font-size:13px;">
<a href="login.php">Back to Login</a>
</p>
</div>

</body>
</html>

