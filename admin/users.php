<?php
require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../config/auth.php";
require_admin();

if(isset($_GET["toggle"])){
  $id=(int)$_GET["toggle"];
  $stmt=$pdo->prepare("SELECT role,status FROM users WHERE id=?"); $stmt->execute([$id]); $u=$stmt->fetch();
  if($u && $u["role"]!=="admin"){
    $new=$u["status"]==="active" ? "blocked":"active";
    $pdo->prepare("UPDATE users SET status=? WHERE id=?")->execute([$new,$id]);
  }
  header("Location: users.php"); exit;
}

$users=$pdo->query("SELECT id,fullname,email,role,status,created_at FROM users ORDER BY created_at DESC")->fetchAll();
$title="สมาชิก | Admin";
include __DIR__ . "/partials/admin_header.php";
?>
<div class="container section">
  <div class="card">
    <div class="badge"><span class="dot ok"></span> สมาชิก</div>
    <table class="table" style="margin-top:12px">
      <thead><tr><th>ID</th><th>ชื่อ</th><th>อีเมล</th><th>บทบาท</th><th>สถานะ</th><th>จัดการ</th></tr></thead>
      <tbody>
        <?php foreach($users as $u): ?>
          <tr>
            <td><?php echo (int)$u["id"]; ?></td>
            <td><?php echo e($u["fullname"]); ?></td>
            <td><?php echo e($u["email"]); ?></td>
            <td><?php echo e($u["role"]); ?></td>
            <td><?php echo e($u["status"]); ?></td>
            <td>
              <?php if($u["role"]!=="admin"): ?>
                <a class="pill" href="users.php?toggle=<?php echo (int)$u["id"]; ?>"><?php echo $u["status"]==="active" ? "ระงับ":"ปลดระงับ"; ?></a>
              <?php else: ?><span class="small">—</span><?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php include __DIR__ . "/partials/admin_footer.php"; ?>
