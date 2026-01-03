<?php
session_start();
if ($_SESSION['role']!='admin') die("Access denied");

$c = new mysqli("mysql","monitor","monitor123","monitoring");

if ($_POST) {
 $p = hash("sha256", $_POST['password']);
 $q = $c->prepare("
 INSERT INTO servers (hostname, ip_address, ssh_user, ssh_password)
 VALUES (?,?,?,?)
 ");
 $q->bind_param("ssss",
  $_POST['hostname'],
  $_POST['ip'],
  $_POST['user'],
  $p
 );
 $q->execute();
 header("Location: index.php");
}
?>

<form method="post">
<input name="hostname" placeholder="Hostname">
<input name="ip" placeholder="IP Address">
<input name="user" placeholder="SSH User">
<input name="password" placeholder="SSH Password" type="password">
<button>Add Server</button>
</form>

