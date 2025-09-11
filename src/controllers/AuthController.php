<?php
/**
 * Authentication Controller for SAMPARK
 * Handles user login, logout, and registration
 */

require_once 'BaseController.php';
require_once __DIR__ . '/../utils/Validator.php';

class AuthController extends BaseController {
    
    public function showLogin() {
        // Check for remember me token first
        if ($this->session->checkRememberMe()) {
            $this->redirectToDashboard();
            return;
        }
        
        // If already logged in, redirect to appropriate dashboard
        if ($this->session->isLoggedIn()) {
            $this->redirectToDashboard();
            return;
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
        $rememberMe = isset($_POST['remember_me']);
        
        Config::logInfo('Customer login attempt', [
            'email_or_phone' => $emailOrPhone,
            'remember_me' => $rememberMe
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
            'email' => $customer['email'],
            'remember_me' => $rememberMe
        ]);
        
        $this->session->login('customer', [
            'id' => $customer['customer_id'],
            'customer_id' => $customer['customer_id'],
            'role' => 'customer',
            'name' => $customer['name'],
            'email' => $customer['email'],
            'mobile' => $customer['mobile'],
            'company_name' => $customer['company_name']
        ], $rememberMe);
        
        // Update last login
        $this->updateLastLogin('customers', 'customer_id', $customer['customer_id']);
        
        // Log successful login
        $this->logActivity('customer_login', ['customer_id' => $customer['customer_id']]);
        
        $this->setFlash('success', 'Welcome back, ' . $customer['name']);
        $this->redirect(Config::getAppUrl() . '/');
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
        $rememberMe = isset($_POST['remember_me']);
        
        $sql = "SELECT * FROM users WHERE login_id = ? AND status = 'active'";
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
        
        // Login successful
        $this->session->login('user', [
            'id' => $user['id'],
            'login_id' => $user['login_id'],
            'role' => $user['role'],
            'name' => $user['name'],
            'email' => $user['email'],
            'mobile' => $user['mobile'],
            'department' => $user['department'],
            'division' => $user['division'],
            'zone' => $user['zone']
        ], $rememberMe);
        
        // Update last login
        $this->updateLastLogin('users', 'id', $user['id']);
        
        // Log successful login
        $this->logActivity('user_login', ['user_id' => $user['id'], 'role' => $user['role']]);
        
        $this->setFlash('success', 'Welcome back, ' . $user['name']);
        $this->redirectToDashboard($user['role']);
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
            'password_confirmation' => 'required',
            'terms' => 'required'
        ]);
        
        if (!$isValid) {
            $this->setFlash('error', implode('<br>', $validator->getAllErrorMessages()));
            $this->redirect(Config::getAppUrl() . '/signup');
            return;
        }
        
        // Check password confirmation
        if ($_POST['password'] !== $_POST['password_confirmation']) {
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
            
            $this->setFlash('success', 'Registration successful! Your account is pending approval. You will receive an email confirmation once approved.');
            $this->redirect(Config::getAppUrl() . '/login');
            
        } catch (Exception $e) {
            $this->db->rollback();
            error_log("Registration error: " . $e->getMessage());
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
    
    private function redirectToDashboard($role = null) {
        if (!$role) {
            $role = $this->session->getUserRole();
        }
        
        switch ($role) {
            case 'customer':
                $this->redirect(Config::getAppUrl() . '/');
                break;
            case 'controller':
            case 'controller_nodal':
                $this->redirect(Config::getAppUrl() . '/controller/dashboard');
                break;
            case 'admin':
            case 'superadmin':
                $this->redirect(Config::getAppUrl() . '/admin/dashboard');
                break;
            default:
                $this->redirect(Config::getAppUrl() . '/');
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
        // Implementation for sending email notification
        // This would use the email service
    }
    
    private function sendApprovalRequestToAdmin($customerId, $data) {
        // Implementation for sending approval request to admin
        // This would use the notification service
    }
}
