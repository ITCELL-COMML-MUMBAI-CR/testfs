-- Simple Migration: Enhanced Notifications System
-- Date: 2025-09-16
-- Description: Safe migration to update notifications table with error handling

-- Step 1: Add new columns to notifications table (with error handling)
SET @sql = 'ALTER TABLE `notifications` ADD COLUMN `user_type` ENUM(''customer'', ''controller'', ''controller_nodal'', ''admin'', ''superadmin'') DEFAULT NULL AFTER `customer_id`';
SET @error_msg = '';

-- Execute ALTER statements individually for better error handling
BEGIN
    DECLARE CONTINUE HANDLER FOR SQLEXCEPTION SET @error_msg = 'Error adding user_type column';
    PREPARE stmt FROM @sql;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;
END;

-- Add priority column
SET @sql = 'ALTER TABLE `notifications` ADD COLUMN `priority` ENUM(''low'', ''medium'', ''high'', ''urgent'', ''critical'') DEFAULT ''medium'' AFTER `type`';
BEGIN
    DECLARE CONTINUE HANDLER FOR SQLEXCEPTION SET @error_msg = 'Priority column might already exist';
    PREPARE stmt FROM @sql;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;
END;

-- Add related_id column
SET @sql = 'ALTER TABLE `notifications` ADD COLUMN `related_id` VARCHAR(50) DEFAULT NULL AFTER `complaint_id`';
BEGIN
    DECLARE CONTINUE HANDLER FOR SQLEXCEPTION SET @error_msg = 'related_id column might already exist';
    PREPARE stmt FROM @sql;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;
END;

-- Add related_type column
SET @sql = 'ALTER TABLE `notifications` ADD COLUMN `related_type` VARCHAR(50) DEFAULT NULL AFTER `related_id`';
BEGIN
    DECLARE CONTINUE HANDLER FOR SQLEXCEPTION SET @error_msg = 'related_type column might already exist';
    PREPARE stmt FROM @sql;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;
END;

-- Add expires_at column
SET @sql = 'ALTER TABLE `notifications` ADD COLUMN `expires_at` TIMESTAMP NULL DEFAULT NULL AFTER `read_at`';
BEGIN
    DECLARE CONTINUE HANDLER FOR SQLEXCEPTION SET @error_msg = 'expires_at column might already exist';
    PREPARE stmt FROM @sql;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;
END;

-- Add metadata column
SET @sql = 'ALTER TABLE `notifications` ADD COLUMN `metadata` JSON DEFAULT NULL AFTER `expires_at`';
BEGIN
    DECLARE CONTINUE HANDLER FOR SQLEXCEPTION SET @error_msg = 'metadata column might already exist';
    PREPARE stmt FROM @sql;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;
END;

-- Add dismissed_at column
SET @sql = 'ALTER TABLE `notifications` ADD COLUMN `dismissed_at` TIMESTAMP NULL DEFAULT NULL AFTER `metadata`';
BEGIN
    DECLARE CONTINUE HANDLER FOR SQLEXCEPTION SET @error_msg = 'dismissed_at column might already exist';
    PREPARE stmt FROM @sql;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;
END;

-- Add updated_at column
SET @sql = 'ALTER TABLE `notifications` ADD COLUMN `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER `dismissed_at`';
BEGIN
    DECLARE CONTINUE HANDLER FOR SQLEXCEPTION SET @error_msg = 'updated_at column might already exist';
    PREPARE stmt FROM @sql;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;
END;

-- Update type enum to include new notification types
ALTER TABLE `notifications`
MODIFY COLUMN `type` ENUM('info','success','warning','error','escalation','ticket_created','ticket_updated','ticket_assigned','ticket_replied','ticket_resolved','ticket_escalated','priority_escalated','system_announcement','maintenance_alert','sla_warning','account_update') DEFAULT 'info';

-- Add indexes (with error handling)
CREATE INDEX IF NOT EXISTS `idx_notifications_user_type` ON `notifications` (`user_id`, `user_type`, `is_read`);
CREATE INDEX IF NOT EXISTS `idx_notifications_customer` ON `notifications` (`customer_id`, `is_read`);
CREATE INDEX IF NOT EXISTS `idx_notifications_priority` ON `notifications` (`priority`, `created_at`);
CREATE INDEX IF NOT EXISTS `idx_notifications_related` ON `notifications` (`related_id`, `related_type`);
CREATE INDEX IF NOT EXISTS `idx_notifications_expires` ON `notifications` (`expires_at`);

