CREATE DATABASE IF NOT EXISTS attendance_tracker CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE attendance_tracker;

CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    user_hash CHAR(64) NOT NULL UNIQUE,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS attendance_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    user_name VARCHAR(100) NOT NULL DEFAULT '',
    remarks TEXT NULL,
    marked_at DATETIME NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_attendance_user
        FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE CASCADE
) ENGINE=InnoDB;

ALTER TABLE attendance_logs
    ADD COLUMN IF NOT EXISTS user_name VARCHAR(100) NOT NULL DEFAULT '' AFTER user_id,
    ADD COLUMN IF NOT EXISTS remarks TEXT NULL AFTER user_name;

INSERT INTO users (name, user_hash)
VALUES
    ('Hasan', SHA2('user1-secret', 256)),
    ('Arnab', SHA2('user2-secret', 256)),
    ('Sabbayasachi', SHA2('user3-secret', 256)),
    ('Tahsin', SHA2('user4-secret', 256))
ON DUPLICATE KEY UPDATE name = VALUES(name);

