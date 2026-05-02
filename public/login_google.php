<?php
require_once __DIR__ . "/../config/google.php";
if (session_status() === PHP_SESSION_NONE) { session_start(); }

$state = bin2hex(random_bytes(16));
$_SESSION["google_oauth_state"] = $state;

$scope = urlencode("openid email profile");
$authUrl = "https://accounts.google.com/o/oauth2/v2/auth"
  . "?response_type=code"
  . "&client_id=" . urlencode(GOOGLE_CLIENT_ID)
  . "&redirect_uri=" . urlencode(GOOGLE_REDIRECT_URI)
  . "&scope=" . $scope
  . "&state=" . urlencode($state)
  . "&access_type=offline"
  . "&prompt=select_account";

header("Location: " . $authUrl);
exit;
