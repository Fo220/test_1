<?php
require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../config/auth.php";
require_admin();

$id=(int)($_GET["id"] ?? 0);
$stmt=$pdo->prepare("SELECT * FROM books WHERE id=?"); $stmt->execute([$id]); $b=$stmt->fetch();
if(!$b) die("Book not found");

$uploadDir=__DIR__ . "/../uploads/previews";
if(!is_dir($uploadDir)) mkdir($uploadDir,0777,true);

$error=""; $ok="";
if($_SERVER["REQUEST_METHOD"]==="POST" && isset($_FILES["preview"])){
  $f=$_FILES["preview"];
  if($f["error"]!==UPLOAD_ERR_OK) $error="อัปโหลดไม่สำเร็จ";
  else{
    $ext=strtolower(pathinfo($f["name"], PATHINFO_EXTENSION));
    $allowed=["pdf","jpg","jpeg","png","webp"];
    if(!in_array($ext,$allowed,true)) $error="รองรับ pdf/jpg/jpeg/png/webp เท่านั้น";
    else{
      if(!empty($b["preview_path"])){
        $fs=realpath(__DIR__ . "/../" . ltrim($b["preview_path"], "/"));
        if($fs && file_exists($fs)) @unlink($fs);
      }
      $name="preview_".$id."_".time().".".$ext;
      $dest=$uploadDir."/".$name;
      if(move_uploaded_file($f["tmp_name"],$dest)){
        $public="../uploads/previews/".$name;
        $pdo->prepare("UPDATE books SET preview_path=? WHERE id=?")->execute([$public,$id]);
        $ok="อัปโหลดสำเร็จ";
        $stmt=$pdo->prepare("SELECT * FROM books WHERE id=?"); $stmt->execute([$id]); $b=$stmt->fetch();
      } else $error="ย้ายไฟล์ไม่สำเร็จ";
    }
  }
}

$title="ทดลองอ่าน | Admin";
include __DIR__ . "/partials/admin_header.php";
?>
<div class="container section">
  <div class="card">
    <div style="display:flex;justify-content:space-between;gap:10px;flex-wrap:wrap;align-items:center">
      <div>
        <div class="badge"><span class="dot ok"></span> ทดลองอ่าน: <?php echo e($b["title"]); ?></div>
        <p class="muted" style="margin:10px 0 0">อัปโหลดไฟล์ตัวอย่าง (PDF/รูป) เพื่อให้ลูกค้ากด “ทดลองอ่าน”</p>
      </div>
      <a class="btn ghost" href="books.php">กลับ</a>
    </div>

    <?php if($error): ?><div class="alert"><?php echo e($error); ?></div><?php endif; ?>
    <?php if($ok): ?><div class="success"><?php echo e($ok); ?></div><?php endif; ?>

    <?php if(!empty($b["preview_path"])): ?>
      <div class="card" style="margin-top:12px">
        <div class="badge"><span class="dot ok"></span> ไฟล์ปัจจุบัน</div>
        <div style="display:flex;gap:10px;flex-wrap:wrap;margin-top:12px;align-items:center">
          <a class="btn ghost" target="_blank" rel="noopener" href="<?php echo e($b["preview_path"]); ?>">เปิดดู</a>
          <span class="small"><?php echo e($b["preview_path"]); ?></span>
        </div>
      </div>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data" class="form" style="margin-top:12px;max-width:520px">
      <label>เลือกไฟล์ทดลองอ่าน</label>
      <input type="file" name="preview" accept=".pdf,.jpg,.jpeg,.png,.webp" required>
      <button class="btn" type="submit">อัปโหลด</button>
    </form>
  </div>
</div>
<?php include __DIR__ . "/partials/admin_footer.php"; ?>
