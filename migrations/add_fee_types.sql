-- =====================================================
-- MIGRATION: Add Fee Types System
-- Created: February 2026
-- Description: Adds support for multiple fee types (Admission, Cardio, etc.)
-- =====================================================

USE `avengers`;

-- =====================================================
-- CREATE FEE_TYPES TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS `fee_types` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL UNIQUE,
  `default_amount` DECIMAL(10, 2) DEFAULT 0.00,
  `is_active` BOOLEAN DEFAULT TRUE,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_is_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- INSERT DEFAULT FEE TYPES
-- =====================================================
INSERT INTO `fee_types` (`name`, `default_amount`) VALUES
('Membership Fee', 0.00),
('Admission Fee', 1000.00),
('Cardio Fee', 500.00),
('Personal Training', 2000.00),
('Locker Fee', 300.00),
('Other', 0.00);

-- =====================================================
-- ADD FEE_TYPE_ID TO PAYMENTS TABLE
-- =====================================================
ALTER TABLE `payments` 
ADD COLUMN `fee_type_id` INT DEFAULT 1 AFTER `member_id`,
ADD CONSTRAINT `fk_payments_fee_type` 
  FOREIGN KEY (`fee_type_id`) REFERENCES `fee_types`(`id`);

-- =====================================================
-- UPDATE EXISTING PAYMENTS TO DEFAULT FEE TYPE
-- =====================================================
UPDATE `payments` SET `fee_type_id` = 1 WHERE `fee_type_id` IS NULL;
