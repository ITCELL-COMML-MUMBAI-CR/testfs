<?php
/**
 * Admin Remarks Model
 * Handles admin remarks on tickets with department-wise reporting
 */

require_once __DIR__ . '/../config/Config.php';

class AdminRemarksModel {

    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Add admin remarks to a ticket
     */
    public function addAdminRemarks($complaintId, $adminId, $adminType, $remarks, $remarksCategory = null, $isRecurringIssue = false) {
        try {
            // Get ticket details for department/division/zone
            $ticket = $this->getTicketDetails($complaintId);
            if (!$ticket) {
                throw new Exception("Ticket not found: {$complaintId}");
            }

            // Check if admin can add remarks (within 3 days of closure)
            if (!$this->canAddRemarks($ticket)) {
                throw new Exception("Admin remarks can only be added within 3 days of ticket closure");
            }

            $sql = "INSERT INTO admin_remarks (
                        complaint_id, admin_id, admin_type, department, division, zone,
                        remarks, remarks_category, is_recurring_issue
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

            $this->db->query($sql, [
                $complaintId,
                $adminId,
                $adminType,
                $ticket['department'],
                $ticket['division'],
                $ticket['zone'],
                $remarks,
                $remarksCategory,
                $isRecurringIssue ? 1 : 0
            ]);

            $remarksId = $this->db->lastInsertId();

            // Log this action in transactions
            $this->logAdminRemarksTransaction($complaintId, $adminId, $remarks, $remarksCategory);

            // Log activity
            require_once __DIR__ . '/../utils/ActivityLogger.php';
            $logger = new ActivityLogger();
            $logger->logTicket($complaintId, 'admin_remarks_added', $adminId, 'admin', [
                'remarks_category' => $remarksCategory,
                'is_recurring_issue' => $isRecurringIssue,
                'admin_type' => $adminType
            ]);

            return [
                'success' => true,
                'remarks_id' => $remarksId,
                'message' => 'Admin remarks added successfully'
            ];

        } catch (Exception $e) {
            error_log("Error adding admin remarks: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get admin remarks for a ticket
     */
    public function getTicketAdminRemarks($complaintId) {
        $sql = "SELECT ar.*, u.name as admin_name, u.role as admin_role
                FROM admin_remarks ar
                LEFT JOIN users u ON ar.admin_id = u.id
                WHERE ar.complaint_id = ?
                ORDER BY ar.created_at DESC";

        return $this->db->fetchAll($sql, [$complaintId]);
    }

    /**
     * Get admin remarks report for department
     */
    public function getDepartmentRemarksReport($department, $dateFrom = null, $dateTo = null, $division = null, $zone = null) {
        $whereClauses = ["ar.department = ?"];
        $params = [$department];

        if ($division) {
            $whereClauses[] = "ar.division = ?";
            $params[] = $division;
        }

        if ($zone) {
            $whereClauses[] = "ar.zone = ?";
            $params[] = $zone;
        }

        if ($dateFrom) {
            $whereClauses[] = "ar.created_at >= ?";
            $params[] = $dateFrom;
        }

        if ($dateTo) {
            $whereClauses[] = "ar.created_at <= ?";
            $params[] = $dateTo;
        }

        $whereClause = implode(' AND ', $whereClauses);

        $sql = "SELECT
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
                WHERE {$whereClause}
                GROUP BY ar.department, ar.division, ar.zone, ar.remarks_category
                ORDER BY total_remarks DESC, ar.department, ar.remarks_category";

        return $this->db->fetchAll($sql, $params);
    }

    /**
     * Get all admin remarks for a department (for viewing together)
     */
    public function getAllDepartmentRemarks($department, $limit = 100, $offset = 0) {
        $sql = "SELECT
                    ar.*,
                    u.name as admin_name,
                    u.role as admin_role,
                    c.complaint_id,
                    c.description as ticket_description,
                    c.closed_at,
                    cust.name as customer_name
                FROM admin_remarks ar
                LEFT JOIN users u ON ar.admin_id = u.id
                LEFT JOIN complaints c ON ar.complaint_id = c.complaint_id
                LEFT JOIN customers cust ON c.customer_id = cust.customer_id
                WHERE ar.department = ?
                ORDER BY ar.created_at DESC
                LIMIT ? OFFSET ?";

        return $this->db->fetchAll($sql, [$department, $limit, $offset]);
    }

    /**
     * Get summary statistics for admin remarks
     */
    public function getRemarksStatistics($department = null, $dateFrom = null, $dateTo = null) {
        $whereClauses = [];
        $params = [];

        if ($department) {
            $whereClauses[] = "department = ?";
            $params[] = $department;
        }

        if ($dateFrom) {
            $whereClauses[] = "created_at >= ?";
            $params[] = $dateFrom;
        }

        if ($dateTo) {
            $whereClauses[] = "created_at <= ?";
            $params[] = $dateTo;
        }

        $whereClause = empty($whereClauses) ? '' : 'WHERE ' . implode(' AND ', $whereClauses);

        $sql = "SELECT
                    COUNT(*) as total_remarks,
                    COUNT(DISTINCT complaint_id) as tickets_with_remarks,
                    COUNT(DISTINCT department) as departments_with_remarks,
                    COUNT(CASE WHEN is_recurring_issue = TRUE THEN 1 END) as recurring_issues,
                    COUNT(CASE WHEN created_within_3_days = TRUE THEN 1 END) as remarks_within_3_days,
                    AVG(CASE WHEN created_within_3_days = TRUE THEN 1.0 ELSE 0.0 END) * 100 as timely_remarks_percentage
                FROM admin_remarks
                {$whereClause}";

        return $this->db->fetch($sql, $params);
    }

    /**
     * Get most common remark categories
     */
    public function getTopRemarkCategories($limit = 10, $department = null) {
        $whereClauses = ["remarks_category IS NOT NULL"];
        $params = [];

        if ($department) {
            $whereClauses[] = "department = ?";
            $params[] = $department;
        }

        $whereClause = implode(' AND ', $whereClauses);
        $params[] = $limit;

        $sql = "SELECT
                    remarks_category,
                    COUNT(*) as category_count,
                    COUNT(DISTINCT department) as departments_affected,
                    COUNT(CASE WHEN is_recurring_issue = TRUE THEN 1 END) as recurring_count
                FROM admin_remarks
                WHERE {$whereClause}
                GROUP BY remarks_category
                ORDER BY category_count DESC
                LIMIT ?";

        return $this->db->fetchAll($sql, $params);
    }

    /**
     * Check if admin can add remarks (within 3 days of closure)
     */
    private function canAddRemarks($ticket) {
        if ($ticket['status'] !== 'closed' || !$ticket['closed_at']) {
            return false;
        }

        $closedAt = new DateTime($ticket['closed_at']);
        $now = new DateTime();
        $daysDiff = $now->diff($closedAt)->days;

        return $daysDiff <= 3;
    }

    /**
     * Get ticket details
     */
    private function getTicketDetails($complaintId) {
        $sql = "SELECT complaint_id, department, division, zone, status, closed_at
                FROM complaints
                WHERE complaint_id = ?";

        return $this->db->fetch($sql, [$complaintId]);
    }

    /**
     * Log admin remarks transaction
     */
    private function logAdminRemarksTransaction($complaintId, $adminId, $remarks, $remarksCategory) {
        $sql = "INSERT INTO transactions (
                    complaint_id, transaction_type, remarks, remarks_type,
                    created_by_id, created_by_type, created_by_role, created_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";

        $this->db->query($sql, [
            $complaintId,
            'admin_remarks_added',
            $remarks,
            'admin_remarks',
            $adminId,
            'user',
            'admin'
        ]);
    }

    /**
     * Delete admin remarks (if needed)
     */
    public function deleteAdminRemarks($remarksId, $adminId) {
        try {
            // Check if admin owns the remarks or is superadmin
            $remarks = $this->db->fetch(
                "SELECT ar.*, u.role FROM admin_remarks ar LEFT JOIN users u ON ar.admin_id = u.id WHERE ar.id = ?",
                [$remarksId]
            );

            if (!$remarks) {
                throw new Exception("Remarks not found");
            }

            $currentUser = $this->db->fetch("SELECT role FROM users WHERE id = ?", [$adminId]);

            if ($remarks['admin_id'] != $adminId && $currentUser['role'] !== 'superadmin') {
                throw new Exception("You can only delete your own remarks");
            }

            $sql = "DELETE FROM admin_remarks WHERE id = ?";
            $this->db->query($sql, [$remarksId]);

            return [
                'success' => true,
                'message' => 'Admin remarks deleted successfully'
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Update admin remarks
     */
    public function updateAdminRemarks($remarksId, $adminId, $remarks, $remarksCategory = null, $isRecurringIssue = false) {
        try {
            // Check ownership and 3-day rule
            $existingRemarks = $this->db->fetch(
                "SELECT ar.*, c.closed_at FROM admin_remarks ar
                 LEFT JOIN complaints c ON ar.complaint_id = c.complaint_id
                 WHERE ar.id = ?",
                [$remarksId]
            );

            if (!$existingRemarks) {
                throw new Exception("Remarks not found");
            }

            if ($existingRemarks['admin_id'] != $adminId) {
                $currentUser = $this->db->fetch("SELECT role FROM users WHERE id = ?", [$adminId]);
                if ($currentUser['role'] !== 'superadmin') {
                    throw new Exception("You can only edit your own remarks");
                }
            }

            // Check 3-day rule
            if ($existingRemarks['closed_at']) {
                $closedAt = new DateTime($existingRemarks['closed_at']);
                $now = new DateTime();
                $daysDiff = $now->diff($closedAt)->days;

                if ($daysDiff > 3) {
                    throw new Exception("Cannot edit remarks more than 3 days after ticket closure");
                }
            }

            $sql = "UPDATE admin_remarks
                    SET remarks = ?, remarks_category = ?, is_recurring_issue = ?
                    WHERE id = ?";

            $this->db->query($sql, [
                $remarks,
                $remarksCategory,
                $isRecurringIssue ? 1 : 0,
                $remarksId
            ]);

            return [
                'success' => true,
                'message' => 'Admin remarks updated successfully'
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
}