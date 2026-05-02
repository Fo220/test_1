require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../config/auth.php";
if(isset($pdo)) clear_remember_token($pdo);
<?php
session_start(); session_unset(); session_destroy();
header("Location: index.php"); exit;
