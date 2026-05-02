<?php
// public/social_login.php
session_start();
require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../config/social_lib.php";

$cfg = require __DIR__ . "/../config/social.php";
$provider = $_GET["provider"] ?? "";
$provider = preg_replace("/[^a-z]/","",$provider);

$allowed = ["google","facebook","line","apple"];
if(!in_array($provider, $allowed)){ header("Location: auth.php"); exit; }

$provCfg = $cfg["providers"][$provider] ?? ["enabled"=>false];
if(empty($provCfg["enabled"])) { header("Location: auth.php"); exit; }

// If OAuth real configured, you can implement real flow later.
// For now: MOCK LOGIN (dev) -> เข้าได้จริงทันที
if(!empty($cfg["mock_enabled"]) || empty($provCfg["client_id"]) || empty($provCfg["client_secret"])) {
  $email = $provider . "_demo@" . $_SERVER["HTTP_HOST"];
  $name = strtoupper($provider) . " Demo";
  $uid = social_find_or_create_user($pdo, $email, $name);
  social_do_login($pdo, $uid);
  $_SESSION["flash_success"] = "เข้าสู่ระบบด้วย ".strtoupper($provider)." สำเร็จ";
  header("Location: index.php");
  exit;
}

$_SESSION["flash_error"] = "ยังไม่ได้ตั้งค่า OAuth จริงของ {$provider} (เปิด mock_enabled ได้ใน config/social.php)";
header("Location: auth.php");
