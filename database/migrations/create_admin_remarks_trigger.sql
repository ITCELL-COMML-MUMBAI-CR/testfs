-- Create trigger for admin_remarks table
DELIMITER ;;

CREATE TRIGGER tr_admin_remarks_before_insert
BEFORE INSERT ON admin_remarks
FOR EACH ROW
BEGIN
    DECLARE ticket_closed_at TIMESTAMP DEFAULT NULL;

    -- Get the closed_at timestamp for the ticket
    SELECT closed_at INTO ticket_closed_at
    FROM complaints
    WHERE complaint_id = NEW.complaint_id;

    -- Calculate if the remark is created within 3 days
    IF ticket_closed_at IS NOT NULL THEN
        SET NEW.created_within_3_days = (TIMESTAMPDIFF(DAY, ticket_closed_at, NOW()) <= 3);
    ELSE
        SET NEW.created_within_3_days = FALSE;
    END IF;
END;;

DELIMITER ;