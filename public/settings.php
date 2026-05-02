<?php
$title="ตั้งค่าบัญชี & เชื่อมต่อ";
require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../config/auth.php";
require_login();

$uid=(int)$_SESSION["user_id"];
$errors=[]; $success="";

if($_SERVER["REQUEST_METHOD"]==="POST" && ($_POST["action"] ?? "")==="change_password"){
  $old = (string)($_POST["old_password"] ?? "");
  $new = (string)($_POST["new_password"] ?? "");
  $new2= (string)($_POST["new_password2"] ?? "");
  if(!$old || !$new || !$new2) $errors[]="กรุณากรอกข้อมูลให้ครบ";
  elseif(strlen($new) < 8) $errors[]="รหัสผ่านใหม่ต้องยาวอย่างน้อย 8 ตัวอักษร";
  elseif($new !== $new2) $errors[]="รหัสผ่านใหม่ไม่ตรงกัน";
  else{
    $st=$pdo->prepare("SELECT password_hash FROM users WHERE id=?");
    $st->execute([$uid]);
    $u=$st->fetch();
    if(!$u || !password_verify($old, $u["password_hash"])) $errors[]="รหัสผ่านเดิมไม่ถูกต้อง";
    else{
      $hash=password_hash($new, PASSWORD_DEFAULT);
      $pdo->prepare("UPDATE users SET password_hash=? WHERE id=?")->execute([$hash,$uid]);
      $success="เปลี่ยนรหัสผ่านเรียบร้อย";
    }
  }
}

require_once __DIR__ . "/partials/header.php";
?>
<div class="container section">
  <div class="card">
    <h2 style="margin-top:0">ตั้งค่าบัญชี & เชื่อมต่อ</h2>

    <?php if($errors): ?><div class="alert"><?php echo e(implode("\n",$errors)); ?></div><?php endif; ?>
    <?php if($success): ?><div class="success"><?php echo e($success); ?></div><?php endif; ?>

    <h3 style="margin-bottom:8px">เปลี่ยนรหัสผ่าน</h3>
    <form class="form" method="post">
      <input type="hidden" name="action" value="change_password">
      <label>รหัสผ่านเดิม</label>
      <input type="password" name="old_password" required>

      <label>รหัสผ่านใหม่</label>
      <input type="password" name="new_password" required>

      <label>ยืนยันรหัสผ่านใหม่</label>
      <input type="password" name="new_password2" required>

      <button class="btn blue" type="submit">บันทึก</button>
    </form>

    <hr style="border:0;border-top:1px solid var(--stroke);margin:18px 0">

    <h3 style="margin-bottom:8px">เชื่อมต่อบัญชี</h3>
    <p class="muted">Google Login ใช้งานได้จริง (ตั้งค่าใน <code>config/google.php</code>)</p>
    <a class="btn ghost" href="login_google.php">เชื่อมต่อ/เข้าสู่ระบบด้วย Google</a>
  </div>
</div>
<?php require_once __DIR__ . "/partials/footer.php"; ?>
</body></html>
