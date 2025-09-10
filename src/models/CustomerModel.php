<?php
/**
 * Customer Model for SAMPARK
 * Handles customer-related database operations
 */

require_once 'BaseModel.php';

class CustomerModel extends BaseModel {
    
    protected $table = 'customers';
    protected $primaryKey = 'customer_id';
    
    protected $fillable = [
        'customer_id', 'password', 'name', 'email', 'mobile', 'company_name',
        'designation', 'role', 'status', 'division', 'zone', 'created_by'
    ];
    
    /**
     * Find customer by email
     */
    public function findByEmail($email) {
        return $this->findBy(['email' => $email]);
    }
    
    /**
     * Find customer by mobile
     */
    public function findByMobile($mobile) {
        return $this->findBy(['mobile' => $mobile]);
    }
    
    /**
     * Find customer by email or mobile (for login)
     */
    public function findByEmailOrMobile($identifier) {
        $sql = "SELECT * FROM customers WHERE (email = ? OR mobile = ?) AND status = 'approved' LIMIT 1";
        return $this->fetch($sql, [$identifier, $identifier]);
    }
    
    /**
     * Check if email exists (for unique validation)
     */
    public function emailExists($email, $excludeId = null) {
        $sql = "SELECT customer_id FROM customers WHERE email = ?";
        $params = [$email];
        
        if ($excludeId) {
            $sql .= " AND customer_id != ?";
            $params[] = $excludeId;
        }
        
        $result = $this->fetch($sql, $params);
        return !empty($result);
    }
    
    /**
     * Check if mobile exists (for unique validation)
     */
    public function mobileExists($mobile, $excludeId = null) {
        $sql = "SELECT customer_id FROM customers WHERE mobile = ?";
        $params = [$mobile];
        
        if ($excludeId) {
            $sql .= " AND customer_id != ?";
            $params[] = $excludeId;
        }
        
        $result = $this->fetch($sql, $params);
        return !empty($result);
    }
    
    /**
     * Create customer with password hashing
     */
    public function createCustomer($data) {
        if (isset($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }
        
        return $this->create($data);
    }
    
    /**
     * Update customer password
     */
    public function updatePassword($customerId, $newPassword) {
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        return $this->updateByCustomerId($customerId, ['password' => $hashedPassword]);
    }
    
    /**
     * Verify customer password
     */
    public function verifyPassword($customerId, $password) {
        $customer = $this->find($customerId);
        
        if ($customer && password_verify($password, $customer['password'])) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Update by customer ID (custom primary key)
     */
    public function updateByCustomerId($customerId, $data) {
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
        
        $params[] = $customerId;
        
        $sql = "UPDATE {$this->table} SET " . implode(', ', $setParts) . " WHERE customer_id = ?";
        
        return $this->query($sql, $params);
    }
    
    /**
     * Get pending approvals
     */
    public function getPendingApprovals() {
        return $this->findAll(['status' => 'pending'], 'created_at ASC');
    }
    
    /**
     * Get customers by status
     */
    public function getCustomersByStatus($status) {
        return $this->findAll(['status' => $status], 'created_at DESC');
    }
    
    /**
     * Get customers in division
     */
    public function getCustomersInDivision($division, $status = null) {
        $conditions = ['division' => $division];
        
        if ($status) {
            $conditions['status'] = $status;
        }
        
        return $this->findAll($conditions, 'name ASC');
    }
    
    /**
     * Get customer statistics
     */
    public function getCustomerStats() {
        $sql = "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                    SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved,
                    SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected,
                    SUM(CASE WHEN status = 'suspended' THEN 1 ELSE 0 END) as suspended
                FROM customers";
        
        return $this->fetch($sql);
    }
    
    /**
     * Get customer activity metrics
     */
    public function getCustomerActivityMetrics($customerId, $dateFrom = null, $dateTo = null) {
        $conditions = ['customer_id' => $customerId];
        $params = [$customerId];
        
        if ($dateFrom) {
            $conditions[] = 'created_at >= ?';
            $params[] = $dateFrom . ' 00:00:00';
        }
        
        if ($dateTo) {
            $conditions[] = 'created_at <= ?';
            $params[] = $dateTo . ' 23:59:59';
        }
        
        $whereClause = implode(' AND ', $conditions);
        
        $sql = "SELECT 
                    COUNT(*) as total_tickets,
                    SUM(CASE WHEN status = 'closed' THEN 1 ELSE 0 END) as closed_tickets,
                    SUM(CASE WHEN status = 'closed' AND rating = 'excellent' THEN 1 ELSE 0 END) as excellent_ratings,
                    SUM(CASE WHEN status = 'closed' AND rating = 'satisfactory' THEN 1 ELSE 0 END) as satisfactory_ratings,
                    SUM(CASE WHEN status = 'closed' AND rating = 'unsatisfactory' THEN 1 ELSE 0 END) as unsatisfactory_ratings,
                    AVG(CASE WHEN status = 'closed' THEN TIMESTAMPDIFF(HOUR, created_at, closed_at) END) as avg_resolution_hours
                FROM complaints 
                WHERE {$whereClause}";
        
        return $this->fetch($sql, $params);
    }
    
    /**
     * Generate next customer ID
     */
    public function generateCustomerId() {
        $year = date('Y');
        $month = date('m');
        $prefix = "CUST{$year}{$month}";
        
        $sql = "SELECT customer_id FROM customers 
                WHERE customer_id LIKE ? 
                ORDER BY customer_id DESC LIMIT 1";
        
        $lastCustomer = $this->fetch($sql, [$prefix . '%']);
        
        if ($lastCustomer) {
            $lastNumber = intval(substr($lastCustomer['customer_id'], -4));
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }
        
        return $prefix . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }
    
    /**
     * Approve customer
     */
    public function approveCustomer($customerId, $approvedBy = null) {
        return $this->updateByCustomerId($customerId, [
            'status' => 'approved',
            'updated_by' => $approvedBy
        ]);
    }
    
    /**
     * Reject customer
     */
    public function rejectCustomer($customerId, $rejectedBy = null) {
        return $this->updateByCustomerId($customerId, [
            'status' => 'rejected',
            'updated_by' => $rejectedBy
        ]);
    }
    
    /**
     * Suspend customer
     */
    public function suspendCustomer($customerId, $suspendedBy = null) {
        return $this->updateByCustomerId($customerId, [
            'status' => 'suspended',
            'updated_by' => $suspendedBy
        ]);
    }
}
