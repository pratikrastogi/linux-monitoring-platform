<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$conn = new mysqli("mysql","monitor","monitor123","monitoring");
if ($conn->connect_error) die("DB Error");

$username = $_SESSION['user'];
$user_id  = $_SESSION['user_id'];

/* ===============================
   FETCH LAB SESSION
================================ */
$res = $conn->query("
  SELECT * FROM lab_sessions
  WHERE user_id=$user_id
  ORDER BY id DESC LIMIT 1
");

$session = $res->fetch_assoc();
$now = new DateTime("now", new DateTimeZone("UTC"));

$expired = true;
if ($session) {
    $expiry = new DateTime($session['access_expiry'], new DateTimeZone("UTC"));
    if ($expiry > $now && $session['status'] == 'ACTIVE') {
        $expired = false;
    }
}

/* ===============================
   HANDLE EXTENSION REQUEST
================================ */
if (isset($_POST['request_extension'])) {

    $hours = (int)$_POST['hours'];
    $experience = $_POST['experience'];
    $domain = $_POST['domain'];
    $feedback = $_POST['feedback'];
    $suggestion = $_POST['suggestion'];

    $stmt = $conn->prepare("
      INSERT INTO lab_extension_requests
      (user_id, username, hours, status)
      VALUES (?, ?, ?, 'PENDING')
    ");
    $stmt->bind_param("isi", $user_id, $username, $hours);
    $stmt->execute();

    $message = "✅ Extension request submitted for admin approval";
}

/* ===============================
   IST DISPLAY
================================ */
date_default_timezone_set("Asia/Kolkata");
$expiry_ist = $session
  ? (new DateTime($session['access_expiry'], new DateTimeZone("UTC")))
      ->setTimezone(new DateTimeZone("Asia/Kolkata"))
      ->format("Y-m-d h:i:s A")
  : null;
?>
<!DOCTYPE html>
<html>
<head>
<title>Lab Terminal</title>
<link rel="stylesheet" href="assets/style.css">
</head>
<body>

<div class="topbar">
  <div class="logo">🖥️ Linux Lab Terminal</div>
  <div class="top-actions">
    <?= htmlspecialchars($username) ?>
    <a href="logout.php" class="logout">Logout</a>
  </div>
</div>

<div class="content">

<?php if (!$expired): ?>

  <div class="card">
    <h3>✅ Lab Active</h3>
    <p><b>Expires at:</b> <?= $expiry_ist ?> IST</p>
    <a class="button" href="web_terminal.php">🚀 Open Terminal</a>
  </div>

<?php else: ?>

  <div class="card">
    <h3>⛔ Free Lab Time Expired</h3>
    <p>Request extension to continue using the lab.</p>

    <?php if (!empty($message)) echo "<p style='color:green'>$message</p>"; ?>

    <form method="post">
      <label>Current Experience in Linux</label>
      <textarea name="experience" required></textarea>

      <label>Core Technical Domain</label>
      <input name="domain" required>

      <label>Feedback about Product</label>
      <textarea name="feedback" required></textarea>

      <label>Suggestion for Improvement</label>
      <textarea name="suggestion" required></textarea>

      <label>Requested Hours</label>
      <select name="hours">
        <option value="1">1 Hour</option>
        <option value="2">2 Hours</option>
        <option value="4">4 Hours</option>
      </select>

      <button name="request_extension">📨 Request Extension</button>
    </form>
  </div>

<?php endif; ?>

</div>
</body>
</html>

