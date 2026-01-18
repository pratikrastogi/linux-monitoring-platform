<?php
$conn = new mysqli("mysql","monitor","monitor123","monitoring");
$m = $_GET['m'] ?? '';
$q = $conn->prepare("SELECT 1 FROM users WHERE mobile=?");
$q->bind_param("s",$m);
$q->execute();
echo json_encode(["ok"=>$q->get_result()->num_rows==0]);

