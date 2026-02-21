-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 20, 2026 at 06:59 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `avengers`
--

-- --------------------------------------------------------

--
-- Table structure for table `attendance`
--

CREATE TABLE `attendance` (
  `id` int(11) NOT NULL,
  `member_id` int(11) NOT NULL,
  `check_in_time` datetime DEFAULT current_timestamp(),
  `check_out_time` datetime DEFAULT NULL,
  `status` enum('present','absent') DEFAULT 'present',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `attendance`
--

INSERT INTO `attendance` (`id`, `member_id`, `check_in_time`, `check_out_time`, `status`, `created_at`) VALUES
(1, 6, '2026-02-17 09:25:01', NULL, 'present', '2026-02-17 04:25:01'),
(2, 6, '2026-02-18 12:07:22', NULL, 'present', '2026-02-18 07:07:22'),
(3, 11, '2026-02-20 10:38:34', NULL, 'present', '2026-02-20 05:38:34'),
(4, 11, '2026-02-20 10:38:44', NULL, 'present', '2026-02-20 05:38:44'),
(5, 11, '2026-02-20 10:38:46', NULL, 'present', '2026-02-20 05:38:46'),
(6, 6, '2026-02-20 10:40:33', NULL, 'present', '2026-02-20 05:40:33'),
(7, 5, '2026-02-20 10:40:36', NULL, 'present', '2026-02-20 05:40:36'),
(8, 4, '2026-02-20 10:40:37', NULL, 'present', '2026-02-20 05:40:37'),
(9, 3, '2026-02-20 10:40:38', NULL, 'present', '2026-02-20 05:40:38'),
(10, 1, '2026-02-20 10:40:54', NULL, 'present', '2026-02-20 05:40:54');

-- --------------------------------------------------------

--
-- Table structure for table `commissions`
--

CREATE TABLE `commissions` (
  `id` int(11) NOT NULL,
  `trainer_id` int(11) NOT NULL,
  `member_id` int(11) NOT NULL,
  `payment_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `commission_rate` decimal(5,2) NOT NULL COMMENT 'Rate at time of payment',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `commissions`
--

INSERT INTO `commissions` (`id`, `trainer_id`, `member_id`, `payment_id`, `amount`, `commission_rate`, `created_at`) VALUES
(6, 1, 6, 17, 4000.00, 80.00, '2026-02-17 07:24:56'),
(9, 1, 6, 24, 4000.00, 80.00, '2026-02-20 04:22:22'),
(10, 3, 11, 32, 5000.00, 50.00, '2026-02-20 05:30:15');

-- --------------------------------------------------------

--
-- Table structure for table `expenses`
--

CREATE TABLE `expenses` (
  `id` int(11) NOT NULL,
  `title` varchar(100) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `expense_date` date NOT NULL,
  `category` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `expenses`
--

INSERT INTO `expenses` (`id`, `title`, `amount`, `expense_date`, `category`, `description`, `created_at`, `updated_at`) VALUES
(1, 'GYM Software', 25000.00, '2026-02-17', 'Equipment', '', '2026-02-17 04:21:19', '2026-02-17 04:21:19'),
(2, 'Rent', 50000.00, '2026-02-20', 'Rent', '', '2026-02-20 05:04:41', '2026-02-20 05:04:41');

-- --------------------------------------------------------

--
-- Table structure for table `fee_types`
--

CREATE TABLE `fee_types` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `default_amount` decimal(10,2) DEFAULT 0.00,
  `is_commissionable` tinyint(1) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `fee_types`
--

INSERT INTO `fee_types` (`id`, `name`, `default_amount`, `is_commissionable`, `is_active`, `created_at`) VALUES
(1, 'Membership Fee', 0.00, 0, 1, '2026-02-17 03:44:44'),
(2, 'Admission Fee', 1000.00, 0, 1, '2026-02-17 03:44:44'),
(3, 'Cardio Fee', 500.00, 0, 1, '2026-02-17 03:44:44'),
(4, 'Personal Trainer (Rajab Raza)', 2000.00, 1, 1, '2026-02-17 03:44:44'),
(5, 'Locker Fee', 300.00, 0, 1, '2026-02-17 03:44:44'),
(6, 'Other', 0.00, 0, 1, '2026-02-17 03:44:44'),
(7, 'Updated Test Fee', 750.00, 0, 0, '2026-02-17 04:48:11');

-- --------------------------------------------------------

--
-- Table structure for table `members`
--

CREATE TABLE `members` (
  `id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `gender` enum('Male','Female','Other') DEFAULT 'Male',
  `profile_picture` varchar(255) DEFAULT NULL,
  `plan_id` int(11) NOT NULL,
  `trainer_id` int(11) DEFAULT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `status` enum('active','expired','suspended') DEFAULT 'active',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `members`
--

INSERT INTO `members` (`id`, `full_name`, `phone`, `gender`, `profile_picture`, `plan_id`, `trainer_id`, `start_date`, `end_date`, `status`, `notes`, `created_at`, `updated_at`) VALUES
(1, 'Ahmed Khan', '03001234567', 'Male', NULL, 1, NULL, '2026-02-01', '2026-03-02', 'active', NULL, '2026-02-17 03:37:26', '2026-02-17 03:37:26'),
(2, 'Fatima Ali', '03009876543', 'Female', NULL, 2, NULL, '2025-11-15', '2026-02-13', 'expired', NULL, '2026-02-17 03:37:26', '2026-02-17 03:37:26'),
(3, 'Hassan Ahmad', '03101112131', 'Male', NULL, 3, NULL, '2025-09-01', '2026-02-28', 'active', NULL, '2026-02-17 03:37:26', '2026-02-17 03:37:26'),
(4, 'Sara Khan', '03357654321', 'Female', NULL, 1, NULL, '2026-02-10', '2026-03-10', 'active', NULL, '2026-02-17 03:37:26', '2026-02-17 03:37:26'),
(5, 'Ali Raza', '03451234567', 'Male', NULL, 4, NULL, '2026-02-18', '2027-02-18', 'active', NULL, '2026-02-17 03:37:26', '2026-02-18 08:56:15'),
(6, 'Syed Abdullah', '0313225897', 'Male', NULL, 1, 1, '2026-02-27', '2026-03-29', 'active', NULL, '2026-02-17 04:17:09', '2026-02-17 05:00:44'),
(11, 'Rajab Raza', '03152891255', 'Male', NULL, 5, 3, '2026-02-20', '2026-02-21', 'active', NULL, '2026-02-20 05:09:50', '2026-02-20 05:26:21');

-- --------------------------------------------------------

--
-- Table structure for table `membership_plans`
--

CREATE TABLE `membership_plans` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `duration` int(11) NOT NULL COMMENT 'Duration in days',
  `price` decimal(10,2) NOT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `membership_plans`
--

INSERT INTO `membership_plans` (`id`, `name`, `duration`, `price`, `description`, `is_active`, `created_at`, `updated_at`) VALUES
(1, '1 Month', 30, 2500.00, 'Basic monthly membership', 1, '2026-02-17 03:37:26', '2026-02-17 03:37:26'),
(2, '3 Months', 90, 6500.00, '3-month membership plan', 1, '2026-02-17 03:37:26', '2026-02-17 03:37:26'),
(3, '6 Months', 180, 11000.00, '6-month membership with discount', 1, '2026-02-17 03:37:26', '2026-02-17 03:37:26'),
(4, '1 Year', 365, 18000.00, 'Annual membership - Best Value', 1, '2026-02-17 03:37:26', '2026-02-17 03:37:26'),
(5, '1 Day Test ', 1, 500.00, '', 1, '2026-02-20 05:09:25', '2026-02-20 05:09:25');

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `member_id` int(11) NOT NULL,
  `fee_type_id` int(11) DEFAULT 1,
  `amount` decimal(10,2) NOT NULL,
  `payment_method` enum('cash','easypaisa','jazzcash','nayapay','sadapay','bank_transfer') DEFAULT 'cash',
  `payment_date` date NOT NULL,
  `description` text DEFAULT NULL,
  `receipt_number` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`id`, `member_id`, `fee_type_id`, `amount`, `payment_method`, `payment_date`, `description`, `receipt_number`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 2500.00, 'cash', '2026-02-01', 'Monthly membership payment', NULL, '2026-02-17 03:37:26', '2026-02-17 03:37:26'),
(2, 2, 1, 6500.00, 'easypaisa', '2025-11-15', 'Quarterly membership payment', NULL, '2026-02-17 03:37:26', '2026-02-17 03:37:26'),
(3, 3, 1, 11000.00, 'jazzcash', '2025-09-01', 'Semi-annual membership payment', NULL, '2026-02-17 03:37:26', '2026-02-17 03:37:26'),
(4, 4, 1, 2500.00, 'cash', '2026-02-10', 'Monthly membership payment', NULL, '2026-02-17 03:37:26', '2026-02-17 03:37:26'),
(5, 5, 1, 18000.00, 'bank_transfer', '2025-02-16', 'Annual membership payment', NULL, '2026-02-17 03:37:26', '2026-02-17 03:37:26'),
(6, 1, 1, 2500.00, 'nayapay', '2026-02-16', 'Renewal payment', NULL, '2026-02-17 03:37:26', '2026-02-17 03:37:26'),
(16, 6, 1, 2500.00, 'nayapay', '2026-02-18', '', 'REC-20260217082456-6', '2026-02-17 07:24:56', '2026-02-17 07:24:56'),
(17, 6, 4, 5000.00, 'nayapay', '2026-02-18', '', 'REC-20260217082456-6', '2026-02-17 07:24:56', '2026-02-17 07:24:56'),
(18, 6, 2, 1000.00, 'nayapay', '2026-02-18', '', 'REC-20260217082456-6', '2026-02-17 07:24:56', '2026-02-17 07:24:56'),
(19, 6, 3, 500.00, 'nayapay', '2026-02-18', '', 'REC-20260217082456-6', '2026-02-17 07:24:56', '2026-02-17 07:24:56'),
(20, 5, 1, 18000.00, 'cash', '2026-02-18', 'Membership Renewal (2026-02-18 to 2027-02-18)', 'REC-1771404810-5', '2026-02-18 08:53:30', '2026-02-18 08:53:30'),
(21, 5, 1, 18000.00, 'cash', '2026-02-18', 'Membership Renewal (2026-02-18 to 2027-02-18)', 'REC-1771404844-5', '2026-02-18 08:54:04', '2026-02-18 08:54:04'),
(22, 5, 1, 18000.00, 'cash', '2026-02-18', 'Membership Renewal (2026-02-18 to 2027-02-18)', 'REC-1771404975-5', '2026-02-18 08:56:15', '2026-02-18 08:56:15'),
(23, 6, 1, 2500.00, 'cash', '2026-02-20', '', 'REC-20260220092222-6', '2026-02-20 04:22:22', '2026-02-20 04:22:22'),
(24, 6, 4, 5000.00, 'cash', '2026-02-20', '', 'REC-20260220092222-6', '2026-02-20 04:22:22', '2026-02-20 04:22:22'),
(25, 6, 2, 1000.00, 'cash', '2026-02-20', '', 'REC-20260220092222-6', '2026-02-20 04:22:22', '2026-02-20 04:22:22'),
(26, 6, 3, 500.00, 'cash', '2026-02-20', '', 'REC-20260220092222-6', '2026-02-20 04:22:22', '2026-02-20 04:22:22'),
(31, 11, 1, 500.00, 'bank_transfer', '2026-02-20', '', 'REC-20260220103015-11', '2026-02-20 05:30:15', '2026-02-20 05:30:15'),
(32, 11, 4, 10000.00, 'bank_transfer', '2026-02-20', '', 'REC-20260220103015-11', '2026-02-20 05:30:15', '2026-02-20 05:30:15'),
(33, 11, 2, 1000.00, 'bank_transfer', '2026-02-20', '', 'REC-20260220103015-11', '2026-02-20 05:30:15', '2026-02-20 05:30:15'),
(34, 11, 3, 500.00, 'bank_transfer', '2026-02-20', '', 'REC-20260220103015-11', '2026-02-20 05:30:15', '2026-02-20 05:30:15'),
(35, 11, 5, 300.00, 'bank_transfer', '2026-02-20', '', 'REC-20260220103015-11', '2026-02-20 05:30:15', '2026-02-20 05:30:15');

-- --------------------------------------------------------

--
-- Table structure for table `trainers`
--

CREATE TABLE `trainers` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `specialization` varchar(100) DEFAULT NULL,
  `commission_rate` decimal(5,2) DEFAULT 80.00 COMMENT 'Percentage share for trainer',
  `fee` decimal(10,2) DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `trainers`
--

INSERT INTO `trainers` (`id`, `name`, `phone`, `specialization`, `commission_rate`, `fee`, `created_at`, `updated_at`) VALUES
(1, 'Rajab Raza', '03001234656', 'Body Building', 80.00, 5000.00, '2026-02-17 04:12:02', '2026-02-17 05:06:00'),
(3, 'Faizan', '0313258/884', 'Body Building', 50.00, 10000.00, '2026-02-20 05:14:52', '2026-02-20 05:29:26');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `full_name`, `created_at`, `updated_at`) VALUES
(1, 'admin', 'admin@gym.local', 'admin123', 'Admin User', '2026-02-17 03:37:26', '2026-02-17 03:37:26');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `attendance`
--
ALTER TABLE `attendance`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_attendance_member_date` (`member_id`,`check_in_time`),
  ADD KEY `idx_check_in` (`check_in_time`);

--
-- Indexes for table `commissions`
--
ALTER TABLE `commissions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `member_id` (`member_id`),
  ADD KEY `payment_id` (`payment_id`),
  ADD KEY `idx_trainer_commissions` (`trainer_id`,`created_at`);

