-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 17, 2025 at 02:06 PM
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
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `closed_at` timestamp NULL DEFAULT NULL,
  `escalated_at` timestamp NULL DEFAULT NULL,
  `escalation_stopped` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `complaints`
--

INSERT INTO `complaints` (`complaint_id`, `category_id`, `date`, `time`, `shed_id`, `wagon_id`, `rating`, `rating_remarks`, `description`, `action_taken`, `status`, `department`, `division`, `zone`, `customer_id`, `fnr_number`, `e_indent_number`, `assigned_to_department`, `forwarded_flag`, `priority`, `created_at`, `updated_at`, `closed_at`, `escalated_at`, `escalation_stopped`) VALUES
('202509150001', 31, '2025-09-15', '12:42:23', 279, 1024, 'excellent', 'Good', 'rake is delayed at LNL for more than 5 hours.', 'Made Necessary changes to the other one was', 'closed', 'CML', 'BB', 'CR', 'CUST2025090001', '123456789', '', 'CML', 0, 'critical', '2025-09-15 07:12:23', '2025-09-17 05:31:37', '2025-09-17 05:31:37', '2025-09-15 19:26:55', 1),
('202509150002', 29, '2025-09-15', '12:55:24', 266, 1222, NULL, NULL, 'staff is not co operating after asking information about rake.', NULL, 'pending', NULL, 'BB', 'CR', 'CUST2025090001', '', '', 'OPTG', 1, 'critical', '2025-09-15 07:25:24', '2025-09-17 09:20:19', NULL, NULL, 0),
('202509150003', 7, '2025-09-15', '12:58:39', 103, 1027, NULL, NULL, 'My loading trucks will arrive at depot by 2300 hrs please provide my rake till then, also crew after 6 hours at 0500 hrs', NULL, 'pending', NULL, 'BB', 'CR', 'CUST2025090001', '', '', 'OPTG', 1, 'critical', '2025-09-15 07:28:39', '2025-09-17 09:20:45', NULL, NULL, 0),
('202509150004', 29, '2025-09-15', '13:20:55', 55, 1017, NULL, NULL, 'Staff was arguing while creating the FNR.', NULL, 'pending', NULL, 'BB', 'CR', 'CUST2025090001', '', '', 'OPTG', 1, 'critical', '2025-09-15 07:50:55', '2025-09-17 09:23:45', NULL, NULL, 0),
('202509150005', 6, '2025-09-15', '13:24:57', 55, 1029, 'unsatisfactory', 'Cant see Why Action not being taken', 'I need stacking permission for my goods', 'Noted. Will Take Action', 'closed', 'CML', 'BB', 'CR', 'CUST2025090001', '', '', 'CML', 0, 'critical', '2025-09-15 07:54:57', '2025-09-17 11:48:02', '2025-09-17 11:48:02', '2025-09-15 19:27:25', 1),
('202509160001', 6, '2025-09-16', '14:34:28', 61, 1013, NULL, NULL, 'TESTING THE EMAIL SENDER', NULL, 'pending', NULL, 'BB', 'CR', 'CUST2025090001', '', '', 'CML', 0, 'critical', '2025-09-16 09:04:28', '2025-09-17 09:07:41', NULL, '2025-09-16 12:26:30', 0),
('202509170001', 1, '2025-09-17', '00:39:59', 129, 1012, NULL, NULL, 'this is for the testing of notification', NULL, 'pending', NULL, 'BB', 'CR', 'CUST2025090001', '', '', 'CML', 0, 'high', '2025-09-16 19:09:59', '2025-09-17 09:07:47', NULL, NULL, 0),
('202509170002', 43, '2025-09-17', '10:42:20', 164, 1027, NULL, NULL, 'Testing the new notifications', NULL, 'pending', NULL, 'BB', 'CR', 'CUST2025090001', '', '', 'CML', 0, 'medium', '2025-09-17 05:12:20', '2025-09-17 09:12:41', NULL, NULL, 0),
('202509170003', 6, '2025-09-17', '11:25:54', 345, 1012, NULL, NULL, 'This is testing for notifications', NULL, 'pending', NULL, 'BB', 'CR', 'CUST2025090001', '', '', 'CML', 0, 'medium', '2025-09-17 05:55:54', '2025-09-17 10:07:05', NULL, NULL, 0),
('202509170004', 6, '2025-09-17', '11:54:42', 129, 1013, NULL, NULL, 'Testing the email sender', NULL, 'pending', NULL, 'BB', 'CR', 'CUST2025090001', '', '', 'CML', 0, 'medium', '2025-09-17 06:24:42', '2025-09-17 10:25:09', NULL, NULL, 0),
('202509170005', 1, '2025-09-17', '12:17:06', 129, 1013, NULL, NULL, 'Email and notifications testing', NULL, 'pending', NULL, 'BB', 'CR', 'CUST2025090001', '', '', 'CML', 0, 'medium', '2025-09-17 06:47:06', '2025-09-17 10:58:42', NULL, NULL, 0),
('202509170006', 8, '2025-09-17', '15:12:57', 61, 1011, NULL, NULL, 'Testing new auto notifications', NULL, 'pending', NULL, 'BB', 'CR', 'CUST2025090001', '', '', 'CML', 0, 'normal', '2025-09-17 09:42:57', '2025-09-17 09:42:57', NULL, NULL, 0),
('202509170007', 49, '2025-09-17', '15:37:37', 345, 1014, NULL, NULL, 'Testing new functionality.', NULL, 'pending', NULL, 'BB', 'CR', 'CUST2025090001', '', '', 'CML', 0, 'normal', '2025-09-17 10:07:37', '2025-09-17 10:07:37', NULL, NULL, 0),
('202509170008', 61, '2025-09-17', '17:07:18', 129, 1012, NULL, NULL, 'This testing new holding notifications\n\n--- Additional Info ---\nThis is the new info', NULL, 'pending', NULL, 'BB', 'CR', 'CUST2025090001', '11', '11', 'CML', 0, 'normal', '2025-09-17 11:37:18', '2025-09-17 11:40:08', NULL, NULL, 1);

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
('CUST2025090001', '$2y$10$Op.un2uvPATgiVHiCERz3.3LcG1GDqYJQ8xrKoZ1S3K7DVC9nHgzS', 'TEST', 'dineshkurkure.dk@gmail.com', '987654321', 'TEST', 'TEST', NULL, 'individual', 'customer', 'approved', 'BB', 'CR', '2025-09-08 07:08:17', NULL, '2025-09-17 11:46:57');

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
(8, 'RPF', 'Security', 'Security Department - Railway Protection Force, safety', 1, '2025-09-11 18:20:49', '2025-09-11 18:25:42'),
(14, 'ADM', 'Administration', 'Administration Department - General administration', 1, '2025-09-12 11:11:18', '2025-09-12 11:11:18'),
(15, 'IT', 'IT', 'Information Technology Department - Systems and technology', 1, '2025-09-12 11:11:18', '2025-09-12 11:11:18');

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
(1, 'BB', 'Mumbai CSMT Division', 1, 'CR', 'Mumbai CSMT', 1, '2025-09-11 18:20:49', '2025-09-15 07:43:30'),
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
(14, 'AMB', 'Ambala Division', 3, 'NR', 'Ambala', 1, '2025-09-11 18:20:49', '2025-09-11 18:20:49'),
(15, 'HQ', 'Headquarter', 1, 'CR', 'Mumbai CSMT', 1, '2025-09-11 18:20:49', '2025-09-15 07:43:30');

-- --------------------------------------------------------

--
-- Table structure for table `email_templates`
--

CREATE TABLE `email_templates` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `template_code` varchar(100) NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `subject` varchar(255) DEFAULT NULL,
  `template_json` longtext DEFAULT NULL,
  `body_html` longtext DEFAULT NULL,
  `body_text` longtext DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `email_templates`
--

