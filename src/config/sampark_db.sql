-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 11, 2025 at 08:32 PM
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

DELIMITER $$
--
-- Functions
--
CREATE DEFINER=`root`@`localhost` FUNCTION `GenerateComplaintNumber` () RETURNS VARCHAR(12) CHARSET utf8mb4 COLLATE utf8mb4_general_ci DETERMINISTIC READS SQL DATA BEGIN
    DECLARE next_number INT DEFAULT 1;
    DECLARE complaint_date VARCHAR(8);
    DECLARE complaint_id VARCHAR(12);
    
    SET complaint_date = DATE_FORMAT(NOW(), '%Y%m%d');
    
    SELECT COALESCE(MAX(CAST(SUBSTRING(complaint_id, 9) AS UNSIGNED)), 0) + 1 
    INTO next_number
    FROM complaints 
    WHERE complaint_id LIKE CONCAT(complaint_date, '%');
    
    SET complaint_id = CONCAT(complaint_date, LPAD(next_number, 4, '0'));
    
    RETURN complaint_id;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Stand-in structure for view `active_tickets`
-- (See below for the actual view)
--
CREATE TABLE `active_tickets` (
`complaint_id` varchar(20)
,`category_id` int(11)
,`date` date
,`time` time
,`shed_id` int(11)
,`wagon_id` int(11)
,`rating` enum('excellent','satisfactory','unsatisfactory')
,`rating_remarks` text
,`description` text
,`action_taken` text
,`status` enum('pending','awaiting_feedback','awaiting_info','awaiting_approval','closed')
,`department` varchar(100)
,`division` varchar(100)
,`zone` varchar(100)
,`customer_id` varchar(20)
,`fnr_number` varchar(50)
,`e_indent_number` varchar(50)
,`assigned_to_department` varchar(100)
,`forwarded_flag` tinyint(1)
,`priority` enum('normal','medium','high','critical')
,`sla_deadline` timestamp
,`created_at` timestamp
,`updated_at` timestamp
,`closed_at` timestamp
,`escalated_at` timestamp
,`category` varchar(100)
,`type` varchar(100)
,`subtype` varchar(100)
,`shed_name` varchar(200)
,`shed_code` varchar(10)
,`customer_name` varchar(100)
,`customer_email` varchar(100)
,`customer_mobile` varchar(15)
,`company_name` varchar(150)
);

-- --------------------------------------------------------

--
-- Table structure for table `activity_logs`
--

