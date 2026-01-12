<?php
session_start();

/* ================= OAUTH STATE ================= */
if (!isset($_GET['state'], $_SESSION['oauth_state']) ||
    $_GET['state'] !== $_SESSION['oauth_state']) {
    die("Invalid OAuth state");
}
unset($_SESSION['oauth_state']);

/* ================= CONFIG ================= */
$config = require __DIR__ . '/../config/oauth.php';
$g = $config['google'];

if (empty($_GET['code'])) {
    die("Missing auth code");
}
$code = $_GET['code'];

/* ================= TOKEN ================= */
$tokenResp = file_get_contents(
    "https://oauth2.googleapis.com/token",
    false,
    stream_context_create([
        'http' => [
            'method'  => 'POST',
            'header'  => "Content-Type: application/x-www-form-urlencoded",
            'content' => http_build_query([
                'client_id'     => $g['client_id'],
                'client_secret' => $g['client_secret'],
                'code'          => $code,
                'redirect_uri'  => $g['redirect_uri'],
                'grant_type'    => 'authorization_code'
            ])
        ]
    ])
);

$token = json_decode($tokenResp, true);
if (empty($token['access_token'])) {
    die("Token exchange failed");
}

/* ================= GOOGLE PROFILE ================= */
$profile = json_decode(
    file_get_contents(
        "https://www.googleapis.com/oauth2/v2/userinfo?access_token=".$token['access_token']
    ),
    true
);

if (empty($profile['email']) || !$profile['verified_email']) {
    die("Email not verified");
}

$email    = strtolower($profile['email']);
$oauthId  = $profile['id'];
$username = explode('@', $email)[0];   // SAME AS NORMAL USER

/* ================= DB ================= */
$conn = new mysqli("mysql", "monitor", "monitor123", "monitoring");
if ($conn->connect_error) {
    die("DB error");
}

/* =================================================
   CASE 1: Google user already exists
================================================= */
$q = $conn->prepare("
    SELECT id, username, role
    FROM users
    WHERE oauth_provider='google' AND oauth_id=?
    LIMIT 1
");
$q->bind_param("s", $oauthId);
$q->execute();
$res = $q->get_result();

if ($res->num_rows === 1) {
    $row = $res->fetch_assoc();
    $_SESSION['uid']  = (int)$row['id'];
    $_SESSION['user'] = $row['username'];
    $_SESSION['role'] = $row['role'];
    header("Location: /terminal.php");
    exit;
}

/* =================================================
   CASE 2: Username already exists (normal user)
================================================= */
$q = $conn->prepare("
    SELECT id, username, role
    FROM users
    WHERE username=?
    LIMIT 1
");
$q->bind_param("s", $username);
$q->execute();
$res = $q->get_result();

if ($res->num_rows === 1) {
    $row = $res->fetch_assoc();

    // Link Google to existing user
    $upd = $conn->prepare("
        UPDATE users
        SET oauth_provider='google', oauth_id=?
        WHERE id=?
    ");
    $upd->bind_param("si", $oauthId, $row['id']);
    $upd->execute();

    $_SESSION['uid']  = (int)$row['id'];
    $_SESSION['user'] = $row['username'];
    $_SESSION['role'] = $row['role'];
    header("Location: /terminal.php");
    exit;
}

/* =================================================
   CASE 3: Brand new user (NORMAL USER BEHAVIOUR)
================================================= */
$ins = $conn->prepare("
    INSERT INTO users
    (username, email, role, plan, enabled, oauth_provider, oauth_id)
    VALUES (?, ?, 'user', 'FREE', 1, 'google', ?)
");
$ins->bind_param("sss", $username, $email, $oauthId);
$ins->execute();

$uid = $conn->insert_id;

/* ================= SESSION ================= */
$_SESSION['uid']  = (int)$uid;
$_SESSION['user'] = $username;
$_SESSION['role'] = 'user';

header("Location: /terminal.php");
exit;

