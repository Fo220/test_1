<?php
require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../config/auth.php";

$title = "หน้าแรก | UsedBooks Market";

$bookCount = (int)$pdo->query("SELECT COUNT(*) c FROM books WHERE is_published=1")->fetch()["c"];
$userCount = (int)$pdo->query("SELECT COUNT(*) c FROM users")->fetch()["c"];
$cats = $pdo->query("SELECT id,name FROM categories ORDER BY name")->fetchAll();

$orderCount = (int)$pdo->query("SELECT COUNT(*) c FROM orders")->fetch()["c"];

$latest = $pdo->query("SELECT b.*, c.name category_name
  FROM books b LEFT JOIN categories c ON b.category_id=c.id
  WHERE b.is_published=1 AND IFNULL(b.is_deleted,0)=0
  ORDER BY b.created_at DESC LIMIT 8")->fetchAll();

include __DIR__ . "/partials/header.php";
?>
  <div class="container hero">
    <div class="hero-grid">
      <div class="card">
        <div class="badge"><span class="dot ok"></span> ตลาดหนังสือมือสอง</div>
        <h1 class="h1">ซื้อ-ขายหนังสือมือสอง<br/>ใช้งานได้จริง</h1>
        <p class="muted">
          มีระบบสมาชิก/ล็อกอิน, ตะกร้าสินค้า, สั่งซื้อ และหลังบ้านสำหรับแอดมิน
        </p>
        <div style="display:flex;gap:10px;flex-wrap:wrap;margin-top:14px">
          <a class="btn blue" href="shop.php">เริ่มช้อปเลย</a>
          <a class="btn ghost" href="register.php">สมัครสมาชิก</a>
                <div style="margin-top:14px">
          <div class="small" style="margin-bottom:8px">หมวดหมู่ยอดนิยม</div>
          <div class="chips">
            <a class="chip active" href="shop.php">ทั้งหมด</a>
            <?php foreach($cats as $c): ?>
              <a class="chip" href="shop.php?cat=<?php echo (int)$c["id"]; ?>"><?php echo e($c["name"]); ?></a>
            <?php endforeach; ?>
          </div>
        </div>

      </div>

        <div class="split" style="margin-top:16px">
          <div class="card" style="padding:14px">
            <div class="small">หนังสือในระบบ</div>
            <div style="font-size:22px;font-weight:900"><?php echo $bookCount; ?> เล่ม</div>
          </div>
          <div class="card" style="padding:14px">
            <div class="small">คำสั่งซื้อทั้งหมด</div>
            <div style="font-size:22px;font-weight:900"><?php echo $orderCount; ?> รายการ</div>
          </div>
        </div>
        <div class="small" style="margin-top:10px;color:rgba(234,240,255,.7)">สมาชิกในระบบ: <?php echo $userCount; ?></div>
      </div>

      <div class="card">
        <div class="badge"><span class="dot ok"></span> ทดสอบแอดมิน</div>
        <div class="book" style="margin-top:12px">
          <div class="small">Email: admin@books.local</div>
          <div class="small">Pass: Admin@1234</div>
          <div class="small" style="margin-top:8px;color:rgba(234,240,255,.7)">
            ถ้าเข้าไม่ได้: เปิด /public/reset_admin.php แล้วลบทิ้ง
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="container section">
    <div class="card" style="margin-bottom:12px">
      <div class="badge"><span class="dot ok"></span> หนังสือมาใหม่</div>
      <p class="muted" style="margin:10px 0 0">เลือกหนังสือมือสองสภาพดี ราคาคุ้มค่า</p>
    </div>

    <div class="grid">
      <?php foreach($latest as $b): ?>
        <div class="book">
          <div class="book-thumb">
  <?php if(!empty($b["cover_path"])): ?>
    <img class="zoomable" src="<?php echo e($b["cover_path"]); ?>" alt="<?php echo e($b["title"]); ?>">
  <?php else: ?>
    <div class="placeholder">ไม่มีรูปปก</div>
  <?php endif; ?>
</div>
<div class="badge"><span class="dot ok"></span> <?php echo e($b["condition"]); ?></div>
          <h3><?php echo e($b["title"]); ?></h3>
          <div class="muted"><?php echo e($b["author"] ?: "—"); ?></div>
          <div class="meta">
            <span class="tag"><?php echo e($b["category_name"] ?: "ไม่ระบุหมวด"); ?></span>
            <span class="tag">คงเหลือ <?php echo (int)$b["stock"]; ?></span>
          </div>
          <div class="price">฿<?php echo number_format((float)$b["price"], 0); ?></div>
          <a class="btn ghost" href="book.php?id=<?php echo (int)$b["id"]; ?>">ดูรายละเอียด</a>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
<?php include __DIR__ . "/partials/footer.php"; ?>
