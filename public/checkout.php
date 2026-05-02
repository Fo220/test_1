<?php
require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../config/auth.php";
require_once __DIR__ . "/partials/cart_lib.php";
require_login();

$cart=cart_get();
if(!$cart){ header("Location: cart.php"); exit; }

function load_items($pdo,$cart){
  $ids=array_keys($cart);
  $in=implode(",", array_fill(0,count($ids),"?"));
  $stmt=$pdo->prepare("SELECT * FROM books WHERE id IN ($in) AND is_published=1");
  $stmt->execute($ids);
  $books=$stmt->fetchAll(); $map=[];
  foreach($books as $b) $map[(int)$b["id"]]=$b;
  $items=[]; $total=0;
  foreach($cart as $id=>$qty){
    if(!isset($map[$id])) continue;
    $b=$map[$id];
    $qty=( (int)($b["stock"]??0) > 0 ? min((int)$qty,(int)$b["stock"]) : (int)$qty );
    if($qty<=0) continue;
    $sub=(float)$b["price"]*$qty; $total+=$sub;
    $items[]=["b"=>$b,"qty"=>$qty,"sub"=>$sub];
  }
  return [$items,$total];
}

[$items,$total]=load_items($pdo,$cart);
if(!$items){ cart_clear(); header("Location: cart.php"); exit; }

$error="";
if($_SERVER["REQUEST_METHOD"]==="POST"){
  $name=trim($_POST["name"]??"");
  $phone=trim($_POST["phone"]??"");
  $address=trim($_POST["address"]??"");
  if($name===""||$phone===""||$address==="") $error="กรุณากรอกข้อมูลจัดส่งให้ครบ";
  else{
    try{
      $pdo->beginTransaction();
      foreach($items as $it){
        $b=$it["b"]; $qty=$it["qty"];
        $stmt=$pdo->prepare("UPDATE books SET stock=stock-? WHERE id=? AND stock>=?");
        $stmt->execute([$qty,(int)$b["id"],$qty]);
        if($stmt->rowCount()===0) throw new Exception("สต็อกไม่พอ: ".$b["title"]);
      }
      $stmt=$pdo->prepare("INSERT INTO orders(user_id,total,status,shipping_name,shipping_phone,shipping_address) VALUES(?,?,?,?,?,?)");
      $stmt->execute([(int)$_SESSION["user_id"],$total,"pending",$name,$phone,$address]);
      $orderId=(int)$pdo->lastInsertId();
      // v21: insert order_items with snapshot columns if available, else fallback for legacy schema
$hasSnap = has_column($pdo,"order_items","title_snapshot") && has_column($pdo,"order_items","price_snapshot");
$ins = $hasSnap
  ? $pdo->prepare("INSERT INTO order_items(order_id,book_id,title_snapshot,price_snapshot,qty) VALUES(?,?,?,?,?)")
  : $pdo->prepare("INSERT INTO order_items(order_id,book_id,qty,price) VALUES(?,?,?,?)");
      foreach($items as $it){
        $b=$it["b"]; $qty=$it["qty"];
                    if($hasSnap){
  $ins->execute([$orderId,(int)$b["id"],$b["title"],(float)$b["price"],$qty]);
}else{
  $ins->execute([$orderId,(int)$b["id"],$qty,(float)$b["price"]]);
}
      }
      $pdo->commit();
      cart_clear();
      header("Location: order_detail.php?id=".$orderId); exit;
    }catch(Exception $ex){
      $pdo->rollBack();
      $error=$ex->getMessage();
      [$items,$total]=load_items($pdo,$cart);
    }
  }
}

$title="สั่งซื้อ | UsedBooks";
include __DIR__ . "/partials/header.php";
?>
<div class="container section">
  <div class="card">
    <div class="badge"><span class="dot ok"></span> ยืนยันสั่งซื้อ</div>
    <?php if($error): ?><div class="alert"><?php echo e($error); ?></div><?php endif; ?>

    <table class="table" style="margin-top:12px">
      <thead><tr><th>หนังสือ</th><th>ราคา</th><th>จำนวน</th><th>รวม</th></tr></thead>
      <tbody>
        <?php foreach($items as $it): $b=$it["b"]; ?>
          <tr>
            <td><strong><?php echo e($b["title"]); ?></strong><br><span class="small"><?php echo e($b["condition"]); ?></span></td>
            <td>฿<?php echo number_format((float)$b["price"],0); ?></td>
            <td><?php echo (int)$it["qty"]; ?></td>
            <td>฿<?php echo number_format((float)$it["sub"],0); ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>

    <div class="card" style="padding:14px;margin-top:12px">
      <div class="small">ยอดรวม</div>
      <div style="font-size:26px;font-weight:900">฿<?php echo number_format((float)$total,0); ?></div>
      <div class="small" style="margin-top:6px;color:rgba(234,240,255,.7)">สถานะเริ่มต้น: pending</div>
    </div>

    <div class="card" style="margin-top:12px">
      <div class="badge"><span class="dot ok"></span> ข้อมูลจัดส่ง</div>
      <form method="post" class="form" style="margin-top:12px;max-width:720px">
        <label>ชื่อผู้รับ</label><input name="name" required>
        <label>เบอร์โทร</label><input name="phone" required>
        <label>ที่อยู่จัดส่ง</label><textarea name="address" required></textarea>
        <div style="display:flex;gap:10px;flex-wrap:wrap">
          <button class="btn" type="submit">ยืนยันสั่งซื้อ</button>
          <a class="btn ghost" href="cart.php">กลับตะกร้า</a>
        </div>
      </form>
    </div>
  </div>
</div>
<?php include __DIR__ . "/partials/footer.php"; ?>
