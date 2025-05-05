-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 03, 2025 at 02:37 AM
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
-- Database: `bus_tracking_db`
--
CREATE DATABASE IF NOT EXISTS `bus_tracking_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `bus_tracking_db`;

-- --------------------------------------------------------

--
-- Table structure for table `buses`
--

CREATE TABLE `buses` (
  `id` int(11) NOT NULL,
  `bus_number` varchar(255) NOT NULL,
  `route` varchar(255) NOT NULL,
  `status` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `locations`
--

CREATE TABLE `locations` (
  `id` int(11) NOT NULL,
  `bus_id` int(11) DEFAULT NULL,
  `latitude` float DEFAULT NULL,
  `longitude` float DEFAULT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `buses`
--
ALTER TABLE `buses`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `locations`
--
ALTER TABLE `locations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `bus_id` (`bus_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `buses`
--
ALTER TABLE `buses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `locations`
--
ALTER TABLE `locations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `locations`
--
ALTER TABLE `locations`
  ADD CONSTRAINT `locations_ibfk_1` FOREIGN KEY (`bus_id`) REFERENCES `buses` (`id`);
--
-- Database: `pares88db`
--
CREATE DATABASE IF NOT EXISTS `pares88db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `pares88db`;

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `staff_id` int(10) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(50) NOT NULL,
  `email` varchar(50) NOT NULL,
  `contact_number` int(11) NOT NULL,
  `failed_attempts` int(11) DEFAULT 0,
  `full_name` varchar(100) NOT NULL,
  `position` varchar(50) NOT NULL,
  `profile_picture` varchar(255) DEFAULT NULL,
  `last_failed_attempt` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`staff_id`, `username`, `password`, `email`, `contact_number`, `failed_attempts`, `full_name`, `position`, `profile_picture`, `last_failed_attempt`) VALUES
