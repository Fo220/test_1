<?php
// public/add_to_cart.php
require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/partials/cart_lib.php";

$id = (int)($_GET["id"] ?? 0);
$qty = max(1, (int)($_GET["qty"] ?? 1));
$back = $_SERVER["HTTP_REFERER"] ?? "shop.php";

if($id <= 0){
  header("Location: shop.php"); exit;
}

// Validate book exists & published
$st = $pdo->prepare("SELECT id, stock, is_published FROM books WHERE id=? LIMIT 1");
$st->execute([$id]);
$b = $st->fetch();

if(!$b || (int)($b["is_published"] ?? 0) !== 1){
  header("Location: shop.php"); exit;
}

// If stock is tracked, clamp qty
$stock = (int)($b["stock"] ?? 0);
if($stock > 0){
  $qty = min($qty, $stock);
}

cart_add($id, $qty);

// Redirect to cart by default (or back if ?back=1)
if(isset($_GET["back"]) && $_GET["back"]=="1"){
  header("Location: " . $back); exit;
}
header("Location: cart.php");
exit;
