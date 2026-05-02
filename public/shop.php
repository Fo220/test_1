<?php
$title = "ร้านหนังสือ";
require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../config/auth.php";
require_once __DIR__ . "/partials/header.php";

// Query params
$q = trim($_GET["q"] ?? "");
$scope = $_GET["scope"] ?? "all"; // all|title|author
$cat = $_GET["cat"] ?? "";        // category id
$cond = $_GET["cond"] ?? "";      // condition enum
$minp = $_GET["minp"] ?? "";
$maxp = $_GET["maxp"] ?? "";
$sort = $_GET["sort"] ?? "new";   // new|price_asc|price_desc|title

$page = max(1, (int)($_GET["page"] ?? 1));
$per = 12;
$off = ($page-1) * $per;

// Load categories for sidebar
$cats = $pdo->query("SELECT id,name FROM categories ORDER BY name")->fetchAll();

// Build WHERE
$where = ["b.is_published=1 AND IFNULL(b.is_deleted,0)=0"];
$params = [];

if($q !== ""){
  if($scope === "title"){
    $where[] = "b.title LIKE ?";
    $params[] = "%$q%";
  } elseif($scope === "author"){
    $where[] = "b.author LIKE ?";
    $params[] = "%$q%";
  } else {
    $where[] = "(b.title LIKE ? OR b.author LIKE ?)";
    $params[] = "%$q%";
    $params[] = "%$q%";
  }
}

if($cat !== "" && ctype_digit($cat)){
  $where[] = "b.category_id = ?";
  $params[] = (int)$cat;
}

$cond_allowed = ["เหมือนใหม่","ดีมาก","ดี","พอใช้"];
if($cond !== "" && in_array($cond, $cond_allowed, true)){
  $where[] = "b.`condition` = ?";
  $params[] = $cond;
}

if($minp !== "" && is_numeric($minp)){
  $where[] = "b.price >= ?";
  $params[] = (float)$minp;
}
if($maxp !== "" && is_numeric($maxp)){
  $where[] = "b.price <= ?";
  $params[] = (float)$maxp;
}

$wsql = $where ? ("WHERE " . implode(" AND ", $where)) : "";

$order = "b.id DESC";
if($sort === "price_asc") $order = "b.price ASC, b.id DESC";
if($sort === "price_desc") $order = "b.price DESC, b.id DESC";
if($sort === "title") $order = "b.title ASC";

$count = $pdo->prepare("SELECT COUNT(*) FROM books b $wsql");
$count->execute($params);
$total = (int)$count->fetchColumn();
$pages = max(1, (int)ceil($total / $per));

$sql = "SELECT b.*, c.name AS category_name
        FROM books b
        LEFT JOIN categories c ON c.id=b.category_id
        $wsql
        ORDER BY $order
        LIMIT $per OFFSET $off";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$books = $stmt->fetchAll();

function build_url($overrides=[]){
  $q = $_GET;
  foreach($overrides as $k=>$v){
    if($v === null) unset($q[$k]);
    else $q[$k] = $v;
  }
  $qs = http_build_query($q);
  return "shop.php" . ($qs ? ("?$qs") : "");
}

// Active chips
$chips = [];
if($q !== "") $chips[] = ["ค้นหา", $q, ["q"=>null,"page"=>1]];
if($cat !== "") {
  $cn = "";
  foreach($cats as $c){ if((string)$c["id"]===(string)$cat){ $cn=$c["name"]; break; } }
  $chips[] = ["หมวดหมู่", $cn ?: $cat, ["cat"=>null,"page"=>1]];
}
if($cond !== "") $chips[] = ["สภาพ", $cond, ["cond"=>null,"page"=>1]];
if($minp !== "") $chips[] = ["ราคาเริ่ม", $minp, ["minp"=>null,"page"=>1]];
if($maxp !== "") $chips[] = ["ราคาสูงสุด", $maxp, ["maxp"=>null,"page"=>1]];
?>
<div class="container bw-top">
  <form class="bw-searchbar" method="get" action="shop.php">
    <span style="font-size:18px;opacity:.75">🔎</span>
    <input name="q" value="<?php echo e($q); ?>" placeholder="ค้นหา" />
    <div class="sep"></div>
    <select name="scope">
      <option value="all" <?php echo $scope==="all"?"selected":""; ?>>ค้นหาทั้งหมด</option>
      <option value="title" <?php echo $scope==="title"?"selected":""; ?>>ชื่อหนังสือ</option>
      <option value="author" <?php echo $scope==="author"?"selected":""; ?>>นักเขียน</option>
    </select>
    <button class="btn blue" type="submit">ค้นหา</button>
  </form>
</div>