--
-- Indexes for table `expenses`
--
ALTER TABLE `expenses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_expense_date` (`expense_date`),
  ADD KEY `idx_category` (`category`);

--
-- Indexes for table `fee_types`
--
ALTER TABLE `fee_types`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`),
  ADD KEY `idx_is_active` (`is_active`);

--
-- Indexes for table `members`
--
ALTER TABLE `members`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `phone` (`phone`),
  ADD KEY `plan_id` (`plan_id`),
  ADD KEY `trainer_id` (`trainer_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_phone` (`phone`),
  ADD KEY `idx_end_date` (`end_date`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `membership_plans`
--
ALTER TABLE `membership_plans`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`),
  ADD KEY `idx_is_active` (`is_active`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_member_id` (`member_id`),
  ADD KEY `idx_payment_date` (`payment_date`),
  ADD KEY `idx_created_at` (`created_at`),
  ADD KEY `fk_payments_fee_type` (`fee_type_id`);

--
-- Indexes for table `trainers`
--
ALTER TABLE `trainers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_username` (`username`),
  ADD KEY `idx_email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `attendance`
--
ALTER TABLE `attendance`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `commissions`
--
ALTER TABLE `commissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `expenses`
--
ALTER TABLE `expenses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `fee_types`
--
ALTER TABLE `fee_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `members`
--
ALTER TABLE `members`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `membership_plans`
--
ALTER TABLE `membership_plans`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT for table `trainers`
--
ALTER TABLE `trainers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `attendance`
--
ALTER TABLE `attendance`
  ADD CONSTRAINT `attendance_ibfk_1` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `commissions`
--
ALTER TABLE `commissions`
  ADD CONSTRAINT `commissions_ibfk_1` FOREIGN KEY (`trainer_id`) REFERENCES `trainers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `commissions_ibfk_2` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `commissions_ibfk_3` FOREIGN KEY (`payment_id`) REFERENCES `payments` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `members`
--
ALTER TABLE `members`
  ADD CONSTRAINT `members_ibfk_1` FOREIGN KEY (`plan_id`) REFERENCES `membership_plans` (`id`),
  ADD CONSTRAINT `members_ibfk_2` FOREIGN KEY (`trainer_id`) REFERENCES `trainers` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `fk_payments_fee_type` FOREIGN KEY (`fee_type_id`) REFERENCES `fee_types` (`id`),
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
