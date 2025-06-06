-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 17, 2025 at 12:47 AM
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
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `display_from` date NOT NULL,
  `display_duration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `announcements`
--

INSERT INTO `announcements` (`id`, `title`, `message`, `created_at`, `display_from`, `display_duration`) VALUES
(4, 'Ferry Maintenance', 'Ferry 1 will be under maintenance', '2025-05-11 14:17:14', '2025-05-15', 3),
(5, 'Service Interruption', 'No service at Guadalupe station due to repairs', '2025-05-11 14:17:14', '2025-05-20', 5),
(6, 'Special Schedule', 'Holiday schedule in effect for Independence Day', '2025-05-11 14:17:14', '2025-06-12', 1),
(7, 'New Ferry Service', 'Additional ferry service added to Escolta route', '2025-05-11 14:17:14', '2025-05-10', 14),
(11, 'Free Rides', 'Free ride for the month of july', '2025-05-14 03:36:50', '2025-05-13', 14);

-- --------------------------------------------------------

--
-- Table structure for table `boat_maintenance`
--

CREATE TABLE `boat_maintenance` (
  `id` int(11) NOT NULL,
  `ferry_id` int(11) NOT NULL,
  `maintenance_date` date NOT NULL,
  `maintenance_type` varchar(100) NOT NULL,
  `maintenance_type_detail` varchar(100) DEFAULT NULL,
  `performed_by` varchar(100) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `parts_replaced` text DEFAULT NULL,
  `cost` decimal(10,2) DEFAULT 0.00,
  `status` enum('Scheduled','In Progress','Completed','Skipped') DEFAULT 'Scheduled',
  `next_due_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `boat_maintenance`
--

INSERT INTO `boat_maintenance` (`id`, `ferry_id`, `maintenance_date`, `maintenance_type`, `maintenance_type_detail`, `performed_by`, `notes`, `parts_replaced`, `cost`, `status`, `next_due_date`, `created_at`) VALUES
(1, 1, '2025-05-01', 'Engine Check', NULL, 'Juan Dela Cruz', 'Routine engine inspection. All clear.', NULL, 0.00, 'Scheduled', '2025-08-01', '2025-05-11 04:30:29'),
(2, 2, '2025-04-20', 'Hull Cleaning', NULL, 'Pedro Santos', 'Cleaned algae buildup. Improved efficiency.', NULL, 0.00, 'Scheduled', '2025-07-20', '2025-05-11 04:30:29'),
(3, 1, '2025-01-15', 'Propeller Maintenance', NULL, 'MarineTech Co.', 'Replaced worn-out propeller blade.', NULL, 0.00, 'Scheduled', '2025-06-15', '2025-05-11 04:30:29'),
(4, 1, '2024-04-25', 'Engine Overhaul', 'Engine Oil Change', NULL, 'Routine monthly checkup', 'Oil filter, fuel filter', 15000.00, 'Completed', NULL, '2025-05-11 22:42:32');

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
(7, 'Kalawaan → Escolta', 1, 7, 'Sta. Ana', '06:40:00'),
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
  `ferry_code` varchar(20) DEFAULT NULL,
  `latitude` decimal(9,6) DEFAULT NULL,
  `longitude` decimal(9,6) DEFAULT NULL,
  `last_updated` timestamp NOT NULL DEFAULT current_timestamp(),
  `active_time` float DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'inactive',
  `operator` varchar(100) DEFAULT NULL,
  `ferry_type` enum('passenger','cargo','mixed') DEFAULT 'passenger',
  `status_changed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `max_capacity` int(11) NOT NULL DEFAULT 30,
  `cargo_capacity` int(11) DEFAULT 0,
  `current_capacity` int(11) NOT NULL DEFAULT 0,
  `length` decimal(6,2) DEFAULT NULL,
  `width` decimal(6,2) DEFAULT NULL,
  `speed` float DEFAULT 0,
  `max_speed` decimal(6,2) DEFAULT NULL,
  `fuel_type` enum('diesel','gasoline','electric','hybrid','other') DEFAULT 'diesel',
  `engine_power` int(11) DEFAULT NULL,
  `engine_count` int(11) DEFAULT 1,
  `manufacturer` varchar(100) DEFAULT NULL,
  `model` varchar(100) DEFAULT NULL,
  `year_built` int(11) DEFAULT NULL,
  `hull_material` enum('steel','aluminum','fiberglass','wood','composite','other') DEFAULT NULL,
  `registration_number` varchar(50) DEFAULT NULL,
  `registration_date` date DEFAULT NULL,
  `last_inspection_date` date DEFAULT NULL,
  `next_inspection_date` date DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `registration_document_path` varchar(255) DEFAULT NULL,
  `route_index` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ferries`
--

