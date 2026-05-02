<?php
require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../config/auth.php";
require_once __DIR__ . "/partials/cart_lib.php";

if($_SERVER["REQUEST_METHOD"]==="POST"){
  foreach((array)($_POST["qty"] ?? []) as $bookId=>$qty) cart_set((int)$bookId,(int)$qty);
  if(isset($_POST["clear"])) cart_clear();
  header("Location: cart.php"); exit;
}

$cart=cart_get(); $items=[]; $total=0;
if($cart){
  $ids=array_keys($cart);
  $in=implode(",", array_fill(0,count($ids),"?"));
  $stmt=$pdo->prepare("SELECT * FROM books WHERE id IN ($in) AND IFNULL(is_deleted,0)=0 AND IFNULL(is_published,1)=1");
  $stmt->execute($ids);
  $books=$stmt->fetchAll(); $map=[];
  foreach($books as $b) $map[(int)$b["id"]]=$b;

  foreach($cart as $id=>$qty){
    if(!isset($map[$id])) continue;
    $b=$map[$id];
                   $stock = (int)($b["stock"] ?? 0);
if($stock > 0){
  $qty = min((int)$qty, $stock);
}else{
  $qty = (int)$qty; // stock=0 => ไม่จำกัด (ถือว่าไม่ track สต็อก)
}
    if($qty<=0) continue;
    $sub=(float)$b["price"]*$qty; $total+=$sub;
    $items[]=["b"=>$b,"qty"=>$qty,"sub"=>$sub];
  }
}

$title="ตะกร้า | UsedBooks";
include __DIR__ . "/partials/header.php";
?>
<div class="container section">
  <div class="card">
    <div class="badge"><span class="dot ok"></span> ตะกร้าสินค้า</div>
    <?php if(!$items): ?>
      <p class="muted" style="margin-top:12px">ตะกร้าว่าง</p>
      <a class="btn" href="shop.php">ไปเลือกซื้อ</a>
    <?php else: ?>
      <form method="post" style="margin-top:12px">
        <table class="table">
          <thead><tr><th>หนังสือ</th><th>ราคา</th><th>จำนวน</th><th>รวม</th></tr></thead>
          <tbody>
            <?php foreach($items as $it): $b=$it["b"]; ?>
              <tr>
                <td><strong><?php echo e($b["title"]); ?></strong><br><span class="small"><?php echo e($b["condition"]); ?></span></td>
                <td>฿<?php echo number_format((float)$b["price"],0); ?></td>
                <td style="max-width:180px">
                  <input type="number" min="0" max="<?php echo (int)$b["stock"]; ?>" name="qty[<?php echo (int)$b["id"]; ?>]" value="<?php echo (int)$it["qty"]; ?>">
                  <div class="small">คงเหลือ <?php echo (int)$b["stock"]; ?></div>
                </td>
                <td>฿<?php echo number_format((float)$it["sub"],0); ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>

        <div class="split">
          <div class="card" style="padding:14px">
            <div class="small">ยอดรวม</div>
            <div style="font-size:26px;font-weight:900">฿<?php echo number_format((float)$total,0); ?></div>
          </div>
          <div class="card" style="padding:14px;display:flex;gap:10px;flex-wrap:wrap;align-items:center;justify-content:flex-end">
            <button class="btn ghost" name="clear" value="1" type="submit" onclick="return confirm('ล้างตะกร้า?');">ล้างตะกร้า</button>
            <button class="btn" type="submit">อัปเดตจำนวน</button>
            <a class="btn" href="checkout.php">ไปสั่งซื้อ</a>
          </div>
        </div>
      </form>
    <?php endif; ?>
  </div>
</div>
<?php include __DIR__ . "/partials/footer.php"; ?>
