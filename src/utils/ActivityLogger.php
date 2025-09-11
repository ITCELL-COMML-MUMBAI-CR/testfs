<?php
/**
 * Activity Logger for SAMPARK
 * Logs user activities and system events for auditing
 */

class ActivityLogger {
    
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Log user activity
     */
    public function log($data) {
        try {
            $sql = "INSERT INTO activity_logs (
                user_id, customer_id, user_role, action, description, 
                complaint_id, ip_address, user_agent, created_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";
            
            // Fix: Ensure customer_id goes to customer_id field, not user_id
            $userId = null;
            $customerId = null;
            
            if (isset($data['user_id']) && !empty($data['user_id'])) {
                if ($data['user_role'] === 'customer' || strpos($data['user_id'], 'CUST') === 0) {
                    $customerId = $data['user_id'];
                } else {
                    $userId = $data['user_id'];
                }
            }
            
            if (isset($data['customer_id']) && !empty($data['customer_id'])) {
                $customerId = $data['customer_id'];
            }
            
            $params = [
                $userId,
                $customerId,
                $data['user_role'] ?? null,
                $data['action'],
                $data['description'] ?? null,
                $data['complaint_id'] ?? null,
                $data['ip_address'] ?? $_SERVER['REMOTE_ADDR'],
                $data['user_agent'] ?? $_SERVER['HTTP_USER_AGENT']
            ];
            
            $this->db->query($sql, $params);
            
            return true;
            
        } catch (Exception $e) {
            error_log("Activity logging error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Log authentication events
     */
    public function logAuth($action, $identifier, $userType, $success = true, $details = []) {
        $description = $success ? 
            "Successful {$action} for {$userType}: {$identifier}" :
            "Failed {$action} attempt for {$userType}: {$identifier}";
        
        if (!empty($details)) {
            $description .= " - " . json_encode($details);
        }
        
        return $this->log([
            'action' => $action,
            'description' => $description,
            'user_role' => $userType
        ]);
    }
    
    /**
     * Log ticket activities
     */
    public function logTicket($complaintId, $action, $userId, $userType, $details = []) {
        $description = "Ticket {$action}";
        
        if (!empty($details)) {
            if (is_array($details)) {
                $description .= " - " . json_encode($details);
            } else {
                $description .= " - " . $details;
            }
        }
        
        $logData = [
            'complaint_id' => $complaintId,
            'action' => "ticket_{$action}",
            'description' => $description
        ];
        
        if ($userType === 'customer') {
            $logData['customer_id'] = $userId;
            $logData['user_role'] = 'customer';
        } else {
            $logData['user_id'] = $userId;
            $logData['user_role'] = $this->getUserRole($userId);
        }
        
        return $this->log($logData);
    }
    
    /**
     * Log system events
     */
    public function logSystem($action, $description, $details = []) {
        $fullDescription = $description;
        
        if (!empty($details)) {
            $fullDescription .= " - " . (is_array($details) ? json_encode($details) : $details);
        }
        
        return $this->log([
            'action' => "system_{$action}",
            'description' => $fullDescription,
            'user_role' => 'system'
        ]);
    }
    
    /**
     * Log security events
     */
    public function logSecurity($action, $description, $severity = 'medium') {
        $logData = [
            'action' => "security_{$action}",
            'description' => "[{$severity}] {$description}"
        ];
        
        // Also log to security log file for high severity events
        if ($severity === 'high' || $severity === 'critical') {
            $this->logToSecurityFile($action, $description, $severity);
        }
        
        return $this->log($logData);
    }
    
    /**
     * Get user activities for a specific user
     */
    public function getUserActivities($userId, $userType = 'user', $limit = 50, $offset = 0) {
        $whereClause = $userType === 'customer' ? 'customer_id = ?' : 'user_id = ?';
        
        $sql = "SELECT * FROM activity_logs 
                WHERE {$whereClause}
                ORDER BY created_at DESC 
                LIMIT ? OFFSET ?";
        
        return $this->db->fetchAll($sql, [$userId, $limit, $offset]);
    }
    
    /**
     * Get ticket activities
     */
    public function getTicketActivities($complaintId) {
        $sql = "SELECT al.*, 
                       u.name as user_name,
                       c.name as customer_name
                FROM activity_logs al
                LEFT JOIN users u ON al.user_id = u.id
                LEFT JOIN customers c ON al.customer_id = c.customer_id
                WHERE al.complaint_id = ?
                ORDER BY al.created_at ASC";
        
        return $this->db->fetchAll($sql, [$complaintId]);
    }
    
    /**
     * Get system activities with filtering
     */
    public function getSystemActivities($filters = [], $limit = 100, $offset = 0) {
        $conditions = [];
        $params = [];
        
        if (!empty($filters['action'])) {
            $conditions[] = 'action LIKE ?';
            $params[] = '%' . $filters['action'] . '%';
        }
        
        if (!empty($filters['user_role'])) {
            $conditions[] = 'user_role = ?';
            $params[] = $filters['user_role'];
        }
        
        if (!empty($filters['date_from'])) {
            $conditions[] = 'created_at >= ?';
            $params[] = $filters['date_from'] . ' 00:00:00';
        }
        
        if (!empty($filters['date_to'])) {
            $conditions[] = 'created_at <= ?';
            $params[] = $filters['date_to'] . ' 23:59:59';
        }
        
        if (!empty($filters['complaint_id'])) {
            $conditions[] = 'complaint_id = ?';
            $params[] = $filters['complaint_id'];
        }
        
        $whereClause = '';
        if (!empty($conditions)) {
            $whereClause = 'WHERE ' . implode(' AND ', $conditions);
        }
        
        $sql = "SELECT al.*, 
                       u.name as user_name, u.role as user_role_name,
                       c.name as customer_name
                FROM activity_logs al
                LEFT JOIN users u ON al.user_id = u.id
                LEFT JOIN customers c ON al.customer_id = c.customer_id
                {$whereClause}
                ORDER BY al.created_at DESC 
                LIMIT ? OFFSET ?";
        
        $params[] = $limit;
        $params[] = $offset;
        
        return $this->db->fetchAll($sql, $params);
    }
    
    /**
     * Get activity statistics
     */
    public function getActivityStats($dateRange = 30) {
        $sql = "SELECT 
                    COUNT(*) as total_activities,
                    COUNT(DISTINCT COALESCE(user_id, customer_id)) as unique_users,
                    COUNT(DISTINCT complaint_id) as tickets_involved,
                    SUM(CASE WHEN action LIKE 'login%' THEN 1 ELSE 0 END) as login_activities,
                    SUM(CASE WHEN action LIKE 'ticket_%' THEN 1 ELSE 0 END) as ticket_activities,
                    SUM(CASE WHEN action LIKE 'security_%' THEN 1 ELSE 0 END) as security_events
                FROM activity_logs 
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)";
        
        return $this->db->fetch($sql, [$dateRange]);
    }
    
    /**
     * Clean up old activity logs
     */
    public function cleanup($retentionDays = 365) {
        try {
            $sql = "DELETE FROM activity_logs 
                    WHERE created_at < DATE_SUB(NOW(), INTERVAL ? DAY)";
            
            $this->db->query($sql, [$retentionDays]);
            
            return $this->db->getConnection()->rowCount();
            
        } catch (Exception $e) {
            error_log("Activity log cleanup error: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Export activities to CSV
     */
    public function exportToCsv($filters = [], $filename = null) {
        if (!$filename) {
            $filename = 'activity_logs_' . date('Y-m-d_H-i-s') . '.csv';
        }
        
        $activities = $this->getSystemActivities($filters, 10000, 0);
        
        $csvPath = sys_get_temp_dir() . '/' . $filename;
        $handle = fopen($csvPath, 'w');
        
        if (!$handle) {
            return false;
        }
        
        // Write CSV header
        fputcsv($handle, [
            'ID', 'User ID', 'User Name', 'Customer ID', 'Customer Name',
            'User Role', 'Action', 'Description', 'Complaint ID',
            'IP Address', 'User Agent', 'Created At'
        ]);
        
        // Write data rows
        foreach ($activities as $activity) {
            fputcsv($handle, [
                $activity['id'],
                $activity['user_id'],
                $activity['user_name'],
                $activity['customer_id'],
                $activity['customer_name'],
                $activity['user_role'],
                $activity['action'],
                $activity['description'],
                $activity['complaint_id'],
                $activity['ip_address'],
                $activity['user_agent'],
                $activity['created_at']
            ]);
        }
        
        fclose($handle);
        
        return $csvPath;
    }
    
    /**
     * Get user role from database
     */
    private function getUserRole($userId) {
        try {
            $sql = "SELECT role FROM users WHERE id = ?";
            $result = $this->db->fetch($sql, [$userId]);
            return $result ? $result['role'] : 'unknown';
        } catch (Exception $e) {
            return 'unknown';
        }
    }
    
    /**
     * Log to security file for critical events
     */
    private function logToSecurityFile($action, $description, $severity) {
        $logFile = '../logs/security.log';
        $timestamp = date('Y-m-d H:i:s');
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
        
        $logEntry = "[{$timestamp}] [{$severity}] {$action}: {$description} | IP: {$ip} | UA: {$userAgent}" . PHP_EOL;
        
        file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
    }
}
