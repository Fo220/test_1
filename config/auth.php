<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }

function e($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

function current_user_id(): int {
  return isset($_SESSION["user_id"]) ? (int)$_SESSION["user_id"] : 0;
}
function is_logged_in(): bool { return current_user_id() > 0; }
function current_role(): string { return (string)($_SESSION["role"] ?? ""); }
function is_admin(): bool { return current_role() === "admin"; }

/* ===== Remember Me (30 days) =====
   Cookie format: selector:validator
*/
function remember_me_login(PDO $pdo): void {
  if(is_logged_in()) return;
  if(empty($_COOKIE["remember"])) return;

  $raw = $_COOKIE["remember"];
  if(strpos($raw, ":") === false) return;
  [$selector, $validator] = explode(":", $raw, 2);
  if(!$selector || !$validator) return;

  $stmt = $pdo->prepare("SELECT * FROM auth_tokens WHERE selector=? LIMIT 1");
  $stmt->execute([$selector]);
  $row = $stmt->fetch(PDO::FETCH_ASSOC);
  if(!$row) return;

  if(strtotime($row["expires_at"]) < time()){
    $pdo->prepare("DELETE FROM auth_tokens WHERE selector=?")->execute([$selector]);
    setcookie("remember","", time()-3600, "/");
    return;
  }

  $calc = hash("sha256", $validator);
  if(!hash_equals($row["validator_hash"], $calc)){
    // token mismatch -> revoke all tokens for safety
    $pdo->prepare("DELETE FROM auth_tokens WHERE user_id=?")->execute([(int)$row["user_id"]]);
    setcookie("remember","", time()-3600, "/");
    return;
  }

  // load user
  $u = $pdo->prepare("SELECT id, role, status FROM users WHERE id=? LIMIT 1");
  $u->execute([(int)$row["user_id"]]);
  $user = $u->fetch(PDO::FETCH_ASSOC);
  if(!$user || ($user["status"] ?? "") !== "active"){
    $pdo->prepare("DELETE FROM auth_tokens WHERE selector=?")->execute([$selector]);
    setcookie("remember","", time()-3600, "/");
    return;
  }

  $_SESSION["user_id"] = (int)$user["id"];
  $_SESSION["role"] = $user["role"];

  // rotate token
  $newSelector = bin2hex(random_bytes(12));
  $newValidator = bin2hex(random_bytes(20));
  $newHash = hash("sha256", $newValidator);
  $expires = date("Y-m-d H:i:s", time()+60*60*24*30);

  $pdo->prepare("UPDATE auth_tokens SET selector=?, validator_hash=?, expires_at=? WHERE id=?")
      ->execute([$newSelector, $newHash, $expires, (int)$row["id"]]);

  setcookie("remember", $newSelector.":".$newValidator, time()+60*60*24*30, "/");
}

function issue_remember_token(PDO $pdo, int $user_id): void {
  $selector = bin2hex(random_bytes(12));
  $validator = bin2hex(random_bytes(20));
  $hash = hash("sha256", $validator);
  $expires = date("Y-m-d H:i:s", time()+60*60*24*30);

  $pdo->prepare("INSERT INTO auth_tokens(user_id, selector, validator_hash, expires_at) VALUES (?,?,?,?)")
      ->execute([$user_id, $selector, $hash, $expires]);

  setcookie("remember", $selector.":".$validator, time()+60*60*24*30, "/");
}

function clear_remember_token(PDO $pdo): void {
  if(!empty($_COOKIE["remember"]) && strpos($_COOKIE["remember"], ":") !== false){
    [$selector,] = explode(":", $_COOKIE["remember"], 2);
    $pdo->prepare("DELETE FROM auth_tokens WHERE selector=?")->execute([$selector]);
  }
  setcookie("remember","", time()-3600, "/");
}

function require_login(): void {
  global $pdo;
  if(isset($pdo)) remember_me_login($pdo);
  if(!is_logged_in()){
    header("Location: auth.php?mode=login");
    exit;
  }
}

function require_admin(): void {
  global $pdo;
  if(isset($pdo)) remember_me_login($pdo);
  if(!is_logged_in() || !is_admin()){
    header("Location: auth.php?mode=login");
    exit;
  }
}
