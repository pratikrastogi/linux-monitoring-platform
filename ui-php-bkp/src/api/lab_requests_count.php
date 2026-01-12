<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['count' => 0]);
    exit;
}

$conn = new mysqli("mysql", "monitor", "monitor123", "monitoring");
if ($conn->connect_error) {
    echo json_encode(['count' => 0, 'error' => 'Database connection failed']);
    exit;
}

$result = $conn->query("SELECT COUNT(*) as count FROM lab_extension_requests WHERE status='PENDING'");
$row = $result->fetch_assoc();

echo json_encode(['count' => (int)$row['count']]);

$conn->close();
?>
