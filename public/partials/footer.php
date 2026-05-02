<?php
// public/partials/footer.php
?>
<footer class="site-footer">
  <div class="container">
    <div class="footer-top">
      <div class="footer-brand">
        <span class="footer-logo" aria-hidden="true"></span>
        <div>
          <strong>UsedBooks Market</strong><br/>
          <span class="small" style="color:rgba(255,255,255,.70)">ตลาดซื้อ-ขายหนังสือมือสอง</span>
        </div>
      </div>
    </div>

    <div class="footer-grid">
      <div class="footer-col">
        <h4>About</h4>
        <p>
          เว็บซื้อ-ขายหนังสือมือสอง ดีไซน์ทันสมัย พร้อมระบบสมาชิก, หลังบ้านแอดมิน,
          ตะกร้า, ออเดอร์ และชำระเงินแนบสลิป
        </p>
      </div>

      <div class="footer-col">
        <h4>Navigation</h4>
        <div class="footer-links">
          <a href="shop.php">Our Products</a>
          <a href="shop.php">Promotions</a>
          <a href="shop.php?sort=new">สินค้ามาใหม่</a>
          <a href="orders.php">ออเดอร์ของฉัน</a>
        </div>
      </div>

      <div class="footer-col">
        <h4>Follow us</h4>
        <div class="footer-links">
          <a href="javascript:void(0)">Facebook</a>
          <a href="javascript:void(0)">X</a>
          <a href="javascript:void(0)">Line</a>
          <a href="javascript:void(0)">Instagram</a>
          <a href="javascript:void(0)">Tiktok</a>
        </div>
      </div>

      <div class="footer-col">
        <h4>Contact Us</h4>
        <p>
          ที่อยู่: ใส่ที่อยู่ร้านของคุณตรงนี้<br/>
          โทร: 0xx-xxx-xxxx<br/>
          อีเมล: yourshop@email.com
        </p>
      </div>
    </div>

    <div class="footer-bottom">
      <div>© <?php echo date("Y"); ?> UsedBooks Market. All rights reserved.</div>
      <div>Privacy · Terms · Cookies</div>
    </div>
  </div>
</footer>

<div class="cookie-bar">
  <div class="cookie-card">
    <div class="cookie-left">
      <div class="cookie-icon">🍪</div>
      <div class="cookie-text">
        <strong>เราใช้คุกกี้!</strong>
        <span>เราใช้คุกกี้เพื่อปรับปรุงประสบการณ์ของคุณ คุณสามารถเลือกตั้งค่าได้</span>
      </div>
    </div>
    <div class="cookie-actions">
      <button class="btn ghost" type="button" data-cookie-settings>ตั้งค่าคุกกี้</button>
      <button class="btn blue" type="button" data-cookie-accept>ยอมรับทั้งหมด</button>
    </div>
  </div>
</div>

<script src="assets/js/cookie.js"></script>
