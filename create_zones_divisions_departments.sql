-- SQL Script to create Zones, Divisions, and Departments tables for SAMPARK
-- Run this script in your MySQL database

-- Create Zones table
CREATE TABLE IF NOT EXISTS `zones` (
  `zone_id` int(11) NOT NULL AUTO_INCREMENT,
  `zone_code` varchar(10) NOT NULL UNIQUE,
  `zone_name` varchar(100) NOT NULL,
  `zone_full_name` varchar(200) NOT NULL,
  `headquarters` varchar(100) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`zone_id`),
  UNIQUE KEY `idx_zone_code` (`zone_code`),
  KEY `idx_zone_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create Divisions table
CREATE TABLE IF NOT EXISTS `divisions` (
  `division_id` int(11) NOT NULL AUTO_INCREMENT,
  `division_code` varchar(10) NOT NULL,
  `division_name` varchar(100) NOT NULL,
  `zone_id` int(11) NOT NULL,
  `zone_code` varchar(10) NOT NULL,
  `headquarters` varchar(100) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`division_id`),
  UNIQUE KEY `idx_division_code_zone` (`division_code`, `zone_code`),
  KEY `idx_zone_id` (`zone_id`),
  KEY `idx_division_active` (`is_active`),
  FOREIGN KEY (`zone_id`) REFERENCES `zones` (`zone_id`) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create Departments table
CREATE TABLE IF NOT EXISTS `departments` (
  `department_id` int(11) NOT NULL AUTO_INCREMENT,
  `department_code` varchar(20) NOT NULL,
  `department_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`department_id`),
  UNIQUE KEY `idx_department_code` (`department_code`),
  KEY `idx_department_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert Zone data based on current shed table data
INSERT INTO `zones` (`zone_code`, `zone_name`, `zone_full_name`, `headquarters`) VALUES
('CR', 'Central Railway', 'Central Railway Zone', 'Mumbai'),
('WR', 'Western Railway', 'Western Railway Zone', 'Mumbai'),
('NR', 'Northern Railway', 'Northern Railway Zone', 'New Delhi'),
('SR', 'Southern Railway', 'Southern Railway Zone', 'Chennai'),
('ER', 'Eastern Railway', 'Eastern Railway Zone', 'Kolkata'),
('NER', 'North Eastern Railway', 'North Eastern Railway Zone', 'Gorakhpur'),
('NFR', 'Northeast Frontier Railway', 'Northeast Frontier Railway Zone', 'Guwahati'),
('SCR', 'South Central Railway', 'South Central Railway Zone', 'Secunderabad'),
('SER', 'South Eastern Railway', 'South Eastern Railway Zone', 'Kolkata'),
('SECR', 'South East Central Railway', 'South East Central Railway Zone', 'Bilaspur'),
('SWR', 'South Western Railway', 'South Western Railway Zone', 'Hubli'),
('WCR', 'West Central Railway', 'West Central Railway Zone', 'Jabalpur'),
('NCR', 'North Central Railway', 'North Central Railway Zone', 'Allahabad'),
('NWR', 'North Western Railway', 'North Western Railway Zone', 'Jaipur'),
('ECR', 'East Central Railway', 'East Central Railway Zone', 'Hajipur'),
('ECOR', 'East Coast Railway', 'East Coast Railway Zone', 'Bhubaneswar'),
('KR', 'Konkan Railway', 'Konkan Railway Corporation', 'Navi Mumbai');

-- Insert Division data based on current shed table data and typical railway divisions
INSERT INTO `divisions` (`division_code`, `division_name`, `zone_id`, `zone_code`, `headquarters`) VALUES
-- Central Railway Divisions
('CSMT', 'Mumbai CSMT Division', 1, 'CR', 'Mumbai CSMT'),
('BSL', 'Bhusaval Division', 1, 'CR', 'Bhusaval'),
('NGP', 'Nagpur Division', 1, 'CR', 'Nagpur'),
('PUNE', 'Pune Division', 1, 'CR', 'Pune'),
('SOLAPUR', 'Solapur Division', 1, 'CR', 'Solapur'),
-- Western Railway Divisions
('BRC', 'Vadodara Division', 2, 'WR', 'Vadodara'),
('RTM', 'Ratlam Division', 2, 'WR', 'Ratlam'),
('ADI', 'Ahmedabad Division', 2, 'WR', 'Ahmedabad'),
('RJT', 'Rajkot Division', 2, 'WR', 'Rajkot'),
('BB', 'Mumbai Central Division', 2, 'WR', 'Mumbai Central'),
-- Northern Railway Divisions
('DLI', 'Delhi Division', 3, 'NR', 'New Delhi'),
('FZR', 'Firozpur Division', 3, 'NR', 'Firozpur'),
('LDH', 'Ludhiana Division', 3, 'NR', 'Ludhiana'),
('AMB', 'Ambala Division', 3, 'NR', 'Ambala');

-- Insert Department data
INSERT INTO `departments` (`department_code`, `department_name`, `description`) VALUES
('COMM', 'Commercial', 'Commercial Department - Revenue, freight operations, passenger services'),
('TECH', 'Technical', 'Technical Department - Infrastructure, signaling, telecommunications'),
('MECH', 'Mechanical', 'Mechanical Department - Rolling stock maintenance, workshops'),
('ELEC', 'Electrical', 'Electrical Department - Electrical systems, traction, power supply'),
('S&T', 'Signal & Telecom', 'Signal & Telecommunication Department'),
('CIVIL', 'Civil Engineering', 'Civil Engineering Department - Track, bridges, buildings'),
('OPER', 'Operations', 'Operations Department - Train operations, crew management'),
('SEC', 'Security', 'Security Department - Railway Protection Force, safety'),
('MED', 'Medical', 'Medical Department - Healthcare services for railway employees'),
('PERS', 'Personnel', 'Personnel Department - Human resources, staff management'),
('FIN', 'Finance', 'Finance Department - Accounts, budget, expenditure'),
('STORES', 'Stores', 'Stores Department - Procurement, inventory management'),
('CATERING', 'Catering', 'Catering Department - Food services, IRCTC coordination');

-- Update the shed table to use proper foreign keys (optional, for data integrity)
-- First, let's see what zones and divisions exist in current data
SELECT DISTINCT zone, division FROM shed ORDER BY zone, division;

-- Update API endpoints to use new tables
-- The existing API will continue to work but will now pull from proper normalized tables

-- Create indexes for better performance
CREATE INDEX idx_divisions_zone ON divisions(zone_code);
CREATE INDEX idx_divisions_active ON divisions(is_active);
CREATE INDEX idx_zones_active ON zones(is_active);
CREATE INDEX idx_departments_active ON departments(is_active);

-- Create a view for easy zone-division-department combinations
CREATE OR REPLACE VIEW `zone_division_departments` AS
SELECT 
    z.zone_code,
    z.zone_name,
    d.division_code,
    d.division_name,
    dept.department_code,
    dept.department_name,
    z.is_active as zone_active,
    d.is_active as division_active,
    dept.is_active as department_active
FROM zones z
CROSS JOIN divisions d ON d.zone_code = z.zone_code
CROSS JOIN departments dept
WHERE z.is_active = 1 AND d.is_active = 1 AND dept.is_active = 1;

-- Add sample data to test the RBAC functionality
-- Note: Run this AFTER creating the tables above

-- Sample insert for testing (adjust according to your user structure)
-- Uncomment and modify as needed:
/*
-- Sample controller_nodal user for CR zone, CSMT division
INSERT INTO `users` (`login_id`, `password`, `role`, `department`, `division`, `zone`, `name`, `email`, `mobile`, `status`, `created_at`) VALUES
('CN_CSMT_001', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'controller_nodal', 'Commercial', 'CSMT', 'CR', 'Nodal Controller CSMT', 'nodal.csmt@railway.gov.in', '9876543210', 'active', NOW());

-- Sample regular controller for CR zone, CSMT division
INSERT INTO `users` (`login_id`, `password`, `role`, `department`, `division`, `zone`, `name`, `email`, `mobile`, `status`, `created_at`) VALUES
('C_CSMT_001', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'controller', 'Commercial', 'CSMT', 'CR', 'Controller CSMT', 'controller.csmt@railway.gov.in', '9876543211', 'active', NOW());
*/

-- Update the existing API endpoints to support the new structure
-- The getDivisions and getZones methods in ApiController.php will be updated to use these tables

-- RBAC Rules Summary:
-- 1. controller_nodal can forward to:
--    - Any department within their division
--    - Only Commercial department when forwarding outside their division
-- 2. controller can forward to:
--    - Any department within their division only
-- 3. Priority is ALWAYS reset to "normal" when forwarding (no user control)

COMMIT;