-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 17, 2025 at 11:36 PM
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
-- Database: `sampark_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `evidence`
--

CREATE TABLE `evidence` (
  `id` int(11) NOT NULL,
  `complaint_id` varchar(20) NOT NULL,
  `file_name_1` varchar(255) DEFAULT NULL,
  `file_name_2` varchar(255) DEFAULT NULL,
  `file_name_3` varchar(255) DEFAULT NULL,
  `file_type_1` varchar(50) DEFAULT NULL,
  `file_type_2` varchar(50) DEFAULT NULL,
  `file_type_3` varchar(50) DEFAULT NULL,
  `file_path_1` varchar(500) DEFAULT NULL,
  `file_path_2` varchar(500) DEFAULT NULL,
  `file_path_3` varchar(500) DEFAULT NULL,
  `compressed_size_1` int(11) DEFAULT NULL,
  `compressed_size_2` int(11) DEFAULT NULL,
  `compressed_size_3` int(11) DEFAULT NULL,
  `additional_file_name_1` varchar(255) DEFAULT NULL,
  `additional_file_name_2` varchar(255) DEFAULT NULL,
  `additional_file_type_1` varchar(50) DEFAULT NULL,
  `additional_file_type_2` varchar(50) DEFAULT NULL,
  `additional_file_path_1` varchar(500) DEFAULT NULL,
  `additional_file_path_2` varchar(500) DEFAULT NULL,
  `additional_compressed_size_1` int(11) DEFAULT NULL,
  `additional_compressed_size_2` int(11) DEFAULT NULL,
  `additional_files_uploaded_at` timestamp NULL DEFAULT NULL,
  `uploaded_by_type` enum('customer','user') NOT NULL,
  `uploaded_by_id` varchar(50) NOT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Evidence table with support for 3 initial files + 2 additional files when info is requested';

--
-- Indexes for dumped tables
--

--
-- Indexes for table `evidence`
--
ALTER TABLE `evidence`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_complaint_id` (`complaint_id`),
  ADD KEY `idx_uploaded_by` (`uploaded_by_type`,`uploaded_by_id`),
  ADD KEY `idx_evidence_complaint_uploaded` (`complaint_id`,`uploaded_by_type`,`uploaded_by_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `evidence`
--
ALTER TABLE `evidence`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
