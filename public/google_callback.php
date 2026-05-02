<?php
require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../config/auth.php";
require_once __DIR__ . "/../config/google.php";

if (session_status() === PHP_SESSION_NONE) { session_start(); }

$code = $_GET["code"] ?? "";
$state = $_GET["state"] ?? "";
if(!$code || !$state || empty($_SESSION["google_oauth_state"]) || !hash_equals($_SESSION["google_oauth_state"], $state)){
  http_response_code(400);
  die("Invalid OAuth state");
}
unset($_SESSION["google_oauth_state"]);

// Exchange code -> token
$tokenUrl = "https://oauth2.googleapis.com/token";
$post = http_build_query([
  "code" => $code,
  "client_id" => GOOGLE_CLIENT_ID,
  "client_secret" => GOOGLE_CLIENT_SECRET,
  "redirect_uri" => GOOGLE_REDIRECT_URI,
  "grant_type" => "authorization_code",
]);

$ch = curl_init($tokenUrl);
curl_setopt_array($ch, [
  CURLOPT_POST => true,
  CURLOPT_POSTFIELDS => $post,
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_HTTPHEADER => ["Content-Type: application/x-www-form-urlencoded"],
]);
$res = curl_exec($ch);
$err = curl_error($ch);
curl_close($ch);

if(!$res){
  http_response_code(500);
  die("Token request failed: " . $err);
}
$tok = json_decode($res, true);
$access = $tok["access_token"] ?? "";
if(!$access){
  http_response_code(500);
  die("Token missing. Response: " . e($res));
}

// Userinfo
$infoUrl = "https://openidconnect.googleapis.com/v1/userinfo";
$ch = curl_init($infoUrl);
curl_setopt_array($ch, [
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_HTTPHEADER => ["Authorization: Bearer ".$access],
]);
$uRes = curl_exec($ch);
$uErr = curl_error($ch);
curl_close($ch);

if(!$uRes){
  http_response_code(500);
  die("Userinfo request failed: " . $uErr);
}
$info = json_decode($uRes, true);
$email = $info["email"] ?? "";
$sub = $info["sub"] ?? "";
$name = $info["name"] ?? "Google User";
$avatar = $info["picture"] ?? null;

if(!$email || !$sub){
  http_response_code(500);
  die("Userinfo incomplete. Response: " . e($uRes));
}

// Find user by google_id or email
$stmt = $pdo->prepare("SELECT * FROM users WHERE google_id=? OR email=? LIMIT 1");
$stmt->execute([$sub, $email]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$user){
  // Create new user (password_hash random, user role)
  $randomPass = bin2hex(random_bytes(16));
  $hash = password_hash($randomPass, PASSWORD_DEFAULT);
  $ins = $pdo->prepare("INSERT INTO users(fullname,email,password_hash,role,status,avatar_url,google_id) VALUES (?,?,?,?,?,?,?)");
  $ins->execute([$name, $email, $hash, "user", "active", $avatar, $sub]);

  $id = (int)$pdo->lastInsertId();
  $user = ["id"=>$id, "role"=>"user", "status"=>"active"];
} else {
  // Link google_id if missing
  if(empty($user["google_id"])){
    $pdo->prepare("UPDATE users SET google_id=?, avatar_url=? WHERE id=?")->execute([$sub, $avatar, (int)$user["id"]]);
  }
}

if(($user["status"] ?? "") !== "active"){
  die("บัญชีถูกระงับการใช้งาน");
}

$_SESSION["user_id"] = (int)$user["id"];
$_SESSION["role"] = $user["role"] ?? "user";

// Optional remember me (if you append ?remember=1 to login_google.php)
if(isset($_GET["remember"]) || !empty($_SESSION["remember_after_google"])){
  issue_remember_token($pdo, (int)$user["id"]);
  unset($_SESSION["remember_after_google"]);
}

header("Location: index.php");
exit;
