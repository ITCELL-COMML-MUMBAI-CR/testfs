<?php
/**
 * User Model for SAMPARK
 * Handles user-related database operations
 */

require_once 'BaseModel.php';

class UserModel extends BaseModel {
    
    protected $table = 'users';
    protected $primaryKey = 'id';
    
    protected $fillable = [
        'login_id', 'password', 'role', 'department', 'division', 'zone',
        'name', 'email', 'mobile', 'status', 'created_by'
    ];
    
    /**
     * Find user by login ID
     */
    public function findByLoginId($loginId) {
        return $this->findBy(['login_id' => $loginId]);
    }
    
    /**
     * Find user by email
     */
    public function findByEmail($email) {
        return $this->findBy(['email' => $email]);
    }
    
    /**
     * Get users by role
     */
    public function getUsersByRole($role, $division = null) {
        $conditions = ['role' => $role, 'status' => 'active'];
        
        if ($division) {
            $conditions['division'] = $division;
        }
        
        return $this->findAll($conditions, 'name ASC');
    }
    
    /**
     * Get users in division
     */
    public function getUsersInDivision($division, $role = null) {
        $conditions = ['division' => $division, 'status' => 'active'];
        
        if ($role) {
            $conditions['role'] = $role;
        }
        
        return $this->findAll($conditions, 'role ASC, name ASC');
    }
    
    /**
     * Check if email exists (for unique validation)
     */
    public function emailExists($email, $excludeId = null) {
        $sql = "SELECT id FROM users WHERE email = ?";
        $params = [$email];
        
        if ($excludeId) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }
        
        $result = $this->fetch($sql, $params);
        return !empty($result);
    }
    
    /**
     * Check if login ID exists (for unique validation)
     */
    public function loginIdExists($loginId, $excludeId = null) {
        $sql = "SELECT id FROM users WHERE login_id = ?";
        $params = [$loginId];
        
        if ($excludeId) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }
        
        $result = $this->fetch($sql, $params);
        return !empty($result);
    }
    
    /**
     * Create user with password hashing
     */
    public function createUser($data) {
        if (isset($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }
        
        return $this->create($data);
    }
    
    /**
     * Update user password
     */
    public function updatePassword($userId, $newPassword) {
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        return $this->update($userId, ['password' => $hashedPassword]);
    }
    
    /**
     * Verify user password
     */
    public function verifyPassword($userId, $password) {
        $user = $this->find($userId);
        
        if ($user && password_verify($password, $user['password'])) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Get user statistics
     */
    public function getUserStats() {
        $sql = "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active,
                    SUM(CASE WHEN status = 'inactive' THEN 1 ELSE 0 END) as inactive,
                    SUM(CASE WHEN status = 'suspended' THEN 1 ELSE 0 END) as suspended,
                    SUM(CASE WHEN role = 'controller' THEN 1 ELSE 0 END) as controllers,
                    SUM(CASE WHEN role = 'controller_nodal' THEN 1 ELSE 0 END) as nodal_controllers,
                    SUM(CASE WHEN role = 'admin' THEN 1 ELSE 0 END) as admins
                FROM users";
        
        return $this->fetch($sql);
    }
    
    /**
     * Get users with workload (ticket count)
     */
    public function getUsersWithWorkload($division = null) {
        $sql = "SELECT u.id, u.name, u.role, u.division, u.status,
                       COUNT(c.complaint_id) as active_tickets,
                       AVG(CASE WHEN c.status = 'closed' THEN TIMESTAMPDIFF(HOUR, c.created_at, c.closed_at) END) as avg_resolution_hours
                FROM users u
                LEFT JOIN complaints c ON u.id = c.assigned_to_user_id AND c.status != 'closed'
                WHERE u.status = 'active' AND u.role IN ('controller', 'controller_nodal')";
        
        $params = [];
        
        if ($division) {
            $sql .= " AND u.division = ?";
            $params[] = $division;
        }
        
        $sql .= " GROUP BY u.id ORDER BY active_tickets ASC, u.name ASC";
        
        return $this->fetchAll($sql, $params);
    }
}
