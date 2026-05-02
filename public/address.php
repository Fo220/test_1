<?php
$title="ที่อยู่ของฉัน";
require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../config/auth.php";
require_login();

$uid = (int)$_SESSION["user_id"];
$errors=[]; $success="";

$st = $pdo->prepare("SELECT * FROM user_addresses WHERE user_id=?");
$st->execute([$uid]);
$addr = $st->fetch() ?: ["line1"=>"","line2"=>"","province"=>"","zipcode"=>"","phone"=>""];

if($_SERVER["REQUEST_METHOD"]==="POST"){
  $line1 = trim($_POST["line1"] ?? "");
  $line2 = trim($_POST["line2"] ?? "");
  $province = trim($_POST["province"] ?? "");
  $zipcode = trim($_POST["zipcode"] ?? "");
  $phone = trim($_POST["phone"] ?? "");

  if(!$line1) $errors[]="กรุณากรอกที่อยู่บรรทัดที่ 1";
  if(!$province) $errors[]="กรุณากรอกจังหวัด";
  if(!$zipcode) $errors[]="กรุณากรอกรหัสไปรษณีย์";
  if(!$errors){
    $pdo->prepare("INSERT INTO user_addresses(user_id,line1,line2,province,zipcode,phone)
      VALUES(?,?,?,?,?,?)
      ON DUPLICATE KEY UPDATE line1=VALUES(line1), line2=VALUES(line2), province=VALUES(province),
      zipcode=VALUES(zipcode), phone=VALUES(phone)")
      ->execute([$uid,$line1,$line2,$province,$zipcode,$phone]);
    $success="บันทึกที่อยู่เรียบร้อย";
    $st->execute([$uid]);
    $addr=$st->fetch();
  }
}
require_once __DIR__ . "/partials/header.php";
?>
<div class="container section">
  <div class="card">
    <h2 style="margin-top:0">ที่อยู่ของฉัน</h2>
    <p class="muted">ใช้สำหรับจัดส่งสินค้า (คุณแก้ไขได้ตลอดเวลา)</p>

    <?php if($errors): ?><div class="alert"><?php echo e(implode("\n",$errors)); ?></div><?php endif; ?>
    <?php if($success): ?><div class="success"><?php echo e($success); ?></div><?php endif; ?>

    <form class="form" method="post">
      <label>ที่อยู่บรรทัดที่ 1</label>
      <input name="line1" value="<?php echo e($addr["line1"] ?? ""); ?>" required>

      <label>ที่อยู่บรรทัดที่ 2 (ไม่บังคับ)</label>
      <input name="line2" value="<?php echo e($addr["line2"] ?? ""); ?>">

      <div class="split">
        <div>
          <label>จังหวัด</label>
          <input name="province" value="<?php echo e($addr["province"] ?? ""); ?>" required>
        </div>
        <div>
          <label>รหัสไปรษณีย์</label>
          <input name="zipcode" value="<?php echo e($addr["zipcode"] ?? ""); ?>" required>
        </div>
      </div>

      <label>เบอร์โทร</label>
      <input name="phone" value="<?php echo e($addr["phone"] ?? ""); ?>" placeholder="0xx-xxx-xxxx">

      <button class="btn blue" type="submit">บันทึก</button>
    </form>
  </div>
</div>
<?php require_once __DIR__ . "/partials/footer.php"; ?>
</body></html>