INSERT INTO `email_templates` (`id`, `name`, `template_code`, `is_active`, `subject`, `template_json`, `body_html`, `body_text`, `updated_at`) VALUES
(1, 'Ticket Created Notification', 'ticket_created', 1, 'Your Support Ticket #{{complaint_id}} has been Created', NULL, '<!DOCTYPE html><html><head><meta charset=\"utf-8\"><meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\"></head><body style=\"margin: 0; padding: 0; font-family: Arial, sans-serif; background-color: #f5f5f5;\"><div style=\"max-width: 600px; margin: 0 auto; background-color: #ffffff; box-shadow: 0 4px 6px rgba(0,0,0,0.1);\"><header style=\"background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: white; padding: 30px 20px; text-align: center;\"><h1 style=\"margin: 0; font-size: 28px; font-weight: bold;\">{{app_name}}</h1><p style=\"margin: 5px 0 0 0; opacity: 0.9; font-size: 14px;\">Support & Mediation Portal</p></header><div style=\"padding: 30px 20px;\"><div style=\"background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: white; padding: 20px; border-radius: 8px; text-align: center; margin-bottom: 30px;\"><h2 style=\"margin: 0; font-size: 24px;\">­ƒÄ½ Ticket Created Successfully!</h2><p style=\"margin: 10px 0 0 0; font-size: 16px; opacity: 0.9;\">Your support request has been received</p></div><h2 style=\"color: #28a745; margin-bottom: 20px;\">Dear {{customer_name}},</h2><p style=\"font-size: 16px; line-height: 1.6; color: #333;\">Thank you for contacting us. Your support ticket has been created successfully and our team will review it shortly.</p><div style=\"background-color: #f8f9fa; border-left: 4px solid #28a745; padding: 20px; margin: 25px 0; border-radius: 0 8px 8px 0;\"><h3 style=\"margin-top: 0; color: #28a745; font-size: 18px;\">­ƒôï Ticket Details</h3><p style=\"margin: 8px 0;\"><strong>Ticket ID:</strong> <span style=\"color: #28a745; font-weight: bold;\">#{{complaint_id}}</span></p><p style=\"margin: 8px 0;\"><strong>Company:</strong> {{company_name}}</p><p style=\"margin: 8px 0;\"><strong>Status:</strong> <span style=\"background: #28a745; color: white; padding: 4px 8px; border-radius: 4px; font-size: 12px;\">ACTIVE</span></p><p style=\"margin: 8px 0;\"><strong>Created:</strong> {{created_date}}</p></div><div style=\"background-color: #e8f5e8; border: 1px solid #28a745; border-radius: 8px; padding: 20px; margin: 25px 0;\"><h3 style=\"margin-top: 0; color: #155724;\">­ƒô× What happens next?</h3><ul style=\"margin: 10px 0; padding-left: 20px; color: #155724;\"><li style=\"margin: 8px 0;\">Our team will review your ticket within 24 hours</li><li style=\"margin: 8px 0;\">You will receive email updates on any progress</li><li style=\"margin: 8px 0;\">You can track your ticket status online anytime</li><li style=\"margin: 8px 0;\">Additional information may be requested if needed</li></ul></div><div style=\"text-align: center; margin: 35px 0;\"><a href=\"{{view_url}}\" style=\"display: inline-block; background: linear-gradient(135deg, #007bff 0%, #0056b3 100%); color: white; padding: 15px 30px; text-decoration: none; border-radius: 8px; font-weight: bold; font-size: 16px; box-shadow: 0 4px 8px rgba(0,123,255,0.3);\">­ƒöì View & Track Your Ticket</a></div><div style=\"text-align: center; margin: 20px 0;\"><a href=\"{{login_url}}\" style=\"display: inline-block; background-color: #6c757d; color: white; padding: 12px 25px; text-decoration: none; border-radius: 6px; font-weight: bold; margin: 5px;\">­ƒöÉ Login to Your Account</a></div><div style=\"background-color: #fff3cd; border: 1px solid #ffeaa7; border-radius: 8px; padding: 15px; margin: 25px 0;\"><p style=\"margin: 0; color: #856404; font-size: 14px;\"><strong>­ƒÆí Tip:</strong> Save this email for your records. You can use your ticket ID <strong>#{{complaint_id}}</strong> to track progress anytime.</p></div></div><footer style=\"background-color: #333; color: white; text-align: center; padding: 25px 20px; font-size: 12px;\"><p style=\"margin: 0 0 10px 0;\"><strong>{{app_name}} Support Team</strong></p><p style=\"margin: 0; opacity: 0.8;\">This is an automated message. Please do not reply directly to this email.</p><p style=\"margin: 10px 0 0 0; opacity: 0.6;\">┬® 2024 {{app_name}}. All rights reserved.</p></footer></div></body></html>', 'Ticket Created Successfully! Dear {{customer_name}}, Your support ticket #{{complaint_id}} has been created successfully for {{company_name}}. Track your ticket: {{view_url}} Login: {{login_url}} Thank you for using {{app_name}}.', '2025-09-17 11:55:38'),
(2, 'Registration Approval Notification', 'registration_approved', 1, 'Account Approved - Welcome to {{app_name}}', NULL, '<!DOCTYPE html><html><head><meta charset=\"utf-8\"><meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\"></head><body style=\"margin: 0; padding: 0; font-family: Arial, sans-serif; background-color: #f5f5f5;\"><div style=\"max-width: 600px; margin: 0 auto; background-color: #ffffff; box-shadow: 0 4px 6px rgba(0,0,0,0.1);\"><header style=\"background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px 20px; text-align: center;\"><h1 style=\"margin: 0; font-size: 28px; font-weight: bold;\">{{app_name}}</h1><p style=\"margin: 5px 0 0 0; opacity: 0.9; font-size: 14px;\">Support & Mediation Portal</p></header><div style=\"padding: 30px 20px;\"><div style=\"background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: white; padding: 25px; border-radius: 12px; text-align: center; margin-bottom: 30px;\"><h2 style=\"margin: 0; font-size: 28px;\">­ƒÄë Welcome Aboard!</h2><p style=\"margin: 15px 0 0 0; font-size: 18px; opacity: 0.9;\">Your registration has been approved</p></div><h2 style=\"color: #28a745; margin-bottom: 20px;\">Dear {{customer_name}},</h2><p style=\"font-size: 16px; line-height: 1.6; color: #333;\">Congratulations! Your registration for {{app_name}} has been <strong>approved</strong> by the divisional administrator.</p><div style=\"background-color: #f8f9fa; border-left: 4px solid #17a2b8; padding: 20px; margin: 25px 0; border-radius: 0 8px 8px 0;\"><h3 style=\"margin-top: 0; color: #17a2b8; font-size: 18px;\">­ƒöÉ Your Login Credentials</h3><p style=\"margin: 8px 0;\"><strong>Email/Username:</strong> {{email}}</p><p style=\"margin: 8px 0;\"><strong>Password:</strong> Use the password you provided during registration</p><p style=\"margin: 8px 0;\"><strong>Division:</strong> {{division}}</p></div><div style=\"background-color: #e8f5e8; border: 1px solid #28a745; border-radius: 8px; padding: 20px; margin: 25px 0;\"><h3 style=\"margin-top: 0; color: #155724;\">­ƒÜÇ You can now:</h3><ul style=\"margin: 10px 0; padding-left: 20px; color: #155724;\"><li style=\"margin: 8px 0;\">Create and track support tickets</li><li style=\"margin: 8px 0;\">View your complete ticket history</li><li style=\"margin: 8px 0;\">Update your profile information</li><li style=\"margin: 8px 0;\">Access all SAMPARK services</li><li style=\"margin: 8px 0;\">Receive real-time notifications</li></ul></div><div style=\"text-align: center; margin: 35px 0;\"><a href=\"{{login_url}}\" style=\"display: inline-block; background: linear-gradient(135deg, #007bff 0%, #0056b3 100%); color: white; padding: 15px 30px; text-decoration: none; border-radius: 8px; font-weight: bold; font-size: 16px; box-shadow: 0 4px 8px rgba(0,123,255,0.3);\">­ƒöÉ Login to Your Account</a></div><div style=\"background-color: #d1ecf1; border: 1px solid #bee5eb; border-radius: 8px; padding: 15px; margin: 25px 0;\"><p style=\"margin: 0; color: #0c5460; font-size: 14px;\"><strong>­ƒÆí Getting Started:</strong> After logging in, explore the dashboard to familiarize yourself with all available features.</p></div></div><footer style=\"background-color: #333; color: white; text-align: center; padding: 25px 20px; font-size: 12px;\"><p style=\"margin: 0 0 10px 0;\"><strong>{{app_name}} Support Team</strong></p><p style=\"margin: 0; opacity: 0.8;\">This is an automated message. Please do not reply directly to this email.</p><p style=\"margin: 10px 0 0 0; opacity: 0.6;\">┬® 2024 {{app_name}}. All rights reserved.</p></footer></div></body></html>', '­Registration Approved! Dear {{customer_name}}, Your {{app_name}} account has been approved! Login with {{email}} at {{login_url}}. You can now create tickets, track history, and access all services. Welcome aboard!', '2025-09-17 11:55:46'),
(3, 'Information Needed Notification', 'awaiting_info', 1, 'Additional Information Required - Ticket #{{complaint_id}}', NULL, '<!DOCTYPE html><html><head><meta charset=\"utf-8\"><meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\"></head><body style=\"margin: 0; padding: 0; font-family: Arial, sans-serif; background-color: #f5f5f5;\"><div style=\"max-width: 600px; margin: 0 auto; background-color: #ffffff; box-shadow: 0 4px 6px rgba(0,0,0,0.1);\"><header style=\"background: linear-gradient(135deg, #ffc107 0%, #fd7e14 100%); color: white; padding: 30px 20px; text-align: center;\"><h1 style=\"margin: 0; font-size: 28px; font-weight: bold;\">{{app_name}}</h1><p style=\"margin: 5px 0 0 0; opacity: 0.9; font-size: 14px;\">Support & Mediation Portal</p></header><div style=\"padding: 30px 20px;\"><div style=\"background: linear-gradient(135deg, #ffc107 0%, #fd7e14 100%); color: white; padding: 20px; border-radius: 8px; text-align: center; margin-bottom: 30px;\"><h2 style=\"margin: 0; font-size: 24px;\">­ƒôï Additional Information Needed</h2><p style=\"margin: 10px 0 0 0; font-size: 16px; opacity: 0.9;\">Please provide more details for your ticket</p></div><h2 style=\"color: #fd7e14; margin-bottom: 20px;\">Dear {{customer_name}},</h2><p style=\"font-size: 16px; line-height: 1.6; color: #333;\">We are working on your support ticket and need additional information to proceed effectively.</p><div style=\"background-color: #f8f9fa; border-left: 4px solid #ffc107; padding: 20px; margin: 25px 0; border-radius: 0 8px 8px 0;\"><h3 style=\"margin-top: 0; color: #ffc107; font-size: 18px;\">­ƒÄ½ Ticket Information</h3><p style=\"margin: 8px 0;\"><strong>Ticket ID:</strong> <span style=\"color: #fd7e14; font-weight: bold;\">#{{complaint_id}}</span></p><p style=\"margin: 8px 0;\"><strong>Company:</strong> {{company_name}}</p><p style=\"margin: 8px 0;\"><strong>Status:</strong> <span style=\"background: #ffc107; color: #212529; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: bold;\">AWAITING INFO</span></p></div><div style=\"background-color: #fff3cd; border: 1px solid #ffeaa7; border-radius: 8px; padding: 20px; margin: 25px 0;\"><h3 style=\"margin-top: 0; color: #856404;\">Ôä╣´©Å What we need from you:</h3><div style=\"background-color: #ffffff; padding: 15px; border-radius: 6px; margin: 15px 0; border: 1px solid #fed136;\"><p style=\"margin: 0; color: #333; font-size: 14px; font-style: italic;\">{{additional_info_request}}</p></div><p style=\"margin: 15px 0 0 0; color: #856404; font-size: 14px;\">Please provide the requested information to help us resolve your issue quickly.</p></div><div style=\"text-align: center; margin: 35px 0;\"><a href=\"{{view_url}}\" style=\"display: inline-block; background: linear-gradient(135deg, #ffc107 0%, #fd7e14 100%); color: white; padding: 15px 30px; text-decoration: none; border-radius: 8px; font-weight: bold; font-size: 16px; box-shadow: 0 4px 8px rgba(255,193,7,0.3);\">­ƒôØ Provide Information</a></div><div style=\"text-align: center; margin: 20px 0;\"><a href=\"{{login_url}}\" style=\"display: inline-block; background-color: #6c757d; color: white; padding: 12px 25px; text-decoration: none; border-radius: 6px; font-weight: bold; margin: 5px;\">­ƒöÉ Login to Your Account</a></div><div style=\"background-color: #f8d7da; border: 1px solid #f5c6cb; border-radius: 8px; padding: 15px; margin: 25px 0;\"><p style=\"margin: 0; color: #721c24; font-size: 14px;\"><strong>ÔÜá´©Å Important:</strong> Your ticket will remain on hold until we receive the requested information. Please respond at your earliest convenience.</p></div></div><footer style=\"background-color: #333; color: white; text-align: center; padding: 25px 20px; font-size: 12px;\"><p style=\"margin: 0 0 10px 0;\"><strong>{{app_name}} Support Team</strong></p><p style=\"margin: 0; opacity: 0.8;\">This is an automated message. Please do not reply directly to this email.</p><p style=\"margin: 10px 0 0 0; opacity: 0.6;\">┬® 2024 {{app_name}}. All rights reserved.</p></footer></div></body></html>', '­Information Needed - Ticket #{{complaint_id}}. Dear {{customer_name}}, We need additional information for your ticket #{{complaint_id}} at {{company_name}}. Please provide details at: {{view_url}} or login: {{login_url}}. Your ticket is on hold until we receive the information.', '2025-09-17 11:55:52'),
(4, 'Feedback Needed Notification', 'awaiting_feedback', 1, 'Your Feedback Required - Ticket #{{complaint_id}}', NULL, '<!DOCTYPE html><html><head><meta charset=\"utf-8\"><meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\"></head><body style=\"margin: 0; padding: 0; font-family: Arial, sans-serif; background-color: #f5f5f5;\"><div style=\"max-width: 600px; margin: 0 auto; background-color: #ffffff; box-shadow: 0 4px 6px rgba(0,0,0,0.1);\"><header style=\"background: linear-gradient(135deg, #6f42c1 0%, #e83e8c 100%); color: white; padding: 30px 20px; text-align: center;\"><h1 style=\"margin: 0; font-size: 28px; font-weight: bold;\">{{app_name}}</h1><p style=\"margin: 5px 0 0 0; opacity: 0.9; font-size: 14px;\">Support & Mediation Portal</p></header><div style=\"padding: 30px 20px;\"><div style=\"background: linear-gradient(135deg, #6f42c1 0%, #e83e8c 100%); color: white; padding: 20px; border-radius: 8px; text-align: center; margin-bottom: 30px;\"><h2 style=\"margin: 0; font-size: 24px;\">­ƒÆ¼ Your Feedback is Needed</h2><p style=\"margin: 10px 0 0 0; font-size: 16px; opacity: 0.9;\">Help us finalize your support request</p></div><h2 style=\"color: #6f42c1; margin-bottom: 20px;\">Dear {{customer_name}},</h2><p style=\"font-size: 16px; line-height: 1.6; color: #333;\">We have made progress on your support ticket and would like your feedback to ensure everything meets your expectations.</p><div style=\"background-color: #f8f9fa; border-left: 4px solid #6f42c1; padding: 20px; margin: 25px 0; border-radius: 0 8px 8px 0;\"><h3 style=\"margin-top: 0; color: #6f42c1; font-size: 18px;\">­ƒÄ½ Ticket Summary</h3><p style=\"margin: 8px 0;\"><strong>Ticket ID:</strong> <span style=\"color: #6f42c1; font-weight: bold;\">#{{complaint_id}}</span></p><p style=\"margin: 8px 0;\"><strong>Company:</strong> {{company_name}}</p><p style=\"margin: 8px 0;\"><strong>Status:</strong> <span style=\"background: #6f42c1; color: white; padding: 4px 8px; border-radius: 4px; font-size: 12px;\">AWAITING FEEDBACK</span></p></div><div style=\"background-color: #f3e5f5; border: 1px solid #d1b7d8; border-radius: 8px; padding: 20px; margin: 25px 0;\"><h3 style=\"margin-top: 0; color: #6f42c1;\">­ƒôØ Recent Update</h3><div style=\"background-color: #ffffff; padding: 15px; border-radius: 6px; margin: 15px 0; border: 1px solid #d1b7d8;\"><p style=\"margin: 0; color: #333; font-size: 14px;\">{{recent_update}}</p></div><p style=\"margin: 15px 0 0 0; color: #6f42c1; font-size: 14px; font-weight: bold;\">Please review this update and let us know if this resolves your issue or if further assistance is needed.</p></div><div style=\"background-color: #e8f4fd; border: 1px solid #bee5eb; border-radius: 8px; padding: 20px; margin: 25px 0;\"><h3 style=\"margin-top: 0; color: #0c5460;\">ÔØô Please tell us:</h3><ul style=\"margin: 10px 0; padding-left: 20px; color: #0c5460;\"><li style=\"margin: 8px 0;\">Does this solution meet your needs?</li><li style=\"margin: 8px 0;\">Is there anything else we can help with?</li><li style=\"margin: 8px 0;\">Are you satisfied with the resolution?</li><li style=\"margin: 8px 0;\">Can we close this ticket?</li></ul></div><div style=\"text-align: center; margin: 35px 0;\"><a href=\"{{view_url}}\" style=\"display: inline-block; background: linear-gradient(135deg, #6f42c1 0%, #e83e8c 100%); color: white; padding: 15px 30px; text-decoration: none; border-radius: 8px; font-weight: bold; font-size: 16px; box-shadow: 0 4px 8px rgba(111,66,193,0.3);\">­ƒÆ¼ Provide Feedback</a></div><div style=\"text-align: center; margin: 20px 0;\"><a href=\"{{login_url}}\" style=\"display: inline-block; background-color: #6c757d; color: white; padding: 12px 25px; text-decoration: none; border-radius: 6px; font-weight: bold; margin: 5px;\">­ƒöÉ Login to Your Account</a></div><div style=\"background-color: #d4edda; border: 1px solid #c3e6cb; border-radius: 8px; padding: 15px; margin: 25px 0;\"><p style=\"margin: 0; color: #155724; font-size: 14px;\"><strong>Ô£à Almost Done:</strong> Your feedback will help us close this ticket and improve our service quality.</p></div></div><footer style=\"background-color: #333; color: white; text-align: center; padding: 25px 20px; font-size: 12px;\"><p style=\"margin: 0 0 10px 0;\"><strong>{{app_name}} Support Team</strong></p><p style=\"margin: 0; opacity: 0.8;\">This is an automated message. Please do not reply directly to this email.</p><p style=\"margin: 10px 0 0 0; opacity: 0.6;\">┬® 2024 {{app_name}}. All rights reserved.</p></footer></div></body></html>', '­Feedback Needed - Ticket #{{complaint_id}}. Dear {{customer_name}}, We need your feedback on ticket #{{complaint_id}} at {{company_name}}. Please review our latest update and let us know if the issue is resolved. Respond at: {{view_url}} or login: {{login_url}}', '2025-09-17 11:55:56'),
(5, 'Signup Approved Notification', 'signup_approved', 1, 'Account Approved - Welcome to {{app_name}}', NULL, '<h2>Account Approved</h2><p>Dear {{customer_name}},</p><p>Your account has been approved and you can now access all features of {{app_name}}.</p><p><a href=\"{{login_url}}\" class=\"btn btn-primary\">Login Now</a></p><p>Best regards,<br>{{app_name}} Team</p>', 'Account Approved - Dear {{customer_name}}, Your account has been approved. Login at: {{login_url}}', '2025-09-17 12:03:18'),
(6, 'Ticket Awaiting Information', 'ticket_awaiting_info', 1, 'Additional Information Required - Ticket {{complaint_id}}', NULL, '<h2>Additional Information Required</h2><p>Dear {{customer_name}},</p><p>We need additional information to process your ticket #{{complaint_id}}.</p><p><strong>Request:</strong> {{message}}</p><p><a href=\"{{view_url}}\" class=\"btn btn-primary\">Provide Information</a></p><p>Best regards,<br>{{app_name}} Team</p>', 'Additional Information Required - Ticket #{{complaint_id}}. Please provide: {{message}}. Visit: {{view_url}}', '2025-09-17 12:03:18'),
(7, 'Ticket Awaiting Feedback', 'ticket_awaiting_feedback', 1, 'Feedback Required - Ticket {{complaint_id}}', NULL, '<h2>Feedback Required</h2><p>Dear {{customer_name}},</p><p>Your ticket #{{complaint_id}} has been resolved. Please provide your feedback.</p><p><strong>Resolution:</strong> {{message}}</p><p><a href=\"{{view_url}}\" class=\"btn btn-primary\">Provide Feedback</a></p><p>Best regards,<br>{{app_name}} Team</p>', 'Feedback Required - Ticket #{{complaint_id}} has been resolved. Please provide feedback at: {{view_url}}', '2025-09-17 12:03:18'),
(8, 'Ticket Assigned Notification', 'ticket_assigned', 1, 'New Ticket Assigned - {{complaint_id}}', NULL, '<h2>New Ticket Assignment</h2><p>Dear Team Member,</p><p>Ticket #{{complaint_id}} has been assigned to you.</p><p><strong>Customer:</strong> {{customer_name}}</p><p><strong>Priority:</strong> {{priority}}</p><p><a href=\"{{view_url}}\" class=\"btn btn-primary\">View Ticket</a></p>', 'New Ticket #{{complaint_id}} assigned to you from {{customer_name}}. Priority: {{priority}}. View: {{view_url}}', '2025-09-17 12:03:18'),
(9, 'Priority Escalation Notification', 'priority_escalated', 1, 'Ticket {{complaint_id}} Priority Escalated to {{priority}}', NULL, '<h2>Priority Escalation Alert</h2><p>Ticket #{{complaint_id}} has been escalated to <strong>{{priority}}</strong> priority.</p><p><strong>Reason:</strong> {{escalation_reason}}</p><p><a href=\"{{view_url}}\" class=\"btn btn-primary\">View Ticket</a></p>', 'Ticket #{{complaint_id}} escalated to {{priority}} priority. Reason: {{escalation_reason}}. View: {{view_url}}', '2025-09-17 12:03:18'),
(10, 'Information Provided Notification', 'info_provided', 1, 'Information Provided - Ticket {{complaint_id}}', NULL, '<h2>Information Provided</h2><p>Dear Team Member,</p><p>Customer has provided additional information for ticket #{{complaint_id}}.</p><p><strong>Information:</strong> {{additional_info}}</p><p><a href=\"{{view_url}}\" class=\"btn btn-primary\">Review Ticket</a></p>', 'Information provided for ticket #{{complaint_id}}: {{additional_info}}. Review at: {{view_url}}', '2025-09-17 12:03:18'),
(11, 'Ticket Escalated Notification', 'ticket_escalated', 1, 'Ticket {{complaint_id}} Escalated', NULL, '<h2>Ticket Escalated</h2><p>Ticket #{{complaint_id}} has been escalated due to SLA breach.</p><p><a href=\"{{view_url}}\" class=\"btn btn-primary\">View Ticket</a></p>', 'Ticket #{{complaint_id}} has been escalated. View: {{view_url}}', '2025-09-17 12:03:18'),
(12, 'Ticket Auto-Closed Notification', 'ticket_auto_closed', 1, 'Ticket {{complaint_id}} Auto-Closed', NULL, '<h2>Ticket Auto-Closed</h2><p>Ticket #{{complaint_id}} has been automatically closed due to inactivity.</p><p><a href=\"{{view_url}}\" class=\"btn btn-primary\">View Ticket</a></p>', 'Ticket #{{complaint_id}} has been auto-closed. View: {{view_url}}', '2025-09-17 12:03:18'),
(13, 'Ticket Forwarded Notification', 'ticket_forwarded', 1, 'Ticket {{complaint_id}} Forwarded', NULL, '<h2>Ticket Forwarded</h2><p>Ticket #{{complaint_id}} has been forwarded to {{to_department}} department.</p><p><strong>Reason:</strong> {{remarks}}</p><p><a href=\"{{view_url}}\" class=\"btn btn-primary\">View Ticket</a></p>', 'Ticket #{{complaint_id}} forwarded to {{to_department}}. Reason: {{remarks}}. View: {{view_url}}', '2025-09-17 12:03:18'),
(14, 'Reply Approved Notification', 'reply_approved', 1, 'Reply Approved - Ticket {{complaint_id}}', NULL, '<h2>Reply Approved</h2><p>Dear {{customer_name}},</p><p>Your reply for ticket #{{complaint_id}} has been approved.</p><p><a href=\"{{view_url}}\" class=\"btn btn-primary\">View Ticket</a></p>', 'Reply approved for ticket #{{complaint_id}}. View: {{view_url}}', '2025-09-17 12:03:18'),
(15, 'Reply Rejected Notification', 'reply_rejected', 1, 'Reply Rejected - Ticket {{complaint_id}}', NULL, '<h2>Reply Rejected</h2><p>Dear Team Member,</p><p>Your reply for ticket #{{complaint_id}} has been rejected.</p><p><strong>Reason:</strong> {{reason}}</p><p><a href=\"{{view_url}}\" class=\"btn btn-primary\">View Ticket</a></p>', 'Reply rejected for ticket #{{complaint_id}}. Reason: {{reason}}. View: {{view_url}}', '2025-09-17 12:03:18'),
(16, 'Information Requested Notification', 'info_requested', 1, 'Information Requested - Ticket {{complaint_id}}', NULL, '<h2>Information Requested</h2><p>Dear Team Member,</p><p>Additional information has been requested for ticket #{{complaint_id}}.</p><p><strong>Request:</strong> {{info_request}}</p><p><a href=\"{{view_url}}\" class=\"btn btn-primary\">View Ticket</a></p>', 'Information requested for ticket #{{complaint_id}}: {{info_request}}. View: {{view_url}}', '2025-09-17 12:03:18'),
(17, 'Ticket Closed Notification', 'ticket_closed', 1, 'Ticket {{complaint_id}} Closed', NULL, '<h2>Ticket Closed</h2><p>Dear {{customer_name}},</p><p>Your ticket #{{complaint_id}} has been closed.</p><p><strong>Resolution:</strong> {{action_taken}}</p><p><a href=\"{{view_url}}\" class=\"btn btn-primary\">View Ticket</a></p>', 'Ticket #{{complaint_id}} closed. Resolution: {{action_taken}}. View: {{view_url}}', '2025-09-17 12:03:18'),
(18, 'Closed Ticket Approval Needed', 'closed_ticket_approval_needed', 1, 'Ticket {{complaint_id}} Closed - Approval Needed', NULL, '<h2>Ticket Closed - Approval Needed</h2><p>Dear Team Member,</p><p>Ticket #{{complaint_id}} has been closed and requires your approval.</p><p><strong>Closed by:</strong> {{closed_by}}</p><p><a href=\"{{view_url}}\" class=\"btn btn-primary\">Review Ticket</a></p>', 'Ticket #{{complaint_id}} closed by {{closed_by}} - approval needed. Review: {{view_url}}', '2025-09-17 12:03:18'),
(19, 'Interim Remarks Added', 'interim_remarks_added', 1, 'Interim Remarks Added - Ticket {{complaint_id}}', NULL, '<h2>Interim Remarks Added</h2><p>Dear Team Member,</p><p>Interim remarks have been added to ticket #{{complaint_id}}.</p><p><strong>Remarks:</strong> {{remarks}}</p><p><a href=\"{{view_url}}\" class=\"btn btn-primary\">View Ticket</a></p>', 'Interim remarks added to ticket #{{complaint_id}}: {{remarks}}. View: {{view_url}}', '2025-09-17 12:03:19'),
(20, 'Weekly Report Notification', 'weekly_report', 1, 'Weekly Report - {{current_date}}', NULL, '<h2>Weekly Report</h2><p>Dear Team,</p><p>Here is your weekly report for the period ending {{current_date}}.</p><p><strong>Summary:</strong> {{summary}}</p><p><a href=\"{{report_url}}\" class=\"btn btn-primary\">View Full Report</a></p>', 'Weekly Report for {{current_date}}. Summary: {{summary}}. View: {{report_url}}', '2025-09-17 12:03:37'),
(21, 'Monthly Report Notification', 'monthly_report', 1, 'Monthly Report - {{current_date}}', NULL, '<h2>Monthly Report</h2><p>Dear Team,</p><p>Here is your monthly report for {{current_date}}.</p><p><strong>Summary:</strong> {{summary}}</p><p><a href=\"{{report_url}}\" class=\"btn btn-primary\">View Full Report</a></p>', 'Monthly Report for {{current_date}}. Summary: {{summary}}. View: {{report_url}}', '2025-09-17 12:03:37'),
(22, 'Daily Digest Notification', 'daily_digest', 1, 'Daily Digest - {{current_date}}', NULL, '<h2>Daily Digest</h2><p>Dear Team Member,</p><p>Here is your daily digest for {{current_date}}.</p><p><strong>Summary:</strong> {{summary}}</p><p><a href=\"{{digest_url}}\" class=\"btn btn-primary\">View Full Digest</a></p>', 'Daily Digest for {{current_date}}. Summary: {{summary}}. View: {{digest_url}}', '2025-09-17 12:03:37'),
(23, 'Weekly Digest Notification', 'weekly_digest', 1, 'Weekly Digest - {{current_date}}', NULL, '<h2>Weekly Digest</h2><p>Dear Team Member,</p><p>Here is your weekly digest for the week ending {{current_date}}.</p><p><strong>Summary:</strong> {{summary}}</p><p><a href=\"{{digest_url}}\" class=\"btn btn-primary\">View Full Digest</a></p>', 'Weekly Digest for {{current_date}}. Summary: {{summary}}. View: {{digest_url}}', '2025-09-17 12:03:37'),
(24, 'SLA Warning Notification', 'sla_warning', 1, 'SLA Warning - Ticket {{complaint_id}}', NULL, '<h2>SLA Warning</h2><p>Dear Team Member,</p><p>Ticket #{{complaint_id}} is approaching SLA deadline.</p><p><strong>Time Remaining:</strong> {{time_remaining}}</p><p><a href=\"{{view_url}}\" class=\"btn btn-primary\">View Ticket</a></p>', 'SLA Warning - Ticket #{{complaint_id}} approaching deadline. Time remaining: {{time_remaining}}. View: {{view_url}}', '2025-09-17 12:03:37');

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
-- Dumping data for table `evidence`
--

