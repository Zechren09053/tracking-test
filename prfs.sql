-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 10, 2025 at 04:48 PM
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

DELIMITER $$
--
-- Procedures
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `populate_ferry_logs` ()   BEGIN
    DECLARE month_idx INT DEFAULT 0;
    DECLARE curr_date DATE;
    DECLARE i INT;

    SET month_idx = 0;
    WHILE month_idx < 6 DO
        SET curr_date = DATE_SUB(CURDATE(), INTERVAL (5 - month_idx) MONTH);

        SET i = 1;
        WHILE i <= 15 DO
            INSERT INTO ferry_logs (ferry_id, trip_date, passenger_count, speed, latitude, longitude)
            VALUES (
                FLOOR(1 + RAND() * 3),  -- Random ferry ID 1-3
                DATE_ADD(curr_date, INTERVAL FLOOR(RAND() * 28) DAY),
                FLOOR(10 + RAND() * 20),  -- 10 to 30 passengers
                ROUND(RAND() * 15, 2),
                14.589 + (RAND() * 0.01),
                121.003 + (RAND() * 0.01)
            );
            SET i = i + 1;
        END WHILE;

        SET month_idx = month_idx + 1;
    END WHILE;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `seed_ferry_logs` ()   BEGIN
    DECLARE month_offset INT DEFAULT 0;
    DECLARE i INT;
    DECLARE trip_day DATE;

    WHILE month_offset < 6 DO
        SET i = 1;
        WHILE i <= 15 DO
            SET trip_day = DATE_ADD(DATE_SUB(CURDATE(), INTERVAL month_offset MONTH), INTERVAL FLOOR(RAND() * 28) DAY);
            INSERT INTO ferry_logs (ferry_id, trip_date, passenger_count, speed, latitude, longitude)
            VALUES (
                FLOOR(1 + RAND() * 3),  -- Ferry ID between 1-3
                trip_day,
                FLOOR(10 + RAND() * 25),  -- Passengers between 10–35
                ROUND(5 + RAND() * 10, 2),
                14.586 + (RAND() * 0.01),
                121.004 + (RAND() * 0.01)
            );
            SET i = i + 1;
        END WHILE;
        SET month_offset = month_offset + 1;
    END WHILE;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `announcements`
--

CREATE TABLE `announcements` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `announcements`
--

INSERT INTO `announcements` (`id`, `title`, `message`, `created_at`) VALUES
(1, 'New Announcement', 'test 1 announce ', '2025-05-07 13:44:42'),
(2, 'New Announcement2', 'adawd1dawda', '2025-05-07 13:45:37'),
(3, '3', '3', '2025-05-07 13:54:44'),
(4, '4', '4', '2025-05-07 13:54:46'),
(5, '5', 'weqweqwe', '2025-05-07 15:48:03'),
(6, '6', '667', '2025-05-09 01:00:33');

-- --------------------------------------------------------

--
-- Table structure for table `downstream_schedules`
--

