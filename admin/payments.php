<?php
require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../config/auth.php";
require_admin();

if(isset($_GET["approve"])){
  $id=(int)$_GET["approve"];
  $p=$pdo->prepare("SELECT * FROM payments WHERE id=?"); $p->execute([$id]); $pay=$p->fetch();
  if($pay){
    $pdo->prepare("UPDATE payments SET status='approved' WHERE id=?")->execute([$id]);
    $pdo->prepare("UPDATE orders SET status='paid' WHERE id=?")->execute([(int)$pay["order_id"]]);
  }
  header("Location: payments.php"); exit;
}

if(isset($_GET["reject"])){
  $id=(int)$_GET["reject"];
  $p=$pdo->prepare("SELECT * FROM payments WHERE id=?"); $p->execute([$id]); $pay=$p->fetch();
  if($pay){
    $pdo->prepare("UPDATE payments SET status='rejected' WHERE id=?")->execute([$id]);
    // keep order as pending
  }
  header("Location: payments.php"); exit;
}

$stmt=$pdo->query("SELECT p.*, o.status AS order_status, o.total AS order_total, u.fullname, u.email
  FROM payments p
  JOIN orders o ON o.id=p.order_id
  JOIN users u ON u.id=p.user_id
  ORDER BY p.id DESC");

$title="Payments | Admin";
include __DIR__ . "/partials/admin_header.php";
?>
<div class="container section">
  <div class="card">
    <div class="badge"><span class="dot ok"></span> แจ้งชำระเงิน</div>

    <table class="table" style="margin-top:12px">
      <thead>
        <tr>
          <th>ID</th><th>Order</th><th>ลูกค้า</th><th>ยอดโอน</th><th>ยอดออเดอร์</th><th>วันเวลาโอน</th><th>สลิป</th><th>สถานะสลิป</th><th>จัดการ</th>
        </tr>
      </thead>
      <tbody>
      <?php while($p=$stmt->fetch()): ?>
        <tr>
          <td><?php echo (int)$p["id"]; ?></td>
          <td>#<?php echo (int)$p["order_id"]; ?><div class="small">order: <?php echo e($p["order_status"]); ?></div></td>
          <td>
            <?php echo e($p["fullname"]); ?>
            <div class="small"><?php echo e($p["email"]); ?></div>
          </td>
          <td>฿<?php echo number_format((float)$p["amount"],2); ?></td>
          <td>฿<?php echo number_format((float)$p["order_total"],2); ?></td>
          <td><?php echo e($p["transfer_datetime"]); ?></td>
          <td>
  <?php $sp = (string)($p["slip_path"] ?? ""); $ext=strtolower(pathinfo(parse_url($sp, PHP_URL_PATH) ?? $sp, PATHINFO_EXTENSION)); ?>
  <?php if($sp): ?>
    <button type="button" class="pill" style="cursor:pointer" data-slip-src="<?php echo e($sp); ?>" data-slip-ext="<?php echo e($ext); ?>">ดูสลิป</button>
    <a class="pill" style="margin-left:6px" target="_blank" rel="noopener" href="<?php echo e($sp); ?>">เปิดใหม่</a>
  <?php else: ?>-
  <?php endif; ?>
</td>
          <td><strong><?php echo e($p["status"]); ?></strong></td>
          <td style="display:flex;gap:8px;flex-wrap:wrap">
            <?php if($p["status"]!=="approved"): ?>
              <a class="btn" href="?approve=<?php echo (int)$p["id"]; ?>">ยืนยัน</a>
            <?php endif; ?>
            <?php if($p["status"]!=="rejected"): ?>
              <a class="btn ghost" href="?reject=<?php echo (int)$p["id"]; ?>">ปฏิเสธ</a>
            <?php endif; ?>
          </td>
        </tr>
      <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>
<!-- Slip Preview Overlay -->
<div class="overlay" id="slipOverlay">
  <div class="overlay-backdrop" data-ov-close></div>
  <div class="overlay-panel">
    <button class="overlay-close" data-ov-close>✕</button>
    <div class="overlay-body">
      <div class="overlay-header">
        <div class="overlay-title" id="slipTitle">สลิปโอนเงิน</div>
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
      if(ext==="pdf"){
        frame.src=src; frame.style.display="block";
      }else{
        img.src=src; img.style.display="block";
      }
    });
  });
})();
</script>

<?php include __DIR__ . "/partials/admin_footer.php"; ?>
