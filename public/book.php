<?php
$title = "รายละเอียดหนังสือ";
require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../config/auth.php";

$id = (int)($_GET["id"] ?? 0);
$stmt = $pdo->prepare("SELECT b.*, c.name AS category_name FROM books b LEFT JOIN categories c ON c.id=b.category_id WHERE b.id=? AND b.is_published=1 AND IFNULL(b.is_deleted,0)=0 AND IFNULL(b.is_deleted,0)=0 LIMIT 1");
$stmt->execute([$id]);
$book = $stmt->fetch();
if(!$book){
  http_response_code(404);
  echo "ไม่พบหนังสือ"; exit;
}

require_once __DIR__ . "/partials/header.php";

$price = (float)$book["price"];
$rating = 4.8; // demo
$ratingsCount = 28; // demo
?>
<div class="container pd-wrap">
  <div class="pd-grid">
    <div class="pd-cover">
      <div style="position:relative">
        <?php if(($book["stock"] ?? 0) > 0): ?>
          <div class="pd-badge">Best Seller</div>
        <?php endif; ?>
        <div class="imgbox" data-zoom-src="<?php echo e($book["cover_path"] ?: ""); ?>">
          <?php if(!empty($book["cover_path"])): ?>
            <img src="<?php echo e($book["cover_path"]); ?>" alt="">
          <?php else: ?>
            <div class="placeholder">NO COVER</div>
          <?php endif; ?>
        </div>
      </div>
      <div class="small" style="margin-top:10px;color:rgba(15,23,42,.55)">
        คลิกที่รูปเพื่อซูมดูหน้าปก
      </div>
    </div>

    <div>
      <h1 class="pd-title"><?php echo e($book["title"]); ?></h1>

      <div class="pd-by">
        <?php if(!empty($book["author"])): ?>
          <span class="muted">โดย</span> <a href="shop.php?scope=author&q=<?php echo urlencode($book["author"]); ?>"><?php echo e($book["author"]); ?></a>
        <?php endif; ?>
        <?php if(!empty($book["category_name"])): ?>
          <span class="muted">หมวดหมู่</span> <a href="shop.php?cat=<?php echo (int)$book["category_id"]; ?>"><?php echo e($book["category_name"]); ?></a>
        <?php endif; ?>
        <?php if(!empty($book["publisher"])): ?>
          <span class="muted">สำนักพิมพ์</span> <a href="shop.php?q=<?php echo urlencode($book["publisher"]); ?>"><?php echo e($book["publisher"]); ?></a>
        <?php endif; ?>
      </div>

      <div class="pd-actions">
        <?php if(!empty($book["preview_path"])): ?>
          <button class="btn preview" type="button" data-preview-src="<?php echo e($book["preview_path"]); ?>">ทดลองอ่าน</button>
        <?php else: ?>
          <button class="btn preview" type="button" onclick="alert('ยังไม่มีไฟล์ทดลองอ่านสำหรับเล่มนี้')">ทดลองอ่าน</button>
        <?php endif; ?>
        <a class="btn buy" href="add_to_cart.php?id=<?php echo (int)$book['id']; ?>">ซื้อ <?php echo number_format($price,0); ?> บาท</a>
      </div>

      <div class="pd-rating">
        <span style="color:rgba(15,23,42,.7)"><?php echo number_format($rating,2); ?></span>
        <span class="stars">★★★★★</span>
        <span class="muted"><?php echo (int)$ratingsCount; ?> Rating</span>
      </div>

      <div class="pd-miniicons">
        <a class="miniicon" href="javascript:void(0)" onclick="alert('เพิ่มลงรายการโปรด (เดโม)')">
          <span class="dotbtn">🤍</span><span>อยากได้</span>
        </a>
        <a class="miniicon" href="javascript:void(0)" onclick="alert('ส่งเป็นของขวัญ (เดโม)')">
          <span class="dotbtn">🎁</span><span>ซื้อเป็นของขวัญ</span>
        </a>
        <a class="miniicon" href="javascript:void(0)" onclick="alert('ติดตาม (เดโม)')">
          <span class="dotbtn">➕</span><span>ติดตาม</span>
        </a>
        <a class="miniicon" href="javascript:void(0)" onclick="navigator.clipboard.writeText(location.href);alert('คัดลอกลิงก์แล้ว')">
          <span class="dotbtn">🔗</span><span>แชร์</span>
        </a>
      </div>

      <div class="pd-spec">
        <table>
          <tr><td>ซีรีส์</td><td><?php echo e($book["series"] ?? "-"); ?></td></tr>
          <tr><td>ประเภทไฟล์</td><td><?php echo e($book["file_type"] ?? (!empty($book["preview_path"]) ? "pdf" : "-")); ?></td></tr>
          <tr><td>สภาพ</td><td><?php echo e($book["condition"] ?? "ดี"); ?></td></tr>
          <tr><td>จำนวนคงเหลือ</td><td><?php echo (int)($book["stock"] ?? 0); ?></td></tr>
          <tr><td>ราคาปก</td><td><?php echo !empty($book["list_price"]) ? number_format((float)$book["list_price"],0) . " บาท" : "-"; ?></td></tr>
        </table>
      </div>

      <div class="pd-desc">
        <h3>เรื่องย่อ</h3>
        <div class="muted">
          <?php echo nl2br(e($book["description"] ?? "ยังไม่มีคำอธิบายสำหรับเล่มนี้")); ?>
        </div>

        <div class="tagrow">
          <?php foreach(array_filter(array_map("trim", explode(",", (string)($book["tags"] ?? "")))) as $tg): ?>
            <span class="tagpill"><?php echo e($tg); ?></span>
          <?php endforeach; ?>
          <?php if(empty($book["tags"])): ?>
            <span class="tagpill">มังงะญี่ปุ่น</span>
            <span class="tagpill">แอ็กชัน</span>
            <span class="tagpill">ดราม่า</span>
            <span class="tagpill">พลังพิเศษ</span>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Overlay reuse from v3 (zoom/preview) -->