INSERT INTO `evidence` (`id`, `complaint_id`, `file_name_1`, `file_name_2`, `file_name_3`, `file_type_1`, `file_type_2`, `file_type_3`, `file_path_1`, `file_path_2`, `file_path_3`, `compressed_size_1`, `compressed_size_2`, `compressed_size_3`, `additional_file_name_1`, `additional_file_name_2`, `additional_file_type_1`, `additional_file_type_2`, `additional_file_path_1`, `additional_file_path_2`, `additional_compressed_size_1`, `additional_compressed_size_2`, `additional_files_uploaded_at`, `uploaded_by_type`, `uploaded_by_id`, `uploaded_at`) VALUES
(0, '202509170005', '202509170005_file1.jpg', NULL, NULL, 'jpg', NULL, NULL, '202509170005_file1.jpg', NULL, NULL, 3090212, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'customer', 'CUST2025090001', '2025-09-17 06:47:06'),
(1, '202509150003', '202509150003_file1.jpg', NULL, NULL, 'jpg', NULL, NULL, '202509150003_file1.jpg', NULL, NULL, 76637, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'customer', 'CUST2025090001', '2025-09-15 07:28:39');

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
  `user_type` enum('customer','controller','controller_nodal','admin','superadmin') DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `type` enum('info','success','warning','error','escalation','ticket_created','ticket_updated','ticket_assigned','ticket_replied','ticket_resolved','ticket_escalated','priority_escalated','system_announcement','maintenance_alert','sla_warning','account_update') DEFAULT 'info',
  `priority` enum('low','medium','high','urgent','critical') DEFAULT 'medium',
  `complaint_id` varchar(20) DEFAULT NULL,
  `related_id` varchar(50) DEFAULT NULL,
  `related_type` varchar(50) DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `action_url` varchar(500) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `read_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`metadata`)),
  `dismissed_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `customer_id`, `user_type`, `title`, `message`, `type`, `priority`, `complaint_id`, `related_id`, `related_type`, `is_read`, `action_url`, `created_at`, `read_at`, `expires_at`, `metadata`, `dismissed_at`, `updated_at`) VALUES
