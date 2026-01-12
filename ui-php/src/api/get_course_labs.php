<?php
session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['user'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

if ($_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Not authorized']);
    exit;
}

$db = new mysqli("mysql", "monitor", "monitor123", "monitoring");
if ($db->connect_error) {
    echo json_encode(['success' => false, 'message' => 'DB Error: ' . $db->connect_error]);
    exit;
}

$course_id = isset($_GET['course_id']) ? (int)$_GET['course_id'] : 0;

if (!$course_id) {
    echo json_encode(['success' => false, 'message' => 'No course ID', 'labs' => []]);
    exit;
}

// Get active labs for the course with server info
$query = "SELECT l.id, l.lab_name, l.course_id, l.server_id, l.duration_minutes, 
       l.max_concurrent_users, l.active, s.hostname, s.ip_address
FROM labs l
LEFT JOIN servers s ON l.server_id = s.id
WHERE l.course_id = $course_id AND l.active = 1
ORDER BY l.lab_name ASC";

$result = $db->query($query);

if (!$result) {
    echo json_encode(['success' => false, 'message' => 'Query error: ' . $db->error, 'labs' => []]);
    exit;
}

$labs = [];
while ($lab = $result->fetch_assoc()) {
    $labs[] = $lab;
}

echo json_encode([
    'success' => true,
    'labs' => $labs,
    'count' => count($labs)
]);
$db->close();
?>

