<?php
// config/social_lib.php
require_once __DIR__ . "/db.php";
require_once __DIR__ . "/auth.php";

function social_find_or_create_user(PDO $pdo, string $email, string $fullname="Social User"): int {
  $email = trim(strtolower($email));
  $fullname = trim($fullname) ?: "Social User";
  $st = $pdo->prepare("SELECT id FROM users WHERE email=? LIMIT 1");
  $st->execute([$email]);
  $u = $st->fetch();
  if($u) return (int)$u["id"];

  // create random password (unused)
  $rand = bin2hex(random_bytes(8));
  $hash = password_hash($rand, PASSWORD_DEFAULT);
  $ins = $pdo->prepare("INSERT INTO users (fullname,email,password_hash,role,created_at) VALUES (?,?,?,?,NOW())");
  $ins->execute([$fullname, $email, $hash, "user"]);
  return (int)$pdo->lastInsertId();
}

function social_do_login(PDO $pdo, int $user_id){
  $_SESSION["user_id"] = $user_id;
}
