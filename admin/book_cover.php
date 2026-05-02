<?php
require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../config/auth.php";
require_admin();

$id=(int)($_GET["id"] ?? 0);
$stmt=$pdo->prepare("SELECT * FROM books WHERE id=?"); $stmt->execute([$id]); $b=$stmt->fetch();
if(!$b) die("Book not found");

$uploadDir=__DIR__ . "/../uploads/covers";
if(!is_dir($uploadDir)) mkdir($uploadDir,0777,true);

$error=""; $ok="";
if($_SERVER["REQUEST_METHOD"]==="POST" && isset($_FILES["cover"])){
  $f=$_FILES["cover"];
  if($f["error"]!==UPLOAD_ERR_OK) $error="อัปโหลดไม่สำเร็จ";
  else{
    $ext=strtolower(pathinfo($f["name"], PATHINFO_EXTENSION));
    $allowed=["jpg","jpeg","png","webp"];
    if(!in_array($ext,$allowed,true)) $error="รองรับไฟล์ jpg/jpeg/png/webp เท่านั้น";
    else{
      if($b["cover_path"]){
        $fs=realpath(__DIR__ . "/../" . ltrim($b["cover_path"], "/"));
        if($fs && file_exists($fs)) @unlink($fs);
      }
      $name="book_".$id."_".time().".".$ext;
      $dest=$uploadDir."/".$name;
      if(move_uploaded_file($f["tmp_name"],$dest)){
        $public="../uploads/covers/".$name;
        $pdo->prepare("UPDATE books SET cover_path=? WHERE id=?")->execute([$public,$id]);
        $ok="อัปโหลดสำเร็จ";
        $stmt=$pdo->prepare("SELECT * FROM books WHERE id=?"); $stmt->execute([$id]); $b=$stmt->fetch();
      } else $error="ย้ายไฟล์ไม่สำเร็จ";
    }
  }
}

$title="รูปปก | Admin";
include __DIR__ . "/partials/admin_header.php";
?>
<div class="container section">
  <div class="card">
    <div style="display:flex;justify-content:space-between;gap:10px;flex-wrap:wrap;align-items:center">
      <div>
        <div class="badge"><span class="dot ok"></span> รูปปก: <?php echo e($b["title"]); ?></div>
        <p class="muted" style="margin:10px 0 0">อัปโหลดรูปปกเพื่อแสดงในหน้ารายละเอียด</p>
      </div>
      <a class="btn ghost" href="books.php">กลับ</a>
    </div>
    <?php if($error): ?><div class="alert"><?php echo e($error); ?></div><?php endif; ?>
    <?php if($ok): ?><div class="success"><?php echo e($ok); ?></div><?php endif; ?>
    <?php if($b["cover_path"]): ?>
      <img src="<?php echo e($b["cover_path"]); ?>" alt="" style="width:240px;max-width:100%;border-radius:16px;border:1px solid rgba(255,255,255,.12);margin-top:12px">
    <?php endif; ?>
    <form method="post" enctype="multipart/form-data" class="form" style="margin-top:12px;max-width:520px">
      <label>เลือกรูปปก</label><input type="file" name="cover" accept=".jpg,.jpeg,.png,.webp" required>
      <button class="btn" type="submit">อัปโหลด</button>
    </form>
  </div>
</div>
<?php include __DIR__ . "/partials/admin_footer.php"; ?>
