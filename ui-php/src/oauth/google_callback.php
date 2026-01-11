<?php
session_start();

/* ===============================
   OAUTH STATE CHECK
================================ */
if (
    !isset($_GET['state'], $_SESSION['oauth_state']) ||
    $_GET['state'] !== $_SESSION['oauth_state']
) {
    die("Invalid OAuth state");
}
unset($_SESSION['oauth_state']);

/* ===============================
   CONFIG
================================ */
$config = require __DIR__ . '/../config/oauth.php';
$g = $config['google'];

if (empty($_GET['code'])) {
    die("Missing auth code");
}
$code = $_GET['code'];

/* ===============================
   TOKEN EXCHANGE
================================ */
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

/* ===============================
   GOOGLE PROFILE
================================ */
$profile = json_decode(
    file_get_contents(
        "https://www.googleapis.com/oauth2/v2/userinfo?access_token=" . $token['access_token']
    ),
    true
);

if (empty($profile['email']) || !$profile['verified_email']) {
    die("Email not verified");
}

$email   = strtolower($profile['email']);
$oauthId = $profile['id'];

/* ===============================
   DB CONNECT
================================ */
$conn = new mysqli("mysql", "monitor", "monitor123", "monitoring");
if ($conn->connect_error) {
    die("DB connection failed");
}

/* ===============================
   CHECK EXISTING GOOGLE USER
================================ */
$stmt = $conn->prepare("
    SELECT id, username, role
    FROM users
    WHERE oauth_provider='google'
      AND oauth_id=?
    LIMIT 1
");
$stmt->bind_param("s", $oauthId);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows === 1) {

    /* ---------- EXISTING USER ---------- */
    $row      = $res->fetch_assoc();
    $uid      = (int)$row['id'];
    $username = $row['username'];
    $role     = $row['role'];

} else {

    /* ===============================
       GENERATE UNIQUE USERNAME
    ================================ */
    $base = preg_replace('/[^a-z0-9]/', '', explode('@', $email)[0]);
    if ($base === '') {
        $base = 'user';
    }

    $username = $base;
    $i = 1;

    while (true) {
        $chk = $conn->prepare("SELECT 1 FROM users WHERE username=? LIMIT 1");
        $chk->bind_param("s", $username);
        $chk->execute();
        if ($chk->get_result()->num_rows === 0) {
            break;
        }
        $username = $base . $i;
        $i++;
    }

    /* ===============================
       INSERT USER (USERNAME GUARANTEED)
    ================================ */
    $ins = $conn->prepare("
        INSERT INTO users
        (username, email, role, plan, enabled, oauth_provider, oauth_id)
        VALUES (?, ?, 'user', 'FREE', 1, 'google', ?)
    ");
    $ins->bind_param("sss", $username, $email, $oauthId);
    $ins->execute();

    $uid  = $conn->insert_id;
    $role = 'user';
}

/* ===============================
   ENSURE FREE LAB EXISTS
================================ */
$labChk = $conn->prepare("SELECT 1 FROM lab_sessions WHERE user_id=? LIMIT 1");
$labChk->bind_param("i", $uid);
$labChk->execute();

if ($labChk->get_result()->num_rows === 0) {
    $expiry = date("Y-m-d H:i:s", time() + 3600);

    $labIns = $conn->prepare("
        INSERT INTO lab_sessions (user_id, status, access_expiry)
        VALUES (?, 'REQUESTED', ?)
    ");
    $labIns->bind_param("is", $uid, $expiry);
    $labIns->execute();
}

/* ===============================
   SESSION
================================ */
$_SESSION['user'] = $username;
$_SESSION['uid']  = $uid;
$_SESSION['role'] = $role;

/* ===============================
   REDIRECT
================================ */
header("Location: /terminal.php");
exit;