(75671246, 'howie', '12345', 'severinokenji@gmail.com', 672574982, 0, 'Howie Kenji S. Severino', 'Manager', 'uploads/Bg1.jpg', NULL),
(87652958, 'patrick', 'taba', 'hseverino.k12043257@umak.edu.ph', 567892876, 0, 'John Patrick Onrubia', 'Assistant manager', 'uploads/hwo.jpg', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `coupons`
--

CREATE TABLE `coupons` (
  `id` int(11) NOT NULL,
  `coupon_code` varchar(6) NOT NULL,
  `is_used` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `claim_date` date DEFAULT NULL,
  `discount` int(11) DEFAULT 0,
  `expiration_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `coupons`
--

INSERT INTO `coupons` (`id`, `coupon_code`, `is_used`, `created_at`, `claim_date`, `discount`, `expiration_date`) VALUES
(51, 'I8HHZ2', 1, '2024-12-11 14:51:56', '2024-12-11', 10, '2025-01-10'),
(53, 'QGEQIV', 1, '2024-12-11 16:39:08', '2024-12-12', 10, '2025-01-10'),
(54, 'M6Q5ER', 1, '2024-12-11 16:39:08', '2024-12-12', 10, '2025-01-10'),
(55, 'FMBBUR', 1, '2024-12-11 16:39:08', '2024-12-12', 50, '2025-01-10'),
(57, '5XPODL', 1, '2024-12-11 17:04:06', '2024-12-12', 10, '2025-01-10'),
(58, 'AV5LV6', 1, '2024-12-11 17:04:06', '2024-12-12', 10, '2025-01-10'),
(59, 'NG7TLL', 1, '2024-12-12 12:51:15', '2024-12-12', 10, '2025-01-11'),
(60, 'PT1388', 0, '2024-12-12 18:10:11', NULL, 10, '2025-01-11');

-- --------------------------------------------------------

--
-- Table structure for table `employees`
--

CREATE TABLE `employees` (
  `id` int(11) NOT NULL,
  `firstName` varchar(100) NOT NULL,
  `lastName` varchar(100) NOT NULL,
  `birthDate` date NOT NULL,
  `email` varchar(100) NOT NULL,
  `contact` varchar(15) NOT NULL,
  `address` text NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `failed_attempt` int(11) NOT NULL DEFAULT 0,
  `position` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `qr_code_path` varchar(255) DEFAULT NULL,
  `profile_picture` varchar(255) DEFAULT NULL,
  `last_failed_attempt` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employees`
--

INSERT INTO `employees` (`id`, `firstName`, `lastName`, `birthDate`, `email`, `contact`, `address`, `username`, `password`, `failed_attempt`, `position`, `created_at`, `qr_code_path`, `profile_picture`, `last_failed_attempt`) VALUES
(4, 'howie2', 'Severino', '2024-10-27', 'severinokenji@gmail.com', '09053058242', '193 D Guiho St.', 'howie', '123', 0, 'Manager', '2024-11-13 15:52:38', 'qr_codes/employee_4.png', NULL, NULL),
(8, 'tom', 'lara', '2024-11-29', 'tom@gmail.com', '45678563534', '43 umak rd 3rd floor underground', 'lara', '123', 0, 'Assistant Manager', '2024-11-13 15:54:27', 'qr_codes/employee_8.png', NULL, NULL),
(9, 'onrubia', 'jonathan', '2024-11-30', '1seji@gmail.com', '09053058242', '193 D Guiho St.', 'wow', '$2y$10$cXija2k1860s7KWcoZUBb.SaKvCIaM8cXjXDTjQ.5L6o4ufgxRfjq', 0, 'Chef', '2024-11-13 15:55:27', 'qr_codes/employee_9.png', NULL, NULL),
(10, 'test1', 'test1', '2024-11-06', 'test 23', '09053058242', '193 D Guiho St.', '123', '$2y$10$wxkvca3ajOMij5G.L15Wcum/jMphOas8SszY7Jw/3X8vt8dEpCSZa', 0, 'cashier', '2024-11-13 17:12:37', 'qr_codes/employee_10.png', NULL, NULL),
(19, 'test2', 'tes2', '2024-11-08', 'test2@gmail.com', '12312', '123123123', 'test2', '123', 0, 'cook', '2024-11-13 19:07:03', 'qr_codes/employee_19.png', NULL, NULL),
(21, 'test3', '3test', '1241-03-12', '23123', '123123123', '1123123', '1233', '$2y$10$3x8fK9eFrv15Ag4imLPvi.iixkTuEdW3zs3jMhl4IkpDw0HS0I78y', 0, 'head_cook', '2024-11-14 08:07:37', 'qr_codes/employee_21.png', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `feedback`
--

CREATE TABLE `feedback` (
  `id` int(11) NOT NULL,
  `name` varchar(25) NOT NULL COMMENT 'Stores the name of the user',
  `email` varchar(60) NOT NULL COMMENT 'Stores email',
  `comment` varchar(600) NOT NULL COMMENT 'Records the feedback of the user regarding their order',
  `rating` int(5) NOT NULL COMMENT 'Total ratings the customer gave'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `feedback`
--

INSERT INTO `feedback` (`id`, `name`, `email`, `comment`, `rating`) VALUES
(1, 'taong tabon', 'ej@gmail.com', 'wow sarap', 5),
(2, 'howie', 'severinokenji@gmail.com', '123312312', 3);

-- --------------------------------------------------------

--
-- Table structure for table `inventory`
--

CREATE TABLE `inventory` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `category` varchar(255) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `stock` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `max_storage` int(11) NOT NULL DEFAULT 100,
  `last_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `inventory`
--

INSERT INTO `inventory` (`id`, `name`, `category`, `price`, `stock`, `created_at`, `max_storage`, `last_updated`) VALUES
(3, 'Rice (100g)', 'Base', 0.50, 64, '2024-11-25 08:01:36', 200, '2025-01-23 12:17:37'),
(4, 'Oil(1/2 cup)', 'Cooking', 0.20, 49, '2024-11-25 08:01:36', 1000, '2024-12-14 14:50:47'),
(5, 'Garlic(10g)', 'Flavoring', 0.30, 35, '2024-11-25 08:01:36', 100, '2025-01-23 12:17:37'),
(6, 'Peas(30g)', 'Vegetable', 0.40, 21, '2024-11-25 08:01:36', 100, '2025-01-23 12:17:37'),
(7, 'Carrots', 'Vegetable', 0.35, 31, '2024-11-25 08:01:36', 100, '2025-01-23 12:17:37'),
(8, 'Onions', 'Vegetable', 0.25, 43, '2024-11-25 08:01:36', 100, '2024-12-10 18:35:04'),
(9, 'Bell Peppers', 'Vegetable', 0.60, 50, '2024-11-25 08:01:36', 100, '2024-12-07 20:44:44'),
(10, 'Eggs', 'Protein', 1.00, 20, '2024-11-25 08:01:36', 100, '2025-01-23 12:17:37'),
(11, 'Soy Sauce', 'Seasoning', 0.15, 98, '2024-11-25 08:01:36', 100, '2024-12-07 20:44:44'),
(12, 'Green Onions', 'Garnish', 0.50, 45, '2024-11-25 08:01:36', 100, '2024-12-10 18:35:04'),
(13, 'Chicken', 'Protein', 2.00, 100, '2024-11-25 08:01:36', 100, '2024-12-07 20:44:44'),
(14, 'Shrimp', 'Protein', 3.00, 50, '2024-11-25 08:01:36', 100, '2024-12-07 20:44:44'),
(15, 'Tofu', 'Protein', 1.50, 50, '2024-11-25 08:01:36', 100, '2024-12-07 20:44:44'),
(16, 'Oyster Sauce', 'Seasoning', 0.75, 100, '2024-11-25 08:01:36', 100, '2024-12-07 20:44:44'),
(17, 'Fish Sauce', 'Seasoning', 0.50, 63, '2024-11-25 08:01:36', 100, '2024-12-10 18:35:04'),
(19, 'Mani', 'snacks', 20.00, 50, '2024-11-26 13:57:36', 100, '2024-12-07 20:44:44'),
(20, 'Noodles(pack)', 'beverages', 2.00, 12, '2024-12-02 17:21:27', 100, '2024-12-14 14:50:47'),
(21, 'Water', 'beverages', 2.00, 173, '2024-12-02 18:21:19', 300, '2024-12-14 14:50:47'),
(22, 'Pepper', 'condiments', 2.00, 20, '2024-12-03 12:45:54', 100, '2024-12-07 20:44:44');

-- --------------------------------------------------------

--
-- Table structure for table `menu_items`
--

CREATE TABLE `menu_items` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `image_url` varchar(255) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `category` enum('Specials','Silog','Sizzling','Soups','Add-ons','Drinks') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `menu_items`
--

INSERT INTO `menu_items` (`id`, `name`, `description`, `image_url`, `price`, `category`) VALUES
(1, 'Fried rice', 'fired na icer', 'http://localhost/trial/friedrice.jpg', 20.00, 'Add-ons'),
(2, 'egg', 'egg', 'http://localhost/trial/prideegg.jpg', 30.00, 'Add-ons'),
(30, 'BURGERSTEAK', 'Burger + Egg + Rice', 'brgr stk.jpg', 99.00, 'Silog'),
(31, 'Sizzling Chicken', 'Chicken + Rice + Egg', 'http://localhost/trial/chicken.jpg', 99.00, 'Sizzling'),
(32, 'Sizzling Hungarian', 'Hungarian + Rice + Egg', 'hungarian.jpg', 150.00, 'Sizzling'),
(33, 'Liempo Silog', 'Liempo + Rice + Egg', 'Lmpo.jpg', 160.00, 'Silog'),
(34, 'Pares Mami', 'Noodles + Beef', 'http://localhost/trial/mami.jpg', 20.00, 'Soups'),
(36, 'Water', 'Cold + Warm', 'http://localhost/trial/water.jpg', 20.00, 'Drinks'),
(37, 'Bulalo', 'Beef', 'ec26dc5a-2e50-414f-a379-00a7592d378f.jpg', 150.00, 'Soups'),
(38, 'Pepsi', 'Softdrinks', 'b575648a-c98d-47f8-8581-2b8653f24bcd.jpg', 45.00, 'Drinks'),
(39, 'beef parese', 'beef', '1DS.png', 99.00, 'Soups');

-- --------------------------------------------------------

--
-- Table structure for table `menu_item_ingredients`
--

CREATE TABLE `menu_item_ingredients` (
  `menu_item_id` int(11) NOT NULL,
  `ingredient_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `menu_item_ingredients`
--

INSERT INTO `menu_item_ingredients` (`menu_item_id`, `ingredient_id`, `quantity`) VALUES
(1, 3, 10),
(2, 4, 2),
(2, 10, 2),
(30, 3, 2),
(30, 4, 1),
(30, 5, 1),
(30, 10, 1),
(31, 3, 2),
(31, 6, 2),
(31, 7, 2),
(31, 10, 2),
(32, 3, 1),
(32, 5, 1),
(32, 6, 1),
(32, 7, 1),
(32, 10, 1),
(33, 3, 1),
(33, 5, 1),
(33, 6, 1),
(33, 7, 1),
(33, 10, 1),
(34, 20, 1),
(36, 21, 1),
(37, 5, 1),
(37, 8, 1),
(37, 12, 1),
(37, 17, 1),
(37, 21, 1),
(38, 21, 3),
(39, 3, 1),
(39, 4, 2),
(39, 20, 2),
(39, 21, 2);

-- --------------------------------------------------------

--
-- Table structure for table `otp`
--

CREATE TABLE `otp` (
  `id` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `otp` varchar(10) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `qr_code_scans`
--

CREATE TABLE `qr_code_scans` (
  `id` int(11) NOT NULL,
  `qr_data` varchar(255) NOT NULL,
  `scan_time` time DEFAULT NULL,
  `clock_in_time` datetime DEFAULT NULL,
  `clock_out_time` datetime DEFAULT NULL,
  `status` enum('clocked_in','clocked_out') DEFAULT 'clocked_out',
  `user_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `qr_code_scans`
--

INSERT INTO `qr_code_scans` (`id`, `qr_data`, `scan_time`, `clock_in_time`, `clock_out_time`, `status`, `user_id`) VALUES
(33, 'ID: 8\nName: tom1 lara\nPosition: Designsatabi\nEmail: tom@gmail.com\nQR Key: p@re$88', '08:55:58', '2024-11-18 08:55:58', '2024-11-18 08:56:05', 'clocked_out', NULL),
(34, 'Employee: totoongtao jokelang | ID: ', '08:56:22', '2024-11-18 08:56:22', '2024-11-18 08:56:44', 'clocked_out', NULL),
(35, '', '08:56:34', '2024-11-18 08:56:34', NULL, 'clocked_out', NULL),
(36, 'ID: 9\nName: onrubia jonathan\nPosition: tae\nEmail: 1severinokenji@gmail.com\nQR Key: p@re$88', '09:06:42', '2024-11-18 09:06:42', '2024-11-18 09:06:49', 'clocked_out', NULL),
(37, 'ID: 10\nName: test1 test1\nPosition: cashier\nEmail: \nQR Key: p@re$88', '09:06:55', '2024-11-18 09:06:55', NULL, 'clocked_out', NULL),
(38, 'ID: 9\nName: onrubia jonathan\nPosition: tae\nEmail: 1severinokenji@gmail.com\nQR Key: p@re$88', '09:07:12', '2024-11-18 09:07:12', '2024-11-18 09:07:21', 'clocked_out', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `receipts`
--

CREATE TABLE `receipts` (
  `id` int(11) NOT NULL,
  `reference_number` varchar(50) NOT NULL,
  `receipt_date` datetime NOT NULL,
  `total_cost` decimal(10,2) NOT NULL,
  `discount_amount` decimal(10,2) NOT NULL,
  `net_total` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `vat_amount` decimal(10,2) NOT NULL,
  `inclusive_total` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `receipts`
--

INSERT INTO `receipts` (`id`, `reference_number`, `receipt_date`, `total_cost`, `discount_amount`, `net_total`, `created_at`, `vat_amount`, `inclusive_total`) VALUES
(113, 'REF6759c0ef818b5', '2024-12-11 17:42:23', 60.00, 6.00, 54.00, '2024-12-11 16:42:23', 0.00, 0.00),
(114, 'REF6759c422db59e', '2024-12-11 17:56:02', 40.00, 0.00, 40.00, '2024-12-11 16:56:02', 0.00, 0.00),
(115, 'REF6759c43fecb8d', '2024-12-11 17:56:31', 30.00, 15.00, 15.00, '2024-12-11 16:56:31', 0.00, 0.00),
(116, 'REF6759c62eb386a', '2024-12-11 18:04:46', 30.00, 3.00, 27.00, '2024-12-11 17:04:46', 0.00, 0.00),
(117, 'REF6759cc68ad857', '2024-12-11 18:31:20', 30.00, 0.00, 30.00, '2024-12-11 17:31:20', 0.00, 0.00),
(118, 'REF6759d09752d86', '2024-12-11 18:49:11', 20.00, 0.00, 20.00, '2024-12-11 17:49:11', 0.00, 0.00),
(119, 'REF675ae530ba401', '2024-12-12 14:29:20', 30.00, 0.00, 30.00, '2024-12-12 13:29:20', 3.60, 33.60),
(120, 'REF675b271959878', '2024-12-12 19:10:33', 319.00, 0.00, 319.00, '2024-12-12 18:10:33', 38.28, 357.28),
(121, 'REF675d9b474d14e', '2024-12-14 15:50:47', 129.00, 0.00, 129.00, '2024-12-14 14:50:47', 15.48, 144.48),
(122, 'REF6792336138a4a', '2025-01-23 13:17:37', 498.00, 0.00, 498.00, '2025-01-23 12:17:37', 59.76, 557.76);

-- --------------------------------------------------------

--
-- Table structure for table `receipt_items`
--

CREATE TABLE `receipt_items` (
  `id` int(11) NOT NULL,
  `receipt_id` int(11) NOT NULL,
  `item_name` varchar(100) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `receipt_items`
--

INSERT INTO `receipt_items` (`id`, `receipt_id`, `item_name`, `quantity`, `price`, `subtotal`) VALUES
(131, 113, '\n                    \n                    egg', 2, 30.00, 60.00),
(132, 114, '\n                    \n                    Fried rice', 2, 20.00, 40.00),
(133, 115, '\n                    \n                    egg', 1, 30.00, 30.00),
(134, 116, '\n                    \n                    egg', 1, 30.00, 30.00),
(135, 117, '\n                    \n                    egg', 1, 30.00, 30.00),
(136, 118, '\n                    \n                    Water', 1, 20.00, 20.00),
(137, 119, '\n                    \n                    egg', 1, 30.00, 30.00),
(138, 120, '\n                    \n                    egg', 2, 30.00, 60.00),
(139, 120, '\n                    \n                    Liempo Silog', 1, 160.00, 160.00),
(140, 120, '\n                    \n                    BURGERSTEAK', 1, 99.00, 99.00),
(141, 121, '\n                    \n                    beef parese', 1, 99.00, 99.00),
(142, 121, '\n                    \n                    egg', 1, 30.00, 30.00),
(143, 122, '\n                    \n                    Sizzling Chicken', 2, 99.00, 198.00),
(144, 122, '\n                    \n                    Sizzling Hungarian', 2, 150.00, 300.00);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`staff_id`);

--
-- Indexes for table `coupons`
--
ALTER TABLE `coupons`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `coupon_code` (`coupon_code`);

--
-- Indexes for table `employees`
--
ALTER TABLE `employees`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `feedback`
--
ALTER TABLE `feedback`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `inventory`
--
ALTER TABLE `inventory`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `menu_items`
--
ALTER TABLE `menu_items`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `menu_item_ingredients`
--
ALTER TABLE `menu_item_ingredients`
  ADD PRIMARY KEY (`menu_item_id`,`ingredient_id`),
  ADD KEY `ingredient_id` (`ingredient_id`);

--
-- Indexes for table `otp`
--
ALTER TABLE `otp`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `qr_code_scans`
--
ALTER TABLE `qr_code_scans`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `receipts`
--
ALTER TABLE `receipts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `reference_number` (`reference_number`);

--
-- Indexes for table `receipt_items`
--
ALTER TABLE `receipt_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `receipt_id` (`receipt_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `staff_id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=87652959;

--
-- AUTO_INCREMENT for table `coupons`
--
ALTER TABLE `coupons`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=61;

--
-- AUTO_INCREMENT for table `employees`
--
ALTER TABLE `employees`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `feedback`
--
ALTER TABLE `feedback`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `inventory`
--
ALTER TABLE `inventory`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `menu_items`
--
ALTER TABLE `menu_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT for table `otp`
--
ALTER TABLE `otp`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT for table `qr_code_scans`
--
ALTER TABLE `qr_code_scans`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT for table `receipts`
--
ALTER TABLE `receipts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=123;

--
-- AUTO_INCREMENT for table `receipt_items`
--
ALTER TABLE `receipt_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=145;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `menu_item_ingredients`
--
ALTER TABLE `menu_item_ingredients`
  ADD CONSTRAINT `menu_item_ingredients_ibfk_1` FOREIGN KEY (`menu_item_id`) REFERENCES `menu_items` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `menu_item_ingredients_ibfk_2` FOREIGN KEY (`ingredient_id`) REFERENCES `inventory` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `receipt_items`
--
ALTER TABLE `receipt_items`
  ADD CONSTRAINT `receipt_items_ibfk_1` FOREIGN KEY (`receipt_id`) REFERENCES `receipts` (`id`) ON DELETE CASCADE;
--
-- Database: `peracle_db`
--
CREATE DATABASE IF NOT EXISTS `peracle_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `peracle_db`;

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `first_name` varchar(50) DEFAULT NULL,
  `middle_name` varchar(50) DEFAULT NULL,
  `last_name` varchar(50) DEFAULT NULL,
  `age` int(11) DEFAULT NULL,
  `birthdate` date DEFAULT NULL,
  `username` varchar(50) DEFAULT NULL,
  `password` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `first_name`, `middle_name`, `last_name`, `age`, `birthdate`, `username`, `password`) VALUES
(1, 'Howie Kenji', 'Secret', 'Secret rin', 20, '2024-06-01', '123', '123');

-- --------------------------------------------------------

--
-- Table structure for table `donations`
--

CREATE TABLE `donations` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `donation_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `amount` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `price`, `amount`) VALUES
(1, 'ecobrick', 3.00, 200),
(3, 'eco friendly wood veener', 1000.00, 100),
(4, 'Ecobag', 1.00, 1000);

-- --------------------------------------------------------

--
-- Table structure for table `purchases`
--

CREATE TABLE `purchases` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `purchase_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `redeemcodes`
--

CREATE TABLE `redeemcodes` (
  `id` int(11) NOT NULL,
  `code` varchar(255) NOT NULL,
  `value` decimal(10,2) NOT NULL,
  `material` enum('metal','plastic','cardboard') NOT NULL,
  `weight` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_used` tinyint(1) DEFAULT 0,
  `redeemed_at` datetime DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `redeemcodes`
--

INSERT INTO `redeemcodes` (`id`, `code`, `value`, `material`, `weight`, `created_at`, `is_used`, `redeemed_at`, `user_id`) VALUES
(16, 'JF52OVLBR6', 500.00, 'metal', 10.00, '2024-06-10 05:10:35', 1, '2024-06-10 13:11:03', 1),
(17, 'OSKMDQXAYB', 1500.00, 'metal', 30.00, '2024-06-10 09:33:57', 1, '2024-06-10 17:35:50', 24),
(18, 'H5Z24LFOTX', 220.00, 'cardboard', 22.00, '2024-06-10 09:35:00', 1, '2024-06-10 17:35:07', 24),
(19, 'RU32NIYGB9', 50000.00, 'metal', 1000.00, '2024-06-10 13:06:39', 1, '2024-06-10 21:07:40', 24),
(20, 'HLDP84EU7Z', 100.00, 'cardboard', 10.00, '2024-06-10 13:08:34', 1, '2024-06-10 21:09:20', 25),
(21, 'NAKL70TUPV', 99999999.99, 'metal', 20000000.00, '2024-06-10 13:10:14', 1, '2024-06-10 21:10:30', 25),
(22, 'AJWTI6HMEB', 500.00, 'metal', 10.00, '2024-11-19 13:18:08', 0, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `first_name` varchar(50) DEFAULT NULL,
  `middle_name` varchar(50) DEFAULT NULL,
  `last_name` varchar(50) DEFAULT NULL,
  `age` int(11) DEFAULT NULL,
  `birthdate` date DEFAULT NULL,
  `username` varchar(50) DEFAULT NULL,
  `password` varchar(50) DEFAULT NULL,
  `balance` decimal(10,2) DEFAULT 0.00,
  `is_locked` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `first_name`, `middle_name`, `last_name`, `age`, `birthdate`, `username`, `password`, `balance`, `is_locked`) VALUES
(24, 'Roel', 'G', 'Traballo', 20, '2000-02-10', 'Roel', '0000', 100.00, 0),
(25, 'howie', 'kenj', 'Severino', 20, '2004-08-05', 'howie', '0000', 100.00, 0);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `donations`
--
ALTER TABLE `donations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `purchases`
--
ALTER TABLE `purchases`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `redeemcodes`
--
ALTER TABLE `redeemcodes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `donations`
--
ALTER TABLE `donations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `purchases`
--
ALTER TABLE `purchases`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `redeemcodes`
--
ALTER TABLE `redeemcodes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `donations`
--
ALTER TABLE `donations`
  ADD CONSTRAINT `donations_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `purchases`
--
ALTER TABLE `purchases`
  ADD CONSTRAINT `purchases_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `purchases_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);
--
-- Database: `phpmyadmin`
--
CREATE DATABASE IF NOT EXISTS `phpmyadmin` DEFAULT CHARACTER SET utf8 COLLATE utf8_bin;
USE `phpmyadmin`;

-- --------------------------------------------------------

--
-- Table structure for table `pma__bookmark`
--

CREATE TABLE `pma__bookmark` (
  `id` int(10) UNSIGNED NOT NULL,
  `dbase` varchar(255) NOT NULL DEFAULT '',
  `user` varchar(255) NOT NULL DEFAULT '',
  `label` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `query` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Bookmarks';

-- --------------------------------------------------------

--
-- Table structure for table `pma__central_columns`
--

CREATE TABLE `pma__central_columns` (
  `db_name` varchar(64) NOT NULL,
  `col_name` varchar(64) NOT NULL,
  `col_type` varchar(64) NOT NULL,
  `col_length` text DEFAULT NULL,
  `col_collation` varchar(64) NOT NULL,
  `col_isNull` tinyint(1) NOT NULL,
  `col_extra` varchar(255) DEFAULT '',
  `col_default` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Central list of columns';

-- --------------------------------------------------------

--
-- Table structure for table `pma__column_info`
--

CREATE TABLE `pma__column_info` (
  `id` int(5) UNSIGNED NOT NULL,
  `db_name` varchar(64) NOT NULL DEFAULT '',
  `table_name` varchar(64) NOT NULL DEFAULT '',
  `column_name` varchar(64) NOT NULL DEFAULT '',
  `comment` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `mimetype` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `transformation` varchar(255) NOT NULL DEFAULT '',
  `transformation_options` varchar(255) NOT NULL DEFAULT '',
  `input_transformation` varchar(255) NOT NULL DEFAULT '',
  `input_transformation_options` varchar(255) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Column information for phpMyAdmin';

-- --------------------------------------------------------

--
-- Table structure for table `pma__designer_settings`
--

CREATE TABLE `pma__designer_settings` (
  `username` varchar(64) NOT NULL,
  `settings_data` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Settings related to Designer';

-- --------------------------------------------------------

--
-- Table structure for table `pma__export_templates`
--

CREATE TABLE `pma__export_templates` (
  `id` int(5) UNSIGNED NOT NULL,
  `username` varchar(64) NOT NULL,
  `export_type` varchar(10) NOT NULL,
  `template_name` varchar(64) NOT NULL,
  `template_data` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Saved export templates';

--
-- Dumping data for table `pma__export_templates`
--

INSERT INTO `pma__export_templates` (`id`, `username`, `export_type`, `template_name`, `template_data`) VALUES
(1, 'root', 'server', 'pares88', '{\"quick_or_custom\":\"quick\",\"what\":\"sql\",\"db_select[]\":[\"pares88db\",\"phpmyadmin\",\"test\"],\"aliases_new\":\"\",\"output_format\":\"sendit\",\"filename_template\":\"@SERVER@\",\"remember_template\":\"on\",\"charset\":\"utf-8\",\"compression\":\"none\",\"maxsize\":\"\",\"codegen_structure_or_data\":\"data\",\"codegen_format\":\"0\",\"csv_separator\":\",\",\"csv_enclosed\":\"\\\"\",\"csv_escaped\":\"\\\"\",\"csv_terminated\":\"AUTO\",\"csv_null\":\"NULL\",\"csv_columns\":\"something\",\"csv_structure_or_data\":\"data\",\"excel_null\":\"NULL\",\"excel_columns\":\"something\",\"excel_edition\":\"win\",\"excel_structure_or_data\":\"data\",\"json_structure_or_data\":\"data\",\"json_unicode\":\"something\",\"latex_caption\":\"something\",\"latex_structure_or_data\":\"structure_and_data\",\"latex_structure_caption\":\"Structure of table @TABLE@\",\"latex_structure_continued_caption\":\"Structure of table @TABLE@ (continued)\",\"latex_structure_label\":\"tab:@TABLE@-structure\",\"latex_relation\":\"something\",\"latex_comments\":\"something\",\"latex_mime\":\"something\",\"latex_columns\":\"something\",\"latex_data_caption\":\"Content of table @TABLE@\",\"latex_data_continued_caption\":\"Content of table @TABLE@ (continued)\",\"latex_data_label\":\"tab:@TABLE@-data\",\"latex_null\":\"\\\\textit{NULL}\",\"mediawiki_structure_or_data\":\"data\",\"mediawiki_caption\":\"something\",\"mediawiki_headers\":\"something\",\"htmlword_structure_or_data\":\"structure_and_data\",\"htmlword_null\":\"NULL\",\"ods_null\":\"NULL\",\"ods_structure_or_data\":\"data\",\"odt_structure_or_data\":\"structure_and_data\",\"odt_relation\":\"something\",\"odt_comments\":\"something\",\"odt_mime\":\"something\",\"odt_columns\":\"something\",\"odt_null\":\"NULL\",\"pdf_report_title\":\"\",\"pdf_structure_or_data\":\"data\",\"phparray_structure_or_data\":\"data\",\"sql_include_comments\":\"something\",\"sql_header_comment\":\"\",\"sql_use_transaction\":\"something\",\"sql_compatibility\":\"NONE\",\"sql_structure_or_data\":\"structure_and_data\",\"sql_create_table\":\"something\",\"sql_auto_increment\":\"something\",\"sql_create_view\":\"something\",\"sql_create_trigger\":\"something\",\"sql_backquotes\":\"something\",\"sql_type\":\"INSERT\",\"sql_insert_syntax\":\"both\",\"sql_max_query_size\":\"50000\",\"sql_hex_for_binary\":\"something\",\"sql_utc_time\":\"something\",\"texytext_structure_or_data\":\"structure_and_data\",\"texytext_null\":\"NULL\",\"yaml_structure_or_data\":\"data\",\"\":null,\"as_separate_files\":null,\"csv_removeCRLF\":null,\"excel_removeCRLF\":null,\"json_pretty_print\":null,\"htmlword_columns\":null,\"ods_columns\":null,\"sql_dates\":null,\"sql_relation\":null,\"sql_mime\":null,\"sql_disable_fk\":null,\"sql_views_as_tables\":null,\"sql_metadata\":null,\"sql_drop_database\":null,\"sql_drop_table\":null,\"sql_if_not_exists\":null,\"sql_simple_view_export\":null,\"sql_view_current_user\":null,\"sql_or_replace_view\":null,\"sql_procedure_function\":null,\"sql_truncate\":null,\"sql_delayed\":null,\"sql_ignore\":null,\"texytext_columns\":null}');

-- --------------------------------------------------------

--
-- Table structure for table `pma__favorite`
--

CREATE TABLE `pma__favorite` (
  `username` varchar(64) NOT NULL,
  `tables` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Favorite tables';

-- --------------------------------------------------------

--
-- Table structure for table `pma__history`
--

CREATE TABLE `pma__history` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `username` varchar(64) NOT NULL DEFAULT '',
  `db` varchar(64) NOT NULL DEFAULT '',
  `table` varchar(64) NOT NULL DEFAULT '',
  `timevalue` timestamp NOT NULL DEFAULT current_timestamp(),
  `sqlquery` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='SQL history for phpMyAdmin';

-- --------------------------------------------------------

--
-- Table structure for table `pma__navigationhiding`
--

CREATE TABLE `pma__navigationhiding` (
  `username` varchar(64) NOT NULL,
  `item_name` varchar(64) NOT NULL,
  `item_type` varchar(64) NOT NULL,
  `db_name` varchar(64) NOT NULL,
  `table_name` varchar(64) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Hidden items of navigation tree';

-- --------------------------------------------------------

--
-- Table structure for table `pma__pdf_pages`
--

CREATE TABLE `pma__pdf_pages` (
  `db_name` varchar(64) NOT NULL DEFAULT '',
  `page_nr` int(10) UNSIGNED NOT NULL,
  `page_descr` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='PDF relation pages for phpMyAdmin';

-- --------------------------------------------------------

--
-- Table structure for table `pma__recent`
--

CREATE TABLE `pma__recent` (
  `username` varchar(64) NOT NULL,
  `tables` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Recently accessed tables';

--
-- Dumping data for table `pma__recent`
--

INSERT INTO `pma__recent` (`username`, `tables`) VALUES
('root', '[{\"db\":\"prfs\",\"table\":\"ferry_locations\"},{\"db\":\"prfs\",\"table\":\"ferries\"},{\"db\":\"prfs\",\"table\":\"staff_users\"},{\"db\":\"bus_tracking_db\",\"table\":\"buses\"},{\"db\":\"bus_tracking_db\",\"table\":\"locations\"},{\"db\":\"pares88db\",\"table\":\"otp\"},{\"db\":\"pares88db\",\"table\":\"employees\"},{\"db\":\"pares88db\",\"table\":\"receipts\"},{\"db\":\"pares88db\",\"table\":\"menu_items\"},{\"db\":\"pares88db\",\"table\":\"admin\"}]');

-- --------------------------------------------------------

--
-- Table structure for table `pma__relation`
--

CREATE TABLE `pma__relation` (
  `master_db` varchar(64) NOT NULL DEFAULT '',
  `master_table` varchar(64) NOT NULL DEFAULT '',
  `master_field` varchar(64) NOT NULL DEFAULT '',
  `foreign_db` varchar(64) NOT NULL DEFAULT '',
  `foreign_table` varchar(64) NOT NULL DEFAULT '',
  `foreign_field` varchar(64) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Relation table';

-- --------------------------------------------------------

--
-- Table structure for table `pma__savedsearches`
--

CREATE TABLE `pma__savedsearches` (
  `id` int(5) UNSIGNED NOT NULL,
  `username` varchar(64) NOT NULL DEFAULT '',
  `db_name` varchar(64) NOT NULL DEFAULT '',
  `search_name` varchar(64) NOT NULL DEFAULT '',
  `search_data` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Saved searches';

-- --------------------------------------------------------

--
-- Table structure for table `pma__table_coords`
--

CREATE TABLE `pma__table_coords` (
  `db_name` varchar(64) NOT NULL DEFAULT '',
  `table_name` varchar(64) NOT NULL DEFAULT '',
  `pdf_page_number` int(11) NOT NULL DEFAULT 0,
  `x` float UNSIGNED NOT NULL DEFAULT 0,
  `y` float UNSIGNED NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Table coordinates for phpMyAdmin PDF output';

-- --------------------------------------------------------

--
-- Table structure for table `pma__table_info`
--

CREATE TABLE `pma__table_info` (
  `db_name` varchar(64) NOT NULL DEFAULT '',
  `table_name` varchar(64) NOT NULL DEFAULT '',
  `display_field` varchar(64) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Table information for phpMyAdmin';

-- --------------------------------------------------------

--
-- Table structure for table `pma__table_uiprefs`
--

CREATE TABLE `pma__table_uiprefs` (
  `username` varchar(64) NOT NULL,
  `db_name` varchar(64) NOT NULL,
  `table_name` varchar(64) NOT NULL,
  `prefs` text NOT NULL,
  `last_update` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Tables'' UI preferences';

--
-- Dumping data for table `pma__table_uiprefs`
--

INSERT INTO `pma__table_uiprefs` (`username`, `db_name`, `table_name`, `prefs`, `last_update`) VALUES
('root', 'pares88db', 'admin', '{\"CREATE_TIME\":\"2024-12-06 01:25:31\"}', '2024-12-12 15:09:10'),
('root', 'pares88db', 'employees', '{\"CREATE_TIME\":\"2024-12-07 09:17:10\",\"col_order\":[0,1,2,3,4,5,6,7,9,8,10,11,12,13,14],\"col_visib\":[1,1,1,1,1,1,1,1,1,1,1,1,1,1,1]}', '2024-12-12 15:03:15'),
('root', 'prfs', 'ferries', '{\"sorted_col\":\"`active_time` ASC\"}', '2025-04-23 08:58:47');

-- --------------------------------------------------------

--
-- Table structure for table `pma__tracking`
--

CREATE TABLE `pma__tracking` (
  `db_name` varchar(64) NOT NULL,
  `table_name` varchar(64) NOT NULL,
  `version` int(10) UNSIGNED NOT NULL,
  `date_created` datetime NOT NULL,
  `date_updated` datetime NOT NULL,
  `schema_snapshot` text NOT NULL,
  `schema_sql` text DEFAULT NULL,
  `data_sql` longtext DEFAULT NULL,
  `tracking` set('UPDATE','REPLACE','INSERT','DELETE','TRUNCATE','CREATE DATABASE','ALTER DATABASE','DROP DATABASE','CREATE TABLE','ALTER TABLE','RENAME TABLE','DROP TABLE','CREATE INDEX','DROP INDEX','CREATE VIEW','ALTER VIEW','DROP VIEW') DEFAULT NULL,
  `tracking_active` int(1) UNSIGNED NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Database changes tracking for phpMyAdmin';

-- --------------------------------------------------------

--
-- Table structure for table `pma__userconfig`
--

CREATE TABLE `pma__userconfig` (
  `username` varchar(64) NOT NULL,
  `timevalue` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `config_data` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='User preferences storage for phpMyAdmin';

--
-- Dumping data for table `pma__userconfig`
--

INSERT INTO `pma__userconfig` (`username`, `timevalue`, `config_data`) VALUES
('root', '2025-04-28 09:06:06', '{\"Console\\/Mode\":\"collapse\",\"NavigationWidth\":371}');

-- --------------------------------------------------------

--
-- Table structure for table `pma__usergroups`
--

CREATE TABLE `pma__usergroups` (
  `usergroup` varchar(64) NOT NULL,
  `tab` varchar(64) NOT NULL,
  `allowed` enum('Y','N') NOT NULL DEFAULT 'N'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='User groups with configured menu items';

-- --------------------------------------------------------

--
-- Table structure for table `pma__users`
--

CREATE TABLE `pma__users` (
  `username` varchar(64) NOT NULL,
  `usergroup` varchar(64) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Users and their assignments to user groups';

--
-- Indexes for dumped tables
--

--
-- Indexes for table `pma__bookmark`
--
ALTER TABLE `pma__bookmark`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `pma__central_columns`
--
ALTER TABLE `pma__central_columns`
  ADD PRIMARY KEY (`db_name`,`col_name`);

--
-- Indexes for table `pma__column_info`
--
ALTER TABLE `pma__column_info`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `db_name` (`db_name`,`table_name`,`column_name`);

--
-- Indexes for table `pma__designer_settings`
--
ALTER TABLE `pma__designer_settings`
  ADD PRIMARY KEY (`username`);

--
-- Indexes for table `pma__export_templates`
--
ALTER TABLE `pma__export_templates`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `u_user_type_template` (`username`,`export_type`,`template_name`);

--
-- Indexes for table `pma__favorite`
--
ALTER TABLE `pma__favorite`
  ADD PRIMARY KEY (`username`);

--
-- Indexes for table `pma__history`
--
ALTER TABLE `pma__history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `username` (`username`,`db`,`table`,`timevalue`);

--
-- Indexes for table `pma__navigationhiding`
--
ALTER TABLE `pma__navigationhiding`
  ADD PRIMARY KEY (`username`,`item_name`,`item_type`,`db_name`,`table_name`);

--
-- Indexes for table `pma__pdf_pages`
--
ALTER TABLE `pma__pdf_pages`
  ADD PRIMARY KEY (`page_nr`),
  ADD KEY `db_name` (`db_name`);

--
-- Indexes for table `pma__recent`
--
ALTER TABLE `pma__recent`
  ADD PRIMARY KEY (`username`);

--
-- Indexes for table `pma__relation`
--
ALTER TABLE `pma__relation`
  ADD PRIMARY KEY (`master_db`,`master_table`,`master_field`),
  ADD KEY `foreign_field` (`foreign_db`,`foreign_table`);

--
-- Indexes for table `pma__savedsearches`
--
ALTER TABLE `pma__savedsearches`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `u_savedsearches_username_dbname` (`username`,`db_name`,`search_name`);

--
-- Indexes for table `pma__table_coords`
--
ALTER TABLE `pma__table_coords`
  ADD PRIMARY KEY (`db_name`,`table_name`,`pdf_page_number`);

--
-- Indexes for table `pma__table_info`
--
ALTER TABLE `pma__table_info`
  ADD PRIMARY KEY (`db_name`,`table_name`);

--
-- Indexes for table `pma__table_uiprefs`
--
ALTER TABLE `pma__table_uiprefs`
  ADD PRIMARY KEY (`username`,`db_name`,`table_name`);

--
-- Indexes for table `pma__tracking`
--
ALTER TABLE `pma__tracking`
  ADD PRIMARY KEY (`db_name`,`table_name`,`version`);

--
-- Indexes for table `pma__userconfig`
--
ALTER TABLE `pma__userconfig`
  ADD PRIMARY KEY (`username`);

--
-- Indexes for table `pma__usergroups`
--
ALTER TABLE `pma__usergroups`
  ADD PRIMARY KEY (`usergroup`,`tab`,`allowed`);

--
-- Indexes for table `pma__users`
--
ALTER TABLE `pma__users`
  ADD PRIMARY KEY (`username`,`usergroup`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `pma__bookmark`
--
ALTER TABLE `pma__bookmark`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pma__column_info`
--
ALTER TABLE `pma__column_info`
  MODIFY `id` int(5) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pma__export_templates`
--
ALTER TABLE `pma__export_templates`
  MODIFY `id` int(5) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `pma__history`
--
ALTER TABLE `pma__history`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pma__pdf_pages`
--
ALTER TABLE `pma__pdf_pages`
  MODIFY `page_nr` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pma__savedsearches`
--
ALTER TABLE `pma__savedsearches`
  MODIFY `id` int(5) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- Database: `prfs`
--
CREATE DATABASE IF NOT EXISTS `prfs` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `prfs`;

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
(1, 'Ferry 001', 14.565300, 121.055800, '2025-05-03 00:38:00', NULL, 'active', NULL, '2025-05-02 09:45:39', 30, 27, 0, 5),
(2, 'Ferry 215', 14.555000, 121.073100, '2025-05-03 00:38:00', NULL, 'active', NULL, '2025-05-02 09:45:48', 30, 0, 0, 5),
(3, 'Ferry 212', 14.562200, 121.061400, '2025-05-03 00:38:00', NULL, 'active', NULL, '2025-05-02 06:35:54', 30, 24, 0, 5),
(4, 'Ferry 241', 14.555000, 121.073100, '2025-05-03 00:38:00', NULL, 'active', NULL, '2025-05-01 08:14:51', 30, 0, 0, 5);

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
--
-- Database: `test`
--
CREATE DATABASE IF NOT EXISTS `test` DEFAULT CHARACTER SET latin1 COLLATE latin1_swedish_ci;
USE `test`;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
