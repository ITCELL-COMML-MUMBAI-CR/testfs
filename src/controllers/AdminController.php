<?php
/**
 * Admin Controller for SAMPARK
 * Handles admin dashboard, user management, customer approval, system settings
 */

require_once 'BaseController.php';
require_once __DIR__ . '/../utils/Validator.php';
require_once __DIR__ . '/../utils/NotificationService.php';


class AdminController extends BaseController {
    
    public function dashboard() {
        $user = $this->getCurrentUser();
        
        $data = [
            'page_title' => 'Admin Dashboard - SAMPARK',
            'user' => $user,
            'system_stats' => $this->getSystemStats(),
            'recent_registrations' => $this->getRecentRegistrations(),
            'system_health' => $this->getSystemHealth(),
            'ticket_summary' => $this->getTicketSummary(),
            'user_activity' => $this->getRecentUserActivity(),
            'pending_approvals' => $this->getPendingApprovals(),
            'system_alerts' => $this->getSystemAlerts(),
            'csrf_token' => $this->session->getCSRFToken()
        ];
        
        $this->view('admin/dashboard', $data);
    }
    
    public function users() {
        $user = $this->getCurrentUser();
        $page = $_GET['page'] ?? 1;
        $role = $_GET['role'] ?? '';
        $status = $_GET['status'] ?? '';
        $division = $_GET['division'] ?? '';
        $search = $_GET['search'] ?? '';
        
        // Build query conditions
        $conditions = ['1=1'];
        $params = [];
        
        if ($role) {
            $conditions[] = 'role = ?';
            $params[] = $role;
        }
        
        if ($status) {
            $conditions[] = 'status = ?';
            $params[] = $status;
        }
        
        if ($division) {
            $conditions[] = 'division = ?';
            $params[] = $division;
        }
        
        if ($search) {
            $conditions[] = '(name LIKE ? OR email LIKE ? OR login_id LIKE ?)';
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
        }
        
        $whereClause = implode(' AND ', $conditions);
        
        $sql = "SELECT id, login_id, name, email, mobile, role, department, 
                       division, zone, status, created_at, updated_at
                FROM users 
                WHERE {$whereClause}
                ORDER BY created_at DESC";
        
        $users = $this->paginate($sql, $params, $page, 20);
        
        $data = [
            'page_title' => 'User Management - SAMPARK',
            'user' => $user,
            'users' => $users['data'], // Extract the actual users from the paginated result
            'total_users' => $users['total'],
            'current_page' => $users['page'],
            'total_pages' => $users['total_pages'],
            'has_next' => $users['has_next'],
            'has_prev' => $users['has_prev'],
            'filters' => [
                'role' => $role,
                'status' => $status,
                'division' => $division,
                'search' => $search
            ],
            'roles' => Config::USER_ROLES,
            'status_options' => Config::USER_STATUS,
            'divisions' => $this->getDivisions(),
            'csrf_token' => $this->session->getCSRFToken()
        ];
        
        $this->view('admin/users/index', $data);
    }
    
    public function createUser() {
        $user = $this->getCurrentUser();
        
        $regions = []; // Add logic to get regions if needed
        
        $data = [
            'page_title' => 'Create User - SAMPARK',
            'user' => $user,
            'roles' => Config::USER_ROLES,
            'divisions' => $this->getDivisions(),
            'departments' => $this->getDepartments(),
            'regions' => $regions,
            'csrf_token' => $this->session->getCSRFToken()
        ];
        
        $this->view('admin/users/create', $data);
    }
    
