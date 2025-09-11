-- Database migration to remove user-based ticket assignment
-- Route tickets by department, division, and zone instead

-- 1. Remove foreign key constraint for assigned_to_user_id
ALTER TABLE complaints DROP FOREIGN KEY complaints_ibfk_2;

-- 2. Remove the index for assigned_to_user_id
ALTER TABLE complaints DROP INDEX idx_assigned_user;

-- 3. Drop the assigned_to_user_id column
ALTER TABLE complaints DROP COLUMN assigned_to_user_id;

-- 4. Update the active_tickets view to remove assigned user references
DROP VIEW IF EXISTS active_tickets;

CREATE VIEW `active_tickets` AS 
SELECT 
    `c`.`complaint_id` AS `complaint_id`, 
    `c`.`category_id` AS `category_id`, 
    `c`.`date` AS `date`, 
    `c`.`time` AS `time`, 
    `c`.`shed_id` AS `shed_id`, 
    `c`.`wagon_id` AS `wagon_id`, 
    `c`.`rating` AS `rating`, 
    `c`.`rating_remarks` AS `rating_remarks`, 
    `c`.`description` AS `description`, 
    `c`.`action_taken` AS `action_taken`, 
    `c`.`status` AS `status`, 
    `c`.`department` AS `department`, 
    `c`.`division` AS `division`, 
    `c`.`zone` AS `zone`, 
    `c`.`customer_id` AS `customer_id`, 
    `c`.`fnr_number` AS `fnr_number`, 
    `c`.`gstin_number` AS `gstin_number`, 
    `c`.`e_indent_number` AS `e_indent_number`, 
    `c`.`assigned_to_department` AS `assigned_to_department`, 
    `c`.`forwarded_flag` AS `forwarded_flag`, 
    `c`.`priority` AS `priority`, 
    `c`.`sla_deadline` AS `sla_deadline`, 
    `c`.`created_at` AS `created_at`, 
    `c`.`updated_at` AS `updated_at`, 
    `c`.`closed_at` AS `closed_at`, 
    `c`.`escalated_at` AS `escalated_at`, 
    `cat`.`category` AS `category`, 
    `cat`.`type` AS `type`, 
    `cat`.`subtype` AS `subtype`, 
    `s`.`name` AS `shed_name`, 
    `s`.`shed_code` AS `shed_code`, 
    `cust`.`name` AS `customer_name`, 
    `cust`.`email` AS `customer_email`, 
    `cust`.`mobile` AS `customer_mobile`, 
    `cust`.`company_name` AS `company_name`
FROM 
    `complaints` `c` 
    LEFT JOIN `complaint_categories` `cat` ON `c`.`category_id` = `cat`.`category_id` 
    LEFT JOIN `shed` `s` ON `c`.`shed_id` = `s`.`shed_id` 
    LEFT JOIN `customers` `cust` ON `c`.`customer_id` = `cust`.`customer_id` 
WHERE 
    `c`.`status` <> 'closed';

-- Migration completed: Tickets now routed by department/division/zone instead of individual users