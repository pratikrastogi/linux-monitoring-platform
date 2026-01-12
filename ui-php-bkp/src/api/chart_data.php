<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$conn = new mysqli("mysql","monitor","monitor123","monitoring");

$server_id = $_GET['server_id'] ?? 0;

$sql = "
SELECT 
 cpu_usage,
 mem_usage,
 disk_usage,
 collected_at
FROM server_metrics
WHERE server_id = ?
ORDER BY collected_at DESC
LIMIT 30
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $server_id);
$stmt->execute();
$res = $stmt->get_result();

$data = [];
while ($row = $res->fetch_assoc()) {
 $data[] = $row;
}

header('Content-Type: application/json');
echo json_encode(array_reverse($data));

