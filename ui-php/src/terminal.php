<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$conn = new mysqli("mysql","monitor","monitor123","monitoring");
if ($conn->connect_error) {
    die("Database connection failed");
}

$username = $_SESSION['user'];

/* =====================================================
   FETCH user_id SAFELY
===================================================== */
$stmt = $conn->prepare("SELECT id FROM users WHERE username=?");
$stmt->bind_param("s", $username);
$stmt->execute();
$stmt->bind_result($user_id);
$stmt->fetch();
$stmt->close();

if (!$user_id) {
    die("Invalid user session");
}

/* =====================================================
   FETCH LATEST LAB SESSION
===================================================== */
$stmt = $conn->prepare("
    SELECT *
    FROM lab_sessions
    WHERE user_id=?
    ORDER BY id DESC
    LIMIT 1
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$res = $stmt->get_result();
$lab = $res->fetch_assoc();
$stmt->close();

/* =====================================================
   CHECK EXPIRY (UTC LOGIC)
===================================================== */
$nowUtc = new DateTime("now", new DateTimeZone("UTC"));
$expired = true;

if ($lab) {
    $expiryUtc = new DateTime($lab['access_expiry'], new DateTimeZone("UTC"));
    if ($lab['status'] === 'ACTIVE' && $expiryUtc > $nowUtc) {
        $expired = false;
    }
}

/* =====================================================
   HANDLE EXTENSION REQUEST (USER FORM)
===================================================== */
$message = "";

if (isset($_POST['request_extension'])) {

    $hours = (int)$_POST['hours'];
    $experience = trim($_POST['experience']);
    $domain = trim($_POST['domain']);
    $feedback = trim($_POST['feedback']);
    $suggestion = trim($_POST['suggestion']);

    if ($hours > 0 && $experience && $domain && $feedback && $suggestion) {

        $stmt = $conn->prepare("
            INSERT INTO lab_extension_requests
            (user_id, username, hours, status)
            VALUES (?, ?, ?, 'PENDING')
        ");
        $stmt->bind_param("isi", $user_id, $username, $hours);
        $stmt->execute();
        $stmt->close();

        $message = "✅ Extension request submitted. Please wait for admin approval.";
    } else {
        $message = "❌ All fields are mandatory.";
    }
}

/* =====================================================
   IST DISPLAY ONLY
===================================================== */
date_default_timezone_set("Asia/Kolkata");
$expiry_ist = null;

if ($lab) {
    $expiry_ist = (new DateTime($lab['access_expiry'], new DateTimeZone("UTC")))
        ->setTimezone(new DateTimeZone("Asia/Kolkata"))
        ->format("d M Y, h:i:s A");
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Lab Terminal</title>
<link rel="stylesheet" href="assets/style.css">
</head>

<body>

<div class="topbar">
  <div class="logo">🖥️ Linux LAB Terminal</div>
  <div class="top-actions">
    👤 <?= htmlspecialchars($username) ?>
    <a href="logout.php" class="logout">Logout</a>
  </div>
</div>

<div class="content">

<?php if (!$expired): ?>

  <!-- ================= ACTIVE LAB ================= -->
  <div class="card">
    <h3>✅ Lab Active</h3>
    <p><b>Expires at:</b> <?= $expiry_ist ?> IST</p>
    <a href="web_terminal.php" class="button">🚀 Open Terminal</a>
  </div>

<?php else: ?>

  <!-- ================= EXPIRED LAB ================= -->
  <div class="card">
    <h3>⛔ Lab Time Expired</h3>
    <p>Your free lab time is over. Request extension to continue.</p>

    <?php if ($message): ?>
      <p style="color:green;font-weight:bold;"><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>

    <form method="post">
      <label>Current Experience in Linux</label>
      <textarea name="experience" required></textarea>

      <label>Core Technical Domain</label>
      <input type="text" name="domain" required>

      <label>Feedback about Product</label>
      <textarea name="feedback" required></textarea>

      <label>Suggestions for Improvement</label>
      <textarea name="suggestion" required></textarea>

      <label>Requested Hours</label>
      <select name="hours">
        <option value="1">1 Hour</option>
        <option value="2">2 Hours</option>
        <option value="4">4 Hours</option>
      </select>

      <button type="submit" name="request_extension">
        📨 Request Extension
      </button>
    </form>
  </div>

<?php endif; ?>

</div>

</body>
</html>

