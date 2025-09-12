<?php
/**
 * Role-based Authorization Middleware for SAMPARK
 * Ensures user has required role to access specific routes
 */

class RoleMiddleware {
    
    private $requiredRoles;
    
    public function __construct($roles = null) {
        // Parse roles from middleware parameter (e.g., "role:admin,superadmin")
        if ($roles && strpos($roles, ':') !== false) {
            $parts = explode(':', $roles, 2);
            $this->requiredRoles = explode(',', $parts[1]);
        } else if ($roles) {
            $this->requiredRoles = is_array($roles) ? $roles : [$roles];
        } else {
            $this->requiredRoles = [];
        }
    }
    
    public function handle() {
        $session = new Session();
        
        // First check if user is logged in
        if (!$session->isLoggedIn()) {
            $this->unauthorizedResponse('Authentication required');
            return false;
        }
        
        // If no specific roles required, just being logged in is enough
        if (empty($this->requiredRoles)) {
            return true;
        }
        
        $userRole = $session->getUserRole();
        
        // Check if user has required role
        if (!in_array($userRole, $this->requiredRoles)) {
            $this->unauthorizedResponse('Insufficient permissions');
            return false;
        }
        
        // Additional role-specific checks
        return $this->performRoleSpecificChecks($userRole, $session);
    }
    
    private function performRoleSpecificChecks($userRole, $session) {
        switch ($userRole) {
            case 'customer':
                return $this->checkCustomerAccess($session);
                
            case 'controller':
                return $this->checkControllerAccess($session);
                
            case 'controller_nodal':
                return $this->checkNodalControllerAccess($session);
                
            case 'admin':
                return $this->checkAdminAccess($session);
                
            case 'superadmin':
                return true; // Superadmin has access to everything
                
            default:
                $this->unauthorizedResponse('Invalid user role');
                return false;
        }
    }
    
    private function checkCustomerAccess($session) {
        // Check if customer account is still approved
        $customerId = $session->get('customer_id');
        if (!$customerId) {
            $this->unauthorizedResponse('Invalid customer session');
            return false;
        }
        
        $db = Database::getInstance();
        $sql = "SELECT status FROM customers WHERE customer_id = ?";
        $customer = $db->fetch($sql, [$customerId]);
        
        if (!$customer || $customer['status'] !== 'approved') {
            $session->logout();
            $this->unauthorizedResponse('Customer account is not active');
            return false;
        }
        
        return true;
    }
    
    private function checkControllerAccess($session) {
        // Check if user account is still active
        $userId = $session->get('user_id');
        if (!$userId) {
            $this->unauthorizedResponse('Invalid user session');
            return false;
        }
        
        $db = Database::getInstance();
        $sql = "SELECT status, role FROM users WHERE id = ?";
        $user = $db->fetch($sql, [$userId]);
        
        if (!$user || $user['status'] !== 'active') {
            $session->logout();
            $this->unauthorizedResponse('User account is not active');
            return false;
        }
        
        // Verify role hasn't changed
        if ($user['role'] !== $session->getUserRole()) {
            $session->logout();
            $this->unauthorizedResponse('User role has changed');
            return false;
        }
        
        return true;
    }
    
    private function checkNodalControllerAccess($session) {
        // Same checks as controller plus additional nodal-specific checks
        if (!$this->checkControllerAccess($session)) {
            return false;
        }
        
        // Additional checks for nodal controllers can be added here
        // e.g., division-specific permissions, etc.
        
        return true;
    }
    
    private function checkAdminAccess($session) {
        // Same checks as controller
        return $this->checkControllerAccess($session);
    }
    
    private function unauthorizedResponse($message) {
        // Check if it's an AJAX request
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            header('Content-Type: application/json');
            http_response_code(403);
            echo json_encode(['error' => $message]);
            exit;
        }
        
        // For regular requests, redirect to appropriate page
        $session = new Session();
        $session->setFlash('error', $message);
        
        if (!$session->isLoggedIn()) {
            header('Location: ' . Config::getAppUrl() . '/login');
        } else {
            // Redirect to their dashboard or access denied page
            $userRole = $session->getUserRole();
            switch ($userRole) {
                case 'customer':
                    header('Location: ' . Config::getAppUrl() . '/customer/dashboard');
                    break;
                case 'controller':
                case 'controller_nodal':
                    header('Location: ' . Config::getAppUrl() . '/controller/dashboard');
                    break;
                case 'admin':
                case 'superadmin':
                    header('Location: ' . Config::getAppUrl() . '/admin/dashboard');
                    break;
                default:
                    header('Location: ' . Config::getAppUrl() . '/');
            }
        }
        exit;
    }
    
    /**
     * Check if user can access specific division/department data
     */
    public static function canAccessDivision($userRole, $userDivision, $targetDivision) {
        // Superadmin can access everything
        if ($userRole === 'superadmin') {
            return true;
        }
        
        // Admin can access their zone
        if ($userRole === 'admin') {
            // Additional logic to check zone access
            return true; // Simplified for now
        }
        
        // Controller nodal can access multiple divisions
        if ($userRole === 'controller_nodal') {
            // Nodal controllers can access their own division and other divisions
            // but should have proper validation for cross-division access
            return $userDivision === $targetDivision || self::canAccessCrossDivision($userDivision, $targetDivision);
        }
        
        // Regular controller can only access their own division
        if ($userRole === 'controller') {
            return $userDivision === $targetDivision;
        }
        
        return false;
    }
    
    /**
     * Check if nodal controller can access cross-division
     */
    private static function canAccessCrossDivision($userDivision, $targetDivision) {
        // For now, allow cross-division access for nodal controllers
        // In a real implementation, this should check against a configuration
        // or database table that defines which divisions a nodal controller can access
        return true;
    }
    
    /**
     * Check if user can perform specific action on ticket
     */
    public static function canPerformTicketAction($userRole, $action, $ticketData, $userData) {
        switch ($action) {
            case 'forward':
                return in_array($userRole, ['controller', 'controller_nodal']);
                
            case 'reply':
                return in_array($userRole, ['controller', 'controller_nodal']);
                
            case 'approve':
                return $userRole === 'controller_nodal';
                
            case 'reject':
                return $userRole === 'controller_nodal';
                
            case 'revert':
                return $userRole === 'controller_nodal';
                
            case 'view':
                // Complex logic based on ticket assignment and user permissions
                return self::canViewTicket($userRole, $ticketData, $userData);
                
            default:
                return false;
        }
    }
    
    private static function canViewTicket($userRole, $ticketData, $userData) {
        // Customers can only view their own tickets
        if ($userRole === 'customer') {
            return $ticketData['customer_id'] === $userData['customer_id'];
        }
        
        // Superadmin can view all tickets
        if ($userRole === 'superadmin') {
            return true;
        }
        
        // Admin can view tickets in their zone
        if ($userRole === 'admin') {
            return $ticketData['zone'] === $userData['zone'];
        }
        
        // Controllers can view tickets in their division or assigned to them
        if (in_array($userRole, ['controller', 'controller_nodal'])) {
            return $ticketData['division'] === $userData['division'] || 
                   $ticketData['assigned_to_user_id'] === $userData['id'];
        }
        
        return false;
    }
}
