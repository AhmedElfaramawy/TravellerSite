-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3307
-- Generation Time: 14 مايو 2025 الساعة 16:15
-- إصدار الخادم: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `flight_booking`
--

-- --------------------------------------------------------

--
-- بنية الجدول `bookings`
--

CREATE TABLE `bookings` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `flight_id` int(11) NOT NULL,
  `booking_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `num_travelers` int(11) NOT NULL DEFAULT 1,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone_number` varchar(20) NOT NULL,
  `birth_date` date NOT NULL,
  `gender` enum('male','female') NOT NULL,
  `passport_number` varchar(20) NOT NULL,
  `nationality` varchar(50) NOT NULL,
  `passport_expiration` date NOT NULL,
  `seats` int(11) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `bookings`
--

INSERT INTO `bookings` (`id`, `user_id`, `flight_id`, `booking_date`, `num_travelers`, `first_name`, `last_name`, `email`, `phone_number`, `birth_date`, `gender`, `passport_number`, `nationality`, `passport_expiration`, `seats`) VALUES
(33, 3, 107, '2025-05-14 12:59:05', 1, 'Ahmed', 'Elfaramawy', 'ahmeddragonred@gmail.com', '01212225926', '2003-06-15', '', '15645464', 'EG', '2025-11-25', 1);

-- --------------------------------------------------------

--
-- بنية الجدول `flights`
--

CREATE TABLE `flights` (
  `id` int(11) NOT NULL,
  `flight_number` varchar(20) NOT NULL,
  `departure` varchar(100) NOT NULL,
  `destination` varchar(100) NOT NULL,
  `departure_time` datetime NOT NULL,
  `arrival_time` datetime NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `seats_available` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `flights`
--

INSERT INTO `flights` (`id`, `flight_number`, `departure`, `destination`, `departure_time`, `arrival_time`, `price`, `seats_available`) VALUES
(107, 'FL001', 'Cairo', 'Dubai', '2025-05-15 08:00:00', '2025-05-15 12:00:00', 350.00, 120),
(108, 'FL002', 'Cairo', 'London', '2025-05-16 10:30:00', '2025-05-16 14:30:00', 450.00, 100),
(109, 'FL003', 'Cairo', 'Paris', '2025-05-17 09:15:00', '2025-05-17 13:45:00', 400.00, 80),
(110, 'FL004', 'Cairo', 'New York', '2025-05-18 23:00:00', '2025-05-19 06:30:00', 700.00, 150),
(111, 'FL005', 'Dubai', 'Cairo', '2025-05-20 14:00:00', '2025-05-20 18:00:00', 370.00, 110),
(112, 'FL006', 'Dubai', 'London', '2025-05-21 01:30:00', '2025-05-21 06:30:00', 520.00, 95),
(113, 'FL007', 'Dubai', 'Tokyo', '2025-05-22 22:45:00', '2025-05-23 13:15:00', 850.00, 200),
(114, 'FL008', 'London', 'Cairo', '2025-05-23 11:20:00', '2025-05-23 15:50:00', 460.00, 85),
(115, 'FL009', 'London', 'New York', '2025-05-24 17:00:00', '2025-05-24 20:30:00', 600.00, 130),
(116, 'FL010', 'London', 'Sydney', '2025-05-25 19:45:00', '2025-05-26 17:30:00', 1200.00, 175);

-- --------------------------------------------------------

--
-- بنية الجدول `payments`
--

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `booking_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_status` enum('paid','pending') DEFAULT 'pending',
  `payment_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- بنية الجدول `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone_number` varchar(20) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('passenger','admin') DEFAULT 'passenger'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `phone_number`, `password`, `role`) VALUES
(3, 'AhmedMohamed', 'ahmeddragonred@gmail.com', '01212225926', '$2y$10$2LPvv.L9JJS.DftQR.mjBeS8qIZfZqSusg0uoYHE9SVp9ZBjacp/6', 'passenger'),
(4, 'AhmedElfaramawy', 'ahmedelfaramawy@gmail.com', '01002251585', '$2y$10$e0ag.2iF3Iq22jwbAEyd2evVKQnQNeks8TFF6KUAwi9qqtqw75E6G', 'passenger'),
(6, 'Test User', 'user@example.com', NULL, '$2y$10$8tPRwX0jAKSYle1XRm/Wd.iCcSIH0xIZnOL0L3QvUUzs5.XVSjqSe', 'passenger'),
(11, 'Admin User', 'admin@example.com', NULL, '$2y$10$/LA/u9ArWGu3b/dILu3UZ.xgsIhMGvo83xoLt7UA8yud/x.1aoYre', 'admin');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `flight_id` (`flight_id`);

--
-- Indexes for table `flights`
--
ALTER TABLE `flights`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `flight_number` (`flight_number`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `booking_id` (`booking_id`);

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
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT for table `flights`
--
ALTER TABLE `flights`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=117;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- قيود الجداول المُلقاة.
--

--
-- قيود الجداول `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `bookings_ibfk_2` FOREIGN KEY (`flight_id`) REFERENCES `flights` (`id`) ON DELETE CASCADE;

--
-- قيود الجداول `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