CREATE TABLE `activity_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `customer_id` varchar(20) DEFAULT NULL,
  `user_role` varchar(50) DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `complaint_id` varchar(20) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `activity_logs`
--

INSERT INTO `activity_logs` (`id`, `user_id`, `customer_id`, `user_role`, `action`, `description`, `complaint_id`, `ip_address`, `user_agent`, `created_at`) VALUES
(1, NULL, NULL, NULL, 'failed_login', 'Failed login attempt for user: AD002', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-09-08 07:10:31'),
(2, NULL, NULL, NULL, 'failed_login', 'Failed login attempt for user: AD002', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-09-08 07:10:41'),
(3, NULL, NULL, NULL, 'failed_login', 'Failed login attempt for user: AD002', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-09-09 05:46:49'),
(4, 3, NULL, 'admin', 'user_login', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-09-09 05:46:58'),
(5, 3, NULL, 'admin', 'customer_approved', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-09-09 07:43:32'),
(6, 3, NULL, 'admin', 'logout', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-09-09 07:44:20'),
(7, 3, NULL, 'admin', 'user_login', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-09-09 08:05:35'),
(8, 3, NULL, 'admin', 'logout', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-09-09 08:07:02'),
(15, 3, NULL, 'admin', 'user_login', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-09-10 10:40:56'),
(16, 3, NULL, 'admin', 'user_password_reset', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-09-10 10:45:14'),
(17, 3, NULL, 'admin', 'logout', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-09-10 10:45:20'),
(18, NULL, NULL, NULL, 'failed_login', 'Failed login attempt for user: CN003', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-09-10 10:45:35'),
(19, 7, NULL, 'controller_nodal', 'user_login', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-09-10 10:46:20'),
(20, 7, NULL, 'controller_nodal', 'user_login', '{\"user_id\":7,\"role\":\"controller_nodal\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-09-10 12:31:13'),
(21, 7, NULL, 'controller_nodal', 'logout', '{\"previous_role\":\"controller_nodal\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-09-10 13:26:50'),
(22, NULL, NULL, NULL, 'failed_login', 'Failed login attempt for customer: test@gmail.com', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-09-10 13:27:07'),
(23, NULL, 'CUST2025090001', 'customer', 'customer_login', '{\"customer_id\":\"CUST2025090001\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-09-10 13:27:23'),
(24, NULL, 'CUST2025090001', 'customer', 'logout', '{\"previous_role\":\"customer\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-09-10 13:31:10'),
(25, 7, NULL, 'controller_nodal', 'user_login', '{\"user_id\":7,\"role\":\"controller_nodal\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-09-10 13:31:24'),
(26, 7, NULL, 'controller_nodal', 'logout', '{\"previous_role\":\"controller_nodal\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-09-10 13:35:44'),
(27, NULL, 'CUST2025090001', 'customer', 'customer_login', '{\"customer_id\":\"CUST2025090001\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-09-10 13:35:53'),
(28, NULL, 'CUST2025090001', 'customer', 'logout', '{\"previous_role\":\"customer\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-09-10 13:36:19'),
(29, NULL, NULL, NULL, 'failed_login', 'Failed login attempt for customer: CN0032', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-09-10 13:36:48'),
(30, 16, NULL, 'controller_nodal', 'user_login', '{\"user_id\":16,\"role\":\"controller_nodal\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-09-10 13:36:58'),
(31, NULL, NULL, NULL, 'failed_login', 'Failed login attempt for user: CN003', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 04:51:11'),
(32, NULL, NULL, NULL, 'failed_login', 'Failed login attempt for user: CN003', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 04:51:20'),
(33, NULL, NULL, NULL, 'failed_login', 'Failed login attempt for user: CN003', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 04:51:29'),
(34, 3, NULL, 'admin', 'user_login', '{\"user_id\":3,\"role\":\"admin\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 04:51:43'),
(35, 3, NULL, 'admin', 'user_password_reset', '{\"user_id\":\"16\",\"user_login\":\"CN0032\",\"reset_by\":3}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 04:52:24'),
(36, 3, NULL, 'admin', 'logout', '{\"previous_role\":\"admin\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 04:55:39'),
(37, NULL, NULL, NULL, 'failed_login', 'Failed login attempt for user: CN0032', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 04:56:32'),
(38, NULL, NULL, NULL, 'failed_login', 'Failed login attempt for user: CN0032', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 04:57:08'),
(39, NULL, NULL, NULL, 'failed_login', 'Failed login attempt for customer: 9876543210', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 05:02:36'),
(40, NULL, 'CUST2025090001', 'customer', 'customer_login', '{\"customer_id\":\"CUST2025090001\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 05:02:48'),
(41, NULL, NULL, 'system', 'system_background_automation', 'Processed 3 automation tasks in 123.8ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 05:22:19'),
(42, NULL, NULL, 'system', 'system_background_automation', 'Processed 3 automation tasks in 83.94ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 05:23:19'),
(43, NULL, NULL, 'system', 'system_background_automation', 'Processed 3 automation tasks in 92.38ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 05:24:19'),
(44, NULL, NULL, 'system', 'system_background_automation', 'Processed 3 automation tasks in 65.77ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 05:25:19'),
(45, NULL, NULL, 'system', 'system_background_automation', 'Processed 3 automation tasks in 46.12ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 05:26:19'),
(46, NULL, NULL, 'system', 'system_background_automation', 'Processed 3 automation tasks in 46.09ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 05:27:19'),
(47, NULL, NULL, 'system', 'system_background_automation', 'Processed 3 automation tasks in 79.29ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 05:28:19'),
(48, NULL, NULL, 'system', 'system_background_automation', 'Processed 3 automation tasks in 37.42ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 05:29:19'),
(49, NULL, NULL, 'system', 'system_background_automation', 'Processed 3 automation tasks in 63.88ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 05:30:19'),
(50, NULL, NULL, 'system', 'system_background_automation', 'Processed 3 automation tasks in 40.9ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 05:31:19'),
(51, NULL, NULL, 'system', 'system_background_automation', 'Processed 3 automation tasks in 33.16ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 05:32:19'),
(52, NULL, NULL, 'system', 'system_background_automation', 'Processed 3 automation tasks in 79.61ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 05:33:19'),
(53, NULL, NULL, 'system', 'system_background_automation', 'Processed 3 automation tasks in 41.1ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 05:34:23'),
(54, NULL, NULL, 'system', 'system_background_automation', 'Processed 3 automation tasks in 32.27ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 05:35:22'),
(55, NULL, NULL, 'system', 'system_background_automation', 'Processed 3 automation tasks in 28.71ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 05:36:22'),
(56, NULL, NULL, 'system', 'system_background_automation', 'Processed 3 automation tasks in 64.53ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 05:37:23'),
(57, NULL, NULL, 'system', 'system_background_automation', 'Processed 3 automation tasks in 29.81ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 05:38:26'),
(58, NULL, NULL, 'system', 'system_background_automation', 'Processed 3 automation tasks in 61.79ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 05:39:48'),
(59, NULL, NULL, 'system', 'system_background_automation', 'Processed 3 automation tasks in 61.77ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 05:40:48'),
(60, NULL, NULL, 'system', 'system_background_automation', 'Processed 3 automation tasks in 66.86ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 05:41:49'),
(61, NULL, NULL, 'system', 'system_background_automation', 'Processed 3 automation tasks in 58.51ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 05:42:49'),
(62, NULL, NULL, 'system', 'system_background_automation', 'Processed 3 automation tasks in 62.71ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 05:43:49'),
(63, NULL, NULL, 'system', 'system_background_automation', 'Processed 3 automation tasks in 56.16ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 05:44:48'),
(64, NULL, NULL, 'system', 'system_background_automation', 'Processed 3 automation tasks in 80.02ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 05:45:49'),
(65, NULL, NULL, 'system', 'system_background_automation', 'Processed 3 automation tasks in 67.54ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 05:46:50'),
(66, NULL, NULL, 'system', 'system_background_automation', 'Processed 3 automation tasks in 76.81ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 05:47:51'),
(67, NULL, NULL, 'system', 'system_background_automation', 'Processed 3 automation tasks in 48.03ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 05:48:52'),
(68, NULL, NULL, 'system', 'system_background_automation', 'Processed 3 automation tasks in 67.99ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 05:49:53'),
(69, NULL, NULL, 'system', 'system_background_automation', 'Processed 3 automation tasks in 64.53ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 05:50:54'),
(70, NULL, NULL, 'system', 'system_background_automation', 'Processed 3 automation tasks in 71.78ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 05:51:55'),
(71, NULL, NULL, 'system', 'system_background_automation', 'Processed 3 automation tasks in 52.14ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 05:52:56'),
(72, NULL, NULL, 'system', 'system_background_automation', 'Processed 3 automation tasks in 31.71ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 05:53:56'),
(73, NULL, NULL, 'system', 'system_background_automation', 'Processed 3 automation tasks in 51.12ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 05:54:57'),
(74, NULL, NULL, 'system', 'system_background_automation', 'Processed 3 automation tasks in 35.56ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 05:55:58'),
(75, NULL, NULL, 'system', 'system_background_automation', 'Processed 3 automation tasks in 40.29ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 05:57:05'),
(76, NULL, NULL, 'system', 'system_background_automation', 'Processed 3 automation tasks in 67.22ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 05:58:05'),
(77, NULL, NULL, 'system', 'system_background_automation', 'Processed 3 automation tasks in 31.4ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 05:58:58'),
(78, NULL, NULL, 'system', 'system_background_automation', 'Processed 3 automation tasks in 55.97ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 06:00:04'),
(79, NULL, NULL, 'system', 'system_background_automation', 'Processed 3 automation tasks in 41.14ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 06:01:04'),
(80, NULL, NULL, 'system', 'system_background_automation', 'Processed 3 automation tasks in 21.82ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 06:02:03'),
(81, NULL, NULL, 'system', 'system_background_automation', 'Processed 3 automation tasks in 57.01ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 06:03:04'),
(82, NULL, NULL, 'system', 'system_background_automation', 'Processed 3 automation tasks in 31.32ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 06:04:03'),
(83, NULL, NULL, 'system', 'system_background_automation', 'Processed 3 automation tasks in 40.54ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 06:05:04'),
(84, NULL, NULL, 'system', 'system_background_automation', 'Processed 3 automation tasks in 88.58ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 06:06:04'),
(85, NULL, NULL, 'system', 'system_background_automation', 'Processed 3 automation tasks in 27.59ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 06:07:03'),
(86, NULL, NULL, 'system', 'system_background_automation', 'Processed 3 automation tasks in 43.1ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 06:08:04'),
(87, NULL, NULL, 'system', 'system_background_automation', 'Processed 3 automation tasks in 36.28ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 06:10:05'),
(88, NULL, NULL, 'system', 'system_background_automation', 'Processed 3 automation tasks in 38.21ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 06:11:07'),
(89, NULL, NULL, 'system', 'system_background_automation', 'Processed 3 automation tasks in 32.87ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 06:12:08'),
(90, NULL, NULL, 'system', 'system_background_automation', 'Processed 3 automation tasks in 25.31ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 06:13:00'),
(91, NULL, NULL, 'system', 'system_background_automation', 'Processed 3 automation tasks in 50.52ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 06:13:32'),
(92, NULL, NULL, 'system', 'system_background_automation', 'Processed 3 automation tasks in 27.54ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 06:14:03'),
(93, NULL, 'CUST2025090001', 'customer', 'customer_login', '{\"customer_id\":\"CUST2025090001\"}', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 06:14:38'),
(94, NULL, NULL, 'system', 'system_background_automation', 'Processed 3 automation tasks in 38.92ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 06:14:40'),
(95, NULL, NULL, 'system', 'system_background_automation', 'Processed 3 automation tasks in 39.1ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 06:15:11'),
(96, NULL, NULL, 'system', 'system_background_automation', 'Processed 3 automation tasks in 72.77ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 06:16:14'),
(97, NULL, NULL, 'system', 'system_background_automation', 'Processed 3 automation tasks in 105.57ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 06:17:05'),
(98, NULL, NULL, 'system', 'system_background_automation', 'Processed 3 automation tasks in 42.48ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 06:18:15'),
(99, NULL, NULL, 'system', 'system_background_automation', 'Processed 3 automation tasks in 56.51ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 06:19:14'),
(100, NULL, NULL, 'system', 'system_background_automation', 'Processed 3 automation tasks in 44.69ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 06:20:15'),
(101, NULL, NULL, 'system', 'system_background_automation', 'Processed 3 automation tasks in 36.69ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 06:21:14'),
(102, NULL, NULL, 'system', 'system_background_automation', 'Processed 3 automation tasks in 93.86ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 06:50:41'),
(103, NULL, 'CUST2025090001', 'customer', 'logout', '{\"previous_role\":\"customer\"}', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 06:51:11'),
(104, NULL, NULL, 'system', 'system_background_automation', 'Processed 3 automation tasks in 37.3ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 06:51:13'),
(105, NULL, 'CUST2025090001', 'customer', 'customer_login', '{\"customer_id\":\"CUST2025090001\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 06:51:25'),
(106, NULL, NULL, 'system', 'system_background_automation', 'Processed 3 automation tasks in 59.09ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 06:52:22'),
(107, NULL, NULL, 'system', 'system_background_automation', 'Processed 3 automation tasks in 31.99ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 06:52:55'),
(108, NULL, NULL, 'system', 'system_background_automation', 'Processed 3 automation tasks in 19.47ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 06:53:41'),
(109, NULL, NULL, 'system', 'system_background_automation', 'Processed 3 automation tasks in 45.75ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 06:54:44'),
(110, NULL, NULL, 'system', 'system_background_automation', 'Processed 3 automation tasks in 45.98ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 06:55:27'),
(111, NULL, 'CUST2025090001', 'customer', 'customer_login', '{\"customer_id\":\"CUST2025090001\"}', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 06:55:33'),
(112, NULL, NULL, 'system', 'system_background_automation', 'Processed 3 automation tasks in 33.03ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 06:55:57'),
(113, NULL, NULL, 'system', 'system_background_automation', 'Processed 3 automation tasks in 50.89ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 06:56:59'),
(114, NULL, NULL, 'system', 'system_background_automation', 'Processed 3 automation tasks in 63.76ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 06:57:43'),
(115, NULL, NULL, 'system', 'system_background_automation', 'Processed 3 automation tasks in 63ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 06:59:05'),
(116, NULL, NULL, 'system', 'system_background_automation', 'Processed 3 automation tasks in 45.64ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 07:00:05'),
(117, NULL, NULL, 'system', 'system_background_automation', 'Processed 3 automation tasks in 60.35ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 07:01:05'),
(118, NULL, NULL, 'system', 'system_background_automation', 'Processed 3 automation tasks in 49.19ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 07:02:05'),
(119, NULL, 'CUST2025090001', 'customer', 'logout', '{\"previous_role\":\"customer\"}', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 07:02:19'),
(120, 3, NULL, 'admin', 'user_login', '{\"user_id\":3,\"role\":\"admin\"}', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 07:02:31'),
(121, NULL, NULL, 'system', 'system_background_automation', 'Processed 3 automation tasks in 44.22ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 07:03:01'),
(122, NULL, NULL, 'system', 'system_background_automation', 'Processed 3 automation tasks in 46.21ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 07:04:26'),
(123, 3, NULL, 'admin', 'user_updated', '{\"updated_user_id\":\"5\",\"updated_user_login\":\"CN001\",\"changes\":{\"csrf_token\":\"df9c70bd511bdc1c1f888e0d5c0af9dade2d267e3725ee08b7a256d2ba9acf6c\",\"user_id\":\"5\",\"division\":\"CSMT\",\"employee_id\":\"CN001\",\"new_password\":\"Demo@123\",\"password_confirmation\":\"Demo@123\",\"notes\":\"\"}}', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 07:04:36'),
(124, 3, NULL, 'admin', 'user_updated', '{\"updated_user_id\":\"5\",\"updated_user_login\":\"CN001\",\"changes\":{\"csrf_token\":\"df9c70bd511bdc1c1f888e0d5c0af9dade2d267e3725ee08b7a256d2ba9acf6c\",\"user_id\":\"5\",\"employee_id\":\"CN001\",\"new_password\":\"Hello@123\",\"password_confirmation\":\"Hello@123\",\"notes\":\"\"}}', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 07:05:07'),
(125, NULL, NULL, 'system', 'system_background_automation', 'Processed 3 automation tasks in 48.6ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 07:05:13'),
(126, 3, NULL, 'admin', 'logout', '{\"previous_role\":\"admin\"}', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 07:05:13'),
(127, 5, NULL, 'controller_nodal', 'user_login', '{\"user_id\":5,\"role\":\"controller_nodal\"}', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 07:05:23'),
(128, NULL, NULL, 'system', 'system_background_automation', 'Processed 3 automation tasks in 31.33ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 07:06:06'),
(129, NULL, NULL, 'system', 'system_background_automation', 'Processed 3 automation tasks in 44.51ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 07:06:54'),
(130, NULL, NULL, 'system', 'system_background_automation', 'Processed 3 automation tasks in 36.56ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 07:07:40'),
(131, NULL, NULL, 'system', 'system_background_automation', 'Processed 3 automation tasks in 32.38ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 07:08:13'),
(132, NULL, NULL, 'system', 'system_background_automation', 'Processed 3 automation tasks in 34.15ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 07:08:51'),
(133, NULL, NULL, 'system', 'system_background_automation', 'Processed 3 automation tasks in 24.24ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 07:09:51'),
(134, NULL, NULL, 'system', 'system_background_automation', 'Processed 3 automation tasks in 34.85ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 07:10:51'),
(135, NULL, NULL, 'system', 'system_background_automation', 'Processed 3 automation tasks in 41.91ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 07:11:32'),
(136, NULL, NULL, 'system', 'system_background_automation', 'Processed 3 automation tasks in 59.03ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 07:12:38'),
(137, NULL, NULL, 'system', 'system_background_automation', 'Processed 3 automation tasks in 34.23ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 07:13:37'),
(138, NULL, NULL, 'system', 'system_background_automation', 'Processed 3 automation tasks in 56.54ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 07:31:36'),
(139, NULL, NULL, 'system', 'system_background_automation', 'Processed 3 automation tasks in 47.87ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 07:32:20'),
(140, NULL, NULL, 'system', 'system_background_automation', 'Processed 3 automation tasks in 45.9ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 07:33:10'),
(141, NULL, NULL, 'system', 'system_background_automation', 'Processed 3 automation tasks in 58.85ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 07:33:47'),
(142, NULL, NULL, 'system', 'system_background_automation', 'Processed 3 automation tasks in 50.05ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 07:34:21'),
(143, NULL, 'CUST2025090001', 'customer', 'logout', '{\"previous_role\":\"customer\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 07:34:28'),
(144, NULL, NULL, 'system', 'system_background_automation', 'Processed 3 automation tasks in 38.68ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 07:34:57'),
(145, NULL, NULL, 'system', 'system_background_automation', 'Processed 3 automation tasks in 50.2ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 07:35:29'),
(146, NULL, NULL, 'system', 'system_background_automation', 'Processed 3 automation tasks in 43.84ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 07:36:03'),
(147, NULL, 'CUST2025090001', 'customer', 'customer_login', '{\"customer_id\":\"CUST2025090001\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 07:36:22'),
(148, NULL, NULL, 'system', 'system_background_automation', 'Processed 3 automation tasks in 45.44ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 07:36:41'),
(149, NULL, NULL, 'system', 'system_background_automation', 'Processed 3 automation tasks in 69.78ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 07:37:47'),
(150, NULL, NULL, 'system', 'system_background_automation', 'Processed 3 automation tasks in 35.44ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 07:38:58'),
(151, NULL, NULL, 'system', 'system_background_automation', 'Processed 3 automation tasks in 43.86ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 07:39:58'),
(152, NULL, NULL, 'system', 'system_background_automation', 'Processed 3 automation tasks in 47.74ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 07:40:50'),
(153, NULL, NULL, 'system', 'system_background_automation', 'Processed 3 automation tasks in 37.32ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 07:41:31'),
(154, NULL, NULL, 'system', 'system_background_automation', 'Processed 3 automation tasks in 69.29ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 07:42:31'),
(155, NULL, NULL, 'system', 'system_background_automation', 'Processed 3 automation tasks in 55.6ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 07:43:45'),
(156, NULL, NULL, 'system', 'system_background_automation', 'Processed 3 automation tasks in 71.4ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 07:44:44'),
(157, NULL, NULL, 'system', 'system_background_automation', 'Processed 3 automation tasks in 101.3ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 07:45:45'),
(158, NULL, NULL, 'system', 'system_background_automation', 'Processed 3 automation tasks in 82.5ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 07:46:45'),
(159, NULL, NULL, 'system', 'system_background_automation', 'Processed 3 automation tasks in 86.07ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 07:47:45'),
(160, NULL, NULL, 'system', 'system_background_automation', 'Processed 3 automation tasks in 68.62ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 07:48:45'),
(161, NULL, NULL, 'system', 'system_background_automation', 'Processed 3 automation tasks in 85.32ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 07:49:45'),
(162, NULL, NULL, 'system', 'system_background_automation', 'Processed 3 automation tasks in 40.68ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 07:50:45'),
(163, NULL, NULL, 'system', 'system_background_automation', 'Processed 3 automation tasks in 26.84ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 07:51:45'),
(164, NULL, NULL, 'system', 'system_background_automation', 'Processed 3 automation tasks in 37.74ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 07:52:46'),
(165, NULL, NULL, 'system', 'system_background_automation', 'Processed 3 automation tasks in 30.82ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 07:53:47'),
(166, NULL, NULL, 'system', 'system_background_automation', 'Processed 3 automation tasks in 66.05ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 07:54:49'),
(167, NULL, NULL, 'system', 'system_background_automation', 'Processed 3 automation tasks in 39.78ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 07:55:31'),
(168, NULL, NULL, 'system', 'system_background_automation', 'Processed 3 automation tasks in 44.32ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 07:56:26'),
(169, NULL, NULL, 'system', 'system_background_automation', 'Processed 3 automation tasks in 46.9ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 07:57:01'),
(170, NULL, NULL, 'system', 'system_background_automation', 'Processed 3 automation tasks in 70.91ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 07:57:49'),
(171, NULL, NULL, 'system', 'system_background_automation', 'Processed 3 automation tasks in 72.46ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 07:58:49'),
(172, NULL, NULL, 'system', 'system_background_automation', 'Processed 3 automation tasks in 68.27ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 07:59:49'),
(173, NULL, NULL, 'system', 'system_background_automation', 'Processed 3 automation tasks in 63.19ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 08:00:49'),
(174, NULL, NULL, 'system', 'system_background_automation', 'Processed 3 automation tasks in 40.83ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 08:01:49'),
(175, NULL, NULL, 'system', 'system_background_automation', 'Processed 3 automation tasks in 36.84ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 08:02:49'),
(176, NULL, NULL, 'system', 'system_background_automation', 'Processed 3 automation tasks in 36.84ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 08:03:49'),
(177, NULL, NULL, 'system', 'system_background_automation', 'Processed 3 automation tasks in 43.98ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 08:04:49'),
(178, NULL, NULL, 'system', 'system_background_automation', 'Processed 3 automation tasks in 37.19ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 08:05:49'),
(179, NULL, NULL, 'system', 'system_background_automation', 'Processed 3 automation tasks in 36.94ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 08:06:49'),
(180, NULL, NULL, 'system', 'system_background_automation', 'Processed 3 automation tasks in 75.35ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 08:07:43'),
(181, NULL, NULL, 'system', 'system_background_automation', 'Processed 3 automation tasks in 43.79ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 08:09:12'),
(182, NULL, NULL, 'system', 'system_background_automation', 'Processed 3 automation tasks in 28.99ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 08:09:49'),
(183, NULL, NULL, 'system', 'system_background_automation', 'Processed 3 automation tasks in 28.24ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 08:10:49'),
(184, NULL, NULL, 'system', 'system_background_automation', 'Processed 3 automation tasks in 44.02ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 08:11:34'),
(185, NULL, NULL, 'system', 'system_background_automation', 'Processed 3 automation tasks in 68.68ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 08:12:35'),
(186, NULL, NULL, 'system', 'system_background_automation', 'Processed 3 automation tasks in 45.62ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 08:13:34'),
(187, NULL, NULL, 'system', 'system_background_automation', 'Processed 3 automation tasks in 28.05ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 08:14:20'),
(188, NULL, NULL, 'system', 'system_background_automation', 'Processed 3 automation tasks in 64.44ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 08:15:20'),
(189, NULL, NULL, 'system', 'system_background_automation', 'Processed 3 automation tasks in 41.93ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 08:16:19'),
(190, NULL, NULL, 'system', 'system_background_automation', 'Processed 3 automation tasks in 97.23ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 08:17:20'),
(191, NULL, NULL, 'system', 'system_background_automation', 'Processed 3 automation tasks in 39.12ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 08:18:19'),
(192, NULL, NULL, 'system', 'system_background_automation', 'Processed 3 automation tasks in 42.08ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 08:19:19'),
(193, NULL, NULL, 'system', 'system_background_automation', 'Processed 3 automation tasks in 36.48ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 08:20:09'),
(194, NULL, NULL, 'system', 'system_background_automation', 'Processed 3 automation tasks in 61.01ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 08:20:49'),
(195, NULL, NULL, 'system', 'system_background_automation', 'Processed 3 automation tasks in 42.16ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 08:21:49'),
(196, NULL, NULL, 'system', 'system_background_automation', 'Processed 3 automation tasks in 90.32ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 08:22:49'),
(197, NULL, NULL, 'system', 'system_background_automation', 'Processed 3 automation tasks in 57.11ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 08:23:49'),
(198, NULL, NULL, 'system', 'system_background_automation', 'Processed 3 automation tasks in 77.33ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 08:24:49'),
(199, NULL, NULL, 'system', 'system_background_automation', 'Processed 3 automation tasks in 64.22ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 08:25:49'),
(200, NULL, NULL, 'system', 'system_background_automation', 'Processed 4 automation tasks in 400.53ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 08:26:50'),
(201, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 70.48ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 08:27:49'),
(202, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 39.42ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 08:28:49'),
(203, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 75.18ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 08:29:49');
INSERT INTO `activity_logs` (`id`, `user_id`, `customer_id`, `user_role`, `action`, `description`, `complaint_id`, `ip_address`, `user_agent`, `created_at`) VALUES
(204, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 31.52ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 08:30:49'),
(205, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 41.24ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 08:31:29'),
(206, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 52.97ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 08:32:15'),
(207, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 27.65ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 08:32:49'),
(208, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 82.58ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 08:33:49'),
(209, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 40.96ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 08:34:49'),
(210, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 78.94ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 08:35:49'),
(211, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 78.98ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 08:36:49'),
(212, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 63.21ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 08:37:49'),
(213, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 34ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 08:38:27'),
(214, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 80ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 08:39:26'),
(215, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 38.84ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 08:40:14'),
(216, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 51.66ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 08:40:58'),
(217, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 40.14ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 08:42:26'),
(218, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 81.89ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 08:43:27'),
(219, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 29.74ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 08:44:02'),
(220, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 23.02ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 08:44:49'),
(221, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 30.1ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 08:45:39'),
(222, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 32.26ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 08:46:25'),
(223, NULL, 'CUST2025090001', 'customer', 'logout', '{\"previous_role\":\"customer\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 08:46:26'),
(224, NULL, 'CUST2025090001', 'customer', 'customer_login', '{\"customer_id\":\"CUST2025090001\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 08:46:46'),
(225, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 34.51ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 08:46:58'),
(226, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 24.37ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 08:47:49'),
(227, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 39.9ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 08:48:49'),
(228, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 29.93ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 08:49:49'),
(229, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 21.24ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 08:50:49'),
(230, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 61.32ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 08:51:49'),
(231, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 32.37ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 08:52:49'),
(232, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 35.44ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 08:53:49'),
(233, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 71.87ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 08:54:49'),
(234, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 61.16ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 08:55:49'),
(235, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 23.63ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 08:56:30'),
(236, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 79.44ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 08:57:15'),
(237, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 69.4ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 08:58:16'),
(238, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 67.3ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 08:59:17'),
(239, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 75.57ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 09:00:18'),
(240, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 66.49ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 09:01:19'),
(241, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 67.94ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 09:02:20'),
(242, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 70.7ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 09:03:21'),
(243, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 62.5ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 09:04:22'),
(244, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 50.39ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 09:05:23'),
(245, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 80.98ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 09:06:24'),
(246, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 69.7ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 09:07:25'),
(247, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 63.38ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 09:08:26'),
(248, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 77.01ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 09:09:27'),
(249, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 72.92ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 09:10:28'),
(250, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 74.8ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 09:11:29'),
(251, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 83.72ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 09:12:30'),
(252, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 61.82ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 09:13:31'),
(253, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 65.72ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 09:14:32'),
(254, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 68.06ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 09:15:33'),
(255, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 76.51ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 09:16:34'),
(256, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 62.43ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 09:17:35'),
(257, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 68.39ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 09:18:36'),
(258, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 64.3ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 09:19:37'),
(259, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 67.5ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 09:20:38'),
(260, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 78.41ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 09:21:39'),
(261, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 53.03ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 09:22:40'),
(262, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 57.53ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 09:23:41'),
(263, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 80.54ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 09:24:42'),
(264, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 74.35ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 09:25:42'),
(265, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 74.95ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 09:26:42'),
(266, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 70.5ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 09:27:42'),
(267, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 82.18ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 09:28:42'),
(268, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 64.54ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 09:29:41'),
(269, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 53.32ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 09:30:42'),
(270, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 64.45ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 09:31:42'),
(271, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 61.08ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 09:32:42'),
(272, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 62.29ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 09:33:42'),
(273, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 70.25ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 09:34:42'),
(274, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 71.44ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 09:35:42'),
(275, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 73.32ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 09:36:42'),
(276, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 78.49ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 09:37:42'),
(277, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 74.14ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 09:38:42'),
(278, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 63.85ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 09:39:42'),
(279, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 72.47ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 09:40:42'),
(280, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 74.21ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 09:41:42'),
(281, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 46.52ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 09:42:20'),
(282, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 44.98ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 09:43:25'),
(283, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 38.28ms', NULL, '10.31.37.92', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-09-11 09:44:06'),
(284, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 75.59ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 09:45:30'),
(285, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 79.95ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 09:46:30'),
(286, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 60.89ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 09:47:30'),
(287, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 43.79ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 09:48:30'),
(288, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 56.38ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 09:49:29'),
(289, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 76.07ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 09:50:30'),
(290, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 66.15ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 09:51:31'),
(291, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 42.35ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 09:52:39'),
(292, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 48.21ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 09:53:40'),
(293, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 79.29ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 09:54:40'),
(294, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 48.75ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 09:55:40'),
(295, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 46.7ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 09:56:40'),
(296, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 77.57ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 09:57:40'),
(297, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 78.8ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 09:58:40'),
(298, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 79.87ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 09:59:41'),
(299, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 72.97ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 10:00:42'),
(300, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 77.69ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 10:01:42'),
(301, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 67.49ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 10:02:42'),
(302, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 67.94ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 10:03:42'),
(303, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 44.05ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 10:04:48'),
(304, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 33.68ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 10:05:47'),
(305, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 74.93ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 10:06:48'),
(306, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 73.68ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 10:07:48'),
(307, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 49.33ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 10:08:19'),
(308, NULL, 'CUST2025090001', 'customer', 'ticket_provide_info', 'Ticket provide_info - {\"new_status\":\"pending\",\"message\":\"Additional information provided. Ticket is now back under review.\"}', '202509100001', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 10:08:27'),
(309, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 78.18ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 10:09:41'),
(310, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 48.56ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 10:10:41'),
(311, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 52.18ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 10:11:40'),
(312, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 53.15ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 10:12:41'),
(313, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 48.13ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 10:13:40'),
(314, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 83.4ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 10:14:41'),
(315, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 69.33ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 10:16:09'),
(316, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 76.61ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 10:16:59'),
(317, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 55.08ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 10:18:06'),
(318, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 94.89ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 10:18:52'),
(319, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 47.95ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 10:20:05'),
(320, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 29.76ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 10:20:52'),
(321, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 99.38ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 10:21:52'),
(322, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 64.5ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 10:22:52'),
(323, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 69.34ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 10:23:52'),
(324, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 39.83ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 10:24:51'),
(325, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 69.32ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 10:25:52'),
(326, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 77.68ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 10:26:52'),
(327, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 63.59ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 10:27:52'),
(328, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 88.83ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 10:28:53'),
(329, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 95.93ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 10:30:17'),
(330, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 39.82ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 10:30:48'),
(331, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 70.38ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 10:31:52'),
(332, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 43.97ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 10:33:03'),
(333, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 68.57ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 10:34:07'),
(334, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 82.96ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 10:35:07'),
(335, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 75.7ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 10:36:07'),
(336, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 43.63ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 10:37:06'),
(337, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 68.73ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 10:38:07'),
(338, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 72.49ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 10:39:07'),
(339, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 44.84ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 10:40:07'),
(340, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 29.63ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 10:40:54'),
(341, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 54.6ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 10:41:27'),
(342, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 43.14ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 10:42:04'),
(343, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 58.94ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 10:43:03'),
(344, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 65.29ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 10:44:22'),
(345, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 56.37ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 10:45:23'),
(346, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 31.68ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 10:46:22'),
(347, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 66.36ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 10:47:23'),
(348, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 50.2ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 10:48:12'),
(349, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 51.66ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 10:49:05'),
(350, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 46.34ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 10:49:36'),
(351, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 50.71ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 10:50:29'),
(352, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 26.37ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 10:51:29'),
(353, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 51.1ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 10:52:29'),
(354, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 70.88ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 10:53:29'),
(355, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 79.73ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 10:54:11'),
(356, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 32.96ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 10:54:44'),
(357, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 55.78ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 10:55:27'),
(358, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 88.2ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 10:56:27'),
(359, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 47.82ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 10:57:27'),
(360, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 44.64ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 10:58:26'),
(361, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 65.95ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 10:59:27'),
(362, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 30.78ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 11:00:26'),
(363, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 57.65ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 11:01:27'),
(364, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 39.34ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 11:02:26'),
(365, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 39.99ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 11:03:28'),
(366, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 47.13ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 11:04:29'),
(367, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 53.49ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 11:05:30'),
(368, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 44.26ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 11:06:31'),
(369, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 89.92ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 11:07:32'),
(370, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 90.73ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 11:08:33'),
(371, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 47.08ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 11:09:08'),
(372, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 49.92ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 11:10:13'),
(373, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 69.31ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 11:11:13'),
(374, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 46.52ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 11:14:50'),
(375, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 42.44ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 11:15:52'),
(376, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 75.66ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 11:16:52'),
(377, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 46.83ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 11:17:52'),
(378, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 59.46ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 11:28:44'),
(379, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 41.48ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 11:29:22'),
(380, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 31.05ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 11:30:21'),
(381, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 55ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 11:31:15'),
(382, 3, NULL, 'admin', 'user_login', '{\"user_id\":3,\"role\":\"admin\"}', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 11:31:26'),
(383, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 40.72ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 11:31:46'),
(384, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 44.48ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 11:32:22'),
(385, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 58.97ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 11:32:55'),
(386, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 49.14ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 11:33:34'),
(387, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 63.46ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 11:34:34'),
(388, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 64.33ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 11:35:34'),
(389, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 44.87ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 11:36:34'),
(390, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 59.87ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 11:37:34'),
(391, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 68.87ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 11:38:34'),
(392, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 56.94ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 11:39:42'),
(393, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 57.39ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 11:40:43'),
(394, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 64.01ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 11:41:44'),
(395, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 51.31ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 11:42:24'),
(396, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 89.86ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 11:43:46'),
(397, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 74.8ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 11:44:47'),
(398, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 72.01ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 11:45:48');
INSERT INTO `activity_logs` (`id`, `user_id`, `customer_id`, `user_role`, `action`, `description`, `complaint_id`, `ip_address`, `user_agent`, `created_at`) VALUES
(399, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 40.23ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 11:46:43'),
(400, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 46.94ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 11:47:44'),
(401, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 47.99ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 11:48:45'),
(402, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 45.37ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 11:49:33'),
(403, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 61.01ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 11:50:10'),
(404, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 49.04ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 11:50:50'),
(405, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 43.06ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 11:51:50'),
(406, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 48.88ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 11:52:50'),
(407, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 33.43ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 11:53:50'),
(408, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 37.37ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 11:54:50'),
(409, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 77.5ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 11:55:50'),
(410, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 75.02ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 11:56:50'),
(411, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 36.06ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 11:57:50'),
(412, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 48.24ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 11:58:50'),
(413, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 69.8ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 11:59:50'),
(414, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 72.89ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 12:00:50'),
(415, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 63.81ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 12:01:50'),
(416, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 77.5ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 12:02:50'),
(417, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 44.27ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 12:03:46'),
(418, NULL, 'CUST2025090001', 'customer', 'profile_updated', '{\"customer_id\":\"CUST2025090001\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 12:04:01'),
(419, NULL, 'CUST2025090001', 'customer', 'profile_updated', '{\"customer_id\":\"CUST2025090001\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 12:04:15'),
(420, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 58.02ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 12:04:18'),
(421, NULL, 'CUST2025090001', 'customer', 'profile_updated', '{\"customer_id\":\"CUST2025090001\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 12:04:23'),
(422, NULL, 'CUST2025090001', 'customer', 'profile_updated', '{\"customer_id\":\"CUST2025090001\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 12:04:35'),
(423, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 41.95ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 12:04:50'),
(424, NULL, 'CUST2025090001', 'customer', 'logout', '{\"previous_role\":\"customer\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 12:05:05'),
(425, NULL, 'CUST2025090001', 'customer', 'customer_login', '{\"customer_id\":\"CUST2025090001\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 12:05:16'),
(426, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 41.23ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 12:05:24'),
(427, NULL, 'CUST2025090001', 'customer', 'password_changed', '{\"customer_id\":\"CUST2025090001\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 12:05:37'),
(428, NULL, 'CUST2025090001', 'customer', 'logout', '{\"previous_role\":\"customer\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 12:05:41'),
(429, NULL, NULL, NULL, 'failed_login', 'Failed login attempt for customer: 9876543210', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 12:05:52'),
(430, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 35.12ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 12:05:54'),
(431, NULL, 'CUST2025090001', 'customer', 'customer_login', '{\"customer_id\":\"CUST2025090001\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 12:06:00'),
(432, NULL, 'CUST2025090001', 'customer', 'logout', '{\"previous_role\":\"customer\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 12:06:03'),
(433, NULL, 'CUST2025090001', 'customer', 'customer_login', '{\"customer_id\":\"CUST2025090001\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 12:06:15'),
(434, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 44.04ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 12:06:27'),
(435, NULL, 'CUST2025090001', 'customer', 'logout', '{\"previous_role\":\"customer\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 12:06:28'),
(436, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 31.99ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 12:07:36'),
(437, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 59.54ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 12:09:47'),
(438, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 41.35ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 12:10:46'),
(439, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 58.5ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 12:11:50'),
(440, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 81.27ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 12:12:50'),
(441, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 46.71ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 12:13:50'),
(442, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 48.19ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 12:14:50'),
(443, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 67.15ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 12:15:50'),
(444, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 73.95ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 12:16:50'),
(445, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 37.15ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 12:17:50'),
(446, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 69.51ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 12:18:50'),
(447, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 62.63ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 12:19:50'),
(448, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 37.74ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 12:20:50'),
(449, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 70.6ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 12:21:50'),
(450, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 77.16ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 12:22:50'),
(451, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 41.75ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 12:23:50'),
(452, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 56.32ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 12:24:50'),
(453, NULL, NULL, NULL, 'failed_login', 'Failed login attempt for customer: CN003', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 12:24:57'),
(454, NULL, NULL, NULL, 'failed_login', 'Failed login attempt for customer: CN003', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 12:25:04'),
(455, NULL, NULL, NULL, 'failed_login', 'Failed login attempt for user: CN003', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 12:25:12'),
(456, NULL, NULL, NULL, 'failed_login', 'Failed login attempt for user: CN003', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 12:25:21'),
(457, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 34.42ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 12:25:22'),
(458, NULL, NULL, NULL, 'failed_login', 'Failed login attempt for user: CN003', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 12:25:43'),
(459, NULL, NULL, NULL, 'failed_login', 'Failed login attempt for user: CN001', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 12:26:10'),
(460, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 39.21ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 12:26:11'),
(461, 5, NULL, 'controller_nodal', 'user_login', '{\"user_id\":5,\"role\":\"controller_nodal\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 12:26:18'),
(462, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 75.8ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 12:26:50'),
(463, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 45.44ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 12:27:50'),
(464, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 34.76ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 12:28:50'),
(465, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 66.91ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 12:29:50'),
(466, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 74.07ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 12:30:50'),
(467, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 68.33ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 12:31:50'),
(468, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 63.96ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 12:32:50'),
(469, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 63.88ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 12:33:50'),
(470, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 49.84ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 12:34:50'),
(471, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 62.08ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 12:35:50'),
(472, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 83.11ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 12:36:50'),
(473, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 65.86ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 12:37:50'),
(474, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 68.1ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 12:38:50'),
(475, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 73.65ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 12:39:50'),
(476, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 59.89ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 12:40:50'),
(477, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 63.5ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 12:41:50'),
(478, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 62.26ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 12:42:50'),
(479, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 53.91ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 12:43:50'),
(480, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 62.63ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 12:44:50'),
(481, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 61.1ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 12:45:50'),
(482, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 54.95ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 12:46:50'),
(483, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 73.96ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 12:47:50'),
(484, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 64.94ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 12:48:50'),
(485, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 71.59ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 12:49:50'),
(486, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 59.56ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 12:50:50'),
(487, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 78.44ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 12:51:50'),
(488, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 69.65ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 12:52:50'),
(489, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 77.19ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 12:53:50'),
(490, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 69.82ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 12:54:50'),
(491, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 69.6ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 12:55:50'),
(492, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 78.07ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 12:56:50'),
(493, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 48.81ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 12:57:50'),
(494, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 89.46ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 12:58:50'),
(495, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 76.78ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 12:59:50'),
(496, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 62.39ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 13:00:50'),
(497, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 71.33ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 13:01:50'),
(498, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 65.73ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 13:02:50'),
(499, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 72.63ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 13:03:50'),
(500, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 67.42ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 13:04:50'),
(501, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 75.13ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 13:05:50'),
(502, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 61.05ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 13:06:50'),
(503, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 70.52ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 13:07:50'),
(504, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 66.96ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 13:08:50'),
(505, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 88.44ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 13:09:50'),
(506, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 62.03ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 13:10:50'),
(507, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 82.17ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 13:11:50'),
(508, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 79.22ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 13:12:50'),
(509, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 74.59ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 13:13:50'),
(510, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 86.34ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 13:14:50'),
(511, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 77.2ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 13:15:50'),
(512, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 58.5ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 13:16:50'),
(513, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 59.39ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 13:17:50'),
(514, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 89.34ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 13:18:50'),
(515, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 34.01ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 13:19:50'),
(516, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 71ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 13:20:50'),
(517, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 70.49ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 13:21:50'),
(518, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 62.81ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 13:22:50'),
(519, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 58.63ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 13:23:50'),
(520, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 57.74ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 13:24:50'),
(521, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 62.53ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 13:25:50'),
(522, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 77.14ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 13:26:50'),
(523, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 69.99ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 13:27:50'),
(524, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 73.63ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 13:28:50'),
(525, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 65.81ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 13:29:50'),
(526, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 70.18ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 13:30:50'),
(527, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 61.85ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 13:31:50'),
(528, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 71.15ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 13:32:50'),
(529, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 62.11ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 13:33:50'),
(530, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 58.36ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 13:34:50'),
(531, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 55.39ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 13:35:50'),
(532, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 67.09ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 13:36:50'),
(533, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 64.52ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 13:37:50'),
(534, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 70.77ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 13:38:50'),
(535, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 70.19ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 13:39:50'),
(536, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 54.88ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 13:40:50'),
(537, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 62.95ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 13:41:50'),
(538, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 58.83ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 13:42:50'),
(539, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 75.04ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 13:43:50'),
(540, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 57.71ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 13:44:50'),
(541, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 53.19ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 13:45:50'),
(542, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 60.65ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 13:46:50'),
(543, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 75.82ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 13:47:50'),
(544, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 55.79ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 13:48:50'),
(545, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 78.38ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 13:49:50'),
(546, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 63.83ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 13:50:50'),
(547, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 71.34ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 13:51:50'),
(548, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 69.11ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 13:52:50'),
(549, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 79.35ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 13:53:50'),
(550, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 82.24ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 13:54:50'),
(551, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 60.07ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 13:55:50'),
(552, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 63.02ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 13:56:50'),
(553, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 59.26ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 13:57:50'),
(554, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 64.18ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 13:58:50'),
(555, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 56.51ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 13:59:50'),
(556, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 52.6ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 14:00:50'),
(557, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 76.64ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 14:01:50'),
(558, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 54.74ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 14:02:50'),
(559, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 45.64ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 14:03:50'),
(560, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 49.02ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 14:04:50'),
(561, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 66.71ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 14:05:50'),
(562, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 85.42ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 14:06:50'),
(563, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 75.45ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 14:07:50'),
(564, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 67.08ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 14:08:50'),
(565, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 101.72ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 14:09:50'),
(566, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 61.77ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 14:10:50'),
(567, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 48.64ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 14:11:50'),
(568, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 62.13ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 14:12:50'),
(569, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 74.67ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 14:13:50'),
(570, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 66.81ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 14:14:50'),
(571, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 65.15ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 14:15:50'),
(572, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 31.74ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 14:16:50'),
(573, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 71.69ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 14:17:50'),
(574, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 71.34ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 14:18:50'),
(575, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 63.82ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 14:19:50'),
(576, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 59.39ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 14:20:50'),
(577, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 82.59ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 14:21:50'),
(578, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 97.5ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 14:22:50'),
(579, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 84.05ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 14:23:50'),
(580, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 58.34ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 14:24:50'),
(581, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 63.22ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 14:25:50'),
(582, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 73.92ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 14:26:50'),
(583, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 63.53ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 14:27:50'),
(584, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 59.15ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 14:28:50'),
(585, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 85.29ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 14:29:50'),
(586, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 78.9ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 14:30:50'),
(587, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 68.16ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 14:31:50'),
(588, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 69.45ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 14:32:50'),
(589, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 92.38ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 14:33:50');
INSERT INTO `activity_logs` (`id`, `user_id`, `customer_id`, `user_role`, `action`, `description`, `complaint_id`, `ip_address`, `user_agent`, `created_at`) VALUES
(590, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 64.3ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 14:34:50'),
(591, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 51.98ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 14:35:50'),
(592, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 98.58ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 14:36:50'),
(593, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 74.34ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 14:37:50'),
(594, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 87.49ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 14:38:50'),
(595, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 66.06ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 14:39:50'),
(596, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 70.72ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 14:40:50'),
(597, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 86.02ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 14:41:50'),
(598, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 68.34ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 14:42:50'),
(599, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 62.9ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 14:43:50'),
(600, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 74.52ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 14:44:50'),
(601, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 71.89ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 14:45:50'),
(602, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 40.79ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 14:46:50'),
(603, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 80.51ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 14:47:50'),
(604, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 66.86ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 14:48:50'),
(605, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 81.26ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 14:49:50'),
(606, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 70.58ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 14:50:50'),
(607, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 73.54ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 14:51:50'),
(608, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 78.75ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 14:52:50'),
(609, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 70.17ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 14:53:50'),
(610, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 67.43ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 14:54:50'),
(611, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 57.65ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 14:55:50'),
(612, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 69.49ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 14:56:50'),
(613, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 100.04ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 14:57:50'),
(614, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 74.92ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 14:58:50'),
(615, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 68.99ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 14:59:50'),
(616, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 68.33ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 15:00:50'),
(617, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 90.52ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 15:01:50'),
(618, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 77.89ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 15:02:50'),
(619, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 77.92ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 15:03:50'),
(620, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 73.99ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 15:04:50'),
(621, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 83.44ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 15:05:50'),
(622, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 51.09ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 15:06:50'),
(623, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 59.84ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 15:07:50'),
(624, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 65.4ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 15:08:50'),
(625, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 82.81ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 15:09:50'),
(626, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 80.87ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 15:10:50'),
(627, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 79.98ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 15:11:50'),
(628, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 86.26ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 15:12:50'),
(629, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 75.74ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 15:13:50'),
(630, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 73.27ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 15:14:50'),
(631, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 81.32ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 15:15:50'),
(632, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 71.12ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 15:16:50'),
(633, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 60.35ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 15:17:50'),
(634, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 66.35ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 15:18:50'),
(635, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 74.23ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 15:19:50'),
(636, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 64.74ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 15:20:50'),
(637, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 93.88ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 15:21:50'),
(638, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 67.45ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 15:22:50'),
(639, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 78.18ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 15:23:50'),
(640, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 78.29ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 15:24:50'),
(641, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 74.66ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 15:25:50'),
(642, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 77.22ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 15:26:50'),
(643, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 69.81ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 15:27:50'),
(644, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 65.64ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 15:28:50'),
(645, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 46.4ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 15:29:50'),
(646, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 40.71ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 15:30:50'),
(647, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 47.26ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 15:31:50'),
(648, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 69.32ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 15:32:51'),
(649, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 66.81ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 15:33:50'),
(650, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 79.21ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 15:34:50'),
(651, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 70.89ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 15:35:50'),
(652, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 62.93ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 15:36:50'),
(653, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 77.13ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 15:37:50'),
(654, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 67.26ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 15:38:50'),
(655, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 64.01ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 15:39:50'),
(656, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 52.04ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 15:40:50'),
(657, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 72.12ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 15:41:50'),
(658, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 66.08ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 15:42:50'),
(659, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 63.46ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 15:43:50'),
(660, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 69.4ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 15:44:50'),
(661, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 68.7ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 15:45:50'),
(662, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 39.82ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 15:46:50'),
(663, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 65.32ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 15:47:50'),
(664, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 69.87ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 15:48:50'),
(665, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 67.05ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 15:49:50'),
(666, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 86.64ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 15:50:50'),
(667, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 58.05ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 15:51:50'),
(668, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 66.81ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 15:52:50'),
(669, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 81.47ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 15:53:50'),
(670, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 74.79ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 15:54:50'),
(671, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 56.84ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 15:55:50'),
(672, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 41.97ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 15:56:50'),
(673, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 71.67ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 15:57:50'),
(674, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 62.13ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 15:58:50'),
(675, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 66.32ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 15:59:50'),
(676, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 62.51ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 16:00:50'),
(677, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 57.87ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 16:01:50'),
(678, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 68.02ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 16:02:50'),
(679, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 63.91ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 16:03:50'),
(680, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 65ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 16:04:50'),
(681, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 74.4ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 16:05:50'),
(682, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 66.75ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 16:06:50'),
(683, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 78.18ms', NULL, '10.31.210.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 16:07:50'),
(684, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 10.8ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 16:39:27'),
(685, NULL, NULL, NULL, 'failed_login', 'Failed login attempt for customer: 9876543210', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 16:40:10'),
(686, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 18.73ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 16:40:12'),
(687, NULL, 'CUST2025090001', 'customer', 'customer_login', '{\"customer_id\":\"CUST2025090001\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 16:40:25'),
(688, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 14.37ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 16:40:49'),
(689, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 18.58ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 16:41:49'),
(690, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 10.96ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 16:42:49'),
(691, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 14.71ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 16:43:48'),
(692, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 14.41ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 16:45:04'),
(693, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 16.65ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 16:45:49'),
(694, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 10.7ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 16:46:24'),
(695, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 13.27ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 16:47:37'),
(696, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 13.88ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 16:48:09'),
(697, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 19.67ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 16:49:12'),
(698, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 13.71ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 16:50:12'),
(699, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 15.86ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 16:51:12'),
(700, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 17.78ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 16:52:12'),
(701, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 16.6ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 16:53:12'),
(702, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 10.98ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 16:54:12'),
(703, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 11.07ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 16:55:12'),
(704, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 15ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 16:56:13'),
(705, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 14.44ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 16:56:47'),
(706, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 13.47ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 16:57:30'),
(707, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 12.51ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 16:58:02'),
(708, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 7.88ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 16:58:50'),
(709, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 16.37ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 16:59:22'),
(710, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 12.42ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 17:00:22'),
(711, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 12.66ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 17:01:22'),
(712, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 11.02ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 17:02:49'),
(713, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 14.33ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 17:03:49'),
(714, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 13.25ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 17:04:49'),
(715, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 86.15ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 17:05:21'),
(716, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 16.24ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 17:05:52'),
(717, NULL, 'CUST2025090001', 'customer', 'logout', '{\"previous_role\":\"customer\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 17:06:38'),
(718, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 11.05ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 17:06:39'),
(719, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 11.43ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 17:07:45'),
(720, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 15.55ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 17:08:45'),
(721, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 12.2ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 17:09:45'),
(722, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 11.81ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 17:10:32'),
(723, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 13.54ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 17:11:31'),
(724, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 14.3ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 17:12:32'),
(725, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 18.97ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 17:13:32'),
(726, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 12.19ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 17:14:25'),
(727, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 16.14ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 17:15:38'),
(728, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 19ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 17:16:38'),
(729, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 9.19ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 17:17:38'),
(730, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 15.61ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 17:18:11'),
(731, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 10.9ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 17:18:46'),
(732, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 12.65ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 17:19:45'),
(733, NULL, NULL, NULL, 'failed_login', 'Failed login attempt for customer: 9876543211', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 17:19:46'),
(734, NULL, NULL, NULL, 'failed_login', 'Failed login attempt for customer: 9876543211', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 17:20:06'),
(735, NULL, 'CUST2025090001', 'customer', 'customer_login', '{\"customer_id\":\"CUST2025090001\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 17:20:15'),
(736, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 10.9ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 17:20:17'),
(737, NULL, 'CUST2025090001', 'customer', 'logout', '{\"previous_role\":\"customer\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 17:21:03'),
(738, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 11.87ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 17:21:04'),
(739, NULL, 'CUST20250107', 'customer', 'customer_login', '{\"customer_id\":\"CUST20250107\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 17:21:14'),
(740, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 10.25ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 17:22:19'),
(741, NULL, 'CUST20250107', 'customer', 'logout', '{\"previous_role\":\"customer\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 17:22:30'),
(742, 5, NULL, 'controller_nodal', 'user_login', '{\"user_id\":5,\"role\":\"controller_nodal\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 17:22:55'),
(743, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 14.45ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 17:22:57'),
(744, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 9.23ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 17:23:49'),
(745, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 17.05ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 17:24:23'),
(746, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 13.78ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 17:25:23'),
(747, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 10.53ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 17:26:23'),
(748, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 11.27ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 17:27:23'),
(749, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 11.67ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 17:28:23'),
(750, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 13.49ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 17:29:23'),
(751, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 10.43ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 17:33:18'),
(752, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 10.64ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 17:34:18'),
(753, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 12.27ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 17:35:17'),
(754, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 11.07ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 17:36:04'),
(755, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 12.63ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 17:36:51'),
(756, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 13.56ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 17:37:30'),
(757, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 13.23ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 17:38:30'),
(758, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 9.37ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 17:39:11'),
(759, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 11.78ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 17:39:43'),
(760, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 14.78ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 17:40:51'),
(761, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 11.88ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 17:41:51'),
(762, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 19.42ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 17:42:51'),
(763, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 12.62ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 17:43:51'),
(764, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 10.21ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 17:45:08'),
(765, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 12.2ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 17:46:00'),
(766, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 19.17ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 17:47:09'),
(767, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 11.2ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 17:48:02'),
(768, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 17.48ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 17:49:02'),
(769, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 15.24ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 17:50:02'),
(770, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 16.77ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 17:51:02'),
(771, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 14.21ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 17:52:02'),
(772, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 15.56ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 17:53:02'),
(773, 5, NULL, 'controller_nodal', 'logout', '{\"previous_role\":\"controller_nodal\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 17:53:41'),
(774, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 14.98ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 17:53:42'),
(775, NULL, 'CUST2025090001', 'customer', 'customer_login', '{\"customer_id\":\"CUST2025090001\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 17:53:55'),
(776, NULL, 'CUST2025090001', 'customer', 'logout', '{\"previous_role\":\"customer\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 17:54:28'),
(777, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 14.02ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 17:54:30'),
(778, NULL, NULL, NULL, 'failed_login', 'Failed login attempt for user: CN001', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 17:54:40'),
(779, 5, NULL, 'controller_nodal', 'user_login', '{\"user_id\":5,\"role\":\"controller_nodal\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 17:54:58'),
(780, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 11.15ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 17:55:02'),
(781, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 11.29ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 17:56:02'),
(782, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 14.74ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 17:57:02'),
(783, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 15.25ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 17:58:02');
INSERT INTO `activity_logs` (`id`, `user_id`, `customer_id`, `user_role`, `action`, `description`, `complaint_id`, `ip_address`, `user_agent`, `created_at`) VALUES
(784, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 13.12ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 17:59:02'),
(785, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 13.36ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 18:00:02'),
(786, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 13.79ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 18:01:02'),
(787, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 14.94ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 18:02:02'),
(788, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 13.32ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 18:02:38'),
(789, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 12.33ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 18:03:42'),
(790, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 10.64ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 18:04:21'),
(791, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 8.28ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 18:04:57'),
(792, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 11.41ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 18:05:56'),
(793, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 9.57ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 18:06:32'),
(794, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 11.4ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 18:07:35'),
(795, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 10.17ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 18:08:43'),
(796, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 16.04ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 18:09:43'),
(797, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 11.66ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 18:10:43'),
(798, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 11.33ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 18:11:43'),
(799, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 17.72ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 18:12:43'),
(800, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 16.42ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 18:13:47'),
(801, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 10.82ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 18:14:47'),
(802, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 12.25ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 18:15:47'),
(803, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 13.59ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 18:16:47'),
(804, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 17.58ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 18:17:47'),
(805, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 11.07ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 18:18:47'),
(806, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 9ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 18:19:47'),
(807, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 12.75ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 18:20:47'),
(808, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 13.77ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 18:21:55'),
(809, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 17.52ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 18:22:56'),
(810, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 14.35ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 18:23:56'),
(811, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 14.69ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 18:24:56'),
(812, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 12.48ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 18:25:56'),
(813, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 15.58ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 18:26:56'),
(814, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 10.59ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 18:28:19'),
(815, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 10.61ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 18:29:19'),
(816, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 8.76ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 18:30:19'),
(817, NULL, NULL, 'system', 'system_background_automation', 'Processed 5 automation tasks in 12.28ms', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 18:31:17');

-- --------------------------------------------------------

--
-- Table structure for table `complaints`
--

CREATE TABLE `complaints` (
  `complaint_id` varchar(20) NOT NULL,
  `category_id` int(11) NOT NULL,
  `date` date NOT NULL,
  `time` time NOT NULL,
  `shed_id` int(11) NOT NULL,
  `wagon_id` int(11) DEFAULT NULL,
  `rating` enum('excellent','satisfactory','unsatisfactory') DEFAULT NULL,
  `rating_remarks` text DEFAULT NULL,
  `description` text NOT NULL,
  `action_taken` text DEFAULT NULL,
  `status` enum('pending','awaiting_feedback','awaiting_info','awaiting_approval','closed') DEFAULT 'pending',
  `department` varchar(100) DEFAULT NULL,
  `division` varchar(100) NOT NULL,
  `zone` varchar(100) NOT NULL,
  `customer_id` varchar(20) NOT NULL,
  `fnr_number` varchar(50) DEFAULT NULL,
  `e_indent_number` varchar(50) DEFAULT NULL,
  `assigned_to_department` varchar(100) DEFAULT NULL,
  `forwarded_flag` tinyint(1) DEFAULT 0,
  `priority` enum('normal','medium','high','critical') DEFAULT 'normal',
  `sla_deadline` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `closed_at` timestamp NULL DEFAULT NULL,
  `escalated_at` timestamp NULL DEFAULT NULL,
  `escalation_stopped` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `complaints`
--

INSERT INTO `complaints` (`complaint_id`, `category_id`, `date`, `time`, `shed_id`, `wagon_id`, `rating`, `rating_remarks`, `description`, `action_taken`, `status`, `department`, `division`, `zone`, `customer_id`, `fnr_number`, `e_indent_number`, `assigned_to_department`, `forwarded_flag`, `priority`, `sla_deadline`, `created_at`, `updated_at`, `closed_at`, `escalated_at`, `escalation_stopped`) VALUES
('202509090001', 3, '2025-09-09', '17:33:03', 210, 1012, 'unsatisfactory', 'ok', '12dad d aafaf   eewqewq qqj hgig iy78aaa7 a9 9dy adihd', NULL, 'awaiting_feedback', NULL, 'CSMT', 'CR', 'CUST2025090001', '', '', 'Commercial', 0, 'normal', NULL, '2025-09-09 12:03:03', '2025-09-11 10:18:52', '2025-09-11 08:14:17', '2025-09-11 10:18:52', 0),
('202509100001', 6, '2025-09-10', '11:59:14', 266, 1024, NULL, NULL, 'qwqwqwqwqwqqwqwqqwwqwwqw\n\n--- Additional Info ---\nOk', NULL, 'pending', NULL, 'CSMT', 'CR', 'CUST2025090001', '', '', 'Commercial', 0, 'medium', '2025-09-13 08:26:49', '2025-09-10 06:29:14', '2025-09-11 10:08:27', NULL, '2025-09-11 08:26:49', 0),
('202509100002', 9, '2025-09-10', '12:00:21', 97, 1012, NULL, NULL, '21212addad ad dq 2e23sda', NULL, 'awaiting_info', NULL, 'CSMT', 'CR', 'CUST2025090001', '12', '12', 'Commercial', 0, 'medium', '2025-09-13 08:26:49', '2025-09-10 06:30:21', '2025-09-11 10:18:47', NULL, '2025-09-11 08:26:49', 0),
('202509100003', 56, '2025-09-10', '13:25:20', 164, 1028, NULL, NULL, '12qw qwqwq q qwqw q qwqw2', NULL, 'awaiting_feedback', NULL, 'CSMT', 'CR', 'CUST2025090001', '12', '', 'Commercial', 0, 'medium', '2025-09-13 08:26:49', '2025-09-10 07:55:20', '2025-09-11 16:57:25', NULL, '2025-09-11 08:26:49', 0),
('202509100004', 15, '2025-09-10', '15:01:35', 61, NULL, NULL, NULL, '12121  wsdsdss 1212 sssdewsss', NULL, 'pending', NULL, 'CSMT', 'CR', 'CUST2025090001', '11222', '', 'Commercial', 0, 'medium', '2025-09-13 08:26:50', '2025-09-10 09:31:35', '2025-09-11 08:26:50', NULL, '2025-09-11 08:26:50', 0),
('202509100005', 6, '2025-09-10', '19:00:59', 30, 1026, NULL, NULL, '121212 1 121212 121212', NULL, 'pending', NULL, 'CSMT', 'CR', 'CUST2025090001', '12', '12', 'Commercial', 0, 'medium', '2025-09-13 08:26:50', '2025-09-10 13:30:59', '2025-09-11 08:26:50', NULL, '2025-09-11 08:26:50', 0),
('202509100006', 20, '2025-09-10', '19:06:15', 210, 1012, NULL, NULL, '1212121212 1 12 1212 1', NULL, 'pending', NULL, 'CSMT', 'CR', 'CUST2025090001', '12', '12', 'Commercial', 0, 'medium', '2025-09-13 08:26:50', '2025-09-10 13:36:15', '2025-09-11 08:26:50', NULL, '2025-09-11 08:26:50', 0);

-- --------------------------------------------------------

--
-- Table structure for table `complaint_categories`
--

CREATE TABLE `complaint_categories` (
  `category_id` int(11) NOT NULL,
  `category` varchar(100) NOT NULL,
  `type` varchar(100) NOT NULL,
  `subtype` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `complaint_categories`
--

INSERT INTO `complaint_categories` (`category_id`, `category`, `type`, `subtype`) VALUES
(3, 'Assistance', 'Delivery', 'Diversion Permission'),
(1, 'Assistance', 'Delivery', 'eTRR Transfer/Surrender'),
(5, 'Assistance', 'Delivery', 'Open/Assesment Delivery'),
(2, 'Assistance', 'Delivery', 'Rebooking Permission'),
(4, 'Assistance', 'Delivery', 'Short Delivery'),
(7, 'Assistance', 'Loading/Unloading', 'Forecast of Loading/Unloading Completion Time'),
(6, 'Assistance', 'Loading/Unloading', 'Stacking Permission'),
(8, 'Assistance', 'Payment', 'Adjustment of Overcharges'),
(9, 'Assistance', 'Registration', 'Change in Details of Company Registration'),
(10, 'Assistance', 'Registration', 'Change in eDemand User Registration'),
(12, 'Assistance', 'Transit', 'Requirement Time of Engine '),
(11, 'Assistance', 'Weighment and RR', 'Correction of GSTIN in RR'),
(15, 'Complaint', 'Amenities in Depot', 'Bad Condition of Toilet/Drinking Water/ Approach Road / Merchant / Labour Rooms'),
(14, 'Complaint', 'Amenities in Depot', 'Improper Lighting Arrangement in Shed'),
(13, 'Complaint', 'Amenities in Depot', 'Non Availability of proper Amenities in Shed - Toilet/Drinking Water/ Approach Road  / Merchant / La'),
(19, 'Complaint', 'Delivery', 'Derailment Wagon Restoration Charges'),
(18, 'Complaint', 'Delivery', 'Incorrect Levy of Wagon Damage Charges'),
(16, 'Complaint', 'Delivery', 'Issues in E-way Bill Linking'),
(17, 'Complaint', 'Delivery', 'Overdue/ Not Recieved Wagons'),
(20, 'Complaint', 'Loading/Unloading', 'Non Avialability of Space in wharf'),
(21, 'Complaint', 'Loading/Unloading', 'Unavailability of Labour in Odd Hours'),
(42, 'Complaint ', 'Overcharging', 'Freight Collected against Sick Wagons'),
(41, 'Complaint ', 'Overcharging', 'Incorrect Land Leasing Charges'),
(44, 'Complaint ', 'Overcharging', 'Incorrect Staff Charges'),
(43, 'Complaint ', 'Overcharging', 'Overcharging in Demmurage'),
(40, 'Complaint ', 'Overcharging', 'Overcharging in Freight'),
(48, 'Complaint ', 'Overcharging', 'Overcharging in Freight due to RBS Error'),
(47, 'Complaint ', 'Overcharging', 'Overcharging in Shunting Charges'),
(46, 'Complaint ', 'Overcharging', 'Overcharging in Stabling charges'),
(45, 'Complaint ', 'Overcharging', 'Overcharging in Wharfage/Ground Usage'),
(60, 'Complaint ', 'Overcharging', 'Siding Maintainance Charges '),
(49, 'Complaint ', 'Payment', 'Aount Debited but RR not Generated'),
(50, 'Complaint ', 'Payment', 'Refund of Failed Transaction'),
(28, 'Complaint', 'Security', 'Non Availability of Security'),
(27, 'Complaint', 'Security', 'Theft enroute'),
(26, 'Complaint', 'Security', 'Theft in Depots'),
(61, 'Complaint ', 'Siding', 'Delay/Pending Siding Agreement'),
(29, 'Complaint', 'Staff Issues', 'Improper Behaviour of Railway Staff'),
(31, 'Complaint', 'Transit', 'Detention in Transit'),
(30, 'Complaint', 'Transit', 'Late Dispatch'),
(32, 'Complaint', 'Transit', 'Missing Lashing/Packing/Sealing'),
(35, 'Complaint', 'Wagon Supply', 'Damaged Wagons - Fit to Run & Unfit for Loading'),
(33, 'Complaint', 'Wagon Supply', 'Delay in Supply of Wagons'),
(62, 'Complaint ', 'Wagon Supply', 'Missing of Fittings'),
(38, 'Complaint', 'Wagon Supply', 'Odd time Placement of Wagons'),
(37, 'Complaint', 'Wagon Supply', 'Supply of Wagons - Different Type than Indented'),
(34, 'Complaint', 'Wagon Supply', 'Supply of Wagons Less than Indented'),
(36, 'Complaint', 'Wagon Supply', 'Unclean Wagons'),
(39, 'Complaint', 'Wagon Supply', 'Violation of ODR'),
(23, 'Complaint', 'Weighment and RR', 'Delay in Loco/Crew RR Preperation'),
(22, 'Complaint', 'Weighment and RR', 'Delay in RR - Electornic Data Interface'),
(59, 'Complaint ', 'Weighment and RR', 'E-TRR not Received'),
(24, 'Complaint', 'Weighment and RR', 'Incorrect Weighment '),
(25, 'Complaint', 'Weighment and RR', 'Levy of Late Payment Charges'),
(63, 'Complaint ', 'Weighment and RR', 'Weighment Failure'),
(51, 'Enquiry', 'Booking', 'eDemand Registration Help'),
(52, 'Enquiry', 'Booking', 'Modes of Payment Query'),
(56, 'Enquiry', 'Registration', 'Co-user permission'),
(53, 'Enquiry', 'Registration', 'e-Registration of Company'),
(55, 'Enquiry', 'Registration', 'Proposal submission for new traffic'),
(54, 'Enquiry', 'Registration', 'Required Documents'),
(57, 'Enquiry', 'Transit', 'FNR Enquiry, Transit of consignment'),
(58, 'Enquiry', 'Wagon Supply', 'Expected Date & Time of Wagon Supply');

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

CREATE TABLE `customers` (
  `customer_id` varchar(20) NOT NULL,
  `password` varchar(255) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `mobile` varchar(15) NOT NULL,
  `company_name` varchar(150) NOT NULL,
  `designation` varchar(100) DEFAULT NULL,
  `gstin` varchar(15) DEFAULT NULL,
  `customer_type` enum('individual','corporate','government') DEFAULT 'individual',
  `role` enum('customer') DEFAULT 'customer',
  `status` enum('pending','approved','rejected','suspended') DEFAULT 'pending',
  `division` varchar(50) DEFAULT NULL,
  `zone` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_by` varchar(50) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `customers`
