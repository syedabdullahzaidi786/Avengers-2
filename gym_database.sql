-- Gym Management System Database
-- Created: February 2026

CREATE DATABASE IF NOT EXISTS `avengers`;
USE `avengers`;

-- =====================================================
-- USERS TABLE - Admin authentication
-- =====================================================
CREATE TABLE `users` (
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
-- MEMBERSHIP PLANS TABLE
-- =====================================================
CREATE TABLE `membership_plans` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL UNIQUE,
  `duration` INT NOT NULL COMMENT 'Duration in days',
  `price` DECIMAL(10, 2) NOT NULL,
  `description` TEXT,
  `is_active` BOOLEAN DEFAULT TRUE,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_is_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TRAINERS TABLE (Created before members for FK)
-- =====================================================
CREATE TABLE `trainers` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `phone` VARCHAR(20) NOT NULL,
  `specialization` VARCHAR(100),
  `commission_rate` DECIMAL(5, 2) DEFAULT 80.00 COMMENT 'Percentage share for trainer',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- MEMBERS TABLE
-- =====================================================
CREATE TABLE `members` (
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
  INDEX `idx_status` (`status`),
  INDEX `idx_phone` (`phone`),
  INDEX `idx_end_date` (`end_date`),
  INDEX `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- PAYMENTS TABLE
-- =====================================================
CREATE TABLE `payments` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `member_id` INT NOT NULL,
  `amount` DECIMAL(10, 2) NOT NULL,
  `payment_method` ENUM('cash', 'easypaisa', 'jazzcash', 'nayapay', 'sadapay', 'bank_transfer') DEFAULT 'cash',
  `payment_date` DATE NOT NULL,
  `description` TEXT,
  `receipt_number` VARCHAR(50),
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`member_id`) REFERENCES `members`(`id`) ON DELETE CASCADE,
  INDEX `idx_member_id` (`member_id`),
  INDEX `idx_payment_date` (`payment_date`),
  INDEX `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- COMMISSIONS TABLE
-- =====================================================
CREATE TABLE `commissions` (
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
  INDEX `idx_trainer_commissions` (`trainer_id`, `created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- ATTENDANCE TABLE
-- =====================================================
CREATE TABLE `attendance` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `member_id` INT NOT NULL,
  `check_in_time` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `check_out_time` DATETIME NULL,
  `status` ENUM('present', 'absent') DEFAULT 'present',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`member_id`) REFERENCES `members`(`id`) ON DELETE CASCADE,
  INDEX `idx_attendance_member_date` (`member_id`, `check_in_time`),
  INDEX `idx_check_in` (`check_in_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- EXPENSES TABLE - Track Gym Expenditures
-- =====================================================
CREATE TABLE `expenses` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(100) NOT NULL,
  `amount` DECIMAL(10,2) NOT NULL,
  `expense_date` DATE NOT NULL,
  `category` VARCHAR(50) NOT NULL,
  `description` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_expense_date` (`expense_date`),
  INDEX `idx_category` (`category`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- SAMPLE DATA - Admin User
-- =====================================================
INSERT INTO `users` (`username`, `email`, `password`, `full_name`) 
VALUES ('admin', 'admin@gym.local', 'admin123', 'Admin User');

-- =====================================================
-- SAMPLE DATA - Membership Plans
-- =====================================================
INSERT INTO `membership_plans` (`name`, `duration`, `price`, `description`) VALUES
('1 Month', 30, 2500.00, 'Basic monthly membership'),
('3 Months', 90, 6500.00, '3-month membership plan'),
('6 Months', 180, 11000.00, '6-month membership with discount'),
('1 Year', 365, 18000.00, 'Annual membership - Best Value');

-- =====================================================
-- SAMPLE DATA - Members
-- =====================================================
INSERT INTO `members` (`full_name`, `phone`, `gender`, `plan_id`, `start_date`, `end_date`, `status`) VALUES
('Ahmed Khan', '03001234567', 'Male', 1, '2026-02-01', '2026-03-02', 'active'),
('Fatima Ali', '03009876543', 'Female', 2, '2025-11-15', '2026-02-13', 'expired'),
('Hassan Ahmad', '03101112131', 'Male', 3, '2025-09-01', '2026-02-28', 'active'),
('Sara Khan', '03357654321', 'Female', 1, '2026-02-10', '2026-03-10', 'active'),
('Ali Raza', '03451234567', 'Male', 4, '2025-02-16', '2026-02-15', 'expired');

-- =====================================================
-- SAMPLE DATA - Payments
-- =====================================================
INSERT INTO `payments` (`member_id`, `amount`, `payment_method`, `payment_date`, `description`) VALUES
(1, 2500.00, 'cash', '2026-02-01', 'Monthly membership payment'),
(2, 6500.00, 'easypaisa', '2025-11-15', 'Quarterly membership payment'),
(3, 11000.00, 'jazzcash', '2025-09-01', 'Semi-annual membership payment'),
(4, 2500.00, 'cash', '2026-02-10', 'Monthly membership payment'),
(5, 18000.00, 'bank_transfer', '2025-02-16', 'Annual membership payment'),
(1, 2500.00, 'nayapay', '2026-02-16', 'Renewal payment');

-- =====================================================
-- END OF DATABASE SETUP
-- =====================================================
