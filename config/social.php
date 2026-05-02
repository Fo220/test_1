<?php
// config/social.php
// v28: Social login providers
// ถ้ายังไม่มี client id/secret ให้เปิดโหมด mock เพื่อทดสอบเข้าได้หมด (dev)
return [
  "mock_enabled" => true, // เปลี่ยนเป็น false เมื่อตั้งค่า OAuth จริง
  "providers" => [
    "google" => [
      "enabled" => true,
      "client_id" => getenv("GOOGLE_CLIENT_ID") ?: "",
      "client_secret" => getenv("GOOGLE_CLIENT_SECRET") ?: "",
      "redirect_uri" => getenv("GOOGLE_REDIRECT_URI") ?: "",
    ],
    "facebook" => [
      "enabled" => true,
      "client_id" => getenv("FB_CLIENT_ID") ?: "",
      "client_secret" => getenv("FB_CLIENT_SECRET") ?: "",
      "redirect_uri" => getenv("FB_REDIRECT_URI") ?: "",
    ],
    "line" => [
      "enabled" => true,
      "client_id" => getenv("LINE_CLIENT_ID") ?: "",
      "client_secret" => getenv("LINE_CLIENT_SECRET") ?: "",
      "redirect_uri" => getenv("LINE_REDIRECT_URI") ?: "",
    ],
    "apple" => [
      "enabled" => true,
      "client_id" => getenv("APPLE_CLIENT_ID") ?: "",
      "client_secret" => getenv("APPLE_CLIENT_SECRET") ?: "",
      "redirect_uri" => getenv("APPLE_REDIRECT_URI") ?: "",
    ],
  ],
];
