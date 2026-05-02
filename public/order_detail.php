<?php
require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../config/auth.php";
require_login();

$id=(int)($_GET["id"] ?? 0);
$stmt=$pdo->prepare("SELECT * FROM orders WHERE id=? AND user_id=?");
$stmt->execute([$id,(int)$_SESSION["user_id"]]);
$o=$stmt->fetch();
if(!$o){ http_response_code(404); die("Order not found"); }

$it=$pdo->prepare("SELECT * FROM order_items WHERE order_id=?");
$it->execute([$id]);
$items=$it->fetchAll();

// latest payment (if any)
$payStmt = $pdo->prepare("SELECT * FROM payments WHERE order_id=? AND user_id=? ORDER BY id DESC LIMIT 1");
$payStmt->execute([$id,(int)$_SESSION["user_id"]]);
$lastPay = $payStmt->fetch();


$title="ออเดอร์ #$id | UsedBooks";
include __DIR__ . "/partials/header.php";
?>
<div class="container section">
  <div class="card">
    <div class="badge"><span class="dot ok"></span> ออเดอร์ #<?php echo (int)$o["id"]; ?></div>
    <p class="muted" style="margin:10px 0 0">สถานะ: <strong><?php echo e($o["status"]); ?></strong></p>
    <?php if($lastPay): ?>
      <p class="muted" style="margin:8px 0 0">สถานะสลิป: <strong><?php echo e($lastPay["status"]); ?></strong></p>
    <?php endif; ?>

    <div class="split" style="margin-top:12px">
      <div class="card" style="padding:14px">
        <div class="small">ผู้รับ</div>
        <div style="font-weight:900"><?php echo e($o["shipping_name"]); ?></div>
        <div class="small"><?php echo e($o["shipping_phone"]); ?></div>
      </div>
      <div class="card" style="padding:14px">
        <div class="small">ที่อยู่</div>
        <div class="muted"><?php echo nl2br(e($o["shipping_address"])); ?></div>
      </div>
    </div>

    <table class="table" style="margin-top:12px">
      <thead><tr><th>หนังสือ</th><th>ราคา</th><th>จำนวน</th><th>รวม</th></tr></thead>
      <tbody>
        <?php foreach($items as $x): ?>
          <tr>
            <td><?php echo e($x["title_snapshot"]); ?></td>
            <td>฿<?php echo number_format((float)$x["price_snapshot"],0); ?></td>
            <td><?php echo (int)$x["qty"]; ?></td>
            <td>฿<?php echo number_format((float)$x["price_snapshot"]*(int)$x["qty"],0); ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>

    <div class="card" style="padding:14px;margin-top:12px">
      <div class="small">ยอดรวม</div>
      <div style="font-size:26px;font-weight:900">฿<?php echo number_format((float)$o["total"],0); ?></div>
    </div>

    <div style="display:flex;gap:10px;flex-wrap:wrap;margin-top:12px">
      <?php if($o["status"]==="pending" || $o["status"]==="cancelled"): ?><?php endif; ?>
      <a class="btn ghost" href="orders.php">กลับไปคำสั่งซื้อ</a>
      <?php if($o["status"]==="pending"): ?>
        <a class="btn" href="pay.php?order_id=<?php echo (int)$o["id"]; ?>">ชำระเงิน / แจ้งสลิป</a>
      <?php endif; ?>
      <a class="btn" href="shop.php">ไปซื้อเพิ่ม</a>
    </div>
  </div>
</div>
<?php include __DIR__ . "/partials/footer.php"; ?>
