<?php
require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../config/auth.php";

if(is_logged_in()){
  header("Location: index.php"); exit;
}

$mode = $_GET["mode"] ?? "login";
$errors = [];
$success = "";

if($_SERVER["REQUEST_METHOD"] === "POST" && ($_POST["action"] ?? "") === "login"){
  $email = trim($_POST["email"] ?? "");
  $password = (string)($_POST["password"] ?? "");
  if(!$email || !$password){
    $errors[] = "กรุณากรอกอีเมลและรหัสผ่าน";
  } else {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email=? LIMIT 1");
    $stmt->execute([$email]);
    $u = $stmt->fetch();
    if(!$u || !password_verify($password, $u["password_hash"])){
      $errors[] = "อีเมลหรือรหัสผ่านไม่ถูกต้อง";
    } else if(($u["status"] ?? "active") !== "active"){
      $errors[] = "บัญชีถูกระงับการใช้งาน";
    } else {
      $_SESSION["user_id"] = (int)$u["id"];
      $_SESSION["role"] = $u["role"];
      if(!empty($_POST["remember_me"])){
        issue_remember_token($pdo, (int)$u["id"]);
      }
      header("Location: index.php"); exit;
    }
  }
  $mode = "login";
}

if($_SERVER["REQUEST_METHOD"] === "POST" && ($_POST["action"] ?? "") === "register"){
  $fullname = trim($_POST["fullname"] ?? "");
  $email = trim($_POST["email"] ?? "");
  $password = (string)($_POST["password"] ?? "");
  $password2 = (string)($_POST["password2"] ?? "");

  if(!$fullname || !$email || !$password || !$password2){
    $errors[] = "กรุณากรอกข้อมูลให้ครบ";
  } elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)){
    $errors[] = "รูปแบบอีเมลไม่ถูกต้อง";
  } elseif(strlen($password) < 8){
    $errors[] = "รหัสผ่านต้องยาวอย่างน้อย 8 ตัวอักษร";
  } elseif($password !== $password2){
    $errors[] = "รหัสผ่านไม่ตรงกัน";
  } else {
    $exists = $pdo->prepare("SELECT id FROM users WHERE email=? LIMIT 1");
    $exists->execute([$email]);
    if($exists->fetch()){
      $errors[] = "อีเมลนี้ถูกใช้แล้ว";
    } else {
      $hash = password_hash($password, PASSWORD_DEFAULT);
      $ins = $pdo->prepare("INSERT INTO users(fullname,email,password_hash,role,status) VALUES (?,?,?,?,?)");
      $ins->execute([$fullname,$email,$hash,"user","active"]);
      $success = "สมัครสมาชิกสำเร็จ! กรุณาเข้าสู่ระบบ";
      $mode = "login";
    }
  }
}
?>
<!doctype html>
<html lang="th">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>เข้าสู่ระบบ / สมัครสมาชิก</title>
  <link rel="stylesheet" href="assets/css/style.css"/>