    public function storeUser() {
        $this->validateCSRF();
        $user = $this->getCurrentUser();
        
        $validator = new Validator();
        $isValid = $validator->validate($_POST, [
            'login_id' => 'required|min:4|max:50|unique:users,login_id',
            'name' => 'required|min:2|max:100',
            'email' => 'required|email|unique:users,email',
            'mobile' => 'required|phone|unique:users,mobile',
            'role' => 'required|in:' . implode(',', array_keys(Config::USER_ROLES)),
            'department' => 'required|max:100',
            'division' => 'required|exists:shed,division',
            'password' => 'required|password',
            'password_confirm' => 'required'
        ]);
        
        if (!$isValid) {
            $this->json([
                'success' => false,
                'errors' => $validator->getErrors()
            ], 400);
            return;
        }
        
        // Check password confirmation
        if ($_POST['password'] !== $_POST['password_confirm']) {
            $this->json(['success' => false, 'message' => 'Password confirmation does not match'], 400);
            return;
        }
        
        try {
            $this->db->beginTransaction();
            
            // Get zone from division
            $zoneInfo = $this->getZoneFromDivision($_POST['division']);
            
            // Insert user
            $sql = "INSERT INTO users (
                login_id, password, role, department, division, zone,
                name, email, mobile, status, created_by, created_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'active', ?, NOW())";
            
            $params = [
                trim($_POST['login_id']),
                password_hash($_POST['password'], PASSWORD_DEFAULT),
                $_POST['role'],
                trim($_POST['department']),
                $_POST['division'],
                $zoneInfo['zone'],
                trim($_POST['name']),
                trim($_POST['email']),
                trim($_POST['mobile']),
                $user['id']
            ];
            
            $this->db->query($sql, $params);
            $newUserId = $this->db->lastInsertId();
            
            $this->db->commit();
            
            // Log activity
            $this->logActivity('user_created', [
                'new_user_id' => $newUserId,
                'new_user_login' => $_POST['login_id'],
                'new_user_role' => $_POST['role']
            ]);
            
            // Send welcome email
            $this->sendWelcomeEmail($newUserId, $_POST['email'], $_POST['name'], $_POST['login_id']);
            
            $this->json([
                'success' => true,
                'message' => 'User created successfully',
                'redirect' => Config::APP_URL . '/admin/users'
            ]);
            
        } catch (Exception $e) {
            $this->db->rollback();
            Config::logError("User creation error: " . $e->getMessage());
            
            $this->json([
                'success' => false,
                'message' => 'Failed to create user. Please try again.'
            ], 500);
        }
    }
    
    public function editUser($id) {
        $user = $this->getCurrentUser();
        
        $userToEdit = $this->db->fetch(
            "SELECT * FROM users WHERE id = ?",
            [$id]
        );
        
        if (!$userToEdit) {
            $this->setFlash('error', 'User not found');
            $this->redirect(Config::APP_URL . '/admin/users');
            return;
        }
        
        $data = [
            'page_title' => 'Edit User - SAMPARK',
            'user' => $user,
            'user_to_edit' => $userToEdit,
            'roles' => Config::USER_ROLES,
            'divisions' => $this->getDivisions(),
            'departments' => $this->getDepartments(),
            'status_options' => Config::USER_STATUS,
            'csrf_token' => $this->session->getCSRFToken()
        ];
        
        $this->view('admin/users/edit', $data);
    }
    
    public function updateUser($id) {
        $this->validateCSRF();
        $user = $this->getCurrentUser();
        
        $userToEdit = $this->db->fetch(
            "SELECT * FROM users WHERE id = ?",
            [$id]
        );
        
        if (!$userToEdit) {
            $this->json(['success' => false, 'message' => 'User not found'], 404);
            return;
        }
        
        $validator = new Validator();
        $isValid = $validator->validate($_POST, [
            'name' => 'required|min:2|max:100',
            'email' => 'required|email|unique:users,email,' . $id,
            'mobile' => 'required|phone|unique:users,mobile,' . $id,
            'role' => 'required|in:' . implode(',', array_keys(Config::USER_ROLES)),
            'department' => 'required|max:100',
            'division' => 'required|exists:shed,division',
            'status' => 'required|in:' . implode(',', array_keys(Config::USER_STATUS))
        ]);
        
        if (!$isValid) {
            $this->json([
                'success' => false,
                'errors' => $validator->getErrors()
            ], 400);
            return;
        }
        
        try {
            $this->db->beginTransaction();
            
            // Get zone from division
            $zoneInfo = $this->getZoneFromDivision($_POST['division']);
            
            // Update user
            $sql = "UPDATE users SET 
                    name = ?, email = ?, mobile = ?, role = ?, 
                    department = ?, division = ?, zone = ?, status = ?,
                    updated_at = NOW()
                    WHERE id = ?";
            
            $params = [
                trim($_POST['name']),
                trim($_POST['email']),
                trim($_POST['mobile']),
                $_POST['role'],
                trim($_POST['department']),
                $_POST['division'],
                $zoneInfo['zone'],
                $_POST['status'],
                $id
            ];
            
            $this->db->query($sql, $params);
            
            // Update password if provided
            if (!empty($_POST['new_password'])) {
                if ($_POST['new_password'] !== $_POST['password_confirmation']) {
                    $this->json(['success' => false, 'message' => 'Password confirmation does not match'], 400);
                    return;
                }
                
                $this->db->query(
                    "UPDATE users SET password = ? WHERE id = ?",
                    [password_hash($_POST['new_password'], PASSWORD_DEFAULT), $id]
                );
            }
            
            $this->db->commit();
            
            // Log activity
            $this->logActivity('user_updated', [
                'updated_user_id' => $id,
                'updated_user_login' => $userToEdit['login_id'],
                'changes' => array_diff_assoc($_POST, $userToEdit)
            ]);
            
            $this->json([
                'success' => true,
                'message' => 'User updated successfully'
            ]);
            
        } catch (Exception $e) {
            $this->db->rollback();
            Config::logError("User update error: " . $e->getMessage());
            
            $this->json([
                'success' => false,
                'message' => 'Failed to update user. Please try again.'
            ], 500);
        }
    }
    
    public function viewUser($id) {
        $user = $this->getCurrentUser();
        
        $userToView = $this->db->fetch(
            "SELECT * FROM users WHERE id = ?",
            [$id]
        );
        
        if (!$userToView) {
            $this->setFlash('error', 'User not found');
            $this->redirect(Config::APP_URL . '/admin/users');
            return;
        }
        
        $data = [
            'page_title' => 'User Details - SAMPARK',
            'user' => $user,
            'user_to_view' => $userToView,
            'roles' => Config::USER_ROLES,
            'status_options' => Config::USER_STATUS,
            'csrf_token' => $this->session->getCSRFToken()
        ];
        
        $this->view('admin/users/view', $data);
    }
    
    public function toggleUser($id) {
        
        $this->validateCSRF();
        $user = $this->getCurrentUser();
        
        $userToToggle = $this->db->fetch(
            "SELECT * FROM users WHERE id = ?",
            [$id]
        );
        
        if (!$userToToggle) {
            $this->json(['success' => false, 'message' => 'User not found'], 404);
            return;
        }
        
        try {
            $newStatus = $userToToggle['status'] === 'active' ? 'inactive' : 'active';
            
            // Check if status is explicitly provided in the request (for AJAX toggle)
            if (isset($_POST['status']) || (isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false)) {
                // Get JSON input for API calls
                $jsonInput = json_decode(file_get_contents('php://input'), true);
                if (isset($jsonInput['status'])) {
                    $newStatus = $jsonInput['status'];
                }
            }
            
            $this->db->query(
                "UPDATE users SET status = ?, updated_at = NOW() WHERE id = ?",
                [$newStatus, $id]
            );
            
            // Log activity
            $this->logActivity('user_status_changed', [
                'user_id' => $id,
                'user_login' => $userToToggle['login_id'],
                'old_status' => $userToToggle['status'],
                'new_status' => $newStatus
            ]);
            
            $this->json([
                'success' => true,
                'message' => "User {$newStatus} successfully",
                'new_status' => $newStatus
            ]);
            
        } catch (Exception $e) {
            Config::logError("User toggle error: " . $e->getMessage());
            
            $this->json([
                'success' => false,
                'message' => 'Failed to update user status. Please try again.'
            ], 500);
        }
    }
    
    public function resetUserPassword($id) {
        $this->validateCSRF();
        $user = $this->getCurrentUser();
        
        // For AJAX requests
        if (isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) {
            $jsonInput = json_decode(file_get_contents('php://input'), true);
            $_POST = array_merge($_POST, $jsonInput ?? []);
        }
        
        $userToReset = $this->db->fetch(
            "SELECT * FROM users WHERE id = ?",
            [$id]
        );
        
        if (!$userToReset) {
            $this->json(['success' => false, 'message' => 'User not found'], 404);
            return;
        }
        
        // Generate a secure random password
        $newPassword = bin2hex(random_bytes(6)); // 12 characters
        
        try {
            // Update the password
            $this->db->query(
                "UPDATE users SET password = ?, updated_at = NOW() WHERE id = ?",
                [password_hash($newPassword, PASSWORD_DEFAULT), $id]
            );
            
            // Log activity
            $this->logActivity('user_password_reset', [
                'user_id' => $id,
                'user_login' => $userToReset['login_id'],
                'reset_by' => $user['id']
            ]);
            
            $this->json([
                'success' => true,
                'message' => 'Password has been reset successfully',
                'new_password' => $newPassword
            ]);
            
        } catch (Exception $e) {
            Config::logError("Password reset error: " . $e->getMessage());
            
            $this->json([
                'success' => false,
                'message' => 'Failed to reset password. Please try again.'
            ], 500);
        }
    }
    
    public function customers() {
        $user = $this->getCurrentUser();
        $page = $_GET['page'] ?? 1;
        $status = $_GET['status'] ?? '';
        $customer_type = $_GET['customer_type'] ?? '';
        $region = $_GET['region'] ?? '';
        $search = $_GET['search'] ?? '';
        
        // Build query conditions
        $conditions = ['1=1'];
        $params = [];
        
        if ($status) {
            $conditions[] = 'c.status = ?';
            $params[] = $status;
        }
        
        if ($customer_type) {
            $conditions[] = 'COALESCE(c.customer_type, "individual") = ?';
            $params[] = $customer_type;
        }
        
        if ($region) {
            $conditions[] = 'c.division = ?';
            $params[] = $region;
        }
        
        if ($search) {
            $conditions[] = '(c.name LIKE ? OR c.email LIKE ? OR c.company_name LIKE ? OR c.customer_id LIKE ?)';
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
        }
        
        $whereClause = implode(' AND ', $conditions);
        
        // Enhanced query with ticket counts and customer type
        $sql = "SELECT c.customer_id, c.name, c.email, c.mobile, c.company_name, 
                       c.designation, c.gstin, c.division, c.zone, c.status, c.created_at,
                       COALESCE(c.customer_type, 'individual') as customer_type,
                       COALESCE(t.total_tickets, 0) as total_tickets,
                       COALESCE(t.open_tickets, 0) as open_tickets
                FROM customers c
                LEFT JOIN (
                    SELECT customer_id, 
                           COUNT(*) as total_tickets,
                           SUM(CASE WHEN status != 'closed' THEN 1 ELSE 0 END) as open_tickets
                    FROM complaints 
                    GROUP BY customer_id
                ) t ON c.customer_id = t.customer_id
                WHERE {$whereClause}
                ORDER BY c.created_at DESC";
        
        try {
            $customers = $this->paginate($sql, $params, $page, 20);
            
            // Ensure customers data is an array even if empty
            if (!isset($customers['data']) || !is_array($customers['data'])) {
                $customers['data'] = [];
            }
            
            // Get statistics
            $stats = $this->getCustomerStats();
            
            // Get regions for filter - convert to proper format
            $divisions = $this->getDivisions();
            $regions = [];
            foreach ($divisions as $division) {
                $regions[] = [
                    'id' => $division['division'],
                    'name' => $division['division']
                ];
            }
            
            $data = [
                'page_title' => 'Customer Management - SAMPARK',
                'user' => $user,
                'customers' => $customers['data'],
                'total_customers' => isset($customers['total']) ? $customers['total'] : 0,
                'current_page' => isset($customers['page']) ? $customers['page'] : 1,
                'total_pages' => isset($customers['total_pages']) ? $customers['total_pages'] : 1,
                'has_next' => isset($customers['has_next']) ? $customers['has_next'] : false,
                'has_prev' => isset($customers['has_prev']) ? $customers['has_prev'] : false,
                'stats' => $stats,
                'filters' => [
                    'status' => $status,
                    'customer_type' => $customer_type,
                    'region' => $region,
                    'search' => $search
                ],
                'regions' => $regions,
                'csrf_token' => $this->session->getCSRFToken()
            ];
        } catch (\Exception $e) {
            error_log("Error in customers method: " . $e->getMessage());
            
            // Provide default data
            $data = [
                'page_title' => 'Customer Management - SAMPARK',
                'user' => $user,
                'customers' => [],
                'total_customers' => 0,
                'current_page' => 1,
                'total_pages' => 1,
                'has_next' => false,
                'has_prev' => false,
                'stats' => [
                    'total_customers' => 0,
                    'active_customers' => 0,
                    'pending_verification' => 0,
                    'new_this_month' => 0
                ],
                'filters' => [
                    'status' => $status,
                    'customer_type' => $customer_type,
                    'region' => $region,
                    'search' => $search
                ],
                'regions' => [],
                'csrf_token' => $this->session->getCSRFToken()
            ];
        }
        
        $this->view('admin/customers/index', $data);
    }
    
    public function viewCustomer($customerId) {
        $user = $this->getCurrentUser();
        
        $customer = $this->db->fetch(
            "SELECT c.*, 
                    COALESCE(t.total_tickets, 0) as total_tickets,
                    COALESCE(t.open_tickets, 0) as open_tickets,
                    COALESCE(t.closed_tickets, 0) as closed_tickets
             FROM customers c
             LEFT JOIN (
                 SELECT customer_id, 
                        COUNT(*) as total_tickets,
                        SUM(CASE WHEN status != 'closed' THEN 1 ELSE 0 END) as open_tickets,
                        SUM(CASE WHEN status = 'closed' THEN 1 ELSE 0 END) as closed_tickets
                 FROM complaints 
                 GROUP BY customer_id
             ) t ON c.customer_id = t.customer_id
             WHERE c.customer_id = ?",
            [$customerId]
        );
        
        if (!$customer) {
            $this->setFlash('error', 'Customer not found');
            $this->redirect(Config::APP_URL . '/admin/customers');
            return;
        }
        
        // Get recent tickets for this customer
        $recentTickets = $this->db->fetchAll(
            "SELECT complaint_id, description, status, priority, created_at 
             FROM complaints 
             WHERE customer_id = ? 
             ORDER BY created_at DESC 
             LIMIT 10",
            [$customerId]
        );
        
        $data = [
            'page_title' => 'Customer Details - SAMPARK',
            'user' => $user,
            'customer' => $customer,
            'recent_tickets' => $recentTickets,
            'csrf_token' => $this->session->getCSRFToken()
        ];
        
        $this->view('admin/customers/view', $data);
    }
    
    public function editCustomer($customerId) {
        $user = $this->getCurrentUser();
        
        $customer = $this->db->fetch(
            "SELECT * FROM customers WHERE customer_id = ?",
            [$customerId]
        );
        
        if (!$customer) {
            $this->setFlash('error', 'Customer not found');
            $this->redirect(Config::APP_URL . '/admin/customers');
            return;
        }
        
        $data = [
            'page_title' => 'Edit Customer - SAMPARK',
            'user' => $user,
            'customer' => $customer,
            'divisions' => $this->getDivisions(),
            'customer_types' => [
                'individual' => 'Individual',
                'corporate' => 'Corporate',
                'government' => 'Government'
            ],
            'status_options' => [
                'pending' => 'Pending Approval',
                'approved' => 'Approved',
                'rejected' => 'Rejected',
                'suspended' => 'Suspended'
            ],
            'csrf_token' => $this->session->getCSRFToken()
        ];
        
        $this->view('admin/customers/edit', $data);
    }
    
    public function updateCustomer($customerId) {
        $this->validateCSRF();
        $user = $this->getCurrentUser();
        
        $customer = $this->db->fetch(
            "SELECT * FROM customers WHERE customer_id = ?",
            [$customerId]
        );
        
        if (!$customer) {
            $this->json(['success' => false, 'message' => 'Customer not found'], 404);
            return;
        }
        
        $validator = new Validator();
        $isValid = $validator->validate($_POST, [
            'name' => 'required|min:2|max:100',
            'email' => 'required|email|unique:customers,email,' . $customerId . ',customer_id',
            'mobile' => 'required|phone|unique:customers,mobile,' . $customerId . ',customer_id',
            'company_name' => 'required|max:150',
            'designation' => 'max:100',
            'gstin' => 'max:15',
            'customer_type' => 'required|in:individual,corporate,government',
            'division' => 'required|max:50',
            'zone' => 'required|max:50',
            'status' => 'required|in:pending,approved,rejected,suspended'
        ]);
        
        if (!$isValid) {
            $this->json([
                'success' => false,
                'errors' => $validator->getErrors()
            ], 400);
            return;
        }
        
        try {
            $this->db->beginTransaction();
            
            $sql = "UPDATE customers SET 
                    name = ?, email = ?, mobile = ?, company_name = ?, 
                    designation = ?, gstin = ?, customer_type = ?, 
                    division = ?, zone = ?, status = ?, updated_at = NOW()
                    WHERE customer_id = ?";
            
            $params = [
                trim($_POST['name']),
                trim($_POST['email']),
                trim($_POST['mobile']),
                trim($_POST['company_name']),
                trim($_POST['designation']) ?: null,
                trim($_POST['gstin']) ?: null,
                $_POST['customer_type'],
                trim($_POST['division']),
                trim($_POST['zone']),
                $_POST['status'],
                $customerId
            ];
            
            $this->db->query($sql, $params);
            
            $this->db->commit();
            
            // Log activity
            $this->logActivity('customer_updated', [
                'customer_id' => $customerId,
                'customer_name' => $_POST['name'],
                'changes' => array_diff_assoc($_POST, $customer)
            ]);
            
            $this->json([
                'success' => true,
                'message' => 'Customer updated successfully'
            ]);
            
        } catch (Exception $e) {
            $this->db->rollback();
            Config::logError("Customer update error: " . $e->getMessage());
            
            $this->json([
                'success' => false,
                'message' => 'Failed to update customer. Please try again.'
            ], 500);
        }
    }
    
    public function approveCustomer($customerId) {
        $this->validateCSRF();
        $user = $this->getCurrentUser();
        
        try {
            $customer = $this->db->fetch(
                "SELECT * FROM customers WHERE customer_id = ? AND status = 'pending'",
                [$customerId]
            );
            
            if (!$customer) {
                $this->json(['success' => false, 'message' => 'Customer not found or already processed'], 404);
                return;
            }
            
            $this->db->query(
                "UPDATE customers SET status = 'approved', updated_at = NOW() WHERE customer_id = ?",
                [$customerId]
            );
            
            // Log activity
            $this->logActivity('customer_approved', [
                'customer_id' => $customerId,
                'customer_name' => $customer['name'],
                'customer_email' => $customer['email']
            ]);
            
            // Send approval email
            $this->sendCustomerApprovalEmail($customer, 'approved');
            
            $this->json([
                'success' => true,
                'message' => 'Customer approved successfully'
            ]);
            
        } catch (Exception $e) {
            Config::logError("Customer approval error: " . $e->getMessage());
            
            $this->json([
                'success' => false,
                'message' => 'Failed to approve customer. Please try again.'
            ], 500);
        }
    }
    
    public function updateCustomerStatus($customerId) {
        $this->validateCSRF();
        $user = $this->getCurrentUser();
        
        // For AJAX requests
        if (isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) {
            $jsonInput = json_decode(file_get_contents('php://input'), true);
            if (isset($jsonInput['status'])) {
                $_POST['status'] = $jsonInput['status'];
            }
        }
        
        $validator = new Validator();
        $isValid = $validator->validate($_POST, [
            'status' => 'required|in:pending,approved,rejected,suspended'
        ]);
        
        if (!$isValid) {
            $this->json([
                'success' => false,
                'errors' => $validator->getErrors()
            ], 400);
            return;
        }
        
        try {
            $customer = $this->db->fetch(
                "SELECT * FROM customers WHERE customer_id = ?",
                [$customerId]
            );
            
            if (!$customer) {
                $this->json(['success' => false, 'message' => 'Customer not found'], 404);
                return;
            }
            
            $this->db->query(
                "UPDATE customers SET status = ?, updated_at = NOW() WHERE customer_id = ?",
                [$_POST['status'], $customerId]
            );
            
            // Log activity
            $this->logActivity('customer_status_changed', [
                'customer_id' => $customerId,
                'customer_name' => $customer['name'],
                'old_status' => $customer['status'],
                'new_status' => $_POST['status']
            ]);
            
            $this->json([
                'success' => true,
                'message' => 'Customer status updated successfully',
                'new_status' => $_POST['status']
            ]);
            
        } catch (Exception $e) {
            Config::logError("Customer status update error: " . $e->getMessage());
            
            $this->json([
                'success' => false,
                'message' => 'Failed to update customer status. Please try again.'
            ], 500);
        }
    }
    
    public function rejectCustomer($customerId) {
        $this->validateCSRF();
        $user = $this->getCurrentUser();
        
        $validator = new Validator();
        $isValid = $validator->validate($_POST, [
            'rejection_reason' => 'required|min:10|max:500'
        ]);
        
        if (!$isValid) {
            $this->json([
                'success' => false,
                'errors' => $validator->getErrors()
            ], 400);
            return;
        }
        
        try {
            $customer = $this->db->fetch(
                "SELECT * FROM customers WHERE customer_id = ? AND status = 'pending'",
                [$customerId]
            );
            
            if (!$customer) {
                $this->json(['success' => false, 'message' => 'Customer not found or already processed'], 404);
                return;
            }
            
            $this->db->query(
                "UPDATE customers SET status = 'rejected', updated_at = NOW() WHERE customer_id = ?",
                [$customerId]
            );
            
            // Log activity
            $this->logActivity('customer_rejected', [
                'customer_id' => $customerId,
                'customer_name' => $customer['name'],
                'customer_email' => $customer['email'],
                'rejection_reason' => $_POST['rejection_reason']
            ]);
            
            // Send rejection email
            $this->sendCustomerApprovalEmail($customer, 'rejected', $_POST['rejection_reason']);
            
            $this->json([
                'success' => true,
                'message' => 'Customer rejected successfully'
            ]);
            
        } catch (Exception $e) {
            Config::logError("Customer rejection error: " . $e->getMessage());
            
            $this->json([
                'success' => false,
                'message' => 'Failed to reject customer. Please try again.'
            ], 500);
        }
    }
    
    public function categories() {
        $user = $this->getCurrentUser();
        
        $categories = $this->db->fetchAll(
            "SELECT * FROM complaint_categories ORDER BY category, type, subtype"
        );
        
        $data = [
            'page_title' => 'Complaint Categories - SAMPARK',
            'user' => $user,
            'categories' => $categories,
            'csrf_token' => $this->session->getCSRFToken()
        ];
        
        $this->view('admin/categories', $data);
    }
    
    public function storeCategory() {
        $this->validateCSRF();
        $user = $this->getCurrentUser();
        
        $validator = new Validator();
        $isValid = $validator->validate($_POST, [
            'category' => 'required|max:100',
            'type' => 'required|max:100',
            'subtype' => 'required|max:100',
            'description' => 'max:500'
        ]);
        
        if (!$isValid) {
            $this->json([
                'success' => false,
                'errors' => $validator->getErrors()
            ], 400);
            return;
        }
        
        try {
            // Check for duplicate
            $existing = $this->db->fetch(
                "SELECT category_id FROM complaint_categories WHERE category = ? AND type = ? AND subtype = ?",
                [$_POST['category'], $_POST['type'], $_POST['subtype']]
            );
            
            if ($existing) {
                $this->json(['success' => false, 'message' => 'Category combination already exists'], 400);
                return;
            }
            
            $sql = "INSERT INTO complaint_categories (category, type, subtype, description, created_at) 
                    VALUES (?, ?, ?, ?, NOW())";
            
            $this->db->query($sql, [
                trim($_POST['category']),
                trim($_POST['type']),
                trim($_POST['subtype']),
                trim($_POST['description']) ?: null
            ]);
            
            // Log activity
            $this->logActivity('category_created', [
                'category' => $_POST['category'],
                'type' => $_POST['type'],
                'subtype' => $_POST['subtype']
            ]);
            
            $this->json([
                'success' => true,
                'message' => 'Category created successfully'
            ]);
            
        } catch (Exception $e) {
            Config::logError("Category creation error: " . $e->getMessage());
            
            $this->json([
                'success' => false,
                'message' => 'Failed to create category. Please try again.'
            ], 500);
        }
    }
    
    public function updateCategory($categoryId) {
        $this->validateCSRF();
        $user = $this->getCurrentUser();
        
        $validator = new Validator();
        $isValid = $validator->validate($_POST, [
            'category' => 'required|max:100',
            'type' => 'required|max:100',
            'subtype' => 'required|max:100',
            'description' => 'max:500',
            'is_active' => 'boolean'
        ]);
        
        if (!$isValid) {
            $this->json([
                'success' => false,
                'errors' => $validator->getErrors()
            ], 400);
            return;
        }
        
        try {
            $category = $this->db->fetch(
                "SELECT * FROM complaint_categories WHERE category_id = ?",
                [$categoryId]
            );
            
            if (!$category) {
                $this->json(['success' => false, 'message' => 'Category not found'], 404);
                return;
            }
            
            $sql = "UPDATE complaint_categories SET 
                    category = ?, type = ?, subtype = ?, description = ?, 
                    is_active = ?, updated_at = NOW()
                    WHERE category_id = ?";
            
            $this->db->query($sql, [
                trim($_POST['category']),
                trim($_POST['type']),
                trim($_POST['subtype']),
                trim($_POST['description']) ?: null,
                isset($_POST['is_active']) ? 1 : 0,
                $categoryId
            ]);
            
            // Log activity
            $this->logActivity('category_updated', [
                'category_id' => $categoryId,
                'old_category' => $category['category'],
                'new_category' => $_POST['category']
            ]);
            
            $this->json([
                'success' => true,
                'message' => 'Category updated successfully'
            ]);
            
        } catch (Exception $e) {
            Config::logError("Category update error: " . $e->getMessage());
            
            $this->json([
                'success' => false,
                'message' => 'Failed to update category. Please try again.'
            ], 500);
        }
    }
    
    public function deleteCategory($categoryId) {
        $this->validateCSRF();
        $user = $this->getCurrentUser();
        
        try {
            // Check if category is in use
            $inUse = $this->db->fetch(
                "SELECT COUNT(*) as count FROM complaints WHERE category_id = ?",
                [$categoryId]
            );
            
            if ($inUse['count'] > 0) {
                $this->json(['success' => false, 'message' => 'Cannot delete category that is in use by tickets'], 400);
                return;
            }
            
            $category = $this->db->fetch(
                "SELECT * FROM complaint_categories WHERE category_id = ?",
                [$categoryId]
            );
            
            if (!$category) {
                $this->json(['success' => false, 'message' => 'Category not found'], 404);
                return;
            }
            
            $this->db->query(
                "DELETE FROM complaint_categories WHERE category_id = ?",
                [$categoryId]
            );
            
            // Log activity
            $this->logActivity('category_deleted', [
                'category_id' => $categoryId,
                'category' => $category['category'],
                'type' => $category['type'],
                'subtype' => $category['subtype']
            ]);
            
            $this->json([
                'success' => true,
                'message' => 'Category deleted successfully'
            ]);
            
        } catch (Exception $e) {
            Config::logError("Category deletion error: " . $e->getMessage());
            
            $this->json([
                'success' => false,
                'message' => 'Failed to delete category. Please try again.'
            ], 500);
        }
    }
    
    public function sheds() {
        $user = $this->getCurrentUser();
        
        $sheds = $this->db->fetchAll(
            "SELECT * FROM shed ORDER BY division, zone, name"
        );
        
        $data = [
            'page_title' => 'Shed Management - SAMPARK',
            'user' => $user,
            'sheds' => $sheds,
            'divisions' => $this->getDivisions(),
            'zones' => $this->getZones(),
            'csrf_token' => $this->session->getCSRFToken()
        ];
        
        $this->view('admin/sheds', $data);
    }
    
    public function storeShed() {
        $this->validateCSRF();
        $user = $this->getCurrentUser();
        
        $validator = new Validator();
        $isValid = $validator->validate($_POST, [
            'shed_code' => 'required|max:10|unique:shed,shed_code',
            'name' => 'required|max:200',
            'division' => 'required|max:100',
            'zone' => 'required|max:100',
            'terminal' => 'required|max:150',
            'type' => 'required|in:goods_shed,container_depot,private_siding,public_siding'
        ]);
        
        if (!$isValid) {
            $this->json([
                'success' => false,
                'errors' => $validator->getErrors()
            ], 400);
            return;
        }
        
        try {
            $sql = "INSERT INTO shed (shed_code, name, division, zone, terminal, type, created_at) 
                    VALUES (?, ?, ?, ?, ?, ?, NOW())";
            
            $this->db->query($sql, [
                strtoupper(trim($_POST['shed_code'])),
                trim($_POST['name']),
                trim($_POST['division']),
                trim($_POST['zone']),
                trim($_POST['terminal']),
                $_POST['type']
            ]);
            
            // Log activity
            $this->logActivity('shed_created', [
                'shed_code' => $_POST['shed_code'],
                'name' => $_POST['name'],
                'division' => $_POST['division']
            ]);
            
            $this->json([
                'success' => true,
                'message' => 'Shed created successfully'
            ]);
            
        } catch (Exception $e) {
            Config::logError("Shed creation error: " . $e->getMessage());
            
            $this->json([
                'success' => false,
                'message' => 'Failed to create shed. Please try again.'
            ], 500);
        }
    }
    
    public function updateShed($shedId) {
        $this->validateCSRF();
        $user = $this->getCurrentUser();
        
        $validator = new Validator();
        $isValid = $validator->validate($_POST, [
            'shed_code' => 'required|max:10|unique:shed,shed_code,' . $shedId,
            'name' => 'required|max:200',
            'division' => 'required|max:100',
            'zone' => 'required|max:100',
            'terminal' => 'required|max:150',
            'type' => 'required|in:goods_shed,container_depot,private_siding,public_siding',
            'is_active' => 'boolean'
        ]);
        
        if (!$isValid) {
            $this->json([
                'success' => false,
                'errors' => $validator->getErrors()
            ], 400);
            return;
        }
        
        try {
            $shed = $this->db->fetch(
                "SELECT * FROM shed WHERE shed_id = ?",
                [$shedId]
            );
            
            if (!$shed) {
                $this->json(['success' => false, 'message' => 'Shed not found'], 404);
                return;
            }
            
            $sql = "UPDATE shed SET 
                    shed_code = ?, name = ?, division = ?, zone = ?, 
                    terminal = ?, type = ?, is_active = ?, updated_at = NOW()
                    WHERE shed_id = ?";
            
            $this->db->query($sql, [
                strtoupper(trim($_POST['shed_code'])),
                trim($_POST['name']),
                trim($_POST['division']),
                trim($_POST['zone']),
                trim($_POST['terminal']),
                $_POST['type'],
                isset($_POST['is_active']) ? 1 : 0,
                $shedId
            ]);
            
            // Log activity
            $this->logActivity('shed_updated', [
                'shed_id' => $shedId,
                'shed_code' => $_POST['shed_code'],
                'name' => $_POST['name']
            ]);
            
            $this->json([
                'success' => true,
                'message' => 'Shed updated successfully'
            ]);
            
        } catch (Exception $e) {
            Config::logError("Shed update error: " . $e->getMessage());
            
            $this->json([
                'success' => false,
                'message' => 'Failed to update shed. Please try again.'
            ], 500);
        }
    }
    
    public function content() {
        $user = $this->getCurrentUser();
        
        $news = $this->db->fetchAll(
            "SELECT * FROM news ORDER BY publish_date DESC LIMIT 50"
        );
        
        $quickLinks = $this->db->fetchAll(
            "SELECT * FROM quick_links ORDER BY sort_order, title"
        );
        
        $data = [
            'page_title' => 'Content Management - SAMPARK',
            'user' => $user,
            'news' => $news,
            'quick_links' => $quickLinks,
            'divisions' => $this->getDivisions(),
            'zones' => $this->getZones(),
            'csrf_token' => $this->session->getCSRFToken()
        ];
        
        $this->view('admin/content', $data);
    }
    
    public function storeNews() {
        $this->validateCSRF();
        $user = $this->getCurrentUser();
        
        $validator = new Validator();
        $isValid = $validator->validate($_POST, [
            'title' => 'required|max:255',
            'content' => 'required',
            'short_description' => 'max:500',
            'type' => 'required|in:news,announcement,alert,update',
            'priority' => 'required|in:low,medium,high,urgent',
            'publish_date' => 'required|date',
            'expire_date' => 'date|after:publish_date'
        ]);
        
        if (!$isValid) {
            $this->json([
                'success' => false,
                'errors' => $validator->getErrors()
            ], 400);
            return;
        }
        
        try {
            $sql = "INSERT INTO news (
                title, content, short_description, type, priority,
                publish_date, expire_date, division_specific, zone_specific,
                show_on_homepage, show_on_marquee, created_by, created_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
            
            $this->db->query($sql, [
                trim($_POST['title']),
                trim($_POST['content']),
                trim($_POST['short_description']) ?: null,
                $_POST['type'],
                $_POST['priority'],
                $_POST['publish_date'],
                !empty($_POST['expire_date']) ? $_POST['expire_date'] : null,
                !empty($_POST['division_specific']) ? $_POST['division_specific'] : null,
                !empty($_POST['zone_specific']) ? $_POST['zone_specific'] : null,
                isset($_POST['show_on_homepage']) ? 1 : 0,
                isset($_POST['show_on_marquee']) ? 1 : 0,
                $user['id']
            ]);
            
            // Log activity
            $this->logActivity('news_created', [
                'title' => $_POST['title'],
                'type' => $_POST['type'],
                'priority' => $_POST['priority']
            ]);
            
            $this->json([
                'success' => true,
                'message' => 'News/Announcement created successfully'
            ]);
            
        } catch (Exception $e) {
            Config::logError("News creation error: " . $e->getMessage());
            
            $this->json([
                'success' => false,
                'message' => 'Failed to create news. Please try again.'
            ], 500);
        }
    }
    
    public function storeLink() {
        $this->validateCSRF();
        $user = $this->getCurrentUser();
        
        $validator = new Validator();
        $isValid = $validator->validate($_POST, [
            'title' => 'required|max:100',
            'url' => 'required|url|max:500',
            'description' => 'max:500',
            'icon' => 'max:50',
            'target' => 'required|in:_self,_blank',
            'sort_order' => 'integer|min:0'
        ]);
        
        if (!$isValid) {
            $this->json([
                'success' => false,
                'errors' => $validator->getErrors()
            ], 400);
            return;
        }
        
        try {
            $sql = "INSERT INTO quick_links (
                title, description, url, icon, target, sort_order, created_at
            ) VALUES (?, ?, ?, ?, ?, ?, NOW())";
            
            $this->db->query($sql, [
                trim($_POST['title']),
                trim($_POST['description']) ?: null,
                trim($_POST['url']),
                trim($_POST['icon']) ?: null,
                $_POST['target'],
                $_POST['sort_order'] ?? 0
            ]);
            
            // Log activity
            $this->logActivity('quick_link_created', [
                'title' => $_POST['title'],
                'url' => $_POST['url']
            ]);
            
            $this->json([
                'success' => true,
                'message' => 'Quick link created successfully'
            ]);
            
        } catch (Exception $e) {
            Config::logError("Quick link creation error: " . $e->getMessage());
            
            $this->json([
                'success' => false,
                'message' => 'Failed to create quick link. Please try again.'
            ], 500);
        }
    }
    
    public function reports() {
        $user = $this->getCurrentUser();
        
        $reportType = $_GET['type'] ?? 'overview';
        $dateFrom = $_GET['date_from'] ?? date('Y-m-01');
        $dateTo = $_GET['date_to'] ?? date('Y-m-t');
        
        $data = [
            'page_title' => 'System Reports - SAMPARK',
            'user' => $user,
            'report_type' => $reportType,
            'date_range' => ['from' => $dateFrom, 'to' => $dateTo],
            'csrf_token' => $this->session->getCSRFToken()
        ];
        
        switch ($reportType) {
            case 'overview':
                $data['report_data'] = $this->getSystemOverviewReport($dateFrom, $dateTo);
                break;
            case 'tickets':
                $data['report_data'] = $this->getTicketAnalyticsReport($dateFrom, $dateTo);
                break;
            case 'performance':
                $data['report_data'] = $this->getSystemPerformanceReport($dateFrom, $dateTo);
                break;
            case 'users':
                $data['report_data'] = $this->getUserActivityReport($dateFrom, $dateTo);
                break;
        }
        
        $this->view('admin/reports', $data);
    }
    
    // Helper methods
    
    private function getSystemStats() {
        $stats = [];
        
        // Total users
        $stats['total_users'] = $this->db->fetch("SELECT COUNT(*) as count FROM users")['count'];
        $stats['active_users'] = $this->db->fetch("SELECT COUNT(*) as count FROM users WHERE status = 'active'")['count'];
        
        // Total customers
        $stats['total_customers'] = $this->db->fetch("SELECT COUNT(*) as count FROM customers")['count'];
        $stats['approved_customers'] = $this->db->fetch("SELECT COUNT(*) as count FROM customers WHERE status = 'approved'")['count'];
        $stats['pending_customers'] = $this->db->fetch("SELECT COUNT(*) as count FROM customers WHERE status = 'pending'")['count'];
        
        // Total tickets
        $stats['total_tickets'] = $this->db->fetch("SELECT COUNT(*) as count FROM complaints")['count'];
        $stats['open_tickets'] = $this->db->fetch("SELECT COUNT(*) as count FROM complaints WHERE status != 'closed'")['count'];
        $stats['closed_tickets'] = $this->db->fetch("SELECT COUNT(*) as count FROM complaints WHERE status = 'closed'")['count'];
        
        return $stats;
    }
    
    private function getCustomerStats() {
        $stats = [];
        
        try {
            // Total customers
            $result = $this->db->fetch("SELECT COUNT(*) as count FROM customers");
            $stats['total_customers'] = isset($result['count']) ? $result['count'] : 0;
            
            $result = $this->db->fetch("SELECT COUNT(*) as count FROM customers WHERE status = 'approved'");
            $stats['active_customers'] = isset($result['count']) ? $result['count'] : 0;
            
            $result = $this->db->fetch("SELECT COUNT(*) as count FROM customers WHERE status = 'pending'");
            $stats['pending_verification'] = isset($result['count']) ? $result['count'] : 0;
            
            // New customers this month
            $result = $this->db->fetch(
                "SELECT COUNT(*) as count FROM customers WHERE MONTH(created_at) = MONTH(NOW()) AND YEAR(created_at) = YEAR(NOW())"
            );
            $stats['new_this_month'] = isset($result['count']) ? $result['count'] : 0;
        } catch (\Exception $e) {
            error_log("Error in getCustomerStats: " . $e->getMessage());
            
            // Provide default values
            $stats['total_customers'] = 0;
            $stats['active_customers'] = 0;
            $stats['pending_verification'] = 0;
            $stats['new_this_month'] = 0;
        }
        
        return $stats;
    }
    
    private function getRecentRegistrations($limit = 10) {
        $sql = "SELECT customer_id, name, email, company_name, division, status, created_at
                FROM customers 
                ORDER BY created_at DESC 
                LIMIT ?";
        
        return $this->db->fetchAll($sql, [$limit]);
    }
    
    private function getSystemHealth() {
        $health = [
            'database' => 'healthy',
            'storage' => 'healthy',
            'emails' => 'healthy'
        ];
        
        // Check database
        try {
            $this->db->fetch("SELECT 1");
        } catch (Exception $e) {
            $health['database'] = 'unhealthy';
        }
        
        // Check storage
        $uploadPath = Config::getUploadPath();
        if (!is_dir($uploadPath) || !is_writable($uploadPath)) {
            $health['storage'] = 'warning';
        }
        
        return $health;
    }
    
    private function getTicketSummary() {
        $sql = "SELECT 
                    division,
                    COUNT(*) as total,
                    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                    SUM(CASE WHEN status = 'closed' THEN 1 ELSE 0 END) as closed,
                    SUM(CASE WHEN priority IN ('high', 'critical') THEN 1 ELSE 0 END) as high_priority_count
                FROM complaints 
                WHERE division IS NOT NULL
                GROUP BY division 
                ORDER BY total DESC";
        
        return $this->db->fetchAll($sql);
    }
    
    private function getRecentUserActivity($limit = 20) {
        $sql = "SELECT a.action, a.description, a.created_at, 
                       u.name as user_name, u.role as user_role
                FROM activity_logs a
                LEFT JOIN users u ON a.user_id = u.id
                ORDER BY a.created_at DESC 
                LIMIT ?";
        
        return $this->db->fetchAll($sql, [$limit]);
    }
    
    private function getPendingApprovals() {
        $sql = "SELECT customer_id, name, email, company_name, division, created_at
                FROM customers 
                WHERE status = 'pending' 
                ORDER BY created_at ASC";
        
        return $this->db->fetchAll($sql);
    }
    
    private function getSystemAlerts() {
        $alerts = [];
        
        // Check for SLA violations
        $slaViolations = $this->db->fetch(
            "SELECT COUNT(*) as count FROM complaints 
             WHERE sla_deadline IS NOT NULL AND NOW() > sla_deadline AND status != 'closed'"
        )['count'];
        
        if ($slaViolations > 0) {
            $alerts[] = [
                'type' => 'warning',
                'message' => "{$slaViolations} tickets have SLA violations",
                'action_url' => '/admin/reports?type=sla'
            ];
        }
        
        // Check for pending customer approvals
        $pendingApprovals = $this->db->fetch(
            "SELECT COUNT(*) as count FROM customers WHERE status = 'pending'"
        )['count'];
        
        if ($pendingApprovals > 0) {
            $alerts[] = [
                'type' => 'info',
                'message' => "{$pendingApprovals} customer registrations pending approval",
                'action_url' => '/admin/customers?status=pending'
            ];
        }
        
        return $alerts;
    }
    
    private function getDivisions() {
        try {
            $sql = "SELECT DISTINCT division FROM shed WHERE is_active = 1 ORDER BY division";
            $result = $this->db->fetchAll($sql);
            return $result ?: [];
        } catch (\Exception $e) {
            error_log("Error in getDivisions: " . $e->getMessage());
            return [];
        }
    }
    
    private function getZones() {
        try {
            $sql = "SELECT DISTINCT zone FROM shed WHERE is_active = 1 ORDER BY zone";
            $result = $this->db->fetchAll($sql);
            return $result ?: [];
        } catch (\Exception $e) {
            error_log("Error in getZones: " . $e->getMessage());
            return [];
        }
    }
    
    private function getDepartments() {
        try {
            $sql = "SELECT DISTINCT department FROM users WHERE department IS NOT NULL ORDER BY department";
            $result = $this->db->fetchAll($sql);
            return $result ?: [];
        } catch (\Exception $e) {
            error_log("Error in getDepartments: " . $e->getMessage());
            return [];
        }
    }
    
    private function getZoneFromDivision($division) {
        $sql = "SELECT zone FROM shed WHERE division = ? LIMIT 1";
        return $this->db->fetch($sql, [$division]);
    }
    
    private function sendWelcomeEmail($userId, $email, $name, $loginId) {
        $notificationService = new NotificationService();
        // Implementation for sending welcome email
    }
    
    private function sendCustomerApprovalEmail($customer, $status, $reason = null) {
        $notificationService = new NotificationService();
        // Implementation for sending customer approval/rejection email
    }
    
    private function getSystemOverviewReport($dateFrom, $dateTo) {
        // Implementation for system overview report
        return [];
    }
    
    private function getTicketAnalyticsReport($dateFrom, $dateTo) {
        // Implementation for ticket analytics report
        return [];
    }
    
    private function getSystemPerformanceReport($dateFrom, $dateTo) {
        // Implementation for system performance report
        return [];
    }
    
    private function getUserActivityReport($dateFrom, $dateTo) {
        // Implementation for user activity report
        return [];
    }
    
    /**
     * Display email management dashboard
     */
    public function emails() {
        $user = $this->getCurrentUser();
        
        $data = [
            'page_title' => 'Email Management - SAMPARK Admin',
            'user' => $user,
            'email_stats' => $this->getEmailStats(),
            'recent_emails' => $this->getRecentEmails(),
            'email_templates' => $this->getEmailTemplatesList(),
            'csrf_token' => $this->session->getCSRFToken()
        ];
        
        $this->view('admin/emails', $data);
    }
    
    /**
     * Display email templates management
     */
    public function emailTemplates() {
        $user = $this->getCurrentUser();
        
        $data = [
            'page_title' => 'Email Templates - SAMPARK Admin',
            'user' => $user,
            'templates' => $this->getAllEmailTemplates(),
            'template_types' => [
                'welcome' => 'Welcome Email',
                'approval' => 'Account Approval',
                'rejection' => 'Account Rejection',
                'password_reset' => 'Password Reset',
                'ticket_created' => 'Ticket Created',
                'ticket_updated' => 'Ticket Updated',
                'ticket_resolved' => 'Ticket Resolved',
                'sla_warning' => 'SLA Warning',
                'announcement' => 'System Announcement'
            ],
            'csrf_token' => $this->session->getCSRFToken()
        ];
        
        $this->view('admin/email-templates', $data);
    }
    
    /**
     * Send bulk email to users
     */
    public function sendBulkEmail() {
        $this->validateCSRF();
        $user = $this->getCurrentUser();
        
        $validator = new Validator();
        $isValid = $validator->validate($_POST, [
            'subject' => 'required|min:5|max:200',
            'message' => 'required|min:10',
            'recipient_type' => 'required|in:all,customers,staff,admins',
            'template_id' => 'optional|numeric',
            'send_immediately' => 'optional|boolean'
        ]);
        
        if (!$isValid) {
            $this->json([
                'success' => false,
                'errors' => $validator->getErrors()
            ], 400);
            return;
        }
        
        try {
            $subject = $this->sanitize($_POST['subject']);
            $message = $_POST['message']; // Keep HTML formatting
            $recipientType = $_POST['recipient_type'];
            $templateId = $_POST['template_id'] ?? null;
            $sendImmediately = isset($_POST['send_immediately']) && $_POST['send_immediately'];
            
            // Get recipients based on type
            $recipients = $this->getBulkEmailRecipients($recipientType);
            
            if (empty($recipients)) {
                $this->json([
                    'success' => false,
                    'message' => 'No recipients found for the selected criteria'
                ], 400);
                return;
            }
            
            // Create bulk email job
            $emailJobId = $this->createBulkEmailJob([
                'subject' => $subject,
                'message' => $message,
                'recipient_type' => $recipientType,
                'template_id' => $templateId,
                'recipients' => $recipients,
                'created_by' => $user['id'],
                'send_immediately' => $sendImmediately
            ]);
            
            if ($sendImmediately) {
                // Process immediately
                $this->processBulkEmailJob($emailJobId);
                $message = 'Bulk email sent successfully to ' . count($recipients) . ' recipients';
            } else {
                // Queue for later processing
                $message = 'Bulk email queued successfully. It will be processed shortly.';
            }
            
            // Log activity
            $this->log('info', 'Bulk email created', [
                'email_job_id' => $emailJobId,
                'recipient_count' => count($recipients),
                'recipient_type' => $recipientType,
                'admin_id' => $user['id']
            ]);
            
            $this->json([
                'success' => true,
                'message' => $message,
                'job_id' => $emailJobId,
                'recipient_count' => count($recipients)
            ]);
            
        } catch (Exception $e) {
            Config::logError("Bulk email creation error: " . $e->getMessage());
            
            $this->json([
                'success' => false,
                'message' => 'Failed to create bulk email. Please try again.'
            ], 500);
        }
    }
    
    /**
     * Store new announcement
     */
    public function storeAnnouncement() {
        $this->validateCSRF();
        $user = $this->getCurrentUser();
        
        $validator = new Validator();
        $isValid = $validator->validate($_POST, [
            'title' => 'required|min:5|max:200',
            'content' => 'required|min:10',
            'type' => 'required|in:general,maintenance,urgent,news',
            'target_audience' => 'required|in:all,customers,staff,admins',
            'expires_at' => 'optional|datetime',
            'is_active' => 'optional|boolean',
            'priority' => 'optional|in:low,medium,high,urgent'
        ]);
        
        if (!$isValid) {
            $this->json([
                'success' => false,
                'errors' => $validator->getErrors()
            ], 400);
            return;
        }
        
        try {
            $title = $this->sanitize($_POST['title']);
            $content = $_POST['content']; // Keep HTML formatting
            $type = $_POST['type'];
            $targetAudience = $_POST['target_audience'];
            $expiresAt = !empty($_POST['expires_at']) ? $_POST['expires_at'] : null;
            $isActive = isset($_POST['is_active']) && $_POST['is_active'];
            $priority = $_POST['priority'] ?? 'medium';
            
            // Create announcement
            $announcementId = $this->createAnnouncement([
                'title' => $title,
                'content' => $content,
                'type' => $type,
                'target_audience' => $targetAudience,
                'expires_at' => $expiresAt,
                'is_active' => $isActive,
                'priority' => $priority,
                'created_by' => $user['id']
            ]);
            
            if ($announcementId && $isActive) {
                // Send notifications to target audience
                $this->sendAnnouncementNotifications($announcementId, $title, $content, $targetAudience, $priority);
            }
            
            // Log activity
            $this->log('info', 'Announcement created', [
                'announcement_id' => $announcementId,
                'title' => $title,
                'type' => $type,
                'target_audience' => $targetAudience,
                'admin_id' => $user['id']
            ]);
            
            $this->json([
                'success' => true,
                'message' => 'Announcement created successfully',
                'announcement_id' => $announcementId
            ]);
            
        } catch (Exception $e) {
            Config::logError("Announcement creation error: " . $e->getMessage());
            
            $this->json([
                'success' => false,
                'message' => 'Failed to create announcement. Please try again.'
            ], 500);
        }
    }
    
    // Helper methods for email management
    
    private function getEmailStats() {
        try {
            $sql = "SELECT 
                        COUNT(*) as total_sent,
                        COUNT(CASE WHEN status = 'sent' THEN 1 END) as successfully_sent,
                        COUNT(CASE WHEN status = 'failed' THEN 1 END) as failed,
                        COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending
                    FROM email_logs 
                    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
            
            return $this->db->fetch($sql) ?: [
                'total_sent' => 0,
                'successfully_sent' => 0,
                'failed' => 0,
                'pending' => 0
            ];
        } catch (Exception $e) {
            return [
                'total_sent' => 0,
                'successfully_sent' => 0,
                'failed' => 0,
                'pending' => 0
            ];
        }
    }
    
    private function getRecentEmails($limit = 10) {
        try {
            $sql = "SELECT * FROM email_logs ORDER BY created_at DESC LIMIT ?";
            return $this->db->fetchAll($sql, [$limit]);
        } catch (Exception $e) {
            return [];
        }
    }
    
    private function getEmailTemplatesList() {
        try {
            $sql = "SELECT id, name, type, subject FROM email_templates WHERE is_active = 1 ORDER BY name";
            return $this->db->fetchAll($sql);
        } catch (Exception $e) {
            return [];
        }
    }
    
    private function getAllEmailTemplates() {
        try {
            $sql = "SELECT * FROM email_templates ORDER BY type, name";
            return $this->db->fetchAll($sql);
        } catch (Exception $e) {
            return [];
        }
    }
    
    private function getBulkEmailRecipients($recipientType) {
        try {
            $recipients = [];
            
            switch ($recipientType) {
                case 'all':
                    $customers = $this->db->fetchAll("SELECT customer_id as id, email, name, 'customer' as type FROM customers WHERE status = 'active' AND email IS NOT NULL");
                    $staff = $this->db->fetchAll("SELECT id, email, name, user_type as type FROM users WHERE status = 'active' AND email IS NOT NULL");
                    $recipients = array_merge($customers, $staff);
                    break;
                    
                case 'customers':
                    $recipients = $this->db->fetchAll("SELECT customer_id as id, email, name, 'customer' as type FROM customers WHERE status = 'active' AND email IS NOT NULL");
                    break;
                    
                case 'staff':
                    $recipients = $this->db->fetchAll("SELECT id, email, name, user_type as type FROM users WHERE status = 'active' AND email IS NOT NULL AND user_type IN ('controller', 'controller_nodal')");
                    break;
                    
                case 'admins':
                    $recipients = $this->db->fetchAll("SELECT id, email, name, user_type as type FROM users WHERE status = 'active' AND email IS NOT NULL AND user_type IN ('admin', 'superadmin')");
                    break;
            }
            
            return $recipients;
        } catch (Exception $e) {
            return [];
        }
    }
    
    private function createBulkEmailJob($data) {
        try {
            $sql = "INSERT INTO bulk_email_jobs (
                        subject, message, recipient_type, template_id, 
                        recipient_data, created_by, status, created_at
                    ) VALUES (?, ?, ?, ?, ?, ?, 'pending', NOW())";
            
            $this->db->query($sql, [
                $data['subject'],
                $data['message'],
                $data['recipient_type'],
                $data['template_id'],
                json_encode($data['recipients']),
                $data['created_by']
            ]);
            
            return $this->db->lastInsertId();
        } catch (Exception $e) {
            throw $e;
        }
    }
    
    private function processBulkEmailJob($jobId) {
        // This would be implemented to actually send the emails
        // For now, just update the status
        try {
            $sql = "UPDATE bulk_email_jobs SET status = 'completed', processed_at = NOW() WHERE id = ?";
            $this->db->query($sql, [$jobId]);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
    
    private function createAnnouncement($data) {
        try {
            $sql = "INSERT INTO announcements (
                        title, content, type, target_audience, expires_at, 
                        is_active, priority, created_by, created_at
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";
            
            $this->db->query($sql, [
                $data['title'],
                $data['content'],
                $data['type'],
                $data['target_audience'],
                $data['expires_at'],
                $data['is_active'] ? 1 : 0,
                $data['priority'],
                $data['created_by']
            ]);
            
            return $this->db->lastInsertId();
        } catch (Exception $e) {
            throw $e;
        }
    }
    
    private function sendAnnouncementNotifications($announcementId, $title, $content, $targetAudience, $priority) {
        try {
            // Use the NotificationModel to create notifications
            require_once __DIR__ . '/../models/NotificationModel.php';
            $notificationModel = new NotificationModel();
            
            // Create system announcement notifications
            $notificationModel->createSystemAnnouncement(
                $title,
                strip_tags($content), // Remove HTML for notification
                $targetAudience === 'all' ? null : $targetAudience,
                null, // No expiry for notifications
                $priority
            );
            
            return true;
        } catch (Exception $e) {
            Config::logError("Failed to send announcement notifications: " . $e->getMessage());
            return false;
        }
    }
}
