<?php
require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../config/auth.php";
require_login();

$order_id = (int)($_GET["order_id"] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM orders WHERE id=? AND user_id=? LIMIT 1");
$stmt->execute([$order_id, (int)$_SESSION["user_id"]]);
$order = $stmt->fetch();
if(!$order){ http_response_code(404); die("Order not found"); }

// latest payment (if any)
$payStmt = $pdo->prepare("SELECT * FROM payments WHERE order_id=? AND user_id=? ORDER BY id DESC LIMIT 1");
$payStmt->execute([$order_id, (int)$_SESSION["user_id"]]);
$lastPay = $payStmt->fetch();

$error=""; $ok="";

$uploadDir = __DIR__ . "/../uploads/slips";
if(!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

if($_SERVER["REQUEST_METHOD"]==="POST"){
  $amount = (float)($_POST["amount"] ?? 0);
  $bank_name = trim($_POST["bank_name"] ?? "");
  $dt = trim($_POST["transfer_datetime"] ?? "");

  if($amount <= 0) $error="กรุณาใส่ยอดโอน";
  elseif(!$dt) $error="กรุณาเลือกวันเวลาโอน";
  elseif(!isset($_FILES["slip"]) || $_FILES["slip"]["error"]!==UPLOAD_ERR_OK) $error="กรุณาแนบสลิป";
  else{
    $ext = strtolower(pathinfo($_FILES["slip"]["name"], PATHINFO_EXTENSION));
    $allow = ["jpg","jpeg","png","webp","pdf"];
    if(!in_array($ext,$allow,true)) $error="รองรับไฟล์ jpg/png/webp/pdf เท่านั้น";
    else{
      $name = "slip_".$order_id."_".time().".".$ext;
      $dest = $uploadDir."/".$name;
      if(move_uploaded_file($_FILES["slip"]["tmp_name"], $dest)){
        $slip_path = "../uploads/slips/".$name;

        $pdo->prepare("INSERT INTO payments(order_id,user_id,amount,bank_name,transfer_datetime,slip_path) VALUES (?,?,?,?,?,?)")
            ->execute([$order_id, (int)$_SESSION["user_id"], $amount, $bank_name, $dt, $slip_path]);

        $ok="ส่งสลิปเรียบร้อย รอแอดมินตรวจสอบ";
        // refresh latest payment
        $payStmt->execute([$order_id, (int)$_SESSION["user_id"]]);
        $lastPay = $payStmt->fetch();
      } else $error="อัปโหลดไฟล์ไม่สำเร็จ";
    }
  }
}

$title="ชำระเงิน | UsedBooks";
include __DIR__ . "/partials/header.php";
?>

<div class="container section">
  <div class="card">
    <div class="badge"><span class="dot ok"></span> ชำระเงิน Order #<?php echo (int)$order["id"]; ?></div>
    <p class="muted" style="margin:10px 0 0">สถานะออเดอร์: <strong><?php echo e($order["status"]); ?></strong></p>

    <?php if($lastPay): ?>
      <div class="card" style="margin-top:12px">
        <div class="badge"><span class="dot ok"></span> การแจ้งชำระเงินล่าสุด</div>
        <div class="small" style="margin-top:10px;line-height:1.7">
          สถานะสลิป: <b><?php echo e($lastPay["status"]); ?></b><br>
          ยอดโอน: <b>฿<?php echo number_format((float)$lastPay["amount"],2); ?></b><br>
          วันเวลาโอน: <b><?php echo e($lastPay["transfer_datetime"]); ?></b><br>
          <a class="pill" target="_blank" rel="noopener" href="<?php echo e($lastPay["slip_path"]); ?>">เปิดสลิป</a>
        </div>
      </div>
    <?php endif; ?>

    <?php if($order["status"] === "paid"): ?>
      <div class="success" style="margin-top:12px">ออเดอร์นี้ชำระเงินแล้ว ✅</div>
      <div style="margin-top:12px">
        <a class="btn ghost" href="order_detail.php?id=<?php echo (int)$order["id"]; ?>">กลับไปดูออเดอร์</a>
      </div>
    <?php else: ?>

      <?php if($error): ?><div class="alert"><?php echo e($error); ?></div><?php endif; ?>
      <?php if($ok): ?><div class="success"><?php echo e($ok); ?></div><?php endif; ?>

      <div class="card" style="margin-top:12px">
        <div class="badge"><span class="dot ok"></span> ข้อมูลบัญชีรับโอน</div>
        <div class="small" style="margin-top:10px;line-height:1.7">
          ธนาคาร: <b>กสิกรไทย</b><br>
          เลขบัญชี: <b>xxx-x-xxxxx-x</b><br>
          ชื่อบัญชี: <b>UsedBooks Shop</b>
        </div>
        <div class="small" style="margin-top:10px">*แก้ข้อมูลนี้ได้ในไฟล์ public/pay.php</div>
      </div>

      <form class="form" method="post" enctype="multipart/form-data" style="margin-top:12px;max-width:520px">
        <label>ยอดโอน</label>
        <input name="amount" type="number" step="0.01" required>

        <label>ธนาคารที่โอน (ไม่บังคับ)</label>
        <input name="bank_name" placeholder="เช่น SCB / KBank / KTB">

        <label>วันเวลาโอน</label>
        <input name="transfer_datetime" type="datetime-local" required>

        <label>แนบสลิป (jpg/png/webp/pdf)</label>
        <input name="slip" type="file" accept=".jpg,.jpeg,.png,.webp,.pdf" required>

        <button class="btn" type="submit">ส่งสลิป</button>
        <a class="btn ghost" href="order_detail.php?id=<?php echo (int)$order["id"]; ?>">กลับไปดูออเดอร์</a>
      </form>
    <?php endif; ?>
  </div>
</div>

<?php include __DIR__ . "/partials/footer.php"; ?>
