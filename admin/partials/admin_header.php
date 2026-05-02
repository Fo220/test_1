<?php require_once __DIR__ . "/../../config/auth.php"; require_admin(); ?>
<!doctype html>
<html lang="th"><head>
<meta charset="utf-8"/><meta name="viewport" content="width=device-width, initial-scale=1"/>
<title><?php echo e($title ?? "Admin"); ?></title>
<link rel="stylesheet" href="../public/assets/css/style.css"/>
</head><body class="bg">
<div class="nav"><div class="container nav-inner">
  <a class="brand" href="index.php"><span class="logo"></span><strong>Admin • UsedBooks</strong></a>
  <div class="nav-links">
    <a class="pill" href="books.php">หนังสือ</a>
    <a class="pill" href="categories.php">หมวดหมู่</a>
    <a class="pill" href="orders.php">ออเดอร์</a>
    <a class="pill" href="users.php">สมาชิก</a>
    <a class="pill" href="../public/index.php">หน้าเว็บ</a>
    <a class="pill" href="../public/logout.php">ออกจากระบบ</a>
  </div>
</div></div>
