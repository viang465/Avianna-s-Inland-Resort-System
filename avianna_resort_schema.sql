-- ============================================================
--  Avianna's Inland Resort — Full Database Schema
--  Run this in phpMyAdmin or MySQL CLI before deploying.
--  mysql -u root -p < avianna_resort_schema.sql
-- ============================================================

CREATE DATABASE IF NOT EXISTS avianna_resort
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE avianna_resort;

-- ─────────────────────────────────────────────
--  USERS  (admin accounts)
-- ─────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS users (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    username   VARCHAR(100) NOT NULL UNIQUE,
    password   VARCHAR(255) NOT NULL,   -- bcrypt hash
    role       ENUM('admin','staff') NOT NULL DEFAULT 'admin',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Default admin: username = admin | password = admin123
-- Hash generated with password_hash('admin123', PASSWORD_DEFAULT)
INSERT INTO users (username, password, role) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin')
ON DUPLICATE KEY UPDATE id = id;
-- NOTE: The hash above is a placeholder. Run admin/hash.php on your server
-- then UPDATE users SET password='<new_hash>' WHERE username='admin';

-- ─────────────────────────────────────────────
--  BOOKINGS
-- ─────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS bookings (
    id             INT AUTO_INCREMENT PRIMARY KEY,
    name           VARCHAR(150)   NOT NULL,
    email          VARCHAR(200)   NOT NULL,
    contact        VARCHAR(50)    DEFAULT NULL,
    address        VARCHAR(300)   DEFAULT NULL,
    room_type      VARCHAR(100)   DEFAULT 'None',
    cottage_type   VARCHAR(100)   DEFAULT 'None',
    pax            VARCHAR(20)    DEFAULT NULL,
    checkin        DATE           NOT NULL,
    checkout       DATE           NOT NULL,
    payment_method VARCHAR(80)    DEFAULT NULL,
    total_price    DECIMAL(10,2)  DEFAULT 0.00,
    status         ENUM('Pending','Approved','Booked','Cancelled') DEFAULT 'Pending',
    created_at     DATETIME       DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_status   (status),
    INDEX idx_checkin  (checkin),
    INDEX idx_email    (email)
) ENGINE=InnoDB;

-- ─────────────────────────────────────────────
--  DELETED / CANCELLED BOOKINGS (archive)
-- ─────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS deleted_bookings (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    name          VARCHAR(150)  DEFAULT NULL,
    email         VARCHAR(200)  DEFAULT NULL,
    address       VARCHAR(300)  DEFAULT NULL,
    room_type     VARCHAR(100)  DEFAULT NULL,
    checkin_date  DATE          DEFAULT NULL,
    checkout_date DATE          DEFAULT NULL,
    deletion_date DATETIME      DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_deletion (deletion_date)
) ENGINE=InnoDB;

-- ─────────────────────────────────────────────
--  ANNOUNCEMENTS
-- ─────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS announcements (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    title      VARCHAR(255) NOT NULL,
    message    TEXT         NOT NULL,
    created_at DATETIME     DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ─────────────────────────────────────────────
--  REVIEWS
-- ─────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS reviews (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    name            VARCHAR(150)  NOT NULL,
    rating          TINYINT       NOT NULL DEFAULT 5 CHECK (rating BETWEEN 1 AND 5),
    review_text     TEXT          NOT NULL,
    photo_path      VARCHAR(255)  DEFAULT NULL,
    submission_date DATETIME      DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_date (submission_date)
) ENGINE=InnoDB;
