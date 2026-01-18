<?php
header('Content-Type: application/json');

// Database connection
$db = new mysqli("mysql", "monitor", "monitor123", "monitoring");
if ($db->connect_error) {
    echo json_encode(['success' => false, 'error' => 'Database connection failed']);
    exit;
}

// Validate inputs
if (!isset($_POST['name']) || !isset($_POST['mobile']) || !isset($_POST['requirement'])) {
    echo json_encode(['success' => false, 'error' => 'Missing required fields']);
    exit;
}

$name = $db->real_escape_string(trim($_POST['name']));
$mobile = $db->real_escape_string(trim($_POST['mobile']));
$email = isset($_POST['email']) ? $db->real_escape_string(trim($_POST['email'])) : '';
$requirement = $db->real_escape_string(trim($_POST['requirement']));

// Validate mobile number (10 digits)
if (!preg_match('/^[0-9]{10}$/', $mobile)) {
    echo json_encode(['success' => false, 'error' => 'Please enter a valid 10-digit mobile number']);
    exit;
}

// Validate name
if (strlen($name) < 2) {
    echo json_encode(['success' => false, 'error' => 'Please enter a valid name']);
    exit;
}

// Validate requirement
if (strlen($requirement) < 10) {
    echo json_encode(['success' => false, 'error' => 'Please provide more details about your requirement']);
    exit;
}

// Insert query into database
$query = "INSERT INTO user_queries (name, mobile, email, requirement) 
          VALUES ('$name', '$mobile', '$email', '$requirement')";

if ($db->query($query)) {
    echo json_encode([
        'success' => true,
        'message' => 'Your query has been submitted successfully! We will contact you soon.'
    ]);
} else {
    echo json_encode([
        'success' => false,
        'error' => 'Failed to submit query. Please try again later.'
    ]);
}

$db->close();
?>
