<?php
$title="ข้อมูลส่วนตัว";
require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../config/auth.php";
require_login();

$uid = (int)$_SESSION["user_id"];
$errors=[]; $success="";

$st = $pdo->prepare("SELECT fullname,email,avatar_url FROM users WHERE id=?");
$st->execute([$uid]);
$user = $st->fetch();

if($_SERVER["REQUEST_METHOD"]==="POST"){
  $fullname = trim($_POST["fullname"] ?? "");
  $avatar = trim($_POST["avatar_url"] ?? "");
  if(!$fullname){ $errors[]="กรุณากรอกชื่อ"; }
  if(!$errors){
    $up=$pdo->prepare("UPDATE users SET fullname=?, avatar_url=? WHERE id=?");
    $up->execute([$fullname, ($avatar?:null), $uid]);
    $success="บันทึกข้อมูลเรียบร้อย";
    $st->execute([$uid]);
    $user = $st->fetch();
  }
}
require_once __DIR__ . "/partials/header.php";
?>
<div class="container section">
  <div class="card">
    <h2 style="margin-top:0">ข้อมูลส่วนตัว</h2>
    <p class="muted">แก้ไขชื่อแสดงและรูปโปรไฟล์ (ใส่ลิงก์รูปได้)</p>

    <?php if($errors): ?><div class="alert"><?php echo e(implode("\n",$errors)); ?></div><?php endif; ?>
    <?php if($success): ?><div class="success"><?php echo e($success); ?></div><?php endif; ?>

    <form class="form" method="post">
      <label>อีเมล (แก้ไม่ได้)</label>
      <input value="<?php echo e($user["email"]); ?>" disabled>

      <label>ชื่อแสดง</label>
      <input name="fullname" value="<?php echo e($user["fullname"]); ?>" required>

      <label>ลิงก์รูปโปรไฟล์ (Avatar URL)</label>
      <input name="avatar_url" value="<?php echo e($user["avatar_url"] ?? ""); ?>" placeholder="https://...">

      <button class="btn blue" type="submit">บันทึก</button>
    </form>
  </div>
</div>
<?php require_once __DIR__ . "/partials/footer.php"; ?>
</body></html>