CREATE TABLE `downstream_schedules` (
  `id` int(11) NOT NULL,
  `route_name` varchar(255) NOT NULL DEFAULT 'Pinagbuhatan → Escolta',
  `row_id` int(11) NOT NULL,
  `col_id` int(11) NOT NULL,
  `station_name` varchar(255) NOT NULL,
  `schedule_time` time NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `downstream_schedules`
--

INSERT INTO `downstream_schedules` (`id`, `route_name`, `row_id`, `col_id`, `station_name`, `schedule_time`) VALUES
(1, 'Kalawaan → Escolta', 1, 1, 'Kalawaan', '06:00:00'),
(2, 'Kalawaan → Escolta', 1, 2, 'San Joaquin', '06:10:00'),
(3, 'Kalawaan → Escolta', 1, 3, 'Guadalupe', '06:20:00'),
(4, 'Kalawaan → Escolta', 1, 4, 'Hulo', '06:25:00'),
(5, 'Kalawaan → Escolta', 1, 5, 'Valenzuela', '06:28:00'),
(6, 'Kalawaan → Escolta', 1, 6, 'Lambingan', '06:35:00'),
(7, 'Kalawaan → Escolta', 1, 7, 'Sta. Ana', '06:39:00'),
(8, 'Kalawaan → Escolta', 1, 8, 'PUP', '06:46:00'),
(9, 'Kalawaan → Escolta', 1, 9, 'Quinta', '07:00:00'),
(10, 'Kalawaan → Escolta', 1, 10, 'Lawton', '07:03:00'),
(11, 'Kalawaan → Escolta', 1, 11, 'Escolta', '07:08:00'),
(12, 'Kalawaan → Escolta', 2, 1, 'Kalawaan', '07:00:00'),
(13, 'Kalawaan → Escolta', 2, 2, 'San Joaquin', '07:10:00'),
(14, 'Kalawaan → Escolta', 2, 3, 'Guadalupe', '07:20:00'),
(15, 'Kalawaan → Escolta', 2, 4, 'Hulo', '07:25:00'),
(16, 'Kalawaan → Escolta', 2, 5, 'Valenzuela', '07:28:00'),
(17, 'Kalawaan → Escolta', 2, 6, 'Lambingan', '07:34:00'),
(18, 'Kalawaan → Escolta', 2, 7, 'Sta. Ana', '07:40:00'),
(19, 'Kalawaan → Escolta', 2, 8, 'PUP', '07:45:00'),
(20, 'Kalawaan → Escolta', 2, 9, 'Quinta', '07:53:00'),
(21, 'Kalawaan → Escolta', 2, 10, 'Lawton', '07:55:00'),
(22, 'Kalawaan → Escolta', 2, 11, 'Escolta', '08:00:00'),
(23, 'Kalawaan → Escolta', 3, 1, 'Kalawaan', '08:30:00'),
(24, 'Kalawaan → Escolta', 3, 2, 'San Joaquin', '08:40:00'),
(25, 'Kalawaan → Escolta', 3, 3, 'Guadalupe', '08:50:00'),
(26, 'Kalawaan → Escolta', 3, 4, 'Hulo', '08:55:00'),
(27, 'Kalawaan → Escolta', 3, 5, 'Valenzuela', '08:58:00'),
(28, 'Kalawaan → Escolta', 3, 6, 'Lambingan', '09:04:00'),
(29, 'Kalawaan → Escolta', 3, 7, 'Sta. Ana', '09:10:00'),
(30, 'Kalawaan → Escolta', 3, 8, 'PUP', '09:15:00'),
(31, 'Kalawaan → Escolta', 3, 9, 'Quinta', '09:23:00'),
(32, 'Kalawaan → Escolta', 3, 10, 'Lawton', '09:25:00'),
(33, 'Kalawaan → Escolta', 3, 11, 'Escolta', '09:30:00'),
(34, 'Kalawaan → Escolta', 4, 1, 'Kalawaan', '10:30:00'),
(35, 'Kalawaan → Escolta', 4, 2, 'San Joaquin', '10:40:00'),
(36, 'Kalawaan → Escolta', 4, 3, 'Guadalupe', '10:50:00'),
(37, 'Kalawaan → Escolta', 4, 4, 'Hulo', '10:55:00'),
(38, 'Kalawaan → Escolta', 4, 5, 'Valenzuela', '10:58:00'),
(39, 'Kalawaan → Escolta', 4, 6, 'Lambingan', '11:05:00'),
(40, 'Kalawaan → Escolta', 4, 7, 'Sta. Ana', '11:10:00'),
(41, 'Kalawaan → Escolta', 4, 8, 'PUP', '11:15:00'),
(42, 'Kalawaan → Escolta', 4, 9, 'Quinta', '11:23:00'),
(43, 'Kalawaan → Escolta', 4, 10, 'Lawton', '11:25:00'),
(44, 'Kalawaan → Escolta', 4, 11, 'Escolta', '11:30:00'),
(45, 'Kalawaan → Escolta', 5, 1, 'Kalawaan', '11:30:00'),
(46, 'Kalawaan → Escolta', 5, 2, 'San Joaquin', '11:40:00'),
(47, 'Kalawaan → Escolta', 5, 3, 'Guadalupe', '11:50:00'),
(48, 'Kalawaan → Escolta', 5, 4, 'Hulo', '11:55:00'),
(49, 'Kalawaan → Escolta', 5, 5, 'Valenzuela', '11:58:00'),
(50, 'Kalawaan → Escolta', 5, 6, 'Lambingan', '12:05:00'),
(51, 'Kalawaan → Escolta', 5, 7, 'Sta. Ana', '12:10:00'),
(52, 'Kalawaan → Escolta', 5, 8, 'PUP', '12:15:00'),
(53, 'Kalawaan → Escolta', 5, 9, 'Quinta', '12:23:00'),
(54, 'Kalawaan → Escolta', 5, 10, 'Lawton', '12:25:00'),
(55, 'Kalawaan → Escolta', 5, 11, 'Escolta', '12:30:00'),
(56, 'Kalawaan → Escolta', 6, 1, 'Kalawaan', '13:30:00'),
(57, 'Kalawaan → Escolta', 6, 2, 'San Joaquin', '13:40:00'),
(58, 'Kalawaan → Escolta', 6, 3, 'Guadalupe', '13:50:00'),
(59, 'Kalawaan → Escolta', 6, 4, 'Hulo', '13:55:00'),
(60, 'Kalawaan → Escolta', 6, 5, 'Valenzuela', '13:58:00'),
(61, 'Kalawaan → Escolta', 6, 6, 'Lambingan', '14:05:00'),
(62, 'Kalawaan → Escolta', 6, 7, 'Sta. Ana', '14:10:00'),
(63, 'Kalawaan → Escolta', 6, 8, 'PUP', '14:15:00'),
(64, 'Kalawaan → Escolta', 6, 9, 'Quinta', '14:23:00'),
(65, 'Kalawaan → Escolta', 6, 10, 'Lawton', '14:25:00'),
(66, 'Kalawaan → Escolta', 6, 11, 'Escolta', '14:30:00'),
(67, 'Kalawaan → Escolta', 7, 1, 'Kalawaan', '14:30:00'),
(68, 'Kalawaan → Escolta', 7, 2, 'San Joaquin', '14:40:00'),
(69, 'Kalawaan → Escolta', 7, 3, 'Guadalupe', '14:50:00'),
(70, 'Kalawaan → Escolta', 7, 4, 'Hulo', '14:55:00'),
(71, 'Kalawaan → Escolta', 7, 5, 'Valenzuela', '14:58:00'),
(72, 'Kalawaan → Escolta', 7, 6, 'Lambingan', '15:05:00'),
(73, 'Kalawaan → Escolta', 7, 7, 'Sta. Ana', '15:10:00'),
(74, 'Kalawaan → Escolta', 7, 8, 'PUP', '15:15:00'),
(75, 'Kalawaan → Escolta', 7, 9, 'Quinta', '15:23:00'),
(76, 'Kalawaan → Escolta', 7, 10, 'Lawton', '15:25:00'),
(77, 'Kalawaan → Escolta', 7, 11, 'Escolta', '15:30:00');

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
(1, 'Ferry 001', 14.559700, 121.066400, '2025-05-09 07:51:04', 2, 'active', NULL, '2025-05-10 14:29:11', 30, 27, 0, 5),
(2, 'Ferry 215', 14.553300, 121.080600, '2025-05-09 07:51:04', NULL, 'active', NULL, '2025-05-10 14:29:10', 30, 0, 0, 5),
(3, 'Ferry 212', 14.554200, 121.075000, '2025-05-07 16:57:25', NULL, 'active', NULL, '2025-05-10 14:29:08', 30, 24, 0, 5),
(4, 'Ferry 241', 14.553300, 121.080600, '2025-05-09 07:51:04', NULL, 'inactive', NULL, '2025-05-07 15:57:16', 30, 0, 0, 5),
(8, 'Ferry 4', 14.564400, 121.059200, '2025-05-07 16:57:25', NULL, 'inactive', 'RE', '2025-05-09 08:01:10', 23, 0, 0, 0),
(14, 'Ferry Alpha', 14.555000, 121.073100, '2025-05-09 07:51:04', 3.5, 'active', 'Pasig River Transport Inc.', '2025-05-09 01:23:47', 40, 25, 12.4, 1),
(15, 'Ferry Bravo', 14.562200, 121.061400, '2025-05-09 07:51:04', 2.8, 'inactive', 'Metro Ferries Co.', '2025-05-09 01:23:47', 35, 18, 10.2, 2),
(16, 'Ferry Charlie', NULL, NULL, '2025-05-09 01:23:47', 0, 'inactive', 'Pasig River Transport Inc.', '2025-05-09 01:23:47', 30, 0, 0, 3);

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
(20, 'PRFS001', 14.56625320, 121.04830080, '2025-05-09 06:40:36', '2025-05-09 06:40:36'),
(24, 'main', 14.56600294, 121.04829783, '2025-04-28 09:23:03', '2025-04-28 09:23:03'),
(25, 'PRFS003', 14.56617460, 121.04831460, '2025-05-01 08:54:04', '2025-05-01 08:54:04'),
(26, 'PRFS0012', 14.56619040, 121.04830180, '2025-04-28 09:26:29', '2025-04-28 09:26:29'),
(27, 'PRFS009', 14.55279910, 121.05037860, '2025-04-28 09:25:34', '2025-04-28 09:25:34'),
(28, 'PRFS004', 14.56600294, 121.04829783, '2025-04-28 09:34:32', '2025-04-28 09:34:32'),
(29, '1', 14.56600670, 121.04828760, '2025-05-01 08:56:02', '2025-05-01 08:56:02'),
(30, 'PRFS069', 14.55889880, 121.06134070, '2025-05-01 08:49:33', '2025-05-01 08:49:33'),
(31, 'PRFS096', 14.55889490, 121.06134310, '2025-05-01 08:49:58', '2025-05-01 08:49:58'),
(32, 'PRFS005', 14.55932890, 121.06049220, '2025-05-01 08:58:08', '2025-05-01 08:58:08'),
(33, '2', 14.56601700, 121.04831700, '2025-05-09 05:17:32', '2025-05-09 05:17:32'),
(34, '123', 14.56601700, 121.04831700, '2025-05-09 05:27:36', '2025-05-09 05:27:36'),
(35, 'YourFerryCode', 14.56601700, 121.04831700, '2025-05-09 05:27:32', '2025-05-09 05:27:32'),
(36, 'PRFS001262636', 14.56621080, 121.04831220, '2025-05-09 05:27:32', '2025-05-09 05:27:32'),
(37, 'PRFS001ysysy', 14.56622220, 121.04832300, '2025-05-09 06:40:45', '2025-05-09 06:40:45'),
(38, '21312', 14.56601700, 121.04831700, '2025-05-09 07:05:16', '2025-05-09 07:05:16'),
(39, '123123123', 14.56601700, 121.04831700, '2025-05-09 07:05:31', '2025-05-09 07:05:31');

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

--
-- Dumping data for table `ferry_logs`
--

INSERT INTO `ferry_logs` (`id`, `ferry_id`, `trip_date`, `passenger_count`, `speed`, `latitude`, `longitude`) VALUES
(1, 2, '2025-05-10 00:00:00', 31, 9.69, 14.593625, 121.008059),
(2, 2, '2025-05-29 00:00:00', 15, 11.86, 14.593349, 121.010157),
(3, 2, '2025-06-02 00:00:00', 34, 8.77, 14.595203, 121.008698),
(4, 2, '2025-05-25 00:00:00', 32, 13.55, 14.592075, 121.008719),
(5, 1, '2025-05-24 00:00:00', 28, 13.65, 14.587205, 121.004085),
(6, 2, '2025-05-28 00:00:00', 31, 6.15, 14.586259, 121.011836),
(7, 3, '2025-06-01 00:00:00', 28, 6.18, 14.589875, 121.009835),
(8, 1, '2025-05-30 00:00:00', 31, 6.89, 14.589908, 121.007864),
(9, 2, '2025-05-30 00:00:00', 32, 11.6, 14.591552, 121.011952),
(10, 1, '2025-05-17 00:00:00', 32, 5.25, 14.590079, 121.013649),
(11, 1, '2025-05-25 00:00:00', 28, 8.99, 14.593585, 121.009951),
(12, 3, '2025-05-28 00:00:00', 21, 7.45, 14.593909, 121.006186),
(13, 3, '2025-05-29 00:00:00', 24, 14.87, 14.588432, 121.006540),
(14, 3, '2025-05-24 00:00:00', 12, 10.8, 14.592585, 121.009530),
(15, 1, '2025-05-31 00:00:00', 11, 10.3, 14.590113, 121.008660),
(16, 1, '2025-04-11 00:00:00', 12, 8.49, 14.589912, 121.013079),
(17, 1, '2025-04-19 00:00:00', 20, 13.28, 14.594531, 121.011820),
(18, 2, '2025-04-18 00:00:00', 34, 12.08, 14.591833, 121.011911),
(19, 2, '2025-04-14 00:00:00', 26, 8.1, 14.591850, 121.013962),
(20, 1, '2025-04-15 00:00:00', 10, 12.03, 14.590411, 121.004960),
(21, 2, '2025-04-13 00:00:00', 10, 10.67, 14.594026, 121.007112),
(22, 3, '2025-04-13 00:00:00', 24, 10.23, 14.594502, 121.010815),
(23, 1, '2025-05-02 00:00:00', 25, 9.27, 14.588470, 121.013538),
(24, 1, '2025-04-09 00:00:00', 17, 12.02, 14.591873, 121.012309),
(25, 2, '2025-04-19 00:00:00', 14, 9.77, 14.594525, 121.012299),
(26, 2, '2025-04-25 00:00:00', 24, 9.74, 14.592388, 121.011718),
(27, 2, '2025-05-05 00:00:00', 13, 10.96, 14.591068, 121.011468),
(28, 3, '2025-04-14 00:00:00', 22, 5.14, 14.591695, 121.012065),
(29, 1, '2025-04-18 00:00:00', 10, 10.63, 14.593163, 121.012919),
(30, 3, '2025-04-17 00:00:00', 21, 11.15, 14.593289, 121.012001),
(31, 3, '2025-03-31 00:00:00', 32, 9.73, 14.592814, 121.013874),
(32, 2, '2025-04-03 00:00:00', 30, 11.59, 14.593964, 121.004042),
(33, 1, '2025-03-26 00:00:00', 30, 12.56, 14.588603, 121.004344),
(34, 3, '2025-03-19 00:00:00', 12, 13.86, 14.587621, 121.005540),
(35, 3, '2025-03-16 00:00:00', 33, 12.84, 14.587294, 121.006941),
(36, 2, '2025-03-11 00:00:00', 19, 9.11, 14.594535, 121.004357),
(37, 3, '2025-03-26 00:00:00', 11, 8.79, 14.592962, 121.007438),
(38, 1, '2025-03-26 00:00:00', 27, 6.85, 14.593978, 121.008344),
(39, 2, '2025-03-30 00:00:00', 25, 7.92, 14.592221, 121.006355),
(40, 3, '2025-03-17 00:00:00', 17, 5.11, 14.587208, 121.009708),
(41, 3, '2025-03-22 00:00:00', 16, 5.02, 14.588697, 121.007420),
(42, 2, '2025-04-03 00:00:00', 27, 5.45, 14.587352, 121.009400),
(43, 3, '2025-03-17 00:00:00', 19, 8.38, 14.591513, 121.011435),
(44, 1, '2025-03-10 00:00:00', 16, 14.81, 14.587598, 121.012547),
(45, 2, '2025-03-31 00:00:00', 26, 5.37, 14.588302, 121.004401),
(46, 2, '2025-02-23 00:00:00', 25, 12.96, 14.587314, 121.006679),
(47, 3, '2025-03-07 00:00:00', 29, 6.21, 14.588728, 121.004001),
(48, 3, '2025-02-14 00:00:00', 10, 7.79, 14.589895, 121.005114),
(49, 2, '2025-02-19 00:00:00', 31, 10.58, 14.587593, 121.005229),
(50, 1, '2025-02-12 00:00:00', 14, 13.72, 14.594706, 121.011368),
(51, 1, '2025-02-11 00:00:00', 23, 7.25, 14.591193, 121.013217),
(52, 2, '2025-02-10 00:00:00', 17, 14.53, 14.595184, 121.011321),
(53, 1, '2025-03-06 00:00:00', 33, 11.86, 14.592260, 121.004707),
(54, 1, '2025-02-22 00:00:00', 20, 10.08, 14.589378, 121.005646),
(55, 2, '2025-03-03 00:00:00', 18, 5.64, 14.588810, 121.006140),
(56, 2, '2025-02-15 00:00:00', 29, 9.45, 14.594695, 121.004131),
(57, 1, '2025-02-21 00:00:00', 31, 10.35, 14.587143, 121.013658),
(58, 2, '2025-02-22 00:00:00', 15, 9.44, 14.591954, 121.010435),
(59, 1, '2025-02-21 00:00:00', 30, 10.15, 14.586594, 121.011510),
(60, 2, '2025-02-25 00:00:00', 20, 7.48, 14.595545, 121.004279),
(61, 1, '2025-01-16 00:00:00', 26, 8.95, 14.586049, 121.012384),
(62, 2, '2025-01-13 00:00:00', 17, 9.86, 14.590744, 121.013125),
(63, 3, '2025-01-12 00:00:00', 19, 5.19, 14.595557, 121.011222),
(64, 2, '2025-01-29 00:00:00', 23, 14.87, 14.589480, 121.011797),
(65, 3, '2025-02-01 00:00:00', 12, 12.4, 14.589747, 121.010529),
(66, 3, '2025-01-12 00:00:00', 17, 7.32, 14.588850, 121.011283),
(67, 3, '2025-01-31 00:00:00', 19, 11.77, 14.588289, 121.005147),
(68, 1, '2025-02-02 00:00:00', 29, 11.54, 14.595159, 121.010188),
(69, 3, '2025-01-18 00:00:00', 18, 5.75, 14.589539, 121.009439),
(70, 2, '2025-01-27 00:00:00', 17, 11.16, 14.587243, 121.011739),
(71, 1, '2025-01-22 00:00:00', 17, 6.1, 14.591907, 121.010248),
(72, 3, '2025-01-18 00:00:00', 19, 6.85, 14.594214, 121.009520),
(73, 3, '2025-01-17 00:00:00', 15, 11.99, 14.593911, 121.012584),
(74, 1, '2025-02-03 00:00:00', 18, 11.51, 14.588302, 121.005970),
(75, 3, '2025-01-17 00:00:00', 22, 14.47, 14.587813, 121.004666),
(76, 3, '2024-12-31 00:00:00', 18, 10.63, 14.593364, 121.013923),
(77, 3, '2024-12-30 00:00:00', 26, 14.81, 14.595035, 121.009747),
(78, 1, '2024-12-13 00:00:00', 34, 10.46, 14.594376, 121.009516),
(79, 2, '2024-12-15 00:00:00', 12, 13.66, 14.595840, 121.007224),
(80, 1, '2024-12-27 00:00:00', 27, 9.14, 14.586233, 121.012743),
(81, 3, '2024-12-17 00:00:00', 23, 14.76, 14.588975, 121.009603),
(82, 3, '2025-01-03 00:00:00', 24, 8.65, 14.586534, 121.005705),
(83, 3, '2024-12-28 00:00:00', 27, 10.46, 14.592876, 121.012013),
(84, 1, '2025-01-04 00:00:00', 28, 12.64, 14.591959, 121.010873),
(85, 1, '2024-12-27 00:00:00', 34, 7.61, 14.590223, 121.007283),
(86, 3, '2024-12-19 00:00:00', 17, 14.21, 14.592539, 121.009063),
(87, 1, '2024-12-24 00:00:00', 33, 12.23, 14.593841, 121.011531),
(88, 3, '2024-12-20 00:00:00', 29, 10.54, 14.589872, 121.006724),
(89, 1, '2024-12-14 00:00:00', 18, 5.57, 14.589176, 121.008175),
(90, 2, '2024-12-12 00:00:00', 27, 7.42, 14.587066, 121.012072),
(91, 2, '2025-03-28 08:48:19', 23, 11.39, 14.581579, 121.050954),
(92, 1, '2025-03-29 15:21:32', 10, 10.78, 14.586205, 121.057122),
(93, 2, '2025-02-13 17:01:07', 19, 11.06, 14.584987, 121.059651),
(94, 1, '2025-04-01 18:33:01', 22, 14.32, 14.587759, 121.057200),
(95, 1, '2025-01-25 04:34:54', 17, 11.06, 14.588331, 121.056358),
(96, 2, '2025-02-28 09:59:56', 22, 14.79, 14.581635, 121.054221),
(97, 2, '2025-04-11 07:25:10', 10, 11.76, 14.583348, 121.057938),
(98, 2, '2025-02-22 07:25:10', 14, 11.17, 14.583001, 121.059164),
(99, 2, '2025-03-21 06:01:27', 15, 13.81, 14.583391, 121.057924),
(100, 2, '2025-02-11 21:38:00', 19, 13.73, 14.587163, 121.057140),
(101, 1, '2025-04-26 13:18:55', 28, 14.51, 14.581069, 121.052806),
(102, 1, '2025-03-10 14:30:22', 16, 14.45, 14.587780, 121.056631),
(103, 2, '2025-04-21 15:37:45', 20, 13.23, 14.581931, 121.053528),
(104, 1, '2025-04-14 18:35:00', 15, 10.29, 14.584186, 121.059480),
(105, 1, '2025-03-11 10:32:42', 19, 15.92, 14.585534, 121.058051),
(106, 1, '2025-02-19 23:03:04', 21, 15.77, 14.580989, 121.056119),
(107, 2, '2025-04-28 14:28:50', 11, 13.59, 14.587483, 121.059454),
(108, 1, '2025-03-10 10:09:47', 18, 14.25, 14.583516, 121.056351),
(109, 1, '2025-03-25 02:56:50', 20, 11.5, 14.586783, 121.056420),
(110, 1, '2025-04-25 05:23:56', 15, 14.18, 14.586612, 121.052153),
(111, 1, '2025-04-09 19:37:33', 23, 14.2, 14.586066, 121.059338),
(112, 2, '2025-02-23 16:20:36', 11, 11.76, 14.582650, 121.054449),
(113, 1, '2025-04-08 18:39:39', 19, 15.44, 14.581900, 121.052300),
(114, 2, '2025-01-26 07:18:16', 28, 13.39, 14.581393, 121.050006),
(115, 2, '2025-04-21 20:42:45', 21, 10.79, 14.580262, 121.057358),
(116, 2, '2025-04-06 04:11:22', 20, 15.47, 14.580974, 121.057518),
(117, 1, '2025-01-10 23:44:01', 24, 13.54, 14.588156, 121.053081),
(118, 1, '2025-03-07 10:38:08', 22, 13.45, 14.581330, 121.059394),
(119, 1, '2025-03-22 11:10:15', 16, 10.97, 14.588733, 121.058817),
(120, 2, '2025-02-05 02:52:57', 24, 11.12, 14.588042, 121.054616);

-- --------------------------------------------------------

--
-- Table structure for table `ferry_routes`
--

CREATE TABLE `ferry_routes` (
  `id` int(11) NOT NULL,
  `route_name` varchar(100) DEFAULT NULL,
  `origin` varchar(50) DEFAULT NULL,
  `destination` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ferry_routes`
--

INSERT INTO `ferry_routes` (`id`, `route_name`, `origin`, `destination`, `created_at`) VALUES
(1, 'Pinagbuhatan → Escolta', 'Pinagbuhatan', 'Escolta', '2025-05-05 12:45:37'),
(2, 'Escolta → Kalawaan', 'Escolta', 'Kalawaan', '2025-05-05 12:45:37');

-- --------------------------------------------------------

--
-- Table structure for table `ferry_stops`
--

CREATE TABLE `ferry_stops` (
  `id` int(11) NOT NULL,
  `route_id` int(11) DEFAULT NULL,
  `stop_name` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ferry_stops`
--

INSERT INTO `ferry_stops` (`id`, `route_id`, `stop_name`) VALUES
(1, 1, 'Pinagbuhatan'),
(2, 1, 'Kalawaan'),
(3, 1, 'San Joaquin'),
(4, 1, 'Guadalupe'),
(5, 1, 'Hulo'),
(6, 1, 'Valenzuela'),
(7, 1, 'Lambingan'),
(8, 1, 'Sta. Ana'),
(9, 1, 'PUP'),
(10, 1, 'Quinta'),
(11, 1, 'Lawton'),
(12, 1, 'Escolta'),
(13, 2, 'Escolta'),
(14, 2, 'Lawton'),
(15, 2, 'Quinta'),
(16, 2, 'PUP'),
(17, 2, 'Sta. Ana'),
(18, 2, 'Lambingan'),
(19, 2, 'Valenzuela'),
(20, 2, 'Hulo'),
(21, 2, 'Guadalupe'),
(22, 2, 'San Joaquin'),
(23, 2, 'Kalawaan');

-- --------------------------------------------------------

--
-- Table structure for table `login_attempts`
--

CREATE TABLE `login_attempts` (
  `ip_address` varchar(45) NOT NULL,
  `attempts` int(11) NOT NULL DEFAULT 1,
  `last_attempt` timestamp NOT NULL DEFAULT current_timestamp()
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
(1, 'superadmin', 'john.doe@example.com', 'password123', 'admin', 'John', 'Michael', 'Doe', 'Manager', 1, '2025-05-02 06:56:41', '2025-05-02 06:57:17', 'Pic1.png'),
(2, 'admin1', 'admin1@example.com', '123', 'super_admin', 'Lara', 'M.', 'Santos', 'System Admin', 1, '2025-05-09 01:23:55', '2025-05-09 21:03:56', 'default.png'),
(5, 'test1', '', '$2y$10$fSDE2lj0C1ec5QSw87fCN.ZPgmCYp2kskqWr/ts5/jJie3TGvIbZe', 'admin', 'howie', NULL, 'severino', NULL, 1, '2025-05-09 21:10:55', '2025-05-09 21:10:55', 'default.png'),
(7, 'test2', '12312ADAW@HOTMAIL.com', '$2y$10$JzsU67ADlm86qbkt6qLtmu8ER9zxbl//a3hlwvajoD6seN1hXzFd2', 'employee', 'WOIHO', NULL, '13', NULL, 1, '2025-05-09 21:23:15', '2025-05-09 21:23:15', 'default.png'),
(8, '@test1234567', 'severinokenji@gmail.com', '$2y$10$/Dxvo3zsE/HdvyjHrvVlpe8GaXnIfIk3OPfv/y68cHorstuXN/poi', 'employee', 'Howie', NULL, 'sevr', NULL, 1, '2025-05-10 10:27:40', '2025-05-10 10:27:40', 'default.png'),
(9, 'superadmin2', '1severinokenji@gmail.com', '$2y$10$nu/D5duFuoAFh6Jm4DlpJuP9dsTY3kYZL/YLPbKOIBylCUaJHZsq2', 'admin', 'howie2', NULL, 'severino', NULL, 1, '2025-05-10 11:04:36', '2025-05-10 11:04:36', 'default.png');

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

-- --------------------------------------------------------

--
-- Table structure for table `upstream_schedules`
--

CREATE TABLE `upstream_schedules` (
  `id` int(11) NOT NULL,
  `route_name` varchar(255) NOT NULL DEFAULT 'Escolta → Kalawaan',
  `row_id` int(11) NOT NULL,
  `col_id` int(11) NOT NULL,
  `station_name` varchar(255) NOT NULL,
  `schedule_time` time NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `upstream_schedules`
--

INSERT INTO `upstream_schedules` (`id`, `route_name`, `row_id`, `col_id`, `station_name`, `schedule_time`) VALUES
(375, 'Escolta → Kalawaan', 1, 1, 'Escolta', '08:15:00'),
(376, 'Escolta → Kalawaan', 1, 2, 'Lawton', '08:20:00'),
(377, 'Escolta → Kalawaan', 1, 3, 'Quinta', '08:22:00'),
(378, 'Escolta → Kalawaan', 1, 4, 'PUP', '08:35:00'),
(379, 'Escolta → Kalawaan', 1, 5, 'Sta. Ana', '08:41:00'),
(380, 'Escolta → Kalawaan', 1, 6, 'Lambingan', '09:00:00'),
(381, 'Escolta → Kalawaan', 1, 7, 'Valenzuela', '09:10:00'),
(382, 'Escolta → Kalawaan', 1, 8, 'Hulo', '09:15:00'),
(383, 'Escolta → Kalawaan', 1, 9, 'Guadalupe', '09:20:00'),
(384, 'Escolta → Kalawaan', 1, 10, 'San Joaquin', '09:35:00'),
(385, 'Escolta → Kalawaan', 1, 11, 'Kalawaan', '09:30:00'),
(386, 'Escolta → Kalawaan', 2, 1, 'Escolta', '09:00:00'),
(387, 'Escolta → Kalawaan', 2, 2, 'Lawton', '09:05:00'),
(388, 'Escolta → Kalawaan', 2, 3, 'Quinta', '09:07:00'),
(389, 'Escolta → Kalawaan', 2, 4, 'PUP', '09:26:00'),
(390, 'Escolta → Kalawaan', 2, 5, 'Sta. Ana', '09:39:00'),
(391, 'Escolta → Kalawaan', 2, 6, 'Lambingan', '09:42:00'),
(392, 'Escolta → Kalawaan', 2, 7, 'Valenzuela', '09:50:00'),
(393, 'Escolta → Kalawaan', 2, 8, 'Hulo', '09:56:00'),
(394, 'Escolta → Kalawaan', 2, 9, 'Guadalupe', '10:00:00'),
(395, 'Escolta → Kalawaan', 2, 10, 'San Joaquin', '10:15:00'),
(396, 'Escolta → Kalawaan', 2, 11, 'Kalawaan', '10:20:00'),
(397, 'Escolta → Kalawaan', 3, 1, 'Escolta', '10:00:00'),
(398, 'Escolta → Kalawaan', 3, 2, 'Lawton', '10:05:00'),
(399, 'Escolta → Kalawaan', 3, 3, 'Quinta', '10:07:00'),
(400, 'Escolta → Kalawaan', 3, 4, 'PUP', '10:22:00'),
(401, 'Escolta → Kalawaan', 3, 5, 'Sta. Ana', '10:31:00'),
(402, 'Escolta → Kalawaan', 3, 6, 'Lambingan', '10:34:00'),
(403, 'Escolta → Kalawaan', 3, 7, 'Valenzuela', '10:42:00'),
(404, 'Escolta → Kalawaan', 3, 8, 'Hulo', '10:48:00'),
(405, 'Escolta → Kalawaan', 3, 9, 'Guadalupe', '10:52:00'),
(406, 'Escolta → Kalawaan', 3, 10, 'San Joaquin', '11:07:00'),
(407, 'Escolta → Kalawaan', 3, 11, 'Kalawaan', '11:12:00'),
(408, 'Escolta → Kalawaan', 4, 1, 'Escolta', '11:00:00'),
(409, 'Escolta → Kalawaan', 4, 2, 'Lawton', '11:05:00'),
(410, 'Escolta → Kalawaan', 4, 3, 'Quinta', '11:07:00'),
(411, 'Escolta → Kalawaan', 4, 4, 'PUP', '11:26:00'),
(412, 'Escolta → Kalawaan', 4, 5, 'Sta. Ana', '11:35:00'),
(413, 'Escolta → Kalawaan', 4, 6, 'Lambingan', '11:38:00'),
(414, 'Escolta → Kalawaan', 4, 7, 'Valenzuela', '11:46:00'),
(415, 'Escolta → Kalawaan', 4, 8, 'Hulo', '11:49:00'),
(416, 'Escolta → Kalawaan', 4, 9, 'Guadalupe', '11:55:00'),
(417, 'Escolta → Kalawaan', 4, 10, 'San Joaquin', '12:10:00'),
(418, 'Escolta → Kalawaan', 4, 11, 'Kalawaan', '12:15:00'),
(419, 'Escolta → Kalawaan', 5, 1, 'Escolta', '12:00:00'),
(420, 'Escolta → Kalawaan', 5, 2, 'Lawton', '12:05:00'),
(421, 'Escolta → Kalawaan', 5, 3, 'Quinta', '12:07:00'),
(422, 'Escolta → Kalawaan', 5, 4, 'PUP', '12:30:00'),
(423, 'Escolta → Kalawaan', 5, 5, 'Sta. Ana', '12:40:00'),
(424, 'Escolta → Kalawaan', 5, 6, 'Lambingan', '12:44:00'),
(425, 'Escolta → Kalawaan', 5, 7, 'Valenzuela', '12:52:00'),
(426, 'Escolta → Kalawaan', 5, 8, 'Hulo', '12:56:00'),
(427, 'Escolta → Kalawaan', 5, 9, 'Guadalupe', '13:00:00'),
(428, 'Escolta → Kalawaan', 5, 10, 'San Joaquin', '13:15:00'),
(429, 'Escolta → Kalawaan', 5, 11, 'Kalawaan', '13:20:00'),
(430, 'Escolta → Kalawaan', 6, 1, 'Escolta', '13:00:00'),
(431, 'Escolta → Kalawaan', 6, 2, 'Lawton', '13:05:00'),
(432, 'Escolta → Kalawaan', 6, 3, 'Quinta', '13:07:00'),
(433, 'Escolta → Kalawaan', 6, 4, 'PUP', '13:30:00'),
(434, 'Escolta → Kalawaan', 6, 5, 'Sta. Ana', '13:39:00'),
(435, 'Escolta → Kalawaan', 6, 6, 'Lambingan', '13:43:00'),
(436, 'Escolta → Kalawaan', 6, 7, 'Valenzuela', '13:50:00'),
(437, 'Escolta → Kalawaan', 6, 8, 'Hulo', '15:56:00'),
(438, 'Escolta → Kalawaan', 6, 9, 'Guadalupe', '14:00:00'),
(439, 'Escolta → Kalawaan', 6, 10, 'San Joaquin', '14:15:00'),
(440, 'Escolta → Kalawaan', 6, 11, 'Kalawaan', '14:20:00'),
(441, 'Escolta → Kalawaan', 7, 1, 'Escolta', '14:00:00'),
(442, 'Escolta → Kalawaan', 7, 2, 'Lawton', '14:05:00'),
(443, 'Escolta → Kalawaan', 7, 3, 'Quinta', '14:07:00'),
(444, 'Escolta → Kalawaan', 7, 4, 'PUP', '14:23:00'),
(445, 'Escolta → Kalawaan', 7, 5, 'Sta. Ana', '14:35:00'),
(446, 'Escolta → Kalawaan', 7, 6, 'Lambingan', '14:36:00'),
(447, 'Escolta → Kalawaan', 7, 7, 'Valenzuela', '14:43:00'),
(448, 'Escolta → Kalawaan', 7, 8, 'Hulo', '14:47:00'),
(449, 'Escolta → Kalawaan', 7, 9, 'Guadalupe', '14:52:00'),
(450, 'Escolta → Kalawaan', 7, 10, 'San Joaquin', '15:07:00'),
(451, 'Escolta → Kalawaan', 7, 11, 'Kalawaan', '15:15:00');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `announcements`
--
ALTER TABLE `announcements`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `downstream_schedules`
--
ALTER TABLE `downstream_schedules`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `route_name` (`route_name`,`row_id`,`col_id`);

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
-- Indexes for table `ferry_routes`
--
ALTER TABLE `ferry_routes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ferry_stops`
--
ALTER TABLE `ferry_stops`
  ADD PRIMARY KEY (`id`),
  ADD KEY `route_id` (`route_id`);

--
-- Indexes for table `login_attempts`
--
ALTER TABLE `login_attempts`
  ADD PRIMARY KEY (`ip_address`);

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
-- Indexes for table `upstream_schedules`
--
ALTER TABLE `upstream_schedules`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `route_name` (`route_name`,`row_id`,`col_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `announcements`
--
ALTER TABLE `announcements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `downstream_schedules`
--
ALTER TABLE `downstream_schedules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=78;

--
-- AUTO_INCREMENT for table `ferries`
--
ALTER TABLE `ferries`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `ferry_locations`
--
ALTER TABLE `ferry_locations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT for table `ferry_logs`
--
ALTER TABLE `ferry_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=122;

--
-- AUTO_INCREMENT for table `ferry_routes`
--
ALTER TABLE `ferry_routes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `ferry_stops`
--
ALTER TABLE `ferry_stops`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `passenger_id_pass`
--
ALTER TABLE `passenger_id_pass`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `staff_users`
--
ALTER TABLE `staff_users`
  MODIFY `staff_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `tickets`
--
ALTER TABLE `tickets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `upstream_schedules`
--
ALTER TABLE `upstream_schedules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=452;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `ferry_logs`
--
ALTER TABLE `ferry_logs`
  ADD CONSTRAINT `ferry_logs_ibfk_1` FOREIGN KEY (`ferry_id`) REFERENCES `ferries` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `ferry_stops`
--
ALTER TABLE `ferry_stops`
  ADD CONSTRAINT `ferry_stops_ibfk_1` FOREIGN KEY (`route_id`) REFERENCES `ferry_routes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tickets`
--
ALTER TABLE `tickets`
  ADD CONSTRAINT `tickets_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `staff_users` (`staff_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