</head>
<body>
<div class="bg">
  <div class="auth-overlay show">
    <div class="auth-backdrop" onclick="window.location.href='index.php'"></div>

    <div class="auth-modal" role="dialog" aria-modal="true">
      <div class="auth-top">
        <h2><?php echo ($mode==="register") ? "สมัครสมาชิก" : "ล็อกอินเข้าสู่ระบบ"; ?></h2>
        <button class="auth-close" onclick="window.location.href='index.php'">✕</button>
      </div>

      <div class="auth-body">
        <p class="auth-subtitle">เข้าสู่ระบบผ่าน Social Network</p>

        <div class="social-stack">
  <a class="social-btn fb" href="social_login.php?provider=facebook" title="ต้องตั้งค่า OAuth ของ Facebook ก่อน">
    <span class="ico"><img alt="Facebook" src="data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAyNCAyNCIgZmlsbD0id2hpdGUiPjxwYXRoIGQ9Ik0xMy41IDIydi04aDIuN2wuNC0zSDEzLjVWOWMwLS45LjItMS41IDEuNS0xLjVoMS42VjQuOGMtLjMgMC0xLjMtLjEtMi41LS4xLTIuNSAwLTQuMiAxLjUtNC4yIDQuM3YyLjRINy4ydjNIOS44djhoMy43eiIvPjwvc3ZnPg==" style="width:18px;height:18px"/></span>
    <span>Facebook</span>
  </a>
  <a class="social-btn line" href="social_login.php?provider=line" title="ต้องตั้งค่า LINE Login ก่อน">
    <span class="ico"><img alt="LINE" src="data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAyNCAyNCIgZmlsbD0id2hpdGUiPjxwYXRoIGQ9Ik0xOS41IDEwLjhjMC0zLjYtMy40LTYuNS03LjUtNi41UzQuNSA3LjIgNC41IDEwLjhjMCAzLjIgMi43IDUuOSA2LjQgNi40LjIuMS41LjMuNi41LjEuMi4xLjYuMS44IDAgMC0uMS42LS4xLjcgMCAuMi0uMi44LjcuNC45LS40IDQuOS0yLjkgNi43LTUuMSAxLjMtMS40IDEuMS0yLjYgMS4xLTMuN3oiLz48L3N2Zz4=" style="width:18px;height:18px"/></span>
    <span>LINE</span>
  </a>
  <a class="social-btn apple" href="social_login.php?provider=apple" title="ต้องตั้งค่า Sign in with Apple ก่อน">
    <span class="ico"><img alt="Apple" src="data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAyNCAyNCIgZmlsbD0id2hpdGUiPjxwYXRoIGQ9Ik0xNi42IDEzLjRjMC0yIDEuNi0zIDEuNy0zLjEtMS0xLjQtMi41LTEuNi0zLTEuNi0xLjMtLjEtMi41LjgtMy4xLjgtLjYgMC0xLjYtLjgtMi42LS44LTEuMyAwLTIuNi44LTMuMyAyLTEuNCAyLjQtLjQgNiAxIDggMCAwIDEuMSAxLjYgMi40IDEuNiAxLjIgMCAxLjYtLjggMy0uOHMxLjguOCAzLjEuOGMxLjMgMCAyLjEtMS41IDIuMS0xLjUuOC0xLjIgMS4xLTIuNCAxLjEtMi41LS4xIDAtMi40LS45LTIuNC0zLjl6TTE0LjUgNi45Yy42LS43IDEtMS43LjktMi43LS45LjEtMS45LjYtMi41IDEuMy0uNi43LTEuMSAxLjctLjkgMi43IDEgLjEgMS45LS41IDIuNS0xLjN6Ii8+PC9zdmc+" style="width:18px;height:18px"/></span>
    <span>Apple</span>
  </a>
  <a class="social-btn google" href="social_login.php?provider=google">
    <span class="ico"><img alt="Google" src="data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCA0OCA0OCI+PHBhdGggZmlsbD0iI0ZGQzEwNyIgZD0iTTQzLjYgMjAuNUg0MlYyMEgyNHY4aDExLjNDMzMuNyAzMi43IDI5LjMgMzYgMjQgMzZjLTYuNiAwLTEyLTUuNC0xMi0xMnM1LjQtMTIgMTItMTJjMyAwIDUuNyAxLjEgNy44IDIuOWw1LjctNS43QzM0LjggNiAyOS43IDQgMjQgNCAxMi45IDQgNCAxMi45IDQgMjRzOC45IDIwIDIwIDIwIDIwLTguOSAyMC0yMGMwLTEuMS0uMS0yLjMtLjQtMy41eiIvPjxwYXRoIGZpbGw9IiNGRjNEMDAiIGQ9Ik02LjMgMTQuN2w2LjYgNC44QzE0LjcgMTYgMTguOSAxMiAyNCAxMmMzIDAgNS43IDEuMSA3LjggMi45bDUuNy01LjdDMzQuOCA2IDI5LjcgNCAyNCA0IDE2LjMgNCA5LjcgOC4zIDYuMyAxNC43eiIvPjxwYXRoIGZpbGw9IiM0Q0FGNTAiIGQ9Ik0yNCA0NGM1LjYgMCAxMC43LTIuMSAxNC42LTUuNWwtNi43LTUuNUMyOS45IDM0LjkgMjcuMSAzNiAyNCAzNmMtNS4zIDAtOS43LTMuMy0xMS4zLThINC41djUuMUM3LjggMzkuNyAxNS4zIDQ0IDI0IDQ0eiIvPjxwYXRoIGZpbGw9IiMxOTc2RDIiIGQ9Ik00My42IDIwLjVINDJWMjBIMjR2OGgxMS4zYy0xLjEgMy0zLjQgNS4yLTYuMyA2LjZsNi43IDUuNUMzOS44IDM2LjQgNDQgMzAuOCA0NCAyNGMwLTEuMS0uMS0yLjMtLjQtMy41eiIvPjwvc3ZnPg==" style="width:18px;height:18px"/></span>
    <span>Google</span>
  </a>