(1, 5, NULL, 'controller_nodal', 'TEST', 'TESTING this new function.', 'system_announcement', 'medium', NULL, NULL, NULL, 1, NULL, '2025-09-17 09:33:19', '2025-09-17 09:41:47', '2025-09-17 14:32:00', NULL, NULL, '2025-09-17 09:41:47'),
(2, 6, NULL, 'controller_nodal', 'TEST', 'TESTING this new function.', 'system_announcement', 'medium', NULL, NULL, NULL, 0, NULL, '2025-09-17 09:33:19', NULL, '2025-09-17 14:32:00', NULL, NULL, '2025-09-17 09:33:19'),
(3, 7, NULL, 'controller_nodal', 'TEST', 'TESTING this new function.', 'system_announcement', 'medium', NULL, NULL, NULL, 0, NULL, '2025-09-17 09:33:19', NULL, '2025-09-17 14:32:00', NULL, NULL, '2025-09-17 09:33:19'),
(4, 8, NULL, 'controller_nodal', 'TEST', 'TESTING this new function.', 'system_announcement', 'medium', NULL, NULL, NULL, 0, NULL, '2025-09-17 09:33:20', NULL, '2025-09-17 14:32:00', NULL, NULL, '2025-09-17 09:33:20'),
(5, 9, NULL, 'controller_nodal', 'TEST', 'TESTING this new function.', 'system_announcement', 'medium', NULL, NULL, NULL, 0, NULL, '2025-09-17 09:33:20', NULL, '2025-09-17 14:32:00', NULL, NULL, '2025-09-17 09:33:20'),
(6, 5, NULL, 'controller_nodal', 'TEST', 'TESTING 2', 'system_announcement', 'medium', NULL, NULL, NULL, 1, NULL, '2025-09-17 09:42:09', '2025-09-17 09:42:35', '2025-09-17 14:41:00', NULL, NULL, '2025-09-17 09:42:35'),
(7, 6, NULL, 'controller_nodal', 'TEST', 'TESTING 2', 'system_announcement', 'medium', NULL, NULL, NULL, 0, NULL, '2025-09-17 09:42:09', NULL, '2025-09-17 14:41:00', NULL, NULL, '2025-09-17 09:42:09'),
(8, 7, NULL, 'controller_nodal', 'TEST', 'TESTING 2', 'system_announcement', 'medium', NULL, NULL, NULL, 0, NULL, '2025-09-17 09:42:09', NULL, '2025-09-17 14:41:00', NULL, NULL, '2025-09-17 09:42:09'),
(9, 8, NULL, 'controller_nodal', 'TEST', 'TESTING 2', 'system_announcement', 'medium', NULL, NULL, NULL, 0, NULL, '2025-09-17 09:42:09', NULL, '2025-09-17 14:41:00', NULL, NULL, '2025-09-17 09:42:09'),
(10, 9, NULL, 'controller_nodal', 'TEST', 'TESTING 2', 'system_announcement', 'medium', NULL, NULL, NULL, 0, NULL, '2025-09-17 09:42:09', NULL, '2025-09-17 14:41:00', NULL, NULL, '2025-09-17 09:42:09'),
(11, NULL, 'CUST2025090001', NULL, 'ticket_created notification', 'Notification sent via ', 'info', 'medium', NULL, NULL, NULL, 1, NULL, '2025-09-17 09:42:58', '2025-09-17 10:08:13', NULL, NULL, NULL, '2025-09-17 10:08:13'),
(12, 5, NULL, NULL, 'New Ticket Created: #202509170006', 'A new ticket has been created in your division (BB).', '', 'medium', NULL, '202509170006', 'ticket', 1, 'http://localhost/testfs/controller/tickets/202509170006', '2025-09-17 09:42:58', '2025-09-17 09:43:12', NULL, NULL, NULL, '2025-09-17 09:43:12'),
(13, 3, NULL, NULL, 'Priority Escalation: Ticket #202509170003', 'The priority for ticket #202509170003 has been escalated.', '', 'high', NULL, '202509170003', 'ticket', 1, 'http://localhost/testfs/controller/tickets/202509170003', '2025-09-17 10:07:05', '2025-09-17 12:04:15', NULL, NULL, NULL, '2025-09-17 12:04:15'),
(14, 5, NULL, NULL, 'Priority Escalation: Ticket #202509170003', 'The priority for ticket #202509170003 has been escalated.', '', 'high', NULL, '202509170003', 'ticket', 1, 'http://localhost/testfs/controller/tickets/202509170003', '2025-09-17 10:07:05', '2025-09-17 10:23:56', NULL, NULL, NULL, '2025-09-17 10:23:56'),
(15, 6, NULL, NULL, 'Priority Escalation: Ticket #202509170003', 'The priority for ticket #202509170003 has been escalated.', '', 'high', NULL, '202509170003', 'ticket', 0, 'http://localhost/testfs/controller/tickets/202509170003', '2025-09-17 10:07:05', NULL, NULL, NULL, NULL, '2025-09-17 10:07:05'),
(16, 7, NULL, NULL, 'Priority Escalation: Ticket #202509170003', 'The priority for ticket #202509170003 has been escalated.', '', 'high', NULL, '202509170003', 'ticket', 0, 'http://localhost/testfs/controller/tickets/202509170003', '2025-09-17 10:07:05', NULL, NULL, NULL, NULL, '2025-09-17 10:07:05'),
(17, 8, NULL, NULL, 'Priority Escalation: Ticket #202509170003', 'The priority for ticket #202509170003 has been escalated.', '', 'high', NULL, '202509170003', 'ticket', 0, 'http://localhost/testfs/controller/tickets/202509170003', '2025-09-17 10:07:05', NULL, NULL, NULL, NULL, '2025-09-17 10:07:05'),
(18, 9, NULL, NULL, 'Priority Escalation: Ticket #202509170003', 'The priority for ticket #202509170003 has been escalated.', '', 'high', NULL, '202509170003', 'ticket', 0, 'http://localhost/testfs/controller/tickets/202509170003', '2025-09-17 10:07:05', NULL, NULL, NULL, NULL, '2025-09-17 10:07:05'),
(19, 10, NULL, NULL, 'Priority Escalation: Ticket #202509170003', 'The priority for ticket #202509170003 has been escalated.', '', 'high', NULL, '202509170003', 'ticket', 0, 'http://localhost/testfs/controller/tickets/202509170003', '2025-09-17 10:07:05', NULL, NULL, NULL, NULL, '2025-09-17 10:07:05'),
(20, 11, NULL, NULL, 'Priority Escalation: Ticket #202509170003', 'The priority for ticket #202509170003 has been escalated.', '', 'high', NULL, '202509170003', 'ticket', 0, 'http://localhost/testfs/controller/tickets/202509170003', '2025-09-17 10:07:05', NULL, NULL, NULL, NULL, '2025-09-17 10:07:05'),
(21, 12, NULL, NULL, 'Priority Escalation: Ticket #202509170003', 'The priority for ticket #202509170003 has been escalated.', '', 'high', NULL, '202509170003', 'ticket', 0, 'http://localhost/testfs/controller/tickets/202509170003', '2025-09-17 10:07:05', NULL, NULL, NULL, NULL, '2025-09-17 10:07:05'),
(22, 13, NULL, NULL, 'Priority Escalation: Ticket #202509170003', 'The priority for ticket #202509170003 has been escalated.', '', 'high', NULL, '202509170003', 'ticket', 0, 'http://localhost/testfs/controller/tickets/202509170003', '2025-09-17 10:07:05', NULL, NULL, NULL, NULL, '2025-09-17 10:07:05'),
(23, 14, NULL, NULL, 'Priority Escalation: Ticket #202509170003', 'The priority for ticket #202509170003 has been escalated.', '', 'high', NULL, '202509170003', 'ticket', 0, 'http://localhost/testfs/controller/tickets/202509170003', '2025-09-17 10:07:05', NULL, NULL, NULL, NULL, '2025-09-17 10:07:05'),
(24, 18, NULL, NULL, 'Priority Escalation: Ticket #202509170003', 'The priority for ticket #202509170003 has been escalated.', '', 'high', NULL, '202509170003', 'ticket', 0, 'http://localhost/testfs/controller/tickets/202509170003', '2025-09-17 10:07:05', NULL, NULL, NULL, NULL, '2025-09-17 10:07:05'),
(25, NULL, 'CUST2025090001', NULL, 'ticket_created notification', 'Notification sent via ', 'info', 'medium', NULL, NULL, NULL, 1, NULL, '2025-09-17 10:07:38', '2025-09-17 10:08:07', NULL, NULL, NULL, '2025-09-17 10:08:07'),
(26, 5, NULL, NULL, 'New Ticket Created: #202509170007', 'A new ticket has been created in your division (BB).', '', 'medium', NULL, '202509170007', 'ticket', 1, 'http://localhost/testfs/controller/tickets/202509170007', '2025-09-17 10:07:38', '2025-09-17 10:23:45', NULL, NULL, NULL, '2025-09-17 10:23:45'),
(27, NULL, 'CUST2025090001', NULL, 'Ticket status updated - 202509150005', 'Ticket action completed - 202509150005. Please provide your feedback on the resolution.', '', 'medium', NULL, '202509150005', 'ticket', 1, 'http://10.31.210.225/testfs/customer/tickets/202509150005', '2025-09-17 10:22:11', '2025-09-17 11:36:42', NULL, NULL, NULL, '2025-09-17 11:36:42'),
(28, 3, NULL, NULL, 'Ticket priority escalated - 202509170004', 'Ticket #202509170004 priority has been escalated due to time elapsed. Immediate attention required.', '', 'high', NULL, '202509170004', 'ticket', 1, 'http://10.31.210.225/testfs/controller/tickets/202509170004', '2025-09-17 10:25:09', '2025-09-17 12:04:15', NULL, '{\"admin_remarks\":{\"remarks\":\"Testing admin remarks no 2367\",\"added_by\":\"System Administrator\",\"added_at\":\"2025-09-17 16:05:53\"}}', NULL, '2025-09-17 12:04:15'),
(29, 5, NULL, NULL, 'Ticket priority escalated - 202509170004', 'Ticket #202509170004 priority has been escalated due to time elapsed. Immediate attention required.', '', 'high', NULL, '202509170004', 'ticket', 1, 'http://10.31.210.225/testfs/controller/tickets/202509170004', '2025-09-17 10:25:09', '2025-09-17 11:38:18', NULL, NULL, NULL, '2025-09-17 11:38:18'),
(30, 6, NULL, NULL, 'Ticket priority escalated - 202509170004', 'Ticket #202509170004 priority has been escalated due to time elapsed. Immediate attention required.', '', 'high', NULL, '202509170004', 'ticket', 0, 'http://10.31.210.225/testfs/controller/tickets/202509170004', '2025-09-17 10:25:09', NULL, NULL, NULL, NULL, '2025-09-17 10:25:09'),
(31, 7, NULL, NULL, 'Ticket priority escalated - 202509170004', 'Ticket #202509170004 priority has been escalated due to time elapsed. Immediate attention required.', '', 'high', NULL, '202509170004', 'ticket', 0, 'http://10.31.210.225/testfs/controller/tickets/202509170004', '2025-09-17 10:25:09', NULL, NULL, NULL, NULL, '2025-09-17 10:25:09'),
(32, 8, NULL, NULL, 'Ticket priority escalated - 202509170004', 'Ticket #202509170004 priority has been escalated due to time elapsed. Immediate attention required.', '', 'high', NULL, '202509170004', 'ticket', 0, 'http://10.31.210.225/testfs/controller/tickets/202509170004', '2025-09-17 10:25:09', NULL, NULL, NULL, NULL, '2025-09-17 10:25:09'),
(33, 9, NULL, NULL, 'Ticket priority escalated - 202509170004', 'Ticket #202509170004 priority has been escalated due to time elapsed. Immediate attention required.', '', 'high', NULL, '202509170004', 'ticket', 0, 'http://10.31.210.225/testfs/controller/tickets/202509170004', '2025-09-17 10:25:09', NULL, NULL, NULL, NULL, '2025-09-17 10:25:09'),
(34, 10, NULL, NULL, 'Ticket priority escalated - 202509170004', 'Ticket #202509170004 priority has been escalated due to time elapsed. Immediate attention required.', '', 'high', NULL, '202509170004', 'ticket', 0, 'http://10.31.210.225/testfs/controller/tickets/202509170004', '2025-09-17 10:25:09', NULL, NULL, NULL, NULL, '2025-09-17 10:25:09'),
(35, 11, NULL, NULL, 'Ticket priority escalated - 202509170004', 'Ticket #202509170004 priority has been escalated due to time elapsed. Immediate attention required.', '', 'high', NULL, '202509170004', 'ticket', 0, 'http://10.31.210.225/testfs/controller/tickets/202509170004', '2025-09-17 10:25:09', NULL, NULL, NULL, NULL, '2025-09-17 10:25:09'),
(36, 12, NULL, NULL, 'Ticket priority escalated - 202509170004', 'Ticket #202509170004 priority has been escalated due to time elapsed. Immediate attention required.', '', 'high', NULL, '202509170004', 'ticket', 0, 'http://10.31.210.225/testfs/controller/tickets/202509170004', '2025-09-17 10:25:09', NULL, NULL, NULL, NULL, '2025-09-17 10:25:09'),
(37, 13, NULL, NULL, 'Ticket priority escalated - 202509170004', 'Ticket #202509170004 priority has been escalated due to time elapsed. Immediate attention required.', '', 'high', NULL, '202509170004', 'ticket', 0, 'http://10.31.210.225/testfs/controller/tickets/202509170004', '2025-09-17 10:25:09', NULL, NULL, NULL, NULL, '2025-09-17 10:25:09'),
(38, 14, NULL, NULL, 'Ticket priority escalated - 202509170004', 'Ticket #202509170004 priority has been escalated due to time elapsed. Immediate attention required.', '', 'high', NULL, '202509170004', 'ticket', 0, 'http://10.31.210.225/testfs/controller/tickets/202509170004', '2025-09-17 10:25:09', NULL, NULL, NULL, NULL, '2025-09-17 10:25:09'),
(39, 18, NULL, NULL, 'Ticket priority escalated - 202509170004', 'Ticket #202509170004 priority has been escalated due to time elapsed. Immediate attention required.', '', 'high', NULL, '202509170004', 'ticket', 0, 'http://10.31.210.225/testfs/controller/tickets/202509170004', '2025-09-17 10:25:09', NULL, NULL, NULL, NULL, '2025-09-17 10:25:09'),
(40, 3, NULL, NULL, 'Ticket priority escalated - 202509170005', 'Ticket #202509170005 priority has been escalated due to time elapsed. Immediate attention required.', '', 'high', NULL, '202509170005', 'ticket', 1, 'http://10.31.210.225/testfs/controller/tickets/202509170005', '2025-09-17 10:58:42', '2025-09-17 12:04:15', NULL, NULL, NULL, '2025-09-17 12:04:15'),
(41, 5, NULL, NULL, 'Ticket priority escalated - 202509170005', 'Ticket #202509170005 priority has been escalated due to time elapsed. Immediate attention required.', '', 'high', NULL, '202509170005', 'ticket', 1, 'http://10.31.210.225/testfs/controller/tickets/202509170005', '2025-09-17 10:58:42', '2025-09-17 11:38:18', NULL, NULL, NULL, '2025-09-17 11:38:18'),
(42, 6, NULL, NULL, 'Ticket priority escalated - 202509170005', 'Ticket #202509170005 priority has been escalated due to time elapsed. Immediate attention required.', '', 'high', NULL, '202509170005', 'ticket', 0, 'http://10.31.210.225/testfs/controller/tickets/202509170005', '2025-09-17 10:58:42', NULL, NULL, NULL, NULL, '2025-09-17 10:58:42'),
(43, 7, NULL, NULL, 'Ticket priority escalated - 202509170005', 'Ticket #202509170005 priority has been escalated due to time elapsed. Immediate attention required.', '', 'high', NULL, '202509170005', 'ticket', 0, 'http://10.31.210.225/testfs/controller/tickets/202509170005', '2025-09-17 10:58:42', NULL, NULL, NULL, NULL, '2025-09-17 10:58:42'),
(44, 8, NULL, NULL, 'Ticket priority escalated - 202509170005', 'Ticket #202509170005 priority has been escalated due to time elapsed. Immediate attention required.', '', 'high', NULL, '202509170005', 'ticket', 0, 'http://10.31.210.225/testfs/controller/tickets/202509170005', '2025-09-17 10:58:42', NULL, NULL, NULL, NULL, '2025-09-17 10:58:42'),
(45, 9, NULL, NULL, 'Ticket priority escalated - 202509170005', 'Ticket #202509170005 priority has been escalated due to time elapsed. Immediate attention required.', '', 'high', NULL, '202509170005', 'ticket', 0, 'http://10.31.210.225/testfs/controller/tickets/202509170005', '2025-09-17 10:58:42', NULL, NULL, NULL, NULL, '2025-09-17 10:58:42'),
(46, 10, NULL, NULL, 'Ticket priority escalated - 202509170005', 'Ticket #202509170005 priority has been escalated due to time elapsed. Immediate attention required.', '', 'high', NULL, '202509170005', 'ticket', 0, 'http://10.31.210.225/testfs/controller/tickets/202509170005', '2025-09-17 10:58:42', NULL, NULL, NULL, NULL, '2025-09-17 10:58:42'),
(47, 11, NULL, NULL, 'Ticket priority escalated - 202509170005', 'Ticket #202509170005 priority has been escalated due to time elapsed. Immediate attention required.', '', 'high', NULL, '202509170005', 'ticket', 0, 'http://10.31.210.225/testfs/controller/tickets/202509170005', '2025-09-17 10:58:42', NULL, NULL, NULL, NULL, '2025-09-17 10:58:42'),
(48, 12, NULL, NULL, 'Ticket priority escalated - 202509170005', 'Ticket #202509170005 priority has been escalated due to time elapsed. Immediate attention required.', '', 'high', NULL, '202509170005', 'ticket', 0, 'http://10.31.210.225/testfs/controller/tickets/202509170005', '2025-09-17 10:58:42', NULL, NULL, NULL, NULL, '2025-09-17 10:58:42'),
(49, 13, NULL, NULL, 'Ticket priority escalated - 202509170005', 'Ticket #202509170005 priority has been escalated due to time elapsed. Immediate attention required.', '', 'high', NULL, '202509170005', 'ticket', 0, 'http://10.31.210.225/testfs/controller/tickets/202509170005', '2025-09-17 10:58:42', NULL, NULL, NULL, NULL, '2025-09-17 10:58:42'),
(50, 14, NULL, NULL, 'Ticket priority escalated - 202509170005', 'Ticket #202509170005 priority has been escalated due to time elapsed. Immediate attention required.', '', 'high', NULL, '202509170005', 'ticket', 0, 'http://10.31.210.225/testfs/controller/tickets/202509170005', '2025-09-17 10:58:42', NULL, NULL, NULL, NULL, '2025-09-17 10:58:42'),
(51, 18, NULL, NULL, 'Ticket priority escalated - 202509170005', 'Ticket #202509170005 priority has been escalated due to time elapsed. Immediate attention required.', '', 'high', NULL, '202509170005', 'ticket', 0, 'http://10.31.210.225/testfs/controller/tickets/202509170005', '2025-09-17 10:58:42', NULL, NULL, NULL, NULL, '2025-09-17 10:58:42'),
(52, NULL, NULL, 'controller_nodal', 'TESTING ', 'TESTING NEW SYSTEM - 16.36', 'system_announcement', 'urgent', NULL, NULL, NULL, 0, NULL, '2025-09-17 11:06:51', NULL, '2025-09-17 15:07:00', '{\"is_broadcast\":true,\"created_by\":1,\"target_user_types\":[\"controller_nodal\"]}', NULL, '2025-09-17 11:06:51'),
(53, NULL, 'CUST2025090001', 'customer', 'Welcome to SAMPARK!', 'Your account has been successfully activated. You can now create support tickets and track them in real-time.', 'account_update', 'medium', NULL, NULL, NULL, 1, NULL, '2025-09-17 09:35:00', '2025-09-17 11:36:42', NULL, NULL, NULL, '2025-09-17 11:36:42'),
(54, NULL, 'CUST2025090001', 'customer', 'New Feature Available', 'We have added a new ticket tracking feature. Check it out in your dashboard!', 'system_announcement', 'low', NULL, NULL, NULL, 1, NULL, '2025-09-17 10:35:00', '2025-09-17 11:36:42', NULL, NULL, NULL, '2025-09-17 11:36:42'),
(55, NULL, 'CUST2025090001', 'customer', 'Ticket Update Required', 'Your ticket #202501010001 requires additional information from you.', 'ticket_updated', 'high', '202501010001', '202501010001', 'ticket', 1, '/customer/tickets/202501010001', '2025-09-17 11:05:00', '2025-09-17 11:36:42', NULL, NULL, NULL, '2025-09-17 11:36:42'),
(56, 1, NULL, 'admin', 'New Ticket Assigned', 'Ticket #202501010002 has been assigned to your department.', 'ticket_assigned', 'medium', '202501010002', '202501010002', 'ticket', 0, '/admin/tickets/202501010002/view', '2025-09-17 10:50:00', NULL, NULL, NULL, NULL, '2025-09-17 10:50:00'),
(57, 1, NULL, 'admin', 'SLA Warning', 'Ticket #202501010003 is approaching SLA deadline in 2 hours.', 'sla_warning', 'urgent', '202501010003', '202501010003', 'ticket', 0, '/admin/tickets/202501010003/view', '2025-09-17 11:20:00', NULL, NULL, NULL, NULL, '2025-09-17 11:20:00'),
(58, 1, NULL, 'admin', 'System Maintenance', 'Scheduled maintenance will occur tonight from 2 AM to 4 AM.', 'maintenance_alert', 'high', NULL, NULL, NULL, 0, NULL, '2025-09-17 08:35:00', NULL, NULL, NULL, NULL, '2025-09-17 08:35:00'),
(59, NULL, 'CUST2025090001', 'customer', 'Ticket Resolved', 'Your ticket #202501010004 has been successfully resolved.', 'ticket_resolved', 'medium', '202501010004', '202501010004', 'ticket', 1, '/customer/tickets/202501010004', '2025-09-15 11:35:00', '2025-09-16 11:35:00', NULL, NULL, NULL, '2025-09-16 11:35:00'),
(60, 1, NULL, 'admin', 'Daily Report', 'Your daily ticket summary report is ready.', 'system_announcement', 'low', NULL, NULL, NULL, 1, '/admin/reports', '2025-09-15 11:35:00', '2025-09-16 11:35:00', NULL, NULL, NULL, '2025-09-16 11:35:00'),
(61, NULL, 'CUST2025090001', NULL, 'ticket_created notification', 'Notification sent via email', 'info', 'medium', NULL, NULL, NULL, 1, NULL, '2025-09-17 11:37:22', '2025-09-17 11:37:28', NULL, NULL, NULL, '2025-09-17 11:37:28'),
(62, 5, NULL, NULL, 'Ticket created successfully with number - 202509170008', 'A new support ticket has been created and assigned to your division (BB). Please review and take appropriate action.', '', 'medium', NULL, '202509170008', 'ticket', 1, 'http://localhost/testfs/controller/tickets/202509170008', '2025-09-17 11:37:22', '2025-09-17 11:38:11', NULL, NULL, NULL, '2025-09-17 11:38:11'),
(63, NULL, 'CUST2025090001', NULL, 'Ticket status updated - 202509170008', 'Ticket updated successfully - 202509170008. Additional information is required from you to proceed.', '', 'medium', NULL, '202509170008', 'ticket', 1, 'http://localhost/testfs/customer/tickets/202509170008', '2025-09-17 11:39:03', '2025-09-17 11:39:25', NULL, NULL, NULL, '2025-09-17 11:39:25');

