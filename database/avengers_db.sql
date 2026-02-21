-- Avengers Gym Management System - Complete Database Schema
-- Created: 17-Feb-2026
-- Optimized for: XAMPP / MySQL / MariaDB

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+05:00";

-- =====================================================
-- DATABASE SETUP
-- =====================================================
CREATE DATABASE IF NOT EXISTS `avengers` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `avengers`;

-- =====================================================
-- 1. USERS TABLE - Admin Authentication
-- =====================================================
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(50) UNIQUE NOT NULL,
  `email` VARCHAR(100) UNIQUE NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `full_name` VARCHAR(100) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_username` (`username`),
  INDEX `idx_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 2. MEMBERSHIP PLANS TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS `membership_plans` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL UNIQUE,
  `duration` INT NOT NULL COMMENT 'Duration in days',
  `price` DECIMAL(10, 2) NOT NULL,
  `description` TEXT,
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_plan_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 3. FEE TYPES TABLE (Monthly, Admission, Cardio, etc.)
-- =====================================================
CREATE TABLE IF NOT EXISTS `fee_types` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `default_amount` DECIMAL(10, 2) DEFAULT 0.00,
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_fee_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 4. TRAINERS TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS `trainers` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `phone` VARCHAR(20) NOT NULL,
  `specialization` VARCHAR(100),
  `commission_rate` DECIMAL(5, 2) DEFAULT 80.00 COMMENT 'Percentage share for trainer',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_trainer_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 5. MEMBERS TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS `members` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `full_name` VARCHAR(100) NOT NULL,
  `phone` VARCHAR(20) NOT NULL UNIQUE,
  `gender` ENUM('Male', 'Female', 'Other') DEFAULT 'Male',
  `profile_picture` VARCHAR(255),
  `plan_id` INT NOT NULL,
  `trainer_id` INT DEFAULT NULL,
  `start_date` DATE NOT NULL,
  `end_date` DATE NOT NULL,
  `status` ENUM('active', 'expired', 'suspended') DEFAULT 'active',
  `notes` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`plan_id`) REFERENCES `membership_plans`(`id`) ON DELETE RESTRICT,
  FOREIGN KEY (`trainer_id`) REFERENCES `trainers`(`id`) ON DELETE SET NULL,
  INDEX `idx_member_status` (`status`),
  INDEX `idx_member_phone` (`phone`),
  INDEX `idx_member_end_date` (`end_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 6. PAYMENTS TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS `payments` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `member_id` INT NOT NULL,
  `fee_type_id` INT NOT NULL DEFAULT 1,
  `amount` DECIMAL(10, 2) NOT NULL,
  `payment_method` ENUM('cash', 'card', 'online', 'easypaisa', 'jazzcash', 'nayapay', 'sadapay', 'bank_transfer') DEFAULT 'cash',
  `payment_date` DATE NOT NULL,
  `description` TEXT,
  `receipt_number` VARCHAR(50),
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`member_id`) REFERENCES `members`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`fee_type_id`) REFERENCES `fee_types`(`id`) ON DELETE RESTRICT,
  INDEX `idx_payment_member` (`member_id`),
  INDEX `idx_payment_date` (`payment_date`),
  INDEX `idx_payment_receipt` (`receipt_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 7. COMMISSIONS TABLE - Trainer Earnings
-- =====================================================
CREATE TABLE IF NOT EXISTS `commissions` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `trainer_id` INT NOT NULL,
  `member_id` INT NOT NULL,
  `payment_id` INT NOT NULL,
  `amount` DECIMAL(10, 2) NOT NULL,
  `commission_rate` DECIMAL(5, 2) NOT NULL COMMENT 'Rate at time of payment',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`trainer_id`) REFERENCES `trainers`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`member_id`) REFERENCES `members`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`payment_id`) REFERENCES `payments`(`id`) ON DELETE CASCADE,
  INDEX `idx_commission_lookup` (`trainer_id`, `created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 8. ATTENDANCE TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS `attendance` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `member_id` INT NOT NULL,
  `check_in_time` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `check_out_time` DATETIME NULL,
  `status` ENUM('present', 'absent') DEFAULT 'present',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`member_id`) REFERENCES `members`(`id`) ON DELETE CASCADE,
  INDEX `idx_attendance_lookup` (`member_id`, `check_in_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 9. EXPENSES TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS `expenses` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(100) NOT NULL,
  `amount` DECIMAL(10, 2) NOT NULL,
  `expense_date` DATE NOT NULL,
  `category` VARCHAR(50) NOT NULL,
  `description` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_expense_filter` (`expense_date`, `category`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- SEED DATA - Default Admin
-- =====================================================
INSERT INTO `users` (`username`, `email`, `password`, `full_name`) 
VALUES ('admin', 'admin@gym.local', '$2y$10$YourHashedPasswordHere', 'Admin User')
ON DUPLICATE KEY UPDATE id=id;

-- =====================================================
-- SEED DATA - Default Fee Types
-- =====================================================
INSERT INTO `fee_types` (`name`, `default_amount`) VALUES
('Monthly Fee', 2500.00),
('Admission Fee', 1000.00),
('Trainer Fee', 3000.00),
('Personal Training', 5000.00),
('Cardio Fee', 500.00)
ON DUPLICATE KEY UPDATE id=id;

-- =====================================================
-- SEED DATA - Default Plans
-- =====================================================
INSERT INTO `membership_plans` (`name`, `duration`, `price`) VALUES
('1 Month', 30, 2500.00),
('3 Months', 90, 6500.00),
('6 Months', 180, 11000.00),
('1 Year', 365, 18000.00)
ON DUPLICATE KEY UPDATE id=id;

COMMIT;
