
<?php
session_start();
if (!isset($_SESSION['user'])) header("Location: login.php");
$c = new mysqli("mysql","monitor","monitor123","monitoring");

$q = "
SELECT s.hostname, m.*
FROM servers s
JOIN server_metrics m ON s.id=m.server_id
WHERE m.collected_at=(
 SELECT MAX(collected_at)
 FROM server_metrics
 WHERE server_id=s.id
)
";

$r = $c->query($q);
echo json_encode($r->fetch_all(MYSQLI_ASSOC));
?>

