<?php
/**
 * Authentication Controller for SAMPARK
 * Handles user login, logout, and registration
 */

require_once 'BaseController.php';
require_once __DIR__ . '/../utils/Validator.php';
require_once __DIR__ . '/../utils/NotificationService.php';


class AuthController extends BaseController {
    
    public function showLogin() {
        // If already logged in, redirect to appropriate dashboard
        if ($this->session->isLoggedIn()) {
            $this->redirectToDashboard();
            return;
        }

        // Store redirect URL if provided
        $redirectUrl = $_GET['redirect'] ?? null;
        if ($redirectUrl) {
            $this->session->set('login_redirect', $redirectUrl);
        }

        $data = [
            'csrf_token' => $this->session->getCSRFToken(),
            'page_title' => 'Login - SAMPARK',
            'errors' => $this->session->getFlash('error'),
            'success' => $this->session->getFlash('success')
        ];

        $this->view('auth/login', $data);
    }
    
    public function login() {
        // Log login attempt
        Config::logInfo('Login attempt started', [
            'login_type' => $_POST['login_type'] ?? 'customer',
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
        ]);
        
        $this->validateCSRF();
        
        $loginType = $_POST['login_type'] ?? 'customer';
        
        if ($loginType === 'customer') {
            $this->handleCustomerLogin();
        } else {
            $this->handleUserLogin();
        }
    }
    
    private function handleCustomerLogin() {
        $validator = new Validator();
        $isValid = $validator->validate($_POST, [
            'email_or_phone' => 'required',
            'password' => 'required'
        ]);
        
        if (!$isValid) {
            Config::logInfo('Customer login validation failed', [
                'errors' => $validator->getAllErrorMessages(),
                'post_data' => $_POST,
                'email_or_phone' => $_POST['email_or_phone'] ?? 'not set',
                'password' => isset($_POST['password']) ? '[PASSWORD PROVIDED]' : 'not set'
            ]);
            $this->setFlash('error', 'Please fill all required fields: ' . implode(', ', $validator->getAllErrorMessages()));
            $this->redirect(Config::getAppUrl() . '/login');
            return;
        }
        
        $emailOrPhone = trim($_POST['email_or_phone']);
        $password = $_POST['password'];
        
        Config::logInfo('Customer login attempt', [
            'email_or_phone' => $emailOrPhone
        ]);
        
        // Check if input is email or phone
        $isEmail = filter_var($emailOrPhone, FILTER_VALIDATE_EMAIL);
        
        $sql = "SELECT * FROM customers WHERE " . ($isEmail ? "email" : "mobile") . " = ? AND status = 'approved'";
        $customer = $this->db->fetch($sql, [$emailOrPhone]);
        
        if (!$customer || !password_verify($password, $customer['password'])) {
            Config::logInfo('Customer login failed - invalid credentials', [
                'email_or_phone' => $emailOrPhone,
                'customer_found' => !empty($customer),
                'password_match' => $customer ? password_verify($password, $customer['password']) : false
            ]);
            $this->logFailedLogin($emailOrPhone, 'customer');
            $this->setFlash('error', 'Invalid login credentials');
            $this->redirect(Config::getAppUrl() . '/login');
            return;
        }
        
        // Check account status
        if ($customer['status'] !== 'approved') {
            $statusMessage = $this->getCustomerStatusMessage($customer['status']);
            $this->setFlash('error', $statusMessage);
            $this->redirect(Config::getAppUrl() . '/login');
            return;
        }
        
        // Login successful
        Config::logInfo('Customer login successful', [
            'customer_id' => $customer['customer_id'],
            'email' => $customer['email']
        ]);
        
        $this->session->login('customer', [
            'id' => $customer['customer_id'],
            'customer_id' => $customer['customer_id'],
            'role' => 'customer',
            'name' => $customer['name'],
            'email' => $customer['email'],
            'mobile' => $customer['mobile'],
            'company_name' => $customer['company_name']
        ]);

        // Ensure session activity is properly set after login
        $this->session->refreshActivity();

        // Update last login
        $this->updateLastLogin('customers', 'customer_id', $customer['customer_id']);
        
        // Log successful login
        $this->logActivity('customer_login', ['customer_id' => $customer['customer_id']]);

        $this->setFlash('success', 'Welcome back, ' . $customer['name']);

        // Check if there's a redirect URL stored in session
        $redirectUrl = $this->session->get('login_redirect');
        if ($redirectUrl) {
            $this->session->remove('login_redirect');
            $this->redirect(Config::getAppUrl() . $redirectUrl);
        } else {
            $this->redirect(Config::getAppUrl() . '/?login=1');
        }
    }
    
