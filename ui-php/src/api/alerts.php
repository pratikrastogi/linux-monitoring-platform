<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$conn = new mysqli("mysql","monitor","monitor123","monitoring");

$sql = "
SELECT 
 s.hostname,
 a.alert_type,
 a.message,
 a.created_at
FROM alerts a
JOIN servers s ON s.id = a.server_id
WHERE a.active = 1
ORDER BY a.created_at DESC
";

$res = $conn->query($sql);

$data = [];
while ($row = $res->fetch_assoc()) {
 $data[] = $row;
}

header('Content-Type: application/json');
echo json_encode($data);