</div>

<div class="divider"><span>หรือ เข้าสู่ระบบด้วยบัญชีเว็บไซต์</span></div>

        <div class="auth-tabs">
          <button class="tab <?php echo ($mode==="login")?'active':''; ?>" onclick="switchMode('login')">ล็อกอิน</button>
          <button class="tab <?php echo ($mode==="register")?'active':''; ?>" onclick="switchMode('register')">สมัครสมาชิก</button>
        </div>

        <?php if(!empty($errors)): ?>
          <div class="alert"><?php echo e(implode("\n", $errors)); ?></div>
        <?php endif; ?>
        <?php if($success): ?>
          <div class="success"><?php echo e($success); ?></div>
        <?php endif; ?>

        <?php if($mode==="login"): ?>
          <form class="auth-grid" method="post">
            <input type="hidden" name="action" value="login"/>
            <label>อีเมล</label>
            <input name="email" type="email" autocomplete="username" placeholder="you@email.com" required/>

            <label>รหัสผ่าน</label>
            <input id="password" name="password" type="password" autocomplete="current-password" placeholder="Password" required/>

            <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap">
              <button class="btn ghost" type="button" data-toggle-password style="width:fit-content">แสดงรหัสผ่าน</button>
              <label class="small" style="display:flex;gap:8px;align-items:center">
                <input type="checkbox" name="remember_me" value="1" style="width:auto"> จดจำฉันไว้ (30 วัน)
              </label>
            </div>

            <button class="btn blue" type="submit">ล็อกอินเข้าสู่ระบบ</button>

            <div class="helper-links">
              <a href="javascript:void(0)" onclick="alert('ถ้าต้องการระบบลืมรหัสผ่านแบบส่งอีเมล บอกได้ เดี๋ยวทำให้')">ลืม Password</a>
              <a href="javascript:void(0)" onclick="switchMode('register')">สมัครสมาชิก</a>
            </div>
          </form>
        <?php else: ?>
          <form class="auth-grid" method="post">
            <input type="hidden" name="action" value="register"/>
            <label>Username / Display name</label>
            <input name="fullname" type="text" placeholder="Display name" required/>

            <label>E-mail</label>
            <input name="email" type="email" autocomplete="email" placeholder="you@email.com" required/>

            <label>Password</label>
            <input id="password" name="password" type="password" autocomplete="new-password" placeholder="อย่างน้อย 8 ตัว" required/>

            <label>Retype Password</label>
            <input name="password2" type="password" autocomplete="new-password" placeholder="พิมพ์อีกครั้ง" required/>

            <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap">
              <button class="btn ghost" type="button" data-toggle-password style="width:fit-content">แสดงรหัสผ่าน</button>
            </div>

            <button class="btn blue" type="submit">ส่งข้อมูล</button>

            <div class="helper-links">
              <a href="javascript:void(0)" onclick="switchMode('login')">มีบัญชีแล้ว? ล็อกอิน</a>
              <a href="index.php">กลับหน้าแรก</a>
            </div>
          </form>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<script>
function switchMode(m){
  const url = new URL(window.location.href);
  url.searchParams.set("mode", m);
  window.location.href = url.toString();
}
(function(){
  const btn=document.querySelector("[data-toggle-password]");
  const inp=document.querySelector("#password");
  if(!btn || !inp) return;
  btn.addEventListener("click", function(){
    inp.type = (inp.type==="password") ? "text" : "password";
    btn.textContent = (inp.type==="password") ? "แสดงรหัสผ่าน" : "ซ่อนรหัสผ่าน";
  });
})();
</script>
<?php require_once __DIR__ . "/partials/footer.php"; ?>
</body>
</html>
