<?php
session_start();

/* ===============================
   OAUTH STATE VALIDATION
================================ */
if (
    !isset($_GET['state']) ||
    !isset($_SESSION['oauth_state']) ||
    $_GET['state'] !== $_SESSION['oauth_state']
) {
    die("Invalid OAuth state");
}
unset($_SESSION['oauth_state']);

/* ===============================
   LOAD OAUTH CONFIG
================================ */
$config = require __DIR__ . '/../config/oauth.php';
$g = $config['google'];

$code = $_GET['code'] ?? null;
if (!$code) {
    die("Missing authorization code");
}

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
if (!isset($token['access_token'])) {
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

if (!isset($profile['email']) || empty($profile['verified_email'])) {
    die("Google email not verified");
}

$email   = strtolower(trim($profile['email']));
$oauthId = $profile['id'];

/* ===============================
   DB CONNECT
================================ */
$conn = new mysqli("mysql", "monitor", "monitor123", "monitoring");
if ($conn->connect_error) {
    die("DB connection failed");
}

/* ===============================
   HELPER: UNIQUE USERNAME
================================ */
function generateUsername($email, $conn) {
    $base = preg_replace('/[^a-z0-9]/i', '', explode('@', $email)[0]);
    $username = $base;
    $i = 1;

    while (true) {
        $chk = $conn->prepare("SELECT 1 FROM users WHERE username=?");
        $chk->bind_param("s", $username);
        $chk->execute();
        if ($chk->get_result()->num_rows === 0) {
            return $username;
        }
        $username = $base . $i;
        $i++;
    }
}

/* ===============================
   FIND OR CREATE USER
================================ */
$stmt = $conn->prepare("
    SELECT id, username, role
    FROM users
    WHERE (oauth_provider='google' AND oauth_id=?)
       OR email=?
");
$stmt->bind_param("ss", $oauthId, $email);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows === 0) {

    // New Google user → create like normal register
    $username = generateUsername($email, $conn);
    $role = 'user';
    $plan = 'FREE';

    $ins = $conn->prepare("
        INSERT INTO users
        (username, email, role, plan, enabled, oauth_provider, oauth_id)
        VALUES (?, ?, ?, ?, 1, 'google', ?)
    ");
    $ins->bind_param("sssss", $username, $email, $role, $plan, $oauthId);
    $ins->execute();

    $uid = $conn->insert_id;

} else {
    // Existing user
    $row = $res->fetch_assoc();
    $uid      = (int)$row['id'];
    $username = $row['username'];
    $role     = $row['role'];
}

/* ===============================
   AUTO CREATE FREE LAB (60 MIN)
================================ */
$chk = $conn->prepare("
    SELECT id FROM lab_sessions WHERE user_id = ?
");
$chk->bind_param("i", $uid);
$chk->execute();
$chkRes = $chk->get_result();

if ($chkRes->num_rows === 0) {
    $exp = date("Y-m-d H:i:s", time() + 3600);

    $insLab = $conn->prepare("
        INSERT INTO lab_sessions
        (user_id, status, access_expiry)
        VALUES (?, 'REQUESTED', ?)
    ");
    $insLab->bind_param("is", $uid, $exp);
    $insLab->execute();
}

/* ===============================
   SET SESSION (SAME AS LOGIN)
================================ */
$_SESSION['user'] = $username;
$_SESSION['uid']  = $uid;
$_SESSION['role'] = $role;

/* ===============================
   REDIRECT
================================ */
header("Location: /terminal.php");
exit;

