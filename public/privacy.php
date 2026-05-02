<?php
$title="ความปลอดภัย & ความเป็นส่วนตัว";
require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../config/auth.php";
require_login();
require_once __DIR__ . "/partials/header.php";
?>
<div class="container section">
  <div class="card">
    <h2 style="margin-top:0">ความปลอดภัย & ความเป็นส่วนตัว</h2>
    <p class="muted">
      - รหัสผ่านถูกเก็บแบบเข้ารหัส (hash)<br/>
      - ถ้าเลือก “จดจำฉันไว้” ระบบจะใช้ token และหมดอายุอัตโนมัติ<br/>
      - คุณสามารถเปลี่ยนรหัสผ่านได้ในหน้า “ตั้งค่าบัญชี & เชื่อมต่อ”
    </p>
    <a class="btn blue" href="settings.php">ไปที่ตั้งค่า</a>
  </div>
</div>
<?php require_once __DIR__ . "/partials/footer.php"; ?>
</body></html>