<div class="overlay" id="pdOverlay">
  <div class="overlay-backdrop" data-ov-close></div>
  <div class="overlay-panel">
    <button class="overlay-close" data-ov-close>✕</button>
    <div class="overlay-body">
      <div class="overlay-header">
        <div class="overlay-title" id="ovTitle">ดูตัวอย่าง</div>
        <div class="overlay-sub" id="ovSub">กด ESC เพื่อปิด</div>
      </div>
      <img class="overlay-img" id="ovImg" alt="" style="display:none">
      <iframe class="overlay-iframe" id="ovFrame" style="display:none"></iframe>
      <div class="overlay-actions">
        <a class="btn ghost" href="javascript:void(0)" data-ov-close>ปิด</a>
      </div>
    </div>
  </div>
</div>

<script>
(function(){
  const overlay = document.getElementById("pdOverlay");
  const ovImg = document.getElementById("ovImg");
  const ovFrame = document.getElementById("ovFrame");
  const open = (type, src, title) => {
    document.getElementById("ovTitle").textContent = title || "ดูตัวอย่าง";
    overlay.classList.add("show");
    ovImg.style.display = "none";
    ovFrame.style.display = "none";
    if(type==="img"){
      ovImg.src = src; ovImg.style.display = "block";
    } else {
      ovFrame.src = src; ovFrame.style.display = "block";
    }
  };
  const close = ()=>{ overlay.classList.remove("show"); ovImg.src=""; ovFrame.src=""; };
  overlay.querySelectorAll("[data-ov-close]").forEach(el=>el.addEventListener("click", close));
  document.addEventListener("keydown", (e)=>{ if(e.key==="Escape") close(); });

  const imgbox = document.querySelector("[data-zoom-src]");
  if(imgbox){
    imgbox.addEventListener("click", ()=> {
      const src = imgbox.getAttribute("data-zoom-src");
      if(src) open("img", src, "ซูมหน้าปก");
    });
  }
  document.querySelectorAll("[data-preview-src]").forEach(btn=>{
    btn.addEventListener("click", ()=>{
      const src = btn.getAttribute("data-preview-src");
      if(src) open("pdf", src, "ทดลองอ่าน");
    });
  });
})();
</script>

<?php require_once __DIR__ . "/partials/footer.php"; ?>
</body></html>
