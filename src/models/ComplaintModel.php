<?php
/**
 * Complaint Model for SAMPARK
 * Handles complaint/ticket related database operations
 */

require_once 'BaseModel.php';

class ComplaintModel extends BaseModel {
    
    protected $table = 'complaints';
    protected $primaryKey = 'complaint_id';
    
    protected $fillable = [
        'complaint_id', 'category_id', 'date', 'time', 'shed_id', 'wagon_id',
        'rating', 'rating_remarks', 'description', 'action_taken', 'status',
        'department', 'division', 'zone', 'customer_id', 'fnr_number',
        'gstin_number', 'e_indent_number', 'assigned_to_department',
        'forwarded_flag', 'priority'
    ];
    
    /**
     * Get complaint with related data
     */
    public function getComplaintWithDetails($complaintId) {
        $sql = "SELECT c.*,
                       cat.category, cat.type, cat.subtype,
                       s.name as shed_name, s.shed_code,
                       w.wagon_code, w.type as wagon_type,
                       cust.name as customer_name, cust.email as customer_email,
                       cust.mobile as customer_mobile, cust.company_name,
                       TIMESTAMPDIFF(HOUR, c.created_at, NOW()) as hours_elapsed
                FROM complaints c
                LEFT JOIN complaint_categories cat ON c.category_id = cat.category_id
                LEFT JOIN shed s ON c.shed_id = s.shed_id
                LEFT JOIN wagon_details w ON c.wagon_id = w.wagon_id
                LEFT JOIN customers cust ON c.customer_id = cust.customer_id
                WHERE c.complaint_id = ?";

        return $this->fetch($sql, [$complaintId]);
    }
    
    /**
     * Get complaints for customer
     */
    public function getCustomerComplaints($customerId, $page = 1, $perPage = 20, $filters = []) {
        $conditions = ['c.customer_id = ?'];
        $params = [$customerId];
        
        // Add filters
        if (!empty($filters['status'])) {
            $conditions[] = 'c.status = ?';
            $params[] = $filters['status'];
        }
        
        if (!empty($filters['priority'])) {
            $conditions[] = 'c.priority = ?';
            $params[] = $filters['priority'];
        }
        
        if (!empty($filters['date_from'])) {
            $conditions[] = 'c.created_at >= ?';
            $params[] = $filters['date_from'] . ' 00:00:00';
        }
        
        if (!empty($filters['date_to'])) {
            $conditions[] = 'c.created_at <= ?';
            $params[] = $filters['date_to'] . ' 23:59:59';
        }
        
        $whereClause = implode(' AND ', $conditions);
        $offset = ($page - 1) * $perPage;
        
        // Get total count
        $countSql = "SELECT COUNT(*) as total FROM complaints c WHERE {$whereClause}";
        $totalResult = $this->fetch($countSql, $params);
        $total = $totalResult['total'];
        
        // Get paginated data
        $sql = "SELECT c.*, 
                       cat.category, cat.type, cat.subtype,
                       s.name as shed_name, s.shed_code,
                       TIMESTAMPDIFF(HOUR, c.created_at, NOW()) as hours_elapsed
                FROM complaints c
                LEFT JOIN complaint_categories cat ON c.category_id = cat.category_id
                LEFT JOIN shed s ON c.shed_id = s.shed_id
                WHERE {$whereClause}
                ORDER BY c.created_at DESC
                LIMIT {$offset}, {$perPage}";
        
        $data = $this->fetchAll($sql, $params);
        
        return [
            'data' => $data,
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => ceil($total / $perPage),
            'has_next' => $page < ceil($total / $perPage),
            'has_prev' => $page > 1
        ];
    }
    