    private function handleUserLogin() {
        $validator = new Validator();
        $isValid = $validator->validate($_POST, [
            'login_id' => 'required',
            'password' => 'required'
        ]);
        
        if (!$isValid) {
            $this->setFlash('error', 'Please fill all required fields');
            $this->redirect(Config::getAppUrl() . '/login');
            return;
        }
        
        $loginId = trim($_POST['login_id']);
        $password = $_POST['password'];
        
        $sql = "SELECT u.*, d.department_name 
                FROM users u 
                LEFT JOIN departments d ON u.department = d.department_code 
                WHERE u.login_id = ? AND u.status = 'active'";
        $user = $this->db->fetch($sql, [$loginId]);
        
        if (!$user || !password_verify($password, $user['password'])) {
            $this->logFailedLogin($loginId, 'user');
            $this->setFlash('error', 'Invalid login credentials');
            $this->redirect(Config::getAppUrl() . '/login');
            return;
        }
        
        // Check account status
        if ($user['status'] !== 'active') {
            $this->setFlash('error', 'Your account is ' . $user['status'] . '. Please contact administrator.');
            $this->redirect(Config::getAppUrl() . '/login');
            return;
        }

        // Check if user needs to change password
        if (!empty($user['force_password_change'])) {
            // Store user ID in session for password change verification
            $this->session->set('force_password_change_user_id', $user['id']);
            $this->setFlash('info', 'You must change your password before continuing.');
            $this->redirect(Config::getAppUrl() . '/change-password');
            return;
        }

        // Login successful
        $this->session->login('user', [
            'id' => $user['id'],
            'login_id' => $user['login_id'],
            'role' => $user['role'],
            'name' => $user['name'],
            'email' => $user['email'],
            'mobile' => $user['mobile'],
            'department' => $user['department'], // This is the department code
            'department_name' => $user['department_name'], // This is the department name for display
            'division' => $user['division'],
            'zone' => $user['zone']
        ]);

        // Ensure session activity is properly set after login
        $this->session->refreshActivity();

        // Update last login
        $this->updateLastLogin('users', 'id', $user['id']);
        
        // Log successful login
        $this->logActivity('user_login', ['user_id' => $user['id'], 'role' => $user['role']]);

        $this->setFlash('success', 'Welcome back, ' . $user['name']);

        // Check if there's a redirect URL stored in session
        $redirectUrl = $this->session->get('login_redirect');
        if ($redirectUrl) {
            $this->session->remove('login_redirect');
            $this->redirect(Config::getAppUrl() . $redirectUrl);
        } else {
            $this->redirectToDashboard($user['role'], true);
        }
    }
    
    public function showSignup() {
        $data = [
            'csrf_token' => $this->session->getCSRFToken(),
            'page_title' => 'Customer Registration - SAMPARK',
            'errors' => $this->session->getFlash('error'),
            'success' => $this->session->getFlash('success'),
            'divisions' => $this->getDivisions(),
            'zones' => $this->getZones()
        ];
        
        $this->view('auth/signup', $data);
    }
    
