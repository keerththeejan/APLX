-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Oct 18, 2025 at 08:52 AM
-- Server version: 8.3.0
-- PHP Version: 8.2.18

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `parcel_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin_messages`
--

DROP TABLE IF EXISTS `admin_messages`;
CREATE TABLE IF NOT EXISTS `admin_messages` (
  `id` int NOT NULL AUTO_INCREMENT,
  `customer_id` int NOT NULL,
  `customer_name` varchar(120) NOT NULL,
  `customer_email` varchar(150) NOT NULL,
  `subject` varchar(200) NOT NULL,
  `body` mediumtext NOT NULL,
  `delivery_status` enum('queued','sent','failed') NOT NULL DEFAULT 'queued',
  `admin_message` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `sent_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_admin_messages_customer` (`customer_id`),
  KEY `idx_admin_messages_status_created` (`delivery_status`,`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `admin_profile`
--

DROP TABLE IF EXISTS `admin_profile`;
CREATE TABLE IF NOT EXISTS `admin_profile` (
  `id` int NOT NULL,
  `admin_id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(120) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password_hash` varchar(255) DEFAULT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_admin` (`admin_id`),
  UNIQUE KEY `uniq_admin_email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `admin_profile`
--

INSERT INTO `admin_profile` (`id`, `admin_id`, `name`, `email`, `password_hash`, `phone`, `created_at`, `updated_at`) VALUES
(0, 2, 'Administrator', 'admin@parcel.local', '$2y$10$bTlQ./xwM/m5OKgGor5DMe09W57viKeEuYmE4PggJYT7334QQFpKa', '', '2025-10-18 05:46:35', '2025-10-18 05:46:35'),
(1, 1, 'Saravanyaa', 'saravanyaa1@gmail.com', '1234', '56789', '2025-10-07 16:35:31', '2025-10-07 16:36:24');

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

DROP TABLE IF EXISTS `bookings`;
CREATE TABLE IF NOT EXISTS `bookings` (
  `id` int NOT NULL AUTO_INCREMENT,
  `tracking_number` varchar(32) DEFAULT NULL,
  `sender_name` varchar(120) NOT NULL,
  `receiver_name` varchar(120) NOT NULL,
  `origin` varchar(120) NOT NULL,
  `destination` varchar(120) NOT NULL,
  `weight` decimal(10,2) NOT NULL DEFAULT '0.00',
  `price` decimal(10,2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `tracking_number` (`tracking_number`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`id`, `tracking_number`, `sender_name`, `receiver_name`, `origin`, `destination`, `weight`, `price`, `created_at`) VALUES
(1, 'EC33056729A1', 'Srithevi', 'Aravinthan', 'Mullaithivu', 'Mullaithivu', 10.00, 1000.00, '2025-10-07 15:52:35'),
(2, 'D37378F67A91', 'Aravinthan', 'Srithevi', 'Mullaithivu', 'Mullaithivu', 5.00, NULL, '2025-10-14 04:57:09'),
(3, 'B4E89190DC70', 'Aravinthan', 'Srithevi', 'Mullaithivu', 'Mullaithivu', 5.00, NULL, '2025-10-14 04:57:31'),
(4, '7CEF842495E3', 'Shathana', 'Srithevi', 'Mullaithivu', 'Mullaithivu', 5.00, NULL, '2025-10-14 05:09:13'),
(5, 'E3A1949CBF99', 'Aravinthan', 'Srithevi', 'Mullaithivu', 'Mullaithivu', 10.00, NULL, '2025-10-14 05:25:36'),
(6, '1535D801A991', 'Aravinthan', 'Srithevi', 'Mullaithivu', 'Mullaithivu', 85.00, NULL, '2025-10-14 05:33:37'),
(7, '2F14FD8EA5B7', 'Aravinthan', 'Srithevi', 'Mullaithivu', 'Mullaithivu', 50.00, NULL, '2025-10-14 05:36:03');

-- --------------------------------------------------------

--
-- Table structure for table `customer`
--

DROP TABLE IF EXISTS `customer`;
CREATE TABLE IF NOT EXISTS `customer` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(120) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `phone` varchar(30) NOT NULL,
  `address` varchar(255) NOT NULL,
  `district` varchar(80) NOT NULL,
  `province` varchar(80) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `customer`
--

INSERT INTO `customer` (`id`, `name`, `email`, `password_hash`, `phone`, `address`, `district`, `province`, `created_at`) VALUES
(1, 'Srithevi', 'srithevi2025@gmail.com', '$2y$10$3/HpulMk5xnfOdvPQ4gq.e9QEe0NAqMV9Qfwq9ITaxLxvGSsCvZXK', '0772912755', 'Visuvamadu', 'Mullaitivu', 'Northern', '2025-10-10 08:58:22'),
(2, 'Aravinthan', 'shathana2312@gmail.com', '$2y$10$sIXM.q32jlZFAIs9ocGfBOg8nBwF.OvP.UhcMp21lmpol/gUQbLKm', '0774467577', 'Visuvamadu', 'Mullaitivu', 'Northern', '2025-10-13 14:52:23'),
(3, 'Shathana', 'tydy@gmail.com', '$2y$10$eNKHCEKYujeo7TNfP3zaqOw3ENIsyDqv148JYaR.kG08n1qW4cfYm', '088484', 'yfyd6t', 'Kilinochchi', 'Northern', '2025-10-17 05:56:43');

-- --------------------------------------------------------

--
-- Table structure for table `gallery`
--

DROP TABLE IF EXISTS `gallery`;
CREATE TABLE IF NOT EXISTS `gallery` (
  `id` int NOT NULL AUTO_INCREMENT,
  `image_url` varchar(512) NOT NULL,
  `tag` varchar(60) DEFAULT NULL,
  `day` tinyint UNSIGNED DEFAULT NULL,
  `month` varchar(12) DEFAULT NULL,
  `sort_order` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hero_banners`
--

DROP TABLE IF EXISTS `hero_banners`;
CREATE TABLE IF NOT EXISTS `hero_banners` (
  `id` int NOT NULL AUTO_INCREMENT,
  `eyebrow` varchar(150) NOT NULL DEFAULT 'Safe Transportation & Logistics',
  `title` varchar(200) NOT NULL DEFAULT '',
  `subtitle` varchar(200) NOT NULL DEFAULT '',
  `tagline` varchar(300) NOT NULL DEFAULT '',
  `cta1_text` varchar(80) NOT NULL DEFAULT 'Get Started',
  `cta1_link` varchar(300) NOT NULL DEFAULT '/APLX/frontend/login.php',
  `cta2_text` varchar(80) NOT NULL DEFAULT 'Learn More',
  `cta2_link` varchar(300) NOT NULL DEFAULT '#',
  `image_url` varchar(600) NOT NULL DEFAULT '',
  `sort_order` int NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_active_order` (`is_active`,`sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hero_settings`
--

DROP TABLE IF EXISTS `hero_settings`;
CREATE TABLE IF NOT EXISTS `hero_settings` (
  `id` tinyint UNSIGNED NOT NULL DEFAULT '1',
  `eyebrow` varchar(150) NOT NULL DEFAULT 'Safe Transportation & Logistics',
  `title` varchar(200) NOT NULL DEFAULT '',
  `subtitle` varchar(200) NOT NULL DEFAULT '',
  `tagline` varchar(300) NOT NULL DEFAULT '',
  `cta1_text` varchar(80) NOT NULL DEFAULT 'Get Started',
  `cta1_link` varchar(300) NOT NULL DEFAULT '/APLX/frontend/login.php',
  `cta2_text` varchar(80) NOT NULL DEFAULT 'Learn More',
  `cta2_link` varchar(300) NOT NULL DEFAULT '#',
  `background_url` varchar(600) NOT NULL DEFAULT '',
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `mail_logs`
--

DROP TABLE IF EXISTS `mail_logs`;
CREATE TABLE IF NOT EXISTS `mail_logs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `recipient_type` enum('admin','customer') NOT NULL,
  `recipient_email` varchar(255) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `status` varchar(32) NOT NULL DEFAULT 'sent',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `mail_logs`
--

INSERT INTO `mail_logs` (`id`, `recipient_type`, `recipient_email`, `subject`, `status`, `created_at`) VALUES
(1, 'customer', 'srithevi2025@gmail.com', 'Welcome to Parcel Transport – Booking Details Inside', 'failed', '2025-10-10 08:58:24'),
(2, 'customer', 'shathana2312@gmail.com', 'Welcome to Parcel Transport – Booking Details Inside', 'failed', '2025-10-13 14:52:25'),
(3, 'customer', 'tydy@gmail.com', 'Welcome to Parcel Transport – Booking Details Inside', 'failed', '2025-10-17 05:56:46');

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
CREATE TABLE IF NOT EXISTS `migrations` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `applied_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `name`, `applied_at`) VALUES
(1, '0001_create_users.php', '2025-10-18 07:44:00'),
(2, '0002_create_services.php', '2025-10-18 07:44:00'),
(3, '0003_create_gallery.php', '2025-10-18 07:44:00'),
(4, '0004_create_site_contact.php', '2025-10-18 07:44:00'),
(5, '0005_create_customer.php', '2025-10-18 07:44:00'),
(6, '0006_create_hero_settings.php', '2025-10-18 07:44:00');

-- --------------------------------------------------------

--
-- Table structure for table `services`
--

DROP TABLE IF EXISTS `services`;
CREATE TABLE IF NOT EXISTS `services` (
  `id` int NOT NULL AUTO_INCREMENT,
  `icon` varchar(16) DEFAULT NULL,
  `image_url` varchar(512) DEFAULT NULL,
  `title` varchar(120) NOT NULL,
  `description` varchar(500) NOT NULL,
  `sort_order` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `shipments`
--

DROP TABLE IF EXISTS `shipments`;
CREATE TABLE IF NOT EXISTS `shipments` (
  `id` int NOT NULL AUTO_INCREMENT,
  `tracking_number` varchar(32) NOT NULL,
  `sender_name` varchar(120) NOT NULL,
  `receiver_name` varchar(120) NOT NULL,
  `origin` varchar(120) NOT NULL,
  `destination` varchar(120) NOT NULL,
  `weight` decimal(10,2) NOT NULL DEFAULT '0.00',
  `price` decimal(10,2) DEFAULT NULL,
  `status` enum('Booked','In Transit','Delivered','Cancelled') NOT NULL DEFAULT 'Booked',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `tracking_number` (`tracking_number`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `shipments`
--

INSERT INTO `shipments` (`id`, `tracking_number`, `sender_name`, `receiver_name`, `origin`, `destination`, `weight`, `price`, `status`, `created_at`, `updated_at`) VALUES
(1, 'EC33056729A1', 'Srithevi', 'Aravinthan', 'Mullaithivu', 'Mullaithivu', 10.00, 1000.00, 'Booked', '2025-10-07 15:52:35', '2025-10-07 15:52:35'),
(2, 'D37378F67A91', 'Aravinthan', 'Srithevi', 'Mullaithivu', 'Mullaithivu', 5.00, NULL, 'Booked', '2025-10-14 04:57:09', '2025-10-14 04:57:09'),
(3, 'B4E89190DC70', 'Aravinthan', 'Srithevi', 'Mullaithivu', 'Mullaithivu', 5.00, NULL, 'Booked', '2025-10-14 04:57:31', '2025-10-14 04:57:31'),
(4, '7CEF842495E3', 'Shathana', 'Srithevi', 'Mullaithivu', 'Mullaithivu', 5.00, NULL, 'Booked', '2025-10-14 05:09:13', '2025-10-14 05:09:13'),
(5, 'E3A1949CBF99', 'Aravinthan', 'Srithevi', 'Mullaithivu', 'Mullaithivu', 10.00, NULL, 'Booked', '2025-10-14 05:25:36', '2025-10-14 05:25:36'),
(6, '1535D801A991', 'Aravinthan', 'Srithevi', 'Mullaithivu', 'Mullaithivu', 85.00, NULL, 'Booked', '2025-10-14 05:33:37', '2025-10-14 05:33:37'),
(7, '2F14FD8EA5B7', 'Aravinthan', 'Srithevi', 'Mullaithivu', 'Mullaithivu', 50.00, NULL, 'Booked', '2025-10-14 05:36:03', '2025-10-14 05:36:03');

-- --------------------------------------------------------

--
-- Table structure for table `site_contact`
--

DROP TABLE IF EXISTS `site_contact`;
CREATE TABLE IF NOT EXISTS `site_contact` (
  `id` tinyint UNSIGNED NOT NULL DEFAULT '1',
  `address` varchar(255) NOT NULL DEFAULT '',
  `phone` varchar(80) NOT NULL DEFAULT '',
  `email` varchar(150) NOT NULL DEFAULT '',
  `hours_weekday` varchar(120) NOT NULL DEFAULT 'Mon - Fri: 8:30 AM - 4:15 PM',
  `hours_sat` varchar(120) NOT NULL DEFAULT 'Sat: 9:00 AM - 2:00 PM',
  `hours_sun` varchar(120) NOT NULL DEFAULT 'Sun: Closed',
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(120) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` varchar(32) NOT NULL DEFAULT 'customer',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_user_email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password_hash`, `role`, `created_at`) VALUES
(1, 'Administrator', 'admin@parcel.local', '$2y$10$TVhbMqB5L2W.1XyVM6xQKucXlfKB2a/GDolF8khiiaKv8jXsL80tW', 'admin', '2025-10-18 06:04:03'),
(2, 'Ops Admin', 'ops@parcel.local', '$2y$10$He2I2YHC8s1BmblpPZq1GeqvAIyEa9AJivf7Kq6CpX.GdQPvfZePW', 'admin', '2025-10-18 06:06:38');

--
-- Constraints for dumped tables
--

--
-- Constraints for table `admin_messages`
--
ALTER TABLE `admin_messages`
  ADD CONSTRAINT `fk_admin_msg_customer` FOREIGN KEY (`customer_id`) REFERENCES `customer` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