-- Create notification_settings table
CREATE TABLE IF NOT EXISTS `notification_settings` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) DEFAULT NULL,
  `customer_id` VARCHAR(20) DEFAULT NULL,
  `user_type` ENUM('customer', 'controller', 'controller_nodal', 'admin', 'superadmin') NOT NULL,
  `email_enabled` TINYINT(1) DEFAULT 1,
  `sms_enabled` TINYINT(1) DEFAULT 0,
  `browser_enabled` TINYINT(1) DEFAULT 1,
  `priority_escalation_enabled` TINYINT(1) DEFAULT 1,
  `frequency` ENUM('immediate', 'hourly', 'daily', 'weekly') DEFAULT 'immediate',
  `types_enabled` JSON DEFAULT NULL COMMENT 'Array of enabled notification types',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_user_settings` (`user_id`, `customer_id`, `user_type`),
  INDEX `idx_notification_settings_user` (`user_id`, `user_type`),
  INDEX `idx_notification_settings_customer` (`customer_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create notification_templates table
CREATE TABLE IF NOT EXISTS `notification_templates` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `template_code` VARCHAR(100) NOT NULL UNIQUE,
  `name` VARCHAR(255) NOT NULL,
  `description` TEXT DEFAULT NULL,
  `type` ENUM('email', 'sms', 'browser', 'all') DEFAULT 'all',
  `subject` VARCHAR(255) DEFAULT NULL,
  `body_html` TEXT DEFAULT NULL,
  `body_text` TEXT DEFAULT NULL,
  `variables` JSON DEFAULT NULL COMMENT 'Available template variables',
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_notification_templates_code` (`template_code`, `is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default notification templates (ignore if they already exist)
INSERT IGNORE INTO `notification_templates` (`template_code`, `name`, `description`, `subject`, `body_html`, `body_text`, `variables`) VALUES
('priority_escalated', 'Priority Escalation Notification', 'Sent when ticket priority is escalated', 'Ticket {{ticket_id}} Priority Escalated to {{priority}}',
'<h3>Priority Escalation Alert</h3><p>Ticket #{{ticket_id}} has been escalated to <strong>{{priority}}</strong> priority.</p><p><a href="{{view_url}}" class="btn btn-primary">View Ticket</a></p>',
'Ticket #{{ticket_id}} has been escalated to {{priority}} priority. View details: {{view_url}}',
'["ticket_id", "priority", "view_url", "customer_name", "escalation_reason"]'),

('ticket_assigned', 'Ticket Assignment Notification', 'Sent when ticket is assigned to user', 'New Ticket Assigned: {{ticket_id}}',
'<h3>New Ticket Assignment</h3><p>Ticket #{{ticket_id}} has been assigned to you.</p><p>Customer: {{customer_name}}</p><p><a href="{{view_url}}" class="btn btn-primary">View Ticket</a></p>',
'New ticket #{{ticket_id}} assigned to you from {{customer_name}}. View: {{view_url}}',
'["ticket_id", "customer_name", "view_url", "priority", "category"]'),

('critical_priority_alert', 'Critical Priority Alert', 'Sent to admins when ticket reaches critical priority', 'CRITICAL: Ticket {{ticket_id}} Requires Immediate Attention',
'<h3 style="color: red;">⚠️ CRITICAL PRIORITY ALERT</h3><p>Ticket #{{ticket_id}} has reached <strong>CRITICAL</strong> priority and requires immediate attention.</p><p><strong>Customer:</strong> {{customer_name}}</p><p><strong>Division:</strong> {{division}}</p><p><a href="{{view_url}}" class="btn btn-danger">TAKE ACTION NOW</a></p>',
'CRITICAL: Ticket #{{ticket_id}} from {{customer_name}} requires immediate attention. Division: {{division}}. View: {{view_url}}',
'["ticket_id", "customer_name", "division", "view_url", "escalation_time"]');

-- Add escalation tracking columns to complaints table if they don't exist
SET @sql = 'ALTER TABLE `complaints` ADD COLUMN `escalated_at` TIMESTAMP NULL DEFAULT NULL AFTER `updated_at`';
BEGIN
    DECLARE CONTINUE HANDLER FOR SQLEXCEPTION SET @error_msg = 'escalated_at column might already exist';
    PREPARE stmt FROM @sql;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;
END;

SET @sql = 'ALTER TABLE `complaints` ADD COLUMN `escalation_stopped` TINYINT(1) DEFAULT 0 AFTER `escalated_at`';
BEGIN
    DECLARE CONTINUE HANDLER FOR SQLEXCEPTION SET @error_msg = 'escalation_stopped column might already exist';
    PREPARE stmt FROM @sql;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;
END;

-- Add escalation index
CREATE INDEX IF NOT EXISTS `idx_complaints_escalation` ON `complaints` (`escalated_at`, `escalation_stopped`, `priority`);

-- Create notification_logs table (simple version without foreign key)
CREATE TABLE IF NOT EXISTS `notification_logs` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `notification_id` INT(11) DEFAULT NULL,
  `action` ENUM('created', 'sent', 'delivered', 'read', 'dismissed', 'expired') NOT NULL,
  `channel` ENUM('email', 'sms', 'browser', 'system') DEFAULT 'system',
  `status` ENUM('success', 'failed', 'pending') DEFAULT 'pending',
  `details` JSON DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_notification_logs_notification` (`notification_id`, `action`),
  INDEX `idx_notification_logs_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Success message
SELECT 'Notification system migration completed successfully!' as status;