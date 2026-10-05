-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 12, 2026 at 10:14 AM
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
-- Database: `fuelcrisis`
--

-- --------------------------------------------------------

--
-- Table structure for table `fuel_market_prices`
--

CREATE TABLE `fuel_market_prices` (
  `id` int(11) NOT NULL,
  `fuel_type` varchar(50) NOT NULL,
  `price_per_liter` float NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `fuel_market_prices`
--

INSERT INTO `fuel_market_prices` (`id`, `fuel_type`, `price_per_liter`, `updated_at`) VALUES
(1, 'petrol', 12, '2026-06-12 07:04:33'),
(2, 'diesel', 10, '2026-06-12 07:04:33');

-- --------------------------------------------------------

--
-- Table structure for table `logs`
--

CREATE TABLE `logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(255) NOT NULL,
  `details` text DEFAULT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `logs`
--

INSERT INTO `logs` (`id`, `user_id`, `action`, `details`, `timestamp`) VALUES
(1, 3, 'Request Token', 'Requested 100 L petrol at station ID 1. Serial: #1', '2026-06-12 07:02:57'),
(2, 2, 'Call Token', 'Called token #1 at station ID 1', '2026-06-12 07:03:38'),
(3, 2, 'Complete Token', 'Completed token #1. Deducted 100 L petrol from station ID 1', '2026-06-12 07:03:49'),
(4, 2, 'Toggle Status', 'Toggled station ID 1', '2026-06-12 07:04:00'),
(5, 1, 'Update Price', 'Updated Petrol to 12 and Diesel to 10', '2026-06-12 07:04:33'),
(6, 1, 'Edit Station', 'Edited station ID: 1', '2026-06-12 07:04:51'),
(7, 3, 'Request Token', 'Requested 20 L diesel at station ID 2. Serial: #1', '2026-06-12 07:05:22'),
(8, 2, 'Call Token', 'Called token #1 at station ID 2', '2026-06-12 07:06:10'),
(9, 2, 'Complete Token', 'Completed token #1. Deducted 20 L diesel from station ID 2', '2026-06-12 07:06:20'),
(10, 3, 'Request Token', 'Requested 100 L petrol at station ID 2. Serial: #2', '2026-06-12 07:08:13'),
(11, 1, 'Call Token', 'Called token #2 at station ID 2', '2026-06-12 07:08:23'),
(12, 1, 'Complete Token', 'Completed token #2. Deducted 100 L petrol from station ID 2', '2026-06-12 07:08:25'),
(13, 1, 'Toggle Status', 'Toggled station ID 1', '2026-06-12 07:09:15'),
(14, 3, 'Request Token', 'Requested 100 L petrol at station ID 1. Serial: #2', '2026-06-12 07:09:32'),
(15, 1, 'Call Token', 'Called token #2 at station ID 1', '2026-06-12 07:09:40'),
(16, 1, 'Complete Token', 'Completed token #2. Deducted 100 L petrol from station ID 1', '2026-06-12 07:09:41'),
(17, 1, 'Toggle Ban', 'Toggled User ID 3 status to Banned', '2026-06-12 07:13:18'),
(18, 1, 'Toggle Ban', 'Toggled User ID 3 status to Active', '2026-06-12 07:13:27'),
(19, 3, 'Request Token', 'Requested 50 L petrol at station ID 2. Serial: #3', '2026-06-12 07:13:43'),
(20, 1, 'Call Token', 'Called token #3 at station ID 2', '2026-06-12 07:13:54'),
(21, 1, 'Complete Token', 'Completed token #3. Deducted 50 L petrol from station ID 2', '2026-06-12 07:13:56'),
(22, 4, 'Request Token', 'Requested 100 L diesel at station ID 2. Serial: #4', '2026-06-12 07:19:54'),
(23, 3, 'Request Token', 'Requested 100 L diesel at station ID 2. Serial: #5', '2026-06-12 07:20:06'),
(24, 1, 'Call Token', 'Called token #4 at station ID 2', '2026-06-12 07:20:27'),
(25, 1, 'Complete Token', 'Completed token #4. Deducted 100 L diesel from station ID 2', '2026-06-12 07:20:36'),
(26, 1, 'Call Token', 'Called token #5 at station ID 2', '2026-06-12 07:20:48'),
(27, 1, 'Complete Token', 'Completed token #5. Deducted 100 L diesel from station ID 2', '2026-06-12 07:20:50'),
(28, 3, 'Request Token', 'Requested 100 L petrol at station ID 1. Serial: #3', '2026-06-12 07:31:57'),
(29, 1, 'Call Token', 'Called token #3 at station ID 1', '2026-06-12 07:32:48'),
(30, 1, 'Complete Token', 'Completed token #3. Deducted 100 L petrol from station ID 1', '2026-06-12 07:33:05'),
(31, 1, 'Add Station', 'Added station: S T Power Limited CNG', '2026-06-12 08:07:00'),
(32, 1, 'Delete Station', 'Deleted station ID: 1', '2026-06-12 08:07:09'),
(33, 1, 'Delete Station', 'Deleted station ID: 2', '2026-06-12 08:07:11'),
(34, 1, 'Add Station', 'Added station: Makka CNG', '2026-06-12 08:07:51'),
(35, 1, 'Add Station', 'Added station: Gulshan Service Station', '2026-06-12 08:08:20'),
(36, 1, 'Add Station', 'Added station: Dhaka CNG Ltd.', '2026-06-12 08:11:44'),
(37, 1, 'Add Station', 'Added station: Cosmo Filling Station', '2026-06-12 08:12:28');

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `station_id` int(11) NOT NULL,
  `comment` text NOT NULL,
  `rating` tinyint(1) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `stations`
--

CREATE TABLE `stations` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `location` varchar(255) NOT NULL,
  `location_x` decimal(10,2) DEFAULT 0.00,
  `location_y` decimal(10,2) DEFAULT 0.00,
  `on_off_status` enum('on','off') DEFAULT 'on',
  `map_link` text DEFAULT NULL,
  `img_url` text DEFAULT NULL,
  `petrol_stock` float DEFAULT 0,
  `diesel_stock` float DEFAULT 0,
  `status` enum('AVAILABLE','NO STOCK') DEFAULT 'AVAILABLE',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `stations`
--

INSERT INTO `stations` (`id`, `name`, `location`, `location_x`, `location_y`, `on_off_status`, `map_link`, `img_url`, `petrol_stock`, `diesel_stock`, `status`, `created_at`) VALUES
(3, 'S T Power Limited CNG', 'Badda, Link Road', 12.50, 18.00, 'on', 'https://maps.app.goo.gl/whEtGZJ3VU29Nfoa8', 'https://cdn.discordapp.com/attachments/1126155546458865749/1514652058542801087/Screenshot_20260611_212617_Maps.jpg?ex=6a2ccde7&is=6a2b7c67&hm=0e500dfafc8c879db2e70affcba9117e69612eb78ad784927797ec2927f1041a&', 0, 0, 'AVAILABLE', '2026-06-12 08:07:00'),
(4, 'Makka CNG', 'Badda, Link Road', 14.00, 16.75, 'on', 'https://maps.app.goo.gl/ey7zxQvjD4VZxL636', 'https://cdn.discordapp.com/attachments/1126155546458865749/1514653796570235231/Screenshot_20260611_213256_Maps.jpg?ex=6a2ccf86&is=6a2b7e06&hm=85b3d44b696e5a607f7b8abcda6e8ff5f46fcd4763554d8b5c9b5334adbd273b&', 0, 0, 'AVAILABLE', '2026-06-12 08:07:51'),
(5, 'Gulshan Service Station', 'Gulshan', 21.20, 25.00, 'on', 'https://maps.app.goo.gl/GNFKNwep1R9KAm6L8', 'https://cdn.discordapp.com/attachments/1126155546458865749/1514654614283227379/Screenshot_20260611_213618_Maps.jpg?ex=6a2cd048&is=6a2b7ec8&hm=b6c9a645ee94e4f63a268c265f97f0955d52abadebfe6c4a652ad9f6f6e8af45&', 0, 0, 'AVAILABLE', '2026-06-12 08:08:20'),
(6, 'Dhaka CNG Ltd.', 'Mirpur-10', 9.00, 12.50, 'on', 'https://maps.app.goo.gl/1Yaf5UjDyNUNXRDX8', 'https://cdn.discordapp.com/attachments/1126155546458865749/1514655188038975610/Screenshot_20260611_213842_Maps.jpg?ex=6a2cd0d1&is=6a2b7f51&hm=dfbba5f1af0f4222e8b971f34f90a0769ef5c168b2012e0a420aca33490e8cf3&', 0, 0, 'AVAILABLE', '2026-06-12 08:11:44'),
(7, 'Cosmo Filling Station', 'Uttara', 27.80, 19.30, 'on', 'https://maps.app.goo.gl/v2mCYPfDjkc3K8Rj9', 'https://cdn.discordapp.com/attachments/1126155546458865749/1514655719469617282/Screenshot_20260611_214050_Maps.jpg?ex=6a2cd150&is=6a2b7fd0&hm=b21fb66848c9ff2dc22c9ba674f8f6797689f12eb9e82f23fe4d7ce11378f547&', 0, 0, 'AVAILABLE', '2026-06-12 08:12:28');

-- --------------------------------------------------------

--
-- Table structure for table `vehicle_daily_limits`
--

CREATE TABLE `vehicle_daily_limits` (
  `vehicle_type` enum('bike','car') NOT NULL,
  `daily_limit` decimal(8,2) NOT NULL,
  PRIMARY KEY (`vehicle_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `vehicle_daily_limits` (`vehicle_type`, `daily_limit`) VALUES
('bike', 50.00),
('car', 100.00);

-- --------------------------------------------------------

--
-- Table structure for table `tokens`
--

CREATE TABLE `tokens` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `station_id` int(11) NOT NULL,
  `car_id` int(11) DEFAULT NULL,
  `vehicle_type` enum('bike','car') NOT NULL DEFAULT 'car',
  `fuel_type` varchar(50) NOT NULL,
  `liters` float NOT NULL,
  `serial_number` int(11) NOT NULL,
  `status` enum('Pending','Called','Completed') DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cars`
--

CREATE TABLE `cars` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `car_id` int(11) NOT NULL,
  `car_type` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `location` varchar(255) DEFAULT NULL,
  `location_x` decimal(10,2) DEFAULT 0.00,
  `location_y` decimal(10,2) DEFAULT 0.00,
  `password` varchar(255) NOT NULL,
  `role` varchar(50) DEFAULT 'user',
  `status` enum('Active','Banned') DEFAULT 'Active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `full_name`, `email`, `location_x`, `location_y`, `password`, `role`, `status`) VALUES
(1, 'Admin User', 'admin@gmail.com', 12.50, 18.00, 'admin', 'admin', 'Active'),
(2, 'Staff User', 'staff@gmail.com', 15.75, 21.00, 'staff', 'staff', 'Active'),
(3, 'maimun', 'maimun@gmail.com', 9.25, 11.50, '12345', 'user', 'Active'),
(4, 'maruf', 'maruf@gmail.com', 18.30, 14.75, '1234', 'user', 'Active'),
(5, 'Jamal', 'jamal@gmail.com', 22.00, 27.50, 'jamal123', 'user', 'Active'),
(6, 'Nadia', 'nadia@gmail.com', 6.80, 19.20, 'nadia123', 'user', 'Active'),
(7, 'Rafi', 'rafi@gmail.com', 28.40, 10.60, 'rafi123', 'user', 'Active');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `fuel_market_prices`
--
ALTER TABLE `fuel_market_prices`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `fuel_type` (`fuel_type`);

--
-- Indexes for table `logs`
--
ALTER TABLE `logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `station_id` (`station_id`);

--
-- Indexes for table `stations`
--
ALTER TABLE `stations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tokens`
--
ALTER TABLE `tokens`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `station_id` (`station_id`),
  ADD KEY `idx_user_car` (`user_id`, `car_id`),
  ADD KEY `idx_global_vehicle_daily_usage` (`user_id`, `vehicle_type`, `created_at`);

--
-- Indexes for table `cars`
--
ALTER TABLE `cars`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_car_id` (`user_id`, `car_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `fuel_market_prices`
--
ALTER TABLE `fuel_market_prices`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `logs`
--
ALTER TABLE `logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `stations`
--
ALTER TABLE `stations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `tokens`
--
ALTER TABLE `tokens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `cars`
--
ALTER TABLE `cars`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `logs`
--
ALTER TABLE `logs`
  ADD CONSTRAINT `logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`station_id`) REFERENCES `stations` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tokens`
--
ALTER TABLE `tokens`
  ADD CONSTRAINT `tokens_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tokens_ibfk_2` FOREIGN KEY (`station_id`) REFERENCES `stations` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_tokens_car` FOREIGN KEY (`user_id`, `car_id`) REFERENCES `cars` (`user_id`, `car_id`) ON DELETE RESTRICT ON UPDATE CASCADE;

--
-- Constraints for table `cars`
--
ALTER TABLE `cars`
  ADD CONSTRAINT `cars_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
