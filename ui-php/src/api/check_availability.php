<?php
header('Content-Type: application/json');

// Check if field and value are provided
if (!isset($_GET['field']) || !isset($_GET['value'])) {
    echo json_encode(['available' => false, 'error' => 'Missing parameters']);
    exit;
}

$field = $_GET['field'];
$value = $_GET['value'];

// Connect to database
$conn = new mysqli("mysql", "monitor", "monitor123", "monitoring");
if ($conn->connect_error) {
    echo json_encode(['available' => false, 'error' => 'Database connection failed']);
    exit;
}

// Validate field name to prevent SQL injection
$allowedFields = ['username', 'email', 'mobile'];
if (!in_array($field, $allowedFields)) {
    echo json_encode(['available' => false, 'error' => 'Invalid field']);
    exit;
}

// Check if value already exists
$stmt = $conn->prepare("SELECT COUNT(*) as count FROM users WHERE $field = ?");
$stmt->bind_param("s", $value);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

$available = $row['count'] == 0;

echo json_encode(['available' => $available]);

$stmt->close();
$conn->close();
?>
