<?php
require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../config/auth.php";
require_admin();

if($_SERVER["REQUEST_METHOD"]==="POST"){
  $id=(int)($_POST["id"] ?? 0);
  $status=$_POST["status"] ?? "pending";
  $allowed=["pending","paid","shipped","cancelled"];
  if($id>0 && in_array($status,$allowed,true)){
    $pdo->prepare("UPDATE orders SET status=? WHERE id=?")->execute([$status,$id]);
  }
  header("Location: orders.php"); exit;
}

$orders=$pdo->query("SELECT o.*, u.fullname, u.email FROM orders o JOIN users u ON o.user_id=u.id ORDER BY o.created_at DESC")->fetchAll();
$title="ออเดอร์ | Admin";
include __DIR__ . "/partials/admin_header.php";
?>
<div class="container section">
  <div class="card">
    <div class="badge"><span class="dot ok"></span> จัดการออเดอร์</div>
    <table class="table" style="margin-top:12px">
      <thead><tr><th>#</th><th>ลูกค้า</th><th>ยอดรวม</th><th>สถานะ</th><th>จัดการ</th><th>ดู</th></tr></thead>
      <tbody>
        <?php foreach($orders as $o): ?>
          <tr>
            <td>#<?php echo (int)$o["id"]; ?><br><span class="small"><?php echo e(date("d/m/Y H:i", strtotime($o["created_at"]))); ?></span></td>
            <td><strong><?php echo e($o["fullname"]); ?></strong><br><span class="small"><?php echo e($o["email"]); ?></span></td>
            <td>฿<?php echo number_format((float)$o["total"],0); ?></td>
            <td><?php echo e($o["status"]); ?></td>
            <td>
              <form method="post" style="display:flex;gap:10px;flex-wrap:wrap;align-items:center">
                <input type="hidden" name="id" value="<?php echo (int)$o["id"]; ?>">
                <select name="status" style="max-width:160px">
                  <?php foreach(["pending","paid","shipped","cancelled"] as $s): ?>
                    <option value="<?php echo e($s); ?>" <?php echo $o["status"]===$s?'selected':''; ?>><?php echo e($s); ?></option>
                  <?php endforeach; ?>
                </select>
                <button class="pill" type="submit">อัปเดต</button>
              </form>
            </td>
            <td><a class="pill" href="order_detail.php?id=<?php echo (int)$o["id"]; ?>">รายละเอียด</a></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php include __DIR__ . "/partials/admin_footer.php"; ?>
