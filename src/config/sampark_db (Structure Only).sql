-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 17, 2025 at 11:31 PM
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
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `closed_at` timestamp NULL DEFAULT NULL,
  `escalated_at` timestamp NULL DEFAULT NULL,
  `escalation_stopped` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
  `transaction_type` enum('created','forwarded','replied','approved','rejected','reverted','closed','escalated','feedback_submitted','priority_escalated','info_requested','interim_remarks','priority_reset','info_provided') NOT NULL,
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
  ADD KEY `idx_notifications_expires` (`expires_at`),
  ADD KEY `notifications_ibfk_3` (`complaint_id`);

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
  ADD KEY `idx_transactions_search` (`complaint_id`,`transaction_type`,`created_at`),
  ADD KEY `idx_remarks_type` (`remarks_type`,`transaction_type`);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `complaint_categories`
--
ALTER TABLE `complaint_categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `departments`
--
ALTER TABLE `departments`
  MODIFY `department_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `divisions`
--
ALTER TABLE `divisions`
  MODIFY `division_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `email_templates`
--
ALTER TABLE `email_templates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `evidence`
--
ALTER TABLE `evidence`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `news`
--
ALTER TABLE `news`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `quick_links`
--
ALTER TABLE `quick_links`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `remember_tokens`
--
ALTER TABLE `remember_tokens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `shed`
--
ALTER TABLE `shed`
  MODIFY `shed_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `system_cache`
--
ALTER TABLE `system_cache`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `system_settings`
--
ALTER TABLE `system_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `transaction_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `wagon_details`
--
ALTER TABLE `wagon_details`
  MODIFY `wagon_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `zones`
--
ALTER TABLE `zones`
  MODIFY `zone_id` int(11) NOT NULL AUTO_INCREMENT;

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
