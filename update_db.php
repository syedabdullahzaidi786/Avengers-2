<?php
/**
 * Database Migration Script
 * Updates the database schema to include Trainers, Commissions, and Attendance
 */

require_once __DIR__ . '/../config/database.php';

try {
    echo "Starting database update...\n";

    // 1. Create Trainers Table
    $pdo->exec("CREATE TABLE IF NOT EXISTS `trainers` (
      `id` INT AUTO_INCREMENT PRIMARY KEY,
      `name` VARCHAR(100) NOT NULL,
      `phone` VARCHAR(20) NOT NULL,
      `specialization` VARCHAR(100),
      `commission_rate` DECIMAL(5, 2) DEFAULT 80.00 COMMENT 'Percentage share for trainer',
      `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
      `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
    echo "Checked/Created `trainers` table.\n";

    // 2. Add trainer_id to members if not exists
    $stmt = $pdo->query("SHOW COLUMNS FROM `members` LIKE 'trainer_id'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("ALTER TABLE `members` ADD COLUMN `trainer_id` INT NULL, 
                    ADD CONSTRAINT `fk_member_trainer` FOREIGN KEY (`trainer_id`) REFERENCES `trainers`(`id`) ON DELETE SET NULL");
        echo "Added `trainer_id` column to `members` table.\n";
    } else {
        echo "`trainer_id` column already exists in `members` table.\n";
    }

    // 3. Create Commissions Table
    $pdo->exec("CREATE TABLE IF NOT EXISTS `commissions` (
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
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
    echo "Checked/Created `commissions` table.\n";

    // 4. Create Attendance Table
    $pdo->exec("CREATE TABLE IF NOT EXISTS `attendance` (
      `id` INT AUTO_INCREMENT PRIMARY KEY,
      `member_id` INT NOT NULL,
      `check_in_time` DATETIME DEFAULT CURRENT_TIMESTAMP,
      `check_out_time` DATETIME NULL,
      `status` ENUM('present', 'absent') DEFAULT 'present',
      `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
      FOREIGN KEY (`member_id`) REFERENCES `members`(`id`) ON DELETE CASCADE,
      INDEX `idx_attendance_member_date` (`member_id`, `check_in_time`),
      INDEX `idx_check_in` (`check_in_time`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
    echo "Checked/Created `attendance` table.\n";

    echo "Database update completed successfully!\n";

} catch (PDOException $e) {
    die("Error updating database: " . $e->getMessage() . "\n");
}
?>
