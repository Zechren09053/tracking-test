-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 03, 2025 at 02:35 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `prfs`
--

-- --------------------------------------------------------

--
-- Table structure for table `ferries`
--

CREATE TABLE `ferries` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `latitude` decimal(9,6) DEFAULT NULL,
  `longitude` decimal(9,6) DEFAULT NULL,
  `last_updated` timestamp NOT NULL DEFAULT current_timestamp(),
  `active_time` float DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'inactive',
  `operator` varchar(100) DEFAULT NULL,
  `status_changed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `max_capacity` int(11) NOT NULL DEFAULT 30,
  `current_capacity` int(11) NOT NULL DEFAULT 0,
  `speed` float DEFAULT 0,
  `route_index` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ferries`
--

INSERT INTO `ferries` (`id`, `name`, `latitude`, `longitude`, `last_updated`, `active_time`, `status`, `operator`, `status_changed_at`, `max_capacity`, `current_capacity`, `speed`, `route_index`) VALUES
(1, 'Ferry 001', 14.561400, 121.063100, '2025-05-03 00:34:59', NULL, 'active', NULL, '2025-05-02 09:45:39', 30, 27, 0, 5),
(2, 'Ferry 215', 14.553300, 121.080600, '2025-05-03 00:35:00', NULL, 'active', NULL, '2025-05-02 09:45:48', 30, 0, 0, 5),
(3, 'Ferry 212', 14.559700, 121.066400, '2025-05-03 00:35:06', NULL, 'active', NULL, '2025-05-02 06:35:54', 30, 24, 0, 5),
(4, 'Ferry 241', 14.553300, 121.080600, '2025-05-03 00:35:06', NULL, 'active', NULL, '2025-05-01 08:14:51', 30, 0, 0, 5);

--
-- Triggers `ferries`
--
DELIMITER $$
CREATE TRIGGER `update_status_timestamp` BEFORE UPDATE ON `ferries` FOR EACH ROW BEGIN
    IF NEW.status = 'active' AND OLD.status != 'active' THEN
        SET NEW.status_changed_at = NOW();
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `ferry_locations`
--

CREATE TABLE `ferry_locations` (
  `id` int(11) NOT NULL,
  `code` varchar(50) NOT NULL,
  `latitude` decimal(10,8) NOT NULL,
  `longitude` decimal(11,8) NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `last_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ferry_locations`
--

INSERT INTO `ferry_locations` (`id`, `code`, `latitude`, `longitude`, `updated_at`, `last_updated`) VALUES
(20, 'PRFS001', 14.56600670, 121.04828760, '2025-05-01 08:58:54', '2025-05-01 08:58:54'),
(24, 'main', 14.56600294, 121.04829783, '2025-04-28 09:23:03', '2025-04-28 09:23:03'),
(25, 'PRFS003', 14.56617460, 121.04831460, '2025-05-01 08:54:04', '2025-05-01 08:54:04'),
(26, 'PRFS0012', 14.56619040, 121.04830180, '2025-04-28 09:26:29', '2025-04-28 09:26:29'),
(27, 'PRFS009', 14.55279910, 121.05037860, '2025-04-28 09:25:34', '2025-04-28 09:25:34'),
(28, 'PRFS004', 14.56600294, 121.04829783, '2025-04-28 09:34:32', '2025-04-28 09:34:32'),
(29, '1', 14.56600670, 121.04828760, '2025-05-01 08:56:02', '2025-05-01 08:56:02'),
(30, 'PRFS069', 14.55889880, 121.06134070, '2025-05-01 08:49:33', '2025-05-01 08:49:33'),
(31, 'PRFS096', 14.55889490, 121.06134310, '2025-05-01 08:49:58', '2025-05-01 08:49:58'),
(32, 'PRFS005', 14.55932890, 121.06049220, '2025-05-01 08:58:08', '2025-05-01 08:58:08');

-- --------------------------------------------------------

--
-- Table structure for table `ferry_logs`
--

CREATE TABLE `ferry_logs` (
  `id` int(11) NOT NULL,
  `ferry_id` int(11) NOT NULL,
  `trip_date` datetime DEFAULT current_timestamp(),
  `passenger_count` int(11) NOT NULL,
  `speed` float DEFAULT 0,
  `latitude` decimal(9,6) DEFAULT NULL,
  `longitude` decimal(9,6) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `passenger_id_pass`
--

CREATE TABLE `passenger_id_pass` (
  `id` int(11) NOT NULL,
  `passenger_id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `birth_date` date NOT NULL,
  `profile_image` varchar(255) DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `phone_number` varchar(20) NOT NULL,
  `qr_code_data` varchar(255) NOT NULL,
  `issued_at` datetime DEFAULT current_timestamp(),
  `expires_at` datetime NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `last_used` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `staff_users`
--

CREATE TABLE `staff_users` (
  `staff_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('super_admin','admin','employee') NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `middle_name` varchar(50) DEFAULT NULL,
  `last_name` varchar(50) NOT NULL,
  `position` varchar(100) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `profile_pic` varchar(255) DEFAULT 'default.png'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `staff_users`
--

INSERT INTO `staff_users` (`staff_id`, `username`, `email`, `password`, `role`, `first_name`, `middle_name`, `last_name`, `position`, `is_active`, `created_at`, `updated_at`, `profile_pic`) VALUES
(1, 'superadmin', 'john.doe@example.com', 'password123', 'admin', 'John', 'Michael', 'Doe', 'Manager', 1, '2025-05-02 06:56:41', '2025-05-02 06:57:17', 'Pic1.png');

-- --------------------------------------------------------

--
-- Table structure for table `tickets`
--

CREATE TABLE `tickets` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `ticket_type` varchar(255) NOT NULL,
  `purchase_date` datetime DEFAULT current_timestamp(),
  `amount` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `ferries`
--
ALTER TABLE `ferries`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ferry_locations`
--
ALTER TABLE `ferry_locations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ferry_logs`
--
ALTER TABLE `ferry_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ferry_id` (`ferry_id`);

--
-- Indexes for table `passenger_id_pass`
--
ALTER TABLE `passenger_id_pass`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `qr_code_data` (`qr_code_data`);

--
-- Indexes for table `staff_users`
--
ALTER TABLE `staff_users`
  ADD PRIMARY KEY (`staff_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `tickets`
--
ALTER TABLE `tickets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `ferries`
--
ALTER TABLE `ferries`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `ferry_locations`
--
ALTER TABLE `ferry_locations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT for table `ferry_logs`
--
ALTER TABLE `ferry_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `passenger_id_pass`
--
ALTER TABLE `passenger_id_pass`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `staff_users`
--
ALTER TABLE `staff_users`
  MODIFY `staff_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `tickets`
--
ALTER TABLE `tickets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `ferry_logs`
--
ALTER TABLE `ferry_logs`
  ADD CONSTRAINT `ferry_logs_ibfk_1` FOREIGN KEY (`ferry_id`) REFERENCES `ferries` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tickets`
--
ALTER TABLE `tickets`
  ADD CONSTRAINT `tickets_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `staff_users` (`staff_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
