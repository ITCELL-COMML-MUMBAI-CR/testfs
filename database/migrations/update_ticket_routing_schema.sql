-- Migration Script for New Ticket Routing Logic
-- Adds department and CML admin approval layers to the workflow
-- Author: AI Assistant
-- Date: 2025-09-26

-- First, add new status values to support admin approvals
ALTER TABLE complaints MODIFY COLUMN status ENUM(
    'pending',
    'awaiting_feedback',
    'awaiting_info',
    'awaiting_approval',
    'awaiting_dept_admin_approval',
    'awaiting_cml_admin_approval',
    'closed'
) DEFAULT 'pending';

-- Add fields to track approval workflow
ALTER TABLE complaints
ADD COLUMN dept_admin_approved_by INT(11) DEFAULT NULL AFTER assigned_to_department,
ADD COLUMN dept_admin_approved_at TIMESTAMP NULL DEFAULT NULL AFTER dept_admin_approved_by,
ADD COLUMN dept_admin_remarks TEXT DEFAULT NULL AFTER dept_admin_approved_at,
ADD COLUMN cml_admin_approved_by INT(11) DEFAULT NULL AFTER dept_admin_remarks,
ADD COLUMN cml_admin_approved_at TIMESTAMP NULL DEFAULT NULL AFTER cml_admin_approved_by,
ADD COLUMN cml_admin_remarks TEXT DEFAULT NULL AFTER cml_admin_approved_at;

-- Add foreign key constraints for admin approvals
ALTER TABLE complaints
ADD CONSTRAINT fk_complaints_dept_admin
FOREIGN KEY (dept_admin_approved_by) REFERENCES users(id) ON DELETE SET NULL,
ADD CONSTRAINT fk_complaints_cml_admin
FOREIGN KEY (cml_admin_approved_by) REFERENCES users(id) ON DELETE SET NULL;

-- Create admin_remarks table for tracking admin feedback on departments
CREATE TABLE admin_remarks (
    id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    complaint_id VARCHAR(20) NOT NULL,
    admin_id INT(11) NOT NULL,
    admin_type ENUM('dept_admin', 'cml_admin') NOT NULL,
    department VARCHAR(100) NOT NULL,
    division VARCHAR(100) NOT NULL,
    zone VARCHAR(100) NOT NULL,
    remarks TEXT NOT NULL,
    remarks_category VARCHAR(100) DEFAULT NULL,
    is_recurring_issue BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    created_within_3_days BOOLEAN GENERATED ALWAYS AS (
        TIMESTAMPDIFF(DAY, (SELECT closed_at FROM complaints WHERE complaint_id = admin_remarks.complaint_id), created_at) <= 3
    ) STORED,
    FOREIGN KEY (complaint_id) REFERENCES complaints(complaint_id) ON DELETE CASCADE,
    FOREIGN KEY (admin_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_complaint_admin (complaint_id, admin_type),
    INDEX idx_department_remarks (department, remarks_category, created_at),
    INDEX idx_recurring_issues (department, is_recurring_issue, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Admin remarks on tickets with department-wise reporting';

-- Update transactions table to support new transaction types
ALTER TABLE transactions MODIFY COLUMN transaction_type ENUM(
    'created',
    'forwarded',
    'replied',
    'approved',
    'rejected',
    'reverted',
    'closed',
    'escalated',
    'feedback_submitted',
    'priority_escalated',
    'info_requested',
    'interim_remarks',
    'priority_reset',
    'info_provided',
    'dept_admin_approved',
    'dept_admin_rejected',
    'cml_admin_approved',
    'cml_admin_rejected',
    'admin_remarks_added'
) NOT NULL;

-- Update remarks_type to include admin remarks
ALTER TABLE transactions MODIFY COLUMN remarks_type ENUM(
    'internal_remarks',
    'interim_remarks',
    'forwarding_remarks',
    'admin_remarks',
    'customer_remarks',
    'system_remarks',
    'priority_escalation',
    'dept_admin_remarks',
    'cml_admin_remarks'
) DEFAULT 'internal_remarks';

-- Create approval_workflow_log table to track approval chain
CREATE TABLE approval_workflow_log (
    id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    complaint_id VARCHAR(20) NOT NULL,
    workflow_step ENUM('controller_reply', 'dept_admin_review', 'cml_admin_review', 'customer_feedback') NOT NULL,
    action ENUM('submit', 'approve', 'reject', 'edit_and_approve') NOT NULL,
    performed_by INT(11) NOT NULL,
    performed_by_role ENUM('controller', 'controller_nodal', 'admin', 'superadmin') NOT NULL,
    original_content TEXT DEFAULT NULL,
    edited_content TEXT DEFAULT NULL,
    remarks TEXT DEFAULT NULL,
    rejection_reason TEXT DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (complaint_id) REFERENCES complaints(complaint_id) ON DELETE CASCADE,
    FOREIGN KEY (performed_by) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_workflow_tracking (complaint_id, workflow_step, created_at),
    INDEX idx_performer_tracking (performed_by, performed_by_role, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Detailed log of approval workflow steps and actions';

-- Add indexes for better performance on new queries
ALTER TABLE complaints
ADD INDEX idx_dept_admin_approval (dept_admin_approved_by, dept_admin_approved_at),
ADD INDEX idx_cml_admin_approval (cml_admin_approved_by, cml_admin_approved_at),
ADD INDEX idx_status_admin_workflow (status, dept_admin_approved_at, cml_admin_approved_at);

-- Create view for admin remarks reporting
CREATE VIEW admin_remarks_report AS
SELECT
    ar.department,
    ar.division,
    ar.zone,
    ar.remarks_category,
    COUNT(*) as total_remarks,
    COUNT(CASE WHEN ar.is_recurring_issue = TRUE THEN 1 END) as recurring_issues,
    COUNT(CASE WHEN ar.created_within_3_days = TRUE THEN 1 END) as remarks_within_3_days,
    COUNT(DISTINCT ar.complaint_id) as unique_tickets,
    GROUP_CONCAT(DISTINCT u.name ORDER BY ar.created_at DESC SEPARATOR '; ') as admin_names,
    MIN(ar.created_at) as first_remark_date,
    MAX(ar.created_at) as last_remark_date
FROM admin_remarks ar
LEFT JOIN users u ON ar.admin_id = u.id
GROUP BY ar.department, ar.division, ar.zone, ar.remarks_category;

-- Update system settings for new workflow
INSERT INTO system_settings (setting_key, setting_value, setting_type, description, group_name) VALUES
('admin_approval_timeout_hours', '24', 'integer', 'Hours to wait for admin approval before escalation', 'workflow'),
('allow_admin_remarks_days', '3', 'integer', 'Days after closure admin can add remarks', 'workflow'),
('require_dept_admin_approval', '1', 'boolean', 'Require department admin approval for all closures', 'workflow'),
('require_cml_admin_approval', '1', 'boolean', 'Require CML admin approval after dept admin', 'workflow')
ON DUPLICATE KEY UPDATE
setting_value = VALUES(setting_value),
description = VALUES(description);