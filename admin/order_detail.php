<?php
require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../config/auth.php";
require_admin();

$id=(int)($_GET["id"] ?? 0);
$stmt=$pdo->prepare("SELECT o.*, u.fullname, u.email FROM orders o JOIN users u ON o.user_id=u.id WHERE o.id=?");
$stmt->execute([$id]); $o=$stmt->fetch();
if(!$o){ http_response_code(404); die("Order not found"); }

$it=$pdo->prepare("SELECT * FROM order_items WHERE order_id=?");
$it->execute([$id]); $items=$it->fetchAll();

// latest payment (for slip preview)
$pstmt=$pdo->prepare("SELECT * FROM payments WHERE order_id=? ORDER BY created_at DESC LIMIT 1");
$pstmt->execute([$id]); $pay=$pstmt->fetch();


$title="รายละเอียดออเดอร์ #$id | Admin";
include __DIR__ . "/partials/admin_header.php";
?>
<div class="container section">
  <div class="card">
    <div class="badge"><span class="dot ok"></span> ออเดอร์ #<?php echo (int)$o["id"]; ?></div>
    <p class="muted" style="margin:10px 0 0">ลูกค้า: <strong><?php echo e($o["fullname"]); ?></strong> (<?php echo e($o["email"]); ?>) • สถานะ: <strong><?php echo e($o["status"]); ?></strong></p>

    <div class="split" style="margin-top:12px">
      <div class="card" style="padding:14px"><div class="small">ผู้รับ</div><div style="font-weight:900"><?php echo e($o["shipping_name"]); ?></div><div class="small"><?php echo e($o["shipping_phone"]); ?></div></div>
      <div class="card" style="padding:14px"><div class="small">ที่อยู่</div><div class="muted"><?php echo nl2br(e($o["shipping_address"])); ?></div></div>
    </div>

    <table class="table" style="margin-top:12px">
      <thead><tr><th>หนังสือ</th><th>ราคา</th><th>จำนวน</th><th>รวม</th></tr></thead>
      <tbody>
        <?php foreach($items as $x): ?>
          <tr>
            <td><?php echo e($x["title_snapshot"]); ?></td>
            <td>฿<?php echo number_format((float)$x["price_snapshot"],0); ?></td>
            <td><?php echo (int)$x["qty"]; ?></td>
            <td>฿<?php echo number_format((float)$x["price_snapshot"]*(int)$x["qty"],0); ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>

    <?php if(!empty($pay)): ?>
  <div class="card" style="padding:14px;margin-top:12px">
    <div style="display:flex;align-items:center;justify-content:space-between;gap:10px;flex-wrap:wrap">
      <div>
        <div class="small">การชำระเงิน</div>
        <div style="font-weight:900">สถานะ: <?php echo e($pay["status"] ?? "-"); ?></div>
        <div class="small">
          จำนวนเงิน: ฿<?php echo number_format((float)($pay["amount"] ?? 0),0); ?>
          • เวลา: <?php echo e($pay["created_at"] ?? "-"); ?>
          • วิธี: <?php echo e($pay["method"] ?? "transfer"); ?>
        </div>
      </div>
      <div style="display:flex;gap:10px;flex-wrap:wrap">
        <?php if(!empty($pay["slip_path"])): 
          $sp=(string)$pay["slip_path"];
          $ext=strtolower(pathinfo(parse_url($sp, PHP_URL_PATH) ?? $sp, PATHINFO_EXTENSION));
        ?>
          <button type="button" class="btn" style="border-radius:14px" data-slip-src="<?php echo e($sp); ?>" data-slip-ext="<?php echo e($ext); ?>">ดูสลิป</button>
          <a class="btn ghost" target="_blank" rel="noopener" href="<?php echo e($sp); ?>">เปิดใหม่</a>
        <?php else: ?>
          <span class="muted">ยังไม่มีสลิปแนบ</span>
        <?php endif; ?>
      </div>
    </div>
  </div>
<?php else: ?>
  <div class="card" style="padding:14px;margin-top:12px">
    <div class="small">การชำระเงิน</div>
    <div class="muted">ยังไม่มีข้อมูลการชำระเงินสำหรับออเดอร์นี้</div>
    <div style="margin-top:10px">
      <a class="pill" href="payments.php">ไปหน้าสลิปโอนเงิน</a>
    </div>
  </div>
<?php endif; ?>


    <div class="card" style="padding:14px;margin-top:12px">
      <div class="small">ยอดรวม</div>
      <div style="font-size:26px;font-weight:900">฿<?php echo number_format((float)$o["total"],0); ?></div>
    </div>

    <div style="display:flex;gap:10px;flex-wrap:wrap;margin-top:12px">
      <a class="btn ghost" href="orders.php">กลับไปออเดอร์</a>
    </div>
  </div>
</div>
<!-- Slip Preview Overlay -->
<div class="overlay" id="slipOverlay">
  <div class="overlay-backdrop" data-ov-close></div>
  <div class="overlay-panel">
    <button class="overlay-close" data-ov-close>✕</button>
    <div class="overlay-body">
      <div class="overlay-header">
        <div class="overlay-title">สลิปโอนเงิน</div>
        <div class="overlay-sub">กด ESC เพื่อปิด</div>
      </div>
      <img class="overlay-img" id="slipImg" alt="" style="display:none">
      <iframe class="overlay-iframe" id="slipFrame" style="display:none"></iframe>
      <div class="overlay-actions">
        <a class="btn ghost" href="javascript:void(0)" data-ov-close>ปิด</a>
      </div>
    </div>
  </div>
</div>

<script>
(function(){
  const overlay=document.getElementById("slipOverlay");
  const img=document.getElementById("slipImg");
  const frame=document.getElementById("slipFrame");
  const close=()=>{overlay.classList.remove("show"); img.src=""; frame.src="";};
  overlay.querySelectorAll("[data-ov-close]").forEach(el=>el.addEventListener("click", close));
  document.addEventListener("keydown",(e)=>{ if(e.key==="Escape") close(); });

  document.querySelectorAll("[data-slip-src]").forEach(btn=>{
    btn.addEventListener("click", ()=>{
      const src=btn.getAttribute("data-slip-src");
      const ext=(btn.getAttribute("data-slip-ext")||"").toLowerCase();
      overlay.classList.add("show");
      img.style.display="none"; frame.style.display="none";
      if(ext==="pdf"){ frame.src=src; frame.style.display="block"; }
      else { img.src=src; img.style.display="block"; }
    });
  });
})();
</script>

<?php include __DIR__ . "/partials/admin_footer.php"; ?>
