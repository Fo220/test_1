<?php
require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../config/auth.php";
require_admin();

$id=(int)($_GET["id"] ?? 0);
$book=null;
if($id){
  $stmt=$pdo->prepare("SELECT * FROM books WHERE id=?"); $stmt->execute([$id]); $book=$stmt->fetch();
  if(!$book) die("Book not found");
}
$cats=$pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();
$error="";

if($_SERVER["REQUEST_METHOD"]==="POST"){
  $titleB=trim($_POST["title"] ?? "");
  $author=trim($_POST["author"] ?? "");
  $cat = ($_POST["category_id"] ?? "") !== "" ? (int)$_POST["category_id"] : null;
  $price=(float)($_POST["price"] ?? 0);
  $cond=$_POST["condition"] ?? "ดี";
  $stock=(int)($_POST["stock"] ?? 0);
  $publisher=trim($_POST["publisher"] ?? "");
  $series=trim($_POST["series"] ?? "");
  $file_type=trim($_POST["file_type"] ?? "");
  $list_price=($_POST["list_price"] ?? "")!=="" ? (float)$_POST["list_price"] : null;
  $tags=trim($_POST["tags"] ?? "");
  $desc=trim($_POST["description"] ?? "");
  $pub=isset($_POST["is_published"]) ? 1 : 0;

  if($titleB==="" || $price<=0 || $stock<0) $error="กรุณากรอกชื่อ/ราคา/สต็อกให้ถูกต้อง";
  else{
    if($id){
      $stmt=$pdo->prepare("UPDATE books SET title=?, author=?, publisher=?, series=?, file_type=?, list_price=?, tags=?, category_id=?, price=?, `condition`=?, stock=?, description=?, is_published=? WHERE id=?");
      $stmt->execute([$titleB,$author?:null,$publisher?:null,$series?:null,$file_type?:null,$list_price,$tags?:null,$cat,$price,$cond,$stock,$desc?:null,$pub,$id]);
    }else{
      $stmt=$pdo->prepare("INSERT INTO books(title,author,publisher,series,file_type,list_price,tags,category_id,price,`condition`,stock,description,is_published) VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?)");
      $stmt->execute([$titleB,$author?:null,$publisher?:null,$series?:null,$file_type?:null,$list_price,$tags?:null,$cat,$price,$cond,$stock,$desc?:null,$pub]);
      $id=(int)$pdo->lastInsertId();
    }
    header("Location: books.php"); exit;
  }
}

$title=($id?"แก้ไขหนังสือ":"เพิ่มหนังสือ")." | Admin";
include __DIR__ . "/partials/admin_header.php";
?>
<div class="container section">
  <div class="card">
    <div class="badge"><span class="dot ok"></span> <?php echo $id?"แก้ไขหนังสือ":"เพิ่มหนังสือ"; ?></div>
    <?php if($error): ?><div class="alert"><?php echo e($error); ?></div><?php endif; ?>

    <form method="post" class="form" style="margin-top:12px">
      <label>ชื่อหนังสือ</label><input name="title" value="<?php echo e($book["title"] ?? ""); ?>" required>
      <label>ผู้แต่ง</label><input name="author" value="<?php echo e($book["author"] ?? ""); ?>">
<label>สำนักพิมพ์</label><input name="publisher" value="<?php echo e($book["publisher"] ?? ""); ?>">
<label>ซีรีส์</label><input name="series" value="<?php echo e($book["series"] ?? ""); ?>">
<div class="split">
  <div><label>ประเภทไฟล์ (ถ้ามี)</label><input name="file_type" value="<?php echo e($book["file_type"] ?? ""); ?>" placeholder="pdf/epub"></div>
  <div><label>ราคาปก (ไม่บังคับ)</label><input name="list_price" type="number" step="0.01" value="<?php echo e($book["list_price"] ?? ""); ?>"></div>
</div>
<label>แท็ก (คั่นด้วย , )</label><input name="tags" value="<?php echo e($book["tags"] ?? ""); ?>" placeholder="มังงะ,แอ็กชัน,ดราม่า">

      <label>หมวดหมู่</label>
      <select name="category_id">
        <option value="">ไม่ระบุ</option>
        <?php foreach($cats as $c): ?>
          <option value="<?php echo (int)$c["id"]; ?>" <?php echo (isset($book["category_id"]) && (int)$book["category_id"]===(int)$c["id"]) ? "selected":""; ?>><?php echo e($c["name"]); ?></option>
        <?php endforeach; ?>
      </select>
      <div class="split">
        <div><label>ราคา</label><input name="price" type="number" step="0.01" value="<?php echo e($book["price"] ?? ""); ?>" required></div>
        <div><label>คงเหลือ</label><input name="stock" type="number" min="0" value="<?php echo e($book["stock"] ?? 0); ?>" required></div>
      </div>
      <label>สภาพ</label>
      <select name="condition">
        <?php
          $conds=["เหมือนใหม่","ดีมาก","ดี","พอใช้"];
          $cur=$book["condition"] ?? "ดี";
          foreach($conds as $x){ $sel=($cur===$x)?"selected":""; echo "<option value='".e($x)."' $sel>".e($x)."</option>"; }
        ?>
      </select>
      <label>รายละเอียด</label><textarea name="description"><?php echo e($book["description"] ?? ""); ?></textarea>
      <label class="pill" style="cursor:pointer;width:fit-content">
        <input type="checkbox" name="is_published" value="1" <?php echo (($book["is_published"] ?? 1) ? "checked":""); ?> style="accent-color: var(--accent);"> แสดงบนหน้าเว็บ
      </label>
      <div style="display:flex;gap:10px;flex-wrap:wrap">
        <button class="btn" type="submit">บันทึก</button>
        <a class="btn ghost" href="books.php">กลับ</a>
        <?php if($id): ?>
<a class="btn ghost" href="book_cover.php?id=<?php echo $id; ?>">อัปโหลดปก</a>
<a class="btn ghost" href="book_preview.php?id=<?php echo $id; ?>">อัปโหลดไฟล์ทดลองอ่าน</a>
<?php endif; ?>
      </div>
    </form>
  </div>
</div>
<?php include __DIR__ . "/partials/admin_footer.php"; ?>
