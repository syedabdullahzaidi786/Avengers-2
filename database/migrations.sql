-- Migration: Add commission management to fee_types
-- Date: 2026-02-20

ALTER TABLE `fee_types` ADD COLUMN `is_commissionable` TINYINT(1) DEFAULT 0 AFTER `default_amount`;

-- Set initial defaults based on naming patterns
UPDATE `fee_types` SET `is_commissionable` = 1 
WHERE name LIKE '%trainer%' 
   OR name LIKE '%training%' 
   OR name LIKE '%personal%';
