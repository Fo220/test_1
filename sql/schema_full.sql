-- used-books-web v10 (Complete + Compatible)
-- Fresh install: (1) Create DB used_books_db (or let app auto-create) (2) Import this file
-- Charset: utf8mb4

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
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS categories (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(80) NOT NULL UNIQUE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS books (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(200) NOT NULL,
  author VARCHAR(160) NULL,
  category_id INT NULL,
  price DECIMAL(10,2) NOT NULL DEFAULT 0,
  `condition` ENUM('เหมือนใหม่','ดีมาก','ดี','พอใช้') NOT NULL DEFAULT 'ดี',
  stock INT NOT NULL DEFAULT 1,
  description TEXT NULL,
  cover_path VARCHAR(255) NULL,
  preview_path VARCHAR(255) NULL,
  is_published TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS orders (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  total DECIMAL(10,2) NOT NULL DEFAULT 0,
  status ENUM('pending','paid','shipped','cancelled') NOT NULL DEFAULT 'pending',
  shipping_name VARCHAR(120) NOT NULL DEFAULT '',
  shipping_phone VARCHAR(30) NOT NULL DEFAULT '',
  shipping_address TEXT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS order_items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  order_id INT NOT NULL,
  book_id INT NOT NULL,
  title_snapshot VARCHAR(255) NOT NULL DEFAULT '',
  price_snapshot DECIMAL(10,2) NOT NULL DEFAULT 0,
  qty INT NOT NULL DEFAULT 1,
  FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
  FOREIGN KEY (book_id) REFERENCES books(id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS payments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  order_id INT NOT NULL,
  user_id INT NOT NULL,
  amount DECIMAL(10,2) NOT NULL DEFAULT 0,
  bank_name VARCHAR(100) NULL,
  transfer_datetime DATETIME NOT NULL,
  slip_path VARCHAR(255) NOT NULL,
  status ENUM('submitted','approved','rejected') NOT NULL DEFAULT 'submitted',
  admin_note VARCHAR(255) NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

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
) ENGINE=InnoDB;

-- Seed categories
INSERT IGNORE INTO categories(name) VALUES
('นิยาย'),('การ์ตูน'),('การศึกษา'),('ธุรกิจ'),('ไลฟ์สไตล์');

-- Seed admin (email: admin@demo.com, pass: admin1234)
-- password_hash = password_hash('admin1234', PASSWORD_DEFAULT)
INSERT IGNORE INTO users(fullname,email,password_hash,role,status)
VALUES ('Admin','admin@demo.com','$2y$10$V0s1b5bUj0U4Wb1wz7m3IudgM9xj7oQm8oH4QxgKQ3Q3G2cQX5v7K','admin','active');


CREATE TABLE IF NOT EXISTS user_addresses (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL UNIQUE,
  line1 VARCHAR(200) NULL,
  line2 VARCHAR(200) NULL,
  province VARCHAR(120) NULL,
  zipcode VARCHAR(10) NULL,
  phone VARCHAR(30) NULL,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;


-- v18: optional fields for pro product page
ALTER TABLE books
  ADD COLUMN author VARCHAR(160) NULL,
  ADD COLUMN publisher VARCHAR(160) NULL,
  ADD COLUMN series VARCHAR(200) NULL,
  ADD COLUMN file_type VARCHAR(20) NULL,
  ADD COLUMN list_price DECIMAL(10,2) NULL,
  ADD COLUMN tags VARCHAR(255) NULL,
  ADD COLUMN description TEXT NULL,
  ADD COLUMN category_id INT NULL;


ALTER TABLE books ADD COLUMN is_deleted TINYINT(1) NOT NULL DEFAULT 0;
