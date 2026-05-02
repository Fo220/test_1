<?php
// ตั้งค่าจาก Google Cloud Console (OAuth 2.0 Client ID)
// IMPORTANT: เปลี่ยนค่า 3 บรรทัดนี้ให้เป็นของคุณ
define("GOOGLE_CLIENT_ID", "PUT_YOUR_CLIENT_ID_HERE");
define("GOOGLE_CLIENT_SECRET", "PUT_YOUR_CLIENT_SECRET_HERE");
// ให้ตรงกับ Authorized redirect URI ใน Console (เช่น http://localhost/used-books-web/public/google_callback.php)
define("GOOGLE_REDIRECT_URI", "http://localhost/used-books-web/public/google_callback.php");