    public function signup() {
        $this->validateCSRF();
        
        $validator = new Validator();
        $isValid = $validator->validate($_POST, [
            'name' => 'required|min:2|max:100',
            'email' => 'required|email|unique:customers,email',
            'mobile' => 'required|phone|unique:customers,mobile',
            'company_name' => 'required|min:2|max:150',
            'designation' => 'max:100',
            'gstin' => 'gstin',
            'division' => 'required|exists:shed,division',
            'password' => 'required|password',
            'password_confirmation' => 'required'
        ]);
        
        if (!$isValid) {
            if ($this->isAjaxRequest()) {
                $this->json([
                    'success' => false,
                    'errors' => $validator->getAllErrorMessages()
                ], 400);
                return;
            }
            $this->setFlash('error', implode('<br>', $validator->getAllErrorMessages()));
            $this->redirect(Config::getAppUrl() . '/signup');
            return;
        }

        // Check password confirmation
        if ($_POST['password'] !== $_POST['password_confirmation']) {
            if ($this->isAjaxRequest()) {
                $this->json([
                    'success' => false,
                    'message' => 'Password confirmation does not match'
                ], 400);
                return;
            }
            $this->setFlash('error', 'Password confirmation does not match');
            $this->redirect(Config::getAppUrl() . '/signup');
            return;
        }
        
        try {
            $this->db->beginTransaction();
            
            // Generate customer ID
            $customerId = $this->generateCustomerId();
            
            // Get zone from division
            $zoneInfo = $this->getZoneFromDivision($_POST['division']);
            
            // Insert customer
            $sql = "INSERT INTO customers (
                customer_id, password, name, email, mobile, company_name, 
                designation, gstin, division, zone, status, created_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW())";
            
            $params = [
                $customerId,
                password_hash($_POST['password'], PASSWORD_DEFAULT),
                trim($_POST['name']),
                trim($_POST['email']),
                trim($_POST['mobile']),
                trim($_POST['company_name']),
                trim($_POST['designation']),
                !empty($_POST['gstin']) ? trim($_POST['gstin']) : null,
                $_POST['division'],
                $zoneInfo['zone'],
            ];
            
            $this->db->query($sql, $params);
            
            $this->db->commit();
            
            // Send notification email to customer
            $this->sendRegistrationNotification($customerId, $_POST['email'], $_POST['name']);

            // Send approval notification to admin
            $this->sendApprovalRequestToAdmin($customerId, $_POST);

            if ($this->isAjaxRequest()) {
                $this->json([
                    'success' => true,
                    'message' => 'Registration successful! Your account is pending approval. You will receive an email confirmation once approved.',
                    'redirect' => Config::getAppUrl() . '/login'
                ]);
                return;
            }

            $this->setFlash('success', 'Registration successful! Your account is pending approval. You will receive an email confirmation once approved.');
            $this->redirect(Config::getAppUrl() . '/login');

        } catch (Exception $e) {
            $this->db->rollback();
            error_log("Registration error: " . $e->getMessage());

            if ($this->isAjaxRequest()) {
                $this->json([
                    'success' => false,
                    'message' => 'Registration failed. Please try again. Error: ' . $e->getMessage()
                ], 500);
                return;
            }

            $this->setFlash('error', 'Registration failed. Please try again.');
            $this->redirect(Config::getAppUrl() . '/signup');
        }
    }
    
    public function logout() {
        $userInfo = $this->getCurrentUser();
        
        // Log logout activity
        $this->logActivity('logout', ['previous_role' => $userInfo['role']]);
        
        $this->session->logout();
        $this->setFlash('success', 'You have been logged out successfully');
        $this->redirect(Config::getAppUrl() . '/');
    }
    
    private function redirectToDashboard($role = null, $isLogin = false) {
        if (!$role) {
            $role = $this->session->getUserRole();
        }
        
        $loginParam = $isLogin ? '?login=1' : '';
        
        switch ($role) {
            case 'customer':
                $this->redirect(Config::getAppUrl() . '/' . $loginParam);
                break;
            case 'controller':
            case 'controller_nodal':
                $this->redirect(Config::getAppUrl() . '/controller/tickets' . $loginParam);
                break;
            case 'admin':
            case 'superadmin':
                $this->redirect(Config::getAppUrl() . '/admin/dashboard' . $loginParam);
                break;
            default:
                $this->redirect(Config::getAppUrl() . '/' . $loginParam);
        }
    }
    
    private function getCustomerStatusMessage($status) {
        switch ($status) {
            case 'pending':
                return 'Your account is pending approval. Please wait for administrator approval.';
            case 'rejected':
                return 'Your account registration was rejected. Please contact support.';
            case 'suspended':
                return 'Your account has been suspended. Please contact support.';
            default:
                return 'Your account status is ' . $status;
        }
    }
    
    private function generateCustomerId() {
        $year = date('Y');
        $month = date('m');
        
        // Get last customer ID for current month
        $sql = "SELECT customer_id FROM customers 
                WHERE customer_id LIKE ? 
                ORDER BY customer_id DESC LIMIT 1";
        $prefix = "CUST{$year}{$month}";
        $lastCustomer = $this->db->fetch($sql, [$prefix . '%']);
        
        if ($lastCustomer) {
            $lastNumber = intval(substr($lastCustomer['customer_id'], -4));
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }
        
        return $prefix . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }
    
    private function getDivisions() {
        $sql = "SELECT DISTINCT division FROM shed WHERE is_active = 1 ORDER BY division";
        return $this->db->fetchAll($sql);
    }
    
    private function getZones() {
        $sql = "SELECT DISTINCT zone FROM shed WHERE is_active = 1 ORDER BY zone";
        return $this->db->fetchAll($sql);
    }
    
    private function getZoneFromDivision($division) {
        $sql = "SELECT zone FROM shed WHERE division = ? LIMIT 1";
        return $this->db->fetch($sql, [$division]);
    }
    
    private function updateLastLogin($table, $idColumn, $id) {
        $sql = "UPDATE {$table} SET updated_at = NOW() WHERE {$idColumn} = ?";
        $this->db->query($sql, [$id]);
    }
    
    private function logFailedLogin($identifier, $type) {
        // Log failed login attempt
        $sql = "INSERT INTO activity_logs (action, description, ip_address, user_agent, created_at) 
                VALUES (?, ?, ?, ?, NOW())";
        
        $this->db->query($sql, [
            'failed_login',
            "Failed login attempt for {$type}: {$identifier}",
            $_SERVER['REMOTE_ADDR'],
            $_SERVER['HTTP_USER_AGENT']
        ]);
    }
    
    private function sendRegistrationNotification($customerId, $email, $name) {
        try {
            // Get customer data for the email
            $customer = $this->db->fetch(
                "SELECT * FROM customers WHERE customer_id = ?",
                [$customerId]
            );

            if (!$customer) {
                throw new Exception("Customer not found");
            }

            $notificationService = new NotificationService();

            // Send registration confirmation email using template
            $notificationService->sendTemplateEmail(
                $email,
                'customer_registration',
                [
                    'app_name' => 'SAMPARK',
                    'customer_name' => $name,
                    'customer_id' => $customerId,
                    'email' => $email,
                    'company_name' => $customer['company_name'],
                    'division' => $customer['division'],
                    'app_url' => Config::getAppUrl()
                ]
            );

            Config::logInfo("Registration notification sent to customer", [
                'customer_id' => $customerId,
                'email' => $email
            ]);
        } catch (Exception $e) {
            Config::logError("Failed to send registration notification: " . $e->getMessage());
        }
    }

    private function sendApprovalRequestToAdmin($customerId, $data) {
        try {
            // Create notification for admin users
            $sql = "INSERT INTO notifications (
                user_id, customer_id, type, title, message,
                metadata, is_read, created_at
            ) VALUES (?, ?, ?, ?, ?, ?, 0, NOW())";

            $adminUsers = $this->db->fetchAll(
                "SELECT id FROM users WHERE role IN ('admin', 'superadmin') AND status = 'active'"
            );

            $notificationData = json_encode([
                'customer_id' => $customerId,
                'name' => $data['name'],
                'email' => $data['email'],
                'company_name' => $data['company_name'],
                'division' => $data['division']
            ]);

            foreach ($adminUsers as $admin) {
                $this->db->query($sql, [
                    $admin['id'],
                    $customerId,
                    'customer_approval_request',
                    'New Customer Registration - Approval Required',
                    "New customer " . $data['name'] . " (" . $data['company_name'] . ") has registered and requires approval.",
                    $notificationData
                ]);
            }

            Config::logInfo("Approval request notification sent to admins", [
                'customer_id' => $customerId,
                'admin_count' => count($adminUsers)
            ]);
        } catch (Exception $e) {
            Config::logError("Failed to send approval request to admin: " . $e->getMessage());
        }
    }

    public function showChangePassword() {
        // Check if there's a valid force password change session
        $userId = $this->session->get('force_password_change_user_id');
        if (!$userId) {
            $this->setFlash('error', 'Access denied. Please login first.');
            $this->redirect(Config::getAppUrl() . '/login');
            return;
        }

        $data = [
            'page_title' => 'Change Password - SAMPARK',
            'csrf_token' => $this->session->getCSRFToken()
        ];

        $this->view('auth/change-password', $data);
    }

    public function changePassword() {
        $this->validateCSRF();

        // Check if there's a valid force password change session
        $userId = $this->session->get('force_password_change_user_id');
        if (!$userId) {
            $this->setFlash('error', 'Access denied. Please login first.');
            $this->redirect(Config::getAppUrl() . '/login');
            return;
        }

        $validator = new Validator();
        $isValid = $validator->validate($_POST, [
            'current_password' => 'required',
            'new_password' => 'required|password',
            'confirm_password' => 'required'
        ]);

        if (!$isValid) {
            $this->setFlash('error', 'Please check the form for errors.');
            $this->setFlash('errors', $validator->getErrors());
            $this->redirect(Config::getAppUrl() . '/change-password');
            return;
        }

        // Check if new passwords match
        if ($_POST['new_password'] !== $_POST['confirm_password']) {
            $this->setFlash('error', 'New password and confirmation do not match.');
            $this->redirect(Config::getAppUrl() . '/change-password');
            return;
        }

        // Get user
        $user = $this->db->fetch("SELECT * FROM users WHERE id = ?", [$userId]);
        if (!$user) {
            $this->setFlash('error', 'User not found.');
            $this->redirect(Config::getAppUrl() . '/login');
            return;
        }

        // Verify current password
        if (!password_verify($_POST['current_password'], $user['password'])) {
            $this->setFlash('error', 'Current password is incorrect.');
            $this->redirect(Config::getAppUrl() . '/change-password');
            return;
        }

        // Check if new password is different from current password
        if (password_verify($_POST['new_password'], $user['password'])) {
            $this->setFlash('error', 'New password must be different from current password.');
            $this->redirect(Config::getAppUrl() . '/change-password');
            return;
        }

        try {
            // Update password and remove force password change flag
            $this->db->query(
                "UPDATE users SET password = ?, force_password_change = 0, updated_at = NOW() WHERE id = ?",
                [password_hash($_POST['new_password'], PASSWORD_DEFAULT), $userId]
            );

            // Remove the session variable
            $this->session->remove('force_password_change_user_id');

            // Log activity
            $this->logActivity('password_changed', ['user_id' => $userId]);

            $this->setFlash('success', 'Password changed successfully. Please login with your new password.');
            $this->redirect(Config::getAppUrl() . '/login');
        } catch (Exception $e) {
            Config::logError("Password change error: " . $e->getMessage());
            $this->setFlash('error', 'Failed to change password. Please try again.');
            $this->redirect(Config::getAppUrl() . '/change-password');
        }
    }
}
