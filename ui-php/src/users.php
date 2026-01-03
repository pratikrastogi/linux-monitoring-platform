<?php
session_start();
if ($_SESSION['role']!='admin') die("Denied");

$c=new mysqli("mysql","monitor","monitor123","monitoring");

if($_POST){
 $p=hash("sha256",$_POST['password']);
 $c->query("INSERT INTO users(username,password,role)
 VALUES('{$_POST['user']}','$p','user')");
}

$r=$c->query("SELECT username,role FROM users");
?>
<form method="post">
<input name="user" placeholder="Username">
<input name="password" placeholder="Password">
<button>Add User</button>
</form>

<ul>
<?php while($u=$r->fetch_assoc()) echo "<li>{$u['username']} ({$u['role']})</li>"; ?>
</ul>

