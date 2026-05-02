-- Run this if you already imported schema.sql before v7
ALTER TABLE users ADD COLUMN avatar_url VARCHAR(255) NULL AFTER password_hash;
ALTER TABLE users ADD COLUMN google_id VARCHAR(64) NULL UNIQUE AFTER avatar_url;