--

INSERT INTO `customers` (`customer_id`, `password`, `name`, `email`, `mobile`, `company_name`, `designation`, `gstin`, `customer_type`, `role`, `status`, `division`, `zone`, `created_at`, `created_by`, `updated_at`) VALUES
('CUST20250101', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Rohit Agarwal', 'rohit.agarwal@abclogistics.com', '9123456780', 'ABC Logistics Pvt Ltd', 'General Manager', '22AAAAA0000A1Z5', 'individual', 'customer', 'approved', 'Sealdah', 'Eastern', '2025-09-03 11:52:21', '2', '2025-09-03 11:52:21'),
('CUST20250102', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Priya Mehta', 'priya.mehta@xyztrading.com', '9123456781', 'XYZ Trading Company', 'Operations Head', '19BBBBB1111B2Y4', 'individual', 'customer', 'approved', 'Mumbai', 'Central', '2025-09-03 11:52:21', '3', '2025-09-03 11:52:21'),
('CUST20250103', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Amit Sharma', 'amit.sharma@defcargo.com', '9123456782', 'DEF Cargo Solutions', 'Director', '07CCCCC2222C3X3', 'individual', 'customer', 'approved', 'Delhi', 'Northern', '2025-09-03 11:52:21', '4', '2025-09-03 11:52:21'),
('CUST20250104', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Kavitha Reddy', 'kavitha.reddy@ghifreight.com', '9123456783', 'GHI Freight Services', 'Manager', '33DDDDD3333D4W2', 'individual', 'customer', 'approved', 'Chennai', 'Southern', '2025-09-03 11:52:21', '6', '2025-09-03 11:52:21'),
('CUST20250105', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Sanjay Gupta', 'sanjay.gupta@jkltransport.com', '9123456784', 'JKL Transport Corporation', 'Senior Executive', '27EEEEE4444E5V1', 'individual', 'customer', 'approved', 'Howrah', 'Eastern', '2025-09-03 11:52:21', '2', '2025-09-03 11:52:21'),
('CUST20250106', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Neha Joshi', 'neha.joshi@mnoexport.com', '9123456785', 'MNO Export House', 'Export Manager', '14FFFFF5555F6U0', 'individual', 'customer', 'pending', 'Mumbai Central', 'Western', '2025-09-03 11:52:21', '3', '2025-09-03 11:52:21'),
('CUST20250107', '$2y$10$pLYUe3NUVCS/ogXxkk7Pg.NPwU25VJRFJs7FXXOzkU72nfW47sgsa', 'Rajesh Kumar', 'rajesh.kumar@pqrimpex.com', '9876543211', 'PQR Impex Limited', 'Managing Director', '', 'individual', 'customer', 'approved', 'Sealdah', 'Eastern', '2025-09-03 11:52:21', '2', '2025-09-11 17:21:14'),
('CUST2025090001', '$2y$10$pLYUe3NUVCS/ogXxkk7Pg.NPwU25VJRFJs7FXXOzkU72nfW47sgsa', 'TEST', 'test@gmail.com', '9876543210', 'IT CELL', 'Sr CCTC', NULL, 'individual', 'customer', 'approved', 'Mumbai', 'Central', '2025-09-08 07:08:17', NULL, '2025-09-11 17:53:55');

-- --------------------------------------------------------

--
-- Table structure for table `departments`
--

CREATE TABLE `departments` (
  `department_id` int(11) NOT NULL,
  `department_code` varchar(20) NOT NULL,
  `department_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `departments`
--

INSERT INTO `departments` (`department_id`, `department_code`, `department_name`, `description`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'CML', 'Commercial', 'Commercial Department - Revenue, freight operations, passenger services', 1, '2025-09-11 18:20:49', '2025-09-11 18:24:59'),
(2, 'ENG', 'Engineering', 'Technical Department - Infrastructure, signaling, telecommunications', 1, '2025-09-11 18:20:49', '2025-09-11 18:25:14'),
(3, 'C&W', 'Mechanical', 'Mechanical Department - Rolling stock maintenance, workshops', 1, '2025-09-11 18:20:49', '2025-09-11 18:26:11'),
(4, 'ELE', 'Electrical', 'Electrical Department - Electrical systems, traction, power supply', 1, '2025-09-11 18:20:49', '2025-09-11 18:25:19'),
(5, 'S&T', 'Signal & Telecom', 'Signal & Telecommunication Department', 1, '2025-09-11 18:20:49', '2025-09-11 18:20:49'),
(7, 'OPTG', 'Operating', 'Operations Department - Train operations, crew management', 1, '2025-09-11 18:20:49', '2025-09-11 18:25:36'),
(8, 'RPF', 'Security', 'Security Department - Railway Protection Force, safety', 1, '2025-09-11 18:20:49', '2025-09-11 18:25:42');

-- --------------------------------------------------------

--
-- Table structure for table `divisions`
--

CREATE TABLE `divisions` (
  `division_id` int(11) NOT NULL,
  `division_code` varchar(10) NOT NULL,
  `division_name` varchar(100) NOT NULL,
  `zone_id` int(11) NOT NULL,
  `zone_code` varchar(10) NOT NULL,
  `headquarters` varchar(100) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `divisions`
--

INSERT INTO `divisions` (`division_id`, `division_code`, `division_name`, `zone_id`, `zone_code`, `headquarters`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'CSMT', 'Mumbai CSMT Division', 1, 'CR', 'Mumbai CSMT', 1, '2025-09-11 18:20:49', '2025-09-11 18:20:49'),
(2, 'BSL', 'Bhusaval Division', 1, 'CR', 'Bhusaval', 1, '2025-09-11 18:20:49', '2025-09-11 18:20:49'),
(3, 'NGP', 'Nagpur Division', 1, 'CR', 'Nagpur', 1, '2025-09-11 18:20:49', '2025-09-11 18:20:49'),
(4, 'PUNE', 'Pune Division', 1, 'CR', 'Pune', 1, '2025-09-11 18:20:49', '2025-09-11 18:20:49'),
(5, 'SOLAPUR', 'Solapur Division', 1, 'CR', 'Solapur', 1, '2025-09-11 18:20:49', '2025-09-11 18:20:49'),
(6, 'BRC', 'Vadodara Division', 2, 'WR', 'Vadodara', 1, '2025-09-11 18:20:49', '2025-09-11 18:20:49'),
(7, 'RTM', 'Ratlam Division', 2, 'WR', 'Ratlam', 1, '2025-09-11 18:20:49', '2025-09-11 18:20:49'),
(8, 'ADI', 'Ahmedabad Division', 2, 'WR', 'Ahmedabad', 1, '2025-09-11 18:20:49', '2025-09-11 18:20:49'),
(9, 'RJT', 'Rajkot Division', 2, 'WR', 'Rajkot', 1, '2025-09-11 18:20:49', '2025-09-11 18:20:49'),
(10, 'BB', 'Mumbai Central Division', 2, 'WR', 'Mumbai Central', 1, '2025-09-11 18:20:49', '2025-09-11 18:20:49'),
(11, 'DLI', 'Delhi Division', 3, 'NR', 'New Delhi', 1, '2025-09-11 18:20:49', '2025-09-11 18:20:49'),
(12, 'FZR', 'Firozpur Division', 3, 'NR', 'Firozpur', 1, '2025-09-11 18:20:49', '2025-09-11 18:20:49'),
(13, 'LDH', 'Ludhiana Division', 3, 'NR', 'Ludhiana', 1, '2025-09-11 18:20:49', '2025-09-11 18:20:49'),
(14, 'AMB', 'Ambala Division', 3, 'NR', 'Ambala', 1, '2025-09-11 18:20:49', '2025-09-11 18:20:49');

-- --------------------------------------------------------

--
-- Table structure for table `email_templates`
--

CREATE TABLE `email_templates` (
  `template_id` int(11) NOT NULL,
  `template_name` varchar(100) NOT NULL,
  `template_code` varchar(50) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `body_html` text NOT NULL,
  `body_text` text DEFAULT NULL,
  `variables` text DEFAULT NULL COMMENT 'JSON array of available variables',
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `email_templates`
--

INSERT INTO `email_templates` (`template_id`, `template_name`, `template_code`, `subject`, `body_html`, `body_text`, `variables`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Ticket Created', 'ticket_created', 'Support Ticket Created - {{complaint_id}}', '<h2>Support Ticket Created</h2><p>Dear {{customer_name}},</p><p>Your support ticket has been created successfully.</p><p><strong>Ticket ID:</strong> {{complaint_id}}</p><p><strong>Subject:</strong> {{subject}}</p><p>We will respond within 24-48 hours.</p><p>Best regards,<br>SAMPARK Support Team</p>', NULL, '[\"customer_name\",\"complaint_id\",\"subject\"]', 1, '2025-09-03 11:51:58', '2025-09-03 11:51:58'),
(2, 'Ticket Assigned', 'ticket_assigned', 'Support Ticket Assigned - {{complaint_id}}', '<h2>Support Ticket Assigned</h2><p>Dear Team,</p><p>A support ticket has been assigned to you.</p><p><strong>Ticket ID:</strong> {{complaint_id}}</p><p><strong>Customer:</strong> {{customer_name}}</p><p><strong>Priority:</strong> {{priority}}</p><p>Please review and take appropriate action.</p>', NULL, '[\"complaint_id\",\"customer_name\",\"priority\"]', 1, '2025-09-03 11:51:58', '2025-09-03 11:51:58'),
(3, 'Priority Escalated', 'priority_escalated', 'Priority Escalated - {{complaint_id}}', '<h2>Priority Escalated</h2><p>Alert: Ticket priority has been escalated.</p><p><strong>Ticket ID:</strong> {{complaint_id}}</p><p><strong>New Priority:</strong> {{priority}}</p><p><strong>Customer:</strong> {{customer_name}}</p><p>Immediate attention required.</p>', NULL, '[\"complaint_id\",\"priority\",\"customer_name\"]', 1, '2025-09-03 11:51:58', '2025-09-03 11:51:58');

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
  `uploaded_by_type` enum('customer','user') NOT NULL,
  `uploaded_by_id` varchar(50) NOT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `evidence`
--

INSERT INTO `evidence` (`id`, `complaint_id`, `file_name_1`, `file_name_2`, `file_name_3`, `file_type_1`, `file_type_2`, `file_type_3`, `file_path_1`, `file_path_2`, `file_path_3`, `compressed_size_1`, `compressed_size_2`, `compressed_size_3`, `uploaded_by_type`, `uploaded_by_id`, `uploaded_at`) VALUES
(1, '202509100003', '202509100003_file1.jpeg', '202509100003_file2.jpg', '202509100003_file3.png', 'jpeg', 'jpg', 'png', '202509100003_file1.jpeg', '202509100003_file2.jpg', '202509100003_file3.png', 580831, 83040, 542911, 'customer', 'CUST2025090001', '2025-09-10 07:55:20'),
(2, '202509100004', '202509100004_file1.jpeg', NULL, NULL, 'jpeg', NULL, NULL, '202509100004_file1.jpeg', NULL, NULL, 580831, NULL, NULL, 'customer', 'CUST2025090001', '2025-09-10 09:31:35'),
(3, '202509100005', '202509100005_file1.jpg', '202509100005_file2.jpg', NULL, 'jpg', 'jpg', NULL, '202509100005_file1.jpg', '202509100005_file2.jpg', NULL, 1407242, 1847928, NULL, 'customer', 'CUST2025090001', '2025-09-10 13:30:59'),
(4, '202509100006', '202509100006_file1.jpeg', NULL, NULL, 'jpeg', NULL, NULL, '202509100006_file1.jpeg', NULL, NULL, 580831, NULL, NULL, 'customer', 'CUST2025090001', '2025-09-10 13:36:15');

-- --------------------------------------------------------

--
-- Table structure for table `evidence_backup`
--

CREATE TABLE `evidence_backup` (
  `id` int(11) NOT NULL DEFAULT 0,
  `complaint_id` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `original_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_size` int(11) NOT NULL,
  `file_type` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_path` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `compressed_size` int(11) DEFAULT NULL,
  `uploaded_by_type` enum('customer','user') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `uploaded_by_id` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `evidence_backup`
--

INSERT INTO `evidence_backup` (`id`, `complaint_id`, `file_name`, `original_name`, `file_size`, `file_type`, `file_path`, `compressed_size`, `uploaded_by_type`, `uploaded_by_id`, `uploaded_at`) VALUES
(1, '202509100001', '202509100001_file1.jpeg', 'WhatsApp Image 2025-09-08 at 1.31.32 PM.jpeg', 580831, 'jpeg', '202509100001_file1.jpeg', 580831, 'customer', 'CUST2025090001', '2025-09-10 06:29:15'),
(2, '202509100002', '202509100002_file1.jpeg', 'WhatsApp Image 2025-09-08 at 1.31.32 PM.jpeg', 580831, 'jpeg', '202509100002_file1.jpeg', 580831, 'customer', 'CUST2025090001', '2025-09-10 06:30:21'),
(3, '202509100002', '202509100002_file2.png', 'Gemini_Generated_Image_3uj403uj403uj403-removebg-preview.png', 542911, 'png', '202509100002_file2.png', 542911, 'customer', 'CUST2025090001', '2025-09-10 06:30:21');

-- --------------------------------------------------------

--
-- Table structure for table `news`
--

CREATE TABLE `news` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `short_description` varchar(500) DEFAULT NULL,
  `type` enum('news','announcement','alert','update') DEFAULT 'news',
  `priority` enum('low','medium','high','urgent') DEFAULT 'medium',
  `is_active` tinyint(1) DEFAULT 1,
  `show_on_homepage` tinyint(1) DEFAULT 1,
  `show_on_marquee` tinyint(1) DEFAULT 0,
  `publish_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `expire_date` timestamp NULL DEFAULT NULL,
  `division_specific` varchar(100) DEFAULT NULL,
  `zone_specific` varchar(100) DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `news`
--

INSERT INTO `news` (`id`, `title`, `content`, `short_description`, `type`, `priority`, `is_active`, `show_on_homepage`, `show_on_marquee`, `publish_date`, `expire_date`, `division_specific`, `zone_specific`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'SAMPARK Portal Launched for Enhanced Customer Support', '<p>We are pleased to announce the launch of SAMPARK (Support and Mediation Portal for All Rail Cargo), a comprehensive digital platform designed to streamline freight customer support services across Indian Railways.</p>\r\n    <p><strong>Key Features:</strong></p>\r\n    <ul>\r\n    <li>Online ticket submission and tracking</li>\r\n    <li>Real-time status updates</li>\r\n    <li>Document upload capabilities</li>\r\n    <li>Automated escalation system</li>\r\n    <li>Mobile-responsive design</li>\r\n    </ul>\r\n    <p>This portal will significantly reduce response times and improve the overall customer experience for freight services.</p>', 'Launch of SAMPARK portal for improved freight customer support with online ticket tracking and real-time updates.', 'news', 'high', 1, 1, 1, '2025-09-11 17:10:27', '2025-10-11 17:10:27', NULL, NULL, 1, '2025-09-11 17:10:27', '2025-09-11 17:10:27'),
(2, 'Enhanced Security Measures Implemented', '<p>Indian Railways has implemented enhanced security protocols across all freight terminals and goods sheds to ensure the safety of cargo and personnel.</p>\r\n    <p><strong>New Security Features:</strong></p>\r\n    <ul>\r\n    <li>24/7 CCTV surveillance</li>\r\n    <li>Biometric access controls</li>\r\n    <li>GPS tracking for high-value consignments</li>\r\n    <li>Enhanced lighting and perimeter security</li>\r\n    </ul>\r\n    <p>These measures will help prevent theft, damage, and unauthorized access to freight facilities.</p>', 'New security protocols including CCTV surveillance, biometric controls, and GPS tracking implemented across freight terminals.', 'news', 'medium', 1, 1, 0, '2025-09-09 17:10:27', '2025-10-06 17:10:27', NULL, NULL, 1, '2025-09-11 17:10:27', '2025-09-11 17:10:27'),
(3, 'Digital Documentation System Now Live', '<p>All freight booking and delivery processes have been digitized to reduce paperwork and improve efficiency.</p>\r\n    <p><strong>Benefits:</strong></p>\r\n    <ul>\r\n    <li>Paperless transactions</li>\r\n    <li>Faster processing times</li>\r\n    <li>Real-time document verification</li>\r\n    <li>Environmental sustainability</li>\r\n    </ul>\r\n    <p>Customers can now upload all required documents through the SAMPARK portal, eliminating the need for physical document submission.</p>', 'Complete digitization of freight documentation processes for paperless, faster, and more efficient operations.', 'update', 'medium', 1, 1, 0, '2025-09-06 17:10:27', '2025-10-01 17:10:27', NULL, NULL, 1, '2025-09-11 17:10:27', '2025-09-11 17:10:27'),
(4, 'Scheduled Maintenance - September 15, 2025', '<p><strong>IMPORTANT NOTICE:</strong> Scheduled maintenance of SAMPARK portal and related systems.</p>\r\n    <p><strong>Maintenance Window:</strong><br>\r\n    Date: September 15, 2025<br>\r\n    Time: 02:00 AM to 06:00 AM IST</p>\r\n    <p><strong>Services Affected:</strong></p>\r\n    <ul>\r\n    <li>Online ticket submission</li>\r\n    <li>Status tracking</li>\r\n    <li>Document uploads</li>\r\n    <li>Customer portal access</li>\r\n    </ul>\r\n    <p>Emergency support will be available through telephone helpline during this period. We apologize for any inconvenience caused.</p>', 'Scheduled system maintenance on September 15, 2025 from 2:00 AM to 6:00 AM. Online services will be temporarily unavailable.', 'announcement', 'high', 1, 1, 1, '2025-09-11 17:10:27', '2025-09-18 17:10:27', NULL, NULL, 1, '2025-09-11 17:10:27', '2025-09-11 17:10:27'),
(5, 'New Freight Booking Guidelines Effective Immediately', '<p>Updated freight booking guidelines are now in effect to streamline operations and improve service quality.</p>\r\n    <p><strong>Key Changes:</strong></p>\r\n    <ul>\r\n    <li>Advance booking window extended to 30 days</li>\r\n    <li>Enhanced documentation requirements for hazardous materials</li>\r\n    <li>New weight and dimension verification procedures</li>\r\n    <li>Mandatory insurance for high-value consignments</li>\r\n    </ul>\r\n    <p>Please refer to the updated guidelines available in the documents section of the portal.</p>', 'Updated freight booking guidelines now effective with extended advance booking and new documentation requirements.', 'announcement', 'medium', 1, 1, 0, '2025-09-10 17:10:27', '2025-11-10 17:10:27', NULL, NULL, 1, '2025-09-11 17:10:27', '2025-09-11 17:10:27'),
(6, 'Customer Feedback Program Launch', '<p>We are launching a comprehensive customer feedback program to continuously improve our freight services.</p>\r\n    <p><strong>How to Participate:</strong></p>\r\n    <ul>\r\n    <li>Complete online surveys after service delivery</li>\r\n    <li>Provide ratings for terminal facilities</li>\r\n    <li>Submit improvement suggestions</li>\r\n    <li>Participate in quarterly feedback sessions</li>\r\n    </ul>\r\n    <p>Your feedback is valuable and will directly influence service improvements and infrastructure development.</p>', 'New customer feedback program launched to collect suggestions and continuously improve freight service quality.', 'announcement', 'low', 1, 1, 0, '2025-09-08 17:10:27', '2025-10-26 17:10:27', NULL, NULL, 1, '2025-09-11 17:10:27', '2025-09-11 17:10:27'),
(7, 'Emergency Contact Information Updated', '<p><strong>URGENT UPDATE:</strong> Emergency contact information for freight services has been updated.</p>\r\n    <p><strong>New Emergency Helpline:</strong><br>\r\n    📞 1800-111-321 (24/7 Available)<br>\r\n    📧 emergency@sampark.railway.gov.in</p>\r\n    <p><strong>For Immediate Assistance:</strong></p>\r\n    <ul>\r\n    <li>Cargo theft or damage</li>\r\n    <li>Safety incidents</li>\r\n    <li>Urgent delivery requirements</li>\r\n    <li>System technical issues</li>\r\n    </ul>\r\n    <p>Please update your records with the new contact information.</p>', 'Emergency contact information updated. New 24/7 helpline: 1800-111-321 for urgent freight service assistance.', 'alert', 'urgent', 1, 1, 1, '2025-09-11 17:10:27', '2025-12-10 17:10:27', NULL, NULL, 1, '2025-09-11 17:10:27', '2025-09-11 17:10:27');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `customer_id` varchar(20) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `type` enum('info','success','warning','error','escalation') DEFAULT 'info',
  `complaint_id` varchar(20) DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `action_url` varchar(500) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `read_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `quick_links`
--

CREATE TABLE `quick_links` (
  `id` int(11) NOT NULL,
  `title` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `url` varchar(500) NOT NULL,
  `icon` varchar(50) DEFAULT NULL,
  `target` enum('_self','_blank') DEFAULT '_blank',
  `is_active` tinyint(1) DEFAULT 1,
  `sort_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `quick_links`
--

INSERT INTO `quick_links` (`id`, `title`, `description`, `url`, `icon`, `target`, `is_active`, `sort_order`, `created_at`, `updated_at`) VALUES
(2, 'Indian Railways', 'Official Indian Railways website', 'https://indianrailways.gov.in', 'fas fa-train', '_blank', 1, 1, '2025-09-11 17:05:15', '2025-09-11 17:05:15'),
(3, 'Railway Board', 'Ministry of Railways - Railway Board', 'https://railwayboard.gov.in', 'fas fa-building', '_blank', 1, 2, '2025-09-11 17:05:15', '2025-09-11 17:05:15'),
(4, 'Freight Business Portal', 'Online freight booking and tracking', 'https://freight.indianrailways.gov.in', 'fas fa-shipping-fast', '_blank', 1, 3, '2025-09-11 17:05:15', '2025-09-11 17:05:15'),
(5, 'FOIS - Freight Operations', 'Freight Operations Information System', 'https://fois.indianrailways.gov.in', 'fas fa-chart-line', '_blank', 1, 4, '2025-09-11 17:05:15', '2025-09-11 17:05:15'),
(6, 'NTES - Train Enquiry', 'National Train Enquiry System', 'https://enquiry.indianrailways.gov.in', 'fas fa-search', '_blank', 1, 5, '2025-09-11 17:05:15', '2025-09-11 17:05:15'),
(7, 'Railway Safety Guidelines', 'Safety guidelines and protocols', 'https://safety.indianrailways.gov.in', 'fas fa-shield-alt', '_blank', 1, 6, '2025-09-11 17:05:15', '2025-09-11 17:05:15'),
(8, 'Complaints & Suggestions', 'Rail Madad - Customer Care Portal', 'https://railmadad.indianrailways.gov.in', 'fas fa-comment-dots', '_blank', 1, 7, '2025-09-11 17:05:15', '2025-09-11 17:05:15'),
(9, 'Tender Notices', 'Railway procurement and tenders', 'https://tender.indianrailways.gov.in', 'fas fa-file-contract', '_blank', 1, 8, '2025-09-11 17:05:15', '2025-09-11 17:05:15');

-- --------------------------------------------------------

--
-- Table structure for table `remember_tokens`
--

CREATE TABLE `remember_tokens` (
  `id` int(11) NOT NULL,
  `selector` varchar(32) NOT NULL,
  `token_hash` varchar(64) NOT NULL,
  `user_type` enum('customer','user') NOT NULL,
  `user_id` varchar(50) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `shed`
--

CREATE TABLE `shed` (
  `shed_id` int(11) NOT NULL,
  `shed_code` varchar(10) NOT NULL,
  `division` varchar(100) NOT NULL,
  `zone` varchar(100) NOT NULL,
  `name` varchar(200) NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `shed`
--

INSERT INTO `shed` (`shed_id`, `shed_code`, `division`, `zone`, `name`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'AAK', 'BSL', 'CR', 'Ankai kila', 1, '2025-09-09 11:01:58', '2025-09-09 11:01:58'),
(2, 'ABLE', 'PUNE', 'CR', 'Ambale', 1, '2025-09-09 11:01:58', '2025-09-09 11:01:58'),
(3, 'ABSG', 'CSMT', 'CR', 'ORDNANCE FACTORY SDG ABH', 1, '2025-09-09 11:01:58', '2025-09-09 11:01:58'),
(4, 'ACG', 'BSL', 'CR', 'Achegaon', 1, '2025-09-09 11:01:58', '2025-09-09 11:01:58'),
(5, 'AGQ', 'BSL', 'CR', 'Asirgarh Road', 1, '2025-09-09 11:01:58', '2025-09-09 11:01:58'),
(6, 'AGSA', 'NGP', 'CR', 'AJNI Goods shed served by AJNI', 1, '2025-09-09 11:01:58', '2025-09-09 11:01:58'),
(7, 'AJNI', 'NGP', 'CR', 'Ajni', 1, '2025-09-09 11:01:58', '2025-09-09 11:01:58'),
(8, 'AK', 'BSL', 'CR', 'Akola Jn.', 1, '2025-09-09 11:01:58', '2025-09-09 11:01:58'),
(9, 'AKI', 'PUNE', 'CR', 'Adarki', 1, '2025-09-09 11:01:58', '2025-09-09 11:01:58'),
(10, 'AKRD', 'PUNE', 'CR', 'Akurdi', 1, '2025-09-09 11:01:58', '2025-09-09 11:01:58'),
(11, 'ALN', 'PUNE', 'CR', 'Alandi', 1, '2025-09-09 11:01:58', '2025-09-09 11:01:58'),
(12, 'AMI', 'BSL', 'CR', 'Amravati', 1, '2025-09-09 11:01:58', '2025-09-09 11:01:58'),
(13, 'AMLA', 'NGP', 'CR', 'Amla Jn.', 1, '2025-09-09 11:01:58', '2025-09-09 11:01:58'),
(14, 'AMLX', 'NGP', 'CR', 'AMLA JN. YARD (TXR)', 1, '2025-09-09 11:01:58', '2025-09-09 11:01:58'),
(15, 'AMNE', 'PUNE', 'CR', 'AMALNER B', 1, '2025-09-09 11:01:58', '2025-09-09 11:01:58'),
(16, 'AMSG', 'NGP', 'CR', 'AIR FORCE SIDING, AMLA', 1, '2025-09-09 11:01:58', '2025-09-09 11:01:58'),
(17, 'ANG', 'PUNE', 'CR', 'Ahmadnagar.', 1, '2025-09-09 11:01:58', '2025-09-09 11:01:58'),
(18, 'ANK', 'BSL', 'CR', 'Ankai', 1, '2025-09-09 11:01:59', '2025-09-09 11:01:59'),
(19, 'ANKX', 'BSL', 'CR', 'ANKAI MANMAD DIRECT', 1, '2025-09-09 11:01:59', '2025-09-09 11:01:59'),
(20, 'AQX', 'NGP', 'CR', 'AJNI YARD (TXR)', 1, '2025-09-09 11:01:59', '2025-09-09 11:01:59'),
(21, 'ARAG', 'SUR', 'CR', 'Arag', 1, '2025-09-09 11:01:59', '2025-09-09 11:01:59'),
(22, 'ARVI', 'NGP', 'CR', 'Arvi', 1, '2025-09-09 11:01:59', '2025-09-09 11:01:59'),
(23, 'ASTG', 'NGP', 'CR', 'ASHTEGAON', 1, '2025-09-09 11:01:59', '2025-09-09 11:01:59'),
(24, 'AV', 'BSL', 'CR', 'Asvali', 1, '2025-09-09 11:01:59', '2025-09-09 11:01:59'),
(25, 'BALE', 'SUR', 'CR', 'Bale', 1, '2025-09-09 11:01:59', '2025-09-09 11:01:59'),
(26, 'BAP', 'PUNE', 'CR', 'Belapur', 1, '2025-09-09 11:01:59', '2025-09-09 11:01:59'),
(27, 'BAU', 'BSL', 'CR', 'Burhanpur', 1, '2025-09-09 11:01:59', '2025-09-09 11:01:59'),
(28, 'BBTR', 'NGP', 'CR', 'Barbatpur', 1, '2025-09-09 11:01:59', '2025-09-09 11:01:59'),
(29, 'BBV', 'SUR', 'CR', 'Babhulgaon', 1, '2025-09-09 11:01:59', '2025-09-09 11:01:59'),
(30, 'BCCK', 'CSMT', 'CR', 'BULK CEMENT CORP SIDING KLMI', 1, '2025-09-09 11:01:59', '2025-09-09 11:01:59'),
(31, 'BCRD', 'NGP', 'CR', 'Barchhi Road', 1, '2025-09-09 11:01:59', '2025-09-09 11:01:59'),
(32, 'BD', 'BSL', 'CR', 'Badnera Jn.', 1, '2025-09-09 11:01:59', '2025-09-09 11:01:59'),
(33, 'BDGN', 'BSL', 'CR', 'Bhandegaon', 1, '2025-09-09 11:01:59', '2025-09-09 11:01:59'),
(34, 'BDI', 'BSL', 'CR', 'Bhadli', 1, '2025-09-09 11:01:59', '2025-09-09 11:01:59'),
(35, 'BDK', 'SUR', 'CR', 'Bedag', 1, '2025-09-09 11:01:59', '2025-09-09 11:01:59'),
(36, 'BDWD', 'BSL', 'CR', 'Bodwad', 1, '2025-09-09 11:01:59', '2025-09-09 11:01:59'),
(37, 'BDYX', 'BSL', 'CR', 'BADNERA JN. YARD (TXR)', 1, '2025-09-09 11:01:59', '2025-09-09 11:01:59'),
(38, 'BESG', 'BSL', 'CR', 'Maharashtra State Electricity Board Siding Paras', 1, '2025-09-09 11:01:59', '2025-09-09 11:01:59'),
(39, 'BFJ', 'BSL', 'CR', 'Bhoras Budrukh', 1, '2025-09-09 11:01:59', '2025-09-09 11:01:59'),
(40, 'BFSG', 'PUNE', 'CR', 'BHARAT FORGE COMP SDG.', 1, '2025-09-09 11:01:59', '2025-09-09 11:01:59'),
(41, 'BGN', 'BSL', 'CR', 'Borgaon', 1, '2025-09-09 11:01:59', '2025-09-09 11:01:59'),
(42, 'BGVN', 'SUR', 'CR', 'Bhigwan', 1, '2025-09-09 11:01:59', '2025-09-09 11:01:59'),
(43, 'BGWI', 'PUNE', 'CR', 'BEGDEWADI', 1, '2025-09-09 11:01:59', '2025-09-09 11:01:59'),
(44, 'BHLI', 'SUR', 'CR', 'Bohali', 1, '2025-09-09 11:01:59', '2025-09-09 11:01:59'),
(45, 'BIS', 'BSL', 'CR', 'Biswa Bridge', 1, '2025-09-09 11:01:59', '2025-09-09 11:01:59'),
(46, 'BMA', 'BSL', 'CR', 'Bagmar', 1, '2025-09-09 11:01:59', '2025-09-09 11:01:59'),
(47, 'BNOD', 'NGP', 'CR', 'BENODA', 1, '2025-09-09 11:01:59', '2025-09-09 11:01:59'),
(48, 'BOK', 'NGP', 'CR', 'Borkhedi', 1, '2025-09-09 11:02:00', '2025-09-09 11:02:00'),
(49, 'BPAL', 'PUNE', 'CR', 'M/s BPCL PRIVATE SIDING', 1, '2025-09-09 11:02:00', '2025-09-09 11:02:00'),
(50, 'BPCP', 'BSL', 'CR', 'POL SDG. FOR M/S BPCL PANEVADI', 1, '2025-09-09 11:02:00', '2025-09-09 11:02:00'),
(51, 'BPGH', 'SUR', 'CR', 'm/s bpcl hirenanduru', 1, '2025-09-09 11:02:00', '2025-09-09 11:02:00'),
(52, 'BPK', 'NGP', 'CR', 'Bhugaon', 1, '2025-09-09 11:02:00', '2025-09-09 11:02:00'),
(53, 'BPQ', 'NGP', 'CR', 'Balharshah', 1, '2025-09-09 11:02:00', '2025-09-09 11:02:00'),
(54, 'BPQX', 'NGP', 'CR', 'BALHARSHAH YARD (TXR)', 1, '2025-09-09 11:02:00', '2025-09-09 11:02:00'),
(55, 'BPTG', 'CSMT', 'CR', 'Grain Depot', 1, '2025-09-09 11:02:00', '2025-09-09 11:02:00'),
(56, 'BPTV', 'CSMT', 'CR', 'VICTORIA DOCK BPT RLY', 1, '2025-09-09 11:02:00', '2025-09-09 11:02:00'),
(57, 'BQM', 'NGP', 'CR', 'Barelipar', 1, '2025-09-09 11:02:00', '2025-09-09 11:02:00'),
(58, 'BRDH', 'NGP', 'CR', 'Bardhana', 1, '2025-09-09 11:02:00', '2025-09-09 11:02:00'),
(59, 'BRMT', 'PUNE', 'CR', 'Baramati', 1, '2025-09-09 11:02:00', '2025-09-09 11:02:00'),
(60, 'BROL', 'NGP', 'CR', 'POL SDG. FOR M/S BPCL', 1, '2025-09-09 11:02:00', '2025-09-09 11:02:00'),
(61, 'BRSG', 'CSMT', 'CR', 'Bharat Petroleum Siding', 1, '2025-09-09 11:02:00', '2025-09-09 11:02:00'),
(62, 'BRVR', 'BSL', 'CR', 'Borvihir', 1, '2025-09-09 11:02:00', '2025-09-09 11:02:00'),
(63, 'BSBN', 'BSL', 'CR', 'BHUSAWAL B CABIN', 1, '2025-09-09 11:02:00', '2025-09-09 11:02:00'),
(64, 'BSCN', 'BSL', 'CR', 'BHUSAWAL C CABIN', 1, '2025-09-09 11:02:00', '2025-09-09 11:02:00'),
(65, 'BSGS', 'BSL', 'CR', 'BHUSAVAL GOODS SHED', 1, '2025-09-09 11:02:00', '2025-09-09 11:02:00'),
(66, 'BSL', 'BSL', 'CR', 'Bhusaval Jn.', 1, '2025-09-09 11:02:00', '2025-09-09 11:02:00'),
(67, 'BSLX', 'BSL', 'CR', 'BHUSAVAL JN. YARD (TXR)', 1, '2025-09-09 11:02:00', '2025-09-09 11:02:00'),
(68, 'BSSG', 'BSL', 'CR', 'Reserve Petrol Depot siding', 1, '2025-09-09 11:02:00', '2025-09-09 11:02:00'),
(69, 'BTBR', 'NGP', 'CR', 'Buti Bori', 1, '2025-09-09 11:02:00', '2025-09-09 11:02:00'),
(70, 'BTW', 'SUR', 'CR', 'Barsi Town', 1, '2025-09-09 11:02:00', '2025-09-09 11:02:00'),
(71, 'BUPH', 'NGP', 'CR', 'Babupeth', 1, '2025-09-09 11:02:00', '2025-09-09 11:02:00'),
(72, 'BUX', 'NGP', 'CR', 'Bhandak', 1, '2025-09-09 11:02:01', '2025-09-09 11:02:01'),
(73, 'BVNR', 'PUNE', 'CR', 'Bhavani Nagar', 1, '2025-09-09 11:02:01', '2025-09-09 11:02:01'),
(74, 'BVQ', 'PUNE', 'CR', 'Bhilavadi', 1, '2025-09-09 11:02:01', '2025-09-09 11:02:01'),
(75, 'BWRA', 'NGP', 'CR', 'Bharatwada', 1, '2025-09-09 11:02:01', '2025-09-09 11:02:01'),
(76, 'BXY', 'NGP', 'CR', 'Bordhai', 1, '2025-09-09 11:02:01', '2025-09-09 11:02:01'),
(77, 'BYS', 'NGP', 'CR', 'Barsali', 1, '2025-09-09 11:02:01', '2025-09-09 11:02:01'),
(78, 'BZU', 'NGP', 'CR', 'Betul', 1, '2025-09-09 11:02:01', '2025-09-09 11:02:01'),
(79, 'CCCT', 'SUR', 'CR', 'M/s Chettinad cement corporation pvt. Ltd.', 1, '2025-09-09 11:02:01', '2025-09-09 11:02:01'),
(80, 'CCD', 'NGP', 'CR', 'Chichonda', 1, '2025-09-09 11:02:01', '2025-09-09 11:02:01'),
(81, 'CCH', 'PUNE', 'CR', 'Chinchvad', 1, '2025-09-09 11:02:01', '2025-09-09 11:02:01'),
(82, 'CCIK', 'CSMT', 'CR', 'COTTON CORP. OF INDIA LTD.', 1, '2025-09-09 11:02:01', '2025-09-09 11:02:01'),
(83, 'CCSD', 'NGP', 'CR', 'CHARGAON COLLIERY SIDING, MAJRI JN.', 1, '2025-09-09 11:02:01', '2025-09-09 11:02:01'),
(84, 'CD', 'NGP', 'CR', 'Chandrapur', 1, '2025-09-09 11:02:01', '2025-09-09 11:02:01'),
(85, 'CDI', 'BSL', 'CR', 'Chandni (Chandni chauk CBA)', 1, '2025-09-09 11:02:01', '2025-09-09 11:02:01'),
(86, 'CESG', 'NGP', 'CR', 'Associated Cement Co.s. Siding-Ghugus.', 1, '2025-09-09 11:02:01', '2025-09-09 11:02:01'),
(87, 'CGM', 'NGP', 'CR', 'CHARGAON COLLIERY SDG.', 1, '2025-09-09 11:02:01', '2025-09-09 11:02:01'),
(88, 'CKNI', 'NGP', 'CR', 'Chikni Road', 1, '2025-09-09 11:02:01', '2025-09-09 11:02:01'),
(89, 'CMSG', 'PUNE', 'CR', 'CENTRAL ORDINANCE DEPOT, DEHU ROAD', 1, '2025-09-09 11:02:01', '2025-09-09 11:02:01'),
(90, 'CND', 'NGP', 'CR', 'Chandur', 1, '2025-09-09 11:02:01', '2025-09-09 11:02:01'),
(91, 'CNHL', 'SUR', 'CR', 'Chink Hill', 1, '2025-09-09 11:02:01', '2025-09-09 11:02:01'),
(92, 'CPSG', 'PUNE', 'CR', 'ORDINANCE DEPOT SDG, TALEGAON DABHADE', 1, '2025-09-09 11:02:01', '2025-09-09 11:02:01'),
(93, 'CPW', 'NGP', 'CR', 'Choti Padoli', 1, '2025-09-09 11:02:01', '2025-09-09 11:02:01'),
(94, 'CPWS', 'CSMT', 'CR', 'CROMPTON GREAVES LTD. SDG', 1, '2025-09-09 11:02:01', '2025-09-09 11:02:01'),
(95, 'CRCC', 'PUNE', 'CR', 'CHINCHWAD CONTAINER DEPOT', 1, '2025-09-09 11:02:01', '2025-09-09 11:02:01'),
(96, 'CRMM', 'PUNE', 'CR', 'MIRAJ CONTAINER DEPOT', 1, '2025-09-09 11:02:01', '2025-09-09 11:02:01'),
(97, 'CRNM', 'CSMT', 'CR', 'CONTAINER DEPOT NEW MULUND', 1, '2025-09-09 11:02:01', '2025-09-09 11:02:01'),
(98, 'CRTK', 'CSMT', 'CR', 'Turbhe container Siding', 1, '2025-09-09 11:02:01', '2025-09-09 11:02:01'),
(99, 'CSID', 'NGP', 'CR', 'MOHAN SIDING PALACHORI', 1, '2025-09-09 11:02:01', '2025-09-09 11:02:01'),
(100, 'CSN', 'BSL', 'CR', 'Chalisgaon Jn', 1, '2025-09-09 11:02:01', '2025-09-09 11:02:01'),
(101, 'CWHC', 'PUNE', 'CR', 'CENTRAL WAREHOUSE SDG.', 1, '2025-09-09 11:02:01', '2025-09-09 11:02:01'),
(102, 'CWHS', 'BSL', 'CR', 'Central Warehousing Corporation Siding Khandwa', 1, '2025-09-09 11:02:01', '2025-09-09 11:02:01'),
(103, 'CWJC', 'CSMT', 'CR', 'Central Warehousing Corp. siding', 1, '2025-09-09 11:02:01', '2025-09-09 11:02:01'),
(104, 'DAE', 'NGP', 'CR', 'Dahegaon', 1, '2025-09-09 11:02:01', '2025-09-09 11:02:01'),
(105, 'DAH', 'PUNE', 'CR', 'Dehare', 1, '2025-09-09 11:02:01', '2025-09-09 11:02:01'),
(106, 'DAPD', 'PUNE', 'CR', 'Dapodi', 1, '2025-09-09 11:02:01', '2025-09-09 11:02:01'),
(107, 'DASG', 'PUNE', 'CR', 'DEHU AMMUNITION DEPOT, SHELARWADI', 1, '2025-09-09 11:02:01', '2025-09-09 11:02:01'),
(108, 'DBCL', 'CSMT', 'CR', 'DOMBIVLI CTW', 1, '2025-09-09 11:02:02', '2025-09-09 11:02:02'),
(109, 'DCSG', 'NGP', 'CR', 'EAST DONGAR CHIKLI COLLIERY SDG.', 1, '2025-09-09 11:02:02', '2025-09-09 11:02:02'),
(110, 'DD', 'PUNE', 'CR', 'Daund Jn.', 1, '2025-09-09 11:02:02', '2025-09-09 11:02:02'),
(111, 'DDAC', 'PUNE', 'CR', 'DAUND A CABIN', 1, '2025-09-09 11:02:02', '2025-09-09 11:02:02'),
(112, 'DDMT', 'NGP', 'CR', 'Darimeta', 1, '2025-09-09 11:02:02', '2025-09-09 11:02:02'),
(113, 'DDYX', 'PUNE', 'CR', 'DAUND JN. YARD (TXR)', 1, '2025-09-09 11:02:02', '2025-09-09 11:02:02'),
(114, 'DEHR', 'PUNE', 'CR', 'Dehu Road', 1, '2025-09-09 11:02:02', '2025-09-09 11:02:02'),
(115, 'DELI', 'NGP', 'CR', 'DEOLI', 1, '2025-09-09 11:02:02', '2025-09-09 11:02:02'),
(116, 'DGN', 'BSL', 'CR', 'Dongargaon', 1, '2025-09-09 11:02:02', '2025-09-09 11:02:02'),
(117, 'DHI', 'BSL', 'CR', 'Dhule', 1, '2025-09-09 11:02:02', '2025-09-09 11:02:02'),
(118, 'DHQ', 'NGP', 'CR', 'Dharakhoh', 1, '2025-09-09 11:02:02', '2025-09-09 11:02:02'),
(119, 'DIP', 'NGP', 'CR', 'Dipore', 1, '2025-09-09 11:02:02', '2025-09-09 11:02:02'),
(120, 'DLIB', 'NGP', 'CR', 'M/S Distribution Logistics Infrastructure pvt.ltd.', 1, '2025-09-09 11:02:02', '2025-09-09 11:02:02'),
(121, 'DLLM', 'CSMT', 'CR', 'Diesel Loco Shed LTT Mumbai', 1, '2025-09-09 11:02:02', '2025-09-09 11:02:02'),
(122, 'DMGM', 'NGP', 'CR', 'Dinesh OCM Makardhokara-III Gati Shakti Multi-Modal Cargo Terminal', 1, '2025-09-09 11:02:02', '2025-09-09 11:02:02'),
(123, 'DMN', 'NGP', 'CR', 'Dhamangaon', 1, '2025-09-09 11:02:02', '2025-09-09 11:02:02'),
(124, 'DMSG', 'BSL', 'CR', 'DEVLALI MILITARY SIDING, DEVLALI', 1, '2025-09-09 11:02:02', '2025-09-09 11:02:02'),
(125, 'DNJ', 'PUNE', 'CR', 'Daundaj', 1, '2025-09-09 11:02:02', '2025-09-09 11:02:02'),
(126, 'DNZ', 'NGP', 'CR', 'Dhanori', 1, '2025-09-09 11:02:02', '2025-09-09 11:02:02'),
(127, 'DOH', 'NGP', 'CR', 'Dhodra Mohor', 1, '2025-09-09 11:02:02', '2025-09-09 11:02:02'),
(128, 'DRSV', 'SUR', 'CR', 'DHARASHIV', 1, '2025-09-09 11:02:02', '2025-09-09 11:02:02'),
(129, 'DRTA', 'CSMT', 'CR', 'DRONAGIRI Rail Terminal', 1, '2025-09-09 11:02:02', '2025-09-09 11:02:02'),
(130, 'DSK', 'BSL', 'CR', 'Duskheda', 1, '2025-09-09 11:02:02', '2025-09-09 11:02:02'),
(131, 'DTCC', 'CSMT', 'CR', 'DATIVLI CHORD CABIN', 1, '2025-09-09 11:02:02', '2025-09-09 11:02:02'),
(132, 'DTVL', 'CSMT', 'CR', 'DATIVALI CABIN', 1, '2025-09-09 11:02:02', '2025-09-09 11:02:02'),
(133, 'DVL', 'BSL', 'CR', 'Devlali', 1, '2025-09-09 11:02:03', '2025-09-09 11:02:03'),
(134, 'DWJN', 'CSMT', 'CR', 'DIVA JN. CABIN', 1, '2025-09-09 11:02:03', '2025-09-09 11:02:03'),
(135, 'ELDD', 'SUR', 'CR', 'electric loco shed - daund', 1, '2025-09-09 11:02:03', '2025-09-09 11:02:03'),
(136, 'EOLD', 'NGP', 'CR', 'M/s Nayara Energy Ltd', 1, '2025-09-09 11:02:03', '2025-09-09 11:02:03'),
(137, 'ESSG', 'PUNE', 'CR', 'ENGINERING STORE TRANSIT DEPOT, SHELARWADI', 1, '2025-09-09 11:02:03', '2025-09-09 11:02:03'),
(138, 'FBSG', 'NGP', 'CR', 'FOOD CORP. OF INDIA SDG', 1, '2025-09-09 11:02:03', '2025-09-09 11:02:03'),
(139, 'FFSG', 'NGP', 'CR', 'FILLING FACTORY SIDING, BHANDAK', 1, '2025-09-09 11:02:03', '2025-09-09 11:02:03'),
(140, 'FNSG', 'NGP', 'CR', 'FOOD CORPORATION OF INDIA SDG, NAGPUR (AJNI)', 1, '2025-09-09 11:02:03', '2025-09-09 11:02:03'),
(141, 'FSG', 'PUNE', 'CR', 'Phursungi', 1, '2025-09-09 11:02:03', '2025-09-09 11:02:03'),
(142, 'FWSM', 'NGP', 'CR', 'GCT of M/s Fuelco Washeries (India ) Limited', 1, '2025-09-09 11:02:03', '2025-09-09 11:02:03'),
(143, 'FZSG', 'CSMT', 'CR', 'Rashtriya Chemicals, and Fertilizers Siding-Trombay', 1, '2025-09-09 11:02:03', '2025-09-09 11:02:03'),
(144, 'GAA', 'BSL', 'CR', 'Galan', 1, '2025-09-09 11:02:03', '2025-09-09 11:02:03'),
(145, 'GAO', 'BSL', 'CR', 'Gaigaon', 1, '2025-09-09 11:02:03', '2025-09-09 11:02:03'),
(146, 'GCC', 'NGP', 'CR', 'GODHANI CHORD CABIN', 1, '2025-09-09 11:02:03', '2025-09-09 11:02:03'),
(147, 'GDKP', 'NGP', 'CR', 'GHUDANKHAPA', 1, '2025-09-09 11:02:03', '2025-09-09 11:02:03'),
(148, 'GDSG', 'BSL', 'CR', 'Food Corporation Siding Manmad', 1, '2025-09-09 11:02:03', '2025-09-09 11:02:03'),
(149, 'GDYA', 'NGP', 'CR', 'Ghoradongri', 1, '2025-09-09 11:02:03', '2025-09-09 11:02:03'),
(150, 'GGS', 'NGP', 'CR', 'Ghugus', 1, '2025-09-09 11:02:03', '2025-09-09 11:02:03'),
(151, 'GHSG', 'PUNE', 'CR', 'Food Corporation Siding Pune', 1, '2025-09-09 11:02:03', '2025-09-09 11:02:03'),
(152, 'GLV', 'SUR', 'CR', 'Gulvanchi', 1, '2025-09-09 11:02:03', '2025-09-09 11:02:03'),
(153, 'GMG', 'NGP', 'CR', 'Gumgaon', 1, '2025-09-09 11:02:03', '2025-09-09 11:02:03'),
(154, 'GNQ', 'NGP', 'CR', 'Godhani', 1, '2025-09-09 11:02:03', '2025-09-09 11:02:03'),
(155, 'GNQC', 'NGP', 'CR', 'GODHANI CHORD CABIN', 1, '2025-09-09 11:02:03', '2025-09-09 11:02:03'),
(156, 'GNVR', 'NGP', 'CR', 'GONDWANA VISAPUR', 1, '2025-09-09 11:02:03', '2025-09-09 11:02:03'),
(157, 'GNW', 'NGP', 'CR', 'Gangiwara', 1, '2025-09-09 11:02:03', '2025-09-09 11:02:03'),
(158, 'GO', 'BSL', 'CR', 'Ghoti', 1, '2025-09-09 11:02:03', '2025-09-09 11:02:03'),
(159, 'GPR', 'PUNE', 'CR', 'Ghorpuri', 1, '2025-09-09 11:02:03', '2025-09-09 11:02:03'),
(160, 'GRMT', 'PUNE', 'CR', 'Gur Market KOLHAPUR', 1, '2025-09-09 11:02:03', '2025-09-09 11:02:03'),
(161, 'GRWD', 'PUNE', 'CR', 'Ghorawadi', 1, '2025-09-09 11:02:03', '2025-09-09 11:02:03'),
(162, 'GSG', 'NGP', 'CR', 'GHUGUS COILLIERY SDG.', 1, '2025-09-09 11:02:03', '2025-09-09 11:02:03'),
(163, 'HDP', 'PUNE', 'CR', 'Hadapsar', 1, '2025-09-09 11:02:04', '2025-09-09 11:02:04'),
(164, 'HDYD', 'CSMT', 'CR', 'HOLDING YARD', 1, '2025-09-09 11:02:04', '2025-09-09 11:02:04'),
(165, 'HGT', 'NGP', 'CR', 'Hinganghat', 1, '2025-09-09 11:02:04', '2025-09-09 11:02:04'),
(166, 'HLSG', 'NGP', 'CR', 'HINDUSTAN LALPETH COLLIERY SDG.', 1, '2025-09-09 11:02:04', '2025-09-09 11:02:04'),
(167, 'HNWG', 'NGP', 'CR', 'Hirdagarh-Nandan Washery Siding', 1, '2025-09-09 11:02:04', '2025-09-09 11:02:04'),
(168, 'HPLC', 'PUNE', 'CR', 'Hindustan Petroleum Corporation\'s Oil Bhilavdi', 1, '2025-09-09 11:02:04', '2025-09-09 11:02:04'),
(169, 'HPLG', 'PUNE', 'CR', 'Hindustan Petroleum Corporation\'s Oil Terminal sdg Loni', 1, '2025-09-09 11:02:04', '2025-09-09 11:02:04'),
(170, 'HPLX', 'PUNE', 'CR', 'LONI YARD (TXR)', 1, '2025-09-09 11:02:04', '2025-09-09 11:02:04'),
(171, 'HPR', 'BSL', 'CR', 'Hirapur', 1, '2025-09-09 11:02:04', '2025-09-09 11:02:04'),
(172, 'HPSG', 'SUR', 'CR', 'POL SDG FOR HIRENANDURE', 1, '2025-09-09 11:02:04', '2025-09-09 11:02:04'),
(173, 'HRG', 'NGP', 'CR', 'Hirdagarh', 1, '2025-09-09 11:02:04', '2025-09-09 11:02:04'),
(174, 'HSL', 'BSL', 'CR', 'Hisvahal', 1, '2025-09-09 11:02:04', '2025-09-09 11:02:04'),
(175, 'HTK', 'PUNE', 'CR', 'Hatkanagale', 1, '2025-09-09 11:02:04', '2025-09-09 11:02:04'),
(176, 'HTLA', 'PUNE', 'CR', 'HATOLA', 1, '2025-09-09 11:02:04', '2025-09-09 11:02:04'),
(177, 'HTN', 'NGP', 'CR', 'Hatnapur', 1, '2025-09-09 11:02:04', '2025-09-09 11:02:04'),
(178, 'ICBD', 'BSL', 'CR', 'BHUSAVAL ICD CONTAINER DEPOT', 1, '2025-09-09 11:02:04', '2025-09-09 11:02:04'),
(179, 'IGPX', 'CSMT', 'CR', 'IGAT PURI YARD (TXR)', 1, '2025-09-09 11:02:04', '2025-09-09 11:02:04'),
(180, 'IKR', 'NGP', 'CR', 'Iklehra', 1, '2025-09-09 11:02:04', '2025-09-09 11:02:04'),
(181, 'IOBT', 'NGP', 'CR', 'POL SDG. FOR M/S IOC/BPCL TADALI', 1, '2025-09-09 11:02:04', '2025-09-09 11:02:04'),
(182, 'IOC', 'BSL', 'CR', 'POL siding for IOC ltd shirud', 1, '2025-09-09 11:02:04', '2025-09-09 11:02:04'),
(183, 'IOSG', 'CSMT', 'CR', 'Indian Oil Blending Siding', 1, '2025-09-09 11:02:04', '2025-09-09 11:02:04'),
(184, 'JBC', 'CSMT', 'CR', 'JAMBRUNG', 1, '2025-09-09 11:02:04', '2025-09-09 11:02:04'),
(185, 'JCSK', 'NGP', 'CR', 'M/S JSW Steel Coated Products Limited', 1, '2025-09-09 11:02:04', '2025-09-09 11:02:04'),
(186, 'JJR', 'PUNE', 'CR', 'Jejuri', 1, '2025-09-09 11:02:04', '2025-09-09 11:02:04'),
(187, 'JKR', 'NGP', 'CR', 'Jaulkhera', 1, '2025-09-09 11:02:04', '2025-09-09 11:02:04'),
(188, 'JL', 'BSL', 'CR', 'Jalgaon jn', 1, '2025-09-09 11:02:04', '2025-09-09 11:02:04'),
(189, 'JM', 'BSL', 'CR', 'Jalamb Jn.', 1, '2025-09-09 11:02:04', '2025-09-09 11:02:04'),
(190, 'JMD', 'BSL', 'CR', 'Jamdha', 1, '2025-09-09 11:02:04', '2025-09-09 11:02:04'),
(191, 'JMV', 'NGP', 'CR', 'Jambhara', 1, '2025-09-09 11:02:04', '2025-09-09 11:02:04'),
(192, 'JNO', 'NGP', 'CR', 'Junnor Deo', 1, '2025-09-09 11:02:05', '2025-09-09 11:02:05'),
(193, 'JNPT', 'CSMT', 'CR', 'JAWAHARLAL NEHRU PORT TRUST', 1, '2025-09-09 11:02:05', '2025-09-09 11:02:05'),
(194, 'JSLE', 'CSMT', 'CR', 'JASAI CHIRLE', 1, '2025-09-09 11:02:05', '2025-09-09 11:02:05'),
(195, 'JSP', 'PUNE', 'CR', 'Jayasingpur', 1, '2025-09-09 11:02:05', '2025-09-09 11:02:05'),
(196, 'JSSR', 'CSMT', 'CR', 'M/s JSW Steel Ltd.', 1, '2025-09-09 11:02:05', '2025-09-09 11:02:05'),
(197, 'JSV', 'PUNE', 'CR', 'Jarandeshwar', 1, '2025-09-09 11:02:05', '2025-09-09 11:02:05'),
(198, 'JSWD', 'CSMT', 'CR', 'JSW steel ltd. siding', 1, '2025-09-09 11:02:05', '2025-09-09 11:02:05'),
(199, 'JSWV', 'CSMT', 'CR', 'M/s JSW steel coated products Ltd.', 1, '2025-09-09 11:02:05', '2025-09-09 11:02:05'),
(200, 'KAOT', 'NGP', 'CR', 'Kaotha', 1, '2025-09-09 11:02:05', '2025-09-09 11:02:05'),
(201, 'KASG', 'PUNE', 'CR', 'AMMUNITION FACTORY SDG, KHADKI', 1, '2025-09-09 11:02:05', '2025-09-09 11:02:05'),
(202, 'KATL', 'NGP', 'CR', 'Katol', 1, '2025-09-09 11:02:05', '2025-09-09 11:02:05'),
(203, 'KAYR', 'NGP', 'CR', 'KAYAR', 1, '2025-09-09 11:02:05', '2025-09-09 11:02:05'),
(204, 'KBGN', 'NGP', 'CR', 'Khubgaon', 1, '2025-09-09 11:02:05', '2025-09-09 11:02:05'),
(205, 'KBSN', 'BSL', 'CR', 'Kasbe Sukene', 1, '2025-09-09 11:02:05', '2025-09-09 11:02:05'),
(206, 'KCB', 'SUR', 'CR', 'Kuslamb', 1, '2025-09-09 11:02:05', '2025-09-09 11:02:05'),
(207, 'KDG', 'PUNE', 'CR', 'Kedgaon', 1, '2025-09-09 11:02:05', '2025-09-09 11:02:05'),
(208, 'KDK', 'BSL', 'CR', 'Kohdad', 1, '2025-09-09 11:02:05', '2025-09-09 11:02:05'),
(209, 'KECM', 'NGP', 'CR', 'M/s Karnataka Power Corporation Ltd', 1, '2025-09-09 11:02:05', '2025-09-09 11:02:05'),
(210, 'KFCG', 'CSMT', 'CR', 'Food Corporation of India Siding.', 1, '2025-09-09 11:02:05', '2025-09-09 11:02:05'),
(211, 'KFSG', 'PUNE', 'CR', 'HIGH EXPLOSIVES FACTORY SDG, KHADKI', 1, '2025-09-09 11:02:05', '2025-09-09 11:02:05'),
(212, 'KJ', 'BSL', 'CR', 'Kajgaon', 1, '2025-09-09 11:02:05', '2025-09-09 11:02:05'),
(213, 'KJL', 'BSL', 'CR', 'Khumgaon Burti', 1, '2025-09-09 11:02:05', '2025-09-09 11:02:05'),
(214, 'KK', 'PUNE', 'CR', 'Khadki', 1, '2025-09-09 11:02:05', '2025-09-09 11:02:05'),
(215, 'KLAT', 'BSL', 'CR', 'kolvihir', 1, '2025-09-09 11:02:05', '2025-09-09 11:02:05'),
(216, 'KLBA', 'NGP', 'CR', 'Kalambha', 1, '2025-09-09 11:02:05', '2025-09-09 11:02:05'),
(217, 'KLBG', 'SUR', 'CR', 'KALABURAGI', 1, '2025-09-09 11:02:05', '2025-09-09 11:02:05'),
(218, 'KLHD', 'BSL', 'CR', 'Kolhadi', 1, '2025-09-09 11:02:05', '2025-09-09 11:02:05'),
(219, 'KLMG', 'CSMT', 'CR', 'Kalamboli Goods Shed', 1, '2025-09-09 11:02:06', '2025-09-09 11:02:06'),
(220, 'KLMI', 'CSMT', 'CR', 'KALAMBOLI EXCHANGE YARD', 1, '2025-09-09 11:02:06', '2025-09-09 11:02:06'),
(221, 'KMKD', 'BSL', 'CR', 'Khamkhed', 1, '2025-09-09 11:02:06', '2025-09-09 11:02:06'),
(222, 'KMN', 'BSL', 'CR', 'Khamgaon', 1, '2025-09-09 11:02:06', '2025-09-09 11:02:06'),
(223, 'KMST', 'PUNE', 'CR', 'Kamshet', 1, '2025-09-09 11:02:06', '2025-09-09 11:02:06'),
(224, 'KNW', 'BSL', 'CR', 'Khandwa', 1, '2025-09-09 11:02:06', '2025-09-09 11:02:06'),
(225, 'KNWX', 'BSL', 'CR', 'KHANDWA JN. (CR) YARD (TXR)', 1, '2025-09-09 11:02:06', '2025-09-09 11:02:06'),
(226, 'KOHL', 'NGP', 'CR', 'Kohli', 1, '2025-09-09 11:02:06', '2025-09-09 11:02:06'),
(227, 'KOP', 'PUNE', 'CR', 'Chatrapathi Sahumaharaj Terminus KOLHAPUR', 1, '2025-09-09 11:02:06', '2025-09-09 11:02:06'),
(228, 'KOV', 'PUNE', 'CR', 'Kirloskarvadi', 1, '2025-09-09 11:02:06', '2025-09-09 11:02:06'),
(229, 'KOVS', 'PUNE', 'CR', 'Kirloskar Bros. Ltd Siding - Kirloskarvadi', 1, '2025-09-09 11:02:06', '2025-09-09 11:02:06'),
(230, 'KPG', 'PUNE', 'CR', 'Kopargaon', 1, '2025-09-09 11:02:06', '2025-09-09 11:02:06'),
(231, 'KQE', 'NGP', 'CR', 'Kala Akhar', 1, '2025-09-09 11:02:06', '2025-09-09 11:02:06'),
(232, 'KRD', 'PUNE', 'CR', 'Karad', 1, '2025-09-09 11:02:06', '2025-09-09 11:02:06'),
(233, 'KRG', 'PUNE', 'CR', 'Koregaon', 1, '2025-09-09 11:02:06', '2025-09-09 11:02:06'),
(234, 'KRI', 'NGP', 'CR', 'Khapri', 1, '2025-09-09 11:02:06', '2025-09-09 11:02:06'),
(235, 'KRSP', 'NGP', 'CR', 'KIRSADOH RAILWAY SIDING', 1, '2025-09-09 11:02:06', '2025-09-09 11:02:06'),
(236, 'KRTH', 'NGP', 'CR', 'Kiratgarh', 1, '2025-09-09 11:02:06', '2025-09-09 11:02:06'),
(237, 'KRYL', 'PUNE', 'CR', 'M/S RAMAKRISHI RASAYANI SDG.', 1, '2025-09-09 11:02:06', '2025-09-09 11:02:06'),
(238, 'KSAG', 'CSMT', 'CR', 'Steel Authority of India Ltd. Siding', 1, '2025-09-09 11:02:06', '2025-09-09 11:02:06'),
(239, 'KSLA', 'NGP', 'CR', 'Kesla', 1, '2025-09-09 11:02:06', '2025-09-09 11:02:06'),
(240, 'KSWD', 'PUNE', 'CR', 'Kasarwadi', 1, '2025-09-09 11:02:06', '2025-09-09 11:02:06'),
(241, 'KSWR', 'NGP', 'CR', 'Kalmeshwar', 1, '2025-09-09 11:02:06', '2025-09-09 11:02:06'),
(242, 'KTIG', 'CSMT', 'CR', 'TATA IRON & STEEL CO. SIDING', 1, '2025-09-09 11:02:06', '2025-09-09 11:02:06'),
(243, 'KTP', 'BSL', 'CR', 'Katepurna', 1, '2025-09-09 11:02:06', '2025-09-09 11:02:06'),
(244, 'KTTG', 'CSMT', 'CR', 'TATA IRON & STEEL CO. SIDING', 1, '2025-09-09 11:02:06', '2025-09-09 11:02:06'),
(245, 'KUM', 'BSL', 'CR', 'Kuram', 1, '2025-09-09 11:02:06', '2025-09-09 11:02:06'),
(246, 'KUX', 'NGP', 'CR', 'Khirsadoh Jn.', 1, '2025-09-09 11:02:06', '2025-09-09 11:02:06'),
(247, 'KVSG', 'PUNE', 'CR', 'ARMOURED FIGHTING VEHICLE DEPOT SIDING, KHADKI', 1, '2025-09-09 11:02:07', '2025-09-09 11:02:07'),
(248, 'KW', 'BSL', 'CR', 'Khervadi', 1, '2025-09-09 11:02:07', '2025-09-09 11:02:07'),
(249, 'KWV', 'SUR', 'CR', 'Kurduwadi', 1, '2025-09-09 11:02:07', '2025-09-09 11:02:07'),
(250, 'KYN', 'CSMT', 'CR', 'Kalyan Jn.', 1, '2025-09-09 11:02:07', '2025-09-09 11:02:07'),
(251, 'KYNX', 'CSMT', 'CR', 'KALYAN JN. YARD (TXR)', 1, '2025-09-09 11:02:07', '2025-09-09 11:02:07'),
(252, 'LAUL', 'SUR', 'CR', 'Laul', 1, '2025-09-09 11:02:07', '2025-09-09 11:02:07'),
(253, 'LLD', 'NGP', 'CR', 'Lalawadi', 1, '2025-09-09 11:02:07', '2025-09-09 11:02:07'),
(254, 'LNL', 'CSMT', 'CR', 'LONAVALA', 1, '2025-09-09 11:02:07', '2025-09-09 11:02:07'),
(255, 'LNLX', 'CSMT', 'CR', 'LONAVLA YARD (TXR)', 1, '2025-09-09 11:02:07', '2025-09-09 11:02:07'),
(256, 'LNN', 'PUNE', 'CR', 'Lonand', 1, '2025-09-09 11:02:07', '2025-09-09 11:02:07'),
(257, 'LNT', 'NGP', 'CR', 'LINGTI', 1, '2025-09-09 11:02:07', '2025-09-09 11:02:07'),
(258, 'LONI', 'PUNE', 'CR', 'Loni', 1, '2025-09-09 11:02:07', '2025-09-09 11:02:07'),
(259, 'LPSG', 'NGP', 'CR', 'L P. G. Bottling PlantSiding for Hindustan', 1, '2025-09-09 11:02:07', '2025-09-09 11:02:07'),
(260, 'LS', 'BSL', 'CR', 'Lasalgaon', 1, '2025-09-09 11:02:07', '2025-09-09 11:02:07'),
(261, 'LT', 'BSL', 'CR', 'Lahavit', 1, '2025-09-09 11:02:07', '2025-09-09 11:02:07'),
(262, 'LUR', 'SUR', 'CR', 'Latur', 1, '2025-09-09 11:02:07', '2025-09-09 11:02:07'),
(263, 'MALK', 'NGP', 'CR', 'Malkapur Road', 1, '2025-09-09 11:02:07', '2025-09-09 11:02:07'),
(264, 'MANA', 'BSL', 'CR', 'Mana', 1, '2025-09-09 11:02:07', '2025-09-09 11:02:07'),
(265, 'MBCB', 'NGP', 'CR', 'BALLARPUR COLLIERY SDG.', 1, '2025-09-09 11:02:07', '2025-09-09 11:02:07'),
(266, 'MBPP', 'CSMT', 'CR', 'BPCL SDG. AT URAN', 1, '2025-09-09 11:02:07', '2025-09-09 11:02:07'),
(267, 'MBSH', 'SUR', 'CR', 'M/S ULTRATECH CEMENT LTD.', 1, '2025-09-09 11:02:07', '2025-09-09 11:02:07'),
(268, 'MDDG', 'PUNE', 'CR', 'Maladgaon', 1, '2025-09-09 11:02:07', '2025-09-09 11:02:07'),
(269, 'MDIT', 'NGP', 'CR', 'Dhariwal infrastrutre ltd siding', 1, '2025-09-09 11:02:07', '2025-09-09 11:02:07'),
(270, 'MELG', 'NGP', 'CR', 'Maharashtra Electrosmelt Siding', 1, '2025-09-09 11:02:07', '2025-09-09 11:02:07'),
(271, 'MER', 'NGP', 'CR', 'Metpanjra', 1, '2025-09-09 11:02:07', '2025-09-09 11:02:07'),
(272, 'MFSG', 'BSL', 'CR', 'Maharashtra State Electricity Board Siding Bsl', 1, '2025-09-09 11:02:07', '2025-09-09 11:02:07'),
(273, 'MGCS', 'CSMT', 'CR', 'MAHARASHTRA GAS CRAKER COMPLEX SDG', 1, '2025-09-09 11:02:08', '2025-09-09 11:02:08'),
(274, 'MGO', 'SUR', 'CR', 'Mahisgaon', 1, '2025-09-09 11:02:08', '2025-09-09 11:02:08'),
(275, 'MGRD', 'NGP', 'CR', 'MagarDoh', 1, '2025-09-09 11:02:08', '2025-09-09 11:02:08'),
(276, 'MHAD', 'BSL', 'CR', 'Mohadi Pragane Laling', 1, '2025-09-09 11:02:08', '2025-09-09 11:02:08'),
(277, 'MHLC', 'CSMT', 'CR', 'MONKEY HILL', 1, '2025-09-09 11:02:08', '2025-09-09 11:02:08'),
(278, 'MILK', 'CSMT', 'CR', 'M/S CENTRAL WAREHOUSING CORPORATION.', 1, '2025-09-09 11:02:08', '2025-09-09 11:02:08'),
(279, 'MIOJ', 'CSMT', 'CR', 'M/S IOT INFRASRUCTURE & ENERGY SERVICES LTD.', 1, '2025-09-09 11:02:08', '2025-09-09 11:02:08'),
(280, 'MJKN', 'NGP', 'CR', 'Majrikhadan', 1, '2025-09-09 11:02:08', '2025-09-09 11:02:08'),
(281, 'MJRI', 'NGP', 'CR', 'Majri Jn.', 1, '2025-09-09 11:02:08', '2025-09-09 11:02:08'),
(282, 'MJSG', 'NGP', 'CR', 'NEW MANJRI COLLIERY SIDING, MAJRI JN.', 1, '2025-09-09 11:02:08', '2025-09-09 11:02:08'),
(283, 'MJY', 'NGP', 'CR', 'Maramjhiri', 1, '2025-09-09 11:02:08', '2025-09-09 11:02:08'),
(284, 'MKCW', 'NGP', 'CR', 'KARTIKEYA COAL WASHERY SDG.', 1, '2025-09-09 11:02:08', '2025-09-09 11:02:08'),
(285, 'MKDN', 'NGP', 'CR', 'Markadhana', 1, '2025-09-09 11:02:08', '2025-09-09 11:02:08'),
(286, 'MKSG', 'CSMT', 'CR', 'MILITARY TRANSIT SDG MANKHURD', 1, '2025-09-09 11:02:08', '2025-09-09 11:02:08'),
(287, 'MKU', 'BSL', 'CR', 'Malkapur', 1, '2025-09-09 11:02:08', '2025-09-09 11:02:08'),
(288, 'MLR', 'NGP', 'CR', 'Malkhed', 1, '2025-09-09 11:02:08', '2025-09-09 11:02:08'),
(289, 'MLSW', 'NGP', 'CR', 'M/s evonith value steel ltd.', 1, '2025-09-09 11:02:08', '2025-09-09 11:02:08'),
(290, 'MMR', 'BSL', 'CR', 'Manmad Jn.', 1, '2025-09-09 11:02:08', '2025-09-09 11:02:08'),
(291, 'MMRX', 'BSL', 'CR', 'MANMAD JN. YARD (TXR)', 1, '2025-09-09 11:02:08', '2025-09-09 11:02:08'),
(292, 'MNSG', 'NGP', 'CR', 'MAJRI OLD SDG, MAJRI JN.', 1, '2025-09-09 11:02:08', '2025-09-09 11:02:08'),
(293, 'MORS', 'NGP', 'CR', 'MOORSA', 1, '2025-09-09 11:02:08', '2025-09-09 11:02:08'),
(294, 'MPBG', 'NGP', 'CR', 'MPEB SDG.', 1, '2025-09-09 11:02:08', '2025-09-09 11:02:08'),
(295, 'MQSG', 'BSL', 'CR', 'Maharashtra State Electricity Boards Siding Odha', 1, '2025-09-09 11:02:08', '2025-09-09 11:02:08'),
(296, 'MRDK', 'NGP', 'CR', 'Makardhokada', 1, '2025-09-09 11:02:08', '2025-09-09 11:02:08'),
(297, 'MRJ', 'PUNE', 'CR', 'Miraj Jn.', 1, '2025-09-09 11:02:08', '2025-09-09 11:02:08'),
(298, 'MRJX', 'PUNE', 'CR', 'MIRAJ JN. YARD (TXR)', 1, '2025-09-09 11:02:09', '2025-09-09 11:02:09'),
(299, 'MRSH', 'NGP', 'CR', 'Morshi', 1, '2025-09-09 11:02:09', '2025-09-09 11:02:09'),
(300, 'MSR', 'PUNE', 'CR', 'Masur', 1, '2025-09-09 11:02:09', '2025-09-09 11:02:09'),
(301, 'MTY', 'NGP', 'CR', 'Multai', 1, '2025-09-09 11:02:09', '2025-09-09 11:02:09'),
(302, 'MVL', 'PUNE', 'CR', 'Malavli', 1, '2025-09-09 11:02:09', '2025-09-09 11:02:09'),
(303, 'MWA', 'BSL', 'CR', 'Mandwa', 1, '2025-09-09 11:02:09', '2025-09-09 11:02:09'),
(304, 'MWAD', 'NGP', 'CR', 'MOWAD', 1, '2025-09-09 11:02:09', '2025-09-09 11:02:09'),
(305, 'MWD', 'BSL', 'CR', 'Mhasavad', 1, '2025-09-09 11:02:09', '2025-09-09 11:02:09'),
(306, 'MWK', 'BSL', 'CR', 'Mordad Tanda', 1, '2025-09-09 11:02:09', '2025-09-09 11:02:09'),
(307, 'MYJ', 'BSL', 'CR', 'Maheji', 1, '2025-09-09 11:02:09', '2025-09-09 11:02:09'),
(308, 'MZR', 'BSL', 'CR', 'Murtajapur', 1, '2025-09-09 11:02:09', '2025-09-09 11:02:09'),
(309, 'NB', 'BSL', 'CR', 'Nimbhora', 1, '2025-09-09 11:02:09', '2025-09-09 11:02:09'),
(310, 'NDE', 'PUNE', 'CR', 'Nandre', 1, '2025-09-09 11:02:09', '2025-09-09 11:02:09'),
(311, 'NEDA', 'NGP', 'CR', 'new electric loco shed - government maintenance depot, ajni', 1, '2025-09-09 11:02:09', '2025-09-09 11:02:09'),
(312, 'NEI', 'SUR', 'CR', 'Neoli', 1, '2025-09-09 11:02:09', '2025-09-09 11:02:09'),
(313, 'NGD', 'BSL', 'CR', 'Nagardevla', 1, '2025-09-09 11:02:09', '2025-09-09 11:02:09'),
(314, 'NGI', 'NGP', 'CR', 'Nagri', 1, '2025-09-09 11:02:09', '2025-09-09 11:02:09'),
(315, 'NGN', 'BSL', 'CR', 'Nandgaon', 1, '2025-09-09 11:02:09', '2025-09-09 11:02:09'),
(316, 'NGNX', 'BSL', 'CR', 'NANDGAON YARD (TXR)', 1, '2025-09-09 11:02:09', '2025-09-09 11:02:09'),
(317, 'NGP', 'NGP', 'CR', 'Nagpur', 1, '2025-09-09 11:02:09', '2025-09-09 11:02:09'),
(318, 'NGSM', 'CSMT', 'CR', 'New Mulund Goods Depot', 1, '2025-09-09 11:02:09', '2025-09-09 11:02:09'),
(319, 'NGTN', 'CSMT', 'CR', 'Nagothane', 1, '2025-09-09 11:02:09', '2025-09-09 11:02:09'),
(320, 'NGZ', 'BSL', 'CR', 'Shri Kshetra Nagjhari', 1, '2025-09-09 11:02:09', '2025-09-09 11:02:09'),
(321, 'NI', 'BSL', 'CR', 'Naydongri', 1, '2025-09-09 11:02:09', '2025-09-09 11:02:09'),
(322, 'NIRA', 'PUNE', 'CR', 'Nira', 1, '2025-09-09 11:02:09', '2025-09-09 11:02:09'),
(323, 'NK', 'BSL', 'CR', 'Nashik Road', 1, '2025-09-09 11:02:09', '2025-09-09 11:02:09'),
(324, 'NKSG', 'BSL', 'CR', 'Security PressSiding Nasik', 1, '2025-09-09 11:02:09', '2025-09-09 11:02:09'),
(325, 'NLGS', 'NGP', 'CR', 'M/s NAGPUR MMLP GATI SHAKTI MULTI-MODAL CARGO TERMINAL', 1, '2025-09-09 11:02:09', '2025-09-09 11:02:09'),
(326, 'NMDK', 'NGP', 'CR', 'New Makardhokada', 1, '2025-09-09 11:02:09', '2025-09-09 11:02:09'),
(327, 'NMSG', 'BSL', 'CR', 'NEPA LIMITED SIDING', 1, '2025-09-09 11:02:09', '2025-09-09 11:02:09'),
(328, 'NN', 'BSL', 'CR', 'NANDURA', 1, '2025-09-09 11:02:09', '2025-09-09 11:02:09'),
(329, 'NNB', 'PUNE', 'CR', 'Nimblak', 1, '2025-09-09 11:02:09', '2025-09-09 11:02:09'),
(330, 'NNCN', 'CSMT', 'CR', 'NAGNATH CABIN', 1, '2025-09-09 11:02:09', '2025-09-09 11:02:09'),
(331, 'NPNR', 'BSL', 'CR', 'Nepanagar', 1, '2025-09-09 11:02:10', '2025-09-09 11:02:10'),
(332, 'NR', 'BSL', 'CR', 'Niphad', 1, '2025-09-09 11:02:10', '2025-09-09 11:02:10'),
(333, 'NRKR', 'NGP', 'CR', 'Narkher', 1, '2025-09-09 11:02:10', '2025-09-09 11:02:10'),
(334, 'NRSG', 'CSMT', 'CR', 'NATIONAL RAYON CORPN. SDG', 1, '2025-09-09 11:02:10', '2025-09-09 11:02:10'),
(335, 'NSKG', 'CSMT', 'CR', 'NAVAL SIDING KARANJA, URAN CITY', 1, '2025-09-09 11:02:10', '2025-09-09 11:02:10'),
(336, 'NTPG', 'NGP', 'CR', 'New Thermal Power Station Siding-Chandrapur', 1, '2025-09-09 11:02:10', '2025-09-09 11:02:10'),
(337, 'NVG', 'NGP', 'CR', 'Navegaon', 1, '2025-09-09 11:02:10', '2025-09-09 11:02:10'),
(338, 'NYDO', 'PUNE', 'CR', 'NARAYANDOHO', 1, '2025-09-09 11:02:10', '2025-09-09 11:02:10'),
(339, 'OCSB', 'BSL', 'CR', 'ORIENT CEMENT SDG Bhadli', 1, '2025-09-09 11:02:10', '2025-09-09 11:02:10'),
(340, 'ODHA', 'BSL', 'CR', 'Odha', 1, '2025-09-09 11:02:10', '2025-09-09 11:02:10'),
(341, 'PAA', 'PUNE', 'CR', 'Patas', 1, '2025-09-09 11:02:10', '2025-09-09 11:02:10'),
(342, 'PAAL', 'NGP', 'CR', 'Pala', 1, '2025-09-09 11:02:10', '2025-09-09 11:02:10'),
(343, 'PALB', 'NGP', 'CR', 'M/s adani logistics ltd. pft', 1, '2025-09-09 11:02:10', '2025-09-09 11:02:10'),
(344, 'PAR', 'NGP', 'CR', 'Pandhurna', 1, '2025-09-09 11:02:10', '2025-09-09 11:02:10'),
(345, 'PATP', 'CSMT', 'CR', 'adani agri logistics ltd', 1, '2025-09-09 11:02:10', '2025-09-09 11:02:10'),
(346, 'PC', 'BSL', 'CR', 'Pachora Jn.', 1, '2025-09-09 11:02:10', '2025-09-09 11:02:10'),
(347, 'PCGN', 'NGP', 'CR', 'Pachegaon', 1, '2025-09-09 11:02:10', '2025-09-09 11:02:10'),
(348, 'PCLI', 'NGP', 'CR', 'Palachauri', 1, '2025-09-09 11:02:10', '2025-09-09 11:02:10'),
(349, 'PCP', 'SUR', 'CR', 'Palsap', 1, '2025-09-09 11:02:11', '2025-09-09 11:02:11'),
(350, 'PCPK', 'NGP', 'CR', 'Multi Modal Logistic Park', 1, '2025-09-09 11:02:11', '2025-09-09 11:02:11'),
(351, 'PEN', 'CSMT', 'CR', 'Pen', 1, '2025-09-09 11:02:11', '2025-09-09 11:02:11'),
(352, 'PHQ', 'BSL', 'CR', 'Pardhade', 1, '2025-09-09 11:02:11', '2025-09-09 11:02:11'),
(353, 'PI', 'BSL', 'CR', 'Padli', 1, '2025-09-09 11:02:11', '2025-09-09 11:02:11'),
(354, 'PIOP', 'SUR', 'CR', 'POL SDG FOR M/S IOC', 1, '2025-09-09 11:02:11', '2025-09-09 11:02:11'),
(355, 'PJN', 'BSL', 'CR', 'Panjhan', 1, '2025-09-09 11:02:11', '2025-09-09 11:02:11'),
(356, 'PKE', 'BSL', 'CR', 'Pimparkhed', 1, '2025-09-09 11:02:11', '2025-09-09 11:02:11'),
(357, 'PLLD', 'PUNE', 'CR', 'PHALTAN', 1, '2025-09-09 11:02:11', '2025-09-09 11:02:11'),
(358, 'PLO', 'NGP', 'CR', 'Pulgaon jn.', 1, '2025-09-09 11:02:11', '2025-09-09 11:02:11'),
(359, 'PLV', 'PUNE', 'CR', 'Palsi', 1, '2025-09-09 11:02:11', '2025-09-09 11:02:11'),
(360, 'PMEC', 'NGP', 'CR', 'M/s GMR Warora Energy Ltd', 1, '2025-09-09 11:02:11', '2025-09-09 11:02:11'),
(361, 'PMKT', 'NGP', 'CR', 'Pimpalkhuti', 1, '2025-09-09 11:02:11', '2025-09-09 11:02:11'),
(362, 'PMP', 'PUNE', 'CR', 'Pimpri', 1, '2025-09-09 11:02:11', '2025-09-09 11:02:11'),
(363, 'PNCS', 'CSMT', 'CR', 'NAVKAR CORP. LTD', 1, '2025-09-09 11:02:11', '2025-09-09 11:02:11'),
(364, 'PNV', 'BSL', 'CR', 'Panevadi', 1, '2025-09-09 11:02:11', '2025-09-09 11:02:11'),
(365, 'POLG', 'BSL', 'CR', 'POL SDG. FOR M/S IOC GAIGAON', 1, '2025-09-09 11:02:11', '2025-09-09 11:02:11'),
(366, 'POSG', 'NGP', 'CR', 'ORDINANCE DEPOT MILITARY SDG, PULGAON', 1, '2025-09-09 11:02:11', '2025-09-09 11:02:11'),
(367, 'POX', 'NGP', 'CR', 'POLA PATHAR', 1, '2025-09-09 11:02:11', '2025-09-09 11:02:11'),
(368, 'PPCP', 'PUNE', 'CR', 'M/s Penna cement Industries ltd', 1, '2025-09-09 11:02:11', '2025-09-09 11:02:11'),
(369, 'PPDP', 'CSMT', 'CR', 'M/S PNP MARITIME SERVICES LTD', 1, '2025-09-09 11:02:11', '2025-09-09 11:02:11'),
(370, 'PRGT', 'NGP', 'CR', 'Pargothan', 1, '2025-09-09 11:02:11', '2025-09-09 11:02:11'),
(371, 'PRLW', 'CSMT', 'CR', 'PAREL LOCO WORK SHOP', 1, '2025-09-09 11:02:12', '2025-09-09 11:02:12'),
(372, 'PS', 'BSL', 'CR', 'Paras', 1, '2025-09-09 11:02:12', '2025-09-09 11:02:12'),
(373, 'PSIA', 'PUNE', 'CR', 'POL SDG FOR M/S IOC AKOLNER', 1, '2025-09-09 11:02:12', '2025-09-09 11:02:12'),
(374, 'PSNH', 'SUR', 'CR', 'm/S National Thermal Power Corporation LTD', 1, '2025-09-09 11:02:12', '2025-09-09 11:02:12'),
(375, 'PSS', 'SUR', 'CR', 'Padsali', 1, '2025-09-09 11:02:12', '2025-09-09 11:02:12'),
(376, 'PTRT', 'SUR', 'CR', 'Pathrot', 1, '2025-09-09 11:02:12', '2025-09-09 11:02:12'),
(377, 'PUNE', 'PUNE', 'CR', 'Pune Jn.', 1, '2025-09-09 11:02:12', '2025-09-09 11:02:12'),
(378, 'PUSA', 'NGP', 'CR', 'PUSLA', 1, '2025-09-09 11:02:12', '2025-09-09 11:02:12'),
(379, 'PUX', 'NGP', 'CR', 'Parasia', 1, '2025-09-09 11:02:12', '2025-09-09 11:02:12'),
(380, 'PVIT', 'NGP', 'CR', 'M/S VimaLa infrastructure india pvt ltd', 1, '2025-09-09 11:02:12', '2025-09-09 11:02:12'),
(381, 'PVR', 'SUR', 'CR', 'Pandharpur', 1, '2025-09-09 11:02:12', '2025-09-09 11:02:12'),
(382, 'PWCL', 'NGP', 'CR', 'M/s Sai Wardha Power Generation Ltd.', 1, '2025-09-09 11:02:12', '2025-09-09 11:02:12'),
(383, 'RAJR', 'NGP', 'CR', 'Rajur', 1, '2025-09-09 11:02:12', '2025-09-09 11:02:12'),
(384, 'RANG', 'SUR', 'CR', 'Ramling', 1, '2025-09-09 11:02:12', '2025-09-09 11:02:12'),
(385, 'RCXG', 'NGP', 'CR', 'RAYATWARI COLLIERY SDG.', 1, '2025-09-09 11:02:12', '2025-09-09 11:02:12'),
(386, 'RGTM', 'NGP', 'CR', 'M/s Reliance cement company pvt. ltd.', 1, '2025-09-09 11:02:12', '2025-09-09 11:02:12'),
(387, 'RHNE', 'BSL', 'CR', 'Rohini', 1, '2025-09-09 11:02:12', '2025-09-09 11:02:12'),
(388, 'RHO', 'NGP', 'CR', 'Rohna', 1, '2025-09-09 11:02:12', '2025-09-09 11:02:12'),
(389, 'RHOT', 'NGP', 'CR', 'Rohna Town', 1, '2025-09-09 11:02:12', '2025-09-09 11:02:12'),
(390, 'RID', 'SUR', 'CR', 'Ridhore', 1, '2025-09-09 11:02:12', '2025-09-09 11:02:12'),
(391, 'RJSG', 'NGP', 'CR', 'RAJUR COLLIERY SDG.', 1, '2025-09-09 11:02:12', '2025-09-09 11:02:12'),
(392, 'RJW', 'PUNE', 'CR', 'Rajevadi', 1, '2025-09-09 11:02:12', '2025-09-09 11:02:12'),
(393, 'RKD', 'PUNE', 'CR', 'Rukadi', 1, '2025-09-09 11:02:12', '2025-09-09 11:02:12'),
(394, 'RKSG', 'NGP', 'CR', 'RAWANWARA KHAS COLLIERY SDG.', 1, '2025-09-09 11:02:12', '2025-09-09 11:02:12'),
(395, 'RM', 'BSL', 'CR', 'Rajmane', 1, '2025-09-09 11:02:12', '2025-09-09 11:02:12'),
(396, 'RMP', 'PUNE', 'CR', 'Rahimatpur', 1, '2025-09-09 11:02:13', '2025-09-09 11:02:13'),
(397, 'RNGS', 'NGP', 'CR', 'rajur new goods shed served by rajur', 1, '2025-09-09 11:02:13', '2025-09-09 11:02:13'),
(398, 'RNSG', 'CSMT', 'CR', 'INDIAN NAVY STORE DEPOT MILITARY SIDING, KURLA JN.', 1, '2025-09-09 11:02:13', '2025-09-09 11:02:13'),
(399, 'ROHA', 'CSMT', 'CR', 'Roha', 1, '2025-09-09 11:02:13', '2025-09-09 11:02:13'),
(400, 'RPLW', 'BSL', 'CR', 'M/S RATANINDIA POWER lTD', 1, '2025-09-09 11:02:13', '2025-09-09 11:02:13'),
(401, 'RRI', 'PUNE', 'CR', 'Rahuri', 1, '2025-09-09 11:02:13', '2025-09-09 11:02:13'),
(402, 'RV', 'BSL', 'CR', 'Raver', 1, '2025-09-09 11:02:13', '2025-09-09 11:02:13'),
(403, 'RVJ', 'CSMT', 'CR', 'RAVLI JN', 1, '2025-09-09 11:02:13', '2025-09-09 11:02:13'),
(404, 'SAHL', 'NGP', 'CR', 'Saheli', 1, '2025-09-09 11:02:13', '2025-09-09 11:02:13'),
(405, 'SAV', 'BSL', 'CR', 'Savda', 1, '2025-09-09 11:02:13', '2025-09-09 11:02:13'),
(406, 'SCGP', 'PUNE', 'CR', 'm/s maharashtra cement plant (a unit of shri cement ltd.) at patas', 1, '2025-09-09 11:02:13', '2025-09-09 11:02:13'),
(407, 'SDSG', 'SUR', 'CR', 'JAYPEE CEMENT CORPORATION LTD', 1, '2025-09-09 11:02:13', '2025-09-09 11:02:13'),
(408, 'SEG', 'BSL', 'CR', 'Shegaon', 1, '2025-09-09 11:02:13', '2025-09-09 11:02:13'),
(409, 'SEGM', 'NGP', 'CR', 'Sevagram', 1, '2025-09-09 11:02:13', '2025-09-09 11:02:13'),
(410, 'SGND', 'PUNE', 'CR', 'Shrigonda Road', 1, '2025-09-09 11:02:13', '2025-09-09 11:02:13'),
(411, 'SHF', 'BSL', 'CR', 'Shirud', 1, '2025-09-09 11:02:13', '2025-09-09 11:02:13'),
(412, 'SHIV', 'PUNE', 'CR', 'Shindawane', 1, '2025-09-09 11:02:13', '2025-09-09 11:02:13'),
(413, 'SIW', 'PUNE', 'CR', 'Shiravde', 1, '2025-09-09 11:02:13', '2025-09-09 11:02:13'),
(414, 'SLI', 'PUNE', 'CR', 'Sangli', 1, '2025-09-09 11:02:13', '2025-09-09 11:02:13'),
(415, 'SLNK', 'SUR', 'CR', 'SULTANPUR KARNATAKA', 1, '2025-09-09 11:02:13', '2025-09-09 11:02:13'),
(416, 'SLOR', 'NGP', 'CR', 'Seloo Road', 1, '2025-09-09 11:02:13', '2025-09-09 11:02:13'),
(417, 'SLP', 'PUNE', 'CR', 'Salpa', 1, '2025-09-09 11:02:13', '2025-09-09 11:02:13'),
(418, 'SLRW', 'PUNE', 'CR', 'Shelarwadi', 1, '2025-09-09 11:02:13', '2025-09-09 11:02:13'),
(419, 'SNE', 'PUNE', 'CR', 'Shenoli', 1, '2025-09-09 11:02:13', '2025-09-09 11:02:13'),
(420, 'SNI', 'NGP', 'CR', 'Sindi', 1, '2025-09-09 11:02:13', '2025-09-09 11:02:13'),
(421, 'SNKB', 'NGP', 'CR', 'Sonkhamb', 1, '2025-09-09 11:02:13', '2025-09-09 11:02:13'),
(422, 'SNN', 'NGP', 'CR', 'Sonegaon', 1, '2025-09-09 11:02:13', '2025-09-09 11:02:13'),
(423, 'SRTA', 'NGP', 'CR', 'Sorta', 1, '2025-09-09 11:02:13', '2025-09-09 11:02:13'),
(424, 'SS', 'BSL', 'CR', 'Shirsoli', 1, '2025-09-09 11:02:13', '2025-09-09 11:02:13'),
(425, 'SSF', 'PUNE', 'CR', 'Sirsuphal', 1, '2025-09-09 11:02:13', '2025-09-09 11:02:13'),
(426, 'SSV', 'PUNE', 'CR', 'Sasvad Road', 1, '2025-09-09 11:02:13', '2025-09-09 11:02:13'),
(427, 'STR', 'PUNE', 'CR', 'Satara', 1, '2025-09-09 11:02:13', '2025-09-09 11:02:13'),
(428, 'SUM', 'BSL', 'CR', 'Summit', 1, '2025-09-09 11:02:14', '2025-09-09 11:02:14'),
(429, 'SUR', 'SUR', 'CR', 'Solapur', 1, '2025-09-09 11:02:14', '2025-09-09 11:02:14'),
(430, 'SURX', 'SUR', 'CR', 'SOLAPUR JN. YARD (TXR)', 1, '2025-09-09 11:02:14', '2025-09-09 11:02:14'),
(431, 'SVJR', 'PUNE', 'CR', 'Shivaji Nagar', 1, '2025-09-09 11:02:14', '2025-09-09 11:02:14'),
(432, 'SXA', 'BSL', 'CR', 'Sagphata', 1, '2025-09-09 11:02:14', '2025-09-09 11:02:14'),
(433, 'TAE', 'NGP', 'CR', 'Tadali', 1, '2025-09-09 11:02:14', '2025-09-09 11:02:14'),
(434, 'TAKU', 'NGP', 'CR', 'Taku', 1, '2025-09-09 11:02:14', '2025-09-09 11:02:14'),
(435, 'TAPG', 'CSMT', 'CR', 'TURBHE APM COMPLEX', 1, '2025-09-09 11:02:14', '2025-09-09 11:02:14'),
(436, 'TAZ', 'PUNE', 'CR', 'Targaon', 1, '2025-09-09 11:02:14', '2025-09-09 11:02:14'),
(437, 'TEO', 'NGP', 'CR', 'Teegaon', 1, '2025-09-09 11:02:14', '2025-09-09 11:02:14'),
(438, 'TER', 'SUR', 'CR', 'Thair', 1, '2025-09-09 11:02:14', '2025-09-09 11:02:14'),
(439, 'TGCR', 'CSMT', 'CR', 'TGR Cabin No. 1', 1, '2025-09-09 11:02:14', '2025-09-09 11:02:14'),
(440, 'TGN', 'PUNE', 'CR', 'Talegaon', 1, '2025-09-09 11:02:14', '2025-09-09 11:02:14'),
(441, 'TGP', 'NGP', 'CR', 'Tuljapur', 1, '2025-09-09 11:02:14', '2025-09-09 11:02:14'),
(442, 'TGRT', 'CSMT', 'CR', 'TGR Cabin No. 2', 1, '2025-09-09 11:02:14', '2025-09-09 11:02:14'),
(443, 'TGTC', 'CSMT', 'CR', 'TGR Cabin No. 3', 1, '2025-09-09 11:02:14', '2025-09-09 11:02:14'),
(444, 'THAL', 'CSMT', 'CR', 'THAL', 1, '2025-09-09 11:02:14', '2025-09-09 11:02:14'),
(445, 'THK', 'CSMT', 'CR', 'Thakurli', 1, '2025-09-09 11:02:14', '2025-09-09 11:02:14'),
(446, 'THSG', 'NGP', 'CR', 'Satpura Thermal power siding', 1, '2025-09-09 11:02:14', '2025-09-09 11:02:14'),
(447, 'TJSP', 'SUR', 'CR', 'TAJ SULTANPUR', 1, '2025-09-09 11:02:14', '2025-09-09 11:02:14'),
(448, 'TKI', 'BSL', 'CR', 'Takli', 1, '2025-09-09 11:02:14', '2025-09-09 11:02:14'),
(449, 'TKMY', 'PUNE', 'CR', 'Taklimiya', 1, '2025-09-09 11:02:14', '2025-09-09 11:02:14'),
(450, 'TKR', 'PUNE', 'CR', 'Takari', 1, '2025-09-09 11:02:14', '2025-09-09 11:02:14'),
(451, 'TLN', 'NGP', 'CR', 'Talni', 1, '2025-09-09 11:02:14', '2025-09-09 11:02:14'),
(452, 'TMBY', 'CSMT', 'CR', 'Trombay', 1, '2025-09-09 11:02:14', '2025-09-09 11:02:14'),
(453, 'TMT', 'NGP', 'CR', 'Timtala', 1, '2025-09-09 11:02:14', '2025-09-09 11:02:14'),
(454, 'TNH', 'NGP', 'CR', 'Tinkheda', 1, '2025-09-09 11:02:14', '2025-09-09 11:02:14'),
(455, 'TPHG', 'CSMT', 'CR', 'Tata Power House Siding', 1, '2025-09-09 11:02:14', '2025-09-09 11:02:14'),
(456, 'TPND', 'CSMT', 'CR', 'TALOJE PANCHNAND', 1, '2025-09-09 11:02:14', '2025-09-09 11:02:14'),
(457, 'TRW', 'BSL', 'CR', 'Tarsod', 1, '2025-09-09 11:02:14', '2025-09-09 11:02:14'),
(458, 'TTPS', 'CSMT', 'CR', 'Tata Thermal Power Station Siding', 1, '2025-09-09 11:02:14', '2025-09-09 11:02:14'),
(459, 'TVSG', 'CSMT', 'CR', 'Rashtriya Chemicals and Fertilizers siding-Thal Vaishett', 1, '2025-09-09 11:02:14', '2025-09-09 11:02:14'),
(460, 'UBCN', 'CSMT', 'CR', 'ULHAS BRIDGE CABIN', 1, '2025-09-09 11:02:14', '2025-09-09 11:02:14'),
(461, 'UGN', 'BSL', 'CR', 'Ugaon', 1, '2025-09-09 11:02:14', '2025-09-09 11:02:14'),
(462, 'UMSG', 'NGP', 'CR', 'Umred Colliery Siding', 1, '2025-09-09 11:02:14', '2025-09-09 11:02:14'),
(463, 'UPI', 'SUR', 'CR', 'Uplai', 1, '2025-09-09 11:02:14', '2025-09-09 11:02:14'),
(464, 'URAN', 'CSMT', 'CR', 'URAN', 1, '2025-09-09 11:02:14', '2025-09-09 11:02:14'),
(465, 'URI', 'PUNE', 'CR', 'Uruli', 1, '2025-09-09 11:02:14', '2025-09-09 11:02:14'),
(466, 'UTCU', 'PUNE', 'CR', 'M/s Ultratech cement siding', 1, '2025-09-09 11:02:14', '2025-09-09 11:02:14'),
(467, 'VADR', 'NGP', 'CR', 'Varud', 1, '2025-09-09 11:02:15', '2025-09-09 11:02:15'),
(468, 'VDN', 'PUNE', 'CR', 'Vadgaon', 1, '2025-09-09 11:02:15', '2025-09-09 11:02:15'),
(469, 'VGL', 'BSL', 'CR', 'Vaghli', 1, '2025-09-09 11:02:15', '2025-09-09 11:02:15'),
(470, 'VIPS', 'NGP', 'CR', 'm/s Vidharbha Inustries Power Ltd', 1, '2025-09-09 11:02:15', '2025-09-09 11:02:15'),
(471, 'VL', 'PUNE', 'CR', 'Vilad', 1, '2025-09-09 11:02:15', '2025-09-09 11:02:15'),
(472, 'VNA', 'BSL', 'CR', 'Varangaon', 1, '2025-09-09 11:02:15', '2025-09-09 11:02:15'),
(473, 'VOSG', 'CSMT', 'CR', 'Hindustan Petroleum Corporation Siding', 1, '2025-09-09 11:02:15', '2025-09-09 11:02:15'),
(474, 'VSD', 'CSMT', 'CR', 'Vasind', 1, '2025-09-09 11:02:15', '2025-09-09 11:02:15'),
(475, 'VSPG', 'CSMT', 'CR', 'VISHAKAPATTANAM STEEL PROJECT SDG.', 1, '2025-09-09 11:02:15', '2025-09-09 11:02:15'),
(476, 'VUL', 'NGP', 'CR', 'Virul', 1, '2025-09-09 11:02:15', '2025-09-09 11:02:15'),
(477, 'VV', 'PUNE', 'CR', 'Valivade', 1, '2025-09-09 11:02:15', '2025-09-09 11:02:15'),
(478, 'VVKN', 'NGP', 'CR', 'VIVEKANANDA NAGAR', 1, '2025-09-09 11:02:15', '2025-09-09 11:02:15'),
(479, 'WADI', 'SUR', 'CR', 'Wadi', 1, '2025-09-09 11:02:15', '2025-09-09 11:02:15'),
(480, 'WANI', 'NGP', 'CR', 'Wani', 1, '2025-09-09 11:02:15', '2025-09-09 11:02:15'),
(481, 'WB', 'CSMT', 'CR', 'WADI BANDAR', 1, '2025-09-09 11:02:15', '2025-09-09 11:02:15'),
(482, 'WDSG', 'SUR', 'CR', 'ACC SDG', 1, '2025-09-09 11:02:15', '2025-09-09 11:02:15'),
(483, 'WDSX', 'SUR', 'CR', 'ASSOCIATE CEMENT CO. LTD. SDG, WADI JN. YARD (TXR)', 1, '2025-09-09 11:02:15', '2025-09-09 11:02:15'),
(484, 'WG', 'NGP', 'CR', 'Wagholi', 1, '2025-09-09 11:02:15', '2025-09-09 11:02:15'),
(485, 'WGA', 'BSL', 'CR', 'Waghoda', 1, '2025-09-09 11:02:15', '2025-09-09 11:02:15'),
(486, 'WLGN', 'BSL', 'CR', 'walgaon', 1, '2025-09-09 11:02:15', '2025-09-09 11:02:15'),
(487, 'WLH', 'PUNE', 'CR', 'Valha', 1, '2025-09-09 11:02:15', '2025-09-09 11:02:15'),
(488, 'WNGS', 'NGP', 'CR', 'WANI NEW GOODS SHED', 1, '2025-09-09 11:02:15', '2025-09-09 11:02:15'),
(489, 'WOC', 'NGP', 'CR', 'WARUD ORANGE CITY', 1, '2025-09-09 11:02:15', '2025-09-09 11:02:15'),
(490, 'WR', 'NGP', 'CR', 'Wardha Jn.', 1, '2025-09-09 11:02:15', '2025-09-09 11:02:15'),
(491, 'WRR', 'NGP', 'CR', 'Warora', 1, '2025-09-09 11:02:15', '2025-09-09 11:02:15'),
(492, 'WTR', 'PUNE', 'CR', 'Wathar', 1, '2025-09-09 11:02:15', '2025-09-09 11:02:15'),
(493, 'WTWI', 'PUNE', 'CR', 'WETALWADI', 1, '2025-09-09 11:02:15', '2025-09-09 11:02:15'),
(494, 'X133', 'SUR', 'CR', 'Dhoki', 1, '2025-09-09 11:02:15', '2025-09-09 11:02:15'),
(495, 'YAD', 'BSL', 'CR', 'Yeulkhed', 1, '2025-09-09 11:02:15', '2025-09-09 11:02:15'),
(496, 'YL', 'PUNE', 'CR', 'Yeola', 1, '2025-09-09 11:02:15', '2025-09-09 11:02:15'),
(497, 'YNA', 'NGP', 'CR', 'Yenor', 1, '2025-09-09 11:02:15', '2025-09-09 11:02:15'),
(498, 'YT', 'PUNE', 'CR', 'Yevat', 1, '2025-09-09 11:02:15', '2025-09-09 11:02:15'),
(499, 'ZCT', 'SUR', 'CR', 'Zuari Cement Ltd', 1, '2025-09-09 11:02:15', '2025-09-09 11:02:15');

-- --------------------------------------------------------

--
-- Table structure for table `sla_definitions`
--

CREATE TABLE `sla_definitions` (
  `id` int(11) NOT NULL,
  `priority_level` enum('normal','medium','high','critical') NOT NULL,
  `escalation_hours` int(11) NOT NULL,
  `resolution_hours` int(11) NOT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sla_definitions`
--

INSERT INTO `sla_definitions` (`id`, `priority_level`, `escalation_hours`, `resolution_hours`, `description`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'normal', 24, 72, 'Standard priority tickets - 24 hours to medium, 72 hours target resolution', 1, '2025-09-03 11:51:58', '2025-09-03 11:51:58'),
(2, 'medium', 12, 48, 'Medium priority tickets - escalated after 3 hours from normal, 48 hours target resolution', 1, '2025-09-03 11:51:58', '2025-09-03 11:51:58'),
(3, 'high', 4, 24, 'High priority tickets - escalated after 12 hours from normal, 24 hours target resolution', 1, '2025-09-03 11:51:58', '2025-09-03 11:51:58'),
(4, 'critical', 2, 12, 'Critical priority tickets - escalated after 24 hours from normal, immediate attention required', 1, '2025-09-03 11:51:58', '2025-09-03 11:51:58');

-- --------------------------------------------------------

--
-- Table structure for table `system_cache`
--

CREATE TABLE `system_cache` (
  `id` int(11) NOT NULL,
  `cache_key` varchar(100) NOT NULL,
  `cache_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`cache_data`)),
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='System cache for background refresh and automation';

--
-- Dumping data for table `system_cache`
--

INSERT INTO `system_cache` (`id`, `cache_key`, `cache_data`, `expires_at`, `created_at`, `updated_at`) VALUES
(1, 'last_automation_run', '{\"last_run\":\"2025-09-12 00:01:17\"}', NULL, '2025-09-11 04:58:46', '2025-09-11 18:31:17'),
(2, 'last_heartbeat', '{\"timestamp\":\"2025-09-12 00:01:17\"}', NULL, '2025-09-11 04:58:46', '2025-09-11 18:31:17'),
(3, 'system_stats', '{\"total_active_tickets\":7,\"high_priority_tickets\":0,\"sla_violations\":0,\"updated_at\":\"2025-09-12 00:01:17\"}', NULL, '2025-09-11 04:58:46', '2025-09-11 18:31:17');

-- --------------------------------------------------------

--
-- Table structure for table `system_settings`
--

CREATE TABLE `system_settings` (
  `id` int(11) NOT NULL,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text NOT NULL,
  `setting_type` enum('string','integer','boolean','json','text') DEFAULT 'string',
  `description` text DEFAULT NULL,
  `group_name` varchar(50) DEFAULT 'general',
  `is_editable` tinyint(1) DEFAULT 1,
  `updated_by` int(11) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `system_settings`
--

INSERT INTO `system_settings` (`id`, `setting_key`, `setting_value`, `setting_type`, `description`, `group_name`, `is_editable`, `updated_by`, `updated_at`) VALUES
(1, 'site_name', 'SAMPARK', 'string', 'Website name', 'general', 1, NULL, '2025-09-03 11:51:58'),
(2, 'site_tagline', 'Support and Mediation Portal for All Rail Cargo', 'string', 'Website tagline', 'general', 1, NULL, '2025-09-03 11:51:58'),
(3, 'auto_close_days', '3', 'integer', 'Number of days after which reverted tickets are auto-closed', 'tickets', 1, NULL, '2025-09-03 11:51:58'),
(4, 'max_file_size', '2097152', 'integer', 'Maximum file upload size in bytes (2MB)', 'uploads', 1, NULL, '2025-09-03 11:51:58'),
(5, 'max_files_per_ticket', '3', 'integer', 'Maximum number of files per ticket', 'uploads', 1, NULL, '2025-09-03 11:51:58'),
(6, 'session_timeout', '3600', 'integer', 'Session timeout in seconds (1 hour)', 'security', 1, NULL, '2025-09-03 11:51:58'),
(7, 'enable_sms', '0', 'boolean', 'Enable SMS notifications', 'notifications', 1, NULL, '2025-09-03 11:51:58'),
(8, 'enable_email', '1', 'boolean', 'Enable email notifications', 'notifications', 1, NULL, '2025-09-03 11:51:58');

-- --------------------------------------------------------

--
-- Stand-in structure for view `ticket_summary`
-- (See below for the actual view)
--
CREATE TABLE `ticket_summary` (
`division` varchar(100)
,`zone` varchar(100)
,`status` enum('pending','awaiting_feedback','awaiting_info','awaiting_approval','closed')
,`priority` enum('normal','medium','high','critical')
,`ticket_count` bigint(21)
,`avg_resolution_hours` decimal(24,4)
);

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `transaction_id` int(11) NOT NULL,
  `complaint_id` varchar(20) NOT NULL,
  `remarks` text DEFAULT NULL,
  `internal_remarks` text DEFAULT NULL,
  `transaction_type` enum('created','forwarded','replied','approved','rejected','reverted','closed','escalated','feedback_submitted') NOT NULL,
  `from_user_id` int(11) DEFAULT NULL,
  `to_user_id` int(11) DEFAULT NULL,
  `from_customer_id` varchar(20) DEFAULT NULL,
  `to_customer_id` varchar(20) DEFAULT NULL,
  `from_department` varchar(100) DEFAULT NULL,
  `to_department` varchar(100) DEFAULT NULL,
  `from_division` varchar(100) DEFAULT NULL,
  `to_division` varchar(100) DEFAULT NULL,
  `created_by_id` int(11) DEFAULT NULL,
  `created_by_customer_id` varchar(20) DEFAULT NULL,
  `created_by_type` enum('user','customer') NOT NULL,
  `created_by_role` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `attachment_path` varchar(255) DEFAULT NULL,
  `email_sent` tinyint(1) DEFAULT 0,
  `sms_sent` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `transactions`
--

INSERT INTO `transactions` (`transaction_id`, `complaint_id`, `remarks`, `internal_remarks`, `transaction_type`, `from_user_id`, `to_user_id`, `from_customer_id`, `to_customer_id`, `from_department`, `to_department`, `from_division`, `to_division`, `created_by_id`, `created_by_customer_id`, `created_by_type`, `created_by_role`, `created_at`, `attachment_path`, `email_sent`, `sms_sent`) VALUES
(1, '202509090001', NULL, NULL, 'created', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'CUST2025090001', 'customer', 'customer', '2025-09-09 12:03:03', NULL, 0, 0),
(5, '202509100001', NULL, NULL, 'created', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'CUST2025090001', 'customer', 'customer', '2025-09-10 06:29:14', NULL, 0, 0),
(7, '202509100002', NULL, NULL, 'created', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'CUST2025090001', 'customer', 'customer', '2025-09-10 06:30:21', NULL, 0, 0),
(8, '202509100003', NULL, NULL, 'created', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'CUST2025090001', 'customer', 'customer', '2025-09-10 07:55:20', NULL, 0, 0),
(9, '202509100004', NULL, NULL, 'created', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'CUST2025090001', 'customer', 'customer', '2025-09-10 09:31:35', NULL, 0, 0),
(10, '202509090001', 'Rating: Excellent\nRemarks: Good', NULL, 'feedback_submitted', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'CUST2025090001', 'customer', 'customer', '2025-09-10 10:33:58', NULL, 0, 0),
(11, '202509100005', 'Ticket created by customer', NULL, 'created', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'CUST2025090001', 'customer', 'customer', '2025-09-10 13:30:59', NULL, 0, 0),
(12, '202509100006', 'Ticket created by customer', NULL, 'created', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'CUST2025090001', 'customer', 'customer', '2025-09-10 13:36:15', NULL, 0, 0),
(13, '202509090001', 'Rating: Unsatisfactory\nRemarks: ok', NULL, 'feedback_submitted', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'CUST2025090001', 'customer', 'customer', '2025-09-11 08:14:17', NULL, 0, 0),
(23, '202509100001', 'Ok', NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 'customer', NULL, '2025-09-11 10:08:27', NULL, 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `login_id` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('controller','controller_nodal','admin','superadmin') NOT NULL,
  `department` varchar(100) DEFAULT NULL,
  `division` varchar(100) DEFAULT NULL,
  `zone` varchar(100) DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `mobile` varchar(15) NOT NULL,
  `status` enum('active','inactive','suspended') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_by` int(11) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `login_id`, `password`, `role`, `department`, `division`, `zone`, `name`, `email`, `mobile`, `status`, `created_at`, `created_by`, `updated_at`) VALUES
(1, 'SA001', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'superadmin', 'IT', 'Headquarters', 'All Zones', 'System Administrator', 'admin@sampark.railway.gov.in', '9999999999', 'active', '2025-09-03 11:52:21', 1, '2025-09-03 11:52:21'),
(2, 'AD001', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'Administration', 'Sealdah', 'Eastern', 'Rajesh Kumar', 'admin.sealdah@railway.gov.in', '9876543210', 'active', '2025-09-03 11:52:21', 1, '2025-09-03 11:52:21'),
(3, 'AD002', '$2y$10$lAVN0r4PQ3MexAV3o9lx7ubhPC8UDaTb07h9E4tZ.r.Fu9KdKzZkK', 'admin', 'Administration', 'Mumbai', 'Central', 'Priya Sharma', 'admin.mumbai@railway.gov.in', '9876543211', 'active', '2025-09-03 11:52:21', 1, '2025-09-11 11:31:26'),
(4, 'AD003', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'Administration', 'Delhi', 'Northern', 'Amit Singh', 'admin.delhi@railway.gov.in', '9876543212', 'active', '2025-09-03 11:52:21', 1, '2025-09-03 11:52:21'),
(5, 'CN001', '$2y$10$z1nUw8ZkNql2bMdXOC5xeO7rif9YKepKMs9x9nE/IRz6a1xl4vQKG', 'controller_nodal', 'Commercial', 'CSMT', 'CR', 'Suresh Chandraa', 'commercial.sealdah@railway.gov.in', '9876543220', 'active', '2025-09-03 11:52:21', 1, '2025-09-11 17:54:58'),
(6, 'CN002', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'controller_nodal', 'Commercial', 'Howrah', 'Eastern', 'Meera Patel', 'commercial.howrah@railway.gov.in', '9876543221', 'active', '2025-09-03 11:52:21', 1, '2025-09-03 11:52:21'),
(7, 'CN003', '$2y$10$vk7Sui.A5oRAYH6MFBezlONS.iTf2PrSoh2/jo70zLU788JzvuDzS', 'controller_nodal', 'Commercial', 'CSMT', 'CR', 'Ravi Gupta', 'commercial.mumbai@railway.gov.in', '9876543222', 'active', '2025-09-03 11:52:21', 1, '2025-09-10 13:31:24'),
(8, 'CN004', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'controller_nodal', 'Commercial', 'Delhi', 'Northern', 'Sunita Devi', 'commercial.delhi@railway.gov.in', '9876543223', 'active', '2025-09-03 11:52:21', 1, '2025-09-03 11:52:21'),
(9, 'CN005', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'controller_nodal', 'Commercial', 'Chennai', 'Southern', 'Karthik Raman', 'commercial.chennai@railway.gov.in', '9876543224', 'active', '2025-09-03 11:52:21', 1, '2025-09-03 11:52:21'),
(10, 'CT001', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'controller', 'Mechanical', 'Sealdah', 'Eastern', 'Anand Kumar', 'mechanical.sealdah@railway.gov.in', '9876543230', 'active', '2025-09-03 11:52:21', 1, '2025-09-03 11:52:21'),
(11, 'CT002', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'controller', 'Electrical', 'Sealdah', 'Eastern', 'Deepika Singh', 'electrical.sealdah@railway.gov.in', '9876543231', 'active', '2025-09-03 11:52:21', 1, '2025-09-03 11:52:21'),
(12, 'CT003', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'controller', 'Operating', 'Mumbai', 'Central', 'Vikash Jain', 'operating.mumbai@railway.gov.in', '9876543232', 'active', '2025-09-03 11:52:21', 1, '2025-09-03 11:52:21'),
(13, 'CT004', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'controller', 'Engineering', 'Delhi', 'Northern', 'Pooja Agarwal', 'engineering.delhi@railway.gov.in', '9876543233', 'active', '2025-09-03 11:52:21', 1, '2025-09-03 11:52:21'),
(14, 'CT005', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'controller', 'Security', 'Chennai', 'Southern', 'Ramesh Babu', 'security.chennai@railway.gov.in', '9876543234', 'active', '2025-09-03 11:52:21', 1, '2025-09-03 11:52:21'),
(16, 'CN0032', '$2y$10$HWzpFjlvJ9rjcX4HEZY/Je1CmEoutxLpDKYFvDWfoC10c/jL/AbfS', 'controller_nodal', 'Commercial', 'CSMT', 'CR', 'TEST 2', 'commercial2.mumbai@railway.gov.in', '9876543000', 'active', '2025-09-03 11:52:21', 1, '2025-09-11 04:52:24');

-- --------------------------------------------------------

--
-- Table structure for table `wagon_details`
--

CREATE TABLE `wagon_details` (
  `wagon_id` int(11) NOT NULL,
  `wagon_code` varchar(20) NOT NULL,
  `type` varchar(255) DEFAULT NULL,
  `capacity` decimal(10,2) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `wagon_details`
--

INSERT INTO `wagon_details` (`wagon_id`, `wagon_code`, `type`, `capacity`, `description`, `is_active`, `created_at`, `updated_at`) VALUES
(1011, 'AAA', NULL, NULL, 'AAA', 1, '2025-09-09 11:10:22', '2025-09-09 11:10:22'),
(1012, 'ACT1', NULL, NULL, 'TALLER HEIGHT AUTO CAR1', 1, '2025-09-09 11:10:22', '2025-09-09 11:10:22'),
(1013, 'ACT2A', NULL, NULL, 'TALLER HEIGHT AUTO CAR2A', 1, '2025-09-09 11:10:22', '2025-09-09 11:10:22'),
(1014, 'ACT2B', NULL, NULL, 'TALLER HEIGHT AUTO CAR2B', 1, '2025-09-09 11:10:22', '2025-09-09 11:10:22'),
(1015, 'ACT2C', NULL, NULL, 'TALLER HEIGHT AUTO CAR2C', 1, '2025-09-09 11:10:22', '2025-09-09 11:10:22'),
(1016, 'ACT3A', NULL, NULL, 'TALLER HEIGHT AUTO CAR3A', 1, '2025-09-09 11:10:22', '2025-09-09 11:10:22'),
(1017, 'ACT3B', NULL, NULL, 'TALLER HEIGHT AUTO CAR3B', 1, '2025-09-09 11:10:22', '2025-09-09 11:10:22'),
(1018, 'BC', NULL, NULL, 'BOGIE COVERED WAGON', 1, '2025-09-09 11:10:22', '2025-09-09 11:10:22'),
(1019, 'BCA', NULL, NULL, 'BOGIE COVERED WAGON FOR CATTLE', 1, '2025-09-09 11:10:22', '2025-09-09 11:10:22'),
(1020, 'BCACBMA', NULL, NULL, 'BCACBM-A', 1, '2025-09-09 11:10:22', '2025-09-09 11:10:22'),
(1021, 'BCACBMB', NULL, NULL, 'BCACBM-B', 1, '2025-09-09 11:10:22', '2025-09-09 11:10:22'),
(1022, 'BCACM', NULL, NULL, 'AUTO LOADER VAN', 1, '2025-09-09 11:10:22', '2025-09-09 11:10:22'),
(1023, 'BCBFG', NULL, NULL, 'BOGIE COVERED BROAD GAUGE FOOD GRAIN ADANI', 1, '2025-09-09 11:10:22', '2025-09-09 11:10:22'),
(1024, 'BCCN', NULL, NULL, 'AUTO LOADER VAN', 1, '2025-09-09 11:10:22', '2025-09-09 11:10:22'),
(1025, 'BCCNR', NULL, NULL, 'AUTO LOADER VAN', 1, '2025-09-09 11:10:23', '2025-09-09 11:10:23'),
(1026, 'BCCW', NULL, NULL, 'BOGIE COVERED HOPPER FOR CEMENT', 1, '2025-09-09 11:10:23', '2025-09-09 11:10:23'),
(1027, 'BCCWM', NULL, NULL, 'BOGIE COVERED HOPPER FOR CEMENT', 1, '2025-09-09 11:10:23', '2025-09-09 11:10:23'),
(1028, 'BCDS', NULL, NULL, 'BOGE COVERED AIR BRAKES', 1, '2025-09-09 11:10:23', '2025-09-09 11:10:23'),
(1029, 'BCF', NULL, NULL, 'BCF', 1, '2025-09-09 11:10:23', '2025-09-09 11:10:23'),
(1030, 'BCFC', NULL, NULL, 'BOGIE COCERD BCFC WAGON', 1, '2025-09-09 11:10:23', '2025-09-09 11:10:23'),
(1031, 'BCFCE', NULL, NULL, 'BOGIE COVRD BCFC(DESIGN-E)', 1, '2025-09-09 11:10:23', '2025-09-09 11:10:23'),
(1032, 'BCFCM', NULL, NULL, 'BOGIE COVRD BCFC(MODIFIED)', 1, '2025-09-09 11:10:23', '2025-09-09 11:10:23'),
(1033, 'BCFCM1', NULL, NULL, 'BCFCM1', 1, '2025-09-09 11:10:23', '2025-09-09 11:10:23'),
(1034, 'BCL8AXLE', NULL, NULL, 'BCL(8 AXLE)', 1, '2025-09-09 11:10:23', '2025-09-09 11:10:23'),
(1035, 'BCLA', NULL, NULL, 'BOGE COVERED AIR BRAKES', 1, '2025-09-09 11:10:23', '2025-09-09 11:10:23'),
(1036, 'BCLHTC8AXL', NULL, NULL, 'BCLHTC(8 AXLE)', 1, '2025-09-09 11:10:23', '2025-09-09 11:10:23'),
(1037, 'BCN', NULL, NULL, 'BOGE COVERED AIR BRAKES', 1, '2025-09-09 11:10:23', '2025-09-09 11:10:23'),
(1038, 'BCNA', NULL, NULL, 'BOGIES COVERED AIR BRAKES', 1, '2025-09-09 11:10:23', '2025-09-09 11:10:23'),
(1039, 'BCNAHS', NULL, NULL, 'BOGIE COVERED AIR BRAKE HIGH SPEED', 1, '2025-09-09 11:10:23', '2025-09-09 11:10:23'),
(1040, 'BCNAHSM1', NULL, NULL, 'BOGIE COVERED AIR BRAKES', 1, '2025-09-09 11:10:23', '2025-09-09 11:10:23'),
(1041, 'BCNAM1', NULL, NULL, 'BOGIES COVERED AIR BRAKES', 1, '2025-09-09 11:10:23', '2025-09-09 11:10:23'),
(1042, 'BCNAMI', NULL, NULL, 'BOGIES COVERED AIR BRAKES', 1, '2025-09-09 11:10:23', '2025-09-09 11:10:23'),
(1043, 'BCNHL', NULL, NULL, 'BOGE COVERED AIR BRAKES', 1, '2025-09-09 11:10:23', '2025-09-09 11:10:23'),
(1044, 'BCNHS', NULL, NULL, 'BOGIE COV. AIR BRAK - HI-SPEED', 1, '2025-09-09 11:10:23', '2025-09-09 11:10:23'),
(1045, 'BCNHSM1', NULL, NULL, 'BOGIE COV. AIR BRAK - HI-SPEED', 1, '2025-09-09 11:10:23', '2025-09-09 11:10:23'),
(1046, 'BCNHSM2', NULL, NULL, 'BOGIE COV. AIR BRAK - HI-SPEED', 1, '2025-09-09 11:10:23', '2025-09-09 11:10:23'),
(1047, 'BCNM1', NULL, NULL, 'BOGE COVERED AIR BRAKES', 1, '2025-09-09 11:10:23', '2025-09-09 11:10:23'),
(1048, 'BCNM2', NULL, NULL, 'BOGE COVERED AIR BRAKES', 1, '2025-09-09 11:10:23', '2025-09-09 11:10:23'),
(1049, 'BCNMI', NULL, NULL, 'BOGE COVERED AIR BRAKES', 1, '2025-09-09 11:10:23', '2025-09-09 11:10:23'),
(1050, 'BCR', NULL, NULL, 'BOGIE COVERED WAGON', 1, '2025-09-09 11:10:23', '2025-09-09 11:10:23'),
(1051, 'BCW', NULL, NULL, 'BCW', 1, '2025-09-09 11:10:23', '2025-09-09 11:10:23'),
(1052, 'BCX', NULL, NULL, 'BOGIE COVERED', 1, '2025-09-09 11:10:23', '2025-09-09 11:10:23'),
(1053, 'BCXC', NULL, NULL, 'BOGIE COVERED CBC', 1, '2025-09-09 11:10:23', '2025-09-09 11:10:23'),
(1054, 'BCXN', NULL, NULL, 'BOGE COVERED AIR BRAKES', 1, '2025-09-09 11:10:23', '2025-09-09 11:10:23'),
(1055, 'BCXR', NULL, NULL, 'BOGIE COVERED SCREW', 1, '2025-09-09 11:10:23', '2025-09-09 11:10:23'),
(1056, 'BCXT', NULL, NULL, 'BOGIE COVERED TRANSITION COUPLERS', 1, '2025-09-09 11:10:23', '2025-09-09 11:10:23'),
(1057, 'BCXY', NULL, NULL, 'BOGIE COVERED WAGON UPWARD OPENING DOORS', 1, '2025-09-09 11:10:23', '2025-09-09 11:10:23'),
(1058, 'BFAT', NULL, NULL, 'BOGIE FLAT FOR ARJUN TANK', 1, '2025-09-09 11:10:24', '2025-09-09 11:10:24'),
(1059, 'BFD', NULL, NULL, 'CRANE DUMMY WAGON', 1, '2025-09-09 11:10:24', '2025-09-09 11:10:24'),
(1060, 'BFK', NULL, NULL, 'BOGIE CONTAINER FLAT IRS', 1, '2025-09-09 11:10:24', '2025-09-09 11:10:24'),
(1061, 'BFKHN', NULL, NULL, 'BOGIE FLAT FOR CONTAINERS (HIGH SPEED)', 1, '2025-09-09 11:10:24', '2025-09-09 11:10:24'),
(1062, 'BFKI', NULL, NULL, 'FLAT TO CARRY DOMESTIC ISO FREIGHT CONTAINERS', 1, '2025-09-09 11:10:24', '2025-09-09 11:10:24'),
(1063, 'BFKN', NULL, NULL, 'BOGIE FLAT CONTAINER', 1, '2025-09-09 11:10:24', '2025-09-09 11:10:24'),
(1064, 'BFKX', NULL, NULL, 'FLATS TO CARRY DOMESTIC FRIGHT CONTAINERS', 1, '2025-09-09 11:10:24', '2025-09-09 11:10:24'),
(1065, 'BFNS', NULL, NULL, 'BOGIE FLAT HIGH SPEED', 1, '2025-09-09 11:10:24', '2025-09-09 11:10:24'),
(1066, 'BFNS22.9', NULL, NULL, 'BFNS 22.9 WAGON', 1, '2025-09-09 11:10:24', '2025-09-09 11:10:24'),
(1067, 'BFNSM', NULL, NULL, 'BFNSM 22.9 WAGON', 1, '2025-09-09 11:10:24', '2025-09-09 11:10:24'),
(1068, 'BFNSM1', NULL, NULL, 'BFNS 22.9 WAGON', 1, '2025-09-09 11:10:24', '2025-09-09 11:10:24'),
(1069, 'BFNV', NULL, NULL, 'BOGIE FLATE STEEL WAGON', 1, '2025-09-09 11:10:24', '2025-09-09 11:10:24'),
(1070, 'BFR', NULL, NULL, 'BOGIE FLAT FOR RAILS', 1, '2025-09-09 11:10:24', '2025-09-09 11:10:24'),
(1071, 'BFRF', NULL, NULL, 'BFRF', 1, '2025-09-09 11:10:24', '2025-09-09 11:10:24'),
(1072, 'BFT', NULL, NULL, 'BOGIE FLAT FOR TIMBER', 1, '2025-09-09 11:10:24', '2025-09-09 11:10:24'),
(1073, 'BFU', NULL, NULL, 'BOGIE WELL WAGON', 1, '2025-09-09 11:10:24', '2025-09-09 11:10:24'),
(1074, 'BFUF', NULL, NULL, 'BOGIE WELL WAGON', 1, '2025-09-09 11:10:24', '2025-09-09 11:10:24'),
(1075, 'BFWP', NULL, NULL, 'BOGIE RAIL TRUCK AIR-BRAKE', 1, '2025-09-09 11:10:24', '2025-09-09 11:10:24'),
(1076, 'BG', NULL, NULL, 'BG', 1, '2025-09-09 11:10:24', '2025-09-09 11:10:24'),
(1077, 'BK', NULL, NULL, 'BK', 1, '2025-09-09 11:10:24', '2025-09-09 11:10:24'),
(1078, 'BKC', NULL, NULL, 'BOGIE OPEN WAGON HIGH SPEED', 1, '2025-09-09 11:10:24', '2025-09-09 11:10:24'),
(1079, 'BKCA', NULL, NULL, 'BOGIE OPEN WAGON HIGH SIDED', 1, '2025-09-09 11:10:24', '2025-09-09 11:10:24'),
(1080, 'BKCC', NULL, NULL, 'BOGIE OPEN WAGON HIGH SIDED', 1, '2025-09-09 11:10:24', '2025-09-09 11:10:24'),
(1081, 'BKCF', NULL, NULL, 'BKCF', 1, '2025-09-09 11:10:24', '2025-09-09 11:10:24'),
(1082, 'BKCW', NULL, NULL, 'BKCW', 1, '2025-09-09 11:10:24', '2025-09-09 11:10:24'),
(1083, 'BKCX', NULL, NULL, 'BOGIE OPEN WAGON', 1, '2025-09-09 11:10:24', '2025-09-09 11:10:24'),
(1084, 'BKCXY', NULL, NULL, 'BOX WAGONS WITH SEALED UPWARD OPENING DOOR', 1, '2025-09-09 11:10:24', '2025-09-09 11:10:24'),
(1085, 'BKD', NULL, NULL, 'BOGIE OPEN WITH MEDIUM HEIGHT', 1, '2025-09-09 11:10:24', '2025-09-09 11:10:24'),
(1086, 'BKDF', NULL, NULL, 'BKDF', 1, '2025-09-09 11:10:24', '2025-09-09 11:10:24'),
(1087, 'BKE', NULL, NULL, 'BOGIE OPEN GENERAL / ELEPHANT', 1, '2025-09-09 11:10:24', '2025-09-09 11:10:24'),
(1088, 'BKF', NULL, NULL, 'BOGIE OPEN END OPENING', 1, '2025-09-09 11:10:24', '2025-09-09 11:10:24'),
(1089, 'BKH', NULL, NULL, 'BOGIE OPEN HOPPER', 1, '2025-09-09 11:10:25', '2025-09-09 11:10:25'),
(1090, 'BKHF', NULL, NULL, 'BOGIE OPEN HOPPER', 1, '2025-09-09 11:10:25', '2025-09-09 11:10:25'),
(1091, 'BKHN', NULL, NULL, 'BOGIE OPEN HOPPER', 1, '2025-09-09 11:10:25', '2025-09-09 11:10:25'),
(1092, 'BKI', NULL, NULL, 'BOGIE FLAT FOR CONTAINERS FLATS MODIFIED', 1, '2025-09-09 11:10:25', '2025-09-09 11:10:25'),
(1093, 'BKK', NULL, NULL, 'BKK', 1, '2025-09-09 11:10:25', '2025-09-09 11:10:25'),
(1094, 'BKL', NULL, NULL, 'BOGIE OPEN LOW SIDED', 1, '2025-09-09 11:10:25', '2025-09-09 11:10:25'),
(1095, 'BKM', NULL, NULL, 'BOGIE OPEN WAGON FOR MILITARY', 1, '2025-09-09 11:10:25', '2025-09-09 11:10:25'),
(1096, 'BKU', NULL, NULL, 'BOGIE OPEN PLATFORM WAGONS', 1, '2025-09-09 11:10:25', '2025-09-09 11:10:25'),
(1097, 'BKW', NULL, NULL, 'BOGIE OPEN WAGON BALLAST', 1, '2025-09-09 11:10:25', '2025-09-09 11:10:25'),
(1098, 'BLCSA', NULL, NULL, 'BOGIE CONTAINER FLAT WAGON BLCS(A-CAR)', 1, '2025-09-09 11:10:25', '2025-09-09 11:10:25'),
(1099, 'BLCSB', NULL, NULL, 'BOGIE CONTAINER FLAT WAGON BLCS(B-CAR)', 1, '2025-09-09 11:10:25', '2025-09-09 11:10:25'),
(1100, 'BLLMA', NULL, NULL, 'BOGIE FLAT FOR MODIFIED CONTAINER LONG CAR A', 1, '2025-09-09 11:10:25', '2025-09-09 11:10:25'),
(1101, 'BLLMB', NULL, NULL, 'BOGIE FLAT FOR MODIFIED CONTAINER LONG CAR B', 1, '2025-09-09 11:10:25', '2025-09-09 11:10:25'),
(1102, 'BLSSA', NULL, NULL, 'BLSSA', 1, '2025-09-09 11:10:25', '2025-09-09 11:10:25'),
(1103, 'BLSSB', NULL, NULL, 'BLSSB', 1, '2025-09-09 11:10:25', '2025-09-09 11:10:25'),
(1104, 'BMKM', NULL, NULL, 'FLAT/WAGON FOR CARRYING MILITARY VEHICLES', 1, '2025-09-09 11:10:25', '2025-09-09 11:10:25'),
(1105, 'BNE', NULL, NULL, 'BOGIE STORE VAN', 1, '2025-09-09 11:10:25', '2025-09-09 11:10:25'),
(1106, 'BOB', NULL, NULL, 'OPEN WAGON HOPPER', 1, '2025-09-09 11:10:25', '2025-09-09 11:10:25'),
(1107, 'BOBC', NULL, NULL, 'OPEN WAGON HOPPER WITH CENTRE DISCHARGE ARRANGEMENTS', 1, '2025-09-09 11:10:25', '2025-09-09 11:10:25'),
(1108, 'BOBR', NULL, NULL, 'BOGIE HOPPER RAPID DISCHARGE (VAC)', 1, '2025-09-09 11:10:25', '2025-09-09 11:10:25'),
(1109, 'BOBRM1', NULL, NULL, 'BOGIE HOPPER RAPID DISCHARGE (VAC)', 1, '2025-09-09 11:10:25', '2025-09-09 11:10:25'),
(1110, 'BOBRN', NULL, NULL, 'BOGIE HOPPER RAPID DISCHARGE (AIR)', 1, '2025-09-09 11:10:25', '2025-09-09 11:10:25'),
(1111, 'BOBRNAHSM1', NULL, NULL, 'BOGIE HOPPER RAPID DISCHARGE HIGH SPEED', 1, '2025-09-09 11:10:25', '2025-09-09 11:10:25'),
(1112, 'BOBRNEL', NULL, NULL, 'BOGIE HOPPER RAPID DISCHARGE (AIR)', 1, '2025-09-09 11:10:25', '2025-09-09 11:10:25'),
(1113, 'BOBRNHS', NULL, NULL, 'BOGIE HOPPER RAPID DISCHARGE HIGH SPEED', 1, '2025-09-09 11:10:25', '2025-09-09 11:10:25'),
(1114, 'BOBRNHSM1', NULL, NULL, 'BOGIE HOPPER RAPID DISCHARGE HIGH SPEED', 1, '2025-09-09 11:10:25', '2025-09-09 11:10:25'),
(1115, 'BOBRNHSM2', NULL, NULL, 'BOGIE HOPPER RAPID DISCHARGE HIGH SPEED', 1, '2025-09-09 11:10:25', '2025-09-09 11:10:25'),
(1116, 'BOBRNM1', NULL, NULL, 'BOGIE HOPPER RAPID DISCHARGE (AIR)', 1, '2025-09-09 11:10:25', '2025-09-09 11:10:25'),
(1117, 'BOBRNM2', NULL, NULL, 'BOBRNM2', 1, '2025-09-09 11:10:25', '2025-09-09 11:10:25'),
(1118, 'BOBRNSHM1', NULL, NULL, 'BOGIE OPEN RAPID DISCHARGE HOPPER WAGON', 1, '2025-09-09 11:10:25', '2025-09-09 11:10:25'),
(1119, 'BOBS', NULL, NULL, 'OPEN WAGON HOPPER WITH SIDE DISCHARGE', 1, '2025-09-09 11:10:25', '2025-09-09 11:10:25'),
(1120, 'BOBSN', NULL, NULL, 'OPEN WAGON HOPPER WITH SIDE DISCHARGE AIR BRAKE', 1, '2025-09-09 11:10:26', '2025-09-09 11:10:26'),
(1121, 'BOBSNM', NULL, NULL, 'BOBSNMI LOADED WITH IORE ON MXA-DRZ', 1, '2025-09-09 11:10:26', '2025-09-09 11:10:26'),
(1122, 'BOBSNM1', NULL, NULL, 'BOBSNMI LOADED WITH IORE ON MXA-DRZ', 1, '2025-09-09 11:10:26', '2025-09-09 11:10:26'),
(1123, 'BOBSNS', NULL, NULL, 'OPEN WAGON HOPPER WITH SIDE DISCHARGE AIR BRAKE', 1, '2025-09-09 11:10:26', '2025-09-09 11:10:26'),
(1124, 'BOBX', NULL, NULL, 'OPEN WAGON HOPPER WITH CENTRE AND SIDE DISCHARGE ARRANGEMENT', 1, '2025-09-09 11:10:26', '2025-09-09 11:10:26'),
(1125, 'BOBY', NULL, NULL, 'HOPPER WAGON BOTTOM DISCHARGE', 1, '2025-09-09 11:10:26', '2025-09-09 11:10:26'),
(1126, 'BOBYN', NULL, NULL, 'HOPPER WAGON BOTTOM DISCHARGE AIR BRAKE', 1, '2025-09-09 11:10:26', '2025-09-09 11:10:26'),
(1127, 'BOBYN22.9', NULL, NULL, 'HOPPER WGN BOTTOM DISCRG(HIGH SPEED)', 1, '2025-09-09 11:10:26', '2025-09-09 11:10:26'),
(1128, 'BOBYNH', NULL, NULL, 'HOPPER WAGON BOTTOM DISCHARGE HIGH SPEED', 1, '2025-09-09 11:10:26', '2025-09-09 11:10:26'),
(1129, 'BOBYNHS', NULL, NULL, 'HOPPER WGN BOTTOM DISCRG(HIGH SPEED)', 1, '2025-09-09 11:10:26', '2025-09-09 11:10:26'),
(1130, 'BOBYNHSM1', NULL, NULL, 'HOPPER WGN BOTTOM DISCRG(HIGH SPEED)', 1, '2025-09-09 11:10:26', '2025-09-09 11:10:26'),
(1131, 'BOBYNM1', NULL, NULL, 'HOPPER WAGON BOTTOM DISCHARGE AIR BRAKE', 1, '2025-09-09 11:10:26', '2025-09-09 11:10:26'),
(1132, 'BOI', NULL, NULL, 'OPEN WAGON GONDOLA', 1, '2025-09-09 11:10:26', '2025-09-09 11:10:26'),
(1133, 'BOIN', NULL, NULL, 'BOGIE OPEN WAGON GONDOLA', 1, '2025-09-09 11:10:26', '2025-09-09 11:10:26'),
(1134, 'BOM', NULL, NULL, 'BOGIE OPEN MILITARY WAGON', 1, '2025-09-09 11:10:26', '2025-09-09 11:10:26'),
(1135, 'BOMN', NULL, NULL, 'BOGIE OPEN MILITARY AIRBRAKE WAGON', 1, '2025-09-09 11:10:26', '2025-09-09 11:10:26'),
(1136, 'BOSM', NULL, NULL, 'BOGIE OPEN STEEL WGON', 1, '2025-09-09 11:10:26', '2025-09-09 11:10:26'),
(1137, 'BOST', NULL, NULL, 'BOGIE OPEN AIR-BRAK', 1, '2025-09-09 11:10:26', '2025-09-09 11:10:26'),
(1138, 'BOSTHS', NULL, NULL, 'BOGIE OPEN AIR-BRAK', 1, '2025-09-09 11:10:26', '2025-09-09 11:10:26'),
(1139, 'BOSTHSM1', NULL, NULL, 'BOGIE OPEN AIR-BRAK', 1, '2025-09-09 11:10:26', '2025-09-09 11:10:26'),
(1140, 'BOSTHSM2', NULL, NULL, 'BOGIE OPEN AIR-BRAK', 1, '2025-09-09 11:10:26', '2025-09-09 11:10:26'),
(1141, 'BOSTHSM3', NULL, NULL, 'BOGIE OPEN AIR-BRAK', 1, '2025-09-09 11:10:26', '2025-09-09 11:10:26'),
(1142, 'BOSTM1', NULL, NULL, 'BOGIE OPEN AIR-BRAK', 1, '2025-09-09 11:10:26', '2025-09-09 11:10:26'),
(1143, 'BOX', NULL, NULL, 'BOGIE OPEN WAGON', 1, '2025-09-09 11:10:26', '2025-09-09 11:10:26'),
(1144, 'BOXC', NULL, NULL, 'BOX WAGONS WITH CBC', 1, '2025-09-09 11:10:26', '2025-09-09 11:10:26'),
(1145, 'BOXCY', NULL, NULL, 'BOX WAGONS WITH SEALED UPWARD OPENING DOORS', 1, '2025-09-09 11:10:26', '2025-09-09 11:10:26'),
(1146, 'BOXK', NULL, NULL, 'BOGIE OPEN CONTAINER', 1, '2025-09-09 11:10:26', '2025-09-09 11:10:26'),
(1147, 'BOXKH', NULL, NULL, 'BOGIE OPEN CONTAINER (HEAVY)', 1, '2025-09-09 11:10:26', '2025-09-09 11:10:26'),
(1148, 'BOXN', NULL, NULL, 'BOGIE OPEN WAGON AIR BRAKES', 1, '2025-09-09 11:10:27', '2025-09-09 11:10:27'),
(1149, 'BOXNAL', NULL, NULL, 'BOXNAL', 1, '2025-09-09 11:10:27', '2025-09-09 11:10:27'),
(1150, 'BOXNB', NULL, NULL, 'BOXNB', 1, '2025-09-09 11:10:27', '2025-09-09 11:10:27'),
(1151, 'BOXNCR', NULL, NULL, 'BOGIE OPEN WAGON AIR BRAKE CORROSION RESISTANT', 1, '2025-09-09 11:10:27', '2025-09-09 11:10:27'),
(1152, 'BOXNCRM1', NULL, NULL, 'BOGIE OPEN WAGON AIR BRAKE CORROSION RESISTANT', 1, '2025-09-09 11:10:27', '2025-09-09 11:10:27'),
(1153, 'BOXNEL', NULL, NULL, 'BOXNEL LOADED WITH IORE ON BSPX-DATR-JKPR-PRDP', 1, '2025-09-09 11:10:27', '2025-09-09 11:10:27'),
(1154, 'BOXNF', NULL, NULL, 'BOXNF', 1, '2025-09-09 11:10:27', '2025-09-09 11:10:27'),
(1155, 'BOXNG', NULL, NULL, 'BOXNG', 1, '2025-09-09 11:10:27', '2025-09-09 11:10:27'),
(1156, 'BOXNHA', NULL, NULL, 'BOX WAGON (HIGH SPEED)', 1, '2025-09-09 11:10:27', '2025-09-09 11:10:27'),
(1157, 'BOXNHAM', NULL, NULL, 'BOX WAGON (HIGH SPEED)', 1, '2025-09-09 11:10:27', '2025-09-09 11:10:27'),
(1158, 'BOXNHL', NULL, NULL, 'BOGIE OPEN WAGON AIR BRAKES', 1, '2025-09-09 11:10:27', '2025-09-09 11:10:27'),
(1159, 'BOXNHL25T', NULL, NULL, 'BOGIE OPEN WAGON AIR BRAKES 25T', 1, '2025-09-09 11:10:27', '2025-09-09 11:10:27'),
(1160, 'BOXNHS', NULL, NULL, 'BOGIE OPEN AIR-BRAK - HI-SPEED', 1, '2025-09-09 11:10:27', '2025-09-09 11:10:27'),
(1161, 'BOXNHSM1', NULL, NULL, 'BOGIE OPEN AIR-BRAK - HI-SPEED', 1, '2025-09-09 11:10:27', '2025-09-09 11:10:27'),
(1162, 'BOXNHSM2', NULL, NULL, 'BOGIE OPEN AIR-BRAK - HI-SPEED', 1, '2025-09-09 11:10:27', '2025-09-09 11:10:27'),
(1163, 'BOXNLW', NULL, NULL, 'BOGIE OPEN WAGON AIR BRAKES', 1, '2025-09-09 11:10:27', '2025-09-09 11:10:27'),
(1164, 'BOXNLWM1', NULL, NULL, 'BOGIE OPEN WAGON AIR BRAKES', 1, '2025-09-09 11:10:27', '2025-09-09 11:10:27'),
(1165, 'BOXNLWM2', NULL, NULL, 'BOGIE OPEN WAGON AIR BRAKES', 1, '2025-09-09 11:10:27', '2025-09-09 11:10:27'),
(1166, 'BOXNM1', NULL, NULL, 'BOGIE OPEN WAGON AIR BRAKES LIGHT WEIGHT MODIFIED 1', 1, '2025-09-09 11:10:27', '2025-09-09 11:10:27'),
(1167, 'BOXNM2', NULL, NULL, 'BOGIE OPEN WAGON AIR BRAKES LIGHT WEIGHT MODIFIED 1', 1, '2025-09-09 11:10:27', '2025-09-09 11:10:27'),
(1168, 'BOXNR', NULL, NULL, 'BOGIE OPEN WAGON AIR BRAKES-REHABILITATION', 1, '2025-09-09 11:10:27', '2025-09-09 11:10:27'),
(1169, 'BOXNRHS', NULL, NULL, 'BOGIE OPEN WAGON AIR BRAKES-REHABILITATION', 1, '2025-09-09 11:10:27', '2025-09-09 11:10:27'),
(1170, 'BOXNRHSM1', NULL, NULL, 'BOGIE OPEN WAGON AIR BRAKES-REHABILITATION', 1, '2025-09-09 11:10:27', '2025-09-09 11:10:27'),
(1171, 'BOXNRM1', NULL, NULL, 'BOGIE OPEN WAGON AIR BRAKES-REHABILITATION', 1, '2025-09-09 11:10:27', '2025-09-09 11:10:27'),
(1172, 'BOXNRM2', NULL, NULL, 'BOGIE OPEN WAGON AIR BRAKES-REHABILITATION', 1, '2025-09-09 11:10:28', '2025-09-09 11:10:28'),
(1173, 'BOXNS', NULL, NULL, 'BOGIE OPEN WAGON (SWING CUM FLAP DOORS SLIDING ROOF)', 1, '2025-09-09 11:10:28', '2025-09-09 11:10:28'),
(1174, 'BOXNUG', NULL, NULL, 'BOGIE OPEN WAGON AIR BRAKES', 1, '2025-09-09 11:10:28', '2025-09-09 11:10:28'),
(1175, 'BOXR', NULL, NULL, 'BOX WAGONS SCREW COUPLINGS', 1, '2025-09-09 11:10:28', '2025-09-09 11:10:28'),
(1176, 'BOXRY', NULL, NULL, 'BOX WAGONS SEALED UPWARD OPENING DOORS SCREW COUPLINGS', 1, '2025-09-09 11:10:28', '2025-09-09 11:10:28'),
(1177, 'BOXS', NULL, NULL, 'BOGIE OPEN WAGON (SWING CUM FLAP DOORS SLIDING ROOF)', 1, '2025-09-09 11:10:28', '2025-09-09 11:10:28'),
(1178, 'BOXSR', NULL, NULL, 'BOGIE OPEN WAGON WITH SLIDING ROOF', 1, '2025-09-09 11:10:28', '2025-09-09 11:10:28'),
(1179, 'BOXT', NULL, NULL, 'BOX WAGONS WITH SWING CUM FLAP DOORS-TRANSITION', 1, '2025-09-09 11:10:28', '2025-09-09 11:10:28'),
(1180, 'BOXTY', NULL, NULL, 'BOX WAGONS UPWARD OPENING DOORS', 1, '2025-09-09 11:10:28', '2025-09-09 11:10:28'),
(1181, 'BOXY', NULL, NULL, 'BOX WAGON SEALED UPWARD OPENING DOOR', 1, '2025-09-09 11:10:28', '2025-09-09 11:10:28'),
(1182, 'BOY', NULL, NULL, 'BOGIE OPEN FOR IRON ORE', 1, '2025-09-09 11:10:28', '2025-09-09 11:10:28'),
(1183, 'BOYEL', NULL, NULL, 'BOYEL LOADED WITH IORE ON KRDL-KTV-VZP', 1, '2025-09-09 11:10:28', '2025-09-09 11:10:28'),
(1184, 'BOYN', NULL, NULL, 'BOGIE OPEN FOR IRON ORE(AIR BRAKE)', 1, '2025-09-09 11:10:28', '2025-09-09 11:10:28'),
(1185, 'BRH', NULL, NULL, 'BOGIE RAIL TRUCK', 1, '2025-09-09 11:10:28', '2025-09-09 11:10:28'),
(1186, 'BRHC', NULL, NULL, 'BOGIE RAIL WAGON', 1, '2025-09-09 11:10:28', '2025-09-09 11:10:28'),
(1187, 'BRHN', NULL, NULL, 'BOGIE RAIL TRUCK', 1, '2025-09-09 11:10:28', '2025-09-09 11:10:28'),
(1188, 'BRHNEHS', NULL, NULL, 'BOGIE RAIL TRUCK', 1, '2025-09-09 11:10:28', '2025-09-09 11:10:28'),
(1189, 'BRHNEHSM1', NULL, NULL, 'BRHNEHSM1', 1, '2025-09-09 11:10:28', '2025-09-09 11:10:28'),
(1190, 'BRHT', NULL, NULL, 'BOGIE RAIL WAGON', 1, '2025-09-09 11:10:28', '2025-09-09 11:10:28'),
(1191, 'BRN', NULL, NULL, 'BOGIE RAIL TRUCK AIR-BRAKE', 1, '2025-09-09 11:10:28', '2025-09-09 11:10:28'),
(1192, 'BRN22.9', NULL, NULL, 'BRN22.9', 1, '2025-09-09 11:10:28', '2025-09-09 11:10:28'),
(1193, 'BRN22.9M1', NULL, NULL, 'BRN22.9', 1, '2025-09-09 11:10:28', '2025-09-09 11:10:28'),
(1194, 'BRNA', NULL, NULL, 'BOGIE RAIL TRUCK AIR', 1, '2025-09-09 11:10:28', '2025-09-09 11:10:28'),
(1195, 'BRNAHA', NULL, NULL, 'BRNAHA', 1, '2025-09-09 11:10:28', '2025-09-09 11:10:28'),
(1196, 'BRNAHS', NULL, NULL, 'BOGIE RAIL TRUCK AIR HI-SPEED', 1, '2025-09-09 11:10:28', '2025-09-09 11:10:28'),
(1197, 'BRNAHSHA', NULL, NULL, 'BRNAHSHA', 1, '2025-09-09 11:10:28', '2025-09-09 11:10:28'),
(1198, 'BRNAM1', NULL, NULL, 'BRNAM1', 1, '2025-09-09 11:10:28', '2025-09-09 11:10:28'),
(1199, 'BRNHA', NULL, NULL, 'BRNHA', 1, '2025-09-09 11:10:28', '2025-09-09 11:10:28'),
(1200, 'BRNM1', NULL, NULL, 'BRNM1', 1, '2025-09-09 11:10:28', '2025-09-09 11:10:28'),
(1201, 'BRS', NULL, NULL, 'BOGIE RAIL TRUCK', 1, '2025-09-09 11:10:29', '2025-09-09 11:10:29'),
(1202, 'BRST', NULL, NULL, 'BOGIE RAIL TRUCK-TRANSITION', 1, '2025-09-09 11:10:29', '2025-09-09 11:10:29'),
(1203, 'BRSTN', NULL, NULL, 'BRSTN', 1, '2025-09-09 11:10:29', '2025-09-09 11:10:29'),
(1204, 'BRT', NULL, NULL, 'BOGIE RAIL TRUCK', 1, '2025-09-09 11:10:29', '2025-09-09 11:10:29'),
(1205, 'BTA', NULL, NULL, 'BOGIE TANK WAGON ACID', 1, '2025-09-09 11:10:29', '2025-09-09 11:10:29'),
(1206, 'BTAL', NULL, NULL, 'BOGIE TANK WAGON AMMONIA', 1, '2025-09-09 11:10:29', '2025-09-09 11:10:29'),
(1207, 'BTALN', NULL, NULL, 'BOGIE TANK WAGON FOR LIQUID AMMONIA', 1, '2025-09-09 11:10:29', '2025-09-09 11:10:29'),
(1208, 'BTAP', NULL, NULL, 'TANK WAGON FOR AMMONIA ,ALUMINA', 1, '2025-09-09 11:10:29', '2025-09-09 11:10:29'),
(1209, 'BTAPHP', NULL, NULL, 'BOGIE TANK WAGON ALUMINA POWDER', 1, '2025-09-09 11:10:29', '2025-09-09 11:10:29'),
(1210, 'BTCS', NULL, NULL, 'BOGIE C. SODA TANK IRS', 1, '2025-09-09 11:10:29', '2025-09-09 11:10:29'),
(1211, 'BTE', NULL, NULL, 'BOGIE TANK WAGON LIQUID CAUSTIC SODA', 1, '2025-09-09 11:10:29', '2025-09-09 11:10:29'),
(1212, 'BTF', NULL, NULL, 'TANK WAGON AMMONIA', 1, '2025-09-09 11:10:29', '2025-09-09 11:10:29'),
(1213, 'BTFC', NULL, NULL, 'TANK WAGON FOR AMMONIA ,ALUMINA', 1, '2025-09-09 11:10:29', '2025-09-09 11:10:29'),
(1214, 'BTFLN', NULL, NULL, 'TANK WAGON FOR POL', 1, '2025-09-09 11:10:29', '2025-09-09 11:10:29'),
(1215, 'BTFN', NULL, NULL, 'BOGIE TANK AMMONIA', 1, '2025-09-09 11:10:29', '2025-09-09 11:10:29'),
(1216, 'BTHA', NULL, NULL, 'BOGIE TANK WAGON HYDROCHLORIC ACID', 1, '2025-09-09 11:10:29', '2025-09-09 11:10:29'),
(1217, 'BTK', NULL, NULL, 'BOGIE TANK WAGON KEROSENE', 1, '2025-09-09 11:10:29', '2025-09-09 11:10:29'),
(1218, 'BTL', NULL, NULL, 'BOGIE TANK WAGON KEROSENE', 1, '2025-09-09 11:10:29', '2025-09-09 11:10:29'),
(1219, 'BTM', NULL, NULL, 'BOGIETANK WAGON MOLASSES', 1, '2025-09-09 11:10:29', '2025-09-09 11:10:29'),
(1220, 'BTO', NULL, NULL, 'BTO', 1, '2025-09-09 11:10:29', '2025-09-09 11:10:29'),
(1221, 'BTOH', NULL, NULL, 'BOGIE TANK WAGON HEAVY OIL', 1, '2025-09-09 11:10:29', '2025-09-09 11:10:29'),
(1222, 'BTP', NULL, NULL, 'BOGIE TANK WAGON PETROL', 1, '2025-09-09 11:10:29', '2025-09-09 11:10:29'),
(1223, 'BTPA', NULL, NULL, 'BOGIE TANK WAGON FOR ALUMINA', 1, '2025-09-09 11:10:29', '2025-09-09 11:10:29'),
(1224, 'BTPG', NULL, NULL, 'BOGIE TANK WAGON FOR LPG', 1, '2025-09-09 11:10:29', '2025-09-09 11:10:29'),
(1225, 'BTPGL', NULL, NULL, 'BOGIE LPG TANK', 1, '2025-09-09 11:10:29', '2025-09-09 11:10:29'),
(1226, 'BTPGLN', NULL, NULL, 'BOGIE TANK WAGON FOR LPG', 1, '2025-09-09 11:10:29', '2025-09-09 11:10:29'),
(1227, 'BTPGN', NULL, NULL, 'BOGIE TANK WAGON FOR LIQUAFIED PETROLIUM GAS', 1, '2025-09-09 11:10:29', '2025-09-09 11:10:29'),
(1228, 'BTPH', NULL, NULL, 'BOGIE TANK WAGON PHOSPHORIC ACID', 1, '2025-09-09 11:10:29', '2025-09-09 11:10:29'),
(1229, 'BTPN', NULL, NULL, 'BOGIE TANK WAGON AIR BRAKE', 1, '2025-09-09 11:10:29', '2025-09-09 11:10:29'),
(1230, 'BTPX', NULL, NULL, 'BOGIE TANK WGON PETROL', 1, '2025-09-09 11:10:29', '2025-09-09 11:10:29'),
(1231, 'BTS', NULL, NULL, 'BTS', 1, '2025-09-09 11:10:30', '2025-09-09 11:10:30'),
(1232, 'BTSA', NULL, NULL, 'TANK WAGON SULPHURIC ACID', 1, '2025-09-09 11:10:30', '2025-09-09 11:10:30'),
(1233, 'BTV', NULL, NULL, 'BOGIE TANK WAGON VEGETABLE OIL', 1, '2025-09-09 11:10:30', '2025-09-09 11:10:30'),
(1234, 'BTW', NULL, NULL, 'BOGIE TANK WAGON WATER', 1, '2025-09-09 11:10:30', '2025-09-09 11:10:30'),
(1235, 'BV', NULL, NULL, 'BRAKE VAN', 1, '2025-09-09 11:10:30', '2025-09-09 11:10:30'),
(1236, 'BVCM', NULL, NULL, 'BRAKE VAN', 1, '2025-09-09 11:10:30', '2025-09-09 11:10:30'),
(1237, 'BVG', NULL, NULL, 'BRAKE VAN', 1, '2025-09-09 11:10:30', '2025-09-09 11:10:30'),
(1238, 'BVGC', NULL, NULL, 'BRAKE VAN. CBC', 1, '2025-09-09 11:10:30', '2025-09-09 11:10:30'),
(1239, 'BVGT', NULL, NULL, 'BRAKE VAN. TRANSITION CBC', 1, '2025-09-09 11:10:30', '2025-09-09 11:10:30'),
(1240, 'BVZC', NULL, NULL, 'GOODS BRAKE VAN (AIR BRAKE SYSTEM)', 1, '2025-09-09 11:10:30', '2025-09-09 11:10:30'),
(1241, 'BVZI', NULL, NULL, 'BRAKE VAN', 1, '2025-09-09 11:10:30', '2025-09-09 11:10:30'),
(1242, 'BWE', NULL, NULL, '12 AXLE 150T SPECIAL WELL WAGON', 1, '2025-09-09 11:10:30', '2025-09-09 11:10:30'),
(1243, 'BWH', NULL, NULL, 'BOGIE WELL WAGON', 1, '2025-09-09 11:10:30', '2025-09-09 11:10:30'),
(1244, 'BWL', NULL, NULL, 'BOGIE WELL WAGON', 1, '2025-09-09 11:10:30', '2025-09-09 11:10:30'),
(1245, 'BWS', NULL, NULL, 'BOGIE WELL WAGON GROSS LOAD 132.08 TONNES', 1, '2025-09-09 11:10:30', '2025-09-09 11:10:30'),
(1246, 'BWT', NULL, NULL, 'BOGIE WELL WAGON GROSS LOAD 55.88 TON', 1, '2025-09-09 11:10:30', '2025-09-09 11:10:30'),
(1247, 'BWTA', NULL, NULL, 'BWTA', 1, '2025-09-09 11:10:30', '2025-09-09 11:10:30'),
(1248, 'BWTB', NULL, NULL, 'BG BOGIE WELL WAGON TYPE', 1, '2025-09-09 11:10:30', '2025-09-09 11:10:30'),
(1249, 'BWW', NULL, NULL, 'BOGIE WELL WAGON', 1, '2025-09-09 11:10:30', '2025-09-09 11:10:30'),
(1250, 'BWZ', NULL, NULL, 'BOGIE WELL SPL. 12 AXL 182', 1, '2025-09-09 11:10:30', '2025-09-09 11:10:30'),
(1251, 'BXC', NULL, NULL, 'BXC', 1, '2025-09-09 11:10:30', '2025-09-09 11:10:30'),
(1252, 'C', NULL, NULL, 'COVERED WAGON GENERAL', 1, '2025-09-09 11:10:30', '2025-09-09 11:10:30'),
(1253, 'CA', NULL, NULL, '4 WHEELER COVERED. CATTLE', 1, '2025-09-09 11:10:30', '2025-09-09 11:10:30'),
(1254, 'CAF', NULL, NULL, '4 WHEELER COVERED. CATTLE', 1, '2025-09-09 11:10:30', '2025-09-09 11:10:30'),
(1255, 'CAW', NULL, NULL, 'CAW', 1, '2025-09-09 11:10:30', '2025-09-09 11:10:30'),
(1256, 'CC', NULL, NULL, 'CC', 1, '2025-09-09 11:10:30', '2025-09-09 11:10:30'),
(1257, 'CJ', NULL, NULL, 'COVERED WAGON FOR JUTE', 1, '2025-09-09 11:10:30', '2025-09-09 11:10:30'),
(1258, 'CORA', NULL, NULL, 'BOGIE DOUBLE DECK AUTO CAR', 1, '2025-09-09 11:10:31', '2025-09-09 11:10:31'),
(1259, 'CORB', NULL, NULL, 'BOGIE DOUBLE DECK AUTO CAR', 1, '2025-09-09 11:10:31', '2025-09-09 11:10:31'),
(1260, 'COV', NULL, NULL, 'COVERED STOCK', 1, '2025-09-09 11:10:31', '2025-09-09 11:10:31'),
(1261, 'CRANE', NULL, NULL, 'CRANE', 1, '2025-09-09 11:10:31', '2025-09-09 11:10:31'),
(1262, 'CRC', NULL, NULL, 'COVERED GOODS', 1, '2025-09-09 11:10:31', '2025-09-09 11:10:31'),
(1263, 'CRT', NULL, NULL, '4 WHEELER COVERED TRANSITION CBC', 1, '2025-09-09 11:10:31', '2025-09-09 11:10:31'),
(1264, 'CS', NULL, NULL, 'COVERED WAGON FOR SALT', 1, '2025-09-09 11:10:31', '2025-09-09 11:10:31'),
(1265, 'CV', NULL, NULL, 'CV', 1, '2025-09-09 11:10:31', '2025-09-09 11:10:31'),
(1266, 'CW', NULL, NULL, 'CW', 1, '2025-09-09 11:10:31', '2025-09-09 11:10:31'),
(1267, 'DBFU', NULL, NULL, 'WELL WAGON MILITARY', 1, '2025-09-09 11:10:31', '2025-09-09 11:10:31'),
(1268, 'DBFUA', NULL, NULL, 'WELL WAGON MILITARY', 1, '2025-09-09 11:10:31', '2025-09-09 11:10:31'),
(1269, 'DBKM', NULL, NULL, 'BOGIE OPEN MILITARY', 1, '2025-09-09 11:10:31', '2025-09-09 11:10:31'),
(1270, 'DBWT', NULL, NULL, 'WELL WAGON MILITARY', 1, '2025-09-09 11:10:31', '2025-09-09 11:10:31'),
(1271, 'EAB', NULL, NULL, 'END ADAPTOR BOGIE', 1, '2025-09-09 11:10:31', '2025-09-09 11:10:31'),
(1272, 'FD', NULL, NULL, 'DUMMY TRUCK OR RELIEF TRUCK', 1, '2025-09-09 11:10:31', '2025-09-09 11:10:31'),
(1273, 'FK', NULL, NULL, 'FLATS FOR CONTAINERS', 1, '2025-09-09 11:10:31', '2025-09-09 11:10:31'),
(1274, 'FLATCOIL3', NULL, NULL, 'FLATCOIL3', 1, '2025-09-09 11:10:31', '2025-09-09 11:10:31'),
(1275, 'FMPA', NULL, NULL, 'RORO(CAR-A)', 1, '2025-09-09 11:10:31', '2025-09-09 11:10:31'),
(1276, 'FMPAB', NULL, NULL, 'RORO(CAR-A)', 1, '2025-09-09 11:10:31', '2025-09-09 11:10:31'),
(1277, 'FMPB', NULL, NULL, 'RORO(CAR-A)', 1, '2025-09-09 11:10:31', '2025-09-09 11:10:31'),
(1278, 'FR', NULL, NULL, 'RAIL TRUCK', 1, '2025-09-09 11:10:31', '2025-09-09 11:10:31'),
(1279, 'FRT', NULL, NULL, 'RAIL TRUCK TWIN', 1, '2025-09-09 11:10:31', '2025-09-09 11:10:31'),
(1280, 'FT', NULL, NULL, 'TIMBER TRUCK', 1, '2025-09-09 11:10:31', '2025-09-09 11:10:31'),
(1281, 'FTT', NULL, NULL, 'TIMBER TRUCK TWIN', 1, '2025-09-09 11:10:31', '2025-09-09 11:10:31'),
(1282, 'FU', NULL, NULL, 'OPEN WELL', 1, '2025-09-09 11:10:31', '2025-09-09 11:10:31'),
(1283, 'FW', NULL, NULL, 'FW', 1, '2025-09-09 11:10:31', '2025-09-09 11:10:31'),
(1284, 'FX', NULL, NULL, 'TRUCK FOR LIQUID OXYGEN', 1, '2025-09-09 11:10:31', '2025-09-09 11:10:31'),
(1285, 'IRCA', NULL, NULL, 'IRCA', 1, '2025-09-09 11:10:31', '2025-09-09 11:10:31'),
(1286, 'IRS', NULL, NULL, 'IRS', 1, '2025-09-09 11:10:32', '2025-09-09 11:10:32'),
(1287, 'IRSTPR', NULL, NULL, 'IRSTPR', 1, '2025-09-09 11:10:32', '2025-09-09 11:10:32'),
(1288, 'ITS', NULL, NULL, 'ITS', 1, '2025-09-09 11:10:32', '2025-09-09 11:10:32'),
(1289, 'J', NULL, NULL, 'J', 1, '2025-09-09 11:10:32', '2025-09-09 11:10:32'),
(1290, 'JI', NULL, NULL, 'JI', 1, '2025-09-09 11:10:32', '2025-09-09 11:10:32'),
(1291, 'K', NULL, NULL, 'OPEN WAGON', 1, '2025-09-09 11:10:32', '2025-09-09 11:10:32'),
(1292, 'KC', NULL, NULL, 'OPEN WAGON HIGHSIDED', 1, '2025-09-09 11:10:32', '2025-09-09 11:10:32'),
(1293, 'KCA', NULL, NULL, 'OPEN WAGON HIGHSIDED (WITH ANGLEIRONS )', 1, '2025-09-09 11:10:32', '2025-09-09 11:10:32'),
(1294, 'KCC', NULL, NULL, 'OPEN WAGON HIGHSIDED (SPECIAL COAL)', 1, '2025-09-09 11:10:32', '2025-09-09 11:10:32'),
(1295, 'KCF', NULL, NULL, 'KCF', 1, '2025-09-09 11:10:32', '2025-09-09 11:10:32'),
(1296, 'KCH', NULL, NULL, '4 WHEELER HOPPER WAGONS', 1, '2025-09-09 11:10:32', '2025-09-09 11:10:32'),
(1297, 'KCW', NULL, NULL, 'OPEN WAGON WOODEN BDY', 1, '2025-09-09 11:10:32', '2025-09-09 11:10:32'),
(1298, 'KD', NULL, NULL, '4 WHEELER OPEN MEDIUM HEIGHT', 1, '2025-09-09 11:10:32', '2025-09-09 11:10:32'),
(1299, 'KE', NULL, NULL, '4 WHEELER GENERAL/ELEPHANT TRUCK', 1, '2025-09-09 11:10:32', '2025-09-09 11:10:32'),
(1300, 'KF', NULL, NULL, '4 WHEELER OPEN WAGONS', 1, '2025-09-09 11:10:32', '2025-09-09 11:10:32'),
(1301, 'KFW', NULL, NULL, 'KFW', 1, '2025-09-09 11:10:32', '2025-09-09 11:10:32'),
(1302, 'KH', NULL, NULL, '4 WHEELER OPEN WAGON HOPPER', 1, '2025-09-09 11:10:32', '2025-09-09 11:10:32'),
(1303, 'KI', NULL, NULL, 'KI', 1, '2025-09-09 11:10:32', '2025-09-09 11:10:32'),
(1304, 'KK', NULL, NULL, '4 WHEELER OPEN WAGON SUGARCANE', 1, '2025-09-09 11:10:32', '2025-09-09 11:10:32'),
(1305, 'KL', NULL, NULL, '4 WHEELER OPEN WAGON LOW-SIDED', 1, '2025-09-09 11:10:32', '2025-09-09 11:10:32'),
(1306, 'KLF', NULL, NULL, 'KLF', 1, '2025-09-09 11:10:32', '2025-09-09 11:10:32'),
(1307, 'KLW', NULL, NULL, 'KLW', 1, '2025-09-09 11:10:32', '2025-09-09 11:10:32'),
(1308, 'KM', NULL, NULL, '4 WHEELER OPEN WAGON MILITARY', 1, '2025-09-09 11:10:32', '2025-09-09 11:10:32'),
(1309, 'KMF', NULL, NULL, '4 WHEELER OPEN WAGON MILITARY', 1, '2025-09-09 11:10:32', '2025-09-09 11:10:32'),
(1310, 'KOH', NULL, NULL, 'OPEN WAGON HIGH SIDED', 1, '2025-09-09 11:10:32', '2025-09-09 11:10:32'),
(1311, 'KP', NULL, NULL, '4 WHEELER OPEN WAGON POLES OR BAMBOO', 1, '2025-09-09 11:10:32', '2025-09-09 11:10:32'),
(1312, 'KR', NULL, NULL, '4 WHEELER OPEN REEL WAGON FOR WIRING CUM BREAK DOWN TRAIN', 1, '2025-09-09 11:10:32', '2025-09-09 11:10:32'),
(1313, 'KS', NULL, NULL, '4 WHEELER OPEN WAGON SALT', 1, '2025-09-09 11:10:32', '2025-09-09 11:10:32'),
(1314, 'KSW', NULL, NULL, 'KSW', 1, '2025-09-09 11:10:33', '2025-09-09 11:10:33'),
(1315, 'KU', NULL, NULL, '4 WHEELER PLATFORM WAGONS', 1, '2025-09-09 11:10:33', '2025-09-09 11:10:33'),
(1316, 'KV', NULL, NULL, '4 WHEELER OPEN WAGON WEIGHBRIDGE TESTING', 1, '2025-09-09 11:10:33', '2025-09-09 11:10:33'),
(1317, 'KW', NULL, NULL, '4 WHEELER OPEN WAGON BALLAST', 1, '2025-09-09 11:10:33', '2025-09-09 11:10:33'),
(1318, 'KWF', NULL, NULL, 'KWF', 1, '2025-09-09 11:10:33', '2025-09-09 11:10:33'),
(1319, 'LLRM', NULL, NULL, 'MILITARY', 1, '2025-09-09 11:10:33', '2025-09-09 11:10:33'),
(1320, 'M', NULL, NULL, 'MILITARY', 1, '2025-09-09 11:10:33', '2025-09-09 11:10:33'),
(1321, 'MAB', NULL, NULL, 'MIDDLE ADAPTOR BOGIE', 1, '2025-09-09 11:10:33', '2025-09-09 11:10:33'),
(1322, 'MACCN', NULL, NULL, 'MILITARY', 1, '2025-09-09 11:10:33', '2025-09-09 11:10:33'),
(1323, 'MACCW', NULL, NULL, 'MILITARY 2AC SLEEPER', 1, '2025-09-09 11:10:33', '2025-09-09 11:10:33'),
(1324, 'MBFU', NULL, NULL, 'BOGIE WELL WAGON MILITARY', 1, '2025-09-09 11:10:33', '2025-09-09 11:10:33'),
(1325, 'MBFUF', NULL, NULL, 'BOGIE WELL WAGON MILITARY', 1, '2025-09-09 11:10:33', '2025-09-09 11:10:33'),
(1326, 'MBKM', NULL, NULL, 'BOGIE FLAT WAGON FOR CARRYING MILITARY VEHICLES', 1, '2025-09-09 11:10:33', '2025-09-09 11:10:33'),
(1327, 'MBOX', NULL, NULL, 'MODIFIED BOX', 1, '2025-09-09 11:10:33', '2025-09-09 11:10:33'),
(1328, 'MBOXN', NULL, NULL, 'MBOXN', 1, '2025-09-09 11:10:33', '2025-09-09 11:10:33'),
(1329, 'MBWT', NULL, NULL, 'BOGIE WELL WAGON MILITARY', 1, '2025-09-09 11:10:33', '2025-09-09 11:10:33'),
(1330, 'MFR', NULL, NULL, 'MILITARY RAMP WAGON FLAT', 1, '2025-09-09 11:10:33', '2025-09-09 11:10:33'),
(1331, 'MGR', NULL, NULL, 'RAPID DISCHARGE COAL HOPPER', 1, '2025-09-09 11:10:33', '2025-09-09 11:10:33'),
(1332, 'MGS', NULL, NULL, 'MILITARY GS', 1, '2025-09-09 11:10:33', '2025-09-09 11:10:33'),
(1333, 'MGSCN', NULL, NULL, 'MILITARY SECOND CLASS SLEEPER', 1, '2025-09-09 11:10:33', '2025-09-09 11:10:33'),
(1334, 'MGSCNY', NULL, NULL, 'MILITARY', 1, '2025-09-09 11:10:33', '2025-09-09 11:10:33'),
(1335, 'MGSLR', NULL, NULL, 'MILITARY RAMP WAGON FLAT', 1, '2025-09-09 11:10:33', '2025-09-09 11:10:33'),
(1336, 'MILATRY', NULL, NULL, 'MILITARY 2AC SLEEPER WITH PANTRY CAR', 1, '2025-09-09 11:10:33', '2025-09-09 11:10:33'),
(1337, 'MKC', NULL, NULL, 'OPEN WAGON MATERIAL', 1, '2025-09-09 11:10:33', '2025-09-09 11:10:33'),
(1338, 'ML', NULL, NULL, 'MILITARY', 1, '2025-09-09 11:10:33', '2025-09-09 11:10:33'),
(1339, 'MLACCN', NULL, NULL, 'MILITARY', 1, '2025-09-09 11:10:33', '2025-09-09 11:10:33'),
(1340, 'MLACCW', NULL, NULL, 'MILITARY', 1, '2025-09-09 11:10:33', '2025-09-09 11:10:33'),
(1341, 'MOFK', NULL, NULL, 'FLATS FOR CONTAINERS', 1, '2025-09-09 11:10:33', '2025-09-09 11:10:33'),
(1342, 'MRG', NULL, NULL, 'MILITARY CARRIAGE FOR FAMILY', 1, '2025-09-09 11:10:33', '2025-09-09 11:10:33'),
(1343, 'MSLR', NULL, NULL, 'MILITARY LUGGAGE', 1, '2025-09-09 11:10:34', '2025-09-09 11:10:34'),
(1344, 'MWCB', NULL, NULL, 'MILITARY PANTRY CAR', 1, '2025-09-09 11:10:34', '2025-09-09 11:10:34'),
(1345, 'MWFC', NULL, NULL, 'MILITARY FIRST CLASS', 1, '2025-09-09 11:10:34', '2025-09-09 11:10:34'),
(1346, 'MWGACCN', NULL, NULL, 'MILITARY RAMP WAGON FLAT', 1, '2025-09-09 11:10:34', '2025-09-09 11:10:34'),
(1347, 'MWGACCW', NULL, NULL, 'MILITARY RAMP WAGON FLAT', 1, '2025-09-09 11:10:34', '2025-09-09 11:10:34'),
(1348, 'MWGCB', NULL, NULL, 'MILITARY RAMP WAGON FLAT', 1, '2025-09-09 11:10:34', '2025-09-09 11:10:34'),
(1349, 'MWGSCN', NULL, NULL, 'MILITARY RAMP WAGON FLAT', 1, '2025-09-09 11:10:34', '2025-09-09 11:10:34'),
(1350, 'MWGSCNY', NULL, NULL, 'MILITARY RAMP WAGON FLAT', 1, '2025-09-09 11:10:34', '2025-09-09 11:10:34'),
(1351, 'NE', NULL, NULL, 'STORE VAN', 1, '2025-09-09 11:10:34', '2025-09-09 11:10:34'),
(1352, 'NF', NULL, NULL, 'FISH VAN', 1, '2025-09-09 11:10:34', '2025-09-09 11:10:34'),
(1353, 'NH', NULL, NULL, 'FRUIT VAN', 1, '2025-09-09 11:10:34', '2025-09-09 11:10:34'),
(1354, 'NMG', NULL, NULL, 'AUTO LOADER VAN', 1, '2025-09-09 11:10:34', '2025-09-09 11:10:34'),
(1355, 'NMGH', NULL, NULL, 'AUTO LOADER VAN', 1, '2025-09-09 11:10:34', '2025-09-09 11:10:34'),
(1356, 'NMGHS', NULL, NULL, 'AUTO LOADER VAN', 1, '2025-09-09 11:10:34', '2025-09-09 11:10:34'),
(1357, 'NMGHSFS', NULL, NULL, 'AUTO LOADER VAN FRICTION SNUBBER', 1, '2025-09-09 11:10:34', '2025-09-09 11:10:34'),
(1358, 'NMGHSR', NULL, NULL, 'AUTOMOBILE CARRIER CUM BREAK VAN', 1, '2025-09-09 11:10:34', '2025-09-09 11:10:34'),
(1359, 'OC', NULL, NULL, '4 WHEELER COVERED', 1, '2025-09-09 11:10:34', '2025-09-09 11:10:34'),
(1360, 'OCV', NULL, NULL, 'OTHER COACHING VEHICLES', 1, '2025-09-09 11:10:34', '2025-09-09 11:10:34'),
(1361, 'OM', NULL, NULL, 'OPEN WAGON MILITARY', 1, '2025-09-09 11:10:34', '2025-09-09 11:10:34'),
(1362, 'OMT', NULL, NULL, 'OPEN WAGON MILITARY', 1, '2025-09-09 11:10:34', '2025-09-09 11:10:34'),
(1363, 'PCV', NULL, NULL, 'PSGR COACHING VEHICLES', 1, '2025-09-09 11:10:34', '2025-09-09 11:10:34'),
(1364, 'PRC', NULL, NULL, '4 WHEEL PAKISTAN RAILWAY COVERED WAGON', 1, '2025-09-09 11:10:34', '2025-09-09 11:10:34'),
(1365, 'PREIRS', NULL, NULL, 'PREIRS', 1, '2025-09-09 11:10:34', '2025-09-09 11:10:34'),
(1366, 'ROROCARA', NULL, NULL, 'RORO(CAR-A)', 1, '2025-09-09 11:10:34', '2025-09-09 11:10:34'),
(1367, 'ROROCARB', NULL, NULL, 'RORO(CAR-B)', 1, '2025-09-09 11:10:34', '2025-09-09 11:10:34'),
(1368, 'RX', NULL, NULL, 'BALLAST PLOUGH VAN', 1, '2025-09-09 11:10:34', '2025-09-09 11:10:34'),
(1369, 'SLR', NULL, NULL, 'PARCEL', 1, '2025-09-09 11:10:34', '2025-09-09 11:10:34'),
(1370, 'TA', NULL, NULL, 'TANK WAGON SULFURIC ACID', 1, '2025-09-09 11:10:35', '2025-09-09 11:10:35'),
(1371, 'TAP', NULL, NULL, 'TANK WAGON ALUMINA', 1, '2025-09-09 11:10:35', '2025-09-09 11:10:35'),
(1372, 'TAP2', NULL, NULL, 'TANK WAGON FOR AMMONIA ,ALUMINA', 1, '2025-09-09 11:10:35', '2025-09-09 11:10:35'),
(1373, 'TB', NULL, NULL, 'TANK WAGON BENZOL', 1, '2025-09-09 11:10:35', '2025-09-09 11:10:35'),
(1374, 'TC', NULL, NULL, '4 WHEELER TANK WAGON CREOSOTE', 1, '2025-09-09 11:10:35', '2025-09-09 11:10:35'),
(1375, 'TCS', NULL, NULL, 'TANK WAGON LIQUID C.SODA', 1, '2025-09-09 11:10:35', '2025-09-09 11:10:35'),
(1376, 'TD', NULL, NULL, '4 WHEELER TANK WAGON BITUMEN', 1, '2025-09-09 11:10:35', '2025-09-09 11:10:35'),
(1377, 'TE', NULL, NULL, '4 WHEELER TANK WAGON LIQ CAUSTIC SODA', 1, '2025-09-09 11:10:35', '2025-09-09 11:10:35'),
(1378, 'TF', NULL, NULL, '4 WHEELER TANK WAGON AMMONIA', 1, '2025-09-09 11:10:35', '2025-09-09 11:10:35'),
(1379, 'TG', NULL, NULL, '4 WHEELER TANK WAGON LIQUIFIED PETROL', 1, '2025-09-09 11:10:35', '2025-09-09 11:10:35'),
(1380, 'TH', NULL, NULL, '4 WHEELER TANK WAGON HEXANE', 1, '2025-09-09 11:10:35', '2025-09-09 11:10:35'),
(1381, 'THA', NULL, NULL, '4 WHEELER TANK WAGON HYD ACID', 1, '2025-09-09 11:10:35', '2025-09-09 11:10:35'),
(1382, 'TK', NULL, NULL, '4 WHEELER TANK WAGON KEROSENE', 1, '2025-09-09 11:10:35', '2025-09-09 11:10:35'),
(1383, 'TKOPEN', NULL, NULL, 'TKOPEN', 1, '2025-09-09 11:10:35', '2025-09-09 11:10:35'),
(1384, 'TL', NULL, NULL, '4 WHEELER TANK WAGON BLACK OIL', 1, '2025-09-09 11:10:36', '2025-09-09 11:10:36'),
(1385, 'TM', NULL, NULL, '4 WHEELER TANK WAGON MOLASSES', 1, '2025-09-09 11:10:36', '2025-09-09 11:10:36'),
(1386, 'TN', NULL, NULL, '4 WHEELER TANK WAGON MENTHONOL', 1, '2025-09-09 11:10:36', '2025-09-09 11:10:36'),
(1387, 'TO', NULL, NULL, 'TO', 1, '2025-09-09 11:10:36', '2025-09-09 11:10:36'),
(1388, 'TOH', NULL, NULL, '4 WHEELER TANK WAGON HEAVY OIL', 1, '2025-09-09 11:10:36', '2025-09-09 11:10:36'),
(1389, 'TOHT', NULL, NULL, 'TANK WAGON HEAVY OIL', 1, '2025-09-09 11:10:36', '2025-09-09 11:10:36'),
(1390, 'TORH', NULL, NULL, '4 WHEELER TANK WAGON HEAVY OIL', 1, '2025-09-09 11:10:36', '2025-09-09 11:10:36'),
(1391, 'TORHC', NULL, NULL, '4 WHEELER TANK WAGON HEAVY OIL CBC', 1, '2025-09-09 11:10:36', '2025-09-09 11:10:36'),
(1392, 'TORHT', NULL, NULL, '4 WHEELER TANK WAGON HEAVY OIL', 1, '2025-09-09 11:10:36', '2025-09-09 11:10:36'),
(1393, 'TORX', NULL, NULL, 'TANK WAGON HEAVY OIL', 1, '2025-09-09 11:10:36', '2025-09-09 11:10:36'),
(1394, 'TORXC', NULL, NULL, 'TORXC', 1, '2025-09-09 11:10:36', '2025-09-09 11:10:36'),
(1395, 'TORXT', NULL, NULL, 'TORXT', 1, '2025-09-09 11:10:36', '2025-09-09 11:10:36'),
(1396, 'TORXTK', NULL, NULL, 'TORXTK', 1, '2025-09-09 11:10:36', '2025-09-09 11:10:36'),
(1397, 'TORXTV', NULL, NULL, 'TORXTV', 1, '2025-09-09 11:10:36', '2025-09-09 11:10:36'),
(1398, 'TP', NULL, NULL, '4 WHEELER TANK WAGON PETROL', 1, '2025-09-09 11:10:36', '2025-09-09 11:10:36'),
(1399, 'TPGL', NULL, NULL, '4 WHEELER TANK WAGON LIQ. PETROL GAS', 1, '2025-09-09 11:10:36', '2025-09-09 11:10:36'),
(1400, 'TPGLR', NULL, NULL, '4 WHEELER TANK WAGON LIQ. PETROL GAS', 1, '2025-09-09 11:10:36', '2025-09-09 11:10:36'),
(1401, 'TPR', NULL, NULL, '4 WHEELER TANK WAGON PETROL', 1, '2025-09-09 11:10:36', '2025-09-09 11:10:36'),
(1402, 'TPRC', NULL, NULL, 'TANK WAGON', 1, '2025-09-09 11:10:36', '2025-09-09 11:10:36'),
(1403, 'TPRIRS', NULL, NULL, 'TPRIRS', 1, '2025-09-09 11:10:36', '2025-09-09 11:10:36'),
(1404, 'TPTPRC', NULL, NULL, 'TPTPRC', 1, '2025-09-09 11:10:36', '2025-09-09 11:10:36'),
(1405, 'TR', NULL, NULL, '4 WHEELER TANK WAGON COAL TAR', 1, '2025-09-09 11:10:36', '2025-09-09 11:10:36'),
(1406, 'TRACK', NULL, NULL, 'TRACK', 1, '2025-09-09 11:10:36', '2025-09-09 11:10:36'),
(1407, 'TRS', NULL, NULL, 'TRS', 1, '2025-09-09 11:10:36', '2025-09-09 11:10:36'),
(1408, 'TS', NULL, NULL, '4 WHEELER TANK WAGON COUNTRY SPIRIT', 1, '2025-09-09 11:10:36', '2025-09-09 11:10:36'),
(1409, 'TSA', NULL, NULL, '4 WHEELER TANK WAGON SULPHURIC ACID', 1, '2025-09-09 11:10:36', '2025-09-09 11:10:36'),
(1410, 'TV', NULL, NULL, '4 WHEELER TANK WAGON VEGETABLE OIL', 1, '2025-09-09 11:10:36', '2025-09-09 11:10:36'),
(1411, 'TW', NULL, NULL, '4 WHEELER TANK WAGON WATER', 1, '2025-09-09 11:10:37', '2025-09-09 11:10:37'),
(1412, 'TWF', NULL, NULL, '4 WHEELER TANK WAGON WATER', 1, '2025-09-09 11:10:37', '2025-09-09 11:10:37'),
(1413, 'TWT', NULL, NULL, '4 WHEELER TANK WAGON WATER', 1, '2025-09-09 11:10:37', '2025-09-09 11:10:37'),
(1414, 'TX', NULL, NULL, '4 WHEELER TANK WAGON LIQUID CHLORINE', 1, '2025-09-09 11:10:37', '2025-09-09 11:10:37'),
(1415, 'TZ', NULL, NULL, '4 WHEELER TANK WAGON LUBRICATING OIL', 1, '2025-09-09 11:10:37', '2025-09-09 11:10:37'),
(1416, 'V', NULL, NULL, '4 WHEELER BRAKE VAN. ORDINARY', 1, '2025-09-09 11:10:37', '2025-09-09 11:10:37'),
(1417, 'VB', NULL, NULL, '4 WHEELER BRAKE VAN. ORDINARY', 1, '2025-09-09 11:10:37', '2025-09-09 11:10:37'),
(1418, 'VH', NULL, NULL, '4 WHEELER BRAKE VAN. HEAVY', 1, '2025-09-09 11:10:37', '2025-09-09 11:10:37'),
(1419, 'VM', NULL, NULL, '4 WHEELER BRAKE VAN. MEDIUM', 1, '2025-09-09 11:10:37', '2025-09-09 11:10:37'),
(1420, 'VP', NULL, NULL, 'PARCEL', 1, '2025-09-09 11:10:37', '2025-09-09 11:10:37'),
(1421, 'VPH', NULL, NULL, 'PARCEL', 1, '2025-09-09 11:10:37', '2025-09-09 11:10:37'),
(1422, 'VPU', NULL, NULL, 'BOGIE MOTOR CUM PARCEL VAN', 1, '2025-09-09 11:10:37', '2025-09-09 11:10:37'),
(1423, 'VVHN1', NULL, NULL, 'RAIL MILK TANK VAN', 1, '2025-09-09 11:10:37', '2025-09-09 11:10:37'),
(1424, 'VY', NULL, NULL, 'BRAKE VAN', 1, '2025-09-09 11:10:37', '2025-09-09 11:10:37'),
(1425, 'X', NULL, NULL, 'EXPLOSIVES', 1, '2025-09-09 11:10:37', '2025-09-09 11:10:37'),
(1426, 'XC', NULL, NULL, 'EXPLOSIVES', 1, '2025-09-09 11:10:37', '2025-09-09 11:10:37'),
(1427, 'XK', NULL, NULL, 'EXPLOSIVES', 1, '2025-09-09 11:10:37', '2025-09-09 11:10:37');

-- --------------------------------------------------------

--
-- Table structure for table `zones`
--

CREATE TABLE `zones` (
  `zone_id` int(11) NOT NULL,
  `zone_code` varchar(10) NOT NULL,
  `zone_name` varchar(100) NOT NULL,
  `zone_full_name` varchar(200) NOT NULL,
  `headquarters` varchar(100) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `zones`
--

INSERT INTO `zones` (`zone_id`, `zone_code`, `zone_name`, `zone_full_name`, `headquarters`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'CR', 'Central Railway', 'Central Railway Zone', 'Mumbai', 1, '2025-09-11 18:20:49', '2025-09-11 18:20:49'),
(2, 'WR', 'Western Railway', 'Western Railway Zone', 'Mumbai', 1, '2025-09-11 18:20:49', '2025-09-11 18:20:49'),
(3, 'NR', 'Northern Railway', 'Northern Railway Zone', 'New Delhi', 1, '2025-09-11 18:20:49', '2025-09-11 18:20:49'),
(4, 'SR', 'Southern Railway', 'Southern Railway Zone', 'Chennai', 1, '2025-09-11 18:20:49', '2025-09-11 18:20:49'),
(5, 'ER', 'Eastern Railway', 'Eastern Railway Zone', 'Kolkata', 1, '2025-09-11 18:20:49', '2025-09-11 18:20:49'),
(6, 'NER', 'North Eastern Railway', 'North Eastern Railway Zone', 'Gorakhpur', 1, '2025-09-11 18:20:49', '2025-09-11 18:20:49'),
(7, 'NFR', 'Northeast Frontier Railway', 'Northeast Frontier Railway Zone', 'Guwahati', 1, '2025-09-11 18:20:49', '2025-09-11 18:20:49'),
(8, 'SCR', 'South Central Railway', 'South Central Railway Zone', 'Secunderabad', 1, '2025-09-11 18:20:49', '2025-09-11 18:20:49'),
(9, 'SER', 'South Eastern Railway', 'South Eastern Railway Zone', 'Kolkata', 1, '2025-09-11 18:20:49', '2025-09-11 18:20:49'),
(10, 'SECR', 'South East Central Railway', 'South East Central Railway Zone', 'Bilaspur', 1, '2025-09-11 18:20:49', '2025-09-11 18:20:49'),
(11, 'SWR', 'South Western Railway', 'South Western Railway Zone', 'Hubli', 1, '2025-09-11 18:20:49', '2025-09-11 18:20:49'),
(12, 'WCR', 'West Central Railway', 'West Central Railway Zone', 'Jabalpur', 1, '2025-09-11 18:20:49', '2025-09-11 18:20:49'),
(13, 'NCR', 'North Central Railway', 'North Central Railway Zone', 'Allahabad', 1, '2025-09-11 18:20:49', '2025-09-11 18:20:49'),
(14, 'NWR', 'North Western Railway', 'North Western Railway Zone', 'Jaipur', 1, '2025-09-11 18:20:49', '2025-09-11 18:20:49'),
(15, 'ECR', 'East Central Railway', 'East Central Railway Zone', 'Hajipur', 1, '2025-09-11 18:20:49', '2025-09-11 18:20:49'),
(16, 'ECOR', 'East Coast Railway', 'East Coast Railway Zone', 'Bhubaneswar', 1, '2025-09-11 18:20:49', '2025-09-11 18:20:49'),
(17, 'KR', 'Konkan Railway', 'Konkan Railway Corporation', 'Navi Mumbai', 1, '2025-09-11 18:20:49', '2025-09-11 18:20:49');

-- --------------------------------------------------------

--
-- Stand-in structure for view `zone_division_departments`
-- (See below for the actual view)
--
CREATE TABLE `zone_division_departments` (
`zone_code` varchar(10)
,`zone_name` varchar(100)
,`division_code` varchar(10)
,`division_name` varchar(100)
,`department_code` varchar(20)
,`department_name` varchar(100)
,`zone_active` tinyint(1)
,`division_active` tinyint(1)
,`department_active` tinyint(1)
);

-- --------------------------------------------------------

--
-- Structure for view `active_tickets`
--
DROP TABLE IF EXISTS `active_tickets`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `active_tickets`  AS SELECT `c`.`complaint_id` AS `complaint_id`, `c`.`category_id` AS `category_id`, `c`.`date` AS `date`, `c`.`time` AS `time`, `c`.`shed_id` AS `shed_id`, `c`.`wagon_id` AS `wagon_id`, `c`.`rating` AS `rating`, `c`.`rating_remarks` AS `rating_remarks`, `c`.`description` AS `description`, `c`.`action_taken` AS `action_taken`, `c`.`status` AS `status`, `c`.`department` AS `department`, `c`.`division` AS `division`, `c`.`zone` AS `zone`, `c`.`customer_id` AS `customer_id`, `c`.`fnr_number` AS `fnr_number`, `c`.`e_indent_number` AS `e_indent_number`, `c`.`assigned_to_department` AS `assigned_to_department`, `c`.`forwarded_flag` AS `forwarded_flag`, `c`.`priority` AS `priority`, `c`.`sla_deadline` AS `sla_deadline`, `c`.`created_at` AS `created_at`, `c`.`updated_at` AS `updated_at`, `c`.`closed_at` AS `closed_at`, `c`.`escalated_at` AS `escalated_at`, `cat`.`category` AS `category`, `cat`.`type` AS `type`, `cat`.`subtype` AS `subtype`, `s`.`name` AS `shed_name`, `s`.`shed_code` AS `shed_code`, `cust`.`name` AS `customer_name`, `cust`.`email` AS `customer_email`, `cust`.`mobile` AS `customer_mobile`, `cust`.`company_name` AS `company_name` FROM (((`complaints` `c` left join `complaint_categories` `cat` on(`c`.`category_id` = `cat`.`category_id`)) left join `shed` `s` on(`c`.`shed_id` = `s`.`shed_id`)) left join `customers` `cust` on(`c`.`customer_id` = `cust`.`customer_id`)) WHERE `c`.`status` <> 'closed' ;

-- --------------------------------------------------------

--
-- Structure for view `ticket_summary`
--
DROP TABLE IF EXISTS `ticket_summary`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `ticket_summary`  AS SELECT `complaints`.`division` AS `division`, `complaints`.`zone` AS `zone`, `complaints`.`status` AS `status`, `complaints`.`priority` AS `priority`, count(0) AS `ticket_count`, avg(timestampdiff(HOUR,`complaints`.`created_at`,coalesce(`complaints`.`closed_at`,current_timestamp()))) AS `avg_resolution_hours` FROM `complaints` GROUP BY `complaints`.`division`, `complaints`.`zone`, `complaints`.`status`, `complaints`.`priority` ;

-- --------------------------------------------------------

--
-- Structure for view `zone_division_departments`
--
DROP TABLE IF EXISTS `zone_division_departments`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `zone_division_departments`  AS SELECT `z`.`zone_code` AS `zone_code`, `z`.`zone_name` AS `zone_name`, `d`.`division_code` AS `division_code`, `d`.`division_name` AS `division_name`, `dept`.`department_code` AS `department_code`, `dept`.`department_name` AS `department_name`, `z`.`is_active` AS `zone_active`, `d`.`is_active` AS `division_active`, `dept`.`is_active` AS `department_active` FROM ((`zones` `z` join `divisions` `d` on(`d`.`zone_code` = `z`.`zone_code`)) join `departments` `dept`) WHERE `z`.`is_active` = 1 AND `d`.`is_active` = 1 AND `dept`.`is_active` = 1 ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_customer` (`customer_id`),
  ADD KEY `idx_action` (`action`),
  ADD KEY `idx_complaint` (`complaint_id`),
  ADD KEY `idx_created_date` (`created_at`);

--
-- Indexes for table `complaints`
--
ALTER TABLE `complaints`
  ADD PRIMARY KEY (`complaint_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_priority` (`priority`),
  ADD KEY `idx_customer` (`customer_id`),
  ADD KEY `idx_division` (`division`),
  ADD KEY `idx_department` (`department`),
  ADD KEY `idx_created_date` (`date`),
  ADD KEY `idx_sla_deadline` (`sla_deadline`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `shed_id` (`shed_id`),
  ADD KEY `idx_complaints_search` (`status`,`priority`,`division`,`created_at`);

--
-- Indexes for table `complaint_categories`
--
ALTER TABLE `complaint_categories`
  ADD PRIMARY KEY (`category_id`),
  ADD UNIQUE KEY `unique_category_type_subtype` (`category`,`type`,`subtype`),
  ADD KEY `idx_category` (`category`),
  ADD KEY `idx_type` (`type`);

--
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`customer_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `mobile` (`mobile`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_division` (`division`),
  ADD KEY `idx_customers_search` (`status`,`division`,`zone`),
  ADD KEY `idx_customer_type` (`customer_type`);

--
-- Indexes for table `departments`
--
ALTER TABLE `departments`
  ADD PRIMARY KEY (`department_id`),
  ADD UNIQUE KEY `idx_department_code` (`department_code`),
  ADD KEY `idx_department_active` (`is_active`),
  ADD KEY `idx_departments_active` (`is_active`);

--
-- Indexes for table `divisions`
--
ALTER TABLE `divisions`
  ADD PRIMARY KEY (`division_id`),
  ADD UNIQUE KEY `idx_division_code_zone` (`division_code`,`zone_code`),
  ADD KEY `idx_zone_id` (`zone_id`),
  ADD KEY `idx_division_active` (`is_active`),
  ADD KEY `idx_divisions_zone` (`zone_code`),
  ADD KEY `idx_divisions_active` (`is_active`);

--
-- Indexes for table `email_templates`
--
ALTER TABLE `email_templates`
  ADD PRIMARY KEY (`template_id`),
  ADD UNIQUE KEY `template_code` (`template_code`),
  ADD KEY `idx_active` (`is_active`);

--
-- Indexes for table `evidence`
--
ALTER TABLE `evidence`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_complaint_id` (`complaint_id`),
  ADD KEY `idx_uploaded_by` (`uploaded_by_type`,`uploaded_by_id`),
  ADD KEY `idx_evidence_complaint_uploaded` (`complaint_id`,`uploaded_by_type`,`uploaded_by_id`);

--
-- Indexes for table `news`
--
ALTER TABLE `news`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_active` (`is_active`),
  ADD KEY `idx_type` (`type`),
  ADD KEY `idx_priority` (`priority`),
  ADD KEY `idx_homepage` (`show_on_homepage`),
  ADD KEY `idx_marquee` (`show_on_marquee`),
  ADD KEY `idx_publish_date` (`publish_date`),
  ADD KEY `idx_expire_date` (`expire_date`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_customer` (`customer_id`),
  ADD KEY `idx_complaint` (`complaint_id`),
  ADD KEY `idx_read` (`is_read`),
  ADD KEY `idx_created_date` (`created_at`);

--
-- Indexes for table `quick_links`
--
ALTER TABLE `quick_links`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_active` (`is_active`),
  ADD KEY `idx_sort` (`sort_order`);

--
-- Indexes for table `remember_tokens`
--
ALTER TABLE `remember_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `selector` (`selector`),
  ADD KEY `idx_selector` (`selector`),
  ADD KEY `idx_user` (`user_type`,`user_id`),
  ADD KEY `idx_expires` (`expires_at`);

--
-- Indexes for table `shed`
--
ALTER TABLE `shed`
  ADD PRIMARY KEY (`shed_id`),
  ADD UNIQUE KEY `shed_code` (`shed_code`),
  ADD KEY `idx_division` (`division`),
  ADD KEY `idx_zone` (`zone`),
  ADD KEY `idx_active` (`is_active`),
  ADD KEY `idx_search` (`shed_code`,`name`,`division`);

--
-- Indexes for table `sla_definitions`
--
ALTER TABLE `sla_definitions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `priority_level` (`priority_level`),
  ADD KEY `idx_active` (`is_active`);

--
-- Indexes for table `system_cache`
--
ALTER TABLE `system_cache`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `cache_key` (`cache_key`),
  ADD KEY `idx_cache_key` (`cache_key`),
  ADD KEY `idx_expires_at` (`expires_at`);

--
-- Indexes for table `system_settings`
--
ALTER TABLE `system_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`),
  ADD KEY `idx_group` (`group_name`),
  ADD KEY `updated_by` (`updated_by`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`transaction_id`),
  ADD KEY `idx_complaint` (`complaint_id`),
  ADD KEY `idx_transaction_type` (`transaction_type`),
  ADD KEY `idx_from_user` (`from_user_id`),
  ADD KEY `idx_to_user` (`to_user_id`),
  ADD KEY `idx_created_by` (`created_by_id`),
  ADD KEY `idx_created_date` (`created_at`),
  ADD KEY `from_customer_id` (`from_customer_id`),
  ADD KEY `to_customer_id` (`to_customer_id`),
  ADD KEY `idx_transactions_search` (`complaint_id`,`transaction_type`,`created_at`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `login_id` (`login_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_role` (`role`),
  ADD KEY `idx_department` (`department`),
  ADD KEY `idx_division` (`division`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_users_search` (`role`,`department`,`division`,`status`);

--
-- Indexes for table `wagon_details`
--
ALTER TABLE `wagon_details`
  ADD PRIMARY KEY (`wagon_id`),
  ADD UNIQUE KEY `wagon_code` (`wagon_code`),
  ADD KEY `idx_type` (`type`),
  ADD KEY `idx_active` (`is_active`);

--
-- Indexes for table `zones`
--
ALTER TABLE `zones`
  ADD PRIMARY KEY (`zone_id`),
  ADD UNIQUE KEY `zone_code` (`zone_code`),
  ADD UNIQUE KEY `idx_zone_code` (`zone_code`),
  ADD KEY `idx_zone_active` (`is_active`),
  ADD KEY `idx_zones_active` (`is_active`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=818;

--
-- AUTO_INCREMENT for table `complaint_categories`
--
ALTER TABLE `complaint_categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=64;

--
-- AUTO_INCREMENT for table `departments`
--
ALTER TABLE `departments`
  MODIFY `department_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `divisions`
--
ALTER TABLE `divisions`
  MODIFY `division_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `email_templates`
--
ALTER TABLE `email_templates`
  MODIFY `template_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `evidence`
--
ALTER TABLE `evidence`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `news`
--
ALTER TABLE `news`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `quick_links`
--
ALTER TABLE `quick_links`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `remember_tokens`
--
ALTER TABLE `remember_tokens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `shed`
--
ALTER TABLE `shed`
  MODIFY `shed_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=500;

--
-- AUTO_INCREMENT for table `sla_definitions`
--
ALTER TABLE `sla_definitions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `system_cache`
--
ALTER TABLE `system_cache`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2182;

--
-- AUTO_INCREMENT for table `system_settings`
--
ALTER TABLE `system_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `transaction_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `wagon_details`
--
ALTER TABLE `wagon_details`
  MODIFY `wagon_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1428;

--
-- AUTO_INCREMENT for table `zones`
--
ALTER TABLE `zones`
  MODIFY `zone_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD CONSTRAINT `activity_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `activity_logs_ibfk_2` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`customer_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `activity_logs_ibfk_3` FOREIGN KEY (`complaint_id`) REFERENCES `complaints` (`complaint_id`) ON DELETE SET NULL;

--
-- Constraints for table `complaints`
--
ALTER TABLE `complaints`
  ADD CONSTRAINT `complaints_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`customer_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `complaints_ibfk_3` FOREIGN KEY (`category_id`) REFERENCES `complaint_categories` (`category_id`),
  ADD CONSTRAINT `complaints_ibfk_4` FOREIGN KEY (`shed_id`) REFERENCES `shed` (`shed_id`);

--
-- Constraints for table `divisions`
--
ALTER TABLE `divisions`
  ADD CONSTRAINT `divisions_ibfk_1` FOREIGN KEY (`zone_id`) REFERENCES `zones` (`zone_id`) ON UPDATE CASCADE;

--
-- Constraints for table `news`
--
ALTER TABLE `news`
  ADD CONSTRAINT `news_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `notifications_ibfk_2` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`customer_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `notifications_ibfk_3` FOREIGN KEY (`complaint_id`) REFERENCES `complaints` (`complaint_id`) ON DELETE CASCADE;

--
-- Constraints for table `system_settings`
--
ALTER TABLE `system_settings`
  ADD CONSTRAINT `system_settings_ibfk_1` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `transactions_ibfk_1` FOREIGN KEY (`complaint_id`) REFERENCES `complaints` (`complaint_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `transactions_ibfk_2` FOREIGN KEY (`from_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `transactions_ibfk_3` FOREIGN KEY (`to_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `transactions_ibfk_4` FOREIGN KEY (`from_customer_id`) REFERENCES `customers` (`customer_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `transactions_ibfk_5` FOREIGN KEY (`to_customer_id`) REFERENCES `customers` (`customer_id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
