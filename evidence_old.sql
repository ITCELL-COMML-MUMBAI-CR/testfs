-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Sep 17, 2025 at 09:35 PM
-- Server version: 11.8.3-MariaDB-log
-- PHP Version: 7.2.34

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `u473452443_sampark`
--

-- --------------------------------------------------------

--
-- Table structure for table `evidence`
--

CREATE TABLE `evidence` (
  `id` int(11) NOT NULL,
  `complaint_id` varchar(50) NOT NULL,
  `image_1` varchar(255) DEFAULT NULL,
  `image_2` varchar(255) DEFAULT NULL,
  `image_3` varchar(255) DEFAULT NULL,
  `uploaded_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `evidence`
--

INSERT INTO `evidence` (`id`, `complaint_id`, `image_1`, `image_2`, `image_3`, `uploaded_at`) VALUES
(1, 'CMP202508153867', 'CMP202508153867_1_1755234200.jpeg', NULL, NULL, '2025-08-15 10:33:20'),
(2, 'CMP202508157119', 'CMP202508157119_1_1755235604.jpg', 'CMP202508157119_2_1755235604.png', 'CMP202508157119_3_1755235604.png', '2025-08-15 10:56:44'),
(3, 'CMP202508153919', 'CMP202508153919_1_1755242397.png', 'CMP202508153919_2_1755242397.png', 'CMP202508153919_3_1755242397.jpeg', '2025-08-15 12:49:57'),
(4, 'CMP202508158865', 'CMP202508158865_1_1755245673.png', 'CMP202508158865_2_1755245673.png', 'CMP202508158865_3_1755245673.jpeg', '2025-08-15 13:44:33'),
(5, 'CMP202508164448', 'CMP202508164448_1_1755343989.png', 'CMP202508164448_2_1755343989.png', 'CMP202508164448_3_1755343989.png', '2025-08-16 17:03:09'),
(6, 'CMP202508221310', 'CMP202508221310_1_1755845691.jpeg', 'CMP202508221310_2_1755845691.jpeg', NULL, '2025-08-22 12:24:51'),
(8, 'CMP202508254490', 'CMP202508254490_1_1756116594.jpg', NULL, NULL, '2025-08-25 15:39:54'),
(9, 'CMP202508264412', 'CMP202508264412_1_1756212882.jpeg', NULL, NULL, '2025-08-26 18:24:42'),
(10, 'CMP202509023158', 'CMP202509023158_1_1756808311.jpg', 'CMP202509023158_2_1756808311.jpg', NULL, '2025-09-02 15:48:31'),
(11, 'CMP202509044309', 'CMP202509044309_1_1756986345.jpeg', 'CMP202509044309_2_1756986345.jpeg', NULL, '2025-09-04 17:15:45');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `evidence`
--
ALTER TABLE `evidence`
  ADD PRIMARY KEY (`id`),
  ADD KEY `complaint_id` (`complaint_id`),
  ADD KEY `idx_evidence_uploaded` (`uploaded_at`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `evidence`
--
ALTER TABLE `evidence`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `evidence`
--
ALTER TABLE `evidence`
  ADD CONSTRAINT `fk_evidence_complaint` FOREIGN KEY (`complaint_id`) REFERENCES `complaints` (`complaint_id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
