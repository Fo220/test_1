-- v25 migration (safe / idempotent) for used-books-web
-- Run in phpMyAdmin (SQL tab) against your database (e.g. used_books_db)

DELIMITER $$

DROP PROCEDURE IF EXISTS add_col_if_missing $$
CREATE PROCEDURE add_col_if_missing(
  IN tbl VARCHAR(64),
  IN col VARCHAR(64),
  IN ddl TEXT
)
BEGIN
  IF NOT EXISTS (
    SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = tbl AND COLUMN_NAME = col
  ) THEN
    SET @s = CONCAT('ALTER TABLE `', tbl, '` ADD COLUMN ', ddl);
    PREPARE stmt FROM @s;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;
  END IF;
END $$

DROP PROCEDURE IF EXISTS modify_orders_status $$
CREATE PROCEDURE modify_orders_status()
BEGIN
  -- widen enum; ignore if fails
  SET @s = "ALTER TABLE orders MODIFY COLUMN status ENUM('pending','paid','shipped','cancelled') NOT NULL DEFAULT 'pending'";
  PREPARE stmt FROM @s;
  EXECUTE stmt;
  DEALLOCATE PREPARE stmt;
END $$

DELIMITER ;

-- books: admin full fields + soft delete
CALL add_col_if_missing('books','author',        "`author` VARCHAR(160) NULL");
CALL add_col_if_missing('books','publisher',     "`publisher` VARCHAR(160) NULL");
CALL add_col_if_missing('books','series',        "`series` VARCHAR(200) NULL");
CALL add_col_if_missing('books','file_type',     "`file_type` VARCHAR(20) NULL");
CALL add_col_if_missing('books','list_price',    "`list_price` DECIMAL(10,2) NULL");
CALL add_col_if_missing('books','tags',          "`tags` VARCHAR(255) NULL");
CALL add_col_if_missing('books','description',   "`description` TEXT NULL");
CALL add_col_if_missing('books','category_id',   "`category_id` INT NULL");
CALL add_col_if_missing('books','is_deleted',    "`is_deleted` TINYINT(1) NOT NULL DEFAULT 0");

-- orders: shipping fields
CALL add_col_if_missing('orders','shipping_name',    "`shipping_name` VARCHAR(120) NOT NULL DEFAULT ''");
CALL add_col_if_missing('orders','shipping_phone',   "`shipping_phone` VARCHAR(30) NOT NULL DEFAULT ''");
CALL add_col_if_missing('orders','shipping_address', "`shipping_address` TEXT NOT NULL");

-- order_items: snapshot fields
CALL add_col_if_missing('order_items','title_snapshot', "`title_snapshot` VARCHAR(255) NOT NULL DEFAULT ''");
CALL add_col_if_missing('order_items','price_snapshot', "`price_snapshot` DECIMAL(10,2) NOT NULL DEFAULT 0");

-- widen enum if orders exists
SET @t_exists := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='orders');
SET @do := IF(@t_exists>0, '1','0');
-- can't IF easily outside procedure; just attempt:
CALL modify_orders_status();

-- cleanup
DROP PROCEDURE IF EXISTS add_col_if_missing;
DROP PROCEDURE IF EXISTS modify_orders_status;
