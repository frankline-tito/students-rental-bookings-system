-- ================================================================
-- CAMPUS NEST — Student Rental Booking System
-- Database Schema
-- Import this file in phpMyAdmin, or run: mysql -u root -p < schema.sql
-- ================================================================

CREATE DATABASE IF NOT EXISTS rental_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE rental_system;

-- ----------------------------------------------------------------
-- USERS (admin, landlord, student)
-- ----------------------------------------------------------------
CREATE TABLE IF NOT EXISTS users (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    role            ENUM('admin','landlord','student') NOT NULL,
    full_name       VARCHAR(120) NOT NULL,
    email           VARCHAR(150) NOT NULL UNIQUE,
    phone           VARCHAR(20)  NOT NULL,
    password        VARCHAR(255) NOT NULL,
    status          ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
    profile_photo   VARCHAR(255) DEFAULT NULL,
    id_number       VARCHAR(30)  DEFAULT NULL,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ----------------------------------------------------------------
-- LANDLORD PAYMENT / VERIFICATION PROFILE
-- ----------------------------------------------------------------
CREATE TABLE IF NOT EXISTS landlord_profiles (
    id                    INT AUTO_INCREMENT PRIMARY KEY,
    user_id               INT NOT NULL UNIQUE,
    mpesa_number          VARCHAR(20)  DEFAULT NULL,
    paybill_number        VARCHAR(20)  DEFAULT NULL,
    paybill_account       VARCHAR(50)  DEFAULT NULL,
    bank_name             VARCHAR(100) DEFAULT NULL,
    bank_account_number   VARCHAR(50)  DEFAULT NULL,
    account_name          VARCHAR(120) DEFAULT NULL,
    posting_fee_status    ENUM('unpaid','pending','paid') NOT NULL DEFAULT 'unpaid',
    posting_fee_method    VARCHAR(30) DEFAULT NULL,
    posting_fee_ref       VARCHAR(60) DEFAULT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ----------------------------------------------------------------
-- HOUSES / ROOMS
-- ----------------------------------------------------------------
CREATE TABLE IF NOT EXISTS houses (
    id                 INT AUTO_INCREMENT PRIMARY KEY,
    landlord_id        INT NOT NULL,
    title              VARCHAR(150) NOT NULL,
    room_type          ENUM('bedsitter','single','one_bedroom','two_bedroom','hostel') NOT NULL,
    price              DECIMAL(10,2) NOT NULL,
    location           VARCHAR(150) NOT NULL,
    nearest_campus     VARCHAR(150) DEFAULT NULL,
    distance_notes     VARCHAR(150) DEFAULT NULL,
    description        TEXT,
    amenities          VARCHAR(255) DEFAULT NULL,
    map_link           VARCHAR(255) DEFAULT NULL,
    status             ENUM('available','booked') NOT NULL DEFAULT 'available',
    approval_status    ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
    created_at         TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (landlord_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ----------------------------------------------------------------
-- HOUSE IMAGES (multiple photos per listing)
-- ----------------------------------------------------------------
CREATE TABLE IF NOT EXISTS house_images (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    house_id    INT NOT NULL,
    image_path  VARCHAR(255) NOT NULL,
    FOREIGN KEY (house_id) REFERENCES houses(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ----------------------------------------------------------------
-- BOOKINGS
-- ----------------------------------------------------------------
CREATE TABLE IF NOT EXISTS bookings (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    student_id      INT NOT NULL,
    house_id        INT NOT NULL,
    status          ENUM('pending','confirmed','rejected','cancelled') NOT NULL DEFAULT 'pending',
    message         VARCHAR(255) DEFAULT NULL,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (house_id) REFERENCES houses(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ----------------------------------------------------------------
-- WISHLIST / SAVED HOUSES
-- ----------------------------------------------------------------
CREATE TABLE IF NOT EXISTS wishlist (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    student_id  INT NOT NULL,
    house_id    INT NOT NULL,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_wish (student_id, house_id),
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (house_id) REFERENCES houses(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ----------------------------------------------------------------
-- REVIEWS
-- ----------------------------------------------------------------
CREATE TABLE IF NOT EXISTS reviews (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    house_id    INT NOT NULL,
    student_id  INT NOT NULL,
    rating      TINYINT NOT NULL,
    comment     VARCHAR(500) DEFAULT NULL,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (house_id) REFERENCES houses(id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ----------------------------------------------------------------
-- PASSWORD RESET CODES
-- ----------------------------------------------------------------
CREATE TABLE IF NOT EXISTS password_resets (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    user_id     INT NOT NULL,
    code        VARCHAR(6) NOT NULL,
    expires_at  DATETIME NOT NULL,
    used        TINYINT(1) NOT NULL DEFAULT 0,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;
