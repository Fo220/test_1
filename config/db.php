<?php
require_once __DIR__ . "/app.php";
// config/db.php (v10) AUTO: create DB + fix schema + import schema_full.sql if needed

$host = "localhost";
$db   = "used_books_db";
$user = "root";
$pass = "";
$port = "3306";
$charset = "utf8mb4";

$options = [
  PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
  PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
  PDO::ATTR_EMULATE_PREPARES => false,
];

function pdo_no_db($host,$port,$charset,$user,$pass,$options){
  $dsn = "mysql:host=$host;port=$port;charset=$charset";
  return new PDO($dsn, $user, $pass, $options);
}
function pdo_db($host,$port,$db,$charset,$user,$pass,$options){
  $dsn = "mysql:host=$host;port=$port;dbname=$db;charset=$charset";
  return new PDO($dsn, $user, $pass, $options);
}
function has_table(PDO $pdo, string $table): bool {
  $stmt = $pdo->prepare("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = ?");
  $stmt->execute([$table]);
  return (int)$stmt->fetchColumn() > 0;
}
function has_column(PDO $pdo, string $table, string $col): bool {
  $stmt = $pdo->prepare("SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = ? AND column_name = ?");
  $stmt->execute([$table,$col]);
  return (int)$stmt->fetchColumn() > 0;
}
function exec_sql_file(PDO $pdo, string $path): void {
  if(!file_exists($path)) throw new Exception("SQL file not found: ".$path);
  $sql = file_get_contents($path);
  // remove line comments
  $sql = preg_replace('/^\s*--.*$/m','',$sql);
  $parts = array_filter(array_map('trim', explode(";", $sql)));
  foreach($parts as $s){
    if($s !== "") $pdo->exec($s);
  }
}

try{
  $pdo = pdo_db($host,$port,$db,$charset,$user,$pass,$options);
}catch(PDOException $e){
  if(strpos($e->getMessage(),"Unknown database") !== false){
    $tmp = pdo_no_db($host,$port,$charset,$user,$pass,$options);
    $tmp->exec("CREATE DATABASE IF NOT EXISTS `$db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo = pdo_db($host,$port,$db,$charset,$user,$pass,$options);
  }else{
    die("DB connection failed: ".$e->getMessage());
  }
}

// Fresh install: import schema_full.sql if no users table
try{
  $schemaFull = __DIR__ . "/../sql/schema_full.sql";
  if(!has_table($pdo,"users")){
    exec_sql_file($pdo, $schemaFull);
  }
}catch(Exception $e){
  die("DB schema import failed: ".$e->getMessage());
}

// Fix common mismatch (old schema without is_published/categories)
try{
  if((has_table($pdo,"books") && !has_column($pdo,"books","is_published")) || !has_table($pdo,"user_addresses")){
    $fix = __DIR__ . "/../sql/fix_v10.sql";
    exec_sql_file($pdo, $fix);
  }
}catch(Exception $e){
  // don't kill site for minor fix errors; but show for debug
  die("DB auto-fix failed: ".$e->getMessage());
}


// Seed default admin (guarantee login works)
function seed_default_admin(PDO $pdo): void {
  $email = defined("DEFAULT_ADMIN_EMAIL") ? DEFAULT_ADMIN_EMAIL : "admin@demo.com";
  $pass  = defined("DEFAULT_ADMIN_PASSWORD") ? DEFAULT_ADMIN_PASSWORD : "admin1234";
  $hash  = password_hash($pass, PASSWORD_DEFAULT);

  $stmt = $pdo->prepare("SELECT id FROM users WHERE email=? LIMIT 1");
  $stmt->execute([$email]);
  $row = $stmt->fetch(PDO::FETCH_ASSOC);

  if(!$row){
    $ins = $pdo->prepare("INSERT INTO users(fullname,email,password_hash,role,status) VALUES (?,?,?,?,?)");
    $ins->execute(["Admin",$email,$hash,"admin","active"]);
  } else {
    // force to admin + active + reset password to default (dev-friendly)
    $up = $pdo->prepare("UPDATE users SET password_hash=?, role='admin', status='active' WHERE email=?");
    $up->execute([$hash,$email]);
  }
}

try{
  if(has_table($pdo,"users")){
    seed_default_admin($pdo);
  }
}catch(Exception $e){
  // ignore seeding failure
}

// v17: orders shipping columns / enum fix
if(has_table($pdo,"orders")){
  $needShip = !has_column($pdo,"orders","shipping_name") || !has_column($pdo,"orders","shipping_phone") || !has_column($pdo,"orders","shipping_address");
  if($needShip){
    try{
      $pdo->exec("ALTER TABLE orders ADD COLUMN shipping_name VARCHAR(120) NOT NULL DEFAULT '' AFTER status");
    }catch(Exception $e){}
    try{
      $pdo->exec("ALTER TABLE orders ADD COLUMN shipping_phone VARCHAR(30) NOT NULL DEFAULT '' AFTER shipping_name");
    }catch(Exception $e){}
    try{
      $pdo->exec("ALTER TABLE orders ADD COLUMN shipping_address TEXT NOT NULL AFTER shipping_phone");
    }catch(Exception $e){}
  }
  // status enum widen
  try{
    $pdo->exec("ALTER TABLE orders MODIFY COLUMN status ENUM('pending','paid','shipped','cancelled') NOT NULL DEFAULT 'pending'");
  }catch(Exception $e){}
}


// v18: optional book detail fields (safe add)
if(has_table($pdo,"books")){
  $cols = [
    "author" => "VARCHAR(160) NULL",
    "publisher" => "VARCHAR(160) NULL",
    "series" => "VARCHAR(200) NULL",
    "file_type" => "VARCHAR(20) NULL",
    "list_price" => "DECIMAL(10,2) NULL",
    "tags" => "VARCHAR(255) NULL",
    "description" => "TEXT NULL",
    "category_id" => "INT NULL"
  ];
  foreach($cols as $c=>$def){
    if(!has_column($pdo,"books",$c)){
      try{ $pdo->exec("ALTER TABLE books ADD COLUMN `$c` $def"); }catch(Exception $e){}
    }
  }
  // ensure FK for category if possible (ignore errors)
  try{
    $pdo->exec("ALTER TABLE books ADD INDEX idx_books_category_id (category_id)");
  }catch(Exception $e){}
}


// v20: soft delete for books (avoid FK delete errors)
if(has_table($pdo,"books") && !has_column($pdo,"books","is_deleted")){
  try{ $pdo->exec("ALTER TABLE books ADD COLUMN is_deleted TINYINT(1) NOT NULL DEFAULT 0"); }catch(Exception $e){}
}

// v21: order_items snapshot columns (title_snapshot, price_snapshot)
if(has_table($pdo,"order_items")){
  if(!has_column($pdo,"order_items","title_snapshot")){
    try{ $pdo->exec("ALTER TABLE order_items ADD COLUMN title_snapshot VARCHAR(255) NOT NULL DEFAULT '' AFTER book_id"); }catch(Exception $e){}
  }
  if(!has_column($pdo,"order_items","price_snapshot")){
    try{ $pdo->exec("ALTER TABLE order_items ADD COLUMN price_snapshot DECIMAL(10,2) NOT NULL DEFAULT 0 AFTER title_snapshot"); }catch(Exception $e){}
  }
  // If legacy column 'price' exists but price_snapshot empty, you can manually backfill if needed.
}

