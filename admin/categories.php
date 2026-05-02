<?php
require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../config/auth.php";
require_admin();

if(isset($_GET["del"])){
  $pdo->prepare("DELETE FROM categories WHERE id=?")->execute([(int)$_GET["del"]]);
  header("Location: categories.php"); exit;
}
if($_SERVER["REQUEST_METHOD"]==="POST"){
  $name=trim($_POST["name"]??"");
  if($name!=="") $pdo->prepare("INSERT IGNORE INTO categories(name) VALUES(?)")->execute([$name]);
  header("Location: categories.php"); exit;
}
$cats=$pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();
$title="หมวดหมู่ | Admin";
include __DIR__ . "/partials/admin_header.php";
?>
<div class="container section">
  <div class="card">
    <div class="badge"><span class="dot ok"></span> หมวดหมู่</div>
    <form method="post" class="form" style="margin-top:12px;max-width:520px">
      <label>เพิ่มหมวดหมู่</label><input name="name" required><button class="btn" type="submit">เพิ่ม</button>
    </form>
    <table class="table" style="margin-top:12px">
      <thead><tr><th>ID</th><th>ชื่อหมวด</th><th>จัดการ</th></tr></thead>
      <tbody>
        <?php foreach($cats as $c): ?>
          <tr>
            <td><?php echo (int)$c["id"]; ?></td>
            <td><?php echo e($c["name"]); ?></td>
            <td><a class="pill" href="categories.php?del=<?php echo (int)$c["id"]; ?>" onclick="return confirm('ลบหมวดนี้?');">ลบ</a></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php include __DIR__ . "/partials/admin_footer.php"; ?>