-- --------------------------------------------------------

--
-- Table structure for table `notification_logs`
--

CREATE TABLE `notification_logs` (
  `id` int(11) NOT NULL,
  `notification_id` int(11) DEFAULT NULL,
  `action` enum('created','sent','delivered','read','dismissed','expired') NOT NULL,
  `channel` enum('email','sms','browser','system') DEFAULT 'system',
  `status` enum('success','failed','pending') DEFAULT 'pending',
  `details` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`details`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notification_settings`
--

CREATE TABLE `notification_settings` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `customer_id` varchar(20) DEFAULT NULL,
  `user_type` enum('customer','controller','controller_nodal','admin','superadmin') NOT NULL,
  `email_enabled` tinyint(1) DEFAULT 1,
  `sms_enabled` tinyint(1) DEFAULT 0,
  `browser_enabled` tinyint(1) DEFAULT 1,
  `priority_escalation_enabled` tinyint(1) DEFAULT 1,
  `frequency` enum('immediate','hourly','daily','weekly') DEFAULT 'immediate',
  `types_enabled` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Array of enabled notification types' CHECK (json_valid(`types_enabled`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notification_templates`
--

CREATE TABLE `notification_templates` (
  `id` int(11) NOT NULL,
  `template_code` varchar(100) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `type` enum('email','sms','browser','all') DEFAULT 'all',
  `subject` varchar(255) DEFAULT NULL,
  `body_html` text DEFAULT NULL,
  `body_text` text DEFAULT NULL,
  `variables` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Available template variables' CHECK (json_valid(`variables`)),
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `notification_templates`
--

INSERT INTO `notification_templates` (`id`, `template_code`, `name`, `description`, `type`, `subject`, `body_html`, `body_text`, `variables`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'priority_escalated', 'Priority Escalation Notification', 'Sent when ticket priority is escalated', 'all', 'Ticket {{ticket_id}} Priority Escalated to {{priority}}', '<h3>Priority Escalation Alert</h3><p>Ticket #{{ticket_id}} has been escalated to <strong>{{priority}}</strong> priority.</p><p><a href=\"{{view_url}}\" class=\"btn btn-primary\">View Ticket</a></p>', 'Ticket #{{ticket_id}} has been escalated to {{priority}} priority. View details: {{view_url}}', '[\"ticket_id\", \"priority\", \"view_url\", \"customer_name\", \"escalation_reason\"]', 1, '2025-09-16 18:20:06', '2025-09-16 18:20:06'),
(2, 'ticket_assigned', 'Ticket Assignment Notification', 'Sent when ticket is assigned to user', 'all', 'New Ticket Assigned: {{ticket_id}}', '<h3>New Ticket Assignment</h3><p>Ticket #{{ticket_id}} has been assigned to you.</p><p>Customer: {{customer_name}}</p><p><a href=\"{{view_url}}\" class=\"btn btn-primary\">View Ticket</a></p>', 'New ticket #{{ticket_id}} assigned to you from {{customer_name}}. View: {{view_url}}', '[\"ticket_id\", \"customer_name\", \"view_url\", \"priority\", \"category\"]', 1, '2025-09-16 18:20:06', '2025-09-16 18:20:06'),
(3, 'critical_priority_alert', 'Critical Priority Alert', 'Sent to admins when ticket reaches critical priority', 'all', 'CRITICAL: Ticket {{ticket_id}} Requires Immediate Attention', '<h3 style=\"color: red;\">⚠️ CRITICAL PRIORITY ALERT</h3><p>Ticket #{{ticket_id}} has reached <strong>CRITICAL</strong> priority and requires immediate attention.</p><p><strong>Customer:</strong> {{customer_name}}</p><p><strong>Division:</strong> {{division}}</p><p><a href=\"{{view_url}}\" class=\"btn btn-danger\">TAKE ACTION NOW</a></p>', 'CRITICAL: Ticket #{{ticket_id}} from {{customer_name}} requires immediate attention. Division: {{division}}. View: {{view_url}}', '[\"ticket_id\", \"customer_name\", \"division\", \"view_url\", \"escalation_time\"]', 1, '2025-09-16 18:20:06', '2025-09-16 18:20:06');

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
(3, 'ABSG', 'BB', 'CR', 'ORDNANCE FACTORY SDG ABH', 1, '2025-09-09 11:01:58', '2025-09-15 06:59:47'),
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
(30, 'BCCK', 'BB', 'CR', 'BULK CEMENT CORP SIDING KLMI', 1, '2025-09-09 11:01:59', '2025-09-15 06:59:47'),
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
(55, 'BPTG', 'BB', 'CR', 'Grain Depot', 1, '2025-09-09 11:02:00', '2025-09-15 06:59:47'),
(56, 'BPTV', 'BB', 'CR', 'VICTORIA DOCK BPT RLY', 1, '2025-09-09 11:02:00', '2025-09-15 06:59:47'),
(57, 'BQM', 'NGP', 'CR', 'Barelipar', 1, '2025-09-09 11:02:00', '2025-09-09 11:02:00'),
(58, 'BRDH', 'NGP', 'CR', 'Bardhana', 1, '2025-09-09 11:02:00', '2025-09-09 11:02:00'),
(59, 'BRMT', 'PUNE', 'CR', 'Baramati', 1, '2025-09-09 11:02:00', '2025-09-09 11:02:00'),
(60, 'BROL', 'NGP', 'CR', 'POL SDG. FOR M/S BPCL', 1, '2025-09-09 11:02:00', '2025-09-09 11:02:00'),
(61, 'BRSG', 'BB', 'CR', 'Bharat Petroleum Siding', 1, '2025-09-09 11:02:00', '2025-09-15 06:59:47'),
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
(82, 'CCIK', 'BB', 'CR', 'COTTON CORP. OF INDIA LTD.', 1, '2025-09-09 11:02:01', '2025-09-15 06:59:47'),
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
(94, 'CPWS', 'BB', 'CR', 'CROMPTON GREAVES LTD. SDG', 1, '2025-09-09 11:02:01', '2025-09-15 06:59:47'),
(95, 'CRCC', 'PUNE', 'CR', 'CHINCHWAD CONTAINER DEPOT', 1, '2025-09-09 11:02:01', '2025-09-09 11:02:01'),
(96, 'CRMM', 'PUNE', 'CR', 'MIRAJ CONTAINER DEPOT', 1, '2025-09-09 11:02:01', '2025-09-09 11:02:01'),
(97, 'CRNM', 'BB', 'CR', 'CONTAINER DEPOT NEW MULUND', 1, '2025-09-09 11:02:01', '2025-09-15 06:59:47'),
(98, 'CRTK', 'BB', 'CR', 'Turbhe container Siding', 1, '2025-09-09 11:02:01', '2025-09-15 06:59:47'),
(99, 'CSID', 'NGP', 'CR', 'MOHAN SIDING PALACHORI', 1, '2025-09-09 11:02:01', '2025-09-09 11:02:01'),
(100, 'CSN', 'BSL', 'CR', 'Chalisgaon Jn', 1, '2025-09-09 11:02:01', '2025-09-09 11:02:01'),
(101, 'CWHC', 'PUNE', 'CR', 'CENTRAL WAREHOUSE SDG.', 1, '2025-09-09 11:02:01', '2025-09-09 11:02:01'),
(102, 'CWHS', 'BSL', 'CR', 'Central Warehousing Corporation Siding Khandwa', 1, '2025-09-09 11:02:01', '2025-09-09 11:02:01'),
(103, 'CWJC', 'BB', 'CR', 'Central Warehousing Corp. siding', 1, '2025-09-09 11:02:01', '2025-09-15 06:59:47'),
(104, 'DAE', 'NGP', 'CR', 'Dahegaon', 1, '2025-09-09 11:02:01', '2025-09-09 11:02:01'),
(105, 'DAH', 'PUNE', 'CR', 'Dehare', 1, '2025-09-09 11:02:01', '2025-09-09 11:02:01'),
(106, 'DAPD', 'PUNE', 'CR', 'Dapodi', 1, '2025-09-09 11:02:01', '2025-09-09 11:02:01'),
(107, 'DASG', 'PUNE', 'CR', 'DEHU AMMUNITION DEPOT, SHELARWADI', 1, '2025-09-09 11:02:01', '2025-09-09 11:02:01'),
(108, 'DBCL', 'BB', 'CR', 'DOMBIVLI CTW', 1, '2025-09-09 11:02:02', '2025-09-15 06:59:47'),
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
(121, 'DLLM', 'BB', 'CR', 'Diesel Loco Shed LTT Mumbai', 1, '2025-09-09 11:02:02', '2025-09-15 06:59:47'),
(122, 'DMGM', 'NGP', 'CR', 'Dinesh OCM Makardhokara-III Gati Shakti Multi-Modal Cargo Terminal', 1, '2025-09-09 11:02:02', '2025-09-09 11:02:02'),
(123, 'DMN', 'NGP', 'CR', 'Dhamangaon', 1, '2025-09-09 11:02:02', '2025-09-09 11:02:02'),
(124, 'DMSG', 'BSL', 'CR', 'DEVLALI MILITARY SIDING, DEVLALI', 1, '2025-09-09 11:02:02', '2025-09-09 11:02:02'),
(125, 'DNJ', 'PUNE', 'CR', 'Daundaj', 1, '2025-09-09 11:02:02', '2025-09-09 11:02:02'),
(126, 'DNZ', 'NGP', 'CR', 'Dhanori', 1, '2025-09-09 11:02:02', '2025-09-09 11:02:02'),
(127, 'DOH', 'NGP', 'CR', 'Dhodra Mohor', 1, '2025-09-09 11:02:02', '2025-09-09 11:02:02'),
(128, 'DRSV', 'SUR', 'CR', 'DHARASHIV', 1, '2025-09-09 11:02:02', '2025-09-09 11:02:02'),
(129, 'DRTA', 'BB', 'CR', 'DRONAGIRI Rail Terminal', 1, '2025-09-09 11:02:02', '2025-09-15 06:59:47'),
(130, 'DSK', 'BSL', 'CR', 'Duskheda', 1, '2025-09-09 11:02:02', '2025-09-09 11:02:02'),
(131, 'DTCC', 'BB', 'CR', 'DATIVLI CHORD CABIN', 1, '2025-09-09 11:02:02', '2025-09-15 06:59:47'),
(132, 'DTVL', 'BB', 'CR', 'DATIVALI CABIN', 1, '2025-09-09 11:02:02', '2025-09-15 06:59:47'),
(133, 'DVL', 'BSL', 'CR', 'Devlali', 1, '2025-09-09 11:02:03', '2025-09-09 11:02:03'),
(134, 'DWJN', 'BB', 'CR', 'DIVA JN. CABIN', 1, '2025-09-09 11:02:03', '2025-09-15 06:59:47'),
(135, 'ELDD', 'SUR', 'CR', 'electric loco shed - daund', 1, '2025-09-09 11:02:03', '2025-09-09 11:02:03'),
(136, 'EOLD', 'NGP', 'CR', 'M/s Nayara Energy Ltd', 1, '2025-09-09 11:02:03', '2025-09-09 11:02:03'),
(137, 'ESSG', 'PUNE', 'CR', 'ENGINERING STORE TRANSIT DEPOT, SHELARWADI', 1, '2025-09-09 11:02:03', '2025-09-09 11:02:03'),
(138, 'FBSG', 'NGP', 'CR', 'FOOD CORP. OF INDIA SDG', 1, '2025-09-09 11:02:03', '2025-09-09 11:02:03'),
(139, 'FFSG', 'NGP', 'CR', 'FILLING FACTORY SIDING, BHANDAK', 1, '2025-09-09 11:02:03', '2025-09-09 11:02:03'),
(140, 'FNSG', 'NGP', 'CR', 'FOOD CORPORATION OF INDIA SDG, NAGPUR (AJNI)', 1, '2025-09-09 11:02:03', '2025-09-09 11:02:03'),
(141, 'FSG', 'PUNE', 'CR', 'Phursungi', 1, '2025-09-09 11:02:03', '2025-09-09 11:02:03'),
(142, 'FWSM', 'NGP', 'CR', 'GCT of M/s Fuelco Washeries (India ) Limited', 1, '2025-09-09 11:02:03', '2025-09-09 11:02:03'),
(143, 'FZSG', 'BB', 'CR', 'Rashtriya Chemicals, and Fertilizers Siding-Trombay', 1, '2025-09-09 11:02:03', '2025-09-15 06:59:47'),
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
(164, 'HDYD', 'BB', 'CR', 'HOLDING YARD', 1, '2025-09-09 11:02:04', '2025-09-15 06:59:47'),
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
(179, 'IGPX', 'BB', 'CR', 'IGAT PURI YARD (TXR)', 1, '2025-09-09 11:02:04', '2025-09-15 06:59:47'),
(180, 'IKR', 'NGP', 'CR', 'Iklehra', 1, '2025-09-09 11:02:04', '2025-09-09 11:02:04'),
(181, 'IOBT', 'NGP', 'CR', 'POL SDG. FOR M/S IOC/BPCL TADALI', 1, '2025-09-09 11:02:04', '2025-09-09 11:02:04'),
(182, 'IOC', 'BSL', 'CR', 'POL siding for IOC ltd shirud', 1, '2025-09-09 11:02:04', '2025-09-09 11:02:04'),
(183, 'IOSG', 'BB', 'CR', 'Indian Oil Blending Siding', 1, '2025-09-09 11:02:04', '2025-09-15 06:59:47'),
(184, 'JBC', 'BB', 'CR', 'JAMBRUNG', 1, '2025-09-09 11:02:04', '2025-09-15 06:59:47'),
(185, 'JCSK', 'NGP', 'CR', 'M/S JSW Steel Coated Products Limited', 1, '2025-09-09 11:02:04', '2025-09-09 11:02:04'),
(186, 'JJR', 'PUNE', 'CR', 'Jejuri', 1, '2025-09-09 11:02:04', '2025-09-09 11:02:04'),
(187, 'JKR', 'NGP', 'CR', 'Jaulkhera', 1, '2025-09-09 11:02:04', '2025-09-09 11:02:04'),
(188, 'JL', 'BSL', 'CR', 'Jalgaon jn', 1, '2025-09-09 11:02:04', '2025-09-09 11:02:04'),
(189, 'JM', 'BSL', 'CR', 'Jalamb Jn.', 1, '2025-09-09 11:02:04', '2025-09-09 11:02:04'),
(190, 'JMD', 'BSL', 'CR', 'Jamdha', 1, '2025-09-09 11:02:04', '2025-09-09 11:02:04'),
(191, 'JMV', 'NGP', 'CR', 'Jambhara', 1, '2025-09-09 11:02:04', '2025-09-09 11:02:04'),
(192, 'JNO', 'NGP', 'CR', 'Junnor Deo', 1, '2025-09-09 11:02:05', '2025-09-09 11:02:05'),
(193, 'JNPT', 'BB', 'CR', 'JAWAHARLAL NEHRU PORT TRUST', 1, '2025-09-09 11:02:05', '2025-09-15 06:59:47'),
(194, 'JSLE', 'BB', 'CR', 'JASAI CHIRLE', 1, '2025-09-09 11:02:05', '2025-09-15 06:59:47'),
(195, 'JSP', 'PUNE', 'CR', 'Jayasingpur', 1, '2025-09-09 11:02:05', '2025-09-09 11:02:05'),
(196, 'JSSR', 'BB', 'CR', 'M/s JSW Steel Ltd.', 1, '2025-09-09 11:02:05', '2025-09-15 06:59:47'),
(197, 'JSV', 'PUNE', 'CR', 'Jarandeshwar', 1, '2025-09-09 11:02:05', '2025-09-09 11:02:05'),
(198, 'JSWD', 'BB', 'CR', 'JSW steel ltd. siding', 1, '2025-09-09 11:02:05', '2025-09-15 06:59:47'),
(199, 'JSWV', 'BB', 'CR', 'M/s JSW steel coated products Ltd.', 1, '2025-09-09 11:02:05', '2025-09-15 06:59:47'),
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
(210, 'KFCG', 'BB', 'CR', 'Food Corporation of India Siding.', 1, '2025-09-09 11:02:05', '2025-09-15 06:59:47'),
(211, 'KFSG', 'PUNE', 'CR', 'HIGH EXPLOSIVES FACTORY SDG, KHADKI', 1, '2025-09-09 11:02:05', '2025-09-09 11:02:05'),
(212, 'KJ', 'BSL', 'CR', 'Kajgaon', 1, '2025-09-09 11:02:05', '2025-09-09 11:02:05'),
(213, 'KJL', 'BSL', 'CR', 'Khumgaon Burti', 1, '2025-09-09 11:02:05', '2025-09-09 11:02:05'),
(214, 'KK', 'PUNE', 'CR', 'Khadki', 1, '2025-09-09 11:02:05', '2025-09-09 11:02:05'),
(215, 'KLAT', 'BSL', 'CR', 'kolvihir', 1, '2025-09-09 11:02:05', '2025-09-09 11:02:05'),
(216, 'KLBA', 'NGP', 'CR', 'Kalambha', 1, '2025-09-09 11:02:05', '2025-09-09 11:02:05'),
(217, 'KLBG', 'SUR', 'CR', 'KALABURAGI', 1, '2025-09-09 11:02:05', '2025-09-09 11:02:05'),
(218, 'KLHD', 'BSL', 'CR', 'Kolhadi', 1, '2025-09-09 11:02:05', '2025-09-09 11:02:05'),
(219, 'KLMG', 'BB', 'CR', 'Kalamboli Goods Shed', 1, '2025-09-09 11:02:06', '2025-09-15 06:59:47'),
(220, 'KLMI', 'BB', 'CR', 'KALAMBOLI EXCHANGE YARD', 1, '2025-09-09 11:02:06', '2025-09-15 06:59:47'),
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
(238, 'KSAG', 'BB', 'CR', 'Steel Authority of India Ltd. Siding', 1, '2025-09-09 11:02:06', '2025-09-15 06:59:47'),
(239, 'KSLA', 'NGP', 'CR', 'Kesla', 1, '2025-09-09 11:02:06', '2025-09-09 11:02:06'),
(240, 'KSWD', 'PUNE', 'CR', 'Kasarwadi', 1, '2025-09-09 11:02:06', '2025-09-09 11:02:06'),
(241, 'KSWR', 'NGP', 'CR', 'Kalmeshwar', 1, '2025-09-09 11:02:06', '2025-09-09 11:02:06'),
(242, 'KTIG', 'BB', 'CR', 'TATA IRON & STEEL CO. SIDING', 1, '2025-09-09 11:02:06', '2025-09-15 06:59:47'),
(243, 'KTP', 'BSL', 'CR', 'Katepurna', 1, '2025-09-09 11:02:06', '2025-09-09 11:02:06'),
(244, 'KTTG', 'BB', 'CR', 'TATA IRON & STEEL CO. SIDING', 1, '2025-09-09 11:02:06', '2025-09-15 06:59:47'),
(245, 'KUM', 'BSL', 'CR', 'Kuram', 1, '2025-09-09 11:02:06', '2025-09-09 11:02:06'),
(246, 'KUX', 'NGP', 'CR', 'Khirsadoh Jn.', 1, '2025-09-09 11:02:06', '2025-09-09 11:02:06'),
(247, 'KVSG', 'PUNE', 'CR', 'ARMOURED FIGHTING VEHICLE DEPOT SIDING, KHADKI', 1, '2025-09-09 11:02:07', '2025-09-09 11:02:07'),
(248, 'KW', 'BSL', 'CR', 'Khervadi', 1, '2025-09-09 11:02:07', '2025-09-09 11:02:07'),
(249, 'KWV', 'SUR', 'CR', 'Kurduwadi', 1, '2025-09-09 11:02:07', '2025-09-09 11:02:07'),
(250, 'KYN', 'BB', 'CR', 'Kalyan Jn.', 1, '2025-09-09 11:02:07', '2025-09-15 06:59:47'),
(251, 'KYNX', 'BB', 'CR', 'KALYAN JN. YARD (TXR)', 1, '2025-09-09 11:02:07', '2025-09-15 06:59:47'),
(252, 'LAUL', 'SUR', 'CR', 'Laul', 1, '2025-09-09 11:02:07', '2025-09-09 11:02:07'),
(253, 'LLD', 'NGP', 'CR', 'Lalawadi', 1, '2025-09-09 11:02:07', '2025-09-09 11:02:07'),
(254, 'LNL', 'BB', 'CR', 'LONAVALA', 1, '2025-09-09 11:02:07', '2025-09-15 06:59:47'),
(255, 'LNLX', 'BB', 'CR', 'LONAVLA YARD (TXR)', 1, '2025-09-09 11:02:07', '2025-09-15 06:59:47'),
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
(266, 'MBPP', 'BB', 'CR', 'BPCL SDG. AT URAN', 1, '2025-09-09 11:02:07', '2025-09-15 06:59:47'),
(267, 'MBSH', 'SUR', 'CR', 'M/S ULTRATECH CEMENT LTD.', 1, '2025-09-09 11:02:07', '2025-09-09 11:02:07'),
(268, 'MDDG', 'PUNE', 'CR', 'Maladgaon', 1, '2025-09-09 11:02:07', '2025-09-09 11:02:07'),
(269, 'MDIT', 'NGP', 'CR', 'Dhariwal infrastrutre ltd siding', 1, '2025-09-09 11:02:07', '2025-09-09 11:02:07'),
(270, 'MELG', 'NGP', 'CR', 'Maharashtra Electrosmelt Siding', 1, '2025-09-09 11:02:07', '2025-09-09 11:02:07'),
(271, 'MER', 'NGP', 'CR', 'Metpanjra', 1, '2025-09-09 11:02:07', '2025-09-09 11:02:07'),
(272, 'MFSG', 'BSL', 'CR', 'Maharashtra State Electricity Board Siding Bsl', 1, '2025-09-09 11:02:07', '2025-09-09 11:02:07'),
(273, 'MGCS', 'BB', 'CR', 'MAHARASHTRA GAS CRAKER COMPLEX SDG', 1, '2025-09-09 11:02:08', '2025-09-15 06:59:47'),
(274, 'MGO', 'SUR', 'CR', 'Mahisgaon', 1, '2025-09-09 11:02:08', '2025-09-09 11:02:08'),
(275, 'MGRD', 'NGP', 'CR', 'MagarDoh', 1, '2025-09-09 11:02:08', '2025-09-09 11:02:08'),
(276, 'MHAD', 'BSL', 'CR', 'Mohadi Pragane Laling', 1, '2025-09-09 11:02:08', '2025-09-09 11:02:08'),
(277, 'MHLC', 'BB', 'CR', 'MONKEY HILL', 1, '2025-09-09 11:02:08', '2025-09-15 06:59:47'),
(278, 'MILK', 'BB', 'CR', 'M/S CENTRAL WAREHOUSING CORPORATION.', 1, '2025-09-09 11:02:08', '2025-09-15 06:59:47'),
(279, 'MIOJ', 'BB', 'CR', 'M/S IOT INFRASRUCTURE & ENERGY SERVICES LTD.', 1, '2025-09-09 11:02:08', '2025-09-15 06:59:47'),
(280, 'MJKN', 'NGP', 'CR', 'Majrikhadan', 1, '2025-09-09 11:02:08', '2025-09-09 11:02:08'),
(281, 'MJRI', 'NGP', 'CR', 'Majri Jn.', 1, '2025-09-09 11:02:08', '2025-09-09 11:02:08'),
(282, 'MJSG', 'NGP', 'CR', 'NEW MANJRI COLLIERY SIDING, MAJRI JN.', 1, '2025-09-09 11:02:08', '2025-09-09 11:02:08'),
(283, 'MJY', 'NGP', 'CR', 'Maramjhiri', 1, '2025-09-09 11:02:08', '2025-09-09 11:02:08'),
(284, 'MKCW', 'NGP', 'CR', 'KARTIKEYA COAL WASHERY SDG.', 1, '2025-09-09 11:02:08', '2025-09-09 11:02:08'),
(285, 'MKDN', 'NGP', 'CR', 'Markadhana', 1, '2025-09-09 11:02:08', '2025-09-09 11:02:08'),
(286, 'MKSG', 'BB', 'CR', 'MILITARY TRANSIT SDG MANKHURD', 1, '2025-09-09 11:02:08', '2025-09-15 06:59:47'),
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
(318, 'NGSM', 'BB', 'CR', 'New Mulund Goods Depot', 1, '2025-09-09 11:02:09', '2025-09-15 06:59:47'),
(319, 'NGTN', 'BB', 'CR', 'Nagothane', 1, '2025-09-09 11:02:09', '2025-09-15 06:59:47'),
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
(330, 'NNCN', 'BB', 'CR', 'NAGNATH CABIN', 1, '2025-09-09 11:02:09', '2025-09-15 06:59:47'),
(331, 'NPNR', 'BSL', 'CR', 'Nepanagar', 1, '2025-09-09 11:02:10', '2025-09-09 11:02:10'),
(332, 'NR', 'BSL', 'CR', 'Niphad', 1, '2025-09-09 11:02:10', '2025-09-09 11:02:10'),
(333, 'NRKR', 'NGP', 'CR', 'Narkher', 1, '2025-09-09 11:02:10', '2025-09-09 11:02:10'),
(334, 'NRSG', 'BB', 'CR', 'NATIONAL RAYON CORPN. SDG', 1, '2025-09-09 11:02:10', '2025-09-15 06:59:47'),
(335, 'NSKG', 'BB', 'CR', 'NAVAL SIDING KARANJA, URAN CITY', 1, '2025-09-09 11:02:10', '2025-09-15 06:59:47'),
(336, 'NTPG', 'NGP', 'CR', 'New Thermal Power Station Siding-Chandrapur', 1, '2025-09-09 11:02:10', '2025-09-09 11:02:10'),
(337, 'NVG', 'NGP', 'CR', 'Navegaon', 1, '2025-09-09 11:02:10', '2025-09-09 11:02:10'),
(338, 'NYDO', 'PUNE', 'CR', 'NARAYANDOHO', 1, '2025-09-09 11:02:10', '2025-09-09 11:02:10'),
(339, 'OCSB', 'BSL', 'CR', 'ORIENT CEMENT SDG Bhadli', 1, '2025-09-09 11:02:10', '2025-09-09 11:02:10'),
(340, 'ODHA', 'BSL', 'CR', 'Odha', 1, '2025-09-09 11:02:10', '2025-09-09 11:02:10'),
(341, 'PAA', 'PUNE', 'CR', 'Patas', 1, '2025-09-09 11:02:10', '2025-09-09 11:02:10'),
(342, 'PAAL', 'NGP', 'CR', 'Pala', 1, '2025-09-09 11:02:10', '2025-09-09 11:02:10'),
(343, 'PALB', 'NGP', 'CR', 'M/s adani logistics ltd. pft', 1, '2025-09-09 11:02:10', '2025-09-09 11:02:10'),
(344, 'PAR', 'NGP', 'CR', 'Pandhurna', 1, '2025-09-09 11:02:10', '2025-09-09 11:02:10'),
(345, 'PATP', 'BB', 'CR', 'adani agri logistics ltd', 1, '2025-09-09 11:02:10', '2025-09-15 06:59:47'),
(346, 'PC', 'BSL', 'CR', 'Pachora Jn.', 1, '2025-09-09 11:02:10', '2025-09-09 11:02:10'),
(347, 'PCGN', 'NGP', 'CR', 'Pachegaon', 1, '2025-09-09 11:02:10', '2025-09-09 11:02:10'),
(348, 'PCLI', 'NGP', 'CR', 'Palachauri', 1, '2025-09-09 11:02:10', '2025-09-09 11:02:10'),
(349, 'PCP', 'SUR', 'CR', 'Palsap', 1, '2025-09-09 11:02:11', '2025-09-09 11:02:11'),
(350, 'PCPK', 'NGP', 'CR', 'Multi Modal Logistic Park', 1, '2025-09-09 11:02:11', '2025-09-09 11:02:11'),
(351, 'PEN', 'BB', 'CR', 'Pen', 1, '2025-09-09 11:02:11', '2025-09-15 06:59:47'),
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
(363, 'PNCS', 'BB', 'CR', 'NAVKAR CORP. LTD', 1, '2025-09-09 11:02:11', '2025-09-15 06:59:47'),
(364, 'PNV', 'BSL', 'CR', 'Panevadi', 1, '2025-09-09 11:02:11', '2025-09-09 11:02:11'),
(365, 'POLG', 'BSL', 'CR', 'POL SDG. FOR M/S IOC GAIGAON', 1, '2025-09-09 11:02:11', '2025-09-09 11:02:11'),
(366, 'POSG', 'NGP', 'CR', 'ORDINANCE DEPOT MILITARY SDG, PULGAON', 1, '2025-09-09 11:02:11', '2025-09-09 11:02:11'),
(367, 'POX', 'NGP', 'CR', 'POLA PATHAR', 1, '2025-09-09 11:02:11', '2025-09-09 11:02:11'),
(368, 'PPCP', 'PUNE', 'CR', 'M/s Penna cement Industries ltd', 1, '2025-09-09 11:02:11', '2025-09-09 11:02:11'),
(369, 'PPDP', 'BB', 'CR', 'M/S PNP MARITIME SERVICES LTD', 1, '2025-09-09 11:02:11', '2025-09-15 06:59:47'),
(370, 'PRGT', 'NGP', 'CR', 'Pargothan', 1, '2025-09-09 11:02:11', '2025-09-09 11:02:11'),
(371, 'PRLW', 'BB', 'CR', 'PAREL LOCO WORK SHOP', 1, '2025-09-09 11:02:12', '2025-09-15 06:59:47'),
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
(398, 'RNSG', 'BB', 'CR', 'INDIAN NAVY STORE DEPOT MILITARY SIDING, KURLA JN.', 1, '2025-09-09 11:02:13', '2025-09-15 06:59:47'),
(399, 'ROHA', 'BB', 'CR', 'Roha', 1, '2025-09-09 11:02:13', '2025-09-15 06:59:47'),
(400, 'RPLW', 'BSL', 'CR', 'M/S RATANINDIA POWER lTD', 1, '2025-09-09 11:02:13', '2025-09-09 11:02:13'),
(401, 'RRI', 'PUNE', 'CR', 'Rahuri', 1, '2025-09-09 11:02:13', '2025-09-09 11:02:13'),
(402, 'RV', 'BSL', 'CR', 'Raver', 1, '2025-09-09 11:02:13', '2025-09-09 11:02:13'),
(403, 'RVJ', 'BB', 'CR', 'RAVLI JN', 1, '2025-09-09 11:02:13', '2025-09-15 06:59:47'),
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
(435, 'TAPG', 'BB', 'CR', 'TURBHE APM COMPLEX', 1, '2025-09-09 11:02:14', '2025-09-15 06:59:47'),
(436, 'TAZ', 'PUNE', 'CR', 'Targaon', 1, '2025-09-09 11:02:14', '2025-09-09 11:02:14'),
(437, 'TEO', 'NGP', 'CR', 'Teegaon', 1, '2025-09-09 11:02:14', '2025-09-09 11:02:14'),
(438, 'TER', 'SUR', 'CR', 'Thair', 1, '2025-09-09 11:02:14', '2025-09-09 11:02:14'),
(439, 'TGCR', 'BB', 'CR', 'TGR Cabin No. 1', 1, '2025-09-09 11:02:14', '2025-09-15 06:59:47'),
(440, 'TGN', 'PUNE', 'CR', 'Talegaon', 1, '2025-09-09 11:02:14', '2025-09-09 11:02:14'),
(441, 'TGP', 'NGP', 'CR', 'Tuljapur', 1, '2025-09-09 11:02:14', '2025-09-09 11:02:14'),
(442, 'TGRT', 'BB', 'CR', 'TGR Cabin No. 2', 1, '2025-09-09 11:02:14', '2025-09-15 06:59:47'),
(443, 'TGTC', 'BB', 'CR', 'TGR Cabin No. 3', 1, '2025-09-09 11:02:14', '2025-09-15 06:59:47'),
(444, 'THAL', 'BB', 'CR', 'THAL', 1, '2025-09-09 11:02:14', '2025-09-15 06:59:47'),
(445, 'THK', 'BB', 'CR', 'Thakurli', 1, '2025-09-09 11:02:14', '2025-09-15 06:59:47'),
(446, 'THSG', 'NGP', 'CR', 'Satpura Thermal power siding', 1, '2025-09-09 11:02:14', '2025-09-09 11:02:14'),
(447, 'TJSP', 'SUR', 'CR', 'TAJ SULTANPUR', 1, '2025-09-09 11:02:14', '2025-09-09 11:02:14'),
(448, 'TKI', 'BSL', 'CR', 'Takli', 1, '2025-09-09 11:02:14', '2025-09-09 11:02:14'),
(449, 'TKMY', 'PUNE', 'CR', 'Taklimiya', 1, '2025-09-09 11:02:14', '2025-09-09 11:02:14'),
(450, 'TKR', 'PUNE', 'CR', 'Takari', 1, '2025-09-09 11:02:14', '2025-09-09 11:02:14'),
(451, 'TLN', 'NGP', 'CR', 'Talni', 1, '2025-09-09 11:02:14', '2025-09-09 11:02:14'),
(452, 'TMBY', 'BB', 'CR', 'Trombay', 1, '2025-09-09 11:02:14', '2025-09-15 06:59:47'),
(453, 'TMT', 'NGP', 'CR', 'Timtala', 1, '2025-09-09 11:02:14', '2025-09-09 11:02:14'),
(454, 'TNH', 'NGP', 'CR', 'Tinkheda', 1, '2025-09-09 11:02:14', '2025-09-09 11:02:14'),
(455, 'TPHG', 'BB', 'CR', 'Tata Power House Siding', 1, '2025-09-09 11:02:14', '2025-09-15 06:59:47'),
(456, 'TPND', 'BB', 'CR', 'TALOJE PANCHNAND', 1, '2025-09-09 11:02:14', '2025-09-15 06:59:47'),
(457, 'TRW', 'BSL', 'CR', 'Tarsod', 1, '2025-09-09 11:02:14', '2025-09-09 11:02:14'),
(458, 'TTPS', 'BB', 'CR', 'Tata Thermal Power Station Siding', 1, '2025-09-09 11:02:14', '2025-09-15 06:59:47'),
(459, 'TVSG', 'BB', 'CR', 'Rashtriya Chemicals and Fertilizers siding-Thal Vaishett', 1, '2025-09-09 11:02:14', '2025-09-15 06:59:47'),
(460, 'UBCN', 'BB', 'CR', 'ULHAS BRIDGE CABIN', 1, '2025-09-09 11:02:14', '2025-09-15 06:59:47'),
(461, 'UGN', 'BSL', 'CR', 'Ugaon', 1, '2025-09-09 11:02:14', '2025-09-09 11:02:14'),
(462, 'UMSG', 'NGP', 'CR', 'Umred Colliery Siding', 1, '2025-09-09 11:02:14', '2025-09-09 11:02:14'),
(463, 'UPI', 'SUR', 'CR', 'Uplai', 1, '2025-09-09 11:02:14', '2025-09-09 11:02:14'),
(464, 'URAN', 'BB', 'CR', 'URAN', 1, '2025-09-09 11:02:14', '2025-09-15 06:59:47'),
(465, 'URI', 'PUNE', 'CR', 'Uruli', 1, '2025-09-09 11:02:14', '2025-09-09 11:02:14'),
(466, 'UTCU', 'PUNE', 'CR', 'M/s Ultratech cement siding', 1, '2025-09-09 11:02:14', '2025-09-09 11:02:14'),
(467, 'VADR', 'NGP', 'CR', 'Varud', 1, '2025-09-09 11:02:15', '2025-09-09 11:02:15'),
(468, 'VDN', 'PUNE', 'CR', 'Vadgaon', 1, '2025-09-09 11:02:15', '2025-09-09 11:02:15'),
(469, 'VGL', 'BSL', 'CR', 'Vaghli', 1, '2025-09-09 11:02:15', '2025-09-09 11:02:15'),
(470, 'VIPS', 'NGP', 'CR', 'm/s Vidharbha Inustries Power Ltd', 1, '2025-09-09 11:02:15', '2025-09-09 11:02:15'),
(471, 'VL', 'PUNE', 'CR', 'Vilad', 1, '2025-09-09 11:02:15', '2025-09-09 11:02:15'),
(472, 'VNA', 'BSL', 'CR', 'Varangaon', 1, '2025-09-09 11:02:15', '2025-09-09 11:02:15'),
(473, 'VOSG', 'BB', 'CR', 'Hindustan Petroleum Corporation Siding', 1, '2025-09-09 11:02:15', '2025-09-15 06:59:47'),
(474, 'VSD', 'BB', 'CR', 'Vasind', 1, '2025-09-09 11:02:15', '2025-09-15 06:59:47'),
(475, 'VSPG', 'BB', 'CR', 'VISHAKAPATTANAM STEEL PROJECT SDG.', 1, '2025-09-09 11:02:15', '2025-09-15 06:59:47'),
(476, 'VUL', 'NGP', 'CR', 'Virul', 1, '2025-09-09 11:02:15', '2025-09-09 11:02:15'),
(477, 'VV', 'PUNE', 'CR', 'Valivade', 1, '2025-09-09 11:02:15', '2025-09-09 11:02:15'),
(478, 'VVKN', 'NGP', 'CR', 'VIVEKANANDA NAGAR', 1, '2025-09-09 11:02:15', '2025-09-09 11:02:15'),
(479, 'WADI', 'SUR', 'CR', 'Wadi', 1, '2025-09-09 11:02:15', '2025-09-09 11:02:15'),
(480, 'WANI', 'NGP', 'CR', 'Wani', 1, '2025-09-09 11:02:15', '2025-09-09 11:02:15'),
(481, 'WB', 'BB', 'CR', 'WADI BANDAR', 1, '2025-09-09 11:02:15', '2025-09-15 06:59:47'),
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
-- Table structure for table `ticket_summary`
--

CREATE TABLE `ticket_summary` (
  `division` varchar(100) DEFAULT NULL,
  `zone` varchar(100) DEFAULT NULL,
  `status` enum('pending','awaiting_feedback','awaiting_info','awaiting_approval','closed') DEFAULT NULL,
  `priority` enum('normal','medium','high','critical') DEFAULT NULL,
  `ticket_count` bigint(21) DEFAULT NULL,
  `avg_resolution_hours` decimal(24,4) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `transaction_id` int(11) NOT NULL,
  `complaint_id` varchar(20) NOT NULL,
  `remarks` text DEFAULT NULL,
  `internal_remarks` text DEFAULT NULL,
  `remarks_type` enum('internal_remarks','interim_remarks','forwarding_remarks','admin_remarks','customer_remarks','system_remarks','priority_escalation') DEFAULT 'internal_remarks',
  `transaction_type` enum('created','forwarded','replied','approved','rejected','reverted','closed','escalated','feedback_submitted','priority_escalated','info_requested','interim_remarks','priority_reset') NOT NULL,
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

INSERT INTO `transactions` (`transaction_id`, `complaint_id`, `remarks`, `internal_remarks`, `remarks_type`, `transaction_type`, `from_user_id`, `to_user_id`, `from_customer_id`, `to_customer_id`, `from_department`, `to_department`, `from_division`, `to_division`, `created_by_id`, `created_by_customer_id`, `created_by_type`, `created_by_role`, `created_at`, `attachment_path`, `email_sent`, `sms_sent`) VALUES
(1, '202509150001', 'Ticket created by customer', NULL, 'internal_remarks', 'created', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'CUST2025090001', 'customer', 'customer', '2025-09-15 07:12:23', NULL, 0, 0),
(2, '202509150001', 'Priority automatically escalated from normal to medium after 5 hours', NULL, 'priority_escalation', 'priority_escalated', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'user', 'system', '2025-09-15 07:12:32', NULL, 0, 0),
(3, '202509150002', 'Ticket created by customer', NULL, 'internal_remarks', 'created', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'CUST2025090001', 'customer', 'customer', '2025-09-15 07:25:24', NULL, 0, 0),
(4, '202509150002', 'Priority automatically escalated from normal to medium after 5 hours', NULL, 'priority_escalation', 'priority_escalated', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'user', 'system', '2025-09-15 07:25:28', NULL, 0, 0),
(5, '202509150003', 'Ticket created by customer', NULL, 'internal_remarks', 'created', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'CUST2025090001', 'customer', 'customer', '2025-09-15 07:28:39', NULL, 0, 0),
(6, '202509150003', 'Priority automatically escalated from normal to medium after 5 hours', NULL, 'priority_escalation', 'priority_escalated', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'user', 'system', '2025-09-15 07:28:44', NULL, 0, 0),
(7, '202509150004', 'Ticket created by customer', NULL, 'internal_remarks', 'created', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'CUST2025090001', 'customer', 'customer', '2025-09-15 07:50:55', NULL, 0, 0),
(8, '202509150004', 'Priority automatically escalated from normal to medium after 5 hours', NULL, 'priority_escalation', 'priority_escalated', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'user', 'system', '2025-09-15 07:50:59', NULL, 0, 0),
(9, '202509150005', 'Ticket created by customer', NULL, 'internal_remarks', 'created', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'CUST2025090001', 'customer', 'customer', '2025-09-15 07:54:57', NULL, 0, 0),
(10, '202509150005', 'Priority automatically escalated from normal to medium after 5 hours', NULL, 'priority_escalation', 'priority_escalated', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'user', 'system', '2025-09-15 07:55:01', NULL, 0, 0),
(11, '202509150001', NULL, 'Tried calling the Passenger, did not pick up', 'internal_remarks', '', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 5, NULL, 'user', 'controller_nodal', '2025-09-15 07:55:02', NULL, 0, 0),
(12, '202509150003', 'Priority automatically escalated from medium to high after 17 hours', NULL, 'priority_escalation', 'priority_escalated', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'user', 'system', '2025-09-15 19:27:04', NULL, 0, 0),
(13, '202509150004', 'Priority automatically escalated from medium to high after 17 hours', NULL, 'priority_escalation', 'priority_escalated', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'user', 'system', '2025-09-15 19:27:04', NULL, 0, 0),
(14, '202509150005', 'Priority automatically escalated from medium to high after 17 hours', NULL, 'priority_escalation', 'priority_escalated', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'user', 'system', '2025-09-15 19:27:04', NULL, 0, 0),
(15, '202509150001', 'TESTING THE ADMIN REMARKS', NULL, 'admin_remarks', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 3, NULL, 'user', 'admin', '2025-09-15 19:27:56', NULL, 0, 0),
(16, '202509150001', 'Priority automatically escalated from high to critical after 24 hours', NULL, 'priority_escalation', 'priority_escalated', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'user', 'system', '2025-09-16 07:12:25', NULL, 0, 0),
(17, '202509150002', 'Priority automatically escalated from high to critical after 24 hours', NULL, 'priority_escalation', 'priority_escalated', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'user', 'system', '2025-09-16 07:38:56', NULL, 0, 0),
(18, '202509150003', 'Priority automatically escalated from high to critical after 24 hours', NULL, 'priority_escalation', 'priority_escalated', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'user', 'system', '2025-09-16 07:38:56', NULL, 0, 0),
(19, '202509150004', 'Priority automatically escalated from high to critical after 24 hours', NULL, 'priority_escalation', 'priority_escalated', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'user', 'system', '2025-09-16 07:59:41', NULL, 0, 0),
(20, '202509150005', 'Priority automatically escalated from high to critical after 24 hours', NULL, 'priority_escalation', 'priority_escalated', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'user', 'system', '2025-09-16 07:59:42', NULL, 0, 0),
(22, '202509160001', 'Ticket created by customer', NULL, 'internal_remarks', 'created', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'CUST2025090001', 'customer', 'customer', '2025-09-16 09:04:28', NULL, 0, 0),
(0, '202509170001', 'Ticket created by customer', NULL, 'internal_remarks', 'created', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'CUST2025090001', 'customer', 'customer', '2025-09-16 19:09:59', NULL, 0, 0),
(0, '202509160001', 'Priority automatically escalated from medium to high after 19 hours', NULL, 'priority_escalation', 'priority_escalated', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'user', 'system', '2025-09-17 05:02:09', NULL, 0, 0),
(0, '202509170001', 'Priority automatically escalated from normal to medium after 9 hours', NULL, 'priority_escalation', 'priority_escalated', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'user', 'system', '2025-09-17 05:02:42', NULL, 0, 0),
(0, '202509170002', 'Ticket created by customer', NULL, 'internal_remarks', 'created', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'CUST2025090001', 'customer', 'customer', '2025-09-17 05:12:20', NULL, 0, 0),
(0, '202509150001', 'Made Necessary changes to the other one was', NULL, 'customer_remarks', 'replied', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 5, NULL, 'user', 'controller_nodal', '2025-09-17 05:30:26', NULL, 0, 0),
(0, '202509150001', 'Made Necessary changes to the other one was', NULL, 'customer_remarks', '', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 5, NULL, 'user', 'controller_nodal', '2025-09-17 05:30:47', NULL, 0, 0),
(0, '202509150001', 'Rating: Excellent\nRemarks: Good', NULL, 'internal_remarks', 'feedback_submitted', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'CUST2025090001', 'customer', 'customer', '2025-09-17 05:31:37', NULL, 0, 0),
(0, '202509170003', 'Ticket created by customer', NULL, 'internal_remarks', 'created', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'CUST2025090001', 'customer', 'customer', '2025-09-17 05:55:54', NULL, 0, 0),
(0, '202509170004', 'Ticket created by customer', NULL, 'internal_remarks', 'created', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'CUST2025090001', 'customer', 'customer', '2025-09-17 06:24:42', NULL, 0, 0),
(0, '202509170005', 'Ticket created by customer', NULL, 'internal_remarks', 'created', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'CUST2025090001', 'customer', 'customer', '2025-09-17 06:47:06', NULL, 0, 0),
(0, '202509160001', 'Priority automatically escalated from high to critical after 24 hours', NULL, 'priority_escalation', 'priority_escalated', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'user', 'system', '2025-09-17 09:07:41', NULL, 0, 0),
(0, '202509170001', 'Priority automatically escalated from medium to high after 13 hours', NULL, 'priority_escalation', 'priority_escalated', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'user', 'system', '2025-09-17 09:07:47', NULL, 0, 0),
(0, '202509170002', 'Priority automatically escalated from normal to medium after 4 hours', NULL, 'priority_escalation', 'priority_escalated', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'user', 'system', '2025-09-17 09:12:41', NULL, 0, 0),
(0, '202509150002', NULL, 'This is the test.', 'forwarding_remarks', 'forwarded', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 5, NULL, 'user', 'controller_nodal', '2025-09-17 09:20:17', NULL, 0, 0),
(0, '202509150002', 'Priority automatically escalated from normal to critical after 49 hours', NULL, 'priority_escalation', 'priority_escalated', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'user', 'system', '2025-09-17 09:20:19', NULL, 0, 0),
(0, '202509150003', NULL, 'New Testing of forwards', 'forwarding_remarks', 'forwarded', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 5, NULL, 'user', 'controller_nodal', '2025-09-17 09:20:42', NULL, 0, 0),
(0, '202509150003', 'Priority automatically escalated from normal to critical after 49 hours', NULL, 'priority_escalation', 'priority_escalated', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'user', 'system', '2025-09-17 09:20:45', NULL, 0, 0),
(0, '202509150004', NULL, 'Please Check', 'forwarding_remarks', 'forwarded', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 5, NULL, 'user', 'controller_nodal', '2025-09-17 09:23:42', NULL, 0, 0),
(0, '202509150004', 'Priority automatically escalated from normal to critical after 49 hours', NULL, 'priority_escalation', 'priority_escalated', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'user', 'system', '2025-09-17 09:23:45', NULL, 0, 0),
(0, '202509170006', 'Ticket created by customer', NULL, 'internal_remarks', 'created', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'CUST2025090001', 'customer', 'customer', '2025-09-17 09:42:57', NULL, 0, 0),
(0, '202509170001', 'Make Sure Controller provides all the Details Correctly.', NULL, 'admin_remarks', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 3, NULL, 'user', 'admin', '2025-09-17 09:48:54', NULL, 0, 0),
(0, '202509170003', 'Priority automatically escalated from normal to medium after 4 hours', NULL, 'priority_escalation', 'priority_escalated', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'user', 'system', '2025-09-17 10:07:05', NULL, 0, 0),
(0, '202509170007', 'Ticket created by customer', NULL, 'internal_remarks', 'created', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'CUST2025090001', 'customer', 'customer', '2025-09-17 10:07:37', NULL, 0, 0),
(0, '202509150005', 'Noted. Will Take Action', NULL, 'customer_remarks', 'replied', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 5, NULL, 'user', 'controller_nodal', '2025-09-17 10:17:13', NULL, 0, 0),
(0, '202509150005', NULL, 'Checked the System and Replied', 'internal_remarks', 'replied', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 5, NULL, 'user', 'controller_nodal', '2025-09-17 10:17:13', NULL, 0, 0),
(0, '202509150005', 'Noted. Will Take Action', NULL, 'customer_remarks', '', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 5, NULL, 'user', 'controller_nodal', '2025-09-17 10:22:11', NULL, 0, 0),
(0, '202509150005', NULL, 'OK!', 'internal_remarks', '', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 5, NULL, 'user', 'controller_nodal', '2025-09-17 10:22:11', NULL, 0, 0),
(0, '202509170004', 'Priority automatically escalated from normal to medium after 4 hours', NULL, 'priority_escalation', 'priority_escalated', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'user', 'system', '2025-09-17 10:25:09', NULL, 0, 0),
(0, '202509170005', 'Priority automatically escalated from normal to medium after 4 hours', NULL, 'priority_escalation', 'priority_escalated', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'user', 'system', '2025-09-17 10:58:42', NULL, 0, 0),
(0, '202509170008', 'Ticket created by customer', NULL, 'internal_remarks', 'created', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'CUST2025090001', 'customer', 'customer', '2025-09-17 11:37:18', NULL, 0, 0),
(0, '202509170008', 'Additional information requested: We need info', NULL, 'customer_remarks', 'info_requested', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 5, NULL, 'user', 'controller_nodal', '2025-09-17 11:39:03', NULL, 0, 0),
(0, '202509170008', 'This is the new info', NULL, 'internal_remarks', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 'customer', NULL, '2025-09-17 11:40:08', NULL, 0, 0),
(0, '202509150005', 'Rating: Unsatisfactory\nRemarks: Cant see Why Action not being taken', NULL, 'internal_remarks', 'feedback_submitted', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'CUST2025090001', 'customer', 'customer', '2025-09-17 11:48:02', NULL, 0, 0);

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
  `force_password_change` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_by` int(11) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `login_id`, `password`, `role`, `department`, `division`, `zone`, `name`, `email`, `mobile`, `status`, `force_password_change`, `created_at`, `created_by`, `updated_at`) VALUES
(1, 'SA001', '$2y$10$Op.un2uvPATgiVHiCERz3.3LcG1GDqYJQ8xrKoZ1S3K7DVC9nHgzS', 'superadmin', 'IT', 'Headquarters', 'CR', 'System Administrator', 'admin@sampark.railway.gov.in', '9999999999', 'active', 0, '2025-09-03 11:52:21', 1, '2025-09-17 11:05:05'),
(3, 'AD002', '$2y$10$Op.un2uvPATgiVHiCERz3.3LcG1GDqYJQ8xrKoZ1S3K7DVC9nHgzS', 'admin', 'ADM', 'BB', 'CR', 'Priya Sharma', 'admin.mumbai@railway.gov.in', '9876543211', 'active', 0, '2025-09-03 11:52:21', 1, '2025-09-17 11:49:46'),
(5, 'CN001', '$2y$10$Op.un2uvPATgiVHiCERz3.3LcG1GDqYJQ8xrKoZ1S3K7DVC9nHgzS', 'controller_nodal', 'CML', 'BB', 'CR', 'Suresh Chandraa', 'commercial.sealdah@railway.gov.in', '9876543220', 'active', 0, '2025-09-03 11:52:21', 1, '2025-09-17 11:49:18'),
(6, 'CN002', '$2y$10$Op.un2uvPATgiVHiCERz3.3LcG1GDqYJQ8xrKoZ1S3K7DVC9nHgzS', 'controller_nodal', 'CML', 'BSL', 'CR', 'Meera Patel', 'commercial.howrah@railway.gov.in', '9876543221', 'active', 0, '2025-09-03 11:52:21', 1, '2025-09-15 10:00:28'),
(7, 'CN003', '$2y$10$Op.un2uvPATgiVHiCERz3.3LcG1GDqYJQ8xrKoZ1S3K7DVC9nHgzS', 'controller_nodal', 'CML', 'NGP', 'CR', 'Ravi Gupta', 'commercial.mumbai@railway.gov.in', '9876543222', 'active', 0, '2025-09-03 11:52:21', 1, '2025-09-15 10:01:27'),
(8, 'CN004', '$2y$10$Op.un2uvPATgiVHiCERz3.3LcG1GDqYJQ8xrKoZ1S3K7DVC9nHgzS', 'controller_nodal', 'CML', 'PUNE', 'CR', 'Sunita Devi', 'commercial.delhi@railway.gov.in', '9876543223', 'active', 0, '2025-09-03 11:52:21', 1, '2025-09-15 07:41:17'),
(9, 'CN005', '$2y$10$Op.un2uvPATgiVHiCERz3.3LcG1GDqYJQ8xrKoZ1S3K7DVC9nHgzS', 'controller_nodal', 'CML', 'SUR', 'CR', 'Karthik Raman', 'commercial.chennai@railway.gov.in', '9876543224', 'active', 0, '2025-09-03 11:52:21', 1, '2025-09-15 07:41:25'),
(10, 'CT001', '$2y$10$Op.un2uvPATgiVHiCERz3.3LcG1GDqYJQ8xrKoZ1S3K7DVC9nHgzS', 'controller', 'OPTG', 'SUR', 'CR', 'Anand Kumar', 'mechanical.sealdah@railway.gov.in', '9876543230', 'active', 0, '2025-09-03 11:52:21', 1, '2025-09-15 10:17:22'),
(11, 'CT002', '$2y$10$Op.un2uvPATgiVHiCERz3.3LcG1GDqYJQ8xrKoZ1S3K7DVC9nHgzS', 'controller', 'OPTG', 'PUNE', 'CR', 'Deepika Singh', 'electrical.sealdah@railway.gov.in', '9876543231', 'active', 0, '2025-09-03 11:52:21', 1, '2025-09-15 10:13:09'),
(12, 'CT003', '$2y$10$Op.un2uvPATgiVHiCERz3.3LcG1GDqYJQ8xrKoZ1S3K7DVC9nHgzS', 'controller', 'OPTG', 'BB', 'CR', 'Vikash Jain', 'operating.mumbai@railway.gov.in', '9876543232', 'active', 0, '2025-09-03 11:52:21', 1, '2025-09-15 07:00:46'),
(13, 'CT004', '$2y$10$Op.un2uvPATgiVHiCERz3.3LcG1GDqYJQ8xrKoZ1S3K7DVC9nHgzS', 'controller', 'OPTG', 'BSL', 'CR', 'Pooja Agarwal', 'engineering.delhi@railway.gov.in', '9876543233', 'active', 0, '2025-09-03 11:52:21', 1, '2025-09-15 06:30:53'),
(14, 'CT005', '$2y$10$Op.un2uvPATgiVHiCERz3.3LcG1GDqYJQ8xrKoZ1S3K7DVC9nHgzS', 'controller', 'OPTG', 'NGP', 'CR', 'Ramesh Babu', 'security.chennai@railway.gov.in', '9876543234', 'active', 0, '2025-09-03 11:52:21', 1, '2025-09-15 12:02:38'),
(18, 'CT006', '$2y$10$Op.un2uvPATgiVHiCERz3.3LcG1GDqYJQ8xrKoZ1S3K7DVC9nHgzS', 'controller', 'OPTG', 'HQ', 'CR', 'Ramesh Babu', 'security1.chennai@railway.gov.in', '9876543234', 'active', 0, '2025-09-03 11:52:21', 1, '2025-09-15 07:39:10');

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
-- Table structure for table `zone_division_departments`
--

CREATE TABLE `zone_division_departments` (
  `zone_code` varchar(10) DEFAULT NULL,
  `zone_name` varchar(100) DEFAULT NULL,
  `division_code` varchar(10) DEFAULT NULL,
  `division_name` varchar(100) DEFAULT NULL,
  `department_code` varchar(20) DEFAULT NULL,
  `department_name` varchar(100) DEFAULT NULL,
  `zone_active` tinyint(1) DEFAULT NULL,
  `division_active` tinyint(1) DEFAULT NULL,
  `department_active` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `complaints`
--
ALTER TABLE `complaints`
  ADD PRIMARY KEY (`complaint_id`),
  ADD KEY `idx_complaints_escalation` (`escalated_at`,`escalation_stopped`,`priority`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_priority` (`priority`),
  ADD KEY `idx_customer` (`customer_id`),
  ADD KEY `idx_division` (`division`),
  ADD KEY `idx_department` (`department`),
  ADD KEY `idx_created_date` (`date`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `shed_id` (`shed_id`),
  ADD KEY `idx_complaints_search` (`status`,`priority`,`division`,`created_at`),
  ADD KEY `idx_escalation_check` (`status`,`escalation_stopped`,`priority`,`created_at`);

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
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_template_code` (`template_code`),
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
  ADD KEY `idx_notifications_user_type` (`user_id`,`user_type`,`is_read`),
  ADD KEY `idx_notifications_customer` (`customer_id`,`is_read`),
  ADD KEY `idx_notifications_priority` (`priority`,`created_at`),
  ADD KEY `idx_notifications_related` (`related_id`,`related_type`),
  ADD KEY `idx_notifications_expires` (`expires_at`);

--
-- Indexes for table `notification_logs`
--
ALTER TABLE `notification_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_notification_logs_notification` (`notification_id`,`action`),
  ADD KEY `idx_notification_logs_created` (`created_at`);

--
-- Indexes for table `notification_settings`
--
ALTER TABLE `notification_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_settings` (`user_id`,`customer_id`,`user_type`),
  ADD KEY `idx_notification_settings_user` (`user_id`,`user_type`),
  ADD KEY `idx_notification_settings_customer` (`customer_id`);

--
-- Indexes for table `notification_templates`
--
ALTER TABLE `notification_templates`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `template_code` (`template_code`),
  ADD KEY `idx_notification_templates_code` (`template_code`,`is_active`);

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
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `email_templates`
--
ALTER TABLE `email_templates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=64;

--
-- AUTO_INCREMENT for table `notification_logs`
--
ALTER TABLE `notification_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notification_settings`
--
ALTER TABLE `notification_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notification_templates`
--
ALTER TABLE `notification_templates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
