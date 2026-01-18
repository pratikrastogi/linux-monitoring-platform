<?php
session_start();

// Database connection
$conn = new mysqli("mysql", "monitor", "monitor123", "monitoring");
if ($conn->connect_error) {
    die("Database connection failed");
}

// Get login credentials
$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';

if (empty($username) || empty($password)) {
    header("Location: login.php?error=empty");
    exit;
}

// Hash password
$password_hash = hash("sha256", $password);

// Query user
$stmt = $conn->prepare("SELECT id, username, role, enabled, access_expiry FROM users WHERE username=? AND password=?");
$stmt->bind_param("ss", $username, $password_hash);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    header("Location: login.php?error=invalid");
    exit;
}

// Check if account is enabled
if ($user['enabled'] != 1) {
    header("Location: login.php?error=disabled");
    exit;
}

// Check expiry (except admin)
if ($user['role'] !== 'admin' && $user['access_expiry'] !== null) {
    if (strtotime($user['access_expiry']) < time()) {
        header("Location: login.php?error=expired");
        exit;
    }
}

// Login successful - set session variables
$_SESSION['user'] = $user['username'];
$_SESSION['uid'] = $user['id'];
$_SESSION['role'] = $user['role'];

// Redirect to dashboard
header("Location: index.php");
exit;
?>
