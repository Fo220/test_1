-- Run this if you already imported schema.sql before v3
ALTER TABLE books ADD COLUMN preview_path VARCHAR(255) NULL AFTER cover_path;
