<?php
require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../config/auth.php";
require_login();

$stmt=$pdo->prepare("SELECT * FROM orders WHERE user_id=? ORDER BY created_at DESC");
$stmt->execute([(int)$_SESSION["user_id"]]);
$orders=$stmt->fetchAll();

$title="คำสั่งซื้อ | UsedBooks";
include __DIR__ . "/partials/header.php";
?>
<div class="container section">
  <div class="card">
    <div class="badge"><span class="dot ok"></span> คำสั่งซื้อของฉัน</div>
    <?php if(!$orders): ?>
      <p class="muted" style="margin-top:12px">ยังไม่มีคำสั่งซื้อ</p>
      <a class="btn" href="shop.php">ไปเลือกซื้อ</a>
    <?php else: ?>
      <table class="table" style="margin-top:12px">
        <thead><tr><th>#</th><th>วันที่</th><th>ยอดรวม</th><th>สถานะ</th><th>ดู</th></tr></thead>
        <tbody>
          <?php foreach($orders as $o): ?>
            <tr>
              <td>#<?php echo (int)$o["id"]; ?></td>
              <td><?php echo e(date("d/m/Y H:i", strtotime($o["created_at"]))); ?></td>
              <td>฿<?php echo number_format((float)$o["total"],0); ?></td>
              <td><?php echo e($o["status"]); ?></td>
              <td><a class="pill" href="order_detail.php?id=<?php echo (int)$o["id"]; ?>">รายละเอียด</a></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </div>
</div>
<?php include __DIR__ . "/partials/footer.php"; ?>
