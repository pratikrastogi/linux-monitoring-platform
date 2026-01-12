<?php
// Authentication check - ensures user is logged in
// Do NOT call session_start() here - calling page should do it

// If session not started, redirect to login
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user']) || !isset($_SESSION['uid']) || !isset($_SESSION['role'])) {
    // Not logged in, redirect to login page
    header('Location: login.php');
    exit;
}

// Database connection (for pages that need it)
$db = new mysqli("mysql","monitor","monitor123","monitoring");
if ($db->connect_error) {
    die("Database connection failed");
}

// User is authenticated, continue with page
// Session variables available:
// - $_SESSION['user'] - username
// - $_SESSION['uid'] - user ID
// - $_SESSION['role'] - user role (admin/user)
?>