<div class="container section bw-layout">
  <aside class="bw-side">
    <h3>ค้นหาโดย</h3>

    <div class="facet">
      <h4>หมวดหมู่</h4>
      <div class="footer-links">
        <a class="chip" href="<?php echo e(build_url(["cat"=>null,"page"=>1])); ?>">ทั้งหมด</a>
      </div>
      <?php foreach($cats as $c): ?>
        <div class="row">
          <label style="display:flex;align-items:center;gap:10px">
            <input type="checkbox" <?php echo ((string)$cat===(string)$c["id"])?"checked":""; ?>
              onclick="location.href='<?php echo e(build_url(['cat'=> ((string)$cat===(string)$c['id'])?null:$c['id'], 'page'=>1])); ?>'">
            <?php echo e($c["name"]); ?>
          </label>
          <span class="mini">›</span>
        </div>
      <?php endforeach; ?>
    </div>

    <div class="facet">
      <h4>สภาพหนังสือ</h4>
      <?php foreach($cond_allowed as $cc): ?>
        <div class="row">
          <label style="display:flex;align-items:center;gap:10px">
            <input type="checkbox" <?php echo ($cond===$cc)?"checked":""; ?>
              onclick="location.href='<?php echo e(build_url(['cond'=> ($cond===$cc)?null:$cc,'page'=>1])); ?>'">
            <?php echo e($cc); ?>
          </label>
          <span class="mini">›</span>
        </div>
      <?php endforeach; ?>
    </div>

    <div class="facet">
      <h4>ช่วงราคา</h4>
      <form class="form" method="get" action="shop.php" style="margin:0">
        <input type="hidden" name="q" value="<?php echo e($q); ?>">
        <input type="hidden" name="scope" value="<?php echo e($scope); ?>">
        <input type="hidden" name="cat" value="<?php echo e($cat); ?>">
        <input type="hidden" name="cond" value="<?php echo e($cond); ?>">
        <div class="split">
          <div>
            <label class="mini">ต่ำสุด</label>
            <input name="minp" value="<?php echo e($minp); ?>" placeholder="0">
          </div>
          <div>
            <label class="mini">สูงสุด</label>
            <input name="maxp" value="<?php echo e($maxp); ?>" placeholder="9999">
          </div>
        </div>
        <button class="btn ghost" type="submit">ใช้ตัวกรองราคา</button>
      </form>
    </div>

    <div class="facet">
      <a class="btn ghost" href="shop.php">ล้างตัวกรองทั้งหมด</a>
    </div>
  </aside>

  <main class="bw-main">
    <div class="bw-head">
      <div>
        <h2>คีย์เวิร์ดยอดนิยม</h2>
        <div class="chips" style="margin-top:10px">
          <a class="chip" href="<?php echo e(build_url(['q'=>'มังงะ','page'=>1])); ?>"><strong>มังงะ</strong></a>
          <a class="chip" href="<?php echo e(build_url(['q'=>'นิยาย','page'=>1])); ?>"><strong>นิยาย</strong></a>
          <a class="chip" href="<?php echo e(build_url(['cond'=>'เหมือนใหม่','page'=>1])); ?>"><strong>เหมือนใหม่</strong></a>
          <a class="chip" href="<?php echo e(build_url(['q'=>'อ่านฟรี','page'=>1])); ?>"><strong>อ่านฟรี</strong></a>
        </div>
      </div>

      <div class="bw-sort">
        <span class="mini">เรียงตาม</span>
        <select onchange="location.href=this.value">
          <option value="<?php echo e(build_url(['sort'=>'new','page'=>1])); ?>" <?php echo $sort==='new'?'selected':''; ?>>มาใหม่</option>
          <option value="<?php echo e(build_url(['sort'=>'price_asc','page'=>1])); ?>" <?php echo $sort==='price_asc'?'selected':''; ?>>ราคาต่ำ-สูง</option>
          <option value="<?php echo e(build_url(['sort'=>'price_desc','page'=>1])); ?>" <?php echo $sort==='price_desc'?'selected':''; ?>>ราคาสูง-ต่ำ</option>
          <option value="<?php echo e(build_url(['sort'=>'title','page'=>1])); ?>" <?php echo $sort==='title'?'selected':''; ?>>ชื่อ A-Z</option>
        </select>
      </div>
    </div>

    <?php if($chips): ?>
      <div class="chips" style="margin:6px 0 12px">
        <?php foreach($chips as $ch): ?>
          <a class="chip" href="<?php echo e(build_url($ch[2])); ?>"><?php echo e($ch[0]); ?>: <strong><?php echo e($ch[1]); ?></strong> ✕</a>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <p class="muted" style="margin:0 0 12px">พบ <?php echo $total; ?> รายการ</p>

    <div class="grid">
      <?php foreach($books as $b): ?>
        <div class="book">
          <a href="book.php?id=<?php echo (int)$b['id']; ?>" style="text-decoration:none">
            <div class="book-thumb">
              <?php if(!empty($b["cover_path"])): ?>
                <img src="<?php echo e($b["cover_path"]); ?>" alt="">
              <?php else: ?>
                <div class="placeholder">NO COVER</div>
              <?php endif; ?>
            </div>
            <h3><?php echo e($b["title"]); ?></h3>
          </a>
          <div class="meta">
            <?php if(!empty($b["category_name"])): ?><span class="tag"><?php echo e($b["category_name"]); ?></span><?php endif; ?>
            <?php if(!empty($b["author"])): ?><span class="tag"><?php echo e($b["author"]); ?></span><?php endif; ?>
            <span class="tag"><?php echo e($b["condition"]); ?></span>
          </div>
          <div class="price">฿<?php echo number_format((float)$b["price"],2); ?></div>
          <div style="display:flex;gap:10px;flex-wrap:wrap">
            <a class="btn ghost" href="book.php?id=<?php echo (int)$b['id']; ?>">ดูรายละเอียด</a>
            <a class="btn blue" href="add_to_cart.php?id=<?php echo (int)$b['id']; ?>">ใส่ตะกร้า</a>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

    <?php if($pages > 1): ?>
      <div class="chips" style="justify-content:center;margin-top:16px">
        <?php for($i=1;$i<=$pages;$i++): ?>
          <a class="chip" href="<?php echo e(build_url(['page'=>$i])); ?>" style="<?php echo ($i===$page)?'background:rgba(11,116,222,.08);border-color:rgba(11,116,222,.22);':''; ?>">
            <?php echo $i; ?>
          </a>
        <?php endfor; ?>
      </div>
    <?php endif; ?>
  </main>
</div>

<?php require_once __DIR__ . "/partials/footer.php"; ?>
</body></html>