INSERT INTO `ferries` (`id`, `name`, `ferry_code`, `latitude`, `longitude`, `last_updated`, `active_time`, `status`, `operator`, `ferry_type`, `status_changed_at`, `max_capacity`, `cargo_capacity`, `current_capacity`, `length`, `width`, `speed`, `max_speed`, `fuel_type`, `engine_power`, `engine_count`, `manufacturer`, `model`, `year_built`, `hull_material`, `registration_number`, `registration_date`, `last_inspection_date`, `next_inspection_date`, `notes`, `image_path`, `registration_document_path`, `route_index`) VALUES
(1, 'Ferry 001', 'PX-001', 14.566227, 121.048318, '2025-05-15 09:39:27', 4401, 'active', NULL, 'passenger', '2025-05-12 03:08:35', 30, 0, 27, 35.50, 7.20, 0.109525, 22.50, 'diesel', 1200, 2, 'PhilShip Co.', 'PS-2023', 2020, 'steel', 'REG-998877', '2021-06-15', '2024-05-01', '2025-05-01', 'Underwent full repainting in April 2024.', 'uploads/ferries/px001.jpg', 'uploads/docs/px001_reg.pdf', 5),
(2, 'Ferry 215', NULL, 14.553300, 121.080600, '2025-05-15 09:39:27', 4399, 'active', NULL, 'passenger', '2025-05-12 03:09:07', 30, 0, 0, NULL, NULL, 0, NULL, 'diesel', NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 5),
(3, 'Ferry 212', NULL, 14.554200, 121.075000, '2025-05-07 16:57:25', NULL, 'inactive', NULL, 'passenger', '2025-05-10 15:54:00', 30, 0, 24, NULL, NULL, 0, NULL, 'diesel', NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 5),
(4, 'Ferry 241', NULL, 14.553300, 121.080600, '2025-05-09 07:51:04', NULL, 'inactive', NULL, 'passenger', '2025-05-11 11:30:01', 30, 0, 0, NULL, NULL, 0, NULL, 'diesel', NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 5),
(8, 'Ferry 4', NULL, 14.564400, 121.059200, '2025-05-15 09:39:27', 6732, 'active', 'RE', 'passenger', '2025-05-10 23:43:20', 23, 0, 0, NULL, NULL, 0, NULL, 'diesel', NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0),
(13, 'Oplan Alpha', 'OPLANA-001', 14.566017, 121.048317, '2025-05-13 11:16:04', NULL, 'inactive', 'OplanA', 'passenger', '2025-05-13 03:25:57', 30, 0, 0, NULL, NULL, 0, NULL, 'diesel', NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0),
(14, 'Ferry Alpha', NULL, 14.555000, 121.073100, '2025-05-15 09:39:27', 4402, 'active', 'Pasig River Transport Inc.', 'passenger', '2025-05-09 01:23:47', 40, 0, 25, NULL, NULL, 12.4, NULL, 'diesel', NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1),
(15, 'Ferry Bravo', NULL, 14.562200, 121.061400, '2025-05-15 09:39:27', 4401, 'active', 'Metro Ferries Co.', 'passenger', '2025-05-10 23:43:30', 35, 0, 18, NULL, NULL, 10.2, NULL, 'diesel', NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 2),
(16, 'Ferry Charlie', NULL, NULL, NULL, '2025-05-15 09:39:27', 4786, 'active', 'Pasig River Transport Inc.', 'passenger', '2025-05-11 13:21:22', 30, 0, 0, NULL, NULL, 0, NULL, 'diesel', NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 3),
(17, 'Ferry A', NULL, NULL, NULL, '2025-05-15 09:39:27', 1817, 'active', NULL, 'passenger', '2025-05-12 13:09:52', 30, 0, 0, NULL, NULL, 0, NULL, 'diesel', NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0),
(18, 'Ferry B', NULL, NULL, NULL, '2025-05-15 09:39:27', 1817, 'active', NULL, 'passenger', '2025-05-12 02:54:07', 30, 0, 0, NULL, NULL, 0, NULL, 'diesel', NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0),
(19, 'Ferry C', NULL, NULL, NULL, '2025-05-11 17:11:42', NULL, 'inactive', NULL, 'passenger', '2025-05-11 17:11:42', 30, 0, 0, NULL, NULL, 0, NULL, 'diesel', NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0),
(20, 'Ferry Alpha', 'PRFS001', NULL, NULL, '2025-05-12 09:36:09', NULL, 'inactive', 'John Doe', 'passenger', '2025-05-12 09:36:09', 100, 0, 0, NULL, NULL, 0, NULL, 'diesel', NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0),
(201, 'Ferry A', 'F001', 14.589600, 121.065200, '2025-05-15 09:39:27', 1769, 'active', NULL, 'passenger', '2025-05-12 16:27:12', 30, 0, 0, NULL, NULL, 0, NULL, 'diesel', 500, 2, 'Manufacturer A', 'Model A', 2015, 'steel', '123456', '2015-01-01', '2024-01-01', '2025-01-01', 'A new ferry model with excellent speed and fuel efficiency.', '/images/ferry_a.jpg', '/docs/ferry_a_registration.pdf', 1),
(202, 'Ferry B', 'F002', 14.590300, 121.063800, '2025-05-15 09:39:27', 1769, 'active', NULL, 'passenger', '2025-05-12 16:27:12', 30, 0, 0, NULL, NULL, 0, NULL, 'diesel', 600, 2, 'Manufacturer B', 'Model B', 2016, 'aluminum', '123457', '2016-01-01', '2024-02-01', '2025-02-01', 'Ferry B is known for its spacious cargo capacity.', '/images/ferry_b.jpg', '/docs/ferry_b_registration.pdf', 2),
(203, 'Ferry C', 'F003', 14.591000, 121.062500, '2025-05-15 09:39:27', 1769, 'active', NULL, 'passenger', '2025-05-12 16:27:12', 30, 0, 0, NULL, NULL, 0, NULL, 'diesel', 700, 2, 'Manufacturer C', 'Model C', 2017, 'fiberglass', '123458', '2017-01-01', '2024-03-01', '2025-03-01', 'Fast and efficient, ideal for passenger travel.', '/images/ferry_c.jpg', '/docs/ferry_c_registration.pdf', 3),
(204, 'Ferry D', 'F004', 14.592100, 121.061100, '2025-05-15 09:39:27', 1769, 'active', NULL, 'passenger', '2025-05-12 16:27:12', 30, 0, 0, NULL, NULL, 0, NULL, 'gasoline', 650, 2, 'Manufacturer D', 'Model D', 2018, 'composite', '123459', '2018-01-01', '2024-04-01', '2025-04-01', 'This ferry is designed for both passenger and cargo use.', '/images/ferry_d.jpg', '/docs/ferry_d_registration.pdf', 4),
(205, 'Ferry E', 'F005', 14.593500, 121.059800, '2025-05-15 09:39:27', 1769, 'active', NULL, 'passenger', '2025-05-12 16:27:12', 30, 0, 0, NULL, NULL, 0, NULL, 'electric', 550, 2, 'Manufacturer E', 'Model E', 2019, 'wood', '123460', '2019-01-01', '2024-05-01', '2025-05-01', 'An eco-friendly ferry with electric propulsion.', '/images/ferry_e.jpg', '/docs/ferry_e_registration.pdf', 5),
(206, 'Oplan Ferry A', 'OPLANA-001', 14.589600, 121.036000, '2025-05-13 11:17:01', NULL, 'inactive', 'OplanA', 'passenger', '2025-05-13 03:24:27', 60, 0, 0, 25.00, 5.00, 0, 25.00, 'diesel', 500, 2, 'Local Shipyard', 'Model-A', 2023, 'steel', 'REG-001-OPLANA', '2025-05-13', '2025-05-13', '2026-05-13', 'Oplan Ferry assigned to Operator A.', 'oplan_a.png', NULL, 0);

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
-- Table structure for table `ferry_crew`
--

