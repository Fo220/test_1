<?php
require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../config/auth.php";

$error = "";
if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $email = trim($_POST["email"] ?? "");
  $password = $_POST["password"] ?? "";

  $stmt = $pdo->prepare("SELECT id, fullname, password_hash, role, status FROM users WHERE email=? AND role='admin' LIMIT 1");
  $stmt->execute([$email]);
  $u = $stmt->fetch();

  if(!$u || !password_verify($password, $u["password_hash"])) $error = "อีเมลหรือรหัสผ่านแอดมินไม่ถูกต้อง";
  elseif($u["status"] === "blocked") $error = "บัญชีแอดมินถูกระงับ";
  else {
    $_SESSION["user_id"] = (int)$u["id"];
      $_SESSION["role"] = $u["role"];
    setcookie("admin_email", $email, time()+60*60*24*365, "/");
      if(!empty($_POST["remember_me"])){ issue_remember_token($pdo, (int)$u["id"]); }

    $_SESSION["role"] = $u["role"];
    setcookie("admin_email", $email, time()+60*60*24*365, "/");
    $_SESSION["fullname"] = $u["fullname"];
    header("Location: index.php"); exit;
  }
}

$title = "Admin Login | UsedBooks";
?>
<!doctype html>
<html lang="th">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <link rel="stylesheet" href="../public/assets/css/style.css"/>
  <title><?php echo e($title); ?></title>
</head>
<body class="bg">
  <div class="container section">
    <div class="card" style="max-width:520px;margin:0 auto">
      <div class="badge"><span class="dot ok"></span> Admin Login</div>
      <p class="muted" style="margin:10px 0 0">เข้าสู่ระบบสำหรับผู้ดูแลเท่านั้น</p>

      <?php if($error): ?><div class="alert"><?php echo e($error); ?></div><?php endif; ?>

      <form method="post" class="form" style="margin-top:12px">
        <label>อีเมลแอดมิน</label>
        <input name="email" type="email" required value="<?php echo e($_COOKIE['admin_email'] ?? ''); ?>">

        <label>รหัสผ่าน</label>
        <input name="password" id="password" type="password" required>

        <button class="btn ghost" type="button" data-toggle-password style="width:fit-content">แสดงรหัสผ่าน</button>
<button class="btn" type="submit">เข้าสู่ระบบหลังบ้าน</button>
        <a class="pill" href="../public/index.php" style="justify-content:center">กลับหน้าเว็บ</a>

        <div class="small" style="margin-top:10px">ทดสอบแอดมิน: admin@demo.com / admin1234</div>
      </form>
    </div>
  </div>
<script>
(function(){
  const btn=document.querySelector("[data-toggle-password]");
  const inp=document.getElementById("password");
  if(btn && inp){
    btn.addEventListener("click", ()=>{
      const show = inp.type === "password";
      inp.type = show ? "text" : "password";
      btn.textContent = show ? "ซ่อนรหัสผ่าน" : "แสดงรหัสผ่าน";
    });
  }
})();
</script>
</body>
</html>
