<?php
session_start();
require_once '../auth.php';

// Only allow admins to access this
if ($_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$db = new mysqli("mysql", "monitor", "monitor123", "monitoring");
if ($db->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database error']);
    exit;
}

$course_id = isset($_GET['course_id']) ? (int)$_GET['course_id'] : 0;

if (!$course_id) {
    echo json_encode(['success' => false, 'message' => 'No course ID provided']);
    exit;
}

// Get active labs for the course with server info
$result = $db->query("
    SELECT l.id, l.lab_name, l.course_id, l.server_id, l.duration_minutes, 
           l.max_concurrent_users, l.active, s.hostname
    FROM labs l
    LEFT JOIN servers s ON l.server_id = s.id
    WHERE l.course_id = $course_id AND l.active = 1
    ORDER BY l.lab_name ASC
");

if (!$result) {
    echo json_encode(['success' => false, 'message' => 'Query error']);
    exit;
}

$labs = [];
while ($lab = $result->fetch_assoc()) {
    $labs[] = $lab;
}

echo json_encode([
    'success' => true,
    'labs' => $labs
]);
?>
