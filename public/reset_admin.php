<?php
require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../config/app.php";

$key = $_GET["key"] ?? "";
if(!$key || !defined("APP_SETUP_KEY") || $key !== APP_SETUP_KEY){
  http_response_code(403);
  die("Forbidden: invalid key");
}

$email = DEFAULT_ADMIN_EMAIL;
$newPass = DEFAULT_ADMIN_PASSWORD;

$hash = password_hash($newPass, PASSWORD_DEFAULT);
$pdo->prepare("UPDATE users SET password_hash=?, role='admin', status='active' WHERE email=?")
    ->execute([$hash, $email]);

echo "OK: reset admin => {$email} / {$newPass}";
