<?php
/**
 * Base Controller for SAMPARK MVC Framework
 * Provides common functionality for all controllers
 */

require_once __DIR__ . '/../utils/ActivityLogger.php';

class BaseController {
    protected $db;
    protected $session;
    protected $activityLogger;
    
    public function __construct() {
        $this->db = Database::getInstance();
        $this->session = new Session();
        $this->activityLogger = new ActivityLogger();
        
        // Check for remember me token on every page load
        // Only if not already logged in and not on login/logout pages
        $currentPath = $_SERVER['REQUEST_URI'] ?? '';
        $isAuthPage = strpos($currentPath, '/login') !== false || strpos($currentPath, '/logout') !== false;
        
        if (!$this->session->isLoggedIn() && !$isAuthPage) {
            $this->session->checkRememberMe();
        }
    }
    
    /**
     * Render a view with data
     */
    protected function view($viewName, $data = []) {
        // Add authentication data to all views
        $data = array_merge($this->getAuthData(), $data);
        
        // Extract data to variables
        extract($data);
        
        // Include the view file
        $viewFile = "../src/views/{$viewName}.php";
        if (file_exists($viewFile)) {
            require_once $viewFile;
        } else {
            throw new Exception("View not found: {$viewName}");
        }
    }
    
    /**
     * Return JSON response
     */
    protected function json($data, $statusCode = 200) {
        header('Content-Type: application/json');
        http_response_code($statusCode);
        echo json_encode($data);
        exit;
    }
    
    /**
     * Redirect to a URL
     */
    protected function redirect($url) {
        header("Location: {$url}");
        exit;
    }
    
    /**
     * Check if user is authenticated
     */
    protected function requireAuth() {
        if (!$this->session->isLoggedIn()) {
            $this->redirect(Config::APP_URL . '/login');
        }
    }
    
    /**
     * Check if user has required role
     */
    protected function requireRole($roles) {
        $this->requireAuth();
        
        $userRole = $this->session->get('user_role');
        
        if (is_string($roles)) {
            $roles = [$roles];
        }
        
        if (!in_array($userRole, $roles)) {
            $this->json(['error' => 'Access denied'], 403);
        }
    }
    
    /**
     * Validate CSRF token
     */
    protected function validateCSRF() {
        // Accept token from POST/GET parameters or custom header (e.g. sent by fetch/axios)
        $token = $_POST['csrf_token'] ?? $_GET['csrf_token'] ?? '';

        // Fallback: check common CSRF header used by AJAX libraries
        if (empty($token) && isset($_SERVER['HTTP_X_CSRF_TOKEN'])) {
            $token = $_SERVER['HTTP_X_CSRF_TOKEN'];
        }

        if (!$this->session->validateCSRF($token)) {
            // For login forms, redirect back with error instead of JSON
            if (isset($_POST['login_type'])) {
                $this->setFlash('error', 'Invalid security token. Please try again.');
                $this->redirect(Config::APP_URL . '/login');
            } else {
                $this->json(['error' => 'Invalid CSRF token'], 403);
            }
        }
    }
    
    /**
     * Get current user information
     */
    protected function getCurrentUser() {
        $userData = [
            'id' => $this->session->get('user_id'),
            'role' => $this->session->get('user_role'),
            'name' => $this->session->get('user_name'),
            'email' => $this->session->get('user_email')
        ];
        
        // Add user type specific fields
        if ($this->session->getUserType() === 'user') {
            $userData['department'] = $this->session->get('user_department');
            $userData['division'] = $this->session->get('user_division');
            $userData['zone'] = $this->session->get('user_zone');
        } else {
            // Customer specific fields
            $userData['customer_id'] = $this->session->get('customer_id');
            $userData['company_name'] = $this->session->get('company_name');
            $userData['mobile'] = $this->session->get('mobile');
        }
        
        return $userData;
    }
    
    /**
     * Validate input data
     */
    protected function validate($data, $rules) {
        $validator = new Validator();
        return $validator->validate($data, $rules);
    }
    
    /**
     * Upload files securely
     */
    protected function handleFileUpload($files, $complaintId) {
        $uploader = new FileUploader();
        return $uploader->uploadEvidence($files, $complaintId, 'user', $this->getCurrentUser()['id']);
    }
    
    /**
     * Send notification
     */
    protected function sendNotification($type, $recipients, $data) {
        $notifier = new NotificationService();
        return $notifier->send($type, $recipients, $data);
    }
    
    /**
     * Log activity
     */
    protected function logActivity($action, $details = []) {
        $user = $this->getCurrentUser();
        
        return $this->activityLogger->log([
            'user_id' => $user['id'],
            'user_role' => $user['role'],
            'action' => $action,
            'description' => json_encode($details),
            'ip_address' => $_SERVER['REMOTE_ADDR'],
            'user_agent' => $_SERVER['HTTP_USER_AGENT']
        ]);
    }

    protected function log($level, $message, $context = []) {
        $user = $this->getCurrentUser();

        return $this->activityLogger->log([
            'user_id' => $user['id'],
            'user_role' => $user['role'],
            'action' => $level,
            'description' => $message . ' ' . json_encode($context)
        ]);
    }
    
    /**
     * Set flash message
     */
    protected function setFlash($type, $message) {
        $this->session->setFlash($type, $message);
    }
    
    /**
     * Get paginated results
     */
    protected function paginate($query, $params = [], $page = 1, $perPage = null) {
        if ($perPage === null) {
            $perPage = Config::RECORDS_PER_PAGE;
        }
        
        $offset = ($page - 1) * $perPage;
        
        // Count total records
        $countQuery = preg_replace('/SELECT .+ FROM/i', 'SELECT COUNT(*) as total FROM', $query);
        $result = $this->db->fetch($countQuery, $params);
        $total = isset($result['total']) ? $result['total'] : 0;
        
        // Get paginated results
        $query .= " LIMIT {$offset}, {$perPage}";
        $results = $this->db->fetchAll($query, $params);
        
        return [
            'data' => $results,
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => ceil($total / $perPage),
            'has_next' => $page < ceil($total / $perPage),
            'has_prev' => $page > 1
        ];
    }
    
    /**
     * Get authentication data for views
     */
    protected function getAuthData() {
        return [
            'is_logged_in' => $this->session->isLoggedIn(),
            'user_role' => $this->session->getUserRole(),
            'user_name' => $this->session->get('user_name'),
            'csrf_token' => $this->session->getCSRFToken(),
            'flash_messages' => $this->getFlashMessages()
        ];
    }
    
    /**
     * Get flash messages
     */
    protected function getFlashMessages() {
        return $this->session->getFlash();
    }
}
