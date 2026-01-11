<?php
$conn = new mysqli("mysql","monitor","monitor123","monitoring");
$u = $_GET['u'] ?? '';
$q = $conn->prepare("SELECT 1 FROM users WHERE username=?");
$q->bind_param("s",$u);
$q->execute();
echo json_encode(["ok"=>$q->get_result()->num_rows==0]);

