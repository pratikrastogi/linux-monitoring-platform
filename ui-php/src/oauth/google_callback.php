<?php
session_start();

if (!isset($_GET['state']) || $_GET['state'] !== $_SESSION['oauth_state']) {
    die("Invalid OAuth state");
}

$config = require __DIR__ . '/../config/oauth.php';
$g = $config['google'];

$code = $_GET['code'] ?? null;
if (!$code) die("Missing auth code");

/* ---- Exchange code for token ---- */
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

/* ---- Get user profile ---- */
$profile = json_decode(
  file_get_contents(
    "https://www.googleapis.com/oauth2/v2/userinfo?access_token=".$token['access_token']
  ),
  true
);

if (!$profile['verified_email']) die("Email not verified");

$email = $profile['email'];
$oauthId = $profile['id'];
$username = explode('@', $email)[0];

$conn = new mysqli("mysql","monitor","monitor123","monitoring");
if ($conn->connect_error) die("DB Error");

/* ---- Find or create user ---- */
$stmt = $conn->prepare("
  SELECT id, username, role FROM users
  WHERE oauth_provider='google' AND oauth_id=?
");
$stmt->bind_param("s", $oauthId);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows === 0) {
    $role = 'user';
    $plan = 'FREE';

    $ins = $conn->prepare("
      INSERT INTO users
      (username,email,role,plan,enabled,oauth_provider,oauth_id)
      VALUES (?,?,?,?,1,'google',?)
    ");
    $ins->bind_param("sssss",$username,$email,$role,$plan,$oauthId);
    $ins->execute();
}

$_SESSION['user'] = $username;
$_SESSION['role'] = 'user';

header("Location: /index.php");
exit;

