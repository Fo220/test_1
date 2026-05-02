CREATE DATABASE IF NOT EXISTS used_books_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE used_books_db;

CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  fullname VARCHAR(120) NOT NULL,
  email VARCHAR(120) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('user','admin') NOT NULL DEFAULT 'user',
  status ENUM('active','blocked') NOT NULL DEFAULT 'active',
  avatar_url VARCHAR(255) NULL,
  google_id VARCHAR(64) NULL UNIQUE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS categories (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(80) NOT NULL UNIQUE
);

CREATE TABLE IF NOT EXISTS books (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(200) NOT NULL,
  author VARCHAR(160) NULL,
  category_id INT NULL,
  price DECIMAL(10,2) NOT NULL,
  `condition` ENUM('เหมือนใหม่','ดีมาก','ดี','พอใช้') NOT NULL DEFAULT 'ดี',
  stock INT NOT NULL DEFAULT 1,
  description TEXT NULL,
  cover_path VARCHAR(255) NULL,
  preview_path VARCHAR(255) NULL,
  is_published TINYINT(1) NOT NULL DEFAULT 1,
  avatar_url VARCHAR(255) NULL,
  google_id VARCHAR(64) NULL UNIQUE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS orders (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  total DECIMAL(10,2) NOT NULL,
  status ENUM('pending','paid','shipped','cancelled') NOT NULL DEFAULT 'pending',
  shipping_name VARCHAR(120) NOT NULL,
  shipping_phone VARCHAR(30) NOT NULL,
  shipping_address TEXT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS order_items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  order_id INT NOT NULL,
  book_id INT NOT NULL,
  title_snapshot VARCHAR(200) NOT NULL,
  price_snapshot DECIMAL(10,2) NOT NULL,
  qty INT NOT NULL,
  FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
  FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE RESTRICT
);
CREATE TABLE IF NOT EXISTS payments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  order_id INT NOT NULL,
  user_id INT NOT NULL,
  amount DECIMAL(10,2) NOT NULL,
  bank_name VARCHAR(100) NULL,
  transfer_datetime DATETIME NOT NULL,
  slip_path VARCHAR(255) NOT NULL,
  status ENUM('submitted','approved','rejected') NOT NULL DEFAULT 'submitted',
  admin_note VARCHAR(255) NULL,
  avatar_url VARCHAR(255) NULL,
  google_id VARCHAR(64) NULL UNIQUE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
CREATE TABLE IF NOT EXISTS auth_tokens (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  selector CHAR(24) NOT NULL,
  validator_hash CHAR(64) NOT NULL,
  expires_at DATETIME NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uniq_selector (selector),
  INDEX idx_user (user_id),
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);


INSERT IGNORE INTO categories(name) VALUES
('นิยาย'),('การ์ตูน'),('พัฒนาตนเอง'),('เรียน/สอบ'),('ธุรกิจ'),('คอมพิวเตอร์');

INSERT INTO books (title, author, category_id, price, `condition`, stock, description, cover_path, is_published) VALUES
('Atomic Habits (มือสอง)', 'James Clear', (SELECT id FROM categories WHERE name='พัฒนาตนเอง' LIMIT 1), 189.00, 'ดีมาก', 3, 'สภาพดี มีรอยใช้งานเล็กน้อย', NULL, 1),
('One Piece เล่ม 1 (มือสอง)', 'Eiichiro Oda', (SELECT id FROM categories WHERE name='การ์ตูน' LIMIT 1), 39.00, 'ดี', 5, 'ปกมีรอยตามการใช้งาน ปกติอ่านได้', NULL, 1),
('Python Crash Course (มือสอง)', 'Eric Matthes', (SELECT id FROM categories WHERE name='คอมพิวเตอร์' LIMIT 1), 399.00, 'เหมือนใหม่', 2, 'แทบไม่ผ่านการใช้งาน', NULL, 1);

-- Default admin: admin@books.local / Admin@1234
INSERT IGNORE INTO users(fullname,email,password_hash,role,status) VALUES
('Book Admin','admin@books.local','$2y$10$w9JzqI7jUo8q0GdYwAqf5u0D2iJ4mF3xQ8h0v5m7k0V0QmKq7n/8a','admin','active');
