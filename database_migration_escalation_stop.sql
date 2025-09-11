-- Migration to add escalation_stopped column to complaints table
-- This supports the requirement: "Priority escalation stops permanently once reply/action is approved by controller_nodal"

ALTER TABLE complaints 
ADD COLUMN escalation_stopped TINYINT(1) DEFAULT 0 
AFTER escalated_at;

-- Update existing complaints that have been through the approval process
UPDATE complaints 
SET escalation_stopped = 1 
WHERE status = 'awaiting_feedback' 
  AND EXISTS (
    SELECT 1 FROM transactions 
    WHERE transactions.complaint_id = complaints.complaint_id 
      AND transactions.transaction_type = 'approved'
  );