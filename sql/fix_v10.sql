-- used-books-web v10 fix for existing DB (run once if you already have tables)
CREATE TABLE IF NOT EXISTS categories (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(80) NOT NULL UNIQUE
) ENGINE=InnoDB;

-- Add missing columns to users
ALTER TABLE users
  ADD COLUMN IF NOT EXISTS avatar_url VARCHAR(255) NULL,
  ADD COLUMN IF NOT EXISTS google_id VARCHAR(64) NULL UNIQUE,
  MODIFY COLUMN status ENUM('active','blocked') NOT NULL DEFAULT 'active';

-- Add missing columns to books
ALTER TABLE books
  ADD COLUMN IF NOT EXISTS author VARCHAR(160) NULL,
  ADD COLUMN IF NOT EXISTS category_id INT NULL,
  ADD COLUMN IF NOT EXISTS `condition` ENUM('เหมือนใหม่','ดีมาก','ดี','พอใช้') NOT NULL DEFAULT 'ดี',
  ADD COLUMN IF NOT EXISTS description TEXT NULL,
  ADD COLUMN IF NOT EXISTS is_published TINYINT(1) NOT NULL DEFAULT 1,
  ADD COLUMN IF NOT EXISTS created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP;

-- FK category
ALTER TABLE books
  ADD CONSTRAINT IF NOT EXISTS fk_books_category
  FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL;

-- Seed categories
INSERT IGNORE INTO categories(name) VALUES
('นิยาย'),('การ์ตูน'),('การศึกษา'),('ธุรกิจ'),('ไลฟ์สไตล์');


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


-- v17: add missing shipping columns to orders
ALTER TABLE orders
  ADD COLUMN shipping_name VARCHAR(120) NOT NULL DEFAULT '' AFTER status,
  ADD COLUMN shipping_phone VARCHAR(30) NOT NULL DEFAULT '' AFTER shipping_name,
  ADD COLUMN shipping_address TEXT NOT NULL AFTER shipping_phone;

-- v17: ensure status enum supports shipped/cancelled
ALTER TABLE orders
  MODIFY COLUMN status ENUM('pending','paid','shipped','cancelled') NOT NULL DEFAULT 'pending';


-- v21: add snapshot columns to order_items
ALTER TABLE order_items
  ADD COLUMN title_snapshot VARCHAR(255) NOT NULL DEFAULT '' AFTER book_id,
  ADD COLUMN price_snapshot DECIMAL(10,2) NOT NULL DEFAULT 0 AFTER title_snapshot;
