<?php
require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../config/auth.php";
require_admin();

$books=(int)$pdo->query("SELECT COUNT(*) c FROM books")->fetch()["c"];
$users=(int)$pdo->query("SELECT COUNT(*) c FROM users")->fetch()["c"];
$orders=(int)$pdo->query("SELECT COUNT(*) c FROM orders")->fetch()["c"];
$pending=(int)$pdo->query("SELECT COUNT(*) c FROM orders WHERE status='pending'")->fetch()["c"];

$title="Dashboard | Admin";
include __DIR__ . "/partials/admin_header.php";
?>
<div class="container section">
  <div class="card">
    <div class="badge"><span class="dot ok"></span> Dashboard</div>
    <div class="split" style="margin-top:12px">
      <div class="card" style="padding:14px"><div class="small">หนังสือ</div><div style="font-size:26px;font-weight:900"><?php echo $books; ?></div></div>
      <div class="card" style="padding:14px"><div class="small">สมาชิก</div><div style="font-size:26px;font-weight:900"><?php echo $users; ?></div></div>
    </div>
    <div class="split" style="margin-top:12px">
      <div class="card" style="padding:14px"><div class="small">ออเดอร์</div><div style="font-size:26px;font-weight:900"><?php echo $orders; ?></div></div>
      <div class="card" style="padding:14px"><div class="small">Pending</div><div style="font-size:26px;font-weight:900"><?php echo $pending; ?></div></div>
    </div>
    <div style="display:flex;gap:10px;flex-wrap:wrap;margin-top:12px">
      <a class="btn" href="books.php">จัดการหนังสือ</a>
      <a class="btn ghost" href="orders.php">จัดการออเดอร์</a>
    </div>
  </div>
</div>
<?php include __DIR__ . "/partials/admin_footer.php"; ?>
