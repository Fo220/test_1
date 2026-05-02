<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
function cart_get(){ return $_SESSION["cart"] ?? []; }
function cart_add($bookId, $qty=1){
  $cart = cart_get(); $bookId=(int)$bookId; $qty=max(1,(int)$qty);
  $cart[$bookId]=($cart[$bookId] ?? 0)+$qty; $_SESSION["cart"]=$cart;
}
function cart_set($bookId, $qty){
  $cart = cart_get(); $bookId=(int)$bookId; $qty=(int)$qty;
  if($qty<=0) unset($cart[$bookId]); else $cart[$bookId]=$qty;
  $_SESSION["cart"]=$cart;
}
function cart_clear(){ unset($_SESSION["cart"]); }
