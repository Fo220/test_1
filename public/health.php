<?php
require_once __DIR__ . "/../config/db.php";
header("Content-Type: text/plain; charset=utf-8");

function count_table(PDO $pdo, string $t): int {
  try { return (int)$pdo->query("SELECT COUNT(*) FROM `$t`")->fetchColumn(); }
  catch(Exception $e){ return -1; }
}

$tables = ["users","books","orders","order_items","payments","auth_tokens","user_addresses","categories"];
echo "OK: DB connected\n";
foreach($tables as $t){
  $c = count_table($pdo, $t);
  echo str_pad($t, 15) . ": " . $c . "\n";
}
echo "\nDefault admin: " . (defined("DEFAULT_ADMIN_EMAIL")?DEFAULT_ADMIN_EMAIL:"admin@demo.com") . "\n";
