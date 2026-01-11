<?php
session_start();

/* ---------- OAUTH STATE ---------- */
if (!isset($_GET['state'], $_SESSION['oauth_state']) || $_GET['state'] !== $_SESSION['oauth_state']) {
    die("Invalid OAuth state");
}
unset($_SESSION['oauth_state']);

/* ---------- CONFIG ---------- */
$config = require __DIR__ . '/../config/oauth.php';
$g = $config['google'];

$code = $_GET['code'] ?? null;
if (!$code) die("Missing auth code");

/* ---------- TOKEN ---------- */
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
if (!isset($token['access_token'])) die("Token exchange failed");

/* ---------- PROFILE ---------- */
$profile = json_decode(
    file_get_contents(
        "https://www.googleapis.com/oauth2/v2/userinfo?access_token=".$token['access_token']
    ),
    true
);

if (empty($profile['email']) || !$profile['verified_email']) {
    die("Email not verified");
}

$email   = strtolower($profile['email']);
$oauthId = $profile['id'];

/* ---------- DB ---------- */
$conn = new mysqli("mysql", "monitor", "monitor123", "monitoring");
if ($conn->connect_error) die("DB error");

/* ---------- USER LOOKUP ---------- */
$q = $conn->prepare("
    SELECT id, username, role
    FROM users
    WHERE oauth_provider='google' AND oauth_id=?
");
$q->bind_param("s", $oauthId);
$q->execute();
$res = $q->get_result();

if ($res->num_rows === 0) {

    /* ---------- USERNAME GENERATE ---------- */
    $base = preg_replace('/[^a-z0-9]/', '', explode('@', $email)[0]);
    if ($base === '') $base = 'user';

    $username = $base;
    $i = 1;

    while (true) {
        $chk = $conn->prepare("SELECT 1 FROM users WHERE username=?");
        $chk->bind_param("s", $username);
        $chk->execute();
        if ($chk->get_result()->num_rows === 0) break;
        $username = $base . $i++;
    }

    /* ---------- INSERT USER (GUARANTEED USERNAME) ---------- */
    $ins = $conn->prepare("
        INSERT INTO users
        (username, email, role, plan, enabled, oauth_provider, oauth_id)
        VALUES (?, ?, 'user', 'FREE', 1, 'google', ?)
    ");
    $ins->bind_param("sss", $username, $email, $oauthId);
    $ins->execute();

    $uid  = $conn->insert_id;
    $role = 'user';

} else {

    $row = $res->fetch_assoc();
    $uid      = (int)$row['id'];
    $username = $row['username'];
    $role     = $row['role'];
}

/* ---------- AUTO FREE LAB ---------- */
$chkLab = $conn->prepare("SELECT 1 FROM lab_sessions WHERE user_id=?");
$chkLab->bind_param("i", $uid);
$chkLab->execute();

if ($chkLab->get_result()->num_rows === 0) {
    $exp = date("Y-m-d H:i:s", time() + 3600);
    $lab = $conn->prepare("
        INSERT INTO lab_sessions (user_id, status, access_expiry)
        VALUES (?, 'REQUESTED', ?)
    ");
    $lab->bind_param("is", $uid, $exp);
    $lab->execute();
}

/* ---------- SESSION ---------- */
$_SESSION['user'] = $username;
$_SESSION['uid']  = $uid;
$_SESSION['role'] = $role;

header("Location: /terminal.php");
exit;

