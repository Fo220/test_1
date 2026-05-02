<?php require_once __DIR__ . "/../../config/db.php"; require_once __DIR__ . "/../../config/auth.php"; ?>
<?php
$drawerUser = null;
if(isset($_SESSION['user_id']) && isset($pdo)){
  $st = $pdo->prepare('SELECT fullname,email,avatar_url FROM users WHERE id=?');
  $st->execute([ (int)$_SESSION['user_id'] ]);
  $drawerUser = $st->fetch();
}
?>
<!doctype html>
<html lang="th">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title><?php echo e($title ?? "Used Books"); ?></title>
  <link rel="stylesheet" href="assets/css/style.css"/>
  <script src="assets/js/ui.js"></script>
</head>
<body class="bg">
<?php if(!empty($_SESSION["flash_success"])): ?><div class="js-flash" data-type="success" data-msg="<?php echo htmlspecialchars($_SESSION["flash_success"]); ?>"></div><?php unset($_SESSION["flash_success"]); endif; ?>
<?php if(!empty($_SESSION["flash_error"])): ?><div class="js-flash" data-type="error" data-msg="<?php echo htmlspecialchars($_SESSION["flash_error"]); ?>"></div><?php unset($_SESSION["flash_error"]); endif; ?>

  <div class="nav">
  <div class="container nav-inner">
    <a class="brand" href="index.php">
      <span class="logo" aria-hidden="true"></span>
      <strong>UsedBooks Market</strong>
    </a>

    <div class="nav-search">
      <form class="search" action="shop.php" method="get">
        <input name="q" placeholder="ค้นหาหนังสือ / ผู้เขียน / หมวดหมู่..." value="<?php echo e($_GET['q'] ?? ''); ?>"/>
        <button class="search-btn" type="submit">ค้นหา</button>
      </form>
    </div>

    <div class="nav-links">
      <a class="pill" href="shop.php">ร้านหนังสือ</a>
      <a class="pill" href="cart.php">ตะกร้า</a>
      <?php if(isset($_SESSION["user_id"])): ?>
        <a class="pill" href="orders.php">คำสั่งซื้อ</a>
        <?php if(($_SESSION["role"] ?? "") === "admin"): ?>
          <a class="pill" href="../admin/index.php">หลังบ้าน</a>
        <?php endif; ?>
        <?php if($drawerUser): ?>
<button class="acct-btn" type="button" data-open-drawer><span class="acct-avatar"><?php if(!empty($drawerUser["avatar_url"])): ?><img src="<?php echo e($drawerUser["avatar_url"]); ?>" alt=""/><?php else: ?><?php echo e(mb_substr($drawerUser["fullname"],0,1)); ?><?php endif; ?></span><span class="acct-name"><?php echo e($drawerUser["fullname"]); ?></span></button>
<?php else: ?>
<a class="pill" href="logout.php">ออกจากระบบ</a>
<?php endif; ?>
      <?php else: ?>
        <a class="pill" href="auth.php?mode=login">เข้าสู่ระบบ</a>
        <a class="pill primary" href="auth.php?mode=register">สมัครสมาชิก</a>
      <?php endif; ?>
    </div>
  </div>
</div>
</div>
  </div>

<?php if(isset($_SESSION["user_id"]) && $drawerUser): ?>
<div class="acct-drawer-overlay">
  <div class="acct-drawer-backdrop"></div>
  <aside class="acct-drawer" aria-label="Account menu">
    <div class="drawer-top">
      <div class="drawer-user">
        <div class="pic">
          <?php if(!empty($drawerUser["avatar_url"])): ?>
            <img src="<?php echo e($drawerUser["avatar_url"]); ?>" alt=""/>
          <?php else: ?>
            <?php echo e(mb_substr($drawerUser["fullname"],0,1)); ?>
          <?php endif; ?>
        </div>
        <div class="who">
          <strong><?php echo e($drawerUser["fullname"]); ?></strong>
          <span><?php echo e($drawerUser["email"]); ?></span>
        </div>
      </div>
      <button class="drawer-close" type="button" data-close-drawer>✕</button>
    </div>

    <div class="drawer-list">
      <a class="drawer-item" href="orders.php"><span class="ico">📄</span> รายการสั่งซื้อของฉัน</a>
      <a class="drawer-item" href="profile.php"><span class="ico">👤</span> ข้อมูลส่วนตัว</a>
      <a class="drawer-item" href="address.php"><span class="ico">📍</span> ที่อยู่ของฉัน</a>
      <a class="drawer-item" href="privacy.php"><span class="ico">🔒</span> ความปลอดภัย & ความเป็นส่วนตัว</a>
      <a class="drawer-item" href="settings.php"><span class="ico">⚙️</span> ตั้งค่าบัญชี & เชื่อมต่อ</a>
      <a class="drawer-item" href="logout.php"><span class="ico">🚪</span> ออกจากระบบ</a>
    </div>
  </aside>
</div>
<script src="assets/js/drawer.js"></script>
<?php endif; ?>
