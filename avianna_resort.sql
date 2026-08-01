-- ============================================================
-- Avianna's Inland Resort - Complete Database Setup
-- Run this in phpMyAdmin or MySQL CLI: source avianna_resort.sql
-- ============================================================

CREATE DATABASE IF NOT EXISTS `avianna_resort`
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE `avianna_resort`;

-- ── bookings ─────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `bookings` (
    `id`             INT          NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `name`           VARCHAR(150) NOT NULL,
    `email`          VARCHAR(200) NOT NULL,
    `contact`        VARCHAR(30)  NOT NULL DEFAULT '',
    `address`        VARCHAR(255) NOT NULL DEFAULT '',
    `room_type`      VARCHAR(100)          DEFAULT 'None',
    `cottage_type`   VARCHAR(100)          DEFAULT 'None',
    `pax`            VARCHAR(20)           DEFAULT '',
    `checkin`        DATE         NOT NULL,
    `checkout`       DATE         NOT NULL,
    `payment_method` VARCHAR(50)           DEFAULT '',
    `total_price`    DECIMAL(10,2)         DEFAULT 0.00,
    `status`         VARCHAR(30)           DEFAULT 'Pending',
    `created_at`     DATETIME              DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── deleted_bookings (archive) ────────────────────────────────
CREATE TABLE IF NOT EXISTS `deleted_bookings` (
    `id`            INT          NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `name`          VARCHAR(150)          DEFAULT '',
    `email`         VARCHAR(200)          DEFAULT '',
    `address`       VARCHAR(255)          DEFAULT '',
    `room_type`     VARCHAR(100)          DEFAULT '',
    `checkin_date`  DATE                  DEFAULT NULL,
    `checkout_date` DATE                  DEFAULT NULL,
    `deletion_date` DATETIME              DEFAULT CURRENT_TIMESTAMP,
    -- legacy alias kept for public cancel_booking.php compatibility
    `deleted_at`    DATETIME              DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── reviews ──────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `reviews` (
    `id`              INT          NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `name`            VARCHAR(150) NOT NULL,
    `rating`          TINYINT      NOT NULL DEFAULT 3,
    `review_text`     TEXT                  DEFAULT NULL,
    `photo_path`      VARCHAR(255)          DEFAULT '',
    `submission_date` DATETIME              DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── announcements ────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `announcements` (
    `id`         INT          NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `title`      VARCHAR(200) NOT NULL,
    `message`    TEXT         NOT NULL,
    `created_at` DATETIME     DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── admin users ──────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `users` (
    `id`       INT          NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(100) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `role`     VARCHAR(30)  NOT NULL DEFAULT 'admin'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Default admin: username=admin  password=admin123
-- (Change immediately after first login!)
INSERT IGNORE INTO `users` (`username`, `password`, `role`)
VALUES ('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- ── Sample announcement ──────────────────────────────────────
INSERT IGNORE INTO `announcements` (`id`, `title`, `message`)
VALUES (1,
    'Welcome to Avianna\'s Inland Resort!',
    'Book 2-3 days in advance. Operating hours: 8:00 AM – 10:00 PM daily.');
