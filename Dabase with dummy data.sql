-- phpMyAdmin SQL Dump
-- version 4.8.4
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Aug 14, 2023 at 05:08 PM
-- Server version: 5.7.24
-- PHP Version: 7.2.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `ref`
--

-- --------------------------------------------------------

--
-- Table structure for table `clients`
--

DROP TABLE IF EXISTS `clients`;
CREATE TABLE IF NOT EXISTS `clients` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `company_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `city` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `reviews_count` int(11) DEFAULT NULL,
  `company_address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `category` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `website` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `google_maps_link` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `clients`
--

INSERT INTO `clients` (`id`, `company_name`, `city`, `reviews_count`, `company_address`, `category`, `website`, `google_maps_link`) VALUES
(1, 'Victory Martial Arts', 'City 1', 3, 'Address 1', 'Category 1', 'https://www.example.com', 'https://maps.google.com/maps?q=Company+Name'),
(2, 'Victory Martial Arts', 'City 2', 4, 'Address 2', 'Category 1', 'https://www.example.com', 'https://maps.google.com/maps?q=Company+Name'),
(3, 'Victory Martial Arts', 'City 3', 3, 'Address 3', 'Category 1', 'https://www.example.com', 'https://maps.google.com/maps?q=Company+Name'),
(4, 'ABC Electronics', 'New York', 10, '123 Main St', 'Electronics', 'https://www.abcelectronics.com', 'https://maps.google.com/abc');

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

DROP TABLE IF EXISTS `reviews`;
CREATE TABLE IF NOT EXISTS `reviews` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `client_id` int(11) DEFAULT NULL,
  `stars` float DEFAULT NULL,
  `published_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_client_id` (`client_id`)
) ENGINE=MyISAM AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `reviews`
--

INSERT INTO `reviews` (`id`, `client_id`, `stars`, `published_at`) VALUES
(1, 1, 1, '2023-07-31 22:00:00'),
(2, 1, 4, '2023-08-04 22:00:00'),
(3, 1, 1, '2023-08-09 22:00:00'),
(4, 2, 3, '2023-08-01 22:00:00'),
(5, 2, 2, '2023-08-05 22:00:00'),
(6, 2, 3, '2023-08-10 22:00:00'),
(7, 3, 5, '2023-08-02 22:00:00'),
(8, 3, 5, '2023-08-06 22:00:00'),
(9, 3, 4, '2023-08-11 22:00:00'),
(10, 2, 3, '2023-08-01 22:00:00');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
