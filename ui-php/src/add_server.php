<?php
session_start();
if ($_SESSION['role'] != 'admin') die("Access denied");

$conn = new mysqli("mysql","monitor","monitor123","monitoring");
if ($conn->connect_error) die("Database connection failed");

/* =========================
   Default values
========================= */
$edit = false;
$server = [
  'id' => '',
  'hostname' => '',
  'ip_address' => '',
  'ssh_user' => '',
  'ssh_password' => ''
];

/* =========================
   Load server (EDIT MODE)
========================= */
if (isset($_GET['id'])) {
  $edit = true;
  $stmt = $conn->prepare("SELECT * FROM servers WHERE id=?");
  $stmt->bind_param("i", $_GET['id']);
  $stmt->execute();
  $server = $stmt->get_result()->fetch_assoc();
}

/* =========================
   Save server
========================= */
if ($_POST) {

  if (!empty($_POST['id'])) {
    // UPDATE
    $q = $conn->prepare("
      UPDATE servers
      SET hostname=?, ip_address=?, ssh_user=?, ssh_password=?
      WHERE id=?
    ");
    $q->bind_param(
      "ssssi",
      $_POST['hostname'],
      $_POST['ip'],
      $_POST['user'],
      $_POST['password'],   // plain text for now
      $_POST['id']
    );
  } else {
    // INSERT
    $q = $conn->prepare("
      INSERT INTO servers (hostname, ip_address, ssh_user, ssh_password)
      VALUES (?,?,?,?)
    ");
    $q->bind_param(
      "ssss",
      $_POST['hostname'],
      $_POST['ip'],
      $_POST['user'],
      $_POST['password']    // plain text for now
    );
  }

  $q->execute();
  header("Location: add_servers.php");
  exit;
}

/* =========================
   Fetch server list
========================= */
$servers = $conn->query("SELECT id, hostname, ip_address, ssh_user FROM servers ORDER BY id DESC");
?>
<!DOCTYPE html>
<html>
<head>
  <title>Manage Servers</title>
  <meta charset="utf-8">
  <style>
    .advanced-form {
      max-width:520px;
      margin:auto;
    }

    .advanced-form h2 {
      color:#1d2671;
      margin-bottom:20px;
    }

    .advanced-form label {
      font-weight:600;
      margin-top:14px;
      display:block;
      color:#444;
    }

    .password-box {
      position:relative;
    }

    .password-box span {
      position:absolute;
      right:12px;
      top:50%;
      transform:translateY(-50%);
      cursor:pointer;
    }

    .primary-btn {
      background:linear-gradient(135deg,#1d2671,#c33764);
      border:none;
      color:white;
      padding:12px;
      font-size:16px;
      margin-top:20px;
      border-radius:8px;
      cursor:pointer;
      width:100%;
    }

    .edit-link {
      color:#1d2671;
      font-weight:bold;
      text-decoration:none;
    }

    .edit-link:hover {
      text-decoration:underline;
    }
  </style>
</head>
<body>

<div class="dashboard">

  <!-- ADD / EDIT FORM -->
  <div class="card advanced-form">
    <h2><?= $edit ? "Edit Server" : "Add New Server" ?></h2>

    <form method="post">
      <input type="hidden" name="id" value="<?= htmlspecialchars($server['id']) ?>">

      <label>Hostname</label>
      <input name="hostname" required
             value="<?= htmlspecialchars($server['hostname']) ?>"
             placeholder="server01.example.com">

      <label>IP Address</label>
      <input name="ip" required
             value="<?= htmlspecialchars($server['ip_address']) ?>"
             placeholder="192.168.1.10">

      <label>SSH Username</label>
      <input name="user" required
             value="<?= htmlspecialchars($server['ssh_user']) ?>"
             placeholder="root / admin">

      <label>SSH Password</label>
      <div class="password-box">
        <input type="password" id="password" name="password" required
               value="<?= htmlspecialchars($server['ssh_password']) ?>">
        <span onclick="togglePassword()">👁</span>
      </div>

      <button class="primary-btn">
        <?= $edit ? "Save Changes" : "Add Server" ?>
      </button>
    </form>
  </div>

  <!-- SERVER LIST -->
  <div class="card">
    <h3>Configured Servers</h3>

    <table class="modern-table">
      <thead>
        <tr>
          <th>Hostname</th>
          <th>IP Address</th>
          <th>User</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php while($row = $servers->fetch_assoc()): ?>
        <tr>
          <td><?= htmlspecialchars($row['hostname']) ?></td>
          <td><?= htmlspecialchars($row['ip_address']) ?></td>
          <td><?= htmlspecialchars($row['ssh_user']) ?></td>
          <td>
            <a class="edit-link" href="add_servers.php?id=<?= $row['id'] ?>">Edit</a>
          </td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>

</div>

<script>
function togglePassword() {
  const p = document.getElementById("password");
  p.type = p.type === "password" ? "text" : "password";
}
</script>

</body>
</html>

