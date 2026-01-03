<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$conn = new mysqli("mysql","monitor","monitor123","monitoring");

/*
 Logic:
 - Always show servers
 - LEFT JOIN latest metrics (if exist)
*/

$sql = "
SELECT
 s.id AS server_id,
 s.hostname,
 s.ip_address,
 IFNULL(m.os_version, 'NA') AS os_version,
 IFNULL(m.uptime, 'NA') AS uptime,
 IFNULL(m.sshd_status, 'unknown') AS sshd_status,
 IFNULL(m.reachable, 0) AS reachable
FROM servers s
LEFT JOIN server_metrics m
  ON m.id = (
    SELECT id
    FROM server_metrics
    WHERE server_id = s.id
    ORDER BY collected_at DESC
    LIMIT 1
  )
WHERE s.enabled = 1
ORDER BY s.hostname
";

$res = $conn->query($sql);

$data = [];
while ($row = $res->fetch_assoc()) {
  $data[] = $row;
}

header("Content-Type: application/json");
echo json_encode($data);