    /**
     * Get complaints for user (controller/admin)
     */
    public function getUserComplaints($userId, $userRole, $division = null, $page = 1, $perPage = 20, $filters = []) {
        $conditions = [];
        $params = [];
        
        // Role-based access control
        if ($userRole === 'controller') {
            // Controllers see tickets assigned to their department
            // We'll need to implement proper user-department mapping later
        } elseif ($userRole === 'controller_nodal' && $division) {
            $conditions[] = 'c.division = ?';
            $params[] = $division;
        }
        // Admin/Superadmin can see all tickets (no additional conditions)
        
        // Add filters
        if (!empty($filters['status'])) {
            $conditions[] = 'c.status = ?';
            $params[] = $filters['status'];
        }
        
        if (!empty($filters['priority'])) {
            $conditions[] = 'c.priority = ?';
            $params[] = $filters['priority'];
        }
        
        if (!empty($filters['division'])) {
            $conditions[] = 'c.division = ?';
            $params[] = $filters['division'];
        }
        
        if (!empty($filters['date_from'])) {
            $conditions[] = 'c.created_at >= ?';
            $params[] = $filters['date_from'] . ' 00:00:00';
        }
        
        if (!empty($filters['date_to'])) {
            $conditions[] = 'c.created_at <= ?';
            $params[] = $filters['date_to'] . ' 23:59:59';
        }
        
        $whereClause = !empty($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';
        $offset = ($page - 1) * $perPage;
        
        // Get total count
        $countSql = "SELECT COUNT(*) as total FROM complaints c {$whereClause}";
        $totalResult = $this->fetch($countSql, $params);
        $total = $totalResult['total'];
        
        // Get paginated data
        $sql = "SELECT c.*,
                       cat.category, cat.type, cat.subtype,
                       s.name as shed_name, s.shed_code,
                       cust.name as customer_name, cust.company_name,
                       TIMESTAMPDIFF(HOUR, c.created_at, NOW()) as hours_elapsed
                FROM complaints c
                LEFT JOIN complaint_categories cat ON c.category_id = cat.category_id
                LEFT JOIN shed s ON c.shed_id = s.shed_id
                LEFT JOIN customers cust ON c.customer_id = cust.customer_id
                {$whereClause}
                ORDER BY 
                    CASE WHEN c.priority = 'critical' THEN 1
                         WHEN c.priority = 'high' THEN 2
                         WHEN c.priority = 'medium' THEN 3
                         ELSE 4
                    END,
                    c.created_at ASC
                LIMIT {$offset}, {$perPage}";
        
        $data = $this->fetchAll($sql, $params);
        
        return [
            'data' => $data,
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => ceil($total / $perPage),
            'has_next' => $page < ceil($total / $perPage),
            'has_prev' => $page > 1
        ];
    }
    
    /**
     * Get ticket statistics for dashboard
     */
    public function getTicketStats($userId = null, $userRole = null, $division = null, $customerId = null) {
        $conditions = [];
        $params = [];
        
        if ($customerId) {
            $conditions[] = 'customer_id = ?';
            $params[] = $customerId;
        } elseif ($userId && $userRole === 'controller') {
            // Controllers see tickets assigned to their department
            // We'll need to implement proper user-department mapping later
        } elseif ($division && $userRole === 'controller_nodal') {
            $conditions[] = 'division = ?';
            $params[] = $division;
        }
        
        $whereClause = !empty($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';
        
        $sql = "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                    SUM(CASE WHEN status = 'awaiting_info' THEN 1 ELSE 0 END) as awaiting_info,
                    SUM(CASE WHEN status = 'awaiting_approval' THEN 1 ELSE 0 END) as awaiting_approval,
                    SUM(CASE WHEN status = 'awaiting_feedback' THEN 1 ELSE 0 END) as awaiting_feedback,
                    SUM(CASE WHEN status = 'closed' THEN 1 ELSE 0 END) as closed,
                    SUM(CASE WHEN priority IN ('high', 'critical') AND status != 'closed' THEN 1 ELSE 0 END) as high_priority_count,
                    SUM(CASE WHEN escalated_at IS NOT NULL THEN 1 ELSE 0 END) as escalated
                FROM complaints {$whereClause}";
        
        return $this->fetch($sql, $params);
    }
    
    
    
    /**
     * Get customer satisfaction metrics
     */
    public function getCustomerSatisfactionMetrics($dateFrom = null, $dateTo = null, $division = null) {
        $conditions = ["status = 'closed'", "rating IS NOT NULL"];
        $params = [];
        
        if ($dateFrom) {
            $conditions[] = 'closed_at >= ?';
            $params[] = $dateFrom . ' 00:00:00';
        }
        
        if ($dateTo) {
            $conditions[] = 'closed_at <= ?';
            $params[] = $dateTo . ' 23:59:59';
        }
        
        if ($division) {
            $conditions[] = 'division = ?';
            $params[] = $division;
        }
        
        $whereClause = implode(' AND ', $conditions);
        
        $sql = "SELECT 
                    COUNT(*) as total_ratings,
                    SUM(CASE WHEN rating = 'excellent' THEN 1 ELSE 0 END) as excellent,
                    SUM(CASE WHEN rating = 'satisfactory' THEN 1 ELSE 0 END) as satisfactory,
                    SUM(CASE WHEN rating = 'unsatisfactory' THEN 1 ELSE 0 END) as unsatisfactory,
                    ROUND(
                        (SUM(CASE WHEN rating = 'excellent' THEN 100 WHEN rating = 'satisfactory' THEN 75 WHEN rating = 'unsatisfactory' THEN 25 ELSE 0 END) / COUNT(*)), 2
                    ) as satisfaction_score,
                    ROUND(
                        (SUM(CASE WHEN rating IN ('excellent', 'satisfactory') THEN 1 ELSE 0 END) * 100.0 / COUNT(*)), 2
                    ) as positive_rating_percentage
                FROM complaints 
                WHERE {$whereClause}";
        
        return $this->fetch($sql, $params);
    }
    
    /**
     * Update complaint status
     */
    public function updateStatus($complaintId, $status, $additionalData = []) {
        $data = array_merge(['status' => $status], $additionalData);
        
        if ($status === 'closed') {
            $data['closed_at'] = date('Y-m-d H:i:s');
        }
        
        return $this->updateByComplaintId($complaintId, $data);
    }
    
    /**
     * Update by complaint ID (custom primary key)
     */
    public function updateByComplaintId($complaintId, $data) {
        $data = $this->filterFillable($data);
        
        if ($this->timestamps) {
            $data['updated_at'] = date('Y-m-d H:i:s');
        }
        
        $setParts = [];
        $params = [];
        
        foreach ($data as $field => $value) {
            $setParts[] = "{$field} = ?";
            $params[] = $value;
        }
        
        $params[] = $complaintId;
        
        $sql = "UPDATE {$this->table} SET " . implode(', ', $setParts) . " WHERE complaint_id = ?";
        
        return $this->query($sql, $params);
    }
    
    /**
     * Get performance metrics for user/division
     */
    public function getPerformanceMetrics($userId = null, $division = null, $dateFrom = null, $dateTo = null) {
        $conditions = [];
        $params = [];
        
        if ($userId) {
            // Performance metrics for individual users
            // We'll need to implement proper user-department mapping later
        } elseif ($division) {
            $conditions[] = 'division = ?';
            $params[] = $division;
        }
        
        if ($dateFrom) {
            $conditions[] = 'created_at >= ?';
            $params[] = $dateFrom . ' 00:00:00';
        }
        
        if ($dateTo) {
            $conditions[] = 'created_at <= ?';
            $params[] = $dateTo . ' 23:59:59';
        }
        
        $whereClause = !empty($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';
        
        $sql = "SELECT 
                    COUNT(*) as total_handled,
                    SUM(CASE WHEN status = 'closed' THEN 1 ELSE 0 END) as resolved,
                    AVG(CASE WHEN status = 'closed' THEN TIMESTAMPDIFF(HOUR, created_at, closed_at) END) as avg_resolution_hours,
                    SUM(CASE WHEN status = 'closed' AND rating = 'excellent' THEN 1 ELSE 0 END) as excellent_ratings,
                    SUM(CASE WHEN escalated_at IS NOT NULL THEN 1 ELSE 0 END) as escalations
                FROM complaints {$whereClause}";
        
        return $this->fetch($sql, $params);
    }
}