CREATE TABLE `ferry_crew` (
  `id` int(11) NOT NULL,
  `ferry_id` int(11) NOT NULL,
  `staff_id` int(11) NOT NULL,
  `role` enum('captain','engineer','crew','attendant') NOT NULL,
  `assigned_date` date NOT NULL,
  `end_date` date DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ferry_crew`
--

INSERT INTO `ferry_crew` (`id`, `ferry_id`, `staff_id`, `role`, `assigned_date`, `end_date`, `is_active`) VALUES
(4, 1, 1, 'captain', '2025-05-12', NULL, 1),
(5, 1, 104, 'captain', '2025-05-12', NULL, 1),
(28, 13, 206, 'captain', '2025-05-13', NULL, 1);

-- --------------------------------------------------------

--
-- Table structure for table `ferry_fuel_logs`
--

CREATE TABLE `ferry_fuel_logs` (
  `id` int(11) NOT NULL,
  `ferry_id` int(11) NOT NULL,
  `refuel_date` datetime NOT NULL,
  `fuel_amount` decimal(10,2) NOT NULL,
  `fuel_type` enum('diesel','gasoline','other') NOT NULL,
  `cost_per_unit` decimal(10,2) NOT NULL,
  `total_cost` decimal(10,2) NOT NULL,
  `odometer_reading` int(11) DEFAULT NULL,
  `recorded_by` int(11) DEFAULT NULL,
  `notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ferry_fuel_logs`
--

INSERT INTO `ferry_fuel_logs` (`id`, `ferry_id`, `refuel_date`, `fuel_amount`, `fuel_type`, `cost_per_unit`, `total_cost`, `odometer_reading`, `recorded_by`, `notes`) VALUES
(2, 1, '2024-05-05 08:30:00', 200.00, 'diesel', 55.00, 11000.00, 103450, NULL, 'Initial fuel log entry');

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
(1, 'PX-001', 14.56622670, 121.04831780, '2025-05-13 11:28:32', '2025-05-13 11:28:32'),
(11, 'OPLANA-001', 14.58960000, 121.03600000, '2025-05-13 03:24:37', '2025-05-13 03:24:37'),
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
(39, '123123123', 14.56601700, 121.04831700, '2025-05-09 07:05:31', '2025-05-09 07:05:31'),
(40, 'PRFS04', 14.56619690, 121.04829800, '2025-05-12 11:27:17', '2025-05-12 11:27:17'),
(41, 'howie', 14.56601700, 121.04831700, '2025-05-12 13:43:21', '2025-05-12 13:43:21'),
(42, 'PRFS010', 14.53330650, 121.06087510, '2025-05-12 13:45:06', '2025-05-12 13:45:06'),
(201, '', 14.58960000, 121.06520000, '2025-05-12 16:29:43', '2025-05-12 16:29:43'),
(202, '', 14.59030000, 121.06380000, '2025-05-12 16:29:43', '2025-05-12 16:29:43'),
(203, '', 14.59100000, 121.06250000, '2025-05-12 16:29:43', '2025-05-12 16:29:43'),
(204, '', 14.59210000, 121.06110000, '2025-05-12 16:29:43', '2025-05-12 16:29:43'),
(205, '', 14.59350000, 121.05980000, '2025-05-12 16:29:43', '2025-05-12 16:29:43');

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
(120, 2, '2025-02-05 02:52:57', 24, 11.12, 14.588042, 121.054616),
(121, 1, '2025-05-12 19:04:26', 0, 0, 14.670000, 121.050000),
(122, 1, '2025-05-12 19:04:26', 0, 0, 14.670000, 121.050000),
(123, 1, '2025-05-12 19:04:26', 0, 0, 14.670000, 121.050000),
(124, 1, '2025-05-12 19:04:27', 0, 0, 14.566017, 121.048317),
(125, 1, '2025-05-12 19:04:27', 0, 0, 14.566017, 121.048317),
(126, 1, '2025-05-12 19:04:27', 0, 0, 14.566017, 121.048317),
(127, 1, '2025-05-12 19:26:32', 0, 0, 14.670000, 121.050000),
(128, 1, '2025-05-12 19:26:33', 0, 0, 14.566017, 121.048317),
(129, 1, '2025-05-12 19:27:10', 0, 0, 14.670000, 121.050000),
(130, 1, '2025-05-12 19:27:11', 0, 0, 14.566017, 121.048317),
(131, 1, '2025-05-12 22:43:08', 0, 0, 14.670000, 121.050000),
(132, 1, '2025-05-12 22:43:08', 0, 0, 14.566017, 121.048317),
(133, 1, '2025-05-12 22:44:26', 0, 0, 14.566017, 121.048317),
(134, 1, '2025-05-12 22:44:26', 0, 0, 14.566017, 121.048317),
(135, 1, '2025-05-12 22:46:32', 0, 0, 14.566268, 121.048283),
(136, 1, '2025-05-12 22:46:37', 0, 0, 14.566268, 121.048283),
(137, 1, '2025-05-12 22:46:44', 0, 0.762032, 14.566214, 121.048295),
(138, 1, '2025-05-12 22:46:50', 0, 0.0414648, 14.566213, 121.048294),
(139, 1, '2025-05-13 11:32:31', 0, 0, 14.214175, 120.968736),
(140, 1, '2025-05-13 11:32:33', 0, 0, 14.214175, 120.968736),
(141, 13, '2025-05-13 11:33:16', 0, 0, 14.566017, 121.048317),
(142, 1, '2025-05-13 11:34:02', 0, 0, 14.214175, 120.968736),
(143, 1, '2025-05-13 11:34:04', 0, 0, 14.214175, 120.968736),
(144, 13, '2025-05-13 11:34:07', 0, 0, 14.566017, 121.048317),
(145, 1, '2025-05-13 17:21:40', 0, 0, 14.670000, 121.050000),
(146, 1, '2025-05-13 17:21:40', 0, 0, 14.566017, 121.048317),
(147, 1, '2025-05-13 17:21:40', 0, 0, 14.566017, 121.048317),
(148, 1, '2025-05-13 19:15:34', 0, 0, 14.566017, 121.048317),
(149, 1, '2025-05-13 19:16:35', 0, 0, 14.670000, 121.050000),
(150, 1, '2025-05-13 19:16:35', 0, 0, 14.566017, 121.048317),
(151, 1, '2025-05-13 19:27:29', 0, 0, 14.566200, 121.048313),
(152, 1, '2025-05-13 19:27:34', 0, 0, 14.566200, 121.048313),
(153, 1, '2025-05-13 19:27:40', 0, 0.384004, 14.566211, 121.048297),
(154, 1, '2025-05-13 19:27:51', 0, 0.393109, 14.566209, 121.048317),
(155, 1, '2025-05-13 19:27:55', 0, 0.097634, 14.566213, 121.048319),
(156, 1, '2025-05-13 19:28:01', 0, 0.809263, 14.566250, 121.048303),
(157, 1, '2025-05-13 19:28:06', 0, 0.0537712, 14.566251, 121.048303),
(158, 1, '2025-05-13 19:28:08', 0, 0.0896421, 14.566220, 121.048309),
(159, 1, '2025-05-13 19:28:10', 0, 0.121005, 14.566219, 121.048310),
(160, 1, '2025-05-13 19:28:11', 0, 0.165919, 14.566219, 121.048310),
(161, 1, '2025-05-13 19:28:13', 0, 0.0867969, 14.566216, 121.048311),
(162, 1, '2025-05-13 19:28:14', 0, 0.116335, 14.566215, 121.048312),
(163, 1, '2025-05-13 19:28:15', 0, 0.146141, 14.566216, 121.048312),
(164, 1, '2025-05-13 19:28:16', 0, 0.0743583, 14.566217, 121.048313),
(165, 1, '2025-05-13 19:28:17', 0, 0.109933, 14.566217, 121.048313),
(166, 1, '2025-05-13 19:28:18', 0, 0.0906841, 14.566218, 121.048313),
(167, 1, '2025-05-13 19:28:18', 0, 0.0906743, 14.566218, 121.048313),
(168, 1, '2025-05-13 19:28:19', 0, 0.124426, 14.566217, 121.048314),
(169, 1, '2025-05-13 19:28:19', 0, 0.0579105, 14.566218, 121.048314),
(170, 1, '2025-05-13 19:28:20', 0, 0.130748, 14.566218, 121.048314),
(171, 1, '2025-05-13 19:28:20', 0, 0.0932259, 14.566219, 121.048314),
(172, 1, '2025-05-13 19:28:21', 0, 0.0992898, 14.566220, 121.048313),
(173, 1, '2025-05-13 19:28:22', 0, 0.12832, 14.566220, 121.048313),
(174, 1, '2025-05-13 19:28:23', 0, 0.102566, 14.566222, 121.048311),
(175, 1, '2025-05-13 19:28:24', 0, 0.108007, 14.566224, 121.048312),
(176, 1, '2025-05-13 19:28:26', 0, 0.106365, 14.566224, 121.048313),
(177, 1, '2025-05-13 19:28:27', 0, 0.126923, 14.566225, 121.048314),
(178, 1, '2025-05-13 19:28:28', 0, 0.137885, 14.566225, 121.048314),
(179, 1, '2025-05-13 19:28:28', 0, 0.0530862, 14.566225, 121.048316),
(180, 1, '2025-05-13 19:28:32', 0, 0.109525, 14.566227, 121.048318);

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
(2, 'Escolta → Kalawaan', 'Escolta', 'Kalawaan', '2025-05-05 12:45:37'),
(11, 'Guadalupe to Escolta', '3', '1', '2025-05-16 12:13:05'),
(12, 'Hulo to Escolta', '4', '1', '2025-05-16 12:13:05'),
(13, 'Kalawaan to Escolta', '11', '1', '2025-05-16 12:13:05'),
(14, 'Lambingan to Escolta', '6', '1', '2025-05-16 12:13:05'),
(15, 'Lawton to Escolta', '10', '1', '2025-05-16 12:13:05'),
(16, 'PUP to Escolta', '8', '1', '2025-05-16 12:13:05'),
(17, 'Quinta to Escolta', '9', '1', '2025-05-16 12:13:05'),
(18, 'San Joaquin to Escolta', '2', '1', '2025-05-16 12:13:05'),
(19, 'Sta. Ana to Escolta', '7', '1', '2025-05-16 12:13:05'),
(20, 'Valenzuela to Escolta', '5', '1', '2025-05-16 12:13:05'),
(21, 'Escolta to Guadalupe', '1', '3', '2025-05-16 12:13:05'),
(22, 'Hulo to Guadalupe', '4', '3', '2025-05-16 12:13:05'),
(23, 'Kalawaan to Guadalupe', '11', '3', '2025-05-16 12:13:05'),
(24, 'Lambingan to Guadalupe', '6', '3', '2025-05-16 12:13:05'),
(25, 'Lawton to Guadalupe', '10', '3', '2025-05-16 12:13:05'),
(26, 'PUP to Guadalupe', '8', '3', '2025-05-16 12:13:05'),
(27, 'Quinta to Guadalupe', '9', '3', '2025-05-16 12:13:05'),
(28, 'San Joaquin to Guadalupe', '2', '3', '2025-05-16 12:13:05'),
(29, 'Sta. Ana to Guadalupe', '7', '3', '2025-05-16 12:13:05'),
(30, 'Valenzuela to Guadalupe', '5', '3', '2025-05-16 12:13:05'),
(31, 'Escolta to Hulo', '1', '4', '2025-05-16 12:13:05'),
(32, 'Guadalupe to Hulo', '3', '4', '2025-05-16 12:13:05'),
(33, 'Kalawaan to Hulo', '11', '4', '2025-05-16 12:13:05'),
(34, 'Lambingan to Hulo', '6', '4', '2025-05-16 12:13:05'),
(35, 'Lawton to Hulo', '10', '4', '2025-05-16 12:13:05'),
(36, 'PUP to Hulo', '8', '4', '2025-05-16 12:13:05'),
(37, 'Quinta to Hulo', '9', '4', '2025-05-16 12:13:05'),
(38, 'San Joaquin to Hulo', '2', '4', '2025-05-16 12:13:05'),
(39, 'Sta. Ana to Hulo', '7', '4', '2025-05-16 12:13:05'),
(40, 'Valenzuela to Hulo', '5', '4', '2025-05-16 12:13:05'),
(41, 'Escolta to Kalawaan', '1', '11', '2025-05-16 12:13:05'),
(42, 'Guadalupe to Kalawaan', '3', '11', '2025-05-16 12:13:05'),
(43, 'Hulo to Kalawaan', '4', '11', '2025-05-16 12:13:05'),
(44, 'Lambingan to Kalawaan', '6', '11', '2025-05-16 12:13:05'),
(45, 'Lawton to Kalawaan', '10', '11', '2025-05-16 12:13:05'),
(46, 'PUP to Kalawaan', '8', '11', '2025-05-16 12:13:05'),
(47, 'Quinta to Kalawaan', '9', '11', '2025-05-16 12:13:05'),
(48, 'San Joaquin to Kalawaan', '2', '11', '2025-05-16 12:13:05'),
(49, 'Sta. Ana to Kalawaan', '7', '11', '2025-05-16 12:13:05'),
(50, 'Valenzuela to Kalawaan', '5', '11', '2025-05-16 12:13:05'),
(51, 'Escolta to Lambingan', '1', '6', '2025-05-16 12:13:05'),
(52, 'Guadalupe to Lambingan', '3', '6', '2025-05-16 12:13:05'),
(53, 'Hulo to Lambingan', '4', '6', '2025-05-16 12:13:05'),
(54, 'Kalawaan to Lambingan', '11', '6', '2025-05-16 12:13:05'),
(55, 'Lawton to Lambingan', '10', '6', '2025-05-16 12:13:05'),
(56, 'PUP to Lambingan', '8', '6', '2025-05-16 12:13:05'),
(57, 'Quinta to Lambingan', '9', '6', '2025-05-16 12:13:05'),
(58, 'San Joaquin to Lambingan', '2', '6', '2025-05-16 12:13:05'),
(59, 'Sta. Ana to Lambingan', '7', '6', '2025-05-16 12:13:05'),
(60, 'Valenzuela to Lambingan', '5', '6', '2025-05-16 12:13:05'),
(61, 'Escolta to Lawton', '1', '10', '2025-05-16 12:13:05'),
(62, 'Guadalupe to Lawton', '3', '10', '2025-05-16 12:13:05'),
(63, 'Hulo to Lawton', '4', '10', '2025-05-16 12:13:05'),
(64, 'Kalawaan to Lawton', '11', '10', '2025-05-16 12:13:05'),
(65, 'Lambingan to Lawton', '6', '10', '2025-05-16 12:13:05'),
(66, 'PUP to Lawton', '8', '10', '2025-05-16 12:13:05'),
(67, 'Quinta to Lawton', '9', '10', '2025-05-16 12:13:05'),
(68, 'San Joaquin to Lawton', '2', '10', '2025-05-16 12:13:05'),
(69, 'Sta. Ana to Lawton', '7', '10', '2025-05-16 12:13:05'),
(70, 'Valenzuela to Lawton', '5', '10', '2025-05-16 12:13:05'),
(71, 'Escolta to PUP', '1', '8', '2025-05-16 12:13:05'),
(72, 'Guadalupe to PUP', '3', '8', '2025-05-16 12:13:05'),
(73, 'Hulo to PUP', '4', '8', '2025-05-16 12:13:05'),
(74, 'Kalawaan to PUP', '11', '8', '2025-05-16 12:13:05'),
(75, 'Lambingan to PUP', '6', '8', '2025-05-16 12:13:05'),
(76, 'Lawton to PUP', '10', '8', '2025-05-16 12:13:05'),
(77, 'Quinta to PUP', '9', '8', '2025-05-16 12:13:05'),
(78, 'San Joaquin to PUP', '2', '8', '2025-05-16 12:13:05'),
(79, 'Sta. Ana to PUP', '7', '8', '2025-05-16 12:13:05'),
(80, 'Valenzuela to PUP', '5', '8', '2025-05-16 12:13:05'),
(81, 'Escolta to Quinta', '1', '9', '2025-05-16 12:13:05'),
(82, 'Guadalupe to Quinta', '3', '9', '2025-05-16 12:13:05'),
(83, 'Hulo to Quinta', '4', '9', '2025-05-16 12:13:05'),
(84, 'Kalawaan to Quinta', '11', '9', '2025-05-16 12:13:05'),
(85, 'Lambingan to Quinta', '6', '9', '2025-05-16 12:13:05'),
(86, 'Lawton to Quinta', '10', '9', '2025-05-16 12:13:05'),
(87, 'PUP to Quinta', '8', '9', '2025-05-16 12:13:05'),
(88, 'San Joaquin to Quinta', '2', '9', '2025-05-16 12:13:05'),
(89, 'Sta. Ana to Quinta', '7', '9', '2025-05-16 12:13:05'),
(90, 'Valenzuela to Quinta', '5', '9', '2025-05-16 12:13:05'),
(91, 'Escolta to San Joaquin', '1', '2', '2025-05-16 12:13:05'),
(92, 'Guadalupe to San Joaquin', '3', '2', '2025-05-16 12:13:05'),
(93, 'Hulo to San Joaquin', '4', '2', '2025-05-16 12:13:05'),
(94, 'Kalawaan to San Joaquin', '11', '2', '2025-05-16 12:13:05'),
(95, 'Lambingan to San Joaquin', '6', '2', '2025-05-16 12:13:05'),
(96, 'Lawton to San Joaquin', '10', '2', '2025-05-16 12:13:05'),
(97, 'PUP to San Joaquin', '8', '2', '2025-05-16 12:13:05'),
(98, 'Quinta to San Joaquin', '9', '2', '2025-05-16 12:13:05'),
(99, 'Sta. Ana to San Joaquin', '7', '2', '2025-05-16 12:13:05'),
(100, 'Valenzuela to San Joaquin', '5', '2', '2025-05-16 12:13:05'),
(101, 'Escolta to Sta. Ana', '1', '7', '2025-05-16 12:13:05'),
(102, 'Guadalupe to Sta. Ana', '3', '7', '2025-05-16 12:13:05'),
(103, 'Hulo to Sta. Ana', '4', '7', '2025-05-16 12:13:05'),
(104, 'Kalawaan to Sta. Ana', '11', '7', '2025-05-16 12:13:05'),
(105, 'Lambingan to Sta. Ana', '6', '7', '2025-05-16 12:13:05'),
(106, 'Lawton to Sta. Ana', '10', '7', '2025-05-16 12:13:05'),
(107, 'PUP to Sta. Ana', '8', '7', '2025-05-16 12:13:05'),
(108, 'Quinta to Sta. Ana', '9', '7', '2025-05-16 12:13:05'),
(109, 'San Joaquin to Sta. Ana', '2', '7', '2025-05-16 12:13:05'),
(110, 'Valenzuela to Sta. Ana', '5', '7', '2025-05-16 12:13:05'),
(111, 'Escolta to Valenzuela', '1', '5', '2025-05-16 12:13:05'),
(112, 'Guadalupe to Valenzuela', '3', '5', '2025-05-16 12:13:05'),
(113, 'Hulo to Valenzuela', '4', '5', '2025-05-16 12:13:05'),
(114, 'Kalawaan to Valenzuela', '11', '5', '2025-05-16 12:13:05'),
(115, 'Lambingan to Valenzuela', '6', '5', '2025-05-16 12:13:05'),
(116, 'Lawton to Valenzuela', '10', '5', '2025-05-16 12:13:05'),
(117, 'PUP to Valenzuela', '8', '5', '2025-05-16 12:13:05'),
(118, 'Quinta to Valenzuela', '9', '5', '2025-05-16 12:13:05'),
(119, 'San Joaquin to Valenzuela', '2', '5', '2025-05-16 12:13:05'),
(120, 'Sta. Ana to Valenzuela', '7', '5', '2025-05-16 12:13:05');

-- --------------------------------------------------------

--
-- Table structure for table `ferry_routes_backup`
--

CREATE TABLE `ferry_routes_backup` (
  `id` int(11) NOT NULL DEFAULT 0,
  `route_name` varchar(100) DEFAULT NULL,
  `origin` varchar(50) DEFAULT NULL,
  `destination` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ferry_routes_backup`
--

INSERT INTO `ferry_routes_backup` (`id`, `route_name`, `origin`, `destination`, `created_at`) VALUES
(1, 'Pinagbuhatan → Escolta', 'Pinagbuhatan', 'Escolta', '2025-05-05 12:45:37'),
(2, 'Escolta → Kalawaan', 'Escolta', 'Kalawaan', '2025-05-05 12:45:37');

-- --------------------------------------------------------

--
-- Table structure for table `ferry_safety_equipment`
--

CREATE TABLE `ferry_safety_equipment` (
  `id` int(11) NOT NULL,
  `ferry_id` int(11) NOT NULL,
  `equipment_type` enum('life_jackets','life_rafts','fire_extinguishers','first_aid','emergency_radio','other') NOT NULL,
  `quantity` int(11) DEFAULT 1,
  `last_inspection` date DEFAULT NULL,
  `notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ferry_safety_equipment`
--

INSERT INTO `ferry_safety_equipment` (`id`, `ferry_id`, `equipment_type`, `quantity`, `last_inspection`, `notes`) VALUES
(1, 1, 'life_jackets', 50, '2024-04-01', 'All jackets inspected and tagged'),
(2, 1, 'fire_extinguishers', 6, '2024-03-20', '2 replaced due to expiry'),
(3, 1, 'first_aid', 1, '2024-02-15', 'Kit restocked'),
(4, 1, 'emergency_radio', 1, '2024-04-10', 'Fully operational');

-- --------------------------------------------------------

--
-- Table structure for table `ferry_stations`
--

CREATE TABLE `ferry_stations` (
  `id` int(11) NOT NULL,
  `station_name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ferry_stations`
--

INSERT INTO `ferry_stations` (`id`, `station_name`) VALUES
(1, 'Escolta'),
(3, 'Guadalupe'),
(4, 'Hulo'),
(11, 'Kalawaan'),
(6, 'Lambingan'),
(10, 'Lawton'),
(8, 'PUP'),
(9, 'Quinta'),
(2, 'San Joaquin'),
(7, 'Sta. Ana'),
(5, 'Valenzuela');

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
-- Table structure for table `gps_logs`
--

CREATE TABLE `gps_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `role` varchar(50) NOT NULL,
  `lat` double NOT NULL,
  `lng` double NOT NULL,
  `ferry_id` int(11) DEFAULT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
-- Table structure for table `repair_logs`
--

CREATE TABLE `repair_logs` (
  `id` int(11) NOT NULL,
  `ferry_id` int(11) NOT NULL,
  `reported_at` datetime DEFAULT current_timestamp(),
  `repair_date` date DEFAULT NULL,
  `issue` text NOT NULL,
  `repair_action` text DEFAULT NULL,
  `repaired_by` varchar(100) DEFAULT NULL,
  `cost` decimal(10,2) DEFAULT NULL,
  `status` enum('Pending','In Progress','Completed') DEFAULT 'Pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `repair_logs`
--

INSERT INTO `repair_logs` (`id`, `ferry_id`, `reported_at`, `repair_date`, `issue`, `repair_action`, `repaired_by`, `cost`, `status`) VALUES
(1, 2, '2025-05-10 14:00:00', '2025-05-11', 'Engine overheating', 'Replaced coolant system', 'RiverWorks Inc.', 15000.00, 'Completed'),
(2, 1, '2025-05-09 09:00:00', NULL, 'Communication radio not working', NULL, NULL, 0.00, 'Pending'),
(3, 3, '2025-04-28 10:30:00', '2025-04-29', 'Navigation system error', 'System recalibrated and updated firmware', 'Nautical Techs', 5000.00, 'Completed');

-- --------------------------------------------------------

--
-- Table structure for table `staff_users`
--

CREATE TABLE `staff_users` (
  `staff_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('superadmin','admin','employee','operator','Auditor') NOT NULL,
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
(1, 'superadmin', 'john.doe@example.com', 'password123', '', 'John', 'Michael', 'Doe', 'Manager', 1, '2025-05-02 06:56:41', '2025-05-02 06:57:17', 'Pic1.png'),
(2, 'admin1', 'admin1@example.com', '123', '', 'Lara', 'M.', 'Santos', 'System Admin', 1, '2025-05-09 01:23:55', '2025-05-09 21:03:56', 'default.png'),
(5, 'test1', '', '$2y$10$fSDE2lj0C1ec5QSw87fCN.ZPgmCYp2kskqWr/ts5/jJie3TGvIbZe', '', 'howie', NULL, 'severino', NULL, 1, '2025-05-09 21:10:55', '2025-05-09 21:10:55', 'default.png'),
(7, 'test2', '12312ADAW@HOTMAIL.com', '$2y$10$JzsU67ADlm86qbkt6qLtmu8ER9zxbl//a3hlwvajoD6seN1hXzFd2', '', 'WOIHO', NULL, '13', NULL, 1, '2025-05-09 21:23:15', '2025-05-09 21:23:15', 'default.png'),
(8, '@test1234567', 'severinokenji@gmail.com', '$2y$10$/Dxvo3zsE/HdvyjHrvVlpe8GaXnIfIk3OPfv/y68cHorstuXN/poi', '', 'Howie', NULL, 'sevr', NULL, 1, '2025-05-10 10:27:40', '2025-05-13 12:38:28', 'uploads/profile_6823345f5e555.jpg'),
(101, 'jdoe', 'jdoe@example.com', 'hashed_password_here', '', 'John', NULL, 'Doe', NULL, 1, '2025-05-11 22:44:38', '2025-05-11 22:44:38', 'default.png'),
(104, 'Operator2', 'hseverino.k12043257@umak.edu.ph', '$2y$10$IRpFGMQ4NJ1UW.druI7Ii.cJilT6uA2163e/aVnR4JrTgOm8mGx5m', 'operator', 'Sailor', NULL, 'Moon', NULL, 1, '2025-05-12 09:49:31', '2025-05-13 10:47:26', 'default.png'),
(105, 'Auditor', 'test@gmail.com', '$2y$10$LG0/r/J7bNPbN.fcJ/vh4OYRbPouaEyG/bMwmzFKewXI8Tauk.a5C', 'Auditor', 'AUDITOR1', NULL, 'TEST1', NULL, 1, '2025-05-12 15:37:50', '2025-05-12 15:37:50', 'default.png'),
(206, 'OplanA', 'OPLANA@email.com', '$2y$10$mJ/w7yj3vSmcusNMdYRapewIFLUZ29pYGsn6ohHz212ihQ38SV966', 'operator', 'Oplan', NULL, 'A', NULL, 1, '2025-05-13 03:18:43', '2025-05-13 03:18:43', 'default.png'),
(207, '@Auditor2468', '1severinokenji@gmail.com', '$2y$10$6JpHgU9GBDzGn51Bu.EpSuwXSS.l6WDoDw2v/yQqE25RbggPyERcC', 'Auditor', 'Silence', NULL, 'Plitvice', NULL, 1, '2025-05-16 12:40:53', '2025-05-16 12:40:53', 'default.png');

-- --------------------------------------------------------

--
-- Table structure for table `tickets`
--

CREATE TABLE `tickets` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `ferry_id` int(11) NOT NULL,
  `ticket_type` varchar(50) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `purchase_date` datetime DEFAULT current_timestamp(),
  `valid_until` datetime DEFAULT NULL,
  `origin_station_id` int(11) DEFAULT NULL,
  `destination_station_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tickets`
--

INSERT INTO `tickets` (`id`, `user_id`, `ferry_id`, `ticket_type`, `amount`, `purchase_date`, `valid_until`, `origin_station_id`, `destination_station_id`) VALUES
(2, 2, 2, 'Student', 15.00, '2025-05-11 17:41:14', '2025-05-11 23:59:00', NULL, NULL),
(3, 3, 3, 'Senior', 10.00, '2025-05-11 17:41:14', '2025-05-11 23:59:00', NULL, NULL),
(4, 1, 1, 'Regular', 25.00, '2025-05-11 17:54:14', '2025-06-01 23:59:00', NULL, NULL),
(5, 2, 2, 'Student', 15.00, '2025-05-11 17:54:14', '2025-06-05 23:59:00', NULL, NULL),
(46, 8, 18, 'One-way', 40.00, '2025-05-16 20:21:23', '2025-05-17 20:21:00', 3, 1),
(47, 8, 2, 'Special', 60.00, '2025-05-16 20:25:17', '2025-05-17 20:24:00', 1, 3),
(48, 8, 1, 'One-way', 40.00, '2025-05-16 20:28:31', '2025-05-17 20:28:00', 1, 2);

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

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `birth_date` date NOT NULL,
  `profile_image` varchar(255) DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `phone_number` varchar(20) NOT NULL,
  `password` varchar(255) NOT NULL,
  `qr_code_data` varchar(255) NOT NULL,
  `issued_at` datetime DEFAULT current_timestamp(),
  `expires_at` datetime NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `last_used` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `full_name`, `birth_date`, `profile_image`, `email`, `phone_number`, `password`, `qr_code_data`, `issued_at`, `expires_at`, `is_active`, `last_used`, `created_at`) VALUES
(1, 'Carlos Reyes', '1995-04-20', 'carlos.jpg', 'carlos@example.com', '09171234567', '', 'QRUSER001', '2025-05-11 17:41:06', '2025-12-31 00:00:00', 1, NULL, '2025-05-11 09:41:06'),
(2, 'Ana Dela Cruz', '2001-09-10', 'ana.jpg', 'ana@example.com', '09181234567', '', 'QRUSER002', '2025-05-11 17:41:06', '2025-12-31 00:00:00', 0, NULL, '2025-05-11 09:41:06'),
(3, 'Joanna Santos', '1980-03-15', 'joanna.jpg', 'joanna@example.com', '09191234567', '', 'QRUSER003', '2025-05-11 17:41:06', '2025-12-31 00:00:00', 1, NULL, '2025-05-11 09:41:06'),
(4, 'Juan Dela Cruz', '1990-05-10', NULL, 'juan1@example.com', '09171234567', '', 'QRJUAN001', '2025-05-12 01:11:30', '2025-12-31 00:00:00', 1, NULL, '2025-05-11 17:11:30'),
(5, 'Maria Clara', '1985-08-15', NULL, 'maria@example.com', '09181234567', '', 'QRMARIA002', '2025-05-12 01:11:30', '2025-12-31 00:00:00', 1, NULL, '2025-05-11 17:11:30'),
(6, 'Pedro Penduko', '2000-01-01', NULL, 'pedro@example.com', '09191234567', '', 'QRPEDRO003', '2025-05-12 01:11:30', '2025-12-31 00:00:00', 1, NULL, '2025-05-11 17:11:30'),
(8, 'Exusiai', '2000-03-02', 'uploads/6824717346d65_exu.jpg', 'exu@arknights.com', '12323541235', '$2y$10$ZylCVpCm.TqzXlNGfxAkVO.jyMEroKmfDRw8WDP46nXc4d8abrqLi', 'TIX-imj8bkdx3akfgdnu7pp1', '2025-05-14 12:33:23', '2026-05-14 00:00:00', 1, '2025-05-16 23:08:41', '2025-05-14 10:33:23'),
(10, 'Nearl', '2000-03-12', 'uploads/6825cd82129bb_nearl.jpg', 'nearl@ark.com', '1234567890', '$2y$10$/lyx1VQ5.XMSdoaJos0TaOqbUxNaOFHd.Q421vQAUhyuB3ZyZ95t6', 'TIX-sf6yvr4v46skbg063k2tyc', '2025-05-15 13:18:26', '2026-05-15 00:00:00', 1, '2025-05-15 20:28:21', '2025-05-15 11:18:26');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `announcements`
--
ALTER TABLE `announcements`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `boat_maintenance`
--
ALTER TABLE `boat_maintenance`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ferry_id` (`ferry_id`);

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
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_ferries_ferry_code` (`ferry_code`),
  ADD KEY `idx_ferries_ferry_type` (`ferry_type`),
  ADD KEY `idx_ferries_registration_number` (`registration_number`),
  ADD KEY `idx_ferries_inspection_dates` (`last_inspection_date`,`next_inspection_date`);

--
-- Indexes for table `ferry_crew`
--
ALTER TABLE `ferry_crew`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ferry_id` (`ferry_id`),
  ADD KEY `staff_id` (`staff_id`);

--
-- Indexes for table `ferry_fuel_logs`
--
ALTER TABLE `ferry_fuel_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ferry_id` (`ferry_id`),
  ADD KEY `recorded_by` (`recorded_by`);

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
-- Indexes for table `ferry_safety_equipment`
--
ALTER TABLE `ferry_safety_equipment`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ferry_id` (`ferry_id`);

--
-- Indexes for table `ferry_stations`
--
ALTER TABLE `ferry_stations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `station_name` (`station_name`);

--
-- Indexes for table `ferry_stops`
--
ALTER TABLE `ferry_stops`
  ADD PRIMARY KEY (`id`),
  ADD KEY `route_id` (`route_id`);

--
-- Indexes for table `gps_logs`
--
ALTER TABLE `gps_logs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `login_attempts`
--
ALTER TABLE `login_attempts`
  ADD PRIMARY KEY (`ip_address`);

--
-- Indexes for table `repair_logs`
--
ALTER TABLE `repair_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ferry_id` (`ferry_id`);

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
  ADD KEY `idx_tickets_user` (`user_id`),
  ADD KEY `idx_tickets_ferry` (`ferry_id`),
  ADD KEY `idx_tickets_purchase` (`purchase_date`),
  ADD KEY `idx_tickets_valid` (`valid_until`),
  ADD KEY `fk_origin_station` (`origin_station_id`),
  ADD KEY `fk_destination_station` (`destination_station_id`);

--
-- Indexes for table `upstream_schedules`
--
ALTER TABLE `upstream_schedules`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `route_name` (`route_name`,`row_id`,`col_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_phone` (`phone_number`),
  ADD KEY `idx_qr_code` (`qr_code_data`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `announcements`
--
ALTER TABLE `announcements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `boat_maintenance`
--
ALTER TABLE `boat_maintenance`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `downstream_schedules`
--
ALTER TABLE `downstream_schedules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=78;

--
-- AUTO_INCREMENT for table `ferries`
--
ALTER TABLE `ferries`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=207;

--
-- AUTO_INCREMENT for table `ferry_crew`
--
ALTER TABLE `ferry_crew`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `ferry_fuel_logs`
--
ALTER TABLE `ferry_fuel_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `ferry_logs`
--
ALTER TABLE `ferry_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=181;

--
-- AUTO_INCREMENT for table `ferry_routes`
--
ALTER TABLE `ferry_routes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=121;

--
-- AUTO_INCREMENT for table `ferry_safety_equipment`
--
ALTER TABLE `ferry_safety_equipment`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `ferry_stations`
--
ALTER TABLE `ferry_stations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `ferry_stops`
--
ALTER TABLE `ferry_stops`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `gps_logs`
--
ALTER TABLE `gps_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `repair_logs`
--
ALTER TABLE `repair_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `staff_users`
--
ALTER TABLE `staff_users`
  MODIFY `staff_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=208;

--
-- AUTO_INCREMENT for table `tickets`
--
ALTER TABLE `tickets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=49;

--
-- AUTO_INCREMENT for table `upstream_schedules`
--
ALTER TABLE `upstream_schedules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=452;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `boat_maintenance`
--
ALTER TABLE `boat_maintenance`
  ADD CONSTRAINT `boat_maintenance_ibfk_1` FOREIGN KEY (`ferry_id`) REFERENCES `ferries` (`id`);

--
-- Constraints for table `ferry_crew`
--
ALTER TABLE `ferry_crew`
  ADD CONSTRAINT `fk_ferry_crew_ferry` FOREIGN KEY (`ferry_id`) REFERENCES `ferries` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_ferry_crew_staff` FOREIGN KEY (`staff_id`) REFERENCES `staff_users` (`staff_id`) ON DELETE CASCADE;

--
-- Constraints for table `ferry_fuel_logs`
--
ALTER TABLE `ferry_fuel_logs`
  ADD CONSTRAINT `fk_ferry_fuel_ferry` FOREIGN KEY (`ferry_id`) REFERENCES `ferries` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_ferry_fuel_staff` FOREIGN KEY (`recorded_by`) REFERENCES `staff_users` (`staff_id`) ON DELETE SET NULL;

--
-- Constraints for table `ferry_logs`
--
ALTER TABLE `ferry_logs`
  ADD CONSTRAINT `ferry_logs_ibfk_1` FOREIGN KEY (`ferry_id`) REFERENCES `ferries` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `ferry_safety_equipment`
--
ALTER TABLE `ferry_safety_equipment`
  ADD CONSTRAINT `fk_ferry_safety_equip` FOREIGN KEY (`ferry_id`) REFERENCES `ferries` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `ferry_stops`
--
ALTER TABLE `ferry_stops`
  ADD CONSTRAINT `ferry_stops_ibfk_1` FOREIGN KEY (`route_id`) REFERENCES `ferry_routes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `repair_logs`
--
ALTER TABLE `repair_logs`
  ADD CONSTRAINT `repair_logs_ibfk_1` FOREIGN KEY (`ferry_id`) REFERENCES `ferries` (`id`);

--
-- Constraints for table `tickets`
--
ALTER TABLE `tickets`
  ADD CONSTRAINT `fk_destination_station` FOREIGN KEY (`destination_station_id`) REFERENCES `ferry_stations` (`id`),
  ADD CONSTRAINT `fk_ferry` FOREIGN KEY (`ferry_id`) REFERENCES `ferries` (`id`),
  ADD CONSTRAINT `fk_origin_station` FOREIGN KEY (`origin_station_id`) REFERENCES `ferry_stations` (`id`),
  ADD CONSTRAINT `fk_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `tickets_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `tickets_ibfk_2` FOREIGN KEY (`ferry_id`) REFERENCES `ferries` (`id`),
  ADD CONSTRAINT `tickets_ibfk_3` FOREIGN KEY (`origin_station_id`) REFERENCES `ferry_stations` (`id`),
  ADD CONSTRAINT `tickets_ibfk_4` FOREIGN KEY (`destination_station_id`) REFERENCES `ferry_stations` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
