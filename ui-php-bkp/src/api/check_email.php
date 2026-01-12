<?php
$conn = new mysqli("mysql","monitor","monitor123","monitoring");
$e = $_GET['e'] ?? '';
$q = $conn->prepare("SELECT 1 FROM users WHERE email=?");
$q->bind_param("s",$e);
$q->execute();
echo json_encode(["ok"=>$q->get_result()->num_rows==0]);

