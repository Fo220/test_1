<?php
require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../config/auth.php";
require_admin();

// v20 soft delete to avoid FK constraint errors (orders)
if(isset($_GET["delete"]) && ctype_digit($_GET["delete"])){
  $idDel=(int)$_GET["delete"];
  try{
    $pdo->prepare("DELETE FROM books WHERE id=?")->execute([$idDel]);
    header("Location: books.php?msg=deleted"); exit;
  }catch(Exception $e){
    // fallback: soft delete (hide from shop)
    $pdo->prepare("UPDATE books SET is_deleted=1, is_published=0, stock=0 WHERE id=?")->execute([$idDel]);
    header("Location: books.php?msg=archived"); exit;
  }
}
if(isset($_GET["toggle"]) && ctype_digit($_GET["toggle"])){
  $idT=(int)$_GET["toggle"];
  $pdo->prepare("UPDATE books SET is_published = IFNULL(is_published,1)^1 WHERE id=?")->execute([$idT]);
  header("Location: books.php"); exit;
}


if(isset($_GET["del"])){
  $id=(int)$_GET["del"];
  $stmt=$pdo->prepare("SELECT cover_path FROM books WHERE id=?"); $stmt->execute([$id]); $b=$stmt->fetch();
  if($b && $b["cover_path"]){
    $fs=realpath(__DIR__ . "/../" . ltrim($b["cover_path"], "/"));
    if($fs && file_exists($fs)) @unlink($fs);
  }
  $pdo->prepare("DELETE FROM books WHERE id=?")->execute([$id]);
  header("Location: books.php"); exit;
}

$books=$pdo->query("SELECT b.*, c.name category_name FROM books b LEFT JOIN categories c ON b.category_id=c.id ORDER BY b.created_at DESC")->fetchAll();
$title="หนังสือ | Admin";
include __DIR__ . "/partials/admin_header.php";
?>
<div class="container section">
  <div class="card">
    <div style="display:flex;justify-content:space-between;gap:10px;flex-wrap:wrap;align-items:center">
      <div>
        <div class="badge"><span class="dot ok"></span> จัดการหนังสือ</div>
        <p class="muted" style="margin:10px 0 0">เพิ่ม/แก้ไข/ลบ และอัปโหลดรูปปก</p>
      </div>
      <a class="btn" href="book_form.php">+ เพิ่มหนังสือ</a>
    </div>

    <table class="table" style="margin-top:12px">
      <thead><tr><th>ID</th><th>ชื่อ</th><th>หมวด</th><th>ราคา</th><th>คงเหลือ</th><th>แสดง</th><th>จัดการ</th></tr></thead>
      <tbody>
        <?php foreach($books as $b): ?>
          <tr>
            <td><?php echo (int)$b["id"]; ?></td>
            <td><strong><?php echo e($b["title"]); ?></strong><br><span class="small"><?php echo e($b["author"] ?: "—"); ?> • <?php echo e($b["condition"]); ?></span></td>
            <td><?php echo e($b["category_name"] ?: "—"); ?></td>
            <td>฿<?php echo number_format((float)$b["price"],0); ?></td>
            <td><?php echo (int)$b["stock"]; ?></td>
            <td><?php echo $b["is_published"] ? "ใช่" : "ไม่"; ?></td>
            <td style="display:flex;gap:10px;flex-wrap:wrap">
              <a class="btn ghost" href="book_form.php?id=<?php echo (int)$b["id"]; ?>">แก้ไข</a> <a class="pill" href="books.php?toggle=<?php echo (int)$b['id']; ?>" style="padding:8px 10px"><?php echo ((int)($b["is_published"]??1)===1)?"ซ่อน":"เผยแพร่"; ?></a>
              <a class="btn ghost" href="book_cover.php?id=<?php echo (int)$b["id"]; ?>">ปก</a>
              <a class="btn ghost" href="book_preview.php?id=<?php echo (int)$b["id"]; ?>">ทดลองอ่าน</a>
              <a class="btn ghost" href="books.php?del=<?php echo (int)$b["id"]; ?>" onclick="return confirm('ลบหนังสือนี้?');">ลบ</a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php include __DIR__ . "/partials/admin_footer.php"; ?>
