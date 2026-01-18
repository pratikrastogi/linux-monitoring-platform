<?php
session_start();

$config = require __DIR__ . '/../config/oauth.php';
$g = $config['google'];

$state = bin2hex(random_bytes(16));
$_SESSION['oauth_state'] = $state;

$params = [
  'client_id'     => $g['client_id'],
  'redirect_uri'  => $g['redirect_uri'],
  'response_type' => 'code',
  'scope'         => 'openid email profile',
  'state'         => $state,
  'access_type'   => 'online',
  'prompt'        => 'select_account'
];

header("Location: https://accounts.google.com/o/oauth2/v2/auth?" . http_build_query($params));
exit;

